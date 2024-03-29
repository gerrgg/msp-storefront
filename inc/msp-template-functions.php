<?php

defined("ABSPATH") || exit();

/**
 * Opens the header wrapper
 */
function msp_header_wrapper_open()
{
  echo "<nav class='navbar'><div class='container'>";
}

/**
 * Displays the html button for opening and closing the mobile menu
 */
function msp_header_mobile_menu_button()
{
  echo '<button class="btn mobile-menu-button"><i class="fas fa-bars fa-2x"></i></button>';
}

/**
 * Gets the id, src and specified width of the sites logo and displays it.
 */
function msp_header_site_identity()
{
  $logo_id = get_theme_mod("custom_logo");
  $logo_src = wp_get_attachment_image_src($logo_id, "medium");

  if (has_custom_logo() && !empty($logo_src)) {
    echo '<a class="navbar-brand" href="' .
      get_bloginfo("url") .
      '"><img src="' .
      $logo_src[0] .
      '" /></a>';
  } else {
    echo '<a class="navbar-brand" href="' .
      get_bloginfo("url") .
      '">' .
      bloginfo("sitename") .
      "</a>";
  }
}

/**
 * Opens the header middle wrapping div
 */
function msp_header_middle_open()
{
  echo '<div id="hidden-cart-button" class="d-lg-none">';
  msp_header_cart();
  echo "</div>";
  echo '<div id="search-wrapper" class="flex-grow-1">';
}

/**
 * outputs the html generate by the "wcas-search-form" shortcode.
 * shortcode is created by https://wordpress.org/plugins/ajax-search-for-woocommerce/
 */
function msp_header_search_bar()
{
  echo do_shortcode("[wcas-search-form]");
}

/**
 * outputs the header navigation
 */
function msp_header_menu()
{
  echo '<div id="header-menu" class="d-none d-lg-flex align-items-end">';

  do_action("msp_quick_links");

  echo '<div class="d-flex align-items-end ml-auto">';
  msp_header_right_menu();
  msp_header_cart();
  echo "</div>";

  echo "</div>";
}
/**
 * Opens up the center mid navbar
 */

function msp_quick_links_wrapper_open()
{
  echo '<ul class="navbar-nav m-0">';
}

/**
 * displays a navigation link based on whether or not the user has previous orders.
 */
function msp_buy_again_btn()
{
  $order_items = msp_get_customer_unique_order_items(get_current_user_id());

  if (!empty($order_items) && is_user_logged_in()): ?>
        <li class="nav-item buy-again">
            <a class="nav-link" href="<?php echo get_bloginfo(
              "url"
            ); ?>/buy-again">
                Buy Again
            </a>
        </li>
        <?php endif;
}

function msp_shop_btn()
{
  ?>
        <li class="nav-item">
            <a class="nav-link" href="<?php echo get_bloginfo("url"); ?>/shop">
                Shop
            </a>
        </li>
    <?php
}

/**
 * displays a navigation link for quote requests
 */
function msp_quote_btn()
{
  ?>
    <li class="nav-item">
        <a class="nav-link" href="<?php echo get_bloginfo("url"); ?>/quote">
            Request Quote
        </a>
    </li>
    <?php
}

/**
 * shortcode used to display two differant forms depending on the value of $_GET['input']
 * if we got a product from the input, show quote form, else show find_product_id_from
 */
add_shortcode("quote", "msp_quote_shortcode");
function msp_quote_shortcode()
{
  get_msp_quote_find_product_id_form();
  wc_get_template("/template/msp-quote.php");
}

/**
 * Uses Select2 to allow a user easily find and add products to a list for quote.
 * https://rudrastyh.com/wordpress/select2-for-metaboxes-with-ajax.html#respond
 */
function get_msp_quote_find_product_id_form()
{
  $product_ids = isset($_GET["q"]) ? msp_get_products() : [];
  $html =
    "<h4>Enter the part number(s) or name(s) of the item(s) you want quoted. Submit, tell us how many, where its going and expect an email same or next business day.</h4>";

  $html .= '<label for="msp_select2_products">Products:</label><br />';
  $html .=
    '<form method="GET" class="form-inline"><select id="msp_select2_products" name="ids[]" multiple="multiple" class="form-control" style="width: 400px">';
  if ($product_ids) {
    foreach ($product_ids as $id) {
      $title = get_the_title($id);
      $title =
        mb_strlen($title) > 50 ? mb_substr($title, 0, 49) . "..." : $title;
      $html .=
        '<option value="' .
        $id .
        '" selected="selected">' .
        $title .
        "</option>";
    }
  }
  $html .=
    '</select><button role="submit" class="btn btn-success" />Submit</button></form>';
  echo $html;
}

/**
 * Processes the data passed from the get_msp_quote_form, formats it and delivers to admin_email.
 */
function msp_submit_bulk_form()
{
  $to = get_option("msp_contact_email");
  if (empty($to)) {
    $to = get_option("admin_email");
  }

  $headers = [
    "Content-Type: text/html; charset=UTF-8",
    "Reply-To:" . $_POST["email"],
  ];

  $sitename = get_bloginfo("name");

  $products_arr = [];
  foreach ($_POST["product"] as $id => $qty) {
    if (!empty($qty)) {
      $product = wc_get_product($id);
      $products_arr[$id] = [
        "qty" => $qty,
        "name" => $product->get_formatted_name(),
      ];
    }
  }

  if (!empty($products_arr)) {
    ob_start(); ?>
        <h1>Bulk Quote Request</h1>
        <h2>Ship To</h2>
        <p>Reply To: <?php echo $_POST["name"]; ?> <<?php echo $_POST[
   "email"
 ]; ?>> </p>
        <address>
            Address: <?php echo $_POST["street"] . ", " . $_POST["zip"]; ?>
        <address>
        <hr>
        <table>
            <thead>
                <th>Name</th>
                <th>Quantity</th>
            </thead>
            <tbody>
                <?php foreach ($products_arr as $id => $item) {
                  echo "<tr>";
                  echo "<td>" . $item["name"] . "</td>";
                  echo "<td>" . $item["qty"] . "</td>";
                  echo "</tr>";
                } ?>
            </tbody>
        </table>
        <?php
        $html = ob_get_clean();
        $customer_msg =
          "<p>We got your message, expect a response in 1-3 business days. Your quote is below: </p>";
        wp_mail($to, $sitename . " - Quote Request", $html, $headers);
        wp_mail(
          $_POST["email"],
          "We got your quote request!",
          $customer_msg . $html,
          $headers
        );
        wp_redirect("/");

  }
}

/**
 * Dynamically searches for a product in differant ways based on the value of the key
 * @param mixed $data - the input from the get_msp_quote_find_product_id_form() function.
 */
