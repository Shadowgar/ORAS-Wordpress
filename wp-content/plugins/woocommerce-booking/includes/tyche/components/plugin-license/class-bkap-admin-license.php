<?php
/**
 *  Bookings and Appointment Plugin for WooCommerce.
 *
 * Class for Plugin Licensing which restricts access to some BKAP Modules based on type of license.
 *
 * @author      Tyche Softwares
 * @package     BKAP/License
 * @category    Classes
 * @since       5.12.0
 * @since       Updated 5.19.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * BKAP License class.
 *
 * @since 5.12.0
 */
class BKAP_Admin_License {

	/**
	 * License Key.
	 *
	 * @var string
	 */
	public $license_key = '';

	/**
	 * License Type.
	 *
	 * @var string
	 */
	public $license_type = '';

	/**
	 * License Status.
	 *
	 * @var string
	 */
	public $license_status = '';

	/**
	 * General License Error Message.
	 *
	 * @var string
	 */
	public $license_error_message;

	/**
	 * Plugin License Error Message.
	 *
	 * @var string
	 */
	public $plugin_license_error_message;

	/**
	 * Condition to check if the class has been extended by another plugin.
	 *
	 * @var bool
	 */
	public $is_class_extended = false;

	/**
	 * Plugin Name.
	 *
	 * @var string
	 */
	public $plugin_name = '';

	/**
	 * License Key Identifier.
	 *
	 * @var string
	 */
	public $license_key_identifier = '';

	/**
	 * License Type Identifier.
	 *
	 * @var string
	 */
	public $license_type_identifier = '';

	/**
	 * License Status Identifier.
	 *
	 * @var string
	 */
	public $license_status_identifier = '';

	/**
	 * License Expires Identifier.
	 *
	 * @var string
	 */
	public $license_expires_identifier = '';

	/**
	 * Default Constructor
	 *
	 * @param array $data Array of license settings for add-ons.
	 *
	 * @since 5.12.0
	 */
	public function __construct( $data = array() ) {

		// Required.
		$this->plugin_name               = isset( $data ) && is_array( $data ) && isset( $data['plugin_name'] ) ? $data['plugin_name'] : BKAP_PLUGIN_NAME;
		$this->license_key_identifier    = isset( $data ) && is_array( $data ) && isset( $data['license_key_identifier'] ) ? $data['license_key_identifier'] : 'edd_sample_license_key';
		$this->license_status_identifier = isset( $data ) && is_array( $data ) && isset( $data['license_status_identifier'] ) ? $data['license_status_identifier'] : 'edd_sample_license_status';

		// Opional - not needed for add-ons and the rest.
		$this->license_type_identifier    = isset( $data ) && is_array( $data ) && isset( $data['license_type_identifier'] ) ? $data['license_type_identifier'] : 'edd_sample_license_type';
		$this->license_expires_identifier = isset( $data ) && is_array( $data ) && isset( $data['license_expires_identifier'] ) ? $data['license_expires_identifier'] : 'edd_sample_license_expires';
		$this->is_class_extended          = isset( $data ) && is_array( $data ) && count( $data ) > 0;

		$this->license_key    = get_option( $this->license_key_identifier, '' );
		$this->license_type   = get_option( $this->license_type_identifier, '' );
		$this->license_status = get_option( $this->license_status_identifier, '' );
	}

