<?php
define( 'darkmfa_version', '1.3' );
add_action( 'wp_enqueue_scripts', 'darkmfa_enqueue_styles', 15 );
function darkmfa_enqueue_styles() {
wp_enqueue_style( 'darkmfa-css', get_stylesheet_directory_uri() . '/style.css', array( 'astra-theme-css' ), darkmfa_version, 'all' );
}

add_action( 'admin_notices', 'darkmfa_notice' );
function darkmfa_notice() {
$user_id = get_current_user_id();
$admin_url = ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http' ) . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$param = ( count( $_GET ) ) ? '&' : '?';
if ( !get_user_meta( $user_id, 'darkmfa_notice_dismissed_3' ) && current_user_can( 'manage_options' ) )
echo '<div class="notice notice-info"><p><a href="' . $admin_url, $param . 'dismiss" class="alignright" style="text-decoration:none"><big>' . esc_html__( 'Ⓧ', 'dark-mode-for-a' ) . '</big></a>' . wp_kses_post( __( '<big><strong>🚀 Want even more features?</strong></big>', 'dark-mode-for-a' ) ) . '<br><br><a href="https://webguy.io/astra" class="button-primary" target="_blank">' . esc_html__( 'Upgrade to Astra Pro', 'dark-mode-for-a' ) . '</a></p></div>';
}

add_action( 'admin_init', 'darkmfa_notice_dismissed' );
function darkmfa_notice_dismissed() {
$user_id = get_current_user_id();
if ( isset( $_GET['dismiss'] ) )
add_user_meta( $user_id, 'darkmfa_notice_dismissed_3', 'true', true );
}