<?php
/**
 * Elementor oEmbed Widget.
 *
 * Elementor widget that inserts an embbedable content into the page, from any given URL.
 *
 * @since 1.0.0
 */
class Elementor_EP_Fes_Form_Widget extends \Elementor\Widget_Base {

	public function __construct( $data = [], $args = null ) {
	    parent::__construct( $data, $args );
		wp_enqueue_style( 'ep-elementor-public', plugin_dir_url(dirname(__FILE__))."widgets/css/ep-elementor-public.css", null, null, 'all' );
		add_filter( 'ep_filter_frontend_event_submission_options', array( $this, 'event_prime_filter_frontend_event_submission_options' ), 10, 2 );
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
		return 'eventprimeelementorfesform';
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
		return __( 'EventPrime Frontend Event Submission Form', 'eventprime-elementor-integration' );
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
		return 'eicon-form-horizontal';
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
            $basic_functions = new Eventprime_Basic_Functions();
		$frontend_submission_roles = array();
		foreach( $basic_functions->ep_get_all_user_roles() as $key => $role ){
			$frontend_submission_roles[$key] = $role;
		}

		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'EventPrime Frontend Event Submission Form', 'eventprime-elementor-integration' ),
				'tab' 	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'ues_confirm_message',
			[
				'label' => esc_html__( 'Confirmation Message', 'eventprime-elementor-integration' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Use Confirm Message', 'eventprime-elementor-integration' ),
				'placeholder' => esc_html__( 'Use Confirm Message', 'eventprime-elementor-integration' ),
			]
		);

