<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Keeps the tax recorded at checkout when an order's totals are recalculated outside the checkout.
 *
 * @since 2.7.18
 */
class WC_QD_Order_Tax_Guard {

	/**
	 * @var array Recovered rates, keyed by order item id then by rate id
	 */
	private $item_rates = array();

	/**
	 * @var array Rate labels, keyed by rate id
	 */
	private $labels = array();

	/**
	 * @var array Rate codes, keyed by rate id
	 */
	private $codes = array();

	/**
	 * Setup the hooks and filters
	 */
	public function setup() {
		add_action( 'woocommerce_before_save_order_items', array( $this, 'start_for_saved_items' ), 1, 2 );
		add_action( 'woocommerce_order_before_calculate_taxes', array( $this, 'start_for_taxes' ), 1, 2 );
		add_action( 'woocommerce_order_before_calculate_totals', array( $this, 'start' ), 1, 2 );
		add_action( 'woocommerce_order_after_calculate_totals', array( $this, 'stop' ), 999, 2 );
		add_action( 'shutdown', array( $this, 'stop' ), 1 );
	}

	/**
	 * Saving order items recalculates before anything else on the admin path, so engage there too
	 *
	 * @param int $order_id
	 * @param array $items
	 */
	public function start_for_saved_items( $order_id, $items ) {
		$order = wc_get_order( $order_id );

		if ( $order ) {
			$this->start( true, $order );
		}
	}

	/**
	 * The admin recalculation calls calculate_taxes() on its own, ahead of any call to calculate_totals()
	 *
	 * @param array $args
	 * @param WC_Abstract_Order $order
	 */
	public function start_for_taxes( $args, $order ) {
		$this->start( true, $order );
	}

	/**
	 * Remember the rates recorded on the order for the duration of the recalculation
	 *
	 * @param bool $and_taxes
	 * @param WC_Abstract_Order $order
	 */
	public function start( $and_taxes, $order ) {
		if ( ! $and_taxes || ! $order instanceof WC_Abstract_Order ) {
			return;
		}

		$this->reset();
		$this->recover_rates( $order );

		if ( empty( $this->item_rates ) ) {
			return;
		}

		add_action( 'woocommerce_order_item_after_calculate_taxes', array( $this, 'restore_item_taxes' ), 999, 2 );
		add_action( 'woocommerce_order_item_shipping_after_calculate_taxes', array( $this, 'restore_item_taxes' ), 999, 2 );
		add_filter( 'woocommerce_rate_label', array( $this, 'override_rate_label' ), 999, 2 );
		add_filter( 'woocommerce_rate_code', array( $this, 'override_rate_code' ), 999, 2 );
	}

	/**
	 * Stop overriding once the recalculation is done
	 *
	 * @param bool $and_taxes
	 * @param WC_Abstract_Order $order
	 */
	public function stop( $and_taxes = true, $order = null ) {
		remove_action( 'woocommerce_order_item_after_calculate_taxes', array( $this, 'restore_item_taxes' ), 999 );
		remove_action( 'woocommerce_order_item_shipping_after_calculate_taxes', array( $this, 'restore_item_taxes' ), 999 );
		remove_filter( 'woocommerce_rate_label', array( $this, 'override_rate_label' ), 999 );
		remove_filter( 'woocommerce_rate_code', array( $this, 'override_rate_code' ), 999 );

		$this->reset();
	}

