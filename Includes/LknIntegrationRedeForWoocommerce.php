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
class LknIntegrationRedeForWoocommerce
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
		if (defined('LknIntegrationRedeForWoocommerce_VERSION')) {
			$this->version = LknIntegrationRedeForWoocommerce_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'lkn-integration-rede-for-woocommerce';

		$this->load_dependencies();
		$this->loader->add_action('plugins_loaded', $this, 'define_hooks');
	}

	//Define os hooks somente quando woocommerce está ativo
	public function define_hooks(){
		if (class_exists('WC_Payment_Gateway')) {
			$this->define_admin_hooks();
			$this->define_public_hooks();
		}
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
	private function load_dependencies()
	{
		$this->loader = new LknIntegrationRedeForWoocommerceLoader();
	}


	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks()
	{

		$plugin_admin = new LknIntegrationRedeForWoocommerceAdmin($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
		
		/* $wc_rede_class = new LknIntegrationRedeForWoocommerceWcRede();
		$this->loader->add_filter('plugin_action_links_' . plugin_basename( __FILE__ ), $wc_rede_class, 'plugin_action_links');
		if ( !class_exists( 'WC_Payment_Gateway' ) ) {
			$this->loader->add_action('admin_notices', $wc_rede_class, 'woocommerce_missing_notice');
		}	
		
		$wc_rede_credit_class = new LknIntegrationRedeForWoocommerceWcRedeCredit();
		if ( ! $wc_rede_credit_class->auto_capture ) {
			$this->loader->add_action('woocommerce_order_status_completed', $wc_rede_credit_class, 'process_capture');
		}

		$this->loader->add_action('woocommerce_order_status_cancelled', $wc_rede_credit_class, 'process_refund');
		$this->loader->add_action('woocommerce_order_status_refunded', $wc_rede_credit_class, 'process_refund');
		$this->loader->add_action('woocommerce_update_options_payment_gateways_' . $wc_rede_credit_class->id, $wc_rede_credit_class, 'process_admin_options'); //TODO verificar se pode remover caso não esteja sendo ativado
		$this->loader->add_action('woocommerce_api_wc_rede_credit', $wc_rede_credit_class, 'check_return'); //TODO verificar se pode remover caso não esteja sendo ativado
		$this->loader->add_filter('woocommerce_get_order_item_totals', $wc_rede_credit_class,'order_items_payment_details', 10, 2);
		$this->loader->add_action('woocommerce_admin_order_data_after_billing_address', $wc_rede_credit_class,'display_meta', 10, 1);		
		 */
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks()
	{

		$plugin_public = new LknIntegrationRedeForWoocommercePublic($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
		
		$wc_rede_class = new LknIntegrationRedeForWoocommerceWcRede();
		
		/* $this->loader->add_action('plugins_loaded', $wc_rede_class::get_instance(), 'get_instance');
		$this->loader->add_action('update_rede_orders', $wc_rede_class, 'update_rede_orders'); */
		/* $this->loader->add_action('init', $wc_rede_class, 'load_plugin_textdomain'); */
		/* $this->loader->add_action('woocommerce_order_status_on-hold_to_processing', $wc_rede_class, 'capture_payment'); //TODO verificar se pode remover caso não esteja sendo ativado */
		/* $this->loader->add_filter('woocommerce_payment_gateways', $wc_rede_class, 'addMehtodspaymnes'); */ //TODO verificar porque as funções não estão sendo executadas

		/* $this->loader->add_action('wp_enqueue_scripts', $wc_rede_class, 'register_scripts'); */
		
		/* $wc_rede_credit_class = new LknIntegrationRedeForWoocommerceWcRedeCredit();
		$this->loader->add_action('woocommerce_thankyou_' . $wc_rede_credit_class->id, $wc_rede_credit_class, 'thankyou_page');
		$this->loader->add_action('wp_enqueue_scripts', $wc_rede_credit_class,'checkout_scripts'); */

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run()
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
}
