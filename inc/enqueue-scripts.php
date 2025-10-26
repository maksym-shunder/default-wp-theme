<?php

function auto_enqueue_styles()
{
	$exclude = [
		'header.css',
		'global.css',
	];

	$base_dir = get_stylesheet_directory() . '/assets/css';
	$base_url = get_stylesheet_directory_uri() . '/assets/css';

	if (!is_dir($base_dir)) {
		return;
	}

	$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base_dir, RecursiveDirectoryIterator::SKIP_DOTS));

	foreach ($iterator as $file) {
		if ($file->getExtension() !== 'css') {
			continue;
		}

		$filename = $file->getFilename();

		if (in_array($filename, $exclude)) {
			continue;
		}

		$relative_path = str_replace($base_dir . '/', '', $file->getPathname());
		$handle = 'style-' . sanitize_title(str_replace('/', '-', $relative_path));

		$file_url = $base_url . '/' . str_replace($base_dir . '/', '', $file->getPathname());
		$file_url = str_replace('\\', '/', $file_url);

		wp_enqueue_style($handle, $file_url, [], filemtime($file->getPathname()));
	}
}

add_action('wp_enqueue_scripts', 'theme_scripts');
function theme_scripts()
{
	if (!is_user_logged_in()) {
		wp_deregister_style('dashicons');
	}

	//styles
	wp_enqueue_style('style', get_stylesheet_uri(), array(), null);
	auto_enqueue_styles();


	// uncomment next line to remove jQuery if woocommerce isn't use
	//wp_deregister_script('jquery');

	//scripts
	wp_enqueue_script('main-js', get_template_directory_uri() . '/assets/js/main.js', [], filemtime(get_template_directory() . '/assets/js/main.js'), true);
	wp_enqueue_script('swiper-script', get_template_directory_uri() . '/assets/js/swiper.min.js', [], filemtime(get_template_directory() . '/assets/js/swiper.min.js'), true);

	// remove this if woocommerce isn't use
	wp_enqueue_script('wc-cart-fragments');
	wp_enqueue_script('wc-add-to-cart');
	wp_enqueue_script('cart-js', get_template_directory_uri() . '/assets/js/woocommerce.js', [], filemtime(get_template_directory() . '/assets/js/woocommerce.js'), true);
}