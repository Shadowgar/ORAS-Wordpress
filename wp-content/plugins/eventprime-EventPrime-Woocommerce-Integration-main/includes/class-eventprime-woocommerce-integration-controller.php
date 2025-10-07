<?php

class EP_Woocommerce_Integtation_Controller_List {

    public $woocommerce_active = false;
    public $allow_woocommerce_integration = 0;

    public function __construct() {
        if (is_plugin_active('woocommerce/woocommerce.php')) {

            $this->woocommerce_active = true;

            add_action('wp_ajax_admin_woocommerce_product_categories', array($this, 'load_products_by_categories'));

            // check if woocommerce extension is enabled
            $ep_functions = new Eventprime_Basic_Functions();
            $allow_woocommerce_integration = $ep_functions->ep_get_global_settings('allow_woocommerce_integration');
            if ($allow_woocommerce_integration == 1) {
                $this->allow_woocommerce_integration = 1;
            }
        } else {
            $this->woocommerce_active = false;
        }
    }

    public function load_products_by_categories($selected_cats) {
        $response = new stdClass();
        $response->products = array();
        $categories = array();
        $ep_functions = new Eventprime_Basic_Functions();

        $args = array(
            'numberposts' => -1,
            'post_status' => 'publish'
        );

        if (!empty($selected_cats)) {
            $selected_cats = explode(',', $selected_cats);
            $args['category'] = $selected_cats;
            $products = wc_get_products($args);
        } else {
            $products = wc_get_products($args);
        }

        if (!empty($products)) {
            foreach ($products as $key => $value) {
                if ( $value->get_id() == $ep_functions->ep_get_global_settings('ep_wc_product_id') ) {
					continue;
				}
                $response->products[] = array('id' => $value->get_id(), 'name' => $value->get_name());
            }
        }

        return $response;
    }

    public function load_event_product($event_id) {
        if (!empty($event_id)) {
            $event_products[$event_id] = [];
            $event_controller = new Eventprime_Basic_Functions();
            $event = $event_controller->get_single_event($event_id);
            if (!empty($event->em_selectd_products)) {
                foreach ($event->em_selectd_products as $value) {
                    $value = (array) $value;
                    $productData = array();
                    $productid = intval($value['product']);
                    if (isset($productid) && !empty($productid)) {
                        $product = wc_get_product($productid);
                        if ( $product && $product->exists() && 'publish' === $product->get_status() ) {
                            $productData['image'] = $product->get_image(array(100, 100));
                            $productData['name'] = $product->get_name();
                            $productData['price'] = $product->get_price();
                            $productData['type'] = $product->get_type();
                            $productData['purchase_mendatory'] = $value['purchase_mendatory'];
                            $event_products[$event_id][$productid] = $productData;
                        }
                        
                    }
                }
            }
            return $event_products[$event_id];
        }
    }

