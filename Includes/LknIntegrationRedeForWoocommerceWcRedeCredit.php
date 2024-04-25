<?php
namespace Lkn\IntegrationRedeForWoocommerce\Includes;

use Exception;
use Lkn\IntegrationRedeForWoocommerce\Includes\LknIntegrationRedeForWoocommerceWcRedeAbstract;
use WC_Order;
use WP_Error;

class LknIntegrationRedeForWoocommerceWcRedeCredit extends LknIntegrationRedeForWoocommerceWcRedeAbstract {


	public $api = null;

	public function __construct() {
		$this->id = 'rede_credit';
		$this->has_fields = true;
		$this->method_title = esc_attr__( 'Pay with the Rede', 'integration-rede-for-woocommerce' );
		$this->method_description = esc_attr__( 'Enables and configures payments with Rede', 'integration-rede-for-woocommerce' );
		$this->supports = array(
			'products',
			'refunds',
		);

		$this->init_form_fields();

		$this->init_settings();

		$this->title = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );

		$this->environment = $this->get_option( 'environment' );
		$this->pv = $this->get_option( 'pv' );
		$this->token = $this->get_option( 'token' );

		$this->soft_descriptor = $this->get_option( 'soft_descriptor' );

		$this->auto_capture = $this->get_option( 'auto_capture' );
		$this->max_parcels_number = $this->get_option( 'max_parcels_number' );
		$this->min_parcels_value = $this->get_option( 'min_parcels_value' );

		$this->partner_module = $this->get_option( 'module' );
		$this->partner_gateway = $this->get_option( 'gateway' );

		$this->debug = $this->get_option( 'debug' );

		if ( 'yes' == $this->debug ) {
			$this->log = $this->get_logger();
		}
		
