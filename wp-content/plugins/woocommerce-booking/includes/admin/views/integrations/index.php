<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * View for Integration Section.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/Views/Integration
 * @since       5.19.0
 */

BKAP_Admin_Files::load_admin_pages(
	'integrations',
	array(
		'google-calendar',
		'zoom',
		'twilio',
		'fluent-crm',
		'zapier',
		'outlook-calendar',
	)
);
