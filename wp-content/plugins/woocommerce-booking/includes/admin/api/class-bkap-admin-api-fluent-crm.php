<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * REST API for FluentCRM.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/API/FluentCRM
 * @category    Classes
 * @since       5.19.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * BKAP REST API.
 *
 * @since 5.19.0
 */
class BKAP_Admin_API_FluentCRM extends BKAP_Admin_API {

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

		// Fetch FluentCRM data.
		register_rest_route(
			self::$base_endpoint,
			'fluent-crm/fetch',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'fetch_fluent_crm_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Save FluentCRM data.
		register_rest_route(
			self::$base_endpoint,
			'fluent-crm/save',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'save_fluent_crm_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);
	}

	/**
	 * Returns FluentCRM Data.
	 *
	 * @param bool $return_raw Whether to return the Raw response.
	 *
	 * @since 5.19.0
	 */
	public static function fetch_fluent_crm_data( $return_raw = false ) {

		$fluent_crm_settings = get_option(
			'bkap_fluentcrm_connection',
			array(
				'bkap_fluentcrm_api_name' => '',
				'bkap_fluentcrm_api_key'  => '',
				'bkap_fluentcrm_list'     => '',
			)
		);

		$response                            = array();
		$response['bkap_fluentcrm_api_name'] = self::check( $fluent_crm_settings, 'bkap_fluentcrm_api_name', '' );
		$response['bkap_fluentcrm_api_key']  = self::check( $fluent_crm_settings, 'bkap_fluentcrm_api_key', '' );
		$response['bkap_fluentcrm_list']     = self::check( $fluent_crm_settings, 'bkap_fluentcrm_list', '' );
		$fluent_crm_plugins_active           = bkap_fluentcrm()->bkap_fluentcrm_lite_active() && bkap_fluentcrm()->bkap_fluentcrm_pro_active();
		$fluentcrm_lists                     = $fluent_crm_plugins_active ? bkap_fluentcrm()->get_lists() : array();
		$response['lists']                   = is_array( $fluentcrm_lists ) && count( $fluentcrm_lists ) > 0 && isset( $fluentcrm_lists['lists'] ) ? $fluentcrm_lists['lists'] : array();

		return self::return_response( $response, $return_raw );
	}

	/**
	 * Saves FluentCRM Data.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function save_fluent_crm_data( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$data           = $request->get_param( 'data' );
		$bkap_fluentcrm = bkap_fluentcrm();

		$bkap_fluentcrm->fluentcrm_api_name  = self::check( $data, 'bkap_fluentcrm_api_name', '' );
		$bkap_fluentcrm->fluentcrm_api_key   = self::check( $data, 'bkap_fluentcrm_api_key', '' );
		$bkap_fluentcrm->bkap_fluentcrm_list = self::check( $data, 'bkap_fluentcrm_list', '' );

		$lists = $bkap_fluentcrm->get_lists();

		if ( ! is_array( $lists ) || ( is_array( $lists ) && 0 === count( $lists ) ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Invalid API Name or API Key. Please enter a correct API Name and API Key.', 'woocommerce-booking' ) ) );
		}

		update_option(
			'bkap_fluentcrm_connection',
			array(
				'bkap_fluentcrm_api_name' => self::check( $data, 'bkap_fluentcrm_api_name', '' ),
				'bkap_fluentcrm_api_key'  => self::check( $data, 'bkap_fluentcrm_api_key', '' ),
				'bkap_fluentcrm_list'     => self::check( $data, 'bkap_fluentcrm_list', '' ),
			)
		);

		$bkap_fluentcrm->bkap_add_custom_fields( $bkap_fluentcrm->fluentcrm_api_name, $bkap_fluentcrm->fluentcrm_api_key );
		$bkap_fluentcrm->bkap_add_default_events( $bkap_fluentcrm->fluentcrm_api_name, $bkap_fluentcrm->fluentcrm_api_key );

		return self::response( 'success', array( 'lists' => isset( $lists['lists'] ) ? $lists['lists'] : array() ) );
	}
}