function msp_get_product_by_mixed_data($data)
{
  if (!empty($data)) {
    foreach ($data as $key => $query) {
      $func = "wc_get_product_id_by_" . $key;
      $product_id = $func($query);
      if (!empty($product_id)) {
        return wc_get_product($product_id);
      }
    }
  }
}

/**
 * A function which looks for a product by querying the DB for a post_title similar to $str
 * @param $str
 */
function wc_get_product_id_by_name($str)
{
  global $wpdb;
  return $wpdb->get_row(
    "SELECT ID FROM wp_posts WHERE post_title LIKE '%$str%'"
  );
}

/**
 * Simple shortcode used to display items already purchased by the user/customer.
 */
add_shortcode("buy_again", "msp_buy_again_shortcode");
function msp_buy_again_shortcode()
{
  $order_items = msp_get_customer_unique_order_items(get_current_user_id());
  echo '<div class="owl-carousel owl-theme">';
  foreach ($order_items as $id) {
    $product = wc_get_product($id);
    global $product;
    wc_get_template_part("content", "product-simple");
  }
  echo "</div>";
}

/**
 * Sorts through an array of customer orders, picks out the ids and stores them to $order_items.
 * @param int $user_id - The id of the user
 * @return array $order_items - An array of unique product ids purchased by the user.
 */
function msp_get_customer_unique_order_items($user_id)
{
  $order_items = [];
  $orders = wc_get_orders(["customer_id" => $user_id]);

  if (!empty($orders)) {
    foreach ($orders as $order) {
      foreach ($order->get_items() as $order_item) {
        $product = $order_item->get_product();
        if ($product && $product->is_visible()) {
          array_push($order_items, $product->get_id());
        }
      }
    }
  }

  return array_unique($order_items);
}

function msp_get_user_browsing_history()
{
  global $history;
  echo $history->get_user_products_history();

  wp_die();
}

/**
 * Outputs the navigation button linking to the users browsing history
 */
function msp_get_user_products_history_btn()
{
  global $history;

  if (!empty($history->data["products"])): ?>
        <li class="nav-item user-history">
            <a class="nav-link dropdown-toggle">
                Browsing History
            </a>
            <?php
    // $history->get_user_products_history();
    ?>
        </li>
        <?php endif;
}

/**
 * Closes the naviation menu in the center of the header.
 */
function msp_quick_links_wrapper_close()
{
  echo "</ul>";
}

/**
 * Closes the header middle wrapping div
 */
function msp_header_middle_close()
{
  echo "</div>";
}

/**
 * gets the 'secondary' menu - used for account info / cart.
 */
function msp_header_right_menu()
{
  wp_nav_menu([
    "depth" => 2, // 1 = no dropdowns, 2 = with dropdowns.
    "container" => "div",
    "container_id" => "header-right",
    "menu_class" => "navbar-nav m-0",
    "theme_location" => is_user_logged_in() ? "secondary" : "logged-out",
    "fallback_cb" => "WP_Bootstrap_Navwalker::fallback",
    "walker" => new WP_Bootstrap_Navwalker(),
  ]);
}

/**
 * Displays the html markup for the cart button on the header - includes # of items in cart
 */
function msp_header_cart()
{
  // if WC() cart not available - use a 0
  $cart_size = isset(WC()->cart)
    ? sizeof(WC()->cart->get_cart_contents())
    : 0; ?>
    <div class="d-flex cart-wrapper">
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
function msp_header_wrapper_close()
{
  echo "</div></nav>";
}

/**
 * Displays the html required for opening up the mobile menu div
 */
function msp_mobile_menu_wrapper_open()
{
  echo '<div id="mobile-menu">';
}

/**
 * Displays a nice greeting to a logged in user, and encourages non-logged in users to do so.
 */
function msp_mobile_menu_header()
{
  $user = get_userdata(get_current_user_id());
  $username = !empty($user->user_login)
    ? $user->user_login
    : "Sign up or login";
  echo "<h3 class='title py-2 pl-4 mb-1'>Hello, $username</h3>";
  echo "<a class='close'><i class='fas fa-times'></i></a>";
}

/**
 * gets the 'handheld' (mobile) menu.
 */
function msp_mobile_menu()
{
  echo '<p class="mobile-label">SHOP BY CATEGORY</p>';
  wp_nav_menu([
    "theme_location" => "handheld",
    "menu_id" => "mobile-menu-categories",
    "menu_class" => "m-0 list-unstyled mb-2",
    "depth" => 2,
    "fallback_cb" => "WP_Bootstrap_Navwalker::fallback",
    "walker" => new WP_Bootstrap_Navwalker(),
  ]);
}

/**
 * Custom Mobile Menu - Static
 */
function msp_mobile_menu_account_links()
{
  $login_text = is_user_logged_in() ? "My account" : "Login / Register"; ?>
    <p class="mobile-label">ACCOUNT & HELP</p>
    <ul class="m-0 list-unstyled">
        <li class="menu-item"><a href="<?php echo wc_get_page_permalink(
          "myaccount"
        ); ?>"> <i class="fas fa-user pr-3"></i><?php echo $login_text; ?></a></li>
        <li class="menu-item"><a href="<?php echo get_bloginfo(
          "url"
        ); ?>/contact"><i class="fas fa-question pr-3"></i>Help</a></li>
        <li class="menu-item"><a href="<?php echo get_bloginfo(
          "url"
        ); ?>/order-tracking"><i class="fas fa-truck pr-3"></i>Track my order</a></li>
        <li class="menu-item"><a href="<?php echo get_bloginfo(
          "url"
        ); ?>/quote"><i class="fas fa-pencil-alt pr-3"></i>Get a quote</a></li>
        <?php if (is_user_logged_in()): ?>
            <li class="menu-item"><a href="<?php echo wp_logout_url(
              "/"
            ); ?>"><i class="fas fa-sign-out-alt pr-3"></i>Sign out</a></li>
        <?php endif; ?>
    </ul>
    <?php
}

/**
 * Displays the html required for closing up the mobile menu div
 */
function msp_mobile_menu_wrapper_close()
{
  echo "</div> <!-- #mobile-menu -->";
}

/**
 * Displays for the user, the items on each order as well as introduce a new hook 'msp_order_details_actions'.
 */
