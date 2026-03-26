<?php

namespace Lknwoo\IntegrationRedeForWoocommerce\Admin;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://linknacional.com.br
 * @since      1.0.0
 *
 * @package    LknIntegrationRedeForWoocommerce
 * @subpackage LknIntegrationRedeForWoocommerce/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    LknIntegrationRedeForWoocommerce
 * @subpackage LknIntegrationRedeForWoocommerce/admin
 * @author     Link Nacional <contato@linknacional.com>
 */
final class LknIntegrationRedeForWoocommerceAdmin
{
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles(): void
    {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/lkn-integration-rede-for-woocommerce-admin.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts(): void
    {
        wp_enqueue_script('lknIntegrationRedeForWoocommerceProFields', plugin_dir_url(__FILE__) . 'js/lkn-integration-rede-for-woocommerce-admin-pro-fields.js', array('jquery'), $this->version, false);

        // Só enfileira o script se a versão PRO estiver desativada
        if (!is_plugin_active('rede-for-woocommerce-pro/rede-for-woocommerce-pro.php')) {
            wp_enqueue_script('lknIntegrationRedeForWoocommerceProInstallments', plugin_dir_url(__FILE__) . 'js/lkn-integration-rede-for-woocommerce-admin-pro-installments.js', array('jquery'), $this->version, false);
        }

        wp_localize_script('lknIntegrationRedeForWoocommerceProFields', 'lknPhpProFieldsVariables', array(
            'proSettings' => __('PRO Settings', 'woo-rede'),
            'license' => __('License', 'woo-rede'),
            'currency' => __('Currency Converter', 'woo-rede'),
            'currencyQuote' => __('Currency Quote', 'woo-rede'),
            'autoCapture' => __('Auto Capture', 'woo-rede'),
            'autoCaptureLabel' => __('Enables auto capture', 'woo-rede'),
            'customCssShortcode' => __('Custom CSS (Shortcode)', 'woo-rede'),
            'customCssBlockEditor' => __('Custom CSS (Block Editor)', 'woo-rede'),
            'interestOnInstallments' => __('Interest on installments', 'woo-rede'),
            'interestOnInstallmentsDescTip' => __('Select the option interest or discount. Save to continue configuration.', 'woo-rede'),
            'licenseDescTip' => __('License for Rede for WooCommerce plugin extensions.', 'woo-rede'),
            'currencyDescTip' => __('If enabled, automatically converts the order amount to BRL when processing payment.', 'woo-rede'),
            'currencyQuoteDescTip' => __('These are the real-time exchange rates, indicating the value of each listed foreign currency in Brazilian Reais (BRL).', 'woo-rede'),
            'autoCaptureDescTip' => __('By enabling automatic capture, payment is automatically captured immediately after the transaction.', 'woo-rede'),
            'customCssShortcodeDescTip' => __('Possibility to customize the shortcode CSS. Enter the selector and rules, example: .checkout{color:green;}.', 'woo-rede'),
            'customCssBlockEditorDescTip' => __('Possibility to customize the CSS in the block editor checkout. Enter the selector and rules, example: .checkout{color:green;}.', 'woo-rede'),
            'becomePRO' => __('PRO', 'woo-rede'),
            'licenseDescription' => __('License for Rede plugin extensions.', 'woo-rede'),
            'currencyDescription' => __('Automatically converts payment amounts to BRL.', 'woo-rede'),
            'autoCaptureDescription' => __('Automatically captures the payment once authorized by Rede.', 'woo-rede'),
            'autoCaptureDebitLabel' => __('Enable automatic capture for credit card transactions', 'woo-rede'),
            'customCssShortcodeDescription' => __('Define CSS rules for the shortcode.', 'woo-rede'),
            'customCssBlockEditorDescription' => __('Define CSS rules for the block editor.', 'woo-rede'),
            'interestOnInstallmentsDescription' => __('Enables payment with interest in installments. Save to continue configuration. After enabling installment interest, you can define the amount of interest according to the installment.', 'woo-rede'),
            'licenseDataDescription' => __('Save to enable other options.', 'woo-rede'),
            'quoteDataDescription' => __('These are the real-time exchange rates, indicating the value of each listed foreign currency in Brazilian Reais (BRL).', 'woo-rede'),
            'autoCaptureDataDescription' => __('Automatically captures the payment once authorized by Rede.', 'woo-rede'),
            'cssShortcodeDataDescription' => __('Customize the Shortcode CSS using selectors and rules.', 'woo-rede'),
            'cssBlockEditorDataDescription' => __('Customize the Block Editor CSS using selectors and rules.', 'woo-rede'),
            'installmentInterestDataDescription' => __('Applies an interest rate to each installment. Use this if you want to charge extra per installment.', 'woo-rede'),
        ));

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/lkn-integration-rede-for-woocommerce-admin.js', array('jquery'), $this->version, false);

        // Localize the script with custom data
        wp_localize_script($this->plugin_name, 'lknPhpVariables', array(
            'plugin_slug' => 'invoice-payment-for-woocommerce',
            'install_nonce' => wp_create_nonce('install-plugin_invoice-payment-for-woocommerce'),
            'invoice_plugin_installed' => is_plugin_active('invoice-payment-for-woocommerce/invoice-payment-for-woocommerce.php'),
            'isProActive' => is_plugin_active('rede-for-woocommerce-pro/rede-for-woocommerce-pro.php'),
            'whatsapp_number' => LKN_WC_REDE_WPP_NUMBER,
            'site_url' => get_site_url(),
        ));

        $gateways = array(
            'maxipago_credit',
            'maxipago_debit',
            'rede_google_pay',
            'rede_credit',
            'rede_debit',
            'maxipago_pix',
            'rede_pix',
            'integration_rede_pix'
        );

        $page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
        $tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : '';
        $section = isset($_GET['section']) ? sanitize_text_field(wp_unslash($_GET['section'])) : '';

        $versions = __('Plugin Rede API v', 'woo-rede') . INTEGRATION_REDE_FOR_WOOCOMMERCE_VERSION . ' | ' . __('PRO v', 'woo-rede') . 2.1;
        if (defined('REDE_FOR_WOOCOMMERCE_PRO_VERSION')) {
            $versions = __('Plugin Rede API v', 'woo-rede') . INTEGRATION_REDE_FOR_WOOCOMMERCE_VERSION . ' | ' . __('PRO v', 'woo-rede') . REDE_FOR_WOOCOMMERCE_PRO_VERSION;
        }

        if (isset($_GET['section']) && sanitize_text_field(wp_unslash($_GET['section'])) === 'integration_rede_pix') {
            wp_enqueue_script(
                $this->plugin_name . '-pix-settings',
                plugin_dir_url(__FILE__) . 'js/lkn-integration-rede-for-woocommerce-pix-settings.js',
                array('jquery'),
                $this->version,
                false
            );
        }

        if (isset($_GET['section']) && in_array(sanitize_text_field(wp_unslash($_GET['section'])), $gateways, true)) {
            wp_enqueue_script(
                $this->plugin_name . '-plugin-rate',
                plugin_dir_url(__FILE__) . 'js/lkn-integration-rede-for-woocommerce-plugin-rate.js',
                array('jquery'),
                $this->version,
                false
            );
        }

        if ('wc-settings' === $page && 'checkout' === $tab && in_array($section, $gateways, true)) {
            wp_enqueue_script('lknIntegrationRedeForWoocommerceAdminClearLogsButton', plugin_dir_url(__FILE__) . 'js/lkn-integration-rede-for-woocommerce-admin-clear-logs-button.js', array('jquery'), $this->version, false);
            wp_enqueue_script('lknIntegrationRedeForWoocommerceSettingsLayoutScript', plugin_dir_url(__FILE__) . 'js/lkn-integration-rede-for-woocommerce-settings-layout.js', array('jquery'), $this->version, false);
            wp_enqueue_script('lknIntegrationRedeForWoocommerceCard', plugin_dir_url(__FILE__) . 'js/lkn-integration-rede-for-woocommerce-admin-card.js', array('jquery'), $this->version, false);
            wc_get_template(
                'adminCard/adminSettingsCard.php',
                array(
                    'backgrounds' => array(
                        'right' => plugin_dir_url(__FILE__) . 'images/backgroundCardRight.svg',
                        'left' => plugin_dir_url(__FILE__) . 'images/backgroundCardLeft.svg'
                    ),
                    'logo' => plugin_dir_url(__FILE__) . 'images/linkNacionalLogo.webp',
                    'stars' => plugin_dir_url(__FILE__) . 'images/stars.svg',
                    'whatsapp' => plugin_dir_url(__FILE__) . 'images/whatsapp.svg',
                    'telegram' => plugin_dir_url(__FILE__) . 'images/telegram.svg',
                    'versions' => $versions

                ),
                'woocommerce/adminSettingsCard/',
                plugin_dir_path(__FILE__) . '../Includes/templates/'
            );
            wp_localize_script('lknIntegrationRedeForWoocommerceAdminClearLogsButton', 'lknWcRedeTranslations', array(
                'clearLogs' => __('Limpar Logs', 'woo-rede'),
                'sendConfigs' => __('Wordpress Support', 'woo-rede'),
                'alertText' => __('Deseja realmente deletar todos logs dos pedidos?', 'woo-rede')
            ));
            wp_localize_script('lknIntegrationRedeForWoocommerceSettingsLayoutScript', 'lknWcRedeLayoutSettings', array(
                'basic' => plugin_dir_url(__FILE__) . 'images/basicTemplate.png',
                'modern' => plugin_dir_url(__FILE__) . 'images/modernTemplate.png',
            ));

            $gateway_settings = get_option('woocommerce_' . $section . '_settings', array());
            wp_localize_script('lknIntegrationRedeForWoocommerceSettingsLayoutScript', 'lknWcRedeTranslationsInput', array(
                'analytics_url' => admin_url('admin.php?page=wc-admin&path=%2Fanalytics%2Frede-transactions'),
                'gateway_settings' => $gateway_settings,
                'whatsapp_number' => LKN_WC_REDE_WPP_NUMBER,
                'site_domain' => home_url(),
                'gateway_id' => $section,
                'version_free' => defined('INTEGRATION_REDE_FOR_WOOCOMMERCE_VERSION') ? INTEGRATION_REDE_FOR_WOOCOMMERCE_VERSION : 'N/A',
                'version_pro' => defined('REDE_FOR_WOOCOMMERCE_PRO_VERSION') ? REDE_FOR_WOOCOMMERCE_PRO_VERSION : 'N/A',
                'endpointStatus' => get_option('lknRedeForWoocommerceProEndpointStatus', false)
            ));
        }

        if ('wc-settings' === $page && 'checkout' === $tab && $section === 'rede_google_pay' && in_array($section, $gateways, true)) {
            // Registrar script para geração de chaves Google Pay
            wp_enqueue_script( 
                $this->plugin_name . '-keys', 
                plugin_dir_url( __FILE__ ) . 'js/LknRedeForWoocommerceProKeys.js', 
                array('jquery'), 
                $this->version, 
                false 
            );
            
            // Localizar variáveis AJAX para o script de chaves
            wp_localize_script($this->plugin_name . '-keys', 'lkn_keys_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('lkn_keys_ajax_nonce'),
                'translations' => array(
                    'generating' => __('Generating...', 'woo-rede'),
                    'generate_new_keys' => __('Generate New Keys', 'woo-rede'),
                    'success_message' => __('New keys generated successfully!', 'woo-rede'),
                    'error_message' => __('Error generating new keys.', 'woo-rede'),
                    'communication_error' => __('Communication error with the server.', 'woo-rede')
                ),
            ));
        }
    }

