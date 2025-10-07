<?php

namespace ElementPack\Modules\WebhookForm;

use ElementPack\Base\Element_Pack_Module_Base;

if (!defined('ABSPATH')) {
	exit;
}

class Module extends Element_Pack_Module_Base {

	public static $api_settings;

	/**
	 * Sanitize posted value recursively to support arrays (e.g., checkbox[])
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	protected function sanitize_posted_value( $value ) {
		if ( is_array( $value ) ) {
			$sanitized = array();
			foreach ( $value as $key => $item ) {
				$sanitized[$key] = $this->sanitize_posted_value( $item );
			}
			return $sanitized;
		}

		if ( is_email( $value ) ) {
			return strip_tags( sanitize_email( $value ) );
		}

		return strip_tags( sanitize_textarea_field( $value ) );
	}

	public function get_name() {
		return 'webhook-form';
	}

	public function get_widgets() {

		$widgets = ['Webhook_Form'];

		return $widgets;
	}

	public function __construct() {
		parent::__construct();
		add_action('wp_ajax_nopriv_submit_webhook_form', array($this, 'submit_webhook_form'));
		add_action('wp_ajax_submit_webhook_form', array($this, 'submit_webhook_form'));
		$this::$api_settings = get_option('element_pack_api_settings');
	}

	public function is_valid_captcha() {

		$ep_api_settings = $this::$api_settings;

		if (isset($_POST['g-recaptcha-response']) and !empty($ep_api_settings['recaptcha_secret_key'])) {
			$request  = wp_remote_get('https://www.google.com/recaptcha/api/siteverify?secret=' . $ep_api_settings['recaptcha_secret_key'] . '&response=' . esc_textarea($_POST["g-recaptcha-response"]) . '&remoteip=' . $_SERVER["REMOTE_ADDR"]);
			$response = wp_remote_retrieve_body($request);

			$result = json_decode($response, TRUE);

			if (isset($result['success']) && $result['success'] == 1) {
				// Captcha ok
				return true;
			} else {
				// Captcha failed;
				return false;
			}
		}

		return false;
	}

	public function submit_webhook_form() {

		if (!wp_verify_nonce($_POST['nonce'], 'element-pack-site')) {
			echo json_encode(array(
				'success' => false,
				'message' => esc_html__('Nonce verification failed', 'bdthemes-element-pack'),
			));
			wp_die();
		}

		$post_id         = sanitize_text_field($_REQUEST['page_id']);
		$widget_id       = sanitize_text_field($_REQUEST['widget_id']);
		$transient_key   = 'bdt_ep_webhook_form_data_' . $widget_id;
		$transient_value = get_transient($transient_key);
		$ep_api_settings = $this::$api_settings;


		$form_data = array();

		foreach ($_POST as $field => $value) {
			$sanitized = $this->sanitize_posted_value($value);
			if (is_array($sanitized)) {
				$form_data[$field] = implode(', ', $sanitized); // turn array into "val1, val2"
			} else {
				$form_data[$field] = $sanitized;
			}
		}		

		$success_text = isset($form_data['success_text']) & !empty($form_data['success_text']) ? esc_html($form_data['success_text']) : esc_html__('Your data has been sent successfully.', 'bdthemes-element-pack');

		unset($form_data['action']);
		unset($form_data['nonce']);

		if (isset($form_data['widget_id'])) {
			unset($form_data['widget_id']);
		}

		$headers = array();

		if (!empty($transient_value['header'])) {
			$headers = array_merge($headers, $transient_value['header']);
		}

		if (!empty($transient_value['body'])) {
			$form_data = array_merge($form_data, $transient_value['body']);
		}

		$connection_type = isset($transient_value['connection_type']) ? $transient_value['connection_type'] : 'webhook';
		$hook_url = '';

		if ($connection_type === 'google_sheets') {
			// Handle centralized OAuth Google Sheets connection
			$oauth_result = $this->handle_centralized_oauth_submission($form_data, $transient_value);
			if (!$oauth_result['success']) {
				echo json_encode($oauth_result);
				wp_die();
			}
			// OAuth handling is complete, exit here
			echo json_encode($oauth_result);
			wp_die();
		} else {
			$hook_url = $transient_value['webhook_url'];
			if (empty($hook_url)) {
				echo json_encode(array(
					'success' => false,
					'message' => esc_html__('Webhook URL empty.', 'bdthemes-element-pack'),
				));
				wp_die();
			}
		}

		/** Recaptcha*/


		$widget_settings = $this->get_widget_settings($post_id, $widget_id);

