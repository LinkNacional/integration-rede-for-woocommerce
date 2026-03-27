<?php

namespace Lknwoo\IntegrationRedeForWoocommerce\Admin;

use Lknwoo\IntegrationRedeForWoocommerce\Includes\LknIntegrationRedeForWoocommerceHelper;

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
        // Só adiciona a ação de exportação se a licença PRO estiver válida
        if (LknIntegrationRedeForWoocommerceHelper::isProLicenseValid()) {
            $bulk_actions['export_rede_xls'] = __('Rede Orders Spreadsheet', 'woo-rede');
        }
        
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

        // Verificar se a licença PRO está válida
        if (!LknIntegrationRedeForWoocommerceHelper::isProLicenseValid()) {
            wp_die(esc_html__('Esta funcionalidade requer o plugin Rede PRO ativo com licença válida.', 'woo-rede'));
        }

        // Check user permissions
        if (!current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('Você não tem permissão para exportar pedidos.', 'woo-rede'));
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
        header('Content-Disposition: attachment; filename="exportacao_rede_' . gmdate('Y-m-d_H-i-s') . '.xls"');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

        // Output UTF-8 BOM for proper character encoding in Excel
        echo "\xEF\xBB\xBF";

        // Generate CSV content that Excel will interpret as XLS
        $this->generate_xls_content($order_ids);

        exit;
    }

    /**
     * Generate XLS content for orders - Sistema completo do zero
     *
     * @since    1.0.0
     */
    private function generate_xls_content($order_ids)
    {
        // CSV delimiter
        $delimiter = "\t"; // Tab delimiter works better with Excel

        // ===== PRIMEIRO PASSO: DESCOBRIR O NÚMERO MÁXIMO DE PRODUTOS =====
        $max_products = $this->get_max_products_in_orders($order_ids);

        // ===== DEFINIÇÃO COMPLETA DE COLUNAS =====
        $column_definitions = array(
            // 1. DADOS BÁSICOS DO PEDIDO (4 campos)
            array('header' => 'ID do Pedido', 'source' => 'order', 'field' => 'id'),
            array('header' => 'Data do Pedido', 'source' => 'order', 'field' => 'date_created'),
            array('header' => 'Status do Pedido', 'source' => 'order', 'field' => 'status'),
            array('header' => 'Método de Pagamento', 'source' => 'order', 'field' => 'payment_method_title'),
            
            // 2. DADOS DO CLIENTE (5 campos)
            array('header' => 'Nome do Cliente', 'source' => 'order', 'field' => 'billing_name'),
            array('header' => 'Email do Cliente', 'source' => 'order', 'field' => 'billing_email'),
            array('header' => 'Telefone do Cliente', 'source' => 'order', 'field' => 'billing_phone'),
            array('header' => 'CPF/CNPJ', 'source' => 'order', 'field' => 'billing_document'),
            array('header' => 'Tipo de Pessoa', 'source' => 'meta', 'meta_key' => '_billing_persontype', 'format' => 'person_type'),
            
            // 3. DADOS GATEWAY/CARTÃO (5 campos)
            array('header' => 'Cartão Mascarado', 'source' => 'toon', 'toon_path' => 'gateway.masked', 'meta_key' => 'lkn_rede_gateway_masked'),
            array('header' => 'Tipo Cartão', 'source' => 'toon', 'toon_path' => 'gateway.type', 'meta_key' => 'lkn_rede_gateway_type', 'format' => 'gateway_type'),
            array('header' => 'Bandeira', 'source' => 'toon', 'toon_path' => 'gateway.brand', 'meta_key' => 'lkn_rede_gateway_brand'),
            array('header' => 'Validade', 'source' => 'toon', 'toon_path' => 'gateway.expiry', 'meta_key' => 'lkn_rede_gateway_expiry'),
            array('header' => 'Portador', 'source' => 'toon', 'toon_path' => 'gateway.holder_name', 'meta_key' => '_wc_rede_transaction_holder'),
            
            // 4. DADOS TRANSAÇÃO (8 campos)
            array('header' => 'TID', 'source' => 'toon', 'toon_path' => 'transaction.tid', 'meta_key' => '_wc_rede_transaction_id'),
            array('header' => 'NSU', 'source' => 'toon', 'toon_path' => 'transaction.nsu', 'meta_key' => '_wc_rede_transaction_nsu'),
            array('header' => 'Código Autorização', 'source' => 'toon', 'toon_path' => 'transaction.authorization_code', 'meta_key' => '_wc_rede_transaction_authorization_code'),
            array('header' => 'Captura', 'source' => 'toon', 'toon_path' => 'transaction.capture', 'meta_key' => 'lkn_rede_transaction_capture', 'format' => 'yes_no'),
            array('header' => 'Recorrente', 'source' => 'toon', 'toon_path' => 'transaction.recurrent', 'meta_key' => 'lkn_rede_transaction_recurrent', 'format' => 'yes_no'),
            array('header' => 'Autenticação 3DS', 'source' => 'toon', 'toon_path' => 'transaction.3ds_auth', 'meta_key' => 'lkn_rede_transaction_3ds_auth', 'format' => 'yes_no'),
            array('header' => 'Parcelas', 'source' => 'toon', 'toon_path' => 'transaction.installments', 'meta_key' => '_wc_rede_installments'),
            array('header' => 'Valor Parcela', 'source' => 'toon', 'toon_path' => 'transaction.installment_amount', 'meta_key' => 'lkn_rede_transaction_installment_amount'),
            
            // 5. VALORES (5 campos)
            array('header' => 'Total Transação', 'source' => 'toon', 'toon_path' => 'amounts.total', 'meta_key' => '_wc_rede_transaction_amount'),
            array('header' => 'Subtotal Produtos', 'source' => 'toon', 'toon_path' => 'amounts.subtotal', 'meta_key' => 'lkn_rede_amounts_subtotal'),
            array('header' => 'Valor Frete', 'source' => 'toon', 'toon_path' => 'amounts.shipping', 'meta_key' => 'lkn_rede_amounts_shipping'),
            array('header' => 'Juros/Desconto', 'source' => 'toon', 'toon_path' => 'amounts.interest_discount', 'meta_key' => 'lkn_rede_amounts_interest_discount'),
            array('header' => 'Moeda', 'source' => 'toon', 'toon_path' => 'amounts.currency', 'meta_key' => 'lkn_rede_amounts_currency'),
            
            // 6. SISTEMA (4 campos)
            array('header' => 'Ambiente', 'source' => 'toon', 'toon_path' => 'system.environment', 'meta_key' => 'lkn_rede_system_environment', 'format' => 'environment'),
            array('header' => 'Gateway', 'source' => 'toon', 'toon_path' => 'system.gateway', 'meta_key' => 'lkn_rede_system_gateway'),
            array('header' => 'Referência', 'source' => 'toon', 'toon_path' => 'system.reference', 'meta_key' => 'lkn_rede_system_reference'),
            array('header' => 'Data/Hora Requisição', 'source' => 'toon', 'toon_path' => 'system.request_datetime', 'meta_key' => 'lkn_rede_system_request_datetime'),
            
            // 7. RESPOSTA (3 campos)
            array('header' => 'Status HTTP', 'source' => 'toon', 'toon_path' => 'response.http_status', 'meta_key' => 'lkn_rede_response_http_status'),
            array('header' => 'Código Retorno', 'source' => 'toon', 'toon_path' => 'response.return_code', 'meta_key' => '_wc_rede_transaction_return_code'),
            array('header' => 'Mensagem Retorno', 'source' => 'toon', 'toon_path' => 'response.return_message', 'meta_key' => '_wc_rede_transaction_return_message'),
            
            // 8. PIX (3 campos)
            array('header' => 'PIX QR Code', 'source' => 'toon', 'toon_path' => 'pix.qr_code', 'meta_key' => '_wc_rede_pix_qr_code'),
            array('header' => 'PIX TXID', 'source' => 'toon', 'toon_path' => 'pix.txid', 'meta_key' => '_wc_rede_pix_txid'),
            array('header' => 'PIX Expiração', 'source' => 'toon', 'toon_path' => 'pix.expiration', 'meta_key' => '_wc_rede_pix_integration_time_expiration')
        );

        // ===== ADICIONAR COLUNAS DINÂMICAS DE PRODUTOS =====
        for ($i = 1; $i <= $max_products; $i++) {
            $column_definitions[] = array('header' => "ID Produto #{$i}", 'source' => 'product_field', 'field' => 'id', 'product_index' => $i - 1);
            $column_definitions[] = array('header' => "Nome Produto #{$i}", 'source' => 'product_field', 'field' => 'name', 'product_index' => $i - 1);
            $column_definitions[] = array('header' => "Preço Produto #{$i}", 'source' => 'product_field', 'field' => 'price', 'product_index' => $i - 1);
            $column_definitions[] = array('header' => "Quantidade Produto #{$i}", 'source' => 'product_field', 'field' => 'quantity', 'product_index' => $i - 1);
            $column_definitions[] = array('header' => "Variáveis Produto #{$i}", 'source' => 'product_field', 'field' => 'attributes', 'product_index' => $i - 1);
        }

        // Output headers
        $headers = array();
        foreach ($column_definitions as $col) {
            $headers[] = $col['header'];
        }
        
        $escaped_headers = array();
        foreach ($headers as $header) {
            $escaped_headers[] = $this->escape_csv_field($header);
        }
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Data already escaped by escape_csv_field() method for XLS export
        echo implode($delimiter, $escaped_headers) . "\n";

        // ===== PROCESSAMENTO DOS PEDIDOS =====
        foreach ($order_ids as $order_id) {
            if (!$order_id) continue;

            $order = wc_get_order($order_id);
            if (!$order) continue;

            // Extrair dados TOON uma vez
            $transaction_data = $order->get_meta('lkn_rede_transaction_data');
            $data_format = $order->get_meta('lkn_rede_data_format');
            $parsed_data = array();
            
            if (!empty($transaction_data)) {
                $parsed_data = $this->parse_transaction_data($transaction_data, $data_format);
            }

            $row = array();

            // Processar cada coluna definida
            foreach ($column_definitions as $col) {
                $value = $this->extract_column_value($order, $parsed_data, $col);
                $row[] = $value;
            }

            // Output row
            $escaped_row = array();
            foreach ($row as $field) {
                $escaped_row[] = $this->escape_csv_field($field);
            }
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Data already escaped by escape_csv_field() method for XLS export
            echo implode($delimiter, $escaped_row) . "\n";
        }
    }

    /**
     * Get field value from parsed data or order meta
     */
    private function get_field_value($parsed_data, $field_path, $order, $meta_key)
    {
        // Try parsed data first
        if (!empty($parsed_data)) {
            $value = $this->get_nested_value($parsed_data, $field_path);
            if (!empty($value)) {
                return $value;
            }
        }
        
        // Fallback to order meta
        return $order->get_meta($meta_key);
    }

    /**
     * Extract column value based on definition
     */
    private function extract_column_value($order, $parsed_data, $column_def)
    {
        $value = '';

        // Extract raw value based on source
        switch ($column_def['source']) {
            case 'order':
                $value = $this->get_order_field($order, $column_def['field']);
                break;
                
            case 'meta':
                $value = $order->get_meta($column_def['meta_key']) ?: '';
                break;
                
            case 'toon':
                // Try TOON first, fallback to meta
                if (!empty($parsed_data) && !empty($column_def['toon_path'])) {
                    $value = $this->get_nested_value($parsed_data, $column_def['toon_path']);
                }
                if (empty($value) && !empty($column_def['meta_key'])) {
                    $value = $order->get_meta($column_def['meta_key']) ?: '';
                }
                break;
                
            case 'products':
                $items = $order->get_items();
                $value = $this->format_products_string($items);
                break;
                
            case 'product_field':
                $items = $order->get_items();
                $value = $this->get_product_field_value($items, $column_def['product_index'], $column_def['field']);
                break;
        }

        // Apply formatting if specified
        if (!empty($column_def['format'])) {
            $value = $this->apply_formatting($value, $column_def['format'], $order, $parsed_data);
        }

        // Always return string
        return strval($value ?: '');
    }

    /**
     * Get order field value
     */
    private function get_order_field($order, $field)
    {
        switch ($field) {
            case 'id':
                return $order->get_id();
                
            case 'date_created':
                return $order->get_date_created() ? $order->get_date_created()->date('Y-m-d H:i:s') : '';
                
            case 'status':
                return wc_get_order_status_name($order->get_status()) ?: '';
                
            case 'payment_method_title':
                return $order->get_payment_method_title() ?: '';
                
            case 'billing_name':
                return trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()) ?: '';
                
            case 'billing_email':
                return $order->get_billing_email() ?: '';
                
            case 'billing_phone':
                return $order->get_billing_phone() ?: '';
                
            case 'billing_document':
                $cpf = $order->get_meta('_billing_cpf');
                $cnpj = $order->get_meta('_billing_cnpj');
                return $cpf ?: ($cnpj ?: '');
                
            default:
                return '';
        }
    }

    /**
     * Apply formatting to value - TOON data já vem formatado, usar como está
     */
    private function apply_formatting($value, $format, $order = null, $parsed_data = array())
    {
        if (empty($value)) return '';

        switch ($format) {
            case 'person_type':
                // Só formatar se for código numérico
                return match ($value) {
                    '1' => 'Pessoa Física',
                    '2' => 'Pessoa Jurídica',
                    '0' => 'Nenhum',
                    default => $value // Se já está formatado, usar como está
                };
                
            case 'gateway_type':
                // Se já está em português, usar como está
                if (in_array($value, ['Crédito', 'Débito', 'PIX'])) {
                    return $value;
                }
                // Só formatar valores em inglês  
                $value = strtolower(trim($value));
                return match ($value) {
                    'credit', 'creditcard' => 'Crédito',
                    'debit', 'debitcard' => 'Débito', 
                    'pix' => 'PIX',
                    default => $value
                };
                
            case 'yes_no':
                // Se já está em português, usar como está
                if (in_array($value, ['Sim', 'Não', 'Sucesso', 'Falha'])) {
                    return $value;
                }
                // Só formatar valores em inglês ou booleanos
                if (is_bool($value)) {
                    return $value ? 'Sim' : 'Não';
                }
                $value = strtolower(trim($value));
                return ($value === 'true' || $value === '1' || $value === 'yes' || $value === 'sim' || $value === 'sucesso') ? 'Sim' : 'Não';
                
            case 'environment':
                // Se já está formatado, usar como está
                if (in_array($value, ['Sandbox', 'Produção'])) {
                    return $value;
                }
                // Só formatar valores em inglês
                $value = strtolower(trim($value));
                return match ($value) {
                    'sandbox', 'test', 'testing' => 'Sandbox',
                    'production', 'prod', 'live' => 'Produção',
                    default => $value
                };
                
            default:
                return strval($value);
        }
    }

    /**
     * Get product attributes as formatted string
     */
    private function get_product_attributes($product)
    {
        if (!$product) return '';
        
        $attributes = array();
        
        // Product attributes
        if ($product->get_attributes()) {
            foreach ($product->get_attributes() as $attribute_name => $attribute) {
                if (is_a($attribute, 'WC_Product_Attribute')) {
                    $attribute_label = wc_attribute_label($attribute_name);
                    
                    // Remove prefix "attribute_" if present
                    if (strpos(strtolower($attribute_label), 'attribute_') === 0) {
                        $attribute_label = substr($attribute_label, 10); // Remove "attribute_"
                    }
                    
                    $attribute_values = $attribute->get_options();
                    if (is_array($attribute_values)) {
                        $values = implode(', ', $attribute_values);
                        $attributes[] = $attribute_label . ': ' . $values;
                    }
                }
            }
        }
        
        // Variation attributes for variable products
        if ($product->is_type('variation')) {
            $variation_attributes = $product->get_variation_attributes();
            if (is_array($variation_attributes)) {
                foreach ($variation_attributes as $attr_name => $attr_value) {
                    $attr_label = wc_attribute_label($attr_name);
                    
                    // Remove prefix "attribute_" if present and clean up
                    if (strpos(strtolower($attr_label), 'attribute_') === 0) {
                        $attr_label = substr($attr_label, 10); // Remove "attribute_"
                    } elseif (strpos($attr_name, 'attribute_') === 0) {
                        $attr_label = substr($attr_name, 10); // Remove from original name
                        $attr_label = str_replace('pa_', '', $attr_label); // Remove taxonomy prefix
                        $attr_label = ucfirst($attr_label); // Capitalize first letter
                    }
                    
                    $attributes[] = $attr_label . ': ' . $attr_value;
                }
            }
        }
        
        // Always return string with proper formatting
        return implode(' - ', $attributes) ?: '';
    }

    /**
     * Get maximum number of products in any order from the given order IDs
     *
     * @since    1.0.0
     */
    private function get_max_products_in_orders($order_ids)
    {
        $max_products = 1; // Mínimo 1 produto
        
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
        
        return $max_products;
    }

    /**
     * Get specific field value for a product at given index
     *
     * @since    1.0.0
     */
    private function get_product_field_value($items, $product_index, $field)
    {
        if (empty($items)) {
            return '';
        }
        
        // Converter items para array indexado
        $items_array = array_values($items);
        
        // Verificar se o índice existe
        if (!isset($items_array[$product_index])) {
            return '';
        }
        
        $item = $items_array[$product_index];
        
        switch ($field) {
            case 'id':
                return strval($item->get_product_id() ?: '');
                
            case 'name':
                return strval($item->get_name() ?: '');
                
            case 'price':
                $total = $item->get_total() ?: 0;
                return 'R$ ' . number_format(floatval($total), 2, ',', '.');
                
            case 'quantity':
                return strval($item->get_quantity() ?: 0);
                
            case 'attributes':
                $product = $item->get_product();
                if ($product) {
                    return $this->get_product_attributes($product);
                }
                return '';
                
            default:
                return '';
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
        
        // If field contains delimiter, quotes, commas, or newlines, wrap in quotes
        if (strpos($field, "\t") !== false || strpos($field, '"') !== false || strpos($field, ",") !== false || strpos($field, "\n") !== false || strpos($field, "\r") !== false) {
            $field = '"' . $field . '"';
        }

        return $field;
    }

    /**
     * Parse transaction data based on format (TOON or JSON)
     * 
     * @param string $transaction_data Raw data
     * @param string $data_format Format indicator (toon or json)
     * @return array Parsed data structure
     * @since 1.0.0
     */
    private function parse_transaction_data($transaction_data, $data_format)
    {
        if (empty($transaction_data)) {
            return array();
        }

        // Se já é array, retorna direto
        if (is_array($transaction_data)) {
            return $transaction_data;
        }

        // Parse baseado no formato
        if ($data_format === 'toon') {
            return $this->parse_toon_data($transaction_data);
        } else {
            // Assume JSON como fallback
            $parsed = json_decode($transaction_data, true);
            return is_array($parsed) ? $parsed : array();
        }
    }

    /**
     * Parse TOON format data structure
     * 
     * @param string $toon_data TOON formatted string
     * @return array Parsed array structure  
     * @since 1.0.0
     */
    private function parse_toon_data($toon_data)
    {
        if (empty($toon_data) || !is_string($toon_data)) {
            return array();
        }

        $result = array();
        $lines = explode("\n", trim($toon_data));
        $current_section = null;

        foreach ($lines as $line) {
            $line = rtrim($line);
            if (empty($line)) continue;

            // Contar nível de indentação (espaços)
            $current_indent = strlen($line) - strlen(ltrim($line));
            $line = trim($line);

            // Se termina com ':', é uma seção
            if (substr($line, -1) === ':') {
                $section_name = rtrim($line, ':');
                
                if ($current_indent === 0) {
                    // Seção raiz
                    $current_section = $section_name;
                    $result[$current_section] = array();
                } elseif ($current_indent === 2 && $current_section) {
                    // Subseção não precisa criar estrutura aninhada no TOON
                    $current_section = $section_name;
                    if (!isset($result[$current_section])) {
                        $result[$current_section] = array();
                    }
                }
            } else {
                // É um valor chave: valor
                if (strpos($line, ': ') !== false) {
                    list($key, $value) = explode(': ', $line, 2);
                    $key = trim($key);  
                    $value = trim($value);
                    
                    // Remove aspas se existir tanto na chave quanto no valor
                    if ((substr($key, 0, 1) === '"' && substr($key, -1) === '"') ||
                        (substr($key, 0, 1) === "'" && substr($key, -1) === "'")) {
                        $clean_key = substr($key, 1, -1);
                    } else {
                        $clean_key = $key;
                    }
                    
                    if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                        (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                        $value = substr($value, 1, -1);
                    }

                    if ($current_section) {
                        // Armazenar tanto com a chave limpa quanto com a chave original
                        $result[$current_section][$clean_key] = $value;
                        
                        // Se a chave tem aspas, armazenar também com aspas para compatibilidade
                        if ($clean_key !== $key) {
                            $result[$current_section][$key] = $value;
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Extract value from lkn_rede_transaction_data array with enhanced mapping
     *
     * @since    1.0.0
     */
    private function extract_from_transaction_data($meta_key, $transaction_data)
    {
        if (!is_array($transaction_data)) {
            return '';
        }

        // ===== MAPEAMENTO COMPLETO BASEADO NA ESTRUTURA TOON =====
        $mapping = array(
            // === GATEWAY SECTION ===
            'lkn_rede_gateway_masked' => array('gateway.masked', 'masked'),
            'lkn_rede_gateway_type' => array('gateway.type', 'type'),
            'lkn_rede_gateway_brand' => array('gateway.brand', 'brand'),
            'lkn_rede_gateway_expiry' => array('gateway.expiry', 'expiry'),
            '_wc_rede_transaction_holder' => array('gateway.holder_name', 'holder_name'),

            // === TRANSACTION SECTION ===
            'lkn_rede_transaction_tid' => array('transaction.tid', 'tid'),
            '_wc_rede_transaction_id' => array('transaction.tid', 'tid'), // TID é o mesmo que transaction ID
            '_wc_rede_transaction_nsu' => array('transaction.nsu', 'nsu'),
            '_wc_rede_transaction_authorization_code' => array('transaction.authorization_code', 'authorization_code'),
            'lkn_rede_transaction_capture' => array('transaction.capture', 'capture'),
            'lkn_rede_transaction_recurrent' => array('transaction.recurrent', 'recurrent'),
            'lkn_rede_transaction_3ds_auth' => array('transaction.3ds_auth', '3ds_auth', 'transaction."3ds_auth"'),
            '_wc_rede_installments' => array('transaction.installments', 'installments'),
            'lkn_rede_transaction_installment_amount' => array('transaction.installment_amount', 'installment_amount'),

            // === AMOUNTS SECTION (PRIORIDADE ABSOLUTA PARA TOON) ===
            '_wc_rede_transaction_amount' => array('amounts.total', 'total'),        // Total da transação do TOON
            'lkn_rede_amounts_subtotal' => array('amounts.subtotal', 'subtotal'),   // Subtotal do TOON
            'lkn_rede_amounts_shipping' => array('amounts.shipping', 'shipping'),   // Frete do TOON
            'lkn_rede_amounts_interest_discount' => array('amounts.interest_discount', 'interest_discount'),
            'lkn_rede_amounts_currency' => array('amounts.currency', 'currency'),

            // === SYSTEM SECTION ===
            'lkn_rede_system_environment' => array('system.environment', 'environment'),  // Ambiente do TOON
            'lkn_rede_system_gateway' => array('system.gateway', 'gateway'),
            'lkn_rede_system_reference' => array('system.reference', 'reference'),
            'lkn_rede_system_request_datetime' => array('system.request_datetime', 'request_datetime'),
            'lkn_rede_system_version_free' => array('system.version_free', 'version_free'),
            'lkn_rede_system_version_pro' => array('system.version_pro', 'version_pro'),

            // === CREDENTIALS SECTION ===
            'lkn_rede_credentials_pv_masked' => array('credentials.pv_masked', 'pv_masked'),
            'lkn_rede_credentials_token_masked' => array('credentials.token_masked', 'token_masked'),

            // === RESPONSE SECTION ===
            'lkn_rede_response_http_status' => array('response.http_status', 'http_status'),
            '_wc_rede_transaction_return_code' => array('response.return_code', 'return_code'),
            '_wc_rede_transaction_return_message' => array('response.return_message', 'return_message'),

            // === CAMPOS LEGACY/ANTIGOS ===
            '_wc_rede_transaction_last4' => array('gateway.last4', 'last4'),
            '_wc_rede_transaction_bin' => array('gateway.bin', 'bin'),
            '_wc_rede_pix_qr_code' => array('pix.qr_code', 'qr_code'),
            '_wc_rede_pix_txid' => array('pix.txid', 'txid'),
            '_wc_rede_pix_integration_time_expiration' => array('pix.expiration', 'expiration'),
            '_wc_rede_transaction_refund_id' => array('operations.refund_id', 'refund_id'),
            '_wc_rede_transaction_cancel_id' => array('operations.cancel_id', 'cancel_id')
        );

        if (!isset($mapping[$meta_key])) {
            return '';
        }

        $possible_paths = $mapping[$meta_key];
        
        // Tentar cada caminho possível
        foreach ($possible_paths as $path) {
            $value = $this->get_nested_value($transaction_data, $path);
            
            if (!empty($value) && $value !== null && $value !== '') {
                return (string) $value;
            }
        }

        return '';
    }

    /**
     * Get nested value from array using dot notation or direct key
     * 
     * @param array $data Source array
     * @param string $path Path like 'gateway.type' or just 'type'  
     * @return mixed Found value or empty string
     * @since 1.0.0
     */
    private function get_nested_value($data, $path)
    {
        if (!is_array($data)) {
            return '';
        }

        // Simplified debug for critical paths only
        $is_critical_debug = ($path === 'amounts.total' || $path === 'amounts.subtotal');

        // Se o caminho contém '.', é uma estrutura aninhada
        if (strpos($path, '.') !== false) {
            $keys = explode('.', $path);
            $value = $data;

            foreach ($keys as $key) {
                // Remover aspas se existir (ex: "3ds_auth" -> 3ds_auth)
                $clean_key = trim($key, '"\'');
                
                // Tentar primeiro com a chave limpa, depois com a chave original
                if (isset($value[$clean_key])) {
                    $value = $value[$clean_key];
                } elseif (isset($value[$key])) {
                    $value = $value[$key];
                } else {
                    return '';
                }
            }
            
            // Always return string
            return strval($value ?: '');
        } else {
            // Caminho simples, buscar diretamente
            $clean_path = trim($path, '"\'');
            
            // Tentar primeiro com a chave limpa, depois com a chave original
            if (isset($data[$clean_path])) {
                return strval($data[$clean_path] ?: '');
            } elseif (isset($data[$path])) {
                return strval($data[$path] ?: '');
            }
            
            return '';
        }
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

        $metadata_strings = array();
        
        // Para produtos variáveis, coletar metadados da variação E do produto pai
        if ($product->is_type('variation')) {
            // Primeiro coletar metadados da variação (mais específicos)
            $variation_meta = $product->get_meta_data();
            
            foreach ($variation_meta as $meta) {
                $meta_info = $meta->get_data();
                $meta_key = $meta_info['key'];
                $meta_value = $meta_info['value'];

                // Pular metadados internos e excluídos
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
            
            // Também buscar atributos de variação (cor, tamanho, etc.)
            $variation_attributes = $product->get_variation_attributes();
            foreach ($variation_attributes as $attribute_name => $attribute_value) {
                if (!empty($attribute_value)) {
                    // Remove o prefixo 'attribute_' se existir
                    $clean_attribute_name = str_replace('attribute_', '', $attribute_name);
                    // Remove prefixo pa_ se for taxonomia
                    $clean_attribute_name = str_replace('pa_', '', $clean_attribute_name);
                    
                    $metadata_strings[] = $clean_attribute_name . ': ' . $attribute_value;
                }
            }
            
            // Buscar metadados do produto pai (menos específicos)
            $parent_product = wc_get_product($product->get_parent_id());
            if ($parent_product) {
                $parent_meta = $parent_product->get_meta_data();
                foreach ($parent_meta as $meta) {
                    $meta_info = $meta->get_data();
                    $meta_key = $meta_info['key'];
                    $meta_value = $meta_info['value'];

                    // Pular metadados internos e excluídos  
                    if (str_starts_with($meta_key, '_') || in_array($meta_key, $excluded_meta_keys)) {
                        continue;
                    }

                    // Pular se valor estiver vazio
                    if (empty($meta_value)) {
                        continue;
                    }

                    // Verificar se já não foi adicionado pela variação
                    $parent_metadata = $meta_key . ': ' . $meta_value;
                    if (!in_array($parent_metadata, $metadata_strings)) {
                        $metadata_strings[] = $parent_metadata;
                    }
                }
            }
        } else {
            // Para produtos simples, usar a lógica original
            $meta_data = $product->get_meta_data();
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
        }

        return implode(' | ', $metadata_strings);
    }
}
