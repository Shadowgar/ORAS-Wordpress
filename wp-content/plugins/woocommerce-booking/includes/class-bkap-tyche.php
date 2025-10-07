<?php
/**
 * Booking & Appointment Plugin for WooCommerce - Tyche Class
 *
 * @version 1.1.7
 * @since   1.1.3
 * @author  Tyche Softwares
 * @package Booking & Appointment Plugin for WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'BKAP_Tyche' ) ) {

	/** Declaration of Class */
	class BKAP_Tyche {

		/** Constructor */
		public function __construct() {

			add_filter(
				'bkap_el_option',
				function () {
					return bkap_admin_license()->enterprise_license();
				}
			);

			add_filter(
				'bkap_bl_option',
				function () {
					return bkap_admin_license()->business_license();
				}
			);

			add_filter(
				'bkap_el_error_message',
				function () {
					return bkap_admin_license()->enterprise_license_error_message();
				}
			);

			add_filter(
				'bkap_bl_error_message',
				function () {
					return bkap_admin_license()->business_license_error_message();
				}
			);

			add_filter(
				'bkap_other_plugin_listings',
				function ( $files ) {
					$files[] = 'license';
					return $files;
				}
			);

			add_filter(
				'bkap_home_tabs',
				function ( $tabs ) {
					$tabs['license'] = __( 'License', 'woocommerce-booking' );
					return $tabs;
				}
			);

			add_filter(
				'bkap_other_plugin_listings',
				function ( $plugins ) {
					$plugins = array(
						'acp'  => array(
							'name' => __( 'Recover lost sales with Abandon Cart Pro for WooCommerce', 'woocommerce-booking' ),
							'link' => 'https://www.woocommerce.com/products/abandoned-cart-pro',
						),
						'dfw' => array(
							'name' => __( 'Deposits For WooCommerce', 'woocommerce-booking' ),
							'link' => 'https://www.woocommerce.com/products/flexi-deposits',
						),
						'pbur' => array(
							'name' => __( 'Product Price by User Roles', 'woocommerce-booking' ),
							'link' => 'https://www.woocommerce.com/products/product-prices-by-user-roles-for-woocommerce',
						),
						'prdd' => array(
							'name' => __( 'Flexi Custom Order Status', 'woocommerce-booking' ),
							'link' => 'https://www.woocommerce.com/products/flexi-custom-order-status',
						),
					);
					return $plugins;
				}
			);

			add_filter(
				'bkap_do_file_check',
				function ( $filename ) {

					switch ( $filename ) {
						case 'zoom':
						case 'zapier':
							$filename = apply_filters( 'bkap_el_option', true ) ? $filename : BKAP_PLUGIN_PATH . '/includes/tyche/views/invalid-license/' . $filename;
							break;
					}

					return $filename;
				}
			);

			add_filter(
				'bkap_do_file_check_fluent_crm',
				function ( $filename ) {

					$filename = apply_filters( 'bkap_el_option', true ) ? $filename : BKAP_PLUGIN_PATH . '/includes/tyche/views/invalid-license/' . $filename;

					return $filename;
				}
			);

			add_filter(
				'bkap_support_url',
				function () {
					return 'https://support.tychesoftwares.com/help/2285384554?utm_source=bkapfooter&utm_medium=link&utm_campaign=BookingAndAppointmentPlugin';
				}
			);

			add_filter(
				'bkap_onboarding_template_id',
				function () {
					return 'onboarding-data-tracking';
				}
			);
			add_action( 'bkap_onboarding_finish_screen_after_message', array( $this, 'bkap_add_tracking_option' ) );

			add_action( 'bkap_home_welcome_after_plugin_listing', array( $this, 'bkap_add_community_knowledge_support_section' ) );

			add_action( 'bkap_after_global_settings', array( $this, 'bkap_reset_option_global_settings' ) );
			add_action( 'show_message_on_global_settings', array( $this, 'bkap_show_tracking_message' ) );

			add_action( 'bkap_footer_after_support_section', array( $this, 'bkap_section_to_ask_review' ) );
		}

		/**
		 * This function will add the review section in the footer.
		 */
		public function bkap_section_to_ask_review() {
			?>
			<p>
			<?php
				printf(
					/* translators: %s Link to rate it */
					esc_html__(
						'If this plugin helped you, %1$s %2$s',
						'woocommerce-booking'
					),
					sprintf(
						/* Translators: %s Link, %s Link Name */
						'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
						esc_url( 'https://www.tychesoftwares.com/submit-review/?utm_source=bkapfooter&utm_medium=link&utm_campaign=BookingAndAppointmentPlugin' ),
						esc_html__( 'please rate it', 'woocommerce-booking' )
					),
					sprintf( '<span class="rating">★★★★★</span>' )
				);
			?>
			</p>
			<?php
		}

		/**
		 * This function is for adding the content on the global settings page to show a message after resetting the tracking data.
		 * It is called when the reset tracking data button is clicked.
		 */
		public function bkap_show_tracking_message() {
			?>
			<div class="container-fluid pl-info-wrap" id="bkap_admin_view_message"
				v-show="show_reset_tracking_message">
				<div class="row">
					<div class="col-md-12">
						<div class="alert alert-success alert-dismissible fade show" role="alert">
							<?php esc_attr_e( 'Settings for Plugin Usage Tracking have been reset.', 'woocommerce-booking' ); ?>
							<button type="button" class="close" data-dismiss="alert" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * This function is called in the global settings section to add a reset option for usage tracking.
		 * It provides a button to reset the tracking data and displays an info tooltip.
		 */
		public function bkap_reset_option_global_settings() {
			?>
			<div class="panel panel-default">
				<div class="panel-heading">
					<h2 class="panel-title" data-toggle="collapse" data-target="#collapsePluginData"
						aria-expanded="false">
						<?php esc_attr_e( 'Plugin Data', 'woocommerce-booking' ); // phpcs:ignore ?>
					</h2>
				</div>
				<div id="collapsePluginData" class="panel-collapse collapse show">
					<div class="panel-body">
						<div class="tbl-mod-1">
							<div class="tm1-row">
								<div class="col-left">
									<label><?php esc_attr_e( 'Reset usage Tracking', 'woocommerce-booking' ); ?></label>
								</div>

								<div class="col-right">
									<div class="row-box-1 flx-center">
										<div class="rb1-left">
											<img class="tt-info"
												src="<?php echo esc_url( BKAP_IMAGE_URL . 'icon-info.svg' ); ?>"
												alt="Tooltip" data-toggle="tooltip" data-placement="top"
												title="<?php esc_attr_e( 'This will reset your usage tracking settings, causing it to show the opt-in banner again and not sending any data.', 'woocommerce-booking' ); ?>">
										</div>
										<div class="rb1-right">
											<div class="rb1-row">
												<input class="trietary-btn reverse" type="button"
													value="Reset" v-on:click.stop="reset_tracking_data">
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * This function is called in the onboarding finish screen to add checkbox switch for data tracking.
		 */
		public function bkap_add_tracking_option() {
			?>
			<p class="p1">
				<label class="el-switch el-switch-green">
					<input type="checkbox"
						v-model="data.settings.allow_tracking"
						true-value="on" false-value=""
						@change="allowed_tracking">
					<span class="el-switch-style"></span>
				</label>
				<?php esc_html_e( 'Yes, count me in.', 'woocommerce-booking' ); ?>
			</p>
			{{data.allow_tracking}}
			<?php
		}

		/**
		 * Adds the community, knowledge base, and support sections to the welcome page.
		 */
		public function bkap_add_community_knowledge_support_section() {
			?>
			<div class="bkap-wc-3col-mod">
				<div class="wc3-mod-box">
					<div class="wc3-col">
						<h3><?php echo esc_html__( 'Join our Community', 'woocommerce-booking' ); ?></h3>
						<p><?php echo esc_html__( 'Want to hang out with us? Our Facebook group is the perfect place to meet like-minded users and learn more about our plugins.', 'woocommerce-booking' ); ?></p>
						<p><a class="link-wul" href="<?php echo esc_url( 'https://www.facebook.com/groups/3298268366902645' ); ?>" target="_blank"><?php echo esc_html__( 'Tyche Softwares Business Community', 'woocommerce-booking' ); ?></a></p>
					</div>

					<div class="wc3-col">
						<h3><?php echo esc_html__( 'Knowledge base', 'woocommerce-booking' ); ?></h3>
						<p><?php echo esc_html__( 'Maximize your store performance! Get all the info you need from our Knowledge base.', 'woocommerce-booking' ); ?></p>
						<p><a class="link-wul" href="<?php echo esc_url( 'https://www.tychesoftwares.com/docs/docs/booking-appointment-plugin-for-woocommerce-new/' ); ?>" target="_blank"><?php echo esc_html__( 'Read Documentation', 'woocommerce-booking' ); ?></a></p>
					</div>

					<div class="wc3-col">
						<h3><?php echo esc_html__( 'Get Support', 'woocommerce-booking' ); ?></h3>
						<p><?php echo esc_html__( 'Tyche Softwares support hours are from 10 am to 6 pm (GMT +5:30 timezone), Monday through Friday. Our response time is approximately 24 hours during working days.', 'woocommerce-booking' ); ?></p>
						<p><a class="link-wul" href="<?php echo esc_url( 'https://support.tychesoftwares.com/help/2285384554' ); ?>" target="_blank"><?php echo esc_html__( 'Submit a Ticket', 'woocommerce-booking' ); ?></a></p>
					</div>
				</div>
			</div>
			<?php
		}
	}
	new BKAP_Tyche();
}
