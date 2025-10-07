<?php
/**
 * Booking & Appointment Plugin for WooCommerce
 *
 * This file contains all the generic function used in the booking plugin to calculating bookings, availability etc
 *
 * @author      Tyche Softwares
 * @category    Core
 * @package     BKAP/Global-Function
 * @version     4.0.0
 */

use Automattic\WooCommerce\Utilities\OrderUtil;

// phpcs:disable
/**
 * Check if the Booking plugins is active or not
 *
 * @since 1.7
 * return boolean true if active else false
 */

function is_booking_active() {

	if ( is_plugin_active( 'woocommerce-booking/woocommerce-booking.php' ) ) {
		return true;
	}
	return false;
}

/**
 * This function returns the booking plugin version number
 *
 * @return string Current Plugin Version
 *
 * @since 2.0.0
 */

function get_booking_version() {
	$plugin_data    = get_plugin_data( BKAP_FILE );
	$plugin_version = $plugin_data['Version'];
	return $plugin_version;
}

/**
 * Get Booking Settings
 *
 * @since 1.7
 * return array of Booking Settings
 */

function bkap_setting( $product_id ) {
	return apply_filters( 'bkap_product_settings', get_post_meta( $product_id, 'woocommerce_booking_settings', true ), $product_id );
}

/**
 * Get Global Booking Settings
 *
 * @since 1.7
 * return Object of Global Booking Settings
 */

function bkap_global_setting() {

	$global_settings = json_decode( get_option( 'woocommerce_booking_global_settings' ) );

	if ( is_null( $global_settings ) || '' === $global_settings || ! is_object( $global_settings ) ) {
		$global_settings = new stdClass();
	}

	if ( ! isset( $global_settings->booking_date_format ) || empty( $global_settings->booking_date_format ) ) {
		$global_settings->booking_date_format = 'd MM, yy';
	}

	if ( ! isset( $global_settings->booking_time_format ) || empty( $global_settings->booking_time_format ) ) {
		$global_settings->booking_time_format = '12';
	}

	if ( ! isset( $global_settings->booking_months ) || empty( $global_settings->booking_months ) ) {
		$global_settings->booking_months = '1';
	}

	if ( ! isset( $global_settings->booking_timeslot_display_mode ) || empty( $global_settings->booking_timeslot_display_mode ) ) {
		$global_settings->booking_timeslot_display_mode = 'list-view';
	}

	if ( ! isset( $global_settings->booking_calendar_day ) || ( '0' !== $global_settings->booking_calendar_day && empty( $global_settings->booking_calendar_day ) ) ) {

		$global_settings->booking_calendar_day = get_option( 'start_of_week' );
	}

	if ( ! isset( $global_settings->booking_themes ) || empty( $global_settings->booking_themes ) ) {
		$global_settings->booking_themes = 'smoothness';
	}

	if ( ! isset( $global_settings->booking_language ) || empty( $global_settings->booking_language ) ) {
		$global_settings->booking_language = 'en-GB';
	}

	if ( ! isset( $global_settings->allow_disable_weekdays ) || empty( $global_settings->allow_disable_weekdays ) ) {
		$global_settings->allow_disable_weekdays = false;
	}

	if ( ! isset( $global_settings->count_disable_weekdays ) || empty( $global_settings->count_disable_weekdays ) ) {
		$global_settings->count_disable_weekdays = true;
	}

	return apply_filters( 'bkap_global_settings', $global_settings );
}

/**
 * Check if WooCommerce is active.
 *
 * @return bool Returns true if WooCommerce installed else false
 * @since 1.7.0
 */

function bkap_check_woo_installed() {

	if ( class_exists( 'WooCommerce' ) ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Get Booking Type
 *
 * @param int $product_id Product ID.
 *
 * @since 5.3.0
 * return array of Booking Settings
 */
function bkap_type( $product_id ) {
	$bkap_type = get_post_meta( $product_id, '_bkap_booking_type', true );
	
	// Default to Single Day if no booking_type can be found for the product.
	if ( null === $bkap_type || '' === $bkap_type || 'null' === $bkap_type ) {
		$default_booking_type = 'only_day'; 
		update_post_meta( $product_id, '_bkap_booking_type', $default_booking_type ); // Let's update the booking_type still.
		return $default_booking_type;
	}

	return $bkap_type;
}

/**
 * Get selected language
 *
 * @param string $curr_lang Language string
 *
 * @since 1.7
 * return string $curr_lang Current language
 */

function bkap_icl_lang_code( $curr_lang ) {
	if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
		if ( ICL_LANGUAGE_CODE == 'en' ) {
			$curr_lang = 'en-GB';
		} else {
			$curr_lang = ICL_LANGUAGE_CODE;
		}
	}
	return $curr_lang;
}


function bkap_get_duration_types() {
	$type = array(
		'hours' => 'Hour(s)',
		'mins'  => 'Min(s)',
	);

	return $type;
}

function bkap_get_first_last_array( $array ) {
	$first = reset( $array );
	$last  = end( $array );

	return array( $first, $last );
}

/**
 * This functions is for getting an array of dates that are locked
 *
 * @since 4.0.0
 * @global object $wpdb Global wpdb Object.
 * @param int    $product_id Product ID.
 * @param string $min_date Date.
 * @param string $days Day number.
 *
 * @return array $booked_dates Returns array of dates in j-n-Y format.
 */
function bkap_get_lockout( $product_id, $min_date, $days, $booking_settings, $format = 'j-n-Y' ) {

	$booked_dates = array();

	$args = array(
		'post_type'      => 'bkap_booking',
		'post_status'    => array( 'paid', 'pending-confirmation', 'confirmed' ),
		'posts_per_page' => -1,
		'meta_query'     => array(
			'relation' => 'AND',
			array(
				'key'   => '_bkap_product_id',
				'value' => $product_id,
			),
			array(
				'key'     => '_bkap_start',
				'value'   => gmdate( 'YmdHis', strtotime( $min_date ) ),
				'compare' => '>=',
			),

		),
	);

	$query       = new WP_Query( $args );
	$dates       = array();
	$booking_ids = array();

	if ( $query->have_posts() ) {
		foreach ( $query->posts as $post ) {
			$booking_ids[] = $post->ID;
		}
	}

	wp_reset_postdata();

	if ( count( $booking_ids ) > 0 ) {

		$overlapping         = bkap_booking_overlapping_timeslot( bkap_global_setting(), $product_id );
		$timeslotsforproduct = bkap_get_timeslots_weekdays( $booking_settings ); // Fetch weekdays and its timeslots.

		$is_person = false;
		if ( isset( $booking_settings['bkap_person'] ) && 'on' === $booking_settings['bkap_person'] && 'on' === $booking_settings['bkap_each_person_booking'] ) {
			$is_person = true;
		}

		foreach ( $booking_ids as $booking_id ) :

			$start_date = get_post_meta( $booking_id, '_bkap_start', true );
			$end_date   = get_post_meta( $booking_id, '_bkap_end', true );
			$qty        = (int) get_post_meta( $booking_id, '_bkap_qty', true );

			/* Person Calculations */
			if ( $is_person ) {
				$persons = get_post_meta( $booking_id, '_bkap_persons', true );
				if ( count( $persons ) > 0 ) {
					$total_persons = array_sum( $persons );
					$qty           = $qty * $total_persons;
				}
			}

			$start       = substr( $start_date, 0, 8 );
			$start_time  = gmdate( 'H:i', strtotime( $start_date ) );
			$end_time    = gmdate( 'H:i', strtotime( $end_date ) );
			$weeknumber  = gmdate( 'w', strtotime( $start_date ) );
			$weekdayname = 'booking_weekday_' . $weeknumber;

			if ( $overlapping ) {
				// Below is calculations for overlapping timeslots bookings.

				if ( isset( $timeslotsforproduct[ $weekdayname ] ) ) {
					$timecheck = $timeslotsforproduct[ $weekdayname ];

					foreach ( $timecheck as $key => $value ) {

						$bstimeexplode = explode( ' - ', $value );

						if ( strtotime( $end_time ) > strtotime( $bstimeexplode[0] ) && strtotime( $start_time ) < strtotime( $bstimeexplode[1] ) ) {

							if ( strtotime( $start_time ) != strtotime( $bstimeexplode[0] ) || strtotime( $end_time ) != strtotime( $bstimeexplode[1] ) ) {
								if ( isset( $date[ $start ] ) && isset( $dates[ $start ][ "$start_time - $end_time" ] ) ) {
									$dates[ $start ][ $value ] += $qty;
								} else {
									$dates[ $start ][ $value ] = $qty;
								}
							}
						}
					}
				}
			} elseif ( isset( $timeslotsforproduct[ $weekdayname ] ) ) {
				if ( ! in_array( $start_time . ' - ' . $end_time, $timeslotsforproduct[ $weekdayname ] ) || apply_filters( 'bkap_consider_deleted_timeslots_bookings_for_lockout', false, $booking_id, $product_id, $booking_settings ) ) { // do not consider deleted timeslots.
					continue;
				}
			}

			// Overlapping calculation ends here.
			if ( isset( $dates[ $start ] ) && isset( $dates[ $start ][ "$start_time - $end_time" ] ) ) {
				$dates[ $start ][ "$start_time - $end_time" ] += $qty;
			} else {
				$dates[ $start ][ "$start_time - $end_time" ] = $qty;
			}

		endforeach;
	} else {
		return $booked_dates;
	}

	if ( count( $dates ) > 0 ) {

		$specific_dates    = isset( $booking_settings['booking_specific_date'] ) ? $booking_settings['booking_specific_date'] : array();
		$recurring_lockout = isset( $booking_settings['booking_recurring_lockout'] ) ? $booking_settings['booking_recurring_lockout'] : array();

		$resource_count = 1;
		$overlap_resource = apply_filters( 'bkap_allow_overlapping_resources', false, 0, $product_id );
		if ( isset( $booking_settings['_bkap_product_resource_max_booking'] ) && 'on' === $booking_settings['_bkap_product_resource_max_booking'] && ! $overlap_resource ) {
			$resource_costs        = Class_Bkap_Product_Resource::bkap_get_resource_costs( $product_id );
			$product_resource_ids  = array_keys( $resource_costs );
			$resource_count        = count( $product_resource_ids );
		}

		foreach ( $dates as $d_key => $d_value ) {

			$jny_format             = gmdate( $format, strtotime( $d_key ) );
			$jny_date               = gmdate( 'j-n-Y', strtotime( $d_key ) );
			$total_timeslot_lockout = '';
			$total_bookings         = 0;

			foreach ( $d_value as $time_slot => $bookings ) {
				$total_bookings += $bookings;
			}

			if ( isset( $specific_dates[ $jny_date ] ) ) { // specific date lockout has been set.

				$date_lockout           = $specific_dates[ $jny_date ];
				$date_lockout           = absint( $date_lockout ) ? (int) $date_lockout * (int) $resource_count : $date_lockout;
				$total_timeslot_lockout = bkap_get_total_timeslot_maximum_specific_booking( $product_id, gmdate( 'Y-m-d', strtotime( $d_key ) ) );

				if ( absint( $date_lockout ) > 0 && $total_bookings >= $date_lockout ) { // lockout reached.
					$booked_dates[] = $jny_format;
				} elseif ( $total_timeslot_lockout == $total_bookings ) {
					$booked_dates[] = $jny_format;
				}
			} else { // recurring weekday lockout.
				$weekday                = gmdate( 'w', strtotime( $d_key ) );
				$weekday                = "booking_weekday_$weekday";
				$total_timeslot_lockout = bkap_get_total_timeslot_maximum_booking( $product_id, $weekday );

				$recur_lockout = $recurring_lockout[ $weekday ];
				$recur_lockout = absint( $recur_lockout ) ? (int) $recur_lockout * (int) $resource_count : $recur_lockout;

				if ( absint( $recur_lockout ) > 0 && $total_bookings >= $recur_lockout ) {
					// weekday lockout reached.
					$booked_dates[] = $jny_format;
				} elseif ( ! is_null( $total_timeslot_lockout ) && '' !== $total_timeslot_lockout && $total_timeslot_lockout <= $total_bookings ) {
					$booked_dates[] = $jny_format;
				}
			}
		}
	}

	return $booked_dates;
}

/**
 * Function to disable the dates when the booking is not available due to the Global Time Slots Booking module.
 * Issue - 3880.
 *
 * @param array $locked_dates Array of Locked-out Dates.
 * @param int   $product_id Product ID.
 * @param array $booking_settings Booking Settings.
 * @global object $wpdb Global wpdb Object
 *
 * @return array $locked_dates
 *
 * @since 5.2.1
 */
function bkap_locked_dates_fixed_time( $locked_dates, $product_id, $booking_settings ) {

	global $wpdb;

	$today_ymd = gmdate( 'Y-m-d', current_time( 'timestamp' ) );

	$unlimited_query         = 'SELECT * FROM `' . $wpdb->prefix . "booking_history`
						WHERE start_date >= %s
						AND post_id = %d
						AND total_booking = 0
						AND available_booking = 0
						AND status = ''";
	$unlimited_query_results = $wpdb->get_results( $wpdb->prepare( $unlimited_query, $today_ymd, $product_id ) );

	if ( empty( $unlimited_query_results ) ) {
		$date_query         = 'SELECT start_date,SUM(available_booking) FROM `' . $wpdb->prefix . "booking_history`
								WHERE start_date >= %s
								AND post_id = %d
								AND total_booking > 0
								AND available_booking = 0
								AND status = ''
								GROUP BY start_date";
		$date_query_results = $wpdb->get_results( $wpdb->prepare( $date_query, $today_ymd, $product_id ) );

		$day_query         = 'SELECT weekday,SUM(available_booking) FROM `' . $wpdb->prefix . "booking_history`
								WHERE post_id = %d
								AND start_date = '0000-00-00'
								AND total_booking > 0
								AND available_booking > 0
								AND status = ''
								GROUP BY weekday";
		$day_query_results = $wpdb->get_results( $wpdb->prepare( $day_query, $product_id ) );

		$week_data = array();
		foreach ( $day_query_results as $day_data ) {
			$week_data[ $day_data->weekday ] = $day_data->{'SUM(available_booking)'};
		}

		$specific_date_query         = 'SELECT start_date,SUM(available_booking) FROM `' . $wpdb->prefix . "booking_history`
								WHERE post_id = %d
								AND weekday = ''
								AND status = ''
								GROUP BY start_date";
		$specific_date_query_results = $wpdb->get_results( $wpdb->prepare( $specific_date_query, $product_id ) );

		$specific_dates_data = array();
		foreach ( $specific_date_query_results as $specficdates_data ) {
			$specific_dates_data[ $specficdates_data->start_date ] = $specficdates_data->{'SUM(available_booking)'};
		}

		foreach ( $date_query_results as $date_data ) {
			$start_date = $date_data->start_date;
			$datejny    = gmdate( 'j-n-Y', strtotime( $start_date ) );
			if ( ! in_array( $datejny, $locked_dates ) ) {
				$date_count = $date_data->{'SUM(available_booking)'};
				$weekday    = bkap_weekday_string( $start_date );

				if ( isset( $specific_dates_data[ $start_date ] ) ) {

					if ( $specific_dates_data[ $start_date ] === $date_count ) {
						array_push( $locked_dates, $datejny );
					}
				} else {
					if ( isset( $week_data[ $weekday ] ) && $week_data[ $weekday ] === $date_count ) {
						array_push( $locked_dates, $datejny );
					}
				}
			}
		}
	}

	return $locked_dates;
}

/**
 * This function will calculate the total maximum booking for timeslot of specific date.
 *
 * @since 4.5.0
 * @param int    $product_id Product ID
 * @param string $date Date
 * @global object $wpdb Global wpdb Object
 *
 * @return $tatal Blank if unlimited booking for any one timeslot else total of max bookings for all timeslot.
 */

function bkap_get_total_timeslot_maximum_specific_booking( $product_id, $date ) {
	global $wpdb;

	$total = '';

	$unlimited         = 'SELECT available_booking FROM `' . $wpdb->prefix . "booking_history`
                              WHERE post_id= %d
                              AND weekday = ''
                              And start_date = '%s'
                              AND from_time != ''
                              AND total_booking <= 0";
	$unlimited_results = $wpdb->get_results( $wpdb->prepare( $unlimited, $product_id, $date ) );

	if ( empty( $unlimited_results ) ) {
		$date_lockout      = 'SELECT SUM(total_booking) FROM `' . $wpdb->prefix . "booking_history`
                              WHERE post_id= %d
                              AND weekday = ''
                              And start_date = '%s'
                              AND from_time != ''";
		$results_date_lock = $wpdb->get_results( $wpdb->prepare( $date_lockout, $product_id, $date ) );

		$total = $results_date_lock[0]->{'SUM(total_booking)'};
	}

	return $total;
}

/**
 * This function will calculate the total maximum booking for timeslot.
 *
 * @since 4.5.0
 * @param int    $product_id Product ID
 * @param string $weekday Weekday
 * @global object $wpdb Global wpdb Object
 *
 * @return $tatal Blank if unlimited booking for any one timeslot else total of max bookings for all timeslot.
 */

function bkap_get_total_timeslot_maximum_booking( $product_id, $weekday ) {
	global $wpdb;

	$total = '';

	$unlimited         = 'SELECT available_booking FROM `' . $wpdb->prefix . "booking_history`
                              WHERE post_id= %d
                              AND weekday = %s
                              And start_date = '0000-00-00'
                              AND from_time != ''
							  AND total_booking <= 0
							  AND status = ''";
	$unlimited_results = $wpdb->get_results( $wpdb->prepare( $unlimited, $product_id, $weekday ) );

	if ( empty( $unlimited_results ) ) {
		$date_lockout      = 'SELECT SUM(total_booking) FROM `' . $wpdb->prefix . "booking_history`
                              WHERE post_id= %d
                              AND weekday = %s
                              And start_date = '0000-00-00'
							  AND from_time != ''
							  AND status = ''";
		$results_date_lock = $wpdb->get_results( $wpdb->prepare( $date_lockout, $product_id, $weekday ) );

		$total = $results_date_lock[0]->{'SUM(total_booking)'};
	}

	return $total;
}

/**
 * This function will calculate the check-in dates that are booked for multiple
 *
 * @since 4.5.0
 * @param int    $product_id Product ID.
 * @param string $end_check fetch records based on start date and end date.
 * @param string $return_availability Want to return availability or booked dates.
 *
 * @return array $booked_dates Array of the booked dates
 */
function bkap_get_booked( $product_id, $end_check = true, $return_availability = false ) {

	$meta_query = array(
		'relation' => 'AND',
		array(
			'key'     => '_bkap_product_id',
			'value'   => $product_id,
			'compare' => '=',
		),
	);

	$todays_date  = gmdate( 'Ymd' );
	$todays_date .= '000000';

	if ( ! $end_check ) {
		$meta_query = array(
			'relation' => 'AND',
			array(
				'key'     => '_bkap_product_id',
				'value'   => $product_id,
				'compare' => '=',
			),
			array(
				'key'     => '_bkap_start',
				'value'   => $todays_date,
				'compare' => '>=',
			),
		);

	} else {
		$meta_query[] = array(
			'relation' => 'OR',
			array(
				'key'     => '_bkap_start',
				'value'   => $todays_date,
				'compare' => '>=',
			),
			array(
				'key'     => '_bkap_end',
				'value'   => $todays_date,
				'compare' => '>=',
			),
		);
	}

	$args = array(
		'post_type'      => 'bkap_booking',
		'post_status'    => array( 'paid', 'pending-confirmation', 'confirmed' ),
		'posts_per_page' => -1,
		'meta_query'     => $meta_query,
	);

	$query1 = new WP_Query( $args );

	$booking_idss = array();
	if ( $query1->have_posts() ) {
		foreach ( $query1->posts as $post1 ) {
			$booking_idss[] = $post1->ID;
		}
	}

	$booking_idss = apply_filters( 'bkap_additional_bookings_to_be_considered', $booking_idss, $product_id );

	$dates     = array();
	$end_dates = array();

	if ( count( $booking_idss ) > 0 ) {

		$booking_settings = get_post_meta( $product_id, 'woocommerce_booking_settings', true ); // booking settings for rental charges.

		$is_person = false;
		if ( isset( $booking_settings['bkap_person'] ) && 'on' === $booking_settings['bkap_person'] && isset( $booking_settings['bkap_each_person_booking'] ) && 'on' === $booking_settings['bkap_each_person_booking'] ) {
			$is_person = true;
		}

		foreach ( $booking_idss as $booking_id ) {

			$booking    = new BKAP_Booking( $booking_id );
			$start_date = $booking->start;
			$end_date   = $booking->end;
			$qty        = (int) $booking->qty;
			$start      = gmdate( 'Ymd', $start_date );

			/* Person Calculations */
			if ( $is_person ) {
				$persons = $booking->persons;
				if ( count( $persons ) > 0 ) {
					$total_persons = array_sum( $persons );
					$qty           = $qty * $total_persons;
				}
			}

			$start_dny = gmdate( 'd-n-Y', $start_date );
			$end_dny   = gmdate( 'd-n-Y', $end_date );

			if ( isset( $booking_settings['booking_prior_days_to_book'] ) && $booking_settings['booking_prior_days_to_book'] ) {
				$days      = '-' . $booking_settings['booking_prior_days_to_book'] . ' days';
				$start_dny = gmdate( 'j-n-Y', strtotime( $days, strtotime( $start_dny ) ) );
			}
			if ( isset( $booking_settings['booking_later_days_to_book'] ) && $booking_settings['booking_later_days_to_book'] ) {
				$days    = '+' . $booking_settings['booking_later_days_to_book'] . ' days';
				$end_dny = gmdate( 'j-n-Y', strtotime( $days, strtotime( $end_dny ) ) );
			}

			if ( isset( $booking_settings['booking_charge_per_day'] ) && 'on' === $booking_settings['booking_charge_per_day'] ) {
				$get_days = bkap_common::bkap_get_betweendays_when_flat( $start_dny, $end_dny, $product_id );
			} else {
				$get_days = bkap_common::bkap_get_betweendays( $start_dny, $end_dny );
			}

			$i = 0;

			foreach ( $get_days as $days ) {
				$ymd_format = gmdate( 'Ymd', strtotime( $days ) );

				if ( isset( $dates[ $ymd_format ] ) ) {
					$dates[ $ymd_format ] += $qty;
				} else {
					$dates[ $ymd_format ] = $qty;
				}

				if ( 0 !== $i ) {
					if ( isset( $end_dates[ $ymd_format ] ) ) {
						$end_dates[ $ymd_format ] += $qty;
					} else {
						$end_dates[ $ymd_format ] = $qty;
					}
				}
				$i++;
			}
		}

		wp_reset_postdata();
	}

	if ( $return_availability ) {
		return array(
			'dates'     => $dates,
			'end_dates' => $end_dates,
		);
	}

	// get the dates which have reached lockout.
	$booked_dates    = get_booked_multiple( $product_id, $dates );
	$booked_enddates = get_booked_multiple( $product_id, $end_dates );

	return array(
		'booked_dates'    => $booked_dates,
		'booked_enddates' => $booked_enddates,
	);
}

/**
 * This function will calculate the check-out dates that are booked for multiple
 *
 * @param int    $product_id Product ID
 * @param string $min_date Min_date
 * @param string $days Number of days
 * @since 4.5.0
 *
 * @return array $booked_dates Array of the booked dates
 * @todo The same function is written bkap_get_booked. check why it is saperatly written.
 */

function bkap_get_booked_checkout( $product_id, $min_date, $days ) {

	// check the booking type
	$booking_type = bkap_type( $product_id );

	if ( 'multiple_days' === $booking_type ) {

		if ( absint( $days ) > 0 ) {
			$end_date = strtotime( $min_date . "+$days days" );
		} else {
			$end_date = $days;
		}
		// get bookings for that range
		$dates = get_bookings_for_range( $product_id, $min_date, $end_date, false );
		// get the dates which have reached lockout
		$booked_dates = get_booked_multiple( $product_id, $dates );
	}

	return $booked_dates;

}

/**
 * Function to calculate dates and/or time slots with the number of bookings received in the date range.
 *
 * @param int     $product_id Product ID
 * @param string  $min_date Min_date
 * @param string  $end_date Date
 * @param boolean $include_start Pass true if checkout date should be consider
 * @since 4.5.0
 *
 * @return array $dates Array of Date and/or Timeslot with the number of booking received in the date range.
 */

function get_bookings_for_range( $product_id, $min_date, $end_date, $include_start = true, $resource_id = '' ) {

	$is_global_overlapping = false;
	$booking_type          = bkap_type( $product_id );
	$book_global_settings  = json_decode( get_option( 'woocommerce_booking_global_settings' ) );
	if ( in_array( $booking_type, array( 'date_time', 'multidates_fixedtime' ) ) ) {
		$booking_settings = get_post_meta( $product_id, 'woocommerce_booking_settings', true );

		$min_date_str = strtotime( $min_date );
		$week_day     = gmdate( 'l', $min_date_str );
		$weekdays     = bkap_weekdays();
		$weekday      = array_search( $week_day, $weekdays );

		if ( isset( $booking_settings['booking_time_settings'] ) && isset( $min_date ) ) {
			$lockout_settings = array();
			if ( isset( $booking_settings['booking_time_settings'][ $min_date ] ) ) {
				$lockout_settings = $booking_settings['booking_time_settings'][ $min_date ];
			}
			if ( count( $lockout_settings ) == 0 ) {
				if ( isset( $booking_settings['booking_time_settings'][ $weekday ] ) ) {
					$lockout_settings = $booking_settings['booking_time_settings'][ $weekday ];
				}
			}

			if ( ! empty( $lockout_settings ) ) {
				foreach ( $lockout_settings as $l_key => $l_value ) {
					if ( isset( $l_value['global_time_check'] ) ) {
						$global_timeslot_lockout = $l_value['global_time_check'];
					} else {
						$global_timeslot_lockout = '';
					}
				}
			}
		}

		if ( isset( $book_global_settings->booking_global_timeslot ) && 'on' === $book_global_settings->booking_global_timeslot || isset( $global_timeslot_lockout ) && 'on' === $global_timeslot_lockout ) {
			$args    = array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'meta_query'     => array(
					'relation'   => 'AND',
					array(
						'key'     => '_bkap_booking_type',
						'value'   => $booking_type,
						'compare' => '=',
					),
				),
				'fields'         => 'ids',
			);
			$product_ids = get_posts( $args );
			$dup_ids     = array();

			foreach ( $product_ids as $k => $v ) {
				$booking_settings = get_post_meta( $v, 'woocommerce_booking_settings', true );

				if ( isset( $booking_settings['booking_enable_time'] ) && $booking_settings['booking_enable_time'] == 'on' ) {
					if ( isset( $booking_settings['booking_time_settings'] ) && isset( $min_date ) ) {
						$lockout_settings = array();
						if ( isset( $booking_settings['booking_time_settings'][ $min_date ] ) ) {
							$lockout_settings = $booking_settings['booking_time_settings'][ $min_date ];
						}
						if ( count( $lockout_settings ) == 0 ) {
							if ( isset( $booking_settings['booking_time_settings'][ $weekday ] ) ) {
								$lockout_settings = $booking_settings['booking_time_settings'][ $weekday ];
							}
						}

						if ( ! empty( $lockout_settings ) ) {
							foreach ( $lockout_settings as $l_key => $l_value ) {
								if ( isset( $l_value['global_time_check'] ) ) {
									$global_timeslot_lockout = $l_value['global_time_check'];
								} else {
									$global_timeslot_lockout = '';
								}
							}
						}

						if ( isset( $book_global_settings->booking_global_timeslot ) && 'on' === $book_global_settings->booking_global_timeslot || isset( $global_timeslot_lockout ) && 'on' === $global_timeslot_lockout ) {
							$dup_ids[] = $v;
						}
					}
				}
			}

			if ( ! empty( $dup_ids ) ) {
				$pids_array            = array_values( $dup_ids );
				$is_global_overlapping = true;

				$meta_query = array(
					'relation' => 'AND',
					array(
						'key'     => '_bkap_product_id',
						'value'   => $pids_array,
						'compare' => 'IN',
					),
				);

				if ( '' !== $resource_id ) {
					if ( is_array( $resource_id ) ) {
						$meta_query[] = array(
							'key'     => '_bkap_resource_id',
							'value'   => $resource_id,
							'compare' => 'IN',
						);
					} else {
						$meta_query[] = array(
							'key'     => '_bkap_resource_id',
							'value'   => $resource_id,
							'compare' => '=',
						);
					}
				}
			}
		}
	}

	if ( ! $is_global_overlapping ) {
		if ( '' !== $resource_id ) {
			if ( is_array( $resource_id ) ) {
				$meta_query[] = array(
					'relation' => 'AND',
					array(
						'key'     => '_bkap_resource_id',
						'value'   => $resource_id,
						'compare' => 'IN',
					),
				);
			} else {
				$meta_query[] = array(
					'relation' => 'AND',
					array(
						'key'     => '_bkap_resource_id',
						'value'   => $resource_id,
						'compare' => '=',
					),
				);
			}
		} else {
			$meta_query = array(
				'relation' => 'AND',
				array(
					'key'     => '_bkap_product_id',
					'value'   => $product_id,
					'compare' => '=',
				),
			);
		}
	}

	$args = array(
		'post_type'      => 'bkap_booking',
		'post_status'    => array( 'paid', 'pending-confirmation', 'confirmed' ),
		'posts_per_page' => -1,
		'meta_query'     => $meta_query,
	);

	$query1 = new WP_Query( $args );

	$booking_idss = array();
	if ( $query1->have_posts() ) {
		foreach ( $query1->posts as $post1 ) {
			$booking_idss[] = $post1->ID;
		}
	}

	$booking_idss = apply_filters( 'bkap_additional_bookings_to_be_considered', $booking_idss, $product_id, $resource_id );

	$dates = array();

	if ( count( $booking_idss ) > 0 ) {

		$booking_type     = bkap_type( $product_id ); // check the booking type
		$booking_settings = get_post_meta( $product_id, 'woocommerce_booking_settings', true ); // booking settings for rental charges

		$timeslotsforproduct = bkap_get_timeslots_weekdays( $booking_settings ); // Fetch weekdays and its timeslots

		$is_person = false;
		if ( isset( $booking_settings['bkap_person'] ) && 'on' === $booking_settings['bkap_person'] && isset( $booking_settings['bkap_each_person_booking'] ) && 'on' === $booking_settings['bkap_each_person_booking'] ) {
			$is_person = true;
		}

		foreach ( $booking_idss as $booking_id ) :

			$booking    = new BKAP_Booking( $booking_id );
			$start_date = $booking->start;
			$end_date   = $booking->end;
			$qty        = (int) $booking->qty;
			$start      = gmdate( 'Ymd', $start_date );

			/* Person Calculations */
			if ( $is_person ) {
				$persons = $booking->persons;
				if ( count( $persons ) > 0 ) {
					$total_persons = array_sum( $persons );
					$qty           = $qty * $total_persons;
				}
			}

			switch ( $booking_type ) {
				case 'only_day':
					if ( isset( $dates[ $start ] ) ) {
						$dates[ $start ] += $qty;
					} else {
						$dates[ $start ] = $qty;
					}
					break;
				case 'date_time':
				case 'multidates_fixedtime':
					if ( $min_date_str > $start_date && '' === $resource_id ) {
						continue 2;
					}

					$start_time = gmdate( 'H:i', $start_date );
					$end_time   = gmdate( 'H:i', $end_date );

					$overlapping = bkap_booking_overlapping_timeslot( $book_global_settings, $product_id );
					// Below is calculations for overlapping timeslots bookings.
					$weeknumber     = gmdate( 'w', $start_date );
					$start_date_jny = gmdate( 'j-n-Y', $start_date );
					$weekdayname    = 'booking_weekday_' . $weeknumber;

					if ( $overlapping && isset( $timeslotsforproduct[ $weekdayname ] ) ) {
						$timecheck = $timeslotsforproduct[ $weekdayname ];

						foreach ( $timecheck as $key => $value ) {

							$bstimeexplode = explode( ' - ', $value );

							if ( strtotime( $end_time ) > strtotime( $bstimeexplode[0] ) && strtotime( $start_time ) < strtotime( $bstimeexplode[1] ) ) {

								if ( strtotime( $start_time ) != strtotime( $bstimeexplode[0] ) || strtotime( $end_time ) != strtotime( $bstimeexplode[1] ) ) {
									if ( isset( $dates[ $start ] ) && isset( $dates[ $start ][ $value ] ) /* array_key_exists( "$start_time - $end_time", $dates[ $start ] ) */ ) {
										$dates[ $start ][ $value ] += $qty;
									} else {
										$dates[ $start ][ $value ] = $qty;
									}
								}
							}
						}
					}
					// Overlapping calculation ends here.

					if ( isset( $dates[ $start ] ) && isset( $dates[ $start ][ "$start_time - $end_time" ] ) ) {
						$dates[ $start ][ "$start_time - $end_time" ] += $qty;
					} else if ( $overlapping && $product_id == $booking->product_id ) {

						$timecheck = array();
						if ( isset( $timeslotsforproduct[ $start_date_jny ] ) ) {
							$timecheck = $timeslotsforproduct[ $start_date_jny ];
						} elseif ( isset( $timeslotsforproduct[ $weekdayname ] ) ) {
							$timecheck = $timeslotsforproduct[ $weekdayname ];
						}

						if ( ! empty( $timecheck ) ) {
							if ( in_array( "$start_time - $end_time", $timecheck ) ) {
								$dates[ $start ][ "$start_time - $end_time" ] = $qty;
							}
						}
					} else {
						$dates[ $start ][ "$start_time - $end_time" ] = $qty;
					}
					break;

				case 'duration_time':
					$addoneday          = ( $start_date == $end_date ) ? 86400 : 0;
					$end_date_addoneday = $end_date + $addoneday;
					$between_duration   = bkap_get_between_timestamp( $start_date, $end_date_addoneday );

					foreach ( $between_duration as $key => $value ) {
						if ( isset( $dates[ $value ] ) ) {
							$dates[ $value ] += $qty;
						} else {
							$dates[ $value ] = $qty;
						}
					}
					break;

				case 'multiple_days':
					if ( $include_start ) {
						$start_dny = gmdate( 'd-n-Y', $start_date );
					} else {
						$start_date_addoneday = $start_date + 86400;
						$start_dny            = gmdate( 'd-n-Y', $start_date_addoneday );
					}

					$end_dny = gmdate( 'd-n-Y', $end_date );

					if ( isset( $booking_settings['booking_charge_per_day'] ) && 'on' === $booking_settings['booking_charge_per_day'] ) {
						$get_days = bkap_common::bkap_get_betweendays_when_flat( $start_dny, $end_dny, $product_id );
					} else {
						$get_days = bkap_common::bkap_get_betweendays( $start_dny, $end_dny );
					}

					foreach ( $get_days as $days ) {
						$ymd_format = gmdate( 'Ymd', strtotime( $days ) );

						if ( isset( $dates[ $ymd_format ] ) ) {
							$dates[ $ymd_format ] += $qty;
						} else {
							$dates[ $ymd_format ] = $qty;
						}
					}
					break;
			}
		endforeach;

		wp_reset_postdata();
	}

	return $dates;
}

/**
 * Function to prepare array for weekdays and its timeslots.
 *
 * @param array $booking_settings Booking Settings
 *
 * @return array $allweekdaystimeslots Returns array of weekdays and its timeslots
 *
 * @since 4.12.1
 */


function bkap_get_timeslots_weekdays( $booking_settings ) {

	$timesettings         = isset( $booking_settings['booking_time_settings'] ) ? $booking_settings['booking_time_settings'] : array();
	$allweekdaystimeslots = array();

	foreach ( $timesettings as $key => $value ) {
		$timeslot = '';
		foreach ( $value as $k => $v ) {

			$fromtime = $v['from_slot_hrs'] . ':' . $v['from_slot_min'];
			$totime   = $v['to_slot_hrs'] . ':' . $v['to_slot_min'];
			$timeslot = $fromtime . ' - ' . $totime;

			$allweekdaystimeslots[ $key ][] = $timeslot;
		}
	}

	return $allweekdaystimeslots;
}

/**
 * Function to prepare start timestamp of all the durations.
 *
 * @param int $start_time Start date timestamp
 * @param int $end_time End date timestamp
 *
 * @return array $time Returns array of start duration timestamps
 *
 * @since 4.10.0
 */

function bkap_get_between_timestamp( $start_time, $end_time, $minute = 60 ) {

	$time = array();

	while ( $start_time <= $end_time ) {
		$time[]     = $start_time;
		$start_time = $start_time + $minute;
	}

	return $time;
}

/**
 * Fucntion to check if the selected duration is available for booking or not
 *
 * @param int    $product_id Product ID
 * @param array  $booking_settings Booking Settings
 * @param string $start_str start date timestamp
 * @param string $end_str end date timestamp
 * @param array  $booked_duration array of booked duration with its quantity
 *
 * @return bool $available Returns true is the duration is available else false
 *
 * @since 4.10.0
 */

function bkap_check_duration_available( $product_id, $booking_settings, $start_str, $end_str, $booked_duration ) {

	$available = true;
	$d_setting = get_post_meta( $product_id, '_bkap_duration_settings', true );

	if ( isset( $d_setting['duration_max_booking'] ) ) {

		if ( $d_setting['duration_max_booking'] != 0 || $d_setting['duration_max_booking'] != '' ) {

			$between_duration = bkap_get_between_timestamp( (int) $start_str + 1, (int) $end_str - 1 );

			foreach ( $between_duration as $key => $value ) {

				if ( array_key_exists( $value, $booked_duration ) && $booked_duration[ $value ] >= $d_setting['duration_max_booking'] ) {
					$available = false;
					break;
				}
			}
		}
	}

	return $available;
}

