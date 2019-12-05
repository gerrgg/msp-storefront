<?php
/**
 * Single Product Price
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/price.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global $product;

?>

<p class="<?php echo esc_attr( apply_filters( 'woocommerce_product_price_class', 'price' ) );?>">
    <?php 
        if( $product->is_type( 'simple' ) && $product->is_on_sale() ) :
            echo msp_format_sale_price( $product->get_price(), $product->get_regular_price(), $product->get_sale_price() );
        else : 
            echo msp_get_price_html( $product );
        endif; 
    ?>
</p>