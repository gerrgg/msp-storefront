<?php

defined( 'ABSPATH' ) || exit;

function msp_add_bootstrap_cols_to_product(){
    return ( is_archive() ) ? array('col-6', 'col-sm-4', 'col-md-2') : array();
}
add_filter('post_class', 'msp_add_bootstrap_cols_to_product', 30, 3);

function msp_woocommerce_product_loop_start(){
    return '<div id="msp-archive" class="row">';
}
add_filter('woocommerce_product_loop_start', 'msp_woocommerce_product_loop_start', 999);

function msp_woocommerce_product_loop_end(){
    return '</div>';
}
add_filter('woocommerce_product_loop_end', 'msp_woocommerce_product_loop_end', 999);

function msp_products_per_page(){
    return 30;
}
add_filter('loop_shop_per_page', 'msp_products_per_page', 999);