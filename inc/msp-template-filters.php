<?php

defined( 'ABSPATH' ) || exit;


function msp_add_bootstrap_cols_to_product(){
    /**
     * adds bootstrap grid classes to all category and shop pages.
     */
    return ( is_product_category() || is_shop() ) ? array('col-6', 'col-sm-3', 'col-xl-2') : array();
}
add_filter('post_class', 'msp_add_bootstrap_cols_to_product', 30, 3);

function msp_woocommerce_product_loop_start(){
    /**
     * opens up any category and shop pages as a boostrap row.
     */
    return '<div id="msp-archive" class="row">';
}
add_filter('woocommerce_product_loop_start', 'msp_woocommerce_product_loop_start', 999);

function msp_woocommerce_product_loop_end(){
    /**
     * closes up any category and shop pages
     */
    return '</div>';
}
add_filter('woocommerce_product_loop_end', 'msp_woocommerce_product_loop_end', 999);

function msp_products_per_page(){
    /**
     * changes the default per_page value;
     */
    return 30;
}
add_filter('loop_shop_per_page', 'msp_products_per_page', 999);