<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://theeventprime.com
 * @since      1.0.0
 *
 * @package    Eventprime_Event_Tickets
 * @subpackage Eventprime_Event_Tickets/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Eventprime_Event_Tickets
 * @subpackage Eventprime_Event_Tickets/admin
 * @author     EventPrime <support@metagauss.com>
 */
class Eventprime_Event_Tickets_Admin {

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
		 * defined in Eventprime_Event_Tickets_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Eventprime_Event_Tickets_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
                global $post_type ;
		if( $post_type != 'em_ticket' ) {
			return;
		}
		wp_enqueue_style(
			'em-ticket-meta-box-css',
			plugin_dir_url( __FILE__ ) . '/css/ep-admin-ticket-style.css',
			false, $this->version
		);
		
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
		 * defined in Eventprime_Event_Tickets_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Eventprime_Event_Tickets_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		global $post_type ;
		if( $post_type != 'em_ticket' ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_script( 'em-admin-jscolor' );
		
		wp_enqueue_script(
			'em-ticket-meta-box-js',
			plugin_dir_url( __FILE__ ) . '/js/ep-admin-ticket.js',
			false, $this->version
		);
		
		wp_localize_script(
			'em-ticket-meta-box-js', 
			'em_ticket_meta_box_object', 
			array(
				'remove_label' => esc_html__( 'Remove', 'eventprime-event-tickets' ),
			)
		);
	}
        public function ep_plugin_activation_notice_fun() {
            if (!class_exists('Eventprime_Event_Calendar_Management') || !defined('EM_DB_VERSION') || EM_DB_VERSION < '4.0') {
                $this->EventPrime_installation();
            }
        }
        
        public function EventPrime_installation() {
            $plugin_slug = 'eventprime-event-calendar-management';
            $installUrl = admin_url('update.php?action=install-plugin&plugin=' . $plugin_slug);
            $installUrl = wp_nonce_url($installUrl, 'install-plugin_' . $plugin_slug);
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php printf(__("EventPrime Event Tickets work with Eventprime Plugin. You can install it  from <a href='%s'>Here</a>.","eventprime-event-tickets"),$installUrl); ?></p>
            </div>
            <?php
            deactivate_plugins( plugin_basename( plugin_dir_path( __DIR__ ) ) . '/eventprime-event-tickets.php' );
        
        }
        public function register_post_types(){
            if (!class_exists('Eventprime_Event_Calendar_Management')) {
                return;
            }
            $ep_functions = new Eventprime_Basic_Functions();
            $allow_event_tickets = $ep_functions->ep_get_global_settings('allow_event_tickets');
            if ( ! is_blog_installed() || post_type_exists( 'em_ticket' ) || empty( $allow_event_tickets ) ) {
                return;
            }
            register_post_type( 'em_ticket',
                array(
                    'labels' => array(
                        'name'                  => __( 'Tickets','eventprime-event-tickets'),
                        'singular_name'         => __( 'Ticket','eventprime-event-tickets'),
                        'add_new'               => __( 'Add Ticket','eventprime-event-tickets'),
                        'add_new_item'          => __( 'Add New Ticket','eventprime-event-tickets'),
                        'edit'                  => __( 'Edit','eventprime-event-tickets'),
                        'edit_item'             => __( 'Edit Ticket','eventprime-event-tickets'),
                        'new_item'              => __( 'New Ticket','eventprime-event-tickets'),
                        'view'                  => __( 'View Ticket','eventprime-event-tickets'),
                        'view_item'             => __( 'View Ticket','eventprime-event-tickets'),
                        'not_found'             => __( 'No Tickets found','eventprime-event-tickets'),
                        'not_found_in_trash'    => __( 'No Tickets found in trash','eventprime-event-tickets'),
                        'featured_image'        => __( 'Ticket Image','eventprime-event-tickets'),
                        'set_featured_image'    => __( 'Set ticket image','eventprime-event-tickets'),
                        'remove_featured_image' => __( 'Remove ticket image','eventprime-event-tickets'),
                        'use_featured_image'    => __( 'Use as ticket image','eventprime-event-tickets'),
                    ),
                    'description'         => __( 'Here you can add new event tickets.','eventprime-event-tickets'),
                    'public'              => true,
                    'publicly_queryable'  => true,
                    'show_ui'             => true,
                    'show_in_nav_menus'   => true,
                    'show_in_menu'        => true,
                    'has_archive'         => false,
                    'map_meta_cap'        => true,
                    'exclude_from_search' => false,
                    'hierarchical'        => false,
                    'query_var'           => true,
                    'capability_type'     => 'em_ticket', 
                    'supports'            => array( 'title' ),
                    'show_in_menu'        => 'edit.php?post_type=em_event',
                )
            );

            //Add Capability
                global $wp_roles;

                    if ( ! class_exists( 'WP_Roles' ) ) {
                            return;
                    }

                    if ( ! isset( $wp_roles ) ) {
                            $wp_roles = new WP_Roles();
                    }

                    $capability_type = 'em_ticket';
                    $capabilities[ 'em_ticket' ] = array(
                        // Post type.
                        "edit_{$capability_type}",
                        "read_{$capability_type}",
                        "delete_{$capability_type}",
                        "edit_{$capability_type}s",
                        "edit_others_{$capability_type}s",
                        "publish_{$capability_type}s",
                        "read_private_{$capability_type}s",
                        "delete_{$capability_type}s",
                        "delete_private_{$capability_type}s",
                        "delete_published_{$capability_type}s",
                        "delete_others_{$capability_type}s",
                        "edit_private_{$capability_type}s",
                        "edit_published_{$capability_type}s",

                    );
                    foreach ( $capabilities as $cap_group ) {
                            foreach ( $cap_group as $cap ) {
                                    $wp_roles->add_cap( 'administrator', $cap );
                            }
                    }
        }
        
        
        public function add_pages_setting_meta($options){
            $options['allow_event_tickets'] = 1;

            return $options;
        }

