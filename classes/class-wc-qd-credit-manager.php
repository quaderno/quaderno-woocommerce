<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class WC_QD_Credit_Manager {

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
		if ( !empty( $credit_id ) || $order->get_total() == 0 ) {
			return;
		}

		// Get the original invoice
		$invoice_id = get_post_meta( $order->get_id(), '_quaderno_invoice', true );
		if ( empty( $invoice_id ) ) {
			return; 
		}

		$invoice = QuadernoIncome::find( $invoice_id );

		$credit_params = array(
			'issue_date' => current_time('Y-m-d'),
			'contact_id' => $invoice->contact->id,
			'currency' => $refund->get_currency(),
			'po_number' => get_post_meta( $order->get_id(), '_order_number_formatted', true ) ?: $order->get_id(),
			'tag_list' => implode( ',', $invoice->tag_list ),
			'processor' => 'woocommerce',
			'processor_id' => $order->get_id(),
			'payment_method' => self::get_payment_method($order),
      'payment_processor' => $order->get_payment_method(),
      'payment_processor_id' => $order->get_transaction_id(),
			'document_id' => $invoice_id,
      'shipping_street_line_1' => $order->get_shipping_address_1(),
      'shipping_street_line_2' => $order->get_shipping_address_2(),
      'shipping_city' => $order->get_shipping_city(),
      'shipping_postal_code' => $order->get_shipping_postcode(),
      'shipping_region' => $order->get_shipping_state(),
      'shipping_country' => $order->get_shipping_country(),
			'custom_metadata' => array( 'processor_url' => $order->get_edit_order_url() )
		);
		
		//Let's create the credit note
		$credit = new QuadernoCredit($credit_params);

		// Calculate exchange rate
		$exchange_rate = get_post_meta( $order->get_id(), '_woocs_order_rate', true ) ?: 1;

		// Get the first invoice item to calculate taxes
		$item = $invoice->items[0];

		// Add item
		$refunded_amount = -round($refund->get_total() * $exchange_rate, 2);
		$new_item = new QuadernoDocumentItem(array(
			'product_code' => $item->product_code,
			'description' => 'Refund invoice #' . $invoice->number,
			'quantity' => 1,
			'total_amount' => $refunded_amount,
			'tax_1_name' => $item->tax_1_name,
			'tax_1_rate' => $item->tax_1_rate,
			'tax_1_country' => $item->tax_1_country,
			'tax_1_region' => $item->tax_1_region,
			'tax_1_county' => $item->tax_1_county,
			'tax_1_city' => $item->tax_1_city,
			'tax_1_county_code' => $item->tax_1_county_code,
			'tax_1_city_code' => $item->tax_1_city_code,
			'tax_1_transaction_type' => $item->tax_1_transaction_type
		));
		$credit->addItem( $new_item );

		if ( $credit->save() ) {
			add_post_meta( $refund_id, '_quaderno_credit', $credit->id );
			add_user_meta( $order->get_user_id(), '_quaderno_contact', $credit->contact_id, true );

			if ( 'yes' === WC_QD_Integration::$autosend_invoices ) $credit->deliver();
		}
	}

	/**
	 * Get payment method for Quaderno
	 *
	 * @param $order_id
	 */
	public function get_payment_method( $order ) {
		$payment_id = $order->get_payment_method();;
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
				$method = 'paypal';
				break;
			default:
				$method = 'credit_card';
		}
		return $method;
	}

}
