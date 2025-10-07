<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * Class for including BKAP files for the Admin.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/Files
 * @category    Classes
 * @since       5.19.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * BKAP Admin Files.
 *
 * @since 5.19.0
 */
class BKAP_Admin_Files {

	/**
	 * Construct
	 *
	 * @since 5.19.0
	 */
	public function __construct() {
		$this->include_files();
	}

	/**
	 * Include files.
	 *
	 * @since 5.19.0
	 */
	public function include_files() {

		// Common Files.
		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/common/bkap-common.php' );

		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/frontend/class-bkap-cancel-order.php' );
		new bkap_cancel_order();

		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/common/class-bkap-payment-gateway.php' );

		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/common/class-bkap-booking-confirmation.php' );
		new BKAP_Booking_Confirmation();

		// Background Process.
		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/includes/class-bkap-background-process.php' );

		// FluentCRM.
		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/includes/fluent-crm/class-bkap-fluentcrm.php' );

		// Functions.
		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/functions.php' );

		// Declared Functions for BKAP Classes.
		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/functions.php' );

		// Menu.
		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/class-bkap-admin.php' );
		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/class-bkap-admin-menu.php' );

		// Zapier.
		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/includes/zapier/class-bkap-zapier.php' );
		new BKAP_Zapier();

		// Twilio.
		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/libraries/twilio-php/vendor/Twilio/autoload.php' );
		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/includes/twilio/class-bkap-twilio.php' );
		new BKAP_Twilio();
		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/includes/twilio/class-bkap-sms-settings.php' );
		new BKAP_SMS_Settings();

		// Google Calendar.
		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/common/google-calendar/class-bkap-google-calendar-event.php' );
		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/common/google-calendar/class-bkap-product-google-calendar.php' );
		bkap_product_google_calendar();

		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/google/Client.php' );
		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/includes/google-calendar/class-bkap-google-calendar.php' );
		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/includes/google-calendar/class-bkap-oauth-google-calendar.php' );
		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/libraries/iCal/vendor/SG_iCal.php' );
		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/includes/google-calendar/class-bkap-google-calendar-sync.php' );
		bkap_google_calendar_sync();

		// Import Booking.
		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/includes/import-booking/class-bkap-admin-import-booking.php' );

		// Send Reminders.
		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/includes/send-reminders/class-bkap-admin-send-reminders.php' );
		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/common/bkap-send-reminder.php' );

		// Booking Class.
		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/common/class-bkap-booking.php' );

		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/common/class-bkap-person.php' );
		new BKAP_Person();

		// Filter the bookable products on the All products page at the Admin.
		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/includes/class-bkap-product-filter.php' );
		new BKAP_Product_Filter();

		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/includes/class-bkap-dashboard-widget.php' );
		new BKAP_Dashboard_Widget();

		// Booking Calendar.
		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/includes/class-bkap-admin-calendar.php' );
		new BKAP_Admin_Calendar();

		// Admin API.
		self::include_api_files();

		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/class-bkap-admin-scripts.php' );
		new BKAP_Admin_Scripts();

		// Bulk Booking Settings.
		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/includes/class-bkap-bulk-booking-settings.php' );
		bkap_bulk_booking_settings();

		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/includes/class-bkap-admin-system-status.php' );

		// Zoom.
		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/includes/zoom-meetings/class-bkap-zoom-meeting.php' );
	}

