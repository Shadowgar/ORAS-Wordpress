<?php
/**
 * Elementor oEmbed Widget.
 *
 * Elementor widget that inserts an embbedable content into the page, from any given URL.
 *
 * @since 1.0.0
 */
class Elementor_EP_All_Events_Widget extends \Elementor\Widget_Base {

	public function __construct( $data = [], $args = null ) {
	    parent::__construct( $data, $args );
		
		// common js for admin and front both
		/*wp_enqueue_script(
			'ep-common-script',
			EP_BASE_URL . '/includes/assets/js/ep-common-script.js',
			array( 'jquery' ), EVENTPRIME_VERSION
		);*/

		// load calendar library
		/* wp_enqueue_style(
			'ep-front-event-calendar-css',
			EP_BASE_URL . '/includes/assets/css/ep-calendar.min.css',
			false, EVENTPRIME_VERSION
		);
		wp_enqueue_script(
			'ep-front-event-calendar-js',
			EP_BASE_URL . '/includes/assets/js/ep-calendar.min.js',
			false, EVENTPRIME_VERSION
		);
		wp_enqueue_script(
			'ep-front-event-fulcalendar-local-js',
			EP_BASE_URL . '/includes/assets/js/locales-all.js',
			array( 'jquery' ), EVENTPRIME_VERSION
		); */

		// localized global settings
                $basic_functions = new Eventprime_Basic_Functions();
		$global_settings = $basic_functions->ep_get_global_settings();
		$currency_symbol = $basic_functions->ep_currency_symbol();
		$datepicker_format = $basic_functions->ep_get_datepicker_format( 2 );
		wp_localize_script(
			'ep-common-script', 
			'eventprime', 
			array(
				'global_settings'      => $global_settings,
				'currency_symbol'      => $currency_symbol,
				'ajaxurl'              => admin_url( "admin-ajax.php"),
				'trans_obj'            => $basic_functions->ep_define_common_field_errors(),
				'event_wishlist_nonce' => wp_create_nonce( 'event-wishlist-action-nonce' ),
				'security_nonce_failed'=> esc_html__( 'Security check failed. Please refresh the page and try again later.', 'eventprime-event-calendar-management' ),
				'datepicker_format'    => $datepicker_format
			)
		);
		wp_enqueue_style( 'ep-elementor-public', plugin_dir_url(dirname(__FILE__))."widgets/css/ep-elementor-public.css", null, null, 'all' ); 
		wp_enqueue_style( 'ep-front-events-css', plugin_dir_url(EP_PLUGIN_FILE)."public/css/ep-frontend-events.css", null, null, 'all' );
                wp_enqueue_style('ep-responsive-slides-css');
                wp_enqueue_script('ep-responsive-slides-js');
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
		return 'eventprimeelementorallevents';
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
		return __( 'EventPrime All Events', 'eventprime-elementor-integration' );
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
		return 'eicon-archive-posts';
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
        $event_types_data = $epdb_handler->get_event_types_data();
        $event_types_list = array();

		if ( isset($event_types_data->terms) && !empty($event_types_data->terms) ) {
			foreach( $event_types_data->terms as $single_event_type ){
				$event_types_list[$single_event_type->id] = $single_event_type->name;
			}    
		}    

		$event_sites_data = $epdb_handler->get_venues_data();
        $event_sites_list = array();

		if ( isset($event_sites_data->terms) && !empty($event_sites_data->terms) ) {
			foreach( $event_sites_data->terms as $single_event_site ){
				$event_sites_list[$single_event_site->id] = $single_event_site->name;
			}
		}

		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'EventPrime All Events', 'eventprime-elementor-integration' ),
				'tab' 	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'view',
			[
				'label' => esc_html__( 'View', 'eventprime-elementor-integration' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'square_grid',
				'options' => [
					'square_grid'  => esc_html__( 'Square Grid', 'eventprime-elementor-integration' ),
					'staggered_grid' => esc_html__( 'Staggered Grid', 'eventprime-elementor-integration' ),
					'rows' => esc_html__( 'Rows', 'eventprime-elementor-integration' ),
					'slider' => esc_html__( 'Slider', 'eventprime-elementor-integration' ),
					'month' => esc_html__( 'Calendar / Month', 'eventprime-elementor-integration' ),
					'week' => esc_html__( 'Calendar / Week - Regular', 'eventprime-elementor-integration' ),
					'listweek' => esc_html__( 'Calendar / Week - Agenda', 'eventprime-elementor-integration' ),
					'day' => esc_html__( 'Calendar Day', 'eventprime-elementor-integration' ),
				],
			]
		);

