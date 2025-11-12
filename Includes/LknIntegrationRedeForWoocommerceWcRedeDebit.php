<?php

namespace Lkn\IntegrationRedeForWoocommerce\Includes;

use Exception;
use Lkn\IntegrationRedeForWoocommerce\Includes\LknIntegrationRedeForWoocommerceWcRedeAbstract;
use WC_Order;
use WP_Error;

final class LknIntegrationRedeForWoocommerceWcRedeDebit extends LknIntegrationRedeForWoocommerceWcRedeAbstract
{
    public function __construct()
    {
        $this->id = 'rede_debit';
        $this->has_fields = true;
        $this->method_title = esc_attr__('Pay with the Rede Debit', 'woo-rede');
        $this->method_description = esc_attr__('Enables and configures payments with Rede Debit', 'woo-rede');
        $this->supports = array(
            'products',
            'refunds',
        );

        $this->icon = LknIntegrationRedeForWoocommerceHelper::getUrlIcon();

        $this->initFormFields();

        $this->init_settings();

        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');

        $this->environment = $this->get_option('environment');
        $this->pv = $this->get_option('pv');
        $this->token = $this->get_option('token');

        if ($this->get_option('enabled_soft_descriptor') === 'yes') {
            $this->soft_descriptor = preg_replace('/\W/', '', $this->get_option('soft_descriptor'));
        } elseif ($this->get_option('enabled_soft_descriptor') === 'no') {
            add_option('lknIntegrationRedeForWoocommerceSoftDescriptorErrorDebit', false);
            update_option('lknIntegrationRedeForWoocommerceSoftDescriptorErrorDebit', false);
        }

        $this->auto_capture = true;
        $this->max_parcels_number = $this->get_option('max_parcels_number');
        $this->min_parcels_value = $this->get_option('min_parcels_value');

        $this->partner_module = $this->get_option('module');
        $this->partner_gateway = $this->get_option('gateway');

        $this->debug = $this->get_option('debug');

        $this->log = $this->get_logger();

        $this->configs = $this->getConfigsRedeDebit();
    }

    /**
     * Fields validation.
     *
     * @return bool
     */
    public function validate_fields()
    {
        if (empty($_POST['rede_debit_number'])) {
            wc_add_notice(esc_attr__('Card number is a required field', 'woo-rede'), 'error');

            return false;
        }

        if (empty($_POST['rede_debit_expiry'])) {
            wc_add_notice(esc_attr__('Card expiration is a required field', 'woo-rede'), 'error');

            return false;
        }

        if (empty($_POST['rede_debit_cvc'])) {
            wc_add_notice(esc_attr__('Card security code is a required field', 'woo-rede'), 'error');

            return false;
        }

        if (! ctype_digit(sanitize_text_field(wp_unslash($_POST['rede_debit_cvc'])))) {
            wc_add_notice(esc_attr__('Card security code must be a numeric value', 'woo-rede'), 'error');
            return false;
        }

        if (strlen(sanitize_text_field(wp_unslash($_POST['rede_debit_cvc']))) < 3) {
            wc_add_notice(esc_attr__('Card security code must be at least 3 digits long', 'woo-rede'), 'error');
            return false;
        }

        if (empty($_POST['rede_debit_holder_name'])) {
            wc_add_notice(esc_attr__('Cardholder name is a required field', 'woo-rede'), 'error');

            return false;
        }

        return true;
    }

    /**
     * Obtém token de autenticação OAuth2 usando sistema de cache específico do gateway
     */
    private function get_oauth_token()
    {
        $token = LknIntegrationRedeForWoocommerceHelper::get_rede_oauth_token_for_gateway($this->id);
        
        if ($token === null) {
            throw new Exception('Não foi possível obter token de autenticação OAuth2 para ' . $this->id);
        }
        
        return $token;
    }

    /**
     * Processa status do pedido
     */
    private function process_order_status_v2($order, $transaction_response, $note = '')
    {
        $return_code = $transaction_response['returnCode'] ?? '';
        $return_message = $transaction_response['returnMessage'] ?? '';
        
        $status_note = sprintf('Rede[%s]', $return_message);
        $order->add_order_note($status_note . ' ' . $note);

        // Só altera o status se o pedido estiver pendente
        if ($order->get_status() === 'pending') {
            if ($return_code == '00') {
                // Status configurável pelo usuário para pagamentos aprovados
                $payment_complete_status = $this->get_option('payment_complete_status', 'processing');
                $order->update_status($payment_complete_status);
                apply_filters("integrationRedeChangeOrderStatus", $order, $this);
            } else {
                $order->update_status('failed', $status_note);
            }
        }

        WC()->cart->empty_cart();
    }

