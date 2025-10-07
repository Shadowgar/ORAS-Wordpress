<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * Import Booking: Displays Google Events in a Tabular Format.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/Includes/ImportBookingTable
 * @category    Classes
 * @since       5.19.0
 */

defined( 'ABSPATH' ) || exit;

// Load WP_List_Table if not loaded.
if ( ! class_exists( 'WP_List_Table' ) ) {
	BKAP_Files::include_file( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Import Booking Table.
 *
 * @since 5.19.0
 */
class BKAP_Admin_Import_Booking_Table extends WP_List_Table {

	/**
	 * Records per page.
	 *
	 * @var int
	 * @since 5.19.0
	 */
	public $records_per_page = 10;

	/**
	 * Current page number.
	 *
	 * @var int
	 * @since 5.19.0
	 */
	public $page = 1;

	/**
	 * Total number of events.
	 *
	 * @var int
	 * @since 5.19.0
	 */
	private $total_events = 0;

	/**
	 * Total number of un-mapped events.
	 *
	 * @var int
	 * @since 5.19.0
	 */
	private $total_unmapped_events = 0;

	/**
	 * Google Event data.
	 *
	 * @var array
	 * @since 5.19.0
	 */
	private $google_event_data = array();

	/**
	 * Google Event data.
	 *
	 * @var array
	 * @since 5.19.0
	 */
	private $products = array();

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
	 * Custom Post type.
	 *
	 * @var string $post_type
	 */
	public $post_type = 'bkap_gcal_event';

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
				'singular' => 'Google event',
				'plural'   => 'Google events',
				'ajax'     => true,
			)
		);

		$this->products = bkap_common::get_woocommerce_product_list();
	}

	/**
	 * Table Columns.
	 *
	 * @return array
	 * @since 5.19.0
	 */
	public function get_columns() {
		return array(
			'bkap_import_booking_event_summary' => __( 'Event Summary', 'woocommerce-booking' ),
			'bkap_import_booking_description '  => __( 'Description', 'woocommerce-booking' ),
			'bkap_import_booking_start_date'    => __( 'Start Date', 'woocommerce-booking' ),
			'bkap_import_booking_end_date'      => __( 'End Date', 'woocommerce-booking' ),
			'bkap_import_booking_timeslot'      => __( 'Timeslot', 'woocommerce-booking' ),
			'bkap_import_booking_reason'        => __( 'Reason for Failure', 'woocommerce-booking' ),
			'bkap_import_booking_product'       => __( 'Product', 'woocommerce-booking' ),
			'bkap_import_booking_actions'       => __( 'Actions', 'woocommerce-booking' ),
		);
	}

	/**
	 * Table Column Definition.
	 *
	 * @param object $item Google Event data.
	 * @since  5.19.0
	 */
	public function single_row( $item ) {

		$row_id = 'post-' . $item->id;

		echo '<tr id="' . esc_attr( $row_id ) . '">';

		$this->single_row_columns( $item );

		echo '</tr>';
	}

	/**
	 * Table Column Definition.
	 *
	 * @param  array  $event Google Event data.
	 * @param  stirng $column Column.
	 * @since  5.19.0
	 */
	public function column_default( $event, $column ) {
		$status = $event->get_status();

		switch ( $column ) {

			case 'bkap_import_booking_event_summary':
				return esc_html( $event->summary );

			case 'bkap_import_booking_description':
				return esc_html( $event->description );

			case 'bkap_import_booking_start_date':
				return $event->get_start_date();

			case 'bkap_import_booking_end_date':
				return $event->get_end_date();

			case 'bkap_import_booking_timeslot':
				if ( $event->get_start_date() === $event->get_end_date() || '' === $event->get_end_date() ) {
					$start_time = $event->get_start_time();
					$end_time   = $event->get_end_time();

					return '' === $end_time ? $start_time : $start_time . '-' . $end_time;
				}

				return '';
			case 'bkap_import_booking_reason':
				return $event->get_failed_reason();

			case 'bkap_import_booking_product':
				if ( 'bkap-unmapped' === $status ) {
					$product_list = $this->products;
					$user         = new WP_User( get_current_user_id() );

					$default_text = __( 'Select a Product', 'woocommerce-booking' );
					$value        = '<select style="max-width:180px;width:100%;" id="import_event_' . $event->id . '">';
					$value       .= '<option value="" > ' . $default_text . '</option>';

					if ( 'tour_operator' === $user->roles[0] ) {

						foreach ( $product_list as $k => $v ) {
							$booking_setting = get_post_meta( $v[1], 'woocommerce_booking_settings', true );
							$tour_id         = ( isset( $booking_setting['booking_tour_operator'] ) && '' != $booking_setting['booking_tour_operator'] ) ? $booking_setting['booking_tour_operator'] : '';

							if ( get_current_user_id() === $tour_id ) {
								$value .= '<option value="' . $v[1] . '" >' . $v[0] . '</option>';
							}
						}
					} else {
						foreach ( $product_list as $k => $v ) {
							$value .= '<option value="' . $v[1] . '" >' . $v[0] . '</option>';
						}
					}

					$value .= '</select>';
					return $value;
				}
				return '';

			case 'bkap_import_booking_actions':
				if ( 'bkap-mapped' === $status ) {
					return '<p><b>' . __( 'This event is mapped.', 'woocommerce-booking' ) . '</b></p>';
				}

				$default_text = __( 'Map Event', 'woocommerce-booking' );
				$value        = '<input type="button" class="save_button button-primary map_event" name="map_event_' . $event->id . '" value="' . $default_text . '" disabled="disabled">';
				$value       .= '<img src="' . plugins_url() . '/woocommerce-booking/assets/images/ajax-loader.gif" id="event_ajax_loader_' . $event->id . '" style="display:none">';
				return $value;
			default:
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
			'bkap_import_booking_event_summary' => array( 'bkap_import_booking_event_summary', false ),
			'bkap_import_booking_start_date'    => array( 'bkap_import_booking_start_date', false ),
			'bkap_import_booking_end_date'      => array( 'bkap_import_booking_end_date', false ),
		);
	}

	/**
	 * Prepares/Renders Table View.
	 *
	 * @since 5.19.0
	 */
	public function prepare_items() {
		$columns               = $this->get_columns();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, array(), $sortable );
		$data                  = $this->google_event_data;
		$this->items           = $data;

		$this->set_pagination_args(
			array(
				'total_items' => $this->total_events,
				'per_page'    => $this->records_per_page,
				'total_pages' => ceil( $this->total_events / $this->records_per_page ),
				'orderby'     => $this->sort_orderby,
				'order'       => $this->sort_order,
			)
		);
	}

	/**
	 * Populate Event Data.
	 *
	 * @param array $options Filter Options.
	 * @since 5.19.0
	 */
	public function populate_data( $options = array() ) {
		$args = array();

			$meta_args = array();

		if ( isset( $options['orderby'] ) ) {
			$this->sort_orderby = $options['orderby'];
			$args['orderby']    = $options['orderby'];
		}

		if ( isset( $options['order'] ) && 'desc' === $options['order'] ) {
			$this->sort_order = 'desc';
			$args['order']    = 'DESC';
		}

		if ( isset( $options['paged'] ) ) {
			$args['paged'] = $options['paged'];
		}

		if ( isset( $options['order'] ) && 'asc' === $options['order'] ) {
			$this->sort_order = 'asc';
			$args['order']    = 'ASC';
		}

		if ( isset( $options['page'] ) ) {
			if ( is_numeric( $options['page'] ) ) {
				$this->page     = (int) $options['page'];
				$args['offset'] = ( $this->page - 1 ) * $this->records_per_page;
			}
		}

		$this->post_status   = isset( $options['status'] ) && '' !== $options['status'] ? $options['status'] : 'all';
		$args['post_status'] = isset( $options['status'] ) && 'all' !== $options['status'] ? $options['status'] : array( 'bkap-mapped', 'bkap-unmapped' );

		// strtotime does not support all date formats. hence it is suggested to use the "DateTime date_create_from_format".
		$date_formats    = bkap_common::get_booking_global_value( 'bkap_date_formats' );
		$global_settings = bkap_json_decode( get_option( 'woocommerce_booking_global_settings' ) ); // get the global settings to find the date formats.
		$date_format_set = $date_formats[ $global_settings->booking_date_format ];

		$search_string = isset( $options['search'] ) ? $options['search'] : '';

		if ( '' !== $search_string ) {

			$args['meta_query'][] = array(
				'key'     => '_bkap_summary',
				'value'   => sanitize_text_field( $search_string ),
				'compare' => 'LIKE',
			);

			$date_formatted = date_create_from_format( $date_format_set, $search_string );

			if ( $date_formatted && '' !== $date_formatted ) {
				$meta_args      = array();
				$date_strtotime = strtotime( date_format( $date_formatted, 'Y-m-d' ) );

				$meta_args[] = array(
					'key'     => '_bkap_start',
					'value'   => sanitize_text_field( $date_strtotime ),
					'compare' => 'LIKE',
				);

				$meta_args[] = array(
					'key'     => '_bkap_end',
					'value'   => sanitize_text_field( $date_strtotime ),
					'compare' => 'LIKE',
				);

				$args['meta_query'] = array_merge( // phpcs:ignore
					array(
						'relation' => 'OR',
					),
					$meta_args
				);
			}
		}

		$data  = array();
		$posts = new WP_Query(
			wp_parse_args(
				$args,
				array(
					'fields'         => 'ids',
					'post_type'      => $this->post_type,
					'orderby'        => 'date',
					'order'          => 'DESC',
					'posts_per_page' => $this->records_per_page,
				)
			)
		);

		if ( 0 !== $posts->post_count ) {
			$this->total_events = $posts->found_posts;

			foreach ( $posts->posts as $event_id ) {
				$data[] = new BKAP_Google_Calendar_Event( $event_id );
			}
		}

		$this->google_event_data = $data;
	}

	/**
	 * Views.
	 *
	 * @since 5.19.0
	 */
	public function get_views() {
		global $wpdb;

		return array(
			'all'           => sprintf(
				'<a href="%s"%s>%s</a>',
				'javascript:void(0);',
				' class="import-booking-action' . ( 'all' === $this->post_status ? ' bold' : '' ) . '" data-action="set-status" data-status="all"',
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
				__( 'All', 'woocommerce-booking' ) . '&nbsp;<span class="count">(' . $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = %s AND ( post_status = %s OR post_status = %s )", $this->post_type, 'bkap-mapped', 'bkap-unmapped' ) ) . ')</span>'
			),
			'bkap-mapped'   => sprintf(
				'<a href="%s"%s>%s</a>',
				'javascript:void(0);',
				' class="import-booking-action' . ( 'bkap-mapped' === $this->post_status ? ' bold' : '' ) . '" data-action="set-status" data-status="bkap-mapped"',
				__( 'Mapped', 'woocommerce-booking' ) . '&nbsp;<span class="count">(' . $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = %s AND post_status = %s", $this->post_type, 'bkap-mapped' ) ) . ')</span>'
			),
			'bkap-unmapped' => sprintf(
				'<a href="%s"%s>%s</a>',
				'javascript:void(0);',
				' class="import-booking-action' . ( 'bkap-unmapped' === $this->post_status ? ' bold' : '' ) . '" data-action="set-status" data-status="bkap-unmapped"',
				__( 'Un-mapped', 'woocommerce-booking' ) . '&nbsp;<span class="count">(' . $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = %s AND post_status = %s", $this->post_type, 'bkap-unmapped' ) ) . ')</span>'
			),
		);
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
	 * Displays the Google Event search box.
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
<p class="search-box">
	<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $text ); ?>:</label>
	<input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>" />
		<?php submit_button( $text, 'secondary-btn', '', false, array( 'id' => 'google-event-search' ) ); ?>
</p>
		<?php
	}

	/**
	 * Message to be displayed when there are no items found.
	 *
	 * @since 5.19.0
	 */
	public function no_items() {
		esc_html_e( 'No Google Events found.', 'woocommerce-booking' );
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

		echo '<input type="hidden" id="import_booking_status" name="import_booking_status" value="all" />';

		parent::display();
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
		$this->views();
		$views = ob_get_clean();

		ob_start();
		$this->display_tablenav( 'top' );
		$navigation_top = ob_get_clean();

		ob_start();
		$this->display_tablenav( 'bottom' );
		$navigation_bottom = ob_get_clean();

		$response                         = array( 'rows' => $rows );
		$response['column_headers']       = $headers;
		$response['views']                = $views;
		$response['navigation']['top']    = $navigation_top;
		$response['navigation']['bottom'] = $navigation_bottom;

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
	 * @param bool $with_id Whether to set the ID attribute or not
	 */
	public function print_column_headers( $with_id = true ) {
		list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

		$http_host   = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

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

					$asc_text   = __( 'Sort ascending.', 'woocommerce-booking' );
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
