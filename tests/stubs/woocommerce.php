<?php

class WC_Test_Countries
{
  public $base_country = 'FR';
  public $base_postcode = '57160';
  public $base_state = '';
  public $base_city = 'SCY CHAZELLES';
  public $base_address = '124 Voie de la Liberte';

  public function get_base_country()
  {
    return $this->base_country;
  }
  public function get_base_postcode()
  {
    return $this->base_postcode;
  }
  public function get_base_state()
  {
    return $this->base_state;
  }
  public function get_base_city()
  {
    return $this->base_city;
  }
  public function get_base_address()
  {
    return $this->base_address;
  }
  public function get_states($country)
  {
    return array();
  }
}

class WC_Test_Customer
{
  public $is_vat_exempt = false;
  public function set_is_vat_exempt($value)
  {
    $this->is_vat_exempt = (bool) $value;
  }
  public function get_is_vat_exempt()
  {
    return $this->is_vat_exempt;
  }
}

class WC_Test_Session
{
  public $data = array();
  public function set($key, $value) { $this->data[$key] = $value; }
  public function get($key, $default = null) { return $this->data[$key] ?? $default; }
  public function __unset($key) { unset($this->data[$key]); }
}

class WC_Test_Woo
{
  public $countries;
  public $customer;
  public $session;
  public function __construct()
  {
    $this->countries = new WC_Test_Countries();
    $this->customer = new WC_Test_Customer();
    $this->session = new WC_Test_Session();
  }
}

class WC_Test_Logger
{
  public $entries = array();
  public function error($message, $context = array())
  {
    $this->entries[] = array('error', $message, $context);
  }
  public function info($message, $context = array())
  {
    $this->entries[] = array('info', $message, $context);
  }
}

class WC_Tax
{
  public static $rates = array();
  public function find_rates($args = array())
  {
    return self::$rates;
  }
}

class WC_Test_Product
{
  public $id;
  public $sku;
  public $virtual = false;
  public $tax_class = '';
  public $tax_status = 'taxable';
  public $parent_id = 0;

  public function __construct($id, $sku = '')
  {
    $this->id = $id;
    $this->sku = $sku;
  }

  public function get_id()
  {
    return $this->id;
  }
  public function get_sku()
  {
    return $this->sku;
  }
  public function is_virtual()
  {
    return $this->virtual;
  }
  public function get_tax_class()
  {
    return $this->tax_class;
  }
  public function get_tax_status()
  {
    return $this->tax_status;
  }
  public function get_parent_id()
  {
    return $this->parent_id;
  }
}

class WC_Test_Order_Item implements ArrayAccess
{
  public $data = array();
  public $type = 'line_item';
  public $name = '';
  public $quantity = 1;
  public $total_tax = 0;
  public $subtotal = 0;
  public $total = 0;

  public function __construct($attributes = array())
  {
    foreach (array('name', 'quantity', 'type', 'total_tax', 'subtotal', 'total') as $key) {
      if (isset($attributes[$key])) {
        $this->$key = $attributes[$key];
      }
    }
    $this->data['product_id'] = $attributes['product_id'] ?? 0;
    $this->data['variation_id'] = $attributes['variation_id'] ?? 0;
  }

  public function is_type($type)
  {
    return $this->type === $type;
  }
  public function get_name()
  {
    return $this->name;
  }
  public function get_quantity()
  {
    return $this->quantity;
  }
  public function get_product_id()
  {
    return $this->data['product_id'];
  }
  public function get_variation_id()
  {
    return $this->data['variation_id'];
  }
  public function get_total_tax($context = 'view')
  {
    return $this->total_tax;
  }
  public function get_order()
  {
    return null;
  }

  public function offsetExists(mixed $offset): bool
  {
    return isset($this->data[$offset]);
  }
  public function offsetGet(mixed $offset): mixed
  {
    return $this->data[$offset] ?? null;
  }
  public function offsetSet(mixed $offset, mixed $value): void
  {
    $this->data[$offset] = $value;
  }
  public function offsetUnset(mixed $offset): void
  {
    unset($this->data[$offset]);
  }
}

class WC_Test_Order
{
  public $id = 30927;
  public $meta = array();
  public $items = array();
  public $notes = array();
  public $currency = 'EUR';
  public $total = 988.20;
  public $user_id = 2581;
  public $payment_method = 'ppcp-credit-card-gateway';
  public $transaction_id = '9BY30344RW6837319';
  public $date_created = '2026-08-19 21:06:39';

