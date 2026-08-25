<?php

use PHPUnit\Framework\TestCase;

class CheckoutSequenceTest extends TestCase {

  protected function setUp(): void {
    WP_Test_State::reset();
    WC_Test_Registry::reset();
    QuadernoTransaction::reset();
    QuadernoTaxRate::reset();

    QuadernoTaxRate::$response = (object) array(
      'name'    => null,
      'rate'    => 0,
      'country' => 'PL',
      'region'  => null,
      'status'  => 'reverse_charge'
    );

    WC_Test_Registry::$products[16495] = new WC_Test_Product( 16495, 'achat-M414-kit-DIY' );

    $tax_id_field = new WC_QD_Tax_Id_Field();
    $tax_id_field->setup();

    $invoice_manager = new WC_QD_Invoice_Manager();
    $invoice_manager->setup();
  }

  private function build_order() {
    $order = new WC_Test_Order();

    $order->billing = array(
      'first_name' => 'Arkadiusz',
      'last_name'  => 'Wiech',
      'address_1'  => 'Wilamowice ul. Pieczarkowa 5',
      'city'       => 'Skoczow',
      'postcode'   => '43-430',
      'country'    => 'PL',
      'email'      => 'buyer@example.test'
    );
    $order->shipping = $order->billing;
    $order->meta['is_vat_exempt'] = 'no';

    $item = new WC_Test_Order_Item( array(
      'name'         => 'M414 DIY Microphone Kit',
      'quantity'     => 2,
      'product_id'   => 16494,
      'variation_id' => 16495
    ) );
    $item->subtotal = 1098.00;
    $item->total = 988.20;
    $order->items = array( $item );

    WC_Test_Registry::$orders[ $order->get_id() ] = $order;

    return $order;
  }

  public function test_the_handler_is_registered_on_order_creation() {
    $this->assertTrue( has_action( 'woocommerce_new_order' ) );
  }

  public function test_a_paypal_checkout_that_never_posts_our_field_still_produces_a_reverse_charged_invoice() {
    // the buyer's tax ID reaches us only through a tax calculation, as it did on order 30927
    WC_QD_Calculate_Tax::calculate( 'standard', 'good', 988.20, 'EUR', 'PL', '', '43-430', 'Skoczow', '', 'PL5481301248' );

    $order = $this->build_order();

    do_action( 'woocommerce_new_order', $order->get_id() );
    do_action( 'woocommerce_payment_complete', $order->get_id() );

    $this->assertCount( 1, QuadernoTransaction::$saved );

    $transaction = QuadernoTransaction::$saved[0];
    $this->assertSame( 'PL5481301248', $transaction->params['customer']['tax_id'] );
    $this->assertStringContainsString( 'reverse charge', $transaction->notes );
    $this->assertSame( 'PL5481301248', $order->get_meta( 'tax_id' ) );
  }

  public function test_an_order_completed_later_still_carries_the_tax_id() {
    WC_QD_Calculate_Tax::calculate( 'standard', 'good', 988.20, 'EUR', 'PL', '', '43-430', 'Skoczow', '', 'PL5481301248' );

    $order = $this->build_order();

    do_action( 'woocommerce_new_order', $order->get_id() );

    // the session is gone by the time the order is marked completed
    WC()->session = new WC_Test_Session();

    do_action( 'woocommerce_order_status_completed', $order->get_id() );

    $transaction = QuadernoTransaction::$saved[0];
    $this->assertSame( 'PL5481301248', $transaction->params['customer']['tax_id'] );
  }

  public function test_a_checkout_with_no_tax_id_produces_a_taxed_invoice() {
    QuadernoTaxRate::$response = (object) array(
      'name'    => 'PTU/VAT',
      'rate'    => 23.0,
      'country' => 'PL',
      'region'  => null,
      'status'  => 'taxable'
    );

    WC_QD_Calculate_Tax::calculate( 'standard', 'good', 988.20, 'EUR', 'PL', '', '43-430', 'Skoczow', '', '' );

    $order = $this->build_order();

    do_action( 'woocommerce_new_order', $order->get_id() );
    do_action( 'woocommerce_payment_complete', $order->get_id() );

    $transaction = QuadernoTransaction::$saved[0];
    $this->assertSame( '', $transaction->params['customer']['tax_id'] );
    $this->assertStringNotContainsString( 'reverse charge', $transaction->notes );
    $this->assertSame( 23.0, $transaction->items[0]['tax']->rate );
  }
}
