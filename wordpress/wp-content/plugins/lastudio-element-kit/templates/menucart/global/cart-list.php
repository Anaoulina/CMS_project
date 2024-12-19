<?php
/**
 * Cart list template
 */

$close_button_html = $this->_get_icon( 'cart_list_close_icon', '<div class="lakit-cart__close-button lakit-blocks-icon">%s</div>' );
$heading_label = $this->_get_html( 'cart_list_label', '<div class="lakit-cart__list-title h4 theme-heading">%s</div>' );
if( str_contains( $heading_label, "{{cart_count}}" ) ){
    $cart_count = '';
	ob_start();
	include $this->_get_global_template( "cart-count" );
	$cart_count = ob_get_clean();
	$heading_label = str_replace("{{cart_count}}", $cart_count, $heading_label);
}

?>
<div class="lakit-cart__list">
	<?php echo $close_button_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<?php echo $heading_label; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    <div class="widget_shopping_cart_content"><?php
	    if( !lastudio_kit()->get_theme_support('elementor::cart-fragments') ){
            woocommerce_mini_cart();
        }
    ?></div>
</div>