/**
 * Fucntion to check if the product is added to cart and accourdingly do the calculations for the lockout on the product page.
 *
 * @param int    $product_id Product ID
 * @param string $min_date Date
 *
 * @return array $bkap_cart_check Returns array of dates along wih the quantity it is added in the cart
 *
 * @since 4.10.0
 */

function bkap_cart_check_for_duration( $product_id, $selected_date ) {

	$bkap_cart_check = array();
	$minute          = 60;
	$bkap_setting    = bkap_setting( $product_id );

	foreach ( WC()->cart->cart_contents as $c_key => $c_value ) {

		if ( $c_value['product_id'] == $product_id ) {

			if ( isset( $c_value['bkap_booking'] ) ) {
				$booking = $c_value['bkap_booking'][0];

				if ( isset( $booking['duration_time_slot'] ) && $booking['duration_time_slot'] != '' ) {

					if ( $booking['hidden_date'] == $selected_date ) {

						/* Persons Calculations */
						$cart_total_person = 1;
						if ( isset( $booking['persons'] ) ) {
							if ( 'on' === $bkap_setting['bkap_each_person_booking'] ) {
								$cart_total_person = array_sum( $booking['persons'] );
							}
						}

						$all_timestamp_start_end = array();
						$start_date              = $booking['hidden_date'];
						$time                    = $booking['duration_time_slot'];
						$start_date_str          = strtotime( $start_date . ' ' . $time );
						$selected_duration       = explode( '-', $booking['selected_duration'] );

						$bkap_cart_check_keys = array_keys( $bkap_cart_check );

						$end_date_str = bkap_common::bkap_add_hour_to_date(
							$start_date,
							$time,
							$selected_duration[0],
							$product_id,
							$selected_duration[1]
						);

						while ( $start_date_str <= $end_date_str ) {

							if ( in_array( $start_date_str, $bkap_cart_check_keys ) ) {
								$bkap_cart_check[ $start_date_str ] += ( $c_value['quantity'] * $cart_total_person );
							} else {
								$bkap_cart_check[ $start_date_str ] = ( $c_value['quantity'] * $cart_total_person );
							}

							$start_date_str = $start_date_str + $minute;
						}
					}
				}
			}
		}
	}

	return $bkap_cart_check;
}

/**
 * This functions is for calcuating locked dates for duration based time
 *
 * @param int    $product_id Product ID
 * @param string $min_date Date
 * @param string $days Day number
 *
 * @return array $booked_date Returns array of dates in j-n-Y format
 *
 * @since 4.10.0
 */

function bkap_get_duration_lockout( $product_id, $min_date, $days ) {

	if ( absint( $days ) > 0 ) {
		$end_date = strtotime( $min_date . "+$days days" );
	} else {
		$end_date = $days;
	}

	$d_setting     = get_post_meta( $product_id, '_bkap_duration_settings', true );
	$d_max_booking = $d_setting['duration_max_booking'];

	if ( $d_max_booking == 0 || $d_max_booking == '' ) {
		return array();
	}

	$booked_date  = array();
	$min_date_str = strtotime( $min_date );

	while ( $min_date_str <= $end_date ) {

		$dates       = array();
		$noofbooking = 0;

		$beginOfDay = strtotime( 'midnight', $min_date_str ); // start timestamp of date
		$endOfDay   = strtotime( 'tomorrow', $beginOfDay ); // end timestamp of date

		$dates = get_bookings_for_range( $product_id, $beginOfDay + 60, $endOfDay - 60, true );

		$noofbooking = count( $dates );

		if ( $noofbooking > 0 ) {

			$noofmins = ( ( $endOfDay + 60 ) - $beginOfDay ) / 60;

			if ( $noofmins == $noofbooking ) {
				if ( count( array_unique( $dates ) ) === 1 && end( $dates ) == $d_max_booking ) {
					$booked_date[] = gmdate( 'j-n-Y', $beginOfDay );
				}
			}
		}

		$min_date_str = $endOfDay + 1;
	}

	return $booked_date;
}

/**
 *
 * This functions is for calcuating locked dates for duration based time
 *
 * @param int    $product_id Product ID
 * @param string $min_date Date
 * @param string $days Day number
 *
 * @return array $booked_date Returns array of dates in j-n-Y format
 */

function bkap_get_duration_lockout_fixing( $product_id, $min_date, $days ) {

	if ( absint( $days ) > 0 ) {
		$end_date = strtotime( $min_date . "+$days days" );
	} else {
		$end_date = $days;
	}

	$d_setting     = get_post_meta( $product_id, '_bkap_duration_settings', true );
	$d_max_booking = ( ! empty( $d_setting ) && isset( $d_setting['duration_max_booking'] ) ) ? $d_setting['duration_max_booking'] : 0;

	if ( $d_max_booking == 0 || $d_max_booking == '' ) {
		return array();
	}

	$max_duration_hours = $d_setting['duration'] * $d_setting['duration_max'];
	$max_duration_mins  = $max_duration_hours * 60;

	$booked_date  = array();
	$min_date_str = strtotime( $min_date );

	// get all the bookings IDs and start & e( ( $endOfDay + 60 )  - $beginOfDay ) / 60;or the given product ID from post meta
	$args = array(
		'post_type'      => 'bkap_booking',
		'post_status'    => array( 'paid', 'pending-confirmation', 'confirmed' ),
		'posts_per_page' => -1,
		'meta_query'     => array(
			'relation' => 'AND',
			array(
				'key'   => '_bkap_product_id',
				'value' => $product_id,
			),
			array(
				'key'     => '_bkap_start',
				'value'   => gmdate( 'YmdHis', strtotime( $min_date ) ),
				'compare' => '>=',
			),

		),
	);

	$query = new WP_Query( $args );

	$startYmd = gmdate( 'YmdHis', $min_date_str );
	$endYmd   = gmdate( 'YmdHis', $end_date );

	$dates       = array();
	$booking_ids = array();
	if ( $query->have_posts() ) {
		foreach ( $query->posts as $post ) {
			$booking_ids[] = $post->ID;
		}
	}

	if ( count( $booking_ids ) > 0 ) {

		foreach ( $booking_ids as $booking_id ) :

			$start_date = get_post_meta( $booking_id, '_bkap_start', true );
			$end_date   = get_post_meta( $booking_id, '_bkap_end', true );
			$qty        = get_post_meta( $booking_id, '_bkap_qty', true );
			$start      = substr( $start_date, 0, 8 );

			$addoneday = 0;

			if ( $start_date == $end_date ) {
				$addoneday = 86400;
			}

			$between_duration = bkap_get_between_timestamp( strtotime( $start_date ), strtotime( $end_date ) + $addoneday );

			/**
			 * Get each minute data and its quantity for a booking date
			*/
			foreach ( $between_duration as $key => $value ) {
				$date = gmdate( 'Y-m-d', $value );
				if ( isset( $dates[ $date ][ $value ] ) ) {
					$dates[ $date ][ $value ] += $qty;
				} else {
					$dates[ $date ][ $value ] = $qty;
				}
			}

		endforeach;

		wp_reset_postdata();
	}

	$booked_date = array();

	/**
	 * Loop through the $dates array which only has the dates that have atlease one booking and
	 * check the number of minutes that have been added.
	 */
	foreach ( $dates as $date => $lockout ) {
		$no_mins = count( $lockout );

		$date_str = strtotime( $date );

		$beginOfDay = isset( $d_setting['first_duration'] ) & $d_setting['first_duration'] !== '' ? strtotime( $d_setting['first_duration'], $date_str ) : strtotime( 'midnight', $date_str ); // start timestamp of date
		$endOfDay   = isset( $d_setting['end_duration'] ) && $d_setting['end_duration'] !== '' ? strtotime( $d_setting['end_duration'], $date_str ) : strtotime( 'tomorrow', $beginOfDay );

		$no_qty = min( $lockout );

		$total_duration_mins = ( ( $endOfDay + 60 ) - $beginOfDay ) / 60;

		if ( $no_mins >= $total_duration_mins && $no_qty >= $d_max_booking ) { // 2 == 1
			$booked_date[] = gmdate( 'j-n-Y', strtotime( $date ) );
		}
	}

	return $booked_date;
}

/**
 * Returns an array of dates that are completely booked
 * i.e. lockout has been reached.
 * Lockout Priority:
 * 1. specific date lockout
 * 2. weekday lockout
 * 3. lockout date after X orders
 *
 * @since 4.2.0
 * @param int   $product_id Product Id
 * @param array $dates Array of Date and its lockout
 * @return array $booked_dates Return array the dates whose lockout is reached
 */

function get_booked_multiple( $product_id, $dates ) {

	$booked_dates      = array();
	$specific_dates    = get_post_meta( $product_id, '_bkap_specific_dates', true ); // get the specific dates lockout
	$recurring_lockout = get_post_meta( $product_id, '_bkap_recurring_lockout', true ); // get the weekdays lockout
	$any_date_lockout  = get_post_meta( $product_id, '_bkap_date_lockout', true ); // get the Lockout Date after X orders

	// once we have a list of the dates, we need to see if bookings for any date have reached the lockout
	foreach ( $dates as $d_key => $d_value ) {

		$jny_format = gmdate( 'j-n-Y', strtotime( $d_key ) );
		$weekday    = gmdate( 'w', strtotime( $d_key ) );
		$weekday    = "booking_weekday_$weekday";

		if ( is_array( $specific_dates ) && array_key_exists( $jny_format, $specific_dates ) ) { // specific date lockout has been set
			$date_lockout = $specific_dates[ $jny_format ];

			if ( absint( $date_lockout ) > 0 && $d_value >= $date_lockout ) { // lockout reached
				$booked_dates[] = $jny_format;
			}
		} elseif ( is_array( $recurring_lockout ) && array_key_exists( $weekday, $recurring_lockout ) ) { // recurring weekday lockout

			if ( absint( $recurring_lockout[ $weekday ] ) > 0 && $d_value >= $recurring_lockout[ $weekday ] ) { // weekday lockout reached
				$booked_dates[] = $jny_format;
			} elseif ( absint( $any_date_lockout ) > 0 && $d_value >= $any_date_lockout ) {
				$booked_dates[] = $jny_format;
			}
		} else { // Lockout Date after X orders field

			if ( absint( $any_date_lockout ) > 0 && $d_value >= $any_date_lockout ) {
				$booked_dates[] = $jny_format;
			}
		}
	}

	return $booked_dates;
}

/**
 * Returns an array of dates and the number of bookingsdone for the same. array[ Ymd ] => bookings done
 *
 * @since 4.2.0
 * @param int    $product_id Product ID
 * @param string $date Date
 * @return array $dates array of date and number of bookings done
 */

function get_bookings_for_date( $product_id, $date ) {

	$booking_type = bkap_type( $product_id ); // check the booking type

	// get all the bookings IDs and start & end booking times for the given product ID from post meta

	if ( 'multiple_days' === $booking_type ) {
		$args = array(
			'post_type'   => 'bkap_booking',
			'post_status' => array( 'paid', 'pending-confirmation', 'confirmed' ),
			'meta_query'  => array(
				array(
					'key'   => '_bkap_product_id',
					'value' => $product_id,
				),
				array(
					'key'     => '_bkap_start',
					'value'   => gmdate( 'YmdHis', strtotime( $date ) ),
					'compare' => '<=',
				),
				array(
					'key'     => '_bkap_end',
					'value'   => gmdate( 'YmdHis', strtotime( $date ) ),
					'compare' => '>=',
				),
			),
		);
	} else {
		$args = array(
			'post_type'   => 'bkap_booking',
			'post_status' => array( 'paid', 'pending-confirmation', 'confirmed' ),
			'meta_query'  => array(
				array(
					'key'   => '_bkap_product_id',
					'value' => $product_id,
				),
				array(
					'key'     => '_bkap_start',
					'value'   => gmdate( 'Ymd', strtotime( $date ) ),
					'compare' => 'LIKE',
				),
			),
		);
	}

	$dates = array();
	$query = new WP_Query( $args );

	if ( $query->have_posts() ) {

		// booking settings for rental charges
		$booking_settings = get_post_meta( $product_id, 'woocommerce_booking_settings', true );

		while ( $query->have_posts() ) :

			$query->the_post();

			$booking_id = $query->post->ID;
			$start_date = get_post_meta( $booking_id, '_bkap_start', true );
			$end_date   = get_post_meta( $booking_id, '_bkap_end', true );

			$qty   = get_post_meta( $booking_id, '_bkap_qty', true );
			$start = substr( $start_date, 0, 8 );

			switch ( $booking_type ) {
				case 'only_day':
					if ( array_key_exists( $start, $dates ) ) {
						$dates[ $start ] += $qty;
					} else {
						$dates[ $start ] = $qty;
					}
					break;
				case 'date_time':
					$start_time = gmdate( 'H:i', strtotime( $start_date ) );
					$end_time   = gmdate( 'H:i', strtotime( $end_date ) );

					$slot_time = "$start_time - $end_time";
					if ( array_key_exists( $start, $dates ) && array_key_exists( $slot_time, $dates[ $start ] ) ) {
						$dates[ $start ][ $slot_time ] += $qty;
					} else {
						$dates[ $start ][ $slot_time ] = $qty;
					}
					break;
				case 'multiple_days':
					$start_dny = gmdate( 'd-n-Y', strtotime( $start_date ) );
					$end_dny   = gmdate( 'd-n-Y', strtotime( $end_date ) );

					if ( isset( $booking_settings['booking_charge_per_day'] ) && $booking_settings['booking_charge_per_day'] == 'on' ) {
						$get_days = bkap_common::bkap_get_betweendays_when_flat( $start_dny, $end_dny, $product_id );
					} else {
						$get_days = bkap_common::bkap_get_betweendays( $start_dny, $end_dny );
					}

					foreach ( $get_days as $days ) {
						$jny_format = gmdate( 'j-n-Y', strtotime( $days ) );

						if ( strtotime( $days ) == strtotime( $date ) ) {
							if ( array_key_exists( $jny_format, $dates ) ) {
								$dates[ $jny_format ] += $qty;
							} else {
								$dates[ $jny_format ] = $qty;
							}
						}
					}

					break;
			}

		endwhile;
	}

	wp_reset_postdata();

	return $dates;
}

/**
 * This function is to get the available bookings for a date
 *
 * @since 4.2.0
 * @param int    $product_id Product ID
 * @param string $booking_date Date
 * @param array  $bookings_array Array for all the bookings received for the set date
 * @return array Returns the available bookings for a date.
 */

function get_availability_for_date( $product_id, $booking_date, $bookings_array ) {

	$available_bookings = 0;
	$unlimited          = 'YES';

	$lockout = get_date_lockout( $product_id, $booking_date );

	$total_bookings = 0;
	if ( is_array( $bookings_array ) && count( $bookings_array ) > 0 ) {
		foreach ( $bookings_array as $b_key => $b_value ) {
			if ( is_array( $b_value ) && count( $b_value ) > 0 ) {
				foreach ( $b_value as $slot => $booking ) {
					$total_bookings += $booking;
				}
			} else {
				$total_bookings = $b_value;
			}
		}
	}

	if ( 'unlimited' === $lockout ) {
		$available_bookings = 0;
		$unlimited          = 'YES';
	} elseif ( absint( $lockout ) >= 0 ) {
		$unlimited          = 'NO';
		$available_bookings = $lockout - $total_bookings;
	}

	return array(
		'unlimited' => $unlimited,
		'available' => $available_bookings,
	);
}

/**
 * Returns the available bookings for a date & time slot
 *
 * @since 4.2.0
 * @param int    $product_id Product ID
 * @param string $date Date
 * @param string $slot Timeslot
 * @param string $bookings Array for all the bookings received for the dates
 * @return array Returns the available bookings for a date & time slot
 */

function get_slot_availability( $product_id, $date, $slot, $bookings ) {

	$available_bookings = 0; // default
	$total_bookings     = 0; // default total bookings placed to 0
	$date_ymd           = gmdate( 'Ymd', strtotime( $date ) );
	$weekday            = gmdate( 'w', strtotime( $date ) );
	$weekday            = "booking_weekday_$weekday";

	// bookings have been placed for that date
	if ( is_array( $bookings ) && count( $bookings ) > 0 ) {

		if ( array_key_exists( $date_ymd, $bookings ) ) {
			if ( array_key_exists( $slot, $bookings[ $date_ymd ] ) ) {
				$total_bookings = $bookings[ $date_ymd ][ $slot ];
			}
		}
	}

	$lockout = get_slot_lockout( $product_id, $date, $slot );

	$available_bookings = ( absint( $lockout ) > 0 ) ? $lockout - $total_bookings : 'Unlimited';

	if ( $available_bookings === 'Unlimited' ) {
		$unlimited = 'YES';
		$available = 0;
	} else {
		$unlimited = 'NO';
		$available = $available_bookings;
	}

	return array(
		'unlimited' => $unlimited,
		'available' => $available,
	);
}

/**
 * Returns the total bookings allowed for a given date and time slot
 *
 * @since 4.2.0
 * @param int    $product_id Product ID
 * @param string $date Date
 * @param string $slot Timeslot
 * @return int $lockout Returns the total bookings allowed for a given date and time slot
 */

function get_slot_lockout( $product_id, $date, $slot ) {

	$lockout       = 0; // default
	$date_jny      = gmdate( 'j-n-Y', strtotime( $date ) ); // date format
	$weekday       = gmdate( 'w', strtotime( $date ) );
	$weekday       = "booking_weekday_$weekday";
	$time_settings = get_post_meta( $product_id, '_bkap_time_settings', true ); // get the lockout for the date & time slot

	if ( is_array( $time_settings ) && count( $time_settings ) > 0 ) {

		if ( array_key_exists( $date_jny, $time_settings ) ) { // specific date time slot
			$slot_settings = $time_settings[ $date_jny ];
		} elseif ( array_key_exists( $weekday, $time_settings ) ) { // weekday timeslot
			$slot_settings = $time_settings[ $weekday ];
		}

		if ( is_array( $slot_settings ) && count( $slot_settings ) > 0 ) {

			foreach ( $slot_settings as $settings ) {

				$from_time = gmdate( 'H:i', strtotime( $settings['from_slot_hrs'] . ':' . $settings['from_slot_min'] ) );
				$to_time   = gmdate( 'H:i', strtotime( $settings['to_slot_hrs'] . ':' . $settings['to_slot_min'] ) );

				if ( $slot === "$from_time - $to_time" ) {

					$lockout = ( absint( $settings['lockout_slot'] ) > 0 ) ? $settings['lockout_slot'] : 'unlimited';
				}
			}
		}
	}

	return $lockout;
}

/**
 * Function to get the total bookings allowed for a date
 *
 * @since 4.2.0
 * @param int    $product_id Product ID
 * @param string $date Date
 * @return int $lockout Returns the total bookings allowed for a date
 */
function get_date_lockout( $product_id, $date, $check = true, $unlimited_string = true ) {

	$lockout            = 0;
	$recurring_weekdays = get_post_meta( $product_id, '_bkap_recurring_weekdays', true ); // get recurring settings _bkap_recurring_weekdays.
	$booking_type       = bkap_type( $product_id ); // get the booking type.
	$specific_dates     = get_post_meta( $product_id, '_bkap_specific_dates', true ); // get the specific dates lockout.
	$recurring_lockout  = get_post_meta( $product_id, '_bkap_recurring_lockout', true ); // get the weekdays lockout.

	$date_jny = gmdate( 'j-n-Y', strtotime( $date ) );
	$weekday  = gmdate( 'w', strtotime( $date ) );
	$weekday  = "booking_weekday_$weekday";

	if ( $check ) {
		$custom_ranges         = get_post_meta( $product_id, '_bkap_custom_ranges', true ); // get custom ranges.
		$custom_holiday_ranges = get_post_meta( $product_id, '_bkap_holiday_ranges', true );
		$product_holidays      = get_post_meta( $product_id, '_bkap_product_holidays', true );
		if ( is_array( $custom_holiday_ranges ) && count( $custom_holiday_ranges ) > 0 ) {
			foreach ( $custom_holiday_ranges as $range_key => $range_value ) {
				if ( strtotime( $range_value['start'] ) <= strtotime( $date ) && strtotime( $range_value['end'] ) >= strtotime( $date ) ) {
					return $lockout = 0;
				}
			}
		}

		if ( is_array( $custom_ranges ) && count( $custom_ranges ) > 0 ) {

			foreach ( $custom_ranges as $custom_key => $custom_value ) {
				if ( ! ( strtotime( $custom_value['start'] ) <= strtotime( $date ) && strtotime( $custom_value['end'] ) >= strtotime( $date ) ) ) {
					return $lockout = 0;
				}
			}
		}

		if ( is_array( $product_holidays ) && array_key_exists( $date_jny, $product_holidays ) ) {
			return $lockout = 0;
		}
	}

	$unlimited_str = $unlimited_string ? 'unlimited' : 0;

	if ( is_array( $specific_dates ) && isset( $specific_dates[ $date_jny ] ) ) {
		$lockout = ( absint( $specific_dates[ $date_jny ] ) > 0 ) ? (int) $specific_dates[ $date_jny ] : $unlimited_str;
	} elseif ( is_array( $recurring_weekdays ) && 'on' === $recurring_weekdays[ $weekday ] &&
		is_array( $recurring_lockout ) && isset( $recurring_lockout[ $weekday ] ) && 'multiple_days' !== $booking_type ) {
		$lockout = ( absint( $recurring_lockout[ $weekday ] ) > 0 ) ? (int) $recurring_lockout[ $weekday ] : $unlimited_str;
	} else {
		if ( 'multiple_days' === $booking_type ) {
			// get the Lockout Date after X orders
			$any_date_lockout = get_post_meta( $product_id, '_bkap_date_lockout', true );
			$lockout          = ( absint( $any_date_lockout ) > 0 ) ? (int) $any_date_lockout : $unlimited_str;
		}
	}

	return $lockout;
}

/**
 * This function will return booking id of matching booking.
 *
 * @since 5.2.0
 * @param  string $start_date Start Date YmdHis.
 * @param  string $end_date End Date YmdHis.
 * @param  int    $product_id Product ID.
 * @param  int    $variation_id Variation ID.
 * @param  int    $resource_id Resource Post ID.
 * @param  int    $booking_id Booking Post ID.
 *
 * @return int $booking_id Booking Id is matching Booking is found else 0.
 */
function bkap_check_same_booking_info( $start_date, $end_date, $product_id, $variation_id, $resource_id = 0, $booking_id = 0, $single = true, $meeting_query = array() ) {

	if ( $resource_id > 0 ) {
		$additional = array(
			'key'     => '_bkap_resource_id',
			'value'   => $resource_id,
			'compare' => '=',
		);
	} else {
		$additional = array(
			'key'   => '_bkap_product_id',
			'value' => $product_id,
		);
	}

	if ( empty( $meeting_query ) ) {
		$meeting_query = array(
			'key'     => '_bkap_zoom_meeting_link',
			'value'   => '',
			'compare' => '!=',
		);
	}

	$args = array(
		'post_type'      => 'bkap_booking',
		'post_status'    => array( 'paid', 'pending-confirmation', 'confirmed' ),
		'posts_per_page' => -1,
		'post__not_in'   => array( $booking_id ),
		'meta_query'     => array(
			array(
				array(
					'key'     => '_bkap_start',
					'value'   => $start_date,
					'compare' => '>=',
				),
				array(
					'key'     => '_bkap_end',
					'value'   => $end_date,
					'compare' => '=',
				),
				array(
					'key'     => '_bkap_variation_id',
					'value'   => $variation_id,
					'compare' => '=',
				),
				$meeting_query,
				$additional,
			),
		),
	);

	$posts = get_posts( $args );

	if ( $single ) {
		$zoom_booking_id = 0;
		foreach ( $posts as $post ) {
			$zoom_booking_id = $post->ID;
			break;
		}
	} else {
		$zoom_booking_id = array();
		foreach ( $posts as $post ) {
			$zoom_booking_id[] = $post->ID;
		}
	}

	return $zoom_booking_id;
}

/**
 * This function will return an array of Booking Post Meta Data.
 *
 * @since 5.2.0
 * @param int $booking_id Booking ID.
 *
 * @return array $booking_data Array of Booking Meta Data.
 */
function bkap_get_meta_data( $booking_id ) {

	global $wpdb;

	$booking_data        = array();
	$array_of_booking_id = array();

	// Check if $booking_id contains an array of Post IDs in the case of Multiple Dates.
	if ( is_array( $booking_id ) ) {
		$array_of_booking_id = $booking_id;
	} else {
		array_push( $array_of_booking_id, $booking_id );
	}

	foreach ( $array_of_booking_id as $booking_id ) {

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}postmeta WHERE post_id = %d",
				(int) $booking_id
			)
		);

		$_booking_data = array();

		foreach ( $results as $bkap_post_meta ) {
			$key                   = str_replace( '_bkap_', '', $bkap_post_meta->meta_key );
			$_booking_data[ $key ] = $bkap_post_meta->meta_value;
		}

		array_push( $booking_data, $_booking_data );
	}

	return $booking_data;
}

/**
 * This function will return an array of Product IDs that has the passed resource assigned.
 *
 * @since 5.10.0
 * @param  int $resource_id Resource Post ID.
 * @return array $product_ids Array of Product IDs.
 */
function bkap_product_ids_from_resource_id( $resource_id ) {

	$resource_id = ':' . $resource_id . ';';

	$args = array(
		'post_type'   => 'product',
		'post_status' => 'publish',
		'meta_query'  => array(
			array(
				'key'     => '_bkap_resource_base_costs',
				'value'   => $resource_id,
				'compare' => 'LIKE',
			),
		),
	);

	$get_posts = get_posts( $args );

	$product_ids = array();
	foreach ( $get_posts as $key => $value ) {
		$product_ids[] = $value->ID;
	}

	return $product_ids;
}

/**
 * This function will return an array of resource availability and its available quantity.
 *
 * @since 4.6.0
 * @param  int     $post_id Resource Post ID
 * @param  WP_Post $post Resource Post
 * @return array $resource_data Array of resource availability and its available quantity
 */

function bkap_save_resources( $post_id, $post ) {

	if ( isset( $_POST['_bkap_booking_qty'] ) ) {
		$availability  = bkap_get_posted_availability();
		$resource_data = array(
			'bkap_resource_qty'          => wc_clean( $_POST['_bkap_booking_qty'] ),
			'bkap_resource_menu_order'   => wc_clean( $_POST['_bkap_resource_menu_order'] ),
			'bkap_resource_availability' => $availability,
			'bkap_resource_meeting_host' => bkap_get_posted_meeting_host(),
		);

		return $resource_data;
	}
}

/**
 * Fetching Resource's Max Booking.
 *
 * @param int    $resource_id Resource ID.
 * @param string $booking_date Booking Date.
 * @param int    $product_id Product ID.
 * @param array  $booking_settings Booking Settings.
 *
 * @since 5.13.0
 * @return object $post_data  WP Post
 */
function bkap_resource_max_booking( $resource_id, $booking_date, $product_id, $booking_settings ) {

	if ( isset( $booking_settings['_bkap_product_resource_max_booking'] ) && 'on' === $booking_settings['_bkap_product_resource_max_booking'] ) {
		if ( isset( $booking_settings['booking_enable_time'] ) && 'duration_time' == $booking_settings['booking_enable_time'] ) {
			$bkap_resource_availability = $booking_settings['bkap_duration_settings']['duration_max_booking'];
		} else {
			$bkap_resource_availability = get_date_lockout( $product_id, $booking_date, false, false );
		}
	} else {
		$bkap_resource_availability = get_post_meta( $resource_id, '_bkap_resource_qty', true );
	}

	return $bkap_resource_availability;
}

/**
 * Getting all the post which has resource post meta.
 *
 * @since 4.6.0
 * @param  int $resource_id Resource ID
 * @return object $post_data  WP Post
 */

function bkap_booked_resources( $resource_id ) {

	$args = array(
		'post_type'   => 'bkap_booking',
		'numberposts' => -1,
		'post_status' => array( 'paid', 'pending-confirmation', 'confirmed' ),
	);

	if ( is_array( $resource_id ) ) {
		$args['meta_query'] = array(
			array(
				'key'     => '_bkap_resource_id',
				'value'   => $resource_id,
				'compare' => 'IN',
			),
		);
	} else {
		$args['meta_key']   = '_bkap_resource_id';
		$args['meta_value'] = $resource_id;
	}

	$posts_data = get_posts( $args );

	return $posts_data;
}

/**
 * All Booking posts having the resource ID
 *
 * @since 4.6.0
 * @param  int $resource_id Resource ID
 * @return array $booking List of posts having Resource
 */

function bkap_booking_posts_for_resource( $resource_id ) {

	$all_posts = bkap_booked_resources( $resource_id );
	$booking   = array();

	foreach ( $all_posts as $key => $value ) {

		$booking[ $key ] = new BKAP_Booking( $value->ID );
	}

	return $booking;
}

/**
 * Calculating Booked, locked dates and time for resource
 *
 * @since 4.6.0
 * @param  int $resource_id Resource ID
 * @return array $booking_resource_booking_dates Resource's Booked and locked dates
 */

function bkap_calculate_bookings_for_resource( $resource_id, $product_id ) {

	$booking_settings = get_post_meta( $product_id, 'woocommerce_booking_settings', true );
	$overlap_resource = apply_filters( 'bkap_allow_overlapping_resources', false, $resource_id, $product_id );

	if ( isset( $booking_settings['_bkap_product_resource_max_booking'] ) && 'on' === $booking_settings['_bkap_product_resource_max_booking'] && $overlap_resource ) {
		$resource_costs = Class_Bkap_Product_Resource::bkap_get_resource_costs( $product_id );
		$resource_ids   = array_keys( $resource_costs );
		$booking_posts  = bkap_booking_posts_for_resource( $resource_ids );
	} else {
		$booking_posts = bkap_booking_posts_for_resource( $resource_id );
	}

	$dates                          = array();
	$dates_checkout                 = array();
	$datet                          = array();
	$date_t                         = array();
	$day                            = gmdate( 'Y-m-d', current_time( 'timestamp' ) );
	$daystr                         = strtotime( $day );
	$bkap_booking_placed            = '';
	$bkap_booking_placed_checkout   = '';
	$bkap_locked_dates              = '';
	$bkap_locked_dates_checkout     = '';
	$bkap_time_booking_placed       = $bkap_time_locked_dates = '';
	$booking_resource_booking_dates = array(
		'bkap_booking_placed'        => '',
		'bkap_locked_dates'          => '',
		'bkap_locked_dates_checkout' => '',
	);
	$booking_type                    = bkap_type( $product_id );

	// $bkap_resource_availability = get_post_meta( $resource_id, '_bkap_resource_qty', true );

	$is_person = false;
	if ( isset( $booking_settings['bkap_person'] ) && 'on' === $booking_settings['bkap_person'] && 'on' === $booking_settings['bkap_each_person_booking'] ) {
		$is_person = true;
	}

	$time_booking_types = array( 'date_time', 'fixed_time' );

	$timesettings  = isset( $booking_settings['booking_time_settings'] ) ? $booking_settings['booking_time_settings'] : array();
	$all_timeslots = array();
	$date_day_slots = array();

	foreach ( $timesettings as $key => $value ) {
		$timeslot = '';
		foreach ( $value as $k => $v ) {

			$fromtime = $v['from_slot_hrs'] . ':' . $v['from_slot_min'];
			$totime   = $v['to_slot_hrs'] . ':' . $v['to_slot_min'];
			$timeslot = $fromtime . ' - ' . $totime;
			$date_day_slots[$key][] = $timeslot;

			array_push( $all_timeslots, $timeslot );
		}
	}

	if ( count( $all_timeslots ) > 0 ) {
		$all_timeslots = array_unique( $all_timeslots );
	}

	foreach ( $booking_posts as $booking_key => $booking ) {

		/* Below change is for not considering the different product resource bookings */
		$consider_only_same_product = apply_filters( 'bkap_consider_resource_booking_only_for_same_products', true, $booking->product_id, $product_id );
		if ( ! $consider_only_same_product ) {
			continue;
		}

		if ( $booking->end >= $daystr ) {
			$qty  = $booking->qty;
			$tqty = $booking->qty;

			/* Person Calculations */
			if ( $is_person ) {
				$persons = $booking->persons;
				if ( count( $persons ) > 0 ) {
					$total_persons = array_sum( $persons );
					$qty           = $qty * $total_persons;
				}
			}

			$start_time = ( $booking->get_start_time() != '' ) ? gmdate( 'H:i', strtotime( $booking->get_start_time() ) ) : '';
			$end_time   = ( $booking->get_end_time() != '' ) ? gmdate( 'H:i', strtotime( $booking->get_end_time() ) ) : '';

			$time_slot = $start_time . ' - ' . $end_time;
			$start_dny = gmdate( 'd-n-Y', $booking->start );
			$end_dny   = gmdate( 'd-n-Y', $booking->end );

			$rental_status = false;
			if ( is_plugin_active( 'bkap-rental/rental.php' ) ) {
				if ( isset( $booking_settings['booking_prior_days_to_book'] ) && $booking_settings['booking_prior_days_to_book'] > 0 ) {
					$prior_day = $booking_settings['booking_prior_days_to_book'] * 86400;
					$start_dny = gmdate( 'd-n-Y', $booking->start - $prior_day );
				}
				if ( isset( $booking_settings['booking_later_days_to_book'] ) && $booking_settings['booking_later_days_to_book'] > 0 ) {
					$later_days = $booking_settings['booking_later_days_to_book'] * 86400;
					$end_dny    = gmdate( 'd-n-Y', $booking->end + $later_days );
				}

				if ( isset( $booking_settings['booking_charge_per_day'] ) && $booking_settings['booking_charge_per_day'] == 'on' && isset( $booking_settings['booking_same_day'] ) && $booking_settings['booking_same_day'] == 'on' ) {
					$rental_status = true;
				}
			}

			if ( $rental_status ) {
				$get_days = bkap_common::bkap_get_betweendays_when_flat( $start_dny, $end_dny, $booking->get_product_id(), 'j-n-Y' );
			} else {
				$get_days = bkap_common::bkap_get_betweendays( $start_dny, $end_dny, 'j-n-Y' );
			}

			$get_days_checkout = $get_days;
			array_shift( $get_days_checkout );

			foreach ( $get_days as $days ) {

				$jny_format = gmdate( 'j-n-Y', strtotime( $days ) );

				if ( isset( $dates[ $jny_format ] ) ) {
					$dates[ $jny_format ] += $qty;
					if ( $start_time != '' ) {

						if ( isset( $datet[ $jny_format ] ) ) {
							if ( isset( $datet[ $jny_format ][ $time_slot ] ) ) {
								$datet[ $jny_format ][ $time_slot ] += $tqty;
							} else {
								$datet[ $jny_format ][ $time_slot ] = $tqty;
							}
						} else {
							$datet[ $jny_format ][ $time_slot ] = $tqty;
						}
					} elseif ( isset( $datet[ $jny_format ][ $time_slot ] ) ) {
						$datet[ $jny_format ][ $time_slot ] += $tqty;
					}
				} else {
					if ( ' - ' !== $time_slot && in_array( $booking_type, $time_booking_types ) ) {
						if ( in_array( $time_slot, $all_timeslots ) ) {
							$dates[ $jny_format ] = $qty;
						}
					} else {
						$dates[ $jny_format ] = $qty;
					}
					$datet[ $jny_format ][ $time_slot ] = $tqty;
				}

				if ( in_array( $jny_format, $get_days_checkout ) ) {
					if ( isset( $dates_checkout[ $jny_format ] ) ) {
						$dates_checkout[ $jny_format ] += $qty;
					} else {
						$dates_checkout[ $jny_format ] = $qty;
					}
				}
			}
		}
	}

	// Date calculations.

	foreach ( $dates as $boking_date => $booking_qty ) {
		$bkap_booking_placed .= '"' . $boking_date . '"=>' . $booking_qty . ',';
		// @todo - Check how to avoid below.
		$bkap_resource_availability = bkap_resource_max_booking( $resource_id, $boking_date, $product_id, $booking_settings );
		if ( 0 !== $bkap_resource_availability && $bkap_resource_availability <= $booking_qty ) {
			$bkap_locked_dates .= '"' . $boking_date . '",';
		}
	}

	// Checkout Calendar.
	foreach ( $dates_checkout as $boking_date => $booking_qty ) {
		$bkap_booking_placed_checkout .= '"' . $boking_date . '"=>' . $booking_qty . ',';
		// @todo - Check how to avoid below.
		$bkap_resource_availability = bkap_resource_max_booking( $resource_id, $boking_date, $product_id, $booking_settings );
		if ( 0 !== $bkap_resource_availability && $bkap_resource_availability <= $booking_qty ) {
			$bkap_locked_dates_checkout .= '"' . $boking_date . '",';
		}
	}

	// Timeslots calculations.
	$date_t = $datet;
	foreach ( $datet as $boking_date => $booking_time ) {

		foreach ( $booking_time as $b_time => $b_qty ) {

			$qty = $b_qty;
			if ( ' - ' == $b_time ) {
				$b_time = '00:01 - 23:59';
			}
			$time_explode      = explode( ' - ', $b_time );
			$selected_timeslot = array(
				'start' => strtotime( $boking_date . ' ' . $time_explode[0] ),
				'end'   => strtotime( $boking_date . ' ' . $time_explode[1] ),
			);

			$timeslot_array = array();

			if ( isset( $date_day_slots[ $boking_date ] ) ) {
				$all_timeslots = $date_day_slots[ $boking_date ];
			} else {
				$weekday_string = bkap_weekday_string( $boking_date );
				$all_timeslots  = isset( $date_day_slots[ $weekday_string ] ) ? $date_day_slots[ $weekday_string ] : array();
			}

			foreach ( $all_timeslots as $time ) {

				if ( $time == '' || $time == $b_time ) {
					continue;
				}
				$time_explode = explode( ' - ', $time );
				$start_time   = strtotime( $boking_date . ' ' . $time_explode[0] );
				$end_time     = strtotime( $boking_date . ' ' . $time_explode[1] );

				if ( ( $start_time > $selected_timeslot['start'] && $start_time < $selected_timeslot['end'] ) || ( $end_time > $selected_timeslot['start'] && $end_time < $selected_timeslot['end'] ) ) {
					$timeslot_array[] = array(
						'start' => strtotime( $boking_date . ' ' . $time_explode[0] ),
						'end'   => strtotime( $boking_date . ' ' . $time_explode[1] ),
						'time'  => $time,
					);
					unset( $date_t[ $boking_date ][ $b_time ] );
					$date_t[ $boking_date ][ $time ] = $b_qty;
				}
			}

			$bkap_time_booking_placed .= '"' . $boking_date . '"=>' . $b_time . '=>' . $b_qty . ',';
			if ( isset( $bkap_resource_availability ) && $bkap_resource_availability <= $qty ) {
				if ( ! empty( $timeslot_array ) ) {
					foreach ( $timeslot_array as $key => $value ) {
						$bkap_time_locked_dates .= '"' . $boking_date . '"=>' . $value['time'] . ',';
					}
				} else {
					$bkap_time_locked_dates .= '"' . $boking_date . '"=>' . $b_time . ',';
				}
			}
		}
	}

	$bkap_booking_placed        = substr_replace( $bkap_booking_placed, '', -1 );
	$bkap_locked_dates          = substr_replace( $bkap_locked_dates, '', -1 );
	$bkap_locked_dates_checkout = substr_replace( $bkap_locked_dates_checkout, '', -1 );

	$bkap_time_booking_placed = substr_replace( $bkap_time_booking_placed, '', -1 );
	$bkap_time_locked_dates   = substr_replace( $bkap_time_locked_dates, '', -1 );

	$booking_resource_booking_dates['bkap_booking_placed']        = $bkap_booking_placed;
	$booking_resource_booking_dates['bkap_locked_dates']          = $bkap_locked_dates;
	$booking_resource_booking_dates['bkap_locked_dates_checkout'] = $bkap_locked_dates_checkout;
	$booking_resource_booking_dates['bkap_time_booking_placed']   = $bkap_time_booking_placed;
	$booking_resource_booking_dates['bkap_time_locked_dates']     = $bkap_time_locked_dates;
	$booking_resource_booking_dates['bkap_date_time_array']       = $date_t;
	$booking_resource_booking_dates['bkap_date_array']            = $dates;

	return $booking_resource_booking_dates;
}

