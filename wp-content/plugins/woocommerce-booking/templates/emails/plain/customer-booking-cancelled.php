<?php
/**
 * Customer booking confirmed email.
 *
 * @package Email/CustomerBookingCancelled
 */

echo '= ' . esc_html( $email_heading ) . " =\n\n";

$bkaporder = wc_get_order( $booking->order_id );
if ( $bkaporder ) {
	$billing_first_name = ( version_compare( WOOCOMMERCE_VERSION, '3.0.0' ) < 0 ) ? $bkaporder->billing_first_name : $bkaporder->get_billing_first_name();
	/* Translators: %s Customer Name */
	echo sprintf( esc_html__( 'Hello %s', 'woocommerce-booking' ), esc_html( $billing_first_name ) ) . "\n\n";
}

echo esc_html__( 'We are sorry to say that your booking could not be confirmed and has been cancelled. The details of the cancelled booking can be found below.', 'woocommerce-booking' ) . "\n\n";

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";
/* Translators: %s Booked Product */
echo sprintf( esc_html__( 'Booked: %s', 'woocommerce-booking' ), wp_kses_post( $booking->product_title ) ) . "\n";
/* Translators: %1$s Start Date LAbel, %2$s Start Date Value */
echo sprintf( esc_html__( '%1$s: %2$s', 'woocommerce-booking' ), esc_html( get_option( 'book_item-meta-date' ) ), esc_html( $booking->item_booking_date ) ) . "\n";

if ( isset( $booking->item_checkout_date ) && '' !== $booking->item_checkout_date ) {
	/* Translators: %1$s End Date LAbel, %2$s End Date Value */
	echo sprintf( esc_html__( '%1$s: %2$s', 'woocommerce-booking' ), esc_html( get_option( 'checkout_item-meta-date' ) ), esc_html( $booking->item_checkout_date ) ) . "\n";
}

if ( isset( $booking->item_booking_time ) && '' != $booking->item_booking_time ) {
	/* Translators: %1$s Time LAbel, %2$s Time Value */
	echo esc_html( sprintf( __( '%1$s: %2$s', 'woocommerce-booking' ), get_option( 'book_item-meta-time' ), $booking->item_booking_time ) ) . "\n";
}

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo esc_html__( 'Please contact us if you have any questions or concerns.', 'woocommerce-booking' ) . "\n";

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
	echo esc_html( wp_strip_all_tags( wptexturize( $additional_content ) ) );
	echo "\n\n----------------------------------------\n\n";
}

echo esc_html( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
