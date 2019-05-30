<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<div>
    <p class="lead">You can expect a response same or next business day.</p>
    <form method="POST" action="<?php admin_url( 'admin-post.php' ) ?>" class="mx-auto" style="max-width: 500px;">
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" class="form-control" />
        </div>

        <div class="form-group">
            <label for="email">Email <span class="required">*</span></label>
            <input type="email" id="email" name="email" class="form-control" required/>
        </div>

        <div class="form-group">
            <label for="subject">Subject</label>
            <input type="text" id="subject" name="subject" class="form-control" />
        </div>

        <div class="form-group">
            <label for="message">Message <span class="required">*</span></label>
            <input type="text" id="message" name="message" class="form-control" required/>
        </div>

        <button class="btn btn-success">Submit Message</button>
    </form>
</div>