/**
 * Copyright (c) 2017 - Stranger Studios, LLC
 */
jQuery(document).ready(function($){
    "use strict";

	// Assume we are hiding the checkbox to match shipping fields to billing address fields.
	$( '#pmproship_same_billing_address_div' ).hide();

	if ($('#pmpro_billing_address_fields').length && $('#pmpro_billing_address_fields').css('display') !== 'none') {
		$('#pmproship_same_billing_address_div').show();

		// Move the shipping address fields to after the billing address fields.
		// Post 3.1
		$('#pmpro_billing_address_fields').after( $('#pmpro_form_fieldset-pmproship') );
		// Pre 3.1
		$('#pmpro_billing_address_fields').after( $('#pmpro_checkout_box-pmproship') );

		function pmproship_update_shipping_fields() {
			console.log('pmproship_update_shipping_fields');
			// If the "same as billing" checkbox is checked, hide the shipping fields and copy the values. Otherwise, show them.
			if ($('#pmproship_same_billing_address').is(':checked')) {
				// Post 3.1
				$('#pmpro_form_fieldset-pmproship .pmpro_form_field-text, #pmpro_form_fieldset-pmproship .pmpro_form_field-select').each(function (index, element) {
					$(element).hide();
				});

				// Pre 3.1
				$('#pmpro_checkout_box-pmproship .pmpro_checkout-field-text, #pmpro_checkout_box-pmproship .pmpro_checkout-field-select').each(function (index, element) {
					$(element).hide();
				});

				// Copy the billing fields to the shipping fields.
				$('#pmpro_billing_address_fields input, #pmpro_billing_address_fields select').each(function (index, element) {
					// Get the name of the shipping field.
					let shipping_field_name = element.name;

					// Replace the first character with 'pmpro_s' to get the name of the shipping field.
					shipping_field_name = 'pmpro_s' + shipping_field_name.substr(1);

					// Set the value of the shipping field to the value of the billing field.
					$( '#' + shipping_field_name ).val( element.value );
				} );
			} else {
				$('#pmpro_form_fieldset-pmproship .pmpro_form_field-text, #pmpro_form_fieldset-pmproship .pmpro_form_field-select').each(function (index, element) {
					$(element).show();
				});

				$('#pmpro_checkout_box-pmproship .pmpro_checkout-field-text, #pmpro_checkout_box-pmproship .pmpro_checkout-field-select').each(function (index, element) {
					$(element).show();
				});
			}
		}

		// Make sure that the shipping fields are updated when the page loads, when the "same as billing" checkbox is clicked, and when the submit button is clicked.
		pmproship_update_shipping_fields();
		$( '#pmproship_same_billing_address' ).on( 'change', pmproship_update_shipping_fields );
		$( '#pmpro_btn-submit' ).on( 'click', pmproship_update_shipping_fields );
	}
});