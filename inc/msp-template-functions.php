<?php

defined( 'ABSPATH' ) || exit;

/**
 * @see Hook: msp_header
 */

/**
 * Opens the header wrapper
 */
function msp_header_wrapper_open(){
    echo '<nav class="navbar navbar-expand-lg navbar-light bg-dark mb-2"><div class="container align-items-end">';
}

/**
 * Displays the html button for opening and closing the mobile menu
 */
function msp_header_mobile_menu_button(){
    echo '<button class="btn mobile-menu-button"><i class="fas fa-bars fa-2x"></i></button>';
}

/**
 * Gets the id, src and specified width of the sites logo and displays it.
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
 * outputs the html generate by the "wcas-search-form" shortcode.
 * shortcode is created by https://wordpress.org/plugins/ajax-search-for-woocommerce/
 */
function msp_header_search_bar(){
    echo do_shortcode('[wcas-search-form]');
}

/**
 * outputs the header navigation
 */
function msp_header_menu(){
    echo '<div id="header-menu" class="d-flex align-items-end">';

        do_action( 'msp_quick_links' );

        echo '<div class="d-flex align-items-end ml-auto">';
            msp_header_right_menu();
            msp_header_cart();
        echo '</div>';

    echo '</div>';

}

function msp_quick_links_wrapper_open(){
    echo '<ul class="navbar-nav m-0">';
}


function msp_buy_again_btn(){
    $orders = wc_get_orders( array( 'customer_id' => get_current_user_id() ) );

    if( ! empty( $orders ) ) :
        ?>
        <li class="nav-item buy-again">
            <a class="nav-link" href="/buy-again">
                Buy Again
            </a>
        </li>
        <?php
    endif;
    
}

function msp_quote_btn(){
    ?>
    <li class="nav-item">
        <a class="nav-link" href="/quote">
            Request Quote
        </a>
    </li>
    <?php
}

add_shortcode( 'quote' , 'msp_quote_shortcode' );
function msp_quote_shortcode(){
    $input = isset( $_GET['input'] ) ? $_GET['input'] : 0;
    $product = msp_get_product_by_mixed_data( $input );

    if( empty( $product ) ){
       get_msp_quote_find_product_id_form();
    } else {
        get_msp_quote_form( $product );
    }
}

function get_msp_quote_find_product_id_form(){
    ?>
    <div class="alert alert-danger" style="max-width: 450px;" role="alert">
        <form class="form" method="get">
            <p for="input">Enter the ID, SKU or Name of the product you want to quote.</p>
            <div class="form-group">
                <input id="sku" type="text" name="input[sku]" class="form-control" placeholder="Stock Keeping Unit (SKU)" />
            </div>
            <div class="form-group">
                <input id="name" type="text" name="input[name]" class="form-control" placeholder="Product Name" />
            </div>
            
            <input class="btn btn-danger" type="submit" value="Submit" />
        </form>
    </div>
<?php
}

function get_msp_quote_form( $product ){
    set_query_var( 'msp_product_id', $product->get_id() );
    wc_get_template( '/template/msp-quote.php' );
}

add_action( 'admin_post_msp_submit_bulk_form', 'msp_submit_bulk_form' );
add_action( 'admin_post_nopriv_msp_submit_bulk_form', 'msp_submit_bulk_form' );

function msp_submit_bulk_form(){
    var_dump( $_POST );
    $sitename = bloginfo( 'sitename' );
    $products_arr = array();
    foreach( $_POST['product'] as $id => $qty ){
        if( ! empty( $qty ) ){
            $product = wc_get_product( $id );
            $products_arr[$id] = array( 
                'qty' => $qty,
                'name' => $product->get_formatted_name(),
            );
        }
    }

    if( ! empty( $products_arr ) ){
        ob_start();
        ?>
        <h1>Bulk Quote Request</h1>
        <h2>Ship To</h2>
        <p>Reply To: <?php echo $_POST['email'] ?></p>
        <address>
            <?php echo $_POST['street'] . ', ' . $_POST['zip'] ?>
        <address>
        <hr>
        <table>
            <thead>
                <th>Name</th>
                <th>Quantity</th>
            </thead>
            <tbody>
                <?php 
                    foreach( $products_arr as $id => $item ){
                        echo '<tr>';
                        echo '<td>'. $item['name'] .'</td>';
                        echo '<td>'. $item['qty'] .'</td>';
                        echo '</tr>';
                    }
                ?>
            </tbody>
        </table>
        <?php
        $html = ob_get_clean();
        
        //confirm works.
        wp_mail( get_option('admin_email'), $sitename . ' - Quote Request', $html );
        // wc_add_notice( 'We got your quote request, please allow 1-2 business days for a response', 'success' );
        wp_redirect( '/' );
    }
}

