<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * REST API for Rental System.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/API/RentalSystem
 * @category    Classes
 * @since       5.19.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * BKAP REST API.
 *
 * @since 5.19.0
 */
class BKAP_Admin_API_Rental_System extends BKAP_Admin_API {

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

		// Fetch Seasonal Pricing data.
		register_rest_route(
			self::$base_endpoint,
			'rental-system/fetch',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'fetch_rental_system_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Save Seasonal Pricing data.
		register_rest_route(
			self::$base_endpoint,
			'rental-system/save',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'save_rental_system_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);
	}

	/**
	 * Returns Seasonal Pricing Data.
	 *
	 * @param bool $return_raw Whether to return the Raw response.
	 *
	 * @since 5.19.0
	 */
	public static function fetch_rental_system_data( $return_raw = false ) {
		$response = array();

		$seasonal_settings                       = bkap_json_decode( get_option( 'woocommerce_booking_global_settings' ) );
		$response['booking_global_product_page'] = self::check( $seasonal_settings, 'booking_global_product_page' );
		$response['bkap_rent']                   = get_option( 'bkap_rent', '' );
		$response['bkap_sale']                   = get_option( 'bkap_sale', '' );

		return self::return_response( $response, $return_raw );
	}

	/**
	 * Saves Seasonal Pricing Data.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function save_rental_system_data( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$data            = $request->get_param( 'data' );
		$global_settings = bkap_json_decode( get_option( 'woocommerce_booking_global_settings' ) );

		if ( is_array( $data ) ) {

			if ( is_null( $global_settings ) ) {
				$global_settings = new stdClass();
			}

			if ( isset( $data['booking_global_product_page'] ) ) {
				$global_settings->booking_global_product_page = $data['booking_global_product_page'];
			}

			update_option( 'woocommerce_booking_global_settings', wp_json_encode( $global_settings ) );

			if ( isset( $data['bkap_rent'] ) ) {
				update_option( 'bkap_rent', $data['bkap_rent'] );
			}
			if ( isset( $data['bkap_sale'] ) ) {
				update_option( 'bkap_sale', $data['bkap_sale']  );
			}
			return self::response( 'success', array() );
		}

		return self::response( 'error', array( 'error_description' => 'Unknown Error' ) );
	}
}
