<?php
/**
 * Customer booking confirmed email
 */

echo '= ' . esc_html( $email_heading ) . " =\n\n";

$order = wc_get_order( $booking->order_id );

echo sprintf(
	/* translators: %s: Product title */
	esc_html__( 'You have a booking for %s. Your order is as follows: ', 'woocommerce-booking' ),
	esc_html( $booking->product_title )
) . "\n\n";

printf(
	/* translators: %s: Order number */
	esc_html__( 'Order #%s', 'woocommerce-booking' ),
	esc_html( $order->get_order_number() )
);

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo sprintf(
	/* translators: %s: Booked product title */
	esc_html__( 'Booked Product: %s', 'woocommerce-booking' ),
	esc_html( $booking->product_title )
) . "\n";

echo sprintf(
	/* translators: %1$s: Booking date label, %2$s: Selected booking date */
	esc_html__( '%1$s: %2$s', 'woocommerce-booking' ),
	esc_html( get_option( 'book_item-meta-date' ) ),
	esc_html( $booking->item_booking_date )
) . "\n";

if ( isset( $booking->item_checkout_date ) && '' !== $booking->item_checkout_date ) {
	echo sprintf(
		/* translators: %1$s: Checkout date label, %2$s: Selected checkout date */
		esc_html__( '%1$s: %2$s', 'woocommerce-booking' ),
		esc_html( get_option( 'checkout_item-meta-date' ) ),
		esc_html( $booking->item_checkout_date )
	) . "\n";
}

if ( isset( $booking->item_booking_time ) && '' !== $booking->item_booking_time ) {
	echo sprintf(
		/* translators: %1$s: Booking time label, %2$s: Selected booking time */
		esc_html__( '%1$s: %2$s', 'woocommerce-booking' ),
		esc_html( get_option( 'book_item-meta-time' ) ),
		esc_html( $booking->item_booking_time )
	) . "\n";
}

if ( isset( $booking->resource_title ) && '' !== $booking->resource_title ) {
	echo sprintf(
		/* translators: %1$s: Resource label, %2$s: Selected resource title */
		esc_html__( '%1$s: %2$s', 'woocommerce-booking' ),
		esc_html( $booking->resource_label ),
		esc_html( $booking->resource_title )
	) . "\n";
}

if ( isset( $booking->zoom_meeting ) && '' !== $booking->zoom_meeting ) {
	echo sprintf(
		/* translators: %1$s: Zoom meeting label, %2$s: Zoom meeting link */
		esc_html__( '%1$s: %2$s', 'woocommerce-booking' ),
		esc_html( bkap_zoom_join_meeting_label( $booking->product_id ) ),
		esc_html( $booking->zoom_meeting )
	) . "\n";
}

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( ! empty( $additional_content ) ) {
	echo esc_html( wp_strip_all_tags( wptexturize( $additional_content ) ) ) . "\n\n";
	echo "----------------------------------------\n\n";
}

echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
