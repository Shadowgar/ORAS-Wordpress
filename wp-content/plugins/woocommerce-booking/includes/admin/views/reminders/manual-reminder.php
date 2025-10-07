<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * Manual Reminders.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/Views/Booking/ManualReminders
 * @since       5.19.0
 */
?>

<template id="manual-reminder-tab">
	<section>
		<div class="container-list-table bd-page-wrap manual-reminders">
			<div class="row">
				<div class="bkap_admin_loader" id="show_loading_loader" v-show="show_loading_loader">
					<div class="bkap_admin_loader_wrapper">
						{{data.label.loading_loader}} <img
							src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
					</div>
				</div>

				<div class="bkap_admin_loader" id="sending_manual_reminder_loader"
					v-show="sending_manual_reminder_loader">
					<div class="bkap_admin_loader_wrapper">
						{{data.label.sending_manual_reminder_loader}} <img
							src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
					</div>
				</div>

				<div class="bkap_admin_loader" id="saving_manual_reminder_draft" v-show="saving_manual_reminder_draft">
					<div class="bkap_admin_loader_wrapper">
						{{data.label.saving_manual_reminder_draft}} <img
							src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
					</div>
				</div>
			</div>

			<div class="col-md-12">
				<div class="bkap-page-head phw-btn">
					<div class="col-left">
						<h1></h1>
					</div>

					<div class="col-right">
						<input type="button" value="<?php esc_attr_e( 'Save as Draft', 'woocommerce-booking' ); ?>"
							class="trietary-btn reverse" @click.stop="save_reminder_as_draft">
					</div>
				</div>
			</div>

			<div class="col-md-12 reminders">
				<div class="wbc-accordion">
					<div class="panel-group bkap-accordian" id="wbc-accordion">
						<div class="panel panel-default">
							<div class="panel-heading">
								<h2 class="panel-title" data-toggle="collapse" data-target="#collapseOne"
									aria-expanded="false">
									<?php esc_attr_e( 'Manual Email Reminder', 'woocommerce-booking' ); ?>
								</h2>
							</div>
							<div id="collapseOne" class="panel-collapse collapse show">
								<div class="panel-body">
									<div class="tbl-mod-1">
										<div class="tm1-row">
											<div class="col-left">
												<label><?php esc_attr_e( 'Send Reminder For', 'woocommerce-booking' ); ?></label>
											</div>
											<div class="col-right">
												<div class="rc-flx-wrap flx-aln-center ro-wrap">
													<div class="rb-flx-style mb-15">
														<div class="el-radio el-radio-green">
															<input type="radio" value="order" id="order"
																v-model="data.manual_reminder_data.send_reminder_for">
															<label for="order" class="el-radio-style"></label>
														</div>
														<label
															for="order"><?php esc_attr_e( 'Order #', 'woocommerce-booking' ); ?></label>
													</div>
													<div class="rb-flx-style mb-15">
														<div class="el-radio el-radio-green">
															<input type="radio" value="booking" id="booking"
																v-model="data.manual_reminder_data.send_reminder_for">
															<label for="booking" class="el-radio-style"></label>
														</div>
														<label
															for="booking"><?php esc_attr_e( 'Booking ID', 'woocommerce-booking' ); ?></label>
													</div>
													<div class="rb-flx-style mb-15">
														<div class="el-radio el-radio-green">
															<input type="radio" value="product" id="product"
																v-model="data.manual_reminder_data.send_reminder_for">
															<label for="product" class="el-radio-style"></label>
														</div>
														<label
															for="product"><?php esc_attr_e( 'Product', 'woocommerce-booking' ); ?></label>
													</div>
												</div>
											</div>
										</div>

										<div class="tm1-row"
											v-show="'order' === data.manual_reminder_data.send_reminder_for">
											<div class="col-left">
												<label><?php esc_attr_e( 'Order IDs', 'woocommerce-booking' ); ?></label>
											</div>
											<div class="col-right">
												<div class="rc-flx-wrap flx-aln-center">
													<img class="tt-info"
														src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
														alt="Tooltip" data-toggle="tooltip" data-placement="top"
														title="">

													<div class="rc-flx-wrap flx-aln-center">
														<select class="ib-md manual_reminder_send_reminder_for_order"
															v-model="data.manual_reminder_data.order_ids" multiple>
															<option v-for="id in data.settings.order_ids"
																v-bind:value="id">{{id}}</option>
														</select>
													</div>
												</div>
											</div>
										</div>

										<div class="tm1-row"
											v-show="'booking' === data.manual_reminder_data.send_reminder_for">
											<div class="col-left">
												<label><?php esc_attr_e( 'Booking IDs', 'woocommerce-booking' ); ?></label>
											</div>
											<div class="col-right">
												<div class="rc-flx-wrap flx-aln-center">
													<img class="tt-info"
														src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
														alt="Tooltip" data-toggle="tooltip" data-placement="top"
														title="">

													<div class="rc-flx-wrap flx-aln-center">
														<select class="ib-md manual_reminder_send_reminder_for_booking"
															v-model="data.manual_reminder_data.booking_ids" multiple>
															<option v-for="id in data.settings.booking_ids"
																v-bind:value="id">{{id}}</option>
														</select>
													</div>
												</div>
											</div>
										</div>

										<div class="tm1-row"
											v-show="'product' === data.manual_reminder_data.send_reminder_for">
											<div class="col-left">
												<label><?php esc_attr_e( 'Product', 'woocommerce-booking' ); ?></label>
											</div>
											<div class="col-right">
												<div class="rc-flx-wrap flx-aln-center">
													<img class="tt-info"
														src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
														alt="Tooltip" data-toggle="tooltip" data-placement="top"
														title="">

													<div class="rc-flx-wrap flx-aln-center">
														<select class="ib-md manual_reminder_send_reminder_for_product"
															v-model="data.manual_reminder_data.product_ids" multiple>
															<option v-for="item in data.settings.product_ids"
																v-bind:value="item.id">{{item.title}}</option>
														</select>
													</div>
												</div>
											</div>
										</div>

										<div class="tm1-row">
											<div class="col-left">
												<label><?php esc_attr_e( 'Email Subject', 'woocommerce-booking' ); ?></label>
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
																<input class="ib-md" type="text"
																	v-model="data.manual_reminder_data.email_subject">
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>

										<div class="tm1-row">
											<div class="col-left">
												<label><?php esc_attr_e( 'Email Content', 'woocommerce-booking' ); ?></label>
											</div>
											<div class="col-right">
												<div class="row-box-1">
													<div class="rb1-left">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'You can insert the following tags. they will be replaced dynamically:', 'woocommerce-booking' ); ?> <strong>{start_date} {end_date} {booking_time} {booking_id} {booking_resource} {booking_persons} {zoom_link} {product_title} {order_number} {order_date} {customer_name} {customer_first_name} {customer_last_name} {booking_table}</strong>">
													</div>
													<div class="rb1-right">
														<div class="rb1-row flx-center">
														<div class="rb-col" id="bkap_wp_editor_email_content">
																<?php
																	wp_editor(
																		'',
																		'email_content',
																		array(
																			'wpautop'       => false,
																			'media_buttons' => true,
																			'textarea_rows' => 20,
																			'teeny'         => false,
																		)
																	);
																	?>
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
				</div>
			</div>

			<div class="col-md-12">
				<div class="bdp-foot">
					<button type="button" class="bkap-button"
						v-on:click.stop="send_email_reminder(false)"><?php esc_attr_e( 'Send Reminder', 'woocommerce-booking' ); ?></button>
				</div>
			</div>
		</div>
	</section>
</template>
