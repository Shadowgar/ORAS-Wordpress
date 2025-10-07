<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * Import Booking.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/Views/Booking/ImportBooking
 * @since       5.19.0
 */

?>

<template id="import-booking-tab">
	<section>
		<div class="container-list-table bd-page-wrap import-booking-table">
			<div class="row">
				<div class="bkap_admin_loader" id="show_loading_loader" v-show="show_loading_loader">
					<div class="bkap_admin_loader_wrapper">
						{{data.label.loading_loader}} <img
							src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
					</div>
				</div>

				<div class="bkap_admin_loader" id="show_mapping_event_loader" v-show="show_mapping_event_loader">
					<div class="bkap_admin_loader_wrapper">
						{{data.label.mapping_event_loader}} <img
							src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
					</div>
				</div>

				<div class="col-md-12">
					<div class="bkap-page-head phw-btn">
						<div class="col-left">
                            <h1><?php esc_attr_e( 'Google Events', 'woocommerce-booking' ); // phpcs:ignore ?></h1>
                            <p><?php esc_attr_e( 'Imported events from Google Calendar.', 'woocommerce-booking' ); // phpcs:ignore ?>
						</div>
					</div>
				</div>

				<div class="col-md-12 bkap-wp-list-table">
					<div class="wbc-box">
						<div class="the-table">
							<?php
								$table = new BKAP_Admin_Import_Booking_Table();
								$table->populate_data();
								$table->prepare_items();
								$table->views();
								$table->search_box( __( 'Search Google Event', 'woocommerce-booking' ), 'search-google-event' );
								$table->display();
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
</template>
