<?php 

class MSP_Admin{
    /**
     * Class used for everything backend on this child-theme.
     */
    
    function __construct(){
        add_action('admin_menu', array( $this, 'theme_options') );
        add_action( 'add_meta_boxes', array( $this, 'msp_meta_boxes' ) );
        add_action( 'woocommerce_product_options_advanced', array( $this, 'submit_resources_tab' ) );
        add_action( 'woocommerce_process_product_meta', array( $this, 'process_product_resources_meta' ), 10, 2 );
        add_action( 'woocommerce_process_product_meta', array( $this, 'process_product_videos_meta' ), 10, 2 );
        add_action( 'woocommerce_process_product_meta', array( $this, 'process_product_size_guide_meta' ), 10, 2 );
        add_action( 'wp_ajax_msp_admin_sync_vendor', 'msp_admin_sync_vendor' );
        add_action( 'wp_ajax_msp_submit_theme_option', array( $this, 'ajax_update_option' ) );
        add_action( 'edit_user_profile', array( $this, 'add_net30_metabox'), 1 );
        add_action( 'edit_user_profile_update', array( $this, 'update_user_to_net30_terms'), 5 );
    }

    public function add_next_order_btn(){
        /**
         * Adds a next & previous order button for quick pagination of orders.
         */
        $orders = wc_get_orders( array('return' => 'ids', 'limit' => 100) );
        for( $i = 0; $i < sizeof($orders); $i++ ){
            if( $orders[$i] == $_GET['post'] ){
                if( ! empty( $orders[$i - 1] ) ) $prev = $orders[$i - 1];
                if( ! empty( $orders[$i + 1] ) ) $next = $orders[$i + 1];
            }
        }
        ?>
        <div class="wrap">
            <?php if( ! empty( $next ) ) : ?>
            <a href="/wp-admin/post.php?post=<?php echo $next ?>&action=edit" class="btn" style="float:left">Previous Order</a>
            <?php endif; ?>
            <?php if( ! empty( $prev ) ) : ?>
            <a href="/wp-admin/post.php?post=<?php echo $prev ?>&action=edit" class="btn" style="float:right">Next Order</a>
            <?php endif; ?>
        </div>
        <?php
    }

    public function ajax_update_option(){
        /**
         * AJAX function which adds data to options API
         */
        foreach( $_POST['options'] as $option ) {
            if( ! empty($option['key']) && ! empty($option['value']) ) update_option( $option['key'], $option['value'] );
        }

        wp_die();
    }

    public function add_net30_metabox(  $user){
        $is_net30 = get_user_meta( $user->ID, 'iww_net30', true );
        ?>
        <h1><?php esc_html_e( 'Activate Net 30', 'iww' ) ?></h1>
        <table class="form-table" style="background-color: red; color: #fff;">
                <tr>
                    <th><label for="iww_net30"><?php esc_html_e( 'Activate NET 30', 'iww' ); ?></label></th>
                    <td>
                <input type="checkbox" id="iww_net30" name="iww_net_30_data" value="1" <?php if ( $is_net30 ) echo ' checked="checked"'; ?> />
                    </td>
                </tr>
            </table>
        <?php
    }

    public function update_user_to_net30_terms( $user_id ){
        if( current_user_can( 'edit_user', $user_id ) ) {
            update_user_meta( $user_id, 'iww_net30', $_POST['iww_net_30_data'] );
            $sessions = WP_Session_Tokens::get_instance( $user_id );
            $sessions->destroy_all();
        }
    }

    public function msp_meta_boxes(){
        add_meta_box(
            'msp-product-video',
            __('Product Videos', 'msp'),
            'msp_product_video_callback',
            'product',
            'side',
            'low'
        );

        add_meta_box(
            'msp-size-guide',
            __('Product Size Guide', 'msp'),
            'msp_size_guide_callback',
            'product',
            'side',
            'low'
        );
    }

