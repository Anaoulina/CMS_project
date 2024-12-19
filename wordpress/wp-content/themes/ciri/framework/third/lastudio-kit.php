<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

add_filter('lastudio-kit/branding/name', 'ciri_lakit_branding_name');
if(!function_exists('ciri_lakit_branding_name')){
    function ciri_lakit_branding_name( $name ){
        $name = esc_html__('Theme Options', 'ciri');
        return $name;
    }
}

add_filter('lastudio-kit/branding/logo', 'ciri_lakit_branding_logo');
if(!function_exists('ciri_lakit_branding_logo')){
    function ciri_lakit_branding_logo( $logo ){
        $logo = '';
        return $logo;
    }
}

add_filter('lastudio-kit/logo/attr/src', 'ciri_lakit_logo_attr_src');
if(!function_exists('ciri_lakit_logo_attr_src')){
    function ciri_lakit_logo_attr_src( $src ){
        if(!$src){
	        $src = ciri_get_theme_mod('logo_default', get_theme_file_uri('/assets/images/logo.svg'));
        }
        return $src;
    }
}

add_filter('lastudio-kit/logo/attr/src2x', 'ciri_lakit_logo_attr_src2x');
if(!function_exists('ciri_lakit_logo_attr_src2x')){
    function ciri_lakit_logo_attr_src2x( $src ){
        if(!$src){
	        $src = ciri_get_theme_mod('logo_transparency', '');
        }
        return $src;
    }
}

add_filter('lastudio-kit/logo/attr/width', 'ciri_lakit_logo_attr_width');
if(!function_exists('ciri_lakit_logo_attr_width')){
    function ciri_lakit_logo_attr_width( $value ){
        if(!$value){
            $value = 118;
        }
        return $value;
    }
}

add_filter('lastudio-kit/logo/attr/height', 'ciri_lakit_logo_attr_height');
if(!function_exists('ciri_lakit_logo_attr_height')){
    function ciri_lakit_logo_attr_height( $value ){
        if(!$value){
            $value = 23;
        }
        return $value;
    }
}

add_action('elementor/frontend/widget/before_render', 'ciri_lakit_add_class_into_sidebar_widget');
if(!function_exists('ciri_lakit_add_class_into_sidebar_widget')){
    function ciri_lakit_add_class_into_sidebar_widget( $widget ){
        if('sidebar' == $widget->get_name()){
            $widget->add_render_attribute('_wrapper', 'class' , 'widget-area');
        }
    }
}

add_filter('lastudio-kit/products/control/grid_style', 'ciri_lakit_add_product_grid_style');
if(!function_exists('ciri_lakit_add_product_grid_style')){
    function ciri_lakit_add_product_grid_style( $preset ){
        return $preset;
    }
}
add_filter('lastudio-kit/products/control/list_style', 'ciri_lakit_add_product_list_style');
if(!function_exists('ciri_lakit_add_product_list_style')){
    function ciri_lakit_add_product_list_style( $preset ){
        return $preset;
    }
}

add_filter('lastudio-kit/products/box_selector', 'ciri_lakit_product_change_box_selector');
if(!function_exists('ciri_lakit_product_change_box_selector')){
    function ciri_lakit_product_change_box_selector(){
        return '{{WRAPPER}} ul.products .product_item--inner';
    }
}

add_filter('lastudio-kit/posts/format-icon', 'ciri_lakit_change_postformat_icon', 10, 2);
if(!function_exists('ciri_lakit_change_postformat_icon')){
    function ciri_lakit_change_postformat_icon( $icon, $type ){
        return $icon;
    }
}

/**
 * Modify Divider - Weight control
 */
add_action('elementor/element/lakit-portfolio/section_settings/before_section_end', function( $element ){
	$element->add_control(
		'enable_portfolio_lightbox',
		[
			'label'     => esc_html__( 'Enable Lightbox', 'ciri' ),
			'type'      => \Elementor\Controls_Manager::SWITCHER,
			'label_on'  => esc_html__( 'Yes', 'ciri' ),
			'label_off' => esc_html__( 'No', 'ciri' ),
			'default'   => '',
			'return_value' => 'enable-pf-lightbox',
			'prefix_class' => '',
		]
	);
}, 10 );

if(!function_exists('ciri_elementor_register_custom_widgets')){
	function ciri_elementor_register_custom_widgets(){
		require_once get_theme_file_path('/framework/third/elementor/postformat-widget.php');
		\Elementor\Plugin::instance()->widgets_manager->register( new Ciri_Elementor_PostFormat_Content_Widget() );
	}
}

add_action( 'elementor/widgets/widgets_registered', 'ciri_elementor_register_custom_widgets' );

if(!function_exists('ciri_remove_unwanted_metaboxes')){
    function ciri_remove_unwanted_metaboxes( $type, $context, $post ){
        remove_meta_box('slider_revolution_metabox', $type, $context);
    }
}

