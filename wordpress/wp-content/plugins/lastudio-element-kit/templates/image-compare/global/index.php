<?php
/**
 * Image Compare template
 */
$box_classes = ['lakit-image-compare'];

$this->add_render_attribute('wrapper', 'class', $box_classes);
$this->add_render_attribute('wrapper', 'data-settings', $this->_get_js_settings());
?>
<div <?php $this->print_render_attribute_string('wrapper') ?>>
    <?php
    echo $this->_get_image_before(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo $this->_get_image_after(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    ?>
</div>