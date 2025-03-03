document.addEventListener('DOMContentLoaded', function () {
	'use strict';

	var countries = ['AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE', 'GB', 'AU', 'NZ'];

  // Get elements related to billing and tax information
	var billingCountryElement = document.getElementById('billing_country');
	var baseCountryElement = document.getElementById('base_country');
	var taxIdFieldElement = document.getElementById('tax_id_field');
	var taxIdElement = document.getElementById('tax_id');

	function onBillingCountryChange() {
    // If the billing country matches the base country, add a "required" asterisk to the label
		if (baseCountryElement && billingCountryElement.value === baseCountryElement.value) {
			var label = taxIdFieldElement.querySelector('label');
			if (!label.querySelector('abbr')) {
				// Create and append an asterisk indicator for required fields
				var abbr = document.createElement('abbr');
				abbr.className = 'required';
				abbr.title = 'required';
				abbr.innerHTML = '&nbsp;*';
				label.appendChild(abbr);
			}
		} else {
			// Remove the "required" asterisk if the countries do not match
			var existingAbbr = taxIdFieldElement.querySelector('abbr');
			if (existingAbbr) {
				existingAbbr.remove();
			}
		}

    // Show tax ID field if the billing country is in the predefined list, hide otherwise
		if (countries.includes(billingCountryElement.value)) {
			taxIdFieldElement.style.display = '';
		} else {
			taxIdElement.value = '';
			taxIdFieldElement.style.display = 'none';
		}
	}

	billingCountryElement.addEventListener('change', onBillingCountryChange);
  onBillingCountryChange(); // Trigger initially

  // Elements to monitor for input changes to trigger the update checkout event
  var elementsToWatch = document.querySelectorAll('#billing_state, #billing_postcode, #billing_city, #tax_id, #shipping_country, #shipping_state, #shipping_postcode, #shipping_city');
  
  elementsToWatch.forEach(function(element) {
  	element.addEventListener('input', function () {
  		var event = new Event('update_checkout', { bubbles: true });
  		document.body.dispatchEvent(event);
  	});
  });

});