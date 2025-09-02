<?php

namespace Lkn\IntegrationRedeForWoocommerce\Includes;

use DateTime;
use Exception;
use WC_Logger;
use WC_Payment_Gateway;

final class LknIntegrationRedeForWoocommerceWcPixRede extends WC_Payment_Gateway
{
    public $configs;
    public $log;
    public $debug;

    public function __construct()
    {
        $this->id = 'integration_rede_pix';
        $this->title = 'Integration Rede Pix';
        $this->has_fields = true;
        $this->method_title = esc_attr__('Pay with the Rede Pix FREE', 'woo-rede');
        $this->method_description = esc_attr__('Enables and configures payments with Rede Pix', 'woo-rede') . '<a target="_blank" href="https://www.linknacional.com.br/wordpress/woocommerce/rede/doc/">' . esc_attr__( 'Documentation', 'woo-rede' ) . '</a>';
        $this->supports = array(
            'products',
            'refunds',
        );

        // Define os campos de configuração do método de pagamento
        $this->init_form_fields();
        $this->init_settings();

        // Define as configurações do método de pagamento
        $this->title = $this->get_option('title');
        $this->log = $this->get_logger();
        $this->debug = $this->get_option('debug');
    }

    public function init_form_fields(): void
    {
        if (isset($_GET['page']) && isset($_GET['section'])) {
            if ('wc-settings' == sanitize_text_field(wp_unslash($_GET['page'])) && sanitize_text_field(wp_unslash($_GET['section'])) == $this->id) {
                wp_enqueue_script(
                    'lkn-integration-rede-for-woocommerce-endpoint',
                    plugin_dir_url(__FILE__) . '../Admin/js/lkn-integration-rede-for-woocommerce-endpoint.js',
                    array('jquery', 'wp-api'),
                    INTEGRATION_REDE_FOR_WOOCOMMERCE_VERSION,
                    false
                );
        
                wp_localize_script('lkn-integration-rede-for-woocommerce-endpoint', 'lknRedeForWoocommerceProSettings', array(
                    'endpointStatus' => get_option('LknIntegrationRedeForWoocommerceEndpointStatus', false),
                    'translations' => array(
                        'endpointSuccess' => __('Request received!', 'woo-rede'),
                        'endpointError' => __('No requests received!', 'woo-rede'),
                        'howToConfigure' => __('How to Configure', 'woo-rede'),
                    ),
                ));
                
                $this->form_fields = array(
                    'enabled' => array(
                        'title' => esc_attr__('Payment with Rede Pix', 'woo-rede'),
                        'type' => 'checkbox',
                        'label' => __('Enable', 'woo-rede'),
                        'default' => 'no'
                    ),
                    'rede' => array(
                        'title' => esc_attr__('General', 'woo-rede'),
                        'type' => 'title',
                    ),
                    'title' => array(
                        'title' => esc_attr__('Title', 'woo-rede'),
                        'type' => 'text',
                        'description' => __('The payment name that the user sees at checkout.', 'woo-rede'),
                        'default' => __('Pay with the Rede Pix', 'woo-rede'),
                        'desc_tip' => true,
                    ),
                    'description' => array(
                        'title' => __('Description', 'woo-rede'),
                        'type' => 'textarea',
                        'default' => __( 'Pay for your purchase with a pix through ', 'woo-rede' ),
                    ),
                    'endpoint' => array(
                        'title' => esc_attr__('Endpoint', 'woo-rede'),
                        'type' => 'text',
                        'description' => esc_attr__('Return URL to automatically update the status of orders paid via PIX on the Rede portal.', 'woo-rede'),
                        'desc_tip' => true,
                    ),
                    'pv' => array(
                        'title' => esc_attr__('PV', 'woo-rede'),
                        'type' => 'password',
                        'description' => esc_attr__('Enter the establishment affiliation number provided by Rede.', 'woo-rede'),
                        'desc_tip' => true,
                        'default' => '',
                    ),
                    'token' => array(
                        'title' => esc_attr__('Token', 'woo-rede'),
                        'type' => 'password',
                        'description' => esc_attr__('Enter the integration key provided by Rede.', 'woo-rede'),
                        'desc_tip' => true,
                        'default' => '',
                    ),
                    'environment' => array(
                        'title' => esc_attr__('Environment', 'woo-rede'),
                        'type' => 'select',
                        'description' => esc_attr__('Enable test or production environment.', 'woo-rede'),
                        'desc_tip' => true,
                        'class' => 'wc-enhanced-select',
                        'default' => esc_attr__('test', 'woo-rede'),
                        'options' => array(
                            'test' => esc_attr__('Tests', 'woo-rede'),
                            'production' => esc_attr__('Production', 'woo-rede'),
                        ),
                    ),
                    'fake_convert_to_brl' => array(
                        'title' => __('Currency Converter', 'woo-rede'),
                        'type' => 'checkbox',
                        'description' => __('If enabled, automatically converts the order amount to BRL when processing payment.', 'woo-rede'),
                        'desc_tip' => true,
                        'label' => __('Convert to BRL', 'woo-rede'),
                        'default' => 'no',
                        'custom_attributes' => array(
                            'readonly' => 'readonly',
                            'disabled' => 'disabled'
                        )
                    ),
                    'expiration_count' => array(
                        'title' => esc_attr__('PIX validity in hours', 'woo-rede'),
                        'type' => 'number',
                        'description' => esc_attr__('Enter the validity time of Pix in hours.', 'woo-rede'),
                        'desc_tip' => true,
                        'default' => '24',
                        'required' => true,
                        'custom_attributes' => array(
                            'min' => '1',
                            'step' => '1',
                            'readonly' => 'readonly',
                        ),
                    ),
                    'payment_complete_status' => array(
                        'title' => esc_attr__('Payment Complete Status', 'woo-rede'),
                        'type' => 'select',
                        'class' => 'wc-enhanced-select',
                        'desc_tip' => true,
                        'description' => esc_attr__('Select the order status after payment confirmation.', 'woo-rede'),
                        'options' => array(
                            'processing' => _x('Processing', 'Order status', 'woo-rede'),
                            'on-hold' => _x('On hold', 'Order status', 'woo-rede'),
                            'completed' => _x('Completed', 'Order status', 'woo-rede'),
                        ),
                        'default' => 'processing',
                        'custom_attributes' => array(
                            'disabled' => 'disabled',
                        ),
                    ),
                    'show_button' => array(
                        'title' => esc_attr__('Botão Gerar PIX', 'woo-rede'),
                        'type' => 'checkbox',
                        'label' => __('Habilitar', 'woo-rede'),
                        'desc_tip' => true,
                        'description' => esc_attr__('Exibe o botão "Finalizar e Gerar PIX" no checkout.', 'woo-rede'),
                        'default' => 'no',
                        'custom_attributes' => array(
                            'disabled' => 'disabled',
                        ),
                    ),
                    'fake_convert_to_brl' => array(
                        'title' => __('Currency Converter', 'rede-for-woocommerce-pro'),
                        'type' => 'checkbox',
                        'description' => __('If enabled, automatically converts the order amount to BRL when processing payment.', 'rede-for-woocommerce-pro'),
                        'desc_tip' => true,
                        'label' => __('Convert to BRL', 'rede-for-woocommerce-pro'),
                        'default' => 'no',
                        'custom_attributes' => array(
                            'readonly' => 'readonly',
                            'disabled' => 'disabled'
                        )
                    ),
                    'developers' => array(
                        'title' => esc_attr__('Developer', 'woo-rede'),
                        'type' => 'title',
                    ),
                    'debug' => array(
                        'title' => esc_attr__('Debug', 'woo-rede'),
                        'type' => 'checkbox',
                        'label' => esc_attr__('Enable debug logs.', 'woo-rede') . ' ' . wp_kses_post('<a href="' . esc_url(admin_url('admin.php?page=wc-status&tab=logs')) . '" target="_blank">' . __('See logs', 'woo-rede') . '</a>'),
                        'default' => 'no',
                    )
                );

                if ($this->get_option('debug') == 'yes') {
                    $this->form_fields['show_order_logs'] =  array(
                        'title' => __('Visualizar Log no Pedido', 'woo-rede'),
                        'type' => 'checkbox',
                        'label' => sprintf('Habilita visualização do log da transação dentro do pedido.', 'woo-rede'),
                        'default' => 'no',
                    );
                    $this->form_fields['clear_order_records'] =  array(
                        'title' => __('Limpar logs nos Pedidos', 'woo-rede'),
                        'type' => 'button',
                        'id' => 'validateLicense',
                        'class' => 'woocommerce-save-button components-button is-primary'
                    );
                }
            }
        }
    }

