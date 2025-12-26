<?php
  if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
  }

  class WC_QD_Tools {
    public function __construct() {
      add_filter( 'woocommerce_debug_tools', array( $this,'qd_cache_cleaning_button' ) );
      add_filter( 'woocommerce_debug_tools', array( $this,'qd_metadata_cleaning_button' ) );
      add_filter( 'woocommerce_debug_tools', array( $this,'qd_send_orders_to_quaderno' ) );
    }
  
    public function qd_cache_cleaning_button( $old ) {
      $new = array(
          'clear_tax_cache' => array(
              'name'    => __( 'Quaderno - Clear tax cache', 'woocommerce-quaderno' ),
              'button'  => __( 'Clear', 'woocommerce-quaderno' ),
              'desc'    => sprintf(
                '<strong class="red">%1$s</strong> %2$s',
                __( 'Note:', 'woocommerce-quaderno' ),
                __( 'This tool will empty the tax cache created by Quaderno.', 'woocommerce-quaderno' )
              ),
              'callback'  => array( $this, 'qd_cache_cleaning_action' ),
          ),
      );
      $tools = array_merge( $old, $new );
      return $tools;
    }

    public function qd_metadata_cleaning_button( $old ) {
      $new = array(
          'clear_metadata' => array(
              'name'    => __( 'Quaderno - Clear all data', 'woocommerce-quaderno' ),
              'button'  => __( 'Clear', 'woocommerce-quaderno' ),
              'desc'    => sprintf(
                '<strong class="red">%1$s</strong> %2$s',
                __( 'Note:', 'woocommerce-quaderno' ),
                __( 'This tool will clear all data created by Quaderno. Use this if you are going to connect your WooCommerce store to a new Quaderno account.', 'woocommerce-quaderno' )
              ),
              'callback'  => array( $this, 'qd_metadata_cleaning_action' ),
          ),
      );
      $tools = array_merge( $old, $new );
      return $tools;
    }

    public function qd_send_orders_to_quaderno( $old ) {
      $new = array(
          'send_orders_to_quaderno' => array(
              'name'    => __( 'Quaderno - Send orders', 'woocommerce-quaderno' ),
              'button'  => __( 'Send', 'woocommerce-quaderno' ),
              'desc'    => sprintf(
                '<strong class="red">%1$s</strong> %2$s',
                __( 'Note:', 'woocommerce-quaderno' ),
                __( 'This tool will send all completed orders from the last 15 days that don\'t have invoices to Quaderno.', 'woocommerce-quaderno' )
              ),
              'callback'  => array( $this, 'send_orders_to_quaderno_action' ),
          ),
      );
      $tools = array_merge( $old, $new );
      return $tools;
    }
    
    public function qd_cache_cleaning_action() {
      global $wpdb;

      // delete all transients
      $wpdb->query( $wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
        $wpdb->esc_like( '_transient_quaderno_tax_' ) . '%'
      ) );

      $wpdb->query( $wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
        '%' . $wpdb->esc_like( '_vat_number_' ) . '%'
      ) );

      echo esc_html( '<div class="updated"><p>' . __( 'The tax cache has been emptied.', 'woocommerce-quaderno' ) . '</p></div>' );
    }

    public function qd_metadata_cleaning_action()
    {
      global $wpdb;

      $wpdb->query( $wpdb->prepare(
        "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s",
        $wpdb->esc_like( '_quaderno_' ) . '%'
      ) );

      $wpdb->query( $wpdb->prepare(
        "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE %s",
        $wpdb->esc_like( '_quaderno_' ) . '%'
      ) );

      echo esc_html( '<div class="updated"><p>' . __( 'All Quaderno data has been deleted.', 'woocommerce-quaderno' ) . '</p></div>' );
    }

    public function send_orders_to_quaderno_action() {
      // Calculate date 15 days ago
      $fifteen_days_ago = date( 'Y-m-d H:i:s', strtotime( '-15 days' ) );

      // Query orders from last 15 days
      $args = array(
        'status'     => 'completed',
        'type'       => 'shop_order',
        'limit'      => -1,
        'orderby'    => 'date',
        'order'      => 'DESC',
        'date_after' => $fifteen_days_ago,
      );

      $orders = wc_get_orders( $args );
      $sent_count = 0;
      $skipped_count = 0;

      // Get invoice manager instance
      $invoice_manager = new WC_QD_Invoice_Manager();

      foreach ( $orders as $order ) {
        // Skip if already has invoice
        if ( !empty( $order->get_meta( '_quaderno_invoice' ) ) ) {
          $skipped_count++;
          continue;
        }

        // Skip free orders
        if ( $order->get_total() == 0 ) {
          $skipped_count++;
          continue;
        }

        // Create invoice using existing method
        $invoice_manager->create_invoice( $order->get_id() );

        // Refresh order data and check if invoice was created
        $order = wc_get_order( $order->get_id() );
        if ( !empty( $order->get_meta( '_quaderno_invoice' ) ) ) {
          $sent_count++;
        }
      }

      // Display results
      if ( $sent_count > 0 || $skipped_count > 0 ) {
        echo '<div class="updated"><p>';
        if ( $sent_count > 0 ) {
          echo esc_html( sprintf(
            _n( '%d order sent to Quaderno.', '%d orders sent to Quaderno.', $sent_count, 'woocommerce-quaderno' ),
            $sent_count
          ) );
        }
        if ( $skipped_count > 0 ) {
          if ( $sent_count > 0 ) {
            echo ' ';
          }
          echo esc_html( sprintf(
            _n( '%d order skipped.', '%d orders skipped.', $skipped_count, 'woocommerce-quaderno' ),
            $skipped_count
          ) );
        }
        echo '</p></div>';
      } else {
        echo '<div class="updated"><p>' . esc_html( __( 'No orders found to send to Quaderno.', 'woocommerce-quaderno' ) ) . '</p></div>';
      }
    }
}
