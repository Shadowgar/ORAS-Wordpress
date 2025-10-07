<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * REST API for Google Calendar.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/API/GoogleCalendar
 * @category    Classes
 * @since       5.19.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * BKAP REST API.
 *
 * @since 5.19.0
 */
class BKAP_Admin_API_Google_Calendar extends BKAP_Admin_API {

	/**
	 * Construct
	 *
	 * @since 5.19.0
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_endpoints' ) );
	}

	/**
	 * Function for registering the API endpoints.
	 *
	 * @since 5.19.0
	 */
	public static function register_endpoints() {

		// Fetch Google Calendar data.
		register_rest_route(
			self::$base_endpoint,
			'google-calendar/fetch',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'fetch_google_calendar_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Save Google Calendar data.
		register_rest_route(
			self::$base_endpoint,
			'google-calendar/save',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'save_google_calendar_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Test Google Calendar connection.
		register_rest_route(
			self::$base_endpoint,
			'google-calendar/test-connection',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'test_google_calendar_conection' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Import Event.
		register_rest_route(
			self::$base_endpoint,
			'google-calendar/import-event',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'import_event' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Save ICS URLs.
		register_rest_route(
			self::$base_endpoint,
			'google-calendar/save-ics-urls',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'update_ics_urls' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Logout from Google Calendar.
		register_rest_route(
			self::$base_endpoint,
			'google-calendar/logout-google-calendar',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'logout_from_google_calendar' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Save ICS URLs.
		register_rest_route(
			self::$base_endpoint,
			'google-calendar/save-json',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'save_json' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		register_rest_route(
			self::$base_endpoint,
			'google-calendar/remove-json',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'remove_json' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);
	}

	/**
	 * Returns Google Calendar Data.
	 *
	 * @param bool $return_raw Whether to return the Raw response.
	 *
	 * @since 5.19.0
	 */
	public static function fetch_google_calendar_data( $return_raw = false ) {

		$response                                    = array();
		$response['bkap_calendar_event_location']    = get_option( 'bkap_calendar_event_location', '' );
		$response['bkap_calendar_event_summary']     = get_option( 'bkap_calendar_event_summary', '' );
		$response['bkap_calendar_event_description'] = get_option( 'bkap_calendar_event_description', '' );
		$response['bkap_add_to_calendar_order_received_page'] = get_option( 'bkap_add_to_calendar_order_received_page', '' );
		$response['bkap_add_to_calendar_customer_email']      = get_option( 'bkap_add_to_calendar_customer_email', '' );
		$response['bkap_calendar_sync_integration_mode']      = get_option( 'bkap_calendar_sync_integration_mode', 'disabled' );
		$response['bkap_calendar_oauth_integration']          = get_option(
			'bkap_calendar_oauth_integration',
			array(
				'client_id'     => '',
				'client_secret' => '',
			)
		);
		if ( empty( $response['bkap_calendar_oauth_integration'] ) ) {
			$response['bkap_calendar_oauth_integration'] = array(
				'client_id'     => '',
				'client_secret' => '',
			);
		}
		$response['bkap_admin_add_to_calendar_view_booking']       = get_option( 'bkap_admin_add_to_calendar_view_booking', '' );
		$response['bkap_calendar_details_1']                       = get_option( 'bkap_calendar_details_1', array( 'bkap_calendar_id' => '', 'bkap_calendar_service_acc_email_address' => '', 'bkap_calendar_json_file_name' => '' ) );
		$response['bkap_cron_time_duration']                       = get_option( 'bkap_cron_time_duration', '1440' );
		$response['bkap_add_to_calendar_my_account_page']          = get_option( 'bkap_add_to_calendar_my_account_page', '' );
		$response['bkap_admin_add_to_calendar_email_notification'] = get_option( 'bkap_admin_add_to_calendar_email_notification', '' );
		$response['bkap_ics_feed_urls']                            = self::get_ics_feed_urls();
		$response['connect_link']                                  = '';
		$response['redirect_uri']                                  = '';
		$response['logout_url']                                    = '';
		$response['bkap_gcal_success']                             = '';
		$response['bkap_gcal_failure']                             = '';
		$response['calendars']                                     = array();

		if ( is_array( $response['bkap_calendar_details_1'] ) && 0 === count( $response['bkap_calendar_details_1'] ) ) {
			$response['bkap_calendar_details_1'] = array(
				'bkap_calendar_id'                        => '',
				'bkap_calendar_service_acc_email_address' => '',
				'bkap_calendar_json_file_name'            => ''
			);
		}

		if ( isset( $_GET['bkap_con_status'] ) ) { // phpcs:ignore
			$status = $_GET['bkap_con_status']; // phpcs:ignore
			switch ( $status ) {
				case 'success':
					$response['bkap_gcal_success'] = __( 'Google Calendar successfully connected.', 'woocommerce-booking' );
					break;
				case 'fail':
					$uploads     = wp_upload_dir(); // Set log file location.
					$uploads_dir = isset( $uploads['basedir'] ) ? $uploads['basedir'] . '/' : WP_CONTENT_DIR . '/uploads/';
					$log_file    = $uploads_dir . 'bkap-log.txt';
					/* translators: %s: Bkap Log file url. */
					$message                       = sprintf( __( 'Failed to connect to your account, please try again, if the problem persists, please check the log in the %s file and see what is happening or please contact Support team.', 'woocommerce-booking' ), $log_file );
					$response['bkap_gcal_failure'] = $message;
					break;
			}
		}

		$oauth                    = new BKAP_OAuth_Google_Calendar( 0, get_current_user_id() );
		$response['redirect_uri'] = $oauth->bkap_get_redirect_uri();
		if ( isset( $response['bkap_calendar_oauth_integration']['client_id'] ) && '' !== $response['bkap_calendar_oauth_integration']['client_id'] && isset( $response['bkap_calendar_oauth_integration']['client_secret'] ) && '' !== $response['bkap_calendar_oauth_integration']['client_secret'] ) {
			try {
				$authorization_url        = $oauth->bkap_get_google_auth_url();
				$response['connect_link'] = '' !== $authorization_url ? $authorization_url : 'javascript:void(0)';
				$response['calendars']    = $oauth->bkap_get_calendar_list_options();

				if ( $oauth->bkap_is_integration_active() ) {
					$response['logout_url'] = 'yes';
				}
			} catch ( Exception $e ) {
				// TODO: Display error message on front-end about caught exceptions.
			}
		}

		return self::return_response( $response, $return_raw );
	}

	/**
	 * Gets the ICS Feed URLs.
	 *
	 * @return array
	 *
	 * @since 5.19.0
	 */
	public static function get_ics_feed_urls( $product_id = 0 ) {

		$bkap_ics_feed_urls = '';

		if ( 0 === $product_id ) {
			$bkap_ics_feed_urls = get_option( 'bkap_ics_feed_urls', array() );
		}

		if ( is_numeric( $product_id ) && $product_id > 0 ) {
			$booking_settings = get_post_meta( $product_id, 'woocommerce_booking_settings', true );

			if ( '' !== $booking_settings && is_array( $booking_settings ) && isset( $booking_settings['ics_feed_url'] ) && is_array( $booking_settings['ics_feed_url'] ) && count( $booking_settings['ics_feed_url'] ) > 0 ) {
				$bkap_ics_feed_urls = $booking_settings['ics_feed_url'];
			}
		}

		$bkap_ics_feed_urls = '' === $bkap_ics_feed_urls || '{}' === $bkap_ics_feed_urls || '[]' === $bkap_ics_feed_urls || 'null' === $bkap_ics_feed_urls ? array() : $bkap_ics_feed_urls;
		$urls               = array();

		if ( is_array( $bkap_ics_feed_urls ) && count( $bkap_ics_feed_urls ) > 0 ) {
			foreach ( $bkap_ics_feed_urls as $url ) {
				$urls[] = array(
					'url'          => $url,
					'save'         => false,
					'import'       => true,
					'delete'       => true,
					'is_importing' => false,
				);
			}
		}

		return $urls;
	}

	/**
	 * Saves Google Calendar Data.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function save_google_calendar_data( WP_REST_Request $request ) {

		self::verify_nonce( $request );

		$data = $request->get_param( 'data' );

		if ( isset( $data['bkap_ics_feed_urls'] ) ) {
			unset( $data['bkap_ics_feed_urls'] );
		}

		wp_clear_scheduled_hook( 'woocommerce_bkap_import_events' );

		if ( is_array( $data ) ) {

			foreach ( $data as $key => $setting ) {

				$_data = $setting;

				if ( in_array( $key, array( 'bkap_calendar_oauth_integration', 'bkap_calendar_details_1' ), true ) ) {

					$temp = array();

					foreach ( $setting as $_key => $_value ) {
						$temp[ $_key ] = $_value;
					}

					$_data = $temp;
				}

				update_option( $key, $_data );
			}

			return self::response(
				'success',
				array(
					'data'    => self::fetch_google_calendar_data( true ),
					'message' => __(
						'Settings saved successfully.',
						'woocommerce-booking'
					),
				)
			);
		}

		return self::error();
	}

	/**
	 * Test Google Calendar Connection.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function test_google_calendar_conection( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$product_id = $request->get_param( 'product_id' );
		$gcal       = new BKAP_Google_Calendar();
		$response   = $gcal->bkap_test_connection_msg(
			array(
				'user_id'       => get_current_user_id(),
				'product_id'    => ( isset( $product_id ) ) ? $product_id : 0,
				'gcal_api_test' => 1,
			)
		);

		if ( $response && '' !== $response ) {
			return self::response( 'success', array( 'message' => $response ) );
		}

		return self::response( 'error', array( 'error_description' => 'Unknown Error' ) );
	}

	/**
	 * Update ICS URLs.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function save_json( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$bkap_calendar_json = $request->get_param( 'bkap_calendar_json_data' );
		$product_id         = $request->get_param( 'product_id' );
		$name               = $request->get_param( 'name' );

		// Decode the JSON data
		$bkap_calendar_json_data = json_decode( $bkap_calendar_json );

		// Check if the user has permission to upload files.
		if ( ! current_user_can( 'upload_files' ) ) {
			return self::response( 'error', array( 'error_description' => __( 'You do not have permission to upload files.', 'woocommerce-booking' ) ) );
		}

		// Check if a file was uploaded.
		if ( empty( $bkap_calendar_json_data ) ) {
			return self::response( 'error', array( 'error_description' => __( 'No file was uploaded.', 'woocommerce-booking' ) ) );
		}

		// Check if the JSON data is valid.
		if ( ! isset( $bkap_calendar_json_data->private_key ) ) {
			return self::response( 'error', array( 'error_description' => __( 'The uploaded file does not contain valid JSON data.', 'woocommerce-booking' ) ) );
		}

		$data = array( 'name' => $name );
		if ( isset( $product_id ) && $product_id > 0 ) {

			$booking_settings = bkap_setting( $product_id );

			if ( is_string( $booking_settings ) ) {
				$booking_settings = array();
			}

			$booking_settings['bkap_calendar_json_file_data']        = $bkap_calendar_json_data;
			$booking_settings['bkap_calendar_json_file_name']        = $name;
			$booking_settings['product_sync_service_acc_email_addr'] = $bkap_calendar_json_data->client_email;

			update_post_meta( $product_id, '_bkap_calendar_json_file_data', $bkap_calendar_json_data );
			update_post_meta( $product_id, '_bkap_calendar_json_file_name', $name );
			update_post_meta( $product_id, '_bkap_gcal_service_acc', $bkap_calendar_json_data->client_email );

			if ( isset( $booking_settings['product_sync_key_file_name'] ) && '' != $booking_settings['product_sync_key_file_name'] ) {
				$uploads_dir = isset( $uploads['basedir'] ) ? $uploads['basedir'] . '/' : WP_CONTENT_DIR . '/uploads/';
				if ( file_exists( $uploads_dir . 'bkap_uploads/' . $booking_settings['product_sync_key_file_name'] . '.p12' ) ) {
					unlink( $uploads_dir . 'bkap_uploads/' . $booking_settings['product_sync_key_file_name'] . '.p12' );
					unset( $booking_settings['product_sync_key_file_name'] );

					delete_post_meta( $product_id, '_bkap_gcal_key_file_name' );
				}
			}

			update_post_meta( $product_id, 'woocommerce_booking_settings', $booking_settings );

		} else {
			// Store the JSON data in the database.
			update_option( 'bkap_calendar_json_file_data', $bkap_calendar_json_data );

			$gcal_array = '' !== get_option( 'bkap_calendar_details_1' ) ? get_option( 'bkap_calendar_details_1' ) : array();
			if ( isset( $gcal_array['bkap_calendar_key_file_name'] ) ) {
				$uploads_dir = isset( $uploads['basedir'] ) ? $uploads['basedir'] . '/' : WP_CONTENT_DIR . '/uploads/';
				if ( file_exists( $uploads_dir . 'bkap_uploads/' . $gcal_array['bkap_calendar_key_file_name'] . '.p12' ) ) {
					unlink( $uploads_dir . 'bkap_uploads/' . $gcal_array['bkap_calendar_key_file_name'] . '.p12' );
					unset( $gcal_array['bkap_calendar_key_file_name'] );
				}
			}
			$gcal_array['bkap_calendar_service_acc_email_address'] = $bkap_calendar_json_data->client_email;
			$gcal_array['bkap_calendar_json_file_name']            = $name;
			update_option( 'bkap_calendar_details_1', $gcal_array );
		}
		$data['bkap_calendar_service_acc_email_address'] = $bkap_calendar_json_data->client_email;
		$data['bkap_calendar_json_file_name']            = $name;
		$data['message']                                 = __( 'File uploaded successfully!', 'woocommerce-booking' );

		return self::response( 'success', $data );
	}

	/**
	 * Remove JSON File.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function remove_json( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$product_id = $request->get_param( 'product_id' );

		if ( isset( $product_id ) && $product_id > 0 ) {

			$booking_settings = bkap_setting( $product_id );

			if ( ! is_string( $booking_settings && ! empty( $booking_settings ) ) ) {

				$update = false;

				if ( $booking_settings['bkap_calendar_json_file_data'] ) {
					unset( $booking_settings['bkap_calendar_json_file_data'] );
					delete_post_meta( $product_id, '_bkap_calendar_json_file_data' );
					$update = true;
				}
				if ( $booking_settings['bkap_calendar_json_file_name'] ) {
					unset( $booking_settings['bkap_calendar_json_file_name'] );
					delete_post_meta( $product_id, '_bkap_calendar_json_file_name' );
					$update = true;
				}
				if ( $booking_settings['product_sync_service_acc_email_addr'] ) {
					unset( $booking_settings['product_sync_service_acc_email_addr'] );
					delete_post_meta( $product_id, '_bkap_gcal_service_acc' );
					$update = true;
				}

				if ( $update ) {
					update_post_meta( $product_id, 'woocommerce_booking_settings', $booking_settings );
				}
			}
		} else {
			delete_option( 'bkap_calendar_json_file_data' );

			$gcal_array = '' !== get_option( 'bkap_calendar_details_1' ) ? get_option( 'bkap_calendar_details_1' ) : array();

			if ( isset( $gcal_array['bkap_calendar_json_file_name'] ) ) {
				unset( $gcal_array['bkap_calendar_json_file_name'] );
				unset( $gcal_array['bkap_calendar_service_acc_email_address'] );
				update_option( 'bkap_calendar_details_1', $gcal_array );
			}
		}

		$data['message'] = __( 'JSON file data removed successfully!', 'woocommerce-booking' );

		return self::response( 'success', $data );
	}

	/**
	 * Update ICS URLs.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function update_ics_urls( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$ics_urls   = $request->get_param( 'ics_urls' );
		$product_id = $request->get_param( 'product_id' );

		$urls = array();

		if ( is_array( $ics_urls ) && count( $ics_urls ) > 0 ) {
			foreach ( $ics_urls as $item ) {
				$urls[] = $item['url'];
			}
		}

		$bkap_ics_feed_urls = array();

		if ( 0 === $product_id ) {
			update_option( 'bkap_ics_feed_urls', $urls );
			$bkap_ics_feed_urls = self::get_ics_feed_urls();
		}

		if ( is_numeric( $product_id ) && $product_id > 0 ) {
			update_post_meta( $product_id, '_bkap_import_url', $urls );

			$woocommerce_booking_settings                 = get_post_meta( $product_id, 'woocommerce_booking_settings', true );
			$woocommerce_booking_settings['ics_feed_url'] = $urls;
			update_post_meta( $product_id, 'woocommerce_booking_settings', $woocommerce_booking_settings );

			$bkap_ics_feed_urls = self::get_ics_feed_urls( $product_id );
		}

		return self::response( 'success', array( 'bkap_ics_feed_urls' => $bkap_ics_feed_urls ) );
	}

	/**
	 * Import Event.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function import_event( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$id         = $request->get_param( 'id' );
		$product_id = $request->get_param( 'product_id' );
		$response   = bkap_google_calendar_sync()->bkap_setup_import(
			array(
				'$ics_url_key' => $id,
				'product_id'   => $product_id,
			)
		);

		if ( $response ) {

			if ( is_array( $response ) ) {
				if ( isset( $response['type'] ) && 'error' === $response['type'] && isset( $response['error_message'] ) && '' !== $response['error_message'] ) {
					return self::response( 'error', array( 'error_description' => $response['error_message'] ) );
				}
			} elseif ( '' !== $response ) {
				return self::response( 'success', array( 'message' => $response ) );
			}
		}

		return self::response( 'error', array( 'error_description' => 'Error encountered while trying to import the event(s).' ) );
	}

	/**
	 * Logout from Google Calendar.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function logout_from_google_calendar( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$product_id = $request->get_param( 'product_id' );

		$oauth = new BKAP_OAuth_Google_Calendar( $product_id, get_current_user_id() );
		$oauth->oauth_logout();
		return self::response(
			'success',
			array(
				'data'    => 0 === $product_id ? self::fetch_google_calendar_data( true ) : array(),
				'message' => __(
					'You have been successfully logged out.',
					'woocommerce-booking'
				),
			)
		);
	}
}
