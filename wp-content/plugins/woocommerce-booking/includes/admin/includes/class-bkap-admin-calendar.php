<?php
/**
 *  Bookings and Appointment Plugin for WooCommerce.
 *
 * Class for Booking Calendar.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/Calendar
 * @category    Classes
 * @since       5.19.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * BKAP Calendar class.
 *
 * @since 5.12.0
 */
class BKAP_Admin_Calendar {

	/**
	 * Constructor.
	 *
	 * @since 5.9.1
	 */
	public function __construct() {
		add_action( 'wp_loaded', array( $this, 'bkap_calendar_view_events' ) );
		add_action( 'wp_ajax_nopriv_bkap_booking_calender_content', array( __CLASS__, 'bkap_booking_calender_content' ) );
		add_action( 'wp_ajax_bkap_booking_calender_content', array( __CLASS__, 'bkap_booking_calender_content' ) );
	}

	/**
	 * Preparing the Booking Events Data.
	 *
	 * @since 5.9.1
	 */
	public static function bkap_calendar_view_events() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( empty( $_REQUEST['start'] ) && empty( $_REQUEST['end'] ) ) {
			return;
		}

		if ( ! empty( $_REQUEST['bkap_events_feed'] ) ) {
			return;
		}

		if ( ! isset( $_GET['bkapc_events'] ) ) {
			return;
		}

		global $wpdb;

		$start = isset( $_REQUEST['start'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['start'] ) ) : '';
		$end   = isset( $_REQUEST['end'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['end'] ) ) : '';

		$start_date = gmdate( 'YmdHis', strtotime( $start ) );
		$end_date   = gmdate( 'YmdHis', strtotime( $end ) );

		$booking_args = apply_filters(
			'bkap_calendar_view_events_args',
			array(
				'posts_per_page'   => -1,
				'offset'           => 0,
				'orderby'          => 'date',
				'order'            => 'DESC',
				'post_type'        => 'bkap_booking',
				'post_status'      => array( 'paid', 'confirmed' ),
				'suppress_filters' => true, // phpcs:ignore
				'meta_query'     => array( // phpcs:ignore
					'relation' => 'AND',
					array(
						'key'     => '_bkap_end', // Meta key for booking start date.
						'value'   => $start_date,
						'compare' => '>=', // Get bookings starting from today or later.
						'type'    => 'DATE',
					),
				),
			)
		);

		$vendor_id = isset( $_GET['vendor_id'] ) ? sanitize_text_field( wp_unslash( $_GET['vendor_id'] ) ) : '';

		if ( '' != $vendor_id ) {
			$booking_args['meta_query'][] = array(
				'relation' => 'AND',
				array(
					'key'     => '_bkap_vendor_id',
					'value'   => $vendor_id,
					'compare' => '=',
				),
				array(
					'key'     => '_bkap_end', // Meta key for booking start date.
					'value'   => $start_date,
					'compare' => '>=', // Get bookings starting from today or later.
					'type'    => 'DATE',
				),
			);

			$vendor_global_holidays = ( $vendor_id > 0 ) ? get_user_meta( $vendor_id, '_bkap_vendor_holidays', true ) : array();
		}

		$variable_map = array();

		if ( isset( $_REQUEST['product_ids'] ) && ! empty( $_REQUEST['product_ids'] ) ) {

			$product_ids = explode( ',', sanitize_text_field( wp_unslash( $_REQUEST['product_ids'] ) ) );
			foreach ( $product_ids as $key => $value ) {
				$product      = wc_get_product( $value );
				$product_type = $product->get_type();
				if ( 'variation' === $product_type ) {
					$variable_map[ $value ] = $product->get_parent_id();
					$product_ids[ $key ]    = $product->get_parent_id();
				}
			}

			$booking_args['meta_query'][] = array(
				'relation' => 'AND',
				array(
					'key'     => '_bkap_product_id',
					'value'   => $product_ids,
					'compare' => 'IN',
				),
				array(
					'key'     => '_bkap_end', // Meta key for booking start date.
					'value'   => $start_date,
					'compare' => '>=', // Get bookings starting from today or later.
					'type'    => 'DATE',
				),
			);
		}

