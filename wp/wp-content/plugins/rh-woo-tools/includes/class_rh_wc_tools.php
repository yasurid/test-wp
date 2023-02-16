<?php

if ( !defined( 'WPINC' ) ) die;

class RH_WC_Tools {
	
	function __construct(){
		$this->includes();
		$this->init_hooks();
	}
	
		/* Include required core files */
	public function includes(){
		require RHWCT_DIRPATH .'includes/class_rh_wc_settings_tab.php';
		$rh_wc_settings = new RH_WC_Settings_Tab_Tools();
		$rh_wc_settings->init();
		
		if(WC_Admin_Settings::get_option('rhwct_show_accesories') === 'yes'){
			require RHWCT_DIRPATH .'includes/class_rh_wc_accessories.php';
			new RH_WC_Custom_Accessories();
		}
	}
	
	/* Hook into actions and filters.*/
	private function init_hooks(){
		add_action('admin_print_styles-woocommerce_page_wc-settings', array($this, 'admin_styles'));
		add_action('admin_enqueue_scripts', array($this, 'register_scripts'));
		add_action('woocommerce_admin_field_button', array($this, 'add_admin_field_button'));
		add_action('admin_footer', array($this, 'trigger_loading_for_hide_sku'));
		add_action('wp_ajax_hide_duplicate_sku_process', array($this, 'hide_duplicate_sku_process'));
		add_action('rh_woo_single_product_price', array($this, 'add_soldout'));
		
		add_filter('woocommerce_get_related_product_cat_terms', array($this, 'switch_related_products'), 90, 2);
		add_filter('woocommerce_get_related_product_tag_terms', array($this, 'switch_related_products'), 90, 2);
		add_filter('woocommerce_product_tabs', array($this, 'disable_desc_tab'));
		add_filter('woocommerce_product_tabs', array($this, 'product_custom_tabs'));
		add_filter('wc_product_has_unique_sku', '__return_false');
	}

	/* Add admib styles */
	function admin_styles(){
		?>
		<style type="text/css">
			#progress, #trigger, #finished{display:none;}
			#progress {width:400px; height:8px; margin:8px 0;}
			#finished {color:green; font-weight:600;}
		</style>
		<?php
	}

	function add_soldout(){
		$soldoutenable = WC_Admin_Settings::get_option('rhwct_soldout');
		if($soldoutenable && ($soldoutenable=='yes' || $soldoutenable=='1')){
			global $post;
			$sinclude = WC_Admin_Settings::get_option('rhwct_soldout_in');
			$sexclude = WC_Admin_Settings::get_option('rhwct_soldout_ext');
			if($sinclude){
				$sinclude = wp_parse_id_list($sinclude);
				$post_terms = wp_get_post_terms($post->ID, 'product_cat', array("fields" => "ids"));
				$post_in_cat = array_intersect($post_terms, $sinclude);
				if(array_filter($post_in_cat)) {
					rh_soldout_bar($post->ID, '#e33333', true);
				}
			}else if($sexclude){
				$sexclude = wp_parse_id_list($sexclude);
				$post_terms = wp_get_post_terms($post->ID, 'product_cat', array("fields" => "ids"));
				$post_in_cat = array_intersect($post_terms, $sexclude);
				if(!array_filter($post_in_cat)) {
					rh_soldout_bar($post->ID, '#e33333', true);
				}				
			}
			else{
				rh_soldout_bar($post->ID, '#e33333', true);
			}
		}
		
	}

	/* Register admin scripts */
	function register_scripts(){
		wp_register_script( 'rhwct-progressbar', RHWCT_URIPATH .'js/progressbar.js', array(), '0.5.6', true);
		wp_register_script( 'rhwct-scripts', RHWCT_URIPATH .'js/scripts.js', array(), RHWCT_VERSION, true);
	}

	/* Add 'Hide' button to WooCommerce Settings */
	function add_admin_field_button($value){
		if($value['type'] == 'button'): 
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
			</th>
			<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
				<button id="<?php echo esc_attr( $value['id'] ); ?>" style="<?php echo esc_attr( $value['css'] ); ?>" class="<?php echo esc_attr( $value['class'] ); ?>"/><?php echo esc_attr( $value['value'] ); ?></button>
				<p><?php echo esc_html( $value['desc'] ); ?></p>
			</td>
		</tr>
		<?php
		endif;
	}
	
	/* Trigger loading for hididding sku */
	function trigger_loading_for_hide_sku(){
		if(isset($_REQUEST['page']) && $_REQUEST['page'] == 'wc-settings'){
			$ajax_nonce = wp_create_nonce( 'rh-woo' );
			?>
			<div id='progress'></div>
			<p id="finished"><?php esc_html_e('Process is finished.', 'rh-wctools'); ?></p>
			<?php
			wp_enqueue_script( 'rhwct-progressbar' );
			wp_enqueue_script( 'rhwct-scripts' );
			
			$translation_array = array(
				'ajax_nonce' => $ajax_nonce,
			);
			wp_localize_script( 'rhwct-scripts', 'rhwct_translation', $translation_array );
		}
	}
	
