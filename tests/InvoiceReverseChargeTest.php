<?php

use PHPUnit\Framework\TestCase;

class InvoiceReverseChargeTest extends TestCase {

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
      'status'  => 'reverse_charge',
      'notes'   => 'Tax amount subject to reverse charge'
    );

    WC_Test_Registry::$products[16495] = new WC_Test_Product( 16495, 'achat-M414-kit-DIY' );
  }

  private function build_order( $is_vat_exempt ) {
    $order = new WC_Test_Order();

    $order->billing = array(
      'first_name' => 'Arkadiusz',
      'last_name'  => 'Wiech',
      'address_1'  => 'Wilamowice ul. Pieczarkowa 5',
      'address_2'  => 'Gitarek Studio Arkadiusz Wiech',
      'city'       => 'Skoczow',
      'postcode'   => '43-430',
      'country'    => 'PL',
      'email'      => 'buyer@example.test',
      'phone'      => '604060728'
    );
    $order->shipping = $order->billing;

    $order->meta['tax_id'] = 'PL5481301248';
    $order->meta['is_vat_exempt'] = $is_vat_exempt;

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

  private function issue( $order ) {
    $manager = new WC_QD_Invoice_Manager();
    $manager->create_invoice( $order->get_id() );

    $this->assertCount( 1, QuadernoTransaction::$saved, 'no invoice was built' );

    return QuadernoTransaction::$saved[0];
  }

  public function test_keeps_the_buyer_tax_id_when_we_answered_reverse_charge() {
    $order = $this->build_order( 'no' );

    $transaction = $this->issue( $order );

    $this->assertSame( 'PL5481301248', $transaction->params['customer']['tax_id'] );
  }

  public function test_marks_the_invoice_as_reverse_charged_when_we_answered_reverse_charge() {
    $order = $this->build_order( 'no' );

    $transaction = $this->issue( $order );

    $this->assertStringContainsString( 'reverse charge', $transaction->notes );
  }

  public function test_keeps_the_buyer_tax_id_when_woocommerce_kept_the_exempt_flag() {
    $order = $this->build_order( 'yes' );

    $transaction = $this->issue( $order );

    $this->assertSame( 'PL5481301248', $transaction->params['customer']['tax_id'] );
    $this->assertStringContainsString( 'reverse charge', $transaction->notes );
  }

  public function test_strips_the_tax_id_from_a_cross_border_invoice_that_is_taxable() {
    QuadernoTaxRate::$response = (object) array(
      'name'    => 'PTU/VAT',
      'rate'    => 23.0,
      'country' => 'PL',
      'region'  => null,
      'status'  => 'taxable'
    );

    $order = $this->build_order( 'no' );

    $transaction = $this->issue( $order );

    $this->assertSame( '', $transaction->params['customer']['tax_id'] );
    $this->assertStringNotContainsString( 'reverse charge', $transaction->notes );
  }

  public function test_keeps_the_tax_id_on_a_domestic_invoice() {
    QuadernoTaxRate::$response = (object) array(
      'name'    => 'PTU/VAT',
      'rate'    => 23.0,
      'country' => 'PL',
      'region'  => null,
      'status'  => 'taxable'
    );

    WC()->countries->base_country = 'PL';

    $order = $this->build_order( 'no' );

    $transaction = $this->issue( $order );

    $this->assertSame( 'PL5481301248', $transaction->params['customer']['tax_id'] );
  }

  public function test_does_not_ask_the_tax_calculator_about_reverse_charge_without_a_tax_id() {
    $order = $this->build_order( 'no' );
    $order->meta['tax_id'] = '';

    $transaction = $this->issue( $order );

    $this->assertSame( '', $transaction->params['customer']['tax_id'] );
    $this->assertStringNotContainsString( 'reverse charge', $transaction->notes );

    $with_tax_id = array_filter( QuadernoTaxRate::$calls, function( $call ) {
      return ! empty( $call['tax_id'] );
    } );
    $this->assertCount( 0, $with_tax_id );
  }

  public function test_asks_the_tax_calculator_once_per_order() {
    $order = $this->build_order( 'no' );

    $this->issue( $order );

    $with_tax_id = array_filter( QuadernoTaxRate::$calls, function( $call ) {
      return ! empty( $call['tax_id'] );
    } );
    $this->assertCount( 1, $with_tax_id );
  }
}
