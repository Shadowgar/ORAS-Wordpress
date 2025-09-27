<div id="event-ticket-section" class="ticketdiv">
    <?php if(!empty($tickets)):?>
    <select name="ticket_template">
        <option value=""><?php esc_html_e('Select Ticket Template', 'eventprime-event-tickets');?></option>
        <?php foreach($tickets as $ticket){
            ?>
            <option value="<?php esc_attr_e($ticket['id']);?>" <?php echo selected($selected_template, $ticket['id']);?>><?php esc_attr_e($ticket['name']);?></option>
            <?php
        }?>
        
    </select>
    <?php else:?>
    <p><?php esc_html_e('No ticket template found.','eventprime-event-tickets');?></p>
    <?php endif;?>
</div>