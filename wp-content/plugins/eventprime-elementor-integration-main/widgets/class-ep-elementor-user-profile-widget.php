<?php
/**
 * Elementor oEmbed Widget.
 *
 * Elementor widget that inserts an embbedable content into the page, from any given URL.
 *
 * @since 1.0.0
 */
class Elementor_EP_User_Profile_Widget extends \Elementor\Widget_Base {

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
		return 'eventprimeelementoruserprofile';
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
		return __( 'EventPrime User Profile', 'eventprime-elementor-integration' );
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
		return 'eicon-user-circle-o';
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
				'label' => __( 'EventPrime User Profile', 'eventprime-elementor-integration' ),
				'tab' 	=> \Elementor\Controls_Manager::TAB_CONTENT,
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
       
                $user_controller = new Eventprime_Event_Calendar_Management_Public('eventprime-elementor-integration', '1.0.0');
                echo $user_controller->load_profile($atts);
       
	}

}