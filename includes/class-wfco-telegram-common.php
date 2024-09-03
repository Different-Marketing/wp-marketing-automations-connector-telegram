<?php
defined( 'ABSPATH' ) || exit; // Exit if accessed directly

class WFCO_Telegram_Common {
    public static function get_api_key() {
        return get_option( 'wfco_telegram_api_key', '' );
    }

    public static function get_api_secret() {
        return get_option( 'wfco_telegram_api_secret', '' );
    }

    public static function is_connected() {
        return ! empty( self::get_api_key() ) && ! empty( self::get_api_secret() );
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
        } elseif (isset($response['body'])) {
            $body = json_decode($response['body'], true);
            if (isset($body['error'])) {
                $error_message = $body['error'];
            }
        }

        return $error_message ? $error_message : __('Unknown error occurred', 'autonami-automations-connectors');
    }
}
