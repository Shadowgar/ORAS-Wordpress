<?php
/** no direct access **/
defined('MECEXEC') or die();
$year = $this->year;
$month = $this->month;
$day = $this->day;

$events = $this->events;

$this->active_day = $year . '-' . $month . '-' . current_time('d');

// Get layout path
$render_path = dirname(__FILE__) . '/render.php';

$search_path = MECLIQUIDDIR . "core/skins/common/search.php";
$date_path = MECLIQUIDDIR . "core/skins/common/date.php";

// before/after Month
$_1year_before = strtotime('first day of -1 year', strtotime($this->start_date));
$_1month_before = strtotime('first day of -1 month', strtotime($this->start_date));
$_1month_after = strtotime('first day of +1 month', strtotime($this->start_date));
$_1year_after = strtotime('first day of +1 year', strtotime($this->start_date));

// Current month time
$current_month_time = strtotime($this->start_date);

$date_labels = $this->get_date_labels();

ob_start();
include $render_path;
$month_html = ob_get_clean();

$navigator_html = '';
$date_picker_input_id = 'mec-liquid-month-picker-' . date('Y', $current_month_time) . '-' . date('m', $current_month_time);
$current_date_html = '<input  type="text" id="' . $date_picker_input_id . '" class="mec-liquid-month-picker" value="' . date_i18n('F Y', $current_month_time) . '" />'
    . '<div class="mec-calendar-header"><label for="' . $date_picker_input_id . '"><h2>'
    . '<div class="mec-current-date mec-load-month" data-mec-year="' . date('Y', $current_month_time) . '" data-mec-month="' . date('m', $current_month_time) . '"></div>'
    . date_i18n('F Y', $current_month_time)
    . '</h2></label></div>';
// Generate Month Navigator

if ($this->next_previous_button) {

    // Show previous month handler if showing past events allowed
    if (!isset($this->atts['show_past_events']) or
        (isset($this->atts['show_past_events']) and $this->atts['show_past_events']) or
        (isset($this->atts['show_past_events']) and !$this->atts['show_past_events'] and strtotime(date('Y-m-t', $_1year_before)) >= time())
    ) {
        $navigator_html .= '<div class="mec-previous-year mec-load-month" data-mec-year="' . date('Y', $_1year_before) . '" data-mec-month="' . date('m', $_1year_before) . '"><i class="mec-fa-angle-double-left"></i></div>';
    }

    if (!isset($this->atts['show_past_events']) or
        (isset($this->atts['show_past_events']) and $this->atts['show_past_events']) or
        (isset($this->atts['show_past_events']) and !$this->atts['show_past_events'] and strtotime(date('Y-m-t', $_1month_before)) >= time())
    ) {
        $navigator_html .= '<div class="mec-previous-month mec-load-month" data-mec-year="' . date('Y', $_1month_before) . '" data-mec-month="' . date('m', $_1month_before) . '"><i class="mec-fa-angle-left"></i></div>';
    }

    $navigator_html .= $current_date_html;

    // Show next month handler if needed
    if (isset($this->maximum_date) && $this->maximum_date && strtotime($this->maximum_date) < $_1month_after) {
        $navigator_html .= '';
    } else {
        if (!$this->show_only_expired_events or
            ($this->show_only_expired_events and strtotime(date('Y-m-01', $_1month_after)) <= time())
        ) {
            $navigator_html .= '<div class="mec-next-month mec-load-month" data-mec-year="' . date('Y', $_1month_after) . '" data-mec-month="' . date('m', $_1month_after) . '"><i class="mec-fa-angle-right"></i></div>';
        }
    }

    if (isset($this->maximum_date) && $this->maximum_date && strtotime($this->maximum_date) < $_1year_after) {
        $navigator_html .= '';
    } else {
        if (!$this->show_only_expired_events or
            ($this->show_only_expired_events and strtotime(date('Y-m-01', $_1year_after)) <= time())
        ) {
            $navigator_html .= '<div class="mec-next-year mec-load-month" data-mec-year="' . date('Y', $_1year_after) . '" data-mec-month="' . date('m', $_1year_after) . '"><i class="mec-fa-angle-double-right"></i></div>';
        }
    }
}
$month_html .= '
<div class="mec-date-labels-container mec-calendar-d-table">
    <span>' . esc_html__('Day', 'mec-liq') . '</span>
    '.$date_labels.'
    <a href="#" class="mec-table-d-prev mec-color"><i class="mec-sl-angle-left"></i></a>
    <a href="#" class="mec-table-d-next mec-color"><i class="mec-sl-angle-right"></i></a>
