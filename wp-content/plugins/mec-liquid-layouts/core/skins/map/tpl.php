<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_skin_map $this */

$search_path = MECLIQUIDDIR . "core/skins/common/search.php";
$date_path = MECLIQUIDDIR . "core/skins/common/date.php";

// MEC Settings
$settings = $this->main->get_settings();
$settings['view_mode'] = isset($this->atts['location_view_mode']) ? $this->atts['location_view_mode'] : 'normal';
$settings['map'] = isset($settings['default_maps_view']) ? $settings['default_maps_view'] : 'google';

$_1year_before = strtotime('first day of -1 year', strtotime($this->start_date));
$_1month_before = strtotime('first day of -1 month', strtotime($this->start_date));
$_1month_after = strtotime('first day of +1 month', strtotime($this->start_date));
$_1year_after = strtotime('first day of +1 year', strtotime($this->start_date));

// Current month time
$current_month_time = strtotime($this->start_date);


$navigator_html = '';
$date_picker_input_id = 'mec-liquid-month-picker-' . date('Y', $current_month_time) . '-' . date('m', $current_month_time);
$current_date_html = ( $this->next_previous_button ? '<input type="text" id="' . $date_picker_input_id . '" class="mec-liquid-month-picker" value="' . date_i18n('F Y', $current_month_time) . '" />' : '' )
    . '<div class="mec-calendar-header"><label for="' . $date_picker_input_id . '"><h2>'
    . '<div class="mec-current-date mec-load-month" data-mec-year="'.date('Y', $current_month_time).'" data-mec-month="'.date('m',$current_month_time).'"></div>'
    . date_i18n('F Y', $current_month_time)
.'</h2></label></div>';
// Generate Month Navigator

if ($this->next_previous_button) {

    // Show previous month handler if showing past events allowed
    if (!isset($this->atts['show_past_events']) or
       (isset($this->atts['show_past_events']) and $this->atts['show_past_events']) or
       (isset($this->atts['show_past_events']) and !$this->atts['show_past_events'] and strtotime(date('Y-m-t', $_1year_before)) >= time())
    ) {
        $navigator_html .= '<div class="mec-previous-year mec-load-month" data-mec-year="'.date('Y', $_1year_before).'" data-mec-month="'.date('m', $_1year_before).'"><i class="mec-fa-angle-double-left"></i></div>';
    }

    if (!isset($this->atts['show_past_events']) or
       (isset($this->atts['show_past_events']) and $this->atts['show_past_events']) or
       (isset($this->atts['show_past_events']) and !$this->atts['show_past_events'] and strtotime(date('Y-m-t', $_1month_before)) >= time())
    ) {
        $navigator_html .= '<div class="mec-previous-month mec-load-month" data-mec-year="'.date('Y', $_1month_before).'" data-mec-month="'.date('m', $_1month_before).'"><i class="mec-fa-angle-left"></i></div>';
    }

    $navigator_html .= $current_date_html;

    // Show next month handler if needed
    if (isset($this->maximum_date) && $this->maximum_date && strtotime($this->maximum_date) < $_1month_after) {
        $navigator_html .= '';
    } else {
        if (!$this->show_only_expired_events or
           ($this->show_only_expired_events and strtotime(date('Y-m-01', $_1month_after)) <= time())
        ) {
            $navigator_html .= '<div class="mec-next-month mec-load-month" data-mec-year="'.date('Y', $_1month_after).'" data-mec-month="'.date('m', $_1month_after).'"><i class="mec-fa-angle-right"></i></div>';
        }
    }

    if (isset($this->maximum_date) && $this->maximum_date && strtotime($this->maximum_date) < $_1year_after) {
        $navigator_html .= '';
    } else {
        if (!$this->show_only_expired_events or
           ($this->show_only_expired_events and strtotime(date('Y-m-01', $_1year_after)) <= time())
        ) {
            $navigator_html .= '<div class="mec-next-year mec-load-month" data-mec-year="'.date('Y', $_1year_after).'" data-mec-month="'.date('m', $_1year_after).'"><i class="mec-fa-angle-double-right"></i></div>';
        }
    }
}

