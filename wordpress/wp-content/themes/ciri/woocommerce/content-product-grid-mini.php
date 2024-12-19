<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 2.6.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product;

// Ensure visibility
if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}
$class = array('product_item', 'grid-item', 'product');


?>
<li <?php wc_product_class( $class, get_the_ID() ); ?>>
	<div class="product_item--inner">
		<div class="product_item--thumbnail">
			<div class="product_item--thumbnail-holder">
				<?php
				do_action( 'woocommerce_before_shop_loop_item_title' );
				?>
			</div>
		</div>
		<div class="product_item--info">
			<h3 class="product_item--title"><a href="<?php the_permalink();?>"><?php the_title();?></a></h3>
			<?php
				woocommerce_template_loop_price();
				woocommerce_template_loop_rating();
			?>
		</div>
	</div>
</li>