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
        wp_enqueue_style( 'select-style', plugin_dir_url(INTEGRATION_REDE_FOR_WOOCOMMERCE_FILE) . '/Public/css/lknIntegrationRedeForWoocommerceSelectStyle.css', array(), '1.0.0', 'all' );
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

        apply_filters('integrationRedeSetCustomCSSPro', get_option('woocommerce_maxipago_credit_settings')['custom_css_block_editor'] ?? false);

        return array('maxipago_credit-blocks-integration');
    }

    public function get_payment_method_data() {
        $cart_total = LknIntegrationRedeForWoocommerceHelper::getCartTotal();
        $maxParcels = get_option('woocommerce_maxipago_credit_settings')['max_parcels_number'];
        $phpArray = array(
            'title' => $this->gateway->title,
            'description' => $this->gateway->description,
            'nonceMaxipagoCredit' => wp_create_nonce( 'maxipagoCardNonce' ),
            'minInstallmentsMaxipago' => get_option('woocommerce_maxipago_credit_settings')['min_parcels_value'],
            'maxInstallmentsMaxipago' => $maxParcels,
            'cartTotal' => $cart_total,
            'translations' => array(
                'fieldsNotFilled' => __('Please fill in all fields correctly.', 'woo-rede'),
                'cardNumber' => __('Card Number', 'woo-rede'),
                'cardExpiringDate' => __( 'Card Expiring Date', 'woo-rede' ),
                'securityCode' => __('Security Code', 'woo-rede' ),
                'nameOnCard' => __( 'Name on Card', 'woo-rede' ),
                'installments' => __( 'Installments', 'woo-rede' ),
                'district' => __('District', 'woo-rede'),
                'interestFree' => ' ' . __('interest-free', 'woo-rede'),
            )
        );
        if (isset(get_option('woocommerce_maxipago_credit_settings')['installment_interest']) &&
            get_option('woocommerce_maxipago_credit_settings')['installment_interest'] == 'yes') {
            for ($i = 1; $i <= $maxParcels; ++$i) {
                $interest = round((float) get_option('woocommerce_maxipago_credit_settings')[$i . 'x'], 2);
                $phpArray[$i . 'x'] = apply_filters('integrationRedeGetInterest', $cart_total, $interest, $i, 'label', $this->gateway);
            }
        }else {
            for ($i = 1; $i <= $maxParcels; ++$i) {
                $phpArray[$i . 'x'] = html_entity_decode(sprintf('%dx de %s', $i, wp_strip_all_tags(wc_price($cart_total / $i))));
            }
        }
        return $phpArray;
    }
}
?>