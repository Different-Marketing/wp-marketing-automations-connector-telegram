<?php
defined( 'ABSPATH' ) || exit; // Exit if accessed directly

class BWFAN_Telegram_Send_Message extends BWFAN_Action {
    private static $instance = null;
    private $progress = false;
    public $support_language = true;

    /**
     * Initialize the class
     *
     * Sets the action name and description
     * Sets the support for versions
     *
     * @return void
     */
    public function __construct() {
        $this->action_name = __( 'Send Telegram Message', 'autonami-automations-connectors' );
        $this->action_desc = __( 'This action sends a message via Telegram', 'autonami-automations-connectors' );
        $this->support_v2  = true;
        $this->support_v1  = false;
    }
    
    /**
     * Returns the instance of the class.
     *
     * @return BWFAN_Telegram_Send_Sms
     * @since 1.0.0
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Load hooks for this action.
     *
     * @since 2.0.0
     */
    public function load_hooks() {
        add_filter( 'bwfan_modify_send_sms_body', array( $this, 'shorten_link' ), 15, 2 );
    }


    /**
     * Shorten URLs in the message body.
     *
     * @since 2.0.0
     *
     * @param string $body The message body.
     * @param array  $data The automation data.
     *
     * @return string The modified message body.
     */
    public function shorten_link( $body, $data ) {
        if ( true === $this->progress ) {
            $body = preg_replace_callback( '/((\w+:\/\/\S+)|(\w+[\.:]\w+\S+))[^\s,\.]/i', array( $this, 'shorten_urls' ), $body );
        }
        return preg_replace_callback( '/((\w+:\/\/\S+)|(\w+[\.:]\w+\S+))[^\s,\.]/i', array( $this, 'unsubscribe_url_with_mode' ), $body );
    }

    /**
     * Shorten URLs in the message body.
     *
     * @since 2.0.0
     *
     * @param array $matches The matches from the preg_replace_callback.
     *
     * @return string The modified message body.
     */
    protected function shorten_urls( $matches ) {
        $string = $matches[0];
        if ( method_exists( 'BWFAN_Connectors_Common', 'get_shorten_url' ) ) {
            return BWFAN_Connectors_Common::get_shorten_url( $string );
        }
        return do_shortcode( '[bwfan_bitly_shorten]' . $string . '[/bwfan_bitly_shorten]' );
    }


    /**
     * Make v2 data for action.
     *
     * @param array $automation_data Automation data.
     * @param array $step_data       Step data.
     *
     * @return array v2 data.
     */
    public function make_v2_data( $automation_data, $step_data ) {
        $this->add_action();
        $this->progress = true;
        $sms_body       = isset( $step_data['sms_body_textarea'] ) ? $step_data['sms_body_textarea'] : '';

        $data_to_set = array(
            'name'            => BWFAN_Common::decode_merge_tags( '{{customer_first_name}}' ),
            'promotional_sms' => ( isset( $step_data['promotional_sms'] ) ) ? 1 : 0,
            'append_utm'      => ( isset( $step_data['sms_append_utm'] ) ) ? 1 : 0,
            'number'          => ( isset( $step_data['sms_to'] ) ) ? BWFAN_Common::decode_merge_tags( $step_data['sms_to'] ) : '',
            'phone'           => ( isset( $step_data['sms_to'] ) ) ? BWFAN_Common::decode_merge_tags( $step_data['sms_to'] ) : '',
            'event'           => ( isset( $step_data['event_data'] ) && isset( $step_data['event_data']['event_slug'] ) ) ? $step_data['event_data']['event_slug'] : '',
            'text'            => BWFAN_Common::decode_merge_tags( $sms_body ),
            'step_id'         => isset( $automation_data['step_id'] ) ? $automation_data['step_id'] : '',
            'automation_id'   => isset( $automation_data['automation_id'] ) ? $automation_data['automation_id'] : '',
        );

        $data_to_set['login']    = isset( $step_data['connector_data']['login'] ) ? $step_data['connector_data']['login'] : '';
        $data_to_set['password'] = isset( $step_data['connector_data']['password'] ) ? $step_data['connector_data']['password'] : '';

        // UTM параметры и другие настройки

        $data_to_set['text'] = stripslashes( $data_to_set['text'] );
        $data_to_set['text'] = BWFAN_Connectors_Common::modify_sms_body( $data_to_set['text'], $data_to_set );

        $this->remove_action();
        return $data_to_set;
    }

