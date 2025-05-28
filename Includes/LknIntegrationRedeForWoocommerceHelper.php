<?php

namespace Lkn\IntegrationRedeForWoocommerce\Includes;
use WC_Order;

class LknIntegrationRedeForWoocommerceHelper
{
    final public static function getCartTotal()
    {
        global $woocommerce;
        if (empty($woocommerce)) {
            return 0;
        }
        if ($woocommerce->cart) {
            return (float) $woocommerce->cart->total;
        }
        return 0;
    }

    final public static function updateFixLoadScriptOption($id): void
    {
        $wpnonce = isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : '';
        $section = isset($_GET['section']) ? sanitize_text_field(wp_unslash($_GET['section'])) : '';

        if (! empty($wpnonce) && $section === $id) {
            $enabledFixLoadScript = isset($_POST["woocommerce_" . $id . "_enabled_fix_load_script"]) ? 'yes' : 'no';

            $optionsToUpdate = array(
                'maxipago_credit',
                'maxipago_debit',
                'rede_credit',
                'rede_debit',
            );

            foreach ($optionsToUpdate as $option) {
                $paymentOptions = get_option('woocommerce_' . $option . '_settings', array());
                $paymentOptions['enabled_fix_load_script'] = $enabledFixLoadScript;
                update_option('woocommerce_' . $option . '_settings', $paymentOptions);
            }
        }
    }

    final public static function getCardBrand($tid, $instace)
    {
        $auth = base64_encode($instace->pv . ':' . $instace->token);

        if ('production' === $instace->environment) {
            $apiUrl = 'https://api.userede.com.br/erede/v1/transactions';
        } else {
            $apiUrl = 'https://sandbox-erede.useredecloud.com.br/v1/transactions';
        }

        $response = wp_remote_get($apiUrl . '/' . $tid, array(
            'headers' => array(
                'Authorization' => 'Basic ' . $auth,
                'Content-Type' => 'application/json',
                'Transaction-Response' => 'brand-return-opened'
            ),
        ));

        $response_body = wp_remote_retrieve_body($response);
        $response_body = json_decode($response_body, true);

        return($response_body['authorization']['brand']['name']);
    }

    final public static function censorString($string, $censorLength) {
        $length = strlen($string);

        if ($censorLength >= $length) {
            // Se o número de caracteres a censurar for maior ou igual ao comprimento total, censura tudo
            return str_repeat('*', $length);
        }

        $startLength = floor(($length - $censorLength) / 2); // Dividir o restante igualmente entre início e fim
        $endLength = $length - $startLength - $censorLength; // O que sobra para o final

        $start = substr($string, 0, $startLength);
        $end = substr($string, -$endLength);

        $censored = str_repeat('*', $censorLength);
        return $start . $censored . $end;
    }

    public function showOrderLogs(): void {
        $id = isset($_GET['id']) ? sanitize_text_field(wp_unslash($_GET['id'])) : '';
        if (empty($id)) {
            $id = isset($_GET['post']) ? sanitize_text_field(wp_unslash($_GET['post'])) : '';
        }
        if ( ! empty($id)) {
            $order_id = $id;
            $order = wc_get_order( $order_id );
            
            if ($order && $order instanceof WC_Order) {
                $orderLogs = $order->get_meta('lknWcRedeOrderLogs');
                $payment_method_id = $order->get_payment_method();
                $options = get_option('woocommerce_' . $payment_method_id . '_settings');
                if ($orderLogs && 'yes' === $options['show_order_logs']) {
                    $screen = class_exists( '\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController' ) && wc_get_container()->get( 'Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController' )->custom_orders_table_usage_is_enabled()
                    ? wc_get_page_screen_id( 'shop-order' )
                    : 'shop_order';
                    
                    add_meta_box(
                        'showOrderLogs',
                        'Logs das transações',
                        array($this, 'showLogsContent'),
                        $screen,
                        'advanced',
                    );
                }
            }
        }
    }
    
    public function showLogsContent($object): void
    {
        // Obter o objeto WC_Order
        $order = is_a($object, 'WP_Post') ? wc_get_order($object->ID) : $object;
        $orderLogs = $order->get_meta('lknWcRedeOrderLogs');
        
        // Decodificar o JSON armazenado
        $decodedLogs = json_decode($orderLogs, true);
        
        if ($decodedLogs && is_array($decodedLogs)) {
            // Preparar cada seção para exibição com formatação
            $url = $decodedLogs['url'] ?? 'N/A';
            $body = isset($decodedLogs['body']) ? json_encode($decodedLogs['body'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : 'N/A';
            $response = isset($decodedLogs['response']) ? json_encode($decodedLogs['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : 'N/A';
            
            // Exibir as seções formatadas
            ?>
            <div id="lknWcRedeOrderLogs">
                <div>
                    <h3>URL:</h3>
                    <pre class="wc-pre"><?php echo esc_html($url); ?></pre>
                </div>
                
                <h3>Body:</h3>
                <pre class="wc-pre"><?php echo esc_html($body); ?></pre>
                
                <h3>Response:</h3>
                <pre class="wc-pre"><?php echo esc_html($response); ?></pre>
            </div>
            <?php
        }
    }
}
