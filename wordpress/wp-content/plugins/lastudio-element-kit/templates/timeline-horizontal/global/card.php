<?php
/**
 * Card item template
 */
$show_arrow = filter_var( $settings['show_card_arrows'], FILTER_VALIDATE_BOOLEAN );
$title_tag  = lastudio_kit_helper()->validate_html_tag($this->get_settings_for_display('item_title_size'))
?>

<div class="lakit-htimeline-item__card">
	<div class="lakit-htimeline-item__card-inner">
		<?php
        if( ! filter_var($this->get_settings_for_display('move_image_to_meta'), FILTER_VALIDATE_BOOLEAN) ) {
            $this->_render_image( $item_settings );
        }
		echo $this->_loop_item( array( 'item_title' ) , '<' . esc_attr($title_tag) .' class="lakit-htimeline-item__card-title">%s</' . esc_attr($title_tag) . '>' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->_loop_item( array( 'item_desc' ), '<div class="lakit-htimeline-item__card-desc">%s</div>' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
	</div>
	<?php if ( $show_arrow ) { ?>
		<div class="lakit-htimeline-item__card-arrow"></div>
	<?php } ?>
</div>
