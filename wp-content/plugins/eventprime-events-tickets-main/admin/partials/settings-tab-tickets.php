<?php $global_options = $options['global'];?>
<div class="ep-setting-tab-content">
    <h2><?php esc_html_e( 'Ticket Setting', 'eventprime-event-tickets' );?></h2>
    <input type="hidden" name="em_setting_type" value="ticket_settings">
</div>
<table class="form-table">
    <tbody>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="allow_event_tickets">
                    <?php esc_html_e( 'Enable Event Tickets', 'eventprime-event-tickets' );?>
                </label>
            </th>
            <td class="forminp forminp-text">
                <label class="ep-toggle-btn">
                    <input type="checkbox" name="allow_event_tickets" value="1" <?php if($global_options->allow_event_tickets) {echo 'checked';}?>>
                    <span class="ep-toogle-slider round"></span>
                </label>
                <div class="ep-help-tip-info ep-my-2 ep-text-muted"><?php esc_html_e( 'Turn on tickting feature.', 'eventprime-event-tickets' );?></div>
            </td>
        </tr>
    </tbody>
</table>