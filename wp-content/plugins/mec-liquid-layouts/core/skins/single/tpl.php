<?php
/** no direct access **/
defined('MECEXEC') or die();

$styling = $this->main->get_styling();
$event = $this->events[0];
$event_colorskin = (isset($styling['mec_colorskin']) || isset($styling['color'])) ? 'colorskin-custom' : '';
$settings = $this->main->get_settings();

$single_style = isset($settings['single_single_style']) ? $settings['single_single_style'] : '';

$occurrence = (isset($event->date['start']['date']) ? $event->date['start']['date'] : (isset($_GET['occurrence']) ? sanitize_text_field($_GET['occurrence']) : ''));
$occurrence_end_date = trim($occurrence) ? $this->main->get_end_date_by_occurrence($event->data->ID, (isset($event->date['start']['date']) ? $event->date['start']['date'] : $occurrence)) : '';
$occurrence_full = (isset($event->date['start']) and is_array($event->date['start'])) ? $event->date['start'] : [];
if(!count($occurrence_full) and isset($_GET['occurrence'])) $occurrence_full = array('date' => sanitize_text_field($_GET['occurrence']));

$occurrence_end_full = (isset($event->date['end']) and is_array($event->date['end'])) ? $event->date['end'] : [];
if(!count($occurrence_end_full) and trim($occurrence)) $occurrence_end_full = array('date' => $this->main->get_end_date_by_occurrence($event->data->ID, $occurrence));

$style_per_event = '';
if(isset($this->settings['style_per_event']) and $this->settings['style_per_event'])
{
    $style_per_event = get_post_meta($event->data->ID, 'mec_style_per_event', true);
    if($style_per_event === 'global') $style_per_event = '';
}

$layout = '';
if(isset($this->layout) and trim($this->layout)) $layout = $this->layout;
elseif(trim($style_per_event)) $layout = $style_per_event;

if ( $layout === 'm1' ) include dirname(__FILE__) . '/m1.php';
else include dirname(__FILE__) . '/render.php';
