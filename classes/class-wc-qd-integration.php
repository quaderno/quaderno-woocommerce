<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_QD_Integration extends WC_Integration {

	public static $api_token = null;
	public static $api_url = null;
	public static $require_tax_id = null;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id                 = 'quaderno';
		$this->method_title       = 'Quaderno';
    $this->method_description = __( 'Simplify tax calculations and invoicing for your WooCommerce store.', 'woocommerce-quaderno' );

		// Load admin form
		$this->init_form_fields();

		// Load settings
		$this->init_settings();

		self::$api_token = $this->get_option( 'api_token' );
		self::$api_url = $this->get_option( 'api_url' );
		self::$require_tax_id = $this->get_option( 'require_tax_id' );

		// Hooks
		add_action( 'plugins_loaded', array( $this, 'init' ) );
		add_action( 'woocommerce_update_options_integration_quaderno', array( $this, 'process_admin_options' ) );

    /* 
    We keep this section for compatibility with previous versions of this plugin 
    As of version 2.7, users can follow this instructions to activate universal pricing: https://support.quaderno.io/article/1208-universal-pricing-in-woocommerce
    */
		if ( version_compare( WC_VERSION, '2.4.7', '>=' ) && $this->get_option( 'universal_pricing', 'no' ) == 'yes' ) {
			add_filter( 'woocommerce_adjust_non_base_location_prices', '__return_false' );
		}

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
				'description' => '<a href="https://quadernoapp.com/users/api-keys/?utm_source=wordpress&utm_campaign=woocommerce" target="_blank">' . __( 'Get your Quaderno private key', 'woocommerce-quaderno' ) . '</a>',
				'type'        => 'text'
			),
			'api_url'  => array(
				'title'       => __( 'API URL', 'woocommerce-quaderno' ),
				'description' => '<a href="https://quadernoapp.com/users/api-keys/?utm_source=wordpress&utm_campaign=woocommerce" target="_blank">' . __( 'Get your Quaderno API URL', 'woocommerce-quaderno' ) . '</a>',
				'type'        => 'text'
			)
		);

		if ( in_array( $base_country, WC_QD_Tax_Id_Field::COUNTRIES ) ) {
			$this->form_fields[ 'require_tax_id' ] = array(
				'title'       => __( 'Require tax ID', 'woocommerce-quaderno' ),
				/* translators: 1: shop's country */ 
				'description' => sprintf(__( 'Check this if tax ID must be required for all sales in %s.', 'woocommerce-quaderno' ), $woocommerce->countries->countries[ $base_country ]),
				'type'        => 'checkbox'
			);
		}
	}

	/**
	 * Settings prompt
	 */
	public function settings_notice() {
    $current_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';

    if ( 'integration' === $current_tab ) {
       return;
    }
    ?>
    <div id="message" class="updated woocommerce-message">
        <p>
            <?php
            printf(
                /* translators: %s: Strong tag for 'Quaderno' */
                wp_kses_post( '<strong>%s</strong> is almost ready &#8211; Please configure your API keys to start creating automatic invoices.' ),
                esc_html__( 'Quaderno', 'woocommerce-quaderno' )
            );
            ?>
        </p>

        <p class="submit">
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=integration&section=quaderno' ) ); ?>"
               class="button-primary">
                <?php esc_html_e( 'Settings', 'woocommerce-quaderno' ); ?>
            </a>
        </p>
    </div>
    <?php
	}
}
