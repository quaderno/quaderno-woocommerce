<?php

/**
 * Plugin Name: WooCommerce Quaderno
 * Plugin URI: https://wordpress.org/plugins/woocommerce-quaderno/
 * Description:  Automatically calculate tax rates & create instant tax reports for your WooCommerce store.
 * Version: 2.7.1
 * Author: Quaderno
 * Author URI: https://quaderno.io/integrations/woocommerce/?utm_source=wordpress&utm_campaign=woocommerce
 * WC requires at least: 3.2.0
 * WC tested up to: 9.8.1
 * License: GPL v3
 * Text Domain: woocommerce-quaderno
 * Domain Path: /languages/
 * Requires Plugins: woocommerce
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Prevent data leaks
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Required functions
 */
if ( ! function_exists( 'woothemes_queue_update' ) ) {
	require_once( plugin_dir_path( __FILE__ ) . '/woo-includes/woo-functions.php' );
}

/**
 * Class WooCommerce_Quaderno
 *
 * @since 1.0
 */
class WooCommerce_Quaderno {

	private static $instance = null;

	/**
	 * Get the plugin instance
	 *
	 * @since 1.11.5
	 */
	public static function get_instance() {
 		if ( null == self::$instance ) {
      self::$instance = new self;
    }
 
    return self::$instance;
  }

	/**
	 * Get the plugin file
	 *
	 * @static
	 * @since  1.0
	 * @access public
	 *
	 * @return String
	 */
	public static function get_plugin_file() {
		return __FILE__;
	}

	/**
	 * Constructor
	 *
	 * @since  1.0
	 */
	private function __construct() {
		// Check if WC is activated
		if ( ! WC_Dependencies::woocommerce_active_check() ) {
			add_action( 'admin_notices', array( $this, 'notice_activate_wc' ) );
		} elseif ( version_compare( WC_VERSION, '2.3', '<' ) ) {
			add_action( 'admin_notices', array( $this, 'notice_version_wc' ) );
		} else {
			$this->init();
		}
	}

	/**
	 * Display the notice
	 *
	 * @since  1.0
	 * @access public
	 */
	public function notice_activate_wc() {
    ?>
    <div class="error">
        <p>
            <?php
            // The URL for the plugin install page.
            $install_wc_url = admin_url( 'plugin-install.php?tab=search&s=WooCommerce&plugin-search-input=Search+Plugins' );

            printf(
                /* translators: %s: WooCommerce plugin install URL */
                esc_html__( 'Please install and activate %s in order for the WooCommerce Quaderno extension to work', 'woocommerce-quaderno' ),
                '<a href="' . esc_url( $install_wc_url ) . '">WooCommerce</a>'
            );
            ?>
        </p>
    </div>
    <?php
	}

	/**
	 * Display the notice
	 *
	 * @since  1.0
	 * @access public
	 */
	public function notice_version_wc() {
		?>
		<div class="error">
			<p><?php esc_html_e( 'Please update WooCommerce to version 3.2 or higher in order for the WooCommerce Quaderno extension to work!', 'woocommerce-quaderno' ); ?></p>
		</div>
	<?php
	}

	/**
	 * A static method that will setup the autoloader
	 *
	 * @static
	 * @since  1.0
	 * @access private
	 */
	private static function setup_autoloader() {
		require_once( plugin_dir_path( self::get_plugin_file() ) . '/quaderno/quaderno_load.php' );
		require_once( plugin_dir_path( self::get_plugin_file() ) . '/classes/class-wc-qd-autoloader.php' );
		$autoloader = new WC_QD_Autoloader();
		spl_autoload_register( array( $autoloader, 'load' ) );
	}

	/**
	 * Init the plugin
	 *
	 * @since  1.0
	 * @access private
	 */
	private function init() {

		global $invoice_manager, $credit_manager, $order_manager;

		// Setup the autoloader
		self::setup_autoloader();

		// The Tax ID Field
		$tax_id_field = new WC_QD_Tax_Id_Field();
  	$tax_id_field->setup();

    if ( wc_tax_enabled() ) {
  		// Setup the Checkout VAT stuff
  		$checkout_vat = new WC_QD_Checkout_Manager();
  		$checkout_vat->setup();
    }

		// Setup Invoice manager
		$invoice_manager = new WC_QD_Invoice_Manager();
		$invoice_manager->setup();

		// Setup Credit manager
		$credit_manager = new WC_QD_Credit_Manager();
		$credit_manager->setup();

    // Setup Order manager
    $order_manager = new WC_QD_Order_Manager();
    $order_manager->setup();

    // Setup Subscription manager
    $order_manager = new WC_QD_Subscription_Manager();
    $order_manager->setup();

		// Admin only classes
		if ( is_admin() ) {
			// The Quaderno tax class field
			$tax_code_field = new WC_QD_Tax_Code_Field();
			$tax_code_field->setup();

			// Show alerts
			$alerts = new WC_QD_Alerts();

			// Setup Order manager
			$status_page = new WC_QD_Status();
			$status_page->setup();

			// Show tools
			$tools = new WC_QD_Tools();

			// Filter plugin links
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_links' ) );
		}

		// Add Quaderno integration fields
		add_filter( 'woocommerce_integrations', array( $this, 'load_integration' ) );

		// Enqueue scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}

	/**
	 * Plugin page links
	 *
	 * @since 1.0
	 */
	public function plugin_links( $links ) {
		$plugin_links = array(
			'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=integration&section=quaderno' ) . '">' . __( 'Settings', 'woocommerce-quaderno' ) . '</a>',
		);
		return array_merge( $plugin_links, $links );
	}

	/**
	 * Define integration
	 *
	 * @since 1.0
	 * @param  array $integrations
	 * @return array
	 */
	public function load_integration( $integrations ) {
		$integrations[] = 'WC_QD_Integration';

		return $integrations;
	}

	/**
	 * Enqueue the Quaderno scripts
	 *
	 * @since 1.0
	 */
	public function enqueue_scripts() {
		// do nothing if we are not on the target pages
		if ( !is_checkout() ) {
			return;
		}
		
		wp_enqueue_script(
			'wc_qd_checkout_js',
			plugins_url( '/assets/js/checkout.js', WooCommerce_Quaderno::get_plugin_file(),
			array( 'jquery' ) )
		);
	}

	public function enqueue_admin_scripts( $pagehook ) {
		// do nothing if we are not on the target pages
		if ( 'edit.php' != $pagehook ) {
			return;
		}
	 
		wp_enqueue_script( 'wc_qd_products_js', 
			plugins_url( '/assets/js/products.js', WooCommerce_Quaderno::get_plugin_file(),
			array( 'jquery' ) )
		);
	}
}

// Deactivation code
function woocommerce_quaderno_deactivate() {
	global $wpdb;
 
 	// delete all transients
  $wpdb->query( $wpdb->prepare(
    "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
    $wpdb->esc_like( '_transient_quaderno_tax_' ) . '%'
  ) );

  $wpdb->query( $wpdb->prepare(
    "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
    '%' . $wpdb->esc_like( '_vat_number_' ) . '%'
  ) );
}
register_deactivation_hook( __FILE__, 'woocommerce_quaderno_deactivate' );


// The 'main' function
function __woocommerce_quaderno_main() {
	WooCommerce_Quaderno::get_instance();
}
add_action( 'plugins_loaded', '__woocommerce_quaderno_main' );

// compatibility with HPOS
add_action( 'before_woocommerce_init', function() {
  if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
    \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, false );
  }
});

