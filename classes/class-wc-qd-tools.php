<?php
  if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
  }

  class WC_QD_Tools {
    function __construct() {
      add_filter( 'woocommerce_debug_tools', array( $this,'qd_cache_cleaning_button' ) );
    }
  
    function qd_cache_cleaning_button( $old ) {
        $new = array(
            'clear_tax_cache' => array(
                'name'    => __( 'Clear tax cache', 'woocommerce-quaderno' ),
                'button'  => __( 'Clear', 'woocommerce' ),
                'desc'    => sprintf(
                  '<strong class="red">%1$s</strong> %2$s',
                  __( 'Note:', 'woocommerce' ),
                  __( 'This tool will empty the tax cache created by Quaderno.', 'woocommerce-quaderno' )
                ),
                'callback'  => array( $this, 'qd_cache_cleaning_action' ),
            ),
        );
        $tools = array_merge( $old, $new );
        return $tools;
    }
    
    function qd_cache_cleaning_action() {
      global $wpdb;

      $sql = 'DELETE FROM ' . $wpdb->options . ' WHERE option_name LIKE "_transient_quaderno_tax_%"';
      $wpdb->query($sql);

      $sql = 'DELETE FROM ' . $wpdb->options . ' WHERE option_name LIKE "%_vat_number_%"';
      $wpdb->query($sql);

      echo '<div class="updated"><p>' . __( 'The tax cache has been emptied.', 'woocommerce-quaderno' ) . '</p></div>';
    }
}