/**
 * Sorting Resource Ranges based on priority
 *
 * @since 4.6.0
 */

function bkap_sort_date_time_ranges_by_priority( $x, $y ) {
	return (int) $x['priority'] - (int) $y['priority'];
}

/**
 * Delete the record from order history table.
 *
 * @param int $order_id   Order ID.
 * @param int $booking_id ID of record from booking history.
 *
 * @since 5.1.0
 */
function bkap_delete_from_order_hitory( $order_id, $booking_id ) {

	global $wpdb;

	$delete_order_history = 'SELECT * FROM `' . $wpdb->prefix . 'booking_order_history` WHERE order_id = %d and booking_id = %d';
	$result               = $wpdb->get_results( $wpdb->prepare( $delete_order_history, $order_id, $booking_id ) );

	if ( count( $result ) > 1 ) {
		foreach ( $result as $res ) {
			$delete_order_history = 'DELETE FROM `' . $wpdb->prefix . 'booking_order_history` WHERE id = %d';
			$wpdb->query( $wpdb->prepare( $delete_order_history, $res->id ) );
			break;
		}
	} else {
		$delete_order_history = 'DELETE FROM `' . $wpdb->prefix . 'booking_order_history` WHERE order_id = %d and booking_id = %d';
		$wpdb->query( $wpdb->prepare( $delete_order_history, $order_id, $booking_id ) );
	}
}

/**
 * Get date range between month.
 *
 * @since 4.6.0
 * @param $start int Month Start
 * @param $end int Month End
 * @global array $bkap_months
 * @return $date Array Date range of Given Month Range
 */

function bkap_get_month_range( $start, $end ) {
	$bkap_intervals = bkap_intervals();
	$bkap_months    = $bkap_intervals['months'];

	$current_year = gmdate( 'Y', current_time( 'timestamp' ) );
	$next_year    = gmdate( 'Y', strtotime( '+1 year' ) );

	// Start Date
	$month_start_name = $bkap_months[ $start ];
	$month_to_use     = "$month_start_name $current_year";
	$range_start      = gmdate( 'j-n-Y', strtotime( $month_to_use ) );

	// End Date
	$month_end_name = $bkap_months[ $end ];

	if ( $start <= $end ) {
		$month_to_use = "$month_end_name $current_year";
	} else {
		$month_to_use = "$month_end_name $next_year";
	}
	$month_end = gmdate( 'j-n-Y', strtotime( $month_to_use ) );

	$days      = gmdate( 't', strtotime( $month_end ) );
	$days     -= 1;
	$range_end = gmdate( 'j-n-Y', strtotime( "+$days days", strtotime( $month_end ) ) );

	$date['start'] = $range_start;
	$date['end']   = $range_end;

	return $date;

}

/**
 * Get date range between week.
 *
 * @since 4.6.0
 * @param int    $week1 Number of start week
 * @param int    $week2 Number of end week
 * @param string $format 'j-n-Y'
 *
 * @return array $week_date_range Array of date range of give week
 */

function bkap_get_week_range( $week1, $week2, $format = 'j-n-Y' ) {

	$bkap_intervals = bkap_intervals();
	$bkap_months    = $bkap_intervals['months'];

	$week_date_range = array();

	$date = date_create();

	$current_year = gmdate( 'Y', current_time( 'timestamp' ) );
	$next_year    = gmdate( 'Y', strtotime( '+1 year' ) );

	$currentWeekNumber = gmdate( 'W' );

	if ( $week1 >= $currentWeekNumber ) {
		date_isodate_set( $date, $current_year, $week1 );
		$week_date_range['start'] = date_format( $date, $format );

		date_isodate_set( $date, $current_year, $week2, 7 );
		$week_date_range['end'] = date_format( $date, $format );
	} else {
		date_isodate_set( $date, $next_year, $week1 );
		$week_date_range['start'] = date_format( $date, $format );

		date_isodate_set( $date, $next_year, $week2, 7 );
		$week_date_range['end'] = date_format( $date, $format );

	}

	return $week_date_range;

}

/**
 * Get days numbers between days.
 *
 * @since 4.6.0
 * @param int $day1 Number of start weekday.
 * @param int $day2 Number of end weekday.
 *
 * @return string $days Numbers between start and end weekday.
 */
function bkap_get_day_between_Week( $day1, $day2 ) {

	$days = '';

	if ( $day1 == $day2 ) {
		$days = $day1;
		if ( 7 == $day1 ) {
			$days = 0;
		}
	} else {
		for ( $i = 0; $i < 7; $i++ ) {
			if ( $day1 < 7 ) {
				$days .= $day1 . ',';
				$day1++;

				if ( $day1 == $day2 ) {

					if ( $day1 == 7 ) {
						$day1 = 0;
					}
					$days .= $day1;
					break;
				}
				if ( $day1 == 7 ) {
					$day1 = 0;
				}
			}
		}
	}
	return $days;
}

/**
 * Get posted availability fields and format.
 *
 * @since 4.6.0
 * @return array $availability Returns the array of availability data set in the Resource details metabox
 */
function bkap_get_posted_availability() {

	$availability = array();
	$row_size     = isset( $_POST['bkap_availability_type'] ) ? sizeof( $_POST['bkap_availability_type'] ) : 0;

	if ( isset( $_POST['bkap_availability_bookable_hidden'] ) ) {
		$_POST['bkap_availability_bookable'] = $_POST['bkap_availability_bookable_hidden']; // Assiging hidden values for bookable data.
	}
	for ( $i = 0; $i < $row_size; $i ++ ) {

		$availability[ $i ]['bookable'] = 0;

		if ( isset( $_POST['bkap_availability_bookable'] ) ) {
			$availability[ $i ]['bookable'] = wc_clean( $_POST['bkap_availability_bookable'][ $i ] );
		}

		$availability[ $i ]['type'] = wc_clean( $_POST['bkap_availability_type'][ $i ] );

		$availability[ $i ]['priority'] = intval( $_POST['bkap_availability_priority'][ $i ] );

		switch ( $availability[ $i ]['type'] ) {
			case 'custom':
				$availability[ $i ]['from'] = wc_clean( $_POST['bkap_availability_from_date'][ $i ] );
				$availability[ $i ]['to']   = wc_clean( $_POST['bkap_availability_to_date'][ $i ] );
				break;
			case 'months':
				$availability[ $i ]['from'] = wc_clean( $_POST['bkap_availability_from_month'][ $i ] );
				$availability[ $i ]['to']   = wc_clean( $_POST['bkap_availability_to_month'][ $i ] );
				break;
			case 'weeks':
				$availability[ $i ]['from'] = wc_clean( $_POST['bkap_availability_from_week'][ $i ] );
				$availability[ $i ]['to']   = wc_clean( $_POST['bkap_availability_to_week'][ $i ] );
				break;
			case 'days':
				$availability[ $i ]['from'] = wc_clean( $_POST['bkap_availability_from_day_of_week'][ $i ] );
				$availability[ $i ]['to']   = wc_clean( $_POST['bkap_availability_to_day_of_week'][ $i ] );
				break;
			case 'time':
			case 'time:1':
			case 'time:2':
			case 'time:3':
			case 'time:4':
			case 'time:5':
			case 'time:6':
			case 'time:0':
				$availability[ $i ]['from'] = $_POST['bkap_availability_from_time'][ $i ];
				$availability[ $i ]['to']   = $_POST['bkap_availability_to_time'][ $i ];
				break;
			case 'time:range':
				$availability[ $i ]['from']      = $_POST['bkap_availability_from_time'][ $i ];
				$availability[ $i ]['to']        = $_POST['bkap_availability_to_time'][ $i ];
				$availability[ $i ]['from_date'] = wc_clean( $_POST['bkap_availability_from_date'][ $i ] );
				$availability[ $i ]['to_date']   = wc_clean( $_POST['bkap_availability_to_date'][ $i ] );
				break;
		}
	}
	return $availability;
}

function bkap_intervals() {

	$bkap_intervals = array(
		'months' => array(
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
		),
		'days'   => array(
			'1' => __( 'Monday', 'woocommerce-booking' ),
			'2' => __( 'Tuesday', 'woocommerce-booking' ),
			'3' => __( 'Wednesday', 'woocommerce-booking' ),
			'4' => __( 'Thursday', 'woocommerce-booking' ),
			'5' => __( 'Friday', 'woocommerce-booking' ),
			'6' => __( 'Saturday', 'woocommerce-booking' ),
			'7' => __( 'Sunday', 'woocommerce-booking' ),
		),
		'type'   => array(
			'custom'    => __( 'Date range', 'woocommerce-booking' ),
			'months'    => __( 'Range of months', 'woocommerce-booking' ),
			'weeks'     => __( 'Range of weeks', 'woocommerce-booking' ),
			'days'      => __( 'Range of days', 'woocommerce-booking' ),
			'time_data' => array(
				'time'       => __( 'Time Range (all week)', 'woocommerce-booking' ),
				'time:range' => __( 'Date range with recurring time', 'woocommerce-booking' ),
				'time:0'     => __( 'Sunday', 'woocommerce-booking' ),
				'time:1'     => __( 'Monday', 'woocommerce-booking' ),
				'time:2'     => __( 'Tuesday', 'woocommerce-booking' ),
				'time:3'     => __( 'Wednesday', 'woocommerce-booking' ),
				'time:4'     => __( 'Thursday', 'woocommerce-booking' ),
				'time:5'     => __( 'Friday', 'woocommerce-booking' ),
				'time:6'     => __( 'Saturday', 'woocommerce-booking' ),
			),
		),
	);

	// Adding Weeks to interval array.
	for ( $i = 1; $i <= 53; $i ++ ) {
		$bkap_intervals['weeks'][ $i ] = sprintf( __( 'Week %s', 'woocommerce-booking' ), $i );
	}

	return $bkap_intervals;
}

/**
 * Return the available timeslots/blocks based on the Resource Time Availability data.
 *
 * @param string $current_date Selected Date.
 * @param array  $resource_availability_data Resource Availability Data.
 * @param mixed  $time_data Dropdown String or Blocks Array.
 * @param array  $args Array of Additional Data.
 *
 * @since 5.7.1
 * @return array $availability Returns the array of availability data set in the Resource details metabox
 */
function bkap_filter_time_based_on_resource_availability( $current_date, $resource_availability_data, $time_data, $args, $resource_id, $product_id, $booking_settings ) {

	$resource_availability_data = is_array( $resource_availability_data ) ? $resource_availability_data : array();
	usort( $resource_availability_data, 'bkap_sort_date_time_ranges_by_priority' ); // lowest number will be first in the array.

	$bkap_availabile_date_str  = strtotime( $current_date );
	$disable_timeslots         = array();
	$final_timeslots_available = array();
	$final_timeslots_disable   = array();
	$stop                      = false;
	$bkap_all_data_unavailable = false;

	if ( isset( $booking_settings['bkap_all_data_unavailable'] ) && 'on' === $booking_settings['bkap_all_data_unavailable'] ) {
		$bkap_all_data_unavailable = true;
		$resource_id               = '';
	}

	if ( 'fixed_time' === $args['type'] ) {
		$rdrop_down         = explode( '|', $time_data );
		$original_timeslots = $rdrop_down;
		$fixed_time         = true;
	} else {
		$rdrop_down = $time_data;
		$fixed_time = false;
	}

	$mta_must_array    = array();
	$mta_holiday_array = array();

	foreach ( $rdrop_down as $time_key => $time_value ) {

		if ( $fixed_time ) {
			$booking_time_value   = explode( ' - ', $time_value );
			$booking_from_time    = $booking_time_value[0];
			$booking_to_time      = isset( $booking_time_value[1] ) ? $booking_time_value[1] : $booking_time_value[0];
			$booking_datefromtime = strtotime( $current_date . ' ' . $booking_from_time );
			$booking_datetotime   = strtotime( $current_date . ' ' . $booking_to_time );
		} else {
			$booking_datefromtime = $time_value;
			$booking_datetotime   = $time_value + $args['interval'];
		}

		foreach ( $resource_availability_data as $key => $value ) {
			$date_range_start = '';
			$date_range_end   = '';
			$check            = false;
			$date_check       = false;

			switch ( $value['type'] ) {

				case 'custom':
					$date_range_start = strtotime( $value['from'] );
					$date_range_end   = strtotime( $value['to'] . ' 23:59' );
					$date_check       = true;

					break;
				case 'months':
					$month_range = bkap_get_month_range( $value['from'], $value['to'] );
					$date_month  = gmdate( 'n', $bkap_availabile_date_str );

					if ( $date_month >= $value['from'] && $date_month <= $value['to'] ) {
						if ( $value['bookable'] == 0 ) {
							if ( ! in_array( $time_value, $final_timeslots_available ) ) {
								$final_timeslots_disable[] = $time_value;
							}
						} else {
							if ( ! in_array( $time_value, $final_timeslots_disable ) && ! in_array( $time_value, $final_timeslots_available ) ) {
								$final_timeslots_available[] = $time_value;
							}
						}
					}

					$date_range_start = strtotime( $month_range['start'] );
					$date_range_end   = strtotime( $month_range['end'] . ' 23:59' );
					$date_check       = true;
					break;
				case 'weeks':
					$week_range = bkap_get_week_range( $value['from'], $value['to'] );

					$date_range_start = strtotime( $week_range['start'] );
					$date_range_end   = strtotime( $week_range['end'] . ' 23:59' );
					$date_check       = true;
					break;
				case 'days':
					$date_status = '';
					$date_day    = gmdate( 'w', $bkap_availabile_date_str );
					$date_status = bkap_get_day_between_Week( $value['from'], $value['to'] );

					if ( strpos( $date_status, $date_day ) !== false ) {
						$date_range_start = $bkap_availabile_date_str;
						$date_range_end   = $bkap_availabile_date_str + 86400;
						$date_check       = true;
					}
					break;

				case 'time':
				case 'time:range':
				case 'time:0':
				case 'time:1':
				case 'time:2':
				case 'time:3':
				case 'time:4':
				case 'time:5':
				case 'time:6':
					$resource_from = $value['from'];
					$resource_to   = $value['to'];
					if ( 'time' === $value['type'] ) {
						$check = ( '' !== $resource_from && '' !== $resource_to ) ? true : false;
					} else {
						$rad_explode = explode( ':', $value['type'] );

						if ( 'range' === $rad_explode[1] ) {
							if ( $bkap_availabile_date_str >= strtotime( $value['from_date'] ) && $bkap_availabile_date_str <= strtotime( $value['to_date'] ) ) {
								$check = true;
							}
						} else {
							$weekday = gmdate( 'w', $bkap_availabile_date_str );
							if ( $weekday == $rad_explode[1] ) {
								$check = true;
							}
						}
					}

					if ( $check ) {
						$date_range_start = strtotime( $current_date . ' ' . $resource_from );
						$date_range_end   = strtotime( $current_date . ' ' . $resource_to );
					}

					if ( $check ) {
						if ( $value['bookable'] == 0 ) {
							if ( ! in_array( $current_date, $mta_holiday_array ) ) {
								array_push( $mta_holiday_array, $current_date );
							}

							if ( ( $booking_datefromtime > $date_range_start && $booking_datefromtime < $date_range_end ) || ( $booking_datetotime > $date_range_start && $booking_datetotime < $date_range_end ) || ( $booking_datefromtime <= $date_range_start && $booking_datetotime >= $date_range_end ) ) {
								if ( $fixed_time ) {
									$time_data = str_replace( $time_value . '|', '', $time_data );

									if ( ! in_array( $time_value, $final_timeslots_available ) ) {
										$final_timeslots_disable[] = $time_value;
									}
								} else {
									if ( ! in_array( $time_value, $final_timeslots_available ) ) {
										$final_timeslots_disable[] = $time_value;
									}
									unset( $time_data[ $time_key ] );
								}
							}
						} else { // bookable.
							if ( ! in_array( $current_date, $mta_must_array ) ) {
								array_push( $mta_must_array, $current_date );
							}

							if ( ( $booking_datefromtime >= $date_range_start && $booking_datefromtime < $date_range_end ) || ( $booking_datefromtime <= $date_range_start && $booking_datetotime >= $date_range_end ) ) {
								if ( $fixed_time ) {
									if ( ! in_array( $time_value, $final_timeslots_disable ) && ! in_array( $time_value, $final_timeslots_available ) ) {
										$final_timeslots_available[] = $time_value;
									}
								} else {
									if ( ! in_array( $time_value, $final_timeslots_disable ) && ! in_array( $time_value, $final_timeslots_available ) ) {
										$final_timeslots_available[] = $time_value;
									}
								}
							}
						}
					}
					break;
			}

			if ( $date_check ) {

				if ( $value['bookable'] == 0 ) {
					if ( ( $booking_datefromtime > $date_range_start && $booking_datefromtime < $date_range_end ) || ( $booking_datetotime > $date_range_start && $booking_datetotime < $date_range_end ) || ( $booking_datefromtime <= $date_range_start && $booking_datetotime >= $date_range_end ) ) {
						if ( $fixed_time ) {
							// $time_data = str_replace( $time_value . '|', '', $time_data );

							if ( ! in_array( $time_value, $final_timeslots_available ) ) {
								$final_timeslots_disable[] = $time_value;
							}
						} else {
							if ( ! in_array( $time_value, $final_timeslots_available ) ) {
								$final_timeslots_disable[] = $time_value;
							}
						}
					}
				} else { // bookable.
					if ( ( $booking_datefromtime > $date_range_start && $booking_datefromtime < $date_range_end ) || ( $booking_datetotime > $date_range_start && $booking_datetotime < $date_range_end ) || ( $booking_datefromtime <= $date_range_start && $booking_datetotime >= $date_range_end ) ) {
						if ( $fixed_time ) {
							if ( ! in_array( $time_value, $final_timeslots_disable ) && ! in_array( $time_value, $final_timeslots_available ) ) {
								$final_timeslots_available[] = $time_value;
							}
						} else {
							if ( ! in_array( $time_value, $final_timeslots_disable ) && ! in_array( $time_value, $final_timeslots_available ) ) {
								$final_timeslots_available[] = $time_value;
							}
						}
					}
				}
			}
		}
	} // end foreach rdropdown.

	$bookable_check = apply_filters( 'bkap_consider_availability_according_bookable_range', false );

	if ( ( $bkap_all_data_unavailable || $bookable_check ) && isset( $final_timeslots_available ) ) {
		if ( 'fixed_time' === $args['type'] ) {
			$time_data = implode( '|', $final_timeslots_available );
		} else {
			$time_data = $final_timeslots_available;
		}
	}

	return $time_data;
}

function bkap_event_based_on_time_availability_data( $product_id, $event, $booking_settings, $resource_id = 0 ) {

	$status = true;
	if ( isset( $event['extendedProps']['timeslot_value'] ) ) {
		$status = bkap_is_time_available_in_availability_data( $product_id, $event['extendedProps']['timeslot_value'], $event, $booking_settings, $resource_id );
	}
	return $status;
}

function bkap_is_time_available_in_availability_data( $product_id, $time_slot, $event, $booking_settings, $resource_id = 0 ) {

	if ( $resource_id > 0 ) {
		$resource          = new BKAP_Product_Resource( $resource_id, $product_id );
		$availability_data = $resource->get_resource_availability();
	} else {
		$availability_data = $booking_settings['bkap_manage_time_availability'];
	}

	$time_slot_explode = explode( ' - ', $time_slot );
	$time_slot         = bkap_common::bkap_get_formated_time( $time_slot_explode[0] );
	if ( isset( $time_slot_explode[1] ) ) {
		$to_time   = bkap_common::bkap_get_formated_time( $time_slot_explode[1] );
		$time_slot = $time_slot . ' - ' . $to_time;
	}

	$weekday  = $event['rrule']['byweekday'][0];
	$weekdays = array(
		'mo' => 'MONDAY',
		'tu' => 'TUESDAY',
		'we' => 'WEDNESDAY',
		'th' => 'THURSDAY',
		'fr' => 'FRIDAY',
		'sa' => 'SATURDAY',
		'su' => 'SUNDAY',
	);
	$day      = $weekdays[ $weekday ];
	$date     = gmdate( 'Y-m-d', strtotime( 'next ' . $day ) );

	$time = bkap_filter_time_based_on_resource_availability( $date, $availability_data, $time_slot . '|', array( 'type' => 'fixed_time' ), $resource_id, $product_id, $booking_settings );

	if ( '' === $time ) {
		return false;
	}

	return true;
}

/**
 * Return selected zoom meeting host
 *
 * @return string
 * @since 5.2.0
 */
function bkap_get_posted_meeting_host() {
	$resource_host = '';
	if ( isset( $_POST['_bkap_zoom_meeting_host'] ) ) {
		$resource_host = $_POST['_bkap_zoom_meeting_host'];
	}
	return $resource_host;
}

/**
 * Return price based standard decimal thousand separator.
 *
 * @return string
 * @since 4.6.0
 */

function get_standard_decimal_thousand_separator_price( $price ) {

	$decimal_separator  = wc_get_price_decimal_separator();
	$thousand_separator = wc_get_price_thousand_separator();

	if ( '' != $thousand_separator ) {
		$price_with_thousand_separator_removed = str_replace( $thousand_separator, '', $price );
	} else {
		$price_with_thousand_separator_removed = $price;
	}

	if ( '.' != $decimal_separator ) {
		$price = str_replace( $decimal_separator, '.', $price_with_thousand_separator_removed );
	}

	return $price;
}

/**
 * Return date in d-n-Y format after adding days to original date.
 *
 * @param string $date Date in d-n-Y format
 * @param int    $day Number of days to be added to date
 *
 * @return string
 * @since 4.8.0
 */

function bkap_add_days_to_date( $date, $day ) {

	$day_str = '+ ' . $day . ' days';

	return gmdate( 'd-n-Y', strtotime( $date . $day_str ) );
}

/**
 * Return date in d-n-Y format after adding days to original date.
 *
 * @param string $date Date in d-n-Y format
 * @param int    $format format of the date to be created
 *
 * @return string
 * @since 4.8.0
 */

function bkap_date_as_format( $date, $format ) {

	return gmdate( $format, strtotime( $date ) );
}

/**
 * Create array of dates between give start and end dateReturn date in d-n-Y format after adding days to original date.
 *
 * @param string $start Start Date
 * @param string $end End Date
 * @param string $format Format of the date (Optional)
 *
 * @return array $new_week_days_arr Array of dates
 * @since 4.8.0
 */

function bkap_array_of_given_date_range( $start, $end, $format = 'Y-m-d' ) {

	$start_ts = strtotime( $start );
	$end_ts   = strtotime( $end );

	$new_week_days_arr = array();
	$start             = gmdate( $format, $start_ts );

	while ( $start_ts <= $end_ts ) {

		$new_week_days_arr [] = $start;
		$start_ts             = strtotime( '+1 day', $start_ts );
		$start                = gmdate( $format, $start_ts );
	}

	return $new_week_days_arr;
}

/**
 * Create array of dates between give start and end dateReturn date in d-n-Y format after adding days to original date.
 *
 * @param int        $product_id Product ID
 * @param string/int $resource_id Resource ID
 * @param array      $date_range array of Dates
 *
 * @return boolean $status Return true if date range has date on which the resource is lockedout
 * @since 4.8.0
 */

function bkap_check_resource_booked_in_date_range( $product_id, $resource_id, $date_range ) {

	$resource_booked_data = bkap_calculate_bookings_for_resource( $resource_id, $product_id );
	$status               = false;

	if ( isset( $resource_booked_data['bkap_locked_dates'] ) && $resource_booked_data['bkap_locked_dates'] != '' ) {

		$resource_locked_dates_string = $resource_booked_data['bkap_locked_dates'];
		$resource_locked_dates_string = str_replace( '"', '', $resource_locked_dates_string );
		$resource_locked_dates        = explode( ',', $resource_locked_dates_string );

		foreach ( $date_range as $key => $value ) {

			if ( in_array( $value, $resource_locked_dates ) ) {
				$status = true;
				break;
			}
		}
	}

	return $status;
}

/**
 * Delete event from Google Calendar for the given order item id
 *
 * @param int $product_id Product ID
 * @param int $item_id Order Item ID
 * @since 4.8.0
 */

function bkap_delete_event_from_gcal( $product_id, $item_id, $item_number = 0 ) {

	$pro_id           = $product_id;
	$user_id          = get_current_user_id(); // user ID
	$gcal             = new BKAP_Google_Calendar();
	$booking_settings = get_post_meta( $product_id, 'woocommerce_booking_settings', true );

	if ( $gcal->get_api_mode( $user_id, $product_id ) != 'disabled' ) {

		$event_uids = get_post_meta( $product_id, 'bkap_event_uids_ids', true );

		if ( is_array( $event_uids ) && ! empty( $event_uids ) && array_key_exists( $item_id, $event_uids ) ) {
			$pro_id = $product_id;
		} else {
			$pro_id = 0;
		}
		$gcal->delete_event( $item_id, $user_id, $pro_id, '', $item_number );
	}

	do_action( 'bkap_delete_event_from_calendar', $item_id, $product_id, $item_number );

}

/**
 * Insert event to Google Calendar for the given order item id
 *
 * @param object $order_obj Order Object
 * @param int    $product_id Product ID
 * @param int    $item_id Order Item ID
 * @since 4.8.0
 */

function bkap_insert_event_to_gcal( $order_obj, $product_id, $item_id, $item_number = -1 ) {

	$user_id         = get_current_user_id();
	$gcal            = new BKAP_Google_Calendar();
	$order_items_new = $order_obj->get_items();

	foreach ( $order_items_new as $oid => $o_value ) {

		if ( $oid == $item_id ) {
			$itm_value     = $o_value;
			$item_num      = ( $item_number < 0 ) ? 0 : $item_number;
			$order_id      = $order_obj->get_id();
			$event_details = bkap_cancel_order::bkap_create_gcal_object( $order_id, $itm_value, $order_obj, $item_num );

			if ( in_array( $gcal->get_api_mode( $user_id, $product_id ), array( 'directly', 'oauth' ), true ) ) {
				$booking_settings = bkap_setting( $product_id );
				if ( ( ! isset( $booking_settings['product_sync_integration_mode'] ) ) || ( isset( $booking_settings['product_sync_integration_mode'] ) && 'disabled' == $booking_settings['product_sync_integration_mode'] ) ) {
					$product_id = 0;
				}
				$status = $gcal->insert_event( $event_details, $item_id, $user_id, $product_id, false, $item_number );

				if ( $status ) {
					// add an order note, mentioning an event has been created for the item.
					$post_title = $event_details['product_name'];
					$order_note = __( "Booking details for $post_title have been exported to the Google Calendar", 'woocommerce-booking' );

					$order_obj->add_order_note( $order_note );
				}
			}

			do_action( 'bkap_insert_event_to_calendar', $item_id, $o_value, $order_id, $order_obj, $event_details, $item_num );
			break;
		}
	}
}

if ( ! function_exists( 'bkap_oauth_google_calendar_options' ) ) {
	/**
	 * This function is to adding the options for OAuth Integration.
	 *
	 * @param int $product_id Product ID.
	 * @param int $user_id User ID.
	 * @version 5.1.0
	 * @since   5.1.0
	 */
	function bkap_get_oauth_google_calendar_options( $product_id = 0, $user_id = 1 ) {

		$copy_clipboard_str = __( 'Copied!', 'woocommerce-booking' );
		$copy_clipboard     = '<a href="javascript:void(0)" style="border: 1px solid #eee;padding: 4px;" id="bkap_copy_redirect_uri" data-selector-to-copy="#bkap-auth-redirect-uri" data-tip=' . $copy_clipboard_str . ' class="dashicons dashicons-admin-page bkap-oauth-rurl-copy-to-clipboard"></a><span id="bkap_redirect_uri_copied"></span>';
		$bkap_oauth_gcal    = new BKAP_OAuth_Google_Calendar( $product_id, $user_id );
		$integration        = $bkap_oauth_gcal->bkap_is_integration_active();
		$redirect_uri       = $bkap_oauth_gcal->bkap_get_redirect_uri();
		$google_auth_url    = $bkap_oauth_gcal->bkap_get_google_auth_url();
		$calendar_list      = $bkap_oauth_gcal->bkap_get_calendar_list_options();
		$hide_logout        = 'display:none;';
		$hide_calendar      = 'display:none;';
		$hide_connect       = '';
		$successful_msg     = '';
		$instruction        = __( '<br>To find your Client ID and Client Secret please follow the <a href="https://www.tychesoftwares.com/docs/docs/booking-appointment-plugin-for-woocommerce-new/integrations/google-calendar/" target="_blank">instructions.</a><br/>', 'woocommerce-booking' );

		if ( $product_id ) {
			$oauth_settings   = get_post_meta( $product_id, '_bkap_calendar_oauth_integration', true );
			$product_edit_url = get_edit_post_link( $product_id );
			$redirect_args    = array( 'bkap_logout' => $product_id );
			$logout_url       = add_query_arg( $redirect_args, $product_edit_url );
		} else {
			$oauth_settings = get_option( 'bkap_calendar_oauth_integration', null );
			$redirect_args  = array(
				'page'        => 'woocommerce_booking_page',
				'action'      => 'calendar_sync_settings',
				'post_type'   => 'bkap_booking',
				'bkap_logout' => 0,
			);
			$logout_url     = add_query_arg( $redirect_args, admin_url( '/edit.php?' ) );
			$instruction    = '';
		}

		if ( empty( $oauth_settings ) ) {
			$hide_connect = 'display:none;';
		} else {
			if ( isset( $oauth_settings['client_id'] ) && '' !== $oauth_settings['client_id'] && isset( $oauth_settings['client_secret'] ) && '' !== $oauth_settings['client_secret'] ) {
				$hide_connect = ( $integration ) ? 'display:none;' : '';
			} else {
				$hide_connect = 'display:none;';
			}
		}

		$id_secret = array();
		if ( $integration ) {
			$hide_logout    = '';
			$successful_msg = __( 'Successfully authenticated.', 'woocommerce-booking' );
			$hide_calendar  = '';
			$id_secret      = array( 'readonly' => 'readonly' );
		}

		$options = array(
			array(
				'id'                => 'client_id',
				'title'             => __( 'Client ID', 'woocommerce-booking' ),
				'type'              => 'text',
				'default'           => '',
				'css'               => 'width:100%;',
				'custom_attributes' => $id_secret,
			),
			array(
				'id'                => 'client_secret',
				'title'             => __( 'Client Secret', 'woocommerce-booking' ),
				'type'              => 'text',
				'default'           => '',
				'css'               => 'width:100%;',
				'custom_attributes' => $id_secret,
				'desc'              => $instruction,
			),
			array(
				'id'                => 'redirect_uri',
				'title'             => __( 'Redirect URI', 'woocommerce-booking' ),
				'type'              => 'text',
				'default'           => $redirect_uri,
				'css'               => 'width:89%;',
				'desc'              => $copy_clipboard,
				'custom_attributes' => array(
					'readonly' => 'readonly',
					'class'    => 'bkap-auth-redirect-uri',
				),
			),
			array(
				'id'      => 'calendar_id',
				'title'   => __( 'Calendar to be used', 'woocommerce-booking' ),
				'type'    => 'select',
				'default' => 'text',
				'css'     => $hide_calendar,
				'options' => $calendar_list,
			),
			array(
				'title'             => '',
				'value'             => __( 'Connect to Google', 'woocommerce-booking' ),
				'default'           => __( 'Connect to Google', 'woocommerce-booking' ),
				'id'                => 'connect_to_google',
				'type'              => 'button',
				'css'               => $hide_connect,
				'link'              => 'yes',
				'custom_attributes' => array(
					'href'  => $google_auth_url,
					'class' => 'button-primary',
				),
			),
			array(
				'title'             => '',
				'value'             => __( 'Logout', 'woocommerce-booking' ),
				'default'           => __( 'Logout', 'woocommerce-booking' ),
				'id'                => 'logout',
				'type'              => 'button',
				'css'               => $hide_logout,
				'link'              => 'yes',
				'desc'              => $successful_msg,
				'custom_attributes' => array(
					'href'  => $logout_url,
					'class' => 'button-secondary',
				),
			),
		);

		return apply_filters( 'bkap_oauth_google_calendar_options', $options );
	}
}

/**
 * Get label based on the passed string.
 *
 * @param string $option_str String E.g if you want start date lable then string will be 'start_date'
 * @since 4.10.0
 */
function bkap_option( $option_str ) {

	$label = $option = '';

	switch ( $option_str ) {
		case 'start_date':
			$option = 'book_date-label';
			break;
		case 'end_date':
			$option = 'checkout_date-label';
			break;
		case 'time':
			$option = 'book_time-label';
			break;
		case 'choose_time':
			$option = 'book_time-select-option';
			break;
		case 'fixed_block':
			$option = 'book_fixed-block-label';
			break;
		case 'price':
			$option = 'book_price-label';
			break;
		case 'cart_start_date':
			$option = 'book_item-cart-date';
			break;
		case 'cart_end_date':
			$option = 'checkout_item-cart-date';
			break;
		case 'cart_time':
			$option = 'book_item-cart-time';
			break;
		case 'email_start_date':
			$option = 'book_item-meta-date';
			break;
		case 'email_end_date':
			$option = 'checkout_item-meta-date';
			break;
		case 'email_time':
			$option = 'book_item-meta-time';
			break;
		case 'mycal':
			$option = 'book_ics-file-name';
			break;
		case 'add_to_cart':
			$option = 'bkap_add_to_cart';
			break;
		case 'check_availability':
			$option = 'bkap_check_availability';
			break;
	}

	$label = get_option( $option );

	return $label;
}

/**
 * Get tip labels for disabled dates in booking calendar.
 *
 * @since 4.10.0
 */

function bkap_get_disabled_date_labels() {

	return apply_filters(
		'bkap_change_hover_text_for_disabled_dates',
		array(
			'holiday_label'            => __( 'Holiday', 'woocommerce-booking' ),
			'unavailable_label'        => __( 'Unavailable for Booking', 'woocommerce-booking' ),
			'blocked_label'            => __( 'Blocked', 'woocommerce-booking' ),
			'booked_label'             => __( 'Booked', 'woocommerce-booking' ),
			'msg_unavailable'          => __( 'Some of the dates in the selected range are unavailable. Please try another date range.', 'woocommerce-booking' ),
			'date_unavailable'         => __( 'Selected date is unavailable for booking. Please try another date.', 'woocommerce-booking' ),
			'msg_unavailable_resource' => array(
				__( 'is unavailable for booking on', 'woocommerce-booking' ),
				__( 'Please select another date.', 'woocommerce-booking' ),
			),
			'rent_label'               => __( 'On Rent', 'woocommerce-booking' ),
			'time_slot_not_selected'   => __( 'Please select time slot...', 'woocommerce-booking' ),
		)
	);
}

