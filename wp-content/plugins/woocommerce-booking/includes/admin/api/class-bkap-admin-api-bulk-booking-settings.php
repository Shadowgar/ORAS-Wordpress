<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * REST API for Bulk Booking Settings.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/API/Bulk_Booking_Settings
 * @category    Classes
 * @since       5.19.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * BKAP REST API.
 *
 * @since 5.19.0
 */
class BKAP_Admin_API_Bulk_Booking_Settings extends BKAP_Admin_API {

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

		// Save Bulk Booking Settings data.
		register_rest_route(
			self::$base_endpoint,
			'bulk-booking-settings/save',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'save_bulk_booking_settings_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Clear Default Booking Settings.
		register_rest_route(
			self::$base_endpoint,
			'bulk-booking-settings/clear-default-settings',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'clear_default_settings' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);
	}

	/**
	 * Clear Default Booking Settings.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function clear_default_settings( WP_REST_Request $request ) {
		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		delete_option( 'bkap_default_booking_settings' );
		delete_option( 'bkap_default_individual_booking_settings' );

		return self::response( 'success', array( 'message' => __( 'Default Booking Settings have been reset successfully. You may wish to refresh the page to load the new settings.', 'woocommerce-booking' ) ) );
	}

	/**
	 * Return Bulk Booking Setting Data.
	 *
	 * @param bool $return_raw Whether to return the Raw response.
	 *
	 * @since 5.19.0
	 */
	public static function fetch_bulk_booking_settings_data( $return_raw = false ) {

		$response = array();

		$select_product_category_data = array(
			__( 'All Products', 'woocommerce-booking' ) => array(
				'all_products' => __( 'All Products', 'woocommerce-booking' ),
			),
		);

		$categories = get_categories(
			array(
				'taxonomy'       => 'product_cat',
				'order'          => 'ASC',
				'pad_counts'     => 0,
				'hierarchical'   => 1,
				'posts_per_page' => -1,
			)
		);

		foreach ( $categories as $key => $category ) {
			$select_product_category_data[ __( 'Select by Product Category', 'woocommerce-booking' ) ][ 'cat_' . $category->slug ] = ( 'uncategorized' === $category->slug ) ? 'Uncategorized Products' : 'Products in ' . $category->cat_name . ' Category';
		}

		foreach ( self::get_all_products() as $item_id ) {
			$select_product_category_data[ __( 'Select by Product Name', 'woocommerce-booking' ) ][ $item_id ] = get_the_title( $item_id );;
		}

		$response['select_product_category_data']             = $select_product_category_data;
		$response['product_id']                               = array();
		$response['is_bulk_booking']                          = true;
		$response['bulk_booking_booking_settings_data']       = BKAP_Admin_API_Metabox_Booking::fetch_booking_settings_copy( 'bulk_settings' );
		$response['save_selected_options_as_default_options'] = '';
		$response['is_exist_default_booking_settings']        = false !== get_option( 'bkap_default_booking_settings', false ) && false !== get_option( 'bkap_default_individual_booking_settings', false );
		$response['label']                                    = array(
			'btn_clear_defaults'                => __( 'Reset Default Settings', 'woocommerce-booking' ),
			'text_clear_settings_loader'        => __( 'Resetting Default Settings, please wait...', 'woocommerce-booking' ),
			'text_saving_bulk_booking_settings' => __( 'Saving Bulk Booking Settings, please wait...', 'woocommerce-booking' ),
			'select_product_and_category'       => __( 'Select Product and/or Category', 'woocommerce-booking' ),
			'no_match_found'                    => __( 'No matches found', 'woocommerce-booking' ),
		);
		$response['loader']                                   = array(
			'loader_saving_bulk_booking_settings' => false,
		);

		return self::return_response( $response, $return_raw );
	}

	/**
	 * Gets all Products.
	 *
	 * @since 5.19.0
	 */
	public static function get_all_products() {

		$products = get_posts(
			array(
				'post_type'      => array( 'product' ),
				'posts_per_page' => -1,
				'post_status'    => array( 'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash' ),
				'fields'         => 'ids',
			)
		);

		foreach ( $products as $key => $item_id ) {
			$settings = get_post_meta( $item_id, 'woocommerce_booking_settings', true );

			if ( ! isset( $settings['booking_enable_date'] ) || ( isset( $settings['booking_enable_date'] ) && 'on' !== $settings['booking_enable_date'] ) ) {
				unset( $products[ $key ] );
			}
		}

		return $products;
	}

