function mecLiquidOutsideEvent(e) {
    if (!jQuery(e.target).is('.mec-more-events-icon') && !jQuery(e.target).closest('.mec-more-events-wrap').length) {
        jQuery('.mec-more-events-wrap').hide();
    }
    if (!jQuery(e.target).is('.mec-filter-icon') && !jQuery(e.target).closest('.mec-filter-content').length) {
        jQuery('.mec-filter-content').hide();
    }
    if (!jQuery(e.target).is('.mec-liquid-more-views-icon') && !jQuery(e.target).closest('.mec-liquid-more-views-content').length) {
        jQuery('.mec-liquid-more-views-content').removeClass('active');
    }
}

function mecLiquidToggleFilterContent(e) {
    e.preventDefault();
    if (jQuery('.mec-filter-content').is(':visible')) {
        jQuery('.mec-filter-content').css({
            display: 'none',
        });
    } else {
        const displayValue = jQuery(window).width() <= 790 ? 'block' : 'flex';
        jQuery('.mec-filter-content').css({
            display: displayValue,
        });
    }
}

function mecLiquidToggleDisplayValueFilterContent() {
    const displayValue = jQuery(window).width() <= 767 ? 'block' : 'flex';
    if (jQuery('.mec-filter-content').is(':visible')) {
        jQuery('.mec-filter-content').css({
            display: displayValue,
        });
    }
}

function mecLiquidToggleMoreEvents(e) {
    e.preventDefault();
    const moreEventsWrap = jQuery(this).siblings('.mec-more-events-wrap');
    const moreEvents = moreEventsWrap.children('.mec-more-events');
    jQuery('.mec-more-events-wrap').removeClass('active');
    moreEventsWrap.addClass('active');
    jQuery('.mec-more-events-wrap:not(.active)').hide();
    if (moreEventsWrap.is(':visible')) {
        moreEventsWrap.hide();
    } else {
        topElement = moreEventsWrap.closest('.mec-more-events-inner-controller').length > 0 ? moreEventsWrap.closest('.mec-more-events-inner-controller') : moreEventsWrap.closest('.mec-more-events-controller');
        moreEventsWrap.show().css({
            top: topElement.offset().top - window.scrollY,
            left: moreEventsWrap.closest('.mec-more-events-controller').offset().left,
            width: moreEventsWrap.closest('.mec-more-events-controller').width(),
        });
        if (moreEventsWrap.width() > 400) {
            moreEvents.css({
                left: (moreEventsWrap.width() / 2) - (moreEvents.width() / 2),
                width: 400,
            });
        } else {
            moreEvents.css({
                width: moreEventsWrap.width(),
                left: 0,
            });
        }
    }
}

function mecLiquidNiceSelect() {
    if (jQuery('.mec-liquid-wrap').length < 0) {
        return;
    }

    if (jQuery().niceSelect) {
        jQuery('.mec-liquid-wrap').find('.box-filter').find('select:not([multiple])').niceSelect();
    }
}

function mecLiquidCustomScrollbar() {
    if (jQuery(".mec-liquid-wrap").length < 0) {
        return;
    }

    if (jQuery().niceScroll) {
        var moreIcon = jQuery(this);
        if (
            !moreIcon
                .siblings(".mec-more-events-wrap")
                .find(".mec-more-events-body")
                .hasClass("mec-liquid-custom-scrollbar")
        ) {
            moreIcon
                .siblings(".mec-more-events-wrap")
                .find(".mec-more-events-body")
                .addClass("mec-liquid-custom-scrollbar")
                .niceScroll({
                    cursorcolor: "#C7EBFB",
                    cursorwidth: "4px",
                    cursorborderradius: "4px",
                    cursorborder: "none",
                    railoffset: {
                        left: -2,
                    },
                });
        }
        moreIcon
            .siblings(".mec-more-events-wrap")
            .find(".mec-more-events-body")
            .getNiceScroll()
            .onResize();
    }
}

