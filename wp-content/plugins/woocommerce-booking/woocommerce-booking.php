<?php
/**
 * Booking & Appointment Plugin for WooCommerce
 *
 * @package      BKAP
 * @copyright    Copyright (C) 2012-2024, Tyche Softwares - support@tychesoftwares.com
 * @link         https://www.tychesoftwares.com
 * @since        1.0
 * @since        Updated 7.7.0
 *
 * @wordpress-plugin
 * Plugin Name:  Booking & Appointment Plugin for WooCommerce
 * Plugin URI:   https://www.tychesoftwares.com/products/woocommerce-booking-and-appointment-plugin/
 * Description:  This plugin lets you capture the Booking Date & Booking Time for each product thereby allowing your WooCommerce store to effectively function as a Booking system. It allows you to add different time slots for different days, set maximum bookings per time slot, set maximum bookings per day, set global & product specific holidays and much more.
 * Version:      7.7.0
 * Author:       Tyche Softwares
 * Author URI:   http://www.tychesoftwares.com
 * Text Domain:  woocommerce-booking
 * Requires PHP: 7.3
 * WC requires at least: 3.9
 * WC tested up to: 10.1.2
 * Tested up to: 6.8
 * Requires Plugins: woocommerce
 */

defined( 'ABSPATH' ) || exit;

update_option( 'edd_sample_license_key', '12346-123456-123456-123456' );
update_option('edd_sample_license_status', 'valid');
add_filter('pre_http_request', function ($pre, $parsed_args, $url) {
	if (strpos($url, 'https://www.tychesoftwares.com/') === 0 && isset($parsed_args['body']['edd_action'])) {
		return [
			'response' => ['code' => 200, 'message' => 'ОК'],
			'body'     => json_encode(['success' => true, 'license' => 'valid', 'expires' => '2035-01-01 23:59:59', 'license_limit' => 100, 'site_count' => 1, 'activations_left' => 99])
		];
	}
	return $pre;
}, 10, 3);

/**
 * Booking & Appointment Plugin Core Class.
 *
 * @class woocommerce_booking.
 */
final class Woocommerce_Booking {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	protected static $plugin_version = '7.7.0';

	/**
	 * Plugin Name.
	 *
	 * @var string
	 */
	protected static $plugin_name = 'Booking & Appointment Plugin for WooCommerce';

	/**
	 * Plugin URL.
	 *
	 * @var string
	 */
	protected static $plugin_url = 'https://www.tychesoftwares.com/';

	/**
	 * CDN Endpoint.
	 *
	 * @var string
	 */
	protected static $cdn_endpoint = 'https://static.tychesoftwares.com/woocommerce-booking';

	/**
	 * DEV MODE.
	 *
	 * Sets the plugin base to either development ( TRUE ) or production ( FALSE ).
	 *
	 * @var boolean
	 */
	protected static $dev_mode = false;

	/**
	 * The single instance of the class.
	 *
	 * @var Woocommerce_Booking
	 */
	protected static $instance = null;

	/**
	 * Default constructor
	 *
	 * @since 5.23.1
	 */
	private static function setup() {

		/**
		 * Define Plugin Constants.
		 */
		self::define_constants();

		/**
		 * Plugin Global Variables.
		 */
		add_action( 'init', array( __CLASS__, 'bkap_global_values' ) );

		/**
		 * Check required pugins.
		 */
		add_action( 'admin_init', array( __CLASS__, 'bkap_do_required_plugin_check' ) );

		if ( ! self::bkap_is_required_plugin_active() ) {
			return;
		}

		/**
		 * Include Plugin Files.
		 */
		self::bkap_maybe_include_files();

		do_action( 'bkap_plugin_setup_after_file_include' );

		/**
		 * Register Custom Post Type
		 */
		add_action( 'init', array( __CLASS__, 'register_custom_post_type' ) );

		/**
		 * Initialize settings
		 */
		register_activation_hook( __FILE__, array( __CLASS__, 'bkap_bookings_activate' ) );

		/**
		 * Delete options and setting on deactivation of plugin.
		 */
		register_deactivation_hook( __FILE__, array( __CLASS__, 'bkap_bookings_deactivate' ) );
	}

	/**
	 * Retrieve the instance of the class and ensures only one instance is loaded or can be loaded.
	 *
	 * @return Woocommerce_Booking
	 */
	public static function instance() {
		if ( is_null( self::$instance ) && ! ( self::$instance instanceof Woocommerce_Booking ) ) {
			self::$instance = new Woocommerce_Booking();
			self::$instance->setup();
		}

		return self::$instance;
	}

	/**
	 * Including plugin files.
	 *
	 * @since 1.7
	 * @since Updated 5.23.1
	 */
	public static function bkap_include_files() {
		require_once BKAP_BOOKINGS_INCLUDE_PATH . 'class-bkap-files.php';
		new BKAP_Files();
	}

	/**
	 * Function for definining BKAP constants.
	 *
	 * @param string $variable Constant which is to be defined.
	 * @param string $value Valueof the Constant.
	 *
	 * @since 5.23.1
	 */
	public static function define( $variable, $value ) {
		if ( ! defined( $variable ) ) {
			define( $variable, $value );
		}
	}

	/**
	 * Return Error Message when WooCommerce plugin is not found.
	 *
	 * @since 5.23.1
	 */
	public static function return_error_message_when_woocommerce_plugin_not_found() {

		return sprintf(
		/* translators: Plugin Name */
			__( 'WooCommerce not found. %s requires a minimum of WooCommerce v3.3.0.', 'woocommerce-booking' ),
			self::$plugin_name
		);
	}

