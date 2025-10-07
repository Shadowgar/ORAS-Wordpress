<div class="bkap_admin_loader" v-show="data.loader.loader_deleting_timeslots">
	<div class="bkap_admin_loader_wrapper">
		{{data.settings.labels.loader_deleting_timeslots}} <img
			src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
	</div>
</div>

<div class="bkap_admin_loader" v-show="data.loader.loader_updating_timeslots">
	<div class="bkap_admin_loader_wrapper">
		{{data.settings.labels.loader_updating_timeslots}} <img
			src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
	</div>
</div>

<div class="bkap_admin_loader" v-show="data.loader.loader_deleting_all_timeslots">
	<div class="bkap_admin_loader_wrapper">
		{{data.settings.labels.loader_deleting_all_timeslots}} <img
			src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
	</div>
</div>

<div class="bkap_admin_loader" v-show="data.loader.loader_saving_availability_settings">
	<div class="bkap_admin_loader_wrapper">
		{{data.settings.labels.loader_saving_availability_settings}} <img
			src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
	</div>
</div>

<div class="bkap_admin_loader" v-show="data.loader.loader_deleting_manage_availability_data">
	<div class="bkap_admin_loader_wrapper">
		{{data.settings.labels.loader_deleting_manage_availability_data}} <img
			src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
	</div>
</div>

