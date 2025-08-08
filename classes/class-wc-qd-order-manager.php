<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
  }

  class WC_QD_Order_Manager {

   public function setup() {
    add_action( 'wp_enqueue_scripts', array( $this, 'after_my_orders_js' ), 10, 2);
    add_filter( 'woocommerce_my_account_my_orders_actions', array( $this, 'show_invoice_action' ), 10, 2 );
    add_action( 'woocommerce_order_details_after_order_table', array( $this, 'show_invoice_button'), 10, 1 );
    add_action( 'woocommerce_order_details_after_customer_address', array( $this, 'show_tax_id'), 10, 2 );
    add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'display_field' ), 10, 1 );
    add_filter( 'woocommerce_email_order_meta_fields', array( $this, 'add_email_order_meta' ), 10, 3 );
}

	/**
	 * Show invoice action
	 *
   * @param $actions
   * @param $order
	 */
	public function show_invoice_action( $actions, $order ) {
    $permalink = $order->get_meta( '_quaderno_url' );

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
   * Open invoice action in a new tab
   */
  function after_my_orders_js() {
    wp_add_inline_script(
      'woocommerce', 
      "jQuery(function($){
        $('a.invoice').each( function(){
          $(this).attr('target','_blank');
          });
        });",
        'after' // 'after' means it's added after the script handle it depends on
      );
  }

  /**
   * Show invoice button in the order details page
   *
   * @param $order
   */
  public function show_invoice_button( $order ) {
    if ( ! $order || ! is_user_logged_in() ) {
      return;
    }

    // Show the invoice permalink
    $permalink = $order->get_meta( '_quaderno_url' );
    if ( !empty($permalink) ) {
      echo sprintf('<p class="view_invoice"><strong><a href="%s" class="button" target="_blank">%s</a></strong></p>',
        esc_url( $permalink ),
        esc_html__( 'View Invoice', 'woocommerce-quaderno') );
    }
  }

  /**
   * Display the invoice permalink in the backend
   *
   * @param $order
   */
  public function display_field( $order ) {
    $permalink = $order->get_meta( '_quaderno_url' );
    echo '<p><a href="' . esc_url( $permalink ) . '" target="_blank">' . esc_html__( 'View Invoice', 'woocommerce-quaderno' ) . '</a></p>';
  }

  /**
   * Show tax ID in the order details page
   *
   * @param $order
   */
  public function show_tax_id( $address_type, $order ) {
    if ( ! $order || ! is_user_logged_in() ) {
        return;
    }

    // Show the customer's tax ID
    $tax_id = $order->get_meta( 'tax_id' );

    printf(
      '<p class="woocommerce-customer-details--tax-id">%s: %s</p>',
      esc_html__( 'Tax ID', 'woocommerce-quaderno' ),
      esc_html( $tax_id )
    );
  }

  /**
   * Display the customer's tax ID in the order email
   *
   * @param $order
   * @param $sent_to_admin
   * @param $plain_text
   */
  public function add_email_order_meta( $fields, $sent_to_admin, $order ) {
    $tax_id = $order->get_meta( 'tax_id' );

    if( empty( $tax_id ) ) {
      return $fields;
    }

    $fields[ 'tax_id' ] = array(
      'label' => esc_html__( 'Tax ID', 'woocommerce-quaderno' ),
      'value' => $tax_id
    );


    return $fields;
  }

}
