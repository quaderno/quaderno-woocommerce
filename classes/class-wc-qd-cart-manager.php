<?php

if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}

class WC_QD_Cart_Manager {
	
	private $country;
	private $vat_number;
	
	public function __construct($country, $postal_code, $vat_number) {
		$this->country = $country;
		$this->postal_code = $postal_code;
		$this->vat_number = $vat_number;
	}

	/**
	 * Get the formatted items in current cart ready for transaction lines
	 *
	 * @return array
	 */
	public function get_items_from_cart() {

		// The items
		$items = array();

		// Pre Calculate totals
		WC()->cart->calculate_totals();

		// Loop through cart items
		$cart = WC()->cart->get_cart();

		if ( count( $cart ) > 0 ) {
			foreach ( $cart as $cart_key => $cart_item ) {
				$id = ( ( 'variation' === $cart_item['data']->get_type() ) ? $cart_item['variation_id'] : $cart_item['product_id'] );

				// Get the transaction type
				$tax_class = WC_QD_Calculate_Tax::get_tax_class( $id );

				// Calculate taxes
				$tax = WC_QD_Calculate_Tax::calculate( $tax_class, $this->country, $this->postal_code, $this->vat_number );

				$items[ $cart_key ] = array(
					'id' => $id,
					'product_type' => $tax_class,
					'tax_name' => $tax->name,
					'tax_rate' => $tax->rate
				);
				
			}
		}

		return $items;
	}
	
}