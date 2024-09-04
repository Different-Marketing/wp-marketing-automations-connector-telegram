<?php
defined( 'ABSPATH' ) || exit; // Exit if accessed directly

class WFCO_Telegram_Send_Msg extends WFCO_Call {
    private static $ins = null;
    //protected $api_key;
    //protected $api_secret;
    //protected $base_url = 'https://api.[channel].com/v1/';

    public function __construct() {
        $this->api_key = WFCO_telegram_Common::get_api_key();
        $this->api_secret = WFCO_telegram_Common::get_api_secret();
    }

    public static function get_instance() {
        if (null === self::$ins) {
            self::$ins = new self();
        }
        return self::$ins;
    }

    public function process() {
        $params = array(
            'login'    => $this->data['login'],
            'psw'      => $this->data['password'],
            'phones'   => '79119387283',
            'mes'      => 'TEST',
            'charset'  => 'utf-8',
            'fmt'      => 3, // JSON response format
            'cost'     => 3, // Return cost info
        );

        if (!empty($this->data['sender'])) {
            $params['sender'] = $this->data['sender'];
        }

        if (!empty($this->data['translit'])) {
            $params['translit'] = $this->data['translit'];
        }

        //$url = add_query_arg($params, $this->api_endpoint);

        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            return array(
                'status' => false,
                'message' => $response->get_error_message(),
            );
        }

        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);

        if (isset($result['error'])) {
            return array(
                'status' => false,
                'message' => $result['error'],
            );
        }

        return array(
            'status' => true,
            'message' => 'Message sent successfully',
            'data' => $result,
        );
    }

    protected function make_request( $endpoint, $method = 'GET', $data = array() ) {
        $url = $this->base_url . $endpoint;

        $args = array(
            'method'  => $method,
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type'  => 'application/json',
            ),
        );

        if ( ! empty( $data ) ) {
            $args['body'] = wp_json_encode( $data );
        }

        $response = wp_remote_request( $url, $args );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        return json_decode( wp_remote_retrieve_body( $response ), true );
    }
}
return 'WFCO_Telegram_Send_Msg';