<?php
defined( 'ABSPATH' ) || exit;


class EventPrime_Reports_Attendees{
    
    /*
     * @param $filter_args
     * return object
     */
    public function ep_attendee_reports_list( $filter_args = null ) {
        $paged = 1;
        if( ! empty( $filter_args ) && isset( $filter_args->paged ) ) {
            $paged = $filter_args->paged;
        }
        $args = array(
            'numberposts'    => -1,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'post_status'    => 'completed',
            'posts_per_page' => 10,
            'offset'         => (int)( $paged-1 ) * 10,
            'paged'          => $paged,
            'meta_query'     => array( 'relation' => 'AND' ),
            'post_type'      => 'em_booking'
        );
             
        if( ! empty( $filter_args ) ) {
            if( isset( $filter_args->event_id ) && ! empty( $filter_args->event_id ) && $filter_args->event_id != 'all' ) {
                $args['meta_query'][] = array(
                    'key'     => 'em_event', 
                    'value'   => $filter_args->event_id, 
                    'compare' => '=', 
                    'type'    => 'NUMERIC,'
                );
            }
        }
        $bookings = get_posts( $args );
        $wp_query = new WP_Query( $args );
        $wp_query->posts = $bookings;

        return $wp_query;
    }
    
    /*
     * @param $filter_args
     * return object
     */
    public function ep_attendee_reports( $filter_args = null ) {
        $bookings = new stdClass();
        $args = array(
            'numberposts' => -1,
            'orderby'     => 'date',
            'order'       => 'DESC',
            'post_status' => 'completed',
            'meta_query'  => array( 'relation' => 'AND' ),
            'post_type'   => 'em_booking'
        );
            
        if( ! empty( $filter_args ) ) {
            if( isset( $filter_args->event_id ) && ! empty( $filter_args->event_id ) && $filter_args->event_id != 'all' ) {
                $args['meta_query'][] = array(
                    'key'     => 'em_event', 
                    'value'   => $filter_args->event_id, 
                    'compare' => '=', 
                    'type'    => 'NUMERIC,'
                );
            }
        }
        $posts = get_posts( $args );
        $bookings->stat = $this->ep_attendee_stat($posts);
        $bookings->posts = $posts;
        $bookings->posts_details = $this->ep_attendee_reports_list($filter_args);
            
        // For Chart
        $args['order'] = 'ASC';
        $chart_bookings = $posts = get_posts( $args );
        if(!empty($chart_bookings)){
            $start_date = $end_date  = get_the_date( 'Y-m-d', $chart_bookings[0]->ID );
            $end_date  = date('Y-m-d');
            $from = date_create( $start_date );
            $to = date_create( $end_date );
            $diff = date_diff( $to, $from );
            $days_count = $diff->format('%a');
            $bookings->stat->days_count = $days_count;
            $bookings->chart = $this->ep_attendee_chart($days_count, $start_date, $end_date, $filter_args);
            }else{
            $bookings->chart = $this->ep_attendee_chart("", "", "", $filter_args);
            }
        return $bookings;
    }
    
