<?php
/**
 * ReHub Theme Customizer
 *
 * @package rehub
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class REHub_Framework_Customizer {
	public static $rh_cross_option_fields = array(
		'theme_subset',
	    'rehub_custom_color',
	    'rehub_sec_color',
	    'rehub_btnoffer_color',
	    'rehub_btnoffer_color_hover',
	    'rehub_btnoffer_color_text',
	    'rehub_btnofferhover_color_text',
	    'enable_smooth_btn',
	    'rehub_color_link',
	    'rehub_sidebar_left',
	    'rehub_body_block',
	    'rehub_content_shadow',
	    'rehub_color_background',
	    'rehub_background_image',
	    'rehub_background_repeat',
	    'rehub_background_position',
	    'rehub_background_fixed',
	    'rehub_branded_bg_url',
	    'rehub_logo',
	    'rehub_logo_retina',
	    'rehub_logo_retina_width',
	    'rehub_logo_retina_height',
	    'rehub_text_logo',
	    'rehub_text_slogan',
	    'rehub_logo_pad',
	    'rehub_sticky_nav',
	    'rehub_logo_sticky_url',
	    'header_logoline_style',
	    'rehub_header_color_background',
	    'dark_theme',
	    'rehub_header_background_image',
	    'rehub_header_background_repeat',
	    'rehub_header_background_position',
	    'header_menuline_style',
	    'header_menuline_type',
	    'rehub_nav_font_custom',
	    'rehub_nav_font_upper',
	    'rehub_nav_font_light',
	    'rehub_nav_font_border',
	    'rehub_enable_menu_shadow',
	    'rehub_custom_color_nav',
	    'rehub_custom_color_nav_font',
	    'header_topline_style',
	    'rehub_custom_color_top',
	    'rehub_custom_color_top_font',
	    'rehub_header_top_enable',
	    'rehub_top_line_content',
		'rehub_header_style',
		'header_seven_compare_btn',
		'header_seven_compare_btn_label',
		'header_seven_cart',
		'header_seven_cart_as_btn',
		'header_seven_login',
		'header_seven_login_label',
		'header_seven_wishlist',
		'header_seven_wishlist_label',
		'header_seven_more_element',
		'header_six_login',
		'header_six_btn',
		'header_six_btn_color',
		'header_six_btn_txt',
		'header_six_btn_url',
		'header_six_btn_login',
		'header_six_src',
		'header_six_menu',
		'rehub_footer_widgets',
		'footer_style',
		'footer_color_background',
		'footer_background_image',
		'footer_background_repeat',
		'footer_background_position',
		'footer_style_bottom',
		'rehub_footer_text',
		'rehub_footer_logo',
		'width_layout',
		'woo_code_zone_loop',
		'woo_code_zone_button',
		'woo_code_zone_content',
		'woo_code_zone_footer',
		'woo_code_zone_float',								
		'wooloop_image_size',
		'woo_number',
		'woo_design',
		'woo_columns',
		'wooloop_heading_color',
		'wooloop_heading_size',
		'wooloop_price_color',
		'wooloop_price_size',
		'wooloop_sale_color',
		'rehub_sidebar_left_shop',
		'sidebar_mobile_shop',
	);

	/* The single instance of the class.*/
	public static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}	

	public function __construct() {
		add_action( 'customize_register', array( $this, 'rh_customize_register'));
		add_action('admin_enqueue_scripts', array( $this, 'rh_customizer_scripts'));
		add_action( 'customize_preview_init', array( $this, 'rh_live_preview_scripts'));
		add_action( 'save_post_customize_changeset', array( $this, 'rh_save_theme_options'));
		add_action('vp_option_set_before_save', array( $this, 'rh_save_customizer_options'));		
	}

	public function rh_customize_register( $wp_customize ) {

		if ( defined('REHUB_MAIN_COLOR')) {
			$maincolor = REHUB_MAIN_COLOR;
			$secondarycolor = REHUB_SECONDARY_COLOR;
			$btncolor = REHUB_BUTTON_COLOR;
			$btncolortext = REHUB_BUTTON_COLOR_TEXT;
			$default_layout = REHUB_DEFAULT_LAYOUT;
			$contentboxdisable = REHUB_BOX_DISABLE;
		}else{
			$maincolor = '#8035be';
			$secondarycolor = '#000000';
			$btncolor = '#de1414';
			$default_layout = 'communitylist';
			$contentboxdisable = '0';
			$btncolortext = '#ffffff';
		}		

		/* THEME OPTIONS */
		$wp_customize->add_panel( 'panel_id', array(
			'priority' => 121,
			'title' => esc_html__('Theme Options', 'rehub-framework'),
			'description' => esc_html__('ReHub Control Center', 'rehub-framework'),
		));

		/* 
		 * APPEARANCE/COLOR
		*/
		$wp_customize->add_section( 'rh_styling_settings', array(
			'title' => esc_html__('Appearance/Color', 'rehub-framework'),
			'priority'  => 122,
			'panel' => 'panel_id',
		));

		//Width of site
		$wp_customize->add_setting('width_layout', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => 'regular',
		));
		$wp_customize->add_control('width_layout', array(
			'settings' => 'width_layout',
			'label' => esc_html__('Select Width Style', 'rehub-framework'),
			'section' => 'rh_styling_settings',
			'type' => 'select',
			'choices' => array(
				'regular' => esc_html__('Regular (1200px)', 'rehub-framework'),
				'extended' => esc_html__('Extended (1530px)', 'rehub-framework'),
				'compact' => esc_html__('Compact', 'rehub-framework'),
			),
		));	

		//Subset (old child themes)
		$wp_customize->add_setting('theme_subset', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => 'flat',
		));
		$wp_customize->add_control('theme_subset', array(
			'settings' => 'theme_subset',
			'label' => esc_html__('Select theme subset', 'rehub-framework'),
			'section' => 'rh_styling_settings',
			'type' => 'select',
			'choices' => array(
				'flat' => esc_html__('Clean Rehub', 'rehub-framework'),
				'redeal' => esc_html__('Redeal', 'rehub-framework'),
			),
		));			

		//Custom color schema
		$wp_customize->add_setting( 'rehub_custom_color', array(
			'sanitize_callback' => 'sanitize_hex_color',
			'default' => $maincolor,
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'rehub_custom_color', array(
			'label' => esc_html__('Custom color schema', 'rehub-framework'),
			'section' => 'rh_styling_settings',
			'settings' => 'rehub_custom_color',
		)));

		//Custom secondary color
		$wp_customize->add_setting( 'rehub_sec_color', array(
			'sanitize_callback' => 'sanitize_hex_color',
			'default' => $secondarycolor,
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'rehub_sec_color', array(
			'label' => esc_html__('Custom secondary color', 'rehub-framework'),
			'section' => 'rh_styling_settings',
			'settings' => 'rehub_sec_color',
		)));

		//Set offer buttons color
		$wp_customize->add_setting( 'rehub_btnoffer_color', array(
			'sanitize_callback' => 'sanitize_hex_color',
			'default' => $btncolor,
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'rehub_btnoffer_color', array(
			'label' => esc_html__('Set offer buttons color', 'rehub-framework'),
			'section' => 'rh_styling_settings',
			'settings' => 'rehub_btnoffer_color',
		)));
		$wp_customize->add_setting( 'rehub_btnoffer_color_hover', array(
			'sanitize_callback' => 'sanitize_hex_color',
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'rehub_btnoffer_color_hover', array(
			'label' => esc_html__('Set offer button hover color', 'rehub-framework'),
			'section' => 'rh_styling_settings',
			'settings' => 'rehub_btnoffer_color_hover',
		)));
		$wp_customize->add_setting( 'rehub_btnoffer_color_text', array(
			'sanitize_callback' => 'sanitize_hex_color',
			'default' => $btncolortext,
		));	
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'rehub_btnoffer_color_text', array(
			'label' => esc_html__('Set offer button text color', 'rehub-framework'),
			'section' => 'rh_styling_settings',
			'settings' => 'rehub_btnoffer_color_text',
		)));
		$wp_customize->add_setting( 'rehub_btnofferhover_color_text', array(
			'sanitize_callback' => 'sanitize_hex_color',
		));					
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'rehub_btnofferhover_color_text', array(
			'label' => esc_html__('Set offer button text color', 'rehub-framework'),
			'section' => 'rh_styling_settings',
			'settings' => 'rehub_btnofferhover_color_text',
		)));
		//Custom color for links inside posts
		$wp_customize->add_setting( 'rehub_color_link', array(
			'sanitize_callback' => 'sanitize_hex_color',
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'rehub_color_link', array(
			'label' => esc_html__('Custom color for links inside posts','rehub-framework'),
			'section' => 'rh_styling_settings',
			'settings' => 'rehub_color_link',
		)));

		//Enable smooth design for inputs
		$wp_customize->add_setting( 'enable_smooth_btn', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '2',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'enable_smooth_btn', array(
			'label' => esc_html__('Enable smooth design for inputs?', 'rehub-framework'),
			'section'  => 'rh_styling_settings',
			'settings' => 'enable_smooth_btn',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('Rounded', 'rehub-framework'),
				'2' => esc_html__('Soft Rounded', 'rehub-framework'),
			),
		)));

		//Set sidebar to left side
		$wp_customize->add_setting( 'rehub_sidebar_left', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'rehub_sidebar_left', array(
			'label' => esc_html__('Set sidebar to left side?', 'rehub-framework'),
			'section'  => 'rh_styling_settings',
			'settings' => 'rehub_sidebar_left',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
				
		//Enable boxed version
		$wp_customize->add_setting( 'rehub_body_block', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'rehub_body_block', array(
			'label' => esc_html__('Enable boxed version?', 'rehub-framework'),
			'section'  => 'rh_styling_settings',
			'settings' => 'rehub_body_block',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
			
		//Disable box borders under content box
		$wp_customize->add_setting( 'rehub_content_shadow', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => $contentboxdisable,
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'rehub_content_shadow', array(
			'label' => esc_html__('Disable box borders under content box?', 'rehub-framework'),
			'section'  => 'rh_styling_settings',
			'settings' => 'rehub_content_shadow',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));

		$wp_customize->add_setting( 'dark_theme', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'dark_theme', array(
			'label' => esc_html__('Enable dark theme?', 'rehub-framework'),
			'section'  => 'rh_styling_settings',
			'settings' => 'dark_theme',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
				
		//Background Color
		$wp_customize->add_setting( 'rehub_color_background', array(
			'sanitize_callback' => 'sanitize_hex_color',
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'rehub_color_background', array(
			'label' => esc_html__('Background Color', 'rehub-framework'),
			'section' => 'rh_styling_settings',
			'settings' => 'rehub_color_background',
		)));
				
		//Background Image
		$wp_customize->add_setting( 'rehub_background_image', array(
			'sanitize_callback' => 'esc_url_raw',
		));
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'rehub_background_image', array(
			'label' => esc_html__('Background Image', 'rehub-framework'),
			'description' => esc_html__('Set background color before it', 'rehub-framework'),
			'section' => 'rh_styling_settings',
			'settings' => 'rehub_background_image',
		)));

		//Background Repeat
		$wp_customize->add_setting('rehub_background_repeat', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => 'repeat',
		));
		$wp_customize->add_control('rehub_background_repeat', array(
			'settings' => 'rehub_background_repeat',
			'label' => esc_html__('Background Repeat', 'rehub-framework'),
			'section' => 'rh_styling_settings',
			'type' => 'select',
			'choices' => array(
				'repeat' => esc_html__('Repeat', 'rehub-framework'),
				'no-repeat' => esc_html__('No Repeat', 'rehub-framework'),
				'repeat-x' => esc_html__('Repeat X', 'rehub-framework'),
				'repeat-y' => esc_html__('Repeat Y', 'rehub-framework'),
			),
		));
			
		//Background Position
		$wp_customize->add_setting('rehub_background_position', array(
			'sanitize_callback' => 'sanitize_key',
		));
		$wp_customize->add_control('rehub_background_position', array(
			'settings' => 'rehub_background_position',
			'label' => esc_html__('Background Position', 'rehub-framework'),
			'section' => 'rh_styling_settings',
			'type' => 'select',
			'choices' => array(
				'repeat' => esc_html__('Left', 'rehub-framework'),
				'center' => esc_html__('Center', 'rehub-framework'),
				'right' => esc_html__('Right', 'rehub-framework'),
			),
		));
			
			
		//Fixed Background Image
		$wp_customize->add_setting( 'rehub_background_fixed', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'rehub_background_fixed', array(
			'label' => esc_html__('Fixed Background Image?', 'rehub-framework'),
			'section'  => 'rh_styling_settings',
			'settings' => 'rehub_background_fixed',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
				
		//Url for branded background
	 	$wp_customize->add_setting('rehub_branded_bg_url', array(
			'sanitize_callback' => 'wp_kses_post',
		)); 
		$wp_customize->add_control('rehub_branded_bg_url', array(
			'label' => esc_html__('Url for branded background', 'rehub-framework'),
			'description' => esc_html__('Insert url that will be display on background', 'rehub-framework'),
			'section' => 'rh_styling_settings',
			'settings' => 'rehub_branded_bg_url',
			'type' => 'url',
		));

		/* 
		 * LOGO & FAVICON 
		 * Site Identity section
		*/
		
		//Upload Logo
		$wp_customize->add_setting( 'rehub_logo', array(
			'sanitize_callback' => 'esc_url_raw',
		));
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'rehub_logo', array(
			'label' => esc_html__('Upload Logo', 'rehub-framework'),
			'description' => esc_html__('Upload your logo. Max width is 450px. (1200px for full width, 180px for logo + menu row layout)', 'rehub-framework'),
			'section' => 'title_tagline',
			'settings' => 'rehub_logo',
		)));
			
		//Retina Logo (no live preview)
		$wp_customize->add_setting( 'rehub_logo_retina', array(
			'sanitize_callback' => 'esc_url_raw',
		));
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'rehub_logo_retina', array(
			'label' => esc_html__('Upload Logo (retina version)', 'rehub-framework'),
			'description' => esc_html__('Upload retina version of the logo. It should be 2x the size of main logo.', 'rehub-framework'),
			'section' => 'title_tagline',
			'settings' => 'rehub_logo_retina',
		)));
			
		//Logo width (no live preview)
		$wp_customize->add_setting('rehub_logo_retina_width', array(
			'sanitize_callback' => 'wp_kses',
		)); 
		$wp_customize->add_control('rehub_logo_retina_width', array(
			'label' => esc_html__('Logo width', 'rehub-framework'),
			'description' => esc_html__('Please, enter logo width (without px)', 'rehub-framework'),
			'section' => 'title_tagline',
			'settings' => 'rehub_logo_retina_width',
			'type' => 'number',
		));
			
		//Logo width (no live preview)
		$wp_customize->add_setting('rehub_logo_retina_height', array(
			'sanitize_callback' => 'wp_kses',
		)); 
		$wp_customize->add_control('rehub_logo_retina_height', array(
			'label' => esc_html__('Retina logo height', 'rehub-framework'),
			'description' => esc_html__('Please, enter logo height (without px)', 'rehub-framework'),
			'section' => 'title_tagline',
			'settings' => 'rehub_logo_retina_height',
			'type' => 'number',
		));
			
		//Text logo
		$wp_customize->add_setting('rehub_text_logo', array(
			'sanitize_callback' => 'wp_kses',
		)); 
		$wp_customize->add_control('rehub_text_logo', array(
			'label' => esc_html__('Text logo', 'rehub-framework'),
			'description' => esc_html__('You can type text logo. Use this field only if no image logo', 'rehub-framework'),
			'section' => 'title_tagline',
			'settings' => 'rehub_text_logo',
		));
			
		//Slogan
		$wp_customize->add_setting('rehub_text_slogan', array(
			'sanitize_callback' => 'wp_kses',
		)); 
		$wp_customize->add_control('rehub_text_slogan', array(
			'label' => esc_html__('Slogan', 'rehub-framework'),
			'description' => esc_html__('You can type slogan below text logo. Use this field only if no image logo', 'rehub-framework'),
			'section' => 'title_tagline',
			'settings' => 'rehub_text_slogan',
			'type' => 'textarea',
		));
			
		/* 
		 * HEADER AND MENU 
		*/
		$wp_customize->add_section( 'rh_header_settings', array(
			'title' => esc_html__('Header and Menu', 'rehub-framework'),
			'priority'  => 124,
			'panel' => 'panel_id',
		));

		//Select Header style
		$wp_customize->add_setting('rehub_header_style', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => 'header_seven'
		));
		$wp_customize->add_control('rehub_header_style', array(
			'type' => 'select',
			'settings' => 'rehub_header_style',
			'label' => esc_html__('Select Header style', 'rehub-framework'),
			'section' => 'rh_header_settings',
			'choices' => array(
				'header_first' => esc_html__('Logo + code zone 468X60 + search box', 'rehub-framework'),
				'header_eight' => esc_html__('Logo + slogan + search box', 'rehub-framework'),
				'header_second' => esc_html__('Logo + code zone 728X90', 'rehub-framework'),
				'header_fourth' => esc_html__('Full width logo + code zone under logo', 'rehub-framework'),
				'header_five' => esc_html__('Logo + menu in one row', 'rehub-framework'),
				'header_six' => esc_html__('Customizable header', 'rehub-framework'),
				'header_seven' => esc_html__('Shop/Comparison header (logo + search + login + cart/compare icon)', 'rehub-framework'),
				'header_nine' => esc_html__('Centered logo + menu in left + shop, comparison, login icon in right', 'rehub-framework'),		
			)
		));
			/* Subfields 'seven' header */
			//Enable Compare Icon
			$wp_customize->add_setting('header_seven_compare_btn', array(
				'sanitize_callback' => 'sanitize_key',
				'default' => '1'
			));
			$wp_customize->add_control(new WP_Customize_Control( $wp_customize, 'header_seven_compare_btn', array(
				'type' => 'radio',
				'settings' => 'header_seven_compare_btn',
				'label' => esc_html__('Enable Compare Icon', 'rehub-framework'),
				'section' => 'rh_header_settings',
				'choices' => array(
					'0'  => esc_html__('Off', 'rehub-framework'),
					'1' => esc_html__('On', 'rehub-framework'),
				)
			)));
			$wp_customize->add_setting('header_seven_compare_btn_label', array(
				'sanitize_callback' => 'wp_kses',
			));
			$wp_customize->add_control('header_seven_compare_btn_label', array(
				'type' => 'text',
				'settings' => 'header_seven_compare_btn_label',
				'label' => esc_html__('Label for compare icon', 'rehub-framework'),
				'section' => 'rh_header_settings',
			));		
			//Enable Cart Icon
			$wp_customize->add_setting('header_seven_cart', array(
				'sanitize_callback' => 'sanitize_key',
				'default' => '1'
			));
			$wp_customize->add_control(new WP_Customize_Control( $wp_customize, 'header_seven_cart', array(
				'type' => 'radio',
				'settings' => 'header_seven_cart',
				'label' => esc_html__('Enable Cart Icon', 'rehub-framework'),
				'section' => 'rh_header_settings',
				'choices' => array(
					'0'  => esc_html__('Off', 'rehub-framework'),
					'1' => esc_html__('On', 'rehub-framework'),
				)
			)));
			$wp_customize->add_setting('header_seven_cart_as_btn', array(
				'sanitize_callback' => 'sanitize_key',
				'default' => '0'
			));
			$wp_customize->add_control(new WP_Customize_Control( $wp_customize, 'header_seven_cart_as_btn', array(
				'type' => 'radio',
				'settings' => 'header_seven_cart_as_btn',
				'label' => esc_html__('Enable Cart as button', 'rehub-framework'),
				'section' => 'rh_header_settings',
				'choices' => array(
					'0'  => esc_html__('Off', 'rehub-framework'),
					'1' => esc_html__('On', 'rehub-framework'),
				)
			)));		
			//Enable Login Icon
			$wp_customize->add_setting('header_seven_login', array(
				'sanitize_callback' => 'sanitize_key',
				'default' => '0'
			));
			$wp_customize->add_control(new WP_Customize_Control( $wp_customize, 'header_seven_login', array(
				'type' => 'radio',
				'settings' => 'header_seven_login',
				'label' => esc_html__('Enable Login Icon', 'rehub-framework'),
				'section' => 'rh_header_settings',
				'choices' => array(
					'0'  => esc_html__('Off', 'rehub-framework'),
					'1' => esc_html__('On', 'rehub-framework'),
				)
			)));
			$wp_customize->add_setting('header_seven_login_label', array(
				'sanitize_callback' => 'wp_kses',
			));
			$wp_customize->add_control('header_seven_login_label', array(
				'type' => 'text',
				'settings' => 'header_seven_login_label',
				'label' => esc_html__('Label for login icon', 'rehub-framework'),
				'section' => 'rh_header_settings',
			));			
			//Enable Wishlist Icon
			$wp_customize->add_setting('header_seven_wishlist', array(
				'sanitize_callback' => 'wp_kses',
			));
			$wp_customize->add_control('header_seven_wishlist', array(
				'type' => 'url',
				'settings' => 'header_seven_wishlist',
				'label' => esc_html__('Enable Wishlist Icon and set Url', 'rehub-framework'),
				'description' => esc_html__('Set url on your page where you have [rh_get_user_favorites] shortcode. All icons in header will be available also in mobile logo panel. We don\'t recommend to enable more than 2 icons with Mobile logo.', 'rehub-framework'),	
				'section' => 'rh_header_settings',
			));
			$wp_customize->add_setting('header_seven_wishlist_label', array(
				'sanitize_callback' => 'wp_kses',
			));
			$wp_customize->add_control('header_seven_wishlist_label', array(
				'type' => 'text',
				'settings' => 'header_seven_wishlist_label',
				'label' => esc_html__('Label for wishlist icon', 'rehub-framework'),
				'section' => 'rh_header_settings',
			));			
			//Add additional element
			$wp_customize->add_setting('header_seven_more_element', array(
				'sanitize_callback' => 'wp_kses_post',
			));
			$wp_customize->add_control('header_seven_more_element', array(
				'type' => 'textarea',
				'settings' => 'header_seven_more_element',
				'label' => esc_html__('Add additional element (shortcodes and html supported)', 'rehub-framework'),
				'section' => 'rh_header_settings',
			));
			
			/* Subfields 'six' header */
			//Enable login/register
			$wp_customize->add_setting('header_six_login', array(
				'sanitize_callback' => 'sanitize_key',
				'default' => '0'
			));
			$wp_customize->add_control(new WP_Customize_Control( $wp_customize, 'header_six_login', array(
				'type' => 'radio',
				'settings' => 'header_six_login',
				'label' => esc_html__('Enable login/register section', 'rehub-framework'),
				'description' => esc_html__('Also, login popup must be enabled in Theme option - User options', 'rehub-framework'),
				'section' => 'rh_header_settings',
				'choices' => array(
					'0'  => esc_html__('Off', 'rehub-framework'),
					'1' => esc_html__('On', 'rehub-framework'),
				)
			)));
			//Enable additional button
			$wp_customize->add_setting('header_six_btn', array(
				'sanitize_callback' => 'sanitize_key',
				'default' => '0'
			));
			$wp_customize->add_control(new WP_Customize_Control( $wp_customize, 'header_six_btn', array(
				'type' => 'radio',
				'settings' => 'header_six_btn',
				'label' => esc_html__('Enable additional button in header', 'rehub-framework'),
				'description' => esc_html__('This will add button in header', 'rehub-framework'),
				'section' => 'rh_header_settings',
				'choices' => array(
					'0'  => esc_html__('Off', 'rehub-framework'),
					'1' => esc_html__('On', 'rehub-framework'),
				)
			)));
			//Color style of button
			$wp_customize->add_setting('header_six_btn_color', array(
				'sanitize_callback' => 'sanitize_key',
				'default' => 'green'
			));
			$wp_customize->add_control('header_six_btn_color', array(
				'type' => 'select',
				'settings' => 'header_six_btn_color',
				'label' => esc_html__('Choose color style of button', 'rehub-framework'),
				'section' => 'rh_header_settings',
				'choices' => array(
					'btncolor' => esc_html__('Main Color of Buttons', 'rehub-framework'),
					'secondary' => esc_html__('Secondary Theme Color', 'rehub-framework'),
					'main' => esc_html__('Main Theme Color', 'rehub-framework'),
					'green' => esc_html__('green', 'rehub-framework'),
					'orange' => esc_html__('orange', 'rehub-framework'),
					'red' => esc_html__('red', 'rehub-framework'),
					'blue' => esc_html__('blue', 'rehub-framework'),
					'black' => esc_html__('black', 'rehub-framework'),
					'rosy' => esc_html__('rosy', 'rehub-framework'),
					'brown' => esc_html__('brown', 'rehub-framework'),
					'pink' => esc_html__('pink', 'rehub-framework'),
					'purple' => esc_html__('purple', 'rehub-framework'),
					'gold' => esc_html__('gold', 'rehub-framework'),
				)
			));
			//Label for button
			$wp_customize->add_setting('header_six_btn_txt', array(
				'sanitize_callback' => 'wp_kses',
				'default' => esc_html__('Submit a deal', 'rehub-framework'),
			));
			$wp_customize->add_control('header_six_btn_txt', array(
				'settings' => 'header_six_btn_txt',
				'label' => esc_html__('Type label for button', 'rehub-framework'),
				'section' => 'rh_header_settings',
			));
			//URL for button
			$wp_customize->add_setting('header_six_btn_url', array(
				'sanitize_callback' => 'wp_kses',
			));
			$wp_customize->add_control('header_six_btn_url', array(
				'type' => 'url',
				'settings' => 'header_six_btn_url',
				'label' => esc_html__('Type url for button', 'rehub-framework'),
				'section' => 'rh_header_settings',
			));
			//Enable login popup
			$wp_customize->add_setting('header_six_btn_login', array(
				'sanitize_callback' => 'sanitize_key',
				'default' => '0'
			));
			$wp_customize->add_control(new WP_Customize_Control( $wp_customize, 'header_six_btn_login', array(
				'type' => 'radio',
				'settings' => 'header_six_btn_login',
				'label' => esc_html__('Enable login popup for non registered users', 'rehub-framework'),
				'description' => esc_html__('This will open popup if non registered user clicks on button. Also, login popup must be enabled in Theme option - User options', 'rehub-framework'),
				'section' => 'rh_header_settings',
				'choices' => array(
					'0'  => esc_html__('Off', 'rehub-framework'),
					'1' => esc_html__('On', 'rehub-framework'),
				)
			)));
			//Enable search form
			$wp_customize->add_setting('header_six_src', array(
				'sanitize_callback' => 'sanitize_key',
				'default' => '0'
			));
			$wp_customize->add_control(new WP_Customize_Control( $wp_customize, 'header_six_src', array(
				'type' => 'radio',
				'settings' => 'header_six_src',
				'label' => esc_html__('Enable search form in header', 'rehub-framework'),
				'section' => 'rh_header_settings',
				'choices' => array(
					'0'  => esc_html__('Off', 'rehub-framework'),
					'1' => esc_html__('On', 'rehub-framework'),
				)
			)));
			//Enable additional menu
			$wp_customize->add_setting('header_six_menu', array(
				'sanitize_callback' => 'sanitize_key',
			));
			$wp_customize->add_control('header_six_menu', array(
				'type' => 'select',
				'settings' => 'header_six_menu',
				'label' => esc_html__('Enable additional menu near logo', 'rehub-framework'),
				'description' => esc_html__('Use short menu with small number of items!!!', 'rehub-framework'),
				'section' => 'rh_header_settings',
				'choices' => $this->rh_get_menus_customizer(),
			));		
			
		//Set padding from top and bottom
		$wp_customize->add_setting('rehub_logo_pad', array(
			'sanitize_callback' => 'wp_kses',
		)); 
		$wp_customize->add_control('rehub_logo_pad', array(
			'label' => esc_html__('Set padding from top and bottom', 'rehub-framework'),
			'description' => esc_html__('This will add custom padding from top and bottom for all custom elements in logo section. Default is 15', 'rehub-framework'),
			'section' => 'rh_header_settings',
			'settings' => 'rehub_logo_pad',
			'type' => 'number',
		));
			
		//Sticky Menu Bar
		$wp_customize->add_setting( 'rehub_sticky_nav', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'rehub_sticky_nav', array(
			'label' => esc_html__('Sticky Menu Bar', 'rehub-framework'),
			'description' => esc_html__('Enable/Disable Sticky navigation bar.', 'rehub-framework'),
			'section'  => 'rh_header_settings',
			'settings' => 'rehub_sticky_nav',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
			//Upload Logo for sticky menu
			$wp_customize->add_setting( 'rehub_logo_sticky_url', array(
			'sanitize_callback' => 'esc_url_raw',
			));
			$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'rehub_logo_sticky_url', array(
				'label' => esc_html__('Upload Logo for sticky menu', 'rehub-framework'),
				'description' => esc_html__('Upload your logo. Max height is 40px.', 'rehub-framework'),
				'section' => 'rh_header_settings',
				'settings' => 'rehub_logo_sticky_url',
			)));
			
		//Choose color style of header logo section
		$wp_customize->add_setting('header_logoline_style', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control('header_logoline_style', array(
			'settings' => 'header_logoline_style',
			'label' => esc_html__('Color style of header logo section', 'rehub-framework'),
			'section' => 'rh_header_settings',
			'type' => 'select',
			'choices' => array(
				'0' => esc_html__('White style and dark fonts', 'rehub-framework'),
				'1' => esc_html__('Dark style and white fonts', 'rehub-framework'),
			),
		));

		//Custom Background Color
		$wp_customize->add_setting( 'rehub_header_color_background', array(
			'sanitize_callback' => 'sanitize_hex_color',
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'rehub_header_color_background', array(
			'label' => esc_html__('Custom Background Color', 'rehub-framework'),
			'description' => esc_html__('Choose the background color or leave blank for default', 'rehub-framework'),
			'section' => 'rh_header_settings',
			'settings' => 'rehub_header_color_background',
		)));
			
		//Custom Background Image
		$wp_customize->add_setting( 'rehub_header_background_image', array(
			'sanitize_callback' => 'esc_url_raw',
		));
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'rehub_header_background_image', array(
			'label' => esc_html__('Custom Background Image', 'rehub-framework'),
			'description' => esc_html__('Upload a background image or leave blank', 'rehub-framework'),
			'section' => 'rh_header_settings',
			'settings' => 'rehub_header_background_image',
		)));
			
		//Background Repeat
		$wp_customize->add_setting('rehub_header_background_repeat', array(
			'sanitize_callback' => 'sanitize_key',
		));
		$wp_customize->add_control('rehub_header_background_repeat', array(
			'settings' => 'rehub_header_background_repeat',
			'label' => esc_html__('Background Repeat', 'rehub-framework'),
			'section' => 'rh_header_settings',
			'type' => 'select',
			'choices' => array(
				'repeat' => esc_html__('Repeat', 'rehub-framework'),
				'no-repeat' => esc_html__('No Repeat', 'rehub-framework'),
				'repeat-x' => esc_html__('Repeat X', 'rehub-framework'),
				'repeat-y' => esc_html__('Repeat Y', 'rehub-framework'),
			),
		));
			
		//Background Position
		$wp_customize->add_setting('rehub_header_background_position', array(
			'sanitize_callback' => 'sanitize_key',
		));
		$wp_customize->add_control('rehub_header_background_position', array(
			'settings' => 'rehub_header_background_position',
			'label' => esc_html__('Background Position', 'rehub-framework'),
			'section' => 'rh_header_settings',
			'type' => 'select',
			'choices' => array(
				'repeat' => esc_html__('Left', 'rehub-framework'),
				'center' => esc_html__('Center', 'rehub-framework'),
				'right' => esc_html__('Right', 'rehub-framework'),
			),
		));
			
		//Choose color style of header menu section
		$wp_customize->add_setting('header_menuline_style', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control('header_menuline_style', array(
			'settings' => 'header_menuline_style',
			'label' => esc_html__('Color style of header menu section', 'rehub-framework'),	
			'section' => 'rh_header_settings',
			'type' => 'select',
			'choices' => array(
				'0' => esc_html__('White style and dark fonts', 'rehub-framework'),
				'1' => esc_html__('Dark style and white fonts', 'rehub-framework'),
			),
		));
			
		//Choose type of font and padding
		$wp_customize->add_setting('header_menuline_type', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control('header_menuline_type', array(
			'settings' => 'header_menuline_type',
			'label' => esc_html__('Choose type of font and padding', 'rehub-framework'),
			'section' => 'rh_header_settings',
			'type' => 'select',
			'choices' => array(
				'0' => esc_html__('Middle size and padding', 'rehub-framework'),
				'1' => esc_html__('Compact size and padding', 'rehub-framework'),
				'2' => esc_html__('Big size and padding', 'rehub-framework'),
			),
		));
			
		//Add custom font size
		$wp_customize->add_setting('rehub_nav_font_custom', array(
			'sanitize_callback' => 'wp_kses',
		)); 
		$wp_customize->add_control('rehub_nav_font_custom', array(
			'label' => esc_html__('Add custom font size', 'rehub-framework'),
			'description' => esc_html__('Default is 15. Put just number', 'rehub-framework'),
			'section' => 'rh_header_settings',
			'settings' => 'rehub_nav_font_custom',
			'type' => 'number',
		));

		//Enable uppercase font
		$wp_customize->add_setting( 'rehub_nav_font_upper', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'rehub_nav_font_upper', array(
			'label' => esc_html__('Enable uppercase font?', 'rehub-framework'),
			'section'  => 'rh_header_settings',
			'settings' => 'rehub_nav_font_upper',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
		
		//Enable Light font weight
		$wp_customize->add_setting( 'rehub_nav_font_light', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '1',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'rehub_nav_font_light', array(
			'label' => esc_html__('Enable Light font weight?', 'rehub-framework'),
			'section'  => 'rh_header_settings',
			'settings' => 'rehub_nav_font_light',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
		
		//Disable border of items
		$wp_customize->add_setting( 'rehub_nav_font_border', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'rehub_nav_font_border', array(
			'label' => esc_html__('Disable border of items?', 'rehub-framework'),
			'section'  => 'rh_header_settings',
			'settings' => 'rehub_nav_font_border',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
		
		//Menu shadow
		$wp_customize->add_setting( 'rehub_enable_menu_shadow', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'rehub_enable_menu_shadow', array(
			'label' => esc_html__('Menu shadow', 'rehub-framework'),
			'description' => esc_html__('Enable/Disable shadow under menu', 'rehub-framework'),
			'section'  => 'rh_header_settings',
			'settings' => 'rehub_enable_menu_shadow',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
		
		//Custom color of menu background
		$wp_customize->add_setting( 'rehub_custom_color_nav', array(
			'sanitize_callback' => 'sanitize_hex_color',
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'rehub_custom_color_nav', array(
			'label' => esc_html__('Custom color of menu background', 'rehub-framework'),
			'description' => esc_html__('Or leave blank for default color', 'rehub-framework'),
			'section' => 'rh_header_settings',
			'settings' => 'rehub_custom_color_nav',
		)));
		
		//Custom color of menu font
		$wp_customize->add_setting( 'rehub_custom_color_nav_font', array(
			'sanitize_callback' => 'sanitize_hex_color',
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'rehub_custom_color_nav_font', array(
			'label' => esc_html__('Custom color of menu font', 'rehub-framework'),
			'description' => esc_html__('Or leave blank for default color', 'rehub-framework'),
			'section' => 'rh_header_settings',
			'settings' => 'rehub_custom_color_nav_font',
		)));
		
		//Enablee top line
		$wp_customize->add_setting( 'rehub_header_top_enable', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'rehub_header_top_enable', array(
			'label' => esc_html__('Enable top line', 'rehub-framework'),
			'description' => esc_html__('You can enable top line', 'rehub-framework'),
			'section'  => 'rh_header_settings',
			'settings' => 'rehub_header_top_enable',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
		
		//Choose color style of header top line
		$wp_customize->add_setting('header_topline_style', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control('header_topline_style', array(
			'settings' => 'header_topline_style',
			'label' => esc_html__('Choose color style of header top line', 'rehub-framework'),	
			'section' => 'rh_header_settings',
			'type' => 'select',
			'choices' => array(
				'0' => esc_html__('White style and dark fonts', 'rehub-framework'),
				'1' => esc_html__('Dark style and white fonts', 'rehub-framework'),
			),
		));

		//Custom color for top line of header
		$wp_customize->add_setting( 'rehub_custom_color_top', array(
			'sanitize_callback' => 'sanitize_hex_color',
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'rehub_custom_color_top', array(
			'label' => esc_html__('Custom color for top line of header', 'rehub-framework'),
			'description' => esc_html__('Or leave blank for default color', 'rehub-framework'),
			'section' => 'rh_header_settings',
			'settings' => 'rehub_custom_color_top',
		)));
		
		//Custom color of menu font for top line of header
		$wp_customize->add_setting( 'rehub_custom_color_top_font', array(
			'sanitize_callback' => 'sanitize_hex_color',
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'rehub_custom_color_top_font', array(
			'label' => esc_html__('Custom color of menu font for top line of header', 'rehub-framework'),
			'description' => esc_html__('Or leave blank for default color', 'rehub-framework'),
			'section' => 'rh_header_settings',
			'settings' => 'rehub_custom_color_top_font',
		)));

		$wp_customize->add_setting('rehub_top_line_content', array(
			'sanitize_callback' => 'wp_kses_post',
		)); 
		$wp_customize->add_control('rehub_top_line_content', array(
			'label' => esc_html__('Top line content', 'rehub-framework'),
			'description' => esc_html__('Add custom content to top line', 'rehub-framework'),
			'section' => 'rh_header_settings',
			'settings' => 'rehub_top_line_content',
			'type' => 'textarea',
		));
		
		/* 
		 * FOOTER OPTIONS
		*/
		$wp_customize->add_section( 'rh_footer_settings', array(
			'title' => esc_html__('Footer Options', 'rehub-framework'),
			'priority'  => 125,
			'panel' => 'panel_id',
		));
		
		// Footer Widgets
		$wp_customize->add_setting( 'rehub_footer_widgets', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '1',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'rehub_footer_widgets', array(
			'label' => esc_html__('Footer Widgets', 'rehub-framework'),
			'description' => esc_html__('Enable or Disable the footer widget area', 'rehub-framework'),
			'section'  => 'rh_footer_settings',
			'settings' => 'rehub_footer_widgets',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
		
		// Choose color style - widget section
		$wp_customize->add_setting('footer_style', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		)); 
		$wp_customize->add_control('footer_style', array(
			'label' => esc_html__('Choose color style of footer widget section', 'rehub-framework'),
			'section' => 'rh_footer_settings',
			'settings' => 'footer_style',
			'type' => 'select',
			'choices' => array(
				'1' => esc_html__('White style and dark fonts', 'rehub-framework'),
				'0' => esc_html__('Dark style and white fonts', 'rehub-framework'),
			),
		));

		// Background Color
		$wp_customize->add_setting( 'footer_color_background', array(
			'sanitize_callback' => 'sanitize_hex_color',
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'footer_color_background', array(
			'label' => esc_html__('Custom Background Color', 'rehub-framework'),
			'description' => esc_html__('Choose the background color or leave blank for default', 'rehub-framework'),
			'section' => 'rh_footer_settings',
			'settings' => 'footer_color_background',
		)));
		
		//Background Image
		$wp_customize->add_setting( 'footer_background_image', array(
			'sanitize_callback' => 'esc_url_raw',
		));
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'footer_background_image', array(
			'label' => esc_html__('Custom Background Image', 'rehub-framework'),
			'description' => esc_html__('Upload a background image or leave blank', 'rehub-framework'),
			'section' => 'rh_footer_settings',
			'settings' => 'footer_background_image',
		)));
		
		//Background Repeat
		$wp_customize->add_setting('footer_background_repeat', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => 'repeat',
		));
		$wp_customize->add_control('footer_background_repeat', array(
			'label' => esc_html__('Background Repeat', 'rehub-framework'),
			'section' => 'rh_footer_settings',
			'settings' => 'footer_background_repeat',
			'type' => 'select',
			'choices' => array(
				'repeat' => esc_html__('Repeat', 'rehub-framework'),
				'no-repeat' => esc_html__('No Repeat', 'rehub-framework'),
				'repeat-x' => esc_html__('Repeat X', 'rehub-framework'),
				'repeat-y' => esc_html__('Repeat Y', 'rehub-framework'),
			),
		));
		
		//Background Position
		$wp_customize->add_setting('footer_background_position', array(
			'sanitize_callback' => 'sanitize_key',
		));
		$wp_customize->add_control('footer_background_position', array(
			'label' => esc_html__('Background Position', 'rehub-framework'),
			'section' => 'rh_footer_settings',
			'settings' => 'footer_background_position',
			'type' => 'select',
			'choices' => array(
				'repeat' => esc_html__('Left', 'rehub-framework'),
				'center' => esc_html__('Center', 'rehub-framework'),
				'right' => esc_html__('Right', 'rehub-framework'),
			),
		));
		
		// Choose color style - bottom section
		$wp_customize->add_setting('footer_style_bottom', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		)); 
		$wp_customize->add_control('footer_style_bottom', array(
			'label' => esc_html__('Choose color style of bottom section', 'rehub-framework'),	
			'section' => 'rh_footer_settings',
			'settings' => 'footer_style_bottom',
			'type' => 'select',
			'choices' => array(
				'1' => esc_html__('White style and dark fonts', 'rehub-framework'),
				'0' => esc_html__('Dark style and white fonts', 'rehub-framework'),
			),
		));
		
		// Footer Bottom Text
		$wp_customize->add_setting('rehub_footer_text', array(
			'sanitize_callback' => 'wp_kses_post',
			'default' => esc_html__('2018 Wpsoul.com Design. All rights reserved.', 'rehub-framework'),
		)); 
		$wp_customize->add_control('rehub_footer_text', array(
			'label' => esc_html__('Footer Bottom Text', 'rehub-framework'),
			'description' => esc_html__('Enter your copyright text or whatever you want right here.', 'rehub-framework'),
			'section' => 'rh_footer_settings',
			'settings' => 'rehub_footer_text',
			'type' => 'textarea',
		));
		
		// Logo for footer
		$wp_customize->add_setting( 'rehub_footer_logo', array(
			'sanitize_callback' => 'esc_url_raw',
		));
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'rehub_footer_logo', array(
			'label' => esc_html__('Upload Logo for footer', 'rehub-framework'),
			'description' => esc_html__('Upload your logo for footer.', 'rehub-framework'),
			'section' => 'rh_footer_settings',
			'settings' => 'rehub_footer_logo',
		)));

		/* 
		 * Shop settings
		*/
		$wp_customize->add_section( 'rh_shop_settings', array(
			'title' => esc_html__('Woocommerce archive settings', 'rehub-framework'),
			'priority'  => 126,
			'panel' => 'panel_id',
		));

		$wp_customize->add_setting('woo_columns', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '3_col',
		));
		$wp_customize->add_control('woo_columns', array(
			'settings' => 'woo_columns',
			'label' => esc_html__('How to show archives', 'rehub-framework'),
			'section' => 'rh_shop_settings',
			'type' => 'select',
			'choices' => array(
				'3_col' => esc_html__('As 3 columns with sidebar', 'rehub-framework'),
				'4_col' => esc_html__('As 4 columns full width', 'rehub-framework'),
				'4_col_side' => esc_html__('As 4 columns + sidebar', 'rehub-framework'),
				'5_col_side' => esc_html__('As 5 columns + sidebar', 'rehub-framework'),
			),
		));	
		//Set sidebar to left side
		$wp_customize->add_setting( 'rehub_sidebar_left_shop', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'rehub_sidebar_left_shop', array(
			'label' => esc_html__('Set sidebar to left side?', 'rehub-framework'),
			'section'  => 'rh_shop_settings',
			'settings' => 'rehub_sidebar_left_shop',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));
		$wp_customize->add_setting( 'sidebar_mobile_shop', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '0',
		));
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'sidebar_mobile_shop', array(
			'label' => esc_html__('Mobile sliding sidebar on click?', 'rehub-framework'),
			'section'  => 'rh_shop_settings',
			'settings' => 'sidebar_mobile_shop',
			'type' => 'radio',
			'choices' => array(
				'0'  => esc_html__('Off', 'rehub-framework'),
				'1' => esc_html__('On', 'rehub-framework'),
			),
		)));				
		$wp_customize->add_setting('woo_design', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => 'simple',
		));
		$wp_customize->add_control('woo_design', array(
			'settings' => 'woo_design',
			'label' => esc_html__('Set design of woo archive', 'rehub-framework'),
			'section' => 'rh_shop_settings',
			'type' => 'select',
			'choices' => array(
				'simple' => esc_html__('Columns', 'rehub-framework'),
				'grid' => esc_html__('Grid', 'rehub-framework'),
				'gridtwo' => esc_html__('Compact Grid', 'rehub-framework'),
				'gridrev' => esc_html__('Directory Grid', 'rehub-framework'),
				'list' => esc_html__('List', 'rehub-framework'),
				'deallist' => esc_html__('Deal List', 'rehub-framework'),
			),
		));	
		$wp_customize->add_setting('woo_number', array(
			'sanitize_callback' => 'sanitize_key',
			'default' => '12',
		));
		$wp_customize->add_control('woo_number', array(
			'settings' => 'woo_number',
			'label' => esc_html__('Set count of products in loop', 'rehub-framework'),
			'section' => 'rh_shop_settings',
			'type' => 'select',
			'choices' => array(
				'12' => esc_html__('12', 'rehub-framework'),
				'16' => esc_html__('16', 'rehub-framework'),
				'24' => esc_html__('24', 'rehub-framework'),
				'30' => esc_html__('30', 'rehub-framework'),
			),
		));		
		$wp_customize->add_setting('wooloop_image_size', array(
			'sanitize_callback' => 'wp_kses',
		)); 
		$wp_customize->add_control('wooloop_image_size', array(
			'label' => esc_html__('Custom size for loop images', 'rehub-framework'),
			'description' => esc_html__('Add your size as width-height, example, 300-250', 'rehub-framework'),
			'section' => 'rh_shop_settings',
			'settings' => 'wooloop_image_size',
		));	

		$wp_customize->add_setting( 'wooloop_heading_color', array(
			'sanitize_callback' => 'sanitize_hex_color',
			'default' => '',
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'wooloop_heading_color', array(
			'label' => esc_html__('Headings color', 'rehub-framework'),
			'description' => esc_html__('You can set Button color in Theme options - Apearance - Offer Button color', 'rehub-framework'),
			'section' => 'rh_shop_settings',
			'settings' => 'wooloop_heading_color',
		)));	

		$wp_customize->add_setting( 'wooloop_price_color', array(
			'sanitize_callback' => 'sanitize_hex_color',
			'default' => '',
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'wooloop_price_color', array(
			'label' => esc_html__('Price color', 'rehub-framework'),
			'section' => 'rh_shop_settings',
			'settings' => 'wooloop_price_color',
		)));

		$wp_customize->add_setting( 'wooloop_sale_color', array(
			'sanitize_callback' => 'sanitize_hex_color',
			'default' => '',
		));
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'wooloop_sale_color', array(
			'label' => esc_html__('Sale tag color', 'rehub-framework'),
			'section' => 'rh_shop_settings',
			'settings' => 'wooloop_sale_color',
		)));

		$wp_customize->add_setting('wooloop_heading_size', array(
			'sanitize_callback' => 'wp_kses',
		)); 
		$wp_customize->add_control('wooloop_heading_size', array(
			'label' => esc_html__('Heading Font size', 'rehub-framework'),
			'section' => 'rh_shop_settings',
			'settings' => 'wooloop_heading_size',
			'type' => 'number',
		));		

		$wp_customize->add_setting('wooloop_price_size', array(
			'sanitize_callback' => 'wp_kses',
		)); 
		$wp_customize->add_control('wooloop_price_size', array(
			'label' => esc_html__('Price Font size', 'rehub-framework'),
			'section' => 'rh_shop_settings',
			'settings' => 'wooloop_price_size',
			'type' => 'number',
		));	

		$wp_customize->add_section( 'rh_woo_custom_settings', array(
			'title' => esc_html__('Woocommerce Custom Areas', 'rehub-framework'),
			'priority'  => 127,
			'panel' => 'panel_id',
		));	

		$wp_customize->add_setting('woo_code_zone_loop', array(
			'sanitize_callback' => 'wp_kses_post',
		)); 
		$wp_customize->add_control('woo_code_zone_loop', array(
			'label' => esc_html__('Code zone inside product loop', 'rehub-framework'),
			'description' => esc_html__('This code zone is visible on shop pages inside each product item.', 'rehub-framework').' <a href="https://wpsoul.com/make-smart-profitable-deal-affiliate-comparison-site-woocommerce/#featured-attributes-area-in-product-grid">Read more about code zones</a>',
			'section' => 'rh_woo_custom_settings',
			'settings' => 'woo_code_zone_loop',
			'type' => 'textarea',
		));	
		
		$wp_customize->add_setting('woo_code_zone_button', array(
			'sanitize_callback' => 'wp_kses_post',
		)); 
		$wp_customize->add_control('woo_code_zone_button', array(
			'label' => esc_html__('After Button Area', 'rehub-framework'),
			'description' => esc_html__('This code zone is visible on all products after Add to cart Button', 'rehub-framework'),
			'section' => 'rh_woo_custom_settings',
			'settings' => 'woo_code_zone_button',
			'type' => 'textarea',
		));	
		$wp_customize->add_setting('woo_code_zone_content', array(
			'sanitize_callback' => 'wp_kses_post',
		)); 
		$wp_customize->add_control('woo_code_zone_content', array(
			'label' => esc_html__('Before Content', 'rehub-framework'),
			'description' => esc_html__('This code zone is visible on all products before Content', 'rehub-framework'),
			'section' => 'rh_woo_custom_settings',
			'settings' => 'woo_code_zone_content',
			'type' => 'textarea',
		));	
		$wp_customize->add_setting('woo_code_zone_footer', array(
			'sanitize_callback' => 'wp_kses_post',
		)); 
		$wp_customize->add_control('woo_code_zone_footer', array(
			'label' => esc_html__('Before footer', 'rehub-framework'),
			'description' => esc_html__('This code zone is visible on all products Before Footer', 'rehub-framework'),
			'section' => 'rh_woo_custom_settings',
			'settings' => 'woo_code_zone_footer',
			'type' => 'textarea',
		));	
		$wp_customize->add_setting('woo_code_zone_float', array(
			'sanitize_callback' => 'wp_kses_post',
		)); 
		$wp_customize->add_control('woo_code_zone_float', array(
			'label' => esc_html__('In floating panel', 'rehub-framework'),
			'section' => 'rh_woo_custom_settings',
			'settings' => 'woo_code_zone_float',
			'type' => 'textarea',
		));														

		$wp_customize->get_setting( 'rehub_body_block' )->transport  = 'postMessage';
		$wp_customize->get_setting( 'rehub_content_shadow' )->transport  = 'postMessage';
		$wp_customize->get_setting( 'rehub_logo' )->transport  = 'postMessage';
		$wp_customize->get_setting( 'rehub_logo_retina' )->transport  = 'postMessage';
		$wp_customize->get_setting( 'rehub_logo_retina_width' )->transport  = 'postMessage';
		$wp_customize->get_setting( 'rehub_logo_retina_height' )->transport  = 'postMessage';
		$wp_customize->get_setting( 'rehub_text_logo' )->transport  = 'postMessage';
		$wp_customize->get_setting( 'rehub_text_slogan' )->transport  = 'postMessage';
		$wp_customize->get_setting( 'rehub_sticky_nav' )->transport  = 'postMessage';
		$wp_customize->get_setting( 'rehub_logo_sticky_url' )->transport  = 'postMessage';
		$wp_customize->get_setting( 'header_logoline_style' )->transport  = 'postMessage';
		$wp_customize->get_setting( 'header_menuline_style' )->transport  = 'postMessage';
		$wp_customize->get_setting( 'header_topline_style' )->transport  = 'postMessage';
		$wp_customize->get_setting( 'header_six_btn_login' )->transport  = 'postMessage';
	}

	/* Adds admin scripts and styles */
	public function rh_customizer_scripts() {
		$screen = get_current_screen();
		$screen_id = $screen->id;

		if( 'customize' == $screen_id ) {
			wp_enqueue_script( 'customizer-js', RH_FRAMEWORK_URL .'/assets/js/customizer.js', array('jquery'), '1.0', true );
			wp_enqueue_style( 'customizer-css', RH_FRAMEWORK_URL .'/assets/css/customizer.css' );
	    }
	}

	/* Adds scripts to Preview frame */
	public function rh_live_preview_scripts() {
		wp_enqueue_script( 'rh-customizer-js', RH_FRAMEWORK_URL .'/assets/js/theme-customizer.js', array( 'jquery','customize-preview' ), '1.0', true );
		wp_enqueue_script( 'sticky' );
	}

	/* Saves Customizer options to Theme ones */
	public function rh_save_theme_options() {
		$opt = get_option( 'rehub_option' );
		foreach(self::$rh_cross_option_fields as $key ) {
			$old_value = $opt[$key];
			$new_value = get_theme_mod( $key );
			if( $new_value != $old_value )
				$opt[$key] = $new_value;
			continue;
		}
		update_option( 'rehub_option', $opt );
		do_action('rehub_after_saving_customizer');
	}	

	/* Saves Theme options to Customizer ones */
	public function rh_save_customizer_options( $opt ){
	    foreach( self::$rh_cross_option_fields as $key ){
	        $old_value = get_theme_mod( $key );
	        if( isset($opt[$key]) ){
	            $new_value = $opt[$key];
	            if( $new_value != $old_value )
	                set_theme_mod( $key, $new_value );
	        }
	        continue;
	    }
	}	

	/* Get current menus array */
	public function rh_get_menus_customizer() {
		$choices = array();
		$menus = wp_get_nav_menus();
		foreach ($menus as $menu) {
			$choices[$menu->term_id] = $menu->name;
		}
		return $choices;
	}				
}

REHub_Framework_Customizer::instance();