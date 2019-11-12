<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product;

?>

<div class="tab-pane" id="quote-tab-content" role="tabpanel" aria-labelledby="quote-tab">
	<h4>Bulk Quote</h4>
	<p>Submit a request for bulk discount rates. Use this for large quantity orders that exceed the page quantity price breaks. Quotes are typically processed within 1 business day.</p>

	<a href="<?php echo get_bloginfo('url') ?>/quote?ids[]=<?php echo $product->get_id() ?>" class="btn btn-lg btn-info text-white">Free Bulk Quote</a>
</div>
