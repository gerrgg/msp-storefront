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
            $items = $order->get_items();
            foreach( $items as $item ){
                array_push( $order_items, $item->get_product_id() );
            }
        }
    }

    return array_unique( $order_items );
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
            <?php $history->get_user_products_history(); ?>
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
		$date_str = iww_make_date( [5, 15] );
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

    ?>
        <a href="<?php echo msp_get_review_link( $id_arr ) ?>" role="button" class="btn btn-info btn-block link-normal">
            <i class="fas fa-edit"></i> Write a Product Review
        </a>
    <?php
}

/**
 * Simple Modal Feedback Form - Ask how we can improve? Suggestions?
 */
function msp_order_feedback_button(){
    ?>
        <button type="button" class="btn btn-secondary btn-block"><i class="far fa-comments"></i>Leave Feedback</button>
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


function msp_chevron_karma_form( $comment ){
    if( ! $comment->comment_approved ) return;
    global $history;
    $vote = $history->get_karma_vote( $comment->comment_ID );
  ?>
    <div class="d-flex flex-column mx-auto text-center mt-3">
        <i class="fas fa-chevron-circle-up text-secondary fa-2x mb-1 karma karma-up-vote <?php if( $vote == 1 ) echo 'voted'; ?>"></i>
        <span class="mb-1 karma-score"><?php echo $comment->comment_karma ?></span>
        <i class="fas fa-chevron-circle-down text-secondary fa-2x karma karma-down-vote <?php if( $vote == -1 ) echo 'voted'; ?>" ></i>
    </div>
  <?php  
}

function msp_comment_actions_wrapper_open(){
    echo '<div class="comment-actions">';
}

function msp_reply_to_comment_btn( $comment ){
    ?>
    <button class="btn btn-outline-secondary comment-on-comment">
        Comment
        <i class="far fa-comment-alt pl-2"></i>
    </button>
    <?php
}

function msp_flag_comment_btn( $comment ){
    ?>
    <button class="btn btn-outline-danger flag-comment">
        Report Abuse
        <i class="fab fa-font-awesome-flag"></i>
    </button>
    <?php
}

function msp_comment_actions_wrapper_close(){
    echo '</div><!-- .comment-actions -->';
}


function msp_get_create_a_review_btn(){
    global $post;
    $url = msp_get_review_link( $post->ID );
    echo '<p class=""><a href="'. $url .'" role="button" class="btn btn-success btn-lg">Write a customer review</a></p>';
}

function msp_get_rating_histogram( $ratings, $count, $echo = true ){
    ob_start();
    ?>
        <table class="product-rating-histogram">
            <?php 
                for( $i = 5; $i > 0; $i-- ) :
                    $now = ( isset( $ratings[$i] ) ) ? intval( ( $ratings[$i] / $count ) * 100 ) : 0; ?>
                    <tr>
                        <td nowrap>
                            <a href=""><?php echo $i ?> stars</a>
                        </td>
                        <td style="width: 80%">
                            <a class="progress">
                                <div class="progress-bar" role="progressbar" style="width: <?php echo $now; ?>%"aria-valuenow="<?php echo $now ?>%" aria-valuemin="0" aria-valuemax="100"></div>
                            </a>
                        </td nowrap>
                        <td>
                            <a href=""><?php echo $now ?>%</a>
                        </td>
                    </tr>
                <?php endfor; ?>
            </table>
    <?php
    $html = ob_get_clean();

    if( ! $echo ){
        return $html;
    }

    echo $html;
}

function msp_single_product_create_review(){
    ?>
    <hr />
    <h3>Review this product</h3>
    <p>Share your thoughts with other customers.</p>
    <?php
        msp_get_create_a_review_btn();
}

add_shortcode( 'review' , 'msp_get_review_template' );
function msp_get_review_template(){
    wc_get_template( '/template/msp-review.php' );
}

function msp_review_more_products(){
    if( ! isset( $_GET['product_id'] ) ) return;
    $product_ids = explode( ',', $_GET['product_id'] );

    if( sizeof( $product_ids ) <= 1 ) return;

    foreach( $product_ids as $id ){
        $product = wc_get_product( $id );
        if( ! empty( $product ) ){
            ?>
            <div class="col-4">
                <a href="<?php echo $product->get_permalink() ?>" class="pt-5 mt-3 text-center link-normal">
                    <img src="<?php echo msp_get_product_image_src( $product->get_image_id() ) ?>" class="mx-auto" />
                    <p class="shorten link-normal text-dark"><?php echo $product->get_name() ?></p>
                    <?php msp_get_review_more_star_links( $product->get_id() ) ?>
                </a>
            </div>
            <?php
        }
    }
}

function msp_get_review_more_star_links( $product_id, $echo = true ){
    $comment = msp_get_user_product_review( $product_id );
    $highlight = 'far';

    if( ! empty( $comment ) ){
        $rating = get_comment_meta( $comment['comment_ID'], 'rating', true );
    }

    ob_start();

    echo '<div class="d-flex justify-content-center">';
    for( $i = 1; $i <= 5; $i++ ) :
        if( isset( $rating ) ){
            $highlight = ( $i <= $rating ) ? 'fas' : 'far';
        }
    ?>
        <a href="<?php echo msp_get_review_link( $product_id, array('star' => $i) ) ?>" class="link-normal">
            <i class="<?php echo $highlight; ?> fa-star fa-2x"></i>
        </a>
    <?php endfor;
    echo '</div>';

    $html = ob_get_clean();

    if( ! $echo ) return $html;
    echo $html;
}

function msp_get_review_link( $product_id, $args = array() ){
    $comment = msp_get_user_product_review( $product_id );

    $base_url = '/review/?product_id=';
    $base_url .= is_array($product_id) ? implode( ',', $product_id ) : $product_id;

    $defaults = array(
        'action' => ( empty( $comment ) ) ? 'create' : 'edit',
        'comment_id' => '',
        'star' => ''
    );

    $args = wp_parse_args( $args, $defaults );

    foreach( $args as $key => $arg ){
        if( ! empty( $arg ) ) $base_url .= "&$key=$arg"; 
    }

    return $base_url;
}

function msp_create_review_wrapper_open(){
    echo '<div class="col-12">';
    echo '<form method="POST" action="'. admin_url( 'admin-post.php' ) .'" enctype="multipart/form-data">';
}

function msp_create_review_top( $product_id ){
    $src = msp_get_product_image_src_by_product_id( $product_id );
    ?>
    <div class="d-flex align-items-center mt-2 mb-4 pb-4 border-bottom">
        <img src="<?php echo $src; ?>" class="img-mini pr-3">
        <p class="m-0 p-0"><?php echo get_the_title( $product_id ); ?></p>
    </div>
    <?php
}

function msp_get_review_more_star_buttons(){
    $class = 'far';

    echo '<h3>Overall Rating</h3>';
    echo '<div class="d-flex pb-2">';

    for( $i = 1; $i <= 5; $i++ ) :
        if( isset( $_GET['star'] ) ){
            $class = ( $i <= $_GET['star'] ) ? 'fas' : 'far';
        }
    ?>

        <a class="link-normal" href="javascript:void(0)">
            <i class="<?php echo $class; ?> fa-star fa-2x msp-star-rating rating-<?php echo $i ?>" data-rating="<?php echo $i; ?>"></i>
        </a>

    <?php endfor;

    echo '</div>';
    echo '<input type="hidden" id="rating" name="rating" value="" />';
}


function msp_create_review_upload_form( $product_id ){
    if( ! is_user_logged_in() ) return;

    // if $product_id then get all images uploaded by this review

    echo '<div class="pt-4">';
        echo '<h3>Add a photo or video</h3>';
        echo '<p>Shoppers find images and videos more helpful than text alone.</p>';
        echo '<input type="file" name="file" />';
    echo '</div>';
    
}

function msp_create_review_headline( $product_id ){
    $headline = '';
    if( $_GET['action'] == 'edit' ){
        $comment = msp_get_user_product_review( $product_id );
        $headline = get_comment_meta( $comment['comment_ID'], 'headline', true );
    }

    echo '<div class="pt-4">';
        echo '<h3>Add a headline</h3>';
        echo '<input required type="text" name="headline" placeholder="What\'s the most important thing to know?" class="form-control w-50" value="'. $headline .'" />';
    echo '</div>';
}

function msp_create_review_content( $product_id ){
    $content['comment_content'] = '';
    if( $_GET['action'] == 'edit' ){
        $content = msp_get_user_product_review( $product_id );
    }
    echo '<div class="pt-4">';
        echo '<h3>Write your review</h3>';
        echo '<textarea required name="content" class="form-control w-75" placeholder="What did you like or dislike? What did you use this product for?">'. $content['comment_content'] .'</textarea>';
    echo '</div>';
}

function msp_create_review_wrapper_close(){
                echo '<div class="pt-4">';
                    wp_nonce_field( 'create-review_' . $_GET['product_id'] );
                    echo '<input type="hidden" name="product_id" value="'. $_GET['product_id'] .'" />';
                    echo '<input type="hidden" name="action" value="msp_process_create_review" />';
                    echo '<button class="btn btn-success submit-review" />Submit</button>';
                echo '</div>';
            echo '</form>';
        echo '</div> <!-- .row -->';
}

function msp_process_create_review(){
    if( check_admin_referer( 'create-review_' . $_POST['product_id'] ) ){
        $data = $_POST;
        $user = wp_get_current_user();
        
        $args = array(
            'comment_post_ID' => $data['product_id'],
            'comment_author'	=> $user->user_login,
            'comment_author_email'	=> $user->user_email,
            'comment_author_url'	=> $user->user_url,
            'comment_content' =>  $data['content'],
            'comment_type'			=> 'review',
            'comment_author_IP' => $_SERVER['REMOTE_ADDR'],
            'comment_agent' => $_SERVER['HTTP_USER_AGENT'],
            'comment_date' => current_time( 'mysql', $gmt = 0 ),
            'user_id' => get_current_user_id(),
            'comment_approved' => 0,
        );
        
        $comment = msp_get_user_product_review( $data['product_id'] );

        if( ! is_null( $comment ) ){
            // comment_id needs to be available for after this if statement.
            $comment_id = $comment['comment_ID'];
            $args['comment_ID'] = $comment['comment_ID'];
            wp_update_comment($args);
        } else {
            $comment_id = wp_insert_comment( $args );
        }

        update_comment_meta( $comment_id, 'rating', $data['rating'] );
        update_comment_meta( $comment_id, 'headline', $data['headline'] );

        $verified = ( wc_customer_bought_product( $user->user_email, get_current_user_id(), $data['product_id'] ) ) ? 1 : 0;
        update_comment_meta( $comment_id, 'verified', $verified);

        //redirect to review more products!
        $review_more_ids = msp_get_customer_unique_order_items( get_current_user_id() );
        wp_redirect( msp_get_review_link( $review_more_ids, array('action' => 'show_more') ) );
    }
}

function msp_get_comment_headline( $comment ){
    $headline = get_comment_meta( $comment->comment_ID, 'headline', true );
    if( ! empty( $headline ) ){
        echo '<h4 class="review-headline">'. $headline .'</h4>';
    }
}

/**
 * Updates a users karma vote on a comment.
 */
function msp_add_to_karma_table(){
    if( ! isset( $_POST['comment_id'], $_POST['vote'] ) ) return;
    global $wpdb;
    $table_name = 'msp_karma';

    $last_vote = msp_get_user_karma_vote( get_current_user_id(), $_POST['comment_id'] );

    $args = array(
        'karma_user_id'    => get_current_user_id(),
        'karma_comment_id' => $_POST['comment_id'],
        'karma_value'      => $_POST['vote']
    );

    if( empty( $last_vote ) ){
        $wpdb->insert( $table_name, $args );
    } else {
        $wpdb->update( $table_name, $args, array( 'karma_id' => $last_vote->karma_id ) );
    }

    $karma_score = msp_update_comment_karma( $_POST['comment_id'] );
    wp_send_json( $karma_score );

    wp_die();
}

function msp_update_comment_karma( $comment_id ){
    $comment = get_comment( $comment_id, ARRAY_A );
    if( empty( $comment ) ) return;

    global $wpdb;
    $score = 0;

    $results = $wpdb->get_results(
        "SELECT karma_value
         FROM msp_karma
         WHERE karma_comment_id = $comment_id"
    );

    foreach( $results as $vote ){
        $score += $vote->karma_value;
    }

    $comment['comment_karma'] = $score;
    wp_update_comment( $comment );
    
    return $score;
}

function msp_get_user_karma_vote( $user_id, $comment_id ){
    global $wpdb;

    $row = $wpdb->get_row( 
        "SELECT * 
         FROM msp_karma
         WHERE karma_user_id = $user_id
         AND karma_comment_id = $comment_id" 
    );

    return $row;
}

