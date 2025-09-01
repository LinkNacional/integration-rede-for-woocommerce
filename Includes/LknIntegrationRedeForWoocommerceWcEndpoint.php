<?php

namespace Lkn\IntegrationRedeForWoocommerce\Includes;

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
    }

    public function clearOrderLogs($request) {
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

    public function maxipagoDebitListener($request) {
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
            'status' => array_keys( wc_get_order_statuses() ),
            'meta_key' => '_wc_maxipago_transaction_reference_num',
            'meta_value' => $referenceNumber,
        );
        $order = wc_get_orders( $args )[0];

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

    public function redePixListener($request) {
        add_option('lknRedeForWoocommerceProEndpointStatus', true);
        update_option('lknRedeForWoocommerceProEndpointStatus', true);
        $requestParams = $request->get_params();

        $redePixOptions = get_option('woocommerce_rede_pix_settings');
        $tid = $requestParams['data']['id'];

        // Argumentos para buscar todos os pedidos
        $args = array(
            'limit' => -1,
            'status' => array_keys( wc_get_order_statuses() ),
            'meta_key' => '_wc_rede_pix_transaction_tid',
            'meta_value' => $tid,
        );

        // Usa wc_get_orders para buscar os pedidos
        $order = wc_get_orders( $args )[0];

        if ( ! empty($order)) {
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
        $pv = sanitize_text_field($pixOptions['pv']);
        $token = sanitize_text_field($pixOptions['token']);
        $auth = base64_encode($pv . ':' . $token);
        $environment = $pixOptions['environment'];

        if ('production' === $environment) {
            $apiUrl = 'https://api.userede.com.br/erede/v1/transactions/';
        } else {
            $apiUrl = 'https://sandbox-erede.useredecloud.com.br/v1/transactions/';
        }
        $response = wp_remote_post($apiUrl . $tId, array(
            'method' => 'GET',
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . $auth
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
}
