<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class WC_QD_Invoice_Manager {

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
    if ( !empty( $invoice_id ) || $order->get_total() == 0 ) {
      return;
    }

    $invoice_params = array(
      'issue_date' => current_time('Y-m-d'),
      'currency' => $order->get_currency(),
      'po_number' => get_post_meta( $order_id, '_order_number_formatted', true ) ?: $order_id,
      'processor' => 'woocommerce',
      'processor_id' => strtotime($order->get_date_created()) . '_' . $order_id,
      'payment_method' => $this->get_payment_method($order),
      'payment_processor' => $order->get_payment_method(),
      'payment_processor_id' => $order->get_transaction_id(),
      'shipping_street_line_1' => $order->get_shipping_address_1(),
      'shipping_street_line_2' => $order->get_shipping_address_2(),
      'shipping_city' => $order->get_shipping_city(),
      'shipping_postal_code' => $order->get_shipping_postcode(),
      'shipping_region' => $order->get_shipping_state(),
      'shipping_country' => $order->get_shipping_country(),
      'custom_metadata' => array( 'processor_url' => $order->get_edit_order_url() )
    );

    // Add the contact
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

    $invoice_params['contact'] = array(
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
      'tax_id' => $tax_id,
      'processor' => 'woocommerce',
      'processor_id' => $order->get_user_id()
    );

    $contact_id = get_user_meta( $order->get_user_id(), '_quaderno_contact', true );

    // Get last order to see if name has changed
    $args = array(
        'status' => 'completed',
        'type' => 'shop_order',
        'limit' => 1,
        'customer_id' => $order->get_user_id(),
        'exclude' => array( $order->get_id() )
    );
    $past_orders = wc_get_orders( $args );
    $last_order = reset( $past_orders );

    // New contact if name has changed
    if ( !empty( $contact_id ) && !empty( $last_order ) &&
         $last_order->get_billing_company() == $order->get_billing_company() &&
         $last_order->get_billing_first_name() == $order->get_billing_first_name() &&
         $last_order->get_billing_last_name() == $order->get_billing_last_name()
       ) 
    {
      $invoice_params['contact']['id'] = $contact_id;
      unset($invoice_params['contact']['first_name']);
      unset($invoice_params['contact']['last_name']);
    }

    // Add order notes
    if ( $this->is_reverse_charge($order) ) {
      $invoice_params['notes'] = esc_html__('Tax amount subject to reverse charge', 'woocommerce-quaderno' );
    } else {
      $invoice_params['notes'] = $order->get_customer_note();
    }

    // Let's create the invoice
    $invoice = new QuadernoIncome($invoice_params);

    // Let's create the tag list
    $tags = array();

    // Calculate exchange rate
    $exchange_rate = get_post_meta( $order_id, '_woocs_order_rate', true ) ?: 1;

    // Add line items
    $digital_products = false;
    $items = $order->get_items();
    foreach ( $items as $item ) {
      $tax_class = WC_QD_Calculate_Tax::get_tax_class( $item->get_product_id() );
      $tax = $this->get_tax( $order, $tax_class );

      if ( true == in_array( $tax_class, array('eservice', 'ebook') )) {
        $digital_products = true;
      }

      $subtotal = $order->get_line_subtotal($item, true);
      $total = $order->get_line_total($item, true);
      $discount_rate = $subtotal == 0  ? 0 : round( ( $subtotal -  $total ) / $subtotal * 100, 2 );

      $new_item = new QuadernoDocumentItem(array(
        'description' => $item->get_name(),
        'quantity' => $item->get_quantity(),
        'total_amount' => round( $total * $exchange_rate, wc_get_price_decimals() ),
        'discount_rate' => $discount_rate,
        'tax_1_name' => $tax->name,
        'tax_1_rate' => $tax->rate,
        'tax_1_country' => $tax->country,
        'tax_1_region' => $tax->region,
        'tax_1_transaction_type' => $tax->transaction_type
      ));

      // Store the product code
      $product = wc_get_product( $item->get_product_id() );
      if ( !empty( $product ) ) {
        $new_item->product_code = $product->get_sku();
        $tags = array_merge( $tags, wp_get_object_terms( $product->get_id(), 'product_tag', array( 'fields' => 'slugs' ) ) );
      }

      $invoice->addItem( $new_item );
    }

    // Add product tags to invoice
    if ( count( $tags ) > 0 ) {
      $invoice->tag_list = implode( ',', $tags );
    }

    // Add shipping items
    $shipping_total = $order->get_shipping_total();
    if ( $shipping_total > 0 ) {
      // See if we have an explicitly set shipping tax class.
      $tax_class = null;
      $shipping_tax_class = get_option( 'woocommerce_shipping_tax_class' );
      if ( 'inherit' !== $shipping_tax_class ) {
          $tax_class = $shipping_tax_class;
      }

      $tax = $this->get_tax( $order, $tax_class );

      $shipping_tax = $order->get_shipping_tax();
      $shipping_total += $shipping_tax;

      $new_item = new QuadernoDocumentItem(array(
        'description' => esc_html__('Shipping', 'woocommerce-quaderno' ),
        'quantity' => 1,
        'total_amount' => round( $shipping_total * $exchange_rate, 2)
      ));

      if ( $shipping_tax > 0 ) {
        $new_item->tax_1_name = $tax->name;
        $new_item->tax_1_rate = $tax->rate;
        $new_item->tax_1_country = $tax->country;
        $new_item->tax_1_region = $tax->region;
        $new_item->tax_1_transaction_type = $tax->transaction_type;
      }

      $invoice->addItem( $new_item );
    }

    // Add fee items
    $items = $order->get_items('fee');
    foreach ( $items as $fee ) {
      $tax = $this->get_tax( $order, '' );
      $fee_total = $fee['total'] + $fee['total_tax'];

      $new_item = new QuadernoDocumentItem(array(
        'description' => $fee->get_name(),
        'quantity' => 1,
        'total_amount' => round( $fee_total * $exchange_rate, 2),
        'tax_1_name' => $tax->name,
        'tax_1_rate' => $tax->rate,
        'tax_1_country' => $tax->country,
        'tax_1_region' => $tax->region,
        'tax_1_transaction_type' => $tax->transaction_type
      ));
      $invoice->addItem( $new_item );
    }

    if ( $invoice->save() ) {
      add_post_meta( $order_id, '_quaderno_invoice', $invoice->id );
      add_post_meta( $order_id, '_quaderno_invoice_number', $invoice->number );
      add_post_meta( $order_id, '_quaderno_url', $invoice->permalink );
      update_user_meta( $order->get_user_id(), '_quaderno_contact', $invoice->contact->id );

      if ( true === $digital_products ) {
        $evidence = new QuadernoEvidence(array(
          'document_id' => $invoice->id,
          'billing_country' => $order->get_billing_country(),
          'ip_address' => $order->get_customer_ip_address()
        ));
        $evidence->save();
      }

      if ( 'yes' === WC_QD_Integration::$autosend_invoices ) $invoice->deliver();
    }
  }

  /**
   * Get payment method for Quaderno
   *
   * @param $order_id
   */
  public function get_payment_method( $order ) {
    $payment_id = $order->get_payment_method();
    $method = '';
    switch( $payment_id ) {
      case 'bacs':
        $method = 'wire_transfer';
        break;
      case 'cheque':
        $method = 'check';
        break;
      case 'cod':
        $method = 'cash';
        break;
      case 'paypal':
      case 'ppec_paypal':
        $method = 'paypal';
        break;
      case 'stripe':
        $method = 'credit_card';
        break;
      default:
        $method = 'other';
    }
    return $method;
  }
  
  public function get_tax( $order, $tax_class ) {
    // Get tax location
    $location = $this->get_tax_location( $order );

    $tax = WC_QD_Calculate_Tax::calculate( $tax_class, $location['country'], $location['state'], $location['postcode'], $location['city'] );

    // Tax exempted
    if ( $this->is_reverse_charge($order) ) {
      $tax->name = '';
      $tax->rate = 0;
    }

    return $tax;
  }

  public function get_tax_location( $order ) {
    $tax_based_on = get_option( 'woocommerce_tax_based_on' );

    if ( 'shipping' === $tax_based_on && ! $order->get_shipping_country() ) {
      $tax_based_on = 'billing';
    }

    if ( 'base' === $tax_based_on ) {
      $country  = WC()->countries->get_base_country();
      $state  = WC()->countries->get_base_state();
      $postcode = WC()->countries->get_base_postcode();
      $city = WC()->countries->get_base_city();
    } elseif ( 'billing' === $tax_based_on ) {
      $country  = $order->get_billing_country();
      $state = $order->get_billing_state();
      $postcode = $order->get_billing_postcode();
      $city = $order->get_billing_city();
    } else {
      $country  = $order->get_shipping_country();
      $state  = $order->get_shipping_state();
      $postcode = $order->get_shipping_postcode();
      $postcode = $order->get_shipping_postcode();
      $city = $order->get_shipping_city();
    }

    $result = array(
      'country'  => $country,
      'state' => $state,
      'postcode' => $postcode,
      'city' => $city
    );

    return $result;
  }

  public function is_reverse_charge($order) {
    $order_id = $order->get_id();

    $is_vat_exempt = get_post_meta( $order_id, 'is_vat_exempt', true );

    # get the tax ID
    $tax_id = get_post_meta( $order_id, 'vat_number', true );
    if ( empty( $tax_id )) {
      $tax_id = get_post_meta( $order_id, 'tax_id', true );
    }

    // Tax exempted
    return 'yes' === $is_vat_exempt || ( $is_vat_exempt == '' && true === WC_QD_Tax_Id_Field::is_valid( $tax_id, $location['country'] ) );
  }


}
