<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

class WC_QD_Alerts {
  /**
   * Constructor
   */
  public function __construct() {
    // Hooks
    if ( get_transient( 'quaderno_alert' ) ) {
      add_action( 'admin_notices', array( $this, 'quaderno_alert' ) );
      add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_quaderno_alert_script' ) );
      add_action( 'wp_ajax_dismiss_quaderno_alert', array( $this, 'dismiss_quaderno_alert' ) );
    }
  }

  public function quaderno_alert() {
    ?>
    <div id="quaderno-alert" class="notice notice-error is-dismissible">
      <p>
        <?php echo sprintf(__( "We have identified an issue with your Quaderno integration. For more details, please visit the <a href='%s'>logs page</a>.", 'woocommerce-quaderno' ), admin_url('admin.php?page=wc-status&tab=logs')); ?>
      </p>
    </div>
  <?php
  }

  public function enqueue_quaderno_alert_script() {
    wp_enqueue_script('quaderno-alert-script', plugin_dir_url(__FILE__) . '../assets/js/alerts.js', array('jquery'), null, true);
    wp_localize_script('quaderno-alert-script', 'quadernoAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('quaderno_nonce')
    ));
  }

  public function dismiss_quaderno_alert() {
    check_ajax_referer('quaderno_nonce', 'nonce');

    // Delete the transient
    delete_transient('quaderno_alert');

    // Always die in functions hooked to wp_ajax_ to properly terminate the request
    wp_die();
  }
}