    public function theme_options(){
        /**
        * hooked into the admin_init so we can create menus and customize site settings
        */
        add_theme_page( 'MSP Theme Options', 'MSP Theme Options', 'manage_options', 'msp_options', array( $this, 'msp_options_callback' ) );

        add_action( 'admin_init', array( $this, 'register_theme_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widgets' ) );

        add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'submit_tracking_form' ) );
        add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'add_next_order_btn' ) );
        add_action( 'woocommerce_process_shop_order_meta', array( $this, 'save_order_meta' ) );
    }

    public function process_product_size_guide_meta( $id ){
        /**
         * Updates the size guide
         */
        if( isset( $_POST['_msp_size_guide'] ) ){
            update_post_meta( $id, '_msp_size_guide', $_POST['_msp_size_guide'] );
        }
    }

    public function process_product_videos_meta( $id ){
        /**
         * Updates product videos
         */
        
        $limit = sizeof($_POST['product_video']);
        $arr = array();
        for( $i = 0; $i <= $limit; $i++ ){
            if( ! empty( $_POST['product_video'][$i] ) ){
                array_push( $arr, array( $_POST['product_video'][$i] ) );
            }
        }

        update_post_meta( $id, '_msp_product_videos', MSP::package( $arr ) );
    }

    public function process_product_resources_meta( $id ){
        /**
         * Updates resources TODO: Could easily combine these functions.. ^^
         */
        $limit = sizeof($_POST['resource_url']);
        $arr = array();
        for( $i = 0; $i <= $limit; $i++ ){
            if( ! empty( $_POST['resource_label'][$i] ) && ! empty( $_POST['resource_url'][$i] ) ){
                array_push( $arr, array( $_POST['resource_label'][$i], $_POST['resource_url'][$i] ) );
            }
        }

        update_post_meta( $id, '_msp_resources', MSP::package( $arr ) );
    }

    public function submit_resources_tab(){
        /**
         * HTML form on back end for linking resources to products
         */
        global $post;
        $resources = msp_get_product_resources( $post->ID );
        ?>
        <div id="resource_tab" class="option_group">
            <p class="form-field resource_label_field">
                <label for="resource_label">Resources</label>
                <div style="display: flex;">
                    <p id="resource_input_wrapper">
                        <?php if( empty( $resources ) ) : ?>
                            <input type="text" id="resource_label" name="resource_label[0]" style="margin-right: 1rem;" placeholder="Label" />
                            <input type="text" id="resource_url" name="resource_url[0]" placeholder="URL" />
                            <br>
                        <?php else : ?>
                            <?php foreach( $resources as $index => $arr ) : ?>
                                <input type="text" id="resource_label" name="resource_label[<?php echo $index ?>]" style="margin-right: 1rem;" placeholder="Label" value="<?php echo $arr[0] ?>" />
                                <input type="text" id="resource_url" name="resource_url[<?php echo $index ?>]" placeholder="URL" value="<?php echo $arr[1] ?>" />
                                <br>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </p>
                </div>
                <button type="button" class="add_input_line" data-count=0>+</button>
            </p>
        </div>
        <?php
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
                <?php submit_button(); ?>
                <?php do_settings_sections( 'msp_options' ); ?>
                <?php submit_button(); ?>
            </form>        
        </div>
        <?php
    }

    /**
     *
     * dynamically creates options fields based on the arguments passed to add_settings_section.
     * */
    public function register_theme_settings(){
        add_settings_section(
            'front_page',
            'Front Page:',
            '', 
            'msp_options'
        );

        add_settings_section(
            'theme_options',
            'Theme Layout:',
            '', 
            'msp_options'
        );

        add_settings_section(
            'emails',
            'Emails:',
            '', 
            'msp_options'
        );

        add_settings_section(
            'ups_api_creds',
            'UPS API CREDS:', 
            '', 
            'msp_options'
        );


        add_settings_section(
            'integration', 
            'Integration:',
            '', 
            'msp_options'
        );


        $this->add_settings_field_and_register( 'msp_options', 'front_page', 'msp', array( 'promos' ) );
        $this->add_settings_field_and_register( 'msp_options', 'theme_options', 'msp', array( 'primary_color', 'link_color', 'header_background', 'footer_background', 'logo_width' ) );
        $this->add_settings_field_and_register( 'msp_options', 'emails', 'msp', array( 'contact_email' ) );
        $this->add_settings_field_and_register( 'msp_options', 'ups_api_creds', 'ups_api', array( 'key', 'username', 'password', 'account', 'mode', 'end_of_day' ) );
        $this->add_settings_field_and_register( 'msp_options', 'integration', 'integration', array( 'google_analytics_account_id' ) );
    }
}

new MSP_Admin();

// templates called by $this->add_settings_field_and_register();

/** ALL THE HTML CALLBACKS FOR THE THEME OPTIONS PAGE /wp-admin/themes.php?page=msp_options */
function msp_logo_width_callback(){
    echo '<input name="msp_logo_width" id="msp_logo_width" type="number" value="'. get_option( 'msp_logo_width' ) .'" class="code" />';
}