		$this->add_control(
			'allow_submission_by_anonymous_user',
			[
				'label' => esc_html__( 'Guest Submissions', 'eventprime-elementor-integration' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'eventprime-elementor-integration' ),
				'label_off' => esc_html__( 'No', 'eventprime-elementor-integration' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'ues_login_message',
			[
				'label' => esc_html__( 'Log-In Error', 'eventprime-elementor-integration' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Please login to submit your event', 'eventprime-elementor-integration' ),
				'placeholder' => esc_html__( 'Please login to submit your event', 'eventprime-elementor-integration' ),

				'condition' => [
					'allow_submission_by_anonymous_user' => '',
				],
			]
		);

		$this->add_control(
			'ues_default_status',
			[
				'label' => esc_html__( 'Default State', 'eventprime-elementor-integration' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'draft',
				'options' => [
					'publish' => esc_html__( 'Active', 'eventprime-elementor-integration' ),
					'draft' => esc_html__( 'Draft', 'eventprime-elementor-integration' ),
				],
			]
		);

		$this->add_control(
			'frontend_submission_roles',
			[
				'label' => esc_html__( 'Restrict by Roles', 'eventprime-elementor-integration' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'multiple' => true,
				'options' => $frontend_submission_roles,
			]
		);

		$this->add_control(
			'ues_restricted_submission_message',
			[
				'label' => esc_html__( 'Restriction Error', 'eventprime-elementor-integration' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'You are not authorised to access this page. Please contact with your administrator.', 'eventprime-elementor-integration' ),
				'placeholder' => esc_html__( 'You are not authorised to access this page. Please contact with your administrator.', 'eventprime-elementor-integration' ),
			]
		);

		$this->add_control(
			'fes_event_featured_image',
			[
				'label' => esc_html__( 'Event Featured Image', 'eventprime-elementor-integration' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'eventprime-elementor-integration' ),
				'label_off' => esc_html__( 'No', 'eventprime-elementor-integration' ),
				'return_value' => 'yes',
				'default' => 'yes',
			],
		);

		$this->add_control(
			'fes_event_booking',
			[
				'label' => esc_html__( 'Event Booking', 'eventprime-elementor-integration' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'eventprime-elementor-integration' ),
				'label_off' => esc_html__( 'No', 'eventprime-elementor-integration' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'fes_event_link',
			[
				'label' => esc_html__( 'Event Link', 'eventprime-elementor-integration' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'eventprime-elementor-integration' ),
				'label_off' => esc_html__( 'No', 'eventprime-elementor-integration' ),
				'return_value' => 'yes',
				'default' => 'yes',
			],
		);

		$this->add_control(
			'fes_event_type',
			[
				'label' => esc_html__( 'Event-Type', 'eventprime-elementor-integration' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'eventprime-elementor-integration' ),
				'label_off' => esc_html__( 'No', 'eventprime-elementor-integration' ),
				'return_value' => 'yes',
				'default' => 'yes',
			],
		);

		$this->add_control(
			'fes_new_event_type',
			[
				'label' => esc_html__( 'Add New Event-Type', 'eventprime-elementor-integration' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'eventprime-elementor-integration' ),
				'label_off' => esc_html__( 'No', 'eventprime-elementor-integration' ),
				'return_value' => 'yes',
				'default' => 'yes',
			],
		);

		$this->add_control(
			'fes_event_location',
			[
				'label' => esc_html__( 'Venues', 'eventprime-elementor-integration' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'eventprime-elementor-integration' ),
				'label_off' => esc_html__( 'No', 'eventprime-elementor-integration' ),
				'return_value' => 'yes',
				'default' => 'yes',
			],
		);

		$this->add_control(
			'fes_new_event_location',
			[
				'label' => esc_html__( 'Add New Venue', 'eventprime-elementor-integration' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'eventprime-elementor-integration' ),
				'label_off' => esc_html__( 'No', 'eventprime-elementor-integration' ),
				'return_value' => 'yes',
				'default' => 'yes',
			],
		);

		$this->add_control(
			'fes_event_performer',
			[
				'label' => esc_html__( 'Event Performer', 'eventprime-elementor-integration' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'eventprime-elementor-integration' ),
				'label_off' => esc_html__( 'No', 'eventprime-elementor-integration' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'fes_new_event_performer',
			[
				'label' => esc_html__( 'Add New Performer', 'eventprime-elementor-integration' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'eventprime-elementor-integration' ),
				'label_off' => esc_html__( 'No', 'eventprime-elementor-integration' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'fes_event_organizer',
			[
				'label' => esc_html__( 'Event Organizer', 'eventprime-elementor-integration' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'eventprime-elementor-integration' ),
				'label_off' => esc_html__( 'No', 'eventprime-elementor-integration' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'fes_new_event_organizer',
			[
				'label' => esc_html__( 'Add New Organizer', 'eventprime-elementor-integration' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'eventprime-elementor-integration' ),
				'label_off' => esc_html__( 'No', 'eventprime-elementor-integration' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		// Required Fields
		$this->add_control(
			'fes_event_description_req',
			[
				'label' => esc_html__( 'Event Description Required', 'eventprime-elementor-integration' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'eventprime-elementor-integration' ),
				'label_off' => esc_html__( 'No', 'eventprime-elementor-integration' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'fes_event_booking_req',
			[
				'label' => esc_html__( 'Event Booking Required', 'eventprime-elementor-integration' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'eventprime-elementor-integration' ),
				'label_off' => esc_html__( 'No', 'eventprime-elementor-integration' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		// $this->add_control(
		// 	'fes_booking_price_req',
		// 	[
		// 		'label' => esc_html__( 'Event Booking Price Required', 'eventprime-elementor-integration' ),
		// 		'type' => \Elementor\Controls_Manager::SWITCHER,
		// 		'label_on' => esc_html__( 'Yes', 'eventprime-elementor-integration' ),
		// 		'label_off' => esc_html__( 'No', 'eventprime-elementor-integration' ),
		// 		'return_value' => 'yes',
		// 		'default' => 'yes',
		// 	]
		// );

		// $this->add_control(
		// 	'fes_event_link_req',
		// 	[
		// 		'label' => esc_html__( 'Event Link Required', 'eventprime-elementor-integration' ),
		// 		'type' => \Elementor\Controls_Manager::SWITCHER,
		// 		'label_on' => esc_html__( 'Yes', 'eventprime-elementor-integration' ),
		// 		'label_off' => esc_html__( 'No', 'eventprime-elementor-integration' ),
		// 		'return_value' => 'yes',
		// 		'default' => 'yes',
		// 	]
		// );

		$this->add_control(
			'fes_event_type_req',
			[
				'label' => esc_html__( 'Event Type Required', 'eventprime-elementor-integration' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'eventprime-elementor-integration' ),
				'label_off' => esc_html__( 'No', 'eventprime-elementor-integration' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'fes_event_location_req',
			[
				'label' => esc_html__( 'Event Location Required', 'eventprime-elementor-integration' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'eventprime-elementor-integration' ),
				'label_off' => esc_html__( 'No', 'eventprime-elementor-integration' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'fes_event_performer_req',
			[
				'label' => esc_html__( 'Event Performer Required', 'eventprime-elementor-integration' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'eventprime-elementor-integration' ),
				'label_off' => esc_html__( 'No', 'eventprime-elementor-integration' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'fes_event_organizer_req',
			[
				'label' => esc_html__( 'Event Organizer Required', 'eventprime-elementor-integration' ),
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

		$ep_functions = new Eventprime_Basic_Functions();		

		$atts['ues_confirm_message'] = isset( $settings['ues_confirm_message'] ) ? $settings["ues_confirm_message"] : 'Thank you for submitting your event. We will review and publish it soon.';

        $settings['allow_submission_by_anonymous_user'] = ( $settings['allow_submission_by_anonymous_user'] == 'yes' ) ? 1 : 0;
        $atts['allow_submission_by_anonymous_user'] = isset( $settings['allow_submission_by_anonymous_user'] ) ? $settings['allow_submission_by_anonymous_user'] : 0;
		$atts['ues_login_message'] = isset( $settings['ues_login_message'] ) ? $settings["ues_login_message"] : 'Please login to submit your event.';
		$atts['ues_default_status'] = isset( $settings['ues_default_status'] ) ? $settings["ues_default_status"] : 'draft';
		$atts['frontend_submission_roles'] = isset( $settings['frontend_submission_roles'] ) ? $settings["frontend_submission_roles"] : '';
		$atts['ues_restricted_submission_message'] = isset( $settings['ues_restricted_submission_message'] ) ? $settings["ues_restricted_submission_message"] : 'You are not authorised to access this page. Please contact with your administrator.';

		// Form Sections
		$settings['fes_event_featured_image'] = ( $settings['fes_event_featured_image'] == 'yes' ) ? 1 : 0;
        $atts['fes_event_featured_image'] = isset( $settings['fes_event_featured_image'] ) ? $settings['fes_event_featured_image'] : 0;

		$settings['fes_event_booking'] = ( $settings['fes_event_booking'] == 'yes' ) ? 1 : 0;
        $atts['fes_event_booking'] = isset( $settings['fes_event_booking'] ) ? $settings['fes_event_booking'] : 0;

		$settings['fes_event_link'] = ( $settings['fes_event_link'] == 'yes' ) ? 1 : 0;
        $atts['fes_event_link'] = isset( $settings['fes_event_link'] ) ? $settings['fes_event_link'] : 0;

		$settings['fes_event_type'] = ( $settings['fes_event_type'] == 'yes' ) ? 1 : 0;
        $atts['fes_event_type'] = isset( $settings['fes_event_type'] ) ? $settings['fes_event_type'] : 0;

		$settings['fes_new_event_type'] = ( $settings['fes_new_event_type'] == 'yes' ) ? 1 : 0;
        $atts['fes_new_event_type'] = isset( $settings['fes_new_event_type'] ) ? $settings['fes_new_event_type'] : 0;

		$settings['fes_event_location'] = ( $settings['fes_event_location'] == 'yes' ) ? 1 : 0;
        $atts['fes_event_location'] = isset( $settings['fes_event_location'] ) ? $settings['fes_event_location'] : 0;

		$settings['fes_new_event_location'] = ( $settings['fes_new_event_location'] == 'yes' ) ? 1 : 0;
        $atts['fes_new_event_location'] = isset( $settings['fes_new_event_location'] ) ? $settings['fes_new_event_location'] : 0;

		$settings['fes_event_performer'] = ( $settings['fes_event_performer'] == 'yes' ) ? 1 : 0;
        $atts['fes_event_performer'] = isset( $settings['fes_event_performer'] ) ? $settings['fes_event_performer'] : 0;

		$settings['fes_new_event_performer'] = ( $settings['fes_new_event_performer'] == 'yes' ) ? 1 : 0;
        $atts['fes_new_event_performer'] = isset( $settings['fes_new_event_performer'] ) ? $settings['fes_new_event_performer'] : 0;
		
		$settings['fes_event_organizer'] = ( $settings['fes_event_organizer'] == 'yes' ) ? 1 : 0;
        $atts['fes_event_organizer'] = isset( $settings['fes_event_organizer'] ) ? $settings['fes_event_organizer'] : 0;

		$settings['fes_new_event_organizer'] = ( $settings['fes_new_event_organizer'] == 'yes' ) ? 1 : 0;
        $atts['fes_new_event_organizer'] = isset( $settings['fes_new_event_organizer'] ) ? $settings['fes_new_event_organizer'] : 0;

		// Required Fields
		$settings['fes_event_description_req'] = ( $settings['fes_event_description_req'] == 'yes' ) ? 1 : 0;
        $atts['fes_event_description_req'] = isset( $settings['fes_event_description_req'] ) ? $settings['fes_event_description_req'] : 0;

		$settings['fes_event_booking_req'] = ( $settings['fes_event_booking_req'] == 'yes' ) ? 1 : 0;
        $atts['fes_event_booking_req'] = isset( $settings['fes_event_booking_req'] ) ? $settings['fes_event_booking_req'] : 0;

		// $settings['fes_booking_price_req'] = ( $settings['fes_booking_price_req'] == 'yes' ) ? 1 : 0;
        // $atts['fes_booking_price_req'] = isset( $settings['fes_booking_price_req'] ) ? $settings['fes_booking_price_req'] : 0;

		// $settings['fes_event_link_req'] = ( $settings['fes_event_link_req'] == 'yes' ) ? 1 : 0;
        // $atts['fes_event_link_req'] = isset( $settings['fes_event_link_req'] ) ? $settings['fes_event_link_req'] : 0;

		$settings['fes_event_type_req'] = ( $settings['fes_event_type_req'] == 'yes' ) ? 1 : 0;
        $atts['fes_event_type_req'] = isset( $settings['fes_event_type_req'] ) ? $settings['fes_event_type_req'] : 0;

		$settings['fes_event_location_req'] = ( $settings['fes_event_location_req'] == 'yes' ) ? 1 : 0;
        $atts['fes_event_location_req'] = isset( $settings['fes_event_location_req'] ) ? $settings['fes_event_location_req'] : 0;

		$settings['fes_event_performer_req'] = ( $settings['fes_event_performer_req'] == 'yes' ) ? 1 : 0;
        $atts['fes_event_performer_req'] = isset( $settings['fes_event_performer_req'] ) ? $settings['fes_event_performer_req'] : 0;

		$settings['fes_event_organizer_req'] = ( $settings['fes_event_organizer_req'] == 'yes' ) ? 1 : 0;
        $atts['fes_event_organizer_req'] = isset( $settings['fes_event_organizer_req'] ) ? $settings['fes_event_organizer_req'] : 0;
        // $event_controller = new Eventprime_Event_Calendar_Management_Public('eventprime-elementor-integration', '1.0.0');
        $event_controller = new Eventprime_Event_Calendar_Management_Public('eventprime-event-calendar-management', EVENTPRIME_VERSION);

		echo $event_controller->load_event_submit_form($atts);

		// Once the FES is loaded, localize the below object 
		$el_widget_required_fields = array();
		// Filter required fields from $args 
		foreach( $atts as $key => $val) {
			if ( in_array( $key, array("fes_event_description_req", "fes_event_type_req", "fes_event_location_req", "fes_event_performer_req", "fes_event_organizer_req", "fes_event_booking_req" ) ) && !empty( $val ) ) {
				$el_widget_required_fields[ explode( '_req', $key )[0] ] = $val;
			}
		}
		wp_localize_script(
			'ep-front-events-fes-js', 
			// 'em_event_fes_object_el_widget', 
			'em_event_fes_object', 
			array(
                'before_event_scheduling' => esc_html__( 'Please choose start & end date before enable scheduling!', 'eventprime-event-calendar-management' ),
                'before_event_recurrence' => esc_html__( 'Please choose start & end date before enable recurrence!', 'eventprime-event-calendar-management' ),
                'add_schedule_btn'  	  => esc_html__( 'Add New Hourly Schedule', 'eventprime-event-calendar-management' ),
                'add_day_title_label'  	  => esc_html__( 'Title', 'eventprime-event-calendar-management' ),
                'start_time_label'  	  => esc_html__( 'Start Time', 'eventprime-event-calendar-management' ),
                'end_time_label'  	      => esc_html__( 'End Time', 'eventprime-event-calendar-management' ),
                'description_label'  	  => esc_html__( 'Description', 'eventprime-event-calendar-management' ),
                'remove_label'  	      => esc_html__( 'Remove', 'eventprime-event-calendar-management' ),
                'material_icons'          => $ep_functions->get_material_icons(),
                'icon_text'  	   	      => esc_html__( 'Icon', 'eventprime-event-calendar-management' ),
                'icon_color_text'  	      => esc_html__( 'Icon Color', 'eventprime-event-calendar-management' ),
                'additional_date_text' 	  => esc_html__( 'Date', 'eventprime-event-calendar-management' ),
                'additional_time_text' 	  => esc_html__( 'Time', 'eventprime-event-calendar-management' ),
                'optional_text' 	      => esc_html__( '(Optional)', 'eventprime-event-calendar-management' ),
                'additional_label_text'   => esc_html__( 'Label', 'eventprime-event-calendar-management' ),
                'countdown_activate_text' => esc_html__( 'Activates', 'eventprime-event-calendar-management' ),
                'countdown_activated_text'=> esc_html__( 'Activated', 'eventprime-event-calendar-management' ),
                'countdown_on_text'	      => esc_html__( 'On', 'eventprime-event-calendar-management' ),
                'countdown_ends_text'     => esc_html__( 'Ends', 'eventprime-event-calendar-management' ),
                'countdown_activates_on'  => array( 'right_away' => esc_html__( 'Right Away', 'eventprime-event-calendar-management' ), 'custom_date' => esc_html__( 'Custom Date', 'eventprime-event-calendar-management' ), 'event_date' => esc_html__( 'Event Date', 'eventprime-event-calendar-management' ), 'relative_date' => esc_html__( 'Relative Date', 'eventprime-event-calendar-management' ) ),
                'countdown_days_options'  => array( 'before' => esc_html__( 'Days Before', 'eventprime-event-calendar-management' ), 'after' => esc_html__( 'Days After', 'eventprime-event-calendar-management' ) ),
                'countdown_event_options' => array( 'event_start' => esc_html__( 'Event Start', 'eventprime-event-calendar-management' ), 'event_ends' => esc_html__( 'Event Ends', 'eventprime-event-calendar-management' ) ),
                'ticket_capacity_text'    => esc_html__( 'Capacity', 'eventprime-event-calendar-management' ),
                'add_ticket_text'    	  => esc_html__( 'Add Ticket Type', 'eventprime-event-calendar-management' ),
                'add_text'                => esc_html__( 'Add', 'eventprime-event-calendar-management' ),
                'edit_text'    	  	      => esc_html__( 'Edit', 'eventprime-event-calendar-management' ),
                'update_text'    	      => esc_html__( 'Update', 'eventprime-event-calendar-management' ),
                'add_ticket_category_text'=> esc_html__( 'Add Tickets Category', 'eventprime-event-calendar-management' ),
                'price_text'              => esc_html__( 'Fee Per Ticket', 'eventprime-event-calendar-management' ),
                'offer_text'		      => esc_html__( 'Offer', 'eventprime-event-calendar-management' ),
                'no_ticket_found_error'   => esc_html__( 'Booking will be turn off if no ticket found. Are you sure you want to continue?', 'eventprime-event-calendar-management' ),
                'max_capacity_error'      => esc_html__( 'Max allowed capacity is', 'eventprime-event-calendar-management' ),
                'max_less_then_min_error' => esc_html__( 'Maximum tickets number can\'t be less then minimum tickets number.', 'eventprime-event-calendar-management' ),
                'required_text'		      => esc_html__( 'Required', 'eventprime-event-calendar-management' ),
                'one_checkout_field_req'  => esc_html__( 'Please select atleast one attendee field.', 'eventprime-event-calendar-management' ),
                'no_name_field_option'    => esc_html__( 'Please select name field option.', 'eventprime-event-calendar-management' ),
                'some_issue_found'    	  => esc_html__( 'Some issue found. Please refresh the page and try again later.', 'eventprime-event-calendar-management' ),
                'fixed_field_not_selected'=> esc_html__( 'Please selecte fixed field.', 'eventprime-event-calendar-management' ),
                'fixed_field_term_option_required'=> esc_html__( 'Please select one terms option.', 'eventprime-event-calendar-management' ),
                'repeat_child_event_prompt'=> esc_html__( 'This event have multiple child events. They will be deleted after update event.', 'eventprime-event-calendar-management' ),
                'empty_event_title'       => esc_html__( 'Event title is required.', 'eventprime-event-calendar-management' ),
                'empty_start_date'        => esc_html__( 'Event start date is required.', 'eventprime-event-calendar-management' ),
                'end_date_less_from_start'=> esc_html__( 'Event end date can not be less than event start date.', 'eventprime-event-calendar-management' ),
                'event_required_fields'   => $el_widget_required_fields,
                'event_name_error'        => esc_html__( 'Event Name can not be empty.', 'eventprime-event-calendar-management' ),
                'event_desc_error'        => esc_html__( 'Event Description can not be empty.', 'eventprime-event-calendar-management' ),
                'event_start_date_error'  => esc_html__( 'Event start date can not be empty.', 'eventprime-event-calendar-management' ),
                'event_end_date_error'    => esc_html__( 'Event end date can not be empty.', 'eventprime-event-calendar-management' ),
                'event_custom_link_error' => esc_html__( 'Event Url can not be empty.', 'eventprime-event-calendar-management' ),
                'event_custom_link_val_error' => esc_html__( 'Please enter valid url.', 'eventprime-event-calendar-management' ),
                'event_type_error'        => esc_html__( 'Please Select Event Types.', 'eventprime-event-calendar-management' ),
                'event_type_name_error'   => esc_html__( 'Event Type name can not be empty.', 'eventprime-event-calendar-management' ),
                'event_venue_error'       => esc_html__( 'Please Select Event Venues.', 'eventprime-event-calendar-management' ),
                'event_venue_name_error'  => esc_html__( 'Event Venues name can not be empty.', 'eventprime-event-calendar-management' ),
                'event_performer_error'   => esc_html__( 'Please Select Event Performers.', 'eventprime-event-calendar-management' ),
                'event_performer_name_error' => esc_html__( 'Event Perfomer name can not be empty.', 'eventprime-event-calendar-management' ),
                'event_organizer_error'   => esc_html__( 'Please Select Event Organizers.', 'eventprime-event-calendar-management' ),
                'event_organizer_name_error' => esc_html__( 'Event Organizer name can not be empty.', 'eventprime-event-calendar-management' ),
                'fes_nonce'               => wp_create_nonce( 'ep-frontend-event-submission-nonce' ),
                'choose_image_label'      => esc_html__( 'Choose Image', 'eventprime-event-calendar-management' ),
                'use_image_label'         => esc_html__( 'Use Image', 'eventprime-event-calendar-management' ),
            )
		);

	}

	public function event_prime_filter_frontend_event_submission_options( $fes_data, $atts ){
		
		$fes_data->ues_confirm_message = isset( $atts['ues_confirm_message'] ) ? $atts['ues_confirm_message'] : $fes_data->ues_confirm_message;
		$fes_data->fes_event_featured_image = isset( $atts['fes_event_featured_image'] ) ? $atts['fes_event_featured_image'] : $fes_data->fes_event_featured_image;
		$fes_data->fes_event_booking = isset( $atts['fes_event_booking'] ) ? $atts['fes_event_booking'] : $fes_data->fes_event_booking;
		$fes_data->fes_event_booking_req = isset( $atts['fes_event_booking_req'] ) ? $atts['fes_event_booking_req'] : $fes_data->fes_event_booking_req;
		$fes_data->fes_event_link = isset( $atts['fes_event_link'] ) ? $atts['fes_event_link'] : $fes_data->fes_event_link;
		$fes_data->fes_event_type = isset( $atts['fes_event_type'] ) ? $atts['fes_event_type'] : $fes_data->fes_event_type;
		$fes_data->fes_new_event_type = isset( $atts['fes_new_event_type'] ) ? $atts['fes_new_event_type'] : $fes_data->fes_new_event_type;
		$fes_data->fes_event_location = isset( $atts['fes_event_location'] ) ? $atts['fes_event_location'] : $fes_data->fes_event_location;
		$fes_data->fes_new_event_location = isset( $atts['fes_new_event_location'] ) ? $atts['fes_new_event_location'] : $fes_data->fes_new_event_location;
		$fes_data->fes_event_performer = isset( $atts['fes_event_performer'] ) ? $atts['fes_event_performer'] : $fes_data->fes_event_performer;
		$fes_data->fes_new_event_performer = isset( $atts['fes_new_event_performer'] ) ? $atts['fes_new_event_performer'] : $fes_data->fes_new_event_performer;
		$fes_data->fes_event_organizer = isset( $atts['fes_event_organizer'] ) ? $atts['fes_event_organizer'] : $fes_data->fes_event_organizer;
		$fes_data->fes_new_event_organizer = isset( $atts['fes_new_event_organizer'] ) ? $atts['fes_new_event_organizer'] : $fes_data->fes_new_event_organizer;
		$fes_data->fes_event_organizer_req = isset( $atts['fes_event_organizer_req'] ) ? $atts['fes_event_organizer_req'] : $fes_data->fes_event_organizer_req;
		$fes_data->fes_event_more_options = isset( $atts['fes_event_more_options'] ) ? $atts['fes_event_more_options'] : $fes_data->fes_event_more_options;
		$fes_data->fes_event_description_req = isset( $atts['fes_event_description_req'] ) ? $atts['fes_event_description_req'] : $fes_data->fes_event_description_req;
		$fes_data->fes_booking_price_req = isset( $atts['fes_booking_price_req'] ) ? $atts['fes_booking_price_req'] : $fes_data->fes_booking_price_req;
		$fes_data->fes_event_link_req = isset( $atts['fes_event_link_req'] ) ? $atts['fes_event_link_req'] : $fes_data->fes_event_link_req;
		$fes_data->fes_event_type_req = isset( $atts['fes_event_type_req'] ) ? $atts['fes_event_type_req'] : $fes_data->fes_event_type_req;
		$fes_data->fes_event_location_req = isset( $atts['fes_event_location_req'] ) ? $atts['fes_event_location_req'] : $fes_data->fes_event_location_req;
		$fes_data->fes_event_performer_req = isset( $atts['fes_event_performer_req'] ) ? $atts['fes_event_performer_req'] : $fes_data->fes_event_performer_req;
		$fes_data->fes_event_organizer_req = isset( $atts['fes_event_organizer_req'] ) ? $atts['fes_event_organizer_req'] : $fes_data->fes_event_organizer_req;

		return $fes_data;
	}

}