        /**
         * EventPrime sms settings tabs
         */
        public function add_ticket_settings_tab( $tabs ){
            $tabs['tickets'] = esc_html__( 'Tickets', 'eventprime-event-tickets' );
            return $tabs; 
        }

        public function ep_extensions_settings($extensions){
            $extensions['tickets'] = array(
                'extension'=>esc_html__('EventPrime Tickets Extension','eventprime-event-calendar-management'),
                'description'=> esc_html__('An EventPrime extension that generate events tickets.','eventprime-event-calendar-management'),
                'url'=> esc_url(add_query_arg(array('tab' => 'tickets')))
            );
            return $extensions;
        }

        public function add_ticket_settings_tab_content( $active_tab ){
            $options = array();
            $settings = new Eventprime_Global_Settings();
            $options['global'] = $settings->ep_get_settings();
            if( $active_tab == 'tickets' ){
                include __DIR__ .'/partials/settings-tab-'.$active_tab.'.php'; 
            }
        }

        public function save_ticket_settings(){
            $admin_notices = new EventM_Admin_Notices;
        
            if( isset( $_POST['em_setting_type'] ) && ! empty( $_POST['em_setting_type'] ) ) {
                $setting_type = sanitize_text_field( $_POST['em_setting_type'] );
                if( $setting_type == 'ticket_settings' ) {
                    $global_settings                                   = new Eventprime_Global_Settings();
                    $global_settings_data                              = $global_settings->ep_get_settings();
                    $global_settings_data->allow_event_tickets        = isset( $_POST['allow_event_tickets'] ) ? 1 : 0;
                    $global_settings->ep_save_settings( $global_settings_data );
                    $admin_notices->ep_add_notice( 'success', esc_html__('Setting saved successfully', 'eventprime-event-tickets' ) );
                    $redirect_url = admin_url( "edit.php?post_type=em_event&page=ep-settings&tab=tickets" );
                    $nonce = wp_create_nonce('ep_settings_tab');
					$redirect_url = add_query_arg( array('tab_nonce'=>$nonce ),$redirect_url);
					wp_redirect($redirect_url);
					exit();
                }
            }
        }
        
        /**
	* Register meta box for performer
	*/
	public function ep_ticket_register_meta_boxes() {
            $basic_functions = new Eventprime_Basic_Functions();
            
		$allow_event_tickets = $basic_functions->ep_get_global_settings('allow_event_tickets');
		if( ! empty( $allow_event_tickets ) ) {
			add_meta_box(
				'ep_ticket_register_meta_boxes',
				esc_html__( 'Ticket Settings', 'eventprime-event-tickets' ),
				array( $this, 'ep_add_ticket_setting_box' ),
				'em_ticket', 'normal', 'high'
			);
		}
	}
	