    public function payment_fields(): void
    {
        wc_get_template(
            '/pixRedePaymentPaymentFields.php',
            array(),
            'woocommerce/integration-rede/',
            plugin_dir_path(__FILE__) . 'templates/pix/'
        );
        wp_enqueue_style('integration-rede-pix-style', INTEGRATION_REDE_FOR_WOOCOMMERCE_DIR_URL . 'Public/css/rede/LknIntegrationRedeForWoocommercePaymentFields.css', array(), '1.0.0', 'all');
    }

    final public function get_logger()
    {
        if (class_exists('WC_Logger')) {
            return new WC_Logger();
        } else {
            global $woocommerce;

            return $woocommerce->logger();
        }
    }

    public function process_payment($orderId)
    {
        $order = wc_get_order($orderId);

        $valid = true;

        try {
            $pixInfos = array(
                'generated' => $order->get_meta('_wc_rede_pix_integration_transaction_pix_generated'),
                'amount' => $order->get_meta('_wc_rede_pix_integration_transaction_amount'),
            );
            $total = str_replace(".", "", $order->get_total());
            if (empty($pixInfos['generated']) || $total != $pixInfos['amount']) {
                $reference = $orderId;
                if ($total != $pixInfos['amount'] && !empty($pixInfos['amount'])) {
                    $reference = uniqid();
                }

                $pix = LknIntegrationRedeForWoocommerceWcPixHelper::getPixRede($order->get_total(), $this, $reference, $order);
                if ("25" == $pix['returnCode'] || "89" == $pix['returnCode']) {
                    throw new Exception(__('PV or Token is invalid!', 'woo-rede'));
                }
                if ("00" != $pix['returnCode']) {
                    if ('yes' == $this->debug) {
                        $this->log->log('info', $this->id, array(
                            'order' => array(
                                'requestResponse' => $pix,
                            ),
                        ));
                    }
                    throw new Exception(__('An error occurred while processing the payment.', 'woo-rede'));
                }

                $pixReference = $pix['reference'];
                $pixTid = $pix['tid'];
                $pixAmount = $pix['amount'];
                $pixQrCodeData = $pix['qrCodeResponse']['qrCodeData'];
                $pixQrCodeImage = $pix['qrCodeResponse']['qrCodeImage'];

                $order->update_meta_data('_wc_rede_pix_integration_transaction_reference', $pixReference);
                $order->update_meta_data('_wc_rede_integration_pix_transaction_tid', $pixTid);
                $order->update_meta_data('_wc_rede_pix_integration_transaction_amount', $pixAmount);
                $order->update_meta_data('_wc_rede_pix_integration_transaction_pix_code', $pixQrCodeData);
                $order->update_meta_data('_wc_rede_pix_integration_transaction_pix_generated', true);
                $order->update_meta_data('_wc_rede_pix_integration_transaction_pix_qrcode_base64', $pixQrCodeImage);

                if ('yes' == $this->debug) {
                    $this->log->log('info', $this->id, array(
                        'order' => array(
                            'pixReference' => $pixReference,
                            'pixTid' => $pixTid,
                            'pixAmount' => $pixAmount,
                            'pixQrCodeData' => $pixQrCodeData,
                            'pixQrCodeImage' => $pixQrCodeImage,
                        ),
                    ));
                }
                $order->save();
            }
        } catch (Exception $e) {
            $this->add_error($e->getMessage());
            $valid = false;
        }

        if ($valid) {
            return array(
                'result' => 'success',
                'redirect' => $this->get_return_url($order),
            );
        } else {
            return array(
                'result' => 'fail',
                'redirect' => '',
            );
        }
    }

