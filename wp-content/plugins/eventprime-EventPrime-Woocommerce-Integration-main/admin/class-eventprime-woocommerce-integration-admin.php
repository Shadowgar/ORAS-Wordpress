<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://https://theeventprime.com
 * @since      1.0.0
 *
 * @package    Eventprime_Woocommerce_Integration
 * @subpackage Eventprime_Woocommerce_Integration/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Eventprime_Woocommerce_Integration
 * @subpackage Eventprime_Woocommerce_Integration/admin
 * @author     EventPrime <support@metagauss.com>
 */
class Eventprime_Woocommerce_Integration_Admin {

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

	public $woocommerce_active = false;
    public $allow_woocommerce_integration = 0;

	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	public function global_woocommerce_integration_initialization_admin() {
		if ( ! class_exists( 'Eventprime_Basic_Functions' ) ) {
			return; 
		}
		include_once(ABSPATH.'wp-admin/includes/plugin.php');
		if ( is_plugin_active('woocommerce/woocommerce.php') ) {

			$ep_functions = new Eventprime_Basic_Functions;

            $this->woocommerce_active = true;

            add_action('wp_ajax_admin_woocommerce_product_categories', array($this, 'load_products_by_categories'));

            // check if woocommerce extension is enabled
            $allow_woocommerce_integration = $ep_functions->ep_get_global_settings( 'allow_woocommerce_integration' );
            if ($allow_woocommerce_integration == 1) {
                $this->allow_woocommerce_integration = 1;
            }
        } else {
            $this->woocommerce_active = false;
			add_action( 'admin_notices', array( $this, 'ep_woocommerce_not_active' ) );
			// wp_die(); 
			return; 
        }

	}
	
	public function ep_woocommerce_not_active(){ ?>
		<div class="notice notice-success is-dismissible">
			<p><?php _e( 'EventPrime Woocommerce Integration Extension won\'t work as Woocommerce plugin is not active/installed.', 'eventprime-woocommerce-integration' ); ?></p>
		</div><?php
		deactivate_plugins( plugin_basename(plugin_dir_path( __DIR__  ) . 'eventprime-woocommerce-integration.php') );
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_register_style(
			'ep-admin-woocommerce-integration-css',
			plugin_dir_url(__FILE__). 'css/ep-admin-woocommerce-integration-style.css',
			array( 'em-meta-box-admin-custom-css' ), $this->version
		);
            
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_style('em-admin-select2-css');
		wp_enqueue_script('em-admin-select2-js');
		wp_register_script(
			'em-admin-woocommerce-integration-js',
			plugin_dir_url(__FILE__) . 'js/ep-admin-woocommerce-integration.js',
			array('jquery'), $this->version
		);

	}

	public function ep_plugin_activation_notice_fun() {
        if (!class_exists('Eventprime_Event_Calendar_Management')) {
            $this->EventPrime_installation();
        }
    }

    public function EventPrime_installation() {
        $plugin_slug = 'eventprime-event-calendar-management';
        $installUrl = admin_url('update.php?action=install-plugin&plugin=' . $plugin_slug);
        $installUrl = wp_nonce_url($installUrl, 'install-plugin_' . $plugin_slug);
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php printf(__("EventPrime Woocommerce Integration work with Eventprime Plugin. You can install it  from <a href='%s'>Here</a>.","eventprime-event-tickets"),plugin_basename ( plugin_dir_path( __DIR__ ) ), $installUrl); ?></p>
        </div>
        <?php
        deactivate_plugins( plugin_basename( plugin_dir_path( __DIR__ ) ) . '/eventprime-woocommerce-integration.php' );
    }

	public function add_pages_setting_meta($options){
		$options['allow_woocommerce_integration'] = 0;
		return $options;
	}
	
	/**
	 * EventPrime Woocommerce Integration settings tabs
	 */
	public function add_woocommerce_settings_tab( $tabs ){
		$tabs['wc-integration'] = esc_html__( 'Woocommerce Integration', 'eventprime-event-calendar-management' );
		return $tabs; 
	}
	
	public function ep_extensions_settings($extensions){
		$extensions['wc-integration'] = array(
			'extension'=>esc_html__('EventPrime Woocommerce Integration','eventprime-event-calendar-management'),
			'description'=> esc_html__('An EventPrime extension that allows you to add optional and/ or mandatory products to your events. You can define quantity or let users chose it themselves. Fully integrates with EventPrime checkout experience and WooCommerce order management.','eventprime-event-calendar-management'),
			'url'=> esc_url(add_query_arg(array('tab' => 'wc-integration')))
		);
		return $extensions;
	}

