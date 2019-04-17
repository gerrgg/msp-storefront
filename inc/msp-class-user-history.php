<?php 
defined( 'ABSPATH' ) || exit;

class User_History{

    public $data = array();

    function __construct(){
        add_action( 'init', array( $this, 'update_session' ) );
        add_action( 'template_redirect', array( $this, 'check_template' ) );
    }
    
    public function update_session(){
        if( isset( $_SESSION['msp_history'] ) ){
            $this->data = $_SESSION['msp_history'];
        }
    }

    public function get_user_session(){
        if( is_user_logged_in() ){
            return $this->unpackage( get_user_meta( get_current_user_id(), 'msp_history', true ) );
        }
    }

    /**
     * records where the user is.
     */
    public function check_template(){
        // if its not a product or category, we dont want none!
        if( ! is_product() && ! is_product_category() ) return;

        global $post;
        $category = $this->get_category();

        ( is_null( $category ) ) ? 
        array_push( $this->data, array( $post->ID, 'product', time() ) ) : array_push( $this->data, array( $category->term_id, 'category', time() ) );

        $_SESSION['msp_history'] = $this->data;

        if( is_user_logged_in() ){
            update_user_meta( get_current_user_id(), 'msp_history', $this->package( $this->data ) );
        }

    }

    public function package( $thing ){
        return base64_encode( serialize( $thing ) );
    }

    public function unpackage( $thing ){
        return unserialize( base64_decode( $thing ) );
    }

    /**
    * Checks if we are in a category using the URI, if so, grab the slug of the next cat and return WP_Term
    * @return WP_Term $category
    */
    public function get_category(){
        $categories = explode( '/', $_SERVER['REQUEST_URI'] );
        if( $categories[1] = 'product-category' ){
            // store all the categories! or atleast the last...
            $category = get_terms( array(
                'slug' => $categories[2],
                'taxonomy' => 'product_cat',
            ));
            if( empty( $category ) ) return null;
        
        }
        return $category[0];
    }

}
new User_History();