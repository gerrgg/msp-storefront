<?php 

defined( 'ABSPATH' ) || exit;

class User_History{
    public $data = array();

    function __construct(){
        add_action( 'init', array( $this, 'set_cookie' ) );
        add_action( 'template_redirect', array( $this, 'check_template' ) );
        // add_action( 'init', array( $this, 'test' ) );
    }

    /**
     * set cookie
     */
    public function set_cookie(){
        
        //delete the cookie
        if( isset( $_COOKIE['msp_history'] ) ){
            setcookie( 'msp_history', '', 0, '/', false );
        }
        
        //https://silvermapleweb.com/using-the-php-session-in-wordpress/ - Sessions are hard!
        if( isset( $_SESSION['msp_history'] ) ){
            // "update" the cookie
            $two_weeks = time() + (14 * 24* 60 * 60);
            $this->data = $_SESSION['msp_history'];
            setcookie( 'msp_history', base64_encode( serialize( $_SESSION['msp_history'] ) ), $two_weeks, '/', false );
        }

        $this->debug();

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
    }

    public function get_cookie(){
        if( isset( $_COOKIE['msp_history'] ) ){
            return unserialize( base64_decode( $_COOKIE['msp_history'] ) );
        }
    }

    public function get_session(){
        if( isset( $_SESSION['msp_history'] ) ){
            return $_SESSION['msp_history'];
        }
    }

    /**
    * Checks if we are in a category using the URI, if so, grab the slug of the next cat and return WP_Term
    * @return WP_Term $category
    */
    function get_category(){
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

    public function debug(){
        echo 'cookie';
        var_dump( $this->get_cookie() );
        echo 'session';
        var_dump( $this->get_session() );
    }

}
new User_History();
