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
        add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'submit_tracking_form' ) );
        add_action( 'woocommerce_process_shop_order_meta', array( $this, 'save_order_meta' ) );
    }

    public function submit_tracking_form(){
        woocommerce_wp_select( array(
            'id' => 'shipper',
            'label' => 'Shipper:',
            'value' => '',
            'options' => array(
            '' => '',
                'ups' => 'UPS',
                'fedex' => 'Fedex',
                'usps' => 'Post Office',
            ),
            'wrapper_class' => 'form-field-wide'
        ) );

        woocommerce_wp_text_input( array(
            'id' => 'tracking',
            'label' => 'Tracking #:',
            'value' => '',
            'wrapper_class' => 'form-field-wide',
        ) );

        echo '<button class="button button-primary" style="width: 100%; margin-top: 1rem;">Send Tracking</button>';
    }

    public function save_order_meta( $order_id ){
        $custom_meta_keys = array( 'shipper', 'tracking' );
        foreach( $custom_meta_keys as $key ){
            $this->check_for_cron_jobs( $key, $order_id );
            if( isset( $_POST[ $key ] ) && ! empty( $_POST[ $key ] ) ){
                update_post_meta( $order_id, $key, wc_clean( $_POST[ $key ] ) );
            }
        }
    }

    public function check_for_cron_jobs( $key, $order_id ){
        $cron_map = array(
            'tracking' => 'msp_update_order_tracking'
        );

        if( isset( $cron_map[$key] ) ){
            //create key
            $cron_key = 'msp_update_order_' . $order_id . '_' . $key;
    
            //get rid of old job
            $timestamp = wp_next_scheduled( $cron_key, $order_id );
            wp_unschedule_event( $timestamp, $cron_key, $order_id );
            
            update_post_meta( $order_id, $cron_key, $timestamp );

    
            // //make new job
            wp_schedule_event( time(), 'daily', $cron_key, $order_id );
            add_action( $cron_key, "msp_update_order_tracking", 1, 1 );
        }
    }

    public function register_theme_settings(){
        add_settings_section(
            'ups_api_creds', //id
            'UPS API CREDS:', // header
            '', // section label
            'msp_options' // the page to put it on
        );

        add_settings_section(
            'theme_options', //id
            'Theme Layout:', // header
            '', // section label
            'msp_options' // the page to put it on
        );

        $this->add_settings_field_and_register( 'msp_options', 'ups_api_creds', 'ups_api', array( 'key', 'username', 'password', 'account', 'mode', 'end_of_day' ) );
        $this->add_settings_field_and_register( 'msp_options', 'theme_options', 'msp', array( 'logo_width' ) );
    }

    public function add_settings_field_and_register( $page, $section, $prefix, $keys ){
        /**
         * simplfies the task of adding settings fields and registering.
         */

        foreach( $keys as $key ){
            add_settings_field(
                $prefix . "_$key",
                deslugify( $key ) . ':',
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
function ups_api_mode_callback(){
    echo '<input name="ups_api_mode" id="ups_api_mode_test" type="radio" value="https://wwwcie.ups.com/ups.app/xml/" class="code" '. checked( 'https://wwwcie.ups.com/ups.app/xml/', get_option( 'ups_api_mode' ), false ) .' />Test';
    echo '<br>';
    echo '<input name="ups_api_mode" id="ups_api_mode_production" type="radio" value="https://onlinetools.ups.com/ups.app/xml/" class="code" '. checked( 'https://onlinetools.ups.com/ups.app/xml/', get_option( 'ups_api_mode' ), false ) .' />Production';
}
function ups_api_end_of_day_callback(){
    echo '<input type="time" id="ups_api_end_of_day" name="ups_api_end_of_day" value="'. get_option( 'ups_api_end_of_day' ) .'">';
}

function msp_logo_width_callback(){
    echo '<input name="msp_logo_width" id="msp_logo_width" type="number" value="'. get_option( 'msp_logo_width' ) .'" class="code" />';
}

// helpers
function deslugify( $str ){
    return ucwords( str_replace( array('_', '-'), ' ', $str ) );
}