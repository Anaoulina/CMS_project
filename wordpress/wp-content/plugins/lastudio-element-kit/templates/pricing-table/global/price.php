<?php
/**
 * Pricing table price block template
 */
?>
<div class="lakit-pricing-table__price"><?php
    echo '<div class="lakit-pricing-table__price_wrap">';
	$this->_html( 'price_prefix', '<span class="lakit-pricing-table__price-prefix">%s</span>' );
	$this->_html( 'price', '<span class="lakit-pricing-table__price-val">%s</span>' );
	$this->_html( 'price_suffix', '<span class="lakit-pricing-table__price-suffix">%s</span>' );
    echo '</div>';
	$this->_html( 'price_desc', '<div class="lakit-pricing-table__price-desc">%s</div>' );
?></div>