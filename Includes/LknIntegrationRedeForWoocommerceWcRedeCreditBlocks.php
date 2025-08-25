<?php

namespace Lkn\IntegrationRedeForWoocommerce\Includes;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use Lkn\IntegrationRedeForWoocommerce\Includes\LknIntegrationRedeForWoocommerceWcRedeCredit;

final class LknIntegrationRedeForWoocommerceWcRedeCreditBlocks extends AbstractPaymentMethodType
{
    private $gateway;
    protected $name = 'rede_credit';

    public function initialize(): void
    {
        $this->settings = get_option('woocommerce_rede_credit_settings', array());
        $this->gateway = new LknIntegrationRedeForWoocommerceWcRedeCredit();
    }

    public function is_active()
    {
        return $this->gateway->is_available();
    }

    public function get_payment_method_script_handles()
    {
        wp_enqueue_style('select-style', plugin_dir_url(INTEGRATION_REDE_FOR_WOOCOMMERCE_FILE) . '/Public/css/lknIntegrationRedeForWoocommerceSelectStyle.css', array(), '1.0.0', 'all');
        wp_register_script(
            'rede_credit-blocks-integration',
            plugin_dir_url(__FILE__) . '../Public/js/creditCard/rede/lknIntegrationRedeForWoocommerceCheckoutCompiled.js',
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
            wp_set_script_translations('rede_credit-blocks-integration');
        }
        apply_filters('integrationRedeSetCustomCSSPro', get_option('woocommerce_rede_credit_settings')['custom_css_block_editor'] ?? false);

        return array('rede_credit-blocks-integration');
    }

    public function get_payment_method_data()
    {
        $cart_total = LknIntegrationRedeForWoocommerceHelper::getCartTotal();
        $settings = get_option('woocommerce_rede_credit_settings');
        $maxParcels = $settings['max_parcels_number'];
        $minParcelValue = (float) $settings['min_parcels_value'];

        $phpArray = array(
            'title' => $this->gateway->title,
            'description' => $this->gateway->description,
            'nonceRedeCredit' => wp_create_nonce('redeCardNonce'),
            'minInstallmentsRede' => get_option('woocommerce_rede_credit_settings')['min_parcels_value'],
            'maxInstallmentsRede' => $maxParcels,
            'cartTotal' => $cart_total,
            'translations' => array(
                'fieldsNotFilled' => __('Please fill in all fields correctly.', 'woo-rede'),
                'cardNumber' => __('Card Number', 'woo-rede'),
                'cardExpiringDate' => __('Card Expiring Date', 'woo-rede'),
                'securityCode' => __('Security Code', 'woo-rede'),
                'nameOnCard' => __('Name on Card', 'woo-rede'),
                'installments' => __('Installments', 'woo-rede'),
                'interestFree' => ' ' . __('interest-free', 'woo-rede'),
            )
        );

        if (
            isset($settings['installment_interest']) &&
            ($settings['installment_interest'] === 'yes' || $settings['installment_discount']) &&
            is_plugin_active('rede-for-woocommerce-pro/rede-for-woocommerce-pro.php')
        ) {

            for ($i = 1; $i <= $maxParcels; ++$i) {
                $parcelAmount = $cart_total / $i;
                if ($parcelAmount >= $minParcelValue && isset($settings[$i . 'x'])) {
                    $interest = round((float) $settings[$i . 'x'], 2);
                    $customLabel = apply_filters('integrationRedeGetInterest', $cart_total, $interest, $i, 'label', $this->gateway);
                    if($customLabel){
                        $phpArray[$i . 'x'] = $customLabel;
                    }
                }
            }
        } else {
            for ($i = 1; $i <= $maxParcels; ++$i) {
                $parcelAmount = $cart_total / $i;
                if ($parcelAmount >= $minParcelValue) {
                    $phpArray[$i . 'x'] = html_entity_decode(sprintf('%dx de %s', $i, wp_strip_all_tags(wc_price($parcelAmount))));
                }
            }
        }
        return $phpArray;
    }
}
