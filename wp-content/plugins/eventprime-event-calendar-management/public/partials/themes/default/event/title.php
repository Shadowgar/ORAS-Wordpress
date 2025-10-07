<div class="ep-box-row">
    <!-- Event Title -->
    <div class="ep-box-col-8" id="ep-sl-event-name">
        <h2 class="ep-fw-bold ep-fs-2 ep-mt-3 ep-border-left ep-border-3 ep-border-warning ep-ps-3 ep-text-break" id="ep_single_event_title">
            <?php echo esc_html( wp_strip_all_tags($args->post->post_title) );?>
        </h2>
        <br>
    </div>

    <!-- Icons Row -->
    <div class="ep-box-col-4" style="padding-top: 12px; text-align: right; display: flex; justify-content: flex-end; align-items: center; gap: 8px; flex-wrap: nowrap;">

        <!-- Print Icon -->
        <?php if(!empty( $ep_functions->ep_get_global_settings( 'show_print_icon' ) )): ?>
            <span class="material-icons-outlined ep-cursor ep-button-text-color" onclick="window.print();" title="Print">print</span>
        <?php endif; ?>

        <!-- Calendar Dropdown -->
        <div class="ep-sl-event-action ep-cursor ep-position-relative ep-event-ical-action">
            <span class="material-icons-outlined ep-exp-dropbtn ep-mr-3 ep-cursor" title="<?php esc_html_e( 'Add to Calendar', 'eventprime-event-calendar-management' ); ?>">event</span>
            <ul class="ep-calendar-exp-dropdown-content ep-event-share ep-m-0 ep-p-0">
                <li class="ep-event-social-icon">
                    <a href="javascript:void(0);" id="ep_event_ical_export" data-event_id="<?php echo esc_attr( $args->event->id );?>" title="<?php esc_html_e( 'iCal Export', 'eventprime-event-calendar-management' ); ?>">
                        <?php esc_html_e( 'iCal Export', 'eventprime-event-calendar-management' ); ?>
                    </a>
                </li>
                <?php
                $gcal_starts = $ep_functions->ep_convert_event_date_time_to_timestamp( $args->event, 'start' );
                $gcal_ends = $ep_functions->ep_convert_event_date_time_to_timestamp( $args->event, 'end' );
                $gcal_details = urlencode( wp_kses_post( $args->event->description ) );
                $location = !empty($args->event->venue_details->em_address) ? urlencode($args->event->venue_details->em_address) : '';
                $gcal_url = ($gcal_starts && $gcal_ends) ? 'https://www.google.com/calendar/event?action=TEMPLATE&text=' . urlencode( esc_attr( $args->event->name ) ) . '&dates=' . gmdate( 'Ymd\\THi00\\Z', esc_attr( $gcal_starts ) ) . '/' . gmdate('Ymd\\THi00\\Z', esc_attr( $gcal_ends ) ) . '&details=' . esc_attr( $gcal_details ) . '&location=' . esc_attr($location) : '';
                if(!empty($gcal_url)) { ?>
                    <li class="ep-event-social-icon"><a href="<?php echo esc_url($gcal_url); ?>" target="_blank"><?php esc_html_e( 'Google Calendar', 'eventprime-event-calendar-management' ); ?></a></li>
                <?php } ?>

                <?php
                $o365_url = ($gcal_starts && $gcal_ends) ? 'https://outlook.office365.com/owa/?path=/calendar/action/compose&subject=' . rawurlencode( esc_attr( $args->event->name ) ) . '&startdt='.date( 'Y-m-d\TH:i:s', $gcal_starts ).'&enddt='.date( 'Y-m-d\TH:i:s', $gcal_ends ).'&location=' . $location : '';
                if(!empty($o365_url)) { ?>
                    <li class="ep-event-social-icon"><a href="<?php echo esc_url($o365_url); ?>" target="_blank"><?php esc_html_e( 'Outlook 365', 'eventprime-event-calendar-management' ); ?></a></li>
                <?php } ?>

                <?php
                $olive_url = ($gcal_starts && $gcal_ends) ? "https://outlook.live.com/owa/?path=/calendar/action/compose&startdt=".date( 'Ymd\THis\Z', $gcal_starts )."&enddt=".date( 'Ymd\THis\Z', $gcal_ends )."&subject=".rawurlencode( esc_attr( $args->event->name ) )."&location=".$location : '';
                if(!empty($olive_url)) { ?>
                    <li class="ep-event-social-icon"><a href="<?php echo esc_url($olive_url); ?>" target="_blank"><?php esc_html_e( 'Outlook Live', 'eventprime-event-calendar-management' ); ?></a></li>
                <?php } ?>
            </ul>
        </div>

        <!-- Share Dropdown -->
        <div class="ep-sl-event-action ep-cursor ep-position-relative">
            <span class="material-icons-outlined ep-exp-dropbtn" title="<?php esc_html_e( 'Share', 'eventprime-event-calendar-management' ); ?>">share</span>
            <ul class="ep-calendar-exp-dropdown-content ep-event-share ep-m-0 ep-p-0">
                <li class="ep-event-social-icon"><a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(get_permalink($args->event->id)); ?>" target="_blank">Facebook</a></li>
                <li class="ep-event-social-icon"><a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(get_permalink($args->event->id)); ?>" target="_blank">Twitter</a></li>
                <li class="ep-event-social-icon"><a href="mailto:?subject=<?php echo rawurlencode($args->event->name); ?>&body=<?php echo urlencode(get_permalink($args->event->id)); ?>">Email</a></li>
            </ul>
        </div>

        <!-- Social Media Icons -->
        <?php if ( ! empty( $args->event->em_social_links ) ) {
            foreach ( ['facebook','instagram','linkedin','twitter','youtube'] as $network ) {
                if( !empty($args->event->em_social_links[$network]) ) {
                    $image_url = plugin_dir_url( EP_PLUGIN_FILE ) . 'public/partials/images/' . $network . '-icon.png'; ?>
                    <a href="<?php echo esc_url( $args->event->em_social_links[$network] ); ?>" target="_blank" title="<?php echo ucfirst($network); ?>">
                        <img src="<?php echo esc_url($image_url); ?>" width="24" style="display: block;" />
                    </a>
                <?php }
            }
        } ?>
    </div>
</div>
