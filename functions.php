<?php 

class MSP{
    function __construct(){
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }


    public function enqueue_scripts(){
        define('PATH', get_template_directory_uri() . '-child' );

        wp_enqueue_style( 'bootstrap', PATH . '/vendor/bootstrap-4.3.1-dist/css/bootstrap.min.css' );
        wp_enqueue_script( 'bootstrap', PATH . '-child/vendor/bootstrap-4.3.1-dist/js/bootstrap.bundle.min.js' );
    }
}

new MSP();