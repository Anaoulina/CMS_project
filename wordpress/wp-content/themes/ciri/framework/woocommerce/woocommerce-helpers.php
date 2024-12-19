<?php
/**
 * WooCommerce helper functions
 * This functions only load if WooCommerce is enabled because
 * they should be used within Woo loops only.
 *
 * @package Ciri WordPress theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! function_exists( 'woocommerce_template_loop_product_thumbnail' ) ) {

    /**
     * Get the product thumbnail for the loop.
     */
    function woocommerce_template_loop_product_thumbnail() {
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo '<figure>';
        echo woocommerce_get_product_thumbnail();
        echo '</figure>';
    }
}

if(!function_exists('ciri_modify_sale_flash')){
    function ciri_modify_sale_flash( $output ){
        return str_replace('class="onsale"', 'class="la-custom-badge onsale"', $output);
    }
}
add_filter('woocommerce_sale_flash', 'ciri_modify_sale_flash');

if(!function_exists('ciri_modify_product_list_preset')){
    function ciri_modify_product_list_preset( $preset ){
        $preset = array(
            '1' => esc_html__( 'Default', 'ciri' )
        );
        return $preset;
    }
}

add_filter('woocommerce_product_description_heading', '__return_empty_string');
add_filter('woocommerce_product_additional_information_heading', '__return_empty_string');

if(!function_exists('ciri_woo_get_product_per_page_array')){
    function ciri_woo_get_product_per_page_array(){
        $per_page_array = apply_filters('ciri/filter/get_product_per_page_array', ciri_get_theme_mod('wc_per_page_allow', ''));
        if(!empty($per_page_array)){
            $per_page_array = explode(',', $per_page_array);
            $per_page_array = array_map('trim', $per_page_array);
            $per_page_array = array_map('absint', $per_page_array);
            asort($per_page_array);
            return $per_page_array;
        }
        else{
            return array();
        }
    }
}

if(!function_exists('ciri_woo_get_product_per_row_array')){
    function ciri_woo_get_product_per_row_array(){
        $per_page_array = apply_filters('ciri/filter/get_product_per_row_array', ciri_get_theme_mod('wc_per_row_allow', ''));
        if(!empty($per_page_array)){
            $per_page_array = explode(',', $per_page_array);
            $per_page_array = array_map('trim', $per_page_array);
            $per_page_array = array_map('absint', $per_page_array);
            asort($per_page_array);
            return $per_page_array;
        }
        else{
            return array();
        }
    }
}

if(!function_exists('ciri_woo_get_product_per_page')){
    function ciri_woo_get_product_per_page(){
        return apply_filters('ciri/filter/get_product_per_page', ciri_get_theme_mod('wc_per_page_default', 12));
    }
}

if(!function_exists('ciri_get_base_shop_url')){
    function ciri_get_base_shop_url( ){
        if(!function_exists('WC')){
            return home_url('/');
        }

        if ( defined( 'SHOP_IS_ON_FRONT' ) ) {
            $link = home_url();
        }
        elseif ( is_shop() ) {
            $link = get_permalink( wc_get_page_id( 'shop' ) );
        }
        elseif( is_tax( get_object_taxonomies( 'product' ) ) ) {

            if( is_product_category() ) {
                $link = get_term_link( get_query_var( 'product_cat' ), 'product_cat' );
            }
            elseif ( is_product_tag() ) {
                $link = get_term_link( get_query_var( 'product_tag' ), 'product_tag' );
            }
            else{
                $queried_object = get_queried_object();
                $link = get_term_link( $queried_object->slug, $queried_object->taxonomy );
            }
        }
        elseif ( function_exists('dokan_is_store_page') && dokan_is_store_page() ){
            $current_url = add_query_arg(null, null, dokan_get_store_url(get_query_var('author')));
            $current_url = remove_query_arg(array('page', 'paged', 'mode_view', 'la_doing_ajax'), $current_url);
            $link = preg_replace('/\/page\/\d+/', '', $current_url);
            $tmp = explode('?', $link);
            if(isset($tmp[0])){
                $link = $tmp[0];
            }
        }
        else{
            $link = get_post_type_archive_link( 'product' );
        }
        return $link;
    }
}

