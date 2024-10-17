<?php

defined( 'ABSPATH' ) || exit; // Exit if accessed directly

class WFCO_Telegram_Call {
    protected $api_key;
    protected $base_url = 'https://api.telegram.org/bot';
    protected $data = array();

    /**
     * Initializes the Telegram API key for the WFCO_Telegram_Call class.
     *
     * This constructor sets the `$api_key` property of the class by retrieving the
     * Telegram API key from the `WFCO_Telegram_Common::get_api_key()` function.
     */
    public function __construct() {
        $this->api_key = WFCO_Telegram_Common::get_api_key();
    }

    protected function make_request( $endpoint, $method = 'POST', $data = array() ) {
        if (empty($this->api_key)) {
            error_log('Telegram API key is missing');
            return new WP_Error('api_key_missing', 'Telegram API key is missing');
        }

        $url = $this->base_url . $this->api_key . '/' . $endpoint;

        error_log('Telegram API endpoint: ' . $url);
        error_log('Telegram request data: ' . print_r($data, true));

        $args = array(
            'method'  => $method,
            'headers' => array(
                'Content-Type'  => 'application/json',
            ),
            'body' => wp_json_encode( $data ),
        );

        $response = wp_remote_request( $url, $args );

        if ( is_wp_error( $response ) ) {
            error_log('Telegram API error: ' . $response->get_error_message());
            return $response;
        }

        error_log('Telegram API response code: ' . wp_remote_retrieve_response_code($response));
        error_log('Telegram API response body: ' . wp_remote_retrieve_body($response));

        return json_decode( wp_remote_retrieve_body( $response ), true );
    }

    public function set_data($data) {
        $this->data = $data;
    }

    public function process() {
        $endpoint = 'sendMessage';

        error_log(print_r($this->data, true)); //PHP Warning:  Undefined variable $data

        $bot_token = isset($this->data['bot_token']) ? $this->data['bot_token'] : '';
        $chat_id = isset($this->data['chat_id']) ? $this->data['chat_id'] : '';
        $message = isset($this->data['default_message']) ? $this->data['default_message'] : '';

        // Добавленный отладочный код
        error_log('Telegram Bot Token: ' . $bot_token);
        error_log('Telegram Chat ID: ' . $chat_id);
        error_log('Telegram Message: ' . $message);
        error_log('Telegram API Key: ' . $this->api_key);

        if (empty($bot_token)) {
            error_log('Telegram bot token is missing');
            return array(
                'status' => 'error',
                'message' => 'Bot Token is missing',
                'api_data' => array(),
            );
        }
/*
        if (empty($chat_id)) {
            error_log('Telegram chat_id is missing');
            return array(
                'status' => 'error',
                'message' => 'Chat ID is missing',
                'api_data' => array(),
            );
        }

        if (empty($message)) {
            error_log('Telegram message is missing');
            return array(
                'status' => 'error',
                'message' => 'Message is missing',
                'api_data' => array(),
            );
        }
*/
        $this->api_key = $bot_token;

        $body = array(
            'chat_id' => $chat_id,
            'text'    => $message,
        );

        // Если метод getMe, не нужно отправлять chat_id и text
        if ($endpoint === 'getMe') {
            $body = array();
        }

        $response = $this->make_request($endpoint, 'POST', $body);

        if (is_wp_error($response)) {
            return array(
                'status'   => 'error',
                'message'  => $response->get_error_message(),
            );
        }

        if (isset($response['ok']) && $response['ok'] === true) {
            return array(
                'status'   => 'success',
                'message'  => __('Operation completed successfully', 'autonami-automations-connectors'),
                'data'     => $response,
            );
        } else {
            return array(
                'status'   => 'error',
                'message'  => isset($response['description']) ? $response['description'] : __('Unknown error occurred', 'autonami-automations-connectors'),
                'data'     => $response,
            );
        }
    }
}
