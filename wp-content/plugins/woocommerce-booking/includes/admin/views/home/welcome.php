<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * Welcome Tab.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/Views/Home/Welcome
 * @since       5.19.0
 */

?>

<template id="welcome-tab">
<div class="container bkap-welcome-page">
	<div class="row">
		<div class="col-md-12">
			<div class="wbc-box">
				<div class="wbc-head">
					<div class="col-left">
						<img src="<?php echo esc_url( plugins_url() . '/woocommerce-booking/assets/images/welcome-logo.svg' ); ?>" alt="Image">
					</div>
					<div class="col-right">
						<h2><?php echo esc_html__( 'Welcome!', 'woocommerce-booking' ); ?></h2>
						<p><?php echo esc_html__( 'Thank you for choosing Booking & Appointment for WooCommerce plugin.', 'woocommerce-booking' ); ?></p>
					</div>
				</div>

				<div class="wbc-content">
					<div class="bkap-video-wrap">
						<embed
							src="https://www.youtube.com/embed/BzJXBJv-2k0"
							wmode="transparent"
							type="video/mp4"
							width="100%"
							height="500"
							allow="autoplay; encrypted-media; picture-in-picture"
							allowfullscreen
						>
					</div>

					<div class="bkap-wc-accordian">
						<h2 class="h1"><?php echo esc_html__( 'What do you want to do?', 'woocommerce-booking' ); ?></h2>
						<div class="panel-group bkap-accordian" id="accordion">

						<div class="panel panel-default">
								<div class="panel-heading">
									<h4 class="panel-title" data-toggle="collapse" data-target="#collapseOne" aria-expanded="false">
										<?php echo esc_html__( 'What are different types of bookings I can setup with this plugin?', 'woocommerce-booking' ); ?>
									</h4>
								</div>
								<div id="collapseOne" class="panel-collapse collapse">
									<div class="panel-body">
									<p>
										<?php
										printf(
											// Translators: %1$s is the URL for Single Day, %2$s is for Multiple Nights, %3$s is for Fixed Time, and %4$s is for Duration Based Time.
											__( 'Six types of bookings can be setup with this plugin. <br>1. <a href="%1$s">Single Day</a> <br>2. <a href="%2$s" target="_blank">Multiple Nights</a> <br>3. <a href="%3$s" target="_blank">Fixed Time</a> <br>4. <a href="%4$s" target="_blank">Duration Based Time</a> <br>5. <a href="%5$s" target="_blank">Multiple Dates</a> <br>6. <a href="%5$s" target="_blank">Multiple Dates & Time</a>.', 'woocommerce-booking' ), // phpcs:ignore.
											'https://www.tychesoftwares.com/docs/docs/booking-appointment-plugin-for-woocommerce-new/types-of-bookings/recurring-weekdays-booking/',
											'https://www.tychesoftwares.com/docs/docs/booking-appointment-plugin-for-woocommerce-new/types-of-bookings/setup-multiple-nights-booking-simple-product/',
											'https://www.tychesoftwares.com/docs/docs/booking-appointment-plugin-for-woocommerce-new/types-of-bookings/date-time-slot-booking/',
											'https://www.tychesoftwares.com/docs/docs/booking-appointment-plugin-for-woocommerce-new/types-of-bookings/duration-based-booking/',
											'https://www.tychesoftwares.com/docs/docs/booking-appointment-plugin-for-woocommerce-new/types-of-bookings/setup-multiple-dates-booking/',
											'https://www.tychesoftwares.com/docs/docs/booking-appointment-plugin-for-woocommerce-new/types-of-bookings/setup-multiple-dates-booking/'
										);
										?>
									</p>
									</div>
								</div>
							</div>

							<div class="panel panel-default">
								<div class="panel-heading">
									<h4 class="panel-title" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false">
										<?php echo esc_html__( 'How many product types is your Booking plugin compatible with?', 'woocommerce-booking' ); ?>
									</h4>
								</div>
								<div id="collapseTwo" class="panel-collapse collapse">
									<div class="panel-body">
									<p>
										<?php
										printf(
											__( 'Our Booking plugin is compatible with all default product types that come with WooCommerce. Also, we have made it compatible with <a href="%1$s" target="_blank">Bundle</a>, <a href="%2$s" target="_blank">Composite</a>, and <a href="%3$s" target="_blank">Subscriptions</a> product types.', 'woocommerce-booking' ),  // phpcs:ignore.
											'https://woocommerce.com/products/product-bundles/',
											'https://woocommerce.com/products/composite-products/',
											'https://woocommerce.com/products/woocommerce-subscriptions/'
										);
										?>
									</p>
									</div>
								</div>
							</div>

							<div class="panel panel-default">
								<div class="panel-heading">
									<h4 class="panel-title" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false">
									<?php echo esc_html__( 'Can I restrict the number of bookings for each booking date?', 'woocommerce-booking' ); ?>
									</h4>
								</div>
								<div id="collapseThree" class="panel-collapse collapse">
									<div class="panel-body">
									<p>
										<?php
											printf(
												__( 'Yes, by setting up the value in Max Bookings option you can restrict the number of bookings for each date. For Single Day and Date & Time booking type we have the <a href="%1$s" target="_blank">Max Bookings</a> option, and for multiple nights we have the <a href="%2$s" target="_blank">Maximum Bookings On Any Date</a> option in the Availability tab of the Booking meta box.', 'woocommerce-booking' ),  // phpcs:ignore.
												'https://www.tychesoftwares.com/docs/docs/booking-appointment-plugin-for-woocommerce-new/maximum-bookings/maximum-bookings-per-day-date-time-slot/',
												'https://www.tychesoftwares.com/docs/docs/booking-appointment-plugin-for-woocommerce-new/types-of-bookings/setup-multiple-nights-booking-simple-product/'
											);
											?>
									</p>
									</div>
								</div>
							</div>

							<div class="panel panel-default">
								<div class="panel-heading">
									<h4 class="panel-title" data-toggle="collapse" data-target="#collapseFour" aria-expanded="false">
										<?php echo esc_html__( 'Is it possible to allow the customer to make the booking without selecting the booking details?', 'woocommerce-booking' ); ?>
									</h4>
								</div>
								<div id="collapseFour" class="panel-collapse collapse">
									<div class="panel-body">
									<p>
										<?php
											printf(
												__( 'Yes, we have \'<a href="%s" target="_blank">Purchase without choosing a date</a>\' option in the General tab of Booking meta box which allows the customer to purchase the product without selecting the booking details.', 'woocommerce-booking' ),  // phpcs:ignore.
												esc_url( 'https://www.tychesoftwares.com/docs/docs/booking-appointment-plugin-for-woocommerce-new/allow-the-users-to-purchase-a-bookable-product-without-selecting-booking-details/' )
											);
											?>
									</p>
									</div>
								</div>
							</div>

							<div class="panel panel-default">
								<div class="panel-heading">
									<h4 class="panel-title" data-toggle="collapse" data-target="#collapseFive" aria-expanded="false">
										<?php echo esc_html__( 'Can I translate the plugin string into my native language? If yes, then how?', 'woocommerce-booking' ); ?>
									</h4>
								</div>
								<div id="collapseFive" class="panel-collapse collapse">
									<div class="panel-body">
									<p>
										<?php
											printf(
												__( 'You can use the .po file of the plugin for translating the plugin strings. Or you can <a href="%1$s" target="_blank">use WPML plugin for translating strings</a> as we have made our plugin compatible with <a href="%2$s" target="_blank">WPML plugin</a>.', 'woocommerce-booking' ),  // phpcs:ignore.
												esc_url( 'https://www.tychesoftwares.com/docs/docs/booking-appointment-plugin-for-woocommerce-new/wpml-partners/' ),
												esc_url( 'https://wpml.org/' )
											);
											?>
									</p>
									</div>
								</div>
							</div>

							<div class="panel panel-default">
								<div class="panel-heading">
									<h4 class="panel-title" data-toggle="collapse" data-target="#collapseSix" aria-expanded="false">
										<?php echo esc_html__( 'Can I set bookable products that require confirmation?', 'woocommerce-booking' ); ?>
									</h4>
								</div>
								<div id="collapseSix" class="panel-collapse collapse">
									<div class="panel-body">
									<p>
										<?php
											printf(
												__( 'Yes, by enabling \'<a href="%s" target="_blank">Requires Confirmation</a>\' option in the General tab of Booking meta box you can achieve it.', 'woocommerce-booking' ),  // phpcs:ignore.
												esc_url( 'https://www.tychesoftwares.com/docs/docs/booking-appointment-plugin-for-woocommerce-new/set-bookable-products-that-require-confirmation/' )
											);
											?>
									</p>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="bkap-wc-related">
						<h2 class="h1"><?php echo esc_html__( 'You might also like...', 'woocommerce-booking' ); ?></h2>
						<ul>
							<?php
								$plugins_and_links = apply_filters(
									'bkap_other_plugin_listings',
									array(
										'acp'  => array(
											'name' => __( 'Recover lost sales with Abandon Cart Pro for WooCommerce', 'woocommerce-booking' ),
											'link' => 'https://www.tychesoftwares.com/store/premium-plugins/woocommerce-abandoned-cart-pro/?utm_source=bkap_welcome_page&utm_medium=link&utm_campaign=BKAPPlugin',
										),
										'dfw' => array(
											'name' => __( 'Deposits For WooCommerce', 'woocommerce-booking' ),
											'link' => 'https://www.tychesoftwares.com/store/premium-plugins/deposits-for-woocommerce/?utm_source=bkap_welcome_page&utm_medium=link&utm_campaign=BKAPPlugin',
										),
										'ordd' => array(
											'name' => __( 'Order Delivery Date for WooCommerce', 'woocommerce-booking' ),
											'link' => 'https://www.tychesoftwares.com/products/woocommerce-order-delivery-date-pro-plugin//?utm_source=bkap_welcome_page&utm_medium=link&utm_campaign=BKAPPlugin',
										),
										'prdd' => array(
											'name' => __( 'Product Delivery Date Pro for WooCommerce', 'woocommerce-booking' ),
											'link' => 'https://www.tychesoftwares.com/store/premium-plugins/product-delivery-date-pro-for-woocommerce/?utm_source=bkap_welcome_page&utm_medium=link&utm_campaign=BKAPPlugin',
										),
									)
								);
								foreach ( $plugins_and_links as $key => $value ) {
									?>
							<li><a class="link-wul" href="<?php echo esc_url( $value['link'] ); ?>" target="_blank"><?php echo esc_attr( $value['name'] ); ?></a></li>
							<?php } ?>
						</ul>
					</div>

					<?php do_action( 'bkap_home_welcome_after_plugin_listing' ); ?>
					
				</div>
			</div>
		</div>
	</div>
</div>
</template>
