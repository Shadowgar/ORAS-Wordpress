<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * Admin Menu.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/Menu
 * @since       5.19.0
 * @category    Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class for adding the Admin Menus.
 */
class BKAP_Admin_Menu {

	/**
	 * Constructor.
	 *
	 * @since 5.19.0
	 */
	public function __construct() {
		add_action( 'admin_menu', array( &$this, 'admin_menu' ) );

		// Display Booking Box on Add/Edit Products Page.
		add_action( 'add_meta_boxes', array( $this, 'add_product_meta_box' ), 10 );
	}

	/**
	 * Adds the BKAP menu to the admin menu.
	 *
	 * @since 5.19.0
	 */
	public function admin_menu() {

		add_submenu_page(
			'woocommerce',
			__( 'Booking & Appointment', 'woocommerce-booking' ),
			__( 'Booking & Appointment', 'woocommerce-booking' ),
			'manage_woocommerce',
			'bkap_page',
			array( __CLASS__, 'admin_page' )
		);

		// Add the custom menu page using the add_menu_page function.
		add_menu_page(
			'Google Calendar Sync',
			'Google Calendar Sync',
			'manage_options',
			'woocommerce_booking_page',
			array( __CLASS__, 'woocommerce_booking_page' ),
			'',
			null
		);

		// Remove the menu page from the admin menu.
		remove_menu_page( 'woocommerce_booking_page' );
	}

	/**
	 * Added this function to the admin menu for View Bookings page submenu.
	 */
	public static function bkap_view_booking_page() {}

	/**
	 * Added this function to the admin menu for Settings page submenu.
	 */
	public static function bkap_settings_page() {}

	/**
	 * Added this function to the admin menu so that OAuth works fine with the earlier Redirect URLs.
	 */
	public static function woocommerce_booking_page() {}

	/**
	 * Displays the BKAP Settings menu item.
	 *
	 * @since 5.19.0
	 */
	public static function admin_page() {

		$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : ''; //phpcs:ignore
		if ( 'onboarding' !== $action ) {
			do_action( 'before_bkap_page_title' );
			?>

		<h2 class="bkap-page-title"><?php esc_html_e( BKAP_PLUGIN_NAME, 'woocommerce-booking' ); //phpcs:ignore ?></h2>

			<?php
		}
		settings_errors();
		?>

		<?php
		do_action( 'bkap_settings_tab_content_before', $action );

		$old_version     = get_option( 'wc_bkap_prev_db_version', BKAP_VERSION );
		$current_version = get_option( 'woocommerce_booking_db_version', BKAP_VERSION );

		if ( isset( $_GET['skip'] ) || $old_version != $current_version ) { // phpcs:ignore
			update_option( 'bkap_welcome_page_displayed', 'yes' );
		}

		switch ( $action ) {

			case 'onboarding':
				if ( 'yes' === get_option( 'bkap_welcome_page_displayed' ) ) {
					wp_safe_redirect( admin_url( 'admin.php?page=bkap_page' ) );
				}

				BKAP_Admin_Files::load_section_file( 'onboarding' );
				break;

			case 'settings':
				if ( '' === get_option( 'bkap_welcome_page_displayed', '' ) ) {
					wp_safe_redirect( admin_url( 'admin.php?page=bkap_page&action=onboarding' ) );
				}
				BKAP_Admin_Files::load_section_file( 'settings' );
				break;

			case 'appearance':
				BKAP_Admin_Files::load_section_file( 'appearance' );
				break;

			case 'integrations':
				BKAP_Admin_Files::load_section_file( 'integrations' );
				break;

			case 'addons':
				BKAP_Admin_Files::load_section_file( 'addons' );
				break;

			case 'booking':
				BKAP_Admin_Files::load_section_file( 'booking' );
				break;

			case 'reminders':
				BKAP_Admin_Files::load_section_file( 'reminders' );
				break;

			case 'resources':
				BKAP_Admin_Files::load_section_file( 'resources' );
				break;

			default:
				if ( '' === get_option( 'bkap_welcome_page_displayed', '' ) ) {
					wp_safe_redirect( admin_url( 'admin.php?page=bkap_page&action=onboarding' ) );
				}

				if ( isset( $_GET['action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					wp_safe_redirect( admin_url( 'admin.php?page=bkap_page' ) );
				}

				BKAP_Admin_Files::load_section_file( 'home' );
				break;
		}

		do_action( 'bkap_settings_tab_content_after', $action );
	}

	/**
	 * Adds the BKAP metabox to the Add/Edit Product page.
	 *
	 * @since 5.19.0
	 */
	public function add_product_meta_box() {

		add_meta_box(
			'woocommerce-booking-product-meta-box',
			__( 'Booking & Appointment', 'woocommerce-booking' ),
			array( __CLASS__, 'product_meta_box' ),
			'product',
			'normal',
			'core'
		);
	}

	/**
	 * Booking metabox for Add/Edit Product page.
	 *
	 * @since 5.19.0
	 */
	public static function product_meta_box() {

		wc_get_template(
			'metabox/booking/index.php',
			array(),
			'woocommerce-booking/includes/admin/templates/',
			BKAP_PLUGIN_PATH . '/includes/admin/templates/'
		);

		if ( ! BKAP_Admin::is_on_bkap_settings_page() ) {
			BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/views/metabox/booking/index.php' );
		}
	}
}

new BKAP_Admin_Menu();
