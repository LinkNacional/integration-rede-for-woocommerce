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

    final public static function getTransactionBrandDetails($tid, $instance)
    {
        // Autenticação básica
        $auth = base64_encode($instance->pv . ':' . $instance->token);

        $apiUrl = ('production' === $instance->environment)
            ? 'https://api.userede.com.br/erede/v1/transactions'
            : 'https://sandbox-erede.useredecloud.com.br/v1/transactions';

        $headers = array(
            'Authorization' => 'Basic ' . $auth,
            'Content-Type' => 'application/json',
            'Transaction-Response' => 'brand-return-opened',
        );

        $response = wp_remote_get($apiUrl . '/' . $tid, array(
            'headers' => $headers,
        ));

        if (is_wp_error($response)) {
            return null;
        }

        $response_body = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_body, true);

        if (isset($response_data['authorization'])) {
            return [
                'brand' => $response_data['authorization']['brand'] ?? null,
                'returnCode' => $response_data['authorization']['returnCode'] ?? null,
            ];
        }

        return null;
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

    final public static function censorString($string, $censorLength)
    {
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

    public function showOrderLogs(): void
    {
        $id = isset($_GET['id']) ? sanitize_text_field(wp_unslash($_GET['id'])) : '';
        if (empty($id)) {
            $id = isset($_GET['post']) ? sanitize_text_field(wp_unslash($_GET['post'])) : '';
        }
        if (! empty($id)) {
            $order_id = $id;
            $order = wc_get_order($order_id);

            if ($order && $order instanceof WC_Order) {
                $orderLogs = $order->get_meta('lknWcRedeOrderLogs');
                $payment_method_id = $order->get_payment_method();
                $options = get_option('woocommerce_' . $payment_method_id . '_settings');
                if (isset($options['show_order_logs']) && $orderLogs && 'yes' === $options['show_order_logs']) {
                    $screen = class_exists('\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController') && wc_get_container()->get('Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController')->custom_orders_table_usage_is_enabled()
                    ? wc_get_page_screen_id('shop-order')
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

    /**
     * Checks if BRL conversion is enabled via pro plugin license and option.
     * @return bool
     */
    public static function is_convert_to_brl_enabled($id) {
        if (function_exists('is_plugin_active') && is_plugin_active('rede-for-woocommerce-pro/rede-for-woocommerce-pro.php')) {
            $pro_license = get_option('lknRedeForWoocommerceProLicense');
            if ($pro_license) {
                $license_data = base64_decode($pro_license);
                if (strpos($license_data, 'active') !== false) {
                    $options = get_option('woocommerce_' . $id . '_settings', []);
                    $convert_to_brl_option = isset($options['convert_to_brl']) ? $options['convert_to_brl'] : 'no';
                    return ($convert_to_brl_option === 'yes');
                }
            }
        }
        return false;
    }

    /**
     * Converts the order total to BRL if enabled and rates are available.
     * @param float|string $order_total
     * @param WC_Order $order
     * @param bool $convert_to_brl_enabled
     * @return float|string Converted order total or original if not converted
     */
    public static function convert_order_total_to_brl($order_total, $order, $convert_to_brl_enabled) {
        if ($convert_to_brl_enabled) {
            $currency_json_path = INTEGRATION_REDE_FOR_WOOCOMMERCE_DIR . 'Includes/files/linkCurrencies.json';
            // Garante que o diretório e arquivo existem
            LknIntegrationRedeForWoocommerceHelper::ensure_currency_json_path($currency_json_path);
            $currency_transient = INTEGRATION_REDE_FOR_WOOCOMMERCE_RATE_CACHE_KEY;
            if (!get_transient($currency_transient)) {
                LknIntegrationRedeForWoocommerceHelper::lkn_update_currency_rates($currency_json_path);
            }
            $currency_data = LknIntegrationRedeForWoocommerceHelper::lkn_get_currency_rates($currency_json_path);
            $default_currency = get_option('woocommerce_currency', 'BRL');
            $order_currency = method_exists($order, 'get_currency') ? $order->get_currency() : $default_currency;
            if ($order_currency !== 'BRL' && !empty($currency_data['rates'][$order_currency])) {
                $rate = floatval($currency_data['rates'][$order_currency]);
                if ($rate > 0) {
                    $order_total = $order_total * (1 / $rate); // Convert to BRL
                }
            }
        }
        return $order_total;
    }

    // Update currency rates from JSON file
    public static function lkn_update_currency_rates($json_path) {
        $url = INTEGRATION_REDE_FOR_WOOCOMMERCE_LINK_URL_API . '/cotacao/cotacao-BRL.json';
        $response = wp_remote_get($url);
        if (is_wp_error($response)) {
            delete_transient(INTEGRATION_REDE_FOR_WOOCOMMERCE_RATE_CACHE_KEY);
            return false;
        }
        $body = wp_remote_retrieve_body($response);
        if (empty($body)) {
            delete_transient(INTEGRATION_REDE_FOR_WOOCOMMERCE_RATE_CACHE_KEY);
            return false;
        }
        global $wp_filesystem;
        if (empty($wp_filesystem)) {
            require_once ABSPATH . '/wp-admin/includes/file.php';
            WP_Filesystem();
        }
        $result = $wp_filesystem->put_contents($json_path, $body, FS_CHMOD_FILE);
        if ($result) {
            set_transient(INTEGRATION_REDE_FOR_WOOCOMMERCE_RATE_CACHE_KEY, true, 2 * HOUR_IN_SECONDS);
            return json_decode($body, true);
        } else {
            delete_transient(INTEGRATION_REDE_FOR_WOOCOMMERCE_RATE_CACHE_KEY);
            return false;
        }
    }

    /**
     * Ensures the currency JSON directory and file exist, creating them if necessary.
     * Uses WordPress filesystem APIs for permissions.
     */
    public static function ensure_currency_json_path($json_path) {
        $dir = dirname($json_path);
        global $wp_filesystem;
        if (empty($wp_filesystem)) {
            require_once ABSPATH . '/wp-admin/includes/file.php';
            WP_Filesystem();
        }
        // Cria o diretório se não existir
        if (!is_dir($dir)) {
            $wp_filesystem->mkdir($dir, FS_CHMOD_DIR);
            delete_transient(INTEGRATION_REDE_FOR_WOOCOMMERCE_RATE_CACHE_KEY);
        }
        // Cria o arquivo vazio se não existir
        if (!file_exists($json_path)) {
            $wp_filesystem->put_contents($json_path, '{}', FS_CHMOD_FILE);
            delete_transient(INTEGRATION_REDE_FOR_WOOCOMMERCE_RATE_CACHE_KEY);
        }

        // Verifica se o conteúdo do arquivo é vazio, apenas '{}' ou estrutura inválida
        $content = $wp_filesystem->get_contents($json_path);
        $is_invalid = false;
        if (trim($content) === '{}' || trim($content) === '') {
            $is_invalid = true;
        } else {
            $json = json_decode($content, true);
            // Estrutura esperada: base, date, rates (rates é array)
            if (!is_array($json) || !isset($json['base']) || !isset($json['date']) || !isset($json['rates']) || !is_array($json['rates'])) {
                $is_invalid = true;
            }
        }
        if ($is_invalid) {
            delete_transient(INTEGRATION_REDE_FOR_WOOCOMMERCE_RATE_CACHE_KEY);
        }
    }

    // Get currency rates from JSON file
    public static function lkn_get_currency_rates($json_path) {
        if (!file_exists($json_path)) return false;
        $json = file_get_contents($json_path);
        if (empty($json)) return false;
        return json_decode($json, true);
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
?>