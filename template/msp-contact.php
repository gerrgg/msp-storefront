<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<div id="msp-contact">
    <p class="lead">If you’d rather talk to one of us, call <a href="tel:888-723-3864">888-723-3864</a>.</p>
    <form style="max-width: 500px;">
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

        <input type="hidden" name="action" value="msp_process_contact_form" />
        <button class="btn btn-success submit">Submit Message</button>
    </form>
    <hr/>
    <address class="text-muted">
    <h5><b>Michigan Safety Products of Flint, Inc.</b></h5>
    8640 Commerce Court
    Harbor Springs, MI 49740<br>
    Phone: <a href="tel:888-723-3864">888-723-3864</a> <br>
    Fax: 231-439-5557 <br>
    Office Hours: Mon-Fri 7:30AM - 4:30PM EST <br>
    </address>
</div>