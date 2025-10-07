<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * Create Booking.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/Views/Booking/CreateBooking
 * @since       5.19.0
 */

?>

<template id="create-booking-tab">
	<section>
		<div class="container bd-page-wrap">
			<div class="row">
				<div class="container-fluid pl-info-wrap"
					v-show="'' !== data.label.create_booking_post_error">
					<div class="row">
						<div class="col-md-12">
							<div class="alert alert-danger alert-dismissible fade show" role="alert">
								<span
									v-html="data.label.create_booking_post_error"></span>
								<button type="button" class="close" data-dismiss="alert" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
						</div>
					</div>
				</div>

				<div class="container-fluid pl-info-wrap"
					v-show="'' !== data.label.create_booking_post_success">
					<div class="row">
						<div class="col-md-12">
							<div class="alert alert-success alert-dismissible fade show" role="alert">
								<span
									v-html="data.label.create_booking_post_success"></span>
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

				<div class="col-md-12">
					<div class="bkap-page-head phw-btn">
						<div class="col-left">
							<h1>{{data.label.create_booking}}</h1>
							<p>{{data.label.create_booking_description}}</p>
						</div>
					</div>
				</div>

				<div class="col-md-12 create-booking-content" v-show="'false' === data.settings.is_create_booking_post_data">
					<div class="wbc-box">
						<div class="wbc-content">
							<div class="tbl-mod-1">
								<div class="tm1-row">
									<div class="col-left">
										<label><?php esc_attr_e( 'Customer', 'woocommerce-booking' ); ?></label>
									</div>
									<div class="col-right">
										<div class="rc-flx-wrap flx-aln-center choices_search_select_box">
											<select class="ib-md create_booking_user_search"
												v-model="data.settings.user_id">
												<option value="" disabled><?php esc_attr_e( 'Select Customer', 'woocommerce-booking' ); ?></option>
											</select>
										</div>
									</div>
								</div>

								<div class="tm1-row">
									<div class="col-left">
										<label><?php esc_attr_e( 'Bookable Product', 'woocommerce-booking' ); ?></label>
									</div>
									<div class="col-right">
										<div class="rc-flx-wrap flx-aln-center choices_search_select_box">
											<select ref="create_booking_product_search" class="ib-md create_booking_product_search"
												v-model="data.settings.product_id">
												<option value="" disabled><?php esc_attr_e( 'Select a bookable product...', 'woocommerce-booking' ); ?></option>
											</select>
										</div>
									</div>
								</div>

								<div class="tm1-row">
									<div class="col-left">
										<label><?php esc_attr_e( 'Create Order', 'woocommerce-booking' ); ?></label>
									</div>
									<div class="col-right">
										<div class="rc-flx-wrap flx-aln-center ro-wrap">
											<div class="rb-flx-style mb-15">
												<div class="el-radio el-radio-green">
													<input type="radio" value="new" id="new"
														v-model="data.settings.order_type">
													<label for="new" class="el-radio-style"></label>
												</div>
												<label><?php esc_attr_e( 'New Order', 'woocommerce-booking' ); ?></label>
											</div>
											<div class="rb-flx-style mb-15">
												<div class="el-radio el-radio-green">
													<input type="radio" value="existing" id="existing"
														v-model="data.settings.order_type">
													<label for="existing" class="el-radio-style"></label>
												</div>
												<label><?php esc_attr_e( 'Existing Order', 'woocommerce-booking' ); ?></label><br>
											</div>
											<div class="rb-flx-style mb-15">
												<div class="el-radio el-radio-green">
													<input type="radio" value="only_booking" id="only_booking"
														v-model="data.settings.order_type">
													<label for="only_booking" class="el-radio-style"></label>
												</div>
												<label><?php esc_attr_e( 'Only booking', 'woocommerce-booking' ); ?></label><br>
											</div>
										</div>

										<div class="rc-flx-wrap flx-aln-center ro-wrap"
											v-show="'new' === data.settings.order_type">
											<p class="instructions">
												<?php esc_attr_e( 'Create a new corresponding order for this new booking. Please note - the booking will not be active until the order is processed/completed.', 'woocommerce-booking' ); ?>
											</p>
										</div>

										<div class="rc-flx-wrap flx-aln-center ro-wrap"
											v-show="'existing' === data.settings.order_type">
											<p class="instructions">
												<?php esc_attr_e( 'Assign this booking to an existing order.', 'woocommerce-booking' ); ?>
											</p>
										</div>

										<div class="rc-flx-wrap flx-aln-center ro-wrap"
											v-show="'only_booking' === data.settings.order_type">
											<p class="instructions">
												<?php esc_attr_e( 'Create booking without any order.', 'woocommerce-booking' ); ?>
											</p>
										</div>
									</div>
								</div>

								<div class="tm1-row" v-show="'existing' === data.settings.order_type">
									<div class="col-left">
										<label><?php esc_attr_e( 'Exisiting Order ID', 'woocommerce-booking' ); ?></label>
									</div>
									<div class="col-right">
										<div class="row-box-1">
											<div class="rb1-left">
												<img class="tt-info"
													src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
													alt="Tooltip" data-toggle="tooltip" data-placement="top"
													title="">
											</div>
											<div class="rb1-right">
												<div class="rb1-row flx-center">
													<div class="rb-col">
														<input class="ib-lg" type="text"
															v-on:keyup="data.fn.only_numbers"
															v-model="data.settings.existing_order_id">
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>

								<div class="tm1-row">
									<div class="col-left">
										<label><?php esc_html_e( 'Show All Dates/Times (Ignore Availability)', 'woocommerce-booking' ); ?></label>
									</div>
									<div class="col-right">
										<div class="row-box-1">
											<div class="rb1-left">
												<img class="tt-info"
													src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
													alt="Tooltip" data-toggle="tooltip" data-placement="top" title="">
											</div>
											<div class="rb1-right">
												<div class="rb1-row flx-center">
													<div class="rb-col">
														<label class="el-switch el-switch-green">
															<input type="checkbox"
																v-model="data.settings.show_disabled_dates"
																true-value="on" false-value="">
															<span class="el-switch-style"></span>
														</label>
														<div class="rc-flx-wrap flx-aln-center ro-wrap">
															<p class="instructions">
																<?php esc_attr_e( "Lets you select any date or time, even if it's normally unavailable. For manual bookings only.", 'woocommerce-booking' ); ?>
															</p>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>

								<div class="tm1-row bdr-0 pt-0">
									<div class="abulk-box pt-0 ">
										<button class="trietary-btn reverse" type="button"
											v-on:click.stop="prepare_post_data"><?php esc_attr_e( 'Next', 'woocommerce-booking' ); ?></button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<?php
				if ( ! isset( $_POST['create_booking_post_error'] ) && isset( $_POST['create_booking_post_data'] ) && is_array( $_POST['create_booking_post_data'] ) && count( $_POST['create_booking_post_data'] ) > 0 ) { // phpcs:ignore
					$create_booking_post_data = $_POST['create_booking_post_data']; // phpcs:ignore
					$product_id               = BKAP_Admin_API::check( $create_booking_post_data, 'product_id', '' );
					$user_id                  = BKAP_Admin_API::check( $create_booking_post_data, 'user_id', 0 );
					$user_id                  = 'undefined' === $user_id ? 0 : $user_id;
					$order_type               = BKAP_Admin_API::check( $create_booking_post_data, 'order_type', '' );
					$existing_order_id        = BKAP_Admin_API::check( $create_booking_post_data, 'existing_order_id', '' );
					$show_disabled_dates      = BKAP_Admin_API::check( $create_booking_post_data, 'show_disabled_dates', '' );
					$_product                 = wc_get_product( $product_id );
					if ( ! $_product ) {
						$_product = new WC_Product_Variation( $product_id );
					}
					$variation_id             = 0;
					$parent_id                = version_compare( WOOCOMMERCE_VERSION, '3.0.0' ) < 0 ? $_product->parent->id : $_product->get_parent_id();
					$duplicate_id             = $parent_id > 0 ? $parent_id : $product_id;
					$duplicate_id             = bkap_common::bkap_get_product_id( $duplicate_id );
					?>

				<form method="POST">
					<table id="bkap-manual-date-selection">
						<tbody>
							<tr valign="top">
								<td>
									<?php bkap_include_booking_form( $duplicate_id, $_product ); ?>
								</td>
							</tr>

							<tr valign="top">
								<td>
								<?php do_action( 'bkap_after_booking_form_on_create_booking', $duplicate_id, $_product ); ?>
								</td>
							</tr>
							
							<tr valign="top">
								<td>
									<div class="quantity">
										<input type="number" id="manual-booking-qty" class="input-text qty text"
											step="1" min="1" max="" name="quantity" value="1" title="Qty" size="4"
											inputmode="numeric" style="display: inline-block;">
										<input type="submit" name="bkap_create_booking_2"
											class="bkap_create_booking button-primary"
											value="<?php esc_html_e( 'Create Booking', 'woocommerce-booking' ); ?>"
											disabled="disabled" />
									</div>

									<input type="hidden" name="bkap_customer_id"
										value="<?php echo esc_attr( $user_id ); ?>" />
									<input type="hidden" name="bkap_product_id"
										value="<?php echo esc_attr( $product_id ); ?>" />
									<input type="hidden" name="bkap_order"
										value="<?php echo esc_attr( $order_type ); ?>" />
									<input type="hidden" name="bkap_order_id"
										value="<?php echo esc_attr( $existing_order_id ); ?>" />
									<input type="hidden" name="bkap_show_disabled_dates" id="bkap_show_disabled_dates"
										value="<?php echo esc_attr( $show_disabled_dates ); ?>" />

									<?php if ( $parent_id > 0 ) { ?>
									<input type="hidden" class="variation_id"
										value="<?php echo esc_attr( $product_id ); ?>" />
										<?php
											$variation_class = new WC_Product_Variation( $product_id );
											$get_attributes  = $variation_class->get_variation_attributes();

										if ( is_array( $get_attributes ) && count( $get_attributes ) > 0 ) {
											foreach ( $get_attributes as $attr_name => $attr_value ) {
												$attr_value = htmlspecialchars( $attr_value, ENT_QUOTES );
												print( "<input type='hidden' name='$attr_name' value='$attr_value' />" ); // phpcs:ignore
											}
										}
									}

									wp_nonce_field( 'bkap_create_booking', 'bkap_create_booking_nonce' );
									?>
								</td>
							</tr>
						</tbody>
					</table>
				</form>
					<?php
				}
				?>
			</div>
		</div>
	</section>
</template>
