<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * REST API for View Bookings.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/API/ViewBookings
 * @category    Classes
 * @since       5.19.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * BKAP REST API.
 *
 * @since 5.19.0
 */
class BKAP_Admin_API_View_Bookings extends BKAP_Admin_API {

	/**
	 * Construct
	 *
	 * @since 5.19.0
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_endpoints' ) );
		add_action( 'init', array( __CLASS__, 'bkap_download_csv' ) );
	}

	/**
	 * Function for registering the API endpoints.
	 *
	 * @since 5.19.0
	 */
	public static function register_endpoints() {

		// Fetch View Bookings data.
		register_rest_route(
			self::$base_endpoint,
			'view-bookings/fetch',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'fetch_global_settings_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		register_rest_route(
			self::$base_endpoint,
			'view-bookings/table/display',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'return_table_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		register_rest_route(
			self::$base_endpoint,
			'view-bookings/print-csv/export',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'print_csv_export' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Search for Customer.
		register_rest_route(
			self::$base_endpoint,
			'view-bookings/search-customer',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'search_customer' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Confirm Booking.
		register_rest_route(
			self::$base_endpoint,
			'view-bookings/confirm-booking',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'confirm_booking' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Delete Booking.
		register_rest_route(
			self::$base_endpoint,
			'view-bookings/delete-booking',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'delete_booking' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Cancel Booking.
		register_rest_route(
			self::$base_endpoint,
			'view-bookings/cancel-booking',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'cancel_booking' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Fetch Date HTML.
		register_rest_route(
			self::$base_endpoint,
			'view-bookings/date-html',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'get_date_html' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Update Booking.
		register_rest_route(
			self::$base_endpoint,
			'view-bookings/update-booking',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'update_booking' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Add bookings to google calendar from view bookings page.
		register_rest_route(
			self::$base_endpoint,
			'view-bookings/add-to-google-calendar',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'bkap_add_bookings_to_google_calendar' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Save Screen options on View Bookigns page.
		register_rest_route(
			self::$base_endpoint,
			'view-bookings/save-screen-options',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'bkap_save_screen_options' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);
	}

	/**
	 * Returns View Bookings Data.
	 *
	 * @param bool $return_raw Whether to return the Raw response.
	 *
	 * @since 5.19.0
	 */
	public static function fetch_view_bookings_data( $return_raw = false ) {

		$reminders    = bkap_common::bkap_get_email_reminders();
		$reminders[0] = __( 'Manual Reminder', 'woocommerce-booking' );

		$response = array(
			'booking_statuses' => bkap_common::get_bkap_booking_statuses(),
			'bkap_reminders'   => $reminders,
			'bkap_reminder'    => 0,
			'label'            => array(
				'save_booking' => __( 'Update Booking', 'woocommerce-booking' ),
			),
		);

		$user_id             = get_current_user_id();
		$user                = new WP_User( $user_id );
		$display_atgc_button = 'tour_operator' === $user->roles[0] ? esc_attr( get_the_author_meta( 'tours_add_to_calendar_view_booking', $user_id ) ) : get_option( 'bkap_admin_add_to_calendar_view_booking' );
		$gcal                = new BKAP_Google_Calendar();

		if ( in_array( $gcal->get_api_mode( $user_id ), array( 'directly', 'oauth' ), true ) && 'on' === $display_atgc_button ) {

			$total_bookings_to_export                                       = bkap_common::bkap_get_total_bookings_to_export( $user_id );
			$response['add_to_google_calendar']                             = array();
			$response['add_to_google_calendar']['total_bookings_to_export'] = count( $total_bookings_to_export );
			$response['add_to_google_calendar']['user_id']                  = $user_id;
			/* Translators: %s Booking ID */
			$response['add_to_google_calendar']['total_bookings_to_export_msg'] = sprintf( __( '%s bookings have been exported to your Google Calendar. Please refresh your Google Calendar.', 'woocommerce-booking' ), $response['add_to_google_calendar']['total_bookings_to_export'] );
			$response['add_to_google_calendar']['no_bookings_to_export']        = __( 'No pending bookings left to be exported.', 'woocommerce-booking' );
		}

		$hidden_columns = get_user_meta( $user_id, 'manageedit-bkap_bookingcolumnshidden', true );
		$hidden_columns = is_array( $hidden_columns ) ? $hidden_columns : array();
		$no_of_items    = get_user_meta( $user_id, 'edit_bkap_booking_per_page', true );
		$no_of_items    = '' === $no_of_items ? 20 : $no_of_items;

		$response['view_booking_hidden_columns'] = $hidden_columns;
		$response['view_booking_items']          = $no_of_items;
		$response['user_id']                     = $user_id;

		return self::return_response( $response, $return_raw );
	}

	/**
	 * Returns Table Data.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function return_table_data( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$data  = $request->get_param( 'data' );
		$table = self::populate_data(
			array(
				'order'   => self::check( $data, 'order', 'desc' ),
				'orderby' => self::check( $data, 'orderby', 'ID' ),
				'page'    => self::check( $data, 'page', 1 ),
				'search'  => self::check( $data, 'search', '' ),
				'filter'  => self::check( $data, 'filter', array() ),
				'status'  => self::check( $data, 'status', 'all' ),
			)
		);

		if ( ! $table ) {
			return self::response( 'error', array( 'error_description' => __( 'Error encountered while trying to populate booking data.', 'woocommerce-booking' ) ) );
		}

		return self::response( 'success', $table->ajax_response() );
	}

	/**
	 * Populate Data.
	 *
	 * @param bool $data Data.
	 *
	 * @since 5.19.0
	 */
	public static function populate_data( $data ) {

		// Load WordPress Administration APIs.
		require_once ABSPATH . 'wp-admin/includes/admin.php';

		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/includes/view-bookings/class-bkap-admin-view-bookings-table.php' );

		if ( is_array( $data ) && count( $data ) > 0 ) {
			$table = new BKAP_Admin_View_Bookings_Table();
			$table->populate_data( $data );

			return $table;
		}

		return false;
	}

	/**
	 * Search Customer Data.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function search_customer( WP_REST_Request $request ) {

		global $wpdb;

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$search_term = $request->get_param( 'search_term' );
		$limit       = 0;
		$ids         = array();

		// Search by Customer ID if search string is numeric.
		if ( is_numeric( $search_term ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
			$fetch = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT `{$wpdb->prefix}wc_customer_lookup`.customer_id FROM `{$wpdb->prefix}wc_customer_lookup` WHERE `{$wpdb->prefix}wc_customer_lookup`.customer_id = %s",
					$search_term
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
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
			$ids = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT `{$wpdb->prefix}wc_customer_lookup`.customer_id FROM `{$wpdb->prefix}wc_customer_lookup` WHERE (`{$wpdb->prefix}wc_customer_lookup`.first_name LIKE %s OR `{$wpdb->prefix}wc_customer_lookup`.last_name LIKE %s) {$limit}", // phpcs:ignore
					'%' . $search_term . '%',
					'%' . $search_term . '%'
				)
			);
		}

		$found_customers = array();

		foreach ( $ids as $id ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
			$customer = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM `{$wpdb->prefix}wc_customer_lookup` WHERE `{$wpdb->prefix}wc_customer_lookup`.customer_id = %d",
					$id
				)
			);

			$data              = new stdClass();
			$data->value       = $id;
			$data->label       = $customer->first_name . ' ' . $customer->last_name . ' - (' . $customer->email . ')';
			$found_customers[] = $data;
		}

		return self::response( 'success', array( 'data' => $found_customers ) );
	}

	/**
	 * Confirm Booking.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function confirm_booking( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$booking_ids = $request->get_param( 'booking_id' );

		if ( ! is_array( $booking_ids ) ) {
			$booking_ids = array( $booking_ids );
		}

		foreach ( $booking_ids as $booking_id ) {
			$booking = new BKAP_Booking( $booking_id );
			$item_id = $booking->get_item_id();

			BKAP_Booking_Confirmation::bkap_save_booking_status( $item_id, 'confirmed', $booking_id );
		}

		$message = 1 === count( $booking_ids ) ? __( 'Booking has been confirmed successfully.', 'woocommerce-booking' ) : __( 'Bookings have been confirmed successfully.', 'woocommerce-booking' );

		return self::response( 'success', array( 'message' => $message ) );
	}

	/**
	 * Cancel Booking.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function cancel_booking( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$booking_ids = $request->get_param( 'booking_id' );

		if ( ! is_array( $booking_ids ) ) {
			$booking_ids = array( $booking_ids );
		}

		foreach ( $booking_ids as $booking_id ) {
			$booking = new BKAP_Booking( $booking_id );
			$item_id = $booking->get_item_id();

			BKAP_Booking_Confirmation::bkap_save_booking_status( $item_id, 'cancelled', $booking_id );
		}

		$message = 1 === count( $booking_ids ) ? __( 'Booking has been cancelled successfully.', 'woocommerce-booking' ) : __( 'Bookings have been cancelled successfully.', 'woocommerce-booking' );

		return self::response( 'success', array( 'message' => $message ) );
	}

	/**
	 * Delete Booking.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function delete_booking( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$booking_ids  = $request->get_param( 'booking_id' );
		$force_delete = $request->get_param( 'force_delete' );

		if ( ! is_array( $booking_ids ) ) {
			$booking_ids = array( $booking_ids );
		}

		foreach ( $booking_ids as $booking_id ) {
			bkap_cancel_order::bkap_delete_booking( $booking_id );
			if ( $force_delete ) {
				wp_delete_post( $booking_id, true ); // Remove Booking Post.
			} else {
				wp_trash_post( $booking_id ); // Move Booking Post to Trash.
			}
		}

		$message = 1 === count( $booking_ids ) ? __( 'Booking has been deleted successfully.', 'woocommerce-booking' ) : __( 'Bookings have been deleted successfully.', 'woocommerce-booking' );

		return self::response( 'success', array( 'message' => $message ) );
	}

	/**
	 * Print/CSV Export.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function print_csv_export( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$data = $request->get_param( 'data' );

		if ( is_array( $data ) && count( $data ) > 0 ) {

			global $wp_filesystem;

			if ( ! function_exists( 'WP_Filesystem' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}

			if ( ! $wp_filesystem ) {
				WP_Filesystem();
			}

			$step            = (int) self::check( $data, 'step', 1 );
			$total_items     = (int) self::check( $data, 'total_items', 0 );
			$done_items      = (int) self::check( $data, 'done_items', 0 );
			$export_type     = self::check( $data, 'export_type', '' );
			$post_status     = self::check( $data, 'post_status', '' );
			$is_csv_export   = 'csv' === $export_type;
			$is_print_export = 'print' === $export_type;
			$upload_dir      = wp_upload_dir();
			$csv_file        = trailingslashit( $upload_dir['basedir'] ) . 'bkap-csv.csv';

			if ( ! $is_csv_export && ! $is_print_export ) {
				return self::response( 'error', array( 'error_description' => __( 'Unknown Error', 'woocommerce-booking' ) ) );
			}

			if ( $is_csv_export ) {
				if ( ! $wp_filesystem->is_writable( $upload_dir['basedir'] ) ) {
					return self::response( 'error', array( 'error_description' => __( 'Export location or file not writable', 'woocommerce-booking' ) ) );
				}
			}

			$options = array_merge(
				self::check( $data, 'options', array() ),
				array(
					'limit'  => 10,
					'paged'  => (int) $step,
					'status' => 'all' === $post_status ? array( 'confirmed', 'paid', 'pending-confirmation', 'cancelled' ) : $post_status,
				)
			);

			$table        = self::populate_data( $options );
			$booking_data = $table->booking_data;

			if ( 0 === count( $booking_data ) ) {

				if ( 1 === $step ) {
					return self::response( 'error', array( 'error_description' => __( 'Export has failed because no booking data was found for the export parameters provided.', 'woocommerce-booking' ) ) );
				}

				// All booking data have been extracted. Allow downloading of file.
				if ( $is_csv_export ) {

					return self::response(
						'success',
						array(
							'step'    => 'done',
							'url'     => add_query_arg(
								array(
									'step'        => $step,
									'nonce'       => wp_create_nonce( 'bkap-batch-export-csv' ),
									'bkap_action' => 'bkap_download_csv',
								),
								admin_url()
							),
							'message' => __( 'CSV Data has been generated. Please see your CSV file in your downloads folder.', 'woocommerce-booking' ),
						)
					);
				}

				if ( $is_print_export ) {
					return self::response(
						'success',
						array(
							'step'    => 'done',
							'message' => __( 'Print Data has been generated.', 'woocommerce-booking' ),
						)
					);
				}
			}

			if ( count( $booking_data ) > 0 ) {

				$return_data = array(
					'done_items' => $done_items + count( $booking_data ),
					'percentage' => round( ( ( $done_items + count( $booking_data ) ) / $total_items ) * 100 ),
					'step'       => $step + 1,
				);

				if ( $is_csv_export ) {

					self::generate_csv_or_print_data(
						$booking_data,
						array(
							'export_type' => 'csv',
							'csv_file'    => $csv_file,
							'step'        => $step,
						)
					);
				}

				if ( $is_print_export ) {
					$return_data['html'] = self::generate_csv_or_print_data(
						$booking_data,
						array(
							'export_type'  => 'print',
							'return_rows'  => 1 !== $step ? 'yes' : '',
							'return_table' => 1 === $step ? 'yes' : '',
							'step'         => $step,
						)
					);
				}

				return self::response(
					'success',
					$return_data
				);
			}
		}

		return self::response( 'error', array( 'error_description' => __( 'Unknown Error', 'woocommerce-booking' ) ) );
	}

	/**
	 * Generate string data for CSV or Print.
	 *
	 * @param array $data Array of booking information.
	 * @param array $options Array of options.
	 *
	 * @since 4.1.0
	 */
	public static function generate_csv_or_print_data( $data, $options ) {
		$global_settings = bkap_global_setting();
		$is_csv_export   = 'csv' === self::check( $options, 'export_type', '' );
		$is_print_export = 'print' === self::check( $options, 'export_type', '' );
		$step            = (int) self::check( $options, 'step', 0 );
		$csv_file        = self::check( $options, 'csv_file', '' );
		$print_class     = 'style="border:1px solid black;padding:5px;"';

		$columns = self::bkap_get_csv_cols();

		$csv_data = '';
		if ( $is_csv_export && 1 === $step ) {

			@unlink( $csv_file ); // Make sure we start with a fresh file on step 1.
			$csv_data  = apply_filters( 'bkap_bookings_csv_columns_data', implode( ',', $columns ) );
			$csv_data .= "\n";
		}

		$print = '';
		if ( $is_print_export && 1 === $step ) {

			$header = '';

			foreach ( $columns as $col ) {
				$header .= '<th ' . $print_class . '>' . $col . '</th>';
			}

			$header = apply_filters( 'bkap_view_bookings_print_columns', $header );
			$print  = '<tr>' . $header . '</tr>';
		}

		foreach ( $data as $key => $booking ) {
			$booking_id   = $booking['booking_id'];
			$order_id     = $booking['order_id'];
			$status       = bkap_common::get_mapped_status( $booking['status'] );
			$product_name = $booking['product_name'];
			$product_name = str_replace( '<br>', ' - ', $product_name );
			$booked_by    = apply_filters( 'bkap_customer_name_on_view_booking', $booking['customer_obj']->name, $booking['customer_obj'], $booking );
			$start_date   = $booking['_start_date'];
			$end_date     = $booking['_end_date'];
			$order_date   = $booking['order_date'];
			$persons      = $booking['persons_info'];
			$quantity     = $booking['quantity'];
			$final_amt    = wp_strip_all_tags( html_entity_decode( $booking['final_amount'], ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8' ) );
			$meeting_link = $booking['zoom_meeting'];

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

			// CSV.
			$csv_row = '';

			if ( isset( $columns['status'] ) ) {
				$csv_row .= $status . ',';
			}
			if ( isset( $columns['id'] ) ) {
				$csv_row .= $booking_id . ',';
			}
			if ( isset( $columns['booked_product'] ) ) {
				$csv_row .= '"' . $product_name . '",';
			}
			if ( isset( $columns['booked_by'] ) ) {
				$csv_row .= $booked_by . ',';
			}
			if ( isset( $columns['order_id'] ) ) {
				$csv_row .= $order_id . ',';
			}
			if ( isset( $columns['start_date'] ) ) {
				$csv_row .= '"' . $start_date . '",';
			}
			if ( isset( $columns['end_date'] ) ) {
				$csv_row .= '"' . $end_date . '",';
			}
			if ( isset( $columns['persons'] ) ) {
				$csv_row .= '"' . $persons . '",';
			}
			if ( isset( $columns['quantity'] ) ) {
				$csv_row .= $quantity . ',';
			}
			if ( isset( $columns['order_date'] ) ) {
				$csv_row .= $order_date . ',';
			}
			if ( isset( $columns['amount'] ) ) {
				$csv_row .= '"' . $final_amt . '",';
			}
			if ( isset( $columns['zoom_meeting'] ) ) {
				$csv_row .= $meeting_link;
			}

			$csv_row   = apply_filters( 'bkap_bookings_csv_individual_row_data', $csv_row, $booking, $booking_id, $data );
			$csv_data .= $csv_row;
			$csv_data  = apply_filters( 'bkap_bookings_csv_individual_data', $csv_data, $booking, $booking_id, $data, $csv_row );
			$csv_data .= "\n";

			// Print.
			$print_row = '';
			if ( isset( $columns['status'] ) ) {
				$print_row  .= '<td ' . $print_class . '>' . $status . '</td>';
			}

			if ( isset( $columns['id'] ) ) {
				$print_row .= '<td ' . $print_class . '>' . $booking_id . '</td>';
			}

			if ( isset( $columns['booked_product'] ) ) {
				$print_row .= '<td ' . $print_class . '>' . $product_name . '</td>';
			}

			if ( isset( $columns['booked_by'] ) ) {
				$print_row .= '<td ' . $print_class . '>' . $booked_by . '</td>';
			}

			if ( isset( $columns['order_id'] ) ) {
				$print_row .= '<td ' . $print_class . '>' . $order_id . '</td>';
			}

			if ( isset( $columns['start_date'] ) ) {
				$print_row .= '<td ' . $print_class . '>' . $start_date . '</td>';
			}

			if ( isset( $columns['end_date'] ) ) {
				$print_row .= '<td ' . $print_class . '>' . $end_date . '</td>';
			}

			if ( isset( $columns['persons'] ) ) {
				$print_row .= '<td ' . $print_class . '>' . $persons . '</td>';
			}

			if ( isset( $columns['quantity'] ) ) {
				$print_row .= '<td ' . $print_class . '>' . $quantity . '</td>';
			}

			if ( isset( $columns['order_date'] ) ) {
				$print_row .= '<td ' . $print_class . '>' . $order_date . '</td>';
			}

			if ( isset( $columns['amount'] ) ) {
				$print_row .= '<td ' . $print_class . '>' . $final_amt . '</td>';
			}

			if ( isset( $columns['zoom_meeting'] ) ) {
				$print_row .= '<td ' . $print_class . '><small>' . $meeting_link . '</small></td>';
			}

			$print_row  = apply_filters( 'bkap_view_bookings_print_individual_row_data', $print_row, $booking, $booking_id, $data );

			$print .= '<tr>' . $print_row;
			$print  = apply_filters( 'bkap_view_bookings_print_individual_row', $print, $booking, $booking_id );
			$print .= '</tr>';
		}

		$print = apply_filters( 'bkap_view_bookings_print_rows', $print, $booking );
		$table = "<table id='bkap_print_data' style='border:1px solid black;border-collapse:collapse;'>" . $print . '</table>';

		if ( $is_csv_export ) {
			@file_put_contents( $csv_file, self::get_csv_file( $csv_file ) . apply_filters( 'bkap_bookings_csv_data', $csv_data, $data ) );
		}

		if ( $is_print_export ) {
			if ( 'yes' === self::check( $options, 'return_rows', '' ) ) {
				return $print;
			}

			if ( 'yes' === self::check( $options, 'return_table', '' ) ) {
				return $table;
			}

			return '<html><head><title>' . apply_filters( 'bkap_view_bookings_print_title', __( 'Print Bookings', 'woocommerce-booking' ) ) . '</title><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head><body>' . $table . '</body></html>';
		}
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
						case 'bkap_booked_product':
							unset( $cols['booked_product'] );
							break;
						case 'bkap_booked_by':
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
						case 'bkap_quantity':
							unset( $cols['quantity'] );
							break;
						case 'bkap_amount':
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
	 * Function to create CSV file OR get its content.
	 *
	 * @param string $file Path of the CSV file.
	 * @since 5.2.1
	 */
	public static function get_csv_file( $file ) {
		global $wp_filesystem;

		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		if ( ! $wp_filesystem ) {
			WP_Filesystem();
		}

		// Ensure the file exists.
		if ( $wp_filesystem->exists( $file ) ) {
			return ! $wp_filesystem->is_writable( $file ) ? 'X' : $wp_filesystem->get_contents( $file );
		}

		// Create the file with empty content.
		$wp_filesystem->put_contents( $file, '', FS_CHMOD_FILE );

		return '';
	}

	/**
	 * Function to download the CSV for Booking.
	 *
	 * @since 5.2.1
	 */
	public static function bkap_download_csv() {

		if ( isset( $_GET['bkap_action'] ) && 'bkap_download_csv' === $_GET['bkap_action'] ) {

			if ( isset( $_REQUEST['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'bkap-batch-export-csv' ) ) { // nosemgraep: scanner.php.wp.security.csrf.nonce-flawed-logic
				wp_die( esc_html( __( 'Nonce verification failed', 'woocommerce-booking' ) ), esc_html( __( 'Error', 'woocommerce-booking' ) ), array( 'response' => 403 ) );
			}

			$upload_dir = wp_upload_dir();
			$file       = trailingslashit( $upload_dir['basedir'] ) . 'bkap-csv.csv';

			header( 'Content-Type: text/csv; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename=' . apply_filters( 'bkap_csv_file_name', 'Booking-Data-' . gmdate( 'Y-m-d', current_time( 'timestamp' ) ) . '.csv' ) );
			header( 'Expires: 0' );
			echo "\xEF\xBB\xBF";
			readfile( $file ); // phpcs:ignore
			unlink( $file ); // phpcs:ignore
			die();
		}
	}

	/**
	 * Add past bookigns to the google calendar from view bookings page.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function bkap_add_bookings_to_google_calendar( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$user_id = $request->get_param( 'user_id' );

		$result = bkap_google_calendar_sync()->bkap_admin_booking_calendar_events( array( 'user_id' => $user_id ) );

		return self::response( 'success', array( 'message' => $result['total_bookings_to_exported_msg'] ) );
	}

	/**
	 * Add past bookigns to the google calendar from view bookings page.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function bkap_save_screen_options( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$user_id                     = $request->get_param( 'user_id' );
		$view_booking_hidden_columns = $request->get_param( 'view_booking_hidden_columns' );
		$view_booking_items          = $request->get_param( 'view_booking_items' );

		if ( '' !== $view_booking_items ) {
			update_user_meta( $user_id, 'edit_bkap_booking_per_page', $view_booking_items );
		}

		update_user_meta( $user_id, 'manageedit-bkap_bookingcolumnshidden', $view_booking_hidden_columns );

		return self::response( 'success', array( 'message' => __( 'Screen options are saved successfully.', 'woocommerce-booking' ) ) );
	}

	/**
	 * Get Date HTML.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function get_date_html( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		ob_start();

		$booking_id        = $request->get_param( 'booking_id' );
		$booking           = new BKAP_Booking( $booking_id );
		$order             = $booking->get_order();
		$order_id          = absint( ( is_callable( array( $order, 'get_id' ) ) ? $order->get_id() : 0 ) );
		$product_id        = $booking->get_product_id();
		$global_settings   = bkap_global_setting();
		$booking_type      = bkap_type( $product_id );
		$customer_id       = $booking->get_customer_id();
		$product           = $booking->get_product();
		$customer          = $booking->get_customer();
		$resource          = $booking->get_resource();
		$persons           = $booking->get_persons();
		$comment           = $booking->get_additional_comment();
		$statuses          = bkap_common::get_bkap_booking_statuses();
		$bookable_products = array( '' => __( 'N/A', 'woocommerce-booking' ) );
		$quantity          = $booking->get_quantity();
		$quantity          = ! is_numeric( $quantity ) || $quantity < 1 ? 1 : $booking->get_quantity();
		$variation_id      = $booking->get_variation_id();
		$booking_date      = bkap_date_as_format( $booking->get_start(), 'Y-m-d' );
		$times_selected    = explode( '-', $booking->get_time() );
		$time_format       = bkap_common::bkap_get_time_format( $global_settings );
		$date_format       = bkap_common::bkap_get_date_format( $global_settings );
		$time_display      = bkap_date_as_format( trim( $times_selected[0] ), $time_format );
		$get_start_time    = '';
		$get_end_time      = '';

		if ( isset( $times_selected[1] ) && '' !== trim( $times_selected[1] ) ) {
			$time_display .= ' - ' . gmdate( $time_format, strtotime( trim( $times_selected[1] ) ) );
		}

		if ( $booking->get_start() && strtotime( $booking->get_start() ) > strtotime( '+ 2 year', current_time( 'timestamp' ) ) ) {
			echo '<div class="updated highlight"><p>' . esc_html__( 'This booking is scheduled over 2 years into the future. Please ensure this is correct.', 'woocommerce-booking' ) . '</p></div>';
		}

		if ( 'date_time' !== $booking_type ) { // dont run for time bookings as open ended time slots have a lower end time as compared to the start.
			if ( $booking->get_start() && $booking->get_end() && strtotime( $booking->get_start() ) > strtotime( $booking->get_end() ) ) {
				echo '<div class="error"><p>' . esc_html__( 'This booking has an end date set before the start date.', 'woocommerce-booking' ) . '</p></div>';
			}
		}

		if ( $booking->get_product_id() && ! wc_get_product( $booking->get_product_id() ) ) {
			echo '<div class="error"><p>' . esc_html__( 'It appears the booking product associated with this booking has been removed.', 'woocommerce-booking' ) . '</p></div>';
		}

		// check if update errors exist.
		$update_errors = get_post_meta( $booking_id, '_bkap_update_errors', true );
		if ( is_array( $update_errors ) && count( $update_errors ) > 0 ) {
			foreach ( $update_errors as $msg ) {
				echo '<div class="error"><p>' . __( $msg, 'woocommerce-booking' ) . '</p></div>'; //phpcs:ignore
			}

			delete_post_meta( $post->ID, '_bkap_update_errors' );
		}

		$hidden_date     = bkap_date_as_format( $booking_date, 'j-n-Y' );
		$hidden_checkout = '';
		$past_checkout   = 'NO';

		if ( 'multiple_days' === $booking_type ) {
			$hidden_checkout = bkap_date_as_format( $booking->get_end(), 'j-n-Y' );
			$past_checkout   = ( strtotime( $hidden_checkout ) < current_time( 'timestamp' ) ) ? 'YES' : 'NO';
		}

		// Displaying Booking Information on the Edit Booking post page.
		$start_date_label   = bkap_option( 'start_date' );
		$end_date_label     = bkap_option( 'end_date' );
		$time_label         = bkap_option( 'time' );
		$display_start_date = '';
		$display_end_date   = '';
		$get_start_date     = $booking->get_start_date();
		$display_start_date = $get_start_date;
		$get_start_time     = '' !== $booking->get_start_time() ? $booking->get_start_time() : '';
		$display_start_date = '' !== $get_start_time ? $display_start_date . ' - ' . $get_start_time : $display_start_date;
		$get_end_date       = $booking->get_end_date();
		$display_end_date   = $get_end_date;
		$get_end_time       = '' !== $booking->get_end_time() ? $booking->get_end_time() : '';
		$display_end_date   = '' !== $get_end_time ? $display_end_date . ' - ' . $get_end_time : $display_end_date;

		if ( $display_start_date === $display_end_date ) {
			$display_end_date = '';
		}

		// Timezone Calculation.
		$timezone_name = $booking->get_timezone_name();
		$asclientstr   = __( ' (As per customer Timezone)', 'woocommerce-booking' );

		$t_start_date = '';
		$t_end_date   = '';
		if ( '' !== $timezone_name ) {
			$bkap_offset = bkap_get_offset( $booking->get_timezone_offset() );
			date_default_timezone_set( bkap_booking_get_timezone_string() ); //phpcs:ignore
			$t_start_date = gmdate( $date_format . ' ' . $time_format, $bkap_offset + strtotime( $booking->get_start() ) );
			$t_end_date   = gmdate( $date_format . ' ' . $time_format, $bkap_offset + strtotime( $booking->get_end() ) );
			date_default_timezone_set( 'UTC' );  //phpcs:ignore
		}

		$meeting_link = $booking->get_zoom_meeting_link();
		if ( '' !== $meeting_link ) {
			$zoom_enabled  = true;
			$product_id    = $booking->get_product_id();
			$meeting_label = bkap_zoom_join_meeting_label( $product_id );
			$meeting_text  = bkap_zoom_join_meeting_text( $product_id );

			$zoom_data = array(
				'meeting_label' => $meeting_label,
				'meeting_link'  => $meeting_link,
				'meeting_text'  => $meeting_text,
			);

		} else {
			$product_id    = $booking->get_product_id();
			$zoom_enabled  = bkap_zoom_meeting_enable( $product_id );
			$meeting_label = bkap_zoom_join_meeting_label( $product_id );
			$meeting_text  = bkap_zoom_join_meeting_text( $product_id );

			if ( $zoom_enabled ) {
				$zoom_data = array(
					'meeting_label' => $meeting_label,
					'meeting_text'  => $meeting_text,
					'meeting_link'  => '',
					'new_zoom_link' => '',
				);
			}
		}

		$duplicate_of = bkap_common::bkap_get_product_id( $product_id );

		echo '<div class="edit-page-booking-form">';

		$product_type = $product->get_type();
		$item_id      = $booking->get_item_id();

		if ( 'variable' === $product_type ) {

			echo '<input type="hidden" name="variation_id" class="variation_id" value="' . esc_attr( $variation_id ) . '" />';
			$attributes = get_post_meta( $duplicate_of, '_product_attributes', true );

			if ( is_array( $attributes ) && count( $attributes ) > 0 ) {
				foreach ( $attributes as $a_name => $a_details ) {
					$attr_value = htmlspecialchars( wc_get_order_item_meta( $item_id, $a_name ), ENT_QUOTES );
					print( "<input type='hidden' name='attribute_$a_name' value='$attr_value' />" ); //phpcs:ignore
				}
			}
		}

		$_product         = wc_get_product( $product_id );
		$vue_data         = bkap_booking_process::bkap_localize_process_script( $product_id, true, true );
		$hidden_data      = $vue_data['hidden_dates_array'];
		$booking_settings = get_post_meta( $duplicate_of, 'woocommerce_booking_settings', true );

		// Add Resource Information to booking settings.
		$resource_ids = wc_get_order_item_meta( $item_id, '_resource_id' );

		if ( '' !== $resource_ids ) {
			if ( ! is_array( $resource_ids ) ) {
				$temp         = $resource_ids;
				$resource_ids = array( $temp );
			}

			$booking_settings['extra_params']['resource_id'] = $resource_ids;
		}

		// Add parameters for some further actions in AJAX requests.
		$_POST['is_bkap_booking_page'] = true;

		wc_get_template(
			'bookings/bkap-bookings-box.php',
			array(
				'product_id'       => $duplicate_of,
				'product_obj'      => $_product,
				'booking_settings' => $booking_settings,
				'global_settings'  => $global_settings,
				'hidden_dates'     => $hidden_data,
			),
			'woocommerce-booking/',
			BKAP_BOOKINGS_TEMPLATE_PATH
		);

		echo '<br><span id="bkap_price" class="price"></span>';
		echo '</div>';

		$html = ob_get_clean();

		$product_name = '';

		if ( $variation_id > 0 ) {
			$variation_obj = wc_get_product( $variation_id );
			$product_name  = $variation_obj->get_name();
		} else {
			$product_name = $product->get_name();
		}

		$blocks_enabled         = get_post_meta( $product_id, '_bkap_fixed_blocks', true );
		$block_value            = $booking->get_fixed_block();
		$selected_duration      = '';
		$selected_duration_time = '';

		if ( 'duration_time' === $booking_type ) {
			$selected_duration      = $booking->get_selected_duration();
			$selected_duration_time = $booking->get_selected_duration_time();
		}

		$date_created = '';
		if ( $order ) {
			$date_created = date_i18n( wc_date_format(), strtotime( is_callable( array( $order, 'get_date_created' ) ) ? $order->get_date_created() : $order->post_date ) ) . ' @ ' . date_i18n( 'H', strtotime( $booking->get_date_created() ) ) . ':' . date_i18n( 'i', strtotime( $booking->get_date_created() ) );
		} else {
			$booking_date_strtotime = strtotime( $booking->booking_date );
			$date_created           = date_i18n( wc_date_format(), $booking_date_strtotime ) . ' @ ' . date_i18n( 'H', $booking_date_strtotime ) . ':' . date_i18n( 'i', $booking_date_strtotime );
		}

		return self::response(
			'success',
			array(
				'data'     => array(
					'booking_text'     => __( 'Edit Booking', 'woocommerce-booking' ),
					'booking_type'     => $booking_type,
					'booking_id'       => $booking_id,
					'order_url'        => $order ? bkap_order_url( $order->get_id() ) : '',
					'order_id'         => $order ? $order->get_id() : '',
					'order_number'     => $order ? $order->get_order_number() : '',
					'date_created'     => $date_created,
					'booking_status'   => $booking->get_status(),
					'timezone'         => '' === $timezone_name ? array() : array(
						'name'             => $timezone_name,
						'start_date_label' => $start_date_label,
						'start_date'       => $t_start_date,
						'end_date_label'   => $end_date_label,
						'end_date'         => $t_end_date,
					),
					'zoom_meeting'     => $zoom_enabled ? $zoom_data : array(),
					'product_id'       => $product_id,
					'product_name'     => $product_name,
					'start_date_label' => $start_date_label,
					'start_date'       => $display_start_date,
					'end_date_label'   => $end_date_label,
					'end_date'         => $display_end_date,
					'time_lable'       => $time_label,
					'time_slot'        => trim( $time_display ),
					'total'            => $booking->get_cost() * $quantity,
					'quantity'         => $quantity,
					'booking_comment'  => $comment,
				),
				'html'     => $html,
				'vue_data' => $vue_data,
				'edit'     => array(
					'post_id'         => $booking_id,
					'order_url'       => $order ? bkap_order_url( $order->get_id() ) : '',
					'confirm_msg'     => __( 'Are you sure you want to trash the booking?', 'woocommerce-booking' ),
					'booking_type'    => $booking_type,
					'hidden_date'     => bkap_date_as_format( $booking->get_start(), 'j-n-Y' ),
					'hidden_checkout' => $hidden_checkout,
					'pastCheckout'    => $past_checkout,
					'time_slot'       => trim( $time_display ),
					'duration'        => $selected_duration,
					'duration_time'   => $selected_duration_time,
					'variation_id'    => $booking->get_variation_id(),
					'block_value'     => $block_value,
					'resource'        => $resource,
					'persons'         => $persons,
					'booking_comment' => $comment,
				),
			)
		);
	}

	/**
	 * Update Booking.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function update_booking( WP_REST_Request $request ) {

		if ( 'self' !== $request->get_param( 'origin' ) && ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$post_id                       = wc_clean( $request->get_param( 'booking_id' ) );
		$product_id                    = wc_clean( $request->get_param( 'product_id' ) );
		$hidden_date                   = wc_clean( $request->get_param( 'wapbk_hidden_date' ) );
		$hidden_date_checkout          = wc_clean( $request->get_param( 'wapbk_hidden_date_checkout' ) );
		$time_slot                     = wc_clean( $request->get_param( 'time_slot' ) );
		$duration_time_slot            = wc_clean( $request->get_param( 'duration_time_slot' ) );
		$bkap_duration_field           = wc_clean( $request->get_param( 'bkap_duration_field' ) );
		$quantity                      = wc_clean( $request->get_param( 'quantity' ) );
		$status                        = wc_clean( $request->get_param( 'status' ) );
		$selected_duration             = wc_clean( $request->get_param( 'selected_duration' ) );
		$bkap_price_charged            = wc_clean( $request->get_param( 'bkap_price_charged' ) );
		$bkap_field_persons            = wc_clean( $request->get_param( 'bkap_field_persons' ) );
		$bkap_front_resource_selection = wc_clean( $request->get_param( 'bkap_front_resource_selection' ) );
		$resource_selection_type       = BKAP_Product_Resource::get_resource_selection_type( $product_id );

		// Getting Date & Time Format Setting.
		$global_settings        = bkap_global_setting();
		$date_format_to_display = $global_settings->booking_date_format;
		$time_format_to_display = bkap_common::bkap_get_time_format( $global_settings );

		// Fetching Labels of Booking fields.
		$book_item_meta_date     = get_option( 'book_item-meta-date' );
		$book_item_meta_date     = ( '' === $book_item_meta_date ) ? __( 'Start Date', 'woocommerce-booking' ) : $book_item_meta_date;
		$checkout_item_meta_date = get_option( 'checkout_item-meta-date' );
		$checkout_item_meta_date = ( '' === $checkout_item_meta_date ) ? __( 'End Date', 'woocommerce-booking' ) : $checkout_item_meta_date;
		$book_item_meta_time     = get_option( 'book_item-meta-time' );
		$book_item_meta_time     = ( '' === $book_item_meta_time ) ? __( 'Booking Time', 'woocommerce-booking' ) : $book_item_meta_time;

		// Get booking object.
		$booking                     = new BKAP_Booking( $post_id );
		$bkap_setting                = bkap_setting( $product_id );
		$booking_data['date']        = gmdate( 'Y-m-d', strtotime( $hidden_date ) );
		$booking_data['hidden_date'] = $hidden_date;
		$booking_type                = bkap_type( $product_id );
		$days                        = 1;
		$old_end                     = '';
		$old_time                    = '';
		$new_time                    = '';
		$bkap_date_formats           = bkap_date_formats();

		if ( 'multiple_days' === $booking_type ) {
			$old_end                              = gmdate( 'Y-m-d', strtotime( $booking->get_end() ) );
			$booking_data['date_checkout']        = gmdate( 'Y-m-d', strtotime( $hidden_date_checkout ) );
			$booking_data['hidden_date_checkout'] = $hidden_date_checkout;
			$days                                 = ceil( ( strtotime( $hidden_date_checkout ) - strtotime( $hidden_date ) ) / 86400 );
		}

		if ( 'date_time' === $booking_type || 'multidates_fixedtime' === $booking_type ) {
			$old_time                  = $booking->get_time();
			$booking_data['time_slot'] = $old_time;

			if ( '' !== $time_slot ) {
				$new_time_array = explode( '-', $time_slot );
				$new_time       = bkap_date_as_format( trim( $new_time_array[0] ), 'H:i' );

				if ( isset( $new_time_array[1] ) && '' !== $new_time_array[1] ) {
					$new_time .= ' - ' . bkap_date_as_format( trim( $new_time_array [1] ), 'H:i' );
				}

				$booking_data['time_slot'] = $new_time;
			}
		}

		if ( 'duration_time' === $booking_type ) {
			$old_time                           = $booking->get_selected_duration_time();
			$booking_data['duration_time_slot'] = $duration_time_slot;
			$old_duration                       = $booking->get_selected_duration();
			$booking_data['selected_duration']  = $bkap_duration_field;
		}

		$new_qty       = '' !== $quantity ? $quantity : 1;
		$new_status    = $status;
		$product       = wc_get_product( $product_id );
		$product_title = $product->get_name();

		// get the existing data, so we can figure out what has been modified.
		$old_qty    = get_post_meta( $post_id, '_bkap_qty', true );
		$old_status = $booking->get_status();
		$old_start  = bkap_date_as_format( $booking->get_start(), 'Y-m-d' );
		$item_id    = get_post_meta( $post_id, '_bkap_order_item_id', true );
		$order_id   = bkap_order_id_by_itemid( $item_id );

		// default the variables.
		$qty_update        = false;
		$date_update       = false;
		$time_update       = false;
		$resource_changed  = false;
		$notes_array       = array();
		$current_user      = wp_get_current_user();
		$current_user_name = $current_user->display_name;

		/* Checking if the quantity is changed */
		if ( absint( $old_qty ) !== absint( $new_qty ) ) {
			$qty_update    = true;
			$notes_array[] = "The quantity for $product_title was modified from $old_qty to $new_qty by $current_user_name";
		}

		/* Checking if the booking details are changed */
		if ( strtotime( $old_start ) !== strtotime( $hidden_date ) ) {
			$date_update = true;
		}

		if ( 'multiple_days' === $booking_type ) {
			if ( strtotime( $old_end ) !== strtotime( $hidden_date_checkout ) ) {
				$date_update = true;
			}
		} elseif ( 'date_time' === $booking_type || 'multidates_fixedtime' === $booking_type ) {
			if ( $old_time !== $new_time ) {
				$time_update = true;
			}
		} elseif ( 'duration_time' === $booking_type ) {
			if ( $old_time !== $new_time ) {
				$time_update = true;
			}

			if ( $old_duration !== $bkap_duration_field ) {
				$time_update = true;
			}
		}

		/* check if price has been modified */
		$new_price         = $bkap_price_charged;
		$new_price_per_qty = (float) $new_price / (int) $new_qty;

		/* Woo Product Addon Options price are present then add those */
		$addon_price = wc_get_order_item_meta( $item_id, '_wapbk_wpa_prices' );

		if ( $addon_price && $addon_price > 0 ) {
			if ( isset( $global_settings->woo_product_addon_price ) && 'on' === $global_settings->woo_product_addon_price ) {
				$addon_price = $addon_price * $days;
			}

			$new_price_per_qty += $addon_price;
		}

		/* GF Product Addon Options price are present then add those */
		$gf_history = wc_get_order_item_meta( $item_id, '_gravity_forms_history' );

		if ( $gf_history && count( $gf_history ) > 0 ) {
			$gf_details = isset( $gf_history['_gravity_form_lead'] ) ? $gf_history['_gravity_form_lead'] : array();

			if ( count( $gf_details ) > 0 ) {
				$addon_price = array_pop( $gf_details );
				if ( isset( $addon_price ) && $addon_price > 0 ) {
					if ( isset( $global_settings->woo_gf_product_addon_option_price ) && 'on' === $global_settings->woo_gf_product_addon_option_price ) {
						$addon_price = $addon_price * $days;
					}

					$new_price_per_qty += $addon_price;
				}
			}
		}

		$new_price    = $new_price_per_qty * $new_qty;
		$old_price    = (float) $booking->get_cost() * $booking->get_quantity();
		$price_update = false;

		if ( $old_price !== $new_price ) {
			$price_update = apply_filters( 'bkap_price_change_on_edit_booking', true, $booking );
		}

		/* Checking if the persons data has been changed */
		$person_changed = false;
		if ( isset( $bkap_setting['bkap_person'] ) && 'on' === $bkap_setting['bkap_person'] ) {
			if ( '' !== $bkap_field_persons && null !== $bkap_field_persons ) {
				$old_person_info = $booking->get_persons();
				if ( 'on' === $bkap_setting['bkap_person_type'] ) {
					$old_person_info = $booking->get_persons();
					$new_person_info = array();

					foreach ( $bkap_field_persons as $p_id => $p_data ) {
						if ( '' !== $bkap_field_persons[ $p_id ]['person_id'] ) {
							$id                     = $bkap_field_persons[ $p_id ]['person_id'];
							$new_person_info[ $id ] = (int) $bkap_field_persons[ $p_id ]['person_val'];
						}
					}

					if ( $old_person_info !== $new_person_info ) {
						$person_changed = true;
					}
				} else {
					$new_person_info = array( (int) $bkap_field_persons );

					if ( $old_person_info !== $new_person_info ) {
						$person_changed = true;
					}
				}
			}
		}

		/* Resource */
		$resource_id     = '';
		$new_resource_id = '';

		if ( ! is_null( $bkap_front_resource_selection ) && '' !== $bkap_front_resource_selection ) {

			$resource_id      = $bkap_front_resource_selection;
			$old_resource_id  = $booking->get_resource();
			$resource_changed = false;

			if ( 'multiple' === $resource_selection_type ) {
				if ( ! is_array( $resource_id ) ) {
					$temp        = $resource_id;
					$resource_id = array( $temp );
				}

				// Fetch existing resource_ids from the Order.
				$old_resource_id = wc_get_order_item_meta( $item_id, '_resource_id' );

				if ( '' !== $old_resource_id && is_array( $old_resource_id ) ) {
					$_old_resource_id = array_values( $old_resource_id );
					sort( $_old_resource_id );

					$_resource_id = array_values( $resource_id );
					sort( $_resource_id );

					if ( $_resource_id !== $_old_resource_id ) {
						$new_resource_id  = $resource_id;
						$resource_changed = true;
					}
				}
			} else {
				if ( $resource_id !== $old_resource_id ) {
					$new_resource_id  = $resource_id;
					$resource_changed = true;
				}
			}
		}

		if ( $old_status !== $new_status || $qty_update || $date_update || $time_update || $resource_changed || $person_changed ) {
			// gather the data & validate.
			$data['product_id']           = $product_id;
			$data['booking_type']         = $booking_type;
			$data['qty']                  = $new_qty;
			$data['hidden_date']          = $booking_data['hidden_date'];
			$data['hidden_date_checkout'] = isset( $booking_data['hidden_date_checkout'] ) ? $booking_data['hidden_date_checkout'] : '';
			$data['time_slot']            = isset( $booking_data['time_slot'] ) ? $booking_data['time_slot'] : '';
			$data['duration_time_slot']   = isset( $booking_data['duration_time_slot'] ) ? $booking_data['duration_time_slot'] : '';
			$data['post_id']              = $post_id;

			if ( $old_status !== $new_status && 'cancelled' === $old_status ) {
				$data['edit_from'] = 'order';
			}

			$sanity_results = bkap_cancel_order::bkap_sanity_check( $data );
			if ( count( $sanity_results ) > 0 ) {
				// update_post_meta( $post_id, '_bkap_update_errors', $sanity_results );
				return self::response( 'error', array( 'error_description' => implode( ' / ', $sanity_results ) ) );
			}
		}

		if ( 'cancelled' === $new_status && $old_status === $new_status ) {
			// update_post_meta( $post_id, '_bkap_update_errors', $error );
			return self::response( 'error', array( 'error_description' => __( 'You can\'t update booking details of cancelled booking.', 'woocommerce-booking' ) ) );
		}

		$bookings = array();

			// When resource is changed, then update data in the respective places.
		if ( '' !== $resource_id ) {
			if ( 'single' === $resource_selection_type && $resource_changed ) {
				$old_resource_title = get_the_title( $booking->get_resource() );
				$resource_data      = wc_get_order_item_meta( $item_id, '_resource_id' );
				$resource_title     = Class_Bkap_Product_Resource::get_resource_name( $new_resource_id );
				$note               = "The resource for $product_title was modified from $old_resource_title to $resource_title by $current_user_name";

				Class_Bkap_Product_Resource::update_order_item_meta( $product_id, $item_id, $new_resource_id );
				update_post_meta( $post_id, '_bkap_resource_id', $new_resource_id );
				$notes_array[] = $note;
			}

			if ( 'multiple' === $resource_selection_type ) {
				$bookings = bkap_common::get_booking_id( $item_id, true );

				// Ensure that the resources that have been set conform to the number of bookings.
				try {
					$new_bookings = Class_Bkap_Product_Resource::conform_bookings_with_resources( $bookings, $resource_id, $post_id );
				} catch ( Exception $e ) {
					// update_post_meta( $post_id, '_bkap_update_errors', $e->getMessage() );
					return self::response( 'error', array( 'error_description' => $e->getMessage() ) );
				}

				foreach ( $new_bookings as $key => $id ) {
					$resource_title = Class_Bkap_Product_Resource::get_resource_name( $resource_id[ $key ] );

					// Create new bookings for new resources that have been added.
					if ( 0 === $id ) {
						$booking_data['price']       = $new_price_per_qty;
						$booking_data['resource_id'] = $resource_id[ $key ];
						$_booking                    = bkap_checkout::bkap_create_booking_post( $item_id, $product_id, $new_qty, $booking_data );
						$notes_array[]               = 'Booking #' . $_booking->id . ' has been created for Resource ' . $resource_title . '.Product: ' . $product_title;
						$new_bookings[ $key ]        = strval( $_booking->id );

						unset( $booking_data['price'] );
						unset( $booking_data['resource_id'] );
					} else {
						if ( $resource_changed ) {
							update_post_meta( $id, '_bkap_resource_id', $resource_id[ $key ] );
						}
					}
				}

				$bookings = $new_bookings;

				if ( $resource_changed ) {
					Class_Bkap_Product_Resource::update_order_item_meta( $product_id, $item_id, $resource_id );
				}
			}
		}

			/* Checking if the booking status is changed */
		if ( $old_status !== $new_status ) {
			$_POST['item_id']         = $item_id;
			$_POST['status']          = $new_status;
			$post_data['post_status'] = $new_status;

			if ( 'single' === $resource_selection_type ) {
				BKAP_Booking_Confirmation::bkap_save_booking_status( $item_id, $new_status, $post_id );
			} elseif ( 'multiple' === $resource_selection_type ) {
				foreach ( $bookings as $id ) {
					BKAP_Booking_Confirmation::bkap_save_booking_status( $item_id, $new_status, $id );
				}
			}
		}

		if ( $qty_update || $date_update || $time_update || $resource_changed || $person_changed ) {
			$booking_post = bkap_common::get_booking_id( $item_id ); // update the booking post status.
			$item_key     = 0;
			if ( is_array( $booking_post ) ) {
				foreach ( $booking_post as $k => $v ) {
					if ( $v == $post_id ) {
						$item_key = $k;
					}
				}
			}

			if ( 'cancelled' === $new_status || $date_update ) {
				if ( $booking_post ) { // update the booking post status.
					$new_booking = bkap_checkout::get_bkap_booking( $booking_post );
					do_action( 'bkap_rental_delete', $new_booking, $booking_post );
				}
			}

			$order_id = bkap_order_id_by_itemid( $item_id );

			if ( $order_id > 0 || ( $order_id == 0 && $item_id == 0 ) ) {
				$booking_ids_to_update = array();

				if ( 'single' === $resource_selection_type ) {
					$booking_ids_to_update[] = $post_id;
				} elseif ( 'multiple' === $resource_selection_type ) {
					$booking_ids_to_update = $bookings;
				}

				if ( $order_id > 0 ) {
					foreach ( $booking_ids_to_update as $id ) {

						// Update the booking information in the booking tables.
						bkap_edit_bookings_class::bkap_edit_bookings(
							$id,
							$item_key,
							$order_id,
							$item_id,
							$old_start,
							$old_end,
							$old_time,
							$product_id
						);
	
						// add a new booking.
						$details = bkap_checkout::bkap_update_lockout( $order_id, $product_id, 0, $new_qty, $booking_data );
	
						// update the global time slot lockout.
						if ( isset( $booking_data['time_slot'] ) && $booking_data['time_slot'] != '' ) {
							bkap_checkout::bkap_update_global_lockout( $product_id, $new_qty, $details, $booking_data );
						}
					}
				}

				// update item meta.
				$display_start = gmdate( $bkap_date_formats[ $date_format_to_display ], strtotime( $hidden_date ) );
				if ( in_array( $booking_type, array( 'multidates', 'multidates_fixedtime' ), true ) ) {
					$item_bookings = bkap_common::get_booking_id( $item_id );
					foreach ( $item_bookings as $k => $v ) {
						if ( $v == $post_id ) {
							$item_key = $k;
						}
					}

					if ( isset( $item_key ) ) {
						bkap_update_order_itemmeta_multidates( $item_id, $book_item_meta_date, $display_start, $booking->get_start_date(), $item_key );
						bkap_update_order_itemmeta_multidates( $item_id, '_wapbk_booking_date', gmdate( 'Y-m-d', strtotime( $hidden_date ) ), $old_start, $item_key );
					}
				} else {
					wc_update_order_item_meta( $item_id, $book_item_meta_date, $display_start, $booking->get_start_date() );
					wc_update_order_item_meta( $item_id, '_wapbk_booking_date', gmdate( 'Y-m-d', strtotime( $hidden_date ) ), $old_start );
				}

				$meta_start = gmdate( 'Ymd', strtotime( $hidden_date ) );

				switch ( $booking_type ) {
					case 'only_day':
					case 'multidates':
						$meta_start .= '000000';
						$meta_end    = $meta_start;

						// add order notes if needed.
						if ( $date_update ) {
							$old_start_display = gmdate( $bkap_date_formats[ $date_format_to_display ], strtotime( $old_start ) );
							$notes_array[]     = "The booking details have been modified from $old_start_display to $display_start by $current_user_name";
						}
						break;
					case 'multiple_days':
						$display_end = gmdate( $bkap_date_formats[ $date_format_to_display ], strtotime( $hidden_date_checkout ) );
						wc_update_order_item_meta( $item_id, $checkout_item_meta_date, $display_end, '' );
						wc_update_order_item_meta( $item_id, '_wapbk_checkout_date', gmdate( 'Y-m-d', strtotime( $hidden_date_checkout ) ), '' );

						$meta_start .= '000000';
						$meta_end    = gmdate( 'Ymd', strtotime( $hidden_date_checkout ) );
						$meta_end   .= '000000';

						// add order notes if needed.
						if ( $date_update ) {
							$old_start_display = gmdate( $bkap_date_formats[ $date_format_to_display ], strtotime( $old_start ) );
							$old_end_display   = gmdate( $bkap_date_formats[ $date_format_to_display ], strtotime( $old_end ) );
							$notes_array[]     = "The booking details have been modified from $old_start_display - $old_end_display to $display_start - $display_end by $current_user_name";
						}

						break;
					case 'date_time':
					case 'multidates_fixedtime':
						$time_array    = explode( ' - ', $new_time );
						$timezone_name = $booking->get_timezone_name();

						if ( $timezone_name != '' ) {
							$offset = bkap_get_offset( $booking->get_timezone_offset() );
							date_default_timezone_set( bkap_booking_get_timezone_string() );  //phpcs:ignore
							$display_time = gmdate( $time_format_to_display, $offset + strtotime( $time_array[0] ) );
							$db_time      = gmdate( 'H:i', $offset + strtotime( $time_array[0] ) );

							$hidden_date_time_str = $offset + strtotime( $hidden_date . ' ' . $time_array[0] );

							if ( isset( $time_array[1] ) && '' !== $time_array[1] ) {
								$display_time .= ' - ' . gmdate( $time_format_to_display, $offset + strtotime( $time_array[1] ) );
								$db_time      .= ' - ' . gmdate( 'H:i', $offset + strtotime( $time_array[1] ) );

								date_default_timezone_set( 'UTC' ); //phpcs:ignore

								$meta_end  = gmdate( 'Ymd', strtotime( $hidden_date ) );
								$meta_end .= gmdate( 'His', strtotime( $time_array[1] ) );
							} else {
								date_default_timezone_set( 'UTC' ); //phpcs:ignore
								$meta_end  = gmdate( 'Ymd', strtotime( $hidden_date ) );
								$meta_end .= '000000';
							}

							$display_start = gmdate( $bkap_date_formats[ $date_format_to_display ], $hidden_date_time_str );
							wc_update_order_item_meta( $item_id, $book_item_meta_date, $display_start, '' );
							wc_update_order_item_meta( $item_id, '_wapbk_booking_date', gmdate( 'Y-m-d', $hidden_date_time_str ), '' );
							// $meta_start = gmdate( 'Ymd', $hidden_date_time_str );
							$meta_start .= gmdate( 'His', strtotime( trim( $time_array[0] ) ) );
						} else {
							$display_time = gmdate( $time_format_to_display, strtotime( $time_array[0] ) );
							$db_time      = gmdate( 'H:i', strtotime( $time_array[0] ) );
							$meta_start  .= gmdate( 'His', strtotime( $time_array[0] ) );
							if ( isset( $time_array[1] ) && '' !== $time_array[1] ) {
								$display_time .= ' - ' . gmdate( $time_format_to_display, strtotime( $time_array[1] ) );
								$db_time      .= ' - ' . gmdate( 'H:i', strtotime( $time_array[1] ) );
								$meta_end      = gmdate( 'Ymd', strtotime( $hidden_date ) );
								$meta_end     .= gmdate( 'His', strtotime( $time_array[1] ) );
							} else {
								$meta_end  = gmdate( 'Ymd', strtotime( $hidden_date ) );
								$meta_end .= '000000';
							}
						}

						$booking_datas = $data;
						$display_time  = apply_filters( 'bkap_new_time_in_note_on_edit_booking', $display_time, $data, $product_id );

						// add order notes if needed.
						if ( $date_update || $time_update ) {
							$old_start_display = gmdate( $bkap_date_formats[ $date_format_to_display ], strtotime( $old_start ) );

							if ( $timezone_name != '' ) {
								$old_time_disp = bkap_convert_system_time_to_timezone_time( $old_time, $offset, $time_format_to_display );
							} else {
								$old_time_array = explode( '-', $old_time );
								$old_time_disp  = gmdate( $time_format_to_display, strtotime( trim( $old_time_array[0] ) ) );

								if ( isset( $old_time_array[1] ) && '' !== $old_time_array[1] ) {
									$old_time_disp .= ' - ' . gmdate( $time_format_to_display, strtotime( $old_time_array[1] ) );
								}
							}

							$time_change_note = apply_filters(
								'bkap_edit_booking_time_change_note',
								"The booking details have been modified from $old_start_display, $old_time_disp to $display_start, $display_time by $current_user_name",
								$product_id,
								array(
									'old_display_date' => $old_start_display,
									'old_display_time' => $old_time_disp,
									'new_display_date' => $display_start,
									'new_display_time' => $display_time,
									'user_name'        => $current_user_name,
									'old_date'         => $old_start,
									'old_time'         => $old_time,
									'new_date'         => $hidden_date,
									'new_time'         => $new_time,
								)
							);

							$notes_array[] = $time_change_note;

							if ( in_array( $booking_type, array( 'multidates', 'multidates_fixedtime' ), true ) ) {
								if ( isset( $item_key ) ) {
									bkap_update_order_itemmeta_multidates( $item_id, $book_item_meta_time, $display_time, $old_time_disp, $item_key );
									bkap_update_order_itemmeta_multidates( $item_id, '_wapbk_time_slot', $db_time, $old_time, $item_key );
								}
							} else {
								wc_update_order_item_meta( $item_id, $book_item_meta_time, $display_time, '' );
								wc_update_order_item_meta( $item_id, '_wapbk_time_slot', $db_time, '' );
							}
						}

						break;

					case 'duration_time':
						$start_date   = $data['hidden_date'];
						$date_booking = gmdate( 'Y-m-d', strtotime( $data['hidden_date'] ) );
						$time         = $data['duration_time_slot'];
						$meta_start   = gmdate( 'YmdHis', strtotime( $date_booking . ' ' . $time ) );
						$end_date_str = $date_booking;

						if ( '' !== $selected_duration ) {
							$selected_duration = explode( '-', $data['selected_duration'] );
							$hour              = isset( $selected_duration[0] ) ? $selected_duration[0] : '';
							$d_type            = isset( $selected_duration[1] ) ? $selected_duration[1] : '';
						}

						if ( '' !== $bkap_duration_field ) {
							$d_setting = get_post_meta( $product_id, '_bkap_duration_settings', true );
							$hour      = (int) $bkap_duration_field * $d_setting['duration'];
							$d_type    = isset( $d_setting['duration_type'] ) ? $d_setting['duration_type'] : '';
						}

						$end_str  = bkap_common::bkap_add_hour_to_date( $start_date, $time, $hour, $product_id, $d_type ); // return end date timestamp
						$meta_end = gmdate( 'YmdHis', $end_str );
						$end_date = gmdate( 'j-n-Y', $end_str ); // Date in j-n-Y format to compate and store in end date order meta

						// updating end date
						if ( $data['hidden_date'] !== $end_date ) {
							$name_checkout   = ( '' == get_option( 'checkout_item-meta-date' ) ) ? __( 'End Date', 'woocommerce-booking' ) : get_option( 'checkout_item-meta-date' );
							$bkap_format     = bkap_common::bkap_get_date_format(); // get date format set at global
							$end_date_str    = gmdate( 'Y-m-d', strtotime( $end_date ) ); // conver date to Y-m-d format
							$end_date_str    = $date_booking . ' - ' . $end_date_str;
							$end_date_string = gmdate( $bkap_format, strtotime( $end_date ) ); // Get date based on format at global level
							$end_date_string = $start_date . ' - ' . $end_date_string;

							// Updating end date field in order item meta.
							wc_update_order_item_meta( $item_id, '_wapbk_booking_date', sanitize_text_field( $end_date_str, '' ) );
							wc_update_order_item_meta( $item_id, $book_item_meta_date, sanitize_text_field( $end_date_string, '' ) );
						}

						$endtime  = gmdate( 'H:i', $end_str );// getend time in H:i format
						$startime = bkap_common::bkap_get_formated_time( $time ); // return start time based on the time format at global
						$endtime  = bkap_common::bkap_get_formated_time( $endtime ); // return end time based on the time format at global

						$time_slot = $startime . ' - ' . $endtime; // to store time sting in the _wapbk_time_slot key of order item meta

						// Updating timeslot.
						$time_slot_label = ( '' == get_option( 'book_item-meta-time' ) ) ? __( 'Booking Time', 'woocommerce-booking' ) : get_option( 'book_item-meta-time' );

						wc_update_order_item_meta( $item_id, $time_slot_label, $time_slot, '' );
						wc_update_order_item_meta( $item_id, '_wapbk_time_slot', $time_slot, '' );

						$notes_array[] = "The booking details have been modified to $end_date_str, $time_slot by $current_user_name";
						break;
				}

				// if qty has been updated, update the same to be reflected in Woo->Orders.
				if ( $qty_update ) {
					wc_update_order_item_meta( $item_id, '_qty', $new_qty, '' );
				}

				if ( $person_changed ) {
					wc_update_order_item_meta( $item_id, '_persons', $new_person_info );
					update_post_meta( $post_id, '_bkap_persons', $new_person_info );

					if ( 'multiple' === $resource_selection_type ) {
						Class_Bkap_Product_Resource::update_data_for_related_bookings( $post_id, '_bkap_persons', $new_person_info );
					}

					if ( isset( $new_person_info[0] ) ) {
						wc_update_order_item_meta( $item_id, BKAP_Person::bkap_get_person_label( $product_id ), $new_person_info[0] );
					} else {
						foreach ( $new_person_info as $p_key => $p_data ) {
							wc_update_order_item_meta( $item_id, get_the_title( $p_key ), $p_data );
						}
					}

					$notes_array[] = sprintf(
						/* Translators: %1$s Product Title, %$2s User name */
						__( 'The person data for %1$s was modified by %2$s', 'woocommerce-booking' ),
						esc_html( $product_title ),
						esc_html( $current_user_name )
					);
				}

				if ( isset( $post['block_option'] ) ) { // updating selected fixed block data.
					$fixed_block = ( ! empty( $post['block_option'] ) ) ? $post['block_option'] : '';
					update_post_meta( $post_id, '_bkap_fixed_block', $fixed_block );

					if ( 'multiple' === $resource_selection_type ) {
						Class_Bkap_Product_Resource::update_data_for_related_bookings( $post_id, '_bkap_fixed_block', $fixed_block );
					}
				}

				// update the post meta for the booking.
				update_post_meta( $post_id, '_bkap_start', $meta_start );
				update_post_meta( $post_id, '_bkap_end', $meta_end );
				update_post_meta( $post_id, '_bkap_qty', $new_qty );

				if ( $order_id == 0 && $item_id == 0 ) {
					update_post_meta( $post_id, '_bkap_cost', $new_price_per_qty );
				}

				if ( 'multiple' === $resource_selection_type ) {
					Class_Bkap_Product_Resource::update_data_for_related_bookings( $post_id, '_bkap_start', $meta_start );
					Class_Bkap_Product_Resource::update_data_for_related_bookings( $post_id, '_bkap_end', $meta_end );
					Class_Bkap_Product_Resource::update_data_for_related_bookings( $post_id, '_bkap_qty', $new_qty );
				}

				if ( $order_id > 0 ) {
					$new_order_obj = wc_get_order( $order_id );

					if ( $price_update && ( $order_id ) ) {
						$newprice = bkap_common::compute_price_for_order_with_tax_for_edited_bookings( $product_id, $item_id, $order_id, $new_price );

						// $wc_price_args = bkap_common::get_currency_args();
						// $newprice      = number_format( $newprice, $wc_price_args['decimals'], $wc_price_args['decimal_separator'], $wc_price_args['thousand_separator'] );
						update_post_meta( $post_id, '_bkap_cost', $newprice );

						if ( 'multiple' === $resource_selection_type ) {
							Class_Bkap_Product_Resource::update_data_for_related_bookings( $post_id, '_bkap_cost', $newprice );
						}

						// update the order total.
						$old_total = $new_order_obj->get_total();
						$new_total = round( $old_total - $old_price + $new_price, 2 );
						// $new_order_obj->set_total( $new_total );

						$order_currency    = ( version_compare( WOOCOMMERCE_VERSION, '3.0.0' ) < 0 ) ? $new_order_obj->get_order_currency() : $new_order_obj->get_currency();
						$currency_symbol   = get_woocommerce_currency_symbol( $order_currency );
						$display_old_price = $currency_symbol . $old_price;
						$display_new_price = $currency_symbol . $newprice;
						$notes_array[]     = "The booking price for $product_title has been modified from $display_old_price to $display_new_price by $current_user_name";
					}

					$new_order_obj->calculate_totals();

					// Creating Zoom Meeting.
					bkap_common::create_zoom_meetings_for_edited_bookings( $order_id, $post_id );

					bkap_insert_event_to_gcal( $new_order_obj, $product_id, $item_id, $item_key );

					if ( is_array( $notes_array ) && count( $notes_array ) > 0 ) { // add order notes.
						foreach ( $notes_array as $msg ) {
							$new_order_obj->add_order_note( __( $msg, 'woocommerce-booking' ) ); // phpcs:ignore
						}
					}
				}

				do_action( 'bkap_after_update_booking_post', $post_id, $booking, bkap_get_meta_data( $post_id ) );
				do_action( 'bkap_booking_updated', $post_id, get_post( $post_id ), null );
			}
		}

		return self::response( 'success', array( 'message' => __( 'Booking has been updated successfully', 'woocommerce-booking' ) ) );
	}
}
