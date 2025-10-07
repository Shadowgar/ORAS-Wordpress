<?php
/**
 * Bookings and Appointment Plugin for WooCommerce
 *
 * Generating ICS file of the booking on Order Receive page and Email notification
 *
 * @author      Tyche Softwares
 * @package     BKAP/ICS
 * @since       2.0
 * @category    Classes
 */

if ( ! class_exists( 'Bkap_Ics' ) ) {

	/**
	 * Class for Generating ICS file of the booking on Order Receive page and Email notification
	 *
	 * @class Bkap_Ics
	 */
	class Bkap_Ics {

		/**
		 * Default constructor
		 *
		 * @since 4.1.0
		 */
		public function __construct() {

			$global_settings = json_decode( get_option( 'woocommerce_booking_global_settings' ) );
			// Add order details as an attachment.
			if ( isset( $global_settings->booking_attachment ) && 'on' === $global_settings->booking_attachment ) {
				add_filter( 'woocommerce_email_attachments', array( 'bkap_ics', 'bkap_email_attachment' ), 10, 3 );
			}
		}

		/**
		 * This function attach the ICS file with the booking details in the email sent to user and admin.
		 *
		 * @since 1.7
		 * @param  array  $files Empty array.
		 * @param  object $email_id ID of email template.
		 * @param  object $order Order Object.
		 * @global object $wpdb Global wpdb object.
		 * @global object $woocommerce Global WooCommerce object.
		 *
		 * @return $file Returns the ICS file for the booking.
		 */
		public static function bkap_email_attachment( $files, $email_id, $order ) {

			global $wpdb;

			if ( ! isset( $order ) ) {
				return $files;
			}

			$order_id = 0;
			if ( is_int( $order ) ) {
				$order_obj = wc_get_order( $order );
				$order_id  = $order_obj ? $order_obj->get_id() : 0;
			} elseif ( is_object( $order ) && ( 'WC_Order' === get_class( $order ) || 'Automattic\WooCommerce\Admin\Overrides\Order' === get_class( $order ) ) ) {
				$order_id  = $order->get_id();
				$order_obj = $order;
			}

			if ( 0 !== $order_id ) {

				$order_items     = $order_obj->get_items();
				$today_date      = gmdate( 'Y-m-d' );
				$global_settings = bkap_global_setting();
				$timezone_check  = bkap_timezone_check( $global_settings );

				$file_path = bkap_temporary_directory();
				$file_name = get_option( 'book_ics-file-name', 'My_ICS' );
				$c         = count( $files );

				foreach ( $order_items as $item_key => $item_value ) {

					$booking_ids = bkap_common::get_booking_id( $item_key );

					if ( false !== $booking_ids ) {
						$bookings = array();
						if ( is_array( $booking_ids ) ) {
							$bookings = $booking_ids;
						} elseif ( '' !== $booking_ids ) {
							$bookings[] = (int) $booking_ids;
						}

						if ( ! empty( $bookings ) ) {

							$bkap_calendar_sync = bkap_google_calendar_sync();
							$app                = $bkap_calendar_sync->bkap_create_gcal_obj( $item_key, $item_value, $order_obj );
							$site_timezone      = bkap_booking_get_timezone_string();

							foreach ( $bookings as $booking_id ) {
								$timezone   = $site_timezone;
								$start_date = get_post_meta( $booking_id, '_bkap_start', true );
								$end_date   = get_post_meta( $booking_id, '_bkap_end', true );

								$start_date_stime = strtotime( $start_date );
								$today_date_stime = strtotime( $today_date );
								if ( $start_date_stime < $today_date_stime ) {
									continue;
								}

								$start_date = bkap_convert_date_from_timezone_to_timezone( $start_date, $site_timezone, 'UTC', 'Ymd\THis\Z' );
								$end_date   = bkap_convert_date_from_timezone_to_timezone( $end_date, $site_timezone, 'UTC', 'Ymd\THis\Z' );

								$booked_product                      = array();
								$booked_product['start_timestamp']   = $start_date;
								$booked_product['end_timestamp']     = $end_date;
								$booked_product['current_timestamp'] = gmdate( 'Ymd\THis\Z', current_time( 'timestamp' ) ); //phpcs:ignore
								$booked_product['name']              = $item_value['name'];
								$booked_product['summary']           = str_replace(
									array( 'SITE_NAME', 'CLIENT', 'PRODUCT_NAME', 'PRODUCT_WITH_QTY', 'ORDER_DATE_TIME', 'ORDER_DATE', 'ORDER_NUMBER', 'PRICE', 'PHONE', 'NOTE', 'ADDRESS', 'EMAIL', 'RESOURCE', 'PERSONS', 'ZOOM_MEETING' ),
									array( get_bloginfo( 'name' ), $app->client_name, $app->product, $app->product_with_qty, $app->order_date_time, $app->order_date, $app->id, $app->order_total, $app->client_phone, $app->order_note, $app->client_address, $app->client_email, $app->resource, $app->persons, $app->zoom_meeting ),
									get_option( 'bkap_calendar_event_description' )
								);

								$files[ $c ] = $file_path . '/' . $file_name . '_' . $c . '.ics';
								$current     = self::bkap_ics_booking_details_email( $booked_product );
								file_put_contents( $files[ $c ], $current ); // phpcs:ignore
								$c++; // phpcs:ignore
							}
						}
					} else {
						continue;
					}
				}
			}

			return $files;
		}

		/**
		 * This function create the string required to create the ICS file with the booking details.
		 *
		 * @since 1.7
		 * @param array $booked_product Array of booking details and product name.
		 *
		 * @return string $icsString Returns the string of the ICS file for the booking
		 */
		public static function bkap_ics_booking_details_email( $booked_product ) {

			$description = $booked_product['summary'];
			$description = str_replace( '\x0D', '', $description ); // lf - html break.
			$description = preg_replace( "/\r|\n/", '', $description );

			$ics_string = 'BEGIN:VCALENDAR
PRODID:-//Events Calendar//iCal4j 1.0//EN
VERSION:2.0
CALSCALE:GREGORIAN
BEGIN:VEVENT
DTSTART:' . $booked_product['start_timestamp'] . '
DTEND:' . $booked_product['end_timestamp'] . '
DTSTAMP:' . $booked_product['current_timestamp'] . '
UID:' . ( uniqid() ) . '
DESCRIPTION:' . $description . '
SUMMARY:' . $booked_product['name'] . '
END:VEVENT
END:VCALENDAR';

			return $ics_string;
		}
	}
}
