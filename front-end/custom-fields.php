<?php if( function_exists('pnmbr_add_vuupt_fields') ):

pnmbr_add_vuupt_fields(array(
	'key' => 'group_5ed6a1162c008',
	'title' => 'VUUPT',
	'fields' => array(
		array(
			'key' => 'field_5ed6a1247a6f5',
			'label' => 'Modificar data da entrega',
			'name' => 'vuupt_override',
			'type' => 'select',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'choices' => array(
				'none' => 'Entregar normalmente',
				'date' => 'Data específica',
				'next' => 'Fórmula',
			),
			'default_value' => array(
			),
			'allow_null' => 0,
			'multiple' => 0,
			'ui' => 0,
			'return_format' => 'value',
			'ajax' => 0,
			'placeholder' => '',
		),
		array(
			'key' => 'field_5ed6a2d27a6f6',
			'label' => 'Data de entrega',
			'name' => 'vuupt_date',
			'type' => 'date_picker',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => array(
				array(
					array(
						'field' => 'field_5ed6a1247a6f5',
						'operator' => '==',
						'value' => 'date',
					),
				),
			),
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'display_format' => 'd/m/Y',
			'return_format' => 'Y-m-d',
			'first_day' => 1,
		),
		array(
			'key' => 'field_5ed6a3a47a6f7',
			'label' => 'Fórmula da data de entrega',
			'name' => 'vuupt_next',
			'type' => 'text',
			'instructions' => 'Digite aqui a fórmula para a entrega em strtodate. <a href="https://www.php.net/manual/pt_BR/function.strtotime.php" target=_blank>Leia a documentação do <strong>strtodate</strong> completa aqui</a>.<br />
Exemplos: +1 day, next tuesday, tomorrow, +30 days',
			'required' => 0,
			'conditional_logic' => array(
				array(
					array(
						'field' => 'field_5ed6a1247a6f5',
						'operator' => '==',
						'value' => 'next',
					),
				),
			),
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
		),
	),
	'location' => array(
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'product',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'normal',
	'style' => 'default',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
	'active' => 1,
	'description' => '',
));

endif;
