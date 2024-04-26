<?php

namespace Lkn\IntegrationRedeForWoocommerce\Includes;

use WP_REST_Response;

final class LknIntegrationRedeForWoocommerceEndPoints {

    public function registerGetTransactionInstallment(): void {
        register_rest_route('redeForWoocommerce', '/getphpAttributes', array(
            'methods' => 'GET',
            'callback' => array($this, 'getphpAttributes'),
        ));
    }
    
    public function getphpAttributes() {
        $installments_rede = get_option('woocommerce_rede_credit_settings')['max_parcels_number'];
        $installments_maxipago = get_option('woocommerce_maxipago_credit_settings')['max_parcels_number'];
        
        // Convertendo $installments para um objeto em vez de uma string
        $phpAttributes = (object) [
            'installments_rede' => (int)$installments_rede,
            'installments_maxipago' => (int)$installments_maxipago,
            'translations' => [
                'fieldsNotFilled' => __('Please fill in all fields correctly.', 'integration-rede-for-woocommerce')
            ],
        ];
        
        return new WP_REST_Response($phpAttributes, 200);
    }
    
}