	/* Hide duplicated sku proces */
	function hide_duplicate_sku_process(){
		check_ajax_referer('rh-woo', 'security');

		if(empty($_GET['paged'])){
			$_GET['paged'] = 1;
		}
		
		$pricearray = array();
		$output = '';
		
		$args = array(
			'posts_per_page' => 100,
			'post_type' => 'product',
			'post_status' => 'publish',
			'paged' => $_GET['paged'],
			'order' => 'ASC',
			'meta_key' => '_sku',
			'meta_query' => array(
				array(
					'key' => '_price',
					'compare' => 'EXISTS',
				),            
			),
			'orderby' => 'meta_value',
		);
		
		$pageposts = new WP_Query( $args );
		
		if ( $pageposts->have_posts() ) {
			while ($pageposts->have_posts() ) {
				$pageposts->the_post();
				global $post;
				$pid = $post->ID;
				$sku = get_post_meta($pid, '_sku', true);
				$price = get_post_meta($pid, '_price', true);
				$pricearray[$sku][$pid] = $price; 
			}
			wp_reset_query();
		}
	
		foreach($pricearray as $skukey){
			$minprice = min($skukey);
			$minpricekey = array_search($minprice, $skukey);
			foreach($skukey as $pricekey => $pricevalue){
				if($pricekey == $minpricekey){
					wp_remove_object_terms($pricekey, array('exclude-from-search', 'exclude-from-catalog'), 'product_visibility');
				}else{
					wp_set_post_terms($pricekey, array('exclude-from-search', 'exclude-from-catalog'), 'product_visibility');
				}
			}
		}

		if(!isset($_GET['time']) || $_GET['time'] <= 0 ){
			$_GET['time'] = (100/$pageposts->max_num_pages) /100;
		}
		
		$output = array(
			'page_num' => $pageposts->max_num_pages, 
			'post_count' => $pageposts->post_count, 
			'total_count' => $pageposts->found_posts, 
			'paged' => $_GET['paged'] + 1, 
			'time' => $_GET['time'] + (100/$pageposts->max_num_pages) /100,
		);
	
		wp_send_json($output);
	}

	/* Trigger for Related Products section */
	function switch_related_products($terms, $pid){
		$incl = WC_Admin_Settings::get_option('rhwct_incl_related_products');
		$excl = WC_Admin_Settings::get_option('rhwct_excl_related_products');
		$sett = WC_Admin_Settings::get_option('rhwct_hide_related_products');
		$incl_arr = explode(',', $incl); 
		$excl_arr = explode(',', $excl);
		
		if('yes' === $sett){
			if(!empty($excl_arr) && in_array($pid, $excl_arr)){
				$terms = $terms;
			}else{
				$terms = array();
			}
		}
		
		if(!empty($incl_arr) && in_array($pid, $incl_arr)){
			$terms = array();
		}
		return $terms;
	}

	/* Removes Description tab in Tabs array */
	function disable_desc_tab($tabs) {
		if(WC_Admin_Settings::get_option('rhwct_hide_desc_tab') === 'yes') {
			unset($tabs['description']);
		}
		return $tabs;
	}

	/* Adds Custom Tabs the Product Page (see WC -> Settings -> ReHub Tools tab) */
	function product_custom_tabs( $tabs ) {

		$tab_titles = WC_Admin_Settings::get_option('rhwct_tab_product_titles');
		
		if( empty( $tab_titles ) )
			return $tabs;

		$tab_titles = array_map( 'trim', explode(';', $tab_titles) );
		$tab_orders = WC_Admin_Settings::get_option('rhwct_tab_product_orders');
		
		if( empty( $tab_orders ) )
			return $tabs;
		
		$tab_orders = array_map( 'trim', explode(';', $tab_orders) );
		$tab_contents = WC_Admin_Settings::get_option('rhwct_tab_product_contents');
		
		if( empty( $tab_contents ) )
			return $tabs;
		
		$tab_contents = array_map( 'trim', explode('EOT;', $tab_contents) );

		foreach( $tab_titles as $key => $tab_title ){
			$tabs['rhwct_tab_'.$key] = array(
				'title' => $tab_title,
				'priority' => $tab_orders[$key],
				'content' => nl2br( $tab_contents[$key] ),
				'callback'  => array($this, 'product_custom_tab_content'),
			);
		}

		return $tabs;
	}

	/* Callback function for Content of the Castom Tabs */
	function product_custom_tab_content( $key, $tab ){
		echo do_shortcode($tab['content']);
	}
}