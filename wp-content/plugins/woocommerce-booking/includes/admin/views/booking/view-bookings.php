<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * View Bookings.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/Views/Booking/ViewBookings
 * @since       5.19.0
 */

?>

<template id="view-bookings-tab">
	<section>
		<div class="container-list-table bd-page-wrap view-bookings-table">
			<div class="row">
				<div class="bkap_admin_loader" id="show_loading_loader" v-show="show_loading_loader">
					<div class="bkap_admin_loader_wrapper">
						{{data.label.loading_loader}} <img
							src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
					</div>
				</div>

				<div class="bkap_admin_loader" id="show_updating_booking_loader" v-show="show_updating_booking_loader">
					<div class="bkap_admin_loader_wrapper">
						{{data.label.updating_booking_loader}} <img
							src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
					</div>
				</div>

				<div class="bkap_admin_loader" id="show_print_loader" v-show="false">
					<div class="bkap_admin_loader_wrapper">
						<span>{{data.label.print_loader}}</span> <img
							src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
					</div>
				</div>

				<div class="bkap_admin_loader" id="show_csv_loader" v-show="false">
					<div class="bkap_admin_loader_wrapper">
						<span>{{data.label.csv_loader}}</span> <img
							src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
					</div>
				</div>

				<div class="bkap_admin_loader" id="show_custom_message_loader" v-show="false">
					<div class="bkap_admin_loader_wrapper">
						<span></span> <img src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
					</div>
				</div>

				<div class="col-md-12 bkap-wp-list-table" v-show="!show_edit_booking_page">
					<div id="bkap-screen-meta" class="metabox-prefs">
						<div id="screen-options-wrap" class="" tabindex="-1" aria-label="Screen Options Tab">
							<fieldset class="metabox-prefs">
								<legend><?php echo esc_html__( 'Columns', 'woocommerce-booking' ); ?></legend>
								<?php
								$table          = new BKAP_Admin_View_Bookings_Table();
								$hidden_columns = get_user_meta( get_current_user_id(), 'manageedit-bkap_bookingcolumnshidden', true );
								$hidden_columns = is_array( $hidden_columns ) ? $hidden_columns : array();
								foreach ( $table->get_columns() as $column_id => $column_name ) {
									$checked = is_array( $hidden_columns ) && ! in_array( 'bkap_' . $column_id, $hidden_columns );
									if ( str_contains( $column_name, 'checkbox' ) ) {
										$column_name = __( 'ID', 'woocommerce-booking' );
									}
									echo '<label>';
									echo '<input @click="bkap_show_hide_view_booking_column( \'' . esc_attr( $column_id ) . '\' )" type="checkbox" name="column-' . esc_attr( $column_id ) . '" value="1" ' . checked( $checked, true, false ) . '>';
									echo esc_html( $column_name );
									echo '</label>';
								}
								?>
							</fieldset>
							<fieldset class="screen-options">
								<legend><?php echo esc_html__( 'Pagination', 'woocommerce-booking' ); ?></legend>
								<?php $bookings_per_page = get_user_meta( get_current_user_id(), 'edit_bkap_booking_per_page', true ); ?>
								<label
									for="edit_shop_order_per_page"><?php echo esc_html__( 'Number of items per page:', 'woocommerce-booking' ); ?></label>
								<input type="number" step="1" min="1" max="999" class="screen-per-page"
									name="edit_bkap_booking_per_page" id="edit_bkap_booking_per_page" maxlength="3"
									v-model="data.settings.view_booking_items"
									value="<?php echo esc_html( $bookings_per_page ); ?>">
							</fieldset>
							<p class="submit">
								<button type="button" id="bkap_save_screen_options" class="button secondary-btn"
									@click="bkap_save_screen_options"><?php echo esc_html__( 'Apply', 'woocommerce-booking' ); ?></button>
							</p>
						</div>
					</div>
					<div id="screen-meta-links">
						<div id="screen-options-link-wrap" class="hide-if-no-js screen-meta-toggle">
							<button type="button" id="bkap-show-settings-link"
								class="button trietary-btn reverse bkap-show-settings"
								aria-controls="screen-options-wrap"
								aria-expanded="false"><?php echo esc_html__( 'Screen Options', 'woocommerce-booking' ); ?></button>
						</div>
					</div>
					<div class="mb-5"></div>
					<div class="wbc-box">
						<div class="the-table">
							<?php
								do_action( 'before_bkap_view_booking_table' );
								$table->populate_data();
								$table->prepare_items();
								$table->views();
								$table->search_box( __( 'Search', 'woocommerce-booking' ), 'search-view-bookings' );
								$table->display();
							?>
						</div>
					</div>
				</div>
			</div>

			<div class="col-md-8 offset-md-2" v-show="show_edit_booking_page">
				<div class="bkap-page-head phw-btn">
					<div class="col-left">
						<h1>{{data.edit_booking.data.booking_text}} #{{data.edit_booking.data.booking_id}}</h1>
						<br>
						<span v-if="'' != data.edit_booking.data.order_number"
							v-html="`Linked to Order <a href='${data.edit_booking.data.order_url}' target='_blank'>#${data.edit_booking.data.order_number}</a> created on ${data.edit_booking.data.date_created}`"></span>
						<span v-if="'' == data.edit_booking.data.order_number"
							v-html="`No order is linked to this booking. Booking created on ${data.edit_booking.data.date_created}`"></span>
					</div>

					<div class="col-right">
						<input type="button" value="Close Window" class="trietary-btn reverse" @click.stop="close">
					</div>
				</div>
			</div>

			<div class="col-md-8 offset-md-2 view-bookings" v-show="show_edit_booking_page">
				<div class="wbc-accordion">
					<div class="panel-group bkap-accordian" id="wbc-accordion">
						<div class="panel panel-default">
							<div class="panel-heading">
								<h2 class="panel-title" data-toggle="collapse" data-target="#collapseThree"
									aria-expanded="false">
                                    <?php esc_attr_e( 'Booking Details', 'woocommerce-booking' ); // phpcs:ignore ?>
								</h2>
							</div>
							<div id="collapseThree" class="panel-collapse collapse show edit-page-booking-panel">
								<div class="panel-body">
									<div class="tbl-mod-1">
										<div class="tm1-row">
											<div class="col-left">
												<label><?php esc_attr_e( 'Booked Product', 'woocommerce-booking' ); ?></label>
											</div>
											<div class="col-right">
												<div class="row-box-1">
													<div class="rb1-left"></div>
													<div class="rb1-right">
														<div class="rb1-row flx-center">
															<div class="rb-col">
																<input class="ib-md" type="text" disabled
																	v-model="data.edit_booking.data.product_name">
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>

										<div class="tm1-row">
											<div class="col-left">
												<label><?php esc_attr_e( 'Date', 'woocommerce-booking' ); ?></label>
											</div>
											<div class="col-right">
												<div class="row-box-1">
													<div class="rb1-left"></div>
													<div class="rb1-right">
														<div class="rb1-row flx-center">
															<div class="rb-col">
                                                                <label><?php esc_attr_e( 'Start Date:', 'woocommerce-booking' ); // phpcs:ignore  ?></label>
																<span>{{data.edit_booking.data.start_date}}</span><br />
																<br />
																<label
                                                                    v-show="'' !== data.edit_booking.data.end_date"><?php esc_attr_e( 'End Date:', 'woocommerce-booking' ); // phpcs:ignore  ?></label>
																<span
																	v-show="'' !== data.edit_booking.data.end_date">{{data.edit_booking.data.end_date}}</span>
															</div>
														</div>

													</div>
												</div>
											</div>
										</div>

										<div class="tm1-row" v-show="'' !== data.edit_booking.data.booking_comment">
											<div class="col-left">
												<label><?php esc_attr_e( 'Additional Comment', 'woocommerce-booking' ); ?></label>
											</div>
											<div class="col-right">
												<div class="row-box-1">
													<div class="rb1-left"></div>
													<div class="rb1-right">
														<div class="rb1-row flx-center">
															<div class="rb-col">
															{{data.edit_booking.data.booking_comment}}
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>

										<div class="tm1-row">
											<div class="col-left">
												<label><?php esc_attr_e( 'Booking Status', 'woocommerce-booking' ); ?></label>
											</div>
											<div class="col-right">
												<div class="row-box-1">
													<div class="rb1-left"></div>
													<div class="rb1-right">
														<div class="rb1-row flx-center">
															<div class="rb-col">
																<select class="ib-small" id="edit_booking_status"
																	v-model="data.edit_booking.data.booking_status">
																	<option
																		v-for="(item,key) in data.settings.booking_statuses"
																		v-bind:value="key">{{item}}</option>
																</select>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>

										<div class="tm1-row">
											<div class="col-left">
												<label><?php esc_attr_e( 'Quantity', 'woocommerce-booking' ); ?></label>
											</div>
											<div class="col-right">
												<div class="row-box-1">
													<div class="rb1-left"></div>
													<div class="rb1-right">
														<div class="rb1-row flx-center">
															<div class="rb-col">
																<input id="bkap_qty" class="ib-md" type="number"
																	name="quantity" min=1
																	v-model="data.edit_booking.data.quantity">
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>

										<div class="tm1-row">
											<div class="col-left">
												<label><?php esc_attr_e( 'Total', 'woocommerce-booking' ); ?></label>
											</div>
											<div class="col-right">
												<div class="row-box-1">
													<div class="rb1-left"></div>
													<div class="rb1-right">
														<div class="rb1-row flx-center">
															<div class="rb-col">
																<p id="total" class="ib-md">
																	<?php echo get_woocommerce_currency_symbol(); //phpcs:ignore ?>{{data.edit_booking.data.total}}
																</p>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>

										<div class="tm1-row">
											<div class="col-left">
												<label><?php esc_attr_e( 'Send Reminder', 'woocommerce-booking' ); ?></label>
											</div>
											<div class="col-right">
												<div class="row-box-1">
													<div class="rb1-left"></div>
													<div class="rb1-right">
														<div class="rb1-row flx-center">
															<div class="rb-col">
																<select class="ib-small" id="booking_reminder"
																	v-model="data.reminder.reminder_id">
																	<option
																		v-for="(item,key) in data.settings.bkap_reminders"
																		v-bind:value="key">{{item}}</option>
																</select>
															</div>
															<div class="rb-col">
																<input class="secondary-btn"
																	v-on:click.stop="bkap_send_reminder_manually( data.reminder.reminder_id, data.edit_booking.data.booking_id )"
																	v-show="!data.reminder.sending_reminder"
																	value="<?php esc_attr_e( 'Send', 'woocommerce-booking' ); ?>"
																	type="submit">
																<button class="secondary-btn btn-disabled" type="button" disabled
																	v-show="data.reminder.sending_reminder">
																	<span class="spinner-border spinner-border-sm"
																		role="status" aria-hidden="true"></span>
																	<?php esc_attr_e( 'Sending', 'woocommerce-booking' ); ?>
																</button>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>

										<div class="tm1-row"
											v-show="Object.keys(data.edit_booking.data.timezone).length > 0">
											<div class="col-left">
                                                <label><?php esc_attr_e( 'Timezone ( per Client )', 'woocommerce-booking' ); // phpcs:ignore  ?></label>
											</div>
											<div class="col-right">
												<div class="row-box-1">
													<div class="rb1-left"></div>
													<div class="rb1-right">
														<div class="rb1-row flx-center">
															<div class="rb-col">
                                                                <label><?php esc_attr_e( 'Timezone:', 'woocommerce-booking' ); // phpcs:ignore  ?></label>
																<span>{{data.edit_booking.data.timezone.name}}</span><br>
                                                                <label><?php esc_attr_e( 'Start Date:', 'woocommerce-booking' ); // phpcs:ignore  ?></label>
																<span>{{data.edit_booking.data.timezone.start_date}}</span><br>
                                                                <label><?php esc_attr_e( 'End Date:', 'woocommerce-booking' ); // phpcs:ignore  ?></label>
																<span>{{data.edit_booking.data.timezone.end_date}}</span>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>

										<div class="tm1-row"
											v-show="Object.keys(data.edit_booking.data.zoom_meeting).length > 0">
											<div class="col-left">
                                                <label><?php esc_attr_e( 'Zoom Meeting', 'woocommerce-booking' ); // phpcs:ignore  ?></label>
											</div>
											<div class="col-right">
												<div class="row-box-1">
													<div class="rb1-left"></div>
													<div class="rb1-right">
														<div class="rb1-row flx-center">
															<div class="rb-col">
																<div
																	v-if="'' != data.edit_booking.data.zoom_meeting.meeting_link">
																	<span
																		v-html="`<a href='${data.edit_booking.data.zoom_meeting.meeting_link}' target='_blank'>${data.edit_booking.data.zoom_meeting.meeting_text}</a>`"></span>
																</div>
																<div v-else>
																	<input type="text"
																		v-model="data.edit_booking.data.zoom_meeting.new_zoom_link"
																		name="bkap_manual_zoom_meeting"
																		id="bkap_manual_zoom_meeting"
																		placeholder="<?php echo esc_html__( 'Add meeting link here', 'woocommerce-booking' ); ?>"><span
																		id="bkap_add_zoom_meeting"></span><br><button
																		type="button"
																		@click.stop="bkap_add_zoom_meeting"
																		class="secondary-btn mt-3 mb-3"
																		id="bkap_add_zoom_meeting_link"><?php echo esc_html__( 'Add zoom meeting', 'woocommerce-booking' ); ?></button>
																	<br><i
																		id="bkap_manual_zoom_meeting_info"><?php echo esc_html__( 'Keeping above field blank will generate new meeting link.', 'woocommerce-booking' ); ?></i>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>

										<div class="tm1-row">
											<div class="col-left">
												<label><?php esc_attr_e( 'Booking Form', 'woocommerce-booking' ); ?></label>
											</div>
											<div class="col-right edit-page-booking-div">
												<div class="row-box-1">
													<div class="rb1-row flx-center">
														<div class="rb-col" id="bkap_edit_booking">
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

			<div class="col-md-8 offset-md-2" v-show="show_edit_booking_page">
				<div class="bdp-foot">
					<button type="button" class="bkap-button"
						v-on:click.stop="save_booking">{{data.settings.label.save_booking}}</button>
				</div>
			</div>
		</div>
		</div>
	</section>
</template>
