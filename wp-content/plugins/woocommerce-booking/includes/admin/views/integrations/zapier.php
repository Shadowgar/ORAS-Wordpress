<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * Zapier.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/Views/Integrations/Zapier
 * @since       5.19.0
 */

?>
<template id="zapier-tab">
	<section>
		<div class="container bd-page-wrap">
			<div class="row">
				<div class="container-fluid pl-info-wrap" id="bkap_admin_view_message" v-show="show_saved_message">
					<div class="row">
						<div class="col-md-12">
							<div class="alert alert-success alert-dismissible fade show" role="alert">
								<?php esc_attr_e( 'Settings have been saved.', 'woocommerce-booking' ); ?>
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
						{{data.label.saving_loader}} <img
							src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
					</div>
				</div>

				<div class="bkap_admin_loader" v-show="show_loading_loader">
					<div class="bkap_admin_loader_wrapper">
						{{data.label.loading_loader}} <img
							src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
					</div>
				</div>

				<div class="bkap_admin_loader" v-show="show_flushing_loader">
					<div class="bkap_admin_loader_wrapper">
						{{data.label.flushing_loader}} <img
							src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
					</div>
				</div>

				<div class="col-md-12">
					<div class="bkap-page-head phw-btn">
						<div class="col-left">
                            <h1><?php esc_attr_e( 'Zapier', 'woocommerce-booking' ); // phpcs:ignore ?></h1>
							<p>
								<?php
								/* translators: %s a link anchor tags */
								echo wp_kses_post( sprintf( __( 'Zapier is a service which you can use to create, update or delete bookings outside WooCommerce and integrate with other Zapier apps. If you do not have a Zapier account, you may %1$ssign up for one here%2$s.', 'woocommerce-booking' ), "<a href='https://zapier.com/app/signup' target='_blank'>", '</a>' ) );
								?>
							</p>
						</div>

						<div class="col-right">
							<button type="button" class="bkap-button" v-on:click.stop="save_settings">{{data.label.save_settings}}</button>
						</div>
					</div>
				</div>

				<div class="col-md-12">
					<div class="wbc-accordion">
						<div class="panel-group bkap-accordian" id="wbc-accordion">
							<div class="panel panel-default">
								<div class="panel-heading">
									<h2 class="panel-title" data-toggle="collapse" data-target="#collapseOne"
										aria-expanded="false">
                                        <?php esc_attr_e( 'Zapier General Settings', 'woocommerce-booking' ); // phpcs:ignore  ?>
									</h2>
								</div>
								<div id="collapseOne" class="panel-collapse collapse show">
									<div class="panel-body">
										<div class="tbl-mod-1">
											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Activate', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'Activates Zapier Integration on this WooCommerce Store for Zapier requests. Please enable Zapier triggers and actions to allow access to Zapier zaps.', 'woocommerce-booking' ); ?>">
														<label class="el-switch el-switch-green">
															<input type="checkbox"
																v-model="data.settings.bkap_api_zapier_integration"
																true-value="on" false-value="">
															<span class="el-switch-style"></span>
														</label>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Log', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'Logs all Zapier Requests such as Triggers, Actions and Subscriptions.', 'woocommerce-booking' ); ?>">
														<label class="el-switch el-switch-green">
															<input type="checkbox"
																v-model="data.settings.bkap_api_zapier_log_enable"
																true-value="on" false-value="">
															<span class="el-switch-style"></span>
														</label>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="panel panel-default">
								<div class="panel-heading">
									<h2 class="panel-title" data-toggle="collapse" data-target="#collapseOne"
										aria-expanded="false">
                                        <?php esc_attr_e( 'Zapier Triggers', 'woocommerce-booking' ); // phpcs:ignore  ?>
									</h2>
									<p><?php esc_attr_e( 'Zapier Triggers kickstart an event that starts a Zap, i.e Zapier -> WooCommerce. The options below control whether the various Triggers which are sent to Zapier are activated or not.', 'woocommerce-booking' ); ?>
									</p>
								</div>
								<div id="collapseOne" class="panel-collapse collapse show">
									<div class="panel-body">
										<div class="tbl-mod-1">
											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Create Booking Trigger', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'This ensures Bookings which are created on this woocommerce store are sent to zapier for onward trigger of other apps in the zapier workspace.', 'woocommerce-booking' ); ?>">
														<label class="el-switch el-switch-green">
															<input type="checkbox"
																v-model="data.settings.trigger_create_booking"
																true-value="on" false-value="">
															<span class="el-switch-style"></span>
														</label>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Update Booking Trigger', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'This ensures bookings which are updated on this woocommerce store are sent to zapier for onward trigger of other apps in the zapier workspace.', 'woocommerce-booking' ); ?>">
														<label class="el-switch el-switch-green">
															<input type="checkbox"
																v-model="data.settings.trigger_update_booking"
																true-value="on" false-value="">
															<span class="el-switch-style"></span>
														</label>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Delete Booking Trigger', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'This ensures bookings which are deleted on this woocommerce store are sent to zapier for onward trigger of other apps in the zapier workspace.', 'woocommerce-booking' ); ?>">
														<label class="el-switch el-switch-green">
															<input type="checkbox"
																v-model="data.settings.trigger_delete_booking"
																true-value="on" false-value="">
															<span class="el-switch-style"></span>
														</label>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="panel panel-default">
								<div class="panel-heading">
									<h2 class="panel-title" data-toggle="collapse" data-target="#collapseOne"
										aria-expanded="false">
                                        <?php esc_attr_e( 'Zapier Actions', 'woocommerce-booking' ); // phpcs:ignore  ?>
									</h2>
									<?php esc_attr_e( 'Zapier Actions creates, updates or deletes data in the woocommerce store from zapier. For instance, once an update is received from any of the zapier apps, a booking can be created, edited or updated here in this woocommerce store.', 'woocommerce-booking' ); ?>
									<p>
								</div>
								<div id="collapseOne" class="panel-collapse collapse show">
									<div class="panel-body">
										<div class="tbl-mod-1">
											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Create Booking Action', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'This enables zapier apps to create bookings on this woocommerce store.', 'woocommerce-booking' ); ?>">
														<label class="el-switch el-switch-green">
															<input type="checkbox"
																v-model="data.settings.action_create_booking"
																true-value="on" false-value="">
															<span class="el-switch-style"></span>
														</label>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Update Booking Action', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'This enables zapier apps to update bookings on this woocommerce store.', 'woocommerce-booking' ); ?>">
														<label class="el-switch el-switch-green">
															<input type="checkbox"
																v-model="data.settings.action_update_booking"
																true-value="on" false-value="">
															<span class="el-switch-style"></span>
														</label>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Delete Booking Action', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'This enables zapier apps to delete bookings on this woocommerce store.', 'woocommerce-booking' ); ?>">
														<label class="el-switch el-switch-green">
															<input type="checkbox"
																v-model="data.settings.action_delete_booking"
																true-value="on" false-value="">
															<span class="el-switch-style"></span>
														</label>
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

				<div class="col-md-12">
					<div class="bdp-foot">
						<button type="button" class="bkap-button" v-on:click.stop="save_settings">{{data.label.save_settings}}</button>
					</div>
				</div>

				<div class="container-fluid pl-info-wrap" id="bkap_admin_zapier_log_error_message"
					v-show="show_zapier_log_error_message">
					<div class="row">
						<div class="col-md-12">
							<div class="alert alert-danger alert-dismissible fade show" role="alert">
								<span v-html="zapier_log_error_message"></span>
								<button type="button" class="close" data-dismiss="alert" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
						</div>
					</div>
				</div>

				<div id="div_zapier_event_log" class="col-md-12"
					v-show="'on' === data.settings.bkap_api_zapier_integration && data.logs.length > 0">
					<div class="wbc-accordion">
						<div class="panel-group bkap-accordian" id="wbc-accordion">
							<div class="panel panel-default">
								<div class="panel-heading">
									<h2 class="panel-title" data-toggle="collapse" data-target="#collapseOne"
										aria-expanded="false">
                                        <?php esc_attr_e( 'Zapier Event Log', 'woocommerce-booking' ); // phpcs:ignore  ?>
									</h2>
									<?php esc_attr_e( 'Failed events ( error related ) are highlighted in red.', 'woocommerce-booking' ); ?>
									<p>
								</div>
								<div id="collapseOne" class="panel-collapse collapse show">
									<div class="panel-body">
										<div class="tbl-mod-2 tbl-space-sm flx-100">
											<div class="tm2-inner-wrap tbl-responsive">
												<table class="table locations-table">
													<thead>
														<tr>
															<th><?php esc_attr_e( 'Timestamp', 'woocommerce-booking' ); ?>
															</th>
															<th><?php esc_attr_e( 'Action', 'woocommerce-booking' ); ?>
															</th>
															<th><?php esc_attr_e( 'Details', 'woocommerce-booking' ); ?>
															</th>
														</tr>
													</thead>
													<tbody>
														<tr v-for="log in data.logs">
															<td>{{log.timestamp}}</td>
															<td v-html="log.action"></td>
															<td v-html="log.details"></td>
														</tr>
													</tbody>
												</table>

												<div class="link-flx">
													<a @click="flush_logs"><img
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-trash.svg' ); ?>"
															alt="Icon" />
														<?php esc_attr_e( 'Flush Logs', 'woocommerce-booking' ); ?></a>
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
