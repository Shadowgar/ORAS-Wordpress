var license = Vue.component( "tab-license", {
	template: "#license-tab",
	data() {
		return {
			data: {
				'license': bkap_view_license_param.data,
				'label': {
					'saving_loader': bkap_params.vue.label.saving_loader,
					'loading_loader': bkap_params.vue.label.loading_loader,
					'active_license': bkap_view_license_param.label.active_license,
					'inactive_license': bkap_view_license_param.label.inactive_license,
					'activate_license': bkap_view_license_param.label.activate_license,
					'deactivate_license': bkap_view_license_param.label.deactivate_license
				}
			},
			show_saved_message: false,
			show_error_message: false,
			show_saving_loader: false,
			show_loading_loader: true,
			disable_license_textbox: false,
			ajax_error: false,
			error_message: ''
		}
	},
	mounted() {
		this.show_loading_loader = false;
	},
	methods: {
		deactivate_license( plugin ) {
			this.show_saved_message = false;
			this.show_error_message = false;
			this.show_loading_loader = false;
			this.show_saving_loader = true;
			this.ajax_error = false;
			this.error_message = '';
			let $this = this;

			axios.post(
					`${tyche.bkap.rest_url()}license/deactivate`, {
						plugin
					}, {
						headers: {
							'X-WP-Nonce': bkap_params.nonce
						}
					}
				).then( function( response ) {
					if ( 'success' === response.data ) {
						window.location.reload();
						return;
					} else {
						$this.error_message = $this.return_label( 'axios_get_error' );
						$this.show_saving_loader = false;
						$this.show_error_message = true;
					}
				} )
				.catch( function( error ) {
					$this.error_message = $this.return_label( 'axios_get_error', error );
					$this.show_saving_loader = true;
					$this.show_error_message = true;
				} );
		},
		activate_license( plugin ) {
			this.show_saved_message = false;
			this.show_error_message = false;
			this.show_saving_loader = true;
			this.show_loading_loader = false;
			this.ajax_error = false;
			this.error_message = '';
			let $this = this;

			axios.post(
					`${tyche.bkap.rest_url()}license/activate`, {
						'license_key': $this.data.license[ plugin ].license_key,
						plugin
					}, {
						headers: {
							'X-WP-Nonce': bkap_params.nonce
						}
					}
				)
				.then( function( response ) {
					if ( 'undefined' !== typeof response.data.type ) {
						if ( 'success' === response.data.type ) {
							$this.show_saving_loader = false;
							$this.show_saved_message = true;
							$this.data.license = response.data.license_data;
							$this.data.license[ plugin ].disable_license_textbox = true;

							// Remove notice for license activation.
							jQuery( '.notice.notice-error' ).each( function() {
								if ( jQuery( this ).find( 'a[href*="admin.php?page=bkap_page#/license"]' ) ) {
									jQuery( this ).remove();
								}
							} );
						} else if ( 'error' === response.data.type ) {
							$this.error_message = response.data.error_description;
							$this.show_error_message = true;
							$this.show_saving_loader = false;
						}
					}
				} )
				.catch( function( error ) {
					$this.error_message = $this.return_label( 'axios_get_error', error );
					$this.show_saving_loader = true;
					$this.show_error_message = true;
				} );
		}
	}
} );
