<?php
/**
 * Booking & Appointment Plugin for WooCommerce BKAP_Google_Calendar_Event.
 *
 * Handler for adding imported events as a post.
 *
 * @version  4.2.0
 * @category Class
 * @author   Tyche Softwares
 * @package  BKAP/Google-Calendar-Sync
 * @class    BKAP_Google_Calendar_Event
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handler for adding imported events as a post.
 *
 * @class    BKAP_Google_Calendar_Event
 */
class BKAP_Google_Calendar_Event {

	/**
	 * Google Event ID
	 *
	 * @var int $id
	 */
	public $id;

	/**
	 * Google Event Data Populated
	 *
	 * @var bool $populated
	 */
	public $populated;

	/**
	 * Google Event Post created date.
	 *
	 * @var string $booking_date
	 */
	public $booking_date;

	/**
	 * Google Event Post modified date.
	 *
	 * @var string $modified_date
	 */
	public $modified_date;

	/**
	 * Customer ID.
	 *
	 * @var string $customer_id
	 */
	public $customer_id;

	/**
	 * All Custom Fields of Google Event.
	 *
	 * @var array $custom_fields
	 */
	public $custom_fields;

	/**
	 * Order ID of Google Event.
	 *
	 * @var int $order_id
	 */
	public $order_id;

	/**
	 * User ID of Google Event.
	 *
	 * @var int $user_id
	 */
	public $user_id;

	/**
	 * Product ID of Google Event.
	 *
	 * @var int $product_id
	 */
	public $product_id;

	/**
	 * Start Date of Google Event.
	 *
	 * @var int $start
	 */
	public $start;

	/**
	 * End Date of Google Event.
	 *
	 * @var int $end
	 */
	public $end;

	/**
	 * Unique ID of Google Event.
	 *
	 * @var string $uid
	 */
	public $uid;

	/**
	 * Summary of Google Event.
	 *
	 * @var string $summary
	 */
	public $summary;

	/**
	 * Description of Google Event.
	 *
	 * @var string $description
	 */
	public $description;

	/**
	 * Location of Google Event.
	 *
	 * @var string $location
	 */
	public $location;

	/**
	 * Resource ID.
	 *
	 * @var int $resource_id
	 */
	public $resource_id;

	/**
	 * Persons info.
	 *
	 * @var int $persons
	 */
	public $persons;

	/**
	 * Quantity
	 *
	 * @var int $qty
	 */
	public $qty;

	/**
	 * Google Event Post
	 *
	 * @var obj $post
	 */
	public $post;

	/**
	 * Google Event Status
	 *
	 * @var string $status
	 */
	public $status;

	/**
	 * Constructor, possibly sets up with post or id belonging to existing booking or supplied with an array to construct a new booking
	 *
	 * @param int|array|obj $gcal_event Google Calendar Event object.
	 * @since 4.2.0
	 */
	public function __construct( $gcal_event = false ) {
		if ( is_array( $gcal_event ) ) {
			$this->event_data = $gcal_event;
			$this->populated  = false;
		} elseif ( intval( $gcal_event ) && 0 < $gcal_event ) {
			$this->populated = $this->populate_data( $gcal_event );
		}
	}

