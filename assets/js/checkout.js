jQuery(document).ready( function ( $ ) {
  'use strict';
	var eu_countries = ['AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE', 'GB'];

	$('#billing_country').change(function() {
		if ( $.inArray($(this).val(), eu_countries) >= 0 ) {
			if ( $(this).val() == $('#base_location').val() ) {
				$('#tax_id_field').show();
				$('#vat_number_field').hide();
	    	$('#vat_number').val('');
			} else {
				$('#vat_number_field').show();
				$('#tax_id').val('');
	    	$('#tax_id_field').hide();
			} 
		}
	});
	$('#billing_country').trigger('change');

	$('#billing_state, #billing_postcode, #billing_city, #vat_number').change(function () {
	  $('body').trigger('update_checkout');
	});

	$('#shipping_country, #shipping_state, #shipping_postcode, #shipping_city').change(function () {
	  $('body').trigger('update_checkout');
	});

} );