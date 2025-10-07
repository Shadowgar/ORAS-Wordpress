<div class="bkap_admin_loader" v-show="data.loader.loader_deleting_price_range_by_months_data">
	<div class="bkap_admin_loader_wrapper">
		{{data.settings.labels.loader_deleting_price_range_by_months_data}} <img
			src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
	</div>
</div>

<div class="bkap_admin_loader" v-show="data.loader.loader_deleting_all_price_range_by_months_data">
	<div class="bkap_admin_loader_wrapper">
		{{data.settings.labels.loader_deleting_all_price_range_by_months_data}} <img
			src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
	</div>
</div>

<div class="bkap_admin_loader" v-show="data.loader.loader_deleting_fixed_block_data">
	<div class="bkap_admin_loader_wrapper">
		{{data.settings.labels.loader_deleting_fixed_block_data}} <img
			src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
	</div>
</div>

<div class="bkap_admin_loader" v-show="data.loader.loader_deleting_all_fixed_block_data">
	<div class="bkap_admin_loader_wrapper">
		{{data.settings.labels.loader_deleting_all_fixed_block_data}} <img
			src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
	</div>
</div>

<div class="bkap_admin_loader" v-show="data.loader.loader_saving_block_pricing_settings">
	<div class="bkap_admin_loader_wrapper">
		{{data.settings.labels.loader_saving_block_pricing_settings}} <img
			src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
	</div>
</div>

