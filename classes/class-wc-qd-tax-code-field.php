<?php

if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}

class WC_QD_Tax_Code_Field {

  const TAX_CODES = array(
    '' => '– Not applicable –',
    'eservice' => 'e-Service',
    'ebook' => 'e-Book',
    'saas' => 'SaaS',
    'consulting' => 'Consulting',
    'standard' => 'Standard',
    'reduced' => 'Reduced',
    'exempt' => 'Tax-exempt'
  );

  const DIGITAL_TAX_CODES = array('eservice', 'ebook', 'saas');

  /**
   * The setup method
   */
  public function setup() {
    // Product data metabox
    add_action( 'woocommerce_product_options_tax', array( $this, 'quaderno_product_options_tax' ) );
    add_action( 'woocommerce_process_product_meta', array( $this, 'quaderno_save_fields' ), 10, 2 );

    // Quick edition hooks
    add_action( 'woocommerce_product_quick_edit_end', array( $this, 'quaderno_product_quick_edit' ), 10, 2);
    add_action( 'woocommerce_product_quick_edit_save', array( $this, 'quaderno_product_quick_edit_save'), 10, 1);
    add_action( 'manage_product_posts_custom_column', array( $this, 'quaderno_populate_tax_code_columns'), 10, 2);
  } 

  /**
   * Set the tax code meta for products that were created with version 1.x
   */
  public function init_tax_code_meta() {
    $product_id = get_the_ID();
    if ( !metadata_exists('post', $product_id, '_quaderno_tax_code' ) ) {
      $product = wc_get_product( $product_id );

      if ( 'none' === $product->get_tax_status() ) {
        update_post_meta( $product_id, '_quaderno_tax_code', 'exempt' );
      } elseif ( 'yes' === get_post_meta( $product_id, '_ebook', true ) ) {
        update_post_meta( $product_id, '_quaderno_tax_code', 'ebook' );
      } elseif ( $product->is_virtual() ) {
        update_post_meta( $product_id, '_quaderno_tax_code', 'eservice' );
      }
    }
  } 

  /**
   * Show the Quaderno tax code field in the product metadata box
   */
  public function quaderno_product_options_tax(){

    // compatibility with version 1.x
    self::init_tax_code_meta();
    
    echo '<div class="options_group">';

    woocommerce_wp_select(
      array(
        'id'          => '_quaderno_tax_code',
        'value'       => get_post_meta( get_the_ID(), '_quaderno_tax_code', true ),
        'label'       => __( 'Quaderno tax code', 'woocommerce-quaderno' ),
        'options'     => self::TAX_CODES,
        'desc_tip' => true,
        'description' => 'Select an option if you want Quaderno to calculate taxes for this product.'
      )
    );

    echo '</div>';
  }

  /**
   * Save the Quaderno tax code in the product metadata box
   */
  public function quaderno_save_fields( $id, $post ) {
    if ( isset( $_POST['_quaderno_tax_code'] ) ) {
        $tax_code = sanitize_text_field( wp_unslash( $_POST['_quaderno_tax_code'] ) );
        update_post_meta( $id, '_quaderno_tax_code', $tax_code );
    }
  }

  /**
   * Show the Quaderno tax code field in the quick edition box
   */
  public function quaderno_product_quick_edit() {
    ?>
    <br class="clear" />
    <label class="alignleft">
      <span class="title"><?php esc_html_e('Quaderno tax code', 'woocommerce-quaderno' ); ?></span>
      <span class="input-text-wrap">
        <select class="quaderno_tax_code" name="_quaderno_tax_code">
        <?php
          foreach( self::TAX_CODES as $key => $value ) {
            printf( '<option value="%s">%s</option>', esc_attr( $key ), esc_html( $value ) );
          }
        ?>
        </select>
      </span>
    </label>
    <br class="clear" />
    <?php
  }

  /**
   * Save the Quaderno tax code in the quick edition box
   */
  public function quaderno_product_quick_edit_save( $product ) {
    $product_id = $product->get_id();

    if ( isset( $_REQUEST['_quaderno_tax_code'] ) ) {
      $tax_code = sanitize_text_field( wp_unslash( $_REQUEST['_quaderno_tax_code'] ) );
      update_post_meta( $product_id, '_quaderno_tax_code', $tax_code );
    }
  }

  /*
  * Populate the tax code column in the products list
  */
  public function quaderno_populate_tax_code_columns( $column_name, $post_id ) {
 
    switch( $column_name ) :
      case 'name': {
        ?>

        <div class="hidden quaderno_tax_code_inline" id="quaderno_tax_code_inline_<?php echo esc_attr( $post_id ); ?>">
          <div class="quaderno_tax_code_content">
            <?php echo esc_html( get_post_meta( $post_id, '_quaderno_tax_code', true ) ); ?>
          </div>
        </div>

      <?php
        break;
      }
    endswitch;
 
  }
}
