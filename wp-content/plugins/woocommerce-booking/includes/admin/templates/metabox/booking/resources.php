<div class="bkap_admin_loader" v-show="data.loader.loader_deleting_resource_data">
	<div class="bkap_admin_loader_wrapper">
		{{data.settings.labels.loader_deleting_resource_data}} <img
			src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
	</div>
</div>

<div class="bkap_admin_loader" v-show="data.loader.loader_saving_resources_settings">
	<div class="bkap_admin_loader_wrapper">
		{{data.settings.labels.loader_saving_resource_settings}} <img
			src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
	</div>
</div>

<div class="bkap_admin_loader" v-show="data.loader.loader_deleting_all_resource_data">
	<div class="bkap_admin_loader_wrapper">
		{{data.settings.labels.loader_deleting_all_resource_data}} <img
			src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
	</div>
</div>

<div class="tbl-mod-1 tbl-metabox tbl-metabox-resources" v-show="data.sidebar.items.resources">
	<div class="tm1-row">
		<div class="col-left">
            <label><?php esc_attr_e( 'Enable Resource Module', 'woocommerce-booking' ); // phpcs:ignore ?></label>
		</div>
		<div class="col-right">
			<div class="rc-flx-wrap flx-aln-center">
				<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>" alt="Tooltip"
					data-toggle="tooltip" data-placement="top"
					title="<?php esc_attr_e( 'Enable this if this bookable product has multiple bookable resources, for example room types or instructors.', 'woocommerce-booking' ); ?>">
				<label class="el-switch el-switch-green">
					<input type="checkbox" v-model="data.resources.settings.bkap_resource" true-value="on"
						false-value="">
					<span class="el-switch-style"></span>
				</label>
			</div>
		</div>
	</div>

	<div class="tm1-row" v-show="'on' === data.resources.settings.bkap_resource">
		<div class="col-left">
			<label><?php esc_attr_e( 'Label', 'woocommerce-booking' ); ?></label>
		</div>
		<div class="col-right">
			<div class="row-box-1">
				<div class="rb1-left">
					<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>" alt="Tooltip"
						data-toggle="tooltip" data-placement="top"
						title="<?php esc_attr_e( 'Enter the name that will appear on the front end for selecting the resource', 'woocommerce-booking' ); ?>">
				</div>
				<div class="rb1-right">
					<div class="rb1-row flx-center">
						<div class="rb-col">
							<input class="ib-sm" type="text" v-model="data.resources.settings.resource_label">
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="tm1-row" v-show="'on' === data.resources.settings.bkap_resource">
		<div class="col-left">
			<label><?php esc_attr_e( 'Resource Position', 'woocommerce-booking' ); ?></label>
		</div>
		<div class="col-right">
			<div class="rc-flx-wrap flx-aln-center">
				<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>" alt="Tooltip"
					data-toggle="tooltip" data-placement="top"
					title="<?php esc_attr_e( 'Option to show Resource field before OR after the date/time fields in the booking form.', 'woocommerce-booking' ); ?>">
				<select class="ib-md" v-model="data.resources.settings.resource_position">
					<option v-for="(type,key) in data.settings.resource_position_types" v-bind:value="key">
						{{type}}</option>
				</select>
			</div>
		</div>
	</div>

	<div class="tm1-row" v-show="'on' === data.resources.settings.bkap_resource">
		<div class="col-left">
			<label><?php esc_attr_e( 'Resource Assignment', 'woocommerce-booking' ); ?></label>
		</div>
		<div class="col-right">
			<div class="rc-flx-wrap flx-aln-center">
				<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>" alt="Tooltip"
					data-toggle="tooltip" data-placement="top"
					title="<?php esc_attr_e( 'Customer selected will allow customer to choose resource on the front end booking form', 'woocommerce-booking' ); ?>">
				<select class="ib-md" v-model="data.resources.settings.resource_assignment">
					<option v-for="(type,key) in data.settings.resource_assignment_types" v-bind:value="key">
						{{type}}</option>
				</select>
			</div>
		</div>
	</div>

	<div class="tm1-row" v-show="'on' === data.resources.settings.bkap_resource">
		<div class="col-left">
			<label><?php esc_attr_e( 'Resource Selection', 'woocommerce-booking' ); ?></label>
		</div>
		<div class="col-right">
			<div class="rc-flx-wrap flx-aln-center">
				<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>" alt="Tooltip"
					data-toggle="tooltip" data-placement="top"
					title="<?php esc_attr_e( 'Define Resource Selection display type. Select \'Single\' option for dropdown or \'Multiple\' option for checkbox.', 'woocommerce-booking' ); ?>">
				<select class="ib-md" v-model="data.resources.settings.resource_selection">
					<option v-for="(type,key) in data.settings.resource_selection_types" v-bind:value="key">
						{{type}}</option>
				</select>
			</div>
		</div>
	</div>

	<div class="tm1-row" v-show="'on' === data.resources.settings.bkap_resource">
		<div class="col-left">
            <label><?php esc_attr_e( 'Consider Product\'s Max Booking', 'woocommerce-booking' ); // phpcs:ignore ?></label>
		</div>
		<div class="col-right">
			<div class="rc-flx-wrap flx-aln-center">
				<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>" alt="Tooltip"
					data-toggle="tooltip" data-placement="top"
					title="<?php esc_attr_e( 'Enabling this option will override the product\'s max booking over resource\'s available quantity.', 'woocommerce-booking' ); ?>">
				<label class="el-switch el-switch-green">
					<input type="checkbox" v-model="data.resources.settings.consider_product_max_booking"
						true-value="on" false-value="">
					<span class="el-switch-style"></span>
				</label>
			</div>
		</div>
	</div>

	<div class="tm1-row" v-show="'on' === data.resources.settings.bkap_resource">
		<div class="col-left">
			<label><?php esc_attr_e( 'Sort Resources', 'woocommerce-booking' ); ?></label>
		</div>
		<div class="col-right">
			<div class="rc-flx-wrap flx-aln-center">
				<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>" alt="Tooltip"
					data-toggle="tooltip" data-placement="top"
					title="<?php esc_attr_e( 'Enabling this option will sort the resources by menu order on the front end.', 'woocommerce-booking' ); ?>">
				<select class="ib-md" v-model="data.resources.settings.resource_sort_option">
					<option v-for="(option,key) in data.settings.resource_sort_options" v-bind:value="key" v-bind:title="option.title">
						{{option.label}}</option>
				</select>
			</div>
		</div>
	</div>

	<div class="tm1-row bdr-0 pt-0" :class="{'display-block':'on' === data.resources.settings.bkap_resource}"
		v-show="'on' === data.resources.settings.bkap_resource">
		<div class="tbl-mod-2">
			<div :class="{'tbl-responsive': data.toggle_edit_mode.resource_settings, 'tm2-inner-wrap': true}">
				<table class="table table_resource_settings">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Resource', 'woocommerce-booking' ); ?></th>
							<th><?php esc_html_e( 'Base Cost', 'woocommerce-booking' ); ?></th>
							<th><?php esc_html_e( 'Action', 'woocommerce-booking' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(row,index) in data.resources.settings.data.resource_settings">
							<td>
								<span v-show="!row.edit">{{data.settings.resources[row.resource_id]}}</span>
								<select v-show="row.edit" class="ib-md" v-model="row.resource_id">
									<option v-for="(resource,resource_id) in data.settings.resources"
										v-bind:value="resource_id" v-html="resource"></option>
								</select>
								<input v-show="row.edit && 'new_resource' === row.resource_id" class="ib-md resource_title_input" type="text"
									v-model="row.resource_title" />
							</td>

							<td>
								<span v-show="!row.edit"
									v-html="'' !== row.base_cost ? `${data.settings.currency_symbol}${row.base_cost}` : ''"></span>
								<input v-show="row.edit" class="ib-sm wc_input_price" type="text"
									v-on:keyup="data.fn.only_numbers_and_decimals" v-model="row.base_cost">
							</td>

							<td>
								<a href="javascript:void(0);" class="a-link-edit"
									v-on:click.stop="data.fn.resources.edit_row(row,index,data)"
									v-show="!row.edit">Edit</a>
								<a href="javascript:void(0);" class="a-link-delete"
									v-on:click.stop="data.fn.resources.delete_row(row,index,data)">Delete</a>
								<a :href="`${data.resources.site_url}admin.php?page=bkap_page&resource_id=${row.resource_id}&action=resources#/`" class="a-link-view" target="_blank"
									v-show="!row.edit">View</a>
							</td>
						</tr>
					</tbody>
				</table>

				<div class="add-more-link display-flex-and-space-between">
					<a class="al-link" v-on:click.stop="data.fn.resources.add_row(data)"><img
							src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-plus.svg' ); ?>" alt="Icon" />
						<?php esc_attr_e( 'Add/Link Resource', 'woocommerce-booking' ); ?></a>

					<a class="al-link" v-show="data.resources.settings.data.resource_settings.length > 0"
						v-on:click.stop="data.fn.resources.delete_all_rows(data)"><img
							src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-trash.svg' ); ?>" alt="Icon" />
						<?php esc_attr_e( 'Delete All Linked Resource Data', 'woocommerce-booking' ); ?></a>
				</div>
			</div>
		</div>
	</div>

	<div class="rb1-row flx-center mb-3 mt-2" v-if="'undefined' === typeof data.is_bulk_booking">
		<div class="rb-col">
			<a href="javascript:void(0);" class="secondary-btn"
				v-on:click.stop="data.fn.save_settings('resources',data)">{{data.settings.save_settings_button}}</a>
		</div>
	</div>
</div>