	/**
	 * Saves Metabox Booking Data.
	 *
	 * @param array $settings Settings.
	 *
	 * @since 5.19.0
	 */
	public static function save_metabox_booking_data( $settings ) {

		global $wpdb;

		// Inactivating the old records for the product before adding the new settings from bulk booking settings.
		$wpdb->query( 'UPDATE `' . $wpdb->prefix . "booking_history` SET status = 'inactive' WHERE post_id = '" . $settings['product_id'] . "'" ); // phpcs:ignore

		BKAP_Admin_API_Metabox_Booking::save_metabox_booking_data(
			new WP_REST_Request( 'GET', '/' ),
			$settings
		);
	}

	/**
	 * Save Bulk Booking Settings Data.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function save_bulk_booking_settings_data( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$data                     = $request->get_param( 'data' );
		$ids                      = $request->get_param( 'product_id' );
		$save_settings_as_default = 'on' === self::check( $data, 'save_settings_as_default', '' );
		$product_ids              = array();
		$slugs                    = array();

		if ( $save_settings_as_default ) {
			delete_option( 'bkap_onboarding_booking_settings' );
		}

		if ( '' === $data || ! is_array( $data ) || ( is_array( $data ) && 0 === count( $data ) ) ) {
			return self::response( 'error', array( 'error_description' => 'Invalid Bulk Settings Data' ) );
		}

		if ( '' === $ids || ! is_array( $ids ) || ( is_array( $ids ) && 0 === count( $ids ) ) ) {
			return self::response( 'error', array( 'error_description' => 'Invalid Product ID Data' ) );
		}

		foreach ( $ids as $id ) {
			if ( is_numeric( $id ) ) {
				$product_ids[] = $id;
			}
		}

		// If all product is selected then get all product ids.
		if ( in_array( 'all_products', $ids, true ) ) {
			foreach ( self::get_all_products() as $item_id ) {
				$product_ids[] = $item_id;
			}
		}

		// Get Product Category values in Post ID array.
		$product_categories = array_filter(
			$ids,
			function ( $data ) {
				return strpos( $data, 'cat_' ) !== false;
			}
		);

		// Check if Product Category has been selected.
		if ( is_array( $product_categories ) && count( $product_categories ) > 0 ) {
			foreach ( $product_categories as $category ) {
				$slugs[] = str_replace( 'cat_', '', $category );
			}

			if ( count( $slugs ) > 0 ) {
				$products = get_posts(
					array(
						'posts_per_page' => -1,
						'post_type'      => 'product',
						'tax_query'      => array( // phpcs:ignore
							'relation' => 'AND',
							array(
								'taxonomy' => 'product_cat',
								'field'    => 'slug',
								'terms'    => $slugs,
							),
						),
					)
				);

				foreach ( $products as $key => $product ) {
					$product_ids[] = $product->ID;
				}

				// Return unique Post IDs to remove Products that were selected and are also existing in Product Category.
				$product_ids = array_unique( $product_ids );
			}
		}

		if ( count( $product_ids ) < 20 ) {
			foreach ( $product_ids as $product_id ) {
				self::save_metabox_booking_data(
					array(
						'product_id' => bkap_common::bkap_get_product_id( $product_id ),
						'data'       => $data,
					)
				);
			}

			return self::response( 'success', array( 'message' => __( 'Settings have been saved.', 'woocommerce-booking' ), 'save_settings_as_default' => $save_settings_as_default ) );
		}

		// Product Count is more than 50, so we invoke the background processing action.
		$sent_for_processing        = 0;
		$product_settings           = array();
		$bkap_bulk_booking_settings = bkap_bulk_booking_settings();

		foreach ( $product_ids as $product_id ) {
			$bkap_bulk_booking_settings->push_to_queue(
				array(
					'product_id' => bkap_common::bkap_get_product_id( $product_id ),
					'data'       => $data,
			) ); //phpcs:ignore

			$sent_for_processing++;
		}

		if ( ! $sent_for_processing ) {
			return;
		}

		set_transient( 'bkap_bulk_booking_settings_background_process_running', 0 );
		$bkap_bulk_booking_settings->save()->dispatch();
		return self::response(
			'success',
			array(
				'message'         => __( 'Settings are being saved in the background.', 'woocommerce-booking' ),
				'is_bulk_booking' => true,
			)
		);
	}
}
