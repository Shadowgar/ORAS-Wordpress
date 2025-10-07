<div class="bkap_admin_loader" v-show="data.loader.loader_saving_integrations_settings">
	<div class="bkap_admin_loader_wrapper">
		{{data.settings.labels.loader_saving_integrations_settings}} <img
			src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
	</div>
</div>

<div class="bkap_admin_loader"
	v-show="data.toggle_integrations.google_calendar_integration_mode.show_logout_from_calendar_loader">
	<div class="bkap_admin_loader_wrapper">
		{{data.label.logout_from_calendar_loader}} <img
			src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
	</div>
</div>

<div class="col-md-12" v-show="data.sidebar.items.integrations" id="booking-metabox-integrations">
	<div class="wbc-accordion">
		<div class="panel-group bkap-metabox-accordion" id="wbc-accordion">
			<div class="panel">
				<div class="panel-heading">
					<h2 class="panel-title" data-toggle="collapse" data-target="#collapseGoogleCalendar"
						aria-expanded="false">
                        <?php esc_attr_e( 'Google Calendar', 'woocommerce-booking' ); // phpcs:ignore  ?>
					</h2>
				</div>
				<div id="collapseGoogleCalendar" class="panel-collapse collapse">
					<div class="panel-body">
						<div class="tbl-mod-1 tbl-metabox" v-if="data.integrations.settings.is_grouped_product">
							<div class="alert alert-danger alert-dismissible fade show" role="alert">
								<span v-html="data.label.error_is_grouped_product"></span>
								<button type="button" class="close" data-dismiss="alert" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
						</div>

						<h2 v-if="!data.integrations.settings.is_grouped_product">
							<?php esc_attr_e( 'Export Bookings To Google Calendar', 'woocommerce-booking' ); ?></h2>

						<div class="tbl-mod-1 tbl-metabox" v-if="!data.integrations.settings.is_grouped_product">
							<div class="container-fluid pl-info-wrap"
								v-show="'' != data.integrations.settings.bkap_gcal_success">
								<div class="row">
									<div class="col-md-12">
										<div class="alert alert-success alert-dismissible fade show" role="alert">
											<span v-html="data.integrations.settings.bkap_gcal_success"></span>
											<button type="button" class="close" data-dismiss="alert" aria-label="Close">
												<span aria-hidden="true">&times;</span>
											</button>
										</div>
									</div>
								</div>
							</div>

							<div v-show="'' != data.integrations.settings.bkap_gcal_failure">
								<div class="alert alert-danger alert-dismissible fade show" role="alert">
									<span v-html="data.integrations.settings.bkap_gcal_failure"></span>
								</div>
							</div>

							<div class="tm1-row">
								<div class="col-left">
									<label><?php esc_attr_e( 'Integration Mode', 'woocommerce-booking' ); ?></label>
								</div>
								<div class="col-right">
									<div class="rc-flx-wrap flx-aln-center ro-wrap">
										<div class="rb-flx-style mb-15">
											<div class="el-radio el-radio-green">
												<input type="radio" value="oauth" id="oauth"
													v-model="data.integrations.settings.product_sync_integration_mode">
												<label for="oauth" class="el-radio-style"></label>
											</div>
											<label
												for="oauth"><?php esc_attr_e( 'OAuth Sync (Recommended)', 'woocommerce-booking' ); ?></label>
										</div>
										<div class="rb-flx-style mb-15">
											<div class="el-radio el-radio-green">
												<input type="radio" value="directly" id="directly"
													v-model="data.integrations.settings.product_sync_integration_mode">
												<label for="directly" class="el-radio-style"></label>
											</div>
											<label
												for="directly"><?php esc_attr_e( 'Service Account Sync', 'woocommerce-booking' ); ?></label>
										</div>
										<div class="rb-flx-style mb-15">
											<div class="el-radio el-radio-green">
												<input type="radio" value="disabled" id="disabled"
													v-model="data.integrations.settings.product_sync_integration_mode">
												<label for="disabled" class="el-radio-style"></label>
											</div>
											<label
												for="disabled"><?php esc_attr_e( 'Disabled', 'woocommerce-booking' ); ?></label><br>
										</div>
									</div>

									<div class="rc-flx-wrap flx-aln-center ro-wrap"
										v-show="'oauth' === data.integrations.settings.product_sync_integration_mode">
										<p class="instructions">
											<?php esc_attr_e( 'Recommended method to sync events with Google Calendar with minimal steps', 'woocommerce-booking' ); ?>
										</p>
									</div>

									<div class="rc-flx-wrap flx-aln-center ro-wrap"
										v-show="'directly' === data.integrations.settings.product_sync_integration_mode">
										<p class="instructions">
											<?php esc_attr_e( 'Traditional method to sync events with Google Calendar. Requires more steps to configure but the end result is same as OAuth Sync.', 'woocommerce-booking' ); ?>
										</p>
									</div>

									<div class="rc-flx-wrap flx-aln-center ro-wrap"
										v-show="'disabled' === data.integrations.settings.product_sync_integration_mode">
										<p class="instructions">
											<?php esc_attr_e( 'Disables the integration with Google Calendar.', 'woocommerce-booking' ); ?>
										</p>
									</div>
								</div>
							</div>

							<!-- OAuth Sync -->
							<div class="tm1-row"
								v-show="'oauth' === data.integrations.settings.product_sync_integration_mode">
								<div class="col-left">
									<label><?php esc_attr_e( 'Instructions', 'woocommerce-booking' ); ?></label>
                                    <p><?php esc_html_e( 'To find your Client ID and Client Secret, please follow the instructions', 'woocommerce-booking' ); // phpcs:ignore ?>
										<a class="instructions"
											href="https://www.tychesoftwares.com/docs/docs/booking-appointment-plugin-for-woocommerce-new/integrations/google-calendar/"
											target="_blank"><?php esc_attr_e( 'here', 'woocommerce-booking' ); ?></a>
									</p>
								</div>
								<div class="col-right">
								</div>
							</div>

							<div class="tm1-row"
								v-show="'oauth' === data.integrations.settings.product_sync_integration_mode">
								<div class="col-left">
									<label><?php esc_attr_e( 'Client ID', 'woocommerce-booking' ); ?></label>
								</div>
								<div class="col-right">
									<div class="rc-flx-wrap flx-aln-center">
										<img class="tt-info"
											src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
											alt="Tooltip" data-toggle="tooltip" data-placement="top" title="">
										<textarea class="ta-sm textarea-like-input"
											v-model="data.integrations.settings.bkap_calendar_oauth_integration.client_id" :readonly="'' !== data.integrations.settings.bkap_calendar_oauth_integration.logout_url"></textarea>
									</div>
								</div>
							</div>

							<div class="tm1-row"
								v-show="'oauth' === data.integrations.settings.product_sync_integration_mode">
								<div class="col-left">
									<label><?php esc_attr_e( 'Client Secret', 'woocommerce-booking' ); ?></label>
								</div>
								<div class="col-right">
									<div class="rc-flx-wrap flx-aln-center">
										<img class="tt-info"
											src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
											alt="Tooltip" data-toggle="tooltip" data-placement="top" title="">
										<textarea class="ta-sm textarea-like-input"
											v-model="data.integrations.settings.bkap_calendar_oauth_integration.client_secret" :readonly="'' !== data.integrations.settings.bkap_calendar_oauth_integration.logout_url"></textarea>
									</div>
								</div>
							</div>

							<div class="tm1-row"
								v-show="'oauth' === data.integrations.settings.product_sync_integration_mode">
								<div class="col-left">
									<label><?php esc_attr_e( 'Redirect URI', 'woocommerce-booking' ); ?></label>
								</div>
								<div class="col-right">
									<div class="rc-flx-wrap flx-aln-center">
										<img class="tt-info"
											src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
											alt="Tooltip" data-toggle="tooltip" data-placement="top" title="">
										<textarea class="ta-sm textarea-like-input bkap-google-calendar-redirect-uri"
											readonly="readonly">{{data.integrations.settings.bkap_calendar_oauth_integration.redirect_uri}}</textarea>
										<a href="javascript:void(0)"
											data-selector-to-copy="textarea.bkap-google-calendar-redirect-uri"
											data-tip="<?php esc_attr_e( 'Redirect URI has been copied!', 'woocommerce-booking' ); ?>"
											class="dashicons dashicons-admin-page copy-to-clipboard">&nbsp;</a>
									</div>
								</div>
							</div>

							<div class="tm1-row"
								v-show="'oauth' === data.integrations.settings.product_sync_integration_mode && Object.keys(data.integrations.settings.bkap_calendar_oauth_integration.calendars).length > 1">
								<div class="col-left">
									<label><?php esc_attr_e( 'Select Calendar', 'woocommerce-booking' ); ?></label>
								</div>
								<div class="col-right">
									<div class="rc-flx-wrap flx-aln-center">
										<img class="tt-info"
											src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
											alt="Tooltip" data-toggle="tooltip" data-placement="top" title="">
										<select class="ib-md"
											v-model="data.integrations.settings.bkap_calendar_oauth_integration.calendar_id">
											<option
												v-for="(value, key) in data.integrations.settings.bkap_calendar_oauth_integration.calendars"
												:key="key" v-bind:value="key">{{value}}</option>
										</select>
									</div>
								</div>
							</div>

							<div class="tm1-row"
								v-show="Object.keys(data.integrations.settings.bkap_calendar_oauth_integration.calendars).length < 2 && 'oauth' === data.integrations.settings.product_sync_integration_mode && '' !== data.integrations.settings.bkap_calendar_oauth_integration.redirect_uri && '' === data.integrations.settings.bkap_calendar_oauth_integration.logout_url && '' !== data.integrations.settings.bkap_calendar_oauth_integration.connect_link">
								<div class="col-left">
								</div>
								<div class="col-right">
									<a v-bind:href="data.integrations.settings.bkap_calendar_oauth_integration.connect_link"
										class="secondary-btn pl-ss-btn">{{data.label.connect_to_google_calendar}}</a>
								</div>
							</div>

							<div class="tm1-row"
								v-show="Object.keys(data.integrations.settings.bkap_calendar_oauth_integration.calendars).length > 0 && 'oauth' === data.integrations.settings.product_sync_integration_mode && '' !== data.integrations.settings.bkap_calendar_oauth_integration.logout_url">
								<div class="col-left">
								</div>
								<div class="col-right">
									<a v-on:click.stop="data.fn.integrations.logout_from_google_calendar(data)"
										class="secondary-btn pl-ss-btn">{{data.label.logout_from_google_calendar}}</a>
								</div>
							</div>

							<!-- Service Account Sync -->
							<div class="tm1-row"
								v-show="'directly' === data.integrations.settings.product_sync_integration_mode">
								<div class="col-left">
									<label><?php esc_attr_e( 'Instructions', 'woocommerce-booking' ); ?></label>
                                    <p><?php esc_html_e( 'To setup the Service Account Sync option, please follow the instructions', 'woocommerce-booking' ); // phpcs:ignore ?>
										<a class="instructions"
											href="https://www.tychesoftwares.com/how-to-send-woocommerce-bookings-to-different-google-calendars-for-each-bookable-product/"
											target="_blank"><?php esc_attr_e( 'here', 'woocommerce-booking' ); ?></a>
									</p>
								</div>
								<div class="col-right">
								</div>
							</div>

							<div class="tm1-row"
								v-show="'directly' === data.integrations.settings.product_sync_integration_mode">
								<div class="col-left">
									<label><?php esc_attr_e( 'Upload JSON File', 'woocommerce-booking' ); ?></label>
								</div>
								<div class="col-right">
									<div class="row-box-1">
										<div class="rb1-right">
											<div class="rb1-row flx-center">
												<div class="rb-col">
													<div
														v-show="typeof data.integrations.settings.bkap_calendar_directly_integration.json_file_name == 'undefined' || '' == data.integrations.settings.bkap_calendar_directly_integration.json_file_name">
														<input type="file" ref="jsonFileInput"
															name="bkap_calendar_json_data" id="bkap_calendar_json_data"
															@change="data.fn.integrations.handle_file_change($event, data)"
															accept=".json" />
														<input type="button" class="secondary-btn"
															v-on:click.stop="data.fn.integrations.upload_json(data)"
															id="bkap_upload_json_data"
															value="<?php esc_attr_e( 'Upload', 'woocommerce-booking' ); ?>">
													</div>

													<div v-show="typeof data.integrations.settings.bkap_calendar_directly_integration.json_file_name !== 'undefined' && '' != data.integrations.settings.bkap_calendar_directly_integration.json_file_name"
														id="bkap_save_json_data_connection">
														<a id="bkap_connected_json_data"
															name="bkap_connected_json_data"><b><?php esc_attr_e( 'Uploaded File:', 'woocommerce-booking' ); ?></b><span
																id="bkap_json_file_name">{{data.integrations.settings.bkap_calendar_directly_integration.json_file_name}}</span></a>
														<input type="button" class="secondary-btn"
															v-on:click.stop="data.fn.integrations.remove_json(data)"
															id="bkap_disconnect_json_data"
															value="<?php esc_attr_e( 'Remove', 'woocommerce-booking' ); ?>">
													</div>
												</div>
											</div>
											<div class="rb1-row">
												<span v-html="data.toggle_integrations.google_calendar_integration_mode.json_file_upload_message"></span>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="tm1-row"
								v-show="'directly' === data.integrations.settings.product_sync_integration_mode">
								<div class="col-left">
									<label><?php esc_attr_e( 'Calendar to be used', 'woocommerce-booking' ); ?></label>
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
													<input class="ib-md" type="text"
														v-model="data.integrations.settings.bkap_calendar_directly_integration.calendar_id">
												</div>
											</div>
											<div class="rb1-row flx-center">
												<span class="link-wul"
													v-on:click.stop="data.fn.integrations.test_gcal_connection(data)">
													<?php esc_attr_e( 'Test Connection', 'woocommerce-booking' ); ?>
													<img class="tt-info"
														src="<?php echo esc_url( BKAP_IMAGE_URL . 'loader.png' ); ?>"
														alt="Loader"
														v-show="data.toggle_integrations.google_calendar_integration_mode.show_inline_loader_gcal">
												</span>
											</div>
											<div class="rb1-row">
												<span
													v-html="data.toggle_integrations.google_calendar_integration_mode.test_connection_message"></span>
											</div>
										</div>
									</div>
								</div>
							</div>

							<h2><?php esc_attr_e( 'Import/Mapping of Events', 'woocommerce-booking' ); ?>
							</h2>

							<div class="tm1-row">
								<div class="col-left">
									<label><?php esc_attr_e( 'Enable Automated Mapping For Imported Events', 'woocommerce-booking' ); ?></label>
								</div>
								<div class="col-right">
									<div class="rc-flx-wrap flx-aln-center">
										<img class="tt-info"
											src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
											alt="Tooltip" data-toggle="tooltip" data-placement="top"
											title="<?php esc_attr_e( 'Enable if you wish to allow for imported events to be automatically mapped to the product.', 'woocommerce-booking' ); ?>">
										<label class="el-switch el-switch-green">
											<input type="checkbox"
												v-model="data.integrations.settings.enable_automated_mapping"
												true-value="on" false-value="">
											<span class="el-switch-style"></span>
										</label>
									</div>
								</div>
							</div>

							<div class="tm1-row"
								v-show="data.integrations.settings.is_variable_product && Object.keys(data.integrations.settings.variations_for_events).length > 0">
								<div class="col-left">
									<label><?php esc_attr_e( 'Default Variation to which Events should be mapped', 'woocommerce-booking' ); ?></label>
								</div>
								<div class="col-right">
									<div class="rc-flx-wrap flx-aln-center">
										<img class="tt-info"
											src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
											alt="Tooltip" data-toggle="tooltip" data-placement="top"
											title="<?php esc_attr_e( 'Select the default variation to which the product should be mapped. If left blanks, then the first variation shall be chosen.', 'woocommerce-booking' ); ?>">
										<select class="ib-md"
											v-model="data.integrations.settings.default_variation_id_for_events" :disabled="'on' != data.integrations.settings.enable_automated_mapping">
											<option v-for="(value) in data.integrations.settings.variations_for_events"
												v-bind:value="value.variation_id">
												{{value.variation_name}}</option>
										</select>
									</div>
								</div>
							</div>

							<div class="tm1-row">
								<div>
									<label><?php esc_attr_e( 'Instructions', 'woocommerce-booking' ); ?></label>
									<p><a class="instructions"
											v-on:click.stop="data.fn.integrations.toggle_display('show_instructions_import_events',data)"><?php esc_attr_e( 'Click here', 'woocommerce-booking' ); ?></a>
                                        <?php esc_attr_e( ' to view instructions on setting up import events using ICS feed URLs', 'woocommerce-booking' ); // phpcs:ignore ?>
									</p>
									<div class="alert alert-dark alert-info" role="alert" v-show="data.toggle_integrations.google_calendar_integration_mode.show_instructions_import_events">
										<div>
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

							<div class="tm1-row">
								<div class="col-left">
									<label><?php esc_attr_e( 'iCalendar/.ics Feed URL', 'woocommerce-booking' ); ?></label>
								</div>
								<div class="col-right">
									<div class="row-box-1">
										<div class="rb1-left">
											<img class="tt-info"
												src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
												alt="Tooltip" data-toggle="tooltip" data-placement="top" title="">
										</div>
										<div class="rb1-right i-calendar">
											<div class="rb1-row flx-center">
												<div class="alert alert-info alert-dark" role="alert"
													v-show="data.toggle_integrations.google_calendar_integration_mode.show_imported_success_message">
													<div class="left-col"><img
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info-small.svg' ); ?>"
															alt="Info Icon"></div>
													<div class="right-col">
														<p>{{data.label.imported_success_message}}
														</p>
													</div>
												</div>

												<div class="alert alert-danger" role="alert"
													v-show="data.toggle_integrations.google_calendar_integration_mode.show_imported_error_message">
													<div class="left-col"><img
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info-small.svg' ); ?>"
															alt="Info Icon"></div>
													<div class="right-col">
														<p>{{data.label.imported_error_message}}</p>
													</div>
												</div>

												<div class="alert alert-info alert-dark mb-0" role="alert"
													v-show="data.toggle_integrations.google_calendar_integration_mode.show_ics_url_message">
													<div class="left-col"><img
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info-small.svg' ); ?>"
															alt="Info Icon"></div>
													<div class="right-col">
														<p
															v-html="data.toggle_integrations.google_calendar_integration_mode.ics_url_message">
														</p>
													</div>
												</div>
											</div>
											<div class="rb1-row flx-center mb-10"
												v-for="(row, index) in data.integrations.settings.bkap_ics_feed_urls">
												<div class="rb-col">
													<input class="ib-md" type="text" v-model="row.url">
												</div>
												<div class="rb-col">
													<input class="secondary-btn" :class="!row.save ? 'btn-disabled': ''"
														:disabled="!row.save"
														v-on:click.stop="data.fn.integrations.save_ics_url(row,data)"
														value="<?php esc_attr_e( 'Save', 'woocommerce-booking' ); ?>"
														type="button">
												</div>
												<div class="rb-col">
													<input class="secondary-btn"
														:class="!row.import ? 'btn-disabled': ''"
														:disabled="!row.import"
														v-on:click.stop="data.fn.integrations.import_ics_event(index, data)"
														v-show="!row.is_importing"
														value="<?php esc_attr_e( 'Import', 'woocommerce-booking' ); ?>"
														type="button">
													<button class="secondary-btn" type="button" disabled
														v-show="row.is_importing">
														<span class="spinner-border spinner-border-sm" role="status"
															aria-hidden="true"></span>
														<?php esc_attr_e( 'Importing', 'woocommerce-booking' ); ?>
													</button>
												</div>
												<div class="rb-col">
													<a v-on:click.stop="data.fn.integrations.delete_ics_url(row,index,data)"
														:class="!row.delete ? 'btn-disabled': 'delete-icon-active'">
														<svg xmlns="http://www.w3.org/2000/svg" width="25" height="25"
															fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
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
													v-on:click.stop="data.fn.integrations.add_new_ics_url(data)"><?php esc_attr_e( 'Add New ICS feed URL', 'woocommerce-booking' ); ?></a>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="rb1-row flx-center mb-3 mt-2">
								<div class="rb-col">
									<a href="javascript:void(0);" class="secondary-btn"
										v-on:click.stop="data.fn.save_settings('integrations',data)">{{data.settings.save_settings_button}}</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="panel">
				<div class="panel-heading">
					<h2 class="panel-title" data-toggle="collapse" data-target="#collapseOutlookCalendar"
						aria-expanded="false">
                        <?php esc_attr_e( 'Outlook Calendar', 'woocommerce-booking' ); // phpcs:ignore ?>
					</h2>
				</div>
				<div id="collapseOutlookCalendar" class="panel-collapse collapse">
					<div class="panel-body">
						<div class="tbl-mod-1 tbl-metabox"
							v-if="!data.integrations.settings.bkap_outlook_calendar_integration.is_outlook_calendar_addon_active">
							<div class="alert alert-danger alert-dismissible fade show" role="alert">
								<span v-html="data.label.outlook_plugin_not_activated_message"></span>
							</div>
						</div>

						<div class="tbl-mod-1 tbl-metabox"
							v-if="data.integrations.settings.bkap_outlook_calendar_integration.is_outlook_calendar_addon_active">
							<div class="tm1-row">
								<div class="col-left">
									<label><?php esc_attr_e( 'Enable Outlook Calendar', 'woocommerce-booking' ); ?></label>
								</div>
								<div class="col-right">
									<div class="rc-flx-wrap flx-aln-center">
										<img class="tt-info"
											src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
											alt="Tooltip" data-toggle="tooltip" data-placement="top"
											title="">
										<label class="el-switch el-switch-green">
											<input type="checkbox"
												v-model="data.integrations.settings.bkap_outlook_calendar_integration.is_enabled"
												true-value="on" false-value="">
											<span class="el-switch-style"></span>
										</label>
									</div>
								</div>
							</div>

							<div class="tm1-row"
								v-show="'on' === data.integrations.settings.bkap_outlook_calendar_integration.is_enabled">
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
											v-model="data.integrations.settings.bkap_outlook_calendar_integration.client_id"></textarea>
									</div>
								</div>
							</div>

							<div class="tm1-row"
								v-show="'on' === data.integrations.settings.bkap_outlook_calendar_integration.is_enabled">
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
											v-model="data.integrations.settings.bkap_outlook_calendar_integration.client_secret"></textarea>
									</div>
								</div>
							</div>

							<div class="tm1-row"
								v-show="'on' === data.integrations.settings.bkap_outlook_calendar_integration.is_enabled && '' !== data.integrations.settings.bkap_outlook_calendar_integration.redirect_uri">
								<div class="col-left">
									<label><?php esc_attr_e( 'Redirect URI', 'woocommerce-booking' ); ?></label>
								</div>
								<div class="col-right">
									<div class="rc-flx-wrap flx-aln-center">
										<img class="tt-info"
											src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
											alt="Tooltip" data-toggle="tooltip" data-placement="top"
											title="">
										<textarea class="ta-sm textarea-like-input bkap-outlook-calendar-redirect-uri"
											readonly="readonly">{{data.integrations.settings.bkap_outlook_calendar_integration.redirect_uri}}</textarea>
										<a href="javascript:void(0)"
											data-selector-to-copy="textarea.bkap-outlook-calendar-redirect-uri"
											data-tip="<?php esc_attr_e( 'Redirect URI has been copied!', 'woocommerce-booking' ); ?>"
											class="dashicons dashicons-admin-page copy-to-clipboard">&nbsp;</a>
									</div>
								</div>
							</div>

							<div class="tm1-row"
								v-show="'on' === data.integrations.settings.bkap_outlook_calendar_integration.is_enabled && data.integrations.settings.bkap_outlook_calendar_integration.calendars.length > 0">
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
											v-model="data.integrations.settings.bkap_outlook_calendar_integration.calendar_id">
											<option
												v-for="list in data.integrations.settings.bkap_outlook_calendar_integration.calendars"
												v-bind:value="list.id">{{list.name}}
											</option>
										</select>
									</div>
								</div>
							</div>

							<div class="tm1-row"
								v-show="0 === data.integrations.settings.bkap_outlook_calendar_integration.calendars.length && 'on' === data.integrations.settings.bkap_outlook_calendar_integration.is_enabled && '' !== data.integrations.settings.bkap_outlook_calendar_integration.redirect_uri">
								<div class="col-left">
								</div>
								<div class="col-right">
									<a v-bind:href="data.integrations.settings.bkap_outlook_calendar_integration.connect_link"
										class="secondary-btn pl-ss-btn">{{data.label.connect_to_outlook}}</a>
								</div>
							</div>

							<div class="tm1-row"
								v-show="data.integrations.settings.bkap_outlook_calendar_integration.calendars.length > 0 && 'on' === data.integrations.settings.bkap_outlook_calendar_integration.is_enabled && '' !== data.integrations.settings.bkap_outlook_calendar_integration.logout_url">
								<div class="col-left">
								</div>
								<div class="col-right">
									<a v-bind:href="data.integrations.settings.bkap_outlook_calendar_integration.logout_url"
										class="secondary-btn pl-ss-btn">{{data.label.logout_from_outlook}}</a>
								</div>
							</div>

							<div class="rb1-row flx-center mb-3 mt-2">
								<div class="rb-col">
									<a href="javascript:void(0);" class="secondary-btn"
										v-on:click.stop="data.fn.save_settings('integrations',data)">{{data.settings.save_settings_button}}</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="panel">
				<div class="panel-heading">
					<h2 class="panel-title" data-toggle="collapse" data-target="#collapseFluentCRM"
						aria-expanded="false">
                        <?php esc_attr_e( 'FluentCRM', 'woocommerce-booking' ); // phpcs:ignore ?>
					</h2>
				</div>
				<div id="collapseFluentCRM" class="panel-collapse collapse">
					<div class="panel-body">
						<div class="tbl-mod-1 tbl-metabox"
							v-if="!data.integrations.settings.bkap_fluentcrm_integration.is_plugin_activated">
							<div class="alert alert-danger alert-dismissible fade show" role="alert">
								<span v-html="data.label.fluentcrm_plugin_not_activated_message"></span>
							</div>
						</div>

						<div class="tbl-mod-1 tbl-metabox"
							v-if="!data.integrations.settings.bkap_fluentcrm_integration.is_l_active">
							<div class="alert alert-danger alert-dismissible fade show" role="alert">
								<span v-html="data.label.l_error_message"></span>
							</div>
						</div>

						<div class="tbl-mod-1 tbl-metabox"
							v-if="!data.integrations.settings.bkap_fluentcrm_integration.is_api_connection_settings_present">
							<div class="alert alert-danger alert-dismissible fade show" role="alert">
								<span v-html="data.label.fluentcrm_settings_not_present_message"></span>
							</div>
						</div>

						<div class="tbl-mod-1 tbl-metabox tbl-metabox-integrations"
							v-if="data.integrations.settings.bkap_fluentcrm_integration.is_plugin_activated && data.integrations.settings.bkap_fluentcrm_integration.is_l_active && data.integrations.settings.bkap_fluentcrm_integration.is_api_connection_settings_present">
							<div class="tm1-row">
								<div class="col-left">
									<label><?php esc_attr_e( 'Enable FluentCRM', 'woocommerce-booking' ); ?></label>
								</div>
								<div class="col-right">
									<div class="rc-flx-wrap flx-aln-center">
										<img class="tt-info"
											src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
											alt="Tooltip" data-toggle="tooltip" data-placement="top"
											title="">
										<label class="el-switch el-switch-green">
											<input type="checkbox"
												v-model="data.integrations.settings.bkap_fluentcrm_integration.is_enabled"
												true-value="on" false-value="">
											<span class="el-switch-style"></span>
										</label>
									</div>
								</div>
							</div>

							<div class="tm1-row"
								v-show="'on' === data.integrations.settings.bkap_fluentcrm_integration.is_enabled && data.integrations.settings.bkap_fluentcrm_integration.lists.length > 0">
								<div class="col-left">
									<label><?php esc_attr_e( 'Select List', 'woocommerce-booking' ); ?></label>
								</div>
								<div class="col-right">
									<div class="rc-flx-wrap flx-aln-center">
										<img class="tt-info"
											src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
											alt="Tooltip" data-toggle="tooltip" data-placement="top"
											title="<?php esc_attr_e( 'Contact will be added to the selected list', 'woocommerce-booking' ); ?>">
										<select class="ib-md"
											v-model="data.integrations.settings.bkap_fluentcrm_integration.list">
											<option
												v-for="list in data.integrations.settings.bkap_fluentcrm_integration.lists"
												v-bind:value="list.id">{{list.title}}</option>
										</select>
									</div>
								</div>
							</div>

							<div class="rb1-row flx-center mb-3 mt-2">
								<div class="rb-col">
									<a href="javascript:void(0);" class="secondary-btn"
										v-on:click.stop="data.fn.save_settings('integrations',data)">{{data.settings.save_settings_button}}</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="panel">
				<div class="panel-heading">
					<h2 class="panel-title" data-toggle="collapse" data-target="#collapseZapier" aria-expanded="false">
                        <?php esc_attr_e( 'Zapier', 'woocommerce-booking' ); // phpcs:ignore ?>
					</h2>
				</div>
				<div id="collapseZapier" class="panel-collapse collapse">
					<div class="panel-body">
						<div class="tbl-mod-1 tbl-metabox"
							v-if="!data.integrations.settings.bkap_zapier_integration.is_l_active">
							<div class="alert alert-danger alert-dismissible fade show" role="alert">
								<span v-html="data.label.l_error_message"></span>
							</div>
						</div>

						<div class="tbl-mod-1 tbl-metabox tbl-metabox-integrations"
							v-if="data.integrations.settings.bkap_zapier_integration.is_l_active">
							<h2><?php esc_attr_e( 'Create Booking Trigger', 'woocommerce-booking' ); ?></h2>

							<div
								v-show="!data.integrations.settings.bkap_zapier_integration.create_booking_trigger.is_enabled">
								<div class="alert alert-danger alert-dismissible fade show" role="alert">
									<span v-html="data.label.zapier_integration_trigger_disabled"></span>
								</div>
							</div>

							<div
								v-show="data.integrations.settings.bkap_zapier_integration.create_booking_trigger.is_enabled && Object.keys(data.integrations.settings.bkap_zapier_integration.create_booking_trigger.hooks).length <= 0">
								<div class="alert alert-danger alert-dismissible fade show" role="alert">
									<span v-html="data.label.create_booking_no_triggers_found"></span>
								</div>
							</div>

							<div class="tm1-row"
								v-show="data.integrations.settings.bkap_zapier_integration.create_booking_trigger.is_enabled && Object.keys(data.integrations.settings.bkap_zapier_integration.create_booking_trigger.hooks).length > 0">
								<div class="col-left">
									<label><?php esc_attr_e( 'Enable Trigger', 'woocommerce-booking' ); ?></label>
								</div>
								<div class="col-right">
									<div class="rc-flx-wrap flx-aln-center">
										<img class="tt-info"
											src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
											alt="Tooltip" data-toggle="tooltip" data-placement="top"
											title="<?php esc_attr_e( 'Enable Create Booking Trigger so that newly created bookings can be sent to Zapier.', 'woocommerce-booking' ); ?>">
										<label class="el-switch el-switch-green">
											<input type="checkbox"
												v-model="data.integrations.settings.bkap_zapier_integration.create_booking_trigger_status"
												true-value="on" false-value="">
											<span class="el-switch-style"></span>
										</label>
									</div>
								</div>
							</div>

							<div class="tm1-row"
								v-show="'on' === data.integrations.settings.bkap_zapier_integration.create_booking_trigger_status && data.integrations.settings.bkap_zapier_integration.create_booking_trigger.is_enabled && Object.keys(data.integrations.settings.bkap_zapier_integration.create_booking_trigger.hooks).length > 0">
								<div class="col-left">
									<label><?php esc_attr_e( 'Select Trigger', 'woocommerce-booking' ); ?></label>
								</div>
								<div class="col-right">
									<div class="rc-flx-wrap flx-aln-center">
										<img class="tt-info"
											src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
											alt="Tooltip" data-toggle="tooltip" data-placement="top"
											title="Zapier Trigger Hook - Select Label used when creating a Trigger on Zapier.">
										<select class="ib-md"
											v-model="data.integrations.settings.bkap_zapier_integration.create_booking_trigger_label">
											<option
												v-for="(value) in data.integrations.settings.bkap_zapier_integration.create_booking_trigger.hooks"
												:key="value.label" v-bind:value="value.label">{{value.label}}</option>
										</select>
									</div>
								</div>
							</div>

							<h2><?php esc_attr_e( 'Update Booking Trigger', 'woocommerce-booking' ); ?></h2>

							<div
								v-show="!data.integrations.settings.bkap_zapier_integration.update_booking_trigger.is_enabled">
								<div class="alert alert-danger alert-dismissible fade show" role="alert">
									<span v-html="data.label.zapier_integration_trigger_disabled"></span>
								</div>
							</div>

							<div
								v-show="data.integrations.settings.bkap_zapier_integration.update_booking_trigger.is_enabled && Object.keys(data.integrations.settings.bkap_zapier_integration.update_booking_trigger.hooks).length <= 0">
								<div class="alert alert-danger alert-dismissible fade show" role="alert">
									<span v-html="data.label.update_booking_no_triggers_found"></span>
								</div>
							</div>

							<div class="tm1-row"
								v-show="data.integrations.settings.bkap_zapier_integration.update_booking_trigger.is_enabled && Object.keys(data.integrations.settings.bkap_zapier_integration.update_booking_trigger.hooks).length > 0">
								<div class="col-left">
									<label><?php esc_attr_e( 'Enable Trigger', 'woocommerce-booking' ); ?></label>
								</div>
								<div class="col-right">
									<div class="rc-flx-wrap flx-aln-center">
										<img class="tt-info"
											src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
											alt="Tooltip" data-toggle="tooltip" data-placement="top"
											title="<?php esc_attr_e( 'Enable Update Booking Trigger so that updated bookings can be sent to Zapier.', 'woocommerce-booking' ); ?>">
										<label class="el-switch el-switch-green">
											<input type="checkbox"
												v-model="data.integrations.settings.bkap_zapier_integration.update_booking_trigger_status"
												true-value="on" false-value="">
											<span class="el-switch-style"></span>
										</label>
									</div>
								</div>
							</div>

							<div class="tm1-row"
								v-show="'on' === data.integrations.settings.bkap_zapier_integration.update_booking_trigger_status && data.integrations.settings.bkap_zapier_integration.update_booking_trigger.is_enabled && Object.keys(data.integrations.settings.bkap_zapier_integration.update_booking_trigger.hooks).length > 0">
								<div class="col-left">
									<label><?php esc_attr_e( 'Select Trigger', 'woocommerce-booking' ); ?></label>
								</div>
								<div class="col-right">
									<div class="rc-flx-wrap flx-aln-center">
										<img class="tt-info"
											src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
											alt="Tooltip" data-toggle="tooltip" data-placement="top"
											title="Zapier Trigger Hook - Select Label used when creating a Trigger on Zapier.">
										<select class="ib-md"
											v-model="data.integrations.settings.bkap_zapier_integration.update_booking_trigger_label">
											<option
												v-for="(value) in data.integrations.settings.bkap_zapier_integration.update_booking_trigger.hooks"
												:key="value.label" v-bind:value="value.label">{{value.label}}</option>
										</select>
									</div>
								</div>
							</div>

							<h2><?php esc_attr_e( 'Delete Booking Trigger', 'woocommerce-booking' ); ?></h2>

							<div
								v-show="!data.integrations.settings.bkap_zapier_integration.delete_booking_trigger.is_enabled">
								<div class="alert alert-danger alert-dismissible fade show" role="alert">
									<span v-html="data.label.zapier_integration_trigger_disabled"></span>
								</div>
							</div>

							<div
								v-show="data.integrations.settings.bkap_zapier_integration.delete_booking_trigger.is_enabled && Object.keys(data.integrations.settings.bkap_zapier_integration.delete_booking_trigger.hooks).length <= 0">
								<div class="alert alert-danger alert-dismissible fade show" role="alert">
									<span v-html="data.label.delete_booking_no_triggers_found"></span>
								</div>
							</div>

							<div class="tm1-row"
								v-show="data.integrations.settings.bkap_zapier_integration.delete_booking_trigger.is_enabled && Object.keys(data.integrations.settings.bkap_zapier_integration.delete_booking_trigger.hooks).length > 0">
								<div class="col-left">
									<label><?php esc_attr_e( 'Enable Trigger', 'woocommerce-booking' ); ?></label>
								</div>
								<div class="col-right">
									<div class="rc-flx-wrap flx-aln-center">
										<img class="tt-info"
											src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
											alt="Tooltip" data-toggle="tooltip" data-placement="top"
											title="<?php esc_attr_e( 'Enable Delete Booking Trigger so that deleted bookings can be sent to Zapier.', 'woocommerce-booking' ); ?>">
										<label class="el-switch el-switch-green">
											<input type="checkbox"
												v-model="data.integrations.settings.bkap_zapier_integration.delete_booking_trigger_status"
												true-value="on" false-value="">
											<span class="el-switch-style"></span>
										</label>
									</div>
								</div>
							</div>

							<div class="tm1-row"
								v-show="'on' === data.integrations.settings.bkap_zapier_integration.delete_booking_trigger_status && data.integrations.settings.bkap_zapier_integration.delete_booking_trigger.is_enabled && Object.keys(data.integrations.settings.bkap_zapier_integration.delete_booking_trigger.hooks).length > 0">
								<div class="col-left">
									<label><?php esc_attr_e( 'Select Trigger', 'woocommerce-booking' ); ?></label>
								</div>
								<div class="col-right">
									<div class="rc-flx-wrap flx-aln-center">
										<img class="tt-info"
											src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
											alt="Tooltip" data-toggle="tooltip" data-placement="top"
											title="Zapier Trigger Hook - Select Label used when creating a Trigger on Zapier.">
										<select class="ib-md"
											v-model="data.integrations.settings.bkap_zapier_integration.delete_booking_trigger_label">
											<option
												v-for="(value) in data.integrations.settings.bkap_zapier_integration.delete_booking_trigger.hooks"
												:key="value.label" v-bind:value="value.label">{{value.label}}</option>
										</select>
									</div>
								</div>
							</div>

							<div class="rb1-row flx-center mb-3 mt-2" v-if="'undefined' === typeof data.is_bulk_booking"
								v-show="(data.integrations.settings.bkap_zapier_integration.create_booking_trigger.is_enabled && Object.keys(data.integrations.settings.bkap_zapier_integration.create_booking_trigger.hooks).length > 0) || (data.integrations.settings.bkap_zapier_integration.create_booking_trigger.is_enabled && Object.keys(data.integrations.settings.bkap_zapier_integration.create_booking_trigger.hooks).length > 0) || (data.integrations.settings.bkap_zapier_integration.update_booking_trigger.is_enabled && Object.keys(data.integrations.settings.bkap_zapier_integration.update_booking_trigger.hooks).length > 0)">
								<div class="rb-col">
									<a href="javascript:void(0);" class="secondary-btn"
										v-on:click.stop="data.fn.save_settings('integrations',data)">{{data.settings.save_settings_button}}</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="panel">
				<div class="panel-heading">
					<h2 class="panel-title" data-toggle="collapse" data-target="#collapseZoomMeetings"
						aria-expanded="false">
                        <?php esc_attr_e( 'Zoom Meetings', 'woocommerce-booking' ); // phpcs:ignore ?>
					</h2>
				</div>
				<div id="collapseZoomMeetings" class="panel-collapse collapse">
					<div class="panel-body">
						<div class="tbl-mod-1 tbl-metabox"
							v-if="!data.integrations.settings.bkap_zoom_integration.zoom_keys_are_set">
							<div class="alert alert-danger alert-dismissible fade show" role="alert">
								<span v-html="data.settings.labels.zoom_connection_not_active_message"></span>
							</div>
						</div>

						<div class="tbl-mod-1 tbl-metabox"
							v-if="!data.integrations.settings.bkap_zoom_integration.is_l_active">
							<div class="alert alert-danger alert-dismissible fade show" role="alert">
								<span v-html="data.label.l_error_message"></span>
							</div>
						</div>

						<div class="tbl-mod-1 tbl-metabox "
							v-if="!data.integrations.settings.bkap_zoom_integration.user_list_can_be_retrieved && data.integrations.settings.bkap_zoom_integration.zoom_keys_are_set">
							<div class="alert alert-danger alert-dismissible fade show" role="alert">
								<span v-html="data.settings.labels.zoom_connection_user_list_empty"></span>
							</div>
						</div>

						<div class="tbl-mod-1 tbl-metabox tbl-metabox-integrations"
							v-if="data.integrations.settings.bkap_zoom_integration.zoom_keys_are_set && data.integrations.settings.bkap_zoom_integration.is_l_active && data.integrations.settings.bkap_zoom_integration.user_list_can_be_retrieved">
							<div class="tm1-row">
								<div class="col-left">
									<label><?php esc_attr_e( 'Enable Zoom Meetings', 'woocommerce-booking' ); ?></label>
								</div>
								<div class="col-right">
									<div class="rc-flx-wrap flx-aln-center">
										<img class="tt-info"
											src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
											alt="Tooltip" data-toggle="tooltip" data-placement="top"
											title="">
										<label class="el-switch el-switch-green">
											<input type="checkbox"
												v-model="data.integrations.settings.bkap_zoom_integration.is_enabled"
												true-value="on" false-value="">
											<span class="el-switch-style"></span>
										</label>
									</div>
								</div>
							</div>

							<div class="tm1-row">
								<div class="col-left">
									<label><?php esc_attr_e( 'Host', 'woocommerce-booking' ); ?></label>
								</div>
								<div class="col-right">
									<div class="rc-flx-wrap flx-aln-center">
										<img class="tt-info"
											src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
											alt="Tooltip" data-toggle="tooltip" data-placement="top"
											title="<?php esc_attr_e( 'Selected user will be assgined as host for created meeting.', 'woocommerce-booking' ); ?>">
										<select class="ib-md bkap_zoom_integration_hosts"
											v-model="data.integrations.settings.bkap_zoom_integration.host">
											<option
												v-for="(value,key) in data.integrations.settings.bkap_zoom_integration.user_list"
												v-bind:value="key">{{value}}</option>
										</select>
									</div>
								</div>
							</div>

							<div class="tm1-row">
								<div class="col-left">
									<label><?php esc_attr_e( 'Meeting Authentication', 'woocommerce-booking' ); ?></label>
								</div>
								<div class="col-right">
									<div class="rc-flx-wrap flx-aln-center">
										<img class="tt-info"
											src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
											alt="Tooltip" data-toggle="tooltip" data-placement="top"
											title="<?php esc_attr_e( 'Enabling this option will allow only authenticated users to join the meeting.', 'woocommerce-booking' ); ?>">
										<label class="el-switch el-switch-green">
											<input type="checkbox"
												v-model="data.integrations.settings.bkap_zoom_integration.meeting_authentication"
												true-value="on" false-value="">
											<span class="el-switch-style"></span>
										</label>
									</div>
								</div>
							</div>

							<div class="tm1-row">
								<div class="col-left">
									<label><?php esc_attr_e( 'Join Before Host', 'woocommerce-booking' ); ?></label>
								</div>
								<div class="col-right">
									<div class="rc-flx-wrap flx-aln-center">
										<img class="tt-info"
											src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
											alt="Tooltip" data-toggle="tooltip" data-placement="top"
											title="<?php esc_attr_e( 'Enabling this option will allow participants to join the meeting before the host starts the meeting', 'woocommerce-booking' ); ?>">
										<label class="el-switch el-switch-green">
											<input type="checkbox"
												v-model="data.integrations.settings.bkap_zoom_integration.join_before_host"
												true-value="on" false-value="">
											<span class="el-switch-style"></span>
										</label>
									</div>
								</div>
							</div>

							<div class="tm1-row">
								<div class="col-left">
									<label><?php esc_attr_e( 'Host Video', 'woocommerce-booking' ); ?></label>
								</div>
								<div class="col-right">
									<div class="rc-flx-wrap flx-aln-center">
										<img class="tt-info"
											src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
											alt="Tooltip" data-toggle="tooltip" data-placement="top"
											title="<?php esc_attr_e( 'Enabling this option will start the video when the host joins the meeting', 'woocommerce-booking' ); ?>">
										<label class="el-switch el-switch-green">
											<input type="checkbox"
												v-model="data.integrations.settings.bkap_zoom_integration.host_video"
												true-value="on" false-value="">
											<span class="el-switch-style"></span>
										</label>
									</div>
								</div>
							</div>

							<div class="tm1-row">
								<div class="col-left">
									<label><?php esc_attr_e( 'Participant Video', 'woocommerce-booking' ); ?></label>
								</div>
								<div class="col-right">
									<div class="rc-flx-wrap flx-aln-center">
										<img class="tt-info"
											src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
											alt="Tooltip" data-toggle="tooltip" data-placement="top"
											title="<?php esc_attr_e( 'Enabling this option will start the video when the participants join the meeting', 'woocommerce-booking' ); ?>">
										<label class="el-switch el-switch-green">
											<input type="checkbox"
												v-model="data.integrations.settings.bkap_zoom_integration.participant_video"
												true-value="on" false-value="">
											<span class="el-switch-style"></span>
										</label>
									</div>
								</div>
							</div>

							<div class="tm1-row">
								<div class="col-left">
									<label><?php esc_attr_e( 'Mute Upon Entry', 'woocommerce-booking' ); ?></label>
								</div>
								<div class="col-right">
									<div class="rc-flx-wrap flx-aln-center">
										<img class="tt-info"
											src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
											alt="Tooltip" data-toggle="tooltip" data-placement="top"
											title="<?php esc_attr_e( 'Enabling this option will mute the participants upon entry', 'woocommerce-booking' ); ?>">
										<label class="el-switch el-switch-green">
											<input type="checkbox"
												v-model="data.integrations.settings.bkap_zoom_integration.mute_upon_entry"
												true-value="on" false-value="">
											<span class="el-switch-style"></span>
										</label>
									</div>
								</div>
							</div>

							<div class="tm1-row">
								<div class="col-left">
									<label><?php esc_attr_e( 'Auto Recording', 'woocommerce-booking' ); ?></label>
								</div>
								<div class="col-right">
									<div class="rc-flx-wrap flx-aln-center">
										<img class="tt-info"
											src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
											alt="Tooltip" data-toggle="tooltip" data-placement="top"
											title="<?php esc_attr_e( 'Selected user will be assgined as host for created meeting.', 'woocommerce-booking' ); ?>">
										<select class="ib-md bkap_zoom_integration_auto_recording"
											v-model="data.integrations.settings.bkap_zoom_integration.auto_recording">
											<option
												v-for="(value,key) in data.integrations.settings.bkap_zoom_integration.auto_recording_list"
												v-bind:value="key">{{value}}</option>
										</select>
									</div>
								</div>
							</div>

							<div class="tm1-row">
								<div class="col-left">
									<label><?php esc_attr_e( 'Alternative Host', 'woocommerce-booking' ); ?></label>
								</div>
								<div class="col-right">
									<div class="rc-flx-wrap flx-aln-center">
										<img class="tt-info"
											src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
											alt="Tooltip" data-toggle="tooltip" data-placement="top"
											title="<?php esc_attr_e( 'Here you can select the alternative host\'s emails', 'woocommerce-booking' ); ?>">
										<select class="ib-md bkap_zoom_integration_alternative_hosts" multiple="true"
											v-model="data.integrations.settings.bkap_zoom_integration.alternative_hosts">
											<option
												v-for="(value,key) in data.integrations.settings.bkap_zoom_integration.user_list"
												v-bind:value="key">{{value}}</option>
										</select>
									</div>
								</div>
							</div>

							<div class="rb1-row flx-center mb-3 mt-2">
								<div class="rb-col">
									<a href="javascript:void(0);" class="secondary-btn"
										v-on:click.stop="data.fn.save_settings('integrations',data)">{{data.settings.save_settings_button}}</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
