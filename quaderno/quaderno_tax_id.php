<?php
/**
 * Quaderno Tax
 *
 * @package   Quaderno PHP
 * @author    Quaderno <support@quaderno.io>
 * @copyright Copyright (c) 2021, Quaderno
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class QuadernoTaxId extends QuadernoModel {
    

    public static function validate($tax_id, $country) {
        // remove non-word characters from tax ID
        $tax_id = preg_replace('/\W/', '', $tax_id);
        
        // get the country code from the number if it's empty
        if ( empty($country) ) {
            $country = substr( $tax_id, 0, 2 );
        }
        
        $params = array(
            'tax_id' => $tax_id,
            'country' => $country
        );
        
        $slug = 'quaderno_vat_number_' . md5( implode( $params ) );
        
        if ( false === ( $valid = get_transient( $slug ) ) ) {
            $valid = QuadernoTaxId::validate_impl( $params );
            // Cache the result, unless the tax ID validation service was down.
            if ( !is_null($valid) ) {
                set_transient( $slug, (int)$valid, 4 * WEEK_IN_SECONDS );
            }
        }
        return $valid;
    }
    
    private static function validate_impl($params) {
        $request = new QuadernoRequest();
        $request->validate('tax_ids', $params);
        $response = $request->get_response_body();
        return $response->valid;
    }
    
}
?>