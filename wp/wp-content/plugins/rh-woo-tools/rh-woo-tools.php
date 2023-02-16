<?php
/*
Plugin Name: RH WooCommerce Tools
Plugin URI: https://themeforest.net/item/rehub-directory-multi-vendor-shop-coupon-affiliate-theme/7646339
Description: Allows managing WooCommerce plugin with help of Rehub theme tools.
Version: 1.5
Author: Wpsoul
Author URI: http://wpsoul.com
WC requires at least: 4.0.0
WC tested up to: 7.0
Text Domain: rh-wctools
Domain Path: /lang/
*/

if ( !defined( 'WPINC' ) ) die;

define( 'RHWCT_VERSION', '1.5' );
define( 'RHWCT_DIRPATH', plugin_dir_path( __FILE__ ) );
define( 'RHWCT_URIPATH', plugin_dir_url( __FILE__ ) );

/* Init plugin */
function rh_woo_tools_init() {
	if(class_exists('WooCommerce') && ('rehub' == get_option( 'template') || 'rehub-theme' == get_option( 'template'))) {
		
		require RHWCT_DIRPATH .'includes/class_rh_wc_tools.php';
		new RH_WC_Tools();
		
		load_plugin_textdomain( 'rh-wctools', FALSE, RHWCT_DIRPATH . 'lang' );
		
	} else {
	  add_action('admin_notices', 'rhwct_admin_notice');
	}
}
add_action( 'plugins_loaded', 'rh_woo_tools_init' );

/* If the Rehub theme or WooCommerce plugin is not installed show noutification */
function rhwct_admin_notice(){ ?>
	<div class="notice notice-warning">
		<p><?php printf( esc_html__('Sorry, but RH WooCommerce Tools works only with %s and WooCommerce plugin.', 'rh-wctools'), '<a href="https://1.envato.market/wc-tools" target="_blank">REHub theme</a>' ); ?></p>
	</div>
	<?php
}

/* update Class */
if(!class_exists('PucFactory')){
	require RHWCT_DIRPATH .'includes/class-update-checker.php';
}

/* Update plugin */
function rh_update_check_rhwct(){

	if(defined('PLUGIN_REPO')){
		$serverupdateurl = PLUGIN_REPO;
	} else{
		$serverupdateurl = 'https://wpsoul.net/serverupdate/';
	}
	
	$tf = 'tfuser=';
	$rehub_options = get_option( 'Rehub_Key' );
	$tf_username = isset( $rehub_options[ 'tf_username' ] ) ? $rehub_options[ 'tf_username' ] : '';
	
	if($tf_username) {
		$tf = 'tfuser='.$tf_username;
	}

	$wpsmcal_checker = PucFactory::buildUpdateChecker(
		$serverupdateurl.'?action=get_metadata&slug=rh-woo-tools&'. $tf,
		__FILE__,
		'rh-woo-tools',
		'24'
	);
}
add_action('admin_init', 'rh_update_check_rhwct');