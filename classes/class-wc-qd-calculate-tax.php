<?php

if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}

class WC_QD_Calculate_Tax {

	/**
	 * Get the transaction type
	 *
	 * @param int $product_id
	 *
	 * @return String
	 */
	public static function get_tax_class( $product_id ) {
		$type = 'standard';
		$product_types = array( 'product', 'product_variation' );

		if ( in_array( get_post_type( $product_id ), $product_types ) ) {
			$product = wc_get_product( $product_id );
			$type = $product->get_tax_class();

			// Check if this is a virtual product
			if ( $product->is_virtual() ) {
				$type = 'eservice';
			}

			// Check if this is an e-book
			$is_ebook = get_post_meta( $product_id, '_ebook', true );
			if ( 'yes' === $is_ebook ) {
				$type = 'ebook';
			}
		}

		return $type;
	}
	
	/**
	 * Get the product type
	 *
	 * @param String $transaction_type
	 * @param String $country
	 * @param String $postal_code
	 * @param String $vat_number
	 *
	 * @return Tax
	 */
	public static function calculate( $tax_class, $country, $postal_code = '', $vat_number = '' ) {
		switch ( $tax_class ) {
			case 'eservice':
			case 'ebook':
				$transaction_type = $tax_class;
				break;
			default:
				$transaction_type = 'standard';
				break;
		}

		$params = array(
			'country' => $country,
			'postal_code' => urlencode($postal_code),
			'postcode' => urlencode($postal_code),
			'vat_number' => urlencode($vat_number),
			'transaction_type' => urlencode($transaction_type),
			'tax_class' => urlencode($tax_class)
		);

		$slug = 'tax_' . md5( implode( $params ) );

		// Calculate taxes if they're not cached
		if ( false === ( $tax = get_transient( $slug ) ) ) {
			$wc_tax = new WC_Tax();
			$wc_rates = $wc_tax->find_rates( $params );
			$wc_rate = reset( $wc_rates );

			if ( $wc_rate ) {
				$tax = new stdClass();
				$tax->name = $wc_rate['label'];
				$tax->rate = $wc_rate['rate'];
				$tax->country = $country;
			}
			else {
				$tax = QuadernoTax::calculate( $params );				
			}

			set_transient( $slug, $tax, WEEK_IN_SECONDS );
		}

		if( is_null($tax->name) ) $tax->name = __( 'Taxes', 'woocommerce-quaderno' );

		return $tax;
	}

}