function msp_order_details_html($order)
{
  ?>
    <tr class="border-top">
        <td colspan="4">
            <h4 class="text-success iww-date-estimate"><?php echo msp_get_order_estimated_delivery(
              $order->get_id()
            ); ?></h4>
            <?php foreach ($order->get_items() as $item) {
              $product = wc_get_product($item->get_product_id());
              if (!empty($product)) {
                $image_src = MSP::get_product_image_src(
                  $product->get_image_id()
                ); ?>
                        <div class="d-flex">
                            <a href="<?php echo $product->get_permalink(); ?>" style="width: 100px;">
                                <img src="<?php echo $image_src; ?>" style="height: 100px;" class="mb-2 mx-auto" />
                            </a>
                            <div class="pl-4">
                                <a class="link-normal" href="<?php echo $product->get_permalink(); ?>">
                                    <?php echo $product->get_name(); ?>
                                </a>
                                <p class="">Price: <span class="price">$<?php echo $product->get_price(); ?></span></p>
                                <p class="m-0">Qty: <?php echo $item->get_quantity(); ?></p>
                            </div>
                        </div>
                        <?php
              }
            } ?>
            <?php woocommerce_order_again_button($order); ?>
        </td>
        <td>
            <div class="order-actions btn-group-vertical text-align-left">
                    <?php do_action("msp_order_details_actions", $order); ?>
            </div>
        </td>
    </tr>
    <?php
}

/**
 * Displays an external link to track a package based on whether or not a tracking link is present.
 * @param WC_Order $order
 */
