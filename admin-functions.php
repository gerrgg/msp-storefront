<?php
class MSP_Admin
{
  /**
   * Class used for everything backend on this child-theme.
   */

  function __construct()
  {
    // Backend settings and UI changed
    add_action("admin_menu", [$this, "theme_options"]);

    // Custom meta boxes for use in backend (product edit mostly)
    add_action("add_meta_boxes", [$this, "msp_meta_boxes"]);
    add_action("woocommerce_product_options_advanced", [
      $this,
      "submit_resources_tab",
    ]);

    //variation custom fields
    add_action(
      "woocommerce_variation_options_pricing",
      [$this, "msp_add_discontinued_checkbox"],
      1,
      3
    );
    add_action(
      "woocommerce_variation_options_pricing",
      [$this, "msp_variation_quantity"],
      1,
      3
    );
    add_action(
      "woocommerce_variation_options_pricing",
      [$this, "msp_product_variation_leadtime_input_box"],
      1,
      3
    );

    // save variation custom fields
    add_action(
      "woocommerce_save_product_variation",
      [$this, "save_product_variation_meta"],
      10,
      2
    );

    // Save product data
    add_action(
      "woocommerce_process_product_meta",
      [$this, "process_product_meta"],
      10,
      2
    );
    add_action(
      "woocommerce_process_product_meta",
      [$this, "process_product_resources_meta"],
      11,
      2
    );
    add_action(
      "woocommerce_process_product_meta",
      [$this, "process_product_videos_meta"],
      12,
      2
    );
    add_action(
      "woocommerce_process_product_meta",
      [$this, "process_product_size_guide_meta"],
      13,
      2
    );
    add_action(
      "woocommerce_process_product_meta",
      [$this, "process_product_specifications_meta"],
      14,
      2
    );
    add_action(
      "woocommerce_process_product_meta",
      [$this, "process_product_meta"],
      15,
      2
    );

    add_action(
      "woocommerce_product_options_general_product_data",
      "msp_specifications_table"
    );
    add_action("woocommerce_product_options_general_product_data", [
      $this,
      "msp_quantity",
    ]);
    add_action("woocommerce_product_options_general_product_data", [
      $this,
      "msp_product_leadtime_input_box",
    ]);
    add_action("woocommerce_product_options_general_product_data", [
      $this,
      "iww_gsf_title",
    ]);

    // Net 30 checkbox - For both self and other users.
    add_action("show_user_profile", [$this, "add_net30_metabox"], 1);
    add_action("edit_user_profile", [$this, "add_net30_metabox"], 1);

    // Saving Net 30 checkbox data - For both self and other users.
    add_action(
      "personal_options_update",
      [$this, "update_user_to_net30_terms"],
      5
    );
    add_action(
      "edit_user_profile_update",
      [$this, "update_user_to_net30_terms"],
      5
    );
  }

  function msp_variation_quantity($loop, $variation_data, $variation)
  {
    $key = "msp_product_quantity";

    echo '<div class="options_group">';

    woocommerce_form_field(
      $key . "[" . $loop . "]",

      [
        "type" => "number",
        "wrapper_class" => "form-field",
        "label" => "QTY",
        "description" => "How many items with each order?",
      ],

      get_post_meta($variation->ID, $key, true)
    );

    echo "</div>";
  }

  function msp_quantity()
  {
    global $woocommerce, $post;
    $meta_value = get_post_meta($post->ID, "msp_product_quantity", true);
    $value = empty($meta_value) ? 1 : $meta_value;

    echo '<div class="options_group">';

    woocommerce_form_field(
      "msp_product_quantity",
      [
        "type" => "number",
        "wrapper_class" => "form-field",
        "label" => "QTY",
        "description" => "How many items with each order?",
      ],
      $value
    );

    echo "</div>";
  }

  function msp_product_leadtime_input_box()
  {
    /**
     * HTML which displays the input box for adding a product specific leadtime
     */
    global $woocommerce, $post;
    $meta_value = get_post_meta($post->ID, "_leadtime", true);

    echo '<div class="options_group">';

    woocommerce_form_field(
      "_leadtime",
      [
        "type" => "number",
        "wrapper_class" => "form-field",
        "label" => "Leadtime",
        "description" => "How many days of leadtime on product?",
      ],
      $meta_value
    );

    echo "</div>";
  }

