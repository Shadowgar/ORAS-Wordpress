<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * Calendar.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/Views/Appearance/Calendar
 * @since       5.19.0
 */

?>
<template id="calendar-tab">
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
                            <h1><?php esc_attr_e( 'Calendar', 'woocommerce-booking' ); // phpcs:ignore ?></h1>
                            <p><?php esc_attr_e( 'Customize how your calendar appears.', 'woocommerce-booking' ); // phpcs:ignore ?>
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
                                        <?php esc_attr_e( 'Calendar Appearance', 'woocommerce-booking' ); // phpcs:ignore  ?>
									</h2>
								</div>
								<div id="collapseOne" class="panel-collapse collapse show">
									<div class="panel-body">
										<div class="tbl-mod-1">
											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Calendar Language', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'Choose the language for your booking calendar.', 'woocommerce-booking' ); ?>">
														<select class="ib-md" v-model="data.settings.booking_language" @change="load_datepicker_js_file">
															<option v-for="(value, key) in data.config.languages"
																v-bind:value="key">{{value}}</option>
														</select>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Date Format', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'The format in which the booking date appears to the customers throughout the order cycle', 'woocommerce-booking' ); ?>">
														<select class="ib-md"
															v-model="data.settings.booking_date_format">
															<option v-for="(value, key) in data.config.date_formats"
																v-bind:value="key">{{value}}</option>
														</select>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Time Format', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'The format in which booking time appears to the customers throughout the order cycle', 'woocommerce-booking' ); ?>">
														<select class="ib-md"
															v-model="data.settings.booking_time_format">
															<option v-for="(value, key) in data.config.time_formats"
																v-bind:value="key">{{value}}</option>
														</select>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'First Day On Calendar', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'Choose the first day to display on the booking calendar', 'woocommerce-booking' ); ?>">
														<select class="ib-md"
															v-model="data.settings.booking_calendar_day">
															<option v-for="(value, key) in data.config.bkap_days"
																v-bind:value="key">{{value}}</option>
														</select>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Number Of Months To Show In Calendar', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'The number of months to be shown on the calendar. if the booking dates spans across 2 months, then dates of 2 months can be shown simultaneously without the need to press next or back buttons', 'woocommerce-booking' ); ?>">
														<select class="ib-md" v-model="data.settings.booking_months">
															<option value="1">1</option>
															<option value="2">2</option>
														</select>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Calendar Theme', 'woocommerce-booking' ); ?></label>
												</div>

												<div class="col-right">
													<div class="row-box-1">
														<div class="rb1-left">
															<img class="tt-info"
																src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
																alt="Tooltip" data-toggle="tooltip" data-placement="top"
																title="<?php esc_attr_e( 'Select the theme for the calendar. you can choose a theme which blends with the design of your website', 'woocommerce-booking' ); ?>">
														</div>
														<div class="rb1-right">
															<div class="rb1-row">
																<select class="ib-md"
																	v-model="data.settings.booking_themes"
																	@change="set_calendar_theme">
																	<option
																		v-for="(value, key) in data.config.bkap_calendar_themes"
																		v-bind:value="key">{{value}}</option>
																</select>
															</div>
															<div class="rb1-row adj-img cal-theme-image">
																<datepicker
																	:start="data.settings.booking_calendar_day"
																	:language="data.settings.calendar_language">
																</datepicker>
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
