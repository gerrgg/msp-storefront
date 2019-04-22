<?php
$product = wc_get_product( get_query_var('msp_product_id') );
$children = $product->get_children();
$children_product_arr = array();

if( ! empty( $children ) ){
    foreach( $children as $id ){
        $child = wc_get_product( $id );
        $children_product_arr[$id] = array(
            'sku' => $child->get_sku(),
            'price' => '$' . $child->get_price(),
            'attr' => wc_get_formatted_variation( $child->get_variation_attributes(), true, false, true ),
        );
    }
}

?>
<div id="msp-quote" class="row">
    <div class="col-4">
        <?php echo $product->get_image(); ?>
    </div>
    <div class="col">
        <h3><?php echo $product->get_name() ?></h3>
        <p class="lead border-bottom">
            <label class="price">Price: </label>
            <span class="price">$<?php echo $product->get_price(); ?></span>
        </p>
        <form method="POST" action="<?php echo admin_url( 'admin-post.php' ) ?>">
            <?php 
            if( ! empty( $children_product_arr ) ){
                echo '<table class="table">';
                echo '<thead class="thead-dark"><th>Product</th><th>Quantity</th></thead>';
                foreach( $children_product_arr as $key => $child_product ){
                    echo '<tr>';
                    echo '<td>';
                    ?> 
                    <p class="m-0 p-0"><b><?php echo $child_product['attr'] ?></b></p>
                    <p class="m-0 p-0">SKU: <?php echo $child_product['sku'] ?></p>
                    <p class="m-0 p-0 price"><?php echo $child_product['price'] ?></p>
                    <?php
                    echo '</td>';
                    echo '<td><input type="number" id="'. $key .'-qty" name="product['. $key .']" /></td>';
                    echo '</tr>';
                }
                echo '</table>';
            } else {
                ?>
                <div class="form-group">
                    <label>How many? </label>
                    <input class="form-control" type="tel" name="product[<?php echo $product->get_id() ?>]" min="0" />
                </div>
                <?php
            }
            echo '<hr />';
            echo '<h4>Where are we shipping it?</h4>';
            ?>
            <div class="form-group">
                <label for="street">Street</label>
                <input id="street" name="street" class="form-control" type="text">
            </div>

            <div class="form-group">
                <label for="street">Zip</label>
                <input id="street" name="zip" class="form-control" type="text">
            </div>
            
            <div class="form-group">
                <label for="email">Your email</label>
                <input id="email" name="email" class="form-control" type="email">
            </div>

            <input type="hidden" name="action" value="msp_submit_bulk_form" />
            <button type="submit" class="btn btn-danger">Submit quote request</button>
        </form>
    </div>
</div>
