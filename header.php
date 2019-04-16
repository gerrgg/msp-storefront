<?php
/**
 * The header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package storefront
 */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=2.0">
<link rel="profile" href="http://gmpg.org/xfn/11">
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<?php 
/**
 * Functions hooded into storefront_before_site action
 * 
 * @hooked msp_mobile_menu_wrapper_open - 0
 * @hooked msp_mobile_menu_header - 5
 * @hooked msp_mobile_menu - 50
 * @hooked msp_mobile_menu_account_links - 55
 * @hooked msp_mobile_menu_wrapper_close - 100
 */
do_action( 'storefront_before_site' ); 
?>



<div id="page" class="hfeed site">
	<?php do_action( 'storefront_before_header' ); ?>

	<header id="masthead" class="" role="banner" style="<?php storefront_header_styles(); ?>">

		<?php
		/**
		 * Functions hooked into msp_header action
		 *
		 * @hooked msp_header_wrapper_open - 0
		 * @hooked msp_header_mobile_menu_button - 1
		 * @hooked msp_header_site_identity - 5
		 * @hooked msp_header_middle_open - 10
		 * @hooked msp_header_search_bar - 15
		 * @hooked msp_header_menu - 20
		 * @hooked msp_header_middle_close - 25
		 * @hooked msp_header_right_menu - 30
		 * @hooked msp_header_cart - 35
		 * @hooked msp_header_wrapper_close - 100
		 * 
		 */
		do_action( 'msp_header' );
		?>

	</header><!-- #masthead -->

	<?php
	/**
	 * Functions hooked in to storefront_before_content
	 *
	 * @hooked storefront_header_widget_region - 10
	 * @hooked woocommerce_breadcrumb - 10
	 */
	do_action( 'storefront_before_content' );
	?>

	<div id="content" class="site-content" tabindex="-1">
		<div class="col-full">

		<?php
		do_action( 'storefront_content_top' );