<?php
namespace Lkn\IntegrationRedeForWoocommerce\Includes;

use Exception;
use Lkn\IntegrationRedeForWoocommerce\Includes\LknIntegrationRedeForWoocommerceWcRedeAbstract;
use WC_Order;
use WP_Error;

final class LknIntegrationRedeForWoocommerceWcRedeDebit extends LknIntegrationRedeForWoocommerceWcRedeAbstract {
    public $api = null;

    public function __construct() {
        $this->id = 'rede_debit';
        $this->has_fields = true;
        $this->method_title = esc_attr__( 'Pay with the Rede Debit', 'integration-rede-for-woocommerce' );
        $this->method_description = esc_attr__( 'Enables and configures payments with Rede Debit', 'integration-rede-for-woocommerce' );
        $this->supports = array(
            'products',
            'refunds',
        );

        $this->initFormFields();

        $this->init_settings();

        $this->title = $this->get_option( 'title' );
        $this->description = $this->get_option( 'description' );

        $this->environment = $this->get_option( 'environment' );
        $this->pv = $this->get_option( 'pv' );
        $this->token = $this->get_option( 'token' );

        $this->soft_descriptor = preg_replace('/\W/', '', $this->get_option( 'soft_descriptor' ));

        $this->auto_capture = 1;
        $this->max_parcels_number = $this->get_option( 'max_parcels_number' );
        $this->min_parcels_value = $this->get_option( 'min_parcels_value' );

        $this->partner_module = $this->get_option( 'module' );
        $this->partner_gateway = $this->get_option( 'gateway' );

        $this->debug = $this->get_option( 'debug' );

        $this->log = $this->get_logger();

        $this->api = new LknIntegrationRedeForWoocommerceWcRedeAPI( $this );
        $this->configs = $this->getConfigsRedeDebit();
    }

    public function displayMeta( $order ): void {
        if ( $order->get_payment_method() === 'rede_debit' ) {
            $metaKeys = array(
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
                '_wc_rede_transaction_holder' => esc_attr__( 'Cardholder', 'integration-rede-for-woocommerce' ),
                '_wc_rede_transaction_expiration' => esc_attr__( 'Card Expiration', 'integration-rede-for-woocommerce' )
            );

            $this->generateMetaTable( $order, $metaKeys, 'Rede');
        }
    }

    /**
     * This function centralizes the data in one spot for ease mannagment
     *
     * @return array
     */
    public function getConfigsRedeDebit() {
        $configs = array();

        $configs['basePath'] = INTEGRATION_REDE_FOR_WOOCOMMERCE_DIR . 'Includes/logs/';
        $configs['base'] = $configs['basePath'] . gmdate('d.m.Y-H.i.s') . '.RedeDebit.log';
        $configs['debug'] = $this->get_option('debug');

        return $configs;
    }