<div class="tbl-mod-1 tbl-metabox" v-show="data.sidebar.items.availability">
	<div class="tm1-row">
		<div class="col-left">
			<label><?php esc_attr_e( 'Advance Booking Period (in hours)', 'woocommerce-booking' ); ?></label>
		</div>
		<div class="col-right">
			<div class="row-box-1">
				<div class="rb1-left">
					<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>" alt="Tooltip"
						data-toggle="tooltip" data-placement="top"
						title="<?php esc_attr_e( 'Enable Booking after X number of hours from the current time. The customer can select a booking date/time slot that is available only after the minimum hours that are entered here. For example, if you need 12 hours advance notice for a booking, enter 12 here.', 'woocommerce-booking' ); ?>">
				</div>
				<div class="rb1-right">
					<div class="rb1-row flx-center">
						<div class="rb-col">
							<input class="ib-sm" type="number" min=0
								v-model="data.availability.settings.booking_minimum_number_days">
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="tm1-row">
		<div class="col-left">
			<label><?php esc_attr_e( 'Number Of Dates To Choose', 'woocommerce-booking' ); ?></label>
		</div>
		<div class="col-right">
			<div class="row-box-1">
				<div class="rb1-left">
					<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>" alt="Tooltip"
						data-toggle="tooltip" data-placement="top"
						title="<?php esc_attr_e( 'The maximum number of booking dates you want to be available for your customers to choose from. For example, if you take only 2 months booking in advance, enter 60 here.', 'woocommerce-booking' ); ?>">
				</div>
				<div class="rb1-right">
					<div class="rb1-row flx-center">
						<div class="rb-col">
							<input class="ib-sm" type="number" min=0 max="9999"
								:disabled="'' !== data.availability.settings.booking_date_range && data.availability.settings.booking_date_range > 0"
								v-model="data.availability.settings.booking_maximum_number_days">
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="tm1-row" v-show="'multiple_days' === data.general.settings.booking_type">
		<div class="col-left">
			<label><?php esc_attr_e( 'Maximum Bookings On Any Date', 'woocommerce-booking' ); ?></label>
		</div>
		<div class="col-right">
			<div class="row-box-1">
				<div class="rb1-left">
					<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>" alt="Tooltip"
						data-toggle="tooltip" data-placement="top"
						title="<?php esc_attr_e( 'Set this field if you want to place a limit on maximum bookings on any given date. If you can manage up to 15 bookings in a day, set this value to 15. Once 15 orders have been booked, then that date will not be available for further bookings.', 'woocommerce-booking' ); ?>">
				</div>
				<div class="rb1-right">
					<div class="rb1-row flx-center">
						<div class="rb-col">
							<input class="ib-sm" type="number" min=0 max="9999"
								v-model="data.availability.settings.booking_date_lockout">
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="tm1-row" v-show="'multiple_days' === data.general.settings.booking_type">
		<div class="col-left">
			<label><?php esc_attr_e( 'Minimum Number Of Nights To Book', 'woocommerce-booking' ); ?></label>
		</div>
		<div class="col-right">
			<div class="row-box-1">
				<div class="rb1-left">
					<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>" alt="Tooltip"
						data-toggle="tooltip" data-placement="top"
						title="<?php esc_attr_e( 'The minimum number of booking days you want to book for multiple days booking. For example, if you take minimum 2 days of booking, add 2 in the field here.', 'woocommerce-booking' ); ?>">
				</div>
				<div class="rb1-right">
					<div class="rb1-row flx-center">
						<div class="rb-col">
							<input class="ib-sm" type="number" min=0 max="9999"
								v-model="data.availability.settings.booking_minimum_number_days_multiple">
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="tm1-row" v-show="'multiple_days' === data.general.settings.booking_type">
		<div class="col-left">
			<label><?php esc_attr_e( 'Maximum Number Of Nights To Book', 'woocommerce-booking' ); ?></label>
		</div>
		<div class="col-right">
			<div class="row-box-1">
				<div class="rb1-left">
					<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>" alt="Tooltip"
						data-toggle="tooltip" data-placement="top"
						title="<?php esc_attr_e( 'The maximum number of booking days you want to book for multiple days booking. For example, if you take maximum 60 days of booking, add 60 in the field here.', 'woocommerce-booking' ); ?>">
				</div>
				<div class="rb1-right">
					<div class="rb1-row flx-center">
						<div class="rb-col">
							<input class="ib-sm" type="number" min=0 max="9999"
								v-model="data.availability.settings.booking_maximum_number_days_multiple">
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="tm1-row border-top display-block">
		<div class="tbl-mod-2">
			<div class="tm2-inner-wrap">
				<table class="table weekday_settings">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Weekday', 'woocommerce-booking' ); ?></th>
							<th><?php esc_html_e( 'Bookable', 'woocommerce-booking' ); ?></th>
							<th
								v-show="'multiple_days' !== data.general.settings.booking_type && 'duration_time' !== data.general.settings.booking_type">
								<?php esc_html_e( 'Maximum bookings', 'woocommerce-booking' ); ?></th>
							<th v-html="data.settings.table_header_price"></th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="item in data.availability.settings.data.weekday_settings">
							<td>{{item.weekday}}</td>
							<td>

								<label class="el-switch el-switch-green">
									<input type="checkbox" v-model="item.status" true-value="on" false-value="">
									<span class="el-switch-style"></span>
								</label>
							</td>
							<td
								v-show="'multiple_days' !== data.general.settings.booking_type && 'duration_time' !== data.general.settings.booking_type">
								<div class="rb-col">
									<input type="number" step="1" min=0 max="9999" v-on:keyup="data.fn.only_numbers"
										:placeholder="data.settings.placeholders.max_bookings" v-model="item.lockout" />
								</div>
							</td>
							<td>
								<div class="rb-col">
									<input type="number" min=0 class="wc_input_price" v-model="item.price"
										:placeholder="data.settings.placeholders.special_price">
								</div>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<div class="tm1-row">
		<div class="col-left">
			<label><?php esc_html_e( 'Set Availability By Dates/Months', 'woocommerce-booking' ); ?></label>
		</div>
		<div class="col-right">
			<div class="rc-flx-wrap flx-aln-center">
				<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>" alt="Tooltip"
					data-toggle="tooltip" data-placement="top"
					title="">
				<label class="el-switch el-switch-green">
					<input type="checkbox" v-model="data.availability.settings.enable_specific_booking" true-value="on"
						false-value="">
					<span class="el-switch-style"></span>
				</label>
			</div>
		</div>
	</div>

	<div class="tm1-row border-0 pt-0 display-scroll-x"
		:class="{'display-block':'on' === data.availability.settings.enable_specific_booking}"
		v-show="'on' === data.availability.settings.enable_specific_booking">
		<div class="tbl-mod-2">
			<div :class="{'tbl-responsive': data.toggle_edit_mode.weekday_availability, 'tm2-inner-wrap': true}">
				<table class="table table_weekday_availability">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Range Type', 'woocommerce-booking' ); ?></th>
							<th><?php esc_html_e( 'From', 'woocommerce-booking' ); ?></th>
							<th><?php esc_html_e( 'To', 'woocommerce-booking' ); ?></th>
							<th><?php esc_html_e( 'Bookable', 'woocommerce-booking' ); ?></th>
							<th><?php esc_html_e( 'Max bookings', 'woocommerce-booking' ); ?> / <br/> <?php esc_html_e( 'No. of Years', 'woocommerce-booking' ); ?></th>
							<th><?php esc_html_e( 'Action', 'woocommerce-booking' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(row,index) in data.availability.settings.data.availability">
							<td>
								<span
									v-show="!row.edit">{{data.settings.availability_range_types[row.range_type]}}</span>
								<select v-show="row.edit" class="ib-mdl" v-model="row.range_type" @change="data.fn.availability_dates_months.handle_option_change_range_type(index,data)">
									<option v-for="(range,key) in data.settings.availability_range_types"
										v-bind:value="key">{{range}}</option>
								</select>
							</td>

							<td v-show="'custom_range' === row.range_type">
								<span v-show="!row.edit">{{row.custom_range_from}}</span>
								<input v-show="row.edit" class="ib-md" type="date" v-model="row.custom_range_from" />
							</td>

							<td v-show="'custom_range' === row.range_type">
								<span v-show="!row.edit">{{row.custom_range_to}}</span>
								<input v-show="row.edit" class="ib-md" type="date" v-model="row.custom_range_to" />
							</td>

							<td colspan="2" v-show="'specific_dates' === row.range_type">
								<span v-show="!row.edit">{{row.specific_dates_date}}</span>
								<input v-on:click.stop="data.fn.initialize_datepicker($event,'',row,data)"
									class="multiple-date" data-model="specific_dates_date" v-show="row.edit"
									class="ib-md" type="text" v-model="row.specific_dates_date" />
							</td>

							<td v-show="'range_of_months' === row.range_type">
								<span
									v-show="!row.edit">{{data.settings.availability_months[row.range_of_months_from]}}</span>
								<select v-show="row.edit" class="ib-md" v-model="row.range_of_months_from">
									<option v-for="(month,key) in data.settings.availability_months" v-bind:value="key">
										{{month}}</option>
								</select>
							</td>

							<td v-show="'range_of_months' === row.range_type">
								<span
									v-show="!row.edit">{{data.settings.availability_months[row.range_of_months_to]}}</span>
								<select v-show="row.edit" class="ib-md" v-model="row.range_of_months_to">
									<option v-for="(month,key) in data.settings.availability_months" v-bind:value="key">
										{{month}}</option>
								</select>
							</td>

							<td colspan="2" v-show="'holidays' === row.range_type">
								<span v-show="!row.edit">{{row.holidays_date}}</span>
								<input v-on:click.stop="data.fn.initialize_datepicker($event,'',row,data)"
									class="multiple-date" data-model="holidays_date" v-show="row.edit" class="ib-md"
									type="text" v-model="row.holidays_date" />
							</td>

							<td>
								<span v-show="!row.edit"
									v-html="'on' === row.bookable ? data.settings.labels.yes : data.settings.labels.no"></span>
								<label v-show="row.edit" class="el-switch el-switch-green">
									<input type="checkbox" v-model="row.bookable" true-value="on" false-value=""
										:disabled="'holidays' === row.range_type">
									<span class="el-switch-style"></span>
								</label>
							</td>

							<td v-show="'custom_range' === row.range_type">
								<span v-show="!row.edit">{{row.custom_range_number_of_years}}</span>
								<input v-show="row.edit" class="ib-sm" type="number" min=0
									v-on:keyup="data.fn.only_numbers"
									:title="data.settings.titles.custom_range_number_of_years"
									:placeholder="data.settings.placeholders.number_of_years"
									v-model="row.custom_range_number_of_years">
							</td>

							<td v-show="'specific_dates' === row.range_type">
								<span v-show="!row.edit && '' !== row.specific_dates_max_bookings">{{row.specific_dates_max_bookings}}</span>
								<span v-show="!row.edit && '' !== row.specific_dates_max_bookings && '' !== row.specific_dates_price && 'undefined' !== row.specific_dates_price && 'undefined' !== typeof row.specific_dates_price"> / </span>
								<span v-show="!row.edit"
									v-html="'' !== row.specific_dates_price && 'undefined' !== row.specific_dates_price && 'undefined' !== typeof row.specific_dates_price ? `${data.settings.currency_symbol}${row.specific_dates_price}` : ''"></span>

								<input v-show="row.edit" class="ib-sm" type="number" min=0
									v-on:keyup="data.fn.only_numbers"
									:title="data.settings.titles.specific_dates_max_bookings"
									:placeholder="data.settings.placeholders.max_bookings"
									v-model="row.specific_dates_max_bookings">

								<input v-show="row.edit" class="ib-sm wc_input_price" type="number" min=0
									:placeholder="data.settings.placeholders.price"
									:title="data.settings.titles.specific_dates_price"
									v-model="row.specific_dates_price">
							</td>

							<td v-show="'range_of_months' === row.range_type">
								<span v-show="!row.edit">{{row.range_of_months_number_of_years}}</span>
								<input v-show="row.edit" class="ib-sm" type="number" min=0
									v-on:keyup="data.fn.only_numbers"
									:title="data.settings.titles.range_of_months_number_of_years"
									:placeholder="data.settings.placeholders.number_of_years"
									v-model="row.range_of_months_number_of_years">
							</td>

							<td v-show="'holidays' === row.range_type">
								<span v-show="!row.edit">{{row.holidays_number_of_years}}</span>
								<input v-show="row.edit" class="ib-sm" type="number" min=0
									v-on:keyup="data.fn.only_numbers"
									:title="data.settings.titles.holidays_number_of_years"
									:placeholder="data.settings.placeholders.number_of_years"
									v-model="row.holidays_number_of_years">
							</td>

							<td class="bkap-table-action">
								<a href="javascript:void(0);" class="a-link-update"
									v-on:click.stop="data.fn.availability_dates_months.update_row(row,index,data)"
									v-show="row.edit">Update</a>
								<a href="javascript:void(0);" class="a-link-edit"
									v-on:click.stop="data.fn.availability_dates_months.edit_row(row,index,data)"
									v-show="!row.edit">&nbsp;Edit</a>
								<a href="javascript:void(0);" class="a-link-delete"
									v-on:click.stop="data.fn.availability_dates_months.delete_row(row,index,data)">&nbsp;Delete</a>
							</td>
						</tr>
					</tbody>
				</table>

				<div class="add-more-link">
					<a class="al-link" v-on:click.stop="data.fn.availability_dates_months.add_row(data)"><img
							src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-plus.svg' ); ?>" alt="Icon" />
						<?php esc_attr_e( 'Add', 'woocommerce-booking' ); ?></a>
				</div>
			</div>
		</div>
	</div>

	<div class="tm1-row" v-if="data.availability.settings.is_multiple_timeslot_plugin_activated && ( 'date_time' === data.general.settings.booking_type || 'multidates_fixedtime' === data.general.settings.booking_type )">
		<div class="col-left">
			<label><?php esc_html_e( 'Time Slot Selection', 'woocommerce-booking' ); ?></label>
			<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
				alt="Tooltip" data-toggle="tooltip" data-placement="top"
				title="<?php esc_attr_e( 'Enable single to select single timeslot on product page or enable multiple to select multiple timeslots on product page.', 'woocommerce-booking' ); ?>">
		</div>
		<div class="col-right">
			<div class="rc-flx-wrap flx-aln-center">
				<div class="rb-flx-style mb-15">
					<div class="el-radio el-radio-green">
						<input type="radio" value="single"
							id="booking_enable_time_single"
							v-model="data.availability.settings.booking_enable_multiple_time">
						<label for="booking_enable_time_single"
							class="el-radio-style"></label>
					</div>
					<label><?php esc_attr_e( 'Single', 'woocommerce-booking' ); ?></label>
				</div>
				<div class="rb-flx-style mb-15">
					<div class="el-radio el-radio-green">
						<input type="radio" value="multiple"
							id="booking_enable_time_multiple"
							v-model="data.availability.settings.booking_enable_multiple_time">
						<label for="booking_enable_time_multiple"
							class="el-radio-style"></label>
					</div>
					<label><?php esc_attr_e( 'Multiple', 'woocommerce-booking' ); ?></label>
				</div>
			</div>
		</div>
	</div>

	<div class="tm1-row"
		:class="{'display-block':'date_time' === data.general.settings.booking_type || 'multidates_fixedtime' === data.general.settings.booking_type}"
		v-show="'date_time' === data.general.settings.booking_type || 'multidates_fixedtime' === data.general.settings.booking_type">
		<p class="label"><?php esc_html_e( 'Set Weekdays/Dates And It\'s Timeslots', 'woocommerce-booking' ); ?></label>
		<p class="mb-2">
			<?php esc_html_e( 'Create timeslots for days/dates. Enter time in 24 hours format e.g. 14:00 or leave the "TO" field blank if you do not wish to create a fixed time duration slot.', 'woocommerce-booking' ); ?>
		</p>
	</div>

	<div class="tm1-row bdr-0 pt-0 display-scroll-x"
		:class="{'display-block':'date_time' === data.general.settings.booking_type || 'multidates_fixedtime' === data.general.settings.booking_type}"
		v-show="'date_time' === data.general.settings.booking_type || 'multidates_fixedtime' === data.general.settings.booking_type">
		<div class="tbl-mod-2">
			<div :class="{'tbl-responsive': data.toggle_edit_mode.weekdays_dates_timeslots, 'tm2-inner-wrap': true}">
				<table class="table table_weekday_date_timeslots">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Global', 'woocommerce-booking' ); ?></th>
							<th><?php esc_html_e( 'Weekday', 'woocommerce-booking' ); ?></th>
							<th><?php esc_html_e( 'From', 'woocommerce-booking' ); ?></th>
							<th><?php esc_html_e( 'To', 'woocommerce-booking' ); ?></th>
							<th><?php esc_html_e( 'Max bookings', 'woocommerce-booking' ); ?></th>
							<th><?php esc_html_e( 'Price', 'woocommerce-booking' ); ?></th>
							<th><?php esc_html_e( 'Note', 'woocommerce-booking' ); ?></th>
							<th><?php esc_html_e( 'Action', 'woocommerce-booking' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(row,index) in data.availability.settings.data.weekdays_dates_timeslots">
							<td>
								<span v-show="!row.edit"
									v-html="'on' === row.global ? data.settings.labels.yes : data.settings.labels.no"></span>
								<label v-if="row.edit" class="el-switch el-switch-green">
									<input type="checkbox" v-model="row.global" true-value="on" false-value="">
									<span class="el-switch-style"></span>
								</label>
							</td>

							<td>
								<span
									v-show="!row.is_new_row">{{data.settings.weekdays_dates_timeslots_weekday[row.weekday]}}</span>
								<div v-if="row.is_new_row" class="bkap_choices_weekdays_dates_timeslots_weekday"> 
									<select 
										class="ib-md bkap_choices_js_weekdays_dates_timeslots_weekday"
										v-bind:id="`bkap_choices_js_weekdays_dates_timeslots_weekday_${index}`" multiple
										v-model="row.weekday">
										<option v-for="(weekday,key) in data.settings.weekdays_dates_timeslots_weekday"
											v-bind:value="key">{{weekday}}</option>
									</select>
								<div>
							</td>

							<td>
								<span v-show="!row.edit">{{row.from}}</span>
								<input :id='`weekdays_dates_timeslots_input_from_${index}`' v-show="row.edit"
									class="ib-md" type="text" v-model="row.from"
									v-on:keyup="data.fn.validate_weekday_date_timeslots_from_or_to($event,row.from,row.to,'weekdays_dates_timeslots_input_from_'+index,data.settings.validation_messages.weekday_timeslot_validation)"
									:title="data.settings.titles.weekdays_dates_timeslots_from_to" placeholder="HH:MM"
									minlength="5" maxlength="5" />
							</td>

							<td>
								<span v-show="!row.edit">{{ row.to != '00:00' ?  row.to : '' }}</span>
								<input :id='`weekdays_dates_timeslots_input_to_${index}`' v-show="row.edit"
									class="ib-md" type="text" v-model="row.to"
									v-on:keyup="data.fn.validate_weekday_date_timeslots_from_or_to($event,row.from,row.to,'weekdays_dates_timeslots_input_to_'+index,data.settings.validation_messages.weekday_timeslot_validation)"
									:title="data.settings.titles.weekdays_dates_timeslots_from_to" placeholder="HH:MM"
									minlength="5" maxlength="5" />
							</td>

							<td>
								<span v-show="!row.edit">{{row.lockout}}</span>
								<input v-show="row.edit" class="ib-sm" type="number" min=0
									v-on:keyup="data.fn.only_numbers"
									:placeholder="data.settings.placeholders.max_bookings" v-model="row.lockout">
							</td>

							<td>
								<span v-show="!row.edit"
									v-html="'' !== row.price ? `${data.settings.currency_symbol}${row.price}` : ''"></span>
								<input v-show="row.edit" class="ib-sm wc_input_price" type="number" min=0 step="0.00001"
									:placeholder="data.settings.placeholders.price" v-model="row.price">
							</td>

							<td>
								<span v-show="!row.edit">{{row.note}}</span>
								<textarea v-show="row.edit" v-model="row.note"></textarea>
							</td>

							<td class="bkap-table-action">
								<a href="javascript:void(0);" class="a-link-update"
									v-on:click.stop="data.fn.weekdays_dates_timeslots.update_row(row,index,data)"
									v-show="row.edit && !data.is_bulk_booking">Update</a>
								<a href="javascript:void(0);" class="a-link-edit"
									v-on:click.stop="data.fn.weekdays_dates_timeslots.edit_row(row,index,data)"
									v-show="!row.edit">&nbsp;Edit</a>
								<a href="javascript:void(0);" class="a-link-delete"
									v-on:click.stop="data.fn.weekdays_dates_timeslots.delete_row(row,index,data)">&nbsp;Delete</a>
							</td>
						</tr>
					</tbody>
				</table>

				<div class="add-more-link display-flex-and-space-between">
					<a class="al-link" v-on:click.stop="data.fn.weekdays_dates_timeslots.add_timeslot(data)"><img
							src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-plus.svg' ); ?>" alt="Icon" />
						<?php esc_attr_e( 'Add New Timeslot', 'woocommerce-booking' ); ?></a>

					<a class="al-link" v-show="data.availability.settings.data.weekdays_dates_timeslots.length > 0"
						v-on:click.stop="data.fn.weekdays_dates_timeslots.delete_all_timeslots(data)"><img
							src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-trash.svg' ); ?>" alt="Icon" />
						<?php esc_attr_e( 'Delete All Timeslots', 'woocommerce-booking' ); ?></a>
				</div>
			</div>
		</div>
	</div>

	<div v-show="'duration_time' === data.general.settings.booking_type">
		<div class="tm1-row">
			<label><?php esc_html_e( 'Set Duration Based Bookings', 'woocommerce-booking' ); ?></label>
		</div>

		<div class="tm1-row">
			<div class="col-left">
				<label><?php esc_attr_e( 'Label', 'woocommerce-booking' ); ?></label>
			</div>
			<div class="col-right">
				<div class="row-box-1">
					<div class="rb1-left">
						<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
							alt="Tooltip" data-toggle="tooltip" data-placement="top"
							title="<?php esc_attr_e( 'Set label for the duration field on the front end.', 'woocommerce-booking' ); ?>">
					</div>
					<div class="rb1-right">
						<div class="rb1-row flx-center">
							<div class="rb-col">
								<input class="ib-md" type="text" placeholder="<?php esc_attr_e( 'Label for duration.', 'woocommerce-booking' ); ?>"
									v-model="data.availability.settings.duration_based_bookings.duration_label">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="tm1-row">
			<div class="col-left">
				<label><?php esc_attr_e( 'Duration', 'woocommerce-booking' ); ?></label>
			</div>
			<div class="col-right">
				<div class="rb1-right">
					<div class="rc-flx-wrap d-flex flx-aln-center">
						<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
							alt="Tooltip" data-toggle="tooltip" data-placement="top"
							title="<?php esc_attr_e( 'Lengh of the time. Set value to 2 hours/minutes if the duration of your service is 2 hours/minutes. All the 2 hours/minutes durations will be created from mindnight till end of the day.', 'woocommerce-booking' ); ?>">

						<input class="ib-sm mr-1" type="text"
							v-model="data.availability.settings.duration_based_bookings.duration">

						<select class="ib-sm"
							v-model="data.availability.settings.duration_based_bookings.duration_type">
							<option v-for="(type,key) in data.settings.duration_types" v-bind:value="key">
								{{type}}</option>
						</select>
					</div>
				</div>
			</div>
		</div>

		<div class="tm1-row">
			<div class="col-left">
				<label><?php esc_attr_e( 'Gap Between Durations', 'woocommerce-booking' ); ?></label>
			</div>
			<div class="col-right">
				<div class="rb1-right">
					<div class="rc-flx-wrap d-flex flx-aln-center">
						<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
							alt="Tooltip" data-toggle="tooltip" data-placement="top"
							title="<?php esc_attr_e( 'Set gap between two times. Set value to 2 hours/minutes if the gap between your service is 2 hours/minutes. All the duration will be created considering the gap time.', 'woocommerce-booking' ); ?>">

						<input class="ib-sm mr-1" type="text"
							v-model="data.availability.settings.duration_based_bookings.duration_gap">

						<select class="ib-sm"
							v-model="data.availability.settings.duration_based_bookings.duration_gap_type">
							<option v-for="(type,key) in data.settings.duration_types" v-bind:value="key">
								{{type}}</option>
						</select>
					</div>
				</div>
			</div>
		</div>

		<div class="tm1-row">
			<div class="col-left">
				<label><?php esc_attr_e( 'Minimum Duration', 'woocommerce-booking' ); ?></label>
			</div>
			<div class="col-right">
				<div class="row-box-1">
					<div class="rb1-left">
						<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
							alt="Tooltip" data-toggle="tooltip" data-placement="top"
							title="<?php esc_attr_e( 'Minimum duration value a customer can select to book the service.', 'woocommerce-booking' ); ?>">
					</div>
					<div class="rb1-right">
						<div class="rb1-row flx-center">
							<div class="rb-col">
								<input class="ib-sm" type="number" min="1"
									v-model="data.availability.settings.duration_based_bookings.duration_min">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="tm1-row">
			<div class="col-left">
				<label><?php esc_attr_e( 'Maximum Duration', 'woocommerce-booking' ); ?></label>
			</div>
			<div class="col-right">
				<div class="row-box-1">
					<div class="rb1-left">
						<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
							alt="Tooltip" data-toggle="tooltip" data-placement="top"
							title="<?php esc_attr_e( 'Maximum duration value a customer can select to book the service.', 'woocommerce-booking' ); ?>">
					</div>
					<div class="rb1-right">
						<div class="rb1-row flx-center">
							<div class="rb-col">
								<input class="ib-sm" type="number" min="1" max="24"
									v-model="data.availability.settings.duration_based_bookings.duration_max">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="tm1-row">
			<div class="col-left">
				<label><?php esc_attr_e( 'Maximum Booking', 'woocommerce-booking' ); ?></label>
			</div>
			<div class="col-right">
				<div class="row-box-1">
					<div class="rb1-left">
						<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
							alt="Tooltip" data-toggle="tooltip" data-placement="top"
							title="<?php esc_attr_e( 'Set this field if you want to place a limit on maximum bookings on the duration. If you can manage up to 15 bookings in a duration, set this value to 15. Once 15 orders have been booked, then that duration will not be available for further bookings.', 'woocommerce-booking' ); ?>">
					</div>
					<div class="rb1-right">
						<div class="rb1-row flx-center">
							<div class="rb-col">
								<input class="ib-sm" type="number" min="0" max="24"
									v-model="data.availability.settings.duration_based_bookings.duration_max_booking">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="tm1-row">
			<div class="col-left">
				<label><?php esc_attr_e( 'Duration Price', 'woocommerce-booking' ); ?></label>
			</div>
			<div class="col-right">
				<div class="row-box-1">
					<div class="rb1-left">
						<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
							alt="Tooltip" data-toggle="tooltip" data-placement="top"
							title="<?php esc_attr_e( 'Price for the duration.', 'woocommerce-booking' ); ?>">
					</div>
					<div class="rb1-right">
						<div class="rb1-row flx-center">
							<div class="rb-col">
								<input class="ib-sm" type="text" placeholder="<?php esc_attr_e( 'Price', 'woocommerce-booking' ); ?>" v-on:keyup="data.fn.only_numbers_and_decimals"
									v-model="data.availability.settings.duration_based_bookings.duration_price">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="tm1-row">
			<div class="col-left">
				<label><?php esc_attr_e( 'Duration Start & End Range For Days', 'woocommerce-booking' ); ?></label>
			</div>
			<div class="rb1-right">
				<div class="rc-flx-wrap d-flex flx-aln-center">
					<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>" alt="Tooltip"
						data-toggle="tooltip" data-placement="top"
						title="<?php esc_attr_e( 'Set this field if you want to start the duration from perticular time. If your day starts at 10:00am then you can set this value to 10:00. All the durations will be created from 10:00am till the value set in the Duration ends at option. If the Duration ends at option is blank then duration end time will be considered till end of the day.', 'woocommerce-booking' ); ?>">

					<input class="ib-sm mr-1" type="text" placeholder="HH:MM"
						v-model="data.availability.settings.duration_based_bookings.first_duration">

					<input class="ib-sm" type="text" placeholder="HH:MM"
						v-model="data.availability.settings.duration_based_bookings.end_duration">
				</div>
			</div>
		</div>
	</div>

	<div class="tm1-row"
		:class="{'display-block':'date_time' === data.general.settings.booking_type || 'duration_time' === data.general.settings.booking_type || 'multidates_fixedtime' === data.general.settings.booking_type}"
		v-show="'date_time' === data.general.settings.booking_type || 'duration_time' === data.general.settings.booking_type || 'duration_time' === data.general.settings.booking_type">
		<p class="label"><?php esc_html_e( 'Manage Time Availability', 'woocommerce-booking' ); ?></p>
		<p class="mb-2">
			<?php esc_html_e( 'Setup Time Availability data for your store. Rules with lower priority values will override other rules with higher priority values. Ex. 9 overrides 10.', 'woocommerce-booking' ); ?>
		</p>
	</div>

	<div class="tm1-row border-0 pt-0 display-scroll-x"
		:class="{'display-block':'date_time' === data.general.settings.booking_type || 'duration_time' === data.general.settings.booking_type || 'multidates_fixedtime' === data.general.settings.booking_type}"
		v-show="'date_time' === data.general.settings.booking_type || 'duration_time' === data.general.settings.booking_type">
		<div class="tbl-mod-2">
			<div :class="{'tbl-responsive': data.toggle_edit_mode.manage_availability, 'tm2-inner-wrap': true}">
				<table class="table table_weekday_availability">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Range Type', 'woocommerce-booking' ); ?></th>
							<th><?php esc_html_e( 'From', 'woocommerce-booking' ); ?></th>
							<th><?php esc_html_e( 'To', 'woocommerce-booking' ); ?></th>
							<th><?php esc_html_e( 'Bookable', 'woocommerce-booking' ); ?></th>
							<th><?php esc_html_e( 'Priority', 'woocommerce-booking' ); ?></th>
							<th><?php esc_html_e( 'Action', 'woocommerce-booking' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(row,index) in data.availability.settings.data.manage_availability">
							<td>
								<span
									v-show="!row.edit">{{'time' === row.range_type.substring(0,4) ? data.settings.range_type_time_data[row.range_type] : data.settings.range_type_general[row.range_type]}}</span>
								<select v-show="row.edit" class="ib-md" v-model="row.range_type">
									<option v-for="(value,key) in data.settings.range_type_general" v-bind:value="key">
										{{value}}</option>

									<optgroup label="<?php esc_html_e( 'Time Ranges', 'woocommerce-booking' ); ?>">
										<option v-for="(value,key) in data.settings.range_type_time_data"
											v-bind:value="key">
											{{value}}</option>
									</optgroup>
								</select>
							</td>

							<td v-show="'days' === row.range_type">
								<span v-show="!row.edit">{{data.settings.intervals.days[row.range_days_from]}}</span>
								<select v-show="row.edit" class="ib-md" v-model="row.range_days_from">
									<option v-for="(value,key) in data.settings.intervals.days" v-bind:value="key">
										{{value}}</option>
								</select>
							</td>

							<td v-show="'days' === row.range_type">
								<span v-show="!row.edit">{{data.settings.intervals.days[row.range_days_to]}}</span>
								<select v-show="row.edit" class="ib-md" v-model="row.range_days_to">
									<option v-for="(value,key) in data.settings.intervals.days" v-bind:value="key">
										{{value}}</option>
								</select>
							</td>

							<td v-show="'months' === row.range_type">
								<span
									v-show="!row.edit">{{data.settings.intervals.months[row.range_months_from]}}</span>
								<select v-show="row.edit" class="ib-md" v-model="row.range_months_from">
									<option v-for="(value,key) in data.settings.intervals.months" v-bind:value="key">
										{{value}}</option>
								</select>
							</td>

							<td v-show="'months' === row.range_type">
								<span v-show="!row.edit">{{data.settings.intervals.months[row.range_months_to]}}</span>
								<select v-show="row.edit" class="ib-md" v-model="row.range_months_to">
									<option v-for="(value,key) in data.settings.intervals.months" v-bind:value="key">
										{{value}}</option>
								</select>
							</td>

							<td v-show="'weeks' === row.range_type">
								<span v-show="!row.edit">{{data.settings.intervals.weeks[row.range_weeks_from]}}</span>
								<select v-show="row.edit" class="ib-md" v-model="row.range_weeks_from">
									<option v-for="(value,key) in data.settings.intervals.weeks" v-bind:value="key">
										{{value}}</option>
								</select>
							</td>

							<td v-show="'weeks' === row.range_type">
								<span v-show="!row.edit">{{data.settings.intervals.weeks[row.range_weeks_to]}}</span>
								<select v-show="row.edit" class="ib-md" v-model="row.range_weeks_to">
									<option v-for="(value,key) in data.settings.intervals.weeks" v-bind:value="key">
										{{value}}</option>
								</select>
							</td>

							<td v-show="'custom' === row.range_type">
								<span v-show="!row.edit">{{row.range_date_from}}</span>
								<input v-show="row.edit" class="ib-md" type="date" v-model="row.range_date_from" />
							</td>

							<td v-show="'custom' === row.range_type">
								<span v-show="!row.edit">{{row.range_date_to}}</span>
								<input v-show="row.edit" class="ib-md" type="date" v-model="row.range_date_to" />
							</td>

							<td
								v-show="'time' === row.range_type || ('time:range' !== row.range_type && 'time:' === row.range_type.substring(0,5))">
								<span v-show="!row.edit">{{row.range_time_from}}</span>
								<input v-show="row.edit" class="ib-md" type="time" v-model="row.range_time_from" />
							</td>

							<td
								v-show="'time' === row.range_type || ('time:range' !== row.range_type && 'time:' === row.range_type.substring(0,5))">
								<span v-show="!row.edit">{{row.range_time_to}}</span>
								<input v-show="row.edit" class="ib-md" type="time" v-model="row.range_time_to" />
							</td>

							<td v-show="'time:range' === row.range_type">
								<span v-show="!row.edit">{{row.range_time_range_date_from}}</span>
								<input v-show="row.edit" class="ib-md" type="date"
									v-model="row.range_time_range_date_from" />

								<span v-show="!row.edit">{{row.range_time_range_time_from}}</span>
								<input v-show="row.edit" class="ib-md" type="time"
									v-model="row.range_time_range_time_from" />
							</td>

							<td v-show="'time:range' === row.range_type">
								<span v-show="!row.edit">{{row.range_time_range_date_to}}</span>
								<input v-show="row.edit" class="ib-md" type="date"
									v-model="row.range_time_range_date_to" />

								<span v-show="!row.edit">{{row.range_time_range_time_to}}</span>
								<input v-show="row.edit" class="ib-md" type="time"
									v-model="row.range_time_range_time_to" />
							</td>

							<td>
								<span v-show="!row.edit"
									v-html="'on' === row.bookable ? data.settings.labels.yes : data.settings.labels.no"></span>
								<label v-show="row.edit" class="el-switch el-switch-green">
									<input type="checkbox" v-model="row.bookable" true-value="on" false-value="">
									<span class="el-switch-style"></span>
								</label>
							</td>

							<td>
								<span v-show="!row.edit">{{row.priority}}</span>
								<input v-show="row.edit" class="ib-sm" type="number" min=0
									v-on:keyup="data.fn.only_numbers" v-model="row.priority">
							</td>

							<td class="bkap-table-action">
								<a href="javascript:void(0);" class="a-link-update"
									v-on:click.stop="data.fn.manage_availability.update_row(row,index,data)"
									v-show="row.edit">Update</a>
								<a href="javascript:void(0);" class="a-link-edit"
									v-on:click.stop="data.fn.manage_availability.edit_row(row,index,data)"
									v-show="!row.edit">&nbsp;Edit</a>
								<a href="javascript:void(0);" class="a-link-delete"
									v-on:click.stop="data.fn.manage_availability.delete_row(row,index,data)">&nbsp;Delete</a>
							</td>
						</tr>
					</tbody>
				</table>

				<div class="add-more-link">
					<a class="al-link" v-on:click.stop="data.fn.manage_availability.add_row(data)"><img
							src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-plus.svg' ); ?>" alt="Icon" />
						<?php esc_attr_e( 'Add', 'woocommerce-booking' ); ?></a>
				</div>
			</div>
		</div>
	</div>

	<div class="tm1-row"
		v-show="'date_time' === data.general.settings.booking_type || 'duration_time' === data.general.settings.booking_type">
		<div class="col-left">
            <label><?php esc_attr_e( 'Make All Data Block Unavailable', 'woocommerce-booking' ); // phpcs:ignore ?></label>
		</div>
		<div class="col-right">
			<div class="rc-flx-wrap flx-aln-center">
				<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>" alt="Tooltip"
					data-toggle="tooltip" data-placement="top"
					title="<?php esc_attr_e( 'Enable this option to make all the day/date and time unavailable except for the ranges that have been added here.', 'woocommerce-booking' ); ?>">
				<label class="el-switch el-switch-green">
					<input type="checkbox" v-model="data.availability.settings.manage_availability_all_data"
						true-value="on" false-value="">
					<span class="el-switch-style"></span>
				</label>
			</div>
		</div>
	</div>

	<div class="rb1-row flx-center mb-3 mt-2" v-if="'undefined' === typeof data.is_bulk_booking">
		<div class="rb-col">
			<a href="javascript:void(0);" class="secondary-btn"
				v-on:click.stop="data.fn.save_settings('availability',data)">{{data.settings.save_settings_button}}</a>
		</div>
	</div>
</div>
