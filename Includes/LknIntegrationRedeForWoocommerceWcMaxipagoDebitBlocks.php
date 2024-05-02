<?php
namespace Lkn\IntegrationRedeForWoocommerce\Includes;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use Lkn\IntegrationRedeForWoocommerce\Includes\LknIntegrationRedeForWoocommerceWcMaxipagoDebit;

final class LknIntegrationRedeForWoocommerceWcMaxipagoDebitBlocks extends AbstractPaymentMethodType {

    private $gateway;
    protected $name = 'maxipago_debit';

    public function initialize() {
        $this->settings = get_option( 'woocommerce_maxipago_debit_settings', [] );
        $this->gateway = new LknIntegrationRedeForWoocommerceWcMaxipagoDebit();
        
    }

    public function is_active() {
        return $this->gateway->is_available();
    }

    public function get_payment_method_script_handles() {

        wp_register_script(
            'maxipago_debit-blocks-integration',
            plugin_dir_url( __FILE__ ) . '../Public/js/debitCard/lknIntegrationMaxipagoForWoocommerceCheckout.js',
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
            wp_set_script_translations( 'maxipago_debit-blocks-integration');
            
        }

        return [ 'maxipago_debit-blocks-integration' ];       
    }

    public function get_payment_method_data() {
        $nonce = wp_create_nonce( 'maxipagoDebitCardNonce' );

		return [
			'title' => $this->gateway->title,
			'description' => $this->gateway->description,
			'nonce' => $nonce,
		];
	}

}
?>