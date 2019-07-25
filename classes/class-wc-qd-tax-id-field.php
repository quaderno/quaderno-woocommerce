<?php

if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}

// Countries wbere tax ID is required in local purchases
if ( ! defined( 'TAX_ID_COUNTRIES' ) ) {
  define( 'TAX_ID_COUNTRIES', ['BG', 'CY', 'ES', 'HR', 'IT', 'PT'] );
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
		add_action( 'woocommerce_after_checkout_validation', array( $this, 'validate_field' ), 10, 2 );
		add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'display_field' ), 10, 1 );

    add_action( 'woocommerce_quaderno_meta_fields', array( $this, 'add_customer_meta_fields'), 30, 1 ); 
    add_action( 'woocommerce_quaderno_meta_fields', array( $this, 'add_customer_meta_fields'), 30, 1 ); 

    add_action( 'personal_options_update', array( $this, 'save_customer_meta_fields'), 30, 1 ); 
    add_action( 'edit_user_profile_update', array( $this, 'save_customer_meta_fields'), 30, 1 ); 
	}

	/**
	 * Print the Tax ID field
	 *
	 * @since 1.8
	 */
	public function print_field() {
    global $woocommerce;

    $base_country = $woocommerce->countries->get_base_country();
    $user_tax_id = get_user_meta( get_current_user_id(), '_quaderno_tax_id', true );

		woocommerce_form_field( 'tax_id', array(
			'type'   => 'text',
			'label'  => esc_html__( 'Tax ID', 'woocommerce-quaderno' ),
			'required' => in_array($base_country, TAX_ID_COUNTRIES)
		), $user_tax_id );			
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
	public function validate_field( $fields, $errors ) {
	  global $woocommerce;

	  $billing_country = WC()->customer->get_billing_country();
	  $base_country = $woocommerce->countries->get_base_country();

		if ( in_array($base_country, TAX_ID_COUNTRIES) && $billing_country == $base_country && empty( $_POST['tax_id'] ) ) {
      $errors->add( 'required-field', sprintf( __( '%s is a required field.', 'woocommerce' ), '<strong>' . esc_html__( 'Tax ID', 'woocommerce-quaderno' ) . '</strong>' ));
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

  /**
   * Add custom fields to admin area
   *
   * @since 1.12
   */
  public function add_customer_meta_fields( $user ) {
    global $woocommerce;

    $base_country = $woocommerce->countries->get_base_country();

    if ( in_array($base_country, TAX_ID_COUNTRIES) ) {
    ?>    
    <tr>
      <th>
        <label for="tax_id"><?php echo esc_html__( 'Tax ID', 'woocommerce-quaderno' ) ?></label>
      </th>
      <td>
        <input type="text" name="tax_id" id="tax_id" value="<?php echo esc_attr( get_the_author_meta( '_quaderno_tax_id', $user->ID ) ); ?>" class="regular-text" />
      </td>
    </tr>
    <?php
    } 
  }

  /**
   * Save custom fields from admin area
   *
   * @since 1.12
   */
  public function save_customer_meta_fields( $user_id ) {
    if ( !current_user_can( 'edit_user', $user_id ) ) { 
      return false; 
    }
    update_user_meta( $user_id, '_quaderno_tax_id', $_POST['tax_id'] );
  }

}