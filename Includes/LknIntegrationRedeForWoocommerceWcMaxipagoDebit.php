<?php
namespace Lkn\IntegrationRedeForWoocommerce\Includes;

use Exception;
use WC_Order;

class LknIntegrationRedeForWoocommerceWcMaxipagoDebit extends LknIntegrationRedeForWoocommerceWcRedeAbstract {

    public function __construct() {

        $this->id                 = 'maxipago_debit';
        $this->method_title       = esc_attr__( 'Pay with the Maxipago Debit', 'integration-rede-for-woocommerce' );
        $this->method_description = esc_attr__( 'Enables and configures payments with Maxipago', 'integration-rede-for-woocommerce' );
        $this->title              = 'Maxipago';
        $this->has_fields         = true;

        // Define os campos de configuração
        $this->initFormFields();
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

    function addNeighborhoodFieldToCheckout( $fields ) {
        if (!is_plugin_active('woocommerce-extra-checkout-fields-for-brazil/woocommerce-extra-checkout-fields-for-brazil.php')
            && $this->is_available()) {
            $fields['billing']['billing_neighborhood'] = array(
                'label'       => __('District', 'integration-rede-for-woocommerce'),
                'placeholder' => __('District', 'integration-rede-for-woocommerce'),
                'required'    => true,
                'class'       => array('form-row-wide'),
                'clear'       => true,
            );
        
            // Obtém a posição do campo de endereço
            $address_position = array_search( 'billing_address_1', array_keys( $fields['billing'] ) );
        
            // Insere o campo de bairro após o campo de endereço
            $fields['billing'] = array_slice( $fields['billing'], 0, $address_position + 2, true ) +
                                 array('billing_neighborhood' => $fields['billing']['billing_neighborhood']) +
                                 array_slice( $fields['billing'], $address_position + 2, NULL, true );
        }
        return $fields;
    }

    public function initFormFields() {
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
                'default'     => __('Pay with the Maxipago Debit', 'integration-rede-for-woocommerce'),
                'desc_tip'    => true,
            ),
            
            'maxipago' => array(
				'title' => esc_attr__( 'General configuration', 'integration-rede-for-woocommerce' ),
				'type'  => 'title',
			),

            'company_name'    => array(
                'title'       => __('Seller Company Name', 'integration-rede-for-woocommerce'),
                'type'        => 'text',
                'desc_tip'    => true,
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
            )
        );
    }

    protected function getCheckoutForm($order_total = 0) {
        wc_get_template(
            'debit-card/maxipago-payment-debit-form.php',
            array(),
            'woocommerce/maxipago/',
            LknIntegrationRedeForWoocommerceWcRede::getTemplatesPath()
        );
    }


    public function process_payment($order_id) {

        if(!wp_verify_nonce($_POST['maxipago_card_nonce'], 'maxipagoCardNonce')){
			return array(
				'result'   => 'fail',
				'redirect' => '',
			);
		}

		$order       = wc_get_order( $order_id );

        $woocommerce_country = get_option('woocommerce_default_country');
        // Extraindo somente o país da string
        $country_parts = explode(':', $woocommerce_country);
        $country_code = $country_parts[0];
            
        $merchant_id = sanitize_text_field($this->get_option('merchant_id'));
        $company_name = sanitize_text_field($this->get_option('company_name'));
        $merchant_key = sanitize_text_field($this->get_option('merchant_key'));
        $reference_num = uniqid('order_', true);
		$valid       = true;
        
		if ( $valid ) {
			
			$credit_expiry = sanitize_text_field($_POST['maxipago_debit_expiry']);
            
            
			
			if (strpos($credit_expiry, '/') !== false) {
				$expiration   = explode( '/', $credit_expiry );
			} else {
				$expiration = [
					substr($credit_expiry, 0, 2),
					substr($credit_expiry, -2, 2),
				];
			}	
            
            $client_data = array(
                'billing_cpf'   => sanitize_text_field( $_POST['billing_cpf'] ),
                'billing_name'          => sanitize_text_field( $_POST['billing_address_1'] . ' ' . $_POST['billing_address_1']),
                'billing_address_1'     => sanitize_text_field( $_POST['billing_address_1'] ),
                'billing_district'      => sanitize_text_field( $_POST['billing_neighborhood'] ),
                'billing_city'          => sanitize_text_field( $_POST['billing_city'] ),
                'billing_state'         => sanitize_text_field( $_POST['billing_state'] ),
                'billing_postcode'      => sanitize_text_field( $_POST['billing_postcode'] ),
                'billing_phone'         => sanitize_text_field( $_POST['billing_phone'] ),
                'billing_email'         => sanitize_text_field( $_POST['billing_email'] ),
                'currency_code'         => get_option('woocommerce_currency'),
                'country'               => $country_code,                
            );
	
			$card_data = array(
				'card_number'           => preg_replace( '/[^\d]/', '', sanitize_text_field( $_POST['maxipago_debit_number'] ) ),
				'card_expiration_month' => sanitize_text_field( $expiration[0] ),
				'card_expiration_year'  => $this->normalize_expiration_year( sanitize_text_field( $expiration[1] ) ),
				'card_cvv'              => sanitize_text_field( $_POST['maxipago_debit_cvc'] ),
				'card_holder'           => sanitize_text_field( $_POST['maxipago_debit_holder_name'] ),
			);
            
            
			try {
                $environment = $this->get_option('environment');
                
                if ( $valid ) { 
                    $valid = $this->validate_card_number( $card_data['card_number'] );
                }
            
                if ( $valid ) {
                    $valid = $this->validate_card_fields( $_POST );
                }
            
                if(!$this->validateCpf($client_data['billing_cpf'])){
                    throw new Exception(__("Please enter a valid cpf number", 'integration-rede-for-woocommerce'));
                }

                if($environment === 'production'){
                    $api_url = 'https://api.maxipago.net/UniversalAPI/postXML';
                    $processorID = '1';
                }else{
                    $api_url = 'https://testapi.maxipago.net/UniversalAPI/postXML';
                    $processorID = '5';
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
                                <processorID>$processorID</processorID>
                                <referenceNum>$reference_num</referenceNum>
                                <fraudCheck>N</fraudCheck>
                                <customerIdExt>".$client_data['billing_cpf']."</customerIdExt>
                                <billing>
                                    <name>".$client_data['billing_name']."</name>
                                    <address>".$client_data['billing_address_1']."</address>
                                    <district>".$client_data['billing_district']."</district>
                                    <city>".$client_data['billing_city']."</city>
                                    <state>".$client_data['billing_state']."</state>
                                    <postalcode>".$client_data['billing_postcode']."</postalcode>
                                    <country>".$client_data['country']."</country>
                                    <phone>".$client_data['billing_phone']."</phone>
                                    <companyName>$company_name</companyName>
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
                                    <currencyCode>".$client_data['currency_code']."</currencyCode> 
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
                    $order->update_meta_data( '_wc_maxipago_transaction_id', $xml_decode['orderID']);
                    $order->update_meta_data( '_wc_maxipago_transaction_bin', $xml_decode['creditCardBin']);
                    $order->update_meta_data( '_wc_maxipago_transaction_last4', $xml_decode['creditCardLast4']);
                    $order->update_meta_data( '_wc_maxipago_transaction_nsu', $xml_decode['transactionID']);
                    $order->update_meta_data( '_wc_maxipago_transaction_reference_num', $reference_num);
                    $order->update_meta_data( '_wc_maxipago_transaction_authorization_code', $xml_decode['authCode']);
                    $order->update_meta_data( '_wc_maxipago_transaction_environment', $environment );
                    $order->update_meta_data( '_wc_maxipago_transaction_holder', $card_data['card_holder'] );
                    $order->update_meta_data( '_wc_maxipago_transaction_expiration', $credit_expiry );
                    $order->payment_complete();
                    
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

    function validateCpf($cpf) {
        // Remove caracteres não numéricos
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        // Verifica se o CPF possui 11 dígitos
        if (strlen($cpf) != 11) {
            return false;
        }
        
        // Verifica se todos os dígitos são iguais
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }
    
        // Calcula o primeiro dígito verificador
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += intval($cpf[$i]) * (10 - $i);
        }
        $remainder = $sum % 11;
        $digit1 = ($remainder < 2) ? 0 : (11 - $remainder);
    
        // Calcula o segundo dígito verificador
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += intval($cpf[$i]) * (11 - $i);
        }
        $remainder = $sum % 11;
        $digit2 = ($remainder < 2) ? 0 : (11 - $remainder);
    
        // Verifica se os dígitos verificadores calculados são iguais aos fornecidos
        if ($cpf[9] == $digit1 && $cpf[10] == $digit2) {
            return true;
        } else {
            return false;
        }
    }    

    public function displayMeta( $order ) {
		if ( $order->get_payment_method() === 'maxipago_debit' ) {
			$meta_keys = array(
				'_wc_maxipago_transaction_environment' => esc_attr__( 'Environment', 'integration-rede-for-woocommerce' ),
				'_wc_maxipago_transaction_return_message' => esc_attr__( 'Return Message', 'integration-rede-for-woocommerce' ),
				'_wc_maxipago_transaction_id' => esc_attr__( 'Transaction ID', 'integration-rede-for-woocommerce' ),
				'_wc_maxipago_transaction_nsu' => esc_attr__( 'Nsu', 'integration-rede-for-woocommerce' ),
				'_wc_maxipago_transaction_authorization_code' => esc_attr__( 'Authorization Code', 'integration-rede-for-woocommerce' ),
				'_wc_maxipago_transaction_bin' => esc_attr__( 'Bin', 'integration-rede-for-woocommerce' ),
				'_wc_maxipago_transaction_last4' => esc_attr__( 'Last 4', 'integration-rede-for-woocommerce' ),
				'_wc_maxipago_transaction_holder' => esc_attr__( 'Cardholder', 'integration-rede-for-woocommerce' ),
				'_wc_maxipago_transaction_expiration' => esc_attr__( 'Card Expiration', 'integration-rede-for-woocommerce' ),
				'_wc_maxipago_transaction_reference_num' => esc_attr__( 'Reference Number', 'integration-rede-for-woocommerce' )
			);

			$this->generateMetaTable( $order, $meta_keys, 'Maxipago');
        }
	}

    public function checkoutScripts() {
		
		$plugin_url = plugin_dir_url( LknIntegrationRedeForWoocommerceWcRede::FILE ).'../';
		wp_enqueue_script( 'fix-infinite-loading-js', $plugin_url . 'assets/js/fix-infinite-loading.js', array(), '1.0.0', true );
		
		
		if ( ! is_checkout() ) {
			return;
		}

		if ( ! $this->is_available() ) {
			return;
		}

		wp_enqueue_style( 'wc-rede-checkout-webservice' );


		wp_enqueue_style( 'card-style', $plugin_url . 'assets/css/card.css', array(), '1.0.0', 'all' );
		wp_enqueue_style( 'woo-maxipago-debit-style', $plugin_url . 'assets/css/style-maxipago.css', array(), '1.0.0', 'all' );

		wp_enqueue_script( 'woo-maxipago-debit-js', $plugin_url . 'assets/js/woo-maxipago.js', array(), '1.0.0', true );
		wp_enqueue_script( 'woo-rede-animated-card-jquery', $plugin_url . 'assets/js/jquery.card.js', array( 'jquery', 'woo-maxipago-debit-js' ), '2.5.0', true );

		wp_localize_script( 'woo-maxipago-debit-js', 'wooMaxipago', [
			'debug' => defined( 'WP_DEBUG' ) && WP_DEBUG,
		]);
	}
}
