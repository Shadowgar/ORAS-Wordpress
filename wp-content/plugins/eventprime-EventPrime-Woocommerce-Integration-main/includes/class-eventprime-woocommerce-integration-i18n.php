<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://https://theeventprime.com
 * @since      1.0.0
 *
 * @package    Eventprime_Woocommerce_Integration
 * @subpackage Eventprime_Woocommerce_Integration/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Eventprime_Woocommerce_Integration
 * @subpackage Eventprime_Woocommerce_Integration/includes
 * @author     EventPrime <support@metagauss.com>
 */
class Eventprime_Woocommerce_Integration_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'eventprime-woocommerce-integration',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
