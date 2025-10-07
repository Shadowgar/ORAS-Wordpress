<div class="bkap_admin_loader" v-show="data.loader.loader_saving_partial_payments_settings">
	<div class="bkap_admin_loader_wrapper">
		{{data.settings.labels.loader_saving_partial_payments_settings}} <img
			src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
	</div>
</div>

<div class="tbl-mod-1 tbl-metabox"
	v-if="data.sidebar.items.partial_payments && !data.partial_payments.settings.is_plugin_activated">
	<div class="alert alert-danger alert-dismissible fade show" role="alert">
		<span v-html="data.label.partial_payments_plugin_not_activated_message"></span>
	</div>
</div>

<div class="tbl-mod-1 tbl-metabox"
	v-if="data.sidebar.items.partial_payments && !data.partial_payments.settings.is_l_active">
	<div class="alert alert-danger alert-dismissible fade show" role="alert">
		<span v-html="data.label.bl_error_message"></span>
	</div>
</div>

<div class="tbl-mod-1 tbl-metabox" v-show="data.sidebar.items.partial_payments && data.partial_payments.settings.is_plugin_activated && data.partial_payments.settings.is_l_active">
	
	<div class="tm1-row">
		<div class="col-left">
			<label><?php esc_attr_e( 'Enable partial payment', 'woocommerce-booking' ); // phpcs:ignore ?></label>
		</div>
		<div class="col-right">
			<div class="rc-flx-wrap flx-aln-center">
				<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>" alt="Tooltip"
					data-toggle="tooltip" data-placement="top"
					title="<?php esc_attr_e( 'Please select this checkbox if you want to enable accepting partial payments (deposits) for your bookings.', 'woocommerce-booking' ); ?>">
				<label class="el-switch el-switch-green">
					<input type="checkbox" v-model="data.partial_payments.settings.booking_partial_payment_enable" true-value="yes"
						false-value="">
					<span class="el-switch-style"></span>
				</label>
			</div>
		</div>
	</div>

	<div class="tm1-row">
		<div class="col-left">
			<label><?php esc_attr_e( 'Partial payment type', 'woocommerce-booking' ); ?></label>
		</div>
		<div class="col-right">
			<div class="row-box-1">
				<div class="rb1-left">
					<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>" alt="Tooltip"
						data-toggle="tooltip" data-placement="top"
						title="<?php esc_attr_e( 'Please choose the payment type for initial deposit. It could be a flat charge of a specific amount or a percentage charge relative to the current price or a flat security deposit charge to the product price.', 'woocommerce-booking' ); ?>">
				</div>
				<div class="rb1-right">
					<div class="rc-flx-wrap flx-aln-center">
						<select class="ib-md metabox_partial_payment_types" v-model="data.partial_payments.settings.booking_partial_payment_radio">
							<option
								v-for="(value,key) in data.settings.partial_payment_type"
								v-bind:value="key">{{value}}</option>
						</select>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="tm1-row">
		<div class="col-left">
			<label><?php esc_attr_e( 'Deposit', 'woocommerce-booking' ); ?></label>
		</div>
		<div class="col-right">
			<div class="row-box-1">
				<div class="rb1-left">
					<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>" alt="Tooltip"
						data-toggle="tooltip" data-placement="top"
						title="<?php esc_attr_e( 'Please enter the value corresponding to the above selected Payment Type.', 'woocommerce-booking' ); ?>">
				</div>
				<div class="rb1-right">
					<div class="rb1-row flx-center">
						<div class="rb-col">
							<input class="ib-sm" type="number" min=0 v-model="data.partial_payments.settings.booking_partial_payment_value_deposit">
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="tm1-row">
		<div class="col-left">
			<label><?php esc_attr_e( 'Allow Full payment when booking', 'woocommerce-booking' ); // phpcs:ignore ?></label>
		</div>
		<div class="col-right">
			<div class="rc-flx-wrap flx-aln-center">
				<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>" alt="Tooltip"
					data-toggle="tooltip" data-placement="top"
					title="<?php esc_attr_e( 'Please select this checkbox if you want to allow customer choose to pay full amount', 'woocommerce-booking' ); ?>">
				<label class="el-switch el-switch-green">
					<input type="checkbox" v-model="data.partial_payments.settings.allow_full_payment" true-value="yes"
						false-value="">
					<span class="el-switch-style"></span>
				</label>
			</div>
		</div>
	</div>

	<div class="tm1-row">
		<div class="col-left">
			<label><?php esc_attr_e( 'Only full payment within X days from today', 'woocommerce-booking' ); ?></label>
		</div>
		<div class="col-right">
			<div class="row-box-1">
				<div class="rb1-left">
					<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>" alt="Tooltip"
						data-toggle="tooltip" data-placement="top"
						title="<?php esc_attr_e( 'This field should contain the number of days within which you would like to receive full payment when booking. For example: If you would like to receive full payment for bookings made within the next 1 week from today, this field should be set to 7.', 'woocommerce-booking' ); ?>">
				</div>
				<div class="rb1-right">
					<div class="rb1-row flx-center">
						<div class="rb-col">
							<input class="ib-sm" type="number" min=0 v-model="data.partial_payments.settings.booking_deposit_x_days">
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="tm1-row">
		<div class="col-left">
			<label><?php esc_attr_e( 'Default payment type', 'woocommerce-booking' ); ?></label>
		</div>
		<div class="col-right">
			<div class="row-box-1">
				<div class="rb1-left">
					<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>" alt="Tooltip"
						data-toggle="tooltip" data-placement="top"
						title="<?php esc_attr_e( 'Please choose the default payment type for initial deposit. If Full payment is selected then full payment price will be displayed as the default price on the product page.', 'woocommerce-booking' ); ?>">
				</div>
				<div class="rb1-right">
					<div class="rc-flx-wrap flx-aln-center ro-wrap">
						<div class="rb-flx-style mb-15">
							<div class="el-radio el-radio-green">
								<input type="radio" value="partial_payment"
									id="partial_payment"
									v-model="data.partial_payments.settings.booking_default_payment_radio">
								<label for="partial_payment"
									class="el-radio-style"></label>
							</div>
							<label><?php esc_attr_e( 'Partial payment', 'woocommerce-booking' ); ?></label>
						</div>
						<div class="rb-flx-style mb-15">
							<div class="el-radio el-radio-green">
								<input type="radio" value="full_payment"
									id="full_payment"
									v-model="data.partial_payments.settings.booking_default_payment_radio">
								<label for="full_payment"
									class="el-radio-style"></label>
							</div>
							<label><?php esc_attr_e( 'Full payment', 'woocommerce-booking' ); ?></label>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="rb1-row flx-center mb-3 mt-2" v-if="'undefined' === typeof data.is_bulk_booking">
		<div class="rb-col">
			<a href="javascript:void(0);" class="secondary-btn"
				v-on:click.stop="data.fn.save_settings('partial_payments',data)">{{data.settings.save_settings_button}}</a>
		</div>
	</div>
</div>
