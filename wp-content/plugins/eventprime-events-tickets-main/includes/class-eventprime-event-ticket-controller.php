<?php
/**
 * Class for return ticket data
 */

defined( 'ABSPATH' ) || exit;

class EventM_Ticket_Controller_List {
    /**
     * Post Type.
     * 
     * @var string
     */
    private $post_type = 'em_ticket';

    /**
     * Return all tickets posts
     */
    public function get_ticket_all_data( $args = array() ) {
        $db_builder = new EP_DBhandler;
        $default = array(
            'orderby'          => 'title',
            'numberposts'      => -1,
            'offset'           => 0,     
            'order'            => 'ASC',
            'post_type'        => $this->post_type,
            'post_status'      => 'publish',
            
        );
        $args = wp_parse_args( $args, $default );
        //$posts = get_posts( $args );
        $posts = $db_builder->eventprime_get_all_posts($this->post_type, 'posts', 'publish');
        return $posts;
    }

    /**
     * Get specific data from posts
     */
    public function get_ticket_field_data( $fields = array() ) {
        $response = array();
        $posts = $this->get_ticket_all_data();
        if( !empty($posts) && count($posts) > 0 ) {
            foreach( $posts as $post ) {
                $post_data = array();
                if( !empty( $fields ) ) {
                    if( in_array( 'id', $fields, true ) ) {
                        $post_data['id'] = $post->ID;
                    }
                    
                    if( in_array( 'name', $fields, true ) ) {
                        $post_data['name'] = $post->post_title;
                    }
                }
                if( ! empty( $post_data ) ) {
                    $response[] = $post_data;
                }
            }
        }
        return $response;
    }
    
    /**
     * Get single ticket data.
     * 
     * @param int $post_id Post ID.
     * 
     * @return objact $ticket $ticket Data.
     */
    public function get_single_ticket( $post_id, $post = null ) {
        if( empty( $post_id ) ) return;

        $ticket = new stdClass();
        $meta = get_post_meta( $post_id );
        foreach ( $meta as $key => $val ) {
            $ticket->{$key} = maybe_unserialize( $val[0] );
        }
        if( empty( $post ) ) {
            $post = get_post( $post_id );
        }

        $ticket->id            = $post->ID;
        $ticket->name          = $post->post_title;
        $ticket->slug          = $post->post_name;
        $ticket->description   = $post->post_content;
        return $ticket;
    }
    
    /**
     * Get post data
     */
    public function get_tickets_post_data( $args = array() ) {
        $db_builder = new EP_DBhandler;
        
        $posts = $db_builder->eventprime_get_all_posts($this->post_type, 'posts', 'publish');
        
        if( empty( $posts ) )
           return array();
       
        $tickets = array();
        foreach( $posts as $post ) {
            $ticket = $this->get_single_ticket( $post->ID, $post );
            if( ! empty( $ticket ) ) {
                $tickets[] = $ticket;
            }
        }

        $wp_query = new WP_Query( $args );
        $wp_query->posts = $tickets;

        return $wp_query;
    }

    public function ep_get_tickets( $fields ) {
        $tickets_data = array();
        if( ! empty( $fields ) ) {
            $tickets_data = $this->get_ticket_field_data( $fields );
        }
        return $tickets_data;
    }
    
    /**
     * Print Ticket
     * 
     * @param int $booking_id Booking ID.
     * 
     * @param string $seat_no Seat No.
     * 
     * @param int $ticket_id Ticket ID.
     */
    public function print_ticket( $booking_id, $seat_no, $ticket_id ) {
        $basic_functions = new Eventprime_Basic_Functions;
        $global_settings = new Eventprime_Global_Settings;
        $allow_event_tickets = $basic_functions->ep_get_global_settings('allow_event_tickets');
        if( empty( $allow_event_tickets ) ) {
            wp_send_json_error( array( 'error' => esc_html__( 'No data found.', 'eventprime-event-tickets' ) ) );
        }
        $booking_controller = new EventPrime_Bookings();
        if( empty( $booking ) ) {
            wp_send_json_error( array( 'error' => esc_html__( 'Booking not found.', 'eventprime-event-tickets' ) ) );
        }
        $html = $this->get_ticket_html( $booking, $seat_no, $ticket_id );
        $html_args = array( 'name' => $booking->event_data->name.'-ticket', 'title' =>  esc_html__('Ticket','eventprime-event-tickets') );
        
        $this->print_ticket_html( $html, $html_args );
        die;
    }

