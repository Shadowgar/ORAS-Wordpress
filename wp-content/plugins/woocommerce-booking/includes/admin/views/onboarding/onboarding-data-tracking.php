<?php
/**
 * Onboarding Template - Labels
 *
 * @package woocommerce-booking/onboarding
 */

$welcome_logo            = plugins_url() . '/woocommerce-booking/assets/images/welcome-logo-lg.svg';
$welcome_message_content = file_exists( BKAP_PLUGIN_PATH . '/includes/class-bkap-tyche.php' ) ? __( 'Want to help make Booking & Appointment Plugin for WooCommerce even more awesome? Allow Booking & Appointment Plugin for WooCommerce to collect non-sensitive diagnostic data and usage information and get 20% off on your next purchase.', 'woocommerce-booking' ) : __( 'Your Booking & Appointment Plugin for WooCommerce is now ready to power your store’s booking and appointment system. You can now start managing appointments, reservations, and rentals effortlessly. Explore the settings to fine-tune your setup as needed. Happy booking!', 'woocommerce-booking' );
$add_product             = add_query_arg(
	array(
		'post_type'  => 'product',
		'bkap_setup' => 'product',
	),
	admin_url( 'post-new.php' ),
);
$dashboard               = add_query_arg( array( 'bkap_setup' => 'dashboard' ), admin_url() );
?>
<template id="<?php echo esc_attr( apply_filters( 'bkap_onboarding_template_id', 'onboarding-finish' ) ); ?>">
<div>
	<div class="obp-content">
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

		<div class="bkap_admin_loader" v-show="show_loading_loader">
			<div class="bkap_admin_loader_wrapper">
				{{data.label.loading_loader}} <img
					src=<?php echo esc_url( trailingslashit( BKAP_IMAGE_URL ) . 'ajax-loader.gif' ); ?>>
			</div>
		</div>

		<div class="wbc-box">
			<div class="wbc-head">
				<h2><?php esc_attr_e( 'You\'re All Set!', 'woocommerce-booking' ); // phpcs:ignore  ?></h2>
			</div>
			<div class="wbc-content ex-spc">
				<div class="obp-box-1">
					<div class="col-left">
						<img src="<?php echo esc_attr( $welcome_logo ); ?>" alt="Logo">
					</div>
					<div class="col-right">
						<p class="p1">
							<?php echo esc_html( $welcome_message_content ); ?>
						</p>

						<?php do_action( 'bkap_onboarding_finish_screen_after_message' ); ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="obp-footer">
		<div class="col-left">
			<router-link :to="{name: 'labels'}"><span class="link-grey lg-w-arrow" ><?php esc_html_e( 'Back', 'woocommerce-booking' ); ?></span></router-link>
			<router-link :to="{name: 'finish'}"><span class="link-grey"><?php esc_html_e( 'Skip', 'woocommerce-booking' ); ?></span></router-link>
		</div>
		<div class="col-right">
			<a href="<?php echo esc_url( $add_product ); ?>" class="button trietary-btn reverse"><?php esc_html_e( 'Create Bookable Product', 'woocommerce-booking' ); ?></a>
			<a href="<?php echo esc_url( $dashboard ); ?>" class="button trietary-btn reverse"><?php esc_html_e( 'Finish the setup', 'woocommerce-booking' ); ?></a>
		</div>
	</div>
</div>
</template>