// Return the data if called by AJAX
if(isset($this->atts['return_items']) and $this->atts['return_items'])
{
    echo json_encode(array(
        'count'=>$this->found,
        'markers' => $this->render->markers($this->events, $this->style),
        'current_month_divider'=>"0",
        'month'=> $month_html,
        'navigator'=> $navigator_html,
        'previous_year'=>array('label'=>date_i18n('Y F', $_1year_before), 'id'=>date('Ym', $_1year_before), 'year'=>date('Y', $_1year_before), 'month'=>date('m', $_1year_before)),
        'previous_month'=>array('label'=>date_i18n('Y F', $_1month_before), 'id'=>date('Ym', $_1month_before), 'year'=>date('Y', $_1month_before), 'month'=>date('m', $_1month_before)),
        'current_month'=>array('label'=>date_i18n('Y F', $current_month_time), 'id'=>date('Ym', $current_month_time), 'year'=>date('Y', $current_month_time), 'month'=>date('m', $current_month_time)),
        'next_month'=>array('label'=>date_i18n('Y F', $_1month_after), 'id'=>date('Ym', $_1month_after), 'year'=>date('Y', $_1month_after), 'month'=>date('m', $_1month_after)),
        'next_year'=>array('label'=>date_i18n('Y F', $_1year_after), 'id'=>date('Ym', $_1year_after), 'year'=>date('Y', $_1year_after), 'month'=>date('m', $_1year_after)),
    ));
    exit;
}

$events_data = $this->render->markers($this->events, $this->style);
if(count($this->events))
{
    // Include Map Assets such as JS and CSS libraries
    $this->main->load_map_assets(false, $settings);

    $javascript = '<script>
    jQuery(document).ready(function(){

        jQuery("#mec_map_canvas'.esc_js($this->id).'").mecGoogleMaps({
            id: "'.esc_js($this->id).'",
            atts: "'.http_build_query(array('atts' => $this->atts), '', '&').'",
            zoom: '.(isset($settings['google_maps_zoomlevel']) ? esc_js($settings['google_maps_zoomlevel']) : 14).',
            icon: "'.apply_filters('mec_marker_icon', $this->main->asset('img/m-04.png')).'",
            styles: '.((isset($settings['google_maps_style']) and trim($settings['google_maps_style']) != '') ? $this->main->get_googlemap_style($settings['google_maps_style']) : "''").',
            fullscreen_button: '.((isset($settings['google_maps_fullscreen_button']) and trim($settings['google_maps_fullscreen_button'])) ? 'true' : 'false').',
            markers: '.json_encode($events_data).',
            geolocation: '.esc_js($this->geolocation).',
            geolocation_focus: '.esc_js($this->geolocation_focus).',
            clustering_images: "'.esc_js($this->main->asset('img/cluster1/m')).'",
            getDirection: 0,
            ajax_url: "'.admin_url('admin-ajax.php', NULL).'",
            sf: {
                container: "'.($this->sf_status ? '#mec_search_form_'.esc_js($this->id) : '').'",
                reset: '.($this->sf_reset_button ? 1 : 0).',
                refine: '.($this->sf_refine ? 1 : 0).',
            },
            month_navigator: '.($this->next_previous_button ? 1 : 0).',
        });
    });
    </script>';

    $javascript = apply_filters('mec_map_load_script', $javascript, $this, $settings);

    // Include javascript code into the page
    if($this->main->is_ajax() or $this->main->preview()) echo MEC_kses::full($javascript);
    else $this->factory->params('footer', $javascript);
}

do_action('mec_start_skin', $this->id);
do_action('mec_map_skin_head');
?>
<div class="mec-wrap">
    <div class="row">
        <div class="mec-liquid-wrap mec-skin-map-container <?php echo esc_attr($this->html_class); ?>" id="mec_skin_<?php echo esc_attr($this->id); ?>">

            <?php
                if ($this->sf_status) {

                    include $search_path;
                }

                // include $date_path;
            ?>

            <div class="row mec-map-events-wrap">
                <div class="col-sm-7">
                    <?php if(count($this->events)): ?>
                        <div class="mec-googlemap-skin" id="mec_map_canvas<?php echo esc_attr($this->id); ?>" style="height: 600px;">
                            <?php do_action('mec_map_inner_element_tools', $settings); ?>
                        </div>
                    <?php else: ?>
                        <p class="mec-error"><?php esc_html_e('No events found!', 'mec'); ?></p>
                    <?php endif; ?>
                </div>
                <div class="col-sm-5 mec-map-skin-sidebar" id="mec-map-skin-side-<?php echo esc_attr($this->id); ?>"></div>
            </div>
        </div>
    </div>
</div>