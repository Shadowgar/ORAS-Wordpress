<?php
/**
 * Admin new booking email.
 */

$bkaporder = wc_get_order( $booking->order_id );

echo '= ' . esc_html( $email_heading ) . " =\n\n";

if ( $bkaporder && bkap_common::bkap_order_requires_confirmation( $bkaporder ) && 'pending-confirmation' === $booking->item_booking_status ) {
	/* Translators: %s Customer Name */
	$opening_paragraph = __( 'A booking has been made by %s and is awaiting your approval. The details of this booking are as follows:', 'woocommerce-booking' );
} else {
	/* Translators: %s Customer Name */
	$opening_paragraph = __( 'A new booking has been made by %s. The details of this booking are as follows:', 'woocommerce-booking' );
}

$billing_first_name = ( version_compare( WOOCOMMERCE_VERSION, '3.0.0', '<' ) ) ? $bkaporder->billing_first_name : $bkaporder->get_billing_first_name();
$billing_last_name  = ( version_compare( WOOCOMMERCE_VERSION, '3.0.0', '<' ) ) ? $bkaporder->billing_last_name : $bkaporder->get_billing_last_name();

if ( $billing_first_name && $billing_last_name ) {
	echo sprintf( esc_html( $opening_paragraph ), esc_html( $billing_first_name . ' ' . $billing_last_name ) ) . "\n\n";
}

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";
/* Translators: %s Product Name */
echo sprintf( esc_html__( 'Booked: %s', 'woocommerce-booking' ), esc_html( $booking->product_title ) ) . "\n";

echo sprintf(
	/* Translators: %1$s Start Date LAbel, %2$s Start Date Value */
	esc_html__( '%1$s: %2$s', 'woocommerce-booking' ),
	esc_html( get_option( 'book_item-meta-date' ) ),
	esc_html( $booking->item_booking_date )
) . "\n";

if ( isset( $booking->item_checkout_date ) && '' !== $booking->item_checkout_date ) {
	echo sprintf(
		/* Translators: %1$s End Date LAbel, %2$s End Date Value */
		esc_html__( '%1$s: %2$s', 'woocommerce-booking' ),
		esc_html( get_option( 'checkout_item-meta-date' ) ),
		esc_html( $booking->item_checkout_date )
	) . "\n";
}

if ( isset( $booking->item_booking_time ) && '' !== $booking->item_booking_time ) {
	echo sprintf(
		/* Translators: %1$s Time Label, %2$s Time Value */
		esc_html__( '%1$s: %2$s', 'woocommerce-booking' ),
		esc_html( get_option( 'book_item-meta-time' ) ),
		esc_html( $booking->item_booking_time )
	) . "\n";
}

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

if ( bkap_common::bkap_order_requires_confirmation( $bkaporder ) && 'pending-confirmation' === $booking->item_booking_status ) {
	echo esc_html__( 'This booking is awaiting your approval. Please check it and inform the customer if the date is available or not.', 'woocommerce-booking' ) . "\n\n";
}

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( ! empty( $additional_content ) ) {
	echo esc_html( wp_strip_all_tags( wptexturize( $additional_content ) ) ) . "\n\n----------------------------------------\n\n";
}

echo esc_html( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
