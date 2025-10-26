<?php

if (!defined('ABSPATH')) exit;

add_action('after_setup_theme', 'custom_template_register_menus');
function custom_template_register_menus()
{
	register_nav_menus(array(
		'main-menu'   => esc_html__('Main Menu'),
		'menu-footer' => esc_html__('Footer Menu'),
	));
}

add_filter('upload_mimes', 'add_file_types_to_uploads');
function add_file_types_to_uploads($file_types)
{
	$new_filetypes = array();
	$new_filetypes['svg'] = 'image/svg+xml';
	$file_types = array_merge($file_types, $new_filetypes);
	return $file_types;
}

// remove block-library styles
add_action('wp_enqueue_scripts', function () {
	wp_dequeue_style('wp-block-library');
	wp_dequeue_style('wp-block-library-theme');
	wp_dequeue_style('wc-block-style');
}, 100);