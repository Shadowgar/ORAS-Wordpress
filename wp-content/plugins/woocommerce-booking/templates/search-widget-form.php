<?php
/**
 * Search Widget Form
 *
 * Template for Search Widget Form. This template will show the Search Widget Form on the front end of the website
 *
 * @author      Tyche Softwares
 * @package     Bookings & Appointment Plugin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div id="wrapper" class="sbkap_table">
	<form role="search" method="get" id="searchform" action="<?php echo esc_attr( $action ); ?>">
		<input 	type="hidden"
				id="w_allow_category"
				name="w_allow_category"
				value="<?php echo esc_attr( $allow_category_search ); ?>">
		<?php echo wp_kses_post( $bkap_language ); ?>
		<input 	type="hidden"
				id="w_allow_resource"
				name="w_allow_resource"
				value="<?php echo esc_attr( $allow_resource_search ); ?>">
		<?php echo wp_kses_post( $bkap_language ); ?>
		<div class="sbkap_row">
			<div class="sbkap_cell">
				<p><?php echo esc_html( $start_date ); ?>&nbsp;</p>
			</div>
			<div class="sbkap_cell">
				<p>
					<input id="w_check_in" name="w_check_in" style="width:160px" value="<?php echo esc_attr( $start_date_value ); ?>" type="text" readonly/>
					<input type="hidden" id="w_checkin" name="w_checkin" value="<?php echo esc_attr( $session_start_date ); ?>">
				</p>
			</div>
		</div>
		<div class="sbkap_row" style= "display:<?php echo esc_attr( $hide_checkout_field ); ?>">
			<div class="sbkap_cell">
				<p><?php echo esc_attr( $end_date ); ?>&nbsp;</p>
			</div>
			<div class="sbkap_cell">
				<p>
					<input id="w_check_out" name="w_check_out" style="width:160px" value="<?php echo esc_attr( $end_date_value ); ?>" type="text"  readonly/>
					<input type="hidden" id="w_checkout" name="w_checkout" value="<?php echo esc_attr( $session_end_date ); ?>">
				</p>
			</div>
		</div>
		<div class="sbkap_row" style="display: <?php echo esc_attr( $hide_resource_filter ); ?>">
			<div class="sbkap_cell">
				<p><?php echo esc_html( $resource_label ); ?></p>
			</div>
			<div class="sbkap_cell">
				<p><?php echo $resource_contents; // phpcs:ignore ?></p>
			</div>
		</div>
		<div class="sbkap_row" style="display: <?php echo esc_attr( $hide_category_filter ); ?>">
			<div class="sbkap_cell">
				<p><?php echo esc_html( $category_label ); ?></p>
			</div>
			<div class="sbkap_cell">
				<p><?php echo $contents; // phpcs:ignore ?></p>
			</div>
		</div>
		<div class="" style= "text-align: center;">
			<div class="sbkap_cell">
				<p><input type="submit" id="bkap_search" value="<?php echo esc_attr( $search_label ); ?>" disabled="disabled" /></p>
			</div>
			<div class="sbkap_cell">
				<p><input type="button" id="bkap_clear" value="<?php echo esc_attr( $clear_label ); ?>" style="display: <?php echo esc_attr( $clear_button_css ); ?>" /></p>
			</div>
		</div>
		<?php echo wp_kses_post( $text_information ); ?>
	</form>
</div>
