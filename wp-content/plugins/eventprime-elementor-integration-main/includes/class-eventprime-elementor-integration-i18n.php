<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://eventprime.net
 * @since      1.0.0
 *
 * @package    Eventprime_Elementor_Integration
 * @subpackage Eventprime_Elementor_Integration/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Eventprime_Elementor_Integration
 * @subpackage Eventprime_Elementor_Integration/includes
 * @author     EventPrime <support@metagauss.com>
 */
class Eventprime_Elementor_Integration_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'eventprime-elementor-integration',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
