<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
  }

  class WC_QD_Order_Manager {

   public function setup() {
    add_filter( 'woocommerce_my_account_my_orders_actions', array( $this, 'show_invoice_action' ), 10, 2 );
    add_action( 'woocommerce_order_details_after_order_table', array( $this, 'show_invoice_button'), 10 );
    add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'display_field' ), 10, 1 );
  }

	/**
	 * Show invice action
	 *
   * @param $actions
   * @param $order
	 */
	public function show_invoice_action( $actions, $order ) {
    $permalink = get_post_meta( $order->get_id(), '_quaderno_url', true );

    if ( !empty($permalink) ) {
      $actions = array_merge($actions, array(
        'invoice' => array(
          'url'  => esc_url( $permalink ),
          'name' => esc_html__( 'View Invoice', 'woocommerce-quaderno' )
        )
      ));
    }

    return $actions;
  }

  /**
   * Show invice button
   *
   * @param $order
   */
  public function show_invoice_button( $order ) {
    if ( ! $order || ! is_user_logged_in() ) {
      return;
    }

    $permalink = get_post_meta( $order->get_id(), '_quaderno_url', true );
    if ( !empty($permalink) ) {
      ?>

      <p class="view_invoice">
      </strong> <a href="<?php echo esc_url( $permalink ); ?>" class="button"><?php _e( 'View Invoice', 'woocommerce-quaderno'); ?></a>
    </p>

    <?php
  }  
}

  /**
   * Display the invoice permalink in the backend
   *
   * @param $order
   */
  public function display_field( $order ) {
    $permalink = get_post_meta( $order->get_id(), '_quaderno_url', true );
    if ( !empty($permalink) ) {
      echo '<p><a href="' . $permalink . '" target="_blank">' . esc_html__( 'View Invoice', 'woocommerce-quaderno' ) . '</a></p>';
    }
  }

}