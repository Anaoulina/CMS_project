<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme and one of the
 * two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * For example, it puts together the home page when no home.php file exists.
 *
 * Learn more: https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Ciri WordPress theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
get_header();


$is_elementor_theme_exist = function_exists( 'elementor_theme_do_location' );

if ( is_singular() ) {
    if ( ! $is_elementor_theme_exist || ! elementor_theme_do_location( 'single' ) ) {
        get_template_part( 'partials/single' );
    }
} elseif ( is_archive() || is_home() ) {
    if ( ! $is_elementor_theme_exist || ! elementor_theme_do_location( 'archive' ) ) {
        get_template_part( 'partials/archive' );
    }
} elseif ( is_search() ) {
    if ( ! $is_elementor_theme_exist || ! elementor_theme_do_location( 'archive' ) ) {
        get_template_part( 'partials/search' );
    }
} else {
    if ( ! $is_elementor_theme_exist || ! elementor_theme_do_location( 'single' ) ) {
        get_template_part( 'partials/404' );
    }
}

get_footer();