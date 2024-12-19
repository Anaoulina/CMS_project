<?php
/**
 * Pricing table heading template
 */
?>
<div class="lakit-pricing-table__heading">
    <?php
    if( $this->get_settings_for_display('icon_type') === 'image'){
        echo $this->__generate_icon_image(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
    else{
        echo $this->__generate_icon(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
    ?>
	<?php $this->_html( 'title', '<h3 class="lakit-pricing-table__title">%s</h3>' ); ?>
	<?php $this->_html( 'subtitle', '<div class="lakit-pricing-table__subtitle">%s</div>' ); ?>
</div>