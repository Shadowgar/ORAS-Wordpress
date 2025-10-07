<div class="bkap_admin_loader" v-show="data.loader.loader_saving_rental_settings">
	<div class="bkap_admin_loader_wrapper">
		{{data.settings.labels.loader_saving_persons_settings}} <img
			src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
	</div>
</div>

<div class="tbl-mod-1 tbl-metabox"
    v-if="data.sidebar.items.rental && !data.rental.settings.is_plugin_activated">
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <span v-html="data.label.rental_plugin_not_activated_message"></span>
    </div>
</div>

<div class="tbl-mod-1 tbl-metabox"
    v-if="data.sidebar.items.rental && !data.rental.settings.is_l_active">
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <span v-html="data.label.bl_error_message"></span>
    </div>
</div>
<div class="tbl-mod-1 tbl-metabox" v-show="data.sidebar.items.rental && data.rental.settings.is_plugin_activated && data.rental.settings.is_l_active">
	<div class="tm1-row">
		<div class="col-left">
			<label><?php esc_attr_e( 'Book prior days of start date', 'woocommerce-booking' ); ?></label>
		</div>
		<div class="col-right">
			<div class="row-box-1">
				<div class="rb1-left">
					<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>" alt="Tooltip"
						data-toggle="tooltip" data-placement="top"
						title="<?php esc_attr_e( 'Set this field to the number of days to be disabled after the End date after the lockout is reached. For example, if you want 2 days to be disabled after the End date then set this field as 2.', 'woocommerce-booking' ); ?>">
				</div>
				<div class="rb1-right">
					<div class="rb1-row flx-center">
						<div class="rb-col">
							<input class="ib-sm" type="number" min=0 v-model="data.rental.settings.booking_prior_days_to_book">
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

    <div class="tm1-row">
		<div class="col-left">
			<label><?php esc_attr_e( 'Book later days of end date', 'woocommerce-booking' ); ?></label>
		</div>
		<div class="col-right">
			<div class="row-box-1">
				<div class="rb1-left">
					<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>" alt="Tooltip"
						data-toggle="tooltip" data-placement="top"
						title="<?php esc_attr_e( 'Set this field to the number of days to be disabled after the End date after the lockout is reached. For example, if you want 2 days to be disabled after the End date then set this field as 2.', 'woocommerce-booking' ); ?>">
				</div>
				<div class="rb1-right">
					<div class="rb1-row flx-center">
						<div class="rb-col">
							<input class="ib-sm" type="number" min=0 v-model="data.rental.settings.booking_later_days_to_book">
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="tm1-row">
		<div class="col-left">
            <label><?php esc_attr_e( 'Flat charge per day', 'woocommerce-booking' ); // phpcs:ignore ?></label>
		</div>
		<div class="col-right">
			<div class="rc-flx-wrap flx-aln-center">
				<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>" alt="Tooltip"
					data-toggle="tooltip" data-placement="top"
					title="<?php esc_attr_e( 'Set this field if you want to charge for all days including end date.', 'woocommerce-booking' ); ?>">
				<label class="el-switch el-switch-green">
					<input type="checkbox" v-model="data.rental.settings.booking_charge_per_day" true-value="on"
						false-value="">
					<span class="el-switch-style"></span>
				</label>
			</div>
		</div>
	</div>

    <div class="tm1-row">
		<div class="col-left">
            <label><?php esc_attr_e( 'Allow same day booking', 'woocommerce-booking' ); // phpcs:ignore ?></label>
		</div>
		<div class="col-right">
			<div class="rc-flx-wrap flx-aln-center">
				<img class="tt-info" src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>" alt="Tooltip"
					data-toggle="tooltip" data-placement="top"
					title="<?php esc_attr_e( 'Set this field if you want same day booking for each day including end date.', 'woocommerce-booking' ); ?>">
				<label class="el-switch el-switch-green">
					<input type="checkbox" v-model="data.rental.settings.booking_same_day" true-value="on"
						false-value="">
					<span class="el-switch-style"></span>
				</label>
			</div>
		</div>
	</div>

	<div class="rb1-row flx-center mb-3 mt-2" v-if="'undefined' === typeof data.is_bulk_booking">
		<div class="rb-col">
			<a href="javascript:void(0);" class="secondary-btn"
				v-on:click.stop="data.fn.save_settings('rental',data)">{{data.settings.save_settings_button}}</a>
		</div>
	</div>
</div>
