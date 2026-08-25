<?php

class WP_Test_State
{
  public static $options = array();
  public static $transients = array();
  public static $post_meta = array();
  public static $user_meta = array();
  public static $post_types = array();
  public static $object_terms = array();

  public static function reset()
  {
    self::$options = array('woocommerce_tax_based_on' => 'shipping', 'woocommerce_shipping_tax_class' => 'inherit');
    self::$transients = array();
    self::$post_meta = array();
    self::$user_meta = array();
    self::$post_types = array();
    self::$object_terms = array();
    WP_Test_Hooks::reset();
  }
}

WP_Test_State::reset();

class WP_Test_Hooks
{
  public static $actions = array();
  public static $filters = array();

  public static function reset()
  {
    self::$actions = array();
    self::$filters = array();
  }
}

function add_action($tag, $callback, $priority = 10, $args = 1)
{
  WP_Test_Hooks::$actions[$tag][$priority][] = $callback;
  return true;
}

function add_filter($tag, $callback, $priority = 10, $args = 1)
{
  WP_Test_Hooks::$filters[$tag][$priority][] = $callback;
  return true;
}

function do_action($tag, ...$args)
{
  if (empty(WP_Test_Hooks::$actions[$tag])) {
    return;
  }
  $by_priority = WP_Test_Hooks::$actions[$tag];
  ksort($by_priority);
  foreach ($by_priority as $callbacks) {
    foreach ($callbacks as $callback) {
      call_user_func_array($callback, $args);
    }
  }
}

function apply_filters($tag, $value, ...$args)
{
  if (empty(WP_Test_Hooks::$filters[$tag])) {
    return $value;
  }
  $by_priority = WP_Test_Hooks::$filters[$tag];
  ksort($by_priority);
  foreach ($by_priority as $callbacks) {
    foreach ($callbacks as $callback) {
      $value = call_user_func_array($callback, array_merge(array($value), $args));
    }
  }
  return $value;
}

function has_action($tag, $callback = false)
{
  return ! empty(WP_Test_Hooks::$actions[$tag]);
}

function esc_html__($text, $domain = null)
{
  return $text;
}
function __($text, $domain = null)
{
  return $text;
}
function esc_html($text)
{
  return $text;
}
function sanitize_text_field($text)
{
  return trim((string) $text);
}
function sanitize_title($text)
{
  return strtolower(str_replace(' ', '-', (string) $text));
}
function wp_unslash($value)
{
  return $value;
}
function plugin_dir_path($file)
{
  return dirname($file) . '/';
}
function date_i18n($format, $timestamp)
{
  return date($format, $timestamp);
}
function current_time($format)
{
  return date($format);
}

function get_option($name, $default = false)
{
  return array_key_exists($name, WP_Test_State::$options) ? WP_Test_State::$options[$name] : $default;
}

function get_transient($key)
{
  return array_key_exists($key, WP_Test_State::$transients) ? WP_Test_State::$transients[$key] : false;
}

function set_transient($key, $value, $expiration = 0)
{
  WP_Test_State::$transients[$key] = $value;
  return true;
}

function get_post_type($id)
{
  return WP_Test_State::$post_types[$id] ?? 'product';
}

function metadata_exists($type, $id, $key)
{
  return isset(WP_Test_State::$post_meta[$id][$key]);
}

function get_post_meta($id, $key, $single = false)
{
  return WP_Test_State::$post_meta[$id][$key] ?? '';
}

function update_post_meta($id, $key, $value)
{
  WP_Test_State::$post_meta[$id][$key] = $value;
  return true;
}

function get_user_meta($id, $key, $single = false)
{
  return WP_Test_State::$user_meta[$id][$key] ?? '';
}

function update_user_meta($id, $key, $value)
{
  WP_Test_State::$user_meta[$id][$key] = $value;
  return true;
}

function wp_get_object_terms($id, $taxonomy, $args = array())
{
  return WP_Test_State::$object_terms[$id] ?? array();
}

function get_the_ID()
{
  return 0;
}
