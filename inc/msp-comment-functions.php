<?php 

function msp_chevron_karma_form( $comment ){
    if( ! $comment->comment_approved ) return;
    $vote = msp_get_user_karma_vote( get_current_user_id(), $comment->comment_ID );
    $vote = ( ! empty( $vote->karma_value ) ) ? $vote->karma_value : 0;
  ?>
    <div class="d-flex flex-column mx-auto text-center mt-3">
        <i class="fas fa-chevron-circle-up text-secondary fa-2x mb-1 karma karma-up-vote <?php if( $vote == 1 ) echo 'voted'; ?>"></i>
        <span class="mb-1 karma-score"><?php echo $comment->comment_karma ?></span>
        <i class="fas fa-chevron-circle-down text-secondary fa-2x karma karma-down-vote <?php if( $vote == -1 ) echo 'voted'; ?>" ></i>
    </div>
  <?php  
}

function msp_comment_actions_wrapper_open(){
    echo '<div class="comment-actions">';
}

function msp_reply_to_comment_btn( $comment ){
    ?>
    <button class="btn btn-outline-secondary comment-on-comment">
        Comment
        <i class="far fa-comment-alt pl-2"></i>
    </button>
    <?php
}

function msp_flag_comment_btn( $comment ){
    ?>
    <button class="btn btn-outline-danger flag-comment">
        Report Abuse
        <i class="fab fa-font-awesome-flag"></i>
    </button>
    <?php
}

function msp_comment_actions_wrapper_close(){
    echo '</div><!-- .comment-actions -->';
}


function msp_get_create_a_review_btn(){
    global $post;
    $url = msp_get_review_link( $post->ID );
    echo '<p class=""><a href="'. $url .'" role="button" class="btn btn-success btn-lg">Write a customer review</a></p>';
}

function msp_get_rating_histogram( $ratings, $count, $echo = true ){
    ob_start();
    ?>
        <table class="product-rating-histogram">
            <?php 
                for( $i = 5; $i > 0; $i-- ) :
                    $now = ( isset( $ratings[$i] ) ) ? intval( ( $ratings[$i] / $count ) * 100 ) : 0; ?>
                    <tr>
                        <td nowrap>
                            <a href=""><?php echo $i ?> stars</a>
                        </td>
                        <td style="width: 80%">
                            <a class="progress">
                                <div class="progress-bar" role="progressbar" style="width: <?php echo $now; ?>%"aria-valuenow="<?php echo $now ?>%" aria-valuemin="0" aria-valuemax="100"></div>
                            </a>
                        </td nowrap>
                        <td>
                            <a href=""><?php echo $now ?>%</a>
                        </td>
                    </tr>
                <?php endfor; ?>
            </table>
    <?php
    $html = ob_get_clean();

    if( ! $echo ){
        return $html;
    }

    echo $html;
}

function msp_single_product_create_review(){
    ?>
    <hr />
    <h3>Review this product</h3>
    <p>Share your thoughts with other customers.</p>
    <?php
        msp_get_create_a_review_btn();
}

add_shortcode( 'review' , 'msp_get_review_template' );
function msp_get_review_template(){
    wc_get_template( '/template/msp-review.php' );
}

function msp_review_more_products(){
    if( ! isset( $_GET['product_id'] ) ) return;
    $product_ids = explode( ',', $_GET['product_id'] );

    if( sizeof( $product_ids ) <= 1 ) return;

    foreach( $product_ids as $id ){
        $product = wc_get_product( $id );
        if( ! empty( $product ) ){
            ?>
            <div class="col-4">
                <a href="<?php echo $product->get_permalink() ?>" class="pt-5 mt-3 text-center link-normal">
                    <img src="<?php echo msp_get_product_image_src( $product->get_image_id() ) ?>" class="mx-auto" />
                    <p class="shorten link-normal text-dark"><?php echo $product->get_name() ?></p>
                    <?php msp_get_review_more_star_links( $product->get_id() ) ?>
                </a>
            </div>
            <?php
        }
    }
}

function msp_get_review_more_star_links( $product_id, $echo = true ){
    $comment = msp_get_user_product_review( $product_id );
    $highlight = 'far';

    if( ! empty( $comment ) ){
        $rating = get_comment_meta( $comment['comment_ID'], 'rating', true );
    }

    ob_start();

    echo '<div class="d-flex justify-content-center">';
    for( $i = 1; $i <= 5; $i++ ) :
        if( isset( $rating ) ){
            $highlight = ( $i <= $rating ) ? 'fas' : 'far';
        }
    ?>
        <a href="<?php echo msp_get_review_link( $product_id, array('star' => $i) ) ?>" class="link-normal">
            <i class="<?php echo $highlight; ?> fa-star fa-2x"></i>
        </a>
    <?php endfor;
    echo '</div>';

    $html = ob_get_clean();

    if( ! $echo ) return $html;
    echo $html;
}

