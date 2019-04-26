<?php 

defined( 'ABSPATH' ) || exit;

function msp_get_product_image_src( $img_id ){
    $src = wp_get_attachment_image_src( $img_id );
    return $src[0];
}

function msp_get_product_image_src_by_product_id( $product_id ){
    $product = wc_get_product( $product_id );
    $product_image_id = ( ! empty( $product ) ) ? $product->get_image_id() : 0;
    
    return ( ! empty( $product_image_id ) ) ? msp_get_product_image_src( $product_image_id ) : null;
}


function deslugify( $str ){
    return ucwords( str_replace( array('_', '-'), ' ', $str ) );
}