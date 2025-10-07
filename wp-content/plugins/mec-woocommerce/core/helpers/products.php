<?php

namespace MEC_Woocommerce\Core\Helpers;

// Don't load directly
if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use MEC\Settings\Settings;
/**
*  Products.
*
*  @author      Webnus <info@webnus.biz>
*  @package     Modern Events Calendar
*  @since       1.0.0
**/
class Products {

    /**
     * Global Variables
     *
     * @since     1.0.0
     * @access     private
     */
    public static $id = 0;
    public static $access_to_run = 0;
    public static $checkout = [];
    public static $mec_settings;
    public static $gateway_options;
    public static $do_action = true;
    public static $term_id;

    /**
    *  Instance of this class.
    *
    *  @since   1.0.0
    *  @access  public
    *  @var     MEC_Woocommerce
    */
    public static $_instance;


   /**
    *  Provides access to a single instance of a module using the Singleton pattern.
    *
    *  @since   1.0.0
    *  @return  object
    */
    public static function getInstance()
    {
        if(!static::$gateway_options) {
            $main			  = \MEC::getInstance('app.libraries.main');
            $gateways_options = $main->get_gateways_options();
            static::$gateway_options = isset($gateways_options[1995]) ? $gateways_options[1995] : '';
            static::$mec_settings = $main->get_settings();
            if (!isset(static::$mec_settings['datepicker_format'])) {
                static::$mec_settings['datepicker_format'] = 'yy-mm-dd&Y-m-d';
            }
            if (!defined('WP_POST_REVISIONS')) {
                define('WP_POST_REVISIONS', false);
            }
        }

        if (self::$_instance === null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }


    /**
     * Get Product By name|ID
     *
     * @param string $product_title
     * @param boolean $isID
     * @return void
     */
    public function get_product($product_title, $isID = false)
    {
        global $wpdb;
        if ($isID) {
            $post = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE ID = %s AND post_type='product' AND post_status = %s", $product_title, 'MEC_Tickets'));
        } else {
            $post = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type='product' AND post_status = %s", $product_title, 'MEC_Tickets'));
        }
        if ($post) {
            return $post;
        }

        return null;
    }

    /**
     * Get Event Date Label
     *
     * @param string $date
     * @param integer $event_id
     * @return void
     */
    public function get_date_label($date, $event_id)
    {
        $date_format = get_option('date_format');
        $time_format = get_option('time_format');
        $event_date = isset($date) ? explode(':', $date) : array();
        $event_start_time = $event_end_time = $new_event_start_time = $new_event_end_time = '';
        if (is_numeric($event_date[0]) and is_numeric($event_date[1])) {
            $start_datetime = date_i18n('Y/m/d' . ' ' . $time_format, $event_date[0]);
            $end_datetime = date_i18n('Y/m/d' . ' ' . $time_format, $event_date[1]);
        } else {
            $start_datetime = $event_date[0];
            $end_datetime = $event_date[1];
        }
        if (isset($start_datetime) and !empty($start_datetime)) {
            $event_start_time = date_i18n($time_format,strtotime($start_datetime));
        }
        if (isset($end_datetime) and !empty($end_datetime)) {
            $event_end_time = date_i18n($time_format,strtotime($end_datetime));
        }

        if (isset($start_datetime) and !empty($start_datetime)) {
            $event_start_date = date_i18n($date_format,strtotime($start_datetime));
        }
        if (isset($end_datetime) and !empty($end_datetime)) {
            $event_end_date = date_i18n($date_format,strtotime($end_datetime));
        }


        $event = get_post($event_id);
        $render = \MEC::getInstance('app.libraries.render');
        $event->data = $render->data($event_id);
        $allday = isset($event->data->meta['mec_allday']) ? $event->data->meta['mec_allday'] : 0;
        if ($allday == '0' and isset($event->data->time) and trim($event->data->time['start'])) :
            $new_event_date = ($event_end_date == $event_start_date) ? $event_start_date . ' ' . $event_start_time . ' - ' . $event_end_time : $event_start_date . ' ' . $event_start_time . ' - ' . $event_end_date . ' ' . $event_end_time;
        else :
            $new_event_date = ($event_end_date == $event_start_date) ? $event_start_date : $event_start_date . ' - ' . $event_end_date;
        endif;

        return $new_event_date;
    }

    /**
     * Access Protected
     *
     * @param object $obj
     * @param object $prop
     * @param string $value
     * @return void
     */
    public function accessProtected($obj, $prop, $value = null) {
        $reflection = new \ReflectionClass($obj);
        $property = $reflection->getProperty($prop);
        $property->setAccessible(true);
        return $property->getValue($obj)[$value];
    }

    public static function get_order_transaction_ids( $order ) {

        $order = is_numeric( $order ) ? new \WC_Order( $order ) : $order;

        $transactions = [];
        foreach ($order->get_items() as $item_id => $order_item) {
            $product_id = $order_item->get_product_id();
            if ($product_id) {
                $transaction_id                = get_post_meta($product_id, 'transaction_id', true);
                if( !empty( $transaction_id ) ) {

                    $transactions[ $transaction_id ] = $transaction_id;
                }
            }
        }

        return $transactions;
    }

    public static function get_order_book_ids( $order ) {

        $order = is_numeric( $order ) ? new \WC_Order( $order ) : $order;

        if( !is_a( $order, '\WC_Order' ) ) {

            return array();
        }

        $order_id = $order->get_id();

        $book_ids = get_post_meta($order_id, 'mec_order_book_ids', true);

        return is_array( $book_ids ) ? $book_ids : array();
    }

} //Products Helper

Products::getInstance();