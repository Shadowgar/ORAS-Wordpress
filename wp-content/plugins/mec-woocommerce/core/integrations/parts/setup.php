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
*  Setup.
*
*  @author      Webnus <info@webnus.biz>
*  @package     Modern Events Calendar
*  @since       1.0.0
**/
class Setup extends Helper
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
        add_action('init', [$this, 'mec_post_status']);
        add_action('wp_loaded', [$this, 'clean_database']);
        add_action('admin_enqueue_scripts', [$this, 'admin_scripts']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action( 'admin_notices', [$this, 'print_admin_notices'] );
        add_action('mec_top_single_event', 'woocommerce_output_all_notices');
    }

    /**
     * Enqueue Scripts
     *
     * @since     1.0.0
     */
    public function enqueue_scripts()
    {
        $custom_css = 'span.mec-woo-cart-product-person-name {text-transform: capitalize;}span.mec-woo-cart-product-person-email {color: #8d8d8d;padding-left: 3px;font-size: 11px;}';
        wp_add_inline_style('mec-frontend-style', $custom_css);
    }

    /**
     * Register MEC Tickets post status
     *
     * @since     1.0.0
     */
    public function mec_post_status()
    {
        register_post_status(
            'MEC_Tickets',
            array(
                'label'                     => _x('MEC Tickets', 'mec-woocommerce'),
                'public'                    => true,
                'exclude_from_search'       => true,
                'show_in_admin_all_list'    => false,
                'show_in_admin_status_list' => false,
                'label_count'               => _n_noop('MEC Tickets <span class="count">(%s)</span>', 'MEC Tickets <span class="count">(%s)</span>'),
            )
        );
    }

    /**
     * Print Admin Notices
     *
     * @since 1.0.0
     */
    public function print_admin_notices()
    {
        if($message = get_option('mec_woo_print_admin_notices')) {
            delete_option('mec_woo_print_admin_notices');
            $class = 'notice notice-info';
            $message = html_entity_decode($message);
            printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
        }
    }

    /**
     * Clean The DataBase
     *
     * @since     1.0.0
     */
    public function clean_database()
    {
        $args = array(
            'post_type'        => 'product',
            'post_status'      => 'mec_tickets',
            'order' => 'DESC',
            'date_query' => array(
                array(
                    'before' => '6 days ago',
                ),
            )
        );
        $products = get_posts($args);
        foreach ($products as $product) {
            if ($product->post_status  !== 'mec_tickets') {
                continue;
            }

            $paymentComplete = get_post_meta($product->ID, 'mec_payment_complete', true);
            if (!$paymentComplete) {
                wp_delete_post($product->ID, true);
            }
        }
    }

    /**
     * Enqueue admin Scripts
     *
     * @since 1.0.0
     */
    public function admin_scripts()
    {
        $order_id = get_post_meta(get_the_id(), 'mec_order_id', true);
        if (!$order_id) {
            return;
        }
        wp_enqueue_style('woocommerce_admin_styles', \WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION);
    }

} //Setup

Setup::instance();