    // booking page product block
    public function get_booking_page_product_block($args) {
        if (isset($this->allow_woocommerce_integration) && $this->allow_woocommerce_integration == 1) {
            wp_enqueue_script('ep-woocommerce-integration-js', plugin_dir_url(dirname(__FILE__)) . 'public/js/ep_woocommerce_integration.js', array(), Eventprime_Woocommerce_Integration_VERSION);
            wp_localize_script('ep-woocommerce-integration-js', 'epwi_ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
            $ep_functions = new Eventprime_Basic_Functions();

            $event_id = $args->event->id;
            $cart_selected_product = array();
            $woocommerce_products = array();
            $currency_symbol = $ep_functions->ep_currency_symbol();
            if (!empty($event_id)) {
                $products = $this->load_event_product($event_id);
                if (!empty($products) && !empty($args->event->em_selectd_products)) {
                    ob_start();
                    ?>
                    <li class="ep-list-group-item ep-border-opacity-25" aria-current="true" id="ep-woocommerce-booking-product-block">
                        <div class="ep-text-small ep-mb-2">
                            <span class="ep-fw-bold ep-text-small ep-mr-1 ep-text-uppercase"><?php esc_html_e('Included Products', 'eventprime-event-woocommerce-integration'); ?></span>
                            <a href="javascript:void(0)" id="ep_show_wi_edit_product_btn"  ep-modal-open="ep_show_wi_edit_product_popup" data-event-id="<?php echo esc_attr($event_id); ?>"><?php esc_html_e('Edit', 'eventprime-event-woocommerce-integration'); ?></a>
                        </div>
                        <!-- EventPrime Woocommerce Product Block On Checkout Page -->
                        <div id="ep-woocommerce-products-block">
                    <?php
                    $product_total_price = 0;
                    foreach ($products as $productid => $value) {
                        $cart_selected_product['id'] = $productid;
                        $cart_selected_product['qty'] = 1;
                        $cart_selected_product['price'] = (float)$value['price'];
                        ?>
                                <div class="ep-box-row ep-text-small ep-mb-2 ep-woocommerce-product-row">
                                    <div class="ep-box-col-8 ep-d-flex ep-align-items-center">
                                        <div class="ep-d-inline-flex ep-mr-2 ep-woocommerce-product-image"><?php echo $value['image']; ?></div>
                                        <div class="ep-d-inline-flex ep-flex-column ep-text-truncate ep-ml-2">
                                            <div class="ep-d-flex"><?php echo esc_attr($value['name']); ?></div>
                                            <?php if (!empty($value['purchase_mendatory']) && $value['purchase_mendatory'] == 1) { ?>
                                                <div class="ep-d-flex ep-text-small"> <span class="ep-mr-1"> x 1 </span> <span class="ep-text-muted" style="color:red; font-weight:bold;"> <?php esc_html_e('mandatory', 'eventprime-event-woocommerce-integration'); ?> </span></div>
                                            <?php } else { ?>
                                                <div class="ep-d-flex ep-text-small"><span class="ep-mr-1"> x 1 </span><span class="ep-text-muted"></span></div>
                                            <?php } ?>

                                        </div>
                                    </div>
                                    <div class="ep-box-col-4 ep-text-end"><?php echo esc_html($ep_functions->ep_price_with_position((float)$value['price'])); ?> </div>
                                </div>

                                <input type="hidden" value="<?php echo esc_attr($productid); ?>" name="woocommerce_products[]">
                                <input type="hidden" value="1" name="woocommerce_products_qty[]">
                                <input type="hidden" value="<?php echo esc_attr($productid); ?>" name="woocommerce_products_variation_id[]">
                                <input type="hidden" value="<?php echo esc_attr($productid); ?>" name="woocommerce_products_variation_attr[]">

                        <?php
                        $product_total_price += (float)$value['price'];
                    }
                    ?>

                            <input type="hidden" value="" name="billing_address">
                            <input type="hidden" value="" name="shipping_address">
                            <input type="hidden" value="<?php echo esc_attr($product_total_price); ?>" id="ep_wc_product_total" name="ep_wc_product_total">
                        </div>
                        <!-- EventPrime Woocommerce Product Modal On Checkout Page -->
                        <div class="ep-modal ep-modal-view" id="ep_show_wi_edit_product_popup" ep-modal="ep_show_wi_edit_product_popup" style="display:none;">
                            <div class="ep-modal-overlay" ep-modal-close="ep_show_wi_edit_product_popup"></div>
                            <div class="ep-modal-wrap ep-modal-lg">
                                <div class="ep-modal-content">
                                    <div class="ep-modal-titlebar ep-d-flex ep-items-center ep-py-2">
                                        <div class="ep-modal-title ep-px-3 ep-fs-5 ep-my-2">
                                            <?php esc_html_e('Included Products', 'eventprime-event-woocommerce-integration'); ?>
                                        </div>
                                        <span class="ep-modal-close" ep-modal-close="ep_show_wi_edit_product_popup"><span class="material-icons-outlined">close</span></span>
                                    </div>

                                    <div class="ep-modal-body edit-product-block">
                                        <div class="ep-box-wrap">
                                        <div class="ep-box-row">
                                            <div class="ep-box-col-12">
                                                <div class="ep-alert ep-alert-warning ep-text-small ep-mb-4" role="alert">       
                                                    <?php esc_html_e('These products are included with your tickets. You can modify quantity of optional products.', 'eventprime-event-woocommerce-integration'); ?>
                                                </div>
                                            </div>

                                            <?php
                                            foreach ($products as $productid => $value) {
                                                $productid = trim($productid);
                                                $product_description = get_post($productid)->post_content;
                                                $product = wc_get_product($productid);
                                                // get product permalink
                                                $single_product_detail_url = $product->get_permalink();
                                                ?>
                                                <div class="ep-box-col-12 ep-mb-4" id="ep-single-product-block-<?php echo esc_attr($productid); ?>">
                                                    <div class="ep-box-row ep-text-small ep-mb-2">

                                                            <div class="ep-box-col-2"> <?php echo $value['image']; ?></div>
                                                            <div class="ep-box-col-7 ep-d-inline-flex ep-flex-column">
                                                                <div class="">
                                                                    <span class="ep-fw-bold"><?php echo esc_attr($value['name']); ?></span>
                                                                    <a href="<?php echo esc_url($single_product_detail_url); ?>" target="_blank">
                                                                        <span class="material-icons-round ep-fs-6 ep-align-middle ep-text-primary">launch</span>
                                                                    </a>
                                                                </div>
                                                                <?php if (!empty($value['purchase_mendatory']) && $value['purchase_mendatory'] == 1) { ?>
                                                                    <div class="ep-text-danger ep-text-small ep-fw-bold"><span class="ep-text-small">Mandatory</span></div>
                                                                <?php } else { ?>
                                                                    <div class="ep-text-danger ep-text-small ep-fw-bold"><span class="ep-text-small"></span></div>
                                                                <?php } ?>
                                                                <?php if (isset($product_description) && !empty($product_description)) { ?>
                                                                    <div class="ep-text-small ep-text-muted"><span> <?php echo wpautop(wp_kses_post($product_description)); ?><a href="<?php echo esc_url($single_product_detail_url); ?>" target="_blank">more</a></span></div>
                                                                <?php } ?>

                                                                <div class="ep-d-inline-flex ep-flex-column ep-product-variations" >
                                                                    <?php if ($value['type'] == 'variable') {?>
                                                                        <?php
                                                                        if (!empty($product)) {
                                                                            $product_price = $product->get_price();
                                                                            $available_variations = $product->get_available_variations();
                                                                            $available_attributes = wp_json_encode($product->get_variation_attributes());
                                                                            $variations_json = wp_json_encode($available_variations);
                                                                            $variations_attr = function_exists('wc_esc_json') ? wc_esc_json($variations_json) : _wp_specialchars($variations_json, ENT_QUOTES, 'UTF-8', true);
                                                                            $currency_symbol = $ep_functions->ep_currency_symbol();
                                                                            $min_price = $product->get_variation_price('min');
                                                                            $max_price = $product->get_variation_price('max');
                                                                            $price_range = ($min_price == $max_price) ? ($currency_symbol . $min_price) : ($currency_symbol . $min_price . ' - ' . $currency_symbol . $max_price);
                                                                            $available_filters = array();

                                                                            foreach ( $available_variations as $variation ) {
                                                                                $variation_obj = wc_get_product( $variation['variation_id'] );
                                                                                $variation_price = floatval( $variation_obj->get_price() );

                                                                                if ( $variation_price == floatval($value['price']) ) {
                                                                                    $attributes = $variation_obj->get_attributes(); 
                                                                                }
                                                                            }
                                                                            // print_r($attributes);
                                                                            ?>
                                                                            <div class="ep-woocommerce-product-option-popup" id="ep-woocommerce-product-option-popup-model-<?php echo esc_attr($productid); ?>" data-event_id="<?php echo esc_attr($event_id); ?>" data-currency_symbol="<?php echo esc_attr($currency_symbol); ?>" data-product_id="<?php echo esc_attr($productid); ?>" data-final_price="<?php echo $product_price; ?>" data-product_variations="<?php echo $variations_attr; ?>" data-available_attributes="<?php echo esc_attr($available_attributes); ?>">
                                                                                <tr>
                                                                                    <td class="ep-product-price-range"><span class="ep-fs-6"><?php echo esc_html($price_range); ?></span><br></td>
                                                                                    <td class="ep-product-variation-block">
                                                                                        <?php
                                                                                        foreach ($product->get_attributes() as $attr_name => $attr) {
                                                                                            $available_filters[$attr_name] = array();
                                                                                            $attr_label = wc_attribute_label($attr_name);
                                                                                            $attribute_key = 'attribute_' . sanitize_title($attr_name);
                                                                                            ?>
                                                                                            <td class="ep-product-variation-block-left"><span class="ep-fw-bold"><?php echo esc_html( $attr_label); ?></span></td>
                                                                                            <td class="ep-product-variation-block-right">
                                                                                                <select class="ep-product-variation-select ep-product-variation-select-<?php echo esc_attr($productid); ?> ep-form-control" name="<?php echo esc_attr($attribute_key); ?>"  id="<?php echo esc_attr($attribute_key); ?>" data-attr="attribute_<?php echo esc_attr($attribute_key); ?>" data-attr_label="<?php echo esc_attr($attr_label); ?>" data-attr_product_id="<?php echo esc_attr($productid); ?>">
                                                                                                    <option value=""> <?php esc_html_e('Select', 'eventprime-event-woocommerce-integration'); ?> <?php echo esc_attr($attr_label); ?> </option>
                                                                                                    <?php /* if (!empty($attr->get_terms())) {
                                                                                                        foreach ($attr->get_terms() as $term) {
                                                                                                            ?>
                                                                                                            <option value="<?php echo esc_attr($term->slug); ?>"><?php echo esc_attr($term->name); ?></option>
                                                                                                            <?php $available_filters[$attr_name][$term->slug] = $term->name; ?>
                                                                                                        <?php }
                                                                                                    }*/
                                                                                                    ?>

                                                                                                    <?php
                                                                                                    // Check if the attribute is a taxonomy.
                                                                                                    if ($attr->is_taxonomy()) {
                                                                                                        // Get the terms for the attribute's taxonomy.
                                                                                                        $terms = get_terms(array(
                                                                                                            'taxonomy' => $attr->get_name(),
                                                                                                            'hide_empty' => false,
                                                                                                        ));

                                                                                                        if (!empty($terms) && !is_wp_error($terms)) {
                                                                                                            foreach ($terms as $term) {
                                                                                                                ?>
                                                                                                                <option value="<?php echo esc_attr($term->slug); ?>"><?php echo esc_html($term->name); ?></option>
                                                                                                                <?php $available_filters[$attr_name][$term->slug] = $term->name; ?>
                                                                                                            <?php
                                                                                                            }
                                                                                                        }
                                                                                                    } else {
                                                                                                        // If the attribute is not a taxonomy, use the options directly.
                                                                                                        $options = $attr->get_options();
                                                                                                        if (!empty($options)) {
                                                                                                            foreach ($options as $option) {
                                                                                                                //$is_attribute_selected = ( array_key_exists($attr_name,$attributes) && $attributes[$attr_name] === $option ) ? esc_attr(" selected") : "";
                                                                                                                ?>
                                                                                                                <option value="<?php echo esc_attr($option); ?>"><?php echo esc_html($option); ?></option>
                                                                                                                <?php $available_filters[$attr_name][$option] = $option; ?>
                                                                                                            <?php
                                                                                                            }
                                                                                                        }
                                                                                                    }
                                                                                                    ?>


                                                                                                </select>
                                                                                                <span class="ep-product-variation-error-<?php echo esc_attr($productid); ?>"></span>
                                                                                            </td>
                                                                                            <?php 
                                                                                        } ?>
                                                                                        <button type="button" class="ep-clear-variations-<?php echo esc_attr($productid); ?> ep-btn ep-btn-secondary" style="display:none;"><?php esc_html_e( 'Clear', 'eventprime-woocommerce-integration'); ?></button>


                                <?php
                                $available_filters = wp_json_encode($available_filters);
                                $available_filters = function_exists('wc_esc_json') ? wc_esc_json($available_filters) : _wp_specialchars($available_filters, ENT_QUOTES, 'UTF-8', true);
                                ?>

                                                                                    </td>
                                                                                <div class="ep-woocommerce-product-available-filters" data-available_filters="<?php echo esc_attr($available_filters); ?>"></div>
                                                                                </tr>
                                                                            </div>

                                <?php
                            }
                            ?>


                        <?php } ?>


                                                                </div>
                                                            </div>
                                                            <div class="ep-box-col-2 ep-d-inline-flex ep-flex-column ep-align-items-center ep-justify-content-center">
                                                                <div class="ep-btn-group ep-btn-group-sm" role="group" aria-label="t">
                                                                    <button type="button" class="ep-btn ep-border ep-bg-light ep-text-secondary ep_product_minus" data-product_id="<?php echo esc_attr($productid); ?>" data-is_product_mandatory="<?php echo (!empty($value['purchase_mendatory']) && $value['purchase_mendatory'] == 1 ) ? $value['purchase_mendatory'] : 0; ?>">-</button>
                                                                    <button type="button" class="ep-btn ep-border ep-bg-light ep-text-secondary"><input type="hidden" min="1" name="ep_product_quantity[]" class="ep-form-control" value="1" id="ep_product_quantity_<?php echo esc_attr($productid); ?>" /><span class="ep-product-quantity-<?php echo esc_attr($productid); ?>">1</span></button>
                                                                    <button type="button" class="ep-btn ep-border ep-bg-light ep-ext-secondary ep_product_plus" data-product_id="<?php echo esc_attr($productid); ?>" data-is_product_mandatory="<?php echo (!empty($value['purchase_mendatory']) && $value['purchase_mendatory'] == 1 ) ? $value['purchase_mendatory'] : 0; ?>">+</button>
                                                                </div>
                                                                <div class="ep-text-small ep-text-muted ep-mt-2 ep-d-none">x <span id="ep_product_price_range_<?php echo esc_attr($productid); ?>"><?php echo esc_html($ep_functions->ep_price_with_position((float)$value['price'])); ?></span></div>
                                                            </div>
                                                            <div class="ep-box-col-1 ep-d-inline-flex ep-align-items-center ep-justify-content-center">
                                                                <div class="ep-fw-bold" data-single_product_price="<?php echo $value['price']; ?>" data-product_currency="<?php echo esc_attr($currency_symbol); ?>" id="ep_product_total_price_range_<?php echo esc_attr($productid); ?>"><?php echo esc_html($ep_functions->ep_price_with_position((float)$value['price'])); ?></div>
                                                            </div>
                                                           
                                                            <input type="hidden" name="ep_product_variation_id[]" value="<?php echo esc_attr($productid); ?>" />
                                                            <input type="hidden" name="ep_product_variation_attr[]" value="<?php echo esc_attr($productid); ?>" />
                                                        
                                                        <input type="hidden" name="ep_product_image_id[]" value="<?php echo esc_attr($productid); ?>" />

                                                    </div>
                                                </div>
                    <?php } ?>
                                        </div>
                                        </div>
                                        <div class="ep-modal-footer ep-border-0 ep-mt-3 ep-d-flex ep-content-right">
                                            <span class="spinner ep-woocommerce-integration-spinner"></span>  
                                            <a href="javascript:void(0);" class="ep-mr-2" ep-modal-close="ep_show_wi_edit_product_popup ">
                                                <button type="button" class="ep-btn ep-small ep-btn-dark ep-py-2" id="ep_close_wi_edit_product_modal"><?php esc_html_e('Close', 'eventprime-event-woocommerce-integration'); ?></button>
                                            </a>
                                            <input type="hidden" name="edit_product_event_id" id="edit_product_event_id" value="<?php echo esc_attr($event_id); ?>" />
                    <?php wp_nonce_field('ep_show_wi_edit_product_save', 'ep_show_wi_edit_product_save_nonce'); ?>
                                            <button type="button" class="ep-btn ep-small ep-btn-warning ep-py-2 " id="ep_show_wi_edit_product_save_btn"><?php esc_html_e('Update Cart', 'eventprime-event-woocommerce-integration'); ?></button>

                                        </div>


                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    <span class="spinner ep-woocommerce-integration-spinner"></span>
                    <?php
                }
            }
        }

        return ob_get_clean();
    }

    public function get_woocommerce_state_by_country_code() {
        $default_county_states = array();
        if (isset($this->allow_woocommerce_integration) && $this->allow_woocommerce_integration == 1) {

            wp_enqueue_script('ep-woocommerce-integration-js', plugin_dir_url(dirname(__FILE__)) . 'public/js/ep_woocommerce_integration.js', array(), Eventprime_Woocommerce_Integration_VERSION);
            wp_localize_script('ep-woocommerce-integration-js', 'epwi_ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));

            $country_code = isset($_POST['country_code']) ? $_POST['country_code'] : '';
            if (!empty($country_code)) {
                global $woocommerce;
                $countries_obj = new WC_Countries();
                $countries = $countries_obj->__get('countries');
                $default_county_states = $countries_obj->get_states($country_code);
            }
        }
        return $default_county_states;
    }

    public function update_total_price($total_price, $event_id) {
        if (isset($this->allow_woocommerce_integration) && $this->allow_woocommerce_integration == 1) {

            $event_id = $event_id;
            $product_total_price = 0;
            if (!empty($event_id)) {
                $products = $this->load_event_product($event_id);
                if (!empty($products)) {

                    foreach ($products as $productid => $value) {

                        $product_total_price += 1 * (float)$value['price'];
                    }
                }
            }

            $total_price = $product_total_price + $total_price;
            return $total_price;
        }
    }

    // booking page updated product block
    public function get_updated_booking_page_product_block($event_id, $product_qty, $total_price, $total_tickets) {
        if (isset($this->allow_woocommerce_integration) && $this->allow_woocommerce_integration == 1) {
            $response = array('success' => false, 'message' => __('Cart Updated Successfully.', 'eventprime-event-woocommerce-integration'));
            wp_enqueue_script('ep-woocommerce-integration-js', plugin_dir_url(dirname(__FILE__)) . 'public/js/ep_woocommerce_integration.js', array(), Eventprime_Woocommerce_Integration_VERSION);
            wp_localize_script('ep-woocommerce-integration-js', 'epwi_ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
            $ep_functions = new Eventprime_Basic_Functions();
            //$event_id = $event_id;
            $product_total_price = 0;
            $variation_ids = isset($_POST['variation_ids']) ? $_POST['variation_ids'] : '';
            $variation_attr = isset($_POST['variation_attr']) ? $_POST['variation_attr'] : '';
            $currency_symbol = $ep_functions->ep_currency_symbol();
            if (!empty($event_id)) {
                $products = $this->load_event_product($event_id);
                if (!empty($products)) {
                    ob_start();
                    $i = 0;
                    foreach ($products as $productid => $value) {
                        if ($product_qty[$i] == 0 && $product_qty[$i] < 1) {
                            $i++;
                            continue;
                        }
                        if ($variation_ids[$i] != $productid && $variation_ids[$i] != 0) {
                            $variation_id = $variation_ids[$i];
                            $variable_product = wc_get_product($variation_id);
                            $value['image'] = $variable_product->get_image(array(100, 100));
                            $value['price'] = $variable_product->get_price();
                        }
                        ?>
                        <div class="ep-box-row ep-text-small ep-mb-2 ep-woocommerce-product-row">
                            <div class="ep-box-col-8 ep-d-flex ep-align-items-center">
                                <div class="ep-d-inline-flex ep-mr-2 ep-woocommerce-product-image ep-mr-1"><?php echo $value['image']; ?></div>
                                <div class="d-inline-flex ep-flex-column ep-text-truncate">
                                    <div class="ep-d-flex"><?php echo esc_attr($value['name']); ?></div>
                                        <?php if (!empty($value['purchase_mendatory']) && $value['purchase_mendatory'] == 1) { ?>
                                                <div class="ep-d-flex ep-text-small">
                                                    <span class="ep-mr-1">x <?php echo esc_attr($product_qty[$i]); ?></span>
                                                    <span class="ep-text-muted"><?php esc_html_e('mandatory', 'eventprime-event-woocommerce-integration'); ?></span>
                                                </div>
                                        <?php } else { ?>
                                                <div class="ep-d-flex ep-text-small">
                                                    <span class="ep-mr-1">x <?php echo esc_attr($product_qty[$i]); ?></span>
                                                    <span class="ep-text-muted"></span>
                                                </div>
                                        <?php } ?>
                                </div>
                            </div>
                            <div class="ep-box-col-4 ep-text-end"><?php echo esc_attr($currency_symbol . $product_qty[$i] * $value['price']); ?></div>
                        </div>
                        <input type="hidden" value="<?php echo esc_attr($productid); ?>" name="woocommerce_products[]">
                        <input type="hidden" value="<?php echo esc_attr($value['name']); ?>" name="product_name">
                        <input type="hidden" value="<?php echo esc_attr($value['price']); ?>" name="product_price">
                        <input type="hidden" value="<?php echo esc_attr($product_qty[$i]); ?>" name="woocommerce_products_qty[]">
                        <input type="hidden" value="<?php echo esc_attr($variation_ids[$i]); ?>" name="woocommerce_products_variation_id[]">
                        <input type="hidden" value="<?php echo isset($variation_attr[$i]) ? $variation_attr[$i] : $productid; ?>" name="woocommerce_products_variation_attr[]">

                        <?php
                        $product_total_price = $product_total_price + $product_qty[$i] * $value['price'];
                        $i++;
                    }
                    ?>
                    <input type="hidden" value="<?php echo esc_attr($product_total_price); ?>" id="ep_wc_product_total" name="ep_wc_product_total">
                    <?php
                    $extra = array();
                    $extra['product_total_price'] = $product_total_price;
                    $extra['woocommerce_integration'] = true;
                    
                    $response['success'] = true;
                    $response['html'] = ob_get_clean();
                    $response['product_price'] = $product_total_price;
                    $response['total_price_block'] = $this->update_total_price_block($total_price, $total_tickets, $event_id,$extra);
                    $response = apply_filters('ep_wci_extend_product_selection_update_success_response', $response, $total_price, $total_tickets,$event_id,$extra);
                }
            }

            return $response;
        }
    }

    public function update_total_price_block_old($total_price, $total_tickets, $product_total_price) {
        $ep_functions = new Eventprime_Basic_Functions();
        $inital_total_price = $total_price;
        $total_price = $total_price + $product_total_price;
        ob_start();
        ?>
        <div class="ep-box-row ep-py-2 ep-fs-5">
            <div class="ep-box-col-6 fw-bold">
                <?php esc_html_e('Total', 'eventprime-event-woocommerce-integration'); ?>
            </div>
            <div class="ep-box-col-6 ep-text-end ep-fw-bold">
        <?php echo esc_html($ep_functions->ep_price_with_position($total_price)); ?>
                <input type="hidden" name="ep_event_booking_total_price" value="<?php echo esc_attr($total_price); ?>" />
                <input type="hidden" name="ep_event_initial_booking_total_price" value="<?php echo esc_attr($inital_total_price); ?>" />
                <input type="hidden" name="ep_event_booking_total_tickets" value="<?php echo absint($total_tickets); ?>" />
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function update_total_price_block($total_price, $total_tickets, $event_id,$extra) {
        $html_generator = new Eventprime_html_Generator;
        ob_start();
        $html_generator->eventprime_checkout_total_html($total_price,$total_tickets,$event_id,$extra);   
        return ob_get_clean();
    }

    public function format_woocommerce_cart_products($data) {
        $woocommerce_data = array();
        if (isset($this->allow_woocommerce_integration) && $this->allow_woocommerce_integration == 1) {
            $products = $this->load_event_product($data['ep_event_booking_event_id']);
            if (!empty($data['woocommerce_products']) && count($data['woocommerce_products']) > 0 && !empty($data['woocommerce_products_qty']) && !empty($data['woocommerce_products_variation_id']) && !empty($data['ep_product_variation_attr'])) {
                $i = 0;
                $cart_product = (object) $data['woocommerce_products'];
                foreach ($cart_product as $productid) {
                    if (isset($productid)) {
                        if (isset($products[$productid])) {
                            $pdata = array();
                            $pdata = $products[$productid];
                            $pdata['id'] = $productid;
                            $pdata['qty'] = $data['woocommerce_products_qty'][$i];
                            $price = $products[$productid]['price'];
                            // calculate price if there is any variation id
                            if ($data['woocommerce_products_variation_id'][$i] != $productid && $data['woocommerce_products_variation_id'][$i] > 0) {
                                $variable_product = wc_get_product($data['woocommerce_products_variation_id'][$i]);
                                $variation_image = $variable_product->get_image(array(100, 100));
                                // $pdata['variation_id'] = $data['woocommerce_products_variation_id'][$i];
                                $pdata['image'] = $variation_image;
                                $price = $variable_product->get_price();
                                $pdata['price'] = $price;
                            }
                            $subtotal = $price * $data['woocommerce_products_qty'][$i];
                            $pdata['sub_total'] = number_format($subtotal, 2);
                            // check if variation exists
                            if (!is_numeric($data['ep_product_variation_attr'][$i])) {
                                $pdata['variation'] = array();
                                foreach ((array) $data['ep_product_variation_attr'][$i] as $single_attr) {
                                    // $pdata['variation']['variation_id'] = $data['woocommerce_products_variation_id'][$i];
                                    $pdata['variation'] = json_decode($single_attr);
                                }
                            }

                            $woocommerce_data[] = (object) $pdata;
                        }
                    }
                    $i++;
                }
            }
        }
        //epd($woocommerce_data);
        return $woocommerce_data;
    }

    public function format_woocommerce_billing_address($data) {
        $billing_address = array();
        if (isset($this->allow_woocommerce_integration) && $this->allow_woocommerce_integration == 1) {
            if (isset($data) && !empty($data)) {
                $billing_address['billing_first_name'] = isset($data['billing_first_name']) ? $data['billing_first_name'] : '';
                $billing_address['billing_last_name'] = isset($data['billing_last_name']) ? $data['billing_last_name'] : '';
                $billing_address['billing_company'] = isset($data['billing_company']) ? $data['billing_company'] : '';
                $billing_address['billing_country'] = isset($data['billing_country']) ? $data['billing_country'] : '';
                $billing_address['billing_address_1'] = isset($data['billing_address_1']) ? $data['billing_address_1'] : '';
                $billing_address['billing_address_2'] = isset($data['billing_address_2']) ? $data['billing_address_2'] : '';
                $billing_address['billing_city'] = isset($data['billing_city']) ? $data['billing_city'] : '';
                $billing_address['billing_state'] = isset($data['billing_state']) ? $data['billing_state'] : '';
                $billing_address['billing_postcode'] = isset($data['billing_postcode']) ? $data['billing_postcode'] : '';
                $billing_address['billing_phone'] = isset($data['billing_phone']) ? $data['billing_phone'] : '';
                $billing_address['billing_email'] = isset($data['billing_email']) ? $data['billing_email'] : '';
            }
        }

        return $billing_address;
    }

    public function format_woocommerce_shipping_address($data) {
        $shipping_address = array();
        if (isset($this->allow_woocommerce_integration) && $this->allow_woocommerce_integration == 1) {
            if (isset($data) && !empty($data)) {
                if (isset($data['address_option']) && $data['address_option'] == 'diff') {
                    $shipping_address['shipping_first_name'] = isset($data['shipping_first_name']) ? $data['shipping_first_name'] : '';
                    $shipping_address['shipping_last_name'] = isset($data['shipping_last_name']) ? $data['shipping_last_name'] : '';
                    $shipping_address['shipping_company'] = isset($data['shipping_company']) ? $data['shipping_company'] : '';
                    $shipping_address['shipping_country'] = isset($data['shipping_country']) ? $data['shipping_country'] : '';
                    $shipping_address['shipping_address_1'] = isset($data['shipping_address_1']) ? $data['shipping_address_1'] : '';
                    $shipping_address['shipping_address_2'] = isset($data['shipping_address_2']) ? $data['shipping_address_2'] : '';
                    $shipping_address['shipping_city'] = isset($data['shipping_city']) ? $data['shipping_city'] : '';
                    $shipping_address['shipping_state'] = isset($data['shipping_state']) ? $data['shipping_state'] : '';
                    $shipping_address['shipping_postcode'] = isset($data['shipping_postcode']) ? $data['shipping_postcode'] : '';
                    $shipping_address['shipping_phone'] = isset($data['shipping_phone']) ? $data['shipping_phone'] : '';
                    $shipping_address['shipping_email'] = isset($data['shipping_email']) ? $data['shipping_email'] : '';
                }
                if (isset($data['address_option']) && $data['address_option'] == 'same') {
                    $shipping_address['shipping_first_name'] = isset($data['billing_first_name']) ? str_replace('billing_', 'shipping_', $data['billing_first_name']) : '';
                    $shipping_address['shipping_last_name'] = isset($data['billing_last_name']) ? str_replace('billing_', 'shipping_', $data['billing_last_name']) : '';
                    $shipping_address['shipping_company'] = isset($data['billing_company']) ? str_replace('billing_', 'shipping_', $data['billing_company']) : '';
                    $shipping_address['shipping_country'] = isset($data['billing_country']) ? str_replace('billing_', 'shipping_', $data['billing_country']) : '';
                    $shipping_address['shipping_address_1'] = isset($data['billing_address_1']) ? str_replace('billing_', 'shipping_', $data['billing_address_1']) : '';
                    $shipping_address['shipping_address_2'] = isset($data['billing_address_2']) ? str_replace('billing_', 'shipping_', $data['billing_address_2']) : '';
                    $shipping_address['shipping_city'] = isset($data['billing_city']) ? str_replace('billing_', 'shipping_', $data['billing_city']) : '';
                    $shipping_address['shipping_state'] = isset($data['billing_state']) ? str_replace('billing_', 'shipping_', $data['billing_state']) : '';
                    $shipping_address['shipping_postcode'] = isset($data['billing_postcode']) ? str_replace('billing_', 'shipping_', $data['billing_postcode']) : '';
                    $shipping_address['shipping_phone'] = isset($data['billing_phone']) ? str_replace('billing_', 'shipping_', $data['billing_phone']) : '';
                    $shipping_address['shipping_email'] = isset($data['billing_email']) ? str_replace('billing_', 'shipping_', $data['billing_email']) : '';
                }
            }
        }

        return $shipping_address;
    }

    public function get_checkout_page_billing_block($args) {
        if (isset($this->allow_woocommerce_integration) && $this->allow_woocommerce_integration == 1) {
            wp_enqueue_script('ep-woocommerce-integration-js', plugin_dir_url(dirname(__FILE__)) . 'public/js/ep_woocommerce_integration.js', array(), Eventprime_Woocommerce_Integration_VERSION);
            wp_localize_script('ep-woocommerce-integration-js', 'epwi_ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
            $event_id = $args->event->id;
            if (!empty($event_id)) {
                $products = $this->load_event_product($event_id);
                if (!empty($products)) {
                    // get the user meta
                    $userMeta = get_user_meta(get_current_user_id());
                    $current_user = wp_get_current_user();
                    // get the form fields
                    $countries = new WC_Countries();
                    $billing_fields = $countries->get_address_fields('', 'billing_');

                    $load_address = 'billing';
                    $page_title = __('Billing Address', 'eventprime-event-woocommerce-integration');
                    ?>
                    <div class="ep-woocommerce-checkout-forms ep-mt-5" id="ep-woocommerce-checkout-forms">
                        <div class="ep-woocommerce-billing-address ep-woocommerce-address-form">
                            <form action="/my-account/edit-address/billing/" id="woocommerce-billing-address" class="edit-account" method="post">
                                <div class="ep-woocommerce_form_heading ep-fs-5 ep-fw-bold ep-mb-2"><?php echo apply_filters('woocommerce_my_account_edit_address_title', $page_title); ?></div>

                    <?php do_action("woocommerce_before_edit_address_form_billing"); ?>
                                <?php foreach ($billing_fields as $key => $field) { ?>
                                    <?php
                                    $field['custom_attributes']['value'] = 'billing_address.' . $key;
                                    if ($key == 'billing_country') {
                                        $field['custom_attributes']['onchange'] = 'getWoocommerceCountryState("billing_address", "billing_country", "billing_state")';
                                    }
                                    if ($field['required'] == 1) {
                                        $field['custom_attributes']['data-field_required'] = __($field['label'] . ' is required');
                                    }
                                    $field_value = '';
                                    if (isset($userMeta[$key])) {
                                        $field_value = $userMeta[$key][0];
                                    } else {
                                        if ($key == 'billing_first_name') {
                                            $field_value = ($current_user->user_firstname != '') ? $current_user->user_firstname : '';
                                        }
                                        if ($key == 'billing_last_name') {
                                            $field_value = ($current_user->user_lastname != '') ? $current_user->user_lastname : '';
                                        }
                                        if ($key == 'billing_email') {
                                            $field_value = ($current_user->user_email != '') ? $current_user->user_email : '';
                                            if (!empty($field_value)) {
                                                $field['custom_attributes']['readonly'] = 'true';
                                            }
                                        }
                                    }
                                    woocommerce_form_field($key, $field, $field_value);
                                    ?>
                                    <script>
                                        jQuery('<div class="ep-error-message" id="ep_wci_<?php echo $key; ?>_error"></div>').appendTo('#<?php echo $key . '_field'; ?>');
                                    </script>
                        <?php }
                    ?>
                                <?php do_action("woocommerce_after_edit_address_form_billing"); ?>
                            </form>
                        </div><?php
                            }
                }
        }
    }

    public function get_checkout_page_shipping_block($args) {
        if (isset($this->allow_woocommerce_integration) && $this->allow_woocommerce_integration == 1) {
            wp_enqueue_script('ep-woocommerce-integration-js', plugin_dir_url(dirname(__FILE__)) . 'public/js/ep_woocommerce_integration.js', array(), Eventprime_Woocommerce_Integration_VERSION);
            wp_localize_script('ep-woocommerce-integration-js', 'epwi_ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
            $event_id = $args->event->id;
            if (!empty($event_id)) {
                $products = $this->load_event_product($event_id);
                if (!empty($products)) {
                    // get the user meta
                    $userMeta = get_user_meta(get_current_user_id());
                    $current_user = wp_get_current_user();
                    // get the form fields
                    $countries = new WC_Countries();
                    $shipping_fields = $countries->get_address_fields('', 'shipping_');
                    $load_address = 'shipping';
                    $page_title = __('Shipping Address', 'eventprime-event-woocommerce-integration');
                    ?>
                        <div class="ep-woocommerce-shipping-address ep-woocommerce-address-form difl">

                            <div class="ep-woocommerce_form_heading ep-fs-5 ep-fw-bold ep-mt-5 ep-mb-2"><?php echo apply_filters('woocommerce_my_account_edit_address_title', $page_title); ?></div>
                            <div class="em-pdate-shipping-address-block">
                                <div class="em-pdate-shipping-address-row dbfl">
                                    <label>
                                        <input type="radio" name="address_option" value="same" checked>
                    <?php esc_html_e('Same as Billing Address', 'eventprime-event-woocommerce-integration'); ?>
                                    </label>
                                </div>
                                <div class="em-pdate-shipping-address-row dbfl">
                                    <label>
                                        <input type="radio" name="address_option" value="diff" >
                    <?php esc_html_e('Different from Billing Address', 'eventprime-event-woocommerce-integration'); ?>
                                    </label>
                                </div>
                            </div>
                            <div class="em-order-shipping-address-block" style="display:none">

                    <?php do_action("woocommerce_before_edit_address_form_shipping"); ?>
                    <?php foreach ($shipping_fields as $key => $field) : ?>
                                    <?php
                                    $field['custom_attributes']['value'] = 'shipping_address.' . $key;
                                    if ($key == 'shipping_country') {
                                        $field['custom_attributes']['onchange'] = 'getWoocommerceCountryState("shipping_address", "shipping_country", "shipping_state")';
                                    }
                                    if ($field['required'] == 1) {
                                        $field['custom_attributes']['data-field_required'] = __($field['label'] . ' is required');
                                    }
                                    $field_value = '';
                                    if (isset($userMeta[$key])) {
                                        $field_value = $userMeta[$key][0];
                                    } else {
                                        if ($key == 'shipping_first_name') {
                                            $field_value = ($current_user->user_firstname != '') ? $current_user->user_firstname : '';
                                        }
                                        if ($key == 'shipping_last_name') {
                                            $field_value = ($current_user->user_lastname != '') ? $current_user->user_lastname : '';
                                        }
                                        if ($key == 'shipping_email') {
                                            $field_value = $current_user->user_email;
                                            if (!empty($field_value)) {
                                                $field['custom_attributes']['readonly'] = 'true';
                                            }
                                        }
                                    }
                                    woocommerce_form_field($key, $field, $field_value);
                                    ?>
                                    <script>
                                        jQuery('<div class="ep-error-message" id="ep_wci_<?php echo $key; ?>_error"></div>').appendTo('#<?php echo $key . '_field'; ?>');
                                    </script>
                    <?php endforeach; ?>
                                <?php do_action("woocommerce_after_edit_address_form_shipping"); ?>
                            </div>

                        </div>
                    </div><?php
                }
            }
        }
    }

    // products details on booking detail page
    public function front_user_booking_item_details($args) {
        if (!empty($args) && !empty($args->em_id) && !empty($args->em_order_info['woocommerce_products'])) {
            $ep_functions = new Eventprime_Basic_Functions();
            $woocommerce_products = $args->em_order_info['woocommerce_products'];
            ?>
            <div class="ep-box-col-12 ep-border ep-rounded ep-mt-5 ep-bg-white">
                <div class="ep-box-row ep-border-bottom">
                    <div class="ep-box-col-12 ep-py-4 ep-ps-4 ep-fw-bold ep-text-uppercase ep-text-small"><?php esc_html_e('Products', 'eventprime-event-woocommerce-integration'); ?></div>
                </div>
                <div class="ep-box-row">
                    <div class="ep-box-col-12 ep-p-4">
                        <table class="ep-table ep-table-hover ep-text-small ep-table-borderless ep-ml-4 ep-text-start">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col"><?php esc_html_e('Image', 'eventprime-event-woocommerce-integration'); ?></th>
                                    <th scope="col"><?php esc_html_e('Name', 'eventprime-event-woocommerce-integration'); ?></th>
                                    <th scope="col"><?php esc_html_e('Quantity', 'eventprime-event-woocommerce-integration'); ?></th>
                                    <th scope="col"><?php esc_html_e('Price', 'eventprime-event-woocommerce-integration'); ?></th>
                                    <th scope="col"><?php esc_html_e('Total', 'eventprime-event-woocommerce-integration'); ?></th>
                                </tr>
                            </thead>
                            <tbody class="">
                                <?php
                                $i = 1;
                                foreach ($woocommerce_products as $woo) {
                                    // get product permalink
                                    $product_permalink = wc_get_product($woo->id);
                                    $single_product_detail_url = $product_permalink->get_permalink();
                                    ?>
                                    <tr>
                                        <th scope="row" class="py-3"><?php echo $i; ?></th>
                                        <td class="py-3">
                                            <?php echo $woo->image; ?>
                                        </td>
                                        <td class="py-3">
                                            <?php
                                            $variation_name = "";
                                            if (isset($woo->variation) && !empty($woo->variation)) {
                                                //     if (isset($woo->variation[0]->attr_label) && isset($woo->variation[0]->attr_value)) {
                                                //         echo '<p>' . $woo->variation[0]->attr_label . ' : ' . ucfirst($woo->variation[0]->attr_value) . '</p>';
                                                //     }
                                                //     if (isset($woo->variation[1]->attr_label) && isset($woo->variation[1]->attr_value)) {
                                                //         echo '<p>' . $woo->variation[1]->attr_label . ' : ' . ucfirst($woo->variation[1]->attr_value) . '</p>';
                                                //     }
                                                $variation_id = isset($woo->variation[0]->variation_id) && !empty($woo->variation[0]->variation_id) ? $woo->variation[0]->variation_id : ''; 
                                                $variation = new WC_Product_Variation($variation_id);
                                                $variation_name = $variation->get_name();
                                            }
                                            ?>
                                            <a href="<?php echo esc_url($single_product_detail_url); ?>" target="_blank" ><?php echo !empty( $variation_name ) ? esc_html( $variation_name ) : $woo->name; ?></a>
                                        </td>
                                        <td class="py-3"><?php echo $woo->qty; ?></td>
                                        <td class="py-3"><?php echo esc_html($ep_functions->ep_price_with_position($woo->price)); ?></td>
                                        <td class="py-3">
                                            <?php
                                            $subTotal = $woo->price * $woo->qty;
                                            echo esc_html($ep_functions->ep_price_with_position($subTotal));
                                            ?>
                                        </td>
                                    </tr>

                                    <?php
                                    $i++;
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php
        }
    }

    // add new woocommerce order
    public function add_new_woocommerce_order($order_id, $data) {
        if (isset($order_id) && !empty($order_id) && isset($data) && !empty($data)) {
            $booking_controller = new Eventprime_Basic_Functions();
            $booking = $booking_controller->load_booking_detail($order_id);

            if ( isset($booking->em_order_info['ep_wc_checkout_booking']) && !empty($booking->em_order_info['ep_wc_checkout_booking']) ) {
                return;
            }

            $price = $booking->em_order_info['booking_total'];
            $woocommerce_products = isset($booking->em_order_info['woocommerce_products']) ? $booking->em_order_info['woocommerce_products'] : '';

            if ( isset($woocommerce_products) && !empty( $woocommerce_products ) ) {
                $order = wc_create_order();

                foreach ($woocommerce_products as $key => $value) {
                    $prod_id = $value->id;

                    if (isset($value->variation) && !empty($value->variation)) {
    
                        $prod_variation = $value->variation;
                        if (!empty($prod_variation)) {
                            
                            $args = array();
                            $variation_id = '';
                            foreach ($prod_variation as $pvars) {
                                if ($pvars->product_id == $prod_id) {
                                    $args[$pvars->attribute] = $pvars->value;
                                    $variation_id = $pvars->variation_id;
                                }
                            }
                            if (!empty($variation_id)) {
                                $varProduct = new WC_Product_Variation($variation_id);
                                $order->add_product($varProduct, $value->qty, $args);
                            }
                        }
                    } else {
                        $order->add_product(wc_get_product($prod_id), $value->qty);
                    }
    
                    $price = $price;
                }
                $billing_address = $shipping_address = array();
                if (isset($booking->em_order_info['billing_address']) && !empty($booking->em_order_info['billing_address'])) {
                    foreach ($booking->em_order_info['billing_address'] as $key => $value) {
                        // update user billing meta
                        update_user_meta(get_current_user_id(), $key, $value);
                        $keyname = str_replace("billing_", '', $key);
                        $billing_address[$keyname] = $value;
                    }
                }
                if (isset($booking->em_order_info['shipping_address']) && !empty($booking->em_order_info['shipping_address'])) {
                    foreach ($booking->em_order_info['shipping_address'] as $key => $value) {
                        // update user billing meta
                        update_user_meta(get_current_user_id(), $key, $value);
                        $keyname = str_replace("shipping_", '', $key);
                        $shipping_address[$keyname] = $value;
                    }
                }
                $order->set_address($billing_address, 'billing');
                $order->set_address($shipping_address, 'shipping');
                // $order->set_payment_method($booking->em_order_info['payment_gateway']);
                $order->set_payment_method($booking->em_payment_method);
                if ($booking->em_payment_method == 'paypal' || $booking->em_payment_method == 'stripe') {
                    $order->update_status('completed');
                } else {
                    $order->update_status('pending');
                }
                $order->set_customer_ip_address(WC_Geolocation::get_ip_address());
                $order->set_customer_user_agent(wc_get_user_agent());
                                
                $order->calculate_totals();

                // event prime booking id
                $order->update_meta_data('em_booking_id', $booking->em_id);
                $order->save();
                $order_id = $order->get_id();
                // save order id in ep booking order info
                $ep_order_info = $booking->em_order_info;
                $ep_order_info['woocommerce_order_id'] = $order_id;
    
                update_post_meta($booking->em_id, 'em_order_info', $ep_order_info);
                update_post_meta($order_id, '_customer_user', get_current_user_id());
            }

        }
    }
}
