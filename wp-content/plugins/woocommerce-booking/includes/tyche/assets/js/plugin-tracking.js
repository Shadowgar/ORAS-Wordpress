/** Onboaring Wizard - Data Tracking - Onboaring 5 */
var data_tracking = Vue.component( "onboarding-data-tracking", {
    template: "#onboarding-data-tracking",
    data() {
        return {
			data: {
                'settings': {
                    allow_tracking: '',
                },
				'label': {
					'saving_loader': bkap_params.vue.label.saving_loader,
					'loading_loader': bkap_params.vue.label.loading_loader,
					'save_settings': bkap_params.vue.label.save_settings,
				}
			},
			show_saved_message: false,
			show_error_message: false,
			show_loading_loader: false,
			error_message: ''
		}
    },
    methods: {
        allowed_tracking: function () {
            $this                     = this;
            $this.show_loading_loader = true;

            axios.post(
                `${tyche.bkap.rest_url()}onboarding/data-tracking`, {
                    'data': $this.data.settings
                }, {
                    headers: {
                        'X-WP-Nonce': bkap_params.nonce
                    }
                }
            )
            .then( function( response ) {
                if ( 'undefined' !== typeof response.data && 'success' === response.data.type ) {
                    $this.show_loading_loader = false;
                } else {
                    $this.error_message       = tyche.bkap.return_label( 'axios_get_error' );
                    $this.show_loading_loader = false;
                    $this.show_error_message  = true;
                }
            } )
            .catch( function( error ) {
                self.error_message       = tyche.bkap.return_label( 'axios_get_error', error );
                self.show_loading_loader = false;
                self.show_error_message  = true;
            });
        }
    }
});