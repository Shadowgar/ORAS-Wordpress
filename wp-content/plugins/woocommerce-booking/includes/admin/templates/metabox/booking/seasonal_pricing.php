<div class="bkap_admin_loader" v-show="data.loader.loader_saving_seasonal_pricing_settings">
	<div class="bkap_admin_loader_wrapper">
		{{data.settings.labels.loader_saving_seasonal_pricing_settings}} <img
			src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
	</div>
</div>

<div class="tbl-mod-1 tbl-metabox"
	v-if="data.sidebar.items.seasonal_pricing && !data.seasonal_pricing.settings.is_plugin_activated">
	<div class="alert alert-danger alert-dismissible fade show" role="alert">
		<span v-html="data.label.seasonal_pricing_plugin_not_activated_message"></span>
	</div>
</div>

<div class="tbl-mod-1 tbl-metabox"
	v-if="data.sidebar.items.seasonal_pricing && !data.seasonal_pricing.settings.is_l_active">
	<div class="alert alert-danger alert-dismissible fade show" role="alert">
		<span v-html="data.label.bl_error_message"></span>
	</div>
</div>
<div class="tbl-mod-1 tbl-metabox" v-show="data.sidebar.items.seasonal_pricing && data.seasonal_pricing.settings.is_plugin_activated && data.seasonal_pricing.settings.is_l_active">
	<div class="tm1-row">
		<div class="col-left">
			<label><?php esc_attr_e( 'Enable Seasonal Pricing', 'woocommerce-booking' ); // phpcs:ignore ?></label>
		</div>
		<div class="col-right">
			<div class="rc-flx-wrap flx-aln-center">
				<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>" alt="Tooltip"
					data-toggle="tooltip" data-placement="top"
					title="<?php esc_attr_e( 'Enable to allow seasonal pricing for the product.', 'woocommerce-booking' ); ?>">
				<label class="el-switch el-switch-green">
					<input type="checkbox" v-model="data.seasonal_pricing.settings.booking_seasonal_pricing_enable" true-value="yes"
						false-value="">
					<span class="el-switch-style"></span>
				</label>
			</div>
		</div>
	</div>

	<div class="wbc-accordion" v-show="data.seasonal_pricing.settings.booking_seasonal_pricing_enable == 'yes' && data.seasonal_pricing.show_add_season_data_page">
		<div class="panel-group bkap-metabox-accordion" id="wbc-accordion">
			<div class="panel">
				<div class="panel-heading">
					<h2 class="panel-title" data-toggle="collapse" data-target="#collapseSeasonConfiguration"
						aria-expanded="true">
						<?php esc_attr_e( 'Add Season Configuration', 'woocommerce-booking' ); // phpcs:ignore ?>
					</h2>
				</div>
				<div id="collapseSeasonConfiguration" class="panel-collapse">
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
													v-model="data.seasonal_pricing.add_season_data.amount_or_percent">
												<label for="percent" class="el-radio-style"></label>
											</div>
											<label for="percent"><?php esc_attr_e( 'Seasonal Pricing Percent', 'woocommerce-booking' ); ?></label>
										</div>
										<div class="rb-flx-style mb-15">
											<div class="el-radio el-radio-green">
												<input type="radio" value="amount" id="amount"
													v-model="data.seasonal_pricing.add_season_data.amount_or_percent">
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
										<select v-model="data.seasonal_pricing.add_season_data.operator">
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
													<input type="text"
														v-model="data.seasonal_pricing.add_season_data.season_name">
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
											v-model="data.seasonal_pricing.add_season_data.user_role">
											<option value="all">All</option>
											<option v-for="(role,key) in data.settings.wp_roles"
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
													<input type="date"
														v-model="data.seasonal_pricing.add_season_data.start_date" />
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
													<input type="date"
														v-model="data.seasonal_pricing.add_season_data.end_date" />
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
														v-model="data.seasonal_pricing.add_season_data.price">
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
														v-model="data.seasonal_pricing.add_season_data.years">
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="tm1-row">
								<div class="rb1-row">
									<input class="secondary-btn reverse" type="button"
										:value="data.seasonal_pricing.add_season_data.is_edit ? 'Update' : 'Save'"
										v-on:click.stop="data.fn.seasonal_pricing.save_season_data( data )">
									<input class="trietary-btn reverse" type="button" value="Close"
									v-on:click.stop="data.fn.seasonal_pricing.hide_add_season_page( data )">
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="tm1-row bdr-0 pt-0" v-show="data.seasonal_pricing.settings.booking_seasonal_pricing_enable == 'yes' && !data.seasonal_pricing.show_add_season_data_page">
		<div class="tbl-mod-2">
			<div class="tbl-responsive tm2-inner-wrap">
				<table class="table bkap_seasonal_pricing_table_product">
					<thead>
						<tr>
							<th width="20%"> <?php esc_attr_e( 'Season Name', 'woocommerce-booking' ); ?></th>
							<th width="20%"> <?php esc_attr_e( 'User Role', 'woocommerce-booking' ); ?></th>
							<th width="15%"> <?php esc_attr_e( 'Start Date', 'woocommerce-booking' ); ?></th>
							<th width="15%"> <?php esc_attr_e( 'End Date', 'woocommerce-booking' ); ?></th>
							<th width="15%"> <?php esc_attr_e( 'Amount', 'woocommerce-booking' ); ?>/<?php esc_attr_e( 'Percent', 'woocommerce-booking' ); ?></th>
							<th width="10%"><?php esc_attr_e( 'Actions', 'woocommerce-booking' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr
							v-for="(row,index) in data.seasonal_pricing.seasons_configuration_data">
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
								{{ ( 'add' == row.operator ) ? '+' : '-' }}{{parseFloat( row.price ).toFixed( data.settings.wc_currency_args.decimals )}}{{( 'percent' == row.amount_or_percent ) ? '%' : '$' }}
							</td>
							
							<td class="tbl-action-col">
								<a role="button" aria-expanded="false"
									aria-controls="collapseExample"
									v-on:click.stop="data.fn.seasonal_pricing.show_edit_season_page(index, data)">Edit</a>

								<a class="delete text-danger" data-toggle="modal"
									:data-target="`#deleteModal_${index}`"
									title="Delete">Delete</a>

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
													v-on:click.stop="data.fn.seasonal_pricing.delete_season_data(index, data, data.product_id)">Yes</button>
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
				<a class="al-link" v-on:click.stop="data.fn.seasonal_pricing.show_add_season_page( data )"><img
						src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-plus.svg' ); ?>"
						alt="Icon" />
					<?php esc_attr_e( 'Add New Season', 'woocommerce-booking' ); ?></a>

				<a class="delete text-danger" data-toggle="modal"
					:data-target="`#deleteModal_all`"
					title="Delete All Seasons"><img src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-trash.svg' ); ?>" alt="Icon" />
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
									v-on:click.stop="data.fn.seasonal_pricing.delete_season_data( 'all', data, data.product_id )">Yes</button>
								<button type="button"
									class="btn btn-secondary"
									data-dismiss="modal">No</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="rb1-row flx-center mb-3 mt-2" v-if="'undefined' === typeof data.is_bulk_booking">
		<div class="rb-col">
			<a href="javascript:void(0);" class="secondary-btn"
				v-on:click.stop="data.fn.save_settings('seasonal_pricing',data)">{{data.settings.save_settings_button}}</a>
		</div>
	</div>
</div>