	public function add_woocommerce_settings_tab_content( $active_tab ){
		$options = array();
		$settings = new Eventprime_Global_Settings();
		$options['global'] = $settings->ep_get_settings();
		if( $active_tab == 'wc-integration' ){
			include __DIR__ .'/partials/settings/settings-tab-'.$active_tab.'.php'; 
		}
	}
	
	public function save_woocommerce_integration_settings(){
		if( isset( $_POST['em_setting_type'] ) && ! empty( $_POST['em_setting_type'] ) ) {
			$setting_type = sanitize_text_field( $_POST['em_setting_type'] );
			
			/* save woocommerce integration settings */
			if( $setting_type == 'wc_integration' ) {
				$global_settings                                       = new Eventprime_Global_Settings();
				$global_settings_data                                  = $global_settings->ep_get_settings();
				$global_settings_data->allow_woocommerce_integration   = isset( $_POST['allow_woocommerce_integration'] ) ? 1 : 0;
			  
				$global_settings->ep_save_settings( $global_settings_data );

				$admin_notices = new EventM_Admin_Notices(); 
				$admin_notices->ep_add_notice( 'success', esc_html__('Setting saved successfully', 'eventprime-event-calendar-management' ) );
				$redirect_url = admin_url( "edit.php?post_type=em_event&page=ep-settings&tab=wc-integration" );
				$nonce = wp_create_nonce('ep_settings_tab');
            	$redirect_url = add_query_arg( array('tab_nonce'=>$nonce ),$redirect_url);
				wp_safe_redirect( $redirect_url );
				exit();
			}
		}
	}

	/**
	* Add twilio options in global settings object
	*/
	public function ep_add_wci_global_setting_options( $settings, $options ) {
		if( ! empty( $options ) ) {
			// global settings option for license settings
			$settings->ep_wci_item_id          = 526;
			$settings->ep_wci_item_name        = 'WooCommerce Integration';
			$settings->ep_wci_license_key      = ( property_exists( $options, 'ep_wci_license_key' ) ) ? $options->ep_wci_license_key : '';
			$settings->ep_wci_license_status   = ( property_exists( $options, 'ep_wci_license_status' ) ) ? $options->ep_wci_license_status : '';
			$settings->ep_wci_license_response = ( property_exists( $options, 'ep_wci_license_response' ) ) ? $options->ep_wci_license_response : '';
		}
		return $settings;
	}

	public function ep_add_wci_license_setting_block( $options ){ 
		?>
			<tr valign="top" class="ep_wci">
				<td><?php esc_html_e( 'WooCommerce Integration', 'eventprime-woocommerce-integration' );?></td>
				<td><input id="ep_wci_license_key" name="ep_wci_license_key" type="text" class="regular-text ep-box-wrap ep-license-block" data-prefix="ep_wci" value="<?php esc_attr_e( ( isset( $options->ep_wci_license_key ) && ! empty( $options->ep_wci_license_key ) ) ? $options->ep_wci_license_key : '' ); ?>" placeholder="<?php esc_html_e( 'Please Enter License Key', 'eventprime-woocommerce-integration' );?>" /></td>
				<td>         
					<span class="license-expire-date" style="padding-bottom:2rem;" >
						<?php
						if ( ! empty( $options->ep_wci_license_response->expires ) && ! empty( $options->ep_wci_license_status ) && $options->ep_wci_license_status == 'valid' ) {
							if( $options->ep_wci_license_response->expires == 'lifetime' ){
								esc_html_e( 'Your License key is activated for lifetime', 'eventprime-woocommerce-integration' );
							}else{
								echo sprintf( __('Your License Key expires on %s', 'eventprime-woocommerce-integration' ), date( 'F d, Y', strtotime( $options->ep_wci_license_response->expires ) ) );
							}
						} else {
							$expire_date = '';
						}
						?>
					</span>
				</td>
				<td>
					<span class="ep_wci-license-status-block">
						<?php if ( isset( $options->ep_wci_license_key ) && ! empty( $options->ep_wci_license_key )) { ?>
							<?php if ( isset( $options->ep_wci_license_status ) && $options->ep_wci_license_status !== false && $options->ep_wci_license_status == 'valid' ) { ?>
								<button type="button" class="button action ep-my-2 ep_license_deactivate" name="ep_wci_license_deactivate" id="ep_wci_license_deactivate" data-prefix="ep_wci" value="<?php esc_html_e( 'Deactivate License', 'eventprime-woocommerce-integration' );?>"><?php esc_html_e( 'Deactivate License', 'eventprime-woocommerce-integration' );?></button>
							<?php } elseif( ! empty( $options->ep_wci_license_status ) && $options->ep_wci_license_status == 'invalid' ) { ?>
								<button type="button" class="button action ep-my-2 ep_license_activate" name="ep_wci_license_activate" id="ep_wci_license_activate" data-prefix="ep_wci" value="<?php esc_html_e( 'Activate License', 'eventprime-woocommerce-integration' );?>"><?php esc_html_e( 'Activate License', 'eventprime-woocommerce-integration' );?></button>
							<?php }else{ ?>
								<button type="button" class="button action ep-my-2 ep_license_activate" name="ep_wci_license_activate" id="ep_wci_license_activate" data-prefix="ep_wci" value="<?php esc_html_e( 'Activate License', 'eventprime-woocommerce-integration' );?>" style="<?php if ( empty( $options->ep_wci_license_key ) ){ echo 'display:none'; } ?>"><?php esc_html_e( 'Activate License', 'eventprime-woocommerce-integration' );?></button>
							<?php } }else{ ?>
								<button type="button" class="button action ep-my-2 ep_license_activate" name="ep_wci_license_activate" id="ep_wci_license_activate" data-prefix="ep_wci" value="<?php esc_html_e( 'Activate License', 'eventprime-woocommerce-integration' );?>" style="display:none;"><?php esc_html_e( 'Activate License', 'eventprime-woocommerce-integration' );?></button>
							<?php } ?>
					</span>
				</td>
			</tr>
		<?php
	}

