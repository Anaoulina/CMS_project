<?php
/**
 * The template for displaying 404 pages (not found).
 *
 * @package Ciri
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<main class="site-main" role="main">
  <div class="default-404-content">
    <div class="container">
        <div class="lakit-row">
            <div class="lakit-col default-404-content--img">
                <img src="<?php echo esc_url(get_theme_file_uri( '/assets/images/404.svg' )); ?>" width="62" height="62" alt="<?php echo esc_attr_x('Page not found !!', 'front-end', 'ciri') ?>" loading="lazy"/>
            </div>
        </div>
        <div class="lakit-row">
            <div class="lakit-col default-404-content--content">
                <div class="default-404-content--inner">
                    <h4><?php echo esc_html_x('404. Page not found.', 'front-end', 'ciri') ?></h4>
                    <p><?php echo esc_html_x('Sorry, we couldn’t find the page you where looking for. We suggest that you return to homepage.', 'front-end', 'ciri'); ?></p>
                    <div class="button-wrapper"><a class="button" href="<?php echo esc_url(home_url('/')) ?>"><?php echo esc_html_x('Back to homepage', 'front-view','ciri')?></a></div>
                </div>
            </div>
        </div>
    </div>
  </div>
</main>