  public function iww_gsf_title()
  {
    /**
     * Display a nicer
     */
    global $woocommerce, $post;
    echo '<div class="options_group">';
    woocommerce_wp_text_input([
      "id" => "gsf_title",
      "wrapper_class" => "form-field-wide",
      "label" => __("GSF Title", "woocommerce"),
      "description" => "Try to stay under 70",
      "custom_attributes" => ["autocomplete" => "off"],
    ]);
    echo '<p class="form-field form-field-wide">Title Length: <span id="title-length"></span></p>';
    echo "</div>";
  }

  // Save Fields
  public function process_product_meta($post_id)
  {
    if (isset($_POST["gsf_title"])) {
      update_post_meta($post_id, "gsf_title", $_POST["gsf_title"]);
    }

    if (isset($_POST["msp_product_quantity"])) {
      update_post_meta(
        $post_id,
        "msp_product_quantity",
        $_POST["msp_product_quantity"]
      );
    }

    if (isset($_POST["_leadtime"])) {
      update_post_meta($post_id, "_leadtime", $_POST["_leadtime"]);
    }
  }

  public function add_next_order_btn()
  {
    /**
     * Adds a next & previous order button for quick pagination of orders.
     */
    $orders = wc_get_orders(["return" => "ids", "limit" => 100]);
    for ($i = 0; $i < sizeof($orders); $i++) {
      if ($orders[$i] == $_GET["post"]) {
        if (!empty($orders[$i - 1])) {
          $prev = $orders[$i - 1];
        }
        if (!empty($orders[$i + 1])) {
          $next = $orders[$i + 1];
        }
      }
    }
    ?>
        <div class="wrap">
            <?php if (!empty($next)): ?>
            <a href="/wp-admin/post.php?post=<?php echo $next; ?>&action=edit" class="btn" style="float:left">Previous Order</a>
            <?php endif; ?>
            <?php if (!empty($prev)): ?>
            <a href="/wp-admin/post.php?post=<?php echo $prev; ?>&action=edit" class="btn" style="float:right">Next Order</a>
            <?php endif; ?>
        </div>
        <?php
  }

  public function ajax_delete_option()
  {
    /**
     * Is passed the key of the row to be removed from the array. Then serialize and put back in DB.
     * @see ../js/admin.js
     */

    // get position to remove
    $pos = $_POST["target"];

    // get array from db
    $promos = msp_get_promos();

    // remove the selected row from promos array
    unset($promos[$pos]);

    // put back in db
    update_option("msp_promos", maybe_serialize($promos));

    wp_die();
  }

  public function ajax_create_option()
  {
    /**
     * AJAX function which adds data to options API
     */
    if (isset($_POST["options"])) {
      update_option("msp_promos", maybe_serialize($_POST["options"]));
    }

    wp_die();
  }

  public function msp_meta_boxes()
  {
    add_meta_box(
      "msp-product-video",
      __("Product Videos", "msp"),
      "msp_product_video_callback",
      "product",
      "side",
      "low"
    );

    add_meta_box(
      "msp-size-guide",
      __("Product Size Guide", "msp"),
      "msp_size_guide_callback",
      "product",
      "side",
      "low"
    );
  }

  public function save_product_variation_meta($variation_id, $i)
  {
    $variation_meta_keys = [
      "_leadtime",
      "msp_discontinued",
      "our_cost",
      "msp_product_quantity",
    ];

    foreach ($variation_meta_keys as $meta_key) {
      if (isset($_POST[$meta_key])) {
        update_post_meta($variation_id, $meta_key, $_POST[$meta_key][$i]);
      }
    }
  }

  public function theme_options()
  {
    /**
     * hooked into the admin_init so we can create menus and customize site settings
     */
    add_theme_page(
      "MSP Theme Options",
      "MSP Theme Options",
      "manage_options",
      "msp_options",
      [$this, "msp_options_callback"]
    );

    add_action("admin_init", [$this, "register_theme_settings"]);
    add_action("admin_enqueue_scripts", [$this, "enqueue_scripts"]);

    add_action("woocommerce_admin_order_data_after_order_details", [
      $this,
      "submit_tracking_form",
    ]);
    add_action("woocommerce_admin_order_data_after_order_details", [
      $this,
      "add_next_order_btn",
    ]);
  }

  public function process_product_size_guide_meta($id)
  {
    /**
     * Updates the size guide
     */
    if (isset($_POST["_msp_size_guide"])) {
      update_post_meta($id, "_msp_size_guide", $_POST["_msp_size_guide"]);
    }
  }

  public function process_product_videos_meta($id)
  {
    /**
     * Updates product videos
     */

    $limit = sizeof($_POST["product_video"]);
    $arr = [];
    for ($i = 0; $i <= $limit; $i++) {
      if (!empty($_POST["product_video"][$i])) {
        array_push($arr, [$_POST["product_video"][$i]]);
      }
    }

    update_post_meta($id, "_msp_product_videos", MSP::package($arr));
  }

