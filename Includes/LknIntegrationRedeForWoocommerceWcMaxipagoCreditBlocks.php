<?php
namespace Lkn\IntegrationRedeForWoocommerce\Includes;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use Lkn\IntegrationRedeForWoocommerce\Includes\LknIntegrationRedeForWoocommerceWcMaxipagoCredit;

final class LknIntegrationRedeForWoocommerceWcMaxipagoCreditBlocks extends AbstractPaymentMethodType {
    private $gateway;
    protected $name = 'maxipago_credit';

    public function initialize(): void {
        $this->settings = get_option( 'woocommerce_maxipago_credit_settings', array() );
        $this->gateway = new LknIntegrationRedeForWoocommerceWcMaxipagoCredit();
    }

    public function is_active() {
        return $this->gateway->is_available();
    }

    public function get_payment_method_script_handles() {
        wp_register_script(
            'maxipago_credit-blocks-integration',
            plugin_dir_url( __FILE__ ) . '../Public/js/creditCard/maxipago/lknIntegrationMaxipagoForWoocommerceCheckout.js',
            array(
                'wc-blocks-registry',
                'wc-settings',
                'wp-element',
                'wp-html-entities',
                'wp-i18n',
            ),
            '1.0.0',
            true
        );
        if ( function_exists( 'wp_set_script_translations' ) ) {
            wp_set_script_translations( 'maxipago_credit-blocks-integration');
        }

        return array('maxipago_credit-blocks-integration');
    }

    public function get_payment_method_data() {
        $cart_total = LknIntegrationRedeForWoocommerceHelper::getCartTotal();
        
        return array(
            'title' => $this->gateway->title,
            'description' => $this->gateway->description,
            'nonceMaxipagoCredit' => wp_create_nonce( 'maxipagoCardNonce' ),
            'minInstallmentsMaxipago' => get_option('woocommerce_maxipago_credit_settings')['min_parcels_value'],
            'maxInstallmentsMaxipago' => get_option('woocommerce_maxipago_credit_settings')['max_parcels_number'],
            'cartTotal' => $cart_total,
            'translations' => array(
                'fieldsNotFilled' => __('Please fill in all fields correctly.', 'integration-rede-for-woocommerce'),
                'cardNumber' => __('Card Number', 'integration-rede-for-woocommerce'),
                'cardExpiringDate' => __( 'Card Expiring Date', 'integration-maxipago-for-woocommerce' ),
                'securityCode' => __('Security Code', 'integration-maxipago-for-woocommerce' ),
                'nameOnCard' => __( 'Name on Card', 'integration-maxipago-for-woocommerce' ),
                'installments' => __( 'Installments', 'integration-rede-for-woocommerce' ),
                'district' => __('District', 'integration-rede-for-woocommerce'),
            )
        );
    }
}
?>