	/**
	 * Places a call to fetch license details.
	 *
	 * @param string $action Action to send to remote server while fetching license.
	 * @param bool   $return_whole_response Whether to return the whole response or just the body. Default action is to return only the body.
	 *
	 * @since 5.12.0
	 */
	private function fetch_license( $action = 'check_license', $return_whole_response = false ) {

		$api_params = array(
			'edd_action' => $action,
			'license'    => $this->license_key,
			'item_name'  => rawurlencode( $this->plugin_name ),
		);

		// Call the Tyche API.
		$response = wp_remote_get(
			esc_url_raw( add_query_arg( $api_params, BKAP_URL ) ),
			array(
				'timeout'   => 15,
				'sslverify' => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		return $return_whole_response ? $response : bkap_json_decode( wp_remote_retrieve_body( $response ) );
	}

	/**
	 * This function activates the license key.
	 * It sends an API call to the Tyche Server to check the validity of the license and thereafter activates the license if valid.
	 * 5.15.0 Update: Return useful information on the cause of failed activation - https://github.com/TycheSoftwares/woocommerce-booking/issues/5184
	 *
	 * @since 5.12.0
	 * @since Updated 5.15.0
	 */
	public function activate_license() {

		$request_data           = $this->fetch_license( 'activate_license', true );
		$response_code          = (int) $request_data['response']['code'];
		$response_body          = $request_data['body'];
		$response_message       = $request_data['response']['message'];
		$is_response_successful = ( 2 === (int) substr( $response_code, 0, 1 ) );

		if ( $is_response_successful ) {

			$license_data = bkap_json_decode( $response_body );

			if ( $license_data && isset( $license_data->license ) && '' !== $license_data->license && 'invalid' !== $license_data->license ) {

				$this->license_status = $license_data->license;

				update_option( $this->license_key_identifier, $this->license_key );
				update_option( $this->license_status_identifier, $this->license_status );

				if ( ! $this->is_class_extended ) {
					update_option( $this->license_expires_identifier, $license_data->expires );

					$this->license_type = $this->get_license_type( strval( $license_data->price_id ), $license_data );
					update_option( $this->license_type_identifier, $this->license_type );
				}

				return true;
			}

			if ( 'invalid' === $license_data->license ) {
				if ( isset( $license_data->error ) && 'expired' === $license_data->error ) {
					return $this->return_license_error_code_description( 'expired' );
				} else {
					return $this->return_license_error_code_description( '10a' );
				}
			}
		}

		// If we get here, then an error has occurred.
		$error_code = '';
		$error_code = ( 403 === $response_code ) ? '10b' : $error_code;
		$error_code = ( false !== strpos( $response_body, 'BlogVault Firewall' ) ) ? '10c' : $error_code; // Check if error has been caused by Tyche Firewall.

		return $this->return_license_error_code_description( $error_code );
	}

	/**
	 * This function will deactivate the license.
	 *
	 * @since 5.12.0
	 */
	public function deactivate_license() {

		$license_data = $this->fetch_license( 'deactivate_license' );
		if ( isset( $license_data->license ) ) {
			$delete = false;
			switch ( $license_data->license ) {
				case 'deactivated':
					$delete = true;
					break;
				case 'failed':
					if ( isset( $license_data->expires ) && is_int( $license_data->expires ) ) {
						$current_time = current_time( 'timestamp' );
						if ( $license_data->expires < $current_time ) {
							$delete = true;
						}
					}
					break;
			}
			if ( $delete ) {
				delete_option( $this->license_status_identifier );
				if ( ! $this->is_class_extended ) {
					delete_option( $this->license_expires_identifier );
					delete_option( $this->license_type_identifier );
				}
			}
		}
	}

	/**
	 * This checks if a license key is valid.
	 *
	 * @since 5.12.0
	 */
	private function check_license() {

		$license_data = $this->fetch_license();

		$data = 'invalid';
		if ( isset( $license_data->license ) && 'valid' === $license_data->license ) {
			$data = 'valid';
		}

		$this->license_status = $license_data->license;

		update_option( $this->license_status_identifier, $this->license_status );

		if ( ! $this->is_class_extended ) {
			$this->license_type = $this->get_license_type( strval( $license_data->price_id ), $license_data );
			update_option( $this->license_type_identifier, $this->license_type );

			update_option( $this->license_expires_identifier, $license_data->expires );
		}
		return $data;
	}

	/**
	 * This checks that the license type option is not empty. If it is, then we go a quick license key fetch.
	 *
	 * @since 5.12.0
	 */
	public function check_license_type() {

		if ( '' !== $this->license_type ) {
			return;
		}

		$license_data = $this->fetch_license();

		if ( $license_data && isset( $license_data->license ) && '' !== $license_data->license && 'invalid' !== $license_data->license ) {
			$license_type   = $this->get_license_type( strval( $license_data->price_id ), $license_data );
			$license_status = get_option( $this->license_status_identifier, '' );
			if ( '' !== $license_status ) {
				$this->license_type = $license_type;
				update_option( $this->license_type_identifier, $this->license_type );
			}
		}
	}

	/**
	 * This function gets the license type from the Price ID.
	 *
	 * @param string $price_id Price ID of the license.
	 * @param object $license_data License Data.
	 *
	 * @since Updated 5.12.0
	 */
	private function get_license_type( $price_id, $license_data = null ) {

		$license_type = '';

		switch ( $price_id ) {

			case '1':
				$license_type = 'business';
				break;

			case '2':
				$license_type = 'enterprise';
				break;

			case '0':
			case '3':
			default:
				$license_type = 'starter';
				break;
		}

		// Consider starter licenses earlier purchased.
		$is_earlier_purchased = false;

		if ( is_null( $license_data ) ) {
			$license_data = $this->fetch_license();
		}

		if ( isset( $license_data->payment_date ) && '' !== $license_data->payment_date ) {
			$is_earlier_purchased = 'lifetime' === $license_data->expires || ( strtotime( $license_data->payment_date ) <= strtotime( '2022-03-31' ) );
		}

		if ( $is_earlier_purchased ) {
			$license_type = 'enterprise';
		}

		return $license_type;
	}

	/**
	 * Plan Error Message.
	 *
	 * @param string $expected_plan Expected Plan that is valid for the restriced resouce.
	 *
	 * @since 5.12.0
	 */
	public function license_error_message( $expected_plan ) {
		$this->check_license_type();

		$message = sprintf(
			/* translators: %1$s: Plugin name; %2$s: URL for License Page. */
			__( 'We have noticed that the license for <b>%1$s</b> plugin is not active. To receive automatic updates & support, please activate the license <a href= "%2$s"> here </a>.', 'woocommerce-booking' ),
			$this->plugin_name,
			'admin.php?page=bkap_page#/license'
		);

		/* translators: %1$s: Current Plan, %2$s: Expected Plan */
		$this->license_error_message = __( 'You are on the %1$s License. This feature is available only on the %2$s License.', 'woocommerce-booking' );

		if ( '' !== $this->license_status ) {
			$message = sprintf(
				/* translators: %1$s: Current Plan, %2$s: Expected Plan */
				__( $this->license_error_message, 'woocommerce-booking' ), //phpcs:ignore
				ucwords( $this->license_type ),
				ucwords( $expected_plan )
			);
		}

		return $message;
	}

	/**
	 * License inactive license.
	 *
	 * @since 6.2.0
	 */
	public function license_inactive_error_message() {

		$license_key = $this->license_key;
		$message     = '';

		if ( '' === $this->license_key ) {

			/* translators: %1$s: Vendor Plugin */
			$message = sprintf(
				/* translators: %1$s: Plugin name; %2$s: URL for License Page. */
				__( 'We have noticed that the license for <b>%1$s</b> plugin is not active. To utilize this feature, please activate the license.', 'woocommerce-booking' ),
				$this->plugin_name
			);
		}

		return $message;
	}

	/**
	 * Checks if License is for Starter Plan.
	 *
	 * @since 5.12.0
	 */
	public function starter_license() {
		$this->check_license_type();
		return 'starter' === $this->license_type;
	}

	/**
	 * Starter Plan Error Message.
	 *
	 * @since 5.12.0
	 */
	public function starter_license_error_message() {
		return $this->license_error_message( 'starter' );
	}

	/**
	 * Checks if License is for Business Plan.
	 *
	 * @since 5.12.0
	 */
	public function business_license() {
		$this->check_license_type();
		return 'enterprise' === $this->license_type || 'business' === $this->license_type;
	}

	/**
	 * Business Plan Error Message.
	 *
	 * @since 5.12.0
	 */
	public function business_license_error_message() {
		return $this->license_error_message( 'business' );
	}

	/**
	 * Checks if License is for Enterprise Plan.
	 *
	 * @since 5.12.0
	 */
	public function enterprise_license() {
		return 'enterprise' === $this->license_type;
	}

	/**
	 * Enterprise Plan Error Message.
	 *
	 * @since 5.12.0
	 */
	public function enterprise_license_error_message() {
		return $this->license_error_message( 'enterprise' );
	}

	/**
	 * Displays an error notice on the Admin page.
	 *
	 * @param string $notice Error notice to be displayed.
	 *
	 * @since 5.12.0
	 */
	public function display_error_notice( $notice ) {
		printf( "<div class='notice notice-error'><p>%s</p></div>", $notice ); // phpcs:ignore
	}

	/**
	 * Displays an error notice if any of the Vendor Plugins are activated with an un-supported license.
	 *
	 * @since 5.12.0
	 */
	public function vendor_plugin_license_error_notice() {

		global $current_screen;

		if ( ! is_admin() || ( 'page' !== $current_screen->post_type && 'post' !== $current_screen->post_type && 'update' !== $current_screen->base && ! $this->business_license() ) ) {

			/* translators: %1$s: Vendor Plugin, %2$s: Current License, %3$s: Expected License */
			$this->plugin_license_error_message  = __( 'You have activated the %1$s Plugin. Your current license ( %2$s ) does not offer support for Vendor Plugins. Please upgrade to the %3$s License.', 'woocommerce-booking' ); //phpcs:ignore

			if ( class_exists( 'WeDevs_Dokan' ) ) {
				$notice = sprintf(
					/* translators: %1$s: Vendor Plugin, %2$s: Current License, %3$s: Expected License */
					__( $this->plugin_license_error_message, 'woocommerce-booking' ), //phpcs:ignore
					'Dokan Multivendor',
					ucwords( $this->license_type ),
					'Business or Enterprise'
				);

				$this->display_error_notice( $notice );
			}

			if ( function_exists( 'is_wcvendors_active' ) && is_wcvendors_active() ) {
				$notice = sprintf(
					/* translators: %1$s: Vendor Plugin, %2$s: Current License, %3$s: Expected License */
					__( $this->plugin_license_error_message, 'woocommerce-booking' ), //phpcs:ignore
					'WC Vendors',
					ucwords( $this->license_type ),
					'Business or Enterprise'
				);

				$this->display_error_notice( $notice );
			}

			if ( function_exists( 'is_wcfm_page' ) ) {
				$notice = sprintf(
					/* translators: %1$s: Vendor Plugin, %2$s: Current License, %3$s: Expected License */
					__( $this->plugin_license_error_message, 'woocommerce-booking' ), //phpcs:ignore
					'WCFM Marketplace',
					ucwords( $this->license_type ),
					'Business or Enterprise'
				);

				$this->display_error_notice( $notice );
			}
		}
	}

	/**
	 * Displays the Enterprise License Error Message.
	 *
	 * @param string $screen Current Screen.
	 *
	 * @since 5.12.0
	 */
	public function show_enterprise_license_error_message( $screen ) {
		if ( 'outlook_calendar' === $screen ) {
			?>
				<div class="bkap-plugin-error-notice-admin"><?php echo bkap_admin_license()->enterprise_license_error_message(); // phpcs:ignore; ?></div>
			<?php
		}
	}

	/**
	 * Reurns the description for a License Error Code.
	 *
	 * @param string $error_code Errorcode.
	 *
	 * @since 5.19.0
	 */
	public function return_license_error_code_description( $error_code ) {

		$notice = '';

		switch ( $error_code ) {

			case '10a':
				$notice = __( 'The License Key provided is invalid.', 'woocommerce-booking' );
				break;

			case '10b':
				$notice = __( 'A <strong>403 Forbidden</strong> error has been received from the Tyche Server. This isn\'t a problem with your license key but rather with the destination server as the activation request was flatly rejected. Please check your system configuration and ensure that your system is properly provisioned to send valid API requests.', 'woocommerce-booking' );
				break;

			case '10c':
				$domain_name = ( isset( $_SERVER['SERVER_NAME'] ) && '' !== $_SERVER['SERVER_NAME'] ? $_SERVER['SERVER_NAME'] : '[please provide your domain name here]' ); // phpcs:ignore
				$ip_address  = ( isset( $_SERVER['REMOTE_ADDR'] ) && '' !== $_SERVER['REMOTE_ADDR'] ? $_SERVER['REMOTE_ADDR'] : '[please provie your IP Address here]' ); // phpcs:ignore

				$notice = sprintf(
					/* translators: %1s: Create Ticket Link; %2s: Domain Name; %3s: IP address;%4s: License Key. */
					__(
						'Your License Activation Request was unsuccessful because API requests from your system to Tyche Softwares have been blocked by the Tyche Softwares Firewall.<br/>
							<br/>
							To resolve this issue, kindly create a ticket <a href="%1$s"> here </a> and provide the following details ( in the ticket ):<br/>
							<br/>
							<strong>Domain Name:</strong> %2$s<br/>
							<strong>IP Address:</strong> %3$s<br/>
							<strong>License Key:</strong> %4$s<br/>
							<strong>Temporary WP Admin Access:</strong> <em>[please provide username and password here for access to your WP site as it may be needed for debugging]</em><br/>
							<strong>Temporary FTP Access:</strong> <em>[please provide FTP details here as it may be needed if the Tyche Plugin files need to be updated]</em><br/>
							<br/>
							Please deactivate the temporary FTP and WP Admin access as soon as this issue has been resolved by the Tyche Softwares Support Team.',
						'woocommerce-booking'
					),
					'https://support.tychesoftwares.com/help/2285384554',
					$domain_name,
					$ip_address,
					$this->license_key
				);
				break;
			case 'expired':
				$notice = __( 'The License Key provided is expired.', 'woocommerce-booking' );
				break;
			default:
				$notice = sprintf(
				/* translators: %s: License Key */
					__( 'An error has been encountered while trying to activate your license key: %s. Please check that you typed in the key correctly.', 'woocommerce-booking' ),
					$this->license_key
				);
				break;
		}

		return $notice;
	}
}