  public function process_product_resources_meta($id)
  {
    /**
     * Updates resources TODO: Could easily combine these functions.. ^^
     */
    $limit = sizeof($_POST["resource_url"]);
    $arr = [];
    for ($i = 0; $i <= $limit; $i++) {
      if (
        !empty($_POST["resource_label"][$i]) &&
        !empty($_POST["resource_url"][$i])
      ) {
        array_push($arr, [
          $_POST["resource_label"][$i],
          $_POST["resource_url"][$i],
        ]);
      }
    }

    update_post_meta($id, "_msp_resources", MSP::package($arr));
  }

  public function process_product_specifications_meta($id)
  {
    $specs = $_POST["specification"];

    foreach ($specs as $spec) {
      if (!empty($spec["label"]) && !empty($spec["value"])) {
        msp_update_specification($id, $spec["label"], $spec["value"]);
      }
    }
  }

  public function submit_resources_tab()
  {
    /**
     * HTML form on back end for linking resources to products
     */
    global $post;
    $resources = msp_get_product_resources($post->ID);
    ?>
        <div id="resource_tab" class="option_group">
            <p class="form-field resource_label_field">
                <label for="resource_label">Resources</label>
                <div style="display: flex;">
                    <p id="resource_input_wrapper">
                        <?php if (empty($resources)): ?>
                        <p>
                          <input type="text" id="resource_label" name="resource_label[0]" placeholder="Label" />
                            <input type="text" id="resource_url" name="resource_url[0]" placeholder="URL" />
                            <input type="button" name="upload-btn" class="button-secondary upload-btn" value="Upload PDF">
                        </p>
                        </br>

                        <?php else: ?>
                            <?php foreach ($resources as $index => $arr): ?>
                              <p>
                                <input type="text" id="resource_label" name="resource_label[<?php echo $index; ?>]"placeholder="Label" value="<?php echo $arr[0]; ?>" />
                                <input type="text" id="resource_url" name="resource_url[<?php echo $index; ?>]" placeholder="URL" value="<?php echo $arr[1]; ?>" />
                                <input type="button" name="upload-btn" class="button-secondary upload-btn" value="Upload PDF">
                              </p>
                              </br>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </p>
                </div>
                <button type="button" class="add_input_line" data-count=0>CLICK TO ADD NEW PDF</button>
            </p>
        </div>
        <?php
  }

  public function msp_product_variation_leadtime_input_box(
    $loop,
    $variation_data,
    $variation
  ) {
    /**
     * HTML which displays the input box for adding a product specific leadtime
     */

    $key = "_leadtime";

    echo '<div class="options_group">';

    woocommerce_form_field(
      $key . "[" . $loop . "]",

      [
        "type" => "number",
        "wrapper_class" => "form-field",
        "label" => "Leadtime",
        "description" => "What is the expected leadtime on a product?",
      ],

      get_post_meta($variation->ID, $key, true)
    );

    echo "</div>";
  }

  public function msp_add_discontinued_checkbox(
    $loop,
    $variation_data,
    $variation
  ) {
    /**
     * HTML which displays the 'Product Discontinued' checkbox in Product Variations
     */
    woocommerce_wp_checkbox([
      "id" => "msp_discontinued[" . $loop . "]",
      "class" => "short",
      "label" => __("Product Discontinued?  ", "woocommerce"),
      "value" => get_post_meta($variation->ID, "msp_discontinued", true),
    ]);
  }

  public function msp_add_our_cost_input($loop, $variation_data, $variation)
  {
    /**
     * HTML which displays the 'Our cost' Text input in Product Variations
     */
    woocommerce_wp_text_input([
      "id" => "our_cost[" . $loop . "]",
      "class" => "short wc_price",
      "label" => __("Our Cost  ", "woocommerce"),
      "value" => get_post_meta($variation->ID, "our_cost", true),
    ]);
  }

  public function enqueue_scripts($hook)
  {
    /**
     * Add admin script
     */
    wp_enqueue_script(
      "admin",
      get_stylesheet_directory_uri() . "/assets/js/admin.js",
      ["jquery"],
      time()
    );
    wp_enqueue_media();
  }

