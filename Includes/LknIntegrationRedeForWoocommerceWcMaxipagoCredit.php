<?php
namespace Lkn\IntegrationRedeForWoocommerce\Includes;

use Exception;
use WC_Order;
use WP_Error;

final class LknIntegrationRedeForWoocommerceWcMaxipagoCredit extends LknIntegrationRedeForWoocommerceWcRedeAbstract {
    public function __construct() {
        $this->id = 'maxipago_credit';
        $this->method_title = esc_attr__( 'Pay with the Maxipago Credit', 'woo-rede' );
        $this->method_description = esc_attr__( 'Enables and configures payments with Maxipago Credit', 'woo-rede' );
        $this->title = 'Maxipago';
        $this->has_fields = true;
        $this->supports = array(
            'products',
            'refunds',
        );

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

        $this->debug = $this->get_option( 'debug' );

        $this->log = $this->get_logger();

        $this->configs = $this->getConfigsMaxipagoCredit();
    }

    /**
     * Fields validation.
     *
     * @return bool
     */
    public function validate_fields() {
        if ( empty( $_POST['maxipago_credit_card_cpf']) && empty( $_POST['billing_cpf']) && empty( $_POST['billing_cnpj']) ) {
            wc_add_notice( esc_attr__( 'CPF is a required field', 'woo-rede' ), 'error' );

            return false;
        }

        if ( empty( $_POST['maxipago_credit_number'] ) ) {
            wc_add_notice( esc_attr__( 'Card number is a required field', 'woo-rede' ), 'error' );

            return false;
        }

        if ( empty( $_POST['maxipago_credit_expiry'] ) ) {
            wc_add_notice( esc_attr__( 'Card expiration is a required field', 'woo-rede' ), 'error' );

            return false;
        }

        if ( empty( $_POST['maxipago_credit_cvc'] ) ) {
            wc_add_notice( esc_attr__( 'Card security code is a required field', 'woo-rede' ), 'error' );

            return false;
        }

        if ( empty( $_POST['maxipago_credit_holder_name'] ) ) {
            wc_add_notice( esc_attr__( 'Cardholder name is a required field', 'woo-rede' ), 'error' );

            return false;
        }

        return true;
    }