    /**
     * Processa transação de débito
     */
    private function process_debit_transaction_v2($reference, $order_total, $cardData)
    {
        $access_token = $this->get_oauth_token();
        
        $amount = str_replace(".", "", number_format($order_total, 2, '.', ''));
        
        if ($this->environment === 'production') {
            $apiUrl = 'https://api.userede.com.br/erede/v2/transactions';
        } else {
            $apiUrl = 'https://sandbox-erede.useredecloud.com.br/v2/transactions';
        }

        $body = array(
            'capture' => $this->auto_capture,
            'kind' => 'debit',
            'reference' => (string)$reference,
            'amount' => (int)$amount,
            'cardholderName' => $cardData['card_holder'],
            'cardNumber' => $cardData['card_number'],
            'expirationMonth' => (int)$cardData['card_expiration_month'],
            'expirationYear' => (int)$cardData['card_expiration_year'],
            'securityCode' => $cardData['card_cvv'],
            'subscription' => false,
            'origin' => 1,
            'distributorAffiliation' => 0
        );
        
        if ($this->get_option('enabled_soft_descriptor') === 'yes' && !empty($this->soft_descriptor)) {
            $body['softDescriptor'] = $this->soft_descriptor;
        }

        $response = wp_remote_post($apiUrl, array(
            'method' => 'POST',
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $access_token
            ),
            'body' => wp_json_encode($body),
            'timeout' => 60
        ));

        if (is_wp_error($response)) {
            throw new Exception('Erro na requisição: ' . $response->get_error_message());
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_body, true);
        
        if ($response_code !== 200 && $response_code !== 201) {
            $error_message = 'Erro na transação';
            if (isset($response_data['message'])) {
                $error_message = $response_data['message'];
            } elseif (isset($response_data['errors']) && is_array($response_data['errors'])) {
                $error_message = implode(', ', $response_data['errors']);
            }
            throw new Exception($error_message);
        }
        
        if (!isset($response_data['returnCode']) || $response_data['returnCode'] !== '00') {
            $error_message = isset($response_data['returnMessage']) ? $response_data['returnMessage'] : 'Transação recusada';
            throw new Exception($error_message);
        }
        
