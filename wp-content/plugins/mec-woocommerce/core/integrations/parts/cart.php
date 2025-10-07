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
*  Cart.
*
*  @author      Webnus <info@webnus.biz>
*  @package     Modern Events Calendar
*  @since       1.0.0
**/
class Cart extends Helper
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
        add_filter('woocommerce_is_purchasable', [$this, 'valid_mec_ticket_products_purchasable'], 20, 2);
        add_action( 'woocommerce_cart_item_removed', [ __CLASS__, 'remove_item_handler' ], 10, 2 );
    }

   /**
    * Check MEC Products Purchasable Status
    *
    * @param boolean $purchasable
    * @param object $product
    * @return boolean
    */
    public static function valid_mec_ticket_products_purchasable($purchasable, $product)
    {
        if ($product->exists() && ('mec_tickets' === $product->get_status())) {
            $purchasable = true;
        }

        return $purchasable;
    }

    public static function remove_item_handler( $cart_item_key, $cart ){

        $item = $cart->removed_cart_contents[ $cart_item_key ];

        $product_id = $item['product_id'] ?? 0;
        $product = wc_get_product( $product_id );
        if ( 'mec_tickets' === $product->get_status() ) {

            remove_action( 'woocommerce_cart_item_removed', [ __CLASS__, 'remove_item_handler' ], 10, 2 );

            $transaction_id = get_post_meta(  $product_id, 'transaction_id', true );
            $transactionObject = new \MEC\Transactions\Transaction( $transaction_id );
            $transactionObject->remove_ticket_by_product_id( $product_id );
            $tickets = $transactionObject->get_tickets_details();
            $type = get_post_meta(  $product_id, 'm_product_type', true );

            foreach ( wc()->cart->get_cart() as $key => $item ) {

                $product_id     = $item['product_id'];
                $p_transaction_id = get_post_meta($product_id, 'transaction_id', true);

                if( $transaction_id == $p_transaction_id ){

                    WC()->cart->remove_cart_item( $key );

                    if( !empty( $tickets ) && empty( $type ) ) {

                        WC()->cart->add_to_cart( $product_id, 1 );
                    }
                }
            }

            add_action( 'woocommerce_cart_item_removed', [ __CLASS__, 'remove_item_handler' ], 10, 2 );
        }
    }

} //Cart

Cart::instance();
