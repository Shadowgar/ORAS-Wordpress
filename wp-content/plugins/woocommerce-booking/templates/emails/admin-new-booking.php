<?php
/**
 * Admin new booking email
 *
 * @package Email/NewBooking
 */

$bkaporder = wc_get_order( $booking->order_id );
if ( bkap_common::bkap_order_requires_confirmation( $bkaporder ) && 'pending-confirmation' == $booking->item_booking_status ) {
	/* Translators: %s Customer Name */
	$opening_paragraph = esc_html__( 'A booking has been made by %s and is awaiting your approval. The details of this booking are as follows:', 'woocommerce-booking' );
} else {
	/* Translators: %s Customer Name */
	$opening_paragraph = esc_html__( 'A new booking has been made by %s. The details of this booking are as follows:', 'woocommerce-booking' );
}
?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<?php
$billing_first_name = ( version_compare( WOOCOMMERCE_VERSION, '3.0.0' ) < 0 ) ? $bkaporder->billing_first_name : $bkaporder->get_billing_first_name();
$billing_last_name  = ( version_compare( WOOCOMMERCE_VERSION, '3.0.0' ) < 0 ) ? $bkaporder->billing_last_name : $bkaporder->get_billing_last_name();
if ( $bkaporder && $billing_first_name && $billing_last_name ) :
	?>
	<p><?php echo esc_html( sprintf( $opening_paragraph, $billing_first_name . ' ' . $billing_last_name ) ); ?></p>
<?php endif; ?>

<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;" border="1" bordercolor="#eee">
	<tbody>
		<tr>
			<th scope="row" style="text-align:left; border: 1px solid #eee;"><?php esc_html_e( 'Booked Product', 'woocommerce-booking' ); ?></th>
			<td style="text-align:left; border: 1px solid #eee;"><?php echo esc_html( $booking->product_title ); ?></td>
		</tr>
		<tr>
			<th style="text-align:left; border: 1px solid #eee;" scope="row"><?php esc_html_e( get_option( 'book_item-meta-date' ), 'woocommerce-booking' ); // phpcs:ignore ?></th>
			<td style="text-align:left; border: 1px solid #eee;"><?php echo esc_html( $booking->item_booking_date ); ?></td>
		</tr>
		<?php
		if ( isset( $booking->item_checkout_date ) && '' != $booking->item_checkout_date ) {
			?>
			<tr>
				<th style="text-align:left; border: 1px solid #eee;" scope="row"><?php esc_html_e( get_option( 'checkout_item-meta-date' ), 'woocommerce-booking' ); // phpcs:ignore ?></th>
				<td style="text-align:left; border: 1px solid #eee;"><?php echo esc_html( $booking->item_checkout_date ); ?></td>
			</tr>
			<?php
		}
		if ( isset( $booking->item_booking_time ) && '' != $booking->item_booking_time ) {
			?>
			<tr>
				<th style="text-align:left; border: 1px solid #eee;" scope="row"><?php esc_html_e( get_option( 'book_item-meta-time' ), 'woocommerce-booking' ); // phpcs:ignore ?></th>
				<td style="text-align:left; border: 1px solid #eee;"><?php echo esc_html( $booking->item_booking_time ); ?></td>
			</tr>
			<?php
		}
		if ( isset( $booking->resource_title ) && '' != $booking->resource_title ) {
			?>
			<tr>
				<th style="text-align:left; border: 1px solid #eee;" scope="row"><?php echo esc_html( $booking->resource_label ); ?></th>
				<td style="text-align:left; border: 1px solid #eee;" scope="row"><?php echo esc_html( $booking->resource_title ); ?></td>
			</tr>
			<?php
		}

		if ( isset( $booking->person_data ) && '' != $booking->person_data ) {
			?>
			<tr>
				<th style="text-align:left; border: 1px solid #eee;" scope="row"><?php echo esc_html( $booking->person_label ); ?></th>
				<td style="text-align:left; border: 1px solid #eee;" scope="row"><?php echo esc_html( $booking->person_data ); ?></td>
			</tr>
			<?php
		}
		?>
	</tbody>
</table>

<?php if ( bkap_common::bkap_order_requires_confirmation( $bkaporder ) && 'pending-confirmation' == $booking->item_booking_status ) : ?>
<p><?php esc_html_e( 'This booking is awaiting your approval. Please check it and inform the customer if the date is available or not.', 'woocommerce-booking' ); ?></p>
<?php endif; ?>

<p><?php echo make_clickable( sprintf( __( 'You can view and edit this booking in the dashboard here: %s', 'woocommerce-booking' ), admin_url( 'admin.php?page=bkap_page&booking_id=' . $booking->booking_id . '&action=booking#/view-bookings' ) ) ); // phpcs:ignore ?></p>

<?php
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
