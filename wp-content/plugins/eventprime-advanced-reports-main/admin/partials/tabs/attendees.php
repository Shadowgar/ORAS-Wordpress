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
                                        <div class="ep-report-filter-attr">
                                            <label> <?php esc_html_e('Event', 'eventprime-event-advanced-reports'); ?></label>
                                            <select id="ep_event_id" class="ep-form-control ep-form-control-sm" name="event" >
                                                <option value="all"><?php esc_html_e('All Event', 'eventprime-event-advanced-reports'); ?></option>
                                                <?php
                                                if ( !empty( $events_lists ) ):
                                                    foreach ($events_lists as $event):?>
                                                        <option value="<?php echo $event['id']; ?>"><?php echo $event['name']; ?></option>
                                                        <?php
                                                    endforeach;
                                                endif;
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="ep-box-col-2">
                                        <div class="ep-report-filter-attr ep-d-flex ep-align-items-center">
                                            <button type="button" id="ep_attendee_filter" class="button-primary ep-btn ep-ar-btn-primary">Filter</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="postbox " id="test1">
                        <div class="inside chart-with-sidebar">
                            <div class="ep-box-row ep-border-bottom ep-pb-2">
                                <div class="ep-box-col-12">
                                    <div class="chart-sidebar" id="ep_attendee_stat_container">
                                        <?php echo do_action('ep_attendees_report_stat', $attendees_data); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="ep-box-row">
                                <div class="ep-box-col-12">
                                    <div class="ep-box-w-100 ep-border ep-mt-3" id="ep_attendees_chart">

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
        <?php echo do_action('ep_attendees_report_bookings_list', $attendees_data);?>
    </div>
</div>
<script>
    google.load('visualization', '1', {packages: ['corechart']});
    google.charts.setOnLoadCallback( function() { 
        drawAttendeesChart(<?php echo json_encode($attendees_data->chart);?>);
    });
</script>