<?php
namespace Lkn\IntegrationRedeForWoocommerce\Includes;

use Exception;
use WC_Logger;
use WC_Order;
use WC_Payment_Gateway;

abstract class LknIntegrationRedeForWoocommerceWcRedeAbstract extends WC_Payment_Gateway {
    public $debug = 'no';
    public $auto_capture = true;
    public $min_parcels_value = 0;
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

    final public function get_valid_value( $value ) {
        return preg_replace( '/[^\d\.]+/', '', str_replace( ',', '.', $value ) );
    }

    final public function get_api_return_url( $order ) {
        global $woocommerce;

        $url = $woocommerce->api_request_url( get_class( $this ) );

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

    final public function get_logger() {
        if ( class_exists( 'WC_Logger' ) ) {
            return new WC_Logger();
        } else {
            global $woocommerce;

            return $woocommerce->logger();
        }
    }

    final public function order_items_payment_details( $items, $order ) {
        $order_id = $order->get_id();
        if ( $order->get_payment_method() === $this->id ) {
            $tid = get_post_meta( $order_id, '_wc_rede_transaction_id', true );
            $authorization_code = get_post_meta( $order_id, '_wc_rede_transaction_authorization_code', true );
            $installments = get_post_meta( $order_id, '_wc_rede_transaction_installments', true );
            $last = array_pop( $items );
            $items['payment_return'] = array(
                'label' => esc_attr__( 'Payment:', 'integration-rede-for-woocommerce' ),
                'value' => sprintf(
                    // translators: %1$s is the Order ID, %2$s is the number of installments, %3$s is the Transaction Id.
                    __( '<strong>Order ID</strong>: %1$s<br /><strong>Installments</strong>: %2$s<br /><strong>Transaction Id</strong>: %3$s<br />', 'integration-rede-for-woocommerce' ),
                    //'value' => sprintf('<strong>Order ID</strong>: %s<br /><strong>Installments</strong>: %s<br /><strong>Transaction Id</strong>: %s<br />',
                    $order_id,
                    $installments,
                    $tid
                ),
            );
			
            // translators: %s is the name of the plugin required for this one to work.
            $items['payment_return']['value'] .= sprintf( __( '<strong>Autorization Code</strong>: %s', 'integration-rede-for-woocommerce' ), $authorization_code );

            $items[] = $last;
        }

        return $items;
    }

    final public function get_payment_method_name( $slug ) {
        $methods = 'rede';

        if ( isset( $methods[ $slug ] ) ) {
            return $methods[ $slug ];
        }

        return $slug;
    }

    final public function payment_fields(): void {
        if ( $description = $this->get_description() ) {
            echo wp_kses_post( wpautop( $description ) );
        }
	
        $this->getCheckoutForm( $this->get_order_total() );
    }	

    abstract protected function getCheckoutForm( $order_total = 0);

    final public function get_order_total() {
        global $woocommerce;

        $order_total = 0;

        if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.1', '>=' ) ) {
            $order_id = absint( get_query_var( 'order-pay' ) );
        } else {
            $order_id = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : 0;
        }

        if ( 0 < $order_id ) {
            $order = new WC_Order( $order_id );
            $order_total = (float) $order->get_total();
        } elseif ( 0 < $woocommerce->cart->total ) {
            $order_total = (float) $woocommerce->cart->total;
        }

        return $order_total;
    }

    final public function consult_order( $order, $id, $tid, $status ): void {
        $transaction = $this->api->do_transaction_consultation( $tid );

        $this->process_order_status( $order, $transaction, esc_attr_e( 'automatic check', 'integration-rede-for-woocommerce' ) );
    }

    /**
     * @param $order
     * @param \Rede\Transaction $transaction
     * @param string $note
     */
    final public function process_order_status( $order, $transaction, $note = '' ): void {
        $status_note = sprintf( 'Rede[%s]', $transaction->getReturnMessage() );

        $order->add_order_note( $status_note . ' ' . $note );

        if ( $transaction->getReturnCode() == '00' ) {
            if ( $transaction->getCapture() ) {
                $order->update_status('processing');
                apply_filters("lknRedeForWoocommerceProUpdatePayment", $order->get_id());
            } else {
                $order->update_status( 'on-hold' );
                wc_reduce_stock_levels( $order->get_id() );
            }
        } else {
            $order->update_status( esc_attr_e( 'failed', 'integration-rede-for-woocommerce' ), $status_note );
            $order->update_status( esc_attr_e( 'cancelled', 'integration-rede-for-woocommerce' ), $status_note );
        }

        WC()->cart->empty_cart();
    }

    final public function thankyou_page( $order_id ): void {
        $order = new WC_Order( $order_id );
	
        if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.1', '>=' ) ) {
            $order_url = $order->get_view_order_url();
        } else {
            // FIXME - This function is depreciated and needs to be updated see alternative for woocommerce_get_page_id
            $order_url = add_query_arg( 'order', $order_id, get_permalink( woocommerce_get_page_id( 'view_order' ) ) );
        }

