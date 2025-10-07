<?php
// phpcs:disable WordPress.Security.NonceVerification.Recommended
$summary     = isset( $_GET['summary'] ) ? sanitize_text_field( wp_unslash( $_GET['summary'] ) ) : '';
$summary     = bkap_get_escapeString( $summary );
$summary     = str_replace( '\x0D', '', $summary ); // lf - html break.
$summary     = preg_replace( "/\r|\n/", '', $summary );
$description = isset( $_GET['description'] ) ? sanitize_text_field( wp_unslash( $_GET['description'] ) ) : '';
$description = bkap_get_escapeString( $description );
$description = str_replace( '\x0D', '', $description ); // lf - html break.
$description = preg_replace( "/\r|\n/", '', $description );
// phpcs:enable WordPress.Security.NonceVerification.Recommended

header( 'Content-type: text/calendar; charset=utf-8' );
header( 'Content-Disposition: attachment; filename=Calendar-event.ics' );

/**
 * Converts a unix timestamp to an ics-friendly format
 * NOTE: "Z" means that this timestamp is a UTC timestamp. If you need
 * to set a locale, remove the "\Z" and modify DTEND, DTSTAMP and DTSTART
 * with TZID properties (see RFC 5545 section 3.3.5 for info)
 *
 * @since 2.6
 */
function bkap_get_dateToCal( $timestamp ) {
	date_default_timezone_set( 'UTC' ); //phpcs:ignore
	$time = gmdate( 'H:i', $timestamp );
	if ( $time != '00:00' && $time != '00:01' ) {
		return gmdate( 'Ymd\THis\Z', $timestamp );
	} else {
		return gmdate( 'Ymd', $timestamp );
	}
}

/**
 * Escapes a string of characters
 *
 * @since 2.6
 */

function bkap_get_escapeString( $string ) {
	return preg_replace( '/([\,;])/', '\\\$1', $string );
}

/**
 * Echo out the ics file's contents
 *
 * @since 2.6
 */
// phpcs:disable WordPress.Security.NonceVerification
$event_location   = isset( $_GET['event_location'] ) ? sanitize_text_field( wp_unslash( $_GET['event_location'] ) ) : '';
$event_date_start = isset( $_GET['event_date_start'] ) ? sanitize_text_field( wp_unslash( $_GET['event_date_start'] ) ) : '';
$event_date_end   = isset( $_GET['event_date_end'] ) ? sanitize_text_field( wp_unslash( $_GET['event_date_end'] ) ) : '';
$current_time     = isset( $_GET['current_time'] ) ? sanitize_text_field( wp_unslash( $_GET['current_time'] ) ) : '';
// phpcs:enable WordPress.Security.NonceVerification
?>
BEGIN:VCALENDAR
PRODID:-//Microsoft Corporation//Outlook 13.0 MIMEDIR//EN
VERSION:2.0
CALSCALE:GREGORIAN
X-PRIMARY-CALENDAR:TRUE
BEGIN:VEVENT
LOCATION:<?php echo esc_html( $event_location ) . "\n"; //phpcs:ignore ?>
DTSTART:<?php echo esc_html( $event_date_start ) . "\n"; ?>
DTEND:<?php echo esc_html( $event_date_end ) . "\n"; ?>
DTSTAMP:<?php echo esc_html( bkap_get_dateToCal( $current_time ) ) . "\n"; ?>
UID:<?php echo esc_html( uniqid() ) . "\n"; ?>
DESCRIPTION:<?php echo wp_kses_post( $description ) . "\n"; ?>
SUMMARY:<?php echo wp_kses_post( $summary ) . "\n"; ?>
END:VEVENT
END:VCALENDAR
<?php
exit;
?>
