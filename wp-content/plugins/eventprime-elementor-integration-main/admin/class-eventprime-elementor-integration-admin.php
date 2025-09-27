<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://eventprime.net
 * @since      1.0.0
 *
 * @package    Eventprime_Elementor_Integration
 * @subpackage Eventprime_Elementor_Integration/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Eventprime_Elementor_Integration
 * @subpackage Eventprime_Elementor_Integration/admin
 * @author     EventPrime <support@metagauss.com>
 */
class Eventprime_Elementor_Integration_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Eventprime_Elementor_Integration_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Eventprime_Elementor_Integration_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( dirname(__FILE__) ) . 'admin/css/eventprime-elementor-integration-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Eventprime_Elementor_Integration_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Eventprime_Elementor_Integration_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( dirname(__FILE__) ) . 'admin/js/eventprime-elementor-integration-admin.js', array( 'jquery' ), $this->version, false );

	}
        
        public function ep_plugin_activation_notice_fun() {
            if (!class_exists('Eventprime_Event_Calendar_Management') || !defined('EM_DB_VERSION') || EM_DB_VERSION < '4.0') {
                $this->EventPrime_installation();
            }
            if(! did_action( 'elementor/loaded' )){
                $this->Elementor_installation();
            }
        }
        
        public function EventPrime_installation() {
            $plugin_slug = 'eventprime-event-calendar-management';
            $installUrl = admin_url('update.php?action=install-plugin&plugin=' . $plugin_slug);
            $installUrl = wp_nonce_url($installUrl, 'install-plugin_' . $plugin_slug);
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php printf(__("EventPrime Elementor Integration work with Eventprime Plugin. You can install it  from <a href='%s'>Here</a>.","eventprime-elementor-integration"),$installUrl); ?></p>
            </div>
            <?php
            deactivate_plugins( plugin_basename( plugin_dir_path( __DIR__ ) ) . '/eventprime-elementor-integration.php' );
        }
        
        public function Elementor_installation() {
            $plugin_slug = 'elementor';
            $installUrl = admin_url('update.php?action=install-plugin&plugin=' . $plugin_slug);
            $installUrl = wp_nonce_url($installUrl, 'install-plugin_' . $plugin_slug);
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php printf(__("EventPrime Elementor Integration work with Elementor Plugin. You can install it  from <a href='%s'>Here</a>.","eventprime-elementor-integration"),$installUrl); ?></p>
            </div>
            <?php
            deactivate_plugins( plugin_basename( plugin_dir_path( __DIR__ ) ) . '/eventprime-elementor-integration.php' );
        }
	public function admin_notice_minimum_elementor_version(){
		// $plugin = trim(basename(plugin_dir_path(dirname( __FILE__))).'/eventprime-elementor-widget.php');
        //         deactivate_plugins($plugin);

        ?>
            <div class="notice notice-success is-dismissible">
                <p><?php echo sprintf(__( "Elementor Plugin Version is less than required version. Please update your elementor version.", 'eventprime-elementor-integration' ) ); ?></p>
            </div>
        <?php
	}
        
        public function admin_notice_minimum_php_version() {
                    deactivate_plugins( plugin_basename( ELEMENTOR_AWESOMESAUCE ) );
                    return sprintf(
                            wp_kses(
                                    '<div class="notice notice-warning is-dismissible"><p><strong>"%1$s"</strong> requires <strong>"%2$s"</strong> version %3$s or greater.</p></div>',
                                    array(
                                            'div' => array(
                                                    'class'  => array(),
                                                    'p'      => array(),
                                                    'strong' => array(),
                                            ),
                                    )
                            ),
                            'EventPrime Elementor Widget',
                            'Elementor',
                            $this->minimum_php_version
                    );
            }

            public function register_widgets(){
                    $global_function =  new Eventprime_Basic_Functions();
                    $ext_list = $global_function->ep_list_all_exts();

                    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/class-ep-elementor-all-events-widget.php'; 
                \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor_EP_All_Events_Widget() );

                    // require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/class-ep-elementor-widget.php'; 
                // \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor_EP_Widget() );

                    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/class-ep-elementor-eventtypes-widget.php'; 
                \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor_EP_Eventtypes_Widget() );

                    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/class-ep-elementor-venues-widget.php'; 
                \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor_EP_Venues_Widget() );

                    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/class-ep-elementor-organizers-widget.php'; 
                \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor_EP_Organizers_Widget() );

                    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/class-ep-elementor-performers-widget.php'; 
                \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor_EP_Performers_Widget() );

                    if( in_array( "Event Sponsors", $ext_list ) ) {
                            if(class_exists('Eventprime_Event_Sponsor')){
                                    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/class-ep-elementor-sponsors-widget.php'; 
                                    \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor_EP_Sponsors_Widget() );
                            }
                    }

                    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/class-ep-elementor-single-eventtype-widget.php'; 
                \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor_EP_SingleEventtype_Widget() );

                    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/class-ep-elementor-single-venue-widget.php'; 
                \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor_EP_SingleVenue_Widget() );

                    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/class-ep-elementor-single-organizer-widget.php'; 
                \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor_EP_SingleOrganizer_Widget() );

                    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/class-ep-elementor-single-performer-widget.php'; 
                \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor_EP_SinglePerformer_Widget() );

                    if( in_array( "Event Sponsors", $ext_list ) ) {
                            if(class_exists('Eventprime_Event_Sponsor')){
                                    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/class-ep-elementor-single-sponsor-widget.php'; 
                                    \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor_EP_SingleSponsor_Widget() );
                            }
                    }

                    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/class-ep-elementor-fes-form-widget.php'; 
                \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor_EP_Fes_Form_Widget() );

                    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/class-ep-elementor-user-profile-widget.php'; 
                \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor_EP_User_Profile_Widget() );

                    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/class-ep-elementor-user-login-widget.php'; 
                \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor_EP_User_Login_Widget() );

                    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/class-ep-elementor-user-registration-widget.php'; 
                \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor_EP_User_Registration_Widget() );

        }


        public function add_elementor_widget_categories( $elements_manager ){
            $elements_manager->add_category(
                            'eventprime',
                            [
                                    'title' => __( 'EventPrime', 'eventprime-elementor-integration' ),
                                    'icon' => 'fa fa-plug',
                            ]
                    );
        }

        public function ep_plugin_editor_styles(){
            wp_register_style(
			'ep-user-select2-css',
			plugin_dir_url(EP_PLUGIN_FILE) . 'public/css/select2.min.css',
			false, $this->version
		);
	
            wp_register_style(
                'ep-user-views-custom-css',
                plugin_dir_url(EP_PLUGIN_FILE) . 'public/css/ep-user-views.css',
                false, $this->version
            );

            wp_register_script(
                'ep-user-select2-js',
                plugin_dir_url(EP_PLUGIN_FILE) . 'public/js/select2.full.min.js',
                array( 'jquery' ), $this->version
                    );
            wp_register_script(
                'ep-user-views-js',
                plugin_dir_url(EP_PLUGIN_FILE) . 'public/js/ep-user-custom.js',
                array( 'jquery' ), $this->version
            );

            wp_enqueue_style('ep-public-css',plugin_dir_url(EP_PLUGIN_FILE) . 'public/css/em-front-common-utility.css',false, $this->version);
            wp_enqueue_style('ep-material-fonts',plugin_dir_url(EP_PLUGIN_FILE) . 'public/css/ep-material-fonts-icon.css',array(), $this->version);
            wp_enqueue_style('ep-toast-css',plugin_dir_url(EP_PLUGIN_FILE) . 'public/css/jquery.toast.min.css',false, $this->version);
            wp_enqueue_script('ep-toast-js',plugin_dir_url(EP_PLUGIN_FILE) . 'public/js/jquery.toast.min.js',array('jquery'), $this->version);
            wp_enqueue_script('ep-toast-message-js',plugin_dir_url(EP_PLUGIN_FILE) . 'public/js/toast-message.js',array('jquery'), $this->version);

            $ep_functions = new Eventprime_Basic_Functions;
            wp_enqueue_style('em-front-common-utility', plugin_dir_url( EP_PLUGIN_FILE ) . 'public/css/em-front-common-utility.css', array(), $this->version, 'all' );
            wp_enqueue_script('ep-common-script', plugin_dir_url(EP_PLUGIN_FILE) . 'public/js/ep-common-script.js', array('jquery'), $this->version);
                // localized global settings
                $global_settings = $ep_functions->ep_get_global_settings();
                $currency_symbol = $ep_functions->ep_currency_symbol();
                $datepicker_format = $ep_functions->ep_get_datepicker_format( 2 );
                wp_localize_script(
                'ep-common-script', 
                'eventprime', 
                array(
                    'global_settings'      => $global_settings,
                    'currency_symbol'      => $currency_symbol,
                    'ajaxurl'              => admin_url('admin-ajax.php'),
                    'trans_obj'            => $ep_functions->ep_define_common_field_errors(),
                    'event_wishlist_nonce' => wp_create_nonce( 'event-wishlist-action-nonce' ),
                    'security_nonce_failed'=> esc_html__( 'Security check failed. Please refresh the page and try again later.', 'eventprime-event-calendar-management' ),
                    'datepicker_format'    => $datepicker_format
                )
            );



            wp_register_style(
                'ep-responsive-slides-css',
                 plugin_dir_url(EP_PLUGIN_FILE) . 'public/css/responsiveslides.css',
                false, $this->version
            );
            wp_register_script(
                'ep-responsive-slides-js',
                 plugin_dir_url(EP_PLUGIN_FILE) . 'public/js/responsiveslides.min.js',
                array( 'jquery' ), $this->version
            );
        }
}
