<?php

namespace MEC_Woocommerce\Core\Integrations;
use \MEC_Woocommerce\Core\Helpers\Products as Helper;
// Don't load directly
if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

/**
*  MECExport.
*
*  @author      Webnus <info@webnus.biz>
*  @package     Modern Events Calendar
*  @since       1.0.0
**/
class MECExport extends Helper
{

    /**
    *  Instance of this class.
    *
    *  @since   1.0.0
    *  @access  public
    *  @var     MEC_Woocommerce
    */
    public static $instance;

   /**
    *  Provides access to a single instance of a module using the Singleton pattern.
    *
    *  @since   1.0.0
    *  @return  object
    */
    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    public function __construct()
    {
        if (self::$instance === null) {
            self::$instance = $this;
        }

        $this->setHooks();
    }

   /**
    *  Hooks
    *
    *  @since     1.0.0
    */
    public function setHooks()
    {
        add_filter('mec_csv_export_columns', [$this, 'mec_booking_export_columns'], 10, 1);
        add_filter('mec_csv_export_booking', [$this, 'mec_booking_export_add_order_id'], 10, 2);
        add_filter('mec_excel_export_columns', [$this, 'mec_booking_export_columns'], 10, 1);
        add_filter('mec_excel_export_booking', [$this, 'mec_booking_export_add_order_id'], 10, 2);
    }

    /**
     * Add "WOO Order ID" Title into MEC CSV Export Columns
     *
     * @param array $columns
     * @return void
     */
    public function mec_booking_export_columns($columns)
    {
        $columns[] = esc_attr__('Woocommerce Order ID', 'mec-woocommerce');
        return $columns;
    }

    /**
     * Add "WOO Order ID" Value into MEC CSV Export
     *
     * @param array $booking
     * @param integer $post_id
     * @return void
     */
    public function mec_booking_export_add_order_id($booking, $post_id)
    {
        $order_id = get_post_meta($post_id, 'mec_order_id', true);
        if ($order_id) {
            $booking[] = $order_id;
        } else {
            $booking[] = '';
        }
        return $booking;
    }

} //MECExport

MECExport::instance();
