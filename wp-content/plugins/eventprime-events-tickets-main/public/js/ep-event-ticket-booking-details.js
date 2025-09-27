jQuery( function( $ ) {

    // print ticket
    $( document ).on( 'click', '.ep_booking_print_ticket', function() {
        let seat_data = $( this ).data( 'seat_data' );
        if( seat_data ) {
            let seat_no = atob( seat_data.seat_no );
            let booking_id = atob( seat_data.booking_id );
            let ticket_id = atob( seat_data.ticket_id );
            let venue_type = atob( seat_data.venue_type );
        
            if( booking_id && ticket_id && venue_type ) {
                let data = { 
                    action    : 'ep_event_booking_print_ticket', 
                    security  : ep_event_ticket_booking_detail.booking_print_ticket_nonce,
                    booking_id: booking_id,
                    seat_no   : seat_no,
                    ticket_id : ticket_id,
                    venue_type: venue_type,
                };

                $.ajax({
                    type    : "POST",
                    url     : eventprime.ajaxurl,
                    data    : data,
                    success : function( response ) {
                        if( response.success == false ) {
                            show_toast( 'error', response.data.error );
                        } else{
                            let url = response.data.url;
                            document.location.href = url;
                        }
                    }
                });
            }
        }
    });

    // share the ticket
    $( document ).on( 'click', '.ep_event_ticket_share_on_email', function() {
        $( this ).siblings( '.ep-error-message' ).html( '' );
        let ticket_id = $( this ).data( 'ticket_id' );
        let attendee_num = $( this ).data( 'attendee_num' );
        if( ticket_id && attendee_num ) {
            let merge_nums = ticket_id.toString() + attendee_num;
            $( '#ep_booking_detail_share_ticket_'+ merge_nums + ' .ep-loader' ).show();
            let email = $( '#ep_event_ticket_email_address_share_' + merge_nums ).val();
            if( email ) {
                if( !is_valid_email( email ) ) {
                    let invalid_email_string = get_translation_string( 'invalid_email' );
                    $( this ).siblings( '.ep-error-message' ).html( invalid_email_string );
                    $( this ).siblings( '.ep-error-message' ).show();
                    $( '#ep_booking_detail_share_ticket_'+ merge_nums + ' .ep-loader' ).hide();
                    return false;
                }

                let seat_data = $( '#ep_event_ticket_seat_data_'+merge_nums ).val();
                if( seat_data ) {
                    seat_data = JSON.parse( seat_data );
                    let seat_no = atob( seat_data.seat_no );
                    let booking_id = atob( seat_data.booking_id );
                    let ticket_id = atob( seat_data.ticket_id );
                    let venue_type = atob( seat_data.venue_type );
                
                    if( booking_id && venue_type && ticket_id && email ) {
                        // subject and message
                        let subject = '', message = '';
                        subject = $( '#ep_event_ticket_subject_share_' + merge_nums ).val();
                        if( $( '#ep_event_ticket_message_share_' + merge_nums ).is(':visible') ) {
                            message = $( '#ep_event_ticket_message_share_' + merge_nums ).val();
                        } else{
                            message = tinymce.get( 'ep_event_ticket_message_share_' + merge_nums ).getContent();
                        }


                        let ticket_data = {
                            booking_id: booking_id,
                            seat_no   : seat_no,
                            ticket_id : ticket_id,
                            email     : email,
                            venue_type: venue_type,
                            subject   : subject,
                            message   : message
                        };
                        let data = { 
                            action    : 'ep_event_booking_share_ticket', 
                            security  : ep_event_ticket_booking_detail.booking_share_ticket_nonce,
                            ticket_data: JSON.stringify( ticket_data )
                        };

                        $.ajax({
                            type    : "POST",
                            url     : eventprime.ajaxurl,
                            data    : data,
                            success : function( response ) {
                                if( response.success == false ) {
                                    $( '#ep_booking_detail_share_ticket_'+ merge_nums + ' .ep-error-message' ).html( response.data.error );
                                    $( '#ep_booking_detail_share_ticket_'+ merge_nums + ' .ep-error-message' ).show();
                                    return false;
                                } else{
                                    $( '#ep_booking_detail_share_ticket_'+ merge_nums + ' .ep-error-message' ).html( response.data.message );
                                    $( '#ep_booking_detail_share_ticket_'+ merge_nums + ' .ep-error-message' ).show();
                                    setTimeout( function() {
                                        $('#ep_booking_detail_share_ticket_'+merge_nums).fadeOut(200);
                                        $('body').removeClass('ep-modal-open-body');
                                    }, 2000);
                                }
                                $( '#ep_booking_detail_share_ticket_'+ merge_nums + ' .ep-loader' ).hide();
                            }
                        });
                    }
                }
            } else{
                let required_email_string = get_translation_string( 'required' );
                $( this ).siblings( '.ep-error-message' ).html( required_email_string );
                $( this ).siblings( '.ep-error-message' ).show();
                $( '#ep_booking_detail_share_ticket_'+ merge_nums + ' .ep-loader' ).hide();
                return false;
            }
        }
    });
});