<?php

namespace Lknwoo\IntegrationRedeForWoocommerce\Includes;

use Exception;
use WC_Logger;
use WC_Order;
use WC_Payment_Gateway;

abstract class LknIntegrationRedeForWoocommerceWcRedeAbstract extends WC_Payment_Gateway
{
    public $debug = 'no';
    public $auto_capture = true;
    public $min_parcels_value = 5;
    public $max_parcels_number = 12;
    public $configs = array();
    public $api = null;
    public $environment;
    public $pv;
    public $token;
    public $soft_descriptor;
    public $partner_module;
    public $partner_gateway;
    public $log;
    public $merchant_id;
    public $merchant_key;

    /**
     * Fields validation.
     *
     * @return bool
     */
    public function validate_fields()
    {
        return true;
    }

    /**
     * Verify if WooCommerce notice exists before adding.
     *
     * @param string $message
     * @param string $type
     */
    private function add_notice_once($message, $type): void
    {
        if (! wc_has_notice($message, $type)) {
            wc_add_notice($message, $type);
        }
    }

    final public function get_valid_value($value)
    {
        return preg_replace('/[^\d\.]+/', '', str_replace(',', '.', $value));
    }

    final public function get_api_return_url($order)
    {
        global $woocommerce;

        $url = $woocommerce->api_request_url(get_class($this));

        return urlencode(
            add_query_arg(
                array(
                    'key' => $order->order_key,
                    'order' => $order->get_id(),
                ),
                $url
            )
        );
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

    final public function order_items_payment_details($items, $order)
    {
        $order_id = $order->get_id();
        if ($order->get_payment_method() === $this->id) {
            $tid = $order->get_meta('_wc_rede_transaction_id');
            $authorization_code = $order->get_meta('_wc_rede_transaction_authorization_code');
            $installments = $order->get_meta('_wc_rede_transaction_installments');

            $last = array_pop($items);

            $items['orderId'] = array(
                'label' => 'ID do Pedido',
                'value' => $order_id,
            );
            $items['transactionId'] = array(
                'label' => 'ID da Transação',
                'value' => $tid,
            );
            $items['authorizationCode'] = array(
                'label' => 'Código de Autorização',
                'value' => $authorization_code,
            );
            
            // Lógica específica para rede_debit: mostrar tipo de cartão e parcelas conforme o tipo
            if ($this->id === 'rede_debit') {
                $card_type = $order->get_meta('_wc_rede_card_type') ?: 'debit';
                $saved_installments = $order->get_meta('_wc_rede_installments') ?: 1;
                
                $card_type_text = $card_type === 'credit' ? 'Crédito' : 'Débito';
                $items['cardType'] = array(
                    'label' => 'Tipo do cartão',
                    'value' => $card_type_text,
                );
                
                // Só mostra parcelas se for crédito
                if ($card_type === 'credit') {
                    if ($saved_installments == 1) {
                        $installment_text = 'Pagamento à vista';
                    } else {
                        $order_total = $order->get_total();
                        $installment_value = $order_total / $saved_installments;
                        $installment_text = sprintf('%dx de %s', $saved_installments, wc_price($installment_value));
                    }
                    
                    $items['installments'] = array(
                        'label' => 'Parcelamento',
                        'value' => $installment_text,
                    );
                }
            } else {
                // Para outros gateways, usar a lógica original
                if ($installments) {
                    $items['installments'] = array(
                        'label' => 'Parcelamento',
                        'value' => $installments,
                    );
                }
            }

            $items[] = $last;
        }

        return $items;
    }

    final public function get_payment_method_name($slug)
    {
        $methods = 'rede';

        if (isset($methods[$slug])) {
            return $methods[$slug];
        }

        return $slug;
    }

    final public function payment_fields(): void
    {
        if ($description = $this->get_description()) {
            echo wp_kses_post(wpautop($description));
        }

        $this->getCheckoutForm($this->get_cart_subtotal_without_taxes());
    }

    abstract protected function getCheckoutForm($order_total = 0);

    final public function get_order_total()
    {
        global $woocommerce;

        $order_total = 0;

        if (defined('WC_VERSION') && version_compare(WC_VERSION, '2.1', '>=')) {
            $order_id = absint(get_query_var('order-pay'));
        } else {
            $order_id = isset($_GET['order_id']) ? absint(wp_unslash($_GET['order_id'])) : 0;
        }

        if (0 < $order_id) {
            $order = new WC_Order($order_id);
            $order_total = (float) $order->get_total();
        } elseif (0 < $woocommerce->cart->total) {
            $order_total = (float) $woocommerce->cart->total;
        }

        return $order_total;
    }

    final public function get_cart_subtotal_without_taxes()
    {
        global $woocommerce;

        $subtotal = 0;

        if (defined('WC_VERSION') && version_compare(WC_VERSION, '2.1', '>=')) {
            $order_id = absint(get_query_var('order-pay'));
        } else {
            $order_id = isset($_GET['order_id']) ? absint(wp_unslash($_GET['order_id'])) : 0;
        }

        if (0 < $order_id) {
            $order = new WC_Order($order_id);
            // Para pedidos existentes, calcula subtotal + frete + impostos - desconto de cupons + taxas
            $subtotal = (float) $order->get_subtotal() + (float) $order->get_shipping_total() + (float) $order->get_total_tax() - (float) $order->get_discount_total();
            
            // Adicionar taxas (fees) do pedido, excluindo fees do próprio plugin
            foreach ($order->get_fees() as $fee) {
                // Ignorar fees criados pelo próprio plugin
                if ($fee->get_name() !== __('Interest', 'rede-for-woocommerce-pro') && 
                    $fee->get_name() !== __('Discount', 'rede-for-woocommerce-pro')) {
                    $subtotal += (float) $fee->get_amount();
                }
            }
            
        } elseif (isset($woocommerce->cart) && $woocommerce->cart) {
            // Para carrinho, pega subtotal + frete + impostos - desconto de cupons + taxas
            $subtotal = (float) $woocommerce->cart->get_subtotal() + (float) $woocommerce->cart->get_shipping_total() + (float) $woocommerce->cart->get_taxes_total() - (float) $woocommerce->cart->get_discount_total();
            
            // Forçar recálculo do carrinho para garantir que as taxas estejam atualizadas
            $woocommerce->cart->calculate_totals();
            
            // Adicionar taxas (fees) do carrinho, excluindo fees do próprio plugin
            foreach ($woocommerce->cart->get_fees() as $fee) {
                // Ignorar fees criados pelo próprio plugin
                if ($fee->name !== __('Interest', 'rede-for-woocommerce-pro') && 
                    $fee->name !== __('Discount', 'rede-for-woocommerce-pro')) {
                    $subtotal += (float) $fee->amount;
                }
            }
        }

        return $subtotal;
    }

    /**
     * @param $order
     * @param mixed $transaction - Objeto de transação (format pode variar)
     * @param string $note
     */
    final public function process_order_status($order, $transaction, $note = ''): void
    {
        $status_note = sprintf('Rede[%s]', $transaction->getReturnMessage());

        $order->add_order_note($status_note . ' ' . $note);

        // Só altera o status se o pedido estiver pendente
        if ($order->get_status() === 'pending') {
            if ($transaction->getReturnCode() == '00') {
                if ($transaction->getCapture()) {
                    // Status configurável pelo usuário para pagamentos aprovados
                    $payment_complete_status = $this->get_option('payment_complete_status', 'processing');
                    $order->update_status($payment_complete_status);
                    apply_filters("integration_rede_for_woocommerce_change_order_status", $order, $this);
                } else {
                    $order->update_status('on-hold');
                    wc_reduce_stock_levels($order->get_id());
                }
            } else {
                $order->update_status('failed', $status_note);
            }
        }

        WC()->cart->empty_cart();
    }

    final public function thankyou_page($order_id): void
    {
        $order = new WC_Order($order_id);

        if (defined('WC_VERSION') && version_compare(WC_VERSION, '2.1', '>=')) {
            $order_url = $order->get_view_order_url();
        } else {
            // Legacy support for WooCommerce < 2.1
            $order_url = add_query_arg('order', $order_id, get_permalink(woocommerce_get_page_id('view_order')));
        }

        if (
            $order->get_status() == 'on-hold' ||
            $order->get_status() == 'processing' ||
            $order->get_status() == 'completed'
        ) {
            echo '<div class="woocommerce-message">' . 'Seu pedido já está sendo processado. Para mais informações' . ' ' . '<a href="' . esc_url($order_url) . '" class="button" style="display: block !important; visibility: visible !important;">' . 'veja os detalhes do pedido' . '</a><br /></div>';
        } else {
            echo '<div class="woocommerce-info">' . 'Para mais detalhes sobre seu pedido, visite' . ' ' . '<a href="' . esc_url($order_url) . '">' . 'página de detalhes do pedido' . '</a></div>';
        }
    }

    protected function validate_card_number($cardNumber)
    {
        $cardNumber_checksum = '';
        foreach (str_split(strrev(preg_replace('/[^\d]/', '', $cardNumber))) as $i => $d) {
            $cardNumber_checksum .= $i % 2 !== 0 ? $d * 2 : $d;
        }

        if (array_sum(str_split($cardNumber_checksum)) % 10 !== 0) {
            throw new Exception('Por favor, insira um número de cartão de crédito válido');
            return false;
        }

        return true;
    }

    protected function validate_card_fields($posted)
    {
        if (! isset($posted[$this->id . '_holder_name']) || '' === $posted[$this->id . '_holder_name']) {
            throw new Exception('Por favor, insira o nome do portador do cartão');
            return false;
        }

        if (preg_replace(
            '/[^a-zA-Z\s]/',
            '',
            $posted[$this->id . '_holder_name']
        ) != $posted[$this->id . '_holder_name']) {
            throw new Exception('O nome do portador do cartão só pode conter letras');
            return false;
        }

        if (! isset($posted[$this->id . '_expiry']) || '' === $posted[$this->id . '_expiry']) {
            throw new Exception('Por favor, insira a data de vencimento do cartão');
            return false;
        }

        //if user filled expiry date with 3 digits,
        // throw an exception and let him/her/they know.
        if (isset($posted[$this->id . '_expiry'][2]) && ! isset($posted[$this->id . '_expiry'][3])) {
            throw new Exception('A data de vencimento deve conter 2 ou 4 dígitos');
            return false;
        }

        if (strtotime(
            preg_replace(
                '/(\d{2})\s*\/\s*(\d{4})/',
                '$2-$1-01',
                $this->normalize_expiration_date($posted[$this->id . '_expiry'])
            )
        ) < strtotime(gmdate('Y-m') . '-01')) {
            throw new Exception('A data de vencimento do cartão deve ser futura.');
            return false;
        }

        if (! isset($posted[$this->id . '_cvc']) || '' === $posted[$this->id . '_cvc']) {
            throw new Exception('Por favor, insira o código de segurança do cartão');
            return false;
        }

        if (preg_replace('/[^0-9]/', '', $posted[$this->id . '_cvc']) != $posted[$this->id . '_cvc']) {
            throw new Exception('O código de segurança deve conter apenas números');
            return false;
        }

        return true;
    }

    /**
     * Normalize expiry date.
     *
     * Normalize expiry date by adding the '20' when the year has only 2 digits.
     *
     * @param string $date
     * @return string $date
     */
    protected function normalize_expiration_date($date)
    {
        // Check the length of the string. This way of checking length is faster.
        // see https://coderwall.com/p/qgeuna/php-string-length-the-right-way
        if (! isset($date[7])) {
            $date = str_replace('/ ', '/ 20', $date);
        }

        return $date;
    }

    /**
     * Normalize expiry year.
     *
     * Normalize expiry year by adding the '20' when the year has only 2 digits.
     *
     * @param string $year
     * @return string $year
     */
    protected function normalize_expiration_year($year)
    {
        if (! isset($year[3])) {
            $year = '20' . $year;
        }

        return $year;
    }

    final public function add_error($message): void
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

    protected function validate_installments($posted, $order_total)
    {
        if (! isset($posted['rede_credit_installments'])) {
            $posted['rede_credit_installments'] = 1;
        }

        if (1 == $posted['rede_credit_installments']) {
            return true;
        }

        if (! isset($posted['rede_credit_installments']) || '' === $posted['rede_credit_installments']) {
            throw new Exception('Por favor, insira o número de parcelas');
        }

        $installments = absint($posted['rede_credit_installments']);
        $min_value = $this->get_option('min_parcels_value');
        $max_parcels = $this->get_option('max_parcels_number');

        if ($installments > $max_parcels || ((0 != $min_value) && (($order_total / $installments) < $min_value))) {
            throw new Exception('Número de parcelas inválido');
        }

        return true;
    }

    final public function generateMetaTable($order, $metaKeys, $title): void
    {
?>
        <h3><?php esc_html($title); ?>
        </h3>
        <table>
            <tbody>
                <?php
                array_map(function ($meta_key, $label) use ($order): void {
                    $meta_value = $order->get_meta($meta_key);
                    if (! empty($meta_value)) :
                ?>
                        <tr>
                            <td><?php echo esc_attr($label); ?></td>
                            <td><?php echo esc_attr($meta_value); ?></td>
                        </tr>
                <?php
                    endif;
                }, array_keys($metaKeys), $metaKeys);
                ?>
            </tbody>
        </table>
<?php
    }
}