function mecLiquidCustomScrollbarInit(y) {
    if (jQuery('.mec-liquid-wrap').length < 0) {
        return;
    }

    if (jQuery().niceScroll) {
        jQuery('.mec-liquid-custom-scrollbar').niceScroll({
            cursorcolor: '#C7EBFB',
            cursorwidth: '4px',
            cursorborderradius: '4px',
            cursorborder: 'none',
            railoffset: {
                left: -2,
            }
        });
        jQuery('.mec-liquid-custom-scrollbar').getNiceScroll().resize();
        jQuery('.mec-liquid-custom-scrollbar').each(function () {
            if (jQuery(this).find('.mec-liquid-current-time-cell').length > 0) {
                var parentTopOffset = jQuery(this).offset().top;
                var currentTimeCellOffset = jQuery(this).find('.mec-liquid-current-time-cell').offset().top;
                jQuery(this).getNiceScroll(0).doScrollTop(currentTimeCellOffset - parentTopOffset - 16, 120);
                jQuery(this).on('scroll', function () {
                    if (jQuery(this).getNiceScroll(0).scroll.y != 0) {
                        jQuery(this).addClass('mec-scrolling');
                    } else {
                        jQuery(this).removeClass('mec-scrolling');
                    }
                });
            }
            if (typeof y != 'undefined') {
                if (jQuery(this).closest('.mec-skin-list-wrap').length > 0 || jQuery(this).closest('.mec-skin-grid-wrap').length > 0) {
                    jQuery(this).getNiceScroll(0).doScrollTop(0, 120);
                }
            }
        });
        var width = (window.innerWidth > 0) ? window.innerWidth : screen.width;
        if ( width < 768 ) {
            jQuery('.mec-liquid-custom-scrollbar').css('touch-action','unset');
        }
    }
}

function mecLiquidCustomScrollbarInitByEvent( e, y ){

    mecLiquidCustomScrollbarInit(y);
}

function mecLiquidMoreViewsContent() {
    jQuery(this).find('.mec-liquid-more-views-content').toggleClass('active');
}

function mecLiquidMonthlyCalendarUI() {
    if (jQuery(".mec-liquid-monthly-wrap").length < 1) {
        return;
    }

    var widowWidth = jQuery(window).innerWidth();

    if (widowWidth <= 767) {
        var dts = jQuery("dt.mec-calendar-day");
        dts.each(function (index, dtElem) {
            var dt = jQuery(dtElem);

            if (dt.find(".mec-more-events-mobile").length > 0) {
                return;
            }

            var events = dt.children(".simple-skin-ended");

            if (events.length < 1) {
                return;
            }

            var eventsHTML = [];
            events.each(function (index, eventElem) {
                var event = jQuery(eventElem);
                var eventWrapper = event
                    .clone()
                    .empty()
                    .addClass("mec-more-event-copy");
                var eventTitleHTML = event.find(".mec-event-title")[0]
                    .outerHTML;
                var startTimeHTML =
                    '<span class="mec-event-start-time">' +
                    event.data("start-time") +
                    "</span>";
                var endTimeHTML =
                    '<span class="mec-event-end-time">' +
                    event.data("end-time") +
                    "</span>";
                eventWrapper.append(
                    '\n<div class="mec-more-events-content">\n'
                        .concat(
                            eventTitleHTML,
                            '\n<i class="mec-sl-clock"></i>\n'
                        )
                        .concat(startTimeHTML, "\n")
                        .concat(endTimeHTML, "\n</div>")
                );
                eventsHTML[index] = eventWrapper[0].outerHTML;
            });
            var moreEvents = dt.find(".mec-more-events-wrap");

            if (moreEvents.length < 1) {
                var day = dt.data("day");
                var month = dt.data("month");
                var year = dt.data("year");
                var date = new Date(year, month-1, day, 0, 0, 0, 0);
                var HeaderText = dateFormat(date, "fullDate");
                var moreEventsHTML = '\n<span class="mec-more-events-icon">...</span>\n<div class="mec-more-events-wrap mec-more-events-generated" style="display: none;">\n<div class="mec-more-events">\n<h5 class="mec-more-events-header">'.concat(
                    HeaderText,
                    '</h5>\n<div class="mec-more-events-body"></div>\n</div>\n</div>'
                );
                dt.append(moreEventsHTML);
            }

            eventsHTML.forEach(function (eventHTML) {
                dt.find(".mec-more-events-wrap")
                    .addClass("mec-more-events-mobile")
                    .end()
                    .find(".mec-more-events-body")
                    .prepend(eventHTML);
            });
        });
    } else {
        jQuery(".mec-more-events-generated")
            .siblings(".mec-more-events-icon")
            .remove()
            .end()
            .remove();
        jQuery(".mec-more-events-wrap.mec-more-events-mobile")
            .removeClass("mec-more-events-mobile")
            .find(".mec-more-event-copy")
            .remove();
    }
}