        return $response_data;
    }

    public function regOrderLogs($orderId, $order_total, $cardData, $transaction, $order, $brand = null): void
    {
        if ('yes' == $this->debug) {
            $tId = null;
            $returnCode = null;
            
            if ($brand === null && $transaction) {
                $brand = null;
                if (is_array($transaction)) {
                    $tId = $transaction['tid'] ?? null;
                    $returnCode = $transaction['returnCode'] ?? null;
                }
                
                if ($tId) {
                    $brand = LknIntegrationRedeForWoocommerceHelper::getTransactionBrandDetails($tId, $this);
                }
            }
            $default_currency = get_option('woocommerce_currency', 'BRL');
            $order_currency = method_exists($order, 'get_currency') ? $order->get_currency() : $default_currency;
            $currency_json_path = INTEGRATION_REDE_FOR_WOOCOMMERCE_DIR . 'Includes/files/linkCurrencies.json';
            $currency_data = LknIntegrationRedeForWoocommerceHelper::lkn_get_currency_rates($currency_json_path);
            $convert_to_brl_enabled = LknIntegrationRedeForWoocommerceHelper::is_convert_to_brl_enabled($this->id);

            $exchange_rate_value = 1;
            if ($convert_to_brl_enabled && $currency_data !== false && is_array($currency_data) && isset($currency_data['rates']) && isset($currency_data['base'])) {
                // Exibe a cotação apenas se não for BRL
                if ($order_currency !== 'BRL' && isset($currency_data['rates'][$order_currency])) {
                    $rate = $currency_data['rates'][$order_currency];
                    // Converte para string, preservando todas as casas decimais
                    $exchange_rate_value = (string)$rate;
                }
            }

            $bodyArray = array(
                'orderId' => $orderId,
                'amount' => $order_total,
                'orderCurrency' => $order_currency,
                'currencyConverted' => $convert_to_brl_enabled ? 'BRL' : null,
                'exchangeRateValue' => $exchange_rate_value,
                'cardData' => $cardData,
                'brand' => isset($tId) && isset($brand) ? $brand['brand'] : null,
                'returnCode' => isset($returnCode) ? $returnCode : null,
            );

            $bodyArray['cardData']['card_number'] = LknIntegrationRedeForWoocommerceHelper::censorString($bodyArray['cardData']['card_number'], 8);

            $orderLogsArray = array(
                'body' => $bodyArray,
                'response' => $transaction
            );

            $orderLogs = json_encode($orderLogsArray);
            $order->update_meta_data('lknWcRedeOrderLogs', $orderLogs);
            $order->save();
        }
    }

    public function displayMeta($order): void
    {
        if ($order->get_payment_method() === 'rede_debit') {
            $metaKeys = array(
                '_wc_rede_transaction_environment' => esc_attr__('Environment', 'woo-rede'),
                '_wc_rede_transaction_return_code' => esc_attr__('Return Code', 'woo-rede'),
                '_wc_rede_transaction_return_message' => esc_attr__('Return Message', 'woo-rede'),
                '_wc_rede_transaction_id' => esc_attr__('Transaction ID', 'woo-rede'),
                '_wc_rede_transaction_refund_id' => esc_attr__('Refund ID', 'woo-rede'),
                '_wc_rede_transaction_cancel_id' => esc_attr__('Cancellation ID', 'woo-rede'),
                '_wc_rede_transaction_nsu' => esc_attr__('Nsu', 'woo-rede'),
                '_wc_rede_transaction_authorization_code' => esc_attr__('Authorization Code', 'woo-rede'),
                '_wc_rede_transaction_bin' => esc_attr__('Bin', 'woo-rede'),
                '_wc_rede_transaction_last4' => esc_attr__('Last 4', 'woo-rede'),
                '_wc_rede_transaction_holder' => esc_attr__('Cardholder', 'woo-rede'),
                '_wc_rede_transaction_expiration' => esc_attr__('Card Expiration', 'woo-rede')
            );

            $this->generateMetaTable($order, $metaKeys, 'Rede');
        }
    }

    /**
     * This function centralizes the data in one spot for ease mannagment
     *
     * @return array
     */
    public function getConfigsRedeDebit()
    {
        $configs = array();

        $configs['basePath'] = INTEGRATION_REDE_FOR_WOOCOMMERCE_DIR . 'Includes/logs/';
        $configs['base'] = $configs['basePath'] . gmdate('d.m.Y-H.i.s') . '.RedeDebit.log';
        $configs['debug'] = $this->get_option('debug');

        return $configs;
    }

    public function initFormFields(): void
    {
        LknIntegrationRedeForWoocommerceHelper::updateFixLoadScriptOption($this->id);

        $this->form_fields = array(
            'rede' => array(
                'title' => esc_attr__('General', 'woo-rede'),
                'type' => 'title',
            ),
            'enabled' => array(
                'title' => esc_attr__('Enable/Disable', 'woo-rede'),
                'type' => 'checkbox',
                'label' => esc_attr__('Enables payment with Rede', 'woo-rede'),
                'default' => 'no',
                'description' => esc_attr__('Enable or disable the debit card payment method.', 'woo-rede'),
                'desc_tip' => esc_attr__('Check this box and save to enable debit card settings.', 'woo-rede'),
                'custom_attributes' => array(
                    'data-title-description' => esc_attr__('Enable this option to allow customers to pay with debit cards using Rede API.', 'woo-rede')
                )
            ),
            'title' => array(
                'title' => esc_attr__('Title', 'woo-rede'),
                'type' => 'text',
                'default' => esc_attr__('Pay with the Rede Debit', 'woo-rede'),
                'description' => esc_attr__('This controls the title which the user sees during checkout.', 'woo-rede'),
                'desc_tip' => esc_attr__('Enter the title that will be shown to customers during the checkout process.', 'woo-rede'),
                'custom_attributes' => array(
                    'data-title-description' => esc_attr__('This text will appear as the payment method title during checkout. Choose something your customers will easily understand, like “Pay with debit card (Rede)”.', 'woo-rede')
                )
            ),
            'description' => array(
                'title' => __('Description', 'woo-rede'),
                'type' => 'textarea',
                'default' => __('Pay for your purchase with a debit card through ', 'woo-rede'),
                'desc_tip' => esc_attr__('This description appears below the payment method title at checkout. Use it to inform your customers about the payment processing details.', 'woo-rede'),
                'description' => esc_attr__('Payment method description that the customer will see on your checkout.', 'woo-rede'),
                'custom_attributes' => array(
                    'data-title-description' => esc_attr__('Provide a brief message that informs the customer how the payment will be processed. For example: “Your payment will be securely processed by Rede.”', 'woo-rede')
                )
            ),
            'environment' => array(
                'title' => esc_attr__('Environment', 'woo-rede'),
                'type' => 'select',
                'desc_tip' => esc_attr__('Choose between production or development mode for Rede API.', 'woo-rede'),
                'description' => esc_attr__('Choose the environment', 'woo-rede'),
                'custom_attributes' => array(
                    'data-title-description' => esc_attr__('Select "Tests" to test transactions in sandbox mode. Use "Production" for real transactions.”', 'woo-rede')
                ),
                'class' => 'wc-enhanced-select',
                'default' => esc_attr__('test', 'woo-rede'),
                'options' => array(
                    'test' => esc_attr__('Tests', 'woo-rede'),
                    'production' => esc_attr__('Production', 'woo-rede'),
                ),
            ),
            'pv' => array(
                'title' => esc_attr__('PV', 'woo-rede'),
                'type' => 'password',
                'desc_tip' => esc_attr__('Your Rede PV (affiliation number).', 'woo-rede'),
                'description' => esc_attr__('Rede credentials.', 'woo-rede'),
                'custom_attributes' => array(
                    'data-title-description' => esc_attr__('Your Rede PV (affiliation number) should be provided here.', 'woo-rede')
                ),
                'default' => $options['pv'] ?? '',
            ),
            'token' => array(
                'title' => esc_attr__('Token', 'woo-rede'),
                'type' => 'password',
                'desc_tip' => esc_attr__('Your Rede Token.', 'woo-rede'),
                'description' => esc_attr__('Rede credentials.', 'woo-rede'),
                'custom_attributes' => array(
                    'data-title-description' => esc_attr__('Your Rede Token should be placed here.', 'woo-rede')
                ),
                'default' => $options['token'] ?? '',
            ),

            'enabled_soft_descriptor' => array(
                'title' => __('Payment Description', 'woo-rede'),
                'type' => 'checkbox',
                'desc_tip' => esc_attr__('Send payment description to the Rede. If errors occur, disable this option to ensure correct transaction processing.', 'woo-rede'),
                'description' => esc_attr__('Enable sending the payment description to Rede.', 'woo-rede'),
                'custom_attributes' => array(
                    'data-title-description' => esc_attr__('Send payment description to Rede. Disable if it causes errors.', 'woo-rede')
                ),
                'label' => __('I have enabled the payment description feature in the', 'woo-rede') . ' ' . wp_kses_post('<a href="' . esc_url('https://meu.userede.com.br/ecommerce/identificacao-fatura') . '" target="_blank">' . __('Rede Dashboard', 'woo-rede') . '</a>') . '. ' . __('Default (Disabled)', 'woo-rede'),
                'default' => 'no',
            ),

            'soft_descriptor' => array(
                'title' => esc_attr__('Payment Description', 'woo-rede'),
                'type' => 'text',
                'desc_tip' => esc_attr__('Set the description to be sent to Rede along with the payment transaction.', 'woo-rede'),
                'description' => esc_attr__('Description to be sent to Rede.', 'woo-rede'),
                'custom_attributes' => array(
                    'data-title-description' => esc_attr__('Payment description sent to Rede.', 'woo-rede'),
                    'maxlength' => 20,
                ),
            ),

            'enabled_fix_load_script' => array(
                'title' => __('Load on checkout', 'woo-rede'),
                'type' => 'checkbox',
                'desc_tip' => esc_attr__('Disable to load the plugin during checkout. Enable to prevent infinite loading errors.', 'woo-rede'),
                'description' => esc_attr__('Selecione a posição onde o layout PIX será exibido na página de checkout.', 'woo-rede'),
                'custom_attributes' => array(
                    'data-title-description' => esc_attr__("This feature controls the plugin's loading on the checkout page. It's enabled by default to prevent infinite loading errors and should only be disabled if you're experiencing issues with the gateway.", 'woo-rede')
                ),
                'label' => __('Load plugin on checkout. Default (enabled)', 'woo-rede'),
                'default' => 'yes',
            ),

            'payment_complete_status' => array(
                'title' => esc_attr__('Payment Complete Status', 'woo-rede'),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'description' => esc_attr__('Choose what status to set orders after successful payment.', 'woo-rede'),
                'desc_tip' => esc_attr__('Select the order status that will be applied when payment is successfully processed.', 'woo-rede'),
                'default' => 'processing',
                'options' => array(
                    'processing' => esc_attr__('Processing', 'woo-rede'),
                    'completed' => esc_attr__('Completed', 'woo-rede'),
                    'on-hold' => esc_attr__('On Hold', 'woo-rede'),
                ),
                'custom_attributes' => array(
                    'data-title-description' => esc_attr__('Choose the status that approved payments should have. "Processing" is recommended for most cases.', 'woo-rede')
                )
            ),

            'developers' => array(
                'title' => esc_attr__('Developer', 'woo-rede'),
                'type' => 'title',
            ),

            'debug' => array(
                'title' => esc_attr__('Debug', 'woo-rede'),
                'type' => 'checkbox',
                'label' => esc_attr__('Enable debug logs.', 'woo-rede') . ' ' . wp_kses_post('<a href="' . esc_url(admin_url('admin.php?page=wc-status&tab=logs')) . '" target="_blank">' . __('See logs', 'woo-rede') . '</a>'),
                'default' => 'no',
                'desc_tip' => esc_attr__('Enable transaction logging.', 'woo-rede'),
                'description' => esc_attr__('Enable this option to log payment requests and responses for troubleshooting purposes.', 'woo-rede'),
                'custom_attributes' => array(
                    'data-title-description' => esc_attr__("When enabled, all Rede transactions will be logged.", 'woo-rede')
                ),
            )
        );

        if ($this->get_option('debug') == 'yes') {
            $this->form_fields['show_order_logs'] =  array(
                'title' => __('Visualizar Log no Pedido', 'woo-rede'),
                'type' => 'checkbox',
                'label' => sprintf('Habilita visualização do log da transação dentro do pedido.', 'woo-rede'),
                'default' => 'no',
                'desc_tip' => esc_attr__('Useful for quickly viewing payment log data without accessing the system log files.', 'woo-rede'),
                'description' => esc_attr__('Enable this option to log payment requests and responses for troubleshooting purposes.', 'woo-rede'),
                'custom_attributes' => array(
                    'data-title-description' => esc_attr__("Enable this to show the transaction details for Rede payments directly in each order’s admin panel.", 'woo-rede')
                ),
            );
            $this->form_fields['clear_order_records'] =  array(
                'title' => __('Limpar logs nos Pedidos', 'woo-rede'),
                'type' => 'button',
                'id' => 'validateLicense',
                'class' => 'woocommerce-save-button components-button is-primary',
                'desc_tip' => esc_attr__('Use only if you no longer need the Rede transaction logs for past orders.', 'woo-rede'),
                'description' => esc_attr__('Click this button to delete all Rede log data stored in orders.', 'woo-rede'),
            );
        }

        $customConfigs = apply_filters('integrationRedeGetCustomConfigs', $this->form_fields, array(), $this->id);

        if (! empty($customConfigs)) {
            $this->form_fields = array_merge($this->form_fields, $customConfigs);
        }
    }

    public function checkoutScripts(): void
    {
        $plugin_url = plugin_dir_url(LknIntegrationRedeForWoocommerceWcRede::FILE) . '../';
        if ($this->get_option('enabled_fix_load_script') === 'yes') {
            wp_enqueue_script('fixInfiniteLoading-js', $plugin_url . 'Public/js/fixInfiniteLoading.js', array(), '1.0.0', true);
        }

        if (! is_checkout()) {
            return;
        }

        if (! $this->is_available()) {
            return;
        }

        wp_enqueue_style('wc-rede-checkout-webservice');

        wp_enqueue_style('card-style', $plugin_url . 'Public/css/card.css', array(), '1.0.0', 'all');
        wp_enqueue_style('select-style', $plugin_url . 'Public/css/lknIntegrationRedeForWoocommerceSelectStyle.css', array(), '1.0.0', 'all');
        wp_enqueue_style('wooRedeDebit-style', $plugin_url . 'Public/css/rede/styleRedeDebit.css', array(), '1.0.0', 'all');

        wp_enqueue_script('wooRedeDebit-js', $plugin_url . 'Public/js/debitCard/rede/wooRedeDebit.js', array(), '1.0.0', true);
        wp_enqueue_script('woo-rede-animated-card-jquery', $plugin_url . 'Public/js/jquery.card.js', array('jquery', 'wooRedeDebit-js'), '2.5.0', true);

        wp_localize_script('wooRedeDebit-js', 'wooRede', array(
            'debug' => defined('WP_DEBUG') && WP_DEBUG,
        ));

        apply_filters('integrationRedeSetCustomCSSPro', get_option('woocommerce_rede_debit_settings')['custom_css_short_code'] ?? false);
    }

    public function process_payment($order_id)
    {
        if (isset($_POST['rede_card_nonce']) && ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['rede_card_nonce'])), 'redeCardNonce')) {
            return array(
                'result' => 'fail',
                'redirect' => '',
            );
        }

        $order = wc_get_order($order_id);
        $cardNumber = isset($_POST['rede_debit_number']) ?
            sanitize_text_field(wp_unslash($_POST['rede_debit_number'])) : '';

        $debitExpiry = isset($_POST['rede_debit_expiry']) ? sanitize_text_field(wp_unslash($_POST['rede_debit_expiry'])) : '';

        if (strpos($debitExpiry, '/') !== false) {
            $expiration = explode('/', $debitExpiry);
        } else {
            $expiration = array(
                substr($debitExpiry, 0, 2),
                substr($debitExpiry, -2, 2),
            );
        }

        $cardData = array(
            'card_number' => preg_replace('/[^\d]/', '', sanitize_text_field(wp_unslash($_POST['rede_debit_number']))),
            'card_expiration_month' => sanitize_text_field($expiration[0]),
            'card_expiration_year' => $this->normalize_expiration_year(sanitize_text_field($expiration[1])),
            'card_cvv' => isset($_POST['rede_debit_cvc']) ? sanitize_text_field(wp_unslash($_POST['rede_debit_cvc'])) : '',
            'card_holder' => isset($_POST['rede_debit_holder_name']) ? sanitize_text_field(wp_unslash($_POST['rede_debit_holder_name'])) : '',
        );

        try {
            $valid = $this->validate_card_number($cardNumber);
            if (false === $valid) {
                throw new Exception(__('Please enter a valid debit card number', 'woo-rede'));
            }

            $valid = $this->validate_card_fields($_POST);
            if (false === $valid) {
                throw new Exception(__('One or more invalid fields', 'woo-rede'), 500);
            }

            $orderId = $order->get_id();
            $order_total = $order->get_total();
            $decimals = get_option('woocommerce_price_num_decimals', 2);
            $convert_to_brl_enabled = false;
            $default_currency = get_option('woocommerce_currency', 'BRL');
            $order_currency = method_exists($order, 'get_currency') ? $order->get_currency() : $default_currency;

            // Check if BRL conversion is enabled via pro plugin
            $convert_to_brl_enabled = LknIntegrationRedeForWoocommerceHelper::is_convert_to_brl_enabled($this->id);

            // Convert order total to BRL if enabled
            $order_total = LknIntegrationRedeForWoocommerceHelper::convert_order_total_to_brl($order_total, $order, $convert_to_brl_enabled);

            if ($convert_to_brl_enabled) {
                $order->add_order_note(
                    sprintf(
                        // translators: %s is the original order currency code (e.g., USD, EUR, etc.)
                        __('Order currency %s converted to BRL.', 'woo-rede'),
                        $order_currency,
                    )
                );
            }

            $order_total = wc_format_decimal($order_total, $decimals);

            try {
                $transaction_response = $this->process_debit_transaction_v2($orderId + time(), $order_total, $cardData);
                $this->regOrderLogs($orderId, $order_total, $cardData, $transaction_response, $order);
            } catch (Exception $e) {
                $this->regOrderLogs($orderId, $order_total, $cardData, $e->getMessage(), $order);
                throw $e;
            }

            $default_currency = get_option('woocommerce_currency', 'BRL');
            $order_currency = method_exists($order, 'get_currency') ? $order->get_currency() : $default_currency;
            $currency_json_path = INTEGRATION_REDE_FOR_WOOCOMMERCE_DIR . 'Includes/files/linkCurrencies.json';
            $currency_data = LknIntegrationRedeForWoocommerceHelper::lkn_get_currency_rates($currency_json_path);

            $exchange_rate_value = 1;
            if ($convert_to_brl_enabled && $currency_data !== false && is_array($currency_data) && isset($currency_data['rates']) && isset($currency_data['base'])) {
                // Exibe a cotação apenas se não for BRL
                if ($order_currency !== 'BRL' && isset($currency_data['rates'][$order_currency])) {
                    $rate = $currency_data['rates'][$order_currency];
                    // Converte para string, preservando todas as casas decimais
                    $exchange_rate_value = (string)$rate;
                }
            }

            $order->update_meta_data('_wc_rede_transaction_return_code', $transaction_response['returnCode'] ?? '');
            $order->update_meta_data('_wc_rede_transaction_return_message', $transaction_response['returnMessage'] ?? '');
            $order->update_meta_data('_wc_rede_transaction_id', $transaction_response['tid'] ?? '');
            $order->update_meta_data('_wc_rede_transaction_refund_id', $transaction_response['refundId'] ?? '');
            $order->update_meta_data('_wc_rede_transaction_cancel_id', $transaction_response['cancelId'] ?? '');
            $order->update_meta_data('_wc_rede_transaction_bin', $transaction_response['card']['bin'] ?? '');
            $order->update_meta_data('_wc_rede_transaction_last4', $transaction_response['card']['last4'] ?? '');
            $order->update_meta_data('_wc_rede_transaction_brand', $transaction_response['card']['brand'] ?? '');
            $order->update_meta_data('_wc_rede_transaction_nsu', $transaction_response['nsu'] ?? '');
            $order->update_meta_data('_wc_rede_transaction_authorization_code', $transaction_response['authorizationCode'] ?? '');
            $order->update_meta_data('_wc_rede_captured', $transaction_response['capture'] ?? $this->auto_capture);
            $order->update_meta_data('_wc_rede_total_amount', $order->get_total());
            $order->update_meta_data('_wc_rede_total_amount_converted', $order_total);
            $order->update_meta_data('_wc_rede_total_amount_is_converted', $convert_to_brl_enabled ? true : false);
            $order->update_meta_data('_wc_rede_exchange_rate', $exchange_rate_value);
            $order->update_meta_data('_wc_rede_decimal_value', $decimals);

            if (isset($transaction_response['authorization'])) {
                $order->update_meta_data('_wc_rede_transaction_authorization_status', $transaction_response['authorization']['status'] ?? '');
            }

            $order->update_meta_data('_wc_rede_transaction_holder', $cardData['card_holder']);
            $order->update_meta_data('_wc_rede_transaction_expiration', sprintf('%02d/%d', $expiration[0], (int) ($expiration[1])));
            $order->update_meta_data('_wc_rede_transaction_environment', $this->environment);

            $this->process_order_status_v2($order, $transaction_response, '');

            $order->save();

            if ('yes' == $this->debug) {
                $tId = $transaction_response['tid'] ?? null;
                $returnCode = $transaction_response['returnCode'] ?? null;
                $brandDetails = null;
                
                if ($tId) {
                    $brandDetails = LknIntegrationRedeForWoocommerceHelper::getTransactionBrandDetails($tId, $this);
                }

                $this->log->log('info', $this->id, array(
                    'transaction' => $transaction_response,
                    'order' => array(
                        'orderId' => $orderId,
                        'amount' => $order_total,
                        'orderCurrency' => $order_currency,
                        'currencyConverted' => $convert_to_brl_enabled ? 'BRL' : null,
                        'exchangeRateValue' => $exchange_rate_value,
                        'status' => $order->get_status(),
                        'brand' => isset($brandDetails['brand']) ? $brandDetails['brand'] : ($transaction_response['card']['brand'] ?? null),
                        'returnCode' => $returnCode,
                    ),
                ));
            }
        } catch (Exception $e) {
            if ($e->getCode() == 63) {
                add_option('lknIntegrationRedeForWoocommerceSoftDescriptorErrorDebit', true);
                update_option('lknIntegrationRedeForWoocommerceSoftDescriptorErrorDebit', true);
            }

            $this->add_error($e->getMessage());

            return array(
                'result' => 'fail',
                'redirect' => '',
            );
        }

        return array(
            'result' => 'success',
            'redirect' => $this->get_return_url($order),
        );
    }

    /**
     * Processa reembolso
     */
    private function process_refund_v2($tid, $amount)
    {
        $access_token = $this->get_oauth_token();
        
        if ($this->environment === 'production') {
            $apiUrl = 'https://api.userede.com.br/erede/v2/transactions/' . $tid . '/refunds';
        } else {
            $apiUrl = 'https://sandbox-erede.useredecloud.com.br/v2/transactions/' . $tid . '/refunds';
        }

        $amount_int = str_replace(".", "", number_format($amount, 2, '.', ''));
        
        $body = array(
            'amount' => (int)$amount_int
        );

        $response = wp_remote_post($apiUrl, array(
            'method' => 'POST',
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $access_token
            ),
            'body' => wp_json_encode($body),
            'timeout' => 60
        ));

        if (is_wp_error($response)) {
            throw new Exception('Erro na requisição de reembolso: ' . $response->get_error_message());
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_body, true);
        
        if ($response_code !== 200 && $response_code !== 201) {
            $error_message = 'Erro no reembolso';
            if (isset($response_data['message'])) {
                $error_message = $response_data['message'];
            } elseif (isset($response_data['errors']) && is_array($response_data['errors'])) {
                $error_message = implode(', ', $response_data['errors']);
            }
            throw new Exception($error_message);
        }
        
        return $response_data;
    }

    public function process_refund($order_id, $amount = 0, $reason = '')
    {
        $order = new WC_Order($order_id);
        if ($order->get_payment_method() === 'rede_debit') {
            $totalAmount = $order->get_meta('_wc_rede_total_amount');
            $is_converted = $order->get_meta('_wc_rede_total_amount_is_converted');
            $exchange_rate = $order->get_meta('_wc_rede_exchange_rate');
            $decimals = $order->get_meta('_wc_rede_decimal_value');
            $amount_converted = $order->get_meta('_wc_rede_total_amount_converted');
            $order_currency = method_exists($order, 'get_currency') ? $order->get_currency() : 'BRL';

            if (!empty($order->get_meta('_wc_rede_transaction_canceled'))) {
                $order->add_order_note('Rede[Refund Error] ' . esc_attr__('Total refund already processed, check the order notes block.', 'woo-rede'));
                $order->save();
                return false;
            }

            if (! $order || ! $order->get_meta('_wc_rede_transaction_id')) {
                $order->add_order_note('Rede[Refund Error] ' . esc_attr__('Order or transaction invalid for refund.', 'woo-rede'));
                $order->save();
                return false;
            }

            if (empty($order->get_meta('_wc_rede_transaction_canceled'))) {
                $tid = $order->get_meta('_wc_rede_transaction_id');
                $amount = wc_format_decimal($amount, 2);

                // Se conversão está ativa, usa o valor convertido
                if ($is_converted && $exchange_rate) {
                    $amount_brl = floatval($amount) / floatval($exchange_rate);
                    $amount_brl = number_format($amount_brl, (int)$decimals, '.', '');
                    $amount = $amount_brl;
                } else if ($amount == $order->get_total()) {
                    $amount = $totalAmount;
                }

                try {
                    if ($amount > 0) {
                        if (isset($amount) && ($amount > 0 && $amount < $totalAmount) || ($is_converted && $amount > 0 && $amount < $amount_converted)) {
                            $order->add_order_note('Rede[Refund Error] ' . esc_attr__('Partial refunds are not allowed. You must refund the total order amount.', 'woo-rede'));
                            $order->save();
                            return false;
                        } elseif ($order->get_total() == $amount || ($is_converted && $amount == $amount_converted)) {
                            $refund_response = $this->process_refund_v2($tid, $amount);
                        }

                        update_post_meta($order_id, '_wc_rede_transaction_refund_id', $refund_response['refundId'] ?? '');
                        if (empty($refund_response['cancelId'])) {
                            update_post_meta($order_id, '_wc_rede_transaction_cancel_id', $refund_response['tid'] ?? $tid);
                        } else {
                            update_post_meta($order_id, '_wc_rede_transaction_cancel_id', $refund_response['cancelId']);
                        }
                        update_post_meta($order_id, '_wc_rede_transaction_canceled', true);

                        // Formata o valor conforme moeda
                        if ($is_converted) {
                            $formatted_amount = wc_price($amount, array('currency' => 'BRL'));
                        } else {
                            $formatted_amount = wc_price($amount, array('currency' => $order_currency));
                        }
                        $order->add_order_note(esc_attr__('Refunded:', 'woo-rede') . ' ' . $formatted_amount);
                        $order->save();
                    } else {
                        $order->add_order_note('Rede[Refund Error] ' . esc_attr__('Invalid refund amount.', 'woo-rede'));
                        $order->save();
                        return false;
                    }
                } catch (Exception $e) {
                    $order->add_order_note('Rede[Refund Error] ' . sanitize_text_field($e->getMessage()));
                    $order->save();
                    return false;
                }

                return true;
            }
        } else {
            return false;
        }
    }

    protected function getCheckoutForm($order_total = 0): void
    {
        $wc_get_template = 'woocommerce_get_template';

        if (function_exists('wc_get_template')) {
            $wc_get_template = 'wc_get_template';
        }

        $wc_get_template(
            'debitCard/redePaymentDebitForm.php',
            array(),
            'woocommerce/rede/',
            LknIntegrationRedeForWoocommerceWcRede::getTemplatesPath()
        );
    }
}
