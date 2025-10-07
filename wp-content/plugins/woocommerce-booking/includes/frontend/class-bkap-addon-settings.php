<?php
/**
 * Hide Booking Options for Vendors
 *
 * Additional settings for the plugin
 *
 * @author  Tyche Softwares
 * @package Hide Booking Options for Vendors
 */

if ( ! class_exists( 'BKAP_Addon_Settings' ) ) {

	/**
	 * BKAP_Addon_Settings
	 */
	class BKAP_Addon_Settings {

		/**
		 * Constructor BKAP_Addon_Settings
		 */
		public function __construct() {
			add_action( 'bkap_add_addon_settings', array( &$this, 'bkap_settings_view' ), 9 );
			add_action( 'admin_init', array( &$this, 'bkap_settings_init' ), 10 );
			add_action( 'bkap_after_load_products_css', array( &$this, 'bkap_hide_booking_options' ) );
			add_action( 'admin_footer', array( &$this, 'bkap_hide_booking_options' ) );
			add_filter( 'bkap_extra_options', array( &$this, 'bkap_hide_resource_persons_options' ), 10, 1 );
			add_filter( 'bkap_get_booking_types', array( &$this, 'bkap_hide_booking_types' ), 10, 1 );
		}

		/**
		 * For Vendors, remove Booking Type from the Booking Type dropdown.
		 *
		 * @param array $booking_types Array of Booking Types.
		 * @since 5.13.0
		 */
		public function bkap_hide_booking_types( $booking_types ) {

			$path = untrailingslashit( WP_CONTENT_DIR . '/plugins/woocommerce-booking/' );
			include_once $path . '/includes/vendor-integration/vendors-common.php';

			$vendor_id = get_current_user_id();
			$is_vendor = BKAP_Vendors::bkap_is_vendor( $vendor_id );
			if ( $is_vendor ) {
				$bkap_hide_booking_options = get_option( 'bkap_hide_booking_options', array() );

				if ( isset( $bkap_hide_booking_options['booking_type'] ) ) {

					$bkap_types = $bkap_hide_booking_options['booking_type'];
					foreach ( $bkap_types as $bkap_type ) {
						if ( isset( $booking_types[ $bkap_type ] ) ) {
							unset( $booking_types[ $bkap_type ] );
						}
					}
				}
			}

			return $booking_types;
		}

		/**
		 * Hide Resource and Persons from the Vendor Dashboard.
		 *
		 * @param array $data Resource/Persons Data.
		 * @since 5.13.0
		 */
		public function bkap_hide_resource_persons_options( $data ) {

			$path = untrailingslashit( WP_CONTENT_DIR . '/plugins/woocommerce-booking/' );
			include_once $path . '/includes/vendor-integration/vendors-common.php';

			$vendor_id = get_current_user_id();
			$is_vendor = BKAP_Vendors::bkap_is_vendor( $vendor_id );
			if ( $is_vendor ) {
				$bkap_hide_booking_options = get_option( 'bkap_hide_booking_options', array() );

				if ( isset( $bkap_hide_booking_options['resource'] ) && 'on' === $bkap_hide_booking_options['resource'] ) {
					unset( $data['bkap_resource'] );
				}

				if ( isset( $bkap_hide_booking_options['persons'] ) && 'on' === $bkap_hide_booking_options['persons'] ) {
					unset( $data['bkap_person'] );
				}
			}

			return $data;
		}

		/**
		 * Adding CSS to hide the options for the Vendor.
		 *
		 * @since 5.13.0
		 */
		public function bkap_hide_booking_options() {

			$path = untrailingslashit( WP_CONTENT_DIR . '/plugins/woocommerce-booking/' );
			include_once $path . '/includes/vendor-integration/vendors-common.php';

			$vendor_id = get_current_user_id();
			$is_vendor = BKAP_Vendors::bkap_is_vendor( $vendor_id );
			if ( $is_vendor ) {
				self::bkap_hide_booking_options_script();
			}
		}

		/**
		 * Preparing the CSS to hide the options.
		 *
		 * @since 5.13.0
		 */
		public static function bkap_hide_booking_options_script() {

			$bkap_hide_booking_options = get_option( 'bkap_hide_booking_options', array() );

			if ( '' === $bkap_hide_booking_options ) {
				$bkap_hide_booking_options = array();
			}

			$id_class_data = array(
				'enable_booking'         => '#enable_booking_options_section',
				'booking_type_section'   => '#enable_booking_types_section',
				'inline_calendar'        => '#enable_inline_calendar_section',
				'purchase_without_date'  => '#purchase_wo_date_section',
				'requires_confirmation'  => '#requires_confirmation_section',
				'dates_in_dropdown'      => '#date_in_dropdown_section',
				'can_be_cancelled'       => '#can_be_cancelled_section',
				'advance_booking_period' => '#booking_minimum_number_days_row',
				'nod_to_choose'          => '#booking_maximum_number_days_row',
				'max_booking_on_date'    => '#booking_lockout_date_row',
				'min_no_of_nights'       => '#booking_minimum_number_days_multiple_row',
				'max_no_of_nights'       => '#booking_maximum_number_days_multiple_row',
				'google_calendar_export' => '#bkap_gcal_export_section',
				'google_calendar_import' => '#bkap_gcal_import_section',
				'zoom_meetings'          => '.bkap_integration_zoom_button',
				'fluentcrm'              => '.bkap_integration_fluentcrm_button',
				'zapier'                 => '.bkap_integration_zapier_button',
			);

			$css = '';
			foreach ( $bkap_hide_booking_options as $key => $value ) {
				if ( 'on' === $value && isset( $id_class_data[ $key ] ) ) {
					$css .= $id_class_data[ $key ] . ', ';
				}
			}

			if ( '' !== $css ) {
				$css = substr( $css, 0, -2 );
				?>
				<style type="text/css">
					<?php echo esc_html( $css ); ?> { display: none !important; }
				</style>
				<?php
			}
		}

		/**
		 * Registers settings of the plugin and attached to hook to display on Addon Settings Tab
		 */
		public function bkap_settings_view() {

			$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : ''; // phpcs:ignore

			if ( 'addon_settings' === $action ) {

				bkap_include_select2_scripts();
				?>
				<div id="content">
					<form method="post" action="options.php">
						<?php settings_fields( 'bkap_hide_booking_options_settings' ); ?>
						<?php do_settings_sections( 'bkap_hide_booking_options_settings' ); ?> 
						<?php submit_button(); ?>
					</form>
				</div>
				<?php
			}
		}

		/**
		 * Settings API Initialization
		 */
		public function bkap_settings_init() {

			register_setting( 'bkap_hide_booking_options_settings', 'bkap_hide_booking_options' ); // phpcs:ignore

			add_settings_section(
				'bkap_hide_booking_options_section',
				__( 'Hide Booking Options on Vendor Dashboard', 'woocommerce-booking' ),
				array( $this, 'bkap_hide_booking_options_callback' ),
				'bkap_hide_booking_options_settings'
			);

			add_settings_field(
				'general_tab',
				__( 'General Tab Options', 'woocommerce-booking' ),
				array( $this, 'bkap_hide_general_tab_callback' ),
				'bkap_hide_booking_options_settings',
				'bkap_hide_booking_options_section'
			);

			add_settings_field(
				'availability_tab',
				__( 'Availability Tab Options', 'woocommerce-booking' ),
				array( $this, 'bkap_hide_availability_tab_callback' ),
				'bkap_hide_booking_options_settings',
				'bkap_hide_booking_options_section'
			);

			add_settings_field(
				'resource_tab',
				__( 'Resource Tab Options', 'woocommerce-booking' ),
				array( $this, 'bkap_hide_resource_tab_callback' ),
				'bkap_hide_booking_options_settings',
				'bkap_hide_booking_options_section'
			);

			add_settings_field(
				'persons_tab',
				__( 'Persons Tab Options', 'woocommerce-booking' ),
				array( $this, 'bkap_hide_persons_tab_callback' ),
				'bkap_hide_booking_options_settings',
				'bkap_hide_booking_options_section'
			);

			add_settings_field(
				'integration_tab',
				__( 'Integrations Tab Options', 'woocommerce-booking' ),
				array( $this, 'bkap_hide_integration_tab_callback' ),
				'bkap_hide_booking_options_settings',
				'bkap_hide_booking_options_section'
			);
		}

		/**
		 * General Tab Options.
		 *
		 * @since 5.13.0
		 */
		public function bkap_hide_general_tab_callback() {

			$bkap_hide_booking_options = get_option( 'bkap_hide_booking_options' );
			$descriptions              = array(
				'enable_booking'        => __( 'Hide General -> Enable Booking option from Booking Meta Box.', 'woocommerce-booking' ),
				'booking_type'          => array(
					__( 'Selected booking type will be removed from General -> Booking Type dropdown.', 'woocommerce-booking' ),
					__( 'Hide General -> Booking Type dropdown.', 'woocommerce-booking' ),
				),
				'inline_calendar'       => __( 'Hide General -> Enable Inline Calendar option from Booking Meta Box.', 'woocommerce-booking' ),
				'dates_in_dropdown'     => __( 'Hide General -> Show dates in dropdown option from Booking Meta Box.', 'woocommerce-booking' ),
				'purchase_without_date' => __( 'Hide General -> Purchase without choosing date option from Booking Meta Box.', 'woocommerce-booking' ),
				'requires_confirmation' => __( 'Hide General -> Requires Confirmation option from Booking Meta Box.', 'woocommerce-booking' ),
				'can_be_cancelled'      => __( 'Hide General -> Can be cancelled? option from Booking Meta Box.', 'woocommerce-booking' ),
			);

			foreach ( $descriptions as $key => $value ) {
				$function_name = 'bkap_hide_' . $key . '_callback';
				self::$function_name( array( $value, $key, $bkap_hide_booking_options ) );
				echo '<br /><br />';
			}
		}

		/**
		 * Availability Tab Options.
		 *
		 * @since 5.13.0
		 */
		public function bkap_hide_availability_tab_callback() {

			$bkap_hide_booking_options = get_option( 'bkap_hide_booking_options' );
			$descriptions              = array(
				'advance_booking_period' => __( 'Hide Availability -> Advance Booking Period (in hours) option from Booking Meta Box.', 'woocommerce-booking' ),
				'nod_to_choose'          => __( 'Hide Availability -> Number of dates to choose option from Booking Meta Box.', 'woocommerce-booking' ),
				'max_booking_on_date'    => __( 'Hide Availability -> Maximum Bookings On Any Date option from Booking Meta Box.', 'woocommerce-booking' ),
				'min_no_of_nights'       => __( 'Hide Availability -> Minimum number of nights to book option from Booking Meta Box.', 'woocommerce-booking' ),
				'max_no_of_nights'       => __( 'Hide Availability -> Maximum number of nights to book option from Booking Meta Box.', 'woocommerce-booking' ),
			);

			foreach ( $descriptions as $key => $value ) {
				$function_name = 'bkap_hide_' . $key . '_callback';
				self::$function_name( array( $value, $key, $bkap_hide_booking_options ) );
				echo '<br /><br />';
			}
		}

		/**
		 * Resource Tab Options.
		 *
		 * @since 5.13.0
		 */
		public function bkap_hide_resource_tab_callback() {

			$bkap_hide_booking_options = get_option( 'bkap_hide_booking_options' );
			$descriptions              = array(
				'resource' => __( 'Hide Resource option from Booking Meta Box.', 'woocommerce-booking' ),
			);

			foreach ( $descriptions as $key => $value ) {
				$function_name = 'bkap_hide_' . $key . '_callback';
				self::$function_name( array( $value, $key, $bkap_hide_booking_options ) );
				echo '<br /><br />';
			}
		}

		/**
		 * Persons Tab Options.
		 *
		 * @since 5.13.0
		 */
		public function bkap_hide_persons_tab_callback() {

			$bkap_hide_booking_options = get_option( 'bkap_hide_booking_options' );
			$descriptions              = array(
				'persons' => __( 'Hide Persons options from Booking Meta Box.', 'woocommerce-booking' ),
			);

			foreach ( $descriptions as $key => $value ) {
				$function_name = 'bkap_hide_' . $key . '_callback';
				self::$function_name( array( $value, $key, $bkap_hide_booking_options ) );
				echo '<br /><br />';
			}
		}

		/**
		 * Integrations Tab Options.
		 *
		 * @since 5.13.0
		 */
		public function bkap_hide_integration_tab_callback() {

			$bkap_hide_booking_options = get_option( 'bkap_hide_booking_options' );
			$descriptions              = array(
				'google_calendar_export' => __( 'Hide Integrations -> Google Calendar Export option from Booking Meta Box.', 'woocommerce-booking' ),
				'google_calendar_import' => __( 'Hide Integrations -> Google Calendar Import option from Booking Meta Box.', 'woocommerce-booking' ),
				'zoom_meetings'          => __( 'Hide Integrations -> Zoom Meetings option from Booking Meta Box.', 'woocommerce-booking' ),
				'fluentcrm'              => __( 'Hide Integrations -> FluentCRM option from Booking Meta Box.', 'woocommerce-booking' ),
				'zapier'                 => __( 'Hide Integrations -> Zapier option from Booking Meta Box.', 'woocommerce-booking' ),
			);

			foreach ( $descriptions as $key => $value ) {
				$function_name = 'bkap_hide_' . $key . '_callback';
				self::$function_name( array( $value, $key, $bkap_hide_booking_options ) );
				echo '<br /><br />';
			}
		}

		/**
		 * Zapier.
		 *
		 * @param array $args Arguments.
		 * @since 5.13.0
		 */
		public static function bkap_hide_zapier_callback( $args ) {

			$bkap_hide_booking_options = get_option( 'bkap_hide_booking_options' );

			$zapier = '';
			if ( isset( $bkap_hide_booking_options['zapier'] ) && 'on' === $bkap_hide_booking_options['zapier'] ) {
				$zapier = 'checked';
			}

			echo '<input type="checkbox" id="zapier" name="bkap_hide_booking_options[zapier]" ' . esc_attr( $zapier ) . '/>';
			echo '<label for="zapier"> ' . esc_html( $args[0] ) . '</label>';
		}

		/**
		 * FluentCRM.
		 *
		 * @param array $args Arguments.
		 * @since 5.13.0
		 */
		public static function bkap_hide_fluentcrm_callback( $args ) {

			$bkap_hide_booking_options = get_option( 'bkap_hide_booking_options' );

			$fluentcrm = '';
			if ( isset( $bkap_hide_booking_options['fluentcrm'] ) && 'on' === $bkap_hide_booking_options['fluentcrm'] ) {
				$fluentcrm = 'checked';
			}

			echo '<input type="checkbox" id="fluentcrm" name="bkap_hide_booking_options[fluentcrm]" ' . esc_attr( $fluentcrm ) . '/>';
			echo '<label for="fluentcrm"> ' . esc_html( $args[0] ) . '</label>';
		}

		/**
		 * Zoom Meeting.
		 *
		 * @param array $args Arguments.
		 * @since 5.13.0
		 */
		public static function bkap_hide_zoom_meetings_callback( $args ) {

			$bkap_hide_booking_options = get_option( 'bkap_hide_booking_options' );

			$zoom_meetings = '';
			if ( isset( $bkap_hide_booking_options['zoom_meetings'] ) && 'on' === $bkap_hide_booking_options['zoom_meetings'] ) {
				$zoom_meetings = 'checked';
			}

			echo '<input type="checkbox" id="zoom_meetings" name="bkap_hide_booking_options[zoom_meetings]" ' . esc_attr( $zoom_meetings ) . '/>';
			echo '<label for="zoom_meetings"> ' . esc_html( $args[0] ) . '</label>';
		}

		/**
		 * Google Calendar Import.
		 *
		 * @param array $args Arguments.
		 * @since 5.13.0
		 */
		public static function bkap_hide_google_calendar_import_callback( $args ) {

			$bkap_hide_booking_options = get_option( 'bkap_hide_booking_options' );

			$google_calendar_import = '';
			if ( isset( $bkap_hide_booking_options['google_calendar_import'] ) && 'on' === $bkap_hide_booking_options['google_calendar_import'] ) {
				$google_calendar_import = 'checked';
			}

			echo '<input type="checkbox" id="google_calendar_import" name="bkap_hide_booking_options[google_calendar_import]" ' . esc_attr( $google_calendar_import ) . '/>';
			echo '<label for="google_calendar_import"> ' . esc_html( $args[0] ) . '</label>';
		}

		/**
		 * Google Calendar Export.
		 *
		 * @param array $args Arguments.
		 * @since 5.13.0
		 */
		public static function bkap_hide_google_calendar_export_callback( $args ) {

			$bkap_hide_booking_options = get_option( 'bkap_hide_booking_options' );

			$google_calendar_export = '';
			if ( isset( $bkap_hide_booking_options['google_calendar_export'] ) && 'on' === $bkap_hide_booking_options['google_calendar_export'] ) {
				$google_calendar_export = 'checked';
			}

			echo '<input type="checkbox" id="google_calendar_export" name="bkap_hide_booking_options[google_calendar_export]" ' . esc_attr( $google_calendar_export ) . '/>';
			echo '<label for="google_calendar_export"> ' . esc_html( $args[0] ) . '</label>';
		}

		/**
		 * Persons.
		 *
		 * @param array $args Arguments.
		 * @since 5.13.0
		 */
		public static function bkap_hide_persons_callback( $args ) {

			$bkap_hide_booking_options = get_option( 'bkap_hide_booking_options' );

			$persons = '';
			if ( isset( $bkap_hide_booking_options['persons'] ) && 'on' === $bkap_hide_booking_options['persons'] ) {
				$persons = 'checked';
			}

			echo '<input type="checkbox" id="persons" name="bkap_hide_booking_options[persons]" ' . esc_html( $persons ) . '/>';
			echo '<label for="persons"> ' . esc_html( $args[0] ) . '</label>';
		}

		/**
		 * Resource.
		 *
		 * @param array $args Arguments.
		 * @since 5.13.0
		 */
		public static function bkap_hide_resource_callback( $args ) {

			$bkap_hide_booking_options = get_option( 'bkap_hide_booking_options' );

			$resource = '';
			if ( isset( $bkap_hide_booking_options['resource'] ) && 'on' === $bkap_hide_booking_options['resource'] ) {
				$resource = 'checked';
			}

			echo '<input type="checkbox" id="resource" name="bkap_hide_booking_options[resource]" ' . esc_attr( $resource ) . '/>';
			echo '<label for="resource"> ' . esc_html( $args[0] ) . '</label>';
		}

		/**
		 * Maximum numbers of nights to book.
		 *
		 * @param array $args Arguments.
		 * @since 5.13.0
		 */
		public static function bkap_hide_max_no_of_nights_callback( $args ) {

			$bkap_hide_booking_options = get_option( 'bkap_hide_booking_options' );

			$max_no_of_nights = '';
			if ( isset( $bkap_hide_booking_options['max_no_of_nights'] ) && 'on' === $bkap_hide_booking_options['max_no_of_nights'] ) {
				$max_no_of_nights = 'checked';
			}

			echo '<input type="checkbox" id="max_no_of_nights" name="bkap_hide_booking_options[max_no_of_nights]" ' . esc_attr( $max_no_of_nights ) . '/>';
			echo '<label for="max_no_of_nights"> ' . esc_html( $args[0] ) . '</label>';
		}

		/**
		 * Minimum numbers of nights to book.
		 *
		 * @param array $args Arguments.
		 * @since 5.13.0
		 */
		public static function bkap_hide_min_no_of_nights_callback( $args ) {

			$bkap_hide_booking_options = get_option( 'bkap_hide_booking_options' );

			$min_no_of_nights = '';
			if ( isset( $bkap_hide_booking_options['min_no_of_nights'] ) && 'on' === $bkap_hide_booking_options['min_no_of_nights'] ) {
				$min_no_of_nights = 'checked';
			}

			echo '<input type="checkbox" id="min_no_of_nights" name="bkap_hide_booking_options[min_no_of_nights]" ' . esc_attr( $min_no_of_nights ) . '/>';
			echo '<label for="min_no_of_nights"> ' . esc_html( $args[0] ) . '</label>';
		}

		/**
		 * Maximum Booking on a Date.
		 *
		 * @param array $args Arguments.
		 * @since 5.13.0
		 */
		public static function bkap_hide_max_booking_on_date_callback( $args ) {

			$bkap_hide_booking_options = get_option( 'bkap_hide_booking_options' );

			$max_booking_on_date = '';
			if ( isset( $bkap_hide_booking_options['max_booking_on_date'] ) && 'on' === $bkap_hide_booking_options['max_booking_on_date'] ) {
				$max_booking_on_date = 'checked';
			}

			echo '<input type="checkbox" id="max_booking_on_date" name="bkap_hide_booking_options[max_booking_on_date]" ' . esc_attr( $max_booking_on_date ) . '/>';
			echo '<label for="max_booking_on_date"> ' . esc_html( $args[0] ) . '</label>';
		}

		/**
		 * Number of Dates to choose.
		 *
		 * @param array $args Arguments.
		 * @since 5.13.0
		 */
		public static function bkap_hide_nod_to_choose_callback( $args ) {

			$bkap_hide_booking_options = get_option( 'bkap_hide_booking_options' );

			$nod_to_choose = '';
			if ( isset( $bkap_hide_booking_options['nod_to_choose'] ) && 'on' === $bkap_hide_booking_options['nod_to_choose'] ) {
				$nod_to_choose = 'checked';
			}

			echo '<input type="checkbox" id="nod_to_choose" name="bkap_hide_booking_options[nod_to_choose]" ' . esc_attr( $nod_to_choose ) . '/>';
			echo '<label for="nod_to_choose"> ' . esc_html( $args[0] ) . '</label>';
		}

		/**
		 * Advance Booking Period.
		 *
		 * @param array $args Arguments.
		 * @since 5.13.0
		 */
		public static function bkap_hide_advance_booking_period_callback( $args ) {

			$bkap_hide_booking_options = get_option( 'bkap_hide_booking_options' );

			$advance_booking_period = '';
			if ( isset( $bkap_hide_booking_options['advance_booking_period'] ) && 'on' === $bkap_hide_booking_options['advance_booking_period'] ) {
				$advance_booking_period = 'checked';
			}

			echo '<input type="checkbox" id="advance_booking_period" name="bkap_hide_booking_options[advance_booking_period]" ' . esc_attr( $advance_booking_period ) . '/>';
			echo '<label for="advance_booking_period"> ' . esc_html( $args[0] ) . '</label>';
		}

		/**
		 * Can be cancelled.
		 *
		 * @param array $args Arguments.
		 * @since 5.13.0
		 */
		public static function bkap_hide_can_be_cancelled_callback( $args ) {

			$bkap_hide_booking_options = get_option( 'bkap_hide_booking_options' );

			$can_be_cancelled = '';
			if ( isset( $bkap_hide_booking_options['can_be_cancelled'] ) && 'on' === $bkap_hide_booking_options['can_be_cancelled'] ) {
				$can_be_cancelled = 'checked';
			}

			echo '<input type="checkbox" id="can_be_cancelled" name="bkap_hide_booking_options[can_be_cancelled]" ' . esc_attr( $can_be_cancelled ) . '/>';
			echo '<label for="can_be_cancelled"> ' . esc_html( $args[0] ) . '</label>';
		}

		/**
		 * Requires Confirmation.
		 *
		 * @param array $args Arguments.
		 * @since 5.13.0
		 */
		public static function bkap_hide_requires_confirmation_callback( $args ) {

			$bkap_hide_booking_options = get_option( 'bkap_hide_booking_options' );

			$requires_confirmation = '';
			if ( isset( $bkap_hide_booking_options['requires_confirmation'] ) && 'on' === $bkap_hide_booking_options['requires_confirmation'] ) {
				$requires_confirmation = 'checked';
			}

			echo '<input type="checkbox" id="requires_confirmation" name="bkap_hide_booking_options[requires_confirmation]" ' . esc_attr( $requires_confirmation ) . '/>';
			echo '<label for="requires_confirmation"> ' . esc_html( $args[0] ) . '</label>';
		}

		/**
		 * Dates in Dropdown.
		 *
		 * @param array $args Arguments.
		 * @since 5.24.0
		 */
		public static function bkap_hide_dates_in_dropdown_callback( $args ) {

			$bkap_hide_booking_options = get_option( 'bkap_hide_booking_options' );

			$dates_in_dropdown = '';
			if ( isset( $bkap_hide_booking_options['dates_in_dropdown'] ) && 'on' === $bkap_hide_booking_options['dates_in_dropdown'] ) {
				$dates_in_dropdown = 'checked';
			}

			echo '<input type="checkbox" id="dates_in_dropdown" name="bkap_hide_booking_options[dates_in_dropdown]" ' . esc_attr( $dates_in_dropdown ) . '/>'; // phpcs:ignore
			echo '<label for="dates_in_dropdown"> ' . esc_html( $args[0] ) . '</label>';
		}

		/**
		 * Purchase without choosing date.
		 *
		 * @param array $args Arguments.
		 * @since 5.13.0
		 */
		public static function bkap_hide_purchase_without_date_callback( $args ) {

			$bkap_hide_booking_options = get_option( 'bkap_hide_booking_options' );

			$purchase_without_date = '';
			if ( isset( $bkap_hide_booking_options['purchase_without_date'] ) && 'on' === $bkap_hide_booking_options['purchase_without_date'] ) {
				$purchase_without_date = 'checked';
			}

			echo '<input type="checkbox" id="purchase_without_date" name="bkap_hide_booking_options[purchase_without_date]" ' . esc_attr( $purchase_without_date ) . '/>';
			echo '<label for="purchase_without_date"> ' . esc_html( $args[0] ) . '</label>';
		}

		/**
		 * Inline Calendar.
		 *
		 * @param array $args Arguments.
		 * @since 5.13.0
		 */
		public static function bkap_hide_inline_calendar_callback( $args ) {

			$bkap_hide_booking_options = get_option( 'bkap_hide_booking_options' );

			$inline_calendar = '';
			if ( isset( $bkap_hide_booking_options['inline_calendar'] ) && 'on' === $bkap_hide_booking_options['inline_calendar'] ) {
				$inline_calendar = 'checked';
			}

			echo '<input type="checkbox" id="inline_calendar" name="bkap_hide_booking_options[inline_calendar]" ' . esc_attr( $inline_calendar ) . '/>';
			echo '<label for="inline_calendar"> ' . esc_html( $args[0] ) . '</label>';
		}

		/**
		 * Booking Type.
		 *
		 * @param array $args Arguments.
		 * @since 5.13.0
		 */
		public static function bkap_hide_booking_type_callback( $args ) {

			$argument                  = $args[0];
			$bkap_hide_booking_options = get_option( 'bkap_hide_booking_options' );
			$selected_booking_type     = isset( $bkap_hide_booking_options['booking_type'] ) ? $bkap_hide_booking_options['booking_type'] : array();
			$booking_types             = bkap_get_booking_types();

			$booking_type_section = '';
			if ( isset( $bkap_hide_booking_options['booking_type_section'] ) && 'on' === $bkap_hide_booking_options['booking_type_section'] ) {
				$booking_type_section = 'checked';
			}

			?>
			<select id="booking_type" class="booking_type"
					name="bkap_hide_booking_options[booking_type][]"
					placehoder="<?php esc_attr_e( 'Select Booking Type', 'woocommerce-booking' ); ?>"
					multiple="multiple">
				<?php
				foreach ( $booking_types as $key => $booking_type ) {
					$option_value = $key;
					$selected     = '';
					if ( in_array( $key, $selected_booking_type, true ) ) {
						$selected = 'selected="selected"';
					}
					?>
					<option value="<?php echo esc_attr( $option_value ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $booking_type['label'] ); ?></option>
					<?php
				}
				?>
			</select>
			<br>
			<label for="booking_type"><?php echo esc_html( $argument[0] ); ?></label>
			<br>
			<br>
			<input type="checkbox" id="booking_type_section" name="bkap_hide_booking_options[booking_type_section]" <?php echo esc_attr( $booking_type_section ); ?>/>
			<label for="booking_type_section"><?php echo esc_html( $argument[1] ); ?></label>
			<?php
		}

		/**
		 * Enable Booking Option.
		 *
		 * @param array $args Arguments.
		 * @since 5.13.0
		 */
		public static function bkap_hide_enable_booking_callback( $args ) {

			$bkap_hide_booking_options = get_option( 'bkap_hide_booking_options' );

			$booking_option = '';
			if ( isset( $bkap_hide_booking_options['enable_booking'] ) && 'on' === $bkap_hide_booking_options['enable_booking'] ) {
				$booking_option = 'checked';
			}

			echo '<input type="checkbox" id="enable_booking" name="bkap_hide_booking_options[enable_booking]" ' . esc_attr( $booking_option ) . '/>';
			echo '<label for="enable_booking"> ' . esc_html( $args[0] ) . '</label>';
		}

		/**
		 * Settings Section callback (Add any additional information messages here)
		 */
		public function bkap_hide_booking_options_callback() {
			?>
			<p><?php echo esc_html__( 'Hide following Booking options of the Booking Meta Box for the Vendors.', 'woocommerce-booking' ); ?></p>
			<?php
		}
	}
}
