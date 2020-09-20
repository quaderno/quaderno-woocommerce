<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_QD_Integration extends WC_Integration {

	public static $api_token = null;
	public static $api_url = null;
	public static $autosend_invoices = null;
	public static $require_tax_id = null;
	public static $universal_pricing = null;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id                 = 'quaderno';
		$this->method_title       = 'Quaderno';
		$this->method_description = sprintf( __( 'Automatically calculate tax rates & create instant tax reports for your WooCommerce store. %sNote: You need a %sQuaderno%s account for this extension to work.', 'woocommerce-quaderno' ), '<br>', '<a href="https://quadernoapp.com/signup?utm_source=wordpress&utm_campaign=woocommerce" target="_blank">', '</a>' );

		// Load admin form
		$this->init_form_fields();

		// Load settings
		$this->init_settings();

		self::$api_token = $this->get_option( 'api_token' );
		self::$api_url = $this->get_option( 'api_url' );
		self::$autosend_invoices = $this->get_option( 'autosend_invoices' );
		self::$require_tax_id = $this->get_option( 'require_tax_id' );
		self::$universal_pricing = $this->get_option( 'universal_pricing', 'no' );

		// Hooks
		add_action( 'plugins_loaded', array( $this, 'init' ) );
		add_action( 'woocommerce_update_options_integration_quaderno', array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_settings_save_integration', array( $this, 'clear_transients' ) );

		if ( version_compare( WC_VERSION, '2.4.7', '>=' ) && self::$universal_pricing == 'yes' ) {
			add_filter( 'woocommerce_adjust_non_base_location_prices', '__return_false' );
		}

		if ( empty( self::$api_token ) || empty( self::$api_url ) ) {
			add_action( 'admin_notices', array( $this, 'settings_notice' ) );
		}

		// Show review notice
		if ( is_super_admin() && ! get_option( 'quaderno_dismiss_review' ) ) {
			add_action( 'admin_notices', array( $this, 'quaderno_review' ) );
			add_action( 'admin_footer', array( $this, 'quaderno_review_script' ) );
			add_action( 'wp_ajax_quaderno_review', array( $this, 'quaderno_dismiss_review' ) );
		}
	}

	/**
	 * Return Quaderno Universal Pricing settings for form fields
	 */
	public function get_universal_pricing_setting() {
		$universal_pricing_available = false;
		
		$woocommerce_prices_include_tax = ( get_option( 'woocommerce_prices_include_tax' ) == 'yes' ? true : false );
		$woocommerce_tax_display_shop   = ( get_option( 'woocommerce_tax_display_shop' ) == 'incl' ? true : false );
		$woocommerce_tax_display_cart   = ( get_option( 'woocommerce_tax_display_cart' ) == 'incl' ? true : false );
		
		// Check that conditions are met for this option to be enabled
		if ( $woocommerce_prices_include_tax && $woocommerce_tax_display_shop && $woocommerce_tax_display_cart ) {
			$universal_pricing_available = true;
		}
		
		if ( $universal_pricing_available ) {
			$setting = array(
				'title'       => __( 'Force universal pricing', 'woocommerce-quaderno' ),
				'description' => __( 'Check this if you want Quaderno to calculate tax in such a way, that the final price is always the same as the price provided.', 'woocommerce-quaderno' ),
				'type'        => 'checkbox',
				'default'     => 'no',
				'disabled'	  => false
			);
		} else {
			// Build the description of what conditions must be met for this option to be enabled
			$setting_description = sprintf( __( 'In order for this option to be available you must set the following options on the %sTax Options%s page:', 'woocommerce-quaderno' ), '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=tax' ) . '">', '</a>' );
			
			$setting_description .= '<ol>';
			
			if ( $woocommerce_prices_include_tax == false ){
				$setting_description .= '<li><span>' . sprintf( __( 'Prices entered with tax: %sYes, I will enter prices inclusive of tax%s', 'woocommerce' ), '<strong>', '</strong>' ) . '</span></li>';
			}	

			if ( $woocommerce_tax_display_shop == false ) {
				$setting_description .= '<li><span>' . sprintf( __( 'Display prices in the shop: %sIncluding tax%s', 'woocommerce' ), '<strong>', '</strong>' ) . '</span></li>';
			}

			if ( $woocommerce_tax_display_cart == false ) 
			{
				$setting_description .= '<li><span>' . sprintf( __( 'Display prices during cart and checkout: %sIncluding tax%s', 'woocommerce' ), '<strong>', '</strong>' ) . '</span></li>';
			}
			
			$setting_description .= '</ol>';

			$setting = array( 
				'title'       => __( 'Force universal pricing', 'woocommerce-quaderno' ),
				'description' => $setting_description,
				'type'        => 'checkbox',
				'default'     => 'no',
				'disabled'	  => true
			);
		}

		return $setting;
	}
	
	/**
	 * Init integration form fields
	 */
	public function init_form_fields() {
    global $woocommerce;
		$base_country = $woocommerce->countries->get_base_country();

		$this->form_fields = array(
			'api_token' => array(
				'title'       => __( 'Private key', 'woocommerce-quaderno' ),
				'description' => '<a href="https://quadernoapp.com/users/api-keys/?utm_source=wordpress&utm_campaign=woocommerce" target="_blank">' . __( 'Get your Quaderno private key', 'woocommerce-quaderno' ) . '</a>',
				'type'        => 'text'
			),
			'api_url'  => array(
				'title'       => __( 'API URL', 'woocommerce-quaderno' ),
				'description' => '<a href="https://quadernoapp.com/users/api-keys/?utm_source=wordpress&utm_campaign=woocommerce" target="_blank">' . __( 'Get your Quaderno API URL', 'woocommerce-quaderno' ) . '</a>',
				'type'        => 'text'
			),
			'autosend_invoices' => array(
				'title'       => __( 'Autosend receipts', 'woocommerce-quaderno' ),
				'description' => __( 'Check this if you want Quaderno to automatically email your receipts.', 'woocommerce-quaderno' ),
				'type'        => 'checkbox'
			)
		);

		if ( in_array( $base_country, WC_QD_Tax_Id_Field::COUNTRIES ) ) {
			$this->form_fields[ 'require_tax_id' ] = array(
				'title'       => __( 'Require tax ID', 'woocommerce-quaderno' ),
				'description' => sprintf(__( 'Check this if tax ID must be required for all sales in %s.', 'woocommerce-quaderno' ), $woocommerce->countries->countries[ $base_country ]),
				'type'        => 'checkbox'
			);
		}

		if ( version_compare( WC_VERSION, '2.4.7', '>=' ) ) {
			// Get the universal pricing option and add it to the form fields array
			$this->form_fields[ 'universal_pricing' ] = $this->get_universal_pricing_setting();
		}

		$this->form_fields[ 'clear_trasients' ] = array(
				'title'       => __( 'Clear tax cache', 'woocommerce-quaderno' ),
				'description' => __( 'Check this if you have updated your tax settings in Quaderno.', 'woocommerce-quaderno' ),
				'type'        => 'checkbox'
			);
	}

	/**
	 * Clear transients
	 */
	public function clear_transients() {
		global $wpdb;

	 	// delete all transients
	 	if ( isset( $_POST['woocommerce_quaderno_clear_trasients'] )) {
		  $sql = 'DELETE FROM ' . $wpdb->options . ' WHERE option_name LIKE "_transient_quaderno_tax_%"';
		  $wpdb->query($sql);
		  $_POST['woocommerce_quaderno_clear_trasients'] = NULL;
	 	}
	}

	/**
	 * Settings prompt
	 */
	public function settings_notice() {
		if ( ! empty( $_GET['tab'] ) && 'integration' === $_GET['tab'] ) {
			return;
		}
		?>
		<div id="message" class="updated woocommerce-message">
			<p><?php _e( '<strong>Quaderno</strong> is almost ready &#8211; Please configure your API keys to start creating automatic invoices.', 'woocommerce-quaderno' ); ?></p>

			<p class="submit"><a
					href="<?php echo admin_url( 'admin.php?page=wc-settings&tab=integration&section=quaderno' ); ?>"
					class="button-primary"><?php _e( 'Settings', 'woocommerce-quaderno' ); ?></a></p>
		</div>
	<?php
	}

	/**
	 * Ask users to leave a review for the plugin on wp.org.
	 */
 	public function quaderno_review() {
		global $wpdb;

		$post_count = $wpdb->get_var( "SELECT count(*) FROM " . $wpdb->prefix . "postmeta WHERE meta_key = '_quaderno_invoice'" );
		$user_id = get_current_user_id();

		if ( $post_count < 5 ) {
			return;
		}
		?>
		<div id="quaderno-review" class="notice notice-info is-dismissible">
    	<p>
    		<?php echo sprintf(__( "We have noticed that you have been using Quaderno for some time. We hope you love it, and we would really appreciate it if you would <a href='%s' target='_blank'>give us a 5 stars rating</a>.", 'woocommerce-quaderno' ), 'https://wordpress.org/support/plugin/woocommerce-quaderno/reviews/?filter=5#new-post'); ?>
      </p>
    </div>
	<?php
	}

	/**
	 * Loads the inline script to dismiss the review notice.
	 */
	public function quaderno_review_script() {
		echo
			"<script>\n" .
			"jQuery(document).on('click', '#quaderno-review .notice-dismiss', function() {\n" .
			"\tvar quaderno_review_data = {\n" .
			"\t\taction: 'quaderno_review',\n" .
			"\t};\n" .
			"\tjQuery.post(ajaxurl, quaderno_review_data, function(response) {\n" .
			"\t\tif (response) {\n" .
			"\t\t\tconsole.log(response);\n" .
			"\t\t}\n" .
			"\t});\n" .
			"});\n" .
			"</script>\n";
	}

	/**
	 * Disables the notice about leaving a review.
	 */
	function quaderno_dismiss_review() {
		update_option( 'quaderno_dismiss_review', true, false );
		wp_die();
	}
}