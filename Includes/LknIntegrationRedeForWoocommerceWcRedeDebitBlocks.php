<?php
namespace Lkn\IntegrationRedeForWoocommerce\Includes;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use Lkn\IntegrationRedeForWoocommerce\Includes\LknIntegrationRedeForWoocommerceWcRedeDebit;

final class LknIntegrationRedeForWoocommerceWcRedeDebitBlocks extends AbstractPaymentMethodType {

    private $gateway;
    protected $name = 'rede_debit';

    public function initialize() {
        $this->settings = get_option( 'woocommerce_rede_debit_settings', [] );
        $this->gateway = new LknIntegrationRedeForWoocommerceWcRedeDebit();
        
    }

    public function is_active() {
        return $this->gateway->is_available();
    }

    public function get_payment_method_script_handles() {

        wp_register_script(
            'rede_debit-blocks-integration',
            plugin_dir_url( __FILE__ ) . '../Public/js/debitCard/lknIntegrationRedeForWoocommerceCheckout.js',
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
            wp_set_script_translations( 'rede_debit-blocks-integration');
            
        }

        return [ 'rede_debit-blocks-integration' ];       
    }

    public function get_payment_method_data() {
        $nonce = wp_create_nonce( 'redeCardNonce' );

        // Imprimindo o nonce como uma variável global
        echo '<script>window.redeNonce = "' . esc_attr($nonce) . '";</script>';
        
		return [
			'title' => $this->gateway->title,
			'description' => $this->gateway->description,
		];
	}

}
?>