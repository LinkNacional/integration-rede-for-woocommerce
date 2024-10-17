<?php
namespace Lkn\IntegrationRedeForWoocommerce\Includes;

abstract class LknIntegrationRedeForWoocommerceHelper {
    /**
     * Makes a .log file for each donation.
     *
     * @since 1.0.0
     * @since 2.0.0 verification if debug is enabled is done inside the function.
     * The log is registered as JSON.
     *
     * @param  string|array $log
     * @param  string $configs
     *
     * @return void
     */
    final public static function reg_log($log, $configs): void {
        if ('yes' == $configs['debug']) {
            $logDirectory = dirname($configs['base']);
            if ( ! file_exists($logDirectory)) {
                mkdir($logDirectory, 0777, true);
            }
            $jsonLog = wp_json_encode($log, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
            error_log($jsonLog, 3, $configs['base']);
            chmod($configs['base'], 0666);
        }
    }

    final public static function getCartTotal() {
        $cart = WC()->cart;

        if (empty($cart)) {
            return 0;
        }
        $cart_items = $cart->get_cart();
        $total = 0;
        foreach ($cart_items as $cart_item_key => $cart_item) {
            $product = $cart_item['data'];
            $total += $product->get_price() * $cart_item['quantity'];
        }
        return $total;
    }
    
    final public static function updateFixLoadScriptOption($id) {
        if (!empty($_POST) && isset($_POST['_wpnonce']) && isset($_GET['section']) && $_GET['section'] === $id) {
            $enabledFixLoadScript = isset($_POST["woocommerce_" . $id . "_enabled_fix_load_script"]) ? 'yes' : 'no';
            
            $optionsToUpdate = [
                'maxipago_credit',
                'maxipago_debit',
                'rede_credit',
                'rede_debit',
            ];

            foreach ($optionsToUpdate as $option) {
                $paymentOptions = get_option('woocommerce_' . $option . '_settings', array());
                $paymentOptions['enabled_fix_load_script'] = $enabledFixLoadScript;
                update_option('woocommerce_' . $option . '_settings', $paymentOptions);
            }
        }
    }

    final public static function getCardBrand($tid, $instace) {
        $auth = base64_encode( $instace->pv . ':' . $instace->token );

        if('production' === $instace->environment) {
            $apiUrl = 'https://api.userede.com.br/erede/v1/transactions';
        } else {
            $apiUrl = 'https://sandbox-erede.useredecloud.com.br/v1/transactions';
        }

        $response = wp_remote_get( $apiUrl . '/' . $tid , array(
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
    
}