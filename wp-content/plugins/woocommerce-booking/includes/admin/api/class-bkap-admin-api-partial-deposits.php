<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * REST API for Partial Deposits.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/API/PartialDeposits
 * @category    Classes
 * @since       5.19.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * BKAP REST API.
 *
 * @since 5.19.0
 */
class BKAP_Admin_API_Partial_Deposits extends BKAP_Admin_API {

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

		// Fetch Partial Deposits data.
		register_rest_route(
			self::$base_endpoint,
			'partial-deposits/fetch',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'fetch_partial_deposits_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Save Partial Deposits data.
		register_rest_route(
			self::$base_endpoint,
			'partial-deposits/save',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'save_partial_deposits_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);
	}

	/**
	 * Returns Partial Deposits Data.
	 *
	 * @param bool $return_raw Whether to return the Raw response.
	 *
	 * @since 5.19.0
	 */
	public static function fetch_partial_deposits_data( $return_raw = false ) {
		$response                                      = array();
		$response['bkap_partial_payment_disabled_msg'] = get_option( 'bkap_partial_payment_disabled_msg', '' );
		$response['bkap_deposit_amt_label']            = get_option( 'bkap_deposit_amt_label', '' );
		$response['bkap_product_amt_label']            = get_option( 'bkap_product_amt_label', '' );
		$response['bkap_deposit_payment_woocommerc_order_fields'] = get_option( 'bkap_deposit_payment_woocommerc_order_fields', '' );
		$response['bkap_deposit_payment_view_bookings_fields']    = get_option( 'bkap_deposit_payment_view_bookings_fields', '' );
		$response['bkap_deposit_payment_one_order']               = get_option( 'bkap_deposit_payment_one_order', '' );
		$response['bkap_remaining_amt_label']                     = get_option( 'bkap_remaining_amt_label', '' );
		$response['bkap_total_amt_label']                         = get_option( 'bkap_total_amt_label', '' );
		return self::return_response( $response, $return_raw );
	}

	/**
	 * Saves Partial Deposits Data.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function save_partial_deposits_data( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$data = $request->get_param( 'data' );

		if ( is_array( $data ) ) {

			foreach ( $data as $key => $setting ) {
				update_option( $key, $setting );
			}

			return self::response( 'success', array() );
		}

		return self::response( 'error', array( 'error_description' => 'Unknown Error' ) );
	}
}
