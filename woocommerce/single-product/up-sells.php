<?php
/**
 * Single Product Up-Sells
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/up-sells.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @package 	WooCommerce/Templates
 * @version     3.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * If the editor does not set upsells; grab products from the same category
 */
if( empty( $upsells ) ){
	$category = wp_get_post_terms(get_the_ID(), 'product_cat', array('fields' => 'slugs'));
	if( isset( $category[0] ) ){
		$upsells = wc_get_products( array(
			'category' => array( $category[0] ),
		));
	}
}

if ( $upsells ) : ?>

	<section class="up-sells upsells products">

		<h2><?php esc_html_e( 'You may also like&hellip;', 'woocommerce' ); ?></h2>

        <?php woocommerce_product_loop_start(); ?>
        
        <div class="owl-carousel">

			<?php foreach ( $upsells as $upsell ) : ?>

				<?php
					$post_object = get_post( $upsell->get_id() );
					setup_postdata( $GLOBALS['post'] =& $post_object );
					wc_get_template_part( 'content', 'product' ); ?>

            <?php endforeach; ?>
            
        </div>

        <?php woocommerce_product_loop_end(); ?>

	</section>

<?php endif;
wp_reset_postdata();