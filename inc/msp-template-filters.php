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


add_filter( 'wc_add_to_cart_message_html', 'remove_add_to_cart_message' );
function remove_add_to_cart_message() {
    return;
}

add_filter( 'the_content', 'msp_maybe_add_tab_info' );
function msp_maybe_add_tab_info( $content ){
    /**
     * This filter grabs any additional information from yikes_product_tabs plugin.
     * I stopped using plugin.
     */

    if( is_product() ){
        global $product;
    
        $plugin_tabs = get_post_meta( $product->get_id(), 'yikes_woo_products_tabs' );

        if( ! empty( $plugin_tabs ) ){
            foreach( $plugin_tabs[0] as $tab ){
                $content .= '<h4 class="mb-2">'. $tab['title'] .'</h4>' . $tab['content'];
            }
        }
    }
    
    return $content;
}

add_filter( 'woocommerce_dropdown_variation_attribute_options_args', 'msp_add_form_control_to_select_boxes', 100 );
// Add class .form-control to default class for select tags
function msp_add_form_control_to_select_boxes( $args ){
    $args['class'] = 'form-control';
    return $args;
}


add_filter( 'woocommerce_product_price_class', 'msp_product_price_class', 10 );
function msp_product_price_class( $class ){
    return $class . ' my-1';
}

function msp_the_title( $title, $id = null ) {

    $seo_name = get_post_meta( $id, 'gsf_title', true );
    $title = ( ! empty( $seo_name ) ) ? $title . ' ' . $seo_name : $title;

    return $title;
}
add_filter( 'the_title', 'msp_the_title', 10, 2 );


