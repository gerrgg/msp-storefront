<?php

defined( 'ABSPATH' ) || exit;

/** ARCHIVE - SHOP PAGE - TAXONOMY  */
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
add_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );

/**
 * init - maybe move to functions.php
 */
add_action( 'init', 'msp_remove_actions' );
function msp_remove_actions(){
    remove_action( 'storefront_before_content', 'woocommerce_breadcrumb', 10);
    remove_action( 'storefront_footer', 'storefront_credit', 20 );
    
    //http://gerrg.com/how-to-remove_action-woocommerce-upsell-related-items-hook-with-storefront-theme/
    remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
    remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
    remove_action( 'woocommerce_after_single_product_summary', 'storefront_upsell_display', 15 );
    remove_action( 'storefront_sidebar', 'storefront_get_sidebar', 10 );
}



/**
 * storefront_after_footer
 * @see msp_shameless_self_plug - 5
 */
add_action( 'storefront_after_footer', 'msp_add_copyright', 1 );
add_action( 'storefront_after_footer', 'msp_shameless_self_plug', 5 );
add_action( 'storefront_after_footer', 'msp_dynamic_modal', 10 );

/**
 * msp_header.
 * 
 * @see msp_header_wrapper_open();
 * @see msp_header_mobile_menu_button();
 * @see msp_header_site_identity();
 * @see msp_header_middle_open();
 * @see msp_header_search_bar();
 * @see msp_header_menu();
 * @see msp_header_middle_close();
 * @see msp_header_wrapper_close();
 */

add_action( 'msp_header', 'msp_header_wrapper_open', 0 );
add_action( 'msp_header', 'msp_header_mobile_menu_button', 1 );
add_action( 'msp_header', 'msp_header_site_identity', 5 );
add_action( 'msp_header', 'msp_header_middle_open', 10 );
add_action( 'msp_header', 'msp_header_search_bar', 15 );
add_action( 'msp_header', 'msp_header_menu', 20 );
add_action( 'msp_header', 'msp_header_middle_close', 25 );
add_action( 'msp_header', 'msp_header_wrapper_close', 100 );

/**
 * storefront_before_site.
 * 
 * @see msp_mobile_menu_wrapper_open();
 * @see msp_mobile_menu_header();
 * @see msp_mobile_menu();
 * @see msp_mobile_menu_account_links();
 * @see msp_mobile_menu_wrapper_close();
 */
add_action( 'storefront_before_site', 'msp_mobile_menu_wrapper_open', 0 );
add_action( 'storefront_before_site', 'msp_mobile_menu_header', 5 );
add_action( 'storefront_before_site', 'msp_mobile_menu', 50 );
add_action( 'storefront_before_site', 'msp_mobile_menu_account_links', 55 );
add_action( 'storefront_before_site', 'msp_mobile_menu_wrapper_close', 100 );

/**
 * msp_quick_links
 * 
 * @see msp_quick_links();
 * msp_buy_again_btn();
 * msp_get_user_products_history_btn();
 * msp_quote_btn();
 * msp_contact_btn();
 * msp_quick_links_wrapper_close();
 */
add_action( 'msp_quick_links', 'msp_quick_links_wrapper_open', 1 );
add_action( 'msp_quick_links', 'msp_shop_btn', 2 );
// add_action( 'msp_quick_links', 'msp_buy_again_btn', 5 );
add_action( 'msp_quick_links', 'msp_get_user_products_history_btn', 10 );
add_action( 'msp_quick_links', 'msp_quote_btn', 15 );
add_action( 'msp_quick_links', 'msp_contact_btn', 95 );
add_action( 'msp_quick_links', 'msp_quick_links_wrapper_close', 100 );

/**
 * msp_order_details
 * 
 * @see msp_order_details_html();
 */
add_action( 'msp_my_order_details', 'msp_order_details_html', 1, 1 );
add_action( 'msp_my_order_details', 'msp_update_order_tracking', 2, 1 );


/**
 * woocommerce_checkout_order_processed
 * 
 * @see msp_update_order_estimated_delivery();
 * @see commerce_connector_tracking();
 */

add_action( 'woocommerce_thankyou', 'msp_update_order_estimated_delivery', 5, 1 );
add_action( 'woocommerce_thankyou', 'commerce_connector_tracking', 10, 1 );
add_action( 'woocommerce_thankyou', 'msp_add_gmc_conversion_code', 15, 1 );
add_action( 'woocommerce_thankyou', 'cheque_payment_method_order_status_to_processing', 20, 1 );

/**
 * AJAX
 */
add_action( 'wp_ajax_msp_set_estimated_delivery_date', 'msp_set_estimated_delivery_date' );
add_action( 'wp_ajax_nopriv_msp_set_estimated_delivery_date', 'msp_set_estimated_delivery_date' );

