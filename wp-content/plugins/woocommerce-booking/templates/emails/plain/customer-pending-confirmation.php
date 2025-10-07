<?php
/**
 * Customer new pending booking email.
 *
 * @package Email/CustomerPendingBooking
 */

$bkaporder = wc_get_order( $booking->order_id );

echo '= ' . esc_html( $email_heading ) . " =\n\n";

$opening_paragraph = esc_html__( 'We have received your request for a booking. The details of the booking are as follows:', 'woocommerce-booking' );

$billing_first_name = ( version_compare( WOOCOMMERCE_VERSION, '3.0.0', '<' ) ) ? $bkaporder->billing_first_name : $bkaporder->get_billing_first_name();
$billing_last_name  = ( version_compare( WOOCOMMERCE_VERSION, '3.0.0', '<' ) ) ? $bkaporder->billing_last_name : $bkaporder->get_billing_last_name();

echo esc_html( $opening_paragraph ) . "\n\n";

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo sprintf(
	/* translators: %s: Booked product title */
	esc_html__( 'Booked: %s', 'woocommerce-booking' ),
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

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

if ( bkap_common::bkap_order_requires_confirmation( $bkaporder ) && 'pending-confirmation' === $booking->item_booking_status ) {
	/* translators: Message shown when a booking requires confirmation. */
	echo esc_html__( 'You shall receive a confirmation email with the next steps once your booking is confirmed.', 'woocommerce-booking' ) . "\n\n";
}

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( ! empty( $additional_content ) ) {
	echo esc_html( wp_strip_all_tags( wptexturize( $additional_content ) ) ) . "\n\n";
	echo "----------------------------------------\n\n";
}

echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
