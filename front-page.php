<?php
$promos = msp_get_promos();

// TODO: check if array

get_header();
msp_get_departments_silder();
add_promo_row( array_slice( $promos, 0, 2, true ) );
msp_get_random_slider();
add_promo_row( array_slice( $promos, 2, 4, true ) );
msp_get_random_slider();
add_promo_row( array_slice( $promos, 4, 6, true ) );
msp_get_featured_products_silder();
msp_get_customer_service_info();
get_footer(); 
?>