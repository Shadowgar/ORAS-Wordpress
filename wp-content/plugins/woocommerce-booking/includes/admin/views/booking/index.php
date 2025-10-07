<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * View for Bookings Section.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/Views/Bookings
 * @since       5.19.0
 */

BKAP_Admin_Files::load_admin_pages(
	'booking',
	array(
		'create-booking',
		'view-bookings',
		'booking-calendar',
		'import-booking',
	)
);