    /*
     * Generate Attendees Stats
     * @param object $bookings
     * return object
     */
    public function ep_attendee_stat($bookings){
        $data = new stdClass();
        $data->total_revenue = $data->daily_revenue = $data->total_booking = $data->total_tickets = $data->total_attendees = $data->coupon_discount = 0;
        $ticket_sub_total = $coupon_discount = $total_attendees = 0;
        if( ! empty( $bookings ) ) {
            $booking_controller = new Eventprime_Basic_Functions;
            $data->total_booking = count( $bookings );
            foreach( $bookings as $booking ) {
                $booking = $booking_controller->load_booking_detail( $booking->ID );
                $order_info = isset( $booking->em_order_info ) ? $booking->em_order_info : array();
                $tickets = isset( $order_info['tickets'] ) ? $order_info['tickets'] : array();
                $attendees_names = ( ! empty( $booking->em_attendee_names ) ? $booking->em_attendee_names : array() );
                if( isset( $booking->em_old_ep_booking ) && ! empty( $booking->em_old_ep_booking ) ) {
                    if( ! empty( $tickets ) ){
                        foreach( $tickets as $ticket ){
                            $additional_fees = array();
                            if( isset( $ticket->additional_fee ) ) {
                                foreach( $ticket->additional_fee as $fees ) {
                                    if(isset($booking->eventprime_updated_pattern))
                                    {
                                        $additional_fees[] = $fees->label.' ('.$booking_controller->ep_price_with_position( $fees->price ).')';
                                    }
                                    else
                                    {
                                        $additional_fees[] = $fees->label.' ('.$booking_controller->ep_price_with_position( $fees->price * $ticket->qty ).')';
                                    }
                                    
                                }
                            }
                            $ticket_sub_total = $ticket_sub_total + $ticket->subtotal;
                        }
                    } else if( ! empty( $order_info['order_item_data'] ) ) {
                        foreach( $order_info['order_item_data'] as $order_item_data ){
                            $ticket_sub_total = $ticket_sub_total + $order_item_data->sub_total;
                        }
                    }
                    $att_count = 0;
                    if( ! empty( $attendees_names ) ) {
                        foreach( $attendees_names as $key => $attendee_data ) {
                            $att_count++;
                        }
                    }
                } else{
                    if( ! empty( $tickets ) ){
                        foreach( $tickets as $ticket ){
                            $additional_fees = array();
                            if(isset($ticket->additional_fee)){
                                foreach($ticket->additional_fee as $fees){
                                    if(isset($booking->eventprime_updated_pattern))
                                    {
                                        $additional_fees[] = $fees->label.' ('.$booking_controller->ep_price_with_position($fees->price).')';
                                    }
                                    else
                                    {
                                        $additional_fees[] = $fees->label.' ('.$booking_controller->ep_price_with_position($fees->price * $ticket->qty).')';
                                    }
                                    
                                }
                            }
                            $ticket_sub_total = $ticket_sub_total + $ticket->subtotal;
                        }
                    }
                    if( ! empty( $attendees_names ) ) {
                        $att_count = 0;
                        foreach( $booking->em_attendee_names as $ticket_id => $attendee_data ) {
                            if( count( $attendee_data ) >= 1 ) {
                                    $att_count += count( $attendee_data );
                                }
                        }
                    }
                }
                
                if( !empty( $order_info['event_fixed_price'] ) ) {
                    $ticket_sub_total = $ticket_sub_total + $order_info['event_fixed_price'];
                }
                if(isset($order_info['coupon_code'])){
                    $coupon_discount = $coupon_discount + $order_info['discount'];
                }
                $total_attendees = $total_attendees + $att_count; 
            }
        }
        $data->total_revenue = $ticket_sub_total;
        $data->coupon_discount = $coupon_discount;
        $data->total_attendees = $total_attendees;
        return $data;
    }
    
