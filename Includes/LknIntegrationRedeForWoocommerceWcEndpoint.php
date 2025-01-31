<?php

namespace Lkn\IntegrationRedeForWoocommerce\Includes;

use WP_Error;
use WP_REST_Response;

final class LknIntegrationRedeForWoocommerceWcEndpoint
{
    public function registerorderRedeCaptureEndPoint(): void
    {
        register_rest_route('redeIntegration', '/pixListener', array(
            'methods' => 'POST',
            'callback' => array($this, 'pixListener'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route('redeIntegration', '/verifyPixRedeStatus', array(
            'methods' => 'GET',
            'callback' => array($this, 'verifyPixRedeStatus'),
            'permission_callback' => '__return_true',
        ));
    }

    public function pixListener($request)
    {
        add_option('LknIntegrationRedeForWoocommerceEndpointStatus', true);
        update_option('LknIntegrationRedeForWoocommerceEndpointStatus', true);
        $requestParams = $request->get_params();

        $redePixOptions = get_option('woocommerce_integration_rede_pix_settings');
        $tid = $requestParams['data']['id'];

        // Argumentos para buscar todos os pedidos
        $args = array(
            'limit' => -1,
            'status' => array_keys(wc_get_order_statuses()),
            'meta_key' => '_wc_rede_integration_pix_transaction_tid',
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
