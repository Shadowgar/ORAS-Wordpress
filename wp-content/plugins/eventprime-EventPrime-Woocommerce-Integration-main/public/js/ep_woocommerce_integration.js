(function( $ ) {
    //'use strict';
    $(function() {      

    //   $("#ep_show_wi_edit_product_save_btn").on("click", function() {
    //            var em_event_id = $('#edit_product_event_id').val();
    //            let nonce = $('#_wpnonce').val();
    //            
    //            var qty = document.getElementsByName('ep_product_quantity[]');
    //            var pro_variation_ids = document.getElementsByName('ep_product_variation_id[]');
    //            var pro_variation_attr = document.getElementsByName('ep_product_variation_attr[]');
    //           
    //            var total_price = $("input[name=ep_event_booking_total_price]").val();
    //            var total_tickets = $("input[name=ep_event_booking_total_tickets]").val();
    //            var products_total = $("input[name=ep_wc_product_total]").val();
    //            var product_qty = [];
    //            for (var i = 0; i < qty.length; i++) {
    //                product_qty.push(parseInt(qty[i].value));
    //            }
    //            var variation_ids = [];
    //            for (var j = 0; j < pro_variation_ids.length; j++) {
    //                variation_ids.push(parseInt(pro_variation_ids[j].value)); 
    //            }
    //            var variation_attr = [];
    //            for (var j = 0; j < pro_variation_attr.length; j++) {
    //                variation_attr.push(pro_variation_attr[j].value); 
    //            }
    //
    //            var data = {
    //                action: 'ep_woocommerce_refresh_booking_page_product_block',
    //                security  : nonce,
    //                event_id: em_event_id,
    //                product_qty:product_qty,
    //                variation_ids:variation_ids,
    //                variation_attr:variation_attr,
    //                total_price:total_price,
    //                total_tickets:total_tickets,
    //                products_total:products_total,
    //            };
    //            jQuery('.ep-woocommerce-integration-spinner').addClass('ep-is-active');
    //            jQuery('.ep-woocommerce-integration-spinner').removeClass('ep-position-absolute');
    //            jQuery( '#ep_show_wi_edit_product_save_btn' ).prop('disabled', true);
    //            $.post(epwi_ajax_object.ajax_url, data, function(response) {
    //                // console.log(response);
    //                var modal = jQuery("#ep_show_wi_edit_product_popup");
    //                // jQuery('.ep-woocommerce-integration-spinner').removeClass('is-active');
    //                 $("body").removeClass("ep-modal-open-body");
    //                if( response.data.success == true ) {
    //                jQuery('#ep-woocommerce-products-block').html(response.data.html);
    //                jQuery('#ep-booking-total').html(response.data.total_price_block);
    //                
    //                sessionStorage.setItem('ep_booking_additional_price', response.data.product_price);
    //                if( sessionStorage.getItem( "allow_process_for_payment_step" ) == 1 ){
    //                    loadPaymentSection();
    //                }
    //                jQuery('.ep-woocommerce-integration-spinner').removeClass('ep-is-active');
    //                jQuery('.ep-woocommerce-integration-spinner').addClass('ep-position-absolute');
    //                jQuery( '#ep_show_wi_edit_product_save_btn' ).prop('disabled', false);
    //                modal.hide();
    //            }else{
    //                jQuery('.ep-woocommerce-integration-spinner').removeClass('ep-is-active');
    //                jQuery('.ep-woocommerce-integration-spinner').addClass('ep-position-absolute');
    //                jQuery( '#ep_show_wi_edit_product_save_btn' ).prop('disabled', false);
    //                 modal.hide();
    //            }  
    //                
    //            });
    //         
    //        });

        $("#ep_show_wi_edit_product_save_btn").on("click", function() {
            var coupon = '';
            var coupon_amount = '';
            if($('#ep_coupon_code').val())
            {
                coupon = $('#ep_coupon_code').val();
                coupon_amount = $('#ep_coupon_discount').val();
                remove_coupon_code();
            }
            var em_event_id = $('#edit_product_event_id').val();
            let nonce = $('#_wpnonce').val();
            
            var qty = document.getElementsByName('ep_product_quantity[]');
            var pro_variation_ids = document.getElementsByName('ep_product_variation_id[]');
            var pro_variation_attr = document.getElementsByName('ep_product_variation_attr[]');

            var product_qty = [];
            for (var i = 0; i < qty.length; i++) {
                product_qty.push(parseInt(qty[i].value));
            }
            var variation_ids = [];
            for (var j = 0; j < pro_variation_ids.length; j++) {
                if (pro_variation_ids[j] && pro_variation_ids[j].value) {
                    variation_ids.push(parseInt(pro_variation_ids[j].value)); 
                } else {
                    console.log("Invalid variation ID at index:", j);  // Debugging purpose
                }
            }
            var variation_attr = [];
            for (var j = 0; j < pro_variation_attr.length; j++) {
                variation_attr.push(pro_variation_attr[j].value); 
            }

            // Dynamically calculate total price
            var updatedTotalPrice = 0;
            for (var i = 0; i < qty.length; i++) {
                var variation_id = pro_variation_ids[i] ? pro_variation_ids[i].value : null;
                if (variation_id) {
                    var singlePrice = $('#ep_product_total_price_range_' + variation_id).data('single_product_price');
                    if (!isNaN(singlePrice)) {
                        updatedTotalPrice += singlePrice * qty[i].value;
                    } else {
                        console.log("Invalid price for variation ID:", variation_id);  // Debugging purpose
                    }
                } else {
                    console.log("No variation ID found for index:", i);  // Debugging purpose
                }
            }

            console.log("Updated Total Price:", updatedTotalPrice);

            // Update total price input before sending data
            //var initial_price = $("input[name=ep_event_initial_booking_total_price]").val();
            //$("input[name=ep_event_booking_total_price]").val(initial_price);
            var previous_product_price = $("input[name=ep_wc_product_total]").val();
            $("input[name=ep_wc_product_total]").val(updatedTotalPrice);
            var total_price = $("input[name=ep_event_booking_sub_total_price]").val();
            var total_tickets = $("input[name=ep_event_booking_total_tickets]").val();
            var products_total = $("input[name=ep_wc_product_total]").val();
            
 
            var data = {
                action: 'ep_woocommerce_refresh_booking_page_product_block',
                security: nonce,
                event_id: em_event_id,
                product_qty: product_qty,
                variation_ids: variation_ids,
                variation_attr: variation_attr,
                total_price: total_price, // this now holds the updated total price
                total_tickets: total_tickets,
                products_total: products_total,
                previous_product_price:previous_product_price,
                coupon_amount:coupon_amount
            };

            console.log("Sending Data:", data); // Log data to check what's being sent

            jQuery('.ep-woocommerce-integration-spinner').addClass('ep-is-active');
            jQuery('.ep-woocommerce-integration-spinner').removeClass('ep-position-absolute');
            jQuery('#ep_show_wi_edit_product_save_btn').prop('disabled', true);

            $.post(epwi_ajax_object.ajax_url, data, function(response) {
                console.log("Response:", response); // Log the response for debugging
                var modal = jQuery("#ep_show_wi_edit_product_popup");
                $("body").removeClass("ep-modal-open-body");
                if (response.data.success == true) {
                    jQuery('#ep-woocommerce-products-block').html(response.data.html);
                    //console.log(response.data.total_price_block);
                    jQuery('#ep-booking-total .ep-ticket-total-price-section').html(response.data.total_price_block);
                    if( jQuery('.ep-tax-amount-charged').length ) {
                        jQuery('.ep-tax-amount-charged').html(response.data.updated_tax_amount_block);
                    }
                    if(coupon!='')
                    {
                        jQuery('#ep-coupon-field').val(coupon);
                        apply_coupon_code('');
                    }
                    sessionStorage.setItem('ep_booking_additional_price', response.data.product_price);

                    if ( response.data.product_price == 0 ) {
                        $('.ep-woocommerce-billing-address').hide();
                        $('.ep-woocommerce-shipping-address').hide();
                    } else {
                        $('.ep-woocommerce-billing-address').fadeIn();
                        $('.ep-woocommerce-shipping-address').fadeIn();
                    }

                    if (sessionStorage.getItem("allow_process_for_payment_step") == 1) {
                        loadPaymentSection();
                    }
                    jQuery('.ep-woocommerce-integration-spinner').removeClass('ep-is-active');
                    jQuery('.ep-woocommerce-integration-spinner').addClass('ep-position-absolute');
                    jQuery('#ep_show_wi_edit_product_save_btn').prop('disabled', false);

                    modal.hide();
                } else {
                    jQuery('.ep-woocommerce-integration-spinner').removeClass('ep-is-active');
                    jQuery('.ep-woocommerce-integration-spinner').addClass('ep-position-absolute');
                    jQuery('#ep_show_wi_edit_product_save_btn').prop('disabled', false);
                    modal.hide();
                }
            });

        });

        $("input[name='address_option']").on('click', function(){
            var check_box_val = $(this).val();
            if( check_box_val == 'same' ){
                $('.em-order-shipping-address-block').css('display','none');
            }
            if( check_box_val == 'diff' ){
                $('.em-order-shipping-address-block').css('display','block');
            }
            
        });

        // decrease product quantity
        $( document ).on( 'click', '.ep_product_minus', function() {
            let product_id = $( this ).data( 'product_id' );
            let is_product_mandatory = $( this ).data( 'is_product_mandatory' );
            let qty = $( '#ep_product_quantity_' + product_id ).val();
            if( qty != 0 ) {
                --qty;
            }
            if( is_product_mandatory == 1 && qty == 0 ){
                alert('This product is mandatory');
                ++qty;
            }
            $( '#ep_product_quantity_' + product_id ).val( qty );
            $('.ep-product-quantity-' + product_id ).html( qty );
            // let single_product_price = $( '#ep_product_total_price_range_'+ product_id ).data( 'single_product_price' );
            let single_product_price = parseFloat(document.getElementById( 'ep_product_total_price_range_'+ product_id ).dataset.single_product_price);
            let product_currency = $( '#ep_product_total_price_range_'+ product_id ).data( 'product_currency' );
            $('#ep_product_total_price_range_'+ product_id ).html( ep_format_price_with_position( single_product_price * qty ) );
        });

        // increase product quantity
        $( document ).on( 'click', '.ep_product_plus', function() {
            let product_id = $( this ).data( 'product_id' );
            let qty = $( '#ep_product_quantity_' + product_id ).val();
            ++qty;
            $( '#ep_product_quantity_' + product_id ).val( qty );
            $('.ep-product-quantity-' + product_id ).html( qty );
            // let single_product_price = $( '#ep_product_total_price_range_'+ product_id ).data( 'single_product_price' );
            let single_product_price = parseFloat(document.getElementById( 'ep_product_total_price_range_'+ product_id ).dataset.single_product_price);
            let product_currency = $( '#ep_product_total_price_range_'+ product_id ).data( 'product_currency' );
            $('#ep_product_total_price_range_'+ product_id ).html( ep_format_price_with_position ( single_product_price * qty ) );
        });

    //        jQuery(".ep-product-variation-select").change(function() {
    //            var id = this.id;
    //            var select_attr = jQuery(this).data("attr");
    //            var select_attr_val = jQuery(this).val();
    //            var ep_product_id = jQuery(this).data("attr_product_id");
    //
    //            var vardata = jQuery("#ep-woocommerce-product-option-popup-model-" + ep_product_id).data("product_variations");
    //            var vardatastr = JSON.parse(JSON.stringify(vardata));
    //            var currency_symbol = jQuery("#ep-woocommerce-product-option-popup-model-" + ep_product_id).data("currency_symbol");
    //            var attr_var = [];
    //            var available_filters = JSON.parse(JSON.stringify(jQuery(".ep-woocommerce-product-available-filters").data("available_filters")));
    //            var crossed = 0;
    //            var selected_data = [];
    //            var variation_index = -1;
    //            jQuery(".ep-product-variation-select-"+ep_product_id).each(function() {
    //                var data_attr = jQuery(this).data("attr");
    //                var data_value = jQuery(this).val();
    //                if (data_attr == select_attr) {
    //                    crossed = 1;
    //                    selected_data[data_attr] = data_value;
    //                    $.each(vardatastr, function(index, item) {
    //                        $.each(item.attributes, function(idx, itemdata) {
    //                            if (selected_data[idx] != "undefined") {
    //                                if (selected_data[idx] == itemdata) {
    //                                    variation_index = index;
    //                                    return false;
    //                                } else {
    //                                    variation_index = -1;
    //                                }
    //                            }
    //                        });
    //                        if (variation_index > -1) {
    //                            return false;
    //                        }
    //                    });
    //                    if (variation_index > -1) {
    //                        if (vardatastr[variation_index]) {
    //                            var final_data = vardatastr[variation_index];
    //                           
    //                            // block start
    //                            var em_event_id = $("#ep-woocommerce-product-option-popup-model-" + ep_product_id).data("event_id");
    //                            var em_product_id = $("#ep-woocommerce-product-option-popup-model-" + ep_product_id).data("product_id");
    //                        
    //                            var currency_symbol = $("#ep-woocommerce-product-option-popup-model-" + ep_product_id).data("currency_symbol");
    //                            var all_attribute_data = {};
    //                            var is_error = 0;
    //                            var error_msg = '';
    //                            $(".ep-product-variation-select-"+ep_product_id).each(function(){
    //                                var last_val = $(this).val();
    //                                var last_label = $(this).data("attr_label");
    //                                var last_attr = $(this).data("attr");
    //                                if(last_val == ""){
    //                                    is_error = 1;
    //                                    error_msg = error_msg + " " + last_label;
    //                                    return false;  
    //                                }
    //                                else{
    //                                    all_attribute_data[last_attr] = {"label" : last_label, "value" : last_val};
    //                                }
    //                            });
    //                            if(is_error == 1){
    //                                $(".ep-product-variation-error-"+ep_product_id).html(error_msg);
    //                                return false;
    //                            }
    //                            else{
    //                                var last_html = "";
    //                                var variation_submit_id = $(".ep-product-variation-block-submit").attr("variation_id");
    //                                var last_attr_data = [];
    //                                var attr_arr = {};
    //                                var att_id = 0;
    //                                $.each(all_attribute_data, function(attrid, valueid){
    //                                    if(last_html != ""){
    //                                        last_html += "<br>";
    //                                    }
    //                                    last_html += "<span>" + valueid.label + " : " + valueid.value + "</span>";
    //                                    attr_arr = {"id" : att_id, "variation_id" : final_data.variation_id, "attribute" : attrid, "value" : valueid.value, "product_id" : em_product_id, "attr_label": valueid.label, "attr_value": valueid.value};
    //                                    last_attr_data.push(attr_arr);
    //                                    att_id++;
    //                                });
    //                            }
    //                            //alert(final_data.display_price);
    //                            var current_product_qty = jQuery('#ep_product_quantity_'+ ep_product_id ).val();
    //                            jQuery("#ep-single-product-block-" + ep_product_id + " #ep_product_price_range_" + ep_product_id ).html(currency_symbol + current_product_qty * final_data.display_price);
    //                            jQuery("#ep-single-product-block-" + ep_product_id + " #ep_product_total_price_range_" + ep_product_id ).attr( 'data-single_product_price', final_data.display_price );
    //                            jQuery("#ep-single-product-block-" + ep_product_id + " #ep_product_total_price_range_" + ep_product_id ).html(currency_symbol + current_product_qty * final_data.display_price);
    //                            jQuery("#ep-single-product-block-" + ep_product_id + " .ep-product-image img").attr("src", final_data.image.thumb_src);
    //                            jQuery("#ep-single-product-block-" + ep_product_id + " .ep-product-image img").attr("srcset", final_data.image.srcset);
    //        
    //                            jQuery("#ep-single-product-block-" + ep_product_id + " input[name='ep_product_variation_id[]']").val(final_data.variation_id);
    //                            jQuery("#ep-single-product-block-" + ep_product_id + " input[name='ep_product_variation_attr[]']").val(JSON.stringify(last_attr_data));
    //                            jQuery("#ep-woocommerce-product-option-popup-model-" + ep_product_id).attr("data-final_price", final_data.display_price);
    //                        }
    //                    }
    //                } else if (crossed == 0) {
    //                    selected_data[data_attr] = data_value;
    //                } else {
    //                    var next_option = vardatastr[variation_index].attributes;
    //                    var next_option_val = next_option[data_attr];
    //                    var attrid = data_attr.replace("attribute_", "");
    //                    $("#" + attrid).val("");
    //                    if (next_option_val != "") {
    //                        $("#" + attrid + " option").each(function() {
    //                            var opval = $(this).val();
    //                            if (opval != "" && opval != next_option_val) {
    //                                $(this).remove();
    //                            }
    //                        });
    //                    } else {
    //                        $.each(available_filters[attrid], function(fid, fval) {
    //                            if ($("#" + attrid + " option[value=" + fid + "]").length < 1) {
    //                                $("#" + attrid).append(new Option(fval, fid))
    //                            }
    //                        });
    //                    }
    //                }
    //            });
    //        });
    //        
        

        jQuery(".ep-product-variation-select").change(function() {
            var ep_product_id = jQuery(this).data("attr_product_id");
            var vardata = jQuery("#ep-woocommerce-product-option-popup-model-" + ep_product_id).data("product_variations");
            var currency_symbol = jQuery("#ep-woocommerce-product-option-popup-model-" + ep_product_id).data("currency_symbol");
            var selected_data = {};
            var variation_index = -1;
            var all_attributes = jQuery("#ep-woocommerce-product-option-popup-model-" + ep_product_id).data("available_attributes");

            // Collect selected attribute values
            jQuery(".ep-product-variation-select-" + ep_product_id).each(function() {
                var attr_name = jQuery(this).attr("name"); 
                selected_data[attr_name] = jQuery(this).val();
            });

            // Find the matching variation based on selected attributes
            let variationAttributes = [];
            $.each(vardata, function(index, variation) {
                var matched = true;
                variationAttributes.push(variation.attributes);
                $.each(variation.attributes, function(attr_name, attr_value) {
                    if (selected_data[attr_name] !== attr_value) {
                        matched = false;
                        return false;
                    }
                });
                if (matched) {
                    variation_index = index;
                    return false;
                }
            });

            // console.log("Selected Data", selected_data);
            // console.log("All Attributes", all_attributes);
            // console.log(variationAttributes);
            // ==================================================*****=============================================================================== 
            // Get valid options for other attributes based on the filtered variations
            var valid_options = {};

            // Loop through all attributes (color, length, material)
            for (let rawAttr in all_attributes) {
                const attr = rawAttr.toLowerCase(); // Normalize for comparison
                valid_options[attr] = new Set();

                let temp_selection = { ...selected_data };
                delete temp_selection["attribute_" + attr]; // Remove current attr from selection
                // console.log(temp_selection)

                vardata.forEach(function(variation) {
                    let is_match = true;

                    for (let key in temp_selection) {
                        const selected_val = temp_selection[key];
                        // console.log(selected_val)
                        if (selected_val === "") continue;

                        const variation_val = variation.attributes[key];

                        if (
                            variation_val === null ||
                            variation_val === undefined ||
                            variation_val != selected_val
                        ) {
                            is_match = false;
                            break;
                        }
                    }

                    // console.log(is_match)
                    if (is_match) {
                        const val = variation.attributes["attribute_" + attr];
                        if (val !== null && val !== undefined) {
                            valid_options[attr].add(val);
                        }
                    }
                });
            }


            // console.log(valid_options)
            // Update the other select elements with valid options
            jQuery(".ep-product-variation-select-" + ep_product_id).each(function() {
                var attr_name = jQuery(this).attr("name"); // 'attribute_color', etc.
                var raw_attr = attr_name.replace("attribute_", "").toLowerCase(); 

                // Skip if no valid options found (e.g., for the changed dropdown)
                if (!valid_options[raw_attr]) return;

                var $select = jQuery(this);
                var current_val = $select.val();
                var options_html = '<option value="">Select</option>';

                valid_options[raw_attr].forEach(function(opt_val) {
                    var selected = (opt_val === current_val) ? 'selected' : '';
                    options_html += '<option value="' + opt_val + '" ' + selected + '>' + opt_val + '</option>';
                });

                $select.empty(); // Clear old options
                $select.append(options_html); // Add new valid options
            });

            // Check if any select has a non-default value
            let anySelected = false;
            jQuery(".ep-product-variation-select-" + ep_product_id).each(function() {
                if (jQuery(this).val() !== "") {
                    anySelected = true;
                    return false; // break loop
                }
            });

            // Toggle the clear button
            let $clearBtn = jQuery("#ep-single-product-block-" + ep_product_id + " .ep-clear-variations-" + ep_product_id);
            if (anySelected) {
                $clearBtn.show();
            } else {
                $clearBtn.hide();
            }

            jQuery(document).on('click', '.ep-clear-variations-' + ep_product_id, function() {
                // Reset all selects to default
                jQuery(".ep-product-variation-select-" + ep_product_id).each(function() {
                    jQuery(this).val('');
                }).trigger('change'); // Trigger change so UI updates accordingly

                // Hide the clear button
                jQuery(this).hide();
            });

            // ==================================================*****=============================================================================== 


            if (variation_index > -1) {
                var final_data = vardata[variation_index];

                // Update price
                // console.log(final_data);

                jQuery('#ep_product_quantity_' + ep_product_id).val(1);
                jQuery('#ep_product_quantity_' + ep_product_id).siblings('span').html('1');
                if( jQuery('#ep_product_quantity_' + ep_product_id).parent().siblings().length > 0 ) {
                    jQuery('#ep_product_quantity_' + ep_product_id).parent().siblings().each(function() {
                        jQuery(this).prop('disabled', false);
                    });
                }
                var current_product_qty = jQuery('#ep_product_quantity_' + ep_product_id).val();
                jQuery("#ep_product_total_price_range_" + ep_product_id).html(currency_symbol + current_product_qty * final_data.display_price);
                jQuery("#ep-single-product-block-" + ep_product_id + " #ep_product_total_price_range_" + ep_product_id ).attr( 'data-single_product_price', final_data.display_price );
                jQuery("#ep-single-product-block-" + ep_product_id + " #ep_product_total_price_range_" + ep_product_id ).html(currency_symbol + parseFloat(current_product_qty * final_data.display_price).toFixed(2));
                
                // Update the hidden input fields for variation ID and attributes
                var last_attr_data = [];
                jQuery(".ep-product-variation-select-" + ep_product_id).each(function() {
                    var attr_name = jQuery(this).data("attr");
                    var attr_value = jQuery(this).val();
                    last_attr_data.push({
                        'product_id' : ep_product_id, 
                        'variation_id' : final_data.variation_id,
                        'attribute': attr_name,
                        'value': attr_value
                    });
                });

                // Update the variation ID and attributes
                jQuery("#ep-single-product-block-" + ep_product_id + " input[name='ep_product_variation_id[]']").val(final_data.variation_id);
                jQuery("#ep-single-product-block-" + ep_product_id + " input[name='ep_product_variation_attr[]']").val(JSON.stringify(last_attr_data));

                // Optionally, update data-final_price if needed
                jQuery("#ep-woocommerce-product-option-popup-model-" + ep_product_id).attr("data-final_price", final_data.display_price);

                
                // Update other variation data (e.g., image, attributes)
                jQuery("#ep-single-product-block-" + ep_product_id + " .ep-product-image img").attr("src", final_data.image.thumb_src);
                jQuery("#ep-single-product-block-" + ep_product_id + " .ep-product-image img").attr("srcset", final_data.image.srcset);
            } else {
                console.log("No matching variation found.");
                
                jQuery('#ep_product_quantity_' + ep_product_id).val(0);
                jQuery('#ep_product_quantity_' + ep_product_id).siblings('span').html('0');
                if( jQuery('#ep_product_quantity_' + ep_product_id).parent().siblings().length > 0 ) {
                    jQuery('#ep_product_quantity_' + ep_product_id).parent().siblings().each(function() {
                        jQuery(this).prop('disabled', true);
                    });
                }
                jQuery("#ep-single-product-block-" + ep_product_id + " #ep_product_total_price_range_" + ep_product_id ).html(currency_symbol+'0.00'); 
                jQuery("#ep-single-product-block-" + ep_product_id + " input[name='ep_product_variation_id[]']").val(0);
            }
        });

        // close edit product modal
        $( document ).on( 'click', '#ep_close_wi_edit_product_modal', function() {
            $( '[ep-modal="ep_show_wi_edit_product_popup"]' ).fadeOut(100);
            $( 'body' ).removeClass( 'ep-modal-open-body' );
        });

    });

})( jQuery );


