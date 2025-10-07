<?php
/**
 * ORAS Theme Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package ORAS Theme
 * @since 1.0.0
 */

/**
 * Define Constants
 */
$oras_theme = wp_get_theme();
define( 'CHILD_THEME_ORAS_THEME_VERSION', $oras_theme->get( 'Version' ) ?: '1.0.0' );

/**
 * Enqueue styles
 */
function child_enqueue_styles() {
        wp_enqueue_style(
                'oras-theme-style',
                get_stylesheet_uri(),
                array( 'astra-theme-css' ),
                CHILD_THEME_ORAS_THEME_VERSION
        );
}

add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );

function enqueue_starfield_script() {
    wp_enqueue_script( 'starfield', get_stylesheet_directory_uri() . '/js/starfield.js', array(), '1.0', true );
}
add_action( 'wp_enqueue_scripts', 'enqueue_starfield_script' );


// Remove Astra page titles site-wide
add_filter( 'astra_the_title_enabled', '__return_false' );



// Append PMPro invoices under WooCommerce Orders
function oras_add_pmpro_invoices_to_orders() {
    if ( function_exists( 'pmpro_getMemberInvoices' ) ) {
        echo '<h3>Your Membership Invoices</h3>';
        echo do_shortcode('[pmpro_invoices]');
    }
}
add_action( 'woocommerce_account_orders_endpoint', 'oras_add_pmpro_invoices_to_orders' );


// Prevent WooCommerce scripts/styles from loading on non-WooCommerce pages
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

function dequeue_woocommerce_assets() {
    // Only keep assets on shop, cart, checkout, and account pages
    if ( function_exists( 'is_woocommerce' ) && 
         !is_woocommerce() && 
         !is_cart() && 
         !is_checkout() && 
         !is_account_page() ) {

        // Dequeue WooCommerce scripts
        wp_dequeue_script( 'wc-cart-fragments' );
        wp_dequeue_script( 'woocommerce' );
        wp_dequeue_script( 'wc-add-to-cart' );
        wp_dequeue_script( 'jquery-blockui' );
        wp_dequeue_script( 'wc-checkout' );
        wp_dequeue_script( 'wc-add-to-cart-variation' );
        wp_dequeue_script( 'woocommerce-inline' );
        wp_dequeue_script( 'woocommerce_admin' );

        // Dequeue WooCommerce styles
        wp_dequeue_style( 'woocommerce-general' );
        wp_dequeue_style( 'woocommerce-layout' );
        wp_dequeue_style( 'woocommerce-smallscreen' );
        wp_dequeue_style( 'woocommerce_frontend_styles' );
        wp_dequeue_style( 'woocommerce_fancybox_styles' );
        wp_dequeue_style( 'woocommerce_chosen_styles' );
        wp_dequeue_style( 'woocommerce_prettyPhoto_css' );
    }
}
add_action( 'wp_enqueue_scripts', 'dequeue_woocommerce_assets', 99 );


function oras_pmpro_days_left_multiple() {
    if ( is_user_logged_in() ) {
        global $current_user;
        $user_id = $current_user->ID;
        $levels = pmpro_getMembershipLevelsForUser($user_id);

        if(!empty($levels)) {
            $output = '';
            foreach($levels as $level) {
                if(!empty($level->enddate)) {
                    // Convert UNIX timestamp to readable date
                    $end_date = is_numeric($level->enddate) ? $level->enddate : strtotime($level->enddate);
                    $today = time();
                    $days_left = ceil(($end_date - $today)/DAY_IN_SECONDS);

                    $formatted_date = date('F j, Y', $end_date);

                    $output .= "Membership <strong>{$level->name}</strong><br>";
                    $output .= "Renewal Date: <strong>{$formatted_date}</strong><br>";

                    if($days_left > 0) {
                        $output .= "Membership <strong>{$level->name}</strong> expires in $days_left day" . ($days_left > 1 ? 's' : '') . ".<br><br>";
                    } else {
                        $output .= "Membership <strong>{$level->name}</strong> has expired. Please renew!<br><br>";
                    }
                }
            }
            return $output;
        }
    }
    return "";
}
add_shortcode('pmpro_multiple_memberships', 'oras_pmpro_days_left_multiple');




