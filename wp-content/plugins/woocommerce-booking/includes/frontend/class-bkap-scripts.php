<?php
/**
 * Bookings and Appointment Plugin for WooCommerce
 *
 * Include scripts to be included on pages associated with the plugin.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Global-Function
 * @category    Classes
 */

if ( ! class_exists( 'BKAP_Scripts' ) ) {

	/**
	 * Load Scripts needed for Plugin
	 *
	 * @since 4.6.0
	 */
	class BKAP_Scripts {

		/**
		 * Default constructor
		 *
		 * @since 4.10.0
		 */
		public function __construct() {
			add_action( 'admin_head', array( $this, 'bkap_vertical_my_enqueue_scripts_css' ) );
			add_action( 'admin_footer', array( $this, 'bkap_print_js' ) );
			add_action( 'woocommerce_before_single_product', array( &$this, 'bkap_front_side_scripts_js' ) );
			add_action( 'woocommerce_before_single_product', array( &$this, 'bkap_front_side_scripts_css' ) );
		}

		/**
		 * This function will call common function to
		 * includes css files required for frontend.
		 *
		 * @globals WP_Post $post Post Object
		 * @since 1.0.0
		 */
		public static function bkap_front_side_scripts_css() {
			global $post;

			if ( is_product() || is_page() ) {
				self::inlcude_frontend_scripts_css( $post->ID );
			}
		}

		/**
		 * Include Front End scripts (CSS) for Datepicker dependencies
		 *
		 * @param string|int $product_id Product ID.
		 * @since 4.2.0
		 */
		public static function inlcude_frontend_scripts_css( $product_id ) {

			$duplicate_of     = bkap_common::bkap_get_product_id( $product_id );
			$booking_settings = get_post_meta( $duplicate_of, 'woocommerce_booking_settings', true );

			if ( isset( $booking_settings['booking_enable_date'] ) && $booking_settings['booking_enable_date'] == 'on' ) {

				$global_settings    = bkap_global_setting();
				$calendar_theme_sel = $global_settings->booking_themes;

				wp_register_style(
					'bkap-jquery-ui',
					BKAP_Files::rewrite_asset_url( "/assets/css/themes/$calendar_theme_sel/jquery-ui.css", BKAP_FILE, true, false ),
					'',
					BKAP_VERSION,
					false
				);

				wp_deregister_style( 'jquery-ui' );

				wp_enqueue_style( 'bkap-jquery-ui' );
				wp_enqueue_style(
					'bkap-booking',
					BKAP_Files::rewrite_asset_url( '/assets/css/booking.css', BKAP_FILE ),
					'',
					BKAP_VERSION,
					false
				);
			}
		}

		/**
		 * This function will call common function to
		 * includes js files required for frontend.
		 *
		 * @globals WP_Post $post Post Object
		 * @since 1.0.0
		 */
		public static function bkap_front_side_scripts_js() {
			global $post;

			if ( is_product() || is_page() ) {
				self::include_frontend_scripts_js( $post->ID );
			}
		}

		/**
		 * Include Front End scripts (JS) for Datepicker dependencies
		 *
		 * @param string|int $product_id Product ID.
		 * @since 4.2.0
		 */
		public static function include_frontend_scripts_js( $product_id ) {

			$duplicate_of     = bkap_common::bkap_get_product_id( $product_id );
			$booking_settings = get_post_meta( $duplicate_of, 'woocommerce_booking_settings', true );

			if ( isset( $booking_settings['booking_enable_date'] ) && $booking_settings['booking_enable_date'] === 'on' ) {

				$global_settings = bkap_global_setting();
				$curr_lang       = $global_settings->booking_language;

				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'jquery-ui-datepicker' );

				$curr_lang = bkap_icl_lang_code( $curr_lang );

				wp_enqueue_script(
					"$curr_lang",
					BKAP_Files::rewrite_asset_url( "/assets/js/i18n/jquery.ui.datepicker-$curr_lang.js", BKAP_FILE, true ),
					'',
					BKAP_VERSION,
					false
				);

				wp_enqueue_script(
					'accounting',
					WC()->plugin_url() . '/assets/js/accounting/accounting.min.js',
					array( 'jquery' ),
					BKAP_VERSION,
					false
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
			}
		}

		/**
		 * View Bookings Script
		 *
		 * @since 5.10.0
		 */
		public static function bkap_load_view_booking_scripts() {

			$bkap_version       = BKAP_VERSION;
			$g_setting          = bkap_global_setting();
			$calendar_theme_sel = $g_setting->booking_themes;

			wp_enqueue_style(
				'jquery-ui',
				BKAP_Files::rewrite_asset_url( "/assets/css/themes/$calendar_theme_sel/jquery-ui.css", BKAP_FILE, true, false ),
				'',
				$bkap_version,
				false
			);

			wp_enqueue_script( 'jquery-ui' );
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script(
				'bkap-view-booking',
				BKAP_Files::rewrite_asset_url( '/assets/js/bkap-view-booking.js', BKAP_FILE ),
				array( 'jquery', 'selectWoo' ),
				BKAP_VERSION,
				false
			);

			$post_type = isset( $_GET['post_type'] ) ? sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			if ( 'bkap_booking' === $post_type ) {
				wp_enqueue_style(
					'woocommerce_admin_styles',
					WC()->plugin_url() . '/assets/css/admin.css',
					array(),
					BKAP_VERSION,
					true
				);

				wp_enqueue_script(
					'select2',
					WC()->plugin_url() . '/assets/js/select2/select2.full.js',
					array( 'jquery' ),
					'4.0.3',
					false
				);

				wp_enqueue_script(
					'selectWoo',
					WC()->plugin_url() . '/assets/js/selectWoo/selectWoo.full.js',
					array( 'jquery' ),
					'1.0.6',
					false
				);

				$custom_inline_style = '.post-type-bkap_booking .tablenav .select2-selection--single{height:32px;}
				.post-type-bkap_booking .tablenav .select2-selection--single .select2-selection__rendered{line-height:29px;} .post-type-bkap_booking .tablenav .select2-selection--single .select2-selection__arrow{height:30px;} .post-type-bkap_booking .tablenav .select2-container{float:left;width:240px!important;font-size:14px;vertical-align:middle;margin:1px 6px 4px 1px;}';

				wp_add_inline_style( 'woocommerce_admin_styles', $custom_inline_style );
			}

			self::bkap_localize_view_booking();
		}

		/**
		 * Localize parameters for View Bookings Page scripts to be enqueued
		 *
		 * @since 4.6.0
		 */
		public static function bkap_localize_view_booking() {

			$bkap_view_booking           = array( 'ajax_url' => AJAX_URL );
			$bkap_view_booking['labels'] = array(
				'print_label'    => __( 'Print', 'woocommerce-booking' ),
				'csv_label'      => __( 'CSV', 'woocommerce-booking' ),
				'calendar_label' => __( 'Calendar View', 'woocommerce-booking' ),
			);

			$user_id = get_current_user_id();
			$user    = new WP_User( $user_id );

			if ( isset( $user->roles[0] ) && 'tour_operator' === $user->roles[0] ) {
				$display_button_setting = esc_attr( get_the_author_meta( 'tours_add_to_calendar_view_booking', $user_id ) );
			} else {
				$display_button_setting = get_option( 'bkap_admin_add_to_calendar_view_booking' );
			}

			$gcal = new BKAP_Google_Calendar();

			$post_type = isset( $_GET['post_type'] ) ? sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

			if ( 'bkap_booking' === $post_type && in_array( $gcal->get_api_mode( $user_id ), array( 'directly', 'oauth' ), true ) && 'on' === $display_button_setting ) {

				$total_bookings_to_export                              = bkap_common::bkap_get_total_bookings_to_export( $user_id );
				$bkap_view_booking['labels']['add_to_google_calendar'] = __( 'Add to Google Calendar', 'woocommerce-booking' );
				$bkap_view_booking['total_bookings_to_export']         = count( $total_bookings_to_export );
				$bkap_view_booking['user_id']                          = $user_id;
				/* Translators: %s Total Bookings */
				$bkap_view_booking['total_bookings_to_export_msg'] = sprintf( __( 'Total bookings to export %s...', 'woocommerce-booking' ), $bkap_view_booking['total_bookings_to_export'] );
				/* Translators: %s Total Bookings */
				$bkap_view_booking['total_bookings_to_exported_msg'] = sprintf( __( '%s bookings have been exported to your Google Calendar. Please refresh your Google Calendar.', 'woocommerce-booking' ), $bkap_view_booking['total_bookings_to_export'] );
			}
			$bkap_view_booking['no_bookings_to_export'] = __( 'No pending bookings left to be exported.', 'woocommerce-booking' );

			if ( current_user_can( 'operator_bookings' ) ) {
				$bkap_view_booking['url'] = array(
					'print_url'    => esc_url( add_query_arg( array( 'download' => 'data.print' ) ) ),
					'csv_url'      => esc_url( add_query_arg( array( 'download' => 'data.csv' ) ) ),
					'calendar_url' => esc_url( get_admin_url( null, 'edit.php?post_type=bkap_booking&page=woocommerce_history_page&booking_view=booking_calender' ) ),
				);
			} else {
				$bkap_view_booking['url'] = array(
					'print_url'    => esc_url( add_query_arg( array( 'download' => 'data.print' ) ) ),
					'csv_url'      => esc_url( add_query_arg( array( 'download' => 'data.csv' ) ) ),
					'calendar_url' => esc_url( get_admin_url( null, 'edit.php?post_type=bkap_booking&page=woocommerce_history_page&booking_view=booking_calender' ) ),
				);
			}

			if ( 'bkap_booking' === $post_type ) {
				$bkap_view_booking['bkap_customer_filter_params'] = array(
					'i18n_no_matches'           => _x( 'No matches found', 'bkap customer filter', 'woocommerce-booking' ),
					'i18n_ajax_error'           => _x( 'Could not fetch Customer Records', 'bkap customer filter', 'woocommerce-booking' ),
					'i18n_input_too_short_1'    => _x( 'Please enter 1 or more characters', 'bkap customer filter', 'woocommerce-booking' ),
					'i18n_input_too_short_n'    => _x( 'Please enter %qty% or more characters', 'bkap customer filter', 'woocommerce-booking' ),
					'i18n_input_too_long_1'     => _x( 'Please delete 1 character', 'bkap customer filter', 'woocommerce-booking' ),
					'i18n_input_too_long_n'     => _x( 'Please delete %qty% characters', 'bkap customer filter', 'woocommerce-booking' ),
					'i18n_selection_too_long_1' => _x( 'You can only select 1 item', 'bkap customer filter', 'woocommerce-booking' ),
					'i18n_selection_too_long_n' => _x( 'You can only select %qty% items', 'bkap customer filter', 'woocommerce-booking' ),
					'i18n_load_more'            => _x( 'Loading more results&hellip;', 'bkap customer filter', 'woocommerce-booking' ),
					'i18n_searching'            => _x( 'Searching&hellip;', 'bkap customer filter', 'woocommerce-booking' ),
					'ajax_url'                  => admin_url( 'admin-ajax.php' ),
					'search_customers_nonce'    => wp_create_nonce( 'search-customers' ),
				);
			}
			wp_localize_script( 'bkap-view-booking', 'bkap_view_booking', $bkap_view_booking );
		}

		/**
		 * Adds JS code for Vertical tabs in the Booking meta box.
		 *
		 * @hook admin_footer
		 * @since 2.2
		 */

		public static function bkap_print_js() {
			if ( get_post_type() == 'product' ) {
				?>
<script type="text/javascript">
jQuery(document).ready(function($) {
	$('.tstab-content').wrapInner('<div class="tstab-content-inner"></div>');
	$(document).on('click', '.tstab-tab', function() {
		data_link = $(this).data("link");
		cur_data_link = $('.tstab-tab.tstab-active').data("link");
		if (cur_data_link !== data_link) {
			$('.tstab-content').removeClass('tstab-active').hide();
			$("#" + data_link).addClass('tstab-active').css('position', 'relative').fadeIn('slow');
			$('.tstab-tab').removeClass('tstab-active');
			$(this).addClass('tstab-active');
		}
	});
});
</script>
				<?php
			}
		}

		/**
		 * Enqueue BKAP Tabs CSS for product pages
		 *
		 * @since 4.0.0
		 *
		 * @todo replace get_option with the function declared above to fetch version number
		 */
		public function bkap_vertical_my_enqueue_scripts_css() {

			if ( get_post_type() === 'product' ) {
				$plugin_version_number = get_option( 'woocommerce_booking_db_version' );

				self::bkap_load_bkap_tab_css( $plugin_version_number );
			}
		}

		/**
		 * Enquque Common JS Scripts to be included in Admin Side
		 *
		 * @param string $plugin_version_number Plugin Version Number
		 *
		 * @since 4.6.0
		 */
		public static function bkap_common_admin_scripts_js( $plugin_version_number ) {

			$load_files = true;
			$action     = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			if ( 'bulk_booking_settings' === $action ) {
				$load_files = false;
			}

			if ( $load_files ) {
				wp_register_script(
					'multiDatepicker',
					BKAP_Files::rewrite_asset_url( '/assets/js/jquery-ui.multidatespicker.js', BKAP_FILE ),
					'',
					$plugin_version_number,
					true
				);
				wp_enqueue_script( 'multiDatepicker' );
			}

			wp_register_script(
				'datepick',
				BKAP_Files::rewrite_asset_url( '/assets/js/jquery.datepick.js', BKAP_FILE ),
				'',
				$plugin_version_number,
				true
			);
			wp_enqueue_script( 'datepick' );
		}

		/**
		 * Enquque Product JS Scripts.
		 *
		 * @param string $plugin_version_number Plugin Version Number.
		 * @param string $ajax_url Ajax URL.
		 * @param string $called_from Called From.
		 *
		 * @globals WP_Post $post
		 *
		 * @since 4.6.0
		 */
		public static function bkap_load_product_scripts_js( $plugin_version_number, $ajax_url, $called_from = '' ) {

			global $post;

			if ( $called_from == '' ) {
				$post_id = $post->ID;
			} else {
				$post_id = 0;
			}

			$user_id = get_current_user_id();

			wp_register_script(
				'booking-meta-box',
				BKAP_Files::rewrite_asset_url( '/assets/js/booking-meta-box.js', BKAP_FILE ),
				'',
				$plugin_version_number,
				true
			);

			wp_localize_script(
				'booking-meta-box',
				'bkap_settings_params',
				array(
					'ajax_url'                     => $ajax_url,
					'post_id'                      => $post_id,
					'user_id'                      => $user_id,
					'specific_label'               => __( 'Specific Dates', 'woocommerce-booking' ),
					'general_update_msg'           => __( 'General Booking settings have been saved.', 'woocommerce-booking' ),
					'availability_update_msg'      => __( 'Booking Availability settings have been saved.', 'woocommerce-booking' ),
					'error_input_field_msg'        => __( 'Make sure you have filled Weekday and From fields in Set Weekdays/Dates And It\'s Timeslots section of Availability tab to save settings.', 'woocommerce-booking' ),
					'gcal_update_msg'              => __( 'Settings have been saved.', 'woocommerce-booking' ),
					'rental_update_msg'            => __( 'Rental settings have been saved.', 'woocommerce-booking' ),
					'only_day_text'                => __( 'Use this for full day bookings or bookings spanning multiple nights.', 'woocommerce-booking' ),
					'date_time_text'               => __( 'Use this if you wish to take bookings for time slots. For e.g. coaching classes, appointments, ground on rent etc.', 'woocommerce-booking' ),
					'fixed_time_text'              => __( 'Use this if you have fixed time slots for bookings. For e.g. coaching classes, appointments etc.', 'woocommerce-booking' ),
					'duration_time_text'           => __( 'Use this if you want your customer to select required duration for booking. For e.g. sports ground booking, appointments etc.', 'woocommerce-booking' ),
					'multidates_text'              => __( 'Use this for multiple dates bookings.', 'woocommerce-booking' ),
					'multidates_fixedtime_text'    => __( 'Use this for multiple dates and fixed time slots bookings.', 'woocommerce-booking' ),
					'single_day_text'              => __( 'Use this to take bookings like single day tours, event, appointments etc.', 'woocommerce-booking' ),
					'multiple_nights_text'         => __( 'Use this for hotel bookings, rentals, etc. Checkout date is not included in the booking period.', 'woocommerce-booking' ),
					'multiple_nights_price_text'   => __( 'Please enter the per night price in the Regular or Sale Price box in the Product meta box as needed. In case if you wish to charge special prices for a weekday, please enter them above.', 'woocommerce-booking' ),
					'confirm_delete_all_timeslots' => __( 'Are you sure you want to delete all the timeslots?', 'woocommerce-booking' ),
					'confirm_delete_timeslots'     => __( 'Are you sure you want to delete the timeslot?', 'woocommerce-booking' ),
					'success_delete_all_timeslots' => __( 'All the timeslots have been deleted successfully.', 'woocommerce-booking' ),
					'no_timeslots_available'       => __( 'There are no timeslots added for the product.', 'woocommerce-booking' ),
					'resource_update_msg'          => __( 'Resource settings have been saved.', 'woocommerce-booking' ),
					'person_update_msg'            => __( 'Person settings have been saved.', 'woocommerce-booking' ),
					'validation_messages'          => array(
						'range_type_validation'       => __( 'The FROM value must be less than the TO value.', 'woocommerce-booking' ),
						'duration_range_validation'   => __( 'The START range must be less than the END range.', 'woocommerce-booking' ),
						'weekday_timeslot_validation' => __( 'The FROM Weekday timeslot must be less than the TO timeslot.', 'woocommerce-booking' ),
						'validation_alert_message'    => __( 'One or more fields have incorrect START/END or FROM/TO values.', 'woocommerce-booking' ),
					),
					'uploading'                    => __( 'Uploading...', 'woocommerce-booking' ),
					'disconnecting'                => __( 'Disconnecting...', 'woocommerce-booking' ),
					'file_uploaded'                => __( 'File uploaded successfully!', 'woocommerce-booking' ),
					'upload_error'                 => __( 'There was an error uploading the file.', 'woocommerce-booking' ),
					'disconnected'                 => __( 'Successfully disconnected..!', 'woocommerce-booking' ),
				)
			);

			// Messages for Block Pricing.
			wp_localize_script(
				'booking-meta-box',
				'bkap_block_pricing_params',
				array(
					'save_fixed_blocks'               => __( 'Fixed Blocks have been saved.', 'woocommerce-booking' ),
					'delete_fixed_block'              => __( 'Fixed Block have been deleted.', 'woocommerce-booking' ),
					'delete_all_fixed_blocks'         => __( 'All Fixed Blocks have been deleted.', 'woocommerce-booking' ),
					'confirm_delete_fixed_block'      => __( 'Are you sure you want to delete this fixed block?', 'woocommerce-booking' ),
					'confirm_delete_all_fixed_blocks' => __( 'Are you sure you want to delete all the blocks?', 'woocommerce-booking' ),

					'save_price_ranges'               => __( 'Price ranges have been saved.', 'woocommerce-booking' ),
					'delete_price_range'              => __( 'Price Range have been deleted.', 'woocommerce-booking' ),
					'delete_all_price_ranges'         => __( 'All Price Ranges have been deleted.', 'woocommerce-booking' ),
					'confirm_delete_price_range'      => __( 'Are you sure you want to delete this price range?', 'woocommerce-booking' ),
					'confirm_delete_all_price_ranges' => __( 'Are you sure you want to delete all the ranges?', 'woocommerce-booking' ),
				)
			);

			wp_enqueue_script( 'booking-meta-box' );

			wp_enqueue_script( 'jquery' );
			wp_deregister_script( 'jqueryui' );

			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'jquery-ui-datepicker' );

			wp_register_script(
				'booking-time-slots-meta-box',
				BKAP_Files::rewrite_asset_url( '/assets/js/booking-time-slots-meta-box.js', BKAP_FILE ),
				'',
				$plugin_version_number,
				true
			);

			// Messages for loading time slots via ajax.
			wp_localize_script(
				'booking-time-slots-meta-box',
				'bkap_time_slots_params',
				array(
					'ajax_url'                 => $ajax_url,
					'bkap_product_id'          => $post_id,
					'bkap_time_slots_per_page' => absint( apply_filters( 'bkap_time_slots_per_page', 15 ) ),
				)
			);

			wp_enqueue_script( 'booking-time-slots-meta-box' );

			do_action( 'after_bkap_load_product_scripts_js', $post_id );
		}

		/**
		 * Enquque Dokan JS Scripts
		 *
		 * @param string $plugin_version_number Plugin Version Number
		 * @param string $ajax_url Ajax URL
		 *
		 * @since 4.6.0
		 */

		public static function bkap_load_dokan_product_scripts_js( $plugin_version_number, $ajax_url ) {

			wp_register_script(
				'bkap-dokan-product',
				BKAP_Files::rewrite_asset_url( '/assets/js/vendors/dokan/bkap_dokan_product.js', BKAP_FILE ),
				'',
				$plugin_version_number,
				true
			);

			wp_enqueue_script( 'bkap-dokan-product' );
		}

		/**
		 * Enqueue Dokan JS & scripts View Bookings page.
		 *
		 * @param string $plugin_version_number - Plugin Version Number.
		 * @param string $ajax_url - AJAX Url.
		 *
		 * @since 5.0.0
		 */
		public static function bkap_load_dokan_calendar_view_scripts_js( $plugin_version_number, $ajax_url ) {

			$global_settings = bkap_global_setting();

			wp_register_script(
				'bkap-dokan-calendar-view',
				BKAP_Files::rewrite_asset_url( '/assets/js/vendors/dokan/bkap_view_booking.js', BKAP_FILE ),
				'',
				$plugin_version_number,
				true
			);

			wp_localize_script(
				'bkap-dokan-calendar-view',
				'bkap_dokan_localize_params',
				array(
					'global_settings' => wp_json_encode( $global_settings ),
					'ajax_url'        => $ajax_url,
				)
			);
			wp_enqueue_script( 'bkap-dokan-calendar-view' );

			$curr_lang = $global_settings->booking_language;

			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'jquery-ui-datepicker' );

			$curr_lang = bkap_icl_lang_code( $curr_lang );

			wp_enqueue_script(
				"$curr_lang",
				BKAP_Files::rewrite_asset_url( "/assets/js/i18n/jquery.ui.datepicker-$curr_lang.js", BKAP_FILE, true ),
				'',
				BKAP_VERSION,
				false
			);
		}

		/**
		 * Enqueue Dokan CSS & scripts View Bookings page.
		 *
		 * @since 5.0.0
		 */
		public static function bkap_load_dokan_calendar_view_scripts_css() {

			wp_enqueue_style(
				'bkap-dokan-vendor-availability',
				BKAP_Files::rewrite_asset_url( '/assets/css/vendors/dokan/bkap-dokan-view-bookings.css', BKAP_FILE ),
				'',
				BKAP_VERSION,
				false
			);
		}

		/**
		 * Enquque JS & CSS for Resources
		 *
		 * @param string $plugin_version_number Plugin Version Number
		 * @param string $ajax_url Ajax URL
		 *
		 * @globals WP_Post $post
		 *
		 * @since 4.6.0
		 */

		public static function bkap_load_resource_scripts_js( $plugin_version_number, $ajax_url ) {

			global $post;

			if ( is_object( $post ) ) {
				$post_id = $post->ID;
			} else {
				$post_id = 0;
			}

			$bkap_calendar_img = plugins_url() . '/woocommerce-booking/assets/images/cal.gif';

			wp_register_script(
				'bkap-resource',
				BKAP_Files::rewrite_asset_url( '/assets/js/bkap-resource.js', BKAP_FILE ),
				array(
					'jquery',
					'jquery-ui-sortable',
					'jquery-ui-datepicker',
				),
				$plugin_version_number,
				true
			);

			$args = array(
				'ajax_url'                 => $ajax_url,
				'post_id'                  => $post_id,
				'bkap_calendar'            => $bkap_calendar_img,
				'delete_resource_conf'     => __( 'Are you sure you want to delete this resource?', 'woocommerce-booking' ),
				'delete_resource_conf_all' => __( 'Are you sure you want to delete all resources?', 'woocommerce-booking' ),
				'delete_resource'          => __( 'Resource have been deleted.', 'woocommerce-booking' ),
				'delete_person_conf'       => __( 'Are you sure you want to delete this person?', 'woocommerce-booking' ),
				'delete_person_conf_all'   => __( 'Are you sure you want to delete all persons?', 'woocommerce-booking' ),
				'delete_person'            => __( 'Person have been deleted.', 'woocommerce-booking' ),
			);

			wp_localize_script( 'bkap-resource', 'bkap_resource_params', $args );

			wp_localize_script( 'booking-meta-box', 'bkap_resource_params', $args );

			wp_enqueue_script( 'bkap-resource' );

			wp_enqueue_style(
				'bkap-resource-css',
				BKAP_Files::rewrite_asset_url( '/assets/css/bkap-resource-css.css', BKAP_FILE ),
				'',
				$plugin_version_number,
				false
			);
		}

		/**
		 * Enquque JS for Vendors Calendar View.
		 *
		 * @param string $plugin_version_number Plugin Version Number.
		 * @param string $vendor_id Vendor Id.
		 *
		 * @since 4.6.0
		 */
		public static function bkap_load_calendar_scripts( $plugin_version_number, $vendor_id = '' ) {

			wp_enqueue_script( 'jquery' );
			wp_deregister_script( 'jqueryui' );

			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'jquery-ui-datepicker' );

			wp_register_script(
				'full-js',
				BKAP_Files::rewrite_asset_url( '/assets/js/fullcalendar/main.min.js', BKAP_FILE, true ),
				array(),
				BKAP_VERSION,
				false
			);

			wp_register_script(
				'locales-js',
				BKAP_Files::rewrite_asset_url( '/assets/js/fullcalendar/locales-all.min.js', BKAP_FILE, true ),
				array( 'jquery' ),
				BKAP_VERSION,
				false
			);

			wp_register_script(
				'bkap-images-loaded',
				BKAP_Files::rewrite_asset_url( '/assets/js/imagesloaded.pkg.min.js', BKAP_FILE ),
				array(),
				BKAP_VERSION,
				false
			);
			wp_register_script(
				'bkap-qtip',
				BKAP_Files::rewrite_asset_url( '/assets/js/jquery.qtip.min.js', BKAP_FILE ),
				array( 'jquery', 'bkap-images-loaded' ),
				BKAP_VERSION,
				false
			);

			$global_settings = bkap_global_setting();

			wp_register_script(
				'booking-calender-js',
				BKAP_Files::rewrite_asset_url( '/assets/js/booking-calender.js', BKAP_FILE ),
				array( 'jquery', 'bkap-qtip', 'full-js', 'locales-js', 'bkap-images-loaded', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-position', 'jquery-ui-selectmenu' ),
				BKAP_VERSION,
				false
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

			$booking_calendar_params = array(
				'lang'          => $global_settings->booking_language,
				'timeslots'     => $timeslots,
				'initialview'   => $initialview,
				'headertoolbar' => $headertoolbar,
				'daymaxevents'  => $daymaxevents,
				'ajaxurl'       => admin_url( 'admin-ajax.php'/* , $schema */ ),
				'loading'       => __( 'Loading', 'woocommerce-booking' ),
				'tip_close'     => __( 'Close', 'woocommerce-booking' ),
			);

			if ( isset( $vendor_id ) && $vendor_id !== '' ) {
				$booking_calendar_params['bkap_vendor_id'] = '&vendor_id=' . $vendor_id;
			}

			wp_localize_script(
				'booking-calender-js',
				'booking_calendar_params',
				$booking_calendar_params
			);

			wp_enqueue_script( 'booking-calender-js' );
		}

		/**
		 * Localize parameters for calendar JS files
		 *
		 * @param string|int $vendor_id Vendor ID.
		 * @since 4.6.0
		 */
		public static function localize_script( $vendor_id = '' ) {

			$schema                          = is_ssl() ? 'https' : 'http';
			$calendar_view_args              = array();
			$calendar_view_args['ajaxurl']   = admin_url( 'admin-ajax.php', $schema );
			$calendar_view_args['loading']   = __( 'Loading', 'woocommerce-booking' );
			$calendar_view_args['tip_close'] = __( 'Close', 'woocommerce-booking' );

			if ( isset( $vendor_id ) && $vendor_id !== '' ) {
				$calendar_view_args['bkap_vendor_id'] = '&vendor_id=' . $vendor_id;
			}

			wp_localize_script( 'booking-calender-js', 'bkap_calendar_view', $calendar_view_args );
		}

		/**
		 * Enquque BKAP Tab CSS
		 *
		 * @param string $plugin_version_number Plugin Version Number
		 *
		 * @since 4.6.0
		 */
		public static function bkap_load_bkap_tab_css( $plugin_version_number ) {

			wp_enqueue_style(
				'bkap-tabstyle-1',
				BKAP_Files::rewrite_asset_url( '/assets/css/bkap-tabs.css', BKAP_FILE ),
				'',
				$plugin_version_number,
				false
			);

			wp_enqueue_style(
				'bkap-tabstyle-2',
				BKAP_Files::rewrite_asset_url( '/assets/css/style.css', BKAP_FILE ),
				'',
				$plugin_version_number,
				false
			);
		}

		/**
		 * Enquque CSS for Products Pages
		 *
		 * @param string $plugin_version_number Plugin Version Number
		 *
		 * @since 4.6.0
		 */
		public static function bkap_load_products_css( $plugin_version_number ) {

			wp_enqueue_style(
				'bkap-booking',
				BKAP_Files::rewrite_asset_url( '/assets/css/booking.css', BKAP_FILE ),
				'',
				$plugin_version_number,
				false
			);

			// css file for the multi datepicker in admin product pages.
			wp_enqueue_style(
				'bkap-datepick',
				BKAP_Files::rewrite_asset_url( '/assets/css/jquery.datepick.css', BKAP_FILE ),
				'',
				$plugin_version_number,
				false
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

			$global_settings = bkap_global_setting();
			$calendar_theme  = $global_settings->booking_themes; // A default base theme will always be returned from initialization in bkap_global_settings function

			wp_dequeue_style( 'jquery-ui-style' );
			wp_register_style(
				'bkap-jquery-ui',
				BKAP_Files::rewrite_asset_url( "/assets/css/themes/$calendar_theme/jquery-ui.css", BKAP_FILE, true, false ),
				'',
				$plugin_version_number,
				false
			);

			wp_enqueue_style( 'bkap-jquery-ui' );

			do_action( 'bkap_after_load_products_css' );
		}

		/**
		 * Enquque CSS for Calendar View
		 *
		 * @param string $plugin_version_number Plugin Version Number
		 *
		 * @since 4.6.0
		 */

		public static function bkap_load_calendar_styles( $plugin_version_number ) {
			wp_enqueue_style(
				'bkap-data',
				BKAP_Files::rewrite_asset_url( '/assets/css/view.booking.style.css', BKAP_FILE ),
				'',
				$plugin_version_number
			);

			wp_enqueue_style(
				'bkap-fullcalendar-css',
				BKAP_Files::rewrite_asset_url( '/assets/css/fullcalendar.css',BKAP_FILE ),
				array(),
				BKAP_VERSION
			);

			// this is used for displying the hover effect in calendar view.
			wp_enqueue_style(
				'bkap-qtip-css',
				BKAP_Files::rewrite_asset_url( '/assets/css/jquery.qtip.min.css', BKAP_FILE ),
				array(),
				BKAP_VERSION
			);

			// javascript for handling clicks of calendar icon changes.
			wp_register_script(
				'bkap-global-settings',
				BKAP_Files::rewrite_asset_url( '/assets/js/global-booking-settings.js', BKAP_FILE ),
				array(),
				$plugin_version_number,
				false
			);
			wp_localize_script(
				'bkap-global-settings',
				'bkap_global_settings_params',
				array( 'ajax_url' => AJAX_URL )
			);
			wp_enqueue_script( 'bkap-global-settings' );
		}

		/**
		 * Enquque CSS for Dokan Vendor View
		 *
		 * @param string $plugin_version_number Plugin Version Number
		 *
		 * @since 4.6.0
		 */

		public static function bkap_load_dokan_css( $plugin_version_number ) {

			wp_enqueue_style(
				'bkap-dokan-css',
				BKAP_Files::rewrite_asset_url( '/assets/css/bkap-dokan.css', BKAP_FILE ),
				'',
				$plugin_version_number,
				false
			);
		}

		/**
		 * Enquque CSS for Dokan View Booking
		 *
		 * @param string $plugin_version_number Plugin Version Number
		 *
		 * @since 4.6.0
		 */
		public static function bkap_load_dokan_booking_styles( $plugin_version_number ) {

			wp_enqueue_style(
				'bkap-dokan-booking-css',
				BKAP_Files::rewrite_asset_url( '/assets/css/vendors/dokan/bkap-dokan-booking.css', BKAP_FILE ),
				'',
				$plugin_version_number,
				false
			);
		}

		/**
		 * Includes CSS files for the WC Vendors Dashboard.
		 *
		 * @since 4.6.0
		 * @param string $plugin_version Plugin Version Number
		 */

		public static function bkap_wcv_dashboard_css( $plugin_version ) {

			wp_enqueue_style(
				'bkap-woo-css',
				plugins_url() . '/woocommerce/assets/css/woocommerce.css',
				'',
				$plugin_version,
				false
			);

			wp_enqueue_style(
				'bkap-wcv-css',
				BKAP_Files::rewrite_asset_url( '/assets/css/vendors/wc-vendors/bkap-wcv-bookings.css', BKAP_FILE ),
				'',
				$plugin_version,
				false
			);
		}
	}
}
