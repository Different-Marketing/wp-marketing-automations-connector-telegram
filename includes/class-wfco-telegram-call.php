<?php
defined( 'ABSPATH' ) || exit; // Exit if accessed directly

class WFCO_Telegram_Call {
    protected $api_key;
    protected $base_url = 'https://api.telegram.org/bot';
    protected $data = array();

    /**
     * Constructor: initializes the API key
     */
    public function __construct() {
        $this->api_key = WFCO_Telegram_Common::get_api_key();
    }

    /**
     * Makes a request to the Telegram API
     *
     * @param string $endpoint API endpoint
     * @param string $method HTTP method (GET or POST)
     * @param array $data Request data
     * @return array|WP_Error API response or error
     */
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

    /**
     * Sets the data for the API call
     *
     * @param array $data API call data
     */
    public function set_data($data) {
        $this->data = $data;
    }

    /**
     * Processes the API call
     *
     * @return array API response
     */
    public function process() {
        $method = isset($this->data['method']) ? $this->data['method'] : 'sendMessage';

        error_log('Processing Telegram API call: ' . $method);
        error_log('Call data: ' . print_r($this->data, true));

        if ($method === 'getMe') {
            $response = $this->make_request('getMe', 'GET');
        } else {
            $body = array(
                'chat_id' => isset($this->data['chat_id']) ? $this->data['chat_id'] : '',
                'text'    => isset($this->data['message']) ? $this->data['message'] : '',
            );

            // Remove empty fields
            $body = array_filter($body);

            if (empty($body['text'])) {
                return array(
                    'status'   => 'error',
                    'message'  => 'Message text is empty',
                );
            }

            $response = $this->make_request('sendMessage', 'POST', $body);
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