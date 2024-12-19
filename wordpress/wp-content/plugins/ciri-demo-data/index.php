<?php
/*
Plugin Name:    Ciri Package Demo Data
Plugin URI:     http://la-studioweb.com/
Description:    This plugin use only for LA-Studio Theme
Author:         LA Studio
Author URI:     http://la-studioweb.com/
Version:        1.0.0
Text Domain:    lastudio-demodata
*/

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
    exit( 'Direct script access denied.' );
}

if(!function_exists('la_import_check_post_exists')){
    function la_import_check_post_exists( $title, $content = '', $date = '', $type = '' ){
        global $wpdb;

        $post_title = wp_unslash( sanitize_post_field( 'post_title', $title, 0, 'db' ) );
        $post_content = wp_unslash( sanitize_post_field( 'post_content', $content, 0, 'db' ) );
        $post_date = wp_unslash( sanitize_post_field( 'post_date', $date, 0, 'db' ) );
        $post_type = wp_unslash( sanitize_post_field( 'post_type', $type, 0, 'db' ) );

        $query = "SELECT ID FROM $wpdb->posts WHERE 1=1";
        $args = array();

        if ( !empty ( $date ) ) {
            $query .= ' AND post_date = %s';
            $args[] = $post_date;
        }

        if ( !empty ( $title ) ) {
            $query .= ' AND post_title = %s';
            $args[] = $post_title;
        }

        if ( !empty ( $content ) ) {
            $query .= ' AND post_content = %s';
            $args[] = $post_content;
        }

        if ( !empty ( $type ) ) {
            $query .= ' AND post_type = %s';
            $args[] = $post_type;
        }

        if ( !empty ( $args ) )
            return (int) $wpdb->get_var( $wpdb->prepare($query, $args) );

        return 0;
    }
}

if(!function_exists('la_import_get_post_id_by_slug')){
    function la_import_get_post_id_by_slug( $slug, $post_type ){
        global $wpdb;
        $post_name = wp_unslash( sanitize_post_field( 'post_name', $slug, 0, 'db' ) );
        $object_type = wp_unslash( sanitize_post_field( 'post_type', $post_type, 0, 'db' ) );
        $query = "SELECT ID FROM $wpdb->posts WHERE 1=1";
        $args = array();
        if ( !empty ( $post_name ) ) {
            $query .= ' AND post_name = %s';
            $args[] = $post_name;
        }
        if ( !empty ( $object_type ) ) {
            $query .= ' AND post_type = %s';
            $args[] = $object_type;
        }

        if ( !empty ( $args ) ){
            return (int) $wpdb->get_var( $wpdb->prepare($query, $args) );
        }
        return 0;
    }
}


class Ciri_Data_Demo_Plugin_Class{

    public static $plugin_dir_path = null;

    public static $plugin_dir_url = null;

    public static $instance = null;

    public static $theme_name = 'ciri';

    public static $demo_site = 'https://ciri.la-studioweb.com/';

    protected $demo_data = array();

    public static function get_instance() {
        if ( null === static::$instance ) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    protected function __construct( ) {

        self::$plugin_dir_path = plugin_dir_path(__FILE__);

        self::$plugin_dir_url = plugin_dir_url(__FILE__);

        include_once self::$plugin_dir_path . 'demodata.php';

        $this->_setup_demo_data();

        $this->load_importer();

        add_filter(self::$theme_name . '/filter/demo_data', array( $this, 'get_data_for_import_demo') );

        add_action( 'init', array( $this, 'register_menu_import_demo'), 99 );
    }

    private function load_importer(){
        require_once self::$plugin_dir_path . 'importer.php';
        if(class_exists('LaStudio_Importer')){
            new LaStudio_Importer(self::$theme_name, $this->get_data_for_import_demo(), self::$demo_site );
        }
    }


    public function register_menu_import_demo(){
        require_once self::$plugin_dir_path . 'panel.php';

        if(self::isLocal()){
            require_once self::$plugin_dir_path . 'export.php';
            new LaStudio_Export_Demo();
        }
    }

    public function get_data_for_import_demo(){
        $demo = (array) $this->filter_demo_item_by_category('demo');
        return $demo;
    }

    private function _setup_demo_data(){

        $func_name = 'la_'. self::$theme_name .'_get_demo_array';

        $this->demo_data = call_user_func( $func_name, self::$plugin_dir_url . 'previews/', self::$plugin_dir_path . 'data/');
    }

    public static function isLocal(){
        $is_local = false;
        if (isset($_SERVER['X_FORWARDED_HOST']) && !empty($_SERVER['X_FORWARDED_HOST'])) {
            $hostname = $_SERVER['X_FORWARDED_HOST'];
        } else {
            $hostname = $_SERVER['HTTP_HOST'];
        }
        if ( strpos($hostname, '.la-studioweb.com') !== false || strpos($hostname, '.la-studio.io') !== false || strpos($hostname, 'localhost') !== false ) {
            $is_local = true;
        }
        return $is_local;
    }

    public function get_demo_data(){
        return $this->demo_data;
    }

    public function filter_demo_item_by_category( $category ){
        $demo_data = (array) $this->demo_data;
        $return = array();
        if(!empty($demo_data) && !empty($category)){
            foreach( $demo_data as $key => $demo ){
                if(!empty($demo['category'])){
                    $demo_category = array_map('strtolower', $demo['category']);
                    if(in_array(strtolower($category), $demo_category)){
                        $return[$key] = $demo;
                    }
                }
            }
        }
        return $return;
    }

}

add_action('plugins_loaded', function(){

    $theme = wp_get_theme();

    if(strpos(strtolower($theme->get_template()), 'ciri') === false){

        add_action( 'admin_notices', function(){
            printf( __( '%1$s"Ciri Package Demo Data" requires %3$s"Ciri"%4$s theme to be installed and activated. Please active %3$s"Ciri"%4$s to continue.%2$s', 'lastudio-demodata' ), '<div class="error"><p>', '</p></div>' ,'<strong>', '</strong>' );
        });

        add_action( 'admin_init', function(){
            deactivate_plugins( plugin_basename( __FILE__ ) );
        });

        return;
    }

    Ciri_Data_Demo_Plugin_Class::get_instance();
}, 999);