    public function process_refund($orderId, $amount = null, $reason = '')
    {
        if (! $amount) {
            return true;
        }

        $refund = LknIntegrationRedeForWoocommerceWcPixHelper::refundPixRede($amount, $this, $orderId);
        if ('yes' == $this->debug) {
            $this->log->log('info', $this->id, array(
                'order' => array(
                    'refund' => $refund,
                ),
            ));
        }

        if ('359' == $refund['returnCode']) {
            return true;
        } else {
            throw new Exception(esc_html($refund['returnMessage']));
        }
    }

    public function add_error($message): void
    {
        global $woocommerce;

        $title = '<strong>' . esc_html($this->title) . ':</strong> ';

        if (function_exists('wc_add_notice')) {
            $message = wp_kses($message, array());
            throw new Exception(wp_kses_post("{$title} {$message}"));
        } else {
            $woocommerce->add_error($title . $message);
        }
    }

    public function displayMeta($order): void
    {
        if ($order->get_payment_method() === 'integration_rede_pix') {
            $metaKeys = array(
                '_wc_rede_pix_integration_transaction_reference' => esc_attr__('Reference Number', 'woo-rede'),
                '_wc_rede_integration_pix_transaction_tid' => esc_attr__('TID', 'woo-rede'),
            );

            LknIntegrationRedeForWoocommerceWcPixHelper::generateMetaTable($order, $metaKeys, 'Rede');
        }
    }

