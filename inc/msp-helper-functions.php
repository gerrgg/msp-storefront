<?php 

defined( 'ABSPATH' ) || exit;

function msp_get_product_image_src( $img_id ){
    $src = wp_get_attachment_image_src( $img_id );
    return $src[0];
}

function deslugify( $str ){
    return ucwords( str_replace( array('_', '-'), ' ', $str ) );
}