<div class="wrap">
    <div id="poststuff">
        <div id="postbox-container" class="postbox-container">
            <div class="meta-box-sortables ui-sortable" id="normal-sortables">
                <div class="postbox " id="test2">
                    <div class="inside">
                        <div class="ep-report-forms ep-box-wrap">
                            <div class="ep-box-row">
                                <div class="ep-box-col-3">
                                    <div class="ep-report-filter-attr " id="ep-reports-datepicker-div">
                                        <label> <?php esc_html_e('Date', 'eventprime-event-advanced-reports'); ?></label>
                                        <input id="ep-reports-datepicker" type="text"/>
                                    </div>
                                </div>
                                <div class="ep-box-col-3">
                                    <div class="ep-report-filter-attr">
                                        <label> <?php esc_html_e('Event', 'eventprime-event-advanced-reports'); ?></label>
                                        <select id="ep_event_id" class="ep-form-control ep-form-control-sm" name="event" >
                                            <option value="all"><?php esc_html_e('All Event', 'eventprime-event-advanced-reports'); ?></option>

                                            <?php 
                                            if(!empty($events_lists)):
                                                foreach($events_lists as $event):
                                                    ?>
                                                    <option value="<?php echo $event['id'];?>"><?php echo $event['name'];?></option>
                                                    <?php
                                                endforeach;
                                            endif;
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="ep-box-col-2">
                                    <div class="ep-report-filter-attr">
                                        <button type="button" id="ep_booking_filter" class="button-primary ep-btn ep-ar-btn-primary">Filter</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="postbox " id="test1">
                    <div class="inside chart-with-sidebar">
                        <div class="ep-box-row">
                            <div class="ep-box-col-2">
                                <div class="chart-sidebar" id="ep_booking_stat_container">
                                    <?php echo do_action( 'ep_bookings_report_stat', $bookings_data );?>
                                </div>
                            </div>
                            <div class="ep-box-col-9">
                                <div class="main" id="ep_bookings_chart"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>