<?php
/**
 * Plugin Name: GreenShift Advanced Animation Addon
 * Description: Build most advanced animations with GSAP and Greenshift
 * Author: GreenshiftWP
 * Author URI: https://greenshiftwp.com
 * Version: 1.8.5
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define Dir URL
define('GREENSHIFTGSAP_DIR_URL', plugin_dir_url(__FILE__));
define( 'GREENSHIFTGSAP_DIR_PATH', plugin_dir_path( __FILE__ ) );

// Define Freemius
if ( ! function_exists( 'gspb_gsap_freemius' ) ) {
    // Create a helper function for easy SDK access.
    function gspb_gsap_freemius() {
        global $gspb_gsap_freemius;

        if ( ! isset( $gspb_gsap_freemius ) ) {
            // Include Freemius SDK.
            if ( file_exists( dirname( dirname( __FILE__ ) ) . '/greenshift-animation-and-page-builder-blocks/fs/start.php' ) ) {
                // Try to load SDK from parent plugin folder.
                require_once dirname( dirname( __FILE__ ) ) . '/greenshift-animation-and-page-builder-blocks/fs/start.php';
            } else {
                return;
            }

            $gspb_gsap_freemius = fs_dynamic_init( array(
                'id'                  => '9741',
                'slug'                => 'greenshiftgsap',
                'type'                => 'plugin',
                'public_key'          => 'pk_66e4a2707335646183f82cc537e5f',
                'is_premium'          => true,
                'is_premium_only'     => true,
                'has_paid_plans'      => true,
                'is_org_compliant'    => false,
                'parent'              => array(
                    'id'         => '9740',
                    'slug'       => 'greenshift-animation-and-page-builder-blocks',
                    'public_key' => 'pk_672fcb7f9a407e0858ba7792d43cb',
                    'name'       => 'Greenshift - Animation and page builder for Gutenberg Wordpress',
                ),
                'menu'                => array(
                    'first-path'     => 'plugins.php',
                    'account'        => false,
                    'support'        => false,
                ),
                'secret_key'          => 'sk_EC8TRP[];#5zXR-nvtc2RQ1iBH]>I',
            ) );
        }

        return $gspb_gsap_freemius;
    }
}

function gspb_gsap_freemius_is_parent_active_and_loaded() {
    // Check if the parent's init SDK method exists.
    return function_exists( 'gspb_freemius' );
}

function gspb_gsap_freemius_is_parent_active() {
    $active_plugins = get_option( 'active_plugins', array() );

    if ( is_multisite() ) {
        $network_active_plugins = get_site_option( 'active_sitewide_plugins', array() );
        $active_plugins         = array_merge( $active_plugins, array_keys( $network_active_plugins ) );
    }

    foreach ( $active_plugins as $basename ) {
        if ( 0 === strpos( $basename, 'greenshift-animation-and-page-builder-blocks/' ) ||
             0 === strpos( $basename, 'greenshift-animation-and-page-builder-blocks-premium/' )
        ) {
            return true;
        }
    }

    return false;
}

function gspb_gsap_freemius_init() {
    if ( gspb_gsap_freemius_is_parent_active_and_loaded() ) {

		if('rehub-theme' != get_option( 'template' )){
			// Init Freemius.
			gspb_gsap_freemius();
			

			// Signal that the add-on's SDK was initiated.
			do_action( 'gspb_gsap_freemius_loaded' );

			// Parent is active, add your init code here.
		}

		// Hook: Editor assets.
		add_action('enqueue_block_editor_assets', 'greenShiftGsap_editor_assets');

    } else {
        // Parent is inactive, add your error handling here.
		add_action( 'admin_notices', 'gspb_gsap_admin_notice_warning' );
    }
}


if ( gspb_gsap_freemius_is_parent_active_and_loaded() ) {
	// If parent already included, init add-on.
	gspb_gsap_freemius_init();
} else if ( gspb_gsap_freemius_is_parent_active() ) {
	// Init add-on only after the parent is loaded.
	add_action( 'gspb_freemius_loaded', 'gspb_gsap_freemius_init' );
} else {
	// Even though the parent is not activated, execute add-on for activation / uninstall hooks.
	gspb_gsap_freemius_init();
}


/**
 * GreenShift Blocks Category
 */
if(!function_exists('gspb_greenShiftGsap_category')){
	function gspb_greenShiftGsap_category( $categories, $post ) {
		return array_merge(
			array(
				array(
					'slug'  => 'Greenshiftpro',
					'title' => __( 'GreenShift Animations'),
				),
			),
			$categories
		);
	}
}
add_filter( 'block_categories_all', 'gspb_greenShiftGsap_category', 1, 2 );

