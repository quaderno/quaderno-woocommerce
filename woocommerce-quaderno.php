<?php

/**
 * Plugin Name: WooCommerce Quaderno
 * Plugin URI: https://wordpress.org/plugins/woocommerce-quaderno/
 * Description:  Automatically calculate tax rates & create instant tax reports for your WooCommerce store.
 * Version: 2.3.0
 * Author: Quaderno
 * Author URI: https://quaderno.io/integrations/woocommerce/?utm_source=wordpress&utm_campaign=woocommerce
 * WC requires at least: 3.2.0
 * WC tested up to: 8.8.3
 * License: GPL v3
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
			<p><?php printf( __( 'Please install and activate %sWooCommerce%s in order for the WooCommerce Quaderno extension to work!', 'woocommerce-quaderno' ), '<a href="' . admin_url( 'plugin-install.php?tab=search&s=WooCommerce&plugin-search-input=Search+Plugins' ) . '">', '</a>' ); ?></p>
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
			<p><?php _e( 'Please update WooCommerce to <strong>version 2.2.9 or higher</strong> in order for the WooCommerce Quaderno extension to work!', 'woocommerce-quaderno' ); ?></p>
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

		// Load plugin textdomain
		self::load_textdomain();

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
		if ( is_checkout() ) {
			$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

			wp_enqueue_script(
				'wc_qd_checkout_js',
				plugins_url( '/assets/js/checkout' . $suffix . '.js', WooCommerce_Quaderno::get_plugin_file() ),
				array( 'jquery' )
			);
		}
	}

	public function enqueue_admin_scripts( $pagehook ) {
	 
		// do nothing if we are not on the target pages
		if ( 'edit.php' != $pagehook ) {
			return;
		}
	 
	 	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_enqueue_script( 'wc_qd_products_js', 
			plugins_url( '/assets/js/products' . $suffix . '.js', WooCommerce_Quaderno::get_plugin_file() ), 
			array( 'jquery' ) 
		);
	}
	
	public function load_textdomain() {
		$lang_dir = plugin_dir_path( self::get_plugin_file() ) . '/languages/';
		$locale = apply_filters( 'plugin_locale', get_locale(), 'woocommerce-quaderno' );
		$mofile = sprintf( '%1$s-%2$s.mo', 'woocommerce', $locale );

		/* Setup paths to current locale file */
		$mofile_global = WP_LANG_DIR . '/woocommerce-quaderno/' . $mofile;
		$mofile_local = $lang_dir . $mofile;

		if ( file_exists( $mofile_global ) ) {
			/* Look in global /wp-content/languages/woocommerce-quaderno/ folder */
			load_textdomain( 'woocommerce-quaderno', $mofile_global );
		} elseif ( file_exists( $mofile_local ) ) {
			/* Look in local /wp-content/plugins/woocommerce-quaderno/languages/ folder */
			load_textdomain( 'woocommerce-quaderno', $mofile_local );
		} else {
			/* Load the default language files */
			load_plugin_textdomain( 'woocommerce-quaderno', false, $lang_dir );
		}
	}

}

// Deactivation code
function woocommerce_quaderno_deactivate() {
	global $wpdb;
 
 	// delete all transients
  $sql = 'DELETE FROM ' . $wpdb->options . ' WHERE option_name LIKE "_transient_quaderno_tax_%"';
  $wpdb->query($sql);

  $sql = 'DELETE FROM ' . $wpdb->options . ' WHERE option_name LIKE "%_vat_number_%"';
  $wpdb->query($sql);
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

