<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * FAQ Tab.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/Views/Home/FAQ
 * @since       5.19.0
 */

?>

<template id="faq-tab">
	<div class="container bkap-faq-page">
		<div class="row">
			<div class="col-md-12">
				<div class="wbc-box">
					<div class="wbc-head">
						<h2><?php echo esc_html__( 'Frequently Asked Questions', 'woocommerce-booking' ); ?></h2>
					</div>
					<div class="wbc-content">
						<div class="bkap-faq-wrap">
							<div class="row">
								<div class="col-md-6">
									<div class="bkap-wc-accordian">
										<div class="panel-group bkap-accordian" id="accordion">

										<div class="panel panel-default">
											<div class="panel-heading">
												<h4 class="panel-title" data-toggle="collapse" data-target="#collapse1" aria-expanded="false">
													<?php esc_html_e( 'What are different types of bookings I can setup with this plugin?', 'woocommerce-booking' ); ?>
												</h4>
											</div>
											<div id="collapse1" class="panel-collapse collapse">
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
												<h4 class="panel-title" data-toggle="collapse" data-target="#collapse2" aria-expanded="false">
													<?php esc_html_e( 'How many product types is Booking plugin compatible with?', 'woocommerce-booking' ); ?>
												</h4>
											</div>
											<div id="collapse2" class="panel-collapse collapse">
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
												<h4 class="panel-title" data-toggle="collapse" data-target="#collapse3" aria-expanded="false">
													<?php esc_html_e( 'Can I restrict the number of bookings for each booking date?', 'woocommerce-booking' ); ?>
												</h4>
											</div>
											<div id="collapse3" class="panel-collapse collapse">
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
												<h4 class="panel-title" data-toggle="collapse" data-target="#collapse4" aria-expanded="false">
													<?php esc_html_e( 'Is it possible to change the booking details during the booking process?', 'woocommerce-booking' ); ?>
												</h4>
											</div>
											<div id="collapse4" class="panel-collapse collapse">
												<div class="panel-body">
													<p>
														<?php
															printf(
																__( 'Yes, we have an Edit Bookings feature which allows editing the booking details on the Cart and Checkout page. You can enable the option from Booking-> Settings-> Global Booking Settings-> <a href="%s" target="_blank">Allow Bookings to be editable</a>.', 'woocommerce-booking' ),  // phpcs:ignore.
																'https://www.tychesoftwares.com/docs/docs/booking-appointment-plugin-for-woocommerce-new/settings/global-settings/#f-allow-bookings-to-be-editable'
															);
															?>
													</p>
												</div>
											</div>
										</div>



										<div class="panel panel-default">
											<div class="panel-heading">
												<h4 class="panel-title" data-toggle="collapse" data-target="#collapse5" aria-expanded="false">
													<?php esc_html_e( 'Is it possible to view all the bookings from a single view?', 'woocommerce-booking' ); ?>
												</h4>
											</div>
											<div id="collapse5" class="panel-collapse collapse">
												<div class="panel-body">
													<p>
														<?php
															printf(
																__( 'Yes, we have <a href="%s" target="_blank">View Bookings</a> page where one can view, search and sort the bookings.', 'woocommerce-booking' ),  // phpcs:ignore.
																esc_url( 'https://www.tychesoftwares.com/docs/docs/booking-appointment-plugin-for-woocommerce-new/booking/view-bookings/' )
															);
															?>
													</p>
												</div>
											</div>
										</div>

										<div class="panel panel-default">
											<div class="panel-heading">
												<h4 class="panel-title" data-toggle="collapse" data-target="#collapse6" aria-expanded="false">
													<?php esc_html_e( 'Does this plugin allow automatic sync of bookings with Google Calendar?', 'woocommerce-booking' ); ?>
												</h4>
											</div>
											<div id="collapse6" class="panel-collapse collapse">
												<div class="panel-body">
													<p>
														<?php
															printf(
																__( 'Yes, by setting up <a href="%1$s" target="_blank">Google API for products, you can import and export the bookings automatically to the Google Calendar</a>. Product-level settings are in the <a href="%2$s" target="_blank">Google Calendar Sync</a> tab of the Booking meta box on the Edit Product page.', 'woocommerce-booking' ),  // phpcs:ignore.
																esc_url( 'https://www.tychesoftwares.com/docs/docs/booking-appointment-plugin-for-woocommerce-new/integrations/google-calendar/' ),
																esc_url( 'https://www.tychesoftwares.com/docs/docs/booking-appointment-plugin-for-woocommerce-new/integrations/google-calendar/' )
															);
															?>
													</p>
												</div>
											</div>
										</div>

										<div class="panel panel-default">
											<div class="panel-heading">
												<h4 class="panel-title" data-toggle="collapse" data-target="#collapse7" aria-expanded="false">
													<?php esc_html_e( 'How do I create a manual booking?', 'woocommerce-booking' ); ?>
												</h4>
											</div>
											<div id="collapse7" class="panel-collapse collapse">
												<div class="panel-body">
													<p>
														<?php
															printf(
																__( 'You can <a href="%s" target="_blank">create a manual booking</a> from Booking -> Create Booking page. While creating the booking, you can create a new order for the booking or you can add the booking to an already existing order.', 'woocommerce-booking' ),  // phpcs:ignore.
																esc_url( 'https://www.tychesoftwares.com/docs/docs/booking-appointment-plugin-for-woocommerce-new/booking/create-booking/' )
															);
															?>
													</p>
												</div>
											</div>
										</div>


										<div class="panel panel-default">
											<div class="panel-heading">
												<h4 class="panel-title" data-toggle="collapse" data-target="#collapse8" aria-expanded="false">
													<?php esc_html_e( 'Is it possible to allow the customer to make the booking without selecting the booking details?', 'woocommerce-booking' ); ?>
												</h4>
											</div>
											<div id="collapse8" class="panel-collapse collapse">
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
												<h4 class="panel-title" data-toggle="collapse" data-target="#collapse9" aria-expanded="false">
													<?php esc_html_e( 'Can I translate the plugin string into my native language? If yes, then how?', 'woocommerce-booking' ); ?>
												</h4>
											</div>
											<div id="collapse9" class="panel-collapse collapse">
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
												<h4 class="panel-title" data-toggle="collapse" data-target="#collapse10" aria-expanded="false">
													<?php esc_html_e( 'Can I set bookable products that require confirmation?', 'woocommerce-booking' ); ?>
												</h4>
											</div>
											<div id="collapse10" class="panel-collapse collapse">
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

										<div class="panel panel-default">
											<div class="panel-heading">
												<h4 class="panel-title" data-toggle="collapse" data-target="#collapse11" aria-expanded="false">
													<?php esc_html_e( 'Do you have a list of payment gateways which are compatible with this plugin?', 'woocommerce-booking' ); ?>
												</h4>
											</div>
											<div id="collapse11" class="panel-collapse collapse">
												<div class="panel-body">
													<p>
														<?php esc_html_e( 'We do not interfere with the payment gateways. So all the payment gateways that work fine with WooCommerce will work fine with this plugin as well.', 'woocommerce-booking' );  // phpcs:ignore. ?> 
													</p>
												</div>
											</div>
										</div>


										<div class="panel panel-default">
											<div class="panel-heading">
												<h4 class="panel-title" data-toggle="collapse" data-target="#collapse12" aria-expanded="false">
													<?php esc_html_e( 'Can I exclude the weekends for bookings as we do not take bookings on weekends?', 'woocommerce-booking' ); ?>
												</h4>
											</div>
											<div id="collapse12" class="panel-collapse collapse">
												<div class="panel-body">
													<p>
														<?php esc_html_e( 'Yes, you can exclude the weekends by disabling Saturday & Sunday (or any weekdays) in the Weekdays table in the Availability tab of our Booking meta box.', 'woocommerce-booking' );  // phpcs:ignore. ?>
													</p>
												</div>
											</div>
										</div>

										<div class="panel panel-default">
											<div class="panel-heading">
												<h4 class="panel-title" data-toggle="collapse" data-target="#collapse13" aria-expanded="false">
													<?php esc_html_e( 'Is it possible to always display the Booking calendar on the front end product page?', 'woocommerce-booking' ); ?>
												</h4>
											</div>
											<div id="collapse13" class="panel-collapse collapse">
												<div class="panel-body">
													<p>
														<?php esc_html_e( 'Yes, by enabling the “Enable Inline Calendar” option in the General tab of the Booking meta box, the Booking calendar will always be visible.', 'woocommerce-booking' );  // phpcs:ignore. ?>
													</p>
												</div>
											</div>
										</div>

										<div class="panel panel-default">
											<div class="panel-heading">
												<h4 class="panel-title" data-toggle="collapse" data-target="#collapse14" aria-expanded="false">
													<?php esc_html_e( 'Is your plugin compatible with WPML as I wanted my site to be available in multiple languages?', 'woocommerce-booking' ); ?>
												</h4>
											</div>
											<div id="collapse14" class="panel-collapse collapse">
												<div class="panel-body">
													<p>
														<?php esc_html_e( 'The plugin is fully compatible with WPML. We do have a certificate of compatibility from WPML.', 'woocommerce-booking' );  // phpcs:ignore. ?>
													</p>
												</div>
											</div>
										</div>

										<div class="panel panel-default">
											<div class="panel-heading">
												<h4 class="panel-title" data-toggle="collapse" data-target="#collapse15" aria-expanded="false">
													<?php esc_html_e( 'Are customers allowed to reschedule the booking after the order is placed?', 'woocommerce-booking' ); ?>
												</h4>
											</div>
											<div id="collapse15" class="panel-collapse collapse">
												<div class="panel-body">
													<p>
														<?php esc_html_e( 'Yes, we have a Reschedule Bookings feature which allows rescheduling of the bookings from the My Account page.', 'woocommerce-booking' );  // phpcs:ignore. ?>
													</p>
												</div>
											</div>
										</div>

										</div>
									</div>
								</div>

								<div class="col-md-6">
									<div class="bkap-wc-accordian">
										<div class="panel-group bkap-accordian" id="accordion">
										<div class="panel panel-default">
											<div class="panel-heading">
												<h4 class="panel-title" data-toggle="collapse" data-target="#collapse16" aria-expanded="false">
													<?php esc_html_e( 'Can I set labels for booking fields?', 'woocommerce-booking' ); ?>
												</h4>
											</div>
											<div id="collapse16" class="panel-collapse collapse">
												<div class="panel-body">
													<p>
														<?php esc_html_e( 'You can set booking fields labels as per your business requirements from Booking -> Labels & Messages page.', 'woocommerce-booking' );  // phpcs:ignore. ?>
													</p>
												</div>
											</div>
										</div>

										<div class="panel panel-default">
											<div class="panel-heading">
												<h4 class="panel-title" data-toggle="collapse" data-target="#collapse17" aria-expanded="false">
													<?php esc_html_e( 'How to force the customer to select minimum numbers of nights for booking the product?', 'woocommerce-booking' ); ?>
												</h4>
											</div>
											<div id="collapse17" class="panel-collapse collapse">
												<div class="panel-body">
													<p>
														<?php
															printf(
																__( 'You can set it for all the bookable products by setting a value to “Minimum number of days to choose” on the Booking -> Global Booking Settings page. Also, you can enable minimum numbers of nights for a particular product by setting a value to “Minimum number of nights to book” option in the Availability tab of the Booking metabox.', 'woocommerce-booking' )  // phpcs:ignore.
															);
															?>
													</p>
												</div>
											</div>
										</div>

										<div class="panel panel-default">
											<div class="panel-heading">
												<h4 class="panel-title" data-toggle="collapse" data-target="#collapse18" aria-expanded="false">
													<?php esc_html_e( 'How to take bookings for a fixed number of days?', 'woocommerce-booking' ); ?>
												</h4>
											</div>
											<div id="collapse18" class="panel-collapse collapse">
												<div class="panel-body">
													<p>
														<?php esc_html_e( 'We have a Fixed Blocks Booking feature which allows you to set up fixed blocks for booking the product. On the front end of the product, customers have to choose the required fixed block, and upon selecting the start date, the end date will get automatically selected.', 'woocommerce-booking' );  // phpcs:ignore. ?>
													</p>
												</div>
											</div>
										</div>

										<div class="panel panel-default">
											<div class="panel-heading">
												<h4 class="panel-title" data-toggle="collapse" data-target="#collapse19" aria-expanded="false">
													<?php esc_html_e( 'Is it possible to set the price of booking based on the ranges?', 'woocommerce-booking' ); ?>
												</h4>
											</div>
											<div id="collapse19" class="panel-collapse collapse">
												<div class="panel-body">
													<p>
														<?php esc_html_e( 'Yes. We have a “Price By Range Of Nights” feature which allows store owners to create ranges for the product. Range price can be set on a per-day basis or as a fixed price.', 'woocommerce-booking' );  // phpcs:ignore. ?>
													</p>
												</div>
											</div>
										</div>


										<div class="panel panel-default">
											<div class="panel-heading">
												<h4 class="panel-title" data-toggle="collapse" data-target="#collapse20" aria-expanded="false">
													<?php esc_html_e( 'Can I print or get CSV of all the bookings?', 'woocommerce-booking' ); ?>
												</h4>
											</div>
											<div id="collapse20" class="panel-collapse collapse">
												<div class="panel-body">
													<p>
														<?php esc_html_e( 'Yes, on View Bookings page, we provide two buttons, one for printing the bookings and another for downloading the CSV of the bookings.', 'woocommerce-booking' );  // phpcs:ignore. ?>
													</p>
												</div>
											</div>
										</div>

										<div class="panel panel-default">
											<div class="panel-heading">
												<h4 class="panel-title" data-toggle="collapse" data-target="#collapse21" aria-expanded="false">
													<?php esc_html_e( 'Is Calendar View of all the bookings available in this plugin?', 'woocommerce-booking' ); ?>
												</h4>
											</div>
											<div id="collapse21" class="panel-collapse collapse">
												<div class="panel-body">
													<p>
														<?php
															printf(
																__( 'Yes, we have a <a href="%s" target="_blank">Calendar View</a> button on the Booking -> View Bookings page. Click on it, and you will be redirected to a page that shows the calendar view of all the bookings.', 'woocommerce-booking' ),  // phpcs:ignore.
																admin_url( '/admin.php?page=bkap_page&action=booking#/booking-calendar' ) // phpcs:ignore
															);
															?>
													</p>
												</div>
											</div>
										</div>

										<div class="panel panel-default">
											<div class="panel-heading">
												<h4 class="panel-title" data-toggle="collapse" data-target="#collapse22" aria-expanded="false">
													<?php esc_html_e( 'Is it possible to add a special price on weekends or on some specific dates?', 'woocommerce-booking' ); ?>
												</h4>
											</div>
											<div id="collapse22" class="panel-collapse collapse">
												<div class="panel-body">
													<p>
														<?php esc_html_e( 'Yes, one can set special prices for required weekdays as well as for specific dates.', 'woocommerce-booking' );  // phpcs:ignore. ?>
													</p>
												</div>
											</div>
										</div>

										<div class="panel panel-default">
											<div class="panel-heading">
												<h4 class="panel-title" data-toggle="collapse" data-target="#collapse23" aria-expanded="false">
													<?php esc_html_e( 'Is this plugin integrated with any of the multi-vendor marketplace plugins?', 'woocommerce-booking' );  // phpcs:ignore. ?>
												</h4>
											</div>
											<div id="collapse23" class="panel-collapse collapse">
												<div class="panel-body">
													<p>
														<?php esc_html_e( 'Yes, this plugin is compatible with Dokan Pro, WC Vendors Pro, and WCFM Marketplace plugins.', 'woocommerce-booking' );  // phpcs:ignore. ?>
													</p>
												</div>
											</div>
										</div>

										<div class="panel panel-default">
											<div class="panel-heading">
												<h4 class="panel-title" data-toggle="collapse" data-target="#collapse24" aria-expanded="false">
													<?php esc_html_e( 'Can I set up resources with this plugin?', 'woocommerce-booking' ); ?>
												</h4>
											</div>
											<div id="collapse24" class="panel-collapse collapse">
												<div class="panel-body">
													<p>
														<?php
															printf(
																__( 'Yes, we have a Resources feature in this plugin. You can create and manage resources from the Booking -> Resources page. You can set the resource by enabling the “Booking Resource” option and adding the required resource settings in the Resource tab of the Booking meta box.', 'woocommerce-booking' )  // phpcs:ignore.
															);
															?>
													</p>
												</div>
											</div>
										</div>


										<div class="panel panel-default">
											<div class="panel-heading">
												<h4 class="panel-title" data-toggle="collapse" data-target="#collapse25" aria-expanded="false">
													<?php esc_html_e( 'Is it possible to take partial payments for the bookings?', 'woocommerce-booking' ); ?>
												</h4>
											</div>
											<div id="collapse25" class="panel-collapse collapse">
												<div class="panel-body">
													<p>
														<?php esc_html_e( 'No, but we have Partial Deposits Addon which allows taking the partial payment for booking the product.', 'woocommerce-booking' );  // phpcs:ignore. ?>
													</p>
												</div>
											</div>
										</div>

										<div class="panel panel-default">
											<div class="panel-heading">
												<h4 class="panel-title" data-toggle="collapse" data-target="#collapse26" aria-expanded="false">
													<?php esc_html_e( 'Can I increase booking price in season period?', 'woocommerce-booking' ); ?>
												</h4>
											</div>
											<div id="collapse26" class="panel-collapse collapse">
												<div class="panel-body">
													<p>
														<?php esc_html_e( 'No, to do that you have to use Seasonal Pricing Addon which allows to increase/decrease the booking price for certain periods relative to the base price.', 'woocommerce-booking' );  // phpcs:ignore. ?>
													</p>
												</div>
											</div>
										</div>

										<div class="panel panel-default">
											<div class="panel-heading">
												<h4 class="panel-title" data-toggle="collapse" data-target="#collapse28" aria-expanded="false">
													<?php esc_html_e( 'Is it possible to show the bookings availability calendar on the frontend?', 'woocommerce-booking' ); ?>
												</h4>
											</div>
											<div id="collapse28" class="panel-collapse collapse">
												<div class="panel-body">
													<p>
														<?php esc_html_e( 'Yes, it is possible to achieve this with the Available Bookings Block feature of our Booking plugin. This feature allows listing all or specific products along with their booking availability on any page on the frontend in a Calendar view or as a List.', 'woocommerce-booking' );  // phpcs:ignore. ?>
													</p>
												</div>
											</div>
										</div>

										<div class="panel panel-default">
											<div class="panel-heading">
												<h4 class="panel-title" data-toggle="collapse" data-target="#collapse29" aria-expanded="false">
													<?php esc_html_e( 'Is there a way to remind customers of their bookings?', 'woocommerce-booking' ); ?>
												</h4>
											</div>
											<div id="collapse29" class="panel-collapse collapse">
												<div class="panel-body">
													<p>
														<?php esc_html_e( 'Yes, there is an option in the plugin to send an automatic as well as manual reminder email to the customer X days before the booking date. You can set up this under the Booking -> Send Reminder section in the backend.', 'woocommerce-booking' );  // phpcs:ignore. ?>
													</p>
												</div>
											</div>
										</div>

										<div class="panel panel-default">
											<div class="panel-heading">
												<h4 class="panel-title" data-toggle="collapse" data-target="#collapse30" aria-expanded="false">
													<?php esc_html_e( 'Can I sync the booking information with any external applications?', 'woocommerce-booking' ); ?>
												</h4>
											</div>
											<div id="collapse30" class="panel-collapse collapse">
												<div class="panel-body">
													<p>
														<?php esc_html_e( 'Yes, you can sync the booking information with external applications such as Google Calendar, Outlook Calendar, and Zoom. This list will keep growing.', 'woocommerce-booking' );  // phpcs:ignore. ?>
													</p>
												</div>
											</div>
										</div>

										<div class="panel panel-default">
											<div class="panel-heading">
												<h4 class="panel-title" data-toggle="collapse" data-target="#collapse31" aria-expanded="false">
													<?php esc_html_e( 'Does your plugin comply with the GDPR guidelines?', 'woocommerce-booking' ); ?>
												</h4>
											</div>
											<div id="collapse31" class="panel-collapse collapse">
												<div class="panel-body">
													<p>
														<?php esc_html_e( 'We have made every effort to make our plugin compliant with the GDPR guidelines. As a part of compliance, we do not capture any data of end users and customers without consent.', 'woocommerce-booking' );  // phpcs:ignore. ?>
													</p>
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
	</div>
</template>
