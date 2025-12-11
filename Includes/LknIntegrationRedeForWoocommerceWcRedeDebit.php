<?php

namespace Lknwoo\IntegrationRedeForWoocommerce\Includes;

use Exception;
use Lknwoo\IntegrationRedeForWoocommerce\Includes\LknIntegrationRedeForWoocommerceWcRedeAbstract;
use WC_Order;
use WP_Error;

final class LknIntegrationRedeForWoocommerceWcRedeDebit extends LknIntegrationRedeForWoocommerceWcRedeAbstract
{
    public function __construct()
    {
        $this->id = 'rede_debit';
        $this->has_fields = true;
        $this->method_title = esc_attr__('Pay with Rede Debit and Credit 3DS', 'woo-rede');
        $this->method_description = esc_attr__('Enables and configures payments with Rede Debit and Credit cards using 3D Secure', 'woo-rede');
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

        // Auto capture configurável igual ao rede_credit
        $this->auto_capture = sanitize_text_field($this->get_option('auto_capture')) == 'no' ? false : true;
        $this->max_parcels_number = $this->get_option('max_parcels_number');
        $this->min_parcels_value = $this->get_option('min_parcels_value');

        $this->partner_module = $this->get_option('module');
        $this->partner_gateway = $this->get_option('gateway');

        $this->enable_3ds = true; // 3DS sempre ativo para débito
        $this->threeds_fallback_behavior = $this->get_option('3ds_fallback_behavior', 'decline');

        $this->debug = $this->get_option('debug');

        $this->log = $this->get_logger();

        $this->configs = $this->getConfigsRedeDebit();
        
        // Hook para processar retorno do 3DS nas URLs simplificadas
        add_action('init', array($this, 'handle_3ds_return'));
        if (!has_action('woocommerce_init', array($this, 'show_3ds_error_message'))) {
            add_action('woocommerce_init', array($this, 'show_3ds_error_message'));
        }
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
            throw new Exception('Não foi possível obter token de autenticação OAuth2 para ' . esc_html($this->id));
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
        
        // Determinar se foi capturado baseado na resposta da transação
        $capture = $transaction_response['capture'] ?? true; // Default true pois debit sempre captura
        
        // Para transações credit processadas via debit gateway, verificar auto_capture
        $card_kind = $transaction_response['kind'] ?? 'debit';
        if ($card_kind === 'credit') {
            $capture = $transaction_response['capture'] ?? $this->auto_capture;
        }
        
        $status_note = sprintf('Rede[%s]', $return_message);
        $order->add_order_note($status_note . ' ' . $note);

        // Só altera o status se o pedido estiver pendente
        if ($order->get_status() === 'pending') {
            if ($return_code == '00') {
                if ($capture) {
                    // Status configurável pelo usuário para pagamentos aprovados com captura
                    $payment_complete_status = $this->get_option('payment_complete_status', 'processing');
                    $order->update_status($payment_complete_status);
                    apply_filters("integration_rede_for_woocommerce_change_order_status", $order, $this);
                } else {
                    // Para pagamentos credit sem captura, aguardando captura manual
                    $order->update_status('on-hold');
                    wc_reduce_stock_levels($order->get_id());
                }
            } else {
                $order->update_status('failed', $status_note);
            }
        }

        // Esvazia o carrinho apenas se disponível (contexto de checkout regular)
        if (function_exists('WC') && WC() && WC()->cart) {
            WC()->cart->empty_cart();
        }
    }

