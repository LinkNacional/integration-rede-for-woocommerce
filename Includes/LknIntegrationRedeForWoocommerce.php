<?php

namespace Lkn\IntegrationRedeForWoocommerce\Includes;

use Lkn\IntegrationRedeForWoocommerce\Admin\LknIntegrationRedeForWoocommerceAdmin;
use Lkn\IntegrationRedeForWoocommerce\Includes\LknIntegrationRedeForWoocommerceLoader;
use Lkn\IntegrationRedeForWoocommerce\PublicView\LknIntegrationRedeForWoocommercePublic;

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://linknacional.com.br
 * @since      1.0.0
 *
 * @package    LknIntegrationRedeForWoocommerce
 * @subpackage LknIntegrationRedeForWoocommerce/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    LknIntegrationRedeForWoocommerce
 * @subpackage LknIntegrationRedeForWoocommerce/includes
 * @author     Link Nacional <contato@linknacional.com>
 */
final class LknIntegrationRedeForWoocommerce
{
    /**
     * The loader that's responsible for maintaining and registering all hooks that power the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      LknIntegrationRedeForWoocommerceLoader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct()
    {
        if (defined('INTEGRATION_REDE_FOR_WOOCOMMERCE_VERSION')) {
            $this->version = INTEGRATION_REDE_FOR_WOOCOMMERCE_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'lkn-integration-rede-for-woocommerce';

        $this->load_dependencies();
        $this->loader->add_action('plugins_loaded', $this, 'define_hooks');
    }
    public $wc_rede_class;
    public $wc_rede_credit_class;
    public $wc_rede_debit_class;
    public $wc_maxipago_credit_class;
    public $wc_maxipago_debit_class;
    public $LknIntegrationRedeForWoocommercePixRedeClass;
    public $LknIntegrationRedeForWoocommerceEndpointClass;
    public $LknIntegrationRedeForWoocommercePixHelperClass;
    public $LknIntegrationRedeForWoocommerceHelperClass;

    //Define os hooks somente quando woocommerce está ativo
    public function define_hooks(): void
    {
        $this->wc_rede_class = new LknIntegrationRedeForWoocommerceWcRede();
        if (class_exists('WC_Payment_Gateway')) {
            $this->wc_rede_credit_class = new LknIntegrationRedeForWoocommerceWcRedeCredit();
            $this->wc_rede_debit_class = new LknIntegrationRedeForWoocommerceWcRedeDebit();
            $this->wc_maxipago_credit_class = new LknIntegrationRedeForWoocommerceWcMaxipagoCredit();
            $this->wc_maxipago_debit_class = new LknIntegrationRedeForWoocommerceWcMaxipagoDebit();
            $this->LknIntegrationRedeForWoocommercePixRedeClass = new LknIntegrationRedeForWoocommerceWcPixRede();

            $this->define_admin_hooks();
            $this->define_public_hooks();
        } else {
            $this->loader->add_action('admin_notices', $this->wc_rede_class, 'woocommerceMissingNotice');
        }
        $this->run();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - LknIntegrationRedeForWoocommerceLoader. Orchestrates the hooks of the plugin.
     * - LknIntegrationRedeForWoocommerce_Admin. Defines all hooks for the admin area.
     * - LknIntegrationRedeForWoocommercePublic. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies(): void
    {
        $this->loader = new LknIntegrationRedeForWoocommerceLoader();
        $this->LknIntegrationRedeForWoocommerceEndpointClass = new LknIntegrationRedeForWoocommerceWcEndpoint();
        $this->LknIntegrationRedeForWoocommercePixHelperClass = new LknIntegrationRedeForWoocommerceWcPixHelper();
        $this->LknIntegrationRedeForWoocommerceHelperClass = new LknIntegrationRedeForWoocommerceHelper();
        $this->loader->add_filter('integrationRedeGetCardToken', $this->LknIntegrationRedeForWoocommercePixHelperClass, 'getCardToken', 10, 3);
        $this->loader->add_filter('integrationRedeSetSupports', $this->LknIntegrationRedeForWoocommercePixHelperClass, 'setSupports', 10, 1);
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks(): void
    {
        $plugin_admin = new LknIntegrationRedeForWoocommerceAdmin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

        $this->loader->add_action('woocommerce_update_options_payment_gateways_' . $this->LknIntegrationRedeForWoocommercePixRedeClass->id, $this->LknIntegrationRedeForWoocommercePixRedeClass, "process_admin_options");
        $this->loader->add_action('woocommerce_admin_order_data_after_billing_address', $this->LknIntegrationRedeForWoocommercePixRedeClass, 'displayMeta');
        $this->loader->add_action('woocommerce_order_details_after_order_table', $this->LknIntegrationRedeForWoocommercePixRedeClass, "showPix");

        $this->loader->add_filter('plugin_action_links_' . INTEGRATION_REDE_FOR_WOOCOMMERCE_BASENAME, $this, 'addSettings');

        $this->loader->add_action('woocommerce_order_status_cancelled', $this->wc_rede_credit_class, 'process_refund');
        $this->loader->add_action('woocommerce_order_status_cancelled', $this->wc_maxipago_credit_class, 'process_refund');

        $this->loader->add_action('woocommerce_update_options_payment_gateways_' . $this->wc_rede_credit_class->id, $this->wc_rede_credit_class, 'process_admin_options');
        $this->loader->add_action('woocommerce_api_wc_rede_credit', $this->wc_rede_credit_class, 'check_return');
        $this->loader->add_filter('woocommerce_get_order_item_totals', $this->wc_rede_credit_class, 'order_items_payment_details', 10, 2);
        $this->loader->add_action('woocommerce_admin_order_data_after_billing_address', $this->wc_rede_credit_class, 'displayMeta', 10, 1);

        $this->loader->add_action('woocommerce_update_options_payment_gateways_' . $this->wc_rede_debit_class->id, $this->wc_rede_debit_class, 'process_admin_options');
        $this->loader->add_action('woocommerce_api_wc_rede_credit', $this->wc_rede_debit_class, 'check_return');
        $this->loader->add_filter('woocommerce_get_order_item_totals', $this->wc_rede_debit_class, 'order_items_payment_details', 10, 2);
        $this->loader->add_action('woocommerce_admin_order_data_after_billing_address', $this->wc_rede_debit_class, 'displayMeta', 10, 1);

        $this->loader->add_action('woocommerce_update_options_payment_gateways_' . $this->wc_maxipago_credit_class->id, $this->wc_maxipago_credit_class, 'process_admin_options');
        $this->loader->add_action('woocommerce_admin_order_data_after_billing_address', $this->wc_maxipago_credit_class, 'displayMeta', 10, 1);

        $this->loader->add_action('admin_notices', $this->wc_rede_class, 'softDescriptorNotice');

        $this->loader->add_action('woocommerce_update_options_payment_gateways_' . $this->wc_maxipago_debit_class->id, $this->wc_maxipago_debit_class, 'process_admin_options');
        $this->loader->add_action('woocommerce_admin_order_data_after_billing_address', $this->wc_maxipago_debit_class, 'displayMeta', 10, 1);
        $this->loader->add_filter('lknRedeGetMerchantAuth', $this->wc_maxipago_credit_class, 'getMerchantAuth');

        $this->loader->add_filter('plugin_action_links_' . INTEGRATION_REDE_FOR_WOOCOMMERCE_FILE_BASENAME, $this, 'lknIntegrationRedeForWoocommercePluginRowMeta', 10, 2);
        $this->loader->add_filter('plugin_action_links_' . INTEGRATION_REDE_FOR_WOOCOMMERCE_FILE_BASENAME, $this, 'lknIntegrationRedeForWoocommercePluginRowMetaPro', 10, 2);

        $this->loader->add_action('rest_api_init', $this->LknIntegrationRedeForWoocommerceEndpointClass, 'registerorderRedeCaptureEndPoint');
        $this->loader->add_filter('woocommerce_gateway_title', $this, 'customize_wc_payment_gateway_pix_name', 10, 2);

        $this->loader->add_action('add_meta_boxes', $this->LknIntegrationRedeForWoocommerceHelperClass, 'showOrderLogs');

        $this->loader->add_action('admin_notices', $this, 'lkn_admin_notice');

        // Hook para ações personalizadas da ordem PIX
        $this->loader->add_filter('woocommerce_order_actions', $this, 'add_pix_verification_action');
        $this->loader->add_action('woocommerce_order_action_verify_pix_status', $this, 'process_pix_verification_action');

        // Limpa o cron 'update_rede_orders' ao atualizar o plugin via admin
        $this->loader->add_action('upgrader_process_complete', $this, 'clear_update_rede_orders_on_plugin_update', 10, 2);

        // Adiciona endpoint AJAX para parcelas Rede Credit
        $this->loader->add_action('wp_ajax_lkn_get_rede_credit_data', $this, 'ajax_get_rede_credit_data');
        $this->loader->add_action('wp_ajax_nopriv_lkn_get_rede_credit_data', $this, 'ajax_get_rede_credit_data');

        // Adiciona endpoint AJAX para parcelas Rede Debit
        $this->loader->add_action('wp_ajax_lkn_get_rede_debit_data', $this, 'ajax_get_rede_debit_data');
        $this->loader->add_action('wp_ajax_nopriv_lkn_get_rede_debit_data', $this, 'ajax_get_rede_debit_data');

        // Adiciona endpoint AJAX para parcelas Maxipago
        $this->loader->add_action('wp_ajax_lkn_get_maxipago_credit_data', $this, 'ajax_get_maxipago_credit_data');
        $this->loader->add_action('wp_ajax_nopriv_lkn_get_maxipago_credit_data', $this, 'ajax_get_maxipago_credit_data');

        // Hooks para atualizar tokens OAuth2 no checkout (blocks e shortcode)
        $this->loader->add_action('wp_enqueue_scripts', $this, 'lkn_rede_refresh_oauth_token_on_checkout');
        // Adiciona endpoint AJAX para atualização da sessão de parcelas
        $this->loader->add_action('wp_ajax_lkn_update_installment_session', $this, 'ajax_update_installment_session');
        $this->loader->add_action('wp_ajax_nopriv_lkn_update_installment_session', $this, 'ajax_update_installment_session');
    }

    /**
     * Endpoint AJAX para retornar dados de parcelas do Maxipago
     */
    public function ajax_get_maxipago_credit_data()
    {
        $cart_total = 0;
        if (function_exists('WC') && WC()->cart) {
            // Soma dos produtos + taxa de entrega + impostos
            $cart_total = floatval(WC()->cart->get_cart_contents_total());
            $cart_total += floatval(WC()->cart->get_shipping_total());
            $cart_total += floatval(WC()->cart->get_taxes_total());

            WC()->cart->calculate_totals();

            $fees_objects = WC()->cart->get_fees();
            $extra_fees = 0;
            foreach ($fees_objects as $fee) {
                if (
                    strtolower($fee->name) !== strtolower(__('Juros', 'rede-for-woocommerce-pro')) &&
                    strtolower($fee->name) !== strtolower(__('Desconto', 'rede-for-woocommerce-pro'))
                ) {
                    $extra_fees += floatval($fee->amount);
                }
            }

            $cart_total += $extra_fees;
        }
        $max_installments = 12;
        $min_parcels_value = 5;
        if (isset($this->wc_maxipago_credit_class) && method_exists($this->wc_maxipago_credit_class, 'get_option')) {
            $max_installments = intval($this->wc_maxipago_credit_class->get_option('max_parcels_number'));
            $min_parcels_value = $this->wc_maxipago_credit_class->get_option('min_parcels_value');
            if (!is_numeric($min_parcels_value) || $min_parcels_value < 1) {
                $min_parcels_value = 5;
            } else {
                $min_parcels_value = floatval($min_parcels_value);
            }
            if ($max_installments < 1) {
                $max_installments = 12;
            }
        }

        if (function_exists('WC') && WC()->cart && !WC()->cart->is_empty()) {
            foreach (WC()->cart->get_cart() as $cart_item) {
                $product_id = $cart_item['product_id'];
                $product_limit = get_post_meta($product_id, 'lknMaxipagoProdutctInterest', true);
                if ($product_limit !== 'default' && is_numeric($product_limit)) {
                    $product_limit = (int) $product_limit;
                    if ($product_limit > 0 && $product_limit < $max_installments) {
                        $max_installments = $product_limit;
                    }
                }
            }
        }

        // Verificar se a licença PRO está ativa
        $is_pro_active = false;
        if (is_plugin_active('rede-for-woocommerce-pro/rede-for-woocommerce-pro.php')) {
            $pro_license = get_option('lknRedeForWoocommerceProLicense');
            if ($pro_license) {
                $license_data = base64_decode($pro_license);
                $is_pro_active = (strpos($license_data, 'active') !== false);
            }
        }

        // Obter valor mínimo da parcela das configurações
        $min_installment_value = 5; // Valor padrão
        if (isset($this->wc_maxipago_credit_class) && method_exists($this->wc_maxipago_credit_class, 'get_option')) {
            $configured_min_value = floatval($this->wc_maxipago_credit_class->get_option('min_parcels_value'));
            if ($configured_min_value > 0) {
                $min_installment_value = $configured_min_value;
            }
        }

        $installments = [];
        for ($i = 1; $i <= $max_installments; $i++) {
            $installment_value = $cart_total / $i;
            if ($installment_value < $min_parcels_value) {
                // Se nem mesmo 1x atende o valor mínimo, força 1x à vista
                if ($i === 1) {
                    $base_label = sprintf("%dx de %s", 1, wc_price($cart_total));
                    $label = $is_pro_active ? $this->get_installment_label_with_interest(1, $base_label, 'maxipago_credit') : $base_label;
                    $installments[] = [
                        'key' => 1,
                        'label' => $label
                    ];
                }
                break;
            }
            $base_label = sprintf("%dx de %s", $i, wc_price($installment_value));

            // Se a licença PRO estiver ativa, aplicar lógica de juros/desconto
            if ($is_pro_active) {
                $label = $this->get_installment_label_with_interest($i, $base_label, 'maxipago_credit');
            } else {
                $label = $base_label;
            }

            $installments[] = [
                'key' => $i,
                'label' => $label
            ];
        }
        wp_send_json([
            'cartTotal' => $cart_total,
            'installments' => $installments
        ]);
    }

