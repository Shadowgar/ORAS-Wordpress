<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * REST API for Printable Tickets.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/API/PrintableTickets
 * @category    Classes
 * @since       5.19.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * BKAP REST API.
 *
 * @since 5.19.0
 */
class BKAP_Admin_API_Printable_Tickets extends BKAP_Admin_API {

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

		// Fetch Printable Tickets data.
		register_rest_route(
			self::$base_endpoint,
			'printable-tickets/fetch',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'fetch_printable_tickets_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Save Printable Tickets data.
		register_rest_route(
			self::$base_endpoint,
			'printable-tickets/save',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'save_printable_tickets_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);
	}

	/**
	 * Returns Printable Tickets Data.
	 *
	 * @param bool $return_raw Whether to return the Raw response.
	 *
	 * @since 5.19.0
	 */
	public static function fetch_printable_tickets_data( $return_raw = false ) {
		$response                               = array();
		$response['booking_printable_ticket']   = get_option( 'booking_printable_ticket', '' );
		$response['booking_send_ticket_method'] = get_option( 'booking_send_ticket_method', '' );
		return self::return_response( $response, $return_raw );
	}

	/**
	 * Saves Printable Tickets Data.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function save_printable_tickets_data( WP_REST_Request $request ) {

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
