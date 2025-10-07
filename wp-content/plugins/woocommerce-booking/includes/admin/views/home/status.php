<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * Plugin Status.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/Views/Home/Status
 * @since       5.19.0
 */

?>
<template id="status-tab">
   <section>
	  <div class="container fas-page-wrap">
		 <div class="row">

			<div class="col-md-12">
				<div class="bkap-page-head phw-btn">
					<div class="col-left">
						<h1><?php esc_attr_e( 'System Status', 'woocommerce-booking' ); // phpcs:ignore ?></h1>
						<p><?php esc_attr_e( 'Please provide this system status information in your ticket when contacting support.', 'woocommerce-booking' ); // phpcs:ignore ?></p>
					</div>
					<div class="col-right">
						<button type="button" class="bkap-button" v-on:click.stop="copy_settings">Copy Settings</button>
					</div>
				</div>
			</div>

			<div class="col-md-12">
			   <div class="wbc-accordion">
				  <div class="panel-group bkap-accordian" id="wbc-accordion">
					 <div class="panel panel-default">
						<div class="panel-heading">
						   <h2 class="panel-title" data-toggle="collapse" data-target="#collapseOne" aria-expanded="false">
							  <?php esc_attr_e( 'WordPress Environment', 'woocommerce-booking' ); // phpcs:ignore  ?>
						   </h2>
						</div>
						<div id="collapseOne" class="panel-collapse collapse show">
						   <div class="panel-body">
							  <div class="tbl-mod-1">

								 <div class="tm1-row" v-for="(value,name) in data.wordpress_environment">
									<div class="col-left">
									   <label>{{name}}:</label>
									</div>
									<div class="col-right">
									   <div class="rc-flx-wrap flx-aln-center">
										  <p>{{value}}</p>
									   </div>
									</div>
								 </div>
							  </div>
						   </div>
						</div>
					 </div>

					 <div class="panel panel-default">
						<div class="panel-heading">
						   <h2 class="panel-title" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false">
						   <?php esc_attr_e( 'Plugin Settings', 'woocommerce-booking' ); // phpcs:ignore  ?>
						   </h2>
						</div>
						<div id="collapseTwo" class="panel-collapse collapse show">
						   <div class="panel-body">
							  <div class="tbl-mod-1">
								 
							  <div class="tm1-row" v-for="(value,name) in data.plugin_settings">
									<div class="col-left">
									   <label>{{name}}:</label>
									</div>
									<div class="col-right">
									   <div class="rc-flx-wrap flx-aln-center">
										  <p>{{value}}</p>
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

			<textarea id="status_textbox_data" v-show="show_status_textbox" v-html="data.export_data"></textarea>
		 </div>
	  </div>
   </section>
</template>