/**
 * Get string of child id for the grouped product.
 *
 * @param int    $post_id Product ID
 * @param object $_product Product Object
 *
 * @return string $child_ids_str Ids of child product saperated by comma
 * @since 4.10.0
 */

function bkap_grouped_child_ids( $post_id, $_product ) {

	$child_ids_str = '';

	if ( $_product->get_type() === 'grouped' ) {

		if ( function_exists( 'icl_object_id' ) ) {
			$_parent_obj = wc_get_product( $post_id );
		} else {
			$_parent_obj = $_product;
		}

		if ( $_parent_obj->has_child() ) {
			$child_ids = $_parent_obj->get_children();
		}

		if ( isset( $child_ids ) && count( $child_ids ) > 0 ) {
			foreach ( $child_ids as $k => $v ) {
				$child_ids_str .= $v . '-';
			}
		}
	}

	return $child_ids_str;
}

/**
 * Get string page.
 *
 * @return string $bkap_page Name of the current page where booking form is being checked.
 * @since 4.10.0
 */

function bkap_get_page() {
	$bkap_page = is_admin() ? 'bkap_post' : 'view-order';

	if ( is_product() ) {
		$bkap_page = 'product';
	} elseif ( is_cart() ) {
		$bkap_page = 'cart';
	} elseif ( is_checkout() ) {
		$bkap_page = 'checkout';
	} elseif ( is_account_page() ) {
		$bkap_page = 'view-order';
	} elseif ( isset( $_POST['is_bkap_booking_page'] ) ) {
		$bkap_page = 'bkap_post';
	} elseif ( isset( $_GET['page'] ) && 'bkap_create_booking_page' === $_GET['page'] ) {
		$bkap_page = 'create-booking';
	}

	return $bkap_page;
}

/**
 * Get all the future bookings
 *
 * @return array
 * @since 4.10.0
 */

function bkap_get_future_bookings() {
	$current_date = Date( 'Y-m-d', current_time( 'timestamp' ) );

	$args          = apply_filters(
		'bkap_get_future_bookings',
		array(
			'post_type'    => 'bkap_booking',
			'numberposts'  => -1,
			'post_status'  => array( 'paid', 'pending-confirmation', 'confirmed' ),
			'meta_key'     => '_bkap_start',
			'meta_value'   => gmdate( 'YmdHis', strtotime( $current_date ) ),
			'meta_compare' => '>=',
		)
	);
	$booking_posts = get_posts( $args );

	$bookings = array();
	foreach ( $booking_posts as $booking_post ) {
		$bookings[] = new BKAP_Booking( $booking_post->ID );
	}

	return $bookings;
}

/**
 * Get all the past bookings
 *
 * @return array
 * @since 5.14.0
 */
function bkap_get_past_bookings() {

	$current_date = Date( 'Y-m-d H:i:s', current_time( 'timestamp' ) );

	$args          = apply_filters(
		'bkap_get_past_bookings',
		array(
			'post_type'    => 'bkap_booking',
			'numberposts'  => -1,
			'post_status'  => array( 'paid', 'pending-confirmation', 'confirmed' ),
			'meta_key'     => '_bkap_start',
			'meta_value'   => gmdate( 'YmdHis', strtotime( $current_date ) ),
			'meta_compare' => '<',
		)
	);
	$booking_posts = get_posts( $args );

	$bookings = array();
	foreach ( $booking_posts as $booking_post ) {
		$bookings[] = new BKAP_Booking( $booking_post->ID );
	}

	return $bookings;
}



/**
 * Arrange timeslots in chronological order
 *
 * @return array
 * @since 4.10.0
 */

function bkap_sort_time_in_chronological( $timeslotarray ) {
	$tmp = array();
	foreach ( $timeslotarray as $times ) {
		if ( ! empty( $times ) ) {
			if ( strpos( $times, '-' ) ) {
				$tmp[ $times ] = strtotime( substr( $times, 0, strpos( $times, '-' ) ) );
			} else {
				$tmp[ $times ] = strtotime( $times );
			}
		}
	}
	asort( $tmp );
	$timeslotarraynew = array_keys( $tmp );

	return $timeslotarraynew;
}

/**
 * Get user id
 *
 * @return array
 * @since 4.11.0
 */

function bkap_get_user_id() {

	$admin_id = 0;
	$user     = get_user_by( 'email', get_option( 'admin_email' ) );

	if ( isset( $user->ID ) ) {
		$admin_id = $user->ID;
	} else {
		// get the list of administrators
		$args  = array(
			'role'   => 'administrator',
			'fields' => array( 'ID' ),
		);
		$users = get_users( $args );
		if ( isset( $users ) && count( $users ) > 0 ) {
			$admin_id = $users[0]->ID;
		}
	}

	return $admin_id;
}

/**
 * Get status of recurring weekdays
 *
 * @return bool $display_template true if any weekday is enable else false
 * @since 4.11.0
 */

function bkap_check_weekdays_status( $product_id, $display_template ) {

	$recurring_dates = get_post_meta( $product_id, '_bkap_recurring_weekdays' );
	if ( isset( $recurring_dates[0] ) ) {
		foreach ( $recurring_dates[0] as $recur_key => $recur_value ) {
			if ( isset( $recur_value ) && $recur_value != 'on' ) {
				$display_template = false;
			} elseif ( isset( $recur_value ) && $recur_value == 'on' ) {
				$display_template = true;
				break;
			}
		}
	}

	return $display_template;
}

/**
 * Get closest specific date to current date
 *
 * @param array $specific_dates Array of specific dates
 * @param int   $date_str Timestamp of current date
 * @return string Closest specific date to current date
 * @since 4.11.0
 */
function bkap_closest_specific_date( $specific_dates, $date_str ) {

	$specific_dates = array_keys( $specific_dates );

	foreach ( $specific_dates as $date ) {
		$date_strtotime = strtotime( $date );
		if ( $date_strtotime >= $date_str ) {
			$interval[]       = abs( $date_str - strtotime( $date ) );
			$interval_dates[] = $date;
		}
	}

	if ( ! empty( $interval_dates ) ) {
		asort( $interval );
		$closest = key( $interval );

		return $interval_dates[ $closest ];
	} else {
		return gmdate( 'j-n-Y', $date_str );
	}
}

/**
 * Show message when product is unavailable for booking.
 *
 * @since 4.11.0
 */

function bkap_unavailable_for_booking() {
	$unavailable_product_string = apply_filters( 'bkap_product_is_currently_unavaliable', __( 'The product is currently unavailable for booking. Please try again later.', 'woocommerce-booking' ) );
	?>
<div id="bkap-booking-form" class="bkap-booking-form stock out-of-stock">
    <?php echo $unavailable_product_string; ?>
</div>
<?php
}

/**
 * Show message when product is unavailable for booking.
 *
 * @param int   $product_id Product ID
 * @param array $booking_setting Booking setting of the product
 * @param int   $display_template true means display booking fields.
 *
 * @return bool $display_template true if showing booking fields else false
 * @since 4.11.0
 */

function bkap_display_booking_fields( $product_id, $booking_settings, $display_template ) {

	if ( isset( $booking_settings['booking_enable_time'] ) && $booking_settings['booking_enable_time'] == 'on' ) {
		$display_template = false; // assume no time slots are present

		$recurring_date_array = ( isset( $booking_settings['booking_recurring'] ) ) ? $booking_settings['booking_recurring'] : array();
		if ( isset( $booking_settings['booking_recurring'] ) && is_array( $booking_settings['booking_recurring'] ) && count( $booking_settings['booking_recurring'] ) > 0 && $booking_settings['booking_recurring_booking'] == 'on' ) {
			foreach ( $booking_settings['booking_recurring'] as $wkey => $wval ) {

				// for time slots, enable weekday only if 1 or more time slots are present
				if ( isset( $wval ) && $wval == 'on' && isset( $booking_settings['booking_time_settings'] ) && array_key_exists( $wkey, $booking_settings['booking_time_settings'] ) && count( $booking_settings['booking_time_settings'][ $wkey ] ) > 0 ) {
					$display_template = true;
					$bkap_time        = true;
				}
			}
		}

		if ( ! $display_template ) {
			$display_template = bkap_common::bkap_check_specific_date_has_timeslot( $product_id );
		}
	} elseif ( isset( $booking_settings['booking_enable_time'] ) && $booking_settings['booking_enable_time'] == 'duration_time' ) {
		 $display_template = false;
		if ( isset( $booking_settings['bkap_duration_settings'] ) && count( $booking_settings['bkap_duration_settings'] ) > 0 ) {
			$display_template = true;
			$bkap_time        = true;
		}
	}

	if ( isset( $booking_settings['booking_specific_booking'] ) && $booking_settings['booking_specific_booking'] == 'on' ) {
		$today_midnight    = strtotime( 'today midnight' );
		$booking_dates_arr = isset( $booking_settings['booking_specific_date'] ) ? $booking_settings['booking_specific_date'] : array();;
		foreach ( $booking_dates_arr as $key => $value ) {
			if ( strtotime( $key ) < $today_midnight ) {
				unset( $booking_dates_arr[ $key ] );
			}
		}
		if ( empty( $booking_dates_arr ) ) {
			$display_template = bkap_check_weekdays_status( $product_id, $display_template );
		}
	}

	// If Multiple Nights is enabled but all the Weekdays are disabled then do not show template.
	if ( isset( $booking_settings['booking_enable_multiple_day'] ) && $booking_settings['booking_enable_multiple_day'] == 'on' && ! $display_template ) {
		$display_template = bkap_check_weekdays_status( $product_id, $display_template );
	}

	if ( isset( $booking_settings['booking_recurring_booking'] ) && $booking_settings['booking_recurring_booking'] == '' && $booking_settings['booking_specific_booking'] == '' ) {
		$display_template = false;
	}

	/* Custom Range Available and Passed or not */
	if ( $display_template && isset( $booking_settings['booking_date_range'] ) && ! empty( $booking_settings['booking_date_range'] ) ) {
		$booking_date_range = $booking_settings['booking_date_range'];
		$today_midnight     = strtotime( 'today midnight' );
		$in_range           = false;

		foreach ( $booking_date_range as $date_range ) {
			$start = strtotime( $date_range['start'] );
			$end   = strtotime( $date_range['end'] );
			if ( ( $today_midnight >= $start && $today_midnight <= $end ) || ( $start >= $today_midnight && $end >= $today_midnight ) ) {
				$in_range = true;
				break;
			}

			if ( '' != $date_range['years_to_recur'] && $date_range['years_to_recur'] > 0 ) {
				$year       = $date_range['years_to_recur'];
				$start_year = strtotime( '+' . $year . ' year', $start );
				$end_year   = strtotime( '+' . $year . ' year', $end );

				if ( ( $today_midnight >= $start_year && $today_midnight <= $end_year ) || ( $start_year >= $today_midnight && $end_year >= $today_midnight ) ) {
					$in_range = true;
					break;
				}
			}
		}

		$display_template = ( $in_range ) ? true : false;
	}

	if ( isset( $bkap_time ) && isset( $booking_settings['bkap_all_data_unavailable'] ) && 'on' === $booking_settings['bkap_all_data_unavailable'] && isset( $booking_settings['bkap_manage_time_availability'] ) && empty( $booking_settings['bkap_manage_time_availability'] ) ) {
		$display_template = false;
	}

	$display_template = apply_filters( 'bkap_display_booking_fields', $display_template, $product_id, $booking_settings );

	return $display_template;
}

/**
 * Calculate value for showing the total stock on the front end.
 *
 * @param array $booking_setting Booking setting of the product
 *
 * @return string $total_stock_message Total Available booking for product
 * @since 4.11.0
 */

function bkap_total_stock_message( $booking_settings, $product_id, $booking_type ) {

	$total_stock_message = '';
	switch ( $booking_type ) {
		case 'date_time':
		case 'duration_time':
		case 'only_day':
		case 'multidates':
		case 'multidates_fixedtime':
			$total_stock_message = __( 'Select a date to view available bookings.', 'woocommerce-booking' );
			if ( isset( $booking_settings['enable_inline_calendar'] ) && $booking_settings['enable_inline_calendar'] == 'on' ) {
				$total_stock_message = '';
			}

			if ( isset( $booking_settings['_bkap_product_resource_position'] ) && 'after' === $booking_settings['_bkap_product_resource_position'] ) {
				if ( 'only_day' !== $booking_type ) {
					$total_stock_message = __( 'Select a date to view available slots & resources.', 'woocommerce-booking' );
				} else {
					$total_stock_message = __( 'Select a date to view available resources.', 'woocommerce-booking' );
				}
			}
			break;
		case 'multiple_days':
			$available_stock     = __( 'Unlimited', 'woocommerce-booking' );
			$available_stock     = bkap_get_maximum_booking( $product_id, $booking_settings );
			$total_stock_message = get_option( 'book_stock-total' );
			$total_stock_message = str_replace( 'AVAILABLE_SPOTS', $available_stock, $total_stock_message );
			break;
	}

	return apply_filters( 'bkap_select_a_date_to_view_booking', $total_stock_message, $product_id, $booking_settings );
}

/**
 * Removing Rental Settings from Booking Settings Array when Rental System Addon is deactivated
 *
 * @param array $bkap_settings Booking setting of the product
 *
 * @return array $bkap_settings Booking setting of the product
 * @since 4.13.0
 */
function bkap_init_parameter_localize_script_booking_settings_callback( $bkap_settings ) {

	if ( ! is_plugin_active( 'bkap-rental/rental.php' ) ) {
		if ( isset( $bkap_settings['booking_same_day'] ) ) {
			$bkap_settings['booking_same_day'] = '';
		}
		if ( isset( $bkap_settings['booking_charge_per_day'] ) ) {
			$bkap_settings['booking_charge_per_day'] = '';
		}
	}
	return $bkap_settings;
}
add_filter( 'bkap_init_parameter_localize_script_booking_settings', 'bkap_init_parameter_localize_script_booking_settings_callback', 10, 1 );

/**
 * Returning lockout for multiple nights
 *
 * @param int   $product_id Product ID
 * @param array $bkap_settings Booking setting of the product
 *
 * @return int $lockout Return value of the lockout set for product.
 * @since 4.13.1
 */
function bkap_get_maximum_booking( $product_id, $booking_settings ) {

	$lockout = ( isset( $booking_settings['booking_date_lockout'] ) && '' !== $booking_settings['booking_date_lockout'] ) ? $booking_settings['booking_date_lockout'] : 0;

	return apply_filters( 'bkap_booking_date_lockout', $lockout, $product_id, $booking_settings );
}

/**
 * Returning number of days between two dates
 *
 * @param string $date1 Check-in date
 * @param string $date2 Check-out date
 *
 * @return int $number Number of days between two dates .
 * @since 4.14.0
 */

function bkap_get_days_between_two_dates( $date1, $date2 ) {

	$number_of_days = strtotime( $date2 ) - strtotime( $date1 );
	$number         = floor( $number_of_days / ( 60 * 60 * 24 ) );
	$number         = ( $number == 0 ) ? 1 : $number;

	return $number;
}

/**
 * Returning number of days between two dates
 *
 * @param string $product_id Product ID
 * @param string $proid Product Parent ID
 * @param string $product_type Product Type
 * @return array $variations_selected Array of all variations
 *
 * @since 4.15.0
 */

function bkap_get_attribute_variations( $product_id, $proid, $product_type ) {

	$variations_selected = array();

	if ( $product_type == 'variable' ) {

		$variations_selected     = array();
		$string_explode          = '';
		$product_attributes      = get_post_meta( $proid, '_product_attributes', true );
		$product_attributes_lang = get_post_meta( $product_id, '_product_attributes', true );
		$i                       = 0;

		foreach ( $product_attributes as $key => $value ) {
			$string_explode = array();
			if ( isset( $_POST['attribute_selected'] ) ) {
				$string_explode = explode( '|', $_POST['attribute_selected'] );
			}

			if ( $value['is_taxonomy'] ) {
				$value_array = wc_get_product_terms( $product_id, $value['name'], array( 'fields' => 'names' ) );
			} else {
				$value_array = explode( ' | ', $value['value'] );
			}

			foreach ( $string_explode as $sk => $sv ) {
				if ( $sv == '' ) {
					unset( $string_explode[ $sk ] );
				}
			}

			foreach ( $value_array as $k => $v ) {
				$string1 = str_replace( ' ', '', $v );

				if ( count( $string_explode ) > 0 ) {
					$string2 = str_replace( ' ', '', $string_explode[ $i + 1 ] );
				} else {
					$string2 = '';
				}

				if ( strtolower( $string1 ) == strtolower( $string2 ) /* $pos_value != 0*/ ) {

					foreach ( $product_attributes_lang as $key1 => $value1 ) {

						if ( $key1 == $key ) {
							if ( $value['is_taxonomy'] ) {
								$value_array1 = wc_get_product_terms( $product_id, $value1['name'], array( 'fields' => 'names' ) );
							} else {
								$value_array1 = explode( ' | ', $value1['value'] );
							}
						}
					}

					$v = $value_array1[ $k ];
					if ( substr( $v, 0, -1 ) === ' ' ) {
						$result                      = rtrim( $v, ' ' );
						$variations_selected[ $key ] = $result;
					}

					if ( substr( $v, 0, 1 ) === ' ' ) {
						$result                      = preg_replace( '/ /', '', $v, 1 );
						$variations_selected[ $key ] = addslashes( $result );
					} else {
						$variations_selected[ $key ] = addslashes( $v );
					}
				}
			}
			$i++;
		}
	}

	return $variations_selected;
}

/**
 * Getting number of hour as per Advance Booking Period set
 *
 * @param string $booking_settings Booking Settings
 * @param string $product_id Product ID
 *
 * @return int $number Number of hours
 * @since 4.15.0
 */

function bkap_advance_booking_hrs( $booking_settings, $product_id ) {

	$advance_booking_hrs = 0;
	if ( isset( $booking_settings['booking_minimum_number_days'] ) && $booking_settings['booking_minimum_number_days'] != '' ) {
		$advance_booking_hrs = $booking_settings['booking_minimum_number_days'];
	}

	$advance_booking_hrs = apply_filters( 'bkap_advance_booking_period', $advance_booking_hrs, $booking_settings, $product_id );

	return $advance_booking_hrs;
}

/**
 * Function to check if the date time is passed or not based on Advance booking period
 *
 * @param string $date1 Date and time fetched from db
 * @param string $date2 Current date and time.
 * @param bool   $abpcheck Advance booking or not
 * @param string $phpversion PHP version compare
 *
 * @return bool $include true is datetime is greater than currrent time
 * @since 4.19.0
 */

function bkap_dates_compare( $date1, $date2, $abpcheck, $phpversion ) {

	$include    = true;
	$difference = $phpversion ? $date2->diff( $date1 ) : bkap_common::dateTimeDiff( $date2, $date1 );

	if ( $difference->days > 0 ) {
		$days_in_hour  = $difference->h + ( $difference->days * 24 );
		$difference->h = $days_in_hour;
	}

	if ( $abpcheck ) { // Advance booking period calculation
		if ( $difference->invert == 0 || $difference->h < $abpcheck ) {
			$include = false;
		}
	} else {
		if ( $difference->invert == 0 ) {
			$include = false;
		}
	}

	return $include;
}

/**
 * Getting number of hour as per Advance Booking Period set
 *
 * @param string $current_date Date/Time string
 *
 * @return string $weekday_string String based on the day number
 * @since 4.15.0
 */

function bkap_weekday_string( $current_date ) {

	$weekday_string = 'booking_weekday_' . gmdate( 'w', strtotime( $current_date ) );

	return $weekday_string;
}

/**
 * Fetching date record from booking_history table as per passed date
 *
 * @param string $product_id Product ID
 * @param string $date Date/Time string
 * @param string $from_time from time string
 * @param string $to_time to time string
 *
 * @return Object $results Return object of the records if any matching records are found.
 *
 * @since 4.15.0
 */

function bkap_fetch_date_records( $product_id, $date, $from_time = '', $to_time = '' ) {

	global $wpdb;
	$results = array();

	if ( $to_time != '' ) {
		$query   = 'SELECT total_booking, available_booking, start_date FROM `' . $wpdb->prefix . "booking_history`
                    WHERE post_id = %d
                    AND start_date = %s
                    AND from_time = %s
                    AND to_time = %s
                    AND status !=  'inactive' ";
		$results = $wpdb->get_results( $wpdb->prepare( $query, $product_id, $date, $from_time, $to_time ) );
	} else {
		$query   = 'SELECT total_booking, available_booking, start_date FROM `' . $wpdb->prefix . "booking_history`
                    WHERE post_id = %d
                    AND start_date = %s
                    AND from_time = %s
                    AND status !=  'inactive' ";
		$results = $wpdb->get_results( $wpdb->prepare( $query, $product_id, $date, $from_time ) );
	}
	return $results;
}

/**
 * Inserting the record into database
 *
 * @param string $post_id Product ID
 * @param string $weekday Weekday
 * @param string $start_date Start Date
 * @param string $end_date End Date
 * @param string $from_time From Time
 * @param string $to_time To Time
 * @param string $total_booking Total Booking
 * @param string $available_booking Available Booking
 *
 * @return string Insert Id of the record
 * @since 4.15.0
 */
function bkap_insert_record_booking_history( $post_id, $weekday, $start_date, $end_date, $from_time, $to_time, $total_booking = 0, $available_booking = 0 ) {

	global $wpdb;

	$query = 'INSERT INTO `' . $wpdb->prefix . "booking_history`
				(post_id,weekday,start_date,end_date,from_time,to_time,total_booking,available_booking)
				VALUES (
				'" . $post_id . "',
				'" . $weekday . "',
				'" . $start_date . "',
				'" . $end_date . "',
				'" . $from_time . "',
				'" . $to_time . "',
				'" . $total_booking . "',
				'" . $available_booking . "' )";
	$wpdb->query( $query );

	return $wpdb->insert_id;
}

/**
 * This function is used to remove the Proceed to checkout button from cart page and Place order button from checkout page
 *
 * @since 4.15.0
 */

function bkap_remove_proceed_to_checkout() {
	remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );
}

/**
 * This function will replace the shortcode in the error message with the actual data and return the string
 *
 * @param string $option Label option string
 * @param array  $actual_values Array of actual values which is to be replaced with the shortcode
 *
 * @return $message Error message string
 * @since 4.15.0
 */

function bkap_str_replace( $option, $actual_values ) {

	$msg_text = __( get_option( $option ), 'woocommerce-booking' ); // phpcs:ignore.

	switch ( $option ) {
		case 'book_no-booking-msg-date':
			$shortcode = array( 'PRODUCT_NAME', 'DATE' );
			break;
		case 'book_limited-booking-msg-date':
			$shortcode = array( 'PRODUCT_NAME', 'AVAILABLE_SPOTS', 'DATE' );
			break;
		case 'book_limited-booking-msg-time':
			$shortcode = array( 'PRODUCT_NAME', 'AVAILABLE_SPOTS', 'DATE', 'TIME' );
			break;
		case 'book_no-booking-msg-time':
			$shortcode = array( 'PRODUCT_NAME', 'DATE', 'TIME' );
			break;
	}

	$message = str_replace( $shortcode, $actual_values, $msg_text );

	return $message;
}

/**
 * This function will check if the order id information is present in the order_history table or not.
 *
 * @param string $order_id Order ID.
 *
 * @return bool $booking_data_present Return true of order info is present else false
 * @since 4.15.0
 */
function bkap_booking_data_present_check( $order_id ) {
	global $wpdb;

	$check_data = 'SELECT * FROM `' . $wpdb->prefix . 'booking_order_history` WHERE order_id = %s';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
	$results_check = $wpdb->get_results( $wpdb->prepare( $check_data, $order_id ) );

	$booking_data_present = ( count( $results_check ) > 0 ) ? true : false;

	return $booking_data_present;
}

/**
 * This function will return order id based on the item id
 *
 * @param string $item_id Order Item ID.
 *
 * @return string $order_id Return Order Id if found
 * @since 4.15.0
 */
function bkap_order_id_by_itemid( $item_id ) {

	global $wpdb;

	$order_query = 'SELECT order_id FROM `' . $wpdb->prefix . 'woocommerce_order_items` WHERE order_item_id = %s';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
	$order_results = $wpdb->get_results( $wpdb->prepare( $order_query, $item_id ) );

	if ( ! empty( $order_results ) ) {
		$order_id = $order_results[0]->order_id;
	} else {
		$order_id = 0;
	}

	return $order_id;
}

/***************************
 *   Timezone Conversion   *
 *                         *
 ***************************/

/**
 * Converting Date time string as per timezone's offset and returning string based on passed format
 *
 * @param string $time DateTime/Time string
 * @param string $offset Offset of the customer's timezone
 * @param string $format DateTime Format
 * @return string $from_time Returning date/time string based on the timezone offset
 *
 * @since 4.15.0
 */

function bkap_time_convert_asper_timezone( $time, $offset, $format = 'H:i' ) {

	date_default_timezone_set( bkap_booking_get_timezone_string() );

	$from_time = gmdate( $format, strtotime( $time ) - $offset );

	date_default_timezone_set( 'UTC' );

	return $from_time;
}

/**
 * Converting timeslot from customer time to system time
 *
 * @param string $timeslot TimeSlot : 10:00 - 11:00
 * @param string $offset Offset of the customer's timezone
 * @param string $format DateTime Format
 * @return string $timeslot returning Timeslot as system time
 *
 * @since 4.15.0
 */

function bkap_convert_timezone_time_to_system_time( $timeslot, $item_data, $format ) {

	$site_timezone     = bkap_booking_get_timezone_string();
	$customer_timezone = $item_data['wapbk_timezone'];
	$booking_date      = $item_data['wapbk_booking_date'];

	$timeslots = explode( ' - ', $timeslot );
	$from_time = bkap_convert_date_from_timezone_to_timezone( $booking_date . ' ' . $timeslots[0], $customer_timezone, $site_timezone, $format );
	$timeslot  = $from_time;

	if ( isset( $timeslots[1] ) && '' != $timeslots[1] ) {
		$to_time   = bkap_convert_date_from_timezone_to_timezone( $booking_date . ' ' . $timeslots[1], $customer_timezone, $site_timezone, $format );
		$timeslot .= ' - ' . $to_time;
	}

	return $timeslot;
}

/**
 * Converting timeslot from system time to timezone time
 *
 * @param string $timeslot TimeSlot : 10:00 - 11:00
 * @param string $offset Offset of the customer's timezone
 * @param string $format DateTime Format
 * @return string $timeslot returning Timeslot as timezone time
 *
 * @since 4.15.0
 */

function bkap_convert_system_time_to_timezone_time( $timeslot, $offset, $format ) {

	$timeslots = explode( ' - ', $timeslot );

	date_default_timezone_set( bkap_booking_get_timezone_string() );

	$from_time = gmdate( $format, strtotime( $timeslots[0] ) + $offset );
	// bkap_time_convert_asper_timezone( $timeslots[0], $offset, $format );
	$timeslot = $from_time;

	if ( isset( $timeslots[1] ) && '' != $timeslots[1] ) {
		$to_time = gmdate( $format, strtotime( $timeslots[1] ) + $offset );
		// bkap_time_convert_asper_timezone( $timeslots[1], $offset, $format );
		$timeslot .= ' - ' . $to_time;
	}

	date_default_timezone_set( 'UTC' );

	return $timeslot;
}

/**
 * Checking if the timezone setting is enable or not
 *
 * @param Object $global_settings Global Booking Setting
 * @return bool true timezone setting is enabled else false
 *
 * @since 4.15.0
 */

function bkap_timezone_check( $global_settings ) {

	if ( isset( $global_settings->booking_timezone_conversion ) && $global_settings->booking_timezone_conversion == 'on' ) {

		if ( isset( $_POST['bkap_page'] ) && ( $_POST['bkap_page'] == 'bkap_post' || $_POST['bkap_page'] == '' ) ) { // Edit Booking Page and manual booking page
			return false;
		}

		if ( isset( $_POST['page'] ) && 'bkap_page' === $_POST['page'] ) { // phpcs:ignore
			return false;
		}

		if ( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'bkap_booking' ) {
			return false;
		}

		if ( isset( $_REQUEST['post_type'] ) && $_REQUEST['post_type'] == 'bkap_booking' ) {
			return false;
		}

		return true;
	}

	return false;
}

/**
 * If not required then remove once timezone is implemented
 */

function timeZoneConvert( $fromTime, $fromTimezone, $toTimezone, $format = 'Y-m-d H:i:s' ) {

	$from    = new DateTimeZone( $fromTimezone ); // create timeZone object , with fromtimeZone
	$to      = new DateTimeZone( $toTimezone ); // create timeZone object , with totimeZone
	$orgTime = new DateTime( $fromTime, $from ); // read give time into ,fromtimeZone
	$toTime  = new DateTime( $orgTime->format( 'c' ) );
	// fromte input date to ISO 8601 date (added in PHP 5). the create new date time object

	$toTime->setTimezone( $to ); // set target time zone to $toTme ojbect.
	return $toTime->format( $format );
}

/**
 * Get timezone string.
 *
 * inspired by https://wordpress.org/plugins/event-organiser/
 *
 * @return string
 */

function bkap_booking_get_timezone_string() {

	$timezone   = get_option( 'timezone_string' );
	$gmt_offset = get_option( 'gmt_offset' );

	// Remove old Etc mappings. Fallback to gmt_offset.
	if ( ! empty( $timezone ) && false !== strpos( $timezone, 'Etc/GMT' ) ) {
		$timezone = '';
	}

	if ( empty( $timezone ) && 0 != $gmt_offset ) {
		// Use gmt_offset
		$gmt_offset   *= 3600; // convert hour offset to seconds
		$allowed_zones = timezone_abbreviations_list();

		foreach ( $allowed_zones as $abbr ) {
			foreach ( $abbr as $city ) {
				if ( $city['offset'] == $gmt_offset ) {
					$timezone = $city['timezone_id'];
					break 2;
				}
			}
		}
	}

	// Issue with the timezone selected, set to 'UTC'
	if ( empty( $timezone ) ) {
		$timezone = 'UTC';
	}

	return $timezone;
}

/**
 * Getting offset difference between store and client timezone and converting it to seconds
 *
 * @param string $bkap_offset_value Offset value captured from cookie or order item
 * @return string $offset Offset based on the store timezone
 *
 * @since 4.15.0
 */

function bkap_get_offset( $bkap_offset_value ) {

	$gmt_offset = get_option( 'gmt_offset' );
	$gmt_offset = $gmt_offset * 60 * 60;
	// $bkap_offset    = Bkap_Timezone_Conversion::get_timezone_var( 'bkap_offset' );
	$bkap_offset = $bkap_offset_value;
	$bkap_offset = (int)$bkap_offset * 60;
	$offset      = $bkap_offset - $gmt_offset;

	return $offset;
}

/**
 * Getting offset of the passed date based on the timezone
 *
 * @param string $datestr timestamp of date/time
 * @param string $timezone_name Offset based on the store timezone
 * @return string $offset Offset based on the store timezone
 *
 * @since 4.15.0
 */

function bkap_get_offset_from_date( $datestr, $timezone_name ) {

	$ymd             = date( 'Y-m-d H:m:s', $datestr );
	$timezone_string = bkap_booking_get_timezone_string();
	$timezone_obj    = new DateTimeZone( $timezone_string );
	$date_obj        = new DateTime( $ymd, $timezone_obj );
	$gmt_offset      = $timezone_obj->getOffset( $date_obj );

	date_default_timezone_set( Bkap_Timezone_Conversion::get_timezone_var( 'bkap_timezone_name' ) );

	$offset = date( 'Z', $datestr/*strtotime( '2019-10-27 01:01')*/ ) / 3600;
	$offset = $offset * 60 * 60;
	$offset = $offset - $gmt_offset;

	date_default_timezone_set( 'UTC' );

	return $offset;
}

/**
 * Converting date/time string from one timezone to another timezone and return string as per passed format
 *
 * @param string $datestring timestamp of date/time : 2017-01-01 12:00:00
 * @param string $TimeZoneNameFrom From Timezone string
 * @param string $TimeZoneNameTo To Timezone string
 * @param string $format format
 *
 * @return string return date as per converted timezone.
 *
 * @since 4.15.0
 */

function bkap_convert_date_from_to_timezone( $datestring, $TimeZoneNameFrom, $TimeZoneNameTo, $format = 'Y-m-d h:i A' ) {
	// $TimeStr            ="2017-01-01 12:00:00";
	return date_create( $datestring, new DateTimeZone( $TimeZoneNameFrom ) )->setTimezone( new DateTimeZone( $TimeZoneNameTo ) )->format( $format );
}

/**
 * This function will convert date from site timezone to UTC and return date in Y-m-d\TH:i:s\Z format.
 *
 * @param string $date_string Date.
 * @param string $timezone_string Timezone String.
 *
 * @since 4.19.2
 *
 * @return string Formated date based on UTC.
 */
function bkap_get_date_as_per_utc_timezone( $date_string, $timezone_string = '' ) {

	// https://stackoverflow.com/questions/32139407/php-convert-local-time-to-utc/32139499
	$tz_from = $timezone_string;
	$tz_to   = 'UTC';
	$format  = 'Y-m-d\TH:i:s\Z';

	$dt = new DateTime( $date_string, new DateTimeZone( $tz_from ) );
	$dt->setTimeZone( new DateTimeZone( $tz_to ) );
	$dateformat = $dt->format( $format );

	return $dateformat;
}

/**
 * This function will convert date/time from one timezone to another timezone.
 *
 * @param string $date Date.
 * @param string $from_timezone From Timezone.
 * @param string $to_timezone To Timezone.
 * @param string $format Date/Time Format.
 *
 * @since 5.8.2
 *
 * @return string Formated date based timezone.
 */
function bkap_convert_date_from_timezone_to_timezone( $date, $from_timezone, $to_timezone, $format ) {

	// Trim inputs to remove whitespaces which may affect time zone conversion.
	$from_timezone = trim( $from_timezone );
	$to_timezone   = trim( $to_timezone );

	if ( isset( $from_timezone ) && '' !== $from_timezone && isset( $to_timezone ) && '' !== $to_timezone ) {

		$from_tz_obj    = new DateTimeZone( $from_timezone );
		$to_tz_obj      = new DateTimeZone( $to_timezone );
		$converted_time = new DateTime( $date, $from_tz_obj );
		$converted_time->setTimezone( $to_tz_obj );

		return $converted_time->format( $format );
	} else {
		return get_gmt_from_date( gmdate( 'Y-m-d H:i:s', strtotime( $date ) ), $format );
	}
}

/**
 * This function is to get the price of first fixed block created under block pricing.
 *
 * @param string $product_id Product ID
 * @param string $price Price of the product
 * @param string $booking_settings Product's Booking Setting
 *
 * @return string $price Price of the product
 */

function bkap_get_fixed_block_price( $product_id, $price, $booking_settings = array() ) {

	if ( empty( $booking_settings ) ) {
		$booking_settings = bkap_setting( $product_id );
	}

	if ( isset( $booking_settings['booking_fixed_block_enable'] )
		&& $booking_settings['booking_fixed_block_enable'] == 'booking_fixed_block_enable'
		&& $booking_settings['bkap_fixed_blocks_data']
		&& ! empty( $booking_settings['bkap_fixed_blocks_data'] )
	) {

		$fixed_block = $booking_settings['bkap_fixed_blocks_data'];

		foreach ( $fixed_block as $key => $value ) {
			$price = $value['price'];
			break;
		}
	}

	return $price;
}

/**
 * Returns an array with mapped Country codes with ISD codes
 *
 * @return array Mapped Array
 *
 * @since 4.17.0
 */
