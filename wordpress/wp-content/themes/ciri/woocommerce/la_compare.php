<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if(!function_exists('wc_print_notices')){
    return;
}

?>
<div id="la_compare_table_wrapper">
    <?php

    wc_print_notices();

    $lists = Ciri_WooCommerce_Compare::get_data();

    $default_attr = Ciri_WooCommerce_Compare::get_default_attributes();
    $tax_attr = Ciri_WooCommerce_Compare::get_taxonomies();
    $all_attr = array_merge($default_attr, $tax_attr);

    $attribute_allow = [];
    $s = ciri_get_theme_mod('compare_attribute');
    $settings = [];
    if(!empty($s)){
        $settings = explode(',', $s);
        foreach ($settings as $setting){
            if(isset($all_attr[$setting])){
	            $attribute_allow[$setting] = $all_attr[$setting];
            }
        }
    }

    if(empty($attribute_allow)){
        $attribute_allow = $default_attr;
    }
    global $product;

    $terms = array();

    add_filter('ciri/filter/enable_image_lazyload', '__return_false');

        foreach ($lists as $product_id) {
            $product_id = ciri_wpml_object_id($product_id, 'product', true);
            $term = array();
            $_product = wc_get_product($product_id);
            $term['id'] = $product_id;
            $term['sku'] = ($_product->get_sku() ? $_product->get_sku() : esc_html__('N/A', 'ciri'));
            $term['title'] = $_product->get_title();
            $term['image'] = $_product->get_image();
            $term['rating'] = sprintf('<div class="star-rating">%s</div>', wc_get_star_rating_html( $_product->get_average_rating() ));
            $term['price'] = sprintf('<div class="cp-item--price">%1$s</div>', $_product->get_price_html());
            $term['link'] = $_product->get_permalink();
            $term['description'] = wp_trim_words(wp_strip_all_tags($_product->get_short_description()), 10);
            $term['availability'] = $_product->get_availability();
            $term['dimensions'] = wc_format_dimensions($_product->get_dimensions(false));
            $term['weight'] = wc_format_weight($_product->get_weight());
            $term['stock'] = $_product->is_in_stock();

            if($attributes = $_product->get_attributes()){
                foreach ($attributes as $key => $attribute) {
                    if(!in_array($key,$attribute_allow)){
                        continue;
                    }
                    $values = array();
                    if($attribute->is_taxonomy()){
                        $attribute_taxonomy = $attribute->get_taxonomy_object();
                        $attribute_values = wc_get_product_terms( $product_id, $attribute->get_name(), array( 'fields' => 'all' ) );
                        foreach ( $attribute_values as $attribute_value ) {
                            $values[] = esc_html( $attribute_value->name );
                        }

                    }
                    $term[$key] = !empty($values) ? join(', ', $values) : '';
                }
            }

            $terms[] = $term;
        }

        $total_product = count($terms);
        ?>
        <table class="la-compare-table la-compare-table-items has-<?php echo esc_attr($total_product); ?> show-vtb">
            <tbody>
            <?php

            $label_list = array();

            foreach( $attribute_allow as $lk => $lb ){

                if(in_array($lk, array('image','title','add-to-cart'))){
                    $label_list['info'] = esc_html_x('Product Info', 'front-view', 'ciri');
                }
                else{
                    if(isset($all_attr[$lk])){
                        $label_list[$lk] = $all_attr[$lk];
                    }
                }
            }

            foreach ($label_list as $k => $v) {
                echo '<tr class="compare-tr compare-tr-'. esc_attr($k) .'">';
                echo sprintf('<th>%s</th>', $v);
                foreach ($terms as $item) {
                    if ($k == 'info') {
                        echo '<td>';
                            echo sprintf(
                                '<div class="remove"><a href="%1$s" class="la_remove_from_compare" aria-label="%2$s" data-product_id="%3$s"><i class="lastudioicon-e-remove"></i>%2$s</a></div>',
                                esc_url(add_query_arg(array(
                                    'la_helpers_compare_remove' => $item['id']
                                ))),
                                esc_html__('Remove', 'ciri'),
                                esc_attr($item['id'])
                            );
                            echo sprintf(
                                '<a href="%1$s" title="%3$s"><div class="image-wrap">%2$s</div><h4>%3$s</h4></a>',
                                esc_url($item['link']),
                                $item['image'],
                                $item['title']
                            );
                            echo '<div class="add_to_cart_wrap product-action">';
                            $product = wc_get_product($item['id']);
                            woocommerce_template_loop_add_to_cart();
                            echo '</div>';
                        echo '</td>';
                    }
                    elseif( in_array($k, array('rating', 'price', 'description', 'dimensions', 'weight', 'sku')) ) {
                        echo sprintf( '<td>%s</td>', $item[$k] );
                    }
                    elseif ($k == 'stock') {
                        $class_stock = '';
                        $text_stock = '';
                        if ($item['availability'] && !empty($item['availability']['availability'])) {
                            $product2 = wc_get_product($item['id']);
                            $availability_html = empty($item['availability']['availability']) ? '' : '<p class="stock ' . esc_attr($item['availability']['class']) . '">' . esc_html($item['availability']['availability']) . '</p>';
                            $text_stock = apply_filters('woocommerce_stock_html', $availability_html, $item['availability']['availability'], $product2);
                            $class_stock = esc_attr($item['availability']['class']);
                        }
                        else {
                            if ($item['stock']) {
                                $text_stock = esc_html__('In stock', 'ciri');
                                $class_stock = 'in-stock';
                            } else {
                                $text_stock = esc_html__('Out of stock', 'ciri');
                                $class_stock = 'out-of-stock';
                            }
                            $text_stock = '<p class="stock ' . $class_stock . '">' . $text_stock . '</p>';
                        }
                        echo sprintf( '<td>%s</td>', $text_stock );
                    }
                    else{
                        echo sprintf( '<td>%s</td>', (isset($item[$k]) ? $item[$k] : '') );
                    }
                }

                if($total_product < 5){

                    $remain = 5 - $total_product;
                    for ($n = 0; $n < $remain; $n++){
                        echo '<td class="td-placeholder"></td>';
                    }
                }

                echo '</tr>';
            }
            ?>
            </tbody>
        </table>
        <?php

    ?>
</div>

