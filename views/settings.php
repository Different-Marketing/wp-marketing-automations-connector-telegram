<?php
$saved_data = WFCO_Common::$connectors_saved_data;
$old_data   = ( isset( $saved_data[ $this->get_slug() ] ) && is_array( $saved_data[ $this->get_slug() ] ) && count( $saved_data[ $this->get_slug() ] ) > 0 ) ? $saved_data[ $this->get_slug() ] : array();
$bot_token  = isset( $old_data['bot_token'] ) ? $old_data['bot_token'] : '';
$chat_id    = isset( $old_data['chat_id'] ) ? $old_data['chat_id'] : '';
$default_message = isset( $old_data['default_message'] ) ? $old_data['default_message'] : '';
?>
<div class="wfco-telegram-wrap">
    <div class="wfco-form-group featured field-input">
        <label for="bot_token"><?php echo esc_html__( 'Bot Token', 'autonami-automations-connectors' ); ?></label>
        <div class="field-wrap">
            <input type="text" name="bot_token" id="bot_token" value="<?php echo esc_attr( $bot_token ); ?>" class="wfco-input" placeholder="<?php echo esc_attr__( 'Enter Telegram Bot Token', 'autonami-automations-connectors' ); ?>">
        </div>
    </div>

    <div class="wfco-form-group featured field-input">
        <label for="chat_id"><?php echo esc_html__( 'Chat ID', 'autonami-automations-connectors' ); ?></label>
        <div class="field-wrap">
            <input type="text" name="chat_id" id="chat_id" value="<?php echo esc_attr( $chat_id ); ?>" class="wfco-input" placeholder="<?php echo esc_attr__( 'Enter Telegram Chat ID', 'autonami-automations-connectors' ); ?>">
        </div>
    </div>

    <div class="wfco-form-group featured field-input">
        <label for="default_message"><?php echo esc_html__( 'Default Message', 'autonami-automations-connectors' ); ?></label>
        <div class="field-wrap">
            <textarea name="default_message" id="default_message" class="wfco-input" rows="5" placeholder="<?php echo esc_attr__( 'Enter default message', 'autonami-automations-connectors' ); ?>"><?php echo esc_textarea( $default_message ); ?></textarea>
        </div>
    </div>

    <div class="wfco-form-group">
        <?php
        if ( isset( $old_data['id'] ) && (int) $old_data['id'] > 0 ) {
            ?>
            <input type="hidden" name="edit_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wfco-connector-edit' ) ); ?>"/>
            <input type="hidden" name="id" value="<?php echo esc_attr( $old_data['id'] ); ?>"/>
            <input type="hidden" name="wfco_connector" value="<?php echo esc_attr( $this->get_slug() ); ?>"/>
            <button class="wfco-save-btn wfco-connect-btn"><?php esc_html_e( 'Update', 'autonami-automations-connectors' ); ?></button>
        <?php } else { ?>
            <input type="hidden" name="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'wfco-connector' ) ); ?>">
            <input type="hidden" name="wfco_connector" value="<?php echo esc_attr( $this->get_slug() ); ?>"/>
            <button class="wfco-save-btn wfco-connect-btn"><?php esc_html_e( 'Connect', 'autonami-automations-connectors' ); ?></button>
        <?php } ?>
    </div>
</div>
