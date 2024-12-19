<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="prod-qv-wrap wc-page-content single-product-article">
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

                    do_action('woocommerce_next_prev_product');

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
