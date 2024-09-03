<?php
$saved_data = WFCO_Common::$connectors_saved_data;
$old_data   = ( isset( $saved_data[ $this->get_slug() ] ) && is_array( $saved_data[ $this->get_slug() ] ) && count( $saved_data[ $this->get_slug() ] ) > 0 ) ? $saved_data[ $this->get_slug() ] : array();
$login      = isset( $old_data['login'] ) ? $old_data['login'] : '';
$password   = isset( $old_data['password'] ) ? $old_data['password'] : '';
?>
<form method="post" action="options.php">
    <?php settings_fields( 'wfco_telegram_settings' ); ?>
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><?php _e( 'API Key', 'woofunnels-telegram-connector' ); ?></th>
            <td>
                <input type="text" name="wfco_telegram_api_key" value="<?php echo esc_attr( get_option( 'wfco_telegram_api_key' ) ); ?>" />
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e( 'API Secret', 'woofunnels-telegram-connector' ); ?></th>
            <td>
                <input type="password" name="wfco_telegram_api_secret" value="<?php echo esc_attr( get_option( 'wfco_telegram_api_secret' ) ); ?>" />
            </td>
        </tr>
    </table>
    <?php submit_button(); ?>
</form>
