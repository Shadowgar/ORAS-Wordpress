<?php
/**
 * Bookings & Appointment Plugin for WooCommerce
 *
 * Class for Including plugin files.
 *
 * @author   Tyche Softwares
 * @package  BKAP/Files
 * @category Classes
 */

/**
 * Class BKAP_Files.
 *
 * @since 5.3.0
 * @since Updated 5.19.0
 */
class BKAP_Files {

	/**
	 * Constructor.
	 */
	public function __construct() {
		self::include_admin_files();
		self::include_files();
	}

	/**
	 * Include the plugin files for Admin.
	 *
	 * @since 5.19.0
	 */
	public static function include_admin_files() {
		self::include_file( BKAP_PLUGIN_PATH . '/includes/admin/class-bkap-admin-files.php' );
		new BKAP_Admin_Files();
	}

	/**
	 * Include the dependent plugin files.
	 *
	 * @since 1.7.0
	 * @since Updated 5.19.0
	 */
	public static function include_files() {
		self::include_file( BKAP_PLUGIN_PATH . '/includes/frontend/bkap-widget-product-search.php' );

		self::include_file( BKAP_PLUGIN_PATH . '/includes/frontend/bkap-availability-search.php' );
		new Bkap_Availability_Search();

		self::include_file( BKAP_PLUGIN_PATH . '/includes/frontend/class-bkap-webhooks.php' );
		Bkap_Webhooks::init();

		self::include_file( BKAP_PLUGIN_PATH . '/includes/common/class-bkap-create-booking.php' );
		new BKAP_Create_Booking();

		self::include_file( BKAP_PLUGIN_PATH . '/includes/frontend/class-bkap-bookable-query.php' );

		self::include_file( BKAP_PLUGIN_PATH . '/includes/admin/includes/view-bookings/class-bkap-admin-view-bookings-table.php' );


		self::include_file( BKAP_PLUGIN_PATH . '/includes/core/class-bkap-plugin-meta.php' );

		self::include_file( BKAP_PLUGIN_PATH . '/includes/core/class-bkap-privacy-policy.php' );
		new Bkap_Privacy_Policy();

		self::include_file( BKAP_PLUGIN_PATH . '/includes/core/class-bkap-personal-data-export.php' );
		new BKAP_Personal_Data_Export();

		self::include_file( BKAP_PLUGIN_PATH . '/includes/core/class-bkap-privacy-eraser.php' );
		new BKAP_Privacy_Eraser();

		self::include_file( BKAP_PLUGIN_PATH . '/includes/core/class-bkap-localization.php' );
		new Bkap_Localization();

		self::include_file( BKAP_PLUGIN_PATH . '/includes/frontend/bkap-block-pricing.php' );
		new bkap_block_booking();

		self::include_file( BKAP_PLUGIN_PATH . '/includes/frontend/bkap-special-booking-price.php' );
		new bkap_special_booking_price();

		self::include_file( BKAP_PLUGIN_PATH . '/includes/frontend/bkap-validation.php' );
		new Bkap_Validation();

		self::include_file( BKAP_PLUGIN_PATH . '/includes/common/bkap-common.php' );

		self::include_file( BKAP_PLUGIN_PATH . '/includes/common/bkap-booking-process.php' );
		self::include_file( BKAP_PLUGIN_PATH . '/includes/common/bkap-booking-box.php' );

		self::include_file( BKAP_PLUGIN_PATH . '/includes/frontend/bkap-checkout.php' );
		new bkap_checkout();

		self::include_file( BKAP_PLUGIN_PATH . '/includes/frontend/bkap-cart.php' );
		new bkap_cart();

		self::include_file( BKAP_PLUGIN_PATH . '/includes/frontend/class-bkap-ics.php' );
		new Bkap_Ics();

		self::include_file( BKAP_PLUGIN_PATH . '/includes/frontend/bkap-cancel-order.php' );

		self::include_file( BKAP_PLUGIN_PATH . '/includes/frontend/class-bkap-addon-settings.php' );
		new BKAP_Addon_Settings();

		self::include_file( BKAP_PLUGIN_PATH . '/includes/common/class-bkap-reminder.php' );

		self::include_file( BKAP_PLUGIN_PATH . '/includes/frontend/class-bkap-multidates.php' );
		new Bkap_Multidates();

		self::include_file( BKAP_PLUGIN_PATH . '/includes/frontend/class-bkap-timeslot-price.php' );
		new bkap_timeslot_price();

		self::include_file( BKAP_PLUGIN_PATH . '/includes/frontend/class-bkap-duration-time.php' );
		new Bkap_Duration_Time();

		self::include_file( BKAP_PLUGIN_PATH . '/includes/frontend/class-bkap-timezone-conversion.php' );
		new Bkap_Timezone_Conversion();

		self::include_file( BKAP_PLUGIN_PATH . '/includes/frontend/class-bkap-emails.php' );
		new BKAP_Emails();

		self::include_file( BKAP_PLUGIN_PATH . '/includes/frontend/bkap-variation-lockout.php' );
		new bkap_variations();

		self::include_file( BKAP_PLUGIN_PATH . '/includes/frontend/bkap-attribute-lockout.php' );
		new bkap_attributes();

		self::include_file( BKAP_PLUGIN_PATH . '/includes/frontend/class-bkap-edit-bookings.php' );
		global $edit_booking_class;
		$edit_booking_class = new bkap_edit_bookings_class();

		self::include_file( BKAP_PLUGIN_PATH . '/includes/frontend/class-bkap-rescheduled-order.php' );
		new bkap_rescheduled_order_class();

		self::include_file( BKAP_PLUGIN_PATH . '/includes/core/class-bkap-addon-compatibility.php' );
		new bkap_addon_compatibility_class();

		self::include_file( BKAP_PLUGIN_PATH . '/includes/frontend/class-bkap-list-booking.php' );
		new BKAP_List_Booking();

		self::include_file( BKAP_PLUGIN_PATH . '/includes/common/class-bkap-resources-cpt.php' );

		self::include_file( BKAP_PLUGIN_PATH . '/includes/common/class-bkap-product-resource.php' );
		new Class_Bkap_Product_Resource();

		self::include_file( BKAP_PLUGIN_PATH . '/includes/frontend/class-bkap-scripts.php' );
		new BKAP_Scripts();

		self::include_file( BKAP_PLUGIN_PATH . '/includes/common/class-bkap-endpoints.php' );
		new BKAP_Endpoints();

		self::include_file( BKAP_PLUGIN_PATH . '/includes/common/class-bkap-ajax.php' );
		new Bkap_Ajax();

		self::include_file( BKAP_PLUGIN_PATH . '/includes/common/class-bkap-cancel-booking.php' );
		new Bkap_Cancel_Booking();

		self::include_file( BKAP_PLUGIN_PATH . '/includes/frontend/class-bkap-coupons.php' );
		bkap_coupons();

		self::include_file( BKAP_PLUGIN_PATH . '/includes/frontend/class-bkap-import-export-bookable-products.php' );
		bkap_import_export_bookable_products();

		self::include_file( BKAP_PLUGIN_PATH . '/includes/common/class-bkap-product.php' );
		bkap_product();

		self::include_file( BKAP_PLUGIN_PATH . '/includes/admin/includes/fluent-crm/class-bkap-fluentcrm.php' );
		bkap_fluentcrm();

		if ( function_exists( 'woo_vou_default_settings' ) ) {
			self::include_file( BKAP_PLUGIN_PATH . '/includes/frontend/class-bkap-wc-voucher-pdf.php' );
			new Booking_Information_In_Voucher_Template();
		}

		self::include_vendor_files();

		if ( class_exists( 'PP_One_Page_Checkout' ) ) {
			self::include_file( BKAP_PLUGIN_PATH . '/includes/frontend/class-bkap-onepage-checkout.php' );
			new BKAP_OPC_Addon();
		}

		self::include_file( BKAP_PLUGIN_PATH . '/includes/api/zapier/class-bkap-api-zapier-log.php' );
		self::include_file( BKAP_PLUGIN_PATH . '/includes/api/zapier/class-bkap-api-zapier-settings.php' );

		self::include_file( BKAP_PLUGIN_PATH . '/includes/api/class-bkap-api-files.php' );
		new BKAP_API_Files();

		self::include_file( BKAP_PLUGIN_PATH . '/includes/api/class-bkap-rest-api.php' );
		new BKAP_REST_API();
		self::include_file( BKAP_PLUGIN_PATH . '/includes/api/class-bkap-rest-api-bookings-controller.php' );
		self::include_file( BKAP_PLUGIN_PATH . '/includes/api/class-bkap-rest-api-resources-controller.php' );
		self::include_file( BKAP_PLUGIN_PATH . '/includes/api/class-bkap-rest-api-products-controller.php' );

		self::include_file( BKAP_PLUGIN_PATH . '/includes/core/class-bkap-wc-hpos.php' );
		bkap_wc_hpos();
	}

