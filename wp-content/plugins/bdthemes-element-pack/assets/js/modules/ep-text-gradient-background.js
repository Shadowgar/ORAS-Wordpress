;
(function ($, elementor) {
    $(window).on('elementor/frontend/init', function () {
        var ModuleHandler = elementorModules.frontend.handlers.Base,
            TextGradientBackground;

        TextGradientBackground = ModuleHandler.extend({

            bindEvents: function () {
                this.run();
            },

            getDefaultSettings: function () {
                return {
                    allowHTML: true,
                };
            },

            onElementChange: debounce(function (prop) {
                if (prop.indexOf('element_pack_tgb_') !== -1) {
                    this.run();
                }
            }, 400),

            settings: function (key) {
                return this.getElementSettings('element_pack_tgb_' + key);
            },

            run: function () {
                var widgetID = this.$element.data('id');
                var widgetContainer = $('.elementor-element-' + widgetID);

                if ('yes' !== this.settings('enable')) {
                    return;
                }

                if (this.settings('selector')) {
                    widgetContainer = $('.elementor-element-' + widgetID).find(this.settings('selector'));

                    // Apply gradient background and text clipping styles to the selected elements
                    widgetContainer.each(function () {
                        var $this = $(this);
                        if (!$this.hasClass('element-pack-tgb-background')) {
                            $this.addClass('element-pack-tgb-background');
                        }
                        $this.css({
                            'background-clip': 'text',
                            '-webkit-background-clip': 'text',
                            'color': 'transparent',
                            '-webkit-text-fill-color': 'transparent',
                            'text-fill-color': 'transparent'
                        });
                    });
                }


            }
        });

        elementorFrontend.hooks.addAction('frontend/element_ready/widget', function ($scope) {
            elementorFrontend.elementsHandler.addHandler(TextGradientBackground, {
                $element: $scope
            });
        });
    });
})(jQuery, window.elementorFrontend);
