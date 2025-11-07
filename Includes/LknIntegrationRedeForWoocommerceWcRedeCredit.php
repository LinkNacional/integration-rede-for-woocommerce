<?php

namespace Lkn\IntegrationRedeForWoocommerce\Includes;

use Exception;
use Lkn\IntegrationRedeForWoocommerce\Includes\LknIntegrationRedeForWoocommerceWcRedeAbstract;
use Symfony\Component\Console\Event\ConsoleEvent;
use WC_Order;
use WP_Error;

final class LknIntegrationRedeForWoocommerceWcRedeCredit extends LknIntegrationRedeForWoocommerceWcRedeAbstract
{
    public $api = null;

    public function __construct()
    {
        $this->id = 'rede_credit';
        $this->has_fields = true;
        $this->method_title = esc_attr__('Pay with the Rede Credit', 'woo-rede');
        $this->method_description = esc_attr__('Enables and configures payments with Rede Credit', 'woo-rede');
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
            add_option('lknIntegrationRedeForWoocommerceSoftDescriptorErrorCredit', false);
            update_option('lknIntegrationRedeForWoocommerceSoftDescriptorErrorCredit', false);
        }

        $this->auto_capture = sanitize_text_field($this->get_option('auto_capture')) == 'no' ? false : true;
        $this->max_parcels_number = $this->get_option('max_parcels_number');
        $this->min_parcels_value = $this->get_option('min_parcels_value');

        $this->partner_module = $this->get_option('module');
        $this->partner_gateway = $this->get_option('gateway');

        $this->debug = $this->get_option('debug');

        $this->log = $this->get_logger();

        $this->configs = $this->getConfigsRedeCredit();

