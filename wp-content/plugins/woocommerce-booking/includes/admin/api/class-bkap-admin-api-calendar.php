<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * REST API for Calendar.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/API/Calendar
 * @category    Classes
 * @since       5.19.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * BKAP REST API.
 *
 * @since 5.19.0
 */
class BKAP_Admin_API_Calendar extends BKAP_Admin_API {

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

		// Fetch Calendar data.
		register_rest_route(
			self::$base_endpoint,
			'calendar/fetch',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'fetch_calendar_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Save Calendar data.
		register_rest_route(
			self::$base_endpoint,
			'calendar/save',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'save_calendar_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);
	}

	/**
	 * Returns Calendar Data.
	 *
	 * @param bool $return_raw Whether to return the Raw response.
	 *
	 * @since 5.19.0
	 */
	public static function fetch_calendar_data( $return_raw = false ) {
		$response                         = array();
		$global_settings                  = bkap_json_decode( get_option( 'woocommerce_booking_global_settings' ) );
		$response['booking_language']     = self::check( $global_settings, 'booking_language' );
		$response['booking_language']     = '' === $response['booking_language'] ? 'en-GB' : $response['booking_language'];
		$response['calendar_language']    = '';
		$response['booking_date_format']  = self::check( $global_settings, 'booking_date_format' );
		$response['booking_time_format']  = self::check( $global_settings, 'booking_time_format' );
		$response['booking_calendar_day'] = self::check( $global_settings, 'booking_calendar_day' );
		$response['booking_calendar_day'] = '' === $response['booking_calendar_day'] ? get_option( 'start_of_week' ) : $response['booking_calendar_day'];
		$response['booking_months']       = self::check( $global_settings, 'booking_months' );
		$response['booking_themes']       = self::check( $global_settings, 'booking_themes' );
		return self::return_response( $response, $return_raw );
	}

	/**
	 * Saves Calendar Data.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function save_label_data( WP_REST_Request $request ) {

		self::verify_nonce( $request );

		$data            = $request->get_param( 'data' );
		$global_settings = bkap_json_decode( get_option( 'woocommerce_booking_global_settings' ) );

		if ( is_array( $data ) ) {
			foreach ( $data as $key => $setting ) {
				$global_settings->{$key} = $setting;
			}

			update_option( 'woocommerce_booking_global_settings', wp_json_encode( $global_settings ) );
			return self::success();
		}

		return self::error();
	}
}
