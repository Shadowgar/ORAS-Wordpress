<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://theeventprime.com
 * @since      1.0.0
 *
 * @package    Eventprime_Event_Tickets
 * @subpackage Eventprime_Event_Tickets/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Eventprime_Event_Tickets
 * @subpackage Eventprime_Event_Tickets/includes
 * @author     EventPrime <support@metagauss.com>
 */
class Eventprime_Event_Tickets_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'eventprime-event-tickets',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
