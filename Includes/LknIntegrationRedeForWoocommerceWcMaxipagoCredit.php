<?php
namespace Lkn\IntegrationRedeForWoocommerce\Includes;

use Exception;
use WC_Order;

class LknIntegrationRedeForWoocommerceWcMaxipagoCredit extends LknIntegrationRedeForWoocommerceWcRedeAbstract {

    public function __construct() {

        $this->id                 = 'maxipago_credit';
        $this->method_title       = esc_attr__( 'Pay with the Maxipago', 'integration-rede-for-woocommerce' );
        $this->method_description = esc_attr__( 'Enables and configures payments with Maxipago', 'integration-rede-for-woocommerce' );
        $this->title              = 'Maxipago';
        $this->has_fields         = true;

        // Define os campos de configuração
        $this->init_form_fields();
        $this->init_settings();

        // Define as propriedades dos campos de configuração
        $this->merchant_id = $this->get_option('merchant_id');
        $this->merchant_key = $this->get_option('merchant_key');

        // Define as configurações
        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        
        // Carrega os valores dos campos de configuração
        $this->enabled = $this->get_option('enabled');

    }

    public function init_form_fields() {
        $this->form_fields = array(
            'enabled'       => array(
                'title'   => __('Enable/Disable', 'integration-rede-for-woocommerce'),
                'type'    => 'checkbox',
                'label'   => __('Enable Maxipago', 'integration-rede-for-woocommerce'),
                'default' => 'no'
            ),
            'title'         => array(
                'title'       => __('Title', 'integration-rede-for-woocommerce'),
                'type'        => 'text',
                'description' => __('This controls the title which the user sees during checkout.', 'integration-rede-for-woocommerce'),
                'default'     => __('Maxipago', 'integration-rede-for-woocommerce'),
                'desc_tip'    => true,
            ),
            
            'maxipago' => array(
				'title' => esc_attr__( 'General configuration', 'integration-rede-for-woocommerce' ),
				'type'  => 'title',
			),

            'environment' => array(
				'title'       => esc_attr__( 'Environment', 'integration-rede-for-woocommerce' ),
				'type'        => 'select',
				'description' => esc_attr__( 'Choose the environment', 'integration-rede-for-woocommerce' ),
				'desc_tip'    => true,
				'class'       => 'wc-enhanced-select',
				'default'     => esc_attr__( 'test', 'integration-rede-for-woocommerce' ),
				'options' => array(
					'test'       => esc_attr__( 'Tests', 'integration-rede-for-woocommerce' ),
					'production' => esc_attr__( 'Production', 'integration-rede-for-woocommerce' ),
				),
			),

            'description'   => array(
                'title'       => __('Description', 'integration-rede-for-woocommerce'),
                'type'        => 'textarea',
                'description' => __('This controls the description which the user sees during checkout.', 'integration-rede-for-woocommerce'),
                'default'     => __('Pay securely with Maxipago.', 'integration-rede-for-woocommerce'),
                'desc_tip'    => true,
            ),
            'merchant_id'   => array(
                'title'       => __('Merchant ID', 'integration-rede-for-woocommerce'),
                'type'        => 'text',
                'description' => __('Your Maxipago Merchant ID.', 'integration-rede-for-woocommerce'),
                'default'     => '',
                'desc_tip'    => true,
                'required'    => true,
            ),
            'merchant_key'  => array(
                'title'       => __('Merchant Key', 'integration-rede-for-woocommerce'),
                'type'        => 'text',
                'description' => __('Your Maxipago Merchant Key.', 'integration-rede-for-woocommerce'),
                'default'     => '',
                'desc_tip'    => true,
                'required'    => true,
            ),
            'max_parcels_number' => array(
				'title'   => esc_attr__( 'Max installments', 'integration-rede-for-woocommerce' ),
				'type'    => 'select',
				'class'   => 'wc-enhanced-select',
				'default' => '12',
				'options' => array(
					'1'  => '1x',
					'2'  => '2x',
					'3'  => '3x',
					'4'  => '4x',
					'5'  => '5x',
					'6'  => '6x',
					'7'  => '7x',
					'8'  => '8x',
					'9'  => '9x',
					'10' => '10x',
					'11' => '11x',
					'12' => '12x',
				),
			),
        );
    }

