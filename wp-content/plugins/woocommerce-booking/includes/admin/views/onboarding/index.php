<?php
/**
 * Onboarding
 *
 * @package woocommerce-booking/onboarding
 */

$onboarding_logo = plugins_url() . '/woocommerce-booking/assets/images/onboarding-logo.svg';
$plugin_name     = __( 'Booking & Appointment Plugin for WooCommerce', 'woocommerce-booking' );

$onboarding_templates = array(
	'welcome',
	'appearance-settings',
	'booking-settings',
	'edit-reschedule',
	'labels',
	'data-tracking',
);

foreach ( $onboarding_templates as $key => $value ) {
	require __DIR__ . '/onboarding-' . $value . '.php';
}

?>
<div id="bkap-page" class="bkap-page">
	<div class="onboarding-page">
		<div class="container">
			<div class="obp-header">
				<div class="col-left">
					<img src="<?php echo esc_attr( $onboarding_logo ); ?>" alt="Tyche Softwares">
				</div>
				<div class="col-right">
					<h1><?php echo esc_html( $plugin_name ); ?></h1>
				</div>
			</div>
			<router-view></router-view>
		</div>
	</div>
</div>