<?php

function theme_field(string $key, $default = '', $post_id = false)
{
	if (!function_exists('get_field')) {
		return $default;
	}
	$value = get_field($key, $post_id);
	return !empty($value) ? $value : $default;
}