	/**
	 * Include File.
	 *
	 * @param string $file File to be included.
	 * @since 5.19.0
	 */
	public static function include_file( $file ) {
		if ( file_exists( $file ) ) {
			include_once $file; // nosemgrep: audit.php.lang.security.file.inclusion-arg
		}
	}

	/**
	 * Return path/URL for asset file.
	 *
	 * @param string $path Path to the asset file.
	 * @param string $plugin The plugin file path to be relative to. Blank string if no plugin is specified.
	 * @param bool   $use_cdn Use CDN path.
	 * @param bool   $do_minification Whether to skip minification rewriting.
	 * @since 5.19.0
	 */
	public static function rewrite_asset_url( $path, $plugin = '', $use_cdn = false, $do_minification = true ) {

		$cdn           = BKAP_CDN . '/' . BKAP_VERSION;
		$path_with_cdn = $cdn . $path;

		if ( ! BKAP_DEV_MODE ) {

			// Skip the addition of .min. to filename, i.e. skip minification file.

			// Skip minified files.
			if ( $do_minification && false !== strpos( $path, '.min.' ) ) {
				$do_minification = false;
			}

			// Skip files in i18n folder.
			if ( $do_minification && false !== strpos( $path, '/i18n/' ) ) {
				$do_minification = false;
			}

			if ( $do_minification ) {
				$path = str_replace( '.css', '.min.css', $path );
				$path = str_replace( '.js', '.min.js', $path );
			}

			$path_with_cdn = $cdn . $path;
		} else {
			$_path         = '/cdn' . $path;
			$path_with_cdn = ( '' === $plugin ) ? plugins_url( $_path ) : plugins_url( $_path, $plugin );
		}

		if ( ! $use_cdn ) {
			$return_path = ( '' === $plugin ) ? plugins_url( $path ) : plugins_url( $path, $plugin );
		} else {
			$return_path = $path_with_cdn;
		}

		return $return_path;
	}

	/**
	 * Include vendor files.
	 *
	 * @since 5.19.0
	 */
	public static function include_vendor_files() {
		$vendor = false;

		if ( class_exists( 'WeDevs_Dokan' ) && apply_filters( 'bkap_bl_option', true ) ) {
			self::include_file( BKAP_VENDORS_INCLUDES_PATH . 'dokan/class-bkap-dokan-integration.php' );
			$vendor = true;
		}

		self::include_file( BKAP_VENDORS_INCLUDES_PATH . 'vendors-common.php' );

		if ( function_exists( 'is_wcvendors_active' ) && is_wcvendors_active() && apply_filters( 'bkap_bl_option', true ) ) {
			self::include_file( BKAP_VENDORS_INCLUDES_PATH . 'wc-vendors/wc-vendors.php' );
			$vendor = true;
		}

		if ( function_exists( 'is_wcfm_page' ) && apply_filters( 'bkap_bl_option', true ) ) {
			$vendor = true;
		}

		if ( $vendor ) {
			self::include_file( BKAP_VENDORS_INCLUDES_PATH . 'class-bkap-vendor-compatibility.php' );
			self::include_file( BKAP_VENDORS_INCLUDES_PATH . 'class-bkap-vendor-bookings.php' );
		}
	}
}
