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
 *  TitleCorrection.
 *
 * @author      Webnus <info@webnus.biz>
 * @package     Modern Events Calendar
 * @since       1.0.0
 **/
class TitleCorrection extends Helper
{

    /**
     *  Instance of this class.
     *
     * @since   1.0.0
     * @access  public
     * @var     MEC_Woocommerce
     */
    public static $instance;

    /**
     *  The directory of this file
     *
     * @access  public
     * @var     string
     */
    public static $dir;

    /**
     *  Provides access to a single instance of a module using the Singleton pattern.
     *
     * @return  object
     * @since   1.0.0
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
     * @since     1.0.0
     */
    public function setHooks()
    {
        if (static::$do_action) {
            add_filter('woocommerce_cart_item_name', [$this, 'woocommerce_title_correction'], 10, 2);
            add_action('woocommerce_checkout_create_order_line_item', [$this, 'change_order_line_item_title'], 10, 4);
            add_filter('woocommerce_order_get_items', [$this, 'strip_tags_item_name_for_generate_pdf'], 10, 3);
        }

        add_filter('wc_add_to_cart_message_html', [$this, 'woocommerce_message_correction'], -1, 1);
    }

    /**
     * Woocommerce Title Correction
     *
     * @param string $title
     * @param object $product
     * @param string $cart_item_key
     * @return string
     */
    public function woocommerce_title_correction($title, $product = false, $cart_item_key = '')
    {
        if (!static::$do_action) {
            return $title;
        }

        $NH = false;
        if (!empty($product)) {
            $id = $product['product_id'];
        } else {
            $id = $this->get_product($title);
            $product = new \WC_Product($id);
            $NH = true;
        }

        if ($this->accessProtected($product['data'], 'data', 'status') != 'mec_tickets') {
            return $title;
        }

        if (!empty($product) && $id == $product['product_id']) {
            $p = new \WC_Product($product['product_id']);
            $title = preg_replace('/Modern Event Calendar Ticket [(](.*?)[)](.*)/i', '$1', $p->get_name());

            $event_id = get_post_meta($id, 'event_id', true);
            static::$do_action = false;
            $title .= '<br />' . '<span class="mec-woo-cart-product-name"><a href="' . get_permalink($event_id) . '">' . get_the_title($event_id) . '</a></span>';
            static::$do_action = true;
            $pInfo = get_post_meta($id, 'mec_ticket', true);
            if ($pInfo && isset($pInfo['name'])) {
                $title .= '<br />' . '<span class="mec-woo-cart-product-person-name">' . $pInfo['name'] . '</span><span class="mec-woo-cart-product-person-email">(' . $pInfo['email'] . ')</span>';
            }

            $variations = get_post_meta($id, 'MEC_Variation_Data');
            if ($variations) {
                $v = [];
                foreach ($variations as $variation) {
                    if (!is_array($variation) && !is_object($variation)) {
                        $variation = json_decode($variation, true);
                    }
                    if ($variation['MEC_WOO_V_count']) {
                        $v[] = $variation['MEC_WOO_V_title'] . '(' . $variation['MEC_WOO_V_count'] . ')';
                    }
                }

                if ($v) {
                    $v = implode(' - ', $v);
                    $title .= '<br />' . $v;
                }
            }

            $date = get_post_meta($id, 'mec_date', true);
            if ($date && get_post_meta($id, 'ticket_id', true)) {

                $dateObject = explode(':', $date);
                if (count($dateObject) > 1) {
                    $dateObject[0] = is_numeric($dateObject[0]) ? $dateObject[0] : strtotime($dateObject[0]);
                    $dateObject[1] = is_numeric($dateObject[1]) ? $dateObject[1] : strtotime($dateObject[1]);
                    $date = implode(':', $dateObject);
                }

                $event_id = get_post_meta($product['product_id'], 'event_id', true);
                static::$do_action = false;
                $event_date = $this->get_date_label($date, $event_id);
                static::$do_action = true;
                $title .= '<br />' . '<span class="mec-woo-cart-booking-date">' . $event_date . '</span>';
            }
        }
        if ($NH) {
            $title = str_replace('<br />', "\n", $title);
            $title = strip_tags($title);
        }
        return $title;
    }

