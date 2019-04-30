<?php

defined( 'ABSPATH' ) || exit;

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
 * msp_quick_links_wrapper_close();
 */
add_action( 'msp_quick_links', 'msp_quick_links_wrapper_open', 1 );
add_action( 'msp_quick_links', 'msp_buy_again_btn', 5 );
add_action( 'msp_quick_links', 'msp_get_user_products_history_btn', 10 );
add_action( 'msp_quick_links', 'msp_quote_btn', 15 );
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
add_action( 'msp_order_details_actions', 'msp_order_product_review_button', 10, 1 );
add_action( 'msp_order_details_actions', 'msp_order_feedback_button', 15 );
add_action( 'msp_order_details_actions', 'msp_order_return_button', 20 );
add_action( 'msp_order_details_actions', 'msp_order_report_issue_button', 25 );


/**
 * woocommerce_checkout_order_processed
 * 
 * @see msp_update_order_estimated_delivery();
 */
add_action( 'woocommerce_thankyou', 'msp_update_order_estimated_delivery', 5, 1 );

/**
 * AJAX
 */
add_action( 'wp_ajax_msp_set_estimated_delivery_date', 'msp_set_estimated_delivery_date' );
add_action( 'wp_ajax_nopriv_msp_set_estimated_delivery_date', 'msp_set_estimated_delivery_date' );

add_action( 'wp_ajax_msp_update_comment_karma', 'msp_add_to_karma_table' );
add_action( 'wp_ajax_mopriv_msp_update_comment_karma', 'msp_add_to_karma_table' );

add_action( 'wp_ajax_msp_comment_on_comment', 'msp_comment_on_comment_callback' );
add_action( 'wp_ajax_nopriv_msp_comment_on_comment', 'msp_comment_on_comment_callback' );

/**
 * Admin Post
 */

 //bulk form - /quote
add_action( 'admin_post_msp_submit_bulk_form', 'msp_submit_bulk_form' );
add_action( 'admin_post_nopriv_msp_submit_bulk_form', 'msp_submit_bulk_form' );

//create_review - /review
add_action( 'admin_post_msp_process_create_review', 'msp_process_create_review' );
add_action( 'admin_post_nopriv_msp_process_create_review', 'msp_process_create_review' );

/**
 * woocommerce_review_before
 * 
 * @see msp_chevron_karma_form - 5;
 */

add_action( 'woocommerce_review_before', 'msp_chevron_karma_form', 5, 1 );
remove_action( 'woocommerce_review_before', 'woocommerce_review_display_gravatar', 10 );

/**
 * woocommerce_review_before_comment_meta
 * @see woocommerce_review_display_gravatar - 5
 */
remove_action( 'woocommerce_review_before_comment_meta', 'woocommerce_review_display_rating', 10 );
add_action( 'woocommerce_review_before_comment_meta', 'woocommerce_review_display_gravatar', 5 );

/**
 * woocommerce_review_comment_text
 * @see msp_get_comment_headline - 5
 */
add_action( 'woocommerce_review_comment_text', 'msp_get_comment_headline', 5, 1 );

 /**
 * woocommerce_review_meta
 * @see woocommerce_review_display_rating - 8
 */

add_action( 'woocommerce_review_meta', 'woocommerce_review_display_rating', 8 );

/**
 * woocommerce_review_after_comment_text
 * 
 * @see msp_comment_actions_wrapper_open();
 * @see msp_reply_to_comment_btn();
 * @see msp_flag_comment_btn();
 * @see msp_comment_actions_wrapper_close();
 */

// add_action( 'woocommerce_review_after_comment_text', 'msp_comment_actions_wrapper_open', 1 );
// add_action( 'woocommerce_review_after_comment_text', 'msp_reply_to_comment_btn', 5, 1 );
// add_action( 'woocommerce_review_after_comment_text', 'msp_flag_comment_btn', 10, 1 );
// add_action( 'woocommerce_review_after_comment_text', 'msp_comment_actions_wrapper_close', 100, 1 );


/**
 * woocommerce_after_single_product_summary
 * @see comments_template();
 */
add_action( 'woocommerce_after_single_product_summary', 'comments_template' );

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


// debug
// add_action( 'wp_footer', 'get_cron_jobs' );
function get_cron_jobs(){
    var_dump( _get_cron_array() );
}

add_action( 'wp_footer', 'test' );
function test(){
   
}