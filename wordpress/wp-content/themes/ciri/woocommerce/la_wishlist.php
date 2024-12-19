<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


$noLimit = false;
$per_page = 10;
if(!empty($args['limit'])){
	if($args['limit'] == 'nolimit'){
		$per_page = 1000;
		$noLimit = true;
	}
	else{
		$per_page = $args['limit'];
	}
}

if(!$noLimit && !function_exists('wc_print_notices')){
    return;
}

$lists = Ciri_WooCommerce_Wishlist::get_data();
$total = count($lists);

if($noLimit){
	$wishlist_page_id = ciri_get_theme_mod( 'wishlist_page', 0 );
    $wishlist_title = !empty($wishlist_page_id) ? get_the_title($wishlist_page_id) : esc_html__('Wishlist', 'ciri');
    echo sprintf('<div class="la_wishlist-top">%1$s<small>(%2$s)</small><div></div></div>', $wishlist_title, $total);
}

?>
<div id="la_wishlist_table_wrapper" class="lakit-wishlist-wrapper">
<?php
if(!$noLimit){
	wc_print_notices();
}

$current_page = max( 1, get_query_var( 'paged' ) );
$page_links = '';
$newlist = $lists;
if($total > 1){
    $pages = absint(ceil( $total / $per_page ));

    if( $current_page > $pages ) {
        $current_page = $pages;
    }
    $offset = ( $current_page - 1 ) * $per_page;

    if( $pages > 1 ){
        $page_links = paginate_links( array(
            'base' => esc_url( add_query_arg( array( 'paged' => '%#%' ), ciri_get_wishlist_url() ) ),
            'format' => '?paged=%#%',
            'current' => $current_page,
            'total' => $pages,
            'show_all' => true,
            'prev_text'    => esc_html_x('Prev', 'front-view', 'ciri'),
            'next_text'    => esc_html_x('Next', 'front-view', 'ciri'),
            'type'         => 'list'
        ) );
        $page_links = sprintf('<div class="la-pagination">%s</div>', $page_links);
    }
    $newlist = array_slice($lists, $offset, $per_page);
}

?>
<table class="la_wishlist_table shop_table shop_table_responsive woocommerce-cart-form__contents" cellspacing="0">
    <thead>
        <tr>
            <th class="product-remove">&nbsp;</th>
            <th class="product-thumbnail">&nbsp;</th>
            <th class="product-name"><?php esc_html_e( 'Product', 'ciri' ); ?></th>
            <th class="product-price"><?php esc_html_e( 'Price', 'ciri' ); ?></th>
            <th class="product-stock"><?php esc_html_e( 'Stock status', 'ciri' ); ?></th>
            <th class="product-action"></th>
        </tr>
    </thead>
    <tbody>
    <?php

    if($total > 0){

        $stockLabels = wc_get_product_stock_status_options();

        foreach($newlist as $product_id){

            global $product;

            $product_id = ciri_wpml_object_id ( $product_id, 'product', true );
            $_product = wc_get_product($product_id);
            $availability = $_product->get_availability();
            $stock_status = $availability['class'];
            if( $_product && $_product->exists() ){
                $product = $_product;

                $stock_html = '';
	            if($stock_status == 'out-of-stock'){
		            $stock_html = sprintf('<span class="stock out-of-stock">%1$s</span>', $stockLabels['outofstock']);
	            }
	            else{
		            $stock_html = sprintf('<span class="stock in-stock">%1$s</span>', $stockLabels['instock']);
	            }

                ?>
                <tr class="woocommerce-cart-form__cart-item cart_item">
                    <td class="product-remove">
                        <?php
                        // @codingStandardsIgnoreLine
                        echo sprintf(
                            '<a href="%s" class="remove la_remove_from_wishlist" aria-label="%s" data-product_id="%s" data-product_sku="%s">&times;</a>',
                            esc_url( add_query_arg( array(
                                'la_helpers_wishlist_remove' => $product_id
                            ) ) ),
                            esc_attr__( 'Remove this item', 'ciri' ),
                            esc_attr( $product_id ),
                            esc_attr( $_product->get_sku() )
                        );
                        ?>
                    </td>
                    <td class="product-thumbnail"><?php
                        echo sprintf( '<a href="%s">%s</a>', esc_url( $_product->get_permalink() ), $_product->get_image() );
                    ?></td>
                    <td class="product-name" data-title="<?php esc_attr_e( 'Product', 'ciri' ); ?>"><?php
                        echo sprintf( '<div class="wl-item--name"><a href="%s">%s</a></div>', esc_url( $_product->get_permalink() ), $_product->get_name() );
                        echo sprintf('<div class="wl-item--price">%1$s</div>', $_product->get_price_html());
                        echo sprintf('<div class="wl-item--stock">%1$s</div>', $stock_html);
                    ?></td>
                    <td class="product-price" data-title="<?php esc_attr_e( 'Price', 'ciri' ); ?>"><?php
                        echo sprintf('<div class="wl-item--price">%1$s</div>', $_product->get_price_html());
                    ?></td>
                    <td class="product-stock" data-title="<?php esc_attr_e( 'Stock status', 'ciri' ); ?>"><?php
                        echo sprintf('<div class="wl-item--stock">%1$s</div>', $stock_html);
                    ?></td>
                    <td class="product-action">
                        <!-- Add to cart button -->
                        <?php
                        if( isset( $stock_status ) && $stock_status != 'out-of-stock' ){
                            woocommerce_template_loop_add_to_cart();
                        }
                        ?>
                    </td>
                </tr>
                <?php
            }
        }

        if(!empty($page_links)){
            echo sprintf(
                '<tr class="pagination-row"><td colspan="4">%s</td></tr>',
                $page_links
            );
        }
        ?>
        <?php

    }
    else{
        ?>
        <tr class="not-found-product text-center"><td colspan="4"><?php esc_html_e('No products were added to the wishlist', 'ciri') ?></td></tr>
        <?php
    }

    ?>
    </tbody>
</table>
</div>
<?php
if($noLimit){
?>
    <div class="la_wishlist-bottom">
        <div class="la_wishlist-bottom_actions">
            <a class="lawl--page" href="<?php echo esc_url( ciri_get_wishlist_url() ); ?>"><?php echo esc_html__('Open wishlist page', 'ciri'); ?></a>
            <span class="lawl--continue"><?php echo esc_html__('Continue shopping', 'ciri'); ?></span>
        </div>
        <div class="la_wishlist-bottom_notice"></div>
    </div>
    <?php
}