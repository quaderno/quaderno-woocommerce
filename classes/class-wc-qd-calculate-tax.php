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
		$product_types = array( 'product', 'product_variation', 'subscription', 'subscription_variation', 'variable-subscription' );

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

			if ( 'none' === $product->get_tax_status() ) {
				$type = 'exempted';
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
	public static function calculate( $product_id_or_tax_class, $amount, $currency, $country, $region = '', $postal_code = '', $city = '' ) {
		global $woocommerce;

		if ( is_numeric($product_id_or_tax_class) ) {
			$tax_class = WC_QD_Calculate_Tax::get_tax_class( $product_id_or_tax_class );
			$product_type = wc_get_product( $product_id_or_tax_class )->is_virtual() ? 'service' : 'good';			
		} else {
			$tax_class = $product_id_or_tax_class;
			$product_type = in_array( $tax_class, array('eservice', 'ebook') ) ? 'service' : 'good';
		}

		switch ( $tax_class ) {
			case 'eservice':
			case 'ebook':
				$quaderno_tax_class = $tax_class;
				break;
			default:
				$quaderno_tax_class = 'standard';
				break;
		}

		$params = array(
			'from_country' => apply_filters( 'quaderno_shipping_country', $woocommerce->countries->get_base_country() ),
			'from_postal_code' => apply_filters( 'quaderno_shipping_postcode', $woocommerce->countries->get_base_postcode() ),
			'to_country' => $country,
			'to_postal_code' => urlencode($postal_code),
			'to_city' => urlencode($city),
			'tax_code' => urlencode($quaderno_tax_class),
			'product_type' => $product_type,
			'amount' => $amount,
			'currency' => $currency,
			'woocommerce_tax_class' => $tax_class // we need to add the woocommerce_tax_class in this array to create different slugs for custom tax classes
		);

		$slug = 'quaderno_tax_' . md5( implode( $params ) );

		// Calculate taxes if they're not cached
		if ( false === ( $tax = get_transient( $slug ) ) ) {
			$tax = QuadernoTaxRate::calculate( $params );				
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