        if (	$order->get_status() == esc_attr__( 'on-hold', 'integration-rede-for-woocommerce' ) ||
        		$order->get_status() == esc_attr__( 'processing', 'integration-rede-for-woocommerce' ) ||
        		$order->get_status() == esc_attr__( 'completed', 'integration-rede-for-woocommerce' )) {
            echo '<div class="woocommerce-message">' . esc_attr__( 'Your order is already being processed. For more information', 'integration-rede-for-woocommerce' ). ' ' . '<a href="' . esc_url( $order_url ) . '" class="button" style="display: block !important; visibility: visible !important;">' . esc_attr__( 'see order details', 'integration-rede-for-woocommerce' ) . '</a><br /></div>';
        } else {
            echo '<div class="woocommerce-info">' . esc_attr__( 'For more details on your order, please visit', 'integration-rede-for-woocommerce' ). ' ' . '<a href="' . esc_url( $order_url ) . '">' . esc_attr__( 'order details page', 'integration-rede-for-woocommerce' ) . '</a></div>';
        }
    }

    protected function validate_card_number( $cardNumber ) {
        $cardNumber_checksum = '';
        foreach ( str_split( strrev( preg_replace( '/[^\d]/', '', $cardNumber ) ) ) as $i => $d ) {
            $cardNumber_checksum .= $i % 2 !== 0 ? $d * 2 : $d;
        }

        if ( array_sum( str_split( $cardNumber_checksum ) ) % 10 !== 0 ) {
            throw new Exception( esc_attr__( 'Please enter a valid credit card number', 'integration-rede-for-woocommerce' ) );
        }

        return true;
    }

    protected function validate_card_fields( $posted ) {
        if ( ! isset( $posted[ $this->id . '_holder_name' ] ) || '' === $posted[ $this->id . '_holder_name' ] ) {
            throw new Exception( esc_attr__( 'Please enter cardholder name', 'integration-rede-for-woocommerce' ) );
        }
		
        if ( preg_replace(
            '/[^a-zA-Z\s]/',
            '',
            $posted[ $this->id . '_holder_name' ]
        ) != $posted[ $this->id . '_holder_name' ] ) {
            throw new Exception( esc_attr__( 'Cardholder name can only contain letters', 'integration-rede-for-woocommerce' ) );
        }

        if ( ! isset( $posted[ $this->id . '_expiry' ] ) || '' === $posted[ $this->id . '_expiry' ] ) {
            throw new Exception( esc_attr__( 'Please enter card expiration date', 'integration-rede-for-woocommerce' ) );
        }

        //if user filled expiry date with 3 digits,
        // throw an exception and let him/her/they know.
        if ( isset( $posted[ $this->id . '_expiry' ][2] ) && ! isset( $posted[ $this->id . '_expiry' ][3] ) ) {
            throw new Exception( esc_attr__( 'Expiration date must contain 2 or 4 digits', 'integration-rede-for-woocommerce' ) );
        }
        if ( strtotime(
            preg_replace(
                '/(\d{2})\s*\/\s*(\d{4})/',
                '$2-$1-01',
                $this->normalize_expiration_date( $posted[ $this->id . '_expiry' ] )
            )
        ) < strtotime( gmdate( 'Y-m' ) . '-01' ) ) {
            throw new Exception( esc_attr__( 'Card expiration date must be future.', 'integration-rede-for-woocommerce' ) );
        }

        if ( ! isset( $posted[ $this->id . '_cvc' ] ) || '' === $posted[ $this->id . '_cvc' ] ) {
            throw new Exception( esc_attr__( 'Please enter card security code', 'integration-rede-for-woocommerce' ) );
        }

        if ( preg_replace( '/[^0-9]/', '', $posted[ $this->id . '_cvc' ] ) != $posted[ $this->id . '_cvc' ] ) {
            throw new Exception( esc_attr__( 'Security code must contain only numbers', 'integration-rede-for-woocommerce' ) );
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
    protected function normalize_expiration_date( $date ) {
        // Check the length of the string. This way of checking length is faster.
        // see https://coderwall.com/p/qgeuna/php-string-length-the-right-way
        if ( ! isset( $date[7] ) ) {
            $date = str_replace( '/ ', '/ 20', $date );
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
    protected function normalize_expiration_year( $year ) {
        if ( ! isset( $year[3] ) ) {
            $year = '20' . $year;
        }

        return $year;
    }

    final public function add_error( $message ): void {
        global $woocommerce;

        $title = '<strong>' . esc_html( $this->title ) . ':</strong> ';

        if ( function_exists( 'wc_add_notice' ) ) {
            $message = wp_kses( $message, array() );
            throw new Exception(wp_kses_post("{$title} {$message}"));
        } else {
            $woocommerce->add_error( $title . $message );
        }
    }

    protected function validate_installments( $posted, $order_total ) {
        if ( ! isset( $posted['rede_credit_installments'] ) ) {
            $posted['rede_credit_installments'] = 1;
        }

        if ( 1 == $posted['rede_credit_installments'] ) {
            return true;
        }
		
        if ( ! isset( $posted['rede_credit_installments'] ) || '' === $posted['rede_credit_installments'] ) {
            throw new Exception( esc_attr__( 'Please enter the number of installments', 'integration-rede-for-woocommerce' ) );
        }

        $installments = absint( $posted['rede_credit_installments'] );
        $min_value = $this->get_option( 'min_parcels_value' );
        $max_parcels = $this->get_option( 'max_parcels_number' );

        if ( $installments > $max_parcels || ( ( 0 != $min_value ) && ( ( $order_total / $installments ) < $min_value ) ) ) {
            throw new Exception( esc_attr__( 'Invalid number of installments', 'integration-rede-for-woocommerce' ) );
        }

        return true;
    }

    final public function generateMetaTable( $order, $metaKeys, $title): void {
        ?>
<h3><?php esc_attr_e( $title, 'integration-rede-for-woocommerce' ); ?>
</h3>
<table>
	<tbody>
		<?php
        		array_map( function( $meta_key, $label ) use ( $order ): void {
        		    $meta_value = $order->get_meta( $meta_key );
        		    if ( ! empty( $meta_value ) ) :
        		        ?>
		<tr>
			<td><?php echo esc_attr( $label ); ?></td>
			<td><?php echo esc_attr( $meta_value ); ?></td>
		</tr>
		<?php
        		    endif;
        		}, array_keys( $metaKeys ), $metaKeys );
        ?>
	</tbody>
</table>
<?php
    }
}