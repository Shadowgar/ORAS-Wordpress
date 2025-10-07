<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * REST API for Zapier.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/API/Zapier
 * @category    Classes
 * @since       5.19.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * BKAP REST API.
 *
 * @since 5.19.0
 */
class BKAP_Admin_API_Zapier extends BKAP_Admin_API {

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

		// Fetch Zapier data.
		register_rest_route(
			self::$base_endpoint,
			'zapier/fetch',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'fetch_zapier_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Save Zapier data.
		register_rest_route(
			self::$base_endpoint,
			'zapier/save',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'save_zapier_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Flush Logs.
		register_rest_route(
			self::$base_endpoint,
			'zapier/flush-logs',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'flush_zapier_logs' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);
	}

	/**
	 * Returns Zapier Data.
	 *
	 * @param bool $return_raw Whether to return the Raw response.
	 *
	 * @since 5.19.0
	 */
	public static function fetch_zapier_data( $return_raw = false ) {

		$response                                = array();
		$zapier_settings                         = bkap_json_decode( get_option( BKAP_Zapier::$settings_key ) );
		$response['bkap_api_zapier_integration'] = self::check( $zapier_settings, 'bkap_api_zapier_integration' );
		$response['bkap_api_zapier_log_enable']  = self::check( $zapier_settings, 'bkap_api_zapier_log_enable' );
		$response['trigger_create_booking']      = self::check( $zapier_settings, 'trigger_create_booking' );
		$response['trigger_update_booking']      = self::check( $zapier_settings, 'trigger_update_booking' );
		$response['trigger_delete_booking']      = self::check( $zapier_settings, 'trigger_delete_booking' );
		$response['action_create_booking']       = self::check( $zapier_settings, 'action_create_booking' );
		$response['action_update_booking']       = self::check( $zapier_settings, 'action_update_booking' );
		$response['action_delete_booking']       = self::check( $zapier_settings, 'action_delete_booking' );
		return self::return_response( $response, $return_raw );
	}

	/**
	 * Saves Zapier Data.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function save_zapier_data( WP_REST_Request $request ) {

		self::verify_nonce( $request );

		$data            = $request->get_param( 'data' );
		$zapier_settings = get_option( BKAP_Zapier::$settings_key );

		if ( is_array( $data ) ) {
			$zapier_settings = bkap_json_decode( get_option( BKAP_Zapier::$settings_key, '{}' ) );
			foreach ( $data as $key => $setting ) {
				$zapier_settings->{$key} = $setting;
			}

			update_option( BKAP_Zapier::$settings_key, wp_json_encode( $zapier_settings ) );
			return self::success();
		}

		return self::error();
	}

	/**
	 * Flush all Logs.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function flush_zapier_logs( WP_REST_Request $request ) {

		self::verify_nonce( $request );
		BKAP_Zapier::flush();
		return self::success();
	}
}
