<?php
/**
 * Show bookings on the WordPress Dashboard Widget
 *
 * @since 4.12.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'BKAP_Dashboard_Widget' ) ) {

	/**
	 * class BKAP_Dashboard_Widget
	 */
	class BKAP_Dashboard_Widget {

		/**
		 * Constructor
		 *
		 * @since 4.12.0
		 */
		public function __construct() {
			add_action( 'wp_dashboard_setup', array( &$this, 'bkap_add_booking_dashboard_widgets' ) );
		}

		/**
		 * Function to add the dashboard widget.
		 *
		 * @since 4.12.0
		 * @hook wp_dashboard_setup
		 */

		function bkap_add_booking_dashboard_widgets() {
			// we defining a function to hook to the wp_dashboard_setup action.
			wp_add_dashboard_widget(
				'bkap_booking_widget_id',
				__( 'Bookings', 'woocommerce-booking' ),
				array( $this, 'bkap_dashboard_widget_function' )
			);
		}

		/**
		 * Function to display widget content.
		 *
		 * @since 4.12.0
		 */

		function bkap_dashboard_widget_function() {

			$args = apply_filters(
				'bkap_booking_dashboard_widget_args',
				array(
					'post_type'      => 'bkap_booking',
					'post_status'    => 'All',
					'posts_per_page' => 10,
				)
			);

			$bookings = get_posts( $args );

			$top_five_booking = array();

			if ( count( $bookings ) > 0 ) {
				$i = 1;

				foreach ( $bookings as $key => $value ) {

					$top_five_booking[] = $value->ID;

					$booking_post = new BKAP_Booking( $value->ID );

					echo '<b>' . esc_html( $i ) . '.</b> ';
					printf(
						'<a href="%s" target="_blank">%s</a>',
						esc_url( admin_url( 'post.php?post=' . $value->ID . '&action=edit' ) ),
						/* Translators: %d Booking ID */
						sprintf( esc_html__( 'Booking #%d', 'woocommerce-booking' ), intval( $value->ID ) )
					);
					echo ' | ';

					$order = $booking_post->get_order();
					if ( $order ) {
						$order_url = bkap_order_url( $order->get_id() );
						printf(
							'<a href="%s" target="_blank">%s</a> - %s',
							esc_url( $order_url ),
							/* Translators: %d Order ID */
							esc_html( sprintf( __( 'Order #%s', 'woocommerce-booking' ), $order->get_order_number() ) ),
							esc_html( wc_get_order_status_name( $order->get_status() ) )
						);
					} else {
						echo '-';
					}
					echo '<br><i><b>';
					esc_html_e( 'Booking Starts On: ', 'woocommerce-booking' );
					echo '</b></i>';
					echo esc_html( $booking_post->get_start_date() . ' - ' . $booking_post->get_start_time() );

					if ( $booking_post->get_end_date() != '' ) {
						echo '<br>';
						echo '<i><b>';
						echo esc_html__( 'Booking Ends On: ', 'woocommerce-booking' );
						echo '</b></i>';
						echo esc_html( $booking_post->get_end_date() . ' - ' . $booking_post->get_end_time() );
					}

					echo '<br><hr>';
					$i++;
				}

				echo '<div style="text-align:right;margin-top:20px;font-size:large;">
			    		<span class="dashicons dashicons-calendar-alt"></span> ';

				printf( '<a href="%1$s" target="_blank">%2$s</a>', esc_url( admin_url( 'edit.php?post_type=bkap_booking' ) ), esc_html__( 'View all bookings', 'woocommerce-booking' ) );

				echo '</div>';
			} else {
				esc_html_e( 'No bookings.', 'woocommerce-booking' );
			}
		}
	}
}
