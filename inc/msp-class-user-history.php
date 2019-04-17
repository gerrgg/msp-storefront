<?php 

defined( 'ABSPATH' ) || exit;

class User_History{
    // make static?
    private $data = array();

    function __construct(){
        // add_action( 'init', array( $this, 'set_cookie' ) );
        add_action( 'template_redirect', array( $this, 'check_template' ) );
    }

    /**
     * records where the user is.
     */
    public function check_template(){
        // if its not a product or category, we dont want none!
        if( ! is_product() && ! is_product_category() ) return;

        global $post;
        $category = $this->get_category();
        $this->data = ( is_null( $category ) ) ? $post : $category;
        // instead of set cookie, store to $data variable
        add_action( 'init', array( $this, 'set_cookie' ) );
    }

    /**
     * set cookie
     */
    public function set_cookie(){
        $two_weeks = time() + (14 * 24* 60 * 60);
        
        // serialize $this->data, put in cookie.
        // set limit, maybe 50 or 100?
        
        setcookie( 'msp_history', $this->data->ID, $two_weeks );
        // store a product, category and search cookie
    }

    /**
    * Checks if we are in a category using the URI, if so, grab the slug of the next cat and return WP_Term
    * @return WP_Term $category
    */
    function get_category(){
        $categories = explode( '/', $_SERVER['REQUEST_URI'] );
        if( $categories[1] = 'product-category' ){
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
