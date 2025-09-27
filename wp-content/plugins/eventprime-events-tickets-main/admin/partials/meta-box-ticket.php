<div class="emagic">
    <div class="panel-wrap ep_event_metabox ep-box-wrap ep-p-0">
        <a href="<?php echo admin_url( 'admin-ajax.php?action=ep_download_invoice&_nonce='.wp_create_nonce( 'invoice' ).'&booking_id='.$post->ID );?>" type="button" class="button"><?php _e('Download Invoice','eventprime-event-calendar-management');?></a>
    </div>
</div>