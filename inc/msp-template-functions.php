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
/**
 * Opens up the center mid navbar
 */

function msp_quick_links_wrapper_open(){
    echo '<ul class="navbar-nav m-0">';
}

/**
 * displays a navigation link based on whether or not the user has previous orders.
 */
function msp_buy_again_btn(){
    $order_items = msp_get_customer_unique_order_items( get_current_user_id() );

    if( ! empty( $order_items ) ) :
        ?>
        <li class="nav-item buy-again">
            <a class="nav-link" href="/buy-again">
                Buy Again
            </a>
        </li>
        <?php
    endif;
    
}

/**
 * displays a navigation link for quote requests
 */
function msp_quote_btn(){
    ?>
    <li class="nav-item">
        <a class="nav-link" href="/quote">
            Request Quote
        </a>
    </li>
    <?php
}

/**
 * shortcode used to display two differant forms depending on the value of $_GET['input']
 * if we got a product from the input, show quote form, else show find_product_id_from
 */
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
/**
 * Simple html form used for finding a product based on a user's input.
 * Ideally this form wont be used very often and this attribute is passed from a link somewhere else.
 */
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

/**
 * sets up a variable for passing to the msp-quote.php template file
 */
function get_msp_quote_form( $product ){
    set_query_var( 'msp_product_id', $product->get_id() );
    wc_get_template( '/template/msp-quote.php' );
}

/**
 * Processes the data passed from the get_msp_quote_form, formats it and delivers to admin_email.
 */
function msp_submit_bulk_form(){
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
        
        wp_mail( get_option('admin_email'), $sitename . ' - Quote Request', $html );
        wp_redirect( '/' );
    }
}

/**
 * Dynamically searches for a product in differant ways based on the value of the key
 * @param mixed $data - the input from the get_msp_quote_find_product_id_form() function.
 */
function msp_get_product_by_mixed_data( $data ){
    if( ! empty( $data ) ){
        foreach( $data as $key => $query ){
            $func = 'wc_get_product_id_by_' . $key;
            $product_id = $func( $query );
            if( ! empty( $product_id ) ) return wc_get_product( $product_id );
        }
    }
}

/**
 * A function which looks for a product by querying the DB for a post_title similar to $str
 * @param $str
 */
function wc_get_product_id_by_name( $str ){
    global $wpdb;
    return $wpdb->get_row( "SELECT ID FROM wp_posts WHERE post_title LIKE '%$str%'" );
}

/**
 * Simple shortcode used to display items already purchased by the user/customer.
 */
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

/**
 * Sorts through an array of customer orders, picks out the ids and stores them to $order_items.
 * @param int $user_id - The id of the user
 * @return array $order_items - An array of unique product ids purchased by the user.
 */
function msp_get_customer_unique_order_items( $user_id ){
    $order_items = array();
    $orders = wc_get_orders( array( 'customer_id' => $user_id ) );

    if( ! empty( $orders ) ){
        foreach( $orders as $order ){
            foreach( $order->get_items() as $order_item ){
                $product = $order_item->get_product();
                if( $product && $product->is_visible() ) array_push( $order_items, $product->get_id() );
            }
        }
    }

    return array_unique( $order_items );
}

function msp_get_user_browsing_history(){
    global $history;
    echo $history->get_user_products_history();
    wp_die();
}

/**
 * Outputs the navigation button linking to the users browsing history
 */
function msp_get_user_products_history_btn(){
    global $history;

    if( ! empty( $history->data['products'] ) ) :
        ?>
        <li class="nav-item user-history">
            <a class="nav-link dropdown-toggle">
                Browsing History
            </a>
            <?php // $history->get_user_products_history(); ?>
        </li>
        <?php
    endif;
    
}

/**
 * Closes the naviation menu in the center of the header.
 */
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

/**
 * Displays the html markup for the cart button on the header - includes # of items in cart
 */
