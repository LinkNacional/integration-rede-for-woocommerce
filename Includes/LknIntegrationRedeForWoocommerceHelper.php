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

    final public static function getCardBrand($tid, $instance)
    {
        $auth = base64_encode($instance->pv . ':' . $instance->token);

        if ('production' === $instance->environment) {
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

        return ($response_body['authorization']['brand']['name']);
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
    public static function is_convert_to_brl_enabled($id)
    {
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
    public static function convert_order_total_to_brl($order_total, $order, $convert_to_brl_enabled)
    {
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
    public static function lkn_update_currency_rates($json_path)
    {
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
    public static function ensure_currency_json_path($json_path)
    {
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
    public static function lkn_get_currency_rates($json_path)
    {
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

    public static function getUrlIcon()
    {
        return plugin_dir_url(__DIR__) . "Includes/assets/WordpressAssets/icon.svg";
    }

    public static function lknIntegrationRedeProRedeInterest($order_total, $interest, $i, $option, $instance, $order_id = null) 
    {

        $installments = isset($_POST[$instance->id . '_installments']) ?
        absint(sanitize_text_field(wp_unslash($_POST[$instance->id . '_installments']))) : 1;
        $interest = round((float) $instance->get_option($i . 'x'), 2);

        // Usar o subtotal + frete como base para cálculo de juros
        $base_amount = $order_total;
        $additional_fees = 0;
        $discount_amount = 0;
        $tax_amount = 0;
        
        if (WC()->cart && !WC()->cart->is_empty()) {
            $cart_subtotal = WC()->cart->get_subtotal();
            $cart_shipping = WC()->cart->get_shipping_total();
            if ($cart_subtotal > 0) {
                $base_amount = $cart_subtotal + $cart_shipping;
                
                // Pegar fees externos (não criados por este plugin)
                $additional_fees = 0;
                foreach (WC()->cart->get_fees() as $fee) {
                    // Ignorar fees criados pelo próprio plugin
                    if ($fee->name !== __('Juros', 'rede-for-woocommerce-pro') && 
                        $fee->name !== __('Desconto', 'rede-for-woocommerce-pro')) {
                        $additional_fees += $fee->total;
                    }
                }
                
                // Pegar desconto de cupom
                $discount_amount = WC()->cart->get_discount_total();
                
                // Pegar taxes
                $tax_amount = WC()->cart->get_total_tax();
            }
        } elseif ($order_id && function_exists('wc_get_order')) {
            // Se estivermos processando um pedido, usar dados do pedido
            $order = wc_get_order($order_id);
            if ($order) {
                $order_subtotal = $order->get_subtotal();
                $order_shipping = $order->get_shipping_total();
                if ($order_subtotal > 0) {
                    $base_amount = $order_subtotal + $order_shipping;
                    
                    // Pegar fees externos do pedido (não criados por este plugin)
                    $additional_fees = 0;
                    foreach ($order->get_fees() as $fee) {
                        // Ignorar fees criados pelo próprio plugin
                        if ($fee->get_name() !== __('Juros', 'rede-for-woocommerce-pro') && 
                            $fee->get_name() !== __('Desconto', 'rede-for-woocommerce-pro')) {
                            $additional_fees += $fee->get_total();
                        }
                    }
                    
                    // Pegar desconto de cupom do pedido
                    $discount_amount = $order->get_total_discount();
                    
                    // Pegar taxes do pedido
                    $tax_amount = $order->get_total_tax();
                }
            }
        }

        switch ($option) {
            case 'label':
                // Verificar se existe um limite de parcelas por produto
                $extra_fees = 0;

                if ($instance->get_option('installment_interest') == 'yes') {
                    $total = $base_amount / $i;
                    if ($total > $instance->get_option('min_interest') && $instance->get_option('min_interest') > 0) {
                        $interest = 0;
                    }
                    if ($interest >= 1) {
                        // Calcular juros apenas sobre a base (subtotal + shipping)
                        $total_with_interest = $base_amount + ($base_amount * ($interest * 0.01));
                        
                        // Adicionar outros valores: fees externos - cupom + taxes
                        $final_total = $total_with_interest + $additional_fees - $discount_amount + $tax_amount;
                        
                        if ($instance->get_option('interest_show_percent') == 'yes') {
                            return html_entity_decode(sprintf('%dx de %s (%s%% de juros)', $i, wp_strip_all_tags( wc_price( $final_total / $i)), $interest));
                        }
                            return html_entity_decode(sprintf('%dx de %s', $i, wp_strip_all_tags( wc_price(($final_total / $i)))));
                    } else {
                        // Sem juros, mas ainda aplicar outros valores
                        $final_total = $base_amount + $additional_fees - $discount_amount + $tax_amount;
                        return html_entity_decode(sprintf('%dx de %s', $i, wp_strip_all_tags( wc_price( $final_total / $i)))) . ' ' . __("interest-free", 'rede-for-woocommerce-pro');
                    }
                } else {
                    $discount = round((float) $instance->get_option($i . 'x_discount'), 0);
                    $total_with_discount = $base_amount - ($base_amount * ($discount * 0.01));
                    
                    // Adicionar outros valores: fees externos - cupom + taxes
                    $final_total = $total_with_discount + $additional_fees - $discount_amount + $tax_amount;
                    
                    if ($discount >= 1) {
                        if ($instance->get_option('interest_show_percent') == 'yes') {
                            return html_entity_decode(sprintf( '%dx de %s (%s%% de desconto)', $i, wp_strip_all_tags( wc_price(($final_total / $i))), $discount));
                        }
                        return html_entity_decode(sprintf( '%dx de %s', $i, wp_strip_all_tags( wc_price(($final_total / $i)))));
                    } else {
                        return html_entity_decode(sprintf( '%dx de %s', $i, wp_strip_all_tags( wc_price(($final_total / $i)))));
                    }
                }

                break;
        }
    }

    /**
     * Obtém as credenciais de um gateway específico
     */
    final public static function get_gateway_credentials($gateway_id)
    {
        $gateway_settings = get_option('woocommerce_' . $gateway_id . '_settings', array());
        
        // Verificar se o gateway está habilitado
        if (!isset($gateway_settings['enabled']) || $gateway_settings['enabled'] !== 'yes') {
            return false;
        }
        
        // Verificar se as credenciais estão configuradas
        $pv = isset($gateway_settings['pv']) ? trim($gateway_settings['pv']) : '';
        $token = isset($gateway_settings['token']) ? trim($gateway_settings['token']) : '';
        $environment = isset($gateway_settings['environment']) ? $gateway_settings['environment'] : 'test';
        
        if (empty($pv) || empty($token)) {
            return false;
        }
        
        return array(
            'pv' => $pv,
            'token' => $token,
            'environment' => $environment
        );
    }

    /**
     * Gera Basic Authorization para um gateway específico
     */
    final public static function generate_basic_auth($gateway_id)
    {
        $credentials = self::get_gateway_credentials($gateway_id);
        
        if ($credentials === false) {
            return false;
        }
        
        return base64_encode($credentials['pv'] . ':' . $credentials['token']);
    }

    /**
     * Gera token OAuth2 para API Rede v2 usando credenciais específicas de um gateway
     */
    final public static function generate_rede_oauth_token_for_gateway($gateway_id)
    {
        $credentials = self::get_gateway_credentials($gateway_id);
        
        if ($credentials === false) {
            return false;
        }
        
        $auth = base64_encode($credentials['pv'] . ':' . $credentials['token']);
        $environment = $credentials['environment'];
        
        $oauth_url = $environment === 'production' 
            ? 'https://api.userede.com.br/redelabs/oauth2/token'
            : 'https://rl7-sandbox-api.useredecloud.com.br/oauth2/token';

        $oauth_response = wp_remote_post($oauth_url, array(
            'method' => 'POST',
            'headers' => array(
                'Authorization' => 'Basic ' . $auth,
                'Content-Type' => 'application/x-www-form-urlencoded'
            ),
            'body' => 'grant_type=client_credentials',
            'timeout' => 30
        ));
        
        if (is_wp_error($oauth_response)) {
            return false;
        }
        
        $oauth_body = wp_remote_retrieve_body($oauth_response);
        $oauth_data = json_decode($oauth_body, true);

        error_log(json_encode($oauth_data));
        
        if (!isset($oauth_data['access_token'])) {
            return false;
        }
        
        return $oauth_data;
    }

    /**
     * Salva token OAuth2 específico de um gateway no cache
     */
    final public static function cache_rede_oauth_token_for_gateway($gateway_id, $token_data, $environment)
    {
        $cache_data = array(
            'token' => $token_data['access_token'],
            'expires_in' => $token_data['expires_in'],
            'generated_at' => time(),
            'environment' => $environment,
            'gateway_id' => $gateway_id
        );
        
        // Codifica em base64 para segurança
        $encoded_data = base64_encode(json_encode($cache_data));
        
        $option_name = 'lkn_rede_oauth_token_' . $gateway_id . '_' . $environment;
        update_option($option_name, $encoded_data);
        
        return $cache_data;
    }

    /**
     * Recupera token OAuth2 específico de um gateway do cache
     */
    final public static function get_cached_rede_oauth_token_for_gateway($gateway_id, $environment)
    {
        $option_name = 'lkn_rede_oauth_token_' . $gateway_id . '_' . $environment;
        $cached_data = get_option($option_name, '');
        
        if (empty($cached_data)) {
            return null;
        }
        
        // Decodifica do base64
        $decoded_data = json_decode(base64_decode($cached_data), true);
        
        if (!$decoded_data || !isset($decoded_data['token']) || !isset($decoded_data['generated_at'])) {
            return null;
        }
        
        return $decoded_data;
    }

    /**
     * Obtém token OAuth2 válido específico de um gateway
     */
    final public static function get_rede_oauth_token_for_gateway($gateway_id)
    {
        $credentials = self::get_gateway_credentials($gateway_id);
        
        if ($credentials === false) {
            return null;
        }
        
        $environment = $credentials['environment'];
        
        // Tenta recuperar do cache
        $cached_token = self::get_cached_rede_oauth_token_for_gateway($gateway_id, $environment);
        
        // Se token está válido, retorna ele
        if ($cached_token && self::is_rede_oauth_token_valid($cached_token)) {
            return $cached_token['token'];
        }
        
        // Token não existe ou expirou, tenta gerar novo
        $token_data = self::generate_rede_oauth_token_for_gateway($gateway_id);
        
        // Se falhou ao gerar novo token
        if ($token_data === false) {
            // Se há um token em cache (mesmo expirado), usa ele como fallback
            if ($cached_token && isset($cached_token['token'])) {
                return $cached_token['token'];
            }
            
            // Se não há token em cache, retorna null para forçar erro na API
            return null;
        }
        
        // Salva o novo token no cache
        self::cache_rede_oauth_token_for_gateway($gateway_id, $token_data, $environment);
        
        return $token_data['access_token'];
    }

    /**
     * Força renovação dos tokens OAuth2 para todos os gateways configurados
     */
    final public static function refresh_all_rede_oauth_tokens()
    {
        $gateways = array('rede_credit', 'rede_debit', 'integration_rede_pix', 'rede_pix');
        $renewed_count = 0;
        
        foreach ($gateways as $gateway_id) {
            $credentials = self::get_gateway_credentials($gateway_id);
            
            if ($credentials === false) {
                continue;
            }
            
            $environment = $credentials['environment'];
            $token_data = self::generate_rede_oauth_token_for_gateway($gateway_id);
            
            if ($token_data === false) {
                continue;
            }
            
            self::cache_rede_oauth_token_for_gateway($gateway_id, $token_data, $environment);
            $renewed_count++;
        }
        
        return $renewed_count;
    }

    /**
     * Verifica e renova apenas tokens OAuth2 expirados com base em tempo limite
     * 
     * @param int $expiry_minutes Minutos após criação para considerar token expirado
     * @return int Número de tokens renovados
     */
    final public static function refresh_expired_rede_oauth_tokens($expiry_minutes = 15)
    {
        $gateways = array('rede_credit', 'rede_debit', 'integration_rede_pix', 'rede_pix');
        $renewed_count = 0;
        $expiry_seconds = $expiry_minutes * 60;
        
        foreach ($gateways as $gateway_id) {
            $credentials = self::get_gateway_credentials($gateway_id);
            
            if ($credentials === false) {
                continue;
            }
            
            $environment = $credentials['environment'];
            $token_option_name = 'lkn_rede_oauth_token_' . $gateway_id . '_' . $environment;
            $cached_data = get_option($token_option_name, false);
            
            $should_refresh = false;
            
            if ($cached_data === false || empty($cached_data)) {
                // Token não existe, precisa gerar
                $should_refresh = true;
            } else {
                // Decodifica token do cache
                $cached_token = json_decode(base64_decode($cached_data), true);
                
                if (!$cached_token) {
                    // Token corrompido, precisa renovar
                    $should_refresh = true;
                } else {
                    // Verifica se token expirou baseado no tempo
                    $token_created = isset($cached_token['generated_at']) ? $cached_token['generated_at'] : 0;
                    $time_elapsed = time() - $token_created;
                    
                    if ($time_elapsed >= $expiry_seconds) {
                        $should_refresh = true;
                    }
                }
            }
            
            if ($should_refresh) {
                $token_data = self::generate_rede_oauth_token_for_gateway($gateway_id);
                
                if ($token_data !== false) {
                    self::cache_rede_oauth_token_for_gateway($gateway_id, $token_data, $environment);
                    $renewed_count++;
                }
            }
        }
        
        return $renewed_count;
    }

    /**
     * Gera token OAuth2 para API Rede v2 (mantido para compatibilidade)
     * @deprecated Use generate_rede_oauth_token_for_gateway() instead
     */
    final public static function generate_rede_oauth_token($environment = 'test')
    {
        // Tenta usar credenciais do gateway de crédito como padrão
        $credentials = self::get_gateway_credentials('rede_credit');
        
        if ($credentials === false) {
            // Se crédito não está configurado, tenta débito
            $credentials = self::get_gateway_credentials('rede_debit');
        }
        
        if ($credentials === false) {
            return false;
        }
        
        $oauth_url = $environment === 'production' 
            ? 'https://api.userede.com.br/redelabs/oauth2/token'
            : 'https://rl7-sandbox-api.useredecloud.com.br/oauth2/token';

        $auth = base64_encode($credentials['pv'] . ':' . $credentials['token']);

        $oauth_response = wp_remote_post($oauth_url, array(
            'method' => 'POST',
            'headers' => array(
                'Authorization' => 'Basic ' . $auth,
                'Content-Type' => 'application/x-www-form-urlencoded'
            ),
            'body' => 'grant_type=client_credentials',
            'timeout' => 30
        ));
        
        if (is_wp_error($oauth_response)) {
            return false;
        }
        
        $oauth_body = wp_remote_retrieve_body($oauth_response);
        $oauth_data = json_decode($oauth_body, true);
        
        if (!isset($oauth_data['access_token'])) {
            return false;
        }
        
        return $oauth_data;
    }

    /**
     * Salva token OAuth2 no cache com timestamp
     */
    final public static function cache_rede_oauth_token($token_data, $environment = 'test')
    {
        $cache_data = array(
            'token' => $token_data['access_token'],
            'expires_in' => $token_data['expires_in'],
            'generated_at' => time(),
            'environment' => $environment
        );
        
        // Codifica em base64 para segurança
        $encoded_data = base64_encode(json_encode($cache_data));
        
        $option_name = 'lkn_rede_oauth_token_' . $environment;
        update_option($option_name, $encoded_data);
        
        return $cache_data;
    }

    /**
     * Recupera token OAuth2 do cache
     */
    final public static function get_cached_rede_oauth_token($environment = 'test')
    {
        $option_name = 'lkn_rede_oauth_token_' . $environment;
        $cached_data = get_option($option_name, '');
        
        if (empty($cached_data)) {
            return null;
        }
        
        // Decodifica do base64
        $decoded_data = json_decode(base64_decode($cached_data), true);
        
        if (!$decoded_data || !isset($decoded_data['token']) || !isset($decoded_data['generated_at'])) {
            return null;
        }
        
        return $decoded_data;
    }

    /**
     * Verifica se o token está válido (não expirou)
     */
    final public static function is_rede_oauth_token_valid($cached_token)
    {
        if (!$cached_token || !isset($cached_token['generated_at'])) {
            return false;
        }
        
        $current_time = time();
        $token_age_minutes = ($current_time - $cached_token['generated_at']) / 60;
        
        // Token é válido se tem menos de 20 minutos (margem de segurança)
        return $token_age_minutes < 20;
    }

    /**
     * Obtém token OAuth2 válido (mantido para compatibilidade)
     * @deprecated Use get_rede_oauth_token_for_gateway() instead
     */
    final public static function get_rede_oauth_token($environment = 'test')
    {
        // Tenta recuperar do cache
        $cached_token = self::get_cached_rede_oauth_token($environment);
        
        // Se token está válido, retorna ele
        if ($cached_token && self::is_rede_oauth_token_valid($cached_token)) {
            return $cached_token['token'];
        }
        
        // Token não existe ou expirou, tenta gerar novo
        $token_data = self::generate_rede_oauth_token($environment);
        
        // Se falhou ao gerar novo token
        if ($token_data === false) {
            // Se há um token em cache (mesmo expirado), usa ele como fallback
            if ($cached_token && isset($cached_token['token'])) {
                return $cached_token['token'];
            }
            
            // Se não há token em cache, retorna null para forçar erro na API
            return null;
        }
        
        // Salva o novo token no cache
        self::cache_rede_oauth_token($token_data, $environment);
        
        return $token_data['access_token'];
    }

    /**
     * Força renovação do token OAuth2 (mantido para compatibilidade)
     * @deprecated Use refresh_all_rede_oauth_tokens() instead
     */
    final public static function refresh_rede_oauth_token($environment = 'test')
    {
        $token_data = self::generate_rede_oauth_token($environment);
        
        // Se falhou ao gerar novo token, mantém o cache atual
        if ($token_data === false) {
            return false;
        }
        
        self::cache_rede_oauth_token($token_data, $environment);
        
        return $token_data['access_token'];
    }
}
?>