if(!function_exists('ciri_wc_allow_translate_text_in_swatches')){

    function ciri_wc_allow_translate_text_in_swatches( $text ){
        return esc_html_x( 'Choose an option', 'front-view', 'ciri' );
    }

    add_filter('LaStudio/swatches/args/show_option_none', 'ciri_wc_allow_translate_text_in_swatches', 10, 1);
}


if(!function_exists('ciri_override_woothumbnail_size_name')){
    function ciri_override_woothumbnail_size_name( ) {
        return 'woocommerce_gallery_thumbnail';
    }
    add_filter('woocommerce_gallery_thumbnail_size', 'ciri_override_woothumbnail_size_name', 0);
}


if(!function_exists('ciri_override_woothumbnail_size')){
    function ciri_override_woothumbnail_size( $size ) {
        if(!function_exists('wc_get_theme_support')){
            return $size;
        }
        $size['width'] = absint( wc_get_theme_support( 'gallery_thumbnail_image_width', 600 ) );
        $cropping      = get_option( 'woocommerce_thumbnail_cropping', '1:1' );

        if ( 'uncropped' === $cropping ) {
            $size['height'] = '';
            $size['crop']   = 0;
        }
        elseif ( 'custom' === $cropping ) {
            $width          = max( 1, get_option( 'woocommerce_thumbnail_cropping_custom_width', '4' ) );
            $height         = max( 1, get_option( 'woocommerce_thumbnail_cropping_custom_height', '3' ) );
            $size['height'] = absint( round( ( $size['width'] / $width ) * $height ) );
            $size['crop']   = 1;
        }
        else {
            $cropping_split = explode( ':', $cropping );
            $width          = max( 1, current( $cropping_split ) );
            $height         = max( 1, end( $cropping_split ) );
            $size['height'] = absint( round( ( $size['width'] / $width ) * $height ) );
            $size['crop']   = 1;
        }

        return $size;
    }
    add_filter('woocommerce_get_image_size_gallery_thumbnail', 'ciri_override_woothumbnail_size');
}

if(!function_exists('ciri_override_woothumbnail_single')){
    function ciri_override_woothumbnail_single( $size ) {
        if(!function_exists('wc_get_theme_support')){
            return $size;
        }
        $size['width'] = absint( wc_get_theme_support( 'single_image_width', get_option( 'woocommerce_single_image_width', 600 ) ) );
        $cropping      = get_option( 'woocommerce_thumbnail_cropping', '1:1' );

        if ( 'uncropped' === $cropping ) {
            $size['height'] = '';
            $size['crop']   = 0;
        }
        elseif ( 'custom' === $cropping ) {
            $width          = max( 1, get_option( 'woocommerce_thumbnail_cropping_custom_width', '4' ) );
            $height         = max( 1, get_option( 'woocommerce_thumbnail_cropping_custom_height', '3' ) );
            $size['height'] = absint( round( ( $size['width'] / $width ) * $height ) );
            $size['crop']   = 1;
        }
        else {
            $cropping_split = explode( ':', $cropping );
            $width          = max( 1, current( $cropping_split ) );
            $height         = max( 1, end( $cropping_split ) );
            $size['height'] = absint( round( ( $size['width'] / $width ) * $height ) );
            $size['crop']   = 1;
        }

        return $size;
    }
    add_filter('woocommerce_get_image_size_single', 'ciri_override_woothumbnail_single', 0);
}



if ( !function_exists('ciri_modify_text_woocommerce_catalog_orderby') ){
    function ciri_modify_text_woocommerce_catalog_orderby( $data ) {
        global $lakit_enabled;
        if(empty($lakit_enabled)){
            return $data;
        }
        $data = array(
            'menu_order' => esc_html__( 'Sort by Default', 'ciri' ),
            'popularity' => esc_html__( 'Sort by Popularity', 'ciri' ),
            'rating'     => esc_html__( 'Sort by Rated', 'ciri' ),
            'date'       => esc_html__( 'Sort by Latest', 'ciri' ),
            'price'      => sprintf( wp_kses( __( 'Sort by Price: %s', 'ciri' ), array( 'i' => array( 'class' => array() ) ) ), '<i class="lastudioicon-arrow-up"></i>' ),
            'price-desc' => sprintf( wp_kses( __( 'Sort by Price: %s', 'ciri' ), array( 'i' => array( 'class' => array() ) ) ), '<i class="lastudioicon-arrow-down"></i>' ),
        );
        return $data;
    }

    add_filter('woocommerce_catalog_orderby', 'ciri_modify_text_woocommerce_catalog_orderby');
}

