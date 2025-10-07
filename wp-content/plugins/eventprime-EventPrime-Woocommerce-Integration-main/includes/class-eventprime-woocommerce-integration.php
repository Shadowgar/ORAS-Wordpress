<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://https://theeventprime.com
 * @since      1.0.0
 *
 * @package    Eventprime_Woocommerce_Integration
 * @subpackage Eventprime_Woocommerce_Integration/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Eventprime_Woocommerce_Integration
 * @subpackage Eventprime_Woocommerce_Integration/includes
 * @author     EventPrime <support@metagauss.com>
 */
class Eventprime_Woocommerce_Integration {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Eventprime_Woocommerce_Integration_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */

	public function __construct() {
		if ( defined( 'Eventprime_Woocommerce_Integration_VERSION' ) ) {
			$this->version = Eventprime_Woocommerce_Integration_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'eventprime-woocommerce-integration';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Eventprime_Woocommerce_Integration_Loader. Orchestrates the hooks of the plugin.
	 * - Eventprime_Woocommerce_Integration_i18n. Defines internationalization functionality.
	 * - Eventprime_Woocommerce_Integration_Admin. Defines all hooks for the admin area.
	 * - Eventprime_Woocommerce_Integration_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
            
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-eventprime-woocommerce-integration-activator.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-eventprime-woocommerce-integration-deactivator.php';
	
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-eventprime-woocommerce-integration-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-eventprime-woocommerce-integration-i18n.php';
		
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-eventprime-woocommerce-integration-controller.php';
		

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-eventprime-woocommerce-integration-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-eventprime-woocommerce-integration-public.php';

		$this->loader = new Eventprime_Woocommerce_Integration_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Eventprime_Woocommerce_Integration_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Eventprime_Woocommerce_Integration_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Eventprime_Woocommerce_Integration_Admin( $this->get_plugin_name(), $this->get_version() );
		
		$this->loader->add_action( 'init', $plugin_admin, 'global_woocommerce_integration_initialization_admin' );

		$this->loader->add_action( 'init', $plugin_admin, 'event_woocommerce_integration_register_session' );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action('admin_notices', $plugin_admin, 'ep_plugin_activation_notice_fun');
		$this->loader->add_action('network_admin_notices', $plugin_admin, 'ep_plugin_activation_notice_fun');

		$this->loader->add_filter('ep_add_pages_options', $plugin_admin, 'add_pages_setting_meta', 10, 1);
		/* Views Settings */
		$this->loader->add_filter('ep_admin_settings_tabs', $plugin_admin, 'add_woocommerce_settings_tab', 10, 2);
		$this->loader->add_filter('ep_extensions_settings', $plugin_admin, 'ep_extensions_settings', 10, 1);
		$this->loader->add_action( 'ep_get_extended_settings_tabs_content', $plugin_admin, 'add_woocommerce_settings_tab_content' );
		
		/* Save Settings */
		$this->loader->add_action( 'ep_submit_global_setting', $plugin_admin, 'save_woocommerce_integration_settings' );

		// add global settings options
		$this->loader->add_filter('ep_add_global_setting_options', $plugin_admin, 'ep_add_wci_global_setting_options', 10, 2);

		// ep license module addon
		$this->loader->add_action( 'ep_add_license_setting_blocks', $plugin_admin, 'ep_add_wci_license_setting_block' );
		$this->loader->add_filter('ep_pupulate_license_item_id', $plugin_admin, 'ep_pupulate_wci_license_item_id', 10, 2); // populate license item id.
		$this->loader->add_filter('ep_pupulate_license_item_name', $plugin_admin, 'ep_pupulate_wci_license_item_name', 10, 2); // populate license item name. 
		$this->loader->add_action( 'ep_save_license_settings', $plugin_admin, 'ep_save_wci_license_setting', 10, 2 ); 

		// Meta boxes 
		$this->loader->add_filter('ep_event_meta_tabs', $plugin_admin, 'woocommerce_event_meta_tabs', 1, 1); 
		$this->loader->add_action( 'ep_event_tab_content', $plugin_admin, 'woocommerce_event_tabs_content'); 
		$this->loader->add_action( 'ep_after_save_event_data', $plugin_admin, 'ep_save_woocommerce_products_in_events', 1, 2 ); 
		$this->loader->add_action( 'ep_after_save_event_child_data', $plugin_admin, 'ep_save_woocommerce_products_in_events_child', 1, 2 ); 

		// duplicate event hook
		$this->loader->add_action( 'ep_duplicate_event_extension_data', $plugin_admin, 'ep_duplicate_event_add_extension_data', 1, 2 ); 

		// load product categories
		$this->loader->add_action( 'wp_ajax_admin_woocommerce_product_categories', $plugin_admin, 'load_products_by_categories'); 

		$this->loader->add_action( 'wp_ajax_ep_woocommerce_refresh_booking_page_product_block', $plugin_admin, 'event_woocommerce_integration_refresh_booking_page_product_block'); 
		$this->loader->add_action( 'wp_ajax_nopriv_ep_woocommerce_refresh_booking_page_product_block', $plugin_admin, 'event_woocommerce_integration_refresh_booking_page_product_block');

		// admin booking section
		// register new meta box for booking info on admin end
		$this->loader->add_action( 'ep_bookings_register_meta_boxes_addon', $plugin_admin, 'ep_bookings_woocommerce_product_details_meta_box', 10, 1); 
		
		$this->loader->add_filter( 'ep_update_new_data_before_validating_cart', $plugin_admin, 'ep_wci_update_new_data_before_validating_cart', 10, 2); 

		$this->loader->add_filter( 'ep_extend_paypal_order_items', $plugin_admin, 'ep_extend_paypal_order_items_add_wc_prods', 10, 2);

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Eventprime_Woocommerce_Integration_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'init', $plugin_public, 'global_woocommerce_integration_initialization_public' );

		// display woocommerce products popup on event views ( card, masonry )
		$this->loader->add_action( 'ep_event_view_event_icons', $plugin_public, 'event_woocommerce_integration_event_product_popup_render', 10, 1); 

		// single event page
		$this->loader->add_action( 'ep_single_event_load_icons', $plugin_public, 'event_woocommerce_integration_event_product_detail'); 

		// booking and checkout page
		$this->loader->add_action( 'ep_event_booking_after_ticket_info', $plugin_public, 'event_woocommerce_integration_booking_page_product_block'); 
		$this->loader->add_filter('ep_event_booking_total_price', $plugin_public, 'event_prime_woocommerce_products_price_update', 1, 2); 
        // $this->loader->add_filter('ep_event_booking_total_price_extension', $plugin_public, 'event_prime_woocommerce_products_price_update', 1, 4);

		// checkout page 
		$this->loader->add_action( 'wp_ajax_ep_get_woocommerce_state_by_country_code', $plugin_public, 'event_woocommerce_integration_get_woocommerce_state_by_country_code'); 
		$this->loader->add_action( 'wp_ajax_nopriv_ep_get_woocommerce_state_by_country_code', $plugin_public, 'event_woocommerce_integration_get_woocommerce_state_by_country_code'); 
		$this->loader->add_action( 'ep_front_checkout_data_view', $plugin_public, 'event_woocommerce_integration_front_checkout_data_view'); 

		// update booking total for WC checkout with products 
		$this->loader->add_filter('ep_update_booking_order_info', $plugin_public, 'ep_update_booking_total_for_wc_checkout', 99, 2); 

		// confirm booking add order info
		$this->loader->add_filter('ep_update_booking_order_info', $plugin_public, 'event_woocommerce_integration_add_booking_order_info', 99, 2); 
            
		// add new woocommerce order
		// $this->loader->add_action( 'ep_after_booking_created', $plugin_public, 'event_woocommerce_integration_add_new_woocommerce_order', 99, 2); 
		$this->loader->add_action( 'ep_after_booking_complete', $plugin_public, 'event_woocommerce_integration_add_new_woocommerce_order', 99, 2); 
		
		// user profile
		$this->loader->add_action( 'ep_front_user_booking_details_custom_data', $plugin_public, 'event_woocommerce_integration_front_user_booking_item_details', 10, 1);
		
		// add products to EP WC checkout 
		$this->loader->add_action( 'ep_wc_checkout_after_tickets_added_to_cart', $plugin_public, 'ep_add_products_to_woocoomerce_checkout_extension_cart', 10, 1);

		$this->loader->add_action('ep_booking_detail_show_fee_data',$plugin_public, 'ep_wc_product_on_booking_details_page', 10, 1);

		$this->loader->add_action('ep_admin_booking_detail_after_tickets_subtotal',$plugin_public, 'ep_wc_product_on_admin_booking_details_page', 10, 1);


	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Eventprime_Woocommerce_Integration_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
