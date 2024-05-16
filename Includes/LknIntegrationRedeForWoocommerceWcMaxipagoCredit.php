<?php
namespace Lkn\IntegrationRedeForWoocommerce\Includes;

use Exception;
use WC_Order;

class LknIntegrationRedeForWoocommerceWcMaxipagoCredit extends LknIntegrationRedeForWoocommerceWcRedeAbstract {

    public function __construct() {

        $this->id                 = 'maxipago_credit';
        $this->method_title       = esc_attr__( 'Pay with the Maxipago Credit', 'integration-rede-for-woocommerce' );
        $this->method_description = esc_attr__( 'Enables and configures payments with Maxipago Credit', 'integration-rede-for-woocommerce' );
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
        
        $this->configs = $this->getConfigsMaxipagoCredit();

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

    /**
     * This function centralizes the data in one spot for ease mannagment
     *
     * @return array
     */
    public function getConfigsMaxipagoCredit() {
        $configs = array();

        $configs['basePath'] = INTEGRATION_REDE_FOR_WOOCOMMERCE_DIR . 'Includes/logs/';
        $configs['base'] = $configs['basePath'] . gmdate('d.m.Y-H.i.s') . '.maxipagoCredit.log';
        $configs['debug'] = $this->get_option('debug');

        return $configs;
    }

    public function initFormFields() {
        $this->form_fields = array(
            'enabled'       => array(
                'title'   => __('Enable/Disable', 'integration-rede-for-woocommerce'),
                'type'    => 'checkbox',
                'label'   => __('Enables payment with Maxipago', 'integration-rede-for-woocommerce'),
                'default' => 'no'
            ),
            'title'         => array(
                'title'       => __('Title', 'integration-rede-for-woocommerce'),
                'type'        => 'text',
                'description' => __('This controls the title which the user sees during checkout.', 'integration-rede-for-woocommerce'),
                'default'     => __('Pay with the Maxipago Credit', 'integration-rede-for-woocommerce'),
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
            ),
            'credit_options' => array(
				'title' => esc_attr__( 'Credit Card Settings', 'integration-rede-for-woocommerce' ),
				'type'  => 'title',
			),
            'min_parcels_value' => array(
				'title'   => esc_attr__( 'Value of the smallest installment', 'integration-rede-for-woocommerce' ),
				'type'    => 'text',
				'default' => '0',
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
            
            'developers' => array(
                'title' => esc_attr__( 'Developer Settings', 'integration-rede-for-woocommerce' ),
                'type'  => 'title',
            ),

            'debug' => array(
                'title'   => esc_attr__( 'Debug', 'integration-rede-for-woocommerce' ),
                'type'    => 'checkbox',
                'label'   => esc_attr__( 'Enable debug logs', 'integration-rede-for-woocommerce' ),
                'default' => esc_attr__( 'no', 'integration-rede-for-woocommerce' ),
            )
        );

        $customConfigs = apply_filters('integrationRedeGetCustomConfigs', $this->form_fields, array(
            'merchantId' => sanitize_text_field($this->get_option('merchant_id')),
            'merchantKey' => sanitize_text_field($this->get_option('merchant_key'))
        )); 
		
        if ( ! empty($customConfigs)) {
            $this->form_fields = array_merge($this->form_fields, $customConfigs);
        }
    }

    protected function getCheckoutForm($order_total = 0) {
        wc_get_template(
            'creditCard/maxipagoPaymentCreditForm.php',
            array(
                'installments' => $this->getInstallments($order_total),
            ),
            'woocommerce/maxipago/',
            LknIntegrationRedeForWoocommerceWcRede::getTemplatesPath()
        );
    }
    
    public function getInstallments( $order_total = 0 ) {
		$installments = [];
		$defaults     = array(
			'min_value'   => str_replace(',', '.', $this->get_option( 'min_parcels_value' )),
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

    public function process_payment($orderId) {
        if(!wp_verify_nonce($_POST['maxipago_card_nonce'], 'maxipagoCardNonce')){
			return array(
				'result'   => 'fail',
				'redirect' => '',
			);
		}

		$order       = wc_get_order( $orderId );

        $woocommerceCountry = get_option('woocommerce_default_country');
        // Extraindo somente o país da string
        $countryParts = explode(':', $woocommerceCountry);
        $countryCode = $countryParts[0];
            
        $merchantId = sanitize_text_field($this->get_option('merchant_id'));
        $companyName = sanitize_text_field($this->get_option('company_name'));
        $merchantKey = sanitize_text_field($this->get_option('merchant_key'));
        $capture = sanitize_text_field($this->get_option('auto_capture')) == 'yes' ? 'sale' : 'auth';;
        $referenceNum = uniqid('order_', true);
		$valid       = true;
        
		if ( $valid ) {
            $installments = isset( $_POST['maxipago_credit_installments'] ) ? 
            absint( sanitize_text_field($_POST['maxipago_credit_installments']) ) : 1;
			
			$creditExpiry = sanitize_text_field($_POST['maxipago_credit_expiry']);
            
            
			
			if (strpos($creditExpiry, '/') !== false) {
				$expiration   = explode( '/', $creditExpiry );
			} else {
				$expiration = [
					substr($creditExpiry, 0, 2),
					substr($creditExpiry, -2, 2),
				];
			}	
            if($_POST['maxipago_credit_card_cpf']){
                $_POST['billing_cpf'] = $_POST['maxipago_credit_card_cpf'];
            }
            $clientData = array(
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
                'country'               => $countryCode,                
            );
	
			$cardData = array(
				'card_number'           => preg_replace( '/[^\d]/', '', sanitize_text_field( $_POST['maxipago_credit_number'] ) ),
				'card_expiration_month' => sanitize_text_field( $expiration[0] ),
				'card_expiration_year'  => $this->normalize_expiration_year( sanitize_text_field( $expiration[1] ) ),
				'card_cvv'              => sanitize_text_field( $_POST['maxipago_credit_cvc'] ),
				'card_holder'           => sanitize_text_field( $_POST['maxipago_credit_holder_name'] ),
				'card_installments'     => sanitize_text_field( $_POST['maxipago_credit_installments'] ),
			);
            
            
			try {
                $environment = $this->get_option('environment');
                
                if ( $valid ) { 
                    $valid = $this->validate_card_number( $cardData['card_number'] );
                }
            
                if ( $valid ) {
                    $valid = $this->validate_card_fields( $_POST );
                }
            
                if ( $valid ) {
                    $valid = $this->validate_installments( $_POST, $order->get_total() );
                }

                if(!$this->validateCpf($clientData['billing_cpf'])){
                    throw new Exception(__("Please enter a valid cpf number", 'integration-rede-for-woocommerce'));
                }
                if($environment === 'production'){
                    $apiUrl = 'https://api.maxipago.net/UniversalAPI/postXML';
                    $processorID = '1';
                }else{
                    $apiUrl = 'https://testapi.maxipago.net/UniversalAPI/postXML';
                    $processorID = '5';
                }
                $xmlData = "<?xml version='1.0' encoding='UTF-8'?>
                    <transaction-request>
                        <version>3.1.1.15</version>
                        <verification>
                            <merchantId>$merchantId</merchantId>
                            <merchantKey>$merchantKey</merchantKey>
                        </verification>
                        <order>
                            <$capture>
                                <processorID>$processorID</processorID>
                                <referenceNum>$referenceNum</referenceNum>
                                <customerIdExt>".$clientData['billing_cpf']."</customerIdExt>
                                <billing>
                                    <name>".$clientData['billing_name']."</name>
                                    <address>".$clientData['billing_address_1']."</address>
                                    <district>".$clientData['billing_district']."</district>
                                    <city>".$clientData['billing_city']."</city>
                                    <state>".$clientData['billing_state']."</state>
                                    <postalcode>".$clientData['billing_postcode']."</postalcode>
                                    <country>".$clientData['country']."</country>
                                    <phone>".$clientData['billing_phone']."</phone>
                                    <companyName>$companyName</companyName>
                                </billing>
                                <transactionDetail>
                                    <payType>
                                        <creditCard>
                                            <number>".$cardData['card_number']."</number>
                                            <expMonth>".$cardData['card_expiration_month']."</expMonth>
                                            <expYear>".$cardData['card_expiration_year']."</expYear>
                                            <cvvNumber>".$cardData['card_cvv']."</cvvNumber>
                                        </creditCard>
                                    </payType>
                                </transactionDetail>
                                <payment>
                                    <chargeTotal>".$order->get_total()."</chargeTotal>
                                    <currencyCode>".$clientData['currency_code']."</currencyCode> 
                                    <creditInstallment>
                                        <numberOfInstallments>".$installments."</numberOfInstallments>
                                        <chargeInterest>N</chargeInterest>
                                    </creditInstallment>
                                </payment>
                            </$capture>
                        </order>
                    </transaction-request>";
                
                $args = array(
                    'body'        => $xmlData,
                    'headers'     => array(
                        'Content-Type' => 'application/xml'
                    ),
                    'sslverify' => false // Desativa a verificação do certificado SSL
                );

                $response = wp_remote_post($apiUrl, $args);

                if (is_wp_error($response)) {
                    $error_message = $response->get_error_message();
                    throw new Exception(esc_attr($error_message));
                } else {
                    $response_body = wp_remote_retrieve_body($response);
                    $xml = simplexml_load_string($response_body);
                }
                
                //Reconstruindo o $xml para facilitar o uso da variavel
                $xml_encode = wp_json_encode($xml);
                $xml_decode = json_decode($xml_encode, true);
                
                if($xml_decode['processorMessage'] == "APPROVED"){

                    $order->update_meta_data( '_wc_maxipago_transaction_return_message', $xml_decode['processorMessage'] );
                    $order->update_meta_data( '_wc_maxipago_transaction_installments', $installments );
                    $order->update_meta_data( '_wc_maxipago_transaction_id', $xml_decode['orderID']);
                    $order->update_meta_data( '_wc_maxipago_transaction_bin', $xml_decode['creditCardBin']);
                    $order->update_meta_data( '_wc_maxipago_transaction_last4', $xml_decode['creditCardLast4']);
                    $order->update_meta_data( '_wc_maxipago_transaction_nsu', $xml_decode['transactionID']);
                    $order->update_meta_data( '_wc_maxipago_transaction_reference_num', $referenceNum);
                    $order->update_meta_data( '_wc_maxipago_transaction_authorization_code', $xml_decode['authCode']);
                    $order->update_meta_data( '_wc_maxipago_transaction_environment', $environment );
                    $order->update_meta_data( '_wc_maxipago_transaction_holder', $cardData['card_holder'] );
                    $order->update_meta_data( '_wc_maxipago_transaction_expiration', $creditExpiry );
                    if($capture == 'sale'){
                        $order->update_meta_data( '_wc_rede_captured', true );
                        $order->update_status('processing');
                    }
                    if($capture == 'auth'){
                        $order->update_meta_data( '_wc_rede_captured', false );
                        $order->update_status('on-hold');
                    }
                    
                }

                LknIntegrationRedeForWoocommerceHelper::reg_log(array(
					'transaction' => $xml,
                    'order' => [
                        'orderId' => $orderId,
                        'amount' => $order->get_total(),
                        'status' => $order->get_status()
                    ],
					
                ), $this->configs);
                
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
		if ( $order->get_payment_method() === 'maxipago_credit' ) {
			$metaKeys = array(
				'_wc_maxipago_transaction_environment' => esc_attr__( 'Environment', 'integration-rede-for-woocommerce' ),
				'_wc_maxipago_transaction_return_message' => esc_attr__( 'Return Message', 'integration-rede-for-woocommerce' ),
				'_wc_maxipago_transaction_id' => esc_attr__( 'Transaction ID', 'integration-rede-for-woocommerce' ),
				'_wc_maxipago_transaction_nsu' => esc_attr__( 'Nsu', 'integration-rede-for-woocommerce' ),
				'_wc_maxipago_transaction_authorization_code' => esc_attr__( 'Authorization Code', 'integration-rede-for-woocommerce' ),
				'_wc_maxipago_transaction_bin' => esc_attr__( 'Bin', 'integration-rede-for-woocommerce' ),
				'_wc_maxipago_transaction_last4' => esc_attr__( 'Last 4', 'integration-rede-for-woocommerce' ),
				'_wc_maxipago_transaction_installments' => esc_attr__( 'Installments', 'integration-rede-for-woocommerce' ),
				'_wc_maxipago_transaction_holder' => esc_attr__( 'Cardholder', 'integration-rede-for-woocommerce' ),
				'_wc_maxipago_transaction_expiration' => esc_attr__( 'Card Expiration', 'integration-rede-for-woocommerce' ),
				'_wc_maxipago_transaction_reference_num' => esc_attr__( 'Reference Number', 'integration-rede-for-woocommerce' )
			);

			$this->generateMetaTable( $order, $metaKeys, 'Maxipago');
        }
	}

    public function checkoutScripts() {
		
		$plugin_url = plugin_dir_url( LknIntegrationRedeForWoocommerceWcRede::FILE ).'../';
		wp_enqueue_script( 'fixInfiniteLoading-js', $plugin_url . 'Public/js/fixInfiniteLoading.js', array(), '1.0.0', true );
		
		
		if ( ! is_checkout() ) {
			return;
		}

		if ( ! $this->is_available() ) {
			return;
		}

		wp_enqueue_style( 'wc-rede-checkout-webservice' );


		wp_enqueue_style( 'card-style', $plugin_url . 'Public/css/card.css', array(), '1.0.0', 'all' );
		wp_enqueue_style( 'woo-maxipago-style', $plugin_url . 'Public/css/maxipago/styleMaxipagoCredit.css', array(), '1.0.0', 'all' );

		wp_enqueue_script( 'woo-maxipago-js', $plugin_url . 'Public/js/creditCard/maxipago/wooMaxipagoCredit.js', array(), '1.0.0', true );
		wp_enqueue_script( 'woo-rede-animated-card-jquery', $plugin_url . 'Public/js/jquery.card.js', array( 'jquery', 'woo-maxipago-js' ), '2.5.0', true );

		wp_localize_script( 'woo-maxipago-js', 'wooMaxipago', [
			'debug' => defined( 'WP_DEBUG' ) && WP_DEBUG,
		]);
	}

    public function getMerchantAuth(){
        return array(
            'merchantId' => $this->get_option('merchant_id'),
            'merchantKey' => $this->get_option('merchant_key'),
            'environment' => $this->get_option('environment')
        );
    }
}
