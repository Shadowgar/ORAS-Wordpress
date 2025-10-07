<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * Resources Table.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/Includes/ViewResourcesTable
 * @category    Classes
 * @since       5.19.0
 */

defined( 'ABSPATH' ) || exit;

// Load WP_List_Table if not loaded.
if ( ! class_exists( 'WP_List_Table' ) ) {
	BKAP_Files::include_file( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Resources Table.
 *
 * @since 5.19.0
 */
class BKAP_Admin_View_Resources_Table extends WP_List_Table {

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
	 * Total number of resources.
	 *
	 * @var int
	 * @since 5.19.0
	 */
	private $total_resources = 0;

	/**
	 * Resources data.
	 *
	 * @var array
	 * @since 5.19.0
	 */
	private $resources_data = array();

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
	public $post_type = 'bkap_resource';

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
				'singular' => 'Resource',
				'plural'   => 'Resources',
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
		return array(
			'checkbox'       => '
					<div class="custom-control custom-checkbox">
						<input type="checkbox" class="custom-control-input resources_checkbox checkbox_select_all" >
						<label class="custom-control-label"
							for="checkbox_select_all"></label>
					</div>
				',
			'title'          => __( 'Resource Title', 'woocommerce-booking' ),
			'no_of_products' => __( 'No. of Products', 'woocommerce-booking' ),
			'date'           => __( 'Date', 'woocommerce-booking' ),
			'actions'        => __( 'Actions', 'woocommerce-booking' ),
		);
	}

	/**
	 * Adding resource data to the different data properties of the row in the table.
	 *
	 * @param object $item Resource data.
	 *
	 * @since 5.19.0
	 */
	public function single_row( $item ) {

		$row_id = 'row-resource-id-' . $item->get_id();

		echo '<tr id="' . esc_attr( $row_id ) . '">';

		$this->single_row_columns( $item );

		echo '</tr>';
	}

	/**
	 * Table Column Definition.
	 *
	 * @param obj    $resource Resource data.
	 * @param stirng $column Column.
	 * @since 5.19.0
	 */
	public function column_default( $resource, $column ) {
		$resource_id    = $resource->get_id();
		$resource_title = $resource->get_resource_title();

		switch ( $column ) {

			case 'checkbox':
				return '
					<div class="custom-control custom-checkbox resources_checkbox">
						<input type="checkbox" class="custom-control-input" id="checkbox_' . $resource_id . '" data-resource-id="' . $resource_id . '" />
						<label class="custom-control-label" for="checkbox_' . $resource_id . '" data-resource-id="' . $resource_id . '"></label>
					</div>
				';

			case 'title':
				return '<span class="resource-title resources-action" title="Edit" data-action="edit" data-resource-id="' . $resource_id . '">' . $resource_title . '</span><div class="row-actions" style="display:inline-block;"><span class="edit">&nbsp;- ID: ' . $resource_id . '</span></div>';

			case 'no_of_products':
				$count = count( bkap_product_ids_from_resource_id( $resource_id ) );
				if ( $count > 0 ) {
					$url = admin_url() . 'edit.php?post_type=product&bkap_resource_id=' . $resource_id;
					return sprintf( '<a href="%s" title="">%d</a>', $url, $count );
				} else {
					return '0';
				}

			case 'date':
				$date         = '';
				$resource_obj = get_post( $resource_id );

				if ( $resource_obj ) {
					$date = $resource_obj->post_date;
				}

				return $date;

			case 'actions':
				return '
					<span class="resources-action dashicons dashicons-edit color-green" title="Edit" data-action="edit" data-resource-id="' . $resource_id . '"></span>
					<span class="resources-action dashicons dashicons-trash color-red" title="Trash" data-action="trash" data-resource-id="' . $resource_id . '" data-resource-title="' . $resource_title . '"></span>
				';
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
			'title' => array( 'title', false ),
			'date'  => array( 'date', false ),
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
		$data                  = $this->resources_data;
		$this->items           = $data;

		$this->set_pagination_args(
			array(
				'total_items' => $this->total_resources,
				'per_page'    => $this->records_per_page,
				'total_pages' => ceil( $this->total_resources / $this->records_per_page ),
				'orderby'     => $this->sort_orderby,
				'order'       => $this->sort_order,
			)
		);
	}

	/**
	 * Populate Resource Data.
	 *
	 * @param array $options Filter Options.
	 * @since 5.19.0
	 */
	public function populate_data( $options = array() ) {

		$args = array();

		if ( isset( $options['page'] ) ) {
			if ( is_numeric( $options['page'] ) ) {
				$this->page     = (int) $options['page'];
				$args['offset'] = ( $this->page - 1 ) * $this->records_per_page;
			}
		}

		if ( isset( $options['paged'] ) ) {
			$args['paged'] = $options['paged'];
		}

		if ( isset( $options['orderby'] ) ) {
			$this->sort_orderby = $options['orderby'];
			$args['orderby']    = $options['orderby'];
		}

		if ( isset( $options['filter'] ) && 'all_dates' !== $options['filter'] ) {
			$args['date_query'] = array(
				'year'  => substr( $options['filter'], 0, 4 ),
				'month' => substr( $options['filter'], 4, 2 ),
			);
		}

		if ( isset( $options['order'] ) && 'desc' === $options['order'] ) {
			$this->sort_order = 'desc';
			$args['order']    = 'DESC';
		}

		if ( isset( $options['order'] ) && 'asc' === $options['order'] ) {
			$this->sort_order = 'asc';
			$args['order']    = 'ASC';
		}

		if ( isset( $options['search'] ) && '' !== $options['search'] ) {
			$args['s'] = $options['search'];
		}

		$args['post_status'] = isset( $options['status'] ) && 'all' !== $options['status'] ? $options['status'] : 'publish';
		$this->post_status   = $args['post_status'];

		$resource_data   = array();
		$query_resources = new WP_Query(
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

		if ( 0 !== $query_resources->post_count ) {
			$this->total_resources = $query_resources->found_posts;

			foreach ( $query_resources->posts as $resource_id ) {
				$resource_data[] = new BKAP_Product_Resource( $resource_id );
			}
		}

		$this->resources_data = $resource_data;
	}

	/**
	 * Views.
	 *
	 * @since 5.19.0
	 */
	public function get_views() {

		global $wpdb;

		return array(
			'all'     => sprintf(
				'<a href="%s"%s>%s</a>',
				'javascript:void(0);',
				' class="resources-action' . ( 'all' === $this->post_status ? ' bold' : '' ) . '" data-action="set-status" data-status="all"',
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
				__( 'All', 'woocommerce-booking' ) . '&nbsp;<span class="count">(' . $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = %s AND post_status = %s", $this->post_type, 'publish' ) ) . ')</span>'
			),
			'trashed' => sprintf(
				'<a href="%s"%s>%s</a>',
				'javascript:void(0);',
				' class="resources-action' . ( 'trash' === $this->post_status ? ' bold' : '' ) . '" data-action="set-status" data-status="trash"',
				__( 'Trash', 'woocommerce-booking' ) . '&nbsp;<span class="count">(' . $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = %s AND post_status = %s", $this->post_type, 'trash' ) ) . ')</span>'
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
	 * Displays the Search box.
	 *
	 * @since 5.19.0
	 *
	 * @param string $text     The 'submit' button label.
	 * @param string $input_id ID attribute value for the search input field.
	 */
	public function search_box( $text, $input_id ) {

		$input_id = $input_id . '-search-input';
		?>
<p class="search-box">
	<label class="screen-reader-text"
        for="<?php echo esc_attr( $input_id ); ?>"><?php echo $text; // phpcs:ignore ?>:</label>
	<input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>" />
		<?php submit_button( $text, 'secondary-btn', '', false, array( 'id' => 'resources-search' ) ); ?>
</p>
		<?php
	}

	/**
	 * Message to be displayed when there are no items found.
	 *
	 * @since 5.19.0
	 */
	public function no_items() {
		esc_html_e( 'No resources have been found.', 'woocommerce-booking' );
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

		echo '<input type="hidden" id="resource_status" name="resource_status" value="all" />';

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
	 * Bulk actions.
	 *
	 * @since 5.19.0
	 */
	protected function get_bulk_actions() {

		return array(
			'bulk_action_trash_resource'              => __( 'Trash', 'woocommerce-booking' ),
			'bulk_action_restore_resource'            => __( 'Restore', 'woocommerce-booking' ),
			'bulk_action_delete_permanently_resource' => __( 'Delete Permanently', 'woocommerce-booking' ),
		);
	}

	/**
	 * Table Nav.
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
			<button class="trietary-btn reverse" type="button"
				id="button_resource_filter"><?php esc_attr_e( 'Filter', 'woocommerce-booking' ); ?></button>
			<button class="trietary-btn reverse hide" type="button"
				id="button_empty_trash"><?php esc_attr_e( 'Empty Trash', 'woocommerce-booking' ); ?></button>
		</div>
	</div>
</div>
			<?php
		}
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
			$wpdb->prepare(
				"SELECT DISTINCT YEAR( post_date ) AS year, MONTH( post_date ) AS month FROM $wpdb->posts WHERE post_type = %s",
				$this->post_type
			)
		);

		$dates['all_dates'] = __( 'All dates', 'woocommerce-booking' );

		foreach ( $months as $item ) {

			$month = zeroise( $item->month, 2 );
			$year  = $item->year;

			$dates[ $year . $month ] = $wp_locale->get_month( $month ) . ' ' . $year;
		}

		return $dates;
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