	public function ep_pupulate_wci_license_item_id( $item_id, $form_data ){
		if( isset( $form_data['ep_license_type'] ) && $form_data['ep_license_type'] == 'ep_wci' ){
			$global_settings = new Eventprime_Global_Settings();			
			$options = $global_settings->ep_get_settings();
			$item_id  = ( isset(  $options->ep_wci_item_id ) && ! empty( $options->ep_wci_item_id ) ) ? $options->ep_wci_item_id : '';     
		}
		return $item_id; 
	}

	public function ep_pupulate_wci_license_item_name( $item_name, $form_data ){
		if( isset( $form_data['ep_license_type'] ) && $form_data['ep_license_type'] == 'ep_wci' ){
			$global_settings = new Eventprime_Global_Settings();			
			$options = $global_settings->ep_get_settings();
			$item_name  = ( isset( $options->ep_wci_item_name ) && ! empty( $options->ep_wci_item_name ) ) ? $options->ep_wci_item_name : '';    
		}
		return $item_name;
	}

	public function ep_save_wci_license_setting( $form_data, $license_data ){
		if( isset( $form_data['ep_license_type'] ) && $form_data['ep_license_type'] == 'ep_wci' && ! empty( $license_data ) ){

			$global_settings = new Eventprime_Global_Settings();
			$options = $global_settings->ep_get_settings();
			// $license_data->license will be either "valid" or "invalid"
			$options->ep_wci_license_key  = ( isset( $form_data['ep_license_key'] ) && ! empty( $form_data['ep_license_key'] )  && ( $license_data->license == 'valid' || $license_data->license = 'deactivated' ) ) ? $form_data['ep_license_key'] : '';
			$options->ep_wci_license_status  = ( isset( $license_data->license ) && ! empty( $license_data->license ) ) ? $license_data->license : '';
			$options->ep_wci_license_response  = ( isset( $license_data ) && ! empty( $license_data ) ) ? $license_data : '';
			$global_settings->ep_save_settings( $options );
		
		}
	}

	/**
	* Enqueue meta box scripts
	*/
	
	public function enqueue_admin_meta_box_scripts() {
		// wp_register_style(
		// 	'ep-admin-woocommerce-integration-css',
		// 	plugin_dir_path(__FILE__) . 'css/ep-admin-woocommerce-integration-style.css',
		// 	array( 'em-meta-box-admin-custom-css' ), $this->version
		// );
		// wp_register_script(
		// 	'em-admin-woocommerce-integration-js',
		// 	plugin_dir_path(__FILE__) . 'js/ep-admin-woocommerce-integration.js',
		// 	array('jquery'), $this->version
		// );
	}
        
