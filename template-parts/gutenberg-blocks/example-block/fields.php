<?php
acf_add_local_field_group(array(
	'key'      => 'example-block',
	'title'    => 'Example Block',
	'fields'   => array(
		array(
			'key'   => 'text',
			'label' => 'Some Text Field',
			'type'  => 'text',
		),
		array(
			'key'   => 'image',
			'label' => 'Some Image',
			'type'  => 'image',
		),
	),
	'location' => array(
		array(
			array(
				'param'    => 'block',
				'operator' => '==',
				'value'    => 'acf/example-block',
			),
		),
	),
));
