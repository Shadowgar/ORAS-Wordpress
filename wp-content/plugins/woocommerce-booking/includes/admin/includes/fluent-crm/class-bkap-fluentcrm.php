<?php
/**
 * Bookings & Appointment Plugin for WooCommerce
 *
 * FluentCRM
 *
 * @author   Tyche Softwares
 * @package  BKAP/FluentCRM
 * @category Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class.
 *
 * @since   5.2.0
 */
class BKAP_FluentCRM {

	/**
	 * FluentCRM API Name
	 *
	 * @var string $fluentcrm_api_name FluentCRM API Name.
	 */
	public $fluentcrm_api_name;

	/**
	 * FluentCRM API Secret
	 *
	 * @var string $fluentcrm_api_key API Secret.
	 */
	public $fluentcrm_api_key;

	/**
	 * FluentCRM Lists
	 *
	 * @var string $bkap_fluentcrm_list FluentCRM Lists.
	 */
	public $bkap_fluentcrm_list;

	/**
	 * Hold my instance
	 *
	 * @var $_instance Instance of the class.
	 */
	protected static $_instance; // phpcs:ignore

	/**
	 * API endpoint base
	 *
	 * @var string
	 */
	public $fluentcrm_api_url = '';

	/**
	 * Default Tags
	 *
	 * @var array
	 */
	public $events = array( 'Booking Created', 'Booking Updated', 'Booking Deleted', 'Booking Confirmed' );

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->set_api_parameters();

		// Adding Booking data to FluentCRM contact.
		add_action( 'bkap_update_booking_post_meta', array( $this, 'bkap_fluentcrm_booking_created' ), 20, 2 );
		add_action( 'bkap_after_zoom_meeting_created', array( $this, 'bkap_fluentcrm_booking_created' ), 10, 2 );

		add_action( 'bkap_after_update_booking_post', array( $this, 'bkap_fluentcrm_booking_updated' ), 20, 3 );
		add_action( 'bkap_after_rescheduling_booking', array( $this, 'bkap_fluentcrm_booking_updated' ), 20, 3 );

		// Booking Confirmed.
		add_action( 'bkap_booking_confirmed_using_icon', array( $this, 'bkap_fluentcrm_booking_confirmed' ), 10, 1 );
		add_action( 'bkap_booking_is_confirmed', array( $this, 'bkap_fluentcrm_booking_confirmed' ), 10, 1 );

		// Booking Cancelled.
		add_action( 'bkap_booking_is_cancelled', array( $this, 'bkap_fluentcrm_booking_cancelled' ), 20, 1 );
		add_action( 'bkap_reallocation_of_booking', array( $this, 'bkap_fluentcrm_booking_reallocated' ), 20, 4 );

