<?php
/**
 * Booking & Appointment Plugin for WooCommerce - Deactivation Class
 *
 * @version 1.1.7
 * @since   1.1.3
 * @author  Tyche Softwares
 * @package Booking & Appointment Plugin for WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Tyche_BKAP_Deactivation' ) ) {

	/** Declaration of Class */
	class Tyche_BKAP_Deactivation {

		/** Constructor */
		public function __construct() {
			if ( ! is_admin() ) {
				return;
			}

			BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/tyche/components/plugin-deactivation/class-tyche-plugin-deactivation.php' );

			new Tyche_Plugin_Deactivation(
				array(
					'plugin_name'       => EDD_SL_ITEM_NAME_BOOK,
					'plugin_base'       => 'woocommerce-booking/woocommerce-booking.php',
					'script_file'       => plugin_dir_url( BKAP_FILE ) . 'includes/tyche/assets/js/plugin-deactivation.js',
					'plugin_short_name' => 'bkap',
					'version'           => BKAP_VERSION,
				)
			);
		}
	}

	// Initialize the license class.
	new Tyche_BKAP_Deactivation();
}
