<?php
/**
 * Onboarding Template - Welcome
 *
 * @package woocommerce-booking/onboarding
 */

/* translators: %s: Plugin name */
$welcome_message_heading = sprintf( __( 'Welcome to %s', 'woocommerce-booking' ), esc_html( $plugin_name ) );
$welcome_logo            = plugins_url() . '/woocommerce-booking/assets/images/welcome-logo-lg.svg';
$welcome_message_content = __( 'We\'re excited that you\'ve chosen our Booking & Appointment Plugin for WooCommerce. Get ready to transform your store into a booking powerhouse. In just a few moments, we\'ll help you configure the plugin for a tailored experience that suits your needs.', 'woocommerce-booking' );
$skip_url                = admin_url( 'admin.php?page=bkap_page&skip=true' );
?>
<template id="onboarding-welcome">
	<div>
		<div class="obp-content" id="obp-content">
			<div class="wbc-box">
				<div class="wbc-head">
					<h2><?php echo esc_html( $welcome_message_heading ); ?></h2>
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
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="obp-footer">
			<div class="col-left">
				<a class="link-grey" href="<?php echo esc_attr( $skip_url ); ?>"><?php echo esc_html__( 'Skip Setup', 'woocommerce-booking' ); ?></a>
			</div>
			<div class="col-right">
				<router-link :to="{name: 'appearance-settings'}">
					<span class="button trietary-btn reverse"><?php echo esc_html__( 'Let’s Go!', 'woocommerce-booking' ); ?></span>
				</router-link>
			</div>
		</div>
	</div>
</template>
