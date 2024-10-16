<?php
defined( 'ABSPATH' ) || exit; // Exit if accessed directly

class WFCO_Telegram_Send_Msg extends WFCO_Call {
    private static $ins = null;
    protected $api_key;
    protected $base_url = 'https://api.telegram.org/bot';

    public function __construct() {
        $this->api_key = WFCO_Telegram_Common::get_api_key();
    }

    public static function get_instance() {
        if (null === self::$ins) {
            self::$ins = new self();
        }
        return self::$ins;
    }

    public function process() {
        $endpoint = 'sendMessage';
            if (empty($this->api_key)) {
                error_log('Telegram API key is missing');
                return new WP_Error('api_key_missing', 'Telegram API key is missing');
            }
            $url = $this->base_url . $this->api_key . '/' . $endpoint;

        $body = array(
            'chat_id' => isset($this->data['chat_id']) ? $this->data['chat_id'] : '',
            'text'    => isset($this->data['message']) ? $this->data['message'] : '',
        );

        error_log('Telegram API request params: ' . print_r($body, true));

        $args = array(
            'body'    => $body,
            'method'  => 'POST',
            'timeout' => 30,
        );

        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            error_log('Telegram API error: ' . $response->get_error_message());
            return array(
                'status' => false,
                'message' => $response->get_error_message(),
            );
        }

        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);

        error_log('Telegram API response: ' . print_r($result, true));

        if (isset($result['ok']) && $result['ok'] === true) {
            return array(
                'status' => true,
                'message' => 'Message sent successfully',
                'data' => $result,
            );
        } else {
            return array(
                'status' => false,
                'message' => isset($result['description']) ? $result['description'] : 'Unknown error occurred',
                'data' => $result,
            );
        }
    }
}

return 'WFCO_Telegram_Send_Msg';
