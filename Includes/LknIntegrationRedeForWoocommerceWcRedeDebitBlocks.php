<?php
namespace Lkn\IntegrationRedeForWoocommerce\Includes;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use Lkn\IntegrationRedeForWoocommerce\Includes\LknIntegrationRedeForWoocommerceWcRedeDebit;

final class LknIntegrationRedeForWoocommerceWcRedeDebitBlocks extends AbstractPaymentMethodType {
    private $gateway;
    protected $name = 'rede_debit';

    public function initialize(): void {
        $this->settings = get_option( 'woocommerce_rede_debit_settings', array() );
        $this->gateway = new LknIntegrationRedeForWoocommerceWcRedeDebit();
    }

    public function is_active() {
        return $this->gateway->is_available();
    }

    public function get_payment_method_script_handles() {
        wp_register_script(
            'rede_debit-blocks-integration',
            plugin_dir_url( __FILE__ ) . '../Public/js/debitCard/rede/lknIntegrationRedeForWoocommerceCheckout.js',
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
            wp_set_script_translations( 'rede_debit-blocks-integration');
        }

        apply_filters('integrationRedeSetCustomCSSPro', get_option('woocommerce_rede_debit_settings')['custom_css_block_editor'] ?? false);
        return array('rede_debit-blocks-integration');
    }

    public function get_payment_method_data() {
        $cart_total = LknIntegrationRedeForWoocommerceHelper::getCartTotal();

        return array(
            'title' => $this->gateway->title,
            'description' => $this->gateway->description,
            'nonceRedeDebit' => wp_create_nonce( 'redeCardNonce' ),
            'cartTotal' => $cart_total,
            'translations' => array(
                'fieldsNotFilled' => __('Please fill in all fields correctly.', 'woo-rede'),
                'cardNumber' => __('Card Number', 'woo-rede'),
                'cardExpiringDate' => __( 'Card Expiring Date', 'woo-rede' ),
                'securityCode' => __('Security Code', 'woo-rede' ),
                'nameOnCard' => __( 'Name on Card', 'woo-rede' ),
            )
        );
    }
}
?>