function msp_header_cart(){
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

/**
 * Custom Mobile Menu - Static
 */
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


/**
 * Displays for the user, the items on each order as well as introduce a new hook 'msp_order_details_actions'.
 */
function msp_order_details_html( $order ){
    ?>
    <tr class="border-top">
        <td colspan="4">
            <h4 class="text-success iww-date-estimate"><?php echo msp_get_order_estimated_delivery( $order->get_id() ) ?></h4>
            <?php 
                foreach( $order->get_items() as $item ){
                    $product = wc_get_product( $item->get_product_id() );
                    if( ! empty( $product ) ){
                        $image_src = MSP::get_product_image_src( $product->get_image_id() );
                        ?>
                        <div class="d-flex">
                            <a href="<?php echo $product->get_permalink() ?>" style="width: 100px;">
                                <img src="<?php echo $image_src ?>" style="height: 100px;" class="mb-2 mx-auto" />
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

/**
 * Displays an external link to track a package based on whether or not a tracking link is present.
 * @param WC_Order $order
 */
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

/**
 * Creates a link based on the $shipper,
 * @param string $shipper - The company we shipped with
 * @param string $tracking - The tracking number on the package.
 */
function msp_make_tracking_link( $shipper, $tracking ){
    $base_urls = array(
    'ups' => 'https://www.ups.com/track?loc=en_US&tracknum=',
    'fedex' => 'https://www.fedex.com/apps/fedextrack/?tracknumbers=',
    'usps' => 'https://tools.usps.com/go/TrackConfirmAction?tLabels='
    );
    return $base_urls[$shipper] . $tracking;
}

/**
 * Makes an API tracking request to UPS for expected delivery date and updates the db entry.
 * @param int $order_id
 */
function msp_update_order_tracking( $order_id ){
    global $ups;
    $tracking = get_post_meta( $order_id, 'tracking', true );
    $date_est = $ups->track( $tracking );
    if( ! empty( $date_est ) ){
        update_post_meta( $order_id, 'msp_estimated_delivery_date', $date_est );
    }
}

/**
 * gets the msp_estimated_delivery_date meta value of the order
 * @param int $order_id - ID of the order.
 */
function msp_get_order_estimated_delivery( $order_id ){
    $est_date = get_post_meta( $order_id, 'msp_estimated_delivery_date', true );
    if( ! empty( $est_date ) ){
        $string = ( msp_package_delivered( $est_date, $order_id ) ) ? 'Delivered ' : 'Expected to deliver by ';
        return $string . $est_date;
    }
}

/**
 * Checks if the package delivered. If it does and has the order id, it deletes it.
 * @param string - $est_date
 * @param int - $order_id
 * @return bool - $delivered
 */
function msp_package_delivered( $est_date, $order_id = 0 ){
    $delivered = ( time() > strtotime( $est_date ) );

    if( $delivered && ! empty( $order_id ) ){
        MSP_Admin::manage_cron_jobs( 'tracking', $order_id, false );
    }

    return $delivered;
}

/**
 * Sends a request to the UPS Time in Transit API
 * @param int $order_id
 * @return array $response - UPS Time in Transit API response
 */
function msp_get_ups_time_in_transit( $order_id ){
    global $ups;
    $order = wc_get_order( $order_id );

    $ship_to = array(
        'street' => $order->get_shipping_address_1(),
        'postal' => $order->get_shipping_postcode(),
        'country' => $order->get_shipping_country(),
    );

    $response = $ups->time_in_transit( $ship_to );

    if( $response['Response']['ResponseStatusCode'] ){
        return $response['TransitResponse']['ServiceSummary'];
    }
}



/**
 * This is primarily used as a simple estimate before a tracking # is added.
 * Checks for the value of $_SESSION['msp_estimated_delivery_date']
 * if it exists, updates the order msp_estimated_delivery_date.
 * 
 * @param int $order_id
 * @return array $response - UPS Time in Transit API response
 */
function msp_update_order_estimated_delivery( $order_id ){
    global $ups;
    if( isset( $_SESSION['msp_estimated_delivery_date'] ) )
        update_post_meta( $order_id, 'msp_estimated_delivery_date', $_SESSION['msp_estimated_delivery_date'] );
}

/**
 * TODO: Hard-coded; should add some kinda of UI to theme options.
 * Creates a simple guess for when a package should deliver based on the method provided.
 * @param string $method - The label of a shipper method
 * @return string $date_str - A string created by @see iww_make_date();
 */
function msp_get_default_est_delivery( $method ){
	switch( $method ){
		case '3 Day Select (UPS)':
		$date_str = iww_make_date( [3] );
		break;
		case 'Ground (UPS)':
		$date_str = iww_make_date( [2, 5] );
		break;
		case '2nd Day Air (UPS)':
		$date_str = iww_make_date( [2] );
		break;
		case 'Next Day Air (UPS)':
		$date_str = iww_make_date( [2] );
		break;
		case 'Next Day Air Saver (UPS)':
		$date_str = iww_make_date( [1] );
		break;
		case 'Next Day Air Early AM (UPS)':
		$date_str = iww_make_date( [1] );
		break;
		case 'Free shipping':
		$date_str = iww_make_date( [5, 10] );
		break;
		default :
		$date_str = '';
		break;
	}
	return $date_str;
}

/**
 * Takes in an array of dates, takes into account the current day and hour and returns a guess as to when the package should arrive.
 * @param array[int] $dates - An array of numbers representing the number of days until delivery
 * @return string 
 */
function iww_make_date( $dates ){
    date_default_timezone_set('EST');
    $current_hour = date('G');
    $current_day = date('N');
    $date_str = '';

    foreach( $dates as $key => $date ){
        if( $current_day > 5 ){
            $date = ( $current_day == 6 ) ? $date + 1 : $date + 2;
        } else {
            // weekdays
            if( $current_hour >= 12 ) $date++;
        }
        // if this isn't the first date, add a hyphen to the string
            if( $key != 0 )$date_str .= ' - ';
        // create date based on leadtime + $date passed to function
            $future = date( 'l, F jS', strtotime('+' . $date . 'days') );
        // if future lands on a sunday, add another day to it.
            if( preg_match( '/Saturday/', $future ) ) $future = date( 'l, F jS', strtotime('+' . ($date + 2) . 'days') );
            if( preg_match( '/Sunday/', $future ) ) $future = date( 'l, F jS', strtotime('+' . ($date + 1) . 'days') );
            $date_str .= $future;
    }
    return '<h6 class="m-0 p-0 text-success iww-date-estimate">'.$date_str.'</h6>';
}

/**
 * Takes the $_POST value of an ajax call, stores it to $_SESSION and sends back the value just to confirm.
 * The session is used in msp_update_order_estimated_delivery() to update order meta.
 */
function msp_set_estimated_delivery_date(){
    $est_date = explode( ' - ', $_POST['date'] );
    $_SESSION['msp_estimated_delivery_date'] = end( $est_date );
    wp_send_json( $_SESSION['msp_estimated_delivery_date'] );
}


/**
 * Needs integration with custom reviews
 */
function msp_order_product_review_button( $order ){
    $id_arr = array();
    foreach( $order->get_items() as $order_item ){
        array_push( $id_arr, $order_item->get_product_id() );
    }

    $action = ( sizeof( $id_arr ) > 1 ) ? 'show_more' : 'create';

    ?>
        <a href="<?php echo msp_get_review_link( $id_arr, array( 'action' => $action ) ) ?>" role="button" class="btn btn-info btn-block link-normal">
            <i class="fas fa-edit"></i> Write a Product Review
        </a>
    <?php
}

/**
 * Simple Modal Feedback Form - Ask how we can improve? Suggestions?
 */
function msp_order_feedback_button(){
    make_modal_btn( array(
        'type'  => 'button',
        'class' => 'btn btn-secondary btn-block',
        'text'  => "<i class='far fa-comments'></i>Leave Feedback",
        'title' => 'Leave Us Feedback',
        'model' => 'leave_feedback',
    ));
    ?>
        <!-- <button type="button" class="btn btn-secondary btn-block"><i class="far fa-comments"></i>Leave Feedback</button> -->
    <?php
}

/**
 * Integration with UPS / USPS?
 */
function msp_order_return_button(){
    ?>
        <button type="button" class="btn btn-warning btn-block"><i class="fas fa-cube"></i>Return or replace items</button>
    <?php
}

/**
 * Simple modal designed to email store owner to potential problems.
 */
function msp_order_report_issue_button(){
    ?>
        <button type="button" class="btn btn-danger btn-block"><i class="fas fa-exclamation-circle"></i>Problem with order</button>
    <?php
}

function msp_get_resources_tab(){
    global $post;
    $resources = msp_get_product_resources( $post->ID );

    echo '<h2>Resources</h2>';
    echo '<ul>';
    foreach( $resources as $arr ) : ?>
        <li><a target="new" href="<?php echo $arr[1] ?>"><?php echo $arr[0] ?></a></li>
    <?php endforeach;
    echo '</ul>';
}

function msp_get_product_videos_tab(){
    global $post;
    $resources = msp_get_product_videos( $post->ID );

    echo '<h2>Product Videos</h2>';
    foreach( $resources as $arr ) : ?>
        <div class="embed-responsive embed-responsive-16by9 mb-2">
            <iframe class="embed-responsive-item" src="<?php echo $arr[0] ?>"allowfullscreen></iframe>
        </div>
    <?php endforeach;
}

function msp_show_product_size_guide_btn(){
    global $product;
    $size_guide = get_post_meta( $product->get_id(), '_msp_size_guide', true );
    if( ! empty( $size_guide ) ){
        make_modal_btn( array(
            'text' => 'Size Guide',
            'title' => $product->get_name() . ' - Size Guide',
            'model' => 'size_guide',
            'action' => 'show',
            'id' => $product->get_id(),
        ));
    }
}

function msp_shameless_self_plug(){
    ?>
    <p class="text-center bg-dark text-light m-0 p-0">
        <a class="text-light link-normal" href="http://drunk.kiwi">Made with <i class="fas fa-coffee mx-2"></i> & <i class="fas fa-heart text-danger mx-2"></i> by Greg Bastianelli</a>
    </p>
    <?php
}

function msp_dynamic_modal(){
    ?>
    <div class="modal fade" id="msp_modal" tabindex="-1" role="dialog" aria-labelledby="msp_modal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="msp_modal">Modal title</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <div class="spinner-border text-success" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
        </div>

    <?php
}

function msp_get_product_size_guide_src(){
    echo get_post_meta( $_POST['id'], '_msp_size_guide', true );
    wp_die();
}

function msp_get_leave_feedback_form(){
    ob_start();
    wc_get_template( '/template/msp-leave-feedback-form.php' );
    $html = ob_get_clean();
    echo $html;
    wp_die();
}

function msp_process_feedback_form(){
    $form_data = array();
    parse_str( $_POST['form_data'], $form_data );

    if( empty( $form_data['rating'] ) ) return;
    $user = wp_get_current_user();

    $comment_id = wp_insert_comment( array(
        'comment_post_ID' => 0,
        'comment_author'	=> $user->user_login,
        'comment_author_email'	=> $user->user_email,
        'comment_author_url'	=> $user->user_url,
        'comment_content' =>  $form_data['comments'],
        'comment_type'			=> 'store_review',
        'comment_author_IP' => $_SERVER['REMOTE_ADDR'],
        'comment_agent' => $_SERVER['HTTP_USER_AGENT'],
        'comment_date' => current_time( 'mysql', $gmt = 0 ),
        'user_id' => get_current_user_id(),
        'comment_approved' => 1,
    ) );

    update_comment_meta( $comment_id, 'rating', $form_data['rating'] );
    echo $comment_id;
    wp_die();
}

function commerce_connector_tracking( $order_id ){
    $order = wc_get_order( $order_id );
    $product_str = 'https://www.commerce-connector.com/tracking/tracking.gif?shop=1234567890ABC&';
    $count = 0;
    foreach( $order->get_items() as $order_item ){
        $product_id = ( $order_item->get_variation_id() != 0 ) ? $order_item->get_variation_id() : $order_item->get_product_id();
        $product = wc_get_product( $product_id );
            $gpf = get_post_meta( $product->get_id(), '_woocommerce_gpf_data', true );
            $product_str .= sprintf( '&ean[%d]=%s&sale[%d]=%d', $count, $gpf['gtin'], $count, $order_item['quantity'] );
        $count++;
    }
    ?>
    <img src="<?php echo $product_str ?>" width="1" height="1" border="0"/>
    <?php
}

function msp_get_additional_information( $product ){
    echo apply_filters( 'msp_additional_information_html', $product );
}

function msp_get_product_pool( $product ){
    return ( $product->get_children() ) ? $product->get_children() : array( $product->get_id() );
}

function msp_get_product_metadata( $product_ids ){
    $data_sets = array( 'sku' => '_sku', 'gtin' => '_woocommerce_gpf_data' );
    foreach( $data_sets as $label => $meta_key ){
        $str = '';
        foreach( $product_ids as $id ){
            $product = wc_get_product( $id );
            $data = get_post_meta( $id, $meta_key, true );
            if( is_array( $data ) ){
                $data = $data[$label];
            }

            if( ! empty( $data ) ){
                $str .= '<a href="'. $product->get_permalink() .'">'. $data .'</a>, ';
            }
        }
        $data_sets[$label] = $str;
    }
    return $data_sets;
}

function msp_product_additional_information_html( $inner_html ){
    if( empty( $inner_html ) ) return;
    echo '<table>';
    foreach( $inner_html as $label => $value ) : ?>
        <tr class="woocommerce-product-attributes-item">
            <th class="woocommerce-product-attributes-item__label"><?php echo ucfirst($label); ?></th>
            <td class="woocommerce-product-attributes-item__value"><?php echo $value ?></td>
        </tr>
    <?php endforeach;
    echo '</table>';
}