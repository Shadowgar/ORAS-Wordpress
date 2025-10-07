<?php

function bkap_fluentcrm() {
	return new BKAP_FluentCRM();
}

function bkap_google_calendar_sync() {
	return BKAP_Google_Calendar_Sync::get_instance();
}

function bkap_product_google_calendar() {
	return new BKAP_Product_Google_Calendar();
}

function bkap_bulk_booking_settings() {
	return BKAP_Bulk_Booking_Settings::init();
}

function bkap_coupons() {
	return BKAP_Coupons::init();
}

function bkap_import_export_bookable_products() {
	return BKAP_Import_Export_Bookable_Products::init();
}

function bkap_product() {
	return BKAP_Product::init();
}

function bkap_wc_hpos() {
	return BKAP_Wc_Hpos::init();
}

function bkap_seasonal_pricing() {
	return new Bkap_Seasonal_Pricing();
}
