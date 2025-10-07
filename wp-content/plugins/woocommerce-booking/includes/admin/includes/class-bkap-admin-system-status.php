<?php
/**
 * Bookings and Appointment Plugin for WooCommerce
 *
 * Class for handling System Status
 *
 * @author   Tyche Softwares
 * @package  BKAP/BKAP-System-Status
 * @category Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * System Status.
 */
class BKAP_Admin_System_Status {

	/**
	 * Returns the plugin global settings
	 *
	 * @param array $prefix - contains a list of prefixes to be searched for in wp_options.
	 * @return array $settings - contains the plugin settings.
	 * @since 4.12.0
	 */
	public static function bkap_get_plugin_data( $prefix = '', $status = '' ) {

		global $wpdb;

		// we can't fetch any data without the prefix.
		if ( ! is_array( $prefix ) || count( $prefix ) === 0 ) {
			return;
		}

		$settings = array();

		foreach ( $prefix as $prefix_data ) {
			$query_labels = 'SELECT option_id, option_name, option_value FROM `' . $wpdb->prefix . "options`
								WHERE option_name LIKE '%s'";
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
			$get_labels = $wpdb->get_results( $wpdb->prepare( $query_labels, "%$prefix_data%" ) );

			if ( is_array( $get_labels ) && count( $get_labels ) > 0 ) {
				foreach ( $get_labels as $results_data ) {
					if ( $results_data->option_name === 'woocommerce_booking_global_settings' ) {
						$global_settings = bkap_json_decode( $results_data->option_value );
						foreach ( $global_settings as $g_key => $g_data ) {
							$settings[ $g_key ] = $g_data;
						}
					} else {

						if ( '' === $status && false !== strpos( $results_data->option_name, 'imported_event' ) ) {
							continue;
						}

						if ( strpos( $results_data->option_name, 'orddd' ) !== false
							|| strpos( $results_data->option_name, 'acap' ) !== false
							|| strpos( $results_data->option_name, 'wbk_' ) !== false
							|| strpos( $results_data->option_name, 'birchschedule' ) !== false
							|| strpos( $results_data->option_name, 'bookly_' ) !== false
							|| strpos( $results_data->option_name, 'BookingBug' ) !== false ) {
							continue;
						}

						$settings[ $results_data->option_name ] = $results_data->option_value;
					}
				}
			}
		}

		return $settings;
	}

	/**
	 * Returns the generic site information like
	 * WordPress version, WooCommerce version and so on.
	 *
	 * @return array $generic - basic site information
	 * @since 4.12.0
	 */
	public static function bkap_get_generic() {
		$generic                  = array();
		$generic['Home URL']      = get_option( 'home' );
		$generic['Site URL']      = get_option( 'siteurl' );
		$generic['WP Version']    = get_bloginfo( 'version' );
		$generic['WP Multisite']  = is_multisite() ? 'Yes' : 'No';
		$generic['WP Debug Mode'] = ( WP_DEBUG === true ) ? 'On' : 'Off';
		$generic['WP Language']   = get_bloginfo( 'language' );
		$generic['WC Version']    = get_option( 'woocommerce_version' );
		return $generic;
	}

	/**
	 * Formats and returns the data received in a
	 * readable format by replacing _ with spaces and adding
	 * End of lines for each entry in the array
	 *
	 * @param array   $data - Dates that needs to be prettied
	 * @param boolean $replace - TRUE: replace _ with space; FALSE: retains the _ as is.
	 * @param string  $line_break - What sort of Line break to add after each array entry
	 *
	 * @return string $pretty_data - All the data concatenated and formatted into readable lines.
	 * @since 4.12.0
	 */
	public static function bkap_pretty_data( $data = array(), $replace = false, $line_break = '<br><br>' ) {

		$pretty_data = '';
		if ( is_array( $data ) && count( $data ) > 0 ) {
			foreach ( $data as $d_label => $d_value ) {
				if ( $replace ) {
					$d_label = str_replace( '_', ' ', $d_label );
				}

				$pretty_data .= "$d_label: " . print_r( $d_value, true ) . $line_break; //phpcs:ignore
			}
		}

		return $pretty_data;
	}

	/**
	 * Returns the settings data for export.
	 *
	 *  @since 4.12.0
	 */
	public static function bkap_export_data() {

		// fetch generic information.
		$generic_settings = self::bkap_get_generic();

		// Arrange it to make sense.
		$text_generic = self::bkap_pretty_data( $generic_settings, true, PHP_EOL );
		$text_generic = __( '### WordPress Environment ###', 'woocommerce-booking' ) . PHP_EOL . '' . PHP_EOL . $text_generic;

		// fetch the plugin data.
		$settings    = self::bkap_get_plugin_data( array( 'book', 'bkap_' ), 'global' );
		$text_plugin = self::bkap_pretty_data( $settings, false, PHP_EOL );
		$text_plugin = '' . PHP_EOL . __( '### Plugin Settings ###', 'woocommerce-booking' ) . PHP_EOL . '' . PHP_EOL . $text_plugin;

		return $text_generic . $text_plugin;
	}
}
