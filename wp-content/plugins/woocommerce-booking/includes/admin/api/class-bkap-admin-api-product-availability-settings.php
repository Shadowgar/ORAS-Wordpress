<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * REST API for Product Availability Settings.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/API/Product_Availability_Settings
 * @category    Classes
 * @since       5.19.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * BKAP REST API.
 *
 * @since 5.19.0
 */
class BKAP_Admin_API_Product_Availability_Settings extends BKAP_Admin_API {

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

		// Save Bulk Booking Settings data.
		register_rest_route(
			self::$base_endpoint,
			'product-availability-settings/execute-added-action',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'execute_added_action' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);
	}

	/**
	 * Return Bulk Booking Setting Data.
	 *
	 * @param bool $return_raw Whether to return the Raw response.
	 *
	 * @since 5.19.0
	 */
	public static function fetch_product_availability_settings_data( $return_raw = false ) {
		$select_product_data = array(
			0 => __( 'All Products', 'woocommerce-booking' ),
		);

		foreach ( BKAP_Admin_API_Bulk_Booking_Settings::get_all_products() as $item_id ) {
			$select_product_data[ $item_id ] = get_the_title( $item_id );
		}

		$response = array(
			'settings'            => array(
				'product_id'  => array(),
				'action_data' => array(),
			),
			'select_product_data' => $select_product_data,
			'list'                => array(
				'day_date'   => array(
					'day'  => __( 'Day', 'woocommerce-booking' ),
					'date' => __( 'Date', 'woocommerce-booking' ),
				),
				'days_dates' => array(
					'all' => __( 'All', 'woocommerce-booking' ),
					'0'   => __( 'Sunday', 'woocommerce-booking' ),
					'1'   => __( 'Monday', 'woocommerce-booking' ),
					'2'   => __( 'Tuesday', 'woocommerce-booking' ),
					'3'   => __( 'Wednesday', 'woocommerce-booking' ),
					'4'   => __( 'Thursday', 'woocommerce-booking' ),
					'5'   => __( 'Friday', 'woocommerce-booking' ),
					'6'   => __( 'Saturday', 'woocommerce-booking' ),
				),
				'actions'    => array(
					'add'    => __( 'Add', 'woocommerce-booking' ),
					'update' => __( 'Update', 'woocommerce-booking' ),
					'delete' => __( 'Delete', 'woocommerce-booking' ),
				),
			),
			'validation_messages' => array(
				'timeslot_validation'   => __( 'The FROM Weekday timeslot must be less than the TO timeslot.', 'woocommerce-booking' ),
				'some_fields_are_empty' => __( 'Some fields have been left empty. Please try agasin.', 'woocommerce-booking' ),
			),
			'titles'              => array(
				'timeslots_from_to' => __( 'Please enter time in 24 hour format e.g 14:00 or 03:00', 'woocommerce-booking' ),
			),
			'placeholders'        => array(
				'max_booking' => __( 'Max booking', 'woocommerce-booking' ),
				'price'       => __( 'Price', 'woocommerce-booking' ),
			),
			'label'               => array(
				'execute_added_action_button'  => __( 'Execute Added Action(s)', 'woocommerce-booking' ),
				'text_executing_action_loader' => __( 'Executing Added Action(s), please wait...', 'woocommerce-booking' ),
			),
		);

		return self::return_response( $response, $return_raw );
	}

	/**
	 * Bulk Booking Settings -> Manage availability of products. This function will execute the data and perform the operations based on the added data.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function execute_added_action( WP_REST_Request $request ) {
		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$data        = $request->get_param( 'data' );
		$action_data = self::check( $data, 'action_data', array() );
		$product_ids = self::check( $data, 'product_id', array() );

		if ( in_array( '0', $product_ids, true ) ) {
			$product_ids = array();

			foreach ( BKAP_Admin_API_Bulk_Booking_Settings::get_all_products() as $item_id ) {
				$product_ids[] = $item_id;
			}
		}

		if ( ! is_array( $product_ids ) || ( is_array( $product_ids ) && 0 === count( $product_ids ) ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Error populating the selected Product IDs. Please try again.', 'woocommerce-booking' ) ) );
		}

		if ( ! is_array( $action_data ) || ( is_array( $action_data ) && 0 === count( $action_data ) ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Error populating the provided Action Data. Please try again.', 'woocommerce-booking' ) ) );
		}

		foreach ( $product_ids as $product_id ) {

			$booking_type = bkap_type( $product_id );

			foreach ( $action_data as $item ) {
				$booking_settings      = bkap_setting( $product_id );
				$evalue                = new stdClass();
				$evalue->bulk_action   = self::check( $item, 'action', '' );
				$evalue->bulk_day_date = self::check( $item, 'day_date', '' );
				if ( 'day' === $evalue->bulk_day_date ) {
					$evalue->bulk_day_date_value = self::check( $item, 'days_dates', '' );
				} else {
					$evalue->bulk_day_date_value = self::check( $item, 'selectedDate', '' );
				}

				$evalue->bulk_from_time    = self::check( $item, 'from_time', '' );
				$evalue->bulk_to_time      = self::check( $item, 'to_time', '' );
				$evalue->bulk_lockout_slot = self::check( $item, 'max_booking', '' );
				$evalue->bulk_slot_price   = self::check( $item, 'price', '' );
				$evalue->bulk_note         = self::check( $item, 'note', '' );

				switch ( $evalue->bulk_action ) {
					case 'add':
						self::bkap_bulk_add_execution( $product_id, $booking_settings, $evalue, $booking_type );
						break;
					case 'update':
						self::bkap_bulk_update_execution( $product_id, $booking_settings, $evalue, $booking_type );
						break;
					case 'delete':
						self::bkap_bulk_delete_execution( $product_id, $booking_settings, $evalue, $booking_type );
						break;
				}
			}
		}

		return self::response( 'success', array( 'message' => __( 'Actions have been successfully executed.', 'woocommerce-booking' ) ) );
	}

	/**
	 * Updating already available availability data of the product.
	 *
	 * @param int    $product_id Product ID.
	 * @param array  $booking_settings Booking Settings.
	 * @param obj    $execution_data Object of data to be added.
	 * @param string $booking_type Type of Booking.
	 * @since 4.16.0
	 */
	public static function bkap_bulk_update_execution( $product_id, $booking_settings, $execution_data, $booking_type ) {

		switch ( $booking_type ) {
			case 'only_day':
				self::bkap_bulk_update_execution_only_day( $product_id, $booking_settings, $execution_data, $booking_type );
				break;
			case 'date_time':
				if ( '' !== $execution_data->bulk_from_time ) {
					self::bkap_bulk_update_execution_date_time( $product_id, $booking_settings, $execution_data, $booking_type );
				} else {
					self::bkap_bulk_update_execution_only_day( $product_id, $booking_settings, $execution_data, $booking_type );
				}
				break;
			case 'duration_time':
				self::bkap_bulk_update_execution_only_day( $product_id, $booking_settings, $execution_data, $booking_type );
				break;
			case 'multiple_days':
				self::bkap_bulk_update_execution_only_day( $product_id, $booking_settings, $execution_data, $booking_type );
				break;
		}
	}

	/**
	 * This function is for update action of date & time booking type.
	 *
	 * @param int    $product_id Product ID.
	 * @param array  $booking_settings Booking Settings.
	 * @param obj    $execution_data Object of data to be added.
	 * @param string $booking_type Type of Booking.
	 * @since 4.16.0
	 */
	public static function bkap_bulk_update_execution_date_time( $product_id, $booking_settings, $execution_data, $booking_type ) {

		if ( '' !== $execution_data->bulk_from_time ) {
			$bulk_day_date_value   = $execution_data->bulk_day_date_value;
			$booking_time_settings = $booking_settings['booking_time_settings'];
			$settings_data         = array( '_bkap_time_settings' => array() );

			$from_time_explode = explode( ':', $execution_data->bulk_from_time );
			if ( '' === $execution_data->bulk_to_time ) {
				$to_time_explode = explode( ':', '0:00' );
			} else {
				$to_time_explode = explode( ':', $execution_data->bulk_to_time );
			}

			switch ( $execution_data->bulk_day_date ) {
				case 'day':
					$booking_recurring = array();
					$recurring_lockout = array();
					$recurring_prices  = array();

					if ( in_array( 'all', $bulk_day_date_value, true ) ) { // if user selected all.

						/* Enabling the weekday if its not enable */
						for ( $i = 0; $i <= 6; $i++ ) {
							$weekday_name = "booking_weekday_$i";

							if ( isset( $booking_time_settings[ $weekday_name ] ) ) {

								foreach ( $booking_time_settings[ $weekday_name ] as $key => $value ) {

									if ( $value['from_slot_hrs'] === $from_time_explode[0]
										&& $value['from_slot_min'] === $from_time_explode[1]
										&& $value['to_slot_hrs'] === $to_time_explode[0]
										&& $value['to_slot_min'] === $to_time_explode[1]
									) {
										$booking_time_settings[ $weekday_name ][ $key ]['slot_price']   = $execution_data->bulk_slot_price;
										$booking_time_settings[ $weekday_name ][ $key ]['lockout_slot'] = $execution_data->bulk_lockout_slot;
										$settings_data['_bkap_time_settings'][ $weekday_name ]          = array();
										array_push( $settings_data['_bkap_time_settings'][ $weekday_name ], $booking_time_settings[ $weekday_name ][ $key ] );
										break;
									}
								}
							}
						}
					} else {

						foreach ( $bulk_day_date_value as $key => $value ) {
							$weekday_name = "booking_weekday_$value";

							if ( isset( $booking_time_settings[ $weekday_name ] ) ) {
								foreach ( $booking_time_settings[ $weekday_name ] as $key => $value ) {
									if ( $value['from_slot_hrs'] === $from_time_explode[0]
										&& $value['from_slot_min'] === $from_time_explode[1]
										&& $value['to_slot_hrs'] === $to_time_explode[0]
										&& $value['to_slot_min'] === $to_time_explode[1]
									) {
										$booking_time_settings[ $weekday_name ][ $key ]['slot_price']   = $execution_data->bulk_slot_price;
										$booking_time_settings[ $weekday_name ][ $key ]['lockout_slot'] = $execution_data->bulk_lockout_slot;
										$settings_data['_bkap_time_settings'][ $weekday_name ]          = array();
										array_push( $settings_data['_bkap_time_settings'][ $weekday_name ], $booking_time_settings[ $weekday_name ][ $key ] );
										break;
									}
								}
							}
						}
					}

					break;
				case 'date':
					$specific_dates        = explode( ',', $bulk_day_date_value );
					$booking_specific_date = $booking_settings['booking_specific_date'];
					$date_price            = array();

					foreach ( $specific_dates as $key => $value ) {
						if ( isset( $booking_specific_date[ $value ] ) ) {
							$booking_specific_date[ $value ] = $execution_data->bulk_lockout_slot;
							$date_price[ $value ]            = $execution_data->bulk_slot_price;
						}

						if ( isset( $booking_time_settings[ $value ] ) ) { // product already having the date settings.
							foreach ( $booking_time_settings[ $value ] as $k => $v ) {
								if ( $v['from_slot_hrs'] === $from_time_explode[0]
									&& $v['from_slot_min'] === $from_time_explode[1]
									&& $v['to_slot_hrs'] === $to_time_explode[0]
									&& $v['to_slot_min'] === $to_time_explode[1]
								) {
									$booking_time_settings[ $value ][ $k ]['lockout_slot'] = $execution_data->bulk_lockout_slot;
									$booking_time_settings[ $value ][ $k ]['slot_price']   = $execution_data->bulk_slot_price;

									$settings_data['_bkap_time_settings'][ $value ] = array();
									array_push( $settings_data['_bkap_time_settings'][ $value ], $booking_time_settings[ $value ][ $k ] );
									break;
								}
							}
						}
					}
					break;
			}

			if ( count( $settings_data['_bkap_time_settings'] ) > 0 ) {
				$booking_settings['booking_time_settings'] = $booking_time_settings;
				$settings_data['_bkap_time_settings']      = $booking_time_settings;
				update_post_meta( $product_id, 'woocommerce_booking_settings', $booking_settings );
				update_post_meta( $product_id, '_bkap_time_settings', $booking_time_settings );
				bkap_update_booking_history( $product_id, $settings_data, $booking_type );
			}
		}
	}

	/**
	 * Updating the data for the selected day date ; Single Day
	 *
	 * @param int    $product_id Product ID.
	 * @param array  $booking_settings Booking Settings.
	 * @param obj    $execution_data Object of data to be added.
	 * @param string $booking_type Type of Booking.
	 */
	public static function bkap_bulk_update_execution_only_day( $product_id, $booking_settings, $execution_data, $booking_type ) {

		$bulk_day_date_value = $execution_data->bulk_day_date_value;

		switch ( $execution_data->bulk_day_date ) {
			case 'day':
				$booking_recurring = array();
				$recurring_lockout = array();
				$recurring_prices  = array();
				$settings_data     = array();

				$old_lockout = get_post_meta( $product_id, '_bkap_recurring_lockout', true );

				if ( in_array( 'all', $bulk_day_date_value, true ) ) {

					/* Enabling the weekday if its not enable */
					for ( $i = 0; $i <= 6; $i++ ) {
						$weekday_name = "booking_weekday_$i";

						if ( 'on' === $booking_settings['booking_recurring'][ $weekday_name ] ) {
							$recurring_lockout[ $weekday_name ] = $execution_data->bulk_lockout_slot;
							$recurring_prices[ $weekday_name ]  = $execution_data->bulk_slot_price;
						}
					}
				} else {
					foreach ( $bulk_day_date_value as $key => $value ) {
						$weekday_name = "booking_weekday_$value";
						if ( 'on' === $booking_settings['booking_recurring'][ $weekday_name ] ) {
							$recurring_lockout[ $weekday_name ] = $execution_data->bulk_lockout_slot;
							$recurring_prices[ $weekday_name ]  = $execution_data->bulk_slot_price;
						}
					}
				}

				$new_special_price = self::bkap_bulk_special_price( $product_id, $booking_settings, $recurring_prices, 'day' );

				$settings_data['_bkap_recurring_weekdays'] = $booking_settings['booking_recurring'];
				$settings_data['_bkap_specific_dates']     = array();

				if ( 'only_day' === $booking_type || 'date_time' === $booking_type ) {
					$new_lockout                                   = array_merge( $old_lockout, $recurring_lockout );
					$booking_settings['booking_recurring_lockout'] = $new_lockout;
					$settings_data['_bkap_recurring_lockout']      = $new_lockout;
					update_post_meta( $product_id, '_bkap_recurring_lockout', $new_lockout );
					if ( 'only_day' === $booking_type ) {
						bkap_update_booking_history( $product_id, $settings_data, $booking_type ); // Updating records..
					}
				}

				update_post_meta( $product_id, 'woocommerce_booking_settings', $booking_settings );
				update_post_meta( $product_id, '_bkap_special_price', $new_special_price );

				break;
			case 'date':
				$specific_dates        = explode( ',', $bulk_day_date_value );
				$booking_specific_date = $booking_settings['booking_specific_date'];
				$date_price            = array();
				$settings_data         = array();

				foreach ( $specific_dates as $key => $value ) {
					if ( isset( $booking_specific_date[ $value ] ) ) {
						$booking_specific_date[ $value ] = $execution_data->bulk_lockout_slot;
						$date_price[ $value ]            = $execution_data->bulk_slot_price;
					}
				}

				if ( 'multiple_days' !== $booking_type ) { // do not add specific date in db when product type is multiple nights.
					$booking_settings['booking_specific_date'] = $booking_specific_date;
				}

				$settings_data['_bkap_specific_dates']     = $booking_specific_date; // got old plus new specifc date with lockout.
				$settings_data['_bkap_recurring_lockout']  = array();
				$settings_data['_bkap_recurring_weekdays'] = array();

				/* Genrating the special price data based on new specific date price*/
				$new_special_price = self::bkap_bulk_special_price( $product_id, $booking_settings, $date_price, 'date' );

				if ( 'only_day' === $booking_type ) {
					bkap_update_booking_history( $product_id, $settings_data, $booking_type ); // Updating records..
				}
				update_post_meta( $product_id, 'woocommerce_booking_settings', $booking_settings );

				if ( 'multiple_days' !== $booking_type ) {
					update_post_meta( $product_id, '_bkap_specific_dates', $booking_specific_date );
				}

				update_post_meta( $product_id, '_bkap_special_price', $new_special_price );
				break;
		}
	}

	/**
	 * Adding availability to selected product.
	 *
	 * @param int    $product_id Product ID.
	 * @param array  $booking_settings Booking Settings.
	 * @param obj    $execution_data Object of data to be added.
	 * @param string $booking_type Type of booking.
	 *
	 * @since 4.16.0
	 */
	public static function bkap_bulk_add_execution( $product_id, $booking_settings, $execution_data, $booking_type ) {

		switch ( $booking_type ) {
			case 'only_day':
				self::bkap_bulk_add_execution_only_day( $product_id, $booking_settings, $execution_data, $booking_type );
				break;
			case 'date_time':
				if ( '' !== $execution_data->bulk_from_time ) {
					self::bkap_bulk_add_execution_date_time( $product_id, $booking_settings, $execution_data, $booking_type );
				} else {
					self::bkap_bulk_add_execution_only_day( $product_id, $booking_settings, $execution_data, $booking_type );
				}
				break;
			case 'duration_time':
				self::bkap_bulk_add_execution_only_day( $product_id, $booking_settings, $execution_data, $booking_type );
				break;
			case 'multiple_days':
				self::bkap_bulk_add_execution_only_day( $product_id, $booking_settings, $execution_data, $booking_type );
				break;
		}
	}

	/**
	 * Adding the availability for Single Day booking type.
	 *
	 * @param int    $product_id Product ID.
	 * @param array  $booking_settings Booking Settings.
	 * @param obj    $execution_data Object of data to be added.
	 * @param string $booking_type Type of booking.
	 *
	 * @since 4.16.0
	 */
	public static function bkap_bulk_add_execution_only_day( $product_id, $booking_settings, $execution_data, $booking_type ) {

		$bulk_day_date_value = $execution_data->bulk_day_date_value;

		switch ( $execution_data->bulk_day_date ) {
			case 'day':
				$booking_recurring = array();
				$recurring_lockout = array();
				$recurring_prices  = array();
				$settings_data     = array();
				$old_lockout       = get_post_meta( $product_id, '_bkap_recurring_lockout', true );

				if ( in_array( 'all', $bulk_day_date_value, true ) ) {

					/* Enabling the weekday if its not enable */
					for ( $i = 0; $i <= 6; $i++ ) {
						$weekday_name = "booking_weekday_$i";

						if ( 'on' !== $booking_settings['booking_recurring'][ $weekday_name ] ) {
							$booking_recurring[ $weekday_name ] = 'on';
							$recurring_lockout[ $weekday_name ] = $execution_data->bulk_lockout_slot;
							$recurring_prices[ $weekday_name ]  = $execution_data->bulk_slot_price;
						}
					}
				} else {
					foreach ( $bulk_day_date_value as $key => $value ) {
						$weekday_name = "booking_weekday_$value";
						if ( 'on' !== $booking_settings['booking_recurring'][ $weekday_name ] ) {
							$booking_recurring[ $weekday_name ] = 'on';
							$recurring_lockout[ $weekday_name ] = $execution_data->bulk_lockout_slot;
							$recurring_prices[ $weekday_name ]  = $execution_data->bulk_slot_price;
						}
					}
				}

				$new_recurring     = array_merge( $booking_settings['booking_recurring'], $booking_recurring );
				$new_special_price = self::bkap_bulk_special_price( $product_id, $booking_settings, $recurring_prices, 'day' );

				if ( isset( $booking_settings['booking_recurring_booking'] ) && '' === $booking_settings['booking_recurring_booking'] ) {
					if ( in_array( 'on', $new_recurring, true ) ) {
						$booking_settings['booking_recurring_booking'] = 'on';
						update_post_meta( $product_id, '_bkap_enable_recurring', 'on' );
					}
				}

				// Changing the infomration in booking settings.
				$booking_settings['booking_recurring']     = $new_recurring;
				$settings_data['_bkap_recurring_weekdays'] = $new_recurring;
				$settings_data['_bkap_specific_dates']     = array();

				if ( 'only_day' === $booking_type || 'date_time' === $booking_type ) {
					$new_lockout                                   = array_merge( $old_lockout, $recurring_lockout );
					$booking_settings['booking_recurring_lockout'] = $new_lockout;
					$settings_data['_bkap_recurring_lockout']      = $new_lockout;
					update_post_meta( $product_id, '_bkap_recurring_lockout', $new_lockout );

					if ( 'only_day' === $booking_type ) {
						bkap_update_booking_history( $product_id, $settings_data, $booking_type ); // Updating records..
					}
				}

				update_post_meta( $product_id, 'woocommerce_booking_settings', $booking_settings );
				update_post_meta( $product_id, '_bkap_recurring_weekdays', $new_recurring );
				update_post_meta( $product_id, '_bkap_special_price', $new_special_price );
				break;
			case 'date':
				$specific_dates        = explode( ',', $bulk_day_date_value );
				$booking_specific_date = $booking_settings['booking_specific_date'];
				$date_price            = array();
				$settings_data         = array();

				foreach ( $specific_dates as $key => $value ) {
					if ( ! isset( $booking_specific_date[ $value ] ) ) {
						$booking_specific_date[ $value ] = $execution_data->bulk_lockout_slot;
						$date_price[ $value ]            = $execution_data->bulk_slot_price;
					}
				}

				if ( 'multiple_days' !== $booking_type ) { // do not add specific date in db when product type is multiple nights.
					$booking_settings['booking_specific_date'] = $booking_specific_date;
				}

				$settings_data['_bkap_specific_dates']     = $booking_specific_date; // got old plus new specifc date with lockout.
				$settings_data['_bkap_recurring_lockout']  = array();
				$settings_data['_bkap_recurring_weekdays'] = array();

				if ( '' === $booking_settings['booking_specific_booking'] && ( ! empty( $booking_specific_date ) ) ) {
					$booking_settings['booking_specific_booking'] = 'on'; // enabled the specific date option is specific date are available.
					update_post_meta( $product_id, '_bkap_enable_specific', 'on' );
				}

				/* Genrating the special price data based on new specific date price*/
				$new_special_price = self::bkap_bulk_special_price( $product_id, $booking_settings, $date_price, 'date' );

				if ( 'only_day' === $booking_type ) {
					bkap_update_booking_history( $product_id, $settings_data, $booking_type ); // Updating records..
				}

				update_post_meta( $product_id, 'woocommerce_booking_settings', $booking_settings );

				if ( 'multiple_days' !== $booking_type ) {
					update_post_meta( $product_id, '_bkap_specific_dates', $booking_specific_date );
				}

				update_post_meta( $product_id, '_bkap_special_price', $new_special_price );
				break;
		}
	}

	/**
	 * Getting and adding new special price infomration in the post meta.
	 *
	 * @param int    $product_id Product ID.
	 * @param array  $booking_settings Booking Settings.
	 * @param array  $recurring_prices Array of recurring prices.
	 * @param string $daydate Date or Day.
	 *
	 * @since 4.16.0
	 */
	public static function bkap_bulk_special_price( $product_id, $booking_settings, $recurring_prices, $daydate ) {
		// Get the existing record.
		$booking_special_prices = get_post_meta( $product_id, '_bkap_special_price', true );

		switch ( $daydate ) {
			case 'day':
				foreach ( $recurring_prices as $k => $v ) {
					$found = false;
					foreach ( $booking_special_prices as $key => $value ) {
						if ( $value['booking_special_weekday'] === $k ) {
							$booking_special_prices[ $key ]['booking_special_price'] = $v;
							$found = true;
							break;
						}
					}
					if ( ! $found ) {

						if ( empty( $booking_special_prices ) ) {
							$cnt = 0;
						} else {
							$cnt = max( array_keys( $booking_special_prices ) ) + 1; // calculation new key.
						}
						$booking_special_prices[ $cnt ]['booking_special_weekday'] = $k;
						$booking_special_prices[ $cnt ]['booking_special_date']    = '';
						$booking_special_prices[ $cnt ]['booking_special_price']   = $v;
					}
				}
				break;
			case 'date':
				foreach ( $recurring_prices as $k => $v ) {
					$found = false;
					foreach ( $booking_special_prices as $key => $value ) {
						if ( strtotime( $value['booking_special_date'] ) === strtotime( $k ) ) {
							$booking_special_prices[ $key ]['booking_special_price'] = $v;
							$found = true;
							break;
						}
					}

					if ( ! $found ) {

						if ( empty( $booking_special_prices ) ) {
							$cnt = 0;
						} else {
							$cnt = max( array_keys( $booking_special_prices ) ) + 1; // calculation new key.
						}
						$booking_special_prices[ $cnt ]['booking_special_weekday'] = '';
						$booking_special_prices[ $cnt ]['booking_special_date']    = gmdate( 'Y-m-d', strtotime( $k ) );
						$booking_special_prices[ $cnt ]['booking_special_price']   = $v;
					}
				}
				break;
		}

		return $booking_special_prices;
	}

	/**
	 * Adding availability to date and time product
	 *
	 * @param int    $product_id Product ID.
	 * @param array  $booking_settings Booking Settings.
	 * @param obj    $execution_data Object of data to be added.
	 * @param string $booking_type Type of booking.
	 *
	 * @since 4.16.0
	 */
	public static function bkap_bulk_add_execution_date_time( $product_id, $booking_settings, $execution_data, $booking_type ) {

		if ( '' !== $execution_data->bulk_from_time ) {

			$bulk_day_date_value   = $execution_data->bulk_day_date_value;
			$booking_time_settings = $booking_settings['booking_time_settings'];

			$from_time_explode = explode( ':', $execution_data->bulk_from_time );
			if ( '' === $execution_data->bulk_to_time ) {
				$to_time_explode = explode( ':', '0:00' );
			} else {
				$to_time_explode = explode( ':', $execution_data->bulk_to_time );
			}

			switch ( $execution_data->bulk_day_date ) {
				case 'day':
					$booking_recurring = array();
					$recurring_lockout = array();
					$recurring_prices  = array();
					$settings_data     = array();

					if ( in_array( 'all', $bulk_day_date_value, true ) ) { // if user selected all.

						/* Enabling the weekday if its not enable */
						for ( $i = 0; $i <= 6; $i++ ) {
							$weekday_name = "booking_weekday_$i";

							if ( 'on' !== $booking_settings['booking_recurring'][ $weekday_name ] ) {
								$booking_recurring[ $weekday_name ] = 'on';
							}

							if ( isset( $booking_time_settings[ $weekday_name ] ) ) {

								// data available in time setting is sunday and monday 10:00 12:00 12:00 to 14:00
								// Adding new data is sunday 14:00 to 16:00.
								$found = false;
								foreach ( $booking_time_settings[ $weekday_name ] as $key => $value ) {
									if ( $value['from_slot_hrs'] === $from_time_explode[0]
										&& $value['from_slot_min'] === $from_time_explode[1]
										&& $value['to_slot_hrs'] === $to_time_explode[0]
										&& $value['to_slot_min'] === $to_time_explode[1]
									) {
										$found = true;
										break;
									}
								}

								if ( ! $found ) { // This mean the timeslot is not present in the booking time settings.
									$time['from_slot_hrs']     = $from_time_explode[0];
									$time['from_slot_min']     = $from_time_explode[1];
									$time['to_slot_hrs']       = $to_time_explode[0];
									$time['to_slot_min']       = $to_time_explode[1];
									$time['booking_notes']     = $execution_data->bulk_note;
									$time['slot_price']        = $execution_data->bulk_slot_price;
									$time['lockout_slot']      = $execution_data->bulk_lockout_slot;
									$time['global_time_check'] = false;
									array_push( $booking_time_settings[ $weekday_name ], $time );
								}
							} else {
								// This mean any data for the weekday is not present.
								// add key as weekday and timeslot array to booking_time_settings.

								$time['from_slot_hrs']     = $from_time_explode[0];
								$time['from_slot_min']     = $from_time_explode[1];
								$time['to_slot_hrs']       = $to_time_explode[0];
								$time['to_slot_min']       = $to_time_explode[1];
								$time['booking_notes']     = $execution_data->bulk_note;
								$time['slot_price']        = $execution_data->bulk_slot_price;
								$time['lockout_slot']      = $execution_data->bulk_lockout_slot;
								$time['global_time_check'] = false;

								$booking_time_settings[ $weekday_name ] = array( $time );
							}
						}
					} else {
						foreach ( $bulk_day_date_value as $key => $value ) {

							$weekday_name = "booking_weekday_$value";

							if ( 'on' !== $booking_settings['booking_recurring'][ $weekday_name ] ) {
								$booking_recurring[ $weekday_name ] = 'on';
							}

							if ( isset( $booking_time_settings[ $weekday_name ] ) ) {

								// data available in time setting is sunday and monday 10:00 12:00 12:00 to 14:00
								// Adding new data is sunday 14:00 to 16:00.
								$found = false;
								foreach ( $booking_time_settings[ $weekday_name ] as $key => $value ) {
									if ( $value['from_slot_hrs'] === $from_time_explode[0]
										&& $value['from_slot_min'] === $from_time_explode[1]
										&& $value['to_slot_hrs'] === $to_time_explode[0]
										&& $value['to_slot_min'] === $to_time_explode[1]
									) {
										$found = true;
										break;
									}
								}

								if ( ! $found ) { // This mean the timeslot is not present in the booking time settings.
									$time['from_slot_hrs']     = $from_time_explode[0];
									$time['from_slot_min']     = $from_time_explode[1];
									$time['to_slot_hrs']       = $to_time_explode[0];
									$time['to_slot_min']       = $to_time_explode[1];
									$time['booking_notes']     = $execution_data->bulk_note;
									$time['slot_price']        = $execution_data->bulk_slot_price;
									$time['lockout_slot']      = $execution_data->bulk_lockout_slot;
									$time['global_time_check'] = false;
									array_push( $booking_time_settings[ $weekday_name ], $time );
								}
							} else {
								// This mean any data for the weekday is not present
								// add key as weekday and timeslot array to booking_time_settings.

								$time['from_slot_hrs']     = $from_time_explode[0];
								$time['from_slot_min']     = $from_time_explode[1];
								$time['to_slot_hrs']       = $to_time_explode[0];
								$time['to_slot_min']       = $to_time_explode[1];
								$time['booking_notes']     = $execution_data->bulk_note;
								$time['slot_price']        = $execution_data->bulk_slot_price;
								$time['lockout_slot']      = $execution_data->bulk_lockout_slot;
								$time['global_time_check'] = false;

								$booking_time_settings[ $weekday_name ] = array( $time );
							}
						}
					}

					$new_recurring                             = array_merge( $booking_settings['booking_recurring'], $booking_recurring );
					$booking_settings['booking_recurring']     = $new_recurring; // assigning weekdays to booking settings.
					$booking_settings['booking_time_settings'] = $booking_time_settings;
					$settings_data['_bkap_time_settings']      = $booking_time_settings;

					bkap_update_booking_history( $product_id, $settings_data, $booking_type );
					update_post_meta( $product_id, 'woocommerce_booking_settings', $booking_settings );
					update_post_meta( $product_id, '_bkap_recurring_weekdays', $new_recurring );
					update_post_meta( $product_id, '_bkap_time_settings', $booking_time_settings );

					break;
				case 'date':
					$specific_dates        = explode( ',', $bulk_day_date_value );
					$booking_specific_date = $booking_settings['booking_specific_date'];
					$date_price            = array();
					$settings_data         = array();

					foreach ( $specific_dates as $key => $value ) {
						if ( ! isset( $booking_specific_date[ $value ] ) ) {
							$booking_specific_date[ $value ] = $execution_data->bulk_lockout_slot;
							$date_price[ $value ]            = $execution_data->bulk_slot_price;
						}

						if ( isset( $booking_time_settings[ $value ] ) ) { // product already having the date settings.
							$found = false;
							foreach ( $booking_time_settings[ $value ] as $k => $v ) {
								if ( $v['from_slot_hrs'] === $from_time_explode[0]
									&& $v['from_slot_min'] === $from_time_explode[1]
									&& $v['to_slot_hrs'] === $to_time_explode[0]
									&& $v['to_slot_min'] === $to_time_explode[1]
								) {
									$found = true;
									break;
								}
							}

							$time = array();

							if ( ! $found ) { // This mean the timeslot is not present in the booking time settings.
								$time['from_slot_hrs']     = $from_time_explode[0];
								$time['from_slot_min']     = $from_time_explode[1];
								$time['to_slot_hrs']       = $to_time_explode[0];
								$time['to_slot_min']       = $to_time_explode[1];
								$time['booking_notes']     = $execution_data->bulk_note;
								$time['slot_price']        = $execution_data->bulk_slot_price;
								$time['lockout_slot']      = $execution_data->bulk_lockout_slot;
								$time['global_time_check'] = false;
								array_push( $booking_time_settings[ $value ], $time );
							}
						} else {
							// Do not have any date setting for the product so add new to it.
							$time['from_slot_hrs']     = $from_time_explode[0];
							$time['from_slot_min']     = $from_time_explode[1];
							$time['to_slot_hrs']       = $to_time_explode[0];
							$time['to_slot_min']       = $to_time_explode[1];
							$time['booking_notes']     = $execution_data->bulk_note;
							$time['slot_price']        = $execution_data->bulk_slot_price;
							$time['lockout_slot']      = $execution_data->bulk_lockout_slot;
							$time['global_time_check'] = false;

							$booking_time_settings[ $value ] = array( $time );
						}
					}

					// booking_time_settings : this array is ready.

					$booking_settings['booking_specific_date'] = $booking_specific_date;
					$settings_data['_bkap_specific_dates']     = $booking_specific_date; // got old plus new specifc date with lockout.
					if ( '' === $booking_settings['booking_specific_booking'] && ( ! empty( $booking_specific_date ) ) ) {
						$booking_settings['booking_specific_booking'] = 'on'; // enabled the specific date option is specific date are available.
						update_post_meta( $product_id, '_bkap_enable_specific', 'on' );
					}

					$booking_settings['booking_time_settings'] = $booking_time_settings;
					$settings_data['_bkap_time_settings']      = $booking_time_settings;

					// The data we are passing to this function is for weekdays as well as for dates but this will need only dates so later please try to have only date data so that it will save the time by excluding the check of weekdays.
					bkap_update_booking_history( $product_id, $settings_data, $booking_type );
					update_post_meta( $product_id, 'woocommerce_booking_settings', $booking_settings );
					update_post_meta( $product_id, '_bkap_specific_dates', $booking_specific_date );
					update_post_meta( $product_id, '_bkap_time_settings', $booking_time_settings );

					break;
			}
		}
	}

	/**
	 * Deleting availability from selected product
	 *
	 * @param int    $product_id Product ID.
	 * @param array  $booking_settings Booking Settings.
	 * @param obj    $execution_data Object of data to be added.
	 * @param string $booking_type Type of booking.
	 *
	 * @since 4.16.0
	 */
	public static function bkap_bulk_delete_execution( $product_id, $booking_settings, $execution_data, $booking_type ) {

		switch ( $booking_type ) {
			case 'only_day':
				self::bkap_bulk_delete_execution_only_day( $product_id, $booking_settings, $execution_data, $booking_type );
				break;
			case 'date_time':
				if ( '' !== $execution_data->bulk_from_time ) {
					self::bkap_bulk_delete_execution_date_time( $product_id, $booking_settings, $execution_data, $booking_type );
				} else {
					self::bkap_bulk_delete_execution_only_day( $product_id, $booking_settings, $execution_data, $booking_type );
				}
				break;
			case 'duration_time':
				self::bkap_bulk_delete_execution_only_day( $product_id, $booking_settings, $execution_data, $booking_type );
				break;
			case 'multiple_days':
				self::bkap_bulk_delete_execution_only_day( $product_id, $booking_settings, $execution_data, $booking_type );
				break;
		}
	}

	/**
	 * Deleting availability from selected product of date and time type product
	 *
	 * @param int    $product_id Product ID.
	 * @param array  $booking_settings Booking Settings.
	 * @param obj    $execution_data Object of data to be added.
	 * @param string $booking_type Type of booking.
	 *
	 * @since 4.16.0
	 */
	public static function bkap_bulk_delete_execution_date_time( $product_id, $booking_settings, $execution_data, $booking_type ) {

		$from_time = $execution_data->bulk_from_time;

		if ( '' !== $from_time ) {

			$to_time               = $execution_data->bulk_to_time;
			$bulk_day_date_value   = $execution_data->bulk_day_date_value;
			$booking_time_settings = $booking_settings['booking_time_settings'];

			$from_time_explode = explode( ':', $from_time );
			if ( '' === $to_time ) {
				$to_time_explode = explode( ':', '0:00' );
			} else {
				$to_time_explode = explode( ':', $to_time );
			}

			switch ( $execution_data->bulk_day_date ) {
				case 'day':
					$booking_recurring = array();
					$recurring_lockout = array();
					$recurring_prices  = array();
					$settings_data     = array();

					if ( in_array( 'all', $bulk_day_date_value, true ) ) { // if user selected all.

						/* Enabling the weekday if its not enable */
						for ( $i = 0; $i <= 6; $i++ ) {
							$weekday_name = "booking_weekday_$i";

							if ( isset( $booking_time_settings[ $weekday_name ] ) ) {

								foreach ( $booking_time_settings[ $weekday_name ] as $key => $value ) {
									if ( $value['from_slot_hrs'] === $from_time_explode[0]
										&& $value['from_slot_min'] === $from_time_explode[1]
										&& $value['to_slot_hrs'] === $to_time_explode[0]
										&& $value['to_slot_min'] === $to_time_explode[1]
									) {
										unset( $booking_time_settings[ $weekday_name ][ $key ] );
										// deleting the record from database.
										bkap_delete_booking_history( $product_id, $weekday_name, $from_time, $to_time );
										break;
									}
								}
							}
						}
					} else {

						foreach ( $bulk_day_date_value as $key => $value ) {

							$weekday_name = "booking_weekday_$value";

							if ( isset( $booking_time_settings[ $weekday_name ] ) ) {
								foreach ( $booking_time_settings[ $weekday_name ] as $key => $value ) {
									if ( $value['from_slot_hrs'] === $from_time_explode[0]
										&& $value['from_slot_min'] === $from_time_explode[1]
										&& $value['to_slot_hrs'] === $to_time_explode[0]
										&& $value['to_slot_min'] === $to_time_explode[1]
									) {
										unset( $booking_time_settings[ $weekday_name ][ $key ] );
										// deleting the record from database.
										bkap_delete_booking_history( $product_id, $weekday_name, $from_time, $to_time );
										break;
									}
								}
							}
						}
					}

					$booking_settings['booking_time_settings'] = $booking_time_settings;
					$settings_data['_bkap_time_settings']      = $booking_time_settings;
					update_post_meta( $product_id, 'woocommerce_booking_settings', $booking_settings );
					update_post_meta( $product_id, '_bkap_time_settings', $booking_time_settings );

					break;
				case 'date':
					$specific_dates        = explode( ',', $bulk_day_date_value );
					$booking_specific_date = $booking_settings['booking_specific_date'];
					$date_price            = array();
					$settings_data         = array();

					foreach ( $specific_dates as $key => $value ) { // array of dates to be deleted from the product.

						if ( isset( $booking_time_settings[ $value ] ) ) { // product already having the date settings.
							foreach ( $booking_time_settings[ $value ] as $k => $v ) {
								if ( $v['from_slot_hrs'] === $from_time_explode[0]
									&& $v['from_slot_min'] === $from_time_explode[1]
									&& $v['to_slot_hrs'] === $to_time_explode[0]
									&& $v['to_slot_min'] === $to_time_explode[1]
								) {
									// add code to delete the specific date from db and also unset the data from array.
									unset( $booking_time_settings[ $value ][ $k ] );
									bkap_delete_booking_history( $product_id, $value, $from_time, $to_time );
									break;
								}
							}
						}
					}

					$booking_settings['booking_time_settings'] = $booking_time_settings;
					update_post_meta( $product_id, 'woocommerce_booking_settings', $booking_settings );
					update_post_meta( $product_id, '_bkap_time_settings', $booking_time_settings );

					break;
			}
		}
	}

	/**
	 * Deleting the availability from product for only day, duration time and multiple nights
	 *
	 * @param int    $product_id Product ID.
	 * @param array  $booking_settings Booking Settings.
	 * @param obj    $execution_data Object of data to be added.
	 * @param string $booking_type Type of booking.
	 *
	 * @since 4.16.0
	 */
	public static function bkap_bulk_delete_execution_only_day( $product_id, $booking_settings, $execution_data, $booking_type ) {

		$bulk_day_date_value = $execution_data->bulk_day_date_value;

		switch ( $execution_data->bulk_day_date ) {

			case 'day':
				$booking_recurring = array();
				$settings_data     = array();
				$old_lockout       = get_post_meta( $product_id, '_bkap_recurring_lockout', true );

				if ( in_array( 'all', $bulk_day_date_value, true ) ) {

					for ( $i = 0; $i <= 6; $i++ ) { /* Disabling the weekday if its not enable */
						$weekday_name = "booking_weekday_$i";
						if ( 'on' === $booking_settings['booking_recurring'][ $weekday_name ] ) {
							$booking_recurring[ $weekday_name ]                         = '';
							$settings_data['_bkap_recurring_weekdays'][ $weekday_name ] = ''; // preparing this so that it will help deleting the records.
							$settings_data['_bkap_recurring_lockout'][ $weekday_name ]  = ''; // preparing this so that it will help deleting the records.
						}
					}
				} else {
					$detele_recurring = array();
					foreach ( $bulk_day_date_value as $key => $value ) {
						$weekday_name = "booking_weekday_$value";
						if ( 'on' === $booking_settings['booking_recurring'][ $weekday_name ] ) {
							$booking_recurring[ $weekday_name ]                         = '';
							$settings_data['_bkap_recurring_weekdays'][ $weekday_name ] = '';
							$settings_data['_bkap_recurring_lockout'][ $weekday_name ]  = '';
						}
					}
				}

				$new_recurring                         = array_merge( $booking_settings['booking_recurring'], $booking_recurring );
				$booking_settings['booking_recurring'] = $new_recurring; // Changing the infomration in booking settings.

				if ( isset( $booking_settings['booking_recurring_booking'] ) && 'on' === $booking_settings['booking_recurring_booking'] ) {
					if ( ! in_array( 'on', $new_recurring, true ) ) {
						$booking_settings['booking_recurring_booking'] = '';
						update_post_meta( $product_id, '_bkap_enable_recurring', '' );
					}
				}

				if ( 'only_day' === $booking_type ) {
					bkap_update_booking_history( $product_id, $settings_data, $booking_type );
				}

				update_post_meta( $product_id, 'woocommerce_booking_settings', $booking_settings );
				update_post_meta( $product_id, '_bkap_recurring_weekdays', $new_recurring );

				break;
			case 'date':
				$specific_dates        = explode( ',', $bulk_day_date_value );
				$booking_specific_date = $booking_settings['booking_specific_date'];
				$date_price            = array();
				$settings_data         = array();
				$delete_date_price     = array();

				foreach ( $specific_dates as $key => $value ) {
					if ( isset( $booking_specific_date[ $value ] ) ) {
						unset( $booking_specific_date[ $value ] );
						bkap_delete_specific_date( $product_id, $value );
						$delete_date_price[] = $value;
					}
				}

				if ( 'multiple_days' !== $booking_type ) { // do not add specific date in db when product type is multiple nights.
					$booking_settings['booking_specific_date'] = $booking_specific_date;
				}

				/* Genrating the special price data based on delete specific date price*/
				$new_special_price = self::bkap_bulk_special_price_delete( $product_id, $booking_settings, $delete_date_price, 'date' );

				update_post_meta( $product_id, 'woocommerce_booking_settings', $booking_settings );

				if ( 'multiple_days' !== $booking_type ) {
					update_post_meta( $product_id, '_bkap_specific_dates', $booking_specific_date );
				}

				update_post_meta( $product_id, '_bkap_special_price', $new_special_price );

				break;
		}
	}

	/**
	 * Deleting the price for the specific date from special price post meta
	 *
	 * @param int    $product_id Product ID.
	 * @param array  $booking_settings Booking Settings.
	 * @param array  $delete_date_price Array of date price information to be deleted from special price data.
	 * @param string $daydate Date or Day string.
	 *
	 * @since 4.16.0
	 */
	public static function bkap_bulk_special_price_delete( $product_id, $booking_settings, $delete_date_price, $daydate ) {
		// Get the existing record.
		$booking_special_prices = get_post_meta( $product_id, '_bkap_special_price', true );

		switch ( $daydate ) {
			case 'day':
				break;
			case 'date':
				foreach ( $delete_date_price as $k => $v ) {
					foreach ( $booking_special_prices as $key => $value ) {
						if ( strtotime( $value['booking_special_date'] ) === strtotime( $v ) ) {
							unset( $booking_special_prices[ $key ] );
							continue;
						}
					}
				}
				break;
		}

		return $booking_special_prices;
	}
}
