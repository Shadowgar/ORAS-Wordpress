<?php
/**
 * Bookings and Appointment Plugin for WooCommerce
 *
 * Class for handling Manual Bookings using Bookings->Create Booking
 *
 * @author   Tyche Softwares
 * @package  BKAP/Admin-Bookings
 * @category Classes
 */
if ( ! class_exists( 'BKAP_Create_Booking' ) ) {

	/**
	 * Class for creating Manual Bookings using Bookings->Create Booking
	 *
	 * @class BKAP_Create_Booking
	 */
	class BKAP_Create_Booking {

		/**
		 * Stores errors.
		 *
		 * @var array
		 */
		private $errors = array();

		/**
		 * Default Constructor.
		 *
		 * @since 4.1.0
		 */
		public function __construct() {
			add_action( 'woocommerce_order_after_calculate_totals', array( &$this, 'woocommerce_order_after_calculate_totals_callback' ), 10, 2 );
			add_action( 'wp_loaded', array( $this, 'bkap_wp_loaded' ), 10 );
			add_action( 'bkap_after_booking_form_on_create_booking', array( $this, 'bkap_additional_fields' ), 11 );
			add_action( 'bkap_after_manual_booking_created', array( $this, 'bkap_save_additional_fields_data_to_order_item_meta' ), 10, 1 );

			/* Show Disabled Dates&Time for Admin */
			add_filter( 'bkap_consider_recurring_weekday_as_enabled', array( $this, 'bkap_consider_weekday_as_enabled' ), 10, 1 );
			add_filter( 'bkap_manage_time_availability_settings', array( $this, 'bkap_remove_manage_time_availability_settings' ), 10, 3 );
			add_filter( 'bkap_advance_booking_period', array( $this, 'bkap_override_advance_booking_period' ), 10, 1 );
			add_filter( 'bkap_init_parameter_localize_script_booking_settings', array( $this, 'bkap_enable_all_weekdays' ), 10, 1 );
			add_filter( 'bkap_init_parameter_localize_script_additional_data', array( $this, 'bkap_enable_holidays' ), 10, 1 );
		}

		/**
		 * This function will consider the recurring as enabled on Backend.
		 *
		 * @param int $status Recurring Weekday Status.
		 *
		 * @since 6.7.0
		 */
		public function bkap_consider_weekday_as_enabled( $status ) {
			if ( is_admin() && isset( $_POST['create_booking_post_data'] ) && isset( $_POST['create_booking_post_data']['show_disabled_dates'] ) && 'on' === $_POST['create_booking_post_data']['show_disabled_dates'] ) { // phpcs:ignore.
				return true;
			}

			if ( isset( $_POST['show_disabled_dates'] ) && 'on' === $_POST['show_disabled_dates'] ) { // phpcs:ignore.
				return true;
			}

			return $status;
		}

		/**
		 * This function will not ignore the Advance Booking Period settings on Backend.
		 *
		 * @param int $advance_booking_period advance Booking Period.
		 *
		 * @since 6.7.0
		 */
		public function bkap_override_advance_booking_period( $advance_booking_period ) {

			if ( is_admin() && isset( $_POST['create_booking_post_data'] ) && isset( $_POST['create_booking_post_data']['show_disabled_dates'] ) && 'on' === $_POST['create_booking_post_data']['show_disabled_dates'] ) { // phpcs:ignore.
				return 0;
			}

			if ( isset( $_POST['show_disabled_dates'] ) && 'on' === $_POST['show_disabled_dates'] ) { // phpcs:ignore.
				return 0;
			}

			return $advance_booking_period;
		}

		/**
		 * This function will enable all weekdays on the backend.
		 *
		 * @param array $booking_settings Booking Settings.
		 *
		 * @since 6.7.0 
		 */
		public function bkap_enable_all_weekdays( $booking_settings ) {

			if ( is_admin() && isset( $_POST['create_booking_post_data'] ) && isset( $_POST['create_booking_post_data']['show_disabled_dates'] ) && 'on' === $_POST['create_booking_post_data']['show_disabled_dates'] ) { // phpcs:ignore.
				$booking_settings['booking_recurring'] = array(
					'booking_weekday_0' => 'on',
					'booking_weekday_1' => 'on',
					'booking_weekday_2' => 'on',
					'booking_weekday_3' => 'on',
					'booking_weekday_4' => 'on',
					'booking_weekday_5' => 'on',
					'booking_weekday_6' => 'on',
				);

				$booking_settings['booking_product_holiday'] = array();
			}

			return $booking_settings;
		}

		/**
		 * This function will enable all weekdays on the backend.
		 *
		 * @param array $additional_data Additional Booking Data.
		 *
		 * @since 6.7.0
		 */
		public function bkap_enable_holidays( $additional_data ) {

			if ( is_admin() && isset( $_POST['create_booking_post_data'] ) && isset( $_POST['create_booking_post_data']['show_disabled_dates'] ) && 'on' === $_POST['create_booking_post_data']['show_disabled_dates'] ) {  // phpcs:ignore.
				$additional_data['wapbk_block_checkin_weekdays']  = '';
				$additional_data['wapbk_block_checkout_weekdays'] = '';
				$additional_data['holidays']                      = '';
				$additional_data['resource_disable_dates']        = array();
				$additional_data['resource_disable_time_slots']   = array();
				$additional_data['variation_holidays_data']       = array();
			}

			return $additional_data;
		}

		/**
		 * This function will ignore the manage time availability settings on the backend.
		 *
		 * @param array $manage_time_data Manage Availability Time Data.
		 * @param array $booking_settings Booking Settings.
		 * @param int   $product_id PRoduct ID.
		 *
		 * @since 6.7.0
		 */
		public function bkap_remove_manage_time_availability_settings( $manage_time_data, $booking_settings, $product_id ) {

			if ( is_admin() && isset( $_POST['create_booking_post_data'] ) && isset( $_POST['create_booking_post_data']['show_disabled_dates'] ) && 'on' === $_POST['create_booking_post_data']['show_disabled_dates'] ) { // phpcs:ignore.
				return array();
			}

			if ( isset( $_POST['show_disabled_dates'] ) && 'on' === $_POST['show_disabled_dates'] ) { // phpcs:ignore.
				return array();
			}

			return $manage_time_data;
		}

		/**
		 * Adding textarea after booking form when creating manual booking.
		 *
		 * @since 6.4.0
		 */
		public function bkap_additional_fields() {

			$additional_field_label = apply_filters( 'bkap_additional_comment_field_on_manual_booking', __( 'Additional Comment', 'woocommerce-booking' ) );
			echo '<div class="bkap-additional-comment-wrapper" style="margin: 20px 0;">';
			echo '<label for="bkap_additional_comment" style="display:block;">' . esc_html( $additional_field_label ) . ':</label>';
			echo '<textarea id="bkap_additional_comment" name="bkap_additional_comment" rows="5" cols="50"></textarea>';
			echo '</div>';
		}

		/**
		 * Saving textarea value as order item upon successful creation of manual booking.
		 *
		 * @param array $status Array of data regarding created booking.
		 *
		 * @since 6.4.0
		 */
		public function bkap_save_additional_fields_data_to_order_item_meta( $status ) {

			$additional_field_label = apply_filters( 'bkap_additional_comment_field_on_manual_booking', __( 'Additional Comment', 'woocommerce-booking' ) );

			if ( isset( $status['item_id'] ) && '0' !== $status['item_id'] && isset( $_POST['bkap_additional_comment'] ) && '' !== $_POST['bkap_additional_comment'] ) { // phpcs:ignore
				$item_id     = $status['item_id'];
				$booking_ids = array();
				if ( is_array( $status['booking_id'] ) ) {
					$booking_ids = $status['booking_id'];
				} else {
					$booking_ids[] = $status['booking_id'];
				}

				foreach ( $booking_ids as $booking_id ) {
					$additional_field_data = sanitize_textarea_field( wp_unslash( $_POST['bkap_additional_comment'] ) ); // phpcs:ignore

					wc_add_order_item_meta( $item_id, $additional_field_label, $additional_field_data );
					add_post_meta( $booking_id, '_bkap_additional_comment', $additional_field_data );
				}
			}

			return $status;
		}

		/**
		 * Create Booking upon Click of Create Booking Button.
		 * Function loaded on wp_loaded as the same is being used on front end.
		 * Creating Manual Booking fron end was throwing header already sent by message.
		 *
		 * @since 5.10.0
		 */
		public function bkap_wp_loaded() {
			// phpcs:disable WordPress.Security.NonceVerification
			if ( isset( $_POST['bkap_create_booking_nonce'] ) ) {

				if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bkap_create_booking_nonce'] ) ), 'bkap_create_booking' ) ) {
					wp_die( esc_html__( 'Security check', 'woocommerce-booking' ) );
				}

				try {
					if ( ! empty( $_POST['bkap_create_booking_2'] ) ) {

						$create_order = ( isset( $_POST['bkap_order'] ) && 'new' === $_POST['bkap_order'] ) ? true : false;

						// validate the booking data.
						$validations = true;
						$_product    = isset( $_POST['bkap_product_id'] ) ? wc_get_product( sanitize_text_field( wp_unslash( $_POST['bkap_product_id'] ) ) ) : '';

						if ( $_product->post_type === 'product_variation' ) {
							$settings_id = $_product->get_parent_id();
						} else {
							$settings_id = sanitize_text_field( wp_unslash( $_POST['bkap_product_id'] ) );
						}

						if ( isset( $_POST['wapbk_hidden_date'] ) && $_POST['wapbk_hidden_date'] === '' ) {
							$validations = false;
						}

						$booking_type  = bkap_type( $settings_id );
						$bkap_settings = bkap_setting( $settings_id );

						switch ( $booking_type ) {
							case 'multiple_days':
								if ( isset( $_POST['wapbk_hidden_date_checkout'] ) && $_POST['wapbk_hidden_date_checkout'] === '' ) {
									$validations = false;
								}
								break;
							case 'date_time':
								if ( isset( $_POST['time_slot'] ) && $_POST['time_slot'] === '' ) {
									$validations = false;
								}
								break;
							case 'duration_time':
								if ( isset( $_POST['duration_time_slot'] ) && $_POST['duration_time_slot'] === '' ) {
									$validations = false;
								}
								break;
							case 'multidates':
							case 'multidates_fixedtime':
								$validations = true;
								if ( ! isset( $_POST['bkap_multidate_data'] ) && '' === $_POST['bkap_multidate_data'] ) {
									$validations = false;
								}
								break;
						}

						if ( ! $validations ) {
							throw new Exception( __( 'Please select the Booking Details.', 'woocommerce-booking' ) );
						}

						// setup the data.
						$time_slot          = ( isset( $_POST['time_slot'] ) ) ? sanitize_text_field( wp_unslash( $_POST['time_slot'] ) ) : '';
						$duration_time_slot = ( isset( $_POST['duration_time_slot'] ) ) ? sanitize_text_field( wp_unslash( $_POST['duration_time_slot'] ) ) : '';
						$checkout_date      = ( isset( $_POST['wapbk_hidden_date_checkout'] ) && '' !== $_POST['wapbk_hidden_date_checkout'] ) ? sanitize_text_field( wp_unslash( $_POST['wapbk_hidden_date_checkout'] ) ) : '';

						$booking_details['product_id']  = isset( $_POST['bkap_product_id'] ) ? sanitize_text_field( wp_unslash( $_POST['bkap_product_id'] ) ) : '';
						$booking_details['customer_id'] = isset( $_POST['bkap_customer_id'] ) ? sanitize_text_field( wp_unslash( $_POST['bkap_customer_id'] ) ) : '';

						$wapbk_hidden_date = isset( $_POST['wapbk_hidden_date'] ) ? sanitize_text_field( wp_unslash( $_POST['wapbk_hidden_date'] ) ) : '';
						if ( $time_slot !== '' ) {
							if ( is_array( $time_slot ) ) {
								$times = explode( ' - ', $time_slot[0] ); // temporarily fetching only first timeslot to create manual order.
							} else {
								$times = explode( ' - ', $time_slot );
							}
							$start_time = ( isset( $times[0] ) && '' !== $times[0] ) ? gmdate( 'H:i', strtotime( $times[0] ) ) : '00:00';
							$end_time   = ( isset( $times[1] ) && '' !== $times[1] ) ? gmdate( 'H:i', strtotime( $times[1] ) ) : '00:00';

							$booking_details['start'] = strtotime( $wapbk_hidden_date . $start_time );
							$booking_details['end']   = strtotime( $wapbk_hidden_date . $end_time );

						} elseif ( $checkout_date !== '' ) {
							$booking_details['start'] = strtotime( $wapbk_hidden_date );
							$booking_details['end']   = strtotime( $checkout_date );
						} elseif ( $duration_time_slot !== '' ) {

							$d_setting = get_post_meta( $settings_id, '_bkap_duration_settings', true );

							$start_date               = $wapbk_hidden_date; // hiddendate.
							$booking_details['start'] = strtotime( $start_date . ' ' . $duration_time_slot ); // creating start date based on date and time.

							$selected_duration = isset( $_POST['bkap_duration_field'] ) ? sanitize_text_field( wp_unslash( $_POST['bkap_duration_field'] ) ) : 1; // selected duration.
							$duration          = $d_setting['duration']; // Numbers of hours set for product.

							$hour   = $selected_duration * $duration; // calculating numbers of duration by customer.
							$d_type = $d_setting['duration_type']; // hour/min.

							$booking_details['end'] = bkap_common::bkap_add_hour_to_date( $start_date, $duration_time_slot, $hour, $settings_id, $d_type );

							$booking_details['duration'] = $hour . '-' . $d_type;

						} else {
							$booking_details['start'] = strtotime( $wapbk_hidden_date );
							$booking_details['end']   = strtotime( $wapbk_hidden_date );
						}

						$bkap_price_charged = isset( $_POST['bkap_price_charged'] ) ? sanitize_text_field( wp_unslash( $_POST['bkap_price_charged'] ) ) : '';

						if ( get_option( 'woocommerce_prices_include_tax' ) == 'yes' ) {
							$product                       = wc_get_product( $settings_id );
							$product_price                 = wc_get_price_excluding_tax(
								$product,
								array( 'price' => $bkap_price_charged )
							);
							$booking_details['price']      = $product_price;
							$booking_details['price_incl'] = $bkap_price_charged;
						} else {
							$booking_details['price'] = $bkap_price_charged;
						}

						if ( isset( $_POST['bkap_front_resource_selection'] ) && $_POST['bkap_front_resource_selection'] != '' ) {
							$booking_details['bkap_resource_id'] = sanitize_text_field( wp_unslash( $_POST['bkap_front_resource_selection'] ) );
						}

						/* Persons Calculations */
						if ( isset( $bkap_settings['bkap_person'] ) && 'on' === $bkap_settings['bkap_person'] ) {
							if ( isset( $_POST['bkap_field_persons'] ) ) {
								$total_person               = (int) $_POST['bkap_field_persons'];
								$booking_details['persons'] = array( $total_person );
							} else {
								$person_data      = $bkap_settings['bkap_person_data'];
								$person_post_data = array();
								foreach ( $person_data as $p_id => $p_data ) {
									$p_key = 'bkap_field_persons_' . $p_id;
									if ( isset( $_POST[ $p_key ] ) && '' !== $_POST[ $p_key ] ) {
										$person_post_data[ $p_id ] = (int) $_POST[ $p_key ];
									}
								}
								$booking_details['persons'] = $person_post_data;
							}
						}

						if ( isset( $_POST['block_option'] ) && $_POST['block_option'] != '' ) {
							$booking_details['fixed_block'] = sanitize_text_field( wp_unslash( $_POST['block_option'] ) );
						}

						if ( isset( $_POST['quantity'] ) && $_POST['quantity'] != '' ) {
							$booking_details['quantity'] = sanitize_text_field( wp_unslash( $_POST['quantity'] ) );
						}

						if ( isset( $_POST['bkap_show_disabled_dates'] ) && $_POST['bkap_show_disabled_dates'] != '' ) {
							$booking_details['bkap_show_disabled_dates'] = sanitize_text_field( wp_unslash( $_POST['bkap_show_disabled_dates'] ) );
						}

						if ( isset( $_POST['bkap_multidate_data'] ) && '' != $_POST['bkap_multidate_data'] ) {

							$booking_details['has_multidates'] = true;

							$posted_multidate_data = sanitize_text_field( wp_unslash( $_POST['bkap_multidate_data'] ) );
							$temp_data             = str_replace( '\\', '', $posted_multidate_data );
							$bkap_multidate_data   = (array) json_decode( $temp_data );

							foreach ( $bkap_multidate_data as $value ) {

								$booking                = array();
								$booking['date']        = $value->date;
								$booking['hidden_date'] = $value->hidden_date;

								if ( isset( $_POST['block_option'] ) && '' !== $_POST['block_option'] ) {
									$booking['fixed_block'] = sanitize_term_field( wp_unslash( $_POST['block_option'] ) );
								}

								if ( isset( $_POST['booking_calender_checkout'] ) ) {
									$booking['date_checkout'] = sanitize_text_field( wp_unslash( $_POST['booking_calender_checkout'] ) );
								}

								if ( isset( $_POST['wapbk_hidden_date_checkout'] ) ) {
									$booking['hidden_date_checkout'] = sanitize_text_field( wp_unslash( $_POST['wapbk_hidden_date_checkout'] ) );
								}

								if ( isset( $value->time_slot ) ) {
									$booking['time_slot'] = $value->time_slot;
								}

								$booking['price_charged']                = $value->price_charged;
								$booking_details['multidates_booking'][] = $booking;
							}
						}

						$booking_details = apply_filters( 'bkap_detail_before_creating_manual_order', $booking_details );

						if ( 'new' === $_POST['bkap_order'] ) {
							// create a new order.
							$status = BKAP_Admin_Import_Booking::bkap_create_order( $booking_details, false );
							// get the new order ID.
							$order_id = ( absint( $status['order_id'] ) > 0 ) ? $status['order_id'] : 0;

							do_action( 'bkap_manual_booking_created_with_new_order', $order_id );

						} else {
							$order_id = ( isset( $_POST['bkap_order_id'] ) ) ? sanitize_text_field( wp_unslash( $_POST['bkap_order_id'] ) ) : 0;

							if ( 'only_booking' != $order_id && $order_id > 0 ) {
								$booking_details['order_id'] = $order_id;
								$status                      = BKAP_Admin_Import_Booking::bkap_create_booking( $booking_details, false );
							} else {
								$booking_details['order_id']   = 0;
								$booking_details['order_type'] = 'only_booking';
								$status                        = BKAP_Admin_Import_Booking::bkap_create_booking( $booking_details, false );
							}
						}

						$redirect_url = bkap_order_url( $order_id );
						$redirect_url = apply_filters( 'bkap_after_successful_manual_booking', $redirect_url, $order_id );

						do_action( 'bkap_after_manual_booking_created', $status, $booking_details );

						if ( isset( $status['new_order'] ) && $status['new_order'] ) {
							// redirect to the order.
							wp_safe_redirect( $redirect_url );
							exit;
						} elseif ( isset( $status['item_added'] ) && $status['item_added'] ) {

							if ( isset( $_POST['bkap_order'] ) && 'only_booking' === $_POST['bkap_order'] ) {

								$vendor_id = get_current_user_id();
								$is_vendor = BKAP_Vendors::bkap_is_vendor( $vendor_id );
								if ( $is_vendor && ! is_admin() ) {
									wp_safe_redirect( $redirect_url );
									exit;
								} else {
									if ( isset( $status['booking_id'] ) ) {
										//$booking_url = admin_url( 'post.php?post=' . $status['booking_id'] . '&action=edit' );
										$booking_url = admin_url( 'admin.php?page=bkap_page&action=booking#/?new_booking=success' );
										wp_safe_redirect( $booking_url );
										exit;
									} else {
										$booking_url = admin_url( 'admin.php?page=bkap_page&action=booking#/?new_booking=success' );
										wp_safe_redirect( $booking_url );
										exit;
									}
								}
							} else {
								// redirect to the order.
								wp_safe_redirect( $redirect_url );
								exit;
							}
						} else {
							if ( 1 == $status['backdated_event'] ) {
								throw new Exception( __( 'Back Dated bookings cannot be created. Please select a future date.', 'woocommerce-booking' ) );
							}

							if ( 1 == $status['validation_check'] ) {
								throw new Exception( __( 'The product is not available for the given date for the desired quantity.', 'woocommerce-booking' ) );
							}

							if ( 1 == $status['grouped_product'] ) {
								throw new Exception( __( 'Bookings cannot be created for grouped products.', 'woocommerce-booking' ) );
							}
						}
					}
				} catch ( Exception $e ) {
					$bkap_admin_bookings           = new BKAP_Create_Booking();
					$bkap_admin_bookings->errors[] = $e->getMessage();
				}
				// phpcs:enable WordPress.Security.NonceVerification
			}
		}

		/**
		 * Updating the price in the booking when discount is appied from Edit Order page.
		 *
		 * @param bool   $and_taxes true if calculation for taxes else false.
		 * @param Object $order Shop Order post.
		 * @since 4.9.0
		 *
		 * @hook woocommerce_order_after_calculate_totals
		 */
		public function woocommerce_order_after_calculate_totals_callback( $and_taxes, $order ) {

			$item_values = $order->get_items();

			foreach ( $item_values as $cart_item_key => $values ) {

				$product_id = $values['product_id'];
				$bookable   = bkap_common::bkap_get_bookable_status( $product_id );

				if ( ! $bookable ) {
					continue;
				}

				$booking_id    = bkap_common::get_booking_id( $cart_item_key );
				$item_quantity = $values->get_quantity(); // Get the item quantity.
				$item_total    = number_format( (float) $values->get_total(), wc_get_price_decimals(), '.', '' );
				$item_tax      = number_format( (float) $values->get_total_tax(), wc_get_price_decimals(), '.', '' );
				$item_total    = $item_total + $item_tax;
				$item_total    = $item_total / $item_quantity;

				// update booking post meta.
				update_post_meta( $booking_id, '_bkap_cost', $item_total );
			}
		}

		/**
		 * Loads the Create Booking Pages or saves the booking based on
		 * the data passed in $_POST
		 *
		 * @since 4.1.0
		 */

		public static function bkap_create_booking_page() {

			bkap_include_select2_scripts();

			$bookable_product_id = 0;
			$bkap_admin_bookings = new BKAP_Create_Booking();

			$step = 1;
			// phpcs:disable WordPress.Security.NonceVerification
			try {
				if ( ! empty( $_POST['bkap_create_booking'] ) ) {

					$customer_id         = isset( $_POST['customer_id'] ) ? absint( $_POST['customer_id'] ) : 0;
					$bookable_product_id = isset( $_POST['bkap_product_id'] ) ? absint( $_POST['bkap_product_id'] ) : 0;
					$booking_order       = isset( $_POST['bkap_order'] ) ? sanitize_text_field( wp_unslash( $_POST['bkap_order'] ) ) : '';

					if ( ! $bookable_product_id ) {
						throw new Exception( __( 'Please choose a bookable product', 'woocommerce-booking' ) );
					}

					if ( 'existing' === $booking_order ) {
						$order_id      = isset( $_POST['bkap_order_id'] ) ? absint( $_POST['bkap_order_id'] ) : 0;
						$booking_order = $order_id;
						if ( ! wc_get_order( $order_id ) ) {
							throw new Exception( __( 'Invalid order ID provided', 'woocommerce-booking' ) );
						}

						$vendor_id = get_current_user_id();
						$is_vendor = BKAP_Vendors::bkap_is_vendor( $vendor_id );

						if ( $is_vendor && function_exists( 'dokan_is_seller_dashboard' ) && dokan_is_seller_dashboard() ) {
							$order_seller_id = dokan_get_seller_id_by_order( $order_id );

							if ( $vendor_id !== $order_seller_id ) {
								throw new Exception( __( 'You are trying to add a booking to another vendor\'s order.', 'woocommerce-booking' ) );
							}
						}
					}

					$bkap_data['customer_id'] = $customer_id;
					$bkap_data['product_id']  = $bookable_product_id;
					$bkap_data['order_id']    = $booking_order;
					$bkap_data['bkap_order']  = isset( $_POST['bkap_order'] ) ? sanitize_text_field( wp_unslash( $_POST['bkap_order'] ) ) : '';
					$step++;
				}
			} catch ( Exception $e ) {
				$bkap_admin_bookings           = new BKAP_Create_Booking();
				$bkap_admin_bookings->errors[] = $e->getMessage();
			}

			switch ( $step ) {
				case '1':
					$bkap_admin_bookings->create_bookings_1();
					break;
				case '2':
					$bkap_admin_bookings->create_bookings_2( $bkap_data );
					break;
				default:
					$bkap_admin_bookings->create_bookings_1();
					break;
			}

			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Output any warnings/errors that occur when creating a manual booking.
		 *
		 * @since 4.1.0
		 */

		public function show_errors() {
			foreach ( $this->errors as $error ) {
				echo '<div class="error bkap-error woocommerce-error"><p>' . esc_html( $error ) . '</p></div>';
			}
		}

		/**
		 * Display the first page for manual bookings
		 *
		 * @since 4.1.0
		 * @todo Change to function name as per its functionality
		 */
		public function create_bookings_1() {

			global $WCFM;
			$this->show_errors();

			$bkap_customers = array();
			$args           = apply_filters(
				'bkap_create_booking_page_users_dropdown_args',
				array(
					'fields'  => array( 'id', 'display_name', 'user_email' ),
					'orderby' => 'display_name',
					'order'   => 'ASC',
				)
			);
			$wp_users       = get_users( $args );

			foreach ( $wp_users as $users ) {
				$customer_id                    = $users->id;
				if ( ! is_admin() ) {
					if ( function_exists( 'dokan_customer_has_order_from_this_seller' ) && ! dokan_customer_has_order_from_this_seller( $customer_id ) ) {
						continue;
					}

					if ( isset( $WCFM->wcfm_customer ) ) {
						$results = $WCFM->wcfm_customer->wcfm_get_customers_orders_stat( $customer_id );
						if ( 0 === $results['total_order'] ) {
							continue;
						}
					}
				}
				$user_email                     = $users->user_email;
				$user_name                      = $users->display_name;
				$bkap_customers[ $customer_id ] = "$user_name (#$customer_id - $user_email )";
			}

			$product_status             = array( 'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit' );
			$php_version                = ( version_compare( WOOCOMMERCE_VERSION, '3.0.0' ) < 0 );
			$bkap_all_bookable_products = bkap_common::get_woocommerce_product_list( true, 'on', '', $product_status );
			$bkap_admin_bookings        = new BKAP_Create_Booking();

			/* Create Booking Main Page Template */
			wc_get_template(
				'create-booking/bkap-create-booking-form.php',
				array(
					'bkap_admin_bookings'        => $bkap_admin_bookings,
					'bkap_customers'             => $bkap_customers,
					'bkap_all_bookable_products' => $bkap_all_bookable_products,
					'php_version'                => $php_version,
				),
				'woocommerce-booking/',
				BKAP_BOOKINGS_TEMPLATE_PATH
			);
		}

		/**
		 * Display the second page for manual bookings.
		 *
		 * @since 4.1.0
		 * @todo Change to function name as per its functionality
		 */
		public function create_bookings_2( $booking_data ) {

			$this->show_errors();
			// check if the passed product ID is a variation ID.
			$_product     = wc_get_product( $booking_data['product_id'] );
			$variation_id = 0;
			$parent_id    = ( version_compare( WOOCOMMERCE_VERSION, '3.0.0' ) < 0 ) ? $_product->parent->id : $_product->get_parent_id();
			$product_id   = $booking_data['product_id'];
			$duplicate_id = ( $parent_id > 0 ) ? $parent_id : $product_id;
			$duplicate_id = bkap_common::bkap_get_product_id( $duplicate_id );

			/* Create Booking Details Selection Template */
			wc_get_template(
				'create-booking/bkap-booking-selection-form.php',
				array(
					'product_id'   => $product_id,
					'duplicate_id' => $duplicate_id,
					'_product'     => $_product,
					'booking_data' => $booking_data,
					'parent_id'    => $parent_id,
				),
				'woocommerce-booking/',
				BKAP_BOOKINGS_TEMPLATE_PATH
			);
		}
	}
}
