<?php
/**
 * Erase Booking data in
 * Dashboard->Tools->Erase Personal Data
 *
 * @since 4.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'BKAP_Privacy_Eraser' ) ) {

	/**
	 * Export Booking data in
	 * Dashboard->Tools->Erase Personal Data
	 */
	class BKAP_Privacy_Eraser {

		/**
		 * Construct
		 *
		 * @since 4.9.0
		 */
		public function __construct() {
			add_filter( 'wp_privacy_personal_data_erasers', array( &$this, 'bkap_eraser_array' ), 6 );
		}

		/**
		 * Add our eraser and it's callback function.
		 *
		 * @param array $erasers - Erasers list containing our plugin details.
		 *
		 * @since 4.9.0
		 */
		public static function bkap_eraser_array( $erasers = array() ) {

			$eraser_list = array();
			// Add our eraser and it's callback function.
			$eraser_list['bkap_gcal_event'] = array(
				'eraser_friendly_name' => __( 'Google Event', 'woocommerce-booking' ),
				'callback'             => array( 'BKAP_Privacy_Eraser', 'bkap_gcal_data_eraser' ),
			);

			$erasers = array_merge( $erasers, $eraser_list );

			return $erasers;

		}

		/**
		 * Erases personal data for Google Events.
		 *
		 * @param string  $email_address - EMail Address for which personal data is being exported.
		 * @param integer $page - The Eraser page number.
		 * @return array $reponse - Whether the process was successful or no.
		 *
		 * @hook wp_privacy_personal_data_erasers
		 * @global $wpdb
		 * @since 4.9.0
		 */
		public static function bkap_gcal_data_eraser( $email_address, $page ) {

			global $wpdb;

			$page            = (int) $page;
			$user            = get_user_by( 'email', $email_address ); // Check if user has an ID in the DB to load stored personal data.
			$erasure_enabled = wc_string_to_bool( get_option( 'woocommerce_erasure_request_removes_order_data', 'no' ) );

			$response = array(
				'items_removed'  => false,
				'items_retained' => false,
				'messages'       => array(),
				'done'           => true,
			);

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
			$query_results = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT post_id FROM {$wpdb->prefix}postmeta 
					WHERE meta_key = '_bkap_description' 
					AND meta_value LIKE %s",
					'%' . $wpdb->esc_like( $email_address ) . '%'
				)
			);

			$google_ids = array();

			if ( count( $query_results ) > 0 ) {
				foreach ( $query_results as $key => $value ) {
					$google_ids[] = $value->post_id;
				}
			}

			if ( 0 < count( $google_ids ) ) {

				foreach ( $google_ids as $google_id ) {

					if ( apply_filters( 'bkap_privacy_erase_gcal_event_personal_data', $erasure_enabled, $google_id ) ) {
						self::remove_gcal_event_personal_data( $google_id );

						/* Translators: %s Order number. */
						$response['messages'][]    = sprintf( __( 'Removed personal data from google event %s.', 'woocommerce-booking' ), $google_id );
						$response['items_removed'] = true;
					} else {
						/* Translators: %s Order number. */
						$response['messages'][]     = sprintf( __( 'Personal data within google event %s has been retained.', 'woocommerce-booking' ), $google_id );
						$response['items_retained'] = true;
					}
				}

				$response['done'] = 10 > count( $google_ids );
			} else {
				$response['done'] = true;
			}

			return $response;
		}

		/**
		 * Erases the personal data for each Google Event.
		 *
		 * @param integer $google_id - Google Event ID.
		 * @global $wpdb
		 * @since 4.9.0
		 */
		public static function remove_gcal_event_personal_data( $google_id ) {
			global $wpdb;

			$anonymized_gcal = array();

			do_action( 'bkap_privacy_before_remove_gcal_event_personal_data', $google_id );

			// list the props we'll be anonymizing for cart history table.
			$props_to_remove_gcal = apply_filters(
				'bkap_privacy_remove_gcal_event_personal_data_props',
				array(
					'_bkap_description' => 'longtext',
				),
				$google_id
			);

			if ( ! empty( $props_to_remove_gcal ) && is_array( $props_to_remove_gcal ) ) {

				foreach ( $props_to_remove_gcal as $prop => $data_type ) {

					$value = get_post_meta( $google_id, $prop, true );

					if ( empty( $value ) || empty( $data_type ) ) {
						continue;
					}

					if ( function_exists( 'wp_privacy_anonymize_data' ) ) {
						$anon_value = wp_privacy_anonymize_data( $data_type, $value );
					} else {
						$anon_value = '';
					}

					$anonymized_gcal[ $prop ] = apply_filters( 'bkap_privacy_remove_gcal_personal_data_prop_value', $anon_value, $prop, $value, $data_type, $google_id );
				}

				foreach ( $anonymized_gcal as $key => $value ) {
					update_post_meta( $google_id, $key, $value );
				}
			}
		}
	}
}
