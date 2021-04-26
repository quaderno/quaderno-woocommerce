<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class WC_QD_Transaction_Manager {

  public function is_digital( $order ) {
    $result = false;
    $items = $order->get_items();
    foreach ( $items as $item ) { 
      $product_id = $item->get_variation_id() ?: $item->get_product_id();
      $tax_class = WC_QD_Calculate_Tax::get_tax_class( $product_id );

      if ( true == in_array( $tax_class, array('eservice', 'ebook', 'saas') )) {
        $result = true;
      }
    }

    return apply_filters( 'quaderno_is_digital', $result, $order );
  }

  /**
   * Get the description of a particular order item
   *
   * @param $item
   */
  public function get_description( $item ) {
    if ( $item->is_type('line_item') ) {
      $variation_id = $item->get_variation_id();
      if( !empty($variation_id) ) {
        $description = $item->get_name() . " – " . wc_get_formatted_variation( wc_get_product($variation_id), true, true, true );
      }
      else {
        $description = $item->get_name();
      }
    } elseif ( $item->is_type('shipping') ) {
      $description = esc_html__('Shipping', 'woocommerce-quaderno' );
    } else {
      $description = $item->get_name();
    }

    return apply_filters( 'quaderno_item_description', $description, $item );
  }

  /**
   * Get payment method for a particular order
   *
   * @param $order
   */
  public function get_payment_method( $order ) {
    $payment_method = $order->get_payment_method();
    $method = '';
    switch( $payment_method ) {
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
      case 'braintree_paypal':
        $method = 'paypal';
        break;
      case 'stripe':
      case 'braintree_credit_card':
        $method = 'credit_card';
        break;
      default:
        $method = 'other';
    }

    return apply_filters( 'quaderno_payment_method', $method, $order );
  }

  /**
   * Get the tax of a particular order item
   *
   * @param $order
   * @param $item
   */
  public function get_tax( $order, $item ) {
    // Get tax class
    $tax_class = '';
    if ( $item->is_type('line_item') ) {
      $tax_class = WC_QD_Calculate_Tax::get_tax_class( $item->get_variation_id() ?: $item->get_product_id() );
    } elseif ( $item->is_type('shipping') ) {
      $shipping_tax_class = get_option( 'woocommerce_shipping_tax_class' );
      if ( 'inherit' !== $shipping_tax_class ) {
        $tax_class = $shipping_tax_class;
      }
    }

    // Get tax location
    $location = $this->get_tax_location( $order );

    // Calculate tax
    $tax = WC_QD_Calculate_Tax::calculate( $tax_class, $order->get_total(), get_woocommerce_currency(), $location['country'], $location['state'], $location['postcode'], $location['city'] );

    // Check if tax exempted
    if ( $this->is_reverse_charge( $order ) || $tax_class == 'exempted' ) {
      $tax->name = '';
      $tax->rate = 0;
    }

    return apply_filters( 'quaderno_item_tax', $tax, $item, $order );
  }

  /**
   * Get the tax location for a particular order
   *
   * @param $order
   */
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

    $tax_location = array(
      'country'  => $country,
      'state' => $state,
      'postcode' => $postcode,
      'city' => $city
    );

    return apply_filters( 'quaderno_tax_location', $tax_location, $order );
  }

  /**
   * Check if an order is tax reverse-charged or tax exempted
   *
   * @param $order
   */
  public function is_reverse_charge( $order ) {
    $order_id = $order->get_id();

    $is_vat_exempt = get_post_meta( $order_id, 'is_vat_exempt', true );

    # get the tax ID
    $tax_id = get_post_meta( $order_id, 'vat_number', true );
    if ( empty( $tax_id )) {
      $tax_id = get_post_meta( $order_id, 'tax_id', true );
    }

    // Check if the order is tax exempted
    return 'yes' === $is_vat_exempt || ( $is_vat_exempt == '' && true === WC_QD_Tax_Id_Field::is_valid( $tax_id, $location['country'] ) );
  }

}
