jQuery(function ($) {

    $( document ).on( 'click', '#enable_product', function() {
        if( $( this ).prop( 'checked' ) == true ) {
            $( "#ep_show_event_products" ).show();
        } else{
            $( "#ep_show_event_products" ).hide();
        }
    });

    if ( $('.ep-woo-product-block').length <= 1 ) {
        $('.ep-remove-product').hide();
    }

    $(document).on('click', '.add_product', function(){
        // Finding total number of elements added
        var total_element = $(".ep-woo-product-block").length;        
        // last <div> with element class id
        var lastid = $(".ep-woo-product-block:last").attr("id");
        var split_id = lastid.split("_");
        var nextindex = Number(split_id[1]) + 1;
        let categories_list = eventprime_wc_integration.categories_list;
        let products_list = eventprime_wc_integration.products_list;
        let select_product_text = eventprime_wc_integration.select_product_text;
        var html = '';
        html += '<div class="ep-box-col-12 ep-mt-3 ep-event-wc-category-select-wrap"><label class="ep-form-label ep-my-1">'+eventprime_wc_integration.select_category_text+'</label>';
        html += '<select multiple name="search_category_'+nextindex+'[]" id="search_category_'+nextindex+'" class="ep-form-control search_category" onchange="changeCategory('+nextindex+')">';
            categories_list.forEach( (item, index) => {
                html +='<option value="'+item.slug+'">'+item.name+'</option>';
            });
        html +='</select>';
        html +='</div>';
        html += '<div class="ep-box-col-12 ep-mt-3 ep-event-wc-product-select-wrap"><label class="ep-form-label ep-my-1 ep-d-flex ep-align-items-center ep-content-left">'+eventprime_wc_integration.select_product_text+'<span class="spinner"></span>'+'</label>';
        html += '<select name="woocommerce_product['+nextindex+']" class="ep-form-control">';
        html +='<option value="">'+select_product_text+'</option>';
            products_list.forEach( (item, index) => {
                html +='<option value="'+item.id+'">'+item.name+'</option>';
            });
        html += '</select>';
        html += '</div>';
        html += '<div class="ep-box-col-12 ep-mt-3 ep-d-flex ep-items-center" ><label class="form-check form-check-inline ep-mr-2">'+eventprime_wc_integration.is_purchase_mandatory_text+'</label><div class="ep-show-weekly-options"><label class="form-check form-check-inline ep-mr-2"><input type="checkbox" name="purchase_mendatory['+nextindex+']" value="1" ></label></div></div>';
        html += '<button class="ep-remove-product ep-mt-3 remove_product" id="rem_'+nextindex+'">'+eventprime_wc_integration.remove_text+'</button>';
        // Adding new div container after last occurance of element class
        
        $(".ep-woo-product-block:last").after("<div class='ep-box-col-12 ep-mt-3 ep-childfieldsrow ep-woo-product-block' id='tab_"+ nextindex +"'></div>");

        if ( $('.ep-woo-product-block').length > 1 ) {
            $('.ep-remove-product').show();
        }
       
        $('#tab_'+nextindex).append(html);
        
        jQuery(".search_category").select2({
            placeholder: "Select Category",
            tags: true,
            width: '80%'
        });  
        
    });

    $(document).on('click', '.remove_product', function(ev){
        // console.log( $('.ep-woo-product-block').length )
        if ( $('.ep-woo-product-block').length <= 1 ) {
            ev.preventDefault();
            return; 
        }
        $(this).closest('.ep-woo-product-block').remove();
        if ( $('.ep-woo-product-block').length <= 1 ) {
            $('.ep-remove-product').hide();
        }

    });

    setTimeout(() => {
        jQuery(".search_category").select2({
            placeholder: "Select Category",
            tags: true,
            width: '80%'
        });
    }, 1000 );

});

function changeCategory( index ){
    $ = jQuery;
    let catSelectorEl = $("#search_category_"+index);
    var cat_ids = catSelectorEl.val();
    var html = '';
    var formData = new FormData();
    formData.append('action', 'admin_woocommerce_product_categories');
    formData.append('selected_categories', cat_ids);

    catSelectorEl.parent().next('.ep-event-wc-product-select-wrap').find('select').attr('disabled', 'disabled');
    catSelectorEl.parent().next('.ep-event-wc-product-select-wrap').find('.spinner').addClass('is-active');

    $.ajax({
        type : "POST",
        url : eventprime_wc_integration.ajaxurl,
        data: formData,
        contentType: false,
        processData: false,       
        success: function(response) {
            catSelectorEl.parent().next('.ep-event-wc-product-select-wrap').find('.spinner').removeClass('is-active');
            catSelectorEl.parent().next('.ep-event-wc-product-select-wrap').find('select').removeAttr('disabled');
            products = response.data.products;
            html += '<option value=""> Select Product </option>';
            $.each(products, function (i, value) {
                html += '<option value=' + value.id + '>' + value.name + '</option>';
            });
            $("select[name='woocommerce_product["+index+"]']").html(html);       
        }
    }); 
}