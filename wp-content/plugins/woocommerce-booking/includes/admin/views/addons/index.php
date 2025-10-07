<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * View for Addon Section.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/Views/Addon
 * @since       5.19.0
 */

BKAP_Admin_Files::load_admin_pages(
	'addons',
	array(
		'partial-deposits',
		'recurring-bookings',
		'printable-tickets',
		'seasonal-pricing',
		'vendor-options',
		'rental-system',
	)
);