    /**
     * Add bulk actions for WooCommerce orders page
     *
     * @since    1.0.0
     */
    public function add_bulk_order_actions($bulk_actions)
    {
        $bulk_actions['export_rede_xls'] = __('Exportar Rede XLS', 'woo-rede');
        return $bulk_actions;
    }

    /**
     * Handle bulk action for export orders to XLS
     *
     * @since    1.0.0
     */
    public function handle_bulk_export_rede_xls($redirect_to, $action, $order_ids)
    {
        if ($action !== 'export_rede_xls') {
            return $redirect_to;
        }

        if (empty($order_ids)) {
            return $redirect_to;
        }

        // Check user permissions
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('Você não tem permissão para exportar pedidos.', 'woo-rede'));
        }

        // Generate XLS file
        $this->export_orders_to_xls($order_ids);

        return $redirect_to;
    }

    /**
     * Export orders to XLS format
     *
     * @since    1.0.0
     */
    private function export_orders_to_xls($order_ids)
    {
        // Set content type for Excel download
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="exportacao_rede_' . date('Y-m-d_H-i-s') . '.xls"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Start output with UTF-8 BOM for proper encoding
        echo "\xEF\xBB\xBF";

        // Generate CSV content that Excel will interpret as XLS
        $this->generate_xls_content($order_ids);

        exit;
    }

    /**
     * Generate XLS content for orders
     *
     * @since    1.0.0
     */
    private function generate_xls_content($order_ids)
    {
        // CSV delimiter
        $delimiter = "\t"; // Tab delimiter works better with Excel

        // Predefined column headers
        $headers = array(
            'ID do Pedido',
            'Data do Pedido',
            'Status do Pedido',
            'Total do Pedido',
            'Método de Pagamento',
            'Nome do Cliente',
            'Email do Cliente',
            'Telefone do Cliente',
            'CPF/CNPJ',
            'Tipo de Pessoa'
        );

        // Get Rede gateway metadata fields
        $rede_meta_fields = array(
            '_wc_rede_card_type' => 'Tipo do Cartão',
            '_wc_rede_installments' => 'Parcelas',
            '_wc_rede_transaction_card_number' => 'Número do Cartão',
            '_wc_rede_transaction_last4' => 'Últimos 4 dígitos',
            '_wc_rede_transaction_bin' => 'BIN do Cartão',
            '_wc_rede_transaction_expiration_month' => 'Mês de Expiração',
            '_wc_rede_transaction_expiration_year' => 'Ano de Expiração',
            '_wc_rede_transaction_holder' => 'Portador do Cartão',
            '_wc_rede_transaction_refund_id' => 'ID do Reembolso',
            '_wc_rede_transaction_cancel_id' => 'ID do Cancelamento',
            '_wc_rede_pix_integration_time_expiration' => 'Expiração PIX',
            '_wc_rede_transaction_id' => 'ID da Transação Rede',
            '_wc_rede_transaction_return_code' => 'Código de Retorno',
            '_wc_rede_transaction_return_message' => 'Mensagem de Retorno',
            '_wc_rede_transaction_authorization_code' => 'Código de Autorização',
            '_wc_rede_transaction_nsu' => 'NSU',
            '_wc_rede_transaction_amount' => 'Valor da Transação',
            '_wc_rede_pix_qr_code' => 'QR Code PIX',
            '_wc_rede_pix_txid' => 'TXID PIX'
        );

        // Adicionar campos específicos do lkn_rede_transaction_data
        $transaction_data_fields = array(
            'lkn_rede_gateway_type' => 'Tipo Gateway',
            'lkn_rede_gateway_brand' => 'Bandeira',
            'lkn_rede_gateway_masked' => 'Cartão Mascarado',
            'lkn_rede_gateway_expiry' => 'Validade',
            'lkn_rede_transaction_tid' => 'TID',
            'lkn_rede_transaction_capture' => 'Captura',
            'lkn_rede_transaction_recurrent' => 'Recorrente',
            'lkn_rede_transaction_installment_amount' => 'Valor da Parcela',
            'lkn_rede_amounts_subtotal' => 'Subtotal',
            'lkn_rede_amounts_shipping' => 'Frete',
            'lkn_rede_amounts_interest_discount' => 'Juros/Desconto',
            'lkn_rede_system_environment' => 'Ambiente',
            'lkn_rede_system_gateway' => 'Gateway Sistema',
            'lkn_rede_system_reference' => 'Referência',
            'lkn_rede_response_http_status' => 'Status HTTP'
        );

        // Combinar todos os campos de metadados
        $rede_meta_fields = array_merge($rede_meta_fields, $transaction_data_fields);

        // Add Rede metadata headers
        foreach ($rede_meta_fields as $meta_key => $label) {
            $headers[] = $label;
        }

        // Process first order to get max products
        $max_products = 0;

        foreach ($order_ids as $order_id) {
            if (!$order_id) continue;

            $order = wc_get_order($order_id);
            if (!$order) continue;

            $items = $order->get_items();
            $product_count = count($items);
            if ($product_count > $max_products) {
                $max_products = $product_count;
            }
        }

        // Add product headers
        for ($i = 1; $i <= $max_products; $i++) {
            $headers[] = "ID do Produto #{$i}";
            $headers[] = "Nome do Produto #{$i}";
            $headers[] = "Quantidade #{$i}";
            $headers[] = "Preço #{$i}";
            $headers[] = "Metadados do Produto #{$i}";
        }

        // Escape headers and output
        $escaped_headers = array();
        foreach ($headers as $header) {
            $escaped_headers[] = $this->escape_csv_field($header);
        }
        echo implode($delimiter, $escaped_headers) . "\n";

        // Process each order
        foreach ($order_ids as $order_id) {
            if (!$order_id) continue;

            $order = wc_get_order($order_id);
            if (!$order) continue;

            $row = array();

            // Basic order data
            $row[] = $order->get_id();
            $row[] = $order->get_date_created()->date('Y-m-d H:i:s');
            $row[] = $order->get_status();
            $row[] = $order->get_total();
            $row[] = $order->get_payment_method_title();

            // Customer data
            $row[] = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
            $row[] = $order->get_billing_email();
            $row[] = $order->get_billing_phone();

            // CPF/CNPJ field
            $cpf = $order->get_meta('_billing_cpf');
            $cnpj = $order->get_meta('_billing_cnpj');
            $row[] = $cpf ?: $cnpj ?: '';

            // Person type
            $person_type = $order->get_meta('_billing_persontype');
            $person_type_label = '';
            switch ($person_type) {
                case '1':
                    $person_type_label = 'Pessoa Física';
                    break;
                case '2':
                    $person_type_label = 'Pessoa Jurídica';
                    break;
                case '0':
                    $person_type_label = 'Nenhum';
                    break;
                default:
                    $person_type_label = '';
            }
            $row[] = $person_type_label;

            // Rede metadata
            $transaction_data = $order->get_meta('lkn_rede_transaction_data');
            $parsed_transaction_data = array();
            
            // Parse transaction data if available
            if (!empty($transaction_data)) {
                $parsed_transaction_data = is_string($transaction_data) ? json_decode($transaction_data, true) : $transaction_data;
                if (!is_array($parsed_transaction_data)) {
                    $parsed_transaction_data = array();
                }
            }

            foreach ($rede_meta_fields as $meta_key => $label) {
                $meta_value = $order->get_meta($meta_key);
                
                // Se o metadado não existir, tentar extrair do lkn_rede_transaction_data
                if (empty($meta_value) && !empty($parsed_transaction_data)) {
                    $meta_value = $this->extract_from_transaction_data($meta_key, $parsed_transaction_data);
                }
                
                $row[] = $meta_value ?: '';
            }

            // Product data
            $items = $order->get_items();
            $item_index = 0;

            foreach ($items as $item) {
                $item_index++;
                $product_id = $item->get_product_id();
                $product = $item->get_product();

                $row[] = $product_id;
                $row[] = $item->get_name();
                $row[] = $item->get_quantity();
                $row[] = $item->get_total();

                // Consolidated product metadata
                $product_metadata = '';
                if ($product) {
                    $product_metadata = $this->get_consolidated_product_metadata($product, $item->get_name());
                }
                $row[] = $product_metadata;
            }

            // Fill remaining product columns if this order has fewer products than max
            $remaining_products = $max_products - $item_index;
            for ($i = 0; $i < $remaining_products; $i++) {
                $row[] = ''; // Product ID
                $row[] = ''; // Product Name
                $row[] = ''; // Quantity
                $row[] = ''; // Price
                $row[] = ''; // Product Metadata
            }

            // Convert all fields to UTF-8 and escape properly
            $escaped_row = array();
            foreach ($row as $field) {
                $escaped_row[] = $this->escape_csv_field($field);
            }

            // Output row
            echo implode($delimiter, $escaped_row) . "\n";
        }
    }

    /**
     * Escape field for CSV/XLS export
     *
     * @since    1.0.0
     */
    private function escape_csv_field($field)
    {
        // Convert to string and ensure UTF-8
        $field = (string) $field;
        $field = mb_convert_encoding($field, 'UTF-8', 'auto');
        
        // Escape double quotes by doubling them
        $field = str_replace('"', '""', $field);
        
        // If field contains delimiter, quotes, or newlines, wrap in quotes
        if (strpos($field, "\t") !== false || strpos($field, '"') !== false || strpos($field, "\n") !== false || strpos($field, "\r") !== false) {
            $field = '"' . $field . '"';
        }

        return $field;
    }

    /**
     * Extract value from lkn_rede_transaction_data array
     *
     * @since    1.0.0
     */
    private function extract_from_transaction_data($meta_key, $transaction_data)
    {
        if (!is_array($transaction_data)) {
            return '';
        }

        // Mapeamento dos campos de metadados para as chaves do transaction_data
        $mapping = array(
            'lkn_rede_gateway_type' => 'gateway.type',
            'lkn_rede_gateway_brand' => 'gateway.brand', 
            'lkn_rede_gateway_masked' => 'gateway.masked',
            'lkn_rede_gateway_expiry' => 'gateway.expiry',
            'lkn_rede_transaction_tid' => 'transaction.tid',
            'lkn_rede_transaction_capture' => 'transaction.capture',
            'lkn_rede_transaction_recurrent' => 'transaction.recurrent',
            'lkn_rede_transaction_installment_amount' => 'transaction.installment_amount',
            'lkn_rede_amounts_subtotal' => 'amounts.subtotal',
            'lkn_rede_amounts_shipping' => 'amounts.shipping',
            'lkn_rede_amounts_interest_discount' => 'amounts.interest_discount',
            'lkn_rede_system_environment' => 'system.environment',
            'lkn_rede_system_gateway' => 'system.gateway',
            'lkn_rede_system_reference' => 'system.reference',
            'lkn_rede_response_http_status' => 'response.http_status',
            // Mapeamento para campos existentes que podem estar no transaction_data
            '_wc_rede_transaction_nsu' => 'transaction.nsu',
            '_wc_rede_transaction_authorization_code' => 'transaction.authorization_code',
            '_wc_rede_installments' => 'transaction.installments',
            '_wc_rede_transaction_holder' => 'gateway.holder_name'
        );

        if (!isset($mapping[$meta_key])) {
            return '';
        }

        $path = $mapping[$meta_key];
        $keys = explode('.', $path);
        $value = $transaction_data;

        // Navegar pela estrutura aninhada
        foreach ($keys as $key) {
            if (!isset($value[$key])) {
                return '';
            }
            $value = $value[$key];
        }

        return (string) $value;
    }

    /**
     * Get consolidated product metadata in "key: value" format
     *
     * @since    1.0.0
     */
    private function get_consolidated_product_metadata($product, $product_name)
    {
        if (!$product) {
            return '';
        }

        // Metadados que devem ser excluídos
        $excluded_meta_keys = array(
            'lkn_wcip_subscription_limit',
            'lkn_wcip_subscription_interval_type',
            'lkn_wcip_subscription_interval_number',
            'lknMaxipagoProdutctInterest',
            'lknRedeProdutctInterest'
        );

        $meta_data = $product->get_meta_data();
        $metadata_strings = array();

        foreach ($meta_data as $meta) {
            $meta_info = $meta->get_data();
            $meta_key = $meta_info['key'];
            $meta_value = $meta_info['value'];

            // Pular metadados internos (começam com _) e excluídos
            if (str_starts_with($meta_key, '_') || in_array($meta_key, $excluded_meta_keys)) {
                continue;
            }

            // Pular se valor estiver vazio
            if (empty($meta_value)) {
                continue;
            }

            // Formatar como "chave: valor"
            $metadata_strings[] = $meta_key . ': ' . $meta_value;
        }

        return implode(' | ', $metadata_strings);
    }
}
