<?php
/**
 * Bookings and Appointment Plugin for WooCommerce.
 *
 * REST API for Resources.
 *
 * @author      Tyche Softwares
 * @package     BKAP/Admin/API/Resources
 * @category    Classes
 * @since       5.19.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * BKAP REST API.
 *
 * @since 5.19.0
 */
class BKAP_Admin_API_Resources extends BKAP_Admin_API {

	/**
	 * Construct
	 *
	 * @since 5.19.0
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_endpoints' ) );
	}

	/**
	 * Function for registering the API endpoints.
	 *
	 * @since 5.19.0
	 */
	public static function register_endpoints() {

		// Fetch Resource data.
		register_rest_route(
			self::$base_endpoint,
			'resources/fetch',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'fetch_resources_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Fetch Resource by ID.
		register_rest_route(
			self::$base_endpoint,
			'resources/fetch-resource',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'fetch_resource' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Table Data.
		register_rest_route(
			self::$base_endpoint,
			'resources/table/display',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'return_table_data' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		register_rest_route(
			self::$base_endpoint,
			'resources/save-update',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'save_update_resource' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Trash Resource.
		register_rest_route(
			self::$base_endpoint,
			'resources/trash-resource',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'trash_resource' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Restore Resource.
		register_rest_route(
			self::$base_endpoint,
			'resources/restore-resource',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'restore_resource' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Delete Resource.
		register_rest_route(
			self::$base_endpoint,
			'resources/delete-resource',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'delete_resource' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);

		// Delete Trashed Resources.
		register_rest_route(
			self::$base_endpoint,
			'resources/delete-trashed-resources',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'delete_trashed_resources' ),
				'permission_callback' => array( __CLASS__, 'get_permission' ),
			)
		);
	}

	/**
	 * Returns Resources Data.
	 *
	 * @param bool $return_raw Whether to return the Raw response.
	 *
	 * @since 5.19.0
	 */
	public static function fetch_resources_data( $return_raw = false ) {

		$bkap_intervals = bkap_intervals();

		$response = array(
			'user_list_can_be_retrieved' => bkap_zoom_connection()->user_list_can_be_retrieved(),
			'user_list'                  => bkap_zoom_connection()->user_list(),
			'intervals'                  => $bkap_intervals,
			'range_type_general'         => array(
				'custom' => $bkap_intervals['type']['custom'],
				'months' => $bkap_intervals['type']['months'],
				'weeks'  => $bkap_intervals['type']['weeks'],
				'days'   => $bkap_intervals['type']['days'],
			),
			'range_type_time_data'       => $bkap_intervals['type']['time_data'],
			'labels'                     => array(
				'yes' => __( 'Yes', 'woocommerce-booking' ),
				'no'  => __( 'No', 'woocommerce-booking' ),
			),
		);

		return self::return_response( $response, $return_raw );
	}

	/**
	 * Returns Resource Data via ID.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function fetch_resource( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$resource_id   = $request->get_param( 'resource_id' );
		$resource_data = array(
			'id'           => $resource_id,
			'title'        => get_the_title( $resource_id ),
			'qty'          => get_post_meta( $resource_id, '_bkap_resource_qty', true ),
			'menu_order'   => get_post_meta( $resource_id, '_bkap_resource_menu_order', true ),
			'meeting_host' => get_post_meta( $resource_id, '_bkap_resource_meeting_host', true ),
		);

		$availability             = get_post_meta( $resource_id, '_bkap_resource_availability', true );
		$manage_availability_data = array();

		if ( is_array( $availability ) && count( $availability ) > 0 ) {
			foreach ( $availability as $key => $value ) {
				$manage_availability_data[] = array(
					'range_type'                           => self::check( $value, 'type', '' ),
					'edit'                                 => false,
					'range_days_from'                      => 'days' === self::check( $value, 'type', '' ) ? self::check( $value, 'from', '' ) : '',
					'range_days_to'                        => 'days' === self::check( $value, 'type', '' ) ? self::check( $value, 'to', '' ) : '',
					'range_months_from'                    => 'months' === self::check( $value, 'type', '' ) ? self::check( $value, 'from', '' ) : '',
					'range_months_to'                      => 'months' === self::check( $value, 'type', '' ) ? self::check( $value, 'to', '' ) : '',
					'range_weeks_from'                     => 'weeks' === self::check( $value, 'type', '' ) ? self::check( $value, 'from', '' ) : '',
					'range_weeks_to'                       => 'weeks' === self::check( $value, 'type', '' ) ? self::check( $value, 'to', '' ) : '',
					'range_date_from'                      => 'custom' === self::check( $value, 'type', '' ) ? self::check( $value, 'from', '' ) : '',
					'range_date_to'                        => 'custom' === self::check( $value, 'type', '' ) ? self::check( $value, 'to', '' ) : '',
					'range_date_from_formatted'            => 'custom' === self::check( $value, 'type', '' ) ? gmdate( 'd/m/Y', strtotime( self::check( $value, 'from', '' ) ) ) : '',
					'range_date_to_formatted'              => 'custom' === self::check( $value, 'type', '' ) ? gmdate( 'd/m/Y', strtotime( self::check( $value, 'to', '' ) ) ) : '',
					'range_time_from'                      => ( 'time' === self::check( $value, 'type', '' ) || preg_match( '/[0-9]/', self::check( $value, 'type', '' ) ) ) ? self::check( $value, 'from', '' ) : '',
					'range_time_to'                        => ( 'time' === self::check( $value, 'type', '' ) || preg_match( '/[0-9]/', self::check( $value, 'type', '' ) ) ) ? self::check( $value, 'to', '' ) : '',
					'range_time_range_date_from'           => 'time:range' === self::check( $value, 'type', '' ) ? self::check( $value, 'from_date', '' ) : '',
					'range_time_range_date_to'             => 'time:range' === self::check( $value, 'type', '' ) ? self::check( $value, 'to_date', '' ) : '',
					'range_time_range_date_from_formatted' => 'time:range' === self::check( $value, 'type', '' ) ? gmdate( 'd/m/Y', strtotime( self::check( $value, 'from_date', '' ) ) ) : '',
					'range_time_range_date_to_formatted'   => 'time:range' === self::check( $value, 'type', '' ) ? gmdate( 'd/m/Y', strtotime( self::check( $value, 'to_date', '' ) ) ) : '',
					'range_time_range_time_from'           => 'time:range' === self::check( $value, 'type', '' ) ? self::check( $value, 'from', '' ) : '',
					'range_time_range_time_to'             => 'time:range' === self::check( $value, 'type', '' ) ? self::check( $value, 'to', '' ) : '',
					'priority'                             => self::check( $value, 'priority', 10 ),
					'bookable'                             => 1 === (int) self::check( $value, 'bookable', '' ) ? 'on' : '',
					'is_editable'                          => true,
				);
			}
		}

		$resource_data['availability'] = $manage_availability_data;
		return self::response( 'success', array( 'data' => $resource_data ) );
	}

	/**
	 * Returns Table Data.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function return_table_data( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$data  = $request->get_param( 'data' );
		$table = self::populate_data(
			array(
				'order'   => self::check( $data, 'order', 'asc' ),
				'orderby' => self::check( $data, 'orderby', 'title' ),
				'page'    => self::check( $data, 'page', 1 ),
				'search'  => self::check( $data, 'search', '' ),
				'filter'  => self::check( $data, 'filter', array() ),
				'status'  => self::check( $data, 'status', '' ),
			)
		);

		if ( ! $table ) {
			return self::response( 'error', array( 'error_description' => __( 'Error encountered while trying to populate table.', 'woocommerce-booking' ) ) );
		}

		return self::response( 'success', $table->ajax_response() );
	}

	/**
	 * Populate Data.
	 *
	 * @param bool $data Data.
	 *
	 * @since 5.19.0
	 */
	public static function populate_data( $data ) {

		// Load WordPress Administration APIs.
		require_once ABSPATH . 'wp-admin/includes/admin.php';

		BKAP_Files::include_file( BKAP_PLUGIN_PATH . '/includes/admin/includes/view-resources/class-bkap-admin-view-resources-table.php' );

		if ( is_array( $data ) && count( $data ) > 0 ) {
			$table = new BKAP_Admin_View_Resources_Table();
			$table->populate_data( $data );

			return $table;
		}

		return false;
	}

	/**
	 * Save/Update Resource.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function save_update_resource( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$data = $request->get_param( 'data' );

		if ( ! is_array( $data ) || ( is_array( $data ) && 0 === count( $data ) ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Wrong Request', 'woocommerce-booking' ) ) );
		}

		$id                       = self::check( $data, 'id', '' );
		$title                    = self::check( $data, 'title', '' );
		$qty                      = self::check( $data, 'qty', 1 );
		$menu_order               = self::check( $data, 'menu_order', 0 );
		$meeting_host             = self::check( $data, 'meeting_host', '' );
		$availability             = self::check( $data, 'availability', array() );
		$manage_availability_data = array();

		if ( '' === $id || '' === $title ) {
			return self::response( 'error', array( 'error_description' => 'Some required fields are missing.' ) );
		}

		if ( 0 === (int) $id ) {
			$id = wp_insert_post(
				array(
					'post_title'   => $title,
					'post_content' => '',
					'post_status'  => 'publish',
					'post_type'    => 'bkap_resource',
				)
			);

			if ( ! $id || is_wp_error( $id ) ) {
				return self::response( 'error', array( 'error_description' => 'Error encountered while trying to save Resource data.' ) );
			}

			$message = __( 'Resource has been added successfully.', 'woocommerce-booking' );
		} else {
			if ( ! wp_update_post(
				array(
					'ID'          => $id,
					'post_title'  => $title,
					'post_status' => 'publish',
				)
			) ) {
				return self::response( 'error', array( 'error_description' => 'Error encountered while trying to update Resource Data' ) );
			}

			$message = __( 'Resource has been updated successfully.', 'woocommerce-booking' );
		}

		if ( is_array( $availability ) && count( $availability ) > 0 ) {
			foreach ( $availability as $value ) {
				$_data = array(
					'type'     => $value['range_type'],
					'bookable' => 'on' === $value['bookable'] ? 1 : 0,
					'priority' => $value['priority'],
				);

				switch ( $value['range_type'] ) {
					case 'custom':
						$_data['from'] = $value['range_date_from'];
						$_data['to']   = $value['range_date_to'];
						break;

					case 'time:range':
						$_data['from']      = $value['range_time_range_time_from'];
						$_data['from_date'] = $value['range_time_range_date_from'];
						$_data['to']        = $value['range_time_range_time_to'];
						$_data['to_date']   = $value['range_time_range_date_to'];
						break;

					case 'days':
					case 'months':
					case 'weeks':
						$_data['from'] = $value[ 'range_' . $value['range_type'] . '_from' ];
						$_data['to']   = $value[ 'range_' . $value['range_type'] . '_to' ];
						break;

					default:
						if ( 'time:' === substr( $value['range_type'], 0, 5 ) || 'time' === $value['range_type'] ) {
							$_data['from'] = $value['range_time_from'];
							$_data['to']   = $value['range_time_to'];
						}
						break;
				}

				$manage_availability_data[] = $_data;
			}
		}

		update_post_meta( $id, '_bkap_resource_qty', $qty );
		update_post_meta( $id, '_bkap_resource_menu_order', $menu_order );
		update_post_meta( $id, '_bkap_resource_availability', $manage_availability_data );
		update_post_meta( $id, '_bkap_resource_meeting_host', $meeting_host );

		return self::response( 'success', array( 'message' => $message ) );
	}

	/**
	 * Trash Resource.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function trash_resource( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$resource_ids = $request->get_param( 'resource_id' );

		if ( ! is_array( $resource_ids ) ) {
			$resource_ids = array( $resource_ids );
		}

		foreach ( $resource_ids as $resource_id ) {

			if ( ! current_user_can( 'delete_post', $resource_id ) ) {
				return self::response( 'error', array( 'error_description' => __( 'Sorry, you do not have the permission to move the Resource to Trash', 'woocommerce-booking' ) ) );
			}

			if ( ! wp_trash_post( $resource_id ) ) {
				return self::response( 'error', array( 'error_description' => __( 'An Error was encountered while trying to move the Resource to Trash', 'woocommerce-booking' ) ) );
			}
		}

		$message = 1 === count( $resource_ids ) ? __( 'Resource has been trashed successfully.', 'woocommerce-booking' ) : __( 'Resources have been trashed successfully.', 'woocommerce-booking' );

		return self::response( 'success', array( 'message' => $message ) );
	}

	/**
	 * Restore Resource.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function restore_resource( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$resource_ids = $request->get_param( 'resource_id' );

		if ( ! is_array( $resource_ids ) ) {
			$resource_ids = array( $resource_ids );
		}

		foreach ( $resource_ids as $resource_id ) {

			if ( ! wp_untrash_post( $resource_id ) ) {
				return self::response( 'error', array( 'error_description' => __( 'An Error was encountered while trying to restore the Resource from Trash', 'woocommerce-booking' ) ) );
			}
		}

		$message = 1 === count( $resource_ids ) ? __( 'Resource has been restored successfully.', 'woocommerce-booking' ) : __( 'Resources have been restored successfully.', 'woocommerce-booking' );

		return self::response( 'success', array( 'message' => $message ) );
	}

	/**
	 * Delete Resource.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function delete_resource( WP_REST_Request $request ) {

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}

		$resource_ids = $request->get_param( 'resource_id' );

		if ( ! is_array( $resource_ids ) ) {
			$resource_ids = array( $resource_ids );
		}

		foreach ( $resource_ids as $resource_id ) {

			if ( ! current_user_can( 'delete_post', $resource_id ) ) {
				return self::response( 'error', array( 'error_description' => __( 'Sorry, you do not have the permission to delete the Resource', 'woocommerce-booking' ) ) );
			}

			if ( ! wp_delete_post( $resource_id, true ) ) {
				return self::response( 'error', array( 'error_description' => __( 'An Error was encountered while trying to delete the Resource', 'woocommerce-booking' ) ) );
			}
		}

		$message = 1 === count( $resource_ids ) ? __( 'Resource has been deleted successfully.', 'woocommerce-booking' ) : __( 'Resources have been deleted successfully.', 'woocommerce-booking' );

		return self::response( 'success', array( 'message' => $message ) );
	}

	/**
	 * Delete Trashed Resources.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return JSON
	 *
	 * @since 5.19.0
	 */
	public static function delete_trashed_resources( WP_REST_Request $request ) {

		global $wpdb;

		if ( ! self::verify_nonce( $request, false ) ) {
			return self::response( 'error', array( 'error_description' => __( 'Authentication has failed.', 'woocommerce-booking' ) ) );
		}
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
		$ids       = (array) $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type=%s AND post_status = %s", 'bkap_resource', 'trash' ) );
		$ids_count = count( $ids );

		if ( 0 === $ids_count ) {
			return self::response( 'error', array( 'error_description' => __( 'There are no Trashed Resources for deletion.', 'woocommerce-booking' ) ) );
		}

		$id_count = 0;

		foreach ( $ids as $id ) {

			if ( wp_delete_post( $id ) ) {
				$id_count++;
			}
		}

		if ( $id_count === $ids_count ) {
			return self::response( 'success', array( 'message' => __( 'Trashed Resources have been deleted successfully.', 'woocommerce-booking' ) ) );
		} elseif ( $id_count > 0 ) {
			return self::response( 'success', array( 'message' => __( 'Some Trashed Resources have been deleted successfully. There were some that could not be deleted due to some errors.', 'woocommerce-booking' ) ) );
		}

		return self::response( 'error', array( 'error_description' => __( 'Error encountered while trying to delete the trashed items.', 'woocommerce-booking' ) ) );
	}
}
