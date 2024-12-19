<?php
/**
 * Timeline list item template
 */
$settings      = $this->get_settings_for_display();
$item_settings = $this->_processed_item;

$classes = array(
	'lakit-vtimeline-item',
	$settings['animate_cards'],
	'elementor-repeater-item-' . $item_settings['_id']
);

$item_meta_attr = $this->get_item_inline_editing_attributes( 'item_meta', 'cards_list', $this->_processed_item_index, 'lakit-vtimeline-item__meta-content' );
$item_title_attr = $this->get_item_inline_editing_attributes( 'item_title', 'cards_list', $this->_processed_item_index, 'lakit-vtimeline-item__card-title' );
$item_subtitle_attr = $this->get_item_inline_editing_attributes( 'item_subtitle', 'cards_list', $this->_processed_item_index, 'lakit-vtimeline-item__card-subtitle' );
$item_desc_attr = $this->get_item_inline_editing_attributes( 'item_desc', 'cards_list', $this->_processed_item_index, 'lakit-vtimeline-item__card-desc' );

$image_in_meta = filter_var($this->get_settings_for_display('image_in_meta'), FILTER_VALIDATE_BOOLEAN);

$classes = implode( ' ', $classes );
$this->_processed_item_index += 1;
?>
<div class="<?php echo esc_attr($classes) ?>">
	<div class="lakit-vtimeline-item__card">
		<div class="lakit-vtimeline-item__card-inner">
				<?php
                $this->_render_image( $item_settings );

				?>
				<div class="lakit-vtimeline-item__card-content">
					<?php
						echo '<div class="lakit-vtimeline-item__meta">';
						echo $this->_loop_item( array( 'item_meta' ), '<div ' . esc_attr($item_meta_attr) . '>%s</div>' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo '</div>';
						echo '<div class="lakit-vtimeline-item__card-content-inner">';
                        echo $this->_loop_item( array( 'item_subtitle' ) , '<div ' . esc_attr($item_subtitle_attr) . '>%1s</div>' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo $this->_loop_item( array( 'item_title' ) , '<h5 ' . esc_attr($item_title_attr) . '>%1s</h5>' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo $this->_loop_item( array( 'item_desc' ), '<div ' . esc_attr($item_desc_attr) . '>%s</div>' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        echo '</div>';
					?>
				</div>
		</div>
		<div class="lakit-vtimeline-item__card-arrow"></div>
	</div>
	<?php
		$this->_generate_point_content( $item_settings );
		echo '<div class="lakit-vtimeline-item__meta">';
		echo $this->_loop_item( array( 'item_meta' ), '<div ' . esc_attr($item_meta_attr) . '>%s</div>' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		if($image_in_meta){
            $this->_render_image( $item_settings );
        }
		echo '</div>';
	?>
</div>