    /**
     * Endpoint AJAX para retornar dados de parcelas do Rede Credit
     */
    public function ajax_get_rede_credit_data()
    {
        $cart_total = 0;
        if (function_exists('WC') && WC()->cart) {
            // Soma dos produtos + taxa de entrega + impostos
            $cart_total = floatval(WC()->cart->get_cart_contents_total());
            $cart_total += floatval(WC()->cart->get_shipping_total());
            $cart_total += floatval(WC()->cart->get_taxes_total());

            WC()->cart->calculate_totals();

            $fees_objects = WC()->cart->get_fees();
            $extra_fees = 0;
            foreach ($fees_objects as $fee) {
                if (
                    strtolower($fee->name) !== strtolower(__('Juros', 'rede-for-woocommerce-pro')) &&
                    strtolower($fee->name) !== strtolower(__('Desconto', 'rede-for-woocommerce-pro'))
                ) {
                    $extra_fees += floatval($fee->amount);
                }
            }

            $cart_total += $extra_fees;
        }
        $max_installments = 12;
        $min_parcels_value = 5;
        if (isset($this->wc_rede_credit_class) && method_exists($this->wc_rede_credit_class, 'get_option')) {
            $max_installments = intval($this->wc_rede_credit_class->get_option('max_parcels_number'));
            $min_parcels_value = $this->wc_rede_credit_class->get_option('min_parcels_value');
            if (!is_numeric($min_parcels_value) || $min_parcels_value < 1) {
                $min_parcels_value = 5;
            } else {
                $min_parcels_value = floatval($min_parcels_value);
            }
            if ($max_installments < 1) {
                $max_installments = 12;
            }
        }

        if (function_exists('WC') && WC()->cart && !WC()->cart->is_empty()) {
            foreach (WC()->cart->get_cart() as $cart_item) {
                $product_id = $cart_item['product_id'];
                $product_limit = get_post_meta($product_id, 'lknRedeProdutctInterest', true);
                if ($product_limit !== 'default' && is_numeric($product_limit)) {
                    $product_limit = (int) $product_limit;
                    if ($product_limit > 0 && $product_limit < $max_installments) {
                        $max_installments = $product_limit;
                    }
                }
            }
        }

        // Verificar se a licença PRO está ativa
        $is_pro_active = false;
        if (is_plugin_active('rede-for-woocommerce-pro/rede-for-woocommerce-pro.php')) {
            $pro_license = get_option('lknRedeForWoocommerceProLicense');
            if ($pro_license) {
                $license_data = base64_decode($pro_license);
                $is_pro_active = (strpos($license_data, 'active') !== false);
            }
        }

        // Obter valor mínimo da parcela das configurações
        $min_installment_value = 5; // Valor padrão
        if (isset($this->wc_rede_credit_class) && method_exists($this->wc_rede_credit_class, 'get_option')) {
            $configured_min_value = floatval($this->wc_rede_credit_class->get_option('min_parcels_value'));
            if ($configured_min_value > 0) {
                $min_installment_value = $configured_min_value;
            }
        }

        $installments = [];
        for ($i = 1; $i <= $max_installments; $i++) {
            $installment_value = $cart_total / $i;
            if ($installment_value < $min_parcels_value) {
                // Se nem mesmo 1x atende o valor mínimo, força 1x à vista
                if ($i === 1) {
                    $base_label = sprintf("%dx de %s", 1, wc_price($cart_total));
                    $label = $is_pro_active ? $this->get_installment_label_with_interest(1, $base_label, 'rede_credit') : $base_label;
                    $installments[] = [
                        'key' => 1,
                        'label' => $label
                    ];
                }
                break;
            }
            $base_label = sprintf("%dx de %s", $i, wc_price($installment_value));

            // Se a licença PRO estiver ativa, aplicar lógica de juros/desconto
            if ($is_pro_active) {
                $label = $this->get_installment_label_with_interest($i, $base_label, 'rede_credit');
            } else {
                $label = $base_label;
            }

            $installments[] = [
                'key' => $i,
                'label' => $label
            ];
        }
        
        // Definir installment de crédito como 1 na sessão
        if (function_exists('WC') && WC()->session) {
            WC()->session->set('lkn_installments_number_rede_credit', 1);
        }
        
        wp_send_json([
            'cartTotal' => $cart_total,
            'installments' => $installments
        ]);
    }

