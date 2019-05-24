<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global $product; 

$questions = get_comments( array(
    'post_id' => get_the_ID(),
    'type' => 'product_question'
) );

?>

<div id="msp_customer_faq" class="pb-2">
    <h2>Customer questions & awnsers</h2>
    
    <?php 
    
        do_action( 'msp_customer_faq_before_questions' );
    ?>

    <?php if( empty( $questions ) ) : ?>

        <div class="alert alert-info" role="alert">
            <h2><i class="fas fa-exclamation-circle pr-2"></i>Whoops...</h2>
            <p>No questions yet; be the first to ask about this product!</p>
        </div>

    <?php 
        else :

            foreach( $questions as $question ){
                /**
                 * Hook: msp_product_question_html
                 * 
                 * 
                 * @hooked product_question_wrapper_open - 5
                 * @hooked msp_chevron_karma_form - 10
                 * @hooked msp_get_product_question - 15
                 * @hooked msp_get_product_question_awnsers - 20
                 * @hooked product_question_wrapper_end
                 */
               do_action( 'msp_product_question_html', $question );
            }

        endif; 

        /**
         * Hook: msp_product_question_html
         * 
         * 
         * @hooked msp_submit_question_form - 5
         */
        do_action( 'msp_customer_faq_after_questions' );
    ?>
</div>