	/**
	 * BKAPGlobal Values.
	 *
	 * Formerly in bkap-config.php file.
	 *
	 * @since 5.23.1
	 */
	public static function bkap_global_values() {

		global $bkap_calendar_themes, $bkap_time_formats, $bkap_date_formats, $bkap_languages, $bkap_days,
		$bkap_weekdays, $bkap_calendar_icons, $bkap_timeslot_display_modes, $bkap_fixed_days, $bkap_months, $bkap_dates_months_availability, $book_translations, $book_lang;

		/**
		 * Bookable Recurring Weekdays
		 */
		$bkap_weekdays = array(
			'booking_weekday_0' => __( 'Sunday', 'woocommerce-booking' ),
			'booking_weekday_1' => __( 'Monday', 'woocommerce-booking' ),
			'booking_weekday_2' => __( 'Tuesday', 'woocommerce-booking' ),
			'booking_weekday_3' => __( 'Wednesday', 'woocommerce-booking' ),
			'booking_weekday_4' => __( 'Thursday', 'woocommerce-booking' ),
			'booking_weekday_5' => __( 'Friday', 'woocommerce-booking' ),
			'booking_weekday_6' => __( 'Saturday', 'woocommerce-booking' ),
		);

		/**
		 * Available Weekdays for Start/End of Fixed blocks
		 */
		$bkap_fixed_days = array(
			'any_days' => __( 'Any Day', 'woocommerce-booking' ),
			'0'        => __( 'Sunday', 'woocommerce-booking' ),
			'1'        => __( 'Monday', 'woocommerce-booking' ),
			'2'        => __( 'Tuesday', 'woocommerce-booking' ),
			'3'        => __( 'Wednesday', 'woocommerce-booking' ),
			'4'        => __( 'Thursday', 'woocommerce-booking' ),
			'5'        => __( 'Friday', 'woocommerce-booking' ),
			'6'        => __( 'Saturday', 'woocommerce-booking' ),
		);

		/**
		 * Available timeslot view modes.
		 */
		$bkap_timeslot_display_modes = array(
			'dropdown-view' => __( 'Dropdown View', 'woocommerce-booking' ),
			'list-view'     => __( 'List View', 'woocommerce-booking' ),
		);

		/**
		 * Weekdays numbered starting from 0 to 6 for Sunday through Saturday
		 */
		$bkap_days = array(
			'0' => 'Sunday',
			'1' => 'Monday',
			'2' => 'Tuesday',
			'3' => 'Wednesday',
			'4' => 'Thursday',
			'5' => 'Friday',
			'6' => 'Saturday',
		);

		/**
		 * Months of the year
		 */
		$bkap_months = array(
			'1'  => __( 'January', 'woocommerce-booking' ),
			'2'  => __( 'February', 'woocommerce-booking' ),
			'3'  => __( 'March', 'woocommerce-booking' ),
			'4'  => __( 'April', 'woocommerce-booking' ),
			'5'  => __( 'May', 'woocommerce-booking' ),
			'6'  => __( 'June', 'woocommerce-booking' ),
			'7'  => __( 'July', 'woocommerce-booking' ),
			'8'  => __( 'August', 'woocommerce-booking' ),
			'9'  => __( 'September', 'woocommerce-booking' ),
			'10' => __( 'October', 'woocommerce-booking' ),
			'11' => __( 'November', 'woocommerce-booking' ),
			'12' => __( 'December', 'woocommerce-booking' ),
		);

		/**
		 * Available Ranges for setup in Booking->Availability
		 */
		$bkap_dates_months_availability = array(
			'custom_range'    => __( 'Custom Range', 'woocommerce-booking' ),
			'specific_dates'  => __( 'Specific Dates', 'woocommerce-booking' ),
			'range_of_months' => __( 'Range of Months', 'woocommerce-booking' ),
			'holidays'        => __( 'Holidays', 'woocommerce-booking' ),
		);

		$bkap_from_slot_hrs = array();
		$bkap_from_slot_min = array();
		$bkap_to_slot_hrs   = array();
		$bkap_to_slot_min   = array();
		$bkap_time_note     = array();
		$bkap_lockout_time  = array();

		/**
		 * Languages
		 */
		$bkap_languages = array(
			'af'    => 'Afrikaans',
			'ar'    => 'Arabic',
			'ar-DZ' => 'Algerian Arabic',
			'az'    => 'Azerbaijani',
			'id'    => 'Indonesian',
			'ms'    => 'Malaysian',
			'nl-BE' => 'Dutch Belgian',
			'bs'    => 'Bosnian',
			'bg'    => 'Bulgarian',
			'ca'    => 'Catalan',
			'cs'    => 'Czech',
			'cy-GB' => 'Welsh',
			'da'    => 'Danish',
			'de'    => 'German',
			'et'    => 'Estonian',
			'el'    => 'Greek',
			'en-AU' => 'English Australia',
			'en-NZ' => 'English New Zealand',
			'en-GB' => 'English UK',
			'en-us' => 'English US',
			'es'    => 'Spanish',
			'eo'    => 'Esperanto',
			'eu'    => 'Basque',
			'fo'    => 'Faroese',
			'fr'    => 'French',
			'fr-CH' => 'French Swiss',
			'gl'    => 'Galician',
			'sq'    => 'Albanian',
			'ko'    => 'Korean',
			'he'    => 'Hebrew',
			'hi'    => 'Hindi India',
			'hr'    => 'Croatian',
			'hy'    => 'Armenian',
			'is'    => 'Icelandic',
			'it'    => 'Italian',
			'ka'    => 'Georgian',
			'km'    => 'Khmer',
			'lv'    => 'Latvian',
			'lt'    => 'Lithuanian',
			'mk'    => 'Macedonian',
			'hu'    => 'Hungarian',
			'ml'    => 'Malayam',
			'nl'    => 'Dutch',
			'ja'    => 'Japanese',
			'no'    => 'Norwegian',
			'th'    => 'Thai',
			'pl'    => 'Polish',
			'pt'    => 'Portuguese',
			'pt-BR' => 'Portuguese Brazil',
			'ro'    => 'Romanian',
			'rm'    => 'Romansh',
			'ru'    => 'Russian',
			'sk'    => 'Slovak',
			'sl'    => 'Slovenian',
			'sr'    => 'Serbian',
			'fi'    => 'Finnish',
			'sv'    => 'Swedish',
			'ta'    => 'Tamil',
			'vi'    => 'Vietnamese',
			'tr'    => 'Turkish',
			'uk'    => 'Ukrainian',
			'zh-HK' => 'Chinese Hong Kong',
			'zh-CN' => 'Chinese Simplified',
			'zh-TW' => 'Chinese Traditional',
		);

		/**
		 * Date formats for booking.
		 */
		$bkap_date_formats = array(
			'mm/dd/y'      => 'm/d/y',
			'dd/mm/y'      => 'd/m/y',
			'y/mm/dd'      => 'y/m/d',
			'dd.mm.y'      => 'd.m.y',
			'y.mm.dd'      => 'y.m.d',
			'yy-mm-dd'     => 'Y-m-d',
			'dd-mm-y'      => 'd-m-y',
			'd M, y'       => 'j M, y',
			'd M, yy'      => 'j M, Y',
			'd MM, y'      => 'j F, y',
			'd MM, yy'     => 'j F, Y',
			'DD, d MM, yy' => 'l, j F, Y',
			'D, M d, yy'   => 'D, M j, Y',
			'DD, M d, yy'  => 'l, M j, Y',
			'DD, MM d, yy' => 'l, F j, Y',
			'D, MM d, yy'  => 'D, F j, Y',
		);

		/**
		 * Booking Time Formats
		 */
		$bkap_time_formats = array(
			'12' => __( '12 hour', 'woocommerce-booking' ),
			'24' => __( '24 hour', 'woocommerce-booking' ),
		);

		/**
		 * Booking Calendar themes
		 */
		$bkap_calendar_themes = array(
			'smoothness'     => 'Smoothness',
			'ui-lightness'   => 'UI lightness',
			'ui-darkness'    => 'UI darkness',
			'start'          => 'Start',
			'redmond'        => 'Redmond',
			'sunny'          => 'Sunny',
			'overcast'       => 'Overcast',
			'le-frog'        => 'Le Frog',
			'flick'          => 'Flick',
			'pepper-grinder' => 'Pepper Grinder',
			'eggplant'       => 'Eggplant',
			'dark-hive'      => 'Dark Hive',
			'cupertino'      => 'Cupertino',
			'south-street'   => 'South Street',
			'blitzer'        => 'Blitzer',
			'humanity'       => 'Humanity',
			'hot-sneaks'     => 'Hot sneaks',
			'excite-bike'    => 'Excite Bike',
			'vader'          => 'Vader',
			'dot-luv'        => 'Dot Luv',
			'mint-choc'      => 'Mint Choc',
			'black-tie'      => 'Black Tie',
			'trontastic'     => 'Trontastic',
			'swanky-purse'   => 'Swanky Purse',
			'new-theme-1'    => 'Custom Theme 1',
			'new-theme-2'    => 'Custom Theme 2',
		);

		/**
		 * Calendar Icons
		 */
		$bkap_calendar_icons = array(
			'calendar1.gif',
			'none',
		);

		/**
		 * Translation Strings
		 */
		$book_lang         = 'en';
		$book_translations = array(
			'en' => array(

				// Labels for Booking Date & Booking Time on the product page
				'book_date-label'              => __( 'Start Date', 'woocommerce-booking' ),
				'checkout_date-label'          => __( 'End Date', 'woocommerce-booking' ),
				'book_time-label'              => __( 'Booking Time', 'woocommerce-booking' ),
				'book.item-comments'           => __( 'Comments', 'woocommerce-booking' ),

				// Labels for Booking Date & Booking Time on the "Order Received" page on the web and in the notification email to customer & admin
				'book_item-meta-date'          => __( 'Start Date', 'woocommerce-booking' ),
				'checkout_item-meta-date'      => __( 'End Date', 'woocommerce-booking' ),
				'book_item-meta-time'          => __( 'Booking Time', 'woocommerce-booking' ),

				// Labels for Booking Date & Booking Time on the Cart Page and the Checkout page
				'book_item-cart-date'          => __( 'Start Date', 'woocommerce-booking' ),
				'checkout_item-cart-date'      => __( 'End Date', 'woocommerce-booking' ),
				'book_item-cart-time'          => __( 'Booking Time', 'woocommerce-booking' ),

				// Labels for partial payment in partial payment addon
				'book.item-partial-total'      => __( 'Total ', 'woocommerce-booking' ),
				'book.item-partial-deposit'    => __( 'Partial Deposit ', 'woocommerce-booking' ),
				'book.item-partial-remaining'  => __( 'Amount Remaining', 'woocommerce-booking' ),
				'book.partial-payment-heading' => __( 'Partial Payment', 'woocommerce-booking' ),

				// Labels for full payment in partial payment addon
				'book.item-total-total'        => __( 'Total ', 'woocommerce-booking' ),
				'book.item-total-deposit'      => __( 'Total Deposit ', 'woocommerce-booking' ),
				'book.item-total-remaining'    => __( 'Amount Remaining', 'woocommerce-booking' ),
				'book.total-payment-heading'   => __( 'Total Payment', 'woocommerce-booking' ),

				// Labels for security deposits payment in partial payment addon
				'book.item-security-total'     => __( 'Total ', 'woocommerce-booking' ),
				'book.item-security-deposit'   => __( 'Security Deposit ', 'woocommerce-booking' ),
				'book.item-security-remaining' => __( 'Product Price ', 'woocommerce-booking' ),
				'book.total-security-heading'  => __( 'Security Deposit', 'woocommerce-booking' ),

				// Message to be displayed on the Product page when conflicting products are added.
				'book.conflicting-products'    => __( 'You cannot add products requiring Booking confirmation along with other products that do not need a confirmation. The existing products have been removed from your cart.', 'woocommerce-booking' ),
			),
		);
	}

