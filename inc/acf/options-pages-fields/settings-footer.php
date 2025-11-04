<?php

acf_add_local_field_group(array(
	'key'      => 'group_footer',
	'title'    => 'Footer',
	'fields'   => array(
		array(
			'key'           => 'footer_logo',
			'label'         => 'Footer Logo',
			'name'          => 'footer_logo',
			'type'          => 'image',
			'return_format' => 'array',
			'wrapper'       => array('width' => '50%'),
		),
		array(
			'key'   => 'footer_copyright',
			'label' => 'Footer Copyright',
			'name'  => 'footer_copyright',
			'type'  => 'text',
		),
	),
	'location' => array(
		array(
			array(
				'param'    => 'options_page',
				'operator' => '==',
				'value'    => 'settings_footer',
			),
		),
	),
));