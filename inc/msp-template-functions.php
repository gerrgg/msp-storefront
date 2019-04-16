<?php

defined( 'ABSPATH' ) || exit;

/**
 * Opens the header wrapper
 */
function msp_header_wrapper_open(){
    echo '<nav class="navbar navbar-expand-lg navbar-light bg-dark mb-2"><div class="container">';
}
/**
 * Closes the header wrapper
 */
function msp_header_wrapper_close(){
    echo '</div></nav>';
}

/**
 * Displays the html for the sites identity
 */
function msp_header_site_identity(){
    $logo_id = get_theme_mod( 'custom_logo' );
    $logo_src = wp_get_attachment_image_src( $logo_id, 'full' );
    $logo_width = ( ! empty( get_option( 'msp_logo_width' ) ) ) ? get_option( 'msp_logo_width' ) : '100';

    if( has_custom_logo() && ! empty( $logo_src ) ){
        echo '<a class="navbar-brand" style="max-width: '. $logo_width .'px" href="/"><img src="'. $logo_src[0] .'"/></a>';
    } else {
        echo '<a class="navbar-brand" href="/">'. bloginfo( 'sitename' ) .'</a>';
    }
}


/**
 * Opens the header middle wrapping div
 */
function msp_header_middle_open(){
    echo '<div class="flex-grow-1">';
}

/**
 * outputs the header searchbar
 */
function msp_header_search_bar(){
    // echo 'Searchbar';
    echo do_shortcode('[wcas-search-form]');
}

/**
 * outputs the header navigation
 */
function msp_header_menu(){
    echo '<div class="d-flex align-items-end">';
        wp_nav_menu( array(
            'depth'	          => 2, // 1 = no dropdowns, 2 = with dropdowns.
            'container'       => 'div',
            'container_id'    => 'header-middle',
            'menu_class'      => 'navbar-nav m-0',
            'theme_location' => 'primary',
            'fallback_cb'     => 'WP_Bootstrap_Navwalker::fallback',
            'walker'          => new WP_Bootstrap_Navwalker(),
        ) );
        

        echo '<div class="d-flex align-items-end ml-auto">';
            msp_header_right_menu();
            msp_header_cart();
        echo '</div>';
    echo '</div>';

}

/**
 * Closes the header middle wrapping div
 */
function msp_header_middle_close(){
    echo '</div>';
}

function msp_header_right_menu(){
    wp_nav_menu( array(
        'depth'	          => 2, // 1 = no dropdowns, 2 = with dropdowns.
        'container'       => 'div',
        'container_id'    => 'header-right',
        'menu_class'      => 'navbar-nav m-0',
        'theme_location' => 'secondary',
        'fallback_cb'     => 'WP_Bootstrap_Navwalker::fallback',
	    'walker'          => new WP_Bootstrap_Navwalker(),
    ) );
}

function msp_header_cart(){
    $cart_size = sizeof( WC()->cart->get_cart_contents() );
    //subtotal
?>
    <div id="cart-wrapper" class="d-flex">
        <a class="nav-link" href="<?php echo wc_get_cart_url(); ?>">
            <i class="fas fa-shopping-cart fa-2x"></i>
            <span class="item-counter"><?php echo $cart_size; ?></span>
        </a>
    </div>
<?php
}