function msp_get_review_link( $product_id, $args = array() ){
    $comment = msp_get_user_product_review( $product_id );

    $base_url = '/review/?product_id=';
    $base_url .= is_array($product_id) ? implode( ',', $product_id ) : $product_id;

    $defaults = array(
        'action' => ( empty( $comment ) ) ? 'create' : 'edit',
        'comment_id' => '',
        'star' => ''
    );

    $args = wp_parse_args( $args, $defaults );

    foreach( $args as $key => $arg ){
        if( ! empty( $arg ) ) $base_url .= "&$key=$arg"; 
    }

    return $base_url;
}

function msp_create_review_wrapper_open(){
    echo '<div class="col-12">';
    echo '<form method="POST" action="'. admin_url( 'admin-post.php' ) .'" enctype="multipart/form-data">';
}

function msp_create_review_top( $product_id ){
    $src = msp_get_product_image_src_by_product_id( $product_id );
    ?>
    <div class="d-flex align-items-center mt-2 mb-4 pb-4 border-bottom">
        <img src="<?php echo $src; ?>" class="img-mini pr-3">
        <p class="m-0 p-0"><?php echo get_the_title( $product_id ); ?></p>
    </div>
    <?php
}

function msp_get_review_more_star_buttons(){
    $class = 'far';

    echo '<h3>Overall Rating</h3>';
    echo '<div class="d-flex pb-2">';

    for( $i = 1; $i <= 5; $i++ ) :
        if( isset( $_GET['star'] ) ){
            $class = ( $i <= $_GET['star'] ) ? 'fas' : 'far';
        }
    ?>

        <a class="link-normal" href="javascript:void(0)">
            <i class="<?php echo $class; ?> fa-star fa-2x msp-star-rating rating-<?php echo $i ?>" data-rating="<?php echo $i; ?>"></i>
        </a>

    <?php endfor;

    echo '</div>';
    echo '<input type="hidden" id="rating" name="rating" value="" required />';
}


function msp_create_review_upload_form( $product_id ){
    if( ! is_user_logged_in() ) return;
    ?>

     <div class="pt-4">
        <h3>Add a photo or video</h3>
        <p>Shoppers find images much more helpful than text alone.</p>
        <input type="file" name="file" />
     </div>

    <?php
}

function msp_create_review_headline( $product_id ){
    $headline = '';
    if( $_GET['action'] == 'edit' ){
        $comment = msp_get_user_product_review( $product_id );
        $headline = get_comment_meta( $comment['comment_ID'], 'headline', true );
    }

    echo '<div class="pt-4">';
        echo '<h3>Add a headline</h3>';
        echo '<input required type="text" name="headline" placeholder="What\'s the most important thing to know?" class="form-control w-50" value="'. $headline .'" />';
    echo '</div>';
}

function msp_create_review_content( $product_id ){
    $content['comment_content'] = '';
    if( $_GET['action'] == 'edit' ){
        $content = msp_get_user_product_review( $product_id );
    }
    echo '<div class="pt-4">';
        echo '<h3>Write your review</h3>';
        echo '<textarea required name="content" class="form-control w-75" placeholder="What did you like or dislike? What did you use this product for?">'. $content['comment_content'] .'</textarea>';
    echo '</div>';
}

function msp_create_review_wrapper_close(){
                echo '<div class="pt-4">';
                    wp_nonce_field( 'create-review_' . $_GET['product_id'] );
                    echo '<input type="hidden" name="product_id" value="'. $_GET['product_id'] .'" />';
                    echo '<input type="hidden" name="action" value="msp_process_create_review" />';
                    echo '<button class="btn btn-success submit-review" />Submit</button>';
                echo '</div>';
            echo '</form>';
        echo '</div> <!-- .row -->';
}


function msp_process_create_review(){
    if( check_admin_referer( 'create-review_' . $_POST['product_id'] ) ){
        $data = $_POST;
        $user = wp_get_current_user();

        if( isset( $_FILES['file'] ) && ! empty( $_FILES['file'] ) ){
            $attachment_id = media_handle_upload( 'file', $_POST['product_id'], array( 'post_name' => 'user_upload_' . uniqid() ) );
        }

        
        $args = array(
            'comment_post_ID' => $data['product_id'],
            'comment_author'	=> $user->user_login,
            'comment_author_email'	=> $user->user_email,
            'comment_author_url'	=> $user->user_url,
            'comment_content' =>  $data['content'],
            'comment_type'			=> 'review',
            'comment_author_IP' => $_SERVER['REMOTE_ADDR'],
            'comment_agent' => $_SERVER['HTTP_USER_AGENT'],
            'comment_date' => current_time( 'mysql', $gmt = 0 ),
            'user_id' => get_current_user_id(),
            'comment_approved' => 0,
        );
        
        $comment = msp_get_user_product_review( $data['product_id'] );

        if( ! is_null( $comment ) ){
            // comment_id needs to be available for after this if statement.
            $comment_id = $comment['comment_ID'];
            $args['comment_ID'] = $comment['comment_ID'];
            wp_update_comment($args);
        } else {
            $comment_id = wp_insert_comment( $args );
        }

        update_post_meta( $attachment_id, '_msp_attached_to_comment', $comment_id );
        update_comment_meta( $comment_id, 'rating', $data['rating'] );
        update_comment_meta( $comment_id, 'headline', $data['headline'] );

        $verified = ( wc_customer_bought_product( $user->user_email, get_current_user_id(), $data['product_id'] ) ) ? 1 : 0;
        update_comment_meta( $comment_id, 'verified', $verified);

        // redirect to review more products!
        $review_more_ids = msp_get_customer_unique_order_items( get_current_user_id() );
        wp_redirect( msp_get_review_link( $review_more_ids, array('action' => 'show_more') ) );
    }
}