        $this->api = new LknIntegrationRedeForWoocommerceWcRedeAPI($this);
    }

    /**
     * Fields validation.
     *
     * @return bool
     */
    public function validate_fields()
    {
        if (empty($_POST['rede_credit_number'])) {
            wc_add_notice(esc_attr__('Card number is a required field', 'woo-rede'), 'error');

            return false;
        }

        if (empty($_POST['rede_credit_expiry'])) {
            wc_add_notice(esc_attr__('Card expiration is a required field', 'woo-rede'), 'error');

            return false;
        }

        if (empty($_POST['rede_credit_cvc'])) {
            wc_add_notice(esc_attr__('Card security code is a required field', 'woo-rede'), 'error');

            return false;
        }

        if (! ctype_digit(sanitize_text_field(wp_unslash($_POST['rede_credit_cvc'])))) {
            wc_add_notice(esc_attr__('Card security code must be a numeric value', 'woo-rede'), 'error');
            return false;
        }

        if (strlen(sanitize_text_field(wp_unslash($_POST['rede_credit_cvc']))) < 3) {
            wc_add_notice(esc_attr__('Card security code must be at least 3 digits long', 'woo-rede'), 'error');
            return false;
        }

        if (empty($_POST['rede_credit_holder_name'])) {
            wc_add_notice(esc_attr__('Cardholder name is a required field', 'woo-rede'), 'error');

            return false;
        }

        return true;
    }

    public function getConfigsRedeCredit()
    {
        $configs = array();

        $configs['basePath'] = INTEGRATION_REDE_FOR_WOOCOMMERCE_DIR . 'Includes/logs/';
        $configs['base'] = $configs['basePath'] . gmdate('d.m.Y-H.i.s') . '.RedeCredit.log';
        $configs['debug'] = $this->get_option('debug');

        return $configs;
    }

    public function displayMeta($order): void
    {
        if ($order->get_payment_method() === 'rede_credit') {
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
                '_wc_rede_transaction_brand' => esc_attr__('Brand', 'woo-rede'),
                '_wc_rede_transaction_installments' => esc_attr__('Installments', 'woo-rede'),
                '_wc_rede_transaction_holder' => esc_attr__('Cardholder', 'woo-rede'),
                '_wc_rede_transaction_expiration' => esc_attr__('Card Expiration', 'woo-rede')
            );

            $this->generateMetaTable($order, $metaKeys, 'Rede');
        }
    }

    public function initFormFields(): void
    {
        $options = get_option('woocommerce_rede_credit', array());
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
                'default' => $options['enabled'] ?? 'no',
                'desc_tip'    => esc_attr__('Check this box and save to enable credit card settings.', 'woo-rede'),
                'description' => esc_attr__('Enable or disable the credit card payment method.', 'woo-rede'),
                'custom_attributes' => array(
                    'data-title-description' => esc_attr__('Enable this option to allow customers to pay with credit cards using Rede API.', 'woo-rede')
                )
            ),
            'title' => array(
                'title' => esc_attr__('Title', 'woo-rede'),
                'type' => 'text',
                'default' => esc_attr__('Pay with the Rede Credit', 'woo-rede'),
                'desc_tip' => esc_attr__('Enter the title that will be shown to customers during the checkout process.', 'woo-rede'),
                'description' => esc_attr__('This controls the title which the user sees during checkout.', 'woo-rede'),
                'custom_attributes' => array(
                    'data-title-description' => esc_attr__('This text will appear as the payment method title during checkout. Choose something your customers will easily understand, like “Pay with credit card (Rede)”.', 'woo-rede')
                )

            ),
            'description' => array(
                'title' => esc_attr__('Description', 'woo-rede'),
                'type' => 'textarea',
                'default' => esc_attr__('Pay for your purchase with a credit card through ', 'woo-rede'),
                'desc_tip' => esc_attr__('This description appears below the payment method title at checkout. Use it to inform your customers about the payment processing details.', 'woo-rede'),
                'description' => esc_attr__('Payment method description that the customer will see on your checkout.', 'woo-rede'),
                'custom_attributes' => array(
                    'data-title-description' => esc_attr__('Provide a brief message that informs the customer how the payment will be processed. For example: “Your payment will be securely processed by Rede.”', 'woo-rede')
                )
            ),
            'rede' => array(
                'title' => esc_attr__('General', 'woo-rede'),
                'type' => 'title',
            ),
            'environment' => array(
                'title' => esc_attr__('Environment', 'woo-rede'),
                'type' => 'select',
                'description' => esc_attr__('Choose the environment', 'woo-rede'),
                'desc_tip' => esc_attr__('Choose between production or development mode for Rede API.', 'woo-rede'),
                'class' => 'wc-enhanced-select',
                'default' => esc_attr__('test', 'woo-rede'),
                'options' => array(
                    'test' => esc_attr__('Tests', 'woo-rede'),
                    'production' => esc_attr__('Production', 'woo-rede'),
                ),
                'custom_attributes' => array(
                    'data-title-description' => esc_attr__('Select "Tests" to test transactions in sandbox mode. Use "Production" for real transactions.', 'woo-rede')
                )
            ),
            'pv' => array(
                'title' => esc_attr__('PV', 'woo-rede'),
                'type' => 'password',
                'description' => esc_attr__('Rede credentials.', 'woo-rede'),
                'desc_tip' => esc_attr__('Your Rede PV (affiliation number).', 'woo-rede'),
                'default' => $options['pv'] ?? '',
                'custom_attributes' => array(
                    'data-title-description' => esc_attr__('Your Rede PV (affiliation number) should be provided here.', 'woo-rede')
                )
            ),
            'token' => array(
                'title' => esc_attr__('Token', 'woo-rede'),
                'type' => 'password',
                'description' => esc_attr__('Rede credentials.', 'woo-rede'),
                'desc_tip' => esc_attr__('Your Rede Token.', 'woo-rede'),
                'default' => $options['token'] ?? '',
                'custom_attributes' => array(
                    'data-title-description' => esc_attr__('Your Rede Token should be placed here.', 'woo-rede')
                )
            ),

            'enabled_soft_descriptor' => array(
                'title' => __('Payment Description', 'woo-rede'),
                'type' => 'checkbox',
                'description' => esc_attr__('Enable sending the payment description to Rede.', 'woo-rede'),
                'desc_tip' => esc_attr__('Send payment description to the Rede. If errors occur, disable this option to ensure correct transaction processing.', 'woo-rede'),
                'label' => __('I have enabled the payment description feature in the', 'woo-rede') . ' ' . wp_kses_post('<a href="' . esc_url('https://meu.userede.com.br/ecommerce/identificacao-fatura') . '" target="_blank">' . __('Rede Dashboard', 'woo-rede') . '</a>') . '. ' . __('Default (Disabled)', 'woo-rede'),
                'default' => 'no',
                'custom_attributes' => array(
                    'data-title-description' => esc_attr__('Send payment description to Rede. Disable if it causes errors.', 'woo-rede')
                )
            ),

            'soft_descriptor' => array(
                'title' => esc_attr__('Payment Description', 'woo-rede'),
                'type' => 'text',
                'description' => esc_attr__('Description to be sent to Rede.', 'woo-rede'),
                'desc_tip' => esc_attr__('Set the description to be sent to Rede along with the payment transaction.', 'woo-rede'),
                'custom_attributes' => array(
                    'maxlength' => 20,
                ),
                'custom_attributes' => array(
                    'data-title-description' => esc_attr__('Payment description sent to Rede.', 'woo-rede'),
                    'merge-top' => 'woocommerce_rede_credit_enabled_soft_descriptor'
                )
            ),

            'enabled_fix_load_script' => array(
                'title' => __('Load on checkout', 'woo-rede'),
                'type' => 'checkbox',
                'description' => __('Disable to load the plugin during checkout.', 'woo-rede'),
                'desc_tip' => __('Disable to load the plugin during checkout. Enable to prevent infinite loading errors.', 'woo-rede'),
                'label' => __('Load plugin on checkout. Default (enabled)', 'woo-rede'),
                'default' => 'yes',
                'custom_attributes' => array(
                    'data-title-description' => esc_attr__("This feature controls the plugin's loading on the checkout page. It's enabled by default to prevent infinite loading errors and should only be disabled if you're experiencing issues with the gateway.", 'woo-rede')
                )
            ),

            'credit_options' => array(
                'title' => esc_attr__('Credit Card', 'woo-rede'),
                'type' => 'title',
            ),

            'min_parcels_value' => array(
                'title' => esc_attr__('Value of the smallest installment', 'woo-rede'),
                'type' => 'text',
                'default' => '5',
                'description' => esc_attr__('Set the minimum installment value for credit card payments.', 'woo-rede'),
                'desc_tip' => esc_attr__('Set the minimum allowed amount for each installment in credit transactions.', 'woo-rede'),
                'custom_attributes' => array(
                    'data-title-description' => esc_attr__('Enter the minimum value each installment must have.', 'woo-rede')
                )
            ),
            'max_parcels_number' => array(
                'title' => esc_attr__('Max installments', 'woo-rede'),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => '12',
                'options' => array(
                    '1' => '1x',
                    '2' => '2x',
                    '3' => '3x',
                    '4' => '4x',
                    '5' => '5x',
                    '6' => '6x',
                    '7' => '7x',
                    '8' => '8x',
                    '9' => '9x',
                    '10' => '10x',
                    '11' => '11x',
                    '12' => '12x',
                ),
                'description' => esc_attr__('Define the maximum number of credit installments.', 'woo-rede'),
                'desc_tip' => esc_attr__('Set the maximum number of installments allowed in credit transactions.', 'woo-rede'),
                'custom_attributes' => array(
                    'data-title-description' => esc_attr__('Choose the maximum number of installments per order.', 'woo-rede')
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
                'description' => esc_attr__('Enable this option to log payment requests and responses for troubleshooting purposes.', 'woo-rede'),
                'desc_tip' => esc_attr__('Enable transaction logging.', 'woo-rede'),
                'custom_attributes' => array(
                    'data-title-description' => esc_attr__('When enabled, all Rede transactions will be logged.', 'woo-rede')
                )
            )
        );

        if ($this->get_option('debug') == 'yes') {
            $this->form_fields['show_order_logs'] =  array(
                'title' => __('Visualizar Log no Pedido', 'woo-rede'),
                'type' => 'checkbox',
                'label' => sprintf('Habilita visualização do log da transação dentro do pedido.', 'woo-rede'),
                'default' => 'no',
                'description' => esc_attr__('Displays Rede transaction logs inside WooCommerce order details.', 'woo-rede'),
                'desc_tip' => esc_attr__('Useful for quickly viewing payment log data without accessing the system log files.', 'woo-rede'),
                'custom_attributes' => array(
                    'data-title-description' => esc_attr__('Enable this to show the transaction details for Rede payments directly in each order’s admin panel.', 'woo-rede')
                )
            );
            $this->form_fields['clear_order_records'] =  array(
                'title' => __('Limpar logs nos Pedidos', 'woo-rede'),
                'type' => 'button',
                'id' => 'validateLicense',
                'class' => 'woocommerce-save-button components-button is-primary',
                'description' => esc_attr__('Click this button to delete all Rede log data stored in orders.', 'woo-rede'),
                'desc_tip' => esc_attr__('Use only if you no longer need the Rede transaction logs for past orders.', 'woo-rede'),
                'custom_attributes' => array(
                    'data-title-description' => esc_attr__('Choose the maximum number of installments per order.', 'woo-rede')
                )
            );
        }

        $customConfigs = apply_filters('integrationRedeGetCustomConfigs', $this->form_fields, array(
            'installment_interest' => $this->get_option('installment_interest'),
            'max_parcels_number' => $this->get_option('max_parcels_number'),
        ), $this->id);

        if (! empty($customConfigs)) {
            $this->form_fields = array_merge($this->form_fields, $customConfigs);
        }
    }

    public function getInstallments($order_total = 0)
    {
        $installments = array();
        $defaults = array(
            'min_value' => str_replace(',', '.', $this->min_parcels_value),
            'max_parcels' => $this->max_parcels_number,
        );

        $installments_result = wp_parse_args(apply_filters('integration_rede_installments', $defaults), $defaults);

        $min_value = (float) $installments_result['min_value'];
        $max_parcels = (int) $installments_result['max_parcels'];

        // Limita ao menor valor de parcelas permitido entre os produtos do carrinho
        if (function_exists('WC') && WC()->cart && !WC()->cart->is_empty()) {
            foreach (WC()->cart->get_cart() as $cart_item) {
                $product_id = $cart_item['product_id'];
                if ($this->id == 'rede_credit') {
                    $product_limit = get_post_meta($product_id, 'lknRedeProdutctInterest', true);
                } else {
                    $product_limit = get_post_meta($product_id, 'lknMaxipagoProdutctInterest', true);
                }
                
                if ($product_limit !== 'default' && is_numeric($product_limit)) {
                    $product_limit = (int) $product_limit;
                    if ($product_limit < $max_parcels) {
                        $max_parcels = $product_limit;
                    }
                }
            }
        }

        for ($i = 1; $i <= $max_parcels; ++$i) {
            if (($order_total / $i) < $min_value) {
                break;
            }

            $customLabel = null; // Resetar a variável a cada iteração
            $interest = round((float) $this->get_option($i . 'x'), 2);
            $label = sprintf('%dx de %s', $i, wp_strip_all_tags(wc_price($order_total / $i)));

            if (($this->get_option('installment_interest') == 'yes' || $this->get_option('installment_discount') == 'yes') && is_plugin_active('rede-for-woocommerce-pro/rede-for-woocommerce-pro.php')) {
                $customLabel = LknIntegrationRedeForWoocommerceHelper::lknIntegrationRedeProRedeInterest($order_total, $interest, $i, 'label', $this);
            }

            if (gettype($customLabel) === 'string' && $customLabel) {
                $label = $customLabel;
            }

            $has_interest_or_discount = (
                $this->get_option('installment_interest') === 'yes' ||
                $this->get_option('installment_discount') === 'yes'
            );

            $installments[] = array(
                'num'   => $i,
                'label' => $label,
            );
        }

        return $installments;
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
        wp_enqueue_style('woo-rede-style', $plugin_url . 'Public/css/rede/styleRedeCredit.css', array(), '1.0.0', 'all');

        wp_enqueue_script('woo-rede-js', $plugin_url . 'Public/js/creditCard/rede/wooRedeCredit.js', array(), '1.0.0', true);
        wp_localize_script('woo-rede-js', 'wooRedeVars', array(
            'debug' => defined('WP_DEBUG') && WP_DEBUG,
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('rede_payment_fields_nonce'),
        ));
        wp_enqueue_script('woo-rede-animated-card-jquery', $plugin_url . 'Public/js/jquery.card.js', array('jquery', 'woo-rede-js'), '2.5.0', true);


        apply_filters('integrationRedeSetCustomCSSPro', get_option('woocommerce_rede_credit_settings')['custom_css_short_code'] ?? false);
    }

    public function regOrderLogs($orderId, $order_total, $installments, $cardData, $transaction, $order, $brand = null): void
    {
        if ('yes' == $this->debug) {
            $tId = null;
            $returnCode = null;
            if ($brand === null && $transaction) {
                $brand = null;
                if (method_exists($transaction, 'getTid')) {
                    $tId = $transaction->getTid();
                }
                if (method_exists($transaction, 'getReturnCode')) {
                    $returnCode = $transaction->getReturnCode();
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
                'installments' => $installments,
                'cardData' => $cardData,
                'brand' => isset($tId) && isset($brand) ? $brand['brand'] : null,
                'returnCode' => isset($returnCode) ? $returnCode : null,
            );

            $bodyArray['cardData']['card_number'] = LknIntegrationRedeForWoocommerceHelper::censorString($bodyArray['cardData']['card_number'], 8);
            if (gettype($transaction) != 'string' && !is_null($transaction->getCardNumber())) {
                $transaction->setCardNumber(LknIntegrationRedeForWoocommerceHelper::censorString($transaction->getCardNumber(), 8));
            }

            $orderLogsArray = array(
                'body' => $bodyArray,
                'response' => $transaction
            );

            $orderLogs = json_encode($orderLogsArray);
            $order->update_meta_data('lknWcRedeOrderLogs', $orderLogs);
            $order->save();
        }
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
        $cardNumber = isset($_POST['rede_credit_number']) ?
            sanitize_text_field(wp_unslash($_POST['rede_credit_number'])) : '';

        $installments = isset($_POST['rede_credit_installments']) ?
            absint(sanitize_text_field(wp_unslash($_POST['rede_credit_installments']))) : 1;

        $creditExpiry = isset($_POST['rede_credit_expiry']) ? sanitize_text_field(wp_unslash($_POST['rede_credit_expiry'])) : '';

        if (strpos($creditExpiry, '/') !== false) {
            $expiration = explode('/', $creditExpiry);
        } else {
            $expiration = array(
                substr($creditExpiry, 0, 2),
                substr($creditExpiry, -2, 2),
            );
        }

        $cardData = array(
            'card_number' => preg_replace('/[^\d]/', '', sanitize_text_field(wp_unslash($_POST['rede_credit_number']))),
            'card_expiration_month' => sanitize_text_field($expiration[0]),
            'card_expiration_year' => $this->normalize_expiration_year(sanitize_text_field($expiration[1])),
            'card_cvv' => isset($_POST['rede_credit_cvc']) ? sanitize_text_field(wp_unslash($_POST['rede_credit_cvc'])) : '',
            'card_holder' => isset($_POST['rede_credit_holder_name']) ? sanitize_text_field(wp_unslash($_POST['rede_credit_holder_name'])) : '',
        );
        try {
            $valid = $this->validate_card_number($cardNumber);
            if (false === $valid) {
                throw new Exception(__('Please enter a valid credit card number', 'woo-rede'));
            }

            $valid = $this->validate_card_fields($_POST);
            if (false === $valid) {
                throw new Exception(__('One or more invalid fields', 'woo-rede'), 500);
            }

            $valid = $this->validate_installments($_POST, $order->get_total());
            if (false === $valid) {
                throw new Exception(__('Invalid number of installments', 'woo-rede'));
            }

            $orderId = $order->get_id();
            $interest = round((float) $this->get_option($installments . 'x'), 2);
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
                $transaction = $this->api->doTransactionCreditRequest($orderId + time(), $order_total, $installments, $cardData);
                $this->regOrderLogs($orderId, $order_total, $installments, $cardData, $transaction, $order);
            } catch (LknIntegrationRedeForWoocommerceTransactionException $e) {
                $additionalData = $e->getAdditionalData();
                $tid = $additionalData['tid'] ?? 'N/A';

                $brand = LknIntegrationRedeForWoocommerceHelper::getTransactionBrandDetails($tid, $this);

                $this->regOrderLogs($orderId, $order_total, $installments, $cardData, $e->getMessage(), $order, $brand);
                throw $e;
            } catch (Exception $e) {
                // Tratamento para outras exceções
                $this->regOrderLogs($orderId, $order_total, $installments, $cardData, $e->getMessage(), $order);
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

            // Removed update to internal meta key '_transaction_id'.
            $order->update_meta_data('_wc_rede_transaction_return_code', $transaction->getReturnCode());
            $order->update_meta_data('_wc_rede_transaction_return_message', $transaction->getReturnMessage());
            $order->update_meta_data('_wc_rede_transaction_installments', $installments);
            $order->update_meta_data('_wc_rede_transaction_id', $transaction->getTid());
            $order->update_meta_data('_wc_rede_transaction_refund_id', $transaction->getRefundId());
            $order->update_meta_data('_wc_rede_transaction_cancel_id', $transaction->getCancelId());
            $order->update_meta_data('_wc_rede_transaction_bin', $transaction->getCardBin());
            $order->update_meta_data('_wc_rede_transaction_last4', $transaction->getLast4());
            $order->update_meta_data('_wc_rede_transaction_brand', LknIntegrationRedeForWoocommerceHelper::getCardBrand($transaction->getTid(), $this));
            $order->update_meta_data('_wc_rede_transaction_nsu', $transaction->getNsu());
            $order->update_meta_data('_wc_rede_transaction_authorization_code', $transaction->getAuthorizationCode());
            $order->update_meta_data('_wc_rede_captured', $transaction->getCapture());
            $order->update_meta_data('_wc_rede_total_amount', $order->get_total());
            $order->update_meta_data('_wc_rede_total_amount_converted', $order_total);
            $order->update_meta_data('_wc_rede_total_amount_is_converted', $convert_to_brl_enabled ? true : false);
            $order->update_meta_data('_wc_rede_exchange_rate', $exchange_rate_value);
            $order->update_meta_data('_wc_rede_decimal_value', $decimals);

            $authorization = $transaction->getAuthorization();

            if (! is_null($authorization)) {
                $order->update_meta_data('_wc_rede_transaction_authorization_status', $authorization->getStatus());
            }

            $order->update_meta_data('_wc_rede_transaction_holder', $transaction->getCardHolderName());
            $order->update_meta_data('_wc_rede_transaction_expiration', sprintf('%02d/%d', $expiration[0], (int) ($expiration[1])));

            $order->update_meta_data('_wc_rede_transaction_holder', $transaction->getCardHolderName());

            $authorization = $transaction->getAuthorization();

            if (! is_null($authorization)) {
                $order->update_meta_data('_wc_rede_transaction_authorization_status', $authorization->getStatus());
            }

            $order->update_meta_data('_wc_rede_transaction_environment', $this->environment);

            $this->process_order_status($order, $transaction, '');

            $order->save();

            if ('yes' == $this->debug) {
                $tId = null;
                $returnCode = null;
                $brandDetails = null;
                if (method_exists($transaction, 'getTid')) {
                    $tId = $transaction->getTid();
                }
                if (method_exists($transaction, 'getReturnCode')) {
                    $returnCode = $transaction->getReturnCode();
                }
                if ($tId) {
                    $brandDetails = LknIntegrationRedeForWoocommerceHelper::getTransactionBrandDetails($tId, $this);
                }

                $this->log->log('info', $this->id, array(
                    'transaction' => $transaction,
                    'order' => array(
                        'orderId' => $orderId,
                        'amount' => $order_total,
                        'orderCurrency' => $order_currency,
                        'currencyConverted' => $convert_to_brl_enabled ? 'BRL' : null,
                        'exchangeRateValue' => $exchange_rate_value,
                        'status' => $order->get_status(),
                        'brand' => isset($brandDetails['brand']) ? $brandDetails['brand'] : null,
                        'returnCode' => isset($returnCode) ? $returnCode : null,
                    ),
                ));
            }
        } catch (Exception $e) {
            if ($e->getCode() == 63) {
                add_option('lknIntegrationRedeForWoocommerceSoftDescriptorErrorCredit', true);
                update_option('lknIntegrationRedeForWoocommerceSoftDescriptorErrorCredit', true);
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

    public function process_refund($order_id, $amount = 0, $reason = '')
    {
        $order = new WC_Order($order_id);
        if ($order->get_payment_method() === 'rede_credit') {
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
                            $transaction = $this->api->do_transaction_cancellation($tid, $amount);
                        }

                        update_post_meta($order_id, '_wc_rede_transaction_refund_id', $transaction->getRefundId());
                        if ($transaction->getCancelId() === null) {
                            update_post_meta($order_id, '_wc_rede_transaction_cancel_id', $transaction->getTid());
                        } else {
                            update_post_meta($order_id, '_wc_rede_transaction_cancel_id', $transaction->getCancelId());
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

        $session = null;
        $installments_number = 1;

        $wc_get_template(
            'creditCard/redePaymentCreditForm.php',
            array(
                'installments' => $this->getInstallments($order_total),
                'installments_number' => $installments_number,
            ),
            'woocommerce/rede/',
            LknIntegrationRedeForWoocommerceWcRede::getTemplatesPath()
        );
    }
    /**
     * Renderiza os campos de pagamento com total atualizado (para AJAX)
     */
    public function render_payment_fields_with_total($order_total = null): void
    {
        if ($description = $this->get_description()) {
            echo wp_kses_post(wpautop($description));
        }

        if ($order_total === null) {
            $order_total = $this->get_cart_subtotal_without_taxes();
        }

        $this->getCheckoutForm($order_total);
    }
}
