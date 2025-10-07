<?php
/**
 * The template is for listing the Bookings on front end.
 *
 * @package BKAP/Wcfm-Marketplace-List-Bookings
 * @version 1.1.0
 */

global $wp;
$base_url = isset( $_SERVER['REQUEST_URI'] ) && ! empty( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : ( '/' . $wp->request . '/' );

// Get the vendor ID.
$bkap_vendors = new BKAP_Vendors();
$vendor_id    = get_current_user_id();

// Get the total number of records.
$total_count = $bkap_vendors->get_bookings_count( $vendor_id );

$action_name         = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
$booking_status      = isset( $_GET['booking_status'] ) ? sanitize_text_field( wp_unslash( $_GET['booking_status'] ) ) : 'all'; // phpcs:ignore WordPress.Security.NonceVerification
$bkap_bookings_menus = bkap_common::bkap_view_bookings_status();

// Confirm or cancel bookings if data has been passed.
if ( ! empty( $action_name ) ) {

	$bkap_id = isset( $_GET['booking_id'] ) ? (int) $_GET['booking_id'] : 0; // phpcs:ignore WordPress.Security.NonceVerification

	// Check the booking post ID.
	if ( $bkap_id ) {

		// Confirm the post type.
		if ( 'bkap_booking' === get_post_type( $bkap_id ) ) {

			// Set the new status.
			switch ( $action_name ) {
				case 'bkap-confirm':
					$new_status = 'confirmed';
					break;
				case 'bkap-cancel':
					$new_status = 'cancelled';
					break;
				default:
					$new_status = '';
					break;
			}

			// Process the request.
			if ( '' !== $new_status ) {
				$item_id = get_post_meta( $bkap_id, '_bkap_order_item_id', true );
				BKAP_Booking_Confirmation::bkap_save_booking_status( $item_id, $new_status );
			}
		}
	}
}

?>
<ul class="bkap_vendor_booking_status">
	<?php
	$is_first = true;
	foreach ( $bkap_bookings_menus as $bkap_bookings_menu_key => $bkap_bookings_menu ) {
		?>
		<li class="bkap_vendor_booking_status_item">
			<?php
			if ( $is_first ) {
				$is_first = false;
			} else {
				echo '  | ';
			}
			$bkap_list_url = $bkap_vendors->bkap_vendor_bkap_list_url( $bkap_bookings_menu_key, $bkap_vendor );
			?>
			<a class="<?php echo ( $bkap_bookings_menu_key === $booking_status ) ? 'active' : ''; ?>" href="<?php esc_attr_e( $bkap_list_url ); // phpcs:ignore ?>"><?php esc_html_e( $bkap_bookings_menu ); ?></a>
		</li>
		<?php
	}
	?>
</ul>
<?php
if ( $total_count > 0 ) {
	$split_url = explode( '/', $base_url );
	array_pop( $split_url );
	$export_url = implode( '/', $split_url );

	switch ( $bkap_vendor ) {
		case 'dokan':
		case 'wcfm':
			$csv_url   = add_query_arg( 'view', 'bkap-csv', $base_url  );
			$print_url = add_query_arg( 'view', 'bkap-print', $base_url  );
			break;
		case 'wc-vendor':
			$csv_url   = add_query_arg( 'custom', 'bkap-csv', $base_url  );
			$print_url = add_query_arg( 'custom', 'bkap-print', $base_url  );
			break;
		default:
			# code...
			break;
	}
	?>
	<div id="bkap_export">
		<a href="<?php echo esc_url( $csv_url ); ?>" target="_blank" class="wcv-button button"><?php esc_html_e( 'CSV', 'woocommerce-booking' ); ?></a>
		<a href="<?php echo esc_url( $print_url ); ?>" target="_blank" class="wcv-button button"><?php esc_html_e( 'Print', 'woocommerce-booking' ); ?></a>
	</div>
	
	<div class="bkap_booking_filters">
		<form method="get">
		<!-- <input type="text" id="booking-search" name="booking-search" value="102" style=""> -->
		<?php
		global $wpdb;
		// Due to 2918 we have hard coded code to get all bookable products.
		$product_status = array( 'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit' );
		$products       = bkap_common::get_woocommerce_product_list( true, 'on', '', $product_status );

		$products = apply_filters( 'bkap_all_bookable_products_dropdown', $products );
		$output   = '';

		if ( is_array( $products ) && count( $products ) > 0 ) {
			$output .= '<select name="bkap_filter_products" style="width:20%">';
			$output .= '<option value="">' . __( 'All Bookable Products', 'woocommerce-booking' ) . '</option>';

			foreach ( $products as $filter_id => $filter ) {

				$output .= '<option value="' . absint( $filter[1] ) . '" ';

				if ( isset( $_REQUEST['bkap_filter_products'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$output .= selected( $filter[1], sanitize_text_field( wp_unslash( $_REQUEST['bkap_filter_products'] ) ), false ); // phpcs:ignore WordPress.Security.NonceVerification
				}

				$output .= '>' . esc_html( $filter[0] ) . '</option>';
			}

			$output .= '</select>';
		}

		$views = apply_filters(
			'bkap_list_booking_by_dropdown',
			array(
				'today_onwards'  => 'Today Onwards',
				'today_checkin'  => 'Today\'s Check-ins',
				'today_checkout' => 'Today\'s Checkouts',
				'gcal'           => 'Imported Bookings',
				'custom_dates'   => 'Custom Dates',
			)
		);

		$element_id = ( 'dokan' === $bkap_vendor ) ? 'id="dokan-filter-customer"' : 'class="bkap-customer-search-select-box"';

		if ( isset( $_REQUEST['bkap_filter_customer'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification


			$custid   = absint( $_REQUEST['bkap_filter_customer'] ); // phpcs:ignore WordPress.Security.NonceVerification
			$customer = new WC_Customer( $custid );
			/* translators: 1: user display name 2: user ID 3: user email */
			$display_name = sprintf(
				/* translators: $1: customer name, $2 customer id, $3: customer email */
				esc_html__( '%1$s (#%2$s &ndash; %3$s)', 'woocommerce-booking' ),
				$customer->get_first_name() . ' ' . $customer->get_last_name(),
				$customer->get_id(),
				$customer->get_email()
			);

			$output .= '<select ' . $element_id . ' name="bkap_filter_customer" data-placeholder="Search Customers" data-allow_clear="true"><option value="' . esc_attr( $custid ) . '" selected="selected">' . htmlspecialchars( wp_kses_post( $display_name ), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8' ) . '<option></select>';
		} else {
			$output .= '<select ' . $element_id . ' name="bkap_filter_customer" data-placeholder="Search Customers" data-allow_clear="true"></select>';
		}

		if ( ! empty( $views ) ) {

			$output .= '<select name="bkap_filter_views" class="bkap_filter_view">';
			$output .= '<option value="">' . __( 'List bookings by', 'woocommerce-booking' ) . '</option>';

			foreach ( $views as $v_key => $v_value ) {

				$output .= '<option value="' . $v_key . '" ';

				if ( isset( $_REQUEST['bkap_filter_views'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$output .= selected( $v_key, sanitize_text_field( wp_unslash( $_REQUEST['bkap_filter_views'] ) ), false );  // phpcs:ignore WordPress.Security.NonceVerification
				}

				$output .= '>' . esc_html( $v_value ) . '</option>';
			}

			$date_display = 'display:none;';
			if ( isset( $_REQUEST['bkap_filter_views'] ) && 'custom_dates' === $_REQUEST['bkap_filter_views'] ) { // phpcs:ignore WordPress.Security.NonceVerification
				$date_display = '';
			}

			$startdate = isset( $_REQUEST['bkap_custom_startdate'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['bkap_custom_startdate'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			$enddate   = isset( $_REQUEST['bkap_custom_enddate'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['bkap_custom_enddate'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

			$output          .= '</select>';
			$start_date_label = __( 'Start Date', 'woocommerce-booking' );
			$end_date_label   = __( 'End Date', 'woocommerce-booking' );
			$output          .= '<input type="text" name="bkap_custom_startdate" id="bkap_custom_startdate" class="bkap_datepicker" value="' . $startdate . '" style="width:100px;' . $date_display . '" placeholder="' . $start_date_label . '" readonly><input type="text" name="bkap_custom_enddate" id="bkap_custom_enddate" class="bkap_datepicker" value="' . $enddate . '" style="width:100px;' . $date_display . '" placeholder="' . $end_date_label . '" readonly>';
		}

		echo apply_filters( 'bkap_filters_output', $output ); // phpcs:ignore
		?>
			<!-- <input type="submit" name="filter_action" id="post-query-submit" class="button" value="Filter"> -->
			<input type="submit" name="filter-bookings" id="filter-bookings" class="button" value="Filter Bookings">
		</form>
	</div>
	
	<?php
}

// Preparing meta data based on the filter.
$meta_arguments = $bkap_vendors->bkap_filtered_data_meta_query();

// Save meta query in temporary transient so that it can be used to filter records in CSV and Print pages.
set_transient( 'bkap_vendors_view_bookings_meta_query', $meta_arguments, 3600 );


$rec_per_page = 20;
$cur_page     = 1;
$pagenum      = isset( $_GET['pagenum'] ) ? (int) $_GET['pagenum'] : 1; // phpcs:ignore WordPress.Security.NonceVerification

// Get the number of pages.
$page_count = $bkap_vendors->get_number_of_pages( $vendor_id, $rec_per_page, 'bkap_booking', array( 'post_status' => array( $booking_status ), 'meta_query' => $meta_arguments ) );  //phpcs:ignore

if ( 1 < $pagenum ) {
	$cur_page = $pagenum;
}

// Remove the pagenum query variable base url if it already exists.
$pagenum_pos = strpos( $base_url, 'pagenum' );
if ( false !== $pagenum_pos ) {
	$base_url = substr( $base_url, 0, ( $pagenum_pos - 1 ) );
}
$booking_posts = $bkap_vendors->get_booking_data( $vendor_id, $cur_page, $rec_per_page, array( $booking_status ), $meta_arguments );

// Loading Dashboard Page from Booking Plugin.

switch ( $bkap_vendor ) {
	case 'dokan':
		$vendor_file      = 'dokan/bkap-dokan-view-booking.php';
		$vendor_file_path = BKAP_VENDORS_TEMPLATE_PATH;
		break;
	case 'wcfm':
		$vendor_file      = 'bkap-view-bookings.php';
		$vendor_file_path = BKAP_WCFM_TEMPLATE_DIR_PATH;
		break;
	case 'wc-vendor':
		$vendor_file      = 'wc-vendors/bkap-wcv-view-bookings.php';
		$vendor_file_path = BKAP_VENDORS_TEMPLATE_PATH;
		break;
}

wc_get_template(
	$vendor_file,
	array(
		'base_url'      => $base_url,
		'cur_page'      => $cur_page,
		'page_count'    => $page_count,
		'booking_posts' => $booking_posts,
		'total_count'   => $total_count,
		'vendor_id'     => $vendor_id,
	),
	'woocommerce-booking/',
	$vendor_file_path
);
