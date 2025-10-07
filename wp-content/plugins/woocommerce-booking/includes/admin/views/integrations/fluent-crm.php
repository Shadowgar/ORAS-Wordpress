<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * FluentCRM.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/Views/Integrations/FluentCRM
 * @since       5.19.0
 */

?>
<template id="fluent-crm-tab">
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
                            <h1><?php esc_attr_e( 'FluentCRM', 'woocommerce-booking' ); // phpcs:ignore ?></h1>
                            <p><?php esc_attr_e( 'Manage Fluent CRM integration settings.', 'woocommerce-booking' ); // phpcs:ignore ?>
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
                                        <?php esc_attr_e( 'General Settings', 'woocommerce-booking' ); // phpcs:ignore  ?>
									</h2>
								</div>
								<div id="collapseOne" class="panel-collapse collapse show">
									<div class="panel-body">
										<div class="tbl-mod-1">
											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'Instructions', 'woocommerce-booking' ); ?></label>
													<p><a class="instructions"
															v-on:click.stop="toggle_display('show_instructions_fluent_crm')"><?php esc_attr_e( 'Click here', 'woocommerce-booking' ); ?></a>
                                                        <?php echo wp_kses_post( __( ' to view instructions on finding your <b>API Name</b> and <b>API Key</b>', 'woocommerce-booking' ), array( 'b' =>array() ) ); // phpcs:ignore ?>
													</p>
												</div>

												<div class="col-right" v-show="show_instructions_fluent_crm">
													<div class="rc-flx-wrap flx-aln-center ro-wrap">
														<div class="alert alert-dark alert-info" role="alert">
															<div class="left-col"><img
																	src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info-small.svg' ); ?>"
																	alt="Info Icon"></div>
															<div class="right-col">
																<ol>
                                                                    <li><?php esc_attr_e( 'Go to FluentCRM -> Settings -> Managers for Adding New Manager.', 'woocommerce-booking' ); // phpcs:ignore ?>
																	</li>
                                                                    <li><?php esc_attr_e( 'After clicking on Add New Manager button, a popup will appear. Enter the email address and enable the required Permissions and click on Create button.', 'woocommerce-booking' ); // phpcs:ignore?>
																	</li>
                                                                    <li><?php esc_attr_e( 'Go to the REST API tab and click on Add New Key button. Give the name and select the Manager and click on Create button.', 'woocommerce-booking' ); // phpcs:ignore?>
																	</li>
                                                                    <li><?php esc_attr_e( 'Copy API Username and API Password and set them to API Name and API Key fields respectively.', 'woocommerce-booking' ); // phpcs:ignore?>
																	</li>
																</ol>
															</div>
														</div>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'API Name', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'The API Name obtained from FluentCRM-> Settings-> REST API.', 'woocommerce-booking' ); ?>">
														<textarea class="ta-sm textarea-like-input"
															v-model="data.settings.bkap_fluentcrm_api_name"></textarea>
													</div>
												</div>
											</div>

											<div class="tm1-row">
												<div class="col-left">
													<label><?php esc_attr_e( 'API Key', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="<?php esc_attr_e( 'The API Key obtained from FluentCRM-> Settings-> REST API.', 'woocommerce-booking' ); ?>">
														<textarea class="ta-sm textarea-like-input"
															v-model="data.settings.bkap_fluentcrm_api_key"></textarea>
													</div>
												</div>
											</div>

											<div class="tm1-row" v-show="data.settings.lists.length > 0">
												<div class="col-left">
													<label><?php esc_attr_e( 'List', 'woocommerce-booking' ); ?></label>
												</div>
												<div class="col-right">
													<div class="rc-flx-wrap flx-aln-center">
														<img class="tt-info"
															src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
															alt="Tooltip" data-toggle="tooltip" data-placement="top"
															title="">
														<select class="ib-md"
															v-model="data.settings.bkap_fluentcrm_list">
															<option v-for="list in data.settings.lists"
																v-bind:value="list.id">{{list.title}}</option>
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

				<div class="col-md-12">
					<div class="bdp-foot">
						<button type="button" class="bkap-button" v-on:click.stop="save_settings">{{data.label.save_settings}}</button>
					</div>
				</div>
			</div>
		</div>
	</section>
</template>
