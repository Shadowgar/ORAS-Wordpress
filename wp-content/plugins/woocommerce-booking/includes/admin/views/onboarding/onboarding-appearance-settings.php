<?php
/**
 * Onboarding Template - Appearance
 *
 * @package woocommerce-booking/onboarding
 */

?>
<template id="onboarding-appearance-settings" ref="appearance-settings">
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
							<label><?php esc_attr_e( 'Date Format', 'woocommerce-booking' ); ?></label>
						</div>
						<div class="col-right">
							<div class="rc-flx-wrap flx-aln-center">
								<img class="tt-info"
									src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
									alt="Tooltip" data-toggle="tooltip" data-placement="top"
									title="<?php esc_attr_e( 'The format in which the booking date appears to the customers throughout the order cycle', 'woocommerce-booking' ); ?>">
								<select class="ib-md"
									v-model="data.settings.booking_date_format">
									<option v-for="(value, key) in data.config.date_formats"
										v-bind:value="key">{{value}}</option>
								</select>
							</div>
						</div>
					</div>

					<div class="tm1-row">
						<div class="col-left">
							<label><?php esc_attr_e( 'Time Format', 'woocommerce-booking' ); ?></label>
						</div>
						<div class="col-right">
							<div class="rc-flx-wrap flx-aln-center">
								<img class="tt-info"
									src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
									alt="Tooltip" data-toggle="tooltip" data-placement="top"
									title="<?php esc_attr_e( 'The format in which booking time appears to the customers throughout the order cycle', 'woocommerce-booking' ); ?>">
								<select class="ib-md"
									v-model="data.settings.booking_time_format">
									<option v-for="(value, key) in data.config.time_formats"
										v-bind:value="key">{{value}}</option>
								</select>
							</div>
						</div>
					</div>

					<div class="tm1-row">
						<div class="col-left">
							<label><?php esc_attr_e( 'First Day on Calendar', 'woocommerce-booking' ); ?></label>
						</div>
						<div class="col-right">
							<div class="rc-flx-wrap flx-aln-center">
								<img class="tt-info"
									src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
									alt="Tooltip" data-toggle="tooltip" data-placement="top"
									title="<?php esc_attr_e( 'Choose the first day to display on the booking calendar', 'woocommerce-booking' ); ?>">
								<select class="ib-md"
									v-model="data.settings.booking_calendar_day">
									<option v-for="(value, key) in data.config.bkap_days"
										v-bind:value="key">{{value}}</option>
								</select>
							</div>
						</div>
					</div>

					<div class="tm1-row">
						<div class="col-left">
							<label><?php esc_attr_e( 'Calendar Theme', 'woocommerce-booking' ); ?></label>
						</div>

						<div class="col-right">
							<div class="row-box-1">
								<div class="rb1-left">
									<img class="tt-info"
										src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
										alt="Tooltip" data-toggle="tooltip" data-placement="top"
										title="<?php esc_attr_e( 'Select the theme for the calendar. You can choose a theme which blends with the design of your website', 'woocommerce-booking' ); ?>">
								</div>
								<div class="rb1-right">
									<div class="rb1-row">
										<select class="ib-md"
											v-model="data.settings.booking_themes"
											@change="set_calendar_theme">
											<option
												v-for="(value, key) in data.config.bkap_calendar_themes"
												v-bind:value="key">{{value}}</option>
										</select>
									</div>
									<div class="rb1-row adj-img cal-theme-image">
										<datepicker
											:start="data.settings.booking_calendar_day">
										</datepicker>
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
			<router-link :to="{name: 'welcome'}"><span class="link-grey lg-w-arrow" @click="save_appearance_settings"><?php esc_html_e( 'Back', 'woocommerce-booking' ); ?></span></router-link>
			<router-link :to="{name: 'booking-settings'}"><span class="link-grey"><?php esc_html_e( 'Skip', 'woocommerce-booking' ); ?></span></router-link>
		</div>
		<div class="col-right">
			<router-link :to="{name: 'booking-settings'}"><span class="button trietary-btn reverse" @click="save_appearance_settings"><?php esc_html_e( 'Continue', 'woocommerce-booking' ); ?></span></router-link>
		</div>
	</div>
</div>
</template>