    public function addNeighborhoodFieldToCheckout( $fields ) {
        if ( ! is_plugin_active('woocommerce-extra-checkout-fields-for-brazil/woocommerce-extra-checkout-fields-for-brazil.php')
            && $this->is_available()) {
            $fields['billing']['billing_neighborhood'] = array(
                'label' => __('District', 'woo-rede'),
                'placeholder' => __('District', 'woo-rede'),
                'required' => true,
                'class' => array('form-row-wide'),
                'clear' => true,
            );

            // Obtém a posição do campo de endereço
            $address_position = array_search( 'billing_address_1', array_keys( $fields['billing'] ), true );

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

    public function initFormFields(): void {
        LknIntegrationRedeForWoocommerceHelper::updateFixLoadScriptOption($this->id);        

        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable/Disable', 'woo-rede'),
                'type' => 'checkbox',
                'label' => __('Enables payment with Maxipago', 'woo-rede'),
                'default' => 'no'
            ),
            'title' => array(
                'title' => __('Title', 'woo-rede'),
                'type' => 'text',
                'description' => __('This controls the title which the user sees during checkout.', 'woo-rede'),
                'default' => __('Pay with the Maxipago Credit', 'woo-rede'),
                'desc_tip' => true,
            ),

            'maxipago' => array(
                'title' => esc_attr__( 'General', 'woo-rede' ),
                'type' => 'title',
            ),

            'company_name' => array(
                'title' => __('Seller Company Name', 'woo-rede'),
                'type' => 'text',
                'desc_tip' => true,
            ),

            'environment' => array(
                'title' => esc_attr__( 'Environment', 'woo-rede' ),
                'type' => 'select',
                'description' => esc_attr__( 'Choose the environment', 'woo-rede' ),
                'desc_tip' => true,
                'class' => 'wc-enhanced-select',
                'default' => esc_attr__( 'test', 'woo-rede' ),
                'options' => array(
                    'test' => esc_attr__( 'Tests', 'woo-rede' ),
                    'production' => esc_attr__( 'Production', 'woo-rede' ),
                ),
            ),

            'description' => array(
                'title' => __('Description', 'woo-rede'),
                'type' => 'textarea',
                'description' => __('This controls the description which the user sees during checkout.', 'woo-rede'),
                'default' => __('Pay securely with Maxipago.', 'woo-rede'),
                'desc_tip' => true,
            ),
            'merchant_id' => array(
                'title' => __('Merchant ID', 'woo-rede'),
                'type' => 'password',
                'description' => __('Your Maxipago Merchant ID.', 'woo-rede'),
                'default' => '',
                'desc_tip' => true,
                'custom_attributes' => array(
                    'required' => 'required'
                ),
            ),
            'merchant_key' => array(
                'title' => __('Merchant Key', 'woo-rede'),
                'type' => 'password',
                'description' => __('Your Maxipago Merchant Key.', 'woo-rede'),
                'default' => '',
                'desc_tip' => true,
                'custom_attributes' => array(
                    'required' => 'required'
                ),
            ),
            'enabled_fix_load_script' => array(
                'title' => __('Load on checkout', 'woo-rede'),
                'type' => 'checkbox',
                'description' => __('By disabling this feature, the plugin will be loaded during the checkout process. This feature, when enabled, prevents infinite loading errors on the checkout page. Only disable it if you are experiencing difficulties with the gateway loading.', 'woo-rede'),
                'desc_tip' => true,
                'label' => __('Load plugin on checkout. Default (enabled)', 'woo-rede'),
                'default' => 'yes',
            ),
            'credit_options' => array(
                'title' => esc_attr__( 'Credit Card', 'woo-rede' ),
                'type' => 'title',
            ),
            'min_parcels_value' => array(
                'title' => esc_attr__( 'Value of the smallest installment', 'woo-rede' ),
                'type' => 'text',
                'default' => '0',
                'description' => esc_attr__( 'Set the minimum allowed amount for each installment in credit transactions.', 'woo-rede' ),
                'desc_tip' => true,
            ),
            'max_parcels_number' => array(
                'title' => esc_attr__( 'Max installments', 'woo-rede' ),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'default' => '12',
                'options' => array(
                    '1' => '1x',
                    '2' => '2x',
                    '3' => '3x',
                    '4' => '4x',
                    '5' => '5x',
                    '6' => '6x',
                    '7' => '7x',
                    '8' => '8x',
                    '9' => '9x',
                    '10' => '10x',
                    '11' => '11x',
                    '12' => '12x',
                ),
                'description' => esc_attr__( 'Set the maximum number of installments allowed in credit transactions.', 'woo-rede' ),
                'desc_tip' => true,
            ),

            'developers' => array(
                'title' => esc_attr__( 'Developer', 'woo-rede' ),
                'type' => 'title',
            ),

            'debug' => array(
                'title' => esc_attr__( 'Debug', 'woo-rede' ),
                'type' => 'checkbox',
                'label' => esc_attr__( 'Enable debug logs.' . ' ', 'woo-rede' ) . wp_kses_post( '<a href="' . esc_url( admin_url( 'admin.php?page=wc-status&tab=logs' ) ) . '" target="_blank">' . __('See logs', 'woo-rede') . '</a>'),
                'default' => 'no',
                'description' => esc_attr__( 'Enable transaction logging.', 'woo-rede' ),
                'desc_tip' => true,
            )
        );

        $customConfigs = apply_filters('integrationRedeGetCustomConfigs', $this->form_fields, array(
            'installment_interest' => $this->get_option('installment_interest'),
            'max_parcels_number' => $this->get_option('max_parcels_number'),
        ), $this->id);

        if ( ! empty($customConfigs)) {
            $this->form_fields = array_merge($this->form_fields, $customConfigs);
        }
    }

    protected function getCheckoutForm($order_total = 0): void {
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
        $installments = array();
        $customLabel = null;
        $defaults = array(
            'min_value' => str_replace(',', '.', $this->get_option( 'min_parcels_value' )),
            'max_parcels' => $this->get_option( 'max_parcels_number' ),
        );

        $installments_result = wp_parse_args( apply_filters( 'integration_rede_installments', $defaults ), $defaults );

        $min_value = (float) $installments_result['min_value'];
        $max_parcels = (int) $installments_result['max_parcels'];

        for ( $i = 1; $i <= $max_parcels; ++$i ) {
            if ( ( $order_total / $i ) < $min_value ) {
                break;
            }
            $interest = round((float) $this->get_option( $i . 'x' ), 2);
            $label = sprintf( '%dx de %s', $i, wp_strip_all_tags( wc_price( $order_total / $i ) ) );
            if ($this->get_option('installment_interest') == 'yes') {
                $customLabel = apply_filters('integrationRedeGetInterest', $order_total, $interest, $i, 'label', $this);
            }

            if (gettype($customLabel) === 'string' && $customLabel) {
                $label = $customLabel;
            }

            $installments[] = array(
                'num' => $i,
                'label' => $label,
            );
        }

        return $installments;
    }

