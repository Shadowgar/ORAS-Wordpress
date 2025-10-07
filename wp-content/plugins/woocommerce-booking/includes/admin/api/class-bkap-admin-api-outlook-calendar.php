<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * REST API for Outlook Calendar.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/API/OutlookCalendar
 * @category    Classes
 * @since       5.19.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * BKAP REST API.
 *
 * @since 5.19.0
 */
class BKAP_Admin_API_Outlook_Calendar extends BKAP_Admin_API {

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

		// Fetch Outlook Calendar data.
		register_rest_route(
			self::$base_endpoint,
			'outlook-calendar/fetch',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'fetch_outlook_calendar_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Save Outlook Calendar data.
		register_rest_route(
			self::$base_endpoint,
			'outlook-calendar/save',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'save_outlook_calendar_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);
	}

	/**
	 * Returns Outlook Calendar Data.
	 *
	 * @param bool $return_raw Whether to return the Raw response.
	 *
	 * @since 5.19.0
	 */
	public static function fetch_outlook_calendar_data( $return_raw = false ) {
		$response = array();

		$response['bkap_outlook_calendar_event_location']    = get_option( 'bkap_outlook_calendar_event_location', '' );
		$response['bkap_outlook_calendar_event_summary']     = get_option( 'bkap_outlook_calendar_event_summary', '' );
		$response['bkap_outlook_calendar_event_description'] = get_option( 'bkap_outlook_calendar_event_description', '' );
		$response['bkap_outlook_calendar_integration']       = get_option( 'bkap_outlook_calendar_integration', 'disabled' );
		$response['bkap_outlook_calendar_client_key']        = get_option( 'bkap_outlook_calendar_client_key', '' );
		$response['bkap_outlook_calendar_client_secret']     = get_option( 'bkap_outlook_calendar_client_secret', '' );
		$response['bkap_outlook_calendar_id']                = get_option( 'bkap_outlook_calendar_id', '' );
		$response['connect_link']                            = '';
		$response['redirect_uri']                            = '';
		$response['logout_url']                              = '';
		$response['calendars']                               = array();

		if ( '' !== $response['bkap_outlook_calendar_client_key'] && '' !== $response['bkap_outlook_calendar_client_secret'] && class_exists( 'BKAP_Outlook_Calendar_OAuth' ) ) {
			try {
				$outlook = new BKAP_Outlook_Calendar_OAuth();
				$outlook->bkap_outlook_connect();
				$bkap_authorization_url   = $outlook->bkap_authorization_url();
				$response['connect_link'] = '' !== $bkap_authorization_url ? $bkap_authorization_url : 'javascript:void(0)';
				$response['redirect_uri'] = $outlook->bkap_get_redirect_uri();
				$response['logout_url']   = $outlook->bkap_logout_url();
				$response['calendars']    = $outlook->bkap_outlook_calendar_list();
			} catch ( Exception $e ) {
				// TODO: Display error message on front-end about caught exceptions.
			}
		}

		return self::return_response( $response, $return_raw );
	}

	/**
	 * Saves Outlook Calendar Data.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function save_outlook_calendar_data( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$data = $request->get_param( 'data' );

		if ( is_array( $data ) ) {

			foreach ( $data as $key => $setting ) {
				update_option( $key, $setting );
			}

			return self::response( 'success', array( 'data' => self::fetch_outlook_calendar_data( true ) ) );
		}

		return self::response( 'error', array( 'error_description' => 'Unknown Error' ) );
	}
}
