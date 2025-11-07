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
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
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
    public $wc_rede_api_class;
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

            $this->wc_rede_api_class = $this->wc_rede_credit_class->api;
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
        $this->loader->add_filter('lknRedeAPIorderCapture', $this->wc_rede_api_class, 'do_transaction_capture');
        $this->loader->add_filter('lknRedeGetMerchantAuth', $this->wc_maxipago_credit_class, 'getMerchantAuth');

        $this->loader->add_filter('plugin_action_links_' . INTEGRATION_REDE_FOR_WOOCOMMERCE_FILE_BASENAME, $this, 'lknIntegrationRedeForWoocommercePluginRowMeta', 10, 2);
        $this->loader->add_filter('plugin_action_links_' . INTEGRATION_REDE_FOR_WOOCOMMERCE_FILE_BASENAME, $this, 'lknIntegrationRedeForWoocommercePluginRowMetaPro', 10, 2);

        $this->loader->add_action('rest_api_init', $this->LknIntegrationRedeForWoocommerceEndpointClass, 'registerorderRedeCaptureEndPoint');
        $this->loader->add_filter('woocommerce_gateway_title', $this, 'customize_wc_payment_gateway_pix_name', 10, 2);

        $this->loader->add_action('add_meta_boxes', $this->LknIntegrationRedeForWoocommerceHelperClass, 'showOrderLogs');

        $this->loader->add_action('admin_notices', $this, 'lkn_admin_notice');

        // Adiciona endpoint AJAX para parcelas Rede Credit
        $this->loader->add_action('wp_ajax_lkn_get_rede_credit_data', $this, 'ajax_get_rede_credit_data');
        $this->loader->add_action('wp_ajax_nopriv_lkn_get_rede_credit_data', $this, 'ajax_get_rede_credit_data');

        // Adiciona endpoint AJAX para parcelas Maxipago
        $this->loader->add_action('wp_ajax_lkn_get_maxipago_credit_data', $this, 'ajax_get_maxipago_credit_data');
        $this->loader->add_action('wp_ajax_nopriv_lkn_get_maxipago_credit_data', $this, 'ajax_get_maxipago_credit_data');
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
        if (isset($this->wc_maxipago_credit_class) && method_exists($this->wc_maxipago_credit_class, 'get_option')) {
            $max_installments = intval($this->wc_maxipago_credit_class->get_option('max_parcels_number'));
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
                    if ($product_limit < $max_installments) {
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

        $installments = [];
        for ($i = 1; $i <= $max_installments; $i++) {
            $installment_value = $cart_total / $i;
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
        if (isset($this->wc_rede_credit_class) && method_exists($this->wc_rede_credit_class, 'get_option')) {
            $max_installments = intval($this->wc_rede_credit_class->get_option('max_parcels_number'));

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
                    if ($product_limit < $max_installments) {
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

        $installments = [];
        for ($i = 1; $i <= $max_installments; $i++) {
            $installment_value = $cart_total / $i;
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
        wp_send_json([
            'cartTotal' => $cart_total,
            'installments' => $installments
        ]);
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
            $title = __('basic pix', 'woo-rede');
        }
        return $title;
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
        $this->loader->add_action('update_rede_orders', $this->wc_rede_class, 'updateRedeOrders');
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

        // Adiciona endpoint AJAX para parcelas Maxipago
        $this->loader->add_action('wp_ajax_lkn_get_maxipago_credit_data', $this, 'ajax_get_maxipago_credit_data');
        $this->loader->add_action('wp_ajax_nopriv_lkn_get_maxipago_credit_data', $this, 'ajax_get_maxipago_credit_data');

        // Adiciona endpoint AJAX para refresh dos campos de pagamento
        $this->loader->add_action('wp_ajax_rede_refresh_payment_fields', $this, 'rede_refresh_payment_fields');
        $this->loader->add_action('wp_ajax_nopriv_rede_refresh_payment_fields', $this, 'rede_refresh_payment_fields');

        // Adiciona endpoint AJAX para refresh dos campos de pagamento Maxipago
        $this->loader->add_action('wp_ajax_maxipago_refresh_payment_fields', $this, 'maxipago_refresh_payment_fields');
        $this->loader->add_action('wp_ajax_nopriv_maxipago_refresh_payment_fields', $this, 'maxipago_refresh_payment_fields');

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

    /**
     * Método AJAX para refresh dos campos de pagamento Rede Credit
     */
    public function rede_refresh_payment_fields()
    {
        try {
            // Verifica o nonce
            if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'rede_payment_fields_nonce')) {
                wp_send_json_error(['message' => 'Invalid nonce']);
                return;
            }

            // Garantir que o carrinho está inicializado e as taxas calculadas
            if (function_exists('WC') && WC()->cart) {
                WC()->cart->calculate_totals();
            }

            // Carrega a classe do gateway
            $gateways = WC()->payment_gateways()->payment_gateways();
            ob_start();

            // Usa o novo método que renderiza com o total atualizado incluindo cupons
            $gateways['rede_credit']->render_payment_fields_with_total();

            $html = ob_get_clean();
            wp_send_json_success(['html' => $html]);
            return;

            wp_send_json_error(['message' => 'Gateway not found or method not available']);
        } catch (\Throwable $th) {
            wp_send_json_error([
                'message' => $th->getMessage(),
                'line'    => $th->getLine(),
                'file'    => $th->getFile(),
                'trace'   => $th->getTraceAsString(),
            ]);
        }
    }

    /**
     * Método AJAX para refresh dos campos de pagamento Maxipago Credit
     */
    public function maxipago_refresh_payment_fields()
    {
        try {
            // Verifica o nonce
            if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'maxipago_payment_fields_nonce')) {
                wp_send_json_error(['message' => 'Invalid nonce']);
                return;
            }

            // Carrega a classe do gateway
            $gateways = WC()->payment_gateways()->payment_gateways();
            if (isset($gateways['maxipago_credit']) && method_exists($gateways['maxipago_credit'], 'render_payment_fields_with_total')) {
                ob_start();

                // Usa o novo método que renderiza com o total atualizado incluindo cupons
                $gateways['maxipago_credit']->render_payment_fields_with_total();

                $html = ob_get_clean();
                wp_send_json_success(['html' => $html]);
                return;
            }

            wp_send_json_error(['message' => 'Gateway not found or method not available']);
        } catch (\Throwable $th) {
            wp_send_json_error([
                'message' => $th->getMessage(),
                'line'    => $th->getLine(),
                'file'    => $th->getFile(),
                'trace'   => $th->getTraceAsString(),
            ]);
        }
    }

    public function add_gateway_name_to_notes_global($note_data, $args)
    {
        if (isset($note_data['comment_post_ID'])) {
            $order_id = $note_data['comment_post_ID'];
            $order = wc_get_order($order_id);

            if ($order && is_a($order, 'WC_Order')) {

                $payment_method = $order->get_payment_method();

                if (
                    strpos($payment_method, 'rede_') === 0 ||
                    strpos($payment_method, 'maxipago_') === 0 ||
                    strpos($payment_method, 'integration_rede_pix') === 0
                ) {

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
        // TODO traduzir para o inglês
        // Verificar se WooCommerce está ativo e a sessão existe
        if (!function_exists('WC') || !WC()->session) {
            return;
        }

        $chosen_payment_method = WC()->session->get('chosen_payment_method');
        
        // Verificar se é um método de pagamento Rede ou Maxipago
        if (!in_array($chosen_payment_method, ['rede_credit', 'maxipago_credit'])) {
            return;
        }

        // Obter a parcela selecionada da sessão baseada no método de pagamento
        $installment_session_key = '';
        if ($chosen_payment_method === 'rede_credit') {
            $installment_session_key = 'lkn_installments_number_rede_credit';
        } elseif ($chosen_payment_method === 'maxipago_credit') {
            $installment_session_key = 'lkn_installments_number_maxipago_credit';
        }

        $installment = WC()->session->get($installment_session_key);
        
        if (!$installment || $installment <= 0) {
            return;
        }

        // Obter o total do carrinho
        $cart_total = WC()->cart->get_total('raw');
        
        if ($cart_total <= 0) {
            return;
        }

        // Obter configurações do gateway para verificar o tipo de juros/desconto
        $settings = get_option('woocommerce_' . $chosen_payment_method . '_settings', array());

        // Determinar o nome do método de pagamento
        $payment_method_name = '';
        if ($chosen_payment_method === 'rede_credit') {
            $payment_method_name = 'Cartão de Crédito Rede';
        } elseif ($chosen_payment_method === 'maxipago_credit') {
            $payment_method_name = 'Cartão de Crédito Maxipago';
        }

        // Gerar a informação de pagamento e label dinâmico
        if ($installment == 1) {
            $payment_label = 'Pagamento';
            $payment_info = 'À vista';
        } else {
            $payment_label = 'Parcelamento';
            // Calcular valor da parcela (simples divisão)
            $installment_value = $cart_total / $installment;
            $formatted_value = wc_price($installment_value);

            $payment_info = sprintf(
                '%dx de %s',
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
}
