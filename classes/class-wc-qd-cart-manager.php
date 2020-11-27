<?php

if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}

class WC_QD_Cart_Manager {
	
	private $country;
	private $region;
	private $postal_code;
	private $city;
	private $tax_id;
	
	public function __construct($country, $region, $postal_code, $city, $tax_id) {
		$this->country = $country;
		$this->region = $region;
		$this->postal_code = $postal_code;
		$this->city = $city;
		$this->tax_id = $tax_id;
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
				$variation_types = array( 'variation', 'subscription_variation' );
				$id = ( in_array( $cart_item['data']->get_type(), $variation_types ) ? $cart_item['variation_id'] : $cart_item['product_id'] );

				// Get the transaction type
				$tax_class = WC_QD_Calculate_Tax::get_tax_class( $id );

				// Calculate taxes
				$tax = WC_QD_Calculate_Tax::calculate( $tax_class, $this->country, $this->region, $this->postal_code, $this->city );

				$items[ $cart_key ] = array(
					'id' => $id,
					'product_type' => $tax_class,
					'tax_name' => $tax->name,
					'tax_rate' => $tax->rate);
			}
		}

		return $items;
	}
	
}
