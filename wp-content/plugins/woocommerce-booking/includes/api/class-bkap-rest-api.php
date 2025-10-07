<?php
/**
 * WooCommerce Bookings API
 *
 * @package WooCommerce-Booking\Rest-API
 */

/**
 * API class which registers all the booking namespaces.
 */
class BKAP_REST_API {

	/**
	 * Construct.
	 */
	public function __construct() {
		add_filter( 'woocommerce_rest_api_get_rest_namespaces', array( $this, 'bkap_rest_api_get_rest_namespaces' ) );
	}

	/**
	 * Add API namespaces - registering booking namespaces.
	 *
	 * @param array $controllers Array of WooCommerce REST API Controllers Class Names.
	 * @since 6.4.0
	 * @return array List of Namespaces and Main controller classes.
	 */
	public function bkap_rest_api_get_rest_namespaces( $controllers ) {

		$controllers['wc/v3']['bkap/bookings']  = 'BKAP_REST_API_BOOKINGS_CONTROLLER';
		$controllers['wc/v3']['bkap/products']  = 'BKAP_REST_API_BOOKING_PRODUCTS_CONTROLLER';
		$controllers['wc/v3']['bkap/resources'] = 'BKAP_REST_API_BOOKING_RESOURCES_CONTROLLER';

		return $controllers;
	}
}
