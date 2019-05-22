<?php 

defined( 'ABSPATH' ) || exit;

function msp_get_product_image_src( $img_id, $size = 'medium' ){
    $src = wp_get_attachment_image_src( $img_id, $size );
    return $src[0];
}

function msp_get_product_image_srcset( $img_id ){
	$sizes = array( 'woocommerce_thumbnail', 'woocommerce_single' );

	$srcset = array(
		'thumbnail' => msp_get_product_image_src( $img_id, 'woocommerce_thumbnail' ),
		'full' => msp_get_product_image_src( $img_id, 'woocommerce_single' ),
	);

    return $srcset;
}

function msp_get_product_image_src_by_product_id( $product_id ){
    $product = wc_get_product( $product_id );
    $product_image_id = ( ! empty( $product ) ) ? $product->get_image_id() : 0;
    
    return ( ! empty( $product_image_id ) ) ? msp_get_product_image_src( $product_image_id ) : null;
}


function deslugify( $str ){
    return ucwords( str_replace( array('_', '-'), ' ', $str ) );
}

function msp_get_user_product_review( $p_id, $format = ARRAY_A ){
	$comments = get_comments(array(
		'post_id' 						=> $p_id,
		'user_id' 						=> get_current_user_id(),
		'include_unapproved'  => false,
	));
	$comment = get_comment( $comments[0]->comment_ID , $format );
	return $comment;
}

function msp_customer_feedback( $order_id, $format = ARRAY_A ){
    $comments = get_comments(array(
		'post_id' 						=> 0,
		'user_id' 						=> get_current_user_id(),
		'type' 					=> 'store_review',
		'meta_key'				=> 'order_id',
		'meta_value'			=> $order_id,
		'include_unapproved'  => false,
	));
	return( $comments[0] );
}

function msp_get_product_resources( $id ){
	return User_history::unpackage( get_post_meta( $id, '_msp_resources', true ) );
}

function msp_get_product_videos( $id ){
	return User_history::unpackage( get_post_meta( $id, '_msp_product_videos', true ) );
}

function make_modal_btn( $args = array() ){
	$a_text = '<a data-toggle="modal" href="#msp_modal" data-title="%s" data-model="%s" data-action="%s" data-id="%d" class="%s">%s</a>';
	$button_text = '<button data-toggle="modal" data-target="#msp_modal" data-title="%s" data-model="%s" data-action="%s" data-id="%d" class="%s">%s</button>';
	$defaults = array(
		'type'	 => 'a',
		'class'	 => '',
		'text'   => 'text',
		'title'  => 'title',
		'model'  => '',
		'action' => '',
		'id'		 => '',
	);
	$args = wp_parse_args( $args, $defaults );

	$base_html = ( $args['type'] === 'a' ) ? $a_text : $button_text;
	
	echo sprintf( $base_html, $args['title'], $args['model'], $args['action'], $args['id'], $args['class'], $args['text'] );
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


function msp_get_current_category(){
	/**
	* Checks if we are in a category using the URI, if so, grab the slug of the next cat and return WP_Term
	* @return WP_Term $category
	*/
	global $wp_query;
	return $wp_query->get_queried_object();
}

function msp_get_category_children(){
	/**
	* Used to get the children of a product category
	* @return WP_Term $children - The children taxonomys of a product category
	*/
	if( ! is_shop() && ! is_archive() ) return;

	$term = get_queried_object();
	$children = get_terms( $term->taxonomy, array(
		'parent'    => $term->term_id,
		'hide_empty' => false
	));

	return $children;
}