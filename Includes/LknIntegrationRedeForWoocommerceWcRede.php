<?php
namespace Lkn\IntegrationRedeForWoocommerce\Includes;

use WC_Order;
use WP_Query;

class LknIntegrationRedeForWoocommerceWcRede {

	const FILE    = __FILE__;
	const VERSION = '2.1.0';

	protected static $instance = null;

	public function __construct() {			
		$this->upgrade();		
	}

	public function register_scripts() {
	}

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}

	public static function get_templates_path() {
		return plugin_dir_path( __FILE__ ) . 'templates/';
	}

	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'integration-rede-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	public function add_gateway( $methods ) {
		array_push( $methods, 'WC_Rede_Credit' );

		return $methods;
	}

	private function upgrade() {
		if ( is_admin() ) {
			$version = get_option( 'wc_rede_version', '0' );

			if ( version_compare( $version, self::VERSION, '<' ) ) {
				if ( $options = get_option( 'woocommerce_rede_settings' ) ) {
					$credit_options = array(
						'enabled'            => $options['enabled'],
						'title'              => 'Ativar',

						'environment'        => $options['environment'],
						'token'              => $options['token'],
						'pv'                 => $options['pv'],

						'soft_descriptor'    => $options['soft_descriptor'],
						'auto_capture'       => $options['authorization'],

						'min_parcels_value'  => $options['smallest_installment'],
						'max_parcels_number' => $options['installments'],
					);

					update_option( 'woocommerce_rede_credit_settings', $credit_options );

					delete_option( 'woocommerce_rede_settings' );
				}

				update_option( 'wc_rede_version', self::VERSION );
			}
		}
	}

	public function woocommerce_missing_notice() {
		include_once dirname( __FILE__ ) . '/views/notices/html-notice-woocommerce-missing.php';
	}

	public function plugin_action_links( $links ) {
		$plugin_links = array();

		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.1', '>=' ) ) {
			$plugin_links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=rede_credit' ) ) . '">Configurações</a>';
		} else {
			$plugin_links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_rede_credit' ) ) . '">Configurações</a>';
		}

		return array_merge( $plugin_links, $links );
	}

	public function update_rede_orders() {
		$orders = new WP_Query(
			array(
				'post_type'   => 'shop_order',
				'post_status' => array( 'wc-on-hold', 'wc-processing' ),
			)
		);

		foreach ( $orders->posts as $order ) {
			$wc_order        = new WC_Order( $order->ID );
			$wc_id           = $wc_order->get_id();
			$payment_gateway = wc_get_payment_gateway_by_order( $wc_order );
			$order_id        = get_post_meta( $wc_id, '_wc_rede_order_id', true );
			$status          = get_post_meta( $wc_id, '_wc_rede_status', true );
			$tid             = $tid = get_post_meta( $wc_id, '_wc_rede_transaction_id', true );

			if ( $payment_gateway instanceof LknIntegrationRedeForWoocommerceWcRedeAbstract ) {
				if ( $status == 'PENDING' || $status == 'SUBMITTED' ) {
					$payment_gateway->consult_order( $wc_order, $order_id, $tid, $status );
				}
			}
		}

	}
}
