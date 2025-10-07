<?php
/** no direct access **/
defined('MECEXEC') or die();

// Monthpicker Assets
$this->main->load_month_picker_assets();

$nav_html = $this->next_previous_button ? $navigator_html : '<div class="mec-calendar-header">' . $current_date_html .'</div>';
$month_navigator_id = 'mec_month_navigator' . $this->id . '_' . date('Ym', $current_month_time);

if( in_array( $this->skin, array( 'grid', 'list' ) ) ) {

    $month_navigator_id = 'mec_month_navigator' . '_' . $this->id . '_' . date('Ym', $current_month_time);
}
?>
<div class="box-date">
    <div>
        <div class="mec-skin-<?php echo esc_attr( str_replace( '_view', '', $this->skin ) ) ?>-view-month-navigator-container mec-calendar-a-month mec-clear">
            <div class="mec-month-navigator" id="<?php echo $month_navigator_id; ?>">
                <?php echo $nav_html; ?>
            </div>
        </div>
        <?php echo isset($week_html) ? $week_html : ''; ?>
    </div>

    <?php
//    print_r($this->atts['sk-options']['full_calendar']);
     if ($this->atts['skin']=="full_calendar") : ?>
    <div class="mec-totalcal-box type-date-box-liquid">
            <div class="type-date mec-totalcal-view">
                <?php
                //$default_view=$this->atts['sk-options']['full_calendar']['default_view'];
                $default_view=$this->skin;
                $weekly=$this->atts['sk-options']['full_calendar']['weekly'];
                $daily=$this->atts['sk-options']['full_calendar']['daily'];
                $list=$this->atts['sk-options']['full_calendar']['list'];
                $grid=$this->atts['sk-options']['full_calendar']['grid'];
                $skins = [];
                if ($weekly) {
                    $skins['weekly_view'] = 'weekly_view';
                }
                if ($daily) {
                    $skins['daily_view'] = 'daily_view';
                }
                if ($list) {
                    $skins['list'] = 'list';
                }
                if ($grid) {
                    $skins['grid'] = 'grid';
                }
                if ($skins) {
                    switch ($default_view) {
                        case 'weekly_view':
                            if (isset($skins[$default_view])) :
                                echo '<span class="btn-type mec-totalcal-weeklyview mec-totalcalview-selected" data-skin="weekly">' . esc_html__('Weekly', 'mec-liq') . '</span>';
                                unset($skins[$default_view]);
                            endif;
                            break;
                        case 'daily_view':
                            if (isset($skins[$default_view])) :
                                echo '<span class="btn-type mec-totalcal-dailyview mec-totalcalview-selected" data-skin="daily">' . esc_html__('Daily', 'mec-liq') . '</span>';
                                unset($skins[$default_view]);
                            endif;
                            break;
                        case 'list':
                            if (isset($skins[$default_view])) :
                                echo '<span class="btn-type mec-totalcal-listview mec-totalcalview-selected" data-skin="list">' . esc_html__('List', 'mec-liq') . '</span>';
                                unset($skins[$default_view]);
                            endif;
                            break;
                        case 'grid':
                            if (isset($skins[$default_view])) :
                                echo '<span class="btn-type mec-totalcal-gridview mec-totalcalview-selected" data-skin="grid">' . esc_html__('Grid', 'mec-liq') . '</span>';
                                unset($skins[$default_view]);
                            endif;
                            break;
                    }
                }

                if ($skins) {
                    $total_skins = count($skins) + 2; // 1 for list
                    $tab_skins = ($total_skins - 2 > 0) ? $total_skins - 2 : $total_skins;
                    $tab_skins = min([3, $tab_skins]);
                    $i = 1;
                    foreach ($skins as $skin) {
                        if ($i == $tab_skins) {
                            break;
                        }
                        switch ($skin) {
                            case 'weekly_view':
                                if ($weekly) :
                                    echo '<span class="btn-type mec-totalcal-weeklyview" data-skin="weekly">' . esc_html__('Weekly', 'mec-liq') . '</span>';
                                endif;
                                unset($skins[$skin]);
                                break;
                            case 'daily_view':
                                if ($daily) :
                                    echo '<span class="btn-type mec-totalcal-dailyview" data-skin="daily">' . esc_html__('Daily', 'mec-liq') . '</span>';
                                endif;
                                unset($skins[$skin]);
                                break;
                            case 'list':
                                if ($list) :
                                    echo '<span class="btn-type mec-totalcal-listview" data-skin="list">' . esc_html__('List', 'mec-liq') . '</span>';
                                endif;
                                unset($skins[$skin]);
                                break;
                            case 'grid':
                                if ($grid) :
                                    echo '<span class="btn-type mec-totalcal-gridview" data-skin="grid">' . esc_html__('Grid', 'mec-liq') . '</span>';
                                endif;
                                unset($skins[$skin]);
                                break;
                        }
                        $i++;
                    }
                }
                if ($skins) {
                    ?>
                    <a class="btn-type-more">
                        <img src="<?php echo esc_url(MECLIQUIDDASSETS . 'images/ic_more.svg'); ?>"/>
                        <div class="box-more">
                            <ul>
                                <?php
                                foreach ($skins as $skin) {
                                    switch ($skin) {
                                        case 'weekly_view':
                                            if ($weekly) :
                                                echo '<li><span class="btn-type mec-totalcal-weeklyview" data-skin="weekly">' . esc_html__('Weekly', 'mec-liq') . '</span></li>';
                                            endif;
                                            unset($skins[$skin]);
                                            break;
                                        case 'daily_view':
                                            if ($daily) :
                                                echo '<li><span class="btn-type mec-totalcal-dailyview" data-skin="daily">' . esc_html__('Daily', 'mec-liq') . '</span></li>';
                                            endif;
                                            unset($skins[$skin]);
                                            break;
                                        case 'list':
                                            if ($list) :
                                                echo '<li><span class="btn-type mec-totalcal-listview" data-skin="list">' . esc_html__('List', 'mec-liq') . '</span></li>';
                                            endif;
                                            unset($skins[$skin]);
                                            break;
                                        case 'grid':
                                            if ($grid) :
                                                echo '<li><span class="btn-type mec-totalcal-gridview" data-skin="grid">' . esc_html__('Grid', 'mec-liq') . '</span></li>';
                                            endif;
                                            unset($skins[$skin]);
                                            break;
                                    }
                                }
                                ?>
                            </ul>
                        </div>
                    </a>

                <?php } ?>
            </div>
        </div>
        <?php endif; ?>
</div>