function msp_order_tracking_button($order)
{
  $tracking_info = [
    "shipper" => get_post_meta($order->get_id(), "shipper", true),
    "tracking" => get_post_meta($order->get_id(), "tracking", true),
  ];

  if ($order->get_status() == "completed" && !empty($tracking_info["shipper"])):
    $tracking_link = msp_make_tracking_link(
      $tracking_info["shipper"],
      $tracking_info["tracking"]
    ); ?>
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
function msp_make_tracking_link($shipper, $tracking)
{
  $base_urls = [
    "ups" => "https://www.ups.com/track?loc=en_US&tracknum=",
    "fedex" => "https://www.fedex.com/apps/fedextrack/?tracknumbers=",
    "usps" => "https://tools.usps.com/go/TrackConfirmAction?tLabels=",
  ];
  return $base_urls[$shipper] . $tracking;
}

/**
 * Makes an API tracking request to UPS for expected delivery date and updates the db entry.
 * @param int $order_id
 */
function msp_update_order_tracking($order_id)
{
  global $ups;
  $tracking = get_post_meta($order_id, "tracking", true);
  $date_est = $ups->track($tracking);
  if (!empty($date_est)) {
    update_post_meta($order_id, "msp_estimated_delivery_date", $date_est);
  }
}

/**
 * gets the msp_estimated_delivery_date meta value of the order
 * @param int $order_id - ID of the order.
 */
function msp_get_order_estimated_delivery($order_id)
{
  $est_date = get_post_meta($order_id, "msp_estimated_delivery_date", true);
  if (!empty($est_date)) {
    $string = msp_package_delivered($est_date, $order_id)
      ? "Delivered "
      : "Expected to deliver by ";
    return $string . $est_date;
  }
}

/**
 * Checks if the package delivered. If it does and has the order id, it deletes it.
 * @param string - $est_date
 * @param int - $order_id
 * @return bool - $delivered
 */
function msp_package_delivered($est_date, $order_id = 0)
{
  $delivered = time() > strtotime($est_date);

  if ($delivered && !empty($order_id)) {
    MSP_Admin::manage_cron_jobs("tracking", $order_id, false);
  }

  return $delivered;
}

/**
 * Sends a request to the UPS Time in Transit API
 * @param int $order_id
 * @return array $response - UPS Time in Transit API response
 */
function msp_get_ups_time_in_transit($order_id)
{
  global $ups;
  $order = wc_get_order($order_id);

  $ship_to = [
    "street" => $order->get_shipping_address_1(),
    "postal" => $order->get_shipping_postcode(),
    "country" => $order->get_shipping_country(),
  ];

  $response = $ups->time_in_transit($ship_to);

  if ($response["Response"]["ResponseStatusCode"]) {
    return $response["TransitResponse"]["ServiceSummary"];
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
function msp_update_order_estimated_delivery($order_id)
{
  global $ups;
  if (isset($_SESSION["msp_estimated_delivery_date"])) {
    update_post_meta(
      $order_id,
      "msp_estimated_delivery_date",
      $_SESSION["msp_estimated_delivery_date"]
    );
  }
}

/**
 * Takes in an array of dates, takes into account the current day and hour and returns a guess as to when the package should arrive.
 * @param array[int] $dates - An array of numbers representing the numberd of days until delivery
 * @return string
 */
function iww_make_date($dates)
{
  date_default_timezone_set("EST");
  $day_of_the_week = date("N");
  $hour_of_the_day = date("G");

  $date_str = "";

  foreach ($dates as $key => $number_of_days) {
    if ($key != 0) {
      $date_str .= " - ";
    }

    $delivers_on_day_of_week = date(
      "N",
      strtotime("+" . $number_of_days . "days")
    );

    if ($delivers_on_day_of_week == 6 || $delivers_on_day_of_week == 7) {
      $number_of_days += abs(8 - $delivers_on_day_of_week);
    }

    $date_str .= date("l, F jS", strtotime("+" . $number_of_days . "days"));
  }

  return '<h6 class="m-0 p-0 text-success iww-date-estimate">' .
    $date_str .
    "</h6>";
}

/**
 * Takes the $_POST value of an ajax call, stores it to $_SESSION and sends back the value just to confirm.
 * The session is used in msp_update_order_estimated_delivery() to update order meta.
 */
function msp_set_estimated_delivery_date()
{
  $est_date = explode(" - ", $_POST["date"]);
  $_SESSION["msp_estimated_delivery_date"] = end($est_date);
  wp_send_json($_SESSION["msp_estimated_delivery_date"]);
}

/**
 * Needs integration with custom reviews
 */
function msp_order_product_review_button($order)
{
  $id_arr = [];
  foreach ($order->get_items() as $order_item) {
    array_push($id_arr, $order_item->get_product_id());
  }

  $action = sizeof($id_arr) > 1 ? "show_more" : "create";
  ?>
        <a href="<?php echo msp_get_review_link($id_arr, [
          "action" => $action,
        ]); ?>" role="button" class="btn btn-info btn-block link-normal">
            <i class="fas fa-edit"></i> Write a Product Review
        </a>
    <?php
}

/**
 * Simple Modal Feedback Form - Ask how we can improve? Suggestions?
 */
function msp_order_feedback_button($order)
{
  make_modal_btn([
    "type" => "button",
    "class" => "btn btn-secondary btn-block",
    "text" => "<i class='far fa-comments'></i>Leave Feedback",
    "title" => "Leave Us Feedback",
    "model" => "leave_feedback",
    "action" => "get",
    "id" => $order->get_id(),
  ]); ?>
        <!-- <button type="button" class="btn btn-secondary btn-block"><i class="far fa-comments"></i>Leave Feedback</button> -->
    <?php
}

/**
 * Integration with UPS / USPS?
 */
function msp_order_return_button()
{
  ?>
        <a href="tel:8887233864" role="button" class="btn btn-warning btn-block"><i class="fas fa-cube"></i>Return or replace items</a>
    <?php
}

/**
 * Simple modal designed to email store owner to potential problems.
 */
function msp_order_report_issue_button()
{
  ?>
        <a href="tel:8887233864" role="button"class="btn btn-danger btn-block"><i class="fas fa-exclamation-circle"></i>Problem with order</a>
    <?php
}

function msp_get_resources_tab()
{
  /**
   * A callback used to an HTML list based on product meta data
   * @see msp_get_product_resources()
   */
  global $post;
  $resources = msp_get_product_resources($post->ID);

  echo "<h2>Resources</h2>";
  echo "<ul>";
  foreach ($resources as $arr): ?>
        <li><a target="_blank" href="<?php echo $arr[1]; ?>"><?php echo $arr[0]; ?></a></li>
    <?php endforeach;
  echo "</ul>";
}

function msp_get_product_videos_tab()
{
  /**
   * A callback used to an HTML list based on product meta data
   * @see msp_get_product_resources()
   */
  global $post;
  $resources = msp_get_product_videos($post->ID);

  if (empty($resources)) {
    return;
  }
  ?>

    <h4>Product Videos</h4>
    <div id="msp-product-videos" class="owl-carousel">
        <?php foreach ($resources as $arr):

          $video_url = str_replace(
            "https://www.youtube.com/embed/",
            "",
            $arr[0]
          );
          // check if video available
          if (getYoutubeVideoStatus($video_url) !== false): ?>

            <div class="embed-responsive embed-responsive-16by9 mb-2">
                <iframe class="embed-responsive-item" src="<?php echo $arr[0]; ?>" allowfullscreen></iframe>
            </div>
            <?php endif;
          ?>
        <?php
        endforeach; ?>
    </div>
    <?php
}

function msp_show_product_size_guide_btn()
{
  /**
   * Creates a link to the dynamic modal ( modal.js ) if has size_guide attached. ( custom meta box )
   * @see msp_get_product_resources()
   */
  global $product;
  $size_guide = get_post_meta($product->get_id(), "_msp_size_guide", true);
  if (!empty($size_guide)) {
    make_modal_btn([
      "text" => '<i class="fas fa-ruler pr-2"></i>Size Guide',
      "title" => $product->get_name() . " - Size Guide",
      "model" => "size_guide",
      "action" => "show",
      "id" => $product->get_id(),
    ]);
  }
}

function msp_shameless_self_plug()
{
  /**
     * Simply says that I made this website / theme.
     */
  ?>
    <p id="self-plug" class="text-center">
        <a href="http://gerrg.com">Made w/ <i class="fas fa-coffee"></i> & <i class="fas fa-heart"></i>.</a>
    </p>
    <?php
}

function msp_dynamic_modal()
{
  /**
     * Creates the HTML for a dynamic bootstrap modal
     * @see js\modal.js
     */
  ?>
    <div class="modal fade" id="msp_modal" tabindex="-1" role="dialog" aria-labelledby="msp_modal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header d-none d-sm-block">
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

function msp_get_product_size_guide_src()
{
  /**
   * returns the meta value _msp_size_guide - used for AJAX.
   * @return string - path to uploaded size guide ( custom meta box ).
   */
  echo get_post_meta($_POST["id"], "_msp_size_guide", true);
  wp_die();
}

function msp_get_image_src()
{
  /**
   * returns the src for attachment id - used for AJAX.
   */
  echo msp_get_product_image_src($_POST["id"], "full");

  wp_die();
}

function msp_get_promo_pop_up_link_and_image()
{
  /**
   * returns the src and link for promo image pop up - used for AJAX.
   */

  $response = [
    "link" => get_bloginfo("url") . "/" . get_option("promo_pop_up_link"),
    "src" => msp_get_product_image_src($_POST["id"], "full"),
  ];

  wp_send_json($response);

  wp_die();
}

function msp_get_leave_feedback_form()
{
  /**
   * Simply gets the HTML template for the feedback form.
   */
  ob_start();
  set_query_var("order_id", $_POST["id"]);
  wc_get_template("/template/msp-leave-feedback-form.php");
  $html = ob_get_clean();
  echo $html;
  wp_die();
}

function msp_process_feedback_form()
{
  /**
   * Either creates a new comment or edits an older one, based on whether or not the user has
   * reviewed a product.
   *
   * @return string - $comment_id
   */
  $form_data = [];
  $user = wp_get_current_user();
  parse_str($_POST["form_data"], $form_data);

  if (empty($form_data["rating"])) {
    return;
  }

  $args = [
    "comment_post_ID" => 0,
    "comment_author" => $user->user_login,
    "comment_author_email" => $user->user_email,
    "comment_author_url" => $user->user_url,
    "comment_content" => $form_data["comments"],
    "comment_type" => "store_review",
    "comment_author_IP" => $_SERVER["REMOTE_ADDR"],
    "comment_agent" => $_SERVER["HTTP_USER_AGENT"],
    "comment_date" => current_time("mysql", $gmt = 0),
    "user_id" => get_current_user_id(),
    "comment_approved" => 1,
  ];

  $comment = msp_customer_feedback($form_data["order_id"]);
  if (!empty($comment)) {
    $args["comment_ID"] = $comment->comment_ID;
    $comment_id = $args["comment_ID"];
    wp_update_comment($args);
  } else {
    $comment_id = wp_insert_comment($args);
  }

  update_comment_meta($comment_id, "rating", $form_data["rating"]);
  update_comment_meta($comment_id, "order_id", $form_data["order_id"]);
  echo $comment_id;
  wp_die();
}

function commerce_connector_tracking($order_id)
{
  /**
   * Integration with Commerce Connector and hooked into woocommerce_thankyou
   * @param int $order_id
   */
  $order = wc_get_order($order_id);

  $product_str =
    "https://www.commerce-connector.com/tracking/tracking.gif?shop=1234567890ABC&";
  $count = 0;

  foreach ($order->get_items() as $order_item) {
    $product_id =
      $order_item->get_variation_id() != 0
        ? $order_item->get_variation_id()
        : $order_item->get_product_id();
    $product = wc_get_product($product_id);
    $gpf = get_post_meta($product->get_id(), "_woocommerce_gpf_data", true);

    if (!empty($gpf["gtin"])) {
      $product_str .= sprintf(
        "&ean[%d]=%s&sale[%d]=%d",
        $count,
        $gpf["gtin"],
        $count,
        $order_item["quantity"]
      );
    }

    $count++;
  }
  ?>

    <img src="<?php echo $product_str; ?>" width="1" height="1" border="0"/>

    <?php
}

function msp_get_additional_information($product)
{
  /**
   * Simply outputs the html returned by the msp_additional_information_html filter.
   * @see msp_additional_information_html()
   */
  echo apply_filters("msp_additional_information_html", $product);
}

function msp_template_loop_product_link_open()
{
  /**
   * Opens up another a tag within the content-product.php
   */
  echo '<a href="' . get_permalink() . '">';
}

function msp_get_shop_subnav()
{
  /**
   * Outputs the html for a subnav if applicable
   */

  $nav_items = msp_get_top_level_categories();

  if (empty($nav_items)) {
    return;
  }
  ?>
        <nav class="navbar d-none d-sm-flex msp-shop-subnav">
            <div class="navbar-nav flex-row">
               <?php wp_nav_menu([
                 "depth" => 2,
                 "container" => "div",
                 "menu_class" => "navbar-nav m-0 flex-row",
                 "theme_location" => "under_header",
                 "fallback_cb" => "WP_Bootstrap_Navwalker::fallback",
                 "walker" => new msp_mega_menu_walker(),
               ]); ?>
            </div>
        </nav>
    <?php
}

function msp_customer_faq()
{
  wc_get_template("/template/msp-customer-faq.php");
}

function msp_contact_btn()
{
  ?>
    <li class="nav-item">
        <a class="nav-link" href="<?php echo get_bloginfo("url"); ?>/contact">
            Contact Us
        </a>
    </li>
    <?php
}

add_shortcode("contact", "msp_get_contact_page");
function msp_get_contact_page()
{
  ob_start();
  wc_get_template("/template/msp-contact.php");
  echo ob_get_clean();
}

function msp_process_contact_form()
{
  /**
   * The callback which processes a contact form submission.
   * @see ../template/msp-contact.php
   *
   * ERROR CODES:
   *  [0] - success
   *  [1] - required fields
   *  [2] - error
   */
  if (!empty($_POST)) {

    $to = get_option("msp_contact_email");

    // check required fields
    if (
      empty($_POST["email"]) ||
      empty($_POST["message"]) ||
      empty($_POST["name"])
    ) {
      echo 0;
      wp_die();
    }

    // trim subject
    $subject = !empty($_POST["subject"])
      ? $_POST["subject"]
      : wp_trim_words($_POST["message"], 25);

    $headers = [
      "Content-Type: text/html; charset=UTF-8",
      "Reply-To:" . $_POST["email"],
    ];

    //html
    ob_start();
    ?>
        <h4><?php echo $subject; ?></h4>
        <p><?php echo $_POST["message"]; ?></p>
        <br>
        <p>Reply to: <?php echo $_POST["name"] . " - " . $_POST["email"]; ?></p>
        <hr>
        <p>Sent from <?php echo get_bloginfo("url") . "/contact"; ?></p>
        <?php
        $html = ob_get_clean();

        wp_mail($to, $subject, $html, $headers);
        echo 1;

        wp_die();

  }
}

function msp_add_google_analytics()
{
  /**
   * Add google analytics if option filled out in theme options
   */
  $google_account = [
    "UA" => get_option("integration_google_analytics_account_id"),
    "AW" => get_option("integration_google_adwords"),
  ];

  $bing_account = get_option("integration_bing_ads");

  if (!empty($google_account["UA"])): ?>

        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $google_account[
          "UA"
        ]; ?>"></script>

        <script>
            window.dataLayer = window.dataLayer || [];
            
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());

            <?php if (!empty($google_account["UA"])): ?>
                gtag('config', '<?php echo $google_account["UA"]; ?>');
            <?php endif; ?>

            <?php if (!empty($google_account["AW"])): ?>
                gtag('config', '<?php echo $google_account["AW"]; ?>' );
            <?php endif; ?>
        </script>

    <?php endif;
  ?>

    <!-- BING -->
    <?php if (!empty($bing_account)): ?>
        <script>(function(w,d,t,r,u){var f,n,i;w[u]=w[u]||[],f=function(){var o={ti: "<?php echo $bing_account; ?>"};o.q=w[u],w[u]=new UET(o),w[u].push("pageLoad")},n=d.createElement(t),n.src=r,n.async=1,n.onload=n.onreadystatechange=function(){var s=this.readyState;s&&s!=="loaded"&&s!=="complete"||(f(),n.onload=n.onreadystatechange=null)},i=d.getElementsByTagName(t)[0],i.parentNode.insertBefore(n,i)})(window,document,"script","//bat.bing.com/bat.js","uetq");</script>
    <?php endif;
}

add_action("init", "jk_remove_storefront_handheld_footer_bar");
function jk_remove_storefront_handheld_footer_bar()
{
  /**
   * Simply removes the storefront footer from mobile
   */
  remove_action("storefront_footer", "storefront_handheld_footer_bar", 999);
}

function msp_add_sub_cat_links()
{
  /**
   * Gets children of current categoriy, lists them.
   */
  $nav_items = msp_get_category_children();

  if (empty($nav_items)) {
    return;
  }

  $echo = "Shop for ";

  echo "<p>";

  foreach ($nav_items as $item) {
    $echo .=
      '<a href="' .
      get_term_link($item->term_id) .
      '">' .
      $item->name .
      "</a>, ";
  }

  echo rtrim($echo, ", ") . ".</p>";
}

function msp_mobile_product_filter_button()
{
  /**
     * A simple button displayed on shop pages (mobile) which will hide/show store filters.
     */
  ?>
    <div id="filter-button" class="d-block d-xl-none">
        <a role="button" class=><i class="fas fa-filter fa-3x d-block"></i>Filter Products</a>
    </div>
    <?php
}

function msp_bulk_discount_table()
{
  /**
   * HTML table of bulk pricing per object
   */
  global $product;

  $product_id = $product->get_id();
  $enabled = get_post_meta($product_id, "_bulkdiscount_enabled", true);

  $is_discounting = false;

  /**
   * Detect plugin. For use on Front End only.
   */
  include_once ABSPATH . "wp-admin/includes/plugin.php";

  // check for plugin using plugin name
  if (
    is_plugin_active("woocommerce-bulk-discount/woocommerce-bulk-discount.php")
  ) {
    $is_discounting = true;
  }

  $has_a_rule = !empty(
    get_post_meta($product_id, "_bulkdiscount_quantity_1", true)
  );

  if ($is_discounting && $enabled == "yes" && $has_a_rule) { ?>
        <h5>Bulk Discount Pricing:</h5>
        <table id="msp-bulk-pricing">
            <thead>
                <td>QTY</td>
                <?php
                $qtys = get_bulk_discount_data($product_id, "quantity");
                foreach ($qtys as $value) {
                  echo "<td>" . $value . "+</td>";
                }
                ?>
            </thead>
            <tr>
                <td>Price</td>
                <?php
                $qtys = get_bulk_discount_data($product_id, "discount");
                foreach ($qtys as $value) {
                  $percent_off = 1 - $value / 100;
                  $price = number_format(
                    $product->get_price() * $percent_off,
                    2
                  );
                  printf("<td class='%s'>$%s</td>", $percent_off, $price);
                }
                ?>
            </tr>
        </table>
        <?php }
}

function get_bulk_discount_data($product_id, $key)
{
  /**
   * Helper function returns discount data in easy-to-use format
   * @param int $product_id - The ID of a product
   * @param string $key - Specifies which kind of discount we are looking for
   * @return array $data - discount information and at which tier
   */
  $data = [];
  for ($i = 0; $i < 5; $i++) {
    $value = get_post_meta(
      $product_id,
      "_bulkdiscount_" . $key . "_" . $i,
      true
    );
    if (!empty($value)) {
      array_push($data, $value);
    }
  }

  return $data;
}

function msp_get_customer_service_info()
{
  /**
   * HTML block for front page
   */
  $img = URI . "/assets/cs.png";
  $contact = get_option("msp_contact_email");

  ob_start();
  ?>
    <div id="fp-customer-service-top" class="d-block d-md-flex justify-content-center align-items-center text-center">
        <img src="<?php echo $img; ?>" />
        <div class="pl-md-4">
            <h2 class="m-0">Veteran Owned & Operated</h2>
            <p class="lead m-0">Four guys in a office somewhere.</p>
            <p style="font-size: 20px;">
                <a href="tel:8887233864" class="link-normal"> (888) 723-3864 </a>
                |
                <a href="mailto: <?php echo $contact; ?>" class="link-normal"><?php echo $contact; ?></a>
            </p>
        </div>
    </div>
    <div id="fp-customer-service" class="d-block d-lg-flex test-align-center align-items-end py-4 bg-dark text-light my-2">
        <div>
            <h3 class="m-0 text-light bold">Contact us.</h3>
            <p>You'll likely talk to the same guy who ships your package.</p>
        </div>
        <div>
            <p>
                <b>Phone:</b> <a href="tel:8887233864" class="link-normal text-light">(888) 723-3864</a><br>
                <b>Fax:</b> (231) 439-5557
            </p>
        </div>
        <div>
            <h3 class="m-0 text-light bold">Hours</h3>
            <p>Monday - Friday: 8am - 4:30pm (EST)</p>
        </div>
        <div>
            <a href="<?php echo get_bloginfo(
              "url"
            ); ?>/contact" class="btn btn-success btn-lg">Contact us</a>
        </div>
    </div>
    <?php
    $html = ob_get_clean();
    return $html;
}