	/*
		* Add Mailpoet to event setting
		*/
	public function woocommerce_event_meta_tabs( $tabs ){
		$ep_functions = new Eventprime_Basic_Functions();
		$allow_woocommerce_integration = $ep_functions->ep_get_global_settings( 'allow_woocommerce_integration' );
		if( isset( $allow_woocommerce_integration ) && $allow_woocommerce_integration == 1 ){
			$tabs['woocommerce'] = array(
				'label'      => esc_html__( 'Woocommerce', 'eventprime-woocommerce-integration' ),
				'target'     => 'ep_event_woocommerce_data',
				'class'      => array( 'ep_event_woocommerce' ),
				'priority'   => 120,
			);
		}
		return $tabs;
	}
	
	/*
		* Add WC setting html
		*/
	public function woocommerce_event_tabs_content(){
		global $post, $post_id;
		$ep_functions = new Eventprime_Basic_Functions();
		$woocommerce_integration = new EP_Woocommerce_Integtation_Controller_List();
		// categories
		$categories = get_terms( ['taxonomy' => 'product_cat', 'hide_empty' => false] );
		// tags
		$tags = get_terms( ['taxonomy' => 'product_tag', 'hide_empty' => false] );
		// woocommerce products
		$products = array();
		$args = array(
			'numberposts' => -1,
			'post_status' => 'publish',
		);
		$wc_products = wc_get_products( $args );
		if ( ! empty( $wc_products )) {
			foreach ( $wc_products as $key => $value ) {
				if ( $value->get_id() == $ep_functions->ep_get_global_settings('ep_wc_product_id') ) {
					continue;
				}
				$products[] = array( 'id' => $value->get_id(), 'name' => $value->get_name() );
			}
		}
		wp_enqueue_style( 'ep-admin-woocommerce-integration-css' );
		wp_enqueue_script( 'em-admin-woocommerce-integration-js' );
		wp_localize_script(
            'em-admin-woocommerce-integration-js', 
            'eventprime_wc_integration', 
            array(
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'select_category_text' => esc_html__( 'Select Category', 'eventprime-event-calendar-management' ),
				'categories_list' => $categories,
				'select_product_text' => esc_html__( 'Select Product', 'eventprime-event-calendar-management' ),
				'products_list' => $products,
				'is_purchase_mandatory_text' => esc_html__( 'Is Purchase Mandatory', 'eventprime-event-calendar-management' ),
				'remove_text' => esc_html__( 'Remove', 'eventprime-event-calendar-management' ),
            )
        );
		include __DIR__ .'/partials/metaboxes/meta-box-wi-panel-html.php';
	}
	
	/*
		* Save Woocommerce Products in Event Meta
		* 
		*/
	
	public function ep_save_woocommerce_products_in_events( $post_id, $post ){
		$ep_functions = new Eventprime_Basic_Functions();
		$allow_woocommerce_integration = $ep_functions->ep_get_global_settings( 'allow_woocommerce_integration' );
		
		if( isset( $allow_woocommerce_integration ) && $allow_woocommerce_integration == 1 ){
			if( isset( $_POST['enable_product'] ) ) {
				// $display_combined_cost = absint( $_POST['display_combined_cost'] );
				// update_post_meta( $post_id, 'em_display_combined_cost', $display_combined_cost );
				// update_post_meta($post_id, 'em_selectd_products', $_POST['woocommerce_product']);

				$selected_products = array();
				
				if( isset ( $_POST['woocommerce_product'] ) && ! empty( $_POST['woocommerce_product'] ) ){
					$woocommerce_product = $_POST['woocommerce_product'];
					foreach( $woocommerce_product as $key => $product_id ){
						if( isset( $product_id ) && ! empty( $product_id ) && $product_id != ' ' ){
							if( isset( $_POST['purchase_mendatory'][$key] ) ){
								$is_purchase_mandatory = $_POST['purchase_mendatory'][$key];
							}else{
								$is_purchase_mandatory = 0;
							}
							$selected_categories = array('uncategorized');
							if ( isset( $_POST["search_category_$key"] ) && !empty($_POST["search_category_$key"]) 
								&& is_array($_POST["search_category_$key"]) && count( $_POST["search_category_$key"] ) > 0 
							) {
								$selected_categories = $_POST["search_category_$key"]; 
							}
							array_push( $selected_products, array( 'selected_categories' => $selected_categories, 'product' => $product_id, 'purchase_mendatory' => $is_purchase_mandatory ) );
						}else{
							continue;
						}
					}
				}
				
				$enable_product = ( !empty($selected_products) && count($selected_products) > 0 ) ? absint( $_POST['enable_product'] ) : 0;
				update_post_meta( $post_id, 'em_enable_product', $enable_product );

				update_post_meta($post_id, 'em_selectd_products', $selected_products);

			}else{
				update_post_meta( $post_id, 'em_enable_product', 0 );
			}
		}
	}
	
