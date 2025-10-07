<?php
/**
 * Customer booking cancelled email.
 *
 * @package Email/CustomerBookingCancelled
 */

defined( 'ABSPATH' ) || exit;

?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<?php
$bkaporder = wc_get_order( $booking->order_id );
if ( $bkaporder ) :
	$billing_first_name = ( version_compare( WC_VERSION, '3.0.0', '<' ) ) ? $bkaporder->billing_first_name : $bkaporder->get_billing_first_name();
	?>
	<p>
		<?php
		/* Translators: %s Customer name */
		printf( esc_html__( 'Hello %s', 'woocommerce-booking' ), esc_html( $billing_first_name ) );
		?>
	</p>
<?php endif; ?>

<p>
	<?php esc_html_e( 'We are sorry to say that your booking could not be confirmed and has been cancelled. The details of the cancelled booking can be found below.', 'woocommerce-booking' ); ?>
</p>

<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;" border="1" bordercolor="#eee">
	<tbody>
		<tr>
			<th scope="row" style="text-align:left; border: 1px solid #eee;">
				<?php esc_html_e( 'Booked Product', 'woocommerce-booking' ); ?>
			</th>
			<td style="text-align:left; border: 1px solid #eee;">
				<?php echo esc_html( $booking->product_title ); ?>
			</td>
		</tr>
		<tr>
			<th style="text-align:left; border: 1px solid #eee;" scope="row">
				<?php echo esc_html( get_option( 'book_item-meta-date', __( 'Start Date', 'woocommerce-booking' ) ) ); ?>
			</th>
			<td style="text-align:left; border: 1px solid #eee;">
				<?php echo esc_html( $booking->item_booking_date ); ?>
			</td>
		</tr>
		<?php if ( ! empty( $booking->item_checkout_date ) ) : ?>
			<tr>
				<th style="text-align:left; border: 1px solid #eee;" scope="row">
					<?php echo esc_html( get_option( 'checkout_item-meta-date', __( 'End Date', 'woocommerce-booking' ) ) ); ?>
				</th>
				<td style="text-align:left; border: 1px solid #eee;">
					<?php echo esc_html( $booking->item_checkout_date ); ?>
				</td>
			</tr>
		<?php endif; ?>

		<?php if ( ! empty( $booking->item_booking_time ) ) : ?>
			<tr>
				<th style="text-align:left; border: 1px solid #eee;" scope="row">
					<?php echo esc_html( get_option( 'book_item-meta-time', __( 'Time', 'woocommerce-booking' ) ) ); ?>
				</th>
				<td style="text-align:left; border: 1px solid #eee;">
					<?php echo esc_html( $booking->item_booking_time ); ?>
				</td>
			</tr>
		<?php endif; ?>

		<?php if ( ! empty( $booking->resource_title ) ) : ?>
			<tr>
				<th style="text-align:left; border: 1px solid #eee;" scope="row">
					<?php echo esc_html( $booking->resource_label ); ?>
				</th>
				<td style="text-align:left; border: 1px solid #eee;">
					<?php echo esc_html( $booking->resource_title ); ?>
				</td>
			</tr>
		<?php endif; ?>

		<?php if ( ! empty( $booking->person_data ) ) : ?>
			<tr>
				<th style="text-align:left; border: 1px solid #eee;" scope="row">
					<?php echo esc_html( $booking->person_label ); ?>
				</th>
				<td style="text-align:left; border: 1px solid #eee;">
					<?php echo esc_html( $booking->person_data ); ?>
				</td>
			</tr>
		<?php endif; ?>
	</tbody>
</table>

<p>
	<?php esc_html_e( 'Please contact us if you have any questions or concerns.', 'woocommerce-booking' ); ?>
</p>

<?php
/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( ! empty( $additional_content ) ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

/**
 * Hooked function: WC_Emails::email_footer() - Output the email footer.
 */
do_action( 'woocommerce_email_footer' );
