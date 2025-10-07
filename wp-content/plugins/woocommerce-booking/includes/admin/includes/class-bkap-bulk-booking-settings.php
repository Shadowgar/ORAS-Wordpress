<?php
/**
 * Bookings and Appointment Plugin for WooCommerce
 *
 * Class for handling Bulk Booking Settings
 *
 * @author   Tyche Softwares
 * @package  BKAP/Bulk-Booking-Settings
 * @category Classes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'BKAP_Bulk_Booking_Settings' ) ) {

	/**
	 * Class for Bulk Booking Settings.
	 */
	class BKAP_Bulk_Booking_Settings extends BKAP_Background_Process {

		/**
		 * Initializes the BKAP_Bulk_Booking_Settings() class.
		 *
		 * @since 5.13.0
		 */
		public static function init() {

			static $instance = false;

			if ( ! $instance ) {
				$instance = new BKAP_Bulk_Booking_Settings();
			}

			return $instance;
		}

		/**
		 * Constructor.
		 *
		 * @since 4.16.0
		 */
		public function __construct() {
			add_action( 'admin_notices', array( $this, 'background_notice_running' ) );
			add_action( 'admin_notices', array( $this, 'background_notice_completed' ) );
			add_action( 'admin_notices', array( $this, 'background_notice_error' ) );
			add_action( 'admin_init', array( $this, 'cancel_process' ) );

			parent::__construct( 'bulk_booking_settings' );
		}

		/**
		 * This function will delete the saved default booking options.
		 *
		 * @since 5.13.0
		 */
		public static function bkap_clear_defaults() {

			delete_option( 'bkap_default_booking_settings' );
			delete_option( 'bkap_default_individual_booking_settings' );
		}

		/**
		 * Adds a notice showing status for background process that are running.
		 *
		 * @since 5.13.0
		 */
		public static function background_notice_running() {

			if ( ! BKAP_Admin::is_on_bkap_page() ) {
				return;
			}

			$process_count = get_transient( 'bkap_bulk_booking_settings_background_process_running' );

			if ( false === $process_count || 0 === $process_count ) {
				return;
			}

			$running = $process_count;
			$status  = 'info';

			// translators: %1$s = Number of products that have booking setting shave been apllied to.
			$message = '<p>' . sprintf( _n( 'Bulk Booking Settings: %1$s product has been updated. (refresh to view progress)', 'Bulk Booking Settings: %1$s products have been updated. (refresh to view progress)', $running, 'woocommerce-booking' ), $running ) . ' <em> - (' . date_i18n( get_option( 'date_format' ) ) . ' @ ' . date_i18n( get_option( 'time_format' ) ) . ') </em></p>';
			$action  = '<a class="wp-core-ui button" href="' . admin_url( 'page=bkap_page&bulk_booking_settings_action=cancel_process&_wpnonce=' . wp_create_nonce( 'bulk_booking_settings_cancel_process' ) . '&action=settings#/bulk-booking-settings' ) . '">' . __( 'Stop Bulk Booking Update', 'woocommerce-booking' ) . '</a>';

			BKAP_Background_Process::display_notice(
				array(
					'status'      => $status,
					'message'     => $message,
					'dismissible' => 'notice-bulk-booking-settings-running',
					'action'      => $action,
				)
			);
		}

		/**
		 * Adds a notice showing status for background process that are running.
		 *
		 * @since 5.13.0
		 */
		public static function background_notice_completed() {

			if ( ! BKAP_Admin::is_on_bkap_page() ) {
				return;
			}

			$check = get_transient( 'bkap_bulk_booking_settings_background_process_complete' );

			if ( false === $check ) {
				return;
			}

			$check_timestamp = get_transient( 'bkap_bulk_booking_settings_background_process_complete_time' );

			if ( false === $check_timestamp ) {
				return;
			}

			delete_transient( 'bkap_bulk_booking_settings_background_process_complete' );
			delete_transient( 'bkap_bulk_booking_settings_background_process_complete_time' );

			BKAP_Background_Process::display_notice(
				array(
					// translators: %1$d is the number of products that bulk booking settings have been applied to.
					'message' => sprintf( _n( 'Bulk Booking Settings has been completed and applied to %1$d product.', 'Bulk Booking Settings have been completed and applied to %1$d products.', $check, 'woocommerce-booking' ), $check ) . ' <em> (' . $check_timestamp . ') </em>',
				)
			);
		}

		/**
		 * Adds a notice showing error messages.
		 *
		 * @since 5.13.0
		 */
		public static function background_notice_error() {

			if ( ! BKAP_Admin::is_on_bkap_page() ) {
				return;
			}

			$error_message = get_transient( 'bkap_bulk_booking_settings_background_process_error' );

			if ( false === $error_message ) {
				return;
			}

			delete_transient( 'bkap_bulk_booking_settings_background_process_error' );

			BKAP_Background_Process::display_notice(
				array(
					'status'      => 'error',
					'message'     => $error_message,
					'dismissible' => 'notice-bulk-booking-settings-running',
				)
			);
		}

		/**
		 * Process task action: Saves Bulk Booking Setting for Product.
		 *
		 * @param integer $task Array of Product Settings.
		 *
		 * @since 5.13.0
		 */
		public function process( $task ) {

			global $wpdb;

			if ( ! is_array( $task ) && ( ! isset( $task['product_id'] ) || ! isset( $task['data'] ) ) ) { // phpcs:ignore
				return false;
			}

			$product_id       = $task['product_id'];
			$product_settings = $task['data'];

			BKAP_Admin_API_Bulk_Booking_Settings::save_metabox_booking_data(
				array(
					'data'       => $product_settings,
					'product_id' => $product_id,
				)
			);

			$transient_name = 'bkap_bulk_booking_settings_background_process_running';
			$count          = get_transient( $transient_name );
			set_transient( $transient_name, $count + 1 );

			// A  little pause before we continue.
			usleep( 500000 );

			return true;
		}

		/**
		 * Runs when background process batch job is complete.
		 *
		 * @since 5.13.0
		 */
		public function complete() {
			set_transient( 'bkap_bulk_booking_settings_background_process_complete', get_transient( 'bkap_bulk_booking_settings_background_process_running' ) );
			set_transient( 'bkap_bulk_booking_settings_background_process_complete_time', date_i18n( get_option( 'date_format' ) ) . ' @ ' . date_i18n( get_option( 'time_format' ) ) );
			delete_transient( 'bkap_bulk_booking_settings_background_process_running' );

			parent::complete();
		}

		/**
		 * Dispatches the batch job. We need to exposed the protected function via this function.
		 *
		 * @since 5.13.0
		 */
		public function dispatch() {

			parent::dispatch();
		}

		/**
		 * Cancels the current process batch job.
		 *
		 * @since 5.13.0
		 */
		public function cancel_process() {

			if ( ! BKAP_Admin::is_on_bkap_page() || ! isset( $_GET['bulk_booking_settings_action'] ) || ( isset( $_GET['bulk_booking_settings_action'] ) && 'cancel_process' !== wp_unslash( $_GET['bulk_booking_settings_action'] ) ) ) { // phpcs:ignore
				return;
			}

			$redirect_url = admin_url( 'page=bkap_page&action=settings#/bulk-booking-settings' );

			if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'bulk_booking_settings_cancel_process' ) ) {
				set_transient( 'bkap_bulk_booking_settings_background_process_error', __( 'Bulk Booking Settings Process could not be cancelled due to some errors.', 'woocommerce-booking' ) );
				wp_safe_redirect( $redirect_url );
				die();
			}

			parent::cancel_process();
			delete_transient( 'bkap_bulk_booking_settings_background_process_running' );
			wp_safe_redirect( $redirect_url );
			die();
		}

		/**
		 * Create/Delete Availability for products
		 *
		 * @param array $products Array of product ids.
		 * @since 4.16.0
		 */
		public static function bkap_bulk_availability_table( $products ) {

			// The parameter is for temporary.. try to optimize it by getting only bookable products i think function is already available
			// once above thing is done then remove the comment.

			$pro_desc = __( 'Below added actions will be excecuted for the products selected in this field.', 'woocommerce-booking' );
			?>
		<div class="panel-wrap" id="bulk_execution_table">
			<div class="options_group">
				<table class="bkap-form-table form-table">
					<tr>
						<th>
							<h3><?php esc_html_e( 'Bookable products:', 'woocommerce-booking' ); ?></h3>
						</th>
						<td>
							<select id="bkap_executable_products"
									name="bkap_executable_products[]"
									placehoder="Select Products"
									class="bkap_bulk_products"
									style="width: 300px"
									multiple="multiple">
								<option value="all_bookable_products"><?php esc_html_e( 'All Products', 'woocommerce-booking' ); ?></option>
								<?php
								$productss = '';
								foreach ( $products as $bkey => $bval ) {
									$non_bookable = bkap_common::bkap_get_bookable_status( $bval->ID );
									if ( $non_bookable ) {
										$productss .= $bval->ID . ',';
										?>
										<option value="<?php echo esc_attr( $bval->ID ); ?>"><?php echo esc_html( $bval->post_title ); ?></option>
										<?php
									}
								}
								if ( '' !== $productss ) {
									$productss = substr( $productss, 0, -1 );
								}
								?>
							</select>
							<div>
								<label for="bkap_executable_products"><?php echo esc_html( $pro_desc ); ?></label>
							</div>
							<input type="hidden" id="bkap_all_bookable_products" name="custId" value="<?php echo esc_attr( $productss ); ?>">
						</td>
					</tr>
				</table>
			</div>
			<br/>
			<div>
				<table class="bkap-executable-table">
					<thead>
						<tr>
							<th style="width: 10%;"><b><?php esc_html_e( 'Day/Date', 'woocommerce-booking' ); ?></b>
								<?php echo esc_html( wc_help_tip( __( 'Select day or date option to for which you want to manage the availability.', 'woocommerce-booking' ) ) ); ?>
							</th>
							<th style="width: 28%;"><b><?php esc_html_e( 'Which Days/Dates?', 'woocommerce-booking' ); ?></b>
								<?php echo esc_html( wc_help_tip( __( 'For which day or date you want to manage the availability.', 'woocommerce-booking' ) ) ); ?>
							</th>
							<th style="width: 10%;"><b><?php esc_html_e( 'Action', 'woocommerce-booking' ); ?></b>
								<?php echo esc_html( wc_help_tip( __( 'Select the action you want to perform on the selected day/date and product. You can add, update and delete the availability of the product.', 'woocommerce-booking' ) ) ); ?>
							</th>
							<th style="width: 15%;"><b><?php esc_html_e( 'From & To time', 'woocommerce-booking' ); ?></b>
								<?php echo esc_html( wc_help_tip( __( 'Select from and to time slot value for which you want to manage the availability. This field will be applicable only if you want to manage the availability of the product which is set up with Fixed Time booking type.', 'woocommerce-booking' ) ) ); ?>
							</th>
							<th style="width: 10%;"><b><?php esc_html_e( 'Max Booking', 'woocommerce-booking' ); ?></b>
								<?php echo esc_html( wc_help_tip( __( 'Set this field if you want to place a limit on maximum bookings on any given date. If you can manage up to 15 bookings in a day, set this value to 15. Once 15 orders have been booked, then that date will not be available for further bookings. This field will be used only when availability is being added or updated.', 'woocommerce-booking' ) ) ); ?>
							</th>
							<th style="width: 8%;"><b><?php esc_html_e( 'Price', 'woocommerce-booking' ); ?></b>
								<?php echo esc_html( wc_help_tip( __( 'This field is for adding/updating the special price for selected day/date.', 'woocommerce-booking' ) ) ); ?>
							</th>
							<th style="width: 15%;"><b><?php esc_html_e( 'Note', 'woocommerce-booking' ); ?></b>
								<?php echo esc_html( wc_help_tip( __( 'This field is for adding/updating the note for selected day/date and time slot. This field will be applicable only for Fixed Time booking type.', 'woocommerce-booking' ) ) ); ?>
							</th>
							<th class="remove_bulk" width="1%">&nbsp;</th>
						</tr>
					</thead>					
					<tfoot>
						<tr>
							<th colspan="5" style="text-align: left;font-size: 11px;font-style: italic;">
								<?php esc_html_e( 'You can add, update and delete the day/date availability from here.', 'woocommerce-booking' ); ?>
							</th>   
							<th colspan="3" style="text-align: right;">
								<a href="#" class="button button-primary bkap_add_row_bulk" style="text-align: right;" data-row="
								<?php
									ob_start();
									include BKAP_BOOKINGS_TEMPLATE_PATH . 'html-bkap-bulk-booking-rules.php';
									$html = ob_get_clean();
									echo esc_attr( $html );
								?>
								"><?php esc_html_e( 'Add Action', 'woocommerce-booking' ); ?></a>
							</th>
						</tr>
						<tr>
							<th colspan="5">
								<div id='execute_booking_update_notification' style='display:none;'></div>
							</th>
							<th colspan="3" style="text-align: right;">
								<a class="button button-primary bkap_execute_row_bulk" style="text-align: right;">
									<img id="ajax_img" class="ajax_img" src="<?php echo plugins_url() . '/woocommerce-booking/assets/images/ajax-loader.gif'; // phpcs:ignore ?>">
									<?php esc_html_e( 'Execute Added Action', 'woocommerce-booking' ); ?>
								</a>
							</th>
						</tr>
					</tfoot>                    
					<tbody id="bulk_setting_availability_rows">                        
					</tbody>
				</table>
			</div>
			<?php
		}

		/**
		 * Add our Booking Meta Box on each product page
		 *
		 * @since 4.16.0
		 */
		public static function bkap_add_tab_data() {

			$bkap_version = BKAP_VERSION;

			BKAP_Scripts::bkap_load_products_css( $bkap_version );
			BKAP_Scripts::bkap_load_bkap_tab_css( $bkap_version );

			$product_id   = 0;
			$has_defaults = false;

			// phpcs:disable WordPress.Security.NonceVerification
			if ( isset( $_GET['bkap_product_id'] ) && '' !== $_GET['bkap_product_id'] ) { // to load the booking settings of provided bookable product.
				$product_id                  = intval( $_GET['bkap_product_id'] );
				$booking_settings            = bkap_setting( $product_id );
				$individual_booking_settings = array();
			} else {
				$booking_settings            = get_option( 'bkap_default_booking_settings', array() );
				$individual_booking_settings = get_option( 'bkap_default_individual_booking_settings', array() );
				$has_defaults                = ( ! empty( $individual_booking_settings ) );
			}
			// phpcs:enable WordPress.Security.NonceVerification
			$product_info = array(
				'duplicate_of'                => $product_id,
				'booking_settings'            => $booking_settings,
				'individual_booking_settings' => $individual_booking_settings,
				'has_defaults'                => $has_defaults,
				'post_type'                   => '',
			);

			bkap_booking_box_class::bkap_meta_box_template( $product_info );

			$ajax_url = get_admin_url() . 'admin-ajax.php';
			BKAP_Scripts::bkap_common_admin_scripts_js( $bkap_version );
			BKAP_Scripts::bkap_load_product_scripts_js( $bkap_version, $ajax_url, 'bulk' );
		}
	}
}
