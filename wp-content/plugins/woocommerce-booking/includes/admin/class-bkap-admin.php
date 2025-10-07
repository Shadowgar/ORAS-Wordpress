<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * BKAP Admin Base Class.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/Files
 * @category    Classes
 * @since       5.19.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * BKAP Admin Base Class.
 *
 * @since 5.19.0
 */
class BKAP_Admin {

	/**
	 * Construct
	 *
	 * @since 5.19.0
	 */
	public function __construct() {
	}

	/**
	 * Checks if the user is on the Admin Section of the BKAP Plugin.
	 *
	 * @since 5.19.0
	 */
	public static function is_on_bkap_page() {
		global $pagenow;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return 'admin.php' === $pagenow && isset( $_GET['page'] ) && 'bkap_page' === $_GET['page'];
	}

	/**
	 * Checks if the user is on the Edit Product Page.
	 *
	 * @since 5.19.0
	 */
	public static function is_on_product_page() {
		global $pagenow;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return 'post.php' === $pagenow || 'post' === get_post_type() || ( 'post-new.php' === $pagenow && 'product' === get_post_type() );
	}

	/**
	 * Checks if the user is on the Bookings Section on the BKAP Admin Page.
	 *
	 * @since 5.19.0
	 */
	public static function is_on_bkap_booking_section_page() {
		global $pagenow;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return 'admin.php' === $pagenow && isset( $_GET['page'] ) && 'bkap_page' === $_GET['page'] && isset( $_GET['action'] ) && ( 'booking' === $_GET['action'] || 'settings' === $_GET['action'] || 'onboarding' === $_GET['action'] );
	}

	/**
	 * Checks if the user is on the BKAP Admin SettingsPage.
	 *
	 * @since 5.19.0
	 */
	public static function is_on_bkap_settings_page() {
		global $pagenow;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return 'admin.php' === $pagenow && isset( $_GET['page'] ) && 'bkap_page' === $_GET['page'] && isset( $_GET['action'] ) && 'settings' === $_GET['action'];
	}
}
