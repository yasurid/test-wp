<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php $def_p_types = REHub_Framework::get_option('rehub_ptype_formeta');?>
<?php $def_p_types = (!empty($def_p_types[0])) ? (array)$def_p_types : array('post', 'blog')?>
<?php

$rehub_side_panel = array(
	'id'          => 'rehub_post_side',
	'types'       => $def_p_types,
	'title'       => esc_html__('Post settings', 'rehub-framework'),
	'priority'    => 'low',
	'mode'        => WPALCHEMY_MODE_EXTRACT,
	'context'     => 'side',
	'template'    => array(

		array(
			'type' => 'textbox',
			'name' => 'read_more_custom',
			'label' => esc_html__('Read More custom text', 'rehub-framework'),
			'description' => esc_html__('Will be used in some blocks instead of default read more text', 'rehub-framework'),
			'default' => '',
		),	

		array(
			'type' => 'textbox',
			'name' => '_notice_custom',
			'label' => esc_html__('Custom notice', 'rehub-framework'),
			'description' => esc_html__('Will be used as custom notice, for example, for cashback', 'rehub-framework'),
			'default' => '',
		),		

		array(
			'type' => 'select',
			'name' => '_post_layout',
			'label' => esc_html__('Post layout', 'rehub-framework'),
			'default' => 'normal_post',
			'items' => array(
				'data' => array(
					array(
						'source' => 'function',
						'value'  => 'rehub_get_post_layout_array',
					),
				),
			),			
		),			

		array(
			'type' => 'radiobutton',
			'name' => 'post_size',
			'label' => esc_html__('Post w/ sidebar or Full width', 'rehub-framework'),
			'default' => 'normal_post',
			'items' => array(
				array(
					'value' => 'normal_post',
					'label' => esc_html__('Post w/ Sidebar', 'rehub-framework'),
				),
				array(
					'value' => 'full_post',
					'label' => esc_html__('Full Width Post', 'rehub-framework'),
				)
			)
		),

		rehub_custom_badge_admin(),

		array(
			'type' => 'toggle',
			'name' => 'show_featured_image',
			'label' => esc_html__('Disable Featured Image, Video or Gallery in top part on post page', 'rehub-framework'),
			'default' => '0',
		),		
		array(
			'type' => 'textbox',
			'name' => 'rehub_branded_banner_image_single',
			'label' => esc_html__('Branded area', 'rehub-framework'),
			'description' => esc_html__('Set any custom code or link to image for branded banner after header ', 'rehub-framework'),
			'default' => '',
		),
		array(
			'type' => 'toggle',
			'name' => 'disable_parts',
			'label' => esc_html__('Disable parts?', 'rehub-framework'),
			'description' => esc_html__('Check this box if you want to disable tags, breadcrumbs, author box, share buttons in post', 'rehub-framework'),
		), 		

		array(
			'type' => 'toggle',
			'name' => 'show_banner_ads',
			'label' => esc_html__('Disable global ads in post', 'rehub-framework'),
			'description' => '',
			'default' => '0',			
		),		
	),
);
if(REHub_Framework::get_option('theme_subset') == 'repick'){
	$rehub_side_panel['template'][] = 
		array(
			'type' => 'textbox',
			'name' => 'amazon_search_words',
			'label' => __('Search on amazon keyword', 'rehubchild'),
			'description' => __('Will be used in top offer block', 'rehubchild'),
			'default' => '',
		);
	$rehub_side_panel['template'][] = 		array(
			'type' => 'textbox',
			'name' => 'ebay_search_words',
			'label' => __('Search on ebay keyword', 'rehubchild'),
			'description' => __('Will be used in top offer block', 'rehubchild'),
			'default' => '',
		);
}
return $rehub_side_panel;

/**
 * EOF
 */