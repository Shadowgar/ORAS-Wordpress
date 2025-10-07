/**
 * Start tabs widget script
 */

(function ($, elementor) {
    'use strict';
    var widgetTabs = function ($scope, $) {
        const $tabsArea = $scope.find('.bdt-tabs-area'),
            $tabs = $tabsArea.find('.bdt-tabs'),
            $tab = $tabs.find('.bdt-tab'),
            editMode = Boolean(elementorFrontend.isEditMode());

        if (!$tabsArea.length) {
            return;
        }
        const $settings = $tabs.data('settings'),
            animTime = $settings.hashScrollspyTime,
            customOffset = $settings.hashTopOffset,
            navStickyOffset = $settings.navStickyOffset;

        if (navStickyOffset == 'undefined') {
            navStickyOffset = 10;
        }

        $scope.find('.bdt-template-modal-iframe-edit-link').each(function () {
            var modal = $($(this).data('modal-element'));
            $(this).on('click', function (event) {
                bdtUIkit.modal(modal).show();
            });
            modal.on('beforehide', function () {
                window.parent.location.reload();
            });
        });


        function hashHandler($tabs, $tab, animTime, customOffset) {
            // debugger;
            if (window.location.hash) {
                var currentHash = window.location.hash.substring(1);
               
                var currentHash = decodeURIComponent(window.location.hash.substring(1));

                var $targetTab = $($tabs).find('[data-title]').filter(function() {
                    return $(this).attr('data-title').toLowerCase() === currentHash.toLowerCase();
                });
           
                // Case-insensitive matching
                var $targetTab = $($tabs).find('[data-title]').filter(function() {
                    var dataTitle = $.trim($(this).attr('data-title')).toLowerCase();
                    var hashValue = $.trim(currentHash).toLowerCase();
                    var matches = dataTitle === hashValue;
                    return matches;
                });
                
                
                if ($targetTab.length) {
                    var hashTarget = $targetTab.closest($tabs).attr('id');
                    $('html, body').animate({
                        easing: 'slow',
                        scrollTop: $('#' + hashTarget).offset().top - customOffset
                    }, animTime, function () {
                        //#code
                    }).promise().then(function () {
                        
                        try {
                            var tabInstance = bdtUIkit.tab($tab);
                            tabInstance.show($targetTab.data('tab-index'));
                        } catch{}
                    });
                }
            }
        }
        if ($settings.activeHash == 'yes' && $settings.status != 'bdt-sticky-custom') {
            $(document).ready(function () {
                setTimeout(function() {
                    hashHandler($tabs, $tab, animTime, customOffset);
                }, 100);
            });
            $(window).on('load', function () {
                setTimeout(function() {
                    hashHandler($tabs, $tab, animTime, customOffset);
                }, 100);
            });
            $($tabs).find('.bdt-tabs-item-title').off('click').on('click', function (event) {
                event.preventDefault();
                // Encode and Decoded
                const title = $.trim($(this).attr('data-title'));
                // Set encoded hash for logic
                const encoded = encodeURIComponent(title);
                // Update location.hash (encoded, works with browser reloads)
                window.location.hash = encoded;
                // Replace the ugly encoded with friendly version
                history.replaceState(null, null, '#' + title);
                // window.location.hash = ($.trim($(this).attr('data-title')));
            });
            $(window).on('hashchange', function (e) {
                hashHandler($tabs, $tab, animTime, customOffset);
            });
        }
        //# code for sticky and also for sticky with hash
        function stickyHachChange($tabs, $tab, navStickyOffset) {
            var currentHash = window.location.hash.substring(1);
            
            //Decode for both decode and encoding
            var decodedHash = decodeURIComponent(currentHash);

            var $targetTab = $($tabs).find('[data-title]').filter(function() {
                return $(this).attr('data-title').toLowerCase() === decodedHash.toLowerCase();
            });
            
            if ($targetTab.length) {
                var hashTarget = $targetTab.closest($tabs).attr('id');
                $('html, body').animate({
                    easing: 'slow',
                    scrollTop: $('#' + hashTarget).offset().top - navStickyOffset
                }, 1000, function () {
                    //#code
                }).promise().then(function () {
                    bdtUIkit.tab($tab).show($targetTab.data('tab-index'));
                });
            }
        }
        if ($settings.status == 'bdt-sticky-custom') {
            $($tabs).find('.bdt-tabs-item-title').bind().click('click', function (event) {
                if ($settings.activeHash == 'yes') {
                    // Encode and Decoded
                    const title = $.trim($(this).attr('data-title'));
                     // Set encoded hash for logic
                    const encoded = encodeURIComponent(title);
                    // Update location.hash (encoded, works with browser reloads)
                    window.location.hash = encoded;
                    // Replace the ugly encoded with friendly version
                    history.replaceState(null, null, '#' + title);
                } else {
                    $('html, body').animate({
                        easing: 'slow',
                        scrollTop: $($tabs).offset().top - navStickyOffset
                    }, 500, function () {
                        //#code
                    });
                }
            });
            // # actived Hash#
            if ($settings.activeHash == 'yes' && $settings.status == 'bdt-sticky-custom') {
                $(document).ready(function () {
                    if (window.location.hash) {
                        setTimeout(function() {
                            stickyHachChange($tabs, $tab, navStickyOffset);
                        }, 100);
                    }
                });
                $(window).on('load', function () {
                    if (window.location.hash) {
                        setTimeout(function() {
                            stickyHachChange($tabs, $tab, navStickyOffset);
                        }, 100);
                    }
                });
                $(window).on('hashchange', function (e) {
                    stickyHachChange($tabs, $tab, navStickyOffset);
                });
            }
        }

        // start linkWidget


        var $linkWidget = $settings['linkWidgetSettings'],
            $activeItem = ($settings['activeItem']) - 1;
        if ($linkWidget !== undefined && editMode === false) {

            $linkWidget.forEach(function (entry, index) {

                if (index == 0) {
                    $('#bdt-tab-content-' + $settings['linkWidgetId']).parent().remove();
                    $(entry).parent().wrapInner('<div class="bdt-switcher-wrapper" />');
                    $(entry).parent().wrapInner('<div id="bdt-tab-content-' + $settings['linkWidgetId'] + '" class="bdt-switcher bdt-switcher-item-content" />');

                    if ($settings['activeItem'] == undefined) {
                        $(entry).addClass('bdt-active');
                    }
                }

                if ($settings['activeItem'] !== undefined && index == $activeItem) {
                    $(entry).addClass('bdt-active');
                }

                $(entry).attr('data-content-id', "tab-" + (index + 1));

            });

            /**
             * Sometimes not works UIKIT connect that's why below code
             */
            $tab.find('a').on('click', function () {
                let index = $(this).data('tab-index');
                $('#bdt-tab-content-' + $settings['linkWidgetId'] + '>').removeClass('bdt-active');
                $('#bdt-tab-content-' + $settings['linkWidgetId'] + '>').eq(index).addClass('bdt-active');
            });

        }
        // end linkWidget

        if (typeof $settings.sectionBg != "undefined") {
            if (typeof $settings.sectionBgSelector == "undefined") {
                return;
            }
            var $id = (($settings.sectionBgSelector) + '-ep-dynamic').substring(1);

            if ($(`#${$id}-wrapper`).length) {
                $(`#${$id}-wrapper`).remove();
            }

            var dynamicBG = `<div id="${$id}-wrapper"  style = "position: absolute; z-index: 0; top: 0; right: 0; bottom: 0; left: 0;" >`;

            $($settings.sectionBg).each(function (e) {

                let newLine = '<div class="bdt-hidden ' + $id + ' bdt-animation-' + $settings.sectionBgAnim + '" style=" width: 100%; height: 100%; transition: all .5s;">';
                newLine += '<img src = "' + $settings.sectionBg[e] + '" style = " height: 100%; width: 100%; object-fit: cover;" >';
                newLine += '</div>';
                dynamicBG += newLine;
            });

            dynamicBG += `</div>`;

            $($settings.sectionBgSelector).prepend(dynamicBG);
            var activeIndex = $tab.find('>.bdt-tabs-item.bdt-active').index();
            $(`.${$id}:eq('${activeIndex}')`).removeClass('bdt-hidden');

            $tabsArea.find('.bdt-tabs-item-title').on('click', function () {
                let $tabImg = $(this).data('tab-index');
                $('.' + $id + ':eq(' + $tabImg + ')').siblings().addClass('bdt-hidden');
                $('.' + $id + ':eq(' + $tabImg + ')').removeClass('bdt-hidden');
            });

        }

        // start section link
        var $linkSection = $settings['linkSectionSettings'];
        if ($linkSection !== undefined && editMode === false) {
            $linkSection.forEach(function (entry, index) {
                let $tabContent = $('#bdt-tab-content-' + $settings.linkWidgetId),
                $section = $(entry);
                $tabContent.find('.bdt-tab-content-item' + ':eq(' + index + ')').html($section);
            });
        }

    };
    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/bdt-tabs.default', widgetTabs);
        elementorFrontend.hooks.addAction('frontend/element_ready/bdt-acf-tabs.default', widgetTabs);
    });
}(jQuery, window.elementorFrontend));

/**
 * End tabs widget script
 */