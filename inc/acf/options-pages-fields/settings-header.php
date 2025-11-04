<?php

acf_add_local_field_group(array(
	'key'      => 'group_header',
	'title'    => 'Header',
	'fields'   => array(
		array(
			'key'           => 'header_logo',
			'label'         => 'Header Logo',
			'name'          => 'header_logo',
			'type'          => 'image',
			'return_format' => 'array',
			'wrapper'       => array('width' => '50%'),
		),
	),
	'location' => array(
		array(
			array(
				'param'    => 'options_page',
				'operator' => '==',
				'value'    => 'settings_header',
			),
		),
	),
));