	/**
	 * Include API files.
	 *
	 * @since 5.19.0
	 */
	public function include_api_files() {
		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/api/class-bkap-admin-api.php' );

		$tyche_files = array(
			'class-tyche-bkap-updater.php',
			'class-tyche-bkap-license.php',
			'class-tyche-bkap-tracking.php',
			'class-tyche-bkap-deactivation.php',
			'class-bkap-tyche.php',
		);

		foreach ( $tyche_files as $tyche_file ) {
			if ( file_exists( BKAP_PLUGIN_PATH . '/includes/' . $tyche_file ) ) {
				require_once BKAP_PLUGIN_PATH . '/includes/' . $tyche_file;
			}
		}


		// Settings.
		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/api/class-bkap-admin-api-global-settings.php' );
		new BKAP_Admin_API_Global_Settings();

		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/api/class-bkap-admin-api-bulk-booking-settings.php' );
		new BKAP_Admin_API_Bulk_Booking_Settings();

		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/api/class-bkap-admin-api-product-availability-settings.php' );
		new BKAP_Admin_API_Product_Availability_Settings();

		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/api/class-bkap-admin-api-label.php' );
		new BKAP_Admin_API_Label();

		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/api/class-bkap-admin-api-calendar.php' );
		new BKAP_Admin_API_Calendar();

		// Integrations.
		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/api/class-bkap-admin-api-zoom.php' );
		new BKAP_Admin_API_Zoom();

		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/api/class-bkap-admin-api-zapier.php' );
		new BKAP_Admin_API_Zapier();

		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/api/class-bkap-admin-api-twilio.php' );
		new BKAP_Admin_API_Twilio();

		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/api/class-bkap-admin-api-google-calendar.php' );
		new BKAP_Admin_API_Google_Calendar();

		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/api/class-bkap-admin-api-fluent-crm.php' );
		new BKAP_Admin_API_FluentCRM();

		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/api/class-bkap-admin-api-outlook-calendar.php' );
		new BKAP_Admin_API_Outlook_Calendar();

		// Addons.
		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/api/class-bkap-admin-api-partial-deposits.php' );
		new BKAP_Admin_API_Partial_Deposits();

		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/api/class-bkap-admin-api-recurring-bookings.php' );
		new BKAP_Admin_API_Recurring_Bookings();

		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/api/class-bkap-admin-api-printable-tickets.php' );
		new BKAP_Admin_API_Printable_Tickets();

		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/api/class-bkap-admin-api-seasonal-pricing.php' );
		new BKAP_Admin_API_Seasonal_Pricing();

		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/api/class-bkap-admin-api-rental-system.php' );
		new BKAP_Admin_API_Rental_System();

		// Booking.
		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/api/class-bkap-admin-api-view-bookings.php' );
		new BKAP_Admin_API_View_Bookings();

		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/api/class-bkap-admin-api-create-booking.php' );
		new BKAP_Admin_API_Create_Booking();

		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/api/class-bkap-admin-api-import-booking.php' );
		new BKAP_Admin_API_Import_Booking();

		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/api/class-bkap-admin-api-reminders.php' );
		new BKAP_Admin_API_Reminders();

		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/api/class-bkap-admin-api-resources.php' );
		new BKAP_Admin_API_Resources();

		// Booking Metabox for the Edit Product Page.
		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/api/class-bkap-admin-api-metabox-booking.php' );
		new BKAP_Admin_API_Metabox_Booking();

		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/api/class-bkap-admin-api-onboarding.php' );
		new BKAP_Admin_API_Onboarding();
	}

	/**
	 * Loads an Admin View File.
	 *
	 * @param string $filename View File to be loaded.
	 * @since 5.19.0
	 */
	public static function load_view_file( $filename ) {
		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/views/' . $filename . '.php' );
	}

	/**
	 * Loads an Admin Section File.
	 *
	 * @param string $section Section Directory.
	 * @param string $filename File in the section Directory to be loaded.
	 * @since 5.19.0
	 */
	public static function load_section_file( $section, $filename = '' ) {

		if ( '' === $section ) {
			return;
		}

		$section_dir = BKAP_PLUGIN_PATH . '/includes/admin/views/' . $section;
		$file        = $section_dir . '/' . ( '' === $filename ? 'index.php' : self::do_file_check( $filename ) );

		// Check if file exists, else look in second location.
		if ( ! file_exists( $file ) ) {
			$section_dir = BKAP_PLUGIN_PATH . '/includes/tyche/views';
			$file        = $section_dir . '/' . ( '' === $filename ? 'index.php' : self::do_file_check( $filename ) );
		}

		BKAP_Files::include_file( $file );
	}

