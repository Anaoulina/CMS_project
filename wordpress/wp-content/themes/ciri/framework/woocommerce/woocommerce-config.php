<?php
/**
 * Perform all main WooCommerce configurations for this theme
 *
 * @package Ciri WordPress theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if(!class_exists('Ciri_WooCommerce_Config')){

    class Ciri_WooCommerce_Config {

        /**
         * Main Class Constructor
         *
         * @since 1.0.0
         */
        public function __construct() {

            // Include helper functions
            require_once get_theme_file_path('/framework/woocommerce/woocommerce-helpers.php');
            require_once get_theme_file_path('/framework/woocommerce/woocommerce-compare.php');
            require_once get_theme_file_path('/framework/woocommerce/woocommerce-wishlist.php');

            add_filter('ciri/filter/sidebar_primary_name', array( $this, 'set_sidebar_for_shop'), 20 );

            add_action('init', array( $this, 'set_cookie_default' ), 2 );
            add_action('init', array( $this, 'custom_handling_empty_cart' ), 1 );

            add_filter('loop_shop_per_page', array( $this, 'change_per_page_default'), 10 );
            add_action('pre_get_posts', array( $this, 'set_posts_per_page_wc_builder'), 30 );

            // Remove WooCommerce default style
            add_filter( 'woocommerce_enqueue_styles', array($this, 'remove_woo_scripts') );

            // Load theme CSS
            add_action( 'wp_enqueue_scripts', array( $this, 'theme_css' ), 20 );

            // Load theme js
            add_action( 'wp_enqueue_scripts', array( $this, 'theme_js' ), 20 );

            // register sidebar widget areas
            add_action( 'widgets_init', array( $this, 'register_sidebars' ) );

            /**
             * Hooks in plugins
             */
            add_filter('woocommerce_show_page_title', '__return_false');
            add_action('init', array( $this, 'disable_plugin_hooks'));

            add_filter('template_include', array( $this, 'load_quickview_template'), 20 );

            /**
             * Hooks in plugins
             * WC_Vendors
             */
            if(class_exists('WC_Vendors', false)){
                // Add sold by to product loop before add to cart
                if ( WC_Vendors::$pv_options->get_option( 'sold_by' ) ) {
                    remove_action( 'woocommerce_after_shop_loop_item', array('WCV_Vendor_Shop', 'template_loop_sold_by'), 9 );
                    add_action( 'woocommerce_shop_loop_item_title', array('WCV_Vendor_Shop', 'template_loop_sold_by'), 10 );
                }
            }

            /**
             * Hooks in plugins
             */

            /**
             * Remove default wrappers and add new ones
             */
            remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
            remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
            remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
            add_action( 'woocommerce_before_main_content', array( $this, 'content_wrapper' ), 10 );
            add_action( 'woocommerce_after_main_content', array( $this, 'content_wrapper_end' ), 10 );


            /**
             * For Shop Page & Taxonomies
             */

            add_filter('woocommerce_post_class', array( $this, 'add_class_to_product_loop'), 30, 2 );

            add_action('woocommerce_before_subcategory_title', function (){ echo '<figure>'; }, 9);
            add_action('woocommerce_before_subcategory_title', function (){ echo '</figure>'; }, 11);

            add_action('woocommerce_before_shop_loop',  [ $this, 'setup_toolbar' ] , -999 );

            add_action('woocommerce_before_shop_loop',  [ $this, 'add_toolbar_open' ] , 15 );
            add_action('woocommerce_before_shop_loop',  [ $this, 'add_toolbar_close' ] , 35 );

            add_filter('woocommerce_loop_add_to_cart_args', array( $this, 'woocommerce_loop_add_to_cart_args'), 10, 2 );

            /**
             * For details page
             */

            add_filter('woocommerce_gallery_image_size', function(){ return 'shop_single'; } );
            add_action('woocommerce_before_add_to_cart_button', function(){ echo '<div class="lakit-wrap-cart-cta"><div class="wrap-cart-cta">'; }, 100);
            add_action('woocommerce_after_add_to_cart_button', function(){ echo '</div>'; }, 0);
            add_action('woocommerce_after_add_to_cart_button', array( $this , 'add_hidden_button_to_to_cart_form' ) );

            add_action('woocommerce_after_add_to_cart_button', array( $this , 'add_wishlist_btn' ), 50 );
            add_action('woocommerce_after_add_to_cart_button', array( $this , 'add_compare_btn' ), 55 );
            add_action('woocommerce_after_add_to_cart_button', function(){ echo '</div>'; }, 60);


            add_action('woocommerce_before_single_product_summary', array($this, 'wrapper_before_product_main'), -101);
            add_action('woocommerce_before_single_product_summary', array($this, 'wrapper_before_product_main_image'), -100);
            add_action('woocommerce_before_single_product_summary', array($this, 'wrapper_after_product_main_image'), 100);
            add_action('woocommerce_after_single_product_summary', array($this, 'wrapper_after_product_main'), -100);

            add_filter('woocommerce_product_tabs', array( $this, 'add_custom_tabs'));


            /**
             * For Cart
             */
            remove_action('woocommerce_cart_collaterals', 'woocommerce_cross_sell_display');
            add_filter('woocommerce_add_to_cart_fragments', [ $this, 'cart_fragments' ]);

            /**
             * Catalog Mode
             */
            if( ciri_string_to_bool( ciri_get_theme_mod('catalog_mode') ) ){
                // In Loop
                add_filter( 'woocommerce_loop_add_to_cart_link', '__return_empty_string', 10 );
                // In Page
                add_action( 'wp', array( $this, 'set_page_when_active_catalog_mode' ) );

                if( ciri_string_to_bool( ciri_get_theme_mod('catalog_mode_price') ) ){
                    add_filter('woocommerce_catalog_orderby', array( $this, 'remove_sortby_price_in_toolbar_when_active_catalog' ));
                    add_filter('woocommerce_default_catalog_orderby_options', array( $this, 'remove_sortby_price_in_toolbar_when_active_catalog' ));
                }
            }
        }

        public function register_sidebars(){
            $heading = 'h4';
            $heading = apply_filters( 'ciri/filter/sidebar_heading', $heading );

            // Shop Sidebar
            register_sidebar( array(
                'name'			=> esc_html__( 'Sidebar - Shop', 'ciri' ),
                'id'			=> 'sidebar-shop',
                'description'	=> esc_html__( 'Widgets in this area will be displayed in the shop page.', 'ciri' ),
                'before_widget'	=> '<div id="%1$s" class="widget %2$s">',
                'after_widget'	=> '</div>',
                'before_title'	=> '<'. $heading .' class="widget-title"><span>',
                'after_title'	=> '</span></'. $heading .'>',
            ) );

            register_sidebar( array(
                'name'			=> esc_html_x( 'Sidebar - Filter', 'admin-view',  'ciri' ),
                'id'            => 'sidebar-shop-filter',
                'before_widget'	=> '<div id="%1$s" class="widget %2$s">',
                'after_widget'	=> '</div>',
                'before_title'	=> '<'. $heading .' class="widget-title"><span>',
                'after_title'	=> '</span></'. $heading .'>',
            ) );
        }

        /**
         * Removes WooCommerce scripts.
         *
         * @access public
         * @since 1.0
         * @param array $scripts The WooCommerce scripts.
         * @return array
         */
        public function remove_woo_scripts($scripts) {
            if (isset($scripts['woocommerce-layout'])) {
                unset($scripts['woocommerce-layout']);
            }
            if (isset($scripts['woocommerce-smallscreen'])) {
                unset($scripts['woocommerce-smallscreen']);
            }
            if (isset($scripts['woocommerce-general'])) {
                unset($scripts['woocommerce-general']);
            }
            return $scripts;
        }

        public function theme_css(){

        }

        public function theme_js(){
            $theme_version = defined('WP_DEBUG') && WP_DEBUG ? time() : CIRI_THEME_VERSION;
            $ext = apply_filters('ciri/use_minify_js_file', false) || ( defined('WP_DEBUG') && WP_DEBUG ) ? '' : '.min';

            wp_register_script('ciri-woocommerce', get_theme_file_uri( '/assets/js/lib/woocommerce'. $ext .'.js' ), array('jquery'), $theme_version, true);
            wp_add_inline_script('wc-single-product', $this->product_image_flexslider_vars(), 'before');

            if ( 'yes' === get_option( 'woocommerce_enable_shipping_calc' ) && wc_shipping_enabled() ) {
                // Optional for better select.
                wp_enqueue_script( 'selectWoo' );
                wp_enqueue_style( 'select2' );

                // Required.
                wp_enqueue_script( 'wc-country-select' );
            }
        }

        public function product_image_flexslider_vars(){
            return "try{ wc_single_product_params.flexslider.directionNav=true; wc_single_product_params.flexslider.before = function(slider){ jQuery('.woocommerce-product-gallery').css( 'opacity', 1 ); jQuery(document).trigger('lastudiokit/woocommerce/single/init_product_slider', [slider]); jQuery(document).trigger('lastudio-kit/woocommerce/single/init_product_slider', [slider]) } }catch(ex){}";
        }

        /**
         * Content wrapper.
         */
        public function content_wrapper() {
            get_template_part( 'woocommerce/wc-content-wrapper' );
        }

        /**
         * Content wrapper end.
         */
        public function content_wrapper_end() {
            get_template_part( 'woocommerce/wc-content-wrapper-end' );
        }

        /**
         *
         * Override the sidebar for shop
         *
         * @param $sidebar
         * @return mixed
         */

        public function set_sidebar_for_shop( $sidebar ) {

            if( is_woocommerce() || is_account_page() || is_checkout() || is_cart() || is_wc_endpoint_url() ){
                $sidebar = 'sidebar-shop';
            }

            return $sidebar;
        }

        public function woocommerce_loop_add_to_cart_args( $args, $product) {
            if(isset($args['attributes'])){
                $args['attributes']['data-product_title'] = $product->get_title();
            }
            if(isset($args['class'])){
                $args['class'] = $args['class'] . ($product->is_purchasable() && $product->is_in_stock() ? '' : ' add_to_cart_button');
            }
            return $args;
        }

        public function add_compare_btn(){
            global $yith_woocompare, $product;
            if( ciri_string_to_bool( ciri_get_theme_mod('woocommerce_show_compare_btn') ) ) {
                if ( !empty($yith_woocompare->obj) ) {

                    $action_add = 'yith-woocompare-add-product';

                    $css_class = 'add_compare button';

                    if( $yith_woocompare->obj instanceof YITH_Woocompare_Frontend ){
                        $action_add = $yith_woocompare->obj->action_add;
                        if(!empty($yith_woocompare->obj->products_list) && in_array($product->get_id(), $yith_woocompare->obj->products_list)){
                            $css_class .= ' added';
                        }
                    }
                    $url_args = array('action' => $action_add, 'id' => $product->get_id());
                    $url = apply_filters('yith_woocompare_add_product_url', wp_nonce_url(add_query_arg($url_args), $action_add));

                    printf(
                        '<a class="%s" href="%s" title="%s" rel="nofollow" data-product_title="%s" data-product_id="%s"><span class="labtn-icon labtn-icon-compare"></span><span class="labtn-text">%s</span></a>',
                        esc_attr($css_class),
                        esc_url($url),
                        esc_attr_x('Add to compare','front-view', 'ciri'),
                        esc_attr($product->get_title()),
                        esc_attr($product->get_id()),
                        esc_attr_x('Add to compare','front-view', 'ciri')
                    );
                }
                else{

                    $css_class = 'add_compare button la-core-compare lakit-hint lakit-hint--top';
                    $url = $product->get_permalink();
                    $text = esc_html_x('Add to compare','front-view', 'ciri');
                    printf(
                        '<a class="%1$s" href="%2$s" title="%3$s" data-hint="%3$s" rel="nofollow" data-product_title="%4$s" data-product_id="%5$s"><span class="labtn-icon labtn-icon-compare"></span><span class="labtn-text">%3$s</span></a>',
                        esc_attr($css_class),
                        esc_url($url),
                        esc_attr($text),
                        esc_attr($product->get_title()),
                        esc_attr($product->get_id())
                    );
                }
            }
        }

        public function add_wishlist_btn(){

	        if( ciri_string_to_bool( ciri_get_theme_mod('woocommerce_show_wishlist_btn') ) ) {
                global $product;
                if (function_exists('YITH_WCWL')) {
                    $default_wishlists = is_user_logged_in() ? YITH_WCWL()->get_wishlists(array('is_default' => true)) : false;
                    if (!empty($default_wishlists)) {
                        $default_wishlist = $default_wishlists[0]['ID'];
                    }
                    else {
                        $default_wishlist = false;
                    }

                    if (YITH_WCWL()->is_product_in_wishlist($product->get_id(), $default_wishlist)) {
                        $text = esc_html_x('View Wishlist', 'front-view', 'ciri');
                        $class = 'add_wishlist la-yith-wishlist button added';
                        $url = YITH_WCWL()->get_wishlist_url('');
                    }
                    else {
                        $text = esc_html_x('Add to Wishlist', 'front-view', 'ciri');
                        $class = 'add_wishlist la-yith-wishlist button';
                        $url = add_query_arg('add_to_wishlist', $product->get_id(), YITH_WCWL()->get_wishlist_url(''));
                    }

                    printf(
                        '<a class="%s" href="%s" title="%s" rel="nofollow" data-product_title="%s" data-product_id="%s"><span class="labtn-icon labtn-icon-wishlist"></span><span class="labtn-text">%s</span></a>',
                        esc_attr($class),
                        esc_url($url),
                        esc_attr($text),
                        esc_attr($product->get_title()),
                        esc_attr($product->get_id()),
                        esc_attr($text)
                    );
                }

                elseif(class_exists('TInvWL_Public_AddToWishlist', false)){
                    $wishlist = TInvWL_Public_AddToWishlist::instance();
                    $user_wishlist = $wishlist->user_wishlist($product);
                    if(isset($user_wishlist[0], $user_wishlist[0]['in']) && $user_wishlist[0]['in']){
                        $class = 'add_wishlist button la-ti-wishlist added';
                        $url = tinv_url_wishlist_default();
                        $text = esc_html_x('View Wishlist', 'front-view', 'ciri');
                    }
                    else{
                        $class = 'add_wishlist button la-ti-wishlist';
                        $url = $product->get_permalink();
                        $text = esc_html_x('Add to wishlist', 'front-view', 'ciri');
                    }
                    printf(
                        '<a class="%s" href="%s" title="%s" rel="nofollow" data-product_title="%s" data-product_id="%s"><span class="labtn-icon labtn-icon-wishlist"></span><span class="labtn-text">%s</span></a>',
                        esc_attr($class),
                        esc_url($url),
                        esc_attr($text),
                        esc_attr($product->get_title()),
                        esc_attr($product->get_id()),
                        esc_attr($text)
                    );
                }

                else{

                    if(Ciri_WooCommerce_Wishlist::is_product_in_wishlist($product->get_id())){
                        $class = 'add_wishlist button la-core-wishlist added lakit-hint lakit-hint--top';
                        $url = ciri_get_wishlist_url();
                        $text = esc_html_x('View Wishlist', 'front-view', 'ciri');
                    }
                    else{
                        $class = 'add_wishlist button la-core-wishlist lakit-hint lakit-hint--top';
                        $url = $product->get_permalink();
                        $text = esc_html_x('Add to wishlist', 'front-view', 'ciri');
                    }

                    printf(
                        '<a class="%1$s" href="%2$s" title="%3$s" data-hint="%3$s" rel="nofollow" data-product_title="%4$s" data-product_id="%5$s"><span class="labtn-icon labtn-icon-wishlist"></span><span class="labtn-text">%3$s</span></a>',
                        esc_attr($class),
                        esc_url($url),
                        esc_attr($text),
                        esc_attr($product->get_title()),
                        esc_attr($product->get_id())
                    );
                }
            }
        }

        public function add_class_to_product_loop( $classes, $product ) {

            if($product->is_type( 'variable' )){
                if($product->child_is_in_stock()){
                    $classes[] = 'child-instock';
                }
            }
            return $classes;
        }


        public function custom_handling_empty_cart(){
            if (isset($_REQUEST['clear-cart'])) {
                WC()->cart->empty_cart();
            }
        }

        public function change_per_page_default($cols){
            $per_page_array = ciri_woo_get_product_per_page_array();
            $per_page = ciri_woo_get_product_per_page();
            if( !empty($per_page) && !empty($per_page_array) && ( in_array($per_page, $per_page_array) || count($per_page_array) == 1  )){
                $cols = $per_page;
            }
            else{
	            $cols = $per_page;
            }
            return $cols;
        }

        public function set_cookie_default(){
            if (isset($_GET['per_page'])) {
                add_filter('ciri/filter/get_product_per_page', array( $this, 'get_parameter_per_page'));
            }
        }

        public function get_parameter_per_page($per_page) {
            if (isset($_GET['per_page']) && ($_per_page = $_GET['per_page'])) {
                $param_allow = ciri_woo_get_product_per_page_array();
                if(!empty($param_allow) && in_array($_per_page, $param_allow)){
                    $per_page = $_per_page;
                }
            }
            return $per_page;
        }

        public function disable_plugin_hooks() {
            global $yith_woocompare;
            if(function_exists('YITH_WCWL_Init')){
                $yith_wcwl_obj = YITH_WCWL_Init();
                remove_action('wp_head', array($yith_wcwl_obj, 'add_button'));
            }
            if( !empty($yith_woocompare->obj) && ($yith_woocompare->obj instanceof YITH_Woocompare_Frontend ) ){
                remove_action('woocommerce_single_product_summary', array($yith_woocompare->obj, 'add_compare_link'), 35);
                remove_action('woocommerce_after_shop_loop_item', array($yith_woocompare->obj, 'add_compare_link'), 20);
            }
        }

        public function load_quickview_template( $template ){
            if(is_singular('product') && isset($_GET['product_quickview'])){
                $file     = locate_template( array(
                    'woocommerce/single-quickview.php'
                ) );
                if( isset($_GET['quickcart']) ){
                    $file     = locate_template( array(
                        'woocommerce/single-quickcart.php'
                    ) );
                }
                if($file){
                    return $file;
                }
            }
            return $template;
        }

        public function add_hidden_button_to_to_cart_form(){
            global $product;
            if($product->is_type('simple')){
                echo '<input type="hidden" name="add-to-cart" value="'.esc_attr($product->get_id()).'"/>';
            }
        }

        public function add_custom_tabs( $tabs ){

            if(ciri_string_to_bool(ciri_get_option('woo_enable_custom_tab'))){
                $custom_tabs = ciri_get_option('woo_custom_tabs');
                if(!empty($custom_tabs) && is_array($custom_tabs)){
                    foreach ($custom_tabs as $k => $custom_tab){
                        if(!empty($custom_tab['title']) && !empty($custom_tab['content'])){
                            $tabs['lasf_tab_' . $k] = array(
                                'title' => esc_html($custom_tab['title']),
                                'priority' => 50 + ($k * 5),
                                'custom_content' => $custom_tab['content'],
                                'el_class'  => isset($custom_tab['el_class']) ? $custom_tab['el_class'] : '',
                                'callback' => array( $this, 'callback_custom_tab_content')
                            );
                        }
                    }
                }
            }

            return $tabs;
        }

        public function callback_custom_tab_content( $tab_key, $tab_instance ){
            if(!empty($tab_instance['custom_content'])){
                echo wp_kses_post( ciri_transfer_text_to_format($tab_instance['custom_content'], true) );
            }
        }

        public function wrapper_before_product_main_image(){
            echo '<div class="woocommerce-product-gallery-outer layout-type-1">';
        }

        public function wrapper_after_product_main_image(){
            echo '</div>';
        }

        public function wrapper_before_product_main(){
            echo '<div class="product--inner">';
        }

        public function wrapper_after_product_main(){
            echo '</div>';
        }

        /*
         * Catalog Mode
         */
        public function set_page_when_active_catalog_mode(){
            wp_reset_postdata();
            if (is_cart() || is_checkout()) {
                wp_redirect(wc_get_page_permalink('shop'));
                exit;
            }
        }
        public function remove_sortby_price_in_toolbar_when_active_catalog( $array ){
            if( isset($array['price']) ){
                unset( $array['price'] );
            }
            if( isset($array['price-desc']) ){
                unset( $array['price-desc'] );
            }
            return $array;
        }

        public function add_toolbar_open(){
            if(wc_get_loop_prop('lakit_loop_allow_extra_filters')){
                echo '<div class="wc-toolbar-container">';
                echo '<div class="wc-toolbar wc-toolbar-top">';
            }
        }

        public function add_toolbar_close(){
            if(wc_get_loop_prop('lakit_loop_allow_extra_filters')){
                $view_mode = apply_filters('ciri/filter/catalog_view_mode', 'grid');
                $woocommerce_toggle_grid_list = true;
                $active_shop_filter = true;
                $per_page_array = ciri_woo_get_product_per_page_array();
                $per_row_array = ciri_woo_get_product_per_row_array();
                $per_page =  ciri_woo_get_product_per_page();
                $current_url = add_query_arg(null, null);
                $current_url = remove_query_arg(array('page', 'paged', 'mode_view', 'la_doing_ajax'), $current_url);
                $current_url = preg_replace('/\/page\/\d+/', '', $current_url);

                if(!empty($per_row_array)){
                    echo '<div class="lasf-custom-dropdown wc-view-item">';
                    echo sprintf('<button><span data-text="%1$s">%1$s</span></button>', esc_html__('View', 'ciri'));
                    echo '<ul>';
                    foreach ($per_row_array as $val){
                        echo sprintf('<li><a data-col="%3$s" href="javascript:;">%1$s %2$s</a></li>', esc_html__('View', 'ciri'), ($val < 10 ? '0' . $val : $val), esc_html($val));
                    }
                    echo '</ul>';
                    echo '</div>';
                }
                if(!empty($per_page_array)){
                    echo '<div class="lasf-custom-dropdown wc-view-count">';
                    echo sprintf('<button><span data-text="%1$s">%1$s %2$s</span></button>', esc_html__('Show', 'ciri'), esc_html($per_page));
                    echo '<ul>';
                    foreach ($per_page_array as $val){
                        echo sprintf('<li %4$s><a href="%3$s">%1$s %2$s</a></li>',
                            esc_html__('Show', 'ciri'),
                            esc_html($val),
                            esc_url(add_query_arg('per_page', $val, $current_url)),
                            ( $per_page === $val ? 'class="active"' : '' )
                        );
                    }
                    echo '</ul>';
                    echo '</div>';
                }
                if($active_shop_filter && is_active_sidebar('sidebar-shop-filter')){
                    echo sprintf('<div class="lasf-custom-dropdown wc-custom-filters"><button class="btn-advanced-shop-filter"><span>%1$s</span></button></div>', esc_html__('Filters', 'ciri'));
                }
                if( ciri_string_to_bool($woocommerce_toggle_grid_list) ):
                    ?>
                    <div class="wc-view-toggle">
                        <button data-view_mode="list"<?php if ($view_mode == 'list') echo ' class="active"';?>><i title="<?php echo esc_attr__('List view', 'ciri') ?>" class="lastudioicon-list-bullet-2"></i></button>
                        <button data-view_mode="grid"<?php if ($view_mode == 'grid')  echo ' class="active"'; ?>><i title="<?php echo esc_attr__('Grid view', 'ciri') ?>" class="lastudioicon-microsoft"></i></button>
                    </div>
                <?php
                endif;

                    echo '</div>';

                if (ciri_string_to_bool($active_shop_filter) && is_active_sidebar('sidebar-shop-filter')):
                ?>
                    <div class="clearfix"></div>
                    <div class="la-advanced-product-filters widget-area clearfix" data-id="shop-advance-filter">
                        <div class="sidebar-inner">
                            <div class="sidebar-inner--filters">
                                <?php dynamic_sidebar('sidebar-shop-filter'); ?>
                            </div>
                            <?php if( is_filtered() || (!is_filtered() && is_product_taxonomy() && isset($_GET['la_doing_ajax'])) ) : ?>
                                <div class="la-advanced-product-filters-result">
                                    <?php
                                    $base_filter = ciri_get_base_shop_url();
                                    if(isset($_GET['la_preset'])){
                                        $base_filter = add_query_arg('la_preset', $_GET['la_preset'], $base_filter);
                                    }
                                    ?>
                                    <a class="reset-all-shop-filter" href="<?php echo esc_url($base_filter) ?>"><i class="lastudioicon-e-remove"></i><span><?php echo esc_html__('Clear All Filter', 'ciri'); ?></span></a>
                                </div>
                            <?php endif; ?>
                        </div>
                        <a class="close-advanced-product-filters" href="javascript:;" rel="nofollow"><i class="lastudioicon-e-remove"></i></a>
                    </div>
                <?php
                endif;

                echo '</div>';
            }
        }

        public function setup_toolbar(){
            if(empty(wc_get_loop_prop('lakit_loop_before')) && (is_shop() || is_product_taxonomy())){
                wc_set_loop_prop('lakit_loop_allow_extra_filters', true);
            }
        }


        public function set_posts_per_page_wc_builder( $query ){
            global $wp_the_query;

            if ( ( ! is_admin() ) && ( $query === $wp_the_query ) && is_product_taxonomy()) {
                $query->set( 'posts_per_page',  $this->change_per_page_default(ciri_woo_get_product_per_page()) );
            }

            return $query;
        }

	    public function cart_fragments($fragments){
		    if(!isset($fragments['div.widget_shopping_cart_content'])){
			    ob_start();
			    woocommerce_mini_cart();
			    $mini_cart = ob_get_clean();
			    $fragments['div.widget_shopping_cart_content'] = '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>';
		    }
		    ob_start();
		    wc_get_template( 'cart/cart-totals-table.php' );
		    $content = ob_get_clean();
		    $content = str_replace(['id="shipping_method_', 'for="shipping_method_'], ['id="pp_shipping_method_', 'for="pp_shipping_method_'], $content);
		    $fragments['div.cart-totals-table'] = $content;

		    if ( apply_filters( 'woocommerce_enable_order_notes_field', 'yes' === get_option( 'woocommerce_enable_order_comments', 'yes' ) ) ) {
			    ob_start();
			    wc_get_template( 'cart/popup-order-notes.php' );
			    $content                              = ob_get_clean();
			    $fragments['#popup-cart-order-notes'] = $content;
		    }
		    if ( 'yes' === get_option( 'woocommerce_enable_shipping_calc' ) ) {
			    ob_start();
			    wc_get_template( 'cart/popup-shipping-calculator.php' );
			    $content = ob_get_clean();
			    $fragments['#popup-cart-shipping-calculator'] = $content;
		    }
		    if ( wc_coupons_enabled() ) {
			    ob_start();
			    wc_get_template( 'cart/popup-coupon.php' );
			    $content                         = ob_get_clean();
			    $fragments['#popup-cart-coupon'] = $content;
		    }

		    return $fragments;
	    }
    }

}

new Ciri_WooCommerce_Config();