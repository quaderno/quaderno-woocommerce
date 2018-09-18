jQuery(document).ready( function ( $ ) {
  'use strict';
	var eu_countries = ['AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE', 'GB'];

	$('#billing_country').change(function() {
	  // show vat number if buyer is in the EU
	  if ( $(this).val() == $('#base_location').val() || $.inArray($(this).val(), eu_countries) == -1 ) {
	    $('#vat_number_field').hide();
	    $('#vat_number').val('');
	  } else {
	    $('#vat_number_field').show();
	  }

    // show tax id field if vendor and buyer are in the same country
	  if ( $('#tax_id_field').length > 0 && $(this).val() == $('#base_location').val() ) {
	    $('#tax_id_field').show();
	  } else {
	    $('#tax_id').val('');
	    $('#tax_id_field').hide();
	  }
	});

	$('#billing_state').change(function () {
	  $('body').trigger('update_checkout');
	});

	$('#billing_postcode').change(function () {
	  $('body').trigger('update_checkout');
	});

	$('#vat_number').change(function() {
		$('body').trigger('update_checkout');
	});

} );