    /**
     * Ticket html
     * 
     * @param object $booking Booking Data.
     * 
     * @param string $seat_no Seat No.
     * 
     * @param int $event_ticket_id Event ticket ID.
     * 
     * @return Ticket html
     */
    public function get_ticket_html( $booking, $seat_no, $event_ticket_id ) {
        $basic_functions = new Eventprime_Basic_Functions;
        $global_settings = new Eventprime_Global_Settings;
        
        $allow_event_tickets = $basic_functions->ep_get_global_settings('allow_event_tickets');
        if( empty( $allow_event_tickets ) ) return;
        $tpl_location = plugin_dir_path(dirname(__FILE__)).'/public/partials/ticket.php';
		$data['date_time'] =  $booking->event_data->em_start_date;
        $data['hide_start_time'] = $booking->event_data->em_hide_event_start_time; 
        $data['hide_end_time'] = $booking->event_data->em_hide_event_end_time; 
        $data['start_time'] = $booking->event_data->em_start_time;
        $data['end_time'] = $booking->event_data->em_end_time;
		$data['duration'] = 'Duration: 1 day(s) ';
		$data['booking_id'] = $booking->em_id;
        $data['event_title'] = $booking->event_data->name;
		// $em_seating_organizer = $booking->event_data->venue_details->em_seating_organizer;
        // $data['organiser'] = ( ! empty( $em_seating_organizer ) ? $em_seating_organizer : '' );
        $data['organiser'] = (isset($booking->event_data->organizer_details) && !empty($booking->event_data->organizer_details)) ? $booking->event_data->organizer_details[0]->name : '';
        $data['age_group'] = $data['audience_note'] = '';
		if( ! empty( $booking->event_data->event_type_details ) && ! empty( $booking->event_data->event_type_details->em_age_group ) ) {
			$age_group = $booking->event_data->event_type_details->em_age_group;
			if( $age_group == "parental_guidance") {
                $data['age_group'] = esc_html__( 'All ages but parental guidance', 'eventprime-event-tickets' );
            } else if( $age_group == 'custom_group' ){
				$custom_group = $booking->event_data->event_type_details->em_custom_group;
                $data['age_group']= ( ! empty( $custom_group ) ? str_replace( '-',' to ', $custom_group ) : esc_html__( 'Not Specified. Contact organizer for details.', 'eventprime-event-tickets' ) );
            } else {
				$data['age_group'] =  ucwords( $age_group );
            }
			// $data['audience_note'] = $booking->event_data->event_type_details->description;
		}

        if( !empty($booking->event_data->em_audience_notice) ) $data['audience_note'] = $booking->event_data->em_audience_notice;

		$data['thumbnail'] = $booking->event_data->image_url;
		$data['venue_address'] = $data['venue_name'] = $data['venue_type'] = '';
		$venue_details = $booking->event_data->venue_details;
		if( ! empty( $venue_details ) ) {
			$data['venue_address'] = ( ! empty( $venue_details->em_address ) ? $venue_details->em_address : '' );
			$data['venue_name'] = ( ! empty( $venue_details->name ) ? $venue_details->name : '' );
			$data['venue_type'] = ( ! empty( $venue_details->em_type ) ? $venue_details->em_type : 'standings' );
		}

		$data['price_option_name'] = $data['currency_symbol'] = $data['ticket_price_dec'] = $data['seat_type'] = $data['seat_no'] = '';
		$data['pay_status'] = esc_html__( 'PAID', 'eventprime-event-tickets' );
		$data['ticket_price'] = esc_html__( 'Free', 'eventprime-event-tickets' );
		$data['currency_symbol'] = $basic_functions->ep_get_global_settings( 'currency' );
		$all_ticket_data = $booking->event_data->all_tickets_data;
		$ticket_data = '';
		foreach( $all_ticket_data as $tickets ) {
			if( $tickets->id == $event_ticket_id ) {
				$ticket_data = $tickets;
				break;
			}
		}
		if( ! empty( $ticket_data ) ) {
			$data['price_option_name'] = esc_html( $ticket_data->name );
			$data['ticket_price_dec'] = stripslashes( $ticket_data->description );
			$data['ticket_price'] = esc_html( $basic_functions->ep_price_with_position( $ticket_data->price ) ) ;
		}
		$data['seat_type'] = esc_html__( 'Seat No.', 'eventprime-event-tickets' );
		$data['seat_no'] = $seat_no;
        $data['qrcode_image'] = '';
        if( $basic_functions->ep_get_global_settings( 'show_qr_code_on_ticket' ) == 1 ) {
            $data['qrcode_image'] = $basic_functions->get_booking_qr_code( $booking );
        }

        $ticket_template_data = array();
        $ticket_template_id = ( ! empty( $ticket_data->ticket_template_id ) ? absint( $ticket_data->ticket_template_id ) : '' );
        if( ! empty( $ticket_template_id ) ) {
            $ticket_template_data = $this->get_single_ticket( $ticket_template_id );
        }

        $data['font_color']       = ( ! empty( $ticket_template_data->em_font_color ) ? $ticket_template_data->em_font_color : '#865C16' );
        $data['font1']            = ( ! empty( $ticket_template_data->em_font1 ) ? $ticket_template_data->em_font1 : 'Times' );
        $logo                     = ( ! empty( $ticket_template_data->em_logo ) ? wp_get_attachment_url( $ticket_template_data->em_logo ) : '' );
        $data['ticket_logo1']     = $logo;
        $data['background_color'] = ( ! empty( $ticket_template_data->em_background_color ) ? $ticket_template_data->em_background_color : '#E2C699' );
        $data['border_color']     = ( ! empty( $ticket_template_data->em_border_color ) ? $ticket_template_data->em_border_color : '#C8A366' );
        // font size (Element)
        // $data['em_font_size']     = ( ! empty( $ticket_template_data->em_font_size ) ? $ticket_template_data->em_font_size : '12px' );

		$data = apply_filters('ep_filter_data_before_ticket_print', $data, $booking, $event_ticket_id );
        if( file_exists( $tpl_location ) ) {
            ob_start();
            include( $tpl_location) ;
            $html = ob_get_clean();
        }
       
        return $html;
    }

