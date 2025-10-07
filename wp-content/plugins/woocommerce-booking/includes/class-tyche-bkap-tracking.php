<?php
/**
 * Booking & Appointment Plugin for WooCommerce - Tracking Class
 *
 * @version 1.1.7
 * @since   1.1.3
 * @author  Tyche Softwares
 * @package Booking & Appointment Plugin for WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Tyche_BKAP_Tracking' ) ) {

	/** Declaration of Class */
	class Tyche_BKAP_Tracking {

		/** Constructor */
		public function __construct() {

			BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/tyche/components/plugin-tracking/class-tyche-plugin-tracking.php' );

			add_filter( 'ts_tracker_data', array( __CLASS__, 'bkap_ts_add_plugin_data' ), 10, 1 );
			add_action( 'admin_footer', array( __CLASS__, 'ts_admin_notices_scripts' ) );
			add_action( 'admin_init', array( __CLASS__, 'ts_reset_tracking_setting' ) );
			add_action( 'bkap_init_tracker_completed', array( __CLASS__, 'init_tracker_completed' ), 10, 2 );

			add_action( 'bkap_enqueue_js_onboarding', array( $this, 'bkap_add_tracking_scripts' ) );


			new Tyche_Plugin_Tracking(
				array(
					'plugin_name'       => EDD_SL_ITEM_NAME_BOOK,
					'plugin_locale'     => 'woocommerce-booking',
					'plugin_short_name' => 'bkap',
					'version'           => BKAP_VERSION,
					'blog_link'         => 'https://www.tychesoftwares.com/booking-appointment-plugin-usage-tracking',
				)
			);
		}

		/**
		 * Enqueue the tracking scripts.
		 *
		 * @since 6.8
		 * @access public
		 */
		public function bkap_add_tracking_scripts() {
			wp_enqueue_script(
				'bkap-data-tracking',
				plugin_dir_url( __FILE__ ) . 'tyche/assets/js/plugin-tracking.js',
				array(),
				BKAP_VERSION,
				true
			);
		}

		/**
		 * Send the plugin data when the user has opted in
		 *
		 * @hook ts_tracker_data
		 * @param array $data All data to send to server
		 * @return array $plugin_data All data to send to server
		 */
		public static function bkap_ts_add_plugin_add_data( $data ) {

			global $booking_plugin_version;

			if ( isset( $_GET['bkap_tracker_optin'] ) && isset( $_GET['bkap_tracker_nonce'] ) && wp_verify_nonce( $_GET['bkap_tracker_nonce'], 'bkap_tracker_optin' ) ) {
				$data['plugin_data'] = array(
					'total_bookable_products'   => wp_json_encode( bkap_common::ts_get_all_bookable_products() ),
					'total_gcal_count'          => bkap_common::ts_get_event_counts(),
					'total_global_setting'      => wp_json_encode( bkap_common::ts_global_booking_setting() ),
					'bookable_products_setting' => wp_json_encode( bkap_common::ts_get_all_bookable_products_settings() ),
					'booking_counts'            => bkap_common::ts_get_booking_counts(),
				);
			}

			return $data;
		}

		/**
		 * Load the js file in the admin
		 *
		 * @since 6.8
		 * @access public
		 */
		public static function ts_admin_notices_scripts() {
			wp_enqueue_script(
				'bkap_ts_dismiss_notice',
				BKAP_Files::rewrite_asset_url( '/assets/js/dismiss-notice.js', BKAP_FILE ),
				array(),
				BKAP_VERSION,
				false
			);

			wp_localize_script(
				'bkap_ts_dismiss_notice',
				'bkap_ts_dismiss_notice',
				array(
					'ts_prefix_of_plugin' => 'bkap',
					'ts_admin_url'        => admin_url( 'admin-ajax.php' ),
				)
			);
		}

		/**
		 * It will delete the tracking option from the database.
		 */
		public static function ts_reset_tracking_setting() {
			if ( isset( $_GET ['ts_action'] ) && 'reset_tracking' == $_GET ['ts_action'] ) { // phpcs:disable WordPress.Security.NonceVerification
				Tyche_Plugin_Tracking::reset_tracker_setting( 'bkap' );
				$ts_url = remove_query_arg( 'ts_action' );
				wp_safe_redirect( $ts_url );
			}
		}

		/**
		 * Redirects after initializing the tracker.
		 */
		public static function init_tracker_completed() {
			header( 'Location: ' . admin_url( 'admin.php?page=bkap_page&action=settings#/' ) );
			exit;
		}
	}

	// Initialize the license class.
	new Tyche_BKAP_Tracking();
}
