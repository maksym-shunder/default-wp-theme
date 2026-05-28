<?php

add_filter('acf/settings/save_json', function ($path) {
	return get_template_directory() . '/acf-json';
});

add_filter('acf/settings/load_json', function ($paths) {
	$paths[] = get_template_directory() . '/acf-json';
	return $paths;
});

require_once __DIR__ . '/inc/theme.php';

require_once __DIR__ . '/inc/acf/acf.php';

require_once __DIR__ . '/inc/enqueue-scripts.php';

require_once __DIR__ . '/inc/maintenance-page.php';

require_once __DIR__ . '/inc/helpers.php';

require_once __DIR__ . '/inc/woocommerce.php';