function bkap_country_code_map() {

	return array(
		'IL' => array(
			'name'      => 'Israel',
			'dial_code' => '+972',
		),
		'AF' => array(
			'name'      => 'Afghanistan',
			'dial_code' => '+93',
		),
		'AL' => array(
			'name'      => 'Albania',
			'dial_code' => '+355',
		),
		'DZ' => array(
			'name'      => 'Algeria',
			'dial_code' => '+213',
		),
		'AS' => array(
			'name'      => 'AmericanSamoa',
			'dial_code' => '+1684',
		),
		'AD' => array(
			'name'      => 'Andorra',
			'dial_code' => '+376',
		),
		'AO' => array(
			'name'      => 'Angola',
			'dial_code' => '+244',
		),
		'AI' => array(
			'name'      => 'Anguilla',
			'dial_code' => '+1264',
		),
		'AG' => array(
			'name'      => 'Antigua and Barbuda',
			'dial_code' => '+1268',
		),
		'AR' => array(
			'name'      => 'Argentina',
			'dial_code' => '+54',
		),
		'AM' => array(
			'name'      => 'Armenia',
			'dial_code' => '+374',
		),
		'AW' => array(
			'name'      => 'Aruba',
			'dial_code' => '+297',
		),
		'AU' => array(
			'name'      => 'Australia',
			'dial_code' => '+61',
		),
		'AT' => array(
			'name'      => 'Austria',
			'dial_code' => '+43',
		),
		'AZ' => array(
			'name'      => 'Azerbaijan',
			'dial_code' => '+994',
		),
		'BS' => array(
			'name'      => 'Bahamas',
			'dial_code' => '+1 242',
		),
		'BH' => array(
			'name'      => 'Bahrain',
			'dial_code' => '+973',
		),
		'BD' => array(
			'name'      => 'Bangladesh',
			'dial_code' => '+880',
		),
		'BB' => array(
			'name'      => 'Barbados',
			'dial_code' => '+1 246',
		),
		'BY' => array(
			'name'      => 'Belarus',
			'dial_code' => '+375',
		),
		'BE' => array(
			'name'      => 'Belgium',
			'dial_code' => '+32',
		),
		'BZ' => array(
			'name'      => 'Belize',
			'dial_code' => '+501',
		),
		'BJ' => array(
			'name'      => 'Benin',
			'dial_code' => '+229',
		),
		'BM' => array(
			'name'      => 'Bermuda',
			'dial_code' => '+1 441',
		),
		'BT' => array(
			'name'      => 'Bhutan',
			'dial_code' => '+975',
		),
		'BA' => array(
			'name'      => 'Bosnia and Herzegovina',
			'dial_code' => '+387',
		),
		'BW' => array(
			'name'      => 'Botswana',
			'dial_code' => '+267',
		),
		'BR' => array(
			'name'      => 'Brazil',
			'dial_code' => '+55',
		),
		'IO' => array(
			'name'      => 'British Indian Ocean Territory',
			'dial_code' => '+246',
		),
		'BG' => array(
			'name'      => 'Bulgaria',
			'dial_code' => '+359',
		),
		'BF' => array(
			'name'      => 'Burkina Faso',
			'dial_code' => '+226',
		),
		'BI' => array(
			'name'      => 'Burundi',
			'dial_code' => '+257',
		),
		'KH' => array(
			'name'      => 'Cambodia',
			'dial_code' => '+855',
		),
		'CM' => array(
			'name'      => 'Cameroon',
			'dial_code' => '+237',
		),
		'CA' => array(
			'name'      => 'Canada',
			'dial_code' => '+1',
		),
		'CV' => array(
			'name'      => 'Cape Verde',
			'dial_code' => '+238',
		),
		'KY' => array(
			'name'      => 'Cayman Islands',
			'dial_code' => '+ 345',
		),
		'CF' => array(
			'name'      => 'Central African Republic',
			'dial_code' => '+236',
		),
		'TD' => array(
			'name'      => 'Chad',
			'dial_code' => '+235',
		),
		'CL' => array(
			'name'      => 'Chile',
			'dial_code' => '+56',
		),
		'CN' => array(
			'name'      => 'China',
			'dial_code' => '+86',
		),
		'CX' => array(
			'name'      => 'Christmas Island',
			'dial_code' => '+61',
		),
		'CO' => array(
			'name'      => 'Colombia',
			'dial_code' => '+57',
		),
		'KM' => array(
			'name'      => 'Comoros',
			'dial_code' => '+269',
		),
		'CG' => array(
			'name'      => 'Congo',
			'dial_code' => '+242',
		),
		'CK' => array(
			'name'      => 'Cook Islands',
			'dial_code' => '+682',
		),
		'CR' => array(
			'name'      => 'Costa Rica',
			'dial_code' => '+506',
		),
		'HR' => array(
			'name'      => 'Croatia',
			'dial_code' => '+385',
		),
		'CU' => array(
			'name'      => 'Cuba',
			'dial_code' => '+53',
		),
		'CY' => array(
			'name'      => 'Cyprus',
			'dial_code' => '+537',
		),
		'CZ' => array(
			'name'      => 'Czech Republic',
			'dial_code' => '+420',
		),
		'DK' => array(
			'name'      => 'Denmark',
			'dial_code' => '+45',
		),
		'DJ' => array(
			'name'      => 'Djibouti',
			'dial_code' => '+253',
		),
		'DM' => array(
			'name'      => 'Dominica',
			'dial_code' => '+1 767',
		),
		'DO' => array(
			'name'      => 'Dominican Republic',
			'dial_code' => '+1849',
		),
		'EC' => array(
			'name'      => 'Ecuador',
			'dial_code' => '+593',
		),
		'EG' => array(
			'name'      => 'Egypt',
			'dial_code' => '+20',
		),
		'SV' => array(
			'name'      => 'El Salvador',
			'dial_code' => '+503',
		),
		'GQ' => array(
			'name'      => 'Equatorial Guinea',
			'dial_code' => '+240',
		),
		'ER' => array(
			'name'      => 'Eritrea',
			'dial_code' => '+291',
		),
		'EE' => array(
			'name'      => 'Estonia',
			'dial_code' => '+372',
		),
		'ET' => array(
			'name'      => 'Ethiopia',
			'dial_code' => '+251',
		),
		'FO' => array(
			'name'      => 'Faroe Islands',
			'dial_code' => '+298',
		),
		'FJ' => array(
			'name'      => 'Fiji',
			'dial_code' => '+679',
		),
		'FI' => array(
			'name'      => 'Finland',
			'dial_code' => '+358',
		),
		'FR' => array(
			'name'      => 'France',
			'dial_code' => '+33',
		),
		'GF' => array(
			'name'      => 'French Guiana',
			'dial_code' => '+594',
		),
		'PF' => array(
			'name'      => 'French Polynesia',
			'dial_code' => '+689',
		),
		'GA' => array(
			'name'      => 'Gabon',
			'dial_code' => '+241',
		),
		'GM' => array(
			'name'      => 'Gambia',
			'dial_code' => '+220',
		),
		'GE' => array(
			'name'      => 'Georgia',
			'dial_code' => '+995',
		),
		'DE' => array(
			'name'      => 'Germany',
			'dial_code' => '+49',
		),
		'GH' => array(
			'name'      => 'Ghana',
			'dial_code' => '+233',
		),
		'GI' => array(
			'name'      => 'Gibraltar',
			'dial_code' => '+350',
		),
		'GR' => array(
			'name'      => 'Greece',
			'dial_code' => '+30',
		),
		'GL' => array(
			'name'      => 'Greenland',
			'dial_code' => '+299',
		),
		'GD' => array(
			'name'      => 'Grenada',
			'dial_code' => '+1 473',
		),
		'GP' => array(
			'name'      => 'Guadeloupe',
			'dial_code' => '+590',
		),
		'GU' => array(
			'name'      => 'Guam',
			'dial_code' => '+1 671',
		),
		'GT' => array(
			'name'      => 'Guatemala',
			'dial_code' => '+502',
		),
		'GN' => array(
			'name'      => 'Guinea',
			'dial_code' => '+224',
		),
		'GW' => array(
			'name'      => 'Guinea-Bissau',
			'dial_code' => '+245',
		),
		'GY' => array(
			'name'      => 'Guyana',
			'dial_code' => '+595',
		),
		'HT' => array(
			'name'      => 'Haiti',
			'dial_code' => '+509',
		),
		'HN' => array(
			'name'      => 'Honduras',
			'dial_code' => '+504',
		),
		'HU' => array(
			'name'      => 'Hungary',
			'dial_code' => '+36',
		),
		'IS' => array(
			'name'      => 'Iceland',
			'dial_code' => '+354',
		),
		'IN' => array(
			'name'      => 'India',
			'dial_code' => '+91',
		),
		'ID' => array(
			'name'      => 'Indonesia',
			'dial_code' => '+62',
		),
		'IQ' => array(
			'name'      => 'Iraq',
			'dial_code' => '+964',
		),
		'IE' => array(
			'name'      => 'Ireland',
			'dial_code' => '+353',
		),
		'IL' => array(
			'name'      => 'Israel',
			'dial_code' => '+972',
		),
		'IT' => array(
			'name'      => 'Italy',
			'dial_code' => '+39',
		),
		'JM' => array(
			'name'      => 'Jamaica',
			'dial_code' => '+1876',
		),
		'JP' => array(
			'name'      => 'Japan',
			'dial_code' => '+81',
		),
		'JO' => array(
			'name'      => 'Jordan',
			'dial_code' => '+962',
		),
		'KZ' => array(
			'name'      => 'Kazakhstan',
			'dial_code' => '+77',
		),
		'KE' => array(
			'name'      => 'Kenya',
			'dial_code' => '+254',
		),
		'KI' => array(
			'name'      => 'Kiribati',
			'dial_code' => '+686',
		),
		'KW' => array(
			'name'      => 'Kuwait',
			'dial_code' => '+965',
		),
		'KG' => array(
			'name'      => 'Kyrgyzstan',
			'dial_code' => '+996',
		),
		'LV' => array(
			'name'      => 'Latvia',
			'dial_code' => '+371',
		),
		'LB' => array(
			'name'      => 'Lebanon',
			'dial_code' => '+961',
		),
		'LS' => array(
			'name'      => 'Lesotho',
			'dial_code' => '+266',
		),
		'LR' => array(
			'name'      => 'Liberia',
			'dial_code' => '+231',
		),
		'LI' => array(
			'name'      => 'Liechtenstein',
			'dial_code' => '+423',
		),
		'LT' => array(
			'name'      => 'Lithuania',
			'dial_code' => '+370',
		),
		'LU' => array(
			'name'      => 'Luxembourg',
			'dial_code' => '+352',
		),
		'MG' => array(
			'name'      => 'Madagascar',
			'dial_code' => '+261',
		),
		'MW' => array(
			'name'      => 'Malawi',
			'dial_code' => '+265',
		),
		'MY' => array(
			'name'      => 'Malaysia',
			'dial_code' => '+60',
		),
		'MV' => array(
			'name'      => 'Maldives',
			'dial_code' => '+960',
		),
		'ML' => array(
			'name'      => 'Mali',
			'dial_code' => '+223',
		),
		'MT' => array(
			'name'      => 'Malta',
			'dial_code' => '+356',
		),
		'MH' => array(
			'name'      => 'Marshall Islands',
			'dial_code' => '+692',
		),
		'MQ' => array(
			'name'      => 'Martinique',
			'dial_code' => '+596',
		),
		'MR' => array(
			'name'      => 'Mauritania',
			'dial_code' => '+222',
		),
		'MU' => array(
			'name'      => 'Mauritius',
			'dial_code' => '+230',
		),
		'YT' => array(
			'name'      => 'Mayotte',
			'dial_code' => '+262',
		),
		'MX' => array(
			'name'      => 'Mexico',
			'dial_code' => '+52',
		),
		'MC' => array(
			'name'      => 'Monaco',
			'dial_code' => '+377',
		),
		'MN' => array(
			'name'      => 'Mongolia',
			'dial_code' => '+976',
		),
		'ME' => array(
			'name'      => 'Montenegro',
			'dial_code' => '+382',
		),
		'MS' => array(
			'name'      => 'Montserrat',
			'dial_code' => '+1664',
		),
		'MA' => array(
			'name'      => 'Morocco',
			'dial_code' => '+212',
		),
		'MM' => array(
			'name'      => 'Myanmar',
			'dial_code' => '+95',
		),
		'NA' => array(
			'name'      => 'Namibia',
			'dial_code' => '+264',
		),
		'NR' => array(
			'name'      => 'Nauru',
			'dial_code' => '+674',
		),
		'NP' => array(
			'name'      => 'Nepal',
			'dial_code' => '+977',
		),
		'NL' => array(
			'name'      => 'Netherlands',
			'dial_code' => '+31',
		),
		'AN' => array(
			'name'      => 'Netherlands Antilles',
			'dial_code' => '+599',
		),
		'NC' => array(
			'name'      => 'New Caledonia',
			'dial_code' => '+687',
		),
		'NZ' => array(
			'name'      => 'New Zealand',
			'dial_code' => '+64',
		),
		'NI' => array(
			'name'      => 'Nicaragua',
			'dial_code' => '+505',
		),
		'NE' => array(
			'name'      => 'Niger',
			'dial_code' => '+227',
		),
		'NG' => array(
			'name'      => 'Nigeria',
			'dial_code' => '+234',
		),
		'NU' => array(
			'name'      => 'Niue',
			'dial_code' => '+683',
		),
		'NF' => array(
			'name'      => 'Norfolk Island',
			'dial_code' => '+672',
		),
		'MP' => array(
			'name'      => 'Northern Mariana Islands',
			'dial_code' => '+1670',
		),
		'NO' => array(
			'name'      => 'Norway',
			'dial_code' => '+47',
		),
		'OM' => array(
			'name'      => 'Oman',
			'dial_code' => '+968',
		),
		'PK' => array(
			'name'      => 'Pakistan',
			'dial_code' => '+92',
		),
		'PW' => array(
			'name'      => 'Palau',
			'dial_code' => '+680',
		),
		'PA' => array(
			'name'      => 'Panama',
			'dial_code' => '+507',
		),
		'PG' => array(
			'name'      => 'Papua New Guinea',
			'dial_code' => '+675',
		),
		'PY' => array(
			'name'      => 'Paraguay',
			'dial_code' => '+595',
		),
		'PE' => array(
			'name'      => 'Peru',
			'dial_code' => '+51',
		),
		'PH' => array(
			'name'      => 'Philippines',
			'dial_code' => '+63',
		),
		'PL' => array(
			'name'      => 'Poland',
			'dial_code' => '+48',
		),
		'PT' => array(
			'name'      => 'Portugal',
			'dial_code' => '+351',
		),
		'PR' => array(
			'name'      => 'Puerto Rico',
			'dial_code' => '+1939',
		),
		'QA' => array(
			'name'      => 'Qatar',
			'dial_code' => '+974',
		),
		'RO' => array(
			'name'      => 'Romania',
			'dial_code' => '+40',
		),
		'RW' => array(
			'name'      => 'Rwanda',
			'dial_code' => '+250',
		),
		'WS' => array(
			'name'      => 'Samoa',
			'dial_code' => '+685',
		),
		'SM' => array(
			'name'      => 'San Marino',
			'dial_code' => '+378',
		),
		'SA' => array(
			'name'      => 'Saudi Arabia',
			'dial_code' => '+966',
		),
		'SN' => array(
			'name'      => 'Senegal',
			'dial_code' => '+221',
		),
		'RS' => array(
			'name'      => 'Serbia',
			'dial_code' => '+381',
		),
		'SC' => array(
			'name'      => 'Seychelles',
			'dial_code' => '+248',
		),
		'SL' => array(
			'name'      => 'Sierra Leone',
			'dial_code' => '+232',
		),
		'SG' => array(
			'name'      => 'Singapore',
			'dial_code' => '+65',
		),
		'SK' => array(
			'name'      => 'Slovakia',
			'dial_code' => '+421',
		),
		'SI' => array(
			'name'      => 'Slovenia',
			'dial_code' => '+386',
		),
		'SB' => array(
			'name'      => 'Solomon Islands',
			'dial_code' => '+677',
		),
		'ZA' => array(
			'name'      => 'South Africa',
			'dial_code' => '+27',
		),
		'GS' => array(
			'name'      => 'South Georgia and the South Sandwich Islands',
			'dial_code' => '+500',
		),
		'ES' => array(
			'name'      => 'Spain',
			'dial_code' => '+34',
		),
		'LK' => array(
			'name'      => 'Sri Lanka',
			'dial_code' => '+94',
		),
		'SD' => array(
			'name'      => 'Sudan',
			'dial_code' => '+249',
		),
		'SR' => array(
			'name'      => 'Suriname',
			'dial_code' => '+597',
		),
		'SZ' => array(
			'name'      => 'Swaziland',
			'dial_code' => '+268',
		),
		'SE' => array(
			'name'      => 'Sweden',
			'dial_code' => '+46',
		),
		'CH' => array(
			'name'      => 'Switzerland',
			'dial_code' => '+41',
		),
		'TJ' => array(
			'name'      => 'Tajikistan',
			'dial_code' => '+992',
		),
		'TH' => array(
			'name'      => 'Thailand',
			'dial_code' => '+66',
		),
		'TG' => array(
			'name'      => 'Togo',
			'dial_code' => '+228',
		),
		'TK' => array(
			'name'      => 'Tokelau',
			'dial_code' => '+690',
		),
		'TO' => array(
			'name'      => 'Tonga',
			'dial_code' => '+676',
		),
		'TT' => array(
			'name'      => 'Trinidad and Tobago',
			'dial_code' => '+1868',
		),
		'TN' => array(
			'name'      => 'Tunisia',
			'dial_code' => '+216',
		),
		'TR' => array(
			'name'      => 'Turkey',
			'dial_code' => '+90',
		),
		'TM' => array(
			'name'      => 'Turkmenistan',
			'dial_code' => '+993',
		),
		'TC' => array(
			'name'      => 'Turks and Caicos Islands',
			'dial_code' => '+1649',
		),
		'TV' => array(
			'name'      => 'Tuvalu',
			'dial_code' => '+688',
		),
		'UG' => array(
			'name'      => 'Uganda',
			'dial_code' => '+256',
		),
		'UA' => array(
			'name'      => 'Ukraine',
			'dial_code' => '+380',
		),
		'AE' => array(
			'name'      => 'United Arab Emirates',
			'dial_code' => '+971',
		),
		'GB' => array(
			'name'      => 'United Kingdom',
			'dial_code' => '+44',
		),
		'US' => array(
			'name'      => 'United States',
			'dial_code' => '+1',
		),
		'UY' => array(
			'name'      => 'Uruguay',
			'dial_code' => '+598',
		),
		'UZ' => array(
			'name'      => 'Uzbekistan',
			'dial_code' => '+998',
		),
		'VU' => array(
			'name'      => 'Vanuatu',
			'dial_code' => '+678',
		),
		'WF' => array(
			'name'      => 'Wallis and Futuna',
			'dial_code' => '+681',
		),
		'YE' => array(
			'name'      => 'Yemen',
			'dial_code' => '+967',
		),
		'ZM' => array(
			'name'      => 'Zambia',
			'dial_code' => '+260',
		),
		'ZW' => array(
			'name'      => 'Zimbabwe',
			'dial_code' => '+263',
		),
		'BO' => array(
			'name'      => 'Bolivia, Plurinational State of',
			'dial_code' => '+591',
		),
		'BN' => array(
			'name'      => 'Brunei Darussalam',
			'dial_code' => '+673',
		),
		'CC' => array(
			'name'      => 'Cocos (Keeling) Islands',
			'dial_code' => '+61',
		),
		'CD' => array(
			'name'      => 'Congo, The Democratic Republic of the',
			'dial_code' => '+243',
		),
		'CI' => array(
			'name'      => 'Cote dIvoire',
			'dial_code' => '+225',
		),
		'FK' => array(
			'name'      => 'Falkland Islands (Malvinas)',
			'dial_code' => '+500',
		),
		'GG' => array(
			'name'      => 'Guernsey',
			'dial_code' => '+44',
		),
		'VA' => array(
			'name'      => 'Holy See (Vatican City State)',
			'dial_code' => '+379',
		),
		'HK' => array(
			'name'      => 'Hong Kong',
			'dial_code' => '+852',
		),
		'IR' => array(
			'name'      => 'Iran, Islamic Republic of',
			'dial_code' => '+98',
		),
		'IM' => array(
			'name'      => 'Isle of Man',
			'dial_code' => '+44',
		),
		'JE' => array(
			'name'      => 'Jersey',
			'dial_code' => '+44',
		),
		'KP' => array(
			'name'      => 'Korea, Democratic Peoples Republic of',
			'dial_code' => '+850',
		),
		'KR' => array(
			'name'      => 'Korea, Republic of',
			'dial_code' => '+82',
		),
		'LA' => array(
			'name'      => 'Lao Peoples Democratic Republic',
			'dial_code' => '+856',
		),
		'LY' => array(
			'name'      => 'Libyan Arab Jamahiriya',
			'dial_code' => '+218',
		),
		'MO' => array(
			'name'      => 'Macao',
			'dial_code' => '+853',
		),
		'MK' => array(
			'name'      => 'Macedonia, The Former Yugoslav Republic of',
			'dial_code' => '+389',
		),
		'FM' => array(
			'name'      => 'Micronesia, Federated States of',
			'dial_code' => '+691',
		),
		'MD' => array(
			'name'      => 'Moldova, Republic of',
			'dial_code' => '+373',
		),
		'MZ' => array(
			'name'      => 'Mozambique',
			'dial_code' => '+258',
		),
		'PS' => array(
			'name'      => 'Palestinian Territory, Occupied',
			'dial_code' => '+970',
		),
		'PN' => array(
			'name'      => 'Pitcairn',
			'dial_code' => '+872',
		),
		'RE' => array(
			'name'      => 'Réunion',
			'dial_code' => '+262',
		),
		'RU' => array(
			'name'      => 'Russia',
			'dial_code' => '+7',
		),
		'BL' => array(
			'name'      => 'Saint Barthélemy',
			'dial_code' => '+590',
		),
		'SH' => array(
			'name'      => 'Saint Helena, Ascension and Tristan Da Cunha',
			'dial_code' => '+290',
		),
		'KN' => array(
			'name'      => 'Saint Kitts and Nevis',
			'dial_code' => '+1 869',
		),
		'LC' => array(
			'name'      => 'Saint Lucia',
			'dial_code' => '+1758',
		),
		'MF' => array(
			'name'      => 'Saint Martin',
			'dial_code' => '+590',
		),
		'PM' => array(
			'name'      => 'Saint Pierre and Miquelon',
			'dial_code' => '+508',
		),
		'VC' => array(
			'name'      => 'Saint Vincent and the Grenadines',
			'dial_code' => '+1784',
		),
		'ST' => array(
			'name'      => 'Sao Tome and Principe',
			'dial_code' => '+239',
		),
		'SO' => array(
			'name'      => 'Somalia',
			'dial_code' => '+252',
		),
		'SJ' => array(
			'name'      => 'Svalbard and Jan Mayen',
			'dial_code' => '+47',
		),
		'SY' => array(
			'name'      => 'Syrian Arab Republic',
			'dial_code' => '+963',
		),
		'TW' => array(
			'name'      => 'Taiwan, Province of China',
			'dial_code' => '+886',
		),
		'TZ' => array(
			'name'      => 'Tanzania, United Republic of',
			'dial_code' => '+255',
		),
		'TL' => array(
			'name'      => 'Timor-Leste',
			'dial_code' => '+670',
		),
		'VE' => array(
			'name'      => 'Venezuela, Bolivarian Republic of',
			'dial_code' => '+58',
		),
		'VN' => array(
			'name'      => 'Viet Nam',
			'dial_code' => '+84',
		),
		'VG' => array(
			'name'      => 'Virgin Islands, British',
			'dial_code' => '+1284',
		),
		'VI' => array(
			'name'      => 'Virgin Islands, U.S.',
			'dial_code' => '+1340',
		),
	);
}

/**
 * This function will return the SMS settings
 *
 * @since 5.17.0
 */

function bkap_get_sms_settings() {
	$sms_settings = get_option( 'bkap_sms_settings' );
	$send_sms     = false;

	if ( isset( $sms_settings['enable_sms'] ) && 'on' == $sms_settings['enable_sms'] ) {

		$send_sms   = true;
		$from       = '';
		$acc_id     = '';
		$auth_token = '';
		$body       = '';

		if ( isset( $sms_settings['from'] ) && '' !== $sms_settings['from'] ) {
			$from = $sms_settings['from'];
		}
		if ( isset( $sms_settings['account_sid'] ) && '' !== $sms_settings['account_sid'] ) {
			$acc_id = $sms_settings['account_sid'];
		}
		if ( isset( $sms_settings['auth_token'] ) && '' !== $sms_settings['auth_token'] ) {
			$auth_token = $sms_settings['auth_token'];
		}

		if ( isset( $sms_settings['body'] ) && '' !== $sms_settings['body'] ) {
			$body = $sms_settings['body'];
		}

		if ( '' === $from || '' === $acc_id || '' === $auth_token ) {
			return false;
		} else {
			$twilio_details = array(
				'sid'   => $acc_id,
				'token' => $auth_token,
				'from'  => $from,
				'body'  => $body,
			);
			return $twilio_details;
		}
	}

	return false;
}

/**
 * This function will return the Merge Codes.
 *
 * @since 5.14.0
 */
function bkap_reminder_merge_codes() {

	$merge_codes = array(
		'{start_date}'          => __( 'Use this merge code to replace the Start Date infomration of the Booking.', 'woocommerce-booking' ),
		'{end_date}'            => __( 'Use this merge code to replace the End Date infomration of the Booking.', 'woocommerce-booking' ),
		'{booking_time}'        => __( 'Use this merge code to replace the Time infomration of the Booking.', 'woocommerce-booking' ),
		'{booking_id}'          => __( 'Use this merge code to replace the Booking ID infomration of the Booking.', 'woocommerce-booking' ),
		'{booking_resource}'    => __( 'Use this merge code to replace the Resource infomration of the Booking.', 'woocommerce-booking' ),
		'{booking_persons}'     => __( 'Use this merge code to replace the Persons infomration of the Booking.', 'woocommerce-booking' ),
		'{zoom_link}'           => __( 'Use this merge code to replace the Zoom Link infomration of the Booking.', 'woocommerce-booking' ),
		'{product_title}'       => __( 'Use this merge code to replace the Product Title of the Booking.', 'woocommerce-booking' ),
		'{order_number}'        => __( 'Use this merge code to replace the Order Number infomration of the Booking.', 'woocommerce-booking' ),
		'{order_date}'          => __( 'Use this merge code to replace the Order Date infomration of the Booking.', 'woocommerce-booking' ),
		'{customer_name}'       => __( 'Use this merge code to replace the Customer Name infomration of the Booking.', 'woocommerce-booking' ),
		'{customer_first_name}' => __( 'Use this merge code to replace the Customer First Name infomration of the Booking.', 'woocommerce-booking' ),
		'{customer_last_name}'  => __( 'Use this merge code to replace the Customer Last Name infomration of the Booking.', 'woocommerce-booking' ),
		'{booking_table}'       => __( 'Use this merge code to show the all booking infomration in the table.', 'woocommerce-booking' ),
	);

	return $merge_codes;
}

/**
 * Save Reminder meta upon save/update Reminder.
 *
 * @since 5.14.0
 */
function bkap_reminder_save_data( $post_id ) {

	if ( isset( $_POST['bkap_email_subject'] ) ) {

		/* Email Reminder Settings */

		if ( isset( $_POST['bkap_email_content'] ) ) {
			$bkap_email_content = wp_filter_post_kses( $_POST['bkap_email_content'] ); // phpcs:ignore
			update_post_meta( $post_id, 'bkap_email_content', $bkap_email_content );
		}

		if ( isset( $_POST['bkap_email_subject'] ) ) {
			$email_subject = sanitize_text_field( $_POST['bkap_email_subject'] ); // phpcs:ignore
			update_post_meta( $post_id, 'bkap_email_subject', $email_subject );
		}

		if ( isset( $_POST['bkap_email_heading'] ) ) {
			$bkap_email_heading = sanitize_text_field( $_POST['bkap_email_heading'] ); // phpcs:ignore
			update_post_meta( $post_id, 'bkap_email_heading', $bkap_email_heading );
		}

		/* Trigger Settings */

		if ( isset( $_POST['bkap_sending_delay'] ) ) {
			$bkap_sending_delay = sanitize_text_field( wp_unslash( $_POST['bkap_sending_delay'] ) );
			update_post_meta( $post_id, 'bkap_sending_delay', $bkap_sending_delay );
			update_post_meta( $post_id, 'bkap_delay_value', $bkap_sending_delay['delay_value'] );
			update_post_meta( $post_id, 'bkap_delay_unit', $bkap_sending_delay['delay_unit'] );
		}

		if ( isset( $_POST['bkap_trigger'] ) ) {
			$bkap_trigger = sanitize_text_field( wp_unslash( $_POST['bkap_trigger'] ) );
			update_post_meta( $post_id, 'bkap_trigger', $bkap_trigger );
		}

		if ( isset( $_POST['bkap_products'] ) ) {
			$bkap_products = $_POST['bkap_products']; // phpcs:ignore
			update_post_meta( $post_id, 'bkap_products', $bkap_products );
		}

		if ( isset( $_POST['bkap_enable_sms'] ) ) {
			$bkap_enable_sms = sanitize_text_field( wp_unslash( $_POST['bkap_enable_sms'] ) );
			update_post_meta( $post_id, 'bkap_enable_sms', $bkap_enable_sms );
		} else {
			update_post_meta( $post_id, 'bkap_enable_sms', '' );
		}

		if ( isset( $_POST['bkap_sms_body'] ) ) {
			$bkap_products = wp_filter_post_kses( $_POST['bkap_sms_body'] ); // phpcs:ignore
			update_post_meta( $post_id, 'bkap_sms_body', $bkap_products );
		}

		if ( ! as_next_scheduled_action( 'bkap_auto_reminder_emails' ) ) {
			as_schedule_recurring_action( time(), HOUR_IN_SECONDS, 'bkap_auto_reminder_emails' );
		}

		do_action( 'bkap_reminder_save_data', $post_id );
	}
}

/**
 * This function will return max booking set for specific date.
 *
 * @since 5.18.0
 */

function bkap_get_specific_date_maximum_booking( $available_tickets, $selected_date, $product_id, $booking_settings ) {
	if ( isset( $booking_settings['booking_specific_booking'] ) && 'on' == $booking_settings['booking_specific_booking'] ) {
		if ( isset( $booking_settings['booking_specific_date'] ) && count( $booking_settings['booking_specific_date'] ) > 0 ) {
			$specific_dates = $booking_settings['booking_specific_date'];
			if ( isset( $specific_dates[ $selected_date ] ) ) {
				$available_tickets = $specific_dates[ $selected_date ];
			}
		}
	}

	return $available_tickets;
}

/**
 * This function will include select2 scripts
 *
 * @since 4.19.2
 */
function bkap_include_select2_scripts() {

	wp_enqueue_style(
		'bkap-woocommerce_admin_styles',
		plugins_url() . '/woocommerce/assets/css/admin.css',
		'',
		BKAP_VERSION,
		false
	);

	wp_register_script(
		'select2',
		plugins_url() . '/woocommerce/assets/js/select2/select2.min.js',
		array( 'jquery', 'jquery-ui-widget', 'jquery-ui-core' ),
		BKAP_VERSION,
		false
	);

	wp_enqueue_script( 'select2' );
}

/**
 * This function return true if overlapping timeslots booking is enabled
 *
 * @since 4.19.2
 */
function bkap_booking_overlapping_timeslot( $global_settings, $product_id = 0 ) {

	$overlapping = isset( $global_settings->booking_overlapping_timeslot ) && 'on' === $global_settings->booking_overlapping_timeslot ? true : false;

	return apply_filters( 'bkap_booking_overlapping_timeslot', $overlapping, $product_id );
}

/**
 * This function return min and max date based on custom range set for the product.
 *
 * @since 5.0.0
 */
function bkap_minmax_date_custom_range( $product_id, $ranges ) {

	$current_time = current_time( 'timestamp' ); // WordPress Time.

	// default the fields
	$min_date = '';
	$days     = '';

	$active_dates = array();
	$loop_count   = count( $ranges );

	for ( $i = 0; $i < $loop_count; $i++ ) {

		$key   = '';
		$first = true;

		foreach ( $ranges as $range_key => $range_data ) {

			if ( $first ) {
				$min_start = $range_data['start'];
				$min_end   = $range_data['end'];
				$key       = $range_key;
				$first     = false;
			}

			$new_start = strtotime( $range_data['start'] );

			if ( $new_start < strtotime( $min_start ) ) {
				$min_start = $range_data['start'];
				$min_end   = $range_data['end'];
				$key       = $range_key;
			}
		}

		$active_dates[] = array(
			'start' => $min_start,
			'end'   => $min_end,
		); // add the minimum data to the new array

		unset( $ranges[ $key ] ); // remove the minimum start & end record
	}

	// now get the first start date i.e. the min date
	foreach ( $active_dates as $dates ) {
		// very first active range
		$start = $dates['start'];

		// if it is a past date, check the end date to see if the entire range is past
		if ( strtotime( $start ) < $current_time ) {
			$end = $dates['end'];

			if ( strtotime( $end ) < $current_time ) {
				continue; // range is past, so check the next record
			} else { // few days left in the range
				$min_date = bkap_common::bkap_min_date_based_on_AdvanceBookingPeriod( $product_id, $current_time );  // so min date is today
				break;
			}
		} else { // this is a future date
			$min_date = bkap_common::bkap_min_date_based_on_AdvanceBookingPeriod( $product_id, $current_time );
			if ( strtotime( $start ) >= strtotime( $min_date ) ) {
				$min_date = $dates['start'];
			}
			break;
		}
	}

	$all_customrange_dates = array();
	foreach ( $active_dates as $a_date ) {
		$last_dates[]          = strtotime( $a_date['end'] );
		$all_customrange_dates = array_merge( $all_customrange_dates, bkap_common::bkap_get_betweendays_when_flat( $a_date['start'], $a_date['end'], $product_id, $format = 'j-n-Y' ) );
	}
	$last_date = gmdate( 'Y-m-d', max( $last_dates ) );

	// set the max date
	$active_dates_count  = count( $active_dates );
	$active_dates_count -= 1;
	$days                = $active_dates[ $active_dates_count ]['end'];

	// if min date is blanks, happens when all ranges are in the past
	if ( $min_date === '' ) {
		$min_date = $active_dates[ $active_dates_count ]['end'];
	}

	return array( $min_date, $last_date, $all_customrange_dates );
}

/**
 * This function return Currency Arguments require to format the price.
 * Similar function available get_currency_args() but not useful in js.
 * Verify both function after the release and keep only one.
 *
 * @since 5.0.1
 */
function wc_currency_arguments() {

	$args = array(
		'currency_format_num_decimals' => wc_get_price_decimals(),
		'currency_format_symbol'       => get_woocommerce_currency_symbol(),
		'currency_format_decimal_sep'  => esc_attr( wc_get_price_decimal_separator() ),
		'currency_format_thousand_sep' => esc_attr( wc_get_price_thousand_separator() ),
		'currency_format'              => esc_attr( str_replace( array( '%1$s', '%2$s' ), array( '%s', '%v' ), get_woocommerce_price_format() ) ), // For accounting JS.
		'rounding_precision'           => wc_get_rounding_precision(),
	);

	return $args;
}

/**
 * This function re-arranges the Booking Types into groups needed for the Booking Type drpdown.
 *
 * @since 5.6.1
 */
function bkap_get_booking_type_groups_dropdown() {

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

			// If we get here, then booking type does not have a group.
			else {
				// Ensure that important items have been set.
				if ( ! isset( $booking_type['key'] ) || ! isset( $booking_type['label'] ) ) {
					continue;
				}

				$booking_types_group['n-g'][] = $booking_type; // n-g stands for no grouping.
			}
		}
	}

	return $booking_types_group;
}

/**
 * This function returns the list of Booking Types with their respective proerties in an array.
 *
 * @since 5.6.1
 */
function bkap_get_booking_types() {

	$group_only_days   = 'Only Days';
	$group_date_time   = 'Date & Time';
	$group_multi_dates = 'Multiple Dates';

	$booking_types = array(
		'only_day'             => array(
			'key'   => 'only_day',
			'label' => __( 'Single Day', 'woocommerce-booking' ),
			'group' => $group_only_days,
		),
		'multiple_days'        => array(
			'key'   => 'multiple_days',
			'label' => __( 'Multiple Nights', 'woocommerce-booking' ),
			'group' => $group_only_days,
		),
		'date_time'            => array(
			'key'   => 'date_time',
			'label' => __( 'Fixed Time', 'woocommerce-booking' ),
			'group' => $group_date_time,
		),
		'duration_time'        => array(
			'key'   => 'duration_time',
			'label' => __( 'Duration Based Time', 'woocommerce-booking' ),
			'group' => $group_date_time,
		),
		'multidates'           => array(
			'key'   => 'multidates',
			'label' => __( 'Dates', 'woocommerce-booking' ),
			'group' => $group_multi_dates,
		),
		'multidates_fixedtime' => array(
			'key'   => 'multidates_fixedtime',
			'label' => __( 'Dates & Fixed Time', 'woocommerce-booking' ),
			'group' => $group_multi_dates,
		),
	);

	return apply_filters( 'bkap_get_booking_types', $booking_types );
}

/**
 * This function will update the appropriate item meta when multiple dates booking is updated.
 *
 * @param int    $item_id Item ID.
 * @param string $label Label of Meta Key of Item.
 * @param string $new_value Label of Meta Key of Item.
 * @param string $old_value Label of Meta Key of Item.
 * @param int    $key Number occurance of item when multiple same meta key.
 *
 * @since 5.0.0
 */
function bkap_update_order_itemmeta_multidates( $item_id, $label, $new_value, $old_value, $key ) {
	global $wpdb;
	$item_meta_query = 'SELECT * FROM `' . $wpdb->prefix . 'woocommerce_order_itemmeta` WHERE order_item_id = %d AND meta_key = %s';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
	$results_booking = $wpdb->get_results( $wpdb->prepare( $item_meta_query, $item_id, $label ), ARRAY_A );

	if ( isset( $results_booking[ $key ] ) ) {
		$meta          = $results_booking[ $key ];
		$meta_id       = $meta['meta_id'];
		$table         = $wpdb->prefix . 'woocommerce_order_itemmeta';
		$rows_affected = $wpdb->query(
			$wpdb->prepare(
				"UPDATE {$table} SET meta_value = %s WHERE meta_id = %d AND meta_key = %s AND meta_value = %s",
				$new_value,
				$meta_id,
				$label,
				$old_value
			)
		);
		$item          = new WC_Order_Item_Product( $item_id );
		$item->save();
	}
}

/**
 * This function return the booking field labels.
 *
 * @since 5.5.2
 */
function bkap_booking_fields_label() {

	$book_item_meta_date     = ( '' === get_option( 'book_item-meta-date' ) ) ? __( 'Start Date', 'woocommerce-booking' ) : get_option( 'book_item-meta-date' );
	$checkout_item_meta_date = ( '' === get_option( 'checkout_item-meta-date' ) ) ? __( 'End Date', 'woocommerce-booking' ) : get_option( 'checkout_item-meta-date' );
	$book_item_meta_time     = ( '' === get_option( 'book_item-meta-time' ) ) ? __( 'Booking Time', 'woocommerce-booking' ) : get_option( 'book_item-meta-time' );
	$booking_labels          = array(
		'start_date' => $book_item_meta_date,
		'end_date'   => $checkout_item_meta_date,
		'time_slot'  => $book_item_meta_time,
	);

	return $booking_labels;
}

