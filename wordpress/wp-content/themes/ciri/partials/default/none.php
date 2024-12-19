<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

?>
<div id="blog-entries">
    <article class="not-found-search">
        <div class="entry-content">
            <div class="entry not-found-search"><p class="text-center"><?php echo esc_html_x( 'It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'front-view', 'ciri' ); ?></p></div>
            <?php get_search_form(); ?>
        </div><!-- .entry-content -->
    </article><!-- #post-## -->
</div>