    /*
     * Generate Attendees Chart
     */
    public function ep_attendee_chart($days_count="", $start_date="", $end_date="", $filter_args=array()){
        $chart_data = array();
        $start_date = new DateTime( $start_date );
        $end_date = new DateTime( $end_date );
        $end_date = $end_date->modify( '+1 day' ); 

        $interval = new DateInterval('P1D');
        $daterange = new DatePeriod($start_date, $interval ,$end_date);

        // Step 4: Looping Through the Date Range
        foreach ($daterange as $date) {
            $args = array(
                'numberposts' => -1,
                'orderby'     => 'date',
                'order'       => 'DESC',
                'post_status' => 'completed',
                'date_query' => array(
                    array(
                        'year'   => $date->format('Y'),
                        'month'  => $date->format('m'),
                        'day'    => $date->format('d'),
                    )
                 ),
                'meta_query'=> array('relation'=>'AND'),
                'post_type'   => 'em_booking'
            );
             
            if( ! empty( $filter_args ) ) {
                if( isset( $filter_args->event_id ) && ! empty( $filter_args->event_id ) && $filter_args->event_id != 'all' ) {
                    $args['meta_query'][] = array(
                        'key'     => 'em_event', 
                        'value'   => $filter_args->event_id, 
                        'compare' => '=', 
                        'type'    => 'NUMERIC,'
                    );
                }
            }
            $bookings = get_posts( $args );
            if( ! empty( $bookings ) ) {
                $chart = new stdClass();
                $chart->date = $date->format('Y-m-d');
                $ticket_sub_total = $booking_count = $attendees_count = 0;
                if( ! empty( $bookings ) ) {
                    $booking_controller = new Eventprime_Basic_Functions;
                    $booking_count = count( $bookings );
                    foreach( $bookings as $booking ) {
                        $booking = $booking_controller->load_booking_detail( $booking->ID );
                        $order_info = isset( $booking->em_order_info ) ? $booking->em_order_info : array();
                        $tickets = isset( $order_info['tickets'] ) ? $order_info['tickets'] : array();
                        if( isset( $booking->em_old_ep_booking ) && ! empty( $booking->em_old_ep_booking ) ) {
                            foreach( $booking->em_attendee_names as $key => $attendee_data ) {
                                $attendees_count++;
                            }
                        } else{
                            if( ! empty( $booking->em_attendee_names ) && count( $booking->em_attendee_names ) > 0 ) {
                                $attendee_names = isset($booking->em_attendee_names) &&!empty($booking->em_attendee_names) ? maybe_unserialize($booking->em_attendee_names): array();
                                foreach( $attendee_names as $ticket_id => $attendee_data ) {
                                    foreach( $attendee_data as $booking_attendees ) {
                                        $booking_attendees_val = array_values( $booking_attendees );
                                        /* foreach( $booking_attendees_val as $baval ) {

                                        } */
                                        $attendees_count++;
                                    }
                                }
                            }
                        }
                    }
                }
                $chart->booking = $booking_count;
                $chart->attendees = ($attendees_count < 1 ) ? 1 :$attendees_count;
                $chart_data[] = $chart;
            }else{
                $chart = new stdClass();
                $chart->date = $date->format('Y-m-d');
                $chart->booking = 0;
                $chart->attendees = 0;
                $chart_data[] = $chart;
            }
        }
        return $chart_data;
    }
    
    /*
     * Filter records
     * @param ajax
     */
    public function eventprime_attendees_filters(){
        $filter_data = '';
        $data = $_POST;
        $event_id = 'all';
        if( ! empty( $data ) ) {
            if( isset( $data['event_id'] ) && ! empty( $data['event_id'] ) ) {
                $event_id = sanitize_text_field( $data['event_id'] );
            }
            $filter_args = new stdClass();
            $filter_args->event_id = $event_id;  
            if(isset($data['ep_report_action_type']) && sanitize_text_field($data['ep_report_action_type']) == 'load_more'){
                $filter_args->paged = intval(sanitize_text_field($data['paged'])) + 1;
                return $this->ep_load_more_report_attendees($filter_args);
            }
            $filter_data = $this->ep_attendee_reports($filter_args);
        }
        $booking_data = $filter_data;
        ob_start();
            do_action('ep_attendees_report_stat',$booking_data);
        $stat_html = ob_get_clean();
        
        ob_start();
            do_action('ep_attendees_report_bookings_list',$booking_data);
        $booking_html = ob_get_clean();
        
        $booking_data->stat_html  = $stat_html;
        $booking_data->booking_html  = $booking_html;
        return $booking_data;
    }
    
    /*
     * load more records
     */
    public function ep_load_more_report_attendees($filter_args){
        $bookings_data = new stdClass();
        $bookings_data->posts_details = $this->ep_attendee_reports_list($filter_args);
        ob_start();
        do_action('ep_attendees_reports_bookings_list_load_more',$bookings_data);
        $booking_html = ob_get_clean();
        $bookings_data->booking_html  = $booking_html;
        return $bookings_data;
    }
}