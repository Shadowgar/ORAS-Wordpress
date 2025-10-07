<?php
/**
 * Manual Reminder Settings.
 *
 * @package BKAP/Reminders
 */

?>
<section class="bkap-manual" id="bkap-manual">
	<div class="wrap">
		<h2><?php esc_html_e( 'Manual Email Reminders', 'woocommerce-booking' ); ?></h2>
		<p>
		<?php
		printf(
			/* translators: %1$s Opening strong tag, %2$s Closing strong tag, %3$s Opening anchor tag, %4$s Closing anchor tag. */
			esc_html__(
				'You may send an email notification to all customers who have a %1$sfuture%2$s booking for a particular product. This will use the default template specified under %3$sWooCommerce > Settings > Emails%4$s.',
				'woocommerce-booking'
			),
			'<strong>',
			'</strong>',
			'<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=email' ) ) . '">',
			'</a>'
		);
		?>
		</p>

		<form method="POST">
			<table class="form-table">
				<tbody>
					<tr valign="top" >
						<th scope="row">
							<label><?php esc_html_e( 'Send Reminder for', 'woocommerce-booking' ); ?></label>
						</th>
						<td class="forminp">

							<input type="radio" name="bkap_reminder_option" id="bkap_reminder_order" value="bkap_reminder_order" checked/>
							<label><?php esc_html_e( 'Order #', 'woocommerce-booking' ); ?></label>

							<input type="radio" name="bkap_reminder_option" id="bkap_reminder_booking" value="bkap_reminder_booking" />    
							<label><?php esc_html_e( 'Booking ID', 'woocommerce-booking' ); ?></label>

							<input type="radio" name="bkap_reminder_option" id="bkap_reminder_product" value="bkap_reminder_product"/>    
							<label><?php esc_html_e( 'Product', 'woocommerce-booking' ); ?></label>

						</td>
					</tr>

					<tr valign="top" id="reminder_order_ids">
						<th scope="row">
							<label for="bkap_reminder_order_id"><?php esc_html_e( 'Order Ids', 'woocommerce-booking' ); ?></label>
						</th>
						<td class="forminp">
							<select id="bkap_reminder_order_id" name="bkap_reminder_order_id[]"  multiple="multiple" style="width:50%" >
								<?php foreach ( $order_ids as $order_id ) : ?>
									<option value="<?php echo esc_attr( $order_id ); ?>"><?php echo esc_attr( $order_id ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>

					<tr valign="top" style = "display:none" id="reminder_booking_ids">
						<th scope="row">
							<label for="bkap_reminder_booking_id"><?php esc_html_e( 'Booking Ids', 'woocommerce-booking' ); ?></label>
						</th>
						<td class="forminp">
							<select id="bkap_reminder_booking_id" name="bkap_reminder_booking_id[]"  multiple="multiple" style="width:50%" >
								<?php foreach ( $booking_ids as $booking_id ) : ?>
									<option value="<?php echo esc_attr( $booking_id ); ?>"><?php echo esc_attr( $booking_id ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>

					<tr valign="top" style = "display:none" id="reminder_product_ids">
						<th scope="row">
							<label for="bkap_reminder_product_id"><?php esc_html_e( 'Product', 'woocommerce-booking' ); ?></label>
						</th>
						<td class="forminp">
							<select id="bkap_reminder_product_id" name="bkap_reminder_product_id[]"  multiple="multiple" style="width:50%" >
								<?php foreach ( $bookable_products as $key => $value ) : ?>
									<option value="<?php echo esc_attr( $value->ID ); ?>"><?php echo wp_kses_post( $value->post_title ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="bkap_reminder_subject"><?php esc_html_e( 'Subject', 'woocommerce-booking' ); ?></label>
						</th>
						<td>
							<input type="text" placeholder="<?php esc_html_e( 'Email subject', 'woocommerce-booking' ); ?>" name="bkap_reminder_subject" id="bkap_reminder_subject" value="<?php echo esc_attr( $email_subject ); ?>" />
						</td>
					</tr>

					<tr valign="top">
						<th scope="row">
							<label for="bkap_reminder_message"><?php esc_html_e( 'Message', 'woocommerce-booking' ); ?></label>
						</th>
						<td>
							<?php wp_editor( $content, 'bkap_reminder_message', array( 'textarea_name' => 'bkap_reminder_message' ) ); ?>
							<span class="description"><?php esc_html_e( 'You can insert the following tags. They will be replaced dynamically', 'woocommerce-booking' ); ?>: <code>{product_title} {order_date} {order_number} {customer_name} {customer_first_name} {customer_last_name} {start_date} {end_date} {booking_time} {booking_id} {booking_resource} {booking_persons} {zoom_link}</code></span>
						</td>
					</tr>

					<tr valign="top">
						<td>
							<input type="submit" name="bkap_send_reminder" class="button-primary" value="<?php esc_attr_e( 'Send Reminder', 'woocommerce-booking' ); ?>" />
						</td>

						<td style="display:flex;">
							<input type="button" id="bkap_save_message" name="bkap_save_message" class="button-primary" value="<?php esc_attr_e( 'Save Draft', 'woocommerce-booking' ); ?>" />
							<div id="ajax_img" name="ajax_img" style="float:right; display:none;"> 
								<img src="<?php echo esc_attr( bkap_ajax_loader_gif() ); // phpcs:ignore ?>">
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		</form>
	<div>
</section>