/**
 * This function will prepare the Event Details array upon the order place.
 *
 * @param int   $post_id Product ID.
 * @param array $booking_settings Booking Settings.
 * @param int   $order_id Order ID.
 * @param int   $item_id Item ID.
 * @param int   $variation_id Variation ID.
 * @param array $booking_data Booking Data.
 * @param int   $quantity Quantity.
 *
 * @since 5.6.1
 */
function bkap_event_details_upon_order_placed( $post_id, $booking_settings, $order_id, $item_id, $variation_id, $booking_data, $quantity ) {

	$item       = new WC_Order_Item_Product( $item_id );
	$post_title = $item->get_name();

	if ( 0 < $variation_id ) {
		$variation_obj      = new WC_Product_Variation( $variation_id );
		$variation_attr_cnt = count( $variation_obj->get_variation_attributes() );
		if ( 2 < $variation_attr_cnt ) {
			$product_variations = implode( ', ', $variation_obj->get_variation_attributes() );
			$post_title         = $post_title . ' - ' . $product_variations;
		}
	}

	$is_date_set = false;
	if ( isset( $booking_data['hidden_date'] ) ) {
		$day = gmdate( 'Y-m-d', current_time( 'timestamp' ) );
		if ( strtotime( $booking_data['hidden_date'] ) >= strtotime( $day ) ) {
			$is_date_set = true;
		}
	}

	$event_details = array();
	if ( $is_date_set ) {
		$global_settings                      = bkap_global_setting();
		$event_details['hidden_booking_date'] = $booking_data['hidden_date'];

		if ( isset( $booking_data['hidden_date_checkout'] ) && '' !== $booking_data['hidden_date_checkout'] ) {
			$event_details['hidden_checkout_date'] = $booking_data['hidden_date_checkout'];
		}

		if ( is_plugin_active( 'bkap-rental/rental.php' ) ) {
			if ( isset( $booking_settings['booking_prior_days_to_book'] ) && '' !== $booking_settings['booking_prior_days_to_book'] && '0' !== $booking_settings['booking_prior_days_to_book'] ) {
				$prior_day                            = $booking_settings['booking_prior_days_to_book'];
				$event_details['hidden_booking_date'] = gmdate( 'Y-m-d', strtotime( "-$prior_day day", strtotime( $booking_data['hidden_date'] ) ) );
			}

			if ( isset( $booking_settings['booking_charge_per_day'] ) && 'on' === $booking_settings['booking_charge_per_day'] ) {
				$event_details['hidden_checkout_date'] = gmdate( 'Y-m-d', strtotime( '+1 day', strtotime( $booking_data['hidden_date_checkout'] ) ) );
			} elseif ( isset( $booking_settings['booking_later_days_to_book'] ) && '' !== $booking_settings['booking_later_days_to_book'] && '0' !== $booking_settings['booking_later_days_to_book'] ) {
				$later_day                             = $booking_settings['booking_later_days_to_book'];
				$event_details['hidden_checkout_date'] = gmdate( 'Y-m-d', strtotime( "+$later_day day", strtotime( $booking_data['hidden_date_checkout'] ) ) );
			}
		}

		if ( isset( $booking_data['selected_duration'] ) && '' !== $booking_data['selected_duration'] ) {

			$start_date = $booking_data['hidden_date'];
			$time       = $booking_data['duration_time_slot'];

			$selected_duration = explode( '-', $booking_data['selected_duration'] );

			$hour   = $selected_duration[0];
			$d_type = $selected_duration[1];

			$end_str  = bkap_common::bkap_add_hour_to_date( $start_date, $time, $hour, $post_id, $d_type ); // return end date timestamp
			$end_date = gmdate( 'j-n-Y', $end_str ); // Date in j-n-Y format to compate and store in end date order meta

			// updating end date
			if ( $start_date != $end_date ) {
				$event_details['hidden_checkout_date'] = $end_date;
			}

			$endtime        = gmdate( 'H:i', $end_str );// getend time in H:i format
			$back_time_slot = $time . ' - ' . $endtime; // to store time sting in the _wapbk_time_slot key of order item meta

			$event_details['duration_time_slot'] = $back_time_slot;

		}

		if ( isset( $booking_data['resource_id'] ) && '' !== $booking_data['resource_id'] ) {
			$event_details['resource'] = get_the_title( $booking_data['resource_id'] );
		}

		$event_details['billing_email']      = isset( $_POST['billing_email'] ) ? sanitize_text_field( $_POST['billing_email'] ) : '';
		$event_details['billing_first_name'] = isset( $_POST['billing_first_name'] ) ? sanitize_text_field( $_POST['billing_first_name'] ) : '';
		$event_details['billing_last_name']  = isset( $_POST['billing_last_name'] ) ? sanitize_text_field( $_POST['billing_last_name'] ) : '';
		$event_details['billing_address_1']  = isset( $_POST['billing_address_1'] ) ? sanitize_text_field( $_POST['billing_address_1'] ) : '';
		$event_details['billing_address_2']  = isset( $_POST['billing_address_2'] ) ? sanitize_text_field( $_POST['billing_address_2'] ) : '';
		$event_details['billing_city']       = isset( $_POST['billing_city'] ) ? sanitize_text_field( $_POST['billing_city'] ) : '';
		$event_details['billing_country']    = isset( $_POST['billing_country'] ) ? sanitize_text_field( $_POST['billing_country'] ) : '';
		$event_details['billing_postcode']   = isset( $_POST['billing_postcode'] ) ? sanitize_text_field( $_POST['billing_postcode'] ) : '';
		$event_details['billing_phone']      = isset( $_POST['billing_phone'] ) ? sanitize_text_field( $_POST['billing_phone'] ) : '';
		$event_details['order_comments']     = isset( $_POST['order_comments'] ) ? sanitize_text_field( $_POST['order_comments'] ) : '';
		$event_details['order_id']           = $order_id;

		if ( isset( $_POST['shipping_first_name'] ) && $_POST['shipping_first_name'] != '' ) {
			$event_details['shipping_first_name'] = sanitize_text_field( $_POST['shipping_first_name'] );
		}
		if ( isset( $_POST['shipping_last_name'] ) && $_POST['shipping_last_name'] != '' ) {
			$event_details['shipping_last_name'] = sanitize_text_field( $_POST['shipping_last_name'] );
		}
		if ( isset( $_POST['shipping_address_1'] ) && $_POST['shipping_address_1'] != '' ) {
			$event_details['shipping_address_1'] = sanitize_text_field( $_POST['shipping_address_1'] );
		}
		if ( isset( $_POST['shipping_address_2'] ) && $_POST['shipping_address_2'] != '' ) {
			$event_details['shipping_address_2'] = sanitize_text_field( $_POST['shipping_address_2'] );
		}
		if ( isset( $_POST['shipping_city'] ) && $_POST['shipping_city'] != '' ) {
			$event_details['shipping_city'] = sanitize_text_field( $_POST['shipping_city'] );
		}
		if ( isset( $_POST['shipping_postcode'] ) && $_POST['shipping_postcode'] != '' ) {
			$event_details['shipping_postcode'] = sanitize_text_field( $_POST['shipping_postcode'] );
		}

		// Get the billing details from the order if $_POST is not set (especially for checkout block page).
		$order = wc_get_order( $order_id );
		if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
			return $event_details;
		}
		$event_details['billing_email']      = '' === $event_details['billing_email'] ? $order->get_billing_email() : $event_details['billing_email'];
		$event_details['billing_first_name'] = '' === $event_details['billing_first_name'] ? $order->get_billing_first_name() : $event_details['billing_first_name'];
		$event_details['billing_last_name']  = '' === $event_details['billing_last_name'] ? $order->get_billing_last_name() : $event_details['billing_last_name'];
		$event_details['billing_address_1']  = '' === $event_details['billing_address_1'] ? $order->get_billing_address_1() : $event_details['billing_address_1'];
		$event_details['billing_address_2']  = '' === $event_details['billing_address_2'] ? $order->get_billing_address_2() : $event_details['billing_address_2'];
		$event_details['billing_city']       = '' === $event_details['billing_city'] ? $order->get_billing_city() : $event_details['billing_city'];
		$event_details['billing_country']    = '' === $event_details['billing_country'] ? $order->get_billing_country() : $event_details['billing_country'];
		$event_details['billing_postcode']   = '' === $event_details['billing_postcode'] ? $order->get_billing_postcode() : $event_details['billing_postcode'];
		$event_details['billing_phone']      = '' === $event_details['billing_phone'] ? $order->get_billing_phone() : $event_details['billing_phone'];
		$event_details['order_comments']     = '' === $event_details['order_comments'] ? $order->get_customer_note() : $event_details['order_comments'];


		$event_details['product_name']  = $post_title;
		$event_details['product_qty']   = $quantity;
		$event_details['product_total'] = $quantity * $booking_data['price'];

		$zoom_label                    = bkap_zoom_join_meeting_label( $post_id );
		$zoom_meeting                  = wc_get_order_item_meta( $item_id, $zoom_label );
		$event_details['zoom_meeting'] = '';
		if ( '' != $zoom_meeting ) {
			$event_details['zoom_meeting'] = $zoom_label . ' - ' . $zoom_meeting;
		}
		$person_ids = wc_get_order_item_meta( $item_id, '_person_ids' );
		if ( isset( $person_ids ) && '' != $person_ids ) {
			if ( isset( $person_ids[0] ) ) {
				$person_info = BKAP_Person::bkap_get_person_label( $product_id ) . ' : ' . $person_ids[0];
			} else {
				$person_info = '';
				foreach ( $person_ids as $p_key => $p_value ) {
					$person_info .= get_the_title( $p_key ) . ' : ' . $p_value . ',';
				}
			}
			$event_details['persons'] = $person_info;
		}
	}

	return $event_details;
}

/**
 * This function will prepare the Event Details array from Item ID.
 *
 * @param int   $item_id Item ID.
 * @param array $item_value Item Array.
 * @param int   $order_id Booking Data.
 * @param obj   $order Order Object.
 * @param array $additional_data Array of additional data.
 *
 * @since 5.6.1
 */
function bkap_event_details_from_item( $item_id, $item_value, $order_id, $order, $additional_data = array() ) {

	$event_details = array();

	$event_details['hidden_booking_date'] = $item_value['wapbk_booking_date'];

	if ( is_array( $additional_data ) && count( $additional_data ) > 0 ) {
		if ( isset( $additional_data['order_has_multiple_bookings'] ) && true === $additional_data['order_has_multiple_bookings'] && '' !== $additional_data['wapbk_booking_date'] ) {
			$event_details['hidden_booking_date'] = $additional_data['wapbk_booking_date'];
		}
	}

	if ( isset( $item_value['wapbk_checkout_date'] ) && $item_value['wapbk_checkout_date'] != '' ) {
		$event_details['hidden_checkout_date'] = $item_value['wapbk_checkout_date'];
	}

	if ( isset( $item_value['wapbk_time_slot'] ) && $item_value['wapbk_time_slot'] != '' ) {
		$event_details['time_slot'] = $item_value['wapbk_time_slot'];
	}

	if ( isset( $item_value['resource_id'] ) && $item_value['resource_id'] != '' ) {
		$event_details['resource'] = get_the_title( $item_value['resource_id'] );
	}

	if ( isset( $item_value['person_ids'] ) && $item_value['person_ids'] != '' ) {

		if ( isset( $item_value['person_ids'][0] ) ) {
			$person_info = BKAP_Person::bkap_get_person_label( $item_value['product_id'] ) . ' : ' . $item_value['person_ids'][0];
		} else {
			$person_info = '';
			foreach ( $item_value['person_ids'] as $p_key => $p_value ) {
				$person_info .= get_the_title( $p_key ) . ' : ' . $p_value . ',';
			}
		}
		$event_details['persons'] = $person_info;
	}

	$wcversion = ( version_compare( WOOCOMMERCE_VERSION, '3.0.0' ) < 0 );

	$event_details['billing_email']      = $wcversion ? $order->billing_email : $order->get_billing_email();
	$event_details['billing_first_name'] = $wcversion ? $order->billing_first_name : $order->get_billing_first_name();
	$event_details['billing_last_name']  = $wcversion ? $order->billing_last_name : $order->get_billing_last_name();
	$event_details['billing_address_1']  = $wcversion ? $order->billing_address_1 : $order->get_billing_address_1();
	$event_details['billing_address_2']  = $wcversion ? $order->billing_address_2 : $order->get_billing_address_2();
	$event_details['billing_postcode']   = $wcversion ? $order->billing_postcode : $order->get_billing_postcode();
	$event_details['shipping_postcode']  = $wcversion ? $order->shipping_postcode : $order->get_shipping_postcode();
	$event_details['billing_city']       = $wcversion ? $order->billing_city : $order->get_billing_city();
	$event_details['billing_country']    = $wcversion ? $order->billing_country : $order->get_billing_country();

	$event_details['billing_phone']  = $wcversion ? $order->billing_phone : $order->get_billing_phone();
	$event_details['order_comments'] = $wcversion ? $order->customer_note : $order->get_customer_note();
	$event_details['order_id']       = $order_id;

	$shipping_first_name = $wcversion ? $order->shipping_first_name : $order->get_shipping_first_name();
	if ( isset( $shipping_first_name ) && $shipping_first_name != '' ) {
		$event_details['shipping_first_name'] = $shipping_first_name;
	}

	$shipping_last_name = $wcversion ? $order->shipping_last_name : $order->get_shipping_last_name();
	if ( isset( $shipping_last_name ) && $shipping_last_name != '' ) {
		$event_details['shipping_last_name'] = $shipping_last_name;
	}

	$shipping_address_1 = $wcversion ? $order->shipping_address_1 : $order->get_shipping_address_1();
	if ( isset( $shipping_address_1 ) && $shipping_address_1 != '' ) {
		$event_details['shipping_address_1'] = $shipping_address_1;
	}

	$shipping_address_2 = $wcversion ? $order->shipping_address_2 : $order->get_shipping_address_2();
	if ( isset( $shipping_address_2 ) && $shipping_address_2 != '' ) {
		$event_details['shipping_address_2'] = $shipping_address_2;
	}

	$shipping_city = $wcversion ? $order->shipping_city : $order->get_shipping_city();
	if ( isset( $shipping_city ) && $shipping_city != '' ) {
		$event_details['shipping_city'] = $shipping_city;
	}

	$_product   = wc_get_product( $item_value['product_id'] );
	$post_title = $_product->get_title();

	$event_details['product_name']  = $post_title;
	$event_details['product_qty']   = $item_value['qty'];
	$event_details['product_total'] = $item_value['line_total'];

	$zoom_label                    = bkap_zoom_join_meeting_label( $item_value['product_id'] );
	$zoom_meeting                  = wc_get_order_item_meta( $item_id, $zoom_label );
	$event_details['zoom_meeting'] = '';
	if ( '' != $zoom_meeting ) {
		$event_details['zoom_meeting'] = $zoom_label . ' - ' . $zoom_meeting;
	}

	return $event_details;
}

/**
 * This function will prepare the Booking Object required for Preparing the Event.
 *
 * @param array $event_details Event Data.
 * @param int   $event_id Item ID.
 *
 * @since 5.6.1
 */
function bkap_event_data( $event_details, $event_id ) {

	if ( isset( $event_details['hidden_booking_date'] ) && $event_details['hidden_booking_date'] != '' ) {

		$booking_date = $event_details['hidden_booking_date'];

		$bkap        = new stdClass();
		$bkap->start = gmdate( 'Y-m-d', strtotime( $booking_date ) );

		if ( isset( $event_details['hidden_checkout_date'] ) && $event_details['hidden_checkout_date'] != '' ) {
			$checkout_date = $event_details['hidden_checkout_date'];
		} else {
			$checkout_date = $event_details['hidden_booking_date'];
		}

		$bkap->end = gmdate( 'Y-m-d', strtotime( $checkout_date ) );

		if ( isset( $event_details['time_slot'] ) && $event_details['time_slot'] != '' ) {

			$timeslot  = explode( ' - ', $event_details['time_slot'] );
			$from_time = gmdate( 'H:i', strtotime( $timeslot[0] ) );

			if ( isset( $timeslot[1] ) && $timeslot[1] != '' ) {
				$to_time        = gmdate( 'H:i', strtotime( $timeslot[1] ) );
				$bkap->end_time = $to_time;
			} else {
				$bkap->end_time = '00:00';
				$bkap->end      = gmdate( 'Y-m-d', strtotime( $event_details['hidden_booking_date'] . '+1 day' ) );
			}

			$bkap->start_time = $from_time;
		} elseif ( isset( $event_details['duration_time_slot'] ) && $event_details['duration_time_slot'] != '' ) {
			// duration_time_slot = 10:00 - 12:00
			$timeslot = explode( ' - ', $event_details['duration_time_slot'] );

			if ( isset( $timeslot[1] ) && $timeslot[1] != '' ) {
				$bkap->end_time = $timeslot[1];
			} else {
				$bkap->end_time = '00:00';
				$bkap->end      = gmdate( 'Y-m-d', strtotime( $event_details['hidden_booking_date'] . '+1 day' ) );
			}
			$bkap->start_time = $timeslot[0];

		} else {
			$bkap->start_time = '';
			$bkap->end_time   = '';
		}

		$bkap->resource = '';
		if ( isset( $event_details['resource'] ) && '' !== $event_details['resource'] ) {
			$bkap->resource = $event_details['resource'];
		}

		$bkap->persons = '';
		if ( isset( $event_details['persons'] ) && '' !== $event_details['persons'] ) {
			$bkap->persons = $event_details['persons'];
		}

		$bkap->client_email = $event_details['billing_email'];

		$billing_postcode = isset( $event_details['billing_postcode'] ) ? $event_details['billing_postcode'] : '';

		if ( get_option( 'woocommerce_enable_shipping_calc' ) == 'yes' ) {

			if ( get_option( 'woocommerce_ship_to_destination' ) == 'shipping' ) {

				if ( ( isset( $event_details['shipping_first_name'] ) && $event_details['shipping_first_name'] != '' ) && ( isset( $event_details['shipping_last_name'] ) && $event_details['shipping_last_name'] != '' ) ) {
					$bkap->client_name = $event_details['shipping_first_name'] . ' ' . $event_details['shipping_last_name'];
				} else {
					$bkap->client_name = $event_details['billing_first_name'] . ' ' . $event_details['billing_last_name'];
				}

				if ( ( isset( $event_details['shipping_address_1'] ) && $event_details['shipping_address_1'] != '' ) && ( isset( $event_details['shipping_address_2'] ) && $event_details['shipping_address_2'] != '' ) ) {
					$bkap->client_address = $event_details['shipping_address_1'] . ' ' . $event_details['shipping_address_2'] . ' ' . $event_details['shipping_postcode'];
				} else {
					$address = '';
					if ( isset( $event_details['billing_address_1'] ) ) {
						$address = $event_details['billing_address_1'] . ' ';
					}
					if ( isset( $event_details['billing_address_2'] ) ) {
						$address .= $event_details['billing_address_2'] . ' ';
					}
					$address .= $billing_postcode;
					$bkap->client_address = $address;
				}

				if ( isset( $event_details['shipping_city'] ) && $event_details['shipping_city'] != '' ) {
					$bkap->client_city = $event_details['shipping_city'];
				} else {
					$bkap->client_city = isset( $event_details['billing_city'] ) ? $event_details['billing_city'] : '';
				}
				$bkap->client_country = isset( $event_details['billing_country'] ) ? $event_details['billing_country'] : '' ;
			} elseif ( get_option( 'woocommerce_ship_to_destination' ) == 'billing' ) {

				if ( ( isset( $event_details['shipping_first_name'] ) && $event_details['shipping_first_name'] != '' ) && ( isset( $event_details['shipping_last_name'] ) && $event_details['shipping_last_name'] != '' ) ) {
					$bkap->client_name = $event_details['shipping_first_name'] . ' ' . $event_details['shipping_last_name'];
				} else {
					$bkap->client_name = $event_details['billing_first_name'] . ' ' . $event_details['billing_last_name'];
				}

				if ( isset( $event_details['shipping_address_1'] ) && $event_details['shipping_address_1'] != '' ) {

					$shippig_address = $event_details['shipping_address_1'];

					if ( isset( $event_details['shipping_address_2'] ) && $event_details['shipping_address_2'] != '' ) {
						$shippig_address .= $event_details['shipping_address_2'];
					}

					if ( isset( $event_details['shipping_postcode'] ) && $event_details['shipping_postcode'] != '' ) {
						$shippig_address .= $event_details['shipping_postcode'];
					}

					$bkap->client_address = $shippig_address;

				} else {
					$bkap->client_address = $event_details['billing_address_1'] . ' ' . $event_details['billing_address_2'] . ' ' . $billing_postcode;
				}

				if ( isset( $event_details['shipping_city'] ) && $event_details['shipping_city'] != '' ) {
					$bkap->client_city = $event_details['shipping_city'];
				} else {
					$bkap->client_city = $event_details['billing_city'];
				}
				$bkap->client_country = isset( $event_details['billing_country'] ) ? $event_details['billing_country'] : '' ;
			} elseif ( get_option( 'woocommerce_ship_to_destination' ) == 'billing_only' ) {
				$bkap->client_name    = $event_details['billing_first_name'] . ' ' . $event_details['billing_last_name'];
				$bkap->client_address = $event_details['billing_address_1'] . ' ' . $event_details['billing_address_2'] . ' ' . $billing_postcode;
				$bkap->client_city    = $event_details['billing_city'];
				$bkap->client_country = isset( $event_details['billing_country'] ) ? $event_details['billing_country'] : '' ;
			}
		} else {
			$bkap->client_name    = $event_details['billing_first_name'] . ' ' . $event_details['billing_last_name'];
			$bkap->client_address = $event_details['billing_address_1'] . ' ' . $event_details['billing_address_2'] . ' ' . $billing_postcode;
			$bkap->client_city    = $event_details['billing_city'];
			$bkap->client_country = isset( $event_details['billing_country'] ) ? $event_details['billing_country'] : '' ;
		}
		$bkap->client_phone = $event_details['billing_phone'];
		$bkap->order_note   = $event_details['order_comments'];
		$order              = wc_get_order( $event_details['order_id'] );

		$product          = $event_details['product_name'];
		$product_with_qty = $event_details['product_name'] . '(QTY: ' . $event_details['product_qty'] . ')';

		$bkap->order_total      = $event_details['product_total'];
		$bkap->product          = $product;
		$bkap->product_with_qty = $product_with_qty;

		$order_date = '';
		$bkap->id   = 0;
		$bkap->order_date_time = 0;
		if ( version_compare( WOOCOMMERCE_VERSION, '3.0.0' ) < 0 ) {
			$bkap->id              = $order->id;
			$bkap->order_date_time = $order->post->post_date;
			$order_date            = gmdate( 'Y-m-d', strtotime( $order->post->post_date ) );
		} else {
			if ( $order ) {
				$bkap->id              = $order->get_id();
				$order_post            = wc_get_order( $event_details['order_id'] );
				$order_timestamp       = ! is_null( $order_post->get_date_created() ) ? $order_post->get_date_created()->getOffsetTimestamp() : '';
				$bkap->order_date_time = gmdate( 'Y-m-d H:i:s', $order_timestamp );
				$order_date            = gmdate( 'Y-m-d', $order_timestamp );
			}
		}

		$bkap->order_date = $order_date;
		$bkap->item_id    = $event_id;

		$bkap->zoom_meeting = '';
		if ( isset( $event_details['zoom_meeting'] ) && '' !== $event_details['zoom_meeting'] ) {
			$bkap->zoom_meeting = $event_details['zoom_meeting'];
		}

		return $bkap;
	} else {
		return null;
	}
}

/**
 * This function will return the localtion description and sumarry of the event being exported to the calendar.
 *
 * @param array  $app Booking Object.
 * @param string $calendar Type of Calendar.
 * @since 5.6.1
 */
function bkap_get_event_location_description_summary( $app, $calendar = 'gcal' ) {

	switch ( $calendar ) {
		case 'gcal':
			$location_option    = get_option( 'bkap_calendar_event_location', '' );
			$summary_option     = get_option( 'bkap_calendar_event_summary', '' );
			$description_option = get_option( 'bkap_calendar_event_description', '' );
			$location_filter    = 'bkap_google_event_location';
			$summary_filter     = 'bkap_google_event_summary';
			$description_filter = 'bkap_google_event_description';
			break;
		case 'outlook':
			$location_option    = get_option( 'bkap_outlook_calendar_event_location', '' );
			$summary_option     = get_option( 'bkap_outlook_calendar_event_summary', '' );
			$description_option = get_option( 'bkap_outlook_calendar_event_description', '' );
			$location_filter    = 'bkap_outlook_event_location';
			$summary_filter     = 'bkap_outlook_event_summary';
			$description_filter = 'bkap_outlook_event_description';
			break;
	}

	$blog_name = get_bloginfo( 'name' );

	if ( $location_option != '' ) {
		$location = str_replace( array( 'ADDRESS', 'CITY' ), array( $app->client_address, $app->client_city ), $location_option );
	} else {
		$location = get_bloginfo( 'description' );
	}

	$summary = str_replace(
		array( 'SITE_NAME', 'CLIENT', 'PRODUCT_NAME', 'PRODUCT_WITH_QTY', 'ORDER_DATE_TIME', 'ORDER_DATE', 'ORDER_NUMBER', 'PRICE', 'PHONE', 'NOTE', 'ADDRESS', 'EMAIL', 'RESOURCE', 'PERSONS', 'ZOOM_MEETING' ),
		array( $blog_name, $app->client_name, $app->product, $app->product_with_qty, $app->order_date_time, $app->order_date, $app->id, $app->order_total, $app->client_phone, $app->order_note, $app->client_address, $app->client_email, $app->resource, $app->persons, $app->zoom_meeting ),
		$summary_option
	);

	$description = str_replace(
		array( 'SITE_NAME', 'CLIENT', 'PRODUCT_NAME', 'PRODUCT_WITH_QTY', 'ORDER_DATE_TIME', 'ORDER_DATE', 'ORDER_NUMBER', 'PRICE', 'PHONE', 'NOTE', 'ADDRESS', 'EMAIL', 'RESOURCE', 'PERSONS', 'ZOOM_MEETING', 'CITY', 'COUNTRY' ),
		array( $blog_name, $app->client_name, $app->product, $app->product_with_qty, $app->order_date_time, $app->order_date, $app->id, $app->order_total, $app->client_phone, $app->order_note, $app->client_address, $app->client_email, $app->resource, $app->persons, $app->zoom_meeting, $app->client_city, $app->client_country ),
		$description_option
	);

	$location    = apply_filters( $location_filter, $location, $app );
	$summary     = apply_filters( $summary_filter, $summary, $app );
	$description = apply_filters( $description_filter, $description, $app );

	return array(
		'location'    => $location,
		'summary'     => $summary,
		'description' => $description,
	);
}

/**
 * This function save the created event id in option/post_meta according to the item id and the product id.
 *
 * @param string $uid ID of Created Event.
 * @param int    $product_id Product ID.
 * @param int    $user_id User ID.
 * @param int    $item_number Item Number.
 * @param string $integration Type of Integration.
 *
 * @since 5.6.1
 */
function bkap_update_event_item_uid_data( $uid, $product_id, $user_id, $item_id, $item_number, $integration ) {

	switch ( $integration ) {
		case 'gcal':
			$bkap_event_item_ids  = 'bkap_event_item_ids';
			$bkap_event_uids_ids  = 'bkap_event_uids_ids';
			$tours_event_item_ids = 'tours_event_item_ids';
			$tours_event_uids_ids = 'tours_event_uids_ids';
			break;
		case 'outlook':
			$bkap_event_item_ids  = 'bkap_outlook_event_item_ids';
			$bkap_event_uids_ids  = 'bkap_outlook_event_uids_ids';
			$tours_event_item_ids = 'tours_outlook_event_item_ids';
			$tours_event_uids_ids = 'tours_outlook_event_uids_ids';
		default:
			break;
	}

	if ( 0 != $product_id ) {
		$event_orders = get_post_meta( $product_id, $bkap_event_item_ids, true );
		$event_uids   = get_post_meta( $product_id, $bkap_event_uids_ids, true );
	} else {
		// get the user role
		$user = new WP_User( $user_id );
		if ( isset( $user->roles[0] ) && 'tour_operator' == $user->roles[0] ) {
			$event_orders = get_the_author_meta( $tours_event_item_ids, $user_id );
			$event_uids   = get_the_author_meta( $tours_event_uids_ids, $user_id );
		} else {
			$event_orders = get_option( $bkap_event_item_ids );
			$event_uids   = get_option( $bkap_event_uids_ids );
		}
	}

	if ( $event_orders == '' || $event_orders == '{}' || $event_orders == '[]' || $event_orders == 'null' ) {
		$event_orders = array();
	}
	array_push( $event_orders, $item_id );

	if ( $event_uids == '' || $event_uids == '{}' || $event_uids == '[]' || $event_uids == 'null' ) {
		$event_uids = array();
	}

	if ( isset( $event_uids[ $item_id ] ) ) {
		if ( $item_number < 0 ) {
			$event_uids[ $item_id ] = $event_uids[ $item_id ] . ',' . $uid;
		} else {
			$uids                   = explode( ',', $event_uids[ $item_id ] );
			$uids[ $item_number ]   = $uid;
			$event_uids[ $item_id ] = implode( ',', $uids );
		}
	} else {
		$event_uids[ $item_id ] = $uid;
	}

	if ( 0 != $product_id ) {
		update_post_meta( $product_id, $bkap_event_item_ids, $event_orders );
		update_post_meta( $product_id, $bkap_event_uids_ids, $event_uids );
	} else {
		if ( isset( $user->roles[0] ) && 'tour_operator' == $user->roles[0] ) {
			update_user_meta( $user_id, $tours_event_item_ids, $event_orders );
			update_user_meta( $user_id, $tours_event_uids_ids, $event_uids );
		} else {
			update_option( $bkap_event_item_ids, $event_orders );
			update_option( $bkap_event_uids_ids, $event_uids );
		}
	}
}

/**
 * This function will sort the array by ascending order. Key of the array should be date in d-m-Y format
 *
 * @param string $a1 Parameter for ORDER BY query
 * @param string $b1 Parameter for ORDER BY query
 *
 * @return int return based on date difference
 * @since 4.4.0
 */
function bkap_orderby_date_key( $a1, $b1 ) {

	$format = 'd-m-Y';

	$a = strtotime( date_format( DateTime::createFromFormat( $format, $a1 ), 'Y-m-d H:i:s' ) );
	$b = strtotime( date_format( DateTime::createFromFormat( $format, $b1 ), 'Y-m-d H:i:s' ) );

	if ( $a == $b ) {
		return 0;
	} elseif ( $a > $b ) {
		return 1;
	} else {
		return -1;
	}
}

/**
 * This function will return the variation ID of default variation for a
 * given variable product.
 *
 * @param WC_Product $product - Product
 * @param array      $attributes - Array of default attribute values
 * @return integer Default Variation ID
 * @since 4.8.0
 */

function bkap_find_matching_product_variation( $product, $attributes ) {

	foreach ( $attributes as $key => $value ) {
		if ( strpos( $key, 'attribute_' ) === 0 ) {
			continue;
		}

		unset( $attributes[ $key ] );
		$attributes[ sprintf( 'attribute_%s', $key ) ] = $value;
	}

	if ( class_exists( 'WC_Data_Store' ) ) {

		$data_store = WC_Data_Store::load( 'product' );
		return $data_store->find_matching_product_variation( $product, $attributes );

	} else {

		return $product->get_matching_variation( $attributes );
	}
}

/**
 * This function is used to sort the timeslot by from time ascending order
 *
 * @since 4.8.0
 */

function bkap_sort_by_from_time( $x, $y ) {

	return $x['from_slot_hrs'] - $y['from_slot_hrs'];
}

/**
 * This function is used calculate the special booking price for composite product.
 *
 * @param int    $product_id Product ID.
 * @param array  $data Array of data required for getting special price. Array consists of date and booking type.
 * @param string $price Price.
 *
 * @since 5.6
 */
function bkap_get_special_price( $product_id, $data, $price = '' ) {

	$booking_special_prices = get_post_meta( $product_id, '_bkap_special_price', true );
	$special_prices         = array();

	if ( is_array( $booking_special_prices ) && count( $booking_special_prices ) > 0 ) {
		foreach ( $booking_special_prices as $special_key => $special_value ) {
			$weekday_set = $special_value['booking_special_weekday'];
			$date_set    = $special_value['booking_special_date'];
			if ( $weekday_set != '' ) {
				$special_prices[ $weekday_set ] = $special_value['booking_special_price'];
			} elseif ( $date_set != '' ) {
				$special_prices[ $date_set ] = $special_value['booking_special_price'];
			}
		}

		if ( ! empty( $special_prices ) ) {

			if ( 'multiple_days' === $data[2] ) {
				$dates = bkap_common::bkap_get_betweendays( $data[0], $data[1], 'Y-m-d' );
				$i     = 0;
				$total = 0;
				foreach ( $dates as $date ) {
					$weekday = gmdate( 'w', strtotime( $date ) );
					$weekday = 'booking_weekday_' . $weekday;

					if ( isset( $special_prices[ $date ] ) ) {
						$sprice = $special_prices[ $date ];
					} elseif ( isset( $special_prices[ $weekday ] ) ) {
						$sprice = $special_prices[ $weekday ];
					} else {
						$sprice = $price;
					}

					$total += $sprice;
					$i++;
				}

				$price = $total / $i;

			} else {
				$weekday = 'booking_weekday_' . $data[1];
				if ( isset( $special_prices[ $data[0] ] ) ) {
					$price = $special_prices[ $data[0] ];
				} elseif ( isset( $special_prices[ $weekday ] ) ) {
					$price = $special_prices[ $weekday ];
				}
			}
		}
	}
	return $price;
}



/**
 * This function will fetch the individual booking settings
 * saved in post meta and push them in a single array
 * and return the same.
 *
 * @return array $booking_settings
 * @since 4.0.0
 */

function bkap_get_post_meta( $product_id ) {

	$booking_settings = array();

	if ( isset( $product_id ) && $product_id > 0 ) {

		// create an array of the meta keys for individual data
		$meta_args = array(
			'_bkap_enable_booking',
			'_bkap_booking_type',
			'_bkap_enable_specific',
			'_bkap_enable_recurring',
			'_bkap_specific_dates',
			'_bkap_recurring_weekdays',
			'_bkap_recurring_lockout',
			'_bkap_enable_inline',
			'_bkap_purchase_wo_date',
			'_bkap_requires_confirmation',
			'_bkap_product_holidays',
			'_bkap_multiple_day_min',
			'_bkap_multiple_day_max',
			'_bkap_date_lockout',
			'_bkap_custom_ranges',
			'_bkap_abp',
			'_bkap_max_bookable_days',
			'_bkap_time_settings',
			'_bkap_fixed_blocks',
			'_bkap_price_ranges',
			'_bkap_gcal_integration_mode',
			'_bkap_gcal_calendar_id',
			'_bkap_enable_automated_mapping',
			'_bkap_default_variation',
			'_bkap_import_url',
			'_bkap_can_be_cancelled',
		);

		// run a foreach and save the data
		foreach ( $meta_args as $key => $value ) {
			$temp = get_post_meta( $product_id, $value, true );

			switch ( $value ) {
				case '_bkap_enable_booking':
					$booking_settings['booking_enable_date'] = $temp;
					break;
				case '_bkap_booking_type':
					if ( 'multiple_days' == $temp ) {
						$booking_settings['booking_enable_multiple_day'] = 'on';
						$booking_settings['booking_enable_time']         = '';
					} elseif ( 'date_time' == $temp ) {
						$booking_settings['booking_enable_multiple_day'] = '';
						$booking_settings['booking_enable_time']         = 'on';
					} elseif ( 'only_day' == $temp ) {
						$booking_settings['booking_enable_multiple_day'] = '';
						$booking_settings['booking_enable_time']         = '';
					}
					break;
				case '_bkap_enable_specific':
					$booking_settings['booking_specific_booking'] = $temp;
					break;
				case '_bkap_enable_recurring':
					$booking_settings['booking_recurring_booking'] = $temp;
					break;
				case '_bkap_specific_dates':
					$booking_settings['booking_specific_date'] = $temp;
					break;
				case '_bkap_recurring_weekdays':
					$booking_settings['booking_recurring'] = $temp;
					break;
				case '_bkap_recurring_lockout':
					$booking_settings['booking_recurring_lockout'] = $temp;
					break;
				case '_bkap_enable_inline':
					$booking_settings['enable_inline_calendar'] = $temp;
					break;
				case '_bkap_purchase_wo_date':
					$booking_settings['booking_purchase_without_date'] = $temp;
					break;
				case '_bkap_requires_confirmation':
					$booking_settings['booking_confirmation'] = $temp;
					break;
				case '_bkap_product_holidays':
					$booking_settings['booking_product_holiday'] = $temp;
					break;
				case '_bkap_multiple_day_min':
					if ( $temp > 0 ) {
						$booking_settings['enable_minimum_day_booking_multiple']  = 'on';
						$booking_settings['booking_minimum_number_days_multiple'] = $temp;
					} else {
						$booking_settings['enable_minimum_day_booking_multiple']  = '';
						$booking_settings['booking_minimum_number_days_multiple'] = 0;
					}
					break;
				case '_bkap_multiple_day_max':
						$booking_settings['booking_maximum_number_days_multiple'] = $temp;
					break;
				case '_bkap_date_lockout':
					$booking_settings['booking_date_lockout'] = $temp;
					break;
				case '_bkap_custom_ranges':
					$booking_settings['booking_date_range'] = $temp;
					break;
				case '_bkap_abp':
					$booking_settings['booking_minimum_number_days'] = $temp;
					break;
				case '_bkap_max_bookable_days':
					$booking_settings['booking_maximum_number_days'] = $temp;
					break;
				case '_bkap_time_settings':
					$booking_settings['booking_time_settings'] = $temp;
					break;
				case '_bkap_fixed_blocks':
					// if ( isset( $temp ) && $temp != '' ) {
						$booking_settings['booking_fixed_block_enable'] = $temp;
					// }
					break;
				case '_bkap_price_ranges':
					// if ( isset( $temp ) && $temp != '' ) {
						$booking_settings['booking_block_price_enable'] = $temp;
					// }
					break;
				case '_bkap_gcal_integration_mode':
					$booking_settings['product_sync_integration_mode'] = $temp;
					break;
				case '_bkap_gcal_calendar_id':
					$booking_settings['product_sync_calendar_id'] = $temp;
					break;
				case '_bkap_enable_automated_mapping':
					// if ( isset( $temp ) && $temp != '' ) {
						$booking_settings['enable_automated_mapping'] = $temp;
					// }
					break;
				case '_bkap_default_variation':
					// if ( isset( $temp ) && $temp > 0 ) {
						$booking_settings['gcal_default_variation'] = $temp;
					// }
					break;
				case '_bkap_import_url':
					$booking_settings['ics_feed_url'] = $temp;
					break;
				case '_bkap_can_be_cancelled':
					$booking_settings['booking_can_be_cancelled'] = $temp;
					break;
				default:
					break;
			}
		}
	}
	return $booking_settings;
}