	/**
	 * Work out, per item, which of its rates WooCommerce can no longer resolve and at what rate they were charged
	 *
	 * @param WC_Abstract_Order $order
	 */
	private function recover_rates( $order ) {
		$recorded = array();

		foreach ( $order->get_items( 'tax' ) as $tax_item ) {
			$rate_id = (string) $tax_item->get_rate_id();
			$rate    = $this->rate_from_code( $tax_item->get_rate_code() );

			if ( '' === $rate_id || is_null( $rate ) ) {
				continue;
			}

			$recorded[ $rate_id ] = array(
				'rate'     => $rate,
				'label'    => $tax_item->get_label(),
				'shipping' => 'yes',
				'compound' => $tax_item->is_compound() ? 'yes' : 'no',
			);

			$this->labels[ $rate_id ] = $tax_item->get_label();
			$this->codes[ $rate_id ]  = $tax_item->get_rate_code();
		}

		if ( empty( $recorded ) ) {
			return;
		}

		$resolvable = $this->resolvable_rate_ids( array_keys( $recorded ) );

		foreach ( $order->get_items( array( 'line_item', 'fee', 'shipping' ) ) as $item_id => $item ) {
			$taxes = $item->get_taxes();
			$mine  = array();

			foreach ( array_keys( (array) $taxes['total'] ) as $rate_id ) {
				$rate_id = (string) $rate_id;

				if ( isset( $recorded[ $rate_id ] ) && ! in_array( $rate_id, $resolvable, true ) ) {
					$mine[ $rate_id ] = $recorded[ $rate_id ];
				}
			}

			if ( ! empty( $mine ) ) {
				$this->item_rates[ $item_id ] = $mine;
			}
		}
	}

	/**
	 * Put back the rates WooCommerce could not resolve, leaving anything it did resolve alone
	 *
	 * @param WC_Order_Item $item
	 * @param array $calculate_tax_for
	 */
	public function restore_item_taxes( $item, $calculate_tax_for ) {
		$item_id = $item->get_id();

		if ( empty( $this->item_rates[ $item_id ] ) ) {
			return;
		}

		if ( is_callable( array( $item, 'get_tax_status' ) ) && 'taxable' !== $item->get_tax_status() ) {
			return;
		}

		$taxes = $item->get_taxes();

		// WooCommerce resolved a rate of its own for this item, so its answer stands
		if ( ! empty( array_filter( (array) $taxes['total'] ) ) ) {
			return;
		}

		$rates = $this->item_rates[ $item_id ];
		$total = WC_Tax::calc_tax( $item->get_total(), $rates, false );

		if ( is_callable( array( $item, 'get_subtotal' ) ) ) {
			$item->set_taxes( array( 'total' => $total, 'subtotal' => WC_Tax::calc_tax( $item->get_subtotal(), $rates, false ) ) );
			return;
		}

		$item->set_taxes( array( 'total' => $total ) );
	}

	/**
	 * The rate the plugin wrote into the rate code at checkout, as in "SALES TAX|6.5000"
	 *
	 * @param String $rate_code
	 *
	 * @return float|null
	 */
	private function rate_from_code( $rate_code ) {
		if ( false === strpos( (string) $rate_code, '|' ) ) {
			return null;
		}

		$rate = substr( strrchr( $rate_code, '|' ), 1 );

		return is_numeric( $rate ) ? (float) $rate : null;
	}

	/**
	 * Which of the given rate ids WooCommerce can still resolve by itself
	 *
	 * @param array $rate_ids
	 *
	 * @return array
	 */
	private function resolvable_rate_ids( $rate_ids ) {
		global $wpdb;

		$rate_ids = array_filter( array_map( 'strval', $rate_ids ) );

		if ( empty( $rate_ids ) ) {
			return array();
		}

		$placeholders = implode( ',', array_fill( 0, count( $rate_ids ), '%s' ) );

		return array_map( 'strval', $wpdb->get_col( $wpdb->prepare(
			"SELECT tax_rate_id FROM {$wpdb->prefix}woocommerce_tax_rates WHERE tax_rate_id IN ({$placeholders})", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$rate_ids
		) ) );
	}

	/**
	 * Keep the label the order was issued with
	 *
	 * @param String $rate_name
	 * @param String $key
	 *
	 * @return String
	 */
	public function override_rate_label( $rate_name, $key ) {
		if ( ! empty( $this->labels[ (string) $key ] ) ) {
			return $this->labels[ (string) $key ];
		}

		return $rate_name;
	}

	/**
	 * Keep the rate code, which is where the rate itself survives between recalculations
	 *
	 * @param String $code_string
	 * @param String $key
	 *
	 * @return String
	 */
	public function override_rate_code( $code_string, $key ) {
		if ( ! empty( $this->codes[ (string) $key ] ) ) {
			return $this->codes[ (string) $key ];
		}

		return $code_string;
	}

	/**
	 * Forget everything gathered for the previous order
	 */
	private function reset() {
		$this->item_rates = array();
		$this->labels     = array();
		$this->codes      = array();
	}

}
