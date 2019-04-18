<?php 
defined( 'ABSPATH' ) || exit;

class User_History{
    /**
     * A class which records the ID of a page visited by a user. 
     * This data is stored as a $_SESSION and uploaded to the DB for logged in users.
     * 
     */

    public $data = array(
        'products' => '',
        'categories' => '',
        'searches' => '',
        'orders' => '',
    );

    function __construct(){
        add_action( 'init', array( $this, 'update_session' ) );
        add_action( 'template_redirect', array( $this, 'check_template' ) );
        add_action( 'wp_ajax_msp_get_user_browsing_history', array( $this, 'get_user_products_history' ) );
    }
    
    /**
     * updates the $data array with user data from the db or session based on whether or not a user is logged in.
     * TODO: Possibly find a way to store $_SESSION on guests as well, like IP? How does woocommerce do it? Do they do it?
     */
    public function update_session(){
        foreach( $this->data as $key => $value ){
            $db_key = 'msp_' . $key;
            
            if( is_user_logged_in() ){
                $this->data[$key] = $this->unpackage( get_user_meta( get_current_user_id(), $db_key, true ) );

                // $this->data[$key] must be an array for the array_merge function.
                if( ! $this->data[$key] ) $this->data[$key] = array();
                if ( isset( $_SESSION[$db_key] ) ) array_merge( $this->data[$key], $_SESSION[$db_key] );
            } else {
                $this->data[$key] = ( isset( $_SESSION[$db_key] ) ) ? $_SESSION[$db_key] : array();
            }
        }
    }

    /**
     * records where the user is and updates db if nessicary.
     */
    public function check_template(){
        // if its not a product or category, we dont want none!
        if( ! is_product() && ! is_product_category() ) return;

        $category = $this->get_category();
        $this->sort( $category );
        $this->update_user_products_history();
    }

    /**
     * checks if the user is logged in, if so, saves the session to the DB.
     */
    public function update_user_products_history(){
        if( is_user_logged_in() ){
            foreach( $this->data as $key => $data ){
                if( ! empty( $data ) ){
                    update_user_meta( get_current_user_id(), 'msp_' . $key, $this->package( $data ) );
                }
            }
        }
    }

    /**
     * places the $_SESSION in a specific array based on the value of $category
     * @param WP_Term $category
     */
    public function sort( $category ){
        if( is_null( $category ) ){
            global $post;
            array_push( $this->data['products'], array( $post->ID, time() ) );
        } else {
            array_push( $this->data['categories'], array( $category->term_id, time() ) );
        }

        $_SESSION['msp_products'] = $this->data['products'];
        $_SESSION['msp_categories'] = $this->data['categories'];
    }

    /**
     * packs up an array for saving to the DB
     * @param array $thing
     * @return string
     */
    public function package( $thing ){
        return base64_encode( serialize( $thing ) );
    }

    /**
     * unpacks a encoded serialzed string of data from the DB
     * @param string $thing
     * @return array
     */
    public function unpackage( $thing ){
        return unserialize( base64_decode( $thing ) );
    }

    /**
     * sends a json of the products array
     * @param int $limit - the numbers of results we'd liked returned.
     */
    public function get_user_products_history( $limit = 20 ){
        $size = sizeof( $this->data['products'] );
        $offset = $size - $limit;
        ( $size > $limit ) ? wp_send_json( array_splice( $this->data['products'], $offset ) ) : wp_send_json( $this->data['products'] );

    }

    public function get_user_categories_history(){
        wp_send_json( $this->data['categories'] );
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