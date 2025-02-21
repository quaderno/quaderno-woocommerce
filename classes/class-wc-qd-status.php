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
    $tabs['quaderno'] = __( 'Quaderno', 'woocommerce' );
    return $tabs;
  }

  function add_quaderno_status_content(){
    global $woocommerce;

    $base_country = $woocommerce->countries->get_base_country();
    $base_region = $woocommerce->countries->get_base_state();

    // Get all the standard tax codes
    $codes = array();
    foreach( WC_TAX::get_rates_for_tax_class('') as $key => $rate ) {
      array_unshift( $codes, WC_TAX::get_rate_code( $key ) . ' => ' . round($rate->tax_rate, 2) . '%' );
    }

    $tax_calculation_options = array(
      'shipping' => __( 'Customer shipping address', 'woocommerce' ),
      'billing'  => __( 'Customer billing address', 'woocommerce' ),
      'base'     => __( 'Shop base address', 'woocommerce' ),
    );

    $shipping_tax_classes = array( 'inherit' => __( 'Shipping tax class based on cart items', 'woocommerce' ) ) + wc_get_product_tax_class_options();

    $request = new QuadernoRequest();
    $api_response = $request->ping() ? 'yes' : 'no';
    
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
            <td><?php echo $base_country . ' — ' . $base_region; ?></td>
          </tr>
          <tr>
            <td data-export-label="Tax in prices">Tax in prices:</td>
            <td class="help"></td>
            <td><?php echo get_option( 'woocommerce_prices_include_tax' ) == 'yes' ? 'Included' : 'Excluded' ?></td>
          </tr>
          <tr>
            <td data-export-label="Tax calculations based on">Tax calculations based on:</td>
            <td class="help"></td>
            <td><?php echo $tax_calculation_options[get_option( 'woocommerce_tax_based_on' )] ?></td>
          </tr>
          <tr>
            <td data-export-label="Shipping tax class">Shipping tax class:</td>
            <td class="help"></td>
            <td><?php echo $shipping_tax_classes[get_option( 'woocommerce_shipping_tax_class' )] ?></td>
          </tr>
          <tr>
            <td data-export-label="Display prices">Display prices during cart and checkout:</td>
            <td class="help"></td>
            <td><?php echo get_option( 'woocommerce_tax_display_cart' ) == 'incl' ? 'Included' : 'Excluded' ?></td>
          </tr>
          <tr>
            <td data-export-label="Display prices">Standard tax rates:</td>
            <td class="help"><?php if (get_option( 'woocommerce_prices_include_tax' ) == 'yes') { echo wc_help_tip( esc_html__( 'You must add your base country in the standard tax rates page if you work with tax included prices.', 'woocommerce-quaderno' ) ); } ?></td>
            <td><?php echo(implode(', ', $codes)) ?></td>
          </tr>
        </tbody>
    </table>
    <table class="wc_status_table wc_status_table--quaderno widefat" cellspacing="0">
      <thead>
        <th colspan="3" data-export-label="API Settings">
          <h2>Plugin Settings</h2>
        </th>
      </thead>
        <tbody class="quaderno">
          <tr>
            <td data-export-label="API URL">API credentials:</td>
            <td class="help"><?php echo wc_help_tip( esc_html__( 'Check if API credentials are correct.', 'woocommerce-quaderno' ) ); ?></td>
            <td><mark class="<?php echo($api_response) ?>"><span class="dashicons dashicons-<?php echo($api_response) ?>"></span></mark></td>
          </tr>
          <tr>
            <td data-export-label="Autosend invoices">Autosend invoices:</td>
            <td class="help"><?php echo wc_help_tip( esc_html__( 'Invoices and credit notes will be automatically sent.', 'woocommerce-quaderno' ) ); ?></td>
            <td><mark class="<?php echo(WC_QD_Integration::$autosend_invoices) ?>"><span class="dashicons dashicons-<?php echo(WC_QD_Integration::$autosend_invoices) ?>"></span></mark></td>
          </tr>
          <tr>
            <td data-export-label="Autosend invoices">Update tax in subscriptions:</td>
            <td class="help"><?php echo wc_help_tip( esc_html__( 'Taxes will be recalculated for subscriptions if needed', 'woocommerce-quaderno' ) ); ?></td>
            <td><mark class="<?php echo(WC_QD_Integration::$update_subscription_tax) ?>"><span class="dashicons dashicons-<?php echo(WC_QD_Integration::$update_subscription_tax) ?>"></span></mark></td>
          </tr>
          <tr>
            <td data-export-label="Require tax ID">Require tax ID in <?php echo $woocommerce->countries->countries[ $base_country ] ?>:</td>
            <td class="help"><?php echo wc_help_tip( esc_html__( 'Local customers have to enter their tax ID.', 'woocommerce-quaderno' ) ); ?></td>
            <td><mark class="<?php echo(WC_QD_Integration::$require_tax_id) ?>"><span class="dashicons dashicons-<?php echo(WC_QD_Integration::$require_tax_id) ?>"></span></mark></td>
          </tr>
          <tr>
            <td data-export-label="Universal pricing">Universal pricing:</td>
            <td class="help"><?php echo wc_help_tip( esc_html__( 'All customers pay the same price for the same product, no matter where they are based.', 'woocommerce-quaderno' ) ); ?></td>
            <td><mark class="<?php echo(WC_QD_Integration::$universal_pricing) ?>"><span class="dashicons dashicons-<?php echo(WC_QD_Integration::$universal_pricing) ?>"></span></mark></td>
          </tr>
        </tbody>
    </table>
    <?php
  }

}
