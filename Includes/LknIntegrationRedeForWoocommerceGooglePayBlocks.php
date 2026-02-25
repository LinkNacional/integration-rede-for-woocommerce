<?php

namespace Lknwoo\IntegrationRedeForWoocommerce\Includes;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use Lknwoo\IntegrationRedeForWoocommerce\Includes\LknIntegrationRedeForWoocommerceGooglePay;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class LknIntegrationRedeForWoocommerceGooglePayBlocks extends AbstractPaymentMethodType {
    
    protected $name = 'rede_google_pay';
    
    /**
     * Gateway instance.
     *
     * @var LknIntegrationRedeForWoocommerceGooglePay
     */
    protected $gateway;

    public function initialize() {
        $this->settings = get_option( 'woocommerce_rede_google_pay_settings', array() );
        $this->gateway = new LknIntegrationRedeForWoocommerceGooglePay();
    }

    public function is_active() {
        return $this->gateway->is_available();
    }

    public function get_payment_method_script_handles() {
        wp_enqueue_style( 'woo-rede-google-pay-style-blocks', plugin_dir_url(__FILE__) . '../Public/css/rede/LknIntegrationRedeForWoocommerceGooglePay.css', array(), INTEGRATION_REDE_FOR_WOOCOMMERCE_VERSION, 'all' );
        
        // Registrar primeiro a API do Google Pay
        wp_register_script(
            'google-pay-api',
            'https://pay.google.com/gp/p/js/pay.js',
            array(),
            null,
            true
        );
        
        wp_register_script(
            'rede_google_pay-blocks-integration',
            plugin_dir_url( __FILE__ ) . '../Public/js/googlePay/LknIntegrationRedeForWoocommerceGooglePay.js',
            array(
                'wc-blocks-registry',
                'wc-settings',
                'wp-element',
                'wp-html-entities',
                'wp-i18n',
                'google-pay-api'
            ),
            INTEGRATION_REDE_FOR_WOOCOMMERCE_VERSION,
            true
        );
        
        if( function_exists( 'wp_set_script_translations' ) ) {            
            wp_set_script_translations( 'rede_google_pay-blocks-integration');
        }

        return array('rede_google_pay-blocks-integration');
    }

    public function get_payment_method_data() {
        $option = get_option('woocommerce_rede_google_pay_settings');

        return array(
            'title' => $option['title'] ?? __('Pay with Google Pay', 'woo-rede'),
            'description' => $option['description'] ?? __('Pay securely with Google Pay', 'woo-rede'),
            'environment' => $option['environment'] ?? 'test',
            'merchant_name' => get_bloginfo('name'),
            'merchant_id' => $option['pv'] ?? 'rede_merchant',
            'nonce' => wp_create_nonce('wc_store_api'),
            'supports' => array('products'),
            'google_merchant_id' => $option['google_merchant_id'] ?? 'google_merchant',
            'google_pay_public_key'    => $option['google_pay_public_key'] ?? '',
            'google_text_button' => $option['google_text_button'] ?? 'pay',
        );
    }
}