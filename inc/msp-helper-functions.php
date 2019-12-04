<?php 

defined( 'ABSPATH' ) || exit;

function msp_get_differant_colored_product_variations( $product ){
	/**
	 * @param WP_Product
	 * @return Array - 
	 */
	global $wpdb;

	$color_slug = 'pa_all_color'; // Need to ask in theme page
	$color_options = array();
	
	foreach( $product->get_attributes() as $key => $v ){
		if( $key == $color_slug ){
			foreach( $v['options'] as $term_id ){
				$term = get_term( $term_id, $color_slug );
				if( ! is_wp_error( $term ) ){
					$sql = "SELECT m.post_id FROM " . $wpdb->prefix . "posts p, " . $wpdb->prefix . "postmeta m WHERE p.post_parent = " . $product->get_id() . " AND p.post_type LIKE '%product_variation%' AND m.meta_key = 'attribute_pa_all_color' AND m.meta_value = '" . $term->slug . "' LIMIT 1";
					$variation = wc_get_product( $wpdb->get_results( $sql )[0]->post_id );
					$src = msp_get_product_image_src( $variation->get_image_id(), 'thumbnail' );

					if( ! empty( $src ) ) array_push( $color_options, $src );
					
				}
			}
	
			/**
			 * SELECT m.post_id FROM wp_posts p, wp_postmeta m WHERE p.post_parent = 2870 AND p.post_type LIKE '%product_variation% AND m.meta_key = 'attribute_pa_all_color' AND m.meta_value = '990-black'
			 */
			break;
		}
	}

	return $color_options;
}

function get_actual_id( $product ) {
	/**
	* Checks if the object passed is a product or variation, returns appropriate ID
	* @param WC_Product|WC_Product_Variation $product
	* @return int - Product ID
	*/
	return ( $product instanceof WC_Product_Variation ) ? $this->get_variation_id($product) : $this->get_product_id($product);
}

function msp_get_products(){
	// we will pass post IDs and titles to this array
	$return = array();
 
	// you can use WP_Query, query_posts() or get_posts() here - it doesn't matter
	$search_results = new WP_Query( array( 
		's'=> $_GET['q'], // the search query
		'post_status' => 'publish', // if you don't want drafts to be returned
		'ignore_sticky_posts' => 1,
		'posts_per_page' => 50 // how much to show at once
	) );
	if( $search_results->have_posts() ) :
		while( $search_results->have_posts() ) : $search_results->the_post();	
			$title = ( mb_strlen( $search_results->post->post_title ) > 50 ) ? mb_substr( $search_results->post->post_title, 0, 49 ) . '...' : $search_results->post->post_title;
			$return[] = array( $search_results->post->ID, $title ); 
		endwhile;
	endif;
	echo json_encode( $return );
	die;
}

function msp_get_product_variation_data( $product_variations ){
	/** Takes in an array of Product variations, and grabs sku, price and label.
	 * @param array - List of product variations
	 * @return array - list of product variation attributes like sku, price and label
	 */
	$arr = array();
	foreach( $product_variations as $id ){
        $product = wc_get_product( $id );
        $arr[$id] = array(
            'sku' => $product->get_sku(),
            'price' => '$' . $product->get_price(),
            'attr' => wc_get_formatted_variation( $product->get_variation_attributes(), true, false, true ),
        );
	}
	return $arr;
}

function msp_get_product_image_src( $img_id, $size = 'medium' ){
    /** returns the src of a wp_attachment
	 * @param int $img_id - The ID of the image being passed.
	 * @param string $size - The size of the image returned
	 * @return string - image src
	 */
    $src = wp_get_attachment_image_src( $img_id, $size );
    return $src[0];
}

function msp_get_product_image_srcset( $img_id ){
	/**
	 * Calls msp_get_product_image_src() on a number image sizes
	 * @return array - array of srcs
	 */

	$srcset = array(
		'thumbnail' => msp_get_product_image_src( $img_id, 'woocommerce_thumbnail' ),
		'full' => msp_get_product_image_src( $img_id, 'woocommerce_single' ),
	);

    return $srcset;
}

