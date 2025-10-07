<?php

namespace ElementPack\Modules\TextGradientBackground;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use ElementPack;
use ElementPack\Base\Element_Pack_Module_Base;

if (!defined('ABSPATH')) {
	exit;
} // Exit if accessed directly

class Module extends Element_Pack_Module_Base {

	public function __construct() {
		parent::__construct();
		$this->add_actions();
	}

	public function get_name() {
		return 'bdt-text-gradient-background';
	}

	public function register_section($element) {
		$element->start_controls_section(
			'section_element_pack_tgb_controls',
			[
				'tab'   => Controls_Manager::TAB_ADVANCED,
				'label' => BDTEP_CP . esc_html__('Text Gradient Background', 'bdthemes-element-pack') . BDTEP_NC,
			]
		);
		$element->end_controls_section();
	}


	public function register_controls($widget, $args) {

		$widget->add_control(
			'element_pack_tgb_enable',
			[
				'label'              => esc_html__('Use Text Gradient Background?', 'bdthemes-element-pack'),
				'type'               => Controls_Manager::SWITCHER,
				'label_on'           => esc_html__('Yes', 'bdthemes-element-pack'),
				'label_off'          => esc_html__('No', 'bdthemes-element-pack'),
				'render_type'        => 'template',
				'frontend_available' => true,
			]
		);

		$widget->add_control(
			'element_pack_tgb_selector',
			[
				'label'              => esc_html__('Class & ID Selector', 'bdthemes-element-pack'),
				'placeholder'        => esc_html__('.class or #id', 'bdthemes-element-pack'),
				'description'        => esc_html__('Enter your class or id selector of your text parent tag. e.g: .my-class, #my-id', 'bdthemes-element-pack'),
				'type'               => Controls_Manager::TEXT,
				'frontend_available' => true,
				'condition'          => [
					'element_pack_tgb_enable' => 'yes',
				],
			]
		);

        $widget->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'element_pack_tgb_background',
                'selector' => '{{WRAPPER}} .element-pack-tgb-background',
                'condition' => [
					'element_pack_tgb_enable' => 'yes',
				],
            ]
        );
	}

    public function should_script_enqueue($widget) {
		if ('yes' === $widget->get_settings_for_display('element_pack_tgb_enable')) {
			wp_enqueue_script('ep-text-gradient-background');
		}
	}

	protected function add_actions() {

		add_action('elementor/element/common/_section_style/after_section_end', [$this, 'register_section']);

		add_action('elementor/element/common/section_element_pack_tgb_controls/before_section_end', [$this, 'register_controls'], 10, 2);

		//render scripts
		add_action('elementor/frontend/widget/before_render', [$this, 'should_script_enqueue']);

	}
        
}
