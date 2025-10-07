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
*  MetaBox.
*
*  @author      Webnus <info@webnus.biz>
*  @package     Modern Events Calendar
*  @since       1.0.0
**/
class MetaBox extends Helper
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
        add_action('admin_init', [$this, 'meta_box_init']);
    }

    /**
     * Meta Box Init
     *
     * @since     1.0.0
     */
    public function meta_box_init()
    {
        if (isset($_GET['post']) && get_post_type($_GET['post']) == 'shop_order') {
            $order_type = get_post_meta($_GET['post'], 'mec_order_type', true);
            if (!empty($order_type) && $order_type == 'mec_ticket') {
                add_meta_box('mec_ticket_information', 'MEC Tickets', [$this, 'mec_ticket_meta_box'], 'shop_order', 'side');
            }
        }
    }

    /**
     * MEC Ticket Meta Box
     *
     * @since     1.0.0
     */
    public function mec_ticket_meta_box()
    {
        $order_id     = get_the_ID();
        $order        = new \WC_Order($order_id);
        $transactions = [];
        foreach ($order->get_items() as $item_id => $order_item) {
            $product = $this->get_product($order_item['product_id'], true);
            if ($product) {
                $transaction_id                  = get_post_meta($product->ID, 'transaction_id', true);
                $transactions[$transaction_id] = $transaction_id;
            }
        }

    ?>
        <div class="mec-attendees-meta-box">
            <?php
            $tt      = 0;
            $factory = \MEC::getInstance('app.libraries.factory');
            foreach ($transactions as $transaction_id) {
                $tt++;
                $transaction = get_option($transaction_id, false);
                $tickets     = get_post_meta($transaction['event_id'], 'mec_tickets', true);
            ?>
                <div class="mec-transaction">
                    <?php if ($tt > 1) : ?>
                        <hr>
                    <?php endif; ?>
                    <a href="<?php echo get_edit_post_link(get_option($transaction_id . '_MEC_payment_complete')); ?>" class="mec-edit-booking"><?php echo esc_html__('Edit Booking', 'mec-woocommerce'); ?></a>
                    <br>
                    <span class="mec-attendee-name">
                        <?php echo esc_html__('Event Name: ', 'mec-woocommerce'); ?><a href="<?php echo get_permalink($transaction['event_id']); ?>"><?php echo get_the_title($transaction['event_id']); ?></a></span>
                        <br>
                    </span>
                    <?php
                    $location_id = get_post_meta($transaction['event_id'], 'mec_location_id', true);
                    $location = get_term($location_id, 'mec_location');
                    if (isset($location->name)) {
                    ?>
                        <span class="mec-attendee-location"><?php echo esc_html__('Location: ', 'mec-woocommerce'); ?> <?php echo $location->name; ?></span>
                        <br>
                    <?php
                    }
                    ?>

                <?php
                $this->get_ticket_data($transaction_id, $order_id, $order_item['product_id'], $transaction['event_id']);

                echo '</div>';
            }
        echo '</div>';
    }

    /**
     * Display Order Details After Order Table
     *
     * @param string $transaction_id
     * @param integer $order_id
     * @param integer $product_id
     * @param integer $event_id
     * @return void
     */
    public function get_ticket_data($transaction_id, $order_id, $product_id, $event_id)
    {
        $book_id = get_option($transaction_id . '_MEC_payment_complete');
        $main	= \MEC::getInstance('app.libraries.main');
        $meta	= $main->get_post_meta($book_id);
        // The booking is not saved so we will skip this and show booking form instead.
        if (!$event_id) return false;

        $tickets = get_post_meta($event_id, 'mec_tickets', true);
        $transactionObject = new \MEC\Transactions\Transaction( $transaction_id );
        $attendees = $transactionObject->get_tickets_details();
        $reg_fields = $main->get_reg_fields($event_id);

        # Attachments
        if (isset($attendees['attachments']) && !empty($attendees['attachments'])) {
            echo '<hr />';
            echo '<h3>' . _e('Attachments', 'mec-woocommerce') . '</h3>';
            foreach ($attendees['attachments'] as $attachment) {
                echo '<div class="mec-attendee">';
                if (!isset($attachment['error']) && $attachment['response'] === 'SUCCESS') {
                    $a = getimagesize($attachment['url']);
                    $image_type = $a[2];
                    if (in_array($image_type, array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_BMP))) {
                        echo '<a href="' . $attachment['url'] . '" target="_blank">
                    <img src="' . $attachment['url'] . '" alt="' . $attachment['filename'] . '" title="' . $attachment['filename'] . '" style="max-width:250px;float: left;margin: 5px;">
                </a>';
                    } else {
                        echo '<a href="' . $attachment['url'] . '" target="_blank">' . $attachment['filename'] . '</a>';
                    }
                }

                echo '</div>';
            }
            echo '<div class="clear"></div>';
        }

        foreach ($attendees as $key => $attendee) {
            $reg_form = isset($attendee['reg']) ? $attendee['reg'] : array();
            if ($key === 'attachments') continue;
            if (isset($attendee[0]['MEC_TYPE_OF_DATA'])) continue;

            echo '<hr>';
            echo '<div class="mec-attendee">';
        ?>
            <div class="mec-row">
                <strong><?php _e('Email', 'mec'); ?>: </strong>
                <span class="mec-attendee-email"><?php echo ((isset($attendee['email']) and trim($attendee['email'])) ? $attendee['email'] : '---'); ?></span>
            </div>
            <div class="mec-row">
                <strong><?php _e('Name', 'mec'); ?>: </strong>
                <span class="mec-attendee-name"><?php echo ((isset($attendee['name']) and trim($attendee['name'])) ? $attendee['name'] : '---'); ?></span>
            </div>

            <div class="mec-row">
                <strong><?php echo esc_html__('Event Time:', 'mec-woocommerce'); ?> </strong>
                <span class="mec-attendee-date">
                <?php
                    echo $this->get_date_label($attendee['date'], $transactionObject->get_event_id());
                ?>
            </div>
            <div class="mec-row">
                <strong><?php echo $main->m('ticket', __('Ticket', 'mec')); ?>: </strong>
                <span><?php echo ((isset($attendee['id']) and isset($tickets[$attendee['id']]['name'])) ? $tickets[$attendee['id']]['name'] : __('Unknown', 'mec')); ?></span>
            </div>
            <?php
            // Ticket Variations
            if (isset($attendee['variations']) and is_array($attendee['variations']) and count($attendee['variations'])) {
                $ticket_variations = $main->ticket_variations($event_id,$attendee['id']);
                foreach ($attendee['variations'] as $variation_id => $variation_count) {
                    if (!$variation_count or ($variation_count and $variation_count < 0)) continue;

                    $variation_title = (isset($ticket_variations[$variation_id]) and isset($ticket_variations[$variation_id]['title'])) ? $ticket_variations[$variation_id]['title'] : '';
                    if (!trim($variation_title)) continue;

                    echo '<div class="mec-row">
                <span>+ ' . $variation_title . '</span>
                <span>(' . $variation_count . ')</span>
            </div>';
                }
            }
            ?>
            <?php if (isset($reg_form) && !empty($reg_form)) : foreach ($reg_form as $field_id => $value) : $label = isset($reg_fields[$field_id]) ? $reg_fields[$field_id]['label'] : '';
                    $type = isset($reg_fields[$field_id]) ? $reg_fields[$field_id]['type'] : ''; ?>
                    <?php if ($type == 'agreement') : ?>
                        <div class="mec-row">
                            <strong><?php echo sprintf(__($label, 'mec'), '<a href="' . get_the_permalink($reg_fields[$field_id]['page']) . '">' . get_the_title($reg_fields[$field_id]['page']) . '</a>'); ?>: </strong>
                            <span><?php echo ($value == '1' ? __('Yes', 'mec') : __('No', 'mec')); ?></span>
                        </div>
                    <?php else : ?>
                        <div class="mec-row">
                            <strong><?php _e($label, 'mec'); ?>: </strong>
                            <span><?php echo (is_string($value) ? $value : (is_array($value) ? implode(', ', $value) : '---')); ?></span>
                        </div>
                    <?php endif; ?>
        <?php endforeach;
            endif;
            echo '</div>';
        }
    }

} //MetaBox

MetaBox::instance();
