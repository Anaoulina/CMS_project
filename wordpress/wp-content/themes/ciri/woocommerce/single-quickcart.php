<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="prod-qv-wrap product-quickcart wc-page-content single-product-article">
	<?php
	while ( have_posts() ) : the_post();
		/**
		 * woocommerce_before_single_product hook.
		 *
		 * @hooked wc_print_notices - 10
		 */
		do_action( 'woocommerce_before_single_product' );
		if ( post_password_required() ) {
			echo get_the_password_form();

			return;
		}

		?>

        <div id="product-<?php the_ID(); ?>" <?php wc_product_class(); ?>>

            <div class="product--inner">

                <div class="woocommerce-product-gallery-outer">
                    <?php
                    woocommerce_show_product_images();
                    ?>
                </div>

                <div class="summary entry-summary">
                    <?php

                    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_rating');
                    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);
                    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50);
                    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);
                    remove_all_actions('woocommerce_single_product_summary', 60);
                    remove_all_actions('woocommerce_after_add_to_cart_button', 50);
                    remove_all_actions('woocommerce_after_add_to_cart_button', 55);

                    add_action('woocommerce_after_add_to_cart_button', function (){
                        global $product;
                        echo sprintf('<a class="button button-view-detail" href="%2$s">%1$s</a>', __('View Detail', 'ciri'), $product->get_permalink());
                    }, -10);

                    /**
                     * Hook: woocommerce_single_product_summary.
                     *
                     * @hooked woocommerce_template_single_title - 5
                     * @hooked woocommerce_template_single_rating - 10
                     * @hooked woocommerce_template_single_price - 10
                     * @hooked woocommerce_template_single_excerpt - 20
                     * @hooked woocommerce_template_single_add_to_cart - 30
                     * @hooked woocommerce_template_single_meta - 40
                     * @hooked woocommerce_template_single_sharing - 50
                     * @hooked WC_Structured_Data::generate_product_data() - 60
                     */
                    do_action( 'woocommerce_single_product_summary' );
                    ?>
                </div>
            </div>

        </div>

		<?php do_action( 'woocommerce_after_single_product' ); ?>

	<?php endwhile; ?>
</div>
