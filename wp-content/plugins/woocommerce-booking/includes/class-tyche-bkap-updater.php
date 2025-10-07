<?php
/**
 * Booking & Appointment Plugin for WooCommerce - Updater Class
 *
 * @version 1.1.7
 * @since   1.1.3
 * @author  Tyche Softwares
 * @package Booking & Appointment Plugin for WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Tyche_BKAP_Updater' ) ) {

	/** Declaration of Class */
	class Tyche_BKAP_Updater {

		/** Constructor */
		public function __construct() {
			add_action( 'bkap_plugin_setup_after_file_include', array( $this, 'bkap_plugin_updater' ) );
		}

		/**
		 * Loads the Plugin Updater class.
		 */
		public static function bkap_plugin_updater() {

			require_once BKAP_BOOKINGS_INCLUDE_PATH . 'tyche/components/plugin-updater/class-bkap-plugin-updater.php';

			new BKAP_Plugin_Updater(
				'https://www.tychesoftwares.com/',
				BKAP_FILE,
				array(
					'version'   => BKAP_VERSION,
					'license'   => trim( get_option( 'edd_sample_license_key' ) ),
					'item_name' => 'Booking & Appointment Plugin for WooCommerce',
					'author'    => 'Ashok Rane',
				)
			);
		}
	}

	// Initialize the updater class.
	new Tyche_BKAP_Updater();
}
