<?php
/**
 * Adds Product custom accessories
 */
 
if ( !defined( 'WPINC' ) ) die;

class RH_WC_Custom_Accessories {
	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		$this->init();
	}
	/**
	 * Init settings.
	 */
	public function init() {
		// Admin interface
		add_action( 'woocommerce_product_options_related', [ $this, 'add_custom_accessories_panel_data' ] );
		add_action( 'woocommerce_process_product_meta_simple', [ $this, 'save_custom_accessories_panel_data' ] );
		add_action( 'woocommerce_process_product_meta_variable', [ $this, 'save_custom_accessories_panel_data' ] );
		add_action( 'woocommerce_process_product_meta_grouped', [ $this, 'save_custom_accessories_panel_data' ] );
		add_action( 'woocommerce_process_product_meta_external', [ $this, 'save_custom_accessories_panel_data' ] );
		
        // Accessories Ajax Total Price Update
        add_action( 'wp_ajax_nopriv_accessory_checked_custom_price', [ $this, 'accessory_checked_custom_price' ] );
        add_action( 'wp_ajax_accessory_checked_custom_price', [ $this, 'accessory_checked_custom_price' ] );

        // Accessories Ajax Add to Cart for Variable Products
        add_action( 'wp_ajax_nopriv_custom_accessories_add_to_cart', [ $this, 'custom_accessories_add_to_cart' ] );
        add_action( 'wp_ajax_custom_accessories_add_to_cart', [ $this, 'custom_accessories_add_to_cart' ] );
		
		// Public interface
//		add_action( 'rh_woo_button_loop', [ $this, 'variable_button_loop' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_filter( 'woocommerce_product_tabs', [ $this, 'wpb_custom_accessories_tab_data' ] );
	}
	
	/*  */
	public function enqueue_scripts() {
		wp_register_style( 'accessories-public', RHWCT_URIPATH .'css/woo-custom-accessories.css', array(), RHWCT_VERSION, 'all' );
		wp_register_script( 'accessories_public', RHWCT_URIPATH .'js/woo-custom-accessories.js', array( 'jquery' ), RHWCT_VERSION, false );
	}
	
	/*  */
    public function add_custom_accessories_panel_data() {
        global $post;
		?>
		<div class="options_group">
			<p class="form-field">
				<label for="custom_accessory_ids"><?php esc_html_e( 'Accessories', 'rh-wctools' ); ?></label>
				<select class="wc-product-search" multiple="multiple" style="width: 50%;" id="custom_accessory_ids" name="custom_accessory_ids[]" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'rh-wctools' ); ?>" data-action="woocommerce_json_search_products_and_variations" data-exclude="<?php echo intval( $post->ID ); ?>">
					<?php
					$product_ids = array_filter( array_map( 'absint', (array) get_post_meta( $post->ID, '_custom_accessory_ids', true ) ) );
					
					foreach ( $product_ids as $product_id ) {
						$product = wc_get_product( $product_id );
						if ( is_object( $product ) ) {
							echo '<option value="' . esc_attr( $product_id ) . '"' . selected( true, true, false ) . '>' . htmlspecialchars( wp_kses_post( $product->get_formatted_name() ) ) . '</option>';
						}
					}
					?>
				</select>
				<?php echo wc_help_tip( __( 'Accessories are products which you recommend to be bought along with this product. Only simple products can be added as accessories.', 'rh-wctools' ) ); ?>
			</p>
		</div>
		<?php
    }
	
	/*  */
    public static function save_custom_accessories_panel_data( $post_id ) {
        $accessories = isset( $_POST['custom_accessory_ids'] ) ? array_map( 'intval', (array) $_POST['custom_accessory_ids'] ) : array();
		update_post_meta( $post_id, '_custom_accessory_ids', $accessories );
    }
	
	/*  */
    public function wpb_custom_accessories_tab_data( $tabs ){
        global $product;
		
		$priority = WC_Admin_Settings::get_option('rhwct_tab_product_order_accessory');
		$title = WC_Admin_Settings::get_option('rhwct_accessory_title');
		if(!$title) $title = esc_html__('Accessories', 'rh-wctools');
		$tancontent = get_post_meta($product->get_id(), '_custom_accessory_ids', true);
		
		if(empty($priority)) $priority = 10;

        if ( !empty($tancontent)) {
            $tabs['accessories_tab'] = array(
                'title' => $title,
                'priority' => $priority,
                'callback' => array( &$this, 'rhwoo_custom_accessories_tab_content' )
            );
        }
		
        return $tabs;
    }
	
	/*  */
    public function rhwoo_custom_accessories_tab_content() {
        require_once RHWCT_DIRPATH .'/public/custom_accessories_tab_content.php';
    }
	
	/*  */
    public function accessory_checked_custom_price(){
        global $woocommerce;
        $price = empty( $_POST['price'] ) ? 0 : $_POST['price'];

        if( $price ) {
            $price_html = wc_price( $price );
            echo wp_kses_post( $price_html );
        }

        die();
    }
	
	/*  */
    public function custom_accessories_add_to_cart() {
        $product_id = absint( $_POST['product_id'] );
        $quantity = empty( $_POST['quantity'] ) ? 1 : wc_stock_amount( $_POST['quantity'] );
        $variation_id = empty( $_POST['variation_id'] ) ? 0 : $_POST['variation_id'];
        $variation = empty( $_POST['variation'] ) ? 0 : $_POST['variation'];
        $product_status = get_post_status( $product_id );

        if ( WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation ) && 'publish' === $product_status ) {
            do_action( 'woocommerce_ajax_added_to_cart', $product_id );
			
            if ( get_option( 'woocommerce_cart_redirect_after_add' ) == 'yes' ) {
                wc_add_to_cart_message( $product_id );
            }

            // Return fragments
            WC_AJAX::get_refreshed_fragments();
        } else {
            // If there was an error adding to the cart, redirect to the product page to show any errors
            $data = array(
                'error'       => true,
                'product_url' => apply_filters( 'woocommerce_cart_redirect_after_error', get_permalink( $product_id ), $product_id )
            );
			
            wp_send_json( $data );
        }

        die();
    }

	/*  */
    public function custom_template_loop_categories() {
        global $product;
		$categories = wc_get_product_category_list( $product->get_id() );
        echo wp_kses_post( sprintf( '<span class="loop-product-categories">%s</span>', $categories ) );
    }

	/*  */
	public function custom_accessories_price(){
		global $product;
		
		if( $product->is_type( 'variable' ) && $product->has_child()) {
			$attributes = $product->get_attributes(); // GET ALL ATRIBUTES

			echo '<div class="wc-accessory-price">';
			remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20 );
			add_filter( 'woocommerce_is_sold_individually', 'wc_remove_all_quantity_fields', 10, 2 );
			
			if(!function_exists('wc_remove_all_quantity_fields')){
				function wc_remove_all_quantity_fields( $return, $product ) {
					return( true );
				}
			}
			
			woocommerce_variable_add_to_cart();
			echo '</div>';
		}
		else { ?> 
			<div class="price-add-to-cart"><p class="price fontnormal"><?php echo wp_kses_post( $product->get_price_html() ); ?></p></div>
		<?php }
	}
}