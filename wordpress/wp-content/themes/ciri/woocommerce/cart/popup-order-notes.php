<?php
defined( 'ABSPATH' ) || exit;


$notes = WC()->session->get( 'lakit_order_notes', '' );

?>

<div id="popup-cart-order-notes" class="lakit-popup-template">
    <h4 class="lakit-popup--title"><?php esc_html_e( 'Add note for seller', 'ciri' ); ?></h4>
    <div class="lakit-popup--content">
        <form class="form-order-notes" method="POST">
            <?php ciri_wc_render_order_note_textarea(); ?>
            <div class="form-submit">
                <button type="submit" class="button"><span><?php esc_html_e( 'Save', 'ciri' ); ?></span></button>
                <a href="#" rel="nofollow" class="lakit-popup--close"><span><?php esc_html_e( 'Cancel', 'ciri' ) ?></span></a>
            </div>
        </form>
    </div>
</div>