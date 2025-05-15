<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class WC_QD_Checkout_Manager {

	/**
	 * Setup the class
	 */
	public function setup() {
		// Update the taxes on the cart page
		if ( is_cart() ) {
   	 add_action( 'woocommerce_before_calculate_totals', array( $this, 'update_taxes_on_cart_view' ), 10, 1 );
  	}

		// Update the taxes on the checkout page whenever the order review template part is refreshed
		add_action( 'woocommerce_checkout_update_order_review', array( $this, 'update_taxes_on_update_order_review' ), 10, 1 );

		// Update the taxes in the checkout process when the checkout is processed
		add_action( 'woocommerce_checkout_process', array( $this, 'update_taxes_on_check_process' ), 10 );

		// Update the taxes when the line taxes are calculated in the admin
		add_filter( 'woocommerce_ajax_calc_line_taxes', array( $this, 'update_taxes_on_calc_line_taxes' ), 10, 3 );
	}

	/**
	 * Update taxes in cart
	 *
	 * @param object $transaction
	 * @param String $country_code
	 */
	private function update_taxes_in_cart( $items ) {
		if ( count( $items ) > 0 ) {

			// Create tax manager object
			$tax_manager = new WC_QD_Tax_Manager();

			foreach ( $items as $key => $item ) {
				// Add the new product class for this product
				$tax_manager->add_product_tax_class( $item['id'], $item['product_type'] );

				// Add the new tax rate for this transaction line
				$tax_manager->add_tax_rate( $item['product_type'], $item['tax_rate'], $item['tax_name'], $item['additional_tax_rate'], $item['additional_tax_name'] );
			}

			$tax_rates = $tax_manager->get_tax_rates();

			return $tax_rates;
		}
	}

  /**
   * Show tax amount in the cart view
   *
   * @param integer $total
   * @param String $country_code
   */
  public function update_taxes_on_cart_view( $cart ) {
    $shipping_country = $cart->get_customer()->get_shipping_country();
    $shipping_state = $cart->get_customer()->get_shipping_state();
    $shipping_postcode = $cart->get_customer()->get_shipping_postcode();
    $shipping_city = $cart->get_customer()->get_shipping_city();

    // The cart manager
    $cart_manager = new WC_QD_Cart_Manager( $shipping_country, $shipping_state, $shipping_postcode, $shipping_city, '', '' );

    // Update the taxes in cart based on cart items
    $this->update_taxes_in_cart( $cart_manager->get_items_from_cart() );
  }

	/**
	 * Catch the update order review action and update taxes to selected billing country
	 *
	 * @param $post_data
	 */
	public function update_taxes_on_update_order_review( $post_data ) {
		// Parse the string
		parse_str( $post_data, $post_arr );

		$tax_based_on = get_option( 'woocommerce_tax_based_on' );
		if ( 'shipping' === $tax_based_on && ! isset($post_arr['ship_to_different_address']) ) {
			$tax_based_on = 'billing';
		}

		// Tax location
		if ( 'base' === $tax_based_on ) {
			$country  = WC()->countries->get_base_country();
			$state  = WC()->countries->get_base_state();
			$postcode = WC()->countries->get_base_postcode();
			$city = WC()->countries->get_base_city();
      $street = WC()->countries->get_base_address();
		} elseif ( 'billing' === $tax_based_on ) {
			$country  = sanitize_text_field( $post_arr['billing_country'] );
			$state = isset( $post_arr['billing_state'] ) ? sanitize_text_field( $post_arr['billing_state'] ) : null;
			$postcode = isset( $post_arr['billing_postcode'] ) ? sanitize_text_field( $post_arr['billing_postcode'] ) : null;
			$city = isset( $post_arr['billing_city'] ) ? sanitize_text_field( $post_arr['billing_city'] ) : null;
			$street = isset( $post_arr['billing_address_1'] ) ? sanitize_text_field( $post_arr['billing_address_1'] ) : null;
		} else {
			$country  = sanitize_text_field( $post_arr['shipping_country'] );
			$state = isset( $post_arr['shipping_state'] ) ? sanitize_text_field( $post_arr['shipping_state'] ) : null;
			$postcode = isset( $post_arr['shipping_postcode'] ) ? sanitize_text_field( $post_arr['shipping_postcode'] ) : null;
			$city = isset( $post_arr['shipping_city'] ) ? sanitize_text_field( $post_arr['shipping_city'] ) : null;
			$street = isset( $post_arr['shipping_address_1'] ) ? sanitize_text_field( $post_arr['shipping_address_1'] ) : null;
		}
		$tax_id = sanitize_text_field( 'base' != $tax_based_on ? $post_arr['tax_id'] : '' );

		// The cart manager
		$cart_manager = new WC_QD_Cart_Manager($country, $state, $postcode, $city, $street, $tax_id);

		// Update the taxes in cart based on cart items
		$this->update_taxes_in_cart( $cart_manager->get_items_from_cart() );
	}

	/**
	 * Update taxes in the checkout processing process
	 */
	public function update_taxes_on_check_process() {
		$tax_based_on = get_option( 'woocommerce_tax_based_on' );
		if ( 'shipping' === $tax_based_on && ! isset( $_POST['ship_to_different_address'] )) {
			$tax_based_on = 'billing';
		}

		// Tax location
		if ( 'base' === $tax_based_on ) {
			$country  = WC()->countries->get_base_country();
			$state = WC()->countries->get_base_state();
			$postcode = WC()->countries->get_base_postcode();
			$city = WC()->countries->get_base_city();
      $street = WC()->countries->get_base_address();
		} elseif ( 'billing' === $tax_based_on ) {
			$country  = sanitize_text_field( $_POST['billing_country'] );
			$state = sanitize_text_field( $_POST['billing_state'] );
			$postcode = sanitize_text_field( $_POST['billing_postcode'] );
			$city = sanitize_text_field( $_POST['billing_city'] );
			$street = sanitize_text_field( $_POST['billing_address_1'] );
		} else {
			$country  = sanitize_text_field( $_POST['shipping_country'] );
			$state = sanitize_text_field( $_POST['shipping_state'] );
			$postcode = sanitize_text_field( $_POST['shipping_postcode'] );
			$city = sanitize_text_field( $_POST['shipping_city'] );
			$street = sanitize_text_field( $_POST['shipping_address_1'] );
		}
		$tax_id = sanitize_text_field( 'base' != $tax_based_on ? $_POST['tax_id'] : '' );

		// The cart manager
		$cart_manager = new WC_QD_Cart_Manager($country, $state, $postcode, $city, $street, $tax_id);

		// Update the taxes in the cart based on cart items
		$this->update_taxes_in_cart( $cart_manager->get_items_from_cart() );
	}

	/**
	 * Update the taxes when the line taxes are calculated in the admin
	 *
	 * @param array $items
	 * @param int $order_id
	 * @param String $country
	 *
	 * @return array
	 */
	public function update_taxes_on_calc_line_taxes( $items, $order_id, $country ) {
		// Check for items
		if ( isset( $items['order_item_id'] ) ) {
			$tax_manager = new WC_QD_Tax_Manager();

			// Get the order
			$order = wc_get_order( $order_id );

			// Loop through items
			foreach ( $items['order_item_id'] as $item_id ) {
				// Get the product ID
				$product_id = $order->get_item_meta( $item_id, '_product_id', true );

				// Get the tax class and the product type
				$tax_class = WC_QD_Calculate_Tax::get_tax_class( $product_id );
				$product_type = WC_QD_Calculate_Tax::get_product_type( $product_id );

				// Calculate taxes
				$tax = WC_QD_Calculate_Tax::calculate($tax_class, $product_type, $order->get_total(''), get_woocommerce_currency(), $country, '', '', '', '', $order->get_meta( 'tax_id' ));

				$tax_manager->add_product_tax_class( $item_id, $tax_class );
				$tax_manager->add_tax_rate( $tax_class, $tax->rate, $tax->name );
				$items['order_item_tax_class'][ $item_id ] = $tax_manager->clean_tax_class( $tax_class );
			}

		}

		return $items;
	}

}
