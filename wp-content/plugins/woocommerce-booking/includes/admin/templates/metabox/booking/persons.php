<div class="bkap_admin_loader" v-show="data.loader.loader_deleting_person_data">
	<div class="bkap_admin_loader_wrapper">
		{{data.settings.labels.loader_deleting_person_data}} <img
			src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
	</div>
</div>

<div class="bkap_admin_loader" v-show="data.loader.loader_saving_persons_settings">
	<div class="bkap_admin_loader_wrapper">
		{{data.settings.labels.loader_saving_persons_settings}} <img
			src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
	</div>
</div>

<div class="bkap_admin_loader" v-show="data.loader.loader_deleting_all_person_data">
	<div class="bkap_admin_loader_wrapper">
		{{data.settings.labels.loader_deleting_all_person_data}} <img
			src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
	</div>
</div>

<div class="tbl-mod-1 tbl-metabox" v-show="data.sidebar.items.persons">
	<div class="tm1-row">
		<div class="col-left">
            <label><?php esc_attr_e( 'Enable Persons Module', 'woocommerce-booking' ); // phpcs:ignore ?></label>
		</div>
		<div class="col-right">
			<div class="rc-flx-wrap flx-aln-center">
				<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>" alt="Tooltip"
					data-toggle="tooltip" data-placement="top"
					title="<?php esc_attr_e( 'Enable this if this bookable product can be booked by a customer defined number of persons.', 'woocommerce-booking' ); ?>">
				<label class="el-switch el-switch-green">
					<input type="checkbox" v-model="data.persons.settings.bkap_person" true-value="on" false-value="">
					<span class="el-switch-style"></span>
				</label>
			</div>
		</div>
	</div>

	<div class="tm1-row" v-show="'on' === data.persons.settings.bkap_person">
		<div class="col-left">
			<label><?php esc_attr_e( 'Min Persons', 'woocommerce-booking' ); ?></label>
		</div>
		<div class="col-right">
			<div class="row-box-1">
				<div class="rb1-left">
					<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>" alt="Tooltip"
						data-toggle="tooltip" data-placement="top"
						title="<?php esc_attr_e( 'Total number of persons can not be lesser than this value.', 'woocommerce-booking' ); ?>">
				</div>
				<div class="rb1-right">
					<div class="rb1-row flx-center">
						<div class="rb-col">
							<input class="ib-sm" type="number" min=0 v-model="data.persons.settings.bkap_min_person">
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="tm1-row" v-show="'on' === data.persons.settings.bkap_person">
		<div class="col-left">
			<label><?php esc_attr_e( 'Max Persons', 'woocommerce-booking' ); ?></label>
		</div>
		<div class="col-right">
			<div class="row-box-1">
				<div class="rb1-left">
					<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>" alt="Tooltip"
						data-toggle="tooltip" data-placement="top"
						title="<?php esc_attr_e( 'Total number of persons will not exceed this value.', 'woocommerce-booking' ); ?>">
				</div>
				<div class="rb1-right">
					<div class="rb1-row flx-center">
						<div class="rb-col">
							<input class="ib-sm" type="number" min=0 v-model="data.persons.settings.bkap_max_person">
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="tm1-row" v-show="'on' === data.persons.settings.bkap_person">
		<div class="col-left">
            <label><?php esc_attr_e( 'Multiply Price By Persons Count', 'woocommerce-booking' ); // phpcs:ignore ?></label>
		</div>
		<div class="col-right">
			<div class="rc-flx-wrap flx-aln-center">
				<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>" alt="Tooltip"
					data-toggle="tooltip" data-placement="top"
					title="<?php esc_attr_e( 'All the cost will be multiplied by the number of persons.', 'woocommerce-booking' ); ?>">
				<label class="el-switch el-switch-green">
					<input type="checkbox" v-model="data.persons.settings.bkap_price_per_person" true-value="on"
						false-value="">
					<span class="el-switch-style"></span>
				</label>
			</div>
		</div>
	</div>

	<div class="tm1-row" v-show="'on' === data.persons.settings.bkap_person">
		<div class="col-left">
            <label><?php esc_attr_e( 'Consider Each Person As Booking', 'woocommerce-booking' ); // phpcs:ignore ?></label>
		</div>
		<div class="col-right">
			<div class="rc-flx-wrap flx-aln-center">
				<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>" alt="Tooltip"
					data-toggle="tooltip" data-placement="top"
					title="<?php esc_attr_e( 'Enable this to count each person as booking until the Max Bookings per block is reached.', 'woocommerce-booking' ); ?>">
				<label class="el-switch el-switch-green">
					<input type="checkbox" v-model="data.persons.settings.bkap_each_person_booking" true-value="on"
						false-value="">
					<span class="el-switch-style"></span>
				</label>
			</div>
		</div>
	</div>

	<div class="tm1-row" v-show="'on' === data.persons.settings.bkap_person">
		<div class="col-left">
            <label><?php esc_attr_e( 'Enable Person Type', 'woocommerce-booking' ); // phpcs:ignore ?></label>
		</div>
		<div class="col-right">
			<div class="rc-flx-wrap flx-aln-center">
				<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>" alt="Tooltip"
					data-toggle="tooltip" data-placement="top"
					title="<?php esc_attr_e( 'Enable this to add different types of persons and its costs, e.g Adults and Children.', 'woocommerce-booking' ); ?>">
				<label class="el-switch el-switch-green">
					<input type="checkbox" v-model="data.persons.settings.bkap_person_type" true-value="on"
						false-value="">
					<span class="el-switch-style"></span>
				</label>
			</div>
		</div>
	</div>

	<div class="tm1-row bdr-0 pt-0" :class="{'display-block':'on' === data.persons.settings.bkap_person_type}"
		v-show="'on' === data.persons.settings.bkap_person_type">
		<div class="tbl-mod-2">
			<div :class="{'tbl-responsive': data.toggle_edit_mode.persons_person_type, 'tm2-inner-wrap': true}">
				<table class="table table_persons_person_type">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Person Type', 'woocommerce-booking' ); ?></th>
							<th><?php esc_html_e( 'Base Cost', 'woocommerce-booking' ); ?></th>
							<th><?php esc_html_e( 'Block Cost', 'woocommerce-booking' ); ?></th>
							<th><?php esc_html_e( 'Minimum', 'woocommerce-booking' ); ?></th>
							<th><?php esc_html_e( 'Maximum', 'woocommerce-booking' ); ?></th>
							<th><?php esc_html_e( 'Description', 'woocommerce-booking' ); ?></th>
							<th><?php esc_html_e( 'Action', 'woocommerce-booking' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(row,index) in data.persons.settings.data.person_settings">
							<td>
								<span v-show="!row.edit">{{row.person_title}}</span>
								<input v-show="row.edit" class="ib-md" type="text" v-model="row.person_title" />
							</td>

							<td>
								<span v-show="!row.edit"
									v-html="'' !== row.base_cost ? `${data.settings.currency_symbol}${row.base_cost}` : ''"></span>
								<input v-show="row.edit" class="ib-sm wc_input_price" type="text"
									v-on:keyup="data.fn.only_numbers_and_decimals" v-model="row.base_cost">
							</td>

							<td>
								<span v-show="!row.edit"
									v-html="'' !== row.block_cost ? `${data.settings.currency_symbol}${row.block_cost}` : ''"></span>
								<input v-show="row.edit" class="ib-sm wc_input_price" type="text"
									v-on:keyup="data.fn.only_numbers_and_decimals" v-model="row.block_cost">
							</td>

							<td>
								<span v-show="!row.edit">{{row.person_min}}</span>
								<input v-show="row.edit" class="ib-sm" type="number" min=0
									v-on:keyup="data.fn.only_numbers" v-model="row.person_min">
							</td>

							<td>
								<span v-show="!row.edit">{{row.person_max}}</span>
								<input v-show="row.edit" class="ib-sm" type="number" min=0
									v-on:keyup="data.fn.only_numbers" v-model="row.person_max">
							</td>

							<td>
								<span v-show="!row.edit">{{row.person_desc}}</span>
								<input v-show="row.edit" class="ib-md" type="text" v-model="row.person_desc"
									:placeholder="data.settings.placeholders.person_desc" />
							</td>

							<td>
								<a href="javascript:void(0);" class="a-link-edit"
									v-on:click.stop="data.fn.persons.edit_row(row,index,data)"
									v-show="!row.edit">Edit</a>
								<a href="javascript:void(0);" class="a-link-delete"
									v-on:click.stop="data.fn.persons.delete_row(row,index,data)">Delete</a>
							</td>
						</tr>
					</tbody>
				</table>

				<div class="add-more-link display-flex-and-space-between">
					<a class="al-link" v-on:click.stop="data.fn.persons.add_row(data)"><img
							src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-plus.svg' ); ?>" alt="Icon" />
						<?php esc_attr_e( 'Add Person Type', 'woocommerce-booking' ); ?></a>

					<a class="al-link" v-show="data.persons.settings.data.person_settings.length > 0"
						v-on:click.stop="data.fn.persons.delete_all_rows(data)"><img
							src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-trash.svg' ); ?>" alt="Icon" />
						<?php esc_attr_e( 'Delete All Persons Data', 'woocommerce-booking' ); ?></a>
				</div>
			</div>
		</div>
	</div>

	<div class="rb1-row flx-center mb-3 mt-2" v-if="'undefined' === typeof data.is_bulk_booking">
		<div class="rb-col">
			<a href="javascript:void(0);" class="secondary-btn"
				v-on:click.stop="data.fn.save_settings('persons',data)">{{data.settings.save_settings_button}}</a>
		</div>
	</div>
</div>
