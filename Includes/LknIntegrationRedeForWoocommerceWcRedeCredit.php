<?php
namespace Lkn\IntegrationRedeForWoocommerce\Includes;

use Exception;
use Lkn\IntegrationRedeForWoocommerce\Includes\LknIntegrationRedeForWoocommerceWcRedeAbstract;
use Symfony\Component\Console\Event\ConsoleEvent;
use WC_Order;
use WP_Error;

final class LknIntegrationRedeForWoocommerceWcRedeCredit extends LknIntegrationRedeForWoocommerceWcRedeAbstract {
    public $api = null;

    public function __construct() {
        $this->id = 'rede_credit';
        $this->has_fields = true;
        $this->method_title = esc_attr__( 'Pay with the Rede Credit', 'woo-rede' );
        $this->method_description = esc_attr__( 'Enables and configures payments with Rede Credit', 'woo-rede' );
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
            add_option('lknIntegrationRedeForWoocommerceSoftDescriptorErrorCredit', false);
            update_option('lknIntegrationRedeForWoocommerceSoftDescriptorErrorCredit', false);
        }

        $this->auto_capture = sanitize_text_field($this->get_option('auto_capture')) == 'no' ? false : true;
        $this->max_parcels_number = $this->get_option( 'max_parcels_number' );
        $this->min_parcels_value = $this->get_option( 'min_parcels_value' );

        $this->partner_module = $this->get_option( 'module' );
        $this->partner_gateway = $this->get_option( 'gateway' );

        $this->debug = $this->get_option( 'debug' );

        $this->log = $this->get_logger();

        $this->configs = $this->getConfigsRedeCredit();