add_action( 'wp_ajax_msp_get_product_size_guide_src', 'msp_get_product_size_guide_src' );
add_action( 'wp_ajax_nopriv_msp_get_product_size_guide_src', 'msp_get_product_size_guide_src' );

add_action( 'wp_ajax_msp_delete_specification', 'msp_delete_specification' );
add_action( 'wp_ajax_nopriv_msp_delete_specification', 'msp_delete_specification' );

add_action( 'wp_ajax_msp_get_image_src', 'msp_get_image_src' );
add_action( 'wp_ajax_nopriv_msp_get_image_src', 'msp_get_image_src' );

add_action( 'wp_ajax_msp_get_promo_pop_up_link_and_image', 'msp_get_promo_pop_up_link_and_image' );
add_action( 'wp_ajax_nopriv_msp_get_promo_pop_up_link_and_image', 'msp_get_promo_pop_up_link_and_image' );

add_action( 'wp_ajax_msp_get_leave_feedback_form', 'msp_get_leave_feedback_form' );
add_action( 'wp_ajax_nopriv_msp_get_leave_feedback_form', 'msp_get_leave_feedback_form' );

add_action( 'wp_ajax_msp_process_feedback_form', 'msp_process_feedback_form' );
add_action( 'wp_ajax_nopriv_msp_process_feedback_form', 'msp_process_feedback_form' );

add_action( 'wp_ajax_msp_process_customer_submit_question', 'msp_process_customer_submit_question' );
add_action( 'wp_ajax_nopriv_msp_process_customer_submit_question', 'msp_process_customer_submit_question' );

add_action( 'wp_ajax_msp_process_customer_submit_awnser', 'msp_process_customer_submit_awnser' );
add_action( 'wp_ajax_nopriv_msp_process_customer_submit_awnser', 'msp_process_customer_submit_awnser' );

add_action( 'wp_ajax_msp_get_products', 'msp_get_products' );
add_action( 'wp_ajax_nopriv_msp_get_products', 'msp_get_products' );

add_action( 'wp_ajax_msp_get_variation_price_html', 'msp_get_variation_price_html' );
add_action( 'wp_ajax_nopriv_msp_get_variation_price_html', 'msp_get_variation_price_html' );

add_filter( 'woocommerce_get_price_html', 'msp_get_price_html', 100, 2 );
add_filter( 'woocommerce_cart_item_price', 'msp_get_cart_item_price_html', 100, 3 );

function msp_get_price_html( $price, $product ){
    $qty = msp_get_product_unit_price( $product );
    return $price . $qty;
}

function msp_get_cart_item_price_html( $wc,  $cart_item,  $cart_item_key ){
    $id = ( $cart_item['variation_id'] === 0 ) ? $cart_item["product_id"] : $cart_item['variation_id'];
    $product = wc_get_product( $id );
    return $product->get_price_html();
}

/**
 * Admin Post
 */

 //bulk form - /quote
add_action( 'admin_post_msp_submit_bulk_form', 'msp_submit_bulk_form' );
add_action( 'admin_post_nopriv_msp_submit_bulk_form', 'msp_submit_bulk_form' );

//create_review - /review
add_action( 'admin_post_msp_process_create_review', 'msp_process_create_review' );
add_action( 'admin_post_nopriv_msp_process_create_review', 'msp_process_create_review' );

//contact_form - /contact
add_action( 'wp_ajax_msp_process_contact_form', 'msp_process_contact_form' );
add_action( 'wp_ajax_nopriv_msp_process_contact_form', 'msp_process_contact_form' );


/**
 * woocommerce_after_single_product_summary
 * @see comments_template();
 */
add_action( 'woocommerce_after_single_product_summary', 'msp_get_product_videos_tab', 0 );
add_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 5 );
// add_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 5);

/**
 * woocommerce_single_product_summary
 * @see msp_show_product_size_guide - 25
 */
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );

add_action( 'woocommerce_single_product_summary', 'msp_brand_name', 1 );
add_action( 'woocommerce_single_product_summary', 'msp_add_tabs', 12 );
add_action( 'woocommerce_single_product_summary', 'msp_open_single_product_tabs', 13 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 25 );
add_action( 'woocommerce_single_product_summary', 'msp_check_bogo_deal', 27 );
add_action( 'woocommerce_single_product_summary', 'msp_warn_about_leadtime', 29 );
add_action( 'msp_before_size_attribute', 'msp_show_product_size_guide_btn', 30 );
add_action( 'woocommerce_single_product_summary', 'msp_bulk_discount_table', 35 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 36);
add_action( 'woocommerce_single_product_summary', 'msp_close_order_tab_content_tag', 50 );
add_action( 'woocommerce_single_product_summary', 'msp_add_bulk_tab', 100 );
add_action( 'woocommerce_single_product_summary', 'msp_add_quote_tab', 101 );
add_action( 'woocommerce_single_product_summary', 'msp_close_single_product_tabs', 9999 );


