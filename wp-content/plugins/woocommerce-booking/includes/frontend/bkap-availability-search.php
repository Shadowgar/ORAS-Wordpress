<?php
/**
 * Bookings and Appointment Plugin for WooCommerce
 *
 * Calculations and display of the available products based on the search dates
 *
 * @author      Tyche Softwares
 * @package     BKAP/Search-Widget
 * @since       1.7
 * @category    Classes
 */

if ( ! class_exists( 'Bkap_Availability_Search' ) ) {
	/**
	 * Class Bkap_Plugin_Meta.
	 *
	 * @since 5.3.0
	 */
	class Bkap_Availability_Search {

		/**
		 * Bkap_Availability_Search constructor.
		 */
		public function __construct() {
			add_action( 'widgets_init', array( $this, 'bkap_widgets_init' ) ); // Registering Booking & Appointment Availability Search Widget.
			add_filter( 'bkap_max_date', 'calback_bkap_max_date', 10, 3 );
			add_action( 'pre_get_posts', array( $this, 'bkap_generate_bookable_data' ), 20 );
			add_shortcode( 'bkap_search_widget', array( $this, 'bkap_search_widget_shortcode' ) );
			add_action( 'init', array( $this, 'bkap_set_searched_dates_in_cookies' ) );
		}

		/**
		 * This function initialize the wideget and register the same.
		 *
		 * @since 4.3
		 * @hook widgets_init
		 */
		public function bkap_widgets_init() {
			register_widget( 'Custom_WooCommerce_Widget_Product_Search' );
		}

		/**
		 * This function calculate the maximum available date in the booking calendar
		 *
		 * @since 4.4.0
		 * @hook bkap_generate_bookable_data
		 * @param object $query WP_Query Object
		 *
		 * @return object $query Return modified WP_Query Object
		 */
		public function bkap_generate_bookable_data( $query ) {

			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			if ( ! empty( $_GET['w_checkin'] ) ) {

				if ( 'product' !== $query->get( 'post_type' ) ) {
					return $query;
				}

				$start_date = sanitize_text_field( wp_unslash( $_GET['w_checkin'] ) );
				if ( ! empty( $_GET['w_checkout'] ) ) {
					$end_date = sanitize_text_field( wp_unslash( $_GET['w_checkout'] ) );
				} else {
					$end_date = sanitize_text_field( wp_unslash( $_GET['w_checkin'] ) );
				}

				if ( isset( WC()->session ) ) {
					WC()->session->set( 'start_date', $start_date );
					WC()->session->set( 'end_date', $end_date );
					if ( ! empty( $_GET['w_allow_category'] ) && $_GET['w_allow_category'] === 'on' && isset( $_GET['w_category'] ) ) {
						WC()->session->set( 'selected_category', sanitize_text_field( wp_unslash( $_GET['w_category'] ) ) );
					} else {
						WC()->session->set( 'selected_category', 'disable' );
					}

					if ( ! empty( $_GET['w_allow_resource'] ) && $_GET['w_allow_resource'] === 'on' && isset( $_GET['w_resource'] ) ) {
						WC()->session->set( 'selected_resource', sanitize_text_field( wp_unslash( $_GET['w_resource'] ) ) );
					} else {
						WC()->session->set( 'selected_resource', 'disable' );
					}
				}
			}

			if ( ! empty( $start_date ) &&
				! empty( $end_date ) &&
				$query->is_main_query()
			) {

				$query->set( 'suppress_filters', false );
				$filtered_products = array();

				// If widget has only start date then filter out all the products if its an holiday.
				if ( $start_date === $end_date ) {
					$is_global_holiday = bkap_check_holiday( $start_date, $end_date );

					if ( $is_global_holiday ) {
						$query->set( 'post__in', array( '' ) );
						return $query;
					}
				}

				if ( ! empty( $_GET['select_cat'] ) && $_GET['select_cat'] != 0 ) {

					$tax_query[] = array(
						'taxonomy' => 'product_cat',
						'field'    => 'id',
						'terms'    => array( sanitize_text_field( wp_unslash( $_GET['select_cat'] ) ) ),
						'operator' => 'IN',
					);

					$query->set( 'tax_query', $tax_query );
				}

				/* Retrive products only if it contains selected resource. */
				$meta_query = array();
				if ( ! empty( $_GET['select_res'] ) && $_GET['select_res'] != 0 ) {

					$resource_id = sanitize_text_field( wp_unslash( $_GET['select_res'] ) );
					$meta_query  = array(
						array(
							'key'     => '_bkap_resource_base_costs',
							'value'   => $resource_id,
							'compare' => 'LIKE',
						),
						array(
							'key'     => '_bkap_enable_booking',
							'value'   => 'on',
							'compare' => '=',
						),
					);

					$resource                   = new BKAP_Product_Resource( $resource_id );
					$resource_availability_data = $resource->get_resource_availability();

					$resource_availability = false;
					if ( is_array( $resource_availability_data ) && count( $resource_availability_data ) > 0 ) {
						$date_range = bkap_array_of_given_date_range( $start_date, $end_date, 'Y-m-d' );

						foreach ( $date_range as $d_range ) {
							$is_resource_available = bkap_filter_time_based_on_resource_availability( $d_range, $resource_availability_data, '00:00 - 23:59|', array( 'type' => 'fixed_time' ), $resource_id, 0, array() );
							if ( '' != $is_resource_available ) {
								$resource_availability = true;
							}
						}

						if ( ! $resource_availability ) {
							$query->set( 'post__in', array( '' ) );
							return $query;
						}
					}
				}

				$bookable_products = bkap_common::get_woocommerce_product_list( false, 'on', '', array(), $meta_query );

				$wpml_active = function_exists( 'icl_object_id' ) ? true : false;

				foreach ( $bookable_products as $pro_key => $pro_value ) {

					$product_id   = $pro_value['1'];
					$view_product = bkap_check_booking_available( $product_id, $start_date, $end_date );

					if ( $view_product ) {

						if ( $wpml_active ) {
							$product_id = icl_object_id( $product_id, 'product', true );
						}

						array_push( $filtered_products, $product_id );
					}
				}

				$filtered_products = apply_filters( 'bkap_additional_products_search_result', $filtered_products );

				if ( count( $filtered_products ) === 0 ) {
					$filtered_products = array( '' );
				}

				$query->set( 'post__in', $filtered_products );
			} elseif ( ! empty( $_GET['select_cat'] ) && $_GET['select_cat'] != 0 ) {

				if ( 'product' !== $query->get( 'post_type' ) ) {
					return $query;
				}

				if ( isset( WC()->session ) ) {
					if ( ! empty( $_GET['w_allow_category'] ) && $_GET['w_allow_category'] == 'on' && isset( $_GET['w_category'] ) ) {
						WC()->session->set( 'selected_category', sanitize_text_field( wp_unslash( $_GET['w_category'] ) ) );
					} else {
						WC()->session->set( 'selected_category', 'disable' );
					}

					if ( ! empty( $_GET['w_allow_resource'] ) && $_GET['w_allow_resource'] == 'on' && isset( $_GET['w_resource'] ) ) {
						WC()->session->set( 'selected_resource', sanitize_text_field( wp_unslash( $_GET['w_resource'] ) ) );
					} else {
						WC()->session->set( 'selected_resource', 'disable' );
					}
				}

				$tax_query[] = array(
					'taxonomy' => 'product_cat',
					'field'    => 'id',
					'terms'    => array( sanitize_text_field( wp_unslash( $_GET['select_cat'] ) ) ),
					'operator' => 'IN',
				);

				$query->set( 'tax_query', $tax_query );
			} elseif ( ! empty( $_GET['select_res'] ) && $_GET['select_res'] != 0 ) {

				if ( 'product' !== $query->get( 'post_type' ) ) {
					return $query;
				}

				if ( isset( WC()->session ) ) {
					if ( ! empty( $_GET['w_allow_category'] ) && $_GET['w_allow_category'] == 'on' && isset( $_GET['w_category'] ) ) {
						WC()->session->set( 'selected_category', sanitize_text_field( wp_unslash( $_GET['w_category'] ) ) );
					} else {
						WC()->session->set( 'selected_category', 'disable' );
					}

					if ( ! empty( $_GET['w_allow_resource'] ) && $_GET['w_allow_resource'] == 'on' && isset( $_GET['w_resource'] ) ) {
						WC()->session->set( 'selected_resource', sanitize_text_field( wp_unslash( $_GET['w_resource'] ) ) );
					} else {
						WC()->session->set( 'selected_resource', 'disable' );
					}
				}

				/* Retrive products only if it contains selected resource. */
				$meta_query = array();
				if ( ! empty( $_GET['select_res'] ) && $_GET['select_res'] != 0 ) {

					$resource_id = sanitize_text_field( wp_unslash( $_GET['select_res'] ) );
					$meta_query  = array(
						array(
							'key'     => '_bkap_resource_base_costs',
							'value'   => $resource_id,
							'compare' => 'LIKE',
						),
						array(
							'key'     => '_bkap_enable_booking',
							'value'   => 'on',
							'compare' => '=',
						),
					);
				}

				$bookable_products = bkap_common::get_woocommerce_product_list( false, 'on', '', array(), $meta_query );
				$filtered_products = array();
				foreach ( $bookable_products as $pro_key => $pro_value ) {
					$product_id          = $pro_value['1'];
					$filtered_products[] = $product_id;
				}

				$filtered_products = apply_filters( 'bkap_additional_products_search_result', $filtered_products );

				if ( count( $filtered_products ) === 0 ) {
					$filtered_products = array( '' );
				}

				$query->set( 'post__in', $filtered_products );
			}
			return $query;

			// phpcs:enable
		}

		/**
		 * This function initialize the wideget and register the same.
		 *
		 * @param array $atts Attribute Data.
		 * @since 4.3
		 * @hook bkap_search_widget
		 */
		public function bkap_search_widget_shortcode( $atts ) {

			$html = '';

			$shortcode_instance = array(
				'enable_day_search_label' => '',
				'category'                => 'no',
			);

			$shortcode_instance['start_date_label'] = isset( $atts['start_date_label'] ) ? esc_html( $atts['start_date_label'] ) : __( 'Start Date', 'woocommerce-booking' );
			$shortcode_instance['end_date_label']   = isset( $atts['end_date_label'] ) ? esc_html( $atts['end_date_label'] ) : __( 'End Date', 'woocommerce-booking' );
			$shortcode_instance['search_label']     = isset( $atts['search_label'] ) ? esc_html( $atts['search_label'] ) : __( 'Search', 'woocommerce-booking' );
			$shortcode_instance['clear_label']      = isset( $atts['clear_label'] ) ? esc_html( $atts['clear_label'] ) : __( 'Clear', 'woocommerce-booking' );
			$shortcode_instance['text_label']       = isset( $atts['text_label'] ) ? esc_html( $atts['text_label'] ) : '';
			$shortcode_instance['category_title']   = isset( $atts['category_label'] ) ? esc_html( $atts['category_label'] ) : __( 'Select Category', 'woocommerce-booking' );

			if ( isset( $atts['search_by_category'] ) && 'yes' === $atts['search_by_category'] ) {
				$shortcode_instance['category'] = 'on';
			}

			if ( isset( $atts['hide_end_date'] ) && 'yes' === $atts['hide_end_date'] ) {
				$shortcode_instance['enable_day_search_label'] = 'on';
			}

			if ( isset( $atts['resource'] ) && 'on' === $atts['resource'] ) {
				$shortcode_instance['resource'] = 'on';
			}

			if ( isset( $atts['resource_title'] ) && '' !== $atts['resource_title'] ) {
				$shortcode_instance['resource_title'] = $atts['resource_title'];
			}

			$html = Custom_WooCommerce_Widget_Product_Search::bkap_search_widget_form( $shortcode_instance, 'shortcode' );

			return $html;// nosemgrep
		}

		/**
		 * When searched for available date by guest then storing the searched dates in cookie
		 *
		 * @since 4.14.0
		 * @hook init
		 */
		public function bkap_set_searched_dates_in_cookies() {

			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			if ( ! empty( $_GET['w_checkin'] ) ) {
				unset( $_COOKIE['start_date'] );
				unset( $_COOKIE['end_date'] );

				setcookie( 'start_date', sanitize_text_field( wp_unslash( $_GET['w_checkin'] ) ), 0, '/' );

				if ( ! empty( $_GET['w_checkout'] ) ) {
					setcookie( 'end_date', sanitize_text_field( wp_unslash( $_GET['w_checkout'] ) ), 0, '/' );
				} else {
					setcookie( 'end_date', sanitize_text_field( wp_unslash( $_GET['w_checkin'] ) ), 0, '/' );
				}
			}
			// phpcs:enable
		}
	}
}
