<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * REST API for Zoom.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/API/Zoom
 * @category    Classes
 * @since       5.19.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * BKAP REST API.
 *
 * @since 5.19.0
 */
class BKAP_Admin_API_Zoom extends BKAP_Admin_API {

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

		// Fetch Zoom data.
		register_rest_route(
			self::$base_endpoint,
			'zoom/fetch',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'fetch_zoom_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Save Zoom data.
		register_rest_route(
			self::$base_endpoint,
			'zoom/save',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'save_zoom_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Test Zoom Connection.
		register_rest_route(
			self::$base_endpoint,
			'zoom/test-connection',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'test_zoom_conection' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Assign Meeting to Bookings.
		register_rest_route(
			self::$base_endpoint,
			'zoom/assign-meeting-to-bookings',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'assign_meeting_to_bookings' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Zoom Logout.
		register_rest_route(
			self::$base_endpoint,
			'zoom/logout',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'zoom_logout' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// add zoom meeting manually.
		register_rest_route(
			self::$base_endpoint,
			'zoom/add-zoom-meeting',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'add_zoom_meeting' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);
	}

	/**
	 * Returns Zoom Data.
	 *
	 * @param bool $return_raw Whether to return the Raw response.
	 *
	 * @since 5.19.0
	 */
	public static function fetch_zoom_data( $return_raw = false ) {

		$zoom_enabled                          = bkap_zoom_connection_type();
		$zoom_connection                       = bkap_zoom_connection();
		$client_id                             = get_option( 'bkap_zoom_client_id', '' );
		$client_secret                         = get_option( 'bkap_zoom_client_secret', '' );
		$response                              = array();
		$response['bkap_zoom_client_id']       = $client_id;
		$response['bkap_zoom_client_secret']   = $client_secret;
		$response['bkap_zoom_connection_type'] = $zoom_enabled;
		$response['bkap_zoom_access_token']    = '';
		$response['bkap_zoom_connect_link']    = '';
		$response['bkap_zoom_redirect_uri']    = bkap_zoom_redirect_url();
		$response['bkap_zoom_logout_url']      = '';
		/* $response['bkap_zoom_success']         = '';
		$response['bkap_zoom_failure']         = ''; */
		$response['bkap_zoom_con_status']      = '';
		$response['settings']                  = array(
			'assign_meeting_status'   => get_option( 'bkap_assign_meeting_scheduled', false ),
			'is_zoom_meeting_enabled' => bkap_zoom_meeting_enable(),
		);

		if ( isset( $_GET['bkap_zoom_con_status'] )  ) { // phpcs:ignore
			$response['bkap_zoom_con_status'] = $_GET['bkap_zoom_con_status']; // phpcs:ignore
			/* switch ( $status ) {
				case 'success':
					$response['bkap_zoom_success'] = __( 'Successfully connected to Zoom.', 'woocommerce-booking' );
					break;
				case 'fail':
					$response['bkap_zoom_failure'] = __( 'Failed to connect to your account, please try again, if the problem persists or please contact Support team.', 'woocommerce-booking' );
					break;
			} */
		}

		if ( '' !== $client_id && '' !== $client_secret ) {
			$access_token = get_option( 'bkap_zoom_access_token', '' );
			if ( '' !== $access_token ) {
				$response['bkap_zoom_logout_url'] = bkap_zoom_redirect_url() . '&logout=1';
			} else {
				$zoom_connection->zoom_client_id    = $client_id;
				$response['bkap_zoom_connect_link'] = $zoom_connection->bkap_redirect_url();
			}
		}

		return self::return_response( $response, $return_raw );
	}

	/**
	 * Saves Zoom Data.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function save_zoom_data( WP_REST_Request $request ) {

		self::verify_nonce( $request );

		$data = $request->get_param( 'data' );

		if ( is_array( $data ) ) {

			foreach ( $data as $key => $setting ) {
				update_option( $key, $setting );
			}

			return self::response( 'success', array( 'data' => self::fetch_zoom_data( true ) ) );
		}

		return self::error();
	}

	/**
	 * Manually Adding the meeting to booking.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function add_zoom_meeting( WP_REST_Request $request ) {

		self::verify_nonce( $request );

		$booking_id   = $request->get_param( 'booking_id' );
		$meeting_link = $request->get_param( 'meeting_link' );

		$result = Bkap_Zoom_Meetings::bkap_add_zoom_meeting( array( 'booking_id' => $booking_id, 'meeting_link' => $meeting_link ) );

		if ( isset( $result['meeting_link'] ) && '' !== $result['meeting_link'] ) {
			return self::response( 'success', array( 'message' => __( 'Meeting has been added successfully', 'woocommerce-booking' ), 'meeting_link' => $result['meeting_link'] ) );
		} else {
			return self::response( 'error', array( 'error_description' => __( 'Please add link to the zoom meeting field.', 'woocommerce-booking' ) ) );
		}
	}

	/**
	 * Test Zoom Connection.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function test_zoom_conection( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$data       = $request->get_param( 'data' );
		$api_key    = self::check( $data, 'bkap_zoom_api_key', '' );
		$api_secret = self::check( $data, 'bkap_zoom_api_secret', '' );

		/* if ( '' !== $api_key && '' !== $api_secret ) {

			update_option( 'bkap_zoom_api_key', $api_key );
			update_option( 'bkap_zoom_api_secret', $api_secret ); */

			$zoom_connection = bkap_zoom_connection();
			$result          = bkap_json_decode( $zoom_connection->bkap_list_users() );

			if ( empty( $result ) ) {
				return self::response( 'error', array( 'error_description' => __( 'Unknown Zoom Error', 'woocommerce-booking' ) ) );
			}

			if ( ! empty( $result->code ) ) {
				return self::response( 'error', array( 'error_description' => $result->message ) );
			}

			if ( 200 !== http_response_code() ) {
				return self::response( 'error', array( 'error_description' => $result ) );
			}

			return self::response( 'success', array( 'data' => self::fetch_zoom_data( true ) ) );
		/* } */

		return self::response( 'error', array( 'error_description' => __( 'Zoom API Key or Secret is missing!', 'woocommerce-booking' ) ) );
	}

	/**
	 * Logout Zoom Connection.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function zoom_logout( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		delete_option( 'bkap_zoom_access_token' );
		delete_option( 'bkap_zoom_token_expiry' );
		delete_option( 'bkap_zoom_access_data' );

		return self::response( 'success', array( 'data' => self::fetch_zoom_data( true ) ) );
	}

	/**
	 * Assign Meeting to Bookings.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function assign_meeting_to_bookings( WP_REST_Request $request ) {

		self::verify_nonce( $request );

		if ( bkap_zoom_meeting_enable() ) {
			as_schedule_single_action( time() + 10, 'bkap_assign_meetings', array( 'test' => 20 ) );
			add_option( 'bkap_assign_meeting_scheduled', 'yes' );
			return self::success();
		}

		return self::error();
	}
}
