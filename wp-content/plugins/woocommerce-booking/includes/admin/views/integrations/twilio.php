<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * Twilio SMS.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/Views/Integrations/Twilio
 * @since       5.19.0
 */

?>
<template id="twilio-tab">
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

				<div class="bkap_admin_loader" v-show="show_sending_sms_loader">
					<div class="bkap_admin_loader_wrapper">
						{{data.label.sending_sms_loader}} <img
							src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
					</div>
				</div>

				<div class="col-md-12">
					<div class="bkap-page-head phw-btn">
						<div class="col-left">
                            <h1><?php esc_attr_e( 'Twilio SMS', 'woocommerce-booking' ); // phpcs:ignore ?></h1>
							<p><?php esc_attr_e( 'Configure your Twilio account settings below. Please note that due to some restrictions from Twilio, customers may sometimes receive delayed messages', 'woocommerce-booking' ); ?>
							</p>
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
                                        <?php esc_attr_e( 'Twilio General Settings', 'woocommerce-booking' ); // phpcs:ignore  ?>
									</h2>
								</div>
								<div id="collapseOne" class="panel-collapse collapse show">
									<div class="panel-body">
										<div class="tbl-mod-1">
											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Activate', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'Enable the ability to send reminder SMS for bookings.', 'woocommerce-booking' ); ?>">
														<label class="el-switch el-switch-green">
															<input type="checkbox" v-model="data.settings.enable_sms"
																true-value="on" false-value="">
															<span class="el-switch-style"></span>
														</label>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Sender', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="row-box-1">
														<div class="rb1-left">
															<img class="tt-info"
																src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
																alt="Tooltip" data-toggle="tooltip" data-placement="top"
																title="<?php esc_attr_e( 'Enter a phone number (e.g., +1xxxxxxxxxx) or an alphanumeric sender ID.', 'woocommerce-booking' ); ?>">
														</div>
														<div class="rb1-right">
															<div class="rb1-row flx-center">
																<div class="rb-col">
																	<input class="ib-md" type="text"
																		v-model="data.settings.from">
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Account SID', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="">
														<textarea class="ta-sm textarea-like-input"
															v-model="data.settings.account_sid"></textarea>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Auth Token', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="">
														<textarea class="ta-sm textarea-like-input"
															v-model="data.settings.auth_token"></textarea>
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

				<div class="container-fluid pl-info-wrap" id="bkap_send_sms_success_message"
					v-show="show_send_sms_success_message">
					<div class="row">
						<div class="col-md-12">
							<div class="alert alert-success alert-dismissible fade show" role="alert">
								<span v-html="send_sms_success_message"></span>
								<button type="button" class="close" data-dismiss="alert" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
						</div>
					</div>
				</div>

				<div class="container-fluid pl-info-wrap" v-show="show_sending_sms_error_message">
					<div class="row">
						<div class="col-md-12">
							<div class="alert alert-danger alert-dismissible fade show" role="alert">
								<span v-html="sending_sms_error_message"></span>
								<button type="button" class="close" data-dismiss="alert" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
						</div>
					</div>
				</div>

				<div id="div_send_test_sms" class="col-md-12" v-show="'on' === data.settings.enable_sms">
					<div class="wbc-accordion">
						<div class="panel-group bkap-accordian" id="wbc-accordion">
							<div class="panel panel-default">
								<div class="panel-heading">
									<h2 class="panel-title" data-toggle="collapse" data-target="#collapseOne"
										aria-expanded="false">
                                        <?php esc_attr_e( 'Send Test SMS', 'woocommerce-booking' ); // phpcs:ignore  ?>
									</h2>
									
									<p>
								</div>
								<div id="collapseOne" class="panel-collapse collapse show">
									<div class="panel-body">
										<div class="tbl-mod-1">
											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Recepient', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="row-box-1">
														<div class="rb1-left">
															<img class="tt-info"
																src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
																alt="Tooltip" data-toggle="tooltip" data-placement="top"
																title="<?php esc_attr_e( 'Enter a phone number (e.g., +1xxxxxxxxxx) or an alphanumeric sender ID.', 'woocommerce-booking' ); ?>">
														</div>
														<div class="rb1-right">
															<div class="rb1-row flx-center">
																<div class="rb-col">
																	<input class="ib-md" type="text"
																		v-model="data.test.phone_number">
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Message', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="">
														<textarea class="ta-sm" v-model="data.test.message"></textarea>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
												</div>
												<div class="col-right">
													<a href="javascript:void(0);" class="secondary-btn pl-ss-btn"
														@click="send_test_sms">{{data.label.send_test_sms}}</a>
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
		</div>
	</section>
</template>
