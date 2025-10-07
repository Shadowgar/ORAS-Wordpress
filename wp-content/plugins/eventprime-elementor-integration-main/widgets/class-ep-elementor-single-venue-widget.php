<?php
/**
 * Elementor oEmbed Widget.
 *
 * Elementor widget that inserts an embbedable content into the page, from any given URL.
 *
 * @since 1.0.0
 */
class Elementor_EP_SingleVenue_Widget extends \Elementor\Widget_Base {

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
		return 'eventprimeelementorsinglevenue';
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
		return __( 'EventPrime Single Venue', 'eventprime-elementor-integration' );
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
		return 'eicon-google-maps';
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
            
            $epdb_handler = new EP_DBhandler();
            $event_sites_data = $epdb_handler->get_venues_data();
            $event_sites_list = array();
            if(isset($event_sites_data->terms) && !empty($event_sites_data->terms)){
                foreach( $event_sites_data->terms as $single_event_site ){
                    $event_sites_list[$single_event_site->id] = $single_event_site->name;
                }
            }

		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'EventPrime Single Venue', 'eventprime-elementor-integration' ),
				'tab' 	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'event_site_id',
			[
				'label' => esc_html__( 'Select Event Venue', 'eventprime-elementor-integration' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'grid',
				'options' => $event_sites_list,
			]
		);

		$this->add_control(
			'event_style',
			[
				'label' => esc_html__( 'Related Events View', 'eventprime-elementor-integration' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'grid',
				'options' => [
					'grid'  => esc_html__( 'Square Grid', 'eventprime-elementor-integration' ),
					'rows' => esc_html__( 'Stacked Rows', 'eventprime-elementor-integration' ),
					'plain_list' => esc_html__( 'Plain List', 'eventprime-elementor-integration' ),
				],
			]
		);

		$this->add_control(
			'event_limit',
			[
				'label' => esc_html__( 'Limit', 'eventprime-elementor-integration' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 0,
				'max' => 100,
				'step' => 1,
				'default' => 10,
			]
		);

		$this->add_control(
			'event_cols',
			[
				'label' => esc_html__( 'Events Columns', 'eventprime-elementor-integration' ),
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
			'hide_past_events',
			[
				'label' => esc_html__( 'Hide Past Events', 'eventprime-elementor-integration' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'eventprime-elementor-integration' ),
				'label_off' => esc_html__( 'No', 'eventprime-elementor-integration' ),
				'return_value' => 'yes',
				'default' => 'yes',
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
        $event_site_id = absint( $settings['event_site_id'] );
        $atts['id']                     = isset( $event_site_id ) ? $event_site_id : '';
        if( isset( $atts['id'] ) && ! empty( $atts['id'] ) ){
            $atts['event_style']            =   isset( $settings['event_style'] ) ? $settings["event_style"] : 'grid';
            $atts['event_limit']            =   isset( $settings['event_limit'] ) ? $settings['event_limit'] : 10; 
            $atts['event_cols']             =   isset( $settings['event_cols'] ) ? $settings['event_cols'] : 4;
            $settings['load_more']          =   ( $settings['load_more'] == 'yes' ) ? 1 : 0;
            $atts['load_more']              =   isset( $settings['load_more'] ) ? $settings['load_more'] : 1;
            $settings['hide_past_events']   =   ( $settings['hide_past_events'] == 'yes' ) ? 1 : 0;
            $atts['hide_past_events']       =   isset( $settings['hide_past_events'] ) ? $settings['hide_past_events'] : 0;

            $event_controller = new Eventprime_Event_Calendar_Management_Public('eventprime-elementor-integration', '1.0.0');
            echo $event_controller->load_single_venue($atts);
        }

	}

}