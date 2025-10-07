<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * View Reminders.
 *
 * @author  Tyche Softwares
 * @package BKAP/Admin/Views/Booking/Reminders
 * @since   5.19.0
 */

?>

<template id="reminders-tab">
	<section>
		<div class="container-list-table bd-page-wrap reminders-table">
			<p><?php echo esc_html__( 'Send automated, customizable booking reminder emails to notify customers about their upcoming bookings and request feedback afterwards, helping improve customer satisfaction.', 'woocommerce-booking' ); ?></p>
			<div class="row">
				<div class="bkap_admin_loader" id="show_loading_loader" v-show="show_loading_loader">
					<div class="bkap_admin_loader_wrapper">
						{{data.label.loading_loader}} <img
							src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
					</div>
				</div>

				<div class="bkap_admin_loader" id="show_saving_reminder_loader" v-show="show_saving_reminder_loader">
					<div class="bkap_admin_loader_wrapper">
						{{data.label.saving_reminder_loader}} <img
							src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
					</div>
				</div>

				<div class="bkap_admin_loader" id="show_updating_reminder_loader"
					v-show="show_updating_reminder_loader">
					<div class="bkap_admin_loader_wrapper">
						{{data.label.updating_reminder_loader}} <img
							src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
					</div>
				</div>

				<div class="bkap_admin_loader" id="show_sending_test_reminder_email_loader"
					v-show="show_sending_test_reminder_email_loader">
					<div class="bkap_admin_loader_wrapper">
						{{data.label.show_sending_test_reminder_email_loader}} <img
							src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
					</div>
				</div>

				<div class="bkap_admin_loader" id="show_custom_message_loader" v-show="false">
					<div class="bkap_admin_loader_wrapper">
						<span></span> <img src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
					</div>
				</div>

				<div class="col-md-12 bkap-wp-list-table" v-show="!show_add_edit_reminder_page">
					<div class="wbc-box">
						<div class="the-table">
							<?php
							$table = new BKAP_Admin_View_Reminders_Table();
							$table->populate_data();
							$table->prepare_items();
							$table->views();
							$table->search_box( __( 'Search', 'woocommerce-booking' ), 'reminder' );
							$table->display();
							?>
						</div>
					</div>
				</div>
			</div>

			<div class="col-md-12" v-show="show_add_edit_reminder_page">
				<div class="bkap-page-head phw-btn">
					<div class="col-left">
						<h1></h1>
					</div>

					<div class="col-right">
						<input type="button"
							value="<?php esc_attr_e( 'Send Test Reminder Email', 'woocommerce-booking' ); ?>"
							class="trietary-btn reverse" @click.stop="send_test_reminder_email" />
						<input type="button" value="<?php esc_attr_e( 'Close', 'woocommerce-booking' ); ?>"
							class="trietary-btn reverse" @click.stop="close" />
					</div>
				</div>
			</div>

			<div class="col-md-12 reminders" v-show="show_add_edit_reminder_page">
				<div class="wbc-accordion">
					<div class="panel-group bkap-accordian" id="wbc-accordion">
						<div class="panel panel-default">
							<div class="panel-heading">
								<h2 class="panel-title" data-toggle="collapse" data-target="#collapseOne"
									aria-expanded="false"
									v-html="is_edit_mode ? data.label.edit_reminder + ' ' + data.reminder_data.title : data.label.add_reminder">
								</h2>
							</div>
							<div id="collapseOne" class="panel-collapse collapse show">
								<div class="panel-body">
									<div class="tbl-mod-1">
										<div class="tm1-row">
											<div class="col-left">
												<label><?php esc_attr_e( 'Name', 'woocommerce-booking' ); ?></label>
											</div>
											<div class="col-right">
												<div class="row-box-1">
													<div class="rb1-left">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'This will be the name or title of the reminder', 'woocommerce-booking' ); ?>">
													</div>
													<div class="rb1-right">
														<div class="rb1-row flx-center">
															<div class="rb-col">
																<input class="ib-md" type="text"
																	v-model="data.reminder_data.title">
															</div>
														</div>
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
															title="<?php esc_attr_e( 'This controls the email subject line. leave blank to to use the default subject: <strong>[{blogname}] you have a booking for {product_title}</strong>', 'woocommerce-booking' ); ?>">
													</div>
													<div class="rb1-right">
														<div class="rb1-row flx-center">
															<div class="rb-col">
																<input class="ib-md" type="text"
																	v-model="data.reminder_data.email_subject">
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>

										<div class="tm1-row">
											<div class="col-left">
												<label><?php esc_attr_e( 'Email Heading', 'woocommerce-booking' ); ?></label>
											</div>
											<div class="col-right">
												<div class="row-box-1">
													<div class="rb1-left">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'This controls the email heading. leave blank to to use the default heading: <strong>Booking Reminder</strong>', 'woocommerce-booking' ); ?>">
													</div>
													<div class="rb1-right">
														<div class="rb1-row flx-center">
															<div class="rb-col">
																<input class="ib-md" type="text"
																	v-model="data.reminder_data.email_heading">
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

										<div class="tm1-row">
											<div class="col-left">
												<label><?php esc_attr_e( 'Sending delay', 'woocommerce-booking' ); ?></label>
											</div>
											<div class="col-right reminders-sending-delay">
												<div class="rc-flx-wrap flx-aln-center">
													<img class="tt-info"
														src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
														alt="Tooltip" data-toggle="tooltip" data-placement="top"
														title="<?php esc_attr_e( 'Reminder will be sent according to the selected delay value and unit.', 'woocommerce-booking' ); ?>">
													<input type="number"
														v-model="data.reminder_data.sending_delay_value" min="0">
													<select class="ib-md"
														v-model="data.reminder_data.sending_delay_unit">
														<option
															v-for="(item,key) in data.settings.sending_delay_options"
															v-bind:value="key">
															{{item}}</option>
													</select>
												</div>
											</div>
										</div>

										<div class="tm1-row">
											<div class="col-left">
												<label><?php esc_attr_e( 'Trigger', 'woocommerce-booking' ); ?></label>
											</div>
											<div class="col-right reminders-trigger">
												<div class="rc-flx-wrap flx-aln-center">
													<img class="tt-info"
														src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
														alt="Tooltip" data-toggle="tooltip" data-placement="top"
														title="<?php esc_attr_e( 'The value in this field would decide whether the reminder should be sent before the booking date or after the booking date.', 'woocommerce-booking' ); ?>">
													<select class="ib-md" v-model="data.reminder_data.trigger">
														<option v-for="(value, key) in data.settings.trigger_options"
															v-bind:value="key">{{value}}</option>
													</select>
												</div>
											</div>
										</div>

										<div class="tm1-row">
											<div class="col-left">
												<label><?php esc_attr_e( 'Products', 'woocommerce-booking' ); ?></label>
											</div>
											<div class="col-right">
												<div class="rc-flx-wrap flx-aln-center">
													<img class="tt-info"
														src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
														alt="Tooltip" data-toggle="tooltip" data-placement="top"
														title="<?php esc_attr_e( 'Reminder will be sent for the selected products in this field', 'woocommerce-booking' ); ?>">

													<div class="rc-flx-wrap flx-aln-center">
														<select class="ib-md reminder_selected_products"
															v-model="data.reminder_data.products" multiple>
															<option value="all"><?php echo esc_html__( 'All Products', 'woocommerce-booking' ); ?></option>
															<option v-for="item in data.settings.products"
																v-bind:value="item.value">{{item.label}}</option>
														</select>
													</div>
												</div>
											</div>
										</div>

										<div class="tm1-row">
											<div class="col-left">
												<label><?php esc_attr_e( 'Send SMS', 'woocommerce-booking' ); ?></label>
											</div>
											<div class="col-right">
												<div class="rc-flx-wrap flx-aln-center">
													<img class="tt-info"
														src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
														alt="Tooltip" data-toggle="tooltip" data-placement="top"
														title="<?php esc_attr_e( 'Enable this to start sending the SMS reminders. you can setup the Twilio SMS at Integrations -> Twilio SMS', 'woocommerce-booking' ); ?>">
													<label class="el-switch el-switch-green">
														<input type="checkbox"
															v-model="data.reminder_data.is_sms_enabled" true-value="on"
															false-value="">
														<span class="el-switch-style"></span>
													</label>
												</div>
											</div>
										</div>

										<div class="tm1-row reminders-sms-message"
											v-show="'on' === data.reminder_data.is_sms_enabled">
											<div class="col-left">
												<label><?php esc_attr_e( 'SMS Message', 'woocommerce-booking' ); ?></label>
											</div>
											<div class="col-right">
												<div class="rc-flx-wrap flx-aln-center">
													<img class="tt-info"
														src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
														alt="Tooltip" data-toggle="tooltip" data-placement="top"
														title="<?php esc_attr_e( 'You can insert the following tags. they will be replaced dynamically:', 'woocommerce-booking' ); ?> <strong>{product_title} {order_date} {order_number} {customer_name} {customer_first_name} {customer_last_name} {start_date} {end_date} {booking_time} {booking_id} {booking_resource} {booking_persons} {zoom_link}</strong>">
													<textarea class="ta-sm"
														v-model="data.reminder_data.sms_content"></textarea>
												</div>
											</div>
										</div>

										<div class="tm1-row">
											<div class="col-left">
												<label><?php esc_attr_e( 'Status', 'woocommerce-booking' ); ?></label>
											</div>
											<div class="col-right reminders-trigger">
												<div class="rc-flx-wrap flx-aln-center">
													<img class="tt-info"
														src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
														alt="Tooltip" data-toggle="tooltip" data-placement="top"
														title="<?php esc_attr_e( 'The reminder will be marked as active by default. change this setting if you want the reminder to be inactive after it has been saved.', 'woocommerce-booking' ); ?>">
													<select class="ib-md" v-model="data.reminder_data.status">
														<option
															v-for="(value, key) in data.settings.reminder_status_options"
															v-bind:value="key">{{value}}</option>
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
			</div>

			<div class="col-md-12" v-show="show_add_edit_reminder_page">
				<div class="bdp-foot">
					<button type="button" class="bkap-button" v-on:click.stop="save_update_reminder(false)"
						v-html="is_edit_mode ? data.label.update_reminder : data.label.save_reminder"></button>
				</div>
			</div>
		</div>
	</section>
</template>