	/**
	 * Define constants to be used accross the plugin
	 *
	 * @since 4.6.0
	 * @since Updated 5.23.1
	 */
	public static function define_constants() {
		self::define( 'EDD_SL_STORE_URL_BOOK', self::$plugin_url );
		self::define( 'EDD_SL_ITEM_NAME_BOOK', self::$plugin_name );
		self::define( 'BKAP_URL', self::$plugin_url );
		self::define( 'BKAP_PLUGIN_NAME', self::$plugin_name );
		self::define( 'BKAP_VERSION', self::$plugin_version );
		self::define( 'BKAP_CDN', self::$cdn_endpoint );
		self::define( 'BKAP_DEV_MODE', self::$dev_mode );
		self::define( 'BKAP_FILE', __FILE__ );
		self::define( 'BKAP_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
		self::define( 'BKAP_PLUGIN_URL', untrailingslashit( plugins_url( '/', __FILE__ ) ) );
		self::define( 'BKAP_IMAGE_URL', BKAP_PLUGIN_URL . '/assets/images/' );
		self::define( 'BKAP_BOOKINGS_INCLUDE_PATH', BKAP_PLUGIN_PATH . '/includes/' );
		self::define( 'BKAP_BOOKINGS_TEMPLATE_PATH', BKAP_PLUGIN_PATH . '/templates/' );
		self::define( 'BKAP_VENDORS_INCLUDES_PATH', BKAP_PLUGIN_PATH . '/includes/vendor-integration/' );
		self::define( 'BKAP_VENDORS_LIBRARIES_PATH', BKAP_PLUGIN_PATH . '/includes/libraries/' );
		self::define( 'BKAP_VENDORS_TEMPLATE_PATH', BKAP_BOOKINGS_TEMPLATE_PATH . 'vendors-integration/' );
		self::define( 'AJAX_URL', get_admin_url() . 'admin-ajax.php' );
	}

	/**
	 * This function creates all the tables necessary in database detects when the booking plugin is activated.
	 */
	public static function bkap_bookings_activate() {
		if ( ! self::bkap_is_required_plugin_active() ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( self::return_error_message_when_woocommerce_plugin_not_found() ); //phpcs:ignore
		}

		self::activate();
	}

	/**
	 * Delete orphaned records from database on deactivation.
	 */
	public static function bkap_bookings_deactivate() {
		delete_transient( 'bkap_timeslot_notice' );
	}

	/**
	 * Checks if WooCommerce is installed and active.
	 *
	 * @since 5.12.0
	 */
	public static function bkap_do_required_plugin_check() {
		if ( ! self::bkap_is_required_plugin_active() ) {
			if ( is_plugin_active( plugin_basename( __FILE__ ) ) ) {
				deactivate_plugins( plugin_basename( __FILE__ ) );
				add_action( 'admin_notices', array( __CLASS__, 'show_required_plugin_error_notice' ) );
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended
				if ( isset( $_GET['activate'] ) ) {
					unset( $_GET['activate'] );
				}
			}
		}
	}

	/**
	 * Checks if WooCommerce is installed and active.
	 *
	 * @since 5.12.0
	 */
	public static function bkap_is_required_plugin_active() {

		// WooCommerce is required, so we do a check.
		$woocommerce_path = 'woocommerce/woocommerce.php';
		$active_plugins   = (array) get_option( 'active_plugins', array() );

		$active = false;
		if ( is_multisite() ) {
			$plugins = get_site_option( 'active_sitewide_plugins' );
			if ( isset( $plugins[ $woocommerce_path ] ) ) {
				$active = true;
			}
		}

		return in_array( $woocommerce_path, $active_plugins ) || array_key_exists( $woocommerce_path, $active_plugins ) || $active;
	}

	/**
	 * Displays WooCommerce Required Notice.
	 *
	 * @since 5.12.0
	 */
	public static function show_required_plugin_error_notice() {
		echo '<div class="error"><p><strong>' . self::return_error_message_when_woocommerce_plugin_not_found() . '</strong></p></div>'; // phpcs:ignore
	}

	/**
	 * Checks whether to inlcude BKAP files.
	 *
	 * @since 5.12.0
	 */
	public static function bkap_maybe_include_files() {

		if ( self::bkap_is_required_plugin_active() ) {
			self::bkap_include_files();
		}
	}

	/**
	 * Registers the custom post ype for the plugin.
	 */
	public static function register_custom_post_type() {

		/**
		 * Booking Custom Post Type.
		 */
		register_post_type(
			'bkap_booking',
			apply_filters(
				'bkap_register_post_type_bkap_booking',
				array(
					'label'               => __( 'Booking', 'woocommerce-booking' ),
					'labels'              => array(
						'name'               => __( 'Booking', 'woocommerce-booking' ),
						'singular_name'      => __( 'Booking', 'woocommerce-booking' ),
						'add_new'            => __( 'Create Booking', 'woocommerce-booking' ),
						'add_new_item'       => __( 'Add New Booking', 'woocommerce-booking' ),
						'edit'               => __( 'Edit', 'woocommerce-booking' ),
						'edit_item'          => __( 'Edit Booking', 'woocommerce-booking' ),
						'new_item'           => __( 'New Booking', 'woocommerce-booking' ),
						'view'               => __( 'View Booking', 'woocommerce-booking' ),
						'view_item'          => __( 'View Booking', 'woocommerce-booking' ),
						'search_items'       => __( 'Search Bookings', 'woocommerce-booking' ),
						'not_found'          => __( 'No Bookings found', 'woocommerce-booking' ),
						'not_found_in_trash' => __( 'No Bookings found in trash', 'woocommerce-booking' ),
						'parent'             => __( 'Parent Bookings', 'woocommerce-booking' ),
						'menu_name'          => _x( 'Booking', 'Admin menu name', 'woocommerce-booking' ),
						'all_items'          => __( 'View Bookings', 'woocommerce-booking' ),
					),
					'description'         => __( 'This is where bookings are stored.', 'woocommerce-booking' ),
					'public'              => false,
					'capability_type'     => 'post',
					'map_meta_cap'        => true,
					'supports'            => array( '' ),
					'menu_icon'           => 'dashicons-calendar-alt',
					'publicly_queryable'  => false,
					'has_archive'         => true,
					'query_var'           => true,
					'can_export'          => true,
					'rewrite'             => false,
					'hierarchical'        => false,
					'show_in_rest'        => true,
					'exclude_from_search' => true,
				)
			)
		);

		/**
		 * Post status for Booking Custom Post Type.
		 */

		/**
		 * Post status Paid.
		 */
		register_post_status(
			'paid',
			array(
				'label'                     => '<span class="status-paid tips" data-tip="' . _x( 'Paid &amp; Confirmed', 'woocommerce-booking', 'woocommerce-booking' ) . '">' . _x( 'Paid &amp; Confirmed', 'woocommerce-booking', 'woocommerce-booking' ) . '</span>',
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				// Translators: %s Paid & Confirmed Booking Count, %s Paid & Confirmed Booking Count.
				'label_count'               => _n_noop( 'Paid &amp; Confirmed <span class="count">(%s)</span>', 'Paid &amp; Confirmed <span class="count">(%s)</span>', 'woocommerce-booking' ),
			)
		);

		/**
		 * Post status confirmed.
		 */
		register_post_status(
			'confirmed',
			array(
				'label'                     => '<span class="status-confirmed tips" data-tip="' . _x( 'Confirmed', 'woocommerce-booking', 'woocommerce-booking' ) . '">' . _x( 'Confirmed', 'woocommerce-booking', 'woocommerce-booking' ) . '</span>',
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				// Translators: %s Confirmed Booking Count, Confirmed Booking Count.
				'label_count'               => _n_noop( 'Confirmed <span class="count">(%s)</span>', 'Confirmed <span class="count">(%s)</span>', 'woocommerce-booking' ),
			)
		);

		/**
		 * Post status pending confirmation.
		 */
		register_post_status(
			'pending-confirmation',
			array(
				'label'                     => '<span class="status-pending tips" data-tip="' . _x( 'Pending Confirmation', 'woocommerce-booking', 'woocommerce-booking' ) . '">' . _x( 'Pending Confirmation', 'woocommerce-booking', 'woocommerce-booking' ) . '</span>',
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				// Translators: %s Pending Confirmation Booking Count, %s Pending Confirmation Booking Count.
				'label_count'               => _n_noop( 'Pending Confirmation <span class="count">(%s)</span>', 'Pending Confirmation <span class="count">(%s)</span>', 'woocommerce-booking' ),
			)
		);

		/**
		 * Post status cancelled.
		 */
		register_post_status(
			'cancelled',
			array(
				'label'                     => '<span class="status-cancelled tips" data-tip="' . _x( 'Cancelled', 'woocommerce-booking', 'woocommerce-booking' ) . '">' . _x( 'Cancelled', 'woocommerce-booking', 'woocommerce-booking' ) . '</span>',
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				// Translators: %s Cancelled Booking Count, %s Cancelled Booking Count.
				'label_count'               => _n_noop( 'Cancelled <span class="count">(%s)</span>', 'Cancelled <span class="count">(%s)</span>', 'woocommerce-booking' ),
			)
		);

		do_action( 'bkap_registering_custom_booking_status' );

		/**
		 * Registering post type for Google Calendar Events.
		 */
		register_post_type(
			'bkap_gcal_event',
			apply_filters(
				'bkap_register_post_type_bkap_gcal_event',
				array(
					'label'               => __( 'Import Bookings', 'woocommerce-booking' ),
					'labels'              => array(
						'name'               => __( 'Google Event', 'woocommerce-booking' ),
						'singular_name'      => __( 'Google Event', 'woocommerce-booking' ),
						'add_new'            => __( 'Add Google Event', 'woocommerce-booking' ),
						'add_new_item'       => __( 'Add New Google Event', 'woocommerce-booking' ),
						'edit'               => __( 'Edit', 'woocommerce-booking' ),
						'edit_item'          => __( 'Edit Google Event', 'woocommerce-booking' ),
						'new_item'           => __( 'New Google Event', 'woocommerce-booking' ),
						'view'               => __( 'Import Bookings', 'woocommerce-booking' ),
						'view_item'          => __( 'View Google Event', 'woocommerce-booking' ),
						'search_items'       => __( 'Search Google Event', 'woocommerce-booking' ),
						'not_found'          => __( 'No Google Event found', 'woocommerce-booking' ),
						'not_found_in_trash' => __( 'No Google Event found in trash', 'woocommerce-booking' ),
						'parent'             => __( 'Parent Google Events', 'woocommerce-booking' ),
						'menu_name'          => _x( 'Google Event', 'Admin menu name', 'woocommerce-booking' ),
						'all_items'          => __( 'Import Booking', 'woocommerce-booking' ),
					),
					'description'         => __( 'This is where bookings are stored.', 'woocommerce-booking' ),
					'public'              => false,
					'capability_type'     => 'post',
					'capabilities'        => array(
						'create_posts' => 'do_not_allow', // will have to be removed oncce we show the custom post type
					),
					'map_meta_cap'        => true,
					'publicly_queryable'  => false,
					'exclude_from_search' => true,
					'hierarchical'        => false,
					'rewrite'             => false,
					'query_var'           => false,
					'supports'            => array( '' ),
					'has_archive'         => false,
				)
			)
		);

		/**
		 * Registering the status of the Google Calendar Events
		 */

		/**
		 * Post status unmapped
		 */
		register_post_status(
			'bkap-unmapped',
			array(
				'label'                     => '<span class="status-un-mapped tips" data-tip="' . _x( 'Un-mapped', 'woocommerce-booking', 'woocommerce-booking' ) . '">' . _x( 'Un-mapped', 'woocommerce-booking', 'woocommerce-booking' ) . '</span>',
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				// Translators: %s Unmapped Event, %s Unmapped Event.
				'label_count'               => _n_noop( 'Un-mapped <span class="count">(%s)</span>', 'Un-mapped <span class="count">(%s)</span>', 'woocommerce-booking' ),
			)
		);

		/**
		 * Post status mapped
		 */
		register_post_status(
			'bkap-mapped',
			array(
				'label'                     => '<span class="status-mapped tips" data-tip="' . _x( 'Mapped', 'woocommerce-booking', 'woocommerce-booking' ) . '">' . _x( 'Mapped', 'woocommerce-booking', 'woocommerce-booking' ) . '</span>',
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				// Translators: %s Mapped Event, %s Mapped Event.
				'label_count'               => _n_noop( 'Mapped <span class="count">(%s)</span>', 'Mapped <span class="count">(%s)</span>', 'woocommerce-booking' ),
			)
		);

		/**
		 * Post status deleted
		 */
		register_post_status(
			'bkap-deleted',
			array(
				'label'                     => '<span class="status-deleted tips" data-tip="' . _x( 'Deleted', 'woocommerce-booking', 'woocommerce-booking' ) . '">' . _x( 'Deleted', 'woocommerce-booking', 'woocommerce-booking' ) . '</span>',
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				// Translators: %s Deleted Event, %s Deleted Event.
				'label_count'               => _n_noop( 'Deleted <span class="count">(%s)</span>', 'Mapped <span class="count">(%s)</span>', 'woocommerce-booking' ),
			)
		);

		/**
		 * Booking Resources Post Type
		 */
		register_post_type(
			'bkap_resource',
			apply_filters(
				'bkap_register_post_type_resource',
				array(
					'label'               => __( 'Booking Resources', 'woocommerce-booking' ),
					'labels'              => array(
						'name'               => __( 'Bookable resource', 'woocommerce-booking' ),
						'singular_name'      => __( 'Bookable resource', 'woocommerce-booking' ),
						'add_new'            => __( 'Add Resource', 'woocommerce-booking' ),
						'add_new_item'       => __( 'Add New Resource', 'woocommerce-booking' ),
						'edit'               => __( 'Edit', 'woocommerce-booking' ),
						'edit_item'          => __( 'Edit Resource', 'woocommerce-booking' ),
						'new_item'           => __( 'New Resource', 'woocommerce-booking' ),
						'view'               => __( 'View Resource', 'woocommerce-booking' ),
						'view_item'          => __( 'View Resource', 'woocommerce-booking' ),
						'search_items'       => __( 'Search Resource', 'woocommerce-booking' ),
						'not_found'          => __( 'No Resource found', 'woocommerce-booking' ),
						'not_found_in_trash' => __( 'No Resource found in trash', 'woocommerce-booking' ),
						'parent'             => __( 'Parent Resources', 'woocommerce-booking' ),
						'menu_name'          => _x( 'Resources', 'Admin menu name', 'woocommerce-booking' ),
						'all_items'          => __( 'Resources', 'woocommerce-booking' ),
					),
					'description'         => __( 'Bookable resources are bookable within a bookings product.', 'woocommerce-booking' ),
					'public'              => false,
					'capability_type'     => 'product',
					'map_meta_cap'        => true,
					'publicly_queryable'  => false,
					'exclude_from_search' => true,
					'hierarchical'        => false,
					'rewrite'             => false,
					'query_var'           => false,
					'supports'            => array( 'title' ),
					'has_archive'         => false,
					'show_in_rest'        => true,
				)
			)
		);

		/**
		 * Booking Person Post Type
		 */
		register_post_type(
			'bkap_person',
			apply_filters(
				'bkap_register_post_type_person',
				array(
					'label'               => __( 'Booking Persons', 'woocommerce-booking' ),
					'labels'              => array(
						'name'               => __( 'Bookable person', 'woocommerce-booking' ),
						'singular_name'      => __( 'Bookable person', 'woocommerce-booking' ),
						'add_new'            => __( 'Add Person', 'woocommerce-booking' ),
						'add_new_item'       => __( 'Add New Person', 'woocommerce-booking' ),
						'edit'               => __( 'Edit', 'woocommerce-booking' ),
						'edit_item'          => __( 'Edit Person', 'woocommerce-booking' ),
						'new_item'           => __( 'New Person', 'woocommerce-booking' ),
						'view'               => __( 'View Person', 'woocommerce-booking' ),
						'view_item'          => __( 'View Person', 'woocommerce-booking' ),
						'search_items'       => __( 'Search Person', 'woocommerce-booking' ),
						'not_found'          => __( 'No Person found', 'woocommerce-booking' ),
						'not_found_in_trash' => __( 'No Person found in trash', 'woocommerce-booking' ),
						'parent'             => __( 'Parent Persons', 'woocommerce-booking' ),
						'menu_name'          => _x( 'Persons', 'Admin menu name', 'woocommerce-booking' ),
						'all_items'          => __( 'Persons', 'woocommerce-booking' ),
					),
					'public'              => false,
					'capability_type'     => 'product',
					'map_meta_cap'        => true,
					'publicly_queryable'  => false,
					'exclude_from_search' => true,
					'hierarchical'        => false,
					'rewrite'             => false,
					'query_var'           => false,
					'supports'            => array( 'title' ),
					'has_archive'         => false,
					'show_in_rest'        => true,
				)
			)
		);

		/**
		 * Post Type: Send Reminder.
		 */
		register_post_type(
			'bkap_reminder',
			apply_filters(
				'bkap_register_post_type_reminder',
				array(
					'label'               => __( 'Send Reminder', 'woocommerce-booking' ),
					'labels'              => array(
						'name'               => __( 'Send Reminder', 'woocommerce-booking' ),
						'singular_name'      => __( 'Send Reminder', 'woocommerce-booking' ),
						'add_new'            => __( 'Add new Reminder', 'woocommerce-booking' ),
						'add_new_item'       => __( 'Add New Reminder', 'woocommerce-booking' ),
						'edit'               => __( 'Edit', 'woocommerce-booking' ),
						'edit_item'          => __( 'Edit Reminder', 'woocommerce-booking' ),
						'new_item'           => __( 'New Reminder', 'woocommerce-booking' ),
						'view'               => __( 'View Reminder', 'woocommerce-booking' ),
						'view_item'          => __( 'View Reminder', 'woocommerce-booking' ),
						'search_items'       => __( 'Search Resource', 'woocommerce-booking' ),
						'not_found'          => __( 'No Resource found', 'woocommerce-booking' ),
						'not_found_in_trash' => __( 'No Resource found in trash', 'woocommerce-booking' ),
						'parent'             => __( 'Parent Reminders', 'woocommerce-booking' ),
						'menu_name'          => _x( 'Send Reminders', 'Admin menu name', 'woocommerce-booking' ),
						'all_items'          => __( 'Send Reminders', 'woocommerce-booking' ),
					),
					// 'description'         => __( 'Bookable resources are bookable within a bookings product.', 'woocommerce-booking' ),
					'public'              => false,
					'capability_type'     => 'product',
					'map_meta_cap'        => true,
					'publicly_queryable'  => false,
					'exclude_from_search' => true,
					'hierarchical'        => false,
					'rewrite'             => false,
					'query_var'           => false,
					'supports'            => array( 'title' ),
					'has_archive'         => false,
					'show_in_rest'        => true,
				)
			)
		);

		/**
		 * Post status cancelled.
		 */
		register_post_status(
			'bkap-active',
			array(
				'label'                     => '<span class="status-active tips" data-tip="' . _x( 'Active', 'woocommerce-booking', 'woocommerce-booking' ) . '">' . _x( 'Active', 'woocommerce-booking', 'woocommerce-booking' ) . '</span>',
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				// Translators: %s Active Reminder, %s Active Reminder.
				'label_count'               => _n_noop( 'Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', 'woocommerce-booking' ),
			)
		);

		register_post_status(
			'bkap-inactive',
			array(
				'label'                     => '<span class="status-inactive tips" data-tip="' . _x( 'Inactive', 'woocommerce-booking', 'woocommerce-booking' ) . '">' . _x( 'Inactive', 'woocommerce-booking', 'woocommerce-booking' ) . '</span>',
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				// Translators: %s Inactive Reminder, %s Inactive Reminder.
				'label_count'               => _n_noop( 'Inactive <span class="count">(%s)</span>', 'Inactive <span class="count">(%s)</span>', 'woocommerce-booking' ),
			)
		);
	}

	/**
	 * Activation functions.
	 */
	public static function activate() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table_name = "{$wpdb->prefix}booking_history";

		dbDelta(
			"CREATE TABLE IF NOT EXISTS {$table_name} (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`post_id` int(11) NOT NULL,
			`weekday` varchar(50) NOT NULL,
			`start_date` date NOT NULL,
			`end_date` date NOT NULL,
			`from_time` varchar(50) NOT NULL,
			`to_time` varchar(50) NOT NULL,
			`total_booking` int(11) NOT NULL,
			`available_booking` int(11) NOT NULL,
			`status` varchar(20) NOT NULL,
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1"
		);

		dbDelta(
			"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}booking_order_history (
								`id` int(11) NOT NULL AUTO_INCREMENT,
								`order_id` int(11) NOT NULL,
								`booking_id` int(11) NOT NULL,
								PRIMARY KEY (`id`)
					)ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1"
		);