		$this->api = new LknIntegrationRedeForWoocommerceWcRedeAPI( $this );
	}

	public function display_meta( $order ) {
		if ( $order->get_payment_method() === 'rede_credit' ) {
			$meta_keys = array(
				'_wc_rede_transaction_environment' => esc_attr__( 'Environment', 'integration-rede-for-woocommerce' ),
				'_wc_rede_transaction_return_code' => esc_attr__( 'Return Code', 'integration-rede-for-woocommerce' ),
				'_wc_rede_transaction_return_message' => esc_attr__( 'Return Message', 'integration-rede-for-woocommerce' ),
				'_wc_rede_transaction_id' => esc_attr__( 'Transaction ID', 'integration-rede-for-woocommerce' ),
				'_wc_rede_transaction_refund_id' => esc_attr__( 'Refund ID', 'integration-rede-for-woocommerce' ),
				'_wc_rede_transaction_cancel_id' => esc_attr__( 'Cancellation ID', 'integration-rede-for-woocommerce' ),
				'_wc_rede_transaction_nsu' => esc_attr__( 'Nsu', 'integration-rede-for-woocommerce' ),
				'_wc_rede_transaction_authorization_code' => esc_attr__( 'Authorization Code', 'integration-rede-for-woocommerce' ),
				'_wc_rede_transaction_bin' => esc_attr__( 'Bin', 'integration-rede-for-woocommerce' ),
				'_wc_rede_transaction_last4' => esc_attr__( 'Last 4', 'integration-rede-for-woocommerce' ),
				'_wc_rede_transaction_installments' => esc_attr__( 'Installments', 'integration-rede-for-woocommerce' ),
				'_wc_rede_transaction_holder' => esc_attr__( 'Cardholder', 'integration-rede-for-woocommerce' ),
				'_wc_rede_transaction_expiration' => esc_attr__( 'Card Expiration', 'integration-rede-for-woocommerce' )
			);
		
			$this->generate_meta_table( $order, $meta_keys, 'Rede');
		
		}
	}
	

	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'   => esc_attr__( 'Enable/Disable', 'integration-rede-for-woocommerce' ),
				'type'    => 'checkbox',
				'label'   => esc_attr__( 'Enables payment with Rede', 'integration-rede-for-woocommerce' ),
				'default' => 'yes',
			),
			'title' => array(
				'title'   => esc_attr__( 'Title', 'integration-rede-for-woocommerce' ),
				'type'    => 'text',
				'default' => esc_attr__( 'Pay with Rede', 'integration-rede-for-woocommerce' ),
			),

			'rede' => array(
				'title' => esc_attr__( 'General configuration', 'integration-rede-for-woocommerce' ),
				'type'  => 'title',
			),
			'environment' => array(
				'title'       => esc_attr__( 'Environment', 'integration-rede-for-woocommerce' ),
				'type'        => 'select',
				'description' => esc_attr__( 'Choose the environment', 'integration-rede-for-woocommerce' ),
				'desc_tip'    => true,
				'class'       => 'wc-enhanced-select',
				'default'     => esc_attr__( 'test', 'integration-rede-for-woocommerce' ),
				'options' => array(
					'test'       => esc_attr__( 'Tests', 'integration-rede-for-woocommerce' ),
					'production' => esc_attr__( 'Production', 'integration-rede-for-woocommerce' ),
				),
			),
			'pv' => array(
				'title'   => esc_attr__( 'PV', 'integration-rede-for-woocommerce' ),
				'type'    => 'text',
				'default' => '',
			),
			'token' => array(
				'title'   => esc_attr__( 'Token', 'integration-rede-for-woocommerce' ),
				'type'    => 'text',
				'default' => '',
			),

			'soft_descriptor' => array(
				'title'   => esc_attr__( 'Soft Descriptor', 'integration-rede-for-woocommerce' ),
				'type'    => 'text',
				'default' => '',
			),

			'credit_options' => array(
				'title' => esc_attr__( 'Credit Card Settings', 'integration-rede-for-woocommerce' ),
				'type'  => 'title',
			),

			'auto_capture' => array(
				'title'   => esc_attr__( 'Authorization and Capture', 'integration-rede-for-woocommerce' ),
				'type'    => 'select',
				'class'   => 'wc-enhanced-select',
				'default' => '2',
				'options' => array(
					'1' => esc_attr__( 'Authorize and capture automatically', 'integration-rede-for-woocommerce' ),
					'0' => esc_attr__( 'Just authorize', 'integration-rede-for-woocommerce' ),
				),
			),
			'min_parcels_value' => array(
				'title'   => esc_attr__( 'Value of the smallest installment', 'integration-rede-for-woocommerce' ),
				'type'    => 'text',
				'default' => '0',
			),
			'max_parcels_number' => array(
				'title'   => esc_attr__( 'Max installments', 'integration-rede-for-woocommerce' ),
				'type'    => 'select',
				'class'   => 'wc-enhanced-select',
				'default' => '12',
				'options' => array(
					'1'  => '1x',
					'2'  => '2x',
					'3'  => '3x',
					'4'  => '4x',
					'5'  => '5x',
					'6'  => '6x',
					'7'  => '7x',
					'8'  => '8x',
					'9'  => '9x',
					'10' => '10x',
					'11' => '11x',
					'12' => '12x',
				),
			),

			'partners' => array(
				'title' => esc_attr__( 'Partner Settings', 'integration-rede-for-woocommerce' ),
				'type'  => 'title',
			),
			'module' => array(
				'title'   => esc_attr__( 'Module ID', 'integration-rede-for-woocommerce' ),
				'type'    => 'text',
				'default' => '',
			),
			'gateway' => array(
				'title'   => esc_attr__( 'Gateway ID', 'integration-rede-for-woocommerce' ),
				'type'    => 'text',
				'default' => '',
			),

			'developers' => array(
				'title' => esc_attr__( 'Developer Settings', 'integration-rede-for-woocommerce' ),
				'type'  => 'title',
			),

			'debug' => array(
				'title'   => esc_attr__( 'Debug', 'integration-rede-for-woocommerce' ),
				'type'    => 'checkbox',
				'label'   => esc_attr__( 'Enable debug logs', 'integration-rede-for-woocommerce' ),
				'default' => esc_attr__( 'no', 'integration-rede-for-woocommerce' ),
			),
		);
	}

	public function get_installment_text( $quantity, $order_total ) {
		$installments = $this->get_installments( $order_total );

		if ( isset( $installments[ $quantity - 1 ] ) ) {
			return $installments[ $quantity - 1 ]['label'];
		}

		if ( isset( $installments[ $quantity ] ) ) {
			return $installments[ $quantity ]['label'];
		}

		return $quantity;
	}

	public function get_installments( $order_total = 0 ) {
		$installments = [];
		$defaults     = array(
			'min_value'   => $this->min_parcels_value,
			'max_parcels' => $this->max_parcels_number,
		);

		$installments_result = wp_parse_args( apply_filters( 'integration_rede_installments', $defaults ), $defaults );

		$min_value   = (float) $installments_result['min_value'];
		$max_parcels = (int) $installments_result['max_parcels'];

		for ( $i = 1; $i <= $max_parcels; ++$i ) {
			if ( ( $order_total / $i ) < $min_value ) break;

			$label = sprintf( '%dx de %s', $i, wp_strip_all_tags( wc_price( $order_total / $i ) ) );

			if ( $i === 1 ) $label .= ' (à vista)';

			$installments[] = array(
				'num'   => $i,
				'label' => $label,
			);
		}

		return $installments;
	}

	public function checkout_scripts() {
		
		$plugin_url = plugin_dir_url( LknIntegrationRedeForWoocommerceWcRede::FILE ).'../';
		wp_enqueue_script( 'fix-infinite-loading-js', $plugin_url . 'assets/js/fix-infinite-loading.js', array(), '1.0.0', true );
		
		
		if ( ! is_checkout() ) {
			return;
		}

		if ( ! $this->is_available() ) {
			return;
		}

		wp_enqueue_style( 'wc-rede-checkout-webservice' );


		wp_enqueue_style( 'card-style', $plugin_url . 'assets/css/card.css', array(), '1.0.0', 'all' );
		wp_enqueue_style( 'woo-maxipago-style', $plugin_url . 'assets/css/style-maxipago.css', array(), '1.0.0', 'all' );
		wp_enqueue_style( 'woo-rede-style', $plugin_url . 'assets/css/style-rede.css', array(), '1.0.0', 'all' );

		wp_enqueue_script( 'woo-rede-js', $plugin_url . 'assets/js/woo-rede.js', array(), '1.0.0', true );
		wp_enqueue_script( 'woo-maxipago-js', $plugin_url . 'assets/js/woo-maxipago.js', array(), '1.0.0', true );
		wp_enqueue_script( 'woo-rede-animated-card-jquery', $plugin_url . 'assets/js/jquery.card.js', array( 'jquery', 'woo-rede-js' ), '2.5.0', true );

		wp_localize_script( 'woo-rede-js', 'wooRede', [
			'debug' => defined( 'WP_DEBUG' ) && WP_DEBUG,
		] );
	}

	public function process_payment( $order_id ) {
		
		if(!wp_verify_nonce($_POST['rede_card_nonce'], 'redeCardNonce')){
			return array(
				'result'   => 'fail',
				'redirect' => '',
			);
		}
		
		$order       = wc_get_order( $order_id );
		$card_number = isset( $_POST['rede_credit_number'] ) ? 
			sanitize_text_field( $_POST['rede_credit_number'] ) : '';
		$valid       = true;
	
		if ( $valid ) {
			$valid = $this->validate_card_number( $card_number );
		}
	
		if ( $valid ) {
			$valid = $this->validate_card_fields( $_POST );
		}
	
		if ( $valid ) {
			$valid = $this->validate_installments( $_POST, $order->get_total() );
		}
	
		if ( $valid ) {
			$installments = isset( $_POST['rede_credit_installments'] ) ? 
				absint( sanitize_text_field($_POST['rede_credit_installments']) ) : 1;
			
			$credit_expiry = sanitize_text_field($_POST['rede_credit_expiry']);
			
			if (strpos($credit_expiry, '/') !== false) {
				$expiration   = explode( '/', $credit_expiry );
			} else {
				$expiration = [
					substr($credit_expiry, 0, 2),
					substr($credit_expiry, -2, 2),
				];
			}			
	
			$card_data = array(
				'card_number'           => preg_replace( '/[^\d]/', '', sanitize_text_field( $_POST['rede_credit_number'] ) ),
				'card_expiration_month' => sanitize_text_field( $expiration[0] ),
				'card_expiration_year'  => $this->normalize_expiration_year( sanitize_text_field( $expiration[1] ) ),
				'card_cvv'              => sanitize_text_field( $_POST['rede_credit_cvc'] ),
				'card_holder'           => sanitize_text_field( $_POST['rede_credit_holder_name'] ),
			);
	
			try {
				$order_id    = $order->get_id();
				$amount      = $order->get_total();
				$transaction = $this->api->do_transaction_request( $order_id + time(), $amount, $installments, $card_data );
	
				$order->update_meta_data( '_transaction_id', $transaction->getTid() );
				$order->update_meta_data( '_wc_rede_transaction_return_code', $transaction->getReturnCode() );
				$order->update_meta_data( '_wc_rede_transaction_return_message', $transaction->getReturnMessage() );
				$order->update_meta_data( '_wc_rede_transaction_installments', $installments );
				$order->update_meta_data( '_wc_rede_transaction_id', $transaction->getTid() );
				$order->update_meta_data( '_wc_rede_transaction_refund_id', $transaction->getRefundId() );
				$order->update_meta_data( '_wc_rede_transaction_cancel_id', $transaction->getCancelId() );
				$order->update_meta_data( '_wc_rede_transaction_bin', $transaction->getCardBin() );
				$order->update_meta_data( '_wc_rede_transaction_last4', $transaction->getLast4() );
				$order->update_meta_data( '_wc_rede_transaction_nsu', $transaction->getNsu() );
				$order->update_meta_data( '_wc_rede_transaction_authorization_code', $transaction->getAuthorizationCode() );
	
				$authorization = $transaction->getAuthorization();
	
				if ( ! is_null( $authorization ) ) {
					$order->update_meta_data( '_wc_rede_transaction_authorization_status', $authorization->getStatus() );
				}
	
				$order->update_meta_data( '_wc_rede_transaction_holder', $transaction->getCardHolderName() );
				$order->update_meta_data( '_wc_rede_transaction_expiration', sprintf( '%02d/%d', $expiration[0], intval($expiration[1]) ) );
	
				$order->update_meta_data( '_wc_rede_transaction_holder', $transaction->getCardHolderName() );
	
				$authorization = $transaction->getAuthorization();
	
				if ( ! is_null( $authorization ) ) {
					$order->update_meta_data( '_wc_rede_transaction_authorization_status', $authorization->getStatus() );
				}
								
				$order->update_meta_data( '_wc_rede_transaction_environment', $this->environment );
				
				$this->process_order_status( $order, $transaction, '' );
				$order->save();
			} catch ( Exception $e ) {
				$this->add_error( $e->getMessage() );
				$valid = false;
			}
		}
	
		if ( $valid ) {
			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order ),
			);
		} else {
			return array(
				'result'   => 'fail',
				'redirect' => '',
			);
		}
	}
	

	public function process_refund( $order_id, $amount = null, $reason = '' ) {
		$order = new WC_Order( $order_id );

		if ( ! $order || ! $order->get_transaction_id() ) {
			return false;
		}

		if ( empty( $order->get_meta( '_wc_rede_transaction_canceled' ) ) ) {
			$tid    = $order->get_transaction_id();
			$amount = wc_format_decimal( $amount );

			try {
				$transaction = $this->api->do_transaction_cancellation( $tid, $amount );

				update_post_meta( $order_id, '_wc_rede_transaction_refund_id', $transaction->getRefundId() );
				update_post_meta( $order_id, '_wc_rede_transaction_cancel_id', $transaction->getCancelId() );
				update_post_meta( $order_id, '_wc_rede_transaction_canceled', true );

				$order->add_order_note( esc_attr_e( 'Refunded:', 'integration-rede-for-woocommerce' ) . wc_price( $amount ) );
			} catch ( Exception $e ) {
				return new WP_Error( 'rede_refund_error', sanitize_text_field( $e->getMessage() ) );
			}

			return true;
		}

		return false;
	}

	public function process_capture( $order_id ) {
		$order = new WC_Order( $order_id );

		if ( ! $order || ! $order->get_transaction_id() ) {
			return false;
		}

		if ( empty( $order->get_meta( '_wc_rede_captured' ) ) ) {
			$tid    = $order->get_transaction_id();
			$amount = $order->get_total();

			try {
				$transaction = $this->api->do_transaction_capture( $tid, $amount );

				update_post_meta( $order_id, '_wc_rede_transaction_nsu', $transaction->getNsu() );
				update_post_meta( $order_id, '_wc_rede_captured', true );

				$order->add_order_note( esc_attr_e( 'Captured', 'integration-rede-for-woocommerce' ) );
			} catch ( Exception $e ) {
				return new WP_Error( 'rede_capture_error', sanitize_text_field( $e->getMessage() ) );
			}

			return true;
		}

		return false;
	}

	protected function get_checkout_form( $order_total = 0 ) {
		$wc_get_template = 'woocommerce_get_template';

		if ( function_exists( 'wc_get_template' ) ) {
			$wc_get_template = 'wc_get_template';
		}

		$wc_get_template(
			'credit-card/rede-payment-form.php',
			array(
				'installments' => $this->get_installments( $order_total ),
			),
			'woocommerce/rede/',
			LknIntegrationRedeForWoocommerceWcRede::get_templates_path()
		);
	}
}
