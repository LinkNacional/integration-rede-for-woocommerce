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
                'title'   => __('Enable/Disable', 'woocommerce'),
                'type'    => 'checkbox',
                'label'   => __('Enable Maxipago', 'woocommerce'),
                'default' => 'no'
            ),
            'title'         => array(
                'title'       => __('Title', 'woocommerce'),
                'type'        => 'text',
                'description' => __('This controls the title which the user sees during checkout.', 'woocommerce'),
                'default'     => __('Maxipago', 'woocommerce'),
                'desc_tip'    => true,
            ),
            'description'   => array(
                'title'       => __('Description', 'woocommerce'),
                'type'        => 'textarea',
                'description' => __('This controls the description which the user sees during checkout.', 'woocommerce'),
                'default'     => __('Pay securely with Maxipago.', 'woocommerce'),
                'desc_tip'    => true,
            ),
            'merchant_id'   => array(
                'title'       => __('Merchant ID', 'woocommerce'),
                'type'        => 'text',
                'description' => __('Your Maxipago Merchant ID.', 'woocommerce'),
                'default'     => '',
                'desc_tip'    => true,
                'required'    => true,
            ),
            'merchant_key'  => array(
                'title'       => __('Merchant Key', 'woocommerce'),
                'type'        => 'text',
                'description' => __('Your Maxipago Merchant Key.', 'woocommerce'),
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
		$valid       = true;
	
		/* if ( $valid ) {
			$valid = $this->validate_card_number( $card_number );
		}
	
		if ( $valid ) {
			$valid = $this->validate_card_fields( $_POST );
		}
	
		if ( $valid ) {
			$valid = $this->validate_installments( $_POST, $order->get_total() );
		} */
        
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
			);
            
            add_option('post teste pagamento233', json_encode($card_data));
            
            
			try {
                $mode = $this->get_option('mode'); // Adicione essa opção ao seu init_form_fields()
    
                // Defina o URL de acordo com o modo (teste ou produção)
                $url = 'https://testapi.maxipago.net/UniversalAPI/postXML';
            
                // Dados da solicitação XML
                //TODO Mover requisição de maxipago-payment-form.php para este arquivo depois da correção
                $xml_data = '
                    <?xml version="1.0" encoding="UTF-8"?>
                    <transaction-request>
                    <version>3.1.1.15</version>
                    <verification>
                    <merchantId>24187</merchantId>
                    <merchantKey>r7wz16zltnpkf61i4ugo3wds</merchantKey>
                    </verification>
                    <order>
                    <sale>
                    <processorID>1</processorID>
                    <referenceNum>Sandbox_teste_1</referenceNum>
                    <fraudCheck>N</fraudCheck>
                    <ipAddress>192.168.0.10</ipAddress>
                    <customerIdExt>120.071.510-14</customerIdExt>
                    <billing>
                    <name>Cliente Gateway</name>
                    <address>R. Volkswagen 1</address>
                    <address2>11º Andar</address2>
                    <district>Jabaquara</district>
                    <city>Sao Paulo</city>
                    <state>SP</state>
                    <postalcode>04344902</postalcode>
                    <country>BR</country>
                    <phone>1140044828</phone>
                    <email>clientegateway@clientegateway.com.br</email>
                    <companyName>maxiPago!</companyName>
                    </billing>
                    <shipping>
                    <name>Cliente Gateway</name>
                    <address>R. Volkswagen 1</address>
                    <address2>11º Andar</address2>
                    <district>Jabaquara</district>
                    <city>Sao Paulo</city>
                    <state>SP</state>
                    <postalcode>04344902</postalcode>
                    <country>BR</country>
                    <phone>1140044828</phone>
                    <email>clientegateway@clientegateway.com.br</email>
                    </shipping>
                    <transactionDetail>
                    <payType>
                    <creditCard>
                    <number>5307189041557414</number>
                    <expMonth>09</expMonth>
                    <expYear>2025</expYear>
                    <cvvNumber>513</cvvNumber>
                    </creditCard>
                    </payType>
                    </transactionDetail>
                    <payment>
                    <chargeTotal>100.00</chargeTotal>
                    <currencyCode>BRL</currencyCode>
                    <creditInstallment>
                    <numberOfInstallments>2</numberOfInstallments>
                    <chargeInterest>N</chargeInterest>
                    </creditInstallment>
                    </payment>
                    </sale>
                    </order>
                    </transaction-request>
                ';

                
                $response = wp_remote_post('https://testapi.maxipago.net/UniversalAPI/postXML', array(
                    'body' => $xml_data,
                ));
                
                if (is_wp_error($response)) {
                    // Trata erro do WordPress
                    $error_message = $response->get_error_message();
                    return array(
                        'result' => 'fail',
                        'message' => __('Houve um erro ao processar seu pagamento. Por favor, tente novamente mais tarde.', 'woocommerce'),
                        'error' => $error_message,
                    );
                } else {
                    // Extrai o corpo da resposta da API
                    $body = wp_remote_retrieve_body($response);
                    // Analisa o XML da resposta
                    $xml = simplexml_load_string($body);
                    throw new Exception( print_r($response) );
        
                    if ($xml === false) {
                        // Falha ao analisar o XML
                        return array(
                            'result' => 'fail',
                            'message' => 'Falha ao analisar a resposta XML',
                        );
                    }
        
                    // Verifica se a resposta contém uma mensagem de erro
                    if (!empty($xml->errorMessage)) {
                        // Resposta de erro
                        $error_message = (string)$xml->errorMessage;
                        return array(
                            'result' => 'fail',
                            'message' => $error_message,
                        );
                    } else {
                        // Resposta bem-sucedida
                        // Extrai informações necessárias do XML
                        $auth_code = (string)$xml->authCode;
                        $order_id = (string)$xml->orderID;
                        // Retorna os dados de sucesso
                        return array(
                            'result' => 'success',
                            'auth_code' => $auth_code,
                            'order_id' => $order_id,
                            'redirect' => $this->get_return_url($order),
                        );
                    }
                }
                
                
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
