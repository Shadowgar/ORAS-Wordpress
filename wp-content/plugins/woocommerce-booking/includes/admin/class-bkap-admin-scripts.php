<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * Class for loading asset files for the Admin.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/Files
 * @category    Classes
 * @since       5.19.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * BKAP Admin Scripts.
 *
 * @since 5.19.0
 */
class BKAP_Admin_Scripts extends BKAP_Admin {

	/**
	 * Construct
	 *
	 * @since 5.19.0
	 */
	public function __construct() {
		parent::__construct();
		add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_css' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_js' ) );
	}

	/**
	 * CSS.
	 *
	 * @since 5.19.0
	 */
	public static function enqueue_css() {

		if ( self::is_on_bkap_page() || self::is_on_product_page() ) {

			if ( self::is_on_bkap_booking_section_page() ) {

				$global_settings = bkap_json_decode( get_option( 'woocommerce_booking_global_settings' ) );
				$calendar_theme  = '' === $global_settings->booking_themes ? 'smoothness' : $global_settings->booking_themes;

				wp_dequeue_style( 'jquery-ui' );
				wp_dequeue_style( 'jquery-ui-style' );

				wp_register_style(
					'bkap-jquery-ui-style',
					BKAP_Files::rewrite_asset_url( "/assets/css/themes/$calendar_theme/jquery-ui.css", BKAP_FILE, true, false ),
					array(),
					BKAP_VERSION,
					false
				);

				wp_enqueue_style(
					'bkap-jquery-ui-datepicker',
					BKAP_Files::rewrite_asset_url( '/assets/css/admin/jquery-ui-datepicker.css', BKAP_FILE ),
					array(),
					BKAP_VERSION
				);

				wp_enqueue_style( 'bkap-jquery-ui-style' );

				BKAP_Admin_Calendar::bkap_load_calendar_styles();
			}

			if ( self::is_on_product_page() || self::is_on_bkap_settings_page() ) {

				// css file for the multi datepicker in admin product pages.
				wp_enqueue_style(
					'bkap-datepick',
					BKAP_Files::rewrite_asset_url( '/assets/css/jquery.datepick.css', BKAP_FILE ),
					array(),
					BKAP_VERSION,
					false
				);

				do_action( 'bkap_after_load_products_css' );
			}

			wp_enqueue_style(
				'timepicker-css',
				BKAP_Files::rewrite_asset_url( '/assets/css/admin/jquery.timepicker.min.css', BKAP_FILE, false, false ),
				array(),
				WC_VERSION
			);

			wp_enqueue_style(
				'bkap-bootstrap',
				BKAP_Files::rewrite_asset_url( '/assets/css/admin/bootstrap.css', BKAP_FILE ),
				array(),
				BKAP_VERSION
			);

			wp_enqueue_style(
				'bkap-bootstrap-tokenfield',
				BKAP_Files::rewrite_asset_url( '/assets/css/admin/bootstrap-tokenfield.css', BKAP_FILE ),
				array(),
				BKAP_VERSION
			);

			wp_enqueue_style(
				'bkap-checkbox',
				BKAP_Files::rewrite_asset_url( '/assets/css/admin/checkbox.css', BKAP_FILE ),
				array(),
				BKAP_VERSION
			);

			wp_enqueue_style(
				'bkap-choices',
				BKAP_Files::rewrite_asset_url( '/assets/css/admin/choices.min.css', BKAP_FILE, false, false ),
				array(),
				BKAP_VERSION
			);

			wp_enqueue_style(
				'bkap-app',
				BKAP_Files::rewrite_asset_url( '/assets/css/admin/app.css', BKAP_FILE ),
				array(),
				BKAP_VERSION
			);

			wp_enqueue_style(
				'bkap-multidatepicker',
				BKAP_Files::rewrite_asset_url( '/assets/css/admin/jquery-ui.multidatespicker.css', BKAP_FILE ),
				array(),
				BKAP_VERSION
			);

			wp_enqueue_style(
				'aw-notification',
				BKAP_Files::rewrite_asset_url( '/assets/css/admin/aw-notification.min.css', BKAP_FILE, false, false ),
				array(),
				BKAP_VERSION
			);

			wp_enqueue_style(
				'bkap-tingle',
				BKAP_Files::rewrite_asset_url( '/assets/css/admin/tingle.min.css', BKAP_FILE, false, false ),
				array(),
				BKAP_VERSION
			);

			wp_enqueue_style(
				'bkap-alertify',
				BKAP_Files::rewrite_asset_url( '/assets/css/admin/alertify.min.css', BKAP_FILE, false, false ),
				array(),
				BKAP_VERSION
			);

			wp_enqueue_style(
				'bkap-alertify-theme',
				BKAP_Files::rewrite_asset_url( '/assets/css/admin/alertify-theme.min.css', BKAP_FILE, false, false ),
				array(),
				BKAP_VERSION
			);

			wp_enqueue_style(
				'bkap-main',
				BKAP_Files::rewrite_asset_url( '/assets/css/admin/main.css', BKAP_FILE ),
				array(),
				BKAP_VERSION,
				false
			);
		}
	}

	/**
	 * JS.
	 *
	 * @since 5.19.0
	 */
	public static function enqueue_js() {

		global $post, $bkap_languages, $bkap_date_formats, $bkap_time_formats, $bkap_days, $bkap_calendar_themes;

		$post_id = isset( $post->ID ) ? $post->ID : 0;
		$_post   = $post;

		if ( self::is_on_bkap_page() || self::is_on_product_page() ) {

			wp_enqueue_script(
				'multi-datepick',
				BKAP_Files::rewrite_asset_url( '/assets/js/jquery-ui.multidatespicker.js', BKAP_FILE ),
				array( 'jquery', 'jquery-ui-datepicker' ),
				BKAP_VERSION,
				true
			);

			wp_enqueue_script(
				'datepick',
				BKAP_Files::rewrite_asset_url( '/assets/js/jquery.datepick.js', BKAP_FILE ),
				array(),
				BKAP_VERSION,
				true
			);

			wp_enqueue_script(
				'bkap-vue',
				BKAP_Files::rewrite_asset_url( '/assets/js/admin/vue' . ( BKAP_DEV_MODE ? '-dev' : '.min' ) . '.js', BKAP_FILE, false, false ),
				array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker' ),
				BKAP_VERSION,
				true
			);

			wp_enqueue_script(
				'bkap-vue-router',
				BKAP_Files::rewrite_asset_url( '/assets/js/admin/vue-router.js', BKAP_FILE ),
				array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker' ),
				BKAP_VERSION,
				true
			);

			wp_enqueue_script(
				'bkap-vue-axios',
				BKAP_Files::rewrite_asset_url( '/assets/js/admin/axios.min.js', BKAP_FILE, false, false ),
				array(),
				BKAP_VERSION,
				true
			);

			wp_enqueue_script(
				'bkap-bootstrap',
				BKAP_Files::rewrite_asset_url( '/assets/js/admin/bootstrap.min.js', BKAP_FILE, false, false ),
				array( 'bkap-popper' ),
				BKAP_VERSION,
				true
			);

			wp_enqueue_script(
				'bkap-bootstrap-tokenfield',
				BKAP_Files::rewrite_asset_url( '/assets/js/admin/bootstrap-tokenfield.js', BKAP_FILE ),
				array(),
				BKAP_VERSION,
				true
			);

			wp_enqueue_script(
				'bkap-choices',
				BKAP_Files::rewrite_asset_url( '/assets/js/admin/choices.min.js', BKAP_FILE, false, false ),
				array(),
				BKAP_VERSION,
				true
			);

			wp_enqueue_script(
				'bkap-popper',
				BKAP_Files::rewrite_asset_url( '/assets/js/admin/popper.min.js', BKAP_FILE, false, false ),
				array(),
				BKAP_VERSION,
				true
			);

			wp_enqueue_script(
				'aw-notification',
				BKAP_Files::rewrite_asset_url( '/assets/js/admin/aw-notification.min.js', BKAP_FILE, false, false ),
				array(),
				BKAP_VERSION,
				true
			);

			wp_enqueue_script(
				'bkap-tingle',
				BKAP_Files::rewrite_asset_url( '/assets/js/admin/tingle.min.js', BKAP_FILE, false, false ),
				array(),
				BKAP_VERSION,
				true
			);

			wp_enqueue_script(
				'bkap-alertify',
				BKAP_Files::rewrite_asset_url( '/assets/js/admin/alertify.min.js', BKAP_FILE, false, false ),
				array(),
				BKAP_VERSION,
				true
			);

			wp_enqueue_script(
				'tyche',
				BKAP_Files::rewrite_asset_url( '/assets/js/tyche.js', BKAP_FILE ),
				array(),
				BKAP_VERSION,
				true
			);

			wp_register_script(
				'bkap-main',
				BKAP_Files::rewrite_asset_url( '/assets/js/admin/main.js', BKAP_FILE ),
				array( 'jquery', 'jquery-ui-core', 'tyche' ),
				BKAP_VERSION,
				true
			);

			wp_localize_script(
				'bkap-main',
				'bkap_params',
				array(
					'nonce'                                => wp_create_nonce( 'wp_rest' ),
					'ajax_url'                             => AJAX_URL,
					'bkap_rest_url'                        => get_rest_url() . BKAP_Admin_API::endpoint(),
					'bkap_calendar_theme_url'              => BKAP_Files::rewrite_asset_url( '/assets/css/themes/{CALENDAR_THEME}/jquery-ui.css', BKAP_FILE, true, false ),
					'bkap_calendar_lang_url'               => BKAP_Files::rewrite_asset_url( '/assets/js/i18n/jquery.ui.datepicker-{LANG}.js', BKAP_FILE, true, false ),
					'post_id'                              => $post_id,
					'user_id'                              => get_current_user_id(),
					'vue'                                  => array(
						'label'                            => array(
							'axios_get_error' => __( 'An error has been encountered while trying to establish a connection to your store.', 'woocommerce-booking' ),
							'saving_loader'   => __( 'Saving, please wait ...', 'woocommerce-booking' ),
							'loading_loader'  => __( 'Loading, please wait ...', 'woocommerce-booking' ),
							'save_settings'   => __( 'Save Settings', 'woocommerce-booking' ),
						),
						'el_error_message' => apply_filters( 'bkap_el_error_message', '' ),
					),
					'empty_trash'                          => __( 'Trash is being emptied, please wait...', 'woocommerce-booking' ),
					'empty_trash_text'                     => __( 'Empty Trash', 'woocommerce-booking' ),
					'are_you_sure_you_want_to_empty_trash' => __( 'Are you sure you want to delete all of the trashed items?', 'woocommerce-booking' ),
					'menu'    => array(
						'home'         => __( 'Home', 'woocommerce-booking' ),
						'settings'     => __( 'Settings', 'woocommerce-booking' ),
						'appearance'   => __( 'Appearance', 'woocommerce-booking' ),
						'integrations' => __( 'Integrations', 'woocommerce-booking' ),
						'addons'       => __( 'Addons', 'woocommerce-booking' ),
						'booking'      => __( 'Bookings', 'woocommerce-booking' ),
						'reminders'    => __( 'Reminders', 'woocommerce-booking' ),
						'resources'    => __( 'Resources', 'woocommerce-booking' ),
					),
					'submenu' => array(
						'home'         => apply_filters(
							'bkap_home_tabs',
							array(
								'welcome' => __( 'Welcome', 'woocommerce-booking' ),
								'faq'     => __( 'FAQ', 'woocommerce-booking' ),
								'status'  => __( 'Status', 'woocommerce-booking' ),
							)
						),
						'settings'     => array(
							'global'               => __( 'Global', 'woocommerce-booking' ),
							'bulk_booking'         => __( 'Bulk Booking', 'woocommerce-booking' ),
							'product_availability' => __( 'Product Availability', 'woocommerce-booking' ),
							'vendor_options'       => __( 'Vendor Options', 'woocommerce-booking' ),
						),
						'appearance'   => array(
							'label'    => __( 'Label & Messages', 'woocommerce-booking' ),
							'calendar' => __( 'Calendar', 'woocommerce-booking' ),
						),
						'integrations' => array(
							'google'    => __( 'Google Calendar', 'woocommerce-booking' ),
							'zoom'      => __( 'Zoom Meetings', 'woocommerce-booking' ),
							'twilio'    => __( 'Twilio SMS', 'woocommerce-booking' ),
							'fluentcrm' => __( 'Fluent CRM', 'woocommerce-booking' ),
							'zapier'    => __( 'Zapier', 'woocommerce-booking' ),
							'outlook'   => __( 'Outlook Calendar', 'woocommerce-booking' ),
						),
						'addons'       => array(
							'partial'   => __( 'Partial Deposits', 'woocommerce-booking' ),
							'recurring' => __( 'Recurring Bookings', 'woocommerce-booking' ),
							'print'     => __( 'Printable Tickets', 'woocommerce-booking' ),
							'seasonal'  => __( 'Seasonal Pricing', 'woocommerce-booking' ),
							'rental'    => __( 'Rental System', 'woocommerce-booking' ),
						),
						'booking'      => array(
							'create_booking'  => __( 'Create Booking', 'woocommerce-booking' ),
							'calendar'        => __( 'Calendar', 'woocommerce-booking' ),
							'view_bookings'   => __( 'View Bookings', 'woocommerce-booking' ),
							'import_bookings' => __( 'Import Bookings', 'woocommerce-booking' ),
						),
						'reminders'    => array(
							'view_reminders'  => __( 'View Reminders', 'woocommerce-booking' ),
							'add_reminder'    => __( 'Add Reminder', 'woocommerce-booking' ),
							'manual_reminder' => __( 'Manual Reminder', 'woocommerce-booking' ),
						),
						'resources'    => array(
							'view_resource' => __( 'View Resources', 'woocommerce-booking' ),
							'add_resource'  => __( 'Add Resource', 'woocommerce-booking' ),
						),
					),
				)
			);

			wp_enqueue_script( 'bkap-main' );

			if ( self::is_on_product_page() || self::is_on_bkap_settings_page() ) {

				if ( self::is_on_bkap_settings_page() ) {
					$_post         = new stdClass();
					$_post->filter = 'raw';
					$_post->ID     = 0;
				}

				wp_register_script(
					'bkap-metabox-booking-template',
					BKAP_Files::rewrite_asset_url( '/assets/js/admin/views/metabox/template.js', BKAP_FILE ),
					array(),
					BKAP_VERSION,
					true
				);

				wp_register_script(
					'bkap-metabox-booking',
					BKAP_Files::rewrite_asset_url( '/assets/js/admin/views/metabox/booking/index.js', BKAP_FILE ),
					array(),
					BKAP_VERSION,
					true
				);

				$data = BKAP_Admin_API_Metabox_Booking::fetch_metabox_booking_data( true, $_post );

				if ( isset( $_GET['bkap_bookable'] ) && 'true' === $_GET['bkap_bookable'] ) { // phpcs:disable WordPress.Security.NonceVerification
					$data['general']['settings']['booking_enable_date'] = 'on';
				}

				wp_localize_script(
					'bkap-metabox-booking',
					'bkap_metabox_booking_param',
					array(
						'data'  => $data,
						'_data' => $data,
						'label' => array(
							'date_time_text'               => __( 'Use this if you wish to take bookings for time slots. For e.g. coaching classes, appointments, ground on rent etc.', 'woocommerce-booking' ),
							'fixed_time_text'              => __( 'Use this if you have fixed time slots for bookings. For e.g. coaching classes, appointments etc.', 'woocommerce-booking' ),
							'duration_time_text'           => __( 'Use this if you want your customer to select required duration for booking. For e.g. sports ground booking, appointments etc.', 'woocommerce-booking' ),
							'multidates_text'              => __( 'Use this for multiple dates bookings.', 'woocommerce-booking' ),
							'multidates_fixedtime_text'    => __( 'Use this for multiple dates and fixed time slots bookings.', 'woocommerce-booking' ),
							'single_day_text'              => __( 'Use this to take bookings like single day tours, event, appointments etc.', 'woocommerce-booking' ),
							'multiple_nights_text'         => __( 'Use this for hotel bookings, rentals, etc. Checkout date is not included in the booking period.', 'woocommerce-booking' ),
							'single_day_text'              => __( 'Use this to take bookings like single day tours, event, appointments etc.', 'woocommerce-booking' ),
							'input_duration_text'          => __( 'Duration', 'woocommerce-booking' ),
							'connect_to_google_calendar'   => __( 'Connect to Google Calendar', 'woocommerce-booking' ),
							'logout_from_google_calendar'  => __( 'Logout from Google Calendar', 'woocommerce-booking' ),
							'error_is_grouped_product'     => __( 'Google Calendar Sync cannot be set up for a Grouped Product. Please set up the sync settings for individual child products.', 'woocommerce-booking' ),
							'imported_success_message'     => __( 'Event(s) have been successfully imported.', 'woocommerce-booking' ),
							'imported_error_message'       => __( 'Error encountered while trying to import event(s).', 'woocommerce-booking' ),
							'save_ics_url_success_message' => __( 'ICS URL has been saved.', 'woocommerce-booking' ),
							'ics_url_is_empty_message'     => __( 'Cannot save! ICS URL is empty.', 'woocommerce-booking' ),
							'logout_from_calendar_loader'  => __( 'Logging out from Google Calendar, please wait...', 'woocommerce-booking' ),
							'fluentcrm_plugin_not_activated_message' => __( 'FluentCRM plugin is not active. Please install and activate the plugin to get started.', 'woocommerce-booking' ),
							'rental_plugin_not_activated_message' => __( 'Rental System Addon is not active. Please install and activate the plugin to get started.', 'woocommerce-booking' ),
							'partial_payments_plugin_not_activated_message' => __( 'Partial Deposits Addon is not active. Please install and activate the plugin to get started.', 'woocommerce-booking' ),
							'seasonal_pricing_plugin_not_activated_message' => __( 'Seasonal Pricing Addon is not active. Please install and activate the plugin to get started.', 'woocommerce-booking' ),
							'fluentcrm_settings_not_present_message' => __( 'API Connection Settings for the FluentCRM plugin seem to be missing. Please configure the connection settings on the Integrations page.', 'woocommerce-booking' ),
							'outlook_plugin_not_activated_message' => __( 'BKAP Outlook Calendar add-on plugin is not active. Please install and activate the add-on to get started.', 'woocommerce-booking' ),
							'connect_to_outlook'           => __( 'Connect to Outlook', 'woocommerce-booking' ),
							'logout_from_outlook'          => __( 'Logout from Outlook', 'woocommerce-booking' ),
							'l_error_message'              => apply_filters( 'bkap_el_error_message', '' ),
							'bl_error_message'             => apply_filters( 'bkap_bl_error_message', '' ),
							'zapier_integration_trigger_disabled' => sprintf(
								/* translators: %1$s openeing tag, %2$s closing tag */
								__( '%1$s This Trigger is disabled. Please visit the Booking Settings page to enable it. %2$s', 'woocommerce-booking' ),
								'<span>',
								'</span'
							),
							'create_booking_no_triggers_found' => __( 'No Create Booking Triggers found!', 'woocommerce-booking' ),
							'update_booking_no_triggers_found' => __( 'No Update Booking Triggers found!', 'woocommerce-booking' ),
							'delete_booking_no_triggers_found' => __( 'No Delete Booking Triggers found!', 'woocommerce-booking' ),
							'unsaved_changes' => __( 'Unsaved Changes detected!', 'woocommerce-booking' ),
							'unsaved_changes_description' => __( 'Hey! You\'re moving to a new section without saving changes in the former section. What do you want to do?', 'woocommerce-booking' ),
						),
					)
				);

				wp_enqueue_script( 'bkap-metabox-booking-template' );
				wp_enqueue_script( 'bkap-metabox-booking' );

				if ( ! self::is_on_bkap_settings_page() ) {

					wp_enqueue_script(
						'bkap-metabox-booking-component',
						BKAP_Files::rewrite_asset_url( '/assets/js/admin/views/metabox/booking/component.js', BKAP_FILE ),
						array(),
						BKAP_VERSION,
						true
					);
				}
			}

			if ( self::is_on_bkap_page() ) {

            	$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : ''; // phpcs:ignore

				foreach ( $bkap_date_formats as $key => $format ) {
					$bkap_dates_as_formats[ $key ] = gmdate( $format );
				}
				switch ( $action ) {
					case 'onboarding':
						do_action( 'bkap_enqueue_js_onboarding' );

						wp_register_script(
							'bkap-view-datepicker',
							BKAP_Files::rewrite_asset_url( '/assets/js/admin/views/datepicker.js', BKAP_FILE ),
							array(),
							BKAP_VERSION,
							true
						);

						wp_enqueue_script( 'bkap-view-datepicker' );

						wp_register_script(
							'bkap-onboarding',
							BKAP_Files::rewrite_asset_url( '/assets/js/admin/views/onboarding/onboarding.js', BKAP_FILE ),
							array(),
							BKAP_VERSION,
							true
						);

						wp_localize_script(
							'bkap-onboarding',
							'bkap_onboarding_param',
							array(
								'appearance_data'      => BKAP_Admin_API_Calendar::fetch_calendar_data( true ),
								'booking_data'         => BKAP_Admin_API_Onboarding::fetch_booking_data( true ),
								'labels_data'          => BKAP_Admin_API_Onboarding::fetch_labels_data( true ),
								'languages'            => $bkap_languages,
								'bkap_date_formats'    => $bkap_dates_as_formats,
								'bkap_time_formats'    => $bkap_time_formats,
								'bkap_days'            => $bkap_days,
								'bkap_weekdays'        => bkap_weekdays(),
								'bkap_booking_types'   => bkap_get_booking_types(),
								'bkap_calendar_themes' => $bkap_calendar_themes,
							)
						);

						wp_enqueue_script( 'bkap-onboarding' );
						break;

					case 'booking':
						wp_register_script(
							'bkap-view-import-booking',
							BKAP_Files::rewrite_asset_url( '/assets/js/admin/views/booking/import-booking.js', BKAP_FILE ),
							array(),
							BKAP_VERSION,
							true
						);

						wp_localize_script(
							'bkap-view-import-booking',
							'bkap_view_import_booking_param',
							array(
								'label' => array(
									'mapping_event_loader' => __( 'Mapping event, please wait ...', 'woocommerce-booking' ),
								),
							)
						);

						wp_register_script(
							'bkap-view-create-booking',
							BKAP_Files::rewrite_asset_url( '/assets/js/admin/views/booking/create-booking.js', BKAP_FILE ),
							array(),
							BKAP_VERSION,
							true
						);

						$is_create_booking_post_data      = isset( $_POST['create_booking_post_data'] ); // phpcs:ignore
						$create_booking_post_data         = $is_create_booking_post_data && is_array( $_POST['create_booking_post_data'] ) ? $_POST['create_booking_post_data'] : array(); // phpcs:ignore
						$create_booking_label             = '';
						$create_booking_label_description = __( 'You can create a new booking for a customer here. This form will create a booking for the user, and optionally an associated order. Created orders will be marked as processing.', 'woocommerce-booking' );
						$create_booking_post_error        = isset( $_POST['create_booking_post_error'] ) ? sanitize_text_field( wp_unslash( $_POST['create_booking_post_error'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

						if ( $is_create_booking_post_data && isset( $create_booking_post_data['order_type'] ) && '' !== $create_booking_post_data['order_type'] ) {
							$create_booking_label_description = '';
							switch ( $create_booking_post_data['order_type'] ) {
								case 'existing':
									$create_booking_label             = __( 'Create Booking for Existing Order', 'woocommerce-booking' );
									/* Translators: %s Order ID */
									$create_booking_label_description = sprintf( __( 'Order ID #%s.', 'woocommerce-booking' ), $create_booking_post_data['existing_order_id'] );
									break;
								case 'only_booking':
									$create_booking_label = __( 'Create Booking without Order', 'woocommerce-booking' );
									break;
								case 'new':
								default:
									$create_booking_label = __( 'Create Booking', 'woocommerce-booking' );
									break;
							}

							if ( '' !== $create_booking_post_error ) {
								$is_create_booking_post_data = false;
							}
						}

						wp_localize_script(
							'bkap-view-create-booking',
							'bkap_view_create_booking_param',
							array(
								'label' => array(
									'create_booking'             => __( $create_booking_label, 'woocommerce-booking' ), // phpcs:ignore,
									'create_booking_description' => __( $create_booking_label_description, 'woocommerce-booking' ), // phpcs:ignore,
									'create_booking_success' => __( 'Booking has been created successfully.', 'woocommerce-booking' ),
									'create_booking_error' => __( 'An error was encountered while trying to create a new booking. Please try again.', 'woocommerce-booking' ),
								),
								'is_create_booking_post_data' => $is_create_booking_post_data ? 'true' : 'false',
								'create_booking_post_error' => $create_booking_post_error,
							)
						);

						wp_register_script(
							'bkap-view-view-bookings',
							BKAP_Files::rewrite_asset_url( '/assets/js/admin/views/booking/view-bookings.js', BKAP_FILE ),
							array(),
							BKAP_VERSION,
							true
						);

						wp_localize_script(
							'bkap-view-view-bookings',
							'bkap_view_view_bookings_param',
							array(
								'label'      => array(
									'print_loader'         => __( 'Generating Printable Page, please wait!', 'woocommerce-booking' ),
									'csv_loader'           => __( 'Generating CSV Data, please wait!', 'woocommerce-booking' ),
									'updating_booking_loader' => __( 'Updating Booking Data, please wait!', 'woocommerce-booking' ),
									'completed'            => __( 'completed', 'woocommerce-booking' ),
									'error_enable_pop_up_window' => __( 'Pop-up window feature is required but it seems it has been disabled on your device. Please have it enabled and try again.', 'woocommerce-booking' ),
									'delete_booking'       => __( 'Deleting selected booking, please wait!', 'woocommerce-booking' ),
									'delete_bookings'      => __( 'Deleting selected bookings, please wait!', 'woocommerce-booking' ),
									'confirm_booking'      => __( 'Confirming selected booking, please wait!', 'woocommerce-booking' ),
									'confirm_bookings'     => __( 'Confirming selected bookings, please wait!', 'woocommerce-booking' ),
									'cancel_booking'       => __( 'Cancelling selected booking, please wait!', 'woocommerce-booking' ),
									'cancel_bookings'      => __( 'Cancelling selected bookings, please wait!', 'woocommerce-booking' ),
									'no_bookings_selected' => __( 'No Bookings have been selected.', 'woocommerce-booking' ),
									'booking_caps'         => __( 'Booking', 'woocommerce-booking' ),
									'are_you_sure_you_want_to' => __( 'Are you sure you want to', 'woocommerce-booking' ),
									'are_you_sure_you_want_to_delete_bookings' => __( 'Are you sure you want to DELETE the selected Booking IDs?', 'woocommerce-booking' ),
									'are_you_sure_you_want_to_confirm_bookings' => __( 'Are you sure you want to CONFIRM the selected Booking IDs?', 'woocommerce-booking' ),
									'are_you_sure_you_want_to_cancel_bookings' => __( 'Are you sure you want to CANCEL the selected Booking IDs?', 'woocommerce-booking' ),
									'delete_bookings_text' => __( 'Delete Bookings', 'woocommerce-booking' ),
									'confirm_bookings_text' => __( 'Confirm Bookings', 'woocommerce-booking' ),
									'cancel_bookings_text' => __( 'Cancel Bookings', 'woocommerce-booking' ),
								),
								'data'       => BKAP_Admin_API_View_Bookings::fetch_view_bookings_data( true ),
								'booking_id' => isset( $_GET['booking_id'] ) ? sanitize_text_field( $_GET['booking_id'] ) : '', // phpcs:ignore
							)
						);

						// For Edit Booking.

						if ( ! isset( $_POST['create_booking_post_data'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
							wp_enqueue_script( 'jquery' );
							wp_enqueue_script( 'jquery-ui-datepicker' );

							$curr_lang = bkap_icl_lang_code( bkap_global_setting()->booking_language );

							wp_enqueue_script(
								"$curr_lang",
								BKAP_Files::rewrite_asset_url( "/assets/js/i18n/jquery.ui.datepicker-$curr_lang.js", BKAP_FILE, true ),
								'',
								BKAP_VERSION,
								true
							);

							wp_enqueue_script(
								'accounting',
								WC()->plugin_url() . '/assets/js/accounting/accounting.min.js',
								array( 'jquery' ),
								BKAP_VERSION,
								true
							);

							if ( ! in_array( 'font-awesome/index.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
								if ( ! wp_style_is( 'font-awesome', 'enqueued' ) ) {
									wp_enqueue_style(
										'bkap-font-awesome-min',
										BKAP_Files::rewrite_asset_url( '/assets/css/font-awesome/css/all.css', BKAP_FILE, true ),
										'',
										BKAP_VERSION,
										false
									);
								}
							}

							wp_register_script(
								'bkap-init-datepicker',
								BKAP_Files::rewrite_asset_url( '/assets/js/initialize-datepicker.js', BKAP_FILE ),
								array(),
								BKAP_VERSION,
								true
							);

							wp_localize_script(
								'bkap-init-datepicker',
								'bkap_init_params',
								apply_filters(
									'bkap_init_parameter_localize_script',
									array()
								)
							);

							wp_enqueue_script( 'bkap-init-datepicker' );

							wp_register_script(
								'bkap-process-functions',
								BKAP_Files::rewrite_asset_url( '/assets/js/booking-process-functions.js', BKAP_FILE ),
								array(),
								BKAP_VERSION,
								true
							);

							wp_localize_script(
								'bkap-process-functions',
								'bkap_process_params',
								array()
							);

							wp_localize_script(
								'bkap-process-functions',
								'product_id',
								array()
							);

							wp_enqueue_script( 'bkap-process-functions' );

							wp_enqueue_script(
								'booking-process',
								BKAP_Files::rewrite_asset_url( '/assets/js/booking-process.js', BKAP_FILE ),
								array(),
								BKAP_VERSION,
								true
							);
						}

						BKAP_Admin_Calendar::enqueue_calendar_scripts();

						wp_register_script(
							'bkap-view-booking-calendar',
							BKAP_Files::rewrite_asset_url( '/assets/js/admin/views/booking/booking-calendar.js', BKAP_FILE ),
							array( 'jquery', 'bkap-qtip', 'full-js', 'locales-js', 'bkap-images-loaded', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-position', 'jquery-ui-selectmenu' ),
							BKAP_VERSION,
							true
						);

						$timeslots     = apply_filters( 'bkap_calendar_timeslot_params', '00:00', '24:00' );
						$timeslots     = ( ! is_array( $timeslots ) ) ? array( '00:00', '24:00' ) : $timeslots;
						$initialview   = apply_filters( 'bkap_calendar_initial_view_params', 'timeGridWeek' );
						$headertoolbar = apply_filters(
							'bkap_calendar_header_toolbar_params',
							wp_json_encode(
								array(
									'left'   => 'prev,next,today',
									'center' => 'title',
									'right'  => 'dayGridMonth,timeGridWeek,timeGridDay',
								)
							)
						);
						$daymaxevents  = apply_filters( 'bkap_calendar_day_max_events_params', 'true' );

						wp_localize_script(
							'bkap-view-booking-calendar',
							'bkap_view_booking_calendar_param',
							array(
								'lang'           => bkap_global_setting()->booking_language,
								'label'          => array(
									'loading_calendar_events' => __( 'BKAP Partial Deposits add-on plugin is not active. Please install and activate the add-on to get started.', 'woocommerce-booking' ),
								),
								'ajaxurl'        => admin_url( 'admin-ajax.php', is_ssl() ? 'https' : 'http' ),
								'loading'        => __( 'Loading', 'woocommerce-booking' ),
								'tip_close'      => __( 'Close', 'woocommerce-booking' ),
								'bkap_vendor_id' => isset( $vendor_id ) && '' !== $vendor_id ? $vendor_id : '',
								'timeslots'      => $timeslots,
								'initialview'    => $initialview,
								'headertoolbar'  => $headertoolbar,
								'daymaxevents'   => $daymaxevents,
							)
						);

						wp_register_script(
							'bkap-view-bookings',
							BKAP_Files::rewrite_asset_url( '/assets/js/admin/views/booking/index.js', BKAP_FILE ),
							array(),
							BKAP_VERSION,
							true
						);

						wp_enqueue_script( 'bkap-view-import-booking' );
						wp_enqueue_script( 'bkap-view-create-booking' );
						wp_enqueue_script( 'bkap-view-view-bookings' );
						wp_enqueue_script( 'bkap-view-booking-calendar' );
						wp_enqueue_script( 'bkap-view-bookings' );
						wp_enqueue_script(
							'jquery-blockui',
							WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI.js',
							array( 'jquery' ),
							'2.70',
							true
						);

						wp_enqueue_style(
							'bkap-booking',
							BKAP_Files::rewrite_asset_url( '/assets/css/booking.css', BKAP_FILE ),
							'',
							BKAP_VERSION,
							false
						);
						break;

					case 'addons':
						wp_register_script(
							'bkap-view-partial-deposits',
							BKAP_Files::rewrite_asset_url( '/assets/js/admin/views/addons/partial-deposits.js', BKAP_FILE ),
							array(),
							BKAP_VERSION,
							true
						);

						wp_localize_script(
							'bkap-view-partial-deposits',
							'bkap_view_partial_deposits_param',
							array(
								'data' => BKAP_Admin_API_Partial_Deposits::fetch_partial_deposits_data( true ),
								'plugin_not_activated_message' => __( 'BKAP Partial Deposits add-on plugin is not active. Please install and activate the add-on to get started.', 'woocommerce-booking' ),
							)
						);

						wp_register_script(
							'bkap-view-recurring-bookings',
							BKAP_Files::rewrite_asset_url( '/assets/js/admin/views/addons/recurring-bookings.js', BKAP_FILE ),
							array(),
							BKAP_VERSION,
							true
						);

						wp_localize_script(
							'bkap-view-recurring-bookings',
							'bkap_view_recurring_bookings_param',
							array(
								'data' => BKAP_Admin_API_Recurring_Bookings::fetch_recurring_bookings_data( true ),
								'plugin_not_activated_message' => __( 'BKAP Recurring Bookings add-on plugin is not active. Please install and activate the add-on to get started.', 'woocommerce-booking' ),
							)
						);

						wp_register_script(
							'bkap-view-printable-tickets',
							BKAP_Files::rewrite_asset_url( '/assets/js/admin/views/addons/printable-tickets.js', BKAP_FILE ),
							array(),
							BKAP_VERSION,
							true
						);

						wp_localize_script(
							'bkap-view-printable-tickets',
							'bkap_view_printable_tickets_param',
							array(
								'data' => BKAP_Admin_API_Printable_Tickets::fetch_printable_tickets_data( true ),
								'plugin_not_activated_message' => __( 'BKAP Printable Tickets add-on plugin is not active. Please install and activate the add-on to get started.', 'woocommerce-booking' ),
							)
						);

						wp_register_script(
							'bkap-view-seasonal-pricing',
							BKAP_Files::rewrite_asset_url( '/assets/js/admin/views/addons/seasonal-pricing.js', BKAP_FILE ),
							array(),
							BKAP_VERSION,
							true
						);

						$seasonal_active = false;
						if ( is_plugin_active( 'bkap-seasonal-pricing/seasonal_pricing.php' ) ) {
							$seasonal_active = true;
						}

						wp_localize_script(
							'bkap-view-seasonal-pricing',
							'bkap_view_seasonal_pricing_param',
							array(
								'data'             => $seasonal_active ? BKAP_Admin_API_Seasonal_Pricing::fetch_seasonal_pricing_data( true ) : array(),
								'wp_roles'         => BKAP_Admin_API_Seasonal_Pricing::get_wp_roles(),
								'wc_currency_args' => bkap_common::get_currency_args(),

								'plugin_not_activated_message' => __( 'BKAP Seasonal Pricing add-on plugin is not active. Please install and activate the add-on to get started.', 'woocommerce-booking' ),
							)
						);

						wp_register_script(
							'bkap-view-rental-system',
							BKAP_Files::rewrite_asset_url( '/assets/js/admin/views/addons/rental-system.js', BKAP_FILE ),
							array(),
							BKAP_VERSION,
							true
						);

						wp_localize_script(
							'bkap-view-rental-system',
							'bkap_view_rental_system_param',
							array(
								'data'                         => BKAP_Admin_API_Rental_System::fetch_rental_system_data( true ),
								'plugin_not_activated_message' => __( 'BKAP Rental System add-on plugin is not active. Please install and activate the add-on to get started.', 'woocommerce-booking' ),
							)
						);

						wp_register_script(
							'bkap-view-addons',
							BKAP_Files::rewrite_asset_url( '/assets/js/admin/views/addons/index.js', BKAP_FILE ),
							array(),
							BKAP_VERSION,
							true
						);

						wp_enqueue_script( 'bkap-view-partial-deposits' );
						wp_enqueue_script( 'bkap-view-recurring-bookings' );
						wp_enqueue_script( 'bkap-view-printable-tickets' );
						wp_enqueue_script( 'bkap-view-seasonal-pricing' );
						wp_enqueue_script( 'bkap-view-rental-system' );
						wp_enqueue_script( 'bkap-view-addons' );
						break;

					case 'integrations':
						wp_register_script(
							'bkap-view-zoom',
							BKAP_Files::rewrite_asset_url( '/assets/js/admin/views/integrations/zoom.js', BKAP_FILE ),
							array(),
							BKAP_VERSION,
							true
						);

						wp_localize_script(
							'bkap-view-zoom',
							'bkap_view_zoom_param',
							array(
								'data'  => BKAP_Admin_API_Zoom::fetch_zoom_data( true ),
								'label' => array(
									'test_connection_success_message' => __( 'Test connection succeeded!', 'woocommerce-booking' ),
									'test_connection_error_message'   => __( 'Test connection Failed', 'woocommerce-booking' ),
									'assign_bookings_success_message' => __( 'Meeting(s) are getting assigned to the bookings in background!', 'woocommerce-booking' ),
									'assign_bookings_error_message'   => __( 'Error while trying to assign meetings to the bookings', 'woocommerce-booking' ),
									'zoom_connection_success_message' => __( 'Successfully connected to Zoom.', 'woocommerce-booking' ),
									'zoom_connection_failure_message' => __( 'Failed to connect to your account, please try again. If the problem persists then please contact Support team.', 'woocommerce-booking' ),
									'zoom_connection_logout_message'  => __( 'Logged out successfully.', 'woocommerce-booking' ),
								),
							)
						);

						wp_register_script(
							'bkap-view-zapier',
							BKAP_Files::rewrite_asset_url( '/assets/js/admin/views/integrations/zapier.js', BKAP_FILE ),
							array(),
							BKAP_VERSION,
							true
						);

						wp_localize_script(
							'bkap-view-zapier',
							'bkap_view_zapier_param',
							array(
								'data'  => BKAP_Admin_API_Zapier::fetch_zapier_data( true ),
								'logs'  => BKAP_Zapier::get_logs(),
								'label' => array(
									'flushing_loader' => __( 'Flushing Logs, please wait ...', 'woocommerce-booking' ),
									'flush_logs_confirmation_text' => __( 'Are you sure you want to clear all logs from the database?', 'woocommerce-booking' ),
								),
							)
						);

						wp_register_script(
							'bkap-view-twilio',
							BKAP_Files::rewrite_asset_url( '/assets/js/admin/views/integrations/twilio.js', BKAP_FILE ),
							array(),
							BKAP_VERSION,
							true
						);

						wp_localize_script(
							'bkap-view-twilio',
							'bkap_view_twilio_param',
							array(
								'data'  => BKAP_Admin_API_Twilio::fetch_twilio_data( true ),
								'label' => array(
									'sending_sms_loader' => __( 'Sending SMS, please wait ...', 'woocommerce-booking' ),
									'send_test_sms'      => __( 'Send Test SMS', 'woocommerce-booking' ),
								),
							)
						);

						wp_register_script(
							'bkap-view-google-calendar',
							BKAP_Files::rewrite_asset_url( '/assets/js/admin/views/integrations/google-calendar.js', BKAP_FILE ),
							array(),
							BKAP_VERSION,
							true
						);

						wp_localize_script(
							'bkap-view-google-calendar',
							'bkap_view_google_calendar_param',
							array(
								'data'  => BKAP_Admin_API_Google_Calendar::fetch_google_calendar_data( true ),
								'label' => array(
									'imported_success_message' => __( 'Event(s) have been successfully imported.', 'woocommerce-booking' ),
									'imported_error_message'   => __( 'Error encountered while trying to import event(s).', 'woocommerce-booking' ),
									'save_ics_url_success_message' => __( 'ICS URL has been saved.', 'woocommerce-booking' ),
									'ics_url_is_empty_message' => __( 'Cannot save! ICS URL is empty.', 'woocommerce-booking' ),
									'connect_to_google_calendar'  => __( 'Connect to Google Calendar', 'woocommerce-booking' ),
									'logout_from_google_calendar' => __( 'Logout from Google Calendar', 'woocommerce-booking' ),
									'logout_from_calendar_loader' => __( 'Logging out from Google Calendar, please wait...', 'woocommerce-booking' ),
								),
							)
						);

						wp_register_script(
							'bkap-view-fluent-crm',
							BKAP_Files::rewrite_asset_url( '/assets/js/admin/views/integrations/fluent-crm.js', BKAP_FILE ),
							array(),
							BKAP_VERSION,
							true
						);

						wp_localize_script(
							'bkap-view-fluent-crm',
							'bkap_view_fluent_crm_param',
							array(
								'data'                  => BKAP_Admin_API_FluentCRM::fetch_fluent_crm_data( true ),
								'plugin_not_activated_message' => __( 'FluentCRM plugin is not active. Please install and activate the plugin to get started.', 'woocommerce-booking' ),
								'l_error_message' => apply_filters( 'bkap_el_error_message', '' ),
							)
						);

						wp_register_script(
							'bkap-view-outlook-calendar',
							BKAP_Files::rewrite_asset_url( '/assets/js/admin/views/integrations/outlook-calendar.js', BKAP_FILE ),
							array(),
							BKAP_VERSION,
							true
						);

						wp_localize_script(
							'bkap-view-outlook-calendar',
							'bkap_view_outlook_calendar_param',
							array(
								'data'  => BKAP_Admin_API_Outlook_Calendar::fetch_outlook_calendar_data( true ),
								'plugin_not_activated_message' => __( 'BKAP Outlook Calendar add-on plugin is not active. Please install and activate the add-on to get started.', 'woocommerce-booking' ),
								'label' => array(
									'connect_to_outlook'  => __( 'Connect to Outlook', 'woocommerce-booking' ),
									'logout_from_outlook' => __( 'Logout from Outlook', 'woocommerce-booking' ),
								),
							)
						);

						wp_register_script(
							'bkap-view-integrations',
							BKAP_Files::rewrite_asset_url( '/assets/js/admin/views/integrations/index.js', BKAP_FILE ),
							array(),
							BKAP_VERSION,
							true
						);

						wp_enqueue_script( 'bkap-view-zoom' );
						wp_enqueue_script( 'bkap-view-zapier' );
						wp_enqueue_script( 'bkap-view-twilio' );
						wp_enqueue_script( 'bkap-view-fluent-crm' );
						wp_enqueue_script( 'bkap-view-google-calendar' );
						wp_enqueue_script( 'bkap-view-outlook-calendar' );
						wp_enqueue_script( 'bkap-view-integrations' );
						break;

					case 'appearance':
						wp_register_script(
							'bkap-view-label',
							BKAP_Files::rewrite_asset_url( '/assets/js/admin/views/appearance/label.js', BKAP_FILE ),
							array(),
							BKAP_VERSION,
							true
						);

						wp_localize_script(
							'bkap-view-label',
							'bkap_view_label_param',
							array(
								'data' => BKAP_Admin_API_Label::fetch_label_data( true ),
							)
						);

						wp_register_script(
							'bkap-view-datepicker',
							BKAP_Files::rewrite_asset_url( '/assets/js/admin/views/datepicker.js', BKAP_FILE ),
							array(),
							BKAP_VERSION,
							true
						);

						wp_register_script(
							'bkap-view-calendar',
							BKAP_Files::rewrite_asset_url( '/assets/js/admin/views/appearance/calendar.js', BKAP_FILE ),
							array(),
							BKAP_VERSION,
							true
						);

						wp_localize_script(
							'bkap-view-calendar',
							'bkap_view_calendar_param',
							array(
								'data'                 => BKAP_Admin_API_Calendar::fetch_calendar_data( true ),
								'languages'            => $bkap_languages,
								'bkap_date_formats'    => $bkap_dates_as_formats,
								'bkap_time_formats'    => $bkap_time_formats,
								'bkap_days'            => $bkap_days,
								'bkap_calendar_themes' => $bkap_calendar_themes,
							)
						);

						wp_register_script(
							'bkap-view-appearance',
							BKAP_Files::rewrite_asset_url( '/assets/js/admin/views/appearance/index.js', BKAP_FILE ),
							array(),
							BKAP_VERSION,
							true
						);

						wp_enqueue_script( 'bkap-view-label' );
						wp_enqueue_script( 'bkap-view-datepicker' );
						wp_enqueue_script( 'bkap-view-calendar' );
						wp_enqueue_script( 'bkap-view-appearance' );
						break;

					case 'settings':
						wp_register_script(
							'bkap-view-global-settings',
							BKAP_Files::rewrite_asset_url( '/assets/js/admin/views/settings/global-settings.js', BKAP_FILE ),
							array(),
							BKAP_VERSION,
							true
						);

						$booking_global_holidays = bkap_global_setting()->booking_global_holidays;
						$booking_calendar_day    = bkap_global_setting()->booking_calendar_day;
						$datepicker_options      = array(
							'dateFormat'    => 'd-m-yy',
							'minDate'       => '0',
							'firstDay'      => (int) ( isset( $booking_calendar_day ) && '' !== $booking_calendar_day ? $booking_calendar_day : get_option( 'start_of_week' ) ),
							'bkap_type'     => 'multiple',
							'altField'      => '#booking_global_holidays',
							'bkap_language' => 'en-GB',
						);

						if ( '' !== $booking_global_holidays ) {
							$datepicker_options['addDates'] = json_encode( explode( ',', $booking_global_holidays ) );
						}

						wp_localize_script(
							'bkap-view-global-settings',
							'bkap_view_global_settings_param',
							array(
								'data'                   => BKAP_Admin_API_Global_Settings::fetch_global_settings_data( true ),
								'timeslot_display_modes' => bkap_common::get_booking_global_value( 'bkap_timeslot_display_modes' ),
								'datepicker_options'     => $datepicker_options,
							)
						);

						wp_register_script(
							'bkap-view-bulk-booking-settings',
							BKAP_Files::rewrite_asset_url( '/assets/js/admin/views/settings/bulk-booking-settings.js', BKAP_FILE ),
							array(),
							BKAP_VERSION,
							true
						);

						wp_localize_script(
							'bkap-view-bulk-booking-settings',
							'bkap_view_bulk_booking_settings_param',
							array(
								'data' => BKAP_Admin_API_Bulk_Booking_Settings::fetch_bulk_booking_settings_data( true ),
							)
						);

						wp_register_script(
							'bkap-view-product-availability-settings',
							BKAP_Files::rewrite_asset_url( '/assets/js/admin/views/settings/product-availability-settings.js', BKAP_FILE ),
							array(),
							BKAP_VERSION,
							true
						);

						wp_localize_script(
							'bkap-view-product-availability-settings',
							'bkap_view_product_availability_settings_param',
							array(
								'data' => BKAP_Admin_API_Product_Availability_Settings::fetch_product_availability_settings_data( true ),
							)
						);

						wp_register_script(
							'bkap-view-settings',
							BKAP_Files::rewrite_asset_url( '/assets/js/admin/views/settings/index.js', BKAP_FILE ),
							array(),
							BKAP_VERSION,
							true
						);

						wp_register_script(
							'bkap-view-vendor-options',
							BKAP_Files::rewrite_asset_url( '/assets/js/admin/views/settings/vendor-options.js', BKAP_FILE ),
							array(),
							BKAP_VERSION,
							true
						);

						$vendor = false;
						if ( class_exists( 'WeDevs_Dokan' ) || ( function_exists( 'is_wcvendors_active' ) && is_wcvendors_active() ) || function_exists( 'is_wcfm_page' ) ) {
							$vendor = true;
						}

						wp_localize_script(
							'bkap-view-vendor-options',
							'bkap_view_vendor_options_param',
							array(
								'data'               => BKAP_Admin_API_Global_Settings::fetch_vendor_options_data( true ),
								'bkap_booking_types' => bkap_get_booking_types(),
								'plugin_not_activated_message' => __( 'These options will work only with Dokan, WC Vendors, and WCFM Marketplace vendor plugins. Currently, none of these plugins are active on the store.', 'woocommerce-booking' ),
								'bkap_vendor_plugin_notice' => $vendor,
							)
						);

						wp_enqueue_script( 'bkap-view-global-settings' );
						wp_enqueue_script( 'bkap-view-bulk-booking-settings' );
						wp_enqueue_script( 'bkap-view-product-availability-settings' );
						wp_enqueue_script( 'bkap-view-vendor-options' );
						wp_enqueue_script( 'bkap-view-settings' );
						break;

					case 'resources':
						wp_register_script(
							'bkap-view-resources',
							BKAP_Files::rewrite_asset_url( '/assets/js/admin/views/resources/resources.js', BKAP_FILE ),
							array(),
							BKAP_VERSION,
							true
						);

						wp_localize_script(
							'bkap-view-resources',
							'bkap_view_resources_param',
							array(
								'data'  => BKAP_Admin_API_Resources::fetch_resources_data( true ),
								'label' => array(
									'saving_resource_loader' => __( 'Saving Resource Data, please wait ...', 'woocommerce-booking' ),
									'updating_resource_loader' => __( 'Updating Resource Data, please wait ...', 'woocommerce-booking' ),
									'add_resource'         => __( 'Add Resource', 'woocommerce-booking' ),
									'edit_resource'        => __( 'Edit Resource', 'woocommerce-booking' ),
									'delete_resource'      => __( 'Deleting selected resource', 'woocommerce-booking' ),
									'delete_resources'     => __( 'Deleting selected resources', 'woocommerce-booking' ),
									'restore_resource'     => __( 'Restoring selected resource', 'woocommerce-booking' ),
									'restore_resources'    => __( 'Restoring selected resources', 'woocommerce-booking' ),
									'trash_resource'       => __( 'Trashing selected resource, please wait!', 'woocommerce-booking' ),
									'trash_resources'      => __( 'Trashing selected resources, please wait!', 'woocommerce-booking' ),
									'resource_caps'        => __( 'Resource', 'woocommerce-booking' ),
									'are_you_sure_you_want_to' => __( 'Are you sure you want to', 'woocommerce-booking' ),
									'save_resource'        => __( 'Save Resource', 'woocommerce-booking' ),
									'update_resource'      => __( 'Update Resource', 'woocommerce-booking' ),
									'no_resources_selected' => __( 'No Resources have been selected.', 'woocommerce-booking' ),
									'trash_resources_text' => __( 'Trash Resources', 'woocommerce-booking' ),
									'restore_resources_text' => __( 'Restore Resources', 'woocommerce-booking' ),
									'delete_resources_text' => __( 'Delete Resources', 'woocommerce-booking' ),
									'are_you_sure_you_want_to_trash_resources' => __( 'Are you sure you want to TRASH the selected Resources?', 'woocommerce-booking' ),
									'are_you_sure_you_want_to_restore_resources' => __( 'Are you sure you want to RESTORE the selected Resources?', 'woocommerce-booking' ),
									'are_you_sure_you_want_to_delete_resources' => __( 'Are you sure you want to PERMANENTLY DELETE the selected Resources?', 'woocommerce-booking' ),
								),
								'resource_id' => isset( $_GET['resource_id'] ) ? sanitize_text_field( $_GET['resource_id'] ) : '', // phpcs:ignore
							)
						);

						wp_register_script(
							'bkap-view-resources-main',
							BKAP_Files::rewrite_asset_url( '/assets/js/admin/views/resources/index.js', BKAP_FILE ),
							array(),
							BKAP_VERSION,
							true
						);

						wp_enqueue_script( 'bkap-view-resources' );
						wp_enqueue_script( 'bkap-view-resources-main' );
						break;

					case 'reminders':
						wp_register_script(
							'bkap-view-view-reminders',
							BKAP_Files::rewrite_asset_url( '/assets/js/admin/views/reminders/view-reminders.js', BKAP_FILE ),
							array(),
							BKAP_VERSION,
							true
						);

						wp_localize_script(
							'bkap-view-view-reminders',
							'bkap_view_view_reminders_param',
							array(
								'data'  => BKAP_Admin_API_Reminders::fetch_reminders_data( true ),
								'label' => array(
									'saving_reminder_loader' => __( 'Saving Reminder Data, please wait ...', 'woocommerce-booking' ),
									'updating_reminder_loader' => __( 'Updating Reminder Data, please wait ...', 'woocommerce-booking' ),
									'no_reminders_selected' => __( 'No Reminders have been selected.', 'woocommerce-booking' ),
									'trash_reminders_text' => __( 'Trash Reminders', 'woocommerce-booking' ),
									'are_you_sure_you_want_to_trash_reminders' => __( 'Are you sure you want to TRASH the selected Reminders?', 'woocommerce-booking' ),
									'reminder_caps'        => __( 'Reminder', 'woocommerce-booking' ),
									'are_you_sure_you_want_to' => __( 'Are you sure you want to', 'woocommerce-booking' ),
									'trash_reminder'       => __( 'Trashing selected reminder, please wait!', 'woocommerce-booking' ),
									'delete_reminder'      => __( 'Deleting selected reminder, please wait!', 'woocommerce-booking' ),
									'delete_reminders'     => __( 'Deleting selected reminders, please wait!', 'woocommerce-booking' ),
									'restore_reminder'     => __( 'Restoring selected reminder, please wait!', 'woocommerce-booking' ),
									'restore_reminders'    => __( 'Restoring selected reminders, please wait!', 'woocommerce-booking' ),
									'trash_reminders'      => __( 'Trashing selected reminders, please wait!', 'woocommerce-booking' ),
									'saving_reminder_loader' => __( 'Saving Reminder Data, please wait ...', 'woocommerce-booking' ),
									'updating_reminder_loader' => __( 'Updating Reminder Data, please wait ...', 'woocommerce-booking' ),
									'add_reminder'         => __( 'Add Reminder', 'woocommerce-booking' ),
									'edit_reminder'        => __( 'Edit Reminder:', 'woocommerce-booking' ),
									'save_reminder'        => __( 'Save Reminder', 'woocommerce-booking' ),
									'update_reminder'      => __( 'Update Reminder', 'woocommerce-booking' ),
									'show_sending_test_reminder_email_loader' => __( 'Sending Test Reminder Email, please wait ...', 'woocommerce-booking' ),
									'please_enter_an_email_address' => __( 'Please enter an email address', 'woocommerce-booking' ),
									'you_provided_invalid_email_address' => __( 'Oops! The email address you provided looks invalid', 'woocommerce-booking' ),
									'trash_reminders_text' => __( 'Trash Reminders', 'woocommerce-booking' ),
									'restore_reminders_text' => __( 'Restore Reminders', 'woocommerce-booking' ),
									'delete_reminders_text' => __( 'Delete Reminders', 'woocommerce-booking' ),
									'are_you_sure_you_want_to_restore_reminders' => __( 'Are you sure you want to RESTORE the selected Reminders?', 'woocommerce-booking' ),
									'are_you_sure_you_want_to_delete_reminders' => __( 'Are you sure you want to PERMANENTLY DELETE the selected Reminders?', 'woocommerce-booking' ),

								),
							)
						);

						wp_register_script(
							'bkap-view-manual-reminder',
							BKAP_Files::rewrite_asset_url( '/assets/js/admin/views/reminders/manual-reminder.js', BKAP_FILE ),
							array(),
							BKAP_VERSION,
							true
						);

						wp_localize_script(
							'bkap-view-manual-reminder',
							'bkap_view_manual_reminder_param',
							array(
								'data'  => BKAP_Admin_API_Reminders::fetch_manual_reminder_data( true ),
								'label' => array(
									'sending_manual_reminder_loader' => __( 'Sending Reminder, please wait ...', 'woocommerce-booking' ),
									'saving_manual_reminder_draft' => __( 'Saving Reminder as Draft, please wait ...', 'woocommerce-booking' ),
								),
							)
						);

						wp_register_script(
							'bkap-view-reminders',
							BKAP_Files::rewrite_asset_url( '/assets/js/admin/views/reminders/index.js', BKAP_FILE ),
							array(),
							BKAP_VERSION,
							true
						);

						wp_enqueue_script( 'bkap-view-view-reminders' );
						wp_enqueue_script( 'bkap-view-manual-reminder' );
						wp_enqueue_script( 'bkap-view-reminders' );
						break;

					default:
						wp_register_script(
							'bkap-view-faq',
							BKAP_Files::rewrite_asset_url( '/assets/js/admin/views/home/faq.js', BKAP_FILE ),
							array(),
							BKAP_VERSION,
							true
						);

						wp_register_script(
							'bkap-view-welcome',
							BKAP_Files::rewrite_asset_url( '/assets/js/admin/views/home/welcome.js', BKAP_FILE ),
							array(),
							BKAP_VERSION,
							true
						);

						wp_register_script(
							'bkap-view-status',
							BKAP_Files::rewrite_asset_url( '/assets/js/admin/views/home/status.js', BKAP_FILE ),
							array(),
							BKAP_VERSION,
							true
						);

						wp_localize_script(
							'bkap-view-status',
							'bkap_view_status_param',
							array(
								'wordpress_environment' => BKAP_Admin_System_Status::bkap_get_generic(),
								'plugin_settings'       => BKAP_Admin_System_Status::bkap_get_plugin_data( array( 'book', 'bkap_' ) ),
								'export_data'           => BKAP_Admin_System_Status::bkap_export_data(),
								'label'                 => array(
									'data_copy_success_message' => __( 'Status Data has been copied!', 'woocommerce-booking' ),
								),
							)
						);

						wp_register_script(
							'bkap-view-home',
							BKAP_Files::rewrite_asset_url( '/assets/js/admin/views/home/index.js', BKAP_FILE ),
							array(),
							BKAP_VERSION,
							true
						);

						do_action( 'bkap_enqueue_js_home' );

						wp_enqueue_script( 'bkap-view-faq' );
						wp_enqueue_script( 'bkap-view-welcome' );
						wp_enqueue_script( 'bkap-view-status' );
						wp_enqueue_script( 'bkap-view-home' );
						break;
				}

				wp_register_script(
					'bkap-view-main',
					BKAP_Files::rewrite_asset_url( '/assets/js/admin/views/main.js', BKAP_FILE ),
					array(),
					BKAP_VERSION,
					true
				);

				wp_enqueue_script( 'bkap-view-main' );
			}

			do_action( 'bkap_enqueue_js' );
		}
	}
}
