<?php
/**
 * Customer booking confirmed email
 */

do_action( 'woocommerce_email_header', $email_heading, $email );

$bkaporder = ! empty( $booking ) ? wc_get_order( $booking->order_id ) : array();

$product_title = ! empty( $booking ) ? $booking->product_title : 'Car';
$booking_date  = ! empty( $booking ) ? $booking->item_booking_date : gmdate( 'j-n-Y' );

$html = '<br><table cellspacing="0" cellpadding="6" style="width: 100%;border-color: #aaa; border: 1px solid #aaa;">';
$html .= '<tbody>';
$html .= '<tr>';
$html .= '<th scope="row" style="text-align:left; border: 1px solid #eee;">' . __( 'Booked Product', 'woocommerce-booking' ) . '</th>';
$html .= '<td scope="row" style="text-align:left; border: 1px solid #eee;">' . $product_title . '</td>';
$html .= '</tr>';
$html .= '<tr>';
$html .= '<th style="text-align:left; border: 1px solid #eee;" scope="row">' . __( ! empty( $booking ) ? $booking->start_date_label : 'Start Date', 'woocommerce-booking' ) . '</th>'; // phpcs:ignore
$html .= '<td style="text-align:left; border: 1px solid #eee;" scope="row">' . $booking_date . '</td>';
$html .= '</tr>';

if ( ! empty( $booking ) && isset( $booking->item_checkout_date ) && '' != $booking->item_checkout_date ) {
	$html .= '<tr>';
	$html .= '<th style="text-align:left; border: 1px solid #eee;" scope="row">' . $booking->end_date_label . '</th>'; // phpcs:ignore
	$html .= '<td style="text-align:left; border: 1px solid #eee;" scope="row">' . $booking->item_checkout_date . '</td>';
	$html .= '</tr>';
}

if ( ! empty( $booking ) && isset( $booking->item_booking_time ) && '' != $booking->item_booking_time ) {
	$html .= '<tr>';
	$html .= '<th style="text-align:left; border: 1px solid #eee;" scope="row">' . $booking->time_label . '</th>'; // phpcs:ignore
	$html .= '<td style="text-align:left; border: 1px solid #eee;" scope="row">' . $booking->item_booking_time . '</td>';
	$html .= '</tr>';
}

if ( ! empty( $booking ) && isset( $booking->resource_title ) && '' != $booking->resource_title ) {
	$html .= '<tr>';
	$html .= '<th style="text-align:left; border: 1px solid #eee;" scope="row">' . $booking->resource_label . '</th>';
	$html .= '<td style="text-align:left; border: 1px solid #eee;" scope="row">' . $booking->resource_title . '</td>';
	$html .= '</tr>';
}

if ( ! empty( $booking ) && isset( $booking->person_data ) && '' != $booking->person_data ) {
	$html .= '<tr>';
	$html .= '<th style="text-align:left; border: 1px solid #eee;" scope="row">' . $booking->person_label . '</th>';
	$html .= '<td style="text-align:left; border: 1px solid #eee;" scope="row">' . $booking->person_data . '</td>';
	$html .= '</tr>';
}

if ( ! empty( $booking ) && isset( $booking->zoom_meeting ) && '' != $booking->zoom_meeting ) {
	$html .= '<tr>';
	$html .= '<th style="text-align:left; border: 1px solid #eee;" scope="row">' . bkap_zoom_join_meeting_label( $booking->product_id ) . '</th>';
	$html .= '<td style="text-align:left; border: 1px solid #eee;" scope="row">' . $booking->zoom_meeting . '</td>';
	$html .= '</tr>';
}

if ( $bkaporder && ! empty( $booking ) && $booking->customer_id > 0 ) {
	$html .= '<tr>';
	$html .= '<th style="text-align:left; border: 1px solid #eee;">Order</th>';
	$html .= '<td style="text-align:left; border: 1px solid #eee;"><a href="' . $bkaporder->get_view_order_url() . '"> View Order </a></td>';
	$html .= '</tr>';
}

$html .= '</tbody>';
$html .= '</table><br>';

$show_table = true;

if ( $message !== '' ) :

	if ( stripos( $message, '{booking_table}' ) > 0 ) {

		$message    = str_replace( '{booking_table}', $html, $message );
		$show_table = false;
	}
	echo wp_kses_post( wpautop( wptexturize( $message ) ) );

else :
	if ( $bkaporder ) :
		$billing_first_name = ( version_compare( WOOCOMMERCE_VERSION, '3.0.0' ) < 0 ) ? $bkaporder->billing_first_name : $bkaporder->get_billing_first_name();
		?>
		<p>
		<?php
		/* Translators: %s Billing Customer Name */
		printf( esc_html__( 'Hello %s', 'woocommerce-booking' ), esc_html( $billing_first_name ) );
		?>
		</p>
	<?php endif; ?>

	<p><?php esc_html_e( 'You have an upcoming booking. The details of your booking are shown below.', 'woocommerce-booking' ); ?></p>

	<?php
	/**
	 * Show Booking Table.
	 */
	echo ( $show_table ) ? $html : ''; // phpcs:ignore
	endif;

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer' );
