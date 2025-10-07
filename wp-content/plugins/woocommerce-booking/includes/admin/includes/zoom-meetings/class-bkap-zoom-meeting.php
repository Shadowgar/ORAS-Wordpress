<?php
/**
 * Bookings & Appointment Plugin for WooCommerce
 *
 * Class for Booking->Settings->Integration->Zoom Meetings
 *
 * @author   Tyche Softwares
 * @package  BKAP/Zoom-Meetings
 * @category Classes
 * @class    Bkap_Zoom_Meetings
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Not Allowed Here !' ); // If this file is called directly, abort.
}

if ( ! class_exists( 'Bkap_Zoom_Meetings' ) ) {
	/**
	 * Class Bkap_Zoom_Meetings.
	 */
	class Bkap_Zoom_Meetings {

		/**
		 * Class instance.
		 *
		 * @var $_instance Class Instance.
		 * @since 5.2.0
		 */
		private static $_instance = null; // phpcs:ignore

		/**
		 * Create only one instance so that it may not Repeat
		 *
		 * @since 5.2.0
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Default Constructor
		 *
		 * @since 5.2.0
		 */
		public function __construct() {

			$this->bkap_zoom_autoloader();
			$this->bkap_zoom_load_dependencies();
			$this->bkap_zoom_init_api();

			add_action( 'wp_ajax_bkap_zoom_meeting_test_connection', array( &$this, 'bkap_zoom_meeting_test_connection' ) );
			add_action( 'woocommerce_order_status_completed', array( $this, 'bkap_create_zoom_meeting_on_order_confirmed_or_processing' ), 5, 2 );
			add_action( 'woocommerce_order_status_processing', array( $this, 'bkap_create_zoom_meeting_on_order_confirmed_or_processing' ), 5, 2 );
			add_filter( 'woocommerce_email_order_items_args', array( $this, 'bkap_add_zoom_link_to_order_item' ), 8, 1 );
			add_action( 'bkap_admin_booking_data_after_booking_details', array( &$this, 'bkap_display_zoom_meeting_info_booking_details' ), 10, 2 );
			add_action( 'bkap_after_delete_booking', array( &$this, 'bkap_delete_zoom_meeting' ), 10, 2 );

			add_action( 'admin_init', array( &$this, 'bkap_assign_meetings' ), 9 );
			add_action( 'bkap_assign_meetings', array( &$this, 'bkap_assign_meetings_to_booking' ) );
			add_action( 'admin_notices', array( &$this, 'bkap_assign_meetings_to_booking_notice' ) );
		}

		/**
		 * This function will display notice for meeting assignment.
		 *
		 * @since 5.2.0
		 */
		public function bkap_assign_meetings_to_booking_notice() {

			$bkap_el_option = apply_filters( 'bkap_el_option', true );

			if ( ! $bkap_el_option ) {
				return false;
			}

			if ( 'page' !== get_post_type() && 'post' !== get_post_type() ) {

				if ( isset( $_GET['section'] ) && 'zoom_meeting' === $_GET['section'] ) { // phpcs:ignore

					if ( isset( $_GET['success'] ) && wp_unslash( $_GET['success'] ) ) {  // phpcs:ignore
						$message = __( 'Connected to Zoom successfully.', 'woocommerce-booking' );
						$class   = 'notice notice-success';
						printf( '<div class="%s"><p>%s</p></div>', esc_attr( $class ), esc_html( $message ) );
					}

					if ( isset( $_GET['logout'] ) && wp_unslash( $_GET['logout'] ) ) { // phpcs:ignore

						$message = __( 'Successfully logged out from Zoom.', 'woocommerce-booking' );
						$class   = 'notice notice-success';
						printf( '<div class="%s"><p>%s</p></div>', esc_attr( $class ), esc_html( $message ) );
					}
				}

				$key    = get_option( 'bkap_zoom_api_key', '' );
				$secret = get_option( 'bkap_zoom_api_secret', '' );

				if ( '' !== $key && '' !== $secret ) {
					$client_id      = get_option( 'bkap_zoom_client_id', '' );
					$client_secret  = get_option( 'bkap_zoom_client_secret', '' );
					$migrate_notice = get_option( 'bkap_zoom_access_token', '' );
					if ( '' === $client_id || '' === $client_secret ) {
						$class          = 'notice notice-info';
						$zoom_page_link = bkap_zoom_redirect_url();

						/* translators: %s: URL Zoom Meeting page */
						$message = sprintf( __( 'Urgent Notification: Attention, please! By September 8, 2023, Zoom will no longer support JWT app type authorization. Your site is currently benefiting from the Zoom integration offered by the Booking & Appointment Plugin for WooCommerce. To continue enjoying uninterrupted service, we recommend transitioning from JWT app type to OAuth app type <a href="%s">here.</a>', 'woocommerce-booking' ), $zoom_page_link );

						printf( '<div class="%s"><p>%s</p></div>', $class, $message ); // phpcs:ignore	
					}
				}

				$option = get_option( 'bkap_assign_meeting_scheduled', false );
				if ( $option ) {
					switch ( $option ) {
						case 'yes':
							/* translators: %s: URL of Google Calendar Sync page */
							$message = __( 'Zoom meeting links are getting generated and assigned in the background for future bookings. This process may take a little while, so please be patient.', 'woocommerce-booking' );
							$class   = 'notice notice-info';
							printf( '<div class="%s"><p><b>%s</b></p></div>', $class, $message ); // phpcs:ignore
							break;
						case 'done':
							/* translators: %s: URL of Google Calendar Sync page */
							$message = __( 'Zoom meeting links have been generated and assigned to future bookings.', 'woocommerce-booking' );
							$class   = 'notice notice-success bkap-meeting-notice is-dismissible';
							printf( '<div class="%s"><p><b>%s</b></p></div>', $class, $message ); // phpcs:ignore
							break;
					}
				}

				if ( isset( $_GET['section'] ) && 'zoom_meeting' === $_GET['section'] && ! bkap_zoom_meeting_enable() ) { // phpcs:ignore

					if ( '' !== $key && '' !== $secret ) {
						$message = __( 'Zoom Meetings - Connection Failed.', 'woocommerce-booking' );
						$class   = 'notice notice-error';
						printf( '<div class="%s"><p>%s</p></div>', $class, $message ); // phpcs:ignore
					}
				}
			}
		}

		/**
		 * This function will schedule an action to create/assign the meeting for future bookings.
		 *
		 * @since 5.2.0
		 */
		public function bkap_assign_meetings() {

			if ( isset( $_GET['section'] ) && 'zoom_meeting' === $_GET['section'] ) { // phpcs:ignore

				if ( isset( $_GET['code'] ) && '' !== $_GET['code'] ) { // phpcs:ignore
					$zoom_connection = bkap_zoom_connection();
					$data            = $zoom_connection->bkap_exchange_code_for_token( sanitize_text_field( wp_unslash( $_GET['code'] ) ) ); // phpcs:ignore

					if ( $data ) {
						if ( isset( $data['access_token'] ) && '' !== $data['access_token'] ) {
							update_option( 'bkap_zoom_access_token', $data['access_token'] );
							update_option( 'bkap_zoom_token_expiry', time() + $data['expires_in'] );
							update_option( 'bkap_zoom_access_data', $data );

							delete_option( 'bkap_zoom_api_key' );
							delete_option( 'bkap_zoom_api_secret' );

							/* $zoom_meeting_page = bkap_zoom_redirect_url();
							$query_args        = array(
								'success' => '1',
							);
							$zoom_page_link    = add_query_arg( $query_args, $zoom_meeting_page );



							wp_safe_redirect( $zoom_page_link );
							exit(); */

							$status = 'success';
						} else {
							$status = 'fail';
						}
						$redirect_args = array(
							'page'                 => 'bkap_page',
							'bkap_zoom_con_status' => $status,
							'action'               => 'integrations#/zoom',
						);
						$url           = add_query_arg( $redirect_args, admin_url( '/admin.php?' ) );

						wp_safe_redirect( $url );
						exit;
					}
				}

				// @todo - in new ui this will be done using js so later we can remove this.
				if ( isset( $_GET['logout'] ) && $_GET['logout'] ) { // phpcs:ignore

					delete_option( 'bkap_zoom_access_token' );
					delete_option( 'bkap_zoom_token_expiry' );
					delete_option( 'bkap_zoom_access_data' );
				}
			}

			if ( isset( $_POST['bkap_assign_meeting_to_booking'] ) && '' != $_POST['bkap_assign_meeting_to_booking'] ) { // phpcs:ignore
				if ( bkap_zoom_meeting_enable() ) {
					as_schedule_single_action( time() + 10, 'bkap_assign_meetings', array( 'test' => 20 ) );
					add_option( 'bkap_assign_meeting_scheduled', 'yes' );
				}
			}
		}

		/**
		 * This function will be executed for creating/assigning the meeting for future bookings.
		 *
		 * @since 5.2.0
		 */
		public function bkap_assign_meetings_to_booking() {

			$meta_query = array(
				'relation' => 'AND',
				array(
					'key'     => '_bkap_enable_booking',
					'value'   => 'on',
					'compare' => '=',
				),
				array(
					'key'     => '_bkap_zoom_meeting',
					'value'   => 'on',
					'compare' => '=',
				),
			);

			$product_ids   = bkap_common::get_woocommerce_product_list( false, 'on', 'yes', $post_status = array(), $meta_query );
			$bookings      = bkap_get_bookings_to_assign_zoom_meeting( $product_ids );
			$meeting_query = array(
				'key'     => '_bkap_zoom_meeting_link',
				'compare' => 'NOT EXISTS',
			);

			foreach ( $bookings as $booking ) {

				$start_date    = $booking->_bkap_start;
				$end_date      = $booking->_bkap_end;
				$product_id    = $booking->_bkap_product_id;
				$variation_id  = $booking->_bkap_variation_id;
				$resource_id   = $booking->_bkap_resource_id;
				$order_item_id = $booking->_bkap_order_item_id;
				$order_id      = $booking->_bkap_parent_id;
				$booking_id    = $booking->ID;

				$booking_data = array(
					'start'         => $start_date,
					'end'           => $end_date,
					'product_id'    => $product_id,
					'resource_id'   => $resource_id,
					'variation_id'  => $variation_id,
					'order_item_id' => $order_item_id,
					'parent_id'     => $order_id,
				);

				$meeting_data = self::bkap_create_zoom_meeting( $booking_id, $booking_data );

				if ( is_array( $meeting_data ) && count( $meeting_data ) > 0 ) {
					/* translators: %s: Booking ID and Meeting link. */
					$meeting_msg = sprintf( __( 'Updated Zoom Meeting Link for Booking #%1$s - %2$s', 'woocommerce-booking' ), $booking_id, isset( $meeting_data['meeting_link'] ) ? $meeting_data['meeting_link'] : '' );
					$order_obj   = wc_get_order( $order_id );
					$order_obj->add_order_note( $meeting_msg, 1, false );
				}
			}

			update_option( 'bkap_assign_meeting_scheduled', 'done' );
		}

		/**
		 * Autoloader.
		 *
		 * @since 5.2.0
		 */
		public function bkap_zoom_autoloader() {
			require_once BKAP_VENDORS_LIBRARIES_PATH . 'firebase-jwt/vendor/autoload.php';
		}

		/**
		 * INitialize the hooks
		 *
		 * @since 5.2.0
		 */
		protected function bkap_zoom_init_api() {
			// Load the Credentials.
			$bkap_zoom_connection                     = bkap_zoom_connection();
			$bkap_zoom_connection->zoom_api_key       = get_option( 'bkap_zoom_api_key' );
			$bkap_zoom_connection->zoom_api_secret    = get_option( 'bkap_zoom_api_secret' );
			$bkap_zoom_connection->zoom_client_id     = get_option( 'bkap_zoom_client_id' );
			$bkap_zoom_connection->zoom_client_secret = get_option( 'bkap_zoom_client_secret' );
		}

		/**
		 * Load the other class dependencies
		 *
		 * @since 5.2.0
		 */
		protected function bkap_zoom_load_dependencies() {
			// Include the Main Class.
			require_once BKAP_BOOKINGS_INCLUDE_PATH . 'admin/includes/zoom-meetings/class-bkap-zoom-meeting-connection.php';
		}

		/**
		 * Creating assigning the meeting link to the booking.
		 *
		 * @param int    $booking_id Booking ID.
		 * @param obj    $booking_data Booking Object.
		 * @param string $action default is blank - 'update' can be passed for update operation.
		 * @since 5.2.0
		 */
		public static function bkap_create_zoom_meeting( $booking_id, $booking_data, $action = '', $paid_check = true ) {

			$booking_obj = new BKAP_Booking( $booking_id );

			// Don't create Zoom Meeting if payment for the Order has not been made.
			// For now, we check for payment from the Order.
			// TODO: Find a way of checking for payment for the Booking itself since Zoom Meetings are actually created for the Bookings and not orders.
			$order_obj = $booking_obj->get_order();

			if ( false === $order_obj ) {

				// Stop if order information can't be retrieved. This could be as a result of the order not being created yet or custom post type not being available.
				return;
			}

			if ( ! $order_obj->get_date_paid() && $paid_check ) {
				return;
			}

			$booking_data = (array) $booking_data;
			extract( $booking_data ); // phpcs:ignore

			$meeting_info = array();
			if ( bkap_zoom_meeting_enable( $product_id, $resource_id ) ) {

				$zoom_booking_id = bkap_check_same_booking_info( $start, $end, $product_id, $variation_id, $resource_id, $booking_id );
				$meeting_label   = bkap_zoom_join_meeting_label( $product_id );
				$meeting_text    = bkap_zoom_join_meeting_text( $product_id );

				if ( 0 !== $zoom_booking_id ) {
					$meeting_link = get_post_meta( $zoom_booking_id, '_bkap_zoom_meeting_link', true );
					$meeting_data = get_post_meta( $zoom_booking_id, '_bkap_zoom_meeting_data', true );
					update_post_meta( $booking_id, '_bkap_zoom_meeting_link', $meeting_link );
					update_post_meta( $booking_id, '_bkap_zoom_meeting_data', $meeting_data );
					$meeting_link = sprintf( '<a href="%s">%s</a>', $meeting_link, $meeting_text );
					wc_add_order_item_meta( $order_item_id, $meeting_label, $meeting_link );

					$meeting_info['meeting_link'] = $meeting_link;
					$meeting_info['meeting_data'] = $meeting_data;

					// Save Zoom Meeting Information to Order Note.
					$order_meeting_link = get_post_meta( $zoom_booking_id, '_bkap_zoom_meeting_link', true );
					$order_meeting_link = sprintf( '<a href="%s">%s</a>', $order_meeting_link, $order_meeting_link );
					$order_meeting_link = $meeting_label . ': ' . $order_meeting_link;
					bkap_common::save_booking_information_to_order_note( '', $parent_id, $order_meeting_link );
				} else {
					// Creating meeting.

					$duration = 24 * 60;
					if ( $start != $end ) { // phpcs:ignore
						$t1       = strtotime( $end );
						$t2       = strtotime( $start );
						$diff     = $t1 - $t2;
						$duration = round( $diff / 60 ); // to minutes.
					}

					$product_title      = booked_product_name( $booking_obj );
					$product_title      = str_replace( '<br>', ' - ', $product_title );
					$meeting_start_date = gmdate( 'Y-m-d H:i:s', strtotime( $start ) ); // phpcs:ignore
					$topic              = $product_title . ' - ' . get_bloginfo();
					$timezone_string    = bkap_booking_get_timezone_string();
					$booking_type       = bkap_type( $product_id );
					$zooom_host         = get_post_meta( $product_id, '_bkap_zoom_meeting_host', true );
					$bkap_settings      = get_post_meta( $product_id, 'woocommerce_booking_settings', true );

					if ( $resource_id > 0 ) {
						$zooom_host = get_post_meta( $resource_id, '_bkap_resource_meeting_host', true );
					}

					$meeting_authentication = false;
					if ( isset( $bkap_settings['zoom_meeting_auth'] ) && 'on' === $bkap_settings['zoom_meeting_auth'] ) {
						$meeting_authentication = true;
					}
					$join_before_host = false;
					if ( isset( $bkap_settings['zoom_meeting_join_before_host'] ) && 'on' === $bkap_settings['zoom_meeting_join_before_host'] ) {
						$join_before_host = true;
					}
					$participant_video = false;
					if ( isset( $bkap_settings['zoom_meeting_participant_video'] ) && 'on' === $bkap_settings['zoom_meeting_participant_video'] ) {
						$participant_video = true;
					}
					$host_video = false;
					if ( isset( $bkap_settings['zoom_meeting_host_video'] ) && 'on' === $bkap_settings['zoom_meeting_host_video'] ) {
						$host_video = true;
					}
					$mute_upon_entry = false;
					if ( isset( $bkap_settings['zoom_meeting_mute_upon_entry'] ) && 'on' === $bkap_settings['zoom_meeting_mute_upon_entry'] ) {
						$mute_upon_entry = true;
					}
					$auto_recording = 'none';
					if ( isset( $bkap_settings['zoom_meeting_auto_recording'] ) && '' !== $bkap_settings['zoom_meeting_auth'] ) {
						$auto_recording = $bkap_settings['zoom_meeting_auto_recording'];
					}
					$alternative_hosts = array();
					if ( isset( $bkap_settings['zoom_meeting_alternative_host'] ) ) {
						$alternative_hosts = $bkap_settings['zoom_meeting_alternative_host'];
					}

					$meeting_data = array(
						'start_date'             => $meeting_start_date,
						'agenda'                 => $topic, // unable to find.
						'meetingTopic'           => $topic, // front par dekhase.
						'timezone'               => $timezone_string,
						'userId'                 => $zooom_host,
						'duration'               => $duration,
						'meeting_authentication' => $meeting_authentication,
						'join_before_host'       => $join_before_host,
						'host_video'             => $host_video,
						'participant_video'      => $participant_video,
						'mute_upon_entry'        => $mute_upon_entry,
						'auto_recording'         => $auto_recording,
						'alternative_hosts'      => $alternative_hosts,
					);

					if ( 'multiple_days' === $booking_type ) {
						$numberofdays               = bkap_get_days_between_two_dates( $start, $end );
						$meeting_data['type']       = 8;
						$meeting_data['duration']   = 24 * 60;
						$meeting_data['recurrence'] = array(
							'type'            => 1,
							'repeat_interval' => 1,
							'end_times'       => $numberofdays,
						);
					}

					$meeting = bkap_json_decode( bkap_zoom_connection()->bkap_create_meeting( $meeting_data ) );

					if ( isset( $meeting->join_url ) ) {
						$meeting_link       = $meeting->join_url;
						$order_meeting_link = $meeting_link;
						$meeting_data       = $meeting;

						update_post_meta( $booking_id, '_bkap_zoom_meeting_link', $meeting_link );
						update_post_meta( $booking_id, '_bkap_zoom_meeting_data', $meeting_data );
						$meeting_link = sprintf( '<a href="%s">%s</a>', $meeting->join_url, $meeting_text );
						wc_add_order_item_meta( $order_item_id, $meeting_label, $meeting_link );

						$meeting_info['meeting_link'] = $meeting_link;
						$meeting_info['meeting_data'] = $meeting_data;

						// Save Zoom Meeting Information to Order Note.
						$order_meeting_link = sprintf( '<a href="%s">%s</a>', $order_meeting_link, $order_meeting_link );
						$order_meeting_link = $meeting_label . ': ' . $order_meeting_link;
						bkap_common::save_booking_information_to_order_note( '', $parent_id, $order_meeting_link );
					} else {
						$meeting_info['meeting_error'] = $meeting->message;
						$order_obj                     = wc_get_order( $parent_id );
						/* translators: %s: Booking ID and Meeting link. */
						$meeting_msg = sprintf( __( 'Zoom Meeting Error for Booking #%1$s - %2$s', 'woocommerce-booking' ), $booking_id, $meeting->message );
						$order_obj->add_order_note( $meeting_msg );
					}
				}

				do_action( 'bkap_zoom_meeting_created', $booking_id, $booking_data, $meeting_info );
			}

			return $meeting_info;
		}

		/**
		 * Deleting Zoom Meeting.
		 *
		 * @param int $booking_id Booking ID.
		 * @param obj $booking Booking Object.
		 *
		 * @since 5.2.0
		 */
		public static function bkap_delete_zoom_meeting( $booking_id, $booking ) {

			global $wpdb;

			$meeting_link = $booking->get_zoom_meeting_link();
			$product_id   = $booking->get_product_id();

			if ( '' !== $meeting_link && bkap_zoom_meeting_enable( $product_id ) ) {
				$start_date      = $booking->get_start();
				$end_date        = $booking->get_end();
				$resource_id     = $booking->get_resource();
				$order_item_id   = $booking->get_item_id();
				$variation_id    = $booking->get_variation_id();
				$zoom_booking_id = bkap_check_same_booking_info( $start_date, $end_date, $product_id, $variation_id, $resource_id, $booking_id );

				if ( 0 === $zoom_booking_id ) {
					$meeting_data = $booking->get_zoom_meeting_data();
					$meeting_id   = $meeting_data->id;
					$meeting      = bkap_zoom_connection()->bkap_delete_meeting( $meeting_id );
				}

				update_post_meta( $booking_id, '_bkap_zoom_meeting_link', '' );
				update_post_meta( $booking_id, '_bkap_zoom_meeting_data', '' );
				$meeting_label = bkap_zoom_join_meeting_label( $product_id );

				wc_delete_order_item_meta( $order_item_id, $meeting_label );
			}
		}

		/**
		 * Displaying Zoom Meeting information in Edit Booking-> Booking Details.
		 *
		 * @param int $booking_id Booking ID.
		 * @param obj $booking Booking Object.
		 *
		 * @since 5.2.0
		 */
		public function bkap_display_zoom_meeting_info_booking_details( $booking_id, $booking ) {

			$meeting_link = $booking->get_zoom_meeting_link();
			if ( '' !== $meeting_link ) {
				$product_id    = $booking->get_product_id();
				$meeting_label = bkap_zoom_join_meeting_label( $product_id );
				$meeting_text  = bkap_zoom_join_meeting_text( $product_id );
				?>

			<p class="form-field form-field-wide">
				<label for="_bkap_zoom_meeting"><?php echo $meeting_label; ?> - <a href="<?php echo $meeting_link; ?>" target="_blank"><?php echo $meeting_text; // phpcs:ignore ?></a></label>
			</p>
				<?php
			} else {
				$product_id    = $booking->get_product_id();
				$zoom_enabled  = bkap_zoom_meeting_enable( $product_id );
				$meeting_label = bkap_zoom_join_meeting_label( $product_id );

				if ( $zoom_enabled ) {
					?>
					<p class="form-field form-field-wide">
						<label for="bkap_add_zoom_meeting"><?php echo esc_html( $meeting_label ); ?> - <a href="javascript:void(0)" id="bkap_add_zoom_meeting" ><?php echo esc_html__( 'Add zoom meeting', 'woocommerce-booking' ); ?></a></label>
						<input type="text" name="bkap_manual_zoom_meeting" id="bkap_manual_zoom_meeting" placeholder="<?php echo esc_html__( 'Add meeting link here', 'woocommerce-booking' ); ?>">
						<i id="bkap_manual_zoom_meeting_info"><?php echo esc_html__( 'Keeping above field blank will generate new meeting link.', 'woocommerce-booking' ); ?></i>
					</p>
					<?php
				}
			}
		}

		/**
		 * This function will create a zoom meeting link when order status has been updated to confirmed or processing.
		 *
		 * @param int    $order_id Order ID.
		 * @param object $instance WC_Order.
		 * @since 5.8.0
		 */
		public function bkap_create_zoom_meeting_on_order_confirmed_or_processing( $order_id, $instance ) {
			global $bkap_temp_order_object;

			// $bkap_temp_order_object has been introduced to temporarily save the $order information to be used later on in a filter function where the $order object will need to be updated to incorporate the Zoom Meeting Link information.

			$order       = wc_get_order( $order_id );
			$item_values = $order->get_items();
			$booking_ids = array();

			foreach ( $item_values as $item_id => $item_value ) {
				$booking_id = bkap_common::get_booking_id( $item_id );

				if ( is_array( $booking_id ) ) {
					$booking_ids = $booking_id;
				} elseif ( $booking_id ) {
					$booking_ids[] = $booking_id;
				}

				foreach ( $booking_ids as $key => $id ) {
					// Get Meeting Label and use that to search if Zoom Meeting already exists for $item_id.
					$booking_data = bkap_get_meta_data( $id );

					foreach ( $booking_data as $data ) {
						$product_id        = $data['product_id'];
						$meeting_label     = bkap_zoom_join_meeting_label( $product_id );
						$zoom_meeting_link = wc_get_order_item_meta( $item_id, $meeting_label, false );

						if ( ! empty( $zoom_meeting_link ) && ( is_array( $booking_id ) || is_countable( $booking_id ) ) && count( $booking_id ) === count( $zoom_meeting_link ) ) {
							return;
						}

						self::bkap_create_zoom_meeting( $id, $data );
						$bkap_temp_order_object = wc_get_order( $order_id ); // Reload the $order object to get Zoom Link elements and save to $bkap_temp_order_object.

						do_action( 'bkap_after_zoom_meeting_created', $id, $data );
					}
				}
			}
		}

		/**
		 * This function will update the Order object to reflect the Zoom Meeting link information.
		 *
		 * @param array $order_array Array of Order data.
		 * @since 5.8.0
		 */
		public function bkap_add_zoom_link_to_order_item( $order_array ) {
			global $bkap_temp_order_object;

			if ( isset( $bkap_temp_order_object ) && is_object( $bkap_temp_order_object ) ) {

				// $bkap_temp_order_object is a valid object, so we proceed.
				// Ensure that the ID of both orders (1. order from the global variable. 2. order from the filter function) are the same to be sure that we do not tamper with unrelated data.

				// $order_array['order']->get_id() - Stale data without Zoom Meeting Link.
				// $bkap_temp_order_object->get_id() - Update data with Zoom Meeting Link.

				if ( $order_array['order']->get_id() === $bkap_temp_order_object->get_id() ) {

					// Orders are similar.
					$order_array['order'] = $bkap_temp_order_object;
					$order_array['items'] = $bkap_temp_order_object->get_items();
					unset( $bkap_temp_order_object );
				}
			}
			return $order_array;
		}

		/**
		 * Manually adding the meeting link to the booking.
		 *
		 * @param array $order_array Array of Order data.
		 * @since 5.15.0
		 */
		public static function bkap_add_zoom_meeting( $post_data = array() ) {
			// phpcs:disable WordPress.Security.NonceVerification
			$booking_id   = isset( $_POST['booking_id'] ) ? sanitize_text_field( wp_unslash( $_POST['booking_id'] ) ) : '';
			$booking_id   = isset( $post_data['booking_id'] ) ? $post_data['booking_id'] : $booking_id;
			$meeting_link = isset( $_POST['meeting_link'] ) ? sanitize_text_field( wp_unslash( $_POST['meeting_link'] ) ) : '';
			$meeting_link = isset( $post_data['meeting_link'] ) ? $post_data['meeting_link'] : $meeting_link;

			if ( $meeting_link == '' ) {
				$booking_data = bkap_get_meta_data( $booking_id );

				foreach ( $booking_data as $data ) {
					$meeting_info = self::bkap_create_zoom_meeting( $booking_id, $data, 'add', false );
					$meeting_link = isset( $meeting_info['meeting_link'] ) ? $meeting_info['meeting_link'] : '';
				}
			} else {

				$meeting_link_data  = wp_parse_url( $meeting_link );
				$meeting_explode    = explode( '/', $meeting_link_data['path'] );
				$meeting_id         = $meeting_explode[2];
				$meeting_id_explode = explode( '?', $meeting_id );
				$meeting_id         = $meeting_id_explode[0];

				if ( $meeting_id != '' ) {
					$meeting      = bkap_json_decode( bkap_zoom_connection()->bkap_get_meeting_info( $meeting_id ) );
					$booking_data = bkap_get_meta_data( $booking_id );

					extract( $booking_data[0] );
					if ( isset( $meeting->join_url ) ) {

						$meeting_label      = bkap_zoom_join_meeting_label( $product_id );
						$meeting_text       = bkap_zoom_join_meeting_text( $product_id );
						$meeting_link       = $meeting->join_url;
						$order_meeting_link = $meeting_link;
						$meeting_data       = $meeting;

						update_post_meta( $booking_id, '_bkap_zoom_meeting_link', $meeting_link );
						update_post_meta( $booking_id, '_bkap_zoom_meeting_data', $meeting_data );
						$meeting_link = sprintf( '<a href="%s">%s</a>', $meeting->join_url, $meeting_text );
						wc_add_order_item_meta( $order_item_id, $meeting_label, $meeting_link, true );

						$meeting_info['meeting_link'] = $meeting_link;
						$meeting_info['meeting_data'] = $meeting_data;

						// Save Zoom Meeting Information to Order Note.
						$order_meeting_link = sprintf( '<a href="%s">%s</a>', $order_meeting_link, $order_meeting_link );
						$order_meeting_link = $meeting_label . ': %s' . $order_meeting_link;
						bkap_common::save_booking_information_to_order_note( '', $parent_id, $order_meeting_link );
					} else {
						$meeting_link = $meeting->message;
						$order_obj    = wc_get_order( $parent_id );
						/* translators: %s: Booking ID and Meeting link. */
						$meeting_msg = sprintf( __( 'Zoom Meeting Error for Booking #%1$s - %2$s', 'woocommerce-booking' ), $booking_id, $meeting->message );
						$order_obj->add_order_note( $meeting_msg );
					}
				} else {
					$meeting_link = __( 'Invalid meeting link.', 'woocommerce-booking' );
				}
			}

			if ( count( $post_data ) > 0 ) {
				return array( 'meeting_link' => $meeting_link );
			} else {
				wp_send_json( array( 'meeting_link' => $meeting_link ) );
			}
		}
	}
	Bkap_Zoom_Meetings::instance();
}