    /**
     * Print ticket html
     * 
     * @param string $html Ticket html
     * 
     * @param array $html_args
     */
    public function print_ticket_html( $html, $html_args ) {
        $basic_functions = new Eventprime_Basic_Functions;
        $allow_event_tickets = $basic_functions->ep_get_global_settings('allow_event_tickets');
        if( empty( $allow_event_tickets ) ) return;
        if (!class_exists('TCPDF')){
            require_once plugin_dir_path(EP_PLUGIN_FILE) . '/includes/lib/tcpdf_min/tcpdf.php';
        }

        // create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        if(!empty($html_args['title'])){
            $pdf->SetTitle($html_args['title']);
        }
        
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set font
        $font=  isset($html_args['font']) ? $html_args['font'] : 'dejavusans';
        $pdf->SetFont($font, '', 10);

        // add a page
        $pdf->AddPage('L');
        $pdf->writeHTML($html, true, false, true, false, '');
        $name= isset($html_args['name']) ? $html_args['name'] : '';
        $pdf->Output("booking'-'$name.pdf", 'D');
    }

    /**
     * Save ticket
     * 
     * @param string $html Ticket html
     * 
     * @param array $html_args
     * 
     * @return PDF URL
     */
    public static function save_ticket_html( $html, $args = array() ) { 
        $basic_functions = new Eventprime_Basic_Functions;
        $allow_event_tickets = $basic_functions->ep_get_global_settings('allow_event_tickets');
        if( empty( $allow_event_tickets ) ) return;
        if ( ! class_exists( 'TCPDF' ) ){
            require_once plugin_dir_path(EP_PLUGIN_FILE) . '/includes/lib/tcpdf_min/tcpdf.php';
        }

        // create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        if(!empty($args['title'])){
            $pdf->SetTitle($args['title']);
        }
        
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set font
        $font=  isset($args['font']) ? $args['font'] : 'dejavusans';
        $pdf->SetFont($font, '', 10);

        // add a page
        $pdf->AddPage('L');
        $pdf->writeHTML($html, true, false, true, false, '');
        $name = isset($args['name']) ? $args['name'] : '';
        $name = str_replace(array('/', ' '), array('-', ''), $name);
        $path = plugin_dir_path(dirname(__FILE__)) . '/includes/tickets-pdf/';
        if ( ! file_exists( $path ) ) {
            mkdir( $path, 0777, true );
        }
        $pdf_name = plugin_dir_path(dirname(__FILE__)) . '/includes/tickets-pdf/' . $name . '.pdf';
        $pdf->Output( $pdf_name, 'F' );
        return $pdf_name;
    }

}