function msp_get_category_slider($categories, $header = "")
{
  /**
   * Creates a slider of category children - USES OWL CAROUSEL
   * @param array $categories - Full of WP_Term objects
   * @param string $header - String to display over the slider
   */
  if (!empty($header)): ?>
        <h2 class="pb-2"><?php echo $header; ?></h3>
    <?php endif; ?>

    <?php if (empty($categories)) {
      return;
    } ?>

    <div class="owl-carousel dept category-slider border-bottom">
        <?php foreach ($categories as $term):
          $image_src = wp_get_attachment_image_src(
            get_term_meta($term->term_id, "thumbnail_id", true),
            "medium"
          );
          if (!empty($image_src)): ?>
                <a href="<?php echo get_term_link(
                  $term->term_id
                ); ?>" class="text-center">
                    <img src="<?php echo $image_src[0]; ?>" class="img-fluid mx-auto" style="height: 100px; width: auto;" />
                    <p class="text-center"><?php echo $term->name; ?></p>
                </a>
            <?php endif;
        endforeach; ?>
    </div>
    <?php
}

function msp_get_products_slider($atts)
{
  /**
   * Same as msp_get_category_slider but for products.
   * TODO: Could/should probally combine both functions into one
   */
  ob_start();

  if (!empty($atts["header"])): ?>
        <h2 class="pb-2"><?php echo $atts["header"]; ?></h3>
    <?php endif;
  ?>

    <?php if (empty($atts["products"])) {
      return;
    } ?>

    <?php $products = explode(",", $atts["products"]); ?>
        <?php woocommerce_product_loop_start(); ?>
            <div class="owl-carousel mb-3">

                <?php foreach ($products as $product): ?>

                    <?php
                    $post_object = get_post($product);
                    setup_postdata($GLOBALS["post"] = &$post_object);
                    // Create new template for slider?
                    wc_get_template_part("content", "product");
                    ?>

                <?php endforeach; ?>

            </div>
        <?php woocommerce_product_loop_end(); ?>
        
    <?php
    // Important
    wp_reset_postdata();

    $html = ob_get_clean();
    return $html;
}

