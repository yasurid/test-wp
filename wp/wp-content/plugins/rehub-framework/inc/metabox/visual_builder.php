<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

return apply_filters('rh_layout_builder_fields', array(
	'id'          => 'vcr',
	'types'       => array('page'),
	'title'       => esc_html__('Page options', 'rehub-framework'),
	'priority'    => 'low',
	'context'     => 'side',
	'mode'        => WPALCHEMY_MODE_EXTRACT,
	'template'    => array(	
		array(
			'type' => 'radiobutton',
			'name' => 'header_disable',
			'label' => esc_html__('How to show header?', 'rehub-framework'),
			'default' => '0',
			'items' => array(
				array(
					'value' => '0',
					'label' => esc_html__('Default', 'rehub-framework'),
				),
				array(
					'value' => '1',
					'label' => esc_html__('Disable header', 'rehub-framework'),
				),
				array(
					'value' => '2',
					'label' => esc_html__('Transparent', 'rehub-framework'),
				),				
			)
		),		
		array(
			'type' => 'toggle',
			'name' => 'menu_disable',
			'label' => esc_html__('Disable menu', 'rehub-framework'),
		),			
		array(
			'type' => 'toggle',
			'name' => 'footer_disable',
			'label' => esc_html__('Disable footer', 'rehub-framework'),
		),
		array(
			'type' => 'radiobutton',
			'name' => 'content_type',
			'label' => esc_html__('Type of content area', 'rehub-framework'),
			'default' => 'normal_post',
			'items' => array(
				array(
					'value' => 'def',
					'label' => esc_html__('Default content box', 'rehub-framework'),
				),
				array(
					'value' => 'no_shadow',
					'label' => esc_html__('Content box without border', 'rehub-framework'),
				),
				array(
					'value' => 'full_post_area',
					'label' => esc_html__('Full width of browser window', 'rehub-framework'),
				),				
			),
			'default' => array(
				'full_post_area',
			),	
		),	
		array(
			'type' => 'toggle',
			'name' => 'bg_disable',
			'label' => esc_html__('Disable default background image', 'rehub-framework'),
		),																			
	),
	'include_template' => 'visual_builder.php',
));

/**
 * EOF
 */