<div class="tbl-mod-1 tbl-metabox" v-show="data.sidebar.items.block_pricing">
	<div class="tm1-row">
		<div class="col-left">
			<label><?php esc_html_e( 'Block Pricing', 'woocommerce-booking' ); ?></label>
		</div>
		<div class="col-right">
			<div class="rc-flx-wrap flx-aln-center ro-wrap">
				<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>" alt="Tooltip"
					data-toggle="tooltip" data-placement="top"
					title="<?php esc_attr_e( 'Select Fixed Block Booking option if you want customers to book or rent this product for fixed number of days. Select Price By Range Of Nights option if you want to charge customers different prices for different day ranges.', 'woocommerce-booking' ); ?>">
				<div class="rb-flx-style mb-15">
					<div class="el-radio el-radio-green">
						<input type="radio" value="booking_fixed_block_enable" id="booking_fixed_block_enable"
							v-model="data.block_pricing.settings.block_pricing_type">
						<label for="booking_fixed_block_enable" class="el-radio-style"></label>
					</div>
					<label><?php esc_attr_e( 'Fixed Block Booking', 'woocommerce-booking' ); ?></label>
				</div>
				<div class="rb-flx-style mb-15">
					<div class="el-radio el-radio-green">
						<input type="radio" value="booking_block_price_enable" id="booking_block_price_enable"
							v-model="data.block_pricing.settings.block_pricing_type">
						<label for="booking_block_price_enable" class="el-radio-style"></label>
					</div>
					<label><?php esc_attr_e( 'Price By Range Of Nights', 'woocommerce-booking' ); ?></label>
				</div>
				<div class="rb-flx-style mb-15">
				<i><a href="javascript:void(0);" class="a-link-delete"
									v-on:click.stop="data.fn.block_pricing.clear_block_selection( data )">Clear selection</a></i>
				</div>
				
			</div>
		</div>
	</div>

	<div class="tm1-row bdr-0 pt-0"
		:class="{'display-block':'booking_fixed_block_enable' === data.block_pricing.settings.block_pricing_type}"
		v-show="'booking_fixed_block_enable' === data.block_pricing.settings.block_pricing_type">
		<div class="tbl-mod-2">
			<div :class="{'tbl-responsive': data.toggle_edit_mode.block_pricing_fixed_block, 'tm2-inner-wrap': true}">
				<table class="table table_block_pricing_fixed_block">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Block Name', 'woocommerce-booking' ); ?></th>
							<th><?php esc_html_e( 'Days', 'woocommerce-booking' ); ?></th>
							<th><?php esc_html_e( 'Start Day', 'woocommerce-booking' ); ?></th>
							<th><?php esc_html_e( 'End Day', 'woocommerce-booking' ); ?></th>
							<th><?php esc_html_e( 'Price', 'woocommerce-booking' ); ?></th>
							<th><?php esc_html_e( 'Action', 'woocommerce-booking' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(row,index) in data.block_pricing.settings.data.fixed_block">
							<td>
								<span v-show="!row.edit">{{row.block_name}}</span>
								<input v-show="row.edit" class="ib-md" type="text" v-model="row.block_name"
									:placeholder="data.settings.placeholders.block_name" />
							</td>

							<td>
								<span v-show="!row.edit">{{row.number_of_days}}</span>
								<input v-show="row.edit" class="ib-sm" type="number" min=0
									v-on:keyup="data.fn.only_numbers" v-model="row.number_of_days">
							</td>

							<td>
								<span
									v-show="!row.edit">{{data.settings.block_pricing_fixed_days[row.start_day]}}</span>
								<select v-show="row.edit" class="ib-md" v-model="row.start_day">
									<option v-for="(day,key) in data.settings.block_pricing_fixed_days"
										v-bind:value="key">
										{{day}}</option>
								</select>
							</td>

							<td>
								<span v-show="!row.edit">{{data.settings.block_pricing_fixed_days[row.end_day]}}</span>
								<select v-show="row.edit" class="ib-md" v-model="row.end_day">
									<option v-for="(day,key) in data.settings.block_pricing_fixed_days"
										v-bind:value="key">
										{{day}}</option>
								</select>
							</td>

							<td>
								<span v-show="!row.edit"
									v-html="'' !== row.price ? `${data.settings.currency_symbol}${row.price}` : ''"></span>
								<input v-show="row.edit" class="ib-sm wc_input_price" type="text" data-model="price"
									
									:placeholder="data.settings.placeholders.price" v-model="row.price">
							</td>

							<td>
								<a href="javascript:void(0);" class="a-link-edit"
									v-on:click.stop="data.fn.block_pricing.fixed_block.edit_row(row,index,data)"
									v-show="!row.edit">Edit</a>
								<a href="javascript:void(0);" class="a-link-delete"
									v-on:click.stop="data.fn.block_pricing.fixed_block.delete_row(row,index,data)">Delete</a>
							</td>
						</tr>
					</tbody>
				</table>

				<div class="add-more-link display-flex-and-space-between">
					<a class="al-link" v-on:click.stop="data.fn.block_pricing.fixed_block.add_row(data)"><img
							src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-plus.svg' ); ?>" alt="Icon" />
						<?php esc_attr_e( 'Add New Block', 'woocommerce-booking' ); ?></a>

					<a class="al-link" v-show="data.block_pricing.settings.data.fixed_block.length > 0"
						v-on:click.stop="data.fn.block_pricing.fixed_block.delete_all_rows(data)"><img
							src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-trash.svg' ); ?>" alt="Icon" />
						<?php esc_attr_e( 'Delete All Block Data', 'woocommerce-booking' ); ?></a>
				</div>
			</div>
		</div>
	</div>

	<div class="tm1-row bdr-0 pt-0"
		:class="{'display-block':'booking_block_price_enable' === data.block_pricing.settings.block_pricing_type}"
		v-show="'booking_block_price_enable' === data.block_pricing.settings.block_pricing_type">
		<div class="tbl-mod-2">
			<div
				:class="{'tbl-responsive': data.toggle_edit_mode.block_pricing_price_by_range, 'tm2-inner-wrap': true}">
				<table class="table table_block_pricing_price_by_range">
					<thead>
						<tr>
							<th
								v-for="header in data.block_pricing.settings.header_columns_block_pricing_price_by_range_nights" v-html="header">
							</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(row,index) in data.block_pricing.settings.data.price_by_range_of_nights">
							<td v-if="Object.keys( data.settings.block_pricing_variable_product_attributes ).length > 0"
								v-for="(attribute_key,attribute_data) in data.settings.block_pricing_variable_product_attributes">
								<span v-show="!row.edit">{{row[attribute_data]}}</span>
								<select v-show="row.edit" class="ib-md" v-model="row[attribute_data]">
									
									<option v-for="attribute in attribute_key" v-bind:value="attribute">
										{{attribute}}</option>
								</select>
							</td>

							<td>
								<span v-show="!row.edit">{{row.min_number}}</span>
								<input v-show="row.edit" class="ib-sm" type="number" min=0
									v-on:keyup="data.fn.only_numbers" v-model="row.min_number">
							</td>

							<td>
								<span v-show="!row.edit">{{row.max_number}}</span>
								<input v-show="row.edit" class="ib-sm" type="number" min=0
									v-on:keyup="data.fn.only_numbers" v-model="row.max_number">
							</td>

							<td>
								<span v-show="!row.edit"
									v-html="'' !== row.per_day_price ? `${data.settings.currency_symbol}${row.per_day_price}` : ''"></span>
								<input v-show="row.edit" class="ib-sm wc_input_price" type="text" data-model="per_day_price"
									v-model="row.per_day_price">
							</td>

							<td>
								<span v-show="!row.edit"
									v-html="'' !== row.fixed_price ? `${data.settings.currency_symbol}${row.fixed_price}` : ''"></span>
								<input v-show="row.edit" class="ib-sm wc_input_price" type="text" data-model="fixed_price"
									v-model="row.fixed_price">
							</td>

							<td>
								<a href="javascript:void(0);" class="a-link-edit"
									v-on:click.stop="data.fn.block_pricing.price_by_range_of_nights.edit_row(row,index,data)"
									v-show="!row.edit">Edit</a>
								<a href="javascript:void(0);" class="a-link-delete"
									v-on:click.stop="data.fn.block_pricing.price_by_range_of_nights.delete_row(row,index,data)">Delete</a>
							</td>
						</tr>
					</tbody>
				</table>

				<div class="add-more-link display-flex-and-space-between">
					<a class="al-link"
						v-on:click.stop="data.fn.block_pricing.price_by_range_of_nights.add_row(data)"><img
							src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-plus.svg' ); ?>" alt="Icon" />
						<?php esc_attr_e( 'Add New Range', 'woocommerce-booking' ); ?></a>

					<a class="al-link" v-show="data.block_pricing.settings.data.fixed_block.length > 0"
						v-on:click.stop="data.fn.block_pricing.price_by_range_of_nights.delete_all_rows(data)"><img
							src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-trash.svg' ); ?>" alt="Icon" />
						<?php esc_attr_e( 'Delete All Block Data', 'woocommerce-booking' ); ?></a>
				</div>
			</div>
		</div>
	</div>

	<div class="rb1-row flx-center mb-3 mt-2" v-if="'undefined' === typeof data.is_bulk_booking">
		<div class="rb-col">
			<a href="javascript:void(0);" class="secondary-btn"
				v-on:click.stop="data.fn.save_settings('block_pricing',data)">{{data.settings.save_settings_button}}</a>
		</div>
	</div>
</div>
