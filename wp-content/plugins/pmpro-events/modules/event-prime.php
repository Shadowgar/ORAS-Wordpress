<?php
/**
 * Paid Memberships Pro - Events Prime Module
 * @since 1.1
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( class_exists( 'Eventprime_Event_Calendar_Management' ) ) {

    /**
     * Add "Require Membership" meta box to event edit screen
     */
    function pmpro_events_prime_add_meta_box() {
        add_meta_box(
            'pmpro_event_levels_meta',
            'Require Membership',
            'pmpro_events_prime_page_meta', // callback function
            'em_event',
            'side',
            'high'
        );
    }
    add_action( 'add_meta_boxes', 'pmpro_events_prime_add_meta_box', 20 );

    /**
     * Callback to display the membership levels checkboxes
     */
    function pmpro_events_prime_page_meta( $post ) {
        $levels = get_post_meta($post->ID, '_pmpro_levels', true);
        if(!is_array($levels)) $levels = [];

        $all_levels = pmpro_getAllLevels(false, true);

        echo '<div id="pmpro-memberships-checklist">';
        foreach ( $all_levels as $level ) {
            $checked = in_array($level->id, $levels) ? 'checked' : '';
            echo '<label><input type="checkbox" name="pmpro_levels[]" value="' . esc_attr($level->id) . '" ' . $checked . '> ' . esc_html($level->name) . '</label><br>';
        }
        echo '</div>';

        // Event type restrictions
        $types = get_post_meta($post->ID, '_pmpro_event_types', true);
        if(!is_array($types)) $types = [];
        $event_types = get_terms(['taxonomy'=>'em_event_type','hide_empty'=>false]);
        echo '<hr><strong>Restrict by Event Type:</strong><br>';
        foreach($event_types as $type){
            $checked = in_array($type->term_id, $types) ? 'checked' : '';
            echo '<label><input type="checkbox" name="pmpro_event_types[]" value="'.esc_attr($type->term_id).'" '.$checked.'> '.esc_html($type->name).'</label><br>';
        }
    }

    /**
     * Save membership and type restrictions when event is saved
     */
    function pmpro_events_prime_save_meta_box( $post_id ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        // Membership levels
        if ( ! empty( $_POST['pmpro_levels'] ) && is_array( $_POST['pmpro_levels'] ) ) {
            $levels = array_map('intval', $_POST['pmpro_levels']);
            update_post_meta($post_id, '_pmpro_levels', $levels);
        } else {
            delete_post_meta($post_id, '_pmpro_levels');
        }

        // Event types
        if ( ! empty( $_POST['pmpro_event_types'] ) && is_array( $_POST['pmpro_event_types'] ) ) {
            $types = array_map('intval', $_POST['pmpro_event_types']);
            update_post_meta($post_id, '_pmpro_event_types', $types);
        } else {
            delete_post_meta($post_id, '_pmpro_event_types');
        }
    }
    add_action( 'save_post_em_event', 'pmpro_events_prime_save_meta_box' );

    /**
     * Restrict single event content and calendar GET links
     * Shows popup overlay instead of redirect
     */
    function pmpro_events_prime_check_access() {
        if ( is_singular('em_event') || isset($_GET['event']) ) {

            $event_id = is_singular('em_event') ? get_the_ID() : intval($_GET['event']);
            $levels = get_post_meta($event_id, '_pmpro_levels', true);
            if(!is_array($levels)) $levels = [];
            $user_levels = wp_list_pluck(pmpro_getMembershipLevelsForUser(get_current_user_id()), 'id');

            // If membership is required and user doesn't have access
            if ( !empty($levels) && !array_intersect($levels, $user_levels) ) {

                // Dynamic button URL and text
                if ( is_user_logged_in() ) {
                    $redirect_url = esc_url(get_permalink(get_option('woocommerce_myaccount_page_id')));
                    $button_text = "Go to My Account";
                } else {
                    $redirect_url = esc_url('https://oras.educationalconsultingsolutions.org/registration-information/');
                    $button_text = "Learn How to Become a Member";
                }

                // Inject popup overlay HTML and CSS
                add_action('wp_footer', function() use ($button_text, $redirect_url) {
                    ?>
                    <div id="pmpro-access-overlay" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.75);z-index:99999;display:flex;align-items:center;justify-content:center;">
                        <div style="background:#fff;color:#000;padding:30px;border-radius:8px;max-width:500px;text-align:center;">
                            <p style="font-size:16px;">You must be a member to view this event.</p>
                            <button onclick="window.location.href='<?php echo $redirect_url; ?>';" style="margin-top:15px;padding:10px 20px;font-size:16px;"><?php echo $button_text; ?></button>
                        </div>
                    </div>
                    <?php
                });

                // Hide default content and ticket buttons
                add_filter('the_content', function($content){ return ''; });
                add_filter('ep_ticket_button_html', function($html){ return ''; });
                add_filter('ep_single_ticket_button', function($html){ return ''; });
            }
        }
    }
    add_action('template_redirect','pmpro_events_prime_check_access');
}