		$this->add_control(
			'event_types',
			[
				'label' => esc_html__( 'Select Event-Types', 'eventprime-elementor-integration' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'multiple' => true,
				'options' => $event_types_list
			]
		);

		$this->add_control(
			'event_sites',
			[
				'label' => esc_html__( 'Select Event Venues', 'eventprime-elementor-integration' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'multiple' => true,
				'options' => $event_sites_list
			]
		);

		$this->add_control(
			'limit',
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
			'upcoming',
			[
				'label' => esc_html__( 'Show Only Past Events', 'eventprime-elementor-integration' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'eventprime-elementor-integration' ),
				'label_off' => esc_html__( 'No', 'eventprime-elementor-integration' ),
				'return_value' => 'yes',
				'default' => 'no',
			]
		);

		$this->add_control(
			'disable_filter',
			[
				'label' => esc_html__( 'Disable Filter', 'eventprime-elementor-integration' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'eventprime-elementor-integration' ),
				'label_off' => esc_html__( 'No', 'eventprime-elementor-integration' ),
				'return_value' => 'yes',
				'default' => '',
			]
		);

		$this->add_control(
			'filter_elements',
			[
				'label' => esc_html__( 'Filter Elements', 'eventprime-elementor-integration' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'multiple' => true,
				'options' => [
					'quick_search'  => esc_html__( 'Quick Search', 'eventprime-elementor-integration' ),
					'date_range' => esc_html__( 'Date Range', 'eventprime-elementor-integration' ),
					'event_type' => esc_html__( 'Event Type', 'eventprime-elementor-integration' ),
					'venue' => esc_html__( 'Venue', 'eventprime-elementor-integration' ),
					'performer' => esc_html__( 'Performer', 'eventprime-elementor-integration' ),
					'organizer' => esc_html__( 'Organizer', 'eventprime-elementor-integration' ),
				],

				'condition' => [
					'disable_filter' => '',
				],
			]
		);

		$this->add_control(
			'individual_events',
			[
				'label' => esc_html__( 'Individual Events', 'eventprime-elementor-integration' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					'' => esc_html__( 'Select Option', 'eventprime-elementor-integration' ),
					'yesterday'  => esc_html__( 'Yesterday', 'eventprime-elementor-integration' ),
					'today' => esc_html__( 'Today', 'eventprime-elementor-integration' ),
					'tomorrow' => esc_html__( 'Tomorrow', 'eventprime-elementor-integration' ),
					'this month' => esc_html__( 'This Month', 'eventprime-elementor-integration' ),
				],
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

		// $this->add_control(
		// 	'featured',
		// 	[
		// 		'label' => esc_html__( 'Enable Featured Events', 'eventprime-elementor-integration' ),
		// 		'type' => \Elementor\Controls_Manager::SWITCHER,
		// 		'label_on' => esc_html__( 'Yes', 'eventprime-elementor-integration' ),
		// 		'label_off' => esc_html__( 'No', 'eventprime-elementor-integration' ),
		// 		'return_value' => 'yes',
		// 		'default' => 'no',
		// 	]
		// );

		// $this->add_control(
		// 	'popular',
		// 	[
		// 		'label' => esc_html__( 'Enable Popular Events', 'eventprime-elementor-integration' ),
		// 		'type' => \Elementor\Controls_Manager::SWITCHER,
		// 		'label_on' => esc_html__( 'Yes', 'eventprime-elementor-integration' ),
		// 		'label_off' => esc_html__( 'No', 'eventprime-elementor-integration' ),
		// 		'return_value' => 'yes',
		// 		'default' => 'no',
		// 	]
		// );

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
                $basic_functions = new Eventprime_Basic_Functions();
		$settings = $this->get_settings_for_display();
		$ext_list = $basic_functions->ep_list_all_exts();
		$atts = array();
		$atts['view'] 				= 	isset( $settings['view'] ) ? $settings["view"] : 'square_grid'; 
		$atts['types'] 				= 	( isset( $settings['event_types'] ) && ! empty( $settings['event_types'] ) ) ? implode( ',', $settings['event_types'] ) : '';
		$atts['sites'] 				= 	( isset( $settings['event_sites'] ) && ! empty( $settings['event_sites'] ) ) ? implode( ',', $settings['event_sites'] ) : '';
		$atts['show']         		= 	isset( $settings['limit'] ) ? $settings['limit'] : 10; 
		$settings['upcoming'] 		= 	( $settings['upcoming'] == 'yes' ) ? 0 : 1;
		$atts['upcoming'] 			= 	( isset( $settings['upcoming'] ) && $settings['upcoming'] == 1 ) ? '' : 0;
		$settings['disable_filter'] = 	( $settings['disable_filter'] == 'yes' ) ? 1 : 0;
                $atts['disable_filter'] 	= 	isset( $settings['disable_filter'] ) ? $settings['disable_filter'] : 0;
		$atts['filter_elements'] 	= 	( isset( $settings['filter_elements'] ) & ! empty( $settings['filter_elements'] ) ) ? implode( ',', $settings['filter_elements'] ) : '';
		$atts['individual_events'] 	= 	isset( $settings['individual_events'] ) ? $settings['individual_events'] : '';
		$atts['cols']        		= 	isset( $settings['event_cols'] ) ? $settings['event_cols'] : 4;
		$settings['load_more'] 		= 	( $settings['load_more'] == 'yes' ) ? 1 : 0;
                $atts['load_more']     		= 	isset( $settings['load_more'] ) ? $settings['load_more'] : 1;
		if( in_array( "Event List Widgets", $ext_list ) ) {
			$ext_details = $basic_functions->em_get_more_extension_data('Event List Widgets'); 
			$ext_details['is_activate'] = 1;
			if( $ext_details['is_activate'] ){
				$atts = apply_filters( 'ep_elementor_attribute_render', $atts, $settings );
			}
		}

		$event_controller = new Eventprime_Event_Calendar_Management_Public('eventprime-elementor-integration', '1.0.0');
		echo $event_controller->load_events( $atts );
                ?>
                <script>
                jQuery( '.ep-event-loader' ).hide();
                </script>
                <?php 

	}

}