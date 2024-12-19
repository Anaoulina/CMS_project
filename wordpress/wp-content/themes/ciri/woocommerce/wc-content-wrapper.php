<?php
/**
 * After Container template.
 *
 * @package Astrids WordPress theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
$ex_class = '';
$ex_attr = '';
if(is_product()){
    $page_title = single_post_title('', false);
    $ex_class = ' single-product-article';
}
else{
    $page_title = woocommerce_page_title(false);
}

if(is_search()){
    $ex_class = ' lakit-products';
    $ex_attr = ' data-widget_current_query="yes"';
}

?>

<main <?php post_class('site-main'); ?> role="main">
    <?php if (apply_filters('ciri/filter/enable_page_title', true) && !empty($page_title)) : ?>
        <header class="page-header page-header--default">
            <div class="container page-header-inner">
                <?php
                echo sprintf('<h1 class="entry-title">%1$s</h1>', esc_html($page_title));
                ?>
            </div>
        </header>
    <?php endif; ?>

    <div id="site-content-wrap" class="container">

        <?php get_sidebar(); ?>

        <div class="site-content--default">

            <div class="page-content wc-page-content<?php echo esc_attr($ex_class); ?>"<?php echo sprintf('%s', $ex_attr); ?>>