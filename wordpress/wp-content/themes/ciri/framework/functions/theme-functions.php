<?php
/**
 * This file includes helper functions used throughout the theme.
 *
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if(!function_exists('ciri_add_meta_into_head_tag')){
    function ciri_add_meta_into_head_tag(){
        do_action('ciri/action/head');
    }
}

/**
 * Add classes to the body tag
 *
 * @since 1.0.0
 */
if (!function_exists('ciri_body_classes')) {
    function ciri_body_classes($classes) {
        global $lakit_enabled;

        $classes[] = is_rtl() ? 'rtl' : 'ltr';
        $classes[] = 'ciri-body';
        $classes[] = 'lastudio-ciri';

        $sidebar = apply_filters('ciri/filter/sidebar_primary_name', 'sidebar');

        if(!is_active_sidebar($sidebar) || is_page_template(['templates/no-sidebar.php', 'templates/fullwidth.php'])){
            $classes[] = 'site-no-sidebar';
        }
        elseif ( is_active_sidebar($sidebar) ){
	        $classes[] = 'site-has-sidebar';
        }

        if (is_singular('page')) {
            global $post;
            if (strpos($post->post_content, 'la_wishlist') !== false) {
                $classes[] = 'woocommerce-page';
                $classes[] = 'woocommerce-page-wishlist';
            }
            if (strpos($post->post_content, 'la_compare') !== false) {
                $classes[] = 'woocommerce-page';
                $classes[] = 'woocommerce-compare';
            }
        }

        $classes[] = 'body-loading';
	    if( ciri_string_to_bool( ciri_get_theme_mod('page_preloader') ) ){
            $classes[] = 'site-loading';
            $classes[] = 'active_page_loading';
        }

        $tmp = join('|', $classes);
        preg_match('/elementor-page-(\d+)/i', $tmp, $matches);
        if(empty($matches[1])){
            $lakit_enabled = false;
            $classes[] = 'wp-default-theme';
        }
        else{
            $lakit_enabled = true;
        }

		if(is_404() && empty($matches[1])){
			$classes[] = 'lakitdoc-enable-header-transparency';
		}

        // Return classes
        return $classes;
    }
}

/**
 * Add page loader icon
 *
 * @since 1.0.0
 */
if(!function_exists('ciri_add_pageloader_icon')){
    function ciri_add_pageloader_icon(){
        if( ciri_string_to_bool( ciri_get_theme_mod('page_preloader') ) ){
            $loading_style = ciri_get_theme_mod('page_preloader_type', 1);
            if($loading_style == 'custom'){
                if(($img = ciri_get_theme_mod('page_preloader_custom')) && !empty($img) ){
                    add_filter('ciri/filter/enable_image_lazyload', '__return_false', 10000);
                    add_filter('wp_lazy_loading_enabled', '__return_false', 10000);
                    echo '<div class="la-image-loading spinner-custom"><div class="content"><div class="la-loader"><img data-no-lazy="true" src="'.esc_url($img).'" width="50" height="50" alt="'.esc_attr(get_bloginfo('display')).'"/></div><div class="la-loader-ss"></div></div></div>';
                    ciri_deactive_filter('ciri/filter/enable_image_lazyload', '__return_false', 10000);
                    ciri_deactive_filter('wp_lazy_loading_enabled', '__return_false', 10000);
                }
                else{
                    echo '<div class="la-image-loading"><div class="content"><div class="la-loader spinner1"></div><div class="la-loader-ss"></div></div></div>';
                }
            }
            else{
                echo '<div class="la-image-loading"><div class="content"><div class="la-loader spinner'.esc_attr($loading_style).'"><div class="dot1"></div><div class="dot2"></div><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div><div class="cube1"></div><div class="cube2"></div><div class="cube3"></div><div class="cube4"></div></div><div class="la-loader-ss"></div></div></div>';
            }
        }
    }
}

/**
 * helper to change the excerpt length
 */
if(!function_exists('ciri_change_excerpt_length')){
    function ciri_change_excerpt_length( $length ){
        $length = 51;
        return $length;
    }
}

/**
 * Helper to render inline svg
 */

if(!function_exists('ciri_render_inline_icon_to_footer')){
    function ciri_render_inline_icon_to_footer(){
        get_template_part('partials/icons');
    }
}

if(!function_exists('ciri_change_excerpt_more')){
	function ciri_change_excerpt_more(){
		return '&hellip;';
	}
}