<?php
/**
 * The shop sidebar
 *
 * @package storefront
 */
if ( ! is_active_sidebar( 'sidebar-msp-shop' ) ) {
	return;
}
?>

<div id="shop-filters" class="border-right pr-2" role="complementary">
	<?php dynamic_sidebar( 'sidebar-msp-shop' ); ?>
</div><!-- #secondary -->