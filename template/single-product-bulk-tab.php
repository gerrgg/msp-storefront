<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product;

if( $product->is_type( 'variable' ) ){
    $child_vars = [];
    $children = $product->get_children();
    foreach( $children as $id ){
        $child = wc_get_product( $id );

        if( is_null( $child ) || $child == false ) return;

        if( $child->is_purchasable() ){
            $child_vars[$id] = array(
                'id' => $id,
                'sku' => $child->get_sku(),
                'attr_str' => wc_get_formatted_variation( $child->get_variation_attributes(), true, false, true ),
                'price' => number_format((float)$child->get_price(), 2, '.', ''),
                'instock' => $child->get_stock_status(),
                'stock' => $child->get_stock_quantity(),
            );
        }
    }
} else {
    return; // dont make tab
}

$image_src = msp_get_product_image_src( $product->get_image_id(), 'thumbnail' );

?>
<div class="tab-pane" id="bulk-tab-content" role="tabpanel" aria-labelledby="bulk-tab">
    <div id="var_bulk_form">
        <?php
        foreach( $child_vars as $var ){
            if( $var['instock'] == 'instock'  ){
                ?>
                    <div class="d-flex">
                        <input id="<?php echo $var['id'] ?>" type="number" placeholder="0" class="my-2 var-bulk-update qty" />
                        <div class="ml-5">
                            <span><?php echo $var['sku'], ' - ' . $var['attr_str'] ?></span><br>
                            <span><?php echo '<b>' . $var['stock'] . '</b>' ?> in stock</span>
                        </div>
                    </div>
                <?php
            }
        }
        ?>
    </div>
    <a href="#" id="iww_bulk_form_submit" class="single_add_to_cart_button btn btn-danger w-100 my-2">Bulk Add to Cart</a>
</div>
<?php


