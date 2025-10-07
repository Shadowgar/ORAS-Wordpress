<?php

/** no direct access **/
defined( 'MECEXEC' ) or die();

$settings                = $this->main->get_settings();
$days_in_month           = date( 't', mktime( 0, 0, 0, $month, 1, $year ) );
$this->localtime         = isset( $this->skin_options['include_local_time'] ) ? $this->skin_options['include_local_time'] : false;
$map_events              = array();
$showLoadMore            = false;
$display_label           = isset( $this->skin_options['display_label'] ) ? $this->skin_options['display_label'] : false;
$display_cats            = isset( $this->skin_options['display_categories'] ) ? (bool) $this->skin_options['display_categories'] : false;
$reason_for_cancellation = isset( $this->skin_options['reason_for_cancellation'] ) ? $this->skin_options['reason_for_cancellation'] : false;

$style             = $this->style;
$display_thumbnail = isset( $this->skin_options['display_thumbnail'] ) ? (bool) $this->skin_options['display_thumbnail'] : true;
?>
<div class="mec-event-grid-classic">
	<?php
	$count    = $this->count;
	$grid_div = $this->count;

	if ( $count == 0 or $count == 5 ) {
		$col = 4;
	} else {
		$col = 12 / $count;
	}

	$rcount = 1;
	$i      = 0;
	for ( $list_day = 1; $list_day <= $days_in_month; $list_day++ ) {
		$time  = strtotime( $year . '-' . $month . '-' . $list_day );
        $today = date( 'Y-m-d', $time );

		if ( isset( $events[ $today ] ) and count( $events[ $today ] ) ) {
			$showLoadMore = true;
			foreach ( $this->events[ $today ] as $event ) :
				++$i;
				$map_events[]     = $event;
				$link             = $this->main->get_event_date_permalink( $event, ( isset( $event->date['start'] ) ? $event->date['start']['date'] : null ) );
				$infowindow_thumb = trim( $event->data->featured_image['custom'] ) ? '<div class="mec-event-image"><a data-event-id="' . esc_attr( $event->data->ID ) . '" href="' . esc_url( $link ) . '"><img src="' . esc_url( $event->data->featured_image['custom'] ) . '" alt="' . esc_attr( $event->data->title ) . '" width="' . esc_attr( $options['thumbnail_size']['width'] ) . '" height="' . esc_attr( $options['thumbnail_size']['height'] ) . '" /></a></div>' : '';
				$infowindow_thumb = ! $infowindow_thumb && trim( $event->data->featured_image['full'] ) ? '<div class="mec-event-image"><a data-event-id="' . esc_attr( $event->data->ID ) . '" href="' . esc_url( $link ) . '"><img src="' . esc_url( $event->data->featured_image['full'] ) . '" alt="' . esc_attr( $event->data->title ) . '" /></a></div>' : $infowindow_thumb;

				$start_time             = ( isset( $event->data->time ) ? $event->data->time['start'] : '' );
				$end_time               = ( isset( $event->data->time ) ? $event->data->time['end'] : '' );
				$event_color            = isset( $event->data->meta['mec_color'] ) ? '#' . $event->data->meta['mec_color'] : '';
				$event_start_date       = ! empty( $event->date['start']['date'] ) ? $event->date['start']['date'] : '';
				$event_color_dot        = $this->get_event_color_dot( $event );
				$event_start_date       = ! empty( $event->date['start']['date'] ) ? $event->date['start']['date'] : '';
				$event_start_date_day   = ! empty( $event->date['start']['date'] ) ? $this->main->date_i18n( 'd', strtotime( $event->date['start']['date'] ) ) : '';
				$event_start_date_month = ! empty( $event->date['start']['date'] ) ? $this->main->date_i18n( 'M', strtotime( $event->date['start']['date'] ) ) : '';
				$event_start_date_year  = ! empty( $event->date['start']['date'] ) ? $this->main->date_i18n( 'Y', strtotime( $event->date['start']['date'] ) ) : '';

				echo ( $rcount == 1 ) ? '<div class="row">' : '';
				echo '<div class="col-md-' . $col . ' col-sm-' . $col . '">';

				$location  = isset( $event->data->locations[ $event->data->meta['mec_location_id'] ] ) ? $event->data->locations[ $event->data->meta['mec_location_id'] ] : array();
				$organizer = isset( $event->data->organizers[ $event->data->meta['mec_organizer_id'] ] ) ? $event->data->organizers[ $event->data->meta['mec_organizer_id'] ] : array();

				$label_style = '';
				if ( ! empty( $event->data->labels ) ) {
					foreach ( $event->data->labels as $label ) {
						if ( ! isset( $label['style'] ) or ( isset( $label['style'] ) and ! trim( $label['style'] ) ) ) {
							continue;
						}
						if ( $label['style'] == 'mec-label-featured' ) {
							$label_style = esc_html__( 'Featured', 'mec-fl' );
						} elseif ( $label['style'] == 'mec-label-canceled' ) {
							$label_style = esc_html__( 'Canceled', 'mec-fl' );
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
								"name": "<?php echo ( isset( $location['name'] ) ? $location['name'] : '' ); ?>",
								"image": "
								<?php
								echo ( isset( $location['thumbnail'] ) ? esc_url( $location['thumbnail'] ) : '' );
								?>
								",
								"address": "<?php echo ( isset( $location['address'] ) ? $location['address'] : '' ); ?>"
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
					<?php
				endif;
				echo '<article data-style="' . $label_style . '" class="' . ( ( isset( $event->data->meta['event_past'] ) and trim( $event->data->meta['event_past'] ) ) ? 'mec-past-event' : '' ) . ' mec-event-article mec-clear ' . $this->get_event_classes( $event ) . ' item-' . $style . ' "  itemscope>';
				?>
				<?php echo $display_thumbnail ? \MEC_kses::element( $infowindow_thumb ) : ''; ?>
				<div class="mec-event-details">
					<div class="mec-liquid-event-date mec-event-date">
						<i class="mec-sl-calendar"></i>
						<span class="mec-liquid-day"><?php echo esc_html( $event_start_date_day ); ?></span>
						<span class="mec-liquid-month"><?php echo esc_html( $event_start_date_month ); ?></span>,
						<span class="mec-liquid-year"><?php echo esc_html( $event_start_date_year ); ?></span>
						<?php if ( $style != 'liquid-small' ) { ?>
						<div><?php echo esc_html( date_i18n( 'l', $event->date['start']['timestamp'] ) ); ?></div>
						<?php } ?>
					</div>
					<div class="mec-liquid-event-time">
						<i class="mec-sl-clock"></i>
						<span class="mec-liquid-start"><?php echo esc_html( $start_time ); ?></span>
						<?php if ( $style != 'liquid-small' ) { ?>
						<div class="mec-liquid-end"><?php echo esc_html( $end_time ); ?></div>
						<?php } ?>
					</div>
				</div>
				<div class="mec-event-content">
					<h4 class="mec-event-title">
					<?php
						$title = null;
						$class = 'mec-color-hover';
						echo $this->display_link( $event, $title, $class ) . $event_color_dot;
						echo $this->display_custom_data( $event );
						echo $this->main->get_normal_labels( $event, $display_label ) . $this->main->display_cancellation_reason( $event, $reason_for_cancellation );
					?>
					</h4>
					<?php if ( $style == 'liquid-large' ) { ?>
						<?php if ( trim( $event->data->post->post_content ) ) : ?>
							<p>
								<?php echo $event->data->post->post_content; ?>
							</p>
						<?php endif; ?>
					<?php } ?>
					<?php
					if ( ! empty( $label_style ) ) {
						echo '<span class="mec-fc-style">' . $label_style . '</span>';}
					?>
					<?php
					if ( $this->localtime ) {
						echo $this->main->module( 'local-time.type2', array( 'event' => $event ) );}
					?>
					<?php if ( isset( $location['address'] ) and trim( $location['address'] ) ) : ?>
						<div class="mec-event-location">
							<i class="mec-sl-location-pin"></i>
							<address class="mec-events-address"><span class="mec-address"><?php echo ( isset( $location['address'] ) ? $location['address'] : '' ); ?></span></address>
						</div>
					<?php endif; ?>
					<?php if ( $display_cats ) : ?>
						<?php if ( ! empty( $this->display_categories( $event ) ) ) : ?>
						<div class="mec-list-cats-details">
							<i class="mec-sl-folder"></i>
							<?php echo $this->display_categories( $event ); ?>
						</div>
						<?php endif; ?>
					<?php endif; ?>
				</div>
				</article>
</div>

				<?php
				if ( $rcount == $count ) {
					echo '</div>';
					$rcount = 0;
				}

				++$rcount;
			endforeach;
		}
	}
	if ( ( $i % $count ) != 0 ) {
		echo '</div>';
	}
	?>
<?php if ( $this->loadMoreRunning == false && $showLoadMore == false ) { ?>
	<span class="mec-fluent-no-event"><?php esc_html_e( 'No Events', 'mec-fl' ); ?></span>
<?php } ?>
</div>

<?php if ( $showLoadMore && $this->load_more_button and $this->found >= $this->limit ) : ?>
	<?php
	$endMonth    = $this->year . '-' . $this->month . '-' . date( 't', strtotime( $this->year . '-' . $this->month ) );
	$maximumDate = $this->maximum_date && ( strtotime( $this->maximum_date ) < strtotime( $endMonth ) ) ? $this->maximum_date : $endMonth;
	?>
	<div class="mec-load-more-wrap">
		<div class="mec-load-more-button" data-end-date="<?php echo esc_attr( $this->end_date ); ?>" data-maximum-date="<?php echo esc_attr( $maximumDate ); ?>" data-next-offset="<?php echo esc_attr( $this->next_offset ); ?>" data-year="<?php echo esc_attr( $this->year ); ?>" data-month="<?php echo esc_attr( $this->month ); ?>" onclick=""><?php echo __( 'Load More', 'mec' ); ?></div>
	</div>
<?php endif; ?>

<?php
if ( isset( $this->map_on_top ) and $this->map_on_top ) :

	// Include Map Assets such as JS and CSS libraries
	$this->main->load_map_assets();
	if ( isset( $map_events ) and ! empty( $map_events ) ) {

		// It changing geolocation focus, because after done filtering, if it doesn't. then the map position will not set correctly.
		if ( ( isset( $_REQUEST['action'] ) and $_REQUEST['action'] == 'mec_grid_load_more' ) and isset( $_REQUEST['sf'] ) ) {
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
            geolocation_focus: ' . $this->geolocation_focus . ',
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