		$bkap_posts_array = get_posts( $booking_args );

		$data = array();

		foreach ( $bkap_posts_array as $key => $value ) {

			$booking = new BKAP_Booking( $value->ID );

			$order = $booking->get_order();

			if ( false === $order ) {
				continue;
			}

			$order_status = $order->get_status();
			if ( isset( $order_status ) && ( $order_status != 'wc-cancelled' ) && ( $order_status != 'wc-refunded' ) && ( $order_status != 'trash' ) && ( $order_status != '' ) && ( $order_status != 'wc-failed' ) ) {

				$product = $booking->get_product();

				if ( false === $product ) {
					continue;
				}

				$product_name = $product_id = '';
				if ( isset( $product ) && $product !== '' && $product !== null ) {
					$product_name = html_entity_decode( $product->get_title(), ENT_COMPAT, 'UTF-8' );
					$product_id   = $product->get_id();
				}

				if ( 'variable' === $product->get_type() ) {
					if ( count( $variable_map ) > 0 ) {
						if ( in_array( $product_id, $variable_map, true ) ) {
							$variation_id = $booking->get_variation_id();
							if ( '' !== $variation_id ) {
								if ( ! in_array( $variation_id, array_keys( $variable_map ) ) ) {
									continue;
								}
							}
						}
					}
				}

				$user             = new WP_User( get_current_user_id() );
				$add_event        = 'YES';
				$booking_settings = get_post_meta( $product_id, 'woocommerce_booking_settings', true );
				if ( isset( $user->roles[0] ) && $user->roles[0] == 'tour_operator' ) {

					$add_event = 'NO';
					if ( isset( $booking_settings['booking_tour_operator'] ) && $booking_settings['booking_tour_operator'] == get_current_user_id() ) {
						$add_event = 'YES';
					}
				}
				if ( isset( $add_event ) && 'YES' == $add_event ) {

					$resource_title = '';
					if ( $booking->get_resource() != '' ) {
						$resource_title = $booking->get_resource_title();
					}

					$order_id  = $order->get_id();
					$order_url = bkap_order_url( $order_id );

					$value = array(
						'order_id'      => $order->get_id(),
						'order_url'     => apply_filters( 'bkap_after_successful_manual_booking', $order_url, $order->get_id() ),
						'post_id'       => $product_id,
						'start_date'    => $booking->get_start_date(),
						'end_date'      => $booking->get_end_date(),
						'from_time'     => $booking->get_start_time(),
						'to_time'       => $booking->get_end_time(),
						'order_item_id' => $booking->get_item_id(),
						'resource'      => $resource_title,
						'persons'       => $booking->get_persons_info(),
					);

					$value        = apply_filters( 'bkap_additional_data_value_in_calendar_tip', $value, $booking );
					$product_name = apply_filters( 'bkap_product_name_calendar_title', $product_name, $booking );

					if ( $booking->get_start_time() != '' && $booking->get_end_time() != '' ) { // this condition is used for adding from and to time slots.

						$post_from_timestamp = strtotime( $booking->get_start() );
						$post_from_date      = gmdate( 'Y-m-d H:i:s', $post_from_timestamp );

						$post_to_timestamp = strtotime( $booking->get_end() );
						$post_to_date      = gmdate( 'Y-m-d H:i:s', $post_to_timestamp );

						array_push(
							$data,
							array(
								'id'    => $order->get_id(),
								'title' => $product_name,
								'start' => $post_from_date,
								'end'   => $post_to_date,
								'value' => $value,
							)
						);
					} elseif ( $booking->get_start_time() != '' ) { // this condition is used for adding only from time slots.
						$post_from_timestamp = strtotime( $booking->get_start() );
						$post_from_date      = gmdate( 'Y-m-d H:i:s', $post_from_timestamp );

						$time    = strtotime( $booking->get_start() );
						$endTime = gmdate( 'Y-m-d H:i', strtotime( '+30 minutes', $time ) );

						$post_to_timestamp = strtotime( $endTime );
						$post_to_date      = gmdate( 'Y-m-d H:i:s', $post_to_timestamp );

						array_push(
							$data,
							array(
								'id'    => $order->get_id(),
								'title' => $product_name,
								'start' => $post_from_date,
								'end'   => $post_to_date,
								'value' => $value,
							)
						);
					} else {

						$start = strtotime( $booking->get_start() );
						$end   = strtotime( $booking->get_end() );

						if ( isset( $booking_settings['booking_charge_per_day'] ) && $booking_settings['booking_charge_per_day'] == 'on' ) {
							$end += ( 60 * 60 * 24 );
						}

						array_push(
							$data,
							array(
								'id'    => $order->get_id(),
								'title' => $product_name,
								'start' => gmdate( 'Y-m-d', $start ),
								'end'   => gmdate( 'Y-m-d', $end ),
								'value' => $value,
							)
						);
					}
				}
			}
		}

