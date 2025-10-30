<?php

function get_first_html_block_name($post_id = null)
{
	if (!$post_id) {
		$post_id = get_the_ID();
	}

	$content = apply_filters('the_content', get_post_field('post_content', $post_id));

	if (preg_match('/<\s*(div|section|article|header|main|footer)([^>]*)>/i', $content, $matches)) {
		$attributes = $matches[2];

		if (preg_match('/class=["\']([^"\']+)["\']/', $attributes, $classMatch)) {
			$classes = explode(' ', trim($classMatch[1]));
			return $classes[0];
		}
	}

	return null;
}