    /**
     * Woocommerce Message Correction
     *
     * @param string $message
     * @return string
     */
    public function woocommerce_message_correction($message)
    {
        if (preg_match('/&ldquo;Modern Event Calendar Ticket [(](.*?)[)](.*)&rdquo;/i', $message)) {
            $message = preg_replace('/&ldquo;Modern Event Calendar Ticket [(](.*?)[)](.*)&rdquo;/i', '&ldquo;$1&rdquo;', $message);
        }

        if (preg_match('/&ldquo;Modern Event Calendar Ticket Variation [(](.*?)[)](.*)&rdquo;/i', $message)) {
            $message = preg_replace('/&ldquo;Modern Event Calendar Ticket Variation [(](.*?)[)](.*)&rdquo;/i', '&ldquo;$1&rdquo;', $message);
        }

        return $message;
    }

    /**
     * Change Order Line Item Title
     *
     * @param object $item
     * @param string $cart_item_key
     * @param object $cart_item
     * @param object $order
     * @return void
     */
    function change_order_line_item_title($item, $cart_item_key, $cart_item, $order)
{
    // Get order item quantity
    $title = $item->get_name();
    $id = $item->get_product_id();

    if (get_post_meta($id, 'mec_ticket', true)) :
        $title = preg_replace('/Modern Event Calendar Ticket [(](.*?)[)](.*)/i', '$1', $title);

        $event_id = get_post_meta($id, 'event_id', true);
        static::$do_action = false;
        $title .= '<br />' . '<span class="mec-woo-cart-product-name"><a href="' . get_permalink($event_id) . '">' . get_the_title($event_id) . '</a></span>';
        static::$do_action = true;

        $pInfo = get_post_meta($id, 'mec_ticket', true);
        if ($pInfo && isset($pInfo['name'])) {
            $title .= '<br />' . '<span style="color: #888; padding-right:20px;">Event:</span><span class="mec-woo-cart-product-person-name">' . $pInfo['name'] . '</span><span class="mec-woo-cart-product-person-email">(' . $pInfo['email'] . ')</span>';
        }

        $variations = get_post_meta($id, 'MEC_Variation_Data');
        if ($variations) {
            $v = [];
            foreach ($variations as $variation) {
                if (!is_array($variation) && !is_object($variation)) {
                    $variation = json_decode($variation, true);
                }
                if ($variation['MEC_WOO_V_count']) {
                    $v[] = $variation['MEC_WOO_V_title'] . '(' . $variation['MEC_WOO_V_count'] . ')';
                }
            }

            if ($v) {
                $v = implode(' - ', $v);
                $title .= '<br />' . $v;
            }
        }

        $date = get_post_meta($id, 'mec_date', true);
        if ($date && get_post_meta($id, 'ticket_id', true)) {
            $event_id = get_post_meta($id, 'event_id', true);
            static::$do_action = false;
            $event_date = $this->get_date_label($date, $event_id);
            static::$do_action = true;
            $title .= '<br />' . '<span style="color: #888; padding-right:20px;">Date:</span><span class="mec-woo-cart-booking-date">' . $event_date . '</span>';
        }

        // Add Transaction ID
        $transaction_id = get_post_meta($id, 'transaction_id', true);
        if (!empty($transaction_id)) {
            $title .= '<br />' . '<span style="color: #888; padding-right:20px;">Transaction ID:</span><span class="mec-woo-cart-transaction-id">' . $transaction_id . '</span>';
        }

    endif;

    // Update order item quantity
    $item->set_name($title);
}

    public function strip_tags_item_name_for_generate_pdf($items, $instance, $types)
    {

        if (
            //Generate pdf by woocommerce-pdf-invoices
        !isset($_GET['bewpi_action'])
        ) {
            return $items;
        }

        if (!in_array('line_item', (array)$types)) {
            return $items;
        }

        foreach ($items as $k => $item) {
            if (method_exists($item, 'set_name')) {

                $item->set_name(strip_tags(str_replace('<', ' <', $item->get_name())));
            } else {

                $items[$k]['name'] = strip_tags(str_replace('<', ' <', $item['name']));
            }
        }

        return $items;
    }

} //TitleCorrection

TitleCorrection::instance();