  public function submit_tracking_form()
  {
    /**
     * simple form which allows backend users to submit tracking information.
     */
    woocommerce_wp_select([
      "id" => "shipper",
      "label" => "Shipper:",
      "value" => "",
      "options" => [
        "" => "",
        "ups" => "UPS",
        "fedex" => "Fedex",
        "usps" => "Post Office",
      ],
      "wrapper_class" => "form-field-wide",
    ]);

    woocommerce_wp_text_input([
      "id" => "tracking",
      "label" => "Tracking #:",
      "value" => "",
      "wrapper_class" => "form-field-wide",
    ]);

    echo '<button class="button button-primary" style="width: 100%; margin-top: 1rem;">Post Tracking</button>';
  }

  public static function manage_cron_jobs($key, $order_id, $create = true)
  {
    /**
     * Run when saving order meta data, this function checks if the key is in the $cron_map array
     * if true, clear any old cron_jobs, and create the new one mapped to the function in $cron_map.
     * @param string $key - meta key
     * @param int $order_id - order id
     */

    $cron_map = [
      "tracking" => "msp_update_order_tracking",
    ];

    if (isset($cron_map[$key])) {
      //create key
      $cron_key = "msp_update_order_" . $order_id . "_" . $key;

      //get rid of old job
      $timestamp = wp_next_scheduled($cron_key, $order_id);
      wp_unschedule_event($timestamp, $cron_key, $order_id);
      update_post_meta($order_id, $cron_key, $timestamp);

      if ($create) {
        // //make new job
        wp_schedule_event(time(), "daily", $cron_key, $order_id);
        add_action($cron_key, $cron_map[$key], 1, 1);
      }
    }
  }

  public function add_settings_field_and_register(
    $page,
    $section,
    $prefix,
    $keys
  ) {
    /**
     * simplfies the task of adding settings fields and registering.
     */

    foreach ($keys as $key) {
      add_settings_field(
        $prefix . "_$key",
        deslugify($key) . ":",
        $prefix . "_" . $key . "_callback",
        $page,
        $section
      );
      register_setting($page, $prefix . "_$key");
    }
  }

  /**
   * simple html wrapper for the theme options page.
   */
  public function msp_options_callback()
  {
    ?>
        <div class="wrap">
            <h1>MSP Theme Options</h1>

            <form method="post" action="options.php">
                <?php settings_fields("msp_options"); ?>
                <?php submit_button(); ?>
                <?php do_settings_sections("msp_options"); ?>
                <?php submit_button(); ?>
            </form>        
        </div>
        <?php
  }

  /**
   *
   * dynamically creates options fields based on the arguments passed to add_settings_section.
   * */
  public function register_theme_settings()
  {
    // add_settings_section(
    //     'front_page',
    //     'Front Page:',
    //     '',
    //     'msp_options'
    // );

    add_settings_section("theme_options", "Theme Layout:", "", "msp_options");

    add_settings_section("emails", "Emails:", "", "msp_options");

    add_settings_section("integration", "Integration:", "", "msp_options");

    add_settings_section("woocommerce", "Woocommerce:", "", "msp_options");

    add_settings_section("promotions", "Promotions:", "", "msp_options");

    $this->add_settings_field_and_register(
      "msp_options",
      "promotions",
      "promo",
      [
        "top_bar_text",
        "top_bar_link",
        "pop_up_title",
        "pop_up_link",
        "pop_up_image_id",
        "pop_up_version",
      ]
    );

    $this->add_settings_field_and_register(
      "msp_options",
      "theme_options",
      "msp",
      ["primary_color", "secondary_color", "light_or_dark_theme"]
    );

    $this->add_settings_field_and_register("msp_options", "emails", "msp", [
      "contact_email",
      "gtin_field",
    ]);

    $this->add_settings_field_and_register(
      "msp_options",
      "integration",
      "integration",
      [
        "tidio_secret",
        "google_analytics_account_id",
        "google_recaptcha",
        "google_adwords",
        "google_aw_campaign",
        "bing_ads",
        "ups_api_key",
        "ups_username",
        "ups_password",
      ]
    );

    $this->add_settings_field_and_register(
      "msp_options",
      "woocommerce",
      "woo",
      [
        "use_leadtime",
        "default_leadtime",
        "priority_mail",
        "ups_only_shipping_class_id",
        "ltl_shipping_class_id",
        "free_shipping_method_id",
        "ltl_shipping_method_id",
        "two_day_shipping_method_id",
        "three_day_shipping_method_id",
        "standard_shipping_method_id",
      ]
    );
  }
}

new MSP_Admin();

// templates called by $this->add_settings_field_and_register();

