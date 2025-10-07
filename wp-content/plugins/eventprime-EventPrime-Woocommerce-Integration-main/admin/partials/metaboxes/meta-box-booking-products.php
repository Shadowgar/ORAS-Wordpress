<?php
/**
 * Booking meta box html
 */
defined( 'ABSPATH' ) || exit;
$booking_controller = new Eventprime_Basic_Functions();

$booking_id = $post->ID;
$post_meta = get_post_meta($booking_id);
$booking = $booking_controller->load_booking_detail($booking_id);  

$ep_functions = new Eventprime_Basic_Functions(); 
?>

    <div class="panel-wrap ep_event_metabox">
    <?php if( ! empty( $booking->em_order_info['woocommerce_products'] ) && count( $booking->em_order_info['woocommerce_products'] ) > 0 ) {
            $woocommerce_products = $booking->em_order_info['woocommerce_products'];
            // print_r($woocommerce_products);
            ?>
        
                <div class="ep-border-bottom">
                    <div class="ep-py-3 ep-ps-3 ep-fw-bold ep-text-uppercase ep-text-small">
                        <?php esc_html_e( 'Products', 'eventprime-woocommerce-integration' );?>
                    </div>
                </div>
                <?php $booking_attendees_field_labels = array();
                // foreach( $booking->em_attendee_names as $ticket_id => $attendee_data ) { 
                //     $booking_attendees_field_labels = $ep_functions->ep_get_booking_attendee_field_labels( $attendee_data[1] );
                    
                    
                    ?>

                
                        <div class="ep-p-4">
                           
                            <table class="ep-table ep-table-hover ep-text-small ep-table-borderless">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col"><?php esc_html_e( 'Image', 'eventprime-woocommerce-integration' );?></th>
                                        <th scope="col"><?php esc_html_e( 'Name', 'eventprime-woocommerce-integration' );?></th>
                                        <th scope="col"><?php esc_html_e( 'Quantity', 'eventprime-woocommerce-integration' );?></th>
                                        <th scope="col"><?php esc_html_e( 'Price', 'eventprime-woocommerce-integration' );?></th>
                                        <th scope="col"><?php esc_html_e( 'Total', 'eventprime-woocommerce-integration' );?></th>
                                    </tr>
                                </thead>
                                <tbody class=""><?php $i =1;
                                    foreach( $woocommerce_products as $woo ) {
                                        ?>
                                        <tr>
                                            <th scope="row" class="py-3"><?php echo esc_html( $i );?></th>
                                            <td class="py-3"> <?php echo $woo->image;?></td>
                                            <td class="py-3"> 
                                                <?php 
                                                    $variation_name = "";
                                                    if( isset( $woo->variation ) && ! empty( $woo->variation ) ){
                                                        // $ep_functions->epd( $woo->variation[0]['variation_id'] );
                                                        $variation_id = isset($woo->variation[0]->variation_id) && !empty($woo->variation[0]->variation_id) ? $woo->variation[0]->variation_id : ''; 
                                                        $variation = new WC_Product_Variation($variation_id);
                                                        // $ep_functions->epd($variation->get_name());
                                                        $variation_name = $variation->get_name();
                                                        // echo '<p>'.$woo->variation[0]->attr_label. ' : '.ucfirst( $woo->variation[0]->attr_value ).'</p>';
                                                        // echo '<p>'.$woo->variation[1]->attr_label. ' : '.ucfirst( $woo->variation[1]->attr_value ).'</p>';
                                                    }
                                                    if( !empty( $variation_name ) ) {
                                                        echo esc_html( $variation_name );
                                                    } else {
                                                        echo esc_html( $woo->name );
                                                    }
                                                ?>
                                            </td>
                                            <td class="py-3"><?php echo $woo->qty; ?></td>
                                            <td class="py-3"><?php echo $ep_functions->ep_price_with_position($woo->price); ?></td>
                                            <td class="py-3">
                                                <?php 
                                                    $subTotal = $woo->price * $woo->qty;
                                                    echo $ep_functions->ep_price_with_position($subTotal); 
                                                ?>
                                            </td>
                                            
                                        </tr>
                                        <?php
                                        $i++;
                                    }?>
                                </tbody>
                            </table>
                        </div>
                    <?php
                // }
                ?>
            <?php
        }?>
    </div>

