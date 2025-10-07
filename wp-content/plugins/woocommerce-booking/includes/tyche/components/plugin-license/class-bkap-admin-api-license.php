<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * REST API for License.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/API/License
 * @category    Classes
 * @since       5.19.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * BKAP REST API.
 *
 * @since 5.19.0
 */
class BKAP_Admin_API_License extends BKAP_Admin_API {

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

		// Fetch license data.
		register_rest_route(
			self::$base_endpoint,
			'license/fetch',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'fetch_license_data' ),
				'permission_callback' => '__return_true',
			)
		);

		// Activate License.
		register_rest_route(
			self::$base_endpoint,
			'license/activate',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'activate_license' ),
				'permission_callback' => '__return_true',
			)
		);

		// Deactivate License.
		register_rest_route(
			self::$base_endpoint,
			'license/deactivate',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'deactivate_license' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Returns License Data.
	 *
	 * @param bool $return_raw Whether to return the Raw response.
	 *
	 * @since 5.19.0
	 */
	public static function fetch_license_data( $return_raw = false ) {

		$response = array();

		$response['bkap'] = array(
			'license_key'             => get_option( 'edd_sample_license_key', '' ),
			'license_type'            => get_option( 'edd_sample_license_type', '' ),
			'license_status'          => get_option( 'edd_sample_license_status', '' ),
			'disable_license_textbox' => 'valid' === get_option( 'edd_sample_license_status', '' ),
		);

		if ( is_plugin_active( 'bkap-outlook-calendar/bkap-outlook-calendar.php' ) ) {
			$response['outlook_calendar'] = array(
				'license_key'             => get_option( 'edd_outlook_calendar_license_key', '' ),
				'license_status'          => get_option( 'edd_outlook_calendar_license_status', '' ),
				'disable_license_textbox' => 'valid' === get_option( 'edd_outlook_calendar_license_status', '' ),
			);
		}

		if ( is_plugin_active( 'bkap-deposits/deposits.php' ) ) {
			$response['partial_deposits'] = array(
				'license_key'             => get_option( 'edd_sample_license_key_dep_book', '' ),
				'license_status'          => get_option( 'edd_sample_license_status_dep_book', '' ),
				'disable_license_textbox' => 'valid' === get_option( 'edd_sample_license_status_dep_book', '' ),
			);
		}

		if ( is_plugin_active( 'bkap-printable-tickets/printable-tickets.php' ) ) {
			$response['printable_tickets'] = array(
				'license_key'             => get_option( 'edd_sample_license_key_print_ticket_book', '' ),
				'license_status'          => get_option( 'edd_sample_license_status_print_ticket_book', '' ),
				'disable_license_textbox' => 'valid' === get_option( 'edd_sample_license_status_print_ticket_book', '' ),
			);
		}

		if ( is_plugin_active( 'bkap-recurring-bookings/bkap-recurring-bookings.php' ) ) {
			$response['recurring_bookings'] = array(
				'license_key'             => get_option( 'edd_sample_license_key_subscription_book', '' ),
				'license_status'          => get_option( 'edd_sample_license_status_subscription_book', '' ),
				'disable_license_textbox' => 'valid' === get_option( 'edd_sample_license_status_subscription_book', '' ),
			);
		}

		if ( is_plugin_active( 'bkap-seasonal-pricing/seasonal_pricing.php' ) ) {
			$response['seasonal_pricing'] = array(
				'license_key'             => get_option( 'edd_sample_license_key_ssl_book', '' ),
				'license_status'          => get_option( 'edd_sample_license_status_ssl_book', '' ),
				'disable_license_textbox' => 'valid' === get_option( 'edd_sample_license_status_ssl_book', '' ),
			);
		}

		if ( is_plugin_active( 'bkap-rental/rental.php' ) ) {
			$response['rental'] = array(
				'license_key'             => get_option( 'edd_sample_license_key_rental_book', '' ),
				'license_status'          => get_option( 'edd_sample_license_status_rental_book', '' ),
				'disable_license_textbox' => 'valid' === get_option( 'edd_sample_license_status_rental_book', '' ),
			);
		}

		if ( is_plugin_active( 'bkap-multiple-time-slot/multiple-time-slot.php' ) ) {
			$response['multiple_timeslots'] = array(
				'license_key'             => get_option( 'edd_sample_license_key_multiple_timeslot_book', '' ),
				'license_status'          => get_option( 'edd_sample_license_status_multiple_timeslot_book', '' ),
				'disable_license_textbox' => 'valid' === get_option( 'edd_sample_license_status_multiple_timeslot_book', '' ),
			);
		}

		if ( is_plugin_active( 'bkap-marketplace-integration/bkap-marketplace-integration.php' ) ) {
			$response['bkap_marketplace'] = array(
				'license_key'             => get_option( 'edd_bkap_wcfm_integration_license', '' ),
				'license_status'          => get_option( 'edd_bkap_wcfm_integration_license_status', '' ),
				'disable_license_textbox' => 'valid' === get_option( 'edd_bkap_wcfm_integration_license_status', '' ),
			);
		}

		return self::return_response( $response, $return_raw );
	}

	/**
	 * Activates the License.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function activate_license( WP_REST_Request $request ) {

		$license_key = sanitize_text_field( $request->get_param( 'license_key' ) );
		$plugin      = sanitize_text_field( $request->get_param( 'plugin' ) );

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		if ( '' === $license_key ) {
			return self::response( 'error', array( 'error_description' => __( 'Please provide a License Key for activation.', 'woocommerce-booking' ) ) );
		}

		$license              = self::get_plugin_class( $plugin );
		$license->license_key = $license_key;
		$response             = $license->activate_license();

		if ( true === is_bool( $response ) && true === $response ) {
			return self::response( 'success', array( 'license_data' => self::fetch_license_data( true ) ) );
		}

		return self::response( 'error', array( 'error_description' => $response ) );
	}

	/**
	 * Deactivates the License.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function deactivate_license( WP_REST_Request $request ) {

		self::verify_nonce( $request );

		$plugin  = sanitize_text_field( $request->get_param( 'plugin' ) );
		$license = self::get_plugin_class( $plugin );
		$license->deactivate_license();

		return self::success();
	}

	/**
	 * Gets class for the license based on the plugin/add-on.
	 *
	 * @param string $plugin Plugin.
	 * @return Object
	 *
	 * @since 5.19.0
	 */
	public static function get_plugin_class( $plugin ) {

		$class = null;

		switch ( $plugin ) {

			case 'outlook_calendar':
				$class = new BKAP_Admin_License(
					array(
						'plugin_name'               => 'Outlook Calendar Sync',
						'license_key_identifier'    => 'edd_outlook_calendar_license_key',
						'license_status_identifier' => 'edd_outlook_calendar_license_status',
					)
				);
				break;

			case 'partial_deposits':
				$class = new BKAP_Admin_License(
					array(
						'plugin_name'               => 'Partial Deposits Addon for the WooCommerce Booking and Appointment Plugin',
						'license_key_identifier'    => 'edd_sample_license_key_dep_book',
						'license_status_identifier' => 'edd_sample_license_status_dep_book',
					)
				);
				break;

			case 'printable_tickets':
				$class = new BKAP_Admin_License(
					array(
						'plugin_name'               => 'Printable Tickets Addon for WooCommerce Booking & Appointment Plugin',
						'license_key_identifier'    => 'edd_sample_license_key_print_ticket_book',
						'license_status_identifier' => 'edd_sample_license_status_print_ticket_book',
					)
				);
				break;

			case 'recurring_bookings':
				$class = new BKAP_Admin_License(
					array(
						'plugin_name'               => 'Recurring Bookings Addon for Booking and Appointment plugin',
						'license_key_identifier'    => 'edd_sample_license_key_subscription_book',
						'license_status_identifier' => 'edd_sample_license_status_subscription_book',
					)
				);
				break;

			case 'seasonal_pricing':
				$class = new BKAP_Admin_License(
					array(
						'plugin_name'               => 'Seasonal Pricing Addon for the WooCommerce Booking and Appointment Plugin',
						'license_key_identifier'    => 'edd_sample_license_key_ssl_book',
						'license_status_identifier' => 'edd_sample_license_status_ssl_book',
					)
				);
				break;
			case 'rental':
				$class = new BKAP_Admin_License(
					array(
						'plugin_name'               => 'Rental System Addon for Woocommerce Booking and Appointment Plugin',
						'license_key_identifier'    => 'edd_sample_license_key_rental_book',
						'license_status_identifier' => 'edd_sample_license_status_rental_book',
					)
				);
				break;
			case 'multiple_timeslots':
				$class = new BKAP_Admin_License(
					array(
						'plugin_name'               => 'Multiple Time Slot addon for WooCommerce Booking and Appointment Plugin',
						'license_key_identifier'    => 'edd_sample_license_key_multiple_timeslot_book',
						'license_status_identifier' => 'edd_sample_license_status_multiple_timeslot_book',
					)
				);
				break;
			case 'bkap_marketplace':
				$class = new BKAP_Admin_License(
					array(
						'plugin_name'               => 'Marketplace Integration for Booking & Appointment plugin',
						'license_key_identifier'    => 'edd_bkap_wcfm_integration_license',
						'license_status_identifier' => 'edd_bkap_wcfm_integration_license_status',
					)
				);
				break;

			default:
				$class = new BKAP_Admin_License();
				break;
		}

		return $class;
	}
}
