<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * Seasonal Pricing.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/Views/Addons/SeasonalPricing
 * @since       5.19.0
 */

?>
<template id="seasonal-pricing-tab">
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

				<div class="container-fluid pl-info-wrap" v-show="show_season_data_saved_message">
					<div class="row">
						<div class="col-md-12">
							<div class="alert alert-success alert-dismissible fade show" role="alert">
								<?php esc_attr_e( 'Season Data has been saved.', 'woocommerce-booking' ); ?>
								<button type="button" class="close" data-dismiss="alert" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
						</div>
					</div>
				</div>

				<div class="container-fluid pl-info-wrap" v-show="show_season_data_updated_message">
					<div class="row">
						<div class="col-md-12">
							<div class="alert alert-success alert-dismissible fade show" role="alert">
								<?php esc_attr_e( 'Season Data has been updated.', 'woocommerce-booking' ); ?>
								<button type="button" class="close" data-dismiss="alert" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
						</div>
					</div>
				</div>

				<div class="container-fluid pl-info-wrap" v-show="show_season_data_deleted_message">
					<div class="row">
						<div class="col-md-12">
							<div class="alert alert-success alert-dismissible fade show" role="alert">
								<?php esc_attr_e( 'Season Data has been deleted.', 'woocommerce-booking' ); ?>
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
                            <h1><?php esc_attr_e( 'Seasonal Pricing', 'woocommerce-booking' ); // phpcs:ignore ?></h1>
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
							<div class="panel panel-default" v-show="!show_add_season_data_page">
								<div class="panel-heading">
									<h2 class="panel-title" data-toggle="collapse" data-target="#collapseSeason"
										aria-expanded="false">
                                        <?php esc_attr_e( 'Settings', 'woocommerce-booking' ); // phpcs:ignore  ?>
									</h2>
								</div>
								<div id="collapseSeason" class="panel-collapse collapse show">
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
															title="<?php esc_attr_e( 'Enable to allow Seasonal Pricing for all the products.', 'woocommerce-booking' ); ?>">
														<label class="el-switch el-switch-green">
															<input type="checkbox"
																v-model="data.settings.enable_seasonal_pricing"
																true-value="yes" false-value="">
															<span class="el-switch-style"></span>
														</label>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="panel panel-default"
								v-show="'yes' === data.settings.enable_seasonal_pricing && show_add_season_data_page">
								<div class="panel-heading">
									<h2 class="panel-title" data-toggle="collapse" data-target="#collapseConfiguration"
										aria-expanded="false">
                                        <?php esc_attr_e( 'Add Season Configuration', 'woocommerce-booking' ); // phpcs:ignore  ?>
									</h2>
								</div>
								<div id="collapseConfiguration" class="panel-collapse collapse show">
									<div class="panel-body">
										<div class="tbl-mod-1">
											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Season Pricing Type', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center ro-wrap">
														<div class="rb-flx-style mb-15">
															<div class="el-radio el-radio-green">
																<input type="radio" value="percent" id="percent"
																	v-model="data.add_season_data.amount_or_percent">
																<label for="percent" class="el-radio-style"></label>
															</div>
															<label for="percent"><?php esc_attr_e( 'Seasonal Pricing Percent', 'woocommerce-booking' ); ?></label>
														</div>
														<div class="rb-flx-style mb-15">
															<div class="el-radio el-radio-green">
																<input type="radio" value="amount" id="amount"
																	v-model="data.add_season_data.amount_or_percent">
																<label for="amount" class="el-radio-style"></label>
															</div>
															<label for="amount"><?php esc_attr_e( 'Seasonal Pricing Value', 'woocommerce-booking' ); ?></label><br>
														</div>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Calculate', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="">
														<select class="ib-md" v-model="data.add_season_data.operator">
															<option value="add">Add</option>
															<option value="subtract">Subtract</option>
														</select>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Season Name', 'woocommerce-booking' ); ?></label>
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
																<div class="rb-col">
																	<input class="ib-md" type="text"
																		v-model="data.add_season_data.season_name">
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'User Role', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="">
														<select class="ib-md bkap_choices_js_user_role" multiple
															v-model="data.add_season_data.user_role">
															<option value="all">All</option>
															<option v-for="(role,key) in data.wp_roles"
																v-bind:value="key">{{role}}</option>
														</select>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Start Date', 'woocommerce-booking' ); ?></label>
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
																<div class="rb-col">
																	<input class="ib-md" type="date"
																		v-model="data.add_season_data.start_date" />
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'End Date', 'woocommerce-booking' ); ?></label>
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
																<div class="rb-col">
																	<input class="ib-md" type="date"
																		v-model="data.add_season_data.end_date" />
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Amount/Percent', 'woocommerce-booking' ); ?></label>
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
																<div class="rb-col">
																	<input class="ib-sm" type="number" min=0
																		v-model="data.add_season_data.price">
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'No. of Years', 'woocommerce-booking' ); ?></label>
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
																<div class="rb-col">
																	<input class="ib-sm" type="number" min=0 max="100"
																		v-model="data.add_season_data.years">
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="rb1-row">
													<input class="secondary-btn reverse" type="button"
														:value="'undefined' !== typeof data.add_season_data.is_edit ? 'Update' : 'Save'"
														v-on:click.stop="save_season_data">
													<input class="trietary-btn reverse" type="button" value="Close"
														v-on:click.stop="hide_add_season_page">
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="panel panel-default"
								v-show="'yes' === data.settings.enable_seasonal_pricing && !show_add_season_data_page">
								<div class="panel-heading">
									<h2 class="panel-title" data-toggle="collapse" data-target="#collapseOne"
										aria-expanded="false">
                                        <?php esc_attr_e( 'Season Configuration', 'woocommerce-booking' ); // phpcs:ignore  ?>
									</h2>
								</div>
								<div id="collapseOne" class="panel-collapse collapse show">
									<div class="panel-body">
										<div class="tbl-mod-1 mt-15">
											<div class="bkap-tbl tbl-responsive">
												<table class="table bkap_seasonal_pricing_table_global">
													<thead>
														<tr>
															<th width="5%">
																<div class="el-checkbox el-checkbox-green d-uni-col">
																	<input type="checkbox" class="ckbCheckAll"
																		id="cb_opt_nm_9A" value="cb_opt_val_9"
																		@change="bulk_select_to_delete">
																	<label class="el-checkbox-style mb-0"
																		for="cb_opt_nm_9A"></label>
																</div>
															</th>
															<th width="20%"> <?php esc_attr_e( 'Season Name', 'woocommerce-booking' ); ?>
															</th>
															<th width="20%"> <?php esc_attr_e( 'User Role', 'woocommerce-booking' ); ?>
															</th>
															<th width="15%"> <?php esc_attr_e( 'Start Date', 'woocommerce-booking' ); ?>
															</th>
															<th width="15%"> <?php esc_attr_e( 'End Date', 'woocommerce-booking' ); ?>
															</th>
															<th width="15%"> <?php esc_attr_e( 'Amount/Percent', 'woocommerce-booking' ); ?>
															</th>
															<th width="10%"><?php esc_attr_e( 'Actions', 'woocommerce-booking' ); ?>
															</th>
														</tr>
													</thead>
													<tbody>
														<tr
															v-for="(row,index) in data.settings.seasons_configuration_data">
															<td>
																<div class="el-checkbox el-checkbox-green d-uni-col">
																	<input type="checkbox" class="checkBoxClass"
																		:id="`id_${index}`"
																		@change="row.is_checked = !row.is_checked"
																		:checked="row.is_checked ? 'checked' : '' " />
																	<label class="el-checkbox-style mb-0"
																		:for="`id_${index}`"></label>
																</div>
															</td>
															<td>
																{{row.season_name}}
															</td>
															<td>
																{{ ( Array.isArray( row.user_role ) && row.user_role.length > 0 ) ? row.user_role.join(',') : '' }}
															</td>
															<td>
																{{row.start_date}}
															</td>
															<td>
																{{row.end_date}}
															</td>
															<td>
																{{ ( 'add' == row.operator ) ? '+' : '-' }}{{ parseFloat( row.price ).toFixed( data.wc_currency_args.decimals ) }}{{( 'percent' == row.amount_or_percent ) ? '%' : '$' }}
															</td>
															
															<td>
																<a role="button" aria-expanded="false"
																	aria-controls="collapseExample"
																	v-on:click.stop="show_edit_season_page(index)"><span
																		class="dashicons dashicons-edit"
																		title="Edit"></span></a> |

																<a class="delete text-danger" data-toggle="modal"
																	:data-target="`#deleteModal_${index}`"
																	title="Delete"><span
																		class="dashicons dashicons-trash" title="Delete"
																		style="color:#dc3545"></span></a>

																<div class="modal fade bkap-modal"
																	:id="`deleteModal_${index}`" tabindex="-1"
																	role="dialog" aria-labelledby="deleteModalLabel"
																	aria-hidden="true">
																	<div class="modal-dialog" role="document">
																		<div class="modal-content">
																			<div class="modal-body">
																				<div class="del-msg">
																					<img src="<?php echo esc_url( BKAP_IMAGE_URL . 'delete-note.svg' ); ?>"
																						alt="">
																					<p><?php esc_html_e( 'Are you sure you want to delete? This action cannot be reversed.', 'woocommerce-booking' ); ?>
																					</p>
																				</div>
																			</div>
																			<div class="modal-footer">
																				<button type="button"
																					class="btn btn-primary"
																					v-on:click.stop="delete_season_data(index)">Yes</button>
																				<button type="button"
																					class="btn btn-secondary"
																					data-dismiss="modal">No</button>
																			</div>
																		</div>
																	</div>
																</div>
															</td>
														</tr>
													</tbody>
												</table>
											</div>
											<div class="add-more-link display-flex-and-space-between">
												<a class="al-link" v-on:click.stop="show_add_season_page"><img
														src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-plus.svg' ); ?>"
														alt="Icon" />
													<?php esc_attr_e( 'Add New Season', 'woocommerce-booking' ); ?></a>

													<a class="delete text-danger" data-toggle="modal"
														:data-target="`#deleteModal_all`"
														title="Delete All Seasons">
														<img src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-trash.svg' ); ?>" alt="Icon" />
														<?php esc_attr_e( 'Delete All Seasons', 'woocommerce-booking' ); ?></a>

													<div class="modal fade bkap-modal"
														:id="`deleteModal_all`" tabindex="-1"
														role="dialog" aria-labelledby="deleteModalLabel"
														aria-hidden="true">
														<div class="modal-dialog" role="document">
															<div class="modal-content">
																<div class="modal-body">
																	<div class="del-msg">
																		<img src="<?php echo esc_url( BKAP_IMAGE_URL . 'delete-note.svg' ); ?>"
																			alt="">
																		<p><?php esc_html_e( 'Are you sure you want to delete? This action cannot be reversed.', 'woocommerce-booking' ); ?>
																		</p>
																	</div>
																</div>
																<div class="modal-footer">
																	<button type="button"
																		class="btn btn-primary"
																		v-on:click.stop="delete_season_data( 'all', data )">Yes</button>
																	<button type="button"
																		class="btn btn-secondary"
																		data-dismiss="modal">No</button>
																</div>
															</div>
														</div>
													</div>
											</div>
										</div>

										<div class="tm1-row bdr-0 pt-0 bkap-bulk-box mt-15">
											<div class="abulk-box pt-0 ">
												<select class="ib-small">
													<option>Delete</option>
												</select>
												<button class="trietary-btn reverse" type="button"
													v-on:click.stop="bulk_delete_season_data"><?php esc_attr_e( 'Apply', 'woocommerce-booking' ); ?></button>
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
