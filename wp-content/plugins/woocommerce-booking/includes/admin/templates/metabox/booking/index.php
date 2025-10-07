<template id="booking-metabox-template">
	<section>
		<div class="bd-page-wrap">
			<div class="row mr-0 ml-0">
				<div class="col-md-12 pr-0 pl-0">
					<div class="wbc-box no-background">
						<div class="wbc-content">
							<div class="tbl-mod-1">
								<div class="tm1-row p-0">
									<div class="col-left col-left-sidebar">
										<?php BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/templates/metabox/booking/sidebar.php' ); ?>
									</div>
									<div class="col-right col-right-sidebar">
										<?php BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/templates/metabox/booking/general.php' ); ?>
										<?php BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/templates/metabox/booking/availability.php' ); ?>
										<?php BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/templates/metabox/booking/integrations.php' ); ?>
										<?php BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/templates/metabox/booking/block_pricing.php' ); ?>
										<?php BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/templates/metabox/booking/resources.php' ); ?>
										<?php BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/templates/metabox/booking/persons.php' ); ?>
										<?php BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/templates/metabox/booking/rental.php' ); ?>
										<?php BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/templates/metabox/booking/partial_payments.php' ); ?>
										<?php BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/templates/metabox/booking/seasonal_pricing.php' ); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
</template>
