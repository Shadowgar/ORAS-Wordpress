<?php
/**
 * Booking & Appointment Plugin for WooCommerce - License Class
 *
 * @version 1.1.7
 * @since   1.1.3
 * @author  Tyche Softwares
 * @package Booking & Appointment Plugin for WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Tyche_BKAP_License' ) ) {

	/** Declaration of Class */
	class Tyche_BKAP_License {

		/** Constructor */
		public function __construct() {

			add_filter(
				'bkap_home_files',
				function ( $files ) {
					$files[] = 'license';
					return $files;
				}
			);

			add_action( 'bkap_enqueue_js_home', array( $this, 'bkap_add_license_scripts' ) );

			BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/tyche/components/plugin-license/class-bkap-admin-license.php' );

			BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/tyche/components/plugin-license/license-active-notice/ts-active-license-notice.php' );
			new Bkap_Active_License_Notice( EDD_SL_ITEM_NAME_BOOK, 'edd_sample_license_status', 'admin.php?page=bkap_page#/license', 'woocommerce-booking' );

			BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/tyche/components/plugin-license/class-bkap-admin-api-license.php' );
			new BKAP_Admin_API_License();
		}

		public function bkap_add_license_scripts() {
			wp_register_script(
				'bkap-view-license',
				plugin_dir_url( BKAP_FILE ) . 'includes/tyche/assets/js/plugin-license.js',
				array(),
				BKAP_VERSION,
				true
			);

			wp_localize_script(
				'bkap-view-license',
				'bkap_view_license_param',
				array(
					'label' => array(
						'active_license'     => __( 'Active', 'woocommerce-booking' ),
						'inactive_license'   => __( 'Inactive', 'woocommerce-booking' ),
						'activate_license'   => __( 'Save & Activate', 'woocommerce-booking' ),
						'deactivate_license' => __( 'Deactivate', 'woocommerce-booking' ),
					),
					'data'  => BKAP_Admin_API_License::fetch_license_data( true ),
				)
			);
			wp_enqueue_script( 'bkap-view-license' );
		}
	}
	new Tyche_BKAP_License();
}

/**
 * Instance of BKAP_Admin_License.
 *
 * @return BKAP_Admin_License
 */
function bkap_admin_license() {
	return new BKAP_Admin_License();
}

/**
 * This function is for showing the notice on vendor dashboard.
 */
function bkap_vendor_license_check() {
	$license_active_message = bkap_admin_license()->license_inactive_error_message();
	if ( '' === $license_active_message ) {
		if ( ! apply_filters( 'bkap_bl_option', true ) ) {
			printf( '<div class="woocommerce-error">' );
			bkap_admin_license()->vendor_plugin_license_error_notice();
			printf( '</div>' );
		}
	} else {
		printf( '<div class="woocommerce-error">' );
		echo $license_active_message;
		printf( '</div>' );
	}
}
