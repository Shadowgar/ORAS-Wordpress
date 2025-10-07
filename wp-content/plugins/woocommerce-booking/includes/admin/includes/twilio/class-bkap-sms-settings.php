<?php
/**
 * It will display the email template listing.
 *
 * @author   Tyche Softwares
 * @package  BKAP/SMS-Reminder
 * @since 4.17.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get the PHP helper library from twilio.com/docs/php/install.
require_once BKAP_PLUGIN_PATH . '/includes/libraries/twilio-php/vendor/Twilio/autoload.php'; // Loads the library.
use Twilio\Rest\Client;

if ( ! class_exists( 'BKAP_SMS_Settings' ) ) {
	/**
	 * It will display the SMS settings for the plugin.
	 *
	 * @since 4.17.0
	 */
	class BKAP_SMS_Settings {

		/**
		 * Constructor
		 *
		 * @since 4.17.0
		 */
		public function __construct() {
			add_action( 'bkap_sms_reminder_settings', array( $this, 'bkap_send_sms_reminders' ) );
			add_action( 'admin_init', array( $this, 'bkap_save_sms_settings' ) );
			add_action( 'init', array( $this, 'bkap_save_sms_settings' ) );
		}

		/**
		 * This function will save the SMS settings to option
		 *
		 * @since 5.17.0
		 */
		public static function bkap_save_sms_settings() {

			if ( ! empty( $_POST ) && isset( $_POST['bkap_sms_reminder'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification

				$is_vendor = false;
				if ( ! is_admin() ) {
					$vendor_id = get_current_user_id();
					$is_vendor = BKAP_Vendors::bkap_is_vendor( $vendor_id );
				}

				if ( $is_vendor ) {
					$sms_option_name = 'bkap_vendor_sms_settings_' . $vendor_id;
				} else {
					$sms_option_name = 'bkap_sms_settings';
				}

				$bkap_sms_settings = array();
				if ( isset( $_POST['bkap_sms_settings'] ) && is_array( $_POST['bkap_sms_settings'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$bkap_sms_settings = array_map( 'sanitize_text_field', wp_unslash( $_POST['bkap_sms_settings'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
					update_option( $sms_option_name, $bkap_sms_settings );
				}
			}
		}

		/**
		 * Adds settings for SMS Notifications
		 *
		 * @since 7.9
		 */
		public static function bkap_send_sms_reminders() {

			$sms_settings = get_option( 'bkap_sms_settings' );
			$sms_settings = apply_filters( 'bkap_sms_settings', $sms_settings );

			wc_get_template(
				'reminders/bkap-reminder-sms-view.php',
				array( 'options' => $sms_settings ),
				'woocommerce-booking/',
				BKAP_BOOKINGS_TEMPLATE_PATH
			);
		}
	}
}
