<div class="lakit-countdown-timer__item item-seconds">
	<div class="lakit-countdown-timer__item-value" data-value="seconds"><?php echo $this->date_placeholder(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
	<?php $this->_html( 'label_sec', '<div class="lakit-countdown-timer__item-label">%s</div>' ); ?>
</div>