/** ALL THE HTML CALLBACKS FOR THE THEME OPTIONS PAGE /wp-admin/themes.php?page=msp_options */
function woo_use_leadtime_callback()
{
  $checked =
    "yes" === get_option("woo_use_leadtime") ? 'checked="checked"' : "";

  echo '<input name="woo_use_leadtime" id="woo_use_leadtime" type="checkbox" value="yes" class="code"' .
    $checked .
    "/>";
}

function woo_default_leadtime_callback()
{
  $option = get_option("woo_default_leadtime");
  echo "<p>Default leadtime is overwritten if product is given specific leadtime. Use <code>0</code> for no default leadtime! </p>";
  echo '<input name="woo_default_leadtime" id="woo_default_leadtime" type="number" value="' .
    get_option("woo_default_leadtime") .
    '" class="code" />';
}

function woo_priority_mail_callback()
{
  $option = get_option("woo_priority_mail");
  echo "<p>This shipping method will be removed from carts greater than 1 lb. and is intended for use in AK and HI:</p>";
  echo '<input name="woo_priority_mail" id="woo_priority_mail" type="number" value="' .
    get_option("woo_priority_mail") .
    '" class="code" />';
}

function promo_pop_up_title_callback()
{
  $option = get_option("promo_pop_up_title");
  echo '<input name="promo_pop_up_title" id="promo_pop_up_title" type="text" value="' .
    get_option("promo_pop_up_title") .
    '" class="code" />';
}

/** Shipping Classes */
function woo_ups_only_shipping_class_id_callback()
{
  $option = get_option("woo_ups_only_shipping_class_id");
  echo '<p><a href="/wp-admin/admin.php?page=wc-settings&tab=shipping&section=classes">Shipping Classes</a> are used as a condition to determine which shipping methods are available.</p>';
  echo '<input name="woo_ups_only_shipping_class_id" id="woo_ups_only_shipping_class_id" type="text" value="' .
    get_option("woo_ups_only_shipping_class_id") .
    '" class="code" />';
}

function woo_ltl_shipping_class_id_callback()
{
  $option = get_option("woo_ltl_shipping_class_id");
  echo '<input name="woo_ltl_shipping_class_id" id="woo_ltl_shipping_class_id" type="text" value="' .
    get_option("woo_ltl_shipping_class_id") .
    '" class="code" />';
}

/** Shipping Methods */
function woo_free_shipping_method_id_callback()
{
  $option = get_option("woo_free_shipping_method_id");
  echo '<p>Map the ID of your <a href="/wp-admin/admin.php?page=wc-settings&tab=shipping">shipping Methods</a> so we can add/remove methods when certain conditions are set. (cart weight, shipping class)</p>';
  echo '<input name="woo_free_shipping_method_id" id="woo_free_shipping_method_id" type="text" value="' .
    get_option("woo_free_shipping_method_id") .
    '" class="code" />';
}

function woo_ltl_shipping_method_id_callback()
{
  $option = get_option("woo_ltl_shipping_method_id");
  echo '<input name="woo_ltl_shipping_method_id" id="woo_ltl_shipping_method_id" type="text" value="' .
    get_option("woo_ltl_shipping_method_id") .
    '" class="code" />';
}

function woo_two_day_shipping_method_id_callback()
{
  $option = get_option("woo_two_day_shipping_method_id");
  echo '<input name="woo_two_day_shipping_method_id" id="woo_two_day_shipping_method_id" type="text" value="' .
    get_option("woo_two_day_shipping_method_id") .
    '" class="code" />';
}

function woo_three_day_shipping_method_id_callback()
{
  $option = get_option("woo_three_day_shipping_id");
  echo '<input name="woo_three_day_shipping_method_id" id="woo_three_day_shipping_method_id" type="text" value="' .
    get_option("woo_three_day_shipping_method_id") .
    '" class="code" />';
}

function woo_standard_shipping_method_id_callback()
{
  $option = get_option("woo_standard_shipping_method_id");
  echo '<input name="woo_standard_shipping_method_id" id="woo_standard_shipping_method_id" type="text" value="' .
    get_option("woo_standard_shipping_method_id") .
    '" class="code" />';
}

function promo_pop_up_link_callback()
{
  echo '<input name="promo_pop_up_link" id="promo_pop_up_link" type="text" value="' .
    get_option("promo_pop_up_link") .
    '" class="code" />';
}

function promo_pop_up_image_id_callback()
{
  echo '<input name="promo_pop_up_image_id" id="promo_pop_up_image_id" type="text" value="' .
    get_option("promo_pop_up_image_id") .
    '" class="code" />';
}

