<?php

defined( 'ABSPATH' ) || exit;

add_filter( 'woocommerce_get_availability_text', 'change_backorder_message', 10, 2 );

function change_backorder_message( $text, $product ){
    if ( $product->managing_stock() && $product->is_on_backorder( 1 ) ) {
        $text = __( "Out of stock and on backorder, we'll keep you updated", "msp" );
    }
    return $text;
}

function msp_add_bootstrap_cols_to_product( $class ){
    /**
     * adds bootstrap grid classes to all category and shop pages.
     */
    if( ! is_product() && ! is_cart() && ! is_front_page() && ! is_page( 'clearance' ) ){
        $class[] = 'col-6 col-sm-3 col-xl-2';
    }
    return $class;
}
add_filter('woocommerce_post_class', 'msp_add_bootstrap_cols_to_product', 30, 3);

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

//https://stackoverflow.com/questions/43564232/add-the-product-description-to-woocommerce-email-notifications
// Setting the email_is as a global variable
add_action('woocommerce_email_before_order_table', 'the_email_id_as_a_global', 1, 4);
function the_email_id_as_a_global($order, $sent_to_admin, $plain_text, $email ){
    $GLOBALS['email_id_str'] = $email->id;
}

// Displaying product description in new email notifications
add_action( 'woocommerce_order_item_meta_end', 'maybe_add_product_discontinued_in_new_email_notification', 10, 4 );
function maybe_add_product_discontinued_in_new_email_notification( $item_id, $item, $order = null, $plain_text = false ){

    // Getting the email ID global variable
    $refNameGlobalsVar = $GLOBALS;
    $email_id = $refNameGlobalsVar['email_id_str'];

    // If empty email ID we exit
    if(empty($email_id)) return;

    // Only for "New Order email notification"
    if ( 'new_order' == $email_id ) {

        if( version_compare( WC_VERSION, '3.0', '<' ) ) { 
            $product_id = $item['product_id']; // Get The product ID (for simple products)
            $product = wc_get_product($item['product_id']); 
        } else {
            $product = $item->get_product();
        }

        $is_discontinued = get_post_meta( $product->get_id(), 'msp_discontinued', true );
        $our_cost = get_post_meta( $product->get_id(), 'our_cost', true );

        if( $is_discontinued === 'yes' ){
            echo '<div class="product-discontinued"><p><strong>Product Discontinued</strong></p></div>';
        }

        if( ! empty( $our_cost ) ){
            echo '<div class="product-our-cost"><p><strong>Our Cost: </strong> $' . $our_cost . ' </p></div>';
        }
    }
}