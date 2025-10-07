<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * Booking Meta Box.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/Templates/MetaBox/Booking
 * @since       5.19.0
 */

?>

<div id="booking-metabox" class="bkap-page" :class="{'bulk-booking':'undefined' !== typeof data.is_bulk_booking}">
	<booking-metabox v-bind:data="data"></booking-metabox>
</div>
