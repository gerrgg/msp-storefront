<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 *  this form seems silly to attach to each order. 
 *  We can either make this specific feedback on each order ( but will customers want to do that?)
 *  Or we can detatch this form from the orders page and use for other applications. 
 */ 

$comment = msp_customer_feedback();
$rating = get_comment_meta( $comment->comment_ID, 'rating', true );
$_GET['star'] = $rating;
?>

<form id="msp-feedback-form" class='d-flex flex-column justify-content-center align-items-center'>
    <?php if( ! empty( $comment ) ) : ?>

    <div class="form-group border-bottom">
        <p class="lead">On <?php echo date( 'l, F jS Y', strtotime( $comment->comment_date ) ) ?> you gave the follow review:</p>
    </div>

    <?php endif; ?>

    <div class="form-group">
        <h3>How did we do on this order?</h3>
        <?php msp_get_review_more_star_buttons() ?>
    </div>

    <div class="form-group w-75">
        <h4>What can we do to improve?</h4>
        <textarea name="comments"><?php echo ( isset( $comment->comment_content ) ) ? $comment->comment_content : ''; ?></textarea>
    </div>
    <input type="hidden" name="user_id" value="<?php echo get_current_user_id() ?>" />
    <input type="hidden" name="order_id" value="<?php echo get_current_user_id() ?>" />
    <input type="hidden" name="action" value="msp_process_feedback_form" />
    <div class="alert alert-warning feedback" role="alert"></div>
    <button type="submit" class="btn btn-success btn-block">Submit Feedback</button>
</form>
