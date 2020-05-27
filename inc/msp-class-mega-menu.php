<?php 

class msp_mega_menu {

/*--------------------------------------------*
 * Constructor
 *--------------------------------------------*/

/**
 * Initializes the plugin by setting localization, filters, and administration functions.
 */
function __construct() {
    add_filter( 'wp_setup_nav_menu_item', array( $this, 'msp_add_custom_menu_field' ) );
    add_action( 'wp_update_nav_menu_item', array( $this, 'msp_update_custom_nav_fields'), 10, 3 );
    add_filter( 'wp_edit_nav_menu_walker', array( $this, 'msp_edit_walker'), 10, 2 );
} 


    /**
     * Add custom fields to $item nav object
     * in order to be used in custom Walker
     *
     * @access      public
     * @since       1.0 
     * @return      void
    */
    function msp_add_custom_menu_field( $menu_item ) {

        $menu_item->image_url = get_post_meta( $menu_item->ID, '_menu_item_image_url', true );
        return $menu_item;

    }


    /**
     * Save menu custom fields
     *
     * @access      public
     * @since       1.0 
     * @return      void
    */
    function msp_update_custom_nav_fields( $menu_id, $menu_item_db_id, $args ) {

        // Check if element is properly sent
        if ( is_array( $_REQUEST['menu-item-image-url']) ) {
            $subtitle_value = $_REQUEST['menu-item-image-url'][$menu_item_db_id];
            update_post_meta( $menu_item_db_id, '_menu_item_image_url', $subtitle_value );
        }

    }

    /**
     * Define new Walker edit
     *
     * @access      public
     * @since       1.0 
     * @return      void
    */
    function msp_edit_walker($walker,$menu_id) {

        return 'Walker_Nav_Menu_Edit_Custom';

    }

}

// instantiate plugin's class
$GLOBALS['msp_mega_menu'] = new msp_mega_menu();

include_once( PATH . '/template/megamenu/edit_custom_walker.php' );
include_once( PATH . '/template/megamenu/custom_walker.php' );

