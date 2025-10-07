<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * Calendar.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/Views/Calendar/Calendar
 * @since       5.19.0
 */

?>

<template id="booking-calendar-tab">
	<section>
		<div class="container-booking-calendar pl-page-wrap">
			<div class="row mb-15 mt-20">
				<div class="col-md-12">
					<div class="wbc-box">
						<div class="wbc-content">
							<div class="tm1-row mb-15">
								<div class="col-left">
									<label><?php esc_attr_e( 'Filter Booking By Products:', 'woocommerce-booking' ); ?></label>
								</div>
								<div class="col-right">
									<div class="rc-flx-wrap choices_search_select_box">
										<select multiple class="ib-md booking_calendar_product_search"
											v-model="data.settings.product_id">
										</select>
									</div>
								</div>
							</div>
							<div class="tm1-row bdr-0 pt-0">
								<div class="abulk-box pt-0 ">
									<button class="trietary-btn" type="button"
										v-on:click.stop="filter_calendar_data_by_product"><?php esc_attr_e( 'Filter', 'woocommerce-booking' ); ?></button>
								</div>
							</div>
						</div>
					</div>
					
					<div id="bkap_filter_products" class="bkap_filter_products hide">
						<div class="bkap_filter_products_wrapper">
							{{data.label.loading_calendar_events}}
							<img src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
						</div>
					</div>
					
					<div id="bkap_events_loader" class="bkap_admin_loader hide">
						<div class="bkap_admin_loader_wrapper">
							{{data.label.loading_calendar_events}}
							<img src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
						</div>
					</div>
					<div id="bkap-calendar"></div>
				</div>
			</div>
		</div>
	</section>
</template>
