<?php

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
    exit( 'Direct script access denied.' );
}

class Ciri_Admin {

    public function __construct(){
        $this->load_config();
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts') );
        add_action( 'customize_controls_enqueue_scripts', array( $this, 'customize_scripts') );
    }

    private function load_config(){
        require_once get_theme_file_path('/framework/configs/options.php');
        require_once get_theme_file_path('/framework/configs/metaboxes.php');
    }

    public function admin_scripts(  ){
        $ext = defined('WP_DEBUG') && WP_DEBUG ? '' : '.min';
	    $theme_version = defined('WP_DEBUG') && WP_DEBUG ? time() : CIRI_THEME_VERSION;
        wp_enqueue_style('ciri-admin-css', get_theme_file_uri( '/assets/css/admin'.$ext.'.css' ), null, $theme_version );
        $body_font_family = ciri_get_theme_mod('body_font_family');
        if(!empty($body_font_family)){
            wp_add_inline_style('ciri-admin-css', '.block-editor .editor-styles-wrapper .editor-block-list__block{ font-family: '.$body_font_family.' }');
        }
    }

    public function customize_scripts(){
	    $ext = defined('WP_DEBUG') && WP_DEBUG ? '' : '.min';
        $theme_version = defined('WP_DEBUG') && WP_DEBUG ? time() : CIRI_THEME_VERSION;
        $dependency = array(
            'jquery',
            'customize-base',
            'customize-controls',
        );
        wp_enqueue_script( 'ciri-customize-admin', get_theme_file_uri('/assets/js/customizer'.$ext.'.js'), $dependency, $theme_version, true );
    }

}