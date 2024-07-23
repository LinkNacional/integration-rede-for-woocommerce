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
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/lkn-integration-rede-for-woocommerce-admin.js', array('jquery'), $this->version, false);

        // Localize the script with custom data
        wp_localize_script($this->plugin_name, 'lknPhpTranslations', array(
            'title' => __('Get new features with Rede Pro', 'integration-rede-for-woocommerce'),
            'desc' => __('Discover and purchase the PRO plugin', 'integration-rede-for-woocommerce'),
            'capture' => __('Manual capture of transaction/order', 'integration-rede-for-woocommerce'),
            'tax' => __('Adjust interest rate based on installment', 'integration-rede-for-woocommerce'),
            'css' => __('Custom CSS for payment forms', 'integration-rede-for-woocommerce'),
            'pix' => __('Enable payment with Pix', 'integration-rede-for-woocommerce'),
            'freeHost' => __('Congratulations! You have won a free WooCommerce hosting for 12 months. Claim it now!', 'integration-rede-for-woocommerce')
        ));
    }
}
