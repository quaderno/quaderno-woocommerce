<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class WC_QD_Invoice_Manager extends WC_QD_Transaction_Manager {

  public function setup() {
    add_action( 'woocommerce_payment_complete', array( $this, 'create_invoice' ), 10, 1 );
    add_action( 'woocommerce_order_status_completed', array( $this, 'create_invoice' ), 10, 1 );
  }

  /**
   * Create invoice
   *
   * @param $order_id
   */
  public function create_invoice( $order_id ) {
    // Get the order
    $order = wc_get_order( $order_id );

    // Return if an invoice has already been issued for this order or the order is free
    $invoice_id = get_post_meta( $order_id, '_quaderno_invoice', true );
    $skip = false; 
    if ( !empty( $invoice_id ) || $order->get_total() == 0 || apply_filters( 'quaderno_invoice_skip', $skip, $order_id ) ) {
      return;
    }

    // Get the PO number
    $po_number = get_post_meta( $order_id, '_order_number_formatted', true ) ?: $order_id;

    $transaction_params = array(
      'type' => 'sale',
      'date' => current_time('Y-m-d'),
      'currency' => $order->get_currency(),
      'po_number' => apply_filters( 'quaderno_invoice_po_number', $po_number, $order),
      'processor' => 'woocommerce',
      'processor_id' => strtotime($order->get_date_created()) . '_' . $order_id,
      'payment' => array(
        'method' => $this->get_payment_method($order),
        'processor' => $order->get_payment_method(),
        'processor_id' => $order->get_transaction_id()
      ), 
      'shipping_address' => array(
        'street_line_1' => $order->get_shipping_address_1(),
        'street_line_2' => $order->get_shipping_address_2(),
        'city' => $order->get_shipping_city(),
        'postal_code' => $order->get_shipping_postcode(),
        'region' => $order->get_shipping_state(),
        'country' => $order->get_shipping_country()
      ),
      'custom_metadata' => array( 'processor_url' => $order->get_edit_order_url() )
    );

    // Add the customer
    if ( !empty( $order->get_billing_company() ) ) {
      $kind = 'company';
      $first_name = $order->get_billing_company();
      $last_name = '';
      $contact_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
    } else {
      $kind = 'person';
      $first_name = empty( $order->get_billing_first_name() ) ? esc_html__('WooCommerce customer', 'woocommerce-quaderno' ) : $order->get_billing_first_name();
      $last_name = $order->get_billing_last_name();
      $contact_name = '';
    }

    $state = $order->get_billing_state();
    $country = $order->get_billing_country();
    $states = WC()->countries->get_states( $country );
    $full_state = ( !in_array( $country, array('US', 'CA') ) && isset( $states[ $state ] ) ) ? $states[ $state ] : $state;

    $tax_id = get_post_meta( $order_id, 'vat_number', true );
    if ( empty( $tax_id )) {
      $tax_id = get_post_meta( $order_id, 'tax_id', true );
    }

    $transaction_params['customer'] = array(
      'kind' => $kind,
      'first_name' => $first_name,
      'last_name' => $last_name,
      'contact_name' => $contact_name,
      'street_line_1' => $order->get_billing_address_1(),
      'street_line_2' => $order->get_billing_address_2(),
      'city' => $order->get_billing_city(),
      'postal_code' => $order->get_billing_postcode(),
      'region' => $full_state,
      'country' => $country,
      'email' => $order->get_billing_email(),
      'phone_1' => $order->get_billing_phone(),
      'tax_id' => $tax_id
    );

    if ( !empty( $order->get_user_id() ) ) {
      // If the customer is registered, we send their WooCommerce ID to Quaderno
      $transaction_params['customer']['processor'] = 'woocommerce';
      $transaction_params['customer']['processor_id'] = $order->get_user_id();

      // Some users use the same WooCommerce account to buy on behalf of different customers
      // Let's get the last order to see if customer's name has changed
      $args = array(
          'status' => 'completed',
          'type' => 'shop_order',
          'limit' => 1,
          'customer_id' => $order->get_user_id(),
          'exclude' => array( $order->get_id() )
      );
      $past_orders = wc_get_orders( $args );
      $last_order = reset( $past_orders );

      // If this is the customer's first order or their name hasn't changed, we reuse the contact ID
      // Otherwise, a new contact will be created in Quaderno 
      $contact_id = get_user_meta( $order->get_user_id(), '_quaderno_contact', true );
      if ( !empty( $contact_id ) && !empty( $last_order ) &&
           $last_order->get_billing_company() == $order->get_billing_company() &&
           $last_order->get_billing_first_name() == $order->get_billing_first_name() &&
           $last_order->get_billing_last_name() == $order->get_billing_last_name()
         ) 
      {
        $transaction_params['customer']['id'] = $contact_id;
        unset($transaction_params['customer']['first_name']);
        unset($transaction_params['customer']['last_name']);
      }
    }

    // Let's create the transaction
    $transaction = new QuadernoTransaction($transaction_params);

    // Calculate exchange rate
    $exchange_rate = get_post_meta( $order_id, '_woocs_order_rate', true ) ?: 1;

    // Calculate transaction items
    $tags = array();
    $transaction_items = array();
    $items = $order->get_items(array('line_item', 'shipping' ,'fee'));
    foreach ( $items as $item ) {
      $subtotal = $order->get_line_subtotal($item, true);
      $total = $order->get_line_total($item, true);
      $discount_rate = $subtotal == 0  ? 0 : round( ( $subtotal -  $total ) / $subtotal * 100, 2 );

      $new_item = array(
        'description' => $this->get_description( $item ),
        'quantity' => $item->get_quantity() ?: 1,
        'amount' => round( $total * $exchange_rate, wc_get_price_decimals() ),
        'discount_rate' => $discount_rate
      );

      // Add tax info for line items, fees, and taxable shipping costs
      if ( !$item->is_type('shipping') || $item->get_total_tax('edit') > 0 ) {
        $new_item['tax'] = $this->get_tax( $order, $item );
      }

      if ( $item->is_type('line_item') ) {
        // Get the product code and tags if exist
        $product = wc_get_product( $item->get_product_id() );
        if ( !empty( $product ) ) {
          $new_item['product_code'] = $product->get_sku();
          $tags = array_merge( $tags, wp_get_object_terms( $product->get_id(), 'product_tag', array( 'fields' => 'slugs' ) ) );
        }
      }

      array_push($transaction_items, $new_item );
    }

    // Add items to transaction
    $transaction->items = $transaction_items;

    // Add product tags to transaction
    if ( count( $tags ) > 0 ) {
      $transaction->tags = implode( ',', $tags );
    }

    // Add location evidence
    if ( true === $this->is_digital( $order ) ) {
      $transaction->evidence = array(
        'billing_country' => $order->get_billing_country(),
        'ip_address' => $order->get_customer_ip_address()
      );
    }

    // Add order notes
    if ( $this->is_reverse_charge( $order ) ) {
      $transaction->notes = esc_html__('Tax amount subject to reverse charge', 'woocommerce-quaderno' ) . '<br>';
    }
    $transaction->notes .= apply_filters( 'quaderno_invoice_notes', $order->get_customer_note(), $order );

    if ( $transaction->save() ) {
      add_post_meta( $order_id, '_quaderno_invoice', $transaction->id );
      add_post_meta( $order_id, '_quaderno_invoice_number', $transaction->number );
      add_post_meta( $order_id, '_quaderno_url', $transaction->permalink );
      add_post_meta( $order_id, '_quaderno_contact_id', $transaction->contact->id );
      update_user_meta( $order->get_user_id(), '_quaderno_contact', $transaction->contact->id );

      if ( 'yes' === WC_QD_Integration::$autosend_invoices ) $transaction->deliver();
    }
  }

}
