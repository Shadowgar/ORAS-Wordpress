<?php

if ( ! defined( 'BDTEP_TITLE' ) ) {
    $white_label_title = get_option( 'ep_white_label_title' );
	define( 'BDTEP_TITLE', $white_label_title );
}

if ( ! defined( 'BDTEP_LO' ) ) {
    $hide_license = get_option( 'ep_white_label_hide_license', false );
    if ( $hide_license ) {
        define( 'BDTEP_LO', true );
    }
}

if ( ! defined( 'BDTEP_HIDE' ) ) {
    $hide_ep = get_option( 'ep_white_label_bdtep_hide', false );
    if ( $hide_ep ) {
        define( 'BDTEP_HIDE', true );
    }
}