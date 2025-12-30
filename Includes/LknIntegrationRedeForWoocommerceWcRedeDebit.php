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
        $this->method_title = 'Pagar com Cartão Débito e Crédito 3DS Rede';
        $this->method_description = 'Habilita e configura pagamentos com cartões Débito e Crédito Rede usando 3D Secure';
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
            wc_add_notice('Número do cartão é um campo obrigatório', 'error');

            return false;
        }

        if (empty($_POST['rede_debit_expiry'])) {
            wc_add_notice('Vencimento do cartão é um campo obrigatório', 'error');

            return false;
        }

        if (empty($_POST['rede_debit_cvc'])) {
            wc_add_notice('Código de segurança do cartão é um campo obrigatório', 'error');

            return false;
        }

        if (! ctype_digit(sanitize_text_field(wp_unslash($_POST['rede_debit_cvc'])))) {
            wc_add_notice('Código de segurança do cartão deve ser um valor numérico', 'error');
            return false;
        }

        if (strlen(sanitize_text_field(wp_unslash($_POST['rede_debit_cvc']))) < 3) {
            wc_add_notice('Código de segurança do cartão deve ter pelo menos 3 dígitos', 'error');
            return false;
        }

        if (empty($_POST['rede_debit_holder_name'])) {
            wc_add_notice('Nome do portador do cartão é um campo obrigatório', 'error');

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
                    'timeZoneOffset' => 3 // Fuso horário em horas.
                ),
            );
            
            // URLs simplificadas - não precisamos dos parâmetros pois os dados vêm no webhook
            $success_return_url = home_url('/wp-json/woorede/s/');
            $failed_return_url = home_url('/wp-json/woorede/f/');

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
                    throw new Exception('Autenticação 3D Secure é obrigatória para transações de cartão de débito, mas não está disponível para este cartão. Transação recusada para conformidade regulatória.');
                } elseif ($this->threeds_fallback_behavior === 'decline') {
                    // Para crédito com fallback configurado como decline
                    throw new Exception('Autenticação 3D Secure falhou e o comportamento de fallback está definido para recusar. Transação recusada.');
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
            wp_die('Pedido ou chave de pedido inválidos.');
            return;
        }

        // Nota: O processamento real dos dados da transação agora é feito pelo webhook 3DS
        // Este método serve apenas para redirecionar o usuário de volta à loja
        
        // Adiciona nota sobre o retorno do 3DS
        if ($threeds_status === 'ok') {
            $order->add_order_note('Cliente retornou da autenticação 3D Secure - Sucesso');
            // Redireciona para a página de confirmação em caso de sucesso
            $redirect_url = $order->get_checkout_order_received_url();
        } else {
            $order->add_order_note('Cliente retornou da autenticação 3D Secure - Falha');
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
            wc_add_notice('Pagamento falhou durante a autenticação 3D Secure. Tente novamente ou use um método de pagamento diferente.', 'error');
        }
    }

    public function displayMeta($order): void
    {
        if ($order->get_payment_method() === 'rede_debit') {
            $metaKeys = array(
                '_wc_rede_transaction_environment' => 'Ambiente',
                '_wc_rede_transaction_return_code' => 'Código de Retorno',
                '_wc_rede_transaction_return_message' => 'Mensagem de Retorno',
                '_wc_rede_transaction_id' => 'ID da Transação',
                '_wc_rede_transaction_refund_id' => 'ID do Reembolso',
                '_wc_rede_transaction_cancel_id' => 'ID do Cancelamento',
                '_wc_rede_transaction_nsu' => 'NSU',
                '_wc_rede_transaction_authorization_code' => 'Código de Autorização',
                '_wc_rede_transaction_bin' => 'BIN',
                '_wc_rede_transaction_last4' => 'Últimos 4 Dígitos',
                '_wc_rede_transaction_holder' => 'Portador do Cartão',
                '_wc_rede_transaction_expiration' => 'Vencimento do Cartão'
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
        // Aplicar validação personalizada antes de salvar
        $this->validate_min_parcels_value();

        $saved = parent::process_admin_options();

        // Se a licença PRO não for válida, forçar valores padrão após o salvamento
        if (!LknIntegrationRedeForWoocommerceHelper::isProLicenseValid()) {
            
            $option_key = "woocommerce_{$this->id}_settings";
            $current_settings = get_option($option_key, array());
            
            // Campos PRO que devem ser resetados para valores padrão
            $pro_fields_defaults = array(
                'interest_or_discount' => 'interest',
                'interest_show_percent' => 'yes',
                'installment_interest' => 'no',
                'installment_discount' => 'no',
                'convert_to_brl' => 'no',
                'auto_capture' => 'yes',
                '3ds_template_style' => 'basic',
                'payment_complete_status' => 'processing'
            );
            
            // Forçar campos PRO básicos para valores padrão
            foreach ($pro_fields_defaults as $field => $default_value) {
                $form_field_name = "woocommerce_{$this->id}_{$field}";
                
                // Só modifica $_POST se o campo está sendo enviado
                if (isset($_POST[$form_field_name])) {
                    $_POST[$form_field_name] = $default_value;
                }
                
                // Forçar no banco de dados
                $current_settings[$field] = $default_value;
            }
            
            // Forçar campos de parcelas para valores padrão
            $max_installments = (int) ($current_settings['max_parcels_number'] ?? 12);
            
            for ($i = 1; $i <= $max_installments; $i++) {
                $installment_form_field = "woocommerce_{$this->id}_{$i}x";
                $discount_form_field = "woocommerce_{$this->id}_{$i}x_discount";
                
                // Só modifica $_POST se o campo está sendo enviado
                if (isset($_POST[$installment_form_field])) {
                    $_POST[$installment_form_field] = '0';
                }
                if (isset($_POST[$discount_form_field])) {
                    $_POST[$discount_form_field] = '0';
                }
                
                // Forçar no banco de dados
                $current_settings["{$i}x"] = '0';
                $current_settings["{$i}x_discount"] = '0';
            }
            
            // Atualizar as configurações no banco
            update_option($option_key, $current_settings);
        }

        return $saved;
    }

    /**
     * Valida o valor mínimo de parcelas
     */
    private function validate_min_parcels_value(): void
    {
        if (isset($_POST['woocommerce_rede_debit_min_parcels_value'])) {
            $min_parcels_value = sanitize_text_field(wp_unslash($_POST['woocommerce_rede_debit_min_parcels_value']));
            
            // Converter para float para validar se é numérico
            $numeric_value = floatval($min_parcels_value);
            
            // Validar se é um número válido e maior ou igual a 5
            if (empty($min_parcels_value) || !is_numeric($min_parcels_value) || $numeric_value < 5) {
                // Forçar valor para 5 se for inválido
                $_POST['woocommerce_rede_debit_min_parcels_value'] = '5';
                
                // Adicionar notificação de erro/aviso para o administrador
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-warning is-dismissible">';
                    echo '<p><strong>Rede Débito/Crédito:</strong> O valor mínimo de parcelas deve ser um número maior ou igual a 5. O valor foi ajustado automaticamente para 5.</p>';
                    echo '</div>';
                });
            } else {
                // Se for um número válido >= 5, garantir que seja um inteiro
                $int_value = (int) $numeric_value;
                if ($int_value >= 5) {
                    $_POST['woocommerce_rede_debit_min_parcels_value'] = (string) $int_value;
                } else {
                    $_POST['woocommerce_rede_debit_min_parcels_value'] = '5';
                    
                    add_action('admin_notices', function() {
                        echo '<div class="notice notice-warning is-dismissible">';
                        echo '<p><strong>Rede Débito/Crédito:</strong> O valor mínimo de parcelas deve ser maior ou igual a 5. O valor foi ajustado automaticamente para 5.</p>';
                        echo '</div>';
                    });
                }
            }
        }
    }

    public function initFormFields(): void
    {
        LknIntegrationRedeForWoocommerceHelper::updateFixLoadScriptOption($this->id);

        // Verifica se a licença PRO é válida
        $isProValid = LknIntegrationRedeForWoocommerceHelper::isProLicenseValid();

        $this->form_fields = array(
            'rede' => array(
                'title' => 'Geral',
                'type' => 'title',
            ),
            'enabled' => array(
                'title' => 'Habilitar/Desabilitar',
                'type' => 'checkbox',
                'label' => 'Habilita pagamento com Rede',
                'default' => 'no',
                'description' => 'Habilitar ou desabilitar o método de pagamento com cartão de débito e crédito.',
                'desc_tip' => 'Marque esta caixa e salve para habilitar as configurações de cartão de débito e crédito.',
                'custom_attributes' => array(
                    'data-title-description' => 'Habilite esta opção para permitir que os clientes paguem com cartões de débito e crédito usando a API Rede com 3D Secure.'
                )
            ),
            'title' => array(
                'title' => 'Título',
                'type' => 'text',
                'default' => 'Pagar com Cartão Débito e Crédito 3DS Rede',
                'description' => 'Isto controla o título que o usuário vê durante o checkout.',
                'desc_tip' => 'Digite o título que será exibido aos clientes durante o processo de checkout.',
                'custom_attributes' => array(
                    'data-title-description' => esc_attr__('This text will appear as the payment method title during checkout. Choose something your customers will easily understand, like “Pay with debit and credit card (Rede 3DS)”.', 'woo-rede')
                )
            ),
            'description' => array(
                'title' => 'Descrição',
                'type' => 'textarea',
                'default' => 'Pague sua compra com cartão de débito ou crédito através da Rede com autenticação 3D Secure',
                'desc_tip' => 'Esta descrição aparece abaixo do título do método de pagamento no checkout. Use para informar seus clientes sobre os detalhes do processamento do pagamento.',
                'description' => 'Descrição do método de pagamento que o cliente verá no seu checkout.',
                'custom_attributes' => array(
                    'data-title-description' => esc_attr__('Provide a brief message that informs the customer how the payment will be processed. For example: “Your payment will be securely processed by Rede.”', 'woo-rede')
                )
            ),
            'environment' => array(
                'title' => 'Ambiente',
                'type' => 'select',
                'desc_tip' => 'Escolha entre modo de produção ou desenvolvimento para a API da Rede.',
                'description' => 'Escolha o ambiente',
                'custom_attributes' => array(
                    'data-title-description' => esc_attr__('Select "Tests" to test transactions in sandbox mode. Use "Production" for real transactions.”', 'woo-rede')
                ),
                'class' => 'wc-enhanced-select',
                'default' => 'test',
                'options' => array(
                    'test' => 'Testes',
                    'production' => 'Produção',
                ),
            ),
            'pv' => array(
                'title' => 'PV',
                'type' => 'password',
                'desc_tip' => 'Seu PV da Rede (número de afiliação).',
                'description' => esc_attr__('Rede credentials.', 'woo-rede'),
                'custom_attributes' => array(
                    'data-title-description' => 'Seu PV da Rede (número de afiliação) deve ser fornecido aqui.'
                ),
                'default' => $options['pv'] ?? '',
            ),
            'token' => array(
                'title' => 'Token',
                'type' => 'password',
                'desc_tip' => 'Seu Token da Rede.',
                'description' => 'Credenciais da Rede.',
                'custom_attributes' => array(
                    'data-title-description' => 'Seu Token da Rede deve ser colocado aqui.'
                ),
                'default' => $options['token'] ?? '',
            ),

            'enabled_soft_descriptor' => array(
                'title' => 'Habilitar Descrição de Pagamento',
                'type' => 'checkbox',
                'desc_tip' => 'Envie uma descrição de pagamento personalizada para a Rede que aparece nos extratos do cliente. Desabilite se causar erros na transação.',
                'description' => 'Habilite descrições de pagamento no' . ' ' . wp_kses_post('<a href="' . esc_url('https://meu.userede.com.br/ecommerce/identificacao-fatura') . '" target="_blank">' . 'Painel Rede' . '</a>') . ' ' . 'primeiro',
                'custom_attributes' => array(
                    'data-title-description' => 'Permite que descrições de pagamento personalizadas sejam enviadas à Rede para identificação no extrato do cliente.'
                ),
                'label' => 'Habilitar recurso de descrição de pagamento personalizada para transações Rede.',
                'default' => 'no',
            ),

            'soft_descriptor' => array(
                'title' => 'Texto da Descrição de Pagamento',
                'type' => 'text',
                'desc_tip' => 'Digite a descrição personalizada (máx. 20 caracteres) que aparecerá nos extratos do cartão de crédito do cliente.',
                'description' => 'Texto personalizado exibido nos extratos do cliente (máximo 20 caracteres).',
                'custom_attributes' => array(
                    'data-title-description' => 'Texto identificador personalizado para extratos de cartão de crédito do cliente.',
                    'maxlength' => 20,
                    'merge-top' => "woocommerce_{$this->id}_enabled_soft_descriptor",
                ),
            ),

            'payment_complete_status' => array(
                'title' => 'Status de Pagamento Completo',
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'description' => 'Escolha que status definir para pedidos após pagamento bem-sucedido.',
                'desc_tip' => 'Selecione o status do pedido que será aplicado quando o pagamento for processado com sucesso.',
                'default' => 'processing',
                'options' => array(
                    'processing' => 'Processando',
                    'completed' => 'Completo',
                    'on-hold' => 'Aguardando',
                ),
                'custom_attributes' => array_merge(array(
                    'data-title-description' => 'Escolha o status que os pagamentos aprovados devem ter. "Processando" é recomendado para a maioria dos casos.'
                ), !$isProValid ? array('lkn-is-pro' => 'true') : array())
            ),

            'enabled_fix_load_script' => array(
                'title' => 'Carregar no checkout',
                'type' => 'checkbox',
                'desc_tip' => 'Desabilite para carregar o plugin durante o checkout. Habilite para evitar erros de carregamento infinito.',
                'description' => 'Controla o carregamento do plugin na página de checkout.',
                'custom_attributes' => array(
                    'data-title-description' => 'Este recurso controla o carregamento do plugin na página de checkout. Está habilitado por padrão para evitar erros de carregamento infinito e só deve ser desabilitado se você estiver enfrentando problemas com o gateway.'
                ),
                'label' => 'Carregar plugin no checkout. Padrão (habilitado)',
                'default' => 'yes',
            ),

            'card' => array(
                'title' => 'Cartão',
                'type' => 'title',
            ),

            'card_type_restriction' => array(
                'title' => 'Restrição de Tipo de Cartão',
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'description' => 'Escolha quais tipos de cartão são aceitos para pagamento. Esta configuração controla se os clientes podem usar cartões de crédito, cartões de débito ou ambos.',
                'desc_tip' => 'Selecione os tipos de cartão que serão aceitos durante o processamento do pagamento. Isso ajuda a controlar o fluxo de pagamento com base nas necessidades do seu negócio.',
                'default' => 'debit_only',
                'options' => array(
                    'debit_only' => 'Apenas Cartões de Débito',
                    'credit_only' => 'Apenas Cartões de Crédito',
                    'both' => 'Cartões de Crédito e Débito',
                ),
                'custom_attributes' => array(
                    'data-title-description' => 'Controle quais tipos de cartão os clientes podem usar para pagamento. Escolha "Apenas Débito" para a configuração atual do gateway de débito.'
                )
            ),

            'auto_capture' => array(
                'title' => 'Captura Automática',
                'label' => 'Habilitar captura automática para transações de cartão de crédito',
                'type' => 'checkbox',
                'description' => 'Se desabilitado, os pagamentos serão apenas autorizados e devem ser capturados manualmente.',
                'desc_tip' => 'Permite que a transação seja capturada após a autenticação automaticamente.',
                'default' => 'yes',
                'custom_attributes' => array_merge(array(
                    'data-title-description' => 'Captura automaticamente o pagamento uma vez autorizado pela Rede.'
                ), !$isProValid ? array('lkn-is-pro' => 'true') : array()),
            ),

            '3ds_fallback_behavior' => array(
                'title' => 'Comportamento de Fallback 3DS (Apenas Cartões de Crédito)',
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'description' => 'Esta configuração se aplica apenas a transações de cartão de crédito. Para cartões de débito, a autenticação 3DS é SEMPRE obrigatória e as transações sempre serão recusadas se o 3DS falhar. Para cartões de crédito, você pode escolher o comportamento de fallback quando a autenticação 3DS não estiver disponível.',
                'desc_tip' => 'Cartões de débito: 3DS é obrigatório (sempre recusar se indisponível). Cartões de crédito: Você pode escolher recusar ou continuar sem 3DS apenas para fins de teste.',
                'default' => 'decline',
                'options' => array(
                    'decline' => 'Recusar transação (RECOMENDADO para produção)',
                    'continue' => 'Continuar sem 3DS (APENAS TESTES - NÃO para produção)',
                ),
                'custom_attributes' => array(
                    'data-title-description' => 'Comportamento de fallback do cartão de crédito quando o 3DS não está disponível. Cartões de débito sempre exigem autenticação 3DS e não podem ser contornados.'
                )
            ),

            '3ds_template_style' => array(
                'title' => 'Estilo de Template para Editor de Blocos',
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'description' => 'Escolha o estilo visual para a interface de autenticação 3D Secure. O template moderno oferece uma experiência de usuário aprimorada com design e usabilidade melhorados.',
                'desc_tip' => 'Selecione o estilo de template que será usado durante a autenticação 3D Secure. O template moderno oferece melhor apelo visual e experiência do usuário.',
                'default' => 'basic',
                'options' => array(
                    'basic' => 'Template Básico',
                    'modern' => 'Template Moderno (PRO)',
                ),
                'custom_attributes' => array_merge(array(
                    'data-title-description' => 'Escolha entre templates de autenticação 3DS básico e moderno. O template moderno oferece design visual aprimorado e melhor experiência do usuário durante a autenticação de pagamento.'
                ), !$isProValid ? array('lkn-is-pro' => 'true') : array())
            ),

            'installment' => array(
                'title' => 'Parcelamento',
                'type' => 'title',
            ),
            'min_parcels_value' => array(
                'title' => 'Valor da menor parcela',
                'type' => 'number',
                'default' => 5,
                'description' => 'Defina o valor mínimo da parcela para pagamentos com cartão de crédito. Valor mínimo aceito pela REDE: 5.',
                'desc_tip' => 'Defina o valor mínimo permitido para cada parcela em transações de crédito.',
                'custom_attributes' => array(
                    'min' => 5,
                    'step' => 'any',
                    'data-title-description' => 'Digite o valor mínimo que cada parcela deve ter.'
                )
            ),
        );
        
        // Field to define maximum number of installments with dynamic options
        $parcels_options = array();
        for ($i = 1; $i <= 24; $i++) {
            // translators: %d is the number of installments
            $parcels_options[$i] = sprintf('%dx', $i);
        }

        $this->form_fields['max_parcels_number'] = array(
            'title' => 'Número Máximo de Parcelas (Apenas Cartões de Crédito)',
            'type' => 'select',
            'desc_tip' => 'Apenas cartões de crédito - débito sempre usa pagamento único.',
            'options' => $parcels_options,
            'custom_attributes' => array(
                'data-merge-top' => 'true',
                // translators: %d is the number of installments
                'data-title-description' => 'Número máximo de parcelas disponíveis para transações de cartão de crédito. Cartões de débito são sempre processados em um único pagamento.'
            ),
            'description' => 'Selecione o número máximo de parcelas permitidas para pagamentos com cartão de crédito (até 24). Esta configuração não afeta transações de cartão de débito, que são sempre processadas como pagamentos únicos.',
            'default' => '12',
        );
        
        $this->form_fields = array_merge($this->form_fields, array(
            'interest_or_discount' => array(
                'title' => 'Configurações de Parcelamento',
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'options' => array(
                    'interest' => 'Juros',
                    'discount' => 'Desconto',
                ),
                'default' => 'interest',
                'desc_tip' => 'Selecione a opção juros ou desconto. Salve para continuar a configuração.',
                'description' => 'Permite ao usuário selecionar desconto ou juros no parcelamento do cartão de crédito.',
                'custom_attributes' => array_merge(array(
                    'data-title-description' => 'Define se o parcelamento aplicará juros ou oferecerá desconto. Salve para carregar mais configurações.'
                ), !$isProValid ? array('lkn-is-pro' => 'true') : array()),
            ),
            'interest_show_percent' => array(
                'title' => 'Exibir porcentagem de juros',
                'label' => 'Exibir porcentagem de juros.',
                'type' => 'checkbox',
                'description' => 'Ao habilitar este recurso, a porcentagem aplicada a cada parcela será exibida ao cliente durante o checkout.',
                'custom_attributes' => !$isProValid ? array('lkn-is-pro' => 'true') : array(),
                'default' => 'yes'
            ),
            'installment_interest' => array(
                'title' => 'Juros no parcelamento',
                'type' => 'checkbox',
                'description' => 'Habilita pagamento com juros no parcelamento.',
                'default' => 'no',
                'desc_tip' => 'Habilite para permitir que juros sejam cobrados nos pagamentos parcelados.',
                'description' => 'Permite pagamento com juros no parcelamento. Salve para continuar a configuração.',
                'custom_attributes' => array_merge(array(
                    'data-title-description' => 'Aplica uma taxa de juros a cada parcela. Use isso se quiser cobrar extra por parcela.'
                ), !$isProValid ? array('lkn-is-pro' => 'true') : array()),
            ),
            'installment_discount' => array(
                'title' => 'Desconto no parcelamento',
                'type' => 'checkbox',
                'desc_tip' => 'Habilite para dar desconto quando o cliente escolher pagar em parcelas.',
                'description' => 'Habilita pagamento com desconto no parcelamento.',
                'custom_attributes' => array_merge(array(
                    'data-title-description' => 'Aplica um desconto por parcela quando selecionado. Útil para incentivar opções de pagamento múltiplo.'
                ), !$isProValid ? array('lkn-is-pro' => 'true') : array()),
                'default' => 'no',
            )
        ));

        // Minimum interest field per transaction
        $this->form_fields['min_interest'] = array(
            'title' => 'Juros Mínimo',
            'type' => 'number',
            'default' => '0',
            'custom_attributes' => array_merge(array(
                'step' => '0.01',
                'min' => '0',
                'max' => '100',
                'merge-top' => "woocommerce_{$this->id}_installment_interest",
                'data-title-description' => 'Porcentagem de juros mínima que será aplicada independentemente do número de parcelas.'
            ), !$isProValid ? array('lkn-is-pro' => 'true') : array()),
            'description' => 'Porcentagem de juros mínima que será aplicada independentemente do número de parcelas.',
        );

        // Dynamic fields for each installment
        $max_installments = (int) $this->get_option('max_parcels_number', 12);
        for ($i = 1; $i <= $max_installments; $i++) {
            // Interest field for specific installment
            $this->form_fields["{$i}x"] = array(
                // translators: %d is the number of installments
                'title' => sprintf('Juros %dx', $i),
                'type' => 'number',
                'default' => '0',

                'custom_attributes' => array_merge(array(
                    'step' => '0.01',
                    'min' => '0',
                    'max' => '100',
                    'merge-top' => "woocommerce_{$this->id}_installment_interest",
                    // translators: %d is the number of installments
                    'data-title-description' => sprintf('Juros aplicado quando o cliente seleciona pagar em %dx. Deixe 0 para sem juros.', $i)
                ), !$isProValid ? array('lkn-is-pro' => 'true') : array()),
                'description' => 'Esta opção define os juros do parcelamento como porcentagem. Aceita apenas números. Por exemplo, para 10% de juros, digite 10. Deixe em branco ou digite zero para uma parcela sem taxa de juros.',
            );

            // Discount field for specific installment  
            $this->form_fields["{$i}x_discount"] = array(
                // translators: %d is the number of installments
                'title' => sprintf('Desconto %dx', $i),
                'type' => 'number',
                'default' => '0',
                'custom_attributes' => array_merge(array(
                    'step' => '0.01',
                    'min' => '0',
                    'max' => '100',
                    'merge-top' => "woocommerce_{$this->id}_installment_discount",
                    // translators: %d is the number of installments
                    'data-title-description' => sprintf('Desconto aplicado quando o cliente seleciona pagar em %dx. Deixe 0 para sem desconto.', $i)
                ), !$isProValid ? array('lkn-is-pro' => 'true') : array()),
                'description' => 'Esta opção define o desconto do parcelamento como porcentagem. Aceita apenas números. Por exemplo, para 10% de desconto, digite 10. Deixe em branco ou digite zero para uma parcela sem taxa de desconto.',
            );
        }

        $this->form_fields['developers'] = array(
            'title' => 'Desenvolvedor',
            'type' => 'title',
        );

        $this->form_fields['debug'] = array(
            'title' => 'Debug',
            'type' => 'checkbox',
            'label' => 'Habilitar logs de debug.' . ' ' . wp_kses_post('<a href="' . esc_url(admin_url('admin.php?page=wc-status&tab=logs')) . '" target="_blank">' . 'Ver logs' . '</a>'),
            'default' => 'no',
            'desc_tip' => 'Habilitar log de transações.',
            'description' => esc_attr__('Enable this option to log payment requests and responses for troubleshooting purposes.', 'woo-rede'),
            'custom_attributes' => array(
                'data-title-description' => 'Quando habilitado, todas as transações da Rede serão registradas.'
            ),
        );

        if ($this->get_option('debug') == 'yes') {
            $this->form_fields['show_order_logs'] =  array(
                'title' => 'Visualizar Log no Pedido',
                'type' => 'checkbox',
                'label' => sprintf('Habilita visualização do log da transação dentro do pedido.', 'woo-rede'),
                'default' => 'no',
                'desc_tip' => 'Útil para visualizar rapidamente dados de log de pagamento sem acessar os arquivos de log do sistema.',
                'description' => 'Habilite esta opção para registrar solicitações e respostas de pagamento para fins de solução de problemas.',
                'custom_attributes' => array(
                    'data-title-description' => esc_attr__("Enable this to show the transaction details for Rede payments directly in each order’s admin panel.", 'woo-rede')
                ),
            );
            $this->form_fields['clear_order_records'] =  array(
                'title' => 'Limpar logs nos Pedidos',
                'type' => 'button',
                'id' => 'validateLicense',
                'class' => 'woocommerce-save-button components-button is-primary',
                'desc_tip' => 'Use apenas se você não precisar mais dos logs de transação da Rede para pedidos passados.',
                'description' => 'Clique neste botão para excluir todos os dados de log da Rede armazenados em pedidos.',
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
                throw new Exception('Por favor, insira um número de cartão de débito válido');
            }

            $valid = $this->validate_card_fields($_POST);
            if (false === $valid) {
                throw new Exception('Um ou mais campos inválidos', 500);
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
                    $order->add_order_note('Autenticação 3D Secure necessária. Cliente redirecionado para autenticação do banco.');
                    
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
                $order->add_order_note('Rede[Refund Error] ' . 'Reembolso total já processado, verifique o bloco de notas do pedido.');
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
