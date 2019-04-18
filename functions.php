<?php 

defined( 'ABSPATH' ) || exit;

//globals
define('URI', get_stylesheet_directory_uri() );
define('PATH', get_stylesheet_directory() );

//require
require_once( PATH . '/vendor/class-wp-bootstrap-navwalker.php' );
require_once( PATH . '/admin-functions.php' );
require_once( PATH . '/inc/msp-class-user-history.php' );
require_once( PATH . '/inc/msp-template-hooks.php' );
require_once( PATH . '/inc/msp-template-functions.php' );

/**
 * Front-end Theme Settings
 */

class MSP{
    function __construct(){
        add_action('init', array( $this, 'myStartSession'), 1 );

        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'after_setup_theme', array( $this, 'register_menus' ) );

        add_action('wp_logout', array( $this, 'myEndSession') );
        add_action('wp_login', array( $this, 'myEndSession') );
    }

    public function myStartSession(){
        if(!session_id()) {
            session_start();
        }
    }

    public function myEndSession() {
        session_destroy ();
    }

    /**
     * Enqueue scripts & css for child theme.
     */
    public function enqueue_scripts(){
        // Custom javascript functions
        wp_enqueue_script( 'msp', URI . '/inc/functions.js', array('jquery'), filemtime( __DIR__ . '\inc\functions.js' ), true );
        wp_localize_script( 'msp', 'wp_ajax', array(
            'url' => admin_url( 'admin-ajax.php' ),
        ) );

        // Font Awesome
        wp_enqueue_style( 'font-awesome', 'https://use.fontawesome.com/releases/v5.8.1/css/all.css' );

        //Twitter Bootstrap - https://getbootstrap.com/docs/4.3/getting-started/introduction/
        wp_enqueue_style( 'bootstrap', URI . '/vendor/bootstrap-4.3.1-dist/css/bootstrap.min.css' );
        wp_enqueue_script( 'bootstrap', URI . '/vendor/bootstrap-4.3.1-dist/js/bootstrap.bundle.min.js' );

        //slideout.js - https://github.com/Mango/slideout
        wp_enqueue_script( 'slideout', URI . '/vendor/slideout/dist/slideout.min.js', array(), 
            filemtime( __DIR__ . '\vendor\slideout\dist\slideout.min.js' ), true );
    }

    public function register_menus(){
        // register menu for logged out users
        register_nav_menus( array(
            'logged-out' => __('Secondary menu for logged out users', 'msp')
        ) );
    }


}


//init
new MSP();