<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * Admin Footer.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/Views
 * @since       5.19.0
 */

$support_url = apply_filters( 'bkap_support_url', 'https://woocommerce.com/my-account/contact-support/' );
?>
<div class="bkap-footer">
			<div class="container">
				<div class="row">
					<div class="col-md-12">
						<div class="footer-wrap">
						<div class="ft-text">
							<p>
								<a href="<?php echo esc_url( $support_url ); ?>" target="_blank">
									<?php echo esc_html__( 'Need Support?', 'woocommerce-booking' ); ?>
								</a> 
								<strong><?php echo esc_html__( 'We’re always happy to help you.', 'woocommerce-booking' ); ?></strong>
							</p>
							<?php do_action( 'bkap_footer_after_support_section' ); ?>
						</div>
						</div>
					</div>
				</div>
			</div>
		</div>
</div>
