<?php

namespace MEC_Woocommerce\Core\Integrations;
use \MEC_Woocommerce\Core\Helpers\Products as Helper;
// Don't load directly
if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

/**
*  Payment.
*
*  @author      Webnus <info@webnus.biz>
*  @package     Modern Events Calendar
*  @since       1.0.0
**/
class Payment extends Helper
{

    /**
    *  Instance of this class.
    *
    *  @since   1.0.0
    *  @access  public
    *  @var     MEC_Woocommerce
    */
    public static $instance;

   /**
    *  Provides access to a single instance of a module using the Singleton pattern.
    *
    *  @since   1.0.0
    *  @return  object
    */
    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    public function __construct()
    {
        if (self::$instance === null) {
            self::$instance = $this;
        }

        $this->setHooks();
    }

   /**
    *  Hooks
    *
    *  @since     1.0.0
    */
    public function setHooks()
    {
        add_action('woocommerce_checkout_order_processed', [$this, 'capture_payment'], 10, 1);
        add_action('woocommerce_new_order', [$this, 'capture_payment'], 10, 1);
        add_action('woocommerce_after_checkout_validation', [$this,'checkout_validation'], 10, 1);
    }

    public function checkout_validation($data){

        $can_add = true;
        $cart_items = @WC()->cart->get_cart();
        foreach ($cart_items as $item_id => $order_item) {

            $product_id = $order_item['product_id'];
            $product = $this->get_product($product_id, true); # Get Product
            // Check The Product is Processed
            if ($product && !get_post_meta($product->ID, 'mec_payment_complete', true)) {
                $transaction_id = get_post_meta($product->ID, 'transaction_id', true);
                // Don't Process Shop Products
                if(!$transaction_id) {
                    continue;
                }

                // Don't Process Processed Transaction
                if (get_option($transaction_id . '_MEC_payment_complete', false)) {
                    continue;
                }

                //check items
                $event_id = get_post_meta($product_id, 'event_id', true);
                $event_timestamp = get_post_meta($product_id, 'mec_date', true);
                $event_ticket_id = get_post_meta($product_id, 'ticket_id', true);
                if( !isset($checked[$event_id][$event_timestamp][$event_ticket_id]) ){

                    $addToCart = new AddToCart();
                    $can_add = $can_add && $addToCart->mec_wc_qty_update_cart_validation(
                        true,
                        null,
                        ['product_id' => $product_id],
                        0
                    );

                    $checked[$event_id][$event_timestamp][$event_ticket_id] = true;
                }
            }
        }
    }

    /**
     * Capture WOO Payment
     *
     * @param integer $order_id
     * @return void
     */
    public function capture_payment($order_id)
    {
        // Don't Capture Processed Order
        if (get_post_meta($order_id, 'mw_capture_completed', true)) {
            return;
        }

        // Set Variables
        $order  = new \WC_Order($order_id);
        $wc_discount = $order->get_total_discount(); # Order Discount
        $applied_coupons = $order->get_coupon_codes();
        $wc_before_discount = $order->get_subtotal();
        $tax = $order->get_tax_totals(); # Order Tax

        $order_book_ids = [];
        $transactions = static::get_order_transaction_ids( $order );
        if( empty( $transactions ) ){

            return;
        }
        foreach( $transactions as $transaction_id ) {

            $transactionObject = new \MEC\Transactions\Transaction( $transaction_id );
            $book_id = $transactionObject->get_book_id();
            $transaction_data = $transactionObject->get_data();
            $transaction_data['order_id'] = $order_id;
            $transaction_data['wc_coupons'] = $applied_coupons;
            $transaction_data['wc_discounts'] = $wc_discount;

            $transactionObject->set_data( $transaction_data );
            $transactionObject->update_data();
            $transaction_data = $transactionObject->get_data();

            $book_id = $transactionObject->create_book_from_transaction([
                'mec_gateway' => 'MEC_gateway_add_to_woocommerce_cart',
            ],true);

            if( !is_numeric( $book_id ) ){

                error_log( "add book error transaction: {$transaction_id}");
                continue;
            }

            update_option($transaction_id . '_MEC_payment_complete', $book_id);

            update_post_meta($book_id, 'mec_price', $transactionObject->get_total());
            update_post_meta($book_id, 'mec_order_id', $order_id);

            $order_book_ids[$book_id] = $book_id;
        }

        update_post_meta($order_id, 'mec_order_type', 'mec_ticket');
        update_post_meta($order_id, 'mec_order_book_ids', $order_book_ids);

        // Capture Order as Completed
        update_post_meta($order_id, 'mw_capture_completed', true);
    }

} //Payment

Payment::instance();
