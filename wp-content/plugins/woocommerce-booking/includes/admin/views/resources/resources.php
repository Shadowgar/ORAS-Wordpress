<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * Resources.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/Views/Booking/Resources
 * @since       5.19.0
 */

?>

<template id="resources-tab">
	<section>
		<div class="container-list-table bd-page-wrap resources-table">
			<div class="row">
				<div class="bkap_admin_loader" id="show_loading_loader" v-show="show_loading_loader">
					<div class="bkap_admin_loader_wrapper">
						{{data.label.loading_loader}} <img
							src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
					</div>
				</div>

				<div class="bkap_admin_loader" id="show_saving_resource_loader" v-show="show_saving_resource_loader">
					<div class="bkap_admin_loader_wrapper">
						{{data.label.saving_resource_loader}} <img
							src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
					</div>
				</div>

				<div class="bkap_admin_loader" id="show_updating_resource_loader"
					v-show="show_updating_resource_loader">
					<div class="bkap_admin_loader_wrapper">
						{{data.label.updating_resource_loader}} <img
							src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
					</div>
				</div>

				<div class="bkap_admin_loader" id="show_custom_message_loader" v-show="false">
					<div class="bkap_admin_loader_wrapper">
						<span></span> <img src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
					</div>
				</div>

				<div class="col-md-12 bkap-wp-list-table" v-show="!show_add_edit_resource_page">
					<div class="wbc-box">
						<div class="the-table">
							<?php
							$table = new BKAP_Admin_View_Resources_Table();
							$table->populate_data();
							$table->prepare_items();
							$table->views();
							$table->search_box( __( 'Search', 'woocommerce-booking' ), 'resources' );
							$table->display();
							?>
						</div>
					</div>
				</div>
			</div>

			<div class="col-md-12" v-show="show_add_edit_resource_page">
				<div class="bkap-page-head phw-btn">
					<div class="col-left">
						<h1></h1>
					</div>

					<div class="col-right">
						<input type="button" value="Close Window" class="trietary-btn reverse" @click.stop="close">
					</div>
				</div>
			</div>

			<div class="col-md-12 resources" v-show="show_add_edit_resource_page">
				<div class="wbc-accordion">
					<div class="panel-group bkap-accordian" id="wbc-accordion">
						<div class="panel panel-default">
							<div class="panel-heading">
								<h2 class="panel-title" data-toggle="collapse" data-target="#collapseOne"
									aria-expanded="false"
									v-html="is_edit_mode ? data.label.edit_resource + ' #' + data.add_edit_resource.data.id : data.label.add_resource">
								</h2>
							</div>
							<div id="collapseOne" class="panel-collapse collapse show">
								<div class="panel-body">
									<div class="tbl-mod-1">
										<div class="tm1-row">
											<div class="col-left">
												<label><?php esc_attr_e( 'Name', 'woocommerce-booking' ); ?></label>
											</div>
											<div class="col-right">
												<div class="row-box-1">
													<div class="rb1-left">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'This will be the name or title of the resource', 'woocommerce-booking' ); ?>">
													</div>
													<div class="rb1-right">
														<div class="rb1-row flx-center">
															<div class="rb-col">
																<input class="ib-md" type="text"
																	v-model="data.add_edit_resource.data.title">
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>

										<div class="tm1-row">
											<div class="col-left">
												<label><?php esc_attr_e( 'Available Quantity', 'woocommerce-booking' ); ?></label>
											</div>
											<div class="col-right">
												<div class="row-box-1">
													<div class="rb1-left">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'The quantity of this resource available at any given time', 'woocommerce-booking' ); ?>">
													</div>
													<div class="rb1-right">
														<div class="rb1-row flx-center">
															<div class="rb-col">
																<input class="ib-sm" type="number" min=0 step="1"
																	v-model="data.add_edit_resource.data.qty">
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>

										<div class="tm1-row">
											<div class="col-left">
												<label><?php esc_attr_e( 'Menu Order', 'woocommerce-booking' ); ?></label>
											</div>
											<div class="col-right">
												<div class="row-box-1">
													<div class="rb1-left">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'Setting value to this field will decide the appearance of resource in the list', 'woocommerce-booking' ); ?>">
													</div>
													<div class="rb1-right">
														<div class="rb1-row flx-center">
															<div class="rb-col">
																<input class="ib-sm" type="number" min=0 step="1"
																	v-model="data.add_edit_resource.data.menu_order">
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>

										<div class="tm1-row"
											v-show="data.settings.user_list_can_be_retrieved && Object.keys( data.settings.user_list ).length > 0">
											<div class="col-left">
												<label><?php esc_attr_e( 'Meeting Host', 'woocommerce-booking' ); ?></label>
											</div>
											<div class="col-right">
												<div class="rc-flx-wrap flx-aln-center">
													<img class="tt-info"
														src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
														alt="Tooltip" data-toggle="tooltip" data-placement="top"
														title="">
													<select class="ib-md"
														v-model="data.add_edit_resource.data.meeting_host">
														<option v-for="(value, key) in data.settings.user_list"
															:key="key" v-bind:value="key">{{value}}</option>
													</select>
												</div>
											</div>
										</div>

										<div class="tm1-row display-block">
											<p class="label">
												<?php esc_html_e( 'Availability', 'woocommerce-booking' ); ?>
											</p>
											<p class="mb-2">
												<?php esc_html_e( 'Rules with lower priority values will override other rules with higher priority values. Ex. 9 overrides 10.', 'woocommerce-booking' ); ?>
											</p>
										</div>

										<div class="tm1-row border-0 pt-0 display-block display-scroll-x">
											<div class="tbl-mod-2">
												<div
													:class="{'tbl-responsive': data.toggle_edit_mode.manage_availability, 'tm2-inner-wrap': true}">
													<table class="table table_weekday_availability">
														<thead>
															<tr>
																<th><?php esc_html_e( 'Range Type', 'woocommerce-booking' ); ?>
																</th>
																<th><?php esc_html_e( 'From', 'woocommerce-booking' ); ?>
																</th>
																<th><?php esc_html_e( 'To', 'woocommerce-booking' ); ?>
																</th>
																<th><?php esc_html_e( 'Bookable', 'woocommerce-booking' ); ?>
																</th>
																<th><?php esc_html_e( 'Priority', 'woocommerce-booking' ); ?>
																</th>
																<th><?php esc_html_e( 'Action', 'woocommerce-booking' ); ?>
																</th>
															</tr>
														</thead>
														<tbody>
															<tr
																v-for="(row,index) in data.add_edit_resource.data.availability">
																<td>
																	<span
																		v-show="!row.edit">{{'time' === row.range_type.substring(0,4) ? data.settings.range_type_time_data[row.range_type] : data.settings.range_type_general[row.range_type]}}</span>
																	<select v-show="row.edit" class="ib-md"
																		v-model="row.range_type">
																		<option
																			v-for="(value,key) in data.settings.range_type_general"
																			v-bind:value="key">
																			{{value}}</option>

																		<optgroup
																			label="<?php esc_html_e( 'Time Ranges', 'woocommerce-booking' ); ?>">
																			<option
																				v-for="(value,key) in data.settings.range_type_time_data"
																				v-bind:value="key">
																				{{value}}</option>
																		</optgroup>
																	</select>
																</td>

																<td v-show="'days' === row.range_type">
																	<span
																		v-show="!row.edit">{{data.settings.intervals.days[row.range_days_from]}}</span>
																	<select v-show="row.edit" class="ib-md"
																		v-model="row.range_days_from">
																		<option
																			v-for="(value,key) in data.settings.intervals.days"
																			v-bind:value="key">
																			{{value}}</option>
																	</select>
																</td>

																<td v-show="'days' === row.range_type">
																	<span
																		v-show="!row.edit">{{data.settings.intervals.days[row.range_days_to]}}</span>
																	<select v-show="row.edit" class="ib-md"
																		v-model="row.range_days_to">
																		<option
																			v-for="(value,key) in data.settings.intervals.days"
																			v-bind:value="key">
																			{{value}}</option>
																	</select>
																</td>

																<td v-show="'months' === row.range_type">
																	<span
																		v-show="!row.edit">{{data.settings.intervals.months[row.range_months_from]}}</span>
																	<select v-show="row.edit" class="ib-md"
																		v-model="row.range_months_from">
																		<option
																			v-for="(value,key) in data.settings.intervals.months"
																			v-bind:value="key">
																			{{value}}</option>
																	</select>
																</td>

																<td v-show="'months' === row.range_type">
																	<span
																		v-show="!row.edit">{{data.settings.intervals.months[row.range_months_to]}}</span>
																	<select v-show="row.edit" class="ib-md"
																		v-model="row.range_months_to">
																		<option
																			v-for="(value,key) in data.settings.intervals.months"
																			v-bind:value="key">
																			{{value}}</option>
																	</select>
																</td>

																<td v-show="'weeks' === row.range_type">
																	<span
																		v-show="!row.edit">{{data.settings.intervals.weeks[row.range_weeks_from]}}</span>
																	<select v-show="row.edit" class="ib-md"
																		v-model="row.range_weeks_from">
																		<option
																			v-for="(value,key) in data.settings.intervals.weeks"
																			v-bind:value="key">
																			{{value}}</option>
																	</select>
																</td>

																<td v-show="'weeks' === row.range_type">
																	<span
																		v-show="!row.edit">{{data.settings.intervals.weeks[row.range_weeks_to]}}</span>
																	<select v-show="row.edit" class="ib-md"
																		v-model="row.range_weeks_to">
																		<option
																			v-for="(value,key) in data.settings.intervals.weeks"
																			v-bind:value="key">
																			{{value}}</option>
																	</select>
																</td>

																<td v-show="'custom' === row.range_type">
																	<span
																		v-show="!row.edit">{{row.range_date_from_formatted}}</span>
																	<input v-show="row.edit" class="ib-md" type="date"
																		v-model="row.range_date_from" />
																</td>

																<td v-show="'custom' === row.range_type">
																	<span
																		v-show="!row.edit">{{row.range_date_to_formatted}}</span>
																	<input v-show="row.edit" class="ib-md" type="date"
																		v-model="row.range_date_to" />
																</td>

																<td
																	v-show="'time' === row.range_type || ('time:range' !== row.range_type && 'time:' === row.range_type.substring(0,5))">
																	<span
																		v-show="!row.edit">{{row.range_time_from}}</span>
																	<input v-show="row.edit" class="ib-md" type="time"
																		v-model="row.range_time_from" />
																</td>

																<td
																	v-show="'time' === row.range_type || ('time:range' !== row.range_type && 'time:' === row.range_type.substring(0,5))">
																	<span
																		v-show="!row.edit">{{row.range_time_to}}</span>
																	<input v-show="row.edit" class="ib-md" type="time"
																		v-model="row.range_time_to" />
																</td>

																<td v-show="'time:range' === row.range_type">
																	<span
																		v-show="!row.edit">{{row.range_time_range_date_from_formatted}}</span>
																	<input v-show="row.edit" class="ib-md" type="date"
																		v-model="row.range_time_range_date_from" />
																		
																	<span
																		v-show="!row.edit">{{row.range_time_range_time_from}}</span>
																	<input v-show="row.edit" class="ib-md" type="time"
																		v-model="row.range_time_range_time_from" />
																</td>

																<td v-show="'time:range' === row.range_type">
																	<span
																		v-show="!row.edit">{{row.range_time_range_date_to_formatted}}</span>
																	<input v-show="row.edit" class="ib-md" type="date"
																		v-model="row.range_time_range_date_to" />

																	<span
																		v-show="!row.edit">{{row.range_time_range_time_to}}</span>
																	<input v-show="row.edit" class="ib-md" type="time"
																		v-model="row.range_time_range_time_to" />
																</td>

																<td>
																	<span v-show="!row.edit"
																		v-html="'on' === row.bookable ? data.settings.labels.yes : data.settings.labels.no"></span>
																	<label v-show="row.edit"
																		class="el-switch el-switch-green">
																		<input type="checkbox" v-model="row.bookable"
																			true-value="on" false-value="">
																		<span class="el-switch-style"></span>
																	</label>
																</td>

																<td>
																	<span v-show="!row.edit">{{row.priority}}</span>
																	<input v-show="row.edit" class="ib-sm" type="number"
																		min=0 v-on:keyup="data.fn.only_numbers"
																		v-model="row.priority">
																</td>

																<td class="bkap-table-action">
																	<a href="javascript:void(0);" class="a-link-update"
																		v-on:click.stop="update_row(row)"
																		v-show="row.edit">Update</a>
																	<a href="javascript:void(0);" class="a-link-edit"
																		v-on:click.stop="edit_row(row)"
																		v-show="!row.edit">&nbsp;Edit</a>
																	<a href="javascript:void(0);" class="a-link-delete"
																		v-on:click.stop="delete_row(index)">&nbsp;Delete</a>
																</td>
															</tr>
														</tbody>
													</table>

													<div class="add-more-link">
														<a class="al-link" v-on:click.stop="add_row"><img
																src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-plus.svg' ); ?>"
																alt="Icon" />
															<?php esc_attr_e( 'Add', 'woocommerce-booking' ); ?></a>
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

			<div class="col-md-12" v-show="show_add_edit_resource_page">
				<div class="bdp-foot">
					<button type="button" class="bkap-button" v-on:click.stop="save_update_resource"
						v-html="is_edit_mode ? data.label.update_resource : data.label.save_resource"></button>
				</div>
			</div>
		</div>
	</section>
</template>
