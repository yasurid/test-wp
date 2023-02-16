<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

return array(
	'id'          => 'rehub_review_woo',
	'types'       => array('product'),
	'title'       => esc_html__('Editor Review', 'rehub-framework'),
	'priority'    => 'low',
	'mode'        => WPALCHEMY_MODE_EXTRACT,
	'template'    => array(	
		array(
			'type'      => 'upload',
			'name'      => '_woo_review_image_bg',
			'label'     => esc_html__('Add Image to review', 'rehub-framework'),
			'description' => esc_html__('In Full width Photo Layout, this image will be visible in top section. In other layouts - in review box', 'rehub-framework'),
		),		
		array(
			'type'      => 'slider',
			'name'      => '_review_post_score_manual',
			'label'     => esc_html__('Set overall score', 'rehub-framework'),
			'description' => esc_html__('Enter overall score of review or leave blank to auto calculation based on criterias score', 'rehub-framework'),
			'min'       => 0,
			'max'       => 10,
			'step'      => 0.5,					
		),
		array(
			'type'      => 'textbox',
			'name'      => '_review_heading',
			'label'     => esc_html__('Review Heading', 'rehub-framework'),
		),		
		array(
			'type'      => 'textarea',
			'name'      => '_review_post_summary_text',
			'label'     => esc_html__('Summary Text (optional)', 'rehub-framework'),
		),								 				 													 
		array(
			'type'      => 'textarea',
			'name'      => '_review_post_pros_text',
			'label'     => esc_html__('PROS. Place each from separate line (optional)', 'rehub-framework'),
		),
		array(
			'type'      => 'textarea',
			'name'      => '_review_post_cons_text',
			'label'     => esc_html__('CONS. Place each from separate line (optional)', 'rehub-framework'),
		),								
		array(
			'type'      => 'group',
			'repeating' => true,
			'sortable'  => true,
			'name'      => '_review_post_criteria',
			'title'     => esc_html__('Review Criterias', 'rehub-framework'),
			'fields'    => array(
				array(
					'type'      => 'textbox',
					'name'      => 'review_post_name',
					'label'     => esc_html__('Name', 'rehub-framework'),
				),
				array(
					'type'      => 'slider',
					'name'      => 'review_post_score',
					'label'     => esc_html__('Score', 'rehub-framework'),
					'min'       => 0,
					'max'       => 10,
					'step'      => 0.5,
				),
			),
		),
		array(
			'type' => 'toggle',
			'name' => 'review_woo_shortcode',
			'label' => esc_html__('Enable shortcode inserting', 'rehub-framework'),
			'description' => esc_html__('If enable you can insert review box in any place of content with shortcode [wpsm_reviewbox regular=1]. If disable - it will be after content.', 'rehub-framework'),					
		),		
	),
);

/**
 * EOF
 */