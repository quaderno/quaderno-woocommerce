<?php

if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}

class WC_QD_Calculate_Tax {

	/**
	 * Get the tax class
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
	 * @param String $tax_class
	 * @param String $country
	 * @param String $region
	 * @param String $postal_code
	 * @param String $city
	 *
	 * @return Tax
	 */
	public static function calculate( $tax_class, $country, $region = '', $postal_code = '', $city = '' ) {
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
			'region' => urlencode($region),
			'postal_code' => urlencode($postal_code),
			'city' => urlencode($city),
			'transaction_type' => urlencode($transaction_type),
			'tax_class' => $tax_class
		);

		$slug = 'quaderno_tax_' . md5( implode( $params ) );

		// Calculate taxes if they're not cached
		if ( false === ( $tax = get_transient( $slug ) ) ) {
			$tax = QuadernoTax::calculate( $params );				
			
			$wc_rate = self::get_wc_rate( $tax_class, $country, $region, $postal_code, $city );
			if ( !empty( $wc_rate ) ) {
				$tax->name = $wc_rate['label'];
				$tax->rate = $wc_rate['rate'];
				$tax->country = $country;
				$tax->region = $region;
			}

			set_transient( $slug, $tax, DAY_IN_SECONDS );
		}

		return $tax;
	}

	/**
	 * Get the WooCommerce default rate
	 *
	 * @param String $tax_class
	 * @param String $country
	 * @param String $postal_code
	 *
	 * @return WC_Tax
	 */
	public static function get_wc_rate( $tax_class, $country, $region = '', $postal_code = '', $city = '' ) {
		$wc_tax = new WC_Tax();
		$params = array(
			'country' => $country,
			'state' => $region,
			'postcode' => urlencode($postal_code),
			'city' => urlencode($city),
			'tax_class' => urlencode($tax_class)
		);
		$wc_rates = $wc_tax->find_rates( $params );
		return reset( $wc_rates );
	}

}
