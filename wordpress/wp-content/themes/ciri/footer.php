<?php
/**
 * The template for displaying the footer.
 * @package Ciri WordPress theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'footer' ) ) {
    get_template_part('partials/default/footer');
}

?>


<div class="la-overlay-global"></div>

<?php wp_footer(); ?>
</body>
</html>