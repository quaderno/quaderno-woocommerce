<?php

if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}

class WC_QD_Vat_Number_Field {

	const META_KEY = 'vat_number';

	/**
	 * Setup
	 *
	 * @since 1.0
	 */
	public function setup() {
		add_action( 'woocommerce_after_checkout_billing_form', array( $this, 'print_field' ) );
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'save_field' ) );
		add_action( 'woocommerce_checkout_process', array( $this, 'validate_field' ), 1 );
		add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'display_field' ), 10, 1 );
		add_filter( 'woocommerce_form_field_hidden', array( $this, 'wc_form_hidden_field' ), 10, 4 );
	}

	/**
	 * Print the VAT field
	 *
	 * @since 1.0
	 */
	public function print_field() {
	  global $woocommerce;

		woocommerce_form_field( 'vat_number', array(
			'type'   => 'text',
			'label'  => esc_html__( 'VAT number', 'woocommerce-quaderno' ),
			'class'  => array( 'update_totals_on_change' )
		), '' );

		woocommerce_form_field( 'base_location', array(
			'type'   => 'hidden',
			'default' => $woocommerce->countries->get_base_country()
		));
	}

	/**
	 * Save the VAT number to the order
	 *
	 * @param $order_id
	 */
	public function save_field( $order_id ) {
		if ( ! empty( $_POST['vat_number'] ) ) {
			// Save the VAT number
			update_post_meta( $order_id, self::META_KEY, sanitize_text_field( $_POST['vat_number'] ) );

			// Reset the customer VAT exempt state
			WC()->customer->set_is_vat_exempt( false );
		}
	}

	/**
	 * Validate the VAT field
	 *
	 * @since 1.4
	 */
	public function validate_field() {
		if ( ! empty( $_POST['vat_number'] ) ) {
		  $valid_number = $this::is_valid( $_POST['vat_number'], $_POST['billing_country'] );

			if ( false === $valid_number ) {
				wc_add_notice( sprintf( esc_html__( '%s is not valid.', 'woocommerce-quaderno' ), '<strong>' . esc_html__( 'VAT number', 'woocommerce-quaderno' ) . '</strong>' ), 'error' );
			}
		}
	}

	/**
	 * Display the VAT field in the backend
	 *
	 * @param $order
	 */
	public function display_field( $order ) {
		$vat_number = get_post_meta( $order->get_id(), self::META_KEY, true );
		if ( '' != $vat_number ) {
			echo '<p><strong style="display:block;">' . esc_html__( 'VAT number', 'woocommerce-quaderno' ) . ':</strong> ' . $vat_number . '</p>';
		}
	}

	/**
	 * Outputs a hidden form field.
	 *
	 * @param string $field
	 * @param string $key
	 * @param mixed $args
	 * @param string $value (default: null)
	 *
	 * @since 1.5
	 */
	function wc_form_hidden_field( $field, $key, $args, $value ){
		$defaults = array(
			'label'             => '',
			'id'                => $key,
			'class'             => array(),
			'input_class'       => array(),
			'custom_attributes' => array(),
			'default'           => '',
		); 
		$args = wp_parse_args( $args, $defaults );

		// Custom attribute handling
		$custom_attributes = array();
		if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) )
			foreach ( $args['custom_attributes'] as $attribute => $attribute_value )
			$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';

		$field = '<p class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) .'" id="' . esc_attr( $args['id'] ) . '_field">';

		$field .= '<input type="hidden" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) .'" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" value="' . esc_attr( $value ) . '" ' . implode( ' ', $custom_attributes ) . ' /></p>';

		return $field;
	}

	/**
	 * Validate a VAT number
	 *
	 * @param string $vat_number
	 * @param string $country
	 *
	 * @return boolean
	 *
	 * @since 1.9
	 */
	public static function is_valid( $vat_number, $country ){
	  $params = array(
			'vat_number' => $vat_number,
			'country' => $country
		);

		$slug = 'vat_number_' . md5( implode( $params ) );

		if ( false === ( $valid_number = get_transient( $slug ) ) ) {
			$valid_number = (int) QuadernoTax::validate( $params );
			set_transient( $slug, $valid_number, 4 * WEEK_IN_SECONDS );
		}

		return $valid_number == 1;
	}

}