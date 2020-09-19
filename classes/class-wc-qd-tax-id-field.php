<?php

if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}

class WC_QD_Tax_Id_Field {

  const COUNTRIES = ['AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE', 'GB', 'AU', 'NZ', 'TR'];

	/**
	 * Setup
	 *
	 * @since 1.8
	 */
	public function setup() {
		add_action( 'woocommerce_after_checkout_billing_form', array( $this, 'print_field' ) );
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'save_field' ) );
    add_action( 'woocommerce_after_checkout_validation', array( $this, 'validate_field' ), 10, 2 );

    add_filter( 'woocommerce_form_field', array( $this, 'remove_checkout_optional_text'), 10, 4 );
    add_filter( 'woocommerce_default_address_fields', array( $this, 'display_field_in_address_form' ), 20, 1 );
    add_filter( 'woocommerce_customer_meta_fields', array( $this, 'display_field_in_admin_form' ), 20, 1 );
	}

	/**
	 * Print the Tax ID field
	 *
	 * @since 1.8
	 */
	public function print_field() {
    global $woocommerce;

    $user_tax_id = get_user_meta( get_current_user_id(), 'billing_tax_id', true );
    if ( empty( $user_tax_id ) ) {
      $user_tax_id = get_user_meta( get_current_user_id(), '_quaderno_vat_number', true );
    }
		if ( empty( $user_tax_id ) ) {
      $user_tax_id = get_user_meta( get_current_user_id(), '_quaderno_tax_id', true );
    }

    woocommerce_form_field( 'tax_id', array(
			'type'   => 'text',
			'label'  => esc_html__( 'Tax ID', 'woocommerce-quaderno' ),
      'placeholder' => esc_html__( 'VAT number, ABN, or NZBN', 'woocommerce-quaderno' ),
      'class'  => array( 'update_totals_on_change' ),
      'autocomplete' => 'nope'
		), $user_tax_id );			
	}

	/**
	 * Save the Tax ID number to the order
	 *
	 * @param $order_id
	 */
	public function save_field( $order_id ) {
    global $woocommerce;

		if ( ! empty( $_POST['tax_id'] ) ) {
      // Remove non-word characters
      $tax_id = preg_replace('/\W/', '', sanitize_text_field( $_POST['tax_id'] ));

      $order = wc_get_order( $order_id );
      $billing_country = $order->get_billing_country();
      $base_country = $woocommerce->countries->get_base_country();

      if ( $billing_country == $base_country || 'yes' === get_post_meta( $order_id, 'is_vat_exempt', true ) ) {
        update_post_meta( $order_id, 'tax_id', $tax_id );
      } else {
        $order->add_order_note( sprintf( __( 'Tax ID %s could not be validated', 'woocommerce-quaderno' ), $tax_id ) );
      }
		}
	}

  /**
   * Validate the Tax ID field
   *
   * @since 1.19
   */
  public function validate_field( $fields, $errors ) {
    global $woocommerce;
    $billing_country = WC()->customer->get_billing_country();
    $base_country = $woocommerce->countries->get_base_country();

    if ( 'yes' === WC_QD_Integration::$require_tax_id && in_array( $base_country, self::COUNTRIES ) && $billing_country == $base_country && empty( $_POST['tax_id'] ) ) {
      $errors->add( 'required-field', sprintf( __( '%s is a required field.', 'woocommerce' ), '<strong>' . esc_html__( 'Tax ID', 'woocommerce-quaderno' ) . '</strong>' ));
    }
  }

  /**
   * Validate business number
   *
   * @param string $tax_id
   * @param string $country
   *
   * @return boolean
   *
   * @since 1.18
   */
  public static function is_valid( $tax_id, $country ){
    global $woocommerce;

    // remove non-word characters from tax ID
    $tax_id = preg_replace('/\W/', '', $tax_id);

    // get the country code from the number if it's empty
    if ( empty($country) ) {
      $country = substr( $tax_id, 0, 2 );
    }

    $params = array(
      'vat_number' => $tax_id,
      'country' => $country
    );

    $slug = 'vat_number_' . md5( implode( $params ) );

    if ( false === ( $valid_number = get_transient( $slug ) ) ) {
      $valid_number = (int) QuadernoTax::validate( $params );
      set_transient( $slug, $valid_number, 4 * WEEK_IN_SECONDS );
    }

    return $valid_number == 1 && $country != $woocommerce->countries->get_base_country();
  }

  /**
   * Remove the text "optional" if the tax ID is required
   *
   * @since 1.19
   */
  public function remove_checkout_optional_text( $field, $key, $args, $value ) {
    if( is_checkout() && ! is_wc_endpoint_url() && 'yes' === WC_QD_Integration::$require_tax_id ) {
      $optional = '&nbsp;<span class="optional">(' . esc_html__( 'optional', 'woocommerce' ) . ')</span>';
      $field = str_replace( $optional, '', $field );
    }
    return $field;
  } 

  /**
   * Show the Tax ID field in My Account section
   *
   * @since 1.21
   */
  public function display_field_in_address_form( $fields ) {
    // Only on account pages
    if( ! is_account_page() ) return $fields;

    $fields['tax_id'] = array(
        'label'        => __( 'Tax ID', 'woocommerce-quaderno' ),
        'required'     => false,
        'priority'     => 33
      );

    return $fields;
  }

  /**
   * Show the Tax ID field in the User form
   *
   * @since 1.21
   */
  public function display_field_in_admin_form( $admin_fields ) {
    $admin_fields['billing']['fields']['billing_tax_id'] = array(
      'label' => __( 'Tax ID', 'woocommerce-quaderno' )
    );

    return $admin_fields;
  }

}
