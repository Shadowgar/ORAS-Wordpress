<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_skin_carousel $this */

$styling = $this->main->get_styling();
$event_colorskin = (isset($styling['mec_colorskin']) or isset($styling['color'])) ? 'colorskin-custom' : '';
$settings = $this->main->get_settings();
$display_label = isset($this->skin_options['display_label']) ? $this->skin_options['display_label'] : false;
$reason_for_cancellation = isset($this->skin_options['reason_for_cancellation']) ? $this->skin_options['reason_for_cancellation'] : false;

$carousel_type = 'type3';
?>
<div class="mec-wrap <?php echo esc_attr($event_colorskin); ?>">
    <div class="mec-event-carousel-type3 mec-event-carousel-<?php echo esc_attr($this->style); ?>">
        <div class='mec-owl-crousel-skin-<?php echo esc_attr($carousel_type); ?> mec-owl-carousel mec-owl-theme'>
            <?php foreach($this->events as $date => $events): ?>
                <?php foreach($events as $event):

                    $location_id = $this->main->get_master_location_id($event);
                    $location = ($location_id ? $this->main->get_location_data($location_id) : array());

                    $organizer_id = $this->main->get_master_organizer_id($event);
                    $organizer = ($organizer_id ? $this->main->get_organizer_data($organizer_id) : array());
                    $event_color = $this->get_event_color_dot($event);
                    $event_date = (isset($event->date['start']) ? $event->date['start']['date'] : $event->data->meta['mec_start_date']);
                    $event_start_date = !empty($event->date['start']['date']) ? $event->date['start']['date'] : '';
                    $event_end_date = !empty($event->date['end']['date']) ? $event->date['end']['date'] : '';
                    $start_time = (isset($event->data->time) ? $event->data->time['start'] : '');
                    $end_time = (isset($event->data->time) ? $event->data->time['end'] : '');
                    $multiple_date = ($event_start_date != $event_end_date) ? 'mec-multiple-event' : '';
                    ?>
                    <article class="<?php echo (isset($event->data->meta['event_past']) and trim($event->data->meta['event_past'])) ? 'mec-past-event ' : ''; ?>mec-event-article <?php echo esc_attr($multiple_date); ?> mec-clear <?php echo esc_attr($this->get_event_classes($event)); ?>" itemscope>
                        <?php do_action('mec_schema', $event); // MEC Schema ?>
                        <div class="event-carousel-type3-head clearfix">
                            <div class="mec-event-image">
                                <?php
                                    if($event->data->thumbnails['full']) echo MEC_kses::element($this->display_link($event, $event->data->thumbnails['full'], ''));
                                    else echo '<img src="'. $this->main->asset('img/no-image.png') .'" />';
                                ?>
                                <?php echo MEC_kses::element($this->get_label_captions($event)); ?>
                            </div>
                            <div class="mec-event-footer-carousel-type3">
                                <div class="mec-event-datetime-info-wrap">
                                    <div class="mec-event-date-info">
                                        <i class="mec-sl-calendar"></i>
                                        <div class="mec-event-date">
                                            <?php if(isset($settings['multiple_day_show_method']) && $settings['multiple_day_show_method'] == 'all_days') : ?>
                                                <?php echo esc_html($this->main->date_i18n($this->date_format_type3_1, strtotime($event->date['start']['date']))); ?>
                                            <?php else: ?>
                                                <?php echo MEC_kses::element($this->main->dateify($event, $this->date_format_type3_1)); ?>
                                            <?php endif; ?>
                                            <div class="mec-event-day">
                                                <span><?php echo date_i18n('l', strtotime($date)); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mec-event-time-info">
                                        <i class="mec-sl-clock"></i>
                                        <?php
                                        $args = array(
                                            'separator' => '',
                                        );
                                        echo $this->main->display_time( $start_time, $end_time, $args );
                                        ?>
                                    </div>
                                </div>
                                <?php $soldout = $this->main->get_flags($event); ?>
                                <?php //if($this->include_events_times) echo MEC_kses::element($this->main->display_time($start_time, $end_time)); ?>
                                <?php //if($this->localtime) echo MEC_kses::full($this->main->module('local-time.type3', array('event' => $event))); ?>
                                <h4 class="mec-event-carousel-title">
                                    <?php
                                    echo MEC_kses::element($this->display_link( $event ));
                                    echo $event_color;

                                    // echo MEC_kses::element($this->display_custom_data($event));
                                    // echo MEC_kses::element($soldout);
                                    ?>
                                    <?php echo MEC_kses::element($this->main->get_normal_labels($event, $display_label).$this->main->display_cancellation_reason($event, $reason_for_cancellation)); ?><?php do_action('mec_shortcode_virtual_badge', $event->data->ID); ?>
                                </h4>

                                <?php
                                $excerpt = trim($event->data->post->post_excerpt) ? $event->data->post->post_excerpt : '';
                                // Safe Excerpt for UTF-8 Strings
                                if(!trim($excerpt)) {
                                    $ex = explode(' ', strip_tags(strip_shortcodes($event->data->post->post_content)));
                                    $words = array_slice($ex, 0, 30);
                                    $excerpt = implode(' ', $words);
                                } else {
                                    $ex = explode(' ', strip_tags(strip_shortcodes($excerpt)));
                                    $words = array_slice($ex, 0, 30);
                                    $excerpt = implode(' ', $words);
                                }
                                ?>
                                <div class="mec-event-description">
                                    <?php if( !empty( $excerpt ) ): ?>
                                        <p><?php echo $excerpt.(trim($excerpt) ? ' ...' : ''); ?></p>
                                    <?php endif; ?>
                                </div>


                                <?php if(isset($location['address']) and trim($location['address'])): ?>
                                    <div class="mec-event-location">
                                        <i class="mec-sl-location-pin"></i>
                                        <address class="mec-events-address"><span class="mec-address"><?php echo (isset($location['address']) ? $location['address'] : ''); ?></span></address>
                                    </div>
                                <?php endif; ?>
                                <?php if($settings['social_network_status'] != '0'): ?>
                                    <ul class="mec-event-sharing-wrap">
                                        <li class="mec-event-share">
                                            <a href="#" class="mec-event-share-icon">
                                                <i class="mec-sl-share mec-bg-color-hover mec-border-color-hover"></i>
                                            </a>
                                        </li>
                                        <li>
                                            <ul class="mec-event-sharing">
                                                <?php echo MEC_kses::full($this->main->module('links.list', array('event' => $event))); ?>
                                            </ul>
                                        </li>
                                    </ul>
                                <?php endif; ?>
                                <?php echo MEC_kses::element($this->display_link($event, ((is_array($event->data->tickets) and count($event->data->tickets) and !strpos($soldout, '%%soldout%%') and !$this->booking_button and !$this->main->is_expired($event)) ? $this->main->m('register_button', esc_html__('Register', 'mec')) : $this->main->m('view_detail', esc_html__('View Detail', 'mec'))), 'mec-booking-button mec-bg-color-hover mec-border-color-hover')); ?>
                                <?php echo MEC_kses::form($this->booking_button($event)); ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
	</div>
</div>