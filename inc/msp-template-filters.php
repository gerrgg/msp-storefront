<?php

defined("ABSPATH") || exit();

/**
 * Allow HTML in term (category, tag) descriptions
 */
foreach (["pre_term_description"] as $filter) {
  remove_filter($filter, "wp_filter_kses");
  if (!current_user_can("unfiltered_html")) {
    add_filter($filter, "wp_filter_post_kses");
  }
}

foreach (["term_description"] as $filter) {
  remove_filter($filter, "wp_kses_data");
}
/**
 * ===================================================
 */

function msp_add_bootstrap_cols_to_product($class)
{
  /**
   * adds bootstrap grid classes to all category and shop pages.
   * @param array $class
   */
  if (
    !is_product() &&
    !is_cart() &&
    !is_front_page() &&
    !is_page("clearance")
  ) {
    $class[] = "col-6 col-sm-3 col-xl-2";
  }
  return $class;
}

function msp_woocommerce_product_loop_start()
{
  /**
   * opens up any category and shop pages as a bootstrap row.
   */
  return '<div id="msp-archive" class="row">';
}

function msp_woocommerce_product_loop_end()
{
  /**
   * closes up any category and shop pages
   */
  return "</div>";
}

function msp_products_per_page()
{
  /**
   * changes the default per_page value;
   * @return int
   */
  return 30;
}

function remove_add_to_cart_message()
{
  return;
}

function msp_maybe_add_tab_info($content)
{
  /**
   * This filter grabs any additional information from yikes_product_tabs plugin.
   * I stopped using plugin.
   */

  if (is_product()) {
    global $product;

    $plugin_tabs = get_post_meta($product->get_id(), "yikes_woo_products_tabs");

    if (!empty($plugin_tabs)) {
      foreach ($plugin_tabs[0] as $tab) {
        $content .=
          '<h4 class="mb-2">' . $tab["title"] . "</h4>" . $tab["content"];
      }
    }
  }

  return $content;
}

function msp_maybe_category_description($content)
{
  /**
   * Gets current product category, sorts terms in hierarchy order
   *
   * @param string $content
   * @return string $content
   */

  if (is_product()) {
    global $product;

    $terms = get_terms([
      "object_ids" => $product->get_id(),
      "taxonomy" => "product_cat",
    ]);

    foreach ($terms as $term) {
      $content .= msp_get_term_description($term);
    }
  }

  // must always return content (leave outside if ^^)
  return $content;
}

function msp_get_term_description($term)
{
  /**
   * Extracts term description if there is one and returns html
   * @param WP_Term $term
   * @return string $html
   */
  $html = "";

  if (!is_wp_error($term) && !empty($term->description)) {
    $html .= sprintf("%s", $term->description);
  }

  return $html;
}

function msp_sort_terms_to_hierarchy($terms)
{
  /**
   * Sorts taxonomies into set hierarchy
   * @param array $terms
   * @return array $terms
   */

  if (empty($terms)) {
    return false;
  }

  foreach ($terms as $key => $term) {
    if ($term->parent != 0) {
      $terms[$term->parent]->children[] = $term;
      unset($terms[$key]);
    }
  }

  return $terms;
}

function msp_maybe_attribute_description($content)
{
  /**
   * Gets product attribute descriptions and displays them in content.
   * @param string $content
   * @param string $content
   */

  $arr = [];
  $html = "";

  if (is_product()) {
    global $product;
    // get attributes that are visible and not variations
    $visible_attributes = msp_get_visible_non_variable_product_attributes(
      $product
    );

    foreach ($visible_attributes as $attribute) {
      $term = wc_get_product_terms($product->get_id(), $attribute->get_name(), [
        "all",
      ]);

      if (!is_wp_error($term) & isset($term[0])) {
        // clean attribute slug and capitalize
        $taxonomy_name = ucfirst(str_replace("pa_", "", $term[0]->taxonomy));

        // init strings
        $term_name = $term_description = "";

        for ($i = 0; $i < sizeof($term); $i++) {
          // only use attributes with descriptions
          if (!empty($term[$i]->description)) {
            $term_name .= $term[$i]->name . ", ";
            $term_description .= sprintf(
              "<div>%s</div>",
              $term[$i]->description
            );
          }
        }

        if (!empty($term_description)) {
          $html .= sprintf("%s<br>", $term_description);
        }
      }
    }
    $content .= $html;
  }

  return $content;
}

function msp_get_visible_non_variable_product_attributes($product)
{
  /**
   * Returns an array of product attributes that are visible but not used as a variation.
   * @param WC_Product
   * @return Array of WC_Product_Attribute
   */

  $arr = [];

  foreach ($product->get_attributes() as $attribute) {
    // If attribute is visible but not used for variations.
    if ($attribute->get_visible() && !$attribute->get_variation()) {
      array_push($arr, $attribute);
    }
  }

  return $arr;
}
