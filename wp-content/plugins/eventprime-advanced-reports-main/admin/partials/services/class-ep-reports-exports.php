<?php
defined( 'ABSPATH' ) || exit;

class EventPrime_Reports_Export{
    
    /*
     * Download Bookings CSV
     */
    public function process_booking_downloadable_csv($bookings){
        
            
        $bookings_data = array(); 
        $bookings_data[0]['id']=__('Booking ID', 'eventprime-event-advanced-reports');
        $bookings_data[0]['user_name']=__('User Name', 'eventprime-event-advanced-reports');
        $bookings_data[0]['email']=__('Email', 'eventprime-event-advanced-reports');
        $bookings_data[0]['event']=__('Event Name', 'eventprime-event-advanced-reports');
        $bookings_data[0]['sdate']=__('Start Date', 'eventprime-event-advanced-reports');
        $bookings_data[0]['stime']=__('Start Time', 'eventprime-event-advanced-reports');
        $bookings_data[0]['edate']=__('End Date', 'eventprime-event-advanced-reports');
        $bookings_data[0]['etime']=__('End Time', 'eventprime-event-advanced-reports');
        $bookings_data[0]['event_type']=__('Event Type', 'eventprime-event-advanced-reports');
        $bookings_data[0]['venue']=__('Venue', 'eventprime-event-advanced-reports');
        $bookings_data[0]['address']=__('Address', 'eventprime-event-advanced-reports');
        $bookings_data[0]['seat_type']=__('Seating Type', 'eventprime-event-advanced-reports');
        $bookings_data[0]['attendees']=__('Attendees', 'eventprime-event-advanced-reports');
        $bookings_data[0]['seat']=__('Seat No.', 'eventprime-event-advanced-reports');
        $bookings_data[0]['currency']=__('Currency', 'eventprime-event-advanced-reports');
        $bookings_data[0]['price']=__('Price', 'eventprime-event-advanced-reports');
        $bookings_data[0]['attendees_count']=__('Ticket Count', 'eventprime-event-advanced-reports');
        $bookings_data[0]['subtotal']=__('Subtotal', 'eventprime-event-advanced-reports');
        $bookings_data[0]['event_price']=__('Fixed Event Price', 'eventprime-event-advanced-reports');
        $bookings_data[0]['discount']=__('Discount', 'eventprime-event-advanced-reports');
        $bookings_data[0]['amount_received']=__('Amount Received', 'eventprime-event-advanced-reports');
        $bookings_data[0]['gateway']=__('Payment Gateway', 'eventprime-event-advanced-reports');
        $bookings_data[0]['booking_status']=__('Booking Status', 'eventprime-event-advanced-reports');
        $bookings_data[0]['payment_status']=__('Payment Status', 'eventprime-event-advanced-reports');
        $bookings_data[0]['log']=__('Transacton Log', 'eventprime-event-advanced-reports');
        $bookings_data[0]['guest']=__('Guest Booking Data', 'eventprime-event-advanced-reports');
        if(!empty($bookings)){
            $booking_controller = new Eventprime_Basic_Functions;
            $booking_controller_data = new EventPrime_Bookings;
            $row =1;
            foreach($bookings as $booking){
                $booking = $booking_controller_data->load_booking_detail( $booking->em_id );
                
                $bookings_data[$row]['id']= $booking->em_id;
                $bookings_data[$row]['user_name'] = '';
                $bookings_data[$row]['email'] = '';
                $user_id = isset($booking->em_user) ? (int) $booking->em_user : 0;
                if($user_id){
                    $user = get_userdata($user_id);
                        $bookings_data[$row]['user_name'] = $user->user_login ;
                        $bookings_data[$row]['email'] = $user->user_email;
                    }else{
                    $bookings_data[$row]['user_name'] =  __('Guest','eventprime-event-calendar-management');
                    $bookings_data[$row]['email'] =  __('Guest','eventprime-event-calendar-management');
                }
                
                $bookings_data[$row]['event'] = $booking->em_name;
                $bookings_data[$row]['sdate'] = '';
                $bookings_data[$row]['stime'] = '';
                $bookings_data[$row]['edate'] = '';
                $bookings_data[$row]['etime'] = '';
                $bookings_data[$row]['event_type'] = '';
                $bookings_data[$row]['venue']='';
                $bookings_data[$row]['address']='';
                $bookings_data[$row]['seat_type']='';
                if(isset($booking->event_data) && !empty($booking->event_data)){
                    $event = $booking->event_data;
                    $bookings_data[$row]['sdate'] = isset($event->em_start_date) && !empty($event->em_start_date) ? $booking_controller->ep_timestamp_to_date($event->em_start_date): '';
                    $bookings_data[$row]['edate'] = isset($event->em_end_date) && !empty($event->em_end_date) ? $booking_controller->ep_timestamp_to_date($event->em_end_date): '';
                    $bookings_data[$row]['stime'] = isset($event->em_start_time) && !empty($event->em_start_time) ? $event->em_start_time: '';
                    $bookings_data[$row]['etime'] = isset($event->em_end_time) && !empty($event->em_end_time) ? $event->em_end_time: '';
                    
                    if(isset($event->event_type_details) && !empty($event->event_type_details)){
                        $bookings_data[$row]['event_type'] = $booking->event_data->event_type_details->name;
                    }
                    if(isset($event->venue_details) && !empty($event->venue_details)){
                       $venue = $booking->event_data->venue_details;
                       $bookings_data[$row]['venue']= $venue->name; 
                       $bookings_data[$row]['address']=isset($venue->em_address) ? $venue->em_address : '';
                       $bookings_data[$row]['seat_type']=isset($venue->em_type) ? $venue->em_type : '';
                    }
                }
                $bookings_data[$row]['attendees'] = '';
                $bookings_data[$row]['seat'] = '';
                $bookings_data[$row]['currency']=isset($booking->em_payment_log['currency']) ? $booking->em_payment_log['currency'] : $booking_controller->ep_get_global_settings('currency');
                
                $order_info = isset($booking->em_order_info) ? $booking->em_order_info : array();
                $tickets = isset($order_info['tickets']) ? $order_info['tickets'] : array();
                $ticket_sub_total = 0;
                if(!empty($tickets)):
                    foreach($tickets as $ticket):
                        $ticket_sub_total = $ticket_sub_total + $ticket->subtotal;           
                    endforeach;
                endif;
                $bookings_data[$row]['price']=$booking_controller->ep_price_with_position($ticket_sub_total);
                $bookings_data[$row]['attendees_count']= '';
                $bookings_data[$row]['subtotal']=$booking_controller->ep_price_with_position($ticket_sub_total);
                $bookings_data[$row]['event_price']='';
                
                if( !empty( $order_info['event_fixed_price'] ) ) {
                    $bookings_data[$row]['event_price']=$booking_controller->ep_price_with_position($order_info['event_fixed_price']);
                }
                $bookings_data[$row]['discount']='';
                
                if(isset($order_info['coupon_code'])){
                    $bookings_data[$row]['discount']= $booking_controller->ep_price_with_position($order_info['discount']);
                }
                $bookings_data[$row]['amount_received']= $booking_controller->ep_price_with_position($order_info['booking_total']) ;
                
                $bookings_data[$row]['gateway']= isset($booking->em_payment_method) ? ucfirst($booking->em_payment_method) : 'N/A';
                $bookings_data[$row]['booking_status'] = isset($booking->em_status) ? ucfirst($booking->em_status) : 'N/A';
                $payment_log = isset($booking->em_payment_log) ? $booking->em_payment_log : array();
                $payment_status='';
                if(strtolower($booking->em_payment_method) == 'offline'){
                    $payment_status = isset($payment_log['offline_status']) ? $payment_log['offline_status'] : '';
                }else{
                    $payment_status = isset($payment_log['payment_status']) ? $payment_log['payment_status'] : '';
                }
                $bookings_data[$row]['payment_status']= $payment_status;
                $bookings_data[$row]['log']=serialize($payment_log);
                $except = array('multi_price_option_data', 'coupon_code', 'coupon_discount', 'coupon_amount', 'coupon_type', 'applied_ebd', 'ebd_id', 'ebd_name', 'ebd_rule_type', 'ebd_discount_type', 'ebd_discount', 'ebd_discount_amount');
                if(!empty($payment_log)){
                    foreach($payment_log as $logs_key => $logs){
                        if(in_array($logs_key, $except)){
                            unset($payment_log[$logs_key]);
                        }
                    }
                }
                $bookings_data[$row]['log']=serialize($payment_log);
                $bookings_data[$row]['guest']='';
                if(isset($booking->em_guest_booking) && !empty($booking->em_guest_booking)){
                    $bookings_data[$row]['guest'] = serialize($order_info['guest_booking_custom_data']);
                }
                
                $attendees_count = 0;
                if( ! empty( $booking->em_attendee_names ) && count( $booking->em_attendee_names ) > 0 ) {
                    $attendee_names = isset($booking->em_attendee_names) &&!empty($booking->em_attendee_names) ? maybe_unserialize($booking->em_attendee_names): array();
                    foreach( $attendee_names as $ticket_id => $attendee_data ) {
                        foreach( $attendee_data as $booking_attendees ) {
                            $booking_attendees_val = array_values( $booking_attendees );
                                foreach( $booking_attendees_val as $baval ) {
                                    
                                }
                                $attendees_count++;
                        }
                    }
                    $bookings_data[$row]['attendees_count']=$attendees_count;
                    $booking_attendees_field_labels = array();
                    $count=0;
                    foreach( $booking->em_attendee_names as $ticket_id => $attendee_data ) {
                        $booking_attendees_field_labels = $booking_controller->ep_get_booking_attendee_field_labels( $attendee_data[1] );
                        foreach( $attendee_data as $booking_attendees ) {
                            $booking_attendees_val = array_values( $booking_attendees );
                            $attendees = '';
                            foreach( $booking_attendees_field_labels as $labels ){
                                $formated_val = str_replace( ' ', '_', strtolower( $labels ) );
                                $at_val = '---';
                                foreach( $booking_attendees_val as $baval ) {
                                    if( isset( $baval[$formated_val] ) && ! empty( $baval[$formated_val] ) ) {
                                        $at_val = $baval[$formated_val];
                                        break;
                                    }  
                                }
                                if (is_array($at_val)) {
                                    $at_val = implode(', ', $at_val); 
                                }
                                $attendees .= $labels.' : '.$at_val.' | ';
                            }
                            $bookings_data[$row]['attendees'] = $attendees;
                            if($count < $attendees_count-1) {
                                $row++;
                                $bookings_data[$row]['id']='';
                                $bookings_data[$row]['user_name']='';
                                $bookings_data[$row]['email']='';
                                $bookings_data[$row]['event']='';
                                $bookings_data[$row]['sdate']='';
                                $bookings_data[$row]['stime']='';
                                $bookings_data[$row]['edate']='';
                                $bookings_data[$row]['etime']='';
                                $bookings_data[$row]['event_type']='';
                                $bookings_data[$row]['venue']='';
                                $bookings_data[$row]['address']='';
                                $bookings_data[$row]['seat_type']='';
                                $bookings_data[$row]['attendees']=$attendees;
                                $bookings_data[$row]['seat']='';
                                $bookings_data[$row]['currency']='';
                                $bookings_data[$row]['price']='';
                                $bookings_data[$row]['attendees_count']='';
                                $bookings_data[$row]['subtotal']='';
                                $bookings_data[$row]['event_price']='';
                                $bookings_data[$row]['discount']='';
                                $bookings_data[$row]['amount_received']='';
                                $bookings_data[$row]['gateway']='';
                                $bookings_data[$row]['booking_status']='';
                                $bookings_data[$row]['payment_status']='';
                                $bookings_data[$row]['log']='';
                                $bookings_data[$row]['guest']='';
                            }
                            $count++;
                        }
                    }
                }
                $row++;
            }
        }
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="ep-bookings-'.md5(time().mt_rand(100, 999)).'.csv"');
        $f = fopen('php://output', 'w');
        foreach ($bookings_data as $line) {
            fputcsv($f, $line);
        }
    }
    
