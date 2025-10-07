<?php

/** no direct access **/
defined( 'MECEXEC' ) or die();

$settings                = $this->main->get_settings();
$options                 = get_post_meta( $this->atts['id'], 'sk-options', true );
$days_in_month           = date( 't', mktime( 0, 0, 0, $month, 1, $year ) );
$this->localtime         = isset( $this->skin_options['include_local_time'] ) ? $this->skin_options['include_local_time'] : false;
$map_events              = array();
$showLoadMore            = false;
$display_label           = isset( $this->skin_options['display_label'] ) ? $this->skin_options['display_label'] : false;
$display_cats            = isset( $this->skin_options['display_categories'] ) ? (bool) $this->skin_options['display_categories'] : false;
$reason_for_cancellation = isset( $this->skin_options['reason_for_cancellation'] ) ? $this->skin_options['reason_for_cancellation'] : false;
$style                   = $this->style;
$display_thumbnail       = isset( $this->skin_options['display_thumbnail'] ) ? (bool) $this->skin_options['display_thumbnail'] : true;
$single                  = new MEC_skin_single();
?>
	<div class="mec-event-list-liquid">
		<?php for ( $list_day = 1; $list_day <= $days_in_month; $list_day++ ) { ?>
			<?php
			$time  = strtotime( $year . '-' . $month . '-' . $list_day );
			$today = date( 'Y-m-d', $time );
			?>
			<?php if ( isset( $events[ $today ] ) and count( $events[ $today ] ) ) { ?>
				<?php $showLoadMore = true; ?>
				<?php foreach ( $this->events[ $today ] as $event ) : ?>
					<?php
					$date_format            = $this->date_format_liquid_1 ?? 'd M, Y';
					$map_events[]           = $event;
					$link                   = $this->main->get_event_date_permalink( $event, ( isset( $event->date['start'] ) ? $event->date['start']['date'] : null ) );
					$infowindow_thumb       = trim( $event->data->featured_image['custom'] ) ? '<div class="mec-event-image"><a data-event-id="' . esc_attr( $event->data->ID ) . '" href="' . esc_url( $link ) . '"><img src="' . esc_url( $event->data->featured_image['custom'] ) . '" alt="' . esc_attr( $event->data->title ) . '" width="' . esc_attr( $options['thumbnail_size']['width'] ) . '" height="' . esc_attr( $options['thumbnail_size']['height'] ) . '" /></a></div>' : '';
					$infowindow_thumb       = ! $infowindow_thumb && trim( $event->data->featured_image['medium'] ) ? '<div class="mec-event-image"><a data-event-id="' . esc_attr( $event->data->ID ) . '" href="' . esc_url( $link ) . '"><img src="' . esc_url( $event->data->featured_image['medium'] ) . '" alt="' . esc_attr( $event->data->title ) . '" /></a></div>' : $infowindow_thumb;
					$location               = isset( $event->data->locations[ $event->data->meta['mec_location_id'] ] ) ? $event->data->locations[ $event->data->meta['mec_location_id'] ] : array();
					$organizer              = isset( $event->data->organizers[ $event->data->meta['mec_organizer_id'] ] ) ? $event->data->organizers[ $event->data->meta['mec_organizer_id'] ] : array();
					$start_time             = ( isset( $event->data->time ) ? $event->data->time['start'] : '' );
					$end_time               = ( isset( $event->data->time ) ? $event->data->time['end'] : '' );
					$event_color            = isset( $event->data->meta['mec_color'] ) ? '#' . $event->data->meta['mec_color'] : '';
					$event_color_dot        = $this->get_event_color_dot( $event );
					$event_start_date       = ! empty( $event->date['start']['date'] ) ? $this->main->date_i18n( $date_format, strtotime( $event->date['start']['date'] ) ) : '';
					$event_start_date_day   = ! empty( $event->date['start']['date'] ) ? $this->main->date_i18n( 'd', strtotime( $event->date['start']['date'] ) ) : '';
					$event_start_date_month = ! empty( $event->date['start']['date'] ) ? $this->main->date_i18n( 'M', strtotime( $event->date['start']['date'] ) ) : '';
					$event_start_date_year  = ! empty( $event->date['start']['date'] ) ? $this->main->date_i18n( 'Y', strtotime( $event->date['start']['date'] ) ) : '';
					$event_status           = $event->data->meta['mec_event_status'] ?? '';
					$event_status_text      = '';
					switch ( $event_status ) {
						case 'EventScheduled':
							$event_status_text = __( 'Scheduled', 'mec-liq' );
							break;
						case 'EventPostponed':
							$event_status_text = __( 'Postponed', 'mec-liq' );
							break;
						case 'EventCancelled':
							$event_status_text = __( 'Cancelled', 'mec-liq' );
							break;
						case 'EventMovedOnline':
							$event_status_text = __( 'Moved', 'mec-liq' );
							break;
					}


					$event_id = $event->ID;
					$tickets  = isset( $event->data->tickets ) ? $event->data->tickets : array();
					$dates    = isset( $event->dates ) ? $event->dates : array( $event->date );

					$occurrence_time = isset( $dates[0]['start']['timestamp'] ) ? $dates[0]['start']['timestamp'] : strtotime( $dates[0]['start']['date'] );

					$default_ticket_number = 0;
					if ( count( $tickets ) == 1 ) {
						$default_ticket_number = 1;
					}

					$book         = $this->getBook();
					$availability = $book->get_tickets_availability( $event_id, strtotime( $event_start_date . ' ' . $event->data->time['start'] ) );


					$spots = 0;
					foreach ( $tickets as $ticket_id => $ticket ) {
						$spots = isset( $availability[ $ticket_id ] ) ? $availability[ $ticket_id ] : -1;
					}

					$label_style = '';
					if ( ! empty( $event->data->labels ) ) {
						foreach ( $event->data->labels as $label ) {
							if ( ! isset( $label['style'] ) or ( isset( $label['style'] ) and ! trim( $label['style'] ) ) ) {
								continue;
							}
							if ( $label['style'] == 'mec-label-featured' ) {
								$label_style = esc_html__( 'Featured', 'mec-liq' );
							} elseif ( $label['style'] == 'mec-label-canceled' ) {
								$label_style = esc_html__( 'Canceled', 'mec-liq' );
							}
						}
					}

					$speakers = '""';
					if ( ! empty( $event->data->speakers ) ) {
						$speakers = array();
						foreach ( $event->data->speakers as $key => $value ) {
							$speakers[] = array(
								'@type'  => 'Person',
								'name'   => $value['name'],
								'image'  => $value['thumbnail'],
								'sameAs' => $value['facebook'],
							);
						}

						$speakers = json_encode( $speakers );
					}

					$location = isset( $event->data->locations[ $event->data->meta['mec_location_id'] ] ) ? $event->data->locations[ $event->data->meta['mec_location_id'] ] : array();

					$schema_settings = isset( $settings['schema'] ) ? $settings['schema'] : '';
					if ( $schema_settings == '1' ) :
						?>
					<script type="application/ld+json">
						{
							"@context": "http://schema.org",
							"@type": "Event",
							"startDate": "<?php echo ! empty( $event->data->meta['mec_date']['start']['date'] ) ? $event->data->meta['mec_date']['start']['date'] : ''; ?>",
							"endDate": "<?php echo ! empty( $event->data->meta['mec_date']['end']['date'] ) ? $event->data->meta['mec_date']['end']['date'] : ''; ?>",
							"location": {
								"@type": "Place",
								"name": "<?php echo( isset( $location['name'] ) ? $location['name'] : '' ); ?>",
								"image": "
								<?php
								echo( isset( $location['thumbnail'] ) ? esc_url( $location['thumbnail'] ) : '' );
								?>
",
								"address": "<?php echo( isset( $location['address'] ) ? $location['address'] : '' ); ?>"
							},
							"offers": {
								"url": "<?php echo $event->data->permalink; ?>",
								"price": "<?php echo isset( $event->data->meta['mec_cost'] ) ? $event->data->meta['mec_cost'] : ''; ?>",
								"priceCurrency": "<?php echo isset( $settings['currency'] ) ? $settings['currency'] : ''; ?>"
							},
							"performer": <?php echo $speakers; ?>,
							"description": "<?php echo esc_html( preg_replace( '/<p>\\s*?(<a .*?><img.*?><\\/a>|<img.*?>)?\\s*<\\/p>/s', '<div class="figure">$1</div>', preg_replace( '/\s/u', ' ', $event->data->post->post_content ) ) ); ?>",
							"image": "<?php echo ! empty( $event->data->featured_image['full'] ) ? esc_html( $event->data->featured_image['full'] ) : ''; ?>",
							"name": "<?php esc_html_e( $event->data->title ); ?>",
							"url": "<?php echo $this->main->get_event_date_permalink( $event, $event->date['start']['date'] ); ?>"
						}

					</script>
					<?php endif; ?>

					<article data-style="<?php echo $label_style; ?>"
							class="<?php echo ( isset( $event->data->meta['event_past'] ) and trim( $event->data->meta['event_past'] ) ) ? 'mec-past-event ' : ''; ?>mec-event-article <?php echo $this->get_event_classes( $event ); ?> mec-list-liquid-item <?php echo ! empty( $style ) ? 'mec-list-' . $style : ''; ?> <?php echo ! $display_thumbnail ? 'mec-list-hide-thumbnail' : ''; ?>"
							itemscope>
						<?php echo $display_thumbnail ? \MEC_kses::element( $infowindow_thumb ) : ''; ?>
						<div class="content">
							<div class="ticket-status <?php echo $event_status; ?>"><span><?php echo $event_status_text; ?></span></div>
							<h1 class="mec-event-title">
								<?php
								echo \MEC_kses::element( $this->display_link( $event ) ) . $event_color_dot;
								echo \MEC_kses::element( $this->main->get_flags( $event ) );
								?>
								<?php $options['event_fields']['display_custom_data_fields'] == '1' ? $single->display_data_fields( $event ) : ''; ?>
							</h1>
							<?php if ( trim( $event->data->post->post_content ) ) : ?>
								<p>
									<?php
									$content = explode( ' ', strip_tags( strip_shortcodes( $event->data->post->post_content ) ) );
									$excerpt = implode( ' ', array_slice( $content, 0, 30 ) );
									echo $excerpt;
									?>
								</p>
							<?php endif; ?>
							<div class="action">
								<?php $soldout = $this->main->get_flags( $event->data->ID, $event_start_date ); ?>
								<?php
								$title = ( is_array( $event->data->tickets ) and count( $event->data->tickets ) and ! strpos( $soldout, '%%soldout%%' ) ) 
								    ? $this->main->m( 'register_button', __( 'Register', 'mec-liq' ) ) 
								    : $this->main->m( 'view_detail', __( 'View Detail', 'mec-liq' ) );
								$class = 'mec-booking-button';

								$sed_method = isset($options['list']['sed_method']) ? $options['list']['sed_method'] : '';
								$target_attr = ($sed_method === 'new') ? 'target="_blank"' : '';

								$link_html = '<a href="' . esc_url($this->main->get_event_date_permalink($event, $event->date['start']['date'])) . '" class="' . esc_attr($class) . '" ' . $target_attr . '>' . esc_html($title) . '</a>';

								echo $link_html;
								?>


								<?php if ( isset( $settings['social_network_status'] ) and $settings['social_network_status'] != '0' ) : ?>
									<ul class="mec-event-sharing-wrap">
										<li class="mec-event-share">
											<a href="#" class="mec-event-share-icon">
												<i class="mec-sl-share"></i>
											</a>
										</li>
										<li>
											<ul class="mec-event-sharing">
												<?php echo $this->main->module( 'links.list', array( 'event' => $event ) ); ?>
											</ul>
										</li>
									</ul>
								<?php endif; ?>
							</div>
						</div>
						<div class="details">
							<ul>
								<li>
									<i class="mec-sl-calendar"></i>
									<div>
										<span><?php echo esc_html( $event_start_date ); ?></span>
										<span><?php echo esc_html( date_i18n( 'l', $event->date['start']['timestamp'] ) ); ?></span>
									</div>
								</li>
								<li>
									<i class="mec-sl-clock"></i>
									<div>
										<span><?php echo $start_time; ?></span>
										<span><?php echo $end_time; ?></span>
									</div>
								</li>
								<?php if ( isset( $location['address'] ) && $location['address'] != '' ) { ?>
									<li>
										<i class="mec-sl-location-pin"></i>
										<div>
											<span><?php echo ( isset( $location['address'] ) ? $location['address'] : '' ); ?></span>
										</div>
									</li>
									<?php } ?>
							</ul>
							<?php echo MEC_kses::element( $this->display_organizers( $event ) ); ?>
						</div>
					</article>
				<?php endforeach; ?>
			<?php } ?>
		<?php } ?>
		<?php if ( $this->loadMoreRunning == false && $showLoadMore == false ) { ?>
			<span class="mec-liquid-no-event"><?php esc_html_e( 'No Events', 'mec-liq' ); ?></span>
		<?php } ?>
	</div>

