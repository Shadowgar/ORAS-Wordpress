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
define( 'CHILD_THEME_ORAS_THEME_VERSION', '1.0.0' );

/**
 * Enqueue styles
 */
function child_enqueue_styles() {

	wp_enqueue_style( 'oras-theme-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), CHILD_THEME_ORAS_THEME_VERSION, 'all' );

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