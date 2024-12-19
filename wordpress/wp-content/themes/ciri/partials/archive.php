<?php
/**
 * The template for displaying archive pages.
 *
 * @package Ciri
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Default title is null
$page_title = NULL;
// Homepage - display blog description if not a static page
if ( is_front_page() && ! is_singular( 'page' ) ) {

    if ( get_bloginfo( 'description' ) ) {
        $page_title = get_bloginfo( 'description' );
    } else {
        $page_title = esc_html__( 'Recent Posts', 'ciri' );
    }

    // Homepage posts page
}
elseif ( is_home() && ! is_singular( 'page' ) ) {
    $page_title = get_the_title( get_option( 'page_for_posts', true ) );
}
else{
    $page_title = get_the_archive_title();
}

$page_description = get_the_archive_description();


?>
<main class="site-main" role="main">
	<?php if ( apply_filters( 'ciri/filter/enable_page_title', true ) && ( !empty($page_title)  || !empty( $page_description ) ) ) : ?>
		<header class="page-header page-header--default">
            <div class="container page-header-inner">
                <?php

                if(!empty($page_title)){
                    echo sprintf('<h1 class="entry-title">%1$s</h1>', $page_title);
                }

                if(!empty($page_description)){
                    echo sprintf('<div class="archive-description">%1$s</div>', $page_description);
                }

                ?>
            </div>
		</header>
	<?php endif; ?>

    <div id="site-content-wrap" class="container">
        <?php get_sidebar(); ?>
        <div class="site-content--default">
            <?php

            if ( have_posts() ) {

                echo '<div id="blog-entries">';

                // Loop through posts
                while ( have_posts() ) {

                    the_post();

                    get_template_part( 'partials/default/content', get_post_type() );

                }

                echo '</div>';

                // Display post pagination
	            the_posts_pagination([
		            'prev_text'    => __( '&laquo;', 'ciri' ),
		            'next_text'    => __( '&raquo;', 'ciri' ),
	            ]);

                wp_reset_postdata();

            }
            else{
                get_template_part( 'partials/default/none');
            }

            ?>
        </div>
    </div>
</main>
