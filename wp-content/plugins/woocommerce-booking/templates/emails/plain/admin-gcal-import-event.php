<?php
/**
 * Admin new imported event email.
 *
 * @package Email/AdminGCalImportEvent
 */

$opening_paragraph = __( 'A new event has been imported. The details of the event are as follows:', 'woocommerce-booking' );

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

/* translators: %s: Event Summary */
echo sprintf( esc_html__( 'Event Summary: %s', 'woocommerce-booking' ), esc_html( $event_details->event_summary ) ) . "\n";

/* translators: %s: Event Description */
echo sprintf( esc_html__( 'Event Description: %s', 'woocommerce-booking' ), esc_html( $event_details->event_description ) ) . "\n";

/* translators: %s: Event Start Date */
echo sprintf( esc_html__( 'Event Start Date: %s', 'woocommerce-booking' ), esc_html( $event_details->booking_start ) ) . "\n";

if ( isset( $event_details->booking_end ) && '' !== $event_details->booking_end ) {
	/* translators: %s: Event End Date */
	echo sprintf( esc_html__( 'Event End Date: %s', 'woocommerce-booking' ), esc_html( $event_details->booking_end ) ) . "\n";
}

if ( isset( $event_details->booking_time ) && '' !== $event_details->booking_time ) {
	/* translators: %s: Event Time */
	echo sprintf( esc_html__( 'Event Time: %s', 'woocommerce-booking' ), esc_html( $event_details->booking_time ) ) . "\n";
}

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo esc_html__( 'This event has been imported and needs to be mapped. Please check it and map the event to the corresponding product to ensure it\'s added to the list of bookings on the website.', 'woocommerce-booking' ) . "\n\n";

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( ! empty( $additional_content ) ) {
	echo esc_html( wp_strip_all_tags( wptexturize( $additional_content ) ) ) . "\n\n----------------------------------------\n\n";
}

echo esc_html( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
