<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * License.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/Views/Home/Welcome
 * @since       5.19.0
 */

?>

<template id="license-tab">
	<section>
		<div class="container pl-page-wrap">
			<div class="container-fluid pl-info-wrap" id="bkap_admin_view_message" v-show="show_saved_message">
				<div class="row">
					<div class="col-md-12">
						<div class="alert alert-success alert-dismissible fade show" role="alert">
							<?php esc_attr_e( 'License Key has been activated.', 'woocommerce-booking' ); ?>
							<button type="button" class="close" data-dismiss="alert" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
					</div>
				</div>
			</div>

			<div class="container-fluid pl-info-wrap" id="bkap_admin_error_message" v-show="show_error_message">
				<div class="row">
					<div class="col-md-12">
						<div class="alert alert-danger alert-dismissible fade show" role="alert">
							<span v-html="error_message"></span>
							<button type="button" class="close" data-dismiss="alert" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
					</div>
				</div>
			</div>

			<div class="bkap_admin_loader" v-show="show_saving_loader">
				<div class="bkap_admin_loader_wrapper">
					{{data.label.saving_loader}} <img src=<?php echo esc_url( BKAP_IMAGE_URL . 'ajax-loader.gif' ); ?>>
				</div>
			</div>

			<div class="bkap_admin_loader" v-show="show_loading_loader">
				<div class="bkap_admin_loader_wrapper">
					{{data.label.loading_loader}} <img src=<?php echo esc_url( BKAP_IMAGE_URL . 'ajax-loader.gif' ); ?>>
				</div>
			</div>

			<div class="row mb-15 mt-20">
				<div class="col-md-12">
					<div class="wbc-box">

						<div class="wbc-head">
                            <h2><?php esc_attr_e( BKAP_PLUGIN_NAME, 'woocommerce-booking' ); //phpcs:ignore ?></h2>
						</div>

						<div class="wbc-content" v-if="!ajax_error">
							<div class="tbl-mod-1">
								<div class="tm1-row flx-center">
									<div class="col-left">
										<label><?php esc_attr_e( 'License Status:', 'woocommerce-booking' ); ?></label>
									</div>

									<div class="col-right" v-if="'valid' === data.license.bkap.license_status">
										<span class="mode-active">{{data.label.active_license}}</span>
									</div>

									<div class="col-right" v-else>
										<span class="mode-deactive">{{data.label.inactive_license}}</span>
									</div>
								</div>

								<div class="tm1-row flx-fs-space">
									<div class="col-left">
										<label><?php esc_attr_e( 'License Key', 'woocommerce-booking' ); ?></label>
									</div>

									<div class="col-right">
										<div class="row-box-1">
											<div class="rb1-right">
												<div class="rb1-row flx-center">
													<div class="rb-col">
														<input class="ib-lg" type="text"
															v-model="data.license.bkap.license_key"
															:disabled="'valid' === data.license.bkap.license_status">
													</div>

													<div class="rb-col">
														<input class="secondary-btn btn-red" type="button"
															v-on:click.stop="deactivate_license( 'bkap' )"
															:value="data.label.deactivate_license"
															v-if="'valid' === data.license.bkap.license_status">
														<input class="bkap-button btn-small" type="button"
															v-on:click.stop="activate_license( 'bkap' )"
															:value="data.label.activate_license" v-else>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="row mb-15" v-if="'undefined' !== typeof data.license.outlook_calendar">
				<div class="col-md-12">
					<div class="wbc-box">

						<div class="wbc-head">
                            <h2><?php esc_attr_e( 'Outlook Calendar Add-on', 'woocommerce-booking' ); //phpcs:ignore ?>
							</h2>
						</div>

						<div class="wbc-content" v-if="!ajax_error">
							<div class="tbl-mod-1">
								<div class="tm1-row flx-center">
									<div class="col-left">
										<label><?php esc_attr_e( 'License Status:', 'woocommerce-booking' ); ?></label>
									</div>

									<div class="col-right"
										v-if="'valid' === data.license.outlook_calendar.license_status">
										<span class="mode-active">{{data.label.active_license}}</span>
									</div>

									<div class="col-right" v-else>
										<span class="mode-deactive">{{data.label.inactive_license}}</span>
									</div>
								</div>

								<div class="tm1-row flx-fs-space">
									<div class="col-left">
										<label><?php esc_attr_e( 'License Key', 'woocommerce-booking' ); ?></label>
									</div>

									<div class="col-right">
										<div class="row-box-1">
											<div class="rb1-right">
												<div class="rb1-row flx-center">
													<div class="rb-col">
														<input class="ib-lg" type="text"
															v-model="data.license.outlook_calendar.license_key"
															:disabled="'valid' === data.license.outlook_calendar.license_status">
													</div>

													<div class="rb-col">
														<input class="secondary-btn btn-red" type="button"
															v-on:click.stop="deactivate_license( 'outlook_calendar' )"
															:value="data.label.deactivate_license"
															v-if="'valid' === data.license.outlook_calendar.license_status">
														<input class="bkap-button btn-small" type="button"
															v-on:click.stop="activate_license( 'outlook_calendar' )"
															:value="data.label.activate_license" v-else>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="row mb-15" v-if="'undefined' !== typeof data.license.partial_deposits">
				<div class="col-md-12">
					<div class="wbc-box">

						<div class="wbc-head">
                            <h2><?php esc_attr_e( 'Partial Deposits Add-on', 'woocommerce-booking' ); //phpcs:ignore ?>
							</h2>
						</div>

						<div class="wbc-content" v-if="!ajax_error">
							<div class="tbl-mod-1">
								<div class="tm1-row flx-center">
									<div class="col-left">
										<label><?php esc_attr_e( 'License Status:', 'woocommerce-booking' ); ?></label>
									</div>

									<div class="col-right"
										v-if="'valid' === data.license.partial_deposits.license_status">
										<span class="mode-active">{{data.label.active_license}}</span>
									</div>

									<div class="col-right" v-else>
										<span class="mode-deactive">{{data.label.inactive_license}}</span>
									</div>
								</div>

								<div class="tm1-row flx-fs-space">
									<div class="col-left">
										<label><?php esc_attr_e( 'License Key', 'woocommerce-booking' ); ?></label>
									</div>

									<div class="col-right">
										<div class="row-box-1">
											<div class="rb1-right">
												<div class="rb1-row flx-center">
													<div class="rb-col">
														<input class="ib-lg" type="text"
															v-model="data.license.partial_deposits.license_key"
															:disabled="'valid' === data.license.partial_deposits.license_status">
													</div>

													<div class="rb-col">
														<input class="secondary-btn btn-red" type="button"
															v-on:click.stop="deactivate_license( 'partial_deposits' )"
															:value="data.label.deactivate_license"
															v-if="'valid' === data.license.partial_deposits.license_status">
														<input class="bkap-button btn-small" type="button"
															v-on:click.stop="activate_license( 'partial_deposits' )"
															:value="data.label.activate_license" v-else>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="row mb-15" v-if="'undefined' !== typeof data.license.printable_tickets">
				<div class="col-md-12">
					<div class="wbc-box">

						<div class="wbc-head">
                            <h2><?php esc_attr_e( 'Printable Tickets Add-on', 'woocommerce-booking' ); //phpcs:ignore ?>
							</h2>
						</div>

						<div class="wbc-content" v-if="!ajax_error">
							<div class="tbl-mod-1">
								<div class="tm1-row flx-center">
									<div class="col-left">
										<label><?php esc_attr_e( 'License Status:', 'woocommerce-booking' ); ?></label>
									</div>

									<div class="col-right"
										v-if="'valid' === data.license.printable_tickets.license_status">
										<span class="mode-active">{{data.label.active_license}}</span>
									</div>

									<div class="col-right" v-else>
										<span class="mode-deactive">{{data.label.inactive_license}}</span>
									</div>
								</div>

								<div class="tm1-row flx-fs-space">
									<div class="col-left">
										<label><?php esc_attr_e( 'License Key', 'woocommerce-booking' ); ?></label>
									</div>

									<div class="col-right">
										<div class="row-box-1">
											<div class="rb1-right">
												<div class="rb1-row flx-center">
													<div class="rb-col">
														<input class="ib-lg" type="text"
															v-model="data.license.printable_tickets.license_key"
															:disabled="'valid' === data.license.printable_tickets.license_status">
													</div>

													<div class="rb-col">
														<input class="secondary-btn btn-red" type="button"
															v-on:click.stop="deactivate_license( 'printable_tickets' )"
															:value="data.label.deactivate_license"
															v-if="'valid' === data.license.printable_tickets.license_status">
														<input class="bkap-button btn-small" type="button"
															v-on:click.stop="activate_license( 'printable_tickets' )"
															:value="data.label.activate_license" v-else>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="row mb-15" v-if="'undefined' !== typeof data.license.recurring_bookings">
				<div class="col-md-12">
					<div class="wbc-box">

						<div class="wbc-head">
                            <h2><?php esc_attr_e( 'Recurring Bookings Add-on', 'woocommerce-booking' ); //phpcs:ignore ?>
							</h2>
						</div>

						<div class="wbc-content" v-if="!ajax_error">
							<div class="tbl-mod-1">
								<div class="tm1-row flx-center">
									<div class="col-left">
										<label><?php esc_attr_e( 'License Status:', 'woocommerce-booking' ); ?></label>
									</div>

									<div class="col-right"
										v-if="'valid' === data.license.recurring_bookings.license_status">
										<span class="mode-active">{{data.label.active_license}}</span>
									</div>

									<div class="col-right" v-else>
										<span class="mode-deactive">{{data.label.inactive_license}}</span>
									</div>
								</div>

								<div class="tm1-row flx-fs-space">
									<div class="col-left">
										<label><?php esc_attr_e( 'License Key', 'woocommerce-booking' ); ?></label>
									</div>

									<div class="col-right">
										<div class="row-box-1">
											<div class="rb1-right">
												<div class="rb1-row flx-center">
													<div class="rb-col">
														<input class="ib-lg" type="text"
															v-model="data.license.recurring_bookings.license_key"
															:disabled="'valid' === data.license.recurring_bookings.license_status">
													</div>

													<div class="rb-col">
														<input class="secondary-btn btn-red" type="button"
															v-on:click.stop="deactivate_license( 'recurring_bookings' )"
															:value="data.label.deactivate_license"
															v-if="'valid' === data.license.recurring_bookings.license_status">
														<input class="bkap-button btn-small" type="button"
															v-on:click.stop="activate_license( 'recurring_bookings' )"
															:value="data.label.activate_license" v-else>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="row mb-15" v-if="'undefined' !== typeof data.license.seasonal_pricing">
				<div class="col-md-12">
					<div class="wbc-box">

						<div class="wbc-head">
                            <h2><?php esc_attr_e( 'Seasonal Pricing Add-on', 'woocommerce-booking' ); //phpcs:ignore ?>
							</h2>
						</div>

						<div class="wbc-content" v-if="!ajax_error">
							<div class="tbl-mod-1">
								<div class="tm1-row flx-center">
									<div class="col-left">
										<label><?php esc_attr_e( 'License Status:', 'woocommerce-booking' ); ?></label>
									</div>

									<div class="col-right"
										v-if="'valid' === data.license.seasonal_pricing.license_status">
										<span class="mode-active">{{data.label.active_license}}</span>
									</div>

									<div class="col-right" v-else>
										<span class="mode-deactive">{{data.label.inactive_license}}</span>
									</div>
								</div>

								<div class="tm1-row flx-fs-space">
									<div class="col-left">
										<label><?php esc_attr_e( 'License Key', 'woocommerce-booking' ); ?></label>
									</div>

									<div class="col-right">
										<div class="row-box-1">
											<div class="rb1-right">
												<div class="rb1-row flx-center">
													<div class="rb-col">
														<input class="ib-lg" type="text"
															v-model="data.license.seasonal_pricing.license_key"
															:disabled="'valid' === data.license.seasonal_pricing.license_status">
													</div>

													<div class="rb-col">
														<input class="secondary-btn btn-red" type="button"
															v-on:click.stop="deactivate_license( 'seasonal_pricing' )"
															:value="data.label.deactivate_license"
															v-if="'valid' === data.license.seasonal_pricing.license_status">
														<input class="bkap-button btn-small" type="button"
															v-on:click.stop="activate_license( 'seasonal_pricing' )"
															:value="data.label.activate_license" v-else>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="row mb-15" v-if="'undefined' !== typeof data.license.rental">
				<div class="col-md-12">
					<div class="wbc-box">

						<div class="wbc-head">
                            <h2><?php esc_attr_e( 'Rental System Addon for Booking & Appointment Plugin for WooCommerce', 'woocommerce-booking' ); //phpcs:ignore ?>
							</h2>
						</div>

						<div class="wbc-content" v-if="!ajax_error">
							<div class="tbl-mod-1">
								<div class="tm1-row flx-center">
									<div class="col-left">
										<label><?php esc_attr_e( 'License Status:', 'woocommerce-booking' ); ?></label>
									</div>

									<div class="col-right"
										v-if="'valid' === data.license.rental.license_status">
										<span class="mode-active">{{data.label.active_license}}</span>
									</div>

									<div class="col-right" v-else>
										<span class="mode-deactive">{{data.label.inactive_license}}</span>
									</div>
								</div>

								<div class="tm1-row flx-fs-space">
									<div class="col-left">
										<label><?php esc_attr_e( 'License Key', 'woocommerce-booking' ); ?></label>
									</div>

									<div class="col-right">
										<div class="row-box-1">
											<div class="rb1-right">
												<div class="rb1-row flx-center">
													<div class="rb-col">
														<input class="ib-lg" type="text"
															v-model="data.license.rental.license_key"
															:disabled="'valid' === data.license.rental.license_status">
													</div>

													<div class="rb-col">
														<input class="secondary-btn btn-red" type="button"
															v-on:click.stop="deactivate_license( 'rental' )"
															:value="data.label.deactivate_license"
															v-if="'valid' === data.license.rental.license_status">
														<input class="bkap-button btn-small" type="button"
															v-on:click.stop="activate_license( 'rental' )"
															:value="data.label.activate_license" v-else>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="row mb-15" v-if="'undefined' !== typeof data.license.multiple_timeslots">
				<div class="col-md-12">
					<div class="wbc-box">

						<div class="wbc-head">
                            <h2><?php esc_attr_e( 'Multiple Time Slot Addon for Booking & Appointment Plugin for WooCommerce', 'woocommerce-booking' ); //phpcs:ignore ?>
							</h2>
						</div>

						<div class="wbc-content" v-if="!ajax_error">
							<div class="tbl-mod-1">
								<div class="tm1-row flx-center">
									<div class="col-left">
										<label><?php esc_attr_e( 'License Status:', 'woocommerce-booking' ); ?></label>
									</div>

									<div class="col-right"
										v-if="'valid' === data.license.multiple_timeslots.license_status">
										<span class="mode-active">{{data.label.active_license}}</span>
									</div>

									<div class="col-right" v-else>
										<span class="mode-deactive">{{data.label.inactive_license}}</span>
									</div>
								</div>

								<div class="tm1-row flx-fs-space">
									<div class="col-left">
										<label><?php esc_attr_e( 'License Key', 'woocommerce-booking' ); ?></label>
									</div>

									<div class="col-right">
										<div class="row-box-1">
											<div class="rb1-right">
												<div class="rb1-row flx-center">
													<div class="rb-col">
														<input class="ib-lg" type="text"
															v-model="data.license.multiple_timeslots.license_key"
															:disabled="'valid' === data.license.multiple_timeslots.license_status">
													</div>

													<div class="rb-col">
														<input class="secondary-btn btn-red" type="button"
															v-on:click.stop="deactivate_license( 'multiple_timeslots' )"
															:value="data.label.deactivate_license"
															v-if="'valid' === data.license.multiple_timeslots.license_status">
														<input class="bkap-button btn-small" type="button"
															v-on:click.stop="activate_license( 'multiple_timeslots' )"
															:value="data.label.activate_license" v-else>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="row mb-15" v-if="'undefined' !== typeof data.license.bkap_marketplace">
				<div class="col-md-12">
					<div class="wbc-box">

						<div class="wbc-head">
                            <h2><?php esc_attr_e( 'Marketplace Addon for Booking & Appointment Plugin for WooCommerce', 'woocommerce-booking' ); //phpcs:ignore ?>
							</h2>
						</div>

						<div class="wbc-content" v-if="!ajax_error">
							<div class="tbl-mod-1">
								<div class="tm1-row flx-center">
									<div class="col-left">
										<label><?php esc_attr_e( 'License Status:', 'woocommerce-booking' ); ?></label>
									</div>

									<div class="col-right"
										v-if="'valid' === data.license.bkap_marketplace.license_status">
										<span class="mode-active">{{data.label.active_license}}</span>
									</div>

									<div class="col-right" v-else>
										<span class="mode-deactive">{{data.label.inactive_license}}</span>
									</div>
								</div>

								<div class="tm1-row flx-fs-space">
									<div class="col-left">
										<label><?php esc_attr_e( 'License Key', 'woocommerce-booking' ); ?></label>
									</div>

									<div class="col-right">
										<div class="row-box-1">
											<div class="rb1-right">
												<div class="rb1-row flx-center">
													<div class="rb-col">
														<input class="ib-lg" type="text"
															v-model="data.license.bkap_marketplace.license_key"
															:disabled="'valid' === data.license.bkap_marketplace.license_status">
													</div>

													<div class="rb-col">
														<input class="secondary-btn btn-red" type="button"
															v-on:click.stop="deactivate_license( 'bkap_marketplace' )"
															:value="data.label.deactivate_license"
															v-if="'valid' === data.license.bkap_marketplace.license_status">
														<input class="bkap-button btn-small" type="button"
															v-on:click.stop="activate_license( 'bkap_marketplace' )"
															:value="data.label.activate_license" v-else>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
</template>