if( get_option( 'wc_add_net_30_to_single_product' ) )
    add_action( 'woocommerce_single_product_summary', 'add_net_30', 37 );

/**
 * woocommerce_product_additional_information
 * @see msp_add_to_product_additional_information - 50
 */
add_action( 'woocommerce_product_additional_information', 'msp_get_additional_information', 5, 1 );
add_action( 'woocommerce_product_additional_information', 'msp_product_specification_html', 10, 1 );

add_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
add_action( 'woocommerce_before_shop_loop', 'msp_mobile_product_filter_button', 5 );


/**
 * woocommerce_archive_description
 */

remove_action( 'woocommerce_archive_description',  'woocommerce_taxonomy_archive_description', 10 );
remove_action( 'woocommerce_archive_description',  'woocommerce_product_archive_description', 10 );

add_action( 'woocommerce_after_shop_loop',  'msp_archive_description_header', 45 );
add_action( 'woocommerce_after_shop_loop',  'woocommerce_taxonomy_archive_description', 50 );
add_action( 'woocommerce_after_shop_loop',  'woocommerce_product_archive_description', 50 );

add_action( 'woocommerce_archive_description', 'woocommerce_breadcrumb', 5 );
add_action( 'woocommerce_archive_description', 'msp_add_sub_cat_links', 1 );
add_action( 'woocommerce_archive_description', 'msp_add_category_images', 2 );

add_action( 'woocommerce_review_order_before_payment', 'msp_add_payment_heading' );
function msp_add_payment_heading(){
    echo "<h3 class='payment-method-header'>Select your payment option</h3>";
}

/**
 * woocommerce_before_shop_loop_item_title
 * @see we do this to wrap a product image in an a tag - CSS
 */
add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_link_close', 15 );

/**
 * woocommerce_before_shop_loop_item_title
 * @see we do this to wrap the rest of the product in another tag - CSS
 */
add_action( 'woocommerce_shop_loop_item_title', 'msp_template_loop_product_link_open', 5 );
add_action( 'woocommerce_before_shop_loop_item_title', 'msp_brand_name', 15 );

/**
 * woocommerce_after_shop_loop_item
 */
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );

/**
 * woocommerce_before_single_product_summary
 */
add_action( 'woocommerce_before_single_product_summary', 'woocommerce_breadcrumb', 5 );
remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
add_action( 'woocommerce_single_product_summary', 'msp_featured_item', 11 );

add_action( 'storefront_before_content', 'msp_get_shop_subnav', 10 );

add_action( 'wp_footer', 'bbloomer_cart_refresh_update_qty' ); 
add_action( 'wp_footer', 'msp_maybe_show_promo_pop_up' );


//theme options
if( get_option( 'wc_easy_qty_breaks' ) ) add_action( 'woocommerce_update_product', 'save_product_with_qty_breaks', 10, 1 );


remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );

add_action( 'woocommerce_cart_collaterals', 'msp_wc_checkout_button', 5 );
add_action( 'woocommerce_after_cart', 'woocommerce_cross_sell_display', 100 );


if ( wp_is_mobile() ){
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
    add_action('woocommerce_before_single_product_summary', 'woocommerce_template_single_title', 8);

    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10);
    add_action('woocommerce_before_single_product_summary', 'woocommerce_template_single_rating', 8);

    remove_action( 'woocommerce_single_product_summary', 'msp_brand_name', 1 );
    add_action( 'woocommerce_before_single_product_summary', 'msp_brand_name', 1 );
}

add_filter( 'msp_additional_information_html', 'msp_get_product_pool', 5, 1 );
add_filter( 'msp_additional_information_html', 'msp_get_product_metadata', 10, 1 );
add_filter( 'msp_additional_information_html', 'msp_product_additional_information_html', 15, 1 );

add_filter( 'the_content', 'msp_maybe_add_tab_info', 9 );
add_filter( 'the_content', 'msp_get_product_tabs', 10 );
add_filter( 'the_content', 'msp_maybe_category_description', 11 );
add_filter( 'the_content', 'msp_maybe_attribute_description', 12 );

add_filter( 'woocommerce_get_availability_text', 'change_backorder_message', 10, 2 );
add_filter('woocommerce_post_class', 'msp_add_bootstrap_cols_to_product', 30, 3);
add_filter('woocommerce_product_loop_start', 'msp_woocommerce_product_loop_start', 999);
add_filter('woocommerce_product_loop_end', 'msp_woocommerce_product_loop_end', 999);
add_filter('loop_shop_per_page', 'msp_products_per_page', 999);
add_filter( 'wc_add_to_cart_message_html', 'remove_add_to_cart_message' );
