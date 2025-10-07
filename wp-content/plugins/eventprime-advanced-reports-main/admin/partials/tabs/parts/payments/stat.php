<?php
$ep_functions = new Eventprime_Basic_Functions;
?>
<ul class="ep-chart-legend ep-mt-3 ep-d-flex">
    <li>
        <div class="ep-chart-legend-stat ep-fs-2"><?php echo esc_attr($ep_functions->ep_price_with_position($payments_data->stat->total_revenue)); ?></div>
       <div class="ep-chart-legend-text ep-text-muted ep-text-small"><?php esc_html_e('gross sales in this period', 'eventprime-event-advanced-reports'); ?></div>
    </li>
    <li>
        <div class="ep-chart-legend-stat ep-fs-2"><?php
            if (!empty($payments_data->stat->total_revenue && !empty($payments_data->stat->days_count))) {
                echo esc_attr($ep_functions->ep_price_with_position($payments_data->stat->total_revenue / $payments_data->stat->days_count));
            } else {
                echo esc_attr($ep_functions->ep_price_with_position(0));
            }
            ?>
        </div>
        <div class="ep-chart-legend-text ep-text-muted ep-text-small"><?php esc_html_e('average gross daily sales', 'eventprime-event-advanced-reports'); ?></div>
    </li>
    <li>
        <div class="ep-chart-legend-stat ep-fs-2"><?php echo esc_attr($payments_data->stat->total_booking); ?></div>
        <div class="ep-chart-legend-text ep-text-muted ep-text-small"><?php esc_html_e('Booking placed', 'eventprime-event-advanced-reports'); ?></div>
    </li>
    <li>
        <div class="ep-chart-legend-stat ep-fs-2"><?php echo esc_attr($payments_data->stat->total_attendees); ?></div>
       <div class="ep-chart-legend-text ep-text-muted ep-text-small"><?php esc_html_e('Tickets purchased', 'eventprime-event-advanced-reports'); ?></div>
    </li>
    <li>
        <div class="ep-chart-legend-stat ep-fs-2"><?php echo esc_attr($ep_functions->ep_price_with_position($payments_data->stat->coupon_discount)); ?></div>
        <div class="ep-chart-legend-text ep-text-muted ep-text-small"><?php esc_html_e('Coupon Used', 'eventprime-event-advanced-reports'); ?></div>
    </li>
</ul>