<?php if ( $showLoadMore && $this->load_more_button and $this->found >= $this->limit ) : ?>
	<?php
	$endMonth    = $this->year . '-' . $this->month . '-' . date( 't', strtotime( $this->year . '-' . $this->month ) );
	$maximumDate = $this->maximum_date && ( strtotime( $this->maximum_date ) < strtotime( $endMonth ) ) ? $this->maximum_date : $endMonth;
	?>
	<div class="mec-load-more-wrap">
		<div class="mec-load-more-button" data-end-date="<?php echo esc_attr( $this->end_date ); ?>"
			data-maximum-date="<?php echo esc_attr( $maximumDate ); ?>"
			data-next-offset="<?php echo esc_attr( $this->next_offset ); ?>"
			data-year="<?php echo esc_attr( $this->year ); ?>" data-month="<?php echo esc_attr( $this->month ); ?>"
			onclick=""><?php echo __( 'Load More', 'mec' ); ?></div>
	</div>
<?php endif; ?>

<?php
if ( isset( $this->map_on_top ) and $this->map_on_top ) :
	// Include Map Assets such as JS and CSS libraries
	$this->main->load_map_assets();
	if ( isset( $map_events ) and ! empty( $map_events ) ) {

		// It changing geolocation focus, because after done filtering, if it doesn't. then the map position will not set correctly.
		if ( ( isset( $_REQUEST['action'] ) and $_REQUEST['action'] == 'mec_list_load_more' ) and isset( $_REQUEST['sf'] ) ) {
			$this->geolocation_focus = true;
		}

		$map_javascript = '<script type="text/javascript">
    var mecmap' . $this->id . ';
    jQuery(document).ready(function()
    {
        var jsonPush = gmapSkin(' . json_encode( $this->render->markers( $map_events, $this->style ) ) . ');
        mecmap' . $this->id . ' = jQuery("#mec_googlemap_canvas' . $this->id . '").mecGoogleMaps(
        {
            id: "' . $this->id . '",
            autoinit: false,
            atts: "' . http_build_query( array( 'atts' => $this->atts ), '', '&' ) . '",
            zoom: ' . ( isset( $settings['google_maps_zoomlevel'] ) ? $settings['google_maps_zoomlevel'] : 14 ) . ',
            icon: "' . apply_filters( 'mec_marker_icon', $this->main->asset( 'img/m-04.png' ) ) . '",
            styles: ' . ( ( isset( $settings['google_maps_style'] ) and trim( $settings['google_maps_style'] ) != '' ) ? $this->main->get_googlemap_style( $settings['google_maps_style'] ) : "''" ) . ',
            markers: jsonPush,
            clustering_images: "' . $this->main->asset( 'img/cluster1/m' ) . '",
            getDirection: 0,
            ajax_url: "' . admin_url( 'admin-ajax.php', null ) . '",
            geolocation: "' . $this->geolocation . '",
            geolocation_focus: ' . $this->geolocation_focus . '
        });

        var mecinterval' . $this->id . ' = setInterval(function()
        {
            if(jQuery("#mec_googlemap_canvas' . $this->id . '").is(":visible"))
            {
                mecmap' . $this->id . '.init();
                clearInterval(mecinterval' . $this->id . ');
            };
        }, 1000);
    });
    </script>';

		$map_data              = new stdClass();
		$map_data->id          = $this->id;
		$map_data->atts        = $this->atts;
		$map_data->events      = $map_events;
		$map_data->render      = $this->render;
		$map_data->geolocation = $this->geolocation;
		$map_data->sf_status   = null;
		$map_data->main        = $this->main;

		$map_javascript = apply_filters( 'mec_map_load_script', $map_javascript, $map_data, $settings );

		// Include javascript code into the page
		if ( $this->main->is_ajax() ) {
			echo $map_javascript;
		} else {
			$this->factory->params( 'footer', $map_javascript );
		}
	}
endif;
