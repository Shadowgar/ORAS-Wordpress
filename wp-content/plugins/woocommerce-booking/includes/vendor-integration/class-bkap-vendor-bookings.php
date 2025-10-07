<?php

/**
 * Bookings and Appointment Plugin for WooCommerce
 *
 * Class for View Bookings
 *
 * @author   Tyche Softwares
 * @package  BKAP/View-Bookings
 * @category Classes
 */

if ( ! class_exists( 'BKAP_Vendor_Bookings' ) ) {

	/**
	 * Class for View Bookings
	 *
	 * @since 4.1.0
	 */
	class BKAP_Vendor_Bookings {

		/**
		 * Download the CSV of the bookings.
		 *
		 * @param array $report array of bookings based on filter.
		 *
		 * @since 4.1.0
		 */
		public static function bkap_download_csv_file( $report ) {

			$csv = self::generate_csv( $report );

			header( 'Content-type: application/x-msdownload' );
			header( 'Content-Disposition: attachment; filename= ' . apply_filters( 'bkap_csv_file_name', 'Booking-Data-' . gmdate( 'Y-m-d', current_time( 'timestamp' ) ) . '.csv' ) );
			header( 'Pragma: no-cache' );
			header( 'Expires: 0' );
			echo "\xEF\xBB\xBF";
			echo $csv; // phpcs:ignore
			exit;
		}

		/**
		 * Print of the bookings.
		 *
		 * @param array $report array of bookings based on filter.
		 *
		 * @since 4.1.0
		 */
		public static function bkap_download_print_file( $report, $table = false, $col_data = false, $row_data = false ) {

			$global_settings = bkap_global_setting();
			$cols            = self::bkap_get_csv_cols();

			$print_data_columns = '<tr>';
			foreach ( $cols as $col ) {
				$print_data_columns .= '<th style="border:1px solid black;padding:5px;">' . $col . '</th>';
			}
			$print_data_columns .= '</tr>';
			$print_data_columns = apply_filters( 'bkap_view_bookings_print_columns', $print_data_columns );

			if ( $col_data ) {
				return $print_data_columns;
			}

			$print_data_row_data = '';
			$currency            = get_woocommerce_currency();
			$phpversion          = ( version_compare( WOOCOMMERCE_VERSION, '3.0.0' ) < 0 );
			foreach ( $report as $key => $booking ) {

				$booking_id   = $booking->id;
				$order_id     = $booking->order_id;
				$status       = self::status_data( $booking ); // Status.
				$product_name = self::product_name_data( $booking ); // Booked Product.
				$booked_by    = self::customer_name_data( $booking ); // Booked By.
				$start_date   = self::start_date_data( $booking, $global_settings ); // Start Date.
				$end_date     = self::end_date_data( $booking, $global_settings ); // End Date.
				$order_date   = $booking->get_date_created(); // Order Date.
				$quantity     = $booking->get_quantity();
				$persons      = $booking->get_persons_info();
				$final_amt    = self::final_amount_data( $booking, $quantity, $currency, $phpversion );
				$meeting_link = $booking->get_zoom_meeting_link();

				$data = array(
					'booking_id'   => $booking_id,
					'order_id'     => $order_id,
					'status'       => $status,
					'product_name' => $product_name,
					'booked_by'    => $booked_by,
					'start_date'   => $start_date,
					'end_date'     => $end_date,
					'order_date'   => $order_date,
					'quantity'     => $quantity,
					'persons'      => $persons,
					'final_amt'    => $final_amt,
					'meeting_link' => $meeting_link,
				);

				$print_data_row_data_td = '';
				if ( isset( $cols['status'] ) ) {
					$print_data_row_data_td .= '<td style="border:1px solid black;padding:5px;">' . $status . '</td>';
				}
				if ( isset( $cols['id'] ) ) {
					$print_data_row_data_td .= '<td style="border:1px solid black;padding:5px;">' . $booking->id . '</td>';
				}
				if ( isset( $cols['booked_product'] ) ) {
					$print_data_row_data_td .= '<td style="border:1px solid black;padding:5px;">' . $product_name . '</td>';
				}
				if ( isset( $cols['booked_by'] ) ) {
					$print_data_row_data_td .= '<td style="border:1px solid black;padding:5px;">' . $booked_by . '</td>';
				}
				if ( isset( $cols['order_id'] ) ) {
					$print_data_row_data_td .= '<td style="border:1px solid black;padding:5px;">' . $booking->order_id . '</td>';
				}
				if ( isset( $cols['start_date'] ) ) {
					$print_data_row_data_td .= '<td style="border:1px solid black;padding:5px;">' . $start_date . '</td>';
				}
				if ( isset( $cols['end_date'] ) ) {
					$print_data_row_data_td .= '<td style="border:1px solid black;padding:5px;">' . $end_date . '</td>';
				}
				if ( isset( $cols['persons'] ) ) {
					$print_data_row_data_td .= '<td style="border:1px solid black;padding:5px;">' . $persons . '</td>';
				}
				if ( isset( $cols['quantity'] ) ) {
					$print_data_row_data_td .= '<td style="border:1px solid black;padding:5px;">' . $quantity . '</td>';
				}
				if ( isset( $cols['order_date'] ) ) {
					$print_data_row_data_td .= '<td style="border:1px solid black;padding:5px;">' . $order_date . '</td>';
				}
				if ( isset( $cols['amount'] ) ) {
					$print_data_row_data_td .= '<td style="border:1px solid black;padding:5px;">' . $final_amt . '</td>';
				}
				if ( isset( $cols['zoom_meeting'] ) ) {
					$print_data_row_data_td .= '<td style="border:1px solid black;padding:5px;"><small>' . $meeting_link . '</small></td>';
				}
				$print_data_row_data_td = apply_filters( 'bkap_view_bookings_print_individual_row_data', $print_data_row_data_td, $booking, $booking_id, $data );

				$print_data_row_data .= '<tr>';
				$print_data_row_data .= $print_data_row_data_td;
				$print_data_row_data  = apply_filters( 'bkap_view_bookings_print_individual_row', $print_data_row_data, $booking, $booking_id );
				$print_data_row_data .= '</tr>';
			}

			$print_data_row_data = apply_filters( 'bkap_view_bookings_print_rows', $print_data_row_data, $report );
			if ( $row_data ) {
				return $print_data_row_data;
			}

			$print_data_title = apply_filters( 'bkap_view_bookings_print_title', __( 'Print Bookings', 'woocommerce-booking' ) );

			if ( $table ) {
				$print_data = "<table id='bkap_print_data' style='border:1px solid black;border-collapse:collapse;'>" . $print_data_columns . $print_data_row_data . '</table>';
				return $print_data; // phpcs:ignore
			} else {
				$print_data = '<html><head><title>' . $print_data_title . "</title><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\"></head><body><table style='border:1px solid black;border-collapse:collapse;'>" . $print_data_columns . $print_data_row_data . '</table></body></html>';
				echo $print_data; // phpcs:ignore
				exit;
			}
		}

		/**
		 * Booking status.
		 *
		 * @param obj $booking Booking Object.
		 *
		 * @since 4.1.0
		 */
		public static function status_data( $booking ) {
			$status = bkap_common::get_mapped_status( $booking->get_status() );
			return $status;
		}

		/**
		 * Booked Product Name.
		 *
		 * @param obj $booking Booking Object.
		 *
		 * @since 4.1.0
		 */
		public static function product_name_data( $booking ) {
			$product      = $booking->get_product();
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
		 * Customer Name of Booking.
		 *
		 * @param obj $booking Booking Object.
		 *
		 * @since 4.1.0
		 */
		public static function customer_name_data( $booking ) {
			$customer = $booking->get_customer();
			return apply_filters( 'bkap_customer_name_on_view_booking', $customer->name, $customer, $booking );
		}

		/**
		 * Booking Start Date.
		 *
		 * @param obj $booking Booking Object.
		 *
		 * @since 4.1.0
		 */
		public static function start_date_data( $booking, $global_settings ) {
			$start_date     = $booking->get_start_date( $global_settings );
			$get_start_time = $booking->get_start_time( $global_settings );
			if ( '' !== $get_start_time ) {
				$start_date .= ' - ' . $get_start_time;
			}

			return $start_date;
		}

		/**
		 * Booking End Date.
		 *
		 * @param obj $booking Booking Object.
		 *
		 * @since 4.1.0
		 */
		public static function end_date_data( $booking, $global_settings ) {
			$end_date     = '';
			$get_end_date = $booking->get_end_date( $global_settings );
			if ( '' !== $get_end_date ) {
				$end_date     = $get_end_date;
				$get_end_time = $booking->get_end_time( $global_settings );

				if ( '' !== $get_end_time ) {
					$end_date .= ' - ' . $get_end_time;
				}
			}

			return $end_date;
		}

		/**
		 * Booking Amount.
		 *
		 * @param obj    $booking Booking Object.
		 * @param int    $quantity Booking Quantity.
		 * @param string $currency Currency Symbol.
		 * @param bool   $phpversion PHP Version.
		 *
		 * @since 4.1.0
		 */
		public static function final_amount_data( $booking, $quantity, $currency, $phpversion ) {
			// Amount.
			$amount    = $booking->get_cost();
			$final_amt = (float) $amount * (int) $quantity;

			if ( absint( $booking->order_id ) > 0 ) {
				$order = wc_get_order( $booking->order_id );

				if ( $order ) {
					$currency = ( $phpversion ) ? $order->get_order_currency() : $order->get_currency();
				}
			}

			$final_amt = wc_price( $final_amt, array( 'currency' => $currency ) );

			return $final_amt;
		}

		/**
		 * Generate string for CSV of booking.
		 *
		 * @param array $data array of booking information.
		 *
		 * @since 4.1.0
		 */
		public static function generate_csv( $data, $column = true ) {

			$global_settings = bkap_global_setting();

			$csv  = '';
			$cols = self::bkap_get_csv_cols();
			if ( $column ) {
				foreach ( $cols as $col ) {
					$csv .= $col . ',';
				}
				$csv  = substr( $csv, 0, -1 );
				$csv  = apply_filters( 'bkap_bookings_csv_columns_data', $csv );
				$csv .= "\n";
			}

			$currency   = get_woocommerce_currency();
			$phpversion = ( version_compare( WOOCOMMERCE_VERSION, '3.0.0' ) < 0 );

			foreach ( $data as $key => $booking ) {

				$booking_id   = $booking->id; // ID.
				$order_id     = $booking->order_id; // Order ID.
				$status       = self::status_data( $booking ); // Status.
				$product_name = self::product_name_data( $booking ); // Booked Product.
				$product_name = str_replace( '<br>', ' - ', $product_name );
				$booked_by    = self::customer_name_data( $booking ); // Booked By.
				$start_date   = self::start_date_data( $booking, $global_settings ); // Start Date.
				$end_date     = self::end_date_data( $booking, $global_settings ); // End Date.
				$order_date   = $booking->get_date_created(); // Order Date.
				$persons      = $booking->get_persons_info();
				$quantity     = $booking->get_quantity();
				$final_amt    = self::final_amount_data( $booking, $quantity, $currency, $phpversion );
				$final_amt    = wp_strip_all_tags( html_entity_decode( $final_amt, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8' ) );
				$meeting_link = $booking->get_zoom_meeting_link();

				$data = array(
					'booking_id'   => $booking_id,
					'order_id'     => $order_id,
					'status'       => $status,
					'product_name' => $product_name,
					'booked_by'    => $booked_by,
					'start_date'   => $start_date,
					'end_date'     => $end_date,
					'order_date'   => $order_date,
					'persons'      => $persons,
					'quantity'     => $quantity,
					'final_amt'    => $final_amt,
					'meeting_link' => $meeting_link,
				);

				// Create the data row.
				$row = '';
				if ( isset( $cols['status'] ) ) {
					$row .= $status . ',';
				}
				if ( isset( $cols['id'] ) ) {
					$row .= $booking_id . ',';
				}
				if ( isset( $cols['booked_product'] ) ) {
					$row .= '"' . $product_name . '",';
				}
				if ( isset( $cols['booked_by'] ) ) {
					$row .= $booked_by . ',';
				}
				if ( isset( $cols['order_id'] ) ) {
					$row .= $order_id . ',';
				}
				if ( isset( $cols['start_date'] ) ) {
					$row .= '"' . $start_date . '",';
				}
				if ( isset( $cols['end_date'] ) ) {
					$row .= '"' . $end_date . '",';
				}
				if ( isset( $cols['persons'] ) ) {
					$row .= '"' . $persons . '",';
				}
				if ( isset( $cols['quantity'] ) ) {
					$row .= $quantity . ',';
				}
				if ( isset( $cols['order_date'] ) ) {
					$row .= $order_date . ',';
				}
				if ( isset( $cols['amount'] ) ) {
					$row .= '"' . $final_amt . '",';
				}
				if ( isset( $cols['zoom_meeting'] ) ) {
					$row .= $meeting_link;
				}
				$row = apply_filters( 'bkap_bookings_csv_individual_row_data', $row, $booking, $booking_id, $data );

				$csv .= $row;

				$csv  = apply_filters( 'bkap_bookings_csv_individual_data', $csv, $booking, $booking_id, $data, $row );
				$csv .= "\n";
			}
			$csv = apply_filters( 'bkap_bookings_csv_data', $csv, $data );
			return $csv;
		}

		/**
		 * This function will write the Booking Column Data to CSV File.
		 *
		 * @param string $file Path of CSV File.
		 *
		 * @since 5.2.1
		 */
		public function bkap_print_csv_cols( $file ) {

			$cols      = self::bkap_get_csv_cols();
			$col_data  = implode( ',', $cols );
			$col_data .= "\r\n";

			self::bkap_stash_step_data( $file, $col_data );

			return $col_data;
		}

		/**
		 * Function will return the Columns of Booking Data..
		 *
		 * @since 5.2.1
		 */
		public static function bkap_get_csv_cols() {
			$cols = array(
				'status'         => __( 'Status', 'woocommerce-booking' ),
				'id'             => __( 'ID', 'woocommerce-booking' ),
				'booked_product' => __( 'Booked Product', 'woocommerce-booking' ),
				'booked_by'      => __( 'Booked By', 'woocommerce-booking' ),
				'order_id'       => __( 'Order ID', 'woocommerce-booking' ),
				'start_date'     => __( 'Start Date', 'woocommerce-booking' ),
				'end_date'       => __( 'End Date', 'woocommerce-booking' ),
				'persons'        => __( 'Persons', 'woocommerce-booking' ),
				'quantity'       => __( 'Quantity', 'woocommerce-booking' ),
				'order_date'     => __( 'Order Date', 'woocommerce-booking' ),
				'amount'         => __( 'Amount', 'woocommerce-booking' ),
				'zoom_meeting'   => __( 'Zoom Meeting', 'woocommerce-booking' ),
			);

			$user_id = get_current_user_id();
			if ( ! empty( $user_id ) ) {
				$h_cols = get_user_meta( $user_id, 'manageedit-bkap_bookingcolumnshidden', true );
				if ( ! empty( $h_cols ) ) {
					foreach ( $h_cols as $column ) {
						switch ( $column ) {
							case 'bkap_status':
								unset( $cols['status'] );
								break;
							case 'bkap_id':
								unset( $cols['id'] );
								break;
							case 'bkap_product':
								unset( $cols['booked_product'] );
								break;
							case 'bkap_customer':
								unset( $cols['booked_by'] );
								break;
							case 'bkap_order':
								unset( $cols['order_id'] );
								break;
							case 'bkap_start_date':
								unset( $cols['start_date'] );
								break;
							case 'bkap_end_date':
								unset( $cols['end_date'] );
								break;
							case 'bkap_persons':
								unset( $cols['persons'] );
								break;
							case 'bkap_qty':
								unset( $cols['quantity'] );
								break;
							case 'bkap_amt':
								unset( $cols['amount'] );
								break;
							case 'bkap_order_date':
								unset( $cols['order_date'] );
								break;
							case 'bkap_zoom_meeting':
								unset( $cols['zoom_meeting'] );
								break;
						}
					}
				}
			}

			return apply_filters( 'bkap_bookings_csv_columns', $cols );
		}

		/**
		 * Search for customers.
		 */
		public static function bkap_view_bookings_json_search_customers() {
			global $wpdb;

			ob_start();

			check_ajax_referer( 'search-customers', 'security' );

			if ( ! current_user_can( 'edit_shop_orders' ) ) {
				wp_die( -1 );
			}

			$term  = isset( $_GET['term'] ) ? (string) sanitize_text_field( wp_unslash( $_GET['term'] ) ) : '';
			$limit = 0;

			if ( empty( $term ) ) {
				wp_die();
			}

			$ids = array();

			// Search by Customer ID if search string is numeric.
			if ( is_numeric( $term ) ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
				$fetch = $wpdb->get_col(
					$wpdb->prepare(
						"SELECT `{$wpdb->prefix}wc_customer_lookup`.customer_id FROM `{$wpdb->prefix}wc_customer_lookup` WHERE `{$wpdb->prefix}wc_customer_lookup`.customer_id = %s",
						$term
					)
				);

				if ( count( $fetch ) > 0 ) {
					$ids = $fetch;
				}
			}

			// Usernames can be numeric so we first check that no users was found by ID before searching for numeric username, this prevents performance issues with ID lookups.
			if ( empty( $ids ) ) {
				$limit = '';

				// If search is smaller than 3 characters, limit result set to avoid
				// too many rows being returned.
				if ( 3 > strlen( $term ) ) {
					$limit = ' LIMIT 20';
				}

				// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
				$ids = $wpdb->get_col(
					$wpdb->prepare(
						"SELECT `{$wpdb->prefix}wc_customer_lookup`.customer_id FROM `{$wpdb->prefix}wc_customer_lookup` WHERE (`{$wpdb->prefix}wc_customer_lookup`.first_name LIKE %s OR `{$wpdb->prefix}wc_customer_lookup`.last_name LIKE %s) {$limit}",
						'%' . $term . '%',
						'%' . $term . '%'
					)
				);
				// phpcs:enable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
			}

			$found_customers = array();

			if ( ! empty( $_GET['exclude'] ) ) {
				$ids = array_diff( $ids, array_map( 'absint', (array) wp_unslash( $_GET['exclude'] ) ) );
			}

			foreach ( $ids as $id ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
				$customer = $wpdb->get_row(
					$wpdb->prepare(
						"SELECT `{$wpdb->prefix}wc_customer_lookup`.first_name, `{$wpdb->prefix}wc_customer_lookup`.last_name FROM `{$wpdb->prefix}wc_customer_lookup` WHERE `{$wpdb->prefix}wc_customer_lookup`.customer_id = %d",
						$id
					)
				);
				$found_customers[ $id ] = $customer->first_name . ' ' . $customer->last_name;
			}

			wp_send_json( $found_customers );
		}
	}
}
return new BKAP_Vendor_Bookings();