/**
 * This function will return default or current booking settings.
 *
 * @return string
 * @since 5.13.0
 */
function bkap_get_post_meta_data( $product_id, $meta_key, $default_data = array(), $default = false ) {

	if ( 0 === $product_id ) {
		return '';
	}

	if ( $default ) {
		return isset( $default_data[ $meta_key ] ) ? $default_data[ $meta_key ] : '';
	}

	if ( '_bkap_booking_type' === $meta_key ) {
		return bkap_type( $product_id );
	}
	
	return get_post_meta( $product_id, $meta_key, true );
}

/**
 * This function will return path to ajax loader gif file.
 *
 * @return string
 * @since 5.10.0
 */
function bkap_ajax_loader_gif() {
	return plugins_url() . '/woocommerce-booking/assets/images/ajax-loader.gif';
}

/**
 * This function will return path to ajax loader gif file.
 *
 * @param int $product_id Product ID.
 * @param obj $_product Product Object.
 * @since 5.10.0
 */
function bkap_include_booking_form( $product_id, $_product ) {

	BKAP_Scripts::inlcude_frontend_scripts_css( $product_id ); // CSS scripts.
	BKAP_Scripts::include_frontend_scripts_js( $product_id ); // JS scripts.
	$hidden_dates = bkap_booking_process::bkap_localize_process_script( $product_id ); // localize the scripts.
	// print the hidden fields.
	// print the booking form.
	$booking_settings = bkap_setting( $product_id );
	$global_settings  = bkap_global_setting();

	wc_get_template(
		'bookings/bkap-bookings-box.php',
		array(
			'product_id'       => $product_id,
			'product_obj'      => $_product,
			'booking_settings' => $booking_settings,
			'global_settings'  => $global_settings,
			'hidden_dates'     => $hidden_dates,
		),
		'woocommerce-booking/',
		BKAP_BOOKINGS_TEMPLATE_PATH
	);

	// price display.
	bkap_booking_process::bkap_price_display();
}

/**
 * This function will return path to help image.
 *
 * @since 5.11.0
 */
function bkap_help_tip_img() {
	return plugins_url() . '/woocommerce/assets/images/help.png';
}

/**
 * This function will add html for help image.
 *
 * @since 5.11.0
 */
function bkap_help_tip_html( $title ) {
	?>
<img class="help_tip" width="16" height="16" data-tip="<?php _e( $title, 'woocommerce-booking' ); // phpcs:ignore ?>" src="<?php echo bkap_help_tip_img(); ?>" />
	<?php
}

/**
 * This function will validate the person selection according to person settings.
 *
 * @param array $selected_person_data Selected Person Data in Booking Form.
 * @param int   $total_person Total of selected person data in booking form.
 * @param array $bkap_settings Booking Settings.
 * @param int   $product_id Product ID.
 * @since 5.11.0
 */
function bkap_validate_person_selection( $selected_person_data, $total_person, $bkap_settings, $product_id ) {

	if ( ! empty( $selected_person_data ) ) {

		$min_person  = $bkap_settings['bkap_min_person'];
		$max_person  = $bkap_settings['bkap_max_person'];
		$person_data = $bkap_settings['bkap_person_data'];
		/* Translators: %s Maximum Person Message. */
		$max_msg = apply_filters( 'bkap_max_person_message', __( 'The maximum persons per group is %d', 'woocommerce-booking' ), $product_id );
		/* Translators: %s Minimum Person Message. */
		$min_msg = apply_filters( 'bkap_min_person_message', __( 'The minimum persons per group is %d', 'woocommerce-booking' ), $product_id );

		if ( $max_person && $total_person > $max_person ) {
			return sprintf( $max_msg, $max_person );
		}

		if ( $total_person < $min_person ) {
			return sprintf( $min_msg, $min_person );
		}

		foreach ( $selected_person_data as $key => $value ) {
			$person_id = (int) $value['person_id'];
			if ( 1 !== $person_id ) {
				$min_person = $person_data[ $person_id ]['person_min'];
				$max_person = $person_data[ $person_id ]['person_max'];

				if ( $max_person && $value['person_val'] > $max_person ) {
					return sprintf( $max_msg, $max_person );
				}

				if ( $value['person_val'] < $min_person ) {
					return sprintf( $min_msg, $min_person );
				}
			}
		}
	}

	return '';
}

/**
 * This function will return the message to be displayed in the Booking Form
 * when the selected person values in the persons field exceeds the Max Booking.
 *
 * @param string $time Date & Time Booking Type.
 *
 * @since 5.11.0
 */
function bkap_max_persons_available_msg( $time = '', $product_id = 0 ) {

	if ( '' !== $time ) {
		/* Translators: %s Max Person Available Message. */
		$msg = __( 'There are a maximum of %1$s places remaining on %2$s - %3$s', 'woocommerce-booking' );
	} else {
		/* Translators: %s Max Person Available Message. */
		$msg = __( 'There are a maximum of %1$s places remaining on %2$s', 'woocommerce-booking' );
	}

	return apply_filters( 'bkap_max_persons_available_msg', $msg, $product_id );
}

/**
 * This function will add html for displaying the Booking Error on Booking Form.
 *
 * @since 5.11.0
 */
function bkap_woocommerce_error_div( $message ) {
	return '<div class="woocommerce-error">' . $message . '<div>';
}

/**
 * Process Order Refund through Code
 *
 * @return WC_Order_Refund|WP_Error
 *
 * @since 5.11.0
 */
function bkap_process_refund_for_booking( $order_id, $order_item_id, $refund_reason = '' ) {

	$order = wc_get_order( $order_id );

	// IF it's something else such as a WC_Order_Refund, we don't want that.
	if ( ! is_a( $order, 'WC_Order' ) ) {
		return;
	}

	// Get Items
	$order_items = $order->get_items();

	// Refund Amount
	$refund_amount = 0;

	// Prepare line items which we are refunding
	$line_items = array();

	if ( $order_items = $order->get_items() ) {

		foreach ( $order_items as $item_id => $item ) {

			if ( $item_id == $order_item_id ) {
				$item_meta    = $order->get_item_meta( $item_id );
				$product_data = wc_get_product( $item_meta['_product_id'][0] );
				$item_ids[]   = $item_id;
				$tax_data     = $item_meta['_line_tax_data'];
				$refund_tax   = 0;

				if ( is_array( $tax_data[0] ) ) {
					$refund_tax = array_map( 'wc_format_decimal', $tax_data[0] );
				}

				$refund_amount          = wc_format_decimal( $refund_amount ) + wc_format_decimal( $item_meta['_line_total'][0] );
				$line_items[ $item_id ] = array(
					'qty'          => $item_meta['_qty'][0],
					'refund_total' => wc_format_decimal( $item_meta['_line_total'][0] ),
					'refund_tax'   => $refund_tax,
				);
			}
		}
	}

	if ( count( $line_items ) > 0 ) {
		$refund = wc_create_refund(
			array(
				'amount'         => $refund_amount,
				'reason'         => $refund_reason,
				'order_id'       => $order_id,
				'line_items'     => $line_items,
				'refund_payment' => true,
			)
		);
	}

	return $refund;
}

/**
 * Prepare string information of booked persons.
 *
 * @param array $persons Array of booked person.
 * @param int   $product_id Product ID.
 *
 * @return WC_Order_Refund|WP_Error
 *
 * @since 5.12.0
 */
function bkap_persons_info( $persons, $product_id ) {
	if ( isset( $persons[0] ) ) {
		$person_info = BKAP_Person::bkap_get_person_label( $product_id ) . ' : ' . $persons[0];
	} else {
		$person_info = '';
		foreach ( $persons as $p_key => $p_value ) {
			$person_info .= get_the_title( $p_key ) . ' : ' . $p_value . ',';
		}
	}

	return $person_info;
}

/**
 * This function return the first booking product array.
 *
 * @param string $booking_type Booking Type.
 * @since 5.14.0
 */
function bkap_get_first_booking_data_from_cart( $booking_type = '' ) {

	$bkap_booking = array();
	$cart         = WC()->cart;

	if ( isset( WC()->cart ) ) {
		$cart_content = WC()->cart->cart_contents;

		if ( ! empty( $cart_content ) ) {

			foreach ( $cart_content as $key => $value ) {
				if ( isset( $value['bkap_booking'] ) ) {
					$p_id   = $value['product_id'];
					$b_type = bkap_type( $p_id );

					if ( '' !== $booking_type ) {

						if ( $booking_type == $b_type ) {
							$bkap_booking = $value['bkap_booking'][0];
							break;
						} else {
							continue;
						}
					} else {
						$bkap_booking = $value['bkap_booking'][0];
						break;
					}
				}
			}
		}
	}

	return $bkap_booking;
}

/**
 * Sorting the array based on the key value.
 *
 * @since 5.14.0
 */
function bkap_array_orderby_array_key() {
	$args = func_get_args();
	$data = array_shift( $args );
	foreach ( $args as $n => $field ) {
		if ( is_string( $field ) ) {
			$tmp = array();
			foreach ( $data as $key => $row ) {
				$tmp[ $key ] = $row[ $field ];
			}
			$args[ $n ] = $tmp;
		}
	}
	$args[] = &$data;
	call_user_func_array( 'array_multisort', $args );
	return array_pop( $args );
}

/**
 * Create temporary directory in uploads folder.
 *
 * @since 5.14.0
 */
function bkap_temporary_directory() {
	$file_path = WP_CONTENT_DIR . '/uploads/wbkap_tmp';
	if ( ! file_exists( $file_path ) ) {
		mkdir( $file_path, 0777 );
	}

	return $file_path;
}

/**
 * Check if HPOS is enabled or not.
 *
 * @since 5.17.0
 * return boolean true if enabled else false
 */

function bkap_wc_hpos_enabled() {

	if ( class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) ) {
		if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
			return true;
		}
	}

	return false;
}

/**
 * Order url considering the HPOS check.
 *
 * @since 5.17.0
 * return string $order_url
 */
function bkap_order_url( $order_id ) {

	if ( bkap_wc_hpos_enabled() ) {
		$order_url = admin_url( 'admin.php?page=wc-orders&id=' . ( $order_id ) . '&action=edit' );
	} else {
		$order_url = admin_url( 'post.php?post=' . ( $order_id ) . '&action=edit' );
	}

	return $order_url;
}

/**
 * Skips json_decode when value is an array.
 *
 * @since 5.19.0
 */
function bkap_json_decode( $value, $return_as_array = false ) {
	return is_array( $value ) ? $value : json_decode( $value, $return_as_array );
}

/**
 * Options for the Types of Ranges in Set availability by dates/months table.
 *
 * @since 5.20.0
 */
function bkap_type_of_ranges_options() {

	$type_of_ranges = array(
		'custom_range'    => __( 'Custom Range', 'woocommerce-booking' ),
		'specific_dates'  => __( 'Specific Dates', 'woocommerce-booking' ),
		'range_of_months' => __( 'Range of Months', 'woocommerce-booking' ),
		'holidays'        => __( 'Holidays', 'woocommerce-booking' ),
	);

	return $type_of_ranges;
}

function bkap_weekdays() {

	$bkap_weekdays = array(
		'booking_weekday_0' => __( 'Sunday', 'woocommerce-booking' ),
		'booking_weekday_1' => __( 'Monday', 'woocommerce-booking' ),
		'booking_weekday_2' => __( 'Tuesday', 'woocommerce-booking' ),
		'booking_weekday_3' => __( 'Wednesday', 'woocommerce-booking' ),
		'booking_weekday_4' => __( 'Thursday', 'woocommerce-booking' ),
		'booking_weekday_5' => __( 'Friday', 'woocommerce-booking' ),
		'booking_weekday_6' => __( 'Saturday', 'woocommerce-booking' ),
	);

	return $bkap_weekdays;
}

/**
 * Start day/ End Day options in Fixed Blocks.
 *
 * @since 5.20.0
 */
function bkap_fixed_days() {

	$bkap_fixed_days = array(
		'any_days' => __( 'Any Days', 'woocommerce-booking' ),
		'0'        => __( 'Sunday', 'woocommerce-booking' ),
		'1'        => __( 'Monday', 'woocommerce-booking' ),
		'2'        => __( 'Tuesday', 'woocommerce-booking' ),
		'3'        => __( 'Wednesday', 'woocommerce-booking' ),
		'4'        => __( 'Thursday', 'woocommerce-booking' ),
		'5'        => __( 'Friday', 'woocommerce-booking' ),
		'6'        => __( 'Saturday', 'woocommerce-booking' ),
	);

	return $bkap_fixed_days;
}

/**
 * Weekdays options for Booking.
 *
 * @since 5.20.0
 */
function bkap_days() {

	$bkap_days = array(
		'0' => 'Sunday',
		'1' => 'Monday',
		'2' => 'Tuesday',
		'3' => 'Wednesday',
		'4' => 'Thursday',
		'5' => 'Friday',
		'6' => 'Saturday',
	);

	return $bkap_days;
}

/**
 * Booking Date Formats.
 *
 * @since 5.20.0
 */
function bkap_date_formats() {

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

	return $bkap_date_formats;
}

/**
 * Zoom Option enable.
 *
 * @param int $product_id Product ID.
 *
 * @since 5.2.0
 */
function bkap_zoom_meeting_enable( $product_id = 0, $resource_id = 0 ) {

	/* Check if the product having the zoom meeting option enabled. */
	$check = false;
	if ( $product_id ) {
		$zoom_enable = get_post_meta( $product_id, '_bkap_zoom_meeting', true );
		if ( 'on' === $zoom_enable ) {

			if ( $resource_id > 0 ) {
				$zoom_host = get_post_meta( $resource_id, '_bkap_resource_meeting_host', true );
			} else {
				$zoom_host = get_post_meta( $product_id, '_bkap_zoom_meeting_host', true );
			}

			if ( '' !== $zoom_host ) {
				$check = true;
			}
		}
	} else {
		$check = true;
	}

	if ( ! $check ) {
		return false;
	}

	$zoom_type = bkap_zoom_connection_type();

	if ( '' === $zoom_type ) {
		return false;
	}

	$zoom_connection = bkap_zoom_connection();
	$response        = json_decode( $zoom_connection->bkap_list_users() );
	if ( ! empty( $response ) ) {
		if ( ! empty( $response->code ) ) {
			return false;
		}

		if ( http_response_code() === 200 ) {
			return true;
		}
	}

	return true;
}

/**
 * Zoom Meeting Label.
 *
 * @param int $product_id Product ID.
 *
 * @since 5.2.0
 */
function bkap_zoom_join_meeting_label( $product_id ) {
	return apply_filters( 'bkap_zoom_join_meeting_label', __( 'Zoom Meeting', 'woocommerce-booking' ), $product_id );
}

/**
 * Zoom Meeting Link Text.
 *
 * @param int $product_id Product ID.
 *
 * @since 5.2.0
 */
function bkap_zoom_join_meeting_text( $product_id ) {
	return apply_filters( 'bkap_zoom_join_meeting_text', __( 'Join Meeting', 'woocommerce-booking' ), $product_id );
}

/**
 * Zoom Meeting Link Text.
 *
 * @param array $product_ids Array of Product IDs.
 * @since 5.2.0
 */
function bkap_get_bookings_to_assign_zoom_meeting( $product_ids = array() ) {

	$zoom_booking_id = 0;
	$start_date      = gmdate( 'YmdHis', current_time( 'timestamp' ) ); // phpcs:ignore
	$args            = array(
		'post_type'      => 'bkap_booking',
		'post_status'    => array( 'paid', 'pending-confirmation', 'confirmed' ),
		'posts_per_page' => -1,
		'meta_query'     => array( // phpcs:ignore
			'relation' => 'AND',
			array(
				'key'     => '_bkap_start',
				'value'   => $start_date,
				'compare' => '>=',
			),
			array(
				'key'     => '_bkap_product_id',
				'value'   => $product_ids,
				'compare' => 'IN',
			),
			array(
				'key'     => '_bkap_zoom_meeting_link',
				'compare' => 'NOT EXISTS',
			),
		),
	);

	$posts = get_posts( $args );

	return $posts;
}

/**
 * Zoom Redirect URL.
 *
 * @since 5.23.0
 */
function bkap_zoom_redirect_url() {

	$query_args     = array(
		'post_type' => 'bkap_booking',
		'page'      => 'woocommerce_booking_page',
		'action'    => 'calendar_sync_settings',
		'section'   => 'zoom_meeting',
	);
	$zoom_page_link = add_query_arg( $query_args, admin_url( 'edit.php' ) );

	return $zoom_page_link;
}

/**
 * Zoom Connection Type.
 *
 * @since 5.23.0
 */
function bkap_zoom_connection_type() {

	$type               = '';
	$zoom_api_key       = get_option( 'bkap_zoom_api_key' );
	$zoom_api_secret    = get_option( 'bkap_zoom_api_secret' );
	$zoom_client_id     = get_option( 'bkap_zoom_client_id', '' );
	$zoom_client_secret = get_option( 'bkap_zoom_client_secret', '' );

	if ( '' !== $zoom_client_id && '' !== $zoom_client_secret ) {
		$bkap_zoom_access_token = get_option( 'bkap_zoom_access_token', '' );
		if ( '' === $bkap_zoom_access_token ) {
			if ( empty( $zoom_api_key ) || empty( $zoom_api_secret ) ) {
				return '';
			} else {
				$type = 'jwt';
			}
		} else {
			$type = 'oauth';
		}
	} elseif ( empty( $zoom_api_key ) || empty( $zoom_api_secret ) ) {
		$type = '';
	} else {
		$type = 'jwt';
	}

	return $type;
}

/**
 * Updates the Booking History table. Removes/Inactivates the
 * desired records for the deleted time slot.
 *
 * @param int    $product_id - Product ID
 * @param string $day_value - Weekday/Date
 * @param string $from_time - H:i
 * @param string $to_time - H:i
 * @global object $wpdb Global wpdb Object
 *
 * @since 4.0.0
 */
function bkap_delete_booking_history( $product_id, $day_value, $from_time = '', $to_time = '' ) {

	global $wpdb;

	$to_hrs  = '';
	$to_mins = '';

	if ( isset( $to_time ) && '' != $to_time ) {
		$to_time_array = explode( ':', $to_time );
		$to_hrs        = $to_time_array[0];
		$to_mins       = $to_time_array[1];
	}

	if ( $to_hrs == 0 && $to_mins == 0 ) {
		$to_time = '';
	}

	// set all date records to inactive
	$from_db = gmdate( 'H:i', strtotime( $from_time ) );
	$to_db   = gmdate( 'H:i', strtotime( $to_time ) );

	if ( isset( $day_value ) && substr( $day_value, 0, 7 ) == 'booking' ) { // recurring weekday

		// delete the base record.
		$delete_base = 'DELETE FROM `' . $wpdb->prefix . "booking_history`
			WHERE post_id = '" . $product_id . "'
			AND weekday = '" . $day_value . "'
			AND start_date = '0000-00-00'
			AND TIME_FORMAT( from_time, '%H:%i' ) = '" . $from_db . "'
			AND TIME_FORMAT( to_time, '%H:%i' ) = '" . $to_db . "'";
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
		$wpdb->query( $delete_base );

		if ( $to_time == '' ) {
			$update_date_status = 'UPDATE `' . $wpdb->prefix . "booking_history`
							SET status = 'inactive'
							WHERE post_id = '" . $product_id . "'
							AND weekday = '" . $day_value . "'
							AND start_date <> '0000-00-00'
							AND TIME_FORMAT( from_time, '%H:%i' ) = '" . $from_db . "'
							AND to_time = '" . $to_time . "'";

		} else {
			$update_date_status = 'UPDATE `' . $wpdb->prefix . "booking_history`
							SET status = 'inactive'
							WHERE post_id = '" . $product_id . "'
							AND weekday = '" . $day_value . "'
							AND start_date <> '0000-00-00'
							AND TIME_FORMAT( from_time, '%H:%i' ) = '" . $from_db . "'
							AND TIME_FORMAT( to_time, '%H:%i' ) = '" . $to_db . "'";
		}
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
		$wpdb->query( $update_date_status );

	} elseif ( isset( $day_value ) && '' != $day_value ) { // specific date.

		$date = gmdate( 'Y-m-d', strtotime( $day_value ) );

		// set the date record to inactive
		$update_date_query = 'UPDATE `' . $wpdb->prefix . "booking_history`
						SET status = 'inactive'
						WHERE post_id = '" . $product_id . "'
						AND start_date = '" . $date . "'
						AND from_time = '" . $from_time . "'
						AND to_time = '" . $to_time . "'";
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
		$delete_result = $wpdb->query( $update_date_query );

		if ( $delete_result == 0 ) {

			// set the date record to inactive.
			$update_date_query = 'UPDATE `' . $wpdb->prefix . "booking_history`
							SET status = 'inactive'
							WHERE post_id = '" . $product_id . "'
							AND start_date = '" . $date . "'
							AND from_time = '" . $from_db . "'
							AND to_time = '" . $to_db . "'";
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
			$delete_result = $wpdb->query( $update_date_query );
		}
	}
}

/**
 * Updates the Booking History data.
 *
 * @param int    $product_id - Product ID
 * @param array  $settings Booking Settings
 * @param string $booking_type Booking Type
 *
 * @since 4.0.0
 */
function bkap_update_booking_history( $product_id, $settings, $booking_type ) {

	global $wpdb;

	if ( in_array( $booking_type, array( 'only_day', 'multidates' ) ) ) {

		if ( count( $settings ) > 0 ) {
			$recurring_array   = $settings['_bkap_recurring_weekdays'];
			$recurring_lockout = $settings['_bkap_recurring_lockout'];
			$specific_array    = isset( $settings['_bkap_specific_dates'] ) ? $settings['_bkap_specific_dates'] : array();

			// recurring days and lockout update
			if ( count( $recurring_array ) > 0 && count( $recurring_lockout ) > 0 ) {

				foreach ( $recurring_array as $weekday => $w_status ) {
					if ( 'on' === $w_status ) { // weekday is enabled
						$available_booking = $recurring_lockout[ $weekday ];
						$updated_lockout   = $recurring_lockout[ $weekday ];
						$check_weekday       = $wpdb->get_results( $wpdb->prepare( 'SELECT total_booking, available_booking FROM `' . $wpdb->prefix . "booking_history` WHERE post_id = %d AND weekday = %s AND start_date = '0000-00-00' AND status = ''", $product_id, $weekday ) ); // check if the weekday is already present.

						// if yes, then update the lockout
						if ( isset( $check_weekday ) && count( $check_weekday ) > 0 ) { // there will be only 1 active record at any given time
							if ( is_numeric( $recurring_lockout[ $weekday ] ) && $recurring_lockout[ $weekday ] > 0 ) {
								$change_in_lockout = $recurring_lockout[ $weekday ] - $check_weekday[0]->total_booking;
							} elseif ( '' === $recurring_lockout[ $weekday ] || 0 === $recurring_lockout[ $weekday ]) { // unlimited bookings
								$change_in_lockout = 0;
							}

							if ( isset( $change_in_lockout ) && is_numeric( $change_in_lockout ) ) {

								// Update the existing record so that lockout is managed and orders do not go missing frm the View bookings page
								if ( 0 === $change_in_lockout && ( '' === $recurring_lockout[ $weekday ] || 0 === $recurring_lockout[ $weekday ] ) ) { // unlimited bookings
									// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
									$wpdb->query(
										$wpdb->prepare(
											"UPDATE {$wpdb->prefix}booking_history
											SET total_booking = %d,
												available_booking = %d
											WHERE post_id = %d
											AND weekday = %s
											AND start_date = '0000-00-00'
											AND status = ''",
											$updated_lockout,
											$change_in_lockout,
											$product_id,
											$weekday
										)
									);
								} else {
									// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
									$wpdb->query(
										$wpdb->prepare(
											"UPDATE {$wpdb->prefix}booking_history
											SET total_booking = %d,
												available_booking = available_booking + %d
											WHERE post_id = %d
											AND weekday = %s
											AND start_date = '0000-00-00'
											AND status = ''",
											$updated_lockout,
											$change_in_lockout,
											$product_id,
											$weekday
										)
									);
								}
							}
						} else {
							// if not found, check if there's a date record present.
							// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
							$lockout_results  = $wpdb->get_results(
								$wpdb->prepare(
									'SELECT total_booking FROM `' . $wpdb->prefix . "booking_history` WHERE post_id = %d AND start_date != '0000-00-00' AND weekday = %s ORDER BY id DESC LIMIT 1",
									$product_id,
									$weekday
								)
							);

							if ( isset( $lockout_results ) && count( $lockout_results ) > 0 ) {
								if ( is_numeric( $recurring_lockout[ $weekday ] ) && $recurring_lockout[ $weekday ] > 0 ) {
									$change_in_lockout = $recurring_lockout[ $weekday ] - $lockout_results[0]->total_booking;
									$available_booking = $lockout_results[0]->total_booking + $change_in_lockout;
								} elseif ( $recurring_lockout[ $weekday ] === '' || $recurring_lockout[ $weekday ] == 0 ) {
									$change_in_lockout = 0;
									$available_booking = 0;
								}
							}

							// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
							$wpdb->query(
								$wpdb->prepare(
									"INSERT INTO {$wpdb->prefix}booking_history 
									(post_id, weekday, start_date, end_date, from_time, to_time, total_booking, available_booking) 
									VALUES (%d, %s, %s, %s, %s, %s, %d, %d)",
									$product_id,
									$weekday,
									'0000-00-00',
									'0000-00-00',
									'',  // Empty from_time.
									'',  // Empty to_time.
									$updated_lockout,
									$available_booking
								)
							);
						}

						if ( isset( $change_in_lockout ) && is_numeric( $change_in_lockout ) ) {

							// Update the existing records for the dates.
							if ( 0 === $change_in_lockout && ( '' === $recurring_lockout[ $weekday ] || 0 === $recurring_lockout[ $weekday ] ) ) { // unlimited bookings

								// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
								$wpdb->query(
									$wpdb->prepare(
										"UPDATE {$wpdb->prefix}booking_history
										SET total_booking = %d, 
											available_booking = %d, 
											status = ''
										WHERE post_id = %d 
										AND weekday = %s 
										AND start_date <> %s",
										$updated_lockout,
										$change_in_lockout,
										$product_id,
										$weekday,
										'0000-00-00'
									)
								);
							} else {
								// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
								$wpdb->query(
									$wpdb->prepare(
										"UPDATE {$wpdb->prefix}booking_history
										SET total_booking = %d, 
											available_booking = available_booking + %d, 
											status = ''
										WHERE post_id = %d 
										AND weekday = %s 
										AND start_date <> %s",
										$updated_lockout,
										$change_in_lockout,
										$product_id,
										$weekday,
										'0000-00-00'
									)
								);
							}
						}
					} else { // weekday is disabled.

						// if a record exists in the table, it needs to be deactivated.
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
						$wpdb->query( $wpdb->prepare( 'UPDATE `' . $wpdb->prefix . "booking_history` SET status = 'inactive' WHERE post_id = %d AND weekday = %s", $product_id, $weekday ) );

						// Delete the base records for the recurring weekdays.
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
						$wpdb->query( 'DELETE FROM `' . $wpdb->prefix . "booking_history` WHERE post_id = '" . $product_id . "' AND weekday = '" . $weekday . "' AND start_date = '0000-00-00'" );
					}
				}
			}

			if ( is_array( $specific_array ) && count( $specific_array ) > 0 ) {
				foreach ( $specific_array as $specific_date => $specific_lockout ) {
					$insert            = true;
					$specific_date     = gmdate( 'Y-m-d', strtotime( $specific_date ) );
					$available_booking = $specific_lockout;
					$updated_lockout   = $specific_lockout;
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
					$check_date1 = $wpdb->get_results(
						$wpdb->prepare(
							'SELECT total_booking, available_booking FROM `' . $wpdb->prefix . "booking_history` WHERE post_id = %d AND weekday != '' AND start_date = %s AND status = ''",
							$product_id,
							$specific_date
						)
					);

					if ( count( $check_date1 ) > 0 ) {
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
						$wpdb->query( 'UPDATE `' . $wpdb->prefix . "booking_history` SET weekday = '', status = '' WHERE post_id = '" . $product_id . "' AND start_date = '" . $specific_date . "'");
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
						$insert = false;
						if ( is_numeric( $specific_lockout ) && $specific_lockout > 0 ) {
							$change_in_lockout = $specific_lockout - $check_date[0]->total_booking;
						} elseif ( $specific_lockout === '' || $specific_lockout == 0 ) { // unlimited bookings.
							$change_in_lockout = 0;
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
							$insert = false;
							if ( is_numeric( $specific_lockout ) && $specific_lockout > 0 ) {
								$change_in_lockout = $specific_lockout - $lockout_results[0]->total_booking;
							} elseif ( $specific_lockout === '' || $specific_lockout == 0 ) { // unlimited bookings
								$change_in_lockout = 0;
							}
						}						
					}

					if ( $insert ) {
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
						$wpdb->query(
							$wpdb->prepare(
								"INSERT INTO {$wpdb->prefix}booking_history 
								(post_id, weekday, start_date, end_date, from_time, to_time, total_booking, available_booking) 
								VALUES (%d, %s, %s, %s, %s, %s, %d, %d)",
								$product_id,
								'', // Empty string for weekday.
								$specific_date,
								'0000-00-00',
								'', // Empty string for from_time.
								'', // Empty string for to_time.
								$specific_lockout,
								$available_booking
							)
						);
					} elseif ( isset( $change_in_lockout ) && is_numeric( $change_in_lockout ) ) {
						// Update the existing record so that lockout is managed and orders do not go missing frm the View bookings page.
						if ( 0 === $change_in_lockout && ( '' === $specific_lockout || 0 === $specific_lockout ) ) { // unlimited bookings.
							// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
							$wpdb->query(
								$wpdb->prepare(
									"UPDATE {$wpdb->prefix}booking_history 
									SET total_booking = %d, available_booking = %d, status = %s 
									WHERE post_id = %d AND weekday = %s AND start_date = %s",
									$specific_lockout,
									$change_in_lockout,
									'',
									$product_id,
									'',
									$specific_date
								)
							);
						} else {
							// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
							$wpdb->query(
								$wpdb->prepare(
									"UPDATE {$wpdb->prefix}booking_history 
									SET total_booking = %d, available_booking = available_booking + %d, status = %s 
									WHERE post_id = %d AND weekday = %s AND start_date = %s",
									$specific_lockout,
									$change_in_lockout,
									'',
									$product_id,
									'',
									$specific_date
								)
							);
						}
					}
				}
			}
		}

		return;
	}

	if ( in_array( $booking_type, array( 'date_time', 'multidates_fixedtime' ) ) ) {

		if ( count( $settings ) > 0 ) {
			$booking_time_settings = isset( $settings['_bkap_time_settings'] ) ? $settings['_bkap_time_settings'] : array();

			// recurring days and lockout update.
			if ( is_array( $booking_time_settings ) && count( $booking_time_settings ) > 0 ) {

				foreach ( $booking_time_settings as $day => $s_data ) {

					if ( 'booking' !== substr( $day, 0, 7 ) ) { // specific dates.

						$date = gmdate( 'Y-m-d', strtotime( $day ) );

						foreach ( $s_data as $time_data ) {
							$insert            = true;
							$available_booking = $time_data['lockout_slot'];
							$updated_lockout   = $time_data['lockout_slot'];
							$from_time         = $time_data['from_slot_hrs'] . ':' . $time_data['from_slot_min'];
							$to_time           = $time_data['to_slot_hrs'] . ':' . $time_data['to_slot_min'];
							$to_time           = ( '0:00' === $to_time || '00:00' === $to_time ) ? '' : $to_time;

							// check if the date is already present.
							// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
							$check_date = $wpdb->get_results(
								$wpdb->prepare(
									'SELECT total_booking, available_booking FROM `' . $wpdb->prefix . "booking_history`
									WHERE post_id = %d
									AND weekday = ''
									AND start_date = %s
									AND from_time = %s
									AND to_time = %s
									AND status = ''",
									$product_id,
									$date,
									$from_time,
									$to_time
								)
							);

							// if yes, then update the lockout.
							if ( isset( $check_date ) && count( $check_date ) > 0 ) { // there will be only 1 active record at any given time
								$insert = false;
								if ( is_numeric( $time_data['lockout_slot'] ) && $time_data['lockout_slot'] > 0 ) {
									$change_in_lockout = $time_data['lockout_slot'] - $check_date[0]->total_booking;
								} elseif ( '' === $time_data['lockout_slot'] || 0 === $time_data['lockout_slot'] ) { // unlimited bookings
									$change_in_lockout = 0;
								}
							} else {
								// if not found, check if there's an inactive date record present.
								// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
								$lockout_results = $wpdb->get_results(
									$wpdb->prepare(
										'SELECT total_booking FROM `' . $wpdb->prefix . "booking_history`
										WHERE post_id = %d
										AND start_date = %s
										AND weekday = ''
										AND from_time = %s
										AND to_time = %s
										AND status <> ''",
										$product_id,
										$date,
										$from_time,
										$to_time
									)
								);

								if ( isset( $lockout_results ) && count( $lockout_results ) > 0 ) {
									$insert = false;
									if ( is_numeric( $time_data['lockout_slot'] ) && $time_data['lockout_slot'] > 0 ) {
										$change_in_lockout = $time_data['lockout_slot'] - $lockout_results[0]->total_booking;
									} elseif ( '' === $time_data['lockout_slot'] || 0 === $time_data['lockout_slot'] ) { // unlimited bookings
										$change_in_lockout = 0;
									}
								}
							}

							if ( $insert ) {
								// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
								$wpdb->query(
									$wpdb->prepare(
										"INSERT INTO {$wpdb->prefix}booking_history 
										(post_id, weekday, start_date, end_date, from_time, to_time, total_booking, available_booking) 
										VALUES (%d, %s, %s, %s, %s, %s, %d, %d)",
										$product_id,
										'',
										$date,
										'0000-00-00',
										$from_time,
										$to_time,
										$time_data['lockout_slot'],
										$available_booking
									)
								);
							} else {

								// Update the existing record so that lockout is managed and orders do not go missing frm the View bookings page.
								if ( 0 === $change_in_lockout && ( '' === $time_data['lockout_slot'] || 0 === $time_data['lockout_slot'] ) ) { // unlimited bookings.
									// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
									$wpdb->query(
										$wpdb->prepare(
											"UPDATE {$wpdb->prefix}booking_history
											SET total_booking = %d,
											available_booking = %d,
											status = ''
											WHERE post_id = %d
											AND start_date = %s
											AND from_time = %s
											AND to_time = %s",
											$time_data['lockout_slot'],
											$change_in_lockout,
											$product_id,
											$date,
											$from_time,
											$to_time
										)
									);
								} else {
									// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
									$wpdb->query(
										$wpdb->prepare(
											"UPDATE {$wpdb->prefix}booking_history
											SET total_booking = %d,
											available_booking = available_booking + %d,
											status = ''
											WHERE post_id = %d
											AND start_date = %s
											AND from_time = %s
											AND to_time = %s",
											$time_data['lockout_slot'],
											$change_in_lockout,
											$product_id,
											$date,
											$from_time,
											$to_time
										)
									);
								}
							}
						}

						continue;
					}

					// recurring weekdays.
					foreach ( $s_data as $time_data ) {
						$available_booking = $time_data['lockout_slot'];
						$updated_lockout   = $time_data['lockout_slot'];
						$from_time         = $time_data['from_slot_hrs'] . ':' . $time_data['from_slot_min'];
						$to_time           = $time_data['to_slot_hrs'] . ':' . $time_data['to_slot_min'];
						$to_time           = ( '0:00' === $to_time || '00:00' === $to_time ) ? '' : $to_time;
						$from_db           = gmdate( 'H:i', strtotime( $from_time ) );
						$to_db             = gmdate( 'H:i', strtotime( $to_time ) );

						// check if the weekday is already present.
						// Duplicate records were being inserted when openended timeslot becasue DATE_TIME of blank returns no records.
						// Hence in below if, we are not comparing with DATE_TIME function.

						if ( '' === $to_time ) {
							// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
							$check_weekday = $wpdb->get_results(
								$wpdb->prepare(
									'SELECT total_booking, available_booking FROM `' . $wpdb->prefix . "booking_history` WHERE post_id = %d AND weekday = %s AND start_date = '0000-00-00' AND TIME_FORMAT( from_time, %s ) = %s AND to_time = %s AND status = ''",
									$product_id,
									$day,
									'%H:%i',
									$from_db,
									$to_time
								)
							);
						} else {
							// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
							$check_weekday = $wpdb->get_results(
								$wpdb->prepare(
									'SELECT total_booking, available_booking FROM `' . $wpdb->prefix . "booking_history` WHERE post_id = %d AND weekday = %s AND start_date = '0000-00-00' AND TIME_FORMAT( from_time, %s ) = %s AND TIME_FORMAT( to_time, %s ) = %s AND status = ''",
									$product_id,
									$day,
									'%H:%i',
									$from_db,
									'%H:%i',
									$to_db
								)
							);
						}

						// if yes, then update the lockout
						if ( isset( $check_weekday ) && count( $check_weekday ) > 0 ) { // there will be only 1 active record at any given time
							if ( is_numeric( $time_data['lockout_slot'] ) && $time_data['lockout_slot'] > 0 ) {
								$change_in_lockout = $time_data['lockout_slot'] - $check_weekday[0]->total_booking;
							} elseif ( '' === $time_data['lockout_slot'] || 0 === $time_data['lockout_slot'] ) { // unlimited bookings
								$change_in_lockout = 0;
							}

							if ( isset( $change_in_lockout ) && is_numeric( $change_in_lockout ) ) {

								// Update the existing record so that lockout is managed and orders do not go missing frm the View bookings page.
								if ( 0 === $change_in_lockout && ( '' === $time_data['lockout_slot'] || 0 === $time_data['lockout_slot'] ) ) { // unlimited bookings.

									// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
									$wpdb->query(
										$wpdb->prepare(
											"UPDATE {$wpdb->prefix}booking_history
											SET total_booking = %d,
											available_booking = %d
											WHERE post_id = %d
											AND weekday = %s
											AND start_date = '0000-00-00'
											AND TIME_FORMAT(from_time, %s) = %s
											AND TIME_FORMAT(to_time, %s) = %s
											AND status = ''",
											$updated_lockout,
											$change_in_lockout,
											$product_id,
											$day,
											'%H:%i',
											$from_db,
											'%H:%i',
											$to_db
										)
									);

								} else {
									if ( '' === $to_time ) {
										// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
										$wpdb->query(
											$wpdb->prepare(
												"UPDATE {$wpdb->prefix}booking_history
												SET total_booking = %d,
												available_booking = available_booking + %d
												WHERE post_id = %d
												AND weekday = %s
												AND start_date = '0000-00-00'
												AND TIME_FORMAT(from_time, %s) = %s
												AND to_time = ''
												AND status = ''",
												$updated_lockout,
												$change_in_lockout,
												$product_id,
												$day,
												'%H:%i',
												$from_db
											)
										);
									} else {
										// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
										$wpdb->query(
											$wpdb->prepare(
												"UPDATE {$wpdb->prefix}booking_history
												SET total_booking = %d,
												available_booking = available_booking + %d
												WHERE post_id = %d
												AND weekday = %s
												AND start_date = '0000-00-00'
												AND TIME_FORMAT(from_time, %s) = %s
												AND TIME_FORMAT(to_time, %s) = %s
												AND status = ''",
												$updated_lockout,
												$change_in_lockout,
												$product_id,
												$day,
												'%H:%i',
												$from_db,
												'%H:%i',
												$to_db
											)
										);
									}
								}
							}
						} else { // if not found, check if there's a date record present.
							// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
							$lockout_results = $wpdb->get_results(
								$wpdb->prepare(
									'SELECT total_booking FROM `' . $wpdb->prefix . "booking_history`
									WHERE post_id = %d
									AND start_date != '0000-00-00'
									AND weekday = %s
									AND TIME_FORMAT( from_time, %s ) = %s
									AND TIME_FORMAT( to_time, %s ) = %s
									ORDER BY id DESC LIMIT 1",
									$product_id,
									$day,
									'%H:%i',
									$from_db,
									'%H:%i',
									$to_db
								)
							);

							if ( isset( $lockout_results ) && count( $lockout_results ) > 0 ) {
								if ( is_numeric( $time_data['lockout_slot'] ) && $time_data['lockout_slot'] > 0 ) {
									$change_in_lockout = $time_data['lockout_slot'] - $lockout_results[0]->total_booking;
									$available_booking = $lockout_results[0]->total_booking + $change_in_lockout;
								} elseif ( '' === $time_data['lockout_slot'] || 0 === $time_data['lockout_slot'] ) { // unlimited bookings.
									$change_in_lockout = 0;
									$available_booking = 0;
								}
							}

							// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
							$wpdb->query(
								$wpdb->prepare(
									"INSERT INTO {$wpdb->prefix}booking_history 
									(post_id, weekday, start_date, end_date, from_time, to_time, total_booking, available_booking) 
									VALUES (%d, %s, '0000-00-00', '0000-00-00', %s, %s, %d, %d)",
									$product_id,
									$day,
									$from_time,
									$to_time,
									$updated_lockout,
									$available_booking
								)
							);

							// if there are other time slots present for the weekday, add this slot for the date.
							// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
							$dates_set = $wpdb->get_col(
								$wpdb->prepare(
									'SELECT DISTINCT( start_date ) FROM `' . $wpdb->prefix . 'booking_history`
									WHERE start_date >= %s
									AND post_id = %d
									AND weekday = %s',
									gmdate( 'Y-m-d', current_time( 'timestamp' ) ),
									$product_id,
									$day
								)
							);

							if ( is_array( $dates_set ) && count( $dates_set ) > 0 ) {

								// build an array of dates that already have this slot present.
								// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
								$dates_present = $wpdb->get_col(
									$wpdb->prepare(
										'SELECT DISTINCT( start_date ) FROM `' . $wpdb->prefix . "booking_history`
										WHERE start_date >= %s
										AND post_id = %d
										AND weekday = %s
										AND TIME_FORMAT( from_time, %s ) = %s
										AND TIME_FORMAT( to_time, %s ) = %s",
										gmdate( 'Y-m-d', current_time( 'timestamp' ) ),
										$product_id,
										$day,
										'%H:%i',
										$from_db,
										'%H:%i',
										$to_db
									)
								);

								foreach ( $dates_set as $date ) {
									// In a scenario where a future date is locked out, as all the time slot bookings are full,
									// we need to run this insert to ensure the date is unblocked and bookings can be taken for the new slot.
									if ( ! in_array( $date, $dates_present, true ) ) {
										// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
										$wpdb->query(
											$wpdb->prepare(
												"INSERT INTO {$wpdb->prefix}booking_history 
												(post_id, weekday, start_date, end_date, from_time, to_time, total_booking, available_booking) 
												VALUES (%d, %s, %s, '0000-00-00', %s, %s, %d, %d)",
												$product_id,
												$day,
												$date,
												$from_time,
												$to_time,
												$updated_lockout,
												$available_booking
											)
										);
									}
								}
							}
						}

						if ( isset( $change_in_lockout ) && is_numeric( $change_in_lockout ) ) {

							// Update the existing records for the dates.
							if ( 0 === $change_in_lockout && ( '' === $time_data['lockout_slot'] || 0 === $time_data['lockout_slot'] ) ) { // unlimited.bookings

								// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
								$wpdb->query(
									$wpdb->prepare(
										"UPDATE {$wpdb->prefix}booking_history 
										SET total_booking = %d, available_booking = %d, status = '' 
										WHERE post_id = %d 
										AND weekday = %s 
										AND start_date <> '0000-00-00' 
										AND TIME_FORMAT(from_time, %s) = %s 
										AND TIME_FORMAT(to_time, %s) = %s 
										AND status = ''",
										$updated_lockout,
										$change_in_lockout,
										$product_id,
										$day,
										'%H:%i',
										$from_db,
										'%H:%i',
										$to_db
									)
								);
							} else {
								if ( '' === $to_time ) {
									// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
									$wpdb->query(
										$wpdb->prepare(
											"UPDATE {$wpdb->prefix}booking_history 
											SET total_booking = %d, available_booking = available_booking + %d, status = '' 
											WHERE post_id = %d 
											AND weekday = %s 
											AND start_date <> '0000-00-00' 
											AND TIME_FORMAT(from_time, %s) = %s 
											AND to_time = '' 
											AND status = ''",
											$updated_lockout,
											$change_in_lockout,
											$product_id,
											$day,
											'%H:%i',
											$from_db
										)
									);
								} else {
									// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
									$wpdb->query(
										$wpdb->prepare(
											"UPDATE {$wpdb->prefix}booking_history 
											SET total_booking = %d, available_booking = available_booking + %d, status = '' 
											WHERE post_id = %d 
											AND weekday = %s 
											AND start_date <> '0000-00-00' 
											AND TIME_FORMAT(from_time, %s) = %s 
											AND TIME_FORMAT(to_time, %s) = %s 
											AND status = ''",
											$updated_lockout,
											$change_in_lockout,
											$product_id,
											$day,
											'%H:%i',
											$from_db,
											'%H:%i',
											$to_db
										)
									);
								}
							}
						}
					}
				}
			}
		}
	}
}

