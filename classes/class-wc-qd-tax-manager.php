<?php

if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}

class WC_QD_Tax_Manager {

	/**
	 * @var array The tax rates
	 */
	private $tax_rates = array();

	/**
	 * @var array Hashmap containing product ID's with the correct tax class
	 */
	private $product_to_tax_class = array();

	public function __construct() {
		$this->setup();
	}

	/**
	 * Setup the hooks and filters
	 */
	private function setup() {
		// Override the product tax class
		add_filter( 'woocommerce_product_get_tax_class', array( $this, 'override_product_tax_class' ), 10, 2 );
		add_filter( 'woocommerce_product_variation_get_tax_class', array( $this, 'override_product_tax_class' ), 10, 2 );
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'modify_tax_rate_id' ), 10, 4 );
		add_action( 'woocommerce_checkout_create_order_fee_item', array( $this, 'modify_extra_item' ), 10, 4 );
		add_action( 'woocommerce_checkout_create_order_shipping_item', array( $this, 'modify_extra_item' ), 10, 4 );
		add_action( 'woocommerce_checkout_create_order_tax_item', array( $this, 'modify_tax_item' ), 10, 3 );

		// Override the tax rate
		add_filter( 'woocommerce_find_rates', array( $this, 'override_tax_rate' ), 10, 2 );

		// Override the rate code
		add_filter( 'woocommerce_rate_code', array( $this, 'override_rate_code' ), 10, 2 );

		// Override the rate label
		add_filter( 'woocommerce_rate_label', array( $this, 'override_rate_label'), 10, 2 );
	}

	/**
	 * Creates a hash rate id which is unique but repeatable.
	 *
	 * @since 1.14.4
	 * @return string
	 */
	private function create_unique_rate_id( $rate_id ) {
		if ( isset( $this->tax_rates[ $rate_id ] ) ) {
			$rate  = $this->tax_rates[ $rate_id ][ $rate_id ]['rate'];
			$label = $this->tax_rates[ $rate_id ][ $rate_id ]['label'];

			return (string) hexdec( substr( md5( strtolower( $label . '-' . $rate ) ), 0, 15 ) );
		}

		return (string) hexdec( substr( md5( strtolower( $rate_id ) ), 0, 15 ) );
	}

	/**
	 * Modifies the tax lines to use uniquely generated number as rate id as strings are not valid since WC30.
	 *
	 * @since 1.14.4
	 */
	public function modify_tax_rate_id( $item, $cart_item_key, $values, $order ) {
		$rebuilt_tax = array();

		foreach ( $values['line_tax_data'] as $key => $value ) {
			foreach ( $value as $k => $v ) {
				if ( preg_match( '/quaderno/', $k ) ) {
					$rate_id = $this->create_unique_rate_id( $k );
					$rebuilt_tax[ $key ][ $rate_id ] = $v;

					if ( ! isset( $this->unique_rate_id[ $k ] ) ) {
						$this->unique_rate_id[ $k ] = $rate_id;
					}
				} else {
					$rebuilt_tax[ $key ][ $k ] = $v;
				}
			}
		} 

		$item->set_props( array( 'taxes' => $rebuilt_tax ) );
	}

	/**
	 * Modifies the fee and shipping taxes to use uniquely generated number as rate id as strings are not valid since WC30.
	 *
	 * @since 1.14.5
	 */
	public function modify_extra_item( $item, $key, $value, $order ) {
		$rebuilt_tax = array();

		foreach ( $item['taxes'] as $key => $value ) {
			foreach ( $value as $k => $v ) {
				if ( preg_match( '/quaderno/', $k ) ) {
					$rate_id = $this->create_unique_rate_id( $k );
					$rebuilt_tax[ $key ][ $rate_id ] = $v;
				} else {
					$rebuilt_tax[ $key ][ $k ] = $v;
				}
			}
		}

		$item->set_props( array( 'taxes' => $rebuilt_tax ) );
	}

	/**
	 * Modifies the tax item tax rate id so it can match the tax line item.
	 *
	 * @since 1.14.4
	 */
	public function modify_tax_item( $item, $tax_rate_id, $order ) {
		if ( isset( $this->unique_rate_id[ $tax_rate_id ] ) ) {
			$item->set_props( array( 'rate_id' => $this->unique_rate_id[ $tax_rate_id ] ) );
		}
	}

	/**
	 * Format a raw tax class to a sanitized tax class
	 *
	 * @param $raw_tax_class
	 *
	 * @return string
	 */
	public function clean_tax_class( $raw_tax_class ) {
		return 'quaderno_' . sanitize_title( $raw_tax_class );
	}

	/**
	 * Map a tax class to a product ID
	 *
	 * @param int $product_id
	 * @param String $tax_class
	 *
	 * @return bool
	 */
	public function add_product_tax_class( $product_id, $tax_class ) {
		if ( ! isset( $this->product_to_tax_class[ $product_id ] ) ) {
			$this->product_to_tax_class[ $product_id ] = $this->clean_tax_class( $tax_class );

			return true;
		}

		return false;
	}

	/**
	 * Add a new tax rate
	 *
	 * @param String $tax_class
	 * @param float $rate
	 * @param String $label
	 *
	 * @return bool
	 */
	public function add_tax_rate( $tax_class, $rate, $label ) {
		$clean_slug = $this->clean_tax_class( $tax_class );

		if ( ! isset( $this->tax_rates[ $clean_slug ] ) ) {
			$this->tax_rates[ $clean_slug ] = array(
				$clean_slug => array(
					'rate'     => number_format( floatval( $rate ), 4 ),
					'label'    => $label,
					'shipping' => 'yes',
					'compound' => 'no'
				)
			);

			return true;
		}

		return false;
	}

	/**
	 * Override the WooCommerce product tax class with the new Quaderno tax class
	 *
	 * @param String $tax_class
	 * @param WC_Product $product
	 *
	 * @return string
	 */
	public function override_product_tax_class( $tax_class, $product ) {
		// Get the correct ID
		$id = ( version_compare( WC_VERSION, '3.0', '<' ) && isset( $product->variation_id ) ) ? $product->variation_id : $product->get_id();

		// Check if we got a Quaderno class for this product
		if ( isset( $this->product_to_tax_class[ $id ] ) ) {
			$tax_class = $this->product_to_tax_class[ $id ];
		}

		return $tax_class;
	}

	/**
	 * Override the WooCommerce tax rate with the new Quaderno tax rate
	 *
	 * @param array $matched_tax_rates
	 * @param array $args
	 *
	 * @return array Correct tax rates
	 */
	public function override_tax_rate( $matched_tax_rates, $args ) {
		// Check if we got a Quaderno rate for this tax class
		if ( isset( $this->tax_rates[ $args['tax_class'] ] ) ) {
			$matched_tax_rates = $this->tax_rates[ $args['tax_class'] ];
		}

		return $matched_tax_rates;
	}

	/**
	 * Override the WooCommerce code string with the custom rate code
	 *
	 * @param String $code_string
	 * @param String $key
	 *
	 * @return String $code_string
	 */
	public function override_rate_code( $code_string, $key ) {
		if ( isset( $this->tax_rates[ $key ] ) ) {
			$code_string = strtoupper( $this->tax_rates[ $key ][ $key ]['label'] . '|' . $this->tax_rates[ $key ][ $key ]['rate'] );
		}

		return $code_string;
	}

	/**
	 * Override the WooCommerce rate label with the custom rate label
	 * @param $rate_name
	 * @param $key
	 *
	 * @return mixed
	 */
	public function override_rate_label( $rate_name, $key ) {
		if ( isset( $this->tax_rates[ $key ] ) ) {
			$rate_name = $this->tax_rates[ $key ][ $key ]['label'];
		}

		return $rate_name;
	}

}
