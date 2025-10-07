<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * Class for displaying Settings page for Zapier.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Zapier
 * @category    Classes
 * @since       5.11.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BKAP_Zapier' ) ) {

	/**
	 * Display Settings page on the WordPress Admin Page.
	 *
	 * @since 5.11.0
	 */
	class BKAP_Zapier {

		/**
		 * Key name for saving Zapier API Settings to the database.
		 *
		 * @var string
		 */
		public static $settings_key = 'bkap_api_zapier_settings';

		/**
		 * Key name for saving Zapier API Subscriptions to the database.
		 *
		 * @var string
		 */
		public static $subscription_key = 'bkap_api_zapier_subscription';

		/**
		 * Key name for saving Zapier API Product Settings to the database.
		 *
		 * @var string
		 */
		public static $product_settings_key = '_bkap_zapier';

		/**
		 * Database table for Zapier Log.
		 *
		 * @var string
		 * @since 5.11.0
		 */
		public static $database_table = 'bkap_api_zapier_log';

		/**
		 * Construct
		 *
		 * @since 5.11.0
		 */
		public function __construct() {
			add_action( 'bkap_update_booking_post_meta', array( &$this, 'bkap_api_zapier_create_booking_trigger' ), 10, 2 );
			add_action( 'bkap_requires_confirmation_after_save_booking_status', array( &$this, 'bkap_api_zapier_create_booking_trigger' ), 10, 2 );
			add_action( 'bkap_after_update_booking_post', array( &$this, 'bkap_api_zapier_update_booking_trigger' ), 10, 2 );
			add_action( 'bkap_before_delete_booking_post', array( &$this, 'bkap_api_zapier_delete_booking_trigger' ), 10, 2 );
		}

		/**
		 * Retrieve Zapier Settings from the database.
		 *
		 * @param string $option Property of Zapier Setting object that is stored in the database.
		 * @since 5.11.0
		 */
		public static function bkap_api_zapier_get_settings( $option = '' ) {
			$settings = get_option( self::$settings_key );

			if ( '' !== $settings ) {
				$settings = bkap_json_decode( $settings );
			}

			return ( '' === $option ) ? $settings : ( ( '' !== $settings && isset( $settings->$option ) &&
			'' !== $settings->$option ) ? $settings->$option : '' );
		}

		/**
		 * Retrieve Zapier Product Settings from the database.
		 *
		 * @param string $product_id Product ID of Product where settings would be retrieved from.
		 * @param string $option Property of Zapier Product Setting object that is stored in the database.
		 * @since 5.11.0
		 */
		public static function bkap_api_zapier_get_product_settings( $product_id, $option = '' ) {
			$settings = get_post_meta( $product_id, self::$product_settings_key, true );

			return ( '' === $option ) ? $settings : ( ( '' !== $settings && isset( $settings[ $option ] ) &&
			'' !== $settings[ $option ] ) ? $settings[ $option ] : array() );
		}

		/**
		 * Get Zapier Product Setting for a Trigger.
		 *
		 * @param int    $product_id Product ID.
		 * @param string $trigger Zapier Trigger.
		 * @param string $parameter Parameter that should be fetched and returned.
		 * @since 5.11.0
		 */
		public static function bkap_api_zapier_get_product_trigger_setting( $product_id, $trigger, $parameter ) {

			$trigger_data = '';
			$setting      = self::bkap_api_zapier_get_product_settings( $product_id, $trigger );

			if ( isset( $setting[ $parameter ] ) && '' !== $setting[ $parameter ] ) {
				$trigger_data = $setting[ $parameter ];
			}

			return $trigger_data;
		}

		/**
		 * Get Trigger Hooks from Zapier.
		 *
		 * @param int    $user_id Product ID.
		 * @param string $type Zapier Trigger Type.
		 * @since 5.11.0
		 */
		public static function bkap_api_zapier_get_trigger_hooks( $user_id, $type ) {

			$hooks = (array) self::bkap_api_zapier_get_subscriptions( $type );

			if ( '' !== $user_id && is_array( $hooks ) ) {
				foreach ( $hooks as $key => $hook ) {

					if ( is_string( $hook ) ) {
						unset( $hooks[ $key ] );
						continue;
					}

					// Remove hooks not created/meant for the current user.
					if ( (int) $user_id !== (int) $hook->created_by ) {
						unset( $hooks[ $key ] );
					}
				}
			} else {
				$hooks = array();
			}

			return $hooks;
		}

		/**
		 * Retrieve Zapier Subscriptions from the database.
		 *
		 * @param string $option Property of Zapier Subscriptions object that is stored in the database.
		 * @since 5.11.0
		 */
		public static function bkap_api_zapier_get_subscriptions( $option = '' ) {
			$subscriptions = get_option( self::$subscription_key );

			if ( '' !== $subscriptions ) {
				$subscriptions = bkap_json_decode( $subscriptions );
			}

			return ( '' === $option ) ? $subscriptions : ( ( '' !== $subscriptions && isset( $subscriptions->$option ) &&
			'' !== $subscriptions->$option ) ? $subscriptions->$option : '' );
		}

		/**
		 * Checks if Zapier API has been enabled.
		 *
		 * @since 5.11.0
		 */
		public static function bkap_api_zapier_is_zapier_enabled() {
			return 'on' === self::bkap_api_zapier_get_settings( 'bkap_api_zapier_integration' );
		}

		/**
		 * Checks if Logging has been enabled.
		 *
		 * @since 5.11.0
		 */
		public static function bkap_api_zapier_is_logging_enabled() {
			return 'on' === self::bkap_api_zapier_get_settings( 'bkap_api_zapier_log_enable' );
		}

		/**
		 * Checks if Create Booking Trigger has been enabled.
		 *
		 * @since 5.11.0
		 */
		public static function bkap_api_zapier_is_create_booking_trigger_enabled() {
			return 'on' === self::bkap_api_zapier_get_settings( 'trigger_create_booking' );
		}

		/**
		 * Checks if Create Booking Trigger for a Product has been enabled.
		 *
		 * @param int $product_id Product ID.
		 * @since 5.11.0
		 */
		public static function bkap_api_zapier_is_create_booking_trigger_enabled_for_product( $product_id ) {
			return 'on' === self::bkap_api_zapier_get_product_trigger_setting( $product_id, 'trigger_create_booking', 'status' );
		}

		/**
		 * Gets Hook for Create Booking Trigger for a Product.
		 *
		 * @param int $product_id Product ID.
		 * @since 5.11.0
		 */
		public static function bkap_api_zapier_get_create_booking_trigger_product_hook( $product_id ) {
			return self::bkap_api_zapier_get_zapier_hook_url( 'booking_create', 'trigger_create_booking', $product_id );
		}

		/**
		 * Gets Label for Create Booking Trigger for a Product.
		 *
		 * @param int $product_id Product ID.
		 * @since 5.14.0
		 */
		public static function bkap_api_zapier_get_create_booking_trigger_product_label( $product_id ) {
			return self::bkap_api_zapier_get_product_trigger_setting( $product_id, 'trigger_create_booking', 'label' );
		}

		/**
		 * Gets Hooks for Create Booking Triggers on Zapier.
		 *
		 * @param string $user_id User ID to return hooks asiigned/created by a User.
		 * @since 5.11.0
		 */
		public static function bkap_api_zapier_get_create_booking_trigger_hooks( $user_id = '' ) {
			return self::bkap_api_zapier_get_trigger_hooks( $user_id, 'booking_create' );
		}

		/**
		 * Checks if Update Booking Trigger has been enabled.
		 *
		 * @since 5.11.0
		 */
		public static function bkap_api_zapier_is_update_booking_trigger_enabled() {
			return 'on' === self::bkap_api_zapier_get_settings( 'trigger_update_booking' );
		}

		/**
		 * Checks if Update Booking Trigger for a Product has been enabled.
		 *
		 * @param int $product_id Product ID.
		 * @since 5.11.0
		 */
		public static function bkap_api_zapier_is_update_booking_trigger_enabled_for_product( $product_id ) {
			return 'on' === self::bkap_api_zapier_get_product_trigger_setting( $product_id, 'trigger_update_booking', 'status' );
		}

		/**
		 * Gets Hook for Update Booking Trigger for a Product.
		 *
		 * @param int $product_id Product ID.
		 * @since 5.11.0
		 */
		public static function bkap_api_zapier_get_update_booking_trigger_product_hook( $product_id ) {
			return self::bkap_api_zapier_get_zapier_hook_url( 'booking_update', 'trigger_update_booking', $product_id );
		}

		/**
		 * Gets Label for Update Booking Trigger for a Product.
		 *
		 * @param int $product_id Product ID.
		 * @since 5.11.0
		 */
		public static function bkap_api_zapier_get_update_booking_trigger_product_label( $product_id ) {
			return self::bkap_api_zapier_get_product_trigger_setting( $product_id, 'trigger_update_booking', 'label' );
		}

		/**
		 * Gets Hooks for Update Booking Triggers on Zapier.
		 *
		 * @param string $user_id User ID to return hooks asiigned/created by a User.
		 * @since 5.11.0
		 */
		public static function bkap_api_zapier_get_update_booking_trigger_hooks( $user_id = '' ) {
			return self::bkap_api_zapier_get_trigger_hooks( $user_id, 'booking_update' );
		}

		/**
		 * Checks if Delete Booking Trigger has been enabled.
		 *
		 * @since 5.11.0
		 */
		public static function bkap_api_zapier_is_delete_booking_trigger_enabled() {
			return 'on' === self::bkap_api_zapier_get_settings( 'trigger_delete_booking' );
		}

		/**
		 * Checks if Delete Booking Trigger for a Product has been enabled.
		 *
		 * @param int $product_id Product ID.
		 * @since 5.11.0
		 */
		public static function bkap_api_zapier_is_delete_booking_trigger_enabled_for_product( $product_id ) {
			return 'on' === self::bkap_api_zapier_get_product_trigger_setting( $product_id, 'trigger_delete_booking', 'status' );
		}

		/**
		 * Gets Hook for Delete Booking Trigger for a Product.
		 *
		 * @param int $product_id Product ID.
		 * @since 5.11.0
		 */
		public static function bkap_api_zapier_get_delete_booking_trigger_product_hook( $product_id ) {
			return self::bkap_api_zapier_get_zapier_hook_url( 'booking_delete', 'trigger_delete_booking', $product_id );
		}

		/**
		 * Gets Label for Delete Booking Trigger for a Product.
		 *
		 * @param int $product_id Product ID.
		 * @since 5.11.0
		 */
		public static function bkap_api_zapier_get_delete_booking_trigger_product_label( $product_id ) {
			return self::bkap_api_zapier_get_product_trigger_setting( $product_id, 'trigger_delete_booking', 'label' );
		}

		/**
		 * Gets Hooks for Delete Booking Triggers on Zapier.
		 *
		 * @param string $user_id User ID to return hooks asiigned/created by a User.
		 * @since 5.11.0
		 */
		public static function bkap_api_zapier_get_delete_booking_trigger_hooks( $user_id = '' ) {
			return self::bkap_api_zapier_get_trigger_hooks( $user_id, 'booking_delete' );
		}

		/**
		 * Checks if Create Booking Action has been enabled.
		 *
		 * @since 5.11.0
		 */
		public static function bkap_api_zapier_is_create_booking_action_enabled() {
			return 'on' === self::bkap_api_zapier_get_settings( 'action_create_booking' );
		}

		/**
		 * Checks if Update Booking Action has been enabled.
		 *
		 * @since 5.11.0
		 */
		public static function bkap_api_zapier_is_update_booking_action_enabled() {
			return 'on' === self::bkap_api_zapier_get_settings( 'action_update_booking' );
		}

		/**
		 * Checks if Create Booking Action has been enabled.
		 *
		 * @since 5.11.0
		 */
		public static function bkap_api_zapier_is_delete_booking_action_enabled() {
			return 'on' === self::bkap_api_zapier_get_settings( 'action_delete_booking' );
		}

		/**
		 * Callback for displaying instructions on General Settings section.
		 *
		 * @since 5.11.0
		 */
		public static function bkap_api_zapier_event_log_instructions() {
			?>
				<p>Manage Zapier logs and monitor the events taking place on your WooCommerce Store.</p>
			<?php
		}

		/**
		 * Save the Zapier API Setting record to the database.
		 *
		 * @param string $option_key Option key for saving record to the database.
		 * @param string $key Identifier for the record.
		 * @param string $record Record to be saved to the database.
		 * @param bool   $overwrite Whether to overwrite existing data or append.
		 * @return bool True if save operation is successful.
		 * @since 5.11.0
		 */
		public static function bkap_api_zapier_save_records_to_db( $option_key, $key, $record, $overwrite = true ) {

			$settings = array();

			if ( '_all' === $key ) {
				$settings = $record;
			} else {

				if ( ! $overwrite ) {
					$_settings = array();
					$settings  = get_option( $option_key );

					if ( '' !== $settings ) {

						$settings = bkap_json_decode( $settings, true );

						if ( is_array( $settings ) ) {

							if ( isset( $settings[ $key ] ) && '' !== $settings[ $key ] ) {

								if ( is_array( $settings[ $key ] ) ) {

									if ( $option_key === self::$subscription_key ) {

										// Remove all former values of hook action and label, thereby to allow for unique value.
										$__settings = $settings[ $key ];
										foreach ( $__settings as $index => $setting ) {

											if ( isset( $setting['action'] ) && isset( $record['action'] ) && isset( $record['label'] ) ) {
												if ( $setting['label'] === $record['label'] && $setting['action'] === $record['action'] ) {
													unset( $__settings[ $index ] );
												}
											} elseif ( ! isset( $setting['action'] ) && isset( $record['action'] ) && isset( $record['label'] ) ) {
												if ( $setting['label'] === $record['label'] ) {
													unset( $__settings[ $index ] );
												}
											}
										}

										$settings[ $key ] = $__settings;
									}

									$settings[ $key ][] = $record;
								} else {
									$_settings[ $key ][] = $settings[ $key ];
									$_settings[ $key ][] = $record;
									$settings            = $_settings;
								}
							} else {
								$settings[ $key ][] = $record;
							}
						} else {
							$_settings[]         = $settings;
							$_settings[ $key ][] = $record;
							$settings            = $_settings;
						}
					} else {
						$settings           = array();
						$settings[ $key ][] = $record;
					}
				} else {
					$settings         = array();
					$settings[ $key ] = $record;
				}
			}

			return update_option( $option_key, wp_json_encode( $settings ) );
		}

		/**
		 * Prepares the Zapier API Setting record that is to be saved to the database.
		 *
		 * @param array $input Zapier API Settings.
		 * @return string Zapier API Settings.
		 * @since 5.11.0
		 */
		public static function bkap_api_zapier_prepare_records_for_saving_to_db( $input ) {

			// Create Database Table for Zapier Log if it does not exist.
			if ( isset( $input ) && isset( $input['bkap_api_zapier_log_enable'] ) && 'on' === $input['bkap_api_zapier_log_enable'] ) {
				self::maybe_create_log_table();
			}

			return is_array( $input ) ? wp_json_encode( $input ) : $input;
		}

		/**
		 * Returns WP_Error in the format Zapier can understand.
		 *
		 * @since 5.11.0
		 * @param WP_Error $error WP_Error class.
		 */
		public static function bkap_api_zapier_error( $error ) {
			$error_message = $error->get_error_message();
			status_header( 400 );
			die( $error_message ); // phpcs:ignore
		}

		/**
		 * Triggers Zap when a booking triggger is executed.
		 *
		 * @since 5.11.0
		 * @param int    $booking_id Booking ID.
		 * @param string $trigger Trigger that has been executed for which this function should be called.
		 * @throws Exception If error encountered.
		 */
		public static function bkap_api_zapier_do_booking_trigger( $booking_id, $trigger ) {

			try {

				$booking_data = self::bkap_api_zapier_get_booking( $booking_id );
				$booking_id   = $booking_data['id'];
				$product_id   = $booking_data['product_id'];

				$is_booking_trigger_enabled             = false;
				$is_booking_trigger_enabled_for_product = false;
				$trigger_label                          = '';
				$hook                                   = '';

				switch ( $trigger ) {

					case 'create_booking_trigger':
						$trigger_label                          = 'Create Booking';
						$is_booking_trigger_enabled             = self::bkap_api_zapier_is_create_booking_trigger_enabled();
						$is_booking_trigger_enabled_for_product = self::bkap_api_zapier_is_create_booking_trigger_enabled_for_product( $product_id );
						$hook                                   = self::bkap_api_zapier_get_create_booking_trigger_product_hook( $product_id );
						break;

					case 'update_booking_trigger':
						$trigger_label                          = 'Update Booking';
						$is_booking_trigger_enabled             = self::bkap_api_zapier_is_update_booking_trigger_enabled();
						$is_booking_trigger_enabled_for_product = self::bkap_api_zapier_is_update_booking_trigger_enabled_for_product( $product_id );
						$hook                                   = self::bkap_api_zapier_get_update_booking_trigger_product_hook( $product_id );
						break;

					case 'delete_booking_trigger':
						$trigger_label                          = 'Delete Booking';
						$is_booking_trigger_enabled             = self::bkap_api_zapier_is_delete_booking_trigger_enabled();
						$is_booking_trigger_enabled_for_product = self::bkap_api_zapier_is_delete_booking_trigger_enabled_for_product( $product_id );
						$hook                                   = self::bkap_api_zapier_get_delete_booking_trigger_product_hook( $product_id );
						break;

					default:
						throw new Exception( __( 'Zapier API Trigger Request not understood.', 'woocommerce-booking' ), 400 );
				}

				if ( ! self::bkap_api_zapier_is_zapier_enabled() ) {
					throw new Exception( __( 'Zapier API is disabled. Please enable in WooCommerce Booking Settings.', 'woocommerce-booking' ), 400 );
				}

				if ( ! $is_booking_trigger_enabled ) {
					throw new Exception(
						sprintf(
							/* translators: %s Trigger Label */
							__( 'Zapier API %s Trigger is disabled. Please enable in WooCommerce Booking Settings.', 'woocommerce-booking' ),
							$trigger_label
						),
						400
					);
				}

				if ( ! $is_booking_trigger_enabled_for_product ) {
					throw new Exception(
						sprintf(
							/* translators: %d Booking ID */
							__( 'Zapier API %1$s Trigger is disabled for Product #%2$d. Please enable in WooCommerce Booking Settings.', 'woocommerce-booking' ),
							$trigger_label,
							$product_id
						),
						400
					);
				}

				if ( '' === $hook ) {
					throw new Exception(
						sprintf(
							/* translators: %s Trigger Label */
							__( 'Invalid or missing Zap Label for the Zapier API %s Trigger. Please check that the Zap is enabled and active on Zapier. Alternatively, you can switch the Zap off and back on.', 'woocommerce-booking' ),
							$trigger_label
						),
						400
					);
				}

				// Check if booking requires confirmation. If it does, do not proceed as booking status is not confirmed.
				if ( bkap_common::bkap_product_requires_confirmation( $product_id ) ) {

					$booking = new BKAP_Booking( $booking_id );

					if ( 'confirmed' !== $booking->get_status() ) {
						return;
					}
				}

				$response = wp_remote_post(
					$hook,
					array(
						'body' => wp_json_encode( $booking_data ),
					)
				);

				if ( $response ) {
					self::add_log( "{$trigger_label} Trigger", "{$trigger_label} Trigger request for Booking #{$booking_id} has been successfully sent to Zapier", $booking_data );
				}
			} catch ( Exception $e ) {
				self::add_log( "{$trigger_label} Trigger Error", $e->getMessage(), $booking_data );
			}
		}

		/**
		 * Create Booking Trigger request sent to Zapier.
		 *
		 * @since 5.11.0
		 * @param int   $booking_id Booking ID.
		 * @param array $data Booking data.
		 */
		public function bkap_api_zapier_create_booking_trigger( $booking_id, $data ) {

			$this->bkap_api_zapier_do_booking_trigger( $booking_id, 'create_booking_trigger' );
		}

		/**
		 * Update Booking Trigger request sent to Zapier.
		 *
		 * @since 5.11.0
		 * @param int   $booking_id Booking ID.
		 * @param array $data Booking data.
		 */
		public function bkap_api_zapier_update_booking_trigger( $booking_id, $data ) {

			$this->bkap_api_zapier_do_booking_trigger( $booking_id, 'update_booking_trigger' );
		}

		/**
		 * Delete Booking Trigger request sent to Zapier.
		 *
		 * @since 5.11.0
		 * @param int   $booking_id Booking ID.
		 * @param array $data Booking data.
		 */
		public function bkap_api_zapier_delete_booking_trigger( $booking_id, $data ) {

			$this->bkap_api_zapier_do_booking_trigger( $booking_id, 'delete_booking_trigger' );
		}

		/**
		 * Gets Booking data that will be returned to Zapier.
		 *
		 * @since 5.11.0
		 * @param int   $booking_id Booking ID.
		 * @param array $options Booking Options.
		 * @return array Booking data.
		 */
		public static function bkap_api_zapier_get_booking( $booking_id, $options = array() ) {

			// Check is request is for sample_data.
			$for_sample_data = false;
			if ( isset( $options['sample_data'] ) && true === $options['sample_data'] ) {
				$for_sample_data = true;
			} else {

				$booking = new BKAP_Booking( $booking_id );

				$booking_label = 'Booking #' . $booking->get_id();

				$product = wc_get_product( $booking->get_product_id() );
				if ( $product ) {
					$booking_label = $booking_label . ' for Product - ' . $product->get_name();
				}

				$customer       = $booking->get_customer();
				$customer_name  = $customer->name;
				$customer_email = $customer->email;

				$start_date = gmdate( 'Y-m-d', strtotime( strval( $booking->get_start() ) ) );
				$end_date   = gmdate( 'Y-m-d', strtotime( strval( $booking->get_end() ) ) );

				$amount = 0;
				$order  = wc_get_order( $booking->get_order_id() );
				if ( $order ) {
					$amount = $order->get_total();
				}
			}

			return array(
				'id'                     => ! $for_sample_data ? (int) $booking->get_id() : 0,
				'label'                  => ! $for_sample_data ? strval( $booking_label ) : 'Test Booking - This Booking data is for Testing Purposes',
				'order_id'               => ! $for_sample_data ? (int) $booking->get_order_id() : 0,
				'order_item_id'          => ! $for_sample_data ? (int) $booking->get_item_id() : 0,
				'booking_status'         => ! $for_sample_data ? strval( $booking->get_status() ) : 'test-booking',
				'customer_id'            => ! $for_sample_data ? (int) $booking->get_customer_id() : 0,
				'customer_name'          => ! $for_sample_data ? strval( $customer_name ) : 'James Macover',
				'customer_email'         => ! $for_sample_data ? strval( $customer_email ) : 'james@macover.com',
				'product_id'             => ! $for_sample_data ? (int) $booking->get_product_id() : 0,
				'start_date'             => ! $for_sample_data ? strval( $start_date ) : gmdate( 'Y-m-d ' ),
				'end_date'               => ! $for_sample_data ? strval( $end_date ) : gmdate( 'Y-m-d ' ),
				'start_time'             => ! $for_sample_data ? strval( $booking->get_start_time() ) : '09:00 AM',
				'end_time'               => ! $for_sample_data ? strval( $booking->get_end_time() ) : '10:00 AM',
				'resource_id'            => ! $for_sample_data ? (int) $booking->get_resource() : 0,
				'fixed_block'            => ! $for_sample_data ? strval( $booking->get_fixed_block() ) : '4-days',
				'unit_cost'              => ! $for_sample_data ? (float) $booking->get_cost() : 100,
				'quantity'               => ! $for_sample_data ? (int) $booking->get_quantity() : 5,
				'variation_id'           => ! $for_sample_data ? (int) $booking->get_variation_id() : 0,
				'resource_title'         => ! $for_sample_data ? strval( $booking->get_resource_title() ) : 'Number of Rooms',
				'duration'               => ! $for_sample_data ? strval( $booking->get_selected_duration() ) : '5 days',
				'duration_time'          => ! $for_sample_data ? strval( $booking->get_selected_duration_time() ) : '5',
				'client_timezone'        => ! $for_sample_data ? strval( $booking->get_timezone_name() ) : 'Asia/Kolkata',
				'client_timezone_offset' => ! $for_sample_data ? strval( $booking->get_timezone_offset() ) : '10',
				'zoom_meeting_url'       => ! $for_sample_data ? strval( $booking->get_zoom_meeting_link() ) : 'https://zoom.us/test-booking',
				'total_amount'           => ! $for_sample_data ? (float) $amount : 500,
			);
		}

		/**
		 * Gets the Zapier Hook URL.
		 *
		 * @since 5.14.0
		 * @param string $action Hook Action.
		 * @param string $hook Hook.
		 * @param int    $product_id Product ID.
		 * @param array  $data Data of information needed to retrieve Hook URL.
		 */
		public static function bkap_api_zapier_get_zapier_hook_url( $action, $hook, $product_id ) {

			$_hook = self::bkap_api_zapier_get_product_trigger_setting( $product_id, $hook, 'hook' );
			$label = self::bkap_api_zapier_get_product_trigger_setting( $product_id, $hook, 'label' );

			if ( '' === $label && '' !== $_hook ) {
				// Before we started using label as key.
				$hook_data = self::bkap_api_zapier_fetch_subscription_information( $action, 'url', $_hook );
				$label     = isset( $hook_data->label ) ? $hook_data->label : '';
			}

			$label_data = self::bkap_api_zapier_fetch_subscription_information( $action, 'label', $label );
			return isset( $label_data->url ) ? $label_data->url : '';
		}

		/**
		 * Fetch Zapier Subscription information.
		 *
		 * @since 5.14.0
		 * @param string $action Hook Action
		 * @param string $key Subscription Key.
		 * @param string $value Subscription Value.
		 */
		public static function bkap_api_zapier_fetch_subscription_information( $action, $key, $value ) {

			$subscriptions = (array) self::bkap_api_zapier_get_subscriptions( $action );

			if ( '' !== $subscriptions && is_array( $subscriptions ) && count( $subscriptions ) > 0 ) {

				// Reverse array to have most recent items at the top.
				$subscriptions = array_reverse( $subscriptions );

				foreach ( $subscriptions as $subscription ) {
					if ( isset( $subscription->{$key} ) && $subscription->{$key} === $value && isset( $subscription->action ) && $action === $subscription->action ) {
						return $subscription;
					}
				}
			}

			return '';
		}

		/**
		 * Returns the Zapier Logs.
		 *
		 * @global wpdb $wpdb
		 * @since 5.11.0
		 */
		public static function get_logs() {
			global $wpdb;

			$logs = array();

			self::maybe_create_log_table();

			$query_items = "
				SELECT log_id, timestamp, action, message
				FROM {$wpdb->prefix}" . self::$database_table . '
				ORDER BY timestamp DESC, log_id DESC
			';

			$items = $wpdb->get_results( $query_items, ARRAY_A ); // phpcs:ignore

			foreach ( $items as $item ) {
				$logs[] = array(
					'timestamp' => gmdate(
						'Y-m-d H:i:s',
						$item['timestamp']
					),
					'action'    => strpos( strtolower( $item['action'] ), 'error' ) !== false ? '<span style="color:red;font-weight:bold">' . $item['action'] . '</span>' : $item['action'],
					'details'   => $item['message'],
				);
			}

			return $logs;
		}

		/**
		 * Creates log table if it does not exist.
		 *
		 * @global wpdb $wpdb
		 *
		 * @return bool False on error, true if already exists or success.
		 * @since 5.11.0
		 */
		public static function maybe_create_log_table() {
			global $wpdb;

			$collate = '';

			if ( $wpdb->has_cap( 'collation' ) ) {
				$collate = $wpdb->get_charset_collate();
			}

			return self::maybe_create_table(
				$wpdb->prefix . self::$database_table,
				"
				CREATE TABLE {$wpdb->prefix}" . self::$database_table . " (
					log_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
					timestamp BIGINT UNSIGNED NOT NULL,
					action varchar(200) NOT NULL,
					message longtext NOT NULL,
					request longtext NULL,
					PRIMARY KEY (log_id)
				  ) $collate;
				"
			);
		}

		/**
		 * Creates a table in the database if it doesn't already exist.
		 *
		 * @since 5.11.0
		 *
		 * @global wpdb $wpdb.
		 *
		 * @param string $table_name Database table name.
		 * @param string $sql SQL statement to create table.
		 * @return bool True on success or if the table already exists. False on failure.
		 */
		public static function maybe_create_table( $table_name, $sql ) {
			global $wpdb;

			foreach ( $wpdb->get_col( 'SHOW TABLES', 0 ) as $table ) { // phpcs:ignore
				if ( $table === $table_name ) {
					return true;
				}
			}

			$wpdb->query( $sql ); // phpcs:ignore

			foreach ( $wpdb->get_col( 'SHOW TABLES', 0 ) as $table ) { // phpcs:ignore
				if ( $table === $table_name ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Adds log entry to the database.
		 *
		 * @param string $action Action.
		 * @param string $message Log message.
		 * @param array  $request Request parameter or any important information that will be serialized and stored in the database.
		 * @since 5.11.0
		 *
		 * @return bool True if log entry action was successful.
		 */
		public static function add_log( $action, $message, $request = '' ) {
			global $wpdb;

			// Don't log if Logging has not been enabled in Zapier API Settings.
			if ( ! self::bkap_api_zapier_is_logging_enabled() ) {
				return;
			}

			$insert = array(
				'timestamp' => strtotime( 'now' ),
				'action'    => $action,
				'message'   => $message,
				'request'   => $request,
			);

			$format = array(
				'%d',
				'%s',
				'%s',
				'%s',
			);

			if ( ! empty( $request ) ) {
				$insert['request'] = var_export( $request, true ); // phpcs:ignore
			}

			return false !== $wpdb->insert( "{$wpdb->prefix}" . self::$database_table, $insert, $format ); // phpcs:ignore
		}

		/**
		 * Clear all zapier logs from the DB.
		 *
		 * @return bool True if flush was successful.
		 * @since 5.11.0
		 */
		public static function flush() {
			global $wpdb;

			return $wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}" . self::$database_table ); // phpcs:ignore
		}

		/**
		 * Delete selected logs from DB.
		 *
		 * @param int|string|array $log_ids Log ID or array of Log IDs to be deleted.
		 *
		 * @return bool
		 * @since 5.11.0
		 */
		public static function delete( $log_ids ) {
			global $wpdb;

			if ( ! is_array( $log_ids ) ) {
				$log_ids = array( $log_ids );
			}

			$format   = array_fill( 0, count( $log_ids ), '%d' );
			$query_in = '(' . implode( ',', $format ) . ')';
			return $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}" . self::$database_table . " WHERE log_id IN {$query_in}", $log_ids ) ); // phpcs:ignore
		}

		/**
		 * Bulk delete Zapier log.
		 *
		 * @since 5.11.0
		 */
		private static function log_bulk_actions() {
			if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bkap-api-zapier-log-status' ) ) { // phpcs:ignore
				wp_die( esc_html__( 'Action failed. Please refresh the page and try and again.', 'woocommerce-booking' ) );
			}

			$log_ids = array_map( 'absint', (array) isset( $_REQUEST['log'] ) ? wp_unslash( $_REQUEST['log'] ) : array() ); // phpcs:ignore

			if ( ( isset( $_REQUEST['action'] ) && 'delete' === $_REQUEST['action'] ) ) { // phpcs:ignore
				self::delete( $log_ids );
				wp_safe_redirect( esc_url_raw( admin_url( 'edit.php?post_type=bkap_booking&page=woocommerce_booking_page&action=calendar_sync_settings&section=zapier#bkap_api_zapier_event_log' ) ) );
				exit();
			}
		}
	}
}