    /**
     * Processa transação de débito/crédito
     */
    private function process_debit_and_credit_transaction_v2($reference, $order_total, $cardData, $order = null)
    {
        $access_token = $this->get_oauth_token();
        
        $amount = str_replace(".", "", number_format($order_total, 2, '.', ''));
        
        if ($this->environment === 'production') {
            $apiUrl = 'https://api.userede.com.br/erede/v2/transactions';
        } else {
            $apiUrl = 'https://sandbox-erede.useredecloud.com.br/v2/transactions';
        }

        // Determinar o kind e capture baseado no tipo de cartão
        $card_type = isset($cardData['card_type']) ? $cardData['card_type'] : 'debit';
        $installments = isset($cardData['installments']) ? $cardData['installments'] : 1;
        
        // Auto capture condicional: sempre true para debit, configurável para credit
        $capture = ($card_type === 'debit') ? true : $this->auto_capture;

        $body = array(
            'capture' => $capture,
            'kind' => $card_type, // Dinâmico: 'debit' ou 'credit'
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
        
        // Adiciona parcelas apenas para crédito
        if ($card_type === 'credit' && $installments >= 1) {
            $body['installments'] = $installments;
        }
        
        // Add 3D Secure configuration if enabled (para débito e crédito)
        if ($this->enable_3ds) {
            
            // Determina o comportamento de fallback baseado no tipo de cartão
            $fallback_behavior = ($card_type === 'debit') ? 'decline' : $this->threeds_fallback_behavior;
            
            $body['threeDSecure'] = array(
                'embedded' => true, // Integração direta na página (true) ou redirecionamento (false)
                'onFailure' => $fallback_behavior, // Para débito: sempre 'decline'. Para crédito: configurável
                'device' => array(
                    'colorDepth' => 24, // Profundidade de cores do monitor. Aceita: 1, 4, 8, 15, 16, 24, 32, 48
                    'deviceType3ds' => 'BROWSER', // Tipo de dispositivo. Aceita: 'BROWSER', 'SDK'
                    'javaEnabled' => false, // Java habilitado no navegador. Aceita: true, false
                    'language' => 'pt-BR', // Idioma do navegador. Formato ISO 639-1: 'pt-BR', 'en-US', etc.
                    'screenHeight' => 500, // Altura da tela em pixels. Aceita: número inteiro
                    'screenWidth' => 500, // Largura da tela em pixels. Aceita: número inteiro
                    'timeZoneOffset' => 180 // Fuso horário em minutos vs UTC. Brasil (GMT-3), 3 * 60 = 180
                ),
            );
            
            // Add return URLs for 3DS authentication - URLs mais curtas para evitar erro de tamanho da API Rede
            $base_url = home_url('/');
            
            if ($order instanceof WC_Order) {
                // URLs super simplificadas para evitar limite de tamanho da API Rede
                $success_return_url = home_url('/wp-json/redeIntegration/s/?o=' . $order->get_id() . '&k=' . substr($order->get_order_key(), 0, 8) . '&r=' . substr($reference, -8));
                
                $failed_return_url = home_url('/wp-json/redeIntegration/f/?o=' . $order->get_id() . '&k=' . substr($order->get_order_key(), 0, 8));
            } else {
                // URLs de fallback simples
                $success_return_url = home_url('/wp-json/redeIntegration/s/');
                $failed_return_url = home_url('/wp-json/redeIntegration/f/');
            }

            $body['urls'] = array(
                array(
                    'kind' => 'threeDSecureSuccess', // URL de retorno em caso de autenticação bem-sucedida
                    'url' => $success_return_url
                ),
                array(
                    'kind' => 'threeDSecureFailure', // URL de retorno em caso de falha na autenticação
                    'url' => $failed_return_url
                )
            );
        }
        
        if ($this->get_option('enabled_soft_descriptor') === 'yes' && !empty($this->soft_descriptor)) {
            $body['softDescriptor'] = $this->soft_descriptor;
        }

        // Debug: Log complete payload being sent to API (SECURITY: mask sensitive data)
        $safe_body = $body;
        if (isset($safe_body['cardNumber'])) {
            $safe_body['cardNumber'] = '**** **** **** ' . substr($body['cardNumber'], -4);
        }
        if (isset($safe_body['securityCode'])) {
            $safe_body['securityCode'] = '***';
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
            throw new Exception('Erro na requisição: ' . esc_html($response->get_error_message()));
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_body, true);

        if ($response_code !== 200 && $response_code !== 201) {
            $error_message = 'Erro na transação';
            if (isset($response_data['returnMessage'])) {
                $error_message = $response_data['returnMessage'];
            } elseif (isset($response_data['errors']) && is_array($response_data['errors'])) {
                $error_message = implode(', ', $response_data['errors']);
            } elseif (isset($response_data['returnCode']) && $response_data['returnCode'] === '204') {
                // Cardholder not registered for 3DS - check fallback behavior baseado no tipo de cartão
                
                if ($card_type === 'debit') {
                    // Para débito, 3DS é sempre obrigatório - sempre decline
                    throw new Exception(esc_html(__('3D Secure authentication is mandatory for debit card transactions but is not available for this card. Transaction declined for regulatory compliance.', 'woo-rede')));
                } elseif ($this->threeds_fallback_behavior === 'decline') {
                    // Para crédito com fallback configurado como decline
                    throw new Exception(esc_html(__('3D Secure authentication failed and fallback is set to decline. Transaction declined.', 'woo-rede')));
                } else {
                    // Para crédito com fallback configurado como continue - ONLY for testing
                    return $this->retry_transaction_without_3ds($reference, $amount, $cardData);
                }
            }
            throw new Exception(esc_html($error_message));
        }
        
        // Handle 3DS authentication response
        if ($this->enable_3ds && isset($response_data['threeDSecure'])) {
            
            $threeDSecure = $response_data['threeDSecure'];

            // If 3DS authentication is required, redirect to authentication URL
            if (isset($threeDSecure['url']) && !empty($threeDSecure['url'])) {
                
                // Return special response for 3DS redirect
                return array(
                    'result' => '3ds_required',
                    'threeDSecure' => $threeDSecure,
                    'reference' => $reference
                );
            } else {
            }
        } else if ($this->enable_3ds) {
            // 3DS was enabled but response was processed directly (no challenge needed)
        }
        
        if (!isset($response_data['returnCode']) || $response_data['returnCode'] !== '00') {
            $error_message = isset($response_data['returnMessage']) ? $response_data['returnMessage'] : 'Transação recusada';
            throw new Exception(esc_html($error_message));
        }
        
        return $response_data;
    }

    /**
     * Retry transaction without 3DS when cardholder is not registered
     */
    private function retry_transaction_without_3ds($reference, $amount, $cardData)
    {
        $access_token = $this->get_oauth_token();
        
        if ($this->environment === 'production') {
            $apiUrl = 'https://api.userede.com.br/erede/v2/transactions';
        } else {
            $apiUrl = 'https://sandbox-erede.useredecloud.com.br/v2/transactions';
        }

        // Determinar o kind e capture baseado no tipo de cartão
        $card_type = isset($cardData['card_type']) ? $cardData['card_type'] : 'debit';
        $installments = isset($cardData['installments']) ? $cardData['installments'] : 1;
        
        // Auto capture condicional: sempre true para debit, configurável para credit
        $capture = ($card_type === 'debit') ? true : $this->auto_capture;
        
        $body = array(
            'capture' => $capture,
            'kind' => $card_type, // Dinâmico: 'debit' ou 'credit'
            'reference' => (string)$reference . '-no3ds',
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
        
        // Adiciona parcelas apenas para crédito
        if ($card_type === 'credit' && $installments > 1) {
            $body['installments'] = $installments;
        }
        
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
            throw new Exception('Erro na requisição (retry): ' . esc_html($response->get_error_message()));
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_body, true);
        
        if ($response_code !== 200 && $response_code !== 201) {
            $error_message = 'Erro na transação (retry)';
            if (isset($response_data['returnMessage'])) {
                $error_message = $response_data['returnMessage'];
            } elseif (isset($response_data['errors']) && is_array($response_data['errors'])) {
                $error_message = implode(', ', $response_data['errors']);
            }
            throw new Exception(esc_html($error_message));
        }
        
        if (!isset($response_data['returnCode']) || $response_data['returnCode'] !== '00') {
            $error_message = isset($response_data['returnMessage']) ? $response_data['returnMessage'] : 'Transação recusada (retry)';
            throw new Exception(esc_html($error_message));
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
                'cardType' => isset($cardData['card_type']) ? $cardData['card_type'] : 'debit',
                'installments' => (isset($cardData['card_type']) && $cardData['card_type'] === 'credit' && isset($cardData['installments'])) ? $cardData['installments'] : null,
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

    /**
     * Processa retorno do 3DS das URLs simplificadas
     */
    public function handle_3ds_return()
    {
        // Verifica se é um retorno do 3DS
        if (!isset($_GET['3ds']) || !isset($_GET['wc_order']) || !isset($_GET['key'])) {
            return;
        }

        $order_id = intval($_GET['wc_order']);
        $order_key = sanitize_text_field(wp_unslash($_GET['key']));
        $threeds_status = sanitize_text_field(wp_unslash($_GET['3ds']));

        // Valida o pedido
        $order = wc_get_order($order_id);
        if (!$order || $order->get_order_key() !== $order_key) {
            wp_die(esc_html(__('Invalid order or order key.', 'woo-rede')));
            return;
        }

        // Nota: O processamento real dos dados da transação agora é feito pelo webhook 3DS
        // Este método serve apenas para redirecionar o usuário de volta à loja
        
        // Adiciona nota sobre o retorno do 3DS
        if ($threeds_status === 'ok') {
            $order->add_order_note(__('Customer returned from 3D Secure authentication - Success', 'woo-rede'));
            // Redireciona para a página de confirmação em caso de sucesso
            $redirect_url = $order->get_checkout_order_received_url();
        } else {
            $order->add_order_note(__('Customer returned from 3D Secure authentication - Failure', 'woo-rede'));
            $order->update_status('failed');
            
            // Redireciona para a página de checkout em caso de falha
            $redirect_url = add_query_arg('3ds_error', '1', wc_get_checkout_url());
        }

        wp_safe_redirect($redirect_url);
        exit;
    }

    /**
     * Exibe mensagem de erro 3DS na página de checkout
     */
    public function show_3ds_error_message()
    {
        if (isset($_GET['3ds_error']) && $_GET['3ds_error'] == '1') {
            wc_add_notice(__('Payment failed during 3D Secure authentication. Please try again or use a different payment method.', 'woo-rede'), 'error');
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

    /**
     * Processa as opções administrativas e aplica validação PRO
     * 
     * @return bool
     */
    public function process_admin_options()
    {
        $saved = parent::process_admin_options();

        // Se a licença PRO não for válida, resetar campos PRO para valores padrão
        if (!LknIntegrationRedeForWoocommerceHelper::isProLicenseValid()) {
            $this->enforceProFieldDefaults();
        }

        return $saved;
    }

    /**
     * Força valores padrão para campos PRO se a licença não for válida
     */
    private function enforceProFieldDefaults(): void
    {
        $option_key = "woocommerce_{$this->id}_settings";
        $settings = get_option($option_key, array());

        // Campos PRO que devem ser resetados para valores padrão
        $pro_fields_defaults = array(
            'interest_or_discount' => 'interest',
            'interest_show_percent' => 'yes',
            'installment_interest' => 'no',
            'installment_discount' => 'no',
            'min_interest' => '0',
            'convert_to_brl' => 'no',
            'auto_capture' => 'yes',
            '3ds_template_style' => 'basic',
            'payment_complete_status' => 'processing'
        );

        // Reset campos de parcelas específicas
        $max_installments = (int) ($settings['max_parcels_number'] ?? 12);
        for ($i = 1; $i <= $max_installments; $i++) {
            $pro_fields_defaults["{$i}x"] = '0';
            $pro_fields_defaults["{$i}x_discount"] = '0';
        }

        // Aplica os valores padrão para campos PRO
        foreach ($pro_fields_defaults as $field => $default_value) {
            if (isset($settings[$field])) {
                $settings[$field] = $default_value;
            }
        }

        // Atualiza as configurações no banco
        update_option($option_key, $settings);

        // Adiciona uma notificação para o administrador
        add_action('admin_notices', function() {
            echo '<div class="notice notice-warning is-dismissible">';
            echo '<p>' . esc_html(__('PRO license is required to modify installment settings. Settings have been reset to default values.', 'woo-rede')) . '</p>';
            echo '</div>';;
        });
    }

    public function initFormFields(): void
    {
        LknIntegrationRedeForWoocommerceHelper::updateFixLoadScriptOption($this->id);

        // Verifica se a licença PRO é válida
        $isProValid = LknIntegrationRedeForWoocommerceHelper::isProLicenseValid();

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
                'description' => esc_attr__('Enable or disable the debit and credit card payment method.', 'woo-rede'),
                'desc_tip' => esc_attr__('Check this box and save to enable debit and credit card settings.', 'woo-rede'),
                'custom_attributes' => array(
                    'data-title-description' => esc_attr__('Enable this option to allow customers to pay with debit and credit cards using Rede API with 3D Secure.', 'woo-rede')
                )
            ),
            'title' => array(
                'title' => esc_attr__('Title', 'woo-rede'),
                'type' => 'text',
                'default' => esc_attr__('Pay with Rede Debit and Credit 3DS', 'woo-rede'),
                'description' => esc_attr__('This controls the title which the user sees during checkout.', 'woo-rede'),
                'desc_tip' => esc_attr__('Enter the title that will be shown to customers during the checkout process.', 'woo-rede'),
                'custom_attributes' => array(
                    'data-title-description' => esc_attr__('This text will appear as the payment method title during checkout. Choose something your customers will easily understand, like “Pay with debit and credit card (Rede 3DS)”.', 'woo-rede')
                )
            ),
            'description' => array(
                'title' => __('Description', 'woo-rede'),
                'type' => 'textarea',
                'default' => __('Pay for your purchase with a debit or credit card through Rede with 3D Secure authentication', 'woo-rede'),
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
                'title' => __('Enable Payment Description', 'woo-rede'),
                'type' => 'checkbox',
                'desc_tip' => esc_attr__('Send a custom payment description to Rede that appears on customer statements. Disable if it causes transaction errors.', 'woo-rede'),
                'description' => __('Enable payment descriptions in the', 'woo-rede') . ' ' . wp_kses_post('<a href="' . esc_url('https://meu.userede.com.br/ecommerce/identificacao-fatura') . '" target="_blank">' . __('Rede Dashboard', 'woo-rede') . '</a>') . ' ' . __('first', 'woo-rede'),
                'custom_attributes' => array(
                    'data-title-description' => esc_attr__('Allow custom payment descriptions to be sent to Rede for customer statement identification.', 'woo-rede')
                ),
                'label' => esc_attr__('Enable custom payment description feature for Rede transactions.', 'woo-rede'),
                'default' => 'no',
            ),

            'soft_descriptor' => array(
                'title' => esc_attr__('Payment Description Text', 'woo-rede'),
                'type' => 'text',
                'desc_tip' => esc_attr__('Enter the custom description (max 20 characters) that will appear on customer credit card statements.', 'woo-rede'),
                'description' => esc_attr__('Custom text displayed on customer statements (maximum 20 characters).', 'woo-rede'),
                'custom_attributes' => array(
                    'data-title-description' => esc_attr__('Custom identifier text for customer credit card statements.', 'woo-rede'),
                    'maxlength' => 20,
                    'merge-top' => "woocommerce_{$this->id}_enabled_soft_descriptor",
                ),
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
                'custom_attributes' => array_merge(array(
                    'data-title-description' => esc_attr__('Choose the status that approved payments should have. "Processing" is recommended for most cases.', 'woo-rede')
                ), !$isProValid ? array('lkn-is-pro' => 'true') : array())
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

            'card' => array(
                'title' => esc_attr__('Card', 'woo-rede'),
                'type' => 'title',
            ),

            'card_type_restriction' => array(
                'title' => esc_attr__('Card Type Restriction', 'woo-rede'),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'description' => esc_attr__('Choose which card types are accepted for payment. This setting controls whether customers can use credit cards, debit cards, or both.', 'woo-rede'),
                'desc_tip' => esc_attr__('Select the card types that will be accepted during payment processing. This helps control the payment flow based on your business needs.', 'woo-rede'),
                'default' => 'debit_only',
                'options' => array(
                    'debit_only' => esc_attr__('Debit Cards Only', 'woo-rede'),
                    'credit_only' => esc_attr__('Credit Cards Only', 'woo-rede'),
                    'both' => esc_attr__('Both Credit and Debit Cards', 'woo-rede'),
                ),
                'custom_attributes' => array(
                    'data-title-description' => esc_attr__('Control which card types customers can use for payment. Choose "Debit Only" for the current debit gateway configuration.', 'woo-rede')
                )
            ),

            'auto_capture' => array(
                'title' => esc_attr__('Auto Capture', 'woo-rede'),
                'label' => esc_attr__('Enable automatic capture for credit card transactions', 'woo-rede'),
                'type' => 'checkbox',
                'description' => esc_attr__('If disabled, payments will only be authorized and must be captured manually.', 'woo-rede'),
                'desc_tip' => esc_attr__('Allows the transaction to be captured after authentication automatically.', 'woo-rede'),
                'default' => 'yes',
                'custom_attributes' => array_merge(array(
                    'data-title-description' => esc_attr__("Automatically captures the payment once authorized by Rede.", 'woo-rede')
                ), !$isProValid ? array('lkn-is-pro' => 'true') : array()),
            ),

            '3ds_fallback_behavior' => array(
                'title' => esc_attr__('3DS Fallback Behavior (Credit Cards Only)', 'woo-rede'),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'description' => esc_attr__('This setting only applies to credit card transactions. For debit cards, 3DS authentication is ALWAYS mandatory and transactions will always be declined if 3DS fails. For credit cards, you can choose the fallback behavior when 3DS authentication is unavailable.', 'woo-rede'),
                'desc_tip' => esc_attr__('Debit cards: 3DS is mandatory (always decline if unavailable). Credit cards: You can choose to decline or continue without 3DS for testing purposes only.', 'woo-rede'),
                'default' => 'decline',
                'options' => array(
                    'decline' => esc_attr__('Decline transaction (RECOMMENDED for production)', 'woo-rede'),
                    'continue' => esc_attr__('Continue without 3DS (TESTING ONLY - NOT for production)', 'woo-rede'),
                ),
                'custom_attributes' => array(
                    'data-title-description' => esc_attr__('Credit card fallback behavior when 3DS is unavailable. Debit cards always require 3DS authentication and cannot be bypassed.', 'woo-rede')
                )
            ),

            '3ds_template_style' => array(
                'title' => esc_attr__('Template Style for Blocks Editor', 'woo-rede'),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'description' => esc_attr__('Choose the visual style for the 3D Secure authentication interface. The modern template provides an enhanced user experience with improved design and usability.', 'woo-rede'),
                'desc_tip' => esc_attr__('Select the template style that will be used during 3D Secure authentication. Modern template offers better visual appeal and user experience.', 'woo-rede'),
                'default' => 'basic',
                'options' => array(
                    'basic' => esc_attr__('Basic Template', 'woo-rede'),
                    'modern' => esc_attr__('Modern Template (PRO)', 'woo-rede'),
                ),
                'custom_attributes' => array_merge(array(
                    'data-title-description' => esc_attr__('Choose between basic and modern 3DS authentication templates. Modern template provides enhanced visual design and better user experience during payment authentication.', 'woo-rede')
                ), !$isProValid ? array('lkn-is-pro' => 'true') : array())
            ),

            'installment' => array(
                'title' => esc_attr__('Installments', 'woo-rede'),
                'type' => 'title',
            ),
            'min_parcels_value' => array(
                'title' => esc_attr__('Value of the smallest installment', 'woo-rede'),
                'type' => 'number',
                'default' => 5,
                'description' => esc_attr__('Set the minimum installment value for credit card payments. Accepted minimum value by REDE: 5.', 'woo-rede'),
                'desc_tip' => esc_attr__('Set the minimum allowed amount for each installment in credit transactions.', 'woo-rede'),
                'custom_attributes' => array(
                    'min' => 5,
                    'step' => 'any',
                    'data-title-description' => esc_attr__('Enter the minimum value each installment must have.', 'woo-rede')
                )
            ),
        );
        
        // Field to define maximum number of installments with dynamic options
        $parcels_options = array();
        for ($i = 1; $i <= 24; $i++) {
            // translators: %d is the number of installments
            $parcels_options[$i] = sprintf(__('%dx', 'woo-rede'), $i);
        }

        $this->form_fields['max_parcels_number'] = array(
            'title' => __('Maximum Number of Installments (Credit Cards Only)', 'woo-rede'),
            'type' => 'select',
            'desc_tip' => esc_attr__('Credit cards only - debit always uses single payment.', 'woo-rede'),
            'options' => $parcels_options,
            'custom_attributes' => array(
                'data-merge-top' => 'true',
                // translators: %d is the number of installments
                'data-title-description' => esc_attr__('Maximum number of installments available for credit card transactions. Debit cards are always processed in a single payment.', 'woo-rede')
            ),
            'description' => __('Select the maximum number of installments allowed for credit card payments (up to 24). This setting does not affect debit card transactions, which are always processed as single payments.', 'woo-rede'),
            'default' => '12',
        );
        
        $this->form_fields = array_merge($this->form_fields, array(
            'interest_or_discount' => array(
                'title' => esc_attr__('Installment Settings', 'woo-rede'),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'options' => array(
                    'interest' => __('Interest', 'woo-rede'),
                    'discount' => __('Discount', 'woo-rede'),
                ),
                'default' => 'interest',
                'desc_tip' => esc_attr__('Select the option interest or discount. Save to continue configuration.', 'woo-rede'),
                'description' => esc_attr__('Allows the user to select discount or interest on credit card installments.', 'woo-rede'),
                'custom_attributes' => array_merge(array(
                    'data-title-description' => esc_attr__("Defines whether the installment will apply interest or offer a discount. Save to load more settings.", 'woo-rede')
                ), !$isProValid ? array('lkn-is-pro' => 'true') : array()),
            ),
            'interest_show_percent' => array(
                'title' => __('Display interest percentage', 'woo-rede'),
                'label' => __('Display interest percentage.', 'woo-rede'),
                'type' => 'checkbox',
                'description' => __('By enabling this feature, the percentage applied to each installment will be displayed to the customer during checkout.', 'woo-rede'),
                'custom_attributes' => !$isProValid ? array('lkn-is-pro' => 'true') : array(),
                'default' => 'yes'
            ),
            'installment_interest' => array(
                'title' => __('Interest on installments', 'woo-rede'),
                'type' => 'checkbox',
                'description' => __('Enables payment with interest on installments.', 'woo-rede'),
                'default' => 'no',
                'desc_tip' => esc_attr__('Enable to allow interest to be charged on installment payments.', 'woo-rede'),
                'description' => esc_attr__('Allows payment with interest in installments. Save to continue configuration.', 'woo-rede'),
                'custom_attributes' => array_merge(array(
                    'data-title-description' => esc_attr__("Applies an interest rate to each installment. Use this if you want to charge extra per installment.", 'woo-rede')
                ), !$isProValid ? array('lkn-is-pro' => 'true') : array()),
            ),
            'installment_discount' => array(
                'title' => __('Discount on installments', 'woo-rede'),
                'type' => 'checkbox',
                'desc_tip' => esc_attr__('Enable to give a discount when the customer chooses to pay in installments.', 'woo-rede'),
                'description' => esc_attr__('Enables payment with discount on installments.', 'woo-rede'),
                'custom_attributes' => array_merge(array(
                    'data-title-description' => esc_attr__("Applies a discount per installment when selected. Useful to encourage multi-payment options.", 'woo-rede')
                ), !$isProValid ? array('lkn-is-pro' => 'true') : array()),
                'default' => 'no',
            )
        ));

        // Minimum interest field per transaction
        $this->form_fields['min_interest'] = array(
            'title' => __('Minimum Interest', 'woo-rede'),
            'type' => 'number',
            'default' => '0',
            'custom_attributes' => array_merge(array(
                'step' => '0.01',
                'min' => '0',
                'max' => '100',
                'merge-top' => "woocommerce_{$this->id}_installment_interest",
                'data-title-description' => esc_attr__('Minimum interest percentage that will be applied regardless of installment number.', 'woo-rede')
            ), !$isProValid ? array('lkn-is-pro' => 'true') : array()),
            'description' => __('Minimum interest percentage that will be applied regardless of installment number.', 'woo-rede'),
        );

        // Dynamic fields for each installment
        $max_installments = (int) $this->get_option('max_parcels_number', 12);
        for ($i = 1; $i <= $max_installments; $i++) {
            // Interest field for specific installment
            $this->form_fields["{$i}x"] = array(
                // translators: %d is the number of installments
                'title' => sprintf(__('Interest %dx', 'woo-rede'), $i),
                'type' => 'number',
                'default' => '0',

                'custom_attributes' => array_merge(array(
                    'step' => '0.01',
                    'min' => '0',
                    'max' => '100',
                    'merge-top' => "woocommerce_{$this->id}_installment_interest",
                    // translators: %d is the number of installments
                    'data-title-description' => sprintf(esc_attr__('Interest applied when customer selects to pay in %dx. Leave 0 for no interest.', 'woo-rede'), $i)
                ), !$isProValid ? array('lkn-is-pro' => 'true') : array()),
                'description' => __('This option defines the interest on the installment as a percentage. Only accepts numbers. For example, for 10% interest, enter 10. Leave it blank or enter zero for an installment without an interest rate.', 'woo-rede'),
            );

            // Discount field for specific installment  
            $this->form_fields["{$i}x_discount"] = array(
                // translators: %d is the number of installments
                'title' => sprintf(__('Discount %dx', 'woo-rede'), $i),
                'type' => 'number',
                'default' => '0',
                'custom_attributes' => array_merge(array(
                    'step' => '0.01',
                    'min' => '0',
                    'max' => '100',
                    'merge-top' => "woocommerce_{$this->id}_installment_discount",
                    // translators: %d is the number of installments
                    'data-title-description' => sprintf(esc_attr__('Discount applied when customer selects to pay in %dx. Leave 0 for no discount.', 'woo-rede'), $i)
                ), !$isProValid ? array('lkn-is-pro' => 'true') : array()),
                'description' => __('This option defines the discount on the installment as a percentage. Only accepts numbers. For example, for 10% discount, enter 10. Leave it blank or enter zero for an installment without a discount rate.', 'woo-rede'),
            );
        }

        $this->form_fields['developers'] = array(
            'title' => esc_attr__('Developer', 'woo-rede'),
            'type' => 'title',
        );

        $this->form_fields['debug'] = array(
            'title' => esc_attr__('Debug', 'woo-rede'),
            'type' => 'checkbox',
            'label' => esc_attr__('Enable debug logs.', 'woo-rede') . ' ' . wp_kses_post('<a href="' . esc_url(admin_url('admin.php?page=wc-status&tab=logs')) . '" target="_blank">' . __('See logs', 'woo-rede') . '</a>'),
            'default' => 'no',
            'desc_tip' => esc_attr__('Enable transaction logging.', 'woo-rede'),
            'description' => esc_attr__('Enable this option to log payment requests and responses for troubleshooting purposes.', 'woo-rede'),
            'custom_attributes' => array(
                'data-title-description' => esc_attr__("When enabled, all Rede transactions will be logged.", 'woo-rede')
            ),
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

        $customConfigs = apply_filters('integration_rede_for_woocommerce_get_custom_configs', $this->form_fields, array(), $this->id);

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
        
        // Enfileira CSS do template moderno apenas se PRO estiver ativo e template configurado como modern
        if (LknIntegrationRedeForWoocommerceHelper::isProLicenseValid() && $this->get_option('3ds_template_style') === 'modern') {
            wp_enqueue_style('lknwoo-modern-template', $plugin_url . 'Public/css/rede/LknIntegrationRedeForWoocommerceModernTemplate.css', array(), '1.0.0', 'all');
        }

        wp_enqueue_style('wooRedeDebit-style', $plugin_url . 'Public/css/rede/styleRedeDebit.css', array(), '1.0.0', 'all');

        wp_enqueue_script('wooRedeDebit-js', $plugin_url . 'Public/js/debitCard/rede/wooRedeDebit.js', array(), '1.0.0', true);
        wp_enqueue_script('woo-rede-animated-card-jquery', $plugin_url . 'Public/js/jquery.card.js', array('jquery', 'wooRedeDebit-js'), '2.5.0', true);

        wp_localize_script('wooRedeDebit-js', 'wooRedeDebit', array(
            'debug' => defined('WP_DEBUG') && WP_DEBUG,
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('rede_debit_payment_fields_nonce'),
        ));

        apply_filters('integration_rede_for_woocommerce_set_custom_css', get_option('woocommerce_rede_debit_settings')['custom_css_short_code'] ?? false);
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

        // Captura o tipo de cartão selecionado
        $card_type = isset($_POST['rede_debit_card_type']) ? sanitize_text_field(wp_unslash($_POST['rede_debit_card_type'])) : 'debit';
        
        // Captura o número de parcelas (apenas para crédito)
        $installments = 1;
        if ($card_type === 'credit' && isset($_POST['rede_debit_installments'])) {
            $installments = intval(sanitize_text_field(wp_unslash($_POST['rede_debit_installments'])));
            if ($installments < 1) $installments = 1;
        }

        $cardData = array(
            'card_number' => preg_replace('/[^\d]/', '', sanitize_text_field(wp_unslash($_POST['rede_debit_number']))),
            'card_expiration_month' => sanitize_text_field($expiration[0]),
            'card_expiration_year' => $this->normalize_expiration_year(sanitize_text_field($expiration[1])),
            'card_cvv' => isset($_POST['rede_debit_cvc']) ? sanitize_text_field(wp_unslash($_POST['rede_debit_cvc'])) : '',
            'card_holder' => isset($_POST['rede_debit_holder_name']) ? sanitize_text_field(wp_unslash($_POST['rede_debit_holder_name'])) : '',
            'card_type' => $card_type,
            'installments' => $installments,
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
                // Salva metadados do cartão antes de enviar a transação (para recuperar no webhook)
                $order->update_meta_data('_wc_rede_card_type', $card_type);
                $order->update_meta_data('_wc_rede_installments', $installments);
                $order->save();
                
                $transaction_response = $this->process_debit_and_credit_transaction_v2($orderId . '-' . time(), $order_total, $cardData, $order);
                
                // Handle 3DS authentication requirement
                if (is_array($transaction_response) && isset($transaction_response['result']) && $transaction_response['result'] === '3ds_required') {
                    
                    // Add order note
                    $order->add_order_note(__('3D Secure authentication required. Customer redirected to bank authentication.', 'woo-rede'));
                    
                    return array(
                        'result' => 'success',
                        'redirect' => $transaction_response['threeDSecure']['url']
                    );
                }
                
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
            $order->update_meta_data('_wc_rede_captured', $transaction_response['capture'] ?? (isset($cardData['card_type']) && $cardData['card_type'] === 'debit' ? true : $this->auto_capture));
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
                        'cardType' => isset($cardData['card_type']) ? $cardData['card_type'] : 'debit',
                        'installments' => (isset($cardData['card_type']) && $cardData['card_type'] === 'credit' && isset($cardData['installments'])) ? $cardData['installments'] : null,
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
            throw new Exception('Erro na requisição de reembolso: ' . esc_html($response->get_error_message()));
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
            throw new Exception(esc_html($error_message));
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

    /**
     * Gera opções de parcelas
     */
    public function getInstallments($order_total = 0)
    {
        $installments = array();
        $card_type_restriction = $this->get_option('card_type_restriction', 'debit_only');
        
        // Só gera parcelas se permitir crédito
        if ($card_type_restriction === 'credit_only' || $card_type_restriction === 'both') {
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
                    $product_limit = get_post_meta($product_id, 'lknRedeProdutctInterest', true);
                    
                    if ($product_limit !== 'default' && is_numeric($product_limit)) {
                        $product_limit = (int) $product_limit;
                        if ($product_limit > 0 && $product_limit < $max_parcels) {
                            $max_parcels = $product_limit;
                        }
                    }
                }
            }

            for ($i = 1; $i <= $max_parcels; ++$i) {
                // Para 1x à vista, sempre permite mesmo se for menor que o valor mínimo
                if ($i === 1 || ($order_total / $i) >= $min_value) {
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
            }
        }
        
        return $installments;
    }

    protected function getCheckoutForm($order_total = 0): void
    {
        $wc_get_template = 'woocommerce_get_template';

        if (function_exists('wc_get_template')) {
            $wc_get_template = 'wc_get_template';
        }

        $session = null;
        // Buscar valor da sessão ao invés de fixar em 1
        $installments_number = 1;
        $card_type = 'debit'; // Valor padrão
        if (function_exists('WC') && WC()->session) {
            // Buscar tipo de cartão da sessão primeiro
            $session_card_type = WC()->session->get('lkn_card_type_rede_debit');
            if (!empty($session_card_type)) {
                $card_type = $session_card_type;
            }
            
            // Buscar parcelas da sessão, mas forçar 1 para débito
            if ($card_type === 'debit') {
                $installments_number = 1;
            } else {
                $session_value = WC()->session->get('lkn_installments_number_rede_debit');
                if (!empty($session_value) && is_numeric($session_value) && $session_value > 0) {
                    $installments_number = intval($session_value);
                }
            }
        }

        $wc_get_template(
            'debitCard/redePaymentDebitForm.php',
            array(
                'installments' => $this->getInstallments($order_total),
                'installments_number' => $installments_number,
                'card_type_restriction' => $this->get_option('card_type_restriction', 'debit_only'),
                'card_type' => $card_type,
            ),
            'woocommerce/rede/',
            LknIntegrationRedeForWoocommerceWcRede::getTemplatesPath()
        );
    }
}
