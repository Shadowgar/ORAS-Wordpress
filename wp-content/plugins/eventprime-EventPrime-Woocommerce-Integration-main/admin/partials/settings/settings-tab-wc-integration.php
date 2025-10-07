<div class="ep-setting-tab-content">
    <h2><?php esc_html_e( 'Allow Woocommerce Integration', 'eventprime-event-woocommerce-integration' );?></h2>
    <input type="hidden" name="em_setting_type" value="wc_integration">
</div>
<table class="form-table">
    <tbody>
      
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="enable_twilio_text_services">
                    <?php esc_html_e( 'Allow Woocommerce Integration', 'eventprime-event-woocommerce-integration' );?>
                </label>
            </th>
            <td class="forminp forminp-text">
                <label class="ep-toggle-btn">
                    <input type="checkbox" name="allow_woocommerce_integration" id="allow_woocommerce_integration" value="1" <?php echo isset( $options['global']->allow_woocommerce_integration ) && $options['global']->allow_woocommerce_integration == 1 ? 'checked' : '';?>>
                    <span class="ep-toogle-slider round"></span>
                </label>
                <div class="ep-help-tip-info ep-my-2 ep-text-muted"><?php esc_html_e( 'Allow EventPrime integration with Woocommerce.', 'eventprime-event-woocommerce-integration' );?></div>
            </td>
        </tr>
    </tbody>
</table>
