<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $product;

?>
<div class="la-threesixty-container">
    <div data-la_component="WooThreeSixty" class="js-el la-threesixty threesixty" data-woothreesixty_vars="<?php echo esc_attr(json_encode($la_threesixty_vars)) ?>">
        <div class="spinner"><span>0%</span></div>
        <ol class="threesixty_images"></ol>
    </div>
</div>