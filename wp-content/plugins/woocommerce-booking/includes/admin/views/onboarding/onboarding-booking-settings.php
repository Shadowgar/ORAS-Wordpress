<?php
/**
 * Onboarding Template Part 1
 *
 * @package woocommerce-booking/onboarding
 */

?>
<template id="onboarding-booking-settings">
<div>
	<div class="obp-content">
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

		<div class="bkap_admin_loader" v-show="show_loading_loader">
			<div class="bkap_admin_loader_wrapper">
				{{data.label.loading_loader}} <img
					src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
			</div>
		</div>

		<div class="wbc-box">
			<div class="wbc-head">
				<h2><?php esc_attr_e( 'Booking Type & Other options', 'woocommerce-booking' ); // phpcs:ignore  ?></h2>
			</div>
			<div class="wbc-content ex-spc">
				<div class="tbl-mod-1">
					<div class="tm1-row">
						<div class="col-left">
							<label><?php esc_attr_e( 'Booking Type', 'woocommerce-booking' ); ?></label>
						</div>
						<div class="col-right">
							<div class="rc-flx-wrap flx-aln-center">
								<img class="tt-info"
									src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
									alt="Tooltip" data-toggle="tooltip" data-placement="top"
									title="<?php esc_attr_e( 'The Booking Type which you want to set for the products on your store.', 'woocommerce-booking' ); ?>">
								<select class="ib-md"
									v-model="data.settings.bkap_booking_type">
									<option v-for="(value, key) in data.config.bkap_booking_types"
										v-bind:value="key">{{value.label}}</option>
								</select>
							</div>
						</div>
					</div>

					<div class="tm1-row">
						<div class="col-left">
							<label><?php esc_attr_e( 'Bookable Weekdays', 'woocommerce-booking' ); ?></label>
						</div>
						<div class="col-right">
							<div class="">
								<img class="tt-info"
									src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
									alt="Tooltip" data-toggle="tooltip" data-placement="top"
									title="<?php esc_attr_e( 'The format in which the booking date appears to the customers throughout the order cycle', 'woocommerce-booking' ); ?>">
									<div v-for="(value, key)  in data.config.bkap_weekdays">
										<div>
											<label class="el-switch el-switch-green">
												<input type="checkbox" v-model="data.settings.bkap_weekdays[key]" true-value="on" false-value="" checked>
												<span class="el-switch-style"></span>
											</label>
											{{data.config.bkap_weekdays[key]}}
										</div>
									</div>
							</div>
						</div>
					</div>

					<div class="tm1-row">
						<div class="col-left">
							<label><?php esc_attr_e( 'Inline Calendar', 'woocommerce-booking' ); ?></label>
						</div>
						<div class="col-right">
							<div class="rc-flx-wrap flx-aln-center">
								<img class="tt-info"
									src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
									alt="Tooltip" data-toggle="tooltip" data-placement="top"
									title="<?php esc_attr_e( 'The format in which the booking date appears to the customers throughout the order cycle', 'woocommerce-booking' ); ?>">
									<label class="el-switch el-switch-green">
										<input type="checkbox"
											v-model="data.settings.bkap_inline"
											true-value="on" false-value="">
										<span class="el-switch-style"></span>
									</label>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="obp-footer">
		<div class="col-left">
			<router-link :to="{name: 'appearance-settings'}"><span class="link-grey lg-w-arrow" @click="save_booking_settings"><?php esc_html_e( 'Back', 'woocommerce-booking' ); ?></span></router-link>
			<router-link :to="{name: 'edit-reschedule'}"><span class="link-grey"><?php esc_html_e( 'Skip', 'woocommerce-booking' ); ?></span></router-link>
		</div>
		<div class="col-right">
			<router-link :to="{name: 'edit-reschedule'}"><span class="button trietary-btn reverse" @click="save_booking_settings"><?php esc_html_e( 'Continue', 'woocommerce-booking' ); ?></span></router-link>
		</div>
	</div>
</div>
</template>
