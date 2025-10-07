<?php
/**
 * Customer booking confirmed email.
 *
 * @package Email/RescheduleBookingAdmin
 */

echo '= ' . esc_html( $email_heading ) . " =\n\n";

$bkaporder = wc_get_order( $booking->order_id );
/* Translators: %s Customer Name */
echo esc_html( sprintf( __( 'Bookings have been rescheduled for an order from %s. The order is as follows:', 'woocommerce-booking' ), $bkaporder->get_formatted_billing_full_name() ) ) . "\n\n";
/* Translators: %s Order ID */
echo esc_html( printf( __( 'Order #%s', 'woocommerce-booking' ), $bkaporder->get_order_number() ) );

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";
/* Translators: %s Product Name */
echo esc_html( sprintf( __( 'Rescheduled Product: %s', 'woocommerce-booking' ), $booking->product_title ) ) . "\n";
/* Translators: %1$s Booking Start Date, %2$s Booking Start Date Value */
echo esc_html( sprintf( __( '%1$s: %2$s', 'woocommerce-booking' ), get_option( 'book_item-meta-date' ), $booking->item_booking_date ) ) . "\n";

if ( isset( $booking->item_checkout_date ) && '' != $booking->item_checkout_date ) {
	/* Translators: %1$s Booking End Date, %2$s Booking End Date Value */
	echo esc_html( sprintf( __( '%1$s: %2$s', 'woocommerce-booking' ), get_option( 'checkout_item-meta-date' ), $booking->item_checkout_date ) ) . "\n";
}

if ( isset( $booking->item_booking_time ) && '' != $booking->item_booking_time ) {
	/* Translators: %1$s Booking Time, %2$s Booking Time Value */
	echo esc_html( sprintf( __( '%1$s: %2$s', 'woocommerce-booking' ), get_option( 'book_item-meta-time' ), $booking->item_booking_time ) ) . "\n";
}

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
	echo esc_html( wp_strip_all_tags( wptexturize( $additional_content ) ) );
	echo "\n\n----------------------------------------\n\n";
}

echo esc_html( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