        $this->api = new LknIntegrationRedeForWoocommerceWcRedeAPI( $this );
    }

    /**
     * Fields validation.
     *
     * @return bool
     */
    public function validate_fields() {
        if ( empty( $_POST['rede_credit_number'] ) ) {
            wc_add_notice( esc_attr__( 'Card number is a required field', 'woo-rede' ), 'error' );

            return false;
        }

        if ( empty( $_POST['rede_credit_expiry'] ) ) {
            wc_add_notice( esc_attr__( 'Card expiration is a required field', 'woo-rede' ), 'error' );

            return false;
        }

        if ( empty( $_POST['rede_credit_cvc'] ) ) {
            wc_add_notice( esc_attr__( 'Card security code is a required field', 'woo-rede' ), 'error' );

            return false;
        }

        if ( empty( $_POST['rede_credit_holder_name'] ) ) {
            wc_add_notice( esc_attr__( 'Cardholder name is a required field', 'woo-rede' ), 'error' );

            return false;
        }

        return true;
    }

    public function getConfigsRedeCredit() {
        $configs = array();

        $configs['basePath'] = INTEGRATION_REDE_FOR_WOOCOMMERCE_DIR . 'Includes/logs/';
        $configs['base'] = $configs['basePath'] . gmdate('d.m.Y-H.i.s') . '.RedeCredit.log';
        $configs['debug'] = $this->get_option('debug');

        return $configs;
    }

    public function displayMeta( $order ): void {
        if ( $order->get_payment_method() === 'rede_credit' ) {
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
                '_wc_rede_transaction_brand' => esc_attr__( 'Brand', 'woo-rede' ),
                '_wc_rede_transaction_installments' => esc_attr__( 'Installments', 'woo-rede' ),
                '_wc_rede_transaction_holder' => esc_attr__( 'Cardholder', 'woo-rede' ),
                '_wc_rede_transaction_expiration' => esc_attr__( 'Card Expiration', 'woo-rede' )
            );

            $this->generateMetaTable( $order, $metaKeys, 'Rede');
        }
    }

    public function initFormFields(): void {
        $options = get_option('woocommerce_rede_credit', array());
        LknIntegrationRedeForWoocommerceHelper::updateFixLoadScriptOption($this->id);
        
        $this->form_fields = array(
            'enabled' => array(
                'title' => esc_attr__( 'Enable/Disable', 'woo-rede' ),
                'type' => 'checkbox',
                'label' => esc_attr__( 'Enables payment with Rede', 'woo-rede' ),
                'default' => $options['enabled'] ?? 'no',
            ),
            'title' => array(
                'title' => esc_attr__( 'Title', 'woo-rede' ),
                'type' => 'text',
                'default' => esc_attr__( 'Pay with the Rede Credit', 'woo-rede' ),
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

            'credit_options' => array(
                'title' => esc_attr__( 'Credit Card', 'woo-rede' ),
                'type' => 'title',
            ),
            'min_parcels_value' => array(
                'title' => esc_attr__( 'Value of the smallest installment', 'woo-rede' ),
                'type' => 'text',
                'default' => '0',
                'description' => esc_attr__( 'Set the minimum allowed amount for each installment in credit transactions.', 'woo-rede' ),
                'desc_tip' => true,
            ),
            'max_parcels_number' => array(
                'title' => esc_attr__( 'Max installments', 'woo-rede' ),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => '12',
                'options' => array(
                    '1' => '1x',
                    '2' => '2x',
                    '3' => '3x',
                    '4' => '4x',
                    '5' => '5x',
                    '6' => '6x',
                    '7' => '7x',
                    '8' => '8x',
                    '9' => '9x',
                    '10' => '10x',
                    '11' => '11x',
                    '12' => '12x',
                ),
                'description' => esc_attr__( 'Set the maximum number of installments allowed in credit transactions.', 'woo-rede' ),
                'desc_tip' => true,
            ),

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

        $customConfigs = apply_filters('integrationRedeGetCustomConfigs', $this->form_fields, array(
            'installment_interest' => $this->get_option('installment_interest'),
            'max_parcels_number' => $this->get_option('max_parcels_number'),
        ), $this->id);

        if ( ! empty($customConfigs)) {
            $this->form_fields = array_merge($this->form_fields, $customConfigs);
        }
    }

    public function get_installment_text( $quantity, $order_total ) {
        $installments = $this->getInstallments( $order_total );

        if ( isset( $installments[ $quantity - 1 ] ) ) {
            return $installments[ $quantity - 1 ]['label'];
        }

        if ( isset( $installments[ $quantity ] ) ) {
            return $installments[ $quantity ]['label'];
        }

        return $quantity;
    }

    public function getInstallments( $order_total = 0 ) {
        $installments = array();
        $customLabel = null;
        $defaults = array(
            'min_value' => str_replace(',', '.', $this->min_parcels_value),
            'max_parcels' => $this->max_parcels_number,
        );

        $installments_result = wp_parse_args( apply_filters( 'integration_rede_installments', $defaults ), $defaults );

        $min_value = (float) $installments_result['min_value'];
        $max_parcels = (int) $installments_result['max_parcels'];

        for ( $i = 1; $i <= $max_parcels; ++$i ) {
            if ( ( $order_total / $i ) < $min_value ) {
                break;
            }

            $interest = round((float) $this->get_option( $i . 'x' ), 2);
            $label = sprintf( '%dx de %s', $i, wp_strip_all_tags( wc_price( $order_total / $i ) ) );

            if ($this->get_option('installment_interest') == 'yes') {
                $customLabel = apply_filters('integrationRedeGetInterest', $order_total, $interest, $i, 'label', $this);
            }

            if (gettype($customLabel) === 'string' && $customLabel) {
                $label = $customLabel;
            }

            $installments[] = array(
                'num' => $i,
                'label' => $label,
            );
        }

        return $installments;
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
        wp_enqueue_style( 'woo-rede-style', $plugin_url . 'Public/css/rede/styleRedeCredit.css', array(), '1.0.0', 'all' );

        wp_enqueue_script( 'woo-rede-js', $plugin_url . 'Public/js/creditCard/rede/wooRedeCredit.js', array(), '1.0.0', true );
        wp_enqueue_script( 'woo-rede-animated-card-jquery', $plugin_url . 'Public/js/jquery.card.js', array('jquery', 'woo-rede-js'), '2.5.0', true );

        wp_localize_script( 'woo-rede-js', 'wooRede', array(
            'debug' => defined( 'WP_DEBUG' ) && WP_DEBUG,
        ));

        apply_filters('integrationRedeSetCustomCSSPro', get_option('woocommerce_rede_credit_settings')['custom_css_short_code'] ?? false);
    }

    public function process_payment( $order_id ) {
        if ( ! wp_verify_nonce($_POST['rede_card_nonce'], 'redeCardNonce')) {
            return array(
                'result' => 'fail',
                'redirect' => '',
            );
        }

        $order = wc_get_order( $order_id );
        $cardNumber = isset( $_POST['rede_credit_number'] ) ?
            sanitize_text_field( $_POST['rede_credit_number'] ) : '';

        $installments = isset( $_POST['rede_credit_installments'] ) ?
            absint( sanitize_text_field($_POST['rede_credit_installments']) ) : 1;

        $creditExpiry = sanitize_text_field($_POST['rede_credit_expiry']);

        if (strpos($creditExpiry, '/') !== false) {
            $expiration = explode( '/', $creditExpiry );
        } else {
            $expiration = array(
                substr($creditExpiry, 0, 2),
                substr($creditExpiry, -2, 2),
            );
        }

        $cardData = array(
            'card_number' => preg_replace( '/[^\d]/', '', sanitize_text_field( $_POST['rede_credit_number'] ) ),
            'card_expiration_month' => sanitize_text_field( $expiration[0] ),
            'card_expiration_year' => $this->normalize_expiration_year( sanitize_text_field( $expiration[1] ) ),
            'card_cvv' => sanitize_text_field( $_POST['rede_credit_cvc'] ),
            'card_holder' => sanitize_text_field( $_POST['rede_credit_holder_name'] ),
        );
        try {
            $valid = $this->validate_card_number( $cardNumber );
            if ( false === $valid ) {
                throw new Exception( __( 'Please enter a valid credit card number', 'woo-rede' ) );
            }

            $valid = $this->validate_card_fields( $_POST );
            if ( false === $valid ) {
                throw new Exception(__('One or more invalid fields', 'woo-rede'), 500);
            }

            $valid = $this->validate_installments( $_POST, $order->get_total() );
            if ( false === $valid ) {
                throw new Exception( __( 'Invalid number of installments', 'woo-rede' ) );
            }

            $orderId = $order->get_id();
            $interest = round((float) $this->get_option( $installments . 'x' ), 2);
            $order_total = $order->get_total();
            if ($this->get_option('installment_interest') == 'yes') {
                $order_total = apply_filters('integrationRedeGetInterest', $order_total, $interest, '', 'total');
            }

            $transaction = $this->api->doTransactionCreditRequest( $orderId + time(), $order_total, $installments, $cardData );
            $order->update_meta_data( '_transaction_id', $transaction->getTid() );
            $order->update_meta_data( '_wc_rede_transaction_return_code', $transaction->getReturnCode() );
            $order->update_meta_data( '_wc_rede_transaction_return_message', $transaction->getReturnMessage() );
            $order->update_meta_data( '_wc_rede_transaction_installments', $installments );
            $order->update_meta_data( '_wc_rede_transaction_id', $transaction->getTid() );
            $order->update_meta_data( '_wc_rede_transaction_refund_id', $transaction->getRefundId() );
            $order->update_meta_data( '_wc_rede_transaction_cancel_id', $transaction->getCancelId() );
            $order->update_meta_data( '_wc_rede_transaction_bin', $transaction->getCardBin() );
            $order->update_meta_data( '_wc_rede_transaction_last4', $transaction->getLast4() );
            $order->update_meta_data( '_wc_rede_transaction_brand', LknIntegrationRedeForWoocommerceHelper::getCardBrand($transaction->getTid(), $this));
            $order->update_meta_data( '_wc_rede_transaction_nsu', $transaction->getNsu() );
            $order->update_meta_data( '_wc_rede_transaction_authorization_code', $transaction->getAuthorizationCode() );
            $order->update_meta_data( '_wc_rede_captured', $transaction->getCapture() );
            $order->update_meta_data( '_wc_rede_total_amount', $order_total );

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
                        'amount' => $order_total,
                        'status' => $order->get_status()
                    ),
                ));
            }
        } catch ( Exception $e ) {
            
            if($e->getCode() == 63){
                add_option('lknIntegrationRedeForWoocommerceSoftDescriptorErrorCredit', true);
                update_option('lknIntegrationRedeForWoocommerceSoftDescriptorErrorCredit', true);
            }

            $this->add_error( $e->getMessage());

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
        $totalAmount = $order->get_meta('_wc_rede_total_amount');

        if ( ! $order || ! $order->get_transaction_id() ) {
            return false;
        }

        if ( empty( $order->get_meta( '_wc_rede_transaction_canceled' ) ) ) {
            $tid = $order->get_transaction_id();
            $amount = wc_format_decimal( $amount );

            try {
                if ($order->get_total() == $amount) {
                    $transaction = $this->api->do_transaction_cancellation( $tid, $totalAmount );
                } else {
                    $transaction = $this->api->do_transaction_cancellation( $tid, $amount );
                }

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
            'creditCard/redePaymentCreditForm.php',
            array(
                'installments' => $this->getInstallments( $order_total ),
            ),
            'woocommerce/rede/',
            LknIntegrationRedeForWoocommerceWcRede::getTemplatesPath()
        );
    }
}