    /**
     * Endpoint AJAX para retornar dados de parcelas do Rede Debit
     * Reutiliza a lógica do Rede Credit, mas considera as configurações de débito
     */
    public function ajax_get_rede_debit_data()
    {
        $cart_total = 0;
        if (function_exists('WC') && WC()->cart) {
            // Soma dos produtos + taxa de entrega + impostos
            $cart_total = floatval(WC()->cart->get_cart_contents_total());
            $cart_total += floatval(WC()->cart->get_shipping_total());
            $cart_total += floatval(WC()->cart->get_taxes_total());

            WC()->cart->calculate_totals();

            $fees_objects = WC()->cart->get_fees();
            $extra_fees = 0;
            foreach ($fees_objects as $fee) {
                if (
                    strtolower($fee->name) !== strtolower(__('Juros', 'rede-for-woocommerce-pro')) &&
                    strtolower($fee->name) !== strtolower(__('Desconto', 'rede-for-woocommerce-pro'))
                ) {
                    $extra_fees += floatval($fee->amount);
                }
            }

            $cart_total += $extra_fees;
        }
        
        $max_installments = 12;
        $min_parcels_value = 5;
        if (isset($this->wc_rede_debit_class) && method_exists($this->wc_rede_debit_class, 'get_option')) {
            $max_installments = intval($this->wc_rede_debit_class->get_option('max_parcels_number'));
            $min_parcels_value = $this->wc_rede_debit_class->get_option('min_parcels_value');
            if (!is_numeric($min_parcels_value) || $min_parcels_value < 1) {
                $min_parcels_value = 5;
            } else {
                $min_parcels_value = floatval($min_parcels_value);
            }
            if ($max_installments < 1) {
                $max_installments = 12;
            }
        }

        if (function_exists('WC') && WC()->cart && !WC()->cart->is_empty()) {
            foreach (WC()->cart->get_cart() as $cart_item) {
                $product_id = $cart_item['product_id'];
                $product_limit = get_post_meta($product_id, 'lknRedeProdutctInterest', true);
                if ($product_limit !== 'default' && is_numeric($product_limit)) {
                    $product_limit = (int) $product_limit;
                    if ($product_limit > 0 && $product_limit < $max_installments) {
                        $max_installments = $product_limit;
                    }
                }
            }
        }

        // Verificar se a licença PRO está ativa
        $is_pro_active = false;
        if (is_plugin_active('rede-for-woocommerce-pro/rede-for-woocommerce-pro.php')) {
            $pro_license = get_option('lknRedeForWoocommerceProLicense');
            if ($pro_license) {
                $license_data = base64_decode($pro_license);
                $is_pro_active = (strpos($license_data, 'active') !== false);
            }
        }

        // Obter valor mínimo da parcela das configurações
        $min_installment_value = 5; // Valor padrão
        if (isset($this->wc_rede_debit_class) && method_exists($this->wc_rede_debit_class, 'get_option')) {
            $configured_min_value = floatval($this->wc_rede_debit_class->get_option('min_parcels_value'));
            if ($configured_min_value > 0) {
                $min_installment_value = $configured_min_value;
            }
        }

        $installments = [];
        for ($i = 1; $i <= $max_installments; $i++) {
            $installment_value = $cart_total / $i;
            if ($installment_value < $min_parcels_value) {
                // Se nem mesmo 1x atende o valor mínimo, força 1x à vista
                if ($i === 1) {
                    $base_label = sprintf("%dx de %s", 1, wc_price($cart_total));
                    $label = $is_pro_active ? $this->get_installment_label_with_interest(1, $base_label, 'rede_debit') : $base_label;
                    $installments[] = [
                        'key' => 1,
                        'label' => $label
                    ];
                }
                break;
            }
            $base_label = sprintf("%dx de %s", $i, wc_price($installment_value));

            // Se a licença PRO estiver ativa, aplicar lógica de juros/desconto
            if ($is_pro_active) {
                $label = $this->get_installment_label_with_interest($i, $base_label, 'rede_debit');
            } else {
                $label = $base_label;
            }

            $installments[] = [
                'key' => $i,
                'label' => $label
            ];
        }
        
        // Definir installment de debit como 1 na sessão e capturar tipo de cartão se enviado
        if (function_exists('WC') && WC()->session) {
            WC()->session->set('lkn_installments_number_rede_debit', 1);
            
            // Se o tipo de cartão foi enviado na requisição, salvar na sessão
            if (isset($_POST['card_type']) && !empty($_POST['card_type'])) {
                $card_type = sanitize_text_field($_POST['card_type']);
                if (in_array($card_type, ['credit', 'debit'])) {
                    WC()->session->set('lkn_card_type_rede_debit', $card_type);
                }
            }
        }
        
        wp_send_json([
            'cartTotal' => $cart_total,
            'installments' => $installments
        ]);
    }

