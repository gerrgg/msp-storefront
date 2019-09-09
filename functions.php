<?php 
/**
 * 
 */
defined( 'ABSPATH' ) || exit;

//globals
define('URI', get_stylesheet_directory_uri() );
define('PATH', get_stylesheet_directory() );

//require
require_once( PATH . '/vendor/wp-bootstrap-navwalker-master/class-wp-bootstrap-navwalker.php' );
require_once( PATH . '/admin-functions.php' );
require_once( PATH . '/inc/msp-template-hooks.php' );
require_once( PATH . '/inc/msp-template-functions.php' );
require_once( PATH . '/inc/msp-template-filters.php' );
require_once( PATH . '/inc/msp-helper-functions.php' );

/**
 * Front-end Theme Settings
 */

class MSP{
    function __construct(){
        add_action( 'init', array( $this, 'myStartSession' ), 1 );
        add_action( 'init', array( $this, 'create_theme_pages' ), 2 );
        add_action( 'widgets_init', array( $this, 'register_sidebar_shop' ), 100 );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'after_setup_theme', array( $this, 'register_menus' ) );
        add_action('wp_logout', array( $this, 'myEndSession') );
        add_action('wp_login', array( $this, 'myEndSession') );
        add_action( 'wp_footer', array( $this, 'add_recaptcha_script_to_footer' ) );
        add_filter( 'woocommerce_min_password_strength', array( $this, 'msp_password_strength' ) );
        add_filter( 'woocommerce_form_field_args', array( $this, 'msp_form_field_args' ), 10, 3 );
        add_filter( 'woocommerce_product_tabs', array( $this, 'msp_product_tabs' ) );
        add_filter( 'woocommerce_package_rates', array( $this, 'maybe_hide_ltl_shipping_option' ), 50, 2 );
        add_filter( 'storefront_footer_widget_columns', function(){ return 1; } );
        add_filter( 'woocommerce_checkout_fields', array( $this, 'msp_checkout_fields' ) );
        add_filter( 'woocommerce_available_payment_gateways', array($this, 'msp_enable_net30'), 99999 );
    }

    public function add_recaptcha_script_to_footer()
    {
        ?>
            <script src="https://www.google.com/recaptcha/api.js?render=6LfkZLYUAAAAAKBHW3f3-Uu1U9YX0jWtOgE4XwnK"></script>
            <script>
            grecaptcha.ready(function() {
                grecaptcha.execute('6LfkZLYUAAAAAKBHW3f3-Uu1U9YX0jWtOgE4XwnK');
            });
            </script>
            <?php
    }

    public function msp_enable_net30( $available_gateways ){
        $user = wp_get_current_user();
        $is_net30 = get_user_meta( $user->ID, 'iww_net30', true );

        if( isset( $available_gateways['cheque'] ) && !$is_net30 ){
            unset( $available_gateways['cheque'] );
        }

        return $available_gateways;
    }

    public function msp_checkout_fields( $fields ){
        $fields['billing']['billing_email']['priority'] = 1;
        return $fields;
    }

    public function register_sidebar_shop(){
        register_sidebar( array(
            'name'          => __( 'Shop Sidebar', 'msp' ),
            'id'            => 'sidebar-msp-shop',
            'before_widget' => '<aside id="%1$s" class="widget %2$s">',
            'after_widget'  => '</aside>',
            'before_title'  => '<h3 class="widget-title">',
            'after_title'   => '</h3>',
        ) );

    }

    /**
     * Enqueue scripts & css for child theme.
     */
    public function enqueue_scripts(){
        global $pagename;

        // Custom javascript functions
        wp_enqueue_script( 'main', URI . '/js/functions.js', array('jquery'), '', true );
        wp_enqueue_script( 'header', URI . '/js/header-functions.js', array('jquery'), '', true );
        wp_enqueue_script( 'modal', URI . '/js/modal.js', array('jquery'), '', true );

        if( is_checkout() )
            wp_enqueue_script( 'checkout', URI . '/js/checkout-functions.js', array('jquery'), '', true );

        
        $this->wp_localize_scripts( array('main') );
        
        // Font Awesome - https://fontawesome.com/icons?d=gallery
        wp_enqueue_style( 'font-awesome', 'https://use.fontawesome.com/releases/v5.8.1/css/all.css' );
        
        // slideout.js - https://github.com/Mango/slideout
        wp_enqueue_script( 'slideout', URI . '/vendor/slideout/dist/slideout.min.js', array(), 
            '', true );

        //Twitter Bootstrap - https://getbootstrap.com/docs/4.3/getting-started/introduction/
        wp_enqueue_style( 'bootstrap', URI . '/vendor/bootstrap-4.3.1-dist/css/bootstrap.min.css' );
        wp_enqueue_script( 'bootstrap', URI . '/vendor/bootstrap-4.3.1-dist/js/bootstrap.bundle.min.js', array( 'jquery' ), '', true );

        //Owl Carousel - https://owlcarousel2.github.io/OwlCarousel2/
        wp_enqueue_style( 'owl-carousel', URI . '/vendor/OwlCarousel2-2.3.4/dist/assets/owl.carousel.min.css' );
        wp_enqueue_style( 'owl-carousel-theme', URI . '/vendor/OwlCarousel2-2.3.4/dist/assets/owl.theme.default.min.css' );
        wp_enqueue_script( 'owl-carousel', URI . '/vendor/OwlCarousel2-2.3.4/dist/owl.carousel.min.js', array( 'jquery' ), '', true );
        
        //Select2 - https://select2.org/
        wp_enqueue_style('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css' );
	    wp_enqueue_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js', array('jquery') );
    }

    public function msp_product_tabs( $tabs ){
        global $post;
        global $product;
        
        $priority = 30;
        $custom_tabs = array(
            'product_videos' => msp_get_product_videos( $post->ID ),
            'resources' => msp_get_product_resources( $post->ID ),
        );

        // $tabs['description']['callback'] = "msp_maybe_append_description";


        foreach( $custom_tabs as $key => $data ){
            if( ! empty( $data ) ){
                $tabs[$key] = array(
                    'title'    => deslugify($key),
                    'callback' => 'msp_get_'. $key .'_tab',
                    'priority' => $priority += 5,
                );
            }
        }


        // Renamed additional info
        if( $product->has_attributes() || $product->has_dimensions() || $product->has_weight() ) {
            $tabs['additional_information']['title'] = 'Specifications';
        }

        // seperate reviews tab
        unset( $tabs['reviews'] );

        return $tabs;
    }

    public function msp_form_field_args( $args, $key, $value ){
        $args['class'] = array('col-12');
        $args['input_class'] = array('form-control');
        return $args;
    }

    public function create_theme_pages(){
        $slugs = array( 'buy-again', 'quote', 'contact' );

        foreach( $slugs as $slug ){
            if( ! $this->the_slug_exists( $slug ) ){
                $shortcode = str_replace( '-', '_', $slug );
                wp_insert_post( array(
                    'post_title' => deslugify( $slug ),
                    'post_content' => "[$shortcode]",
                    'post_status' => 'publish',
                    'post_author' => 1,
                    'post_type' => 'page'
                ) );
            }
        }
    }

    public function the_slug_exists( $post_name ) {
        global $wpdb;
        return ( $wpdb->get_row("SELECT post_name FROM $wpdb->posts WHERE post_name = '" . $post_name . "'", 'ARRAY_A') );
    }

    public function myStartSession(){
        if(!session_id()) {
            session_start();
        }
    }

    public function myEndSession() {
        session_destroy ();
    }

    

    public function wp_localize_scripts( $arr ){
        foreach( $arr as $handle ){
            wp_localize_script( $handle, 'wp_ajax', array(
                'url' => admin_url( 'admin-ajax.php' ),
                'post' => admin_url( 'admin-post.php' )
            ) );
        }
    }

    public function register_menus(){
        // register menu for logged out users
        register_nav_menus( array(
            'logged-out' => __('Secondary menu for logged out users', 'msp')
        ) );
    }

    public function msp_password_strength(){
        return 1;
    }

    public static function get_product_image_src( $img_id ){
        return msp_get_product_image_src( $img_id );
    }

    public function maybe_hide_ltl_shipping_option( $rates ){
        $targeted_class = 54; // ltl shipping class id
        
        // if its not there, remove the LTL shipping option
        foreach( WC()->cart->cart_contents as $key => $values ) {
            if( $values[ 'data' ]->get_shipping_class_id() == $targeted_class ) {
                
                $ltl = $rates['flat_rate:6'];
                $rates = array( 'flat_rate:6' => $ltl );
                return $rates;
            } 
        }
    
        unset( $rates['flat_rate:6']);
        return $rates;
    }
    public static function get_wrapper_class(){
        return ( is_archive() ) ? 'container-fluid' : 'col-full';
    }

    /**
     * packs up an array for saving to the DB
     * @param array $thing
     * @return string
     */
    public static function package( $thing ){
        return base64_encode( serialize( $thing ) );
    }

    /**
     * unpacks a encoded serialzed string of data from the DB
     * @param string $thing
     * @return array
     */
    public static function unpackage( $thing ){
        return unserialize( base64_decode( $thing ) );
    }

}

//init
new MSP();
add_shortcode( 'contact', 'msp_get_contact_page' );

define('temp_file', ABSPATH.'/_temp_out.txt' );
add_action("activated_plugin", "activation_handler1");
function activation_handler1(){
    $cont = ob_get_contents();
    if(!empty($cont)) file_put_contents(temp_file, $cont );
}

add_action( "pre_current_active_plugins", "pre_output1" );
function pre_output1($action){
    if(is_admin() && file_exists(temp_file))
    {
        $cont= file_get_contents(temp_file);
        if(!empty($cont))
        {
            echo '<div class="error"> Error Message:' . $cont . '</div>';
            @unlink(temp_file);
        }
    }
}

function pluralize( $count, $str ){
    return ( $count <= 1 ) ? $str : $str . 's'; 
}

