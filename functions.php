<?php 
defined( 'ABSPATH' ) || exit;

//globals
define('URI', get_stylesheet_directory_uri() );
// URI => http://one.wordpress.test/wp-content/themes/msp-storefront


define('PATH', get_stylesheet_directory() );
// PATH => /srv/www/wordpress-one/public_html/wp-content/themes/msp-storefront 

require_once( PATH . '/vendor/autoload.php' );
require_once( PATH . '/admin-functions.php' );
require_once( PATH . '/inc/msp-template-hooks.php' );
require_once( PATH . '/inc/msp-template-functions.php' );
require_once( PATH . '/inc/msp-template-filters.php' );
require_once( PATH . '/inc/msp-helper-functions.php' );
require_once( PATH . '/inc/msp-class-mega-menu.php' );
require_once( PATH . '/inc/msp-ups-time-in-transit.php');

/**
 * Front-end Theme Settings
 */

class MSP{
    function __construct(){
        // Creates custom theme pages upon activation
        add_action( 'init', array( $this, 'create_theme_pages' ), 2 );
        add_action( 'init', array( $this, 'maybe_create_specifications_table' ), 3 );

        // Add custom scripts
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        // Add custom menu for loggin out
        add_action( 'after_setup_theme', array( $this, 'register_menus' ) );
        // Add recaptcha to footer
        add_action( 'wp_footer', array( $this, 'add_recaptcha_script_to_footer' ) );
        // modifies default password strength
        add_filter( 'woocommerce_min_password_strength', array( $this, 'msp_password_strength' ) );
        // Add custom tabs to single product
        add_filter( 'woocommerce_product_tabs', array( $this, 'msp_product_tabs' ) );
        // Add a coondition for which shipping options are presented based on shipping class
        add_filter( 'woocommerce_package_rates', array( $this, 'custom_shipping_rules' ), 50, 2 );
        // Change how many columns are in the footer
        add_filter( 'storefront_footer_widget_columns', function(){ return 1; } );
        // Changes the order of fields in checkout
        add_filter( 'woocommerce_checkout_fields', array( $this, 'msp_checkout_fields' ), 100 );
        // Adds a condition for which payment options
        add_filter( 'woocommerce_available_payment_gateways', array($this, 'msp_enable_net30'), 999 );
    }

    function maybe_create_specifications_table(){
        /**
         * Creates the wp_specification table if not exists
         */

         // must require upgrade.php to use maybe_create_table()
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'specifications';
        $sql = "CREATE TABLE $table_name (
            spec_id bigint(20)  NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            spec_label varchar(100) NOT NULL,
            spec_value varchar(255) NOT NULL,
            PRIMARY KEY  (spec_id)
        ) $charset_collate;";

