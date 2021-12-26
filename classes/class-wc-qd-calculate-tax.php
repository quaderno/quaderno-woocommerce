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
    $tax_class = 'standard';
    $product_types = array( 'product', 'product_variation', 'subscription', 'subscription_variation', 'variable-subscription' );

    if ( in_array( get_post_type( $product_id ), $product_types ) ) {

  		// the product has a Quaderno tax clas
  		if ( metadata_exists('post', $product_id, '_quaderno_tax_code' )  ) {
  			$tax_class = get_post_meta( $product_id, '_quaderno_tax_code', true );

  			if ( empty( $tax_class ) ) {
  				$product = wc_get_product( $product_id );
  				$tax_class = $product->get_tax_class();
  			}
  		}
  		else {
        // for compability with old versions
        // we use the former rules
				$product = wc_get_product( $product_id );
				$tax_class = $product->get_tax_class();

				// check if this is a virtual product
				if ( $product->is_virtual() ) {
					$tax_class = 'eservice';
				}

				// check if this is an e-book
				$is_ebook = get_post_meta( $product_id, '_ebook', true );
				if ( 'yes' === $is_ebook ) {
					$tax_class = 'ebook';
				}

				if ( 'none' === $product->get_tax_status() ) {
					$tax_class = 'exempt';
				}
  		}
    }

		return $tax_class;
	}

  /**
   * Get the product type
   *
   * @param int $product_id
   *
   * @return String
   */
  public static function get_product_type( $product_id ) {
    wc_get_product( $product_id )->is_virtual() ? 'service' : 'good';
  }

	
	/**
	 * Get the tax rate
	 *
	 * @param String $tax_class
	 * @param String $country
	 * @param String $region
	 * @param String $postal_code
	 * @param String $city
	 *
	 * @return Tax
	 */
	public static function calculate( $tax_class, $product_type, $amount, $currency, $country, $region = '', $postal_code = '', $city = '' ) {
		global $woocommerce;

		$params = array(
			'from_country' => apply_filters( 'quaderno_shipping_country', $woocommerce->countries->get_base_country() ),
			'from_postal_code' => apply_filters( 'quaderno_shipping_postcode', $woocommerce->countries->get_base_postcode() ),
			'to_country' => $country,
			'to_postal_code' => urlencode($postal_code),
			'to_city' => urlencode($city),
			'tax_code' => urlencode($tax_class),
			'product_type' => $product_type,
			'amount' => $amount,
			'currency' => $currency,
			'woocommerce_tax_class' => $tax_class // we need to add the woocommerce_tax_class in this array to create different slugs for custom tax classes
		);

		$slug = 'quaderno_tax_' . md5( implode( $params ) );

		// calculate taxes if they're not cached
		if ( false === ( $tax = get_transient( $slug ) ) ) {
			$tax = QuadernoTaxRate::calculate( $params );

			// we use the WooCommerce tax engine if the tax code doesn't exist in Quaderno
			if ( !array_key_exists( $tax_class, WC_QD_Tax_Code_Field::TAX_CODES ) || empty( $tax_class ) ) {
				$tax->tax_code = 'standard';

				$wc_rate = self::get_wc_rate( $tax_class, $country, $region, $postal_code, $city );
				if ( !empty( $wc_rate ) ) {
					$tax->name = $wc_rate['label'];
					$tax->rate = $wc_rate['rate'];
					$tax->country = $country;
					$tax->region = $region;
				}
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
