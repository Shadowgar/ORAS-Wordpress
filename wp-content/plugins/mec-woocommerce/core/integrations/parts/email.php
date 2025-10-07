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
*  Email.
*
*  @author      Webnus <info@webnus.biz>
*  @package     Modern Events Calendar
*  @since       1.0.0
**/
class Email extends Helper
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
        add_action('woocommerce_email_after_order_table', [$this, 'customize_order_table'], 10, 1);
    }

    /**
     * Customize Order Table
     *
     * @param object $order
     * @return void
     */
    public function customize_order_table($order)
    {
        $this->order_details_after_order_table_email_version($order);
    }

    /**
     * Display Order Details After Order Table Email Version
     *
     * @since     1.0.0
     */
    public function order_details_after_order_table_email_version($order)
    {
        $order_id   = $order->get_id();
        $order_type = get_post_meta($order_id, 'mec_order_type', true);
        $factory = \MEC::getInstance('app.libraries.factory');
        if (empty($order_type) || $order_type != 'mec_ticket') {
            return;
        }

        $transactions = static::get_order_transaction_ids( $order );

    ?>
        <div>
            <h2><?php echo esc_html__('Attendees List', 'mec-woocommerce'); ?></h2>
            <table class="woocommerce-table shop_table order_details" style="border:none;width:100%;">
                <thead style="border:solid 1px #ddd;">
                    <th style="border:solid 1px #ddd;"><?php echo esc_html__('Attendees', 'mec-woocommerce'); ?></th>
                    <th style="border:solid 1px #ddd;"><?php echo esc_html__('Information', 'mec-woocommerce'); ?></th>
                </thead>

                <tbody>
                    <?php
                    foreach ($transactions as $transaction_id) {

                        $transactionObject = new \MEC\Transactions\Transaction( $transaction_id );
                        $event_id = $transactionObject->get_event_id();
                        $tickets = $transactionObject->get_attendees_info();

                        $main	= \MEC::getInstance('app.libraries.main');
                        $reg_fields = $main->get_reg_fields($event_id);
                        foreach ( $tickets as $ticket ) {
                            if (!isset($ticket['email'])) {
                                continue;
                            }

                            $t_date = isset( $ticket['date'] ) && !empty( $ticket['date'] ) ? $ticket['date'] : $transactionObject->get_date();

                            if ( $transactionObject->is_first_for_all() ) { ?>
                                <tr>
                                    <td style="border:solid 1px #ddd;padding:5px;">
                                        <span class="mec-attendee-name"><?php echo esc_html__('Name: ', 'mec-woocommerce'); ?><?php echo $ticket['name']; ?></span>
                                        <br>
                                        <span class="mec-attendee-email"><?php echo $ticket['email']; ?></span>
                                        <span class="mec-attendee-tickets-count"><?php echo '<strong> × ' . $ticket['count'] . '</strong>'; ?></span>

                                    </td>
                                    <td style="border:solid 1px #ddd;padding:5px;">
                                        <span class="mec-attendee-name"><a href="<?php echo get_permalink($event_id); ?>"><?php echo get_the_title($event_id); ?></a></span>
                                        <br>
                                        <span class="mec-attendee-date"><?php echo $this->get_date_label($t_date, $event_id); ?></span>
                                        <?php
                                        $location_id = get_post_meta($event_id, 'mec_location_id', true);
                                        $location = get_term($location_id, 'mec_location');
                                        if (isset($location->name)) {
                                            echo '<div style="display:block;padding:1px;width:100%;">
                                                <strong>' . __('Location', 'mec-woocommerce') . ':</strong>
                                                <span>' . $location->name . '</span>
                                            </div>';
                                        }
                                        if (!isset($ticket['reg']) || !is_array($ticket['reg'])) {
                                            $ticket['reg'] = [];
                                        }
                                        ?>
                                        <?php
                                        foreach ($ticket['reg'] as $field_id => $value) : $label = isset($reg_fields[$field_id]) ? $reg_fields[$field_id]['label'] : '';
                                            $type = isset($reg_fields[$field_id]) ? $reg_fields[$field_id]['type'] : ''; ?>
                                            <?php if ($type == 'agreement') : ?>
                                                <div style="display:block;padding:1px;width:100%;">
                                                    <strong><?php echo sprintf(__($label, 'mec'), '<a href="' . get_the_permalink($reg_fields[$field_id]['page']) . '">' . get_the_title($reg_fields[$field_id]['page']) . '</a>'); ?>: </strong>
                                                    <span><?php echo ($value == '1' ? __('Yes', 'mec') : __('No', 'mec')); ?></span>
                                                </div>
                                            <?php else : ?>
                                                <div style="display:block;padding:1px;width:100%;">
                                                    <strong><?php _e($label, 'mec'); ?>: </strong>
                                                    <span><?php echo (is_string($value) ? $value : (is_array($value) ? implode(', ', $value) : '---')); ?></span>
                                                </div>
                                            <?php endif;

                                        endforeach;?>
                                    </td>
                                </tr>
                            <?php
                                break;
                            } else {
                            ?>
                                <tr>
                                    <td style="border:solid 1px #ddd;padding:5px;">
                                        <span class="mec-attendee-name"><?php echo esc_html__('Name: ', 'mec-woocommerce'); ?><?php echo $ticket['name']; ?></span>
                                        <br>
                                        <span class="mec-attendee-email"><?php echo $ticket['email']; ?></span>
                                        <span class="mec-attendee-tickets-count"><?php echo '<strong> × ' . $ticket['count'] . '</strong>'; ?></span>
                                        <?php if (!isset($ticket['reg'])) $ticket['reg'] = []; ?>
                                        <?php
                                        foreach ($ticket['reg'] as $field_id => $value) : $label = isset($reg_fields[$field_id]) ? $reg_fields[$field_id]['label'] : '';
                                            $type = isset($reg_fields[$field_id]) ? $reg_fields[$field_id]['type'] : ''; ?>
                                            <?php if ($type == 'agreement') : ?>
                                                <div style="display:block;padding:1px;width:100%;">
                                                    <strong><?php echo sprintf(__($label, 'mec'), '<a href="' . get_the_permalink($reg_fields[$field_id]['page']) . '">' . get_the_title($reg_fields[$field_id]['page']) . '</a>'); ?>: </strong>
                                                    <span><?php echo ($value == '1' ? __('Yes', 'mec') : __('No', 'mec')); ?></span>
                                                </div>
                                            <?php else : ?>
                                                <div style="display:block;padding:1px;width:100%;">
                                                    <strong><?php _e($label, 'mec'); ?>: </strong>
                                                    <span><?php echo (is_string($value) ? $value : (is_array($value) ? implode(', ', $value) : '---')); ?></span>
                                                </div>
                                            <?php endif;

                                        endforeach;?>
                                    </td>
                                    <td style="border:solid 1px #ddd;padding:5px;">
                                        <span class="mec-attendee-name"><a href="<?php echo get_permalink($event_id); ?>"><?php echo get_the_title($event_id); ?></a></span>
                                        <br>
                                        <span class="mec-attendee-date"><?php echo $this->get_date_label($t_date, $event_id); ?></span>
                                    </td>
                                </tr><?php
                            }
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <br /><?php
    }

} //Email

Email::instance();
