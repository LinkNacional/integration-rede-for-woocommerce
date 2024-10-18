<?php
namespace Lkn\IntegrationRedeForWoocommerce\Includes;

use WC_Order;
use WP_Query;

final class LknIntegrationRedeForWoocommerceWcRede {
    public const FILE = __FILE__;

    public const VERSION = '3.1.2';
    protected static $instance = null;

    public function __construct() {
        $this->upgrade();
    }

    public function getInstance() {
        if ( null == self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function getTemplatesPath() {
        return plugin_dir_path( __FILE__ ) . 'templates/';
    }

    public function loadPluginTextdomain(): void {
        load_plugin_textdomain( 'woo-rede', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    public function addGateway( $methods ) {
        $wc_rede_credit_class = new LknIntegrationRedeForWoocommerceWcRedeCredit();
        $wc_rede_debit_class = new LknIntegrationRedeForWoocommerceWcRedeDebit();
        $wc_maxipago_credit_class = new LknIntegrationRedeForWoocommerceWcMaxipagoCredit();
        $wc_maxipago_debit_class = new LknIntegrationRedeForWoocommerceWcMaxipagoDebit();

        array_push( $methods, $wc_rede_credit_class);
        array_push( $methods, $wc_rede_debit_class);
        array_push( $methods, $wc_maxipago_credit_class);
        array_push( $methods, $wc_maxipago_debit_class);

        return $methods;
    }

    private function upgrade(): void {
        if ( is_admin() ) {
            $version = get_option( 'wc_rede_version', '0' );

            if ( version_compare( $version, self::VERSION, '<' ) ) {
                if ( $options = get_option( 'woocommerce_rede_settings' ) ) {
                    $credit_options = array(
                        'enabled' => $options['enabled'],
                        'title' => 'Ativar',

                        'environment' => $options['environment'],
                        'token' => $options['token'],
                        'pv' => $options['pv'],

                        'soft_descriptor' => $options['soft_descriptor'],

                        'min_parcels_value' => $options['smallest_installment'],
                        'max_parcels_number' => $options['installments'],
                    );

                    update_option( 'woocommerce_rede_credit_settings', $credit_options );

                    delete_option( 'woocommerce_rede_settings' );
                }

                update_option( 'wc_rede_version', self::VERSION );
            }
        }
    }

    public function woocommerceMissingNotice(): void {
        deactivate_plugins( plugin_basename( INTEGRATION_REDE_FOR_WOOCOMMERCE_FILE ) );
        include_once __DIR__ . '/views/notices/html-notice-woocommerce-missing.php';
    }

    public function softDescriptorNotice(): void {
        $errorCredit = get_option('lknIntegrationRedeForWoocommerceSoftDescriptorErrorCredit');      
        $errorDebit = get_option('lknIntegrationRedeForWoocommerceSoftDescriptorErrorDebit');      
        if($errorCredit == true || $errorDebit == true) {
            $message = '
                <div class="notice notice-error" id="lknIntegrationRedeForWoocommerceSoftDescriptorErrorDiv">
                    <p>' 
                        . __('There was an error in the transaction, disable the', 'woo-rede') . ' ' . 
                        wp_kses_post('<a href="' . esc_url(admin_url('admin.php?page=wc-settings&tab=checkout')) 
                        . '" target="_blank">' . __('Payment Description.', 'woo-rede') . '</a>') . 
                    '</p>
                </div>
            ';
    
            echo wp_kses_post($message);
        }
    }

    public function updateRedeOrders(): void {
        $orders = new WP_Query(
            array(
                'post_type' => 'shop_order',
                'post_status' => array('wc-on-hold', 'wc-processing'),
            )
        );

        foreach ( $orders->posts as $order ) {
            $wc_order = new WC_Order( $order->ID );
            $wc_id = $wc_order->get_id();
            $payment_gateway = wc_get_payment_gateway_by_order( $wc_order );
            $order_id = get_post_meta( $wc_id, '_wc_rede_order_id', true );
            $status = get_post_meta( $wc_id, '_wc_rede_status', true );
            $tid = $tid = get_post_meta( $wc_id, '_wc_rede_transaction_id', true );

            if ( $payment_gateway instanceof LknIntegrationRedeForWoocommerceWcRedeAbstract ) {
                if ( 'PENDING' == $status || 'SUBMITTED' == $status ) {
                    $payment_gateway->consult_order( $wc_order, $order_id, $tid, $status );
                }
            }
        }
    }
}
