<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * REST API for Reminders.
 *
 * @author   Tyche Softwares
 * @package  BKAP/Admin/API/Reminders
 * @category Classes
 * @since    5.19.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * BKAP REST API.
 *
 * @since 5.19.0
 */
class BKAP_Admin_API_Reminders extends BKAP_Admin_API {


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

		// Fetch Reminder data.
		register_rest_route(
			self::$base_endpoint,
			'reminders/fetch',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'fetch_reminders_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Fetch Reminder by ID.
		register_rest_route(
			self::$base_endpoint,
			'reminders/fetch-reminder',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'fetch_reminder' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Table Data.
		register_rest_route(
			self::$base_endpoint,
			'reminders/table/display',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'return_table_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		register_rest_route(
			self::$base_endpoint,
			'reminders/save-update',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'save_update_reminder' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Trash Reminder.
		register_rest_route(
			self::$base_endpoint,
			'reminders/trash-reminder',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'trash_reminder' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Restore Reminder.
		register_rest_route(
			self::$base_endpoint,
			'reminders/restore-reminder',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'restore_reminder' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Send Manual Reminder.
		register_rest_route(
			self::$base_endpoint,
			'reminders/send-manual-reminder',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'send_manual_reminder' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// To manually send the booking reminder from the edit booking screen.
		register_rest_route(
			self::$base_endpoint,
			'reminders/send-manual-booking-reminder',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'bkap_manually_send_reminder_for_booking' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Delete Reminder.
		register_rest_route(
			self::$base_endpoint,
			'reminders/delete-reminder',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'delete_reminder' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Delete Trashed Reminders.
		register_rest_route(
			self::$base_endpoint,
			'reminders/delete-trashed-reminders',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'delete_trashed_reminders' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);
	}

	/**
	 * Returns Reminders Data.
	 *
	 * @param bool $return_raw Whether to return the Raw response.
	 *
	 * @since 5.19.0
	 */
	public static function fetch_reminders_data( $return_raw = false ) {

		$args = array(
			'post_type'      => array( 'product' ),
			'posts_per_page' => -1,
			'post_status'    => array( 'publish', 'pending', 'draft', 'future', 'private', 'inherit' ),
			'fields'         => 'ids',
		);

		if ( BKAP_Vendors::bkap_is_vendor( get_current_user_id() ) ) {
			$args['author'] = get_current_user_id();
		}

		$products = array();
		$posts    = get_posts( $args );

		if ( ! is_wp_error( $posts ) && is_array( $posts ) && count( $posts ) ) {

			$products[] = array(
				'id'    => 'all',
				'title' => __( 'All Products', 'woocommerce-booking' ),
			);

			foreach ( $posts as $post_id ) {
				$products[] = array(
					'value' => $post_id,
					'label' => get_the_title( $post_id ),
				);
			}
		}

		$email_content = 'Hi {customer_first_name},

You have a booking of {product_title} on {start_date}. 
        
Your Order # : {order_number}
Order Date : {order_date}
Your booking id is: {booking_id}';

		$response = array(
			'sending_delay_options'         => array(
				'hours'  => __( 'Hour(s)', 'woocommerce-booking' ),
				'days'   => __( 'Day(s)', 'woocommerce-booking' ),
				'months' => __( 'Month(s)', 'woocommerce-booking' ),
				'years'  => __( 'Year(s)', 'woocommerce-booking' ),
			),
			'trigger_options'               => array(
				'before_booking_date' => __( 'Before Booking Date', 'woocommerce-booking' ),
				'after_booking_date'  => __( 'After Booking Date', 'woocommerce-booking' ),
			),
			'products'                      => $products,
			'sms_content'                   => $email_content,
			'email_subject'                 => __( '[{blogname}] You have a booking for {product_title}', 'woocommerce-booking' ),
			'email_heading'                 => __( 'Booking Reminder', 'woocommerce-booking' ),
			'email_content'                 => 'Hello {customer_first_name},

You have an upcoming booking. The details of your booking are shown below.

{booking_table}',
			'order_ids'                     => array_unique( bkap_common::bkap_get_orders_with_bookings( apply_filters( 'bkap_get_bookings_args_for_manual_reminder', array() ) ) ),
			'send_test_reminder_modal'      => sprintf(
				'
				<div class="send_test_reminder_modal">
					<h2>%s</h2>
					<p>%s</p>
					<input class="ib-md" type="text" id="send_test_reminder_email_address" placeholder="%s" required>
					<p class="error"></p>
				</div>
			',
				__( 'Send Test Reminder Email', 'woocommerce-booking' ),
				__( 'Test your Reminder Emails. Enter an email address that the test email will be sent to.', 'woocommerce-booking' ),
				__( 'Email Address', 'woocommerce-booking' )
			),
			'manual_reminder_email_content' => get_option( 'reminder_message', $email_content ),
			'reminder_status_options'       => array(
				'bkap-active'   => __( 'Active', 'woocommerce-booking' ),
				'bkap-inactive' => __( 'Inactive', 'woocommerce-booking' ),
			),
		);

		return self::return_response( $response, $return_raw );
	}

	/**
	 * Returns Manual Reminder Data.
	 *
	 * @param bool $return_raw Whether to return the Raw response.
	 *
	 * @since 5.19.0
	 */
	public static function fetch_manual_reminder_data( $return_raw = false ) {

		$booking_ids = array();
		$bookings    = bkap_common::bkap_get_bookings( array( 'paid', 'confirmed' ), apply_filters( 'bkap_get_bookings_args_for_manual_reminder', array() ) );

		foreach ( $bookings as $key => $value ) {
			array_push( $booking_ids, $value->get_id() );
		}

		$product_ids = array();
		$posts       = bkap_common::ts_get_all_bookable_products( apply_filters( 'bkap_get_product_args_for_manual_reminder', array() ) );

		if ( ! is_wp_error( $posts ) && is_array( $posts ) && count( $posts ) ) {
			foreach ( $posts as $value ) {
				$product_ids[] = array(
					'id'    => $value->ID,
					'title' => $value->post_title,
				);
			}
		}

		return self::return_response(
			array(
				'order_ids'                     => array_unique( bkap_common::bkap_get_orders_with_bookings( apply_filters( 'bkap_get_bookings_args_for_manual_reminder', array() ) ) ),
				'booking_ids'                   => $booking_ids,
				'product_ids'                   => $product_ids,
				'manual_reminder_email_subject' => apply_filters( 'bkap_manual_reminder_email_subject', get_option( 'reminder_subject', __( 'Booking Reminder', 'woocommerce-booking' ) ) ),
				'manual_reminder_email_content' => self::fetch_reminders_data( true )['manual_reminder_email_content'],
			),
			$return_raw
		);
	}

	/**
	 * Returns Reminder Data via ID.
	 *
	 * @param  WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function fetch_reminder( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$reminder_id   = $request->get_param( 'reminder_id' );
		$reminder      = new BKAP_Reminder( $reminder_id );
		$sending_delay = $reminder->get_sending_delay();
		$reminder_data = array(
			'id'                  => $reminder_id,
			'title'               => $reminder->get_title(),
			'email_subject'       => $reminder->get_email_subject(),
			'email_heading'       => $reminder->get_email_heading(),
			'email_content'       => $reminder->get_email_content(),
			'products'            => $reminder->get_products(),
			'trigger'             => $reminder->get_trigger(),
			'sending_delay_unit'  => self::check( $sending_delay, 'delay_unit', 0 ),
			'sending_delay_value' => self::check( $sending_delay, 'delay_value', 'hours' ),
			'sms_content'         => $reminder->get_sms_body(),
			'is_sms_enabled'      => $reminder->get_enable_sms(),
			'status'              => $reminder->get_status(),
		);

		return self::response( 'success', array( 'data' => $reminder_data ) );
	}

	/**
	 * Returns Table Data.
	 *
	 * @param  WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function return_table_data( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		// Load WordPress Administration APIs.
		include_once ABSPATH . 'wp-admin/includes/admin.php';

		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/includes/view-reminders/class-bkap-admin-view-reminders-table.php' );

		$data = $request->get_param( 'data' );

		if ( is_array( $data ) && count( $data ) > 0 ) {
			$table = new BKAP_Admin_View_Reminders_Table();
			$table->populate_data(
				array(
					'order'   => self::check( $data, 'order', 'asc' ),
					'orderby' => self::check( $data, 'orderby', 'title' ),
					'page'    => self::check( $data, 'page', 1 ),
					'search'  => self::check( $data, 'search', '' ),
					'status'  => self::check( $data, 'status', '' ),
				)
			);

			return self::response( 'success', $table->ajax_response() );
		}

		return self::response( 'error', array( 'error_description' => 'Unknown Error' ) );
	}

	/**
	 * Trash Reminder.
	 *
	 * @param  WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function trash_reminder( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$reminder_ids = $request->get_param( 'reminder_id' );

		if ( ! is_array( $reminder_ids ) ) {
			$reminder_ids = array( $reminder_ids );
		}

		foreach ( $reminder_ids as $reminder_id ) {

			if ( ! current_user_can( 'delete_post', $reminder_id ) ) {
				/* Translators: %s Reminder ID */
				return self::response( 'error', array( 'error_description' => sprintf( __( 'Sorry, you do not have the permission to move Reminder #%s to Trash', 'woocommerce-booking' ), $reminder_id ) ) );
			}

			if ( ! wp_trash_post( $reminder_id ) ) {
				/* Translators: %s Reminder ID */
				return self::response( 'error', array( 'error_description' => sprintf( __( 'An Error was encountered while trying to move Reminder #%s to Trash', 'woocommerce-booking' ), $reminder_id ) ) );
			}
		}

		$message = 1 === count( $reminder_ids ) ? __( 'Reminder has been trashed successfully.', 'woocommerce-booking' ) : __( 'Reminders have been trashed successfully.', 'woocommerce-booking' );

		return self::response( 'success', array( 'message' => $message ) );
	}

	/**
	 * Add new Reminder.
	 *
	 * @param  WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function save_update_reminder( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$data = $request->get_param( 'data' );

		if ( ! is_array( $data ) || ( is_array( $data ) && 0 === count( $data ) ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Wrong Request', 'woocommerce-booking' ) ) );
		}

		$id                  = self::check( $data, 'id', '' );
		$title               = self::check( $data, 'title', '' );
		$send_test_email     = self::check( $data, 'send_test_email', '' );
		$email_address       = self::check( $data, 'email_address', '' );
		$email_subject       = sanitize_text_field( self::check( $data, 'email_subject', '' ) );
		$email_heading       = sanitize_text_field( self::check( $data, 'email_heading', '' ) );
		$email_content       = wp_filter_post_kses( self::check( $data, 'email_content', '' ) );
		$sending_delay_unit  = self::check( $data, 'sending_delay_unit', '' );
		$sending_delay_value = self::check( $data, 'sending_delay_value', '' );
		$trigger             = self::check( $data, 'trigger', '' );
		$products            = self::check( $data, 'products', '' );
		$is_sms_enabled      = self::check( $data, 'is_sms_enabled', '' );
		$sms_content         = wp_filter_post_kses( self::check( $data, 'sms_content', '' ) );
		$status              = self::check( $data, 'status', 'bkap-active' );

		if ( '' === $id || '' === $email_subject || '' === $email_heading ) {
			return self::response( 'error', array( 'error_description' => 'Some required fields are missing.' ) );
		}

		if ( 'yes' === $send_test_email ) {
			if ( '' === $email_address ) {
				return self::response( 'error', array( 'error_description' => 'Error: The email address field has been l;eft empty. Pleae try again.' ) );
			}

			$booking = bkap_common::bkap_get_bookings( array( 'paid', 'confirmed' ), apply_filters( 'bkap_get_bookings_args_for_manual_reminder', array( 'posts_per_page' => 1 ) ) );

			if ( count( $booking ) === 0 ) {
				$booking = array();
				$item_id = 0;
			} else {
				$booking = $booking[0];
				$item_id = $booking->get_item_id();
			}

			$mailer              = WC()->mailer();
			$reminder            = $mailer->emails['BKAP_Email_Booking_Reminder'];
			$reminder->recipient = ( '' === $reminder->recipient ) ? $email_address : ',' . $email_address;
			$reminder->trigger( $item_id, $email_subject, $email_content, $email_heading, $email_address, true );

			$twilio_details = bkap_get_sms_settings();

			if ( is_array( $twilio_details ) && count( $twilio_details ) > 0 ) {
				BKAP_Twilio::bkap_send_automatic_sms_reminder( $booking, $twilio_details, $item_id );
			}

			return self::response( 'success', array( 'message' => __( 'Test Reminder has been sent successfully.', 'woocommerce-booking' ) ) );
		}

		if ( 0 === (int) $id ) {
			$id = wp_insert_post(
				array(
					'post_title'   => $title,
					'post_content' => '',
					'post_status'  => $status,
					'post_type'    => 'bkap_reminder',
				)
			);

			if ( ! $id || is_wp_error( $id ) ) {
				return self::response( 'error', array( 'error_description' => 'Error encountered while trying to save Reminder data.' ) );
			}

			$message = __( 'Reminder has been added successfully.', 'woocommerce-booking' );
		} else {
			if ( ! wp_update_post(
				array(
					'ID'          => $id,
					'post_title'  => $title,
					'post_status' => $status,
				)
			)
			) {
				return self::response( 'error', array( 'error_description' => 'Error encountered while trying to update Reminder Data for #' . $id ) );
			}

			$message = __( 'Reminder has been updated successfully.', 'woocommerce-booking' );
		}

		update_post_meta( $id, 'bkap_email_content', $email_content );
		update_post_meta( $id, 'bkap_email_subject', $email_subject );
		update_post_meta( $id, 'bkap_email_heading', $email_heading );
		update_post_meta(
			$id,
			'bkap_sending_delay',
			array(
				'delay_value' => $sending_delay_value,
				'delay_unit'  => $sending_delay_unit,
			)
		);
		update_post_meta( $id, 'bkap_delay_value', $sending_delay_value );
		update_post_meta( $id, 'bkap_delay_unit', $sending_delay_unit );
		update_post_meta( $id, 'bkap_trigger', $trigger );
		update_post_meta( $id, 'bkap_products', $products );
		update_post_meta( $id, 'bkap_sms_body', $sms_content );
		update_post_meta( $id, 'bkap_enable_sms', $is_sms_enabled );

		if ( ! as_next_scheduled_action( 'bkap_auto_reminder_emails' ) ) {
			as_schedule_recurring_action( time(), HOUR_IN_SECONDS, 'bkap_auto_reminder_emails' );
		}

		do_action( 'bkap_reminder_save_data', $id );

		return self::response( 'success', array( 'message' => $message ) );
	}

	/**
	 * Sends Manual Reminder for Selected Data.
	 *
	 * @param  WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function send_manual_reminder( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$data = $request->get_param( 'data' );

		if ( ! is_array( $data ) || ( is_array( $data ) && 0 === count( $data ) ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Wrong Request', 'woocommerce-booking' ) ) );
		}

		$is_draft          = self::check( $data, 'is_draft', '' );
		$vendor_id         = self::check( $data, 'vendor_id', '' );
		$send_reminder_for = self::check( $data, 'send_reminder_for', '' );
		$order_ids         = self::check( $data, 'order_ids', '' );
		$booking_ids       = self::check( $data, 'booking_ids', '' );
		$product_ids       = self::check( $data, 'product_ids', '' );
		$email_subject     = sanitize_text_field( self::check( $data, 'email_subject', '' ) );
		$email_content     = wp_filter_post_kses( self::check( $data, 'email_content', '' ) );

		if ( '' === $email_subject || '' === $email_content ) {
			return self::response( 'error', array( 'error_description' => 'Some required fields are missing.' ) );
		}

		if ( 'yes' === $is_draft ) {
			$is_vendor = BKAP_Vendors::bkap_is_vendor( (int) $vendor_id );
			update_option( $is_vendor ? 'bkap_vendor_reminder_message_' . $vendor_id : 'reminder_message', $email_content );
			update_option( $is_vendor ? 'bkap_vendor_reminder_subject_' . $vendor_id : 'reminder_subject', $email_subject );

			return self::response( 'success', array( 'message' => __( 'Reminder data has been successfully saved as draft', 'woocommerce-booking' ) ) );
		}

		if ( '' === $send_reminder_for ) {
			return self::response( 'error', array( 'error_description' => 'Please select an option for the "Send Reminder for" field.' ) );
		}

     $current_date = gmdate( bkap_common::bkap_get_date_format() ); // phpcs:ignore
		$reminder  = WC()->mailer()->emails['BKAP_Email_Booking_Reminder'];

		if ( 'product' === $send_reminder_for ) {
			if ( ! is_array( $product_ids ) || ( is_array( $product_ids ) && 0 === count( $product_ids ) ) ) {
				return self::response( 'error', array( 'error_description' => __( 'No Product(s) have been selected.', 'woocommerce-booking' ) ) );
			}

			foreach ( $product_ids as $product_id ) {
				$bookings = bkap_common::bkap_get_bookings_by_product( $product_id );

				foreach ( $bookings as $booking ) {
					$item_id = $booking->get_item_id();
					$reminder->trigger( $item_id, $email_subject, $email_content );
					do_action( 'bkap_send_manual_reminder_emails', $booking, $item_id );
				}
			}

			return self::response( 'success', array( 'message' => __( 'Reminder has been sent successfully.', 'woocommerce-booking' ) ) );
		}

		if ( 'order' === $send_reminder_for ) {
			if ( ! is_array( $order_ids ) || ( is_array( $order_ids ) && 0 === count( $order_ids ) ) ) {
				return self::response( 'error', array( 'error_description' => __( 'No Order ID(s) have been selected.', 'woocommerce-booking' ) ) );
			}

			foreach ( $order_ids as $order_id ) {
				$order_bookings = bkap_common::get_booking_ids_from_order_id( $order_id );

				foreach ( $order_bookings as $booking_id ) {
					$booking = new BKAP_Booking( $booking_id );

					if ( strtotime( $booking->get_start() ) > strtotime( $current_date ) ) {
						$item_id = $booking->get_item_id();
						$reminder->trigger( $item_id, $email_subject, $email_content );
						do_action( 'bkap_send_manual_reminder_emails', $booking, $item_id );
					}
				}
			}

			return self::response( 'success', array( 'message' => __( 'Reminder has been sent successfully.', 'woocommerce-booking' ) ) );
		}

		if ( 'booking' === $send_reminder_for ) {
			if ( ! is_array( $booking_ids ) || ( is_array( $booking_ids ) && 0 === count( $booking_ids ) ) ) {
				return self::response( 'error', array( 'error_description' => __( 'No Booking ID(s) have been selected.', 'woocommerce-booking' ) ) );
			}

			foreach ( $booking_ids as $booking_id ) {
				$booking = new BKAP_Booking( $booking_id );

				if ( strtotime( $booking->get_start() ) > strtotime( $current_date ) ) {
					$item_id = $booking->get_item_id();
					$reminder->trigger( $item_id, $email_subject, $email_content );
					do_action( 'bkap_send_manual_reminder_emails', $booking, $item_id );
				}
			}

			return self::response( 'success', array( 'message' => __( 'Reminder has been sent successfully.', 'woocommerce-booking' ) ) );
		}

		return self::response( 'error', array( 'error_description' => 'An Unknown Error has been encountered.' ) );
	}

	/**
	 * Function to manually send the reminder for the booking from the edit booking screen.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 6.4.0
	 */
	public static function bkap_manually_send_reminder_for_booking( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$booking_id  = $request->get_param( 'booking_id' );
		$booking     = new BKAP_Booking( $booking_id );
		$reminder_id = $request->get_param( 'reminder_id' );

		if ( $reminder_id ) {
			$reminder   = new BKAP_Reminder( $reminder_id );
			$subject    = $reminder->get_email_subject();
			$message    = $reminder->get_email_content();
			$heading    = $reminder->get_email_heading();
			$trigger    = $reminder->get_trigger();
			$enable_sms = $reminder->get_enable_sms();
			$sms_body   = $reminder->get_sms_body();

			$item_id        = $booking->get_item_id();
			$mailer         = WC()->mailer();
			$reminder_email = $mailer->emails['BKAP_Email_Booking_Reminder'];

			$reminder_email->trigger( $item_id, $subject, $message, $heading );

			if ( 'on' === $enable_sms ) {
				$vendor_id           = $booking->get_vendor_id();
				$main_twilio_details = bkap_get_sms_settings(); // Getting SMS settings.
				$vendor_sms          = BKAP_Vendors::bkap_vendor_sms_settings(); // key - Vendor ID & Value - Hours.
				if ( isset( $vendor_sms[ $vendor_id ] ) ) {
					$vendor_twilio_details         = $vendor_sms[ $vendor_id ];
					$vendor_twilio_details['body'] = $sms_body;
					BKAP_Twilio::bkap_send_automatic_sms_reminder( $booking, $vendor_twilio_details, $item_id );
				} else {
					if ( is_array( $main_twilio_details ) ) {
						$main_twilio_details['body'] = $sms_body;
						BKAP_Twilio::bkap_send_automatic_sms_reminder( $booking, $main_twilio_details, $item_id );
					}
				}
			}

			do_action( 'bkap_send_manual_reminder_emails', $booking, $item_id );
		} else {
			$email_subject = apply_filters( 'bkap_manual_reminder_email_subject', get_option( 'reminder_subject', __( 'Booking Reminder', 'woocommerce-booking' ) ) );
			$email_content = self::fetch_reminders_data( true )['manual_reminder_email_content'];
			$reminder      = WC()->mailer()->emails['BKAP_Email_Booking_Reminder'];
			$item_id       = $booking->get_item_id();
			$reminder->trigger( $item_id, $email_subject, $email_content );
			do_action( 'bkap_send_manual_reminder_emails', $booking, $item_id );
		}

		return self::response( 'success', array( 'message' => __( 'Reminder is sent successfully.', 'woocommerce-booking' ) ) );
	}

	/**
	 * Restore Reminder.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function restore_reminder( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$reminder_ids = $request->get_param( 'reminder_id' );

		if ( ! is_array( $reminder_ids ) ) {
			$reminder_ids = array( $reminder_ids );
		}

		foreach ( $reminder_ids as $reminder_id ) {

			if ( ! wp_untrash_post( $reminder_id ) ) {
				return self::response( 'error', array( 'error_description' => __( 'An Error was encountered while trying to restore the Reminder from Trash', 'woocommerce-booking' ) ) );
			}
		}

		$message = 1 === count( $reminder_ids ) ? __( 'Reminder has been restored successfully.', 'woocommerce-booking' ) : __( 'Reminders have been restored successfully.', 'woocommerce-booking' );

		return self::response( 'success', array( 'message' => $message ) );
	}

	/**
	 * Delete Reminder.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function delete_reminder( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$reminder_ids = $request->get_param( 'reminder_id' );

		if ( ! is_array( $reminder_ids ) ) {
			$reminder_ids = array( $reminder_ids );
		}

		foreach ( $reminder_ids as $reminder_id ) {

			if ( ! current_user_can( 'delete_post', $reminder_id ) ) {
				return self::response( 'error', array( 'error_description' => __( 'Sorry, you do not have the permission to delete the Reminder', 'woocommerce-booking' ) ) );
			}

			if ( ! wp_delete_post( $reminder_id, true ) ) {
				return self::response( 'error', array( 'error_description' => __( 'An Error was encountered while trying to delete the Reminder', 'woocommerce-booking' ) ) );
			}
		}

		$message = 1 === count( $reminder_ids ) ? __( 'Reminder has been deleted successfully.', 'woocommerce-booking' ) : __( 'Reminders have been deleted successfully.', 'woocommerce-booking' );

		return self::response( 'success', array( 'message' => $message ) );
	}

	/**
	 * Delete Trashed Reminders.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function delete_trashed_reminders( WP_REST_Request $request ) {

		global $wpdb;

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
		$ids       = (array) $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type=%s AND post_status = %s", 'bkap_reminder', 'trash' ) );
		$ids_count = count( $ids );

		if ( 0 === $ids_count ) {
			return self::response( 'error', array( 'error_description' => __( 'There are no Trashed Reminders for deletion.', 'woocommerce-booking' ) ) );
		}

		$id_count = 0;

		foreach ( $ids as $id ) {

			if ( wp_delete_post( $id ) ) {
				$id_count++;
			}
		}

		if ( $id_count === $ids_count ) {
			return self::response( 'success', array( 'message' => __( 'Trashed Reminders have been deleted successfully.', 'woocommerce-booking' ) ) );
		} elseif ( $id_count > 0 ) {
			return self::response( 'success', array( 'message' => __( 'Some Trashed Reminders have been deleted successfully. There were some that could not be deleted due to some errors.', 'woocommerce-booking' ) ) );
		}

		return self::response( 'error', array( 'error_description' => __( 'Error encountered while trying to delete the trashed items.', 'woocommerce-booking' ) ) );
	}
}