function msp_add_gmc_conversion_code($order_id)
{
  /**
   * Adds Google Adwords conversation tracking information if information supplied in theme page
   * @param int $order_id
   */
  $google_aw = get_option("integration_google_adwords");
  $google_campaign = get_option("integration_google_aw_campaign");

  if (empty($google_aw) || empty($google_campaign)) {
    return;
  }

  $order = wc_get_order($order_id);
  ?>
    <script>
        gtag('event', 'conversion', {
            'send_to': '<?php echo $google_aw; ?>/<?php echo $google_campaign; ?>',
            'currency': 'USD',
            'transaction_id': '<?php echo $order->get_id(); ?>',
            'value': '<?php echo $order->get_total(); ?>'
        });
    </script>
    <?php
}

function msp_meets_bogo_criteria($product)
{
  /**
   * Preforms a specific test based on the bogo target (brand, category or ids)
   * @param WC_Product
   * @return bool
   */
  $type = get_option("promo_bogo_target");
  $needle = get_option("promo_bogo_needle");
  $meets_criteria = false;

  if ($type == "brand") {
    $brand_slug = get_option("promo_brand_slug");
    $brand = $product->get_attribute($brand_slug);

    if ($brand == $needle) {
      $meets_criteria = true;
    }
  } elseif ($type == "category") {
    $categories = $product->get_category_ids();
    foreach ($categories as $category_id) {
      if ($category_id == $needle) {
        $meets_criteria = true;
      }
    }
  } else {
    $ids = explode(", ", $needle);

    foreach ($ids as $id) {
      if ($product->get_id() == $id) {
        $meets_criteria = true;
      }
    }
  }

  return $meets_criteria;
}

