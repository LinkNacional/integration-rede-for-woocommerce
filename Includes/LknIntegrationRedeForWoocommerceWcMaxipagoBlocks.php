<?php
namespace Lkn\IntegrationRedeForWoocommerce\Includes;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use Lkn\IntegrationRedeForWoocommerce\Includes\LknIntegrationRedeForWoocommerceWcMaxipagoCredit;

final class LknIntegrationRedeForWoocommerceWcMaxipagoBlocks extends AbstractPaymentMethodType {

    private $gateway;
    protected $name = 'maxipago_credit';

    public function initialize() {
        $this->settings = get_option( 'woocommerce_maxipago_credit_settings', [] );
        $this->gateway = new LknIntegrationRedeForWoocommerceWcMaxipagoCredit();
        
    }

    public function is_active() {
        return $this->gateway->is_available();
    }

    public function get_payment_method_script_handles() {

        wp_register_script(
            'maxipago_credit-blocks-integration',
            plugin_dir_url( __FILE__ ) . '../Public/js/lkn-integration-maxipago-for-woocommerce-checkout.js',
            [
                'wc-blocks-registry',
                'wc-settings',
                'wp-element',
                'wp-html-entities',
                'wp-i18n',
            ],
            null,
            true
        );
        if( function_exists( 'wp_set_script_translations' ) ) {            
            wp_set_script_translations( 'maxipago_credit-blocks-integration');
            
        }

        return [ 'maxipago_credit-blocks-integration' ];       
    }

    public function get_payment_method_data() {
        $nonce = wp_create_nonce( 'maxipagoCardNonce' );

        // Imprimindo o nonce como uma variável global
        echo '<script>window.maxipagoNonce = "' . esc_attr($nonce) . '";</script>';
        
		return [
			'title' => $this->gateway->title,
			'description' => $this->gateway->description,
		];
	}

}
?>