/**
 * Start section sticky widget script
 */

(function ($, elementor) {

    'use strict';

    // Debounce function to prevent excessive function calls
    function debounce(func, wait) {
        var timeout;
        return function executedFunction() {
            var later = function() {
                clearTimeout(timeout);
                func();
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Function to handle dynamic positioning for sticky elements
    function handleDynamicPositioning($stickyElement) {
        var stickyData = $stickyElement.attr('data-bdt-sticky');

        // Check if dynamic positioning is enabled
        if (!stickyData) {
            return;
        }

        // Store original position for reference
        var originalOffset = $stickyElement.offset();
        var originalLeft = originalOffset ? originalOffset.left : 0;
        var isPositioned = false;

        // Function to calculate inset value
        function calculateInsetValue() {
            var elementWidth = $stickyElement.outerWidth();
            var documentWidth = $(document).width();
            var isRTL = $('html').attr('dir') === 'rtl' || $('body').hasClass('rtl');

            return isRTL ?
                Math.max(documentWidth - elementWidth - originalLeft, 0) :
                originalLeft;
        }

        // Function to apply or remove positioning
        function updatePositioning(immediate) {
            if ($stickyElement.hasClass('bdt-active')) {
                if (!isPositioned) {
                    var insetValue = calculateInsetValue();
                    $stickyElement.css({
                        'inset-inline-start': insetValue + 'px',
                        'transition': immediate ? 'none' : 'inset-inline-start 0.15s ease-out'
                    });
                    isPositioned = true;
                }
            } else {
                if (isPositioned) {
                    $stickyElement.css({
                        'inset-inline-start': '',
                        'transition': 'inset-inline-start 0.15s ease-out'
                    });
                    isPositioned = false;
                }
            }
        }

        // Debounced resize handler
        var debouncedResize = debounce(function() {
            if (isPositioned) {
                updatePositioning(true);
            }
        }, 100);

        // Update on window resize
        $(window).on('resize', debouncedResize);

        // Update when sticky becomes active (using MutationObserver)
        var observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    // Use requestAnimationFrame for smooth transitions
                    requestAnimationFrame(function() {
                        updatePositioning(false);
                    });
                }
            });
        });

        observer.observe($stickyElement[0], {
            attributes: true,
            attributeFilter: ['class']
        });

        // Listen for UIkit sticky events with proper timing
        $stickyElement.on('active', function () {
            requestAnimationFrame(function() {
                updatePositioning(true);
            });
        });

        $stickyElement.on('inactive', function () {
            requestAnimationFrame(function() {
                updatePositioning(false);
            });
        });

        // Initial check with delay to ensure proper initialization
        setTimeout(function() {
            updatePositioning(true);
        }, 150);
    }

    var widgetSectionSticky = function ($scope, $) {
        var $section = $scope;

        // Sticky fixes for inner section
        $section.each(function () {
            var $stickyFound = $(this).find('.elementor-inner-section.bdt-sticky');
            if ($stickyFound.length) {
                $stickyFound.wrap('<div class="bdt-sticky-wrapper"></div>');
            }
        });

        // Handle dynamic positioning for sticky elements
        var $stickyElements = $section.find('[data-bdt-sticky]');
        if ($stickyElements.length === 0) {
            $stickyElements = $('[data-bdt-sticky]');
        }

        $stickyElements.each(function () {
            handleDynamicPositioning($(this));
        });
    };

    // Handle elements that might already be present
    $(document).ready(function () {
        $('[data-bdt-sticky]').each(function () {
            handleDynamicPositioning($(this));
        });
    });

    // Initialize on Elementor frontend
    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/section', widgetSectionSticky);
    });

}(jQuery, window.elementorFrontend));

/**
 * End section sticky widget script
 */