function promo_pop_up_version_callback()
{
  echo '<input name="promo_pop_up_version" id="promo_pop_up_version" type="text" value="' .
    get_option("promo_pop_up_version") .
    '" class="code" /> <hr>';
}

function promo_top_bar_text_callback()
{
  $option = get_option("promo_top_bar_text");
  echo '<input name="promo_top_bar_text" id="promo_top_bar_text" type="text" placeholder="some shit you wanna say" value="' .
    get_option("promo_top_bar_text") .
    '" class="code" />';
}

function promo_top_bar_link_callback()
{
  $option = get_option("promo_top_bar_link");
  echo '<input name="promo_top_bar_link" id="promo_top_bar_link" type="text" placeholder="shop/" value="' .
    get_option("promo_top_bar_link") .
    '" class="code" />';
}

function promo_top_bar_image_id_callback()
{
  $option = get_option("promo_top_bar_image_id");
  echo '<input name="promo_top_bar_image_id" id="promo_top_bar_image_id" type="text" value="' .
    get_option("promo_top_bar_image_id") .
    '" class="code" /> <hr>';
}

function promo_bogo_label_callback()
{
  echo '<input name="promo_bogo_label" id="promo_bogo_label" type="text" value="' .
    get_option("promo_bogo_label") .
    '" class="code" />';
}

function promo_brand_slug_callback()
{
  echo '<input name="promo_brand_slug" id="promo_brand_slug" type="text" value="' .
    get_option("promo_brand_slug") .
    '" class="code" /><br>';
  echo '<small>Exclude the "pa_"</small>';
}

function promo_bogo_target_callback()
{
  $target = get_option("promo_bogo_target"); ?>
    <select name="promo_bogo_target" id ="promo_bogo_target" class="code">
        <option>    -    </option>
        <option value="brand" <?php if ($target == "brand") {
          echo "selected";
        } ?> >Brand</option>
        <option value="category" <?php if ($target == "category") {
          echo "selected";
        } ?>>Category</option>
        <option value="specific_ids" <?php if ($target == "specific_ids") {
          echo "selected";
        } ?>>Specific Ids</option>
    </select>
    <?php
}

function msp_logo_width_callback()
{
  echo '<input name="msp_logo_width" id="msp_logo_width" type="number" value="' .
    get_option("msp_logo_width") .
    '" class="code" />';
}

function msp_primary_color_callback()
{
  echo '<input name="msp_primary_color" id="msp_primary_color" type="text" value="' .
    get_option("msp_primary_color") .
    '" class="color-field code" />';
}

function msp_light_or_dark_theme_callback()
{
  $value = get_option("msp_light_or_dark_theme"); ?>

    <input type="radio" name="msp_light_or_dark_theme" value="dark-theme" <?php checked(
      "dark-theme" == $value
    ); ?> /> Dark<br>
    <input type="radio" name="msp_light_or_dark_theme" value="light-theme" <?php checked(
      "light-theme" == $value
    ); ?> /> Light
    <?php
}

function msp_secondary_color_callback()
{
  echo '<input name="msp_secondary_color" id="msp_secondary_color" type="text" value="' .
    get_option("msp_secondary_color") .
    '" class="color-field code" />';
}

function msp_contact_email_callback()
{
  echo '<input name="msp_contact_email" id="msp_contact_email" type="email" value="' .
    get_option("msp_contact_email") .
    '" class="code" />';
}

function msp_gtin_field_callback()
{
  echo '<input name="msp_gtin_field" id="msp_gtin_field" type="text" value="' .
    get_option("msp_gtin_field") .
    '" class="code" />';
}

function integration_tidio_secret_callback()
{
  echo '<p>
            Enter tidio secret here - it will look like <code>qgnmbr78979879898uuahfxz98ydoa</code> <a href="https://www.tidio.com/panel/settings/live-chat/integration">Tidio Integration</a>
            <br><input name="integration_tidio_secret" id="integration_tidio_secret" type="text" value="' .
    get_option("integration_tidio_secret") .
    '" class="code" />
         </p>';
}

function integration_google_analytics_account_id_callback()
{
  echo '<input name="integration_google_analytics_account_id" id="integration_google_analytics_account_id" type="text" value="' .
    get_option("integration_google_analytics_account_id") .
    '" class="code" />';
}

function integration_google_recaptcha_callback()
{
  echo '<input name="integration_google_recaptcha" id="integration_google_recaptcha" type="text" value="' .
    get_option("integration_google_recaptcha") .
    '" class="code" />';
}

function integration_bing_ads_callback()
{
  echo '<input name="integration_bing_ads" id="integration_bing_ads" type="text" value="' .
    get_option("integration_bing_ads") .
    '" class="code" />';
}

