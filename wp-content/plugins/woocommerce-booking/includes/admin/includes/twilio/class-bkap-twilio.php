<?php
/**
 * Twilio SMS.
 *
 * @author   Tyche Softwares
 * @package  BKAP/Twilio
 * @since 4.17.0
 */

defined( 'ABSPATH' ) || exit;

use Twilio\Rest\Client;

/**
 * Twilio SMS.
 *
 * @since 4.17.0
 */
class BKAP_Twilio {

	/**
	 * This function will send the SMS
	 *
	 * @param obj     $booking - Booking Object.
	 * @param array   $twilio_details - Array of Twilio Settings Details.
	 * @param integer $item_id - Item ID.
	 *
	 * @since 5.17.0
	 */
	public static function bkap_send_automatic_sms_reminder( $booking, $twilio_details, $item_id ) {

		$item_obj            = bkap_common::get_bkap_booking( $item_id );
		$from                = $twilio_details['from'];
		$sid                 = $twilio_details['sid'];
		$token               = $twilio_details['token'];
		$msg_body            = $twilio_details['body'];
		$product_title       = $item_obj->product_title;
		$order_date          = $item_obj->order_date;
		$order_number        = $item_obj->order_id;
		$start_date          = $item_obj->item_booking_date;
		$end_date            = $item_obj->item_checkout_date;
		$booking_time        = $item_obj->item_booking_time;
		$booking_id          = $item_obj->booking_id;
		$booking_resource    = $item_obj->resource_title;
		$booking_persons     = $item_obj->person_data;
		$zoom_link           = $item_obj->zoom_meeting;
		$customer_name       = '';
		$customer_first_name = '';
		$customer_last_name  = '';
		$order_obj           = wc_get_order( $item_obj->order_id );
		$user_id             = isset( $item_obj->customer_id ) ? $item_obj->customer_id : 0;
		if ( $user_id > 0 ) {
			$customer            = get_user_by( 'id', $item_obj->customer_id );
			$customer_name       = $customer->display_name;
			$customer_first_name = $customer->first_name;
			$customer_last_name  = $customer->last_name;
			$to_phone            = self::bkap_get_phone( $item_obj->customer_id );
		} else {
			if ( $order_obj ) {
				$to_phone            = $order_obj->get_billing_phone();
				$billing_country     = $order_obj->get_billing_country();
				$customer_name       = $order_obj->get_formatted_billing_full_name();
				$customer_first_name = $order_obj->get_billing_first_name();
				$customer_last_name  = $order_obj->get_billing_last_name();
			} else {
				$user_id         = get_current_user_id();
				$to_phone        = get_user_meta( $user_id, 'billing_phone', true );
				$billing_country = get_user_meta( $user_id, 'billing_country', true );
			}

			$country_map = bkap_country_code_map();
			$dial_code   = isset( $country_map[ $billing_country ] ) ? $country_map[ $billing_country ]['dial_code'] : '';
			if ( is_numeric( $to_phone ) ) {
				// if first character is not a +, add it.
				if ( substr( $to_phone, 0, 1 ) !== '+' ) {
					if ( '' !== $dial_code ) {
						$to_phone = $dial_code . $to_phone;
					} else {
						$to_phone = '+' . $to_phone;
					}
				}
			}
		}

		$body = str_replace(
			array(
				'{product_title}',
				'{order_date}',
				'{order_number}',
				'{customer_name}',
				'{customer_first_name}',
				'{customer_last_name}',
				'{start_date}',
				'{end_date}',
				'{booking_time}',
				'{booking_id}',
				'{booking_resource}',
				'{booking_persons}',
				'{zoom_link}',
			),
			array(
				$product_title,
				$order_date,
				$order_number,
				$customer_name,
				$customer_first_name,
				$customer_last_name,
				$start_date,
				$end_date,
				$booking_time,
				$booking_id,
				$booking_resource,
				$booking_persons,
				$zoom_link,
			),
			$msg_body
		);

		// send the message.
		if ( $to_phone ) {

			try {
				$client  = new Client( $sid, $token );
				$message = $client->messages->create(
					$to_phone,
					array(
						'from' => $from,
						'body' => $body,
					)
				);

				if ( $message->sid ) {
					$message_sid     = $message->sid;
					$message_details = $client->messages( $message_sid )->fetch();
					$status          = $message_details->status;
					/* translators: %s: Booking ID */
					$sms_msg = sprintf( __( 'The reminder SMS for Booking #%1$s has been sent to %2$s.', 'woocommerce-booking' ), $booking_id, $to_phone );

					$order_obj->add_order_note( $sms_msg );
				}
			} catch ( Exception $e ) {
				$msg = $e->getMessage();
			}
		}
	}

