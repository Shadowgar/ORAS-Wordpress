<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://theeventprime.com
 * @since      1.0.0
 *
 * @package    Eventprime_Event_Tickets
 * @subpackage Eventprime_Event_Tickets/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Eventprime_Event_Tickets
 * @subpackage Eventprime_Event_Tickets/public
 * @author     EventPrime <support@metagauss.com>
 */
class Eventprime_Event_Tickets_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Eventprime_Event_Tickets_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Eventprime_Event_Tickets_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		//wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/eventprime-event-tickets-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Eventprime_Event_Tickets_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Eventprime_Event_Tickets_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		//wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/eventprime-event-tickets-public.js', array( 'jquery' ), $this->version, false );

	}
        
        // enqueue ticket script on the booking detail page
        public function ep_event_ticket_add_custom_script() {
            wp_enqueue_script(
                'ep-event-ticket-booking-detail-script',
                plugin_dir_url(dirname(__FILE__)) . 'public/js/ep-event-ticket-booking-details.js',
                array( 'jquery' ), $this->version
            );
            wp_localize_script(
                'ep-event-ticket-booking-detail-script', 
                'ep_event_ticket_booking_detail', 
                array(
                    'ajaxurl'              => admin_url( 'admin-ajax.php' ),
                    'booking_print_ticket_nonce' => wp_create_nonce( 'event-booking-print-ticket-nonce' ),
                    'booking_share_ticket_nonce' => wp_create_nonce( 'event-booking-share-ticket-nonce' )
                )
            );
        }
        
        // add custom header in the booking detail attendee table
        public function ep_event_ticket_booking_detail_attendee_table_header() {
            $basic_functions = new Eventprime_Basic_Functions;
            $allow_event_tickets = $basic_functions->ep_get_global_settings('allow_event_tickets');
            if( empty( $allow_event_tickets ) ) return;?>
            <th scope="col">&nbsp;</th><?php
        }
        
        public function ep_event_ticket_allowed_condition($booking, $ticket_id = null){
            $basic_functions = new Eventprime_Basic_Functions;
            $template_id = '';    
            if(!empty($ticket_id) && !empty($booking)){
                
               // $event_controller = EventM_Factory_Service::ep_get_instance( 'EventM_Event_Controller_List' );
                $tickets = $basic_functions->get_event_solo_ticket($booking->em_event);
                if(!empty($tickets)){
                    foreach($tickets as $ticket){
                        if($ticket_id == $ticket->id){
                            $template_id = $ticket->ticket_template_id;
                            break;
                        }
                    }
                }
                $category_tickets = $basic_functions->get_event_ticket_category($booking->em_event);
                if (!empty($category_tickets)) {
                    foreach ($category_tickets as $category_tic) {

                        if (!empty($category_tic->tickets)) {
                            foreach ($category_tic->tickets as $ticket) {
                                if ($ticket_id == $ticket->id) {
                                    $template_id = $ticket->ticket_template_id;
                                    break;
                                }
                            }
                        }
                    }
                }
            }
            return $template_id;
        }
        
        // add custom data in the booking detail attendee table
        public function ep_event_ticket_booking_detail_attendee_table_data( $booking_attendees_val, $ticket_id, $booking_id ) {
            $basic_functions = new Eventprime_Basic_Functions;
            $allow_event_tickets = $basic_functions->ep_get_global_settings('allow_event_tickets');
            if( empty( $allow_event_tickets ) ) return;
            $seat_no = $email_id = '';
            $venue_type = 'standings';
            $rand_attendee_num = rand( 1, 100 );
            $booking_controller = new EventPrime_Bookings;
            $booking = $booking_controller->load_booking_detail( $booking_id );
            $allowed =  $this->ep_event_ticket_allowed_condition($booking, $ticket_id);
            
            if(empty($allowed)){return;} 

            // if booking cancelled then not show print options
            if( $booking->em_status == 'cancelled' ) return;

            // if Offline payment is pending then don't show print and share buttons *****
            if ( $booking->em_payment_log['payment_gateway'] == 'offline' && isset($booking->em_payment_log['offline_status']) && $booking->em_payment_log['offline_status'] != 'Received'  ) return; 
            
            
            if( ! empty( $booking->event_data->venue_details ) && ! empty( $booking->event_data->venue_details->em_type ) ) {
                $venue_type = $booking->event_data->venue_details->em_type;
            }
            // check for seat no.
            foreach( $booking_attendees_val as $attendee_data ) {
                if( isset( $attendee_data['seat'] ) && ! empty( $attendee_data['seat'] ) ) {
                    $seat_no = $attendee_data['seat'];
                    break;
                }
            }
            // check for email
            foreach( $booking_attendees_val as $attendee_data ) {
                foreach( $attendee_data as $ad ) {
                    if( filter_var( $ad, FILTER_VALIDATE_EMAIL ) ) {
                        $email_id = $ad;
                        break;
                    }
                }
            }
            $seat_data = array();
            $seat_data['seat_no'] = base64_encode( $seat_no );
            $seat_data['booking_id'] = base64_encode( $booking_id );
            $seat_data['ticket_id'] = base64_encode( $ticket_id );
            $seat_data['email_id'] = base64_encode( $email_id );
            $seat_data['venue_type'] = base64_encode( $venue_type );
            $seat_data['rand_attendee_num'] = base64_encode( $rand_attendee_num );?>
            <td class="py-3">
                <span class="material-icons-outlined ep-fs-6 ep-mr-2 ep-cursor ep_booking_print_ticket" data-seat_data='<?php echo json_encode( $seat_data );?>'>print</span>
                <span class="material-icons-outlined ep-fs-6 ep-cursor ep_booking_share_ticket" ep-modal-open="ep_booking_detail_share_ticket_<?php echo esc_attr( $ticket_id.$rand_attendee_num );?>_modal" data-seat_data='<?php echo json_encode( $seat_data );?>' data-attendee_num="<?php echo esc_attr( $rand_attendee_num );?>">share</span>
                <div class="ep-modal ep-modal-view" id="ep_booking_detail_share_ticket_<?php echo esc_attr( $ticket_id.$rand_attendee_num );?>" ep-modal="ep_booking_detail_share_ticket_<?php echo esc_attr( $ticket_id.$rand_attendee_num );?>_modal" style="display: none;">
                    <div class="ep-modal-overlay" ep-modal-close="ep_booking_detail_share_ticket_<?php echo esc_attr( $ticket_id.$rand_attendee_num );?>_modal"></div>
                    <div class="ep-modal-wrap ep-modal-lg">
                        <div class="ep-modal-content">
                            <div class="ep-modal-body">
                                <div class="ep-modal-titlebar ep-d-flex ep-items-center">
                                    <h3 class="ep-modal-title ep-px-3 ">
                                        <?php esc_html_e('Share ticket', 'eventprime-event-tickets'); ?>
                                    </h3>
                                    <a href="#" class="ep-modal-close close-popup" ep-modal-close="ep_booking_detail_share_ticket_<?php echo esc_attr( $ticket_id.$rand_attendee_num );?>_modal"">&times;</a>
                                </div>
                                <div class="ep-modal-content-wrap">
                                    <div class="ep-box-row">
                                        <input type="hidden" name="ep_event_ticket_seat_data_<?php echo esc_attr( $ticket_id.$rand_attendee_num );?>" id="ep_event_ticket_seat_data_<?php echo esc_attr( $ticket_id.$rand_attendee_num );?>" value='<?php echo json_encode( $seat_data );?>' />
                                        <div class="ep-box-col-12 ep-py-3">
                                            <label class="ep-form-label">
                                                <?php esc_html_e( 'Subject', 'eventprime-event-tickets' ); ?>
                                            </label>
                                            <input type="text" class="ep-form-control" name="ep_event_ticket_subject_share_<?php echo esc_attr( $ticket_id.$rand_attendee_num );?>" id="ep_event_ticket_subject_share_<?php echo esc_attr( $ticket_id.$rand_attendee_num );?>" value="">
                                        </div>
                                        <div class="ep-box-col-12 ep-py-3">
                                            <label class="ep-form-label">
                                                <?php esc_html_e( 'Email address', 'eventprime-event-tickets' ); ?>
                                            </label>
                                            <input type="text" class="ep-form-control" name="ep_event_ticket_email_address_share_<?php echo esc_attr( $ticket_id.$rand_attendee_num );?>" id="ep_event_ticket_email_address_share_<?php echo esc_attr( $ticket_id.$rand_attendee_num );?>" value="<?php echo esc_attr( $email_id );?>">
                                        </div>
                                        <div class="ep-box-col-12 ep-py-3 ep-event-ticket-email-content" id="ep_event_ticket_email_address_share_<?php echo esc_attr( $ticket_id.$rand_attendee_num );?>">
                                            <?php
                                            $rnum = $ticket_id.$rand_attendee_num;
                                            $wp_edi_id = "ep_event_ticket_message_share_$rnum";
                                            $settings  = array( 'media_buttons' => false );
                                            ob_start();
                                                wp_editor( '', $wp_edi_id, $settings );
                                            $temp = ob_get_clean();
                                            echo $temp;
                                            ?>
                                        </div>
                                    </div>
                                    <div class="ep-modal-footer ep-mt-3 ep-d-flex ep-items-end ep-content-right">
                                        <div class="ep-loader" style="display:none"></div>
                                        <span class="ep-error-message ep-box-col-5 ep-mr-2 ep-mb-2 ep-text-end"></span>
                                        <button type="button" class="button ep-mr-3 ep-modal-close close-popup" ep-modal-close="ep_booking_detail_share_ticket_<?php echo esc_attr( $ticket_id.$rand_attendee_num );?>_modal""><?php esc_html_e( 'Close', 'eventprime-event-tickets' ); ?></button>
                                        <button type="button" class="button button-primary button-large ep_event_ticket_share_on_email" data-ticket_id="<?php echo esc_attr( $ticket_id );?>" data-attendee_num="<?php echo esc_attr( $rand_attendee_num );?>"><?php esc_html_e( 'Share', 'eventprime-event-tickets' ); ?></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </td><?php
        }

        // Attach tickets to confirmation mail. 
        public function ep_attach_ticket_to_booking_conf_mail( $attachments, $booking ) {
            $basic_functions = new Eventprime_Basic_Functions;
            $allow_event_tickets = $basic_functions->ep_get_global_settings('allow_event_tickets');
            if( empty( $allow_event_tickets ) ) return;

            $ticket_controller = new EventM_Ticket_Controller_List;

            $booking_id = $booking->em_id;
            $seat_no = '';
            // $ticket_id = $booking->em_order_info['tickets'][0]->id;
            $tickets_arr = $booking->em_order_info['tickets'];
            // $ticket_ids_arr = $booking->em_order_info['tickets'];

            $ticket_no = 1;     // Ticket no. 
            
            $attendees = $booking->em_attendee_names;
            if(!empty($attendees)){
                foreach($attendees as $ticket_id => $attendee){
                    
                    $allowed =  $this->ep_event_ticket_allowed_condition($booking, $ticket_id);
                    if(!empty($allowed)){
                        foreach($attendee as $att){
                            $seat_no = '';
                            if(isset($att['seat'])){
                                $seat_no = isset($att['seat']['seat']) ? $att['seat']['seat'] : '';
                            }
                            
                            $html = $ticket_controller->get_ticket_html( $booking, $seat_no, $ticket_id );

                            $html_args = array( 'name' => $booking->event_data->name.'-ticket'.$ticket_no, 'title' =>  esc_html__( 'Ticket','eventprime-event-tickets' ), 'ticket_id' => $ticket_id );
                            $pdf_url = $ticket_controller->save_ticket_html( $html, $html_args );
                            array_push( $attachments, $pdf_url );
                            $ticket_no++;
                        }
                    }
                }
            }
            return $attachments;
        }


        // print ticket from booking detail page
        public function ep_event_booking_print_ticket() {
            $basic_functions = new Eventprime_Basic_Functions;
            $allow_event_tickets = $basic_functions->ep_get_global_settings('allow_event_tickets');
            if( ! empty( $allow_event_tickets ) ) {
                if( wp_verify_nonce( $_POST['security'], 'event-booking-print-ticket-nonce' ) ) {
                    if( isset( $_POST['booking_id'] ) && isset( $_POST['venue_type'] ) && isset( $_POST['ticket_id'] ) ) {
                        $booking_id = absint( $_POST['booking_id'] );
                        $seat_no = sanitize_text_field( $_POST['seat_no'] );
                        $ticket_id = absint( $_POST['ticket_id'] );
                        $venue_type = sanitize_text_field( $_POST['venue_type'] );
                        if( ! empty( $booking_id ) && ! empty( $venue_type ) && ! empty( $ticket_id ) ) {
                            if( $seat_no == '' ) {
                                $response = admin_url( 'admin-ajax.php?action=ep_print_event_ticket&_nonce='.wp_create_nonce( 'ep-event-ticket' ).'&booking_id='.$booking_id.'&ticket_id='.$ticket_id );
                            } else{
                                $response = admin_url( 'admin-ajax.php?action=ep_print_event_ticket&_nonce='.wp_create_nonce( 'ep-event-ticket' ).'&booking_id='.$booking_id.'&seat_no='.$seat_no.'&ticket_id='.$ticket_id );
                            }
                            wp_send_json_success( array( 'url' => $response ) );
                        }
                    } else{
                        wp_send_json_error( array( 'error' => esc_html__( 'Data not found', 'eventprime-event-tickets' ) ) );
                    }
                } else{
                    wp_send_json_error( array( 'error' => esc_html__( 'Security check failed. Please refresh the page and try again later.', 'eventprime-event-tickets' ) ) );
                }
            } else{
                wp_send_json_error( array( 'error' => esc_html__( 'Data not found', 'eventprime-event-tickets' ) ) );
            }
        }

        public function ep_print_event_ticket() {
            $basic_functions = new Eventprime_Basic_Functions;
            $allow_event_tickets = $basic_functions->ep_get_global_settings('allow_event_tickets');
            if( ! empty( $allow_event_tickets ) ) {
                if( wp_verify_nonce( $_GET['_nonce'], 'ep-event-ticket' ) ) {
                    $booking_id = isset( $_GET['booking_id'] ) && ! empty( $_GET['booking_id'] ) ? (int) $_GET['booking_id'] : 0;
                    if( ! empty( $booking_id ) ) {
                        $booking_controller = new EventPrime_Bookings;
                        $booking = $booking_controller->load_booking_detail( $booking_id );
                        if( empty( $booking ) ) {
                            wp_die(  esc_html__( 'Booking not found.', 'eventprime-event-tickets' ) );
                        }
                        $seat_no = isset( $_GET['seat_no'] ) && ! empty( $_GET['seat_no'] ) ? $_GET['seat_no'] : '';
                        $ticket_id = isset( $_GET['ticket_id'] ) && ! empty( $_GET['ticket_id'] ) ? (int) $_GET['ticket_id'] : 0;
                        if( ! empty( $booking_id ) && ! empty( $ticket_id ) ) {
                            $ticket_controller = new EventM_Ticket_Controller_List;
                            $html = $ticket_controller->get_ticket_html( $booking, $seat_no, $ticket_id );

                            // epd( $html );

                            $html_args = array( 'name' => $booking->event_data->name.'-ticket', 'title' =>  esc_html__( 'Ticket','eventprime-event-tickets' ) );
                            $ticket_controller->print_ticket_html( $html, $html_args );
                            die;
                        }
                    } else{
                        wp_die(  esc_html__( 'Booking not found.', 'eventprime-event-tickets' ) );
                    }
                }else{
                    wp_die(  esc_html__( 'Security verification failed.', 'eventprime-event-tickets' ) );
                }
            } else{
                wp_send_json_error( array( 'error' => esc_html__( 'Data not found', 'eventprime-event-tickets' ) ) );
            }
            die;
        }

        // share the ticket
        public function ep_event_booking_share_ticket() {
            $basic_functions = new Eventprime_Basic_Functions;
            
            $allow_event_tickets = $basic_functions->ep_get_global_settings('allow_event_tickets');
            if( ! empty( $allow_event_tickets ) ) {
                if( wp_verify_nonce( $_POST['security'], 'event-booking-share-ticket-nonce' ) ) {
                    if( ! empty( $_POST['ticket_data'] ) ) {
                        $ticket_data = json_decode( stripslashes( $_POST['ticket_data'] ) );
                        $booking_id = $ticket_data->booking_id;
                        $booking_controller = new EventPrime_Bookings;
                        $booking = $booking_controller->load_booking_detail( $booking_id );
                        if( empty( $booking ) ) {
                            wp_send_json_error( array( 'error' => esc_html__( 'Booking Not Found.', 'eventprime-event-tickets' ) ) );
                        }
                        $seat_no    = $ticket_data->seat_no;
                        $ticket_id  = $ticket_data->ticket_id;
                        $email      = $ticket_data->email;
                        $venue_type = $ticket_data->venue_type;
                        if( ! empty( $booking_id ) && ! empty( $venue_type ) && ! empty( $ticket_id ) && ! empty( $email ) ) {
                            $ticket_controller = new EventM_Ticket_Controller_List;
                            $html = $ticket_controller->get_ticket_html( $booking, $seat_no, $ticket_id );

                            $pdf_name = strtolower( str_replace( ' ', '_', $booking->event_data->name ) );
                            if( $seat_no ) {
                                $pdf_name .= '_'.$seat_no;
                            }
                            $html_args = array( 'name' => $pdf_name, 'title' =>  esc_html__( 'Ticket','eventprime-event-tickets' ) );
                            $pdf_url = $ticket_controller->save_ticket_html( $html, $html_args );
                            if( ! empty( $pdf_url ) ) {
                                $to = $email;
                                $subject = ( ! empty( $ticket_data->subject ) ? $ticket_data->subject : esc_html__( 'Event Ticket', 'eventprime-event-tickets' ) );
                                $body = ( ! empty( $ticket_data->message ) ? $ticket_data->message : esc_html__( 'Event Ticket', 'eventprime-event-tickets' ) );
                                $headers[] = 'Content-Type: text/html; charset=UTF-8';
                                $from = get_bloginfo('name') . '<' . get_bloginfo('admin_email') . '>';
                                $headers[] = 'From: ' . $from;
                                $attachments = array( $pdf_url );

                                wp_mail( $to, $subject, $body, $headers, $attachments );
                                wp_send_json_success( array( 'message' => esc_html__( 'Ticket Sent.', 'eventprime-event-tickets' ) ) );
                            }
                        } else{
                            wp_send_json_error( array( 'error' => esc_html__( 'Required data is missing.', 'eventprime-event-tickets' ) ) );
                        }
                    } else{
                        wp_send_json_error( array( 'error' => esc_html__( 'Data not found.', 'eventprime-event-tickets' ) ) );
                    }
                } else{
                    wp_send_json_error( array( 'error' => esc_html__( 'Security verification failed.', 'eventprime-event-tickets' ) ) );
                }
            } else{
                wp_send_json_error( array( 'error' => esc_html__( 'Data not found', 'eventprime-event-tickets' ) ) );
            }
        }


}