if(!function_exists('ciri_add_custom_badge_for_product')){
    function ciri_add_custom_badge_for_product(){
        global $product;

	    $availability = $product->get_availability();
	    if(!empty($availability['class']) && $availability['class'] == 'out-of-stock' && !empty($availability['availability'])){
		    printf('<span class="la-custom-badge badge-out-of-stock">%s</span>', esc_html($availability['availability']));
	    }

        $product_badges = get_post_meta($product->get_id(), '_la_product_badges', true);
        if(empty($product_badges)){
            return;
        }
        $_tmp_badges = array();
        foreach($product_badges as $badge){
            if(!empty($badge['text'])){
                $_tmp_badges[] = $badge;
            }
        }
        if(empty($_tmp_badges)){
            return;
        }
        foreach($_tmp_badges as $i => $badge){
            $attribute = array();
            if(!empty($badge['bg'])){
                $attribute[] = 'background-color:' . esc_attr($badge['bg']);
            }
            if(!empty($badge['color'])){
                $attribute[] = 'color:' . esc_attr($badge['color']);
            }
            $el_class = ($i%2==0) ? 'odd' : 'even';
            if(!empty($badge['el_class'])){
                $el_class .= ' ';
                $el_class .= $badge['el_class'];
            }

            echo sprintf(
                '<span class="la-custom-badge %1$s" %3$s><span>%2$s</span></span>',
                esc_attr($el_class),
                esc_html($badge['text']),
                (!empty($attribute) ? ciri_render_style_attribute(implode(';', $attribute)) : '')
            );
        }
    }
    add_action( 'woocommerce_before_shop_loop_item', 'ciri_add_custom_badge_for_product', 11 );
    add_action( 'woocommerce_before_single_product_summary', 'ciri_add_custom_badge_for_product', 9 );
    add_action( 'lastudio-kit/woocommerce/product-images/render', 'ciri_add_custom_badge_for_product', 9 );
}

add_action('woocommerce_before_shop_loop_item', 'woocommerce_show_product_loop_sale_flash', 11);
remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash');

if(!function_exists('ciri_add_custom_block_to_cart_page')){
    function ciri_add_custom_block_to_cart_page(){
        ?>
        <div class="lasf-extra-cart lasf-extra-cart--calc">
            <h2><?php esc_html_e('Estimate Shipping', 'ciri'); ?></h2>
            <p><?php esc_html_e('Enter your destination to get shipping', 'ciri'); ?></p>
            <div class="lasf-extra-cart-box"></div>
        </div>
        <div class="lasf-extra-cart lasf-extra-cart--coupon">
            <h2><?php esc_html_e('Discount code', 'ciri'); ?></h2>
            <p><?php esc_html_e('Enter your coupon if you have one', 'ciri'); ?></p>
            <div class="lasf-extra-cart-box"></div>
        </div>
        <?php
    }
    add_action('woocommerce_cart_collaterals', 'ciri_add_custom_block_to_cart_page', 5);
}


if(!function_exists('ciri_add_custom_heading_to_checkout_order_review')){
    function ciri_add_custom_heading_to_checkout_order_review(){
        ?><h3 id="order_review_heading_ref"><?php esc_html_e( 'Your order', 'ciri' ); ?></h3><?php
    }
}
add_action('woocommerce_checkout_order_review', 'ciri_add_custom_heading_to_checkout_order_review', 0);


