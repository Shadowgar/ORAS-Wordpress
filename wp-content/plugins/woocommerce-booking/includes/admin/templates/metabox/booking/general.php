<div class="bkap_admin_loader" v-show="data.loader.loader_saving_general_settings">
	<div class="bkap_admin_loader_wrapper">
		{{data.settings.labels.loader_saving_general_settings}} <img
			src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
	</div>
</div>

<div class="tbl-mod-1 tbl-metabox" v-show="data.sidebar.items.general">
	<div class="tm1-row">
		<div class="col-left">
            <label><?php esc_attr_e( 'Enable Booking', 'woocommerce-booking' ); // phpcs:ignore ?></label>
		</div>
		<div class="col-right">
			<div class="rc-flx-wrap flx-aln-center">
				<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>" alt="Tooltip"
					data-toggle="tooltip" data-placement="top"
					title="<?php esc_attr_e( 'Enable booking date on products page', 'woocommerce-booking' ); ?>">
				<label class="el-switch el-switch-green">
					<input type="checkbox" v-model="data.general.settings.booking_enable_date" true-value="on"
						false-value="">
					<span class="el-switch-style"></span>
				</label>
			</div>
		</div>
	</div>

	<div class="tm1-row">
		<div class="col-left">
            <label><?php esc_attr_e( 'Booking Type', 'woocommerce-booking' ); // phpcs:ignore ?></label>
		</div>
		<div class="col-right">
			<div class="rc-flx-wrap flx-aln-center">
				<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>" alt="Tooltip"
					data-toggle="tooltip" data-placement="top"
					title="<?php esc_attr_e( 'Choose booking type for your business', 'woocommerce-booking' ); ?>">
				<select class="ib-md metabox_booking_booking_types" v-model="data.general.settings.booking_type">
					<optgroup v-for="(_item,_key) in data.settings.booking_types" v-bind:label="_key">
						<option v-for="item in _item" v-bind:value="item.key">{{item.label}}</option>
					</optgroup>
				</select>
			</div>
		</div>
	</div>

	<p class="mb-3 font-bold font-italic" v-show="'only_day' === data.general.settings.booking_type"
		v-html="data.label.single_day_text"></p>
	<p class="mb-3 font-bold font-italic" v-show="'multidates' === data.general.settings.booking_type"
		v-html="data.label.multidates_text"></p>
	<p class="mb-3 font-bold font-italic" v-show="'multiple_days' === data.general.settings.booking_type"
		v-html="data.label.multiple_nights_text"></p>
	<p class="mb-3 font-bold font-italic" v-show="'multidates_fixedtime' === data.general.settings.booking_type"
		v-html="data.label.multidates_fixedtime_text"></p>
	<p class="mb-3 font-bold font-italic" v-show="'date_time' === data.general.settings.booking_type"
		v-html="data.label.fixed_time_text"></p>
	<p class="mb-3 font-bold font-italic" v-show="'duration_time' === data.general.settings.booking_type"
		v-html="data.label.duration_time_text"></p>

	<!-- Sale & Rent Options From Rental System Addon  -->

	<div class="tm1-row" v-if="data.rental.settings.is_plugin_activated && data.rental.settings.is_l_active">
		<div class="col-left">
            <label><?php esc_attr_e( 'Allow sale and rent', 'woocommerce-booking' ); // phpcs:ignore ?></label>
		</div>
		<div class="col-right">
			<div class="rc-flx-wrap flx-aln-center">
				<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>" alt="Tooltip"
					data-toggle="tooltip" data-placement="top"
					title="<?php esc_attr_e( 'Enable to show purchase mode buttons on Product Page. Priority will be given to Booking->Global Bookings Settings->Sale and Rent On Product Page. Both options should be enabled to show Sale & Rent button on the front end.', 'woocommerce-booking' ); ?>">
				<label class="el-switch el-switch-green">
					<input type="checkbox" v-model="data.general.settings.bkap_show_mode" true-value="on"
						false-value="">
					<span class="el-switch-style"></span>
				</label>
			</div>
		</div>
	</div>

	<div class="tm1-row" v-if="data.rental.settings.is_plugin_activated && data.rental.settings.is_l_active && 'on' === data.general.settings.bkap_show_mode">
		<div class="col-left">
			<label><?php esc_html_e( 'Choose mode', 'woocommerce-booking' ); ?></label>
			<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
				alt="Tooltip" data-toggle="tooltip" data-placement="top"
				title="<?php esc_attr_e( 'Select the mode of purchase as per your choice. If you want to sale the product then choose Sale and if you want to rent the product then select Rent. If you want both sale and rent behaviour for the product then choose Both option.', 'woocommerce-booking' ); ?>">
		</div>
		<div class="col-right">
			<div class="rc-flx-wrap flx-aln-center">
				<div class="rb-flx-style mb-15">
					<div class="el-radio el-radio-green">
						<input type="radio" value="sale"
							id="booking_rent_mode_sale"
							v-model="data.general.settings.bkap_purchase_mode"
							@click="data.general.settings.booking_enable_date = '';data.general.settings.booking_purchase_without_date = ''">
						<label for="booking_rent_mode_sale"
							class="el-radio-style"></label>
					</div>
					<label><?php esc_attr_e( 'Sale', 'woocommerce-booking' ); ?></label>
				</div>
				<div class="rb-flx-style mb-15">
					<div class="el-radio el-radio-green">
						<input type="radio" value="rent"
							id="booking_rent_mode_rent"
							v-model="data.general.settings.bkap_purchase_mode"
							@click="data.general.settings.booking_enable_date = 'on'; data.general.settings.booking_purchase_without_date = ''">
						<label for="booking_rent_mode_rent"
							class="el-radio-style"></label>
					</div>
					<label><?php esc_attr_e( 'Rent', 'woocommerce-booking' ); ?></label>
				</div>
				<div class="rb-flx-style mb-15">
					<div class="el-radio el-radio-green">
						<input type="radio" value="both"
							id="booking_rent_mode_both"
							v-model="data.general.settings.bkap_purchase_mode"
							@click="data.general.settings.booking_enable_date = 'on'; data.general.settings.booking_purchase_without_date = 'on'">
						<label for="booking_rent_mode_both"
							class="el-radio-style"></label>
					</div>
					<label><?php esc_attr_e( 'Both', 'woocommerce-booking' ); ?></label>
				</div>
			</div>
		</div>
	</div>

	<div class="tm1-row" v-if="data.rental.settings.is_plugin_activated && data.rental.settings.is_l_active && 'both' === data.general.settings.bkap_purchase_mode">
		<div class="col-left">
			<label><?php esc_html_e( 'Default mode should be', 'woocommerce-booking' ); ?></label>
			<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
				alt="Tooltip" data-toggle="tooltip" data-placement="top"
				title="<?php esc_attr_e( 'Select the default mode when the Sale & Rent is enabled.', 'woocommerce-booking' ); ?>">
		</div>
		<div class="col-right">
			<div class="rc-flx-wrap flx-aln-center">
				<div class="rb-flx-style mb-15">
					<div class="el-radio el-radio-green">
						<input type="radio" value="default_sale_mode"
							id="booking_default_mode_sale"
							v-model="data.general.settings.bkap_default_mode">
						<label for="booking_default_mode_sale"
							class="el-radio-style"></label>
					</div>
					<label><?php esc_attr_e( 'Sale', 'woocommerce-booking' ); ?></label>
				</div>
				<div class="rb-flx-style mb-15">
					<div class="el-radio el-radio-green">
						<input type="radio" value="default_rent_mode"
							id="booking_default_mode_rent"
							v-model="data.general.settings.bkap_default_mode">
						<label for="booking_default_mode_rent"
							class="el-radio-style"></label>
					</div>
					<label><?php esc_attr_e( 'Rent', 'woocommerce-booking' ); ?></label>
				</div>
			</div>
		</div>
	</div>

	<div class="tbl-mod-1 border-top">
		<div class="tm1-row"
			v-show="'multidates' === data.general.settings.booking_type || 'multidates_fixedtime' === data.general.settings.booking_type">
			<div class="col-left">
				<label><?php esc_attr_e( 'Type Of Selection', 'woocommerce-booking' ); ?></label>
			</div>
			<div class="col-right">
				<div class="rc-flx-wrap flx-aln-center">
					<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>" alt="Tooltip"
						data-toggle="tooltip" data-placement="top"
						title="<?php esc_attr_e( 'Allow customers to choose fixed number of days or variable days based on a range.', 'woocommerce-booking' ); ?>">
					<select class="ib-md" v-model="data.general.settings.multidates_type">
						<option v-for="(type,key) in data.settings.multidates_selection_type"
							v-bind:value="key">
							{{type}}</option>
					</select>
				</div>
			</div>
		</div>

		<div class="tm1-row"
			v-show="('multidates' === data.general.settings.booking_type || 'multidates_fixedtime' === data.general.settings.booking_type) && 'fixed' === data.general.settings.multidates_type">
			<div class="col-left">
				<label><?php esc_attr_e( 'Numbers Of Dates', 'woocommerce-booking' ); ?></label>
			</div>
			<div class="col-right">
				<div class="row-box-1">
					<div class="rb1-left">
						<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
							alt="Tooltip" data-toggle="tooltip" data-placement="top"
							title="<?php esc_attr_e( 'Fixed number of dates the customer has to select while placing a booking. The value to this option should be 2 or higher.', 'woocommerce-booking' ); ?>">
					</div>
					<div class="rb1-right">
						<div class="rb1-row flx-center">
							<div class="rb-col">
								<input class="ib-sm" type="number" min="2" max="9999"
									v-on:keyup="data.fn.validate_field($event,2,'Numbers of dates')"
									v-model="data.general.settings.multidates_fixed_number">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="tm1-row"
			v-show="('multidates' === data.general.settings.booking_type || 'multidates_fixedtime' === data.general.settings.booking_type) && 'range' === data.general.settings.multidates_type">
			<div class="col-left">
				<label><?php esc_attr_e( 'Minimum Date(s)', 'woocommerce-booking' ); ?></label>
			</div>
			<div class="col-right">
				<div class="row-box-1">
					<div class="rb1-left">
						<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
							alt="Tooltip" data-toggle="tooltip" data-placement="top"
							title="<?php esc_attr_e( 'Set minimum date(s) the customer can select while placing a booking.', 'woocommerce-booking' ); ?>">
					</div>
					<div class="rb1-right">
						<div class="rb1-row flx-center">
							<div class="rb-col">
								<input class="ib-sm" type="number" min="1" max="9999"
									v-model="data.general.settings.multidates_range_min">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="tm1-row"
			v-show="('multidates' === data.general.settings.booking_type || 'multidates_fixedtime' === data.general.settings.booking_type) && 'range' === data.general.settings.multidates_type">
			<div class="col-left">
				<label><?php esc_attr_e( 'Maximum Dates', 'woocommerce-booking' ); ?></label>
			</div>
			<div class="col-right">
				<div class="row-box-1">
					<div class="rb1-left">
						<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
							alt="Tooltip" data-toggle="tooltip" data-placement="top"
							title="<?php esc_attr_e( 'Set maximum dates the customer can select while placing a booking.', 'woocommerce-booking' ); ?>">
					</div>
					<div class="rb1-right">
						<div class="rb1-row flx-center">
							<div class="rb-col">
								<input class="ib-sm" type="number" min="1" max="9999"
									v-model="data.general.settings.multidates_range_max">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="tm1-row">
			<div class="col-left">
                <label><?php esc_attr_e( 'Enable Inline Calendar', 'woocommerce-booking' ); // phpcs:ignore ?></label>
			</div>
			<div class="col-right">
				<div class="rc-flx-wrap flx-aln-center">
					<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>" alt="Tooltip"
						data-toggle="tooltip" data-placement="top"
						title="<?php esc_attr_e( 'Enable inline calendar on products page', 'woocommerce-booking' ); ?>">
					<label class="el-switch el-switch-green">
						<input type="checkbox" v-model="data.general.settings.enable_inline_calendar" true-value="on"
							false-value="">
						<span class="el-switch-style"></span>
					</label>
				</div>
			</div>
		</div>

		<div class="tm1-row">
			<div class="col-left">
                <label><?php esc_attr_e( 'Purchase Without Choosing a Date', 'woocommerce-booking' ); // phpcs:ignore ?></label>
			</div>
			<div class="col-right">
				<div class="rc-flx-wrap flx-aln-center">
					<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>" alt="Tooltip"
						data-toggle="tooltip" data-placement="top"
						title="<?php esc_attr_e( 'Enables your customers to purchase without choosing a date. Select this option if you want the ADD TO CART button always visible on the product page. Cannot be applied to products that require confirmation.', 'woocommerce-booking' ); ?>">
					<label class="el-switch el-switch-green">
						<input type="checkbox" v-model="data.general.settings.booking_purchase_without_date"
							true-value="on" false-value="">
						<span class="el-switch-style"></span>
					</label>
				</div>
			</div>
		</div>

		<div class="tm1-row"
			v-show="('only_day' === data.general.settings.booking_type || 'date_time' === data.general.settings.booking_type || 'duration_time' == data.general.settings.booking_type )">
			<div class="col-left">
                <label><?php esc_attr_e( 'Show Dates In Dropdown', 'woocommerce-booking' ); // phpcs:ignore ?></label>
			</div>
			<div class="col-right">
				<div class="rc-flx-wrap flx-aln-center">
					<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>" alt="Tooltip"
						data-toggle="tooltip" data-placement="top"
						title="<?php esc_attr_e( 'Enable this setting if you want to show dates in dropdown instead of calendar (This option will not work with Multiple Nights booking type)', 'woocommerce-booking' ); ?>">
					<label class="el-switch el-switch-green">
						<input type="checkbox" v-model="data.general.settings.show_dates_dropdown" true-value="on"
							false-value="">
						<span class="el-switch-style"></span>
					</label>
				</div>
			</div>
		</div>

		<div class="tm1-row">
			<div class="col-left">
                <label><?php esc_attr_e( 'Requires Confirmation', 'woocommerce-booking' ); // phpcs:ignore ?></label>
			</div>
			<div class="col-right">
				<div class="rc-flx-wrap flx-aln-center">
					<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>" alt="Tooltip"
						data-toggle="tooltip" data-placement="top"
						title="<?php esc_attr_e( 'Enable this setting if the booking requires admin approval/confirmation. Payment will not be taken at checkout', 'woocommerce-booking' ); ?>">
					<label class="el-switch el-switch-green">
						<input type="checkbox" v-model="data.general.settings.booking_confirmation" true-value="on"
							false-value="">
						<span class="el-switch-style"></span>
					</label>
				</div>
			</div>
		</div>

		<div class="tm1-row">
			<div class="col-left">
                <label><?php esc_attr_e( 'Can Be Cancelled', 'woocommerce-booking' ); // phpcs:ignore ?></label>
			</div>
			<div class="col-right">
				<div class="rc-flx-wrap flx-aln-center">
					<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>" alt="Tooltip"
						data-toggle="tooltip" data-placement="top"
						title="<?php esc_attr_e( 'When enabled, this allows bookings to be cancelled by customers. Bookings can be cancelled anytime or at some duration before the booking.', 'woocommerce-booking' ); ?>">
					<label class="el-switch el-switch-green">
						<input type="checkbox" v-model="data.general.settings.bkap_can_be_cancelled" true-value="on"
							false-value="">
						<span class="el-switch-style"></span>
					</label>
				</div>
			</div>
		</div>

		<div class="tm1-row bkap_can_be_cancelled_div" v-show="'on' === data.general.settings.bkap_can_be_cancelled">
			<div class="col-left">
                <label><?php esc_attr_e( 'Booking can be cancelled until', 'woocommerce-booking' ); // phpcs:ignore ?></label>
			</div>
			<div class="col-right">
				<div class="rc-flx-wrap flx-aln-center">
					<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>" alt="Tooltip"
						data-toggle="tooltip" data-placement="top"
						title="">
					<input type="number" v-model="data.general.settings.bkap_can_be_cancelled_duration"
						v-bind:placeholder="data.label.input_duration_text" min="0">
					<select class="ib-md" v-model="data.general.settings.bkap_can_be_cancelled_period">
						<option v-for="(item,key) in data.settings.booking_can_be_cancelled_periods" v-bind:value="key"
							v-bind:disabled="'' === key">
							{{item}}</option>
					</select>
				</div>
			</div>
		</div>
	</div>

	<div class="rb1-row flx-center mb-3 mt-2" v-if="'undefined' === typeof data.is_bulk_booking">
		<div class="rb-col">
			<a href="javascript:void(0);" class="secondary-btn"
				v-on:click.stop="data.fn.save_settings('general',data)">{{data.settings.save_settings_button}}</a>
		</div>
	</div>
</div>