	/**
	* Add ticket setting details
	*
	* @param $post
	*/
	public function ep_add_ticket_setting_box( $post ): void {
		wp_nonce_field( 'ep_save_ticket_data', 'ep_ticket_meta_nonce' );
		$options = array();
		$settings = new Eventprime_Global_Settings();
                $options['global'] = $settings->ep_get_settings();
		$fonts = array(
			'FreeSerif'     => esc_html__( 'FreeSerif','eventprime-event-tickets' ),
			'Courier'       => esc_html__( 'Courier','eventprime-event-tickets' ),
			'Helvetica'     => esc_html__( 'Helvetica','eventprime-event-tickets' ),
			'Times'         => esc_html__( 'Times','eventprime-event-tickets ')
		);
		include_once __DIR__ .'/partials/meta-box-panel-html.php';
	}
	
	
	/**
	* Save ticket data
	* 
	* @param int 	 $post_id Post ID.
	* @param object $post Post object.
	*/
	public function ep_save_meta_boxes( $post_id, $post ) {
		$post_id = absint( $post_id );
		
		// $post_id and $post are required
		if ( empty( $post_id ) || empty( $post )) {
			return;
		}
		
		// Dont' save meta boxes for revisions or autosaves.
		if( defined('DOING_AUTOSAVE') and DOING_AUTOSAVE ) {
			return false;
		}
		
		// Check the nonce.
		if ( empty( $_POST['ep_ticket_meta_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['ep_ticket_meta_nonce'] ), 'ep_save_ticket_data' ) ) {
			return;
		}
		
		// Check the post being saved == the $post_id to prevent triggering this call for other save_post events.
		if ( empty( $_POST['post_ID'] ) || absint( $_POST['post_ID'] ) !== $post_id ) {
			return;
		}
		
		// Check user has permission to edit.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
                
		$em_font1 = isset($_POST['em_font1']) ? sanitize_text_field($_POST['em_font1']) : '';
		$em_font_color = isset($_POST['em_font_color']) ? sanitize_text_field($_POST['em_font_color']) : '';
		$em_background_color = isset($_POST['em_background_color']) ? sanitize_text_field($_POST['em_background_color']) : '';
		$em_border_color = isset($_POST['em_border_color']) ? sanitize_text_field($_POST['em_border_color']) : '';
		$em_logo = isset($_POST['em_logo']) ? sanitize_text_field($_POST['em_logo']) : '';
		
		update_post_meta( $post_id, 'em_font1', $em_font1 );
		update_post_meta( $post_id, 'em_font_color', $em_font_color );
		update_post_meta( $post_id, 'em_background_color', $em_background_color );
		update_post_meta( $post_id, 'em_border_color', $em_border_color );
		update_post_meta( $post_id, 'em_logo', $em_logo );
		update_post_meta( $post_id, 'em_created_by', $post->post_author );
		
		do_action( 'ep_after_save_ticket_data', $post_id, $post );
	}
	
	/**
	* Remove default meta boxes
	*/
	public function ep_ticket_remove_meta_boxes() {
		remove_meta_box( 'postexcerpt', 'em_ticket', 'normal' );
		remove_meta_box( 'commentsdiv', 'em_ticket', 'normal' );
		remove_meta_box( 'commentstatusdiv', 'em_ticket', 'side' );
		remove_meta_box( 'commentstatusdiv', 'em_ticket', 'normal' );
		remove_meta_box( 'postcustom', 'em_ticket', 'normal' );
		remove_meta_box( 'pageparentdiv', 'em_ticket', 'side' );
	}
	
	public function ep_save_tickets_in_events($post_id, $post){
		$template = isset($_POST['ticket_template']) && !empty($_POST['ticket_template']) ? (int) sanitize_text_field($_POST['ticket_template']) : '';
		update_post_meta( $post_id, 'ticket_template',  $template);
	}
	
	public function ep_save_tickets_in_events_child($post_id, $post){
		$template = isset($_POST['ticket_template']) && !empty($_POST['ticket_template']) ? (int) sanitize_text_field($_POST['ticket_template']) : '';
		update_post_meta( $post_id, 'ticket_template', (int) sanitize_text_field($_POST['ticket_template']) );
	}
        
	public function remove_permalink($actions ){
		if( get_post_type() === 'em_ticket' ) {
			unset( $actions['view'] );
		}
		return $actions;
	}
        
        /**
        * Add custom script on the admin event page for seat plan meta box
        */
        public function ep_event_ticket_add_admin_event_custom_script() {
            wp_enqueue_script(
                'ep-event-ticket-admin-event-script',
                plugin_dir_url( __FILE__ )  . '/js/ep-admin-ticket-event-meta-box.js',
                array( 'jquery' ), $this->version
            );
        }

        // show ticket template option in the event tickets modal
        public function ep_event_ticket_template_options() {
            $basic_functions = new Eventprime_Basic_Functions();
            $ep_dbhandler = new EP_DBhandler();
                    $allow_event_tickets = $basic_functions->ep_get_global_settings('allow_event_tickets');
                    if( empty( $allow_event_tickets ) ) return;
                    $all_tickets = $ep_dbhandler->eventprime_get_all_posts('em_ticket');
                    if( ! empty( $all_tickets ) ) {?>
                            <option value="0"><?php esc_html_e( '-- Select Ticket Template --', 'eventprime-event-tickets' );?></option><?php
                            foreach( $all_tickets as $ticket ) {
                                    $ticket_id = $ticket->ID;
                                    $ticket_title = $ticket->post_title;?>
                                    <option value="<?php echo esc_attr( $ticket_id );?>"><?php echo esc_attr( $ticket_title );?></option><?php
                            }
                    }
        }

}