function msp_get_product_image_src_by_product_id( $product_id ){
	/**
	 * returns the src of the WC_Product main image ID
	 * @param int $product_id
	 * @return string|null - either a src or null
	 */
    $product = wc_get_product( $product_id );
    $product_image_id = ( ! empty( $product ) ) ? $product->get_image_id() : 0;
    
    return ( ! empty( $product_image_id ) ) ? msp_get_product_image_src( $product_image_id ) : null;
}


function deslugify( $str ){
	/**
	 * Simply takes in a string, converts any _ to - and capitalizes each word in the string.
	 * @param string
	 * @return string
	 */
    return ucwords( str_replace( array('_', '-'), ' ', $str ) );
}

function msp_get_user_product_review( $p_id, $format = ARRAY_A ){
	/**
	 * returns a customers product review
	 * @param int $product_id - The id of a product.
	 */
	$comments = get_comments(array(
		'post_id' 						=> $p_id,
		'user_id' 						=> get_current_user_id(),
		'include_unapproved'  => false,
	));
	$comment = get_comment( $comments[0]->comment_ID , $format );
	return $comment;
}

function msp_customer_feedback( $order_id, $include_unapproved = false ){
	/**
	 * returns a customer store review connected to $order_id
	 * @param int $order_id - The ID of an order.
	 * @return WP_Comment
	 */
    $comments = get_comments(array(
		'post_id' 						=> 0,
		'user_id' 						=> get_current_user_id(),
		'type' 					=> 'store_review',
		'meta_key'				=> 'order_id',
		'meta_value'			=> $order_id,
		'include_unapproved'  => $include_unapproved,
	));
	if( isset( $comments[0] ) ) return( $comments[0] );
}

function msp_get_product_resources( $id ){
	/**
	 * used to get and unpack array of product resource links stored in DB.
	 * @return array
	 */
	return MSP::unpackage( get_post_meta( $id, '_msp_resources', true ) );
}

function msp_get_product_videos( $id ){
	/**
	 * used to get and unpack array of product video links stored in DB.
	 * @return array
	 */
	$arr = MSP::unpackage( get_post_meta( $id, '_msp_product_videos', true ) ); 
	return ( ! empty( $arr ) ) ? $arr : array();
}

function make_modal_btn( $args = array() ){
	/**
	 * A simple helper function used to properly format a button to work in conjunction with dynamic modals (/js/modal.js).
	 * @param array - $args - An array of arguments
	 * @return string - the HTML output of the button.
	 */
	$a_text = '<a data-toggle="modal" href="#msp_modal" data-title="%s" data-model="%s" data-action="%s" data-id="%d" class="%s">%s</a>';
	$button_text = '<button data-toggle="modal" data-target="#msp_modal" data-title="%s" data-model="%s" data-action="%s" data-id="%d" class="%s">%s</button>';
	$defaults = array(
		'type'	 => 'a',
		'class'	 => '',
		'text'   => 'text',
		'title'  => 'title',
		'model'  => '',
		'action' => 'show',
		'id'		 => '',
	);
	$args = wp_parse_args( $args, $defaults );

	$base_html = ( $args['type'] === 'a' ) ? $a_text : $button_text;
	
	echo sprintf( $base_html, $args['title'], $args['model'], $args['action'], $args['id'], $args['class'], $args['text'] );
}

function msp_get_product_pool( $product ){
	/**
	 * Checks whether the product has children ( variations ).
	 * @param WC_Product - $product
	 * @return array
	 */
    return ( $product->get_children() ) ? $product->get_children() : array( $product->get_id() );
}

function msp_get_product_metadata( $product_ids ){
	/**
	 * loops through an array where the key is the label and value is the meta_key
	 * @param array $product_ids - An array of ids.
	 * @return array $data_sets - An array of key => value pairs.
	 */
	
	$gtin_field = ( ! empty( get_option( 'msp_gtin_field' ) ) ) ? get_option( 'msp_gtin_field') : '_woocommerce_gpf_data';

    $data_sets = array( 'mpn' => '_sku', 'gtin' => $gtin_field );
    foreach( $data_sets as $label => $meta_key ){
        $str = '';
        foreach( $product_ids as $id ){
            $product = wc_get_product( $id );
			$data = get_post_meta( $id, $meta_key, true );
			// if data is an array, we set $data to equal the string we want; specified by the $label attribute.
            if( is_array( $data ) && isset( $data[$label] ) ){
				$data = $data[$label];
			}

			if( ! empty( $data ) && ! is_array( $data ) ) $str .= '<a href="'. $product->get_permalink() .'">'. $data .'</a>, ';
        }
        $data_sets[$label] = $str;
	}
    return $data_sets;
}

