<?php

acf_add_local_field_group(array(
	'key'      => 'group_site_settings',
	'title'    => 'Site Settings',
	'fields'   => array(
		array(
			'key'           => 'field_maintenance_mode',
			'label'         => 'Enable Maintenance Mode',
			'name'          => 'maintenance_mode',
			'type'          => 'true_false',
			'default_value' => 0,
			'ui'            => 1,
			'wrapper'       => array(
				'width' => '50%',
			),
		),
		array(
			'key'           => 'field_disable_payments',
			'label'         => 'Disable Payments',
			'name'          => 'disable_payments',
			'type'          => 'true_false',
			'default_value' => 0,
			'ui'            => 1,
			'wrapper'       => array(
				'width' => '50%',
			),
		),
	),
	'location' => array(
		array(
			array(
				'param'    => 'options_page',
				'operator' => '==',
				'value'    => 'global_settings',
			),
		),
	),
));
