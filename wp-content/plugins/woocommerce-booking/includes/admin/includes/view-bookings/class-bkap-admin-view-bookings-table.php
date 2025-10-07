<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * View Bookings Table.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/Includes/ViewBookingsTable
 * @category    Classes
 * @since       5.19.0
 */

defined( 'ABSPATH' ) || exit;

// Load WP_List_Table if not loaded.
if ( ! class_exists( 'WP_List_Table' ) ) {
	BKAP_Files::include_file( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * View Bookings Table.
 *
 * @since 5.19.0
 */
class BKAP_Admin_View_Bookings_Table extends WP_List_Table {

	/**
	 * Records per page.
	 *
	 * @var int
	 * @since 5.19.0
	 */
	public $records_per_page = 20;

	/**
	 * Current page number.
	 *
	 * @var int
	 * @since 5.19.0
	 */
	public $page = 1;

	/**
	 * Total number of Bookings.
	 *
	 * @var int
	 * @since 5.19.0
	 */
	public $total_bookings = 0;

	/**
	 * Booking data.
	 *
	 * @var array
	 * @since 5.19.0
	 */
	public $booking_data = array();

	/**
	 * Custom Post type for Bookings.
	 *
	 * @var string $post_type
	 */
	public $post_type = 'bkap_booking';

	/**
	 * Default Booking Post Status.
	 *
	 * @var array $default_post_status
	 */
	public $default_post_status = array(
		'draft',
		'cancelled',
		'confirmed',
		'paid',
		'pending-confirmation',
	);

	/**
	 * Sort - Order.
	 *
	 * @var string $sort_order
	 */
	public $sort_order = 'desc';

	/**
	 * Sort - Order By.
	 *
	 * @var string $sort_orderby
	 */
	public $sort_orderby = '';

	/**
	 * Post Status.
	 *
	 * @var string $post_status
	 */
	public $post_status = 'all';

	/**
	 * Construct
	 *
	 * @since 5.19.0
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'booking',
				'plural'   => 'bookings',
				'ajax'     => true,
			)
		);
	}

	/**
	 * Table Columns.
	 *
	 * @return array
	 * @since 5.19.0
	 */
	public function get_columns() {

		$columns = array(
			'checkbox'       => '
				<div class="custom-control custom-checkbox">
					<input type="checkbox" class="custom-control-input view_bookings_checkbox checkbox_select_all" >
					<label class="custom-control-label"
						for="checkbox_select_all"></label>
				</div>
			',
			'actions'        => __( 'Actions', 'woocommerce-booking' ),
			'status'         => __( 'Status', 'woocommerce-booking' ),
			'booking_id'     => __( 'Booking ID', 'woocommerce-booking' ),
			'booked_product' => __( 'Booked Product', 'woocommerce-booking' ),
			'booked_by'      => __( 'Booked By', 'woocommerce-booking' ),
			'order'          => __( 'Order', 'woocommerce-booking' ),
			'start_date'     => __( 'Start Date', 'woocommerce-booking' ),
			'end_date'       => __( 'End Date', 'woocommerce-booking' ),
			'persons'        => __( 'Persons', 'woocommerce-booking' ),
			'quantity'       => __( 'Quantity', 'woocommerce-booking' ),
			'amount'         => __( 'Amount', 'woocommerce-booking' ),
			'order_date'     => __( 'Order Date', 'woocommerce-booking' ),
			'zoom_meeting'   => __( 'Zoom Meeting', 'woocommerce-booking' ),
		);

		if ( is_plugin_active( 'bkap-deposits/deposits.php' ) && 'on' === get_option( 'bkap_deposit_payment_view_bookings_fields', '' ) ) {
			$columns['remaining_balance_order'] = __( 'Remaining Balance Order', 'woocommerce-booking' );
			$columns['payment_status']          = __( 'Payment Status', 'woocommerce-booking' );
		}

		if ( is_plugin_active( 'bkap-printable-tickets/printable-tickets.php' ) ) {
			$columns['ticket_id']     = __( 'Ticket ID', 'woocommerce-booking' );
			$columns['security_code'] = __( 'Security Code', 'woocommerce-booking' );
		}

		return apply_filters(
			'bkap_view_booking_columns',
			$columns
		);
	}

	/**
	 * Table Column Definition.
	 *
	 * @param object $item Booking data.
	 * @since  5.19.0
	 */
	public function single_row( $item ) {

		$row_id = 'row-booking-id-' . $item['booking_id'];

		echo '<tr id="' . esc_attr( $row_id ) . '">';

		$this->single_row_columns( $item );

		echo '</tr>';
	}

	/**
	 * Table Column Definition.
	 *
	 * @param  array  $data Booking data.
	 * @param  stirng $column Column.
	 * @since  5.19.0
	 */
	public function column_default( $data, $column ) {

		switch ( $column ) {

			case 'checkbox':
				return '
					<div class="custom-control custom-checkbox view_bookings_checkbox">
						<input type="checkbox" class="custom-control-input" id="checkbox_' . $data['booking_id'] . '" data-booking-id="' . $data['booking_id'] . '" />
						<label class="custom-control-label" for="checkbox_' . $data['booking_id'] . '" data-booking-id="' . $data['booking_id'] . '"></label>
					</div>
				';

			case 'status':
				return bkap_common::get_mapped_status( $data['status'] );

			case 'booking_id':
				return isset( $data['actions'] ) && isset( $data['actions']['view'] ) ? '<div class="tbl_booking_action"><span class="view-bookings-action" data-action="edit" data-booking-id="' . $data['booking_id'] . '">#' . $data['booking_id'] . '</span></div>' : '#' . $data['booking_id']; // phpcs:ignore

			case 'booked_product':
				return $data['booked_product'];

			case 'booked_by':
				return $data['booked_by'];

			case 'order':
				return $data['order'];

			case 'start_date':
				return $data['start_date'];

			case 'end_date':
				return $data['end_date'];

			case 'persons':
				return $data['persons'];

			case 'quantity':
				return $data['quantity'];

			case 'amount':
				return $data['amount'];

			case 'order_date':
				return $data['order_date'];

			case 'zoom_meeting':
				return $data['zoom_meeting_link'];

			case 'remaining_balance_order':
				return $data['remaining_balance_order'];

			case 'payment_status':
				return $data['payment_status'];

			case 'security_code':
				return $data['security_code'];

			case 'ticket_id':
				return $data['ticket_id'];

			case 'actions':
				$html = '';

				if ( isset( $data['actions'] ) ) {

					$html = '<div class="tbl_booking_action">';

					if ( isset( $data['actions']['confirm'] ) ) {
						$html .= '<span class="view-bookings-action dashicons dashicons-saved color-black" title="Confirm" data-action="confirm" data-booking-id="' . $data['booking_id'] . '"></span>'; // phpcs:ignore
					}

					if ( isset( $data['actions']['view'] ) ) {
						$html .= '<span class="view-bookings-action dashicons dashicons-edit color-green" title="Edit" data-action="edit" data-booking-id="' . $data['booking_id'] . '"></span>'; // phpcs:ignore
					}

					if ( isset( $data['actions']['delete'] ) ) {
						$html .= '<span class="view-bookings-action dashicons dashicons-trash color-red" title="Delete" data-action="delete" data-booking-id="' . $data['booking_id'] . '"></span>'; // phpcs:ignore
					}

					$html .= '</div>';
				}

				return $html;

			default:
				do_action( 'bkap_view_booking_column_data', $column, $data );
				return '';
		}
	}

	/**
	 * Sortable Columns List.
	 *
	 * @return array
	 * @since 5.19.0
	 */
	public function get_sortable_columns() {
		return array(
			'booking_id' => array( 'booking_id', false ),
			'start_date' => array( 'start_date', false ),
			'end_date'   => array( 'end_date', false ),
			'order_date' => array( 'order_date', false ),
		);
	}

	/**
	 * Prepares/Renders Table View.
	 *
	 * @since 5.19.0
	 */
	public function prepare_items() {

		$hidden = ( is_array( get_user_meta( get_current_user_id(), 'manageedit-bkap_bookingcolumnshidden', true ) ) ) ? get_user_meta( get_current_user_id(), 'manageedit-bkap_bookingcolumnshidden', true ) : array();
		if ( is_array( $hidden ) ) { // @todo: earlier columns were with bkap_ but we removed it so think of readding it.
			foreach ( $hidden as $key => $value ) {
				$hidden[ $key ] = str_replace( 'bkap_', '', $value );
			}
		}
		$columns                = $this->get_columns();
		$sortable               = $this->get_sortable_columns();
		$this->_column_headers  = array( $columns, $hidden, $sortable );
		$this->items            = $this->booking_data;
		$this->records_per_page = $this->get_items_per_page( 'edit_bkap_booking_per_page', 20 );

		$this->set_pagination_args(
			array(
				'total_items' => $this->total_bookings,
				'per_page'    => $this->records_per_page,
				'total_pages' => ceil( $this->total_bookings / $this->records_per_page ),
				'orderby'     => '' === $this->sort_orderby,
				'order'       => $this->sort_order,
			)
		);
	}

	/**
	 * Populate Booking Data.
	 *
	 * @param array $options Options.
	 * @since 5.19.0
	 */
	public function populate_data( $options = array() ) {

		global $wpdb;

		$this->records_per_page = $this->get_items_per_page( 'edit_bkap_booking_per_page', 20 );

		$data   = array();
		$filter = isset( $options['filter'] ) ? $options['filter'] : array();

		if ( isset( $filter['list_booking_by'] ) ) {

			$current_date = gmdate( 'Ymd', current_time( 'timestamp' ) );
			$current_time = $current_date . '000000';

			switch ( $filter['list_booking_by'] ) {
				case 'today_onwards':
					$data['filter']['meta_query'][] = array(
						'key'     => '_bkap_start',
						'value'   => $current_time,
						'compare' => '>=',
					);
					break;

				case 'today_checkin':
					$data['filter']['meta_query'][] = array(
						'key'     => '_bkap_start',
						'value'   => $current_date,
						'compare' => 'LIKE',
					);
					break;

				case 'today_checkout':
					$data['filter']['meta_query'][] = array(
						'key'     => '_bkap_end',
						'value'   => $current_date,
						'compare' => 'LIKE',
					);

					$data['filter']['meta_query'][] = array(
						'key'     => '_bkap_start',
						'value'   => $current_date,
						'compare' => 'NOT LIKE',
					);
					break;

				case 'custom_dates':
					$start_date_check = isset( $filter['custom_date_start'] ) && '' !== $filter['custom_date_start'];
					$end_date_check   = isset( $filter['custom_date_end'] ) && '' !== $filter['custom_date_end'];
					$startdate        = $start_date_check ? $filter['custom_date_start'] : $current_date;
					$enddate          = $end_date_check ? $filter['custom_date_end'] : $startdate;
					$from_date        = gmdate( 'YmdHis', strtotime( $startdate . '00:00:00' ) );
					$to_date          = gmdate( 'YmdHis', strtotime( $enddate . '23:59:59' ) );

					if ( ! $start_date_check || ! $end_date_check ) {

						if ( $start_date_check ) {

							$from_date_start = gmdate( 'YmdHis', strtotime( $startdate . '00:00:00' ) );
							$from_date_end   = gmdate( 'YmdHis', strtotime( $startdate . '23:59:59' ) );

							$data['filter']['meta_query'][] = array(
								'key'     => '_bkap_start',
								'value'   => array( $from_date_start, $from_date_end ),
								'type'    => 'NUMERIC',
								'compare' => 'BETWEEN',
							);
						}

						if ( $end_date_check ) {

							$end_date_start = gmdate( 'YmdHis', strtotime( $enddate . '00:00:00' ) );
							$end_date_end   = gmdate( 'YmdHis', strtotime( $enddate . '23:59:59' ) );
							$data['filter']['meta_query'][] = array(
								'key'     => '_bkap_end',
								'value'   => array( $end_date_start, $end_date_end ),
								'type'    => 'NUMERIC',
								'compare' => 'BETWEEN',
							);
						}
					} else {

						$same_start_end = apply_filters( 'bkap_view_bookings_with_same_start_end_date', false );

						if ( ! $same_start_end ) {
							$end_date_meta_query = apply_filters(
								'bkap_view_bookings_end_date_meta_query',
								array(
									'key'     => '_bkap_end',
									'value'   => array( $from_date, $to_date ),
									'type'    => 'NUMERIC',
									'compare' => 'BETWEEN',
								),
								$from_date,
								$to_date,
								$filter
							);

							$data['filter']['meta_query'][] = $end_date_meta_query;

							$start_date_meta_query = apply_filters(
								'bkap_view_bookings_start_date_meta_query',
								array(
									'key'     => '_bkap_start',
									'value'   => array( $from_date, $to_date ),
									'type'    => 'NUMERIC',
									'compare' => 'BETWEEN',
								),
								$from_date,
								$to_date,
								$filter
							);

							$data['filter']['meta_query'][] = $start_date_meta_query;
						} else {
							$end_date_meta_query = apply_filters(
								'bkap_view_bookings_end_date_meta_query',
								array(
									'key'     => '_bkap_end',
									'value'   => $to_date,
									'compare' => '=',
								),
								$from_date,
								$to_date,
								$filter
							);

							$data['filter']['meta_query'][] = $end_date_meta_query;

							$start_date_meta_query = apply_filters(
								'bkap_view_bookings_start_date_meta_query',
								array(
									'key'     => '_bkap_start',
									'value'   => $from_date,
									'compare' => '=',
								),
								$from_date,
								$to_date,
								$filter
							);

							$data['filter']['meta_query'][] = $start_date_meta_query;
						}
					}

					$data = apply_filters( 'bkap_view_bookings_filter_by_custom_date_range', $data );
					break;
				case 'gcal':
					$data['filter']['meta_query'][] = array(
						'key'     => '_bkap_gcal_event_uid',
						'value'   => false,
						'compare' => '!=',
					);
					break;
			}
		}

		if ( isset( $filter['bookable_products'] ) ) {

			if ( 'all_bookable_products' !== $filter['bookable_products'] ) {
				$data['filter']['meta_query'][] = array(
					'key'   => '_bkap_product_id',
					'value' => absint( $filter['bookable_products'] ),
				);
			}
		}

		if ( isset( $filter['date'] ) ) {

			if ( 'all_dates' !== $filter['date'] ) {
				$data['filter']['meta_query'][] = array(
					'relation' => 'OR',
					array(
						'key'     => '_bkap_start',
						'value'   => $filter['date'],
						'compare' => 'LIKE',
					),
					array(
						'key'     => '_bkap_end',
						'value'   => $filter['date'],
						'compare' => 'LIKE',
					),
				);
			}
		}

		if ( isset( $filter['customer_id'] ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
			$customer = $wpdb->get_row(
				'SELECT `' . $wpdb->prefix . 'wc_customer_lookup`.first_name, `' . $wpdb->prefix . 'wc_customer_lookup`.last_name FROM `' . $wpdb->prefix . 'wc_customer_lookup` WHERE `' . $wpdb->prefix . 'wc_customer_lookup`.customer_id = ' . absint( $filter['customer_id'] )
			);

			if ( is_object( $customer ) && count( (array) $customer ) > 0 ) {

				// To cater for instances where more than one customer may have the same last name or first name, we then get an array of ALL Post IDs that have a combination with either First Name or Last Name as Customer Name.
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
				$all_post_ids = $wpdb->get_col(
					$wpdb->prepare(
						"SELECT post_id FROM {$wpdb->postmeta} WHERE ( meta_key = '_billing_first_name' AND meta_value = %s )",
						$customer->first_name
					)
				);

				if ( empty( $all_post_ids ) ) {
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
					$all_post_ids = $wpdb->get_col(
						$wpdb->prepare(
							"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_billing_email' AND meta_value LIKE %s",
							'%' . $wpdb->esc_like( $customer->first_name ) . '%'
						)
					);
				}

				// Sort array values by count. Post ID of the customer to be searched for would have the count as 2, i.e. Post ID appears once for the First Name and then appears a second time for the last name. So we check for a count of 2.

				$sorted_post_ids = array_count_values( $all_post_ids );
				$post_ids        = array();

				foreach ( $sorted_post_ids as $_post_id => $count ) {
					$post_ids[] = $_post_id;
				}

				if ( is_array( $post_ids ) && count( $post_ids ) > 0 ) {
					$data['filter']['meta_query'][] = array(
						'key'     => '_bkap_parent_id',
						'value'   => $post_ids,
						'compare' => 'IN',
					);
				}
			}
		}

		if ( isset( $options['limit'] ) ) {
			if ( is_numeric( $options['limit'] ) ) {
				$this->records_per_page           = $options['limit'];
				$data['filter']['posts_per_page'] = $options['limit'];
			}
		}

		if ( isset( $options['page'] ) ) {
			if ( is_numeric( $options['page'] ) ) {
				$this->page               = (int) $options['page'];
				$data['filter']['offset'] = ( $this->page - 1 ) * $this->records_per_page;
			}
		}

		if ( isset( $options['paged'] ) ) {
			$data['filter']['paged'] = $options['paged'];
		}

		if ( isset( $options['orderby'] ) ) {
			$this->sort_orderby        = $options['orderby'];
			$data['filter']['orderby'] = $options['orderby'];
		}

		if ( isset( $options['order'] ) && 'desc' === $options['order'] ) {
			$this->sort_order        = 'desc';
			$data['filter']['order'] = 'DESC';
		}

		if ( isset( $options['order'] ) && 'asc' === $options['order'] ) {
			$this->sort_order        = 'asc';
			$data['filter']['order'] = 'ASC';
		}

		if ( isset( $filter['resource_id'] ) ) {
			if ( is_numeric( $filter['resource_id'] ) ) {
				$data['filter']['meta_query'][] = array(
					'key'   => '_bkap_resource_id',
					'value' => $filter['resource_id'],
					'type'  => 'numeric',
				);
			}
		}

		if ( isset( $filter['order_id'] ) ) {
			$order_id = absint( $filter['order_id'] );

			if ( wc_get_order( $order_id ) ) {
				$data['filter']['meta_query'][] = array(
					'key'   => '_bkap_parent_id',
					'value' => (int) $filter['order_id'],
					'type'  => 'numeric',
				);
			}
		}

		if ( isset( $options['status'] ) && 'all' !== $options['status'] ) {

			$this->post_status   = $options['status'];
			$data['filter']['post_status'] = $options['status'];
		}

		if ( isset( $filter['start_date'] ) ) {
			if ( false !== DateTime::createFromFormat( 'Y-m-d', $filter['start_date'] ) ) {

				$start_date                     = gmdate( 'Ymd', strtotime( $filter['start_date'] ) ) . '000000';
				$data['filter']['meta_query'][] = array(
					'key'     => '_bkap_start',
					'value'   => $start_date,
					'compare' => '>',
				);
			}
		}

		if ( isset( $filter['end_date'] ) ) {
			if ( false !== DateTime::createFromFormat( 'Y-m-d', $filter['end_date'] ) ) {

				$end_date                       = gmdate( 'Ymd', strtotime( $filter['end_date'] ) ) . '000000';
				$data['filter']['meta_query'][] = array(
					'key'     => '_bkap_end',
					'value'   => $end_date,
					'compare' => '<',
				);
			}
		}

		if ( isset( $options['quantity'] ) ) {
			if ( is_numeric( $options['quantity'] ) ) {
				$data['filter']['meta_query'][] = array(
					'key'   => '_bkap_qty',
					'value' => (int) $options['quantity'],
				);
			}
		}

		if ( isset( $options['search'] ) && '' !== $options['search'] ) {
			$term             = wc_clean( $options['search'] );
			$bkap_date_format = bkap_common::bkap_get_date_format();

			if ( is_numeric( $term ) ) {
				// check if a booking exists by this ID.
				if ( false !== get_post_status( $term ) && 'bkap_booking' === get_post_type( $term ) ) {
					$booking_ids = array( $term );
				} else { // else assume the numeric value is an order ID.
					if ( function_exists( 'wc_order_search' ) ) {
						$order_ids = wc_order_search( $term );
						if ( count( $order_ids ) > 0 ) {
							$booking_ids = bkap_common::get_booking_ids_from_order_id( $order_ids[0] );
							$booking_ids = is_array( $booking_ids ) && count( $booking_ids ) == 0 ? array( 0 ) : $booking_ids;
						}
					}
				}
			} else {
				$regex_text    = strpos( $term, ' ' ) > 0 ? implode( '|', explode( ' ', $term ) ) : $term;
				$search_fields = array_map(
					'wc_clean',
					array(
						'_billing_first_name',
						'_billing_last_name',
						'_billing_company',
						'_billing_address_1',
						'_billing_address_2',
						'_billing_city',
						'_billing_postcode',
						'_billing_country',
						'_billing_state',
						'_billing_email',
						'_billing_phone',
						'_shipping_first_name',
						'_shipping_last_name',
						'_shipping_address_1',
						'_shipping_address_2',
						'_shipping_city',
						'_shipping_postcode',
						'_shipping_country',
						'_shipping_state',
					)
				);

				// Search orders.
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
				$order_ids = $wpdb->get_col( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key IN ('" . implode( "','", $search_fields ) . "') AND meta_value REGEXP '" . $regex_text . "'" );

				if ( empty( $order_ids ) ) {
					$date_from_format = DateTime::createFromFormat( $bkap_date_format, $term );
					$timestamp        = $date_from_format ? strtotime( $date_from_format->format( 'Y-m-d' ) ) : strtotime( $term );

					if ( false !== $timestamp ) {
						$date      = gmdate( 'Y-m-d', $timestamp );
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
						$order_ids = $wpdb->get_col(
							$wpdb->prepare(
								"SELECT post_id FROM {$wpdb->postmeta} 
								WHERE ( meta_key = '_bkap_start' OR meta_key = '_bkap_end' ) 
								AND meta_value LIKE %s;",
								'%' . $wpdb->esc_like( gmdate( 'Ymd', $timestamp ) ) . '%'
							)
						);

						$booking_ids = $order_ids;
					}
				}

				// If the search is not for date, search for product name.
				if ( empty( $order_ids ) ) {
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
					$order_ids = $wpdb->get_col(
						$wpdb->prepare(
							"SELECT post_id
							FROM {$wpdb->postmeta}
							WHERE meta_key = '_bkap_product_id'
							AND meta_value IN (
								SELECT ID FROM {$wpdb->posts} 
								WHERE post_title LIKE %s
							)",
							'%' . $wpdb->esc_like( $term ) . '%'
						)
					);

					$booking_ids = $order_ids;
				}

				// ensure db query doesn't throw an error due to empty post_parent value.
				$order_ids = empty( $order_ids ) ? array( '-1' ) : $order_ids;

				// so we know we're doing this.
				if ( empty( $booking_ids ) ) {
					$booking_ids = array_merge(
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
						$wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_parent IN (" . implode( ',', $order_ids ) . ');' ),
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
						$wpdb->get_col(
							$wpdb->prepare(
								"SELECT ID
								FROM {$wpdb->posts}
								WHERE post_title LIKE %s
								OR ID = %d",
								'%' . $wpdb->esc_like( $term ) . '%',
								absint( $term )
							)
						),
						array( 0 ) // so we don't get back all results for incorrect search.
					);
				}
			}

			$data['filter']['s']        = false;
			$data['filter']['post__in'] = $booking_ids;
		}

		$arguments = wp_parse_args(
			isset( $data['filter'] ) ? $data['filter'] : array(),
			array(
				'fields'         => 'ids',
				'post_status'    => $this->default_post_status,
				'post_type'      => $this->post_type,
				'orderby'        => 'date',
				'posts_per_page' => $this->records_per_page,
			)
		);

		if ( isset( $data['filter'] ) && 'start_date' === $data['filter']['orderby'] ) {
			$arguments['orderby']  = 'meta_value_num';
			$arguments['meta_key'] = '_bkap_start'; // phpcs:ignore
		}

		if ( isset( $data['filter'] ) && 'end_date' === $data['filter']['orderby'] ) {
			$arguments['orderby']  = 'meta_value_num';
			$arguments['meta_key'] = '_bkap_end'; // phpcs:ignore
		}

		$query_bookings = new WP_Query( $arguments );

		$deposits_data = is_plugin_active( 'bkap-deposits/deposits.php' ) && 'on' === get_option( 'bkap_deposit_payment_view_bookings_fields', '' );
		$ticket_data   = is_plugin_active( 'bkap-printable-tickets/printable-tickets.php' );
		$bookings      = array();

		if ( 0 !== $query_bookings->post_count ) {

			$this->total_bookings = $query_bookings->found_posts;

			foreach ( $query_bookings->posts as $booking_id ) {

				$booking      = new BKAP_Booking( $booking_id );
				$booking_data = array(
					'booking_id'     => $booking->get_id(),
					'order_id'       => $booking->get_order_id(),
					'order_item_id'  => $booking->get_item_id(),
					'booking_status' => $booking->get_status(),
					'is_checked'     => false,
					'is_visible'     => false,
				);

				$status                         = $booking->status;
				$booking_data['status']         = $status;
				$booking_data['booked_product'] = '';
				$product                        = $booking->get_product();

				if ( $product ) {
					$product_title = $product->get_title();
					$resource_id   = $booking->get_resource();
					$variation_id  = $booking->get_variation_id();

					if ( 0 < $variation_id ) {
						$variation_obj      = new WC_Product_Variation( $variation_id );
						$variation_attr_cnt = count( $variation_obj->get_variation_attributes() );
						$product_variations = implode( ', ', $variation_obj->get_variation_attributes() );
						$product_title      = $product_title . ' - ' . $product_variations;
					}

					$booking_data['product_title']  = $product_title;
					$booking_data['product_name']   = $product_title;
					$booking_data['booked_product'] = '<a href="' . admin_url( 'post.php?post=' . ( is_callable( array( $product, 'get_id' ) ) ? $product->get_id() : $product->id ) . '&action=edit' ) . '">' . $product_title . '</a>';

					if ( '' !== $resource_id && '0' !== $resource_id ) {
						$resource_title                = $booking->get_resource_title();
						$booking_data['product_name'] .= '<br>( ' . esc_html( $resource_title ) . ' )';
						$show_resource                 = apply_filters( 'bkap_display_resource_info_on_view_booking', true, $product, $resource_id );

						if ( $show_resource ) {
							$booking_data['booked_product']      = $booking_data['booked_product'] . '<br>( <a href="' . admin_url( 'post.php?post=' . $resource_id . '&action=edit' ) . '">' . esc_html( $resource_title ) . '</a> )';
							$booking_data['booked_product_name'] = '<br>( ' . esc_html( $resource_title ) . ' )';
						}
					}
				}

				$customer                     = $booking->get_customer();
				$booking_data['customer_obj'] = $customer;
				$booking_data['booked_by']    = apply_filters( 'bkap_customer_name_on_view_booking', $customer->name, $customer, $booking );
				$booking_data['order']        = '';
				$order                        = $booking->get_order();

				if ( $order ) {
					$order_url             = bkap_order_url( $order->get_id() );
					$booking_data['order'] = '<a href="' . $order_url . '">#' . $order->get_order_number() . '</a><br> ' . esc_html( wc_get_order_status_name( $order->get_status() ) );
				}

				$booking_data['start_date'] = $booking->get_start_date() . '<br>' . $booking->get_start_time();
				$booking_data['end_date']   = $booking->get_end_date() . '<br>' . $booking->get_end_time();
				$global_settings            = bkap_global_setting();
				$start_date                 = $booking->get_start_date( $global_settings );
				$get_start_time             = $booking->get_start_time( $global_settings );

				if ( '' !== $get_start_time ) {
					$start_date .= ' - ' . $get_start_time;
				}

				$booking_data['_start_date'] = $start_date;
				$end_date                    = '';
				$get_end_date                = $booking->get_end_date( $global_settings );

				if ( '' !== $get_end_date ) {
					$end_date     = $get_end_date;
					$get_end_time = $booking->get_end_time( $global_settings );

					if ( '' !== $get_end_time ) {
						$end_date .= ' - ' . $get_end_time;
					}
				}

				$booking_data['_end_date'] = $end_date;
				$booking_data['persons']   = '';
				$persons                   = $booking->persons;

				if ( count( $persons ) > 0 ) {
					if ( isset( $persons[0] ) ) {
						$booking_data['persons'] = BKAP_Person::bkap_get_person_label( $booking->product_id ) . ' : ' . $persons[0] . '<br>';
					} else {
						foreach ( $persons as $key => $value ) {
							$booking_data['persons'] .= get_the_title( $key ) . ' : ' . $value . '<br>';
						}
					}
				}

				$booking_cost                 = (float) $booking->cost;
				$booking_data['persons_info'] = $booking->get_persons_info();
				$booking_data['quantity']     = $booking->qty;
				$booking_data['amount']       = wc_price( $booking_cost * $booking->qty );
				$booking_data['order_date']   = $booking->get_date_created();

				// Final Amount.
				$final_amt = $booking_cost * $booking->qty;
				$currency  = get_woocommerce_currency();

				if ( absint( $booking->order_id ) > 0 ) {
					$order = wc_get_order( $booking->order_id );
					if ( $order ) {
						$currency = ( version_compare( WOOCOMMERCE_VERSION, '3.0.0' ) < 0 ) ? $order->get_order_currency() : $order->get_currency();
					}
				}

				$booking_data['final_amount']      = wc_price( $final_amt, array( 'currency' => $currency ) );
				$booking_data['zoom_meeting_link'] = '';
				$meeting_link                      = $booking->get_zoom_meeting_link();
				$booking_data['zoom_meeting']      = $meeting_link;

				if ( '' !== $meeting_link ) {
					$booking_data['zoom_meeting_link'] = sprintf( '<a href="%s" target="_blank"><span class="dashicons dashicons-video-alt2"></span></a>', $meeting_link );
				}

				$booking_data['actions'] = array(
					'view'   => array(
						'url'    => admin_url( 'post.php?post=' . $booking_id . '&action=edit' ),
						'name'   => __( 'View', 'woocommerce-booking' ),
						'action' => 'view',
					),
					'delete' => array(
						'name'    => __( 'Delete', 'woocommerce-booking' ),
						'action'  => 'delete',
						'use_vue' => true,
					),
				);

				if ( 'pending-confirmation' === $status ) {
					$booking_data['actions']['confirm'] = array(
						'name'    => __( 'Confirm', 'woocommerce-booking' ),
						'action'  => 'confirm',
						'use_vue' => true,
					);
				}

				$booking_data['actions'] = apply_filters( 'bkap_view_bookings_actions', $booking_data['actions'], $booking_data );

				/* Adding Partial Deposits Columns Data */

				if ( $deposits_data ) {
					$pd_data                                 = bkap_partial_deposits_data_on_view_booking( $booking );
					$booking_data['remaining_balance_order'] = $pd_data['remaining_balance_order'];
					$booking_data['payment_status']          = $pd_data['payment_status'];
				}

				/* Adding Printable Tickets Column Data */
				if ( $ticket_data ) {
					$ticket_list = '';
					$tickets     = get_post_meta( $booking_id, '_bkap_ticket_id', true );
					if ( is_array( $tickets ) && count( $tickets ) > 0 ) {
						$ticket_list = implode( '<br>', $tickets );
					}
					$booking_data['ticket_id'] = $ticket_list;

					$code_list = '';
					$codes     = get_post_meta( $booking_id, '_bkap_security_code', true );
					if ( is_array( $codes ) && count( $codes ) > 0 ) {
						$code_list = implode( '<br>', $codes );
					}
					$booking_data['security_code'] = $code_list;
				}
				$booking_data = apply_filters( 'bkap_view_booking_individual_data', $booking_data, $booking, $booking_id );
				$bookings[] = $booking_data;
			}
		}

		$this->booking_data = $bookings;
	}

	/**
	 * Views.
	 *
	 * @since 5.19.0
	 */
	public function get_views() {

		global $wpdb;

		$views = array(
			'all'                  => sprintf(
				'<a href="%s"%s>%s</a>',
				'javascript:void(0);',
				' class="view-bookings-action' . ( 'all' === $this->post_status ? ' bold' : '' ) . '" data-action="set-status" data-status="all"',
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
				__( 'All', 'woocommerce-booking' ) . '&nbsp;<span class="count">(' . $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = %s", $this->post_type ) ) . ')</span>'
			),
			'paid'                 => sprintf(
				'<a href="%s"%s>%s</a>',
				'javascript:void(0);',
				' class="view-bookings-action' . ( 'paid' === $this->post_status ? ' bold' : '' ) . '" data-action="set-status" data-status="paid"',
				__( 'Paid & Confirmed', 'woocommerce-booking' ) . '&nbsp;<span class="count">(' . $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = %s AND post_status = %s", $this->post_type, 'paid' ) ) . ')</span>'
			),
			'confirmed'            => sprintf(
				'<a href="%s"%s>%s</a>',
				'javascript:void(0);',
				' class="view-bookings-action' . ( 'confirmed' === $this->post_status ? ' bold' : '' ) . '" data-action="set-status" data-status="confirmed"',
				__( 'Confirmed', 'woocommerce-booking' ) . '&nbsp;<span class="count">(' . $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = %s AND post_status = %s", $this->post_type, 'confirmed' ) ) . ')</span>'
			),
			'pending_confirmation' => sprintf(
				'<a href="%s"%s>%s</a>',
				'javascript:void(0);',
				' class="view-bookings-action' . ( 'pending-confirmation' === $this->post_status ? ' bold' : '' ) . '" data-action="set-status" data-status="pending-confirmation"',
				__( 'Pending Confirmation', 'woocommerce-booking' ) . '&nbsp;<span class="count">(' . $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = %s AND post_status = %s", $this->post_type, 'pending-confirmation' ) ) . ')</span>'
			),
			'cancelled'            => sprintf(
				'<a href="%s"%s>%s</a>',
				'javascript:void(0);',
				' class="view-bookings-action' . ( 'cancelled' === $this->post_status ? ' bold' : '' ) . '" data-action="set-status" data-status="cancelled"',
				__( 'Cancelled', 'woocommerce-booking' ) . '&nbsp;<span class="count">(' . $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = %s AND post_status = %s", $this->post_type, 'cancelled' ) ) . ')</span>'
			),
			'trash'                => sprintf(
				'<a href="%s"%s>%s</a>',
				'javascript:void(0);',
				' class="view-bookings-action' . ( 'trash' === $this->post_status ? ' bold' : '' ) . '" data-action="set-status" data-status="trash"',
				__( 'Trashed', 'woocommerce-booking' ) . '&nbsp;<span class="count">(' . $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = %s AND post_status = %s", $this->post_type, 'trash' ) ) . ')</span>'
			),
		);

		return $views;
	}

	/**
	 * Gets the current page number.
	 *
	 * @since 5.19.0
	 *
	 * @return int
	 */
	public function get_pagenum() {
		$pagenum = $this->page;

		if ( isset( $this->_pagination_args['total_pages'] ) && $pagenum > $this->_pagination_args['total_pages'] ) {
			$pagenum = $this->_pagination_args['total_pages'];
		}

		return max( 1, $pagenum );
	}

	/**
	 * Displays the search box.
	 *
	 * @since 5.19.0
	 *
	 * @param string $text     The 'submit' button label.
	 * @param string $input_id ID attribute value for the search input field.
	 */
	public function search_box( $text, $input_id ) {
		if ( ! $this->has_items() ) {
			return;
		}

		$input_id = $input_id . '-search-input';

		?>
<p class="search-box mb-3">
	<label class="screen-reader-text"
        for="<?php echo esc_attr( $input_id ); ?>"><?php echo $text; // phpcs:ignore ?>:</label>
	<input type="search" @keyup.enter="bkap_search_bookings" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>" />
		<?php submit_button( $text, 'button trietary-btn reverse', '', false, array( 'id' => 'view-bookings-search' ) ); ?>
</p>
<div class="bkap-print-csv-atgc mb-3">
	<button class="button trietary-btn reverse" type="button" id="button_view_bookings_print">Print</button>
	<button class="button trietary-btn reverse" type="button" id="button_view_bookings_csv">CSV</button>
	<button class="button trietary-btn reverse" v-if="'undefined' != typeof data.settings.add_to_google_calendar && data.settings.add_to_google_calendar.total_bookings_to_export > 0" @click="bkap_add_bookings_to_gcal" type="button" id="bkap_add_bookings_to_gcal">Add to Google Calendar</button>
</div>
	
		<?php
	}

	/**
	 * Message to be displayed when there are no items found.
	 *
	 * @since 5.19.0
	 */
	public function no_items() {
		esc_html_e( 'No bookings have been found.', 'woocommerce-booking' );
	}

	/**
	 * Displays the rendered table.
	 *
	 * @since 5.19.0
	 */
	public function display() {

		if ( isset( $this->_pagination_args['paged'] ) ) {
			echo '<input type="hidden" id="page" name="page" value="' . esc_attr( $this->_pagination_args['paged'] ) . '" />';
		}

		if ( isset( $this->_pagination_args['order'] ) ) {
			echo '<input type="hidden" id="order" name="order" value="' . esc_attr( $this->_pagination_args['order'] ) . '" />';
		}

		if ( isset( $this->_pagination_args['orderby'] ) ) {
			echo '<input type="hidden" id="orderby" name="orderby" value="' . esc_attr( $this->_pagination_args['orderby'] ) . '" />';
		}

		echo '<input type="hidden" id="booking_status" name="booking_status" value="all" />';

		parent::display();
	}

	/**
	 * Bulk actions.
	 *
	 * @since 5.19.0
	 */
	protected function get_bulk_actions() {

		return array(
			'bulk_action_delete_booking'  => __( 'Delete Booking', 'woocommerce-booking' ),
			'bulk_action_confirm_booking' => __( 'Confirm Booking', 'woocommerce-booking' ),
			'bulk_action_cancel_booking'  => __( 'Cancel Booking', 'woocommerce-booking' ),
		);
	}

	/**
	 * Renders the table for display in AJAX request.
	 *
	 * @since 5.19.0
	 */
	public function ajax_response() {
		$this->prepare_items();

		extract( $this->_args );
		extract( $this->_pagination_args, EXTR_SKIP );

		ob_start();
		$this->display_rows_or_placeholder();
		$rows = ob_get_clean();

		ob_start();
		$this->print_column_headers();
		$headers = ob_get_clean();

		ob_start();
		$this->pagination( 'top' );
		$pagination_top = ob_get_clean();

		ob_start();
		$this->pagination( 'bottom' );
		$pagination_bottom = ob_get_clean();

		ob_start();
		$this->views();
		$views = ob_get_clean();

		$response                         = array( 'rows' => $rows );
		$response['pagination']['top']    = $pagination_top;
		$response['pagination']['bottom'] = $pagination_bottom;
		$response['column_headers']       = $headers;
		$response['views']                = $views;

		if ( isset( $total_items ) ) {
			/* Translators: %s Total Items */
			$response['total_items_i18n'] = sprintf( _n( '%s item', '%s items', $total_items, 'woocommerce-booking' ), number_format_i18n( $total_items ) );
		}

		if ( isset( $total_pages ) ) {
			$response['total_pages']      = $total_pages;
			$response['total_pages_i18n'] = number_format_i18n( $total_pages );
		}

		return $response;
	}

	/**
	 * Filter - Dropdown Dates.
	 *
	 * @since 5.19.0
	 */
	public function filter_dropdown_dates() {

		global $wpdb, $wp_locale;

		$dates = array();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
		$months = $wpdb->get_results(
			"SELECT DISTINCT YEAR( meta_value ) AS year, MONTH(meta_value) AS month FROM {$wpdb->prefix}woocommerce_order_itemmeta
                    WHERE meta_key = '_wapbk_booking_date' OR meta_key = '_wapbk_checkout_date' ORDER BY meta_value DESC"
		);

		$dates['all_dates'] = __( 'All dates', 'woocommerce-booking' );

		foreach ( $months as $item ) {

			if ( empty( $item->month ) ) {
				continue;
			}

			$month = zeroise( $item->month, 2 );
			$year  = $item->year;

			$dates[ $year . $month ] = $wp_locale->get_month( $month ) . ' ' . $year;
		}

		return $dates;
	}

	/**
	 * Filter - Bookable Products.
	 *
	 * @since 5.19.0
	 */
	public static function filter_bookable_products() {

		global $wpdb;

		// Due to 2918 we have hard coded code to get all bookable products.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
		$product = $wpdb->get_results(
			"SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_bkap_enable_booking' AND meta_value = 'on'"
		);

		$products = array();
		$output   = array();

		if ( count( $product ) > 0 ) {
			foreach ( $product as $key => $value ) {
				$theid      = $value->post_id;
				$thetitle   = get_the_title( $theid );
				$products[] = array( $thetitle, $theid );
			}
			sort( $products );
		}

		$products = apply_filters( 'bkap_all_bookable_products_dropdown', $products );

		$output['all_bookable_products'] = __( 'All Bookable Products', 'woocommerce-booking' );

		if ( is_array( $products ) && count( $products ) > 0 ) {

			foreach ( $products as $product ) {
				$output[ absint( $product[1] ) ] = $product[0];
			}
		}

		return $output;
	}

	/**
	 * Filter - List Booking By.
	 *
	 * @since 5.19.0
	 */
	public static function filter_list_booking_by() {
		return apply_filters(
			'bkap_list_booking_by_dropdown',
			array(
				'default'        => __( 'Default Listing', 'woocommerce-booking' ),
				'today_onwards'  => __( 'Today Onwards', 'woocommerce-booking' ),
				'today_checkin'  => __( 'Today\'s Check-ins', 'woocommerce-booking' ),
				'today_checkout' => __( 'Today\'s Checkouts', 'woocommerce-booking' ),
				'gcal'           => __( 'Imported Bookings', 'woocommerce-booking' ),
				'custom_dates'   => __( 'Custom Dates', 'woocommerce-booking' ),
			)
		);
	}

	/**
	 * Booking Filter options.
	 *
	 * @param string $which Position of table nav.
	 *
	 * @since 5.19.0
	 */
	protected function extra_tablenav( $which ) {
		?>
<div class="alignleft actions">
		<?php
		if ( 'top' === $which ) {
			?>

	<div class="rc-flx-wrap d-flex flx-aln-center mb-2">
		<div class="select-box">
			<select id="filter_dropdown_dates" class="ib-small">
				<?php
				foreach ( $this->filter_dropdown_dates() as $key => $option ) {
					?>
				<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $option ); ?></option>
					<?php
				}
				?>
			</select>
		</div>

		<div class="select-box">
			<select id="filter_bookable_products" class="ib-small">
				<?php
				foreach ( $this->filter_bookable_products() as $key => $option ) {
					?>
				<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $option ); ?></option>
					<?php
				}
				?>
			</select>
		</div>

		<div class="select-box choices_search_select_box">
			<select class="ib-md view_bookings_customer_search">
				<option value="" disabled><?php esc_attr_e( 'Select customer', 'woocommerce-booking' ); ?></option>
			</select>
		</div>

		<div class="select-box">
			<select id="filter_list_booking_by" class="ib-small">
				<?php
				foreach ( $this->filter_list_booking_by() as $key => $option ) {
					?>
				<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $option ); ?></option>
					<?php
				}
				?>
			</select>
			<div>
				<input class="ib-md filter_list_booking_by_input start_date" title="<?php echo esc_html__( 'Start Date', 'woocommerce-booking' ); ?>" type="date" />
				<input class="ib-md filter_list_booking_by_input end_date"  title="<?php echo esc_html__( 'End Date', 'woocommerce-booking' ); ?>" type="date" />
			</div>
		</div>

		<div class="select-box">
			<button class="trietary-btn reverse" type="button"
				id="button_view_bookings_apply_filters"><?php esc_attr_e( 'Apply filters', 'woocommerce-booking' ); ?></button>
		</div>
	</div>
</div>
			<?php
		}
	}

	/**
	 * Gets a list of CSS classes for the WP_List_Table table tag.
	 *
	 * @since 5.19.0
	 *
	 * @return string[] Array of CSS classes for the table tag.
	 */
	protected function get_table_classes() {
		return array( 'widefat', 'striped', 'table-view-list', $this->_args['plural'] );
	}

	/**
	 * Prints column headers.
	 *
	 * @since 5.19.0
	 *
	 * @param bool $with_id Whether to set the ID attribute or not.
	 */
	public function print_column_headers( $with_id = true ) {
		list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

		$http_host       = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
		$request_uri     = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$current_url     = set_url_scheme( 'http://' . $http_host . $request_uri );
		$current_url     = remove_query_arg( 'paged', $current_url );
		$current_orderby = $this->sort_orderby;
		$current_order   = $this->sort_order;

		if ( ! empty( $columns['cb'] ) ) {
			static $cb_counter = 1;
			$columns['cb']     = '<input id="cb-select-all-' . $cb_counter . '" type="checkbox" />
			<label for="cb-select-all-' . $cb_counter . '">' .
				'<span class="screen-reader-text">' .
					/* translators: Hidden accessibility text. */
					__( 'Select All', 'woocommerce-booking' ) .
				'</span>' .
				'</label>';
			++$cb_counter;
		}

		foreach ( $columns as $column_key => $column_display_name ) {
			$class          = array( 'manage-column', "column-$column_key" );
			$aria_sort_attr = '';
			$abbr_attr      = '';
			$order_text     = '';

			if ( in_array( $column_key, $hidden, true ) ) {
				$class[] = 'hidden';
			}

			if ( 'cb' === $column_key ) {
				$class[] = 'check-column';
			} elseif ( in_array( $column_key, array( 'posts', 'comments', 'links' ), true ) ) {
				$class[] = 'num';
			}

			if ( $column_key === $primary ) {
				$class[] = 'column-primary';
			}

			if ( isset( $sortable[ $column_key ] ) ) {
				$orderby       = isset( $sortable[ $column_key ][0] ) ? $sortable[ $column_key ][0] : '';
				$desc_first    = isset( $sortable[ $column_key ][1] ) ? $sortable[ $column_key ][1] : false;
				$abbr          = isset( $sortable[ $column_key ][2] ) ? $sortable[ $column_key ][2] : '';
				$orderby_text  = isset( $sortable[ $column_key ][3] ) ? $sortable[ $column_key ][3] : '';
				$initial_order = isset( $sortable[ $column_key ][4] ) ? $sortable[ $column_key ][4] : '';

				/*
				 * We're in the initial view and there's no $_GET['orderby'] then check if the
				 * initial sorting information is set in the sortable columns and use that.
				 */
				if ( '' === $current_orderby && $initial_order ) {
					// Use the initially sorted column $orderby as current orderby.
					$current_orderby = $orderby;
					// Use the initially sorted column asc/desc order as initial order.
					$current_order = $initial_order;
				}

				/*
				 * True in the initial view when an initial orderby is set via get_sortable_columns()
				 * and true in the sorted views when the actual $_GET['orderby'] is equal to $orderby.
				 */
				if ( $current_orderby === $orderby ) {
					// The sorted column. The `aria-sort` attribute must be set only on the sorted column.
					if ( 'asc' === $current_order ) {
						$order          = 'desc';
						$aria_sort_attr = ' aria-sort="ascending"';
					} else {
						$order          = 'asc';
						$aria_sort_attr = ' aria-sort="descending"';
					}

					$class[] = 'sorted';
					$class[] = $current_order;
				} else {
					// The other sortable columns.
					$order = strtolower( $desc_first );

					if ( ! in_array( $order, array( 'desc', 'asc' ), true ) ) {
						$order = $desc_first ? 'desc' : 'asc';
					}

					$class[] = 'sortable';
					$class[] = 'desc' === $order ? 'asc' : 'desc';

					/* translators: Hidden accessibility text. */
					$asc_text = __( 'Sort ascending.', 'woocommerce-booking' );
					/* translators: Hidden accessibility text. */
					$desc_text  = __( 'Sort descending.', 'woocommerce-booking' );
					$order_text = 'asc' === $order ? $asc_text : $desc_text;
				}

				if ( '' !== $order_text ) {
					$order_text = ' <span class="screen-reader-text">' . $order_text . '</span>';
				}

				// Print an 'abbr' attribute if a value is provided via get_sortable_columns().
				$abbr_attr = $abbr ? ' abbr="' . esc_attr( $abbr ) . '"' : '';

				$column_display_name = sprintf(
					'<a href="%1$s">' .
						'<span>%2$s</span>' .
						'<span class="sorting-indicators">' .
							'<span class="sorting-indicator asc" aria-hidden="true"></span>' .
							'<span class="sorting-indicator desc" aria-hidden="true"></span>' .
						'</span>' .
						'%3$s' .
					'</a>',
					esc_url( add_query_arg( compact( 'orderby', 'order' ), $current_url ) ),
					$column_display_name,
					$order_text
				);
			}

			$tag   = ( 'cb' === $column_key ) ? 'td' : 'th';
			$scope = ( 'th' === $tag ) ? 'scope="col"' : '';
			$id    = $with_id ? "id='$column_key'" : '';

			if ( ! empty( $class ) ) {
				$class = "class='" . implode( ' ', $class ) . "'";
			}

			printf(
				'<%1$s %2$s %3$s %4$s %5$s>%6$s</%1$s>',
				wp_kses_post( $tag ),
				wp_kses_post( $scope ),
				wp_kses_post( $id ),
				wp_kses_post( $class ),
				wp_kses_post( $abbr_attr . ' ' . $aria_sort_attr ),
				wp_kses_post( $column_display_name )
			);
		}
	}
}
