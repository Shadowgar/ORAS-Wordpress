<?php
/** no direct access **/
defined('MECEXEC') or die();

?>

<div id="mec_search_form_<?php echo esc_attr($this->id) ?>" class="mec-search-form mec-totalcal-box">
    <div class="box-search">
        <?php
        if ( 'text_input' === $this->sf_options['text_search']['type'] ) {
            ?>
            <?php echo $this->sf_search_field( 'text_search', $this->sf_options['text_search'], false ) ?>
        <?php } ?>

        <div class="mec-event-statuses">
            <ul class="type-event"
                style="<?php echo $this->sf_options['text_search']['type'] == 0 ? "padding-inline-start: 0px;" : ""; ?>">
                <li class="active">
                    <label>
                        <input type="radio" name="mec_sf_event_status_<?php echo $this->id ?>" class="mec_sf_event_status_<?php echo $this->id ?>" value="all" checked="checked" />
                        <?php esc_html_e('All Events', 'mec-liq'); ?>
                    </label>
                </li>
                <li>
                    <label>
                        <input type="radio" name="mec_sf_event_status_<?php echo $this->id ?>" class="mec_sf_event_status_<?php echo $this->id ?>" value="EventScheduled" />
                        <?php esc_html_e('Scheduled', 'mec-liq'); ?>
                    </label>
                </li>
                <li>
                    <label>
                        <input type="radio" name="mec_sf_event_status_<?php echo $this->id ?>" class="mec_sf_event_status_<?php echo $this->id ?>" value="EventPostponed" />
                        <?php esc_html_e('Postponed', 'mec-liq'); ?>
                    </label>
                </li>
                <li>
                    <label>
                        <input type="radio" name="mec_sf_event_status_<?php echo $this->id ?>" class="mec_sf_event_status_<?php echo $this->id ?>" value="EventCancelled" />
                        <?php esc_html_e('Cancelled', 'mec-liq'); ?>
                    </label>
                </li>
                <li>
                    <label>

                        <input type="radio" name="mec_sf_event_status_<?php echo $this->id ?>" class="mec_sf_event_status_<?php echo $this->id ?>" value="EventMovedOnline" />
                        <?php esc_html_e('Moved Online', 'mec-liq'); ?>
                    </label>
                </li>
            </ul>
        </div>

        <div class="mec-filters-wrap">
            <?php
            $field_options = is_array( $this->sf_options ) ? $this->sf_options : array();
            unset( $field_options['text_search'] );

            $display_style = $fields = $end_div = '';
            $first_row = 'not-started';
            $display_form = array();


            foreach($field_options as $field=>$options){
                // Event Fields is disabled
                if($field === 'fields' and (!isset($this->settings['display_event_fields_search']) or (isset($this->settings['display_event_fields_search']) and !$this->settings['display_event_fields_search']))) continue;

                $display_form[] = (isset($options['type']) ? $options['type'] : ($field === 'fields' ? 'fields' : NULL));
                $fields_array = array('category', 'location', 'organizer', 'speaker', 'tag', 'label');
                $fields_array = apply_filters('mec_filter_fields_search_array', $fields_array);

                $field_html = '';
                $field_html_option = $this->sf_search_field($field, $options, $this->sf_display_label);
                if( in_array($field, $fields_array) && !empty( $field_html_option ) ){

                    if($this->sf_options['category']['type'] != 'dropdown' and $this->sf_options['category']['type'] != 'checkboxes' and $this->sf_options['location']['type'] != 'dropdown' and $this->sf_options['organizer']['type'] != 'dropdown' and (isset($this->sf_options['speaker']['type']) && $this->sf_options['speaker']['type'] != 'dropdown') and (isset($this->sf_options['tag']['type']) && $this->sf_options['tag']['type'] != 'dropdown') and  $this->sf_options['label']['type'] != 'dropdown'){

                        $display_style = 'style="display: none;"';
                    }

                    $field_html .= '<div class="mec-dropdown-wrap" ' . $display_style . '>';

                    $field_html .= $field_html_option;

                    $field_html .= '</div>';
                }else{

                    $field_html .= $field_html_option;
                }

                if( !empty( $field_html ) ){

                    $fields .= $field_html;
                }
            }

            $field_options_html = apply_filters('mec_filter_fields_search_form', $fields, $this);

            if( !empty( $field_options_html ) ):

                ?>
                <a class="btn-filter">
                    <div>
                        <img src="<?php echo esc_url(MECLIQUIDDASSETS . 'images/ic_filter.svg'); ?>" class="ic-filter" alt="">
                        <?php esc_html_e( 'Filter', 'mec-liq' ); ?>
                    </div>
                </a>
                <div class="box-filter">
                    <?php echo $field_options_html; ?>
                </div>
            <?php endif; ?>

            <?php echo $this->sf_reset_button == 1 ? "<a class='btn-reset' id='mec_search_form_" . esc_attr($this->id) . "_reset'>" . esc_html__( 'Reset', 'mec-liq' ) . "</a>" : ""; ?>
        </div>
    </div>
</div>
