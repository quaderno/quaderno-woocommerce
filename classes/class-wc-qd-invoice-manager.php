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

		// Return if an invoice has already been issued for this order
		$invoice_id = get_post_meta( $order->id, '_quaderno_invoice', true );
		if ( !empty( $invoice_id ) ) {
			return;
		}

		$invoice_params = array(
			'issue_date' => date('Y-m-d'),
			'currency' => $order->order_currency,
			'po_number' => get_post_meta( $order->id, '_order_number_formatted', true ) ?: $order->id,
			'notes' => $order->order_comments,
			'processor' => 'woocommerce',
			'processor_id' => $order->id,
			'payment_method' => self::get_payment_method($order->id)
		);

		// Add the contact
		$contact_id = get_user_meta( $order->get_user_id(), '_quaderno_contact', true );
		if ( !empty( $contact_id ) ) {
			$invoice_params['contact_id'] = $contact_id;
		}
		else {
			if ( !empty( $order->billing_company ) ) {
				$kind = 'company';
				$first_name = $order->billing_company;
				$last_name = '';
				$contact_name = $order->billing_first_name . ' ' . $order->billing_last_name;
			} else {
				$kind = 'person';
				$first_name = $order->billing_first_name;
				$last_name = $order->billing_last_name;
				$contact_name = '';
			}

			$invoice_params['contact'] = array(
				'kind' => $kind,
				'first_name' => $first_name,
				'last_name' => $last_name,
				'contact_name' => $contact_name,
				'street_line_1' => $order->billing_address_1,
				'street_line_2' => $order->billing_address_2,
				'city' => $order->billing_city,
				'postal_code' => $order->billing_postcode,
				'region' => $order->billing_state,
				'country' => $order->billing_country,
				'email' => $order->billing_email,
				'vat_number' => get_post_meta( $order->id, WC_QD_Vat_Number_Field::META_KEY, true ),
				'processor' => 'woocommerce',
				'processor_id' => $order->get_user_id()
			);
		}

		// Let's create the receipt or the invoice
		if ( $order->get_total() < intval( WC_QD_Integration::$receipts_threshold )) {
		  $invoice = new QuadernoReceipt($invoice_params);
		} else {
		  $invoice = new QuadernoInvoice($invoice_params);
		}

		// Calculate exchange rate
		$exchange_rate = get_post_meta( $order->id, '_woocs_order_rate', true ) ?: 1;

		// Calculate taxes
		$taxes = $order->get_taxes();
		$tax = array_shift($taxes);
		if ( isset( $tax ) ) {
			list($tax_name, $tax_rate) = explode( '|', $tax['name'] );
		} else {
			list($tax_name, $tax_rate) = array( NULL, 0 );
		}

		// Add items
		$items = $order->get_items();
		foreach ( $items as $item ) {
			$new_item = new QuadernoDocumentItem(array(
				'description' => $item['name'],
				'quantity' => $order->get_item_count($item),
				'total_amount' => round($order->get_line_total($item, true) * $exchange_rate, 2),
				'tax_1_name' => $tax_name,
				'tax_1_rate' => $tax_rate,
				'tax_1_country' => $order->billing_country
			));
			$invoice->addItem( $new_item );
		}

		if ( $invoice->save() ) {
			add_post_meta( $order->id, '_quaderno_invoice', $invoice->id );
			add_post_meta( $order->id, '_quaderno_invoice_number', $invoice->number );
			add_user_meta( $order->get_user_id(), '_quaderno_contact', $invoice->contact_id, true );

			if ( true === in_array( $tax['rate_id'], array('quaderno_eservice', 'quaderno_ebook') ) ) {
				$evidence = new QuadernoEvidence(array(
					'document_id' => $invoice->id,
					'billing_country' => $order->billing_country,
					'ip_address' => $order->customer_ip_address
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
	public function get_payment_method( $order_id ) {
		$payment_id = get_post_meta( $order_id, '_payment_method', true );
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
