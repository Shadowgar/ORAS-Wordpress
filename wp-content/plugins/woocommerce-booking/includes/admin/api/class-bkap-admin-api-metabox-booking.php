<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * REST API for Metabox - Booking.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/API/Metabox/Booking
 * @category    Classes
 * @since       5.19.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * BKAP REST API.
 *
 * @since 5.19.0
 */
class BKAP_Admin_API_Metabox_Booking extends BKAP_Admin_API {

	/**
	 * Settings.
	 *
	 * @var array $all_settings
	 */
	public static $all_settings = array();

	/**
	 * Construct
	 *
	 * @since 5.19.0
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_endpoints' ) );
	}

	/**
	 * Function for registering the API endpoints.
	 *
	 * @since 5.19.0
	 */
	public static function register_endpoints() {

		// Fetch Metabox Booking data.
		register_rest_route(
			self::$base_endpoint,
			'metabox/booking/fetch',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'fetch_metabox_booking_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Save Metabox Booking data.
		register_rest_route(
			self::$base_endpoint,
			'metabox/booking/save',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'save_metabox_booking_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Delete Availability data.
		register_rest_route(
			self::$base_endpoint,
			'metabox/booking/availability/delete',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'delete_availability_record' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Update Weekdays/Dates Timeslots.
		register_rest_route(
			self::$base_endpoint,
			'metabox/booking/weekdays-dates-timeslots/update',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'update_weekdays_dates_timeslots' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Delete Weekdays/Dates Timeslots.
		register_rest_route(
			self::$base_endpoint,
			'metabox/booking/weekdays-dates-timeslots/delete',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'delete_weekdays_dates_timeslots' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Delete ALL Weekdays/Dates Timeslots.
		register_rest_route(
			self::$base_endpoint,
			'metabox/booking/weekdays-dates-timeslots/delete-all',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'delete_all_weekdays_dates_timeslots' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Delete Block Pricing Price Range by Nights Data.
		register_rest_route(
			self::$base_endpoint,
			'metabox/booking/block-pricing/price-range-by-nights/delete',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'delete_block_pricing_price_range_by_night_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Delete ALL Block Pricing Price Range by Nights Data.
		register_rest_route(
			self::$base_endpoint,
			'metabox/booking/block-pricing/price-range-by-nights/delete-all',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'delete_all_block_pricing_price_range_by_night_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Delete Fixed Block Data.
		register_rest_route(
			self::$base_endpoint,
			'metabox/booking/block-pricing/fixed-block/delete',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'delete_block_pricing_fixed_block_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Delete ALL Fixed Block Data.
		register_rest_route(
			self::$base_endpoint,
			'metabox/booking/block-pricing/fixed-block/delete-all',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'delete_all_block_pricing_fixed_block_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Delete Person Data.
		register_rest_route(
			self::$base_endpoint,
			'metabox/booking/person/delete',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'delete_person_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Delete ALL Persons Data.
		register_rest_route(
			self::$base_endpoint,
			'metabox/booking/person/delete-all',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'delete_all_person_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Delete Resource Data.
		register_rest_route(
			self::$base_endpoint,
			'metabox/booking/resource/delete',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'delete_resource_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Delete ALL Resource Data.
		register_rest_route(
			self::$base_endpoint,
			'metabox/booking/resource/delete-all',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'delete_all_resource_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);
	}

	/**
	 * Returns Metabox Booking Data.
	 *
	 * @param bool   $return_raw Whether to return the Raw response.
	 * @param object $post Post.
	 *
	 * @since 5.19.0
	 */
	public static function fetch_metabox_booking_data( $return_raw, $post ) {

		global $bkap_dates_months_availability, $bkap_months, $bkap_fixed_days;

		$bkap_weekdays = bkap_weekdays();

		$response                 = array();
		$product_id               = isset( $post ) ? $post->ID : 0;
		$duplicate_of             = bkap_common::bkap_get_product_id( $product_id );
		$booking_settings         = get_post_meta( $duplicate_of, 'woocommerce_booking_settings', true );
		$default_booking_settings = array();
		$has_defaults             = false;
		$bkap_bl_option           = apply_filters( 'bkap_bl_option', '' );
		$_product                 = wc_get_product( $duplicate_of );

		if ( ( isset( $post->filter ) && 'raw' === $post->filter ) && ( '' === $booking_settings || false === $booking_settings || 0 === $product_id ) ) {
			$booking_settings         = get_option( 'bkap_default_booking_settings', array() );
			$default_booking_settings = get_option( 'bkap_default_individual_booking_settings', array() );
			$has_defaults             = ( ! empty( $default_booking_settings ) );
		}

		if ( '' === $booking_settings || false === $booking_settings || ! is_array( $booking_settings ) ) {
			$booking_settings = array();
		}

		$booking_settings['product_id'] = $duplicate_of;

		$booking_type       = bkap_get_post_meta_data( $duplicate_of, '_bkap_booking_type', $default_booking_settings, $has_defaults );
		$inline_calendar    = self::check( $booking_settings, 'enable_inline_calendar' );
		$onboarding_setting = get_option( 'bkap_onboarding_booking_settings', array() );

		if ( ( isset( $post->filter ) && 'raw' === $post->filter ) && ! empty( $onboarding_setting ) && ! $has_defaults ) {
			$booking_type          = isset( $onboarding_setting['bkap_booking_type'] ) ? $onboarding_setting['bkap_booking_type'] : $booking_type;
			$inline_calendar       = isset( $onboarding_setting['bkap_inline'] ) && 'on' === $onboarding_setting['bkap_inline'] ? 'on' : $inline_calendar;
			$bkap_default_weekdays = isset( $onboarding_setting['bkap_weekdays'] ) ? $onboarding_setting['bkap_weekdays'] : array();

			add_post_meta( $duplicate_of, '_bkap_booking_type', $booking_type );
			add_post_meta( $duplicate_of, '_bkap_enable_inline', $inline_calendar );
			add_post_meta( $duplicate_of, '_bkap_recurring_weekdays', $bkap_default_weekdays );

			$booking_settings['enable_inline_calendar'] = $inline_calendar;
			$booking_settings['booking_recurring']      = $bkap_default_weekdays;
			update_post_meta( $duplicate_of, 'woocommerce_booking_settings', $booking_settings );
		}

		$response['general'] = array(
			'settings' => array(
				'booking_enable_date'            => self::check( $booking_settings, 'booking_enable_date', apply_filters( 'bkap_enable_booking_default_value', '' ) ),
				'booking_type'                   => '' === $booking_type || false === $booking_type ? 'only_day' : $booking_type,
				'enable_inline_calendar'         => $inline_calendar,
				'booking_purchase_without_date'  => self::check( $booking_settings, 'booking_purchase_without_date' ),
				'booking_confirmation'           => self::check( $booking_settings, 'booking_confirmation' ),
				'show_dates_dropdown'            => self::check( $booking_settings, 'bkap_date_in_dropdown' ),
				'bkap_can_be_cancelled'          => ( isset( $booking_settings['booking_can_be_cancelled'] ) && isset( $booking_settings['booking_can_be_cancelled']['status'] ) ) ? $booking_settings['booking_can_be_cancelled']['status'] : '',
				'bkap_can_be_cancelled_duration' => ( isset( $booking_settings['booking_can_be_cancelled'] ) && isset( $booking_settings['booking_can_be_cancelled']['status'] ) && 'on' === $booking_settings['booking_can_be_cancelled']['status'] && isset( $booking_settings['booking_can_be_cancelled']['duration'] ) && '' !== $booking_settings['booking_can_be_cancelled']['duration'] ) ? $booking_settings['booking_can_be_cancelled']['duration'] : '',
				'bkap_can_be_cancelled_period'   => ( isset( $booking_settings['booking_can_be_cancelled'] ) && isset( $booking_settings['booking_can_be_cancelled']['status'] ) && 'on' === $booking_settings['booking_can_be_cancelled']['status'] && isset( $booking_settings['booking_can_be_cancelled']['period'] ) && '' !== $booking_settings['booking_can_be_cancelled']['period'] ) ? $booking_settings['booking_can_be_cancelled']['period'] : 'day',
				'multidates_type'                => self::check( $booking_settings, 'multidates_type', 'fixed' ),
				'multidates_fixed_number'        => self::check( $booking_settings, 'multidates_fixed_number', 2 ),
				'multidates_range_min'           => self::check( $booking_settings, 'multidates_range_min' ),
				'multidates_range_max'           => self::check( $booking_settings, 'multidates_range_max' ),
			),
		);

		$weekday_settings = self::metabox_weekday_settings( $booking_settings, bkap_get_post_meta_data( $duplicate_of, '_bkap_special_price', $default_booking_settings, $has_defaults ) );

		if ( ( isset( $post->filter ) && 'raw' === $post->filter ) && ! empty( $onboarding_setting ) && ! $has_defaults ) {
			$onboarding_weekdays = isset( $onboarding_setting['bkap_weekdays'] ) ? $onboarding_setting['bkap_weekdays'] : array();

			foreach ( $weekday_settings as $w_key => $w_value ) {
				if ( isset( $onboarding_weekdays[ $w_key ] ) && $onboarding_weekdays[ $w_key ] == '' ) {
					$weekday_settings[ $w_key ]['status'] = '';
				}
			}
		}

		$response['availability'] = array(
			'settings' => array(
				'booking_minimum_number_days'          => self::check( $booking_settings, 'booking_minimum_number_days', 0 ),
				'booking_maximum_number_days'          => self::check( $booking_settings, 'booking_maximum_number_days', apply_filters( 'bkap_number_of_dates_to_choose', '30', $duplicate_of, $booking_settings ) ),
				'booking_date_range'                   => self::check( $booking_settings, 'booking_date_range' ),
				'booking_date_lockout'                 => self::check( $booking_settings, 'booking_date_lockout', 60 ),
				'booking_minimum_number_days_multiple' => self::check( $booking_settings, 'booking_minimum_number_days_multiple', 0 ),
				'booking_maximum_number_days_multiple' => self::check( $booking_settings, 'booking_maximum_number_days_multiple', 365 ),
				'enable_specific_booking'              => self::check( $booking_settings, 'booking_specific_booking' ),
				'enable_booking_time'                  => self::check( $booking_settings, 'booking_enable_time', '' ),
				'data'                                 => array(
					'weekday_settings'         => $weekday_settings,
					'availability'             => self::return_availability_data(
						$booking_settings,
						array(
							'booking_type'   => $booking_type,
							'custom_ranges'  => bkap_get_post_meta_data( $duplicate_of, '_bkap_custom_ranges', $default_booking_settings, $has_defaults ),
							'holiday_ranges' => bkap_get_post_meta_data( $duplicate_of, '_bkap_holiday_ranges', $default_booking_settings, $has_defaults ),
							'month_ranges'   => bkap_get_post_meta_data( $duplicate_of, '_bkap_month_ranges', $default_booking_settings, $has_defaults ),
							'specific_dates' => bkap_get_post_meta_data( $duplicate_of, '_bkap_specific_dates', $default_booking_settings, $has_defaults ),
							'special_prices' => bkap_get_post_meta_data( $duplicate_of, '_bkap_special_price', $default_booking_settings, $has_defaults ),
						)
					),
					'manage_availability'      => self::return_manage_availability_data( $booking_settings ),
					'weekdays_dates_timeslots' => self::metabox_weekdays_dates_timeslots( $booking_settings ),
				),
				'duration_based_bookings'              => self::return_duration_based_bookings_data( $booking_settings ),
				'manage_availability_all_data'         => self::check( $booking_settings, 'bkap_all_data_unavailable', '' ),
			),
		);

		$response['availability']['settings']['data']['_weekdays_dates_timeslots'] = $response['availability']['settings']['data']['weekdays_dates_timeslots'];

		$response['block_pricing'] = array(
			'settings' => array(
				'block_pricing_type' => self::return_block_pricing_type( $duplicate_of, $default_booking_settings, $has_defaults ),
				'header_columns_block_pricing_price_by_range_nights' => self::return_header_columns_block_pricing_price_by_range_nights( $duplicate_of ),
				'data'               => array(
					'fixed_block'              => self::return_fixed_block_data( $duplicate_of, $default_booking_settings, $has_defaults ),
					'price_by_range_of_nights' => self::return_price_by_range_of_nights_data( $duplicate_of, $default_booking_settings, $has_defaults ),
				),
			),
		);

		if ( $_product && in_array( $_product->get_type(), array( 'variable', 'variable-subscription' ), true ) ) {
			$response['block_pricing']['settings']['product_attributes'] = get_post_meta( $duplicate_of, '_product_attributes', true );
		}

		$response['persons'] = array(
			'settings' => array(
				'bkap_person'              => self::check( $booking_settings, 'bkap_person', '' ),
				'bkap_min_person'          => self::check( $booking_settings, 'bkap_min_person', 1 ),
				'bkap_max_person'          => self::check( $booking_settings, 'bkap_max_person', 1 ),
				'bkap_price_per_person'    => self::check( $booking_settings, 'bkap_price_per_person', '' ),
				'bkap_each_person_booking' => self::check( $booking_settings, 'bkap_each_person_booking', '' ),
				'bkap_person_type'         => self::check( $booking_settings, 'bkap_person_type', '' ),
				'data'                     => array(
					'person_settings' => self::return_person_type_data( $booking_settings ),
				),
			),
		);

		$response['resources'] = array(
			'settings' => array(
				'bkap_resource'                => bkap_get_post_meta_data( $duplicate_of, '_bkap_resource', $default_booking_settings, $has_defaults ),
				'resource_label'               => bkap_get_post_meta_data( $duplicate_of, '_bkap_product_resource_lable', $default_booking_settings, $has_defaults ),
				'resource_assignment'          => self::return_value_if_empty( bkap_get_post_meta_data( $duplicate_of, '_bkap_product_resource_selection', $default_booking_settings, $has_defaults ), 'bkap_customer_resource' ),
				'resource_position'            => self::return_value_if_empty( bkap_get_post_meta_data( $duplicate_of, '_bkap_product_resource_position', $default_booking_settings, $has_defaults ), 'before' ),
				'consider_product_max_booking' => bkap_get_post_meta_data( $duplicate_of, '_bkap_product_resource_max_booking', $default_booking_settings, $has_defaults ),
				'resource_sort_option'         => bkap_get_post_meta_data( $duplicate_of, '_bkap_product_resource_sorting', $default_booking_settings, $has_defaults ),
				'resource_selection'           => self::return_value_if_empty( bkap_get_post_meta_data( $duplicate_of, '_bkap_product_resource_selection_type', $default_booking_settings, $has_defaults ), 'single' ),
				'data'                         => array(
					'resource_settings' => self::return_resource_data( $duplicate_of, $default_booking_settings, $has_defaults ),
				),
			),
			'site_url' => admin_url(),
		);

		// Integrations.
		$bkap_calendar_oauth_integration = get_post_meta( $duplicate_of, '_bkap_calendar_oauth_integration', true );
		$bkap_oauth_gcal                 = new BKAP_OAuth_Google_Calendar( $duplicate_of, get_current_user_id() );
		$fluent_crm_plugins_active       = bkap_fluentcrm()->bkap_fluentcrm_lite_active() && bkap_fluentcrm()->bkap_fluentcrm_pro_active();
		$fluentcrm_lists                 = $fluent_crm_plugins_active ? bkap_fluentcrm()->get_lists() : array();

		$response['integrations'] = array(
			'settings' => array(
				'is_grouped_product'                 => ( $_product && 'grouped' === $_product->get_type() ) ? true : false,
				'is_variable_product'                => ( $_product && 'variable' === $_product->get_type() ) ? true : false,
				'product_sync_integration_mode'      => self::check( $booking_settings, 'product_sync_integration_mode', 'disabled' ),
				'bkap_calendar_oauth_integration'    => array(
					'is_integration_active' => $bkap_oauth_gcal->bkap_is_integration_active(),
					'client_id'             => self::check( $bkap_calendar_oauth_integration, 'client_id', '' ),
					'client_secret'         => self::check( $bkap_calendar_oauth_integration, 'client_secret', '' ),
					'calendar_id'           => self::check( $bkap_calendar_oauth_integration, 'calendar_id', '' ),
					'connect_link'          => '',
					'logout_url'            => '',
					'calendars'             => array(),
				),
				'bkap_calendar_directly_integration' => array(
					'key_file_name'  => self::check( $booking_settings, 'product_sync_key_file_name', '' ),
					'email_address'  => self::check( $booking_settings, 'product_sync_service_acc_email_addr', '' ),
					'json_file_name' => self::check( $booking_settings, 'bkap_calendar_json_file_name', '' ),
					'json_data'      => self::check( $booking_settings, 'bkap_calendar_json_file_data', '' ),
					'calendar_id'    => self::check( $booking_settings, 'product_sync_calendar_id', '' ),
				),
				'bkap_gcal_success'                  => '',
				'bkap_gcal_failure'                  => '',
				'enable_automated_mapping'           => self::check( $booking_settings, 'enable_automated_mapping', '' ),
				'default_variation_id_for_events'    => self::check( $booking_settings, 'gcal_default_variation', '' ),
				'bkap_outlook_calendar_integration'  => array(
					'is_outlook_calendar_addon_active' => is_plugin_active( 'bkap-outlook-calendar/bkap-outlook-calendar.php' ),
					'is_enabled'                       => self::check( $booking_settings, 'bkap_outlook_calendar', '' ),
					'client_id'                        => self::check( $booking_settings, 'bkap_outlook_calendar_client_id', '' ),
					'client_secret'                    => self::check( $booking_settings, 'bkap_outlook_calendar_client_secret', '' ),
					'calendar_id'                      => self::check( $booking_settings, 'bkap_outlook_calendar_id', '' ),
					'connect_link'                     => '',
					'redirect_uri'                     => '',
					'logout_url'                       => '',
					'calendars'                        => '',
				),
				'bkap_fluentcrm_integration'         => array(
					'is_plugin_activated'                => $fluent_crm_plugins_active,
					'is_l_active'                        => apply_filters( 'bkap_el_option', true ),
					'is_api_connection_settings_present' => '' !== bkap_fluentcrm()->fluentcrm_api_name && '' !== bkap_fluentcrm()->fluentcrm_api_key,
					'is_enabled'                         => self::check( $booking_settings, 'bkap_fluentcrm', '' ),
					'list'                               => self::check( $booking_settings, 'bkap_fluentcrm_list', '' ),
					'lists'                              => is_array( $fluentcrm_lists ) && count( $fluentcrm_lists ) > 0 && isset( $fluentcrm_lists['lists'] ) ? $fluentcrm_lists['lists'] : array(),
				),
				'bkap_zapier_integration'            => array(
					'is_l_active'                   => apply_filters( 'bkap_el_option', true ),
					'create_booking_trigger'        => array(
						'is_enabled' => BKAP_Zapier::bkap_api_zapier_is_create_booking_trigger_enabled(),
						'hooks'      => BKAP_Zapier::bkap_api_zapier_get_create_booking_trigger_hooks( get_current_user_id() ),
					),
					'create_booking_trigger_status' => BKAP_Zapier::bkap_api_zapier_is_create_booking_trigger_enabled_for_product( $duplicate_of ) ? 'on' : '',
					'create_booking_trigger_label'  => BKAP_Zapier::bkap_api_zapier_get_create_booking_trigger_product_label( $duplicate_of ),

					'update_booking_trigger'        => array(
						'is_enabled' => BKAP_Zapier::bkap_api_zapier_is_update_booking_trigger_enabled(),
						'hooks'      => BKAP_Zapier::bkap_api_zapier_get_update_booking_trigger_hooks( get_current_user_id() ),
					),
					'update_booking_trigger_status' => BKAP_Zapier::bkap_api_zapier_is_update_booking_trigger_enabled_for_product( $duplicate_of ) ? 'on' : '',
					'update_booking_trigger_label'  => BKAP_Zapier::bkap_api_zapier_get_update_booking_trigger_product_label( $duplicate_of ),

					'delete_booking_trigger'        => array(
						'is_enabled' => BKAP_Zapier::bkap_api_zapier_is_delete_booking_trigger_enabled(),
						'hooks'      => BKAP_Zapier::bkap_api_zapier_get_delete_booking_trigger_hooks( get_current_user_id() ),
					),
					'delete_booking_trigger_status' => BKAP_Zapier::bkap_api_zapier_is_delete_booking_trigger_enabled_for_product( $duplicate_of ) ? 'on' : '',
					'delete_booking_trigger_label'  => BKAP_Zapier::bkap_api_zapier_get_delete_booking_trigger_product_label( $duplicate_of ),
				),
				'bkap_zoom_integration'              => array(
					'zoom_keys_are_set'          => bkap_zoom_connection()->zoom_keys_are_set(),
					'is_l_active'                => apply_filters( 'bkap_el_option', true ),
					'is_enabled'                 => self::check( $booking_settings, 'zoom_meeting', '' ),
					'user_list_can_be_retrieved' => bkap_zoom_connection()->user_list_can_be_retrieved(),
					'user_list'                  => bkap_zoom_connection()->user_list(),
					'auto_recording_list'        => array(
						'none'  => __( 'None', 'woocommerce-booking' ),
						'local' => __( 'Local', 'woocommerce-booking' ),
						'cloud' => __( 'Cloud', 'woocommerce-booking' ),
					),
					'host'                       => self::check( $booking_settings, 'zoom_meeting_host', '' ),
					'meeting_authentication'     => self::check( $booking_settings, 'zoom_meeting_auth', '' ),
					'join_before_host'           => self::check( $booking_settings, 'zoom_meeting_join_before_host', '' ),
					'host_video'                 => self::check( $booking_settings, 'zoom_meeting_host_video', '' ),
					'participant_video'          => self::check( $booking_settings, 'zoom_meeting_participant_video', '' ),
					'mute_upon_entry'            => self::check( $booking_settings, 'zoom_meeting_mute_upon_entry', '' ),
					'auto_recording'             => self::check( $booking_settings, 'zoom_meeting_auto_recording', 'none' ),
					'alternative_hosts'          => self::check( $booking_settings, 'zoom_meeting_alternative_host', array() ),
				),
			),
		);

		$variations_for_events = array();

		if ( $_product && 'variable' === $_product->get_type() ) {
			$variations = $_product->get_available_variations();

			if ( is_array( $variations ) && count( $variations ) > 0 ) {
				foreach ( $variations as $key => $value ) {
					$variation_product       = wc_get_product( $value['variation_id'] );
					$variations_for_events[] = array(
						'variation_id'   => $value['variation_id'],
						'variation_name' => $variation_product->get_name() . ' #(' . $value['variation_id'] . ')',
					);
				}
			}
		}

		$response['integrations']['settings']['variations_for_events'] = $variations_for_events;
		$response['integrations']['settings']['bkap_ics_feed_urls']    = BKAP_Admin_API_Google_Calendar::get_ics_feed_urls( (int) $duplicate_of );

		if ( isset( $_GET['bkap_con_status'] ) ) { // phpcs:ignore
			$status = $_GET['bkap_con_status']; // phpcs:ignore
			switch ( $status ) {
				case 'success':
					$response['integrations']['settings']['bkap_gcal_success'] = __( 'Google Calendar successfully connected.', 'woocommerce-booking' );
					break;
				case 'fail':
					$uploads     = wp_upload_dir(); // Set log file location.
					$uploads_dir = isset( $uploads['basedir'] ) ? $uploads['basedir'] . '/' : WP_CONTENT_DIR . '/uploads/';
					$log_file    = $uploads_dir . 'bkap-log.txt';
					/* translators: %s: Bkap Log file url. */
					$message = sprintf( __( 'Failed to connect to your account, please try again, if the problem persists, please check the log in the %s file and see what is happening or please contact Support team.', 'woocommerce-booking' ), $log_file );
					$response['integrations']['settings']['bkap_gcal_failure'] = $message;
					break;
			}
		}

		$oauth = new BKAP_OAuth_Google_Calendar( $duplicate_of, get_current_user_id() );
		$response['integrations']['settings']['bkap_calendar_oauth_integration']['redirect_uri'] = $oauth->bkap_get_redirect_uri();
		if ( isset( $response['integrations']['settings']['bkap_calendar_oauth_integration']['client_id'] ) && '' !== $response['integrations']['settings']['bkap_calendar_oauth_integration']['client_id'] && isset( $response['integrations']['settings']['bkap_calendar_oauth_integration']['client_secret'] ) && '' !== $response['integrations']['settings']['bkap_calendar_oauth_integration']['client_secret'] ) {
			try {
				$authorization_url = $oauth->bkap_get_google_auth_url();
				$response['integrations']['settings']['bkap_calendar_oauth_integration']['connect_link'] = '' !== $authorization_url ? $authorization_url : 'javascript:void(0)';
				$response['integrations']['settings']['bkap_calendar_oauth_integration']['calendars']    = $oauth->bkap_get_calendar_list_options();

				if ( $oauth->bkap_is_integration_active() ) {
					$response['integrations']['settings']['bkap_calendar_oauth_integration']['logout_url'] = 'yes';
				}
			} catch ( Exception $e ) {
				// TODO: Display error message on front-end about caught exceptions.
			}
		}

		if ( '' !== $response['integrations']['settings']['bkap_outlook_calendar_integration']['client_id'] && '' !== $response['integrations']['settings']['bkap_outlook_calendar_integration']['client_secret'] && class_exists( 'BKAP_Outlook_Calendar_OAuth' ) ) {
			try {
				$outlook = new BKAP_Outlook_Calendar_OAuth( $duplicate_of, get_current_user_id() );
				$outlook->bkap_outlook_connect();
				$bkap_authorization_url = $outlook->bkap_authorization_url();
				$response['integrations']['settings']['bkap_outlook_calendar_integration']['connect_link'] = '' !== $bkap_authorization_url ? $bkap_authorization_url : 'javascript:void(0)';
				$response['integrations']['settings']['bkap_outlook_calendar_integration']['redirect_uri'] = $outlook->bkap_get_redirect_uri();
				$response['integrations']['settings']['bkap_outlook_calendar_integration']['logout_url']   = $outlook->bkap_logout_url();
				$response['integrations']['settings']['bkap_outlook_calendar_integration']['calendars']    = $outlook->bkap_outlook_calendar_list();
			} catch ( Exception $e ) {
				// TODO: Display error message on front-end about caught exceptions.
			}
		}

		$bkap_intervals = bkap_intervals();

		$specific_dates      = ( isset( $booking_settings['booking_specific_date'] ) && count( $booking_settings['booking_specific_date'] ) > 0 ? $booking_settings['booking_specific_date'] : array() );
		$specific_dates_data = array();
		foreach ( $specific_dates as $key => $value ) {
			$specific_dates_data[ $key ] = $key;
		}

		$response['settings'] = array(
			'booking_types'                             => self::booking_types(),
			'booking_can_be_cancelled_periods'          => array(
				'day'    => __( 'Day(s)', 'woocommerce-booking' ),
				'hour'   => __( 'Hour(s)', 'woocommerce-booking' ),
				'minute' => __( 'Minute(s)', 'woocommerce-booking' ),
			),
			'multidates_selection_type'                 => array(
				'fixed' => __( 'Fixed dates', 'woocommerce-booking' ),
				'range' => __( 'Range based', 'woocommerce-booking' ),
			),
			'currency_symbol'                           => get_woocommerce_currency_symbol(),
			'table_header_price'                        => 'Price (' . get_woocommerce_currency_symbol() . ')',
			'placeholders'                              => array(
				'max_bookings'    => __( 'Max bookings', 'woocommerce-booking' ),
				'special_price'   => __( 'Special Price', 'woocommerce-booking' ),
				'number_of_years' => __( 'No. of Years', 'woocommerce-booking' ),
				'price'           => __( 'Price', 'woocommerce-booking' ),
				'block_name'      => __( 'Enter Name of Block', 'woocommerce-booking' ),
			),
			'titles'                                    => array(
				'custom_range_number_of_years'     => __( 'Please enter the number of years you want this custom range to recur.', 'woocommerce-booking' ),
				'specific_dates_max_bookings'      => __( 'This field is for maximum booking for selected specific dates.', 'woocommerce-booking' ),
				'specific_dates_price'             => __( 'This field is for price of selected specific dates.', 'woocommerce-booking' ),
				'range_of_months_number_of_years'  => __( 'Please enter the number of years you want the selected months to recur.', 'woocommerce-booking' ),
				'holidays_number_of_years'         => __( 'Please enter the number of years you want the selected holidays to recur.', 'woocommerce-booking' ),
				'weekdays_dates_timeslots_from_to' => __( 'Please enter time in 24 hour format e.g 14:00 or 03:00', 'woocommerce-booking' ),
			),
			'labels'                                    => array(
				'yes'                                      => __( 'Yes', 'woocommerce-booking' ),
				'no'                                       => __( 'No', 'woocommerce-booking' ),
				'loader_saving_general_settings'           => __( 'Saving General Settings, please wait...', 'woocommerce-booking' ),
				'loader_saving_availability_settings'      => __( 'Saving Availability Settings, please wait...', 'woocommerce-booking' ),
				'loader_saving_block_pricing_settings'     => __( 'Saving Block Pricing Settings, please wait...', 'woocommerce-booking' ),
				'loader_saving_persons_settings'           => __( 'Saving Persons Settings, please wait...', 'woocommerce-booking' ),
				'loader_saving_seasonal_pricing_settings'  => __( 'Saving Season Settings, please wait...', 'woocommerce-booking' ),
				'loader_saving_rental_settings'            => __( 'Saving Rental Settings, please wait...', 'woocommerce-booking' ),
				'loader_deleting_timeslots'                => __( 'Deleting, please wait...', 'woocommerce-booking' ),
				'loader_deleting_all_timeslots'            => __( 'Deleting all timeslots, please wait...', 'woocommerce-booking' ),
				'loader_updating_timeslots'                => __( 'Updating selected timeslot, please wait...', 'woocommerce-booking' ),
				'loader_deleting_price_range_by_months_data' => __( 'Deleting selected Price Range data, please wait...', 'woocommerce-booking' ),
				'loader_deleting_all_price_range_by_months_data' => __( 'Deleting ALL of the Price Range data, please wait...', 'woocommerce-booking' ),
				'loader_deleting_fixed_block_data'         => __( 'Deleting selected Fixed Block data, please wait...', 'woocommerce-booking' ),
				'loader_deleting_all_fixed_block_data'     => __( 'Deleting ALL of the Fixed Block data, please wait...', 'woocommerce-booking' ),
				'loader_deleting_person_data'              => __( 'Deleting selected Persons data, please wait...', 'woocommerce-booking' ),
				'loader_deleting_all_person_data'          => __( 'Deleting ALL of the Persons data, please wait...', 'woocommerce-booking' ),
				'loader_saving_resource_settings'          => __( 'Saving Resource Settings, please wait...', 'woocommerce-booking' ),
				'loader_deleting_resource_data'            => __( 'Deleting selected Linked Resource data, please wait...', 'woocommerce-booking' ),
				'loader_deleting_all_resource_data'        => __( 'Deleting ALL of the Linked Resource data, please wait...', 'woocommerce-booking' ),
				'loader_saving_integrations_settings'      => __( 'Saving Settings for Integrations, please wait...', 'woocommerce-booking' ),
				'loader_deleting_manage_availability_data' => __( 'Deleting selected Manage Availability data, please wait...', 'woocommerce-booking' ),
				'copy_booking_settings_success_message'    => __( 'Booking Settings Data has been copied!', 'woocommerce-booking' ),
				'copy_booking_settings_error_message'      => __( 'Error! Booking Settings Data could not be copied!', 'woocommerce-booking' ),
				'zoom_connection_not_active_message'       => __( 'Zoom connection has not been established. Please configure Zoom on the Integration Tab of the Settings page.', 'woocommerce-booking' ),
				'zoom_connection_user_list_empty'          => __( 'Error! Zoom User List cannot be retrieved.', 'woocommerce-booking' ),
			),
			'validation_messages'                       => array(
				'range_type_validation'       => __( 'The FROM value must be less than the TO value.', 'woocommerce-booking' ),
				'duration_range_validation'   => __( 'The START range must be less than the END range.', 'woocommerce-booking' ),
				'weekday_timeslot_validation' => __( 'The FROM Weekday timeslot must be less than the TO timeslot.', 'woocommerce-booking' ),
				'validation_alert_message'    => __( 'One or more fields have incorrect START/END or FROM/TO values.', 'woocommerce-booking' ),
			),
			'confirmation_messages'                     => array(
				'delete_all_timeslots'    => __( 'Are you sure you want to delete all timeslots?', 'woocommerce-booking' ),
				'delete_timeslot'         => __( 'Are you sure you want to delete this timeslot?', 'woocommerce-booking' ),
				'delete_price_range'      => __( 'Are you sure you want to delete this price range?', 'woocommerce-booking' ),
				'delete_all_price_ranges' => __( 'Are you sure you want to delete ALL of the price ranges?', 'woocommerce-booking' ),
				'delete_fixed_block'      => __( 'Are you sure you want to delete this fixed block?', 'woocommerce-booking' ),
				'delete_all_fixed_blocks' => __( 'Are you sure you want to delete ALL of the fixed blocks?', 'woocommerce-booking' ),
				'delete_person'           => __( 'Are you sure you want to delete this person data?', 'woocommerce-booking' ),
				'delete_all_persons'      => __( 'Are you sure you want to delete ALL of the person data?', 'woocommerce-booking' ),
				'delete_resource'         => __( 'Are you sure you want to delete this resource data?', 'woocommerce-booking' ),
				'delete_all_resources'    => __( 'Are you sure you want to delete ALL of the resource data?', 'woocommerce-booking' ),
			),
			'availability_range_types'                  => $bkap_dates_months_availability,
			'availability_months'                       => $bkap_months,
			'weekdays_dates_timeslots_weekday'          => array_merge( array( 'all' => __( 'All', 'woocommerce-booking' ) ), $bkap_weekdays, $specific_dates_data ),
			'duration_types'                            => array(
				'hours' => __( 'Hour(s)', 'woocommerce-booking' ),
				'mins'  => __( 'Min(s)', 'woocommerce-booking' ),
			),
			'block_pricing_fixed_days'                  => $bkap_fixed_days,
			'save_settings_button'                      => __( 'Save Settings', 'woocommerce-booking' ),
			'block_pricing_variable_product_attributes' => self::return_block_pricing_variable_product_attributes( $duplicate_of ),
			'resource_assignment_types'                 => array(
				'bkap_customer_resource'  => __( 'Chosen by Customer', 'woocommerce-booking' ),
				'bkap_automatic_resource' => __( 'Automatically Assigned', 'woocommerce-booking' ),
			),
			'resource_position_types'                 => array(
				'before' => __( 'Before date/time fields', 'woocommerce-booking' ),
				'after'  => __( 'After date/time fields', 'woocommerce-booking' ),
			),
			'resource_selection_types'                  => array(
				'single'   => __( 'Single Choice ( Dropdown )', 'woocommerce-booking' ),
				'multiple' => __( 'Multiple Choice ( Checkbox )', 'woocommerce-booking' ),
			),
			'resource_sort_options'                     => array(
				''           => array(
					'label' => __( 'Default', 'woocommerce-booking' ),
					'title' => __( 'Resources will appear as it appears in the below table.', 'woocommerce-booking' ),
				),
				'ascending'  => array(
					'label' => __( 'Ascending', 'woocommerce-booking' ),
					'title' => __( 'Resources will be sorted by Ascending order.', 'woocommerce-booking' ),
				),
				'menu_order' => array(
					'label' => __( 'Menu Order', 'woocommerce-booking' ),
					'title' => __( 'Resources will be sorted by the value set in Menu Order of Resource.', 'woocommerce-booking' ),
				),
				'price_low'  => array(
					'label' => __( 'Price - Low to High', 'woocommerce-booking' ),
					'title' => __( 'Resources will be sorted by price low to high.', 'woocommerce-booking' ),
				),
				'price_high' => array(
					'label' => __( 'Price - High to Low', 'woocommerce-booking' ),
					'title' => __( 'Resources will be sorted by price high to low.', 'woocommerce-booking' ),
				),
			),
			'resources'                                 => self::return_resources(),
			'intervals'                                 => $bkap_intervals,
			'range_type_general'                        => array(
				'custom' => $bkap_intervals['type']['custom'],
				'months' => $bkap_intervals['type']['months'],
				'weeks'  => $bkap_intervals['type']['weeks'],
				'days'   => $bkap_intervals['type']['days'],
			),
			'range_type_time_data'                      => $bkap_intervals['type']['time_data'],
			'copy_booking_settings_data'                => self::fetch_booking_settings_copy( $duplicate_of ),
			'partial_payment_type' => array(
				'value'            => __( 'Flat amount', 'woocommerce-booking' ),
				'percent'          => __( 'Percent', 'woocommerce-booking' ),
				'security_deposit' => __( 'Security deposit', 'woocommerce-booking' ),
			),
			'wp_roles'                                  => BKAP_Admin_API_Seasonal_Pricing::get_wp_roles(),
			'wc_currency_args'                          => bkap_common::get_currency_args(),
		);

		/* Rental Settings */

		$charge_per_day = 'on';
		if ( isset( $booking_settings['booking_charge_per_day'] ) ) {
			$charge_per_day = $booking_settings['booking_charge_per_day'];
		}

		$same_day = 'on';
		if ( isset( $booking_settings['booking_same_day'] ) ) {
			$same_day = $booking_settings['booking_same_day'];
		}

		$bkap_purchase_mode = '';
		$bkap_default_mode   = 'default_sale_mode';
		if ( isset( $booking_settings['bkap_purchase_mode'] ) ) {
			if ( 'default_sale_mode' === $booking_settings['bkap_purchase_mode'] || 'default_rent_mode' === $booking_settings['bkap_purchase_mode'] ) {
				$bkap_purchase_mode = 'both';
				$bkap_default_mode  = $booking_settings['bkap_purchase_mode'];
			} else {
				$bkap_purchase_mode = $booking_settings['bkap_purchase_mode'];
			}
		}

		$rental_active = is_plugin_active( 'bkap-rental/rental.php' );

		if ( $rental_active ) {
			$response['rental'] = array(
				'settings' => array(
					'is_plugin_activated'        => $rental_active,
					'is_l_active'                => $bkap_bl_option,
					'booking_prior_days_to_book' => self::check( $booking_settings, 'booking_prior_days_to_book', 0 ),
					'booking_later_days_to_book' => self::check( $booking_settings, 'booking_later_days_to_book', 0 ),
					'booking_charge_per_day'     => $charge_per_day,
					'booking_same_day'           => $same_day,
				),
			);
		} else {
			$response['rental']['settings'] = array();
		}

		if ( $rental_active ) {
			$response['general']['settings']['bkap_show_mode']     = self::check( $booking_settings, 'bkap_show_mode', '' );
			$response['general']['settings']['bkap_purchase_mode'] = $bkap_purchase_mode;
			$response['general']['settings']['bkap_default_mode']  = $bkap_default_mode;
		}
		/* Partial Deposits Settings */

		$response['partial_payments'] = array(
			'settings' => array(
				'is_plugin_activated'                   => is_plugin_active( 'bkap-deposits/deposits.php' ),
				'is_l_active'                           => $bkap_bl_option,
				'booking_partial_payment_enable'        => self::check( $booking_settings, 'booking_partial_payment_enable', '' ),
				'booking_partial_payment_radio'         => self::check( $booking_settings, 'booking_partial_payment_radio', 'value' ),
				'booking_partial_payment_value_deposit' => self::check( $booking_settings, 'booking_partial_payment_value_deposit', '' ),
				'allow_full_payment'                    => self::check( $booking_settings, 'allow_full_payment', '' ),
				'booking_deposit_x_days'                => self::check( $booking_settings, 'booking_deposit_x_days', '' ),
				'booking_default_payment_radio'         => self::check( $booking_settings, 'booking_default_payment_radio', 'partial_payment' ),
			),
		);

		/* Multiple TimeSlots Addon Settings */

		$response['availability']['settings']['is_multiple_timeslot_plugin_activated'] = is_plugin_active( 'bkap-multiple-time-slot/multiple-time-slot.php' );

		if ( $response['availability']['settings']['is_multiple_timeslot_plugin_activated'] ) {
			$response['availability']['settings']['booking_enable_multiple_time'] = self::check( $booking_settings, 'booking_enable_multiple_time', 'single' );
			$response['availability']['settings']['booking_enable_multiple_time'] = self::check( $booking_settings, 'booking_enable_multiple_time', 'single' );
		}

		/* Seasonal Pricing Addon Settings */

		$seasonal_active = is_plugin_active( 'bkap-seasonal-pricing/seasonal_pricing.php' );

		$response['seasonal_pricing'] = array(
			'settings'                  => array(
				'is_plugin_activated'             => $seasonal_active,
				'is_l_active'                     => $bkap_bl_option,
				'booking_seasonal_pricing_enable' => self::check( $booking_settings, 'booking_seasonal_pricing_enable', '' ),
			),
			'add_season_data'           => array(
				'amount_or_percent' => 'percent',
				'operator'          => 'add',
				'season_name'       => '',
				'user_role'         => array(),
				'start_date'        => '',
				'end_date'          => '',
				'price'             => '',
				'years'             => 1,
				'is_edit'           => false,
			),
			'show_add_season_data_page' => false,
		);

		if ( $seasonal_active ) {
			$response['seasonal_pricing']['seasons_configuration_data'] = BKAP_Admin_API_Seasonal_Pricing::fetch_seasons_configuration_data( $duplicate_of );
		}

		$response['product_id'] = $duplicate_of;

		return self::return_response( $response, $return_raw );
	}

	/**
	 * Re-arranges the Booking Types into groups needed for the Booking Type drpdow.
	 *
	 * @since 5.19.0
	 */
	public static function booking_types() {

		$booking_types_group = array();
		$booking_types       = bkap_get_booking_types();

		foreach ( $booking_types as $booking_type ) {

			// Check if sub-array item is an array.
			if ( isset( $booking_type ) && isset( $booking_type['key'] ) && isset( $booking_type['label'] ) ) {

				if ( isset( $booking_type['group'] ) && '' !== $booking_type['group'] ) {

					$group = $booking_type['group'];

					if ( ! isset( $booking_types_group[ $group ] ) ) {
						$booking_types_group[ $group ] = array();
					}

					$booking_types_group[ $group ][] = $booking_type;
				}
			} else {
				// Ensure that important items have been set.
				if ( ! isset( $booking_type['key'] ) || ! isset( $booking_type['label'] ) ) {
					continue;
				}

				$booking_types_group['n-g'][] = $booking_type; // n-g stands for no grouping.
			}
		}

		return $booking_types_group;
	}

	/**
	 * Saves Metabox Booking Data.
	 *
	 * @param WP_REST_Request $request Request.
	 * @param array           $settings Array of settings - for Bulk Booking Settings.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function save_metabox_booking_data( WP_REST_Request $request, $settings = array() ) {

		global $bkap_months;

		self::$all_settings = array();
		$data               = self::check( $settings, 'data', array() );
		$product_id         = self::check( $settings, 'product_id', array() );

		if ( is_array( $settings ) && 0 === count( $settings ) ) {
			if ( ! self::verify_nonce( $request, false ) ) {
				return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
			}

			$data       = $request->get_param( 'data' );
			$product_id = $request->get_param( 'product_id' );
		}

		$save_settings_as_default = 'on' === self::check( $data, 'save_settings_as_default', '' );

		if ( self::check( $data, 'is_save_all', false ) ) {
			$_data = self::check( $data, 'save_all_data', array() );

			if ( is_array( $_data ) && count( $_data ) > 0 ) {
				$data = array();

				foreach ( $_data as $__data ) {
					$data = array_merge_recursive( $data, $__data );
				}
			}
		}

		$woocommerce_booking_settings = get_post_meta( $product_id, 'woocommerce_booking_settings', true );

		if ( '' === $woocommerce_booking_settings || false === $woocommerce_booking_settings || ! is_array( $woocommerce_booking_settings ) ) {
			$woocommerce_booking_settings = array();
		}

		if ( is_array( $data ) && count( $data ) > 0 ) {

			foreach ( $data as $key => $value ) {

				// General.
				if ( 'booking_enable_date' === $key ) {
					self::update_post_meta( $product_id, '_bkap_enable_booking', $value );
					$woocommerce_booking_settings['booking_enable_date'] = $value;
				}

				if ( 'booking_type' === $key ) {
					self::update_post_meta( $product_id, '_bkap_booking_type', $value );

					switch ( $value ) {
						case 'date_time':
							$woocommerce_booking_settings['booking_enable_multiple_day'] = '';
							$woocommerce_booking_settings['booking_enable_time']         = 'on';
							break;
						case 'duration_time':
							$woocommerce_booking_settings['booking_enable_multiple_day'] = '';
							$woocommerce_booking_settings['booking_enable_time']         = 'duration_time';
							break;
						case 'multiple_days':
							$woocommerce_booking_settings['booking_enable_multiple_day'] = 'on';
							$woocommerce_booking_settings['booking_enable_time']         = '';
							break;
						case 'only_day':
							$woocommerce_booking_settings['booking_enable_multiple_day'] = '';
							$woocommerce_booking_settings['booking_enable_time']         = '';
							break;
						case 'multidates':
							$woocommerce_booking_settings['booking_enable_multiple_day'] = 'multidates';
							$woocommerce_booking_settings['booking_enable_time']         = '';
							break;
						case 'multidates_fixedtime':
							$woocommerce_booking_settings['booking_enable_multiple_day'] = 'multidates';
							$woocommerce_booking_settings['booking_enable_time']         = 'dates_time';
							break;
					}
				}

				if ( 'multidates_type' === $key ) {
					self::update_post_meta( $product_id, '_bkap_multidates_type', $value );
					$woocommerce_booking_settings['multidates_type'] = $value;
				}

				if ( 'multidates_fixed_number' === $key ) {
					self::update_post_meta( $product_id, '_bkap_multidates_fixed_number', $value );
					$woocommerce_booking_settings['multidates_fixed_number'] = $value;
				}

				if ( 'multidates_range_min' === $key ) {
					self::update_post_meta( $product_id, '_bkap_multidates_range_min', $value );
					$woocommerce_booking_settings['multidates_range_min'] = $value;
				}

				if ( 'multidates_range_max' === $key ) {
					self::update_post_meta( $product_id, '_bkap_multidates_range_max', $value );
					$woocommerce_booking_settings['multidates_range_max'] = $value;
				}

				if ( 'enable_inline_calendar' === $key ) {
					self::update_post_meta( $product_id, '_bkap_enable_inline', $value );
					$woocommerce_booking_settings['enable_inline_calendar'] = $value;
				}

				if ( 'booking_purchase_without_date' === $key ) {
					self::update_post_meta( $product_id, '_bkap_purchase_wo_date', $value );
					$woocommerce_booking_settings['booking_purchase_without_date'] = $value;
				}

				if ( 'booking_confirmation' === $key ) {
					self::update_post_meta( $product_id, '_bkap_requires_confirmation', $value );
					$woocommerce_booking_settings['booking_confirmation'] = $value;
				}

				if ( 'show_dates_dropdown' === $key ) {
					self::update_post_meta( $product_id, '_bkap_date_in_dropdown', $value );
					$woocommerce_booking_settings['bkap_date_in_dropdown'] = $value;
				}

				if ( 'bkap_can_be_cancelled' === $key ) {

					$value = array(
						'status'   => $value,
						'duration' => ( '' === $data['bkap_can_be_cancelled_duration'] ) ? 0 : abs( $data['bkap_can_be_cancelled_duration'] ),
						'period'   => $data['bkap_can_be_cancelled_period'],
					);

					self::update_post_meta( $product_id, '_bkap_can_be_cancelled', $value );
					$woocommerce_booking_settings['booking_can_be_cancelled'] = $value;
				}

				if ( ! isset( $woocommerce_booking_settings['booking_recurring_booking'] ) ) {
					$woocommerce_booking_settings['booking_recurring_booking'] = 'on';
				}

				if ( ! isset( $woocommerce_booking_settings['booking_recurring'] ) ) {
					$woocommerce_booking_settings['booking_recurring'] = array(
						'booking_weekday_0' => 'on',
						'booking_weekday_1' => 'on',
						'booking_weekday_2' => 'on',
						'booking_weekday_3' => 'on',
						'booking_weekday_4' => 'on',
						'booking_weekday_5' => 'on',
						'booking_weekday_6' => 'on',
					);

					self::update_post_meta( $product_id, '_bkap_recurring_weekdays', $woocommerce_booking_settings['booking_recurring'] );
				}

				if ( ! isset( $woocommerce_booking_settings['booking_recurring_lockout'] ) ) {
					$woocommerce_booking_settings['booking_recurring_lockout'] = array(
						'booking_weekday_0' => '',
						'booking_weekday_1' => '',
						'booking_weekday_2' => '',
						'booking_weekday_3' => '',
						'booking_weekday_4' => '',
						'booking_weekday_5' => '',
						'booking_weekday_6' => '',
					);
					self::update_post_meta( $product_id, '_bkap_recurring_lockout', $woocommerce_booking_settings['booking_recurring_lockout'] );
				}

				// Availability.
				if ( 'booking_minimum_number_days' === $key ) {
					self::update_post_meta( $product_id, '_bkap_abp', $value );
					$woocommerce_booking_settings['booking_minimum_number_days'] = $value;
				}

				if ( 'booking_maximum_number_days' === $key ) {
					$max_bookable_days = '' != $value ? $value : 0;
					$max_bookable_days = $max_bookable_days >= 0 ? $max_bookable_days : 0;
					self::update_post_meta( $product_id, '_bkap_max_bookable_days', $max_bookable_days );
					$woocommerce_booking_settings['booking_maximum_number_days'] = $max_bookable_days;
				}

				if ( 'booking_date_lockout' === $key ) {
					self::update_post_meta( $product_id, '_bkap_date_lockout', $value );
					$woocommerce_booking_settings['booking_date_lockout'] = $value;
				}

				if ( 'booking_minimum_number_days_multiple' === $key ) {
					self::update_post_meta( $product_id, '_bkap_multiple_day_min', $value );
					$woocommerce_booking_settings['booking_minimum_number_days_multiple'] = $value;
					$woocommerce_booking_settings['enable_minimum_day_booking_multiple']  = (int) $value > 0 ? 'on' : '';
				}

				if ( 'booking_maximum_number_days_multiple' === $key ) {
					$value = $value > 0 ? $value : 365;
					self::update_post_meta( $product_id, '_bkap_multiple_day_max', $value );
					$woocommerce_booking_settings['booking_maximum_number_days_multiple'] = $value;
				}

				if ( 'enable_specific_booking' === $key ) {
					self::update_post_meta( $product_id, '_bkap_enable_specific', $value );
					$woocommerce_booking_settings['booking_specific_booking'] = $value;
				}

				// Duration Booking.
				if ( 'duration_based_bookings' === $key && is_array( $value ) && count( $value ) > 0 ) {
					self::update_post_meta( $product_id, '_bkap_duration_settings', $value );
					$woocommerce_booking_settings['bkap_duration_settings'] = $value;
				}

				// Block Pricing.
				if ( 'block_pricing_type' === $key ) {

					$value_fixed_blocks = 'booking_fixed_block_enable' === $value ? $value : '';
					$value_price_ranges = 'booking_block_price_enable' === $value ? $value : '';

					self::update_post_meta( $product_id, '_bkap_fixed_blocks', $value_fixed_blocks );
					self::update_post_meta( $product_id, '_bkap_price_ranges', $value_price_ranges );
					$woocommerce_booking_settings['booking_fixed_block_enable'] = $value_fixed_blocks;
					$woocommerce_booking_settings['booking_block_price_enable'] = $value_price_ranges;
				}

				// Persons.
				if ( 'bkap_person' === $key ) {
					self::update_post_meta( $product_id, '_bkap_person', $value );
					$woocommerce_booking_settings['bkap_person'] = $value;
				}

				if ( 'bkap_min_person' === $key ) {
					self::update_post_meta( $product_id, '_bkap_min_person', $value );
					$woocommerce_booking_settings['bkap_min_person'] = $value;
				}

				if ( 'bkap_max_person' === $key ) {
					self::update_post_meta( $product_id, '_bkap_max_person', $value );
					$woocommerce_booking_settings['bkap_max_person'] = $value;
				}

				if ( 'bkap_price_per_person' === $key ) {
					self::update_post_meta( $product_id, '_bkap_price_per_person', $value );
					$woocommerce_booking_settings['bkap_price_per_person'] = $value;
				}

				if ( 'bkap_each_person_booking' === $key ) {
					self::update_post_meta( $product_id, '_bkap_each_person_booking', $value );
					$woocommerce_booking_settings['bkap_each_person_booking'] = $value;
				}

				if ( 'bkap_person_type' === $key ) {
					self::update_post_meta( $product_id, '_bkap_person_type', $value );
					$woocommerce_booking_settings['bkap_person_type'] = $value;
				}

				if ( 'bkap_resource' === $key ) {
					self::update_post_meta( $product_id, '_bkap_resource', $value );
					$woocommerce_booking_settings['_bkap_resource'] = $value;
				}

				if ( 'resource_label' === $key ) {
					self::update_post_meta( $product_id, '_bkap_product_resource_lable', $value );
					$woocommerce_booking_settings['_bkap_product_resource_lable'] = $value;
				}

				if ( 'resource_assignment' === $key ) {
					self::update_post_meta( $product_id, '_bkap_product_resource_selection', $value );
					$woocommerce_booking_settings['_bkap_product_resource_selection'] = $value;
				}

				if ( 'resource_position' === $key ) {
					self::update_post_meta( $product_id, '_bkap_product_resource_position', $value );
					$woocommerce_booking_settings['_bkap_product_resource_position'] = $value;
				}

				if ( 'resource_selection' === $key ) {
					self::update_post_meta( $product_id, '_bkap_product_resource_selection_type', $value );
					$woocommerce_booking_settings['_bkap_product_resource_selection_type'] = $value;
				}

				if ( 'consider_product_max_booking' === $key ) {
					self::update_post_meta( $product_id, '_bkap_product_resource_max_booking', $value );
					$woocommerce_booking_settings['_bkap_product_resource_max_booking'] = $value;
				}

				if ( 'resource_sort_option' === $key ) {
					self::update_post_meta( $product_id, '_bkap_product_resource_sorting', $value );
					$woocommerce_booking_settings['_bkap_product_resource_sorting'] = $value;
				}

				// Integrations.
				if ( 'product_sync_integration_mode' === $key ) {
					self::update_post_meta( $product_id, '_bkap_gcal_integration_mode', $value );
					$woocommerce_booking_settings['product_sync_integration_mode'] = $value;
				}

				if ( 'bkap_calendar_oauth_integration' === $key ) {
					$bkap_calendar_oauth_integration = array(
						'client_id'     => self::check( $value, 'client_id', '' ),
						'client_secret' => self::check( $value, 'client_secret', '' ),
						'calendar_id'   => self::check( $value, 'calendar_id', '' ),
					);

					self::update_post_meta( $product_id, '_bkap_calendar_oauth_integration', $bkap_calendar_oauth_integration );
					$woocommerce_booking_settings['bkap_calendar_oauth_integration'] = $bkap_calendar_oauth_integration;
				}

				if ( 'bkap_calendar_directly_integration' === $key ) {
					self::update_post_meta( $product_id, '_bkap_gcal_key_file_name', self::check( $value, 'key_file_name', '' ) );
					$woocommerce_booking_settings['product_sync_key_file_name'] = self::check( $value, 'key_file_name', '' );

					self::update_post_meta( $product_id, '_bkap_gcal_service_acc', self::check( $value, 'email_address', '' ) );
					$woocommerce_booking_settings['product_sync_service_acc_email_addr'] = self::check( $value, 'email_address', '' );

					self::update_post_meta( $product_id, '_bkap_gcal_calendar_id', self::check( $value, 'calendar_id', '' ) );
					$woocommerce_booking_settings['product_sync_calendar_id'] = self::check( $value, 'calendar_id', '' );
				}

				if ( 'enable_automated_mapping' === $key ) {
					self::update_post_meta( $product_id, '_bkap_enable_automated_mapping', $value );
					$woocommerce_booking_settings['enable_automated_mapping'] = $value;
				}

				if ( 'default_variation_id_for_events' === $key ) {
					self::update_post_meta( $product_id, '_bkap_default_variation', $value );
					$woocommerce_booking_settings['gcal_default_variation'] = $value;
				}

				if ( 'bkap_outlook_calendar_integration' === $key ) {
					self::update_post_meta( $product_id, '_bkap_outlook_calendar', self::check( $value, 'is_enabled', '' ) );
					$woocommerce_booking_settings['bkap_outlook_calendar'] = self::check( $value, 'is_enabled', '' );

					self::update_post_meta( $product_id, '_bkap_outlook_calendar_client_id', self::check( $value, 'client_id', '' ) );
					$woocommerce_booking_settings['bkap_outlook_calendar_client_id'] = self::check( $value, 'client_id', '' );

					self::update_post_meta( $product_id, '_bkap_outlook_calendar_client_secret', self::check( $value, 'client_secret', '' ) );
					$woocommerce_booking_settings['bkap_outlook_calendar_client_secret'] = self::check( $value, 'client_secret', '' );

					self::update_post_meta( $product_id, '_bkap_outlook_calendar_id', self::check( $value, 'client_id', '' ) );
					$woocommerce_booking_settings['bkap_outlook_calendar_id'] = self::check( $value, 'client_id', '' );
				}

				if ( 'bkap_fluentcrm_integration' === $key ) {
					self::update_post_meta( $product_id, '_bkap_fluentcrm', self::check( $value, 'is_enabled', '' ) );
					$woocommerce_booking_settings['bkap_fluentcrm'] = self::check( $value, 'is_enabled', '' );

					self::update_post_meta( $product_id, '_bkap_fluentcrm_list', self::check( $value, 'list', '' ) );
					$woocommerce_booking_settings['bkap_fluentcrm_list'] = self::check( $value, 'list', '' );
				}

				if ( 'manage_availability_all_data' === $key ) {
					self::update_post_meta( $product_id, '_bkap_all_data_unavailable', $value );
					$woocommerce_booking_settings['bkap_all_data_unavailable'] = $value;
				}

				if ( 'bkap_zapier_integration' === $key ) {
					$settings = array(
						'trigger_create_booking' => array(
							'status' => self::check( $value, 'create_booking_trigger_status', '' ),
							'label'  => self::check( $value, 'create_booking_trigger_label', '' ),
						),
						'trigger_update_booking' => array(
							'status' => self::check( $value, 'update_booking_trigger_status', '' ),
							'label'  => self::check( $value, 'update_booking_trigger_label', '' ),
						),
						'trigger_delete_booking' => array(
							'status' => self::check( $value, 'delete_booking_trigger_status', '' ),
							'label'  => self::check( $value, 'delete_booking_trigger_label', '' ),
						),
					);

					self::update_post_meta( $product_id, BKAP_Zapier::$product_settings_key, $settings );
					$woocommerce_booking_settings[ 'booking_' . str_replace( '_bkap_', '', BKAP_Zapier::$product_settings_key ) ] = $settings;
				}

				if ( 'bkap_zoom_integration' === $key ) {
					self::update_post_meta( $product_id, '_bkap_zoom_meeting', self::check( $value, 'is_enabled', '' ) );
					$woocommerce_booking_settings['zoom_meeting'] = self::check( $value, 'is_enabled', '' );

					self::update_post_meta( $product_id, '_bkap_zoom_meeting_host', self::check( $value, 'host', '' ) );
					$woocommerce_booking_settings['zoom_meeting_host'] = self::check( $value, 'host', '' );

					self::update_post_meta( $product_id, '_bkap_zoom_meeting_auth', self::check( $value, 'meeting_authentication', '' ) );
					$woocommerce_booking_settings['zoom_meeting_auth'] = self::check( $value, 'meeting_authentication', '' );

					self::update_post_meta( $product_id, '_bkap_zoom_meeting_join_before_host', self::check( $value, 'join_before_host', '' ) );
					$woocommerce_booking_settings['zoom_meeting_join_before_host'] = self::check( $value, 'join_before_host', '' );

					self::update_post_meta( $product_id, '_bkap_zoom_meeting_host_video', self::check( $value, 'host_video', '' ) );
					$woocommerce_booking_settings['zoom_meeting_host_video'] = self::check( $value, 'host_video', '' );

					self::update_post_meta( $product_id, '_bkap_zoom_meeting_participant_video', self::check( $value, 'participant_video', '' ) );
					$woocommerce_booking_settings['zoom_meeting_participant_video'] = self::check( $value, 'participant_video', '' );

					self::update_post_meta( $product_id, '_bkap_zoom_meeting_mute_upon_entry', self::check( $value, 'mute_upon_entry', '' ) );
					$woocommerce_booking_settings['zoom_meeting_mute_upon_entry'] = self::check( $value, 'mute_upon_entry', '' );

					self::update_post_meta( $product_id, '_bkap_zoom_meeting_auto_recording', self::check( $value, 'auto_recording', '' ) );
					$woocommerce_booking_settings['zoom_meeting_auto_recording'] = self::check( $value, 'auto_recording', '' );

					self::update_post_meta( $product_id, '_bkap_zoom_meeting_alternative_host', self::check( $value, 'alternative_hosts', '' ) );
					$woocommerce_booking_settings['zoom_meeting_alternative_host'] = self::check( $value, 'alternative_hosts', '' );
				}

				if ( 'data' === $key ) {

					if ( isset( $data['data']['weekday_settings'] ) || isset( $data['data']['availability'] ) || isset( $data['data']['weekdays_dates_timeslots'] ) || isset( $data['data']['manage_availability'] ) ) {

						$booking_recurring = array();
						$recurring_lockout = array();
						$recurring_prices  = array();
						$custom_range      = array();
						$range_of_months   = array();
						$holiday_ranges    = array();
						$specific_dates    = array();
						$specific_prices   = array();
						$product_holidays  = array();

						// Special Price.
						$_map_special_prices     = array();
						$existing_special_prices = get_post_meta( $product_id, '_bkap_special_price', true );
						$existing_special_prices = ( '' === $existing_special_prices || ! is_array( $existing_special_prices ) || ( is_array( $existing_special_prices ) && 0 === count( $existing_special_prices ) ) ) ? array() : $existing_special_prices;

						// Loop through the existing records, note down the weekday/date and the key.
						if ( count( $existing_special_prices ) > 0 ) {
							foreach ( $existing_special_prices as $key => $value ) {
								$weekday = $value['booking_special_weekday'];
								$date    = $value['booking_special_date'];

								if ( '' !== $weekday ) {
									$_map_special_prices[ $weekday ] = $key;
								} elseif ( '' !== $date ) {
									$_map_special_prices[ $date ] = $key;
								}
							}
						}

						if ( isset( $data['data']['weekday_settings'] ) && is_array( $data['data']['weekday_settings'] ) && count( $data['data']['weekday_settings'] ) > 0 ) {
							foreach ( $data['data']['weekday_settings'] as $_key => $_value ) {
								$booking_recurring[ $_key ] = self::check( $_value, 'status' );
								$recurring_lockout[ $_key ] = self::check( $_value, 'lockout' );
								$price                      = self::check( $_value, 'price' );
								$recurring_prices[ $_key ]  = $price;
							}

							if ( count( $recurring_prices ) > 0 ) {
								foreach ( $recurring_prices as $key => $price ) {

									$new_data = array(
										'booking_special_weekday' => $key,
										'booking_special_date' => '',
										'booking_special_price' => $price,
									);

									if ( isset( $_map_special_prices[ $key ] ) ) {
										$existing_special_prices[ $_map_special_prices[ $key ] ] = $new_data;
									} else {
										$existing_special_prices[] = $new_data;
									}
								}
							}

							self::update_post_meta( $product_id, '_bkap_enable_recurring', ( in_array( 'on', $booking_recurring ) ? 'on' : '' ) );
							$woocommerce_booking_settings['booking_recurring_booking'] = in_array( 'on', $booking_recurring ) ? 'on' : '';

							self::update_post_meta( $product_id, '_bkap_recurring_weekdays', $booking_recurring );
							$woocommerce_booking_settings['booking_recurring'] = $booking_recurring;

							self::update_post_meta( $product_id, '_bkap_recurring_lockout', $recurring_lockout );
							$woocommerce_booking_settings['booking_recurring_lockout'] = $recurring_lockout;
						}

						if ( isset( $data['data']['availability'] ) && is_array( $data['data']['availability'] ) && count( $data['data']['availability'] ) > 0 ) {

							$current_year = gmdate( 'Y', current_time( 'timestamp' ) );
							$next_year    = gmdate( 'Y', strtotime( '+1 year' ) );

							foreach ( $data['data']['availability'] as $availability ) {

								$range_type  = $availability['range_type'];
								$is_bookable = self::check( $availability, 'bookable' );

								if ( in_array( $range_type, array( 'custom_range', 'range_of_months' ) ) ) {

									$from            = self::check( $availability, $range_type . '_from' );
									$to              = self::check( $availability, $range_type . '_to' );
									$number_of_years = self::check( $availability, $range_type . '_number_of_years', '' );

									if ( '' === $from || '' === $to ) {
										continue;
									}

									if ( 'range_of_months' === $range_type ) {

										$_from = $from;
										$_to   = $to;

										$from = gmdate( 'j-n-Y', strtotime( $from ) );
										$to   = gmdate( 'j-n-Y', strtotime( $to ) );

										if ( is_numeric( $_from ) ) {

											// it's a month number.
											$month = $bkap_months[ $_from ];
											$from  = "$month $current_year";
											$from  = gmdate( 'j-n-Y', strtotime( $from ) );
										}

										if ( is_numeric( $_to ) ) {

											// it's a month number.
											$month       = $bkap_months[ $_to ];
											$month_use   = $_from <= $_to ? "$month $current_year" : "$month $next_year";
											$month_start = gmdate( 'j-n-Y', strtotime( $month_use ) );
											$days        = gmdate( 't', strtotime( $month_start ) );
											$days       -= 1;
											$to          = gmdate( 'j-n-Y', strtotime( "+$days days", strtotime( $month_start ) ) );
										}
									}

									if ( 'custom_range' == $range_type ) {
										$from = gmdate( 'j-n-Y', strtotime( $from ) );
										$to   = gmdate( 'j-n-Y', strtotime( $to ) );
									}

									$range = array(
										'start' => $from,
										'end'   => $to,
									);

									//if ( '' !== $number_of_years ) {
										$range['years_to_recur'] = $number_of_years;
									//}

									if ( 'on' === $is_bookable ) {
										array_push( $$range_type, $range );
									} else {
										$range['range_type'] = $range_type;
										array_push( $holiday_ranges, $range );
									}
								}

								if ( in_array( $range_type, array( 'specific_dates', 'holidays' ), true ) ) {

									$dates                    = isset( $availability['specific_dates_date'] ) && '' !== $availability['specific_dates_date'] ? $availability['specific_dates_date'] : $availability['holidays_date'];
									$holidays_number_of_years = isset( $availability['holidays_number_of_years'] ) ? $availability['holidays_number_of_years'] : '';
									$number_of_years          = isset( $availability['specific_dates_max_bookings'] ) && '' !== $availability['specific_dates_max_bookings'] ? $availability['specific_dates_max_bookings'] : $holidays_number_of_years;
									$price                    = isset( $availability['specific_dates_price'] ) && '' !== $availability['specific_dates_price'] ? $availability['specific_dates_price'] : '';

									$dates = explode( ',', $dates );

									foreach ( $dates as $date ) {
										if ( '' === $date ) {
											continue;
										}
										// Replace invalid characters from dates.
										$date = preg_replace( '/[^0-9-]/', '', $date );

										if ( 'on' === $is_bookable ) {
											$specific_dates[ $date ] = isset( $availability['specific_dates_max_bookings'] ) ? $availability['specific_dates_max_bookings'] : 0;
										} else {
											$product_holidays[ $date ] = isset( $availability['holidays_number_of_years'] ) ? 0 : $number_of_years;
										}

										if ( '' !== $price && $price > 0 ) {
											$specific_prices[ $date ] = $price;
										}
									}
								}
							}

							if ( count( $specific_prices ) > 0 ) {
								foreach ( $specific_prices as $key => $price ) {

									$key      = gmdate( 'Y-m-d', strtotime( $key ) );
									$new_data = array(
										'booking_special_weekday' => '',
										'booking_special_date' => $key,
										'booking_special_price' => $price,
									);

									if ( isset( $_map_special_prices[ $key ] ) ) {
										$existing_special_prices[ $_map_special_prices[ $key ] ] = $new_data;
									} else {
										$existing_special_prices[] = $new_data;
									}
								}
							}

							if ( count( $_map_special_prices ) > 0 ) {
								foreach ( $_map_special_prices as $key => $value ) {
									if ( 'booking' === substr( $key, 0, 7 ) ) {
										if ( ! array_key_exists( $key, $recurring_prices ) ) {
											unset( $existing_special_prices[ $value ] );
										}
									} else {
										// it's a specific date.
										$key = gmdate( 'j-n-Y', strtotime( $key ) );
										if ( ! array_key_exists( $key, $specific_prices ) ) {
											unset( $existing_special_prices[ $value ] );
										}
									}
								}
							}
						}

						self::update_post_meta( $product_id, '_bkap_custom_ranges', $custom_range );
						$woocommerce_booking_settings['booking_date_range'] = $custom_range;

						self::update_post_meta( $product_id, '_bkap_month_ranges', $range_of_months );
						self::update_post_meta( $product_id, '_bkap_holiday_ranges', $holiday_ranges );

						self::update_post_meta( $product_id, '_bkap_product_holidays', $product_holidays );
						$woocommerce_booking_settings['booking_product_holiday'] = $product_holidays;

						self::update_post_meta( $product_id, '_bkap_specific_dates', $specific_dates );
						$woocommerce_booking_settings['booking_specific_date'] = $specific_dates;

						self::update_post_meta( $product_id, '_bkap_special_price', $existing_special_prices );

						if ( isset( $data['data']['weekdays_dates_timeslots'] ) && is_array( $data['data']['weekdays_dates_timeslots'] ) && count( $data['data']['weekdays_dates_timeslots'] ) > 0 ) {

							$_booking_time_settings = array();
							$booking_time_settings  = get_post_meta( $product_id, '_bkap_time_settings', true );
							$recurring_weekdays     = get_post_meta( $product_id, '_bkap_recurring_weekdays', true );
							$specific_dates         = get_post_meta( $product_id, '_bkap_specific_dates', true );

							foreach ( $data['data']['weekdays_dates_timeslots'] as $value ) {

								$global   = self::check( $value, 'global' );
								$weekdays = self::check( $value, 'weekday' );
								$from     = self::check( $value, 'from' );
								$to       = self::check( $value, 'to' );
								$lockout  = self::check( $value, 'lockout' );
								$price    = self::check( $value, 'price' );
								$note     = self::check( $value, 'note' );
								$original = self::check( $value, 'og_data', array() );

								if ( ! empty( $original ) ) {
									if ( $original['from'] !== $from || $original['to'] !== $to ) {
										self::update_weekdays_dates_timeslot( $product_id, $value, $original );
									}
								}

								// skip if some required values are empty.
								if ( '' === $weekdays || '' === $from ) {
									continue;
								}

								if ( ! is_array( $weekdays ) ) {
									$weekdays = array( $weekdays );
								}

								if ( in_array( 'all', $weekdays ) ) {

									if ( ( $all = array_search( 'all', $weekdays ) ) !== false ) {
										unset( $weekdays[ $all ] );
									}

									// add records for all the days/dates.
									if ( is_array( $recurring_weekdays ) && count( $recurring_weekdays ) > 0 ) {
										foreach ( $recurring_weekdays as $_key => $_value ) {
											if ( 'on' === $_value || apply_filters( 'bkap_allow_to_add_timeslots_for_weekday', false, $product_id, $data ) ) {
												$weekdays[] = $_key;
											}
										}
									}

									// add records for all the specific dates.
									if ( is_array( $specific_dates ) && count( $specific_dates ) > 0 ) {
										foreach ( $specific_dates as $_key => $_value ) {
											$weekdays[] = $_key;
										}
									}
								}

								$weekdays = array_unique( $weekdays );

								foreach ( $weekdays as $weekday ) {

									if ( 'all' === $weekday ) {
										continue;
									}

									$exp_from      = explode( ':', $from );
									$from_slot_hrs = '' !== $exp_from[0] ? $exp_from[0] : '00';
									$from_slot_min = isset( $exp_from[1] ) && '' !== $exp_from[1] ? $exp_from[1] : '00';

									$exp_to      = explode( ':', $to );
									$to_slot_hrs = '' !== $exp_to[0] ? $exp_to[0] : '00';
									$to_slot_min = isset( $exp_to[1] ) && '' !== $exp_to[1] ? $exp_to[1] : '00';

									// check if there's a record present for that day/date.
									$new_key           = 0;
									$is_record_found   = false;
									$selected_settings = self::check( $booking_time_settings, $weekday, array() );

									if ( is_array( $selected_settings ) && count( $selected_settings ) > 0 ) {
										foreach ( $selected_settings  as $_key => $_value ) {
											if ( $from_slot_hrs === $_value['from_slot_hrs'] && $from_slot_min === $_value['from_slot_min'] && $to_slot_hrs === $_value['to_slot_hrs'] && $to_slot_min === $_value['to_slot_min'] ) {
												$new_key         = $_key;
												$is_record_found = true;
											}
										}
									}

									if ( ! $is_record_found && isset( $_booking_time_settings[ $weekday ] ) ) {
										$new_key = max( array_keys( $_booking_time_settings[ $weekday ] ) ) + 1;
									}

									$_booking_time_settings[ $weekday ][ $new_key ] = array(
										'from_slot_hrs' => $from_slot_hrs,
										'from_slot_min' => $from_slot_min,
										'to_slot_hrs'   => $to_slot_hrs,
										'to_slot_min'   => $to_slot_min,
										'booking_notes' => $note,
										'slot_price'    => $price,
										'lockout_slot'  => $lockout,
										'global_time_check' => $global,
									);
								}
							}

							if ( count( $_booking_time_settings ) > 0 ) {
								foreach ( $_booking_time_settings as $key => $value ) {
									$value = array_map( 'unserialize', array_unique( array_map( 'serialize', $value ) ) );
									$_booking_time_settings[$key] = $value;
								}
								self::update_post_meta( $product_id, '_bkap_time_settings', $_booking_time_settings );
								$woocommerce_booking_settings['booking_time_settings'] = $_booking_time_settings;
							}
						}

						if ( isset( $data['data']['manage_availability'] ) ) {
							$manage_availability_data = array();

							if ( is_array( $data['data']['manage_availability'] ) && count( $data['data']['manage_availability'] ) > 0 ) {
								foreach ( $data['data']['manage_availability'] as $value ) {
									$_data = array(
										'type'     => $value['range_type'],
										'bookable' => 'on' === $value['bookable'] ? 1 : 0,
										'priority' => $value['priority'],
									);

									switch ( $value['range_type'] ) {
										case 'custom':
											$_data['from'] = $value['range_date_from'];
											$_data['to']   = $value['range_date_to'];
											break;

										case 'time:range':
											$_data['from']      = $value['range_time_range_time_from'];
											$_data['from_date'] = $value['range_time_range_date_from'];
											$_data['to']        = $value['range_time_range_time_to'];
											$_data['to_date']   = $value['range_time_range_date_to'];
											break;

										case 'days':
										case 'months':
										case 'weeks':
											$_data['from'] = $value[ 'range_' . $value['range_type'] . '_from' ];
											$_data['to']   = $value[ 'range_' . $value['range_type'] . '_to' ];
											break;

										default:
											if ( 'time:' === substr( $value['range_type'], 0, 5 ) || 'time' === $value['range_type'] ) {
												$_data['from'] = $value['range_time_from'];
												$_data['to']   = $value['range_time_to'];
											}
											break;
									}

									$manage_availability_data[] = $_data;
								}
							}

							self::update_post_meta( $product_id, '_bkap_manage_time_availability', $manage_availability_data );
							$woocommerce_booking_settings['bkap_manage_time_availability'] = $manage_availability_data;
						}
					}

					if ( isset( $data['data']['fixed_block'] ) ) {

						$fixed_block_data = array();

						foreach ( $data['data']['fixed_block'] as $_value ) {

							if ( isset( $_value['edit'] ) ) {
								unset( $_value['edit'] );
							}

							if ( isset( $_value['is_new_row'] ) ) {
								unset( $_value['is_new_row'] );
							}

							$fixed_block_data[] = $_value;
						}

						self::update_post_meta( $product_id, '_bkap_fixed_blocks_data', $fixed_block_data );
						$woocommerce_booking_settings['bkap_fixed_blocks_data'] = $fixed_block_data;
					}

					if ( isset( $data['data']['price_by_range_of_nights'] ) ) {

						$price_range_data = array();

						foreach ( $data['data']['price_by_range_of_nights'] as $_value ) {

							if ( isset( $_value['edit'] ) ) {
								unset( $_value['edit'] );
							}

							if ( isset( $_value['is_new_row'] ) ) {
								unset( $_value['is_new_row'] );
							}

							$price_range_data[] = $_value;
						}

						self::update_post_meta( $product_id, '_bkap_price_range_data', $price_range_data );
						$woocommerce_booking_settings['bkap_price_range_data'] = $price_range_data;
					}

					if ( isset( $data['data']['person_settings'] ) ) {

						$person_ids  = array();
						$person_data = array();

						foreach ( $data['data']['person_settings'] as $_value ) {
							$person_id = $_value['person_id'];

							if ( '' === $person_id ) {
								$person_id = wp_insert_post(
									array(
										'post_title'   => $_value['person_title'],
										'menu_order'   => 0,
										'post_content' => '',
										'post_status'  => 'publish',
										'post_author'  => get_current_user_id(),
										'post_type'    => 'bkap_person',
									),
									true
								);
							} else {
								wp_update_post(
									array(
										'ID'         => $person_id,
										'post_title' => $_value['person_title'],
									)
								);
							}

							$person_ids[]              = $person_id;
							$person_data[ $person_id ] = array(
								'base_cost'   => $_value['base_cost'],
								'block_cost'  => $_value['block_cost'],
								'person_name' => $_value['person_title'],
								'person_min'  => $_value['person_min'],
								'person_max'  => $_value['person_max'],
								'person_desc' => $_value['person_desc'],
							);
						}

						$woocommerce_booking_settings['bkap_person_data'] = $person_data;
						$woocommerce_booking_settings['bkap_person_ids']  = $person_ids;

						self::update_post_meta( $product_id, '_bkap_person_data', $person_data );
						self::update_post_meta( $product_id, '_bkap_person_ids', $person_ids );
					}

					if ( isset( $data['data']['resource_settings'] ) ) {

						$resource_ids   = array();
						$resource_costs = array();

						foreach ( $data['data']['resource_settings'] as $_value ) {
							$resource_id = $_value['resource_id'];

							if ( 'new_resource' === $resource_id && '' !== $_value['resource_title'] ) {
								$resource_id = wp_insert_post(
									array(
										'post_title'   => $_value['resource_title'],
										'menu_order'   => 0,
										'post_content' => '',
										'post_status'  => 'publish',
										'post_author'  => get_current_user_id(),
										'post_type'    => 'bkap_resource',
									),
									true
								);

								if ( $resource_id && ! is_wp_error( $resource_id ) ) {
									self::update_post_meta( $resource_id, '_bkap_resource_qty', 1 );
									self::update_post_meta( $resource_id, '_bkap_resource_menu_order', 0 );
									self::update_post_meta( $resource_id, '_bkap_resource_availability', array() );
								}
							}

							$resource_ids[]                 = $resource_id;
							$resource_costs[ $resource_id ] = $_value['base_cost'];
						}

						$woocommerce_booking_settings['_bkap_product_resources']   = $resource_ids;
						$woocommerce_booking_settings['_bkap_resource_base_costs'] = $resource_costs;

						self::update_post_meta( $product_id, '_bkap_product_resources', $resource_ids );
						self::update_post_meta( $product_id, '_bkap_resource_base_costs', $resource_costs );
					}
				}

				/* Rental Addon settings save */
				if ( 'booking_charge_per_day' === $key ) {
					self::update_post_meta( $product_id, '_bkap_booking_charge_per_day', $value );
					$woocommerce_booking_settings['booking_charge_per_day'] = $value;
				}

				if ( 'booking_later_days_to_book' === $key ) {
					self::update_post_meta( $product_id, '_bkap_booking_later_days_to_book', $value );
					$woocommerce_booking_settings['booking_later_days_to_book'] = $value;
				}

				if ( 'booking_prior_days_to_book' === $key ) {
					self::update_post_meta( $product_id, '_bkap_booking_prior_days_to_book', $value );
					$woocommerce_booking_settings['booking_prior_days_to_book'] = $value;
				}

				if ( 'booking_same_day' === $key ) {
					self::update_post_meta( $product_id, '_bkap_booking_same_day', $value );
					$woocommerce_booking_settings['booking_same_day'] = $value;
				}

				if ( 'bkap_show_mode' === $key ) {
					self::update_post_meta( $product_id, '_bkap_bkap_show_mode', $value );
					$woocommerce_booking_settings['bkap_show_mode'] = $value;
				}

				if ( 'bkap_purchase_mode' === $key ) {
					switch ( $value ) {
						case 'sale':
							$woocommerce_booking_settings['bkap_purchase_mode'] = 'sale';
							break;
						case 'rent':
							$woocommerce_booking_settings['bkap_purchase_mode'] = 'rent';
							break;
						case 'both':
							$woocommerce_booking_settings['bkap_purchase_mode'] = $data['bkap_default_mode'];
							break;
					}
				}

				/* Partial Deposits Settings Save */

				if ( 'booking_partial_payment_enable' === $key ) {
					self::update_post_meta( $product_id, '_bkap_booking_partial_payment_enable', $value );
					$woocommerce_booking_settings['booking_partial_payment_enable'] = $value;
				}

				if ( 'booking_partial_payment_radio' === $key ) {
					self::update_post_meta( $product_id, '_bkap_booking_partial_payment_radio', $value );
					$woocommerce_booking_settings['booking_partial_payment_radio'] = $value;
				}

				if ( 'booking_partial_payment_value_deposit' === $key ) {
					self::update_post_meta( $product_id, '_bkap_booking_partial_payment_value_deposit', $value );
					$woocommerce_booking_settings['booking_partial_payment_value_deposit'] = $value;
				}

				if ( 'allow_full_payment' === $key ) {
					self::update_post_meta( $product_id, '_bkap_allow_full_payment', $value );
					$woocommerce_booking_settings['allow_full_payment'] = $value;
				}

				if ( 'booking_deposit_x_days' === $key ) {
					self::update_post_meta( $product_id, '_bkap_booking_deposit_x_days', $value );
					$woocommerce_booking_settings['booking_deposit_x_days'] = $value;
				}

				if ( 'booking_default_payment_radio' === $key ) {
					self::update_post_meta( $product_id, '_bkap_booking_default_payment_radio', $value );
					$woocommerce_booking_settings['booking_default_payment_radio'] = $value;
				}

				/* Multiple Timeslots Addon Settings Save */
				if ( 'booking_enable_multiple_time' === $key ) {
					self::update_post_meta( $product_id, '_bkap_booking_enable_multiple_time', $value );
					$woocommerce_booking_settings['booking_enable_multiple_time'] = $value;
				}

				/* Seasonal Pricing Addon Settings Save */
				if ( 'booking_seasonal_pricing_enable' === $key ) {
					self::update_post_meta( $product_id, '_bkap_booking_seasonal_pricing_enable', $value );
					$woocommerce_booking_settings['booking_seasonal_pricing_enable'] = $value;

					if ( 'yes' === $value ) {
						if ( class_exists( 'Bkap_Seasonal_Pricing' ) ) {
							bkap_seasonal_pricing()->bkap_add_missing_global_seasons_to_product( $product_id );
						}
					}
				}
			}

			update_post_meta( $product_id, 'woocommerce_booking_settings', $woocommerce_booking_settings );
			self::update_booking_history_table( $product_id );

			if ( $save_settings_as_default ) {
				update_option( 'bkap_default_booking_settings', $woocommerce_booking_settings );
				update_option( 'bkap_default_individual_booking_settings', self::$all_settings );
			}

			$post     = new stdClass();
			$post->ID = $product_id;

			return self::response(
				'success',
				array(
					'data'    => self::fetch_metabox_booking_data( true, $post ),
					'message' => __(
						'Settings have been saved.',
						'woocommerce-booking'
					),
				)
			);
		}

		return self::response( 'error', array( 'error_description' => 'Unknown Error' ) );
	}

	/**
	 * Returns the Availability Data for the Booking Metabox.
	 *
	 * @param array $booking_settings Array of Booking Settings.
	 * @param array $data Data.
	 *
	 * @since 5.19.0
	 */
	public static function return_availability_data( $booking_settings, $data ) {
		global $bkap_months;

		$availability_data = array();
		$product_id        = self::check( $booking_settings, 'product_id', '' );
		$booking_type      = self::check( $data, 'booking_type', array() );
		$custom_ranges     = self::check( $data, 'custom_ranges', array() );
		$holiday_ranges    = self::check( $data, 'holiday_ranges', array() );
		$month_ranges      = self::check( $data, 'month_ranges', array() );
		$specific_dates    = self::check( $data, 'specific_dates', array() );
		$special_prices    = self::check( $data, 'special_prices', array() );
		$holidays          = self::check( $booking_settings, 'booking_product_holiday', array() );

		// Sort holidays in chronological order.
		if ( is_array( $holidays ) && count( $holidays ) > 0 ) {
			uksort( $holidays, 'bkap_orderby_date_key' );
		}

		// Modify the special prices array.
		if ( is_array( $special_prices ) && count( $special_prices ) > 0 ) {

			$_special_prices = array();

			foreach ( $special_prices as $key => $value ) {
				if ( isset( $value['booking_special_date'] ) && '' !== $value['booking_special_date'] ) {
					$date                     = gmdate( 'j-n-Y', strtotime( $value['booking_special_date'] ) );
					$_special_prices[ $date ] = $value['booking_special_price'];
				}
			}

			$special_prices = $_special_prices;
		}

		if ( is_array( $custom_ranges ) && count( $custom_ranges ) > 0 ) {
			foreach ( $custom_ranges as $key => $value ) {
				$availability_data[] = array(
					'range_type'                   => 'custom_range',
					'edit'                         => false,
					'custom_range_from'            => self::check( $value, 'start', '' ),
					'custom_range_to'              => self::check( $value, 'end', '' ),
					'custom_range_number_of_years' => self::check( $value, 'years_to_recur', '' ),
					'bookable'                     => 'on',
					'is_editable'                  => true,
				);
			}
		}

		if ( is_array( $holiday_ranges ) && count( $holiday_ranges ) > 0 ) {

			foreach ( $holiday_ranges as $key => $value ) {

				$data = array( 'product_id' => $product_id );

				$start_date_of_month = gmdate( '1-n-Y', strtotime( $value['start'] ) );
				$end_date_of_month   = gmdate( 't-n-Y', strtotime( $value['end'] ) );

				$data['edit']        = false;
				$data['bookable']    = '';
				$data['is_editable'] = true;

				switch ( $value['range_type'] ) {
					case 'range_of_months':
						$data['range_type']                      = 'range_of_months';
						$data['range_of_months_from']            = array_search( gmdate( 'F', strtotime( $value['start'] ) ), $bkap_months );
						$data['range_of_months_to']              = array_search( gmdate( 'F', strtotime( $value['end'] ) ), $bkap_months );
						$data['range_of_months_number_of_years'] = self::check( $value, 'years_to_recur', '' );
						break;
					case 'custom_range':
						$data['range_type']                   = 'custom_range';
						$data['custom_range_from']            = $value['start'];
						$data['custom_range_to']              = $value['end'];
						$data['custom_range_number_of_years'] = self::check( $value, 'years_to_recur', '' );
						break;
				}

				$availability_data[] = $data;
				/* 

				if ( $value['start'] === $start_date_of_month && $value['end'] === $end_date_of_month ) {
					$data['range_type']                      = 'range_of_months';
					$data['range_of_months_from']            = array_search( gmdate( 'F', strtotime( $value['start'] ) ), $bkap_months );
					$data['range_of_months_to']              = array_search( gmdate( 'F', strtotime( $value['end'] ) ), $bkap_months );
					$data['range_of_months_number_of_years'] = self::check( $value, 'years_to_recur', '' );
				} else {
					$data['range_type']                   = 'custom_range';
					$data['custom_range_from']            = $value['start'];
					$data['custom_range_to']              = $value['end'];
					$data['custom_range_number_of_years'] = self::check( $value, 'years_to_recur', '' );
				}

				$availability_data[] = $data; */
			}
		}

		if ( is_array( $month_ranges ) && count( $month_ranges ) > 0 ) {
			foreach ( $month_ranges as $key => $value ) {
				$availability_data[] = array(
					'range_type'                      => 'range_of_months',
					'edit'                            => false,
					'range_of_months_from'            => array_search( gmdate( 'F', strtotime( $value['start'] ) ), $bkap_months ),
					'range_of_months_to'              => array_search( gmdate( 'F', strtotime( $value['end'] ) ), $bkap_months ),
					'range_of_months_number_of_years' => self::check( $value, 'years_to_recur', '' ),
					'bookable'                        => 'on',
					'is_editable'                     => true,
				);
			}
		}

		if ( is_array( $specific_dates ) && count( $specific_dates ) > 0 ) {
			foreach ( $specific_dates as $key => $value ) {
				$availability_data[] = array(
					'range_type'                  => 'specific_dates',
					'edit'                        => false,
					'specific_dates_date'         => $key,
					'specific_dates_max_bookings' => $value,
					'specific_dates_price'        => isset( $special_prices[ $key ] ) ? $special_prices[ $key ] : '',
					'bookable'                    => 'on',
					'is_editable'                 => true,
				);
			}
		}

		if ( is_array( $holidays ) && count( $holidays ) > 0 ) {
			foreach ( $holidays as $key => $value ) {
				$availability_data[] = array(
					'range_type'               => 'holidays',
					'edit'                     => false,
					'holidays_date'            => $key,
					'holidays_number_of_years' => $value,
					'bookable'                 => '',
					'is_editable'              => true,
				);
			}
		}

		return $availability_data;
	}

	/**
	 * Returns the Manage Availability Data for the Booking Metabox.
	 *
	 * @param array $booking_settings Array of Booking Settings.
	 *
	 * @since 5.19.0
	 */
	public static function return_manage_availability_data( $booking_settings ) {

		$data = self::check( $booking_settings, 'bkap_manage_time_availability', array() );

		if ( ! is_array( $data ) || ( is_array( $data ) && 0 === count( $data ) ) ) {
			return array();
		}

		$manage_availability_data = array();

		foreach ( $data as $key => $value ) {
			$manage_availability_data[] = array(
				'range_type'                 => self::check( $value, 'type', '' ),
				'edit'                       => false,
				'range_days_from'            => 'days' === self::check( $value, 'type', '' ) ? self::check( $value, 'from', '' ) : '',
				'range_days_to'              => 'days' === self::check( $value, 'type', '' ) ? self::check( $value, 'to', '' ) : '',
				'range_months_from'          => 'months' === self::check( $value, 'type', '' ) ? self::check( $value, 'from', '' ) : '',
				'range_months_to'            => 'months' === self::check( $value, 'type', '' ) ? self::check( $value, 'to', '' ) : '',
				'range_weeks_from'           => 'weeks' === self::check( $value, 'type', '' ) ? self::check( $value, 'from', '' ) : '',
				'range_weeks_to'             => 'weeks' === self::check( $value, 'type', '' ) ? self::check( $value, 'to', '' ) : '',
				'range_date_from'            => 'custom' === self::check( $value, 'type', '' ) ? self::check( $value, 'from', '' ) : '',
				'range_date_to'              => 'custom' === self::check( $value, 'type', '' ) ? self::check( $value, 'to', '' ) : '',
				'range_time_from'            => ( ( 'time' === self::check( $value, 'type', '' ) ) || ( 'time:' === substr( $value['type'], 0, 5 ) ) ) ? self::check( $value, 'from', '' ) : '',
				'range_time_to'              => ( ( 'time' === self::check( $value, 'type', '' ) ) || ( 'time:' === substr( $value['type'], 0, 5 ) ) ) ? self::check( $value, 'to', '' ) : '',
				'range_time_range_date_from' => 'time:range' === self::check( $value, 'type', '' ) ? self::check( $value, 'from_date', '' ) : '',
				'range_time_range_date_to'   => 'time:range' === self::check( $value, 'type', '' ) ? self::check( $value, 'to_date', '' ) : '',
				'range_time_range_time_from' => 'time:range' === self::check( $value, 'type', '' ) ? self::check( $value, 'from', '' ) : '',
				'range_time_range_time_to'   => 'time:range' === self::check( $value, 'type', '' ) ? self::check( $value, 'to', '' ) : '',
				'priority'                   => self::check( $value, 'priority', 10 ),
				'bookable'                   => 1 === (int) self::check( $value, 'bookable', '' ) ? 'on' : '',
				'is_editable'                => true,
			);
		}

		return $manage_availability_data;
	}

	/**
	 * Delets a record from the DB for Availability by Dates/Months.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function delete_availability_record( WP_REST_Request $request ) {

		global $bkap_months, $wpdb;

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$data = $request->get_param( 'data' );

		$product_id  = self::check( $data, 'product_id' );
		$range_type  = self::check( $data, 'range_type' );
		$start       = self::check( $data, 'start' );
		$end         = self::check( $data, 'end' );
		$is_bookable = self::check( $data, 'is_bookable' );

		if ( '' === $range_type ) {
			return self::response( 'error', array( 'error_description' => __( 'Error: Range Type could not be determined.', 'woocommerce-booking' ) ) );
		}

		$meta_mapping = array(
			'custom_range'    => '_bkap_custom_ranges',
			'range_of_months' => '_bkap_month_ranges',
			'specific_dates'  => '_bkap_specific_dates',
			'holidays'        => '_bkap_product_holidays',
			'holiday_range'   => '_bkap_holiday_ranges',
		);

		$meta_mapping_serialized = array(
			'custom_range'   => 'booking_date_range',
			'specific_dates' => 'booking_specific_date',
			'holidays'       => 'booking_product_holiday',
		);

		if ( ( 'custom_range' === $range_type || 'range_of_months' === $range_type ) && 'on' !== $is_bookable ) {
			$range_type = 'holiday_range';
		}

		$key = isset( $meta_mapping[ $range_type ] ) ? $meta_mapping[ $range_type ] : '';

		if ( '' === $key ) {
			return self::response( 'error', array( 'error_description' => __( 'Error: Meta Key could not be determined.', 'woocommerce-booking' ) ) );
		}

		$range_data    = get_post_meta( $product_id, $key, true );
		$key_to_delete = '';

		if ( in_array( $range_type, array( 'custom_range', 'range_of_months', 'holiday_range' ) ) ) {

			if ( '' === $start || '' === $end ) {
				return self::response( 'error', array( 'error_description' => __( 'Error: Start/End Key could not be determined.', 'woocommerce-booking' ) ) );
			}

			if ( '' !== $range_data && is_array( $range_data ) && count( $range_data ) > 0 ) {

				$check_holiday_range = ( 'holiday_range' === $range_type && '' === $is_bookable ) ? true : false;

				if ( 'range_of_months' === $range_type || $check_holiday_range ) {
					$current_year = gmdate( 'Y', current_time( 'timestamp' ) );
					$next_year    = gmdate( 'Y', strtotime( '+1 year' ) );
					$_start       = $start;
					$_end         = $end;

					if ( is_numeric( $start ) ) {

						// it's a month number.
						$month = $bkap_months[ $start ];
						$start = "$month $current_year";
					}

					$start = gmdate( 'j-n-Y', strtotime( $start ) );
					$end   = gmdate( 'j-n-Y', strtotime( $end ) );

					if ( is_numeric( $_end ) ) {

						// it's a month number.
						$month       = $bkap_months[ $_end ];
						$month_use   = $_start <= $_end ? "$month $current_year" : "$month $next_year";
						$month_start = gmdate( 'j-n-Y', strtotime( $month_use ) );
						$days        = gmdate( 't', strtotime( $month_start ) );
						$days       -= 1;
						$end         = gmdate( 'j-n-Y', strtotime( "+$days days", strtotime( $month_start ) ) );
					}
				}

				foreach ( $range_data as $_key => $_value ) {
					$_start = $_value['start'];
					$_end   = $_value['end'];

					if ( $start === $_start || $end === $_end ) {
						$key_to_delete = $_key;
					}
				}

				if ( ! is_numeric( $key_to_delete ) ) {
					return self::response( 'error', array( 'error_description' => __( 'Error: Delete Key must be numeric.', 'woocommerce-booking' ) ) );
				}
			}
		}

		if ( 'specific_dates' === $range_type || 'holidays' === $range_type ) {
			if ( '' === $start ) {
				return self::response( 'error', array( 'error_description' => __( 'Error: Start Key could not be determined.', 'woocommerce-booking' ) ) );
			}

			$key_to_delete = $start;
		}

		if ( '' === $key_to_delete ) {
			return self::response( 'error', array( 'error_description' => __( 'Error: Delete Key could not be determined.', 'woocommerce-booking' ) ) );
		}

		if ( ! isset( $range_data[ $key_to_delete ] ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Error: Delete Key not found in Range Data.', 'woocommerce-booking' ) ) );
		}

		if ( in_array( $range_type, array( 'custom_range', 'specific_dates', 'holidays' ) ) ) {
			$key_serialized = isset( $meta_mapping_serialized[ $range_type ] ) ? $meta_mapping_serialized[ $range_type ] : '';

			if ( '' === $key_serialized ) {
				return self::response( 'error', array( 'error_description' => __( 'Error: Serialized Meta Key could not be determined.', 'woocommerce-booking' ) ) );
			}

			$woocommerce_booking_settings = get_post_meta( $product_id, 'woocommerce_booking_settings', true );
			$serialized_data              = $woocommerce_booking_settings[ $key_serialized ];

			if ( isset( $serialized_data[ $key_to_delete ] ) ) {
				unset( $serialized_data[ $key_to_delete ] );

				$woocommerce_booking_settings[ $key_serialized ] = $serialized_data;
				update_post_meta( $product_id, 'woocommerce_booking_settings', $woocommerce_booking_settings );
			}
		}

		if ( 'specific_dates' === $range_type ) {

			// Update Booking History.
			$date = gmdate( 'Y-m-d', strtotime( $start ) );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE `{$wpdb->prefix}booking_history` SET status = 'inactive' WHERE post_id = '{$product_id}' AND start_date = '{$date}' AND weekday = '' AND from_time = '' AND to_time = ''" //phpcs:ignore
				)
			);

			// Update Special Prices.
			$special_prices = get_post_meta( $product_id, '_bkap_special_price', true );

			if ( is_array( $special_prices ) && count( $special_prices ) > 0 ) {

				$updated_special_prices = array();
				foreach ( $special_prices as $key => $price ) {

					if ( $date !== $price['booking_special_date'] ) {
						$updated_special_prices[ $key ] = $price;
					}
				}

				update_post_meta( $product_id, '_bkap_special_price', $updated_special_prices );
			}
		}

		// Delete from Post Meta Table.
		unset( $range_data[ $key_to_delete ] );
		update_post_meta( $product_id, $key, $range_data );
		return self::response( 'success', array( 'message' => __( 'Selected Record has been deleted successfully', 'woocommerce-booking' ) ) );
	}

	/**
	 * Returns the settings for Weekdays/Dates Timeslots for the Booking Metabox.
	 *
	 * @param array $booking_settings Booking Settings.
	 *
	 * @since 5.19.0
	 */
	public static function metabox_weekdays_dates_timeslots( $booking_settings ) {
		$settings              = array();
		$booking_time_settings = isset( $booking_settings['booking_time_settings'] ) && count( $booking_settings['booking_time_settings'] ) > 0 ? $booking_settings['booking_time_settings'] : array();

		foreach ( $booking_time_settings as $key => $value ) {
			foreach ( $value as $data ) {
				if ( 'all' === $key ) {
					continue;
				}
				$setting = array(
					'global'      => self::check( $data, 'global_time_check' ),
					'weekday'     => $key,
					'from'        => self::check( $data, 'from_slot_hrs' ) . ':' . self::check( $data, 'from_slot_min' ),
					'to'          => self::check( $data, 'to_slot_hrs' ) . ':' . self::check( $data, 'to_slot_min' ),
					'lockout'     => self::check( $data, 'lockout_slot' ),
					'price'       => self::check( $data, 'slot_price' ),
					'note'        => self::check( $data, 'booking_notes' ),
					'is_editable' => true,
				);

				$setting['to'] = '0:00' === $setting['to'] ? '' : $setting['to'];
				$setting['og_data'] = $setting;
				$settings[]    = $setting;
			}
		}

		return $settings;
	}

	/**
	 * Updates Weekdays/Dates Timeslots.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function update_weekdays_dates_timeslots( WP_REST_Request $request ) {

		global $wpdb;

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$data         = $request->get_param( 'data' );
		$current_data = self::check( $data, 'current' );
		$former_data  = self::check( $data, 'former' );
		$product_id   = self::check( $data, 'product_id' );

		self::update_weekdays_dates_timeslot( $product_id, $current_data, $former_data );

		return self::response( 'success', array( 'message' => __( 'Timeslot(s) have been updated successfully.', 'woocommerce-booking' ) ) );
	}

	public static function update_weekdays_dates_timeslot( $product_id, $current_data, $former_data ) {

		global $wpdb;

		$from         = array(
			'current' => sanitize_text_field( self::check( $current_data, 'from' ) ),
			'former'  => sanitize_text_field( self::check( $former_data, 'from' ) ),
		);
		$to           = array(
			'current' => sanitize_text_field( self::check( $current_data, 'to' ) ),
			'former'  => sanitize_text_field( self::check( $former_data, 'to' ) ),
		);
		$weekday      = self::check( $current_data, 'weekday' );
		$global       = array(
			'current' => self::check( $current_data, 'global' ),
			'former'  => self::check( $former_data, 'global' ),
		);
		$lockout      = array(
			'current' => (int) self::check( $current_data, 'lockout' ),
			'former'  => (int) self::check( $former_data, 'lockout' ),
		);
		$price        = array(
			'current' => self::check( $current_data, 'price' ),
			'former'  => self::check( $former_data, 'price' ),
		);
		$note         = array(
			'current' => sanitize_text_field( self::check( $current_data, 'note' ) ),
			'former'  => sanitize_text_field( self::check( $former_data, 'note' ) ),
		);

		// Update _bkap_time_settings.
		$settings           = get_post_meta( $product_id, '_bkap_time_settings', true );
		$time_slot_settings = self::check( $settings, $weekday, array() );

		$exp_from         = explode( ':', $from['current'] );
		$exp_to           = explode( ':', $to['current'] );
		$updated_settings = array(
			'from_slot_hrs'     => $exp_from[0],
			'from_slot_min'     => $exp_from[1],
			'to_slot_hrs'       => ( '' == $exp_to[0] ) ? '00' : $exp_to[0],
			'to_slot_min'       => isset( $exp_to[1] ) ? $exp_to[1] : '00',
			'booking_notes'     => $note['current'],
			'slot_price'        => $price['current'],
			'lockout_slot'      => $lockout['current'],
			'global_time_check' => $global['current'],
		);

		if ( count( $time_slot_settings ) > 0 ) {

			$exp_from = explode( ':', $from['former'] );
			$exp_to   = explode( ':', $to['former'] );

			foreach ( $time_slot_settings as $key => $value ) {
				if ( $exp_from[0] === $value['from_slot_hrs'] && $exp_from[1] === $value['from_slot_min'] && $exp_to[0] === $value['to_slot_hrs'] && ( isset( $exp_to[1] ) ? $exp_to[1] : '00' ) === $value['to_slot_min'] && $global['former'] === $value['global_time_check'] && $price['former'] === $value['slot_price'] ) {
					$settings[ $weekday ][ $key ] = $updated_settings;
				}
			}

			update_post_meta( $product_id, '_bkap_time_settings', $settings );
			$woocommerce_booking_settings['booking_time_settings'] = $settings;
		}

		// Update woocommerce_booking_settings.
		$settings              = get_post_meta( $product_id, 'woocommerce_booking_settings', true );
		$booking_time_settings = self::check( $settings, 'booking_time_settings', array() );
		$selected_settings     = self::check( $booking_time_settings, $weekday, array() );

		if ( count( $selected_settings ) > 0 ) {

			$exp_from = explode( ':', $from['former'] );
			$exp_to   = explode( ':', $to['former'] );

			foreach ( $selected_settings  as $key => $value ) {
				if ( $exp_from[0] === $value['from_slot_hrs'] && $exp_from[1] === $value['from_slot_min'] && $exp_to[0] === $value['to_slot_hrs'] && ( isset( $exp_to[1] ) ? $exp_to[1] : '00' ) === $value['to_slot_min'] && $global['former'] === $value['global_time_check'] && $price['former'] === $value['slot_price'] ) {
					$booking_time_settings[ $weekday ][ $key ] = $updated_settings;
				}
			}

			$settings['booking_time_settings'] = $booking_time_settings;
			update_post_meta( $product_id, 'woocommerce_booking_settings', $settings );
		}

		if ( '00:00' === $to['former'] ) {
			$to['former'] = '';
		}

		// Update booking history.
		$where = array(
			'post_id'   => $product_id,
			'weekday'   => $weekday,
			'from_time' => $from['former'],
			'to_time'   => $to['former'],
		);

		$specific = false;
		if ( false === strpos( $weekday, '_' ) ) {
			$date_from_format    = DateTime::createFromFormat( 'j-n-Y', $weekday );
			$where['start_date'] = $date_from_format->format( 'Y-m-d' );
			$where['weekday']    = '';
			$specific            = true;
		}
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
		$wpdb->update(
			$wpdb->prefix . 'booking_history',
			array(
				'from_time'     => $from['current'],
				'to_time'       => $to['current'],
				'total_booking' => $lockout['current'],
			),
			$where,
			array(
				'%s',
				'%s',
				'%s',
			),
			array(
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
			)
		);

		if ( $from['current'] === $from['former'] && $to['current'] === $to['former'] ) {
			$where['start_date'] = '0000-00-00';
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
			$wpdb->update(
				$wpdb->prefix . 'booking_history',
				array(
					'from_time'         => $from['current'],
					'to_time'           => $to['current'],
					'total_booking'     => $lockout['current'],
					'available_booking' => $lockout['current'],
				),
				$where,
				array(
					'%s',
					'%s',
					'%s',
					'%s',
				),
				array(
					'%d',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
				)
			);
		}

		$st_date     = '0000-00-00';
		$check_query = 'SELECT * FROM `' . $wpdb->prefix . "booking_history`
						WHERE post_id = %d
						AND weekday = %s
						AND start_date != %s
						AND from_time = %s
						AND to_time = %s
						AND status = ''";
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
		$check_date_records = $wpdb->get_results( $wpdb->prepare( $check_query, $product_id, $weekday, $st_date, $from['former'], $to['former'] ) );

		if ( ! empty( $check_date_records ) ) {
			foreach ( $check_date_records as $k => $v ) {
				if ( '' !== $v->start_date ) {

					$date1 = new DateTime( $v->start_date );
					$date2 = new DateTime( gmdate( "Y-m-d", strtotime( 'yesterday' ) ) );

					if ( $date1 > $date2 ) {
						$new_available_booking = ( absint( $lockout['former'] ) > 0 ) ? absint( $lockout['former'] ) - absint( $v->available_booking ) : 0;
						$new_available_booking = (int) $lockout['current'] - (int) $new_available_booking;

						$query = 'UPDATE `' . $wpdb->prefix . "booking_history`
								SET available_booking = '" . $new_available_booking . "',
								total_booking = '" . $lockout['current'] . "'
								WHERE id = '" . $v->id . "'
								AND status = ''";
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
						$wpdb->query( $query );
					}
				}
			}
		}

		if ( $specific ) {

			$st_date     = $date_from_format->format( 'Y-m-d' );
			$weekday     = '';
			$check_query = 'SELECT * FROM `' . $wpdb->prefix . "booking_history`
							WHERE post_id = %d
							AND weekday = %s
							AND start_date = %s
							AND from_time = %s
							AND to_time = %s
							AND status = ''";
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
			$check_date_records = $wpdb->get_results( $wpdb->prepare( $check_query, $product_id, $weekday, $st_date, $from['former'], $to['former'] ) );

			if ( ! empty( $check_date_records ) ) {
				foreach ( $check_date_records as $k => $v ) {
					if ( '' !== $v->start_date ) {

						$date1 = new DateTime( $v->start_date );
						$date2 = new DateTime( gmdate( 'Y-m-d', strtotime( 'yesterday' ) ) ); // phpcs:ignore

						if ( $date1 > $date2 ) {
							$new_available_booking = ( absint( $lockout['former'] ) > 0 ) ? absint( $lockout['former'] ) - absint( $v->available_booking ) : 0;
							$new_available_booking = (int) $lockout['current'] - (int) $new_available_booking;

							$query = 'UPDATE `' . $wpdb->prefix . "booking_history`
									SET available_booking = '" . $new_available_booking . "',
									total_booking = '" . $lockout['current'] . "'
									WHERE id = '" . $v->id . "'
									AND status = ''";
							// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
							$wpdb->query( $query );
						}
					}
				}
			}
		}
	}

	/**
	 * Deletes a Weekdays/Dates Timeslots.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function delete_weekdays_dates_timeslots( WP_REST_Request $request ) {

		global $wpdb;

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$data                         = $request->get_param( 'data' );
		$from                         = sanitize_text_field( self::check( $data, 'from' ) );
		$to                           = sanitize_text_field( self::check( $data, 'to' ) );
		$weekday                      = sanitize_text_field( self::check( $data, 'weekday' ) );
		$weekday                      = ( ! is_array( $weekday ) ) ? array( $weekday ) : $weekday;
		$lockout                      = sanitize_text_field( self::check( $data, 'lockout' ) );
		$price                        = sanitize_text_field( self::check( $data, 'price' ) );
		$product_id                   = sanitize_text_field( self::check( $data, 'product_id' ) );
		$bkap_time_settings           = get_post_meta( $product_id, '_bkap_time_settings', true );
		$woocommerce_booking_settings = get_post_meta( $product_id, 'woocommerce_booking_settings', true );
		$booking_time_settings        = self::check( $woocommerce_booking_settings, 'booking_time_settings', array() );
		$exp_from                     = explode( ':', $from );
		$exp_to                       = explode( ':', $to );

		if ( is_array( $weekday ) && count( $weekday ) > 0 ) {
			foreach ( $weekday as $day ) {

				// Update _bkap_time_settings.
				$time_slot_settings = self::check( $bkap_time_settings, $day, array() );

				if ( count( $time_slot_settings ) > 0 ) {
					foreach ( $time_slot_settings  as $key => $value ) {
						if ( $exp_from[0] === $value['from_slot_hrs'] && $exp_from[1] === $value['from_slot_min'] && $exp_to[0] === $value['to_slot_hrs'] && ( isset( $exp_to[1] ) ? $exp_to[1] : '00' ) === $value['to_slot_min'] && $lockout === $value['lockout_slot'] && $price === $value['slot_price'] ) {
							unset( $bkap_time_settings[ $day ][ $key ] );
						}
					}
				}

				// Update woocommerce_booking_settings.
				$selected_settings = self::check( $booking_time_settings, $day, array() );

				if ( count( $selected_settings ) > 0 ) {
					foreach ( $selected_settings  as $key => $value ) {
						if ( $exp_from[0] === $value['from_slot_hrs'] && $exp_from[1] === $value['from_slot_min'] && $exp_to[0] === $value['to_slot_hrs'] && ( isset( $exp_to[1] ) ? $exp_to[1] : '00' ) === $value['to_slot_min'] && $lockout === $value['lockout_slot'] && $price === $value['slot_price'] ) {
							unset( $booking_time_settings[ $day ][ $key ] );
						}
					}
				}

				// Update booking history.
				$_from = gmdate( 'H:i', strtotime( $from ) );
				$_to   = 0 === $exp_to[0] && 0 === $exp_to[1] ? '' : gmdate( 'H:i', strtotime( $to ) );

				if ( 'booking' === substr( $day, 0, 7 ) ) { // recurring weekday.

					// delete the base record.
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
					$deleted_rows = $wpdb->query(
						'DELETE FROM `' . $wpdb->prefix . "booking_history` WHERE post_id = '" . $product_id . "' AND weekday = '" . $day . "' AND start_date = '0000-00-00' AND TIME_FORMAT( from_time, '%H:%i' ) = '" . $_from . "' AND TIME_FORMAT( to_time, '%H:%i' ) = '" . $_to . "'" //phpcs:ignore
					);
					if ( $deleted_rows > 0 ) {
						if ( '' !== $_to ) {
							// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
							$wpdb->query(
								'UPDATE `' . $wpdb->prefix . "booking_history` SET status = 'inactive' WHERE post_id = '" . $product_id . "' AND weekday = '" . $day . "' AND start_date <> '0000-00-00' AND TIME_FORMAT( from_time, '%H:%i' ) = '" . $_from . "' AND TIME_FORMAT( to_time, '%H:%i' ) = '" . $_to . "'" //phpcs:ignore
							);
						} else {
							// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
							$wpdb->query(
								'UPDATE `' . $wpdb->prefix . "booking_history` SET status = 'inactive' WHERE post_id = '" . $product_id . "' AND weekday = '" . $day . "' AND start_date <> '0000-00-00' AND TIME_FORMAT( from_time, '%H:%i' ) = '" . $_from . "' AND to_time = ''" //phpcs:ignore
							);
						}
					}
				} else { // specific date.

					$date = gmdate( 'Y-m-d', strtotime( $day ) ); //phpcs:ignore

					// set the date record to inactive.
					if (
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
						0 === $wpdb->query(
							'UPDATE `' . $wpdb->prefix . "booking_history` SET status = 'inactive' WHERE post_id = '" . $product_id . "' AND start_date = '" . $date . "' AND from_time = '" . $_from . "' AND to_time = '" . $_to . "'" //phpcs:ignore
						)
					) {
						// set the date record to inactive.
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
						$wpdb->query(
							'UPDATE `' . $wpdb->prefix . "booking_history` SET status = 'inactive' WHERE post_id = '" . $product_id . "' AND start_date = '" . $date . "' AND from_time = '" . $_from . "' AND to_time = '" . $_to . "'" //phpcs:ignore
						);
					}
				}

				do_action( 'bkap_delete_timeslot', $product_id, $day, $from, $to );
			}

			update_post_meta( $product_id, '_bkap_time_settings', $bkap_time_settings );
			$woocommerce_booking_settings['booking_time_settings'] = $booking_time_settings;
			update_post_meta( $product_id, 'woocommerce_booking_settings', $woocommerce_booking_settings );
			return self::response( 'success', array( 'message' => __( 'Timeslot(s) have been updated successfully.', 'woocommerce-booking' ) ) );
		}

		return self::response( 'error', array( 'error_description' => __( 'Error. Timeslot(s) could not be updated.', 'woocommerce-booking' ) ) );
	}

	/**
	 * Returns the settings for Weekday for the Booking Metabox.
	 *
	 * @param array $booking_settings Booking Settings.
	 * @param array $special_prices Special Prices.
	 *
	 * @since 5.19.0
	 */
	public static function metabox_weekday_settings( $booking_settings, $special_prices ) {

		$bkap_weekdays = bkap_weekdays();

		$settings           = array();
		$recurring_weekdays = self::check( $booking_settings, 'booking_recurring', array() );
		$recurring_lockout  = self::check( $booking_settings, 'booking_recurring_lockout', array() );
		$_special_prices    = array();

		if ( is_array( $special_prices ) && count( $special_prices ) > 0 ) {

			$prices = array();

			foreach ( $special_prices as $special_key => $special_value ) {
				$weekday_set = $special_value['booking_special_weekday'];

				if ( '' !== $weekday_set ) {
					$prices[ $weekday_set ] = $special_value['booking_special_price'];
				}
			}

			$_special_prices = $prices;
		}

		if ( empty( $recurring_weekdays ) ) {
			$recurring_weekdays = array(
				'booking_weekday_0' => 'on',
				'booking_weekday_1' => 'on',
				'booking_weekday_2' => 'on',
				'booking_weekday_3' => 'on',
				'booking_weekday_4' => 'on',
				'booking_weekday_5' => 'on',
				'booking_weekday_6' => 'on',
			);
		}

		foreach ( $bkap_weekdays as $key => $value ) {
			$settings[ $key ] = array(
				'weekday' => $value,
				'status'  => isset( $recurring_weekdays[ $key ] ) && '' !== $recurring_weekdays[ $key ] ? 'on' : '',
				'lockout' => self::check( $recurring_lockout, $key, '' ),
				'price'   => self::check( $_special_prices, $key, '' ),
			);
		}

		return $settings;
	}

	/**
	 * Deletes ALL Weekdays/Dates Timeslots.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function delete_all_weekdays_dates_timeslots( WP_REST_Request $request ) {

		global $wpdb;

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$data       = $request->get_param( 'data' );
		$product_id = sanitize_text_field( self::check( $data, 'product_id' ) );

		update_post_meta( $product_id, '_bkap_time_settings', array() );

		$woocommerce_booking_settings                          = get_post_meta( $product_id, 'woocommerce_booking_settings', true );
		$woocommerce_booking_settings['booking_time_settings'] = array();
		update_post_meta( $product_id, 'woocommerce_booking_settings', $woocommerce_booking_settings );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
		$wpdb->query(
			'UPDATE `' . $wpdb->prefix . "booking_history` SET status = 'inactive' WHERE post_id = '" . $product_id . "'" //phpcs:ignore
		);

		return self::response( 'success', array( 'message' => __( 'All Timeslot(s) have been deleted successfully.', 'woocommerce-booking' ) ) );
	}

	/**
	 * Returns the settings for Weekdays/Dates Timeslots for the Booking Metabox.
	 *
	 * @param array $booking_settings Booking Settings.
	 *
	 * @since 5.19.0
	 */
	public static function return_duration_based_bookings_data( $booking_settings ) {

		$settings = array();

		//if ( isset( $booking_settings['bkap_duration_settings'] ) && count( $booking_settings['bkap_duration_settings'] ) > 0 ) {

			$duration_settings = isset( $booking_settings['bkap_duration_settings'] ) ? $booking_settings['bkap_duration_settings'] : array();

			$settings = array(
				'duration_label'       => self::check( $duration_settings, 'duration_label' ),
				'duration'             => self::check( $duration_settings, 'duration', 1 ),
				'duration_gap'         => self::check( $duration_settings, 'duration_gap', 0 ),
				'duration_type'        => self::check( $duration_settings, 'duration_type', 'hours' ),
				'duration_gap_type'    => self::check( $duration_settings, 'duration_gap_type', 'hours' ),
				'duration_min'         => self::check( $duration_settings, 'duration_min', 1 ),
				'duration_max'         => self::check( $duration_settings, 'duration_max', 1 ),
				'duration_max_booking' => self::check( $duration_settings, 'duration_max_booking', 0 ),
				'duration_price'       => self::check( $duration_settings, 'duration_price' ),
				'first_duration'       => self::check( $duration_settings, 'first_duration' ),
				'end_duration'         => self::check( $duration_settings, 'end_duration' ),
			);
		//}

		return $settings;
	}

	/**
	 * Updates the booking history table.
	 *
	 * @param array $product_id Product ID.
	 *
	 * @since 5.19.0
	 */
	public static function update_booking_history_table( $product_id ) {

		global $wpdb;

		$booking_enabled = get_post_meta( $product_id, '_bkap_enable_booking', true );
		$booking_type    = bkap_type( $product_id );

		if ( 'on' === $booking_enabled && in_array( $booking_type, array( 'only_day', 'multidates' ) ) ) {

			$recurring_weekdays = get_post_meta( $product_id, '_bkap_recurring_weekdays', true );
			$recurring_lockout  = get_post_meta( $product_id, '_bkap_recurring_lockout', true );
			$specific_dates     = get_post_meta( $product_id, '_bkap_specific_dates', true );

			// recurring days and lockout update.
			if ( is_array( $recurring_weekdays ) && count( $recurring_weekdays ) > 0 && isset( $recurring_lockout ) && is_array( $recurring_lockout ) && count( $recurring_lockout ) > 0 ) {

				foreach ( $recurring_weekdays as $weekday => $status ) {

					if ( 'on' !== $status ) { // weekday is disabled.

						// if a record exists in the table, it needs to be deactivated.
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
						$wpdb->query(
							$wpdb->prepare(
								'UPDATE `' . $wpdb->prefix . "booking_history` SET status = 'inactive' WHERE post_id = %d AND weekday = %s",
								(int) $product_id,
								$weekday
							)
						);

						// Delete the base records for the recurring weekdays.
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
						$wpdb->query(
							$wpdb->prepare(
								'DELETE FROM `' . $wpdb->prefix . 'booking_history` WHERE post_id = %d AND weekday = %s',
								$product_id,
								$weekday
							)
						);

						continue;
					}

					// weekday is enabled.
					$available_booking = $recurring_lockout[ $weekday ];
					$updated_lockout   = $recurring_lockout[ $weekday ];
					
					// check if the weekday is already present.
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
					$check_weekday = $wpdb->get_results(
						$wpdb->prepare(
							'SELECT total_booking, available_booking FROM `' . $wpdb->prefix . "booking_history` WHERE post_id = %d AND weekday = %s AND start_date = '0000-00-00' AND status = ''",
							$product_id,
							$weekday
						)
					);

					// if yes, then update the lockout.
					if ( is_array( $check_weekday ) && count( $check_weekday ) > 0 ) { // there will be only 1 active record at any given time

						// Update the existing record so that lockout is managed and orders do not go missing frm the View bookings page.
						if ( is_numeric( $recurring_lockout[ $weekday ] ) && $recurring_lockout[ $weekday ] > 0 ) {
							// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
							$wpdb->query(
								$wpdb->prepare(
									"UPDATE {$wpdb->prefix}booking_history 
									SET total_booking = %d, 
										available_booking = available_booking + %d
									WHERE post_id = %s 
									  AND weekday = %s 
									  AND start_date = %s 
									  AND status = %s",
									$updated_lockout,
									( $recurring_lockout[ $weekday ] - $check_weekday[0]->total_booking ),
									$product_id,
									$weekday,
									'0000-00-00',
									''
								)
							);
							// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
							$wpdb->query(
								$wpdb->prepare(
									"UPDATE {$wpdb->prefix}booking_history 
									SET total_booking = %d, 
										available_booking = available_booking + %d, 
										status = %s 
									WHERE post_id = %s 
									  AND weekday = %s 
									  AND start_date <> %s",
									$updated_lockout,
									( $recurring_lockout[ $weekday ] - $check_weekday[0]->total_booking ),
									'',
									$product_id,
									$weekday,
									'0000-00-00'
								)
							);

						} elseif ( '' === $recurring_lockout[ $weekday ] || 0 === $recurring_lockout[ $weekday ] ) { // unlimited bookings
							// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
							$wpdb->query(
								$wpdb->prepare(
									"UPDATE {$wpdb->prefix}booking_history 
									SET total_booking = %d, 
										available_booking = %d 
									WHERE post_id = %s
									  AND weekday = %s 
									  AND start_date = %s 
									  AND status = %s",
									$updated_lockout,
									0,
									$product_id,
									$weekday,
									'0000-00-00',
									''
								)
							);
							// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
							$wpdb->query(
								$wpdb->prepare(
									"UPDATE {$wpdb->prefix}booking_history 
									SET total_booking = %d, 
										available_booking = %d, 
										status = %s 
									WHERE post_id = %s 
									  AND weekday = %s 
									  AND start_date <> %s",
									$updated_lockout,
									0,
									'',
									$product_id,
									$weekday,
									'0000-00-00'
								)
							);
						}
					} else {
						// if not found, check if there's a date record present.
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
						$lockout_results = $wpdb->get_results(
							$wpdb->prepare(
								'SELECT total_booking FROM `' . $wpdb->prefix . "booking_history` WHERE post_id = %d AND start_date != '0000-00-00' AND weekday = %s ORDER BY id DESC LIMIT 1",
								$product_id,
								$weekday
							)
						);

						if ( is_array( $lockout_results ) && count( $lockout_results ) > 0 ) {

							if ( is_numeric( $recurring_lockout[ $weekday ] ) && $recurring_lockout[ $weekday ] > 0 ) {
								$change_in_lockout = $recurring_lockout[ $weekday ] - $lockout_results[0]->total_booking;
								$available_booking = $lockout_results[0]->total_booking + $change_in_lockout;

								// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
								$wpdb->query(
									$wpdb->prepare(
										"INSERT INTO {$wpdb->prefix}booking_history 
										(post_id, weekday, start_date, end_date, from_time, to_time, total_booking, available_booking) 
										VALUES (%s, %s, %s, %s, %s, %s, %d, %d)",
										$product_id,
										$weekday,
										'0000-00-00',
										'0000-00-00',
										'',
										'',
										$updated_lockout,
										$available_booking
									)
								);

								// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
								$wpdb->query(
									$wpdb->prepare(
										"UPDATE {$wpdb->prefix}booking_history 
										SET total_booking = %d, 
											available_booking = available_booking + %d, 
											status = '' 
										WHERE post_id = %s 
											AND weekday = %s 
											AND start_date <> %s",
										$updated_lockout,
										$change_in_lockout,
										$product_id,
										$weekday,
										'0000-00-00'
									)
								);
							} elseif ( '' === $recurring_lockout[ $weekday ] || 0 === $recurring_lockout[ $weekday ] ) {
								// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
								$wpdb->query(
									$wpdb->prepare(
										"INSERT INTO {$wpdb->prefix}booking_history 
										(post_id, weekday, start_date, end_date, from_time, to_time, total_booking, available_booking) 
										VALUES (%s, %s, %s, %s, %s, %s, %d, %d)",
										$product_id,
										$weekday,
										'0000-00-00',
										'0000-00-00',
										'',
										'',
										0,
										0
									)
								);

								// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
								$wpdb->query(
									$wpdb->prepare(
										"UPDATE {$wpdb->prefix}booking_history 
										SET total_booking = %d, available_booking = 0, status = '' 
										WHERE post_id = %s AND weekday = %s AND start_date <> %s",
										$updated_lockout,
										$product_id,
										$weekday,
										'0000-00-00'
									)
								);
							}
						} else {

							
							// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
							$wpdb->query(
								$wpdb->prepare(
									"INSERT INTO {$wpdb->prefix}booking_history 
									(post_id, weekday, start_date, end_date, from_time, to_time, total_booking, available_booking) 
									VALUES (%s, %s, %s, %s, %s, %s, %d, %d)",
									$product_id,
									$weekday,
									'0000-00-00',
									'0000-00-00',
									'',
									'',
									$updated_lockout,
									$available_booking
								)
							);
						}
					}
				}
			}

			if ( is_array( $specific_dates ) && count( $specific_dates ) > 0 ) {

				foreach ( $specific_dates as $specific_date => $specific_lockout ) {

					$specific_date     = gmdate( 'Y-m-d', strtotime( $specific_date ) );
					$available_booking = $specific_lockout;
					$updated_lockout   = $specific_lockout;
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
					$check_date = $wpdb->get_results(
						$wpdb->prepare(
							'SELECT total_booking, available_booking FROM `' . $wpdb->prefix . "booking_history` WHERE post_id = %d AND weekday != '' AND start_date = %s AND status = ''",
							$product_id,
							$specific_date
						)
					);

					if ( count( $check_date ) > 0 ) {
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
						$wpdb->query(
							$wpdb->prepare(
								"UPDATE {$wpdb->prefix}booking_history 
								SET weekday = %s, status = %s 
								WHERE post_id = %d AND start_date = %s",
								'',
								'',
								$product_id,
								$specific_date
							)
						);
					}

					// check if the date is already present.
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
					$check_date = $wpdb->get_results(
						$wpdb->prepare(
							'SELECT total_booking, available_booking FROM `' . $wpdb->prefix . "booking_history` WHERE post_id = %d AND weekday = '' AND start_date = %s AND status = ''",
							$product_id,
							$specific_date
						)
					);

					// if yes, then update the lockout.
					if ( isset( $check_date ) && count( $check_date ) > 0 ) { // there will be only 1 active record at any given time.

						if ( is_numeric( $specific_lockout ) && $specific_lockout > 0 ) {
							// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
							$wpdb->query(
								$wpdb->prepare(
									"UPDATE {$wpdb->prefix}booking_history 
									SET total_booking = %d, 
										available_booking = available_booking + %d, 
										status = %s 
									WHERE post_id = %s 
									AND weekday = %s 
									AND start_date = %s",
									$specific_lockout,
									( $specific_lockout - $check_date[0]->total_booking ),
									'',
									$product_id,
									'',
									$specific_date
								)
							);
						} elseif ( '' === $specific_lockout || 0 === $specific_lockout ) { // unlimited bookings.
							// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
							$wpdb->query(
								$wpdb->prepare(
									"UPDATE {$wpdb->prefix}booking_history 
									SET total_booking = %d, 
										available_booking = %d, 
										status = %s 
									WHERE post_id = %s 
									AND weekday = %s 
									AND start_date = %s",
									$specific_lockout,
									0,
									'',
									$product_id,
									'',
									$specific_date
								)
							);
						}
					} else {
						// if not found, check if there's an inactive date record present.
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
						$lockout_results = $wpdb->get_results(
							$wpdb->prepare(
								'SELECT total_booking FROM `' . $wpdb->prefix . "booking_history` WHERE post_id = %d AND start_date = %s AND weekday = '' AND status <> ''",
								$product_id,
								$specific_date
							)
						);

						if ( isset( $lockout_results ) && count( $lockout_results ) > 0 ) {
							if ( is_numeric( $specific_lockout ) && $specific_lockout > 0 ) {
								// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
								$wpdb->query(
									$wpdb->prepare(
										"UPDATE {$wpdb->prefix}booking_history 
										SET total_booking = %d, 
											available_booking = available_booking + %d, 
											status = %s 
										WHERE post_id = %s 
										AND weekday = %s 
										AND start_date = %s",
										$specific_lockout,
										( $specific_lockout - $lockout_results[0]->total_booking ),
										'',
										$product_id,
										'',
										$specific_date
									)
								);
							} elseif ( '' === $specific_lockout || 0 === $specific_lockout ) { // unlimited bookings.
								// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
								$wpdb->query(
									$wpdb->prepare(
										"UPDATE {$wpdb->prefix}booking_history 
										SET total_booking = %d, 
											available_booking = %d, 
											status = %s 
										WHERE post_id = %d 
										AND weekday = %s 
										AND start_date = %s",
										$specific_lockout,
										0,
										'',
										$product_id,
										'',
										$specific_date
									)
								);
							}
						} else {
							// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
							$wpdb->query(
								$wpdb->prepare(
									"INSERT INTO {$wpdb->prefix}booking_history 
									(post_id, weekday, start_date, end_date, from_time, to_time, total_booking, available_booking) 
									VALUES (%s, %s, %s, %s, %s, %s, %d, %d)",
									$product_id,
									'',
									$specific_date,
									'0000-00-00',
									'',
									'',
									$specific_lockout,
									$available_booking
								)
							);
						}
					}
				}
			}
		}

		if ( 'on' === $booking_enabled && in_array( $booking_type, array( 'date_time', 'multidates_fixedtime' ) ) ) {
			bkap_update_booking_history( $product_id, array( '_bkap_time_settings' => get_post_meta( $product_id, '_bkap_time_settings', true ) ), $booking_type );
		}
	}

	/**
	 * Returns the Block Pricing Type.
	 *
	 * @param array $product_id Product ID.
	 * @param array $default_booking_settings Default Booking Settings.
	 * @param bool  $has_defaults If the Default Booking has been set.
	 *
	 * @since 5.19.0
	 */
	public static function return_block_pricing_type( $product_id, $default_booking_settings, $has_defaults ) {
		$bkap_fixed_blocks = bkap_get_post_meta_data( $product_id, '_bkap_fixed_blocks', $default_booking_settings, $has_defaults );
		$bkap_price_ranges = bkap_get_post_meta_data( $product_id, '_bkap_price_ranges', $default_booking_settings, $has_defaults );
		return '' !== $bkap_fixed_blocks ? $bkap_fixed_blocks : ( '' !== $bkap_price_ranges ? $bkap_price_ranges : '' );
	}

	/**
	 * Returns the data for the Fixed Block Booking Type.
	 *
	 * @param array $product_id Product ID.
	 * @param array $default_booking_settings Default Booking Settings.
	 * @param bool  $has_defaults If the Default Booking has been set.
	 *
	 * @since 5.19.0
	 */
	public static function return_fixed_block_data( $product_id, $default_booking_settings, $has_defaults ) {
		$settings         = array();
		$fixed_block_data = bkap_get_post_meta_data( $product_id, '_bkap_fixed_blocks_data', $default_booking_settings, $has_defaults );

		if ( is_array( $fixed_block_data ) && count( $fixed_block_data ) > 0 ) {
			foreach ( $fixed_block_data as $data ) {
				$data['is_new_row'] = false;
				$data['edit']       = false;
				$settings[]         = $data;
			}
		}

		return $settings;
	}

	/**
	 * Returns the header columns for the Block Pricing - Price by Range of Nights.
	 *
	 * @param array $product_id Product ID.
	 *
	 * @since 5.19.0
	 */
	public static function return_header_columns_block_pricing_price_by_range_nights( $product_id ) {
		$header_data        = array();
		$product            = wc_get_product( $product_id );
		$currency_symbol    = get_woocommerce_currency_symbol();
		$product_attributes = get_post_meta( $product_id, '_product_attributes', true );

		if ( is_array( $product_attributes ) && count( $product_attributes ) > 0 && 'variable' === $product->get_type() ) {
			foreach ( $product_attributes as $attribute ) {
				$header_data[] = __( wc_attribute_label( $attribute['name'] ), 'woocommerce-booking' ); // phpcs:ignore
			}
		}

		return array_merge(
			$header_data,
			array(
				__( 'Minimum Day', 'woocommerce-booking' ),
				__( 'Maximum Day', 'woocommerce-booking' ),
				__( 'Per Day', 'woocommerce-booking' ) . ' (' . $currency_symbol . ')',
				__( 'Fixed', 'woocommerce-booking' ) . ' (' . $currency_symbol . ')',
				__( 'Actions', 'woocommerce-booking' ),
			)
		);
	}

	/**
	 * Returns the attribute data for variable products.
	 *
	 * @param array $product_id Product ID.
	 *
	 * @since 5.19.0
	 */
	public static function return_block_pricing_variable_product_attributes( $product_id ) {
		$attributes         = array();
		$product            = wc_get_product( $product_id );
		$product_attributes = get_post_meta( $product_id, '_product_attributes', true );

		if ( is_array( $product_attributes ) && count( $product_attributes ) > 0 && 'variable' === $product->get_type() ) {
			foreach ( $product_attributes as $key => $value ) {

				$data        = array();
				$value_array = $value['is_taxonomy'] ? wc_get_product_terms( $product_id, $value['name'], array( 'fields' => 'names' ) ) : explode( ' | ', $value['value'] );

				foreach ( $value_array as $_value ) {
					$data[] = html_entity_decode( htmlspecialchars( $_value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8' ) ); //phpcs:ignore
				}

				$attributes[ $key ] = $data;
			}
		}

		return $attributes;
	}

	/**
	 * Returns the data for the Fixed Block Booking Type.
	 *
	 * @param array $product_id Product ID.
	 * @param array $default_booking_settings Default Booking Settings.
	 * @param bool  $has_defaults If the Default Booking has been set.
	 *
	 * @since 5.19.0
	 */
	public static function return_price_by_range_of_nights_data( $product_id, $default_booking_settings, $has_defaults ) {
		$settings           = array();
		$product            = wc_get_product( $product_id );
		$price_range_data   = bkap_get_post_meta_data( $product_id, '_bkap_price_range_data', $default_booking_settings, $has_defaults );
		$product_attributes = get_post_meta( $product_id, '_product_attributes', true );

		if ( is_array( $price_range_data ) && count( $price_range_data ) > 0 ) {
			foreach ( $price_range_data as $data ) {

				if ( 'variable' === $product->get_type() && is_array( $product_attributes ) && count( $product_attributes ) > 0 ) {
					foreach ( $product_attributes as $key => $value ) {
						if ( ! isset( $data[ $key ] ) ) {
							$data[ $key ] = '';
						}
					}
				}

				$data['is_new_row'] = false;
				$data['edit']       = false;

				$settings[] = $data;
			}
		}

		return $settings;
	}

	/**
	 * Deletes Block Pricing Price Range by Nights Data.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function delete_block_pricing_price_range_by_night_data( WP_REST_Request $request ) {
		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$data             = $request->get_param( 'data' );
		$product_id       = sanitize_text_field( self::check( $data, 'product_id' ) );
		$id               = sanitize_text_field( self::check( $data, 'index' ) );
		$price_range_data = get_post_meta( $product_id, '_bkap_price_range_data', true );

		if ( isset( $price_range_data[ $id ] ) ) {
			unset( $price_range_data[ $id ] );

			$price_range_data = array_values( $price_range_data );

			update_post_meta( $product_id, '_bkap_price_range_data', $price_range_data );
			return self::response( 'success', array( 'message' => __( 'Price Range data has been deleted.', 'woocommerce-booking' ) ) );
		}

		return self::response( 'error', array( 'error_description' => __( 'Error. Price Range data could not be deleted.', 'woocommerce-booking' ) ) );
	}

	/**
	 * Deletes ALL Block Pricing Price Range by Nights Data.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function delete_all_block_pricing_price_range_by_night_data( WP_REST_Request $request ) {
		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$data       = $request->get_param( 'data' );
		$product_id = sanitize_text_field( self::check( $data, 'product_id' ) );

		update_post_meta( $product_id, '_bkap_price_range_data', '' );
		return self::response( 'success', array( 'message' => __( 'ALL Price Range data have been deleted.', 'woocommerce-booking' ) ) );
	}

	/**
	 * Deletes Fixed Block Data.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function delete_block_pricing_fixed_block_data( WP_REST_Request $request ) {
		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$data             = $request->get_param( 'data' );
		$product_id       = sanitize_text_field( self::check( $data, 'product_id' ) );
		$id               = sanitize_text_field( self::check( $data, 'index' ) );
		$fixed_block_data = get_post_meta( $product_id, '_bkap_fixed_blocks_data', true );

		if ( isset( $fixed_block_data[ $id ] ) ) {
			unset( $fixed_block_data[ $id ] );

			update_post_meta( $product_id, '_bkap_fixed_blocks_data', $fixed_block_data );
			return self::response( 'success', array( 'message' => __( 'Fixed Block data has been deleted.', 'woocommerce-booking' ) ) );
		}

		return self::response( 'error', array( 'error_description' => __( 'Error. Fixed Block data could not be deleted.', 'woocommerce-booking' ) ) );
	}

	/**
	 * Deletes ALL Fixed Block Data.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function delete_all_block_pricing_fixed_block_data( WP_REST_Request $request ) {
		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$data       = $request->get_param( 'data' );
		$product_id = sanitize_text_field( self::check( $data, 'product_id' ) );

		update_post_meta( $product_id, '_bkap_fixed_blocks_data', '' );
		return self::response( 'success', array( 'message' => __( 'ALL Fixed Block data have been deleted.', 'woocommerce-booking' ) ) );
	}

	/**
	 * Returns the Persons Data.
	 *
	 * @param array $booking_settings Booking Settings.
	 *
	 * @since 5.19.0
	 */
	public static function return_person_type_data( $booking_settings ) {
		$settings    = array();
		$person_ids  = self::check( $booking_settings, 'bkap_person_ids', array() );
		$person_data = self::check( $booking_settings, 'bkap_person_data', array() );

		if ( is_array( $person_ids ) && count( $person_ids ) > 0 && is_array( $person_data ) && count( $person_data ) > 0 ) {
			foreach ( $person_ids as $person_id ) {
				if ( isset( $person_data[ $person_id ] ) && is_array( $person_data[ $person_id ] ) && count( $person_data[ $person_id ] ) > 0 ) {
					$setting                 = $person_data[ $person_id ];
					$setting['person_id']    = $person_id;
					$person                  = get_post( $person_id );
					$setting['person_title'] = $person->post_title;
					$setting['is_new_row']   = false;
					$setting['edit']         = false;
					$settings[]              = $setting;
				}
			}
		}

		return $settings;
	}

	/**
	 * Deletes Person Data.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function delete_person_data( WP_REST_Request $request ) {
		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$data                         = $request->get_param( 'data' );
		$product_id                   = sanitize_text_field( self::check( $data, 'product_id' ) );
		$id                           = (int) sanitize_text_field( self::check( $data, 'id' ) );
		$person_data                  = get_post_meta( $product_id, '_bkap_person_data', true );
		$person_ids                   = get_post_meta( $product_id, '_bkap_person_ids', true );
		$woocommerce_booking_settings = get_post_meta( $product_id, 'woocommerce_booking_settings', true );

		if ( isset( $person_data[ $id ] ) && in_array( $id, $person_ids ) ) {
			unset( $person_data[ $id ] );
			unset( $person_ids[ array_search( $id, $person_ids ) ] );
			$woocommerce_booking_settings['bkap_person_data'] = $person_data;
			$woocommerce_booking_settings['bkap_person_ids']  = $person_ids;

			update_post_meta( $product_id, '_bkap_person_data', $person_data );
			update_post_meta( $product_id, '_bkap_person_ids', $person_ids );
			update_post_meta( $product_id, 'woocommerce_booking_settings', $woocommerce_booking_settings );

			return self::response( 'success', array( 'message' => __( 'Persons data has been deleted.', 'woocommerce-booking' ) ) );
		}

		return self::response( 'error', array( 'error_description' => __( 'Error. Persons data could not be deleted.', 'woocommerce-booking' ) ) );
	}

	/**
	 * Deletes ALL Persons Data.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function delete_all_person_data( WP_REST_Request $request ) {
		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$data                         = $request->get_param( 'data' );
		$product_id                   = sanitize_text_field( self::check( $data, 'product_id' ) );
		$woocommerce_booking_settings = get_post_meta( $product_id, 'woocommerce_booking_settings', true );
		$woocommerce_booking_settings['bkap_person_data'] = array();
		$woocommerce_booking_settings['bkap_person_ids']  = array();

		update_post_meta( $product_id, '_bkap_person_data', '' );
		update_post_meta( $product_id, '_bkap_person_ids', '' );
		update_post_meta( $product_id, 'woocommerce_booking_settings', $woocommerce_booking_settings );

		return self::response( 'success', array( 'message' => __( 'ALL Persons data have been deleted.', 'woocommerce-booking' ) ) );
	}

	/**
	 * Returns all created Resources.
	 *
	 * @since 5.19.0
	 */
	public static function return_resources() {
		$args = array(
			'post_type'      => 'bkap_resource',
			'posts_per_page' => -1,
		);

		// Show vendors to their own resources.
		if ( ! is_admin() && apply_filters( 'bkap_show_resource_created_by_user', true ) ) {
			$args['author'] = get_current_user_id();
		}

		$resources = array( 'new_resource' => __( 'Add new resource', 'woocommerce-booking' ) );
		$posts     = get_posts( $args );

		foreach ( $posts as $post ) {
			$resources[ $post->ID ] = $post->post_title . ' - #' . $post->ID;
		}

		return $resources;
	}

	/**
	 * Returns Resource data.
	 *
	 * @param array $product_id Product ID.
	 * @param array $default_booking_settings Default Booking Settings.
	 * @param bool  $has_defaults If the Default Booking has been set.
	 *
	 * @since 5.19.0
	 */
	public static function return_resource_data( $product_id, $default_booking_settings, $has_defaults ) {
		$settings               = array();
		$product_resources      = bkap_get_post_meta_data( $product_id, '_bkap_product_resources', $default_booking_settings, $has_defaults );
		$product_resource_costs = bkap_get_post_meta_data( $product_id, '_bkap_resource_base_costs', $default_booking_settings, $has_defaults );

		if ( is_array( $product_resources ) && count( $product_resources ) > 0 && is_array( $product_resource_costs ) && count( $product_resource_costs ) > 0 ) {
			foreach ( $product_resources as $resource_id ) {
				if ( get_post_status( $resource_id ) ) {
					$settings[] = array(
						'resource_id'    => $resource_id,
						'resource_title' => '',
						'base_cost'      => isset( $product_resource_costs[ $resource_id ] ) ? $product_resource_costs[ $resource_id ] : '',
						'url'            => apply_filters( 'bkap_resource_link_booking_metabox', admin_url( 'post.php?post=' . $resource_id . '&action=edit' ), $resource_id ),
						'edit'           => false,
						'is_new_row'     => false,
					);
				}
			}
		}

		return $settings;
	}

	/**
	 * Deletes Resource Data.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function delete_resource_data( WP_REST_Request $request ) {
		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$data                         = $request->get_param( 'data' );
		$product_id                   = sanitize_text_field( self::check( $data, 'product_id' ) );
		$id                           = (int) sanitize_text_field( self::check( $data, 'id' ) );
		$product_resources            = get_post_meta( $product_id, '_bkap_product_resources', true );
		$product_resource_base_costs  = get_post_meta( $product_id, '_bkap_resource_base_costs', true );
		$woocommerce_booking_settings = get_post_meta( $product_id, 'woocommerce_booking_settings', true );

		if ( in_array( $id, $product_resources ) && isset( $product_resource_base_costs[ $id ] ) ) {
			unset( $product_resource_base_costs[ $id ] );
			unset( $product_resources[ array_search( $id, $product_resources ) ] );
			$woocommerce_booking_settings['_bkap_product_resources']   = $product_resources;
			$woocommerce_booking_settings['_bkap_resource_base_costs'] = $product_resource_base_costs;

			update_post_meta( $product_id, '_bkap_product_resources', $product_resources );
			update_post_meta( $product_id, '_bkap_resource_base_costs', $product_resource_base_costs );
			update_post_meta( $product_id, 'woocommerce_booking_settings', $woocommerce_booking_settings );

			return self::response( 'success', array( 'message' => __( 'Linked Resource data has been deleted.', 'woocommerce-booking' ) ) );
		}

		return self::response( 'error', array( 'error_description' => __( 'Error. Linked Resource data could not be deleted.', 'woocommerce-booking' ) ) );
	}

	/**
	 * Deletes ALL Resource Data.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function delete_all_resource_data( WP_REST_Request $request ) {
		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$data                         = $request->get_param( 'data' );
		$product_id                   = sanitize_text_field( self::check( $data, 'product_id' ) );
		$woocommerce_booking_settings = get_post_meta( $product_id, 'woocommerce_booking_settings', true );
		$woocommerce_booking_settings['_bkap_product_resources']   = array();
		$woocommerce_booking_settings['_bkap_resource_base_costs'] = array();

		update_post_meta( $product_id, '_bkap_product_resources', '' );
		update_post_meta( $product_id, '_bkap_resource_base_costs', '' );
		update_post_meta( $product_id, 'woocommerce_booking_settings', $woocommerce_booking_settings );

		return self::response( 'success', array( 'message' => __( 'ALL Linked Resource  data have been deleted.', 'woocommerce-booking' ) ) );
	}

	/**
	 * Update Post Meta.
	 *
	 * @param int    $id ID.
	 * @param string $key Key.
	 * @param mixed  $value Value.
	 *
	 * @since 5.19.0
	 */
	public static function update_post_meta( $id, $key, $value ) {
		self::$all_settings[ $key ] = $value;
		update_post_meta( $id, $key, $value );
	}

	/**
	 * Fetch Booking Setings to be copied.
	 *
	 * @param int $product_id Product ID.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function fetch_booking_settings_copy( $product_id ) {
		global $wpdb;

		if ( 0 === $product_id ) {
			return '';
		}

		$settings = '';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
		$get_data = 'bulk_settings' === $product_id ? get_option( 'bkap_default_individual_booking_settings' ) : $wpdb->get_results( $wpdb->prepare( 'SELECT meta_key, meta_value FROM `' . $wpdb->prefix . 'postmeta` WHERE post_id = %d AND meta_key like %s', bkap_common::bkap_get_product_id( $product_id ), '%bkap_%' ) );

		if ( is_array( $get_data ) && count( $get_data ) > 0 ) {
			foreach ( $get_data as $key => $value ) {
				$settings .= ( 'bulk_settings' === $product_id ? $key : $value->meta_key ) . ': ' . ( 'bulk_settings' === $product_id ? ( ( '' !== $value ) ? serialize( $value ) : '' ) : $value->meta_value ) . PHP_EOL;
			}
		}

		return $settings;
	}
}
