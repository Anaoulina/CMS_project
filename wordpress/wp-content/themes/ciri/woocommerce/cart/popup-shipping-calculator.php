<?php
defined( 'ABSPATH' ) || exit;

?>

<div id="popup-cart-shipping-calculator" class="lakit-popup-template">
    <h4 class="lakit-popup--title"><?php esc_html_e( 'Estimate shipping rates', 'ciri' ); ?></h4>
    <div class="lakit-popup--content">
        <?php wc_get_template( 'cart/shipping-calculator.php' ); ?>
        <div class="form-submit">
            <button type="button" class="button"><?php esc_html_e( 'Calculate shipping rates ', 'ciri' ); ?></button>
            <a href="#" rel="nofollow" class="lakit-popup--close"><span><?php esc_html_e( 'Cancel', 'ciri' ) ?></span></a>
        </div>
    </div>
</div>
