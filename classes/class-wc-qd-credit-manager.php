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
		$credit_id = get_post_meta( $refund->id, '_quaderno_credit', true );
		if ( !empty( $credit_id ) ) {
			return;
		}

		$credit_params = array(
			'issue_date' => date('Y-m-d'),
			'currency' => $refund->order_currency,
			'po_number' => get_post_meta( $order->id, '_order_number_formatted', true ) ?: $order->id,
			'processor' => 'woocommerce',
			'processor_id' => $order->id,
			'payment_method' => self::get_payment_method($order->id)
		);

		// Add the contact
		$contact_id = get_user_meta( $order->get_user_id(), '_quaderno_contact', true );
		if ( !empty( $contact_id ) ) {
			$credit_params['contact_id'] = $contact_id;
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

			$credit_params['contact'] = array(
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
  			'tax_id' => get_post_meta( $order->id, WC_QD_Tax_Id_Field::META_KEY, true )
			);
		}
		
		//Let's create the credit note
		$credit = new QuadernoCredit($credit_params);

		// Calculate exchange rate
		$exchange_rate = get_post_meta( $order->id, '_woocs_order_rate', true ) ?: 1;

		// Calculate taxes
		$taxes = $order->get_taxes();
		$tax = array_shift($taxes);
		if ( !isset( $tax ) ) {
			list($tax_name, $tax_rate) = array( NULL, 0 );
		} else if ( in_array( $tax['rate_id'], array('quaderno_eservice', 'quaderno_ebook') ) ) {
			list($tax_name, $tax_rate) = explode( '|', $tax['name'] );
		} else {
			list($tax_name, $tax_rate) = array( WC_Tax::get_rate_label( $tax['rate_id'] ), floatval( WC_Tax::get_rate_percent( $tax['rate_id'] )) );
		}

		// Add item
		$refunded_amount = -round($refund->get_total() * $exchange_rate, 2);
		$new_item = new QuadernoDocumentItem(array(
			'description' => 'Refund invoice #' . get_post_meta( $order->id, '_quaderno_invoice_number', true ),
			'quantity' => 1,
			'total_amount' => $refunded_amount,
			'tax_1_name' => $tax_name,
			'tax_1_rate' => $tax_rate,
			'tax_1_country' => $order->billing_country
		));
		$credit->addItem( $new_item );

		if ( $credit->save() ) {
			add_post_meta( $refund->id, '_quaderno_credit', $refund->id );
			add_user_meta( $order->get_user_id(), '_quaderno_contact', $refund->contact_id, true );

			if ( 'yes' === WC_QD_Integration::$autosend_invoices ) $credit->deliver();
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
