<?php
/**
 * Booking & Appointment Plugin for WooCommerce
 *
 * Class to filter the bookable products on the All products page at the Admin.
 *
 * @author      Tyche Softwares
 * @package     BKAP/BKAP-Product-filter
 * @category    Classes
 */

if ( ! class_exists( 'BKAP_Product_Filter' ) ) {

	/**
	 * Class BKAP_Product_Filter
	 */
	class BKAP_Product_Filter {

		/**
		 * Default constructor
		 *
		 * @since 4.11.0
		 */
		public function __construct() {

			add_action( 'restrict_manage_posts', array( $this, 'bkap_custom_product_filters' ) );
			add_action( 'pre_get_posts', array( $this, 'bkap_custom_bookable_filters' ) );
		}

		/**
		 * This function will filter the bookable product with the booking type on the All products page.
		 *
		 * @param string $post_type type of post.
		 * @since 4.11.0
		 *
		 * @hook restrict_manage_posts
		 */
		public function bkap_custom_product_filters( $post_type ) {

			if ( $post_type == 'product' ) {

				$booking_filter_data = array(
					''                     => __( 'Filter by bookable products', 'woocommerce-booking' ),
					'non_bookable'         => __( 'Non-Bookable Products', 'woocommerce-booking' ),
					'bookable'             => __( 'Bookable Products', 'woocommerce-booking' ),
					'only_day'             => __( 'Single Day', 'woocommerce-booking' ),
					'multiple_days'        => __( 'Multiple Nights', 'woocommerce-booking' ),
					'date_time'            => __( 'Fixed Time', 'woocommerce-booking' ),
					'duration_time'        => __( 'Duration Based Time', 'woocommerce-booking' ),
					'multidates'           => __( 'Multiple Dates', 'woocommerce-booking' ),
					'multidates_fixedtime' => __( 'Multiple Dates & Time', 'woocommerce-booking' ),
				);

				$bookable_filter = isset( $_GET['bookable_filter'] ) ? sanitize_text_field( wp_unslash( $_GET['bookable_filter'] ) ) : ''; // phpcs:ignore

				echo '<select name="bookable_filter">';
				foreach ( $booking_filter_data as $key => $value ) {

					$selected = ( $bookable_filter === $key ) ? ' selected="selected"' : '';
					echo '<option value="' . esc_attr( $key ) . '" ' . esc_attr( $selected ) . '>' . esc_html( $value ) . '</option>';
				}
				echo '</select>';
			}
		}

		/**
		 * This function will filter the bookable product with the booking type on the All products page.
		 *
		 * @global string $pagenow current admin page.
		 * @param  object $query WP_Query Object.
		 *
		 * @since 4.11.0
		 *
		 * @hook pre_get_posts
		 */
		public function bkap_custom_bookable_filters( $query ) {

			global $pagenow;

			// Ensure it is an edit.php admin page, the filter exists and has a value, and that it's the products page.
			if ( $query->is_admin && $pagenow == 'edit.php' && isset( $query->query_vars ) && $query->query_vars['post_type'] == 'product' ) {

				if ( isset( $_GET['bookable_filter'] ) ) {  // phpcs:ignore
					$booking_filter = sanitize_text_field( wp_unslash( $_GET['bookable_filter'] ) ); // phpcs:ignore

					switch ( $booking_filter ) {
						case 'bookable':
						case 'non_bookable':
							$bookable = 'on';
							if ( 'non_bookable' === $booking_filter ) {
								$bookable = '';
							}
							$meta_key_query                  = array(
								array(
									'key'   => '_bkap_enable_booking',
									'value' => $bookable,
								),
							);
							$query->query_vars['meta_query'] = $meta_key_query; // phpcs:ignore
							break;
						case 'only_day':
						case 'multiple_days':
						case 'date_time':
						case 'duration_time':
						case 'multidates':
						case 'multidates_fixedtime':
							$meta_key_query                  = array(
								array(
									'key'   => '_bkap_booking_type',
									'value' => $booking_filter,
								),
							);
							$query->query_vars['meta_query'] = $meta_key_query; // phpcs:ignore
							break;
						default:
							break;
					}
				}

				// Show products that has resource assigned.
				if ( isset( $_GET['bkap_resource_id'] ) ) { // phpcs:ignore
					$resource_id                     = sanitize_text_field( wp_unslash( $_GET['bkap_resource_id'] ) );  // phpcs:ignore
					$resource_id                     = ':' . $resource_id . ';';
					$meta_key_query                  = array(
						array(
							'key'     => '_bkap_resource_base_costs',
							'value'   => $resource_id,
							'compare' => 'LIKE',
						),
					);
					$query->query_vars['meta_query'] = $meta_key_query;  // phpcs:ignore
				}
			}
		}
	}
}