//////////////////////////////////////////////////////////////////
// Functions to render conditional scripts
//////////////////////////////////////////////////////////////////

// Hook: Frontend assets.
add_action('init', 'greenShiftGsap_register_scripts_blocks');
add_filter('render_block', 'greenShiftGsap_block_script_assets', 10, 2);

if(!function_exists('greenShiftGsap_register_scripts_blocks')){
	function greenShiftGsap_register_scripts_blocks()
	{

		wp_register_script(
			'gsap-animation',
			GREENSHIFTGSAP_DIR_URL . 'libs/gsap/gsap.min.js',
			array(),
			'3.10',
			true
		);
		// scroll trigger
		wp_register_script(
			'gsap-scrolltrigger',
			GREENSHIFTGSAP_DIR_URL . 'libs/gsap/ScrollTrigger.min.js',
			array('gsap-animation'),
			'3.10',
			true
		);
		wp_register_script(
			'gsapflip',
			GREENSHIFTGSAP_DIR_URL . 'libs/gsap/Flip.min.js',
			array('gsap-animation'),
			'3.10',
			true
		);
		wp_register_script(
			'gsapsplittext',
			GREENSHIFTGSAP_DIR_URL . 'libs/gsap/SplitText.min.js',
			array('gsap-animation'),
			'3.10',
			true
		);
		wp_register_script(
			'gsapsmoothscroll',
			GREENSHIFTGSAP_DIR_URL . 'libs/gsap/ScrollSmoother.min.js',
			array('gsap-animation', 'gsap-scrolltrigger'),
			'3.10',
			true
		);
		wp_register_script(
			'gsapsmoothscroll-init',
			GREENSHIFTGSAP_DIR_URL . 'libs/gsap/gsap-smoothscroll-init.js',
			array('gsapsmoothscroll'),
			'3.10',
			true
		);
		wp_register_script(
			'gsapsvgdraw',
			GREENSHIFTGSAP_DIR_URL . 'libs/gsap/DrawSVGPlugin.min.js',
			array('gsap-animation'),
			'3.10',
			true
		);
		wp_register_script(
			'gsapsvgmorph',
			GREENSHIFTGSAP_DIR_URL . 'libs/gsap/MorphSVGPlugin.min.js',
			array('gsap-animation'),
			'3.10',
			true
		);
		wp_register_script(
			'gsapsvgpath',
			GREENSHIFTGSAP_DIR_URL . 'libs/gsap/MotionPathPlugin.min.js',
			array('gsap-animation'),
			'3.10',
			true
		);
	
		// gsap init
		wp_register_script(
			'gsap-animation-init',
			GREENSHIFTGSAP_DIR_URL . 'libs/gsap/gsap-init.js',
			array('gsap-animation'),
			'4.2',
			true
		);
		//gsap reveal init
		wp_register_script(
			'gsap-reveal-init',
			GREENSHIFTGSAP_DIR_URL . 'libs/gsap/gsap-reveal-init.js',
			array('gsap-animation'),
			'3.12',
			true
		);
		wp_register_script(
			'gsap-mousemove-init',
			GREENSHIFTGSAP_DIR_URL . 'libs/gsap/gsap-mousemove-init.js',
			array('gsap-animation'),
			'3.9.2',
			true
		);
		wp_register_script(
			'gsap-scrollparallax-init',
			GREENSHIFTGSAP_DIR_URL . 'libs/gsap/gsap-scrollparallax-init.js',
			array('gsap-animation'),
			'3.9.3',
			true
		);
	
		// flip init
		wp_register_script(
			'gsap-flip-init',
			GREENSHIFTGSAP_DIR_URL . 'libs/gsap/gsap-flip-init.js',
			array('gsap-animation', 'gsap-scrolltrigger', 'gsapflip'),
			'4.1',
			true
		);

		// flip init
		wp_register_script(
			'gsap-filter-init',
			GREENSHIFTGSAP_DIR_URL . 'libs/gsap/gsap-filter-init.js',
			array('gsap-animation', 'gsapflip'),
			'3.9.2',
			true
		);
	
		// sequencer init
		wp_register_script(
			'gsap-seq-init',
			GREENSHIFTGSAP_DIR_URL . 'libs/gsap/gsap-seq-init.js',
			array('gsap-animation', 'gsap-scrolltrigger'),
			'4.0',
			true
		);

		//gsap mousefollow init
		wp_register_script(
			'gsap-mousefollow-init',
			GREENSHIFTGSAP_DIR_URL . 'libs/gsap/gsap-mousefollow.js',
			array('gsap-animation'),
			'3.11',
			true
		);
	
		// blob animate init
		wp_register_script(
			'gs-blob-init',
			GREENSHIFTGSAP_DIR_URL . 'libs/blob/index.js',
			array('gsap-animation'),
			'1.0',
			true
		);

		//Lottie interactive loader
		wp_register_script('gs-lottieloader', GREENSHIFTGSAP_DIR_URL . '/libs/lottie/index.js', array(), '1.1', true);
	
		//register blocks on server side with block.json
		register_block_type(__DIR__ . '/blockrender/animation-container');
		register_block_type(__DIR__ . '/blockrender/blob');
		register_block_type(__DIR__ . '/blockrender/flipstate');
		register_block_type(__DIR__ . '/blockrender/sequencer');
		register_block_type(__DIR__ . '/blockrender/pinscroll');
		register_block_type(__DIR__ . '/blockrender/lottie');
		register_block_type(__DIR__ . '/blockrender/flipfilter');
	
	}
}