/**
 * Updates a specific date record to inactive
 * status in booking history table for a given
 * date.
 *
 * @param int    $product_id - Product ID
 * @param string $date - j-n-Y Format
 * @global object $wpdb Global wpdb Object
 *
 * @since 4.0.0
 */

function bkap_delete_specific_date( $product_id, $date ) {

	global $wpdb;

	$specific_date = gmdate( 'Y-m-d', strtotime( $date ) );

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
	$wpdb->query(
		$wpdb->prepare(
			"UPDATE {$wpdb->prefix}booking_history 
			SET status = 'inactive' 
			WHERE post_id = %d 
			AND start_date = %s 
			AND weekday = '' 
			AND from_time = '' 
			AND to_time = ''",
			$product_id,
			$specific_date
		)
	);
}

/**
 * This function is used to call the strings defined for translation
 *
 * @since 1.7
 * @global array $book_translations Array of all the labels
 * @global string $book_lang language set to english
 * @return array $book_translations returns array of all the labels
 */
function bkap_get_book_t( $str ) {
	global $book_translations, $book_lang;
	return $book_translations[ $book_lang ][ $str ];
}

/**
 * This function calculate the maximum available date in the booking calendar
 *
 * @since 1.7
 * @hook bkap_max_date
 * @param string                      $m_d Minimum Date
 * @param int                         $max_dates Numbers of date to choose
 * @param $booking_set Booking Setting
 *
 * @return string $m_d Return Max date
 */
 function calback_bkap_max_date( $m_d, $max_dates, $booking_set ) {

	$next_date      = $m_d;
	$max_loop_count = apply_filters( 'bkap_max_date_loop_count', 1000, $m_d, $max_dates, $booking_set );

	$recurring = true;
	if ( isset( $booking_set['booking_recurring'] ) ) {
		foreach ( $booking_set['booking_recurring'] as $recur_key => $recur_value ) {
			if ( isset( $recur_value ) && $recur_value != 'on' ) {
				$recurring = false;
			} elseif ( isset( $recur_value ) && $recur_value == 'on' ) {
				$recurring = true;
				break;
			}
		}
	}

	if ( isset( $booking_set['booking_specific_date'] )
	&& is_array( $booking_set['booking_specific_date'] )
	&& count( $booking_set['booking_specific_date'] ) > 0 ) {
		$specific_dates = array_keys( $booking_set['booking_specific_date'] );
		$today_midnight = strtotime( 'today midnight' );
		foreach ( $specific_dates as $k => $v ) {
			if ( strtotime( $v ) < $today_midnight ) {
				unset( $specific_dates[ $k ] );
			}
		}
	}

	if ( ! $recurring && isset( $specific_dates ) ) {

		if ( count( $specific_dates ) > 0 ) {
			sort( $specific_dates );
			$next_date = $specific_dates[ 0 ];
		}
	}

	for ( $i = 0; $i < $max_loop_count; $i++ ) {

		$stt = '';
		$stt = gmdate( 'w', strtotime( $next_date ) );
		$stt = 'booking_weekday_' . $stt;

		if ( $max_dates >= 0 ) {

			if ( isset( $booking_set['booking_recurring'] ) && $booking_set['booking_recurring'][ $stt ] == 'on' ) {

				if ( isset( $booking_set['booking_date_range'] ) && count( $booking_set['booking_date_range'] ) > 0 ) {

					foreach ( $booking_set['booking_date_range'] as $range_value ) {
						if ( strtotime( $range_value['start'] ) < strtotime( $next_date ) && strtotime( $range_value['end'] ) > strtotime( $next_date ) ) {
							$m_d = $next_date;
							$max_dates--;
						}
					}
				} else {
					$m_d = $next_date;
					$max_dates--;
				}
			} elseif ( isset( $specific_dates ) && in_array( $next_date, $specific_dates ) ) {
				$m_d = $next_date;
				$max_dates--;
			}
			$next_date = addDayswithdate( $next_date, 1 );
		} else {
			break;
		}
	}

	return $m_d;
}

/**
 * Check if Booking is not locked out for a particular date
 *
 * @since 4.3.0
 * @param string|int $product_id Product ID
 * @param string     $start_date Start Date
 * @param string     $end_date End Date
 * @return bool True for available else false
 */
function bkap_check_booking_available( $product_id, $start_date, $end_date ) {

	$product_id                  = bkap_common::bkap_get_product_id( $product_id );
	$booking_settings            = get_post_meta( $product_id, 'woocommerce_booking_settings', true );
	$booking_type                = bkap_type( $product_id );
	$recurring_selected_weekdays = isset( $booking_settings['booking_recurring'] ) ? $booking_settings['booking_recurring'] : array();
	$booking_specific_booking    = isset( $booking_settings['booking_specific_booking'] ) ? $booking_settings['booking_specific_booking'] : '';
	$product_holidays            = get_post_meta( $product_id, '_bkap_product_holidays', true );
	$holiday_range               = get_post_meta( $product_id, '_bkap_holiday_ranges', true );
	$month_range                 = get_post_meta( $product_id, '_bkap_month_ranges', true );
	$current_time                = current_time( 'timestamp' );

	// Check if the product having resource and that any resource are available on selected date range or not.
	if ( isset( $booking_settings['_bkap_resource'] ) && $booking_settings['_bkap_resource'] == 'on' ) {
		$is_resource_available = bkap_check_resource_available( $product_id, $start_date, $end_date );

		if ( $is_resource_available ) {
			return false;
		}
	}

	// Check if the product is avaialble based on advance booking period or not.
	if ( $start_date === $end_date ) {
		$is_min_date_available = bkap_check_for_min_date( $product_id, $start_date, $current_time );

		if ( ! $is_min_date_available ) {
			return false;
		}
	}

	// Check if the date range having the weekdays enabled in the booking settings
	$return_value_recurring = check_in_range_weekdays( $start_date, $end_date, $recurring_selected_weekdays );
	if ( ! in_array( true, $return_value_recurring, true ) ) {
		if ( $booking_specific_booking != 'on' ) {
			return false;
		}
	}

	if ( is_array( $product_holidays ) ) {
		$product_holidays = array_keys( $product_holidays );
		$dates            = bkap_common::bkap_get_betweendays( $start_date, $end_date, 'j-n-Y' );
		foreach ( $dates as $date ) {
			if ( in_array( $date, $product_holidays ) ) {
				return false;
			}
		}
	}

	if ( is_array( $holiday_range ) && ! empty( $holiday_range ) ) {
		$return_value = bkap_check_in_range_months( $start_date, $end_date, $holiday_range[0]['start'], $holiday_range[0]['end'] );
		if ( in_array( true, $return_value, true ) ) {
			return false;
		}
	}

	// Check if the date range falls in month range or not.
	if ( is_array( $month_range ) && ! empty( $month_range ) ) {
		$return_value = bkap_check_in_range_months( $start_date, $end_date, $month_range[0]['start'], $month_range[0]['end'] );

		if ( ! in_array( true, $return_value, true ) ) {
			return false;
		}
	}

	// Check if the start date is less than the maximum date available for product or not.
	$is_in_max_range = bkap_check_for_max_date( $product_id, $booking_settings, $start_date, $current_time );
	if ( ! $is_in_max_range ) {
		return false;
	}

	switch ( $booking_type ) {
		case 'only_day':
			do {
				$availability_result = bkap_check_day_booking_available( $product_id, $start_date );
				$range_has_holiday   = bkap_check_holiday( $start_date, $start_date );

				if ( $availability_result && ! $range_has_holiday ) {
					return true;
				}
				$start_date = gmdate( 'Y-m-d', strtotime( $start_date . ' +1 day' ) );
			} while ( strtotime( $start_date ) <= strtotime( $end_date ) );

			return false;
			break;

		case 'multiple_days':
			$range_has_holiday = bkap_check_holiday( $start_date, $end_date );
			if ( $range_has_holiday ) {
				return false;
			}

			if ( isset( $booking_settings['booking_fixed_block_enable'] )
				&& $booking_settings['booking_fixed_block_enable'] === 'booking_fixed_block_enable'
			) {

				$block_max_days = 0;
				if ( isset( $booking_settings['bkap_fixed_blocks_data'] ) ) {
					foreach ( $booking_settings['bkap_fixed_blocks_data'] as $block_key => $block_value ) {
						if ( isset( $block_value['number_of_days'] ) && $block_value['number_of_days'] > $block_max_days ) {
							$block_max_days = $block_value['number_of_days'];
						}
					}
				}

				if ( $block_max_days > 0 ) {
					$end_date = gmdate( 'Y-m-d', strtotime( $end_date . " +$block_max_days day" ) );
				}
			}

			do {
				$availability_result = bkap_check_day_booking_available( $product_id, $start_date );
				if ( ! $availability_result ) {
					return false;
				}
				$start_date = gmdate( 'Y-m-d', strtotime( $start_date . ' +1 day' ) );
			} while ( strtotime( $start_date ) < strtotime( $end_date ) );

			return true;
			break;

		case 'date_time':
			do {
				$availability_result = bkap_check_day_booking_available( $product_id, $start_date );
				$range_has_holiday   = bkap_check_holiday( $start_date, $start_date );
				$day_has_timeslot    = bkap_common::bkap_check_timeslot_for_weekday( $product_id, $start_date, $booking_settings );

				if ( $availability_result && ! $range_has_holiday ) {
					$time_slots = explode( '|', bkap_booking_process::get_time_slot( $start_date, $product_id ) );

					if ( sanitize_key( $time_slots[0] ) !== 'error' &&
						( sanitize_key( $time_slots[0] ) !== '' && $day_has_timeslot ) ) {

						return true;
					}
				}
				$start_date = gmdate( 'Y-m-d', strtotime( $start_date . ' +1 day' ) );
			} while ( strtotime( $start_date ) <= strtotime( $end_date ) );
			return false;
			break;

		case 'duration_time':
			$range_has_holiday = bkap_check_holiday( $start_date, $start_date );
			if ( $range_has_holiday ) {
				return false;
			}
			return true;
			break;

		default:
			return false;
			break;
	}
}

/**
 * Check if min booking date is available for booking when compared to start date.
 * Return true if date available else return false
 *
 * @since 4.3.0
 * @param string|int $product_id Product ID
 * @param string     $start_date Start Date
 * @param string     $current_time Current WordPress Time
 * @return bool True if date available else return false
 */
function bkap_check_for_min_date( $product_id, $start_date, $current_time ) {

	$min_date = bkap_common::bkap_min_date_based_on_AdvanceBookingPeriod( $product_id, $current_time );
	if ( strtotime( $min_date ) > strtotime( $start_date ) ) {
		return false;
	} else {
		return true;
	}
}

/**
 * Check if resource is available on the given start and end date range or not.
 *
 * @since 4.8.0
 * @param string|int $product_id Product ID
 * @param string     $start_date Start Date
 * @param string     $end_date End Date
 *
 * @return boolean True if resource has lockout date for searched date range.
 */
function bkap_check_resource_available( $product_id, $start_date, $end_date ) {

	$date_range = bkap_array_of_given_date_range( $start_date, $end_date, 'j-n-Y' );
	$rstatus    = false;

	if ( ! empty( $_GET['select_res'] ) && $_GET['select_res'] != 0 ) {
		$resource_id = (int) $_GET['select_res'];
		$rstatus     = bkap_check_resource_booked_in_date_range( $product_id, $resource_id, $date_range );
	} else {
		$bkap_product_resources = get_post_meta( $product_id, '_bkap_product_resources', true );

		if ( '' != $bkap_product_resources && is_array( $bkap_product_resources ) ) {

			$resource_selection = Class_Bkap_Product_Resource::bkap_product_resource_selection( $product_id );
			if ( 'bkap_automatic_resource' === $resource_selection ) {
				$rstatus = bkap_check_resource_booked_in_date_range( $product_id, $bkap_product_resources[0], $date_range );
			} else {
				foreach ( $bkap_product_resources as $rkey => $rvalue ) {
					$rstatus = bkap_check_resource_booked_in_date_range( $product_id, $rvalue, $date_range );
				}
			}
		}
	}

	return $rstatus;
}

/**
 * Check if start date is out of the max date range (i.e. maximum number of dates to choose).
 * Return true if in range else return false
 *
 * @since 4.3.0
 * @param string|int $product_id Product ID
 * @param array      $booking_settings Booking Settings for the product to check
 * @param string     $start_date Start Date
 * @return bool true if not in range else return false
 */
function bkap_check_for_max_date( $product_id, $booking_settings, $start_date, $current_time ) {

	$numbers_of_days_to_choose = isset( $booking_settings['booking_maximum_number_days'] ) ? $booking_settings['booking_maximum_number_days'] - 1 : '';
	$custom_ranges             = isset( $booking_settings['booking_date_range'] ) ? $booking_settings['booking_date_range'] : array();

	$month_ranges = get_post_meta( $product_id, '_bkap_month_ranges', true );
	$min_date     = bkap_common::bkap_min_date_based_on_AdvanceBookingPeriod( $product_id, $current_time );

	if ( ( isset( $numbers_of_days_to_choose )
			&& '' != $numbers_of_days_to_choose
			&& empty( $custom_ranges )
			&& empty( $month_ranges ) )
		||
		 ( isset( $numbers_of_days_to_choose )
			&& 0 === $numbers_of_days_to_choose )
	 ) {

		if ( isset( $booking_settings['booking_recurring_booking'] )
			&& $booking_settings['booking_recurring_booking'] == 'on'
		) {

			$max_date = apply_filters( 'bkap_max_date', $min_date, $numbers_of_days_to_choose, $booking_settings );

			if ( strtotime( $max_date ) < strtotime( $start_date ) ) {
				return false;
			}
		}
	}

	return true;
}

/**
 * Check if bookings are available for that day for single day bookings
 *
 * @since 4.3.0
 * @param string|int $product_id Product ID
 * @param string     $start_date Start Date
 * @return bool True if booking available else false
 */
function bkap_check_day_booking_available( $product_id, $start_date ) {

	$result = get_bookings_for_date( $product_id, $start_date );
	$res    = get_availability_for_date( $product_id, $start_date, $result );

	if ( count( $res ) > 0 &&
		( $res['unlimited'] === 'YES' || ( $res['unlimited'] === 'NO' && $res['available'] > 0 ) ) ) {

		return true;
	}
	return false;
}

/**
 * Check if the date passed is a part of global holidays
 *
 * @since 4.3.0
 * @param string $start_date Date (start date from widget)
 * @param string $end_date Date (end date from widget)
 * @return bool true if part of global holiday else false
 */
function bkap_check_holiday( $start_date, $end_date ) {

	$global_settings = json_decode( get_option( 'woocommerce_booking_global_settings' ) );
	if ( isset( $global_settings->booking_include_global_holidays ) && $global_settings->booking_include_global_holidays == 'on' ) {
		return false;
	}
	$global_holidays      = array();
	$formatted_start_date = gmdate( 'j-n-Y', strtotime( $start_date ) );
	$formatted_end_date   = gmdate( 'j-n-Y', strtotime( $end_date ) );

	if ( isset( $global_settings->booking_global_holidays ) ) {
		$global_holidays = explode( ',', $global_settings->booking_global_holidays );
	}

	if ( in_array( $formatted_start_date, $global_holidays ) ) {
		return true;
	} elseif ( $formatted_end_date !== $formatted_start_date ) {
		while ( strtotime( $formatted_start_date ) < strtotime( $formatted_end_date ) ) {
			if ( in_array( $formatted_start_date, $global_holidays ) ) {
				return true;
			}
			$formatted_start_date = gmdate( 'j-n-Y', strtotime( $formatted_start_date . ' +1 day' ) );
		}
	}
	return false;
}

/**
 * Check in custom dates which are non-bookable
 *
 * @since 4.3.0
 * @param string $start_date Date (start date from widget)
 * @param string $end_date Date (end date from widget)
 * @param string $custom_start_date Date (start date from holiday range)
 * @param string $custom_end_date Date (end date from holiday range)
 * @return bool true if part of global holiday else false
 */
function bkap_check_in_custom_holiday_range( $start_date, $end_date, $custom_start_date, $custom_end_date ) {

	$start_ts            = strtotime( $start_date );
	$end_ts              = strtotime( $end_date );
	$new_custom_array    = array();
	$custom_return_value = array();

	while ( $start_ts <= $end_ts ) {
		$new_custom_array[] = $start_date;
		$start_ts           = strtotime( '+1 day', $start_ts );
		$start_date         = gmdate( 'j-n-Y', $start_ts );
	}

	foreach ( $new_custom_array as $key => $value ) {

		$custom_values = strtotime( $value );
		if ( $custom_values >= strtotime( $custom_start_date ) && $custom_values <= strtotime( $custom_end_date ) ) {
			$custom_return_value [ $value ] = true;
		} else {
			$custom_return_value [ $value ] = false;
		}
	}

	return $custom_return_value;
}

/**
 * This function will add days to the passed date and return the date
 *
 * @since 1.7
 * @param mixed $date It can be Date(string) or UNIXTIME(int)
 * @param int   $days Numbers of days to be added to the date
 *
 * @return string $m_d Return new date after the days added
 */
function addDayswithdate( $date, $days ) {

	if ( is_numeric( $date ) ) {
		$date = strtotime( '+' . $days . ' days', $date );
	} else {
		$date = strtotime( '+' . $days . ' days', strtotime( $date ) );
	}
	return gmdate( 'j-n-Y', $date );
}

/**
 * This function will check if the date is between give date range or not.
 *
 * @since 2.0
 * @param string $start_date Start Date
 * @param string $end_date End Date
 * @param string $date_from_user selected date by user on front end
 * @return true|false This will return true if user date is in between date range else false
 */
function check_in_range( $start_date, $end_date, $date_from_user ) {
	$start_ts = strtotime( $start_date );
	$end_ts   = strtotime( $end_date );
	$user_ts  = strtotime( $date_from_user );

	// Check that user date is between start & end
	return ( ( $user_ts >= $start_ts ) && ( $user_ts <= $end_ts ) );
}

/**
 * This function will return array of dates with true if date is current/future date and false if date is past date.
 *
 * @since 2.0
 * @param string $start_date Start Date
 * @param string $end_date End Date
 * @param string $date_from_user selected date by user on front end
 *
 * @return array $return_value This will return array of dates with true if date is current/future date and false if date is past date.
 */
function check_in_range_abp( $start_date, $end_date, $date_from_user ) {

	$start_ts          = strtotime( $start_date );
	$end_ts            = strtotime( $end_date );
	$user_ts           = strtotime( $date_from_user );
	$return_value      = array();
	$new_week_days_arr = array();

	while ( $start_ts <= $end_ts ) {
		$new_week_days_arr [] = $start_date;
		$start_ts             = strtotime( '+1 day', $start_ts );
		$start_date           = gmdate( 'j-n-Y', $start_ts );
	}

	foreach ( $new_week_days_arr as $weekday_key => $weekday_value ) {

		$week_day_value = strtotime( $weekday_value );

		if ( $week_day_value == $user_ts ) {
			$return_value [ $weekday_value ] = true;
		} elseif ( $week_day_value >= $user_ts ) {
			$return_value [ $weekday_value ] = true;
		} else {
			$return_value [ $weekday_value ] = false;
		}
	}
	return $return_value;
}

/**
 * This function will return date range with true false based on the enabled weekdays.
 *
 * @since 2.0
 * @param string $start_date Start Date
 * @param string $end_date End Date
 * @param string $recurring_selected_weekdays Weekday setting of the product
 *
 * @return array $return_value This will array of dates with true if date range having weekday enabled else date with false value.
 */
function check_in_range_weekdays( $start_date, $end_date, $recurring_selected_weekdays ) {

	$start_ts          = strtotime( $start_date );
	$end_ts            = strtotime( $end_date );
	$return_value      = array();
	$new_week_days_arr = array();

	while ( $start_ts <= $end_ts ) {

		if ( ! in_array( gmdate( 'w', $start_ts ), $new_week_days_arr ) ) {
			$new_week_days_arr [] = gmdate( 'w', $start_ts );
		} elseif ( ! in_array( gmdate( 'w', $end_ts ), $new_week_days_arr ) ) {
			$new_week_days_arr [] = gmdate( 'w', $end_ts );
		}
		$start_ts = strtotime( '+1 day', $start_ts );
	}

	foreach ( $recurring_selected_weekdays as $weekday_key => $weekday_value ) {

		$week_day_value = substr( $weekday_key, -1 );

		if ( $weekday_value == 'on' && in_array( $week_day_value, $new_week_days_arr ) ) {
			$return_value [] = true;
		} else {
			$return_value [] = false;
		}
	}
	return $return_value;
}

/**
 * This function will return date range with true false based on the added month range.
 *
 * @since 4.12.0
 * @param string $start_date Start Date
 * @param string $end_date End Date
 * @param string $custom_start_date date of the start month
 * @param string $custom_end_date date of the end month
 *
 * @return array $return_value This will array of dates with true if date range falling under month range else date with false value.
 */
function bkap_check_in_range_months( $start_date, $end_date, $custom_start_date, $custom_end_date ) {

	$start_ts            = strtotime( $start_date );
	$end_ts              = strtotime( $end_date );
	$new_custom_array    = array();
	$custom_return_value = array();

	while ( $start_ts <= $end_ts ) {
		$new_custom_array[] = $start_date;
		$start_ts           = strtotime( '+1 day', $start_ts );
		$start_date         = gmdate( 'Y-m-d', $start_ts );
	}
	foreach ( $new_custom_array as $key => $value ) {

		$custom_values = strtotime( $value );

		if ( $custom_values >= strtotime( $custom_start_date ) && $custom_values <= strtotime( $custom_end_date ) ) {
			$custom_return_value [ $value ] = true;
		} else {
			$custom_return_value [ $value ] = false;
		}
	}

	return $custom_return_value;
}

/**
 * This function will return date with true or false value based on the holidays date
 *
 * @since 2.0
 * @param string $start_date Start Date
 * @param string $end_date End Date
 * @param string $recurring_selected_weekdays Array of holiday dates
 *
 * @return array $return_value This will array of dates with true if date holiday else date with false value.
 */
function check_in_range_holidays( $start_date, $end_date, $recurring_selected_weekdays ) {

	$start_ts          = strtotime( $start_date );
	$end_ts            = strtotime( $end_date );
	$return_value      = array();
	$new_week_days_arr = array();

	while ( $start_ts <= $end_ts ) {

		$new_week_days_arr [] = $start_date;
		$start_ts             = strtotime( '+1 day', $start_ts );
		$start_date           = gmdate( 'j-n-Y', $start_ts );
	}

	foreach ( $new_week_days_arr as $weekday_key => $weekday_value ) {

		$week_day_value = strtotime( $weekday_value );

		if ( is_array( $recurring_selected_weekdays ) && in_array( $weekday_value, $recurring_selected_weekdays ) ) {
			$return_value [ $weekday_value ] = true;
		} else {

			$return_value [ $weekday_value ] = false;
		}
	}
	return $return_value;
}

/**
 * This function will return array of dates with true or false value based on the weekday of fixed block
 *
 * @since 2.0
 * @param string $start_date Start Date
 * @param string $end_date End Date
 * @param string $days Array of added days in all fixed blocks
 *
 * @return array $return_value Return array of dates with true or false value based on the weekday of fixed block
 */
function check_in_fixed_block_booking( $start_date, $end_date, $days ) {

	$start_ts          = strtotime( $start_date );
	$end_ts            = strtotime( $end_date );
	$return_value      = array();
	$new_week_days_arr = array();
	$weekdays_array    = array(
		'Sunday'    => '0',
		'Monday'    => '1',
		'Tuesday'   => '2',
		'Wednesday' => '3',
		'Thursday'  => '4',
		'Friday'    => '5',
		'Saturday'  => '6',
	);

	$flag      = false;
	$min_day   = gmdate( 'l', $start_ts );
	$min_value = $weekdays_array[ $min_day ];

	if ( in_array( $min_value, $days ) || in_array( 'any_days', $days ) ) {
		$flag = true;
	}

	if ( $flag ) {
		$return_value [ $start_date ] = true;
	} else {
		$return_value [ $start_date ] = false;
	}

	return $return_value;
}

/**
 * This function will return product name of the booked product.
 *
 * @param object $booking Booking Object.
 *
 * @return array $product_name Return name of booked product.
 */
function booked_product_name( $booking ) {

	$product = $booking->get_product();
	if ( $product ) {

		$product_name = $product->get_title();
		$resource_id  = $booking->get_resource();
		$variation_id = $booking->get_variation_id();
		if ( $variation_id > 0 ) {
			$variation_obj = wc_get_product( $variation_id );
			$product_name  = false != $variation_obj ? $variation_obj->get_name() : '-';
		}

		if ( $resource_id != '' ) {

			$show_resource = apply_filters( 'bkap_display_resource_info_on_view_booking', true, $product, $resource_id );

			if ( $show_resource ) {
				$resource_title = $booking->get_resource_title();
				$product_name  .= '<br>( ' . esc_html( $resource_title ) . ' )';
			}
		}
	} else {
		$product_name = '-';
	}

	return $product_name;
}

/**
 * This function will return remaining balance order link and payment status data on view bookings page.
 *
 * @param object $booking Booking Object.
 *
 * @return array $data Return remaining balance order link and payment status data.
 */
function bkap_partial_deposits_data_on_view_booking( $booking ) {

	$order_item_id           = $booking->custom_fields['_bkap_order_item_id'][0];
	$remaining_balance_order = '';
	$payment_status          = '';
	$totally_paid            = __( 'Totally Paid', 'woocommerce-booking' );
	$partially_paid          = __( 'Partially Paid', 'woocommerce-booking' );
	$balance_payment         = __( 'Awaiting Balance Payment', 'woocommerce-booking' );

	if ( isset( $order_item_id ) ) {
		$child_order_id = wc_get_order_item_meta( $order_item_id, '_bkap_remaining_balance_order_id' );

		if ( isset( $child_order_id ) && '' !== $child_order_id && false !== $child_order_id ) {

			$child_order = wc_get_order( $child_order_id );

			if ( 'pending' === $child_order->get_status() ) {
				$payment_status = $balance_payment;
			} else {
				$payment_status = $totally_paid;
			}
			$order_url               = bkap_order_url( $child_order_id );
			$remaining_balance_order = '<a href="' . $order_url . '">#' . $child_order_id . '</a>';
		} else {
			$order             = $booking->get_order();
			$product_id        = $booking->product_id;
			$booking_settings  = get_post_meta( $product_id, 'woocommerce_booking_settings', true );
			$deposit_remaining = wc_get_order_item_meta( $order_item_id, '_bkap_remaining' );

			if ( isset( $booking_settings['booking_partial_payment_radio'] ) && 'security_deposit' !== $booking_settings['booking_partial_payment_radio'] ) {

				if ( isset( $deposit_remaining ) && $deposit_remaining != 0 && '' != $deposit_remaining ) { // phpcs:ignore
					$payment_status = $partially_paid;
				} else {
					$payment_status = $totally_paid;
				}
			} else {
				$payment_status = $totally_paid;
			}
		}
	}

	$data['remaining_balance_order'] = $remaining_balance_order;
	$data['payment_status']          = $payment_status;

	return $data;
}

/**
 * Checks if two date ranges overlap.
 *
 * This function determines whether the date range defined by 
 * ($start1, $end1) overlaps with the date range defined by 
 * ($start2, $end2).
 *
 * @param string $start1 Start date of the first range.
 * @param string $end1   End date of the first range.
 * @param string $start2 Start date of the second range.
 * @param string $end2   End date of the second range.
 *
 * @return bool True if the date ranges overlap, false otherwise.
 */
function bkap_date_ranges_overlapping( $start1, $end1, $start2, $end2 ) {
	// Convert to timestamps for easy comparison.
	$start1 = strtotime( $start1 );
	$end1 = strtotime( $end1 );
	$start2 = strtotime( $start2 );
	$end2 = strtotime( $end2 );
		
	// Check for overlap.
	return ( $start1 <= $end2 ) && ( $start2 <= $end1 );
}