function msp_primary_color_callback(){
    echo '<input name="msp_primary_color" id="msp_primary_color" type="text" value="'. get_option( 'msp_primary_color' ) .'" class="color-field code" />';
}
function msp_link_color_callback(){
    echo '<input name="msp_link_color" id="msp_link_color" type="text" value="'. get_option( 'msp_link_color' ) .'" class="color-field code" />';
}
function msp_header_background_callback(){
    echo '<input name="msp_header_background" id="msp_header_background" type="text" value="'. get_option( 'msp_header_background' ) .'" class="color-field code" />';
}

function msp_footer_background_callback(){
    echo '<input name="msp_footer_background" id="msp_footer_background" type="text" value="'. get_option( 'msp_footer_background' ) .'" class="color-field code" />';
}

function msp_contact_email_callback(){
    echo '<input name="msp_contact_email" id="msp_contact_email" type="email" value="'. get_option( 'msp_contact_email' ) .'" class="code" />';
}

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

function integration_google_analytics_account_id_callback(){
    echo '<input name="integration_google_analytics_account_id" id="integration_google_analytics_account_id" type="text" value="'. get_option( 'integration_google_analytics_account_id' ) .'" class="code" />';
}



function msp_add_update_stock_widget(){
    /**
     * Form for getting stock data from specified vendors
     */
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

        <h3>Extras:</h3>
        <p>
            <label>Price</label>
            <input type="checkbox" name="price" />
        </p>

        <span class="feedback" style="font-weight: 600; font-color: red; font-size: 18px; "></span>
        <input type="hidden" name="action" value="msp_admin_sync_vendor" />
        <button id="submit_update_vendor" type="button" class="button button-primary" style="margin-top: 1rem;">Submit Vendor!</button>
    </form>
    <?php
}

function msp_product_video_callback( $post ){
    /**
     * Html form for submitting product videos // Maybe make a template
     */
    wp_nonce_field( basename( __FILE__ ), 'msp_product_video_callback' );
    $saved_urls = msp_get_product_videos( $post->ID );
    ?>
    <div id="msp_product_video_input_table">
        <p>Video Url(s)</p>
        <?php if( empty( $saved_urls ) ) : ?>
            <input type="text" name="product_video[0]">
        <?php else : ?>
            <?php foreach( $saved_urls as $index => $url ) : ?>
                <input type="text" name="product_video[<?php echo $index ?>]" value="<?php echo $url[0]; ?>">
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <button type="button" class="add" data-count=<?php echo sizeof( $saved_urls ) ?>>Add</button>
    <?php
}

function msp_promos_callback(){
    global $wpdb;
    $options = $wpdb->get_results( "SELECT * FROM $wpdb->options WHERE option_name LIKE 'msp_promo_src_%' AND option_value != '' " );
    $max = ( sizeof( $options ) > 0 ) ? sizeof($options) : 0;
    ?>
    
    <table id="msp-front-page-builder" class="widefat fixed" cellspacing="0">
        <caption>***AJAX*** Do not include the site url, just everything after the / (eg. "/wp-content/2019/09/photo.php" )</caption>
        <thead>
            <th>Page Link</th>
            <th>Image Link</th>
            <th></th>
        </thead>
        <tbody>
            <?php for( $i = 0; $i <= $max; $i++ ) :
                $src = get_option( 'msp_promo_src_' . $i );
                ?>
                    <tr>   
                        <td><input type="text" name="msp_promo_link_<?php echo $i ?>" value="<?php echo get_option( 'msp_promo_link_' . $i ) ?>" /></td>
                        <td><input type="text" name="msp_promo_src_<?php echo $i ?>" value="<?php echo get_option( 'msp_promo_src_' . $i ) ?>" /></td>
                        <?php if( $i == 0 ) : ?> <td><button class="add" type="button" role="button" >+ ADD +</button></td> <?php endif;  // lazy ?>
                    </tr>
            <?php endfor; ?>
        </tbody>
    </table>
    <?php
}

function msp_size_guide_callback( $post ){
    /**
     * Html form for submitting product size guide // Maybe make a template
     */
    $size_guide_src = get_post_meta( $post->ID, '_msp_size_guide', true );
    ?>
    <div id="msp_product_video_input_table">
        <p>Size Guide</p>
        <input type="url" name="_msp_size_guide" class="code" value="<?php echo $size_guide_src ?>" />
    </div>
    <?php
}
