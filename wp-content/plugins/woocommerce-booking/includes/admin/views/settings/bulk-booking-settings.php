<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * Bulk Booking Settings.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/Views/Settings/Bulk_Booking_Settings
 * @since       5.19.0
 */

?>
<template id="bulk-booking-settings-tab">
	<section>
		<div class="container bd-page-wrap">
			<div class="row">
				<div class="bkap_admin_loader" v-show="data.show_clear_settings_loader">
					<div class="bkap_admin_loader_wrapper">
						{{data.label.text_clear_settings_loader}} <img
							src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
					</div>
				</div>

				<div class="bkap_admin_loader" v-show="data.loader.loader_saving_bulk_booking_settings">
					<div class="bkap_admin_loader_wrapper">
						{{data.label.text_saving_bulk_booking_settings}} <img
							src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
					</div>
				</div>

				<div class="col-md-12">
					<div class="bkap-page-head phw-btn">
						<div class="col-left">
                            <h1><?php esc_attr_e( 'Bulk Booking Settings', 'woocommerce-booking' ); // phpcs:ignore ?>
							</h1>
                            <p><?php esc_attr_e( 'Add booking settings for multiple products. To avoid excess system resouce comsumption, Bulk Booking Settings will be processed in the background for product count greater than 20.', 'woocommerce-booking' ); // phpcs:ignore ?>
							</p>
						</div>

						<div class="col-right"></div>
					</div>
				</div>

				<div class="col-md-12">
					<div class="wbc-accordion">
						<div class="panel-group bkap-accordian" id="wbc-accordion">
							<div class="panel panel-default">
								<div id="collapseOne" class="panel-collapse collapse show">
									<div class="panel-body">
										<div class="tbl-mod-1">
											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Select Products and/or Categories', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<img class="tt-info"
														src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
														alt="Tooltip" data-toggle="tooltip" data-placement="top"
														title="<?php esc_attr_e( 'Select the product for which you want to add the booking settings. you can also select a product category to add booking settings to all the products in the selected category.', 'woocommerce-booking' ); ?>">
													
														<select
															class="ib-md bulk_booking_settings_selected_products_categories"
															v-model="data.product_id" multiple>
															<optgroup
																v-for="(item,key) in data.select_product_category_data"
																v-bind:label="key">
																<option v-for="(_item,_key) in item"
																	v-bind:value="_key">{{_item}}</option>
															</optgroup>
														</select>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="col-md-12" v-show="0 !== data.product_id.length">
					<?php BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/views/metabox/booking/index.php' ); ?>
				</div>

				<div class="col-md-12" v-show="0 !== data.product_id.length">
					<div class="wbc-accordion">
						<div class="panel-group" id="wbc-accordion">
							<div class="panel panel-default">
								<div id="collapseBookingMetabox" class="panel-collapse collapse show">
									<div class="panel-body">
										<div class="tbl-mod-1">
											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Save options as default settings', 'woocommerce-booking' ); ?></label>
													<div class="rb1-row flx-center mb-3 mt-2">
														<a href="javascript:void(0);" class="secondary-btn"
															v-show="data.is_exist_default_booking_settings"
															v-on:click.stop="data.fn.clear_default_settings('general',data)">{{data.label.btn_clear_defaults}}</a>
													</div>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'Enable this to save the selected options as default options.', 'woocommerce-booking' ); ?>">
														<label class="el-switch el-switch-green">
															<input type="checkbox"
																v-model="data.save_selected_options_as_default_options"
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
						</div>
					</div>
				</div>

				<div class="col-md-12" v-show="0 !== data.product_id.length">
					<div class="bkap-page-head phw-btn">
						<div class="col-left">
						</div>

						<div class="col-right">
							<button type="button" class="bkap-button"
								v-on:click.stop="data.fn.save_settings('save_all',data)">{{data.label.save_settings}}</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
</template>
