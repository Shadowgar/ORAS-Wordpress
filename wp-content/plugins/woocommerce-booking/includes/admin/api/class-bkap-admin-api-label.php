<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * REST API for Label & Messages.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/API/Label
 * @category    Classes
 * @since       5.19.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * BKAP REST API.
 *
 * @since 5.19.0
 */
class BKAP_Admin_API_Label extends BKAP_Admin_API {

	/**
	 * Construct
	 *
	 * @since 5.19.0
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_endpoints' ) );
	}

	/**
	 * Function for registering the API endpoints.
	 *
	 * @since 5.19.0
	 */
	public static function register_endpoints() {

		// Fetch Label data.
		register_rest_route(
			self::$base_endpoint,
			'label/fetch',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'fetch_label_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Save Label data.
		register_rest_route(
			self::$base_endpoint,
			'label/save',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'save_label_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);
	}

	/**
	 * Returns Label Data.
	 *
	 * @param bool $return_raw Whether to return the Raw response.
	 *
	 * @since 5.19.0
	 */
	public static function fetch_label_data( $return_raw = false ) {

		$response = array();

		$response['book_date-label']                       = get_option( 'book_date-label', '' );
		$response['checkout_date-label']                   = get_option( 'checkout_date-label', '' );
		$response['book_time-label']                       = get_option( 'book_time-label', '' );
		$response['book_time-select-option']               = get_option( 'book_time-select-option', '' );
		$response['book_fixed-block-label']                = get_option( 'book_fixed-block-label', '' );
		$response['book_price-label']                      = get_option( 'book_price-label', '' );
		$response['bkap_calendar_icon_file']               = get_option( 'bkap_calendar_icon_file', 'none' );
		$response['book_item-meta-date']                   = get_option( 'book_item-meta-date', '' );
		$response['checkout_item-cart-date']               = get_option( 'checkout_item-cart-date', '' );
		$response['bkap_check_availability']               = get_option( 'bkap_check_availability', '' );
		$response['bkap_add_to_cart']                      = get_option( 'bkap_add_to_cart', '' );
		$response['book_stock-total']                      = get_option( 'book_stock-total', '' );
		$response['book_available-stock-date']             = get_option( 'book_available-stock-date', '' );
		$response['book_available-stock-time']             = get_option( 'book_available-stock-time', '' );
		$response['checkout_item-meta-date']               = get_option( 'checkout_item-meta-date', '' );
		$response['book_item-meta-time']                   = get_option( 'book_item-meta-time', '' );
		$response['book_ics-file-name']                    = get_option( 'book_ics-file-name', '' );
		$response['book_item-cart-date']                   = get_option( 'book_item-cart-date', '' );
		$response['book_item-cart-time']                   = get_option( 'book_item-cart-time', '' );
		$response['book_available-stock-date-attr']        = get_option( 'book_available-stock-date-attr', '' );
		$response['book_available-stock-time-attr']        = get_option( 'book_available-stock-time-attr', '' );
		$response['book_real-time-error-msg']              = get_option( 'book_real-time-error-msg', '' );
		$response['book_multidates_min_max_selection_msg'] = get_option( 'book_multidates_min_max_selection_msg', '' );
		$response['book_multidates_fixed_selection_msg']   = get_option( 'book_multidates_fixed_selection_msg', __( 'Select FIXED Dates for booking', 'woocommerce-booking' ) );
		$response['book_limited-booking-msg-date']         = get_option( 'book_limited-booking-msg-date', '' );
		$response['book_no-booking-msg-date']              = get_option( 'book_no-booking-msg-date', '' );
		$response['book_limited-booking-msg-time']         = get_option( 'book_limited-booking-msg-time', '' );
		$response['book_no-booking-msg-time']              = get_option( 'book_no-booking-msg-time', '' );
		$response['book_limited-booking-msg-date-attr']    = get_option( 'book_limited-booking-msg-date-attr', '' );
		$response['book_limited-booking-msg-time-attr']    = get_option( 'book_limited-booking-msg-time-attr', '' );

		return self::return_response( $response, $return_raw );
	}

	/**
	 * Saves Label Data.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function save_label_data( WP_REST_Request $request ) {

		self::verify_nonce( $request );

		$data = $request->get_param( 'data' );

		if ( is_array( $data ) ) {

			foreach ( $data as $key => $setting ) {
				update_option( $key, $setting );
			}

			return self::success();
		}

		return self::error();
	}
}
