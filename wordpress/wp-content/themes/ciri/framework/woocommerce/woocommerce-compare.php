<?php

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
    exit( 'Direct script access denied.' );
}

if(!class_exists('Ciri_WooCommerce_Compare')){
    class Ciri_WooCommerce_Compare {

        protected $setting = null;

        protected static $data = array();

        public function __construct( $setting = array() ){

            $this->setting = array_merge(array(
                'cookie_name'       => 'ciri_compare',
                'user_meta_key'     => 'la_compare',
                'cookie_life'       => MONTH_IN_SECONDS
            ), $setting);

            $this->load_data();
            $this->init();
        }

        private function get_site_id(){
            global $blog_id;
            return is_multisite() ? absint($blog_id) : 1;
        }

        private function load_data( $data = null ){
            /**
             * We need load data from cookie and user meta ( if user is logged in )
             */
            if(is_null($data)){
                $lists = array_merge(
                    $this->get_lists_from_cookie(),
                    $this->get_lists_from_usermeta()
                );
                $data = array_values( array_unique( $lists ) );
            }
            if(!empty($data)){
                $tmp = array();
                foreach($data as $product_id){
                    if($this->check_is_product($product_id)){
                        array_push($tmp, $product_id);
                    }
                }
                $data = $tmp;
            }
            self::$data = $data;
        }

        public static function get_data(){
            return array_values(self::$data);
        }

        private function init(){

	        add_action('lastudio-theme/ajax/register_actions', [ $this, 'register_ajax_actions' ] );
            add_action( 'woocommerce_init', array( $this, 'remove_item_from_url' ), 99 );
            add_filter( 'lastudio/compare/count', array($this, 'get_count') );
            $callback = 'add';
            $callback .= '_shortcode';
            call_user_func($callback, 'la_compare', [ $this, 'output' ] );

        }

	    /**
	     * @param Ciri_Ajax_Manager $ajax_manager
	     *
	     * @return void
	     */
	    public function register_ajax_actions( $ajax_manager ){
		    $ajax_manager->register_ajax_action('compare', [ $this, 'new_ajax_process' ]);
	    }

	    /**
	     * @param array $request
	     *
	     * @return array
	     */
	    public function new_ajax_process( $request = [] ){
		    $return_data = [];
		    if(!empty($request['type']) && $request['type'] == 'load'){
			    $return_data['has_error'] = false;
		    }
		    else if(empty($request['post_id']) || (!empty($request['post_id']) && !$this->check_is_product( $request['post_id'] )) ){
			    $return_data['message'] = esc_html__('Invalid Product ID', 'ciri');
			    $return_data['has_error'] = true;
		    }
		    else{
			    $method = ( !empty( $request['type'] ) && $request['type'] == 'remove' ? 'remove' : 'add' );
			    $return_data = $this->$method($request['post_id']);
		    }
		    $return_data['compare_url'] = ciri_get_compare_url();
		    $return_data['count'] = $this->get_count();
		    $return_data['table_output'] = $this->output();
		    return $return_data;
	    }

        public function remove_item_from_url(){
            if(isset($_GET['la_helpers_compare_remove']) && ( $product_id = absint($_GET['la_helpers_compare_remove'])) ) {
                $product_exists = $this->get_data();
                if(($key = array_search($product_id, $product_exists)) !== false) {
                    unset($product_exists[$key]);
                    $product_exists = array_values($product_exists);
                    $this->set_lists_for_user($product_exists);
                    $this->set_lists_for_cookie($product_exists);
                    self::$data = $product_exists;
                    wc_add_notice( sprintf( esc_html__('"%1$s" has been removed from list', 'ciri'), get_the_title($product_id) ) );
                }
            }
        }

        public function check_is_product( $product_id ) {
            if(empty($product_id)){
                return false;
            }
            else{
                $post_type = get_post_type($product_id);
                if(!in_array($post_type, array('product', 'product_variation'))){
                    return false;
                }
                return true;
            }
        }

        public static function is_product_in_compare( $product_id ) {
            if(empty(self::$data)){
                return false;
            }
            else{
                return in_array( $product_id, self::$data );
            }
        }

        private function add( $post_id = 0 ){
            $lists = $this->get_data();
            $response = array();
            if(in_array($post_id, $lists)){
                $response['message'] = esc_html__('Product already in list', 'ciri');
                $response['has_error'] = true;
            }
            else{
                $response['message'] = sprintf( esc_html__('"%1$s" has been added', 'ciri'), get_the_title($post_id) );
                $response['has_error'] = false;
                array_push($lists, $post_id);
                $lists = array_values($lists);
                $this->set_lists_for_user($lists);
                $this->set_lists_for_cookie($lists);
	            self::$data = $lists;
            }
            return $response;
        }

        private function remove( $post_id = 0 ){
            $lists = $this->get_data();
            $response = array();
            if(($key = array_search($post_id, $lists)) !== false) {
                $response['message'] = esc_html__('Product has been removed from list', 'ciri');
                $response['has_error'] = false;
                unset($lists[$key]);
                $lists = array_values($lists);
                $this->set_lists_for_user($lists);
                $this->set_lists_for_cookie($lists);
	            self::$data = $lists;
            }
            else{
                $response['message'] = esc_html__('Product does not exist in list', 'ciri');
                $response['has_error'] = true;
            }
            return $response;
        }

        private function get_lists_from_cookie( $site_id = null ){

            $lists = array();

            if (empty($_COOKIE[ $this->setting['cookie_name'] ])) return $lists;

            if(empty($site_id)){
                $site_id = (int) $this->get_site_id();
            }

            $values = json_decode(stripslashes($_COOKIE[$this->setting['cookie_name']]), true);

            if(empty($values)) return $lists;

            foreach( $values as $value ){
                if( isset($value['site_id']) && $value['site_id'] == $site_id ){
                    $lists = $value['posts'];
                    break;
                }
            }

            return $lists;
        }

        private function get_lists_from_usermeta( $user_login = '' ){

            $lists = array();
            return $lists;
        }

        private function set_lists_for_user( $lists = array(), $user_login = '' ){
            return;
        }

        private function set_lists_for_cookie( $lists = array() ) {

            $site_id = $this->get_site_id();

            $key = false;

            $values = array();

            if(!empty($_COOKIE[ $this->setting['cookie_name'] ])){
                $values = json_decode(stripslashes($_COOKIE[$this->setting['cookie_name']]), true);
                if(!empty($values)){
                    foreach($values as $k => $value){
                        if( isset($value['site_id']) && $value['site_id'] == $site_id ){
                            $key = $k;
                            break;
                        }
                    }
                    if($key !== false){
                        $values[$key] = array(
                            'site_id'   => $site_id,
                            'posts'     => $lists
                        );
                    }else{
                        $values[] = array(
                            'site_id'   => $site_id,
                            'posts'     => $lists
                        );
                    }
                }else{
                    $values[] = array(
                        'site_id'   => $site_id,
                        'posts'     => $lists
                    );
                }
            }

            else {
                $values[] = array(
                    'site_id'   => $site_id,
                    'posts'     => $lists
                );
            }

            @setcookie( $this->setting['cookie_name'], json_encode($values), time() + $this->setting['cookie_life'], '/' );
        }

        public static function get_count(){
            $lists = self::$data;
            if(is_array($lists)){
                return count($lists);
            }
            return 0;
        }

        public static function get_taxonomies( ){
            $attributes = array();
            if( function_exists( 'wc_get_attribute_taxonomies' ) && function_exists( 'wc_attribute_taxonomy_name' ) ) {
                $attribute_taxonomies = wc_get_attribute_taxonomies();
                if(!empty($attribute_taxonomies)){
                    foreach( $attribute_taxonomies as $attribute ) {
                        $tax = wc_attribute_taxonomy_name( $attribute->attribute_name );
                        $attributes[$tax] = ucfirst( wc_attribute_label( $tax ) );
                    }
                }
            }

            return $attributes;
        }

        public static function get_default_attributes(){
            return array(
                'image'         => esc_html__( 'Image', 'ciri' ),
                'title'         => esc_html__( 'Title', 'ciri' ),
                'add-to-cart'   => esc_html__( 'Add to cart', 'ciri' ),
                'price'         => esc_html__( 'Price', 'ciri' ),
                'sku'           => esc_html__( 'Sku', 'ciri' ),
                'description'   => esc_html__( 'Description', 'ciri' ),
                'stock'         => esc_html__( 'Availability', 'ciri' ),
                'weight'        => esc_html__( 'Weight', 'ciri' ),
                'dimensions'    => esc_html__( 'Dimensions', 'ciri' )
            );
        }

        public function output() {
            ob_start();
            if(function_exists('wc_print_notices')) {
                get_template_part('woocommerce/la_compare');
            }
            return ob_get_clean();
        }
    }
}

new Ciri_WooCommerce_Compare();