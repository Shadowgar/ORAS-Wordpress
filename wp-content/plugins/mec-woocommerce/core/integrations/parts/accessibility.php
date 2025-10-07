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
*  Accessibility.
*
*  @author      Webnus <info@webnus.biz>
*  @package     Modern Events Calendar
*  @since       1.0.0
**/
class Accessibility extends Helper
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
        add_action('woocommerce_product_query', [$this, 'hide_mec_booking_products'], 10, 1);
        add_action('woocommerce_after_single_product_summary', [$this, 'product_invisible']);
        add_action('pre_get_terms', [$this, 'hide_mec_woo_cat'], 10, 1);
        add_filter('woocommerce_product_categories_widget_args', [$this, 'hide_mec_category']);
    }

   /**
    * Hide Mec Category
    *
    * @param array $args
    * @return array args
    */
    public function hide_mec_category($args)
    {
        $term_id         = @get_term_by('name', 'MEC-Woo-Cat', 'product_cat')->term_id;
        $args['exclude'] = $term_id;
        return $args;
    }

    /**
     * Hide The mec_woo_cat
     *
     * @param object $terms_query
     * @return void
     */
    public function hide_mec_woo_cat($terms_query)
    {
        $args = &$terms_query->query_vars;
        if ( $args['slug'] === 'mec-woo-cat' ) $args['exclude'] = [static::$term_id];
        $terms_query->meta_query->parse_query_vars( $args );
    }

    /**
     * Hide MEC booking products
     *
     * @param object $q
     * @return void
     */
    public function hide_mec_booking_products($q)
    {
        if (is_single() || is_shop() || is_page('shop')) { // set conditions here
            $tax_query = (array) $q->get('tax_query');

            $tax_query[] = array(
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => array('MEC-Woo-Cat'), // set product categories here
                'operator' => 'NOT IN',
            );

            $q->set('tax_query', $tax_query);
        }
    }

    /**
     * MEC Product Invisible
     *
     * @since     1.0.0
     */
    public function product_invisible()
    {
        remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
    }


} //Accessibility

Accessibility::instance();
