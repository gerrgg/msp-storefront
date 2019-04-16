<?php 

//globals
define('URI', get_stylesheet_directory_uri() );
define('PATH', get_stylesheet_directory() );

//require
require_once( PATH . '/admin-functions.php' );

/**
 * Front-end Theme Settings
 */

class MSP{
    function __construct(){
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }

    /**
     * Enqueue scripts & css for child theme.
     */
    public function enqueue_scripts(){
        // Custom javascript functions
        wp_enqueue_script( 'msp', URI . '/inc/functions.js', array('jquery'), filemtime( __DIR__ . '\inc\functions.js' ), true );

        //Twitter Bootstrap - https://getbootstrap.com/docs/4.3/getting-started/introduction/
        wp_enqueue_style( 'bootstrap', URI . '/vendor/bootstrap-4.3.1-dist/css/bootstrap.min.css' );
        wp_enqueue_script( 'bootstrap', URI . '/vendor/bootstrap-4.3.1-dist/js/bootstrap.bundle.min.js' );

        //slideout.js - https://github.com/Mango/slideout
        wp_enqueue_style( 'slideout', URI . '/vendor/slideout/dist/slideout.min.js' );
    }

}


//init
new MSP();