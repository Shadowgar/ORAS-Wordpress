<div class="emagic">
<div class="wrap ep-box-wrap">
    <div id="poststuff">
        <div id="postbox-container" class="postbox-container">
            <div class="meta-box-sortables ui-sortable" id="normal-sortables">
                <div class="postbox ep-border ep-border-bottom-0 ep-m-0" id="test2">
                    <div class="inside">
                        <div class="ep-report-forms">
                            <div class="ep-box-row ep-mt-3 ep-items-end">
                                <div class="ep-box-col-3">
                                    <div class="ep-report-filter-attr" id="ep-reports-datepicker-div">
                                        <label> <?php esc_html_e('Date', 'eventprime-event-advanced-reports'); ?></label>
                                        <input id="ep-reports-datepicker" class="ep-form-control" type="text"/>
                                    </div>
                                </div>
                                <div class="ep-box-col-3">
                                    <div class="ep-report-filter-attr">
                                        <label> <?php esc_html_e('Event', 'eventprime-event-advanced-reports'); ?></label>
                                        <select id="ep_event_id" class="ep-form-control ep-form-control-sm" name="event" >
                                            <option value="all"><?php esc_html_e('All Event', 'eventprime-event-advanced-reports'); ?></option><?php 
                                            if( ! empty( $events_lists ) ){
                                                foreach( $events_lists as $event ){?>
                                                    <option value="<?php echo esc_attr( $event['id'] );?>"><?php echo esc_html( $event['name'] );?></option><?php
                                                }
                                            }?>
                                        </select>
                                    </div>
                                </div>
                                <div class="ep-box-col-2">
                                    <div class="ep-report-filter-attr">
                                        <label> <?php esc_html_e('Payment Method', 'eventprime-event-advanced-reports'); ?></label>
                                        <select id="ep_payment_method" class="ep-form-control ep-form-control-sm" name="payment_method" >
                                            <option value="all"><?php esc_html_e('All Payment Method', 'eventprime-event-advanced-reports'); ?></option><?php 
                                            $methods = apply_filters( 'ep_payments_gateways_list', array() );
                                            if( ! empty( $methods ) ) {
                                                foreach( $methods as $key => $method ){?>
                                                    <option value="<?php echo esc_attr( $key );?>"><?php echo esc_html( $method['method'] );?></option><?php
                                                }
                                            }?>
                                        </select>
                                    </div>
                                </div>
                                <div class="ep-box-col-2">
                                    <div class="ep-report-filter-attr">
                                        <?php 
                                        $booking_status_list = array(
                                            'completed' => __( 'Completed', 'eventprime-event-advanced-reports' ),
                                            'pending'   => __( 'Pending', 'eventprime-event-advanced-reports' ),
                                            'cancelled' => __( 'Cancelled', 'eventprime-event-advanced-reports' ),
                                            'refunded'  => __( 'Refunded', 'eventprime-event-advanced-reports' )
                                        );
                                        ?>
                                        <label> <?php esc_html_e('Booking Status', 'eventprime-event-advanced-reports'); ?></label>
                                        <select id="ep_booking_status" class="ep-form-control ep-form-control-sm" name="booking_status" >
                                            <option value=""><?php esc_html_e('All status', 'eventprime-event-advanced-reports'); ?></option>
                                            <?php foreach($booking_status_list as $key => $status){?>
                                                <option value="<?php echo esc_attr( $key );?>" ><?php echo esc_html( $status );?></option><?php 
                                            }?>
                                        </select>
                                    </div>
                                </div>
                                <div class="ep-box-col-2">
                                    <div class="ep-report-filter-attr">
                                        <button type="button" id="ep_payment_filter" class="button-primary ep-btn ep-ar-btn-primary">Filter</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="postbox ep-border" id="test1">
                    <div class="inside chart-with-sidebar">
                        <div class="ep-box-row ep-border-bottom ep-pb-2">
                            <div class="ep-box-col-12">
                                <div class="chart-sidebar" id="ep_payment_stat_container">
                                    <?php echo do_action('ep_payments_report_stat', $payments_data);?>
                                </div>
                            </div>
                        </div>
                        <div class="ep-box-row">
                            <div class="ep-box-col-12">
                                <div class="main" id="ep_bookings_chart">
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="ep-report-booking-list">
        <?php echo do_action('ep_payments_report_bookings_list', $payments_data);?>
    </div>
    
</div>

<script>
google.load('visualization', '1', {packages: ['corechart']});
google.charts.setOnLoadCallback( function() { 
    drawPaymentsChart(<?php echo json_encode($payments_data->chart);?>);
});
</script>