function msp_get_bogo_target_link()
{
  /**
   * Return Link
   */
  $type = get_option("promo_bogo_target");
  $needle = get_option("promo_bogo_needle");

  if ($type === "brand") {
    return msp_get_brand_name($needle);
  } elseif ($type === "category") {
    $term = get_term((int) $needle, "product_cat");

    if (!is_wp_error($term)) {
      return get_term_link($term, "product_cat");
    }
  } else {
    // create page for specific ids
    return "#";
  }
}

function msp_get_bogo_needle_label()
{
  $type = get_option("promo_bogo_target");
  $needle = get_option("promo_bogo_needle");

  if ($type === "brand") {
    return $needle;
  } elseif ($type === "category") {
    $term = get_term((int) $needle, "product_cat");

    if (!is_wp_error($term)) {
      return $term->name;
    }
  } else {
    // create page for specific ids
    return " BOGO item ";
  }
}

add_action(
  "woocommerce_cart_calculate_fees",
  "add_custom_discount_2nd_at_50",
  10,
  1
);
function add_custom_discount_2nd_at_50($wc_cart)
{
  /**
   * Adds a discount if cart item meets a specific criteria
   * @param WC_Cart
   */
  if (is_admin() && !defined("DOING_AJAX")) {
    return;
  }
  $discount = 0;
  $items_prices = [];

  // The discount label you want displayed on cart & checkout
  $label = get_option("promo_bogo_label");

  // The percent off
  $percent_off = intval(get_option("promo_bogo_discount")) / 100;

  // loop through cart
  foreach ($wc_cart->get_cart() as $key => $cart_item) {
    $product = wc_get_product($cart_item["product_id"]);

    // check if product meets bogo criteria setup in backend
    if (msp_meets_bogo_criteria($product)) {
      $qty = intval($cart_item["quantity"]);
      for ($i = 0; $i < $qty; $i++) {
        $items_prices[] = floatval($cart_item["data"]->get_price());
      }
    }
  }

  // get number of eligible items to discount
  $num_of_discounts = intval(count($items_prices) / 2);

  // target the lowest items
  sort($items_prices);

  // calculate discount
  if ($num_of_discounts > 0) {
    for ($i = 0; $i < $num_of_discounts; $i++) {
      $discount -= $items_prices[$i] * $percent_off;
    }
  }

  if ($discount != 0) {
    // The discount
    $wc_cart->add_fee($label, $discount, true);
    # Note: Last argument in add_fee() method is related to applying the tax or not to the discount (true or false)
  }
}

function msp_cart_item_price($price, $item, $key)
{
  /**
   * Checks for price difference, show otherwise.
   */
  $before = $item["data"]->get_regular_price();
  $after = $item["data"]->get_price();

  return $before > $after
    ? wc_format_sale_price($before, $after)
    : wc_price($before);
}

function save_product_with_qty_breaks($post_id)
{
  /**
   * Once a product is updated, update the bulk pricing discounts
   */
  $product = wc_get_product($post_id);
  create_qty_breaks($post_id, $product->get_price());
}

function msp_add_copyright()
{
  /**
   * Connected to theme options. Gives the Admin a chance to change colors.
   */
  $copyright_year = date("Y");

  $bbb_link =
    "https://www.bbb.org/us/mi/harbor-springs/profile/safety-clothing/michigan-safety-products-0372-38125928/accreditation-information";

  echo '<div id="msp-copyright">';
  printf(
    "<a target='_new' href='%s' class='d-block'> %s  <i class='fas fa-copyright'></i>  Michigan Safety Products of Flint Inc. </a>",
    $bbb_link,
    $copyright_year
  );
  echo "</div>";
}

function msp_get_shop_reviews()
{
  $page_id = wc_get_page_id("shop");
  $comments = get_comments(["post_id" => $page_id, "number" => 10]);

  ob_start();
  ?>
        <h2 class="my-2">Recent Customers ❤️</h2>
        <div id="happy-comments" class="owl-carousel border-bottom">
            <?php wp_list_comments(
              apply_filters("woocommerce_product_review_list_args", [
                "callback" => "woocommerce_comments",
              ]),
              $comments
            ); ?>
        </div>
    <?php
    $html = ob_get_clean();
    return $html;
}

function msp_add_tabs()
{
  wc_get_template("template/single-product-tabs.php");
}

function msp_open_single_product_tabs()
{
  /**
     * @see woocommerce_single_product_summary
     * Here we are adding tabs to the 'woocommerce_single_product_summary' hook
     * In this function, we OPEN (but not close) .tab-content so we can add more tabs to this hook
     */
  ?>
    <div class="tab-content">
        <div class="tab-pane active" id="order-tab-content" role="tabpanel" aria-labelledby="order-tab">
    <?php
}

function msp_close_order_tab_content_tag()
{
  /**
     * @see woocommerce_single_product_summary
     * Here we are closing the 'order' tab which is the normal woocommerce stuff you'd expect in this hook (EX. title & rating)
     */
  ?>
        </div> <!-- #order-tab-content -->
    <?php
}

function msp_close_single_product_tabs()
{
  /**
     * Closes the .tab-content div at the end of the hook.
     */
  ?>
        </div> <!-- .tab-content -->
    <?php
}

function msp_add_bulk_tab()
{
  /**
   * Adds the 'bulk' tab content
   */
  wc_get_template("template/single-product-bulk-tab.php"); ?>
    <?php
}

function msp_add_quote_tab()
{
  /**
   * Adds the 'quote' tab content
   */
  wc_get_template("template/single-product-quote-tab.php"); ?>
    <?php
}

function bbloomer_cart_refresh_update_qty()
{
  if (is_cart()) { ?> 
       <script type="text/javascript"> 
          jQuery('div.woocommerce').on('click', 'input.qty', function(){ 
             jQuery("[name='update_cart']").trigger("click"); 
          }); 
       </script> 
       <?php }
}

// MSP FRONT PAGE - FRONT-PAGE HOOKS - FRONT-PAGE.PHP
// Leave functions here for easy access. :D

add_shortcode("msp_fp_products", "msp_get_products_slider");

add_shortcode("msp_fp_promo", "msp_promo_row");

add_shortcode("msp_fp_reviews", "msp_get_shop_reviews");
add_shortcode("msp_fp_customer_service", "msp_get_customer_service_info");

add_action("storefront_before_content", "msp_top_bar");

function msp_top_bar()
{
  /**
   * Displays top bar used for warnings and promos
   */
  $link = get_option("promo_top_bar_link");
  $text = get_option("promo_top_bar_text");

  if (!empty($text)) {
    printf(
      '<a target="_blank" rel="noopener noreferrer" id="msp-top-bar" href="%s">%s</a>',
      $link,
      $text
    );
  }
}

