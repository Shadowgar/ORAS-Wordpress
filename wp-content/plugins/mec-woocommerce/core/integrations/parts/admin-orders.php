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
*  AdminOrders.
*
*  @author      Webnus <info@webnus.biz>
*  @package     Modern Events Calendar
*  @since       1.0.0
**/
class AdminOrders extends Helper
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
        add_action('manage_shop_order_posts_custom_column', [$this, 'woocommerce_column_type_in_shop_orders'], 99, 2);
        add_filter('manage_edit-shop_order_columns', [$this, 'shop_orders_column_type'], 99, 1);
        add_filter('manage_edit-shop_order_sortable_columns', [$this, 'shop_orders_column_type'], 99);
        add_action('woocommerce_order_details_after_order_table', [$this, 'order_details_after_order_table'], 10, 1);
        add_action('woocommerce_before_order_itemmeta', [$this, 'order_title_correct']);
    }

    /**
    * Correct Order Item Title
    *
    * @since     1.0.0
    */
    public function order_title_correct () {
        $rnd = md5(microtime() . random_int(0, 100));
        echo '<div id="randomID' . $rnd . '"></div>';
        echo '<script>
            jQuery("#randomID' . $rnd . '").parents("td").first().find(".wc-order-item-name").replaceWith(function () {
                return jQuery("<strong />").append(jQuery(this).contents());
            });
        </script>';
    }


    /**
     * Woocommerce Column Type In Shop Orders
     *
     * @param string $column_name
     * @param integer $post_id
     * @return void
     */
    public function woocommerce_column_type_in_shop_orders($column_name, $post_id)
    {
        if ($column_name == 'order_type') {
            if ($order_type = get_post_meta($post_id, 'mec_order_type', true)) {
                if ($order_type == 'mec_ticket') {
                    echo esc_html__('MEC Ticket', 'mec-woocommerce');
                    return;
                }
            }
            echo esc_html__('Shop Order', 'mec-woocommerce');
        }
        return;
    }

    /**
     * Column Order Type
     *
     * @param array $columns
     * @return array
     */
    public function shop_orders_column_type($columns)
    {
        $columns['order_type'] = esc_html__('Type', 'mec-woocommerce');
        return $columns;
    }

    /**
     * Display Order Details After Order Table
     *
     * @param object $order
     * @return void
     */
    public function order_details_after_order_table($order)
    {
        $order_id   = $order->get_id();
        $order_type = get_post_meta($order_id, 'mec_order_type', true);
        if (empty($order_type) || $order_type != 'mec_ticket') {
            return;
        }

        $transactions = static::get_order_transaction_ids( $order );

        $download_links = array();
        foreach ($transactions as $transaction_id) {

            $transactionObject  = new \MEC\Transactions\Transaction( $transaction_id );

            $book_id = $transactionObject->get_book_id();
            $event_id = $transactionObject->get_event_id();
            if( $book_id ) {

                $mec_confirmed  = get_post_meta($book_id, 'mec_confirmed', true);
                $dl_file_link   = !$mec_confirmed ? '' : \MEC::getInstance('app.libraries.book')->get_dl_file_link($book_id);
                if( trim( $dl_file_link ) ) {

                    $download_links[ $event_id ] = $dl_file_link;
                }
            }
        }

        if( !empty( $download_links ) ){

            ?>
            <div>
                <h2><?php echo esc_html__('Files', 'mec-woocommerce'); ?></h2>

                <table class="woocommerce-table shop_table order_details">
                    <thead>
                        <th><?php echo esc_html__('Event', 'mec-woocommerce'); ?></th>
                        <th><?php echo esc_html__('Download Link', 'mec-woocommerce'); ?></th>
                    </thead>

                    <tbody>
                        <?php
                        foreach( $download_links as $event_id => $download_link ) {

                            ?>
                            <tr>
                                <td>
                                    <span class="mec-event-title"><?php echo get_the_title( $event_id ); ?></span>
                                </td>
                                <td>
                                    <span class="mec-event-file-download">
                                        <?php echo '<a class="mec-dl-file-download" href="'.esc_url($dl_file_link).'">'.esc_html__('Download File', 'mec').'</a>' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <?php
        }



        ?>
        <div>
            <h2><?php echo esc_html__('Attendees List', 'mec-woocommerce'); ?></h2>

            <table class="woocommerce-table shop_table order_details">
                <thead>
                    <th><?php echo esc_html__('Attendees', 'mec-woocommerce'); ?></th>
                    <th><?php echo esc_html__('Information', 'mec-woocommerce'); ?></th>
                </thead>

                <tbody>
                    <?php
                    foreach ($transactions as $transaction_id) {

                        $transactionObject = new \MEC\Transactions\Transaction( $transaction_id );
                        $event_id = $transactionObject->get_event_id();
                        $tickets = $transactionObject->get_attendees_info();

                        foreach ( $tickets as $ticket) {

                            $t_date = isset( $ticket['date'] ) && !empty( $ticket['date'] ) ? $ticket['date'] : $transaction['date'];

                            $ticket_count = $ticket['count'] ?? 1;
                            if ( $transactionObject->is_first_for_all() ) {

                                ?>
                                <tr>
                                    <td>
                                        <span class="mec-attendee-name"><?php echo esc_html__('Name: ', 'mec-woocommerce'); ?><?php echo $ticket['name']; ?></span>
                                        <br>
                                        <span class="mec-attendee-email"><?php echo $ticket['email']; ?></span>
                                        <span class="mec-attendee-tickets-count"><?php echo '<strong> × ' . $ticket_count . '</strong>'; ?></span>
                                    </td>
                                    <td>
                                        <span class="mec-attendee-name"><a href="<?php echo get_permalink($event_id); ?>"><?php echo get_the_title($event_id); ?></a></span>
                                        <br>
                                        <span class="mec-attendee-date"><?php echo $this->get_date_label($t_date, $event_id); ?></span>
                                    </td>
                                </tr>
                                <?php
                                break;
                            } else {
                            ?>
                                <tr>
                                    <td>
                                        <span class="mec-attendee-name"><?php echo esc_html__('Name: ', 'mec-woocommerce'); ?><?php echo $ticket['name']; ?></span>
                                        <br>
                                        <span class="mec-attendee-email"><?php echo $ticket['email']; ?></span>
                                        <span class="mec-attendee-tickets-count"><?php echo '<strong> × ' . $ticket_count . '</strong>'; ?></span>
                                    </td>
                                    <td>
                                        <span class="mec-attendee-name"><a href="<?php echo get_permalink($event_id); ?>"><?php echo get_the_title($event_id); ?></a></span>
                                        <br>
                                        <span class="mec-attendee-date"><?php echo $this->get_date_label($t_date, $event_id); ?></span>
                                    </td>
                                </tr>
                    <?php
                            }
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    <?php
    }

} //AdminOrders

AdminOrders::instance();