    protected function get_checkout_form($order_total = 0) {
        wc_get_template(
            'credit-card/maxipago-payment-form.php',
            array(
                'installments' => $this->get_installments($order_total),
            ),
            'woocommerce/rede/',
            LknIntegrationRedeForWoocommerceWcRede::get_templates_path()
        );
    }
    
    public function get_installments( $order_total = 0 ) {
		$installments = [];
		$defaults     = array(
			'min_value'   => $this->min_parcels_value,
			'max_parcels' => $this->get_option( 'max_parcels_number' ),
		);

		$installments_result = wp_parse_args( apply_filters( 'integration_rede_installments', $defaults ), $defaults );

		$min_value   = (float) $installments_result['min_value'];
		$max_parcels = (int) $installments_result['max_parcels'];

		for ( $i = 1; $i <= $max_parcels; ++$i ) {
			if ( ( $order_total / $i ) < $min_value ) break;

			$label = sprintf( '%dx de %s', $i, wp_strip_all_tags( wc_price( $order_total / $i ) ) );

			if ( $i === 1 ) $label .= ' (à vista)';

			$installments[] = array(
				'num'   => $i,
				'label' => $label,
			);
		}

		return $installments;
	}

    public function process_payment($order_id) {

        if(!wp_verify_nonce($_POST['maxipago_card_nonce'], 'maxipagoCardNonce')){
			return array(
				'result'   => 'fail',
				'redirect' => '',
			);
		}
		
		$order       = wc_get_order( $order_id );
		$card_number = isset( $_POST['maxipago_credit_number'] ) ? 
			sanitize_text_field( $_POST['maxipago_credit_number'] ) : '';

        $client_data = array(
            'maxipago_credit_cpf'    =>  sanitize_text_field( $_POST['maxipago_credit_cpf'] ),
            'billing_address_1'    =>  sanitize_text_field( $_POST['billing_address_1'] ),
            'billing_city'    =>  sanitize_text_field( $_POST['billing_city'] ),
            'billing_state'    =>  sanitize_text_field( $_POST['billing_state'] ),
            'billing_postcode'    =>  sanitize_text_field( $_POST['billing_postcode'] ),
            'billing_phone'    =>  sanitize_text_field( $_POST['billing_phone'] ),
            'billing_email'    =>  sanitize_text_field( $_POST['billing_email'] ),
        );
            
        $merchant_id = sanitize_text_field($this->get_option('merchant_id'));
        $merchant_key = sanitize_text_field($this->get_option('merchant_key'));
		$valid       = true;
	
		if ( $valid ) { 
			$valid = $this->validate_card_number( $card_number );
		}
	
		if ( $valid ) {
			$valid = $this->validate_card_fields( $_POST );
		}
	
		if ( $valid ) {
			$valid = $this->validate_installments( $_POST, $order->get_total() );
		}
        
		if ( $valid ) {
            $installments = isset( $_POST['maxipago_credit_installments'] ) ? 
            absint( sanitize_text_field($_POST['maxipago_credit_installments']) ) : 1;
			
			$credit_expiry = sanitize_text_field($_POST['maxipago_credit_expiry']);
			
			if (strpos($credit_expiry, '/') !== false) {
				$expiration   = explode( '/', $credit_expiry );
			} else {
				$expiration = [
					substr($credit_expiry, 0, 2),
					substr($credit_expiry, -2, 2),
				];
			}			
	
			$card_data = array(
				'card_number'           => preg_replace( '/[^\d]/', '', sanitize_text_field( $_POST['maxipago_credit_number'] ) ),
				'card_expiration_month' => sanitize_text_field( $expiration[0] ),
				'card_expiration_year'  => $this->normalize_expiration_year( sanitize_text_field( $expiration[1] ) ),
				'card_cvv'              => sanitize_text_field( $_POST['maxipago_credit_cvc'] ),
				'card_holder'           => sanitize_text_field( $_POST['maxipago_credit_holder_name'] ),
				'card_installments'     => sanitize_text_field( $_POST['maxipago_credit_installments'] ),
			);
            
            
			try {
                $environment = $this->get_option('environment');
                
                if($environment === 'production'){
                    $api_url = 'https://api.maxipago.net/UniversalAPI/postXML';
                }else{
                    $api_url = 'https://testapi.maxipago.net/UniversalAPI/postXML';
                }

                $xml_data = "<?xml version='1.0' encoding='UTF-8'?>
                    <transaction-request>
                        <version>3.1.1.15</version>
                        <verification>
                            <merchantId>$merchant_id</merchantId>
                            <merchantKey>$merchant_key</merchantKey>
                        </verification>
                        <order>
                            <sale>
                                <processorID>1</processorID>
                                <referenceNum>Sandbox_teste_1</referenceNum>
                                <fraudCheck>N</fraudCheck>
                                <customerIdExt>".$client_data['maxipago_credit_cpf']."</customerIdExt>
                                <billing>
                                    <name>Cliente Gateway</name>
                                    <address>".$client_data['billing_address_1']."</address>
                                    <district>Jabaquara</district>
                                    <city>".$client_data['billing_city']."</city>
                                    <state>".$client_data['billing_state']."</state>
                                    <postalcode>".$client_data['billing_postcode']."</postalcode>
                                    <country>BR</country>
                                    <phone>".$client_data['billing_phone']."</phone>
                                    <companyName>maxiPago!</companyName>
                                </billing>
                                <transactionDetail>
                                    <payType>
                                        <creditCard>
                                            <number>".$card_data['card_number']."</number>
                                            <expMonth>".$card_data['card_expiration_month']."</expMonth>
                                            <expYear>".$card_data['card_expiration_year']."</expYear>
                                            <cvvNumber>".$card_data['card_cvv']."</cvvNumber>
                                        </creditCard>
                                    </payType>
                                </transactionDetail>
                                <payment>
                                    <chargeTotal>".$order->get_total()."</chargeTotal>
                                    <currencyCode>BRL</currencyCode> 
                                    <creditInstallment>
                                        <numberOfInstallments>".$card_data['card_installments']."</numberOfInstallments>
                                        <chargeInterest>N</chargeInterest>
                                    </creditInstallment>
                                </payment>
                            </sale>
                        </order>
                    </transaction-request>";

                $args = array(
                    'body'        => $xml_data,
                    'headers'     => array(
                        'Content-Type' => 'application/xml'
                    ),
                    'sslverify' => false // Desativa a verificação do certificado SSL
                );

                $response = wp_remote_post($api_url, $args);

                if (is_wp_error($response)) {
                    $error_message = $response->get_error_message();
                    echo "Ocorreu um erro: $error_message";
                } else {
                    $response_body = wp_remote_retrieve_body($response);
                    $xml = simplexml_load_string($response_body);
                }
                
                //Reconstruindo o $xml para facilitar o uso da variavel
                $xml_encode = json_encode($xml);
                $xml_decode = json_decode($xml_encode, true);

                if($xml_decode['processorMessage'] == "APPROVED"){

                    $order->update_meta_data( '_wc_maxipago_transaction_return_message', $xml_decode['processorMessage'] );
                    $order->update_meta_data( '_wc_maxipago_transaction_installments', $installments );
                    $order->update_meta_data( '_wc_maxipago_transaction_id', $xml_decode['orderID']); //TODO lembrar de mudar de transition_id para order_id e adicionar tabela para mostrar detales do cartão na edição de pedido
                    $order->update_meta_data( '_wc_maxipago_transaction_bin', $xml_decode['creditCardBin']);
                    $order->update_meta_data( '_wc_maxipago_transaction_last4', $xml_decode['creditCardLast4']);
                    $order->update_meta_data( '_wc_maxipago_transaction_nsu', $xml_decode['transactionID']);
                    $order->update_meta_data( '_wc_maxipago_transaction_authorization_code', $xml_decode['authCode']);
                    $order->update_meta_data( '_wc_rede_transaction_environment', $environment );
                    $order->update_status( 'completed', esc_attr_e( 'Payment approved', 'integration-rede-for-woocommerce'));

                }
                if($xml_decode['responseMessage']  == "INVALID REQUEST"){
                    throw new Exception($xml_decode['errorMessage']);             
                    
                }  
                //Caso não exista nenhuma das Message, o Merchant ID ou Merchant Key estão invalidos
                if(!isset($xml_decode['processorMessage']) && !isset($xml_decode['processorMessage'])){
                    throw new Exception(__("Merchant ID or Merchant Key is invalid!", 'integration-rede-for-woocommerce'));   
                }


                $order->save();
			} catch ( Exception $e ) {
				$this->add_error( $e->getMessage() );
				$valid = false;
			}
		}
	
		if ( $valid ) {
			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order ),
			);
		} else {
			return array(
				'result'   => 'fail',
				'redirect' => '',
			);
		}
        
    }
}
