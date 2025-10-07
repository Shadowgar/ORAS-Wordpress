<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://theeventprime.com
 * @since             1.0.0
 * @package           Eventprime_Event_Tickets
 *
 * @wordpress-plugin
 * Plugin Name:       EventPrime Event Tickets
 * Plugin URI:        https://theeventprime.com
 * Description:       An EventPrime extension that allows you to create and send event tickets to your attendees.
 * Version:           4.3
 * Author:            EventPrime
 * Author URI:        https://theeventprime.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       eventprime-event-tickets
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'EVENTPRIME_EVENT_TICKETS_VERSION', '4.3' );

if( ! defined( 'EP_EVENT_TICKETS_FILE' ) ) {
    define( 'EP_EVENT_TICKETS_FILE', __FILE__ );
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-eventprime-event-tickets-activator.php
 */
function activate_eventprime_event_tickets() {
	$ticket_activator = new Eventprime_Event_Tickets_Activator();
        $ticket_activator->activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-eventprime-event-tickets-deactivator.php
 */
function deactivate_eventprime_event_tickets() {
	$ticket_deactivator = new Eventprime_Event_Tickets_Deactivator();
        $ticket_deactivator->deactivate();
}

register_activation_hook( __FILE__, 'activate_eventprime_event_tickets' );
register_deactivation_hook( __FILE__, 'deactivate_eventprime_event_tickets' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-eventprime-event-tickets.php';
/* plugin updater start */

if(!class_exists('EventPrime_Plugin_Updater'))
{
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-eventprime-plugin-updater.php';
}

$key = 'Eventprime_Event_Tickets';

$license_status = get_option($key.'_license_status','');
if( ! empty( $license_status ) && $license_status == 'valid' ){
    add_action( 'init','Eventprime_Event_Tickets_plugin_updater' );
}

function Eventprime_Event_Tickets_plugin_updater()
{
    $key = 'Eventprime_Event_Tickets';
    $doing_cron = defined( 'DOING_CRON' ) && DOING_CRON;
    if ( ! current_user_can( 'manage_options' ) && ! $doing_cron ) {
        return;
    }

    // retrieve our license key from the global settings
    $license_key = get_option($key.'_license_key');
    $item_id = get_option($key.'_item_id');
    $site_url = 'https://theeventprime.com/';
    // setup the updater
    $eventprime_updater = new Eventprime_Plugin_Updater(
        $site_url,
        __FILE__,
        array(
            'version' => EVENTPRIME_EVENT_TICKETS_VERSION,  // current version number
            'license' => $license_key,  // license key
            'item_id' => $item_id,       // ID of the product
            'author'  => 'EventPrime', // author of this plugin
            'beta'    => false,
        ),
        $key
    );
}

/* plugin updater end. */

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_eventprime_event_tickets() {

	$plugin = new Eventprime_Event_Tickets();
	$plugin->run();

}
run_eventprime_event_tickets();
