<?php

use ElementPack\Includes\Builder\Builder_Integration;

/**
 * Template for displaying custom post type archives
 *
 * This template can be overridden by copying it to yourtheme/bdthemes-element-pack/custom/{post_type}/archive.php
 *
 * @package BDThemes Element Pack
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

get_header();

do_action('bdthemes-templates-builder/custom/before-main-content');

if (class_exists('Elementor\Plugin')) {
	echo Elementor\Plugin::instance()->frontend->get_builder_content(Builder_Integration::instance()->current_template_id, false);
}

do_action('bdthemes-templates-builder/custom/after-main-content');

get_footer();
