<?php
    /**
     * Event Woocommerce Integration panel html
     */
    defined( 'ABSPATH' ) || exit;
    $ep_functions = new Eventprime_Basic_Functions();
    $selectd_products = metadata_exists('post',$post->ID,'em_selectd_products') ? get_post_meta($post->ID,'em_selectd_products',true) : '';
    $enable_product          = metadata_exists('post',$post->ID,'em_enable_product') ? absint(get_post_meta($post->ID,'em_enable_product',true)) : 0;
    $allow_update_quantity = metadata_exists('post',$post->ID,'em_allow_update_quantity') ? absint(get_post_meta($post->ID,'em_allow_update_quantity',true)) : 0;
    $multiply_product_quantity = metadata_exists('post',$post->ID,'em_multiply_product_quantity') ? absint(get_post_meta($post->ID,'em_multiply_product_quantity',true)) : 0;
    $display_combined_cost = metadata_exists('post',$post->ID,'em_display_combined_cost') ? absint(get_post_meta($post->ID,'em_display_combined_cost',true)) : 0;
?>
<div id="ep_event_woocommerce_data" class="panel ep_event_options_panel">
   
    <div class="ep-box-wrap ep-my-3">
        <div class="rm-box-row">
            <div class="ep-box-col-4 ep-d-flex ep-items-center">
                <div class="form-check form-check-inline">
                    <input type="checkbox" name="enable_product" id="enable_product" value="1" <?php if( absint( $enable_product ) == 1 ) { echo 'checked="checked'; }?> >
                    <label class="form-check-label" for="enable_product"><?php esc_html_e( 'Enable product', 'eventprime-woocommerce-integration' ); ?></label>
                </div>
            </div> 
        </div>

        <div id="ep_show_event_products" style="<?php echo isset( $enable_product ) && $enable_product == 1 ? '' : 'display:none;';?>">
            <div class="ep-meta-box-section">
                <div class="ep-box-row ep-mt-3 ep-items-end">

                    <div class="ep-woo-product-btn">
                        <button type="button" class="ep-add-product add_product button button-primary button-large" ><?php _e('Add Product', 'eventprime-woocommerce-integration'); ?></button>
                    </div>
                    
                    <?php if( ! empty( $selectd_products ) ) { 
                        foreach ( $selectd_products as $pkey => $pvalue ) {
                            
                            $pvalue = (array)$pvalue;
                            $product_id = intval($pvalue['product']);
                            $prod_selected_categories = isset($pvalue['selected_categories']) && !empty($pvalue['selected_categories']) ? $pvalue['selected_categories'] : []; 
                            // $pdetail = wc_get_product( $product_id ); 
                            if ( !empty($prod_selected_categories) ) {
                                $selected_cat_products = [];
                                $args = array(
                                    'numberposts' => -1,
                                    'post_status' => 'publish',
                                    'category'  => $prod_selected_categories,
                                    'meta_query' => array(
                                        array(
                                            'key'     => 'ep_event_product', 
                                            'value'   => '1', 
                                            'compare' => '!=', 
                                        ),
                                    ),
                                );
                                $wc_products = wc_get_products( $args );
                                if( !empty($wc_products) ) {
                                    foreach ( $wc_products as $key => $value ) {
                                        if ( $value->get_id() == $ep_functions->ep_get_global_settings('ep_wc_product_id') ) {
                                            continue;
                                        }
                                        $selected_cat_products[] = array( 'id' => $value->get_id(), 'name' => $value->get_name() );
                                    }
                                }
                                $products = $selected_cat_products;
                            }
                            if( isset( $products ) && ! empty( $products ) ) { ?>
                                <div class="ep-box-col-12 ep-childfieldsrow ep-woo-product-block" id="tab_<?php echo $pkey;?>">
                                    <div class="ep-box-col-12 ep-mt-3 ep-event-wc-category-select-wrap">
                                        <label class="ep-form-label ep-my-1"><?php esc_html_e( 'Select Category', 'eventprime-woocommerce-integration' ); ?></label>
                                        <select multiple name="search_category_<?php echo $pkey;?>[]" class="ep-form-control search_category" id="search_category_<?php echo $pkey;?>" onchange="changeCategory(<?php echo $pkey;?>)">
                                        <?php if( ! empty( $categories ) ){
                                            foreach ( $categories as $key => $value ) {?>
                                                <option value="<?php echo $value->slug;?>" <?php if ( is_array($prod_selected_categories) && in_array($value->slug, $prod_selected_categories)) echo esc_html(' selected'); ?>><?php echo $value->name;?></option>
                                                <?php }
                                            }
                                        ?>
                                        </select>
                                    </div>
                                    <div class="ep-box-col-12 ep-mt-3 ep-event-wc-product-select-wrap">
                                        <label class="ep-form-label ep-my-1 ep-d-flex ep-align-items-center ep-content-left">
                                            <?php esc_html_e( 'Select Product', 'eventprime-woocommerce-integration' ); ?>
                                            <span class="spinner"></span>
                                        </label>
                                        <select name="woocommerce_product[<?php echo $pkey;?>]" class="ep-form-control">
                                            <option value=""><?php esc_html_e("Select Product", "eventprime-woocommerce-integration") ?> </option>
                                            <?php  
                                            foreach( $products as $product ){ ?>
                                                <option value="<?php echo $product['id']; ?>" <?php if ($pvalue['product'] == $product['id']) echo esc_html(' selected'); ?> ><?php echo $product['name']; ?></option>
                                                <?php 
                                            } 
                                            ?> 
                                        </select>
                                    </div>

                                    <div class="ep-box-col-12 ep-mt-3 ep-d-flex ep-items-center" >
                                        <label class="form-check form-check-inline ep-mr-2">
                                            <?php esc_html_e('Is Purchase Mandatory', 'eventprime-woocommerce-integration'); ?>
                                        </label>
                                        <div class="ep-show-weekly-options">
                                            <label class="form-check form-check-inline ep-mr-2">
                                                <input type="checkbox" name="purchase_mendatory[<?php echo $pkey;?>]" value="1" <?php if( absint( $pvalue['purchase_mendatory'] ) == 1 ) { echo 'checked="checked"'; }?>>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- <div class="ep-woo-product-btn"> -->
                                        <button class="ep-mt-3 ep-remove-product remove_product" id="rem_<?php echo $pkey;?>"> <?php _e('Remove', 'eventprime-woocommerce-integration'); ?> </button>
                                    <!-- </div> -->   
                                </div>
                                <?php
                            }
                        } 
                    } else { ?>
                        <div class="ep-box-col-12 ep-childfieldsrow ep-woo-product-block" id="tab_0">
                            <div class="ep-box-col-12 ep-mt-3 ep-event-wc-category-select-wrap">
                                <label class="ep-form-label ep-my-1"><?php esc_html_e( 'Select Category', 'eventprime-woocommerce-integration' ); ?></label>
                                <select multiple name="search_category_0[]" class="ep-form-control search_category" id="search_category_0" onchange="changeCategory(0)">
                                <?php if( ! empty( $categories ) ){
                                    foreach ( $categories as $key => $value ) {?>
                                        <option value="<?php echo $value->slug;?>"><?php echo $value->name;?></option>
                                        <?php }
                                    }
                                ?>
                                </select>
                            </div>
                            
                            <div class="ep-box-col-12 ep-mt-3 ep-event-wc-product-select-wrap">
                                <label class="ep-form-label ep-my-1 ep-d-flex ep-align-items-center ep-content-left">
                                    <?php esc_html_e( 'Select Product', 'eventprime-woocommerce-integration' ); ?>
                                    <span class="spinner"></span>
                                </label>
                                <select name="woocommerce_product[0]" class="ep-form-control">
                                <option value=""><?php esc_html_e( 'Select Product', 'eventprime-woocommerce-integration' ); ?></option>
                                    <?php if( isset( $products ) && ! empty( $products ) ) { 
                                        foreach( $products as $product ){ 
                                            if ( $product['id'] == $ep_functions->ep_get_global_settings('ep_wc_product_id') ) {
                                                continue;
                                            }?>
                                            <option value="<?php echo $product['id']; ?>"><?php echo $product['name']; ?></option>
                                            <?php 
                                        } 
                                    } ?>    
                                </select>
                            </div>

                            <div class="ep-box-col-12 ep-mt-3 ep-d-flex ep-items-center" >
                                <label class="form-check form-check-inline ep-mr-2">
                                    <?php esc_html_e('Is Purchase Mandatory', 'eventprime-woocommerce-integration'); ?>
                                </label>
                                <div class="ep-show-weekly-options">
                                    <label class="form-check form-check-inline ep-mr-2">
                                        <input type="checkbox" name="purchase_mendatory[0]" value="1">
                                    </label>
                                </div>
                            </div>

                            <!-- <div class="ep-woo-product-btn"> -->
                                <!-- <button class="ep-mt-3 ep-remove-product remove_product" id="rem_0"> <?php _e('Remove', 'eventprime-woocommerce-integration'); ?> </button> -->
                            <!-- </div> -->   
                        </div><?php 
                    } ?>

                    <!-- <div class="ep-woo-product-btn">
                        <button type="button" class="ep-mt-3 ep-add-product add_product" ><?php _e('Add Product', 'eventprime-woocommerce-integration'); ?></button>
                    </div> -->
                    
                    <!-- <div class="ep-box-col-12 ep-mt-3 ep-d-flex ep-items-center" >
                        <label class="form-check form-check-inline ep-mr-2">
                            <?php esc_html_e('Display event and product combined cost', 'eventprime-woocommerce-integration'); ?>
                        </label>
                        <div class="ep-show-weekly-options">
                            <label class="form-check form-check-inline ep-mr-2">
                                <input type="checkbox" name="display_combined_cost" value="1" id="display_combined_cost" <?php echo isset( $display_combined_cost ) && $display_combined_cost == 1 ? 'checked' : ''; ?> >
                            </label>
                        </div>
                    </div> -->

                </div>
            </div>
        </div>
    </div>
</div>