if( !function_exists('ciri_calculator_free_shipping_thresholds')){
    function ciri_calculator_free_shipping_thresholds(){
        if( ! ciri_string_to_bool( ciri_get_theme_mod('freeshipping_thresholds') ) ){
            return;
        }

        if ( WC()->cart->is_empty() ) {
            return;
        }
        // Get Free Shipping Methods for Rest of the World Zone & populate array $min_amounts
        $default_zone = new WC_Shipping_Zone( 0 );

        $default_methods = $default_zone->get_shipping_methods();
        foreach ( $default_methods as $key => $value ) {
            if ( $value->id === "free_shipping" ) {
                if ( $value->min_amount > 0 ) {
                    $min_amounts[] = $value->min_amount;
                }
            }
        }
        // Get Free Shipping Methods for all other ZONES & populate array $min_amounts
        $delivery_zones = WC_Shipping_Zones::get_zones();
        foreach ( $delivery_zones as $key => $delivery_zone ) {
            foreach ( $delivery_zone['shipping_methods'] as $key => $value ) {
                if ( $value->id === "free_shipping" ) {
                    if ( $value->min_amount > 0 ) {
                        $min_amounts[] = $value->min_amount;
                    }
                }
            }
        }
        // Find lowest min_amount
        if ( isset( $min_amounts ) ) {
            if ( is_array( $min_amounts ) && $min_amounts ) {
                $min_amount = min( $min_amounts );
                // Get Cart Subtotal inc. Tax excl. Shipping
                $current = WC()->cart->subtotal;
                // If Subtotal < Min Amount Echo Notice
                // and add "Continue Shopping" button
                if ( $current > 0 ) {
                    $percent = round( ( $current / $min_amount ) * 100, 2 );
                    $percent >= 100 ? $percent = '100' : '';

                    $parse_class = 'lakit-goal-free-shipping';
                    if($percent < 100){
                        $parse_class .= ' required-notice';
                    }
                    else{
                        $parse_class .= ' applied';
                    }
                    if ( $percent < 40 ) {
                        $parse_class .= ' first-parse';
                    }
                    elseif ( $percent >= 40 && $percent < 80 ) {
                        $parse_class .= ' second-parse';
                    }
                    elseif ( $percent >= 80 && $percent < 100 ) {
                        $parse_class .= ' final-parse';
                    }

                    $html_allows = [
                        'strong' => [],
                        'b' => [],
                        'u' => [],
                        'span' => [],
                        'i' => [],
                        'em' => [],
                    ];

                    $text1 = ciri_get_theme_mod('thresholds_text1', wp_kses( __('Buy [amount] more to enjoy <strong>FREE Shipping</strong>', 'ciri'),  $html_allows ) );
                    $text2 = ciri_get_theme_mod('thresholds_text2', wp_kses( __('Congrats! You are eligible for <strong>FREE Shipping</strong>', 'ciri'),  $html_allows ) );
                    $icon = '<svg xmlns="http://www.w3.org/2000/svg" width="62" height="45" viewBox="0 0 62 45"><g fill="currentColor" fill-rule="evenodd"><path d="M21 38a2 2 0 1 1-4 0 2 2 0 0 1 4 0m29 0a2 2 0 1 1-4 0 2 2 0 0 1 4 0"></path><path d="M19 33.19A4.816 4.816 0 0 0 14.19 38 4.816 4.816 0 0 0 19 42.81 4.816 4.816 0 0 0 23.81 38 4.816 4.816 0 0 0 19 33.19M19 45c-3.86 0-7-3.14-7-7s3.14-7 7-7 7 3.14 7 7-3.14 7-7 7"></path><path d="M38 37H24.315v-2.145h11.544V2.145H2.14v32.71h11.544V37H0V0h38zm11-3.81A4.816 4.816 0 0 0 44.19 38 4.816 4.816 0 0 0 49 42.81 4.816 4.816 0 0 0 53.81 38 4.816 4.816 0 0 0 49 33.19M49 45c-3.86 0-7-3.14-7-7s3.14-7 7-7 7 3.14 7 7-3.14 7-7 7"></path><path d="M62 37h-7.607v-2.154h5.47V22.835l-8.578-12.681H38.137v24.692h5.465V37H36V8h16.415L62 22.17z"></path><path d="M42.147 19.932h10.792l-4.15-5.864h-6.642v5.864zM57 22H40V12h9.924L57 22z"></path></g></svg>';
                    $text1 = str_replace('[icon]', $icon, $text1);
                    $text2 = str_replace('[icon]', $icon, $text2);
                    $added_text='';
                    if ( $current < $min_amount ) {
                        $text1 = str_replace('[amount]', wc_price( $min_amount - $current ), $text1);
                        $added_text .= $text1;
                    } else {
                        $added_text = $text2;
                    }
                    $css_inline = 'width: ' . $percent . '%';
                    $html = '<div class="' . esc_attr( $parse_class ) . '">';
                    $html .= '<div class="label-free-shipping">'.$added_text.'</div>';
                    $html .= '<div class="la-loading-bar"><div class="load-percent"'. ciri_render_style_attribute($css_inline) .'></div></div>';
                    $html .= '</div>';
                    echo ent2ncr( $html );
                }
            }
        }
    }
    add_action( 'woocommerce_widget_shopping_cart_before_buttons', 'ciri_calculator_free_shipping_thresholds', 5 );
    add_action( 'woocommerce_before_cart_table', 'ciri_calculator_free_shipping_thresholds', 5 );
}

