<?php

namespace ElementPack\Modules\BackdropFilter;

use Elementor\Controls_Manager;
use ElementPack\Base\Element_Pack_Module_Base;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Module extends Element_Pack_Module_Base {

    public function __construct() {
        parent::__construct();
        $this->add_actions();
    }

    public function get_name() {
        return 'bdt-backdrop-filter';
    }

    public function register_controls($widget, $args) {

        $widget->add_control(
            'element_pack_backdrop_filter',
            [
                'label'         => BDTEP_CP . esc_html__('Backdrop Filter/Liquid Glass Effects', 'bdthemes-element-pack'),
                'type'          => Controls_Manager::POPOVER_TOGGLE,
                'return_value'  => 'yes',
                'separator'    => 'before',
                'prefix_class' => 'bdt-backdrop-filter-',
            ]
        );

        $widget->start_popover();

        $widget->add_control(
            'element_pack_backdrop_filter_type',
            [
                'label'     => esc_html__('Effect Type', 'bdthemes-element-pack'),
                'type'      => Controls_Manager::SELECT,
                'default'   => 'backdrop',
                'options'   => [
                    'backdrop' => esc_html__('Backdrop Filter', 'bdthemes-element-pack'),
                    'liquid_glass'    => esc_html__('Liquid Glass Effects', 'bdthemes-element-pack') . BDTEP_LOCK,
                ],
                'condition' => [
                    'element_pack_backdrop_filter' => 'yes'
                ],
                'prefix_class' => 'bdt-filter-',
                'classes' => BDTEP_LOCK_CLASS,
            ]
        );


        $widget->add_control(
            'element_pack_bf_blur',
            [
                'label' => esc_html__('Blur', 'bdthemes-element-pack'),
                'type'  => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 25,
                        'step' => 0.1,
                    ],
                ],
                'condition' => [
                    'element_pack_backdrop_filter' => 'yes',
                    'element_pack_backdrop_filter_type' => 'backdrop'
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--ep-backdrop-filter-blur: {{SIZE}}px;'
                ],
            ]
        );

        $widget->add_control(
            'element_pack_bf_brightness',
            [
                'label'       => esc_html__('Brightness', 'bdthemes-element-pack'),
                'type'        => Controls_Manager::SLIDER,
                'render_type' => 'ui',

                'range' => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 200,
                        'step' => 10,
                    ],
                ],
                'condition' => [
                    'element_pack_backdrop_filter' => 'yes',
                    'element_pack_backdrop_filter_type' => 'backdrop'
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--ep-backdrop-filter-brightness: {{SIZE}}%;'
                ],
            ]
        );

        $widget->add_control(
            'element_pack_bf_contrast',
            [
                'label' => esc_html__('Contrast', 'bdthemes-element-pack'),
                'type'  => Controls_Manager::SLIDER,

                'range' => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 2,
                        'step' => 0.1,
                    ],
                ],
                'condition' => [
                    'element_pack_backdrop_filter' => 'yes',
                    'element_pack_backdrop_filter_type' => 'backdrop'
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--ep-backdrop-filter-contrast: {{SIZE}};'
                ],
            ]
        );

        $widget->add_control(
            'element_pack_bf_grayscale',
            [
                'label' => esc_html__('Grayscale', 'bdthemes-element-pack'),
                'type'  => Controls_Manager::SLIDER,

                'range' => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 1,
                        'step' => 0.1,
                    ],
                ],
                'condition' => [
                    'element_pack_backdrop_filter' => 'yes',
                    'element_pack_backdrop_filter_type' => 'backdrop'
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--ep-backdrop-filter-grayscale: {{SIZE}};'
                ],
            ]
        );

        $widget->add_control(
            'element_pack_bf_invert',
            [
                'label' => esc_html__('Invert', 'bdthemes-element-pack'),
                'type'  => Controls_Manager::SLIDER,

                'range' => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 1,
                        'step' => 0.1,
                    ],
                ],
                'condition' => [
                    'element_pack_backdrop_filter' => 'yes',
                    'element_pack_backdrop_filter_type' => 'backdrop'
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--ep-backdrop-filter-invert: {{SIZE}};'
                ],
            ]
        );

        $widget->add_control(
            'element_pack_bf_opacity',
            [
                'label' => esc_html__('Opacity', 'bdthemes-element-pack'),
                'type'  => Controls_Manager::SLIDER,

                'range' => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 1,
                        'step' => 0.1,
                    ],
                ],
                'condition' => [
                    'element_pack_backdrop_filter' => 'yes',
                    'element_pack_backdrop_filter_type' => 'backdrop'
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--ep-backdrop-filter-opacity: {{SIZE}};'
                ],
            ]
        );

        $widget->add_control(
            'element_pack_bf_sepia',
            [
                'label' => esc_html__('Sepia', 'bdthemes-element-pack'),
                'type'  => Controls_Manager::SLIDER,

                'range' => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 1,
                        'step' => 0.1,
                    ],
                ],
                'condition' => [
                    'element_pack_backdrop_filter' => 'yes',
                    'element_pack_backdrop_filter_type' => 'backdrop'
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--ep-backdrop-filter-sepia: {{SIZE}};'
                ],
            ]
        );

        $widget->add_control(
            'element_pack_bf_saturate',
            [
                'label' => esc_html__('Saturate', 'bdthemes-element-pack'),
                'type'  => Controls_Manager::SLIDER,

                'range' => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 10,
                        'step' => 0.1,
                    ],
                ],
                'condition' => [
                    'element_pack_backdrop_filter' => 'yes',
                    'element_pack_backdrop_filter_type' => 'backdrop'
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--ep-backdrop-filter-saturate: {{SIZE}};'
                ],
            ]
        );

        $widget->add_control(
            'element_pack_bf_hue_rotate',
            [
                'label' => esc_html__('Hue Rotate', 'bdthemes-element-pack'),
                'type'  => Controls_Manager::SLIDER,

                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 200,
                    ],
                ],
                'condition' => [
                    'element_pack_backdrop_filter' => 'yes',
                    'element_pack_backdrop_filter_type' => 'backdrop'
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--ep-backdrop-filter-hue-rotate: {{SIZE}}deg;'
                ],
            ]
        );



        // Liquid Glass Effects Controls
        $widget->add_control(
            'element_pack_liquid_glass_effects_blur',
            [
                'label' => esc_html__('Blur', 'bdthemes-element-pack'),
                'type'  => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 0.5,
                ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 25,
                        'step' => 0.1,
                    ],
                ],
                'condition' => [
                    'element_pack_backdrop_filter' => 'yes',
                    'element_pack_backdrop_filter_type' => 'liquid_glass'
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--ep-liquid-glass-effects-blur: {{SIZE}}px;'
                ],
            ]
        );

        $widget->add_control(
            'ep_liquid_glass_effects_notice',
            [
                'type'            => Controls_Manager::RAW_HTML,
                'raw'             => esc_html__('Liquid glass effect works best with transparent backgrounds. For optimal results, ensure the parent section has a background image.', 'bdthemes-element-pack'),
                'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
                'condition' => [
                    'element_pack_backdrop_filter' => 'yes',
                    'element_pack_backdrop_filter_type' => 'liquid_glass'
                ],
            ]
        );

        $widget->end_popover();

        $widget->add_control(
            'ep_backdrop_filter_notice',
            [
                'type'            => Controls_Manager::RAW_HTML,
                /* translators: %1$s and %2$s are HTML tags for a link */
                'raw'             => sprintf(esc_html__('This feature will not work in the Firefox browser untill you enable browser compatibility so please %1$s look here %2$s', 'bdthemes-element-pack'), '<a href="https://developer.mozilla.org/en-US/docs/Web/CSS/backdrop-filter#Browser_compatibility" target="_blank">', '</a>'),
                'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
            ]
        );
    }

    protected function add_actions() {
        add_action('elementor/element/column/section_style/before_section_end', [$this, 'register_controls'], 10, 2);
        add_action('elementor/element/common/_section_background/before_section_end', [$this, 'register_controls'], 10, 2);
        add_action('elementor/element/container/section_background/before_section_end', [$this, 'register_controls'], 10, 2);
        add_action('elementor/element/section/section_background/before_section_end', [$this, 'register_controls'], 10, 2);
        
        add_action('elementor/frontend/before_render', [$this, 'add_liquid_glass_effects_svg_filter'], 10, 1);
        add_action('elementor/preview/enqueue_scripts', [$this, 'add_liquid_glass_effects_svg_filter'], 10, 1);

    }
    
    public function add_liquid_glass_effects_svg_filter( $element ) {
        if ( !\ElementPack\Element_Pack_Loader::elementor()->preview->is_preview_mode() && !\ElementPack\Element_Pack_Loader::elementor()->editor->is_edit_mode() ) {
            $settings = $element->get_settings_for_display();
            if ( !isset($settings['element_pack_backdrop_filter_type']) || 'liquid_glass' !== $settings['element_pack_backdrop_filter_type'] ) {
                return;
            }
        }
	
        ?>
        <svg style="display: none">
      <filter
        id="bdt-frosted"
        x="0%"
        y="0%"
        width="100%"
        height="100%"
        filterUnits="objectBoundingBox"
      >
        <feTurbulence
          type="fractalNoise"
          baseFrequency="0.01 0.01"
          numOctaves="1"
          seed="5"
          result="turbulence"
        />
        <!-- Seeds: 14, 17,  -->

        <feComponentTransfer in="turbulence" result="mapped">
          <feFuncR type="gamma" amplitude="1" exponent="10" offset="0.5" />
          <feFuncG type="gamma" amplitude="0" exponent="1" offset="0" />
          <feFuncB type="gamma" amplitude="0" exponent="1" offset="0.5" />
        </feComponentTransfer>

        <feGaussianBlur in="turbulence" stdDeviation="3" result="softMap" />

        <feSpecularLighting
          in="softMap"
          surfaceScale="5"
          specularConstant="1"
          specularExponent="100"
          lighting-color="white"
          result="specLight"
        >
          <fePointLight x="-200" y="-200" z="300" />
        </feSpecularLighting>

        <feComposite
          in="specLight"
          operator="arithmetic"
          k1="0"
          k2="1"
          k3="1"
          k4="0"
          result="litImage"
        />

        <feDisplacementMap
          in="SourceGraphic"
          in2="softMap"
          scale="150"
          xChannelSelector="R"
          yChannelSelector="G"
        />
      </filter>
</svg>
        <?php
    }


}