function integration_google_adwords_callback()
{
  echo '<input name="integration_google_adwords" id="integration_google_adwords" type="text" value="' .
    get_option("integration_google_adwords") .
    '" class="code" />';
}

function integration_ups_api_key_callback()
{
  echo '<input name="integration_ups_api_key" id="integration_ups_api_key" type="text" value="' .
    get_option("integration_ups_api_key") .
    '" class="code" />';
}

function integration_ups_username_callback()
{
  echo '<input name="integration_ups_username" id="integration_ups_username" type="text" value="' .
    get_option("integration_ups_username") .
    '" class="code" />';
}

function integration_ups_password_callback()
{
  echo '<input name="integration_ups_password" id="integration_ups_password" type="password" value="' .
    get_option("integration_ups_password") .
    '" class="code" />';
}

function integration_google_aw_campaign_callback()
{
  echo '<input name="integration_google_aw_campaign" id="integration_google_aw_campaign" type="text" value="' .
    get_option("integration_google_aw_campaign") .
    '" class="code" />';
}

function msp_product_video_callback($post)
{
  /**
   * Html form for submitting product videos // Maybe make a template
   */
  wp_nonce_field(basename(__FILE__), "msp_product_video_callback");
  $saved_urls = msp_get_product_videos($post->ID);
  ?>
    <div id="msp_product_video_input_table">
        <p>Video Url(s)</p>
        <?php if (empty($saved_urls)): ?>
            <input type="text" name="product_video[0]">
        <?php else: ?>
            <?php foreach ($saved_urls as $index => $url): ?>
                <input type="text" name="product_video[<?php echo $index; ?>]" value="<?php echo $url[0]; ?>">
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <button type="button" class="add" data-count=<?php echo sizeof(
      $saved_urls
    ); ?>>Add</button>
    <?php
}

function msp_specifications_table()
{
  global $woocommerce, $post;

  $specs = msp_get_product_specifications($post->ID);

  $count = sizeof($specs);
  ?>

    <div id="msp-specifications" class="options_group" style="padding-left: 165px">
        <table>
            <thead>
                <th>Attribute</th>
                <th>Value</th>
                <th>Action</th>
            </thead>
            <?php for ($i = 0; $i < sizeof($specs); $i++): ?>
                <tr class="<?php echo $i; ?>">
                    <td><input type="text" name="specification[<?php echo $i; ?>][label]" style="width: 100%" value="<?php echo $specs[
  $i
]->spec_label; ?>" /></td>
                    <td><input type="text" name="specification[<?php echo $i; ?>][value]" style="width: 100%" value="<?php echo $specs[
  $i
]->spec_value; ?>" /></td>
                    <td><button class="remove" type="button">&times;</button></td>
                </tr>
            <?php endfor; ?>

            <tr class="<?php echo sizeof($specs) + 1; ?>">
                    <td><input type="text" name="specification[<?php echo sizeof(
                      $specs
                    ) + 1; ?>][label]" style="width: 100%" /></td>
                    <td><input type="text" name="specification[<?php echo sizeof(
                      $specs
                    ) + 1; ?>][value]" style="width: 100%" /></td>
                    <td><button class="remove" type="button">&times;</button></td>
            </tr>

        </table>
        <button class="add" type="button">+</button>
    </div>

    <?php
}

function msp_size_guide_callback($post)
{
  /**
   * Html form for submitting product size guide // Maybe make a template
   */
  $size_guide_src = get_post_meta($post->ID, "_msp_size_guide", true); ?>
    <div id="msp_size_guide_input_table">
        <p>Size Guide</p>
        <input type="text" name="_msp_size_guide" class="code" value="<?php echo $size_guide_src; ?>" />
    </div>
    <?php
}

add_action(
  "woocommerce_process_shop_order_meta",
  "sc_save_tracking_details",
  1
);

