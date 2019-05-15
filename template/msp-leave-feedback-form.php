<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<form method="POST" action="<?php echo admin_url( 'admin-post.php' ) ?>" class='d-flex flex-column justify-content-center align-items-center'>
    <div class="form-group">
        <h3>How are we doing?</h3>
        <?php msp_get_review_more_star_buttons() ?>
    </div>

    <div class="form-group w-75">
        <h4>What can we do to improve?</h4>
        <textarea name="comments"></textarea>
    </div>
    <input type="hidden" name="user_id" value="<?php echo get_current_user_id() ?>" />
    <input type="hidden" name="action" value="msp_process_feedback_form" />
</form>