if( !function_exists('ciri_add_popup_into_mini_cart') ){
    function ciri_add_popup_into_mini_cart(){
        ?>
        <div class="cart-footer-actions">
            <?php if ( apply_filters( 'woocommerce_enable_order_notes_field', 'yes' === get_option( 'woocommerce_enable_order_comments', 'yes' ) ) ) : ?>
                <a href="#popup-cart-order-notes" class="la-popup" data-component_name="woo-cart-pp">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10"><path d="m9.5 2.5 3 3M1.5 10.5l3 3M11.5.5l3 3-10 10-4 1 1-4Z"/></g></svg>
                    <span><?php esc_html_e( 'Note', 'ciri' ); ?></span>
                </a>
            <?php endif; ?>
            <?php if ( 'yes' === get_option( 'woocommerce_enable_shipping_calc' ) ) : ?>
                <a href="#popup-cart-shipping-calculator" class="la-popup" data-component_name="woo-cart-pp">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15.313" height="16" viewBox="0 0 15.313 16"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="m.656 3.5 7 3 7-3M7.656 15.5v-9"/><path d="m.656 12.5 7 3 7-3v-9l-7-3-7 3Z"/></g></svg>
                    <span><?php esc_html_e( 'Shipping', 'ciri' ); ?></span>
                </a>
            <?php endif; ?>

            <?php if ( wc_coupons_enabled() ) : ?>
                <a href="#popup-cart-coupon" class="la-popup" data-component_name="woo-cart-pp">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="14" viewBox="0 0 16 14"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10"><path d="M5.5 4.5h5M5.5 9.5h5M13.5 7.5a2 2 0 0 1 2-2v-4a1 1 0 0 0-1-1h-13a1 1 0 0 0-1 1V5a2 2 0 0 1 0 4v3.5a1 1 0 0 0 1 1h13a1 1 0 0 0 1-1v-3a2 2 0 0 1-2-2Z"/></g></svg>
                    <span><?php esc_html_e( 'Coupon', 'ciri' ); ?></span>
                </a>
            <?php endif; ?>
        </div>
        <div class="cart-totals-table"></div>
        <?php
    }
    add_action('woocommerce_widget_shopping_cart_before_buttons', 'ciri_add_popup_into_mini_cart', 6);
}

add_action('wp_footer', function (){
	?>
    <div class="hidden">
        <div id="popup-cart-order-notes" class="lakit-popup-template"></div>
        <div id="popup-cart-shipping-calculator" class="lakit-popup-template"></div>
        <div id="popup-cart-coupon" class="lakit-popup-template"></div>
    </div>
	<?php
}, 100);

add_action('woocommerce_widget_shopping_cart_before_buttons', function (){
    echo '<div class="lakit-minicart--footer">';
}, -1000);
add_action('woocommerce_widget_shopping_cart_before_buttons', function (){
    echo '</div>';
}, 1000);

if(!function_exists('ciri_wc_add_qty_control_plus')){
    function ciri_wc_add_qty_control_plus(){
        echo '<span class="qty-plus"><i class="lastudioicon-i-add-2"></i></span>';
    }
}

if(!function_exists('ciri_wc_add_qty_control_minus')){
    function ciri_wc_add_qty_control_minus(){
        echo '<span class="qty-minus"><i class="lastudioicon-i-delete-2"></i></span>';
    }
}

add_action('woocommerce_after_quantity_input_field', 'ciri_wc_add_qty_control_plus');
add_action('woocommerce_before_quantity_input_field', 'ciri_wc_add_qty_control_minus');

