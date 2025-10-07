<?php
/**
 * Bookings and Appointment Plugin for WooCommerce
 *
 * Menu page for sending manual reminder emails and setting automatic reminders for bookings
 *
 * @author      Tyche Softwares
 * @package     BKAP/Send_Reminder
 * @since       2.0
 * @category    Classes
 */

if ( ! class_exists( 'Bkap_Send_Reminder' ) ) {

	/**
	 * Class Bkap_Send_Reminder
	 */
	class Bkap_Send_Reminder {

		/**
		 * Post Type.
		 *
		 * @var String
		 */
		public $post_type = 'bkap_reminder';

		/**
		 * Constructor
		 */
		public function __construct() {

			add_action( 'bkap_auto_reminder_emails', array( $this, 'bkap_send_auto_reminder_emails' ) );
			add_action( 'bkap_manual_reminder_email_settings', array( $this, 'bkap_manual_reminder_email_settings' ), 10 );
			add_filter( 'wp_untrash_post_status', array( $this, 'bkap_reminder_set_status_upon_untrash' ), 10, 3 );

			// Update Script.
			add_action( 'bkap_bookings_update_db_check', array( $this, 'bkap_reminder_adding_default_reminder' ), 10, 2 );

			add_action( 'wp_ajax_bkap_reminder_preview_email_in_browser', array( $this, 'bkap_reminder_preview_email_in_browser' ), 10 );
			add_action( 'tiny_mce_before_init', array( $this, 'bkap_tiny_mce_before_init_callback' ), 10, 1 );
			add_action( 'admin_init', array( $this, 'bkap_schedule_action_for_auto_send_reminder' ) );
		}

		/**
		 * Schedule action if not set for some reason.
		 */
		public function bkap_schedule_action_for_auto_send_reminder() {
			if ( isset( $_GET['page'] ) && 'bkap_page' === $_GET['page'] ) { // phpcs:ignore
				if ( ! as_next_scheduled_action( 'bkap_auto_reminder_emails' ) ) {
					as_schedule_recurring_action( time(), HOUR_IN_SECONDS, 'bkap_auto_reminder_emails' );
				}
			}
		}

		/**
		 * Add custom font size for wp editor.
		 *
		 * @param array $init_array Array.
		 */
		public static function bkap_tiny_mce_before_init_callback( $init_array ) {
			$fontsizes                      = '8px 10px 12px 14px 16px 18px 20px 22px 24px 28px 32px 36px';
			$init_array['fontsize_formats'] = apply_filters( 'bkap_fontsize_for_wp_editor', $fontsizes );

			return $init_array;
		}

		/**
		 * Trashes/Deletes the Reminder.
		 *
		 * @since 5.14.0
		 */
		public static function bkap_delete_reminder() {

			$reminder_id = $_POST['reminder_id']; // phpcs:ignore
			wp_trash_post( $reminder_id );

			wp_die();
		}

		/**
		 * Open a preview e-mail.
		 *
		 * @return null
		 */
		public function bkap_reminder_preview_email_in_browser() {

			if ( is_admin() ) {
				$bkap_mailer        = WC()->mailer();
				$bkap_email_heading = __( 'Booking Reminder', 'woocommerce-booking' );
				$bkap_email         = new WC_Email();
				$bkap_message       = $wcap_email->style_inline( $bkap_mailer->wrap_message( $bkap_email_heading, 'Booking Content....' ) );
				echo $bkap_message; // phpcs:ignore
			}
			return null;
		}

		/**
		 * Setting actual status of Reminder upon untrashing the reminder.
		 *
		 * @param string $new_status Draft status.
		 * @param int    $post_id Reminder ID.
		 * @param string $previous_status Actual Status of the reminder before moving to the trash.
		 *
		 * @since 5.14.0
		 */
		public static function bkap_reminder_set_status_upon_untrash( $new_status, $post_id, $previous_status ) {

			$post_obj = get_post( $post_id );

			if ( 'bkap_reminder' === $post_obj->post_type ) {
				return $previous_status;
			}

			return $new_status;
		}

		/**
		 * Script to add the default reminder settings.
		 *
		 * @param string $old_version The old version value of plugin.
		 * @param string $new_version The new version value of plugin.
		 * @since 5.14.0
		 */
		public function bkap_reminder_adding_default_reminder( $old_version, $new_version ) {

			if ( $old_version < '5.14.0' ) {

				if ( '' === get_option( 'bkap_reminder_default_data_added', '' ) ) {

					// Adding default Reminder for the Admin/Store owner.
					$number_of_hours = self::bkap_update_reminder_email_day_to_hour(); // reminder settings by admin.
					$sms_settings    = get_option( 'bkap_sms_settings' ); // Getting SMS settings. sms settings by admin.

					$data           = array();
					$data['author'] = bkap_get_user_id();
					if ( $number_of_hours > 0 ) {
						$data['sending_delay'] = (int) $number_of_hours;
						$data['status']        = 'bkap-active';
						$schedule              = true;
					}

					if ( isset( $sms_settings['enable_sms'] ) && 'on' === $sms_settings['enable_sms'] ) {
						$data['enable_sms'] = 'on';
					}

					if ( isset( $sms_settings['body'] ) && 'on' === $sms_settings['body'] ) {
						$data['sms_body'] = $sms_settings['body'];
					}

					$created_reminder = BKAP_Reminder::bkap_create_reminder( $data );

					// Adding default Reminder for the Vendors.
					$vendor_hours = BKAP_Vendors::bkap_vendor_reminder_hours(); // key - Vendor ID & Value - Hours.
					$vendor_sms   = BKAP_Vendors::bkap_vendor_sms_settings(); // key - Vendor ID & Value - Hours.

					foreach ( $vendor_hours as $vendor_id => $number_of_hours ) {

						$data           = array();
						$data['author'] = $vendor_id;
						$user           = get_userdata( $vendor_id );
						$user_name      = $user->display_name;
						$data['title']  = $user_name;

						if ( $number_of_hours > 0 ) {
							$data['sending_delay'] = (int) $number_of_hours;
							$data['status']        = 'bkap-active';
							$schedule              = true;
						}

						$sms_settings = isset( $vendor_sms[ $vendor_id ] ) ? $vendor_sms[ $vendor_id ] : array();

						if ( isset( $sms_settings['enable_sms'] ) && 'on' === $sms_settings['enable_sms'] ) {
							$data['enable_sms'] = 'on';
						}

						if ( isset( $sms_settings['body'] ) ) {
							$data['sms_body'] = $sms_settings['body'];
						}

						$created_reminder = BKAP_Reminder::bkap_create_reminder( $data );
					}

					if ( $schedule ) {
						if ( ! as_next_scheduled_action( 'bkap_auto_reminder_emails' ) ) {
							as_schedule_recurring_action( time(), HOUR_IN_SECONDS, 'bkap_auto_reminder_emails' );
						}
					}

					update_option( 'bkap_reminder_default_data_added', 'done' );
				}
			}

			if ( $old_version < '6.5.0' ) {
				if ( wp_next_scheduled( 'bkap_auto_reminder_emails' ) ) {
					wp_clear_scheduled_hook( 'bkap_auto_reminder_emails' ); // remove the cron and add schedular.
					if ( ! as_next_scheduled_action( 'bkap_auto_reminder_emails' ) ) {
						as_schedule_recurring_action( time(), HOUR_IN_SECONDS, 'bkap_auto_reminder_emails' );
					}
				}
			}
		}

		/**
		 * Manual Reminder Email Settings.
		 *
		 * @since 5.10.0
		 */
		public static function bkap_manual_reminder_email_settings( $settings = false ) {

			$current_time = current_time( 'timestamp' );
			$current_date = gmdate( 'Y-m-d', $current_time );

			// phpcs:disable
			if ( ! empty( $_POST ) ) {

				$product_ids = isset( $_POST['bkap_reminder_product_id'] ) && '' != $_POST['bkap_reminder_product_id'] ? $_POST['bkap_reminder_product_id'] : '';
				$booking_ids = isset( $_POST['bkap_reminder_booking_id'] ) && '' != $_POST['bkap_reminder_booking_id'] ? $_POST['bkap_reminder_booking_id'] : '';
				$order_ids   = isset( $_POST['bkap_reminder_order_id'] ) && '' != $_POST['bkap_reminder_order_id'] ? $_POST['bkap_reminder_order_id'] : '';

				$subject  = isset( $_POST['bkap_reminder_subject'] ) && '' != $_POST['bkap_reminder_subject'] ? $_POST['bkap_reminder_subject'] : 'Booking Reminder';
				$message  = isset( $_POST['bkap_reminder_message'] ) && '' != $_POST['bkap_reminder_message'] ? $_POST['bkap_reminder_message'] : '';
				$mailer   = WC()->mailer();
				$reminder = $mailer->emails['BKAP_Email_Booking_Reminder'];
				$success  = __( 'Reminder sent successfully', 'woocommerce-booking' );

				// phpcs:enable

				if ( is_array( $product_ids ) && ! empty( $product_ids ) ) {

					foreach ( $product_ids as $product_id ) {

						$bookings = bkap_common::bkap_get_bookings_by_product( $product_id );
						foreach ( $bookings as $id => $booking ) {
							$item_id = $booking->get_item_id();
							$reminder->trigger( $item_id, $subject, $message );
							do_action( 'bkap_send_manual_reminder_emails', $booking, $item_id );
							echo '<div class="updated fade"><p>' . $success . '</p></div>'; // phpcs:ignore
						}
					}
				}

				if ( is_array( $booking_ids ) && ! empty( $booking_ids ) ) {

					foreach ( $booking_ids as $booking_id ) {
						$booking    = new BKAP_Booking( $booking_id );
						$start_date = $booking->get_start();

						if ( strtotime( $start_date ) > strtotime( $current_date ) ) {
							$item_id = $booking->get_item_id();
							$reminder->trigger( $item_id, $subject, $message );
							do_action( 'bkap_send_manual_reminder_emails', $booking, $item_id );
							echo '<div class="updated fade"><p>' . $success . '</p></div>'; // phpcs:ignore
						}
					}
				}

				if ( is_array( $order_ids ) && ! empty( $order_ids ) ) {

					foreach ( $order_ids as $order_id ) {
						$order_bookings = bkap_common::get_booking_ids_from_order_id( $order_id );

						foreach ( $order_bookings as $booking_id ) {
							$booking    = new BKAP_Booking( $booking_id );
							$start_date = $booking->get_start();

							if ( strtotime( $start_date ) > strtotime( $current_date ) ) {
								$item_id = $booking->get_item_id();
								$reminder->trigger( $item_id, $subject, $message );
								do_action( 'bkap_send_manual_reminder_emails', $booking, $item_id );
								echo '<div class="updated fade"><p>' . $success . '</p></div>'; // phpcs:ignore
							}
						}
					}
				}
			}

			/* Bookable Products List */
			$product_args      = apply_filters( 'bkap_get_product_args_for_manual_reminder', array() );
			$bookable_products = bkap_common::ts_get_all_bookable_products( $product_args );

			/* Bookings and Orders List */
			$additional_args = apply_filters( 'bkap_get_bookings_args_for_manual_reminder', array() );
			$meta_query      = array();
			if ( ! empty( $additional_args ) ) {
				$meta_query = array(
					'key'     => $additional_args['meta_key'],
					'value'   => $additional_args['meta_value'],
					'compare' => '=',
				);
			}

			$wp_args = array(
				'post_status'    => array( 'confirmed', 'paid' ),
				'post_type'      => 'bkap_booking',
				'posts_per_page' => -1,
				'fields'         => 'id=>parent', // to get booking id and order id[parent id].
				'meta_query'     => array( // phpcs:ignore
					'relation' => 'AND',
					array(
						'key'     => '_bkap_start',
						'value'   => gmdate( 'YmdHis', strtotime( $current_date ) ),
						'compare' => '>=',
					),
					$meta_query,
				),
				'orderby'        => 'date',
				'order'          => 'DESC',
			);

			$booking      = new WP_Query( $wp_args );
			$booking_data = $booking->posts;
			$booking_ids  = array();
			$order_ids    = array();
			foreach ( $booking_data as $key => $value) {
				$booking_ids[] = $value->ID;
				$order_ids[]   = $value->post_parent;
			}

			if ( empty( $booking_ids ) ) {
				$booking_ids[] = __( 'No future bookings', 'woocommerce-booking' );
				$order_ids[]   = __( 'No orders with future bookings', 'woocommerce-booking' );
			} else {
				$order_ids = array_unique( $order_ids );
			}

			/* Email Subject */
			$saved_subject = get_option( 'reminder_subject' );
			if ( isset( $saved_subject ) && '' !== $saved_subject ) {
				$email_subject = $saved_subject;
			} else {
				$email_subject = __( 'Booking Reminder', 'woocommerce-booking' );
			}
			$email_subject = apply_filters( 'bkap_manual_reminder_email_subject', $email_subject );

			/* Email Body */
			$saved_message = get_option( 'reminder_message' );
			if ( isset( $saved_message ) && '' !== $saved_message ) {
				$content = $saved_message;
			} else {
				$content = 'Hi {customer_first_name},

You have a booking of {product_title} on {start_date}.

Your Order # : {order_number}
Order Date : {order_date}
Your booking id is: {booking_id}
';
			}
			$content = apply_filters( 'bkap_manual_reminder_email_content', $content );

			$data = array(
				'bkap_heading'      => __( 'Manual Reminders', 'woocommerce-booking' ),
				'bookable_products' => $bookable_products,
				'booking_ids'       => $booking_ids,
				'order_ids'         => $order_ids,
				'email_subject'     => $email_subject,
				'content'           => $content,
			);

			if ( $settings ) {
				return $data;
			}

			wc_get_template(
				'reminders/bkap-manual-reminder-email-settings.php',
				$data,
				'woocommerce-booking/',
				BKAP_BOOKINGS_TEMPLATE_PATH
			);
		}

		/**
		 * Scheduled event for the automatic reminder emails.
		 *
		 * @since 4.10.0
		 */
		public function bkap_send_auto_reminder_emails() {

			$all_reminders = get_posts(
				array(
					'post_type'      => 'bkap_reminder',
					'post_status'    => array( 'bkap-active' ),
					'posts_per_page' => -1,
					'meta_query'     => array( // phpcs:ignore
						array(
							'key'     => 'bkap_delay_value',
							'value'   => '0',
							'compare' => '>',
						),
					),
				)
			);

			if ( count( $all_reminders ) > 0 ) {

				$ids_of_vendor_has_reminders = array_unique( array_column( $all_reminders, 'post_author' ) );

				$future_bookings     = bkap_get_future_bookings();
				$past_bookings       = bkap_get_past_bookings();
				$current_date        = gmdate( 'Y-m-d H', current_time( 'timestamp' ) ); // phpcs:ignore
				$current_date        = $current_date . ':00';
				$current_date_time   = strtotime( $current_date );
				$mailer              = WC()->mailer();
				$reminder_email      = $mailer->emails['BKAP_Email_Booking_Reminder'];
				$main_twilio_details = bkap_get_sms_settings(); // Getting SMS settings.
				$vendor_sms          = BKAP_Vendors::bkap_vendor_sms_settings(); // key - Vendor ID & Value - Hours.

				foreach ( $all_reminders as $key => $value ) {

					$reminder_id = $value->ID;
					$reminder    = new BKAP_Reminder( $reminder_id );
					$products    = $reminder->get_products();
					if ( empty( $products ) ) {
						continue;
					}

					$subject    = $reminder->get_email_subject();
					$message    = $reminder->get_email_content();
					$heading    = $reminder->get_email_heading();
					$trigger    = $reminder->get_trigger();
					$enable_sms = $reminder->get_enable_sms();
					$sms_body   = $reminder->get_sms_body();

					switch ( $trigger ) {
						case 'before_booking_date':
							$all_booking_posts = $future_bookings;
							break;
						case 'after_booking_date':
							$all_booking_posts = $past_bookings;
							break;
					}

					if ( in_array( 'all', $products, true ) ) {
						$booking_posts = $all_booking_posts;
					} else {
						$booking_posts = array();
						foreach ( $all_booking_posts as $bkey => $bvalue ) {
							if ( in_array( $bvalue->product_id, $products ) ) { // phpcs:ignore
								$booking_posts[] = $bvalue;
							}
						}
					}

					$sending_delay   = $reminder->get_sending_delay();
					$number_of_hours = $sending_delay['delay_value'];
					switch ( $sending_delay['delay_unit'] ) {
						case 'days':
							$number_of_hours *= 24;
							break;
						case 'months':
							$number_of_hours *= 730;
							break;
						case 'years':
							$number_of_hours *= 8760;
							break;
						default:
							break;
					}

					foreach ( $booking_posts as $key => $booking ) {
						$booked_date  = apply_filters( 'bkap_date_compare_for_sending_reminder', gmdate( 'Y-m-d H', strtotime( $booking->get_start() ) ), $booking, $value );
						$booked_date  = $booked_date . ':00';
						$booking_date = strtotime( $booked_date );

						// Check if reminder is set by Vendor.
						$vendor_id = $booking->get_vendor_id();

						if ( in_array( $vendor_id, $ids_of_vendor_has_reminders ) && $value->post_author != $vendor_id ) {
							continue; // This will make sure that the Reminders setup by Vendor will only proceed for sending the reminder for current booking.
						}

						switch ( $trigger ) {
							case 'before_booking_date':
								$interval = ( $booking_date - $current_date_time ); // booking date - current date time.
								break;
							case 'after_booking_date':
								$interval = ( $current_date_time - $booking_date ); // booking date - current date time.
								break;
						}

						if ( $interval === absint( $number_of_hours * 3600 ) ) { // phpcs:ignore

							$item_id        = $booking->get_item_id();
							$mailer         = WC()->mailer();
							$reminder_email = $mailer->emails['BKAP_Email_Booking_Reminder'];

							$reminder_email->trigger( $item_id, $subject, $message, $heading );
							if ( 'on' === $enable_sms ) {
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

							// Sending remiders from other tools.
							do_action( 'bkap_send_auto_reminder_emails', $booking, $item_id );
						}
					}
				}
			}
		}

		/**
		 * Ajax call for the Send Reminder action on Edit Booking page
		 *
		 * @since 4.10.0
		 */
		public static function bkap_send_reminder_action() {

			$booking_id = $_POST['booking_id']; // phpcs:ignore
			$booking    = new BKAP_Booking( $booking_id );
			$item_id    = $booking->get_item_id();
			$mailer     = WC()->mailer();
			$reminder   = $mailer->emails['BKAP_Email_Booking_Reminder'];

			$reminder->trigger( $item_id );

			$twilio_details = bkap_get_sms_settings(); // Getting SMS settings.
			if ( is_array( $twilio_details ) ) {
				BKAP_Twilio::bkap_send_automatic_sms_reminder( $booking, $twilio_details, $item_id );
			}

			// Sending remiders from other tools.
			do_action( 'bkap_send_auto_reminder_emails', $booking, $item_id );
			wp_die();
		}

		/**
		 * Send Test Reminder Email.
		 *
		 * @since 5.14.0
		 */
		public static function bkap_reminder_test() {

			// phpcs:disable
			$email_subject = $_POST['email_subject']; 
			$email_content = $_POST['email_content'];
			$email_heading = $_POST['email_heading'];
			$email_address = $_POST['email_address'];
			$booking_id    = $_POST['booking_id'];
			// phpcs:enable

			if ( $booking_id > 0 ) {
				$booking = new BKAP_Booking( $booking_id );
				$item_id = $booking->get_item_id();
			} else {
				$booking = array();
				$item_id = 0;
			}

			$mailer   = WC()->mailer();
			$reminder = $mailer->emails['BKAP_Email_Booking_Reminder'];

			if ( '' === $email_subject ) {
				$email_subject = $reminder->subject;
			}

			if ( '' === $email_heading ) {
				$email_heading = $reminder->heading;
			}

			$reminder->recipient = ( '' === $reminder->recipient ) ? $email_address : ',' . $email_address;

			$reminder->trigger( $item_id, $email_subject, $email_content, $email_heading, $email_address, true );

			$twilio_details = bkap_get_sms_settings(); // Getting SMS settings.
			if ( is_array( $twilio_details ) ) {
				BKAP_Twilio::bkap_send_automatic_sms_reminder( $booking, $twilio_details, $item_id );
			}

			wp_die();
		}

		/**
		 * Preview Reminder Email.
		 *
		 * @since 5.14.0
		 */
		public static function bkap_preview_reminder() {

			// phpcs:disable
			$email_subject = $_POST['email_subject'];
			$email_content = $_POST['email_content'];
			$email_heading = $_POST['email_heading'];
			$booking_id    = $_POST['booking_id'];
			// phpcs:enable

			if ( $booking_id > 0 ) {
				$booking = new BKAP_Booking( $booking_id );
				$item_id = $booking->get_item_id();
			} else {
				$booking = array();
				$item_id = 0;
			}

			$mailer              = WC()->mailer();
			$reminder            = $mailer->emails['BKAP_Email_Booking_Reminder'];
			$reminder->recipient = '';

			if ( '' === $email_subject ) {
				$email_subject = $reminder->subject;
			}

			if ( '' === $email_heading ) {
				$email_heading = $reminder->heading;
			}

			$reminder->trigger( $item_id, $email_subject, $email_content, $email_heading, '', true );

			$content = $reminder->get_content_html();
			$content = apply_filters( 'woocommerce_mail_content', $reminder->style_inline( $content ) );

			echo $content; // phpcs:ignore

			wp_die();
		}

		/**
		 * Ajax call for saving the email draft on Manual Reminder page
		 *
		 * @since 4.10.0
		 */
		public static function bkap_save_reminder_message() {

			$message = $_POST['message']; // phpcs:ignore
			$subject = $_POST['subject']; // phpcs:ignore

			$reminder_message = 'reminder_message';
			$reminder_subject = 'reminder_subject';

			if ( isset( $_POST['bkap_vendor_id'] ) && '' !== $_POST['bkap_vendor_id'] ) { // phpcs:ignore WordPress.Security.NonceVerification

				$vendor_id = (int) $_POST['bkap_vendor_id']; // phpcs:ignore WordPress.Security.NonceVerification
				$is_vendor = BKAP_Vendors::bkap_is_vendor( $vendor_id );

				if ( $is_vendor ) {
					$reminder_message = 'bkap_vendor_reminder_message_' . $vendor_id;
					$reminder_subject = 'bkap_vendor_reminder_subject_' . $vendor_id;
				}
			}

			if ( isset( $message ) && '' !== $message ) {
				update_option( $reminder_message, $message );
			}

			if ( isset( $subject ) && '' !== $subject ) {
				update_option( $reminder_subject, $subject );
			}
		}

		/**
		 * Function to update 'day' values to 'hour' values.
		 *
		 * @return int hours for reminder email setting.
		 * @since 5.8.1
		 */
		public static function bkap_update_reminder_email_day_to_hour() {

			$saved_settings  = json_decode( get_option( 'bkap_reminder_settings' ) );
			$number_of_hours = ( isset( $saved_settings->reminder_email_before_hours ) &&
			'' !== $saved_settings->reminder_email_before_hours ) ? $saved_settings->reminder_email_before_hours : 0;

			// Check for previous records for days and convert to hours.
			if ( isset( $saved_settings->reminder_email_before_days ) ) {

				// Sometimes, reminder_email_before_days may still exist even when reminder_email_before_hours has been set. In that case, ignore reminder_email_before_days and use reminder_email_before_hours instead.

				if ( ! isset( $saved_settings->reminder_email_before_hours ) && ( ( (int) $saved_settings->reminder_email_before_days ) > 0 ) ) {
					$number_of_hours                             = ( (int) $saved_settings->reminder_email_before_days ) * 24;
					$saved_settings->reminder_email_before_hours = $number_of_hours;

					// Update scheduled event from day to hourly.
					if ( wp_next_scheduled( 'bkap_auto_reminder_emails' ) ) {
						wp_clear_scheduled_hook( 'bkap_auto_reminder_emails' );
						wp_schedule_event( time(), 'hourly', 'bkap_auto_reminder_emails' );
					}
				}

				// Delete bkap_booking_reschedule_days and update record.
				unset( $saved_settings->reminder_email_before_days );
				update_option( 'bkap_reminder_settings', wp_json_encode( $saved_settings ) );
			}

			return $number_of_hours;
		}
	}
	new Bkap_Send_Reminder();
}
