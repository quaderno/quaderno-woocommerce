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

      if ( true == in_array( $tax_class, WC_QD_Tax_Code_Field::DIGITAL_TAX_CODES )) {
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
    $order = $item->get_order();
    $is_subscription_item = false;
    $billing_period_start = '';
    $billing_period_end = '';

    // Check if the item belongs to a subscription
    if ( function_exists( 'wcs_get_subscriptions_for_order' ) ) {
      $subscription_ids = wcs_get_subscriptions_for_order( $order->get_id(), array( 'order_type' => 'any' ) );
      foreach ( $subscription_ids as $subscription_id => $subscription ) {
        if ( $subscription->has_product( $item->get_product_id() ) ) {
          $is_subscription_item = true;
          
          // Get the start and end of the billing period for the subscription
          $billing_period_start = $order->get_date_created();
          $billing_period_end = $subscription->get_date('next_payment');
          break;
        }
      }
    }

    // Get the item description
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

    // Append subscription info to the description if it's part of a subscription
    if ( $is_subscription_item && !empty($billing_period_start) && !empty($billing_period_end) ) {
      $description .= sprintf(' (%s — %s)', 
        date_i18n(get_option('date_format'), strtotime($billing_period_start)), 
        date_i18n(get_option('date_format'), strtotime($billing_period_end))
      );
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
      case 'bizum':
      case 'bizumredsys':
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
      case 'ppcp-gateway':
      case 'braintree_paypal':
        $method = 'paypal';
        break;
      case 'stripe':
      case 'cpsw_stripe':
      case 'stripe_cc':
      case 'braintree_credit_card':
      case 'square_credit_card':
      case 'redsys':
      case 'myredsys':
      case 'redsys_gw':
      case 'fkwcs_stripe':
        $method = 'credit_card';
        break;
      case 'gocardless':
      case 'stripe_sepa':
      case 'mollie_wc_gateway_directdebit':
        $method = 'direct_debit';
        break;
      default:
        $method = 'other';
    }

    return apply_filters( 'quaderno_payment_method', $method, $order );
  }

  /**
   * Get payment processor for a particular order
   *
   * @param $order
   */
  public function get_payment_processor( $order ) {
    $payment_method = $order->get_payment_method();
    $method = '';
    switch( $payment_method ) {
      case 'paypal':
      case 'ppec_paypal':
      case 'ppcp-gateway':
        $method = 'paypal';
        break;
      case 'stripe':
      case 'cpsw_stripe':
      case 'stripe_cc':
      case 'fkwcs_stripe':
      case 'stripe_sepa':
        $method = 'stripe';
        break;
      case 'braintree_paypal':
      case 'braintree_credit_card':
        $method = 'braintree';
        break;
      case 'square_credit_card':
        $method = 'square';
        break;
      case 'gocardless':
        $method = 'gocardless';
        break;
      default:
        $method = $payment_method;
    }

    return apply_filters( 'quaderno_payment_processor', $method, $order );
  }

  /**
   * Get the tax of a particular order item
   *
   * @param $order
   * @param $item
   */
  public function get_tax( $order, $item ) {
    // Get the tax class and the product type
    $tax_class = get_transient( 'last_tax_class' );
    if ( empty( $tax_class ) ) {
      $tax_class = '';
    }
    $product_type = 'good';

    if ( $item->is_type('line_item') ) {
      $product_id = $item->get_variation_id() ?: $item->get_product_id();

      $tax_class = WC_QD_Calculate_Tax::get_tax_class( $product_id );
      $product_type = WC_QD_Calculate_Tax::get_product_type( $product_id );

      # cache tax class
      set_transient( 'last_tax_class', $tax_class );
    } elseif ( $item->is_type('shipping') ) {
      $shipping_tax_class = get_option( 'woocommerce_shipping_tax_class' );
      if ( 'inherit' !== $shipping_tax_class ) {
        $tax_class = $shipping_tax_class;
      }
    }

    // Get tax location
    $location = $this->get_tax_location( $order );

    // Calculate tax
    $tax = WC_QD_Calculate_Tax::calculate( $tax_class, $product_type, $order->get_total(), get_woocommerce_currency(), $location['country'], $location['state'], $location['postcode'], $location['city'], $location['street'] );

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
      $street = WC()->countries->get_base_address();
    } elseif ( 'billing' === $tax_based_on ) {
      $country  = $order->get_billing_country();
      $state = $order->get_billing_state();
      $postcode = $order->get_billing_postcode();
      $city = $order->get_billing_city();
      $street = $order->get_billing_address_1();
    } else {
      $country  = $order->get_shipping_country();
      $state  = $order->get_shipping_state();
      $postcode = $order->get_shipping_postcode();
      $city = $order->get_shipping_city();
      $street = $order->get_shipping_address_1();
    }

    $tax_location = array(
      'country'  => $country,
      'state' => $state,
      'postcode' => $postcode,
      'city' => $city, 
      'street' => $street
    );

    return apply_filters( 'quaderno_tax_location', $tax_location, $order );
  }

  /**
   * Check if an order is tax reverse-charged
   *
   * @param $order
   */
  public function is_reverse_charge( $order ) {
    return 'yes' === $order->get_meta( 'is_vat_exempt' );
  }

}
