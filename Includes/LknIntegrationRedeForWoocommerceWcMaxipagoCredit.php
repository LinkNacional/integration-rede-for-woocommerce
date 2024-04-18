<?php
namespace Lkn\IntegrationRedeForWoocommerce\Includes;

use Exception;
use WC_Order;

class LknIntegrationRedeForWoocommerceWcMaxipagoCredit extends LknIntegrationRedeForWoocommerceWcRedeAbstract {

    public function __construct() {

        $this->id                 = 'maxipago';
        $this->method_title       = 'Maxipago';
        $this->method_description = 'Integrate with Maxipago payment gateway';
        $this->title              = 'Maxipago';
        $this->has_fields         = true;

        // Define os campos de configuração
        $this->init_form_fields();
        $this->init_settings();

        // Define as propriedades dos campos de configuração
        $this->merchant_id = $this->get_option('merchant_id');
        $this->merchant_key = $this->get_option('merchant_key');

    }

    public function init_form_fields() {
        $this->form_fields = array(
            'enabled'      => array(
                'title'   => __('Enable/Disable', 'woocommerce'),
                'type'    => 'checkbox',
                'label'   => __('Enable Maxipago', 'woocommerce'),
                'default' => 'no'
            ),
            'title'        => array(
                'title'       => __('Title', 'woocommerce'),
                'type'        => 'text',
                'description' => __('This controls the title which the user sees during checkout.', 'woocommerce'),
                'default'     => __('Maxipago', 'woocommerce'),
                'desc_tip'    => true,
            ),
            'description'  => array(
                'title'       => __('Description', 'woocommerce'),
                'type'        => 'textarea',
                'description' => __('This controls the description which the user sees during checkout.', 'woocommerce'),
                'default'     => __('Pay securely with Maxipago.', 'woocommerce'),
                'desc_tip'    => true,
            ),
            'merchant_id'  => array(
                'title'       => __('Merchant ID', 'woocommerce'),
                'type'        => 'text',
                'description' => __('Your Maxipago Merchant ID.', 'woocommerce'),
                'default'     => '',
                'desc_tip'    => true,
                'required'    => true,
            ),
            'merchant_key' => array(
                'title'       => __('Merchant Key', 'woocommerce'),
                'type'        => 'text',
                'description' => __('Your Maxipago Merchant Key.', 'woocommerce'),
                'default'     => '',
                'desc_tip'    => true,
                'required'    => true,
            ),
        );
    }

    protected function get_checkout_form($order_total = 0) {
        wc_get_template(
            'credit-card/rede-payment-form.php',
            array(
                'installments' => $this->get_installments($order_total),
            ),
            'woocommerce/rede/',
            LknIntegrationRedeForWoocommerceWcRede::get_templates_path()
        );
    }

    protected function validate_card_number($card_number) {
    }

    protected function validate_card_fields($posted) {
    }

    protected function validate_installments($posted, $order_total) {
    }

    public function process_payment($order_id) {
    }
}