function mecLiquidWrapperFullScreenWidth() {
    if (jQuery('.mec-liquid-bg-wrap').length > 0) {
        jQuery('.mec-liquid-bg-wrap').css({
            maxWidth: jQuery('body').width() + 8,
        });
    }
}

function mecLiquidUI() {
    if (typeof mecdata != 'undefined' && typeof mecdata.enableSingleLiquid != 'undefined' && mecdata.enableSingleLiquid) {
        jQuery('body').addClass('mec-single-liquid-body');
    }
    // Set filter content position
    jQuery(window).on('load resize', function () {
        if (jQuery('.mec-filter-content').length > 0) {
            jQuery('.mec-filter-content').css({
                right: -(jQuery('.mec-calendar').width() - jQuery('.mec-search-form.mec-totalcal-box').position().left - jQuery('.mec-search-form.mec-totalcal-box').width() + 40),
                left: -jQuery('.mec-search-form.mec-totalcal-box').position().left + 40,
            });
        }
        if (jQuery('.mec-filter-icon').is(':visible')) {
            var filterIconLeftPosition = parseInt(jQuery('.mec-search-form.mec-totalcal-box').position().left) + parseInt(jQuery('.mec-filter-icon').position().left) - 25;
            jQuery('head').find('style[title="mecLiquidFilterContentStyle"]').remove().end().append('<style title="mecLiquidFilterContentStyle">.mec-liquid-wrap .mec-filter-content:before{left: ' + filterIconLeftPosition + 'px;}.mec-liquid-wrap .mec-filter-content:after{left: ' + (filterIconLeftPosition + 1) + 'px;}</style>');
        }
    });
    // Hide empty filter content
    if (jQuery('.mec-filter-content').is(':empty')) {
        jQuery('.mec-filter-icon').hide();
    }
    // Prevend Default For Event Share Icon
    jQuery(document).on('click', '.mec-event-share-icon', function (e) {
        e.preventDefault();
    });
}

function mecLiquidSliderUI() {
    jQuery(window).on('load', function () {
        if(typeof mecdata === 'undefined') return;

        jQuery('.mec-liquid-wrap.mec-skin-slider-container .owl-next').prepend('<span>'+ mecdata.next +'</span>');
        jQuery('.mec-liquid-wrap.mec-skin-slider-container .owl-prev').append('<span>'+  mecdata.prev +'</span>');
    });
}

function mec_liquid_wrap_init(){

    if (jQuery('.mec-liquid-wrap').length == 0) {
        return;
    }

    jQuery(window).on("resize", mecLiquidMonthlyCalendarUI);
    jQuery(document).on('load_calendar_data',function(){
        mecLiquidMonthlyCalendarUI();
    });
    mecLiquidMonthlyCalendarUI();
    jQuery(document).on(
        "click",
        ".mec-liquid-wrap .mec-more-events-icon",
        mecLiquidCustomScrollbar
    );
}