function oras_register_header_widget() {
    register_sidebar( array(
        'name'          => 'Header Login Widget',
        'id'            => 'header-login-widget',
        'before_widget' => '<div class="oras-header-widget">',
        'after_widget'  => '</div>',
        'before_title'  => '',
        'after_title'   => '',
    ) );
}

// Shortcode for login/register button
function oras_login_button_shortcode() {
    ob_start();
    if ( is_user_logged_in() ) {
        $current_user = wp_get_current_user();
        ?>
        <a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="oras-login-btn">
            Hello, <?php echo esc_html( $current_user->display_name ); ?>
        </a>
        <?php
    } else {
        ?>
        <a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="oras-login-btn">
            Log In
        </a>
        <?php
    }
    return ob_get_clean();
}
add_shortcode('oras_login_button', 'oras_login_button_shortcode');

/**
 * Determine whether the current request should load Modern Events Calendar overrides.
 *
 * @param WP_Post|null $post Optional post object for shortcode/block detection.
 * @return bool
 */
/**
 * Check if Elementor data contains Modern Events Calendar widgets or shortcodes.
 *
 * @param array $elements          Elementor element data.
 * @param array $mec_shortcodes    List of MEC shortcode slugs to search for.
 * @param array $checked_templates Template IDs that have already been inspected.
 *
 * @return bool
 */
function oras_theme_elementor_elements_have_mec( array $elements, array $mec_shortcodes, array &$checked_templates ) {
    foreach ( $elements as $element ) {
        if ( ! is_array( $element ) ) {
            continue;
        }

        $settings = array();
        if ( isset( $element['settings'] ) && is_array( $element['settings'] ) ) {
            $settings = $element['settings'];
        }

        if ( isset( $element['elType'], $element['widgetType'] ) && 'widget' === $element['elType'] ) {
            $widget_type = strtolower( (string) $element['widgetType'] );
            if ( false !== strpos( $widget_type, 'mec' ) ) {
                return true;
            }
        }

        $string_settings_keys = array( 'shortcode', 'shortcode_content', 'editor', 'custom_html', 'html', 'content', 'text' );
        foreach ( $string_settings_keys as $key ) {
            if ( empty( $settings[ $key ] ) || ! is_string( $settings[ $key ] ) ) {
                continue;
            }

            foreach ( $mec_shortcodes as $shortcode ) {
                if ( has_shortcode( $settings[ $key ], $shortcode ) ) {
                    return true;
                }
            }
        }

        // Recursively inspect nested settings that may contain shortcode definitions.
        foreach ( $settings as $value ) {
            if ( is_array( $value ) && oras_theme_elementor_settings_have_mec( $value, $mec_shortcodes ) ) {
                return true;
            }
        }

        $template_ids = array();
        $template_keys = array( 'template_id', 'templateID', 'template_ids', 'templateId' );
        foreach ( $template_keys as $template_key ) {
            if ( ! empty( $element[ $template_key ] ) ) {
                $template_ids = array_merge( $template_ids, (array) $element[ $template_key ] );
            }
            if ( ! empty( $settings[ $template_key ] ) ) {
                $template_ids = array_merge( $template_ids, (array) $settings[ $template_key ] );
            }
        }

        if ( isset( $settings['_global_widget_id'] ) ) {
            $template_ids[] = $settings['_global_widget_id'];
        }

        if ( ! empty( $template_ids ) ) {
            $template_ids = array_map( 'absint', $template_ids );
            $template_ids = array_filter( array_unique( $template_ids ) );

            foreach ( $template_ids as $template_id ) {
                if ( in_array( $template_id, $checked_templates, true ) ) {
                    continue;
                }

                $checked_templates[] = $template_id;

                $template_post = get_post( $template_id );
                if ( $template_post instanceof WP_Post && oras_theme_elementor_post_has_mec( $template_post, $mec_shortcodes, $checked_templates ) ) {
                    return true;
                }
            }
        }

        if ( isset( $element['elements'] ) && is_array( $element['elements'] ) ) {
            if ( oras_theme_elementor_elements_have_mec( $element['elements'], $mec_shortcodes, $checked_templates ) ) {
                return true;
            }
        }
    }

    return false;
}

/**
 * Recursively inspect Elementor settings for MEC shortcodes.
 *
 * @param array $settings        Elementor settings array.
 * @param array $mec_shortcodes List of MEC shortcode slugs to search for.
 *
 * @return bool
 */
