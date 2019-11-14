<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product;
?>

<!-- Nav tabs -->
<ul class="nav nav-pills bg-dark my-2 p-1 ml-0 border-top border-bottom" id="myTab" role="tablist">
  <li class="nav-item">
    <a class="nav-link active" id="order-tab" data-toggle="tab" href="#order-tab-content" role="tab" aria-controls="order-tab-content" aria-selected="true">
      <i class="fas fa-list pr-1"></i>
        Details
    </a>
  </li>

  <?php if( $product->is_type( 'variable' ) ) : ?>
  <li class="nav-item">
    <a class="nav-link" id="bulk-tab" data-toggle="tab" href="#bulk-tab-content" role="tab" aria-controls="bulk-tab-content" aria-selected="false">
        <i class="fas fa-gifts  pr-1"></i>    
        Buy in Bulk
    </a>
  </li>
  <?php endif; ?>
  
  <li class="nav-item">
    <a class="nav-link" id="quote-tab" data-toggle="tab" href="#quote-tab-content" role="tab" aria-controls="quote-tab-content" aria-selected="false">
        <i class="fas fa-paper-plane  pr-1"></i>    
        Get Quote
    </a>
  </li>
</ul>