function msp_loop_format_sale_price($regular_price, $sale_price)
{
  $price =
    "<del>" .
    (is_numeric($regular_price) ? wc_price($regular_price) : $regular_price) .
    "</del> <ins>" .
    (is_numeric($sale_price) ? wc_price($sale_price) : $sale_price) .
    "</ins>";
  // echo $price;
  return apply_filters(
    "woocommerce_format_sale_price",
    $price,
    $regular_price,
    $sale_price
  );
}

function msp_featured_item()
{
  /**
   *
   */
  global $product;
  if (!$product->is_featured()) {
    return;
  }

  $primary_color = get_option("msp_primary_color");
  $site_name = get_bloginfo("name");?>

    <div class="single-product-featured-item">
        <div class="feature-base">
            <div class="feature-text">
                <span>Best</span>
                <span>Seller</span>
            </div>
        </div>
    </div>


    <?php
}

function msp_brand_name()
{
  /**
   * Get and display product brand w/ link
   */
  global $product;
  // Some sites use all-brand, some use brand. We need the slug so we can get the link.
  $brand_slug = $product->get_attribute("brand") != "" ? "brand" : "all-brand";
  $brand = $product->get_attribute($brand_slug);

  if (empty($brand)) {
    return;
  }

  // we found a brand
  $term = get_term_by("name", $brand, "pa_" . $brand_slug);

  if ($term === false) {
    printf('<div class="product-brand"><span>%s</span></div>', $brand);
  } else {
    $link = get_term_link($term->term_id);
    printf(
      '<div class="product-brand"><a href="%s">%s</a></div>',
      $link,
      $brand
    );
  }
}

function msp_get_brand_name()
{
  /**
   * Get and display product brand w/ link
   */
  global $product;
  $brand_slug = get_option("promo_brand_slug");
  $brand = $product->get_attribute($brand_slug);

  $term = get_term_by("name", $brand, $brand_slug);

  if (false === $term) {
    return $brand;
  }

  $link = get_term_link($term->term_id);

  return $link;
}

function msp_format_sale_price($price, $reg, $sale)
{
  // only on single product pages
  // if( ! is_product() || ! is_ajax() ) return $price;

  if (!is_numeric($reg) || !is_numeric($sale)) {
    //strip down to just number for math
    $sale = substr(strip_tags($sale), 5);
    $reg = substr(strip_tags($reg), 5);
  }

  $price_messages = msp_get_price_messages($sale);
  $savings = (float) $reg - (float) $sale;
  $percentage =
    round((((float) $reg - (float) $sale) / (float) $reg) * 100) . "%";

  return sprintf(
    '<table class="msp-price"><tr><td>Was:</td><td><del>%s</del></td></tr><tr><td>Now:</td><td><ins>%s</ins> %s</td></tr><tr><td>Savings:</td><td class="savings"> %s (%s)</td></tr></table>',
    is_numeric($reg) ? wc_price($reg) : $reg,
    is_numeric($sale) ? wc_price($sale) : $sale,
    $price_messages,
    wc_price($savings),
    $percentage
  );
}

function msp_get_variation_price_html()
{
  $product = wc_get_product($_POST["id"]);
  if ($product) {
    $leadtime = msp_get_product_leadtime($_POST["id"]);
    $msg = msp_get_leadtime_message($leadtime);
    echo $product->get_price_html() . $msg;
  }

  wp_die();
}

function msp_product_has_price_range($product)
{
  if ($product->is_on_sale()) {
    return $product->get_variation_sale_price("min") !=
      $product->get_variation_sale_price("max");
  } else {
    return $product->get_variation_regular_price("min") !=
      $product->get_variation_regular_price("max");
  }

  return true;
}

function msp_get_product_unit_price($product)
{
  /**
   * Checks for product meta data, and displays per unit cost on multi-count items.
   */
  $html = "";

  if ($product !== false) {
    $id = $product->get_id();
    $qty = get_post_meta($id, "msp_product_quantity", true);

    // dont show a per unit cost on variable products with price ranges
    if (
      $product->is_type("variable") &&
      msp_product_has_price_range($product)
    ) {
      return;
    }

    if (!empty($qty) && intval($qty) > 1) {
      $unit_cost = number_format($product->get_price() / $qty, 2);
      $html = sprintf(
        "<span class='unit_price'>(%s%s / per unit)</span>",
        get_woocommerce_currency_symbol(),
        $unit_cost
      );
    }
  }

  return $html;
}

function msp_warn_about_leadtime()
{
  global $product;

  // static id to 3m non-stock shipping class
  $non_stock_item = 1362;
  $today = date("Y-m-d"); // current date;
  $date = strtotime(date("Y-m-d", strtotime($today)) . " +15 day");

  if ($product->get_shipping_class_id() == $non_stock_item) {
    echo '<p style="color: red">Product made to order, ships on or before <b>' .
      date("M d, Y", $date) .
      "</b>.</p>";
  }
}

function msp_product_specification_html()
{
  global $post;

  $specs = msp_get_product_specifications($post->ID);

  echo "<table>";
  foreach ($specs as $spec): ?>
        <tr class="woocommerce-product-attributes-item">
            <th class="woocommerce-product-attributes-item__label"><?php echo ucfirst(
              $spec->spec_label
            ); ?></th>
            <td class="woocommerce-product-attributes-item__value"><?php echo $spec->spec_value; ?></td>
        </tr>
    <?php endforeach;
  echo "</table>";
}

function is_clearance($product)
{
  /**
   * Looks at product tag list and looks for clearance tag
   */

  if (is_product()) {
    global $product;

    $tag_list = $product->get_tag_ids();

    foreach ($tag_list as $key => $tag) {
      $tag = get_term($tag, "product_tag");

      if ($tag->slug === "clearance") {
        return true;
      }
    }
  }
}

function msp_get_product_tabs($content)
{
  /**
   * Gets product tag descriptions if not empty
   */

  if (is_product()) {
    global $product;

    $tag_list = $product->get_tag_ids();

    foreach ($tag_list as $key => $tag) {
      $tag = get_term($tag, "product_tag");

      $desc = tag_description($tag->term_id);

      if (!empty($desc)) {
        $content .= sprintf("%s", $desc);
      }
    }
  }

  return $content;
}

function msp_archive_description_header()
{
  if (is_product_taxonomy() && 0 === absint(get_query_var("paged"))) {
    $term = get_queried_object();

    if ($term && !empty($term->description)) {
      echo "<h3>Description</h3>";
    }
  }
}

function msp_maybe_show_promo_pop_up()
{
  $modal_title = get_option("promo_pop_up_title");
  $modal_id = get_option("promo_pop_up_image_id");

  if (empty($modal_id)) {
    return;
  }

  // IF COOKIE NOT EXISTS
  make_modal_btn([
    "class" => "promo_pop_up_btn d-none",
    "title" => $modal_title,
    "model" => "promo",
    "id" => $modal_id,
  ]);
}
