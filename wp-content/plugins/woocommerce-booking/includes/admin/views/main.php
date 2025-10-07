<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * Main Menu.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/Views
 * @since       5.19.0
 */
?>
<div class="bkap-content-area" id="secondary-nav-wrap" v-cloak>
	<div class="container cw-full secondary-nav">
		<div class="row">
			<div class="col-md-12">
				<div class="secondary-nav-wrap">
					<ul>
						<li v-for="tab in tabs"
							v-bind:key="tab.id"
							v-bind:class="{ 'current-menu-item': currentTabId === tab.id, [tab.id]: true }"
							v-on:click="currentTabId = tab.id"> 
							<router-link :to="{name: tab.id }">{{ tab.text }} </router-link>
						</li>

					</ul>
				</div>
			</div>
		</div>
	</div>

	<router-view></router-view>
</div>
