<?php
/**
 * Register acf
 *
 */
add_action('acf/init', 'acf_blocks');
function acf_blocks()
{
	if (!function_exists('acf_add_local_field_group')) {
		return;
	}

	// Option pages
	require_once __DIR__ . '/options-pages-fields/settings-global.php';
	require_once __DIR__ . '/options-pages-fields/settings-header.php';
	require_once __DIR__ . '/options-pages-fields/settings-footer.php';

	// Gutenberg gutenberg-blocks
	$blocks_dir = __DIR__ . '/../../template-parts/gutenberg-blocks';
	$folders = glob($blocks_dir . '/*', GLOB_ONLYDIR);

	if ($folders) {
		foreach ($folders as $folder) {
			$file = $folder . '/index.php';
			if (file_exists($file)) {
				require_once $file;
			}
		}
	}
}

// Preload all banner block styles early in the head
add_action('wp_head', function () {
	global $styles_to_preload;

	if (!empty($styles_to_preload)) {
		foreach (array_unique($styles_to_preload) as $href) {
			printf('<link rel="preload" as="style" href="%s" onload="this.rel=\'stylesheet\'" />' . "\n", esc_url($href));
		}
	}
}, 1);