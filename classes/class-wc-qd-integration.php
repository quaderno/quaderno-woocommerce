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
		add_action( 'admin_notices', array( $this, 'integration_warnings' ) );

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
	 * Validate settings fields
	 */
	public function validate_api_token_field( $key, $value ) {
		if ( empty( $value ) ) {
			WC_Admin_Settings::add_error(
				__( 'Private key is required.', 'woocommerce-quaderno' )
			);
		}
		return $value;
	}

	/**
	 * Validate API URL field
	 */
	public function validate_api_url_field( $key, $value ) {
		if ( empty( $value ) ) {
			WC_Admin_Settings::add_error(
				__( 'API URL is required.', 'woocommerce-quaderno' )
			);
		}
		return $value;
	}

	/**
	 * Process admin options and validate API credentials
	 */
	public function process_admin_options() {
		$saved = parent::process_admin_options();

		// If settings were saved successfully, validate the API credentials
		if ( $saved ) {
			$api_token = $this->get_option( 'api_token' );
			$api_url = $this->get_option( 'api_url' );

			// Only validate if both API token and URL are provided
			if ( ! empty( $api_token ) && ! empty( $api_url ) ) {
				// Update the static properties so QuadernoRequest can use them
				self::$api_token = $api_token;
				self::$api_url = $api_url;

				// Perform the ping request
				$request = new QuadernoRequest();
				$ping_result = $request->ping();

				if ( ! $ping_result ) {
					// Ping failed - show error message
					WC_Admin_Settings::add_error(
						__( 'Invalid API credentials. Please check your private key and API URL.', 'woocommerce-quaderno' )
					);
				} 
			}
		}

		return $saved;
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

	/**
	 * Show integration warnings
	 */
	public function integration_warnings() {
		// Only show on the integration settings page
		$current_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';
		$current_section = isset( $_GET['section'] ) ? sanitize_key( wp_unslash( $_GET['section'] ) ) : '';

		if ( 'integration' !== $current_tab || 'quaderno' !== $current_section ) {
			return;
		}

		global $woocommerce;

		// Check 1: Checkout block warning
		$checkout_page_id = wc_get_page_id( 'checkout' );
		if ( $checkout_page_id && has_block( 'woocommerce/checkout', $checkout_page_id ) ) {
			?>
			<div class="notice notice-error">
				<p>
					<?php
					esc_html_e( 'The Checkout Block is not fully supported. Please use the Classic Checkout for the best compatibility with Quaderno.', 'woocommerce-quaderno' );
					?>
				</p>
			</div>
			<?php
		}

		// Check 2: Tax calculation not enabled
		if ( get_option( 'woocommerce_calc_taxes' ) == 'no' ) {
			?>
			<div class="notice notice-error">
				<p>
					<?php
					printf(
						/* translators: %s: Link to WooCommerce settings */
						wp_kses_post( __( 'You must enable the tax calculations in <a href="%s">WooCommerce &gt; Settings &gt; General</a>.', 'woocommerce-quaderno' ) ),
						esc_url( admin_url( 'admin.php?page=wc-settings' ) )
					);
					?>
				</p>
			</div>
			<?php
		} elseif ( get_option( 'woocommerce_prices_include_tax' ) == 'yes' ) {
			$base_country = $woocommerce->countries->get_base_country();
			$base_country_in_rates = false;

			// Check 3: Tax-included prices without base country in tax rates
			foreach( WC_TAX::get_rates_for_tax_class('') as $key => $rate ) {
				if ( $rate->tax_rate_country === $base_country ) {
					$base_country_in_rates = true;
					break;
				}
			}

			if ( !$base_country_in_rates ) {
				?>
				<div class="notice notice-error">
					<p>
						<?php
						printf(
							/* translators: %s: Link to tax rates page */
							wp_kses_post( __( 'You must add your base country in the <a href="%s">standard tax rates page</a> if you work with tax included prices.', 'woocommerce-quaderno' ) ),
							esc_url( admin_url( 'admin.php?page=wc-settings&tab=tax&section=standard' ) )
						);
						?>
					</p>
				</div>
				<?php
			}
		}
	}
}
