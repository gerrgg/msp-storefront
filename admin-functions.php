<?php 

class MSP_Admin{
    /**
     * Class used for everything backend on this child-theme.
     */
    
    function __construct(){
        add_action('admin_menu', array( $this, 'theme_options') );
        add_action( 'wp_ajax_msp_admin_sync_vendor', 'msp_admin_sync_vendor' );
    }

    /**
     * hooked into the admin_init so we can create menus and customize site settings
     */
    public function theme_options(){
        add_theme_page( 'MSP Theme Options', 'MSP Theme Options', 'manage_options', 'msp_options', array( $this, 'msp_options_callback' ) );

        add_action( 'admin_init', array( $this, 'register_theme_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widgets' ) );
        add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'submit_tracking_form' ) );
        add_action( 'woocommerce_process_shop_order_meta', array( $this, 'save_order_meta' ) );
    }

    public function enqueue_scripts( $hook ){
        wp_enqueue_script('admin', get_stylesheet_directory_uri() . '/js/admin.js');
    }

    public function add_dashboard_widgets(){
        wp_add_dashboard_widget(
            'msp_add_update_stock',
            'Update Vendors Stock',
            'msp_add_update_stock_widget'
        );
    
        global $wp_meta_boxes;
        $normal_dash = $wp_meta_boxes['dashboard']['normal']['core'];
        $custom_dash = array( 'msp_add_update_stock' => $normal_dash['msp_add_update_stock'] );
        unset( $normal_dash['msp_add_update_stock'] );
        $sorted_dash = array_merge( $custom_dash, $normal_dash );
        $wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dash;
    }

    public function submit_tracking_form(){
        /**
         * simple form which allows backend users to submit tracking information.
         */
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
        /**
         * When order meta is saved via the backend, this function saves the data as well as check for dyanmic cron jobs.
         * @param int $order_id
         */
        $custom_meta_keys = array( 'shipper', 'tracking' );
        foreach( $custom_meta_keys as $key ){
            $this->manage_cron_jobs( $key, $order_id );
            if( isset( $_POST[ $key ] ) && ! empty( $_POST[ $key ] ) ){
                update_post_meta( $order_id, $key, wc_clean( $_POST[ $key ] ) );
            }
        }
    }

    public static function manage_cron_jobs( $key, $order_id, $create = true  ){
        /**
         * Run when saving order meta data, this function checks if the key is in the $cron_map array
         * if true, clear any old cron_jobs, and create the new one mapped to the function in $cron_map.
         * @param string $key - meta key
         * @param int $order_id - order id
         */

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
            
            if( $create ){
                // //make new job
                wp_schedule_event( time(), 'daily', $cron_key, $order_id );
                add_action( $cron_key, $cron_map[$key], 1, 1 );
            }
        }
    }

    /**
     *
     * dynamically creates options fields based on the arguments passed to add_settings_section.
     * */
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

    /**
     * simple html wrapper for the theme options page.
     */
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


function msp_add_update_stock_widget(){
    ?>
    <form id="msp_add_update_stock_form" method="post" action="<?php echo admin_url( 'admin-ajax.php' ) ?>">
        <p>
            <label>Vendor: </label>
            <select name="vendor" >
                <option value="portwest" selected>Portwest</option>
                <option value="helly_hansen">Helly Hansen</option>
            </select>
        </p>
        <p>
            <label>Url: </label>
            <input type="url" name="url" required/>
        </p>

        <span class="feedback" style="font-weight: 600; font-color: red; font-size: 18px; "></span>
        <input type="hidden" name="action" value="msp_admin_sync_vendor" />
        <button id="submit_update_vendor" type="button" class="button button-primary" style="margin-top: 1rem;">Submit Vendor!</button>
    </form>
    <?php
}

function msp_admin_sync_vendor(){
    ob_start();
    $data = array( 
        'name' => $_POST['vendor'],
        'src'    => $_POST['url'],
        'sku_index' => ( $_POST['vendor'] == 'portwest' ) ? 1 : 16,
        'stock_index' => ( $_POST['vendor'] == 'portwest' ) ? 8 : 7
    );

    msp_get_data_and_sync( $data );
    $html = ob_get_clean();
    echo $html;
    wp_die();
}

function msp_get_data_and_sync( $vendor ){
    $start = microtime(true);

    $count = 0;

    $data = file_get_contents( $vendor['src'] );
    if( ! empty( $data ) ){
        foreach( msp_csv_to_array( $data ) as $item ){
            // sku_index and stock_index are the position of the data in the array,
            if( isset( $item[ $vendor['sku_index'] ] ) && isset( $item[ $vendor[ 'stock_index'] ] ) ){
                $sku = $item[ $vendor['sku_index'] ];
                $stock = $item[ $vendor['stock_index'] ];
                if( ! empty( $sku ) ){
                    $id = msp_get_product_id_by_sku( $sku );
                    if( ! empty( $id ) ){
                        msp_update_stock( $id, $stock );
                        $count++;
                    }
                }
            }
        }
    }

    $time_elapsed_secs = microtime(true) - $start;

    echo '<h2>Report</h2>';
    echo 'Products Updated: ' . $count . '.<br>';
    echo 'Time Elasped: ' . number_format( $time_elapsed_secs, 2 ) . ' seconds.<br>';
}

function msp_get_product_id_by_sku( $sku = false ) {
    if( ! $sku ) return null;

    global $wpdb;
    $product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $sku ) );
    return $product_id;
}

function msp_csv_to_array( $data ){
    $rows = explode("\n", $data);
    $s = array();

    foreach($rows as $row) {
        $s[] = str_getcsv($row);
    }

    return $s;
}


function msp_update_stock( $id, $stock){
    $instock = ( $stock > 0 ) ? 'instock' : 'outofstock';
      update_post_meta( $id, '_manage_stock', 'yes' );
      update_post_meta( $id, '_stock_status', $instock );
      update_post_meta( $id, '_stock', $stock );
  }

  
  