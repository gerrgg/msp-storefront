<?php
/**
 * Display single product reviews (comments)
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product-reviews.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.6.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
global $product;
if ( ! comments_open() ) {
	return;
}
?>
<div id="reviews" class="woocommerce-Reviews row border-top border-bottom py-2 my-2">
	<div id="comments" class="col-4">
		<h2 class="woocommerce-Reviews-title">
			<?php
			$ratings = $product->get_rating_counts();
			$avg = $product->get_average_rating();
			$count = $product->get_review_count();
			if ( $count && wc_review_ratings_enabled() ) {
				/* translators: 1: reviews count 2: product name */
				$reviews_title = sprintf( esc_html( _n( '%1$s review for %2$s', '%1$s reviews for %2$s', $count, 'woocommerce' ) ), esc_html( $count ), '<span>' . get_the_title() . '</span>' );
				echo apply_filters( 'woocommerce_reviews_title', $reviews_title, $count, $product ); // WPCS: XSS ok.

			} else {
				esc_html_e( 'Reviews', 'woocommerce' );
			}
			?>
		</h2>

		<?php 
		if( ! empty( $ratings ) ){
			echo wc_get_star_rating_html( $avg, $count );
			msp_get_rating_histogram( $ratings, $count );
		} 
		?>

		<?php 
		if ( get_option( 'woocommerce_review_rating_verification_required' ) === 'no' || wc_customer_bought_product( '', get_current_user_id(), $product->get_id() ) ) : 
			msp_single_product_create_review();
		?>

		<?php else : ?>
			<p class="woocommerce-verification-required"><?php esc_html_e( 'Only logged in customers who have purchased this product may leave a review.', 'woocommerce' ); ?></p>
		<?php endif; ?>
	</div>
	<div class="col">

	<?php 
	$ids = msp_get_user_uploaded_product_image_id();


	
	if( ! empty( $ids ) ){
		$limit = ( sizeof( $ids ) < 4 ) ? sizeof( $ids ) : 4;
		echo '<h3>'. sizeof( $ids ) .' customer uploaded images</h3>';
		echo '<div id="user-uploads" class="d-flex pb-3 mb-3 border-bottom">';
		for( $i = 0; $i < $limit; $i++ ){
			$srcset = msp_get_product_image_srcset( $ids[$i] );
			echo '<a href="'. $srcset['full'] .'">';
				echo '<img src="'. $srcset['thumbnail'] .'" class="mx-2 img-small" />';
			echo '</a>';
		}
		echo '</div>';
	}

	?>

	<?php if ( have_comments() ) : ?>
			<ol class="commentlist">
				<?php wp_list_comments( apply_filters( 'woocommerce_product_review_list_args', array( 'callback' => 'woocommerce_comments' ) ) ); ?>
			</ol>

			<?php
			if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) :
				echo '<nav class="woocommerce-pagination">';
				paginate_comments_links(
					apply_filters(
						'woocommerce_comment_pagination_args',
						array(
							'prev_text' => '&larr;',
							'next_text' => '&rarr;',
							'type'      => 'list',
						)
					)
				);
				echo '</nav>';
			endif;
			?>
		<?php else : ?>
			<p class="woocommerce-noreviews"><?php esc_html_e( 'There are no reviews yet.', 'woocommerce' ); ?></p>
		<?php endif; ?>
	</div>
</div>