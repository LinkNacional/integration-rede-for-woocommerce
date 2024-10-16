<?php
namespace Lkn\IntegrationRedeForWoocommerce\Admin;

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
final class LknIntegrationRedeForWoocommerceAdmin {
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
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles(): void {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in LknIntegrationRedeForWoocommerceLoader as all of the hooks are defined
         * in that particular class.
         *
         * The LknIntegrationRedeForWoocommerceLoader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/lkn-integration-rede-for-woocommerce-admin.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts(): void {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in LknIntegrationRedeForWoocommerceLoader as all of the hooks are defined
         * in that particular class.
         *
         * The LknIntegrationRedeForWoocommerceLoader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_script('lknIntegrationRedeForWoocommerceProFields', plugin_dir_url(__FILE__) . 'js/lkn-integration-rede-for-woocommerce-admin-pro-fields.js', array('jquery'), $this->version, false);
        
        wp_localize_script('lknIntegrationRedeForWoocommerceProFields', 'lknPhpProFieldsVariables', array(
            'proSettings' => __('PRO Settings', 'woo-rede'),
            'license' => __('License', 'woo-rede'),
            'autoCapture' => __('Auto Capture', 'woo-rede'),
            'autoCaptureLabel' => __('Enables auto capture', 'woo-rede'),
            'customCssShortcode' => __('Custom CSS (Shortcode)', 'woo-rede'),
            'customCssBlockEditor' => __('Custom CSS (Block Editor)', 'woo-rede'),
            'interestOnInstallments' => __('Interest on installments', 'woo-rede'),
            'interestOnInstallmentsDescription' => __('Enables payment with interest in installments. Save to continue configuration. After enabling installment interest, you can define the amount of interest according to the installment.', 'woo-rede'),
            'licenseDescription' => __('License for Rede for WooCommerce plugin extensions.', 'woo-rede'),
            'autoCaptureDescription' => __('By enabling automatic capture, payment is automatically captured immediately after the transaction.', 'woo-rede'),
            'customCssShortcodeDescription' => __('Possibility to customize the shortcode CSS. Enter the selector and rules, example: .checkout{color:green;}.', 'woo-rede'),
            'customCssBlockEditorDescription' => __('Possibility to customize the CSS in the block editor checkout. Enter the selector and rules, example: .checkout{color:green;}.', 'woo-rede'),
            'becomePRO' => __('Become PRO', 'woo-rede')
        ));

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/lkn-integration-rede-for-woocommerce-admin.js', array('jquery'), $this->version, false);

        $gateways = array(
            'maxipago_credit',
            'maxipago_debit',
            'rede_credit',
            'rede_debit',
            'maxipago_pix',
            'rede_pix'
        );

        if ( $_GET['page'] === 'wc-settings' && $_GET['tab'] === 'checkout' && in_array($_GET['section'], $gateways) ) {
            wp_enqueue_script('lknIntegrationRedeForWoocommerceSettingsLayoutScript', plugin_dir_url(__FILE__) . 'js/lkn-integration-rede-for-woocommerce-settings-layout.js', array('jquery'), $this->version, false);
        }

        // Localize the script with custom data
        wp_localize_script($this->plugin_name, 'lknPhpVariables', array(
            'title' => __('Get new features with Rede Pro', 'woo-rede'),
            'desc' => __('Discover and purchase the PRO plugin', 'woo-rede'),
            'capture' => __('Manual capture of transaction/order', 'woo-rede'),
            'tax' => __('Adjust interest rate based on installment', 'woo-rede'),
            'css' => __('Custom CSS for payment forms', 'woo-rede'),
            'pix' => __('Enable payment with Pix', 'woo-rede'),
            'descriptionError' => __('Feature with error, disable to fix.', 'woo-rede'),
            'dirURL' => INTEGRATION_REDE_FOR_WOOCOMMERCE_DIR_URL,
            'freeHost' => __('Congratulations! You got 12 months free hosting for WooCommerce. Receive it now!', 'woo-rede')
        ));
    }
}
