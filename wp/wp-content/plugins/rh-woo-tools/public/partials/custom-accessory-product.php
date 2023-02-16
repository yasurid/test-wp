<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// If product variable but not have any variation price not show
if( $product->is_type( 'variable' ) && ! $product->has_child()) {
	return;
}
// Ensure visibility + if not in stock
if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}
?>
<div <?php post_class('pt0 pb0 pr30 pl30'); ?>>
	<div class="product-outer ">
		<div class="product-inner">
			<div class="product-content">
				<?php $this->custom_template_loop_categories(); ?>
				<a href="<?php echo esc_url( $product->get_permalink() );?>" class="blackcolor">
					<div class="woocommerce-loop-product__title fontbold lineheight20 mb10"><?php echo wp_kses_post( $product->get_title() ); ?></div>
				</a>
			</div>
			<div class="product-thumbnail mb10">
				<a href="<?php echo esc_url( $product->get_permalink() ); ?>">
					<?php echo wp_kses_post( $product->get_image( 'shop_catalog' ) ); ?>
				</a>
			</div>
			<?php $this->custom_accessories_price(); ?>
		</div>
	</div>
</div>