    /**
     * Endpoint AJAX para atualizar a sessão de parcelas
     */
    public function ajax_update_installment_session()
    {
        $payment_method = sanitize_text_field($_POST['payment_method']);
        $installments = intval($_POST['installments']);
        $nonce = sanitize_text_field($_POST['nonce']);

        // Capturar tipo de cartão (apenas para rede_debit)
        $card_type = null;
        if ($payment_method === 'rede_debit' && isset($_POST['card_type'])) {
            $card_type = sanitize_text_field($_POST['card_type']);
        }

        // Verificar nonce baseado no método de pagamento
        $nonce_action = '';
        switch ($payment_method) {
            case 'rede_credit':
                $nonce_action = 'rede_payment_fields_nonce';
                break;
            case 'rede_debit':
                $nonce_action = 'rede_debit_payment_fields_nonce';
                break;
            case 'maxipago_credit':
                $nonce_action = 'maxipago_payment_fields_nonce';
                break;
            default:
                wp_send_json_error(['message' => 'Método de pagamento não suportado']);
                return;
        }

        // Verificar nonce para segurança
        if (!wp_verify_nonce($nonce, $nonce_action)) {
            wp_send_json_error(['message' => 'Nonce inválido']);
            return;
        }

        if (empty($payment_method) || $installments < 1) {
            wp_send_json_error(['message' => 'Parâmetros inválidos']);
            return;
        }

        // Determinar a chave da sessão baseada no método de pagamento
        $session_key = '';
        switch ($payment_method) {
            case 'rede_credit':
                $session_key = 'lkn_installments_number_rede_credit';
                break;
            case 'rede_debit':
                $session_key = 'lkn_installments_number_rede_debit';
                break;
            case 'maxipago_credit':
                $session_key = 'lkn_installments_number_maxipago_credit';
                break;
            default:
                wp_send_json_error(['message' => 'Método de pagamento não suportado']);
                return;
        }

        // Atualizar a sessão do WooCommerce
        if (function_exists('WC') && WC()->session) {
            WC()->session->set($session_key, $installments);
            
            // Para rede_debit, salvar também o tipo de cartão na sessão
            if ($payment_method === 'rede_debit' && $card_type) {
                WC()->session->set('lkn_card_type_rede_debit', $card_type);
            }
            
            $response_data = [
                'message' => 'Sessão atualizada com sucesso',
                'payment_method' => $payment_method,
                'installments' => $installments,
                'session_key' => $session_key
            ];
            
            // Incluir tipo de cartão na resposta se for rede_debit
            if ($payment_method === 'rede_debit' && $card_type) {
                $response_data['card_type'] = $card_type;
            }
            
            wp_send_json_success($response_data);
        } else {
            wp_send_json_error(['message' => 'Sessão do WooCommerce não disponível']);
        }
    }

