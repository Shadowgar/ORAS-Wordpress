<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * Product Availability Settings.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/Views/Settings/Product_Availability_Settings
 * @since       5.19.0
 */

?>
<template id="product-availability-tab">
	<section>
		<div class="container bd-page-wrap">
			<div class="row">
				<div class="bkap_admin_loader" v-show="show_executing_action_loader">
					<div class="bkap_admin_loader_wrapper">
						{{data.label.text_clear_settings_loader}} <img
							src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
					</div>
				</div>

				<div class="col-md-12">
					<div class="bkap-page-head phw-btn">
						<div class="col-left">
                            <h1><?php esc_attr_e( 'Manage Availability of Products', 'woocommerce-booking' ); // phpcs:ignore ?>
							</h1>
                            <p><?php echo sprintf( __( 'This page allows you to add, update and delete the availability of mulitple bookable products. We recommend that you go through the documentation %1$shere%2$s before using this feature.', 'woocommerce-booking' ), '<a href="' . esc_url( 'https://www.tychesoftwares.com/docs/docs/booking-appointment-plugin-for-woocommerce-new/settings/product-availability/' ). '">', '</a>' ); // phpcs:ignore ?>
							</p>
						</div>
						<div class="col-right"></div>
					</div>
				</div>

				<div class="col-md-12 mt-15">
					<div class="wbc-accordion">
						<div class="panel-group bkap-accordian" id="wbc-accordion">
							<div class="panel panel-default">
								<div id="collapseOne" class="panel-collapse collapse show">
									<div class="panel-body">
										<div class="tbl-mod-1">
											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Select Products', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'The actions added below will be executed for the products selected here.', 'woocommerce-booking' ); ?>">

														<div class="rc-flx-wrap flx-aln-center">
															<select
																class="ib-md product_availability_settings_selected_products"
																v-model="data.settings.product_id" multiple>
																<option v-for="(item,key) in data.select_product_data"
																	v-bind:value="key">{{item}}</option>
															</select>
														</div>
													</div>
												</div>
											</div>

											<div class="tm1-row bdr-0 pt-0"
												:class="{'display-block': 0 !== data.settings.product_id.length}"
												v-show="0 !== data.settings.product_id.length">
												<div class="tbl-mod-2">
													<div
														:class="{'tbl-responsive': data.toggle_edit_mode, 'tm2-inner-wrap': true}">
														<table class="table table_product_availability_settings">
															<thead>
																<tr>
																	<th><?php esc_html_e( 'Day/Date', 'woocommerce-booking' ); ?></th>
																	<th><?php esc_html_e( 'Which Day/Dates?', 'woocommerce-booking' ); ?></th>
																	<th><?php esc_html_e( 'Action', 'woocommerce-booking' ); ?></th>
																	<th><?php esc_html_e( 'From & To Time', 'woocommerce-booking' ); ?></th>
																	<th><?php esc_html_e( 'Max Booking', 'woocommerce-booking' ); ?></th>
																	<th><?php esc_html_e( 'Price', 'woocommerce-booking' ); ?></th>
																	<th><?php esc_html_e( 'Note', 'woocommerce-booking' ); ?></th>
																	<th><?php esc_html_e( 'Action', 'woocommerce-booking' ); ?></th>
																</tr>
															</thead>
															<tbody>
																<tr v-for="(row,index) in data.settings.action_data">
																	<td>
																		<select class="ib-md bkap_availability_day_date"
																			v-model="row.day_date">
																			<option
																				v-for="(item,key) in data.list.day_date"
																				v-bind:value="key">{{item}}</option>
																		</select>
																	</td>

																	<td>
																		<div v-show="row.day_date === 'day'">
																			<select
																				class="ib-md bkap_choices_js_product_availability_days_dates"
																				v-bind:id="`bkap_choices_js_product_availability_days_dates_${index}`"
																				multiple v-model="row.days_dates">
																				<option
																					v-for="(day,key) in data.list.days_dates"
																					v-bind:value="key">{{day}}</option>
																			</select>
																		</div>

																		<input v-show="'date' === row.day_date"
																			v-on:click.stop="data.fn.initialize_datepicker($event,'',row,data)"
																			v-bind:id="`bkap_multiple_date_product_availability_days_dates_${index}`"
																			class="ib-md multiple-date bkap_multiple_date_product_availability_days_dates"
																			type="text" @blur="set_dates($event, index)"
																			v-model="row.selectedDate" />
																	</td>

																	<td>
																		<select class="ib-md bkap_availability_action"
																			v-model="row.action">
																			<option
																				v-for="(item,key) in data.list.actions"
																				v-bind:value="key">{{item}}</option>
																		</select>
																	</td>

																	<td>
																		<input
																			:id='`product_availability_timeslots_input_from_${index}`'
																			class="ib-md" type="text"
																			v-model="row.from_time"
																			v-on:keyup="data.fn.validate_timeslot_from_to($event,row.from_time,row.to_time,'product_availability_timeslots_input_from_'+index,data.validation_messages.timeslot_validation)"
																			:title="data.titles.timeslots_from_to"
																			placeholder="HH:MM" minlength="5"
																			maxlength="5" /> <br />
																		<input
																			:id='`product_availability_timeslots_input_to_${index}`'
																			class="ib-md" type="text"
																			v-model="row.to_time"
																			v-on:keyup="data.fn.validate_timeslot_from_to($event,row.from,row.to,'product_availability_timeslots_input_to_'+index,data.validation_messages.timeslot_validation)"
																			:title="data.titles.timeslots_from_to"
																			placeholder="HH:MM" minlength="5"
																			maxlength="5" />
																	</td>

																	<td>
																		<input class="ib-sm" type="number" min=0
																			v-on:keyup="data.fn.only_numbers"
																			:placeholder="data.placeholders.max_booking"
																			v-model="row.max_booking">
																	</td>

																	<td>
																		<input class="ib-sm wc_input_price"
																			type="number" min=0
																			:placeholder="data.placeholders.price"
																			v-model="row.price">
																	</td>

																	<td>
																		<textarea v-model="row.note"></textarea>
																	</td>

																	<td>
																		<a href="javascript:void(0);"
																			class="a-link-delete delete-icon-active"
																			v-on:click.stop="delete_row(index)"><svg
																				xmlns="http://www.w3.org/2000/svg"
																				width="25" height="25"
																				fill="currentColor" class="bi bi-trash"
																				viewBox="0 0 16 16">
																				<path
																					d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z">
																				</path>
																				<path
																					d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z">
																				</path>
																			</svg></a>
																	</td>
																</tr>
															</tbody>
														</table>

														<div class="add-more-link display-inline-block">
															<a class="al-link" v-on:click.stop="add_row"><img
																	src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-plus.svg' ); ?>"
																	alt="Icon" />
																<?php esc_attr_e( 'Add Action', 'woocommerce-booking' ); ?></a>
														</div>
													</div>
												</div>
											</div>

											<div class="rb1-row flx-center mb-3 mt-2"
												v-show="0 !== data.settings.product_id.length">
												<div class="rb-col">
													<a href="javascript:void(0);" class="secondary-btn"
														v-on:click.stop="execute_added_action">{{data.label.execute_added_action_button}}</a>
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
