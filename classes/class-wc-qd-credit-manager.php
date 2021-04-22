<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class WC_QD_Credit_Manager extends WC_QD_Transaction_Manager {

	public function setup() {
		add_action( 'woocommerce_refund_created', array( $this, 'create_credit' ), 10, 2 );
	}

	/**
	 * Create credit
	 *
	 * @param $refund_id
	 */
	public function create_credit( $refund_id, $args ) {
		// Get the refund
		$refund = wc_get_order( $refund_id );
		$order = wc_get_order( $args['order_id'] );

		// Return if an credit has already been issued for this refund
		$credit_id = get_post_meta( $refund_id, '_quaderno_credit', true );
    $skip = false;
		if ( !empty( $credit_id ) || $order->get_total() == 0 || apply_filters( 'quaderno_credit_skip', $skip, $refund_id ) ) {
			return;
		}

    // Return if the refund does not have a related invoice in Quaderno
    $invoice_id = get_post_meta( $order->get_id(), '_quaderno_invoice', true );
    if ( empty( $invoice_id ) ) {
      return; 
    }

    // Get the contact ID
    $contact_id = get_post_meta( $order->get_id(), '_quaderno_contact_id', true );
    if( empty( $contact_id) ) {
      $invoice = QuadernoInvoice::find( $invoice_id ) ?: QuadernoReceipt::find( $invoice_id );
      $contact_id = $invoice->contact->id;
    } 

    // Get the PO number
    $po_number = get_post_meta( $order->get_id(), '_order_number_formatted', true ) ?: $order->get_id();

		$transaction_params = array(
			'type' => 'refund',
			'issue_date' => current_time('Y-m-d'),
			'customer' => array(
				'id' => $contact_id
			),
      'currency' => $order->get_currency(),
      'po_number' => apply_filters( 'quaderno_credit_po_number', $po_number, $order),
      'processor' => 'woocommerce',
      'processor_id' => strtotime($order->get_date_created()) . '_' . $args['order_id'],
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
		
		//Let's create the credit note
    $transaction = new QuadernoTransaction($transaction_params);

		// Calculate exchange rate
		$exchange_rate = get_post_meta( $order->get_id(), '_woocs_order_rate', true ) ?: 1;

    // Calculate transaction items and tags
    $tags = array();
    $transaction_items = array();
    $items = $refund->get_items(array('line_item', 'shipping' ,'fee'));
    foreach ( $items as $item ) {
      $new_item = array(
        'description' => $this->get_description( $item ),
        'quantity' => abs( $item->get_quantity() ?: 1 ),
        'amount' => abs(round( $refund->get_line_total($item, true) * $exchange_rate, wc_get_price_decimals() ))
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

    // Add order notes
    if ( $this->is_reverse_charge( $order ) ) {
      $transaction->notes = esc_html__('Tax amount subject to reverse charge', 'woocommerce-quaderno' ) . '<br>';
    }
    $transaction->notes .= apply_filters( 'quaderno_credit_notes', $order->get_customer_note(), $order );

		if ( $transaction->save() ) {
			add_post_meta( $refund_id, '_quaderno_credit', $transaction->id );
      add_post_meta( $refund_id, '_quaderno_credit_number', $transaction->number );
      add_post_meta( $refund_id, '_quaderno_url', $transaction->permalink );
      add_post_meta( $refund_id, '_quaderno_contact_id', $transaction->contact->id );

			if ( 'yes' === WC_QD_Integration::$autosend_invoices ) $transaction->deliver();
		}
	}

}