		if ( isset( $vendor_global_holidays ) && is_array( $vendor_global_holidays ) && count( $vendor_global_holidays ) > 0 ) {
			foreach ( $vendor_global_holidays as $holiday_data ) {

				$start       = gmdate( 'Y-m-d', strtotime( $holiday_data['start'] ) ); //phpcs:ignore
				$end         = gmdate( 'Y-m-d', strtotime( $holiday_data['end'] ) ); //phpcs:ignore
				$end_display = gmdate( 'Y-m-d', strtotime( $holiday_data['end'] . '+1 day' ) ); //phpcs:ignore

				$value = array(
					'id'         => $holiday_data['id'],
					'start_date' => $start,
					'end_date'   => $end,
				);
				array_push(
					$data,
					array(
						'id'        => $holiday_data['id'],
						'title'     => $holiday_data['range_name'],
						'start'     => $start,
						'end'       => $end_display,
						'color'     => 'grey',
						'className' => 'nonbusiness',
						'value'     => $value,
					)
				);
			}
		}

		$data = apply_filters( 'bkap_calendar_view_events_data', $data );

		wp_send_json( $data );
		// phpcs:enable
	}

	/**
	 * Called during AJAX request for qtip content for a calendar item
	 *
	 * @since 2.0.0
	 */
	public static function bkap_booking_calender_content() {
		$content      = '';
		$date_formats = bkap_common::get_booking_global_value( 'bkap_date_formats' );
		// get the global settings to find the date formats
		$global_settings = bkap_json_decode( get_option( 'woocommerce_booking_global_settings' ) );
		$date_format_set = $date_formats[ $global_settings->booking_date_format ];

		$order_txt     = __( 'Order:', 'woocommerce-booking' );
		$product_txt   = __( 'Product Name:', 'woocommerce-booking' );
		$customer_txt  = __( 'Customer Name: ', 'woocommerce-booking' );
		$qty_txt       = __( 'Quantity: ', 'woocommerce-booking' );
		$startdate_txt = __( 'Start Date: ', 'woocommerce-booking' );
		$enddate_txt   = __( 'End Date: ', 'woocommerce-booking' );
		$time_txt      = __( 'Time: ', 'woocommerce-booking' );
		$resource_txt  = __( 'Resource: ', 'woocommerce-booking' );
		$person_txt    = __( 'Person Info: ', 'woocommerce-booking' );

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_REQUEST['order_id'] ) && ! empty( $_REQUEST['event_value'] ) ) {
			$order_id = sanitize_text_field( wp_unslash( $_REQUEST['order_id'] ) );
			$order    = wc_get_order( $order_id );

			$order_items              = $order->get_items();
			$attribute_name           = '';
			$attribute_selected_value = '';

			if ( version_compare( WOOCOMMERCE_VERSION, '3.0.0' ) < 0 ) {
				$billing_first_name = $order->billing_first_name;
				$billing_last_name  = $order->billing_last_name;
			} else {
				$billing_first_name = $order->get_billing_first_name();
				$billing_last_name  = $order->get_billing_last_name();
			}

			$value[] = array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['event_value'] ) );

			$content = '<table>
				<tr> <td> <strong>' . $order_txt . '</strong></td><td><a href="' . $value[0]['order_url'] . '">#' . $order_id . ' </a> </td> </tr>
				<tr> <td> <strong>' . $product_txt . '</strong></td><td> ' . get_the_title( $value[0]['post_id'] ) . '</td> </tr>
				<tr> <td> <strong>' . $customer_txt . '</strong></td><td> ' . $billing_first_name . ' ' . $billing_last_name . '</td> </tr>';

			foreach ( $order_items as $item_id => $item ) {

				if ( $item['variation_id'] != '' && $value[0]['post_id'] == $item['product_id'] && $value[0]['order_item_id'] == $item_id ) {
					$variation_product       = get_post_meta( $item['product_id'] );
					$product_variation_array = isset( $variation_product['_product_attributes'] ) && isset( $variation_product['_product_attributes'][0] ) ? unserialize( $variation_product['_product_attributes'][0] ) : array();

					foreach ( $product_variation_array as $product_variation_key => $product_variation_value ) {
						if ( isset( $item[ $product_variation_key ] ) && '' !== $item[ $product_variation_key ] ) {

							$attribute_name           = $product_variation_value['name'];
							$attribute_selected_value = $item [ $product_variation_key ];
							$content                 .= ' <tr> <td> <strong>' . $attribute_name . ':</strong></td> <td> ' . $attribute_selected_value . '</td> </tr> ';
						}
					}
				}

				if ( $item['qty'] != '' && $value[0]['post_id'] == $item['product_id'] && $value[0]['order_item_id'] == $item_id ) {
					$content .= ' <tr> <td> <strong>' . $qty_txt . '</strong></td> <td> ' . $item['qty'] . '</td> </tr> ';
				}
			}
			if ( isset( $value[0]['start_date'] ) && $value[0]['start_date'] != '' ) {
				$value_date = $value[0]['start_date'];
				$content   .= ' <tr> <td> <strong>' . $startdate_txt . '</strong></td><td> ' . $value_date . '</td> </tr>';
			}

			if ( isset( $value[0]['end_date'] ) && $value[0]['end_date'] != '' ) {
				$value_end_date = $value[0]['end_date'];
				$content       .= ' <tr> <td> <strong>' . $enddate_txt . '</strong></td><td> ' . $value_end_date . '</td> </tr> ';
			}

			// Booking Time
			$time = '';
			if ( isset( $value[0]['from_time'] ) && $value[0]['from_time'] != '' && isset( $value[0]['to_time'] ) && $value[0]['to_time'] != '' ) {
				if ( $global_settings->booking_time_format == 12 ) {
					$to_time   = '';
					$from_time = gmdate( 'h:i A', strtotime( $value[0]['from_time'] ) );
					$time      = $from_time;

					if ( isset( $value[0]['to_time'] ) && $value[0]['to_time'] != '' ) {
						$to_time = gmdate( 'h:i A', strtotime( $value[0]['to_time'] ) );
						$time    = $from_time . ' - ' . $to_time;
					}
				} else {
					$time = $time = $value[0]['from_time'] . ' - ' . $value[0]['to_time'];
				}

				$content .= '<tr> <td> <strong>' . $time_txt . '</strong></td><td> ' . $time . '</td> </tr>';

			} elseif ( isset( $value[0]['from_time'] ) && $value[0]['from_time'] != '' ) {
				if ( $global_settings->booking_time_format == 12 ) {

					$to_time   = '';
					$from_time = gmdate( 'h:i A', strtotime( $value[0]['from_time'] ) );
					$time      = $from_time . ' - Open-end';
				} else {
					$time = $time = $value[0]['from_time'] . ' - Open-end';
				}
				$content .= '<tr> <td> <strong>' . $time_txt . '</strong></td><td> ' . $time . '</td> </tr>';
			}

			if ( isset( $value[0]['resource'] ) && $value[0]['resource'] != '' ) {
				$value_resource = $value[0]['resource'];
				$content       .= ' <tr> <td> <strong>' . $resource_txt . '</strong></td><td> ' . $value_resource . '</td> </tr> ';
			}

			if ( isset( $value[0]['persons'] ) && $value[0]['persons'] != '' ) {
				$value_persons = $value[0]['persons'];
				$content      .= ' <tr> <td> <strong>' . $person_txt . '</strong></td><td> ' . $value_persons . '</td> </tr> ';

			}

			$content = apply_filters( 'bkap_display_additional_info_in_calendar_tip', $content, $value );

			$content .= '</table>';

			if ( $value[0]['post_id'] ) {
				$post_image = get_the_post_thumbnail( $value[0]['post_id'], array( 100, 100 ) );

				if ( ! empty( $post_image ) ) {
					$content = '<div style="float:left; margin:0px 5px 5px 0px; ">' . $post_image . '</div>' . $content;
				}
			}
		}

		echo $content; // phpcs:ignore
		die();
		// phpcs:enable
	}

	/**
	 * Enquque CSS for Calendar View.
	 *
	 * @since 4.6.0
	 */
	public static function bkap_load_calendar_styles() {
		wp_enqueue_style(
			'bkap-fullcalendar',
			BKAP_Files::rewrite_asset_url( '/assets/css/fullcalendar.css', BKAP_FILE ),
			array(),
			BKAP_VERSION
		);

		// this is for displying the full calender view.
		// wp_enqueue_style( 'full-css', BKAP_Files::rewrite_asset_url( '/assets/js/fullcalendar/fullcalendar.css', BKAP_FILE, true ) );

		// this is used for displying the hover effect in calendar view.
		wp_enqueue_style(
			'bkap-qtip',
			BKAP_Files::rewrite_asset_url( '/assets/css/jquery.qtip.min.css', BKAP_FILE ),
			array(),
			BKAP_VERSION
		);

		wp_enqueue_style(
			'woocommerce_admin_styles',
			WC()->plugin_url() . '/assets/css/admin.css',
			array(),
			BKAP_VERSION
		);

		wp_add_inline_style(
			'woocommerce_admin_styles',
			'.post-type-bkap_booking .tablenav .select2-selection--single{height:32px;} .post-type-bkap_booking .tablenav .select2-selection--single .select2-selection__rendered{line-height:29px;} .post-type-bkap_booking .tablenav .select2-selection--single .select2-selection__arrow{height:30px;} .post-type-bkap_booking .tablenav .select2-container{float:left;width:240px!important;font-size:14px;vertical-align:middle;margin:1px 6px 4px 1px;}'
		);
	}

	/**
	 * Enquque JS for Booking Calendar.
	 *
	 * @since 4.6.0
	 */
	public static function enqueue_calendar_scripts() {

		wp_enqueue_script( 'jquery' );
		wp_deregister_script( 'jqueryui' );

		wp_enqueue_script(
			'bkap-jqueryui',
			'//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js', //phpcs:ignore
			array(),
			BKAP_VERSION,
			false
		);

		wp_register_script(
			'full-js',
			BKAP_Files::rewrite_asset_url( '/assets/js/fullcalendar/main.min.js', BKAP_FILE, true ),
			array(),
			BKAP_VERSION,
			false
		);

		wp_register_script(
			'locales-js',
			BKAP_Files::rewrite_asset_url( '/assets/js/fullcalendar/locales-all.min.js', BKAP_FILE, true ),
			array( 'jquery' ),
			BKAP_VERSION,
			false
		);

		wp_register_script(
			'bkap-images-loaded',
			BKAP_Files::rewrite_asset_url( '/assets/js/imagesloaded.pkg.min.js', BKAP_FILE ),
			array(),
			BKAP_VERSION,
			false
		);

		wp_register_script(
			'bkap-qtip',
			BKAP_Files::rewrite_asset_url( '/assets/js/jquery.qtip.min.js', BKAP_FILE ),
			array( 'jquery', 'bkap-images-loaded' ),
			BKAP_VERSION,
			false
		);

		wp_enqueue_script(
			'select2',
			WC()->plugin_url() . '/assets/js/select2/select2.full.js',
			array( 'jquery' ),
			'4.0.3',
			false
		);

		wp_enqueue_script(
			'selectWoo',
			WC()->plugin_url() . '/assets/js/selectWoo/selectWoo.full.js',
			array( 'jquery' ),
			'1.0.6',
			false
		);
	}
}