if(!function_exists('greenShiftGsap_block_script_assets')){
	function greenShiftGsap_block_script_assets($html, $block)
	{
		// phpcs:ignore
	
		//Main styles for blocks are loaded via Redux. Can be found in src/customJS/editor/store/index.js and src/gspb-library/helpers/reusable_block_css/index.js
	
		if(!is_admin()){
		
			// looking for gsap animation.
			if ($block['blockName'] === 'greenshift-blocks/animation-container') {
				wp_enqueue_script('gsap-animation');
				wp_enqueue_script('gsap-scrolltrigger');
		
				// looking for gsap libraries 
				if (!empty($block['attrs']) && isset($block['attrs']['animation_type'])) {
		
					if ($block['attrs']['animation_type'] === 'text_transformations') {
						wp_enqueue_script('gsapsplittext');
					}
					if ($block['attrs']['animation_type'] === 'svg_line_draw') {
						wp_enqueue_script('gsapsvgdraw');
					}
					if ($block['attrs']['animation_type'] === 'svg_motion_path') {
						wp_enqueue_script('gsapsvgpath');
					}
					if ($block['attrs']['animation_type'] === 'svg_morph') {
						wp_enqueue_script('gsapsvgmorph');
					}
				}
	
				if (!empty($block['attrs']) && !empty($block['attrs']['reveal_enabled'])) {
					wp_enqueue_script('gsap-reveal-init');
				}
				if (!empty($block['attrs']) && !empty($block['attrs']['scroll_parallax_enabled'])) {
					wp_enqueue_script('gsap-scrollparallax-init');
				}
				if (!empty($block['attrs']) && !empty($block['attrs']['mouse_move_enabled'])) {
					wp_enqueue_script('gsap-mousemove-init');
				}
				
				$attributearray = array(               
				"x",
				"y",
				"z",
				"xo",
				"yo",
				"r",
				"rx",
				"ry",
				"s",
				"sx",
				"sy",
				"o",
				"width",
				"height",
				"background",
				"origin",
				"text",
				"stagger",
				"stdelay",
				"path",
				"path_align",
				"path_orient",
				"path_align_x",
				"path_align_y",
				"path_start",
				"path_end",
				"morphend",
				"morphstart",
				"morphorigin", 
				"multiple_animation", 
				"delay",
				"yoyo",
				"loop",
				"repeatdelay",
				"strandom",
				"stchild",
				"customtrigger",
				"customobject",
				"triggerstart",
				"triggerend",
				"triggerscrub",
				"pinned",
				"pinspace",
				"triggeraction",
				"triggersnap",
				"batchint");
	
				foreach ($attributearray as $attributeitem){
					if(!empty($block['attrs'][$attributeitem])){
						wp_enqueue_script('gsap-animation-init');
						break;
					}
				}
			
				// gsap init
			}
			// looking for gsap Flip
			if ($block['blockName'] === 'greenshift-blocks/flipstate') {
				wp_enqueue_script('gsap-flip-init');
			}
			if ($block['blockName'] === 'greenshift-blocks/flipfilter') {
				wp_enqueue_script('gsap-filter-init');
			}
			// looking for gsap sequencer
			if ($block['blockName'] === 'greenshift-blocks/sequencer') {
				wp_enqueue_script('gsap-seq-init');
			}
			// looking for pin scroll
			if ($block['blockName'] === 'greenshift-blocks/pinscroll') {
				wp_enqueue_script('gsap-animation');
				wp_enqueue_script('gsap-scrolltrigger');
				wp_enqueue_script('gsap-animation-init');
			}
		
			// looking for blob animation
			if ($block['blockName'] === 'greenshift-blocks/blob') {
				wp_enqueue_script('gsap-animation');
				wp_enqueue_script('gs-blob-init');
				$html = str_replace('stopcolor', 'stop-color', $html);
			}

			if($block['blockName'] == 'greenshift-blocks/lottie'){
				wp_enqueue_script('gs-lottieloader');
			}
		
			if (!empty($block['attrs']) && !empty($block['attrs']['animatesvg'])) {
				wp_enqueue_script('gsap-animation');
				wp_enqueue_script('gsap-scrolltrigger');
				wp_enqueue_script('gsapsvgdraw');
				wp_enqueue_script('gsap-animation-init');
			}
			if($block['blockName'] == 'greenshift-blocks/heading' && !empty($block['attrs']['highlightanimate'])){
				wp_enqueue_script( 'greenshift-inview' );
			}
			if(!empty($block['attrs']) && !empty( $block['attrs']['overlay']['inview'])){
				wp_enqueue_script( 'greenshift-inview' );
			}
			if (!empty($block['attrs']) && !empty($block['attrs']['animation']['usegsap'])) {
				wp_enqueue_script('gsap-animation');
				wp_enqueue_script('gsap-scrolltrigger');
				if (!empty($block['attrs']['animation']['text'])) {
					wp_enqueue_script('gsapsplittext');
				}
				wp_enqueue_script('gsap-animation-init');
			}
		}
	
	
		return $html;
	}
}

