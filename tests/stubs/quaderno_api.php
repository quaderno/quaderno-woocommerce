<?php

class QuadernoTransaction
{
  public static $saved = array();
  public static $save_result = true;

  public $params = array();
  public $items = array();
  public $tags = '';
  public $evidence = array();
  public $notes = '';
  public $id = 'txn_1';
  public $number = 'KAFA002326';
  public $permalink = 'https://example.test/invoice';
  public $contact;

  public function __construct($params = array())
  {
    $this->params = $params;
    $this->contact = (object) array('id' => 45174537);
  }

  public function save()
  {
    self::$saved[] = $this;
    return self::$save_result;
  }

  public function payload()
  {
    return array_merge($this->params, array('items' => $this->items, 'notes' => $this->notes));
  }

  public static function reset()
  {
    self::$saved = array();
    self::$save_result = true;
  }
}

class QuadernoTaxRate
{
  public static $response = null;
  public static $calls = array();

  public static function calculate($params)
  {
    self::$calls[] = $params;
    return self::$response;
  }

  public static function reset()
  {
    self::$response = null;
    self::$calls = array();
  }
}