// billing and shipping fields validations
jQuery( function( $ ) {
    // check billing and shipping information on the checkout page
    $( document ).on( 'click', '#ep_woocommerce_integration_checkout_button', function() {
        let ep_wc_error = 0;
        $( '#ep_wci_billing_first_name_error' ).html( '' );
        $( '#ep_wci_billing_last_name_error' ).html( '' );
        $( '#ep_wci_billing_email_error' ).html( '' );
        $( '#ep_wci_billing_phone_error' ).html( '' );
        $( '#ep_wci_billing_postcode_error' ).html( '' );
        $( '#ep_wci_billing_country_error' ).html( '' );
        // $( '#ep_wci_billing_state_error' ).html( '' );
        $( '#ep_wci_billing_city_error' ).html( '' );

        let requireString = get_translation_string( 'required' );
        let invalidEmailString = get_translation_string( 'invalid_email' );
        let invalidPhoneString = get_translation_string( 'invalid_phone' );
        // first name
        let ep_wci_billing_first_name = $( '#billing_first_name' ).val();
        // var billing_first_name_child_count = document.getElementById("billing_first_name_field").childElementCount;
        if( ! ep_wci_billing_first_name ) {
            let requireString = $( '#billing_first_name' ).data('field_required');
            $( '#ep_wci_billing_first_name_error' ).html( requireString );
            ep_wc_error = 1;
        }
       
        // last name
        let ep_wci_billing_last_name = $( '#billing_last_name' ).val();
        if( ! ep_wci_billing_last_name ) {
            let requireString = $( '#billing_last_name' ).data('field_required');
            $( '#ep_wci_billing_last_name_error' ).html( requireString );
            ep_wc_error = 1;
        }

        //email
        let ep_wci_billing_email = $( '#billing_email' ).val();
        if( !ep_wci_billing_email ) {
            let requireString = $( '#billing_email' ).data('field_required');
            $( '#ep_wci_billing_email_error' ).html( requireString );
            ep_wc_error = 1;
        } else{
            if( !is_valid_email( ep_wci_billing_email ) ) {
                $( '#ep_wci_billing_email_error' ).html( invalidEmailString );
                ep_wc_error = 1;
            }
        }

        // phone
        let ep_wci_billing_phone = $( '#billing_phone' ).val();
        if( !ep_wci_billing_phone ) {
            let requireString = $( '#billing_phone' ).data('field_required');
            $( '#ep_wci_billing_phone_error' ).html( requireString );
            ep_wc_error = 1;
        } else{
            // check for invalid phone
            if( !is_valid_phone( ep_wci_billing_phone ) ) {
                $( '#ep_wci_billing_phone_error' ).html( invalidPhoneString );
                ep_wc_error = 1;
            }
        } 

        // post code
        let ep_wci_billing_postcode = $( '#billing_postcode' ).val();
        if( !ep_wci_billing_postcode ) {
            let requireString = $( '#billing_postcode' ).data('field_required');
            $( '#ep_wci_billing_postcode_error' ).html( requireString );
            ep_wc_error = 1;
        }

        // country
        let ep_wci_billing_country = $( '#billing_country' ).val();
        if( !ep_wci_billing_country ) {
            let requireString = $( '#billing_country' ).data('field_required');
            $( '#ep_wci_billing_country_error' ).html( requireString );
            ep_wc_error = 1;
        }

        // state
        // let ep_wci_billing_state = $( '#billing_state' ).val();
        // if( !ep_wci_billing_state ) {
        //     let requireString = $( '#billing_state' ).data('field_required');
        //     $( '#ep_wci_billing_state_error' ).html( requireString );
        //     ep_wc_error = 1;
        // }

        // city
        let ep_wci_billing_city = $( '#billing_city' ).val();
        if( !ep_wci_billing_city ) {
            let requireString = $( '#billing_city' ).data('field_required');
            $( '#ep_wci_billing_city_error' ).html( requireString );
            ep_wc_error = 1;
        }

        var ep_wci_address_option = $("input[name='address_option']:checked").val();
        if( ep_wci_address_option == 'diff' ){
            $( '#ep_wci_shipping_first_name_error' ).html( '' );
            $( '#ep_wci_shipping_last_name_error' ).html( '' );
            $( '#ep_wci_shipping_postcode_error' ).html( '' );
            $( '#ep_wci_shipping_country_error' ).html( '' );
            // $( '#ep_wci_shipping_state_error' ).html( '' );
            $( '#ep_wci_shipping_city_error' ).html( '' );
                // first name
            let ep_wci_shipping_first_name = $( '#shipping_first_name' ).val();
            if( ! ep_wci_shipping_first_name ) {
                let requireString = $( '#shipping_first_name' ).data('field_required');
                $( '#ep_wci_shipping_first_name_error' ).html( requireString );
                ep_wc_error = 1;
            }
        
            // last name
            let ep_wci_shipping_last_name = $( '#shipping_last_name' ).val();
            if( ! ep_wci_shipping_last_name ) {
                let requireString = $( '#shipping_last_name' ).data('field_required');
                $( '#ep_wci_shipping_last_name_error' ).html( requireString );
                ep_wc_error = 1;
            }

            // post code
            let ep_wci_shipping_postcode = $( '#shipping_postcode' ).val();
            if( !ep_wci_shipping_postcode ) {
                let requireString = $( '#shipping_postcode' ).data('field_required');
                $( '#ep_wci_shipping_postcode_error' ).html( requireString );
                ep_wc_error = 1;
            }

            // country
            let ep_wci_shipping_country = $( '#shipping_country' ).val();
            if( !ep_wci_shipping_country ) {
                let requireString = $( '#shipping_country' ).data('field_required');
                $( '#ep_wci_shipping_country_error' ).html( requireString );
                ep_wc_error = 1;
            }

            // state
            // let ep_wci_shipping_state = $( '#shipping_state' ).val();
            // if( !ep_wci_shipping_state ) {
            //     let requireString = $( '#shipping_state' ).data('field_required');
            //     $( '#ep_wci_shipping_state_error' ).html( requireString );
            //     ep_wc_error = 1;
            // }

            // city
            let ep_wci_shipping_city = $( '#shipping_city' ).val();
            if( !ep_wci_shipping_city ) {
                let requireString = $( '#shipping_city' ).data('field_required');
                $( '#ep_wci_shipping_city_error' ).html( requireString );
                ep_wc_error = 1;
            }

        }


        if( ep_wc_error == 1 ) {
            return false;
        } else{
            sessionStorage.setItem( "allow_process_for_payment_step", 1 );
            $( '#ep-woocommerce-checkout-forms' ).hide();
            $( '#ep_woocommerce_integration_checkout_button' ).hide();
            if( sessionStorage.getItem( "allow_process_for_payment_step" ) == 1 ){
                loadPaymentSection();
            }
            let booking_price = jQuery( 'input[name=ep_event_booking_total_price]' ).val();

            if ( booking_price > 0 ) {
                $( '#ep_event_booking_payment_section' ).show();
            } else {
                $( '#ep_event_booking_checkout_btn' ).show();
                $( '#ep_event_booking_checkout_btn' ).html( ep_event_booking.confirm_booking_text );
            }

            let chkUserId = $( 'input[name=ep_event_booking_user_id]' ).val();
            if( chkUserId == 0 ) {
                $( '#ep_event_booking_checkout_user_section' ).hide( 500 );
            }

            // update step
            $( '#ep_booking_step1' ).removeClass( 'ep-bg-warning' );
            $( '#ep_booking_step1' ).addClass( 'ep-bg-light' );
            $( '#ep_booking_step2' ).removeClass( 'ep-bg-light' );
            $( '#ep_booking_step2' ).addClass( 'ep-bg-warning' );
            $( '#ep_booking_step1' ).html( 'done' );

            $( '#ep_event_booking_checkout_btn' ).data('active_step', 2);
            if( $( '#ep_event_booking_checkout_btn' ).hasClass( 'step1' ) ) {
                $( '#ep_event_booking_checkout_btn' ).removeClass( 'step1' );
                $( '#ep_event_booking_checkout_btn' ).addClass( 'step2' );
            }
            
            return true;
        }
    });
});

