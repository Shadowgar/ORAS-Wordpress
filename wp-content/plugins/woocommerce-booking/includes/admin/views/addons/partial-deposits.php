<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * Partial Deposits.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/Views/Addons/PartialDeposits
 * @since       5.19.0
 */

?>

<template id="partial-deposits-tab">
	<section>
		<div class="container bd-page-wrap">
			<div class="row">
				<div class="container-fluid pl-info-wrap" id="bkap_admin_view_message" v-show="show_saved_message">
					<div class="row">
						<div class="col-md-12">
							<div class="alert alert-success alert-dismissible fade show" role="alert">
								<?php esc_attr_e( 'Settings have been saved.', 'woocommerce-booking' ); ?>
								<button type="button" class="close" data-dismiss="alert" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
						</div>
					</div>
				</div>

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

				<div class="bkap_admin_loader" v-show="show_saving_loader">
					<div class="bkap_admin_loader_wrapper">
						{{data.label.saving_loader}} <img
							src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
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
                            <h1><?php esc_attr_e( 'Partial Deposits', 'woocommerce-booking' ); // phpcs:ignore ?></h1>
							</p>
						</div>

						<div class="col-right">
							<button type="button" class="bkap-button" v-on:click.stop="save_settings">{{data.label.save_settings}}</button>
						</div>
					</div>
				</div>

				<div class="col-md-12">
					<div class="wbc-accordion">
						<div class="panel-group bkap-accordian" id="wbc-accordion">
							<div class="panel panel-default">
								<div class="panel-heading">
									<h2 class="panel-title" data-toggle="collapse" data-target="#collapseOne"
										aria-expanded="false">
                                        <?php esc_attr_e( 'Settings', 'woocommerce-booking' ); // phpcs:ignore  ?>
									</h2>
								</div>
								<div id="collapseOne" class="panel-collapse collapse show">
									<div class="panel-body">
										<div class="tbl-mod-1">
											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Partial Payment Disabled Message', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="row-box-1">
														<div class="rb1-left">
															<img class="tt-info"
																src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
																alt="Tooltip" data-toggle="tooltip" data-placement="top"
																title="<?php esc_attr_e( 'Message to show on product page when partial payment is disabled (if the booking date is within the minimum days required period).', 'woocommerce-booking' ); ?>">
														</div>
														<div class="rb1-right">
															<div class="rb1-row flx-center">
																<div class="rb-col">
																	<input class="ib-md" type="text"
																		v-model="data.settings.bkap_partial_payment_disabled_msg">
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Deposit Amount Label', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="row-box-1">
														<div class="rb1-left">
															<img class="tt-info"
																src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
																alt="Tooltip" data-toggle="tooltip" data-placement="top"
																title="<?php esc_attr_e( 'Label to be used for Deposit Amount on the Product, Cart, Checkout pages and emails', 'woocommerce-booking' ); ?>">
														</div>
														<div class="rb1-right">
															<div class="rb1-row flx-center">
																<div class="rb-col">
																	<input class="ib-md" type="text"
																		v-model="data.settings.bkap_deposit_amt_label">
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Remaining Amount Label', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="row-box-1">
														<div class="rb1-left">
															<img class="tt-info"
																src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
																alt="Tooltip" data-toggle="tooltip" data-placement="top"
																title="<?php esc_attr_e( 'Label to be used for the Remaining Amount on the Product, Cart, Checkout pages and emails', 'woocommerce-booking' ); ?>">
														</div>
														<div class="rb1-right">
															<div class="rb1-row flx-center">
																<div class="rb-col">
																	<input class="ib-md" type="text"
																		v-model="data.settings.bkap_remaining_amt_label">
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Total Amount Label', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="row-box-1">
														<div class="rb1-left">
															<img class="tt-info"
																src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
																alt="Tooltip" data-toggle="tooltip" data-placement="top"
																title="<?php esc_attr_e( 'Label to be used for the Total Amount on the Product, Cart, Checkout pages and emails', 'woocommerce-booking' ); ?>">
														</div>
														<div class="rb1-right">
															<div class="rb1-row flx-center">
																<div class="rb-col">
																	<input class="ib-md" type="text"
																		v-model="data.settings.bkap_total_amt_label">
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Product Amount Label', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="row-box-1">
														<div class="rb1-left">
															<img class="tt-info"
																src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
																alt="Tooltip" data-toggle="tooltip" data-placement="top"
																title="<?php esc_attr_e( 'Label to be used for the Product Amount (in case of Security Deposits) on the Product, Cart, Checkout pages and emails', 'woocommerce-booking' ); ?>">
														</div>
														<div class="rb1-right">
															<div class="rb1-row flx-center">
																<div class="rb-col">
																	<input class="ib-md" type="text"
																		v-model="data.settings.bkap_product_amt_label">
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Add Payment Type and Original Order columns on WooCommerce -> Orders page', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'Enable to add Payment Type and Original Order columns on WooCommerce -> Orders page.<br><i>1. Payment Type will have the labels as "Deposit" or "Remaining Balance". "Deposit label" is for the original order created by Partial deposits and "Remaining Balance" is for the new order created for the remaining balance payment.<br>2. Original Order column will have the base Order Id for the remaining balance payment orders.</i>', 'woocommerce-booking' ); ?>">
														<label class="el-switch el-switch-green">
															<input type="checkbox"
																v-model="data.settings.bkap_deposit_payment_woocommerc_order_fields"
																true-value="on" false-value="">
															<span class="el-switch-style"></span>
														</label>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Add Payment Status and Remaining Balance Order columns on Booking -> View Bookings page', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'Enable to add Payment Status and Remaining Balance Order columns on Booking -> View Bookings page.<br><i>1. Payment Status will have the labels as "Totally Paid" and "Awaiting Balance Payment" and "Partially Paid". "Totally Paid" label is displayed when the Order is paid completely including the remaining balance. "Partially Paid" is displayed when the Order is placed but the invoice is not created for the remaining balance. "Awaiting Balance Payment" is displayed when the invoice is created for the remaining balance but the payment is not made.<br>2. Remaining Balance Order column will have the Order Id for the remaining balance payment orders.</i>', 'woocommerce-booking' ); ?>">
														<label class="el-switch el-switch-green">
															<input type="checkbox"
																v-model="data.settings.bkap_deposit_payment_view_bookings_fields"
																true-value="on" false-value="">
															<span class="el-switch-style"></span>
														</label>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Generate invoice for all the order items to collect remaning amount', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'Enabling this setting you will be able to generate a single invoice for all the order items\' remaining amount', 'woocommerce-booking' ); ?>">
														<label class="el-switch el-switch-green">
															<input type="checkbox"
																v-model="data.settings.bkap_deposit_payment_one_order"
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
				<div class="col-md-12">
					<div class="bkap-page-head phw-btn">
						<div class="col-left">
						</div>

						<div class="col-right">
							<button type="button" class="bkap-button" v-on:click.stop="save_settings">{{data.label.save_settings}}</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
</template>
