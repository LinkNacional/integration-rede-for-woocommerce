<?php
namespace Lkn\IntegrationRedeForWoocommerce\Includes;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use Lkn\IntegrationRedeForWoocommerce\Includes\LknIntegrationRedeForWoocommerceWcRedeCredit;

final class LknIntegrationRedeForWoocommerceWcRedeBlocks extends AbstractPaymentMethodType {

    private $gateway;
    protected $name = 'rede_credit';

    public function initialize() {
        $this->settings = get_option( 'woocommerce_rede_credit_settings', [] );
        $this->gateway = new LknIntegrationRedeForWoocommerceWcRedeCredit();
    }

    public function is_active() {
        return $this->gateway->is_available();
    }

    public function get_payment_method_script_handles() {

        wp_register_script(
            'rede_credit-blocks-integration',
            plugin_dir_url( __FILE__ ) . '../Public/js/lkn-integration-rede-for-woocommerce-checkout.js',
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
            wp_set_script_translations( 'rede_credit-blocks-integration');
            
        }
        return [ 'rede_credit-blocks-integration' ];
    }

    public function get_payment_method_data() {
		return [
			'title' => $this->gateway->title,
			'description' => $this->gateway->description,
		];
	}

}
?>