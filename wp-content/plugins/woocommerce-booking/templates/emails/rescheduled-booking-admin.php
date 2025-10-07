<?php
/**
 * Admin booking rescheduled email.
 *
 * @package WooCommerce Booking
 */

do_action( 'woocommerce_email_header', $email_heading, $email );

if ( ! empty( $booking ) ) :
	$bkaporder = wc_get_order( $booking->order_id );
	?>

	<p>
		<?php
		/* translators: %s: Order billing name */
		printf( esc_html__( 'Bookings have been rescheduled for an order from %s. The order is as follows:', 'woocommerce-booking' ), esc_html( $bkaporder->get_formatted_billing_full_name() ) );
		?>
	</p>

	<h2>
		<a class="link" href="<?php echo esc_url( bkap_order_url( $bkaporder->get_id() ) ); ?>">
			<?php
			/* translators: %s: Order ID */
			printf( esc_html__( 'Order #%s', 'woocommerce-booking' ), esc_html( $bkaporder->get_order_number() ) );
			?>
		</a>
		(<?php echo esc_html( wc_format_datetime( $bkaporder->get_date_created() ) ); ?>)
	</h2>

	<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;" border="1" bordercolor="#eee">
		<tbody>
			<tr>
				<th scope="row" style="text-align:left; border: 1px solid #eee;"><?php esc_html_e( 'Rescheduled Product', 'woocommerce-booking' ); ?></th>
				<td style="text-align:left; border: 1px solid #eee;"><?php echo esc_html( $booking->product_title ); ?></td>
			</tr>
			<tr>
				<th style="text-align:left; border: 1px solid #eee;" scope="row">
					<?php echo esc_html( get_option( 'book_item-meta-date' ) ); ?>
				</th>
				<td style="text-align:left; border: 1px solid #eee;"><?php echo esc_html( $booking->item_booking_date ); ?></td>
			</tr>
			<?php if ( ! empty( $booking->item_checkout_date ) ) : ?>
				<tr>
					<th style="text-align:left; border: 1px solid #eee;" scope="row">
						<?php echo esc_html( get_option( 'checkout_item-meta-date' ) ); ?>
					</th>
					<td style="text-align:left; border: 1px solid #eee;"><?php echo esc_html( $booking->item_checkout_date ); ?></td>
				</tr>
			<?php endif; ?>
			<?php if ( ! empty( $booking->zoom_meeting ) ) : ?>
				<tr>
					<th style="text-align:left; border: 1px solid #eee;" scope="row">
						<?php echo esc_html( bkap_zoom_join_meeting_label( $booking->product_id ) ); ?>
					</th>
					<td style="text-align:left; border: 1px solid #eee;">
						<?php
						echo $booking->zoom_meeting; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
					</td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>

<?php endif; ?>

<?php
if ( ! empty( $additional_content ) ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

do_action( 'woocommerce_email_footer' );
