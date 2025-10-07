<?php

namespace MEC_Liquid\Core\pluginBase;

// don't load directly.
use DateTime;
use MEC\Base;
use stdClass;
use WP_Query;

if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/**
 * MecLiquid.
 *
 * @author      Webnus
 * @package     MEC_Liquid
 * @since       1.0.0
 */
class MecLiquid {

	public static $shortcode_id;
	/**
	 * Instance of this class.
	 *
	 * @since   1.0.0
	 * @access  public
	 * @var     MEC_Liquid
	 */
	public static $instance;

	/**
	 * The directory of the file.
	 *
	 * @access  public
	 * @var     string
	 */
	public static $dir;

	/**
	 * The Args
	 *
	 * @access  public
	 * @var     array
	 */
	public static $args;

	/**
	 * Provides access to a single instance of a module using the singleton pattern.
	 *
	 * @return  object
	 * @since   1.0.0
	 */
	public static function instance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
		self::setHooks( $this );
		self::init();
	}

	/**
	 * Set Hooks.
	 *
	 * @since   1.0.0
	 */
	public static function setHooks( $This ) {

		add_shortcode( 'return_post_id', 'the_dramatist_return_post_id' );

		// List
		add_action( 'mec_list_skin_style_options', array( $This, 'liquidStyleOptionList' ), 99, 1 );
		add_action( 'mec-list-initialize-end', array( $This, 'skin_initialize' ), 1, 1 );
		add_action( 'mec_skin_options_list_end', array( $This, 'listSkinOptions' ), 1, 1 );

		// Grid
		add_action( 'mec_grid_skin_style_options', array( $This, 'liquidStyleOptionGrid' ), 99, 1 );
		add_action( 'mec-grid-initialize-end', array( $This, 'skin_initialize' ), 1, 1 );
		add_action( 'mec_skin_options_grid_end', array( $This, 'gridSkinOptions' ), 1, 1 );

		// Daily
		add_action( 'mec_daily_view_skin_style_options', array( $This, 'StyleOption' ), 1, 1 );
		add_action( 'mec_skin_options_daily_view_end', array( $This, 'dailySkinOptions' ), 1, 1 );

		// General Calendar
		// add_action('mec-general-calendar-initialize-end', [$This, 'generalCalendarInitialize'], 1, 1);
		// add_action('mec_skin_options_general_calendar_init', [$This, 'generalCalendarSkinOptions'], 1, 1);

		// Search
		add_action( 'wp_ajax_search_liquid', array( $This, 'searchLiquid' ) );

		// Date Filter
		add_action( 'wp_ajax_date_filter_liquid', array( $This, 'dateFilterLiquid' ) );

		// Load Skin View
		add_filter( 'mec_get_skin_tpl_path', array( $This, 'tplPath' ), 99, 2 );

		// Single style settings
		add_action( 'mec_single_style', array( $This, 'singleSettings' ), 1, 2 );

		add_filter( 'mec_locolize_data', array( $This, 'locolizeData' ), 99, 1 );

		// Cover
		add_action( 'mec_cover_view_skin_style_options', array( $This, 'StyleOptionMultiple' ), 99, 2 );
		add_action( 'mec_skin_options_cover_end', array( $This, 'coverEndSkinOptions' ), 1, 1 );

		// Slider
		add_action( 'mec_slider_fluent', array( $This, 'StyleOption' ), 99, 1 );
		add_action( 'mec-slider-initialize-end', array( $This, 'sliderInitialize' ), 1, 1 );
		add_action( 'mec_skin_options_slider_end', array( $This, 'sliderSkinOptions' ), 1, 1 );

		// Carousel
		add_action( 'mec_carousel_fluent', array( $This, 'StyleOption' ), 99, 1 );
		add_action( 'mec-carousel-initialize-end', array( $This, 'carouselInitialize' ), 1, 1 );
		add_action( 'mec_skin_options_carousel_end', array( $This, 'carouselSkinOptions' ), 1, 1 );

		// Available Spot
		add_action( 'mec_available_spot_skin_style_options', array( $This, 'StyleOption' ), 99, 1 );
		add_action( 'mec_skin_options_available_spot_init', array( $This, 'availableSpotSkinOptions' ), 1, 1 );
		add_action( 'mec-available-spot-initialize-end', array( $This, 'skin_initialize' ), 1, 1 );
		add_action( 'mec_skin_options_available_spot_end', array( $This, 'availableSpotEndSkinOptions' ), 1, 1 );

		// Available Spot
		add_action( 'mec_map_skin_style_options', array( $This, 'StyleOption' ), 99, 1 );
		add_action( 'mec_skin_options_map_init', array( $This, 'mapSkinOptions' ), 1, 1 );
		add_action( 'mec-map-initialize-end', array( $This, 'mapInitialize' ), 1, 1 );

		// Weekly
		add_action( 'mec_weekly_view_skin_style_options', array( $This, 'StyleOption' ), 1, 1 );
		add_action( 'mec_skin_options_weekly_view_end', array( $This, 'weeklyViewSkinOptions' ), 1, 1 );

		// Full Calendar
		add_action( 'mec_full_calendar_skin_style_options', array( $This, 'StyleOption' ), 1, 1 );
		add_action( 'mec-full-calendar-initialize-end', array( $This, 'fullCalendarInitialize' ), 1, 1 );
		add_filter( 'mec-full-calendar-load-skin-yearly', array( $This, 'fullCalendarLoadSkin' ), 99, 3 );
		add_filter( 'mec-full-calendar-load-skin-monthly', array( $This, 'fullCalendarLoadSkin' ), 99, 3 );
		add_filter( 'mec-full-calendar-load-skin-weekly', array( $This, 'fullCalendarLoadSkin' ), 99, 3 );
		add_filter( 'mec-full-calendar-load-skin-daily', array( $This, 'fullCalendarLoadSkin' ), 99, 3 );
		add_filter( 'mec-full-calendar-load-skin-list', array( $This, 'fullCalendarLoadSkin' ), 99, 3 );
		add_filter( 'mec-full-calendar-load-skin-grid', array( $This, 'fullCalendarLoadSkin' ), 99, 3 );
		add_filter( 'mec-full-calendar-load-skin-tile', array( $This, 'fullCalendarLoadSkin' ), 99, 3 );
		add_action( 'mec_skin_options_full_calendar_end', array( $This, 'fullCalendarEndSkinOptions' ), 1, 1 );
		add_action( 'mec_skin_options', array( $This, 'customOptions' ), 1, 1 );

		add_filter( 'mec_get_marker_lightbox', array( __CLASS__, 'filter_get_marker_lightbox' ), 10, 4 );

		add_action(
			'mec_start_skin',
			function ( $id ) {
				\MEC_Liquid\Core\pluginBase\MecLiquid::$args = $id;
			},
			1,
			1
		);

		add_filter( 'mec_shortcode_builder_style_options', array( __CLASS__, 'filter_shortcode_builder_style_options' ), 10, 2 );
		// $sk_options['thumbnail_size']['width']
		add_filter( 'mec-render-data-featured-image', array( __CLASS__, 'filter_render_data_featured_image' ), 10, 2 );
	}

	public static function filter_render_data_featured_image( $images, $event_id ) {
		global $MEC_Shortcode_id;

		$options = get_post_meta( $MEC_Shortcode_id, 'sk-options', true );
		if ( isset( $options['thumbnail_size']['width'] ) ) {
			$width  = $options['thumbnail_size']['width'] ? $options['thumbnail_size']['width'] : 0;
			$height = $options['thumbnail_size']['height'] ? $options['thumbnail_size']['height'] : 0;

			if ( $width > 0 && $height > 0 ) {
				$image_id         = get_post_thumbnail_id( $event_id );
				$image            = wp_get_attachment_image_src( $image_id, array( $width, $height ), true );
				$images['custom'] = $image[0];
			}
		}

		return $images;
	}

	function the_dramatist_return_post_id() {
		self::$shortcode_id = get_the_ID();
	}

	/**
	 * General Calendar Skin Options
	 *
	 * @since 1.0.0
	 */
	public function generalCalendarSkinOptions( $sk_options_general_calendar_view ) {
		?>
		<div class="mec-form-row">
			<label class="mec-col-4" for="mec_skin_general_calendar_style"><?php _e( 'Style', 'mec-liq' ); ?></label>
			<select class="mec-col-4 wn-mec-select" name="mec[sk-options][general_calendar][style]"
					id="mec_skin_general_calendar_style"
					onchange="mec_skin_style_changed('general_calendar', this.value);">
				<option value="classic"
				<?php
				if ( isset( $sk_options_general_calendar_view['style'] ) and $sk_options_general_calendar_view['style'] == 'classic' ) {
					echo 'selected="selected"';
				}
				?>
				><?php _e( 'Classic', 'mec-liq' ); ?></option>
				<option value="liquid"
				<?php
				if ( isset( $sk_options_general_calendar_view['style'] ) and $sk_options_general_calendar_view['style'] == 'liquid' ) {
					echo 'selected="selected"';
				}
				?>
				><?php _e( 'Liquid', 'mec-liq' ); ?></option>
			</select>
		</div>
		<?php
	}

	/**
	 * General Calendar Initialize Method
	 *
	 * @since 1.0.0
	 */
	public function generalCalendarInitialize( $This ) {
		$This->style = isset( $This->skin_options['style'] ) ? $This->skin_options['style'] : 'classic';
	}

	/**
	 * Add Liquid List Style to Skin Options
	 *
	 * @since 1.0.0
	 */
	public function liquidStyleOptionList( $setting ) {

		?>
		<option value="liquid-large" <?php selected( $setting, 'liquid-large' ); ?> ><?php _e( 'Liquid Large', 'mec-liq' ); ?></option>
		<option value="liquid-medium" <?php selected( $setting, 'liquid-medium' ); ?> ><?php _e( 'Liquid Medium', 'mec-liq' ); ?></option>
		<option value="liquid-small" <?php selected( $setting, 'liquid-small' ); ?> ><?php _e( 'Liquid Small', 'mec-liq' ); ?></option>
		<option value="liquid-minimal" <?php selected( $setting, 'liquid-minimal' ); ?> ><?php _e( 'Liquid Minimal', 'mec-liq' ); ?></option>
		<?php
	}

	/**
	 * Add Liquid Grid Style to Skin Options
	 *
	 * @since 1.0.0
	 */
	public function liquidStyleOptionGrid( $setting ) {
		?>
		<option value="liquid-large" <?php selected( $setting, 'liquid-large' ); ?> ><?php _e( 'Liquid Large', 'mec-liq' ); ?></option>
		<option value="liquid-medium" <?php selected( $setting, 'liquid-medium' ); ?> ><?php _e( 'Liquid Medium', 'mec-liq' ); ?></option>
		<option value="liquid-small" <?php selected( $setting, 'liquid-small' ); ?> ><?php _e( 'Liquid Small', 'mec-liq' ); ?></option>
		<?php
	}


	public function searchLiquid() {
		$text      = $_POST['text'];
		$type      = $_POST['type'];
		$category  = $_POST['category'];
		$location  = $_POST['location'];
		$organizer = $_POST['organizer'];
		$speaker   = $_POST['speaker'];
		$tag       = $_POST['tag'];
		$label     = $_POST['label'];
		$order     = $_POST['order'];

		$category   = $_POST['fo_category'] == '' ? explode( ',', $category ) : explode( ',', $_POST['fo_category'] );
		$location   = $_POST['fo_locations'] == '' ? $location : explode( ',', $_POST['fo_locations'] );
		$organizer  = $_POST['fo_organizer'] == '' ? $organizer : explode( ',', $_POST['fo_organizer'] );
		$label      = $_POST['fo_label'] == '' ? $label : explode( ',', $_POST['fo_label'] );
		$tag        = $_POST['fo_tag'] == '' ? $tag : explode( ',', $_POST['fo_tag'] );
		$author     = explode( ',', $_POST['fo_author'] );
		$occurrence = $_POST['fo_Occurrence'];

		if ( $text != '' && $text != null ) {
			if ( $type === 'all' ) {
				$args = array(
					'post_type'      => 'mec-events',
					's'              => $text,
					'order'          => $order,
					'posts_per_page' => -1,
				);
			} else {
				$args = array(
					'post_type'      => 'mec-events',
					's'              => $text,
					'order'          => $order,
					'posts_per_page' => -1,
					'meta_query'     => array(
						array(
							'key'     => 'mec_event_status',
							'value'   => $type,
							'compare' => '=',
						),
					),
				);
			}
		} elseif ( $type === 'all' ) {
				$args = array(
					'post_type'      => 'mec-events',
					'order'          => $order,
					'posts_per_page' => -1,
				);
		} else {
			$args = array(
				'post_type'      => 'mec-events',
				'order'          => $order,
				'posts_per_page' => -1,
				'meta_query'     => array(
					array(
						'key'     => 'mec_event_status',
						'value'   => $type,
						'compare' => '=',
					),
				),
			);
		}
		$events     = get_posts( $args );
		$list_event = array();

		$i = 0;
		if ( $_POST['fo_category'] != '' || $_POST['category'] != '-1' || $location != '-1' || $organizer != '-1' || $speaker != '-1' || $tag != '-1' || $label != '-1' ) {
			foreach ( $events as $event ) {
				if ( $_POST['category'] != '-1' || $_POST['fo_category'] != '' ) {
					$post_categories = get_the_terms( $event->ID, 'mec_category' );
					if ( is_array( $post_categories ) ) {
						foreach ( $post_categories as $post_category ) {
							if ( in_array( $post_category->term_id, $category ) ) {
								if ( ! in_array( $event, $list_event ) ) {
									$list_event[ $i ] = $event;
									++$i;
								}
							}
						}
					}
				}

				if ( $location != '-1' ) {
					$post_locations = get_the_terms( $event->ID, 'mec_location' );
					if ( is_array( $post_locations ) ) {
						foreach ( $post_locations as $post_location ) {
							if ( is_array( $location ) ) {
								if ( in_array( $post_location->term_id, $location ) ) {
									if ( ! in_array( $event, $list_event ) ) {
										$list_event[ $i ] = $event;
										++$i;
									}
								}
							} elseif ( $post_location->term_id == $location ) {
								if ( ! in_array( $event, $list_event ) ) {
									$list_event[ $i ] = $event;
									++$i;
								}
							}
						}
					}
				}

				if ( $organizer != '-1' ) {
					$post_organizers = get_the_terms( $event->ID, 'mec_organizer' );
					if ( is_array( $post_organizers ) ) {
						foreach ( $post_organizers as $post_organizer ) {
							if ( is_array( $organizer ) ) {
								if ( in_array( $post_organizer->term_id, $organizer ) ) {
									if ( ! in_array( $event, $list_event ) ) {
										$list_event[ $i ] = $event;
										++$i;
									}
								}
							} elseif ( $post_organizer->term_id == $organizer ) {
								if ( ! in_array( $event, $list_event ) ) {
									$list_event[ $i ] = $event;
									++$i;
								}
							}
						}
					}
				}

				if ( $speaker != '-1' ) {
					$post_speakers = get_the_terms( $event->ID, 'mec_speaker' );
					if ( is_array( $post_speakers ) ) {
						foreach ( $post_speakers as $post_speaker ) {
							if ( $post_speaker->term_id == $speaker ) {
								if ( ! in_array( $event, $list_event ) ) {
									$list_event[ $i ] = $event;
									++$i;
								}
							}
						}
					}
				}

				if ( $tag != '-1' ) {
					$post_tags = get_the_terms( $event->ID, apply_filters( 'mec_taxonomy_tag', '' ) );
					if ( is_array( $post_tags ) ) {
						foreach ( $post_tags as $post_tag ) {
							if ( is_array( $tag ) ) {
								if ( in_array( $post_tag->name, $tag ) ) {
									if ( ! in_array( $event, $list_event ) ) {
										$list_event[ $i ] = $event;
										++$i;
									}
								}
							} elseif ( $post_tag->term_id == $tag ) {
								if ( ! in_array( $event, $list_event ) ) {
									$list_event[ $i ] = $event;
									++$i;
								}
							}
						}
					}
				}

				if ( $label != '-1' ) {
					$post_labels = get_the_terms( $event->ID, 'mec_label' );
					if ( is_array( $post_labels ) ) {
						foreach ( $post_labels as $post_label ) {
							if ( is_array( $label ) ) {
								if ( in_array( $post_label->term_id, $label ) ) {
									if ( ! in_array( $event, $list_event ) ) {
										$list_event[ $i ] = $event;
										++$i;
									}
								}
							} elseif ( $post_label->term_id == $label ) {
								if ( ! in_array( $event, $list_event ) ) {
									$list_event[ $i ] = $event;
									++$i;
								}
							}
						}
					}
				}

				if ( $_POST['fo_author'] != '' ) {
					if ( in_array( $event->post_author, $author ) ) {
						if ( ! in_array( $event, $list_event ) ) {
							$list_event[ $i ] = $event;
							++$i;
						}
					}
				}
			}
		} else {
			$list_event = $events;
		}

		$result = array();
		foreach ( $list_event as $event ) {
			$id                 = $event->ID;
			$event_main         = new \MEC\Events\Event( $id );
			$event_main_details = $event_main->get_detail();
			$main               = \MEC::getInstance( 'app.libraries.main' );
			$render             = \MEC::getInstance( 'app.libraries.render' );
			$settings           = $main->get_settings();
			$event_data         = $render->data( $id );

			$item    = array();
			$image   = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'single-post-thumbnail' );
			$content = get_the_content( '', false, $id );
			$content = wpautop( $content );
			$content = do_shortcode( $content );

			$status = $event_data->meta['mec_event_status'] ?? '';
			switch ( $status ) {
				case 'EventScheduled':
					$status = __( 'Scheduled', 'mec-liq' );
					break;
				case 'EventPostponed':
					$status = __( 'Postponed', 'mec-liq' );
					break;
				case 'EventCancelled':
					$status = __( 'Cancelled', 'mec-liq' );
					break;
				case 'EventMovedOnline':
					$status = __( 'Moved', 'mec-liq' );
					break;
			}

			$dates        = $render->dates( $id, null, 1, null );
			$date_start   = isset( $dates[0] ) ? $dates[0] : '';
			$date_weekday = is_array( $date_start ) ? date_i18n( 'l', $date_start['start']['timestamp'] ) : '&nbsp;';
			$date_start   = is_array( $date_start ) ? date( 'd F, Y', $date_start['start']['timestamp'] ) : $date_start;

			$socials         = Base::get_main()->get_social_networks();
			$share_links     = '';
			$social_networks = isset( $settings['sn'] ) && is_array( $settings['sn'] ) ? $settings['sn'] : array();
			foreach ( $socials as $social ) {
				$social_id  = $social['id'];
				$is_enabled = isset( $social_networks[ $social_id ] ) && ! $social_networks[ $social_id ];
				if ( $is_enabled ) {
					continue;
				}
				if ( is_callable( $social['function'] ) ) {
					$share_links .= call_user_func( $social['function'], $event_data->permalink, $event_main_details );
				}
			}

			$item['id']               = $id;
			$item['title']            = $event->post_title;
			$item['content']          = $content;
			$item['img']              = $image[0];
			$item['color']            = isset( $event_data->meta['mec_color'] ) ? '#' . $event_data->meta['mec_color'] : '';
			$item['status']           = $status;
			$item['url']              = $event_data->permalink;
			$item['share']            = $share_links;
			$item['register']         = $event_data->meta['mec_more_info'];
			$item['start_date']       = $date_start;
			$item['event_date_start'] = date( 'Y-m-d', $dates[0]['start']['timestamp'] );
			$item['event_date_end']   = date( 'Y-m-d', $dates[0]['end']['timestamp'] );
			$item['time1']            = date( 'H:i:s', strtotime( "{$dates[0]['start']['hour']}:{$dates[0]['start']['minutes']}:00 {$dates[0]['start']['ampm']}" ) );
			$item['time2']            = date( 'H:i:s', strtotime( "{$dates[0]['end']['hour']}:{$dates[0]['end']['minutes']}:00 {$dates[0]['end']['ampm']}" ) );
			$item['weekday']          = $date_weekday;
			if ( isset( $event_data->locations ) ) {
				foreach ( $event_data->locations as $location ) {
					$item['address']      = $location['address'];
					$item['address_name'] = $location['name'];
					break;
				}
			} else {
				$item['address']      = '&nbsp;';
				$item['address_name'] = '&nbsp;';
			}
			$item['start_time'] = isset( $event_data->time ) ? $event_data->time['start'] : '';
			if ( $item['start_time'] === 'All Day' ) {
				$item['end_time'] = '&nbsp;';
			} else {
				$item['end_time'] = ( isset( $event_data->time ) ? $event_data->time['end'] : '' );
			}

			$item['labels']       = get_the_terms( $id, 'mec_label' );
			$item['categories']   = get_the_terms( $id, 'mec_category' );
			$item['cancellation'] = get_post_meta( $id, 'mec_cancelled_reason', true );

			$result[] = $item;
		}

		echo json_encode(
			array(
				'success' => 1,
				'result'  => $result,
			)
		);
		exit;
	}

	public function dateFilterLiquid() {

		$list_event = array();

		$type_view  = $_POST['type_view'];
		$category   = $_POST['fo_category'] == '' ? '-1' : explode( ',', $_POST['fo_category'] );
		$location   = $_POST['fo_locations'] == '' ? '-1' : explode( ',', $_POST['fo_locations'] );
		$organizer  = $_POST['fo_organizer'] == '' ? '-1' : explode( ',', $_POST['fo_organizer'] );
		$label      = $_POST['fo_label'] == '' ? '-1' : explode( ',', $_POST['fo_label'] );
		$tag        = $_POST['fo_tag'] == '' ? '-1' : explode( ',', $_POST['fo_tag'] );
		$author     = explode( ',', $_POST['fo_author'] );
		$occurrence = $_POST['fo_Occurrence'];

		$type = $_POST['type'];

		if ( $type == 'day' ) {
			$date_selected = $_POST['date'];
			$list_event    = $this->getListEventByDate( $date_selected );
		} elseif ( $type == 'week' ) {
			$year      = $_POST['year'];
			$month     = $_POST['month'];
			$start_day = intval( $_POST['startDay'] );
			$last_day  = intval( $_POST['lastDay'] );
			for ( $i = $start_day; $i <= $last_day; $i++ ) {
				$list       = $this->getListEventByDate( date( 'Y-m-d', strtotime( $year . '-' . $month . '-' . $i ) ) );
				$list_event = array_merge( $list_event, $list );

			}
		} elseif ( $type == 'month' ) {
			$year     = $_POST['year'];
			$month    = $_POST['month'];
			$last_day = intval( $_POST['lastDay'] );
			for ( $i = 1; $i <= $last_day; $i++ ) {
				$list       = $this->getListEventByDate( date( 'Y-m-d', strtotime( $year . '-' . $month . '-' . $i ) ) );
				$list_event = array_merge( $list_event, $list );

			}
		} else {
			$list_event = array();
		}

		$result = array();
		foreach ( $list_event as $key_event => $event ) {
			$id                 = $event['ID'];
			$event_main         = new \MEC\Events\Event( $id );
			$event_main_details = $event_main->get_detail();
			$main               = \MEC::getInstance( 'app.libraries.main' );
			$render             = \MEC::getInstance( 'app.libraries.render' );
			$settings           = $main->get_settings();
			$event_data         = $render->data( $id );

			if ( $category != '-1' || $location != '-1' || $organizer != '-1' || $tag != '-1' || $label != '-1' ) {
				$is_add = false;

				if ( $category != '-1' ) {
					$post_categories = get_the_terms( $id, 'mec_category' );
					if ( is_array( $post_categories ) ) {
						foreach ( $post_categories as $post_category ) {
							if ( in_array( $post_category->term_id, $category ) ) {
								$is_add = true;
							}
						}
					}
				}

				if ( $location != '-1' && ! $is_add ) {
					$post_locations = get_the_terms( $id, 'mec_location' );
					if ( is_array( $post_locations ) ) {
						foreach ( $post_locations as $post_location ) {
							if ( is_array( $location ) ) {
								if ( in_array( $post_location->term_id, $location ) ) {
									$is_add = true;
								}
							}
						}
					}
				}

				if ( $organizer != '-1' && ! $is_add ) {
					$post_organizers = get_the_terms( $id, 'mec_organizer' );
					if ( is_array( $post_organizers ) ) {
						foreach ( $post_organizers as $post_organizer ) {
							if ( is_array( $organizer ) ) {
								if ( in_array( $post_organizer->term_id, $organizer ) ) {
									$is_add = true;
								}
							}
						}
					}
				}

				if ( $tag != '-1' && ! $is_add ) {
					$post_tags = get_the_terms( $id, apply_filters( 'mec_taxonomy_tag', '' ) );
					if ( is_array( $post_tags ) ) {
						foreach ( $post_tags as $post_tag ) {
							if ( is_array( $tag ) ) {
								if ( in_array( $post_tag->name, $tag ) ) {
									$is_add = true;
								}
							}
						}
					}
				}

				if ( $label != '-1' && ! $is_add ) {
					$post_labels = get_the_terms( $id, 'mec_label' );
					if ( is_array( $post_labels ) ) {
						foreach ( $post_labels as $post_label ) {
							if ( is_array( $label ) ) {
								if ( in_array( $post_label->term_id, $label ) ) {
									$is_add = true;
								}
							}
						}
					}
				}

				if ( $_POST['fo_author'] != '' && ! $is_add ) {
					if ( in_array( get_post_field( 'post_author', $id ), $author ) ) {
						$is_add = true;
					}
				}

				if ( $is_add ) {
					$is_add = false;

					$item    = array();
					$image   = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'single-post-thumbnail' );
					$content = get_the_content( '', false, $id );
					$content = wpautop( $content );
					$content = do_shortcode( $content );

					$status = $event_data->meta['mec_event_status'];
					switch ( $status ) {
						case 'EventScheduled':
							$status = __( 'Scheduled', 'mec-liq' );
							break;
						case 'EventPostponed':
							$status = __( 'Postponed', 'mec-liq' );
							break;
						case 'EventCancelled':
							$status = __( 'Cancelled', 'mec-liq' );
							break;
						case 'EventMovedOnline':
							$status = __( 'Moved', 'mec-liq' );
							break;
					}

					$dates        = $render->dates( $id, null, 1, null );
					$date_start   = isset( $dates[0] ) ? $dates[0] : '';
					$date_end     = get_post_meta( $id, 'mec_end_date', true );
					$date_weekday = is_array( $date_start ) ? date_i18n( 'l', strtotime( $event['date'] ) ) : '&nbsp;';
					$date_start   = is_array( $date_start ) ? date( 'd F, Y', strtotime( $event['date'] ) ) : $event['date'];

					$socials         = Base::get_main()->get_social_networks();
					$share_links     = '';
					$social_networks = isset( $settings['sn'] ) && is_array( $settings['sn'] ) ? $settings['sn'] : array();
					foreach ( $socials as $social ) {
						$social_id  = $social['id'];
						$is_enabled = isset( $social_networks[ $social_id ] ) && ! $social_networks[ $social_id ];
						if ( $is_enabled ) {
							continue;
						}
						if ( is_callable( $social['function'] ) ) {
							$share_links .= call_user_func( $social['function'], $event_data->permalink, $event_main_details );
						}
					}

					$item['id']               = $id;
					$item['title']            = $event['post_title'];
					$item['content']          = $content;
					$item['img']              = $image[0];
					$item['color']            = isset( $event_data->meta['mec_color'] ) ? '#' . $event_data->meta['mec_color'] : '';
					$item['status']           = $status;
					$item['url']              = $event_data->permalink;
					$item['share']            = $share_links;
					$item['register']         = $event_data->meta['mec_more_info'];
					$item['start_date']       = $date_start;
					$item['event_date_start'] = $event['date'];

					if ( $event['repeat'] == 1 ) {
						$item['event_date_end'] = $event['date'];
					} else {
						if ( $dates[0]['start']['date'] == $event['date'] ) {
							$item['continuation_date'] = 0;
							$item['event_date_end']    = date( 'Y-m-d', strtotime( $date_end . ' + 1 day' ) );
						} elseif ( strtotime( $dates[0]['start']['date'] ) < strtotime( $event['date'] ) && strtotime( $date_end ) >= strtotime( $event['date'] ) ) {
							$item['continuation_date'] = 1;
							$item['event_date_end']    = $event['date'];
						} else {
							$item['event_date_end'] = date( 'Y-m-d', strtotime( $date_end . ' + 1 day' ) );
						}
						//                    $date1 = new DateTime($dates[0]['start']['date']);
						//                    $date2 = new DateTime($dates[0]['end']['date']);
						//                    $interval = $date1->diff($date2);
						//                    $item['event_date_end'] = date('Y-m-d', strtotime($event['date'] . ' + ' . $interval->days . ' days'));
					}

					$item['time1']   = date( 'H:i:s', strtotime( "{$dates[0]['start']['hour']}:{$dates[0]['start']['minutes']}:00 {$dates[0]['start']['ampm']}" ) );
					$item['time2']   = date( 'H:i:s', strtotime( "{$dates[0]['end']['hour']}:{$dates[0]['end']['minutes']}:00 {$dates[0]['end']['ampm']}" ) );
					$item['weekday'] = $date_weekday;
					if ( isset( $event_data->locations ) ) {
						foreach ( $event_data->locations as $location ) {
							$item['address']      = $location['address'];
							$item['address_name'] = $location['name'];
							break;
						}
					} else {
						$item['address']      = '&nbsp;';
						$item['address_name'] = '&nbsp;';
					}
					$item['start_time'] = isset( $event_data->time ) ? $event_data->time['start'] : '';
					if ( $item['start_time'] === 'All Day' ) {
						$item['end_time'] = '&nbsp;';
					} else {
						$item['end_time'] = ( isset( $event_data->time ) ? $event_data->time['end'] : '' );
					}

					$item['labels']       = get_the_terms( $id, 'mec_label' );
					$item['categories']   = get_the_terms( $id, 'mec_category' );
					$item['cancellation'] = get_post_meta( $id, 'mec_cancelled_reason', true );

					$result[] = $item;
				}
			} else {
				$item    = array();
				$image   = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'single-post-thumbnail' );
				$content = get_the_content( '', false, $id );
				$content = wpautop( $content );
				$content = do_shortcode( $content );

				$status = $event_data->meta['mec_event_status'];
				switch ( $status ) {
					case 'EventScheduled':
						$status = __( 'Scheduled', 'mec-liq' );
						break;
					case 'EventPostponed':
						$status = __( 'Postponed', 'mec-liq' );
						break;
					case 'EventCancelled':
						$status = __( 'Cancelled', 'mec-liq' );
						break;
					case 'EventMovedOnline':
						$status = __( 'Moved', 'mec-liq' );
						break;
				}

				$dates        = $render->dates( $id, null, 1, null );
				$date_end     = get_post_meta( $id, 'mec_end_date', true );
				$date_start   = isset( $dates[0] ) ? $dates[0] : '';
				$date_weekday = is_array( $date_start ) ? date_i18n( 'l', strtotime( $event['date'] ) ) : '&nbsp;';
				$date_start   = is_array( $date_start ) ? date( 'd F, Y', strtotime( $event['date'] ) ) : $event['date'];

				$socials         = Base::get_main()->get_social_networks();
				$share_links     = '';
				$social_networks = isset( $settings['sn'] ) && is_array( $settings['sn'] ) ? $settings['sn'] : array();
				foreach ( $socials as $social ) {
					$social_id  = $social['id'];
					$is_enabled = isset( $social_networks[ $social_id ] ) && ! $social_networks[ $social_id ];
					if ( $is_enabled ) {
						continue;
					}
					if ( is_callable( $social['function'] ) ) {
						$share_links .= call_user_func( $social['function'], $event_data->permalink, $event_main_details );
					}
				}

				$item['id']               = $id;
				$item['title']            = $event['post_title'];
				$item['content']          = $content;
				$item['img']              = $image[0];
				$item['color']            = isset( $event_data->meta['mec_color'] ) ? '#' . $event_data->meta['mec_color'] : '';
				$item['status']           = $status;
				$item['url']              = $event_data->permalink;
				$item['share']            = $share_links;
				$item['register']         = $event_data->meta['mec_more_info'];
				$item['start_date']       = $date_start;
				$item['event_date_start'] = $event['date'];

				if ( $event['repeat'] == 1 ) {
					$item['event_date_end'] = $event['date'];
				} else {
					if ( $dates[0]['start']['date'] == $event['date'] ) {
						$item['continuation_date'] = 0;
						$item['event_date_end']    = date( 'Y-m-d', strtotime( $date_end . ' + 1 day' ) );
					} elseif ( strtotime( $dates[0]['start']['date'] ) < strtotime( $event['date'] ) && strtotime( $date_end ) >= strtotime( $event['date'] ) ) {
						$item['continuation_date'] = 1;
						$item['event_date_end']    = $event['date'];
					} else {
						$item['event_date_end'] = date( 'Y-m-d', strtotime( $date_end . ' + 1 day' ) );
					}
					//                    $date1 = new DateTime($dates[0]['start']['date']);
					//                    $date2 = new DateTime($dates[0]['end']['date']);
					//                    $interval = $date1->diff($date2);
					//                    $item['event_date_end'] = date('Y-m-d', strtotime($event['date'] . ' + ' . $interval->days . ' days'));
				}

				$item['time1']   = date( 'H:i:s', strtotime( "{$dates[0]['start']['hour']}:{$dates[0]['start']['minutes']}:00 {$dates[0]['start']['ampm']}" ) );
				$item['time2']   = date( 'H:i:s', strtotime( "{$dates[0]['end']['hour']}:{$dates[0]['end']['minutes']}:00 {$dates[0]['end']['ampm']}" ) );
				$item['weekday'] = $date_weekday;
				if ( isset( $event_data->locations ) ) {
					foreach ( $event_data->locations as $location ) {
						$item['address']      = $location['address'];
						$item['address_name'] = $location['name'];
						break;
					}
				} else {
					$item['address']      = '&nbsp;';
					$item['address_name'] = '&nbsp;';
				}
				$item['start_time'] = isset( $event_data->time ) ? $event_data->time['start'] : '';
				if ( $item['start_time'] === 'All Day' ) {
					$item['end_time'] = '&nbsp;';
				} else {
					$item['end_time'] = ( isset( $event_data->time ) ? $event_data->time['end'] : '' );
				}

				$item['labels']       = get_the_terms( $id, 'mec_label' );
				$item['categories']   = get_the_terms( $id, 'mec_category' );
				$item['cancellation'] = get_post_meta( $id, 'mec_cancelled_reason', true );

				$result[] = $item;
			}
		}

		if ( isset( $_POST['page'] ) ) {
			$offset = ( $_POST['page'] - 1 ) * $_POST['limit'];
			if ( $offset < 0 ) {
				$offset = 0;
			}
			echo json_encode(
				array(
					'success' => 1,
					'result'  => array_slice( $result, $offset, $_POST['limit'] ),
				)
			);
		} else {
			echo json_encode(
				array(
					'success' => 1,
					'result'  => $result,
				)
			);
		}

		exit;
	}

	function getListEventByDate( $date_selected ) {
		$db                    = \MEC::getInstance( 'app.libraries.db' );
		$main                  = \MEC::getInstance( 'app.libraries.main' );
		$order                 = $_POST['order'];
		$events                = get_posts(
			array(
				'post_type'      => 'mec-events',
				'posts_per_page' => -1,
				'order'          => $order,
			)
		);
		$list                  = array();
		$i                     = 0;
		$timestamp_date_select = strtotime( $date_selected );

		foreach ( $events as $key_event => $event ) {
			$item_event    = array();
			$event_details = $db->select( "SELECT * FROM `#__mec_events` WHERE `post_id`='$event->ID'", 'loadObject' );

			if ( isset( $event_details->repeat ) and $event_details->repeat == '0' ) {
				if ( strtotime( $event_details->start ) <= $timestamp_date_select && strtotime( $event_details->end ) >= $timestamp_date_select ) {
					$item_event['ID']         = $event->ID;
					$item_event['post_title'] = $event->post_title;
					$item_event['date']       = $date_selected;
					$item_event['repeat']     = 0;
					$list[]                   = $item_event;
					++$i;
				}
			} elseif ( strtotime( $event_details->start ) <= $timestamp_date_select && strtotime( $event_details->end ) >= $timestamp_date_select ) {
					$item_event['ID']         = $event->ID;
					$item_event['post_title'] = $event->post_title;
					$item_event['date']       = $date_selected;
					$item_event['repeat']     = 0;
					$list[]                   = $item_event;
					++$i;
					continue 1;
			} elseif ( $event_details->start == $date_selected ) {
				$item_event['ID']         = $event->ID;
				$item_event['post_title'] = $event->post_title;
				$item_event['date']       = $date_selected;
				$item_event['repeat']     = 0;
				$list[]                   = $item_event;
				++$i;
				continue 1;
			} else {
				$repeat_type         = get_post_meta( $event->ID, 'mec_repeat_type', true );
				$today               = null;
				$start_date          = get_post_meta( $event->ID, 'mec_start_date', true );
				$end_date            = get_post_meta( $event->ID, 'mec_end_date', true );
				$finish_date         = array(
					'date'    => get_post_meta( $event->ID, 'mec_end_date', true ),
					'hour'    => get_post_meta( $event->ID, 'mec_end_time_hour', true ),
					'minutes' => get_post_meta( $event->ID, 'mec_end_time_minutes', true ),
					'ampm'    => get_post_meta( $event->ID, 'mec_end_time_ampm', true ),
				);
				$exceptional_days    = ( trim( get_post_meta( $event->ID, 'mec_end_date', true ) ) ) ? explode( ',', trim( get_post_meta( $event->ID, 'mec_not_in_days', true ), ', ' ) ) : array();
				$allday              = get_post_meta( $event->ID, 'mec_allday', true );
				$hide_time           = get_post_meta( $event->ID, 'mec_hide_time', true );
				$event_period        = $main->date_diff( $start_date, $end_date );
				$event_period_days   = $event_period ? $event_period->days : 0;
				$dates               = array();
				$original_start_date = $today;

				if ( in_array( $repeat_type, array( 'daily', 'weekly' ) ) ) {
					$repeat_interval = get_post_meta( $event->ID, 'mec_repeat_interval', true );

					$loop       = true;
					$start_date = date( 'Y-m-d', strtotime( '+' . $repeat_interval . ' Days', strtotime( $start_date ) ) );

					if ( $timestamp_date_select >= strtotime( $start_date ) ) {
						if ( $start_date == date( 'Y-m-d', strtotime( $date_selected ) ) ) {

							$item_event['ID']         = $event->ID;
							$item_event['post_title'] = $event->post_title;
							$item_event['date']       = $date_selected;
							$item_event['repeat']     = 1;
							$list[]                   = $item_event;
							++$i;
							$loop = false;
						}
					} else {
						$loop = false;
					}

					while ( $loop ) {
						$start_date = date( 'Y-m-d', strtotime( '+' . $repeat_interval . ' Days', strtotime( $start_date ) ) );

						if ( ! in_array( $start_date, $exceptional_days ) ) {

							$dates[] = $this->add_timestamps(
								array(
									'start'     => array(
										'date'    => $start_date,
										'hour'    => get_post_meta( $event->ID, 'mec_start_time_hour', true ),
										'minutes' => get_post_meta( $event->ID, 'mec_start_time_minutes', true ),
										'ampm'    => get_post_meta( $event->ID, 'mec_start_time_ampm', true ),
									),
									'end'       => array(
										'date'    => date( 'Y-m-d', strtotime( '+' . $event_period_days . ' Days', strtotime( $start_date ) ) ),
										'hour'    => get_post_meta( $event->ID, 'mec_end_time_hour', true ),
										'minutes' => get_post_meta( $event->ID, 'mec_end_time_minutes', true ),
										'ampm'    => get_post_meta( $event->ID, 'mec_end_time_ampm', true ),
									),
									'allday'    => $allday,
									'hide_time' => $hide_time,
									'past'      => 0,
								)
							);

							if ( $timestamp_date_select >= strtotime( $start_date ) ) {
								if ( $start_date == date( 'Y-m-d', strtotime( $date_selected ) ) ) {

									$item_event['ID']         = $event->ID;
									$item_event['post_title'] = $event->post_title;
									$item_event['date']       = $date_selected;
									$item_event['repeat']     = 1;
									$list[]                   = $item_event;
									++$i;
									break;
								}
							} else {
								$loop = false;
							}
						}
					}
				} elseif ( in_array( $repeat_type, array( 'weekday', 'weekend', 'certain_weekdays' ) ) ) {
					$date_interval = $main->date_diff( $start_date, $today );
					$passed_days   = $date_interval ? $date_interval->days : 0;

					$today = $start_date;
					// Check if date interval is negative (It means the event didn't start yet)
					if ( $date_interval and $date_interval->invert == 1 ) {
						$today = date( 'Y-m-d', strtotime( '+' . $passed_days . ' Days', strtotime( $today ) ) );
					}

					$event_days = explode( ',', trim( $event_details->weekdays, ', ' ) );

					$today_id = date( 'N', strtotime( $today ) );
					$loop     = true;

					while ( $loop ) {

						if ( ! in_array( $today_id, $event_days ) ) {
							$today    = date( 'Y-m-d', strtotime( '+1 Days', strtotime( $today ) ) );
							$today_id = date( 'N', strtotime( $today ) );
							continue;
						}

						$start_date = $today;
						if ( $timestamp_date_select >= strtotime( $start_date ) ) {
							if ( $start_date == date( 'Y-m-d', strtotime( $date_selected ) ) ) {

								$item_event['ID']         = $event->ID;
								$item_event['post_title'] = $event->post_title;
								$item_event['date']       = $date_selected;
								$item_event['repeat']     = 1;
								$list[]                   = $item_event;
								++$i;
								break;
							}
						} else {
							$loop = false;
						}

						if ( ! in_array( $start_date, $exceptional_days ) ) {
							$dates[] = $this->add_timestamps(
								array(
									'start'     => array(
										'date'    => $start_date,
										'hour'    => get_post_meta( $event->ID, 'mec_start_time_hour', true ),
										'minutes' => get_post_meta( $event->ID, 'mec_start_time_minutes', true ),
										'ampm'    => get_post_meta( $event->ID, 'mec_start_time_ampm', true ),
									),
									'end'       => array(
										'date'    => date( 'Y-m-d', strtotime( '+' . $event_period_days . ' Days', strtotime( $start_date ) ) ),
										'hour'    => get_post_meta( $event->ID, 'mec_end_time_hour', true ),
										'minutes' => get_post_meta( $event->ID, 'mec_end_time_minutes', true ),
										'ampm'    => get_post_meta( $event->ID, 'mec_end_time_ampm', true ),
									),
									'allday'    => $allday,
									'hide_time' => $hide_time,
									'past'      => 0,
								)
							);
						}

						$today    = date( 'Y-m-d', strtotime( '+1 Days', strtotime( $today ) ) );
						$today_id = date( 'N', strtotime( $today ) );
					}
				} elseif ( $repeat_type == 'monthly' ) {
					$repeat_interval = max( 1, get_post_meta( $event->ID, 'mec_repeat_interval', true ) );

					// Start from Event Start Date
					$original_start_date = $start_date;

					$event_days      = explode( ',', trim( $event_details->day, ', ' ) );
					$event_start_day = $event_days[0];

					$diff              = $main->date_diff( $start_date, $end_date );
					$event_period_days = $diff->days;

					$q = $repeat_interval;
					$t = strtotime( $original_start_date . ' + ' . $q . ' months' );

					$loop = true;
					while ( $loop ) {
						$today   = date( 'Y-m-d', $t );
						$year    = date( 'Y', $t );
						$month   = date( 'm', $t );
						$day     = $event_start_day;
						$hour    = get_post_meta( $event->ID, 'mec_end_time_hour', true );
						$minutes = get_post_meta( $event->ID, 'mec_end_time_minutes', true );
						$ampm    = get_post_meta( $event->ID, 'mec_end_time_ampm', true );

						// Fix for 31st, 30th, 29th of some months
						while ( ! checkdate( $month, $day, $year ) ) {
							--$day;
						}

						$start_date = $year . '-' . $month . '-' . $day;
						$end_time   = $hour . ':' . $minutes . ' ' . $ampm;

						if ( ! in_array( $start_date, $exceptional_days ) ) {

							if ( $timestamp_date_select >= strtotime( $start_date ) ) {
								if ( $start_date == date( 'Y-m-d', strtotime( $date_selected ) ) ) {

									$item_event['ID']         = $event->ID;
									$item_event['post_title'] = $event->post_title;
									$item_event['date']       = $date_selected;
									$item_event['repeat']     = 1;
									$list[]                   = $item_event;
									++$i;
									break;
								}
							} else {
								$loop = false;
							}

							$dates[] = $this->add_timestamps(
								array(
									'start'     => array(
										'date'    => $start_date,
										'hour'    => get_post_meta( $event->ID, 'mec_start_time_hour', true ),
										'minutes' => get_post_meta( $event->ID, 'mec_start_time_minutes', true ),
										'ampm'    => get_post_meta( $event->ID, 'mec_start_time_ampm', true ),
									),
									'end'       => array(
										'date'    => date( 'Y-m-d', strtotime( '+' . $event_period_days . ' Days', strtotime( $start_date ) ) ),
										'hour'    => get_post_meta( $event->ID, 'mec_end_time_hour', true ),
										'minutes' => get_post_meta( $event->ID, 'mec_end_time_minutes', true ),
										'ampm'    => get_post_meta( $event->ID, 'mec_end_time_ampm', true ),
									),
									'allday'    => $allday,
									'hide_time' => $hide_time,
									'past'      => 0,
								)
							);

						}

						$q += $repeat_interval;
						$t  = strtotime( $original_start_date . ' + ' . $q . ' months' );
					}
				} elseif ( $repeat_type == 'yearly' ) {
					// Start from Event Start Date
					if ( strtotime( $start_date ) > strtotime( $original_start_date ) ) {
						$original_start_date = $start_date;
					}

					$event_days   = explode( ',', trim( $event_details->day, ', ' ) );
					$event_months = explode( ',', trim( $event_details->month, ', ' ) );

					$event_start_day   = $event_days[0];
					$event_period_days = $main->date_diff( $start_date, $end_date )->days;

					$event_start_year  = date( 'Y', strtotime( $original_start_date ) );
					$event_start_month = date( 'n', strtotime( $original_start_date ) );

					$q = 0;

					$loop = true;
					while ( $loop ) {
						$today = date( 'Y-m-d', strtotime( $event_start_year . '-' . $event_start_month . '-' . $event_start_day ) );

						$year  = date( 'Y', strtotime( $today ) );
						$month = date( 'm', strtotime( $today ) );

						if ( ! in_array( $month, $event_months ) ) {
							if ( $event_start_month == '12' ) {
								$event_start_month = 1;
								$event_start_year += 1;
							} else {
								$event_start_month += 1;
							}

							++$q;
							continue;
						}

						$day = $event_start_day;

						// Fix for 31st, 30th, 29th of some months
						while ( ! checkdate( $month, $day, $year ) ) {
							--$day;
						}

						$event_date = $year . '-' . $month . '-' . $day;
						if ( strtotime( $event_date ) >= strtotime( $original_start_date ) ) {
							$start_date = $event_date;

							if ( $timestamp_date_select >= strtotime( $start_date ) ) {
								if ( $start_date == date( 'Y-m-d', strtotime( $date_selected ) ) ) {

									$item_event['ID']         = $event->ID;
									$item_event['post_title'] = $event->post_title;
									$item_event['date']       = $date_selected;
									$item_event['repeat']     = 1;
									$list[]                   = $item_event;
									++$i;
									break;
								}
							} else {
								$loop = false;
							}

							if ( ! in_array( $start_date, $exceptional_days ) ) {
								$dates[] = $this->add_timestamps(
									array(
										'start'     => array(
											'date'    => $start_date,
											'hour'    => get_post_meta( $event->ID, 'mec_start_time_hour', true ),
											'minutes' => get_post_meta( $event->ID, 'mec_start_time_minutes', true ),
											'ampm'    => get_post_meta( $event->ID, 'mec_start_time_ampm', true ),
										),
										'end'       => array(
											'date'    => date( 'Y-m-d', strtotime( '+' . $event_period_days . ' Days', strtotime( $start_date ) ) ),
											'hour'    => get_post_meta( $event->ID, 'mec_end_time_hour', true ),
											'minutes' => get_post_meta( $event->ID, 'mec_end_time_minutes', true ),
											'ampm'    => get_post_meta( $event->ID, 'mec_end_time_ampm', true ),
										),
										'allday'    => $allday,
										'hide_time' => $hide_time,
										'past'      => 0,
									)
								);
							}
						}

						if ( $event_start_month == '12' ) {
							$event_start_month = 1;
							$event_start_year += 1;
						} else {
							$event_start_month += 1;
						}

							++$q;
					}
				} elseif ( $repeat_type == 'custom_days' ) {
					$custom_days = explode( ',', $event_details->days );

					// Add current time if we're checking today's events
					if ( $today == current_time( 'Y-m-d' ) ) {
						$today .= ' ' . current_time( 'H:i:s' );
					}

					$loop = true;
					if ( ( strtotime( $event_details->start ) + get_post_meta( $event->ID, 'mec_start_day_seconds', true ) ) >= strtotime( $today ) and ! in_array( $event_details->start, $exceptional_days ) ) {
						$dates[] = $this->add_timestamps(
							array(
								'start'     => array(
									'date'    => $event_details->start,
									'hour'    => get_post_meta( $event->ID, 'mec_start_time_hour', true ),
									'minutes' => get_post_meta( $event->ID, 'mec_start_time_minutes', true ),
									'ampm'    => get_post_meta( $event->ID, 'mec_start_time_ampm', true ),
								),
								'end'       => array(
									'date'    => $event_details->end,
									'hour'    => get_post_meta( $event->ID, 'mec_end_time_hour', true ),
									'minutes' => get_post_meta( $event->ID, 'mec_end_time_minutes', true ),
									'ampm'    => get_post_meta( $event->ID, 'mec_end_time_ampm', true ),
								),
								'allday'    => $allday,
								'hide_time' => $hide_time,
								'past'      => 0,
							)
						);

						if ( $timestamp_date_select >= strtotime( $event_details->start ) ) {
							if ( $event_details->start == date( 'Y-m-d', strtotime( $date_selected ) ) ) {

								$item_event['ID']         = $event->ID;
								$item_event['post_title'] = $event->post_title;
								$item_event['date']       = $date_selected;
								$item_event['repeat']     = 1;
								$list[]                   = $item_event;
								++$i;
								continue 1;
							}
						} else {
							$loop = false;
						}
					}

					foreach ( $custom_days as $custom_day ) {
						// Found maximum dates
						if ( ! $loop ) {
							break;
						}

						$cday = explode( ':', $custom_day );

						$c_start = $cday[0];
						if ( isset( $cday[2] ) ) {
							$c_start .= ' ' . str_replace( '-', ' ', substr_replace( $cday[2], ':', strpos( $cday[2], '-' ), 1 ) );
						}

						// Date is past
						if ( strtotime( $c_start ) < strtotime( $today ) ) {
							continue;
						}

						$cday_start_hour    = get_post_meta( $event->ID, 'mec_start_time_hour', true );
						$cday_start_minutes = get_post_meta( $event->ID, 'mec_start_time_minutes', true );
						$cday_start_ampm    = get_post_meta( $event->ID, 'mec_start_time_ampm', true );

						$cday_end_hour    = get_post_meta( $event->ID, 'mec_end_time_hour', true );
						$cday_end_minutes = get_post_meta( $event->ID, 'mec_end_time_minutes', true );
						$cday_end_ampm    = get_post_meta( $event->ID, 'mec_end_time_ampm', true );

						if ( isset( $cday[2] ) and isset( $cday[3] ) ) {
							$cday_start_ex      = explode( '-', $cday[2] );
							$cday_start_hour    = $cday_start_ex[0];
							$cday_start_minutes = $cday_start_ex[1];
							$cday_start_ampm    = $cday_start_ex[2];

							$cday_end_ex      = explode( '-', $cday[3] );
							$cday_end_hour    = $cday_end_ex[0];
							$cday_end_minutes = $cday_end_ex[1];
							$cday_end_ampm    = $cday_end_ex[2];
						}

						if ( ! in_array( $cday[0], $exceptional_days ) ) {
							$dates[] = $this->add_timestamps(
								array(
									'start'     => array(
										'date'    => $cday[0],
										'hour'    => $cday_start_hour,
										'minutes' => $cday_start_minutes,
										'ampm'    => $cday_start_ampm,
									),
									'end'       => array(
										'date'    => $cday[1],
										'hour'    => $cday_end_hour,
										'minutes' => $cday_end_minutes,
										'ampm'    => $cday_end_ampm,
									),
									'allday'    => $allday,
									'hide_time' => $hide_time,
									'past'      => 0,
								)
							);
						}

						if ( $timestamp_date_select >= strtotime( $cday[0] ) ) {
							if ( $cday[0] == date( 'Y-m-d', strtotime( $date_selected ) ) ) {

								$item_event['ID']         = $event->ID;
								$item_event['post_title'] = $event->post_title;
								$item_event['date']       = $date_selected;
								$item_event['repeat']     = 1;
								$list[]                   = $item_event;
								++$i;
								continue 1;
							}
						} else {
							$loop = false;
						}
					}

					// No future date found so the event is passed
					if ( ! count( $dates ) ) {
						$dates[] = $this->add_timestamps(
							array(
								'start'     => $start_date,
								'end'       => $finish_date,
								'allday'    => $allday,
								'hide_time' => $hide_time,
								'past'      => 0,
							)
						);
					}
				} elseif ( $repeat_type == 'advanced' ) {
					// Start from Event Start Date
					if ( strtotime( $start_date ) > strtotime( $today ) ) {
						$today = $start_date;
					}

					// Get user specifed days of month for repeat
					$advanced_days = get_post_meta( $event->ID, 'mec_advanced_days', true );

					// Generate dates for event
					$event_info   = array(
						'start'            => $start_date,
						'end'              => $end_date,
						'allday'           => $allday,
						'hide_time'        => $hide_time,
						'finish_date'      => $finish_date['date'],
						'exceptional_days' => $exceptional_days,
						'mec_repeat_end'   => get_post_meta( $event->ID, 'mec_repeat_end', true ),
						'occurrences'      => get_post_meta( $event->ID, 'mec_repeat_end_at_occurrences', true ),
					);
					$referer_date = $today;
					$mode         = 'render';

					if ( ! count( $advanced_days ) ) {
						return array();
					}
					if ( ! trim( $referer_date ) ) {
						$referer_date = date( 'Y-m-d', current_time( 'timestamp', 0 ) );
					}

					$levels = array( 'first', 'second', 'third', 'fourth', 'last' );
					$year   = date( 'Y', strtotime( $event_info['start'] ) );
					$dates  = array();

					// Set last month for include current month results
					$month = date( 'm', strtotime( 'first day of last month', strtotime( $event_info['start'] ) ) );

					if ( $month == '12' ) {
						$year = $year - 1;
					}

					$q    = 0;
					$loop = true;
					// Event info
					$exceptional_days  = array_key_exists( 'exceptional_days', $event_info ) ? $event_info['exceptional_days'] : array();
					$start_date        = $event_info['start'];
					$end_date          = $event_info['end'];
					$allday            = array_key_exists( 'allday', $event_info ) ? $event_info['allday'] : 0;
					$hide_time         = array_key_exists( 'hide_time', $event_info ) ? $event_info['hide_time'] : 0;
					$finish_date       = array_key_exists( 'finish_date', $event_info ) ? $event_info['finish_date'] : '0000-00-00';
					$event_period      = $main->date_diff( $start_date, $end_date );
					$event_period_days = $event_period ? $event_period->days : 0;
					$mec_repeat_end    = array_key_exists( 'mec_repeat_end', $event_info ) ? $event_info['mec_repeat_end'] : '';
					$occurrences       = array_key_exists( 'occurrences', $event_info ) ? $event_info['occurrences'] : 0;

					// Include default start date to results
					if ( ! $main->is_past( $start_date, $referer_date ) and ! in_array( $start_date, $exceptional_days ) ) {
						$dates[] = $this->add_timestamps(
							array(
								'start'     => $start_date,
								'end'       => $end_date,
								'allday'    => $allday,
								'hide_time' => $hide_time,
								'past'      => 0,
							)
						);

						if ( $timestamp_date_select >= strtotime( $start_date ) ) {
							if ( $start_date == date( 'Y-m-d', strtotime( $date_selected ) ) ) {

								$item_event['ID']         = $event->ID;
								$item_event['post_title'] = $event->post_title;
								$item_event['date']       = $date_selected;
								$item_event['repeat']     = 1;
								$list[]                   = $item_event;
								++$i;
								continue 1;
							}
						} else {
							$loop = false;
						}
						++$q;
					}

					while ( $loop ) {
						$start = null;

						foreach ( $advanced_days as $day ) {
							if ( ! $loop ) {
								break;
							}

							// Explode $day value for example (Sun.1) to Sun and 1
							$d = explode( '.', $day );

							// Set indexes for {$levels} index if number day is Last(Sun.l) then indexes set 4th {$levels} index
							$index = intval( $d[1] ) ? ( intval( $d[1] ) - 1 ) : 4;

							// Generate date
							$date = date( 'Y-m-t', strtotime( "{$year}-{$month}-01" ) );

							// Generate start date for example "first Sun of next month"
							$start = date( 'Y-m-d', strtotime( "{$levels[$index]} {$d[0]} of next month", strtotime( $date ) ) );
							$end   = date( 'Y-m-d', strtotime( "+{$event_period_days} Days", strtotime( $start ) ) );

							// Jump to next level if start date is past
							if ( $main->is_past( $start, $referer_date ) or in_array( $start, $exceptional_days ) ) {
								continue;
							}

							// Add dates
							$dates[] = $this->add_timestamps(
								array(
									'start'     => array(
										'date'    => $start,
										'hour'    => get_post_meta( $event->ID, 'mec_start_time_hour', true ),
										'minutes' => get_post_meta( $event->ID, 'mec_start_time_minutes', true ),
										'ampm'    => get_post_meta( $event->ID, 'mec_start_time_ampm', true ),
									),
									'end'       => array(
										'date'    => $end,
										'hour'    => get_post_meta( $event->ID, 'mec_end_time_hour', true ),
										'minutes' => get_post_meta( $event->ID, 'mec_end_time_minutes', true ),
										'ampm'    => get_post_meta( $event->ID, 'mec_end_time_ampm', true ),
									),
									'allday'    => $allday,
									'hide_time' => $hide_time,
									'past'      => 0,
								)
							);

							if ( $timestamp_date_select >= strtotime( $start ) ) {
								if ( $start == date( 'Y-m-d', strtotime( $date_selected ) ) ) {

									$item_event['ID']         = $event->ID;
									$item_event['post_title'] = $event->post_title;
									$item_event['date']       = $date_selected;
									$item_event['repeat']     = 1;
									$list[]                   = $item_event;
									++$i;
									break;
								}
							} else {
								$loop = false;
							}

							++$q;
						}

						// Change month and years for next resualts
						if ( intval( $month ) == 12 ) {
							$year  = intval( $year ) + 1;
							$month = '00';
						}

						$month = sprintf( '%02d', intval( $month ) + 1 );
					}

					if ( ( $mode == 'render' ) and ( trim( $mec_repeat_end ) == 'occurrences' ) and ( count( $dates ) > $occurrences ) ) {
						$max = strtotime( reset( $dates )['start']['date'] );
						$pos = 0;

						for ( $c = 1; $c < count( $dates ); $c++ ) {
							if ( strtotime( $dates[ $c ]['start']['date'] ) > $max ) {
								$max = strtotime( $dates[ $c ]['start']['date'] );
								$pos = $c;
							}
						}

						unset( $dates[ $pos ] );
					}
				}
			}
		}
		return $list;
	}

	public function add_timestamps( $date ) {
		$start = ( isset( $date['start'] ) and is_array( $date['start'] ) ) ? $date['start'] : array();
		$end   = ( isset( $date['end'] ) and is_array( $date['end'] ) ) ? $date['end'] : array();

		if ( ! count( $start ) or ! count( $end ) ) {
			return $date;
		}

		$s_hour = $start['hour'];
		if ( strtoupper( $start['ampm'] ) == 'AM' and $s_hour == '0' ) {
			$s_hour = 12;
		}

		$e_hour = $end['hour'];
		if ( strtoupper( $end['ampm'] ) == 'AM' and $e_hour == '0' ) {
			$e_hour = 12;
		}

		$allday = ( isset( $date['allday'] ) ? $date['allday'] : 0 );

		// All Day Event
		if ( $allday ) {
			$s_hour           = 12;
			$start['minutes'] = 1;
			$start['ampm']    = 'AM';

			$e_hour         = 11;
			$end['minutes'] = 59;
			$end['ampm']    = 'PM';
		}

		$start_time = $start['date'] . ' ' . sprintf( '%02d', $s_hour ) . ':' . sprintf( '%02d', $start['minutes'] ) . ' ' . $start['ampm'];
		$end_time   = $end['date'] . ' ' . sprintf( '%02d', $e_hour ) . ':' . sprintf( '%02d', $end['minutes'] ) . ' ' . $end['ampm'];

		$start['timestamp'] = strtotime( $start_time );
		$end['timestamp']   = strtotime( $end_time );

		$hide_time = ( isset( $date['hide_time'] ) ? $date['hide_time'] : 0 );
		$past      = ( isset( $date['past'] ) ? $date['past'] : 0 );

		return array(
			'start'     => $start,
			'end'       => $end,
			'allday'    => $allday,
			'hide_time' => $hide_time,
			'past'      => $past,
		);
	}

	/**
	 * Add option single template settings
	 *
	 * @param array $settings
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public function singleSettings( $settings, $key = 'single_single_style' ) {
		?>
		<option value="liquid" <?php echo ( isset( $settings[ $key ] ) and $settings[ $key ] == 'liquid' ) ? 'selected="selected"' : ''; ?>><?php _e( 'Liquid Style', 'mec-liq' ); ?></option>
		<?php
	}

	public function tplPath( $skin, $style ) {
		if ( strpos( $style, 'liquid' ) !== false ) {
			switch ( $skin ) {
				case 'list':
					if ( strpos( $style, 'large' ) !== false ) {
						return MECLIQUIDDIR . 'core' . DS . 'skins' . DS . $skin . '-large' . DS . 'tpl.php';
					}
					return MECLIQUIDDIR . 'core' . DS . 'skins' . DS . $skin . DS . 'tpl.php';
					break;
				case 'general_calendar':
					return MECLIQUIDDIR . 'core' . DS . 'skins' . DS . $skin . DS . 'tpl.php';
					break;
				case 'daily_view':
					return MECLIQUIDDIR . 'core' . DS . 'skins' . DS . $skin . DS . 'tpl.php';
					break;
				default:
					return MECLIQUIDDIR . 'core' . DS . 'skins' . DS . $skin . DS . 'tpl.php';
					break;
			}
		}

		return $skin;
	}

	/**
	 * Localize Data
	 *
	 * @param array $data
	 *
	 * @return array
	 * @since 1.0.0
	 *
	 */
	public function locolizeData( $data ) {

		$settings = \MEC::getInstance( 'app.libraries.main' )->get_settings();

		$data['day']                = esc_html__( 'DAY', 'mec-liq' );
		$data['days']               = esc_html__( 'DAY', 'mec-liq' );
		$data['hour']               = esc_html__( 'HRS', 'mec-liq' );
		$data['hours']              = esc_html__( 'HRS', 'mec-liq' );
		$data['minute']             = esc_html__( 'MIN', 'mec-liq' );
		$data['minutes']            = esc_html__( 'MIN', 'mec-liq' );
		$data['second']             = esc_html__( 'SEC', 'mec-liq' );
		$data['seconds']            = esc_html__( 'SEC', 'mec-liq' );
		$data['enableSingleLiquid'] = class_exists( 'MEC_Liquid\Base' ) && ( isset( $settings['single_single_style'] ) and $settings['single_single_style'] == 'liquid' ) ? true : false;

		return $data;
	}

	/**
	 * Add Multiple Style to Skin Options
	 *
	 * @since 1.0.0
	 */
	public function StyleOptionMultiple( $settings, $types ) {

		?>
		<option value="liquid-type1"
		<?php
		if ( isset( $settings ) && $settings === 'liquid-type1' ) {
			echo 'selected="selected"';
		}
		?>
		><?php _e( 'Liquid', 'mec-liq' ); ?></option>
		<?php
	}

	/**
	 * Add Style to Skin Options
	 *
	 * @since 1.0.0
	 */
	public function StyleOption( $settings ) {

		?>
		<option value="liquid"
		<?php
		if ( isset( $settings ) && $settings === 'liquid' ) {
			echo 'selected="selected"';
		}
		?>
		><?php _e( 'Liquid', 'mec-liq' ); ?></option>
		<?php
	}

	/**
	 * Slider Initialize Method
	 *
	 * @since 1.0.0
	 */
	public function sliderInitialize( $This ) {

		$This->display_price             = ( isset( $This->skin_options['display_price'] ) and trim( $This->skin_options['display_price'] ) ) ? true : false;
		$This->display_available_tickets = ( isset( $This->skin_options['display_available_tickets'] ) and trim( $This->skin_options['display_available_tickets'] ) ) ? $This->skin_options['display_available_tickets'] : '';
	}

	/**
	 * Slider Skin Options
	 *
	 * @since 1.0.0
	 */
	public function sliderSkinOptions( $sk_options_slider ) {

		?>
		<div class="mec-form-row mec-switcher mec-slider-liquid">
			<div class="mec-col-4">
				<label for="mec_skin_slider_liquid_display_price"><?php _e( 'Display Event Price', 'mec-liq' ); ?></label>
			</div>
			<div class="mec-col-4">
				<input type="hidden" name="mec[sk-options][slider][display_price]" value="0"/>
				<input type="checkbox" name="mec[sk-options][slider][display_price]"
						id="mec_skin_slider_liquid_display_price"
						value="1"
						<?php
						if ( isset( $sk_options_slider['display_price'] ) and $sk_options_slider['display_price'] ) {
							echo 'checked="checked"';
						}
						?>
						/>
				<label for="mec_skin_slider_liquid_display_price"></label>
			</div>
		</div>
		<div class="mec-form-row mec-switcher mec-slider-liquid">
			<div class="mec-col-4">
				<label for="mec_skin_slider_liquid_display_available_tickets"><?php _e( 'Display Available Tickets', 'mec-liq' ); ?></label>
			</div>
			<div class="mec-col-4">
				<input type="hidden" name="mec[sk-options][slider][display_available_tickets]" value="0"/>
				<input type="checkbox" name="mec[sk-options][slider][display_available_tickets]"
						id="mec_skin_slider_liquid_display_available_tickets"
						value="1"
						<?php
						if ( isset( $sk_options_slider['display_available_tickets'] ) and $sk_options_slider['display_available_tickets'] ) {
							echo 'checked="checked"';
						}
						?>
						/>
				<label for="mec_skin_slider_liquid_display_available_tickets"></label>
			</div>
		</div>
		<div class="mec-form-row mec-slider-liquid">
			<label class="mec-col-4"
					for="mec_skin_slider_liquid_wrapper_bg_color"><?php _e( 'Wrapper Background Color', 'mec-liq' ); ?></label>
			<input type="text" class="mec-col-4 mec-color-picker wp-color-picker-field"
					id="mec_skin_slider_liquid_wrapper_bg_color" name="mec[sk-options][slider][wrapper_bg_color]"
					value="<?php echo( ( isset( $sk_options_slider['wrapper_bg_color'] ) and trim( $sk_options_slider['wrapper_bg_color'] ) != '' ) ? $sk_options_slider['wrapper_bg_color'] : '' ); ?>"
					data-default-color="">
		</div>
		<?php
	}

	/**
	 * Cover Skin Options
	 *
	 * @since 1.0.0
	 */
	public function coverEndSkinOptions( $sk_options_cover ) {
		?>
		<div class="mec-form-row mec-cover-liquid">
			<label class="mec-col-4"
					for="mec_skin_cover_wrapper_bg_color"><?php _e( 'Wrapper Background Color', 'mec-liq' ); ?></label>
			<input type="text" class="mec-col-4 mec-color-picker wp-color-picker-field"
					id="mec_skin_cover_wrapper_bg_color" name="mec[sk-options][cover][wrapper_bg_color]"
					value="<?php echo( ( isset( $sk_options_cover['wrapper_bg_color'] ) and trim( $sk_options_cover['wrapper_bg_color'] ) != '' ) ? $sk_options_cover['wrapper_bg_color'] : '' ); ?>"
					data-default-color="">
		</div>
		<?php
	}

	/**
	 * Carousel Initialize Method
	 *
	 * @since 1.0.0
	 */
	public function carouselInitialize( $This ) {

		// Navigation
		$This->navigation = ( isset( $This->skin_options['navigation'] ) and trim( $This->skin_options['navigation'] ) ) ? $This->skin_options['navigation'] : false;
		// Dots Navigation
		$This->dots_navigation = ( isset( $This->skin_options['dots_navigation'] ) and trim( $This->skin_options['dots_navigation'] ) ) ? $This->skin_options['dots_navigation'] : false;
	}

	/**
	 * Carousel Skin Options
	 *
	 * @since 1.0.0
	 */
	public function carouselSkinOptions( $sk_options_carousel ) {
		?>
		<div class="mec-carousel-liquid mec-form-row mec-switcher">
			<div class="mec-col-4">
				<label for="mec_skin_grid_display_navigation"><?php _e( 'Display Navigation', 'mec-liq' ); ?></label>
			</div>
			<div class="mec-col-4">
				<input type="hidden" name="mec[sk-options][carousel][navigation]" value="0"/>
				<input type="checkbox" name="mec[sk-options][carousel][navigation]"
						id="mec_skin_grid_display_navigation"
						value="1"
						<?php
						if ( ! isset( $sk_options_carousel['navigation'] ) || ( isset( $sk_options_carousel['navigation'] ) and $sk_options_carousel['navigation'] ) ) {
							echo 'checked="checked"';
						}
						?>
						/>
				<label for="mec_skin_grid_display_navigation"></label>
			</div>
		</div>
		<div class="mec-form-row mec-switcher">
			<div class="mec-col-4">
				<label for="mec_skin_carousel_dots_navigation"><?php _e( 'Display Dots Navigation', 'mec-liq' ); ?></label>
			</div>
			<div class="mec-col-4">
				<input type="hidden" name="mec[sk-options][carousel][dots_navigation]" value="0"/>
				<input type="checkbox" name="mec[sk-options][carousel][dots_navigation]"
						id="mec_skin_carousel_dots_navigation"
						value="1"
						<?php
						if ( isset( $sk_options_carousel['dots_navigation'] ) and $sk_options_carousel['dots_navigation'] ) {
							echo 'checked="checked"';
						}
						?>
						/>
				<label for="mec_skin_carousel_dots_navigation"></label>
			</div>
		</div>
		<div class="mec-form-row mec-carousel-liquid">
			<label class="mec-col-4"
					for="mec_skin_carousel_wrapper_bg_color"><?php _e( 'Wrapper Background Color', 'mec-liq' ); ?></label>
			<input type="text" class="mec-col-4 mec-color-picker wp-color-picker-field"
					id="mec_skin_carousel_wrapper_bg_color" name="mec[sk-options][carousel][wrapper_bg_color]"
					value="<?php echo( ( isset( $sk_options_carousel['wrapper_bg_color'] ) and trim( $sk_options_carousel['wrapper_bg_color'] ) != '' ) ? $sk_options_carousel['wrapper_bg_color'] : '' ); ?>"
					data-default-color="">
		</div>
		<?php
	}

	/**
	 * Available Spot Skin Options
	 *
	 * @since 1.0.0
	 */
	public function availableSpotEndSkinOptions( $sk_options_available_spot ) {

		?>
		<div class="mec-form-row mec-available_spot-liquid">
			<label class="mec-col-4"
					for="mec_skin_available_spot_wrapper_bg_color"><?php _e( 'Wrapper Background Color', 'mec-liq' ); ?></label>
			<input type="text" class="mec-col-4 mec-color-picker wp-color-picker-field"
					id="mec_skin_available_spot_wrapper_bg_color"
					name="mec[sk-options][available_spot][wrapper_bg_color]"
					value="<?php echo( ( isset( $sk_options_available_spot['wrapper_bg_color'] ) and trim( $sk_options_available_spot['wrapper_bg_color'] ) != '' ) ? $sk_options_available_spot['wrapper_bg_color'] : '' ); ?>"
					data-default-color="">
		</div>
		<?php
	}

	/**
	 * Skin Initialize Method
	 *
	 * @since 1.0.0
	 */
	public function skin_initialize( $This ) {

		$This->date_format_liquid_1 = ( isset( $This->skin_options['liquid_date_format1'] ) and trim( $This->skin_options['liquid_date_format1'] ) ) ? $This->skin_options['liquid_date_format1'] : 'F d';
	}

	/**
	 * Available Spot Skin Options
	 *
	 * @since 1.0.0
	 */
	public function availableSpotSkinOptions( $sk_options_available_spot ) {

		?>
		<div class="mec-available_spot-liquid mec-form-row">
			<label class="mec-col-4"
					for="mec_skin_available_spot_liquid_date_format1"><?php _e( 'Date Formats', 'mec-liq' ); ?></label>
			<input type="text" class="mec-col-4" name="mec[sk-options][available_spot][liquid_date_format1]"
					id="mec_skin_available_spot_liquid_date_format1"
					value="<?php echo( ( isset( $sk_options_available_spot['liquid_date_format1'] ) and trim( $sk_options_available_spot['liquid_date_format1'] ) != '' ) ? $sk_options_available_spot['liquid_date_format1'] : 'F d' ); ?>"/>
			<span class="mec-tooltip">
				<div class="box top">
					<h5 class="title"><?php _e( 'Date Formats', 'mec-liq' ); ?></h5>
					<div class="content">
						<p><?php esc_attr_e( 'Default value is "F d"', 'mec-liq' ); ?><a
									href="https://webnus.net/dox/modern-events-calendar/available_spot-view-skin/"
									target="_blank"><?php _e( 'Read More', 'mec-liq' ); ?></a></p>
					</div>
				</div>
				<i title="" class="dashicons-before dashicons-editor-help"></i>
			</span>
		</div>
		<?php
	}

	/**
	 * Map Initialize Method
	 *
	 * @since 1.0.0
	 */
	public function mapInitialize( $This ) {

		$This->date_format_liquid_1 = ( isset( $This->skin_options['liquid_date_format1'] ) and trim( $This->skin_options['liquid_date_format1'] ) ) ? $This->skin_options['liquid_date_format1'] : 'F d';
		$This->style                = isset( $This->skin_options['style'] ) ? $This->skin_options['style'] : 'classic';
	}

	/**
	 * Map Skin Options
	 *
	 * @since 1.0.0
	 */
	public function mapSkinOptions( $options ) {

		$this->NextPrevSkinOptions( 'map', $options );
		?>
		<div class="mec-map-liquid mec-form-row">
			<label class="mec-col-4"
					for="mec_skin_map_liquid_date_format1"><?php _e( 'Date Formats', 'mec-liq' ); ?></label>
			<input type="text" class="mec-col-4" name="mec[sk-options][map][liquid_date_format1]"
					id="mec_skin_map_liquid_date_format1"
					value="<?php echo( ( isset( $options['liquid_date_format1'] ) and trim( $options['liquid_date_format1'] ) != '' ) ? $options['liquid_date_format1'] : 'F d' ); ?>"/>
			<span class="mec-tooltip">
				<div class="box top">
					<h5 class="title"><?php _e( 'Date Formats', 'mec-liq' ); ?></h5>
					<div class="content">
						<p><?php esc_attr_e( 'Default value is "F d"', 'mec-liq' ); ?><a
									href="https://webnus.net/dox/modern-events-calendar/map-view-skin/"
									target="_blank"><?php _e( 'Read More', 'mec-liq' ); ?></a></p>
					</div>
				</div>
				<i title="" class="dashicons-before dashicons-editor-help"></i>
			</span>
		</div>
		<?php
	}

	public static function filter_get_marker_lightbox( $content, $event, $date_format, $skin_style = 'classic' ) {

		if ( false === strpos( $skin_style, 'liquid' ) ) {
			return $content;
		}

        global $MEC_Shortcode_id;

		$mainClass              = \MEC\Base::get_main();
		$skinClass              = new \MEC_skins();
        $options                = get_post_meta( $MEC_Shortcode_id, 'sk-options', true );
		$link                   = $mainClass->get_event_date_permalink( $event, ( isset( $event->date['start'] ) ? $event->date['start']['date'] : null ) );
        $infowindow_thumb       = trim($event->data->featured_image['custom']) ? '<div class="mec-event-image"><a data-event-id="' . esc_attr($event->data->ID) . '" href="' . esc_url($link) . '"><img src="' . esc_url($event->data->featured_image['custom']) . '" alt="' . esc_attr($event->data->title) . '" width="' . esc_attr($options['thumbnail_size']['width']) . '" height="' . esc_attr($options['thumbnail_size']['height']) . '" /></a></div>' : '';
        $infowindow_thumb       = ! $infowindow_thumb && trim($event->data->featured_image['thumbnail']) ? '<div class="mec-event-image"><a data-event-id="' . esc_attr($event->data->ID) . '" href="' . esc_url($link) . '"><img src="' . esc_url($event->data->featured_image['thumbnail']) . '" alt="' . esc_attr($event->data->title) . '" /></a></div>' : $infowindow_thumb;
		$event_start_date       = ! empty( $event->date['start']['date'] ) ? $event->date['start']['date'] : '';
		$event_start_date_day   = ! empty( $event->date['start']['date'] ) ? $mainClass->date_i18n( 'd', strtotime( $event->date['start']['date'] ) ) : '';
		$event_start_date_month = ! empty( $event->date['start']['date'] ) ? $mainClass->date_i18n( 'M', strtotime( $event->date['start']['date'] ) ) : '';
		$event_start_date_year  = ! empty( $event->date['start']['date'] ) ? $mainClass->date_i18n( 'Y', strtotime( $event->date['start']['date'] ) ) : '';
		$start_time             = ! empty( $event->data->time['start'] ) ? $event->data->time['start'] : '';
		$end_time               = ! empty( $event->data->time['end'] ) ? $event->data->time['end'] : '';
		$event_color_dot        = $skinClass->get_event_color_dot( $event );

		$content = '
		<div class="mec-wrap">
			<div class="mec-map-lightbox-wp mec-event-list-classic mec-liquid-map-lightbox-wp">
				<article class="' . ( ( isset( $event->data->meta['event_past'] ) and trim( $event->data->meta['event_past'] ) ) ? 'mec-past-event ' : '' ) . 'mec-event-article mec-clear">
					' . \MEC_kses::element( $infowindow_thumb ) . '
                    <div class="mec-event-datetime-wrap">
                        <div class="mec-map-date mec-event-date">
					        <i class="mec-sl-calendar"></i>'
			. '<span class="mec-map-lightbox-month">' . esc_html( $event_start_date_month ) . '</span>'
			. '<span class="mec-map-lightbox-day"> ' . esc_html( $event_start_date_day ) . '</span>'
			. '<span class="mec-map-lightbox-year"> ' . esc_html( $event_start_date_year ) . '</span>'
			. '</div>
                        <div class="mec-map-time">' . \MEC_kses::element( $mainClass->display_time( $start_time, $end_time ) ) . '</div>
                    </div>
					<h4 class="mec-event-title">
                        ' . \MEC_kses::element( $skinClass->display_link( $event ) ) . $event_color_dot . '
                        ' . \MEC_kses::element( $mainClass->get_flags( $event ) ) . '
					</h4>
				</article>
			</div>
		</div>';

		return $content;
	}

	/**
	 * Weekly View Skin Options
	 *
	 * @since 1.0.0
	 */
	public function weeklyViewSkinOptions( $sk_options_weekly_view ) {
		$this->NextPrevSkinOptions( 'weekly_view', $sk_options_weekly_view );
		?>
		<div class="mec-form-row mec-weekly_view-liquid">
			<label class="mec-col-4"
					for="mec_skin_weekly_view_wrapper_bg_color"><?php _e( 'Wrapper Background Color', 'mec-liq' ); ?></label>
			<input type="text" class="mec-col-4 mec-color-picker wp-color-picker-field"
					id="mec_skin_weekly_view_wrapper_bg_color" name="mec[sk-options][weekly_view][wrapper_bg_color]"
					value="<?php echo( ( isset( $sk_options_weekly_view['wrapper_bg_color'] ) and trim( $sk_options_weekly_view['wrapper_bg_color'] ) != '' ) ? $sk_options_weekly_view['wrapper_bg_color'] : '' ); ?>"
					data-default-color="">
		</div>
		<?php
	}

	/**
	 * Grid Skin Options
	 *
	 * @since 1.0.0
	 */
	public function gridSkinOptions( $sk_options_grid ) {
		$this->NextPrevSkinOptions( 'grid', $sk_options_grid );
		?>
		<div class="mec-grid-liquid mec-form-row mec-switcher">
			<div class="mec-col-4">
				<label for="mec_skin_grid_display_thumbnail"><?php _e( 'Display Thumbnail', 'mec-liq' ); ?></label>
			</div>
			<div class="mec-col-4">
				<input type="hidden" name="mec[sk-options][grid][display_thumbnail]" value="0"/>
				<input type="checkbox" name="mec[sk-options][grid][display_thumbnail]"
						id="mec_skin_grid_display_thumbnail"
						value="1"
						<?php
						if ( ! isset( $sk_options_grid['display_thumbnail'] ) || ( isset( $sk_options_grid['display_thumbnail'] ) and $sk_options_grid['display_thumbnail'] ) ) {
							echo 'checked="checked"';
						}
						?>
						/>
				<label for="mec_skin_grid_display_thumbnail"></label>
			</div>
		</div>
		<div class="mec-form-row mec-grid-liquid">
			<label class="mec-col-4"
					for="mec_skin_grid_wrapper_bg_color"><?php _e( 'Wrapper Background Color', 'mec-liq' ); ?></label>
			<input type="text" class="mec-col-4 mec-color-picker wp-color-picker-field"
					id="mec_skin_grid_wrapper_bg_color" name="mec[sk-options][grid][wrapper_bg_color]"
					value="<?php echo( ( isset( $sk_options_grid['wrapper_bg_color'] ) and trim( $sk_options_grid['wrapper_bg_color'] ) != '' ) ? $sk_options_grid['wrapper_bg_color'] : '' ); ?>"
					data-default-color="">
		</div>
		<?php
	}

	/**
	 * Daily View Skin Options
	 *
	 * @since 1.0.0
	 */
	public function dailySkinOptions( $sk_options_daily_view ) {
		$this->NextPrevSkinOptions( 'daily_view', $sk_options_daily_view );
		?>
		<div class="mec-daily_view-liquid mec-form-row mec-switcher">
			<div class="mec-col-4">
				<label for="mec_skin_daily_view_display_thumbnail"><?php _e( 'Display Thumbnail', 'mec-liq' ); ?></label>
			</div>
			<div class="mec-col-4">
				<input type="hidden" name="mec[sk-options][daily_view][display_thumbnail]" value="0"/>
				<input type="checkbox" name="mec[sk-options][daily_view][display_thumbnail]"
						id="mec_skin_daily_view_display_thumbnail"
						value="1"
						<?php
						if ( ! isset( $sk_options_daily_view['display_thumbnail'] ) || ( isset( $sk_options_daily_view['display_thumbnail'] ) and $sk_options_daily_view['display_thumbnail'] ) ) {
							echo 'checked="checked"';
						}
						?>
						/>
				<label for="mec_skin_daily_view_display_thumbnail"></label>
			</div>
		</div>
		<div class="mec-form-row mec-daily_view-liquid">
			<label class="mec-col-4"
					for="mec_skin_daily_view_wrapper_bg_color"><?php _e( 'Wrapper Background Color', 'mec-liq' ); ?></label>
			<input type="text" class="mec-col-4 mec-color-picker wp-color-picker-field"
					id="mec_skin_daily_view_wrapper_bg_color" name="mec[sk-options][daily_view][wrapper_bg_color]"
					value="<?php echo( ( isset( $sk_options_daily_view['wrapper_bg_color'] ) and trim( $sk_options_daily_view['wrapper_bg_color'] ) != '' ) ? $sk_options_daily_view['wrapper_bg_color'] : '' ); ?>"
					data-default-color="">
		</div>
		<?php
	}


	/**
	 * List Skin Options
	 *
	 * @since 1.0.0
	 */
	public function listSkinOptions( $sk_options_list ) {
		$this->NextPrevSkinOptions( 'list', $sk_options_list );
		?>
		<div class="mec-list-liquid mec-form-row mec-switcher">
			<div class="mec-col-4">
				<label for="mec_skin_list_display_thumbnail"><?php _e( 'Display Thumbnail', 'mec-liq' ); ?></label>
			</div>
			<div class="mec-col-4">
				<input type="hidden" name="mec[sk-options][list][display_thumbnail]" value="0"/>
				<input type="checkbox" name="mec[sk-options][list][display_thumbnail]"
						id="mec_skin_list_display_thumbnail"
						value="1"
						<?php
						if ( ! isset( $sk_options_list['display_thumbnail'] ) || ( isset( $sk_options_list['display_thumbnail'] ) and $sk_options_list['display_thumbnail'] ) ) {
							echo 'checked="checked"';
						}
						?>
						/>
				<label for="mec_skin_list_display_thumbnail"></label>
			</div>
		</div>
		<div class="mec-form-row mec-list-liquid">
			<label class="mec-col-4"
					for="mec_skin_list_wrapper_bg_color"><?php _e( 'Wrapper Background Color', 'mec-liq' ); ?></label>
			<input type="text" class="mec-col-4 mec-color-picker wp-color-picker-field"
					id="mec_skin_list_wrapper_bg_color" name="mec[sk-options][list][wrapper_bg_color]"
					value="<?php echo( ( isset( $sk_options_list['wrapper_bg_color'] ) and trim( $sk_options_list['wrapper_bg_color'] ) != '' ) ? $sk_options_list['wrapper_bg_color'] : '' ); ?>"
					data-default-color="">
		</div>
		<?php
	}

	/**
	 * Next Prev Skin Options
	 *
	 * @since 1.0.0
	 */
	public function NextPrevSkinOptions( $skin, $options ) {

		?>
		<div class="mec-<?php echo esc_attr( $skin ); ?>-liquid mec-form-row mec-switcher">
			<div class="mec-col-4">
				<label><?php esc_html_e( 'Next/Previous Buttons', 'mec-liq' ); ?></label>
			</div>
			<div class="mec-col-4">
				<input type="hidden" name="mec[sk-options][<?php echo esc_attr( $skin ); ?>][next_previous_button]"
						value="0"/>
				<input type="checkbox" name="mec[sk-options][<?php echo esc_attr( $skin ); ?>][next_previous_button]"
						id="mec_skin_<?php echo esc_attr( $skin ); ?>_next_previous_button"
						value="1"
						<?php
						if ( ! isset( $options['next_previous_button'] ) or ( isset( $options['next_previous_button'] ) and $options['next_previous_button'] ) ) {
							echo 'checked="checked"';}
						?>
						/>
				<label for="mec_skin_<?php echo esc_attr( $skin ); ?>_next_previous_button"></label>
			</div>
		</div>
		<?php
	}


	/**
	 * Full Calendar Initialize Method
	 *
	 * @since 1.0.0
	 */
	public function fullCalendarInitialize( $This ) {
		$This->style = isset( $This->skin_options['style'] ) ? $This->skin_options['style'] : 'classic';
	}

	/**
	 * Full Calendar Load Skin Method
	 *
	 * @since 1.0.0
	 */
	public function fullCalendarLoadSkin( $atts, $This, $skin ) {
		if ( strpos( $This->style, 'liquid' ) === false ) {
			return $atts;
		}
		$atts['sf_status']                               = $This->sf_status;
		$atts['sk-options'][ $skin ]['style']            = 'liquid';
		$atts['sk-options'][ $skin ]['wrapper_bg_color'] = '';
		$atts['sf-options'][ $skin ]                     = array(
			'category'     => ( isset( $This->sf_options['category'] ) ? $This->sf_options['category'] : array() ),
			'location'     => ( isset( $This->sf_options['location'] ) ? $This->sf_options['location'] : array() ),
			'organizer'    => ( isset( $This->sf_options['organizer'] ) ? $This->sf_options['organizer'] : array() ),
			'speaker'      => ( isset( $This->sf_options['speaker'] ) ? $This->sf_options['speaker'] : array() ),
			'tag'          => ( isset( $This->sf_options['tag'] ) ? $This->sf_options['tag'] : array() ),
			'label'        => ( isset( $This->sf_options['label'] ) ? $This->sf_options['label'] : array() ),
			'month_filter' => ( isset( $This->sf_options['month_filter'] ) ? $This->sf_options['month_filter'] : array() ),
			'text_search'  => ( isset( $This->sf_options['text_search'] ) ? $This->sf_options['text_search'] : array() ),
			'time_filter'  => isset( $This->sf_options['time_filter'] ) ? $This->sf_options['time_filter'] : array(),
			'event_cost'   => isset( $This->sf_options['event_cost'] ) ? $This->sf_options['event_cost'] : array(),
		);
		if ( $skin == 'list' ) {
			$atts['sk-options'][ $skin ]['style']               = 'liquid-large';
			$atts['sk-options'][ $skin ]['liquid_date_format1'] = isset( $This->skin_options['date_format_list'] ) ? $This->skin_options['date_format_list'] : 'd M';
		}

		if ( $skin == 'grid' ) {
			$atts['sk-options'][ $skin ]['style'] = 'liquid-large';
		}

		return $atts;
	}

	/**
	 * Full Calendar Skin Options
	 *
	 * @since 1.0.0
	 */
	public function fullCalendarEndSkinOptions( $sk_options_full_calendar ) {
		?>
		<div class="mec-form-row mec-full_calendar-liquid">
			<label class="mec-col-4"
					for="mec_skin_full_calendar_wrapper_bg_color"><?php _e( 'Wrapper Background Color', 'mec' ); ?></label>
			<input type="text" class="mec-col-4 mec-color-picker wp-color-picker-field"
					id="mec_skin_full_calendar_wrapper_bg_color" name="mec[sk-options][full_calendar][wrapper_bg_color]"
					value="<?php echo( ( isset( $sk_options_full_calendar['wrapper_bg_color'] ) and trim( $sk_options_full_calendar['wrapper_bg_color'] ) != '' ) ? $sk_options_full_calendar['wrapper_bg_color'] : '' ); ?>"
					data-default-color="">
		</div>
		<?php
	}

	/**
	 * Custom Options
	 *
	 * @since 1.0.0
	 */
	public function customOptions( $sk_options ) {
        $main = \MEC::getInstance('app.libraries.main');
        $event_fields = $main->get_event_fields()
		?>
		<style>span.mts-width:after {
				content: "Width (px)";
				position: absolute;
				left: 5px;
				bottom: -10px;
				font-size: 12px;
				width: 100%;
				color: #c1c1c1;
				text-align: center;
				font-style: italic;
			}

			span.mts-height:after {
				content: "Height (px)";
				position: absolute;
				left: 5px;
				bottom: -10px;
				font-size: 12px;
				width: 100%;
				color: #c1c1c1;
				text-align: center;
				font-style: italic;
			}

			span.mts-height, span.mts-width {
				position: relative;
				display: inline-block;
			}</style>
		<div class="mec-form-row mec-full_calendar-liquid" id="mec_skin_thumbnail_size_wrap" style="display:none">
			<label class="mec-col-4"
					for="mec_skin_full_calendar_wrapper_bg_color"><?php _e( 'Thumbnail Size', 'mec' ); ?></label>
			<span class="mts-width">
				<input type="text"
						value="<?php echo( isset( $sk_options['thumbnail_size']['width'] ) ? $sk_options['thumbnail_size']['width'] : '' ); ?>"
						placeholder="Width" id="mec_skin_thumbnail_size_width"
						name="mec[sk-options][thumbnail_size][width]">
			</span>
			<span class="mts-height">
				<input type="text"
						value="<?php echo( isset( $sk_options['thumbnail_size']['height'] ) ? $sk_options['thumbnail_size']['height'] : '' ); ?>"
						placeholder="Height" id="mec_skin_thumbnail_size_height"
						name="mec[sk-options][thumbnail_size][height]">
			</span>
		</div>

		<script>
			jQuery(document).ready(function () {
				jQuery(document).on('change', '.mec-skin-options-container[style$="block;"] select[id$="_style"][name^="mec[sk-options]"][name$="[style]"]', function () {
					var skin = jQuery('#mec_skin').val();
					if ((jQuery(this).val() == 'liquid' || jQuery(this).val() == 'liquid-large' || jQuery(this).val() == 'liquid-medium' || jQuery(this).val() == 'liquid-small' || jQuery(this).val() == 'liquid-minimal') && skin != 'daily_view' && skin != 'weekly_view' && skin != 'available_spot') {
						jQuery('#mec_skin_thumbnail_size_wrap').show();
					} else {
						jQuery('#mec_skin_thumbnail_size_wrap').hide();
					}
				})

				jQuery('#mec_skin').on('change', function () {
					var skin = jQuery(this).val();
					setTimeout(() => {
						var style = jQuery('.mec-skin-options-container[style$="block;"] select[id$="_style"][name^="mec[sk-options]"][name$="[style]"]').val();
						if ((style == 'liquid' || jQuery(this).val() == 'liquid-large' || jQuery(this).val() == 'liquid-medium' || jQuery(this).val() == 'liquid-small' || jQuery(this).val() == 'liquid-minimal') && skin != 'daily_view' && skin != 'weekly_view' && skin != 'available_spot') {
							jQuery('#mec_skin_thumbnail_size_wrap').show();
						} else {
							jQuery('#mec_skin_thumbnail_size_wrap').hide();
						}
					}, 200);
				})

				var skin = jQuery('#mec_skin').val();
				var style = jQuery('.mec-skin-options-container[style$="block;"] select[id$="_style"][name^="mec[sk-options]"][name$="[style]"]').val();
				if ((style == 'liquid' || jQuery(this).val() == 'liquid-large' || jQuery(this).val() == 'liquid-medium' || jQuery(this).val() == 'liquid-small' || jQuery(this).val() == 'liquid-minimal') && skin != 'daily_view' && skin != 'weekly_view' && skin != 'available_spot') {
					jQuery('#mec_skin_thumbnail_size_wrap').show();
				} else {
					jQuery('#mec_skin_thumbnail_size_wrap').hide();
				}
			})
		</script>

        <div class="mec-list-liquid mec-form-row mec-switcher" >
			<div class="mec-col-4">
				<label for="display_custom_data_fields"><?php _e( 'Show Custom-Fields', 'mec' ); ?></label>
			</div>
			<div class="mec-col-4">
				<input type="hidden" name="mec[sk-options][list][display_custom_data_fields]" value="0"/>
				<input type="checkbox" name="mec[sk-options][event_fields][display_custom_data_fields]" id="display_custom_data_fields" value="1"
								<?php echo isset( $sk_options['event_fields']['display_custom_data_fields'] ) ? 'checked' : ''; ?>>
				<label for="display_custom_data_fields"></label>
			</div>
        </div>

		<?php
	}

	/**
	 * Filter shortcode builder style options
	 *
	 * @param  array $styles
	 * @param  string $skin
	 *
	 * @return array
	 */
	public static function filter_shortcode_builder_style_options( $styles, $skin ) {

		switch ( $skin ) {
			case 'list':
				$styles['liquid-large']   = __( 'Liquid Large', 'mec-liq' );
				$styles['liquid-medium']  = __( 'Liquid Medium', 'mec-liq' );
				$styles['liquid-small']   = __( 'Liquid Small', 'mec-liq' );
				$styles['liquid-minimal'] = __( 'Liquid Minimal', 'mec-liq' );

				break;
			case 'grid':
				$styles['liquid-large']  = __( 'Liquid Large', 'mec-liq' );
				$styles['liquid-medium'] = __( 'Liquid Medium', 'mec-liq' );
				$styles['liquid-small']  = __( 'Liquid Small', 'mec-liq' );

				break;
			case 'full_calendar':
			case 'daily_view':
			case 'weekly_view':
			case 'map':
			case 'cover':
			case 'available_spot':
			case 'carousel':
			case 'slider':
				$styles['liquid'] = __( 'Liquid', 'mec-liq' );
				break;
		}

		return $styles;
	}


	/**
	 * Register Autoload Files
	 *
	 * @since     1.0.0
	 */
	public static function init() {
		if ( ! class_exists( '\MEC_Liquid\Autoloader' ) ) {
			return;
		}
	}
} // MecLiquid
