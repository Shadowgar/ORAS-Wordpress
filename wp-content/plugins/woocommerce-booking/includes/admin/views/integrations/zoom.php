<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * Zoom.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/Views/Integrations/Zoom
 * @since       5.19.0
 */

?>
<template id="zoom-tab">
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

				<div class="container-fluid pl-info-wrap" id="bkap_admin_view_message" v-show="'success' === data.settings.bkap_zoom_con_status">
					<div class="row">
						<div class="col-md-12">
							<div class="alert alert-success alert-dismissible fade show" role="alert">
								<span v-html="data.label.zoom_connection_success_message"></span>
								<button type="button" class="close" data-dismiss="alert" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
						</div>
					</div>
				</div>

				<div class="container-fluid pl-info-wrap" id="bkap_admin_error_message" v-show="'fail' === data.settings.bkap_zoom_con_status">
					<div class="row">
						<div class="col-md-12">
							<div class="alert alert-danger alert-dismissible fade show" role="alert">
								<span v-html="data.label.zoom_connection_failure_message"></span>
								<button type="button" class="close" data-dismiss="alert" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
						</div>
					</div>
				</div>

				<div class="container-fluid pl-info-wrap" id="bkap_admin_view_message" v-show="show_logout_message">
					<div class="row">
						<div class="col-md-12">
							<div class="alert alert-danger alert-dismissible fade show" role="alert">
								<span v-html="data.label.zoom_connection_logout_message"></span>
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

				<div class="col-md-12">
					<div class="bkap-page-head phw-btn">
						<div class="col-left">
                            <h1><?php esc_attr_e( 'Zoom Meetings', 'woocommerce-booking' ); // phpcs:ignore ?></h1>
                            <p><?php esc_attr_e( 'Manage your Zoom meetings integrations.', 'woocommerce-booking' ); // phpcs:ignore ?>
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
                                        <?php esc_attr_e( 'API Credentials', 'woocommerce-booking' ); // phpcs:ignore  ?>
									</h2>
								</div>
								<div id="collapseOne" class="panel-collapse collapse show">
									<div class="panel-body">
										<div class="tbl-mod-1">
											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Instructions', 'woocommerce-booking' ); ?></label>
													<p><a class="instructions"
															v-on:click.stop="toggle_display('show_instructions_zoom')"><?php esc_attr_e( 'Click here', 'woocommerce-booking' ); ?></a>
                                                        <?php esc_attr_e( ' to view instructions on creating your API Key and API Secret', 'woocommerce-booking' ); // phpcs:ignore ?>
													</p>
												</div>
												<div class="col-right" v-show="show_instructions_zoom">
													<div class="rc-flx-wrap flx-aln-center ro-wrap">
														<div class="alert alert-dark alert-info" role="alert">
															<div class="left-col"><img
																	src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info-small.svg' ); ?>"
																	alt="Info Icon">
															</div>
															<div class="right-col">
																<ol>
																	<li>
																	<?php
																	printf(
																		wp_kses(
																			/* translators: %s: Zoom Meeting Settings page link */
																			__( 'Sign in to your Zoom account and visit the %s.', 'woocommerce-booking' ),
																			array(
																				'b' => array(),
																				'a' => array(),
																			)
																		),
																		'<b><a href="https://marketplace.zoom.us/" target="_blank">Zoom App Marketplace</a></b>'
																	);
																	?>
																	</li>
																	<li><?php echo wp_kses( __( 'Click on the <b>Develop</b> option on the top-right corner and select <b>Build App</b>.', 'woocommerce-booking' ), array( 'b' => array() ) ); ?>
																	<li><?php echo wp_kses( __( 'A page with various app types will be displayed. Select <b>OAuth</b> as the app type and click on <b>Create</b>.', 'woocommerce-booking' ), array( 'b' => array() ) ); ?>
																	<li><?php echo wp_kses( __( 'Fill out the <b>App Name</b> and choose the app type as <b>Account-level</b> app and switch off the option for publishing Zoom App to Marketplace and click on the <b>Create</b> button.', 'woocommerce-booking' ), array( 'b' => array() ) ); ?>
																	<li><?php echo wp_kses( __( 'The <b>Client ID</b> and <b>Client Secret</b> information will be available in the <b>App Credentials</b> tab. Use them in the form below on this page and click on the Save Settings button.', 'woocommerce-booking' ), array( 'b' => array() ) ); ?>
																	<li><?php echo wp_kses( __( 'Once you\'ve copied over your Client ID and Client Secret, copy the <b>Redirect URL</b> and set it to <b>Redirect URL for the OAuth</b> option in the App Credentials section and <b>Add Allow List</b> option in the OAuth Allow List section. Click on Continue for the next step.', 'woocommerce-booking' ), array( 'b' => array() ) ); ?>
																	<li><?php echo wp_kses( __( 'In the Information tab, fill data in the required fields like Short description, Long description, Company name, and Developer Contact Information and click on the Continue button.', 'woocommerce-booking' ), array( 'b' => array() ) ); ?></li>
																	<li><?php echo wp_kses( __( 'In the Features tab, enable the required features and click on the Continue button.', 'woocommerce-booking' ), array( 'b' => array() ) ); ?></li>
																	<li><?php echo wp_kses( __( 'You will now be on the <b>Scopes</b> tab. Click on the <b>Add Scopes</b> button select all the scopes in <b>Meeting</b> and <b>User</b> tabs and click on the <b>Done</b> button.', 'woocommerce-booking' ), array( 'b' => array() ) ); ?></li>
																	<li><?php echo wp_kses( __( 'Click on the Continue button and if there are no error messages for missing fields on the Activation tab then your app is ready.', 'woocommerce-booking' ), array( 'b' => array() ) ); ?></li>
																	<li><?php echo wp_kses( __( 'Now you can connect the store with the created Zoom App by clicking on the <b>Connect to Zoom</b> button below.', 'woocommerce-booking' ), array( 'b' => array() ) ); ?></li>
																	<li><?php echo wp_kses( __( 'On click of <b>Connect to Zoom</b> button, you will be redirected to the permission page of Zoom for using the app. Click on <b>Allow</b> button to give the permission and you will be redirected back to store upon the successful connection.', 'woocommerce-booking' ), array( 'b' => array() ) ); ?></li>
																</ol>
															</div>
														</div>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Client ID', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'The client id obtained from your oauth app.', 'woocommerce-booking' ); ?>">
														<textarea class="ta-sm textarea-like-input"
															v-model="data.settings.bkap_zoom_client_id"
															:readonly="'' !== data.settings.bkap_zoom_logout_url"></textarea>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Client Secret', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'The client secret obtained from your oauth app.', 'woocommerce-booking' ); ?>">
														<textarea class="ta-sm textarea-like-input"
															v-model="data.settings.bkap_zoom_client_secret"
															:readonly="'' !== data.settings.bkap_zoom_logout_url"></textarea>
													</div>
												</div>
											</div>

											<div class="tm1-row"
												v-show="'' !== data.settings.bkap_zoom_redirect_uri">
												<div class="col-left">
													<label><?php esc_attr_e( 'Redirect URI', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="">
														<textarea
															class="ta-sm textarea-like-input bkap-zoom-meeting-redirect-uri"
															readonly="readonly">{{data.settings.bkap_zoom_redirect_uri}}</textarea>
														<a href="javascript:void(0)"
															data-selector-to-copy="textarea.bkap-zoom-meeting-redirect-uri"
															data-tip="<?php esc_attr_e( 'Redirect URI has been copied!', 'woocommerce-booking' ); ?>"
															class="dashicons dashicons-admin-page copy-to-clipboard">&nbsp;</a>
													</div>
												</div>
											</div>

											<div class="tm1-row"
												v-show="'' !== data.settings.bkap_zoom_connect_link && '' === data.settings.bkap_zoom_access_token">
												<div class="col-left">
												</div>
												<div class="col-right">
													<a v-bind:href="data.settings.bkap_zoom_connect_link"
														class="secondary-btn"><?php esc_html_e( 'Connect to Zoom', 'woocommerce-booking' ); ?></a>
												</div>
											</div>

											<div class="tm1-row"
												v-show="'' !== data.settings.bkap_zoom_logout_url">
												<div class="col-left">
												</div>
												<div class="col-right">
													<a v-on:click.stop="logout" v-show="!is_logging_out"
														class="secondary-btn"><?php esc_html_e( 'Logout', 'woocommerce-booking' ); ?></a>

													<button class="secondary-btn" type="button" disabled
														v-show="is_logging_out">
														<span class="spinner-border spinner-border-sm"
															role="status" aria-hidden="true"></span>
														<?php esc_html_e( 'Logging out, please wait...', 'woocommerce-booking' ); ?>
													</button>
												</div>
											</div>

											<div class="tm1-row" v-show="'' !== data.settings.bkap_zoom_logout_url">
												<div class="col-left">
													<p class="tool-tip-text" v-show="'yes' !== data.settings.settings.assign_meeting_status && 'done' !== data.settings.settings.assign_meeting_status && data.settings.settings.is_zoom_meeting_enabled">
														<?php esc_html_e( 'Click on the "Assign Meeting to Booking" button to Create/Add meeting links for the Bookings which doesn\'t have the meetings.', 'woocommerce-booking' ); ?>
													</p>
												</div>
												<div class="col-right">
													<div class="row-box-1">
														<div class="rb1-right">
															<div class="rb1-row flx-center">
																<div class="alert alert-info alert-dark" role="alert"
																	v-show="show_test_connection_success_message">
																	<div class="left-col"><img
																			src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info-small.svg' ); ?>"
																			alt="Info Icon"></div>
																	<div class="right-col">
																		<p>{{data.label.test_connection_success_message}}
																		</p>
																	</div>
																</div>

																<div class="alert alert-danger" role="alert"
																	v-show="show_test_connection_error_message">
																	<div class="left-col"><img
																			src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info-small.svg' ); ?>"
																			alt="Info Icon"></div>
																	<div class="right-col">
																		<p>{{data.label.test_connection_error_message}}
																		</p>
																	</div>
																</div>

																<div class="alert alert-info alert-dark" role="alert"
																	v-show="show_assign_bookings_success_message">
																	<div class="left-col"><img
																			src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info-small.svg' ); ?>"
																			alt="Info Icon"></div>
																	<div class="right-col">
																		<p>{{data.label.assign_bookings_success_message}}
																		</p>
																	</div>
																</div>

																<div class="alert alert-danger" role="alert"
																	v-show="show_assign_bookings_error_message">
																	<div class="left-col"><img
																			src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info-small.svg' ); ?>"
																			alt="Info Icon"></div>
																	<div class="right-col">
																		<p>{{data.label.assign_bookings_error_message}}
																		</p>
																	</div>
																</div>
															</div>
															<div class="rb1-row flx-center mb-10">
																<div class="rb-col"
																	v-show="'' !== data.settings.bkap_zoom_client_id && '' !== data.settings.bkap_zoom_client_secret">
																	<input class="secondary-btn"
																		v-on:click.stop="test_connection"
																		v-show="!is_testing_connection"
																		value="<?php esc_attr_e( 'Test Connection', 'woocommerce-booking' ); ?>"
																		type="submit">
																	<button class="secondary-btn" type="button" disabled
																		v-show="is_testing_connection">
																		<span class="spinner-border spinner-border-sm"
																			role="status" aria-hidden="true"></span>
																		<?php esc_attr_e( 'Testing, please wait...', 'woocommerce-booking' ); ?>
																	</button>
																</div>

																<div class="rb-col"
																	v-show="'yes' !== data.settings.settings.assign_meeting_status && 'done' !== data.settings.settings.assign_meeting_status && data.settings.settings.is_zoom_meeting_enabled">
																	<input class="secondary-btn"
																		v-on:click.stop="assign_meetings_to_bookings"
																		v-show="!is_assigning_meetings_to_bookings"
																		value="<?php esc_attr_e( 'Assign Meeting to Bookings', 'woocommerce-booking' ); ?>"
																		type="submit">
																	<button class="secondary-btn" type="button" disabled
																		v-show="is_assigning_meetings_to_bookings">
																		<span class="spinner-border spinner-border-sm"
																			role="status" aria-hidden="true"></span>
																		<?php esc_attr_e( 'Assigning Meetings, please wait...', 'woocommerce-booking' ); ?>
																	</button>
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
				</div>

				<div class="col-md-12">
					<div class="bdp-foot">
						<button type="button" class="bkap-button" v-on:click.stop="save_settings">{{data.label.save_settings}}</button>
					</div>
				</div>
			</div>
		</div>
	</section>
</template>
