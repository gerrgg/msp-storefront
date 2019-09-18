<?php
$promos = msp_get_promos();

/** NOT A GOOD SOLUTION - TEMP FIX */

get_header();
msp_get_departments_silder();
if( isset( $promos[0], $promos[1] ) ) add_promo_row( array( $promos[0], $promos[1] ) );
msp_get_random_slider();
if( isset( $promos[2], $promos[3] ) ) add_promo_row( array( $promos[2], $promos[3] ) );
msp_get_random_slider();
if( isset( $promos[4], $promos[5] ) ) add_promo_row( array( $promos[4], $promos[5] ) );
msp_get_featured_products_silder();
msp_get_customer_service_info();
get_footer(); 
?>