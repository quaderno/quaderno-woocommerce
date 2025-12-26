<?php

if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}

class WC_QD_Status {
  /**
   * Setup
   *
   * @since 1.23
   */
  public function setup() {
    add_action( 'woocommerce_admin_status_content_quaderno', array( $this, 'add_quaderno_status_content' ), 10, 1 );
    add_filter( 'woocommerce_admin_status_tabs', array( $this, 'add_quaderno_status_tab' ), 10, 1 );
  }

  function add_quaderno_status_tab( $tabs ){
    $tabs['quaderno'] = __( 'Quaderno', 'woocommerce-quaderno' );
    return $tabs;
  }

  function add_quaderno_status_content(){
    global $woocommerce;

    $base_country = $woocommerce->countries->get_base_country();
    $base_region = $woocommerce->countries->get_base_state();

    // Get all the standard tax codes
    $codes = array();
    $base_country_in_rates = false;
    $has_non_base_country_rates = false;
    foreach( WC_TAX::get_rates_for_tax_class('') as $key => $rate ) {
      array_unshift( $codes, WC_TAX::get_rate_code( $key ) . ' => ' . round($rate->tax_rate, 2) . '%' );
      if ( $rate->tax_rate_country === $base_country ) {
        $base_country_in_rates = true;
      } else {
        $has_non_base_country_rates = true;
      }
    }

    $tax_calculation_options = array(
      'shipping' => __( 'Customer shipping address', 'woocommerce-quaderno' ),
      'billing'  => __( 'Customer billing address', 'woocommerce-quaderno' ),
      'base'     => __( 'Shop base address', 'woocommerce-quaderno' ),
    );

    $shipping_tax_classes = array( 'inherit' => __( 'Shipping tax class based on cart items', 'woocommerce-quaderno' ) ) + wc_get_product_tax_class_options();

    $request = new QuadernoRequest();
    $api_response = $request->ping() ? 'yes' : 'no';

    // Check if the store is using the new checkout block or classic checkout
    $checkout_page_id = wc_get_page_id( 'checkout' );
    $checkout_type = __( 'Unknown', 'woocommerce-quaderno' );
    $is_checkout_block = false;

    if ( $checkout_page_id ) {
      if ( has_block( 'woocommerce/checkout', $checkout_page_id ) ) {
        $checkout_type = __( 'Checkout Block', 'woocommerce-quaderno' );
        $is_checkout_block = true;
      } elseif ( has_block( 'woocommerce/classic-shortcode', $checkout_page_id ) ) {
        $checkout_type = __( 'Classic Checkout', 'woocommerce-quaderno' );
      } 
    }

    ?>
    <p>Please copy and paste this information in your ticket when contacting support:</p>
    <table class="wc_status_table wc_status_table--quaderno widefat" cellspacing="0">
      <thead>
        <th colspan="3" data-export-label="Tax Settings">
          <h2>Tax Settings</h2>
        </th>
      </thead>
      <tbody class="quaderno">
        <tr>
          <td data-export-label="Store base">Store base:</td>
          <td class="help"></td>
          <td><?php echo esc_html( $base_country ) . ' — ' . esc_html( $base_region ); ?></td>
        </tr>
        <tr>
          <td data-export-label="Store base">Enable taxes:</td>
          <td class="help"></td>
          <td><?php 
            echo esc_html( ucfirst( get_option( 'woocommerce_calc_taxes' ) ) ); 
            if ( get_option( 'woocommerce_calc_taxes' ) == 'no' ) {
              echo '&nbsp;<mark class="error" title="' . esc_attr__( 'You must enable the tax calculations in WooCommerce > Settings > General.', 'woocommerce-quaderno' ) . '"><span class="dashicons dashicons-warning"></span></mark>';
            }
          ?></td>
        </tr>
        <tr>
          <td data-export-label="Tax in prices">Tax in prices:</td>
          <td class="help"></td>
          <td><?php 
            echo esc_html( get_option( 'woocommerce_prices_include_tax' ) == 'yes' ? 'Included' : 'Excluded' ); 
            if ( get_option( 'woocommerce_prices_include_tax' ) == 'yes' && ! $base_country_in_rates ) {
              echo '&nbsp;<mark class="error" title="' . esc_attr__( 'You must add your base country in the standard tax rates page if you work with tax excluded prices.', 'woocommerce-quaderno' ) . '"><span class="dashicons dashicons-warning"></span></mark>';
            }
          ?></td>
        </tr>
        <tr>
          <td data-export-label="Tax calculations based on">Tax calculations based on:</td>
          <td class="help"></td>
          <td><?php echo esc_html( $tax_calculation_options[ get_option( 'woocommerce_tax_based_on' ) ] ); ?></td>
        </tr>
        <tr>
          <td data-export-label="Shipping tax class">Shipping tax class:</td>
          <td class="help"></td>
          <td><?php echo esc_html( $shipping_tax_classes[ get_option( 'woocommerce_shipping_tax_class' ) ] ); ?></td>
        </tr>
        <tr>
          <td data-export-label="Display prices">Display prices during cart and checkout:</td>
          <td class="help"></td>
          <td><?php echo esc_html( get_option( 'woocommerce_tax_display_cart' ) == 'incl' ? 'Included' : 'Excluded' ); ?></td>
        </tr>
        <tr>
          <td data-export-label="Display prices">Standard tax rates:</td>
          <td class="help"></td>
        <td><?php
          echo esc_html( implode( ', ', (array) $codes ) );
          if ( $has_non_base_country_rates ) {
            echo '&nbsp;<mark class="error" title="' . esc_attr__( 'You have standard tax rates for locations other than your base country. Please remove them to avoid overwriting Quaderno tax calculations.', 'woocommerce-quaderno' ) . '"><span class="dashicons dashicons-warning"></span></mark>';
          }
        ?></td>
      </tr>
    </tbody>
    </table>
    <table class="wc_status_table wc_status_table--quaderno widefat" cellspacing="0">
      <thead>
        <th colspan="23" data-export-label="API Settings">
          <h2>Plugin Settings</h2>
        </th>
      </thead>
      <tbody class="quaderno">
        <tr>
          <td data-export-label="API URL">API credentials:</td>
          <td class="help"></td>
          <td><?php
          if ( $api_response == 'yes' ) {
            echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
          } else {
            echo '<mark class="error" title="' . esc_attr__( 'Invalid API credentials. Please check your API key and URL in WooCommerce > Settings > Integration > Quaderno.', 'woocommerce-quaderno' ) . '"><span class="dashicons dashicons-warning"></span></mark>';
          }
          ?></td>
        </tr>
        <tr>
          <td data-export-label="Require tax ID">Require tax ID in <?php echo esc_html( $woocommerce->countries->countries[ $base_country ] ); ?>:</td>
          <td class="help"></td>
          <td><mark class="<?php echo esc_attr( WC_QD_Integration::$require_tax_id ); ?>"><span class="dashicons dashicons-<?php echo esc_attr( WC_QD_Integration::$require_tax_id ); ?>"></span></mark></td>
        </tr>
        <tr>
          <td data-export-label="Checkout type">Checkout type:</td>
          <td class="help"></td>
          <td><?php
            echo esc_html( $checkout_type );
            if ( $is_checkout_block ) {
              echo '&nbsp;<mark class="error" title="' . esc_attr__( 'The Checkout Block is not fully supported. Please use the Classic Checkout for the best compatibility with Quaderno.', 'woocommerce-quaderno' ) . '"><span class="dashicons dashicons-warning"></span></mark>';
            }
          ?></td>
        </tr>
      </tbody>
    </table>
    <?php
  }

}
