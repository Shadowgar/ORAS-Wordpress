<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * Welcome Tab.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/Views/Home/Welcome
 * @since       5.19.0
 */

?>

<?php
/**
 * Onboarding Template Part 1
 *
 * @package woocommerce-booking/onboarding
 */

?>
<template id="onboarding-edit-reschedule">
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
				<h2><?php esc_attr_e( 'Allow Rescheduling and Cancelling of Bookings', 'woocommerce-booking' ); // phpcs:ignore  ?></h2>
			</div>
			<div class="wbc-content ex-spc">
				<div class="tbl-mod-1">
					<div class="tm1-row">
						<div class="col-left">
							<label><?php esc_attr_e( 'Allow Bookings to be reschedulable', 'woocommerce-booking' ); ?></label>
						</div>
						<div class="col-right">
							<div class="rc-flx-wrap flx-aln-center">
								<img class="tt-info"
									src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
									alt="Tooltip" data-toggle="tooltip" data-placement="top"
									title="<?php esc_attr_e( 'Enabling this option will allow Bookings to be reschedulable from My Account page', 'woocommerce-booking' ); ?>">
								<label class="el-switch el-switch-green">
									<input type="checkbox"
										v-model="data.settings.bkap_enable_booking_reschedule"
										true-value="on" false-value="">
									<span class="el-switch-style"></span>
								</label>
							</div>
						</div>
					</div>

					<div class="tm1-row"
						v-show="'on' === data.settings.bkap_enable_booking_reschedule">
						<div class="col-left">
							<label><?php esc_attr_e( 'Minimum number of hours for rescheduling', 'woocommerce-booking' ); ?></label>
						</div>
						<div class="col-right">
							<div class="row-box-1">
								<div class="rb1-left">
									<img class="tt-info"
										src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
										alt="Tooltip" data-toggle="tooltip" data-placement="top"
										title="<?php esc_attr_e( 'Minimum number of hours before the booking date, after which Booking cannot be rescheduled. (24 hours = 1 day)', 'woocommerce-booking' ); ?>">
								</div>
								<div class="rb1-right">
									<div class="rb1-row flx-center">
										<div class="rb-col">
											<input class="ib-sm" type="number" min=0
												v-model="data.settings.bkap_booking_reschedule_hours">
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="tm1-row">
						<div class="col-left">
							<label><?php esc_attr_e( 'Allow Bookings to be canceled', 'woocommerce-booking' ); ?></label>
						</div>
						<div class="col-right">
							<div class="rc-flx-wrap flx-aln-center">
								<img class="tt-info"
									src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
									alt="Tooltip" data-toggle="tooltip" data-placement="top"
									title="<?php esc_attr_e( 'Enabling this option will allows the customer to cancel their bookings before perticular time period.', 'woocommerce-booking' ); ?>">
								<label class="el-switch el-switch-green">
									<input type="checkbox"
										v-model="data.settings.bkap_enable_booking_cancel"
										true-value="on" false-value="">
									<span class="el-switch-style"></span>
								</label>
							</div>
						</div>
					</div>

					<div class="tm1-row"
						v-show="'on' === data.settings.bkap_enable_booking_cancel">
						<div class="col-left">
							<label><?php esc_attr_e( 'Minimum number of hours for cancelling booking', 'woocommerce-booking' ); ?></label>
						</div>
						<div class="col-right">
							<div class="row-box-1">
								<div class="rb1-left">
									<img class="tt-info"
										src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
										alt="Tooltip" data-toggle="tooltip" data-placement="top"
										title="<?php esc_attr_e( 'Minimum number of hours before the booking date, after which Booking cannot be cancelled.', 'woocommerce-booking' ); ?>">
								</div>
								<div class="rb1-right">
									<div class="rb1-row flx-center">
										<div class="rb-col">
											<input class="ib-sm" type="number" min=0
												v-model="data.settings.bkap_booking_minimum_hours_cancel">
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

	<div class="obp-footer">
		<div class="col-left">
			<router-link :to="{name: 'booking-settings'}"><span class="link-grey lg-w-arrow" @click="save_edit_reschedule"><?php esc_html_e( 'Back', 'woocommerce-booking' ); ?></span></router-link>
			<router-link :to="{name: 'labels'}"><span class="link-grey"><?php esc_html_e( 'Skip', 'woocommerce-booking' ); ?></span></router-link>
		</div>
		<div class="col-right">
			<router-link :to="{name: 'labels'}"><span class="button trietary-btn reverse" @click="save_edit_reschedule"><?php esc_html_e( 'Continue', 'woocommerce-booking' ); ?></span></router-link>
		</div>
	</div>
</div>
</template>