		// Product Settings.
		add_action( 'bkap_after_zoom_meeting_settings_product', array( $this, 'bkap_fluentcrm_product_settings' ), 10, 2 );
	}

	/**
	 * FluentCRM Settings in Booking Meta Box-> Integrations-> FluentCRM
	 *
	 * @param int   $product_id Product ID.
	 * @param array $booking_settings Booking Settings.
	 *
	 * @since 5.12.0
	 */
	public function bkap_fluentcrm_product_settings( $product_id, $booking_settings ) {

		$bkap_el_option = apply_filters( 'bkap_el_option', true );
		if ( ( ! $this->bkap_fluentcrm_lite_active() || ! $this->bkap_fluentcrm_pro_active() ) || ! $bkap_el_option ) {
			return;
		}

		$enable_fluentcrm = '';
		if ( isset( $booking_settings['bkap_fluentcrm'] ) && 'on' === $booking_settings['bkap_fluentcrm'] ) {
			$enable_fluentcrm = 'checked';
		}

		$settings = get_option(
			'bkap_fluentcrm_connection',
			array(
				'bkap_fluentcrm_api_name' => '',
				'bkap_fluentcrm_api_key'  => '',
				'bkap_fluentcrm_list'     => '',
			)
		);

		$bkap_fluentcrm_api_name = $settings['bkap_fluentcrm_api_name'];
		$bkap_fluentcrm_api_key  = $settings['bkap_fluentcrm_api_key'];

		$data_set = true;
		if ( '' !== $bkap_fluentcrm_api_name && '' !== $bkap_fluentcrm_api_key ) {
			$data_set = true;
			$response = bkap_fluentcrm()->get_lists();
		}
		?>
		<button type="button" class="bkap-integrations-accordion bkap_integration_fluentcrm_button"><b><?php esc_html_e( 'FluentCRM', 'woocommerce-booking' ); ?></b></button>
		<div class="bkap_google_sync_settings_content bkap_integrations_panel bkap_integration_fluentcrm_panel">
			<?php
			if ( ! $this->bkap_fluentcrm_lite_active() || ! $this->bkap_fluentcrm_pro_active() ) {
				$class   = 'notice notice-info';
				$message = __( 'FluentCRM plugin is not active. Please install and activate it.', 'woocommerce-booking' );
				printf( '<p class="%1$s">%2$s</p>', $class, $message ); // phpcs:ignore
			} else {
				?>
			<table class='form-table bkap-form-table'>
				<tr>
					<th>
						<?php esc_html_e( 'Enable FluentCRM', 'woocommerce-booking' ); ?>
					</th>
					<td>
						<label class="bkap_switch">
							<input id="bkap_enable_fluentcrm" name= "bkap_enable_fluentcrm" type="checkbox" <?php echo esc_attr( $enable_fluentcrm ); ?>/>
						<div class="bkap_slider round"></div>
					</td>
					<td>
						<img class="help_tip" width="16" height="16" data-tip="<?php esc_attr_e( 'Enable FluentCRM.', 'woocommerce-booking' ); ?>" src="<?php echo esc_url( plugins_url( 'woocommerce/assets/images/help.png' ) ); ?>"/>
					</td>
				</tr>

				<?php

				if ( ! is_wp_error( $response ) && isset( $response['lists'] ) ) {
					$bkap_fluentcrm_list = '';
					if ( isset( $booking_settings['bkap_fluentcrm_list'] ) && '' !== $booking_settings['bkap_fluentcrm_list'] ) {
						$bkap_fluentcrm_list = $booking_settings['bkap_fluentcrm_list'];
					}
					?>
				<tr>
					<th>
						<?php esc_html_e( 'Select List', 'woocommerce-booking' ); ?>
					</th>
					<td>
						<select name="bkap_fluentcrm_list" id="bkap_fluentcrm_list">
						<option value=''><?php esc_html_e( 'Select List', 'woocommerce-booking' ); ?></option>
						<?php
						foreach ( $response['lists'] as $list ) {
							$selected_list         = ( $list['id'] == $bkap_fluentcrm_list ) ? 'selected' : '';
							$fluent_crm_list_title = $list['title'];
							printf( "<option value='%s' %s>%s</option>", esc_attr( $list['id'] ), esc_attr( $selected_list ), esc_html( $fluent_crm_list_title ) );
						}
						?>
						</select>
					</td>
					<td>
						<img class="help_tip" width="16" height="16" data-tip="<?php esc_attr_e( 'Contact will be added to selected list..', 'woocommerce-booking' ); ?>" src="<?php echo esc_url( plugins_url( 'woocommerce/assets/images/help.png' ) ); ?>"/>
					</td>
				</tr>
				<?php } ?>
				<tr>
					<th></th>
					<td colspan="2">
						<p><i>
							<?php
							if ( ! $data_set ) {
								$redirect_args = array(
									'page'      => 'woocommerce_booking_page',
									'action'    => 'calendar_sync_settings',
									'section'   => 'fluentcrm',
									'post_type' => 'bkap_booking',
								);
								$url           = add_query_arg( $redirect_args, admin_url( '/edit.php?' ) );
								/* translators: %s: FluentCRM Settings page link */
								$api_msg = sprintf( __( 'Set App API Name and API Key for FluentCRM <a href="%s" target="_blank">here.</a>', 'woocommerce-booking' ), $url );
								echo $api_msg; // phpcs:ignore
							}
							?>
							</i>
						</p>
					</td>
				</tr>
			</table>
			<?php } ?>
		</div>
		<?php
	}

	/**
	 * Set FluentCRM API parameters.
	 *
	 * @return string
	 */
	public function set_api_parameters() {

		$settings = get_option(
			'bkap_fluentcrm_connection',
			array(
				'bkap_fluentcrm_api_name' => '',
				'bkap_fluentcrm_api_key'  => '',
				'bkap_fluentcrm_list'     => '',
			)
		);

		$this->fluentcrm_api_name  = $settings['bkap_fluentcrm_api_name'];
		$this->fluentcrm_api_key   = $settings['bkap_fluentcrm_api_key'];
		$this->bkap_fluentcrm_list = $settings['bkap_fluentcrm_list'];
		$this->fluentcrm_api_url   = home_url() . '/wp-json/fluent-crm/v2/';
	}

	/**
	 * Send request to API
	 *
	 * @param string $called_function Slug.
	 * @param array  $data Data.
	 * @param string $request Request Type.
	 *
	 * @return array|bool|string|WP_Error
	 */
	protected function bkap_send_request( $called_function, $data, $request = 'GET' ) {

		$request_url = $this->fluentcrm_api_url . $called_function;

		$args = array(
			'timeout'     => 45,
			'httpversion' => '1.0',
			'blocking'    => true,
			'body'        => $data,
			'method'      => $request,
		);

		$headers = $this->bkap_get_headers();

		if ( is_array( $headers ) && count( $headers ) > 0 ) {
			$args['headers'] = $headers;
		}

		if ( 'GET' === $request ) {
			$args['body'] = ! empty( $data ) ? $data : '';
			$response     = wp_remote_get( $request_url, $args );
		} elseif ( 'DELETE' === $request || 'PATCH' === $request || 'PUT' === $request ) {
			$args['body']   = ! empty( $data ) ? wp_json_encode( $data ) : array();
			$args['method'] = $request;
			$response       = wp_remote_request( $request_url, $args );
		} else {
			$args['body']   = ! empty( $data ) ? wp_json_encode( $data ) : array();
			$args['method'] = 'POST';
			$response       = wp_remote_post( $request_url, $args );
		}

		if ( ! is_wp_error( $response ) ) {
			$body = wp_remote_retrieve_body( $response );

			if ( $this->is_json( $body ) ) {
				$body = bkap_json_decode( $body, true );
			}

			return $body;
		}

		return $response;
	}

	/**
	 * Function to generate headers.
	 *
	 * @return string
	 */
	public function bkap_get_headers() {

		$name = $this->fluentcrm_api_name;
		$key  = $this->fluentcrm_api_key;

		return apply_filters(
			'bkap_fluentcrm_headers',
			array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Basic ' . base64_encode( $name . ':' . $key ),
			)
		);
	}

	/**
	 * Get Lists.
	 */
	public function get_lists() {

		$response = $this->bkap_get_lists();

		if ( ! is_wp_error( $response ) && isset( $response['lists'] ) ) {

			if ( ! count( $response['lists'] ) ) {
				$response['lists'] = $this->bkap_add_default_list();
			}
		}

		return is_wp_error( $response ) ? array() : $response;
	}

	/**
	 * Get Lists.
	 *
	 * @return bool
	 */
	public function bkap_get_lists( $data = array() ) {

		$endpoint = 'lists';

		if ( isset( $data['id'] ) ) {
			$endpoint .= '/' . $data['id'];
		}
		$params = array();

		return $this->bkap_send_request( $endpoint, $params, 'GET' );
	}

	/**
	 * Adding default list when enabling booking and fluentcrm integration.
	 *
	 * @return bool
	 */
	public function bkap_add_default_list() {

		$endpoint        = 'lists';
		$params['title'] = 'Bookings';
		$params['slug']  = 'bkap-bookings';

		return $this->bkap_send_request( $endpoint, $params, 'POST' );
	}

	/**
	 * Get Tags.
	 *
	 * @return bool
	 */
	public function bkap_get_tags() {

		$endpoint = 'tags';
		$params   = array();

		return $this->bkap_send_request( $endpoint, $params, 'GET' );
	}

	/**
	 * Add Tags.
	 *
	 * @return bool
	 */
	public function bkap_add_tags() {

		$endpoint = 'tags';
		$params   = array();

		$events = $this->events;
		foreach ( $events as $event ) {
			$params['title'] = $event;
			$params['slug']  = $this->bkap_get_slug( $event );
			$this->bkap_send_request( $endpoint, $params, 'POST' );
		}
	}

	/**
	 * Adding tags to contact.
	 *
	 * @param array $data Data.
	 *
	 * @return bool
	 */
	public function bkap_add_tags_to_contact( $data ) {
		$endpoint     = 'subscribers/sync-segments';
		$data['type'] = 'tags';
		$add_tags     = $data['add_tags'];

		foreach ( $this->events as $tag ) {
			if ( in_array( $tag, $add_tags ) ) {
				$data['attach'][] = $this->bkap_get_slug( $tag );
			}
		}

		$this->bkap_send_request( $endpoint, $data, 'POST' );
	}

	/**
	 * Check if contact is already exits
	 *
	 * @param array $data Data.
	 *
	 * @return bool
	 */
	public function bkap_remove_tags( $data ) {

		$endpoint     = 'subscribers/sync-segments';
		$data['type'] = 'tags';
		$remove_tags  = $data['remove_tags'];

		foreach ( $this->events as $tag ) {
			if ( in_array( $tag, $remove_tags ) ) {
				$data['detach'][] = $this->bkap_get_slug( $tag );
			}
		}

		$this->bkap_send_request( $endpoint, $data, 'POST' );
	}

	/**
	 * Check if contact is already exits
	 *
	 * @return bool
	 */
	public function bkap_create_contact( $data = array() ) {

		$endpoint = 'subscribers';

		return $this->bkap_send_request( $endpoint, $data, 'POST' );
	}

	/**
	 * Create Custom Field for Contact.
	 *
	 * @return bool
	 */
	public function bkap_custom_fields() {

		$endpoint = 'custom-fields/contacts';
		$params   = array();
		$response = $this->bkap_send_request( $endpoint, $params, 'GET' );

		$bkap_custom_field = $this->bkap_fluentcrm_custom_fields();

		if ( isset( $response['fields'] ) ) {
			if ( count( $response['fields'] ) > 0 ) {
				$params['fields'] = array_merge( $response['fields'], $bkap_custom_field );
			} else {
				$params['fields'] = $bkap_custom_field;
			}

			$this->bkap_send_request( $endpoint, $params, 'PUT' );
		}
	}

	/**
	 * Adding Note to contact.
	 *
	 * @param array $data Data.
	 *
	 * @return bool
	 */
	public function bkap_add_note( $data ) {

		$endpoint = 'subscribers/' . $data['id'] . '/notes';

		unset( $data['id'] );

		return $this->bkap_send_request( $endpoint, $data, 'POST' );
	}

	/**
	 * Check if a string is json or not.
	 *
	 * @param mixed $string
	 *
	 * @return bool
	 */
	public function is_json( $string ) {
		bkap_json_decode( $string );

		return ( json_last_error() === JSON_ERROR_NONE );
	}

	/**
	 * Get slug for an event
	 *
	 *  @param string $event - event.
	 */
	public function bkap_get_slug( $event ) {
		return strtolower( str_replace( ' ', '-', $event ) );
	}

	/**
	 * Sending data to FluentCRM Based on the Booking Actions.
	 *
	 * @param int    $booking_id Booking ID.
	 * @param array  $booking_data Array of Booking data.
	 * @param string $action Booking Action that is being performed.
	 *
	 * @since 5.12.0
	 */
	public function bkap_fluntcrm_booking_actions( $booking_id, $booking_data, $action = '' ) {

		$product_id = isset( $booking_data['product_id'] ) ? $booking_data['product_id'] : 0;

		if ( $this->bkap_fluentcrm_enable( $product_id ) ) {
			$data                   = array();
			$contact_data           = $this->bkap_fluentcrm_get_contact_data( $booking_id, $booking_data );
			$data                   = array_merge( $data, $contact_data );
			$data['status']         = 'subscribed';
			$data['__force_update'] = 'yes';
			$data['lists']          = array();
			$f_list_id              = $this->bkap_fluentcrm_list_id( $product_id );

			if ( $f_list_id['status'] ) {
				$data['lists'] = array( $f_list_id['status'] );
			}

			$tags = $this->bkap_fluentcrm_get_available_tags();

			switch ( $action ) {
				case 'update':
					$event = $this->events[1];
					break;
				case 'confirm':
					$event = $this->events[3];
					break;
				case 'cancel':
					$event = $this->events[2];
					break;
				default:
					$event = $this->events[0];
					break;
			}

			$additional_data       = $this->bkap_fluentcrm_prepare_custom_fields_data( $booking_id, $booking_data );
			$data['custom_values'] = $additional_data['custom_values'];

			$contact_exists = $this->bkap_create_contact( $data );

			if ( isset( $contact_exists['contact'] ) ) {
				$contact_id = $contact_exists['contact']['id'];
				// Removing Tags.
				$remove_tags                = array();
				$remove_tags['remove_tags'] = array( 'Booking Created', 'Booking Updated', 'Booking Deleted', 'Booking Confirmed' );
				$remove_tags['subscribers'] = array( $contact_id );
				$this->bkap_remove_tags( $remove_tags );

				// Adding Tags.
				$add_tags                = array();
				$add_tags['add_tags']    = array( $event );
				$add_tags['subscribers'] = array( $contact_id );
				$this->bkap_add_tags_to_contact( $add_tags );

				// Adding Note to Contact.
				if ( '' === $action || 'update' === $action ) {
					$note_data         = array();
					$note_data['id']   = $contact_id;
					$note_data['note'] = $additional_data['note'];
					$note_response     = $this->bkap_add_note( $note_data );
				}
			}
		}
	}

	/**
	 * Sending data to FluentCRM when Booking is created.
	 *
	 * @param int   $booking_id Booking ID.
	 * @param array $booking_data Array of Booking data.
	 * @since 5.12.0
	 */
	public function bkap_fluentcrm_booking_created( $booking_id, $booking_data ) {
		$this->bkap_fluntcrm_booking_actions( $booking_id, $booking_data );
	}

	/**
	 * Sending data to FluentCRM when Booking is update via Booking Post.
	 *
	 * @param int   $booking_id Booking ID.
	 * @param obj   $booking Booking Object.
	 * @param array $booking_data Array of Booking data.
	 *
	 * @since 5.12.0
	 */
	public function bkap_fluentcrm_booking_updated( $booking_id, $booking, $booking_data ) {
		$this->bkap_fluntcrm_booking_actions( $booking_id, $booking_data[0], 'update' );
	}

	/**
	 * Sending data to FluentCRM when Booking is confirmed.
	 *
	 * @param int $booking_id Booking ID.
	 *
	 * @since 5.12.0
	 */
	public function bkap_fluentcrm_booking_confirmed( $booking_id ) {

		$booking_data = bkap_get_meta_data( $booking_id );
		$this->bkap_fluntcrm_booking_actions( $booking_id, $booking_data[0], 'confirm' );
	}

	/**
	 * Cancel Booking when cancelling order.
	 *
	 * @param int   $item_id Item ID.
	 * @param array $item_value Item Data.
	 * @param int   $product_id Product ID.
	 * @param int   $order_id Order ID.
	 *
	 * @since 5.12.0
	 */
	public function bkap_fluentcrm_booking_reallocated( $item_id, $item_value, $product_id, $order_id ) {
		$booking_id = bkap_common::get_booking_id( $item_id );
		$this->bkap_fluentcrm_booking_cancelled( $booking_id );
	}

	/**
	 * Sending data to FluentCRM Based on the Booking Actions.
	 *
	 * @param int $booking_id Booking ID.
	 *
	 * @since 5.12.0
	 */
	public function bkap_fluentcrm_booking_cancelled( $booking_id ) {

		$booking_data = bkap_get_meta_data( $booking_id );
		$this->bkap_fluntcrm_booking_actions( $booking_id, $booking_data[0], 'cancel' );
	}

	/**
	 * Add Custom Fields for Contact on FluentCRM connection.
	 *
	 * @param string $bkap_fluentcrm_api_name API Name.
	 * @param string $bkap_fluentcrm_api_key API Key.
	 *
	 * @since 5.12.0
	 */
	public function bkap_add_custom_fields( $bkap_fluentcrm_api_name, $bkap_fluentcrm_api_key ) {
		$this->bkap_custom_fields();
	}

	/**
	 * Checks if FluentCRM lite is installed & Active.
	 *
	 * @since 5.12.0
	 */
	public function bkap_fluentcrm_lite_active() {

		if ( ! is_plugin_active( 'fluent-crm/fluent-crm.php' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Checks if FluentCRM Pro is installed & Active.
	 *
	 * @since 5.12.0
	 */
	public function bkap_fluentcrm_pro_active() {

		if ( ! is_plugin_active( 'fluentcampaign-pro/fluentcampaign-pro.php' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Add default Tags on FluentCRM connection.
	 *
	 * @param string $bkap_fluentcrm_api_name API Name.
	 * @param string $bkap_fluentcrm_api_key API Key.
	 *
	 * @since 5.12.0
	 */
	public function bkap_add_default_events( $bkap_fluentcrm_api_name, $bkap_fluentcrm_api_key ) {
		$this->bkap_add_tags();
	}

	/**
	 * Prepare data to check contact.
	 *
	 * @param int   $booking_id Booking ID.
	 * @param array $booking Booking Data.
	 *
	 * @since 5.12.0
	 */
	public function bkap_fluentcrm_get_contact_data( $booking_id, $booking ) {

		$customer_data = array();

		// Defaults.
		$customer_data = array(
			'first_name' => '',
			'last_name'  => '',
			'email'      => '',
			'phone'      => '',
		);
		$order_id      = $booking['parent_id'];
		$order         = wc_get_order( $order_id );

		if ( $order ) {
			$customer_data['email']          = $order->get_billing_email();
			$customer_data['first_name']     = $order->get_billing_first_name();
			$customer_data['last_name']      = $order->get_billing_last_name();
			$customer_data['phone']          = $order->get_billing_phone();
			$customer_data['address_line_1'] = $order->get_billing_address_1();
			$customer_data['address_line_2'] = $order->get_billing_address_2();
			$customer_data['city']           = $order->get_billing_city();
			$customer_data['state']          = $order->get_billing_state();
			$customer_data['postal_code']    = $order->get_billing_postcode();
			$customer_data['country']        = $order->get_billing_country();
		}

		return $customer_data;
	}

	/**
	 * Check active status and which list to take.
	 *
	 * @param int $product_id Product ID.
	 *
	 * @since 5.12.0
	 */
	public function bkap_fluentcrm_list_id( $product_id ) {

		$result         = array(
			'status' => false,
			'level'  => '',
		);
		$fluentcrm_list = get_post_meta( $product_id, '_bkap_fluentcrm_list', true );
		if ( '' !== $fluentcrm_list ) {
			$check  = true;
			$result = array(
				'status' => (int) $fluentcrm_list,
				'level'  => 'product',
			);
		} else {
			if ( '' !== $this->bkap_fluentcrm_list ) {
				$result = array(
					'status' => (int) $this->bkap_fluentcrm_list,
					'level'  => 'global',
				);
			} else {
				return $result;
			}
		}

		return $result;
	}

	/**
	 * Preparing additional data required for adding/updating the contact.
	 *
	 * @since 5.12.0
	 */
	public function bkap_fluentcrm_prepare_custom_fields_data( $booking_id, $booking_data ) {

		$product_id     = $booking_data['product_id'];
		$start_date     = gmdate( 'Y-m-d', strtotime( $booking_data['start'] ) );
		$end_date       = gmdate( 'Y-m-d', strtotime( $booking_data['end'] ) );
		$start_time     = gmdate( 'Y-m-d H:i:s', strtotime( $booking_data['start'] ) );
		$end_time       = gmdate( 'Y-m-d H:i:s', strtotime( $booking_data['end'] ) );
		$resource_title = ( $booking_data['resource_id'] ) ? get_the_title( $booking_data['resource_id'] ) : '';
		$persons        = bkap_persons_info( $booking_data['persons'], $product_id );
		$zoom_meeting   = get_post_meta( $booking_id, '_bkap_zoom_meeting_link', true );
		$zoom_data      = get_post_meta( $booking_id, '_bkap_zoom_meeting_data', true );
		/* Translators: %s Booking ID */
		$note_title = sprintf( __( 'Booking #%s', 'woocommerce-booking' ), $booking_id );
		$post       = get_post( $product_id );
		$vendor_id  = $post->post_author;

		$labels            = bkap_booking_fields_label();
		$note_description  = $labels['start_date'] . ':' . $start_date . '<br>';
		$note_description .= $labels['end_date'] . ':' . $end_date . '<br>';
		$note_description .= $labels['time_slot'] . ':' . $start_time . ' - ' . $end_time . '<br>';
		$note_description .= __( 'Resource', 'woocommerce-booking' ) . ' : ' . $resource_title . '<br>';
		$note_description .= __( 'Persons', 'woocommerce-booking' ) . ' : ' . $persons . '<br>';
		$note_description  = apply_filters( 'bkap_fluentcrm_note_description', $note_description );

		$data = array(
			'custom_values' => array(
				'bkap_booking_id'        => $booking_id,
				'bkap_product_id'        => $booking_data['product_id'],
				'bkap_start_date'        => $start_date,
				'bkap_end_date'          => $end_date,
				'bkap_start_time'        => $start_time,
				'bkap_end_time'          => $end_time,
				'bkap_resource_id'       => $resource_title,
				'bkap_persons'           => $persons,
				'bkap_timezone'          => $booking_data['timezone_name'],
				'bkap_vendor_id'         => $vendor_id,
				'bkap_duration'          => $booking_data['duration'],
				'bkap_order_id'          => $booking_data['parent_id'],
				'bkap_price'             => $booking_data['cost'],
				'bkap_qty'               => $booking_data['qty'],
				'bkap_order_item_id'     => $booking_data['order_item_id'],
				'bkap_variation_id'      => $booking_data['variation_id'],
				'bkap_gcal_event_uid'    => $booking_data['gcal_event_uid'],
				'bkap_zoom_meeting_link' => $zoom_meeting,
				'bkap_zoom_meeting_data' => $zoom_data,
			),
			'note'          => array(
				'title'       => $note_title,
				'description' => $note_description,
				'type'        => 'note',
				'created_by'  => get_current_user_id(),
			),
		);

		return $data;
	}

	/**
	 * FluentCRM Custom Fields
	 *
	 * @since 5.12.0
	 */
	public function bkap_fluentcrm_custom_fields() {

		return apply_filters(
			'bkap_fluentcrm_custom_fields',
			array(
				array(
					'field_key' => 'text',
					'type'      => 'text',
					'label'     => 'Booking ID',
					'slug'      => 'bkap_booking_id',
				),
				array(
					'field_key' => 'text',
					'type'      => 'text',
					'label'     => 'Product ID',
					'slug'      => 'bkap_product_id',
				),
				array(
					'field_key' => 'date',
					'type'      => 'date',
					'label'     => 'Start Date',
					'slug'      => 'bkap_start_date',
				),
				array(
					'field_key' => 'date',
					'type'      => 'date',
					'label'     => 'End Date',
					'slug'      => 'bkap_end_date',
				),
				array(
					'field_key' => 'date_time',
					'type'      => 'date_time',
					'label'     => 'Start Time',
					'slug'      => 'bkap_start_time',
				),
				array(
					'field_key' => 'date_time',
					'type'      => 'date_time',
					'label'     => 'End Time',
					'slug'      => 'bkap_end_time',
				),
				array(
					'field_key' => 'text',
					'type'      => 'text',
					'label'     => 'Duration',
					'slug'      => 'bkap_duration',
				),
				array(
					'field_key' => 'text',
					'type'      => 'text',
					'label'     => 'Resource',
					'slug'      => 'bkap_resource_id',
				),
				array(
					'field_key' => 'text',
					'type'      => 'text',
					'label'     => 'Persons',
					'slug'      => 'bkap_persons',
				),
				array(
					'field_key' => 'text',
					'type'      => 'text',
					'label'     => 'Timezone',
					'slug'      => 'bkap_timezone',
				),
				array(
					'field_key' => 'text',
					'type'      => 'text',
					'label'     => 'Variation ID',
					'slug'      => 'bkap_variation_id',
				),
				array(
					'field_key' => 'text',
					'type'      => 'text',
					'label'     => 'Order ID',
					'slug'      => 'bkap_order_id',
				),
				array(
					'field_key' => 'text',
					'type'      => 'text',
					'label'     => 'Price',
					'slug'      => 'bkap_price',
				),
				array(
					'field_key' => 'number',
					'type'      => 'number',
					'label'     => 'Quantity',
					'slug'      => 'bkap_qty',
				),
				array(
					'field_key' => 'text',
					'type'      => 'text',
					'label'     => 'Order Item ID',
					'slug'      => 'bkap_order_item_id',
				),
				array(
					'field_key' => 'text',
					'type'      => 'text',
					'label'     => 'Vendor ID',
					'slug'      => 'bkap_vendor_id',
				),
				array(
					'field_key' => 'text',
					'type'      => 'text',
					'label'     => 'Zoom Meeting Link',
					'slug'      => 'bkap_zoom_meeting_link',
				),
				array(
					'field_key' => 'text',
					'type'      => 'text',
					'label'     => 'Zoom Meeting Data',
					'slug'      => 'bkap_zoom_meeting_data',
				),
			)
		);
	}

	/**
	 * Get Available Tags from FluentCRM and Getting ids of Booking tags.
	 *
	 * @since 5.12.0
	 */
	public function bkap_fluentcrm_get_available_tags() {

		$tags     = array();
		$all_tags = $this->bkap_get_tags();
		$events   = $this->events;

		if ( isset( $all_tags['tags'] ) && isset( $all_tags['tags']['data'] ) ) {
			foreach ( $all_tags['tags']['data'] as $tag ) {
				foreach ( $events as $event ) {
					if ( $tag['slug'] === $this->bkap_get_slug( $event ) ) {
						$tags[ $event ] = $tag['id'];
					}
				}
			}
		}
		return $tags;
	}

	/**
	 * FluentCRM Option enable.
	 *
	 * @param int $product_id Product ID.
	 *
	 * @since 5.12.0
	 */
	public function bkap_fluentcrm_enable( $product_id = 0 ) {

		$check                = false;
		$response             = $this->bkap_get_lists();

		if ( is_wp_error( $response ) ) {
			return false;
		}

		if ( ! isset( $response['lists'] ) ) {
			return false;
		}

		if ( $product_id ) {
			$result = $this->bkap_fluentcrm_list_id( $product_id );
			return $result['status'];
		} else {
			$check = true;
		}

		return $check;
	}
}