    public function process_payment($orderId) {
        if ( ! wp_verify_nonce($_POST['maxipago_card_nonce'], 'maxipagoCardNonce')) {
            return array(
                'result' => 'fail',
                'redirect' => '',
            );
        }

        $order = wc_get_order( $orderId );
        $order_total = $order->get_total();
        $woocommerceCountry = get_option('woocommerce_default_country');
        // Extraindo somente o país da string
        $countryParts = explode(':', $woocommerceCountry);
        $countryCode = $countryParts[0];

        $merchantId = sanitize_text_field($this->get_option('merchant_id'));
        $companyName = sanitize_text_field($this->get_option('company_name'));
        $merchantKey = sanitize_text_field($this->get_option('merchant_key'));
        $capture = sanitize_text_field($this->get_option('auto_capture')) == 'no' ? 'auth' : 'sale';
        $referenceNum = uniqid('order_', true);

        $installments = isset( $_POST['maxipago_credit_installments'] ) ?
        absint( sanitize_text_field($_POST['maxipago_credit_installments']) ) : 1;

        $interest = round((float) $this->get_option( $installments . 'x' ), 2);
        if ($this->get_option('installment_interest') == 'yes') {
            $order_total = apply_filters('integrationRedeGetInterest', $order_total, $interest, $interest, 'total');
        }

        $creditExpiry = sanitize_text_field($_POST['maxipago_credit_expiry']);

        if (strpos($creditExpiry, '/') !== false) {
            $expiration = explode( '/', $creditExpiry );
        } else {
            $expiration = array(
                substr($creditExpiry, 0, 2),
                substr($creditExpiry, -2, 2),
            );
        }
        if ($_POST['billing_cpf'] === '') {
            $_POST['billing_cpf'] = $_POST['billing_cnpj'];
        }
        if ($_POST['maxipago_credit_card_cpf']) {
            $_POST['billing_cpf'] = $_POST['maxipago_credit_card_cpf'];
        }
        $clientData = array(
            'billing_cpf' => sanitize_text_field( $_POST['billing_cpf'] ),
            'billing_name' => sanitize_text_field( $_POST['billing_address_1'] . ' ' . $_POST['billing_address_1']),
            'billing_address_1' => sanitize_text_field( $_POST['billing_address_1'] ),
            'billing_district' => sanitize_text_field( $_POST['billing_neighborhood'] ),
            'billing_city' => sanitize_text_field( $_POST['billing_city'] ),
            'billing_state' => sanitize_text_field( $_POST['billing_state'] ),
            'billing_postcode' => sanitize_text_field( $_POST['billing_postcode'] ),
            'billing_phone' => sanitize_text_field( $_POST['billing_phone'] ),
            'billing_email' => sanitize_text_field( $_POST['billing_email'] ),
            'currency_code' => get_option('woocommerce_currency'),
            'country' => $countryCode,
        );

        $cardData = array(
            'card_number' => preg_replace( '/[^\d]/', '', sanitize_text_field( $_POST['maxipago_credit_number'] ) ),
            'card_expiration_month' => sanitize_text_field( $expiration[0] ),
            'card_expiration_year' => $this->normalize_expiration_year( sanitize_text_field( $expiration[1] ) ),
            'card_cvv' => sanitize_text_field( $_POST['maxipago_credit_cvc'] ),
            'card_holder' => sanitize_text_field( $_POST['maxipago_credit_holder_name'] ),
            'card_installments' => sanitize_text_field( $_POST['maxipago_credit_installments'] ),
        );

        try {
            $environment = $this->get_option('environment');

            $valid = $this->validate_card_number( $cardData['card_number'] );
            if ( false === $valid ) {
                throw new Exception( __( 'Please enter a valid credit card number', 'woo-rede' ) );
            }

            $valid = $this->validate_card_fields( $_POST );
            if ( false === $valid ) {
                throw new Exception(__('One or more invalid fields', 'woo-rede'), 500);
            }

            $valid = $this->validate_installments( $_POST, $order->get_total() );
            if ( false === $valid ) {
                throw new Exception( __( 'Invalid number of installments', 'woo-rede' ) );
            }

            if ( ! $this->validateCpfCnpj($clientData['billing_cpf'])) {
                throw new Exception(__("Please enter a valid cpf number", 'woo-rede'));
            }
            if ('production' === $environment) {
                $apiUrl = 'https://api.maxipago.net/UniversalAPI/postXML';
                $processorID = '1';
            } else {
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
                                <customerIdExt>" . $clientData['billing_cpf'] . "</customerIdExt>
                                <billing>
                                    <name>" . $clientData['billing_name'] . "</name>
                                    <address>" . $clientData['billing_address_1'] . "</address>
                                    <district>" . $clientData['billing_district'] . "</district>
                                    <city>" . $clientData['billing_city'] . "</city>
                                    <state>" . $clientData['billing_state'] . "</state>
                                    <postalcode>" . $clientData['billing_postcode'] . "</postalcode>
                                    <country>" . $clientData['country'] . "</country>
                                    <phone>" . $clientData['billing_phone'] . "</phone>
                                    <companyName>$companyName</companyName>
                                </billing>
                                <transactionDetail>
                                    <payType>
                                        <creditCard>
                                            <number>" . $cardData['card_number'] . "</number>
                                            <expMonth>" . $cardData['card_expiration_month'] . "</expMonth>
                                            <expYear>" . $cardData['card_expiration_year'] . "</expYear>
                                            <cvvNumber>" . $cardData['card_cvv'] . "</cvvNumber>
                                        </creditCard>
                                    </payType>
                                </transactionDetail>
                                <payment>
                                    <chargeTotal>" . $order_total . "</chargeTotal>
                                    <currencyCode>" . $clientData['currency_code'] . "</currencyCode>
                                    <creditInstallment>
                                        <numberOfInstallments>" . $installments . "</numberOfInstallments>
                                        <chargeInterest>N</chargeInterest>
                                    </creditInstallment>
                                </payment>
                            </$capture>
                        </order>
                    </transaction-request>";

            $args = array(
                'body' => $xmlData,
                'headers' => array(
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

            if ("APPROVED" == $xml_decode['processorMessage']) {
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
                $order->update_meta_data( '_wc_maxipago_total_amount', $order_total );
                if ('sale' == $capture) {
                    $order->update_meta_data( '_wc_rede_captured', true );
                    $order->update_status('processing');
                    apply_filters("integrationRedeChangeOrderStatus", $order, $this);
                }
                if ('auth' == $capture) {
                    $order->update_meta_data( '_wc_rede_captured', false );
                    $order->update_status('on-hold');
                }
            }
            if ( 'yes' == $this->debug ) {
                $this->log->log('info', $this->id, array(
                    'transaction' => $xml,
                    'order' => array(
                        'orderId' => $orderId,
                        'amount' => $order_total,
                        'status' => $order->get_status()
                    ),
                    'installments' => $installments
                ));
            }

            if ("INVALID REQUEST" == $xml_decode['responseMessage']) {
                throw new Exception($xml_decode['errorMessage']);
            }
            //Caso não exista nenhuma das Message, o Merchant ID ou Merchant Key estão invalidos
            if ( ! isset($xml_decode['processorMessage']) && ! isset($xml_decode['processorMessage'])) {
                throw new Exception(__("Merchant ID or Merchant Key is invalid!", 'woo-rede'));
            }

            $order->save();
        } catch ( Exception $e ) {
            $this->add_error( $e->getMessage() );

            return array(
                'result' => 'fail',
                'redirect' => '',
            );
        }

        return array(
            'result' => 'success',
            'redirect' => $this->get_return_url( $order ),
        );
    }

    public function process_refund( $order_id, $amount = null, $reason = '' ) {
        $order = new WC_Order( $order_id );
        $totalAmount = $order->get_meta('_wc_maxipago_total_amount');
        $environment = $this->get_option('environment');
        $orderId = $order->get_meta('_wc_maxipago_transaction_id');
        $referenceNum = $order->get_meta('_wc_maxipago_transaction_reference_num');
        $merchantId = sanitize_text_field($this->get_option('merchant_id'));
        $merchantKey = sanitize_text_field($this->get_option('merchant_key'));

        if ( empty( $order->get_meta( '_wc_maxipago_transaction_canceled' ) ) ) {
            $amount = wc_format_decimal( $amount );

            try {
                if ($order->get_total() == $amount) {
                    $amount = $totalAmount;
                }

                if ('production' === $environment) {
                    $apiUrl = 'https://api.maxipago.net/UniversalAPI/postXML';
                } else {
                    $apiUrl = 'https://testapi.maxipago.net/UniversalAPI/postXML';
                }

                $xmlData = "<?xml version='1.0' encoding='UTF-8'?>
                    <transaction-request>
                        <version>3.1.1.15</version>
                        <verification>
                            <merchantId>$merchantId</merchantId>
                            <merchantKey>$merchantKey</merchantKey>
                        </verification>
                        <order>
                            <return>
                                <orderID>$orderId</orderID>
                                <referenceNum>$referenceNum</referenceNum>
                                <payment>
                                    <chargeTotal>$amount</chargeTotal>
                                </payment>
                            </return>
                        </order>
                    </transaction-request>
                ";

                $args = array(
                    'body' => $xmlData,
                    'headers' => array(
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

                if ("APPROVED" == $xml_decode['processorMessage']) {
                    update_post_meta( $order_id, '_wc_maxipago_transaction_refund_id', $xml_decode['transactionID'] );
                    update_post_meta( $order_id, '_wc_maxipago_transaction_cancel_id', $xml_decode['transactionID'] );
                    update_post_meta( $order_id, '_wc_maxipago_transaction_canceled', true );
                    $order->add_order_note( esc_attr__( 'Refunded:', 'woo-rede' ) . wc_price( $amount ) );
                }

                if ("INVALID REQUEST" == $xml_decode['responseMessage']) {
                    throw new Exception($xml_decode['errorMessage']);
                }

                $order->save();

                $this->log->log('info', $this->id, array(
                    'order' => array(
                        'amount' => $amount,
                        'totalAmount' => $totalAmount,
                        'totalOrder' => $order->get_total(),
                    ),
                ));
            } catch ( Exception $e ) {
                return new WP_Error( 'rede_refund_error', sanitize_text_field( $e->getMessage() ) );
            }

            return true;
        }

        return false;
    }

    public function validateCpfCnpj($cpfCnpj)
    {
        // Remove caracteres especiais
        $cpfCnpj = preg_replace('/[^0-9]/', '', $cpfCnpj);

        // Verifica se é CPF
        if (strlen($cpfCnpj) === 11) {
            // Verifica se todos os dígitos são iguais
            if (preg_match('/(\d)\1{10}/', $cpfCnpj)) {
                return false;
            }

            // Calcula o primeiro dígito verificador
            $sum = 0;
            for ($i = 0; $i < 9; $i++) {
                $sum += intval($cpfCnpj[$i]) * (10 - $i);
            }
            $digit1 = ($sum % 11 < 2) ? 0 : 11 - ($sum % 11);

            // Calcula o segundo dígito verificador
            $sum = 0;
            for ($i = 0; $i < 10; $i++) {
                $sum += intval($cpfCnpj[$i]) * (11 - $i);
            }
            $digit2 = ($sum % 11 < 2) ? 0 : 11 - ($sum % 11);

            // Verifica se os dígitos verificadores estão corretos
            if ($cpfCnpj[9] == $digit1 && $cpfCnpj[10] == $digit2) {
                return true;
            } else {
                return false;
            }
        }
        // Verifica se é CNPJ
        elseif (strlen($cpfCnpj) === 14) {
            // Verifica se todos os dígitos são iguais
            if (preg_match('/(\d)\1{13}/', $cpfCnpj)) {
                return false;
            }

            // Calcula o primeiro dígito verificador
            $sum = 0;
            $weights = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
            for ($i = 0; $i < 12; $i++) {
                $sum += intval($cpfCnpj[$i]) * $weights[$i];
            }
            $digit1 = ($sum % 11 < 2) ? 0 : 11 - ($sum % 11);

            // Calcula o segundo dígito verificador
            $sum = 0;
            $weights = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
            for ($i = 0; $i < 13; $i++) {
                $sum += intval($cpfCnpj[$i]) * $weights[$i];
            }
            $digit2 = ($sum % 11 < 2) ? 0 : 11 - ($sum % 11);

            // Verifica se os dígitos verificadores estão corretos
            if ($cpfCnpj[12] == $digit1 && $cpfCnpj[13] == $digit2) {
                return true;
            } else {
                return false;
            }
        }

        return false;
    }

    public function displayMeta( $order ): void {
        if ( $order->get_payment_method() === 'maxipago_credit' ) {
            $metaKeys = array(
                '_wc_maxipago_transaction_environment' => esc_attr__( 'Environment', 'woo-rede' ),
                '_wc_maxipago_transaction_return_message' => esc_attr__( 'Return Message', 'woo-rede' ),
                '_wc_maxipago_transaction_id' => esc_attr__( 'Transaction ID', 'woo-rede' ),
                '_wc_maxipago_transaction_nsu' => esc_attr__( 'Nsu', 'woo-rede' ),
                '_wc_maxipago_transaction_authorization_code' => esc_attr__( 'Authorization Code', 'woo-rede' ),
                '_wc_maxipago_transaction_bin' => esc_attr__( 'Bin', 'woo-rede' ),
                '_wc_maxipago_transaction_last4' => esc_attr__( 'Last 4', 'woo-rede' ),
                '_wc_maxipago_transaction_installments' => esc_attr__( 'Installments', 'woo-rede' ),
                '_wc_maxipago_transaction_holder' => esc_attr__( 'Cardholder', 'woo-rede' ),
                '_wc_maxipago_transaction_expiration' => esc_attr__( 'Card Expiration', 'woo-rede' ),
                '_wc_maxipago_transaction_reference_num' => esc_attr__( 'Reference Number', 'woo-rede' )
            );

            $this->generateMetaTable( $order, $metaKeys, 'Maxipago');
        }
    }

    public function checkoutScripts(): void {
        $plugin_url = plugin_dir_url( LknIntegrationRedeForWoocommerceWcRede::FILE ) . '../';
        if ($this->get_option('enabled_fix_load_script') === 'yes') {
            wp_enqueue_script( 'fixInfiniteLoading-js', $plugin_url . 'Public/js/fixInfiniteLoading.js', array(), '1.0.0', true );
        }

        if ( ! is_checkout() ) {
            return;
        }

        if ( ! $this->is_available() ) {
            return;
        }

        wp_enqueue_style( 'wc-rede-checkout-webservice' );

        wp_enqueue_style( 'card-style', $plugin_url . 'Public/css/card.css', array(), '1.0.0', 'all' );
        wp_enqueue_style( 'select-style', $plugin_url . 'Public/css/lknIntegrationRedeForWoocommerceSelectStyle.css', array(), '1.0.0', 'all' );
        wp_enqueue_style( 'woo-maxipago-style', $plugin_url . 'Public/css/maxipago/styleMaxipagoCredit.css', array(), '1.0.0', 'all' );

        wp_enqueue_script( 'woo-maxipago-js', $plugin_url . 'Public/js/creditCard/maxipago/wooMaxipagoCredit.js', array(), '1.0.0', true );
        wp_enqueue_script( 'woo-rede-animated-card-jquery', $plugin_url . 'Public/js/jquery.card.js', array('jquery', 'woo-maxipago-js'), '2.5.0', true );

        wp_localize_script( 'woo-maxipago-js', 'wooMaxipago', array(
            'debug' => defined( 'WP_DEBUG' ) && WP_DEBUG,
        ));

        apply_filters('integrationRedeSetCustomCSSPro', get_option('woocommerce_maxipago_credit_settings')['custom_css_short_code'] ?? false);
    }

    public function getMerchantAuth() {
        $plugin_url = plugin_dir_url( LknIntegrationRedeForWoocommerceWcRede::FILE ) . '../';

        return array(
            'merchantId' => $this->get_option('merchant_id'),
            'merchantKey' => $this->get_option('merchant_key'),
            'environment' => $this->get_option('environment')
        );
        wp_enqueue_script( 'woo-maxipago-js', $plugin_url . 'Public/js/creditCard/maxipago/wooMaxipagoCredit.js', array(), '1.0.0', true );
        wp_enqueue_script( 'woo-rede-animated-card-jquery', $plugin_url . 'Public/js/jquery.card.js', array('jquery', 'woo-maxipago-js'), '2.5.0', true );

        wp_localize_script( 'woo-maxipago-js', 'wooMaxipago', array(
            'debug' => defined( 'WP_DEBUG' ) && WP_DEBUG,
        ));
    }
}
?>