</div>';

// Return the data if called by AJAX
if (isset($this->atts['return_items']) and $this->atts['return_items']) {
    echo json_encode(array(
        'count' => $this->found,
        'current_month_divider' => "0",
        'month' => $month_html,
        'navigator' => $navigator_html,
        'previous_year' => array('label' => date_i18n('Y F', $_1year_before), 'id' => date('Ym', $_1year_before), 'year' => date('Y', $_1year_before), 'month' => date('m', $_1year_before)),
        'previous_month' => array('label' => date_i18n('Y F', $_1month_before), 'id' => date('Ym', $_1month_before), 'year' => date('Y', $_1month_before), 'month' => date('m', $_1month_before)),
        'current_month' => array('label' => date_i18n('Y F', $current_month_time), 'id' => date('Ym', $current_month_time), 'year' => date('Y', $current_month_time), 'month' => date('m', $current_month_time)),
        'next_month' => array('label' => date_i18n('Y F', $_1month_after), 'id' => date('Ym', $_1month_after), 'year' => date('Y', $_1month_after), 'month' => date('m', $_1month_after)),
        'next_year' => array('label' => date_i18n('Y F', $_1year_after), 'id' => date('Ym', $_1year_after), 'year' => date('Y', $_1year_after), 'month' => date('m', $_1year_after)),
    ));
    exit;
}

// Inclue OWL Assets
$this->main->load_owl_assets();

// Generating javascript code tpl
$javascript = '<script type="text/javascript">
jQuery(document).ready(function()
{
    jQuery("#mec_daily_view_month_'.$this->id.'_'.date('Ym', $current_month_time).'").mecDailyView(
    {
        id: "'.$this->id.'",
        today: "'.date('Ymd', strtotime($this->active_day)).'",
        month_id: "'.date('Ym', $current_month_time).'",
        year: "'.date('Y', $current_month_time).'",
        month: "'.date('m', $current_month_time).'",
        day: "'.date('d', strtotime($this->active_day)).'",
        events_label: "'.esc_attr__('Events', 'mec-liq').'",
        event_label: "'.esc_attr__('Event', 'mec-liq').'",
        month_navigator: '.($this->next_previous_button ? 1 : 0).',
        atts: "'.http_build_query(array('atts'=>$this->atts), '', '&').'",
        ajax_url: "'.admin_url('admin-ajax.php', null).'",
        sed_method: "'.$this->sed_method.'",
        image_popup: "'.$this->image_popup.'",
        sf:
        {
            container: "'.($this->sf_status ? '#mec_search_form_'.$this->id : '').'",
        },
    });
});
</script>';

// Include javascript code into the page
if ($this->main->is_ajax()) {
    echo $javascript;
} else {
    $this->factory->params('footer', $javascript);
}

$styling = $this->main->get_styling();
$event_colorskin = (isset($styling['mec_colorskin']) || isset($styling['color'])) ? 'colorskin-custom' : '';
do_action('mec_start_skin', $this->id);
do_action('mec_daily_skin_head');
?>

<?php if (isset($this->skin_options['wrapper_bg_color']) and trim($this->skin_options['wrapper_bg_color'])) { ?>
    <div class="mec-liquid-bg-wrap" style="background-color: <?php echo esc_attr($this->skin_options['wrapper_bg_color']); ?>">
<?php } ?>

<div id="mec_skin_<?php echo $this->id; ?>" class="mec-wrap mec-liquid-wrap mec-skin-daily-wrap <?php echo  $this->html_class; ?>">
    <?php
    if ($this->sf_status) {

        include $search_path;
    }

    include $date_path;
    ?>

    <div class="mec-liquid-daily mec-calendar mec-calendar-daily">
        <div class="mec-skin-daily-view-events-container" id="mec_skin_events_<?php echo $this->id; ?>">
            <div class="mec-month-container mec-calendar-a-day mec-clear" id="mec_daily_view_month_<?php echo $this->id; ?>_<?php echo date('Ym', $current_month_time); ?>">
                <?php echo $month_html; ?>
            </div>
        </div>
    </div>

</div>

<?php if (isset($this->skin_options['wrapper_bg_color']) and trim($this->skin_options['wrapper_bg_color'])) { ?>
    </div>
<?php } ?>