	/**
	 * Returns the Phone number of the user
	 *
	 * @param integer $user_id - User ID.
	 * @return string`|boolean - Phone Number.
	 *
	 * @since 4.17.0
	 */
	public static function bkap_get_phone( $user_id ) {

		global $wpdb;

		$country_map     = bkap_country_code_map();
		$to_phone        = '';
		$user            = get_user_by( 'id', $user_id );
		$billing_country = $user->billing_country;
		$dial_code       = isset( $country_map[ $billing_country ] ) ? $country_map[ $billing_country ]['dial_code'] : '';
		$to_phone        = $user->billing_phone;

		// Verify the Phone number.
		if ( is_numeric( $to_phone ) ) {
			// if first character is not a +, add it.
			if ( substr( $to_phone, 0, 1 ) !== '+' ) {
				if ( '' !== $dial_code ) {
					$to_phone = $dial_code . $to_phone;
				} else {
					$to_phone = '+' . $to_phone;
				}
			}
			return $to_phone;
		} else {
			return false;
		}
	}

	/**
	 * Sends a Test SMS
	 *
	 * @since 4.17.0
	 */
	public static function bkap_send_test_sms( $phone_number = '', $msg = '' ) {

		// phpcs:disable WordPress.Security.NonceVerification
		if ( '' == $msg && '' == $phone_number ) {
			$phone_number = isset( $_POST['number'] ) ? sanitize_text_field( wp_unslash( $_POST['number'] ) ) : '';
			$msg          = isset( $_POST['msg'] ) ? sanitize_text_field( wp_unslash( $_POST['msg'] ) ) : '';
		}
		// phpcs:enable WordPress.Security.NonceVerification

		$is_ajax   = wp_doing_ajax();
		$msg_array = array();

		$phone_number = sanitize_text_field( wp_unslash( $phone_number ) );
		$msg          = sanitize_textarea_field( wp_unslash( $msg ) );

		if ( '' === $phone_number || '' === $msg ) {
			$notice_message = __( 'Please make sure the Recipient Number and Message field are populated with valid details.', 'woocommerce-booking' );
			if ( $is_ajax ) {
				$msg_array[] = $notice_message;
				echo wp_json_encode( $msg_array );
				die();
			} else {
				return array(
					'type' => 'error',
					'text' => $notice_message,
				);
			}
		}

			// Verify the Phone number.
		if ( ! is_numeric( $phone_number ) ) {
			$notice_message = __( 'Please enter the phone number in E.164 format', 'woocommerce-booking' );

			if ( $is_ajax ) {
				$msg_array[] = $notice_message;
				echo wp_json_encode( $msg_array );
				die();
			} else {
				return array(
					'type' => 'error',
					'text' => $notice_message,
				);
			}
		}

				// if first character is not a +, add it.
		if ( substr( $phone_number, 0, 1 ) !== '+' ) {
			$phone_number = '+' . $phone_number;
		}

		$sms_settings = get_option( 'bkap_sms_settings' );
		$sms_settings = apply_filters( 'bkap_sms_settings', $sms_settings );

		$sid          = isset( $sms_settings['account_sid'] ) ? $sms_settings['account_sid'] : '';
		$token        = isset( $sms_settings['auth_token'] ) ? $sms_settings['auth_token'] : '';
		$from         = isset( $sms_settings['from'] ) ? $sms_settings['from'] : '';

		if ( '' === $sid || '' === $token ) {
			$notice_message = __( 'Incomplete Twilio Account Details. Please provide an Account SID and Auth Token to send a test message.', 'woocommerce-booking' );
			if ( $is_ajax ) {
				$msg_array[] = $notice_message;
				echo wp_json_encode( $msg_array );
				die();
			} else {
				return array(
					'type' => 'error',
					'text' => $notice_message,
				);
			}
		}

		try {
			$client = new Client( $sid, $token );

			$message = $client->messages->create(
				$phone_number,
				array(
					'from' => $from,
					'body' => $msg,
				)
			);

			if ( $message->sid ) {
				$message_sid = $message->sid;

				$message_details = $client->messages( $message_sid )->fetch();
				$status          = $message_details->status;
				$errormsg        = $message_details->errorMessage; // phpcs:ignore

				$notice_message = sprintf(
					/* translators: %s: Status of the message */
					__( 'SMS  sent. Message Status: %s', 'woocommerce-booking' ),
					$status
				);
				if ( $is_ajax ) {
					$msg_array[] = $notice_message;
					echo wp_json_encode( $msg_array );
					die();
				} else {
					return array(
						'type' => 'success',
						'text' => $notice_message,
					);
				}
			}
		} catch ( Exception $e ) {
			$error_message = $e->getMessage();
			if ( $is_ajax ) {
				$msg_array[] = $error_message;
				echo wp_json_encode( $msg_array );
				die();
			} else {
				return array(
					'type' => 'error',
					'text' => $error_message,
				);
			}
		}
	}
}