		if (isset($widget_settings['show_recaptcha']) && $widget_settings['show_recaptcha'] == 'yes') {
			if (!empty($ep_api_settings['recaptcha_site_key']) and !empty($ep_api_settings['recaptcha_secret_key'])) {
				if (!$this->is_valid_captcha()) {
					echo json_encode(array(
						'success' => false,
						'message' => esc_html__('Error in the reCaptcha.', 'bdthemes-element-pack'),
					));
					wp_die();
				}
			}
		}

		$updated_url = str_replace("&#038;", "&", $hook_url);

		// Standard webhook handling
		$response = wp_remote_post($updated_url, array(
			'headers' => $headers,
			'body'    => $form_data,
		));

		if (is_wp_error($response)) {
			$error_message = $response->get_error_message();
			echo json_encode(array(
				'success' => false,
				'message' => esc_html__($error_message, 'bdthemes-element-pack'),
			));
		} else {
			$body = wp_remote_retrieve_body($response);
			$body = json_decode($body, true);

			if (isset($body['success']) && !$body['success']) {
				echo json_encode(array(
					'success' => false,
					'message' => isset($body['data']['message']) ? esc_html($body['data']['message']) : esc_html__('Error in the response body.', 'bdthemes-element-pack'),
				));
			} else {
				echo json_encode(array(
					'success' => true,
					'message' => $success_text,
				));
			}
		}

		wp_die();
	}

	/**
	 * Handle centralized OAuth submission
	 * 
	 * @param array $form_data Form submission data
	 * @param array $transient_value Widget settings from transient
	 * @return array Response array with success status and message
	 */
	public function handle_centralized_oauth_submission($form_data, $transient_value) {
		// Get selected sheet from widget settings
		$sheet_id = !empty($transient_value['selected_google_sheet']) ? $transient_value['selected_google_sheet'] : '';
		
		if (empty($sheet_id)) {
			return array(
				'success' => false,
				'message' => esc_html__('No Google Sheet selected. Please select a sheet in the widget settings.', 'bdthemes-element-pack'),
			);
		}
		
		// Get access token from centralized OAuth data
		if (!class_exists('\ElementPack\Admin\Google_OAuth_Handler')) {
			return array(
				'success' => false,
				'message' => esc_html__('OAuth handler not available.', 'bdthemes-element-pack'),
			);
		}
		
		$access_token = \ElementPack\Admin\Google_OAuth_Handler::get_access_token();
		
		if (!$access_token) {
			return array(
				'success' => false,
				'message' => esc_html__('Google account not connected or token expired. Please reconnect in Element Pack settings.', 'bdthemes-element-pack'),
			);
		}
		
		// Prepare data for Google Sheets
		$sheet_data = array();
		foreach ($form_data as $key => $value) {
			if ($key !== 'action' && $key !== 'widget_id' && $key !== 'page_id') {
				$sheet_data[] = $value;
			}
		}
		
		// Add timestamp
		$sheet_data[] = current_time('mysql');
		
		// Prepare request data for Google Sheets API
		$request_data = array(
			'values' => array($sheet_data),
			'majorDimension' => 'ROWS'
		);
		
		// Make request to Google Sheets API
		$api_url = 'https://sheets.googleapis.com/v4/spreadsheets/' . $sheet_id . '/values/Sheet1:append?valueInputOption=RAW&insertDataOption=INSERT_ROWS';
		
		$response = wp_remote_post($api_url, array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $access_token,
				'Content-Type' => 'application/json',
			),
			'body' => json_encode($request_data),
			'timeout' => 30
		));
		
		if (is_wp_error($response)) {
			return array(
				'success' => false,
				'message' => esc_html__('Failed to connect to Google Sheets: ', 'bdthemes-element-pack') . $response->get_error_message(),
			);
		}
		
		$response_code = wp_remote_retrieve_response_code($response);
		$response_body = wp_remote_retrieve_body($response);
		$response_data = json_decode($response_body, true);
		
		if ($response_code !== 200) {
			$error_message = 'Unknown error';
			
			if (isset($response_data['error'])) {
				$error_message = $response_data['error']['message'] ?? $error_message;
				
				// Handle specific OAuth errors
				if (strpos($error_message, 'Invalid Credentials') !== false || 
					strpos($error_message, 'Request had invalid authentication') !== false) {
					return array(
						'success' => false,
						'message' => esc_html__('OAuth token has expired. Please reconnect your Google account in Element Pack settings.', 'bdthemes-element-pack'),
					);
				}
			}
			
			return array(
				'success' => false,
				'message' => esc_html__('Failed to submit to Google Sheets: ', 'bdthemes-element-pack') . $error_message,
			);
		}
		
		return array(
			'success' => true,
			'message' => esc_html__('Form submitted successfully to Google Sheets!', 'bdthemes-element-pack'),
		);
	}
}