	/**
	 * Loads an Admin Page.
	 *
	 * @param string $section Section Directory.
	 * @param array  $pages Admin Pagesto be loaded.
	 * @param bool   $load_sub_navigation Whether to load the sub-navigation bar.
	 * @since 5.19.0
	 */
	public static function load_admin_pages( $section, $pages, $load_sub_navigation = true ) {
		self::load_view_file( 'header' );

		if ( is_array( $pages ) && count( $pages ) > 0 ) {
			foreach ( $pages as $page ) {
				self::load_dependencies( $section, $page );
				self::load_section_file( $section, $page );
			}
		}

		if ( $load_sub_navigation ) {
			self::load_view_file( 'main' );
		}

		self::load_view_file( 'footer' );
	}

	/**
	 * Perform file checks so as to load the appropriate file.
	 * Returns the file path to be rendered.
	 *
	 * @param string $filename Filename.
	 * @since 5.19.0
	 */
	public static function do_file_check( $filename ) {

		// License check.
		switch ( $filename ) {

			case 'fluent-crm':
				$filename = apply_filters( 'bkap_do_file_check_fluent_crm', $filename );
				$filename = bkap_fluentcrm()->bkap_fluentcrm_lite_active() && bkap_fluentcrm()->bkap_fluentcrm_pro_active() ? $filename : 'restricted/plugin-not-activated/' . $filename;
				break;

			case 'outlook-calendar':
				$filename = is_plugin_active( 'bkap-outlook-calendar/bkap-outlook-calendar.php' ) ? $filename : 'restricted/plugin-not-activated/' . $filename;
				break;

			case 'partial-deposits':
				$filename = is_plugin_active( 'bkap-deposits/deposits.php' ) ? $filename : 'restricted/plugin-not-activated/' . $filename;
				break;

			case 'printable-tickets':
				$filename = is_plugin_active( 'bkap-printable-tickets/printable-tickets.php' ) ? $filename : 'restricted/plugin-not-activated/' . $filename;
				break;

			case 'recurring-bookings':
				$filename = is_plugin_active( 'bkap-recurring-bookings/bkap-recurring-bookings.php' ) ? $filename : 'restricted/plugin-not-activated/' . $filename;
				break;

			case 'seasonal-pricing':
				$filename = is_plugin_active( 'bkap-seasonal-pricing/seasonal_pricing.php' ) && class_exists( 'Bkap_Seasonal_Pricing' ) ? $filename : 'restricted/plugin-not-activated/' . $filename;
				break;
			case 'rental-system':
				$filename = is_plugin_active( 'bkap-rental/rental.php' ) && class_exists( 'Bkap_Rental' ) ? $filename : 'restricted/plugin-not-activated/' . $filename;
				break;
			default:
				$filename = apply_filters( 'bkap_do_file_check', $filename );
				break;
		}

		return $filename . '.php';
	}

	/**
	 * Loads Dependency Files.
	 * If there are required files needed ( to be included before ) for the execution of the view file, those dependencies can be added here.
	 *
	 * @param string $section Section Directory.
	 * @param string $filename File in the section Directory to be loaded.
	 * @since 5.19.0
	 */
	public static function load_dependencies( $section, $filename ) {

		if ( '' === $section || '' === $filename ) {
			return;
		}

		if ( 'booking' === $section && 'import-booking' === $filename ) {
			BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/includes/import-booking/class-bkap-admin-import-booking-table.php' );
		}

		if ( 'view-reminders' === $filename ) {
			BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/includes/view-reminders/class-bkap-admin-view-reminders-table.php' );
		}

		if ( 'view-bookings' === $filename ) {
			BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/includes/view-bookings/class-bkap-admin-view-bookings-table.php' );
		}

		if ( 'resources' === $section ) {
			BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/includes/view-resources/class-bkap-admin-view-resources-table.php' );
		}

		if ( 'settings' === $section && 'bulk-booking-settings' === $filename ) {
			BKAP_Admin_Menu::product_meta_box();
		}
	}
}
