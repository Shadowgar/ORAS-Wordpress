<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * Label & Messages.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/Views/appearance/Label
 * @since       5.19.0
 */

?>
<template id="label-tab">
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
                            <h1><?php esc_attr_e( 'Label & Messages Settings', 'woocommerce-booking' ); // phpcs:ignore ?></h1>
                            <p><?php esc_attr_e( 'Personalize booking labels for enhanced user engagement and clarity.', 'woocommerce-booking' ); // phpcs:ignore ?>
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
                                        <?php esc_attr_e( 'Product Page Labels', 'woocommerce-booking' ); // phpcs:ignore  ?>
									</h2>
								</div>
								<div id="collapseOne" class="panel-collapse collapse show">
									<div class="panel-body">
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
																title="<?php esc_attr_e( 'Check-in date label on product page', 'woocommerce-booking' ); ?>">
														</div>
														<div class="rb1-right">
															<div class="rb1-row flx-center">
																<div class="rb-col">
																	<input class="ib-md" type="text"
																		v-model="data.settings['book_date-label']">
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
																title="<?php esc_attr_e( 'Check-out date label on product page', 'woocommerce-booking' ); ?>">
														</div>
														<div class="rb1-right">
															<div class="rb1-row flx-center">
																<div class="rb-col">
																	<input class="ib-md" type="text"
																		v-model="data.settings['checkout_date-label']">
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
																title="<?php esc_attr_e( 'Booking time label on product page', 'woocommerce-booking' ); ?>">
														</div>
														<div class="rb1-right">
															<div class="rb1-row flx-center">
																<div class="rb-col">
																	<input class="ib-md" type="text"
																		v-model="data.settings['book_time-label']">
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Choose Time Text', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="row-box-1">
														<div class="rb1-left">
															<img class="tt-info"
																src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
																alt="Tooltip" data-toggle="tooltip" data-placement="top"
																title="<?php esc_attr_e( 'Text for the 1st option of time slot dropdown field that instructs the customer to select a time slot', 'woocommerce-booking' ); ?>">
														</div>
														<div class="rb1-right">
															<div class="rb1-row flx-center">
																<div class="rb-col">
																	<input class="ib-md" type="text"
																		v-model="data.settings['book_time-select-option']">
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Fixed Block Drop Down', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="row-box-1">
														<div class="rb1-left">
															<img class="tt-info"
																src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
																alt="Tooltip" data-toggle="tooltip" data-placement="top"
																title="<?php esc_attr_e( 'Fixed block drop down label on product page', 'woocommerce-booking' ); ?>">
														</div>
														<div class="rb1-right">
															<div class="rb1-row flx-center">
																<div class="rb-col">
																	<input class="ib-md" type="text"
																		v-model="data.settings['book_fixed-block-label']">
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Booking Price', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="row-box-1">
														<div class="rb1-left">
															<img class="tt-info"
																src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
																alt="Tooltip" data-toggle="tooltip" data-placement="top"
																title="<?php esc_attr_e( 'Label for booking price on product page', 'woocommerce-booking' ); ?>">
														</div>
														<div class="rb1-right">
															<div class="rb1-row flx-center">
																<div class="rb-col">
																	<input class="ib-md" type="text"
																		v-model="data.settings['book_price-label']">
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Select Calendar Icon', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="row-box-1">
														<div class="rb1-left">
															<img class="tt-info"
																src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
																alt="Tooltip" data-toggle="tooltip" data-placement="top"
																title="<?php esc_attr_e( 'Calendar icon for booking fields on product page', 'woocommerce-booking' ); ?>">
														</div>
														<div class="rb1-right">
															<div class="rb1-row flx-center">
																<div class="rb-col">
																	<img v-on:click="bkap_calendar_icon_file('calendar1.gif')"
																	:class="data.settings['bkap_calendar_icon_file'] == 'calendar1.gif' ? 'bkap_calendar_icon_file' : 'bkap_calender_no_icon_file'"src="<?php echo esc_url( BKAP_IMAGE_URL . 'calendar1.gif' ); ?>" width="40" :title="data.settings['bkap_calendar_icon_file'] == 'calendar1.gif' ? '' : 'Click to open calendar'">
																	<a href="javascript:void(0);" :class="data.settings['bkap_calendar_icon_file'] == 'none' ? 'bkap_calendar_icon_file' : ''" style="margin-left: 8px;padding:8px;" v-on:click.stop="bkap_calendar_icon_file('none')" :title="data.settings['bkap_calendar_icon_file'] == 'none' ? '' : 'Disable calendar icon'"><?php esc_attr_e( 'No Icon', 'woocommerce-booking' ); ?></a>
																	<!-- <input type="radio" v-model="data.settings['bkap_calendar_icon_file']" style="display:none;"> -->
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

							<div class="panel panel-default">
								<div class="panel-heading">
									<h2 class="panel-title" data-toggle="collapse" data-target="#collapseTwo"
										aria-expanded="false">
                                        <?php esc_attr_e( 'Order Received Page & Email Notifications', 'woocommerce-booking' ); // phpcs:ignore ?>
									</h2>
								</div>
								<div id="collapseTwo" class="panel-collapse collapse show">
									<div class="panel-body">
										<div class="tbl-mod-1">

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Check-In Date', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="row-box-1">
														<div class="rb1-left">
															<img class="tt-info"
																src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
																alt="Tooltip" data-toggle="tooltip" data-placement="top"
																title="<?php esc_attr_e( 'Check-in date label on the order received page and email notification.', 'woocommerce-booking' ); ?>">
														</div>
														<div class="rb1-right">
															<div class="rb1-row flx-center">
																<div class="rb-col">
																	<input class="ib-md" type="text"
																		v-model="data.settings['book_item-meta-date']">
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Check-Out Date', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="row-box-1">
														<div class="rb1-left">
															<img class="tt-info"
																src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
																alt="Tooltip" data-toggle="tooltip" data-placement="top"
																title="<?php esc_attr_e( 'Check-out date label on the order received page and email notification', 'woocommerce-booking' ); ?>">
														</div>
														<div class="rb1-right">
															<div class="rb1-row flx-center">
																<div class="rb-col">
																	<input class="ib-md" type="text"
																		v-model="data.settings['checkout_item-meta-date']">
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
																title="<?php esc_attr_e( 'Booking time label on the order received page and email notification', 'woocommerce-booking' ); ?>">
														</div>
														<div class="rb1-right">
															<div class="rb1-row flx-center">
																<div class="rb-col">
																	<input class="ib-md" type="text"
																		v-model="data.settings['book_item-meta-time']">
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'ICS File Name', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="row-box-1">
														<div class="rb1-left">
															<img class="tt-info"
																src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
																alt="Tooltip" data-toggle="tooltip" data-placement="top"
																title="<?php esc_attr_e( 'ICS file name', 'woocommerce-booking' ); ?>">
														</div>
														<div class="rb1-right">
															<div class="rb1-row flx-center">
																<div class="rb-col">
																	<input class="ib-md" type="text"
																		v-model="data.settings['book_ics-file-name']">
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

							<div class="panel panel-default">
								<div class="panel-heading">
									<h2 class="panel-title" data-toggle="collapse" data-target="#collapseThree"
										aria-expanded="false">
                                        <?php esc_attr_e( ' Cart & Checkout Page', 'woocommerce-booking' ); // phpcs:ignore ?>
									</h2>
								</div>
								<div id="collapseThree" class="panel-collapse collapse show">
									<div class="panel-body">
										<div class="tbl-mod-1">
											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Check-In Date', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="row-box-1">
														<div class="rb1-left">
															<img class="tt-info"
																src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
																alt="Tooltip" data-toggle="tooltip" data-placement="top"
																title="<?php esc_attr_e( 'Check-in date label on the cart and checkout page', 'woocommerce-booking' ); ?>">
														</div>
														<div class="rb1-right">
															<div class="rb1-row flx-center">
																<div class="rb-col">
																	<input class="ib-md" type="text"
																		v-model="data.settings['book_item-cart-date']">
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
																title="<?php esc_attr_e( 'Check-out date label on the cart and checkout page', 'woocommerce-booking' ); ?>">
														</div>
														<div class="rb1-right">
															<div class="rb1-row flx-center">
																<div class="rb-col">
																	<input class="ib-md" type="text"
																		v-model="data.settings['checkout_item-cart-date']">
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
																title="<?php esc_attr_e( 'Booking time label on the cart and checkout page', 'woocommerce-booking' ); ?>">
														</div>
														<div class="rb1-right">
															<div class="rb1-row flx-center">
																<div class="rb-col">
																	<input class="ib-md" type="text"
																		v-model="data.settings['book_item-cart-time']">
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

							<div class="panel panel-default">
								<div class="panel-heading">
									<h2 class="panel-title" data-toggle="collapse" data-target="#collapseAddToCart"
										aria-expanded="false">
                                        <?php esc_attr_e( 'Add to Cart', 'woocommerce-booking' ); // phpcs:ignore ?>
									</h2>
								</div>
								<div id="collapseAddToCart" class="panel-collapse collapse show">
									<div class="panel-body">
										<div class="tbl-mod-1">
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
																title="<?php esc_attr_e( 'Change text for add to cart button on woocommerce product page', 'woocommerce-booking' ); ?>">
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

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Text For Check Availability Button', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="row-box-1">
														<div class="rb1-left">
															<img class="tt-info"
																src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
																alt="Tooltip" data-toggle="tooltip" data-placement="top"
																title="<?php esc_attr_e( 'Change text for check availability button on woocommerce product page when product requires confirmation', 'woocommerce-booking' ); ?>">
														</div>
														<div class="rb1-right">
															<div class="rb1-row flx-center">
																<div class="rb-col">
																	<input class="ib-md" type="text"
																		v-model="data.settings['bkap_check_availability']">
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

							<div class="panel panel-default">
								<div class="panel-heading">
									<h2 class="panel-title" data-toggle="collapse"
										data-target="#collapseBookingAvailabilityMessage" aria-expanded="false">
                                        <?php esc_attr_e( 'Booking Availability Message on Product Page', 'woocommerce-booking' ); // phpcs:ignore ?>
									</h2>
								</div>
								<div id="collapseBookingAvailabilityMessage" class="panel-collapse collapse show">
									<div class="panel-body">
										<div class="tbl-mod-1">
											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Total Stock Display Message', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'The total stock message to be displayed when the product page loads.<br><i>Note: you can use AVAILABLE_SPOTS placeholder which will be replaced by it\'s real value.</i>', 'woocommerce-booking' ); ?>">
														<textarea class="ta-sm"
															v-model="data.settings['book_stock-total']"></textarea>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Availability Display Message For A Date:', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'The availability message displayed when a date is selected in the calendar.<br><i>Note: you can use AVAILABLE_SPOTS, DATE placeholders which will be replaced by their real values.</i>', 'woocommerce-booking' ); ?>">
														<textarea class="ta-sm"
															v-model="data.settings['book_available-stock-date']"></textarea>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Availability Display Message For A Time Slot', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'The availability message displayed when a time slot is selected for a date.<br><i>Note: you can use AVAILABLE_SPOTS, DATE, TIME placeholders which will be replaced by their real values.</i>', 'woocommerce-booking' ); ?>">
														<textarea class="ta-sm"
															v-model="data.settings['book_available-stock-time']"></textarea>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Availability Display Message For A Date When Attribute Level Lockout Is Set', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'The availability message displayed when a date is selected and attribute level lockout is set for the product.<br><i>Note: you can use AVAILABLE_SPOTS, DATE, ATTRIBUTE_NAME placeholders which will be replaced by their real values.</i>', 'woocommerce-booking' ); ?>">
														<textarea class="ta-sm"
															v-model="data.settings['book_available-stock-date-attr']"></textarea>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Availability Display Message For A Time Slot When Attribute Level Lockout Is Set', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'The availability message displayed when a time slot is selected for a date and attribute level lockout is set for the product.<br><i>Note: you can use AVAILABLE_SPOTS, DATE, TIME, ATTRIBUTE_NAME placeholders which will be replaced by their real values.</i>', 'woocommerce-booking' ); ?>">
														<textarea class="ta-sm"
															v-model="data.settings['book_available-stock-time-attr']"></textarea>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Message To Be Displayed When The Time Slot Is Blocked In Real Time', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'The message to be displayed when any time slot for the date selected by the user is fully blocked in real time bookings', 'woocommerce-booking' ); ?>">
														<textarea class="ta-sm"
															v-model="data.settings['book_real-time-error-msg']"></textarea>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Message To Be Displayed For Min & Max Allowed Dates Selection For Multiple Dates', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'The message is to be displayed on the top of the booking form where customer can see that they have to select min & max dates for booking the product.', 'woocommerce-booking' ); ?>">
														<textarea class="ta-sm"
															v-model="data.settings['book_multidates_min_max_selection_msg']"></textarea>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Message To Be Displayed For Fixed Dates Selection For Multiple Dates', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'The message is to be displayed on the top of the booking form where customer can see that they have to select fixed dates for booking the product.', 'woocommerce-booking' ); ?>">
														<textarea class="ta-sm"
															v-model="data.settings['book_multidates_fixed_selection_msg']"></textarea>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="panel panel-default">
								<div class="panel-heading">
									<h2 class="panel-title" data-toggle="collapse"
										data-target="#collapseBookingAvailabilityErrorMessages" aria-expanded="false">
                                        <?php esc_attr_e( 'Booking Availability Error Messages On The Product, Cart & Checkout Pages', 'woocommerce-booking' ); // phpcs:ignore ?>
									</h2>
								</div>
								<div id="collapseBookingAvailabilityErrorMessages" class="panel-collapse collapse show">
									<div class="panel-body">
										<div class="tbl-mod-1">
											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Limited Availability Error Message For a Date', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'The error message displayed for a date booking when user tries to book more than the available quantity.<br><i>Note: you can use PRODUCT_NAME, AVAILABLE_SPOTS, DATE placeholders which will be replaced by their real values.</i>', 'woocommerce-booking' ); ?>">
														<textarea class="ta-sm"
															v-model="data.settings['book_limited-booking-msg-date']"></textarea>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'No Availability Error Message For a Date', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'The error message displayed for a date booking and bookings are no longer available for the selected date.<br><i>Note: you can use PRODUCT_NAME, DATE placeholders which will be replaced by their real values.</i>', 'woocommerce-booking' ); ?>">
														<textarea class="ta-sm"
															v-model="data.settings['book_no-booking-msg-date']"></textarea>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Limited Availability Error Message For A Time Slot', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'The error message displayed for a date and time booking when user tries to book more than the available quantity.<br><i>Note: you can use PRODUCT_NAME, AVAILABLE_SPOTS, DATE, TIME placeholders which will be replaced by their real values.</i>', 'woocommerce-booking' ); ?>">
														<textarea class="ta-sm"
															v-model="data.settings['book_limited-booking-msg-time']"></textarea>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'No Availability Error Message For A Time Slot', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'The error message displayed for a date and time booking and bookings are no longer available for the selected time slot.<br><i>Note: you can use PRODUCT_NAME, DATE, TIME placeholders which will be replaced by their real values.</i>', 'woocommerce-booking' ); ?>">
														<textarea class="ta-sm"
															v-model="data.settings['book_no-booking-msg-time']"></textarea>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Limited Availability Error Message For A Date When Attribute Level Lockout Is Set', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'The error message displayed for a date booking when user tries to book more than the available quantity setup at the attribute level.<br><i>Note: you can use PRODUCT_NAME, AVAILABLE_SPOTS, ATTRIBUTE_NAME, DATE placeholders which will be replaced by their real values.</i>', 'woocommerce-booking' ); ?>">
														<textarea class="ta-sm"
															v-model="data.settings['book_limited-booking-msg-date-attr']"></textarea>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Limited Availability Error Message For A Time Slot When Attribute Level Lockout Is Set', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'The error message displayed for a date and time booking when user tries to book more than the available quantity setup at the attribute level.<br><i>Note: you can use PRODUCT_NAME, AVAILABLE_SPOTS, ATTRIBUTE_NAME, DATE, TIME placeholders which will be replaced by their real values.</i>', 'woocommerce-booking' ); ?>">
														<textarea class="ta-sm"
															v-model="data.settings['book_limited-booking-msg-time-attr']"></textarea>
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
					<div class="bdp-foot">
						<button type="button" class="bkap-button" v-on:click.stop="save_settings">{{data.label.save_settings}}</button>
					</div>
				</div>
			</div>
		</div>
	</section>
</template>
