<?php
defined( 'ABSPATH' ) || exit;

?>

<div id="popup-cart-coupon" class="lakit-popup-template">
    <h4 class="lakit-popup--title"><?php esc_html_e( 'Select or input Coupon', 'ciri' ); ?></h4>
    <div class="lakit-popup--content">
        <form class="form-coupon" method="POST">
            <p class="form-description"><?php esc_html_e( 'If you have a coupon code, please apply it below.', 'ciri' ); ?></p>

            <div class="form-row">
                <input type="text" name="coupon_code" class="input-text" value="" required placeholder="<?php esc_attr_e( 'Coupon code', 'ciri' ); ?>"/>
            </div>

            <?php do_action( 'woocommerce_cart_coupon' ); ?>

            <div class="form-submit">
                <button type="submit" class="button" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'ciri' ); ?>"><span><?php esc_html_e( 'Apply coupon', 'ciri' ); ?></span></button>
                <a href="#" rel="nofollow" class="lakit-popup--close"><span><?php esc_html_e( 'Cancel', 'ciri' ) ?></span></a>
            </div>
        </form>
    </div>
</div>