    public function initFormFields(): void {
        $this->form_fields = array(
            'enabled' => array(
                'title' => esc_attr__( 'Enable/Disable', 'integration-rede-for-woocommerce' ),
                'type' => 'checkbox',
                'label' => esc_attr__( 'Enables payment with Rede', 'integration-rede-for-woocommerce' ),
                'default' => 'no',
            ),
            'title' => array(
                'title' => esc_attr__( 'Title', 'integration-rede-for-woocommerce' ),
                'type' => 'text',
                'default' => esc_attr__( 'Pay with the Rede Debit', 'integration-rede-for-woocommerce' ),
            ),

            'rede' => array(
                'title' => esc_attr__( 'General configuration', 'integration-rede-for-woocommerce' ),
                'type' => 'title',
            ),
            'environment' => array(
                'title' => esc_attr__( 'Environment', 'integration-rede-for-woocommerce' ),
                'type' => 'select',
                'description' => esc_attr__( 'Choose the environment', 'integration-rede-for-woocommerce' ),
                'desc_tip' => true,
                'class' => 'wc-enhanced-select',
                'default' => esc_attr__( 'test', 'integration-rede-for-woocommerce' ),
                'options' => array(
                    'test' => esc_attr__( 'Tests', 'integration-rede-for-woocommerce' ),
                    'production' => esc_attr__( 'Production', 'integration-rede-for-woocommerce' ),
                ),
            ),
            'pv' => array(
                'title' => esc_attr__( 'PV', 'integration-rede-for-woocommerce' ),
                'type' => 'password',
                'description' => esc_attr__( 'Your Rede PV (affiliation number).', 'integration-rede-for-woocommerce' ),
                'desc_tip' => true,
                'custom_attributes' => array(
                    'required' => 'required'
                ),
                'default' => $options['pv'] ?? '',
            ),
            'token' => array(
                'title' => esc_attr__( 'Token', 'integration-rede-for-woocommerce' ),
                'type' => 'password',
                'description' => esc_attr__( 'Your Rede Token.', 'integration-rede-for-woocommerce' ),
                'desc_tip' => true,
                'custom_attributes' => array(
                    'required' => 'required'
                ),
                'default' => $options['token'] ?? '',
            ),

            'soft_descriptor' => array(
                'title' => esc_attr__( 'Payment Description', 'integration-rede-for-woocommerce' ),
                'type' => 'text',
                'default' => esc_attr__( 'Payment', 'integration-rede-for-woocommerce' ),
                'custom_attributes' => array(
                    'maxlength' => 20,
                ),
            ),

            'debit_options' => array(
                'title' => esc_attr__( 'Debit Card Settings', 'integration-rede-for-woocommerce' ),
                'type' => 'title',
            ),
            'partners' => array(
                'title' => esc_attr__( 'Partner Settings', 'integration-rede-for-woocommerce' ),
                'type' => 'title',
            ),
            'module' => array(
                'title' => esc_attr__( 'Module ID', 'integration-rede-for-woocommerce' ),
                'type' => 'text',
                'default' => '',
            ),
            'gateway' => array(
                'title' => esc_attr__( 'Gateway ID', 'integration-rede-for-woocommerce' ),
                'type' => 'text',
                'default' => '',
            ),

            'developers' => array(
                'title' => esc_attr__( 'Developer Settings', 'integration-rede-for-woocommerce' ),
                'type' => 'title',
            ),

            'debug' => array(
                'title' => esc_attr__( 'Debug', 'integration-rede-for-woocommerce' ),
                'type' => 'checkbox',
                'label' => esc_attr__( 'Enable debug logs.' . ' ', 'integration-rede-for-woocommerce' ) . wp_kses_post( '<a href="' . esc_url( admin_url( 'admin.php?page=wc-status&tab=logs' ) ) . '" target="_blank">' . __('See logs', 'integration-rede-for-woocommerce') . '</a>'),
                'default' => esc_attr__( 'no', 'integration-rede-for-woocommerce' ),
            ),
        );

        $customConfigs = apply_filters('integrationRedeGetCustomConfigs', $this->form_fields, array(), $this->id);

        if ( ! empty($customConfigs)) {
            $this->form_fields = array_merge($this->form_fields, $customConfigs);
        }
    }

    public function checkoutScripts(): void {
        $plugin_url = plugin_dir_url( LknIntegrationRedeForWoocommerceWcRede::FILE ) . '../';
        wp_enqueue_script( 'fixInfiniteLoading-js', $plugin_url . 'Public/js/fixInfiniteLoading.js', array(), '1.0.0', true );

        if ( ! is_checkout() ) {
            return;
        }

        if ( ! $this->is_available() ) {
            return;
        }

        wp_enqueue_style( 'wc-rede-checkout-webservice' );

        wp_enqueue_style( 'card-style', $plugin_url . 'Public/css/card.css', array(), '1.0.0', 'all' );
        wp_enqueue_style( 'select-style', $plugin_url . 'Public/css/lknIntegrationRedeForWoocommerceSelectStyle.css', array(), '1.0.0', 'all' );
        wp_enqueue_style( 'wooRedeDebit-style', $plugin_url . 'Public/css/rede/styleRedeDebit.css', array(), '1.0.0', 'all' );

        wp_enqueue_script( 'wooRedeDebit-js', $plugin_url . 'Public/js/debitCard/rede/wooRedeDebit.js', array(), '1.0.0', true );
        wp_enqueue_script( 'woo-rede-animated-card-jquery', $plugin_url . 'Public/js/jquery.card.js', array('jquery', 'wooRedeDebit-js'), '2.5.0', true );

        wp_localize_script( 'wooRedeDebit-js', 'wooRede', array(
            'debug' => defined( 'WP_DEBUG' ) && WP_DEBUG,
        ));

        apply_filters('integrationRedeSetCustomCSSPro', get_option('woocommerce_rede_debit_settings')['custom_css_short_code'] ?? false);
    }

