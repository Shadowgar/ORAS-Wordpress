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
class BKAP_Admin_API_Reset_Tracking extends BKAP_Admin_API {

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

		// Reset Tracking Data.
		register_rest_route(
			self::$base_endpoint,
			'global-settings/reset-tracking-data',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'reset_tracking_data' ),
				'permission_callback' => '__return_true',
			)
		);

		// Save Data tracking data.
		register_rest_route(
			self::$base_endpoint,
			'onboarding/data-tracking',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'save_data_tracking_data' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Reset Tracking Data.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function reset_tracking_data( WP_REST_Request $request ) {

		self::verify_nonce( $request );
		delete_option( 'bkap_allow_tracking' );
		delete_option( 'ts_tracker_last_send' );

		return self::success();
	}



	/**
	 * Saves data tracking data from onboarding.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function save_data_tracking_data( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$data = $request->get_param( 'data' );

		if ( is_array( $data ) && count( $data ) > 0 ) {

			$allow_tracking = self::check( $data, 'allow_tracking', '' );

			BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/tyche/components/plugin-tracking/class-tyche-plugin-tracking.php' );
			$plugin_tracking = new Tyche_Plugin_Tracking(
				array(
					'plugin_name'       => EDD_SL_ITEM_NAME_BOOK,
					'plugin_locale'     => 'woocommerce-booking',
					'plugin_short_name' => 'bkap',
					'version'           => BKAP_VERSION,
					'blog_link'         => 'https://www.tychesoftwares.com/booking-appointment-plugin-usage-tracking',
				)
			);

			if ( '' !== $allow_tracking ) {
				update_option( 'bkap_allow_tracking', 'yes' );
				$plugin_tracking->send_tracking_data();
			} else {
				update_option( 'bkap_allow_tracking', 'dismissed' );
				$plugin_tracking->send_tracking_data();
			}

			return self::response( 'success', array( 'message' => 'Success' ) );
		}

		return self::response( 'error', array( 'error_description' => 'Error saving option for data tracking. Please try again.' ) );
	}
}
