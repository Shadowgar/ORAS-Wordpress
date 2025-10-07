<div class="wrap ep-box-wrap">
    <div id="poststuff" class="ep-box-row">
        <div class="ep-box-col-12"><?php 
            if( isset( $payments_data->posts_details->posts ) && ! empty( $payments_data->posts_details->posts ) ) {
                $booking_controller = new Eventprime_Basic_Functions;
                $booking_controller_data = new EventPrime_Bookings;?>
                <div class="ep-d-flex ep-justify-content-between ep-align-items-center"><?php
                    echo sprintf( esc_html__( '%d booking found', 'eventprime-event-advanced-reports' ), $payments_data->posts_details->found_posts );?>
                    <button type="button" id="ep_payments_export" class="button-primary ep-btn ep-ar-btn-primary"><?php esc_html_e( 'Export All', 'eventprime-event-advanced-reports' );?></button>
                </div>
                <table class="form-table ep-table ep-table-striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Booking ID','eventprime-event-advanced-reports' );?></th>
                            <th><?php esc_html_e( 'Event','eventprime-event-advanced-reports' );?></th>
                            <th><?php esc_html_e( 'Event Date','eventprime-event-advanced-reports' );?></th>
                            <th><?php esc_html_e( 'Status','eventprime-event-advanced-reports' );?></th>
                            <th><?php esc_html_e( 'Payment Gateway','eventprime-event-advanced-reports' );?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($payments_data->posts_details->posts as $booking){
                            $event_title = $booking->post_title;
                            $booking = $booking_controller_data->load_booking_detail( $booking->em_id );?>
                            <tr>
                                <td><?php echo esc_html( $booking->em_id );?></td>
                                <td><?php echo esc_html( $event_title );?></td>
                                <td>
                                    <?php if(isset($booking->event_data->em_start_date)){?>
                                        <span>
                                            <?php echo esc_html($booking_controller->ep_timestamp_to_date( $booking->event_data->em_start_date, 'dS M Y', 1 ) );
                                            if( ! empty( $booking->event_data->em_start_time ) ) {
                                                echo ', ' . esc_html( $booking->event_data->em_start_time );
                                            }?>
                                        </span><?php
                                    }else{
                                        echo '--';
                                    }?>
                                </td>
                                <td>
                                    <?php
                                    if( ! empty( $booking->em_status ) ) {
                                        if( $booking->em_status == 'publish' || $booking->em_status == 'completed' ) {?>
                                            <span class="ep-booking-status ep-status-confirmed">
                                                <?php esc_html_e( 'Completed', 'eventprime-event-advanced-reports' );?>
                                            </span><?php
                                        }
                                        if( $booking->em_status == 'pending' ) {?>
                                            <span class="ep-booking-status ep-status-pending">
                                                <?php esc_html_e( 'Pending', 'eventprime-event-advanced-reports' );?>
                                            </span> <?php
                                        }
                                        if( $booking->em_status == 'cancelled' ) {?>
                                            <span class="ep-booking-status ep-status-cancelled">
                                                <?php esc_html_e( 'Cancelled', 'eventprime-event-advanced-reports' );?>
                                            </span><?php
                                        }
                                        if( $booking->em_status == 'refunded' ) {?>
                                            <span class="ep-booking-status ep-status-refunded">
                                                <?php esc_html_e( 'Refunded', 'eventprime-event-advanced-reports' );?>
                                            </span><?php
                                        }
                                        if( $booking->em_status == 'draft' ) {?>
                                            <span class="ep-booking-status ep-status-draft">
                                                <?php esc_html_e( 'Draft', 'eventprime-event-advanced-reports' );?>
                                            </span><?php
                                        }
                                    } else{
                                        $booking_status = $booking->post_data->post_status;
                                        if( ! empty( $booking_status ) ) {?>
                                            <span class="ep-booking-status ep-status-<?php echo esc_attr( $booking_status );?>">
                                                <?php esc_html_e( $booking_controller->get_status()[$booking_status], 'eventprime-event-advanced-reports' );?>
                                            </span><?php
                                        } else{
                                            echo '--';
                                        }
                                    }?>
                                </td>
                                <td>
                                    <?php
                                    if( ! empty( $booking->em_payment_method ) ) {
                                        echo esc_html( ucfirst( $booking->em_payment_method ) );
                                    } else{
                                        if( ! empty( $booking->em_order_info['payment_gateway'] ) ) {
                                            echo esc_html( ucfirst( $booking->em_order_info['payment_gateway'] ) );
                                        } else{
                                            echo '--';
                                        }
                                    }?>
                                </td>
                            </tr>
                        <?php }?>
                    </tbody>
                </table>
                <div class="ep-reports-boooking-load-more">
                    <?php
                    if( isset($payments_data->posts_details->max_num_pages) && $payments_data->posts_details->max_num_pages > 1) {?>
                        <div class="ep-report-load-more ep-frontend-loadmore ep-box-w-100 ep-my-4 ep-text-center">
                            <input type="hidden" id="ep-report-payment-paged" value="1"/>
                            <button type="button" data-max="<?php echo esc_attr( $payments_data->posts_details->max_num_pages );?>" id="ep-loadmore-report-payments" class="button-primary ep-btn ep-ar-btn-primary"><span class="ep-spinner ep-spinner-border-sm ep-mr-1"></span><?php esc_html_e( 'Load more', 'eventprime-event-advanced-reports' );?></button>
                        </div><?php
                    }?>
                </div><?php 
            } else{ 
                esc_html_e( 'No Booking Found.', 'eventprime-event-advanced-reports' );
            }?>
        </div>
    </div>
</div>