jQuery(document).ready(function ($) {

    //For FullCalendar
    $.fn.mecFullCalendarLiquid = function (options) {
        // Default Options
        var settings = $.extend({
            // These are the defaults.
            id: 0,
            atts: '',
            ajax_url: '',
            sf: {},
            skin: '',
        }, options);

        // Set onclick Listeners
        setListeners();

        $(document).trigger('mec_full_calendar_before_init', $("#mec_full_calendar_container_" + settings.id));

        function setListeners() {
            //Button More Type Date Picker
            $('.type-date .btn-type-more').on("click", function () {

                if (jQuery('.type-date .btn-type-more').hasClass("open")) {
                    jQuery('.type-date .btn-type-more').removeClass("open");
                    jQuery('.box-more').css('display', 'none');
                } else {
                    jQuery('.type-date .btn-type-more').addClass("open");
                    jQuery('.box-more').css('display', 'flex');
                }
            });
            // Add the onclick event
            $(".mec-totalcal-box .mec-totalcal-view span").on('click', function (e) {
                e.preventDefault();
                $(".mec-totalcal-box .mec-totalcal-view span").removeClass('mec-totalcalview-selected');
                $(e).addClass('mec-totalcalview-selected');
                var skin = $(this).data('skin');
                var mec_month_select = $('#mec_sf_month_' + settings.id);
                var mec_year_select = $('#mec_sf_year_' + settings.id);

                if (mec_year_select.val() == 'none') {
                    mec_year_select.find('option').each(function () {
                        var option_val = $(this).val();
                        if (option_val == mecdata.current_year) mec_year_select.val(option_val);
                    });
                }

                if (mec_month_select.val() == 'none') {
                    mec_month_select.find('option').each(function () {
                        var option_val = $(this).val();
                        if (option_val == mecdata.current_month) mec_month_select.val(option_val);
                    });
                }

                if (skin == 'list' || skin == 'grid') {
                    var mec_filter_none = '<option class="mec-none-item" value="none">' + $('#mec-filter-none').val() + '</option>';
                    if (mec_month_select.find('.mec-none-item').length == 0) mec_month_select.prepend(mec_filter_none);
                    if (mec_year_select.find('.mec-none-item').length == 0) mec_year_select.prepend(mec_filter_none);
                }
                else {
                    if (mec_month_select.find('.mec-none-item').length != 0) mec_month_select.find('.mec-none-item').remove();
                    if (mec_year_select.find('.mec-none-item').length != 0) mec_year_select.find('.mec-none-item').remove();
                }


                loadSkin(skin);
            });
        }

        function loadSkin(skin) {
            // Set new Skin
            settings.skin = skin;

            // Add Loading Class
            if(jQuery('.mec-modal-result').length === 0) jQuery('.mec-wrap').append('<div class="mec-modal-result"></div>');
            jQuery('.mec-modal-result').addClass('mec-month-navigator-loading');

            var $month_picker = $("#mec_sf_month_" + settings.id);
            var $year_picker = $("#mec_sf_year_" + settings.id);

            // Add Month & Year
            if(settings.atts.indexOf('sf[month]') <= -1 && $month_picker.length && $year_picker.length)
            {
                settings.atts += '&sf[month]='+$month_picker.val()+'&sf[year]='+$year_picker.val();
            }

            $.ajax({
                url: settings.ajax_url,
                data: "action=mec_full_calendar_switch_skin&skin=" + skin + "&" + settings.atts + "&apply_sf_date=1&sed=" + settings.sed_method,
                type: "post",
                success: function (response) {

                    jQuery(document).trigger( 'mec_before_load_skin_success', [$("#mec_full_calendar_container_" + settings.id), settings.id] );

                    $("#mec_full_calendar_container_" + settings.id).html(response);

                    // Remove loader
                    $('.mec-modal-result').removeClass("mec-month-navigator-loading");

                    // Focus First Active Day
                    mecFocusDay(settings);

                    jQuery(document).trigger( 'mec_load_skin_success', $("#mec_full_calendar_container_" + settings.id) );

                    // Set onclick Listeners
                    setListeners();
                },
                error: function () { }
            });
        }

    };

    if (jQuery('.mec-liquid-wrap').length == 0) {
        return;
    }

    mecLiquidUI();
    mecLiquidNiceSelect();

    jQuery(window).on('resize', mecLiquidToggleDisplayValueFilterContent);
    jQuery(document).on('click', '.mec-liquid-wrap .mec-filter-icon', mecLiquidToggleFilterContent);
    jQuery(document).on('click', '.mec-liquid-wrap .mec-more-events-icon', mecLiquidToggleMoreEvents);
    jQuery(document).on('click', mecLiquidOutsideEvent);
    jQuery(document).on('click', '.mec-liquid-more-views-icon', mecLiquidMoreViewsContent);

    mec_liquid_wrap_init();

    // Map
    jQuery(document).on('mec_map_load_markers', function(e, markers, settings){

        var f = 0;
        var sideHtml = '';

        for (var i in markers) {
            f++;
            var dataMarker = markers[i];

            var gmap_url = 'https://www.google.com/maps/search/?api=1&query=' + dataMarker.latitude + ',' + dataMarker.longitude;

            var marker = {
                lightbox: dataMarker.lightbox + '<div class="mec-map-lightbox-link" style="background: #fff;padding: 4px;font-size: 15px;width:auto;"><a class="mec-go-to-map" target="_blank" href="' + gmap_url + '">'+ settings.show_on_map_text +'</a></div>',
                event_ids: dataMarker.event_ids,

            };

            marker['ukey'] = dataMarker.latitude + ',' + dataMarker.longitude;

            sideHtml += '<div class="mec-map-boxshow" data-id="' + marker['ukey'] + '">' + dataMarker.lightbox + '</div>';
        }

        var sideElement = jQuery('#mec-map-skin-side-' + settings.id);
        if (typeof sideElement != 'undefined' && sideElement != null) {
            sideElement.html(sideHtml);
            // reinitSearch();
        }

        if (f == 0) {
            sideElement.html('<h4>No Event Found</h4>');
        }
    });

    /** Calendar skin init begin */
    function mecLiquidCurrentTimePosition( $wrap = null ){

        if (jQuery('.mec-liquid-wrap').length > 0) {
            jQuery('.mec-liquid-current-time').each(function () {
                var currentTimeMinutes = jQuery(this).data('time');
                var height = jQuery(this).closest('.mec-liquid-current-time-cell').height();
                jQuery(this).css({
                    top: (currentTimeMinutes / 60) * height,
                });
            });
        }
    }

    jQuery(window).on('load', mecLiquidCurrentTimePosition);
    jQuery(document).on('mec_full_calendar_before_init', mecLiquidCurrentTimePosition);
    jQuery(document).on('mec_load_skin_success', mecLiquidCurrentTimePosition);
    jQuery(document).on('mec_search_success', mecLiquidCurrentTimePosition);

    jQuery(window).on('load', mecLiquidCustomScrollbarInit);
    jQuery(document).on('mec_full_calendar_before_init', mecLiquidCustomScrollbarInit);
    jQuery(document).on('mec_load_skin_success', mecLiquidCustomScrollbarInit);
    jQuery(document).on('mec_search_success', mecLiquidCustomScrollbarInit);
    jQuery(document).on('mec_custom_scrollbar_init', mecLiquidCustomScrollbarInitByEvent);

    mecLiquidUI();
    mecLiquidNiceSelect();
    mecLiquidWrapperFullScreenWidth();
    jQuery(window).on('load', mecLiquidWrapperFullScreenWidth);
    jQuery(window).on('load', mecLiquidCurrentTimePosition);
    jQuery(window).on('resize', mecLiquidWrapperFullScreenWidth);
    mecLiquidSliderUI();
    mecLiquidCustomScrollbarInit();

    /** Calendar skin init begin */

    /** Search init begin */
    jQuery(document).on("click", '.btn-filter', function (e) {
        if (jQuery(this).hasClass("active")) {
            jQuery(this).removeClass("active");
            jQuery(this).closest('.box-search').find('.box-filter').css('display', 'none');
        } else {
            jQuery(this).addClass("active");
            jQuery(this).closest('.box-search').find('.box-filter').css('display', 'flex');
        }
    });

    jQuery(document).on("click", '.type-event li', function () {
        jQuery(this).closest('.mec-event-statuses').find('li').removeClass("active");
        jQuery(this).addClass("active");
    });

    $(document).on("click", '.btn-reset', function () {
        $(this).closest('.mec-liquid-wrap').find('.mec-event-statuses ul li input[value="all"]').closest('li').trigger('click');
    });

    function mec_liquid_render_month_picker(){

        if($.fn.monthPicker){

            if ($('.mec-liquid-month-picker').length > 0) {
                $('.mec-liquid-month-picker').monthPicker({
                    format: "yyyy-mm",
                    viewMode: "months",
                    minViewMode: "months",
                    classes: 'mec-liquid-month-picker-calendar',
                });

                $(".mec-liquid-month-picker").monthPicker('hide')
            }
        }
    }

    mec_liquid_render_month_picker();

    $(document).on('mec_month_picker_date_set', function(e, monthPicker){

        if( !monthPicker.element.hasClass('mec-liquid-month-picker') ){

            return;
        }

        var y = monthPicker.date.getFullYear();
        var m = monthPicker.date.getMonth() + 1;

        var $c = monthPicker.element.closest('.mec-month-navigator').find('.mec-current-date');
        if( y != $c.attr('data-mec-year') || m != $c.attr('data-mec-month') ){

            $c.attr('data-mec-year', y)
                .attr('data-mec-month', m)
                .data('mec-year', y)
                .data('mec-month', m)
                .trigger('click');
        }

        monthPicker.hide();

    });

    $(document).on('mec_search_process_end', mec_liquid_render_month_picker);
    $(document).on('mec_set_month_process_end', mec_liquid_render_month_picker);
    $(document).on('mec_full_calendar_before_init', mec_liquid_render_month_picker);
    /** Search init end */

    $(document).on('mec_daily_slider_init', function(e, owl, owl_rtl){

        owl.owlCarousel('destroy');
        owl.owlCarousel({
            responsiveClass: true,
            responsive: {
                479: {
                    items: 7,
                },
                767: {
                    items: 7,
                },
                960: {
                    items: 7,
                },
                1000: {
                    items: 7,
                },
                1200: {
                    items: 7,
                }
            },
            dots: false,
            loop: false,
            rtl: owl_rtl,
        });
    });

});