    /**
     * Gera o label da parcela com informações de juros/desconto (funcionalidade PRO)
     */
    private function get_installment_label_with_interest($installment_number, $base_label, $gateway = 'rede_credit')
    {
        // Obter todas as configurações do gateway
        $gatewaySettings = get_option('woocommerce_' . $gateway . '_settings', array());
        if (!is_array($gatewaySettings)) {
            return $base_label;
        }

        // Obter soma dos produtos + taxa de entrega + impostos
        $cartTotal = 0;
        if (function_exists('WC') && WC()->cart) {
            // Soma dos produtos
            $cartTotal = floatval(WC()->cart->get_cart_contents_total());
            // Adicionar taxa de entrega
            $cartTotal += floatval(WC()->cart->get_shipping_total());
            // Adicionar impostos
            $cartTotal += floatval(WC()->cart->get_taxes_total());

            WC()->cart->calculate_totals();

            $fees_objects = WC()->cart->get_fees();
            $extra_fees = 0;
            foreach ($fees_objects as $fee) {
                if (
                    strtolower($fee->name) !== strtolower(__('Juros', 'rede-for-woocommerce-pro')) &&
                    strtolower($fee->name) !== strtolower(__('Desconto', 'rede-for-woocommerce-pro'))
                ) {
                    $extra_fees += floatval($fee->amount);
                }
            }
        }

        // Calcular o valor da parcela individual
        $installmentValue = $cartTotal / $installment_number;

        // Verificar valor mínimo para aplicar juros
        $minInterest = isset($gatewaySettings['min_interest']) ? floatval($gatewaySettings['min_interest']) : 0;
        $ignoreInterest = ($minInterest > 0 && $installmentValue > $minInterest);

        // Verificar se há checkbox "sem juros" marcado
        $no_interest_key = "{$installment_number}x_no_interest";
        if (isset($gatewaySettings[$no_interest_key]) && $gatewaySettings[$no_interest_key] === 'yes') {
            return $base_label . ' sem juros';
        }

        // Verificar o valor de juros/desconto
        $value_key = "{$installment_number}x";
        $discount_key = "{$installment_number}x_discount";

        $value = 0;
        $is_discount = false;

        // Verificar se existe valor de desconto
        if (isset($gatewaySettings[$discount_key]) && !empty($gatewaySettings[$discount_key])) {
            $value = floatval($gatewaySettings[$discount_key]);
            $is_discount = true;

            // Se valor for negativo, corrigir para 0 e salvar
            if ($value < 0) {
                $value = 0;
                $gatewaySettings[$discount_key] = '0';
                update_option('woocommerce_' . $gateway . '_settings', $gatewaySettings);
            }
        }
        // Senão, verificar se existe valor normal (juros)
        elseif (isset($gatewaySettings[$value_key]) && !empty($gatewaySettings[$value_key])) {
            $value = floatval($gatewaySettings[$value_key]);
            $is_discount = false;

            // Se valor for negativo, corrigir para 0 e salvar
            if ($value < 0) {
                $value = 0;
                $gatewaySettings[$value_key] = '0';
                update_option('woocommerce_' . $gateway . '_settings', $gatewaySettings);
            }
        }

        // Se o valor for 0 ou vazio, adicionar "sem juros"
        if ($value === 0) {
            return $base_label . ' sem juros';
        }

        // Calcular novo valor da parcela com juros/desconto
        $newInstallmentValue = $installmentValue;

        if ($is_discount) {
            // Aplicar desconto
            $total_with_discount = ($cartTotal * (1 - ($value / 100))) + $extra_fees;
            $newInstallmentValue = $total_with_discount / $installment_number;
            $new_label = sprintf("%dx de %s", $installment_number, wc_price($newInstallmentValue));
            return $new_label . " ({$value}% de desconto)";
        } else {
            // Para juros, verificar se deve ignorar devido ao valor mínimo da parcela
            if ($ignoreInterest) {
                return $base_label . ' sem juros';
            } else {
                // Aplicar juros
                $total_with_interest = ($cartTotal * (1 + ($value / 100))) + $extra_fees;
                $newInstallmentValue = $total_with_interest / $installment_number;
                $new_label = sprintf("%dx de %s", $installment_number, wc_price($newInstallmentValue));
                return $new_label . " ({$value}% de juros)";
            }
        }
    }

