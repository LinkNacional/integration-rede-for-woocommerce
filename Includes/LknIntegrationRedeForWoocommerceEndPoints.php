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
        $installments = get_option('woocommerce_rede_credit_settings')['max_parcels_number'];
        
        // Convertendo $installments para um objeto em vez de uma string
        $installments_obj = (object) [
            'installments' => (int)$installments,
            'translations' => [
                'fieldsNotFilled' => esc_attr_e('Please fill in all fields correctly.', 'integration-rede-for-woocommerce')
            ]
        ];
        
        return new WP_REST_Response($installments_obj, 200);
    }
}
