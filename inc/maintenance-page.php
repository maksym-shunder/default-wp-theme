<?php

if (!class_exists('ACF')) {
	return;
}

function maintenance_redirect()
{
	$slug = 'maintenance';

	if (current_user_can('manage_options')) {
		return;
	}

	if (is_admin() || strpos($_SERVER['REQUEST_URI'], 'wp-login.php') !== false) {
		return;
	}

	if (is_page($slug)) {
		return;
	}

	wp_redirect(home_url('/' . $slug));
	exit;
}

if (get_field('maintenance_mode', 'option')) {
	add_action('template_redirect', 'maintenance_redirect');
}

if (get_field('disable_payments', 'option')) {
	//Disable woocommerce selling functions
	add_filter('woocommerce_is_purchasable', '__return_false');
	add_filter('woocommerce_variation_is_purchasable', '__return_false');
}