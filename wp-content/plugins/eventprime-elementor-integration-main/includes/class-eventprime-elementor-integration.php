<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://eventprime.net
 * @since      1.0.0
 *
 * @package    Eventprime_Elementor_Integration
 * @subpackage Eventprime_Elementor_Integration/includes
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
 * @package    Eventprime_Elementor_Integration
 * @subpackage Eventprime_Elementor_Integration/includes
 * @author     EventPrime <support@metagauss.com>
 */
class Eventprime_Elementor_Integration {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Eventprime_Elementor_Integration_Loader    $loader    Maintains and registers all hooks for the plugin.
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
        protected $minimum_elementor_version;
        protected $minimum_php_version;
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
		if ( defined( 'EVENTPRIME_ELEMENTOR_INTEGRATION_VERSION' ) ) {
			$this->version = EVENTPRIME_ELEMENTOR_INTEGRATION_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'eventprime-elementor-integration';
                $this->minimum_elementor_version = '3.1.0';
		$this->minimum_php_version = '7.4';
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
	 * - Eventprime_Elementor_Integration_Loader. Orchestrates the hooks of the plugin.
	 * - Eventprime_Elementor_Integration_i18n. Defines internationalization functionality.
	 * - Eventprime_Elementor_Integration_Admin. Defines all hooks for the admin area.
	 * - Eventprime_Elementor_Integration_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
                require_once plugin_dir_path( dirname(__FILE__) ) . 'includes/class-eventprime-elementor-integration-activator.php';
                require_once plugin_dir_path( dirname(__FILE__) ) . 'includes/class-eventprime-elementor-integration-deactivator.php';
	
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-eventprime-elementor-integration-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-eventprime-elementor-integration-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-eventprime-elementor-integration-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-eventprime-elementor-integration-public.php';

		$this->loader = new Eventprime_Elementor_Integration_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Eventprime_Elementor_Integration_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Eventprime_Elementor_Integration_i18n();

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

		$plugin_admin = new Eventprime_Elementor_Integration_Admin( $this->get_plugin_name(), $this->get_version() );

		
                $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
                
                // Check if Elementor installed and activated.
		
                $this->loader->add_action( 'admin_notices', $plugin_admin, 'ep_plugin_activation_notice_fun' );
			

		// Check for required Elementor version.
		if (defined('ELEMENTOR_VERSION') && ! version_compare( ELEMENTOR_VERSION,$this->minimum_elementor_version, '>=' ) ) {
			$this->loader->add_action( 'admin_notices', $plugin_admin, 'admin_notice_minimum_elementor_version');
			return;
		}

		// Check for required PHP version.
		if ( version_compare( PHP_VERSION,$this->minimum_php_version, '<' ) ) {
			$this->loader->add_action( 'admin_notices',$plugin_admin, 'admin_notice_minimum_php_version');
			return;
		}
                // Register widgets
                $this->loader->add_action( 'elementor/widgets/widgets_registered',$plugin_admin, 'register_widgets');
                $this->loader->add_action( 'elementor/elements/categories_registered',$plugin_admin, 'add_elementor_widget_categories' );
                    $this->loader->add_action( 'elementor/preview/enqueue_styles', $plugin_admin, 'ep_plugin_editor_styles' );
        
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Eventprime_Elementor_Integration_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

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
	 * @return    Eventprime_Elementor_Integration_Loader    Orchestrates the hooks of the plugin.
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
