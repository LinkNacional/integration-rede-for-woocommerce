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
    }

    public function process_refund($order_id, $amount = null, $reason = '')
    {
        $order = new WC_Order($order_id);
        if ($order->get_payment_method() === 'maxipago_credit') {
            
            $totalAmount = $order->get_meta('_wc_maxipago_total_amount');
            $environment = $this->get_option('environment');
            $orderId = $order->get_meta('_wc_maxipago_transaction_id');
            $referenceNum = $order->get_meta('_wc_maxipago_transaction_reference_num');
            $merchantId = sanitize_text_field($this->get_option('merchant_id'));
            $merchantKey = sanitize_text_field($this->get_option('merchant_key'));

            // Get conversion meta
            $is_converted = $order->get_meta('_wc_maxipago_total_amount_is_converted');
            $exchange_rate = $order->get_meta('_wc_maxipago_exchange_rate');
            $decimals = $order->get_meta('_wc_maxipago_decimal_value');
            $amount = $order->get_total();
            $amount_converted = $order->get_meta('_wc_maxipago_total_amount_converted');

            if (empty($order->get_meta('_wc_maxipago_transaction_canceled'))) {
                $amount = wc_format_decimal($amount);

                try {
                    if ($order->get_total() == $amount) {
                        $amount = $totalAmount;
                    }

                    // If converted, calculate refund in BRL using exchange rate
                    if ($is_converted && $exchange_rate) {
                        $amount_brl = floatval($amount) / floatval($exchange_rate);
                        // Format with correct decimals
                        $amount_brl = number_format($amount_brl, (int)$decimals, '.', '');
                        $amount = $amount_brl;
                    }

                    if ('production' === $environment) {
                        $apiUrl = 'https://api.maxipago.net/UniversalAPI/postXML';
                    } else {
                        $apiUrl = 'https://testapi.maxipago.net/UniversalAPI/postXML';
                    }

                    $xmlData = "<?xml version='1.0' encoding='UTF-8'?>
                        <transaction-request>
                            <version>3.1.1.15</version>
                            <verification>
                                <merchantId>$merchantId</merchantId>
                                <merchantKey>$merchantKey</merchantKey>
                            </verification>
                            <order>
                                <return>
                                    <orderID>$orderId</orderID>
                                    <referenceNum>$referenceNum</referenceNum>
                                    <payment>
                                        <chargeTotal>$amount</chargeTotal>
                                    </payment>
                                </return>
                            </order>
                        </transaction-request>
                    ";

                    $args = array(
                        'body' => $xmlData,
                        'headers' => array(
                            'Content-Type' => 'application/xml'
                        ),
                        'sslverify' => false // Desativa a verificação do certificado SSL
                    );

                    $response = wp_remote_post($apiUrl, $args);

                    if (is_wp_error($response)) {
                        $error_message = $response->get_error_message();
                        $order->add_order_note('Maxipago[Refund Error] ' . esc_attr($error_message));
                        $order->save();
                        return false;
                    } else {
                        $response_body = wp_remote_retrieve_body($response);
                        $xml = simplexml_load_string($response_body);
                    }

                    //Reconstruindo o $xml para facilitar o uso da variavel
                    $xml_encode = wp_json_encode($xml);
                    $xml_decode = json_decode($xml_encode, true);

                    if ("APPROVED" == $xml_decode['processorMessage']) {
                        update_post_meta($order_id, '_wc_maxipago_transaction_refund_id', $xml_decode['transactionID']);
                        update_post_meta($order_id, '_wc_maxipago_transaction_cancel_id', $xml_decode['transactionID']);
                        update_post_meta($order_id, '_wc_maxipago_transaction_canceled', true);
                        $order->add_order_note(__('Refunded:', 'woo-rede') . ' ' . wc_price($amount));
                    }

                    if ("INVALID REQUEST" == $xml_decode['responseMessage']) {
                        $order->add_order_note('Maxipago[Refund Error] ' . ($xml_decode['errorMessage'] ?? 'Erro desconhecido.'));
                        $order->save();
                        return false;
                    }

                    $order->save();

                    $this->log->log('info', $this->id, array(
                        'order' => array(
                            'amount' => $amount,
                            'totalAmount' => $totalAmount,
                            'totalOrder' => $order->get_total(),
                            'is_converted' => $is_converted,
                            'exchange_rate' => $exchange_rate,
                            'decimals' => $decimals,
                        ),
                    ));
                } catch (Exception $e) {
                    $order->add_order_note('Maxipago[Refund Error] ' . sanitize_text_field($e->getMessage()));
                    $order->save();
                    return false;
                }

                return true;
            } else {
                $order->add_order_note(
                    'Maxipago[Refund Error] ' . esc_attr__('Reembolso total já realizado, verifique no bloco observações do pedido.', 'woo-rede')
                );
                $order->save();
                return false;
            }
        } else {
            return false;
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
        $url = 'https://www.linknacional.com.br/wordpress/plugins/';
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
}
