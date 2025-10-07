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
 * @package woocommerce-booking/labels
 */

?>
<template id="onboarding-labels">
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
				<h2><?php esc_attr_e( 'Booking Fields Labels', 'woocommerce-booking' ); // phpcs:ignore  ?></h2>
			</div>
			<div class="wbc-content ex-spc">
				<div class="tbl-mod-1">
					<div class="tm1-row">
						<div class="col-left">
							<label><?php esc_attr_e( 'Check-in Date', 'woocommerce-booking' ); ?></label>
						</div>
						<div class="col-right">
							<div class="row-box-1">
								<div class="rb1-left">
									<img class="tt-info"
										src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
										alt="Tooltip" data-toggle="tooltip" data-placement="top"
										title="<?php esc_attr_e( 'Check-in Date label on product page', 'woocommerce-booking' ); ?>">
								</div>
								<div class="rb1-right">
									<div class="rb1-row flx-center">
										<div class="rb-col">
											<input class="ib-md" type="text"
												v-model="data.settings['book_date_label']">
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="tm1-row">
						<div class="col-left">
							<label><?php esc_attr_e( 'Check-out Date', 'woocommerce-booking' ); ?></label>
						</div>
						<div class="col-right">
							<div class="row-box-1">
								<div class="rb1-left">
									<img class="tt-info"
										src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
										alt="Tooltip" data-toggle="tooltip" data-placement="top"
										title="<?php esc_attr_e( 'Check-out Date label on product page', 'woocommerce-booking' ); ?>">
								</div>
								<div class="rb1-right">
									<div class="rb1-row flx-center">
										<div class="rb-col">
											<input class="ib-md" type="text"
												v-model="data.settings['checkout_date_label']">
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="tm1-row">
						<div class="col-left">
							<label><?php esc_attr_e( 'Booking Time', 'woocommerce-booking' ); ?></label>
						</div>
						<div class="col-right">
							<div class="row-box-1">
								<div class="rb1-left">
									<img class="tt-info"
										src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
										alt="Tooltip" data-toggle="tooltip" data-placement="top"
										title="<?php esc_attr_e( 'Booking Time label on product page', 'woocommerce-booking' ); ?>">
								</div>
								<div class="rb1-right">
									<div class="rb1-row flx-center">
										<div class="rb-col">
											<input class="ib-md" type="text"
												v-model="data.settings['book_time_label']">
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="tm1-row">
						<div class="col-left">
							<label><?php esc_attr_e( 'Text For Add To Cart Button', 'woocommerce-booking' ); ?></label>
						</div>
						<div class="col-right">
							<div class="row-box-1">
								<div class="rb1-left">
									<img class="tt-info"
										src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
										alt="Tooltip" data-toggle="tooltip" data-placement="top"
										title="<?php esc_attr_e( 'Change text for Add to Cart button on WooCommerce product page', 'woocommerce-booking' ); ?>">
								</div>
								<div class="rb1-right">
									<div class="rb1-row flx-center">
										<div class="rb-col">
											<input class="ib-md" type="text"
												v-model="data.settings['bkap_add_to_cart']">
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
			<router-link :to="{name: 'edit-reschedule'}"><span class="link-grey lg-w-arrow" @click="save_labels"><?php esc_html_e( 'Back', 'woocommerce-booking' ); ?></span></router-link>
			<router-link :to="{name: 'data-tracking'}"><span class="link-grey"><?php esc_html_e( 'Skip', 'woocommerce-booking' ); ?></span></router-link>
		</div>
		<div class="col-right">
			<router-link :to="{name: 'data-tracking'}"><span class="button trietary-btn reverse" @click="save_labels"><?php esc_html_e( 'Continue', 'woocommerce-booking' ); ?></span></router-link>
		</div>
	</div>
</div>
</template>
