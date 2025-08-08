<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_QD_Integration extends WC_Integration {

	public static $api_token = null;
	public static $api_url = null;
	public static $update_subscription_tax = null;
	public static $require_tax_id = null;
	public static $universal_pricing = null;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id                 = 'quaderno';
		$this->method_title       = 'Quaderno';
    
    $this->method_description = sprintf(
      /* translators: %s: Link to the Quaderno signup page */
      wp_kses_post( 'Automatically calculate tax rates & create instant tax reports for your WooCommerce store. <br>Note: You need a %s for this extension to work.' ),
      '<a href="' . esc_url( 'https://quadernoapp.com/signup?utm_source=wordpress&utm_campaign=woocommerce' ) . '">' . esc_html__( 'Quaderno account', 'woocommerce-quaderno' ) . '</a>'
    );

		// Load admin form
		$this->init_form_fields();

		// Load settings
		$this->init_settings();

		self::$api_token = $this->get_option( 'api_token' );
		self::$api_url = $this->get_option( 'api_url' );
		self::$update_subscription_tax = $this->get_option( 'update_subscription_tax', 'no' );
		self::$require_tax_id = $this->get_option( 'require_tax_id' );
		self::$universal_pricing = $this->get_option( 'universal_pricing', 'no' );

		// Hooks
		add_action( 'plugins_loaded', array( $this, 'init' ) );
		add_action( 'woocommerce_update_options_integration_quaderno', array( $this, 'process_admin_options' ) );

		if ( version_compare( WC_VERSION, '2.4.7', '>=' ) && self::$universal_pricing == 'yes' ) {
			add_filter( 'woocommerce_adjust_non_base_location_prices', '__return_false' );
		}

		if ( empty( self::$api_token ) || empty( self::$api_url ) ) {
			add_action( 'admin_notices', array( $this, 'settings_notice' ) );
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
	    /* translators: 1: url */ 
			$setting_description = sprintf( __( 'In order for this option to be available you must set the following options on the <a href="%s">Tax Options</a> page:', 'woocommerce-quaderno' ), admin_url( 'admin.php?page=wc-settings&tab=tax' ) );
			
			$setting_description .= '<ol>';
			
			if ( $woocommerce_prices_include_tax == false ){
				$setting_description .= '<li><span>' . sprintf( __( 'Prices entered with tax: <strong>Yes, I will enter prices inclusive of tax</strong>', 'woocommerce-quaderno' ) ) . '</span></li>';
			}	

			if ( $woocommerce_tax_display_shop == false ) {
				$setting_description .= '<li><span>' . sprintf( __( 'Display prices in the shop: <strong>Including tax</strong>', 'woocommerce-quaderno' ) ) . '</span></li>';
			}

			if ( $woocommerce_tax_display_cart == false ) 
			{
				$setting_description .= '<li><span>' . sprintf( __( 'Display prices during cart and checkout: <strong>Including tax</strong>', 'woocommerce-quaderno' ) ) . '</span></li>';
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

		if ( is_plugin_active ( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) ) {
			$this->form_fields[ 'update_subscription_tax' ] = array(
				'title'       => __( 'Update tax in subscriptions', 'woocommerce-quaderno' ),
				'description' => __( 'Check this if you want Quaderno to recalculate tax in your subscriptions if needed.', 'woocommerce-quaderno' ),
				'type'        => 'checkbox'
			);
		}

		if ( version_compare( WC_VERSION, '2.4.7', '>=' ) ) {
			// Get the universal pricing option and add it to the form fields array
			$this->form_fields[ 'universal_pricing' ] = $this->get_universal_pricing_setting();
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