  public $billing = array();
  public $shipping = array();

  public function get_id()
  {
    return $this->id;
  }
  public function get_order_number()
  {
    return (string) $this->id;
  }
  public function get_currency()
  {
    return $this->currency;
  }
  public function get_total($context = 'view')
  {
    return $this->total;
  }
  public function get_user_id()
  {
    return $this->user_id;
  }
  public function get_payment_method()
  {
    return $this->payment_method;
  }
  public function get_transaction_id()
  {
    return $this->transaction_id;
  }
  public function get_date_created()
  {
    return $this->date_created;
  }
  public function get_edit_order_url()
  {
    return 'https://example.test/wp-admin/post.php?post=' . $this->id;
  }
  public function get_customer_ip_address()
  {
    return '185.222.7.226';
  }
  public function get_customer_note()
  {
    return '';
  }

  public function get_meta($key, $single = true)
  {
    return $this->meta[$key] ?? '';
  }
  public function add_meta_data($key, $value)
  {
    $this->meta[$key] = $value;
  }
  public function update_meta_data($key, $value)
  {
    $this->meta[$key] = $value;
  }
  public function save()
  {
    return true;
  }
  public function add_order_note($note)
  {
    $this->notes[] = $note;
  }

  public function get_items($types = array())
  {
    return $this->items;
  }

  public function get_line_subtotal($item, $inc_tax = false)
  {
    return $item->subtotal ?? 0;
  }
  public function get_line_total($item, $inc_tax = false)
  {
    return $item->total ?? 0;
  }

  public function get_billing_first_name()
  {
    return $this->billing['first_name'] ?? '';
  }
  public function get_billing_last_name()
  {
    return $this->billing['last_name'] ?? '';
  }
  public function get_billing_company()
  {
    return $this->billing['company'] ?? '';
  }
  public function get_billing_address_1()
  {
    return $this->billing['address_1'] ?? '';
  }
  public function get_billing_address_2()
  {
    return $this->billing['address_2'] ?? '';
  }
  public function get_billing_city()
  {
    return $this->billing['city'] ?? '';
  }
  public function get_billing_postcode()
  {
    return $this->billing['postcode'] ?? '';
  }
  public function get_billing_state()
  {
    return $this->billing['state'] ?? '';
  }
  public function get_billing_country()
  {
    return $this->billing['country'] ?? '';
  }
  public function get_billing_email()
  {
    return $this->billing['email'] ?? '';
  }
  public function get_billing_phone()
  {
    return $this->billing['phone'] ?? '';
  }

  public function get_shipping_address_1()
  {
    return $this->shipping['address_1'] ?? '';
  }
  public function get_shipping_address_2()
  {
    return $this->shipping['address_2'] ?? '';
  }
  public function get_shipping_city()
  {
    return $this->shipping['city'] ?? '';
  }
  public function get_shipping_postcode()
  {
    return $this->shipping['postcode'] ?? '';
  }
  public function get_shipping_state()
  {
    return $this->shipping['state'] ?? '';
  }
  public function get_shipping_country()
  {
    return $this->shipping['country'] ?? '';
  }
}

class WC_Test_Registry
{
  public static $woo;
  public static $orders = array();
  public static $products = array();
  public static $logger;

  public static function reset()
  {
    global $woocommerce;
    self::$woo = new WC_Test_Woo();
    $woocommerce = self::$woo;
    self::$orders = array();
    self::$products = array();
    self::$logger = new WC_Test_Logger();
    WC_Tax::$rates = array();
  }
}

WC_Test_Registry::reset();

function WC()
{
  return WC_Test_Registry::$woo;
}
function wc_get_logger()
{
  return WC_Test_Registry::$logger;
}
function wc_tax_enabled()
{
  return true;
}
function get_woocommerce_currency()
{
  return 'EUR';
}
function wc_get_order($id)
{
  return WC_Test_Registry::$orders[$id] ?? false;
}
function wc_get_product($id)
{
  return WC_Test_Registry::$products[$id] ?? null;
}
function wc_get_formatted_variation($variation, $flat = false, $include_names = true, $skip_attributes = false)
{
  return 'Capsule option: RM CK12 Capsule Premium, Kit option: DIY Kit';
}
