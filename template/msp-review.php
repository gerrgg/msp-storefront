<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<div id="msp_review" class="row">
    <?php 
    /**
     * The msp_before_create_review_form hook.
     *
     * @hooked msp_review_more_products - 5
     */
    do_action( 'msp_before_create_review_form' );

    if( $_GET['action'] != 'create' ) return;

    /**
     * The msp_create_review_form hook.
     *
     * @hooked msp_create_review_wrapper_open - 1
     * @hooked msp_create_review_top - 5
     * @hooked msp_get_review_more_star_buttons - 10
     * @hooked msp_create_review_upload_form - 15
     * @hooked msp_create_review_headline - 20
     * @hooked msp_create_review_content - 25
     * @hooked msp_create_review_wrapper_close - 100
     */
    do_action( 'msp_create_review_form', $_GET['product_id'] ); 
    ?>
</div>
