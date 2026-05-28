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
}

// Register custom block category used by theme block.json files.
add_filter('block_categories_all', function ($categories) {
	return array_merge(
		[
			[
				'slug'  => 'digiway-blocks',
				'title' => __('Digiway Blocks', 'digiway'),
				'icon'  => null,
			],
		],
		$categories
	);
});

// Register Gutenberg blocks from per-folder block.json metadata.
add_action('init', function () {
	$blocks_dir = get_template_directory() . '/template-parts/gutenberg-blocks';
	foreach (glob($blocks_dir . '/*/block.json') as $block_json) {
		register_block_type(dirname($block_json));
	}
});