function msp_product_additional_information_html( $inner_html ){
	/**
	 * takes in an array of key : value pairs and displays them as a table row
	 * @param array - $inner_html - an array of key value pairs
	 */
    if( empty( $inner_html ) ) return;
    echo '<table>';
	foreach( $inner_html as $label => $value ) : ?>
		<?php if( ! empty( $value ) ) : ?>
			<tr class="woocommerce-product-attributes-item">
				<th class="woocommerce-product-attributes-item__label"><?php echo ucfirst($label); ?></th>
				<td class="woocommerce-product-attributes-item__value"><?php echo rtrim($value, ', ') ?></td>
			</tr>
		<?php endif; ?>
    <?php endforeach;
    echo '</table>';
}


function msp_get_current_category(){
	/**
	* Checks if we are in a category using the URI, if so, grab the slug of the next cat and return WP_Term
	* @return WP_Term $category
	*/
	global $wp_query;
	return $wp_query->get_queried_object();
}

function msp_get_category_children( $category = '' ){
	/**
	* Used to get the children of a product category
	* @return WP_Term $children - The children taxonomys of a product category
	*/
	if( ! is_product_category() && empty( $category ) ) return;
	$term = ( empty( $category ) ) ? get_queried_object() : get_term( $category );

	$children = get_terms( $term->taxonomy, array(
		'parent'    => $term->term_id,
		'hide_empty' => false
	));

	return $children;
}

function msp_get_question_count(){
    $questions = get_comments( array(
        'post_id' => get_the_ID(),
        'type' => 'product_question'
    ) );

    return sizeof( $questions );
}

function msp_get_customers_who_purchased_product( $product_id ){
    global $wpdb;
    $order_item = $wpdb->prefix . 'woocommerce_order_items';
    $order_item_meta = $wpdb->prefix . 'woocommerce_order_itemmeta';

    $sql = "SELECT DISTINCT u.id, u.display_name, u.user_email
            FROM $wpdb->users u, $wpdb->posts p, $order_item i, $order_item_meta meta
            WHERE p.post_type = 'shop_order'
            AND p.post_status = 'wc-completed'
            AND p.ID = i.order_id
            AND i.order_item_type = 'line_item'
            AND i.order_item_id = meta.order_item_id
            AND meta.meta_key = '_product_id'
            AND meta.meta_value = $product_id";
            
    return $wpdb->get_results( $sql );
}


function create_qty_breaks( $id, $price ){
	/**
	* Static rules for quickly spreading bulk discount around
	* @param int $id - Product/variation ID
	* @param string $price - regular price of the product
	*/
	if( 0 < $price && $price <= 7.5 ){
	  update_post_meta( $id, '_bulkdiscount_quantity_1', 24 );
	  update_post_meta( $id, '_bulkdiscount_discount_1', 5 );
	  update_post_meta( $id, '_bulkdiscount_quantity_2', 48 );
	  update_post_meta( $id, '_bulkdiscount_discount_2', 7 );
	  update_post_meta( $id, '_bulkdiscount_quantity_3', 72 );
	  update_post_meta( $id, '_bulkdiscount_discount_3', 10 );
	} elseif( 7.5 < $price && $price <= 15 ){
	  update_post_meta( $id, '_bulkdiscount_quantity_1', 12 );
	  update_post_meta( $id, '_bulkdiscount_discount_1', 5 );
	  update_post_meta( $id, '_bulkdiscount_quantity_2', 24 );
	  update_post_meta( $id, '_bulkdiscount_discount_2', 7 );
	  update_post_meta( $id, '_bulkdiscount_quantity_3', 36 );
	  update_post_meta( $id, '_bulkdiscount_discount_3', 10 );
	} elseif( 15 < $price && $price <= 60 ){
	  update_post_meta( $id, '_bulkdiscount_quantity_1', 4 );
	  update_post_meta( $id, '_bulkdiscount_discount_1', 5 );
	  update_post_meta( $id, '_bulkdiscount_quantity_2', 7 );
	  update_post_meta( $id, '_bulkdiscount_discount_2', 7 );
	  update_post_meta( $id, '_bulkdiscount_quantity_3', 12 );
	  update_post_meta( $id, '_bulkdiscount_discount_3', 10 );
	} elseif( $price >= 60 ){
	  update_post_meta( $id, '_bulkdiscount_quantity_1', 3 );
	  update_post_meta( $id, '_bulkdiscount_discount_1', 5 );
	  update_post_meta( $id, '_bulkdiscount_quantity_2', 6 );
	  update_post_meta( $id, '_bulkdiscount_discount_2', 7 );
	  update_post_meta( $id, '_bulkdiscount_quantity_3', 9 );
	  update_post_meta( $id, '_bulkdiscount_discount_3', 10 );
	} else {
	  update_post_meta( $id, '_bulkdiscount_quantity_1', 12 );
	  update_post_meta( $id, '_bulkdiscount_discount_1', 5 );
	  update_post_meta( $id, '_bulkdiscount_quantity_2', 24 );
	  update_post_meta( $id, '_bulkdiscount_discount_2', 7 );
	  update_post_meta( $id, '_bulkdiscount_quantity_3', 36 );
	  update_post_meta( $id, '_bulkdiscount_discount_3', 10 );
	}
  }

  function msp_add_category_images(){
	  /**
	   * Gets children of current category, then displays a slider for easy nav
	   */
    $categories = msp_get_category_children();
    msp_get_category_slider( $categories );
}

