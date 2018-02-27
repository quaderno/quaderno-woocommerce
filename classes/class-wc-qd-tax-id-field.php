<?php

if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}

class WC_QD_Tax_Id_Field {

	const META_KEY = 'tax_id';

	/**
	 * Setup
	 *
	 * @since 1.8
	 */
	public function setup() {
		add_action( 'woocommerce_after_checkout_billing_form', array( $this, 'print_field' ) );
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'save_field' ) );
		add_action( 'woocommerce_checkout_process', array( $this, 'validate_field' ), 1 );
		add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'display_field' ), 10, 1 );
	}

	/**
	 * Print the Tax ID field
	 *
	 * @since 1.8
	 */
	public function print_field() {
		if ( 'yes' === WC_QD_Integration::$show_tax_id ){
			woocommerce_form_field( 'tax_id', array(
				'type'   => 'text',
				'label'  => esc_html__( 'Tax ID', 'woocommerce-quaderno' ),
				'required' => true
			), '' );			
		}
	}

	/**
	 * Save the Tax ID number to the order
	 *
	 * @param $order_id
	 */
	public function save_field( $order_id ) {
		if ( ! empty( $_POST['tax_id'] ) ) {
			// Save the Tax ID number
			update_post_meta( $order_id, self::META_KEY, sanitize_text_field( $_POST['tax_id'] ) );
		}
	}

	/**
	 * Validate the Tax ID field
	 *
	 * @since 1.8
	 */
	public function validate_field() {
	  global $woocommerce;

	  $billing_country = $_POST['billing_country'];
	  $base_country = $woocommerce->countries->get_base_country();

		if ( 'yes' === WC_QD_Integration::$show_tax_id && $billing_country == $base_country && empty( $_POST['tax_id'] ) ) {
		  wc_add_notice( sprintf( __( '%s is a required field.', 'woocommerce' ), '<strong>' . esc_html__( 'Tax ID', 'woocommerce-quaderno' ) . '</strong>' ), 'error' );
		}
	}

	/**
	 * Display the Tax ID field in the backend
	 *
	 * @param $order
	 */
	public function display_field( $order ) {
		$tax_id = get_post_meta( $order->get_id(), self::META_KEY, true );
		if ( '' != $tax_id ) {
			echo '<p><strong style="display:block;">' . esc_html__( 'Tax ID', 'woocommerce-quaderno' ) . ':</strong> ' . $tax_id . '</p>';
		}
	}

}