<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://https://theeventprime.com
 * @since      1.0.0
 *
 * @package    Eventprime_Advanced_Reports
 * @subpackage Eventprime_Advanced_Reports/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Eventprime_Advanced_Reports
 * @subpackage Eventprime_Advanced_Reports/admin
 * @author     EventPrime <support@metagauss.com>
 */
class Eventprime_Advanced_Reports_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Eventprime_Advanced_Reports_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Eventprime_Advanced_Reports_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/eventprime-advanced-reports-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Eventprime_Advanced_Reports_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Eventprime_Advanced_Reports_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/eventprime-advanced-reports-admin.js', array( 'jquery' ), $this->version, false );

	}

    public function ep_plugin_activation_notice_fun() {
        if (!class_exists('Eventprime_Event_Calendar_Management')) {
            $this->EventPrime_installation();
        }
    }

    public function EventPrime_installation() {
        $plugin_slug = 'eventprime-event-calendar-management';
        $installUrl = admin_url('update.php?action=install-plugin&plugin=' . $plugin_slug);
        $installUrl = wp_nonce_url($installUrl, 'install-plugin_' . $plugin_slug);
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php printf(__("EventPrime Advanced Reports work with Eventprime Plugin. You can install it  from <a href='%s'>Here</a>.","eventprime-event-tickets"),plugin_basename ( plugin_dir_path( __DIR__ ) ), $installUrl); ?></p>
        </div>
        <?php
        deactivate_plugins( plugin_basename( plugin_dir_path( __DIR__ ) ) . '/eventprime-advanced-reports.php' );
    }

	/*
     * Download booking CSV
     */
	public function ep_download_report_bookings(){
        $data = $_POST;
        $start_date  = date('d-m-Y', strtotime('-6 days'));
        $end_date  = date('d-m-Y');
        $event_id = 'all';
        if( ! empty( $data ) ) {
            if( isset( $data['ep_filter_date'] ) && ! empty( $data['ep_filter_date'] ) ) {
                $date_range = sanitize_text_field( $data['ep_filter_date'] );
                $dates = explode( ' - ', $date_range );
                $start = isset( $dates[0] ) && ! empty( $dates[0] ) ? $dates[0] : '';
                $end = isset( $dates[1] ) && ! empty( $dates[1] ) ? $dates[1] : '';
                $start_date = date( 'd-m-Y', strtotime( $start ) );
                //$end_date = date( ep_get_datepicker_format(), strtotime( $end ) );
                $end_date = date( 'd-m-Y', strtotime( $end ) );
            }
            if(isset($data['event_id']) && !empty($data['event_id'])){
                $event_id = sanitize_text_field( $data['event_id'] );
            }
            $filter_args = new stdClass();
            $filter_args->start_date = $start_date;
            $filter_args->end_date = $end_date;
            $filter_args->event_id = $event_id;
            $report_controller = new EventM_Report_Controller_List;
            $booking_data = $report_controller->ep_booking_reports($filter_args);
            $export_services = new EventPrime_Reports_Export();
            
            if(isset($booking_data->posts)){
                if(isset($booking_data->posts)){
                    echo $export_services->process_booking_downloadable_csv($booking_data->posts);
                }
            }
            die;
        }
        
    }
	/*
     * Add reports tabs
     */
	public function ep_add_reports_tabs($tabs){
        unset($tabs['paymentspro']);
        unset($tabs['attendeespro']);
        //$tabs['events']     = array('label'=>esc_html__( 'Events', 'eventprime-event-calendar-management' ), 'status'=> 'active');
        $tabs['payments']   = array('label'=>esc_html__( 'Payments', 'eventprime-event-calendar-management' ), 'status'=> 'active');
        $tabs['attendees']  = array('label'=>esc_html__( 'Attendees', 'eventprime-event-calendar-management' ), 'status'=> 'active');
        
        return $tabs;
    }
	/*
     *@param $tabs_key
     */
    public function ep_reports_tabs_content($active_tab){
        $ep_function = new Eventprime_Basic_Functions;
        $events_lists = $ep_function->ep_get_events( array( 'id', 'name' ) );
        $report_service = new EventPrime_Reports_Common();
        $report_attendees_service = new EventPrime_Reports_Attendees();
        if($active_tab == 'events'){
            include __DIR__ .'/partials/tabs/events.php';
        }
        if($active_tab == 'attendees'){
            $attendees_data = $report_attendees_service->ep_attendee_reports();
            include __DIR__ .'/partials/tabs/attendees.php';
        }
        if($active_tab == 'payments'){
            $payments_data = $report_service->ep_payment_reports();
            include __DIR__ .'/partials/tabs/payments.php';
        }
    }

	/*
     * @param $payments_data
     */
    public function ep_payments_report_stat($payments_data){
        ob_start();
        include __DIR__ .'/partials/tabs/parts/payments/stat.php';
        echo ob_get_clean();
    }

	 /*
     * @param $payments_data
     */
    public function ep_payments_report_bookings_list($payments_data){
        ob_start();
        include __DIR__ .'/partials/tabs/parts/payments/booking-list.php';
        echo ob_get_clean();
    }
	/*
     * @param $payments_data
     */
    public function ep_payments_reports_bookings_list_load_more($payments_data){
        ob_start();
        include __DIR__ .'/partials/tabs/parts/payments/load-more-booking-list.php';
        echo ob_get_clean();
    }
	/*
     * @param ajax
     */
    public function ep_payment_reports_filter(){
        $report_service = new EventPrime_Reports_Common();
        $filter_data = $report_service->eventprime_payments_filters();
        wp_send_json_success($filter_data);
    }
	/*
     * Download Payments CSV
     */
    public function ep_download_report_payments(){
        
        $data = $_POST;
        //$start_date  = date(ep_get_datepicker_format(), strtotime('-6 days'));
        //$end_date  = date(ep_get_datepicker_format());
        $start_date  = date('d-m-Y', strtotime('-6 days'));
        $end_date  = date('d-m-Y');
        $event_id = 'all';
        if(!empty($data)){
            if( isset( $data['ep_filter_date'] ) && ! empty( $data['ep_filter_date'] ) ) {
                $date_range = sanitize_text_field( $data['ep_filter_date'] );
                $dates = explode( ' - ', $date_range );
                $start = isset( $dates[0] ) && ! empty( $dates[0] ) ? $dates[0] : '';
                $end = isset( $dates[1] ) && ! empty( $dates[1] ) ? $dates[1] : '';
                
                $start_date = date( 'd-m-Y', strtotime( $start ) );
                $end_date = date( 'd-m-Y', strtotime( $end ) );
            }
            if(isset($data['event_id']) && !empty($data['event_id'])){
                $event_id = sanitize_text_field( $data['event_id'] );
            }
            if(isset($data['payment_method']) && !empty($data['payment_method'])){
                $payment_method = sanitize_text_field( $data['payment_method'] );
            }
            $status = '';
            if(isset($data['status']) && !empty($data['status'])){
                $status = sanitize_text_field( $data['status'] );
            }
            $filter_args = new stdClass();
            $filter_args->start_date = $start_date;
            $filter_args->end_date = $end_date;
            $filter_args->event_id = $event_id;
            $filter_args->payment_method = $payment_method;
            $filter_args->status = $status;
            $report_service = new EventPrime_Reports_Common();
            $booking_data = $report_service->ep_payment_reports($filter_args);
            $export_services = new EventPrime_Reports_Export();
            
            if(isset($booking_data->posts)){
                if(isset($booking_data->posts)){
                    echo $export_services->process_booking_downloadable_csv($booking_data->posts);
                }
            }
            die;
        }   
    }
	/*
     * @param $attendees_data
     */
    public function ep_attendees_report_stat($attendees_data){
        ob_start();
        
        include __DIR__ .'/partials/tabs/parts/attendees/stat.php';
        
        echo ob_get_clean();    
    }
	/*
     * @param $attendees_data
     */    
    public function ep_attendees_report_bookings_list($attendees_data){
        ob_start();
        include __DIR__ .'/partials/tabs/parts/attendees/booking-list.php';
        
        echo ob_get_clean();   
    }
    
    /*
     * @param $attendees_data
     */
    public function ep_attendees_reports_bookings_list_load_more($attendees_data){
        ob_start();
        include __DIR__ .'/partials/tabs/parts/attendees/load-more-booking-list.php';
        
        echo ob_get_clean(); 
    }
	/*
     * @param ajax
     */
    public function ep_attendee_reports_filter(){
        $attendees_service = new EventPrime_Reports_Attendees();
        $filter_data = $attendees_service->eventprime_attendees_filters();
        wp_send_json_success($filter_data);
    }
	/*
     * Download Attendees CSV
     */
    public function ep_download_report_attendees(){
        
        $data = $_POST;
        $event_id = 'all';
        if(!empty($data)){
            
            if(isset($data['event_id']) && !empty($data['event_id'])){
                $event_id = sanitize_text_field( $data['event_id'] );
            }
            
            $filter_args = new stdClass();
            $filter_args->event_id = $event_id;
            $attendees_service = new EventPrime_Reports_Attendees();
            $booking_data = $attendees_service->ep_attendee_reports($filter_args);
            $export_services = new EventPrime_Reports_Export();
            
            if(isset($booking_data->posts)){
                if(isset($booking_data->posts)){
                    echo $export_services->process_attendees_downloadable_csv($booking_data->posts);
                }
            }
            die;
        }
    }
	/**
    * Add reports options in global settings object
    */
    public function ep_add_reports_global_setting_options( $settings, $options ) {
        if( ! empty( $options ) ) {
            // global settings option for license settings
            $settings->ep_reports_item_id          = 21781;
            $settings->ep_reports_item_name        = 'Advanced Reports';
            $settings->ep_reports_license_key      = ( property_exists( $options, 'ep_reports_license_key' ) ) ? $options->ep_reports_license_key : '';
            $settings->ep_reports_license_status   = ( property_exists( $options, 'ep_reports_license_status' ) ) ? $options->ep_reports_license_status : '';
            $settings->ep_reports_license_response = ( property_exists( $options, 'ep_reports_license_response' ) ) ? $options->ep_reports_license_response : '';
        }
        return $settings;
    }
	public function ep_add_reports_license_setting_block( $options ){ 
        ?>
            <tr valign="top" class="ep_reports">
                <td><?php esc_html_e( 'Advanced Reports', 'eventprime-event-advanced-reports' );?></td>
                <td><input id="ep_reports_license_key" name="ep_reports_license_key" type="text" class="regular-text ep-box-wrap ep-license-block" data-prefix="ep_reports" value="<?php esc_attr_e( ( isset( $options->ep_reports_license_key ) && ! empty( $options->ep_reports_license_key ) ) ? $options->ep_reports_license_key : '' ); ?>" placeholder="<?php esc_html_e( 'Please Enter License Key', 'eventprime-event-advanced-reports' );?>" /></td>
                <td>         
                    <span class="license-expire-date" style="padding-bottom:2rem;" >
                        <?php
                        if ( isset( $options->ep_reports_license_response->expires ) && ! empty( $options->ep_reports_license_response->expires ) ) {
                            if( $options->ep_reports_license_response->expires == 'lifetime' ){
                                esc_html_e( 'Your License key is activated for lifetime', 'eventprime-event-advanced-reports' );
                            }else{
                                echo sprintf( __('Your License Key expires on %s', 'eventprime-event-advanced-reports' ), date( 'F d, Y', strtotime( $options->ep_reports_license_response->expires ) ) );
                            }
                        } else {
                            $expire_date = '';
                        }
                        ?>
                    </span>
                </td>
                <td>
                    <span class="ep_reports-license-status-block">
                        <?php if ( isset( $options->ep_reports_license_key ) && ! empty( $options->ep_reports_license_key )) { ?>
                            <?php if ( isset( $options->ep_reports_license_status ) && $options->ep_reports_license_status !== false && $options->ep_reports_license_status == 'valid') { ?>
                                <button type="button" class="button action ep-my-2 ep_license_deactivate" name="ep_reports_license_deactivate" id="ep_reports_license_deactivate" data-prefix="ep_reports" value="<?php esc_html_e( 'Deactivate License', 'eventprime-event-advanced-reports' );?>"><?php esc_html_e( 'Deactivate License', 'eventprime-event-advanced-reports' );?></button>
                            <?php }elseif( ! empty( $options->ep_reports_license_status ) && $options->ep_reports_license_status == 'invalid' ){ ?>
                                <button type="button" class="button action ep-my-2 ep_license_activate" name="ep_reports_license_activate" id="ep_reports_license_activate" data-prefix="ep_reports" value="<?php esc_html_e( 'Activate License', 'eventprime-event-advanced-reports' );?>"><?php esc_html_e( 'Activate License', 'eventprime-event-advanced-reports' );?></button>
                            <?php }else{ ?>
                                <button type="button" class="button action ep-my-2 ep_license_activate" name="ep_reports_license_activate" id="ep_reports_license_activate" data-prefix="ep_reports" value="<?php esc_html_e( 'Activate License', 'eventprime-event-advanced-reports' );?>" style="<?php if ( empty( $options->ep_reports_license_key ) ){ echo 'display:none'; } ?>"><?php esc_html_e( 'Activate License', 'eventprime-event-advanced-reports' );?></button>
                        <?php } }else{ ?>
                            <button type="button" class="button action ep-my-2 ep_license_activate" name="ep_reports_license_activate" id="ep_reports_license_activate" data-prefix="ep_reports" value="<?php esc_html_e( 'Activate License', 'eventprime-event-advanced-reports' );?>" style="display:none;"><?php esc_html_e( 'Activate License', 'eventprime-event-advanced-reports' );?></button>
                        <?php } ?>
                    </span>
                </td>
            </tr>
        <?php
    }
	public function ep_pupulate_reports_license_item_id( $item_id, $form_data ){
        if( isset( $form_data['ep_license_type'] ) && $form_data['ep_license_type'] == 'ep_reports' ){
            $global_settings = new Eventprime_Global_Settings();
            $options = $global_settings->ep_get_settings();
            $item_id  = ( isset(  $options->ep_reports_item_id ) && ! empty( $options->ep_reports_item_id ) ) ? $options->ep_reports_item_id : '';     
        }
        return $item_id; 
    }

    public function ep_pupulate_reports_license_item_name( $item_name, $form_data ){
        if( isset( $form_data['ep_license_type'] ) && $form_data['ep_license_type'] == 'ep_reports' ){
            $global_settings = new Eventprime_Global_Settings();
            $options = $global_settings->ep_get_settings();
            $item_name  = ( isset( $options->ep_reports_item_name ) && ! empty( $options->ep_reports_item_name ) ) ? $options->ep_reports_item_name : '';    
        }
        return $item_name;
    }

    public function ep_save_reports_license_setting( $form_data, $license_data ){
        if( isset( $form_data['ep_license_type'] ) && $form_data['ep_license_type'] == 'ep_reports' && ! empty( $license_data ) ){
            $global_settings = new Eventprime_Global_Settings();
            $options = $global_settings->ep_get_settings();
            // $license_data->license will be either "valid" or "invalid"
            $options->ep_reports_license_key  = ( isset( $form_data['ep_license_key'] ) && ! empty( $form_data['ep_license_key'] )  && ( $license_data->license == 'valid' || $license_data->license = 'deactivated' ) ) ? $form_data['ep_license_key'] : '';
            $options->ep_reports_license_status  = ( isset( $license_data->license ) && ! empty( $license_data->license ) ) ? $license_data->license : '';
            $options->ep_reports_license_response  = ( isset( $license_data ) && ! empty( $license_data ) ) ? $license_data : '';
            $global_settings->ep_save_settings( $options );
        
        }
    }
}
