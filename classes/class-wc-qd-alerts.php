<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

class WC_QD_Alerts {
  /**
   * Constructor
   */
  public function __construct() {
    global $wpdb;

    // Show error notice
    if ( get_transient( 'quaderno_error' ) ) {
      add_action( 'admin_notices', array( $this, 'quaderno_error' ) );
    }

    // Show review notice
    $post_count = $wpdb->get_var( "SELECT count(*) FROM " . $wpdb->prefix . "postmeta WHERE meta_key = '_quaderno_invoice'" );
    if ( $post_count > 10 && ! get_option( 'quaderno_dismiss_review' ) ) {
      add_action( 'admin_notices', array( $this, 'quaderno_review' ) );
    }

    add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_quaderno_alert_script' ) );
    add_action( 'wp_ajax_dismiss_quaderno_alert', array( $this, 'dismiss_quaderno_alert' ) );
  }

  /**
   * Show users an alert if an integration error has been detected
   */
  public function quaderno_error() {
    ?>
    <div id="quaderno-error" class="quaderno-notice notice notice-error is-dismissible">
      <p>
        <?php echo sprintf(__( "We have identified an issue with your Quaderno integration. For more details, please visit the <a href='%s'>logs page</a>.", 'woocommerce-quaderno' ), admin_url('admin.php?page=wc-status&tab=logs')); ?>
      </p>
    </div>
  <?php
  }

  /**
   * Ask users to leave a review for the plugin on wp.org.
   */
  public function quaderno_review() {
    ?>
    <div id="quaderno-review" class="quaderno-notice notice notice-info is-dismissible">
      <p>
        <?php echo sprintf(__( "Thank you for choosing Quaderno to manage your taxes in WooCommerce! Please consider <a href='%s' target='_blank'>writing a quick review</a>, so we can reach more business owners like you.", 'woocommerce-quaderno' ), 'https://wordpress.org/support/plugin/woocommerce-quaderno/reviews/?filter=5#new-post'); ?>
      </p>
    </div>
  <?php
  }

  public function enqueue_quaderno_alert_script() {
    wp_enqueue_script( 'quaderno-alert-script', plugin_dir_url(__FILE__) . '../assets/js/alerts.js', array('jquery'), null, true );
    wp_localize_script( 'quaderno-alert-script', 'quadernoAjax', array(
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'quaderno_nonce' )
    ));
  }

  public function dismiss_quaderno_alert() {
    check_ajax_referer('quaderno_nonce', 'nonce');

    if ( isset( $_POST['alert_id'] ) ) {
      $alert_id = sanitize_text_field( $_POST['alert_id'] );

      switch ( $alert_id ) {
        case 'quaderno-review': 
          update_option( 'quaderno_dismiss_review', true, false );
          break;
        case 'quaderno-error':
          delete_transient( 'quaderno_error' );
          break;
      }
    } else {
      wp_send_json_error( 'Alert ID not provided' );
    }

    // Successfully handled the request, send a success response
    wp_send_json_success();

    // Always die in functions hooked to wp_ajax_ to properly terminate the request
    wp_die();
  }
}