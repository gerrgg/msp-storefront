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

add_filter( 'the_content', 'msp_maybe_add_tab_info', 50 );
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

/**
 * Allow HTML in term (category, tag) descriptions
 */
foreach ( array( 'pre_term_description' ) as $filter ) {
	remove_filter( $filter, 'wp_filter_kses' );
	if ( ! current_user_can( 'unfiltered_html' ) ) {
		add_filter( $filter, 'wp_filter_post_kses' );
	}
}
 
foreach ( array( 'term_description' ) as $filter ) {
	remove_filter( $filter, 'wp_kses_data' );
}

add_filter( 'the_content', 'msp_maybe_category_description', 49 );

function msp_maybe_category_description( $content ){
    /**
     * Get current taxonomy, get description and put into content. Order by parent.
     */

     // must be product or breaks pages / posts
    if( is_product() ){
        global $product;
    
        $terms = get_terms( array(
            'object_ids' => $product->get_id(), 
            'taxonomy' => 'product_cat',
            'orderby' => 'parent',
            'order' => 'ASC'
        ));

        foreach( $terms as $term ){
            if( ! is_wp_error( $term ) && ! empty( $term->description ) ){
                $content .= sprintf( "<h4>%s</h4>%s", $term->name, $term->description );
            }
        }
    
    }

    // must always return content (leave outside if ^^)
    return $content;
}

add_filter( 'the_content', 'msp_maybe_attribute_description', 50 );

function msp_maybe_attribute_description( $content ){
    /**
     * Gets product attribute descriptions and displays them in content.
     */

    $arr = array();
    $html = "";

    if( is_product() ){
        global $product;
        // get attributes that are visible and not variations
        $visible_attributes = msp_get_visible_non_variable_product_attributes( $product );

        foreach( $visible_attributes as $attribute ){
            $term = wc_get_product_terms( $product->get_id(), $attribute->get_name(), array( 'all' ) );

            if( ! is_wp_error( $term ) & isset( $term[0] ) ){
                // clean attribute slug and capitalize
                $taxonomy_name = ucfirst(str_replace('pa_', '', $term[0]->taxonomy ));

                // init strings
                $term_name = $term_description = "";

                for( $i = 0; $i < sizeof( $term ); $i++  ){
                    // only use attributes with descriptions
                    if( ! empty( $term[$i]->description ) ){
                        $term_name .= $term[$i]->name . ', ';
                        $term_description .= sprintf( "<div>%s</div>", $term[$i]->description );
                    }
                }

                if( ! empty( $term_description ) ){
                    $html .= sprintf( "<h4>%s - %s</h4>%s", $taxonomy_name, rtrim($term_name, ", "), $term_description );
                }
            }
        }
        $content .= $html;
       
    }
    
    return $content;
}

function msp_get_visible_non_variable_product_attributes( $product ){
    /**
     * Returns an array of product attributes that are visible but not used as a variation.
     * @param WC_Product
     * @return Array of WC_Product_Attribute
     */

    $arr = array();

    foreach( $product->get_attributes() as $attribute ){

        // If attribute is visible but not used for variations.
        if( $attribute->get_visible() && ! $attribute->get_variation() ){
            array_push( $arr, $attribute );
        }
    }

    return $arr;
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
    // Only for "New Order email notification"
    if ( 'new_order' == $GLOBALS['email_id_str'] ) {

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

        // if( ! empty( $our_cost ) ){
        //     echo '<div class="product-our-cost"><p><strong>Our Cost: </strong> $' . $our_cost . ' </p></div>';
        // }
    }
}