	/*
		* Save Woocommerce Products in Recurring Event meta
		*/
	public function ep_save_woocommerce_products_in_events_child( $post_id, $post_data ){
		$ep_functions = new Eventprime_Basic_Functions();
		$allow_woocommerce_integration = $ep_functions->ep_get_global_settings( 'allow_woocommerce_integration' );
		
		if( isset( $allow_woocommerce_integration ) && $allow_woocommerce_integration == 1 ){
			if( isset( $_POST['enable_product'] ) ) {
				$enable_product = absint( $_POST['enable_product'] );
				// $display_combined_cost = absint( $_POST['display_combined_cost'] );
				update_post_meta( $post_id, 'em_enable_product', $enable_product );
				// update_post_meta( $post_id, 'em_display_combined_cost', $display_combined_cost );
				
				$selected_products = array();
				if( isset ( $_POST['woocommerce_product'] ) && ! empty( $_POST['woocommerce_product'] ) ){
					$woocommerce_product = $_POST['woocommerce_product'];
					foreach( $woocommerce_product as $key => $product_id ){
						if( isset( $product_id ) && ! empty( $product_id ) && $product_id != ' ' ){
							if( isset( $_POST['purchase_mendatory'][$key] ) ){
								$is_purchase_mandatory = $_POST['purchase_mendatory'][$key];
							}else{
								$is_purchase_mandatory = 0;
							}
							array_push( $selected_products, array( 'product' => $product_id, 'purchase_mendatory' => $is_purchase_mandatory ) );
						}else{
							continue;
						}
					}
				}

				update_post_meta($post_id, 'em_selectd_products', $selected_products);
				
			}else{
				update_post_meta( $post_id, 'em_enable_product', 0 );
			}
		}
	}

	// duplicate event data
	function ep_duplicate_event_add_extension_data( $event, $post_id ){
		$em_enable_product = isset( $event->em_enable_product ) ? $event->em_enable_product : 0;
		update_post_meta( $post_id, 'em_enable_product', $em_enable_product );
		
		if( $em_enable_product == 1 ){
		    // woocommerce products data
		    $em_selectd_products = ! empty( $event->em_selectd_products ) ? $event->em_selectd_products : '';
		    update_post_meta( $post_id, 'em_selectd_products', $em_selectd_products );
		}
	}

	public function event_woocommerce_integration_register_session(){
		// check EventPrime and WooCommerce currency
		$this->check_woo_ep_currency();
		// wp_enqueue_script( 'ep-woocommerce-integration-js', plugin_dir_url( __DIR__  ) . 'public/js/ep_woocommerce_integration.js', array( 'jquery' ), $this->version );
		// wp_localize_script('ep-woocommerce-integration-js', 'epwi_ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
	}

	public function check_woo_ep_currency() {
		if ( class_exists( 'Eventprime_Basic_Functions' ) ) {
			$ep_functions = new Eventprime_Basic_Functions;
			$currency = $ep_functions->ep_get_global_settings( 'currency' );
			$woo_currency = get_option('woocommerce_currency');
			if( $currency !== $woo_currency ){
				add_action( 'admin_notices', array( $this, 'em_woocommerce_integration_currrency_diff_message' ) );
			}
		}

	}

	public function em_woocommerce_integration_currrency_diff_message() {?>
		<div class="notice notice-success is-dismissible">
			<p><?php _e( 'Woocommerce Currency is not same as EventPrime Currency. This should be same.', 'eventprime-woocommerce-integration' ); ?></p>
		</div><?php
	}

	public function load_products_by_categories(){
		$selected_categories = $_POST['selected_categories'];
		$woocommerce_integration = new EP_Woocommerce_Integtation_Controller_List();
		$response = $woocommerce_integration->load_products_by_categories( $selected_categories );
		wp_send_json_success( $response );
	}

