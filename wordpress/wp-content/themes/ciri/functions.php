<?php

/**
 * Theme functions and definitions.
 *
 * Sets up the theme and provides some helper functions
 *
 * When using a child theme (see https://codex.wordpress.org/Theme_Development
 * and https://codex.wordpress.org/Child_Themes), you can override certain
 * functions (those wrapped in a function_exists() call) by defining them first
 * in your child theme's functions.php file. The child theme's functions.php
 * file is included before the parent theme's file, so the child theme
 * functions would be used.
 *
 *
 * For more information on hooks, actions, and filters,
 * see https://codex.wordpress.org/Plugin_API
 *
 * @package Ciri WordPress theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if(!defined('CIRI_THEME_VERSION')){
    define('CIRI_THEME_VERSION', '1.0.4');
}

if(!class_exists('Ciri_Theme_Class')){

    final class Ciri_Theme_Class {

	    /**
	     * @var string $template_dir_path
	     */
        public static $template_dir_path = '';

	    /**
	     * @var string $template_dir_url
	     */
        public static $template_dir_url = '';

	    /**
	     * @var Ciri_Ajax_Manager $ajax_manager;
	     */
	    public $ajax_manager;

	    /**
	     * @var string $extra_style
	     */
	    protected $extra_style = '';

	    /**
	     * A reference to an instance of this class.
	     *
	     * @since  1.0.0
	     * @access private
	     * @var    object
	     */
	    private static $instance = null;

        /**
         * Main Theme Class Constructor
         *
         * @since   1.0.0
         */
        public function __construct() {

            self::$template_dir_path   = get_template_directory();
            self::$template_dir_url    = get_template_directory_uri();

            // Define constants
            add_action( 'after_setup_theme', array( $this, 'constants' ), 0 );

            // Load all core theme function files
            add_action( 'after_setup_theme', array( $this, 'include_functions' ), 1 );

            // Load configuration classes
            add_action( 'after_setup_theme', array( $this, 'configs' ), 3 );

            // Load framework classes
            add_action( 'after_setup_theme', array( $this, 'classes' ), 4 );

            // Setup theme => add_theme_support: register_nav_menus, load_theme_textdomain, etc
            add_action( 'after_setup_theme', array( $this, 'theme_setup' ) );

            add_action( 'after_setup_theme', array( $this, 'theme_setup_default' ) );

            // register sidebar widget areas
            add_action( 'widgets_init', array( $this, 'register_sidebars' ) );

            /** Admin only actions **/
            if( is_admin() ) {
                // Load scripts in the WP admin
                add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
                add_action( 'elementor/editor/before_enqueue_scripts', array( $this, 'admin_scripts' ) );
            }
            /** Non Admin actions **/
            else{

                // Load theme CSS
                add_action( 'wp_enqueue_scripts', array( $this, 'theme_css' ) );

                // Load theme js
                add_action( 'wp_enqueue_scripts', array( $this, 'theme_js' ), 99 );

                // Add a pingback url auto-discovery header for singularly identifiable articles
                add_action( 'wp_head', array( $this, 'pingback_header' ), 1 );

                // Add meta viewport tag to header
                add_action( 'wp_head', array( $this, 'meta_viewport' ), 1 );

                // Add meta apple web app capable tag to header
                add_action( 'wp_head', array( $this, 'apple_mobile_web_app_capable_header' ), 1 );

                // Add an X-UA-Compatible header
                add_filter( 'wp_headers', array( $this, 'x_ua_compatible_headers' ) );

                // Add support for Elementor Pro locations
                add_action( 'elementor/theme/register_locations', array( $this, 'register_elementor_locations' ) );

                // Load External Resources

                add_action( 'wp_footer', array( $this, 'load_external_resources' ) );
            }

            add_action( 'elementor/init', array( $this, 'register_breakpoint' ) );

	        require_once get_theme_file_path('/framework/classes/ajax-manager.php');
			$this->ajax_manager = new Ciri_Ajax_Manager();

        }

	    public static function get_instance() {
		    // If the single instance hasn't been set, set it now.
		    if ( null == self::$instance ) {
			    self::$instance = new self;
		    }
		    return self::$instance;
	    }

        /**
         * Define Constants
         *
         * @since   1.0.0
         */
        public function constants() {}

        /**
         * Load all core theme function files
         *
         * @since 1.0.0
         */
        public function include_functions() {

            require_once get_theme_file_path('/framework/functions/helpers.php');
            require_once get_theme_file_path('/framework/functions/theme-hooks.php');
            require_once get_theme_file_path('/framework/functions/theme-functions.php');
            require_once get_theme_file_path('/framework/third/lastudio-kit.php');

        }

        /**
         * Configs for 3rd party plugins.
         *
         * @since 1.0.0
         */
        public function configs() {

            // WooCommerce
            if(function_exists('WC')){
                require_once get_theme_file_path('/framework/woocommerce/woocommerce-config.php');
            }
        }

        /**
         * Load theme classes
         *
         * @since   1.0.0
         */
        public function classes() {
            // Admin only classes
            if ( is_admin() ) {
                // Recommend plugins
                require_once get_theme_file_path('/tgm/class-tgm-plugin-activation.php');
                require_once get_theme_file_path('/tgm/tgm-plugin-activation.php');
            }

            require_once get_theme_file_path('/framework/classes/admin.php');
            // Breadcrumbs class
            require_once get_theme_file_path('/framework/classes/breadcrumbs.php');

            new Ciri_Admin();
        }

        /**
         * Theme Setup
         *
         * @since   1.0.0
         */
        public function theme_setup() {

            $ext = apply_filters('ciri/use_minify_css_file', false) || ( defined('WP_DEBUG') && WP_DEBUG ) ? '' : '.min';

            // Load text domain
            load_theme_textdomain( 'ciri', self::$template_dir_path .'/languages' );

            // Get globals
            global $content_width;
            // Set content width based on theme's default design
            if ( ! isset( $content_width ) ) {
                $content_width = 1200;
            }

            // Register navigation menus
            register_nav_menus( array(
                'main-nav'   => esc_attr_x( 'Main Navigation', 'admin-view', 'ciri' )
            ) );

            // Enable support for Post Formats
            add_theme_support( 'post-formats', array( 'video', 'gallery', 'audio', 'quote', 'link' ) );

            // Enable support for <title> tag
            add_theme_support( 'title-tag' );

            // Add default posts and comments RSS feed links to head
            add_theme_support( 'automatic-feed-links' );

            // Enable support for Post Thumbnails on posts and pages
            add_theme_support( 'post-thumbnails' );

            /**
             * Enable support for header image
             */
            add_theme_support( 'custom-header', apply_filters( 'ciri/filter/custom_header_args', array(
                'width'              => 2000,
                'height'             => 1200,
                'flex-height'        => true,
                'video'              => true,
            ) ) );

            add_theme_support( 'custom-background' );

            // Declare WooCommerce support.
            add_theme_support( 'woocommerce' );
            if( ciri_string_to_bool( ciri_get_theme_mod('woocommerce_gallery_zoom') ) ){
                add_theme_support( 'wc-product-gallery-zoom');
            }
            if( ciri_string_to_bool( ciri_get_theme_mod('woocommerce_gallery_lightbox') ) ){
                add_theme_support( 'wc-product-gallery-lightbox');
            }

            add_theme_support( 'wc-product-gallery-slider');

            // Support WP Job Manager
            add_theme_support( 'job-manager-templates' );

            // Add editor style
            add_editor_style( 'assets/css/editor-style.css' );

            // Adding Gutenberg support
            add_theme_support( 'align-wide' );
            add_theme_support( 'wp-block-styles' );
            add_theme_support( 'responsive-embeds' );
            add_theme_support( 'editor-styles' );
            add_editor_style( 'assets/css/gutenberg-editor.css' );

            add_theme_support( 'editor-color-palette', array(

                array(
                    'name' => esc_attr_x( 'pale pink', 'admin-view', 'ciri' ),
                    'slug' => 'pale-pink',
                    'color' => '#f78da7',
                ),

                array(
                    'name' => esc_attr_x( 'theme primary', 'admin-view', 'ciri' ),
                    'slug' => 'ciri-theme-primary',
                    'color' => '#BC8157',
                ),

                array(
                    'name' => esc_attr_x( 'theme secondary', 'admin-view', 'ciri' ),
                    'slug' => 'ciri-theme-secondary',
                    'color' => '#212121',
                ),

                array(
                    'name' => esc_attr_x( 'strong magenta', 'admin-view', 'ciri' ),
                    'slug' => 'strong-magenta',
                    'color' => '#a156b4',
                ),
                array(
                    'name' => esc_attr_x( 'light grayish magenta', 'admin-view', 'ciri' ),
                    'slug' => 'light-grayish-magenta',
                    'color' => '#d0a5db',
                ),
                array(
                    'name' => esc_attr_x( 'very light gray', 'admin-view',  'ciri' ),
                    'slug' => 'very-light-gray',
                    'color' => '#eee',
                ),
                array(
                    'name' => esc_attr_x( 'very dark gray', 'admin-view', 'ciri' ),
                    'slug' => 'very-dark-gray',
                    'color' => '#444',
                ),
            ) );

	        remove_theme_support( 'widgets-block-editor' );

	        add_theme_support('lastudio', [
                'lakit-swatches' => true,
                'revslider'      => true,
                'header-builder' => [
                    'menu' => true,
                    'header-vertical' => true
                ],
                'lastudio-kit'    => true,
                'elementor'       => [
                    'advanced-carousel' => false,
                    'ajax-templates'    => false,
                    'css-transform'     => false,
                    'floating-effects'  => false,
                    'wrapper-links'     => false,
                    'lastudio-icon'     => true,
                    'custom-fonts'      => true,
                    'mega-menu'         => true,
                    'product-grid-v2'   => true,
                    'slides-v2'         => true,
                ],
            ]);
        }

        /**
         * Theme Setup Default
         *
         * @since   1.0.0
         */
        public function theme_setup_default(){
            $check_theme = get_option('ciri_has_init', false);
            if(!$check_theme || !get_option('lastudio-kit-settings')){

				$cpt_supports = ['page', 'post'];
				if( post_type_exists('la_portfolio') ){
					$cpt_supports[] = ['la_portfolio'];
				}

                update_option('ciri_has_init', true);
                update_option( 'elementor_cpt_support', $cpt_supports );
	            update_option(
		            'lastudio-kit-settings',
		            [
		            	'svg-uploads' => 'enabled',
		            	'lastudio_kit_templates' => 'enabled',
		            	'single_post_template' => 'templates/fullwidth.php',
		            	'single_page_template' => 'templates/fullwidth.php',
		            ]
	            );
	            $customizes = [

                ];
                if(!empty($customizes)){
                    foreach ($customizes as $k => $v){
                        set_theme_mod($k, $v);
                    }
                }
            }
        }

        /**
         * Adds the meta tag to the site header
         *
         * @since 1.0.0
         */
        public function pingback_header() {

            if ( is_singular() && pings_open() ) {
                printf( '<link rel="pingback" href="%s">' . "\n", esc_url( get_bloginfo( 'pingback_url' ) ) );
            }

        }

        /**
         * Adds the meta tag to the site header
         *
         * @since 1.0.0
         */
        public function apple_mobile_web_app_capable_header() {
            printf( '<meta name="apple-mobile-web-app-capable" content="yes">' );
	        echo sprintf('<%1$s data-lastudiopagespeed-nooptimize="true">%2$s</%1$s>', 'script','"undefined"!=typeof navigator&&(/(lighthouse|gtmetrix)/i.test(navigator.userAgent.toLocaleLowerCase()))?document.documentElement.classList.add("isPageSpeed"):document.documentElement.classList.add("lasf-no_ps")');
        }

        /**
         * Adds the meta tag to the site header
         *
         * @since 1.0.0
         */
        public function meta_viewport() {

            // Meta viewport
            $viewport = '<meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1.0">';

            // Apply filters for child theme tweaking
            echo apply_filters( 'ciri_meta_viewport', $viewport );

        }

        /**
         * Load scripts in the WP admin
         *
         * @since 1.0.0
         */
        public function admin_scripts() {
            // Load font icon style
            wp_enqueue_style( 'ciri-font-lastudioicon', get_theme_file_uri( '/assets/css/lastudioicon.min.css' ), false, '1.0.0' );
            wp_enqueue_style( 'ciri-google-fonts', $this->enqueue_google_fonts_url() , array(), null );
            wp_enqueue_style( 'ciri-typekit-fonts', $this->enqueue_typekit_fonts_url() , array(), null );
        }


        /**
         * Load front-end scripts
         *
         * @since   1.0.0
         */
        public function theme_css() {

            $theme_version = defined('WP_DEBUG') && WP_DEBUG ? time() : CIRI_THEME_VERSION;

            $ext = apply_filters('ciri/use_minify_css_file', false) || ( defined('WP_DEBUG') && WP_DEBUG ) ? '' : '.min';

            // Load font icon style
            wp_enqueue_style( 'ciri-font-lastudioicon', get_theme_file_uri ('/assets/css/lastudioicon'.$ext.'.css' ), false, $theme_version );

            wp_enqueue_style( 'ciri-theme', get_parent_theme_file_uri('/style'.$ext.'.css'), false, $theme_version );

            $this->render_extra_style();

            $additional_inline_stype = ciri_minimizeCSS($this->extra_style);

            $inline_handler_name = 'ciri-theme';

            if(function_exists('WC')){
                wp_enqueue_style( 'ciri-woocommerce', get_theme_file_uri( '/assets/css/woocommerce'.$ext.'.css' ), false, $theme_version );
                $inline_handler_name = 'ciri-woocommerce';
            }

            wp_add_inline_style($inline_handler_name, $additional_inline_stype);
        }

        /**
         * Returns all js needed for the front-end
         *
         * @since 1.0.0
         */
        public function theme_js() {

            $theme_version = defined('WP_DEBUG') && WP_DEBUG ? time() : CIRI_THEME_VERSION;

            $ext = !apply_filters('ciri/use_minify_js_file', true) || ( defined('WP_DEBUG') && WP_DEBUG ) ? '' : '.min';

            // Get localized array
            $localize_array = $this->localize_array();

            wp_register_script( 'pace', get_theme_file_uri('/assets/js/lib/pace'.$ext.'.js'), null, $theme_version, true);

            wp_register_script( 'js-cookie', get_theme_file_uri('/assets/js/lib/js.cookie'.$ext.'.js'), array('jquery'), $theme_version, true);

            wp_register_script( 'jquery-featherlight', get_theme_file_uri('/assets/js/lib/featherlight'.$ext.'.js') , array('jquery'), $theme_version, true);

            $dependencies = array( 'jquery', 'js-cookie', 'jquery-featherlight');

	        if( ciri_string_to_bool( ciri_get_theme_mod('page_preloader') ) ){
                $dependencies[] = 'pace';
            }

            if(function_exists('WC')){
                $dependencies[] = 'ciri-woocommerce';
            }

            $dependencies = apply_filters('ciri/filter/js_dependencies', $dependencies);

            wp_enqueue_script('ciri-theme', get_theme_file_uri( '/assets/js/app'.$ext.'.js' ), $dependencies, $theme_version, true);

            if (is_singular() && comments_open() && get_option('thread_comments')) {
                wp_enqueue_script('comment-reply');
            }

            if(apply_filters('ciri/filter/force_enqueue_js_external', true)){
                wp_localize_script('ciri-theme', 'la_theme_config', $localize_array );
            }

            if(function_exists('la_get_polyfill_inline')){

                $polyfill_data = apply_filters('ciri/filter/js_polyfill_data', [
                    'ciri-polyfill-object-assign' => [
                        'condition' => '\'function\'==typeof Object.assign',
                        'src'       => get_theme_file_uri( '/assets/js/lib/polyfill-object-assign'.$ext.'.js' ),
                        'version'   => $theme_version,
                    ],
                    'ciri-polyfill-css-vars' => [
                        'condition' => 'window.CSS && window.CSS.supports && window.CSS.supports(\'(--foo: red)\')',
                        'src'       => get_theme_file_uri( '/assets/js/lib/polyfill-css-vars'.$ext.'.js' ),
                        'version'   => $theme_version,
                    ],
                    'ciri-polyfill-promise' => [
                        'condition' => '\'Promise\' in window',
                        'src'       => get_theme_file_uri( '/assets/js/lib/polyfill-promise'.$ext.'.js' ),
                        'version'   => $theme_version,
                    ],
                    'ciri-polyfill-fetch' => [
                        'condition' => '\'fetch\' in window',
                        'src'       => get_theme_file_uri( '/assets/js/lib/polyfill-fetch'.$ext.'.js' ),
                        'version'   => $theme_version,
                    ],
                    'ciri-polyfill-object-fit' => [
                        'condition' => '\'objectFit\' in document.documentElement.style',
                        'src'       => get_theme_file_uri( '/assets/js/lib/polyfill-object-fit'.$ext.'.js' ),
                        'version'   => $theme_version,
                    ]
                ]);
                $polyfill_inline = la_get_polyfill_inline($polyfill_data);
                if(!empty($polyfill_inline)){
                    wp_add_inline_script('ciri-theme', $polyfill_inline, 'before');
                }
            }

        }

        public function load_external_resources(){
            if(!wp_style_is('elementor-frontend')){
                wp_enqueue_style( 'ciri-google-fonts', $this->enqueue_google_fonts_url() , array(), null );
                wp_enqueue_style( 'ciri-typekit-fonts', $this->enqueue_typekit_fonts_url() , array(), null );
            }
        }

        /**
         * Functions.js localize array
         *
         * @since 1.0.0
         */
        public function localize_array() {

            $template_cache = ciri_string_to_bool(ciri_get_option('template_cache'));

            $array = array(
                'single_ajax_add_cart' => ciri_string_to_bool( ciri_get_theme_mod('single_ajax_add_cart') ),
                'i18n' => array(
                    'backtext' => esc_attr_x('Back', 'front-view', 'ciri'),
                    'compare' => array(
                        'view' => esc_attr_x('Compare List', 'front-view', 'ciri'),
                        'success' => esc_attr_x('has been added to comparison list.', 'front-view', 'ciri'),
                        'error' => esc_attr_x('An error occurred ,Please try again !', 'front-view', 'ciri')
                    ),
                    'wishlist' => array(
                        'view' => esc_attr_x('View Wishlist', 'front-view', 'ciri'),
                        'success' => esc_attr_x('has been added to your wishlist.', 'front-view', 'ciri'),
                        'error' => esc_attr_x('An error occurred, Please try again !', 'front-view', 'ciri')
                    ),
                    'addcart' => array(
                        'view' => esc_attr_x('View Cart', 'front-view', 'ciri'),
                        'success' => esc_attr_x('has been added to your cart', 'front-view', 'ciri'),
                        'error' => esc_attr_x('An error occurred, Please try again !', 'front-view', 'ciri')
                    ),
                    'global' => array(
                        'error' => esc_attr_x('An error occurred ,Please try again !', 'front-view', 'ciri'),
                        'search_not_found' => esc_attr_x('It seems we can’t find what you’re looking for, please try again !', 'front-view', 'ciri'),
                        'comment_author' => esc_attr_x('Please enter Name !', 'front-view', 'ciri'),
                        'comment_email' => esc_attr_x('Please enter Email Address !', 'front-view', 'ciri'),
                        'comment_rating' => esc_attr_x('Please select a rating !', 'front-view', 'ciri'),
                        'comment_content' => esc_attr_x('Please enter Comment !', 'front-view', 'ciri'),
                        'continue_shopping' => esc_attr_x('Continue Shopping', 'front-view', 'ciri'),
                        'cookie_disabled' => esc_attr_x('We are sorry, but this feature is available only if cookies are enabled on your browser', 'front-view', 'ciri'),
                        'more_menu' => esc_attr_x('Show More +', 'front-view', 'ciri'),
                        'less_menu' => esc_attr_x('Show Less', 'front-view', 'ciri'),
                        'search_view_more' => esc_attr_x('View More', 'front-view', 'ciri'),
                    )
                ),
                'js_path'       => esc_attr(apply_filters('ciri/filter/js_path', self::$template_dir_url . '/assets/js/lib/')),
                'js_min'        => apply_filters('ciri/use_minify_js_file', true),
                'theme_path'    => esc_attr(apply_filters('ciri/filter/theme_path', self::$template_dir_url . '/')),
                'ajax_url'      => esc_attr(admin_url('admin-ajax.php')),
                'has_wc' => function_exists('WC' ) ? true : false,
                'cache_ttl' => apply_filters('ciri/cache_time_to_life', !$template_cache ? 30 : (60 * 5)),
                'local_ttl' => apply_filters('ciri/local_cache_time_to_life', !$template_cache ? 30 : (60 * 60 * 24)),
                'home_url' => esc_url(home_url('/')),
                'shop_url'      => function_exists('wc_get_page_id') ? get_permalink( wc_get_page_id( 'shop' ) ) : home_url('/'),
                'current_url' => esc_url( add_query_arg(null,null) ),
                'disable_cache' => $template_cache ? false : true,
                'is_dev' => defined('WP_DEBUG') && WP_DEBUG ? true : false,
				'ajaxGlobal' => [
					'nonce'     => $this->ajax_manager->create_nonce(),
					'action'    => 'lastudio_theme_ajax',
					'useFront'  => 'true',
				]
            );

            if(function_exists('la_get_wc_script_data') && function_exists('WC')){
                $variation_data = la_get_wc_script_data('wc-add-to-cart-variation');
                if(!empty($variation_data)){
                    $array['i18n']['variation'] = $variation_data;
                }
                $array['wc_variation'] = [
                    'base' => esc_url(WC()->plugin_url()) . '/assets/js/frontend/add-to-cart-variation.min.js',
                    'wp_util' => esc_url(includes_url('js/wp-util.min.js')),
                    'underscore' => esc_url(includes_url('js/underscore.min.js'))
                ];
            }

            // Apply filters and return array
            return apply_filters( 'ciri/filter/localize_array', $array );
        }

        /**
         * Add headers for IE to override IE's Compatibility View Settings
         *
         * @since 1.0.0
         */
        public function x_ua_compatible_headers( $headers ) {
            $headers['X-UA-Compatible'] = 'IE=edge';
            return $headers;
        }

        /**
         * Add support for Elementor Pro locations
         *
         * @since 1.0.0
         */
        public function register_elementor_locations( $elementor_theme_manager ) {
            $elementor_theme_manager->register_all_core_location();
        }

        /**
         * Registers sidebars
         *
         * @since   1.0.0
         */
        public function register_sidebars() {

            $heading = 'div';
            $heading = apply_filters( 'ciri/filter/sidebar_heading', $heading );

            // Default Sidebar
            register_sidebar( array(
                'name'			=> esc_html__( 'Default Sidebar', 'ciri' ),
                'id'			=> 'sidebar',
                'description'	=> esc_html__( 'Widgets in this area will be displayed in the left or right sidebar area if you choose the Left or Right Sidebar layout.', 'ciri' ),
                'before_widget'	=> '<div id="%1$s" class="widget %2$s">',
                'after_widget'	=> '</div>',
                'before_title'	=> '<'. $heading .' class="widget-title"><span>',
                'after_title'	=> '</span></'. $heading .'>',
            ) );

        }

        public static function enqueue_google_fonts_url(){
            $fonts_url = '';
            $fonts     = array();

            if ( 'off' !== _x( 'on', 'Josefin Sans font: on or off', 'ciri' ) ) {
                $fonts[] = 'Josefin+Sans:ital,wght@0,100;0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700';
            }

            if ( $fonts ) {
                $fonts_url = add_query_arg( array(
                    'family' => implode( '&family=', $fonts ),
                    'display' => 'swap',
                ), 'https://fonts.googleapis.com/css2' );
            }
            return esc_url_raw( $fonts_url );
        }

        public static function enqueue_typekit_fonts_url(){
	        $fonts_url = '';

	        if ( 'off' !== _x( 'on', 'Niveau Grotesk font: on or off', 'ciri' ) ) {
		        $fonts_url = '//use.typekit.net/xxc8que.css';
	        }

	        return esc_url_raw( $fonts_url );
        }

        public function render_extra_style(){
            $this->extra_style .= $this->css_page_preload();
        }

        private function css_page_preload(){
            ob_start();
            include get_parent_theme_file_path('/framework/css/page-preload-css.php');
            $content = ob_get_clean();
            return $content;
        }

        public function register_breakpoint(){
            if(defined('ELEMENTOR_VERSION') && class_exists('Elementor\Core\Breakpoints\Manager', false)){
                $has_register_breakpoint = get_option('ciri_has_register_breakpoint', false);
                if(empty($has_register_breakpoint)){
                    update_option('elementor_experiment-additional_custom_breakpoints', 'active');
                    update_option('elementor_experiment-container', 'active');
                    $kit_active_id = Elementor\Plugin::$instance->kits_manager->get_active_id();
                    $raw_kit_settings = get_post_meta( $kit_active_id, '_elementor_page_settings', true );
                    if(empty($raw_kit_settings)){
	                    $raw_kit_settings = [];
                    }
                    $default_settings = [
                        'space_between_widgets' => '0',
                        'page_title_selector' => 'h1.entry-title',
                        'stretched_section_container' => '',
                        'active_breakpoints' => [
                            'viewport_mobile',
                            'viewport_mobile_extra',
                            'viewport_tablet',
                            'viewport_laptop',
                        ],
                        'viewport_mobile' => 767,
                        'viewport_md' => 768,
                        'viewport_mobile_extra' => 991,
                        'viewport_tablet' => 1279,
                        'viewport_lg' => 1280,
                        'viewport_laptop' => 1599,
                        'system_colors' => [
	                        [
		                        '_id' => 'primary',
		                        'title' => esc_html__( 'Primary', 'ciri' )
	                        ],
	                        [
		                        '_id' => 'secondary',
		                        'title' => esc_html__( 'Secondary', 'ciri' )
	                        ],
	                        [
		                        '_id' => 'text',
		                        'title' => esc_html__( 'Text', 'ciri' )
	                        ],
	                        [
		                        '_id' => 'accent',
		                        'title' => esc_html__( 'Accent', 'ciri' )
	                        ]
                        ],
                        'system_typography' => [
	                        [
		                        '_id' => 'primary',
		                        'title' => esc_html__( 'Primary', 'ciri' )
	                        ],
	                        [
		                        '_id' => 'secondary',
		                        'title' => esc_html__( 'Secondary', 'ciri' )
	                        ],
	                        [
		                        '_id' => 'text',
		                        'title' => esc_html__( 'Text', 'ciri' )
	                        ],
	                        [
		                        '_id' => 'accent',
		                        'title' => esc_html__( 'Accent', 'ciri' )
	                        ]
                        ]
                    ];
                    $raw_kit_settings = array_merge($raw_kit_settings, $default_settings);
                    update_post_meta( $kit_active_id, '_elementor_page_settings', $raw_kit_settings );
                    Elementor\Core\Breakpoints\Manager::compile_stylesheet_templates();
                    update_option('ciri_has_register_breakpoint', true);
                }
            }
        }
    }

    Ciri_Theme_Class::get_instance();

}