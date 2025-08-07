<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_QD_Integration extends WC_Integration {

	public static $api_token = null;
	public static $api_url = null;
	public static $update_subscription_tax = null;
	public static $require_tax_id = null;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id                 = 'quaderno';
		$this->method_title       = 'Quaderno';
		$this->method_description = sprintf( __( 'Automatically calculate tax rates & create instant tax reports for your WooCommerce store. %sNote: You need a %sQuaderno%s account for this extension to work.', 'woocommerce-quaderno' ), '<br>', '<a href="https://quadernoapp.com/signup?utm_source=wordpress&utm_campaign=verifactu" target="_blank">', '</a>' );

		// Load admin form
		$this->init_form_fields();

		// Load settings
		$this->init_settings();

		self::$api_token = $this->get_option( 'api_token' );
		self::$api_url = $this->get_option( 'api_url' );
		self::$update_subscription_tax = $this->get_option( 'update_subscription_tax', 'no' );
		self::$require_tax_id = $this->get_option( 'require_tax_id' );

		// Hooks
		add_action( 'plugins_loaded', array( $this, 'init' ) );
		add_action( 'woocommerce_update_options_integration_quaderno', array( $this, 'process_admin_options' ) );

		if ( empty( self::$api_token ) || empty( self::$api_url ) ) {
			add_action( 'admin_notices', array( $this, 'settings_notice' ) );
		}
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
				'description' => '<a href="https://quadernoapp.com/users/api-keys/?utm_source=wordpress&utm_campaign=verifactu" target="_blank">' . __( 'Get your Quaderno private key', 'woocommerce-quaderno' ) . '</a>',
				'type'        => 'text'
			),
			'api_url'  => array(
				'title'       => __( 'API URL', 'woocommerce-quaderno' ),
				'description' => '<a href="https://quadernoapp.com/users/api-keys/?utm_source=wordpress&utm_campaign=verifactu" target="_blank">' . __( 'Get your Quaderno API URL', 'woocommerce-quaderno' ) . '</a>',
				'type'        => 'text'
			)
		);

		if ( in_array( $base_country, WC_QD_Tax_Id_Field::COUNTRIES ) ) {
			$this->form_fields[ 'require_tax_id' ] = array(
				'title'       => __( 'Require tax ID', 'woocommerce-quaderno' ),
				'description' => sprintf(__( 'Check this if tax ID must be required for all sales in %s.', 'woocommerce-quaderno' ), $woocommerce->countries->countries[ $base_country ]),
				'type'        => 'checkbox'
			);
		}

		if ( is_plugin_active ( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) ) {
			$this->form_fields[ 'update_subscription_tax' ] = array(
				'title'       => __( 'Update tax in subscriptions', 'woocommerce-quaderno' ),
				'description' => __( 'Check this if you want Quaderno to recalculate tax in your subscriptions if needed.', 'woocommerce-quaderno' ),
				'type'        => 'checkbox'
			);
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
}
