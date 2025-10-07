<?php
$ep_function = new Eventprime_Basic_Functions;
?>
<ul class="ep-chart-legend ep-mt-3 ep-d-flex">
    <li>
        <div class="ep-chart-legend-stat ep-fs-2"><?php echo esc_attr($ep_function->ep_price_with_position($attendees_data->stat->total_revenue)); ?></div>
        <div class="ep-chart-legend-text ep-text-muted ep-text-small"><?php esc_html_e('Total Revenue', 'eventprime-event-advanced-reports'); ?></div>
    </li>
    <li>
        <div class="ep-chart-legend-stat ep-fs-2"><?php echo esc_attr($attendees_data->stat->total_booking); ?></div>
        <div class="ep-chart-legend-text ep-text-muted ep-text-small"><?php esc_html_e('Total Booking', 'eventprime-event-advanced-reports'); ?></div>
    </li>
    <li>
        <div class="ep-chart-legend-stat ep-fs-2"><?php echo esc_attr($attendees_data->stat->total_attendees); ?></div>
        <div class="ep-chart-legend-text ep-text-muted ep-text-small"><?php esc_html_e('Total Attendees', 'eventprime-event-advanced-reports'); ?></div>
    </li>
</ul>