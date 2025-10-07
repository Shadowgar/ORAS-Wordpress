<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * View for Settings Section.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/Views/Settings
 * @since       5.19.0
 */

BKAP_Admin_Files::load_admin_pages(
	'settings',
	array(
		'global-settings',
		'bulk-booking-settings',
		'product-availability-settings',
		'vendor-options',
	)
);
