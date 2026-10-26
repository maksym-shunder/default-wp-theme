<?php

if (!defined('ABSPATH')) exit;

// Security headers
function digiway_send_security_headers(): void
{
	if (headers_sent()) {
		return;
	}

	header('X-Content-Type-Options: nosniff');
	header('X-Frame-Options: SAMEORIGIN');
	header('Referrer-Policy: strict-origin-when-cross-origin');
	header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

	if (is_ssl()) {
		header('Strict-Transport-Security: max-age=31536000');
	}
}

add_action('send_headers', 'digiway_send_security_headers', 20);

// XML-RPC: disable methods and stop xmlrpc.php before the server handles the request
add_filter('xmlrpc_enabled', '__return_false');
add_filter('xmlrpc_methods', '__return_empty_array');

add_action('init', static function (): void {
	if (!defined('XMLRPC_REQUEST') || !XMLRPC_REQUEST) {
		return;
	}

	status_header(403);
	exit;
}, 0);

add_filter('wp_headers', static function (array $headers): array {
	unset($headers['X-Pingback']);

	return $headers;
});

// Application passwords
add_filter('wp_is_application_passwords_available', '__return_false');

// Remove discovery links and versions from <head> and response headers
add_action('init', static function (): void {
	remove_action('wp_head', 'rsd_link');
	remove_action('wp_head', 'wlwmanifest_link');
	remove_action('wp_head', 'wp_shortlink_wp_head');
	remove_action('wp_head', 'rest_output_link_wp_head');
	remove_action('wp_head', 'wp_oembed_add_discovery_links');
	remove_action('wp_head', 'wp_generator');
	remove_action('template_redirect', 'rest_output_link_header', 11);
	remove_action('template_redirect', 'wp_shortlink_header', 11);
});

add_filter('the_generator', '__return_empty_string');

// Replace core version in ?ver= with a salted hash, so the version is hidden but cache busting still works
function digiway_hide_core_version(string $src): string
{
	$wp_version = get_bloginfo('version');

	if (!str_contains($src, 'ver=' . $wp_version)) {
		return $src;
	}

	return add_query_arg('ver', substr(wp_hash($wp_version), 0, 8), $src);
}

add_filter('style_loader_src', 'digiway_hide_core_version', 20);
add_filter('script_loader_src', 'digiway_hide_core_version', 20);

// Generic message for failed login on wp-login.php (login screen only)
add_filter('wp_login_errors', static function (WP_Error $errors): WP_Error {
	$credential_codes = ['invalid_username', 'invalid_email', 'incorrect_password'];

	if (!array_intersect($errors->get_error_codes(), $credential_codes)) {
		return $errors;
	}

	foreach ($credential_codes as $code) {
		$errors->remove($code);
	}

	$errors->add('invalid_credentials', sprintf(
		'<strong>%s</strong> %s',
		esc_html__('Error:', 'digiway'),
		esc_html__('The login details are incorrect.', 'digiway')
	));

	return $errors;
});

// Author archives (/author/<login>/, ?author=N) expose logins
add_action('template_redirect', static function (): void {
	if (!is_author()) {
		return;
	}

	wp_safe_redirect(home_url('/'), 301);
	exit;
}, 0);

// oEmbed responses expose author name and archive URL
add_filter('oembed_response_data', static function (array $data): array {
	unset($data['author_name'], $data['author_url']);

	return $data;
});

// REST: hide /wp/v2/users* routes from guests
add_filter('rest_endpoints', static function (array $endpoints): array {
	if (is_user_logged_in()) {
		return $endpoints;
	}

	foreach (array_keys($endpoints) as $route) {
		if (str_starts_with($route, '/wp/v2/users')) {
			unset($endpoints[$route]);
		}
	}

	return $endpoints;
});

// SVG uploads: only for users with unfiltered_html, and only files that pass the check
function digiway_is_safe_svg_file(string $file): bool
{
	if (!is_readable($file) || filesize($file) > 1024 * 1024) {
		return false;
	}

	$contents = file_get_contents($file);

	if (!is_string($contents) || false === stripos($contents, '<svg')) {
		return false;
	}

	$blocked_patterns = [
		'/<\?(?:php|=)/i',
		'/<script\b/i',
		'/\son[a-z]+\s*=/i',
		'/javascript\s*:/i',
		'/data\s*:\s*text\/html/i',
		'/<foreignObject\b/i',
		'/<iframe\b/i',
		'/<object\b/i',
		'/<embed\b/i',
	];

	foreach ($blocked_patterns as $pattern) {
		if (preg_match($pattern, $contents)) {
			return false;
		}
	}

	return true;
}

add_filter('upload_mimes', static function (array $mimes): array {
	if (!current_user_can('unfiltered_html')) {
		unset($mimes['svg'], $mimes['svgz']);
		return $mimes;
	}

	$mimes['svg'] = 'image/svg+xml';

	return $mimes;
}, 20);

add_filter('wp_handle_upload_prefilter', static function (array $file): array {
	$filename = isset($file['name']) ? (string) $file['name'] : '';

	if (!preg_match('/\.svgz?$/i', $filename)) {
		return $file;
	}

	$tmp_name = isset($file['tmp_name']) ? (string) $file['tmp_name'] : '';

	if (!current_user_can('unfiltered_html') || !digiway_is_safe_svg_file($tmp_name)) {
		$file['error'] = esc_html__('This SVG file is not allowed for security reasons.', 'digiway');
	}

	return $file;
});

add_filter('wp_check_filetype_and_ext', static function (array $data, string $file, string $filename): array {
	if (!preg_match('/\.svg$/i', $filename)) {
		return $data;
	}

	if (!current_user_can('unfiltered_html') || !digiway_is_safe_svg_file($file)) {
		return [
			'ext' => false,
			'type' => false,
			'proper_filename' => false,
		];
	}

	$data['ext'] = 'svg';
	$data['type'] = 'image/svg+xml';

	return $data;
}, 10, 3);
