<?php
defined( 'ABSPATH' ) || exit; // Exit if accessed directly

class WFCO_telegram_Call {
    protected $api_key;
    protected $api_secret;
    protected $base_url = 'https://api.[channel].com/v1/';

    public function __construct() {
        $this->api_key = WFCO_telegram_Common::get_api_key();
        $this->api_secret = WFCO_telegram_Common::get_api_secret();
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
