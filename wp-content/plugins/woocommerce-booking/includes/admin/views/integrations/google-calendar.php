<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * Google Calendar.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/Views/Integrations/GoogleCalendar
 * @since       5.19.0
 */

?>
<template id="google-calendar-tab">
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

				<div class="container-fluid pl-info-wrap" id="bkap_admin_view_message"
					v-show="'' != data.settings.bkap_gcal_success">
					<div class="row">
						<div class="col-md-12">
							<div class="alert alert-success alert-dismissible fade show" role="alert">
								<span v-html="data.settings.bkap_gcal_success"></span>
								<button type="button" class="close" data-dismiss="alert" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
						</div>
					</div>
				</div>

				<div class="container-fluid pl-info-wrap" id="bkap_admin_error_message"
					v-show="'' != data.settings.bkap_gcal_failure">
					<div class="row">
						<div class="col-md-12">
							<div class="alert alert-danger alert-dismissible fade show" role="alert">
								<span v-html="data.settings.bkap_gcal_failure"></span>
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
							src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); // phpcs:ignore ?>>
					</div>
				</div>

				<div class="bkap_admin_loader" v-show="show_loading_loader">
					<div class="bkap_admin_loader_wrapper">
						{{data.label.loading_loader}} <img
							src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
					</div>
				</div>

				<div class="bkap_admin_loader" v-show="show_logout_from_calendar_loader">
					<div class="bkap_admin_loader_wrapper">
						{{data.label.logout_from_calendar_loader}} <img
							src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
					</div>
				</div>

				<div class="col-md-12">
					<div class="bkap-page-head phw-btn">
						<div class="col-left">
							<h1><?php esc_attr_e( 'Google Calendar Sync', 'woocommerce-booking' ); // phpcs:ignore ?>
							</h1>
							<p><?php esc_attr_e( 'Connect your bookings to Google Calendar.', 'woocommerce-booking' ); // phpcs:ignore ?>
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
									<h2 class="panel-title" data-toggle="collapse" data-target="#collapse-general"
										aria-expanded="false">
										<?php esc_attr_e( 'General Settings', 'woocommerce-booking' ); // phpcs:ignore  ?>
									</h2>
								</div>
								<div id="collapse-general" class="panel-collapse collapse show">
									<div class="panel-body">
										<div class="tbl-mod-1">
											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Event Location', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="row-box-1">
														<div class="rb1-left">
															<img class="tt-info"
																src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
																alt="Tooltip" data-toggle="tooltip" data-placement="top"
																title="<?php esc_attr_e( 'Enter the text that will be used as location field in event of the Calendar. If left empty, website description is sent instead. <br><i>Note: you can use ADDRESS and CITY placeholders which will be replaced by their real values.</i>', 'woocommerce-booking' ); ?>">
														</div>
														<div class="rb1-right">
															<div class="rb1-row flx-center">
																<div class="rb-col">
																	<input class="ib-md" type="text"
																		v-model="data.settings.bkap_calendar_event_location">
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Event Summary (name)', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="">
														<textarea class="ta-sm"
															v-model="data.settings.bkap_calendar_event_summary"></textarea>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Event Description', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'You can use the following placeholders which will be replaced by their real values:&nbsp;SITE_NAME, CLIENT, PRODUCT_NAME, PRODUCT_WITH_QTY, RESOURCE, PERSONS, ORDER_DATE_TIME, ORDER_DATE, ORDER_NUMBER, PRICE, PHONE, NOTE, ADDRESS, EMAIL (Client\'s email), ZOOM_MEETING, CITY, COUNTRY', 'woocommerce-booking' ); ?>">
														<textarea class="ta-sm"
															v-model="data.settings.bkap_calendar_event_description"></textarea>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="panel panel-default">
								<div class="panel-heading">
									<h2 class="panel-title" data-toggle="collapse" data-target="#collapse-customer"
										aria-expanded="false">
										<?php esc_attr_e( 'Customer Add To Calendar Button Settings', 'woocommerce-booking' ); // phpcs:ignore  ?>
									</h2>
									</p>
								</div>
								<div id="collapse-customer" class="panel-collapse collapse show">
									<div class="panel-body">
										<div class="tbl-mod-1">
											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Show Add To Calendar Button On Order received Page', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'Show add to calendar button on the order received page for the customers.', 'woocommerce-booking' ); ?>">
														<label class="el-switch el-switch-green">
															<input type="checkbox"
																v-model="data.settings.bkap_add_to_calendar_order_received_page"
																true-value="on" false-value="">
															<span class="el-switch-style"></span>
														</label>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Show Add To Calendar Button In The Customer notification Email', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'Show add to calendar button in the customer notification email.', 'woocommerce-booking' ); ?>">
														<label class="el-switch el-switch-green">
															<input type="checkbox"
																v-model="data.settings.bkap_add_to_calendar_customer_email"
																true-value="on" false-value="">
															<span class="el-switch-style"></span>
														</label>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Show Add To Calendar Button On My Account', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'Show add to calendar button on my account page for the customers.', 'woocommerce-booking' ); ?>">
														<label class="el-switch el-switch-green">
															<input type="checkbox"
																v-model="data.settings.bkap_add_to_calendar_my_account_page"
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
									<h2 class="panel-title" data-toggle="collapse" data-target="#collapse-admin"
										aria-expanded="false">
										<?php esc_attr_e( 'Admin Calendar Sync Settings', 'woocommerce-booking' ); // phpcs:ignore  ?>
									</h2>
									<p><?php esc_attr_e( 'Export Bookings To Google Calendar', 'woocommerce-booking' ); ?>
									</p>
								</div>
								<div id="collapse-admin" class="panel-collapse collapse show">
									<div class="panel-body">

										<div class="tm1-row mt-15">
											<div class="alert alert-dark alert-info" role="alert">
											<?php
												$blog_link = esc_url( 'https://www.tychesoftwares.com/synchronize-booking-dates-andor-time-google-calendar/' );

												$message = sprintf(
													/* translators: %s: Link to Tyche Softwares blog */
													__( 'Product level Google Sync is more beneficial & fully automated than the Global Level Google Sync. Click <a href="%s" target="_blank">here</a> for detailed information.', 'woocommerce-booking' ),
													$blog_link
												);

												printf(
													'<br/><div><p style="font-size:medium;"><b>%s</b></p></div>',
													wp_kses_post( $message )
												);
											?>
											</div>		
										</div>
										<div class="tbl-mod-1">
											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Integration Mode', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center ro-wrap">
														<div class="rb-flx-style mb-15">
															<div class="el-radio el-radio-green">
																<input type="radio" value="oauth" id="oauth"
																	v-model="data.settings.bkap_calendar_sync_integration_mode">
																<label for="oauth" class="el-radio-style"></label>
															</div>
															<label for="oauth"><?php esc_attr_e( 'OAuth Sync (Recommended)', 'woocommerce-booking' ); ?></label>
														</div>
														<div class="rb-flx-style mb-15">
															<div class="el-radio el-radio-green">
																<input type="radio" value="directly" id="directly"
																	v-model="data.settings.bkap_calendar_sync_integration_mode">
																<label for="directly" class="el-radio-style"></label>
															</div>
															<label for="directly" ><?php esc_attr_e( 'Service Account Sync', 'woocommerce-booking' ); ?></label>
														</div>
														<div class="rb-flx-style mb-15">
															<div class="el-radio el-radio-green">
																<input type="radio" value="manually" id="manually"
																	v-model="data.settings.bkap_calendar_sync_integration_mode">
																<label for="manually" class="el-radio-style"></label>
															</div>
															<label for="manually" ><?php esc_attr_e( 'Sync Manually', 'woocommerce-booking' ); ?></label>
														</div>
														<div class="rb-flx-style mb-15">
															<div class="el-radio el-radio-green">
																<input type="radio" value="disabled" id="disabled"
																	v-model="data.settings.bkap_calendar_sync_integration_mode">
																<label for="disabled" class="el-radio-style"></label>
															</div>
															<label for="disabled" ><?php esc_attr_e( 'Disabled', 'woocommerce-booking' ); ?></label><br>
														</div>
													</div>

													<div class="rc-flx-wrap flx-aln-center ro-wrap"
														v-show="'oauth' === data.settings.bkap_calendar_sync_integration_mode">
														<p class="instructions">
															<?php esc_attr_e( 'Recommended method to sync events with Google Calendar with minimal steps', 'woocommerce-booking' ); ?>
														</p>
													</div>

													<div class="rc-flx-wrap flx-aln-center ro-wrap"
														v-show="'directly' === data.settings.bkap_calendar_sync_integration_mode">
														<p class="instructions">
															<?php esc_attr_e( 'Traditional method to sync events with Google Calendar. Requires more steps to configure but the end result is same as OAuth Sync.', 'woocommerce-booking' ); ?>
														</p>
													</div>

													<div class="rc-flx-wrap flx-aln-center ro-wrap"
														v-show="'manually' === data.settings.bkap_calendar_sync_integration_mode">
														<p class="instructions">
															<?php esc_attr_e( 'Will add an "Add to Calendar" button in emails received on new  bookings and on Order Received page. Events will then be synced manually on click of the button.', 'woocommerce-booking' ); ?>
														</p>
													</div>

													<div class="rc-flx-wrap flx-aln-center ro-wrap"
														v-show="'disabled' === data.settings.bkap_calendar_sync_integration_mode">
														<p class="instructions">
															<?php esc_attr_e( 'Disables the integration with Google Calendar.', 'woocommerce-booking' ); ?>
														</p>
													</div>
												</div>
											</div>

											<!-- OAuth Sync -->
											<div class="tm1-row"
												v-show="'oauth' === data.settings.bkap_calendar_sync_integration_mode">
												<div class="col-left">
													<label><?php esc_attr_e( 'Instructions', 'woocommerce-booking' ); ?></label>
													<p><a class="instructions"
															v-on:click.stop="toggle_display('show_instructions_oauth')"><?php esc_attr_e( 'Click here', 'woocommerce-booking' ); ?></a>
														<?php echo wp_kses_post( __( ' to view instructions on setting up the <b>Client ID</b> and <b>Client Secret</b>.', 'woocommerce-booking' ) ); // phpcs:ignore ?>
													</p>
												</div>
												<div class="col-right" v-show="show_instructions_oauth">
													<div class="rc-flx-wrap flx-aln-center ro-wrap">
														<div class="alert alert-dark alert-info" role="alert">
															<div class="left-col"><img
																	src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info-small.svg' ); ?>"
																	alt="Info Icon"></div>
															<div class="right-col">
																<ol>
																	<li>
																		<?php
																		/* translators: %s: URL to Google Developers Console */
																		printf( wp_kses( __( 'Go to the <b>%s</b> and select a project, or create a new one. Login to your Google account if you are not already logged in.', 'woocommerce-booking' ), array( 'b' => array() ) ), '<a href="https://code.google.com/apis/console/" target="_blank">Google Developers Console</a>' );
																		?>
																	</li>
																	<li><?php esc_html_e( 'If creating a new project, give the Project name e.g \'My Booking Project\' and click on the Create button.', 'woocommerce-booking' ); ?>
																	</li>
																	<li><?php echo wp_kses( __( 'Once the project is created, the Calendar API needs to be enabled. To do so, click on <b>ENABLE API AND SERVICES</b> link and search for <b>Google Calendar API</b>, and enable it by clicking the ENABLE button.', 'woocommerce-booking' ), array( 'b' => array() ) ); ?>
																	</li>
																	<li><?php echo wp_kses( __( 'On the left, click <b>Credentials</b>. If this is your first time creating a client ID, you\'ll be prompted to configure the consent screen. Click on <b>Configure Consent Screen</b>.', 'woocommerce-booking' ), array( 'b' => array() ) ); ?>
																	</li>
																	<li><?php echo wp_kses( __( 'Go to the <b>OAuth consent screen</b>. Select User Type as <b>Internal</b> and click on the CREATE button. After that, set the <b>Application name</b> and click on the Create button.', 'woocommerce-booking' ), array( 'b' => array() ) ); ?>
																	</li>
																	<li><?php echo wp_kses( __( 'Go back to the <b>Credentials</b> tab, click <b>Create credentials</b>, then select <b>OAuth client ID</b>.', 'woocommerce-booking' ), array( 'b' => array() ) ); ?>
																	</li>
																	<li><?php echo wp_kses( __( 'Select <b>Web application</b> under Application type and provide the necessary information to create your project\'s credentials.', 'woocommerce-booking' ), array( 'b' => array() ) ); ?>
																	</li>
																	<li><?php echo wp_kses( __( 'For <b>Authorized redirect URIs</b> enter the Redirect URI (Can be found in Booking menu > Settings > Google Calendar Sync). Then click Create button.', 'woocommerce-booking' ), array( 'b' => array() ) ); ?>
																	</li>
																	<li><?php echo wp_kses( __( 'On the dialog that appears, you\'ll see your <b>Client ID</b> and <b>Client Secret</b>. Fill the details in below fields and click on Save Settings.', 'woocommerce-booking' ), array( 'b' => array() ) ); ?>
																	</li>
																	<li><?php echo wp_kses( __( 'Once the Successful Connection to Google, <b>Calendar to be used</b> option will appear. Here select the calendar to which the event should get created for the booking.', 'woocommerce-booking' ), array( 'b' => array() ) ); ?>
																	</li>
																	</ul>
															</div>
														</div>
													</div>
												</div>
											</div>

											<div class="tm1-row"
												v-show="'oauth' === data.settings.bkap_calendar_sync_integration_mode">
												<div class="col-left">
													<label><?php esc_attr_e( 'Client ID', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="">
														<textarea class="ta-sm textarea-like-input"
															v-model="data.settings.bkap_calendar_oauth_integration.client_id"
															:readonly="'' !== data.settings.logout_url"></textarea>
													</div>
												</div>
											</div>

											<div class="tm1-row"
												v-show="'oauth' === data.settings.bkap_calendar_sync_integration_mode">
												<div class="col-left">
													<label><?php esc_attr_e( 'Client Secret', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="">
														<textarea class="ta-sm textarea-like-input"
															v-model="data.settings.bkap_calendar_oauth_integration.client_secret"
															:readonly="'' !== data.settings.logout_url"></textarea>
													</div>
												</div>
											</div>

											<div class="tm1-row"
												v-show="'oauth' === data.settings.bkap_calendar_sync_integration_mode">
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
															class="ta-sm textarea-like-input bkap-google-calendar-redirect-uri"
															readonly="readonly">{{data.settings.redirect_uri}}</textarea>
														<a href="javascript:void(0)"
															data-selector-to-copy="textarea.bkap-google-calendar-redirect-uri"
															data-tip="<?php esc_attr_e( 'Redirect URI has been copied!', 'woocommerce-booking' ); ?>"
															class="dashicons dashicons-admin-page copy-to-clipboard">&nbsp;</a>
													</div>
												</div>
											</div>

											<div class="tm1-row"
												v-show="'oauth' === data.settings.bkap_calendar_sync_integration_mode && Object.keys(data.settings.calendars).length > 1">
												<div class="col-left">
													<label><?php esc_attr_e( 'Select Calendar', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="">
														<select class="ib-md"
															v-model="data.settings.bkap_calendar_oauth_integration.calendar_id">
															<option v-for="(value, key) in data.settings.calendars"
																:key="key" v-bind:value="key">{{value}}</option>
														</select>
													</div>
												</div>
											</div>

											<div class="tm1-row"
												v-show="'' !== data.settings.connect_link &&'' != data.settings.bkap_calendar_oauth_integration.client_secret && '' !== data.settings.bkap_calendar_oauth_integration.client_id && 'oauth' === data.settings.bkap_calendar_sync_integration_mode && '' !== data.settings.redirect_uri && '' === data.settings.logout_url">
												<div class="col-left">
												</div>
												<div class="col-right">
													<a v-bind:href="data.settings.connect_link"
														class="secondary-btn pl-ss-btn">{{data.label.connect_to_google_calendar}}</a>
												</div>
											</div>

											<div class="tm1-row"
												v-show="Object.keys(data.settings.calendars).length > 0 && 'oauth' === data.settings.bkap_calendar_sync_integration_mode && '' !== data.settings.logout_url">
												<div class="col-left">
												</div>
												<div class="col-right">
													<a v-on:click.stop="logout_from_google_calendar"
														class="secondary-btn pl-ss-btn">{{data.label.logout_from_google_calendar}}</a>
												</div>
											</div>

											<!-- Service Account Sync -->
											<div class="tm1-row"
												v-show="'directly' === data.settings.bkap_calendar_sync_integration_mode">
												<div class="col-left">
													<label><?php esc_attr_e( 'Instructions', 'woocommerce-booking' ); ?></label>
													<p><a class="instructions"
															v-on:click.stop="toggle_display('show_instructions_directly')"><?php esc_attr_e( 'Click here', 'woocommerce-booking' ); ?></a>
															<?php echo wp_kses_post( __( ' to view instructions on setting up the <b>JSON File</b> and <b>Calendar to be used</b>.', 'woocommerce-booking' ) ); // phpcs:ignore ?>
													</p>
												</div>
												<div class="col-right" v-show="show_instructions_directly">
													<div class="rc-flx-wrap flx-aln-center ro-wrap">
														<div class="alert alert-dark alert-info" role="alert">
															<div class="left-col"><img
																	src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info-small.svg' ); ?>"
																	alt="Info Icon"></div>
															<div class="right-col">
																<ol>
																	<li><?php esc_html_e( 'Google Calendar API requires PHP V5.3+ and some PHP extensions.', 'woocommerce-booking' ); ?> </li>
																	<li><?php esc_html_e( 'Go to Google APIs console by clicking ', 'woocommerce-booking' ); ?><a href="https://code.google.com/apis/console/" target="_blank">https://code.google.com/apis/console/</a><?php esc_html_e( '. Login to your Google account if you are not already logged in.', 'woocommerce-booking' ); ?></li>
																	<li><?php esc_html_e( "Click on 'Create Project'. Name the project 'Booking & Appointment' (or use your chosen name instead) and create the project.", 'woocommerce-booking' ); ?></li>
																	<li><?php esc_html_e( 'Click on APIs & Services from the left side panel. Select the Project created. ', 'woocommerce-booking' ); ?></li>
																	<li><?php esc_html_e( "Click on 'Enable APIs and services' on the dashboard. Search for 'Google Calendar API' and enable this API.", 'woocommerce-booking' ); ?></li>
																	<li><?php esc_html_e( "Go to 'Credentials' menu in the left side pane and click on 'CREATE CREDENTIALS' link and from the dropdown that appears select 'Service account.'", 'woocommerce-booking' ); ?></li>
																	<li><?php esc_html_e( 'Enter Service account name, id, and description and Create the service account.', 'woocommerce-booking' ); ?></li>
																	<li><?php esc_html_e( 'In the next step assign Owner role under Service account permissions, keep options in the third optional step empty and click on Done button.', 'woocommerce-booking' ); ?></li>
																	<li><?php esc_html_e( "Now edit the Service account that you have created and under the 'Keys' section click on Add Key>> Create New Key, in the popup that opens select 'JSON' option and click on the CREATE button. A file with extension .json will be downloaded.", 'woocommerce-booking' ); ?></li>
																	<li><?php esc_html_e( 'The JSON file is required as you will grant access to your Google Calendar account. So this file serves as a proof of your consent to access to your Google calendar account.', 'woocommerce-booking' ); ?></li>
																	<li><?php esc_html_e( 'Select the downloaded JSON file in Upload JSON File option below and click on Upload button.', 'woocommerce-booking' ); ?></li>
																	<li><?php esc_html_e( 'Open your Google Calendar by clicking this link: ', 'woocommerce-booking' ); ?><a href="https://www.google.com/calendar/render" target="_blank">https://www.google.com/calendar/render</a></li>
																	<li><?php esc_html_e( "Create a new Calendar by clicking on '+' sign next to 'Other Calendars' section on left side pane. Try NOT to use your primary calendar.", 'woocommerce-booking' ); ?></li>
																	<li><?php esc_html_e( 'Give a name to the new calendar, e.g. Booking calendar. Check that Calendar Time Zone setting matches with time zone setting of your WordPress website. Otherwise there will be a time shift.', 'woocommerce-booking' ); ?></li>		
																	<li><?php esc_html_e( "Create the calendar and once it is created click on the Configure link which will appear at the end of the page, this will redirect you to Calendar Settings section. Paste already copied 'Service Account ID' from Manage service account of Google APIs console to 'Add People' field under 'Share with specific people'.", 'woocommerce-booking' ); ?></li>
																	<li><?php esc_html_e( "Set 'Permission Settings' of this person as 'Make changes to events' and add the person.", 'woocommerce-booking' ); ?></li>
																	<li><?php esc_html_e( "Now copy 'Calendar ID' value from Integrate Calendar section and paste the value to 'Calendar to be used' field.", 'woocommerce-booking' ); ?></li>
																	<li><?php esc_html_e( "After saving the settings, you can test the connection by clicking on the 'Test Connection' link.", 'woocommerce-booking' ); ?></li>
																	<li><?php esc_html_e( 'If you get a success message, you should see a test event inserted into the Google Calendar and you are ready to go. If you get an error message, double check your settings.', 'woocommerce-booking' ); ?></li>
																</ol>
															</div>
														</div>
													</div>
												</div>
											</div>

											<div class="tm1-row"
												v-show="'directly' === data.settings.bkap_calendar_sync_integration_mode">
												<div class="col-left">
													<label><?php esc_attr_e( 'Upload JSON File', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="row-box-1">
														<div class="rb1-right">
															<div class="rb1-row flx-center">
																<div class="rb-col">
																	<div
																		v-show="typeof data.settings.bkap_calendar_details_1.bkap_calendar_json_file_name == 'undefined' || '' == data.settings.bkap_calendar_details_1.bkap_calendar_json_file_name">
																		<input type="file" ref="jsonFileInput"
																			name="bkap_calendar_json_data"
																			id="bkap_calendar_json_data"
																			@change="handle_file_change"
																			accept=".json" />
																		<button class="secondary-btn"
																			@click="upload_json"
																			id="bkap_upload_json_data"><?php esc_attr_e( 'Upload', 'woocommerce-booking' ); ?></button>
																	</div>
																	<div v-show="typeof data.settings.bkap_calendar_details_1.bkap_calendar_json_file_name !== 'undefined' && '' != data.settings.bkap_calendar_details_1.bkap_calendar_json_file_name"
																		id="bkap_save_json_data_connection">
																		<a id="bkap_connected_json_data"
																			name="bkap_connected_json_data"><b><?php esc_attr_e( 'Uploaded File:', 'woocommerce-booking' ); ?></b><span
																				id="bkap_json_file_name">{{data.settings.bkap_calendar_details_1.bkap_calendar_json_file_name}}</span></a>
																		<button class="secondary-btn"
																			@click="remove_json"
																			id="bkap_disconnect_json_data">
																			<?php esc_attr_e( 'Remove', 'woocommerce-booking' ); ?></button>
																	</div>
																</div>
															</div>
															<div class="rb1-row">
																<span v-html="json_file_upload_message"></span>
															</div>
														</div>
													</div>
												</div>
											</div>

											<div class="tm1-row"
												v-show="'directly' === data.settings.bkap_calendar_sync_integration_mode">
												<div class="col-left">
													<label><?php esc_attr_e( 'Calendar To Be Used', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="row-box-1">
														<div class="rb1-left">
															<img class="tt-info"
																src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
																alt="Tooltip" data-toggle="tooltip" data-placement="top"
																title="<?php esc_attr_e( 'Enter the ID of the calendar in which your bookings will be saved, e.g. abcdefg1234567890@group.calendar.google.com', 'woocommerce-booking' ); ?>">
														</div>
														<div class="rb1-right">
															<div class="rb1-row flx-center">
																<div class="rb-col">
																	<input class="ib-lg" type="text"
																		v-model="data.settings.bkap_calendar_details_1.bkap_calendar_id">
																</div>
															</div>
															<div class="rb1-row flx-center">
																<div class="rb-col"
																	v-show="'' !== data.settings.bkap_calendar_details_1.bkap_calendar_id && '' !== data.settings.bkap_calendar_details_1.bkap_calendar_json_file_name">
																	<input class="secondary-btn"
																		v-on:click.stop="test_gcal_connection"
																		v-show="!show_test_loader_gcal"
																		value="<?php esc_attr_e( 'Test Connection', 'woocommerce-booking' ); ?>"
																		type="submit">
																	<button class="secondary-btn" type="button" disabled
																		v-show="show_test_loader_gcal">
																		<span class="spinner-border spinner-border-sm"
																			role="status" aria-hidden="true"></span>
																		<?php esc_attr_e( 'Testing, please wait...', 'woocommerce-booking' ); ?>
																	</button>
																</div>
																<!-- <span class="link-wul secondary-btn"
																	v-on:click.stop="test_gcal_connection">
																	<?php //esc_attr_e( 'Test Connection', 'woocommerce-booking' ); ?>
																	<img class="tt-info"
																		src="<?php //echo esc_url( BKAP_IMAGE_URL . 'loader.png' ); ?>"
																		alt="Loader" v-show="show_inline_loader_gcal">
																</span> -->
															</div>

															
															<div class="rb1-row">
																<span v-html="test_connection_message"></span>
															</div>
														</div>
													</div>
												</div>
											</div>

											<div class="tm1-row"
												v-show="'oauth' === data.settings.bkap_calendar_sync_integration_mode || 'directly' === data.settings.bkap_calendar_sync_integration_mode">
												<div class="col-left">
													<label><?php esc_attr_e( 'Show Add To Calendar button On View Bookings page', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="
															<?php
															echo wp_kses(
																__( 'Show Add to Calendar button on the Booking -> View Bookings page.<br><i>Note: This button can be used to export the already placed orders with future bookings from the current date to the calendar used above.</i>', 'woocommerce-booking' ),
																array(
																	'br' => array(),
																	'i'  => array(),
																)
															);
															?>
															">
														<label class="el-switch el-switch-green">
															<input type="checkbox"
																v-model="data.settings.bkap_admin_add_to_calendar_view_booking"
																true-value="on" false-value="">
															<span class="el-switch-style"></span>
														</label>
													</div>
												</div>
											</div>

											<div class="tm1-row"
												v-show="'manually' === data.settings.bkap_calendar_sync_integration_mode">
												<div class="col-left">
													<label><?php esc_attr_e( 'Show Add To Calendar Button In New Order Email Notification', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_html_e( 'Show "Add to Calendar" button in the New Order email notification.', 'woocommerce-booking' ); ?>">
														<label class="el-switch el-switch-green">
															<input type="checkbox"
																v-model="data.settings.bkap_admin_add_to_calendar_email_notification"
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

				<div class="col-md-12 mt-20">
					<div class="wbc-accordion">
						<div class="panel-group bkap-accordian" id="wbc-accordion">
							<div class="panel panel-default">
								<div class="panel-heading">
									<h2 class="panel-title" data-toggle="collapse" data-target="#collapse-import"
										aria-expanded="false">
										<?php esc_attr_e( 'Import Events', 'woocommerce-booking' ); // phpcs:ignore  ?>
									</h2>
									<p><?php esc_attr_e( 'Events will be imported using the ICS Feed URL. Each event will create a new WooCommerce Order once that event gets mapped to the product successfully. The event\'s date & time will be set as the item\'s Booking Date & Time. Lockout will be updated for the product for the set Booking Date & Time.', 'woocommerce-booking' ); ?>
									</p>
								</div>
								<div id="collapse-import" class="panel-collapse collapse show">
									<div class="panel-body">
										<div class="tbl-mod-1">
											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Run Automated Cron After X Minutes', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="row-box-1">
														<div class="rb1-left">
															<img class="tt-info"
																src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
																alt="Tooltip" data-toggle="tooltip" data-placement="top"
																title="<?php esc_attr_e( 'The duration in minutes after which a cron job will be run automatically importing events from all the icalendar/.ics feed urls.<br><i>Note: setting it to a lower number can affect the site perfomance.</i>', 'woocommerce-booking' ); ?>">
														</div>
														<div class="rb1-right">
															<div class="rb1-row flx-center">
																<div class="rb-col">
																	<input class="ib-sm" type="number" min=0
																		v-model="data.settings.bkap_cron_time_duration">
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Instructions', 'woocommerce-booking' ); ?></label>
													<p><a class="instructions"
															v-on:click.stop="toggle_display('show_instructions_import_events')"><?php esc_attr_e( 'Click here', 'woocommerce-booking' ); ?></a>
														<?php esc_attr_e( ' to view instructions on setting up import events using ics feed urls', 'woocommerce-booking' ); // phpcs:ignore ?>
													</p>
												</div>
												<div class="col-right" v-show="show_instructions_import_events">
													<div class="rc-flx-wrap flx-aln-center ro-wrap">
														<div class="alert alert-dark alert-info" role="alert">
															<div class="left-col"><img
																	src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info-small.svg' ); ?>"
																	alt="Info Icon"></div>
															<div class="right-col">
																<ol>
																	<li>
																		<?php
																		/* translators: %s: URL to Google calendar */
																		printf( wp_kses( __( 'Open your Google Calendar by clicking this link: %s', 'woocommerce-booking' ), array( 'b' => array() ) ), '<a href="https://www.google.com/calendar/render" target="_blank">https://www.google.com/calendar/render</a>' );
																		?>
																	</li>
																	<li><?php esc_attr_e( 'Select the calendar to be imported and click "Calendar settings".', 'woocommerce-booking' ); ?>
																	</li>
																	<li><?php esc_attr_e( 'Click on "ICAL" button in Calendar Address option. Please note that you need to select the Private Calendar Address "ICAL" if your calendar is not public.', 'woocommerce-booking' ); ?>
																	</li>
																	<li><?php esc_attr_e( 'Copy the basic.ics file URL.', 'woocommerce-booking' ); ?>
																	</li>
																	<li><?php esc_attr_e( 'Paste this link in the text box under Google Calendar Sync tab->Import Events->iCalendar/.ics Feed URL.', 'woocommerce-booking' ); ?>
																	</li>
																	<li><?php esc_attr_e( 'Save the URL.', 'woocommerce-booking' ); ?>
																	</li>
																	<li><?php esc_attr_e( 'Click on "Import Events" button to import the events from the calendar.', 'woocommerce-booking' ); ?>
																	</li>
																	<li><?php esc_attr_e( 'You can import multiple calendars by using ICS feeds. Add them using the Add New ICS Feed URL button.', 'woocommerce-booking' ); ?>
																	</li>
																	</ul>
															</div>
														</div>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'iCalendar/.ics Feed URL', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="row-box-1">
														<div class="rb1-left">
															<img class="tt-info"
																src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
																alt="Tooltip" data-toggle="tooltip" data-placement="top"
																title="">
														</div>
														<div class="rb1-right">
															<div class="rb1-row flx-center">
																<div class="alert alert-info alert-dark" role="alert"
																	v-show="show_imported_success_message">
																	<div class="left-col"><img
																			src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info-small.svg' ); ?>"
																			alt="Info Icon"></div>
																	<div class="right-col">
																		<p>{{data.label.imported_success_message}}
																		</p>
																	</div>
																</div>

																<div class="alert alert-dangerk" role="alert"
																	v-show="show_imported_error_message">
																	<div class="left-col"><img
																			src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info-small.svg' ); ?>"
																			alt="Info Icon"></div>
																	<div class="right-col">
																		<p>{{data.label.imported_error_message}}</p>
																	</div>
																</div>

																<div class="alert alert-info alert-dark mb-0"
																	role="alert" v-show="show_ics_url_message">
																	<div class="left-col"><img
																			src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info-small.svg' ); ?>"
																			alt="Info Icon"></div>
																	<div class="right-col">
																		<p v-html="ics_url_message"></p>
																	</div>
																</div>
															</div>
															<div class="rb1-row flx-center mb-10"
																v-for="(row, index) in data.settings.bkap_ics_feed_urls">
																<div class="rb-col">
																	<input class="ib-md" type="text" v-model="row.url">
																</div>
																<div class="rb-col">
																	<input class="secondary-btn"
																		:class="!row.save ? 'btn-disabled': ''"
																		:disabled="!row.save"
																		v-on:click.stop="save_ics_url(row)"
																		value="<?php esc_attr_e( 'Save', 'woocommerce-booking' ); ?>"
																		type="submit">
																</div>
																<div class="rb-col">
																	<input class="secondary-btn"
																		:class="!row.import ? 'btn-disabled': ''"
																		:disabled="!row.import"
																		v-on:click.stop="import_ics_event(index)"
																		v-show="!row.is_importing"
																		value="<?php esc_attr_e( 'Import', 'woocommerce-booking' ); ?>"
																		type="submit">
																	<button class="secondary-btn" type="button" disabled
																		v-show="row.is_importing">
																		<span class="spinner-border spinner-border-sm"
																			role="status" aria-hidden="true"></span>
																		<?php esc_attr_e( 'Importing', 'woocommerce-booking' ); ?>
																	</button>
																</div>
																<div class="rb-col">
																	<a v-on:click.stop="delete_ics_url(row,index)"
																		:class="!row.delete ? 'btn-disabled': 'delete-icon-active'">
																		<svg xmlns="http://www.w3.org/2000/svg"
																			width="25" height="25" fill="currentColor"
																			class="bi bi-trash" viewBox="0 0 16 16">
																			<path
																				d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z">
																			</path>
																			<path
																				d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z">
																			</path>
																		</svg>
																	</a>
																</div>
															</div>
															<div class="rb1-row">
																<a class="link-vol-ul"
																	v-on:click.stop="add_new_ics_url"><?php esc_attr_e( 'Add New ICS feed URL', 'woocommerce-booking' ); ?></a>
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
