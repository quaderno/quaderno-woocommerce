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
    $tax_class = '';
    $product_types = array( 'product', 'subscription' );
    $variation_types = array( 'product_variation', 'subscription_variation', 'variable-subscription' );

    if ( in_array( get_post_type( $product_id ), $variation_types ) ) {
   		$variation = wc_get_product( $product_id );
   		$parent = wc_get_product( $variation->get_parent_id() );

  		// the parent has a Quaderno tax class
  		if ( metadata_exists('post', $parent->get_id(), '_quaderno_tax_code' )  ) {
  			$tax_class = get_post_meta( $parent->get_id(), '_quaderno_tax_code', true );
  		}

  		// use the WooCommerce tax class
 			if ( empty( $tax_class ) ) {
 				$tax_class = $variation->get_tax_class();

 				$legacy_class = self::get_legacy_class($variation);
				if ( !empty( $legacy_class ) ) {
					$tax_class = $legacy_class;
				}
 			}
    }
    elseif ( in_array( get_post_type( $product_id ), $product_types ) ) {
			$product = wc_get_product( $product_id );

  		// the product has a Quaderno tax class
  		if ( metadata_exists('post', $product->get_id(), '_quaderno_tax_code' )  ) {
  			$tax_class = get_post_meta( $product->get_id(), '_quaderno_tax_code', true );
  		}

 			if ( empty( $tax_class ) ) {
        // for compability with old versions, we use the former rules
				$tax_class = $product->get_tax_class();

				$legacy_class = self::get_legacy_class($product);
				if ( !empty( $legacy_class ) ) {
					$tax_class = $legacy_class;
				}
  		}
    }

		return $tax_class ?: 'standard';
	}

  /**
   * Get the product type
   *
   * @param int $product_id
   *
   * @return String
   */
  public static function get_product_type( $product_id ) {
  	$product_type = 'good';

  	$product = wc_get_product( $product_id );
		if ( !empty($product) && $product->is_virtual() ) {
			$product_type = 'service';
		}

    return $product_type;
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
	public static function calculate( $tax_class, $product_type, $amount, $currency, $country, $region = '', $postal_code = '', $city = '', $tax_id = '' ) {
		global $woocommerce;
		$cache_tax = true;

		$params = array(
			'from_country' => apply_filters( 'quaderno_shipping_country', $woocommerce->countries->get_base_country() ),
			'from_postal_code' => apply_filters( 'quaderno_shipping_postcode', $woocommerce->countries->get_base_postcode() ),
			'to_country' => $country,
			'to_postal_code' => urlencode($postal_code),
			'to_city' => urlencode($city),
			'tax_id' => urlencode($tax_id),
			'tax_code' => urlencode($tax_class),
			'product_type' => $product_type,
			'amount' => $amount,
			'currency' => $currency,
			'woocommerce_tax_class' => $tax_class // we need to add the woocommerce_tax_class in this array to create different slugs for custom tax classes
		);

		$slug = 'quaderno_tax_' . md5( implode( $params ) );

		// calculate taxes if they're not cached
		if ( false === ( $tax = get_transient( $slug ) ) ) {

			if ( array_key_exists( $tax_class, WC_QD_Tax_Code_Field::TAX_CODES ) ) {
				$tax = QuadernoTaxRate::calculate( $params );
			}

			// fallback if there's any error in the tax calculator
			if ( !is_object( $tax ) ) {
				$tax = (object) ['name' => 'VAT', 'rate' => 0, 'tax_code' => 'standard', 'country' => $country];
				$cache_tax = false; // we do not cache the tax calculations in this case
			}
			$service_down = property_exists($tax, "notice") && $tax->notice == "We couldn't validate the provided tax ID because the validation service was down.";
			$cache_tax = $cache_tax && !$service_down;
			
			// we use the WooCommerce tax calculator if the tax rate exists
			$wc_rate = self::get_wc_rate( $tax_class, $country, $region, $postal_code, $city );
			if ( !empty( $wc_rate ) ) {
				$tax->name = $wc_rate['label'];
				$tax->rate = $wc_rate['rate'];
				$tax->tax_code = 'standard';
				$tax->country = $country;
				$tax->region = $region;
				$tax->city = $city;
				$tax->status = 'taxable';
			}

			if ( true === $cache_tax ) {
				set_transient( $slug, $tax, DAY_IN_SECONDS );
			}
		}

    // if the operation is reverse charge, we mark the customer as tax exempted to remove taxes from checkout form
    if ( isset( WC()->customer ) ) {
      WC()->customer->set_is_vat_exempt( $tax->status == 'reverse_charge' );
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
		$wc_tax_class = $tax_class == 'standard' ? '' : $tax_class;

		$params = array(
			'country' => $country,
			'state' => $region,
			'postcode' => urlencode($postal_code),
			'city' => urlencode($city),
			'tax_class' => urlencode($wc_tax_class)
		);
		$wc_rates = $wc_tax->find_rates( $params );
		return reset( $wc_rates );
	}

	/**
	 * Get the tax class with the legacy rules of version 1.x
	 *
	 * @param $product
	 *
	 * @return String
	 */
	public static function get_legacy_class($product) {
		$tax_class = '';

		// check if this is a virtual product
		if ( $product->is_virtual() ) {
			$tax_class = 'eservice';
		}

		// check if this is an e-book
		$is_ebook = get_post_meta( $product->get_id(), '_ebook', true );
		if ( 'yes' === $is_ebook ) {
			$tax_class = 'ebook';
		}

		// check if the product is exempted
		if ( 'none' === $product->get_tax_status() ) {
			$tax_class = 'exempt';
		}

		return $tax_class;
	}

}
