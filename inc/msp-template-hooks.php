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


/**
 * msp_order_details_actions
 * 
 * @see msp_order_tracking_button();
 * @see msp_order_product_review_button();
 * @see msp_order_feedback_button();
 * @see msp_order_return_button();
 * @see msp_order_report_issue_button();
 */
add_action( 'msp_order_details_actions', 'msp_order_tracking_button', 5 );
add_action( 'msp_order_details_actions', 'msp_order_product_review_button', 10 );
add_action( 'msp_order_details_actions', 'msp_order_feedback_button', 15 );
add_action( 'msp_order_details_actions', 'msp_order_return_button', 20 );
add_action( 'msp_order_details_actions', 'msp_order_report_issue_button', 25 );
