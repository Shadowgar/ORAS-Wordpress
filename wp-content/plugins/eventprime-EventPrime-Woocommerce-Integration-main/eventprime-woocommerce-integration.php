<?php

/**
 * @link              https://theeventprime.com
 * @since             1.0.0
 * @package           Eventprime_Woocommerce_Integration
 *
 * @wordpress-plugin
 * Plugin Name:       EventPrime WooCommerce Integration
 * Plugin URI:        https://theeventprime.com
 * Description:       An EventPrime extension that allows you to add optional and/ or mandatory products to your events. You can define quantity or let users chose it themselves. Fully integrates with EventPrime checkout experience and WooCommerce order management.
 * Version:           4.6
 * Author:            EventPrime
 * Author URI:        https://theeventprime.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       eventprime-woocommerce-integration
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
define( 'Eventprime_Woocommerce_Integration_VERSION', '4.6' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-eventprime-woocommerce-integration-activator.php
 */
function activate_eventprime_woocommerce_integration() {
    $ep_wc_integration_activator = new Eventprime_Woocommerce_Integration_Activator();
	$ep_wc_integration_activator->activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-eventprime-woocommerce-integration-deactivator.php
 */
function deactivate_eventprime_woocommerce_integration() {
	$ep_wc_integration_deactivator = new Eventprime_Woocommerce_Integration_Deactivator();
	$ep_wc_integration_deactivator->deactivate();
}

register_activation_hook( __FILE__, 'activate_eventprime_woocommerce_integration' );
register_deactivation_hook( __FILE__, 'deactivate_eventprime_woocommerce_integration' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-eventprime-woocommerce-integration.php';


/* plugin updater start */
if(!class_exists('EventPrime_Plugin_Updater'))
{
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-eventprime-plugin-updater.php';
}

$key = 'Eventprime_Woocommerce_Integration';

$license_status = get_option($key.'_license_status','');
if( ! empty( $license_status ) && $license_status == 'valid' ){
    add_action( 'init','Eventprime_Woocommerce_Integration_plugin_updater' );
}

function Eventprime_Woocommerce_Integration_plugin_updater()
{
    $key = 'Eventprime_Woocommerce_Integration';
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
            'version' => Eventprime_Woocommerce_Integration_VERSION,  // current version number
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
function run_eventprime_woocommerce_integration() {

	$plugin = new Eventprime_Woocommerce_Integration();
	$plugin->run();

}
run_eventprime_woocommerce_integration();