    public function process_payment( $order_id ) {
        if ( ! wp_verify_nonce($_POST['rede_card_nonce'], 'redeCardNonce')) {
            return array(
                'result' => 'fail',
                'redirect' => '',
            );
        }

        $order = wc_get_order( $order_id );
        $cardNumber = isset( $_POST['rede_debit_number'] ) ?
            sanitize_text_field( $_POST['rede_debit_number'] ) : '';

        $debitExpiry = sanitize_text_field($_POST['rede_debit_expiry']);

        if (strpos($debitExpiry, '/') !== false) {
            $expiration = explode( '/', $debitExpiry );
        } else {
            $expiration = array(
                substr($debitExpiry, 0, 2),
                substr($debitExpiry, -2, 2),
            );
        }

        $cardData = array(
            'card_number' => preg_replace( '/[^\d]/', '', sanitize_text_field( $_POST['rede_debit_number'] ) ),
            'card_expiration_month' => sanitize_text_field( $expiration[0] ),
            'card_expiration_year' => $this->normalize_expiration_year( sanitize_text_field( $expiration[1] ) ),
            'card_cvv' => sanitize_text_field( $_POST['rede_debit_cvc'] ),
            'card_holder' => sanitize_text_field( $_POST['rede_debit_holder_name'] ),
        );

        try {
            $valid = $this->validate_card_number( $cardNumber );
            if ( $valid ) {
                throw new Exception( __( 'Please enter a valid debit card number', 'integration-rede-for-woocommerce' ) );
            }

            $valid = $this->validate_card_fields( $_POST );
            if ( $valid ) {
                throw new Exception(__('One or more invalid fields', 'integration-rede-for-woocommerce'), 500);
            }

            $orderId = $order->get_id();
            $amount = $order->get_total();

            $transaction = $this->api->doTransactionDebitRequest( $orderId + time(), $amount, $cardData );

            $order->update_meta_data( '_transaction_id', $transaction->getTid() );
            $order->update_meta_data( '_wc_rede_transaction_return_code', $transaction->getReturnCode() );
            $order->update_meta_data( '_wc_rede_transaction_return_message', $transaction->getReturnMessage() );
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
            $order->update_meta_data( '_wc_rede_transaction_expiration', sprintf( '%02d/%d', $expiration[0], (int) ($expiration[1]) ) );

            $order->update_meta_data( '_wc_rede_transaction_holder', $transaction->getCardHolderName() );

            $authorization = $transaction->getAuthorization();

            if ( ! is_null( $authorization ) ) {
                $order->update_meta_data( '_wc_rede_transaction_authorization_status', $authorization->getStatus() );
            }

            $order->update_meta_data( '_wc_rede_transaction_environment', $this->environment );

            $this->process_order_status( $order, $transaction, '' );

            $order->save();

            if ( 'yes' == $this->debug ) {
                $this->log->log('info', $this->id, array(
                    'transaction' => $transaction,
                    'order' => array(
                        'orderId' => $orderId,
                        'amount' => $amount,
                        'status' => $order->get_status()
                    ),
                ));
            }
        } catch ( Exception $e ) {
            $this->add_error( $e->getMessage() );

            return array(
                'result' => 'fail',
                'redirect' => '',
            );
        }

        return array(
            'result' => 'success',
            'redirect' => $this->get_return_url( $order ),
        );
    }

    public function process_refund( $order_id, $amount = null, $reason = '' ) {
        $order = new WC_Order( $order_id );

        if ( ! $order || ! $order->get_transaction_id() ) {
            return false;
        }

        if ( empty( $order->get_meta( '_wc_rede_transaction_canceled' ) ) ) {
            $tid = $order->get_transaction_id();
            $amount = wc_format_decimal( $amount );

            try {
                $transaction = $this->api->do_transaction_cancellation( $tid, $amount );

                update_post_meta( $order_id, '_wc_rede_transaction_refund_id', $transaction->getRefundId() );
                update_post_meta( $order_id, '_wc_rede_transaction_cancel_id', $transaction->getCancelId() );
                update_post_meta( $order_id, '_wc_rede_transaction_canceled', true );

                $order->add_order_note( esc_attr__( 'Refunded:', 'integration-rede-for-woocommerce' ) . wc_price( $amount ) );
            } catch ( Exception $e ) {
                return new WP_Error( 'rede_refund_error', sanitize_text_field( $e->getMessage() ) );
            }

            return true;
        }

        return false;
    }

    protected function getCheckoutForm( $order_total = 0 ): void {
        $wc_get_template = 'woocommerce_get_template';

        if ( function_exists( 'wc_get_template' ) ) {
            $wc_get_template = 'wc_get_template';
        }

        $wc_get_template(
            'debitCard/redePaymentDebitForm.php',
            array(),
            'woocommerce/rede/',
            LknIntegrationRedeForWoocommerceWcRede::getTemplatesPath()
        );
    }
}