function msp_get_product_by_mixed_data( $data ){
    if( ! empty( $data ) ){
        foreach( $data as $key => $query ){
            $func = 'wc_get_product_id_by_' . $key;
            $product_id = $func( $query );
            if( ! empty( $product_id ) ) return wc_get_product( $product_id );
        }
    }
}

function wc_get_product_id_by_name( $str ){
    global $wpdb;
    return $wpdb->get_row( "SELECT ID FROM wp_posts WHERE post_title LIKE '%$str%'" );
}

add_shortcode( 'buy_again' , 'msp_buy_again_shortcode' );
function msp_buy_again_shortcode(){
    $order_items = msp_get_customer_unique_order_items( get_current_user_id() );
    echo '<div class="owl-carousel owl-theme">';
    foreach( $order_items as $id ){
        $product = wc_get_product( $id );
        global $product;
        wc_get_template_part( 'content', 'product-simple' );
    }
    echo '</div>';
}

function msp_get_customer_unique_order_items( $user_id ){
    $order_items = array();
    $orders = wc_get_orders( array( 'customer_id' => $user_id ) );
    
    if( ! empty( $orders ) ){
        foreach( $orders as $order ){
            $items = $order->get_items();
            foreach( $items as $id => $item ){
                array_push( $order_items, $item->get_product_id() );
            }
        }
    }

    return array_unique( $order_items );
}

function msp_get_user_products_history_btn(){
    global $history;

    if( ! empty( $history->data['products'] ) ) :
        ?>
        <li class="nav-item user-history">
            <a class="nav-link dropdown-toggle">
                Browsing History
            </a>
            <?php $history->get_user_products_history(); ?>
        </li>
        <?php
    endif;
    
}

function msp_quick_links_wrapper_close(){
    echo '</ul>';
}

/**
 * Closes the header middle wrapping div
 */
function msp_header_middle_close(){
    echo '</div>';
}

/**
 * gets the 'secondary' menu - used for account info / cart.
 */
function msp_header_right_menu(){
    wp_nav_menu( array(
        'depth'	          => 2, // 1 = no dropdowns, 2 = with dropdowns.
        'container'       => 'div',
        'container_id'    => 'header-right',
        'menu_class'      => 'navbar-nav m-0',
        'theme_location'  => ( is_user_logged_in() ) ? 'secondary' : 'logged-out',
        'fallback_cb'     => 'WP_Bootstrap_Navwalker::fallback',
	    'walker'          => new WP_Bootstrap_Navwalker(),
    ) );
}

function msp_header_cart(){
    /**
     * Displays the html markup for the cart button on the header - includes # of items in cart
     */
    $cart_size = sizeof( WC()->cart->get_cart_contents() ); ?>
    <div id="cart-wrapper" class="d-flex">
        <a class="nav-link" href="<?php echo wc_get_cart_url(); ?>">
            <i class="fas fa-shopping-cart fa-2x"></i>
            <span class="item-counter"><?php echo $cart_size; ?></span>
        </a>
    </div>
<?php
}

/**
 * Closes the header wrapper
 */
function msp_header_wrapper_close(){
    echo '</div></nav>';
}

/**
 * @see Hook: storefront_before_site
 */

 /**
  * Displays the html required for opening up the mobile menu div
  */
function msp_mobile_menu_wrapper_open(){
    echo '<div id="mobile-menu">';
}

 /**
  * Displays a nice greeting to a logged in user, and encourages non-logged in users to do so.
  */
function msp_mobile_menu_header(){
    $user = get_userdata( get_current_user_id() );
    $username = ( ! empty( $user->user_login ) ) ? $user->user_login : 'Sign up or login';
    echo "<h3 class='title py-2 pl-4'>Hello, $username</h3>";
}

/**
 * gets the 'handheld' (mobile) menu.
 */
function msp_mobile_menu(){
    echo '<p class="mobile-label">SHOP BY CATEGORY</p>';
    wp_nav_menu( array(
        'theme_location' => 'handheld',
        'menu_id'        => 'mobile-menu-categories',
        'menu_class'     => 'm-0 list-unstyled',
    ));
}

