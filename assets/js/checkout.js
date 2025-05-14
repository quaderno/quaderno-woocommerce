jQuery(document).ready( function ( $ ) {
  'use strict';
	var countries = ['AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE', 'GB', 'AU', 'NZ'];

 	$('#billing_country').on('change', function() {
		if ( $('#base_country') && $('#billing_country').val() == $('#base_country').val() ) {
			$('#tax_id_field label').append('<abbr class="required" title="required">&nbsp;*</abbr>');
		} else {
			$('#tax_id_field label abbr').remove();
		}

		if ( $.inArray($(this).val(), countries) >= 0 ) {
			$('#tax_id_field').show();
		} else {
			$('#tax_id').val('');
    	$('#tax_id_field').hide();
		}
	});
	$('#billing_country').trigger('change');
});
