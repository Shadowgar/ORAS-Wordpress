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
*  WooProducts.
*
*  @author      Webnus <info@webnus.biz>
*  @package     Modern Events Calendar
*  @since       1.0.0
**/
class WooProducts extends Helper
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
        add_action('admin_init', [$this, 'correct_products_number'], 10, 1);
    }

    /**
     * Correct Products Number in Dashboard
     *
     * @return void
     */
    public function correct_products_number () {
        if ( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'product' ) {

            add_action(
                'admin_head',
                function () {
                    ob_start(
                        function ( $buffer ) {
                            $my_query = new \WP_Query(
                                [
                                    'post_type' => 'product',
                                    'post_status__not_in' => 'mec_tickets',
                                    'posts_per_page' => -1,
                                ]
                            );
                            $count    = $my_query->post_count;

                            $my_query = new \WP_Query(
                                [
                                    'post_type' => 'product',
                                    'post_status__not_in' => 'mec_tickets',
                                    'posts_per_page' => -1,
                                    'post_status' => 'publish'
                                ]
                            );
                            $publish_count    = $my_query->post_count;

                            $buffer   = preg_replace( "/<li class='all'>(.*?)<span class=\"count\">(.*?)<\/span>(.*?)<\/li>/", "<li class='all'>$1<span class=\"count\">(" . $count . ')</span>$3</li>', $buffer );
                            $buffer   = preg_replace( "/<li class='publish'>(.*?)<span class=\"count\">(.*?)<\/span>(.*?)<\/li>/", "<li class='publish'>$1<span class=\"count\">(" . $publish_count . ')</span>$3</li>', $buffer );
                            $buffer   = preg_replace( "/<span class=\"displaying-num\">(.*?) (.*?)<\/span>/", "<span class=\"displaying-num\">$count $2</span>", $buffer );
                            return $buffer;
                        }
                    );
                }
            );
            add_action(
                'admin_footer',
                function () {
                    ob_end_flush();
                }
            );
        }
    }


} //WooProducts

WooProducts::instance();
