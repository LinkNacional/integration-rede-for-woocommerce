<?php

namespace Lknwoo\IntegrationRedeForWoocommerce\Includes;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class LknIntegrationRedeForWoocommerceWcPixRedeBlocks extends AbstractPaymentMethodType
{
    private $gateway;
    protected $name = 'integration_rede_pix';

    public function initialize(): void
    {
        $this->settings = get_option('woocommerce_integration_rede_pix_settings', array());
        $this->gateway = new LknIntegrationRedeForWoocommerceWcPixRede();
    }

    public function is_active()
    {
        return $this->gateway->is_available();
    }

    public function get_payment_method_script_handles()
    {
        wp_enqueue_style('integration-rede-pix-style-blocks', INTEGRATION_REDE_FOR_WOOCOMMERCE_DIR_URL . 'Public/css/rede/LknIntegrationRedeForWoocommercePaymentFields.css', array(), '1.0.0', 'all');
        wp_register_script(
            'rede-pix-blocks-integration',
            plugin_dir_url(__FILE__) . '../Public/js/pix/LknIntegrationRedeForWoocommercePixRede.js',
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
        if (function_exists('wp_set_script_translations')) {
            wp_set_script_translations('rede-pix-blocks-integration');
        }

        return array('rede-pix-blocks-integration');
    }

    public function get_payment_method_data()
    {
        $option = get_option('woocommerce_integration_rede_pix_settings');

        return array(
            'title' => $this->gateway->title,
            'description' => $option['description'] ?? __('Pay for your purchase with a pix through ', 'woo-rede'),
        );
    }
}
