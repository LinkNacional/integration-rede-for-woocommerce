<?php
// Certifique-se de que você está definindo o namespace corretamente
namespace Lkn\IntegrationRedeForWoocommerce\Includes;

// Certifique-se de que você importou a classe WC_Payment_Gateway
use WC_Payment_Gateway;

/**
 * Classe para o método de pagamento de teste.
 */
class LknIntegrationRedeForWoocommerceTestPayment extends WC_Payment_Gateway {

    /**
     * Construtor da classe.
     */
    public function __construct() {
        // Define as informações básicas do método de pagamento
        $this->id                 = 'lkn_test_payment';
        $this->icon               = ''; // URL do ícone do método de pagamento
        $this->has_fields         = true;
        $this->method_title       = __( 'LKN Test Payment', 'lkn-integration-rede-for-woocommerce' );
        $this->method_description = __( 'This is a test payment method.', 'lkn-integration-rede-for-woocommerce' );

        // Define os campos do formulário de configuração do método de pagamento
        $this->init_form_fields();

        // Define as configurações padrão do método de pagamento
        $this->init_settings();

        // Define os eventos (hooks) relacionados às funcionalidades do método de pagamento
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
    }

    /**
     * Define os campos do formulário de configuração do método de pagamento.
     */
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => __( 'Enable/Disable', 'lkn-integration-rede-for-woocommerce' ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable LKN Test Payment', 'lkn-integration-rede-for-woocommerce' ),
                'default' => 'yes',
            ),
        );
    }

    /**
     * Processa o pagamento.
     *
     * @param int $order_id ID do pedido.
     * @return array
     */
    public function process_payment( $order_id ) {
        // Aqui você pode adicionar lógica para processar o pagamento, como enviar para um gateway de pagamento real
        $order = wc_get_order( $order_id );

        // Marca o pedido como "processado" e completa-o
        $order->payment_complete();

        // Retorna uma array para redirecionar o usuário para a página de sucesso
        return array(
            'result'   => 'success',
            'redirect' => $this->get_return_url( $order ),
        );
    }
}
