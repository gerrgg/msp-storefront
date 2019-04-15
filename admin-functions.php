<?php 

class MSP_Admin{
    /**
     * Class used for everything backend on this child-theme.
     */
    
    function __construct(){
        add_action('admin_menu', array( $this, 'theme_options') );
    }

    public function theme_options(){
        add_theme_page( 'MSP Theme Options', 'MSP Theme Options', 'manage_options', 'msp_options', array( $this, 'msp_options_callback' ) );

        add_action( 'admin_init', array( $this, 'register_theme_settings' ) );
    }

    public function register_theme_settings(){
        add_settings_section(
            'ups_api_creds', //id
            'UPS API CREDS:', // header
            '', // section label
            'msp_options' // the page to put it on
        );

        $this->add_settings_field_and_register( 'msp_options', 'ups_api_creds', 'ups_api', array( 'key', 'username', 'password', 'account' ) );
    }

    public function add_settings_field_and_register( $page, $section, $prefix, $keys ){
        /**
         * simplfies the task of adding settings fields and registering.
         */

        foreach( $keys as $key ){
            add_settings_field(
                $prefix . "_$key",
                ucfirst( $key ) . ':',
                $prefix . '_' . $key . '_callback',
                $page,
                $section
            );
            register_setting( $page, $prefix . "_$key" );
        }
    }

    public function msp_options_callback(){
        ?>
        <div class="wrap">
        <h1>MSP Theme Options</h1>
        <form method="post" action="options.php">
            <?php settings_fields( 'msp_options' ); ?>
    
            <?php do_settings_sections( 'msp_options' ); ?>
            <?php submit_button(); ?>
        </form>
        </div>
        <?php
    }
}

new MSP_Admin();

// templates called by $this->add_settings_field_and_register();

function ups_api_key_callback(){
    echo '<input name="ups_api_key" id="ups_api_key" type="text" value="'. get_option( 'ups_api_key' ) .'" class="code" />';
}
function ups_api_username_callback(){
    echo '<input name="ups_api_username" id="ups_api_username" type="text" value="'. get_option( 'ups_api_username' ) .'" class="code" />';
}
function ups_api_password_callback(){
    echo '<input name="ups_api_password" id="ups_api_password" type="text" value="'. get_option( 'ups_api_password' ) .'" class="code" />';
}
function ups_api_account_callback(){
    echo '<input name="ups_api_account" id="ups_api_account" type="text" value="'. get_option( 'ups_api_account' ) .'" class="code" />';
}