function oras_theme_elementor_settings_have_mec( array $settings, array $mec_shortcodes ) {
    foreach ( $settings as $value ) {
        if ( is_string( $value ) ) {
            foreach ( $mec_shortcodes as $shortcode ) {
                if ( has_shortcode( $value, $shortcode ) ) {
                    return true;
                }
            }
        } elseif ( is_array( $value ) && oras_theme_elementor_settings_have_mec( $value, $mec_shortcodes ) ) {
            return true;
        }
    }

    return false;
}

/**
 * Check if a post built with Elementor includes MEC widgets/shortcodes.
 *
 * @param WP_Post $post            Post to inspect.
 * @param array   $mec_shortcodes  List of MEC shortcode slugs to search for.
 * @param array   $checked_templates Reference array of template IDs already processed.
 *
 * @return bool
 */
function oras_theme_elementor_post_has_mec( WP_Post $post, array $mec_shortcodes, array &$checked_templates ) {
    $raw_data = get_post_meta( $post->ID, '_elementor_data', true );

    if ( empty( $raw_data ) ) {
        return false;
    }

    $elementor_data = $raw_data;

    if ( is_string( $elementor_data ) ) {
        $elementor_data = wp_unslash( $elementor_data );
        $decoded_data   = json_decode( $elementor_data, true );

        if ( JSON_ERROR_NONE === json_last_error() ) {
            $elementor_data = $decoded_data;
        } else {
            $maybe_unserialized = maybe_unserialize( $elementor_data );
            if ( is_array( $maybe_unserialized ) ) {
                $elementor_data = $maybe_unserialized;
            } else {
                return false;
            }
        }
    }

    if ( ! is_array( $elementor_data ) ) {
        return false;
    }

    return oras_theme_elementor_elements_have_mec( $elementor_data, $mec_shortcodes, $checked_templates );
}

function oras_theme_mec_context_active( $post = null ) {
    if ( ! class_exists( 'MEC' ) ) {
        return false;
    }

    if ( is_singular( 'mec-events' ) || is_post_type_archive( 'mec-events' ) ) {
        return true;
    }

    if ( is_tax( array( 'mec_category', 'mec_location', 'mec_organizer', 'mec_tag' ) ) ) {
        return true;
    }

    if ( null === $post ) {
        $post = get_queried_object();
    }

    if ( ! $post instanceof WP_Post && isset( $GLOBALS['post'] ) && $GLOBALS['post'] instanceof WP_Post ) {
        // Fallback for contexts where wp_enqueue_scripts fires before the main $post is primed.
        $post = $GLOBALS['post'];
    }

    if ( ! $post instanceof WP_Post ) {
        return false;
    }

    $mec_shortcodes = array( 'MEC', 'mec', 'modern_events_calendar', 'MEC_fes_form', 'MEC_single_builder' );
    foreach ( $mec_shortcodes as $shortcode ) {
        if ( has_shortcode( $post->post_content, $shortcode ) ) {
            return true;
        }
    }

    if ( function_exists( 'has_block' ) ) {
        $mec_blocks = array( 'mec/events', 'mec/calendar', 'mec/single', 'mec/shortcode' );
        foreach ( $mec_blocks as $block_name ) {
            if ( has_block( $block_name, $post ) ) {
                return true;
            }
        }
    }

    $checked_templates = array( $post->ID );
    if ( oras_theme_elementor_post_has_mec( $post, $mec_shortcodes, $checked_templates ) ) {
        return true;
    }

    return false;
}

/**
 * Enqueue Modern Events Calendar overrides within the child theme.
 */
function oras_theme_enqueue_mec_overrides() {
    if ( ! oras_theme_mec_context_active() ) {
        return;
    }

    $deps = array();

    if ( wp_style_is( 'mec-style', 'enqueued' ) || wp_style_is( 'mec-style', 'registered' ) ) {
        $deps[] = 'mec-style';
    }

    wp_enqueue_style(
        'oras-mec-custom',
        get_stylesheet_directory_uri() . '/oras-mec.css',
        $deps,
        CHILD_THEME_ORAS_THEME_VERSION
    );
}
add_action( 'wp_enqueue_scripts', 'oras_theme_enqueue_mec_overrides', 100 );
