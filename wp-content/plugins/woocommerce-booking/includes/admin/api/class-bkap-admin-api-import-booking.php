<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * REST API for Import Booking.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/API/ImportBooking
 * @category    Classes
 * @since       5.19.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * BKAP REST API.
 *
 * @since 5.19.0
 */
class BKAP_Admin_API_Import_Booking extends BKAP_Admin_API {

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

		// Table Data.
		register_rest_route(
			self::$base_endpoint,
			'import-booking/table/display',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'return_table_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Map imported calendar events.
		register_rest_route(
			self::$base_endpoint,
			'import-booking/table/map-event',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'map_imported_calendar_event' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);
	}

	/**
	 * Returns Table Data.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function return_table_data( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$data  = $request->get_param( 'data' );
		$table = self::populate_data(
			array(
				'order'   => self::check( $data, 'order', 'asc' ),
				'orderby' => self::check( $data, 'orderby', 'title' ),
				'page'    => self::check( $data, 'page', 1 ),
				'search'  => self::check( $data, 'search', '' ),
				'status'  => self::check( $data, 'status', '' ),
			)
		);

		if ( ! $table ) {
			return self::response( 'error', array( 'error_description' => __( 'Error encountered while trying to populate table.', 'woocommerce-booking' ) ) );
		}

		return self::response( 'success', $table->ajax_response() );
	}

	/**
	 * Populate Data.
	 *
	 * @param bool $data Data.
	 *
	 * @since 5.19.0
	 */
	public static function populate_data( $data ) {

		// Load WordPress Administration APIs.
		require_once ABSPATH . 'wp-admin/includes/admin.php';

		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/includes/import-booking/class-bkap-admin-import-booking-table.php' );

		if ( is_array( $data ) && count( $data ) > 0 ) {
			$table = new BKAP_Admin_Import_Booking_Table();
			$table->populate_data( $data );

			return $table;
		}

		return false;
	}

	/**
	 * Map imported calendar events to the product.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function map_imported_calendar_event( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$data       = $request->get_param( 'data' );
		$post_id    = self::check( $data, 'id', '' );
		$product_id = self::check( $data, 'product_id', '' );
		$search     = self::check( $data, 'search', '' );

		$do_mapping_response = BKAP_Admin_Import_Booking::bkap_map_imported_event(
			array(
				'post_id'    => $post_id,
				'product_id' => $product_id,
				'post_type'  => 'by_post',
				'do_return'  => true,
			)
		);

		if ( '' === $do_mapping_response ) {
			return self::response( 'success', array( 'message' => __( 'The event has been mapped successfully.', 'woocommerce-booking' ) ) );
		}

		return self::response( 'error', array( 'error_description' => $do_mapping_response ) );
	}
}
