<?php

defined( 'ABSPATH' ) || exit;

/**
 * @see Hook: msp_header
 */

/**
 * Opens the header wrapper
 */
function msp_header_wrapper_open(){
    echo '<nav class="navbar navbar-expand-lg navbar-light bg-dark"><div class="container align-items-end">';
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
    echo '<div id="search-wrapper" class="flex-grow-1">';
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
    echo '<div id="header-menu" class="d-none d-sm-flex align-items-end">';

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
    get_msp_quote_find_product_id_form();
    wc_get_template( '/template/msp-quote.php' );
}
/**
 * Uses Select2 to allow a user easily find and add products to a list for quote.
 * https://rudrastyh.com/wordpress/select2-for-metaboxes-with-ajax.html#respond
 */
function get_msp_quote_find_product_id_form(){
    $product_ids = isset( $_GET['p'] ) ? msp_get_products() : array();

    $html = '<label for="msp_select2_products">Products:</label><br />';
    $html .= '<form method="GET" class="form-inline"><select id="msp_select2_products" name="ids[]" multiple="multiple" class="form-control w-100">';
    if( $product_ids ){
        foreach( $product_ids as $id ) {
            $title = get_the_title( $id );
            $title = ( mb_strlen( $title ) > 50 ) ? mb_substr( $title, 0, 49 ) . '...' : $title;
            $html .=  '<option value="' . $id . '" selected="selected">' . $title . '</option>';
        }
    }
    $html .= '</select><button role="submit" class="btn btn-success" />Submit</button></form>';
    echo $html;
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
        $customer_msg = "<p>We got your message, expect a response in 1-3 business days. Your quote is below: </p>";
        wp_mail( get_option('admin_email'), $sitename . ' - Quote Request', $html );
        wp_mail( $_POST['email'], 'We got your quote request!', $customer_msg . $html );
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
function msp_get_customer_unique_order_items( $user_id){
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
    echo "<a class='close'><i class='fas fa-times'></i></a>";
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
            <?php woocommerce_order_again_button( $order ); ?>
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

    if( $order->get_status() == 'completed' && ! empty( $tracking_info['shipper'] ) ) :
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
		$date_str = iww_make_date( [1] );
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
 * @param array[int] $dates - An array of numbers representing the numberd of days until delivery
 * @return string 
 */
function iww_make_date( $dates ){
    date_default_timezone_set('EST');
    $day_of_the_week = date('N');
    $hour_of_the_day = date('G');
    
    $date_str = '';

    foreach( $dates as $key => $date ){
        if( $key != 0 ) $date_str .= ' - ';

        if( $day_of_the_week > 4 ){
            if( $day_of_the_week == 5 && $hour_of_the_day < 12 ) $date--;
            $date_str .= date( 'l, F jS', strtotime( '+'. $date .'days', strtotime( 'next monday' ) ) );
        } else {
            if( $hour_of_the_day > 12 ) $date++;
            $date_str .= date( 'l, F jS', strtotime( '+'. $date .'days' ) );
        }

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
function msp_order_feedback_button( $order ){
    make_modal_btn( array(
        'type'  => 'button',
        'class' => 'btn btn-secondary btn-block',
        'text'  => "<i class='far fa-comments'></i>Leave Feedback",
        'title' => 'Leave Us Feedback',
        'model' => 'leave_feedback',
        'action' => 'get',
        'id'    => $order->get_id(),
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
        <a href="tel:8887233864" role="button" class="btn btn-warning btn-block"><i class="fas fa-cube"></i>Return or replace items</a>
    <?php
}

/**
 * Simple modal designed to email store owner to potential problems.
 */
function msp_order_report_issue_button(){
    ?>
        <a href="tel:8887233864" role="button"class="btn btn-danger btn-block"><i class="fas fa-exclamation-circle"></i>Problem with order</a>
    <?php
}

function msp_get_resources_tab(){
    /**
     * A callback used to an HTML list based on product meta data
     * @see msp_get_product_resources()
     */
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
    /**
     * A callback used to an HTML list based on product meta data
     * @see msp_get_product_resources()
     */
    global $post;
    $resources = msp_get_product_videos( $post->ID );

    echo '<h2>Product Videos</h2>';
    foreach( $resources as $arr ) : ?>
        <div class="embed-responsive embed-responsive-16by9 mb-2">
            <iframe class="embed-responsive-item" src="<?php echo $arr[0] ?>" allowfullscreen></iframe>
        </div>
    <?php endforeach;
}

function msp_show_product_size_guide_btn(){
    /**
     * Creates a link to the dynamic modal ( modal.js ) if has size_guide attached. ( custom meta box )
     * @see msp_get_product_resources()
     */
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
    /**
     * Simply says that I made this website / theme.
     */
    ?>
    <p class="text-center bg-dark text-light m-0 p-0">
        <a class="text-light link-normal" href="http://drunk.kiwi">Made possible by <i class="fas fa-coffee mx-2"></i> & <i class="fas fa-heart text-danger mx-2"></i></a>
    </p>
    <?php
}

function msp_dynamic_modal(){
    /**
     * Creates the HTML for a dynamic bootstrap modal
     * @see js\modal.js
     */
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
    /**
     * returns the meta value _msp_size_guide - used for AJAX.
     * @return string - path to uploaded size guide ( custom meta box ).
     */
    echo get_post_meta( $_POST['id'], '_msp_size_guide', true );
    wp_die();
}

function msp_get_leave_feedback_form(){
    /**
     * Simply gets the HTML template for the feedback form.
     */
    ob_start();
    set_query_var('order_id', $_POST['id']);
    wc_get_template( '/template/msp-leave-feedback-form.php' );
    $html = ob_get_clean();
    echo $html;
    wp_die();
}

function msp_process_feedback_form(){
    /**
     * Either creates a new comment or edits an older one, based on whether or not the user has
     * reviewed a product.
     * 
     * @return string - $comment_id
     */
    $form_data = array();
    $user = wp_get_current_user();
    parse_str( $_POST['form_data'], $form_data );

    if( empty( $form_data['rating'] ) ) return;

    $args = array(
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
    );

    $comment = msp_customer_feedback( $form_data['order_id'] );
    if( ! empty( $comment ) ){
        $args['comment_ID'] = $comment->comment_ID;
        $comment_id = $args['comment_ID'];
        wp_update_comment($args);
    } else {
        $comment_id = wp_insert_comment($args);
    }

    
    update_comment_meta( $comment_id, 'rating', $form_data['rating'] );
    update_comment_meta( $comment_id, 'order_id', $form_data['order_id'] );
    echo $comment_id;
    wp_die();
}

function commerce_connector_tracking( $order_id ){
    /**
     * Integration with Commerce Connector and hooked into woocommerce_thankyou
     * @param int $order_id
     */
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
    /**
     * Simply outputs the html returned by the msp_additional_information_html filter.
     * @see msp_additional_information_html()
     */
    echo apply_filters( 'msp_additional_information_html', $product );
}

function msp_template_loop_product_link_open(){
    /**
     * Opens up another a tag within the content-product.php
     */
    echo '<a href="'. get_permalink() .'">';
}

function msp_get_shop_subnav(){
    /**
     * Outputs the html for a subnav if applicable
     */
    $nav_items = msp_get_category_children();
    if( empty( $nav_items ) || wp_is_mobile() ) return;

    array_unshift( $nav_items, msp_get_current_category() );
    ?>
        <nav class="navbar navbar-light bg-light msp-shop-subnav border-bottom">
            <div class="navbar-nav flex-row">
                <?php foreach( $nav_items as $item ) : ?>
                    <li class="nav-item border-right px-2">
                        <a class="nav-link" href="<?php echo get_term_link( $item->term_id ) ?>" ><?php echo $item->name ?></a>
                    </li>
                <?php endforeach; ?>
            </div>
        </nav>
    <?php
}

function msp_customer_faq(){
    wc_get_template( '/template/msp-customer-faq.php' );
}

function msp_contact_btn(){
    ?>
    <li class="nav-item">
        <a class="nav-link" href="/contact">
            Contact Us
        </a>
    </li>
    <?php
}

add_shortcode( 'contact', 'msp_get_contact_page' );
function msp_get_contact_page(){
    ob_start();
    wc_get_template('/template/msp-contact.php');
    echo ob_get_clean();
}

function msp_process_contact_form(){
    if( ! empty( $_POST ) ){
        $to = get_bloginfo( 'admin_email' );
        $subject = ( ! empty( $_POST['subject'] ) ) ? $_POST['subject'] : wp_trim_words( $_POST['message'], 25 );
        $headers = array(
            'Content-Type: text/html; charset=UTF-8', 
            'Reply-To:' . $_POST['email']
        );

        ob_start();
        ?>
        <h4><?php echo $subject; ?></h4>
        <p><?php echo $_POST['message']; ?></p>
        <br>
        <p>Reply to: <?php echo $_POST['name'] . ' - ' . $_POST['email'] ?></p>
        <hr>
        <p>Sent from <?php echo get_bloginfo( 'url' ) . '/contact' ?></p>
        <?php
        $html = ob_get_clean();

        wp_mail( $to, $subject, $html, $headers );
        wp_mail( $_POST['email'], $subject, $html, $headers );
        wp_redirect( '/' );
        exit;
    }
}

function msp_add_google_analytics(){
    $user_account = get_option( 'integration_google_analytics_account_id' );
    if( empty( $user_account ) ) return;

    ?>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $user_account ?>"></script>

    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '<?php echo $user_account ?>');
    </script>

    <?php
}

function msp_user_email_preferences(){
    $display_name = get_user_meta( $_GET['user_id'], 'nickname', true );
    if( wp_verify_nonce( $_REQUEST['_wpnonce'], 'update-user-' . $display_name ) ){
        update_user_meta( $_GET['user_id'], '_can_email', 0 );
        wc_add_notice( 'Email preferences updated.', 'success' );
    } 

}

function msp_maybe_append_description(){
    global $product;
    $the_content = get_the_content();
    foreach( $product->get_category_ids() as $id ){
        $category = get_term( $id );
        if( ! empty( $category->description ) ){
            $the_content .= '<p>' . $category->description . '</p>';
            // echo $category->description;
        }
    }
    echo $the_content;
}

add_action( 'init', 'jk_remove_storefront_handheld_footer_bar' );

function jk_remove_storefront_handheld_footer_bar() {
  remove_action( 'storefront_footer', 'storefront_handheld_footer_bar', 999 );
}

function msp_add_sub_cat_links(){
    $nav_items = msp_get_category_children();
    if( empty( $nav_items ) ) return;
    $echo = 'Shop for ';
    echo '<p class="text-left">';
    foreach( $nav_items as $item ){
        $echo .= '<a href="'. get_term_link( $item->term_id ) .'">'. $item->name .'</a>, ';
    }
    echo rtrim($echo, ', ') . '.</p>';
}

define('temp_file', ABSPATH.'/_temp_out.txt' );

add_action("activated_plugin", "activation_handler1");
function activation_handler1(){
    $cont = ob_get_contents();
    if(!empty($cont)) file_put_contents(temp_file, $cont );
}

add_action( "pre_current_active_plugins", "pre_output1" );
function pre_output1($action){
    if(is_admin() && file_exists(temp_file))
    {
        $cont= file_get_contents(temp_file);
        if(!empty($cont))
        {
            echo '<div class="error"> Error Message:' . $cont . '</div>';
            @unlink(temp_file);
        }
    }
}