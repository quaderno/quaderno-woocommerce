<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class WC_QD_Subscription_Manager extends WC_QD_Transaction_Manager {
  /**
   * Setup
   *
   * @since 2.1
   */
  public function setup() {
    add_filter( 'wcs_new_order_created', array( $this, 'recalculate_taxes' ), 10, 4 );
  }

  /**
   * Recalculate taxes
   * This function recalculate taxes for all those subscriptions who have no taxes
   * We can use it to add taxes if the user adds a new jurisdiction or
   * the Tax API was down during the subscription creation
   *
   * @param $order
   */
  public function recalculate_taxes( $new_order, $subscription, $type ) {
    if ( 'yes' != WC_QD_Integration::$update_subscription_tax || $new_order->get_total_tax('edit') > 0 ) {
      return $new_order;
    }

    $items = $new_order->get_items(array('line_item', 'shipping' ,'fee'));

    foreach ( $items as $item_id => $item ) {
      $tax = $this->get_tax( $new_order, $item );

      $item->set_subtotal( $tax->subtotal );
      $item->set_total( $tax->total_amount );

      $item->calculate_taxes(); // Make new taxes calculations
      $item->save(); // Save line item data
    }

    $new_order->calculate_totals();

    return $new_order;
  }

}