<?php

namespace MEC_Woocommerce\Core\Integrations;

use MEC_Woocommerce\Core\Helpers\Products;
use \MEC_Woocommerce\Core\Helpers\Products as Helper;

// Don't load directly
if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

/**
*  Sync.
*
*  @author      Webnus <info@webnus.biz>
*  @package     Modern Events Calendar
*  @since       1.0.0
**/
class Sync extends Helper
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
        if (isset(static::$gateway_options['sync_order_status_for_booking']) && static::$gateway_options['sync_order_status_for_booking'] && !static::$access_to_run) {
            # Update MEC Bookings Status
            add_action('woocommerce_order_status_cancelled', [$this, 'change_order_status_handler'], 10, 1);
            add_action('woocommerce_order_status_refunded', [$this, 'change_order_status_handler'], 10, 1);

            add_action('woocommerce_order_status_completed', [$this, 'change_order_status_handler'], 10, 1);
            add_action('woocommerce_order_status_completed_notification', [$this, 'payment_complete'], 1, 1);
            add_action('woocommerce_order_status_processing', [$this, 'change_order_status_handler'], 10, 1);

            add_action('woocommerce_order_status_pending', [$this, 'change_order_status_handler'], 10, 1);
            add_action('woocommerce_order_status_failed', [$this, 'change_order_status_handler'], 10, 1);
            add_action('woocommerce_order_status_on-hold', [$this, 'change_order_status_handler'], 10, 1);

            # Update WOO Orders Status
            add_action('mec_booking_confirmed', [$this, 'mec_booking_confirmed'], 10, 1);
            add_action('mec_booking_pended', [$this, 'mec_booking_pended'], 10, 1);
            add_action('mec_booking_rejected', [$this, 'mec_booking_rejected'], 10, 1);

            add_action('woocommerce_payment_complete',[$this, 'custom_process_order'] , 10, 1);
        }

        if (isset(static::$gateway_options['remove_woo_taxes']) && static::$gateway_options['remove_woo_taxes']) {
            add_filter( 'woocommerce_product_get_tax_class', [$this,'wc_remove_tax_for_mec_ticket'], 1, 2 );
        }


        add_action('wp_trash_post', [$this, 'trash_sync']);
        add_action('wp_delete_post', [$this, 'delete_sync']);
    }

    /**
     * Set No tax for Tickets in woo cart
     */
    public function wc_remove_tax_for_mec_ticket( $tax_class, $product ) {

        if ( $product->get_status() == 'mec_tickets' ) {
            $tax_class = 'none';
        }
        return $tax_class;
    }

    public function get_order_sync_statuses() {

        $default_confirm_statuses = array(
            'pending' => array(
                'pending',
                'failed',
                'on-hold',
            ),
            'confirmed' => array(
                'completed',
                'processing',
            ),
            'rejected' => array(
                'cancelled',
                'refunded',
            ),
        );

        $payment_settings = \MEC\Settings\Settings::getInstance()->get_options('gateways');

        $sync_statuses = $payment_settings['1995']['sync_statuses'] ?? $default_confirm_statuses;

        return $sync_statuses;
    }

    public function change_order_status_handler( $order_id ) {

        $do_confirm = false;
        $order  = new \WC_Order($order_id);
        $book   = \MEC::getInstance('app.libraries.book');

        $status = str_replace( 'woocommerce_order_status_', '', current_action() );

        $sync_statuses = $this->get_order_sync_statuses();
        foreach( $sync_statuses as $confirm_status => $order_statuses ) {

            if ( in_array( $status, $order_statuses ) ) {

                $do_confirm = $confirm_status;
            }
        }

        if( !$do_confirm ){

            return;
        }

        $book_ids = Products::get_order_book_ids( $order );
        if ( empty( $book_ids ) ) {

            return;
        }

        if (static::$access_to_run) {

            return;
        }

        foreach( $book_ids as $book_id ) {

            static::$access_to_run = 1;

            switch( $do_confirm ) {
                case 'pending':

                    $book->pending($book_id);

                    break;
                case 'rejected':

                    $book->reject($book_id);
                    $book->cancel($book_id);

                    break;
                case 'confirmed':

                    $book->confirm($book_id, 'auto');
                    add_filter( 'mec_booking_confirmation', array($this , 'mec_disable_twice_email'), 10, 1 );

                    break;
            }

            static::$access_to_run = 0;
        }
    }

    /**
     * Set "Confirmed" Status for Booking
     *
     * @param integer $order_id
     * @return void
     */
    public function payment_complete($order_id)
    {
        global $mec_wc_payment_complete;
        if (static::$access_to_run || true === $mec_wc_payment_complete) {
            return;
        }
        $mec_wc_payment_complete = true;

        $order  = new \WC_Order($order_id);
        $book   = \MEC::getInstance('app.libraries.book');

        $book_ids = Products::get_order_book_ids( $order );
        foreach( $book_ids as $book_id ) {

            static::$access_to_run = 1;
            $book->confirm($book_id, 'auto');
            add_filter( 'mec_booking_confirmation', array($this , 'mec_disable_twice_email'), 10, 1 );
            static::$access_to_run = 0;
        }
    }

   /**
     * Set "Completed" Status for Order
     *
     * @param integer $book_id
     * @return void
     */
    public function mec_booking_confirmed($book_id)
    {
        if (static::$access_to_run) return;

        $main = \MEC::getInstance('app.libraries.main');
        $gateways_options = $main->get_gateways_options();
        $gateway_options = $gateways_options[1995];
        if (isset($gateway_options['sync_order_status_for_booking']) && $gateway_options['sync_order_status_for_booking']) {
            $order_id = get_post_meta($book_id, 'mec_order_id', true);
            if ($order_id) {
                $order        = new \WC_Order($order_id);
                static::$access_to_run = 1;
                $order->update_status('wc-completed');
                add_filter( 'mec_booking_confirmation', array($this , 'mec_disable_twice_email'), 10, 1 );
                static::$access_to_run = 0;
                update_option('mec_woo_print_admin_notices', __('The order status has been changed. Please manage order from this', 'mec-woocommerce') . ' <a href="' . get_edit_post_link( $order_id ) . '">' . __('link', 'mec-woocommerce') . '</a>' ) ;
            }
        }
        return;
    }

    public function mec_disable_twice_email( $data ) {
        $data = false;
        return $data;
    }

    /**
     * Set On-Hold Status for Order
     *
     * @param integer $book_id
     * @return void
     */
    public function mec_booking_pended($book_id)
    {
        if (static::$access_to_run) return;
        $main            	= \MEC::getInstance('app.libraries.main');
        $gateways_options = $main->get_gateways_options();
        $gateway_options = $gateways_options[1995];
        if (isset($gateway_options['sync_order_status_for_booking']) && $gateway_options['sync_order_status_for_booking']) {
            $order_id = get_post_meta($book_id, 'mec_order_id', true);
            if ($order_id) {
                $order        = new \WC_Order($order_id);
                static::$access_to_run = 1;
                $order->update_status('wc-on-hold');
                static::$access_to_run = 0;
                update_option('mec_woo_print_admin_notices', __('The order status has been changed. Please manage order from this', 'mec-woocommerce') . ' <a href="' . get_edit_post_link( $order_id ) . '">' . __('link', 'mec-woocommerce') . '</a>' ) ;
            }
        }
    }

    /**
     * Cancel The Order
     *
     * @param integer $book_id
     * @return void
     */
    public function mec_booking_rejected($book_id)
    {
        if (static::$access_to_run) return;
        $main            	= \MEC::getInstance('app.libraries.main');
        $gateways_options = $main->get_gateways_options();
        $gateway_options = $gateways_options[1995];
        if (isset($gateway_options['sync_order_status_for_booking']) && $gateway_options['sync_order_status_for_booking']) {
            $order_id = get_post_meta($book_id, 'mec_order_id', true);
            if ($order_id) {
                $order        = new \WC_Order($order_id);
                static::$access_to_run = 1;
                $order->update_status('wc-cancelled');
                static::$access_to_run = 0;
                update_option('mec_woo_print_admin_notices', __('The order status has been changed. Please manage order from this', 'mec-woocommerce') . ' <a href="' . get_edit_post_link( $order_id ) . '">' . __('link', 'mec-woocommerce') . '</a>' ) ;
            }
        }
    }

    /**
     * Set Completed status for Orders
     *
     * @param integer $order_id
     * @return void
     */
    public function custom_process_order($order_id) {
        if ( get_post_meta($order_id, 'mec_order_type',true) == 'mec_ticket') {
            $order = new \WC_Order( $order_id );

            foreach ($order->get_items() as $item_id => $order_item) {
                $product = $this->get_product($order_item['product_id'], true);
                if ($product && get_post_meta($product->ID, 'mec_ticket', true)) {
                    $order->update_status('wc-completed');
                } else {
                    $order->update_status('wc-processing');
                    return;
                }
            }
        }
    }

    /**
     * Sync (Trash Booking|Order)
     *
     * @param integer $post_id
     * @return void
     */
    public function trash_sync($post_id)
    {
        $order_id = get_post_meta($post_id, 'mec_order_id', true);
        if ($order_id) {
            $this->move_to_trash($order_id);
        } else {
            $meta = get_posts(array(
                'post_type' => 'mec-books',
                'meta_key'   => 'mec_order_id',
                'meta_value' => $post_id,
            ));
            if ($meta) {
                $book_id = $meta[0]->ID;
                $this->move_to_trash($book_id);
            }
        }
    }

    /**
     * Sync (Delete Booking|Order)
     *
     * @param integer $post_id
     * @return void
     */
    public function delete_sync($post_id)
    {
        $order_id = get_post_meta($post_id, 'mec_order_id', true);
        if ($order_id) {
            $this->delete_post($order_id);
        } else {
            $meta = get_posts(array(
                'post_type' => 'mec-books',
                'meta_key'   => 'mec_order_id',
                'meta_value' => $post_id,
            ));
            if ($meta) {
                $book_id = $meta[0]->ID;
                $this->delete_post($book_id);
            }
        }
    }

    /**
     * Move to Trash
     *
     * @param integer $post_id
     * @return void
     */
    private function move_to_trash($post_id) {
        $post = get_post($post_id);

        if (!$post) {
            return $post;
        }

        if ('trash' === $post->post_status) {
            return false;
        }

        $check = apply_filters('pre_trash_post', null, $post);
        if (null !== $check) {
            return $check;
        }

        add_post_meta($post_id, '_wp_trash_meta_status', $post->post_status);
        add_post_meta($post_id, '_wp_trash_meta_time', time());

        wp_update_post(
            array(
                'ID'          => $post_id,
                'post_status' => 'trash',
            )
        );
        wp_trash_post_comments($post_id);
    }

    /**
     * Delete Post
     *
     * @param integer $_post_id
     * @param boolean $force_delete
     * @return void
     */
    private function delete_post($_post_id, $force_delete = false)
    {
        global $wpdb;
        $post = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE ID = %d", $_post_id));
        if (!$post) {
            return $post;
        }
        $post = get_post($post);
        if (!$force_delete && ('post' === $post->post_type || 'page' === $post->post_type) && 'trash' !== get_post_status($_post_id) && EMPTY_TRASH_DAYS) {
            return wp_trash_post($_post_id);
        }
        if ('attachment' === $post->post_type) {
            return wp_delete_attachment($_post_id, $force_delete);
        }

        $check = apply_filters('pre_delete_post', null, $post, $force_delete);
        if (null !== $check) {
            return $check;
        }

        delete_post_meta($_post_id, '_wp_trash_meta_status');
        delete_post_meta($_post_id, '_wp_trash_meta_time');
        wp_delete_object_term_relationships($_post_id, get_object_taxonomies($post->post_type));
        $parent_data  = array('post_parent' => $post->post_parent);
        $parent_where = array('post_parent' => $_post_id);
        if (is_post_type_hierarchical($post->post_type)) {
            $children_query = $wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_parent = %d AND post_type = %s", $_post_id, $post->post_type);
            $children       = $wpdb->get_results($children_query);
            if ($children) {
                $wpdb->update($wpdb->posts, $parent_data, $parent_where + array('post_type' => $post->post_type));
            }
        }
        $revision_ids = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_parent = %d AND post_type = 'revision'", $_post_id));
        foreach ($revision_ids as $revision_id) {
            wp_delete_post_revision($revision_id);
        }
        $wpdb->update($wpdb->posts, $parent_data, $parent_where + array('post_type' => 'attachment'));
        wp_defer_comment_counting(true);
        $comment_ids = $wpdb->get_col($wpdb->prepare("SELECT comment_ID FROM $wpdb->comments WHERE comment_post_ID = %d", $_post_id));
        foreach ($comment_ids as $comment_id) {
            wp_delete_comment($comment_id, true);
        }
        wp_defer_comment_counting(false);
        $post_meta_ids = $wpdb->get_col($wpdb->prepare("SELECT meta_id FROM $wpdb->postmeta WHERE post_id = %d ", $_post_id));
        foreach ($post_meta_ids as $mid) {
            delete_metadata_by_mid('post', $mid);
        }
        $result = $wpdb->delete($wpdb->posts, array('ID' => $_post_id));
        if (!$result) {
            return false;
        }
        clean_post_cache($post);
        if (is_post_type_hierarchical($post->post_type) && $children) {
            foreach ($children as $child) {
                clean_post_cache($child);
            }
        }
        wp_clear_scheduled_hook('publish_future_post', array($_post_id));
    }
} //Sync

Sync::instance();
