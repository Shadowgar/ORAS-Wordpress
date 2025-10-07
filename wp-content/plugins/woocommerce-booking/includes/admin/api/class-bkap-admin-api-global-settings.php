<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * REST API for Global Settings.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/API/Global_Settings
 * @category    Classes
 * @since       5.19.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * BKAP REST API.
 *
 * @since 5.19.0
 */
class BKAP_Admin_API_Global_Settings extends BKAP_Admin_API {

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

		// Fetch Global Settings data.
		register_rest_route(
			self::$base_endpoint,
			'global-settings/fetch',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'fetch_global_settings_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Save Global Settings data.
		register_rest_route(
			self::$base_endpoint,
			'global-settings/save',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'save_global_settings_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Reset Tracking Data.
		register_rest_route(
			self::$base_endpoint,
			'global-settings/save-vendor-options',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'save_vendor_options_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);
	}

	/**
	 * Returns Global Setting Data.
	 *
	 * @param bool $return_raw Whether to return the Raw response.
	 *
	 * @since 5.19.0
	 */
	public static function fetch_global_settings_data( $return_raw = false ) {

		$response        = array();
		$global_settings = bkap_json_decode( get_option( 'woocommerce_booking_global_settings' ) );

		$response['booking_availability_display']       = self::check( $global_settings, 'booking_availability_display' );
		$response['booking_global_selection']           = self::check( $global_settings, 'booking_global_selection' );
		$response['same_bookings_in_cart']              = self::check( $global_settings, 'same_bookings_in_cart' );
		$response['minimum_day_booking']                = self::check( $global_settings, 'minimum_day_booking' );
		$response['global_booking_minimum_number_days'] = self::check( $global_settings, 'global_booking_minimum_number_days', 0 );
		$response['booking_timezone_conversion']        = self::check( $global_settings, 'booking_timezone_conversion' );
		$response['booking_global_timeslot']            = self::check( $global_settings, 'booking_global_timeslot' );
		$response['booking_global_holidays']            = self::check( $global_settings, 'booking_global_holidays' );
		$response['booking_include_global_holidays']    = self::check( $global_settings, 'booking_include_global_holidays' );
		$response['booking_attachment']                 = self::check( $global_settings, 'booking_attachment' );
		$response['display_disabled_buttons']           = self::check( $global_settings, 'display_disabled_buttons' );
		$response['booking_timeslot_display_mode']      = self::check( $global_settings, 'booking_timeslot_display_mode' );
		$response['booking_overlapping_timeslot']       = self::check( $global_settings, 'booking_overlapping_timeslot' );
		$response['bkap_auto_cancel_booking']           = self::check( $global_settings, 'bkap_auto_cancel_booking' );
		$response['bkap_booking_minimum_hours_cancel']  = self::check( $global_settings, 'bkap_booking_minimum_hours_cancel' );
		$response['show_order_info_note']               = self::check( $global_settings, 'show_order_info_note' );
		$response['bkap_enable_booking_edit']           = self::check( $global_settings, 'bkap_enable_booking_edit' );
		$response['bkap_enable_booking_reschedule']     = self::check( $global_settings, 'bkap_enable_booking_reschedule' );
		$response['bkap_booking_reschedule_hours']      = self::maybe_update_booking_reschedule_day_to_hour();
		$response['bkap_enable_booking_without_date']   = self::check( $global_settings, 'bkap_enable_booking_without_date' );
		$response['hide_variation_price']               = self::check( $global_settings, 'hide_variation_price' );
		$response['hide_booking_price']                 = self::check( $global_settings, 'hide_booking_price' );
		$response['resource_price_per_day']             = self::check( $global_settings, 'resource_price_per_day' );
		$response['woo_product_addon_price']            = self::check( $global_settings, 'woo_product_addon_price' );
		$response['woo_gf_product_addon_option_price']  = self::check( $global_settings, 'woo_gf_product_addon_option_price' );
		return self::return_response( $response, $return_raw );
	}

	/**
	 * Returns Hide Booking Options on Vendor Dashboard Data.
	 *
	 * @param bool $return_raw Whether to return the Raw response.
	 *
	 * @since 5.19.0
	 */
	public static function fetch_vendor_options_data( $return_raw = false ) {

		$options = get_option( 'bkap_hide_booking_options', array() );
		$options = is_array( $options ) ? $options : array();

		$default = array(
			'enable_booking'         => '',
			'booking_type'           => array(),
			'booking_type_section'   => '',
			'inline_calendar'        => '',
			'purchase_without_date'  => '',
			'requires_confirmation'  => '',
			'can_be_cancelled'       => '',
			'advance_booking_period' => '',
			'nod_to_choose'          => '',
			'max_booking_on_date'    => '',
			'min_no_of_nights'       => '',
			'max_no_of_nights'       => '',
			'resource'               => '',
			'persons'                => '',
			'google_calendar_export' => '',
			'google_calendar_import' => '',
			'zoom_meetings'          => '',
			'fluentcrm'              => '',
			'zapier'                 => '',
		);

		$response = array_merge( $default, $options );

		return self::return_response( $response, $return_raw );
	}

	/**
	 * Function to update 'day' values to 'hour' values
	 *
	 * @return int reschedule_hours.
	 * @since 5.19.0
	 */
	public static function maybe_update_booking_reschedule_day_to_hour() {

		$settings         = bkap_json_decode( get_option( 'woocommerce_booking_global_settings' ) );
		$reschedule_hours = ( isset( $settings->bkap_booking_reschedule_hours ) &&
		'' !== $settings->bkap_booking_reschedule_hours ) ? $settings->bkap_booking_reschedule_hours : 0;

		// Check if previous record exists for bkap_booking_reschedule_days. If it exists, convert to hours and update record.
		if ( isset( $settings->bkap_booking_reschedule_days ) ) {

			// Sometimes, bkap_booking_reschedule_days may still exist even when bkap_booking_reschedule_hours has been set. In that case, ignore bkap_booking_reschedule_days and use bkap_booking_reschedule_hours instead.

			if ( ! isset( $settings->bkap_booking_reschedule_hours ) && ( ( (int) $settings->bkap_booking_reschedule_days ) > 0 ) ) {
				$reschedule_hours                        = ( (int) $settings->bkap_booking_reschedule_days ) * 24;
				$settings->bkap_booking_reschedule_hours = $reschedule_hours;
			}

			// Delete bkap_booking_reschedule_days and update record.
			unset( $settings->bkap_booking_reschedule_days );
			update_option( 'woocommerce_booking_global_settings', wp_json_encode( $settings ) );
		}
		return $reschedule_hours;
	}

	/**
	 * Saves Global Settings Data.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function save_global_settings_data( WP_REST_Request $request ) {

		self::verify_nonce( $request );

		$data            = $request->get_param( 'data' );
		$global_settings = bkap_json_decode( get_option( 'woocommerce_booking_global_settings' ) );

		if ( is_array( $data ) ) {
			foreach ( $data as $key => $setting ) {

				$global_settings->{$key} = $setting;

				if ( 'bkap_auto_cancel_booking' === $key ) {
					if ( '' !== $setting && (int) $setting > 0 ) {
						if ( ! wp_next_scheduled( 'bkap_auto_cancel_booking' ) ) {
							wp_schedule_event( time(), 'hourly', 'bkap_auto_cancel_booking' );
						}
					} else {
						wp_clear_scheduled_hook( 'bkap_auto_cancel_booking' );
					}
				}
			}

			update_option( 'woocommerce_booking_global_settings', wp_json_encode( $global_settings ) );
			return self::response( 'success', array( 'data' => self::fetch_global_settings_data( true ) ) );
		}

		return self::error();
	}

	/**
	 * Saves vendor options data for Addons -> Vendor Options.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function save_vendor_options_data( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$data = $request->get_param( 'data' );

		if ( is_array( $data ) && count( $data ) > 0 ) {
			update_option( 'bkap_hide_booking_options', $data );
			return self::response( 'success', array( 'message' => __( 'Vendor options are saved successfully.', 'woocommerce-booking' ) ) );
		}

		return self::response( 'error', array( 'error_description' => __( 'Error saving vendor options. Please try again.', 'woocommerce-booking' ) ) );
	}
}
