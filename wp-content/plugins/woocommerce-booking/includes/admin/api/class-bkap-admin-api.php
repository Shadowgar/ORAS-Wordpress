<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * REST API for Admin.
 *
 * Will be used to fetch data that will be passed to Vue.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/API
 * @category    Classes
 * @since       5.19.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * BKAP REST API.
 *
 * @since 5.19.0
 */
class BKAP_Admin_API extends BKAP_Admin {

	/**
	 * REST Base Endpoint.
	 *
	 * @var string
	 */
	public static $base_endpoint = 'bkap/admin/v1';

	/**
	 * Returns the REST API Endpoint.
	 *
	 * @since 5.19.0
	 */
	public static function endpoint() {
		return self::$base_endpoint;
	}

	/**
	 * Returns the REST API response.
	 *
	 * @param string $type Response Type.
	 * @param string $response Response.
	 *
	 * @since 5.19.0
	 */
	public static function response( $type, $response ) {
		$response['type'] = $type;
		return self::return_response( $response );
	}

	/**
	 * Returns the REST API response.
	 *
	 * @param string|array $response Response Data.
	 * @param bool         $return_raw Returns the response without passing it through the rest_ensure_response function.
	 *
	 * @since 5.19.0
	 */
	public static function return_response( $response, $return_raw = false ) {
		return $return_raw ? $response : rest_ensure_response( $response );
	}

	/**
	 * Returns a success message.
	 *
	 * @since 5.19.0
	 */
	public static function success() {
		return self::return_response( 'success' );
	}

	/**
	 * Returns an error message.
	 *
	 * @since 5.19.0
	 */
	public static function error() {
		return self::return_response( 'error' );
	}

	/**
	 * Verify nonce.
	 *
	 * @param WP_REST_Request $request Request.
	 * @param bool            $stop_execution TRUE - stops execution, FALSE - return status of nonce verification.
	 *
	 * @since 5.19.0
	 */
	public static function verify_nonce( $request, $stop_execution = true ) {
		if ( ! wp_verify_nonce( $request->get_header( 'X-WP-Nonce' ), 'wp_rest' ) || ! ( current_user_can( 'manage_options' ) || current_user_can( 'manage_woocommerce' ) ) ) {

			if ( $stop_execution ) {
				self::error();
				die(); // phpcs:ignore
			}

			return false;
		}

		return true;
	}

	/**
	 * Get Permissions
	 *
	 * @param WP_REST_Request $request Request.
	 * @since 5.19.0
	 */
	public static function get_permission( $request ) {

		if ( current_user_can( 'manage_options' ) || current_user_can( 'manage_woocommerce' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Checks if a child of an object exists and returns the data.
	 *
	 * Returns ean empty value if not set.
	 *
	 * @param object|array $parent Parent Variable.
	 * @param string       $child Child Variable.
	 * @param string       $default_value Default Value.
	 *
	 * @since 5.19.0
	 */
	public static function check( $parent, $child, $default_value = '' ) {

		$value = '';

		if ( is_object( $parent ) ) {
			$value = isset( $parent->$child ) && '' !== $parent->$child ? $parent->$child : $default_value;
		} elseif ( is_array( $parent ) ) {
			$value = isset( $parent[ $child ] ) && '' !== $parent[ $child ] ? $parent[ $child ] : $default_value;
		}

		return $value;
	}

	/**
	 * Returns a value if the target value is empty.
	 *
	 * @param string $value Target Value.
	 * @param string $return_value_if_empty Value to be returned if target value is empty.
	 *
	 * @since 5.19.0
	 */
	public static function return_value_if_empty( $value, $return_value_if_empty ) {
		return '' === $value ? $return_value_if_empty : $value;
	}
}
