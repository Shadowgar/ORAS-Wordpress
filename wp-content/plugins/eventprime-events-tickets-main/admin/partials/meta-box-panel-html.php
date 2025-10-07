<?php
/**
 * Event meta box html
 */
defined( 'ABSPATH' ) || exit;
$global_options = $options['global'];
?>

<div class="emagic">
    <div class="panel-wrap ep_ticket_metabox">
        <div class="ep-box-wrap ep-ticket-preview">
            <div class="ep-box-row ep-ticket-background">
                <div class="ep-box-col-9 ep-ticket-left-section">
                    <div class="event-details-wrap">
                        <div class="ep-event-title ep-font-color">
                            <?php esc_html_e('Event Name','eventprime-event-tickets');?>
                        </div>
                        <div class="ep-event-date ep-font-color ep-ticket-font ep-text-center">
                            <?php esc_html_e('21st December, 2017 4:30 PM-7:00 PM','eventprime-event-tickets');?>
                         </div>
                        <div class="ep-event-details ep-d-flex ep-align-items-center">
                            <div class="ep-event-logo">
                                <?php $logo_id = get_post_meta($post->ID, 'em_logo', true);
                                     $image = wp_get_attachment_image_url( $logo_id, 'thumbnail' );
                                ?>
                                <img src="<?php echo !empty($image) ? $image : '';?>" id="ep-ticket-logo">
                            </div>
                            <div class="ep-event-detail">
                                <div class="ep-spacer"></div>
                                <div class="ep-site-name ep-font-color ep-ticket-font">
                                    <?php esc_html_e('EVENT SITE NAME','eventprime-event-tickets');?>
                                </div>
                                <div class="ep-site-address ep-font-color">
                                    <?php esc_html_e('Address Line 1, Address Line 2, City  ZipCode','eventprime-event-tickets');?>
                                </div>
                                   <div class="ep-spacer"></div>
                                <div class="ep-site-cordinator ep-font-color ep-ticket-font ep-mb-5">
                                    <?php esc_html_e('BOOKING COORDINATOR','eventprime-event-tickets');?>
                                </div>
                                <div class="ep-site-age-group ep-font-color ep-ticket-font">
                                    <?php esc_html_e('Age group: 18 years and above','eventprime-event-tickets');?>
                                </div>
                            </div>
                        </div>
                        <div class="ep-event-prices">
                            <div class="ep-box-row">
                                <div class="ep-box-col-9">
                                    <div class="ep-event-desc ep-font-color">
                                        <?php esc_html_e('Special Instructions: Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sagittis eget ex sit amet tempor. Maecenas mi nunc, pellentesque quis eleifend eget, fermentum vel nulla.','eventprime-event-tickets');?>
                                    </div>
                                    <div class="ep-font-color ep-font ep-attendee-note ep-mt-3">
                                        <?php esc_html_e("Attendee's Note");?>
                                    </div>
                                </div>
                                <div class="ep-box-col-3">
                                    <div class="ep-ticket-price">
                                        <span class="ep-font-color ep-font ep-price-tag"><?php _e('Price','eventprime-event-tickets'); ?></span>
                                        <span class="ep-price dbfl ep-font-color"><?php esc_html_e('$10','eventprime-event-tickets'); ?><span class="ep-font-color">.00</span></span>
                                        <div class="ep-font-color ep-font ep-ticket-type ep-mt-3"><?php _e('Ticket Type','eventprime-event-tickets'); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="ep-box-col-3 ep-ticket-right-section">
                    <div class="ep-seat-wrap">
                        <p class="ep-font-color ep-seat-tag"><?php _e('SEAT NO.','eventprime-event-tickets'); ?></p>
                        <div class="ep-font-color ep-seat-no"><?php _e('A-21','eventprime-event-tickets'); ?></div>
                        <p class=" ep-font-color ep-seat-id"><?php _e('ID # 1003459234','eventprime-event-tickets'); ?></p>
                        
                    </div>
                    <div class="qr_code">
                        <img src="<?php echo esc_url( plugin_dir_url( __DIR__ ) . 'images/qr_code_sample.png' ) ?>" width="95" height="95" alt="QR Code Image">
                    </div>
                </div>
            </div>
            
            
            
            
        </div>
        
        <div class="ep-box-wrap ep-ticket-setting-fields ep-p-0">
            <div class="ep-box-row">
                <div class="ep-box-col-12">
                <div class="postbox-header"><h2><?php esc_html_e('Ticket Setting','eventprime-event-tickets');?></h2></div> 
            </div>
             </div>
            
            
              <div class="ep-box-row">
                  <div class="ep-box-col-left-2 ep-box-pr-0">
                      <ul class="ep_event_metabox_tabs wc-tabs ep-m-0 ep-p-0 ep-box-h-100">
                          <li class="ep-event-metabox-tab ep-ticket-setting-tab ep-tab-active"> <a href="#"><?php esc_html_e('Setting','eventprime-event-tickets');?></a></li>
                      </ul> 

                  </div>
                  
                <div class="ep-box-col-right-10 ep-box-pl-0">
                    
                    
                    
                    <div class="ep-box-wrap ep-my-3">
                        <div class="ep-box-row ep-mb-3 ep-items-end">
                            <div class="ep-box-col-6 ep-meta-box-data">
                            <label for="em_font1" class="ep-form-label">
                            <?php esc_html_e( 'Font','eventprime-event-tickets' );?>
                            
                        </label>
                                <div class="ep-ticket-font-selector">
                        <?php $font = get_post_meta( $post->ID, 'em_font1', true );?>
                                <select name="em_font1" id="ep-ticket-font" class="ep-form-control"  onchange="ticketFontChange()">
                            <?php foreach($fonts as $key=>$label):?>
                                <option value="<?php echo $key;?>" <?php echo selected($font,$key);?>><?php echo $label;?></option>
                            <?php endforeach;?>
                        </select>
                                </div>  
                                <div class="ep-help-text ep-text-muted ep-mt-1"><?php esc_html('Font to be used in ticket template.','eventprime-event-tickets');?></div>
                            </div>
                        </div>
                        
                        
                        <div class="ep-box-row ep-mb-3 ep-items-end">
                            <div class="ep-box-col-6 ep-meta-box-data">
                                <label for="em_font_color">
                                    <?php esc_html_e('Font Color','eventprime-event-tickets'); ?>
                                    
                                </label>
                                <div class="ep-ticket-font-color">  
                                    <input data-jscolor="{}" class="ep-form-control" value="<?php echo!empty(get_post_meta($post->ID, 'em_font_color', true)) ? get_post_meta($post->ID, 'em_font_color', true) : '#865C16'; ?>" type="text" name="em_font_color" id="ep-ticket-font-color" onchange="ticketFontColorChange()">
                                </div> 
                                <div class="ep-help-text ep-text-muted ep-mt-1"><?php esc_html_e('Font to be used in ticket template.','eventprime-event-tickets');?></div>
                            </div>
                        </div>
                        
                        
                          <div class="ep-box-row ep-mb-3 ep-items-end">
                            <div class="ep-box-col-6 ep-meta-box-data">
                                <label for="em_font_color">
                                  <?php esc_html_e( 'Background Color', 'eventprime-event-calendar-management' );?>
                                  
                                </label>
                                <div class="ep-ticket-font-color">  
                                    <input data-jscolor="{}" class="ep-form-control" value="<?php echo !empty(get_post_meta( $post->ID, 'em_background_color', true )) ? get_post_meta( $post->ID, 'em_background_color', true ) : '#E2C699' ;?>" type="text" name="em_background_color" id="ep-ticket-background-color" onchange="ticketBackgroundColorChange()">
                                </div> 
                                <div class="ep-help-text ep-text-muted ep-mt-1"><?php esc_html_e('Ticket background color. Will be visible in PDF format.','eventprime-event-tickets');?></div>
                            </div>
                        </div>
                        
                        
                        <div class="ep-box-row ep-mb-3 ep-items-end">
                            <div class="ep-box-col-6 ep-meta-box-data">
                                <label for="em_border_color">
                                    <?php esc_html_e( 'Border Color','eventprime-event-tickets' );?>
                                    
                                </label>
                                <div class="ep-ticket-font-color">  
                                    <input data-jscolor="{}" class="ep-form-control" value="<?php echo !empty(get_post_meta( $post->ID, 'em_border_color', true )) ? get_post_meta( $post->ID, 'em_border_color', true ) : '#C8A366' ;?>" type="text" name="em_border_color" id="ep-ticket-border-color" onchange="ticketBorderColorChange()">
                                </div> 
                                <div class="ep-help-text ep-text-muted ep-mt-1"><?php esc_html_e('Ticket border color. Will be visible in PDF format.','eventprime-event-tickets');?></div>
                            </div>
                        </div>
                        
                        
                        <div class="ep-box-row ep-mb-3 ep-items-end">
                            <div class="ep-box-col-6 ep-meta-box-data">
                                <label for="em_logo">
                                    <?php esc_html_e( 'Logo','eventprime-event-tickets' );?>
                                </label>
                                <div class="ep-ticket-font-color">  
                                    <?php 
                                    $logo_id = get_post_meta($post->ID, 'em_logo', true);
                                    if( $image = wp_get_attachment_image_url( $logo_id, 'medium' ) ) : ?>
                                        <a href="#" class="ep-ticket-logo-upload">
                                            <img src="<?php echo esc_url( $image ) ?>" />
                                        </a>
                                        <div class="ep-ticket-logo-remove ep-gal-img-delete"><span class="em-event-gallery-remove dashicons dashicons-trash"></span></div>
                                        <input type="hidden" name="em_logo" value="<?php echo absint( $logo_id ) ?>">
                                    <?php else : ?>
                                    <a href="#" class="button ep-ticket-logo-upload"><?php esc_html_e( 'Upload Logo','eventprime-event-tickets' );?></a>
                                    <div class="ep-ticket-logo-remove ep-gal-img-delete" style="display:none"><span class="em-event-gallery-remove dashicons dashicons-trash"></span></div>
                                    <input type="hidden" name="em_logo" value="">
                                    <?php endif;?>
                                </div> 
                                <div class="ep-help-text ep-text-muted ep-mt-1"><?php esc_html_e('Logo for the Event or Organizer. This will be visible on ticket printouts','eventprime-event-tickets');?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$font = get_post_meta( $post->ID, 'em_font1', true );
$font_color = get_post_meta( $post->ID, 'em_font_color', true );
$background_color = get_post_meta( $post->ID, 'em_background_color', true );
$border_color = get_post_meta( $post->ID, 'em_border_color', true );
?>
<style>
    div#preview-action .preview.button {
        display: none !important;
    }
    <?php if(!empty($font_color)):?>
    .ep-font-color{
        color : <?php echo $font_color;?>
    }
    <?php endif; ?>
    
    <?php if(!empty($background_color)):?>
    .ep-ticket-preview{
        background: <?php echo $background_color;?>
    }
    <?php endif; ?>
    
    <?php if(!empty($border_color)):?>
    .ep-ticket-left-section{
        border-right-color:<?php echo $border_color;?>
    }
    <?php endif; ?>
    <?php if(!empty($font)):?>
    .ep-ticket-preview{
        font-family: <?php echo $font;?>
    }
    <?php endif;?>
</style>