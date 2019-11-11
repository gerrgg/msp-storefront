<?php

defined( 'ABSPATH' ) || exit;
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

if ( wp_is_mobile() ){
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
    add_action('woocommerce_before_single_product_summary', 'woocommerce_template_single_title', 8);
}

/**
 * storefront_after_footer
 * @see msp_shameless_self_plug - 5
 */
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
add_action( 'msp_quick_links', 'msp_buy_again_btn', 5 );
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
 * msp_order_details_actions
 * 
 * @see msp_order_tracking_button();
 * @see msp_order_product_review_button();
 * @see msp_order_feedback_button();
 * @see msp_order_return_button();
 * @see msp_order_report_issue_button();
 */
add_action( 'msp_order_details_actions', 'msp_order_tracking_button', 5, 1 );
// add_action( 'msp_order_details_actions', 'msp_order_product_review_button', 10, 1 );
add_action( 'msp_order_details_actions', 'msp_order_feedback_button', 15, 1 );
add_action( 'msp_order_details_actions', 'msp_order_return_button', 20 );
add_action( 'msp_order_details_actions', 'msp_order_report_issue_button', 25 );

add_action( 'woocommerce_before_cart_table', 'woocommerce_button_proceed_to_checkout' );

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

add_action( 'wp_ajax_msp_update_comment_karma', 'msp_add_to_karma_table' );
add_action( 'wp_ajax_nopriv_msp_update_comment_karma', 'msp_add_to_karma_table' );

add_action( 'wp_ajax_msp_comment_on_comment', 'msp_comment_on_comment_callback' );
add_action( 'wp_ajax_nopriv_msp_comment_on_comment', 'msp_comment_on_comment_callback' );

add_action( 'wp_ajax_msp_delete_user_product_image', 'msp_delete_user_product_image' );

add_action( 'wp_ajax_msp_get_user_browsing_history', 'msp_get_user_browsing_history' );
add_action( 'wp_ajax_nopriv_msp_get_user_browsing_history', 'msp_get_user_browsing_history' );

add_action( 'wp_ajax_msp_get_product_size_guide_src', 'msp_get_product_size_guide_src' );
add_action( 'wp_ajax_nopriv_msp_get_product_size_guide_src', 'msp_get_product_size_guide_src' );

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
add_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 5 );
// add_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 15);
add_action( 'woocommerce_after_single_product_summary', 'comments_template', 25 );


/**
 * woocommerce_single_product_summary
 * @see msp_show_product_size_guide - 25
 */
add_action( 'woocommerce_single_product_summary', 'msp_show_product_size_guide_btn', 25 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 25 );
add_action( 'woocommerce_single_product_summary', 'msp_bulk_discount_table', 26 );

/**
 * msp_before_create_review_form
 * @see msp_review_more_products - 5;
 */
add_action( 'msp_before_create_review_form', 'msp_review_more_products' );

/**
 * msp_create_review_form
 * @see msp_create_review_wrapper_open - 5;
 * @see msp_create_review_top - 5;
 * @see msp_get_review_more_star_buttons -10;
 * @see msp_create_review_upload_form - 15;
 * @see msp_create_review_headline - 20;
 * @see msp_create_review_content - 25;
 * @see msp_create_review_wrapper_close - 100;
 * 
 */
add_action( 'msp_create_review_form', 'msp_create_review_wrapper_open', 1 );
add_action( 'msp_create_review_form', 'msp_create_review_top', 5, 1 );
add_action( 'msp_create_review_form', 'msp_get_review_more_star_buttons', 10 );
add_action( 'msp_create_review_form', 'msp_create_review_upload_form', 15, 1 );
add_action( 'msp_create_review_form', 'msp_create_review_headline', 20, 1 );
add_action( 'msp_create_review_form', 'msp_create_review_content', 25, 1 );
add_action( 'msp_create_review_form', 'msp_create_review_wrapper_close', 100 );

/**
 * woocommerce_product_additional_information
 * @see msp_add_to_product_additional_information - 50
 */
add_action( 'woocommerce_product_additional_information', 'msp_get_additional_information', 5, 1 );
add_filter( 'msp_additional_information_html', 'msp_get_product_pool', 5, 1 );
add_filter( 'msp_additional_information_html', 'msp_get_product_metadata', 10, 1 );
add_filter( 'msp_additional_information_html', 'msp_product_additional_information_html', 15, 1 );

add_action( 'woocommerce_before_shop_loop', 'msp_mobile_product_filter_button', 5 );

/**
 * woocommerce_archive_description
 */
add_action( 'woocommerce_archive_description', 'woocommerce_breadcrumb', 5 );
add_action( 'woocommerce_archive_description', 'msp_add_sub_cat_links', 1 );
add_action( 'woocommerce_archive_description', 'msp_add_category_images', 2 );
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

/**
 * woocommerce_after_shop_loop_item
 */
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );

/**
 * woocommerce_before_single_product_summary
 */
add_action( 'woocommerce_before_single_product_summary', 'woocommerce_breadcrumb', 5 );

add_action( 'woocommerce_before_main_content', 'msp_get_shop_subnav', 5 );


/**
 * msp_front_page
 */
// add_action( 'msp_front_page', 'msp_get_departments_silder', 10 );
add_action( 'msp_front_page', 'add_promo_row', 12, 1 );
add_action( 'msp_front_page', 'msp_get_random_slider', 15 );
add_action( 'msp_front_page', 'add_promo_row', 18, 1 );
add_action( 'msp_front_page', 'msp_get_random_slider', 30 );
add_action( 'msp_front_page', 'msp_get_featured_products_silder', 25 );
add_action( 'msp_front_page', 'msp_get_customer_service_info', 50 );

add_filter( 'woocommerce_structured_data_review', 'msp_sd_reviews', 10, 2 );
function msp_sd_reviews( $markup, $comment ){
    // This is an attempt the fix the Reviews Structed Data Error - https://github.com/woocommerce/woocommerce/issues/24950
}


// debug
// add_filter( 'wp_footer', 'test_structed_data', 100 );

function test_structed_data(){
}

//theme options
if( get_option( 'wc_easy_qty_breaks' ) )
    add_action( 'woocommerce_update_product', 'save_product_with_qty_breaks', 10, 1 );


if( get_option( 'wc_add_net_30_to_single_product' ) )
    add_action( 'woocommerce_single_product_summary', 'add_net_30', 35 );


