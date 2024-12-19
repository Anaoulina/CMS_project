<?php
/**
 * Testimonials item template
 */


$preset = $this->get_settings( 'preset' );

$item_image = $this->_loop_item( array( 'item_image', 'url' ), '%s' );
$item_image = apply_filters('lakit_wp_get_attachment_image_url', $item_image);

$post_classes = ['lakit-testimonials__item', 'lakit-ttm__i'];
$post_classes[] = $this->_loop_item( array( 'el_class' ), '%s' );
$post_classes[] = $this->_loop_item( array( '_id' ), 'elementor-repeater-item-%s' );

if(filter_var( $this->get_settings_for_display('enable_carousel'), FILTER_VALIDATE_BOOLEAN )){
    $post_classes[] = 'swiper-slide';
}
else{
    $post_classes[] = lastudio_kit_helper()->col_new_classes('columns', $this->get_settings_for_display());
}

?>
<div class="<?php echo esc_attr(join(' ', $post_classes)); ?>">
    <?php
    if(!empty($item_image) && $preset === 'type-12'){
        echo '<div class="lakit-testimonials__figure lakit-ttm__fig">';
        do_action('lastudio-kit/testimonials/output/before_image', $preset);
        echo sprintf('<span class="lakit-testimonials__tag-img lakit-ttm__img"><span style="background-image: url(\'%1$s\')"></span></span>', esc_url($item_image) );
        do_action('lastudio-kit/testimonials/output/after_image', $preset);
        echo '</div>';
    }
    ?>
	<div class="lakit-testimonials__item-inner lakit-ttm__inner">
        <?php
            if( !empty($item_image) && $preset === 'type-13' ){
                echo '<div class="lakit-testimonials__figure lakit-ttm__fig">';
                do_action('lastudio-kit/testimonials/output/before_image', $preset);
                echo sprintf('<span class="lakit-testimonials__tag-img lakit-ttm__img"><span style="background-image: url(\'%1$s\')"></span></span>', esc_url($item_image) );
                do_action('lastudio-kit/testimonials/output/after_image', $preset);
                echo '</div>';
            }
        ?>
		<div class="lakit-testimonials__content lakit-ttm__cont"><?php
            if(!empty($item_image) && !in_array($preset, ['type-13', 'type-12'])){
                echo '<div class="lakit-testimonials__figure lakit-ttm__fig">';
                do_action('lastudio-kit/testimonials/output/before_image', $preset);
                echo sprintf('<span class="lakit-testimonials__tag-img lakit-ttm__img"><span style="background-image: url(\'%1$s\')"></span></span>', esc_url($item_image) );
                do_action('lastudio-kit/testimonials/output/after_image', $preset);
                echo '</div>';
            }
            if( $this->get_settings('use_title_field') === 'yes' ){
                echo $this->_loop_item( array( 'item_title' ), '<div class="lakit-testimonials__title lakit-ttm__t">%s</div>' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            }

            echo '<div class="lakit-testimonials__comment lakit-ttm__com">';
                if($preset === 'type-11'){
                    if($this->get_settings('replace_star')){
	                    $this->maybe_render_quote_icon();
                    }
                    else{
                        $item_rating = $this->_loop_item( array( 'item_rating' ), '%d' );
                        if(absint($item_rating)> 0){
                            $percentage =  (absint($item_rating) * 10) . '%';
                            echo '<div class="lakit-testimonials__rating lakit-ttm__r"><span class="star-rating"><span style="width: '.esc_attr($percentage).'"></span></span></div>';
                        }
                    }
                }
                echo $this->_loop_item( array( 'item_comment' ), '<div>%s</div>' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo '</div>';

            if( in_array($preset, ['type-10', 'type-11']) ){
                echo '<div class="lakit-testimonials__infowrap lakit-ttm__iw">';
                echo '<div class="lakit-testimonials__infowrap2 lakit-ttm__iw2">';
            }

            echo $this->_loop_item( array( 'item_name' ), '<div class="lakit-testimonials__name lakit-ttm__n"><span>%s</span></div>' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo $this->_loop_item( array( 'item_position' ), '<div class="lakit-testimonials__position lakit-ttm__p"><span>%s</span></div>' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

            if( in_array($preset, ['type-10', 'type-11']) ){
                echo '</div>';
            }
            if($preset !== 'type-11'){
                if($this->get_settings('replace_star')){
                    $this->maybe_render_quote_icon();
                }
                else{
                    $item_rating = $this->_loop_item( array( 'item_rating' ), '%d' );
                    if(absint($item_rating)> 0){
                        $percentage =  (absint($item_rating) * 10) . '%';
                        echo '<div class="lakit-testimonials__rating"><span class="star-rating"><span style="width: '.esc_attr($percentage).'"></span></span></div>';
                    }
                }
            }
            if(in_array($preset, ['type-10', 'type-11'])){
                echo '</div>';
            }
		?></div>
	</div>
</div>