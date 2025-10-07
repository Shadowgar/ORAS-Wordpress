<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * REST API for Create Booking.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/API/CreateBooking
 * @category    Classes
 * @since       5.19.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * BKAP REST API.
 *
 * @since 5.19.0
 */
class BKAP_Admin_API_Create_Booking extends BKAP_Admin_API {

	/**
	 * Construct
	 *
	 * @since 5.19.0
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_endpoints' ) );
		add_action( 'woocommerce_order_after_calculate_totals', array( &$this, 'woocommerce_order_after_calculate_totals_callback' ), 10, 2 );
		add_action( 'wp_loaded', array( $this, 'bkap_wp_loaded' ), 10 );
	}

	/**
	 * Function for registering the API endpoints.
	 *
	 * @since 5.19.0
	 */
	public static function register_endpoints() {

		// Search User.
		register_rest_route(
			self::$base_endpoint,
			'create-booking/search-user',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'search_user' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Search Product.
		register_rest_route(
			self::$base_endpoint,
			'create-booking/search-product',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'search_product' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);
	}

	/**
	 * Search User.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function search_user( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$search_term = $request->get_param( 'search_term' );
		$search_term = ( '' !== $search_term ) ? strtolower( $search_term ) : $search_term;
		$users       = array();
		$wp_users    = get_users(
			apply_filters(
				'bkap_create_booking_page_users_dropdown_args',
				array(
					'fields'  => array( 'id', 'display_name', 'user_email' ),
					'orderby' => 'display_name',
					'order'   => 'ASC',
				)
			)
		);

		foreach ( $wp_users as $user ) {

			if ( false === strpos( strtolower( $user->display_name ), $search_term ) ) {
				continue;
			}

			$data        = new stdClass();
			$data->value = $user->id;
			$data->label = $user->display_name . ' (#' . $user->id . ' - ' . $user->user_email . ')';
			$users[]     = $data;
		}

		if ( strpos( 'guest', $search_term ) !== false ) {
			$data        = new stdClass();
			$data->value = 0;
			$data->label = __( 'Guest', 'woocommerce-booking' );
			$users[] = $data;
		}

		return self::response( 'success', array( 'data' => $users ) );
	}

	/**
	 * Search Product.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function search_product( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$search_term       = $request->get_param( 'search_term' );
		$search_term = ( '' !== $search_term ) ? strtolower( $search_term ) : $search_term;

		$products          = array();
		$bookable_products = bkap_common::get_woocommerce_product_list( true, 'on', '', array( 'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit' ) );

		if ( is_array( $bookable_products ) && count( $bookable_products ) > 0 ) {

			foreach ( $bookable_products as $product ) {

				if ( ! isset( $product[0] ) || ! isset( $product[1] ) ) {
					continue;
				}

				if ( false === strpos( strtolower( $product[0] ), $search_term ) ) {
					continue;
				}

				$data        = new stdClass();
				$data->value = $product[1];
				$data->label = $product[0];
				$products[]  = $data;
			}
		}

		return self::response( 'success', array( 'data' => $products ) );
	}

	/**
	 * Create Booking upon Click of Create Booking Button.
	 * Function loaded on wp_loaded as the same is being used on front end.
	 * Creating Manual Booking fron end was throwing header already sent by message.
	 *
	 * @since 5.10.0
	 */
	public function bkap_wp_loaded() {

		if ( isset( $_POST['bkap_create_booking_nonce'] ) ) {

			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bkap_create_booking_nonce'] ) ), 'bkap_create_booking' ) ) { // nosemgraep: scanner.php.wp.security.csrf.nonce-flawed-logic.
				wp_die( esc_html__( 'Security check failed.', 'woocommerce-booking' ), 403 );
			}

			try {
				if ( ! empty( $_POST['create_booking_post_data'] ) ) {

					$customer_id         = isset( $_POST['create_booking_post_data']['user_id'] ) ? absint( $_POST['create_booking_post_data']['user_id'] ) : 0;
					$bookable_product_id = isset( $_POST['create_booking_post_data']['product_id'] ) ? absint( $_POST['create_booking_post_data']['product_id'] ) : 0;
					$booking_order       = isset( $_POST['create_booking_post_data']['order_type'] ) ? sanitize_text_field( wp_unslash( $_POST['create_booking_post_data']['order_type'] ) ) : '';

					if ( ! $bookable_product_id ) {
						throw new Exception( __( 'Please choose a bookable product', 'woocommerce-booking' ) );
					}

					if ( 'existing' === $booking_order ) {
						$order_id      = isset( $_POST['create_booking_post_data']['existing_order_id'] ) ? absint( $_POST['create_booking_post_data']['existing_order_id'] ) : 0;
						$booking_order = $order_id;
						if ( ! wc_get_order( $order_id ) ) {
							throw new Exception( __( 'Invalid order ID provided', 'woocommerce-booking' ) );
						}
					}
				}
			} catch ( Exception $e ) {
				$_POST['create_booking_post_error'] = $e->getMessage();
			}
		}
	}

	/**
	 * Updating the price in the booking when discount is appied from Edit Order page.
	 *
	 * @param bool   $and_taxes true if calculation for taxes else false.
	 * @param Object $order Shop Order post.
	 * @since 4.9.0
	 *
	 * @hook woocommerce_order_after_calculate_totals
	 */
	public function woocommerce_order_after_calculate_totals_callback( $and_taxes, $order ) {

		$item_values = $order->get_items();

		foreach ( $item_values as $cart_item_key => $values ) {

			$product_id = $values['product_id'];
			$bookable   = bkap_common::bkap_get_bookable_status( $product_id );

			if ( ! $bookable ) {
				continue;
			}

			$booking_id    = bkap_common::get_booking_id( $cart_item_key );
			$item_quantity = $values->get_quantity(); // Get the item quantity.
			$item_total    = number_format( (float) $values->get_total(), wc_get_price_decimals(), '.', '' );
			$item_tax      = number_format( (float) $values->get_total_tax(), wc_get_price_decimals(), '.', '' );
			$item_total    = $item_total + $item_tax;
			$item_total    = $item_total / $item_quantity;

			// update booking post meta.
			update_post_meta( $booking_id, '_bkap_cost', $item_total );
		}
	}
}
