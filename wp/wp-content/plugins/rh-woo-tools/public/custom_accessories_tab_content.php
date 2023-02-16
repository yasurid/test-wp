<?php
global $product, $post;

wc_set_loop_prop( 'columns', 3 );

$accessories = get_post_meta( $post->ID, '_custom_accessory_ids', true );

if(empty($accessories)){
	$accessories = 0;
}

if ( $accessories === 0 ) {
	echo __( 'No Accessories Found', 'rh-wctools' );
	return;
}

$meta_query = WC()->query->get_meta_query();
$accessories = array_diff($accessories, array($post->ID));

$args = array(
	'post_type' => 'product',
	'ignore_sticky_posts' => 1,
	'no_found_rows' => 1,
	'posts_per_page' => -1,
	'orderby' => 'post__in',
	'post__in' => $accessories,
	'meta_query' => $meta_query
);

unset( $args['meta_query'] );

$products = new WP_Query( $args );
$add_to_cart_checkbox = '';
$total_price = 0;
$count = 0;

if ( $products->have_posts() ) : ?>
	<div class="accessories">
		<?php 
		$title = WC_Admin_Settings::get_option('rhwct_accessory_title');
		if(!$title) $title = esc_html__('Accessories', 'rh-wctools');
		echo '<div class="rh-woo-section-title"><h2 class="rh-heading-icon">'.esc_attr($title).'</span></h2></div>';?>
		<div class="custom-wc-message">
			<?php // AJAX output var woo_accessories ?>
		</div>
		<div class="accessories-row">
			<div class="accessories-column-left">
			
				<div class="col_wrap_three columns-3 products rh-flex-eq-height mt0">
				
				<?php include RHWCT_DIRPATH .'/public/partials/custom-accessory-product.php'; ?>
				<?php
					$count++;
					$price_html = '';
					$display_price = wc_get_price_to_display( $product );
					
					if ( $price_html = $product->get_price_html() ) {
						$price_html = '<span class="accessory-price">' . wc_price( $display_price ) . $product->get_price_suffix() . '</span>';
					}
					$total_price += $display_price;
					
					$add_to_cart_checkbox = '<div class="checkbox accessory-checkbox"><label><input checked disabled type="checkbox" class="product-check" data-price="'. $display_price .'" data-product-id="'. $product->get_id() .'" data-product-type="'. $product->get_type() .'" /> <strong>'. esc_html__( 'This product: ', 'rh-wctools' ) .'</strong><span class="product-title">'. get_the_title() .'</span> - '. $price_html .'</label></div>';
				?>
				<?php wc_set_loop_prop( 'loop', 1 ); ?>
				
				<?php while ( $products->have_posts() ) : $products->the_post(); ?>
					<?php global $product, $post; ?>
					<?php include RHWCT_DIRPATH .'/public/partials/custom-accessory-product.php'; ?>
					<?php 
						$price_html = '';
						$display_price = wc_get_price_to_display( $product );
						
						if ( $price_html = $product->get_price_html() ) {
							$price_html = '<span class="accessory-price">' . wc_price( $display_price ) . $product->get_price_suffix() . '</span>';
						}
						
						$total_price += $display_price;
						$prefix = '';
						
						if($display_price != 0 || $display_price != '' ){
							$count++;
							$add_to_cart_checkbox .= '<div class="checkbox accessory-checkbox"><label><input checked type="checkbox" class="product-check" data-price="'. $display_price .'" data-product-id="'. $product->get_id() .'" data-product-type="'. $product->get_type() .'" /> <span class="product-title">'. $prefix . get_the_title() .'</span> - ' . $price_html . '</label></div>';
						}
					?>
				<?php endwhile; ?>
				
				</div>
				
				<div class="check-products mb25">
					<?php echo $add_to_cart_checkbox; ?>
				</div>
			</div>
		
			<div class="accessories-column-right">
				<div class="total-price">
					<?php
						$total_price_html = '<span class="total-price-html blockstyle font120 redbrightcolor">' . wc_price( $total_price ) . $product->get_price_suffix() . '</span>';
						$total_products_html = '<span class="total-products font90">' . $count . '</span>';
						$total_price = sprintf( __( '%s for %s item(s)', 'rh-wctools' ), $total_price_html, $total_products_html );
						echo wp_kses_post( $total_price );
					?>
				</div>
				<div class="accessories-add-all-to-cart">
					<button type="button" class="button add-all-to-cart"><?php echo esc_html__( 'Add all to cart', 'rh-wctools' ); ?></button>
				</div>
			</div>
			<?php
				wp_enqueue_style( 'accessories-public' );
				wp_enqueue_script( 'accessories_public' );
				wp_localize_script( 'accessories_public', 'woo_accessories', array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'loader_icon' => get_template_directory_uri() . '/images/ajax-loader.gif',
					'success' => sprintf( '<div class="woocommerce-message">%s <a class="button wc-forward" href="%s">%s</a></div>', esc_html__( 'Products was successfully added to your cart.', 'rh-wctools' ), wc_get_cart_url(), esc_html__( 'View Cart', 'rh-wctools' ) ),
					'empty' => sprintf( '<div class="woocommerce-error">%s</div>', esc_html__( 'No Products selected.', 'rh-wctools' ) ),
					'no_variation'	=> sprintf( '<div class="woocommerce-error">%s</div>', esc_html__( 'Product Variation does not selected.', 'rh-wctools' ) ),
					'not_available'	=> sprintf( '<div class="woocommerce-error">%s</div>', esc_html__( 'Sorry, this product is unavailable.', 'rh-wctools' ) ),
				));
			?>
		</div>
	</div>
<?php endif;
wp_reset_postdata();