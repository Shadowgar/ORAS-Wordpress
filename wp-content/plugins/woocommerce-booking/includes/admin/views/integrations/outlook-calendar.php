<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * Outlook Calendar.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/Views/Integrations/OutlookCalendar
 * @since       5.19.0
 */

?>
<template id="outlook-calendar-tab">
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

				<div class="col-md-12">
					<div class="bkap-page-head phw-btn">
						<div class="col-left">
                            <h1><?php esc_attr_e( 'Outlook Calendar Sync', 'woocommerce-booking' ); // phpcs:ignore ?>
							</h1>
                            <p><?php esc_attr_e( 'Connect your bookings to Outlook Calendar.', 'woocommerce-booking' ); // phpcs:ignore ?>
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
                                        <?php esc_attr_e( 'General Settings', 'woocommerce-booking' ); // phpcs:ignore  ?>
									</h2>
								</div>
								<div id="collapseOne" class="panel-collapse collapse show">
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
																title="<?php esc_attr_e( 'Enter the text that will be used as location field in event of the Calendar. If left empty, website description is sent instead. <br><i>Note: You can use ADDRESS and CITY placeholders which will be replaced by their real values.</i>', 'woocommerce-booking' ); ?>">
														</div>
														<div class="rb1-right">
															<div class="rb1-row flx-center">
																<div class="rb-col">
																	<input class="ib-md" type="text"
																		v-model="data.settings.bkap_outlook_calendar_event_location">
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Event summary (name)', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="">
														<textarea class="ta-sm"
															v-model="data.settings.bkap_outlook_calendar_event_summary"></textarea>
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
															v-model="data.settings.bkap_outlook_calendar_event_description"></textarea>
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
                                        <?php esc_attr_e( 'Outlook Calendar Sync Settings', 'woocommerce-booking' ); // phpcs:ignore  ?>
									</h2>
								</div>

								<div id="collapseOne" class="panel-collapse collapse show">
									<div class="panel-body">
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
																	v-model="data.settings.bkap_outlook_calendar_integration">
																<label for="oauth" class="el-radio-style"></label>
															</div>
															<label><?php esc_attr_e( 'OAuth Sync', 'woocommerce-booking' ); ?></label>
														</div>
														<div class="rb-flx-style mb-15">
															<div class="el-radio el-radio-green">
																<input type="radio" value="disabled" id="disabled"
																	v-model="data.settings.bkap_outlook_calendar_integration">
																<label for="disabled" class="el-radio-style"></label>
															</div>
															<label><?php esc_attr_e( 'Disabled', 'woocommerce-booking' ); ?></label><br>
														</div>
													</div>

													<div class="rc-flx-wrap flx-aln-center ro-wrap"
														v-show="'oauth' === data.settings.bkap_outlook_calendar_integration">
														<p class="instructions">
															<?php esc_attr_e( 'Recommended method to sync events with Outlook Calendar with minimal steps', 'woocommerce-booking' ); ?>
														</p>
													</div>
													<div class="rc-flx-wrap flx-aln-center ro-wrap"
														v-show="'disabled' === data.settings.bkap_outlook_calendar_integration">
														<p class="instructions">
															<?php esc_attr_e( 'Disables the integration with Outlook Calendar.', 'woocommerce-booking' ); ?>
														</p>
													</div>
												</div>
											</div>

											<!-- OAuth Sync -->
											<div class="tm1-row"
												v-show="'oauth' === data.settings.bkap_outlook_calendar_integration">
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
																	<?php /* Translators: %s Link to azure portal */ ?>
																	<li><?php echo wp_kses_post( sprintf( __( 'Sign in to the <a href="%s" target="_blank">Azure portal</a> using either a work or school account or a personal Microsoft account.', 'woocommerce-booking' ), 'https://portal.azure.com/' ) ); ?></li>
																	<li><?php esc_html_e( 'In the left-hand navigation pane, select the Azure Active Directory service, and then select App registrations New registration.', 'woocommerce-booking' ); ?></li>
																	<li><?php esc_html_e( 'When the "Register an application" page appears, enter your application\'s Name and select Supported account types as Accounts in any organizational directory (Any Azure AD directory - Multitenant) and personal Microsoft accounts (e.g. Skype, Xbox).', 'woocommerce-booking' ); ?></li>
																	<li><?php esc_html_e( 'Next, you will have to enter the Redirect URI (optional) setting. Select the Web option in the dropdown. Under the "e.g field" you will have to enter a link. Copy it from the Redirect URI field given on this page. Scroll down and you will find it located right after the Client Secret field.', 'woocommerce-booking' ); ?></li>
																	<li><?php esc_html_e( 'Paste it in the "e.g field" When finished, select Register.', 'woocommerce-booking' ); ?></li>
																	<li><?php esc_html_e( 'Azure AD assigns a unique application (client) ID to your app, and you\'re taken to your application\'s Overview page.', 'woocommerce-booking' ); ?></li>
																	<li><?php esc_html_e( 'Copy the Application (client) ID. You\'ll use it in the Client ID field.', 'woocommerce-booking' ); ?></li>
																	<li><?php esc_html_e( 'Under Certificates & secrets, choose New client secret, set Expires to Never and click Add. Copy the app secret value from the Client secrets list before leaving the page. Insert the secret into the Client secret field.', 'woocommerce-booking' ); ?></li>
																	<li><?php esc_html_e( 'Under API permissions, choose Add a permission, and select Microsoft Graph Delegated permissions. In Select permissions find Calendars.ReadWrite, select it and click the Add permissions button.', 'woocommerce-booking' ); ?></li>
																</ol>
															</div>
														</div>
													</div>
												</div>
											</div>

											<div class="tm1-row"
												v-show="'oauth' === data.settings.bkap_outlook_calendar_integration">
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
															v-model="data.settings.bkap_outlook_calendar_client_key"></textarea>
													</div>
												</div>
											</div>

											<div class="tm1-row"
												v-show="'oauth' === data.settings.bkap_outlook_calendar_integration">
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
															v-model="data.settings.bkap_outlook_calendar_client_secret"></textarea>
													</div>
												</div>
											</div>

											<div class="tm1-row"
												v-show="'oauth' === data.settings.bkap_outlook_calendar_integration && '' !== data.settings.redirect_uri">
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
															class="ta-sm textarea-like-input woocommerce-booking-redirect-uri"
															readonly="readonly">{{data.settings.redirect_uri}}</textarea>
														<a href="javascript:void(0)"
															data-selector-to-copy="textarea.woocommerce-booking-redirect-uri"
															data-tip="<?php esc_attr_e( 'Redirect URI has been copied!', 'woocommerce-booking' ); ?>"
															class="dashicons dashicons-admin-page copy-to-clipboard">&nbsp;</a>
													</div>
												</div>
											</div>

											<div class="tm1-row" v-show="data.settings.calendars.length > 0">
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
															v-model="data.settings.bkap_outlook_calendar_id">
															<option v-for="list in data.settings.calendars"
																v-bind:value="list.id">{{list.name}}</option>
														</select>
													</div>
												</div>
											</div>

											<div class="tm1-row"
												v-show="0 === data.settings.calendars.length && 'oauth' === data.settings.bkap_outlook_calendar_integration && '' !== data.settings.redirect_uri">
												<div class="col-left">
												</div>
												<div class="col-right">
													<a v-bind:href="data.settings.connect_link"
														class="secondary-btn pl-ss-btn">{{data.label.connect_to_outlook}}</a>
												</div>
											</div>

											<div class="tm1-row"
												v-show="data.settings.calendars.length > 0 && 'oauth' === data.settings.bkap_outlook_calendar_integration && '' !== data.settings.logout_url">
												<div class="col-left">
												</div>
												<div class="col-right">
													<a v-bind:href="data.settings.logout_url"
														class="secondary-btn pl-ss-btn">{{data.label.logout_from_outlook}}</a>
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