if(!function_exists('ciri_wc_product_bulk_edit_start')){
    function ciri_wc_product_bulk_edit_start(){
        if(!class_exists('LASF', false)){
            return;
        }
        echo '<div class="lasf-bulk-group lasf-show-all"><div class="lasf-section lasf-onload">';
        LASF::field(array(
            'id'        => 'enable_custom_badge',
            'type'      => 'button_set',
            'default'   => 'nochange',
            'title'     => esc_html_x('Enable Custom Badges', 'admin-view', 'ciri'),
            'options'   => array(
                'nochange'          => esc_html_x('No Change', 'admin-view', 'ciri'),
                'addnew'            => esc_html_x('Override', 'admin-view', 'ciri'),
                'removeall'         => esc_html_x('Remove Existing Data', 'admin-view', 'ciri')
            )
        ), '', 'la_custom_badge');
        LASF::field(array(
            'id'                => 'product_badges',
            'type'              => 'group',
            'title'             => esc_html_x('Custom Badges', 'admin-view', 'ciri'),
            'button_title'      => esc_html_x('Add Badge','admin-view', 'ciri'),
            'max'               => 3,
            'dependency'        => array('enable_custom_badge', '==', 'addnew'),
            'fields'            => array(
                array(
                    'id'            => 'text',
                    'type'          => 'text',
                    'default'       => 'New',
                    'title'         => esc_html_x('Badge Text', 'admin-view', 'ciri')
                ),
                array(
                    'id'            => 'bg',
                    'type'          => 'color',
                    'default'       => '',
                    'title'         => esc_html_x('Custom Badge Background Color', 'admin-view', 'ciri')
                ),
                array(
                    'id'            => 'color',
                    'type'          => 'color',
                    'default'       => '',
                    'title'         => esc_html_x('Custom Badge Text Color', 'admin-view', 'ciri')
                ),
                array(
                    'id'            => 'el_class',
                    'type'          => 'text',
                    'default'       => '',
                    'title'         => esc_html_x('Extra CSS class for badge', 'admin-view', 'ciri')
                )
            )
        ), '', 'la_custom_badge');
        echo '</div></div>';
?>
        <?php
    }
}
add_action( 'woocommerce_product_bulk_edit_start', 'ciri_wc_product_bulk_edit_start' );

if(!function_exists('ciri_wc_product_bulk_edit_save')){
    function ciri_wc_product_bulk_edit_save( $product ){
        $product_id = $product->get_id();
        if ( isset( $_REQUEST['la_custom_badge'], $_REQUEST['la_custom_badge']['enable_custom_badge'] ) ) {
	        $old_data = get_post_meta($product->get_id(), '_la_product_badges', true);
            $enable = $_REQUEST['la_custom_badge']['enable_custom_badge'];
            if( 'removeall' == $enable ) {
	            $old_data = [];
                update_post_meta( $product_id, '_la_product_badges', $old_data );
            }
            elseif( 'addnew' == $enable && !empty($_REQUEST['la_custom_badge']['product_badges'])) {
                $product_badges = $_REQUEST['la_custom_badge']['product_badges'];
                update_post_meta( $product_id, '_la_product_badges', $old_data );
            }
        }
    }
}
add_action( 'woocommerce_product_bulk_edit_save', 'ciri_wc_product_bulk_edit_save' );

if(!function_exists('ciri_wc_disable_redirect_single_search_result')){
    function ciri_wc_disable_redirect_single_search_result( $value ){
        if(isset($_GET['la_doing_ajax'])){
            $value = false;
        }
        return $value;
    }
}
add_filter('woocommerce_redirect_single_search_result', 'ciri_wc_disable_redirect_single_search_result');

if(!function_exists('ciri_wc_render_variation_templates')){
    function ciri_wc_render_variation_templates(){
        wc_get_template('single-product/add-to-cart/variation.php');
    }
}
add_action('wp_footer', 'ciri_wc_render_variation_templates');

if(!function_exists('ciri_wc_add_register_link_to_login_frm')){
    function ciri_wc_add_register_link_to_login_frm(){
        if( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ){
            echo sprintf('<p class="wcr_resigter_link"><span>%1$s</span><a href="%3$s">%2$s</a></p>', esc_html__('Don’t have account ?', 'ciri'), esc_html__('Sign up now', 'ciri'), wc_get_page_permalink('myaccount'));
        }
    }
}

add_action('woocommerce_before_customer_login_form', function (){
    if( !wc_string_to_bool(get_option( 'woocommerce_enable_myaccount_registration' )) ){
        echo '<div class="u-columns col2-set col1-set" id="customer_login"><div class="u-column1 col-1">';
    }
}, 1000);

add_action('woocommerce_after_customer_login_form', function (){
    if( !wc_string_to_bool(get_option( 'woocommerce_enable_myaccount_registration' )) ){
        echo '</div></div>';
    }
}, -1000);

remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10 );
remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
remove_action( 'woocommerce_before_checkout_form_cart_notices', 'woocommerce_output_all_notices', 10 );

