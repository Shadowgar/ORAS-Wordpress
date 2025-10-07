jQuery(document).ready(function($){

    $(document).on( 'mec_skin_style_changed', function(e, skin, style, context){

        $wrap = $('#mec_' + skin + '_skin_options_container');
        $search_options_wrap = $('#mec_calendar_search_form');
        if (style.includes('liquid')) {
            jQuery('.mec-' + skin + '-liquid',$wrap).removeClass('hidden');
            if( jQuery('.mec-' + skin + '-liquid',$wrap).is('option') ) {

                jQuery('.mec-' + skin + '-liquid',$wrap).prop('disabled',false);
            }

            jQuery('.mec-not-' + skin + '-liquid',$wrap).addClass('hidden');
            if( jQuery('.mec-not-' + skin + '-liquid',$wrap).is('option') ) {

                jQuery('.mec-not-' + skin + '-liquid',$wrap).prop('disabled',true);
            }

            jQuery('.mec-' + skin + '-liquid',$search_options_wrap).removeClass('hidden');
            if( jQuery('.mec-' + skin + '-liquid',$search_options_wrap).is('option') ) {

                jQuery('.mec-' + skin + '-liquid',$search_options_wrap).prop('disabled',false);
            }

            jQuery('.mec-not-' + skin + '-liquid',$search_options_wrap).addClass('hidden');
            if( jQuery('.mec-not-' + skin + '-liquid',$search_options_wrap).is('option') ) {

                jQuery('.mec-not-' + skin + '-liquid',$search_options_wrap).prop('disabled',true);
            }
        } else {
            jQuery('.mec-' + skin + '-liquid',$wrap).addClass('hidden');
            if( jQuery('.mec-' + skin + '-liquid',$wrap).is('option') ) {

                jQuery('.mec-' + skin + '-liquid',$wrap).prop('disabled',true);
            }

            jQuery('.mec-not-' + skin + '-liquid',$wrap).removeClass('hidden');
            if( jQuery('.mec-not-' + skin + '-liquid',$wrap).is('option') ) {

                jQuery('.mec-not-' + skin + '-liquid',$wrap).prop('disabled',false);
            }

            jQuery('.mec-' + skin + '-liquid',$search_options_wrap).addClass('hidden');
            if( jQuery('.mec-' + skin + '-liquid',$search_options_wrap).is('option') ) {

                jQuery('.mec-' + skin + '-liquid',$search_options_wrap).prop('disabled',true);
            }

            jQuery('.mec-not-' + skin + '-liquid',$search_options_wrap).removeClass('hidden');
            if( jQuery('.mec-not-' + skin + '-liquid',$search_options_wrap).is('option') ) {

                jQuery('.mec-not-' + skin + '-liquid',$search_options_wrap).prop('disabled',false);
            }
        }

        jQuery('.wn-mec-select:not(.mec-custom-nice-select)').niceSelect('update');
    });

    $('#mec_skin_cover_style').trigger('change');
    $('#mec_skin_slider_style').trigger('change');
    $('#mec_skin_carousel_style').trigger('change');
    $('#mec_skin_available_spot_style').trigger('change');
    $('#mec_skin_list_style').trigger('change');
    $('#mec_skin_grid_style').trigger('change');
    $('#mec_skin_daily_view_style').trigger('change');
    $('#mec_skin_full_calendar_style').trigger('change');
    $('#mec_skin_general_calendar_style').trigger('change');
    $('#mec_skin_map_style').trigger('change');
    $('#mec_skin_weekly_view_style').trigger('change');

    $('select[name$="[category][type]"] option[value=checkboxes]').hide()
});