/* eslint-disable prettier/prettier */
!(function ($) {
	'use strict';

	function showModal() {
		$('#bdthemes-templates-builder-modal').show();
	}

	function hideModal() {
		$('#bdthemes-templates-builder-modal').hide();
	}

	function resetModalForm() {
		$('#bdthemes-templates-builder-modal form')[0].reset();
		$('#bdthemes-templates-builder-modal form .template_id').val('');
	}

	function setSubmitBtn(string) {
		$('#bdthemes-templates-builder-modal form .bdt-modal-submit-btn').val(
			string,
		);
	}

	function setError($this) {
		$this.addClass('input-error');
	}

	function removeError($this) {
		$('.input-error').removeClass('input-error');
	}

	function showUltimateStoreKitPopup(pageName) {
		// Create popup HTML
		var popupHtml = '<div id="bdt-ultimate-store-kit-popup" style="' +
			'position: fixed;' +
			'top: 0;' +
			'left: 0;' +
			'width: 100%;' +
			'height: 100%;' +
			'background: rgba(0,0,0,0.8);' +
			'z-index: 99999;' +
			'display: flex;' +
			'align-items: center;' +
			'justify-content: center;' +
		'">' +
			'<div style="' +
				'background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);' +
				'color: white;' +
				'padding: 40px;' +
				'border-radius: 15px;' +
				'max-width: 500px;' +
				'width: 90%;' +
				'text-align: center;' +
				'box-shadow: 0 20px 40px rgba(0,0,0,0.3);' +
				'position: relative;' +
			'">' +
				'<button id="bdt-popup-close" style="' +
					'position: absolute;' +
					'top: 15px;' +
					'right: 15px;' +
					'background: rgba(255,255,255,0.2);' +
					'border: none;' +
					'color: white;' +
					'width: 30px;' +
					'height: 30px;' +
					'border-radius: 50%;' +
					'cursor: pointer;' +
					'font-size: 18px;' +
				'">×</button>' +
				'<h2 style="margin: 0 0 20px 0; font-size: 28px;">🚀 Ultimate Store Kit</h2>' +
				'<h3 style="margin: 0 0 15px 0; color: #ffd700; font-size: 22px;">WooCommerce Dedicated Plugin</h3>' +
				'<p style="font-size: 16px; margin-bottom: 25px; line-height: 1.6;">' +
					'For an enhanced WooCommerce experience, we highly recommend using the dedicated <strong>Ultimate Store Kit</strong> alongside this plugin.' +
				'</p>' +
				'<div style="' +
					'background: rgba(255,255,255,0.1);' +
					'padding: 20px;' +
					'border-radius: 10px;' +
					'margin: 20px 0;' +
				'">' +
					'<p style="margin: 0 0 15px 0; font-size: 14px;">' +
						'The most powerful WooCommerce page builder for Elementor with 80+ widgets, advanced cart & checkout customization, and premium WooCommerce features.' +
					'</p>' +
					'<div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap; margin-top: 20px;">' +
						'<a href="https://storekit.pro/" target="_blank" style="' +
							'background: #ffd700;' +
							'color: #333;' +
							'padding: 12px 25px;' +
							'text-decoration: none;' +
							'border-radius: 25px;' +
							'font-weight: bold;' +
							'display: inline-block;' +
							'transition: all 0.3s ease;' +
						'">Try Store Kit</a>' +
					'</div>' +
				'</div>' +
				'<p style="font-size: 12px; opacity: 0.9; margin: 15px 0 0 0;">' +
					'✨ Includes 80+ WooCommerce widgets • 🎨 Advanced page builder • 🛒 Cart & Checkout customization' +
				'</p>' +
			'</div>' +
		'</div>';
		
		// Add popup to body
		$('body').append(popupHtml);
		
		// Close popup handlers
		$('#bdt-popup-close, #bdt-ultimate-store-kit-popup').on('click', function(e) {
			if (e.target === this) {
				$('#bdt-ultimate-store-kit-popup').remove();
			}
		});
		
		// Close on escape key
		$(document).on('keyup.ultimateStoreKitPopup', function(e) {
			if (e.keyCode === 27) { // Escape key
				$('#bdt-ultimate-store-kit-popup').remove();
				$(document).off('keyup.ultimateStoreKitPopup');
			}
		});
	}

	function handleUltimateStoreKitPromotion($selectElement) {
		// WooCommerce promotional page options
		var promotionalOptions = [
			'product|cart',
			'product|checkout', 
			'product|myaccount',
			'product|thankyou'
		];
		
		var selectedValue = $selectElement.val();
		
		// Hide any existing promotional message
		$('.bdt-ultimate-store-kit-promotion').remove();
		
		// Check if selected option is a promotional WooCommerce page
		if (promotionalOptions.includes(selectedValue)) {
			var pageType = selectedValue.split('|')[1];
			var pageNames = {
				'cart': 'Cart Page',
				'checkout': 'Checkout Page',
				'myaccount': 'My Account Page', 
				'thankyou': 'Thank You Page'
			};
			
			var pageName = pageNames[pageType] || pageType.charAt(0).toUpperCase() + pageType.slice(1);
			
			// Show promotional popup
			showUltimateStoreKitPopup(pageName);
			
			// Reset the select dropdown to previous state
			$selectElement.val('');
		} else {
			// Re-enable fields if not a promotional option
			$('#template_name').prop('disabled', false);
			$('.bdt-modal-submit-btn').prop('disabled', false);
			
			// Only clear template name if we're creating a new template (not editing)
			if ($('.template_id').val() === '') {
				$('#template_name').val('');
				$('.bdt-modal-submit-btn').val('Create Template');
			}
		}
	}

	$(document).on(
		'click',
		'#bdthemes-templates-builder-modal .bdt-modal-close-button',
		function (e) {
			hideModal();
		},
	);

	$(document).on(
		'click',
		'body.post-type-bdt-template-builder a.page-title-action',
		function (e) {
			e.preventDefault();
			resetModalForm();
			setSubmitBtn('Create Template');
			showModal();
		},
	);

	$(document).on(
		'submit',
		'#bdthemes-templates-builder-modal form',
		function (e) {
			e.preventDefault();
			var $serialized = $(this).serialize();
			removeError();

			$.ajax({
				url: ajaxurl,
				dataType: 'json',
				method: 'post',
				cache: false,
				data: {
					action: 'bdthemes_builder_create_template',
					data: $serialized,
				},
				success: function (response) {
					window.location.href = response.data.redirect;
				},
				error: function (errorThrown) {
					if (errorThrown.status == 422) {
						$.each(
							errorThrown.responseJSON.data.errors_arr,
							function (index, value) {
								setError($('#bdthemes-templates-builder-modal #' + index));
							},
						);
					}
				},
			});
		},
	);

	$(document).on(
		'click',
		'body.post-type-bdt-template-builder .row-actions .bdt-edit-action a',
		function (e) {
			e.preventDefault();
			removeError();
			resetModalForm();
			setSubmitBtn('Update Template');

			var templateId = 0;
			var parentColumn = $(this).parents(".column-title");


			$.ajax({
				url: ajaxurl,
				dataType: 'json',
				method: 'post',
				data: {
					action: 'bdthemes_builder_get_edit_template',
					template_id: $(this).data('id'),
					nonce: window.ElementPackConfigBuilder.nonce,
				},
				success: function (response) {
					if (response.success) {
						$('#bdthemes-templates-builder-modal form .template_id')
							.val(response.data.id)
							.change();
						$('#bdthemes-templates-builder-modal form #template_name')
							.val(response.data.name)
							.change();
						$('#bdthemes-templates-builder-modal form #template_type')
							.val(response.data.type)
							.change();
						$('#bdthemes-templates-builder-modal form #template_status')
							.val(response.data.status)
							.change();

						// if #template_type is themes|header or themes|footer then show the .bdt-header-footer-option-container
						if (response.data.type === 'themes|header' || response.data.type === 'themes|footer') {
							$('.bdt-header-footer-option-container').show();
							$('.bdt-template-modalinput-condition_a').val(response.data.condition_a).change();
							$('.bdt-template-modalinput-condition_singular').val(response.data.condition_singular).change();
						}
					}

					templateId = parentColumn.find(".hidden").attr("id").split("_")[1];

					var singularIdInput = $(".bdt-template-modalinput-condition_singular_id");
					$.ajax({
						url: window.ElementPackConfigBuilder.resturl + 'get-singular-list',
						dataType: "json",
						data: { ids: String(response.data.condition_singular_id) || "" },
					}).then(function (response) {
						if (response !== null && response.results.length > 0) {
							singularIdInput.html(" ");
							$.each(response.results, function (index, item) {
								var option = new Option(item.text, item.id, true, true);
								singularIdInput.append(option).trigger("change");
							});
							// singularIdInput.trigger({ type: "select2:select", params: { data: response } });
						}
					});

					showModal();
				},
				error: function (errorThrown) {
					console.log(errorThrown);
					if (errorThrown.status == 422) {
					}
				},
			});
		},
	);

	$(document).ready(function () {

		$('#bdthemes-templates-builder-modal form #template_type').on('change', function () {
			if ($(this).val() === 'themes|header' || $(this).val() === 'themes|footer') {
				$('.bdt-header-footer-option-container').show();
			} else {
				$('.bdt-header-footer-option-container').hide();
			}
			
			// Handle Ultimate Store Kit promotional message
			handleUltimateStoreKitPromotion($(this));
		});

		$(".bdt-template-modalinput-condition_singular_id").select2({
			ajax: {
				url: window.ElementPackConfigBuilder.resturl + 'get-singular-list',
				dataType: "json",
				data: function (params) {
					return { s: params.term };
				},
			},
			cache: true,
			placeholder: "--",
			dropdownParent: $(".bdt-template-modalinput-condition_singular-container"),
		});

		$(document).on('change', '.bdt-template-modalinput-condition_a', function (e) {
			var selectedCondition = $(this).val();
			var singularContainer = $(".bdt-template-modalinput-condition_singular-container");

			if (selectedCondition === "singular") {
				singularContainer.show();
			} else {
				singularContainer.hide();
			}
		});

		$(".bdt-template-modalinput-condition_singular").on("change", function () {
			var selectedConditionSingular = $(this).val();
			var singularIdContainer = $(".bdt-template-modalinput-condition_singular_id-container");

			if (selectedConditionSingular === "selective") {
				singularIdContainer.show();
			} else {
				singularIdContainer.hide();
			}
		});


	});

})(jQuery);
