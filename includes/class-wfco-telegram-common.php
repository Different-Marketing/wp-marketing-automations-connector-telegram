<?php
defined( 'ABSPATH' ) || exit; // Exit if accessed directly

class WFCO_Telegram_Common {
    public static function get_api_key() {
        return get_option( 'wfco_telegram_api_key', '' );
    }

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

    public static function get_chat_id() {
        return get_option('wfco_telegram_chat_id', '');
    }
    
    public static function get_default_message() {
        return get_option('wfco_telegram_default_message', '');
    }
    
    public static function save_settings($settings) {
        update_option('wfco_telegram_api_key', $settings['api_key']);
        update_option('wfco_telegram_chat_id', $settings['chat_id']);
        update_option('wfco_telegram_default_message', $settings['default_message']);
    }
    
}