var emColor = jQuery('.emagic').find('a').css('color');
jQuery(".emagic .ep-view-woocommerce-product svg").css('fill', emColor);

function ep_woo_set_dominent_color(){ 
    $ = jQuery;
    $("#primary.content-area .entry-content").prepend("<a>");
    var epiconColor = $('.emagic, #primary.content-area .entry-content').find('a').css('color');
    $(".emagic .ep-view-woocommerce-product svg").css('fill', epiconColor); 
}

jQuery(document).ready(function () {
    ep_woo_set_dominent_color();
});

jQuery(document).ready(function (){
   var wc_total = jQuery('#ep_wc_product_total').val();
   sessionStorage.setItem('ep_booking_additional_price', wc_total);
});

function getWoocommerceCountryState( address, item, target ){
   if( item != '' && target != '' ){
    country_code = jQuery('#'+item).val();
    var data = {
            action: 'ep_get_woocommerce_state_by_country_code',
            country_code: country_code,
        };

        $.post(epwi_ajax_object.ajax_url, data, function(response) {
            var statelist = response;
            jQuery('#'+target).empty();
            jQuery('#'+target).append( new Option("Select an Option..", ""));
            const parsedStatelist = JSON.parse(statelist);
            jQuery.each( parsedStatelist, function( key, value ) {
                jQuery('#'+target).append(new Option( value, key ) );
            });
        });   
    } 
}

jQuery( document ).on( 'click', '.ep_show_woocommerce_products_popup', function() {
    let event_id = $(this).attr('data-event-id');
    $( '[ep-modal="ep_show_woocommerce_products_popup_'+event_id+'"]' ).fadeIn(100);
    jQuery( 'body' ).addClass( 'ep-modal-open-body' );
});

// hide woocommerce products popup
jQuery( document ).on( 'click', '.ep_close_woocommerce_products_popup', function() {
    let event_id = $(this).attr('data-event-id');
    jQuery( '[ep-modal="ep_show_woocommerce_products_popup_'+event_id+'"]' ).fadeOut(100);
    jQuery( 'body' ).removeClass( 'ep-modal-open-body' );
});

// hide woocommerce products popup
jQuery( document ).on( 'click', '.ep_hide_woocommerce_products_popup', function() {
    let event_id = $(this).attr('data-event-id');
    jQuery( '[ep-modal="ep_show_woocommerce_products_popup_'+event_id+'"]' ).fadeOut(100);
    jQuery( 'body' ).removeClass( 'ep-modal-open-body' );
});