        maybe_create_table($table_name, $sql);
    }


    public function add_recaptcha_script_to_footer(){
        /**
         * Add google recaptcha to website if a code is defined.
         */
        $recaptcha = get_option('integration_google_recaptcha');
        if( ! empty( $recaptcha ) ) :
            ?>
                <script src="https://www.google.com/recaptcha/api.js?render=<?php echo $recaptcha; ?>"></script>
                <script>
                    grecaptcha.ready(function() {
                        grecaptcha.execute('<?php echo $recaptcha ?>');
                    });
                </script>
            <?php
        endif;
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
        /**
        * Customizations to the Woocommerce/checkout
        * @param array - The default woocommerce fields
        */

        // var_dump( $fields );

        // Move email to top for capturing abandoned carts
        $fields['billing']['billing_email']['priority'] = 1;

        $fields['order']['order_comments']['placeholder'] = 'Anything we should know? Need your order by a specific day?';
        $fields['order']['order_comments']['class'][] = "w-100";
        
        // Add purchase field
        $fields['billing']['billing_po'] = array(
            'label'     => __('Purchase Order', 'woocommerce'),
            'required'  => false,
            'class'     => array('col-12 p-0'),
            'priority'	=> 100,
        );


        $keys = array( 'billing', 'shipping' );
        foreach( $keys as $key ){
            $fields[$key][$key . '_first_name']['class'][] = 'col-6 p-0';
            $fields[$key][$key . '_last_name']['class'][] = 'col-6 p-0';
        }
        

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

        $this->inline_css();

        // make admin urls available to JS
        wp_localize_script( 'main', 'wp_ajax', array(
            'url' => admin_url( 'admin-ajax.php' ),
            'post' => admin_url( 'admin-post.php' ),
            'cookie_version' => get_option( 'promo_pop_up_version' )
        ) );
        
        // slideout.js - https://github.com/Mango/slideout
        wp_enqueue_script( 'slideout', URI . '/vendor/slideout/dist/slideout.min.js', array(), 
            '', true );

        //Twitter Bootstrap - https://getbootstrap.com/docs/4.3/getting-started/introduction/
        wp_enqueue_style( 'bootstrap', URI . '/vendor/bootstrap-4.3.1-dist/css/bootstrap.min.css' );
        wp_enqueue_script( 'bootstrap', URI . '/vendor/bootstrap-4.3.1-dist/js/bootstrap.bundle.min.js', array( 'jquery' ), '', true );

        //Owl Carousel - https://owlcarousel2.github.io/OwlCarousel2/
        wp_enqueue_style( 'owl-carousel', URI . '/vendor/OwlCarousel2-2.3.4/dist/assets/owl.carousel.min.css' );
        wp_enqueue_script( 'owl-carousel', URI . '/vendor/OwlCarousel2-2.3.4/dist/owl.carousel.min.js', array( 'jquery' ), '', true );
        
        //Select2 - https://select2.org/
        wp_enqueue_style('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css' );
	    wp_enqueue_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js', array('jquery') );
    }

    public function inline_css(){
        /**
         * Checks theme options, and outputs css rules accordingly.
         */
        $color_primary = get_option( 'msp_primary_color' );
        $color_secondary = get_option( 'msp_secondary_color' );

        $css = "
            body.woocommerce-checkout #payment ul.payment_methods li a { color: $color_primary !important; }
            #place_order { background-color: $color_primary !important; }
            table:not(.variations) tr th { color: $color_primary !important; }
            #self-plug i.fa-coffee { color: $color_primary !important; }
            #self-plug i.fa-heart { color: $color_secondary !important; }
            #masthead .navbar .cart-wrapper .item-counter { color: $color_primary !important; }
            #masthead button.mobile-menu-button i { color: $color_primary !important; }
            body.single-product .product form.cart button.single_add_to_cart_button { background-color: $color_primary !important; }
            #masthead #header-menu ul.navbar-nav li { border-bottom: 2px solid $color_primary !important; }
            .msp-shop-subnav ul.navbar-nav > li .sub-menu li a { color: $color_secondary !important; }
            #msp-sidebar .widget-area span.widget-title { color: $color_secondary !important; }
            #msp-sidebar #mobile-filter-wrapper a.badge { background-color: $color_primary !important; }
            .msp-shop-subnav ul.navbar-nav > li.menu-item-has-children > a::after { color: $color_secondary !important; }
            .single-product-featured-item .feature-text span:first-child { color: $color_primary !important; }
            .site-footer h1, .site-footer h2, .site-footer h3, .site-footer h4, .site-footer h5, .site-footer h6 { color: $color_primary !important; }
            .widget_price_filter .ui-slider .ui-slider-range, .widget_price_filter .ui-slider .ui-slider-handle { color: $color_primary !important; }
            body.single-product #resources_tab ul li::before { color: $color_secondary !important; }
            .woocommerce-info, .woocommerce-noreviews, p.no-comments { background-color: $color_secondary !important; }
            .star-rating span::before, .quantity .plus, .quantity .minus, p.stars a:hover::after, p.stars a::after, .star-rating span::before, #payment .payment_methods li input[type='radio']:first-child:checked + label::before { color: $color_secondary !important }
        ";

        wp_register_style( 'msp', false );
        wp_enqueue_style( 'msp' );
        wp_add_inline_style( 'msp', $css );
            
    }

    public function msp_product_tabs( $tabs ){
        /**
        * Custom product tabs added to the woocommerce_product_tabs filter
        * @param array - The default order of woocommerce tabs
        */
        global $post;
        global $product;
        
        // Start at 30 to skip past decription, specificiations
        $priority = 30;

        // We link to a function to see if the tab is nessicary for calling.
        // If a product does not have a video, resouse or standard we do not include that tab.
        $custom_tabs = array(
            // 'product_videos' => msp_get_product_videos( $post->ID ),
            // 'standards' => msp_get_product_tags( $post->ID ), 
            'resources' => msp_get_product_resources( $post->ID ),
        );

        
        foreach( $custom_tabs as $key => $data ){
            // check if tab should be included
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
            $tabs['additional_information']['priority'] = 95;
        }

        // put reviews at the bottom
        $tabs['reviews']['priority'] = 100;

        return $tabs;
    }

    public function msp_form_field_args( $args, $key, $value ){
        /**
        * Change the class of inputs @ checkout
        */
        $args['class'] = array('col-12');
        $args['input_class'] = array('form-control');
        return $args;
    }

    public function create_theme_pages(){
        /**
        * Create pages the theme requires to operate.
        */
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
        /**
        * Check if a page exists based on the slug
        */
        global $wpdb;
        return ( $wpdb->get_row("SELECT post_name FROM $wpdb->posts WHERE post_name = '" . $post_name . "'", 'ARRAY_A') );
    }

    public function register_menus(){
        // https://github.com/wp-bootstrap/wp-bootstrap-navwalker#installation
        require_once PATH . '/class-wp-bootstrap-navwalker.php';

        // register menu for logged out users
        register_nav_menus( array(
            'logged-out' => __('Secondary menu for logged out users', 'msp'),
            'under_header' =>  __('Menu directly under the header', 'msp'),
        ) );
        
    }

    public function msp_password_strength(){
        /** 
        *Reduce the strength requirement on the woocommerce password.
        * 
        * Strength Settings
        * 3 = Strong (default)
        * 2 = Medium
        * 1 = Weak
        * 0 = Very Weak / Anything
        */
        return 1;
    }

    public static function get_product_image_src( $img_id ){
        return msp_get_product_image_src( $img_id );
    }

    public function custom_shipping_rules( $rates ){
        /**
        * Checks cart contents, compares product shipping class with $custom_rules
        * and unsets differant methods accordingly.
        * @param array - Shipping methods
        */

        // These are the classes assigned to individual products (the condition)
        $shipping_classes = array(
            'ltl' => get_option( 'woo_ltl_shipping_class_id' ),
            'ups_only' => get_option( 'woo_ups_only_shipping_class_id' )
        );

        // There are the methods which are removed and added as needed
        $shipping_methods = array(
            'free' => empty( get_option( 'woo_free_shipping_method_id' ) ) ? '9' : get_option( 'woo_free_shipping_method_id' ),
            'ltl' => empty( get_option( 'woo_ltl_shipping_method_id' ) ) ? '6' : get_option( 'woo_ltl_shipping_method_id' ),
        );

        // loop cart and check for conditions
        foreach( WC()->cart->cart_contents as $key => $values ) {

            // if any products match LTL shipping class, return ONLY ltl freight option
            if( $values[ 'data' ]->get_shipping_class_id() == $shipping_classes['ltl'] ) {
                $ltl = $rates['flat_rate:' . $shipping_methods['ltl']];
                $rates = array( 'flat_rate:' . $shipping_methods['ltl'] => $ltl );
                return $rates;
            }

            // If any products match the UPS ONLY shipping method, remove free shipping and flat rate ground  
            if( $values[ 'data' ]->get_shipping_class_id() == $shipping_classes['ups_only'] ) {
                unset( $rates['free_shipping:' . $shipping_methods['free']]);
            }
        }
        
        // If we make it this far - delete the LTL fright option
        unset( $rates['flat_rate:' . $shipping_methods['ltl']]);
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



 