    public function lkn_admin_notice()
    {
        if (!file_exists(WP_PLUGIN_DIR . '/fraud-detection-for-woocommerce/fraud-detection-for-woocommerce.php')) {
            require INTEGRATION_REDE_FOR_WOOCOMMERCE_DIR . 'Includes/views/notices/lkn-integration-rede-for-woocommerce-notice-download.php';
        }
    }

    public function customize_wc_payment_gateway_pix_name($title, $gateway_id)
    {
        if ($gateway_id === 'integration_rede_pix') {
            $title = __('Rede Pix FREE', 'woo-rede');
        }
        return $title;
    }

    /**
     * Adiciona ação de verificação PIX no dropdown de ações da ordem
     */
    public function add_pix_verification_action($actions)
    {
        global $theorder;
        
        if (!$theorder) {
            return $actions;
        }
        
        $payment_method = $theorder->get_payment_method();
        
        // Só adiciona a ação se for um pedido PIX
        if ($payment_method === 'integration_rede_pix' || $payment_method === 'rede_pix') {
            $actions['verify_pix_status'] = __('Verificar Status PIX', 'woo-rede');
        }
        
        return $actions;
    }

    /**
     * Processa a ação de verificação manual do PIX
     */
    public function process_pix_verification_action($order)
    {
        $payment_method = $order->get_payment_method();
        
        // Validar se é pedido PIX
        if ($payment_method !== 'integration_rede_pix' && $payment_method !== 'rede_pix') {
            $order->add_order_note(__('Verificação PIX: Esta ação é aplicável apenas a pedidos com método de pagamento PIX.', 'woo-rede'));
            return;
        }
        
        // Buscar TID da transação
        $tId = '';
        if ($payment_method === 'integration_rede_pix') {
            $tId = $order->get_meta('_wc_rede_integration_pix_transaction_tid');
        } elseif ($payment_method === 'rede_pix') {
            $tId = $order->get_meta('_wc_rede_pix_transaction_tid');
        }
        
        if (empty($tId)) {
            $order->add_order_note(__('Verificação PIX: Identificador da transação não localizado nos metadados do pedido.', 'woo-rede'));
            return;
        }
        
        // Obter configurações do gateway
        $gateway_id = ($payment_method === 'integration_rede_pix') ? 'integration_rede_pix' : 'rede_pix';
        $pixOptions = get_option('woocommerce_' . $gateway_id . '_settings');
        $environment = $pixOptions['environment'] ?? 'sandbox';
        
        try {
            // Renovar token OAuth2 se necessário
            LknIntegrationRedeForWoocommerceHelper::refresh_expired_rede_oauth_tokens(20);
            $token_data = LknIntegrationRedeForWoocommerceHelper::get_cached_rede_oauth_token_for_gateway($gateway_id, $environment);
            
            if (!$token_data || empty($token_data['token'])) {
                throw new \Exception(__('Erro ao obter token de autenticação.', 'woo-rede'));
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
            
            if (is_wp_error($response)) {
                throw new \Exception(__('Erro na comunicação com a API: ', 'woo-rede') . $response->get_error_message());
            }
            
            $response_body = json_decode(wp_remote_retrieve_body($response), true);
            $status = $response_body['authorization']['status'] ?? 'Unknown';
            
            if ($status === 'Approved') {
                // Obter valor e moeda do pedido usando método compatível
                $order_total = wc_price($order->get_total());
                
                // Só atualiza o status se o pedido estiver pendente
                if ($order->has_status('pending')) {
                    $paymentCompleteStatus = $pixOptions['payment_complete_status'] ?? 'processing';
                    if (empty($paymentCompleteStatus)) {
                        $paymentCompleteStatus = 'processing';
                    }
                    
                    $order->add_order_note(sprintf(__('Verificação Manual PIX: Pagamento de %s confirmado pela Rede.', 'woo-rede'), $order_total));
                    $order->update_status($paymentCompleteStatus);
                } else {
                    $order->add_order_note(sprintf(__('Verificação Manual PIX: Pagamento de %s confirmado pela Rede.', 'woo-rede'), $order_total));
                }
            } else {
                $order->add_order_note(__('Verificação Manual PIX: Pagamento não confirmado pela Rede. Status da transação: ', 'woo-rede') . $status);
            }
            
        } catch (\Exception $e) {
            $order->add_order_note(__('Verificação Manual PIX: Falha na consulta de pagamento na Rede. Detalhes: ', 'woo-rede') . $e->getMessage());
        }
        
        $order->save();
    }

    /**
     * Limpa o cron 'update_rede_orders' ao atualizar o plugin via admin
     */
    public function clear_update_rede_orders_on_plugin_update($upgrader, $options)
    {
        if (
            isset($options['action'], $options['type'], $options['plugins']) &&
            $options['action'] === 'update' &&
            $options['type'] === 'plugin'
        ) {
            foreach ($options['plugins'] as $plugin) {
                if (strpos($plugin, 'integration-rede-for-woocommerce.php') !== false) {
                    wp_clear_scheduled_hook('update_rede_orders');
                }
            }
        }
    }

    public static function lknIntegrationRedeForWoocommercePluginRowMeta($plugin_meta, $plugin_file)
    {
        $new_meta_links['setting'] = sprintf(
            '<a href="%1$s">%2$s</a>',
            admin_url('admin.php?page=wc-settings&tab=checkout'),
            __('Settings', 'woo-rede')
        );

        return array_merge($plugin_meta, $new_meta_links);
    }

    public static function lknIntegrationRedeForWoocommercePluginRowMetaPro($plugin_meta, $plugin_file)
    {
        // Defina o URL e o texto do link
        $url = 'https://www.linknacional.com.br/wordpress/woocommerce/rede/';
        $link_text = sprintf(
            '<span style="color: red; font-weight: bold;">%s</span>',
            __('Be pro', 'woo-rede')
        );

        // Crie o novo link de meta
        $new_meta_link = sprintf('<a href="%1$s">%2$s</a>', $url, $link_text);

        // Adicione o novo link ao array de metadados do plugin
        $plugin_meta[] = $new_meta_link;

        return $plugin_meta;
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks(): void
    {
        $plugin_public = new LknIntegrationRedeForWoocommercePublic($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        $this->wc_rede_class->getInstance();
        $this->loader->add_filter('woocommerce_payment_gateways', $this->wc_rede_class, 'addGateway');

        $this->loader->add_action('woocommerce_thankyou_' . $this->wc_rede_credit_class->id, $this->wc_rede_credit_class, 'thankyou_page');
        $this->loader->add_action('woocommerce_thankyou_' . $this->wc_rede_debit_class->id, $this->wc_rede_debit_class, 'thankyou_page');
        $this->loader->add_action('woocommerce_checkout_fields', $this->wc_maxipago_credit_class, 'addNeighborhoodFieldToCheckout');
        $this->loader->add_action('woocommerce_checkout_fields', $this->wc_maxipago_debit_class, 'addNeighborhoodFieldToCheckout');
        $this->loader->add_action('wp_enqueue_scripts', $this->wc_rede_credit_class, 'checkoutScripts');
        $this->loader->add_action('wp_enqueue_scripts', $this->wc_rede_debit_class, 'checkoutScripts');
        $this->loader->add_action('wp_enqueue_scripts', $this->wc_maxipago_credit_class, 'checkoutScripts');
        $this->loader->add_action('wp_enqueue_scripts', $this->wc_maxipago_debit_class, 'checkoutScripts');

        $this->loader->add_action('before_woocommerce_init', $this, 'wcEditorBlocksActive');
        $this->loader->add_action('woocommerce_blocks_payment_method_type_registration', $this, 'wcEditorBlocksAddPaymentMethod');

        // Adiciona endpoint AJAX para atualizar sessão de parcelas no shortcode
        $this->loader->add_action('wp_ajax_update_installment_session', $this, 'update_installment_session');
        $this->loader->add_action('wp_ajax_nopriv_update_installment_session', $this, 'update_installment_session');

        // Adiciona o nome do gateway nas notas do pedido
        $this->loader->add_filter('woocommerce_new_order_note_data', $this, 'add_gateway_name_to_notes_global', 10, 2);

        // Adiciona informações de parcelamento na revisão do pedido
        $this->loader->add_action('woocommerce_review_order_after_order_total', $this, 'display_payment_installment_info');
    }

    public function wcEditorBlocksActive(): void
    {
        if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
                'cart_checkout_blocks',
                INTEGRATION_REDE_FOR_WOOCOMMERCE_FILE,
                true
            );
        }
    }

    public function wcEditorBlocksAddPaymentMethod(\Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry): void
    {
        if (! class_exists('Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')) {
            return;
        }

        $payment_method_registry->register(new LknIntegrationRedeForWoocommerceWcMaxipagoCreditBlocks());
        $payment_method_registry->register(new LknIntegrationRedeForWoocommerceWcMaxipagoDebitBlocks());
        $payment_method_registry->register(new LknIntegrationRedeForWoocommerceWcRedeCreditBlocks());
        $payment_method_registry->register(new LknIntegrationRedeForWoocommerceWcRedeDebitBlocks());
        $payment_method_registry->register(new LknIntegrationRedeForWoocommerceWcPixRedeBlocks());
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run(): void
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    LknIntegrationRedeForWoocommerceLoader    Orchestrates the hooks of the plugin.
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }

    public function addSettings($plugin_meta)
    {
        $plugin_meta['setting'] = sprintf(
            '<a href="%1$s">%2$s</a>',
            admin_url('admin.php?page=wc-settings&tab=checkout'),
            'Configurações'
        );

        return $plugin_meta;
    }

    public function add_gateway_name_to_notes_global($note_data, $args)
    {
        if (isset($note_data['comment_post_ID'])) {
            $order_id = $note_data['comment_post_ID'];
            $order = wc_get_order($order_id);

            if ($order && is_a($order, 'WC_Order')) {

                // Metodos do plugin integration rede e integration rede pro
                $methods = ['maxipago_credit', 'maxipago_debit', 'integration_rede_pix', 'rede_credit', 'rede_debit', 'maxipago_pix', 'rede_pix'];
                $payment_method = $order->get_payment_method();

                if (in_array($payment_method, $methods)) {

                    $payment_gateways = WC()->payment_gateways->payment_gateways();
                    $gateway_title = 'Rede'; // Fallback

                    if (isset($payment_gateways[$payment_method])) {
                        $gateway_title = $payment_gateways[$payment_method]->get_title();
                    }

                    $prefix = $gateway_title . ' — ';
                    if (strpos($note_data['comment_content'], $prefix) === false) {
                        $note_data['comment_content'] = $prefix . $note_data['comment_content'];
                    }
                }
            }
        }

        return $note_data;
    }

    /**
     * Exibe informações sobre o pagamento parcelado na revisão do pedido
     */
    public function display_payment_installment_info()
    {
        // Verificar se WooCommerce está ativo e a sessão existe
        if (!function_exists('WC') || !WC()->session) {
            return;
        }

        $chosen_payment_method = WC()->session->get('chosen_payment_method');

        // Verificar se é um método de pagamento Rede ou Maxipago
        if (!in_array($chosen_payment_method, ['rede_credit', 'rede_debit', 'maxipago_credit'])) {
            return;
        }

        // Obter o total do carrinho
        $cart_total = WC()->cart->get_total('raw');

        if ($cart_total <= 0) {
            return;
        }

        // Obter configurações do gateway para verificar valor mínimo de parcelas
        $settings = get_option('woocommerce_' . $chosen_payment_method . '_settings', array());
        $min_parcels_value = isset($settings['min_parcels_value']) ? floatval($settings['min_parcels_value']) : 5;
        
        // Para rede_debit, verificar se permite crédito através da configuração de tipo de cartão
        $show_installments = true;
        if ($chosen_payment_method === 'rede_debit') {
            $card_type_restriction = isset($settings['card_type_restriction']) ? $settings['card_type_restriction'] : 'debit_only';
            
            // Verificar tipo de cartão na sessão
            $session_card_type = WC()->session->get('lkn_card_type_rede_debit');
            
            // Se o tipo da sessão for débito, forçar para não mostrar parcelas
            if ($session_card_type === 'debit') {
                $show_installments = false;
            } else {
                // Só mostra parcelas se permitir crédito (both ou credit_only) e não for débito na sessão
                if ($card_type_restriction === 'debit_only') {
                    $show_installments = false;
                }
            }
        }
        
        // Verificar se é possível ter mais de uma parcela com base no valor mínimo
        $max_possible_installments = floor($cart_total / $min_parcels_value);
        if ($max_possible_installments <= 1 || !$show_installments) {
            return; // Não exibe se só é possível 1x à vista ou se não permite crédito
        }

        // Obter a parcela selecionada da sessão baseada no método de pagamento
        $installment_session_key = '';
        if ($chosen_payment_method === 'rede_credit') {
            $installment_session_key = 'lkn_installments_number_rede_credit';
        } elseif ($chosen_payment_method === 'rede_debit') {
            $installment_session_key = 'lkn_installments_number_rede_debit';
        } elseif ($chosen_payment_method === 'maxipago_credit') {
            $installment_session_key = 'lkn_installments_number_maxipago_credit';
        }

        $installment = WC()->session->get($installment_session_key);

        // Para rede_debit, se o tipo de cartão for débito, forçar parcela para 1
        if ($chosen_payment_method === 'rede_debit') {
            $session_card_type = WC()->session->get('lkn_card_type_rede_debit');
            if ($session_card_type === 'debit') {
                $installment = 1;
            }
        }

        if (!$installment || $installment <= 0) {
            return;
        }

        // Determinar o nome do método de pagamento
        $payment_method_name = '';
        if ($chosen_payment_method === 'rede_credit') {
            $payment_method_name = __('Rede Credit Card', 'woo-rede');
        } elseif ($chosen_payment_method === 'rede_debit') {
            $payment_method_name = __('Rede Debit/Credit Card', 'woo-rede');
        } elseif ($chosen_payment_method === 'maxipago_credit') {
            $payment_method_name = __('Maxipago Credit Card', 'woo-rede');
        }

        // Gerar a informação de pagamento e label dinâmico
        if ($installment == 1) {
            $payment_label = __('Payment', 'woo-rede');
            $payment_info = __('Cash payment', 'woo-rede');
        } else {
            $payment_label = __('Installment', 'woo-rede');
            // Calcular valor da parcela (simples divisão)
            $installment_value = $cart_total / $installment;
            $formatted_value = wc_price($installment_value);

            $payment_info = sprintf(
                __('%dx of %s', 'woo-rede'),
                $installment,
                $formatted_value
            );
        }

        // Exibir a informação
        echo '<tr>';
        echo '<th>' . esc_html($payment_label) . '</th>';
        echo '<td>' . wp_kses_post($payment_info) . '</td>';
        echo '</tr>';
    }

    /**
     * Atualiza sessão de parcelas via AJAX (para shortcode checkout)
     */
    public function update_installment_session()
    {
        try {
            // Verifica o nonce
            if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'lkn_rede_installment_shortcode_nonce')) {
                wp_send_json_error(['message' => 'Invalid nonce']);
                return;
            }

            // Obtém dados da requisição
            $gateway = isset($_POST['gateway']) ? sanitize_text_field(wp_unslash($_POST['gateway'])) : '';
            $installments = isset($_POST['installments']) ? intval(sanitize_text_field(wp_unslash($_POST['installments']))) : 1;

            // Valida gateway
            if (!in_array($gateway, ['rede_credit', 'rede_debit', 'maxipago_credit'])) {
                wp_send_json_error(['message' => 'Invalid gateway']);
                return;
            }

            // Valida parcelas
            if ($installments < 1 || $installments > 12) {
                wp_send_json_error(['message' => 'Invalid installments']);
                return;
            }

            // Atualiza sessão WooCommerce
            if (function_exists('WC') && WC()->session) {
                $session_key = 'lkn_installments_number_' . $gateway;
                WC()->session->set($session_key, $installments);
                
                wp_send_json_success([
                    'message' => 'Installments updated successfully',
                    'gateway' => $gateway,
                    'installments' => $installments
                ]);
            } else {
                wp_send_json_error(['message' => 'WooCommerce session not available']);
            }
        } catch (\Throwable $th) {
            wp_send_json_error([
                'message' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile()
            ]);
        }
    }

    /**
     * Verifica e renova tokens OAuth2 expirados no checkout (blocks e shortcode)
     */
    public function lkn_rede_refresh_oauth_token_on_checkout() 
    {
        // Só executa em páginas de checkout
        if (!is_checkout()) {
            return;
        }

        // Verifica e renova tokens expirados (15 minutos)
        LknIntegrationRedeForWoocommerceHelper::refresh_expired_rede_oauth_tokens(15);
    }
}