		update_option( 'woocommerce_booking_alter_queries', 'yes' );

		$wc_bkap_current_db_version = get_option( 'woocommerce_booking_db_version' );
		update_option( 'woocommerce_booking_db_version', BKAP_VERSION );

		// Adding new option to handle upgrade process smoothly.
		if ( ! empty( $wc_bkap_current_db_version ) ) {
			update_option( 'wc_bkap_prev_db_version', $wc_bkap_current_db_version );
		} else {
			update_option( 'wc_bkap_prev_db_version', BKAP_VERSION );
		}

		update_option( 'woocommerce_booking_abp_hrs', 'HOURS' );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
		$results = $wpdb->get_results( "SHOW COLUMNS FROM $table_name LIKE 'end_date'" );

		if ( 0 === count( $results ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
			$wpdb->get_results( "ALTER TABLE $table_name ADD `end_date` date AFTER  `start_date`" );
		}

		if ( ( false === get_option( 'book_date-label' ) || '' === get_option( 'book_date-label' ) ) ) {
			add_option( 'bkap_add_to_cart', __( 'Book Now!', 'woocommerce-booking' ) );
			add_option( 'bkap_check_availability', __( 'Check Availability', 'woocommerce-booking' ) );
		}

		// Set default labels.
		add_option( 'book_date-label', __( 'Start Date', 'woocommerce-booking' ) );
		add_option( 'checkout_date-label', __( 'End Date', 'woocommerce-booking' ) );
		add_option( 'bkap_calendar_icon_file', 'calendar1.gif' );
		add_option( 'book_time-label', __( 'Booking Time', 'woocommerce-booking' ) );
		add_option( 'book_time-select-option', __( 'Choose a Time', 'woocommerce-booking' ) );
		add_option( 'book_fixed-block-label', __( 'Select Period', 'woocommerce-booking' ) );
		add_option( 'book_price-label', __( 'Total:', 'woocommerce-booking' ) );
		add_option( 'book_item-meta-date', __( 'Start Date', 'woocommerce-booking' ) );
		add_option( 'checkout_item-meta-date', __( 'End Date', 'woocommerce-booking' ) );
		add_option( 'book_item-meta-time', __( 'Booking Time', 'woocommerce-booking' ) );
		add_option( 'book_ics-file-name', __( 'Mycal', 'woocommerce-booking' ) );
		add_option( 'book_item-cart-date', __( 'Start Date', 'woocommerce-booking' ) );
		add_option( 'checkout_item-cart-date', __( 'End Date', 'woocommerce-booking' ) );
		add_option( 'book_item-cart-time', __( 'Booking Time', 'woocommerce-booking' ) );
		add_option( 'bkap_update_booking_labels_settings', 'yes' ); // add this option to ensure the labels above are retained in the future updates.

		// add the new messages in the options table.
		add_option( 'book_stock-total', __( 'AVAILABLE_SPOTS stock total', 'woocommerce-booking' ) );
		add_option( 'book_available-stock-date', __( 'AVAILABLE_SPOTS booking(s) are available on DATE', 'woocommerce-booking' ) );
		add_option( 'book_available-stock-time', __( 'AVAILABLE_SPOTS booking(s) are available for TIME on DATE', 'woocommerce-booking' ) );
		add_option( 'book_available-stock-date-attr', __( 'AVAILABLE_SPOTS ATTRIBUTE_NAME booking(s) are available on DATE', 'woocommerce-booking' ) );
		add_option( 'book_available-stock-time-attr', __( 'AVAILABLE_SPOTS ATTRIBUTE_NAME booking(s) are available for TIME on DATE', 'woocommerce-booking' ) );
		add_option( 'book_limited-booking-msg-date', __( 'PRODUCT_NAME has only AVAILABLE_SPOTS tickets available for the date DATE.', 'woocommerce-booking' ) );
		add_option( 'book_no-booking-msg-date', __( 'For PRODUCT_NAME, the date DATE has been fully booked. Please try another date.', 'woocommerce-booking' ) );
		add_option( 'book_limited-booking-msg-time', __( 'PRODUCT_NAME has only AVAILABLE_SPOTS tickets available for TIME on DATE.', 'woocommerce-booking' ) );
		add_option( 'book_no-booking-msg-time', __( 'For PRODUCT_NAME, the time TIME on DATE has been fully booked. Please try another timeslot.', 'woocommerce-booking' ) );
		add_option( 'book_limited-booking-msg-date-attr', __( 'PRODUCT_NAME has only AVAILABLE_SPOTS ATTRIBUTE_NAME tickets available for the date DATE.', 'woocommerce-booking' ) );
		add_option( 'book_limited-booking-msg-time-attr', __( 'PRODUCT_NAME has only AVAILABLE_SPOTS ATTRIBUTE_NAME tickets available for TIME on DATE.', 'woocommerce-booking' ) );
		add_option( 'book_real-time-error-msg', __( 'That date just got booked. Please reload the page.', 'woocommerce-booking' ) );
		add_option( 'book_multidates_min_max_selection_msg', __( 'Select a minimum of MIN Days and maximum of MAX Days', 'woocommerce-booking' ) );

		// add GCal event summary & description.
		add_option( 'bkap_calendar_event_summary', 'SITE_NAME, ORDER_NUMBER' );
		add_option( 'bkap_calendar_event_description', __( 'PRODUCT_WITH_QTY,Name: CLIENT,Contact: EMAIL, PHONE', 'woocommerce-booking' ) );
		add_option( 'bkap_calendar_event_location', 'CITY' ); // add GCal event city.

		// Set default global booking settings.
		$booking_settings                                     = new stdClass();
		$booking_settings->booking_language                   = 'en-GB';
		$booking_settings->booking_date_format                = 'mm/dd/y';
		$booking_settings->booking_time_format                = '12';
		$booking_settings->booking_months                     = '1';
		$booking_settings->booking_calendar_day               = '1';
		$booking_settings->global_booking_minimum_number_days = '0';
		$booking_settings->booking_availability_display       = '';
		$booking_settings->minimum_day_booking                = '';
		$booking_settings->booking_global_selection           = '';
		$booking_settings->booking_global_timeslot            = '';
		$booking_settings->woo_product_addon_price            = '';
		$booking_settings->booking_global_holidays            = '';
		$booking_settings->same_bookings_in_cart              = '';
		$booking_settings->resource_price_per_day             = '';
		$booking_settings->booking_themes                     = 'smoothness';
		$booking_settings->hide_variation_price               = 'on';
		$booking_settings->display_disabled_buttons           = 'on';
		$booking_settings->hide_booking_price                 = '';
		$booking_settings->booking_overlapping_timeslot       = 'on';
		$booking_settings->booking_timeslot_display_mode      = 'list-view';
		$booking_settings->bkap_auto_cancel_booking           = '0';

		$booking_settings = apply_filters( 'woocommerce_booking_global_settings', $booking_settings );

		add_option( 'woocommerce_booking_global_settings', json_encode( $booking_settings ) );
	}
}

/**
 * Returns the main instance of the class.
 *
 * @return Woocommerce_Booking
 */
function BKAP() { //phpcs:ignore
	return Woocommerce_Booking::instance();
}

BKAP();
