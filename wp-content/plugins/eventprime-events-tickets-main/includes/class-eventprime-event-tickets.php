<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://theeventprime.com
 * @since      1.0.0
 *
 * @package    Eventprime_Event_Tickets
 * @subpackage Eventprime_Event_Tickets/includes
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
 * @package    Eventprime_Event_Tickets
 * @subpackage Eventprime_Event_Tickets/includes
 * @author     EventPrime <support@metagauss.com>
 */
class Eventprime_Event_Tickets {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Eventprime_Event_Tickets_Loader    $loader    Maintains and registers all hooks for the plugin.
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
		if ( defined( 'EVENTPRIME_EVENT_TICKETS_VERSION' ) ) {
			$this->version = EVENTPRIME_EVENT_TICKETS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'eventprime-event-tickets';

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
	 * - Eventprime_Event_Tickets_Loader. Orchestrates the hooks of the plugin.
	 * - Eventprime_Event_Tickets_i18n. Defines internationalization functionality.
	 * - Eventprime_Event_Tickets_Admin. Defines all hooks for the admin area.
	 * - Eventprime_Event_Tickets_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
                
                require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-eventprime-event-tickets-activator.php';
                require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-eventprime-event-tickets-deactivator.php';
	
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-eventprime-event-tickets-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-eventprime-event-tickets-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-eventprime-event-tickets-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-eventprime-event-tickets-public.php';
                require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-eventprime-event-ticket-controller.php';
                
		$this->loader = new Eventprime_Event_Tickets_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Eventprime_Event_Tickets_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Eventprime_Event_Tickets_i18n();

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

		$plugin_admin = new Eventprime_Event_Tickets_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
                
                $this->loader->add_action('admin_notices', $plugin_admin, 'ep_plugin_activation_notice_fun');
                $this->loader->add_action('network_admin_notices', $plugin_admin, 'ep_plugin_activation_notice_fun');
                
                
                $this->loader->add_action( 'init', $plugin_admin, 'register_post_types' , 5 );
                $this->loader->add_filter( 'ep_add_pages_options', $plugin_admin ,'add_pages_setting_meta' ,10,1);
                // Views Settings
                $this->loader->add_filter( 'ep_admin_settings_tabs', $plugin_admin ,'add_ticket_settings_tab', 10, 2 );
                $this->loader->add_filter( 'ep_get_settings_tab_content', $plugin_admin ,'add_ticket_settings_tab_content', 10, 2 );
                $this->loader->add_action( 'ep_get_extended_settings_tabs_content', $plugin_admin, 'add_ticket_settings_tab_content'  );
                $this->loader->add_filter( 'ep_extensions_settings', $plugin_admin ,'ep_extensions_settings', 10, 1 ); 
                // Save Settings
                $this->loader->add_action( 'ep_submit_global_setting', $plugin_admin , 'save_ticket_settings' );
                
                $this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'ep_ticket_remove_meta_boxes', 10 );
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'ep_ticket_register_meta_boxes', 1 );
		$this->loader->add_action( 'save_post', $plugin_admin, 'ep_save_meta_boxes' , 1, 2 );
		$this->loader->add_filter( 'post_row_actions', $plugin_admin, 'remove_permalink', 10, 1 );
                
                //License
                $this->loader->add_action( 'ep_event_enqueue_custom_scripts', $plugin_admin, 'ep_event_ticket_add_admin_event_custom_script' );
		$this->loader->add_action( 'ep_event_get_ticket_template_options', $plugin_admin, 'ep_event_ticket_template_options',10 );
	
		
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Eventprime_Event_Tickets_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
                
                $this->loader->add_action( 'ep_bookingh_detail_enqueue_custom_scripts', $plugin_public, 'ep_event_ticket_add_custom_script' );
                $this->loader->add_action( 'ep_booking_detail_attendee_table_header', $plugin_public, 'ep_event_ticket_booking_detail_attendee_table_header', 10 );
                $this->loader->add_action( 'ep_booking_detail_attendee_table_data', $plugin_public, 'ep_event_ticket_booking_detail_attendee_table_data', 10, 3 );

                $this->loader->add_filter( 'event_magic_booking_confirmed_notification_attachments', $plugin_public, 'ep_attach_ticket_to_booking_conf_mail', 99, 2 );

                $this->loader->add_action( 'wp_ajax_ep_event_booking_print_ticket', $plugin_public, 'ep_event_booking_print_ticket' );
                $this->loader->add_action( 'wp_ajax_nopriv_ep_event_booking_print_ticket', $plugin_public, 'ep_event_booking_print_ticket');
                $this->loader->add_action( 'wp_ajax_ep_print_event_ticket', $plugin_public, 'ep_print_event_ticket' );
                $this->loader->add_action( 'wp_ajax_nopriv_ep_print_event_ticket', $plugin_public, 'ep_print_event_ticket' );
                $this->loader->add_action( 'wp_ajax_ep_event_booking_share_ticket', $plugin_public, 'ep_event_booking_share_ticket' );
                $this->loader->add_action( 'wp_ajax_nopriv_ep_event_booking_share_ticket', $plugin_public, 'ep_event_booking_share_ticket' );

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
	 * @return    Eventprime_Event_Tickets_Loader    Orchestrates the hooks of the plugin.
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
