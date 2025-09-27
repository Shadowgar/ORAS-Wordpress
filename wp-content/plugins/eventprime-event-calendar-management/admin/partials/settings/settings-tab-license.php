<?php
$global_settings = new Eventprime_Global_Settings;
$admin_notices = new EventM_Admin_Notices;
$ep_functions = new Eventprime_Basic_Functions;
$ep_license = new EventPrime_License;
$ep_sanitizer = new EventPrime_sanitizer;
$sub_options = $global_settings->sub_options;
$options = $global_settings->ep_get_settings();
wp_enqueue_style( 'ep-toast-css' );
wp_enqueue_script( 'ep-toast-js' );
wp_enqueue_script( 'ep-toast-message-js' );     
// save license key
if( isset( $_POST['submit'] ) && ! empty( $_POST['submit'] ) ){
    $form_data = $ep_sanitizer->sanitize($_POST);
    $options->ep_premium_license_key  = ( isset( $form_data['ep_premium_license_key'] ) && ! empty( $form_data['ep_premium_license_key'] ) ) ? $form_data['ep_premium_license_key'] : '';
    $global_settings->ep_save_settings( $options );
}
$key = 'ep_premium';
$id = $key.'_license_key';

$ep_license_obj = $ep_license->ep_get_license_detail($key, $options);
$ep_premium_license_key = $ep_license_obj->license_key;
$ep_license_status = $ep_license_obj->license_status;
$ep_license_response = $ep_license_obj->license_response;
$ep_premium_license_option_value = $ep_license_obj->license_option_value;
$bundle_id = $ep_license_obj->item_id;
$is_any_ext_activated = $ep_license->ep_get_activate_extensions();


$deactivate_license_btn = $key.'_license_deactivate';
$activate_license_btn = $key.'_license_activate';
?>
<div class="ep-setting-tab-content">
    <h2><?php esc_html_e( 'Plugin License Options', 'eventprime-event-calendar-management' ); ?></h2>
    <p><strong><?php esc_html_e( 'Read about activating licenses ', 'eventprime-event-calendar-management' );?><a target="_blank" href="<?php echo esc_url( 'https://theeventprime.com/adding-license-keys-eventprime/' );?>"><?php esc_html_e( 'here', 'eventprime-event-calendar-management' ); ?></a></strong></p>
</div>

