<?php

defined( 'ABSPATH' ) || exit;

global $product;

// Ensure visibility.
if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}
?>
<div class="card buy-again-product">
    <a class="link-normal" href="<?php echo $product->get_permalink(); ?>">
        <?php echo $product->get_image( 'woocommerce_thumbnail', array( 'class' => 'card-img-top' ) ) ?>
        <div class="card-body">
            <?php echo wc_get_rating_html( $product->get_average_rating(), $product->get_review_count() ) ?>
            <h5><?php echo $product->get_name(); ?></h5>
            <p><?php echo $product->get_price_html() ?></p>
            <?php woocommerce_template_loop_add_to_cart(); ?>
        </div>
    </a>
</div>
<?php

