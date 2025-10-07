/**
 * Start vertical menu widget script
 */

(function ($, elementor) {
    'use strict';
    // Vertical Menu
    var widgetSlinkyVerticalMenu = function ($scope, $) {
        var $vrMenu = $scope.find('.bdt-slinky-vertical-menu');
        var $settings = $vrMenu.attr('id');
        if (!$vrMenu.length) {
            return;
        }

        const slinky = $('#'+$settings).slinky();

        // Override the _move method to handle RTL (moved from vendor)
        const isRTL = document.documentElement.dir === 'rtl';
        if (isRTL) {            
            slinky._move = function(depth) {                
                // get current position from the right
                const right = Math.round(parseInt($('#'+$settings).children().first().get(0).style.right)) || 0;
                
                // set the new position from the right
                $('#'+$settings).children().first().css("right", `${right - depth * 100}%`);
            };
        };
    };

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/bdt-slinky-vertical-menu.default', widgetSlinkyVerticalMenu);
    });

}(jQuery, window.elementorFrontend));

/**
 * End vertical menu widget script
 */