function sc_save_tracking_details($ord_id)
{
  /*
    Quick fix for sending customers tracking - eventually want to hook into API's and automate task.
    */
  if (isset($_POST["shipper"]) && !empty($_POST["shipper"])) {
    $shipper = wc_clean($_POST["shipper"]);
    update_post_meta($ord_id, "shipper", $shipper);
  }

  if (isset($_POST["tracking"]) && !empty($_POST["tracking"])) {
    $tracking = wc_clean($_POST["tracking"]);
    update_post_meta($ord_id, "tracking", $tracking);
  }

  if (isset($shipper, $tracking)) {
    $order = wc_get_order($ord_id);
    $link = sc_make_tracking_link($shipper, $tracking);
    update_post_meta($ord_id, "tracking_link", $link);

    $button_color = !empty(get_option("msp_primary_color"))
      ? get_option("msp_primary_color")
      : "#333";

    $style =
      "width: 350px; font-size: 24px; background-color: " .
      $button_color .
      "; color: #fff; display: block; padding: 1rem; margin: 0 auto;";
    $message = "Track package";

    $order->add_order_note(
      sprintf(
        '<p style="text-align: center"><a href="%s" style="%s">%s</a></p>',
        $link,
        $style,
        $message
      )
    );
  }
}

/**
 * Add new WooCommerce Twilio message variables.
 * Adds shipping method provider, Sequential Order Numbers support, and customer first name.
 *
 * Can work with any custom order meta as well.
 *
 * @param string $message the SMS message
 * @param \WC_Order $order the order object
 * @return string updated message
 */
function sv_wc_twilio_sms_variable_replacement($message, $order)
{
  // Shipment tracking: use first package
  $tracking_link = get_post_meta($order->get_id(), "tracking_link", true);

  if (!empty($tracking_link)) {
    $message = str_replace("%tracking_link%", $tracking_link, $message);
  }

  return $message;
}
add_filter(
  "wc_twilio_sms_customer_sms_before_variable_replace",
  "sv_wc_twilio_sms_variable_replacement",
  10,
  2
);

function sc_make_tracking_link($shipper, $tracking)
{
  $base_urls = [
    "ups" => "https://www.ups.com/track?loc=en_US&tracknum=",
    "fedex" => "https://www.fedex.com/apps/fedextrack/?tracknumbers=",
    "usps" => "https://tools.usps.com/go/TrackConfirmAction?tLabels=",
  ];
  return $base_urls[$shipper] . $tracking;
}

// -----------------------------------------
// Save Meta

/**
 * Handle a custom 'customvar' query var to get products with the 'customvar' meta.
 * @param array $query - Args for WP_Query.
 * @param array $query_vars - Query vars from WC_Product_Query.
 * @return array modified $query
 */
function handle_custom_query_var($query, $query_vars)
{
  if (!empty($query_vars["msp_discontinued"])) {
    $query["meta_query"][] = [
      "key" => "msp_discontinued",
      "value" => esc_attr($query_vars["msp_discontinued"]),
    ];
  }

  return $query;
}

// teach wc_get_products to look for 'msp_discontinued' meta
add_filter(
  "woocommerce_product_data_store_cpt_get_products_query",
  "handle_custom_query_var",
  10,
  2
);

add_shortcode("msp_get_discontinued", "msp_show_discontinued_products");
function msp_get_discontinued_products()
{
  /**
   * Gets all products variations marked as discontinued
   * @return int - product ID's
   */
  $products = wc_get_products([
    "type" => "variation",
    "msp_discontinued" => "yes",
    "return" => "ids",
  ]);
  return $products;
}

function msp_show_discontinued_products()
{
  /**
   * Show discontinued products
   * TODO: NOT DONE
   */
  $close_out_items = msp_get_discontinued_products(); ?>

    <div class="row">
        <?php
        $loop = new WP_Query($close_out_items);

        if ($loop->have_posts()) {
          while ($loop->have_posts()):
            wc_get_template_part("content", "product");
          endwhile;
        } else {
          echo __("No products found");
        }

        wp_reset_postdata();?>
    </div><!--/.products-->
    <?php
}

add_action(
  "woocommerce_email_before_order_table",
  "msp_add_tracking_link_to_order_complete",
  105,
  4
);

if (!function_exists("msp_add_tracking_link_to_order_complete")) {
  function msp_add_tracking_link_to_order_complete(
    $order,
    $sent_to_admin,
    $plain_text,
    $email
  ) {
    /**
     * Include tracking number in completed order email IF isset.
     */
    $tracking_link = get_post_meta(
      $order->get_order_number(),
      "tracking_link",
      true
    );
    $button_color = !empty(get_option("msp_primary_color"))
      ? get_option("msp_primary_color")
      : "#333";

    $style =
      "width: 350px; font-size: 24px; background-color: " .
      $button_color .
      "; color: #fff; display: block; padding: 1rem; margin: 0 auto;";
    $message = "Track package";

    if ($email->id === "customer_completed_order" && $tracking_link !== "") {
      printf(
        '<p style="text-align: center"><a href="%s" style="%s">%s</a></p>',
        $tracking_link,
        $style,
        $message
      );
    }
  }
}
