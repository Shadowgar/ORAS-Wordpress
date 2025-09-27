<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://https://theeventprime.com
 * @since      1.0.0
 *
 * @package    Eventprime_Woocommerce_Integration
 * @subpackage Eventprime_Woocommerce_Integration/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Eventprime_Woocommerce_Integration
 * @subpackage Eventprime_Woocommerce_Integration/public
 * @author     EventPrime <support@metagauss.com>
 */
class Eventprime_Woocommerce_Integration_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */

    public $woocommerce_active = false;
    public $allow_woocommerce_integration = 0;

	public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
		$this->version = $version;
        
	}

    public function global_woocommerce_integration_initialization_public() {
        if ( ! class_exists('Eventprime_Basic_Functions') ) {
            return;
        }

		include_once(ABSPATH.'wp-admin/includes/plugin.php');
		if ( is_plugin_active('woocommerce/woocommerce.php')) {

            $this->woocommerce_active = true;

            add_action('wp_ajax_admin_woocommerce_product_categories', array($this, 'load_products_by_categories'));

            $ep_functions = new Eventprime_Basic_Functions();
            $allow_woocommerce_integration = $ep_functions->ep_get_global_settings( 'allow_woocommerce_integration' );
            if ($allow_woocommerce_integration == 1) {
                $this->allow_woocommerce_integration = 1;
            }
        } else {
            $this->woocommerce_active = false;
        }
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
        wp_enqueue_style(
			'ep-public-woocommerce-integration-css',
			plugin_dir_url(__FILE__). 'css/ep-public-woocommerce-integration-style.css',
			array(), $this->version
		);
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

	}
        
    // view product button action on card, masonry view
    public function event_woocommerce_integration_event_product_popup_render( $event ){
        $wc_controller  = new EP_Woocommerce_Integtation_Controller_List();
        if( isset( $this->allow_woocommerce_integration ) && $this->allow_woocommerce_integration == 1 && ! empty( $event->em_enable_product ) ){
            $event_controller = new Eventprime_Basic_Functions(); 
            $products = $wc_controller->load_event_product( $event->em_id );
            $currency_symbol = $event_controller->ep_currency_symbol();
            if( ! empty( $event->em_selectd_products ) && ! empty( $products ) ){
                $content = '';
                ob_start();?>
                <div id="ep_show_woocommerce_products_btn_<?php echo esc_attr( $event->id ); ?>" class="ep-event-action ep-cursor ep-pr-2 ep_show_woocommerce_products_popup" ep-modal-open="ep_show_woocommerce_products_popup_<?php echo esc_attr( $event->id ); ?>" data-event-id="<?php echo esc_attr( $event->id );?>" title="<?php esc_html_e( 'View included product', 'eventprime-woocommerce-integration' );?>">
                   <span class="material-icons-outlined ep-handle-share ep-button-text-color ep-fs-6">shopping_bag</span>
                </div>

                <div class="ep-modal ep-modal-view" id="ep_show_woocommerce_products_popup_<?php echo esc_attr( $event->id ); ?>" ep-modal="ep_show_woocommerce_products_popup_<?php echo esc_attr( $event->id ); ?>" style="display:none;">
                    <div class="ep-modal-overlay" ep-modal-close="ep_show_woocommerce_products_popup_<?php echo esc_attr( $event->id ); ?>"></div>
                    <div class="ep-modal-wrap ep-modal-lg">
                        <div class="ep-modal-content">
                            <div class="ep-modal-titlebar ep-d-flex ep-items-center ep-py-2">
                                <div class="ep-modal-title ep-px-3 ep-fs-5 ep-my-2">
                                    <?php esc_html_e( 'Products', 'eventprime-woocommerce-integration' );?>  
                                </div>
                                <span class="ep-modal-close" ep-modal-close="ep_show_woocommerce_products_popup_<?php echo esc_attr( $event->id ); ?>"><span class="material-icons-outlined ep_hide_woocommerce_products_popup" data-event-id="<?php echo esc_attr( $event->id );?>">close</span></span>
                            </div>
                            
                            <div class="ep-modal-body edit-product-block"> 
                                <div class="ep-box-row">
                                    <div class="ep-box-col-12 ep-p-4">
                                        <table class="ep-table ep-text-small ep-text-start">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col"><?php esc_html_e( 'Image', 'eventprime-woocommerce-integration' );?></th>
                                                <th scope="col"><?php esc_html_e( 'Name', 'eventprime-woocommerce-integration' );?></th>
                                                <th scope="col"><?php esc_html_e( 'Quantity', 'eventprime-woocommerce-integration' );?></th>
                                                <th scope="col"><?php esc_html_e( 'Is Mandatory', 'eventprime-woocommerce-integration' );?></th>
                                                <th scope="col"><?php esc_html_e( 'Price', 'eventprime-woocommerce-integration' );?></th>
                                            </tr>
                                        </thead>                            
                                        <tbody class="">
                                        <?php
                                        $i =1;
                                        foreach( $products as $productid => $value ){ 
                                            // get product permalink
                                            $product_permalink = wc_get_product( $productid );
                                            $single_product_detail_url = $product_permalink->get_permalink();?>
                                            <tr>
                                                <th scope="row" class="py-3"><?php echo $i;?></th>
                                                <td class="py-3">
                                                    <?php echo $value['image'];?>
                                                </td>
                                                <td class="py-3"><a href="<?php echo esc_url( $single_product_detail_url );?>"><?php echo esc_attr( $value['name'] ); ?></a></td>
                                                <td class="py-3">1</td>
                                                <?php                   
                                                if (!empty($value['purchase_mendatory']) && $value['purchase_mendatory'] == 1) { ?>
                                                <td class="py-3" ><?php esc_html_e( 'Yes', 'eventprime-event-woocstyle="color:red;font-weight:bold;"ommerce-integration' );?></td>
                                            <?php }else{ ?>
                                                <td class="py-3"></td>
                                            <?php } ?>
                                                <td class="py-3"><?php echo esc_attr( $currency_symbol . $value['price'] );?></td>
                                            </tr>
                                            <?php $i++; } ?>                       
                                        </tbody>
                                    </table>
                                </div>
                                    <a href="javascript:void(0);" class="ep_close_woocommerce_products_popup" data-event-id="<?php echo esc_attr( $event->id );?>" ep-modal-close="ep_show_woocommerce_products_popup_<?php echo esc_attr( $event->id ); ?>">
                                    <button type="button" style="float:right;"class="ep-btn ep-small ep-btn-dark ep-text-white ep-py-2 ep-mt-2"><?php esc_html_e( 'Close', 'eventprime-woocommerce-integration' );?></button>
                                    </a>      
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                $content = ob_get_clean();
                echo $content;       
            }
        }
    }

    // view product button action on event detail page
    public function event_woocommerce_integration_event_product_detail( $args ){
        if( isset( $this->allow_woocommerce_integration ) && $this->allow_woocommerce_integration == 1 && ! empty( $args->event->em_enable_product ) ){
            $wc_controller  = new EP_Woocommerce_Integtation_Controller_List();
            $event_controller = new Eventprime_Basic_Functions();
            $event = $event_controller->get_single_event( $args->event->id );
            $products = $wc_controller->load_event_product( $args->event->id );
            $currency_symbol = $event_controller->ep_currency_symbol();
            if( ! empty( $event->em_selectd_products ) && ! empty( $products ) ){
                $content = '';
                ob_start();?>
                <span class="ep-event-action ep-cursor" id="ep_show_woocommerce_products_popup-<?php echo esc_attr( $args->event->id );?>" ep-modal-open="ep_show_woocommerce_products_popup-<?php echo esc_attr( $args->event->id );?>" data-event-id="<?php echo esc_attr( $args->event->id );?>" title="<?php esc_html_e( 'View included product', 'eventprime-woocommerce-integration' );?>">
                    <span class="material-icons-outlined ep-handle-share ep-button-text-color">shopping_bag</span>
                </span>

                <div class="ep-modal ep-modal-view" id="ep_show_woocommerce_products_popup" ep-modal="ep_show_woocommerce_products_popup-<?php echo esc_attr( $args->event->id );?>" style="display:none;">
                    <div class="ep-modal-overlay" ep-modal-close="ep_show_woocommerce_products_popup-<?php echo esc_attr( $args->event->id );?>"></div>
                    <div class="ep-modal-wrap ep-modal-lg">
                        <div class="ep-modal-content">
                            <div class="ep-modal-titlebar ep-d-flex ep-items-center ep-py-2">
                                <div class="ep-modal-title ep-px-3 ep-fs-5 ep-my-2">
                                    <?php esc_html_e( 'Products', 'eventprime-woocommerce-integration' );?> 
                                </div>
                            </div>
                            <div class="ep-modal-body edit-product-block ep-p-5"> 
                                <div class="ep-box-row">
                                    <div class="ep-box-col-12 ep-p-4">
                                        <table class="ep-table ep-text-small ep-text-start">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col"><?php esc_html_e( 'Image', 'eventprime-woocommerce-integration' );?></th>
                                                <th scope="col"><?php esc_html_e( 'Name', 'eventprime-woocommerce-integration' );?></th>
                                                <th scope="col"><?php esc_html_e( 'Quantity', 'eventprime-woocommerce-integration' );?></th>
                                                <th scope="col"><?php esc_html_e( 'Is Mandatory', 'eventprime-woocommerce-integration' );?></th>
                                                <th scope="col"><?php esc_html_e( 'Price', 'eventprime-woocommerce-integration' );?></th>
                                            </tr>
                                        </thead>                            
                                        <tbody class="">
                                            <?php
                                            $i =1;
                                            foreach($products as $productid => $value){ 
                                                // get product permalink
                                                $product_permalink = wc_get_product( $productid );
                                                $single_product_detail_url = $product_permalink->get_permalink();?>
                                                <tr>
                                                    <th scope="row" class="py-3"><?php echo $i;?></th>
                                                    <td class="py-3">
                                                        <?php echo $value['image'];?>
                                                    </td>
                                                    <td class="py-3"><a href="<?php echo esc_url( $single_product_detail_url );?>" target="_blank"><?php echo esc_attr( $value['name'] ); ?></a></td>
                                                    <td class="py-3">1</td>
                                                    <?php                   
                                                    if (!empty($value['purchase_mendatory']) && $value['purchase_mendatory'] == 1) { ?>
                                                    <td class="py-3"><?php esc_html_e( 'Yes', 'eventprime-woocommerce-integration' );?></td>
                                                <?php }else{ ?>
                                                    <td class="py-3"></td>
                                                <?php } ?>
                                                    <td class="py-3"><?php echo esc_attr( $currency_symbol . $value['price'] );?></td>
                                                </tr>
                                            <?php $i++; } ?>                       
                                        </tbody>
                                    </table>
                                </div>
                                <a href="javascript:void(0);" ep-modal-close="ep_show_woocommerce_products_popup">
                                    <button type="button" aria-label="Close" ep-modal-close="ep_show_woocommerce_products_popup-<?php echo esc_attr( $args->event->id );?>" class="ep-btn ep-btn-dark ep-py-2 ep-mt-2"><?php esc_html_e( 'Close', 'eventprime-woocommerce-integration' );?></button>
                                    </a>      
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                $content = ob_get_clean();
                echo $content;       
            }
        }
    }

    // show woocommerce products on checkout
    public function event_woocommerce_integration_booking_page_product_block( $args ){
        if( isset( $this->allow_woocommerce_integration ) && $this->allow_woocommerce_integration == 1 && ! empty( $args->event->em_enable_product ) ){
            $wc_controller = new EP_Woocommerce_Integtation_Controller_List();
            $booking_block = $wc_controller->get_booking_page_product_block( $args );
            echo $booking_block;
        }
    }

    // update total price with products price
    public function event_prime_woocommerce_products_price_update( $total_price, $event_id ){
        $em_enable_product = get_post_meta( $event_id, 'em_enable_product', true );
        if( isset( $this->allow_woocommerce_integration ) && $this->allow_woocommerce_integration == 1 && ! empty( $em_enable_product ) ){
        $wc_controller = new EP_Woocommerce_Integtation_Controller_List();
        $total_price = $wc_controller->update_total_price( $total_price, $event_id );
        }   
        return $total_price;
    }

    public function event_woocommerce_integration_get_woocommerce_state_by_country_code(){
        if( isset( $this->allow_woocommerce_integration ) && $this->allow_woocommerce_integration == 1 ){
            $wc_controller = new EP_Woocommerce_Integtation_Controller_List();
            $states = $wc_controller->get_woocommerce_state_by_country_code();
            echo json_encode($states);
            wp_die();
        }
    }

    public function event_woocommerce_integration_front_checkout_data_view( $args ){
        $wc_controller = new EP_Woocommerce_Integtation_Controller_List();
        if( isset( $this->allow_woocommerce_integration ) && $this->allow_woocommerce_integration == 1 ){
            $ep_functions = new Eventprime_Basic_Functions(); 
            $billing_block = $wc_controller->get_checkout_page_billing_block( $args );
            $shipping_block = $wc_controller->get_checkout_page_shipping_block( $args );
        }
    }

    // confirm booking add order info
    public function event_woocommerce_integration_add_booking_order_info( $order_info, $data ){
        $wc_controller  = new EP_Woocommerce_Integtation_Controller_List();
        if( isset( $this->allow_woocommerce_integration ) && $this->allow_woocommerce_integration == 1 && isset( $data['woocommerce_products'] ) && ! empty( $data['woocommerce_products'] ) ){
            $order_info['woocommerce_products'] = $wc_controller->format_woocommerce_cart_products( $data );
            $order_info['billing_address'] = $wc_controller->format_woocommerce_billing_address( $data );
            $order_info['shipping_address'] = $wc_controller->format_woocommerce_shipping_address( $data );
        }
        return $order_info;
    }

    // update booking total for EP WC checkout integration 
    public function ep_update_booking_total_for_wc_checkout( $order_info, $data ) {
        if ( class_exists('Eventprime_Woocommerce_Checkout_Integration') && isset($order_info['ep_wc_checkout_booking']) && !empty($order_info['ep_wc_checkout_booking']) ) {
            if ( isset($data['woocommerce_products']) && isset($data['woocommerce_products_variation_id']) && isset($data['woocommerce_products_qty'])
            && !empty($data['woocommerce_products']) && !empty($data['woocommerce_products_variation_id']) && !empty($data['woocommerce_products_qty'])
            ) {
                $i = 0;
                foreach( $data['woocommerce_products'] as $product_id ) {
                    if ( $product_id !== $data['woocommerce_products_variation_id'][$i] ) {
                        $product_id = $data['woocommerce_products_variation_id'][$i]; 
                    }
                    $product = wc_get_product( $product_id ); 
                    $product_price = (float) $product->get_price();
                    $order_info['booking_total'] += (float) ( $product_price * $data['woocommerce_products_qty'][$i] ); 
                    $i++;
                }
            }
        }
        return $order_info;
    }

    // add new woocommerce order
    public function event_woocommerce_integration_add_new_woocommerce_order( $order_id, $data ){
        $wc_controller  = new EP_Woocommerce_Integtation_Controller_List();
        if( isset( $this->allow_woocommerce_integration ) && $this->allow_woocommerce_integration == 1 && isset( $order_id ) && ! empty( $order_id ) && ! empty( $data )){
            $wc_controller->add_new_woocommerce_order( $order_id, $data );
        }
    }

    // show product details on booking details page
    public function event_woocommerce_integration_front_user_booking_item_details($args){
        $wc_controller  = new EP_Woocommerce_Integtation_Controller_List();
        if( isset( $this->allow_woocommerce_integration ) && $this->allow_woocommerce_integration == 1 && isset( $args->em_id ) && ! empty( $args->em_order_info['woocommerce_products'] ) ){
            $wc_controller->front_user_booking_item_details( $args );
        }
    }

    // Add WC product sold with event to the EP WC checkout extension cart 
    public function ep_add_products_to_woocoomerce_checkout_extension_cart( $data ) {
        global $woocommerce; 

        $woocommerce_data = [];
        // Add (product_id or variation_id) + (product qty) to the cart 
        if ( isset($data['woocommerce_products'])
            && !empty($data['woocommerce_products']) && count($data['woocommerce_products']) > 0 
            && !empty($data['woocommerce_products_qty']) && !empty($data['woocommerce_products_variation_id']) && !empty($data['ep_product_variation_attr'])
        ) {
            $ep_wc_int_cntrl = new EP_Woocommerce_Integtation_Controller_List();
            $ev_woo_products = $ep_wc_int_cntrl->load_event_product($data['ep_event_booking_event_id']);

            if (!empty($data['woocommerce_products']) && count($data['woocommerce_products']) > 0 && !empty($data['woocommerce_products_qty']) && !empty($data['woocommerce_products_variation_id']) && !empty($data['ep_product_variation_attr'])) {
                $i = 0;
                $cart_product = (object) $data['woocommerce_products'];
                foreach ($cart_product as $productid) {
                    if (isset($productid)) {
                        if (isset($ev_woo_products[$productid])) {
                            $pdata = array();
                            $pdata = $ev_woo_products[$productid];
                            $pdata['id'] = $productid;
                            $pdata['qty'] = $data['woocommerce_products_qty'][$i];
                            $price = $ev_woo_products[$productid]['price'];
                            // calculate price if there is any variation id
                            if ($data['woocommerce_products_variation_id'][$i] != $productid && $data['woocommerce_products_variation_id'][$i] > 0) {
                                $variable_product = wc_get_product($data['woocommerce_products_variation_id'][$i]);
                                $variation_image = $variable_product->get_image(array(100, 100));
                                // $pdata['variation_id'] = $data['woocommerce_products_variation_id'][$i];
                                $pdata['id'] = $data['woocommerce_products_variation_id'][$i];
                                $pdata['image'] = $variation_image;
                                $price = $variable_product->get_price();
                                $pdata['price'] = $price;
                            }
                            $subtotal = (float) $price * (int) $data['woocommerce_products_qty'][$i];
                            $pdata['sub_total'] = number_format($subtotal, 2);
                            // check if variation exists
                            if (!is_numeric($data['ep_product_variation_attr'][$i])) {
                                $pdata['variation'] = array();
                                foreach ((array) $data['ep_product_variation_attr'][$i] as $single_attr) {
                                    // $pdata['variation']['variation_id'] = $data['woocommerce_products_variation_id'][$i];
                                    $pdata['variation'] = json_decode($single_attr);
                                }
                            }

                            $woocommerce_data[] = $pdata;
                        }
                    }
                    $i++;
                }
            }
        }
        if(!empty($woocommerce_data)){
            foreach($woocommerce_data as $product){
                $woocommerce->cart->add_to_cart( $product['id'] , $product['qty'], NULL, NULL, 
                    array('ep_woo_tickets_product_data'=> 
                        [
                            'product' => $product,
                            'additional_product_details' => [
                                'woocommerce_products' => $data['woocommerce_products'],
                                'woocommerce_products_qty' => $data['woocommerce_products_qty'], 
                                'woocommerce_products_variation_id' => $data['woocommerce_products_variation_id'], 
                                'ep_product_variation_attr' => $data['ep_product_variation_attr'], 
                            ],
                        ]
                    ) 
                );
            }
        }
    }

    // Will be added later after WC checkout code refactoring 
    // public function ep_modify_cart_items_details_for_wc_products( $item_data, $cart_item ) {
    //     if ( isset( $cart_item['ep_woo_tickets_product_data'] ) && !empty( $cart_item['ep_woo_tickets_product_data'] )) {
    //         $event_wc_product_data = $cart_item['ep_woo_tickets_product_data']['product'];
    //         // $event_wc_product_data = $cart_item['ep_woo_tickets_product_data']['additional_product_details']['woocommerce_products_qty'];
    //         $item_data[] = array(
    //             'key'     => esc_html__( 'Qty', 'eventprime-woocommerce-checkout-integration' ),
    //             'value'   => wc_clean($event_wc_product_data['qty']),
    //             'display' => '',
    //         );
    //         return $item_data;                       
    //     }
    // }

    





}
