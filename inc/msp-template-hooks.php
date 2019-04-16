<?php

defined( 'ABSPATH' ) || exit;

/**
 * msp_header.
 * @see msp_header_wrapper_open()
 * @see msp_header_site_identity()
 * @see msp_header_middle_open()
 * @see msp_header_middle_close()
 * @see msp_header_wrapper_close()
 */
add_action( 'msp_header', 'msp_header_wrapper_open', 0 );
add_action( 'msp_header', 'msp_header_site_identity', 5 );
add_action( 'msp_header', 'msp_header_middle_open', 10 );
add_action( 'msp_header', 'msp_header_search_bar', 15 );
add_action( 'msp_header', 'msp_header_menu', 20 );
add_action( 'msp_header', 'msp_header_middle_close', 25 );
// add_action( 'msp_header', 'msp_header_right_menu', 30 );
// add_action( 'msp_header', 'msp_header_cart', 35 );
add_action( 'msp_header', 'msp_header_wrapper_close', 100 );
