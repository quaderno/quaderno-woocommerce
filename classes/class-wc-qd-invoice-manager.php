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
			'issue_date' => date('Y-m-d'),
			'currency' => $order->get_currency(),
			'po_number' => get_post_meta( $order_id, '_order_number_formatted', true ) ?: $order_id,
			'notes' => $order->get_customer_note(),
			'processor' => 'woocommerce',
			'processor_id' => $order->get_transaction_id() ?: $order_id,
			'payment_method' => self::get_payment_method($order_id)
		);

		// Add the contact
		$vat_number = get_post_meta( $order_id, WC_QD_Vat_Number_Field::META_KEY, true );
		$tax_id = get_post_meta( $order_id, WC_QD_Tax_Id_Field::META_KEY, true );

		$contact_id = get_user_meta( $order->get_user_id(), '_quaderno_contact', true );
		if ( !empty( $contact_id ) ) {
			$invoice_params['contact_id'] = $contact_id;
		}
		else {
			if ( !empty( $order->get_billing_company() ) ) {
				$kind = 'company';
				$first_name = $order->get_billing_company();
				$last_name = '';
				$contact_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
			} else {
				$kind = 'person';
				$first_name = $order->get_billing_first_name();
				$last_name = $order->get_billing_last_name();
				$contact_name = '';
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
				'region' => $order->get_billing_state(),
				'country' => $order->get_billing_country(),
				'email' => $order->get_billing_email(),
				'phone_1' => $order->get_billing_phone(),
				'vat_number' => $vat_number,
				'tax_id' => $tax_id
			);
		}

		// Let's create the invoice
		$invoice = new QuadernoIncome($invoice_params);

		// Calculate exchange rate
		$exchange_rate = get_post_meta( $order_id, '_woocs_order_rate', true ) ?: 1;

		// Get tax location
		$location = $this->get_tax_location($order);

		// Add line items
		$digital_products = false;
		$items = $order->get_items();
		foreach ( $items as $item ) {
			$line_tax_data = maybe_unserialize( $item['line_tax_data'] );
			$rate_id = key( $line_tax_data['total'] );
			if ( true == in_array( $rate_id, array('quaderno_eservice', 'quaderno_ebook') )) {
				$digital_products = true;
			}
			$tax = self::get_tax( $rate_id, $location['country'], $location['postcode'], $vat_number );

			$subtotal = $order->get_line_subtotal($item, true);
			$total = $order->get_line_total($item, true);
			$discount_rate = round( ( $subtotal -  $total ) / $subtotal * 100, 0 );

			$new_item = new QuadernoDocumentItem(array(
				'description' => $item['name'],
				'quantity' => $item['qty'],
				'total_amount' => round( $total * $exchange_rate, wc_get_price_decimals() ),
				'discount_rate' => $discount_rate,
				'tax_1_name' => $tax['name'],
				'tax_1_rate' => $tax['rate'],
				'tax_1_country' => $tax['country']
			));
			$invoice->addItem( $new_item );
		}

		// Add shipping items
		$items = $order->get_items('shipping');
		foreach ( $items as $shipping ) {
			$shipping_tax_data = maybe_unserialize( $shipping['taxes'] );
			$rate_id = key( reset( $shipping_tax_data ));

			$tax = self::get_tax( $rate_id, $location['country'], $location['postcode'], $vat_number );
			$shipping_total = $shipping['total'] + $shipping['total_tax'];

			$new_item = new QuadernoDocumentItem(array(
				'description' => esc_html__('Shipping', 'woocommerce-quaderno' ),
				'quantity' => 1,
				'total_amount' => round( $shipping_total * $exchange_rate, 2),
				'tax_1_name' => $tax['name'],
				'tax_1_rate' => $tax['rate'],
				'tax_1_country' => $tax['country']
			));
			$invoice->addItem( $new_item );
		}

		// Add fees
		$items = $order->get_items('fee');
		foreach ( $items as $fee ) {
			$fee_tax_data = maybe_unserialize( $fee['line_tax_data'] );
			$rate_id = key( reset( $fee_tax_data ));

			$tax = self::get_tax( $rate_id, $location['country'], $location['postcode'], $vat_number );
			$fee_total = $fee['total'] + $fee['total_tax'];

			$new_item = new QuadernoDocumentItem(array(
				'description' => esc_html__('Fee', 'woocommerce-quaderno' ),
				'quantity' => 1,
				'total_amount' => round( $fee_total * $exchange_rate, 2),
				'tax_1_name' => $tax['name'],
				'tax_1_rate' => $tax['rate'],
				'tax_1_country' => $tax['country']
			));
			$invoice->addItem( $new_item );
		}

		if ( $invoice->save() ) {
			add_post_meta( $order_id, '_quaderno_invoice', $invoice->id );
			add_post_meta( $order_id, '_quaderno_invoice_number', $invoice->number );
		  add_post_meta( $order_id, '_quaderno_url', $invoice->permalink );

			add_user_meta( $order->get_user_id(), '_quaderno_contact', $invoice->contact->id, true );
			update_user_meta( $order->get_user_id(), '_quaderno_tax_id', $tax_id );
			update_user_meta( $order->get_user_id(), '_quaderno_vat_number', $vat_number );

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
			case 'stripe':
				$method = 'credit_card';
				break;
			default:
				$method = 'other';
		}
		return $method;
	}
	
	public function get_tax( $rate_id, $country = '', $postcode = '', $vat_number = '' ) {
		if ( !isset( $rate_id ) ) {
			list($tax_name, $tax_rate) = array( NULL, 0 );
		} else {
			$tax = WC_QD_Calculate_Tax::calculate( str_replace( 'quaderno_', '', $rate_id ), $country, $postcode, $vat_number );
			list($tax_name, $tax_rate) = array( $tax->name, $tax->rate );
		} 
		
		return array( 'name' => $tax_name, 'rate' => $tax_rate, 'country' => $country );
	}

	public function get_tax_location( $order ) {
		$tax_based_on = get_option( 'woocommerce_tax_based_on' );

		if ( 'shipping' === $tax_based_on && ! $order->get_shipping_country() ) {
			$tax_based_on = 'billing';
		}

		if ( 'base' === $tax_based_on ) {
			$country  = WC()->countries->get_base_country();
			$postcode = WC()->countries->get_base_postcode();
		} elseif ( 'billing' === $tax_based_on ) {
			$country  = $order->get_billing_country();
			$postcode = $order->get_billing_postcode();
		} else {
			$country  = $order->get_shipping_country();
			$postcode = $order->get_shipping_postcode();
		}

		$result = array(
			'country'  => $country,
			'postcode' => $postcode
		);

		return $result;
	}

}