function msp_mobile_menu_account_links(){
    ?>
    <hr />
    <p class="mobile-label">ACCOUNT & HELP</p>
    <ul class="m-0 list-unstyled">
        <li class="menu-item"><a href="<?php echo wc_get_page_permalink( 'myaccount' ) ?>">My Account</a></li>
        <li class="menu-item"><a href="#">Help</a></li>
        <?php if( is_user_logged_in() ) : ?>
            <li class="menu-item"><a href="<?php echo wp_logout_url( '/' ) ?>">Sign out</a></li>
        <?php endif; ?>
    <?php

    
}

 /**
  * Displays the html required for closing up the mobile menu div
  */
function msp_mobile_menu_wrapper_close(){
    echo "</div> <!-- #mobile-menu -->";
}


function msp_order_details_html( $order ){
    ?>
    <tr class="border-top">
        <td colspan="4">
            <?php 
                foreach( $order->get_items() as $item ){
                    $product = wc_get_product( $item->get_product_id() );
                    if( ! empty( $product ) ){
                        $image_src = MSP::get_product_image_src( $product->get_image_id() );
                        ?>
                        <div class="d-flex">
                            <a href="<?php echo $product->get_permalink() ?>">
                                <img src="<?php echo $image_src ?>" style="width: 100px; height: 100px;" class="mb-2" />
                            </a>
                            <div class="pl-4">
                                <a class="link-normal" href="<?php echo $product->get_permalink() ?>">
                                <?php echo $product->get_name(); ?>
                            </a>
                            <p class="">Price: <span class="price">$<?php echo $product->get_price(); ?></span></p>
                            <p class="m-0">Qty: <?php echo $item->get_quantity(); ?></p>
                            </div>
                        </div>
                        <?php
                    }
                }
            ?>
        </td>
        <td>
            <div class="order-actions btn-group-vertical text-align-left">
                    <?php do_action( 'msp_order_details_actions', $order ) ?>
            </div>
        </td>
    </tr>
    <?php
}

function msp_order_tracking_button( $order ){
    $tracking_info = array( 
        'shipper' => get_post_meta( $order->get_id(), 'shipper', true ),
        'tracking' => get_post_meta( $order->get_id(), 'tracking', true ),
    );

    if( $order->get_status() == 'completed' && ! empty( $tracking_info ) ) :
        $tracking_link = msp_make_tracking_link( $tracking_info['shipper'], $tracking_info['tracking'] );
        ?>
        <a role="button" href="<?php echo $tracking_link; ?>" target="_new" class="btn btn-success btn-block link-normal">
            <i class="fas fa-shipping-fast"></i>
            Track Package
        </a>
        <?php
    endif;
}

function msp_make_tracking_link( $shipper, $tracking ){
    $base_urls = array(
    'ups' => 'https://www.ups.com/track?loc=en_US&tracknum=',
    'fedex' => 'https://www.fedex.com/apps/fedextrack/?tracknumbers=',
    'usps' => 'https://tools.usps.com/go/TrackConfirmAction?tLabels='
    );
    return $base_urls[$shipper] . $tracking;
}

function msp_get_order_estimated_delivery( $order_id ){
    $order = wc_get_order( $order_id );

    $ship_to = array(
        'street' => $order->get_shipping_address_1(),
        'postal' => $order->get_shipping_postcode(),
        'country' => $order->get_shipping_country(),
    );

    $ups = new UPS();
    $response = $ups->time_in_transit( $ship_to );

    if( $response['Response']['ResponseStatusCode'] ){
        return $response['TransitResponse']['ServiceSummary'];
    }
}

function msp_update_order_estimated_delivery( $order_id ){
    $order = wc_get_order( $order_id );
    $service_summary = msp_get_order_estimated_delivery( $order_id );

    foreach( $service_summary as $service ){
        // find the service which matches our chosen method
        // include a default guess ( ground? ).
        var_dump( $service );
    }

}

function msp_order_product_review_button(){
    /**
     * Needs integration with custom reviews
     */
    ?>
        <button type="button" class="btn btn-info btn-block"><i class="fas fa-edit"></i>Write a Product Review</button>
    <?php
}

function msp_order_feedback_button(){
    /**
     * Simple Modal Feedback Form - Ask how we can improve? Suggestions?
     */
    ?>
        <button type="button" class="btn btn-secondary btn-block"><i class="far fa-comments"></i>Leave Feedback</button>
    <?php
}

function msp_order_return_button(){
    /**
     * Integration with UPS / USPS?
     */
    ?>
        <button type="button" class="btn btn-warning btn-block"><i class="fas fa-cube"></i>Return or replace items</button>
    <?php
}

function msp_order_report_issue_button(){
    /**
     * Simple modal designed to email store owner to potential problems.
     */
    ?>
        <button type="button" class="btn btn-danger btn-block"><i class="fas fa-exclamation-circle"></i>Problem with order</button>
    <?php
}
