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
			'processor_id' => $order_id,
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
		$vat_number = get_post_meta( $order_id, WC_QD_Vat_Number_Field::META_KEY, true );
		$tax_id = get_post_meta( $order_id, WC_QD_Tax_Id_Field::META_KEY, true );

		// Add the reverse charged note
		if ( !empty($vat_number) ) {
			$invoice_params['notes'] = esc_html__('EU VAT reverse charged', 'woocommerce-quaderno' );
		}

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
				$first_name = empty( $order->get_billing_first_name() ) ? esc_html__('WooCommerce customer', 'woocommerce-quaderno' ) : $order->get_billing_first_name();
				$last_name = $order->get_billing_last_name();
				$contact_name = '';
			}

			$state = $order->get_billing_state();
			$country = $order->get_billing_country();
			$states = WC()->countries->get_states( $country );
			$full_state = ( !in_array( $country, array('US', 'CA') ) && isset( $states[ $state ] ) ) ? $states[ $state ] : $state;

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
				'vat_number' => $vat_number,
				'tax_id' => $tax_id
			);
		}

		// Let's create the invoice
		$invoice = new QuadernoIncome($invoice_params);

		// Let's create the tag list
		$tags = array();

		// Calculate exchange rate
		$exchange_rate = get_post_meta( $order_id, '_woocs_order_rate', true ) ?: 1;

		// Get tax location
		$location = $this->get_tax_location($order);

		// Add line items
		$digital_products = false;
		$items = $order->get_items();
		foreach ( $items as $item ) {
			$tax_class = WC_QD_Calculate_Tax::get_tax_class( $item->get_product_id() );
			$tax = WC_QD_Calculate_Tax::calculate( $tax_class, $location['country'], $location['state'], $location['postcode'], $location['city'] );

			// Reverse charge
			if ( true === WC_QD_Vat_Number_Field::is_valid( $vat_number, $country ) ) {
				$tax->name = '';
				$tax->rate = 0;
			}

			if ( true == in_array( $tax_class, array('eservice', 'ebook') )) {
				$digital_products = true;
			}

			$subtotal = $order->get_line_subtotal($item, true);
			$total = $order->get_line_total($item, true);
			$discount_rate = $subtotal == 0  ? 0 : round( ( $subtotal -  $total ) / $subtotal * 100, 0 );

			$new_item = new QuadernoDocumentItem(array(
				'description' => $item->get_name(),
				'quantity' => $item->get_quantity(),
				'total_amount' => round( $total * $exchange_rate, wc_get_price_decimals() ),
				'discount_rate' => $discount_rate,
				'tax_1_name' => $tax->name,
				'tax_1_rate' => $tax->rate,
				'tax_1_country' => $tax->country,
				'tax_1_region' => $tax->region,
				'tax_1_county' => $tax->county,
				'tax_1_city' => $tax->city,
				'tax_1_county_code' => $tax->county_tax_code,
				'tax_1_city_code' => $tax->city_tax_code,
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
			$tax = WC_QD_Calculate_Tax::calculate( '', $location['country'], $location['state'], $location['postcode'], $location['city'] );

			// Reverse charge
			if ( true === WC_QD_Vat_Number_Field::is_valid( $vat_number, $country ) ) {
				$tax->name = '';
				$tax->rate = 0;
			}

			$shipping_tax = $order->get_shipping_tax();
			$shipping_total += $shipping_tax;

			$new_item = new QuadernoDocumentItem(array(
				'description' => esc_html__('Shipping', 'woocommerce-quaderno' ),
				'quantity' => 1,
				'total_amount' => round( $shipping_total * $exchange_rate, 2),
				'tax_1_name' => $tax->name,
				'tax_1_rate' => $tax->rate,
				'tax_1_country' => $tax->country,
				'tax_1_region' => $tax->region,
				'tax_1_county' => $tax->county,
				'tax_1_city' => $tax->city,
				'tax_1_county_code' => $tax->county_tax_code,
				'tax_1_city_code' => $tax->city_tax_code,
				'tax_1_transaction_type' => $tax->transaction_type
			));
			$invoice->addItem( $new_item );
		}

		// Add fee items
		$items = $order->get_items('fee');
		foreach ( $items as $fee ) {
			$tax = WC_QD_Calculate_Tax::calculate( '', $location['country'], $location['state'], $location['postcode'], $location['city'] );

			// Reverse charge
			if ( true === WC_QD_Vat_Number_Field::is_valid( $vat_number, $country ) ) {
				$tax->name = '';
				$tax->rate = 0;
			}

			$fee_total = $fee['total'] + $fee['total_tax'];

			$new_item = new QuadernoDocumentItem(array(
				'description' => esc_html__('Fee', 'woocommerce-quaderno' ),
				'quantity' => 1,
				'total_amount' => round( $fee_total * $exchange_rate, 2),
				'tax_1_name' => $tax->name,
				'tax_1_rate' => $tax->rate,
				'tax_1_country' => $tax->country,
				'tax_1_region' => $tax->region,
				'tax_1_county' => $tax->county,
				'tax_1_city' => $tax->city,
				'tax_1_county_code' => $tax->county_tax_code,
				'tax_1_city_code' => $tax->city_tax_code,
				'tax_1_transaction_type' => $tax->transaction_type
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

}
