<?php

namespace MEC_Woocommerce\Core\Integrations;
use MEC\Books\EventBook;
use \MEC_Woocommerce\Core\Helpers\Products as Helper;
// Don't load directly
if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

/**
*  AddToCart.
*
*  @author      Webnus <info@webnus.biz>
*  @package     Modern Events Calendar
*  @since       1.0.0
**/
class AddToCart extends Helper
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
    *  The directory of this file
    *
    *  @access  public
    *  @var     string
    */
    public static $dir;

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
        add_action('wp_loaded', [$this, 'add_multiple_products_to_cart'], 15, 1);
        add_action('wp_loaded', [$this, 'process_add_to_cart']);
        add_action('wp_enqueue_scripts', [$this, 'render_the_script'], 10000);

        add_filter( 'woocommerce_update_cart_validation', [$this,'mec_wc_qty_update_cart_validation'], 1, 4 );
        add_filter( 'woocommerce_is_sold_individually', [ __CLASS__, 'filter_sold_individually_for_tickets' ], 10, 2 );
		add_filter( 'woocommerce_cart_item_quantity', [ __CLASS__, 'remove_edit_ticket_quantity' ], 10, 3 );
    }

    /**
     * WooCommerce Maybe Add Multiple Products To Cart
     *
     * @param boolean $url
     * @return void
     */
    public function add_multiple_products_to_cart($url = false)
    {
        $get_term = get_term_by('slug', 'mec-woo-cat', 'product_cat');
        static::$term_id = (isset($get_term) and !empty($get_term)) ? $get_term->term_id : '';
        if (!class_exists('WC_Form_Handler') || empty($_REQUEST['add-to-cart'])) {
            return;
        }

        $product_ids = explode(',', $_REQUEST['add-to-cart']);
        foreach ($product_ids as $pid) {
            if ($product = wc_get_product($pid)) {
                if (strtolower($product->get_status()) != 'mec_tickets') {
                    return;
                }
            }
        }

        remove_action('wp_loaded', array('WC_Form_Handler', 'add_to_cart_action'), 20);
        $count  = count($product_ids);
        $cart_keys = array();
        $added = $not_added = 0;
        foreach ($product_ids as $id_and_quantity) {
            $id_and_quantity         = explode(':', $id_and_quantity);
            $product_id              = $id_and_quantity[0];
            $_REQUEST['quantity']    = isset( $id_and_quantity[1] ) && !empty($id_and_quantity[1]) ? absint($id_and_quantity[1]) : 1;
            $_REQUEST['add-to-cart'] = $product_id;

            if( !$product_id ){
                continue;
            }

            $values = [
                'product_id' => $product_id,
                'quantity' => $_REQUEST['quantity'],
            ];
            $can_add = $this->mec_wc_qty_update_cart_validation(true,null,$values,$_REQUEST['quantity']);
            if($can_add){

                $added_to_cart = @\WC()->cart->add_to_cart($product_id, $_REQUEST['quantity']);
                $product_id        = apply_filters('woocommerce_add_to_cart_product_id', absint($product_id));
                $adding_to_cart    = wc_get_product($product_id);
                $added++;
                $cart_keys[] = $added_to_cart;
            }else{
                $not_added++;
            }
        }

        if( $not_added ){

            foreach( $cart_keys as $cart_key ){

                @\WC()->cart->remove_cart_item( $cart_key );
            }
        }

        $redirect_to = isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '';
        $redirect_to_type = isset( $_REQUEST['redirect_to_type'] ) ? $_REQUEST['redirect_to_type'] : '';
        switch( $redirect_to_type ){
            case 'cart':
            case 'checkout':

                $r = array(
                    'redirect_to' => $redirect_to
                );

                wp_send_json( $r );

                break;
            case 'optional_cart':
            case 'optional_checkout':

                if( 'optional_cart' === $redirect_to_type ){

                    $link_text = __('Cart Page', 'mec-woocommerce');
                }elseif( 'optional_checkout' === $redirect_to_type ){

                    $link_text = __('Checkout Page', 'mec-woocommerce');
                }

                if ( $added && $count > 1) {

                    wc_add_notice(__('The Tickets are added to your cart.', 'mec-woocommerce') . ' <a href="' . $redirect_to . '" target="_blank">' . $link_text . '</a>', apply_filters('woocommerce_add_to_cart_notice_type', 'success'));
                } elseif($added) {

                    wc_add_notice(__('The Ticket is added to your cart.', 'mec-woocommerce') . ' <a href="' . $redirect_to . '" target="_blank">' . $link_text . '</a>', apply_filters('woocommerce_add_to_cart_notice_type', 'success'));
                }

                break;
        }

        die();
    }

    /**
     * Render Add To Cart Button
     *
     * @param string $transaction_id
     * @param string $redirect_to
     * @param string  $redirect_to_type
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function render_add_to_cart_button( $transaction_id, $redirect_to, $redirect_to_type ){

        $redirect = 'no';
        $nonce = wp_create_nonce('mec-woocommerce-process-add-to-cart');
        $add_to_cart_url = get_site_url() . '?transaction-id=' . $transaction_id . '&action=mec-woocommerce-process-add-to-cart&nonce=' . $nonce;


        if (isset($_REQUEST['trp-form-language']) && !empty($_REQUEST['trp-form-language'])){
            $redirect_to = home_url() . '/'.sanitize_text_field($_REQUEST['trp-form-language']).'/cart';
        }

        $add_to_cart_url = esc_url_raw(
            add_query_arg(
                array(
                    'redirect_to' => urlencode($redirect_to),
                    'redirect_to_type' => $redirect_to_type,
                ),
                $add_to_cart_url
            )
        );

        switch( $redirect_to_type ){
            case 'cart':
            case 'checkout':

                $redirect = 'yes';

                break;
            case 'optional_cart':
            case 'optional_checkout':

                $redirect_to = '#';

                break;
        }
        $RedirectURL = apply_filters( 'mec_woocommerce_after_add_to_cart_url', $redirect_to );

        echo '<a href="' . esc_attr($add_to_cart_url) . '" id="mec_woo_add_to_cart_btn_r" data-cart-url="' . esc_attr($RedirectURL) . '" class="button mec-add-to-cart-btn-r" aria-label="Please Wait" rel="nofollow">' . esc_html__('Add to cart', 'mec-woocommerce') . '</a>';
    }

    /**
     * Render Inline Script
     *
     * @since     1.0.0
     */
    public function render_the_script(){

        $script = <<<Script
            // MEC Woocommerce Add to Cart BTN
            jQuery(document).on('click', '#mec_woo_add_to_cart_btn_r', function (e) {
                e.preventDefault();
                if( jQuery(this).hasClass('loading') ) {

                    return;
                }

                var _this = jQuery(this);
                _this.addClass('loading');
                var href = jQuery(this).attr('href');
                var cart_url = jQuery(this).data('cart-url');

                jQuery.ajax({
                    type: "get",
                    url: href,
                    success: function (response) {
                        if(typeof response.message != 'undefined') {
                            jQuery('.mec-add-to-cart-message').remove();
                            jQuery('.mec-book-form-gateways').before('<div class="mec-add-to-cart-message mec-util-hidden mec-error" style="display: block;">'+ response.message +'</div>');
                            _this.removeClass('loading');
                            return;
                        }
                        var SUrl = response.url;
                        jQuery.ajax({
                            type: "get",
                            url: SUrl,
                            success: function (response) {
                                jQuery(this).removeClass('loading');
                                setTimeout(function() {
                                    window.location.href = cart_url === '#' ? window.location.href : cart_url;
                                }, 500);
                            }
                        });
                    }
                });
                return false;
            });
        Script;

        wp_add_inline_script('jquery', $script);
    }

    /**
     * Process Add to Cart
     *
     * @since     1.0.0
     */
    public function process_add_to_cart()
    {
        if (isset($_REQUEST['action']) && $_REQUEST['action'] != 'mec-woocommerce-process-add-to-cart') {
            return false;
        } else if (!isset($_REQUEST['action'])) {
            return false;
        }
        if (!wp_verify_nonce($_REQUEST['nonce'], 'mec-woocommerce-process-add-to-cart')) {
            return false;
        }

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $transaction_id 	= isset($_REQUEST['transaction-id']) ? $_REQUEST['transaction-id'] : '';
            if (!$transaction_id) {
                return false;
            }
        } else {
            return false;
        }
        $transactionObject  = new \MEC\Transactions\Transaction( $transaction_id );

        $event_id           = $transactionObject->get_event_id();

        $product_ids    	= [];
        $main            	= \MEC::getInstance('app.libraries.main');
        $book            	= \MEC::getInstance('app.libraries.book');

        $settings 			= static::$mec_settings;
        $gateways_options = $main->get_gateways_options();
        $gateway_options = $gateways_options[1995];

        $can_add_book = $transactionObject->validate_for_add_book();
        if( is_array( $can_add_book ) ) {

            $main->response( current( $can_add_book ) );
            return;
        }

        list($limit, $unlimited) = $book->get_user_booking_limit($event_id);
        $used_times = 0;
        foreach (wc()->cart->get_cart() as $key => $item) {
            $product_id     = $item['product_id'];
            $p_event_id       = get_post_meta($product_id, 'event_id', true);
            if($p_event_id == $event_id ){
                $used_times++;
            }
        }
        if($used_times >= $limit) {
            //$main->response(array('success'=>0, 'message'=>sprintf($main->m('booking_restriction_message3', __("Maximum allowed number of tickets that you can book is %s.", 'mec')), $limit), 'code'=>'LIMIT_REACHED'));
            //return;
        }

        $product_ids = $transactionObject->create_products_from_items();

        do_action('mec-woocommerce-product-created', $product_ids, $transaction_id);

        $countt = 0;
        foreach ($product_ids as $pr_key_1 => $pr_id_1) {

            $ex = explode(':',$pr_id_1);
            $p_id = isset($ex[0]) ? $ex[0] : 0;
            $p_quantity = isset($ex[1]) ? $ex[1] : 1;
            if(!$p_id){

                continue;
            }
            $ticket_id_in_cart = get_post_meta($p_id, '_mec_ticket_id', true);
            $ticket_limit_in_cart = get_post_meta($p_id, '_mec_ticket_limit', true);
            $event_id_in_cart = get_post_meta($p_id, '_mec_event_id', true);

            if ( $countt > 0  && (isset($ticket_limit_in_cart) && !empty($ticket_limit_in_cart) && $countt >= $ticket_limit_in_cart )){
                $main->response(array('success'=>0, 'message'=>sprintf($main->m('booking_restriction_message3', __("Maximum allowed number of tickets that you can book is %s.", 'mec-woocommerce')), $ticket_limit_in_cart), 'code'=>'LIMIT_REACHED'));
                return;
            }
        }

        $redirect_to = isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '';
        $redirect_to_type = isset( $_REQUEST['redirect_to_type'] ) ? $_REQUEST['redirect_to_type'] : '';
        $product_ids     = implode(',', $product_ids);
        $add_to_cart_url = esc_url_raw(
            add_query_arg(
                array(
                    'add-to-cart' => $product_ids,
                    'redirect_to' => urlencode($redirect_to),
                    'redirect_to_type' => $redirect_to_type,
                ),
                wc_get_cart_url()
            )
        );
        ob_start();
        ob_get_clean();
        header('Content-Type: application/json');
        echo json_encode([
            'url' => $add_to_cart_url
        ]);
        die();
    }

    public function get_variation_products(){

        return array(
            'amount_per_booking',
            'amount_per_date',
            'percent',
        );
    }

    public function get_all_dates( $transaction_id ){

        $transaction = get_option( $transaction_id );

        return !empty( $transaction['all_dates'] ) ? $transaction['all_dates'] : [$transaction['date']];
    }


    public function get_dates_count( $product_id ){

        $transaction_id = get_post_meta( $product_id,'transaction_id', true );
        $all_dates = $this->get_all_dates( $transaction_id );

        return count( $all_dates );
    }

    public function are_related_products_added( $p_id, $cart_ticket_ids ){

        $related_ids = get_post_meta( $p_id, 'related_products', true );

        foreach( $related_ids as $k => $related_product_id ){

            if( in_array( $related_product_id, $cart_ticket_ids ) ){

                unset( $related_ids[ $k ] );
            }
        }

        return empty( $related_products ) ? true : false;
    }

    public function is_ticket_variation( $product_id ){

        $product_type = get_post_meta( $product_id, 'm_product_type', true );
        $v_types = $this->get_variation_products();

        return in_array( $product_type, $v_types );
    }

    /**
     * Return ticket product detail
     *
     * @param int $product_id
     *
     * @return array
     */
    public function get_ticket_product_detail( $product_id ){

        $event_id = get_post_meta($product_id, 'event_id', true);
        $event_timestamp = get_post_meta($product_id, 'mec_date', true);
        $ticket_id = get_post_meta($product_id, 'ticket_id', true);
        $related_ids = get_post_meta( $product_id, 'related_products', true );

        $is_variation = $this->is_ticket_variation( $product_id );

        return array(
            'product_id' => $product_id,
            'event_id' => $event_id,
            'event_timestamp' => $event_timestamp,
            'ticket_id' => $ticket_id,
            'is_variation' => $is_variation,
            'related_ids' => $related_ids
        );
    }

    /**
     * Return ticket products detail in cart
     *
     * @return array
     */
    public function get_ticket_products_detail_in_cart(){

        $ticket_products = array();
        $cart = @\WC()->cart->get_cart();
        foreach( $cart as $cart_item_key => $cart_item ){

            $product_status = $cart_item['data']->get_status();
            $product_id = $cart_item['data']->get_id();
            if ( 'mec_tickets' === $product_status ) {

                $ticket_data = $this->get_ticket_product_detail( $product_id );
                $ticket_data['cart_item_key'] = $cart_item_key;
                $ticket_data['quantity'] = $cart_item['quantity'];

                $ticket_products[ $cart_item_key ] =  $ticket_data;
            }
        }

        return $ticket_products;//TODO: cache
    }

    /**
     * Return all total tickets in cart group by event_id and event_timestamp
     *
     * @param string $without_cart_key
     *
     * @return array
     */
    public function get_total_tickets_in_cart( $without_cart_key = null, $without_variations = true ){

        $total_tickets = array();
        $ticket_products = $this->get_ticket_products_detail_in_cart();
        if( !is_null( $without_cart_key ) ){

            unset( $ticket_products[ $without_cart_key ] );
        }

        foreach( $ticket_products as $ticket_cart_item_key => $ticket_product ){

            $p_event_id = $ticket_product['event_id'];
            $p_event_timestamp = $ticket_product['event_timestamp'];
            $p_ticket_id = $ticket_product['ticket_id'];
            $p_quantity = $ticket_product['quantity'];

            $p_is_variation = $ticket_product['is_variation'];

            if( $without_variations && $p_is_variation ){

                continue;
            }

            if( !isset( $total_tickets[ $p_event_id ][ $p_event_timestamp ][ $p_ticket_id ] ) ){

                $total_tickets[ $p_event_id ][ $p_event_timestamp ][ $p_ticket_id ] = 0;
            }

            $total_tickets[ $p_event_id ][ $p_event_timestamp ][ $p_ticket_id ] += $p_quantity;
        }

        return $total_tickets;
    }

    public function get_total_tickets_for_event_in_all_occurrences( $cart_item_key = '', $event_id = 0 ) {

        $running_qty = 0;

        $all_ticket_products = $this->get_total_tickets_in_cart( $cart_item_key );
        foreach( $all_ticket_products as $tp_event_id => $ticket_products ){

            if( $event_id && $tp_event_id != $event_id ){

                continue;
            }

            foreach( $ticket_products as $event_date => $total_tickets ){

                $running_qty += (int) array_sum( $total_tickets );
            }
        }

        return $running_qty;
    }

    /**
     * Return total tickets in the event occurrence
     *
     * @param int $event_id
     * @param string $event_timestamp start:end
     * @param string $without_cart_key
     *
     * @return array
     */
    public function get_total_tickets_in_cart_for_event( $event_id, $event_timestamp, $without_cart_key = null ){

        $total_tickets = $this->get_total_tickets_in_cart( $without_cart_key );

        return $total_tickets[ $event_id ][ $event_timestamp ] ?? array();
    }

    public function mec_wc_qty_update_cart_validation( $passed, $cart_item_key, $values, $quantity ) {

        // Check if product update is MEC ticket
        $product_id = $values['product_id'];
        $product = wc_get_product( $product_id );
        $event_id = get_post_meta( $product_id, 'event_id',true);
        $pr_status = $product->get_status();
        if ($pr_status != 'mec_tickets' ){
            return $passed;
        }

        // $dates_count = $this->get_dates_count( $product_id );
        // $quantity = $quantity * $dates_count;

        $ticket_used = get_post_meta($product_id, 'ticket_used_count', true);
        if( $quantity && (int)$ticket_used !== (int)$quantity ){

            wc_add_notice( apply_filters( 'wc_qty_error_message',__('The number of tickets is not allowed','mec-woocommerce')),'error' );
            return false;
        }

        $settings = static::$mec_settings;

        $product_ids = [];
        $cart = @\WC()->cart->get_cart();
        foreach( $cart as $cart_item ){
            // compatibility with WC +3
            if( version_compare( WC_VERSION, '3.0', '<' ) ){
                $product_status = $cart_item['data']->status();
                $product_id = $cart_item['data']->id();
                if ( $product_status == 'mec_tickets' ) {
                    $product_ids[] = $product_id;
                }
            } else {
                $product_status = $cart_item['data']->get_status();
                $product_id = $cart_item['data']->get_id();
                if ( $product_status == 'mec_tickets' ) {
                    $product_ids[] = $product_id;
                }

            }
        }


        $product_id = $values['product_id'];
        $event_id = get_post_meta($product_id, 'event_id', true);
        $event_title = get_the_title($event_id);
        $event_timestamp = get_post_meta($product_id, 'mec_date', true);
        $event_ticket_id = get_post_meta($product_id, 'ticket_id', true);
        $booking_options = get_post_meta($event_id, 'mec_booking', true);

        $ticket_products = $this->get_ticket_products_detail_in_cart();//TODO:
        $event_tickets_in_cart = $this->get_total_tickets_for_event_in_all_occurrences( $cart_item_key, $event_id );
        $all_booking_limit = EventBook::getInstance()->get_total_booking_limit( $event_id );
        $event_available_tickets = EventBook::getInstance()->get_tickets_availability( $event_id, $event_timestamp );
        $total_event_available_ticket = $event_available_tickets['total'];

        if( $this->is_ticket_variation( $values['product_id'] ) ){

            $added = $this->are_related_products_added( $values['product_id'], $product_ids );
            if( !$added ){

                wc_add_notice( apply_filters( 'wc_qty_error_message', sprintf( __( 'Tickets not added', 'mec-woocommerce' ) ) ),'error' );

                return false;
            }else{
                return $passed;
            }
        }

        // Check limit for All Event in cart
        if ( -1 != $all_booking_limit && !empty($all_booking_limit) && ($event_tickets_in_cart + $quantity) > $all_booking_limit ) {

            wc_add_notice( apply_filters( 'wc_qty_error_message', sprintf( __( 'You can add just %1$s ticket(s) to your cart', 'mec-woocommerce' ),$all_booking_limit), $all_booking_limit ),
            'error' );
            $passed = false;
        }

        $total_tickets = $this->get_total_tickets_in_cart_for_event( $event_id, $event_timestamp, $cart_item_key );
        $total_event_ticket_in_cart = $total_tickets[ $event_ticket_id ] ?? 0;
        $total_event_tickets_in_cart = array_sum( $total_tickets );

        if( $quantity ){

            // $total_event_tickets_in_cart = $total_event_tickets_in_cart + $total_event_ticket_in_cart;
        }

        //tickets check
        $sum_event_ticket_available = $total_event_tickets_in_cart + $quantity;
        if( -1 != $total_event_available_ticket && $sum_event_ticket_available > $total_event_available_ticket ){

            wc_add_notice( apply_filters( 'wc_qty_error_message', sprintf( __( 'You can add a maximum of %1$s "%2$s\'s" to %3$s.', 'mec-woocommerce' ),
                        $total_event_available_ticket,
                        $event_title,
                        '<a href="' . esc_url( wc_get_cart_url() ) . '">' . __( 'your cart', 'mec-woocommerce' ) . '</a>'),
                    $total_event_available_ticket ),
            'error' );

            return false;
        }

        $total_ticket_available_allowed = isset($event_available_tickets[$event_ticket_id]) ? $event_available_tickets[$event_ticket_id] : -1;
        $sum_ticket_available = $total_event_ticket_in_cart + $quantity;
        if( -1 != $total_ticket_available_allowed && $sum_ticket_available > $total_ticket_available_allowed ){

            wc_add_notice( apply_filters( 'wc_qty_error_message', sprintf( __( 'You can add a maximum of %1$s "%2$s\'s" to %3$s.', 'mec-woocommerce' ),
                        $total_ticket_available_allowed,
                        $event_title,
                        '<a href="' . esc_url( wc_get_cart_url() ) . '">' . __( 'your cart', 'mec-woocommerce' ) . '</a>'),
                    $total_ticket_available_allowed ),
            'error' );

            return false;
        }

        $event_user_total_booking_limit = EventBook::getInstance()->get_user_total_booking_limit( $event_id );
        if ( -1 != $event_user_total_booking_limit && ( $total_event_tickets_in_cart + $quantity ) > $event_user_total_booking_limit ) {
            wc_add_notice( apply_filters( 'wc_qty_error_message', sprintf( __( 'You can add a maximum of %1$s %2$s\'s to %3$s.', 'mec-woocommerce' ),
                        $event_user_total_booking_limit,
                        get_the_title(get_post_meta($values['product_id'], 'event_id', true)),
                        '<a href="' . esc_url( wc_get_cart_url() ) . '">' . __( 'your cart', 'mec-woocommerce' ) . '</a>'),
                    $event_user_total_booking_limit ),
            'error' );

            return false;
        }

        $mec_ticket_limit = get_post_meta($values['product_id'], '_mec_ticket_limit', true);
        if ( isset( $mec_ticket_limit) && $mec_ticket_limit && ( $total_event_ticket_in_cart + $quantity ) > $mec_ticket_limit ) {
            wc_add_notice( apply_filters( 'wc_qty_error_message', sprintf( __( 'You can add a maximum of %1$s %2$s\'s to %3$s.', 'mec-woocommerce' ),
                        $mec_ticket_limit,
                        get_the_title(get_post_meta($values['product_id'], 'event_id', true)),
                        '<a href="' . esc_url( wc_get_cart_url() ) . '">' . __( 'your cart', 'mec-woocommerce' ) . '</a>'),
                    $mec_ticket_limit ),
            'error' );
            $passed = false;
        }

        return $passed;
    }


    public static function filter_sold_individually_for_tickets( $return , $product ){

		if($return){

			return $return;
		}

		$is_ticket = 'mec_tickets' === $product->get_status();
		if(!$is_ticket){

			return $return;
		}

		$first_for_all = $product->get_meta('first_for_all');

		if( 'yes' !== $first_for_all ){

			return true;
		}

		return false;
	}

	public static function remove_edit_ticket_quantity( $product_quantity, $cart_item_key, $cart_item ){

		if( is_cart() ){

			$product = wc_get_product( $cart_item['product_id'] );
			if($product && 'mec_tickets' === $product->get_status()){

				$product_quantity = sprintf( '%2$s <input type="hidden" name="cart[%1$s][qty]" value="%2$s" />', $cart_item_key, $cart_item['quantity'] );
			}
		}

		return $product_quantity;
	}


} //AddToCart

AddToCart::instance();
