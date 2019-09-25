<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_QD_Integration extends WC_Integration {

	public static $api_token = null;
	public static $api_url = null;
	public static $autosend_invoices = null;
	public static $receipts_threshold = 0;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id                 = 'quaderno';
		$this->method_title       = 'Quaderno';
		$this->method_description = __( 'Automatically calculate tax rates & create instant tax reports for your WooCommerce store. <br>Note: You need a <a href="https://quadernoapp.com/signup?utm_source=wordpress&utm_campaign=woocommerce" target="_blank">Quaderno account</a> for this extension to work.', 'woocommerce-quaderno' );

		// Load admin form
		$this->init_form_fields();

		// Load settings
		$this->init_settings();

		self::$api_token = $this->get_option( 'api_token' );
		self::$api_url  = $this->get_option( 'api_url' );
		self::$autosend_invoices  = $this->get_option( 'autosend_invoices' );
		self::$receipts_threshold = $this->get_option( 'receipts_threshold' );

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
				'description' => '<a href="https://quadernoapp.com/settings/api/?utm_source=wordpress&utm_campaign=woocommerce" target="_blank">' . __( 'Get your Quaderno private key', 'woocommerce-quaderno' ) . '</a>',
				'type'        => 'text'
			),
			'api_url'  => array(
				'title'       => __( 'API URL', 'woocommerce-quaderno' ),
				'description' => '<a href="https://quadernoapp.com/settings/api/?utm_source=wordpress&utm_campaign=woocommerce" target="_blank">' . __( 'Get your Quaderno API URL', 'woocommerce-quaderno' ) . '</a>',
				'type'        => 'text'
			),
			'receipts_threshold' => array(
				'title'       => __( 'Receipts threshold', 'woocommerce-quaderno' ),
				'description' => __( 'Receipts will be issued for orders below this threshold', 'woocommerce-quaderno' ),
				'type'        => 'text'
			),
			'autosend_invoices' => array(
				'title'       => __( 'Delivery', 'woocommerce-quaderno' ),
				'label'       => __( 'Autosend sales receipts and invoices', 'woocommerce-quaderno' ),
				'description' => __( 'Check this to automatically send your sales receipts and invoices', 'woocommerce-quaderno' ),
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

		$post_count = $wpdb->get_var( "SELECT count(*) FROM " . $wpdb->prefix . "postmeta WHERE meta_key = '_quaderno_invoice'" );
		$user_id = get_current_user_id();

		if ( !current_user_can( 'manage_options' ) || get_user_meta( $user_id, 'quaderno_review_dismissed' ) || $post_count < 5 ) {
			return;
		}
		?>
		<div class="notice notice-info">
    	<p><?php _e( "Awesome, you've been using <strong>Quaderno for WooCommerce</strong> for a while.<br>Could you please do me a BIG favor and give a <strong>5-star rating</strong> on WordPress? Just to help us spread the word and boost our motivation.<br><br>Thank you,<br>Carlos Hernandez, Founder", 'woocommerce-quaderno' ); ?>
      </p>
      <ul>
          <li><a href="https://wordpress.org/support/plugin/woocommerce-quaderno/reviews/?filter=5#new-post" target="_blank"><?php _e( 'Ok, you deserve it', 'woocommerce-quaderno' ); ?></a></li>
          <li><a href="?review-dismissed"><?php _e( 'Nope, maybe later', 'woocommerce-quaderno' ); ?></a></li>
          <li><a href="?review-dismissed"><?php _e( 'I already did it', 'woocommerce-quaderno' ); ?></a></li>
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