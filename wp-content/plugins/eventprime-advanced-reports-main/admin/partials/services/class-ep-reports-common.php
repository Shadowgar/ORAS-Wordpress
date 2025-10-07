<?php
defined( 'ABSPATH' ) || exit;


class EventPrime_Reports_Common{
    
    /*
     * @param $filter_args
     * return object
     */
    public function ep_payment_reports_list($filter_args = null){
        $paged = 1;
        if(!empty($filter_args)){
            if(!empty($filter_args->start_date)){
                $start_date = $filter_args->start_date;
            }else{
                $start_date  = date( 'Y-m-d', strtotime('-6 days'));
                $end_date  = date( 'Y-m-d' );
            }
            
            if(!empty($filter_args->end_date)){
                $end_date = $filter_args->end_date;
            }
            if(isset($filter_args->paged) && !empty($filter_args->paged)){
                $paged = $filter_args->paged;
            }
        }else{
            $start_date  = date( 'Y-m-d', strtotime('-6 days'));
            $end_date  = date( 'Y-m-d' );
        }
        
        $args = array(
                'numberposts' => -1,
                'orderby'     => 'date',
                'order'       => 'DESC',
                'post_status' => array('completed','cancelled','pending','refunded'),
                'posts_per_page'=> 10,
                'offset'      => (int) ( $paged-1 ) * 10,
                'paged'       => $paged,
                'date_query' => array(
                    array(
                        'after'  => array(
                            'year'   => date('Y', strtotime($start_date)),
                            'month'  => date('m', strtotime($start_date)),
                            'day'    => date('d', strtotime($start_date)),
                        ),
                        'before'     => array(
                            'year'   => date('Y', strtotime($end_date)),
                            'month'  => date('m', strtotime($end_date)),
                            'day'    => date('d', strtotime($end_date)),
                        ),
                        'inclusive'  => true,
                    ),
                 ),
                'meta_query'=> array('relation'=>'AND'),
                'post_type'   => 'em_booking'
            );
             
            if(!empty($filter_args)){
                if(isset($filter_args->event_id) && !empty($filter_args->event_id) && $filter_args->event_id != 'all'){
                    $args['meta_query'][] = array(
                        'key'     => 'em_event', 
                        'value'   => $filter_args->event_id, 
                        'compare' => '=', 
                        'type'    => 'NUMERIC,'
                    );
                }
                if(isset($filter_args->payment_method) && !empty($filter_args->payment_method) && $filter_args->payment_method !='all'){
                    $args['meta_query'][] = array(
                        'key'     => 'em_payment_method', 
                        'value'   => strtolower($filter_args->payment_method), 
                        'compare' => '=', 
                        'type'    => 'NUMERIC,'
                    );
                } 
                if(isset($filter_args->status) && !empty($filter_args->status) && $filter_args->status !='all'){
                    $args['post_status'] = strtolower($filter_args->status);
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
    public function ep_payment_reports($filter_args = null){
        
        if(!empty($filter_args)){
            if(!empty($filter_args->start_date)){
                $start_date = $filter_args->start_date;
            }else{
                $start_date  = date( 'Y-m-d', strtotime('-6 days'));
                $end_date  = date( 'Y-m-d' );
            }
            
            if(!empty($filter_args->end_date)){
                $end_date = $filter_args->end_date;
            }
        }else{
            $start_date  = date( 'Y-m-d', strtotime('-6 days'));
            $end_date  = date( 'Y-m-d' );
        }
        
        $bookings = new stdClass();
            $args = array(
                'numberposts' => -1,
                'orderby'     => 'date',
                'order'       => 'DESC',
                'post_status' => array('completed','pending','cancelled','refunded'),
                'date_query' => array(
                    array(
                        'after'  => array(
                            'year'   => date('Y', strtotime($start_date)),
                            'month'  => date('m', strtotime($start_date)),
                            'day'    => date('d', strtotime($start_date)),
                        ),
                        'before'     => array(
                            'year'   => date('Y', strtotime($end_date)),
                            'month'  => date('m', strtotime($end_date)),
                            'day'    => date('d', strtotime($end_date)),
                        ),
                        'inclusive'  => true,
                    ),
                 ),
                'meta_query'=> array('relation'=>'AND'),
                'post_type'   => 'em_booking'
            );
             
            if(!empty($filter_args)){
                if(isset($filter_args->event_id) && !empty($filter_args->event_id) && $filter_args->event_id != 'all'){
                    $args['meta_query'][] = array(
                        'key'     => 'em_event', 
                        'value'   => $filter_args->event_id, 
                        'compare' => '=', 
                        'type'    => 'NUMERIC,'
                    );
                }
                if(isset($filter_args->payment_method) && !empty($filter_args->payment_method) && $filter_args->payment_method !='all'){
                    $args['meta_query'][] = array(
                        'key'     => 'em_payment_method', 
                        'value'   => strtolower($filter_args->payment_method), 
                        'compare' => '=', 
                        'type'    => 'NUMERIC,'
                    );
                }
                if(isset($filter_args->status) && !empty($filter_args->status) && $filter_args->status !='all'){
                    $args['post_status'] = strtolower($filter_args->status);
                }
            }
            //print_r($args);
            $posts = get_posts( $args );
            $bookings->stat = $this->ep_payments_stat($posts);
            $bookings->posts = $posts;
            $bookings->posts_details = $this->ep_payment_reports_list($filter_args);

            //Calculate Days
            $from=date_create($start_date);
            $to=date_create($end_date);
            $diff=date_diff($to,$from);
            $days_count = $diff->format('%a');
            $bookings->stat->days_count = $days_count;
            
            $bookings->chart = $this->ep_payments_chart($days_count, $start_date, $end_date, $filter_args);
            
        return $bookings;
    }
    
    /*
     * Generate Payments Stats
     * @param object $bookings
     * return object
     */
    public function ep_payments_stat($bookings){
        //print_r($bookings);
        $data = new stdClass();
        $data->total_revenue = 0;
        $data->daily_revenue = 0;
        $data->total_booking = 0;
        $data->total_tickets = 0;
        $data->total_attendees = 0;
        $data->coupon_discount = 0;
        $ticket_sub_total = $coupon_discount = $total_attendees = 0;
        if(!empty($bookings)){
            $booking_controller = new Eventprime_Basic_Functions;
            $booking_controller_data = new EventPrime_Bookings;
            
            $data->total_booking = count($bookings);
            
            foreach($bookings as $booking){
                $booking = $booking_controller_data->load_booking_detail( $booking->em_id );
                $order_info = isset( $booking->em_order_info ) ? $booking->em_order_info : array();
                $tickets = isset( $order_info['tickets'] ) ? $order_info['tickets'] : array();
                
                if( isset( $booking->em_old_ep_booking ) && ! empty( $booking->em_old_ep_booking ) ) {
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
                    } else if( ! empty( $order_info['order_item_data'] ) ) {
                        foreach( $order_info['order_item_data'] as $order_item_data ){
                                $ticket_sub_total = $ticket_sub_total + $order_item_data->sub_total;
                        }
                    }
                    $att_count = 1;
                    foreach( $booking->em_attendee_names as $key => $attendee_data ) {
                        $att_count++;
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
                    
                }
                foreach( $booking->em_attendee_names as $ticket_id => $attendee_data ) {
                    if (isset($attendee_data[1])) {
                        $booking_attendees_field_labels = $booking_controller->ep_get_booking_attendee_field_labels($attendee_data[1]);
                        foreach($booking_attendees_field_labels as $labels) {
                            $att_count = 1;
                            if(count($attendee_data) > 1) {
                                $att_count = count($attendee_data);
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
     * Generate Payments Charts data
     */
    public function ep_payments_chart($days_count, $start_date, $end_date, $filter_args){
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
                'post_status' => array('completed','cancelled','pending','refunded'),
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
             
            if(!empty($filter_args)){
                if(isset($filter_args->event_id) && !empty($filter_args->event_id) && $filter_args->event_id != 'all'){
                    $args['meta_query'][] = array(
                        'key'     => 'em_event', 
                        'value'   => $filter_args->event_id, 
                        'compare' => '=', 
                        'type'    => 'NUMERIC,'
                    );
                }
                if(isset($filter_args->payment_method) && !empty($filter_args->payment_method) && $filter_args->payment_method !='all'){
                    $args['meta_query'][] = array(
                        'key'     => 'em_payment_method', 
                        'value'   => strtolower($filter_args->payment_method), 
                        'compare' => '=', 
                        'type'    => 'NUMERIC,'
                    );
                } 
                if(isset($filter_args->status) && !empty($filter_args->status) && $filter_args->status !='all'){
                    $args['post_status'] = strtolower($filter_args->status);
                }
            }
            $bookings = get_posts( $args );
            if(!empty($bookings)){
                $chart = new stdClass();
                $chart->date = $date->format('Y-m-d');
                
                $ticket_sub_total = 0;
                if(!empty($bookings)){
                    $booking_controller = new Eventprime_Basic_Functions;
                    $booking_controller_data = new EventPrime_Bookings;

                    foreach($bookings as $booking){
                        $booking = $booking_controller_data->load_booking_detail( $booking->em_id );
                        $order_info = isset( $booking->em_order_info ) ? $booking->em_order_info : array();
                        $tickets = isset( $order_info['tickets'] ) ? $order_info['tickets'] : array();

                        if( isset( $booking->em_old_ep_booking ) && ! empty( $booking->em_old_ep_booking ) ) {
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
                            } else if( ! empty( $order_info['order_item_data'] ) ) {
                                foreach( $order_info['order_item_data'] as $order_item_data ){
                                        $ticket_sub_total = $ticket_sub_total + $order_item_data->sub_total;
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

                        }

                        if( !empty( $order_info['event_fixed_price'] ) ) {
                            $ticket_sub_total = $ticket_sub_total + $order_info['event_fixed_price'];
                        }
                    }
                }
                $chart->revenue = $ticket_sub_total;
                $chart_data[] = $chart;
            }else{
                $chart = new stdClass();
                $chart->date = $date->format('Y-m-d');
                $chart->revenue = 0;
                $chart_data[] = $chart;
            }
        }
        return $chart_data;
    }
    
    /*
     * Filter bookings based on filter
     * @param ajax
     * return object
     */
    public function eventprime_payments_filters(){
        $filter_data = '';
        $data = $_POST;
        $start_date  = date( 'Y-m-d', strtotime('-6 days'));
        $end_date  = date( 'Y-m-d' );
        $event_id = 'all';
        $payment_method = 'all';
        $status = 'all';
        if(!empty($data)){
            if( isset( $data['ep_filter_date'] ) && ! empty( $data['ep_filter_date'] ) ) {
                $date_range = sanitize_text_field( $data['ep_filter_date'] );
                $dates = explode( ' - ', $date_range );
                $start = isset( $dates[0] ) && ! empty( $dates[0] ) ? $dates[0] : '';
                $end = isset( $dates[1] ) && ! empty( $dates[1] ) ? $dates[1] : '';
                
                $start_date = date(  'Y-m-d', strtotime( $start ) );
                $end_date = date(  'Y-m-d', strtotime( $end ) );
            }
            if(isset($data['event_id']) && !empty($data['event_id'])){
                $event_id = sanitize_text_field( $data['event_id'] );
            }
            if(isset($data['payment_method']) && !empty($data['payment_method'])){
                $payment_method = sanitize_text_field( $data['payment_method'] );
            }
            if(isset($data['status']) && !empty($data['status'])){
                $status = sanitize_text_field( $data['status'] );
            }
            
            $filter_args = new stdClass();
            $filter_args->start_date = $start_date;
            $filter_args->end_date = $end_date;
            $filter_args->event_id = $event_id;  
            $filter_args->payment_method = $payment_method;
            $filter_args->status = $status;
            if(isset($data['ep_report_action_type']) && sanitize_text_field($data['ep_report_action_type']) == 'load_more'){
                $filter_args->paged = intval(sanitize_text_field($data['paged'])) + 1;
                return $this->ep_load_more_report_payments($filter_args);
            }
            $filter_data = $this->ep_payment_reports($filter_args);
        }
        $booking_data = $filter_data;
        ob_start();
        do_action('ep_payments_report_stat',$booking_data);
        $stat_html = ob_get_clean();
        
        ob_start();
        do_action('ep_payments_report_bookings_list',$booking_data);
        $booking_html = ob_get_clean();
        
        $booking_data->stat_html  = $stat_html;
        $booking_data->booking_html  = $booking_html;
        return $booking_data;
    }
    
    /*
     * Load more records on payments
     * @param object $filter_args
     */
    public function ep_load_more_report_payments($filter_args){
        
        $bookings_data = new stdClass();
        $bookings_data->posts_details = $this->ep_payment_reports_list($filter_args);
        ob_start();
        do_action('ep_payments_reports_bookings_list_load_more',$bookings_data);
        $booking_html = ob_get_clean();
        
        $bookings_data->booking_html  = $booking_html;
        
        return $bookings_data;
        
    }
}