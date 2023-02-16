<?php
/**
 * Post Image Gallery
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * RH_Meta_Box_Post.
 */
class RH_Meta_Box_Post {

	/**
	 * Is meta boxes saved once?
	 */
	private static $saved_meta_boxes = false;

	/**
	 * Meta box error messages.
	 */
	public static $meta_box_errors = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 35 );
		add_action( 'woocommerce_product_options_pricing', array( $this, 'show_rehub_woo_meta_box_inner' ) ); //Fields for external products
		add_filter( 'woocommerce_product_data_tabs', array($this, 'rh_custom_code_data_tab'));
		add_action('woocommerce_product_data_panels', array($this, 'rh_custom_code_data_fields'));
		add_action( 'save_post', array( $this, 'save_meta_boxes' ), 10, 2);
		add_action( 'admin_head', array( $this, 'meta_scripts' ));
		//script for panels are loaded in vendor\vafpress\public\js\metabox.min.js, vendor\vafpress\css\metabox.min.css
		
		add_action('admin_init',  array( $this, 'rhwoostore_tax_fields'), 1); //Woocommerce taxonomy meta
		add_action('admin_init', array( $this, 'category_tax_fields'), 1); //Category taxonomy meta

		if(REHub_Framework::get_option('enable_brand_taxonomy') == 1){
			add_action('admin_init', array( $this, 'dealstore_tax_fields'), 1); //Affiliate store taxonomy meta
		}

		// Error handling (for showing errors from meta boxes on next page load)
		add_action( 'admin_notices', array( $this, 'output_errors' ) );
		add_action( 'shutdown', array( $this, 'save_errors' ) );
	}

	/**
	 * Add an error message.
	 * @param string $text
	 */
	public static function add_error( $text ) {
		self::$meta_box_errors[] = $text;
	}

	/**
	 * Save errors to an option.
	 */
	public function save_errors() {
		update_option( 'rehub_meta_box_errors', self::$meta_box_errors );
	}

	public function meta_scripts() {
		global $pagenow, $post;
		if ( $pagenow=='post-new.php' || $pagenow=='post.php' ) {
		    wp_enqueue_script('jquery-ui-datepicker');
			$output = '<script type="text/javascript">
			jQuery(function() {
				jQuery(".rehubdatepicker").each(function(){jQuery(this).datepicker({dateFormat: "yy-mm-dd"});});
				var imageFrame;jQuery(".meta_box_upload_image_button").click(function(e){e.preventDefault();return $self=jQuery(e.target),$div=$self.closest("div.meta_box_image"),imageFrame?void imageFrame.open():(imageFrame=wp.media({title:"Choose Image",multiple:!1,library:{type:"image"},button:{text:"Use This Image"}}),imageFrame.on("select",function(){selection=imageFrame.state().get("selection"),selection&&selection.each(function(e){console.log(e);{var t=e.attributes.sizes.full.url;e.id}$div.find(".meta_box_preview_image").attr("src",t),$div.find(".meta_box_upload_image").val(t)})}),void imageFrame.open())}),jQuery(".meta_box_clear_image_button").click(function(){var e=jQuery(this).parent().siblings(".meta_box_default_image").text();return jQuery(this).parent().siblings(".meta_box_upload_image").val(""),jQuery(this).parent().siblings(".meta_box_preview_image").attr("src",e),!1});
			});
			</script>';	
			echo $output;
		    if ( 'post' === $post->post_type || 'blog' === $post->post_type ) { //Easy woo chooser for reviews

		    	$path_script = get_template_directory_uri() . '/jsonids/json-ids.php';
	            $review_woo_link = vp_metabox('rehub_post.review_post.0.review_woo_product.0.review_woo_link');
	            $review_woo_links = vp_metabox('rehub_post.review_post.0.review_woo_list.0.review_woo_list_links');
	            if(!empty($review_woo_link)){
	            	$woobox_array = array();
					$woobox_title = get_the_title($review_woo_link);
					$woobox_array[] = array( 'id' => $review_woo_link, 'name' => $woobox_title );  		       	
	            	$wooboxpre = json_encode( $woobox_array );   
	            }
	            if(!empty($review_woo_links)){
	            	$review_woo_linkss = explode(',', $review_woo_links);
	            	$woolist_array = array();
					foreach($review_woo_linkss as $review_woo_linksid){
						$woolist_title = get_the_title($review_woo_linksid);
						$woolist_array[] = array( 'id' => $review_woo_linksid, 'name' => $woolist_title );
					}  		       	
	            	$woolistpre = json_encode( $woolist_array );   
	            }            
	            $wooboxprep = (!empty($wooboxpre)) ? $wooboxpre : 'null';	
	            $woolistprep = (!empty($woolistpre)) ? $woolistpre : 'null';    	
			    $output = '
			    <link rel="stylesheet" href="'.get_template_directory_uri().'/jsonids/css/token-input.css" />
			    <script data-cfasync="false" src="'.get_template_directory_uri().'/jsonids/js/jquery.tokeninput.min.js"></script>         
			    <script data-cfasync="false">
					jQuery(function () {
						jQuery("input[name=\"rehub_post[review_post][0][review_woo_product][0][review_woo_link]\"]").tokenInput("'.$path_script.'", { 
							minChars: 3,
							preventDuplicates: true,
							theme: "rehub",
							prePopulate: '.$wooboxprep.',
							tokenLimit: 1,
							onSend: function(params) {
								params.data.posttype = "product";
								params.data.postnum = 5;
							}
						});
						jQuery("input[name=\"rehub_post[review_post][0][review_woo_list][0][review_woo_list_links]\"]").tokenInput("'.$path_script.'", { 
							minChars: 3,
							preventDuplicates: true,
							theme: "rehub",
							prePopulate: '.$woolistprep.',
							onSend: function(params) {
								params.data.posttype = "product";
								params.data.postnum = 5;
							}
						});					
					});
				</script>';	         
			    echo ''.$output;

			}			
		}    
	}	

	/**
	 * Show any stored error messages.
	 */
	public function output_errors() {
		$errors = maybe_unserialize( get_option( 'rehub_meta_box_errors' ) );

		if ( ! empty( $errors ) ) {

			echo '<div id="rehub_errors" class="error notice is-dismissible">';
			
			foreach ( $errors as $error ) {
				echo '<p>' . wp_kses_post( $error ) . '</p>';
			}
			
			echo '</div>';

			// Clear
			delete_option( 'rehub_meta_box_errors' );
		}
	}

	public static function meta_for_posts() {
		$post_custom_meta_fields = apply_filters('rh_post_custom_meta_fields', array(
		    array(
		        'label'=>  esc_html__('Offer url', 'rehub-framework'),
		        'desc'  => esc_html__('Insert url of offer', 'rehub-framework'),
		        'id'    => 'rehub_offer_product_url',
		        'type'  => 'url'
		    ),	
		    array(
		        'label'=>  esc_html__('Name of product', 'rehub-framework'),
		        'desc'  => esc_html__('Insert title or leave blank', 'rehub-framework'),
		        'id'    => 'rehub_offer_name',
		        'type'  => 'text'
		    ),
		    array(
		        'label'=>  esc_html__('Short description of product', 'rehub-framework'),
		        'desc'  => esc_html__('Enter description of product or leave blank', 'rehub-framework'),
		        'id'    => 'rehub_offer_product_desc',
		        'type'  => 'text'
		    ), 
		    array(
		        'label'=>  esc_html__('Disclaimer', 'rehub-framework'),
		        'desc'  => esc_html__('Optional. It works in [quick_offer] and [wpsm_top] shortcodes', 'rehub-framework'),
		        'id'    => 'rehub_offer_disclaimer',
		        'type'  => 'textbox'
		    ),    
		    array(
		        'label'=>  esc_html__('Offer old price', 'rehub-framework'),
		        'desc'  => esc_html__('Insert old price of offer or leave blank', 'rehub-framework'),
		        'id'    => 'rehub_offer_product_price_old',
		        'type'  => 'text'
		    ), 
		    array(
		        'label'=>  esc_html__('Offer sale price', 'rehub-framework'),
		        'desc'  => esc_html__('Insert sale price of offer (example, $55). Please, choose your price pattern in theme options - localizations', 'rehub-framework'),
		        'id'    => 'rehub_offer_product_price',
		        'type'  => 'text'
		    ),  
		    array(
		        'label'=>  esc_html__('Set coupon code', 'rehub-framework'),
		        'desc'  => esc_html__('Set coupon code or leave blank', 'rehub-framework'),
		        'id'    => 'rehub_offer_product_coupon',
		        'type'  => 'text'
		    ),            
			array(
			    'label' => esc_html__('Expiration Date', 'rehub-framework'),
			    'desc'  => esc_html__('Choose expiration date or leave blank', 'rehub-framework'),
			    'id'    => 'rehub_offer_coupon_date',
			    'type'  => 'date'
			),    
		    array(
		        'label'=> esc_html__('Mask coupon code?', 'rehub-framework'),
		        'desc'  => esc_html__('If this option is enabled, coupon code will be hidden.', 'rehub-framework'),
		        'id'    => 'rehub_offer_coupon_mask',
		        'type'  => 'checkbox'
		    ),
		    array(
		        'label'=> esc_html__('Offer is expired?', 'rehub-framework'),
		        'desc'  => esc_html__('This option depends on expiration date field, but you can also enable expiration if you have not expiration date', 'rehub-framework'),
		        'id'    => 're_post_expired',
		        'type'  => 'checkbox'
		    ),    
		    array(
		        'label'=> esc_html__('Button text', 'rehub-framework'),
		        'desc'  => esc_html__('Insert text on button or leave blank to use default text. Use short names (not more than 14 symbols)', 'rehub-framework'),
		        'id'    => 'rehub_offer_btn_text',
		        'type'  => 'text'
		    ),     
			array(
			    'label'  => esc_html__('Upload thumbnail', 'rehub-framework'),
			    'desc'  => esc_html__('Upload thumbnail of product or leave blank to use post thumbnail', 'rehub-framework'),
			    'id'    => 'rehub_offer_product_thumb',
			    'type'  => 'image'
			),
		    array(
		        'label'=> esc_html__('Brand logo url', 'rehub-framework'),
		        'desc'  => esc_html__('Fallback for brand logo (better to add brand logo in Affiliate store fields)', 'rehub-framework'),
		        'id'    => 'rehub_offer_logo_url',
		        'type'  => 'text'
		    ), 		    
		    array(
		        'label'=>  esc_html__('Discount Tag', 'rehub-framework'),
		        'desc'  => esc_html__('Will be visible in deal, coupon list instead featured image. It shows maximum 5 symbols. Example: 50% or $20', 'rehub-framework'),
		        'id'    => 'rehub_offer_discount',
		        'type'  => 'text'
		    ),       	
		    array(
		        'label'=> esc_html__('Shortcode for this offer section', 'rehub-framework'),
		        'id'    => 'rehub_offer_shortcode_generate',
		        'type'  => 'helper'
		    ),         
		));	
		if (defined('\ContentEgg\PLUGIN_PATH')){
		    $post_custom_meta_fields[] =  array(
		        'label'=> esc_html__('Synchronization with Content Egg', 'rehub-framework'),
		        'id'    => '_rh_post_offer_sync_ce',
		        'type'  => 'cesync'
		    );
		}
		return $post_custom_meta_fields;		
	}	

	public static function meta_for_products() {
		$woo_custom_meta_fields = apply_filters('rh_woo_custom_meta_fields', array(
		    array(
		        'label'=>  esc_html__('Set coupon code', 'rehub-framework'),
		        'desc'  => esc_html__('Set coupon code or leave blank', 'rehub-framework'),
		        'id'    => 'rehub_woo_coupon_code',
		        'type'  => 'text'
		    ),
			array(
			    'label' => esc_html__('Offer End Date', 'rehub-framework'),
			    'desc'  => esc_html__('Choose expiration date of product or leave blank', 'rehub-framework'),
			    'id'    => 'rehub_woo_coupon_date',
			    'type'  => 'date'
			),    
		    array(
		        'label'=> esc_html__('Mask coupon code?', 'rehub-framework'),
		        'desc'  => esc_html__('If this option is enabled, coupon code will be hidden.', 'rehub-framework'),
		        'id'    => 'rehub_woo_coupon_mask',
		        'type'  => 'checkbox'
		    ),    
		    array(
		        'label'=> esc_html__('Brand logo url', 'rehub-framework'),
		        'desc'  => esc_html__('Fallback for brand logo (better to add brand logo in Products - Brands fields)', 'rehub-framework'),
		        'id'    => 'rehub_woo_coupon_logo_url',
		        'type'  => 'text'
		    ),
			array(
		        'label'=> esc_html__('Additional coupon image url', 'rehub-framework'),
		        'desc'  => esc_html__('Used for printable coupon function. To enable it, you must have any coupon code above', 'rehub-framework'),
		        'id'    => 'rehub_woo_coupon_coupon_img_url',
		        'type'  => 'text'
			),		    
		        
		));
		return $woo_custom_meta_fields;		
	}

	public static function meta_for_brand_cat() {
		$rh_woostore_tax_meta = apply_filters('rhwoostore_tax_fields', array(
		    array(
		        'label'=>  esc_html__('Set Heading Title', 'rehub-framework'),
		        'id'    => 'brand_heading',
		        'type'  => 'text'
		    ),    
		    array(
		        'label'=>  esc_html__('Set Short description', 'rehub-framework'),
		        'desc'  => esc_html__('Will be in sidebar', 'rehub-framework'),
		        'id'    => 'brand_short_description',
		        'type'  => 'textarea'
		    ),    
		    array(
		        'label'=>  esc_html__('Set url of store', 'rehub-framework'),
		        'id'    => 'brand_url',
		        'type'  => 'url'
		    ),
		    array(
		        'label'=>  esc_html__('Set short notice (cashback notice)', 'rehub-framework'),
		        'id'    => 'cashback_notice',
		        'type'  => 'text'
		    ),    
		    array(
		        'label'=>  esc_html__('Set bottom description', 'rehub-framework'),
		        'desc'  => esc_html__('Will be in bottom of page', 'rehub-framework'),
		        'id'    => 'brand_second_description',
		        'type'  => 'textarea'
		    ),             
		    array(
		        'label'  => esc_html__('Upload logo', 'rehub-framework'),
		        'desc'  => esc_html__('Upload or choose image here for retailer logo or category header banner', 'rehub-framework'),
		        'id'    => 'brandimage',
		        'type'  => 'image'
		    ),      
		));
		return $rh_woostore_tax_meta;		
	}		

	/**
	 * Add Meta boxes.
	 */
	public function add_meta_boxes() {
		
		$def_p_types = rh_get_post_type_formeta();
		add_meta_box( 'rehub-post-images', esc_html__( "Post Thumbnails and video", "rehub-framework"  ), array( $this, 'gallery_output' ), $def_p_types, 'side', 'low' );
		add_meta_box( 'post_rehub_offers', esc_html__( "Post Offer", "rehub-framework"  ), array( $this, 'show_post_metabox' ), $def_p_types, 'normal', 'low' );
		if(function_exists('rh_review_inner_custom_box')){
			add_meta_box( 'rh_review_section', esc_html__( "Post User Review", "rehub-framework" ), 'rh_review_inner_custom_box', 'comment', 'normal' );
		}		
		
		if(class_exists('WooCommerce')){
			add_meta_box( 'rh-wc-product-video', esc_html__( "Product video", "rehub-framework" ), array($this, 'wc_video_output'), 'product', 'side', 'low' );
			add_meta_box( 'side_rh_woo', esc_html__( "Product Layout", "rehub-framework" ), array($this, 'wc_side_output'), 'product', 'side', 'high' );
			if(function_exists('rh_woo_cm_edit_pros_cons')){
				add_meta_box( 'rh_woo_pros_section_edit_comment', esc_html__( "Pros and Cons", "rehub-framework" ), 'rh_woo_cm_edit_pros_cons', 'comment', 'normal' );
			}			
		}	

		add_meta_box( 'rh-shortcode-elementor-box', esc_html__( "Shortcode", "rehub-framework" ), array($this, 'rhe_shortcode_box'), 'elementor_library', 'side', 'high' );
	}

	/**
	 * Show posts metabox
	 */	
	public function show_post_metabox( $post ) {
		$variablearray = $this->meta_for_posts();
		$this->show_meta_box_by_variable( $post, $variablearray );
	}

	/**
	 * Show metabox via variable array
	 */	
	public function show_meta_box_by_variable( $post, $variablearray ) {
	    echo '<table class="form-table">';    
	    foreach ($variablearray as $field) {
	        // get value of this field if it exists for this post
	        $meta = get_post_meta($post->ID, $field['id'], true);
	        // begin a table row with        
	        echo '<tr>
	                <th><label for="'.$field['id'].'">'.$field['label'].'</label></th>
	                <td>';
	                switch($field['type']) {
	                    // text
						case 'text':
						    echo '<input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" size="70" />
						        <br /><span class="description">'.$field['desc'].'</span>';
						break;
	                    // text
						case 'url':
						    echo '<input type="url" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" size="70" />
						        <br /><span class="description">'.$field['desc'].'</span>';
						break;						
						// checkbox
						case 'checkbox':
						    echo '<input type="checkbox" name="'.$field['id'].'" value ="1" id="'.$field['id'].'" ',$meta ? ' checked="checked"' : '','/>
						        <label for="'.$field['id'].'">'.$field['desc'].'</label>';
						break;
	                    case 'helper':
	                        esc_html_e('By default, only next Post layouts will show offerbox automatically: Compact, Button in corner, Big post offer block in top, Offer and review score. You can also add next shortcode to render offerbox:', 'rehub-framework');
	                        echo '<br><br>';                    
	                        echo '[quick_offer id='.$post->ID.']';
	                    break;                    
						// date
						case 'date':
							echo '<input type="text" class="rehubdatepicker" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" size="70" />
									<br /><span class="description">'.$field['desc'].'</span>';
						break;	
	                    case 'textbox':
	                        echo '<textarea cols=20 rows=2 class="short" style="width:100%" name="'.$field['id'].'" id="'.$field['id'].'">'.$meta.'</textarea>
	                            <span class="description">'.$field['desc'].'</span>';
	                    break;                    
	                    //Sync with CE
	                    case 'cesync':
	                        $cegg_field_array = REHub_Framework::get_option('save_meta_for_ce');
	                        $cegg_fields = array();
	                        if (!empty($cegg_field_array) && is_array($cegg_field_array)) {
	                            foreach ($cegg_field_array as $cegg_field) {
	                                if ($cegg_field == 'none' || $cegg_field == ''){ continue;}
	                                $cegg_field_value = \ContentEgg\application\components\ContentManager::getViewData($cegg_field, $post->ID);
	                                if (!empty ($cegg_field_value) && is_array($cegg_field_value)) {
	                                    $cegg_fields += $cegg_field_value;
	                                }       
	                            }
	                            echo '<select name="'.$field['id'].'" id="'.$field['id'].'">'; 
	                            echo '<option value="lowest" '.selected('lowest', $meta).'>Sync with lowest price offer</option>';                           
	                            if (!empty($cegg_fields) && is_array($cegg_fields)) {
	                                foreach ($cegg_fields as $cegg_field_key => $cegg_field_value) {
	                                    $currency_code = (!empty($cegg_field_value['currencyCode'])) ? $cegg_field_value['currencyCode'] : '';                                
	                                    $offer_price = (!empty($cegg_field_value['price'])) ? \ContentEgg\application\helpers\TemplateHelper::formatPriceCurrency($cegg_field_value['price'], $currency_code) : '';
	                                    $domain = (!empty($cegg_field_value['domain'])) ? $cegg_field_value['domain'] : '';
	                                    $title = (!empty($cegg_field_value['title'])) ? $cegg_field_value['title'] : '';
	                                    echo '<option value="'.$cegg_field_key.'" '.selected($cegg_field_key, $meta).'>'.wp_trim_words($title, 10, '...' ).' - '.$offer_price.$currency_code.' - '.$domain.'</option>';                                                                        
	                                }
	                            }
	                            echo '<option value="none" '.selected('none', $meta).'>Disable synchronization for this post</option>';
	                            echo '</select>';

	                        }
	                    break;                    
						// image
						case 'image':
							$image = get_template_directory_uri().'/images/default/noimage_100_70.png';
							echo '<div class="meta_box_image"><span class="meta_box_default_image" style="display:none">' . $image . '</span>';
							if ( $meta ) {
								$image = $meta;
							}				
							echo	
								'<input name="' . esc_attr( $field['id'] ) . '" type="text" size="70" class="meta_box_upload_image" value="' . esc_url( $meta ) . '" />									
								<a href="#" class="meta_box_upload_image_button button" rel="' . get_the_ID() . '">'.__('Choose Image', 'rehub-framework').'</a>
								<small>&nbsp;<a href="#" class="meta_box_clear_image_button button">X</a></small>
								<br /><br />
								<img src="' . esc_attr( $image ) . '" class="meta_box_preview_image" alt="image" style="max-width: 200px" /></div>
								<br clear="all" />' . $field['desc'];
						break;														
	                } //end switch
	        echo '</td></tr>';
	    } // end foreach
	    echo '</table>'; // end table
	}

	/**
	 * Show metabox for woocommerce external products
	 */	
	public function show_rehub_woo_meta_box_inner() {
		global $post;
	    $woo_custom_meta_fields = $this->meta_for_products();
    	// Begin the field table and loop
	    echo '<div class="options_group show_if_external">';
	    foreach ($woo_custom_meta_fields as $field) {
	        // get value of this field if it exists for this post
	        $meta = get_post_meta($post->ID, $field['id'], true);
	        // begin a table row with
	        echo '<p class="form-field rh_woo_meta_'.$field['id'].'">
	                <th><label for="'.$field['id'].'">'.$field['label'].'</label></th>
	                <td>';
	                switch($field['type']) {
	                    // text
						case 'text':
						    echo '<input class="short" type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" size="70" />
						        <span class="description">'.$field['desc'].'</span>';
						break;
	                    // url
						case 'url':
						    echo '<input class="short" type="url" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" size="70" />
						        <span class="description">'.$field['desc'].'</span>';
						break;						
						case 'textbox':
						    echo '<textarea cols=20 rows=2 class="short" name="'.$field['id'].'" id="'.$field['id'].'">'.$meta.'</textarea>
						        <span class="description">'.$field['desc'].'</span>';
						break;					
						// checkbox
						case 'checkbox':
						    echo '<input type="checkbox" name="'.$field['id'].'" id="'.$field['id'].'" ',$meta ? ' checked="checked"' : '','/>
						        <span class="description">'.$field['desc'].'</span>';
						break;
						// date
						case 'date':
							echo '<input class="short rehubdatepicker" type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" size="70" />
									<span class="description">'.$field['desc'].'</span>';
						break;															
	                } //end switch
	        echo '</p>';
	    } // end foreach
	    echo '</div>'; // end table       
	}	

	/**
	 * Check if we're saving, the trigger an action based on the post type.
	 */
	public function save_meta_boxes( $post_id, $post ) {
		// $post_id is required
		if ( empty( $post_id ) || empty( $post ) || self::$saved_meta_boxes ) {
			return;
		}

		$posttype = $post->post_type;

		// Dont' save meta boxes for revisions or autosaves
		if ( (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || is_int(wp_is_post_revision($post_id))  ) {
			return $post_id;
		}

		// Check the post being saved == the $post_id to prevent triggering this call for other save_post events
		if ( empty( $_POST['post_ID'] ) || $_POST['post_ID'] != $post_id ) {
			return $post_id;
		}

		// Check the nonce
		if ( empty( $_POST['rehub_post_meta_nonce'] ) || !wp_verify_nonce( $_POST['rehub_post_meta_nonce'], 'rehub_post_meta_save' ) ) {
			return $post_id;
		}		

		// Check user has permission to edit
	    if ( 'page' == $posttype ) {
	        if (!current_user_can('edit_page', $post_id)) return $post_id;
	    } elseif (!current_user_can('edit_post', $post_id)) {
	        return $post_id;
	    }

		
		// Check the post type
		$def_p_types = rh_get_post_type_formeta();

		if (in_array($posttype, $def_p_types)){

			//Saving gallery
			if( !empty($_POST['rh_post_image_gallery']) && !is_array($_POST['rh_post_image_gallery'])){
				$attachment_ids = sanitize_text_field( $_POST['rh_post_image_gallery']);
				$attachment_ids = explode(",", $attachment_ids);
				$attachment_ids = array_filter($attachment_ids);
				$attachment_ids = implode(',', $attachment_ids);
				update_post_meta( $post_id, 'rh_post_image_gallery', $attachment_ids );
			}elseif(isset($_POST['rh_post_image_gallery'])){
				delete_post_meta( $post_id, 'rh_post_image_gallery' );
			}	

		    // loop through fields and save the data
		    $post_custom_meta_fields = $this->meta_for_posts();
		    $post_custom_meta_fields[] =  array(
		        'id' => 'rh_post_image_videos', //we add here video field
		    );		    
		    foreach ($post_custom_meta_fields as $field) {
		        $old = get_post_meta($post_id, $field['id'], true);
		        if (isset ($_POST[$field['id']])) {
		            if($field['id'] == 'rehub_offer_disclaimer'){
		                $new = wp_kses_post($_POST[$field['id']]);
		            }elseif($field['id'] == 'rehub_offer_product_url'){
		                $new = wp_sanitize_redirect($_POST[$field['id']]);
		                $new = filter_var($new, FILTER_SANITIZE_URL);
		            }
		            else{
		                $new = esc_html($_POST[$field['id']]);
		            }
		            
		        }
		        else {
		           $new =''; 
		        }
		        if ($new && $new != $old) {
		            update_post_meta($post_id, $field['id'], $new);
		            if($field['id'] == 're_post_expired'){ // Update Expiration Taxonomy
		            	wp_set_object_terms($post_id, 'yes', 'offerexpiration', false );
		            }		            
		        } elseif ('' == $new && $old) {
		            delete_post_meta($post_id, $field['id'], $old);
		            if($field['id'] == 're_post_expired'){ // Update Expiration Taxonomy
		            	wp_set_object_terms($post_id, NULL, 'offerexpiration', false );
		            }
		        }
		    } // end foreach

		    // Update and clean price value field
		    $priceold = get_post_meta($post_id, 'rehub_main_product_price', true);
		    $newprice = get_post_meta($post_id, 'rehub_offer_product_price', true);
		    $clean_price = rehub_price_clean($newprice); 

		    if ($clean_price && $clean_price != $priceold) { 
		        update_post_meta($post_id, 'rehub_main_product_price', $clean_price);
		    } elseif ('' == $newprice && $priceold) {
		        delete_post_meta($post_id, 'rehub_main_product_price');
		    }	

		    self::$saved_meta_boxes = true;	    			

		}elseif($posttype == 'product'){

		    // loop through fields and save the data
		    $woo_custom_meta_fields = $this->meta_for_products();
		    $woo_custom_meta_fields[] =  array(
		        'id' => 'rh_product_video', //we add here video field
		    );
		    if (isset ($_POST['rh_code_incart'])) {
				$woo_custom_meta_fields[] = array(
			        'id'    => 'rh_code_incart',
			    ); 
		    }

		    if (isset ($_POST['_rh_woo_product_layout'])) {
				$woo_custom_meta_fields[] = array(
			        'id'    => '_rh_woo_product_layout',
			    ); 
		    } 

		    if (isset ($_POST['is_editor_choice'])) {
		    	if($_POST['is_editor_choice'] == 'no'){
		    		delete_post_meta($post_id, 'is_editor_choice');
		    	}else{
					$woo_custom_meta_fields[] = array(
				        'id'    => 'is_editor_choice',
				    );
		    	} 
		    }          

		    if (isset ($_POST['rehub_woodeals_short'])) {
				$woo_custom_meta_fields[] = array(
			        'id'    => 'rehub_woodeals_short',
			    ); 
		    }  

		    if (isset ($_POST['woo_code_zone_footer'])) {
				$woo_custom_meta_fields[] = array(
			        'id'    => 'woo_code_zone_footer',
			    ); 
		    }		    		    
		    foreach ($woo_custom_meta_fields as $field) {
		        $old = get_post_meta($post_id, $field['id'], true);
		        if (isset ($_POST[$field['id']])) {
		            if($field['id'] == 'rh_code_incart' || $field['id'] == 'rehub_woodeals_short' || $field['id'] == 'woo_code_zone_footer' || $field['id'] == 'rh_product_video'){
		                $new = wp_kses_post($_POST[$field['id']]);
		            }else{
		                $new = sanitize_text_field($_POST[$field['id']]);
		            }
		            
		        }
		        else {
		           $new =''; 
		        }
		        if ($new && $new != $old) {
		            update_post_meta($post_id, $field['id'], $new);
		        } elseif ('' == $new && $old) {
		            delete_post_meta($post_id, $field['id'], $old);
		        }
		    } // end foreach
			self::$saved_meta_boxes = true;
		}		
	}

	/**
	 * Output the metabox.
	 *
	 * @param WP_Post $post
	 */
	public static function gallery_output( $post ) {
		?>
		<div id="rh_post_images_container">
			<ul class="rh_post_images">
				<?php
					if ( metadata_exists( 'post', $post->ID, 'rh_post_image_gallery' ) ) {
						$post_image_gallery = get_post_meta( $post->ID, 'rh_post_image_gallery', true );
					} else {
						// Backwards compat
						$attachment_ids = get_posts( 'post_parent=' . $post->ID . '&numberposts=-1&post_type=attachment&orderby=menu_order&order=ASC&post_mime_type=image&fields=ids&meta_value=0' );
						$attachment_ids = array_diff( $attachment_ids, array( get_post_thumbnail_id() ) );
						$post_image_gallery = implode( ',', $attachment_ids );
					}

					$attachments = array_filter( explode( ',', $post_image_gallery ) );
					$update_meta = false;
					$updated_gallery_ids = array();

					if ( ! empty( $attachments ) ) {
						foreach ( $attachments as $attachment_id ) {
							$attachment = wp_get_attachment_image( $attachment_id, 'thumbnail' );

							// if attachment is empty skip
							if ( empty( $attachment ) ) {
								$update_meta = true;
								continue;
							}

							echo '<li class="image" data-attachment_id="' . esc_attr( $attachment_id ) . '">
								' . $attachment . '
								<ul class="actions">
									<li><a href="#" class="delete tips" data-tip="' . esc_attr__( "Delete image", "rehub-framework" ) . '">' . esc_html__( "Delete", "rehub-framework" ) . '</a></li>
								</ul>
							</li>';

							// rebuild ids to be saved
							$updated_gallery_ids[] = $attachment_id;
						}

						// need to update post meta to set new gallery ids
						if ( $update_meta ) {
							update_post_meta( $post->ID, 'rh_post_image_gallery', implode( ',', $updated_gallery_ids ) );
						}
					}
				?>
			</ul>
			<input type="hidden" id="rh_post_image_gallery" name="rh_post_image_gallery" value="<?php echo esc_attr( $post_image_gallery ); ?>" />
			<?php wp_nonce_field( 'rehub_post_meta_save', 'rehub_post_meta_nonce' ); ?>
		</div>
		<p class="rh_add_post_images hide-if-no-js">
			<a href="#" data-choose="<?php esc_attr_e( "Add Images to Post Gallery", "rehub-framework" ); ?>" data-update="<?php esc_attr_e( "Add to gallery", "rehub-framework" ); ?>" data-delete="<?php esc_attr_e( "Delete image", "rehub-framework" ); ?>" data-text="<?php esc_attr_e( "Delete", "rehub-framework" ); ?>"><?php esc_html_e( "Add post gallery images", "rehub-framework" ); ?></a>
		</p>
		
		<p class="rh_add_post_images hide-if-no-js">
		<small><?php esc_html_e('Add video links, each link from new line. Youtube and vimeo are supported', 'rehub-framework');?></small>
			<textarea id="rh_post_image_videos" rows="3" name="rh_post_image_videos"><?php echo get_post_meta( $post->ID, 'rh_post_image_videos', true );?></textarea>
		</p> 
		<p class="rh_add_post_images hide-if-no-js"><small><?php esc_html_e('You can add gallery to post with shortcode [rh_get_post_thumbnails video=1 height=200 justify=1]. video=1 - include also video. Height is maximum height, justify=1 is parameter to show pretty justify gallery. [rh_get_post_videos] will show only videos in full size column', 'rehub-framework');?></small></p>
		<?php
	}
	
	/**
	 * Output the product metabox.
	 *
	 * @param WP_Post $post
	 */	
	public static function wc_video_output( $post ){
		$post_id = $post->ID;
		$rh_video_url = get_post_meta( $post_id, 'rh_product_video', true );
		wp_nonce_field( 'rehub_post_meta_save', 'rehub_post_meta_nonce' );
		?>
		<div id="product_video_container" class="hide-if-no-js">
			<textarea id="rh_product_video" rows="3" name="rh_product_video"><?php echo get_post_meta( $post_id, 'rh_product_video', true );?></textarea>
			<p class="howto"><?php esc_html_e('Add video links, each link from new line. Youtube and vimeo are supported', 'rehub-framework'); ?></p>
		</div>
		<?php
	}

	//Add Side panel product
	public function wc_side_output($post){
		$meta = get_post_meta($post->ID, '_rh_woo_product_layout', true);
		echo '<select name="_rh_woo_product_layout" id="_rh_woo_product_layout" style="width:100%; margin: 10px 0">'; 
			$product_layouts = apply_filters( 'rehub_product_layout_array', array(
				'global' => esc_html__('Global from Theme option - Shop', 'rehub-framework'),
				'default_sidebar' => esc_html__('Default with sidebar', 'rehub-framework'), 
				'default_no_sidebar' => esc_html__('Default full width', 'rehub-framework'),
				'full_width_extended' => esc_html__('Full width Extended', 'rehub-framework'),
				'full_width_advanced' => esc_html__('Full width Advanced', 'rehub-framework'),
				'sections_w_sidebar' => esc_html__('Sections with sidebar', 'rehub-framework'),
				'ce_woo_list' => esc_html__('Content Egg List', 'rehub-framework'),
				'ce_woo_sections' => esc_html__('Content Egg Auto Sections', 'rehub-framework'),
				'ce_woo_blocks' => esc_html__('Review with Blocks', 'rehub-framework'),			
				'vendor_woo_list' => esc_html__('Compare Prices with shortcode', 'rehub-framework'),
				'compare_woo_list' => esc_html__('Compare Prices by sku', 'rehub-framework'),			
				'full_photo_booking' => esc_html__('Full width Photo', 'rehub-framework'),
				'woo_compact' => esc_html__('Compact Style', 'rehub-framework'),
				'woo_directory' => esc_html__('Directory Style', 'rehub-framework'),							
				)
			);
			foreach ($product_layouts as $key => $value) {
		    	echo '<option value="'.$key.'" '.selected($key, $meta).'>'.$value.'</option>';			
			}
	    echo '</select>';
	    $badgemeta = get_post_meta($post->ID, 'is_editor_choice', true);
		$badges = apply_filters( 'rehub_product_badges', array(
			'no' => esc_html__('No Badge', 'rehub-framework'),		
			'1' => (REHub_Framework::get_option('badge_label_1') !='') ? REHub_Framework::get_option('badge_label_1') : esc_html__('Editor choice', 'rehub-framework'),
			'2' => (REHub_Framework::get_option('badge_label_2') !='') ? REHub_Framework::get_option('badge_label_2') : esc_html__('Best seller', 'rehub-framework'), 
			'3' => (REHub_Framework::get_option('badge_label_3') !='') ? REHub_Framework::get_option('badge_label_3') : esc_html__('Best value', 'rehub-framework'),
			'4' => (REHub_Framework::get_option('badge_label_4') !='') ? REHub_Framework::get_option('badge_label_4') : esc_html__('Best price', 'rehub-framework'),						
			)
		);
		foreach ($badges as $key => $value) {
			echo '<input type="radio" id="badge_'.$key.'" name="is_editor_choice" value="'.$key.'" '.checked($key, $badgemeta, false).'><label for="badge_'.$key.'">'.$value.'</label><br />';			
		}	
	    echo '<p>'.__('Check this if you want to show badge. You can customize them in theme option', 'rehub-framework').'</p>';	    
	}

	// A callback function to edit a custom field to our "deal brand" taxonomy 
	public function rhwoostore_tax_fields_edit($term, $taxonomy) {  
	    wp_nonce_field( basename( __FILE__ ), 'rhwoostore_nonce' );
	    $rh_woostore_tax_meta = $this->meta_for_brand_cat();
	    if($taxonomy != 'dealstore' && $taxonomy != 'store'){
	        unset($rh_woostore_tax_meta[0]);
	        unset($rh_woostore_tax_meta[1]);
	        unset($rh_woostore_tax_meta[2]);
	        unset($rh_woostore_tax_meta[3]);
	        if($taxonomy == 'category'){
	        	unset($rh_woostore_tax_meta[4]);
	        }
	        if($taxonomy != 'product_cat' && $taxonomy != 'category'){
	            unset($rh_woostore_tax_meta[5]);
	        }
	    }
	    if (function_exists('wp_enqueue_media')) {wp_enqueue_media();} 
	    ?>  
	    <?php $settingseditor = array(
	        'textarea_name' => 'description',
	        'textarea_rows' => 10,
	        'editor_class'  => 'i18n-multilingual',
	    );

	    ?>
	    <?php if($taxonomy != 'category'):?>
	    <tr class="form-field term-description-wrap">
	        <th scope="row"><label for="description"><?php esc_html_e( 'Description', 'rehub-framework' ); ?></label></th>
	        <td>
	            <?php

	            wp_editor( wp_specialchars_decode( $term->description, ENT_QUOTES ), 'html-tag-description', $settingseditor );

	            ?>
	            <p class="description"><?php esc_html_e( 'The description is not prominent by default; however, some themes may show it.', 'rehub-framework' ); ?></p>
	        </td>
	        <script>
	            // Remove the non-html field
	            jQuery('textarea#description').closest('.form-field').remove();
	        </script>
	    </tr> 
	    <?php endif;?>   
	    <?php foreach ($rh_woostore_tax_meta as $field) :?>
	        <?php $term_meta = get_term_meta( $term->term_id, $field['id'], true );?>
	        <tr class="form-field">  
	            <th scope="row" valign="top">  
	                <label for="<?php echo ''.$field['id'];?>"><?php echo ''.$field['label'];?></label>  
	            </th>
	            <td>    
	                <?php if ($field['type'] == 'text') :?>
	                    <input name="<?php echo ''.$field['id'];?>" id="<?php echo ''.$field['id'];?>" value="<?php echo ''.$term_meta ? $term_meta : ''; ?>" class="wpsm_tax_text_field" type="text" size="40" /><br /><br />
	                <?php elseif ($field['type'] == 'url') :?>
	                    <input name="<?php echo ''.$field['id'];?>" id="<?php echo ''.$field['id'];?>" value="<?php echo ''.$term_meta ? $term_meta : ''; ?>" class="wpsm_tax_url_field" type="url" size="40" /><br /><br />	                    
	                <?php elseif($field['type'] == 'textarea'):?>
	                    <?php
	                    $meta_content = $term_meta ? wpautop($term_meta) : '';
	                    wp_editor( $meta_content, $field['id'], array(
	                            'wpautop' =>  true,
	                            'media_buttons' => false,
	                            'textarea_name' => $field['id'],
	                            'textarea_rows' => 10,
	                            'teeny' =>  false
	                    ));
	                    ?>
	                    <p class="description"><?php echo ''.$field['desc'];?></p><br /><br />
	                <?php elseif($field['type'] == 'image'):?>
	                    <script>
	                    jQuery(document).ready(function ($) {
	                    //Image helper  
	                        var imageFrame;jQuery(".wpsm_tax_helper_upload_image_button").click(function(e){e.preventDefault();return $self=jQuery(e.target),$div=$self.closest("div.wpsm_tax_helper_image"),imageFrame?void imageFrame.open():(imageFrame=wp.media({title:"Choose Image",multiple:!1,library:{type:"image"},button:{text:"Use This Image"}}),imageFrame.on("select",function(){selection=imageFrame.state().get("selection"),selection&&selection.each(function(e){console.log(e);{var t=e.attributes.sizes.full.url;e.id}$div.find(".wpsm_tax_helper_preview_image").attr("src",t),$div.find(".wpsm_tax_helper_upload_image").val(t)})}),void imageFrame.open())}),jQuery(".wpsm_tax_helper_clear_image_button").click(function(){var e='';return jQuery(this).parent().siblings(".wpsm_tax_helper_upload_image").val(""),jQuery(this).parent().siblings(".wpsm_tax_helper_preview_image").attr("src",e),!1});
	                    });
	                    </script>                        
	                    <div class="wpsm_tax_helper_image">
	                        <img src="<?php echo ''.$term_meta ? esc_url($term_meta) : get_template_directory_uri().'/images/default/noimage_70_70.png'; ?>" class="wpsm_tax_helper_preview_image" alt="image" style="max-height: 80px" />
	                        <p class="description"><?php echo ''.$field['desc'];?></p>
	                        <input name="<?php echo ''.$field['id'];?>" id="<?php echo ''.$field['id'];?>" size="25" style="width:60%;" value="<?php echo ''.$term_meta ? esc_url($term_meta) : ''; ?>" class="wpsm_tax_helper_upload_image" />
	                        <a href="#" class="wpsm_tax_helper_upload_image_button button" rel=""><?php esc_html_e('Choose Image', 'rehub-framework'); ?></a>
	                        <small>&nbsp;<a href="#" class="wpsm_tax_helper_clear_image_button button">X</a></small>
	                        <br /><br />        
	                    </div>          
	                <?php endif;?>
	            </td>
	        </tr>
	    <?php endforeach;?>

	    <?php  
	} 

	// A callback function to add a custom field to our "deal brand" taxonomy  
	public function rhwoostore_tax_fields_new($taxonomy) {   
	    wp_nonce_field( basename( __FILE__ ), 'rhwoostore_nonce' );
	    if (function_exists('wp_enqueue_media')) {wp_enqueue_media();}
	    $rh_woostore_tax_meta = $this->meta_for_brand_cat();
	    if($taxonomy != 'dealstore' && $taxonomy != 'store'){
	        unset($rh_woostore_tax_meta[0]);
	        unset($rh_woostore_tax_meta[1]);
	        unset($rh_woostore_tax_meta[2]);
	        unset($rh_woostore_tax_meta[3]);
	        if($taxonomy == 'category'){
	        	unset($rh_woostore_tax_meta[4]);
	        }
	        if($taxonomy != 'product_cat' && $taxonomy != 'category'){
	            unset($rh_woostore_tax_meta[5]);
	        }
	    }
	    ?>  
	    <?php foreach ($rh_woostore_tax_meta as $field) :?>
	        <div class="form-field">    
	            <label for="<?php echo ''.$field['id'];?>"><?php echo ''.$field['label'];?></label>  
	            <?php if ($field['type'] == 'text') :?>
	                <input name="<?php echo ''.$field['id'];?>" id="<?php echo ''.$field['id'];?>" value="" class="wpsm_tax_text_field" /><br /><br />
	            <?php elseif ($field['type'] == 'url') :?>
	                <input name="<?php echo ''.$field['id'];?>" id="<?php echo ''.$field['id'];?>" value="" class="wpsm_tax_text_field" type="url" /><br /><br />	                
	            <?php elseif($field['type'] == 'textarea'):?>
	                <textarea name="<?php echo ''.$field['id'];?>" id="<?php echo ''.$field['id'];?>" class="wpsm_tax_textarea_field" rows="5" cols="40"></textarea><p class="description"><?php echo ''.$field['desc'];?></p><br /><br />
	            <?php elseif($field['type'] == 'image'):?>
	                <script>
	                jQuery(document).ready(function ($) {
	                //Image helper  
	                    var imageFrame;jQuery(".wpsm_tax_helper_upload_image_button").click(function(e){e.preventDefault();return $self=jQuery(e.target),$div=$self.closest("div.wpsm_tax_helper_image"),imageFrame?void imageFrame.open():(imageFrame=wp.media({title:"Choose Image",multiple:!1,library:{type:"image"},button:{text:"Use This Image"}}),imageFrame.on("select",function(){selection=imageFrame.state().get("selection"),selection&&selection.each(function(e){console.log(e);{var t=e.attributes.sizes.full.url;e.id}$div.find(".wpsm_tax_helper_preview_image").attr("src",t),$div.find(".wpsm_tax_helper_upload_image").val(t)})}),void imageFrame.open())}),jQuery(".wpsm_tax_helper_clear_image_button").click(function(){var e='';return jQuery(this).parent().siblings(".wpsm_tax_helper_upload_image").val(""),jQuery(this).parent().siblings(".wpsm_tax_helper_preview_image").attr("src",e),!1});
	                });
	                </script>                        
	                <div class="wpsm_tax_helper_image">
	                    <img src="<?php echo get_template_directory_uri().'/images/default/noimage_70_70.png';?>" class="wpsm_tax_helper_preview_image" alt="image" style="max-height: 80px" />
	                    <p class="description"><?php echo ''.$field['desc'];?></p>
	                    <input name="<?php echo ''.$field['id'];?>" id="<?php echo ''.$field['id'];?>" size="25" style="width:60%;" value="" class="wpsm_tax_helper_upload_image" />
	                    <a href="#" class="wpsm_tax_helper_upload_image_button button" rel=""><?php esc_html_e('Choose Image', 'rehub-framework'); ?></a>
	                    <small>&nbsp;<a href="#" class="wpsm_tax_helper_clear_image_button button">X</a></small>
	                    <br /><br />        
	                </div>          
	            <?php endif;?>
	        </div>
	    <?php endforeach;?>
	    <?php  
	}  

	// A callback function to save our extra taxonomy field(s)  
	public function rhwoostore_tax_fields_save( $term_id, $tt_id) { 
	    $rh_woostore_tax_meta = $this->meta_for_brand_cat();
	    if (!empty($_POST['rhwoostore_nonce'])){
	        $rhwoostore_nonce = $_POST['rhwoostore_nonce'];
	    }else{
	        return;
	    }
	    if ( ! wp_verify_nonce($rhwoostore_nonce, basename( __FILE__ ) ) || !current_user_can('manage_categories'))
	        return; 
	    // loop through fields and save the data
	    foreach ($rh_woostore_tax_meta as $field) {
	        $old = get_term_meta($term_id, $field['id'], true);
	        if (isset ($_POST[$field['id']])) {
	            if ($field['type'] == 'image'){
	                $new = esc_url($_POST[$field['id']]);
	            }
	            elseif($field['type'] == 'text'){
	                $new = sanitize_text_field($_POST[$field['id']]);
	            } 
	            elseif($field['type'] == 'url'){
	                $new = esc_url($_POST[$field['id']]);
	            }	                      
	            else{
	                $new = wp_kses_post($_POST[$field['id']]);
	            }  
	        }
	        else {
	           $new =''; 
	        }
	        if ($new && $new != $old) {
	            update_term_meta($term_id, $field['id'], $new);
	        } elseif ('' == $new && $old) {
	            delete_term_meta($term_id, $field['id'], $old);
	        }
	    } // end foreach  
	} 

	// A callback function for Templates Elementor
	function rhe_shortcode_box($post){
	    ?>
	    <h4 style="margin-bottom:5px;"><?php esc_html_e('Shortcode', 'rehub-framework');?></h4>
	    <input type='text' class='widefat' value='[RH_ELEMENTOR id="<?php echo $post->ID; ?>"]' readonly="">
	    <h4 style="margin-bottom:5px;"><?php esc_html_e('Shortcode with caching (24 hours)', 'rehub-framework');?></h4>
	    <input type='text' class='widefat' value='[RH_ELEMENTOR id="<?php echo $post->ID; ?>" cache=1 expire=24]' readonly="">
	    <h4 style="margin-bottom:5px;"><?php esc_html_e('Ajax loaded on Hover and trigger classes', 'rehub-framework');?></h4>
	    <input type='text'  style="margin-bottom:5px;" class='widefat' value='[RH_ELEMENTOR id="<?php echo $post->ID; ?>" ajax=1]' readonly="">
	    <input type='text' class='widefat' value='rh-el-onhover load-block-<?php echo $post->ID; ?>' readonly="">	    
	    <h4 style="margin-bottom:5px;"><?php esc_html_e('Php code', 'rehub-framework');?></h4>
	    <input type='text' class='widefat' value="&lt;?php echo do_shortcode('[RH_ELEMENTOR id=&quot;<?php echo $post->ID; ?>&quot;]'); ?&gt;" readonly="">
	    <?php
	}	

	// Init woocommerce taxonomy field
	public function rhwoostore_tax_fields() {  
		if(class_exists('Woocommerce')){
		    add_action( 'store_edit_form_fields', array( $this, 'rhwoostore_tax_fields_edit'), 10, 2 );  
		    add_action( 'store_add_form_fields', array( $this, 'rhwoostore_tax_fields_new'));
		    add_action( 'edited_store', array( $this, 'rhwoostore_tax_fields_save'), 10, 2 ); 
		    add_action( 'create_store', array( $this, 'rhwoostore_tax_fields_save'), 10, 2 );   
		    add_action( 'product_cat_edit_form_fields', array( $this, 'rhwoostore_tax_fields_edit'), 10, 2 );  
		    add_action( 'product_cat_add_form_fields', array( $this, 'rhwoostore_tax_fields_new'));
		    add_action( 'edited_product_cat', array( $this, 'rhwoostore_tax_fields_save'), 10, 2 ); 
		    add_action( 'create_product_cat', array( $this, 'rhwoostore_tax_fields_save'), 10, 2 );
	    }         
	}

	// Init Affiliate store taxonomy field
    function dealstore_tax_fields() {  
        add_action( 'dealstore_edit_form_fields', array( $this, 'rhwoostore_tax_fields_edit'), 10, 2 );  
        add_action( 'dealstore_add_form_fields', array( $this, 'rhwoostore_tax_fields_new'));
        add_action( 'edited_dealstore', array( $this, 'rhwoostore_tax_fields_save'), 10, 2 ); 
        add_action( 'create_dealstore', array( $this, 'rhwoostore_tax_fields_save'), 10, 2 );              
    }

	// Init Category taxonomy field
    function category_tax_fields() {  
        add_action( 'category_edit_form_fields', array( $this, 'rhwoostore_tax_fields_edit'), 10, 2 );  
        add_action( 'category_add_form_fields', array( $this, 'rhwoostore_tax_fields_new'));
        add_action( 'edited_category', array( $this, 'rhwoostore_tax_fields_save'), 10, 2 ); 
        add_action( 'create_category', array( $this, 'rhwoostore_tax_fields_save'), 10, 2 );              
    }    

    //Custom code area Tab
	public function rh_custom_code_data_tab($product_data_tabs){
	    $product_data_tabs['rh-custom-code-tab'] = array(
	        'label' => esc_html__( 'Custom code areas', 'woocommerce' ),
	        'target' => 'rh_custom_code_section',
	    );
	    return $product_data_tabs;
	} 

	//custom code area render fields
	public function rh_custom_code_data_fields() {
	    global $post;

	    ?> <div id = 'rh_custom_code_section'
	    class = 'panel woocommerce_options_panel' > <?php
	        ?> <div class = 'options_group' > <?php
			    woocommerce_wp_textarea_input( array( 'id' => 'rh_code_incart', 'class' => 'short', 'label' => esc_html__( 'Custom shortcode', 'rehub-framework' ), 'description' => esc_html__( 'Will be rendered near button', 'rehub-framework' )  ));
			    woocommerce_wp_textarea_input( array( 'id' => 'rehub_woodeals_short', 'class' => 'short', 'label' => esc_html__( 'Custom shortcode', 'rehub-framework' ), 'description' => esc_html__( 'Will be rendered before Content', 'rehub-framework' )  ));
			    woocommerce_wp_textarea_input( array( 'id' => 'woo_code_zone_footer', 'class' => 'short', 'label' => esc_html__( 'Custom shortcode', 'rehub-framework' ), 'description' => esc_html__( 'Will be rendered as Additional Section', 'rehub-framework' )  )); 
	        ?> </div>

	    </div><?php
	}	  				

}
new RH_Meta_Box_Post();