function msp_get_comment_headline( $comment ){
    $headline = get_comment_meta( $comment->comment_ID, 'headline', true );
    if( ! empty( $headline ) ){
        echo '<h4 class="review-headline">'. $headline .'</h4>';
    }
}

/**
 * Updates a users karma vote on a comment.
 */
function msp_add_to_karma_table(){
    if( ! isset( $_POST['comment_id'], $_POST['vote'] ) ) return;
    global $wpdb;
    $table_name = 'msp_karma';

    $last_vote = msp_get_user_karma_vote( get_current_user_id(), $_POST['comment_id'] );

    $args = array(
        'karma_user_id'    => get_current_user_id(),
        'karma_comment_id' => $_POST['comment_id'],
        'karma_value'      => $_POST['vote']
    );

    if( empty( $last_vote ) ){
        $wpdb->insert( $table_name, $args );
    } else {
        $wpdb->update( $table_name, $args, array( 'karma_id' => $last_vote->karma_id ) );
    }

    $karma_score = msp_update_comment_karma( $_POST['comment_id'] );
    wp_send_json( $karma_score );

    wp_die();
}

function msp_update_comment_karma( $comment_id ){
    $comment = get_comment( $comment_id, ARRAY_A );
    if( empty( $comment ) ) return;

    global $wpdb;
    $score = 0;

    $results = $wpdb->get_results(
        "SELECT karma_value
         FROM msp_karma
         WHERE karma_comment_id = $comment_id"
    );

    foreach( $results as $vote ){
        $score += $vote->karma_value;
    }

    $comment['comment_karma'] = $score;
    wp_update_comment( $comment );
    
    return $score;
}

function msp_get_user_karma_vote( $user_id, $comment_id ){
    global $wpdb;

    $row = $wpdb->get_row( 
        "SELECT * 
         FROM msp_karma
         WHERE karma_user_id = $user_id
         AND karma_comment_id = $comment_id" 
    );

    return $row;
}

function msp_get_user_uploaded_product_image_id(){
    global $wpdb;
    global $post;
    
    $sql = "SELECT DISTINCT ID
            FROM {$wpdb->posts}, {$wpdb->postmeta}
            WHERE {$wpdb->posts}.post_parent = {$post->ID}
            AND {$wpdb->posts}.post_type = 'attachment'
            AND {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id
            AND {$wpdb->postmeta}.meta_key = '_msp_attached_to_comment'";
    
    $results = $wpdb->get_results( $sql, ARRAY_A );

    $arr = array();
    foreach( $results as $id ){
        array_push( $arr, $id['ID'] );
    }

    return $arr;
}

function msp_get_user_attachment_uploaded_to_comment( $comment ){
    global $wpdb;
    global $post;
    $user_id = get_current_user_id();
    
    $sql = "SELECT DISTINCT ID
            FROM {$wpdb->posts}, {$wpdb->postmeta}
            WHERE {$wpdb->posts}.post_parent = {$post->ID}
            AND {$wpdb->posts}.post_type = 'attachment'
            AND {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id
            AND {$wpdb->postmeta}.meta_key = '_msp_attached_to_comment'
            AND {$wpdb->postmeta}.meta_value = {$comment->comment_ID}
            AND {$wpdb->posts}.post_author = {$user_id}";
    
    $results = $wpdb->get_results( $sql, ARRAY_A );

    $arr = array();
    foreach( $results as $id ){
        array_push( $arr, $id['ID'] );
    }

    return $arr;
}

function msp_review_get_user_upload_image( $comment ){
    $ids = msp_get_user_attachment_uploaded_to_comment( $comment );
    foreach( $ids as $id ){
        $srcset = msp_get_product_image_srcset( $id );
        ?>
        <a href="<?php echo $srcset['full'] ?>">
            <img src="<?php echo $srcset['thumbnail'] ?>" class="mr-2 border img-mini" />
        </a>
        <?php
    }
}

