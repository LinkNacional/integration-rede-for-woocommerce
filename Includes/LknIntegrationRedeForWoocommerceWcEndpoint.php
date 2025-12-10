<?php

namespace LknIntegrationRedeForWoocommerce\Includes;

use WP_Error;
use WP_REST_Response;

final class LknIntegrationRedeForWoocommerceWcEndpoint
{
    public function registerorderRedeCaptureEndPoint(): void
    {
        // Só registra a rota redePixListener se o plugin PRO não estiver ativo
        if (!is_plugin_active('rede-for-woocommerce-pro/rede-for-woocommerce-pro.php')) {
            register_rest_route('redePRO', '/redePixListener', array(
                'methods' => 'POST',
                'callback' => array($this, 'redePixListener'),
                'permission_callback' => '__return_true',
            ));
        }

        register_rest_route('redeIntegration', '/pixListener', array(
            'methods' => 'POST',
            'callback' => array($this, 'redePixListener'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route('redeIntegration', '/verifyPixRedeStatus', array(
            'methods' => 'GET',
            'callback' => array($this, 'verifyPixRedeStatus'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route('redeIntegration', '/maxipagoDebitListener', array(
            'methods' => 'POST',
            'callback' => array($this, 'maxipagoDebitListener'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route('redeIntegration', '/clearOrderLogs', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'clearOrderLogs'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route('redeIntegration', '/s', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle3dsSuccess'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route('redeIntegration', '/f', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle3dsFailure'),
            'permission_callback' => '__return_true',
        ));
    }

    public function clearOrderLogs($request)
    {
        $args = array(
            'limit' => -1, // Sem limite, pega todas as ordens
            'meta_key' => 'lknWcRedeOrderLogs', // Meta key específica
            'meta_compare' => 'EXISTS', // Verifica se a meta key existe
        );

        $orders = wc_get_orders($args);

        foreach ($orders as $order) {
            $order->delete_meta_data('lknWcRedeOrderLogs');
            $order->save();
        }

        return new WP_REST_Response($orders, 200);
    }

    public function maxipagoDebitListener($request)
    {
        add_option('LknIntegrationRedeForWoocommerceMaxipagoDebitEndpointStatus', true);
        update_option('LknIntegrationRedeForWoocommerceMaxipagoDebitEndpointStatus', true);
        $requestBody = $request->get_body();

        parse_str($requestBody, $parsedBody);
        $xmlString = urldecode($parsedBody['xml']);
        $xmlObject = simplexml_load_string($xmlString, "SimpleXMLElement", LIBXML_NOCDATA);
        $notification = $xmlObject->{'transaction-event'};

        // Converte o valor de orderID para string
        $referenceNumber = (string) $notification->referenceNumber;
        $transactionStatus = (string) $notification->transactionStatus;
        $maxipagoPixOptions = get_option('woocommerce_maxipago_debit_settings');

        $args = array(
            'limit' => -1,
            'status' => array_keys(wc_get_order_statuses()),
            'meta_key' => '_wc_maxipago_transaction_reference_num',
            'meta_value' => $referenceNumber,
        );
        $order = wc_get_orders($args)[0];

        switch ($transactionStatus) {
            case '3':
                $paymentCompleteStatus = $maxipagoPixOptions['payment_complete_status'];
                if ("" == $paymentCompleteStatus) {
                    $paymentCompleteStatus = 'processing';
                }
                $order->update_status($paymentCompleteStatus);
                break;
            case '9':
                $order->update_status('cancelled');
                break;
            default:
                $order->update_status('cancelled');
                break;
        }

        return new WP_REST_Response('', 200);
    }

    public function redePixListener($request)
    {
        add_option('lknRedeForWoocommerceProEndpointStatus', true);
        update_option('lknRedeForWoocommerceProEndpointStatus', true);
        $requestParams = $request->get_params();

        $redePixOptions = get_option('woocommerce_rede_pix_settings');
        $tid = $requestParams['data']['id'];

        // Argumentos para buscar todos os pedidos
        $args = array(
            'limit' => -1,
            'status' => array_keys(wc_get_order_statuses()),
            'meta_key' => '_wc_rede_pix_transaction_tid',
            'meta_value' => $tid,
        );

        // Usa wc_get_orders para buscar os pedidos
        $order = wc_get_orders($args)[0];

        if (! empty($order)) {
            if ('PV.UPDATE_TRANSACTION_PIX' == $requestParams['events'][0]) {
                $paymentCompleteStatus = $redePixOptions['payment_complete_status'];
                if ("" == $paymentCompleteStatus) {
                    $paymentCompleteStatus = 'processing';
                }
                $order->update_status($paymentCompleteStatus);
            }
        }

        return new WP_REST_Response('', 200);
    }

    public function verifyPixRedeStatus($request)
    {
        $parameters = $request->get_params();
        $order = wc_get_order($parameters['donationId']);
        $tId = $order->get_meta('_wc_rede_integration_pix_transaction_tid');
        if (empty($order)) {
            return new WP_Error('order_not_found', __('Order not found', 'woo-rede'), array('status' => 404));
        }

        $pixOptions = get_option('woocommerce_integration_rede_pix_settings');
        $environment = $pixOptions['environment'];

        // Obter token OAuth2 válido ou renovar se expirado (20 minutos)
        LknIntegrationRedeForWoocommerceHelper::refresh_expired_rede_oauth_tokens(20);
        $token_data = LknIntegrationRedeForWoocommerceHelper::get_cached_rede_oauth_token_for_gateway('integration_rede_pix', $environment);

        if (!$token_data || empty($token_data['token'])) {
            return new WP_REST_Response($response_body['authorization']['status'] ?? 'Invalid Auth', 400);
        }

        // API v2 da Rede
        if ('production' === $environment) {
            $apiUrl = 'https://api.userede.com.br/erede/v2/transactions';
        } else {
            $apiUrl = 'https://sandbox-erede.useredecloud.com.br/v2/transactions';
        }
        
        $response = wp_remote_get($apiUrl . '/' . $tId, array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token_data['token']
            ),
        ));
        
        $response_body = wp_remote_retrieve_body($response);

        $response_body = json_decode($response_body, true);

        if (($response_body['authorization']['status'] ?? 'Accepted') == 'Approved') {
            $paymentCompleteStatus = $pixOptions['payment_complete_status'];
            if ("" == $paymentCompleteStatus) {
                $paymentCompleteStatus = 'processing';
            }
            $order->update_status($paymentCompleteStatus);

            $order->save();
        }

        return new WP_REST_Response($response_body['authorization']['status'] ?? 'Accepted', 200);
    }

    public function handle3dsSuccess($request)
    {
        $parameters = $request->get_params();
        
        // A Rede envia todos os dados da transação no webhook
        $order_id = intval($parameters['o'] ?? 0);
        $key_partial = sanitize_text_field($parameters['k'] ?? '');
        $tid = sanitize_text_field($parameters['tid'] ?? '');
        $return_code = sanitize_text_field($parameters['returnCode'] ?? '');
        
        if (!$order_id || !$tid) {
            return new WP_Error('invalid_parameters', __('Missing required parameters', 'woo-rede'), array('status' => 400));
        }

        // Valida o pedido
        $order = wc_get_order($order_id);
        if (!$order) {
            return new WP_Error('invalid_order', __('Order not found', 'woo-rede'), array('status' => 404));
        }
        
        // Validação parcial da chave (primeiros 8 caracteres)
        $full_order_key = $order->get_order_key();
        if (substr($full_order_key, 0, 8) !== $key_partial) {
            return new WP_Error('invalid_key', __('Invalid order key', 'woo-rede'), array('status' => 403));
        }

        try {
            // Usa os dados que já vêm no webhook da Rede
            $this->update_order_metadata_and_status($order, $parameters);
            
            $redirect_url = $order->get_checkout_order_received_url();
            wp_safe_redirect($redirect_url);
            exit;
        } catch (Exception $e) {
            $order->add_order_note(__('Error processing 3DS success: ', 'woo-rede') . $e->getMessage());
            return new WP_REST_Response(array('status' => 'error', 'message' => $e->getMessage()), 500);
        }
    }

    public function handle3dsFailure($request)
    {
        $parameters = $request->get_params();
        
        // Parâmetros simplificados: o=order_id, k=key_partial
        $order_id = intval($parameters['o'] ?? 0);
        $key_partial = sanitize_text_field($parameters['k'] ?? '');
        
        if (!$order_id) {
            return new WP_Error('invalid_parameters', __('Missing order ID', 'woo-rede'), array('status' => 400));
        }

        // Valida o pedido
        $order = wc_get_order($order_id);
        if (!$order) {
            return new WP_Error('invalid_order', __('Order not found', 'woo-rede'), array('status' => 404));
        }
        
        // Validação parcial da chave (primeiros 8 caracteres)
        $full_order_key = $order->get_order_key();
        if (substr($full_order_key, 0, 8) !== $key_partial) {
            return new WP_Error('invalid_key', __('Invalid order key', 'woo-rede'), array('status' => 403));
        }

        // Marca pedido como falhado
        $order->add_order_note(__('3D Secure authentication failed', 'woo-rede'));
        // $order->update_status('failed');
        $order->save();
        
        // Redireciona para a página de checkout com parâmetro de erro
        $redirect_url = add_query_arg('3ds_error', '1', wc_get_checkout_url());
        wp_safe_redirect($redirect_url);
        exit;
    }

    private function query_rede_transaction_by_reference($reference)
    {
        try {
            // Obtém token OAuth2 válido
            LknIntegrationRedeForWoocommerceHelper::refresh_expired_rede_oauth_tokens(20);
            $token_data = LknIntegrationRedeForWoocommerceHelper::get_cached_rede_oauth_token_for_gateway('rede_debit', 'test'); // ou 'production'

            if (!$token_data || empty($token_data['token'])) {
                throw new Exception('Could not obtain OAuth token');
            }

            // Determine environment (you might need to get this from settings)
            $debit_settings = get_option('woocommerce_rede_debit_settings');
            $environment = $debit_settings['environment'] ?? 'test';

            if ($environment === 'production') {
                $apiUrl = 'https://api.userede.com.br/erede/v2/transactions/' . $reference;
            } else {
                $apiUrl = 'https://sandbox-erede.useredecloud.com.br/v2/transactions/' . $reference;
            }

            $response = wp_remote_get($apiUrl, array(
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token_data['token']
                ),
                'timeout' => 30
            ));

            if (is_wp_error($response)) {
                throw new Exception('API request failed: ' . $response->get_error_message());
            }
            
            $response_code = wp_remote_retrieve_response_code($response);
            $response_body = wp_remote_retrieve_body($response);
            $response_data = json_decode($response_body, true);
            
            if ($response_code !== 200) {
                throw new Exception('API returned error: ' . $response_code . ' - ' . $response_body);
            }
            
            return $response_data;
            
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Atualiza metadados e status do pedido usando dados do webhook 3DS
     */
    private function update_order_metadata_and_status($order, $webhook_data)
    {
        // Configurações do gateway debit
        $debit_settings = get_option('woocommerce_rede_debit_settings');
        
        // Configurações para conversão de moeda
        $convert_to_brl_enabled = LknIntegrationRedeForWoocommerceHelper::is_convert_to_brl_enabled('rede_debit');
        $default_currency = get_option('woocommerce_currency', 'BRL');
        $order_currency = method_exists($order, 'get_currency') ? $order->get_currency() : $default_currency;
        $decimals = get_option('woocommerce_price_num_decimals', 2);
        
        // Conversão do total do pedido
        $order_total = $order->get_total();
        $order_total_converted = LknIntegrationRedeForWoocommerceHelper::convert_order_total_to_brl($order_total, $order, $convert_to_brl_enabled);
        $order_total_converted = wc_format_decimal($order_total_converted, $decimals);

        // Dados de câmbio
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

        // Salva todos os metadados da transação
        $order->update_meta_data('_wc_rede_transaction_return_code', $webhook_data['returnCode'] ?? '');
        $order->update_meta_data('_wc_rede_transaction_return_message', $webhook_data['returnMessage'] ?? '');
        $order->update_meta_data('_wc_rede_transaction_id', $webhook_data['tid'] ?? '');
        $order->update_meta_data('_wc_rede_transaction_refund_id', $webhook_data['refundId'] ?? '');
        $order->update_meta_data('_wc_rede_transaction_cancel_id', $webhook_data['cancelId'] ?? '');
        $order->update_meta_data('_wc_rede_transaction_nsu', $webhook_data['nsu'] ?? '');
        $order->update_meta_data('_wc_rede_transaction_authorization_code', $webhook_data['authorizationCode'] ?? '');
        
        // Dados do cartão - alguns podem não vir no webhook, usar valores padrão
        $order->update_meta_data('_wc_rede_transaction_bin', $webhook_data['bin'] ?? '');
        $order->update_meta_data('_wc_rede_transaction_last4', $webhook_data['last4'] ?? '');
        $order->update_meta_data('_wc_rede_transaction_brand', $webhook_data['brand_name'] ?? '');
        
        // Auto capture condicional baseado no tipo de cartão salvo nos metadados
        $saved_card_type = $order->get_meta('_wc_rede_card_type') ?: 'debit';
        $saved_installments = $order->get_meta('_wc_rede_installments') ?: 1;
        $auto_capture_setting = sanitize_text_field($debit_settings['auto_capture'] ?? 'yes') == 'no' ? false : true;
        
        // Sempre true para debit, configurável para credit
        $capture_value = ($saved_card_type === 'debit') ? true : $auto_capture_setting;
        $order->update_meta_data('_wc_rede_captured', $capture_value);
        
        // Metadados financeiros
        $order->update_meta_data('_wc_rede_total_amount', $order->get_total());
        $order->update_meta_data('_wc_rede_total_amount_converted', $order_total_converted);
        $order->update_meta_data('_wc_rede_total_amount_is_converted', $convert_to_brl_enabled ? true : false);
        $order->update_meta_data('_wc_rede_exchange_rate', $exchange_rate_value);
        $order->update_meta_data('_wc_rede_decimal_value', $decimals);

        // Status de autorização se disponível
        if (isset($webhook_data['authorization_status'])) {
            $order->update_meta_data('_wc_rede_transaction_authorization_status', $webhook_data['authorization_status']);
        }

        // Ambiente de transação
        $environment = $debit_settings['environment'] ?? 'test';
        $order->update_meta_data('_wc_rede_transaction_environment', $environment);

        $order->save();

        // Debug logging se habilitado
        if (($debit_settings['debug'] ?? 'no') === 'yes') {
            $tId = $webhook_data['tid'] ?? null;
            $returnCode = $webhook_data['returnCode'] ?? null;
            $brandDetails = null;
            
            if ($tId) {
                // Cria uma instância temporária do gateway para usar o helper
                $gateway = new \LknIntegrationRedeForWoocommerce\Includes\LknIntegrationRedeForWoocommerceWcRedeDebit();
                $brandDetails = LknIntegrationRedeForWoocommerceHelper::getTransactionBrandDetails($tId, $gateway);
            }

            $logger = wc_get_logger();
            $logger->info('3DS Webhook - Transaction processed', array(
                'source' => 'rede_debit',
                'transaction' => $webhook_data,
                'order' => array(
                    'orderId' => $order->get_id(),
                    'amount' => $order_total_converted,
                    'orderCurrency' => $order_currency,
                    'currencyConverted' => $convert_to_brl_enabled ? 'BRL' : null,
                    'exchangeRateValue' => $exchange_rate_value,
                    'status' => $order->get_status(),
                    'brand' => isset($brandDetails['brand']) ? $brandDetails['brand'] : ($webhook_data['brand_name'] ?? null),
                    'returnCode' => $returnCode,
                ),
            ));
            
            $cardData = array(
                'card_number' => '**** **** **** ' . ($webhook_data['last4'] ?? '****'),
                'holder_name' => 'Card Holder',
                'expiry_month' => '**',
                'expiry_year' => '****',
                'security_code' => '***',
                'card_type' => $saved_card_type,
                'installments' => $saved_installments
            );
            
            $this->regOrderLogs($order->get_id(), $order_total_converted, $cardData, $webhook_data, $order);
        }

        // Processa status do pedido usando a função específica para 3DS (inclui lógica de auto_capture)
        $this->process_3ds_order_status($order, $webhook_data, '3D Secure authentication completed');
    }

    /**
     * Processa status do pedido especificamente para retorno 3DS
     * A resposta 3DS tem estrutura diferente e precisamos do card_type dos metadados
     */
    private function process_3ds_order_status($order, $webhook_data, $note = '')
    {
        $return_code = $webhook_data['returnCode'] ?? '';
        $return_message = $webhook_data['returnMessage'] ?? '';
        
        // Para 3DS, recuperar o card_type dos metadados do pedido
        $saved_card_type = $order->get_meta('_wc_rede_card_type') ?: 'debit';
        
        // Obter configuração de auto_capture do gateway debit
        $debit_settings = get_option('woocommerce_rede_debit_settings');
        $auto_capture = sanitize_text_field($debit_settings['auto_capture'] ?? 'yes') == 'no' ? false : true;
        
        // Determinar se foi capturado baseado no tipo de cartão e configuração
        $capture = ($saved_card_type === 'debit') ? true : $auto_capture;
        
        // Adiciona informação sobre o tipo de cartão detectado na nota
        $card_type_note = sprintf(' [Card Type: %s, Capture: %s]', $saved_card_type, $capture ? 'Yes' : 'No');
        $status_note = sprintf('Rede[%s]', $return_message);
        $order->add_order_note($status_note . ' ' . $note . $card_type_note);

        // Só altera o status se o pedido estiver pendente
        if ($order->get_status() === 'pending') {
            if ($return_code == '00') {
                if ($capture) {
                    // Status configurável pelo usuário para pagamentos aprovados com captura
                    $payment_complete_status = $debit_settings['payment_complete_status'] ?? 'processing';
                    $order->update_status($payment_complete_status);
                } else {
                    // Para pagamentos credit sem captura, aguardando captura manual
                    $order->update_status('on-hold', 'Pagamento autorizado, aguardando captura manual.');
                    wc_reduce_stock_levels($order->get_id());
                }
            } else {
                $order->update_status('failed', $status_note);
            }
        }
    }

    public function regOrderLogs($orderId, $order_total, $cardData, $transaction, $order, $brand = null): void
    {
        $debit_settings = get_option('woocommerce_rede_debit_settings');
        if (($debit_settings['debug'] ?? 'no') === 'yes') {
            $tId = null;
            $returnCode = null;
            
            if ($brand === null && $transaction) {
                $brand = null;
                if (is_array($transaction)) {
                    $tId = $transaction['tid'] ?? null;
                    $returnCode = $transaction['returnCode'] ?? null;
                }
                
                if ($tId) {
                    $gateway = new \LknIntegrationRedeForWoocommerce\Includes\LknIntegrationRedeForWoocommerceWcRedeDebit();
                    $brand = LknIntegrationRedeForWoocommerceHelper::getTransactionBrandDetails($tId, $gateway);
                }
            }
            
            $default_currency = get_option('woocommerce_currency', 'BRL');
            $order_currency = method_exists($order, 'get_currency') ? $order->get_currency() : $default_currency;
            $currency_json_path = INTEGRATION_REDE_FOR_WOOCOMMERCE_DIR . 'Includes/files/linkCurrencies.json';
            $currency_data = LknIntegrationRedeForWoocommerceHelper::lkn_get_currency_rates($currency_json_path);
            $convert_to_brl_enabled = LknIntegrationRedeForWoocommerceHelper::is_convert_to_brl_enabled('rede_debit');

            $exchange_rate_value = 1;
            if ($convert_to_brl_enabled && $currency_data !== false && is_array($currency_data) && isset($currency_data['rates']) && isset($currency_data['base'])) {
                // Exibe a cotação apenas se não for BRL
                if ($order_currency !== 'BRL' && isset($currency_data['rates'][$order_currency])) {
                    $rate = $currency_data['rates'][$order_currency];
                    // Converte para string, preservando todas as casas decimais
                    $exchange_rate_value = (string)$rate;
                }
            }

            // Recupera metadados do pedido se não estiverem em cardData
            $final_card_type = isset($cardData['card_type']) ? $cardData['card_type'] : ($order->get_meta('_wc_rede_card_type') ?: 'debit');
            $final_installments = isset($cardData['installments']) ? $cardData['installments'] : ($order->get_meta('_wc_rede_installments') ?: 1);
            
            $bodyArray = array(
                'orderId' => $orderId,
                'amount' => $order_total,
                'orderCurrency' => $order_currency,
                'currencyConverted' => $convert_to_brl_enabled ? 'BRL' : null,
                'exchangeRateValue' => $exchange_rate_value,
                'cardData' => $cardData,
                'cardType' => $final_card_type,
                'installments' => ($final_card_type === 'credit' && $final_installments >= 1) ? $final_installments : null,
                'brand' => isset($tId) && isset($brand) ? $brand['brand'] : null,
                'returnCode' => isset($returnCode) ? $returnCode : null,
            );

            $bodyArray['cardData']['card_number'] = LknIntegrationRedeForWoocommerceHelper::censorString($bodyArray['cardData']['card_number'], 8);

            // Remove parâmetros desnecessários da resposta
            $cleanedTransaction = $transaction;
            if (is_array($cleanedTransaction)) {
                unset($cleanedTransaction['o'], $cleanedTransaction['k'], $cleanedTransaction['r']);
            }

            $orderLogsArray = array(
                'body' => $bodyArray,
                'response' => $cleanedTransaction
            );

            $orderLogs = json_encode($orderLogsArray);
            $order->update_meta_data('lknWcRedeOrderLogs', $orderLogs);
            $order->save();
        }
    }

}
