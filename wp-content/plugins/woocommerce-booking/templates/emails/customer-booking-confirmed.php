<?php
/**
 * Customer booking confirmed email.
 *
 * @package Email/CustomerBookingConfirmed
 */

?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<?php
$bkaporder = isset( $booking->order_id ) ? wc_get_order( $booking->order_id ) : false;
if ( $bkaporder ) :
	$billing_first_name = ( version_compare( WOOCOMMERCE_VERSION, '3.0.0' ) < 0 ) ? $bkaporder->billing_first_name : $bkaporder->get_billing_first_name();
	?>
	<p>
		<?php
		printf(
			/* Translators: %s Customer Name */
			esc_html__( 'Hello %s', 'woocommerce-booking' ),
			esc_html( $billing_first_name )
		);
		?>
		</p>
<?php endif; ?>

<p><?php esc_html_e( 'Your booking has been confirmed. The details of your booking are shown below.', 'woocommerce-booking' ); ?></p>

<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;" border="1" bordercolor="#eee">
	<tbody>
		<tr>
			<th scope="row" style="text-align:left; border: 1px solid #eee;"><?php esc_html_e( 'Booked Product', 'woocommerce-booking' ); ?></th>
			<td style="text-align:left; border: 1px solid #eee;"><?php echo isset( $booking->product_title ) ? esc_html( $booking->product_title ) : ''; ?></td>
		</tr>
		<tr>
			<th style="text-align:left; border: 1px solid #eee;" scope="row"><?php esc_html_e( get_option( 'book_item-meta-date' ), 'woocommerce-booking' ); //phpcs:ignore ?></th>
			<td style="text-align:left; border: 1px solid #eee;"><?php echo isset( $booking->item_booking_date ) ? esc_html( $booking->item_booking_date ) : esc_html__( 'Booking date not available', 'woocommerce-booking' ); ?></td>
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
					<td style="text-align:left; border: 1px solid #eee;"><?php echo esc_html( $booking->resource_title ); ?></td>
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

<?php if ( $bkaporder ) : ?>

	<?php
		$order_status = $bkaporder->get_status();
	if ( $order_status == 'pending' ) :
		?>
		<p>
			<?php
			printf(
				/* Translators: %s PAy Link */
				esc_html__( 'To pay for this booking please use the following link: %s', 'woocommerce-booking' ),
				'<a id="bkap_pay_for_booking" href="' . esc_url( $bkaporder->get_checkout_payment_url() ) . '">' . esc_html__( 'Pay for booking', 'woocommerce-booking' ) . '</a>'
			);
			?>
		</p>
	<?php endif; ?>

	<?php do_action( 'woocommerce_email_before_order_table', $bkaporder, $sent_to_admin, $plain_text, $email ); ?>

	<?php
	if ( version_compare( WOOCOMMERCE_VERSION, '3.0.0' ) < 0 ) {
		$order_date = $bkaporder->order_date;
	} else {
		$order_post      = wc_get_order( $booking->order_id );
		$order_strtotime = ! is_null( $order_post->get_date_created() ) ? $order_post->get_date_created()->getOffsetTimestamp() : '';
		$order_date      = gmdate( 'Y-m-d H:i:s', $order_strtotime );
	}
	?>
	<h2><?php echo esc_html__( 'Order', 'woocommerce-booking' ) . ': ' . esc_html( $bkaporder->get_order_number() ); ?> (<?php printf( '<time datetime="%s">%s</time>', esc_html( date_i18n( 'c', strtotime( $order_date ) ) ), esc_html( date_i18n( wc_date_format(), strtotime( $order_date ) ) ) ); ?>)</h2>
	<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;" border="1" bordercolor="#eee">
		<thead>
			<tr>
				<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php esc_html_e( 'Product', 'woocommerce-booking' ); ?></th>
				<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php esc_html_e( 'Quantity', 'woocommerce-booking' ); ?></th>
				<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php esc_html_e( 'Price', 'woocommerce-booking' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
				$downloadable = $bkaporder->is_download_permitted();

			switch ( $order_status ) {
				case 'completed':
					$args = array(
						'show_download_links' => $downloadable,
						'show_sku'            => false,
						'show_purchase_note'  => true,
					);
					if ( version_compare( WOOCOMMERCE_VERSION, '3.0.0' ) < 0 ) {
						echo $bkaporder->email_order_items_table( $args ); // phpcs:ignore
					} else {
						echo wc_get_email_order_items( $bkaporder, $args ); // phpcs:ignore
					}
					break;
				case 'processing':
					$args = array(
						'show_download_links' => $downloadable,
						'show_sku'            => true,
						'show_purchase_note'  => true,
					);
					if ( version_compare( WOOCOMMERCE_VERSION, '3.0.0' ) < 0 ) {
						echo $bkaporder->email_order_items_table( $args ); // phpcs:ignore
					} else {
						echo wc_get_email_order_items( $bkaporder, $args ); // phpcs:ignore
					}
					break;
				default:
					$args = array(
						'show_download_links' => $downloadable,
						'show_sku'            => true,
						'show_purchase_note'  => false,
					);
					if ( version_compare( WOOCOMMERCE_VERSION, '3.0.0' ) < 0 ) {
						echo $bkaporder->email_order_items_table( $args ); // phpcs:ignore
					} else {
						echo wc_get_email_order_items( $bkaporder, $args ); // phpcs:ignore
					}
					break;
			}
			?>
		</tbody>
		<tfoot>
			<?php
			if ( $totals = $bkaporder->get_order_item_totals() ) {
				$i = 0;
				foreach ( $totals as $total ) {
					$i++;
					?>
						<tr>
							<th scope="row" colspan="2" style="text-align:left; border: 1px solid #eee; 
							<?php
							if ( $i == 1 ) {
								echo 'border-top-width: 4px;';}
							?>
							"><?php echo esc_html( $total['label'] ); ?></th>
							<td style="text-align:left; border: 1px solid #eee; 
							<?php
							if ( $i == 1 ) {
								echo 'border-top-width: 4px;';}
							?>
							"><?php echo wp_kses_post( $total['value'] ); ?></td>
						</tr>
						<?php
				}
			}
			?>
		</tfoot>
	</table>

	<?php do_action( 'woocommerce_email_after_order_table', $bkaporder, $sent_to_admin, $plain_text, $email ); ?>

	<?php do_action( 'woocommerce_email_order_meta', $bkaporder, $sent_to_admin, $plain_text, $email ); ?>

<?php endif; ?>

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