add_action('woocommerce_before_template_part', function ( $template_name ){
    if( $template_name == 'checkout/form-login.php' || $template_name == 'checkout/form-coupon.php' ){
        echo '<div class="woo-toggle-wrapper">';
    }
    elseif ( $template_name == 'cart/cart-empty.php' || $template_name == 'cart/cart.php' ) {
        echo '<div class="lakitwc-cart-wrapper woocommerce">';
    }
}, 10);

add_action('woocommerce_after_template_part', function ( $template_name ){
    if( $template_name == 'checkout/form-login.php' || $template_name == 'checkout/form-coupon.php' ){
        echo '</div>';
    }
    elseif ( $template_name == 'cart/cart-empty.php' || $template_name == 'cart/cart.php' ) {
        echo '</div>';
    }
}, 10);

add_action('woocommerce_before_checkout_form', function (){
    echo '<div class="woo-toggles-wrapper">';
}, 8);
add_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 8 );
add_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 8 );
add_action('woocommerce_before_checkout_form', function (){
    echo '</div>';
}, 8);


add_filter('woocommerce_form_field_args', function ($args, $key, $value){
    if( $key == 'order_comments' ){
        $args['input_class'] = ['order-node-textarea'];
        $args['default'] = WC()->session->get( 'lakit_order_notes', '' );
    }
    return $args;
}, 10, 3);

if(!function_exists('ciri_wc_render_order_note_textarea')){
    function ciri_wc_render_order_note_textarea(){
        $notes = WC()->session->get( 'lakit_order_notes', '' );
        ?>
        <textarea class="order-node-textarea" name="order_comments" id="cart-order-notes" placeholder="<?php esc_attr_e( 'Notes about your order, e.g. special notes for delivery.', 'ciri' ); ?>"><?php echo esc_html($notes); ?></textarea>
        <?php
    }
}

add_action('lastudio-theme/ajax/register_actions', function ($ajax_manager){
    $ajax_manager->register_ajax_action('save_order_node', function ($request = []){
        $order_notes = isset( $request['order_notes'] ) ? sanitize_textarea_field( $request['order_notes'] ) : '';
        $return_data = [];
        WC()->session->set( 'lakit_order_notes', $order_notes );
        wc_add_notice( __( 'Your order notes saved.', 'ciri' ) );
        $fragments = array();

        ob_start();
        wc_print_notices();
        $notices_html = ob_get_clean();

        ob_start();
        echo '<div class="woocommerce-notices-wrapper">' . $notices_html . '</div>';
        $fragments['.woocommerce-notices-wrapper'] = ob_get_clean();

        ob_start();
        echo '<div class="lakit-pp-cart--messages">' . $notices_html . '</div>';
        $fragments['.lakit-pp-cart--messages'] = ob_get_clean();

        ob_start();
        ciri_wc_render_order_note_textarea();
        $fragments['.order-node-textarea'] = ob_get_clean();
        $return_data['fragments'] = $fragments;
        return $return_data;
    });

    $ajax_manager->register_ajax_action('apply_coupon', function ($request = []){
        $coupon_code = isset( $request['coupon_code'] ) ? $request['coupon_code'] : '';
        if(!empty($coupon_code)){
            WC()->cart->add_discount( wc_format_coupon_code( wp_unslash( $coupon_code ) ) );
        }
        else{
            wc_add_notice( WC_Coupon::get_generic_coupon_error( WC_Coupon::E_WC_COUPON_PLEASE_ENTER ), 'error' );
        }
        $return_data = [];
        $fragments = array();

        ob_start();
        wc_print_notices();
        $notices_html = ob_get_clean();

        ob_start();
        echo '<div class="woocommerce-notices-wrapper">' . $notices_html . '</div>';
        $fragments['.woocommerce-notices-wrapper'] = ob_get_clean();

        ob_start();
        echo '<div class="lakit-pp-cart--messages">' . $notices_html . '</div>';
        $fragments['.lakit-pp-cart--messages'] = ob_get_clean();

        ob_start();
        wc_get_template( 'cart/cart-totals-table.php' );
        $content = ob_get_clean();
        $content = str_replace(['id="shipping_method_', 'for="shipping_method_'], ['id="pp_shipping_method_', 'for="pp_shipping_method_'], $content);
        $fragments['.cart-totals-table'] = $content;

        $return_data['fragments'] = $fragments;
        return $return_data;
    });

    $ajax_manager->register_ajax_action('update_cart_item', function ( $request = [] ){
        $fragments = [];
        $needUpdate = false;
        $is_cart = $request['is_cart'] ?? false;
        if(isset($request['key']) && isset($request['quantity'])){
            $cart_item_key = sanitize_text_field( $request['key'] );
            $quantity = (int) sanitize_text_field( $request['quantity'] );
            $cart_item     = WC()->cart->get_cart_item( $cart_item_key );
            if($cart_item){
                WC()->cart->set_quantity( $cart_item_key, $quantity, true );
                $needUpdate = true;
            }
        }
        if($needUpdate){
            if($is_cart){
                ob_start();
                if ( WC()->cart->is_empty() ) {
                    wc_get_template( 'cart/cart-empty.php' );
                }
                else{
                    wc_get_template( 'cart/cart.php' );
                }
                $fragments['.lakitwc-cart-wrapper'] = ob_get_clean();
            }
        }
        $return_data['fragments'] = apply_filters('woocommerce_add_to_cart_fragments', $fragments);
        return $return_data;
    });
} );

