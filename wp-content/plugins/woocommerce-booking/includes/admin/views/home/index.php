<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * View for Home Section.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/Views/Home
 * @since       5.19.0
 */

BKAP_Admin_Files::load_admin_pages(
	'home',
	apply_filters(
		'bkap_home_files',
		array(
			'welcome',
			'faq',
			'status',
		)
	)
);