    /**
     * Execute the current action.
     * Return 3 for successful execution , 4 for permanent failure.
     * Выполняет отправку SMS
     * @param $action_data
     *
     * @return array
     */
    public function execute_action( $action_data ) {
        global $wpdb;
        $this->set_data( $action_data['processed_data'] );
        $this->data['task_id'] = $action_data['task_id'];

        // Attach track id
        $sql_query         = 'Select meta_value FROM {table_name} WHERE bwfan_task_id = %d AND meta_key = %s';
        $sql_query         = $wpdb->prepare( $sql_query, $this->data['task_id'], 't_track_id' );
        $gids              = BWFAN_Model_Taskmeta::get_results( $sql_query );
        $this->data['gid'] = '';
        if ( ! empty( $gids ) && is_array( $gids ) ) {
            foreach ( $gids as $gid ) {
                $this->data['gid'] = $gid['meta_value'];
            }
        }
    
        // Validate promotional SMS
        if ( 1 === absint( $this->data['promotional_sms'] ) && ( false === apply_filters( 'bwfan_force_promotional_sms', false, $this->data ) ) ) {
            $where             = array(
                'recipient' => $this->data['number'],
                'mode'      => 2,
            );
            $check_unsubscribe = BWFAN_Model_Message_Unsubscribe::get_message_unsubscribe_row( $where );
    
            if ( ! empty( $check_unsubscribe ) ) {
                $this->progress = false;
                return array(
                    'status'  => 4,
                    'message' => __( 'User is already unsubscribed', 'autonami-automations-connectors' ),
                );
            }
        }
  
        // Modify SMS body
        $this->data['text'] = BWFAN_Connectors_Common::modify_sms_body( $this->data['text'], $this->data );
        // Validate connector
        $load_connector = WFCO_Load_Connectors::get_instance();
        $call_class     = $load_connector->get_call( 'wfco_telegram_send_msg' );
        if ( is_null( $call_class ) ) {
            $this->progress = false;
            return array(
                'status'  => 4,
                'message' => __( 'Send Msg call not found', 'autonami-automations-connectors' ),
            );
        }
  
        $integration            = BWFAN_SMSCRU_Integration::get_instance();
        $this->data['login']    = $integration->get_settings( 'login' );
        $this->data['password'] = $integration->get_settings( 'password' );
    
        $call_class->set_data( $this->data );
        $response = $call_class->process();
        do_action( 'bwfan_telegram_action_response', $response, $this->data );
    
        if ( is_array( $response ) && true === $response['status'] ) {
            $this->progress = false;
            return array(
                'status'  => 3,
                'message' => __( 'Msg sent successfully.', 'autonami-automations-connectors' ),
            );
        }
  
        $this->progress = false;
        
        return array(
            'status'  => 4,
            'message' => isset( $response['message'] ) ? $response['message'] : __( 'Msg could not be sent.', 'autonami-automations-connectors' ),
        );
    }

/**
    * Handle response for V2
    *
    * @param array $response V2 response.
    *
    * @return array
    */
    public function handle_response_v2( $response ) {
        do_action( 'bwfan_telegram_action_response', $response, $this->data );
        if ( is_array( $response ) && true === $response['status'] ) {
            $this->progress = false;

            return $this->success_message( __( 'Msg sent successfully.', 'autonami-automations-connectors' ) );
        }
    
        $this->progress = false;

        return $this->skipped_response( isset( $response['message'] ) ? $response['message'] : __( 'Msg could not be sent.', 'autonami-automations-connectors' ) );
    }

    /**
     * Returns an array of field schema for the Telegram connector.
     *
     * The schema includes fields for recipient phone number and message body,
     * each with their respective labels, types, classes, and placeholders. Both
     * fields are required.
     *
     * @return array An array of field schema.
     */
    public function get_fields_schema() {
        return [
            [
                'id'          => 'sms_to',
                'label'       => __( "To", 'wp-marketing-automations' ),
                'type'        => 'text',
                'placeholder' => "",
                "class"       => 'bwfan-input-wrapper',
                'tip'         => __( '', 'autonami-automations-connectors' ),
                "description" => '',
                "required"    => true,
            ],
            [
                'id'          => 'sms_body_textarea',
                'label'       => __( "Text", 'wp-marketing-automations' ),
                'type'        => 'textarea',
                'placeholder' => "Message Body",
                "class"       => 'bwfan-input-wrapper',
                'tip'         => __( '', 'autonami-automations-connectors' ),
                "description" => '',
                "required"    => true,
            ],
            // Другие поля...
        ];
    }
    
    /**
     * Adds filters to change the newline separator in billing and shipping addresses.
     *
     * This method is called by the parent class's constructor.
     */
    private function add_action() {
        add_filter( 'bwfan_order_billing_address_separator', array( $this, 'change_br_to_slash_n' ) );
        add_filter( 'bwfan_order_shipping_address_separator', array( $this, 'change_br_to_slash_n' ) );
    }
      
    /**
     * Removes the filters that were added in add_action.
     * 
     * Filters are removed for bwfan_order_billing_address_params and
     * bwfan_order_shipping_address_separator.
     */
    private function remove_action() {
        remove_filter( 'bwfan_order_billing_address_params', array( $this, 'change_br_to_slash_n' ) );
        remove_filter( 'bwfan_order_shipping_address_separator', array( $this, 'change_br_to_slash_n' ) );
    }

    /**
     * Replaces <br /> with \n in the given string.
     * 
     * @param string $params The string to replace <br /> with \n.
     * 
     * @return string The string with <br /> replaced with \n.
     */
    public function change_br_to_slash_n( $params ) {
		
        return "\n";
	}

}

return 'BWFAN_Telegram_Send_Msg';