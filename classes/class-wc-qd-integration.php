<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_QD_Integration extends WC_Integration {

	public static $api_token = null;
	public static $api_url = null;
	public static $show_tax_id = null;
	public static $autosend_invoices = null;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id                 = 'quaderno';
		$this->method_title       = 'Quaderno';
		$this->method_description = __( sprintf( 'Automatically send customizable invoices and receipts with every order in your store. %sNote: You need a %sQuaderno account%s for this extension to work.', '<br>', '<a href="' . WooCommerce_Quaderno::QUADERNO_URL . '/signup" target="_blank">', '</a>' ), 'woocommerce-quaderno' );

		// Load admin form
		$this->init_form_fields();

		// Load settings
		$this->init_settings();

		self::$api_token = $this->get_option( 'api_token' );
		self::$api_url  = $this->get_option( 'api_url' );
		self::$show_tax_id  = $this->get_option( 'show_tax_id' );
		self::$autosend_invoices  = $this->get_option( 'autosend_invoices' );

		// Hooks
		add_action( 'plugins_loaded', array( $this, 'init' ) );
		add_action( 'woocommerce_update_options_integration_quaderno', array( $this, 'process_admin_options' ) );

		if ( empty( self::$api_token ) || empty( self::$api_url ) ) {
			add_action( 'admin_notices', array( $this, 'settings_notice' ) );
		}

		add_action( 'admin_notices', array( $this, 'review_notice' ) );
		add_action( 'admin_init', array( $this, 'review_dismised' ) );
	}
	
	/**
	 * Init integration form fields
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'api_token' => array(
				'title'       => __( 'Private key', 'woocommerce-quaderno' ),
				'description' => __( 'Get this token from your Quaderno account.', 'woocommerce-quaderno' ),
				'type'        => 'text'
			),
			'api_url'  => array(
				'title'       => __( 'API URL', 'woocommerce-quaderno' ),
				'description' => __( 'Get this URL from your Quaderno account.', 'woocommerce-quaderno' ),
				'type'        => 'text'
			),
			'show_tax_id'  	=> array(
				'title'       => __( 'Tax ID', 'woocommerce-quaderno' ),
				'label' 			=> __( 'Ask for Tax ID when the customer is located in my country', 'woocommerce-quaderno' ),
				'description' => __( 'Additional tax number that is mandatory in some countries. This is not the EU VAT number.', 'woocommerce-quaderno' ),
				'type'        => 'checkbox'
			),
			'autosend_invoices' => array(
				'title'       => __( 'Delivery', 'woocommerce-quaderno' ),
				'label'       => __( 'Autosend sales receipts and invoices', 'woocommerce-quaderno' ),
				'description' => __( 'Check this to automatically send your sales receipts and invoices.', 'woocommerce-quaderno' ),
				'type'        => 'checkbox'
			)
		);
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

	public function review_notice() {
		global $wpdb;

		$post_count = $wpdb->get_var( "SELECT count(*) FROM wp_postmeta WHERE meta_key = '_quaderno_invoice'" );
		$user_id = get_current_user_id();

		if ( get_user_meta( $user_id, 'quaderno_review_dismissed' ) || $post_count < 5 ) {
			return;
		}
		?>
		<div class="notice notice-info">
    	<p>
    		Awesome, you've been using <strong>Quaderno for WooCommerce</strong> for a while. 
    		<br>Could you please do me a BIG favor and give a <strong>5-star rating</strong> on WordPress? Just to help us spread the word and boost our motivation.
    		<br><br>Your help is much appreciated. Thank you very much,<br> ~Carlos Hernandez, Founder
    	</p>
        <ul>
            <li><a href="https://wordpress.org/support/plugin/woocommerce-quaderno/reviews/?filter=5#new-post" target="_blank">Ok, you deserve it</a></li>
            <li><a href="?review-dismissed">Nope, maybe later</a></li>
            <li><a href="?review-dismissed">I already did it</a></li>
        </ul>
    </div>
	<?php
	}

	public function review_dismised() {
		$user_id = get_current_user_id();
    if ( isset( $_GET['review-dismissed'] ) ) {
      add_user_meta( $user_id, 'quaderno_review_dismissed', 'true', true );
    }
	}
}