function msp_get_top_level_categories(){
	$categories = get_categories( array( 'taxonomy' => 'product_cat',
                                         'orderby' => 'name',
										 'parent' => 0 ) );
	return $categories; 
}

function msp_get_departments_silder(){
	/**
	 * Gets top-level categories, then displays a slider for easy navigation
	 */
    $categories = get_categories( array( 'taxonomy' => 'product_cat',
                                         'orderby' => 'name',
                                         'parent' => 0 ) );
    if( empty( $categories ) ) return;
    msp_get_category_slider( $categories, 'Shop by department' );
}

function msp_get_random_slider(){
	/**
	 * Gets children of current category, then displays a slider of products for that category
	 */
    $categories = get_categories( array( 'taxonomy' => 'product_cat',
                                         'orderby' => 'name',
                                         'parent' => 0 ) );
    $category = $categories[ rand( 0, sizeof( $categories ) - 1 ) ];
    $products = wc_get_products( array(
        'limit'    => 10,
        'category' => array( $category->slug ),
    ) );
    msp_get_products_slider( $products, $category->name );
}

function msp_get_featured_products_silder(){
	/**
	 * Gets featured products, and puts them into slider.
	 */
    $featured_products = wc_get_products( array(
        'limit'    => 10,
        'orderby'  => 'rand',
        'featured' => true
    ) );
    if( empty( $featured_products ) ) return;
    msp_get_products_slider( $featured_products, 'Essential PPE' );
}