    /*
     * Download Attendees CSV
     */
    public function process_attendees_downloadable_csv($bookings){
        $booking_controller = new Eventprime_Basic_Functions;
        $booking_controller_data = new EventPrime_Bookings;
        $bookings_data = array(); 
        $bookings_data[0]['id']=__('Booking ID', 'eventprime-event-advanced-reports');
        $bookings_data[0]['user_name']=__('User Name', 'eventprime-event-advanced-reports');
        $bookings_data[0]['email']=__('Email', 'eventprime-event-advanced-reports');
        $bookings_data[0]['event']=__('Event Name', 'eventprime-event-advanced-reports');
        $bookings_data[0]['sdate']=__('Start Date', 'eventprime-event-advanced-reports');
        $bookings_data[0]['stime']=__('Start Time', 'eventprime-event-advanced-reports');
        $bookings_data[0]['edate']=__('End Date', 'eventprime-event-advanced-reports');
        $bookings_data[0]['etime']=__('End Time', 'eventprime-event-advanced-reports');
        $bookings_data[0]['seat_type']=__('Seating Type', 'eventprime-event-advanced-reports');
        $bookings_data[0]['attendees']=__('Attendees', 'eventprime-event-advanced-reports');
        $bookings_data[0]['gateway']=__('Payment Gateway', 'eventprime-event-advanced-reports');
        $bookings_data[0]['booking_status']=__('Booking Status', 'eventprime-event-advanced-reports');
        $bookings_data[0]['payment_status']=__('Payment Status', 'eventprime-event-advanced-reports');
        if(!empty($bookings)){
            $row =1;
            foreach($bookings as $booking){
                $booking = $booking_controller_data->load_booking_detail($booking->em_id);
                $bookings_data[$row]['id']= $booking->em_id;
                $bookings_data[$row]['user_name'] = '';
                $bookings_data[$row]['email'] = '';
                $user_id = isset($booking->em_user) ? (int) $booking->em_user : 0;
                if($user_id){
                    $user = get_userdata($user_id);
                        $bookings_data[$row]['user_name'] = $user->user_login ;
                        $bookings_data[$row]['email'] = $user->user_email;
                    }else{
                    $bookings_data[$row]['user_name'] =  __('Guest','eventprime-event-calendar-management');
                    $bookings_data[$row]['email'] =  __('Guest','eventprime-event-calendar-management');
                }
                
                $bookings_data[$row]['event'] = $booking->em_name;
                $bookings_data[$row]['sdate'] = '';
                $bookings_data[$row]['stime'] = '';
                $bookings_data[$row]['edate'] = '';
                $bookings_data[$row]['etime'] = '';
                $bookings_data[$row]['seat_type']='';
                if(isset($booking->event_data) && !empty($booking->event_data)){
                    $event = $booking->event_data;
                    $bookings_data[$row]['sdate'] = isset($event->em_start_date) && !empty($event->em_start_date) ? $booking_controller->ep_timestamp_to_date($event->em_start_date): '';
                    $bookings_data[$row]['edate'] = isset($event->em_end_date) && !empty($event->em_end_date) ? $booking_controller->ep_timestamp_to_date($event->em_end_date): '';
                    $bookings_data[$row]['stime'] = isset($event->em_start_time) && !empty($event->em_start_time) ? $event->em_start_time: '';
                    $bookings_data[$row]['etime'] = isset($event->em_end_time) && !empty($event->em_end_time) ? $event->em_end_time: '';
                    
                    if(isset($event->venue_details) && !empty($event->venue_details)){
                       $venue = $booking->event_data->venue_details;
                       $bookings_data[$row]['seat_type']=isset($venue->em_type) ? $venue->em_type : '';
                    }
                }
                $bookings_data[$row]['attendees'] = '';
                $order_info = isset($booking->em_order_info) ? $booking->em_order_info : array();
                $tickets = isset($order_info['tickets']) ? $order_info['tickets'] : array();
                $ticket_sub_total = 0;
                if(!empty($tickets)):
                    
                    foreach($tickets as $ticket):
                        $ticket_sub_total = $ticket_sub_total + $ticket->subtotal;           
                    endforeach;
                endif;
                
                $bookings_data[$row]['gateway']= isset($booking->em_payment_method) ? ucfirst($booking->em_payment_method) : 'N/A';
                $bookings_data[$row]['booking_status'] = isset($booking->em_status) ? ucfirst($booking->em_status) : 'N/A';
                $payment_log = isset($booking->em_payment_log) ? $booking->em_payment_log : array();
                $payment_status='';
                if(strtolower($booking->em_payment_method) == 'offline'){
                    $payment_status = isset($payment_log['offline_status']) ? $payment_log['offline_status'] : '';
                }else{
                    $payment_status = isset($payment_log['payment_status']) ? $payment_log['payment_status'] : '';
                }
                $bookings_data[$row]['payment_status']= $payment_status;
                
                $attendees_count = 0;
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
                    $booking_attendees_field_labels = array();
                    $count = 0;
                    foreach( $booking->em_attendee_names as $ticket_id => $attendee_data ) {
                        $booking_attendees_field_labels = $booking_controller->ep_get_booking_attendee_field_labels( $attendee_data[1] );
                        foreach( $attendee_data as $booking_attendees ) {
                            $booking_attendees_val = array_values( $booking_attendees );
                            $attendees = '';
                            foreach( $booking_attendees_field_labels as $labels ){
                                $formated_val = str_replace( ' ', '_', strtolower( $labels ) );
                                $at_val = '---';
                                foreach( $booking_attendees_val as $baval ) {
                                    if( isset( $baval[$formated_val] ) && ! empty( $baval[$formated_val] ) ) {
                                        $at_val = $baval[$formated_val];
                                        break;
                                    }  
                                }
                                if (is_array($at_val)) {
                                    $at_val = implode(', ', $at_val);  // Convert array to a comma-separated string
                                }
                                $attendees .= $labels.' : '.$at_val.' | ';
                            }
                            $bookings_data[$row]['attendees'] = $attendees;
                            if($count < $attendees_count-1){
                                $row++;
                                $bookings_data[$row]['id']='';
                                $bookings_data[$row]['user_name']='';
                                $bookings_data[$row]['email']='';
                                $bookings_data[$row]['event']='';
                                $bookings_data[$row]['sdate']='';
                                $bookings_data[$row]['stime']='';
                                $bookings_data[$row]['edate']='';
                                $bookings_data[$row]['etime']='';
                                $bookings_data[$row]['seat_type']='';
                                $bookings_data[$row]['attendees']=$attendees;
                                $bookings_data[$row]['seat']='';
                                $bookings_data[$row]['gateway']='';
                                $bookings_data[$row]['booking_status']='';
                                $bookings_data[$row]['payment_status']='';
                                
                            }
                            $count++;
                        }
                    }

                }
                $row++;
            }
        }
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="ep-bookings-'.md5(time().mt_rand(100, 999)).'.csv"');
        $f = fopen('php://output', 'w');
        foreach ($bookings_data as $line) {
            fputcsv($f, $line);
        }
    }
}
