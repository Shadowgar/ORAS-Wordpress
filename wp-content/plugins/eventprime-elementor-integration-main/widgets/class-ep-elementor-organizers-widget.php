<?php
/**
 * Elementor oEmbed Widget.
 *
 * Elementor widget that inserts an embbedable content into the page, from any given URL.
 *
 * @since 1.0.0
 */
class Elementor_EP_Organizers_Widget extends \Elementor\Widget_Base {

	public function __construct( $data = [], $args = null ) {
	    parent::__construct( $data, $args );
	    wp_enqueue_style( 'ep-elementor-public', plugin_dir_url(dirname(__FILE__))."widgets/css/ep-elementor-public.css", null, null, 'all' );
	}
	/**
	 * Get widget name.
	 *
	 * Retrieve oEmbed widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'eventprimeelementororganizers';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve oEmbed widget title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'EventPrime Event Organizers', 'eventprime-elementor-integration' );
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve oEmbed widget icon.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-person';
	}

	/**
	 * Get widget categories.
	 *
	 * Retrieve the list of categories the oEmbed widget belongs to.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return [ 'eventprime' ];
	}

	/**
	 * Register EventPrime Elementor widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_controls() {
            

		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'EventPrime Event Organizers', 'eventprime-elementor-integration' ),
				'tab' 	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'display_style',
			[
				'label' => esc_html__( 'Display Style', 'eventprime-elementor-integration' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'grid',
				'options' => [
					'grid'  => esc_html__( 'Grid', 'eventprime-elementor-integration' ),
					'colored_grid' => esc_html__( 'Colored Grid', 'eventprime-elementor-integration' ),
					'rows' => esc_html__( 'Rows', 'eventprime-elementor-integration' ),
				],
			]
		);

		$this->add_control(
			'organizer_box_1_color',
			[
				'label' => esc_html__( 'Grid 1 Background Color', 'eventprime-elementor-integration' ),
				'type' => \Elementor\Controls_Manager::COLOR,
	
				'condition' => [
					'display_style' => 'colored_grid',
				]
			],
		);

		$this->add_control(
			'organizer_box_2_color',
			[
				'label' => esc_html__( 'Grid 2 Background Color', 'eventprime-elementor-integration' ),
				'type' => \Elementor\Controls_Manager::COLOR,
	
				'condition' => [
					'display_style' => 'colored_grid',
				]
			],
		);

		$this->add_control(
			'organizer_box_3_color',
			[
				'label' => esc_html__( 'Grid 3 Background Color', 'eventprime-elementor-integration' ),
				'type' => \Elementor\Controls_Manager::COLOR,
	
				'condition' => [
					'display_style' => 'colored_grid',
				]
			],
		);

		$this->add_control(
			'organizer_box_4_color',
			[
				'label' => esc_html__( 'Grid 4 Background Color', 'eventprime-elementor-integration' ),
				'type' => \Elementor\Controls_Manager::COLOR,
	
				'condition' => [
					'display_style' => 'colored_grid',
				]
			],
		);

		$this->add_control(
			'limit',
			[
				'label' => esc_html__( 'No. of Items to Fetch', 'eventprime-elementor-integration' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 0,
				'max' => 100,
				'step' => 1,
				'default' => 10,
			]
		);

		$this->add_control(
			'cols',
			[
				'label' => esc_html__( 'Grid Columns', 'eventprime-elementor-integration' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 4,
				'options' => [
					1  => esc_html__( 'One', 'eventprime-elementor-integration' ),
					2 => esc_html__( 'Two', 'eventprime-elementor-integration' ),
					3 => esc_html__( 'Three', 'eventprime-elementor-integration' ),
					4 => esc_html__( 'Four', 'eventprime-elementor-integration' ),
				],
			]
		);

		$this->add_control(
			'load_more',
			[
				'label' => esc_html__( 'Enable Load More', 'eventprime-elementor-integration' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'eventprime-elementor-integration' ),
				'label_off' => esc_html__( 'No', 'eventprime-elementor-integration' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'search',
			[
				'label' => esc_html__( 'Enable Search', 'eventprime-elementor-integration' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'eventprime-elementor-integration' ),
				'label_off' => esc_html__( 'No', 'eventprime-elementor-integration' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'featured',
			[
				'label' => esc_html__( 'Enable Featured Organizers', 'eventprime-elementor-integration' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'eventprime-elementor-integration' ),
				'label_off' => esc_html__( 'No', 'eventprime-elementor-integration' ),
				'return_value' => 'yes',
				'default' => 'no',
			]
		);

		$this->add_control(
			'popular',
			[
				'label' => esc_html__( 'Enable Popular Organizers', 'eventprime-elementor-integration' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'eventprime-elementor-integration' ),
				'label_off' => esc_html__( 'No', 'eventprime-elementor-integration' ),
				'return_value' => 'yes',
				'default' => 'no',
			]
		);

		$this->end_controls_section();


	}

	/**
	 * Render oEmbed widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		
		$atts = array();
		$atts['display_style'] = isset( $settings['display_style'] ) ? $settings["display_style"] : 'grid';
        
		$atts['organizer_box_color'][0] = isset( $settings['organizer_box_1_color'] ) ? $settings['organizer_box_1_color'] : '';
		$atts['organizer_box_color'][1] = isset( $settings['organizer_box_2_color'] ) ? $settings['organizer_box_2_color'] : '';
		$atts['organizer_box_color'][2] = isset( $settings['organizer_box_3_color'] ) ? $settings['organizer_box_3_color'] : '';
		$atts['organizer_box_color'][3] = isset( $settings['organizer_box_4_color'] ) ? $settings['organizer_box_4_color'] : '';

		$atts['limit']         = isset( $settings['limit'] ) ? $settings['limit'] : 10; 
        $atts['cols']        = isset( $settings['cols'] ) ? $settings['cols'] : 4;
	
		$settings['load_more'] = ( $settings['load_more'] == 'yes' ) ? 1 : 0;
        $atts['load_more']     = isset( $settings['load_more'] ) ? $settings['load_more'] : 1;

		$settings['search'] = ( $settings['search'] == 'yes' ) ? 1 : 0;
        $atts['search'] = isset( $settings['search'] ) ? $settings['search'] : 0;
        
		$settings['featured'] = ( $settings['featured'] == 'yes' ) ? 1 : 0;
		$atts['featured']      = isset( $settings["featured"] ) ? $settings["featured"] : 0;

		$settings['popular'] = ( $settings['popular'] == 'yes' ) ? 1 : 0;
        $atts['popular']       = isset( $settings["popular"] ) ? $settings["popular"] : 0;
        $event_controller = new Eventprime_Event_Calendar_Management_Public('eventprime-elementor-integration', '1.0.0');
        echo $event_controller->load_event_organizers($atts);

	}

}