<?php
/**
 * Product loop sale flash
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/sale-flash.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global $post, $product;
?>
<div class="item-highlights">

    <?php if ( $product->is_featured() ) : ?>

        <?php msp_featured_item( ) ?>

    <?php endif;
    
    if ( msp_product_is_bogo( $product ) ) :

        msp_check_bogo_deal_badge( $product );

    endif;

echo '</div>';
/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */