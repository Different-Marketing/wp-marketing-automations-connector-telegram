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
        error_log('WFCO_Telegram_Call constructed with API key: ' . $this->api_key);
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
        error_log('Telegram request method: ' . $method);
        error_log('Telegram request data: ' . print_r($data, true));

        $args = array(
            'method'  => $method,
            'headers' => array(
                'Content-Type'  => 'application/json',
            ),
        );

        if ($method === 'POST') {
            $args['body'] = wp_json_encode($data);
        }

        $response = wp_remote_request( $url, $args );

        if ( is_wp_error( $response ) ) {
            error_log('Telegram API error: ' . $response->get_error_message());
            return $response;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        error_log('Telegram API response code: ' . $response_code);
        error_log('Telegram API response body: ' . $response_body);

        return json_decode( $response_body, true );
    }

    /**
     * Sets the data for the API call
     *
     * @param array $data API call data
     */
    public function set_data($data) {
        $this->data = $data;
        error_log('Data set for WFCO_Telegram_Call: ' . print_r($this->data, true));
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

            if (empty($body['text']) && $method !== 'getMe') {
                error_log('Telegram API error: Message text is empty');
                return array(
                    'status'   => 'error',
                    'message'  => 'Message text is empty',
                );
            }

            $response = $this->make_request('sendMessage', 'POST', $body);
        }

        if (isset($response['ok']) && $response['ok'] === true) {
            error_log('Telegram API call successful');
            return array(
                'status'   => 'success',
                'message'  => __('Operation completed successfully', 'autonami-automations-connectors'),
                'data'     => $response,
            );
        } else {
            error_log('Telegram API call failed: ' . print_r($response, true));
            return array(
                'status'   => 'error',
                'message'  => isset($response['description']) ? $response['description'] : __('Unknown error occurred', 'autonami-automations-connectors'),
                'data'     => $response,
            );
        }
    }
}