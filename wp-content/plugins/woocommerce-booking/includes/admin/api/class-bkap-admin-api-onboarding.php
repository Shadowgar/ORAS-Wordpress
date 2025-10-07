<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * REST API for Resources.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/API/Resources
 * @category    Classes
 * @since       5.19.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * BKAP REST API.
 *
 * @since 5.19.0
 */
class BKAP_Admin_API_Onboarding extends BKAP_Admin_API {

	/**
	 * Construct
	 *
	 * @since 5.19.0
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_endpoints' ) );
		add_action( 'admin_init', array( __CLASS__, 'bkap_onboarding_setup_done' ) );
	}

	/**
	 * Set data to finish the onboarding.
	 *
	 * @since 5.19.0
	 */
	public static function bkap_onboarding_setup_done() {

		if ( isset( $_GET['bkap_setup'] ) && ( 'product' === $_GET['bkap_setup'] || 'dashboard' === $_GET['bkap_setup'] ) ) { //phpcs:ignore

			switch ( $_GET['bkap_setup'] ) { // phpcs:ignore WordPress.Security.NonceVerification
				case 'product':
					$location = add_query_arg(
						array( 'post_type' => 'product', 'bkap_bookable' => 'true' ),
						admin_url( 'post-new.php' )
					);
					break;
				case 'dashboard':
					$location = admin_url( 'admin.php?page=bkap_page' );
					break;
			}

			if ( '' !== $location ) {
				update_option( 'bkap_welcome_page_displayed', 'yes' );
				wp_safe_redirect( $location );
			}
		}
	}

	/**
	 * Function for registering the API endpoints.
	 *
	 * @since 5.19.0
	 */
	public static function register_endpoints() {

		// Save Appearance Settings data.
		register_rest_route(
			self::$base_endpoint,
			'onboarding/appearance-settings',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'save_appearance_settings_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Save Booking Settings data.
		register_rest_route(
			self::$base_endpoint,
			'onboarding/booking-settings',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'save_booking_settings_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Save Reschedule and Cancel data.
		register_rest_route(
			self::$base_endpoint,
			'onboarding/reschedule-cancel',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'save_reschedule_cancel_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Save Labels data.
		register_rest_route(
			self::$base_endpoint,
			'onboarding/labels',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'save_labels_data' ),
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
	public static function fetch_booking_data( $return_raw = false ) {

		$response                       = array();
		$response['bkap_booking_types'] = bkap_get_booking_types();
		$response['bkap_weekdays']      = bkap_weekdays();
		$response['bkap_inline']        = '';

		return self::return_response( $response, $return_raw );
	}

	/**
	 * Returns Calendar Data.
	 *
	 * @param bool $return_raw Whether to return the Raw response.
	 *
	 * @since 5.19.0
	 */
	public static function fetch_labels_data( $return_raw = false ) {

		$response                        = array();
		$response['book_date_label']     = get_option( 'book_date-label', '' );
		$response['checkout_date_label'] = get_option( 'checkout_date-label', '' );
		$response['book_time_label']     = get_option( 'book_time-label', '' );
		$response['bkap_add_to_cart']    = get_option( 'bkap_add_to_cart', '' );

		return self::return_response( $response, $return_raw );
	}

	/**
	 * Saves booking settings data from onboarding.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function save_booking_settings_data( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$data = $request->get_param( 'data' );

		if ( is_array( $data ) && count( $data ) > 0 ) {
			$bkap_booking_type = self::check( $data, 'bkap_booking_type', '' );
			$bkap_weekdays     = self::check( $data, 'bkap_weekdays', '' );
			$bkap_inline       = self::check( $data, 'bkap_inline', '' );

			$booking_settings = array(
				'bkap_booking_type' => $bkap_booking_type,
				'bkap_weekdays'     => $bkap_weekdays,
				'bkap_inline'       => $bkap_inline,
			);

			update_option( 'bkap_onboarding_booking_settings', $booking_settings );

			return self::response( 'success', array( 'message' => __( 'Booking Settings saved successfully.', 'woocommerce-booking' ) ) );
		}

		return self::response( 'error', array( 'error_description' => 'Error saving Booking Settings. Please try again.' ) );
	}

	/**
	 * Saves appearance settings data from onboarding.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function save_appearance_settings_data( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$data = $request->get_param( 'data' );

		$global_setting = bkap_global_setting();

		if ( is_array( $data ) && count( $data ) > 0 ) {

			$data                 = $request->get_param( 'data' );
			$booking_date_format  = self::check( $data, 'booking_date_format', '' );
			$booking_time_format  = self::check( $data, 'booking_time_format', '' );
			$booking_calendar_day = self::check( $data, 'booking_calendar_day', '' );
			$booking_themes       = self::check( $data, 'booking_themes', '' );

			$global_setting->booking_date_format  = $booking_date_format;
			$global_setting->booking_time_format  = $booking_time_format;
			$global_setting->booking_calendar_day = $booking_calendar_day;
			$global_setting->booking_themes       = $booking_themes;

			update_option( 'woocommerce_booking_global_settings', json_encode( $global_setting ) );

			return self::response( 'success', array( 'message' => 'Success' ) );
		}

		return self::response( 'error', array( 'error_description' => 'Unknown Error' ) );
	}

	/**
	 * Saves reschedule and cancel data from onboarding.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function save_reschedule_cancel_data( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$data           = $request->get_param( 'data' );
		$global_setting = bkap_global_setting();

		if ( is_array( $data ) && count( $data ) > 0 ) {

			$enable_booking_reschedule = self::check( $data, 'bkap_enable_booking_reschedule', '' );
			$reschedule_hours          = self::check( $data, 'bkap_booking_reschedule_hours', '' );
			$enable_booking_cancel     = self::check( $data, 'bkap_enable_booking_cancel', '' );
			$cancel_hours              = self::check( $data, 'bkap_booking_minimum_hours_cancel', '' );

			$global_setting->bkap_enable_booking_reschedule    = $enable_booking_reschedule;
			$global_setting->bkap_booking_reschedule_hours     = $reschedule_hours;
			$global_setting->bkap_booking_minimum_hours_cancel = $cancel_hours;

			update_option( 'woocommerce_booking_global_settings', json_encode( $global_setting ) );

			return self::response( 'success', array( 'message' => 'Success' ) );
		}

		return self::response( 'error', array( 'error_description' => 'Error saving Booking Reschedule and Cancel Settings. Please try again.' ) );
	}

	/**
	 * Saves appearance settings data from onboarding.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function save_labels_data( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$data = $request->get_param( 'data' );

		if ( is_array( $data ) && count( $data ) > 0 ) {

			$book_date_label     = self::check( $data, 'book_date_label', '' );
			$checkout_date_label = self::check( $data, 'checkout_date_label', '' );
			$book_time_label     = self::check( $data, 'book_time_label', '' );
			$bkap_add_to_cart    = self::check( $data, 'bkap_add_to_cart', '' );

			update_option( 'book_date-label', $book_date_label );
			update_option( 'checkout_date-label', $checkout_date_label );
			update_option( 'book_time-label', $book_time_label );
			update_option( 'bkap_add_to_cart', $bkap_add_to_cart );

			return self::response( 'success', array( 'message' => 'Success' ) );
		}

		return self::response( 'error', array( 'error_description' => 'Error saving Booking Reschedule and Cancel Settings. Please try again.' ) );
	}
}
