<?php
/**
 * Customer booking confirmed email.
 *
 * @package Email/CustomerBookingConfirmed
 */

echo '= ' . esc_html( $email_heading ) . " =\n\n";

$bkaporder = wc_get_order( $booking->order_id );

if ( $bkaporder ) {
	$billing_first_name = ( version_compare( WOOCOMMERCE_VERSION, '3.0.0', '<' ) ) ? $bkaporder->billing_first_name : $bkaporder->get_billing_first_name();
	/* translators: %s: Customer's first name */
	echo sprintf( esc_html__( 'Hello %s', 'woocommerce-booking' ), esc_html( $billing_first_name ) ) . "\n\n";
}

echo esc_html__( 'Your booking has been confirmed. The details of your booking are shown below.', 'woocommerce-booking' ) . "\n\n";

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

/* translators: %s: Booked product title */
echo sprintf( esc_html__( 'Booked: %s', 'woocommerce-booking' ), esc_html( $booking->product_title ) ) . "\n";

/* translators: %1$s: Booking date label, %2$s: Selected booking date */
echo sprintf( esc_html__( '%1$s: %2$s', 'woocommerce-booking' ), esc_html( get_option( 'book_item-meta-date' ) ), esc_html( $booking->item_booking_date ) ) . "\n";

if ( isset( $booking->item_checkout_date ) && '' !== $booking->item_checkout_date ) {
	/* translators: %1$s: Checkout date label, %2$s: Selected checkout date */
	echo sprintf( esc_html__( '%1$s: %2$s', 'woocommerce-booking' ), esc_html( get_option( 'checkout_item-meta-date' ) ), esc_html( $booking->item_checkout_date ) ) . "\n";
}

if ( isset( $booking->item_booking_time ) && '' !== $booking->item_booking_time ) {
	/* translators: %1$s: Booking time label, %2$s: Selected booking time */
	echo sprintf( esc_html__( '%1$s: %2$s', 'woocommerce-booking' ), esc_html( get_option( 'book_item-meta-time' ) ), esc_html( $booking->item_booking_time ) ) . "\n";
}

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

if ( $bkaporder ) {
	$order_status = $bkaporder->get_status();
	if ( 'pending' === $order_status ) {
		/* translators: %s: Checkout payment URL */
		echo sprintf( esc_html__( 'To pay for this booking, please use the following link: %s', 'woocommerce-booking' ), esc_url( $bkaporder->get_checkout_payment_url() ) ) . "\n\n";
	}
	do_action( 'woocommerce_email_before_order_table', $bkaporder, $sent_to_admin, $plain_text, $email );

	$order_date = ( version_compare( WOOCOMMERCE_VERSION, '3.0.0', '<' ) ) ? $bkaporder->order_date : gmdate( 'Y-m-d H:i:s', $bkaporder->get_date_created()->getOffsetTimestamp() );

	/* translators: %s: Order number */
	echo sprintf( esc_html__( 'Order number: %s', 'woocommerce-booking' ), esc_html( $bkaporder->get_order_number() ) ) . "\n";

	/* translators: %s: Order date */
	echo sprintf( esc_html__( 'Order date: %s', 'woocommerce-booking' ), esc_html( date_i18n( wc_date_format(), strtotime( $order_date ) ) ) ) . "\n";
	do_action( 'woocommerce_email_order_meta', $bkaporder, $sent_to_admin, $plain_text, $email );
	echo "\n";

	$downloadable = $bkaporder->is_download_permitted();
	$args         = array(
		'show_download_links' => $downloadable,
		'show_sku'            => ( 'completed' === $order_status || 'processing' === $order_status ),
		'show_purchase_note'  => ( 'completed' === $order_status || 'processing' === $order_status ),
	);
	echo ( version_compare( WOOCOMMERCE_VERSION, '3.0.0', '<' ) ) ? $bkaporder->email_order_items_table( $args ) : wc_get_email_order_items( $bkaporder, $args ); // phpcs:ignore

	echo "==========\n\n";

	if ( $totals = $bkaporder->get_order_item_totals() ) {
		foreach ( $totals as $total ) {
			/* translators: %s: Order total label and value */
			echo esc_html( $total['label'] ) . "\t " . wp_kses_post( $total['value'] ) . "\n";
		}
	}

	echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";
	do_action( 'woocommerce_email_after_order_table', $bkaporder, $sent_to_admin, $plain_text, $email );
}

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( ! empty( $additional_content ) ) {
	echo esc_html( wp_strip_all_tags( wptexturize( $additional_content ) ) ) . "\n\n";
	echo "----------------------------------------\n\n";
}

/* translators: Email footer text */
echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