add_action( 'do_meta_boxes', 'ciri_remove_unwanted_metaboxes', 10, 3 );

if(!function_exists('ciri_elementor_modify_widget_args')){
    function ciri_elementor_modify_widget_args( $default_args, $widget ){
        $new_args = [
            'after_widget'	=> '</div>',
            'before_title'	=> '<div class="widget-title"><span>',
            'after_title'	=> '</span></div>',
        ];
        $widget_cssclass = sprintf('widget widget_%1$s %1$s', $widget->get_widget_instance()->id_base);
        if( !empty($widget->get_widget_instance()->widget_cssclass) ){
            $widget_cssclass = 'widget ' . $widget->get_widget_instance()->widget_cssclass;
        }
        $new_args['before_widget'] = sprintf('<div class="%1$s lakit-wp--widget" data-id="%2$s">', $widget_cssclass, $widget->get_id());

        return array_merge($default_args, $new_args);
    }
}

add_filter('elementor/widgets/wordpress/widget_args', 'ciri_elementor_modify_widget_args', 20, 2);

add_filter('lastudio-kit/products/loop/wishlist-button/class', function( $cssClass ){
    global $product;
    if(Ciri_WooCommerce_Wishlist::is_product_in_wishlist($product->get_id())){
        $cssClass .= ' added';
    }
    return $cssClass;
});

add_filter('lastudio-kit/products/loop/compare-button/class', function( $cssClass ){
    global $product;
    if(Ciri_WooCommerce_Compare::is_product_in_compare($product->get_id())){
        $cssClass .= ' added';
    }
    return $cssClass;
});

add_filter('lastudio-kit/wishlist/settings', function ( $settings, $product, $product_id ){
    if(Ciri_WooCommerce_Wishlist::is_product_in_wishlist($product_id)){
        $settings['link'] = [
            'url' => ciri_get_wishlist_url(),
            'is_external' => '',
            'nofollow' => true,
            'custom_attributes' => 'class|added'
        ];
        $settings['text'] = esc_html_x('View Wishlist', 'front-view', 'ciri');
    }
    else{
        $settings['link'] = [
            'url' => get_permalink($product_id),
            'is_external' => '',
            'nofollow' => true,
        ];
    }
    return $settings;
}, 20, 3);

add_filter('lastudio-kit/compare/settings', function ( $settings, $product, $product_id ){
    if(Ciri_WooCommerce_Compare::is_product_in_compare($product_id)){
        $settings['link'] = [
            'url' => ciri_get_compare_url(),
            'is_external' => '',
            'nofollow' => true,
            'custom_attributes' => 'class|added'
        ];
        $settings['text'] = esc_html_x('Compare List', 'front-view', 'ciri');
    }
    else{
        $settings['link'] = [
            'url' => get_permalink($product_id),
            'is_external' => '',
            'nofollow' => true,
        ];
    }
    return $settings;
}, 20, 3);

add_action('lastudio-kit/testimonials/output/star_rating', function (){
?>
    <svg xmlns="http://www.w3.org/2000/svg" width="110" height="110" fill="none" viewBox="0 0 110 64" style="background: #fff;"><path fill="#C4B8A5" d="m49.426 0 4.107 5.333c-9.388 8.494-15.256 18.37-17.603 29.63 3.912 0 6.846 1.185 8.801 3.555 1.956 2.37 2.934 5.729 2.934 10.075 0 4.345-1.662 8-4.987 10.963C39.548 62.519 35.93 64 31.822 64c-3.912 0-6.943-1.086-9.095-3.26-2.151-2.37-3.227-5.431-3.227-9.184C19.5 33.976 29.475 16.79 49.426 0zm36.967 0L90.5 5.333c-9.975 9.087-15.843 18.963-17.603 29.63 3.716 0 6.552 1.284 8.508 3.852 2.151 2.37 3.227 5.728 3.227 10.074 0 4.148-1.662 7.704-4.987 10.667C76.515 62.519 72.897 64 68.789 64c-3.912 0-6.943-1.086-9.095-3.26-2.151-2.37-3.227-5.431-3.227-9.184 0-18.963 9.975-36.149 29.926-51.556z"/></svg>
    <?php
});

add_filter('pre_set_transient_wc_system_status_theme_info', function ($value, $transient){
	$value['has_outdated_templates'] = '';
	if( !empty($value['overrides']) ){
		$theme_name = wp_get_theme()->get_template() . '/';
		foreach ( $value['overrides'] as &$template ){
			if(str_starts_with($template['file'], $theme_name)){
				$template['version'] = $template['core_version'];
			}
		}
	}
	return $value;
}, 1000, 2);
add_filter('woocommerce_show_admin_notice', function ($show, $notice){
	if($notice === 'template_files'){
		$show = false;
	}
	return $show;
}, 1000, 2);