function woocommerce_maybe_add_multiple_products_to_cart( $url = false ) {
	// https://dsgnwrks.pro/snippets/woocommerce-allow-adding-multiple-products-to-the-cart-via-the-add-to-cart-query-string/#comment-12236
	// Make sure WC is installed, and add-to-cart qauery arg exists, and contains at least one comma.
	if ( ! class_exists( 'WC_Form_Handler' ) || empty( $_REQUEST['add-to-cart'] ) || false === strpos( $_REQUEST['add-to-cart'], ',' ) ) {
		return;
	}
	// Remove WooCommerce's hook, as it's useless (doesn't handle multiple products).
	remove_action( 'wp_loaded', array( 'WC_Form_Handler', 'add_to_cart_action' ), 20 );
	$product_ids = explode( ',', $_REQUEST['add-to-cart'] );
	$count       = count( $product_ids );
	$number      = 0;
	foreach ( $product_ids as $id_and_quantity ) {
		// Check for quantities defined in curie notation (<product_id>:<product_quantity>)
		$id_and_quantity = explode( ':', $id_and_quantity );
		$product_id = $id_and_quantity[0];
		$_REQUEST['quantity'] = ! empty( $id_and_quantity[1] ) ? absint( $id_and_quantity[1] ) : 1;
		if ( ++$number === $count ) {
			// Ok, final item, let's send it back to woocommerce's add_to_cart_action method for handling.
			$_REQUEST['add-to-cart'] = $product_id;
			return WC_Form_Handler::add_to_cart_action( $url );
		}
		$product_id        = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $product_id ) );
		$was_added_to_cart = false;
		$adding_to_cart    = wc_get_product( $product_id );
		if ( ! $adding_to_cart ) {
			continue;
		}
		$add_to_cart_handler = apply_filters( 'woocommerce_add_to_cart_handler', $adding_to_cart->get_type(), $adding_to_cart );
		// Variable product handling
		if ( 'variable' === $add_to_cart_handler ) {
			woo_hack_invoke_private_method( 'WC_Form_Handler', 'add_to_cart_handler_variable', $product_id );
		// Grouped Products
		} elseif ( 'grouped' === $add_to_cart_handler ) {
			woo_hack_invoke_private_method( 'WC_Form_Handler', 'add_to_cart_handler_grouped', $product_id );
		// Custom Handler
		} elseif ( has_action( 'woocommerce_add_to_cart_handler_' . $add_to_cart_handler ) ){
			do_action( 'woocommerce_add_to_cart_handler_' . $add_to_cart_handler, $url );
		// Simple Products
		} else {
			WC()->cart->add_to_cart($product_id, $_REQUEST['quantity']);
		}
	}
}
// Fire before the WC_Form_Handler::add_to_cart_action callback.
add_action( 'wp_loaded', 'woocommerce_maybe_add_multiple_products_to_cart', 15 );
/**
 * Invoke class private method
 * https://dsgnwrks.pro/snippets/woocommerce-allow-adding-multiple-products-to-the-cart-via-the-add-to-cart-query-string/#comment-12236
 *
 * @since   0.1.0
 *
 * @param   string $class_name
 * @param   string $methodName
 *
 * @return  mixed
 */
function woo_hack_invoke_private_method( $class_name, $methodName ) {
	if ( version_compare( phpversion(), '5.3', '<' ) ) {
		throw new Exception( 'PHP version does not support ReflectionClass::setAccessible()', __LINE__ );
	}
	$args = func_get_args();
	unset( $args[0], $args[1] );
	$reflection = new ReflectionClass( $class_name );
	$method = $reflection->getMethod( $methodName );
	$method->setAccessible( true );
	$args = array_merge( array( $class_name ), $args );
	return call_user_func_array( array( $method, 'invoke' ), $args );
}


function msp_promo_row( $atts ){
	/**
	 * Easily display html which connect links to promotional images.
	 * @param array - Key value pair format - link_id => media_id  
	 */
	if( empty( $atts ) ) return;


	$links = explode( ', ', $atts['links'] );
	$images = explode( ', ', $atts['images'] );
	$columns = 12 / sizeof( $links );

	echo '<div class="row">';

	for( $i = 0; $i < sizeof( $links ); $i++ ) : 
		$link = get_term_link( (int)$links[$i], 'product_cat' );
		$image = msp_get_product_image_src( $images[$i], 'large' );

		// Accomodate product links
		if( is_wp_error( $link ) ){
			$product = wc_get_product( (int)$links[$i] );
			if( ! is_wp_error( $product ) ) $link = $product->get_permalink();
		}

		// just give up if error
		if( is_wp_error( $link ) ) return;
	?>

		<div class="col-12 col-lg-<?php echo $columns ?>">
			<a href="<?php echo $link ?>">
				<img src="<?php echo $image ?>" class="img-thumb mb-2" />
			</a>
		</div>

	<?php endfor;

	echo '</div>';
}

function msp_get_price_messages( $sale ){
    /**
     * Handy function for appending prices with messages based on sell price
     * @param string $price
     */
    $price_messages = '';

    if( $sale > 100 ){
        $price_messages .= ' + <strong><a class="text-dark un price-msg" target="new" href="/shipping-delivery/">Free Shipping </a></strong>';
        $price_messages .= ' & <strong><a class="text-dark un price-msg" target="new" href="/how-to-return/">FREE Returns. </a></strong>';
    } else {
        $price_messages .= ' & <strong><a class="text-dark un price-msg" target="new" href="/shipping-delivery/">Free Shipping </a></strong> on orders over $100.00.';
    }

    return $price_messages;
}