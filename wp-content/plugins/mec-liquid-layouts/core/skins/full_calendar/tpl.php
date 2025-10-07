<?php
/** no direct access **/
defined('MECEXEC') or die();

// Inclue OWL Assets
$this->main->load_owl_assets();

// Generating javascript code tpl
$javascript = '<script type="text/javascript">
jQuery(document).ready(function($)
{
    jQuery("#mec_full_calendar_container_' . $this->id . '").mecFullCalendarLiquid(
    {
        id: "' . $this->id . '",
        atts: "' . http_build_query(array('atts' => $this->atts), '', '&') . '",
        ajax_url: "' . admin_url('admin-ajax.php', NULL) . '",
        sed_method: "' . $this->sed_method . '",
        image_popup: "' . $this->image_popup . '",
        sf:
        {
            container: "' . ($this->sf_status ? '#mec_search_form_' . $this->id : '') . '",
        },
        skin: "' . $this->default_view . '",
    });
});
</script>';

// Include javascript code into the page
if ($this->main->is_ajax()) echo $javascript;
else $this->factory->params('footer', $javascript);

$styling = $this->main->get_styling();
$event_colorskin = (isset($styling['mec_colorskin']) || isset($styling['color'])) ? 'colorskin-custom' : '';
do_action('mec_start_skin', $this->id);
do_action('mec_full_skin_head');

?>

<?php if (isset($this->skin_options['wrapper_bg_color']) and trim($this->skin_options['wrapper_bg_color'])) { ?>
    <div class="mec-liquid-bg-wrap" style="background-color: <?php echo esc_attr($this->skin_options['wrapper_bg_color']); ?>">
<?php } ?>

    <div id="mec_skin_<?php echo $this->id; ?>"
         class="mec-wrap mec-liquid-wrap mec-skin-full-calendar-container <?php echo $event_colorskin; ?>">
       
        <div id="mec_full_calendar_container_<?php echo $this->id; ?>">
            <?php echo $this->load_skin($this->default_view); ?>
            <?php echo $this->subscribe_to_calendar(); ?>
        </div>
    </div>

<?php if (isset($this->skin_options['wrapper_bg_color']) and trim($this->skin_options['wrapper_bg_color'])) { ?>
    </div>
<?php } ?>
<script>
jQuery(document).ready(function($) {
    $(document).on('click', '.mec-subscribe-to-calendar-btn', function(e) {
        e.preventDefault();
        var $container = $(this).closest('.mec-subscribe-to-calendar-container');
        var $items = $container.find('.mec-subscribe-to-calendar-items');
        if ($items.is(':visible')) {
            $items.hide();
        } else {
            $items.show();
        }
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('.mec-subscribe-to-calendar-container').length) {
            $('.mec-subscribe-to-calendar-items').hide();
        }
    });
});
</script>