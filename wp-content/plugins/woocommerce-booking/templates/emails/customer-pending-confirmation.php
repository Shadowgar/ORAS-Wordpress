<?php
/**
 * Customer booking Pending email.
 *
 * @package WooCommerce-Booking
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.

do_action( 'woocommerce_email_header', $email_heading, $email );

$booking_order_id = isset( $booking->order_id ) ? $booking->order_id : 0;

$bkaporder = wc_get_order( $booking_order_id );

if ( ! empty( $message ) ) :
	?>
	<p><?php echo wp_kses_post( wpautop( wptexturize( esc_html( $message ) ) ) ); ?></p>
	<?php
else :
	if ( $bkaporder ) :
		$billing_first_name = ( version_compare( WOOCOMMERCE_VERSION, '3.0.0', '<' ) ) ? $bkaporder->billing_first_name : $bkaporder->get_billing_first_name();
		?>
		<?php /* translators: %s: Customer first name */ ?>
		<p><?php printf( esc_html__( 'Hello %s', 'woocommerce-booking' ), esc_html( $billing_first_name ) ); ?></p>
	<?php endif; ?>

	<p><?php esc_html_e( 'We have received your request for a booking. The details of the booking are as follows:', 'woocommerce-booking' ); ?></p>
<?php endif; ?>

<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #aaa;">
	<tbody>
		<tr>
			<th scope="row" style="text-align:left; border: 1px solid #eee;">
				<?php esc_html_e( 'Booked Product', 'woocommerce-booking' ); ?>
			</th>
			<td style="text-align:left; border: 1px solid #eee;">
				<?php echo isset( $booking->product_title ) ? esc_html( $booking->product_title ) : esc_html__( 'Bookable Product', 'woocommerce-booking' ); ?>
			</td>
		</tr>
		<tr>
			<th style="text-align:left; border: 1px solid #eee;" scope="row">
				<?php echo esc_html( isset( $booking->start_date_label ) ? $booking->start_date_label : __( 'Start Date', 'woocommerce-booking' ) ); ?>
			</th>
			<td style="text-align:left; border: 1px solid #eee;">
				<?php echo esc_html( isset( $booking->item_booking_date ) ? $booking->item_booking_date : gmdate( 'Y-m-d' ) ); ?>
			</td>
		</tr>
		<?php if ( isset( $booking->item_checkout_date ) && '' !== $booking->item_checkout_date ) : ?>
			<tr>
				<th style="text-align:left; border: 1px solid #eee;" scope="row">
					<?php echo esc_html( $booking->end_date_label ); ?>
				</th>
				<td style="text-align:left; border: 1px solid #eee;">
					<?php echo esc_html( $booking->item_checkout_date ); ?>
				</td>
			</tr>
		<?php endif; ?>

		<?php if ( isset( $booking->item_booking_time ) && '' !== $booking->item_booking_time ) : ?>
			<tr>
				<th style="text-align:left; border: 1px solid #eee;" scope="row">
					<?php echo esc_html( $booking->time_label ); ?>
				</th>
				<td style="text-align:left; border: 1px solid #eee;">
					<?php echo esc_html( $booking->item_booking_time ); ?>
				</td>
			</tr>
		<?php endif; ?>

		<?php if ( isset( $booking->resource_title ) && '' !== $booking->resource_title ) : ?>
			<tr>
				<th style="text-align:left; border: 1px solid #eee;" scope="row">
					<?php echo esc_html( $booking->resource_label ); ?>
				</th>
				<td style="text-align:left; border: 1px solid #eee;">
					<?php echo esc_html( $booking->resource_title ); ?>
				</td>
			</tr>
		<?php endif; ?>

		<?php if ( isset( $booking->person_data ) && '' !== $booking->person_data ) : ?>
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

<p><?php esc_html_e( 'You shall receive a confirmation email with the next steps once your booking is confirmed.', 'woocommerce-booking' ); ?></p>

<?php
/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( ! empty( $additional_content ) ) {
	echo wp_kses_post( wpautop( wptexturize( esc_textarea( $additional_content ) ) ) );
}

/**
 * Hooked WC_Emails::email_footer() - Outputs the email footer.
 */
do_action( 'woocommerce_email_footer' );