    public function showPix($orderWCID): void
    {
        $order = wc_get_order($orderWCID);

        if ($order->get_payment_method() == 'integration_rede_pix') {
            $orderBase64 = $order->get_meta('_wc_rede_pix_integration_transaction_pix_qrcode_base64');
            $pixCode = $order->get_meta('_wc_rede_pix_integration_transaction_pix_code');
            $filePath = INTEGRATION_REDE_FOR_WOOCOMMERCE_DIR_URL . 'Public/images/share.svg';
            $total = wc_price($order->get_total());
            $timeExpiration = $order->get_meta('_wc_rede_pix_integration_time_expiration');
            $dateTime = new DateTime($timeExpiration);
            $formattedDate = 'Vencimento: ' . $dateTime->format('d/m/Y');
            wc_get_template(
                '/paymentPixQRCode.php',
                array(
                    'donKey' => $pixCode,
                    'donQrCode' => $orderBase64,
                    'filePath' => $filePath,
                    'currencyTxt' => $total,
                    'dueDateMsg' => $formattedDate,
                    'donationId' => $order->get_id()
                ),
                'woocommerce/integration-pix/',
                plugin_dir_path(__FILE__) . 'templates/pix/'
            );
            wp_enqueue_style('integration-rede-pix-style', INTEGRATION_REDE_FOR_WOOCOMMERCE_DIR_URL . 'Public/css/rede/LknIntegrationRedeForWoocommercePix.css', array(), '1.0.0', 'all');

            if (wp_get_theme()->get('Name') == 'Divi') {
                wp_enqueue_style('integration-rede-pix-style-divi', INTEGRATION_REDE_FOR_WOOCOMMERCE_DIR_URL . 'Public/css/rede/LknIntegrationRedeForWoocommercePixDivi.css', array(), '1.0.0', 'all');
            }

            wp_enqueue_script('integration-rede-pix-js', INTEGRATION_REDE_FOR_WOOCOMMERCE_DIR_URL . 'Public/js/pix/LknIntegrationRedeForWoocommercePix.js', array(), '1.0.0', 'all');
            wp_localize_script('integration-rede-pix-js', 'currentTheme', wp_get_theme()->get('Name'));
            wp_localize_script('integration-rede-pix-js', 'phpVarsPix', array(
                'copied' => __('COPIED', 'woo-rede'),
                'copy' => __('COPY', 'woo-rede'),
                'shareError' => __('Sharing is not supported in this browser.', 'woo-rede'),
                'pixButton' => __('I have already paid the PIX', 'woo-rede'),
                'successPayment' => __('Payment successfully completed!', 'woo-rede'),
                'nextVerify' => __('Next verification in (N. attempts', 'woo-rede'),
                'shareTitle' => __('Payment via PIX', 'woo-rede')
            ));
        }
    }

    public function process_admin_options()
    {
        
        if (isset($_POST['woocommerce_integration_rede_pix_expiration_count'])) {
            $_POST['woocommerce_integration_rede_pix_expiration_count'] = '24';
        }

        if (isset($_POST['woocommerce_integration_rede_pix_payment_complete_status'])) {
            $_POST['woocommerce_integration_rede_pix_payment_complete_status'] = 'processing';
        }

        if (isset($_POST['woocommerce_integration_rede_pix_fake_convert_to_brl'])) {
            $_POST['woocommerce_integration_rede_pix_fake_convert_to_brl'] = 'no';
        }

        parent::process_admin_options();
    }
}
