<?php

namespace Lkn\IntegrationRedeForWoocommerce\Includes;

use DateTime;

final class LknIntegrationRedeForWoocommerceWcHelper
{
    public static function getPixRede($total, $pixInstance, $reference, $order)
    {
        $pv = sanitize_text_field($pixInstance->get_option('pv'));
        $total = str_replace(".", "", $total);
        $token = sanitize_text_field($pixInstance->get_option('token'));
        $auth = base64_encode($pv . ':' . $token);
        $environment = $pixInstance->get_option('environment');
        $expirationCountOption = $pixInstance->get_option('expiration_count');
        $expirationCount = empty($expirationCountOption) ? 1 : $expirationCountOption;
        $date = new DateTime();
        $date->modify('+' . $expirationCount . ' hours');
        $dateTimeExpiration = $date->format('Y-m-d\TH:i:s');
        $order->update_meta_data('_wc_rede_pix_time_expiration', $dateTimeExpiration);
        if ('production' === $environment) {
            $apiUrl = 'https://api.userede.com.br/erede/v1/transactions';
        } else {
            $apiUrl = 'https://sandbox-erede.useredecloud.com.br/v1/transactions';
        }

        $body = array(
            'kind' => 'pix',
            'reference' => $reference,
            'amount' => $total,
            'qrCode' => array(
                'dateTimeExpiration' => $dateTimeExpiration
            )
        );

        $response = wp_remote_post($apiUrl, array(
            'method' => 'POST',
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . $auth
            ),
            'body' => json_encode($body),
        ));

        $response_body = wp_remote_retrieve_body($response);
        $response_body = json_decode($response_body, true);

        return $response_body;
    }

    public static function refundPixRede($total, $pixInstance, $orderId)
    {
        $pv = sanitize_text_field($pixInstance->get_option('pv'));
        $token = sanitize_text_field($pixInstance->get_option('token'));
        $auth = base64_encode($pv . ':' . $token);
        $total = str_replace(".", "", $total);
        $environment = $pixInstance->get_option('environment');
        $order = wc_get_order($orderId);
        $tid = $order->get_meta('_wc_rede_integration_pix_transaction_tid');
        if ('production' === $environment) {
            $apiUrl = 'https://api.userede.com.br/erede/v1/transactions';
        } else {
            $apiUrl = 'https://sandbox-erede.useredecloud.com.br/v1/transactions';
        }

        $body = array(
            'amount' => $total,
        );

        $response = wp_remote_post($apiUrl . '/' . $tid . '/refunds', array(
            'method' => 'POST',
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . $auth
            ),
            'body' => json_encode($body),
        ));

        $response_body = wp_remote_retrieve_body($response);
        $response_body = json_decode($response_body, true);

        return $response_body;
    }

    public static function generateMetaTable($order, $metaKeys, $title): void
    {
        require INTEGRATION_REDE_FOR_WOOCOMMERCE_DIR_URL . 'Includes/views/LknIntegrationRedeForWoocommerceMetaTable.php';
    }
}