if(!function_exists('ciri_wc_ajax_calculate_shipping')){
    function ciri_wc_ajax_calculate_shipping(){
        // Constants.
        wc_maybe_define_constant( 'WOOCOMMERCE_CART', true );

        WC_Shortcode_Cart::calculate_shipping();

        WC()->cart->calculate_totals();

        $fragments = array();

        ob_start();
        wc_print_notices();
        $notices_html = ob_get_clean();

        ob_start();
        echo '<div class="woocommerce-notices-wrapper">' . $notices_html . '</div>';
        $fragments['.woocommerce-notices-wrapper'] = ob_get_clean();

        ob_start();
        echo '<div class="lakit-pp-cart--messages">' . $notices_html . '</div>';
        $fragments['.lakit-pp-cart--messages'] = ob_get_clean();

        ob_start();
        wc_get_template( 'cart/cart-totals-table.php' );
        $content = ob_get_clean();
        $content = str_replace(['id="shipping_method_', 'for="shipping_method_'], ['id="pp_shipping_method_', 'for="pp_shipping_method_'], $content);
        $fragments['.cart-totals-table'] = $content;

        wp_send_json_success( [
            'fragments' => $fragments,
        ] );
    }
}

add_action( 'wp_ajax_ciri_calculate_shipping', 'ciri_wc_ajax_calculate_shipping' );
add_action( 'wp_ajax_nopriv_ciri_calculate_shipping', 'ciri_wc_ajax_calculate_shipping' );

if(!function_exists('ciri_wc_ajax_update_shipping_method')){
    function ciri_wc_ajax_update_shipping_method(){
        check_ajax_referer( 'lastudio_theme_ajax', 'security' );
        wc_maybe_define_constant( 'WOOCOMMERCE_CART', true );
        $chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );
        $posted_shipping_methods = isset( $_POST['shipping_method'] ) ? wc_clean( wp_unslash( $_POST['shipping_method'] ) ) : array();
        if ( is_array( $posted_shipping_methods ) ) {
            foreach ( $posted_shipping_methods as $i => $value ) {
                $chosen_shipping_methods[ $i ] = $value;
            }
        }
        WC()->session->set( 'chosen_shipping_methods', $chosen_shipping_methods );
        WC()->cart->calculate_totals();
        $fragments = array();
        ob_start();
        wc_get_template( 'cart/cart-totals-table.php' );
        $content = ob_get_clean();
        $content = str_replace(['id="shipping_method_', 'for="shipping_method_'], ['id="pp_shipping_method_', 'for="pp_shipping_method_'], $content);
        $fragments['.cart-totals-table'] = $content;
        wp_send_json_success( [
            'fragments' => $fragments,
        ] );
    }
}

add_action( 'wp_ajax_ciri_update_shipping', 'ciri_wc_ajax_update_shipping_method' );
add_action( 'wp_ajax_nopriv_ciri_update_shipping', 'ciri_wc_ajax_update_shipping_method' );

add_filter('woocommerce_cart_item_quantity', function ( $product_quantity, $cart_item_key, $cart_item ){
    return str_replace('<input', '<input data-cart_item_key="'.$cart_item_key.'"', $product_quantity);
}, 10, 3);