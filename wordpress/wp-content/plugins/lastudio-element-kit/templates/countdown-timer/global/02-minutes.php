<div class="lakit-countdown-timer__item item-minutes">
	<div class="lakit-countdown-timer__item-value" data-value="minutes"><?php
        echo $this->date_placeholder(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
	<?php $this->_html( 'label_min', '<div class="lakit-countdown-timer__item-label">%s</div>' ); ?>
</div><?php echo $this->blocks_separator(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>