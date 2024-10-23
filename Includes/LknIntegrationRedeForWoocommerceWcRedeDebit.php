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
        $this->method_title = esc_attr__( 'Pay with the Rede Debit', 'woo-rede' );
        $this->method_description = esc_attr__( 'Enables and configures payments with Rede Debit', 'woo-rede' );
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

        if($this->get_option('enabled_soft_descriptor') === 'yes') {
            $this->soft_descriptor = preg_replace('/\W/', '', $this->get_option( 'soft_descriptor' ));
        } else if($this->get_option('enabled_soft_descriptor') === 'no') {
            add_option('lknIntegrationRedeForWoocommerceSoftDescriptorErrorDebit', false);
            update_option('lknIntegrationRedeForWoocommerceSoftDescriptorErrorDebit', false);
        }

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

    /**
     * Fields validation.
     *
     * @return bool
     */
    public function validate_fields() {
        if ( empty( $_POST['rede_debit_number'] ) ) {
            wc_add_notice( esc_attr__( 'Card number is a required field', 'woo-rede' ), 'error' );

            return false;
        }

        if ( empty( $_POST['rede_debit_expiry'] ) ) {
            wc_add_notice( esc_attr__( 'Card expiration is a required field', 'woo-rede' ), 'error' );

            return false;
        }

        if ( empty( $_POST['rede_debit_cvc'] ) ) {
            wc_add_notice( esc_attr__( 'Card security code is a required field', 'woo-rede' ), 'error' );

            return false;
        }

        if ( empty( $_POST['rede_debit_holder_name'] ) ) {
            wc_add_notice( esc_attr__( 'Cardholder name is a required field', 'woo-rede' ), 'error' );

            return false;
        }

        return true;
    }

    public function displayMeta( $order ): void {
        if ( $order->get_payment_method() === 'rede_debit' ) {
            $metaKeys = array(
                '_wc_rede_transaction_environment' => esc_attr__( 'Environment', 'woo-rede' ),
                '_wc_rede_transaction_return_code' => esc_attr__( 'Return Code', 'woo-rede' ),
                '_wc_rede_transaction_return_message' => esc_attr__( 'Return Message', 'woo-rede' ),
                '_wc_rede_transaction_id' => esc_attr__( 'Transaction ID', 'woo-rede' ),
                '_wc_rede_transaction_refund_id' => esc_attr__( 'Refund ID', 'woo-rede' ),
                '_wc_rede_transaction_cancel_id' => esc_attr__( 'Cancellation ID', 'woo-rede' ),
                '_wc_rede_transaction_nsu' => esc_attr__( 'Nsu', 'woo-rede' ),
                '_wc_rede_transaction_authorization_code' => esc_attr__( 'Authorization Code', 'woo-rede' ),
                '_wc_rede_transaction_bin' => esc_attr__( 'Bin', 'woo-rede' ),
                '_wc_rede_transaction_last4' => esc_attr__( 'Last 4', 'woo-rede' ),
                '_wc_rede_transaction_holder' => esc_attr__( 'Cardholder', 'woo-rede' ),
                '_wc_rede_transaction_expiration' => esc_attr__( 'Card Expiration', 'woo-rede' )
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
        LknIntegrationRedeForWoocommerceHelper::updateFixLoadScriptOption($this->id);

        $this->form_fields = array(
            'enabled' => array(
                'title' => esc_attr__( 'Enable/Disable', 'woo-rede' ),
                'type' => 'checkbox',
                'label' => esc_attr__( 'Enables payment with Rede', 'woo-rede' ),
                'default' => 'no',
            ),
            'title' => array(
                'title' => esc_attr__( 'Title', 'woo-rede' ),
                'type' => 'text',
                'default' => esc_attr__( 'Pay with the Rede Debit', 'woo-rede' ),
            ),

            'rede' => array(
                'title' => esc_attr__( 'General', 'woo-rede' ),
                'type' => 'title',
            ),
            'environment' => array(
                'title' => esc_attr__( 'Environment', 'woo-rede' ),
                'type' => 'select',
                'description' => esc_attr__( 'Choose the environment', 'woo-rede' ),
                'desc_tip' => true,
                'class' => 'wc-enhanced-select',
                'default' => esc_attr__( 'test', 'woo-rede' ),
                'options' => array(
                    'test' => esc_attr__( 'Tests', 'woo-rede' ),
                    'production' => esc_attr__( 'Production', 'woo-rede' ),
                ),
            ),
            'pv' => array(
                'title' => esc_attr__( 'PV', 'woo-rede' ),
                'type' => 'password',
                'description' => esc_attr__( 'Your Rede PV (affiliation number).', 'woo-rede' ),
                'desc_tip' => true,
                'custom_attributes' => array(
                    'required' => 'required'
                ),
                'default' => $options['pv'] ?? '',
            ),
            'token' => array(
                'title' => esc_attr__( 'Token', 'woo-rede' ),
                'type' => 'password',
                'description' => esc_attr__( 'Your Rede Token.', 'woo-rede' ),
                'desc_tip' => true,
                'custom_attributes' => array(
                    'required' => 'required'
                ),
                'default' => $options['token'] ?? '',
            ),

            'enabled_soft_descriptor' => array(
                'title' => __('Payment Description', 'woo-rede'),
                'type' => 'checkbox',
                'description' => __('Check this option to send the payment description in requests to Rede. If fatal errors occur due to the description, disable this option to ensure the correct processing of transactions.', 'woo-rede'),
                'desc_tip' => true,
                'label' => __('I have enabled the payment description feature in the', 'woo-rede') . ' ' . wp_kses_post('<a href="' . esc_url('https://meu.userede.com.br/ecommerce/identificacao-fatura') . '" target="_blank">' . __('Rede Dashboard', 'woo-rede') . '</a>') . '. ' . __('Default (Disabled)', 'woo-rede'),
                'default' => 'no',
            ),

            'soft_descriptor' => array(
                'title' => esc_attr__( 'Payment Description', 'woo-rede' ),
                'type' => 'text',
                'description' => esc_attr__( 'Set the description to be sent to Rede along with the payment transaction.', 'woo-rede' ),
                'desc_tip' => true,
                'custom_attributes' => array(
                    'maxlength' => 20,
                ),
            ),

            'enabled_fix_load_script' => array(
                'title' => __('Load on checkout', 'woo-rede'),
                'type' => 'checkbox',
                'description' => __('By disabling this feature, the plugin will be loaded during the checkout process. This feature, when enabled, prevents infinite loading errors on the checkout page. Only disable it if you are experiencing difficulties with the gateway loading.', 'woo-rede'),
                'desc_tip' => true,
                'label' => __('Load plugin on checkout. Default (enabled)', 'woo-rede'),
                'default' => 'yes',
            ),

            //TODO Remover em issue futura
            /* 'partners' => array(
                'title' => esc_attr__( 'Partner Settings', 'woo-rede' ),
                'type' => 'title',
            ),
            'module' => array(
                'title' => esc_attr__( 'Module ID', 'woo-rede' ),
                'type' => 'text',
                'default' => '',
            ),
            'gateway' => array(
                'title' => esc_attr__( 'Gateway ID', 'woo-rede' ),
                'type' => 'text',
                'default' => '',
            ), */

            'developers' => array(
                'title' => esc_attr__( 'Developer', 'woo-rede' ),
                'type' => 'title',
            ),

            'debug' => array(
                'title' => esc_attr__( 'Debug', 'woo-rede' ),
                'type' => 'checkbox',
                'label' => esc_attr__( 'Enable debug logs.' . ' ', 'woo-rede' ) . wp_kses_post( '<a href="' . esc_url( admin_url( 'admin.php?page=wc-status&tab=logs' ) ) . '" target="_blank">' . __('See logs', 'woo-rede') . '</a>'),
                'default' => 'no',
                'description' => esc_attr__( 'Enable transaction logging.', 'woo-rede' ),
                'desc_tip' => true,
            )
        );

        $customConfigs = apply_filters('integrationRedeGetCustomConfigs', $this->form_fields, array(), $this->id);

        if ( ! empty($customConfigs)) {
            $this->form_fields = array_merge($this->form_fields, $customConfigs);
        }
    }

    public function checkoutScripts(): void {
        $plugin_url = plugin_dir_url( LknIntegrationRedeForWoocommerceWcRede::FILE ) . '../';
        if ($this->get_option('enabled_fix_load_script') === 'yes') {
            wp_enqueue_script( 'fixInfiniteLoading-js', $plugin_url . 'Public/js/fixInfiniteLoading.js', array(), '1.0.0', true );
        }

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
            if ( false === $valid ) {
                throw new Exception( __( 'Please enter a valid debit card number', 'woo-rede' ) );
            }

            $valid = $this->validate_card_fields( $_POST );
            if ( false === $valid ) {
                throw new Exception(__('One or more invalid fields', 'woo-rede'), 500);
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
            if($e->getCode() == 63){
                add_option('lknIntegrationRedeForWoocommerceSoftDescriptorErrorDebit', true);
                update_option('lknIntegrationRedeForWoocommerceSoftDescriptorErrorDebit', true);
            }

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

                $order->add_order_note( esc_attr__( 'Refunded:', 'woo-rede' ) . wc_price( $amount ) );
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
