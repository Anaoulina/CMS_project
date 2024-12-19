<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$sidebar = apply_filters('ciri/filter/sidebar_primary_name', 'sidebar');

if(is_active_sidebar($sidebar)):
?>
    <aside class="sidebar-container widget-area">
        <div class="sidebar-inner">
            <?php dynamic_sidebar($sidebar); ?>
        </div>
    </aside>
    <?php
endif;