<?php if( isset( $is_any_ext_activated ) && !empty($is_any_ext_activated ) ) {?>
  <table class="form-table">
                <tbody>
                    <tr>
                        <td class="ep-form-table-wrapper" colspan="2">
                            <table class="ep-form-table-setting ep-setting-table widefat">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e( 'Name', 'eventprime-event-calendar-management' );?></th>
                                        <th><?php esc_html_e( 'License Key', 'eventprime-event-calendar-management' );?></th>
                                        <th><?php esc_html_e( 'Validity', 'eventprime-event-calendar-management' );?></th>
                                        <th><?php esc_html_e( 'Action', 'eventprime-event-calendar-management' );?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    
                                    
                                    <?php 
                                        foreach($is_any_ext_activated as $key=>$product):
                                        if(empty($product) || $product[0]=='')
                                        {
                                            continue;
                                        }
                                        //echo $key;die;
                                          
                                          $id = $key.'_license_key';
                                          $response = $key.'_license_response';
                                          $status = $key.'_license_status';
                                          
                                            $ep_license_obj = $ep_license->ep_get_license_detail($key, $options);
                                            $ep_premium_license_key = $ep_license_obj->license_key;
                                            $ep_license_status = $ep_license_obj->license_status;
                                            $ep_license_response = $ep_license_obj->license_response;
                                            $ep_premium_license_option_value = $ep_license_obj->license_option_value;
                                            $bundle_id = $ep_license_obj->item_id;
                                            $deactivate_license_btn = $key.'_license_deactivate';
                                            $activate_license_btn = $key.'_license_activate';
                                        ?>
                                    
                                            <tr valign="top" class="<?php esc_attr_e($key);?>">
                    <td><?php esc_html_e( $product[1], 'eventprime-event-calendar-management' );?></td>
                    <td><input id="<?php esc_attr_e($id);?>" name="<?php esc_attr_e($id);?>" type="text" class="regular-text ep-box-wrap ep-license-block" data-prefix="<?php esc_attr_e($product[0]);?>" data-key="<?php esc_attr_e($key);?>" value="<?php esc_attr_e($ep_premium_license_key); ?>" placeholder="<?php esc_html_e( 'Please Enter License Key', 'eventprime-event-calendar-management' );?>" /></td>
                    <td>         
                        <span class="license-expire-date" style="padding-bottom:2rem;" >
                            <?php
                            if ( ! empty( $ep_license_response->expires ) && ! empty( $ep_license_status ) && $ep_license_status == 'valid' ) {
                                if( $ep_license_response->expires == 'lifetime' ){
                                    esc_html_e( 'Your License key is activated for lifetime', 'eventprime-event-calendar-management' );
                                }else{
                                    echo sprintf( esc_html__('Your License Key expires on %s', 'eventprime-event-calendar-management' ), esc_html(gmdate( 'F d, Y', strtotime( $ep_license_response->expires ) )) );
                                }
                            } else {
                                $expire_date = '';
                            }
                            ?>
                        </span>
                    </td>
                    <td>
                        <span class="<?php esc_attr_e($key);?>-license-status-block">
                            <?php if ( isset( $ep_premium_license_key ) && ! empty( $ep_premium_license_key )) { ?>
                                <?php if ( isset( $ep_license_status ) && $ep_license_status !== false && $ep_license_status == 'valid' ) { ?>
                                    <button type="button" class="button action ep-my-2 ep_license_deactivate" name="<?php esc_attr_e($deactivate_license_btn);?>" id="<?php esc_attr_e($deactivate_license_btn);?>" data-prefix="<?php esc_attr_e($product[0]); ?>" data-key="<?php esc_attr_e($key);?>" value="<?php esc_html_e( 'Deactivate License', 'eventprime-event-calendar-management' );?>"><?php esc_html_e( 'Deactivate License', 'eventprime-event-calendar-management' );?></button>
                                <?php } elseif( ! empty( $ep_license_status ) && $ep_license_status == 'invalid' ) { ?>
                                    <button type="button" class="button action ep-my-2 ep_license_activate" name="<?php esc_attr_e($activate_license_btn);?>" id="<?php esc_attr_e($activate_license_btn);?>" data-prefix="<?php esc_attr_e($product[0]); ?>" data-key="<?php esc_attr_e($key);?>" value="<?php esc_html_e( 'Activate License', 'eventprime-event-calendar-management' );?>"><?php esc_html_e( 'Activate License', 'eventprime-event-calendar-management' );?></button>
                                <?php }else{ ?>
                                    <button type="button" class="button action ep-my-2 ep_license_activate" name="<?php esc_attr_e($activate_license_btn);?>" id="<?php esc_attr_e($activate_license_btn);?>" data-prefix="<?php esc_attr_e($product[0]); ?>" data-key="<?php esc_attr_e($key);?>" value="<?php esc_html_e( 'Activate License', 'eventprime-event-calendar-management' );?>" style="<?php if ( empty( $ep_premium_license_key ) ){ echo 'display:none'; } ?>"><?php esc_html_e( 'Activate License', 'eventprime-event-calendar-management' );?></button>
                                <?php } }else{ ?>
                                    <button type="button" class="button action ep-my-2 ep_license_activate" name="<?php esc_attr_e($activate_license_btn);?>" id="<?php esc_attr_e($activate_license_btn);?>" data-prefix="<?php esc_attr_e($product[0]); ?>" data-key="<?php esc_attr_e($key);?>" value="<?php esc_html_e( 'Activate License', 'eventprime-event-calendar-management' );?>" style="display:none;"><?php esc_html_e( 'Activate License', 'eventprime-event-calendar-management' );?></button>
                                <?php } ?>
                        </span>
                    </td>
                </tr>
         
                                    
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
<?php }
else
{
   ?>
<div class="">
  <?php
printf(
    esc_html__(
        'Note: License key fields will appear %1$safter activating your purchased extensions.%2$s If you don’t see any fields yet, please activate the corresponding extensions from the %3$sPlugins%4$s page.', 
        'eventprime-event-calendar-management'
    ),
    '<strong>', '</strong>', '<strong>', '</strong>'
);

echo '<br>';

printf(
    esc_html__(
        'You can download your purchased extensions from your %1$sOrder History%2$s page.', 
        'eventprime-event-calendar-management'
    ),
    '<a href="https://theeventprime.com/checkout/order-history/" target="_blank">', '</a>'
);
?>

</div>
    <?php
}

?>

