<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * Admin Header.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/Views
 * @since       5.19.0
 */

$current_action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : ''; //phpcs:ignore
?>

<div id="bkap-page" class="bkap-page">
	<div class="bkap-header">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<div class="header-wrap">
						<div class="branding">
							<a href=""><img
									src="<?php echo esc_url( plugins_url( 'woocommerce-booking/assets/images/tyche-logo.svg' ) ); ?>"
									alt="Logo" /></a>
						</div>

						<nav class="navbar navbar-expand-lg navbar-light bkap-navigation">
							<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
								aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
								<span class="navbar-toggler-icon">
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
										<path fill="none" d="M0 0h24v24H0z" />
										<path d="M3 4h18v2H3V4zm0 7h12v2H3v-2zm0 7h18v2H3v-2z"
											fill="rgba(255,255,255,1)" />
									</svg>
								</span>
							</button>

							<div class="collapse navbar-collapse" id="navbarNav">
								<ul class="navbar-nav menu nav-menu">
									<li class="nav-link<?php echo ( '' === $current_action ) ? ' active' : ''; ?>"><a
											class="nav-link" href="<?php echo 'admin.php?page=bkap_page'; ?>"><?php esc_html_e( 'Home', 'woocommerce-booking' ); ?></a>
									</li>
									<li
										class="nav-item<?php echo ( 'settings' === $current_action ) ? ' active' : ''; ?>">
										<a class="nav-link"
											href="<?php echo 'admin.php?page=bkap_page&action=settings'; ?>"><?php esc_html_e( 'Settings', 'woocommerce-booking' ); ?></a>
									</li>
									<li
										class="nav-item<?php echo ( 'appearance' === $current_action ) ? ' active' : ''; ?>">
										<a class="nav-link"
											href="<?php echo 'admin.php?page=bkap_page&action=appearance'; ?>"><?php esc_html_e( 'Appearance', 'woocommerce-booking' ); ?></a>
									</li>
									<li
										class="nav-item<?php echo ( 'integrations' === $current_action ) ? ' active' : ''; ?>">
										<a class="nav-link"
											href="<?php echo 'admin.php?page=bkap_page&action=integrations'; ?>"><?php esc_html_e( 'Integrations', 'woocommerce-booking' ); ?></a>
									</li>
									<li
										class="nav-item<?php echo ( 'addons' === $current_action ) ? ' active' : ''; ?>">
										<a class="nav-link"
											href="<?php echo 'admin.php?page=bkap_page&action=addons'; ?>"><?php esc_html_e( 'Addons', 'woocommerce-booking' ); ?></a>
									</li>
									<li
										class="nav-item<?php echo ( 'booking' === $current_action ) ? ' active' : ''; ?>">
										<a class="nav-link"
											href="<?php echo 'admin.php?page=bkap_page&action=booking'; ?>"><?php esc_html_e( 'Booking', 'woocommerce-booking' ); ?></a>
									</li>
									<li
										class="nav-item<?php echo ( 'reminders' === $current_action ) ? ' active' : ''; ?>">
										<a class="nav-link"
											href="<?php echo 'admin.php?page=bkap_page&action=reminders'; ?>"><?php esc_html_e( 'Reminders', 'woocommerce-booking' ); ?></a>
									</li>
									<li
										class="nav-item<?php echo ( 'resources' === $current_action ) ? ' active' : ''; ?>">
										<a class="nav-link"
											href="<?php echo 'admin.php?page=bkap_page&action=resources'; ?>"><?php esc_html_e( 'Resources', 'woocommerce-booking' ); ?></a>
									</li>
								</ul>
							</div>
						</nav>

						<div class="bkap-version">
                            <p class="ver-fig"><?php echo 'v' . BKAP_VERSION; // phpcs:ignore ?></p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
