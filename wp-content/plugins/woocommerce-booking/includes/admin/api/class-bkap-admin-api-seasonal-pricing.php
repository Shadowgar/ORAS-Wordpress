<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * REST API for Seasonal Pricing.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/API/SeasonalPricing
 * @category    Classes
 * @since       5.19.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * BKAP REST API.
 *
 * @since 5.19.0
 */
class BKAP_Admin_API_Seasonal_Pricing extends BKAP_Admin_API {

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

		// Fetch Seasonal Pricing data.
		register_rest_route(
			self::$base_endpoint,
			'seasonal-pricing/fetch',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'fetch_seasonal_pricing_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Save Seasonal Pricing data.
		register_rest_route(
			self::$base_endpoint,
			'seasonal-pricing/save',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'save_seasonal_pricing_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Save NEW Seasonal Pricing data.
		register_rest_route(
			self::$base_endpoint,
			'seasonal-pricing/save-season-data',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'save_new_seasonal_pricing_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Delete Seasonal Pricing data.
		register_rest_route(
			self::$base_endpoint,
			'seasonal-pricing/delete-season-data',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'delete_seasonal_pricing_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);
	}

	/**
	 * Returns Seasonal Pricing Data.
	 *
	 * @param bool $return_raw Whether to return the Raw response.
	 *
	 * @since 5.19.0
	 */
	public static function fetch_seasonal_pricing_data( $return_raw = false ) {
		$response = array();

		$seasonal_settings                      = bkap_json_decode( get_option( 'booking_seasonal_pricing_settings' ) );
		$response['enable_seasonal_pricing']    = self::check( $seasonal_settings, 'enable_seasonal_pricing' );
		$response['seasons_configuration_data'] = self::fetch_seasons_configuration_data();

		return self::return_response( $response, $return_raw );
	}

	/**
	 * Fetches Seasons Configuration from the DB.
	 *
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function fetch_seasons_configuration_data( $product_id = 0 ) {

		global $wpdb;

		$data = array();

		if ( 0 === $product_id ) {
			$query = 'SELECT * FROM `' . $wpdb->prefix . "booking_seasonal_pricing` WHERE season_type = 'GLOBAL'";
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
			$results = $wpdb->get_results( $query );
		} else {

			$query = 'SELECT * FROM `' . $wpdb->prefix . "booking_seasonal_pricing` WHERE post_id = %d AND season_type = 'LOCAL'";
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
			$results = $wpdb->get_results( $wpdb->prepare( $query, $product_id ) );
		}

		foreach ( $results as $key => $value ) {

			$user_role     = bkap_json_decode( $value->user_role );
			$user_role_str = '';

			if ( is_array( $user_role ) && count( $user_role ) > 0 ) {
				foreach ( $user_role as $key => $val ) {
					$user_role_str .= $val . ',';
				}

				$user_role_str = substr( $user_role_str, 0, strlen( $user_role_str ) - 1 );
			}

			$user_role_str = explode( ',', $user_role_str );

			$data[ $value->id ] = array(
				'season_id'         => $value->id,
				'season_name'       => $value->season_name,
				'user_role'         => $user_role_str,
				'start_date'        => $value->start_date,
				'end_date'          => $value->end_date,
				'amount_or_percent' => $value->amount_or_percent,
				'operator'          => $value->operator,
				'price'             => number_format( $value->price, wc_get_price_decimals(), '.', '' ),
				'years'             => 1,
				'is_checked'        => false,
			);
		}

		return $data;
	}

	/**
	 * Gets WPRoles.
	 *
	 * @since 5.19.0
	 */
	public static function get_wp_roles() {
		global $wp_roles;
		return $wp_roles->get_names();
	}

	/**
	 * Saves Seasonal Pricing Data.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function save_seasonal_pricing_data( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$data              = $request->get_param( 'data' );
		$seasonal_settings = bkap_json_decode( get_option( 'booking_seasonal_pricing_settings' ) );

		if ( is_array( $data ) ) {

			if ( isset( $data['seasons_configuration_data'] ) ) {
				unset( $data['seasons_configuration_data'] );
			}

			if ( is_null( $seasonal_settings ) ) {
				$seasonal_settings = new stdClass();
			}

			foreach ( $data as $key => $setting ) {
				$seasonal_settings->{$key} = $setting;
			}

			update_option( 'booking_seasonal_pricing_settings', wp_json_encode( $seasonal_settings ) );
			return self::response( 'success', array() );
		}

		return self::response( 'error', array( 'error_description' => 'Unknown Error' ) );
	}

	/**
	 * Saves NEW Seasonal Pricing Data.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function save_new_seasonal_pricing_data( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$season_data = $request->get_param( 'season_data' );
		$product_id  = $request->get_param( 'product_id' );

		if ( ! is_null( $product_id ) ) {
			$season_data['product_id'] = $product_id;
			bkap_seasonal_pricing()->save_season( $season_data );
			return self::response( 'success', array( 'seasons_configuration_data' => self::fetch_seasons_configuration_data( $product_id ) ) );

		} else {
			if ( is_array( $season_data ) && count( $season_data ) > 0 ) {
				bkap_seasonal_pricing()->save_global_season( $season_data );
				return self::response( 'success', array( 'seasons_configuration_data' => self::fetch_seasons_configuration_data() ) );
			}
		}

		return self::response( 'error', array( 'error_description' => 'Unknown Error' ) );
	}

	/**
	 * Deletes Seasonal Pricing Data.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function delete_seasonal_pricing_data( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$ids        = $request->get_param( 'id' );
		$product_id = $request->get_param( 'product_id' );
		if ( '' !== $ids ) {

			if ( 'all' === $ids ) {
				if ( $product_id > 0 ) {
					bkap_seasonal_pricing()->delete_all_seasons( $product_id );
				} else {
					bkap_seasonal_pricing()->delete_all_global_seasons();
				}
			} else {
				if ( ! is_array( $ids ) ) {
					$ids = array( $ids );
				}

				foreach ( $ids as $id ) {
					bkap_seasonal_pricing()->delete_global_season( $id );
				}
			}

			if ( $product_id > 0 ) {
				return self::response( 'success', array( 'seasons_configuration_data' => self::fetch_seasons_configuration_data( $product_id ) ) );
			} else {
				return self::response( 'success', array( 'seasons_configuration_data' => self::fetch_seasons_configuration_data() ) );
			}
		}

		return self::response( 'error', array( 'error_description' => 'Unknown Error' ) );
	}
}
