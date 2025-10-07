<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * REST API for Twilio SMS.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/API/Twilio
 * @category    Classes
 * @since       5.19.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * BKAP REST API.
 *
 * @since 5.19.0
 */
class BKAP_Admin_API_Twilio extends BKAP_Admin_API {

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

		// Fetch Twilio data.
		register_rest_route(
			self::$base_endpoint,
			'twilio/fetch',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'fetch_twilio_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Save Twilio data.
		register_rest_route(
			self::$base_endpoint,
			'twilio/save',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'save_twilio_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Flush Logs.
		register_rest_route(
			self::$base_endpoint,
			'twilio/send-test-sms',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'send_test_sms' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);
	}

	/**
	 * Returns Twilio Data.
	 *
	 * @param bool $return_raw Whether to return the Raw response.
	 *
	 * @since 5.19.0
	 */
	public static function fetch_twilio_data( $return_raw = false ) {

		$response                = array();
		$twilio_settings         = bkap_json_decode( get_option( 'bkap_sms_settings' ) );
		$response['enable_sms']  = self::check( $twilio_settings, 'enable_sms' );
		$response['from']        = self::check( $twilio_settings, 'from' );
		$response['account_sid'] = self::check( $twilio_settings, 'account_sid' );
		$response['auth_token']  = self::check( $twilio_settings, 'auth_token' );
		return self::return_response( $response, $return_raw );
	}

	/**
	 * Saves Twilio Data.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function save_twilio_data( WP_REST_Request $request ) {

		self::verify_nonce( $request );

		$data            = $request->get_param( 'data' );
		$twilio_settings = bkap_json_decode( get_option( 'bkap_sms_settings' ) );

		if ( is_array( $data ) ) {
			foreach ( $data as $key => $setting ) {
				$twilio_settings[$key] = $setting;
			}

			update_option( 'bkap_sms_settings', $twilio_settings );
			return self::success();
		}

		return self::error();
	}

	/**
	 * Send Test SMS.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function send_test_sms( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$phone_number = $request->get_param( 'phone_number' );
		$message      = $request->get_param( 'message' );

		if ( '' === $phone_number ) {
			return self::response( 'error', array( 'error_description' => __( 'Please enter phone number.', 'woocommerce-booking' ) ) );
		}

		if ( '' === $message ) {
			return self::response( 'error', array( 'error_description' => __( 'Message body should not be empty.', 'woocommerce-booking' ) ) );
		}

		$response = BKAP_Twilio::bkap_send_test_sms( $phone_number, $message );

		if ( isset( $response['type'] ) ) {

			switch ( $response['type'] ) {
				case 'success':
					$response['text'] = __( 'SMS has been sent successfully.', 'woocommerce-booking' );
					$result           = self::response( 'success', $response );
					break;
				case 'error':
					$result = self::response( 'error', array( 'error_description' => $response['text'] ) );
					break;
			}

			return $result;
		}

		return self::response( 'error', array( 'error_description' => __( 'There is some problem sending the sms. Please verify the connection data or contact to the support for help.', 'woocommerce-booking' ) ) );
	}
}
