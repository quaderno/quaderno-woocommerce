<?php

use PHPUnit\Framework\TestCase;

class TaxIdFromSessionTest extends TestCase {

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

  public function test_writes_the_checkout_tax_id_onto_an_order_the_checkout_did_not_touch() {
    WC_QD_Calculate_Tax::calculate( 'standard', 'good', 988.20, 'EUR', 'PL', '', '43-430', 'Skoczow', '', 'PL5481301248' );

    $order = $this->build_order();

    $field = new WC_QD_Tax_Id_Field();
    $field->save_tax_id_from_session( $order->get_id() );

    $this->assertSame( 'PL5481301248', $order->get_meta( 'tax_id' ) );
  }

  public function test_the_invoice_keeps_a_tax_id_that_only_the_session_knows_about() {
    WC_QD_Calculate_Tax::calculate( 'standard', 'good', 988.20, 'EUR', 'PL', '', '43-430', 'Skoczow', '', 'PL5481301248' );

    $order = $this->build_order();

    $field = new WC_QD_Tax_Id_Field();
    $field->save_tax_id_from_session( $order->get_id() );

    $manager = new WC_QD_Invoice_Manager();
    $manager->create_invoice( $order->get_id() );

    $transaction = QuadernoTransaction::$saved[0];

    $this->assertSame( 'PL5481301248', $transaction->params['customer']['tax_id'] );
    $this->assertStringContainsString( 'reverse charge', $transaction->notes );
  }

  public function test_does_not_overwrite_a_tax_id_the_checkout_already_saved() {
    WC_QD_Calculate_Tax::calculate( 'standard', 'good', 988.20, 'EUR', 'PL', '', '43-430', 'Skoczow', '', 'PL5481301248' );

    $order = $this->build_order();
    $order->meta['tax_id'] = 'PL9999999999';

    $field = new WC_QD_Tax_Id_Field();
    $field->save_tax_id_from_session( $order->get_id() );

    $this->assertSame( 'PL9999999999', $order->get_meta( 'tax_id' ) );
  }

  public function test_ignores_a_session_tax_id_from_a_different_country() {
    WC_QD_Calculate_Tax::calculate( 'standard', 'good', 100.00, 'EUR', 'DE', '', '10115', 'Berlin', '', 'DE123456789' );

    $order = $this->build_order();

    $field = new WC_QD_Tax_Id_Field();
    $field->save_tax_id_from_session( $order->get_id() );

    $this->assertSame( '', $order->get_meta( 'tax_id' ) );
  }

  public function test_does_nothing_when_no_tax_id_was_entered() {
    WC_QD_Calculate_Tax::calculate( 'standard', 'good', 988.20, 'EUR', 'PL', '', '43-430', 'Skoczow', '', '' );

    $order = $this->build_order();

    $field = new WC_QD_Tax_Id_Field();
    $field->save_tax_id_from_session( $order->get_id() );

    $this->assertSame( '', $order->get_meta( 'tax_id' ) );
  }
}