	// update woocommerce products and total price on checkout
	public function event_woocommerce_integration_refresh_booking_page_product_block( $args ){
		if( isset( $this->allow_woocommerce_integration ) && $this->allow_woocommerce_integration == 1 ){
			wp_verify_nonce( 'ep_show_wi_edit_product_save', 'ep_show_wi_edit_product_save_nonce' );
			$wc_controller = new EP_Woocommerce_Integtation_Controller_List();
			$event_id = isset( $_POST['event_id'] ) ? $_POST['event_id'] : '';
			$product_qty = isset( $_POST['product_qty'] ) ? $_POST['product_qty'] : '';
			$total_price = isset( $_POST['total_price'] ) ? $_POST['total_price'] : '';
			$total_tickets = isset( $_POST['total_tickets'] ) ? $_POST['total_tickets'] : '';
			$products_total = isset( $_POST['products_total'] ) ? $_POST['products_total'] : '';
			$coupon_amount= isset( $_POST['coupon_amount'] ) ? (float) base64_decode($_POST['coupon_amount']) : 0;
			$previous_product_price = isset( $_POST['previous_product_price'] ) ? $_POST['previous_product_price'] : 0;
			if( isset($previous_product_price) && !empty($previous_product_price) && $previous_product_price > 0 ){
				//$total_price = $total_price - $previous_product_price - $coupon_amount;
				//$total_price = $total_price;
				
			}
			$response = $wc_controller->get_updated_booking_page_product_block( $event_id, $product_qty, $total_price, $total_tickets );
			wp_send_json_success( $response );
		}
	}

	// register admin booking meta box
	public function ep_bookings_woocommerce_product_details_meta_box() {
		add_meta_box( 
		 'ep_woocommerce_products', 
		 __( 'Products', 'eventprime-event-calendar-management' ), 
		 array( $this, 'ep_woocommerce_products_box' ),
		 'em_booking', 'normal', 'high' 
		);
	}

	// admin booking product details meta box
	public function ep_woocommerce_products_box( $post ): void {
		wp_nonce_field( 'ep_save_booking_data', 'ep_booking_meta_nonce' );
		wp_enqueue_style(
			'ep-admin-woocommerce-integration-style',
			plugin_dir_url( __FILE__ ) . 'css/ep-admin-woocommerce-integration-style.css',
			false, $this->version
		);
		include_once __DIR__ .'/partials/metaboxes/meta-box-booking-products.php';
	}

	public function ep_wci_update_new_data_before_validating_cart($newdata, $data) {
		$event_id = $data['ep_event_booking_event_id'];
		$ep_functions = new Eventprime_Basic_Functions;
        $event = $ep_functions->get_single_event( $event_id );
		if ( isset($event->em_enable_product) && $event->em_enable_product == 1) { 
			// $newdata['ep_event_booking_total_price'] += $newdata['ep_wc_product_total']; 
			$newdata['ep_event_booking_total_price'] = round( $newdata['ep_event_booking_total_price'] + $newdata['ep_wc_product_total'] , 2 ); 
		}
		return $newdata;
	}

	public function ep_extend_paypal_order_items_add_wc_prods($items, $data) {
        $original_items = $items;
        if( 
            isset( $data['ep_event_booking_event_id'] ) && !empty($data['ep_event_booking_event_id']) && 
            isset($data['woocommerce_products']) && !empty($data['woocommerce_products']) && isset($data['ep_wc_product_total']) 
        ) {
            $ep_functions = new Eventprime_Basic_Functions;
            $event_id = $data['ep_event_booking_event_id'];
            $ep_enabled_woocommerce_integration = $ep_functions->ep_enabled_woocommerce_integration();
            $em_enable_product = metadata_exists('post', $event_id, 'em_enable_product') ? get_post_meta($event_id, 'em_enable_product', true) : 0;
            $em_selectd_products = metadata_exists('post', $event_id, 'em_selectd_products') ? get_post_meta($event_id, 'em_selectd_products', true) : [];

            if( !empty($ep_enabled_woocommerce_integration) && !empty($em_enable_product) && is_array($em_selectd_products) && count($em_selectd_products) > 0 ) {
                $ep_wc_product_total = (float) $data['ep_wc_product_total']; 
                $ep_currency = $ep_functions->ep_get_global_settings('currency');
                
                $item_data = [
                    "name" => esc_html__("Additional Prices", 'eventprime-woocommerce-integration'),
                    "description" => esc_html__("Additional Prices", 'eventprime-woocommerce-integration'),
                    "unit_amount" => [
                        "currency_code" => $ep_currency,
                        "value" => $ep_wc_product_total
                    ],
                    "quantity" => 1
                ];
                $items['items_total'] += $ep_wc_product_total; 
                $items['items'][] = $item_data;

                return $items;
            }
            return $original_items;
        }
        return $original_items;
    }

	





	
        
    
        
	
}