//////////////////////////////////////////////////////////////////
// Enqueue Gutenberg block assets for backend editor.
//////////////////////////////////////////////////////////////////

if(!function_exists('greenShiftGsap_editor_assets')){
	function greenShiftGsap_editor_assets()
	{
		// phpcs:ignor
	
		$index_asset_file = include(GREENSHIFTGSAP_DIR_PATH . 'build/index.asset.php');
	
	
		// Blocks Assets Scripts
		wp_enqueue_script(
			'greenShiftGsap-block-js', // Handle.
			GREENSHIFTGSAP_DIR_URL . 'build/index.js',
			array('greenShift-editor-js', 'greenShift-library-script', 'wp-block-editor', 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor', 'wp-data'),
			$index_asset_file['version'],
			true
		);
		
	
		// Styles.
	
		wp_enqueue_style(
			'greenShiftGsap-block-css', // Handle.
			GREENSHIFTGSAP_DIR_URL . 'build/index.css', // Block editor CSS.
			array('greenShift-library-editor', 'wp-edit-blocks', 'greenShift-icons'),
			$index_asset_file['version']
		);
	
	
	
		// gsap animation
		wp_enqueue_script('gsap-animation');
		wp_enqueue_script('gsap-scrolltrigger');
		wp_enqueue_script('gsapsplittext');
		wp_enqueue_script('gsapsvgdraw');
		wp_enqueue_script('gsapsvgpath');
		wp_enqueue_script('gsapsvgmorph');
		wp_enqueue_script('gsapflip');
		wp_enqueue_script('gsap-flip-init');
		wp_enqueue_script('gsap-seq-init');
	
		// gsap init
		wp_enqueue_script('gsap-animation-init');
		wp_enqueue_script('gsap-reveal-init');
		wp_enqueue_script('gsap-scrollparallax-init');
		wp_enqueue_script('gsap-mousemove-init');

	}
}

//////////////////////////////////////////////////////////////////
// Show if parent is not loaded
//////////////////////////////////////////////////////////////////
function gspb_gsap_admin_notice_warning() {
	?>
	<div class="notice notice-warning">
		<p><?php printf( __( 'Please, activate %s plugin to use Animation Addon' ), '<a href="https://wordpress.org/plugins/greenshift-animation-and-page-builder-blocks" target="_blank">Greenshift</a>' ) ; ?></p>
	</div>
	<?php
}

//////////////////////////////////////////////////////////////////
// What new link
//////////////////////////////////////////////////////////////////
function gspb_gsap_change_action_links( $links ) {

	$links = array_merge( array(
		'<a href="https://greenshiftwp.com/changelog" style="color:#93003c" target="_blank">' . __( 'What\'s New', 'greenshiftgsap' ) . '</a>'
	), $links );

	return $links;

}
add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'gspb_gsap_change_action_links' );