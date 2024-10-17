<?php
defined( 'ABSPATH' ) || exit; // Exit if accessed directly

class WFCO_Telegram_Common {
    /**
     * Gets the Telegram API key.
     *
     * @return string The Telegram API key, or an empty string if not set.
     */
    public static function get_api_key() {
        $api_key = get_option('wfco_telegram_bot_token', '');
        error_log('Telegram API Key from get_api_key: ' . $api_key);
        return $api_key;
    }

    /**
     * Checks if the Telegram API is connected.
     *
     * @return bool True if the Telegram API is connected, false otherwise.
     */
    public static function is_connected() {
        return ! empty( self::get_api_key() );
    }

    /**
     * Handle errors from API responses.
     *
     * @param array|WP_Error $response The API response to handle the error for.
     *
     * @return string The error message if an error occurred, otherwise an empty string.
     */
    public static function handle_error($response) {
        $error_message = '';

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
        } elseif (isset($response['description'])) {
            $error_message = $response['description'];
        }

        error_log('Telegram API error: ' . $error_message);

        return $error_message ? $error_message : __('Unknown error occurred', 'autonami-automations-connectors');
    }

    /**
     * Log API request and response for debugging purposes.
     *
     * @param string $endpoint The API endpoint.
     * @param array $request_data The request data.
     * @param array|WP_Error $response The API response.
     */
    public static function log_api_request($endpoint, $request_data, $response) {
        error_log('Telegram API Request - Endpoint: ' . $endpoint);
        error_log('Telegram API Request - Data: ' . print_r($request_data, true));
        
        if (is_wp_error($response)) {
            error_log('Telegram API Response - Error: ' . $response->get_error_message());
        } else {
            error_log('Telegram API Response - Status: ' . wp_remote_retrieve_response_code($response));
            error_log('Telegram API Response - Body: ' . wp_remote_retrieve_body($response));
        }
    }

    /**
     * Gets the Telegram chat ID.
     *
     * @return string The Telegram chat ID.
     */
    public static function get_chat_id() {
        return get_option('wfco_telegram_chat_id', '');
    }
    
    /**
     * Gets the default Telegram message.
     *
     * @return string The default Telegram message.
     */
    public static function get_default_message() {
        return get_option('wfco_telegram_default_message', '');
    }
    
    /**
     * Saves the Telegram settings.
     *
     * @param array $settings The settings to save, with keys 'api_key', 'chat_id', and 'default_message'.
     */
    public static function save_settings($settings) {
        update_option('wfco_telegram_api_key', $settings['api_key']);
        update_option('wfco_telegram_chat_id', $settings['chat_id']);
        update_option('wfco_telegram_default_message', $settings['default_message']);
    }
    
    /**
     * Gets all Telegram settings.
     *
     * @return array An array containing all Telegram settings.
     */
    public static function get_all_settings() {
        return array(
            'api_key' => self::get_api_key(),
            'chat_id' => self::get_chat_id(),
            'default_message' => self::get_default_message(),
        );
    }
}
