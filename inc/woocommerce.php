<?php

//Disable woocommerce selling functions
if (get_field('disable_payments', 'option')) {
	add_filter('woocommerce_is_purchasable', '__return_false');
	add_filter('woocommerce_variation_is_purchasable', '__return_false');
}

// Move payment methods inside the billing fields group
remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
add_action('woocommerce_checkout_after_customer_details', 'woocommerce_checkout_payment', 25);

function add_woocommerce_support()
{
	add_theme_support('woocommerce');
}

add_action('after_setup_theme', 'add_woocommerce_support');


// enable gutenberg for woocommerce
function activate_gutenberg_product($can_edit, $post_type)
{
	if ($post_type === 'product') {
		$can_edit = true;
	}
	return $can_edit;
}

add_filter('use_block_editor_for_post_type', 'activate_gutenberg_product', 10, 2);


// enable taxonomy fields for woocommerce with gutenberg on
function enable_taxonomy_rest($args)
{
	$args['show_in_rest'] = true;
	return $args;
}

add_filter('woocommerce_taxonomy_args_product_cat', 'enable_taxonomy_rest');
add_filter('woocommerce_taxonomy_args_product_tag', 'enable_taxonomy_rest');


// fragment
function custom_header_add_to_cart_fragment($fragments)
{
	ob_start();
	get_template_part('template-parts/components/basket');
	$fragments['.header__basket'] = ob_get_clean();

	ob_start();
	get_template_part('woocommerce/cart/mini-cart');
	$fragments['.widget_shopping_cart_content'] = ob_get_clean();

	return $fragments;
}

add_filter('woocommerce_add_to_cart_fragments', 'custom_header_add_to_cart_fragment');

// reset password page
add_action('login_form_lostpassword', function () {
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST['user_login'])) {
		wp_redirect(home_url('/reset-password?reset=failed'));
		exit;
	}

	$errors = retrieve_password();

	if (is_wp_error($errors)) {
		wp_redirect(home_url('/reset-password?reset=failed'));
		exit;
	}

	wp_redirect(home_url('/reset-password?reset=success'));
	exit;
});

// login redirect
function custom_login_failed_redirect($username)
{
	$referrer = wp_get_referer();

	if (!empty($referrer) && !str_contains($referrer, 'wp-login') && !str_contains($referrer, 'wp-admin')) {
		wp_redirect(home_url('/customer-login?login=failed'));
		exit;
	}
}

add_action('wp_login_failed', 'custom_login_failed_redirect');

function update_cart_item_quantity()
{
	$cart_item_key = sanitize_text_field($_POST['cart_item_key']);
	$new_qty = intval($_POST['new_qty']);

	if ($cart_item_key && $new_qty >= 0) {
		WC()->cart->set_quantity($cart_item_key, $new_qty, true);
		WC()->cart->calculate_totals();
	}

	ob_start();
	woocommerce_mini_cart();
	$mini_cart_html = ob_get_clean();

	wp_send_json_success([
		'mini_cart' => $mini_cart_html,
	]);
}

add_action('wp_ajax_woocommerce_update_cart_item', 'update_cart_item_quantity');
add_action('wp_ajax_nopriv_woocommerce_update_cart_item', 'update_cart_item_quantity');


add_filter('woocommerce_order_button_text', function ($text) {
	return esc_html__('Place your order', 'checkout');
});