	/**
	 * Populate the data with the id of the event provided.
	 * Will query for the post belonging to this event and store it.
	 *
	 * @param int $event_id Google Event ID.
	 * @since 4.2.0
	 */
	public function populate_data( $event_id ) {

		if ( ! isset( $this->post ) ) {
			$post       = get_post( $event_id );
			$this->post = $post;
		}

		if ( is_object( $post ) ) {

			// We have the post object belonging to this booking, now let's populate.
			$this->id            = $post->ID;
			$this->booking_date  = $post->post_date;
			$this->modified_date = $post->post_modified;
			$this->customer_id   = $post->post_author;
			$this->custom_fields = get_post_meta( $this->id );
			$this->status        = $post->post_status;
			$this->order_id      = $post->post_parent;

			// Define the data we're going to load: Key => Default value.
			$load_data = array(
				'user_id'     => 0,
				'product_id'  => '',
				'start'       => '',
				'end'         => '',
				'uid'         => '',
				'summary'     => '',
				'description' => '',
				'location'    => '',
				'resource_id' => '',
				'persons'     => array(),
				'qty'         => 1,
			);

			// Load the data from the custom fields (with prefix for this plugin ).
			$meta_prefix = '_bkap_';

			foreach ( $load_data as $key => $default ) {
				$this->$key = $default;

				if ( isset( $this->custom_fields[ $meta_prefix . $key ][0] ) && '' !== $this->custom_fields[ $meta_prefix . $key ][0] ) {
					$this->$key = maybe_unserialize( $this->custom_fields[ $meta_prefix . $key ][0] );
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Actual create for the new event.
	 *
	 * @param string $status Status for new event.
	 * @since 4.2.0
	 */
	public function create( $status = 'bkap-unmapped' ) {
		$this->new_booking( $status, $this->event_data );
	}

	/**
	 * Makes the new event.
	 *
	 * @param string $status The status for this new event.
	 * @param array  $event_data Array with all the new event data.
	 *
	 * @since 4.2.0
	 */
	private function new_booking( $status, $event_data ) {

		$event_data = wp_parse_args(
			$event_data,
			array(
				'user_id'           => 1,
				'product_id'        => '',
				'start_date'        => '',
				'end_date'          => '',
				'uid'               => '',
				'summary'           => '',
				'description'       => '',
				'location'          => '',
				'reason_of_fail'    => '',
				'event_option_name' => '',
				'resource_id'       => '',
				'persons'           => array(),
				'qty'               => 1,
			)
		);

		$booking_event_data = array(
			'post_type'   => 'bkap_gcal_event',
			'post_title'  => sprintf( __( 'Google Event &ndash; %s', 'woocommerce-booking' ), strftime( _x( '%1$b %2$d, %Y @ %I:%M %p', 'Booking date parsed by strftime', 'woocommerce-booking' ) ) ), // phpcs:ignore
			'post_status' => $status,
			'ping_status' => 'closed',
			'post_author' => $event_data['user_id'],
		);

		$this->id = wp_insert_post( $booking_event_data );

		// Setup the required data for the current user.
		if ( ! $event_data['user_id'] ) {
			if ( is_user_logged_in() ) {
				$event_data['user_id'] = get_current_user_id();
			} else {
				$event_data['user_id'] = 0;
			}
		}

		$event_meta_args = array(
			'_bkap_user_id'           => $event_data['user_id'],
			'_bkap_product_id'        => $event_data['product_id'],
			'_bkap_resource_id'       => $event_data['resource_id'],
			'_bkap_persons'           => $event_data['persons'],
			'_bkap_start'             => $event_data['start_date'],
			'_bkap_end'               => $event_data['end_date'],
			'_bkap_uid'               => $event_data['uid'],
			'_bkap_summary'           => $event_data['summary'],
			'_bkap_description'       => $event_data['description'],
			'_bkap_location'          => $event_data['location'],
			'_bkap_reason_of_fail'    => $event_data['reason_of_fail'],
			'_bkap_customer_id'       => $event_data['user_id'],
			'_bkap_qty'               => $event_data['qty'],
			'_bkap_variation_id'      => $event_data['variation_id'],
			'_bkap_event_option_name' => $event_data['event_option_name'],

		);

		foreach ( $event_meta_args as $key => $value ) {
			update_post_meta( $this->id, $key, $value );
		}
	}

	/**
	 * Returns the status of this booking.
	 *
	 * @param bool $raw True if want raw value instead of formatted one.
	 *
	 * @since 4.2.0
	 */
	public function get_status( $raw = true ) {

		if ( $this->populated ) {
			if ( $raw ) {
				return $this->status;
			} else {
				$status_object = get_post_status_object( $this->status );
				return $status_object->label;
			}
		}

		return false;
	}

	/**
	 * Returns the Start Date
	 *
	 * @return Start Date as strtotime
	 *
	 * @since 4.2.0
	 */
	public function get_start() {
		$start = get_post_meta( $this->id, '_bkap_start', true );
		return $start;
	}

	/**
	 * Returns the End Date
	 *
	 * @return End Date as strtotime
	 * @since 4.2.0
	 */
	public function get_end() {
		$end = get_post_meta( $this->id, '_bkap_end', true );
		return $end;
	}

	/**
	 * Returns the Time Slot.
	 *
	 * @return Time Slot as H:i.
	 * @since 4.2.0
	 */
	public function get_time() {
		$global_settings = json_decode( get_option( 'woocommerce_booking_global_settings' ) );
		$time_format     = $global_settings->booking_time_format;

		// Commenting since we need 24 hour format for comparision.
		// $time_format = ( $time_format === '12' ) ? 'h:i A' : 'H:i';.
		$time_format = 'H:i';
		$start_time  = gmdate( $time_format, strtotime( $this->get_start() ) ); // phpcs:ignore
		$end_time    = gmdate( $time_format, strtotime( $this->get_end() ) ); // phpcs:ignore

		return "$start_time - $end_time";
	}

	/**
	 * Returns the Start Date.
	 *
	 * @return Date in the format set in Booking->Settings->Date Format.
	 * @since 4.2.0
	 */
	public function get_start_date() {

		$start_date      = '';
		$start           = $this->get_start();
		$global_settings = json_decode( get_option( 'woocommerce_booking_global_settings' ) );
		$date_formats    = bkap_common::get_booking_global_value( 'bkap_date_formats' );

		// get the global settings to find the date formats.
		$global_settings = json_decode( get_option( 'woocommerce_booking_global_settings' ) );
		$date_format_set = $date_formats[ $global_settings->booking_date_format ];

		if ( $start ) {
			$start_date = gmdate( $date_format_set, $start ); // phpcs:ignore
		}

		return $start_date;
	}

	/**
	 * Returns the End Date.
	 *
	 * @return Date in the format set in Booking->Settings->Date Format.
	 * @since 4.2.0
	 */
	public function get_end_date() {
		$end_date = '';
		$start    = $this->get_start();
		$end      = $this->get_end();

		if ( $start !== $end && ! is_null( $end ) && '' !== $end ) {
			$global_settings = json_decode( get_option( 'woocommerce_booking_global_settings' ) );
			$date_formats    = bkap_common::get_booking_global_value( 'bkap_date_formats' );

			// get the global settings to find the date formats.
			$global_settings = json_decode( get_option( 'woocommerce_booking_global_settings' ) );
			$date_format_set = $date_formats[ $global_settings->booking_date_format ];
			$end_date        = gmdate( $date_format_set, $end ); // phpcs:ignore
		}

		return $end_date;
	}

	/**
	 * Returns Start Time.
	 *
	 * @return Start Time in the format set in Booking->Settings->Time Format.
	 * @since 4.2.0
	 */
	public function get_start_time() {

		$start_time           = '';
		$global_settings      = json_decode( get_option( 'woocommerce_booking_global_settings' ) );
		$time_format          = $global_settings->booking_time_format;
		$time_format          = ( '12' === $time_format ) ? 'h:i A' : 'H:i';
		$tdif                 = $this->get_tdif();
		$start                = $this->get_start();
		$start_time_timestamp = $start + $tdif;

		if ( $start ) {
			$start_time = gmdate( $time_format, $start_time_timestamp ); // phpcs:ignore
		}

		return $start_time;
	}

	/**
	 * Returns Start Time.
	 *
	 * @return Start Time in the format set in Booking->Settings->Time Format.
	 * @since 4.2.0
	 */
	public function get_end_time() {

		$end_time        = '';
		$global_settings = json_decode( get_option( 'woocommerce_booking_global_settings' ) );
		$time_format     = $global_settings->booking_time_format;
		$time_format     = ( '12' === $time_format ) ? 'h:i A' : 'H:i';
		$tdif            = $this->get_tdif();
		$end             = $this->get_end();

		if ( $end ) {
			$end_time_timestamp = $end + $tdif;
			$end_time           = gmdate( $time_format, $end_time_timestamp ); // phpcs:ignore
		}

		return $end_time;
	}

	/**
	 * Calculate the time difference for UTC and the TimeZone set for the website.
	 *
	 * @return Time Difference $tdif.
	 * @since 4.2.0
	 */
	public function get_tdif() {
		return ( ! current_time( 'timestamp' ) ) ? 0 : current_time( 'timestamp' ) - time(); // phpcs:ignore
	}

	/**
	 * Failed reason of the mapping of event.
	 *
	 * @return string $failed_reason.
	 * @since 4.2.0
	 */
	public function get_failed_reason() {
		return get_post_meta( $this->id, '_bkap_reason_of_fail', true );
	}

	/**
	 * Set the new status for this event.
	 *
	 * @param string $status Status of Google Event.
	 * @return bool
	 */
	public function update_status( $status ) {

		$current_status   = $this->get_status( true );
		$allowed_statuses = bkap_common::get_bkap_event_statuses();

		if ( $this->populated ) {
			if ( array_key_exists( $status, $allowed_statuses ) ) {
				wp_update_post(
					array(
						'ID'          => $this->id,
						'post_status' => $status,
					)
				);

				// Trigger actions.
				do_action( 'bkap_post_' . $current_status . '_to_' . $status, $this->id );
				do_action( 'bkap_post_' . $status, $this->id );

				return true;
			}
		}

		return false;
	}
}
