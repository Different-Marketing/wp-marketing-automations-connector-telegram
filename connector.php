<?php
defined( 'ABSPATH' ) || exit; // Exit if accessed directly

class BWFCO_TELEGRAM extends BWF_CO {

    public static $instance = null;
    public $v2 = true;

    /**
     * Constructor.
     *
     * @since 1.1.0
     */
    public function __construct() {
        $this->connector_url     = WFCO_TELEGRAM_PLUGIN_URL;
        $this->dir               = __DIR__;
        $this->nice_name         = __('Telegram', 'autonami-automations-connectors');
        $this->autonami_int_slug = 'BWFAN_Telegram_Integration';

        $this->keys_to_track = array(
            'bot_token',
        );
        $this->form_req_keys = array(
            'bot_token',
        );

        add_filter('wfco_connectors_loaded', array($this, 'add_card'));
    }

    /**
     * Returns an instance of the class.
     *
     * @return BWFCO_TELEGRAM
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Returns an array of field schema for the Telegram connector.
     *
     * @return array An array of field schema.
     */
    public function get_fields_schema() {
        return array(
            array(
                'id'          => 'bot_token',
                'label'       => __('Bot Token', 'autonami-automations-connectors'),
                'type'        => 'text',
                'class'       => 'bwfan_telegram_bot_token',
                'placeholder' => __('Enter your Telegram Bot Token', 'autonami-automations-connectors'),
                'required'    => true,
            ),
            array(
                'id'          => 'chat_id',
                'label'       => __('Chat ID', 'autonami-automations-connectors'),
                'type'        => 'text',
                'class'       => 'bwfan_telegram_chat_id',
                'placeholder' => __('Enter Telegram Chat ID', 'autonami-automations-connectors'),
                'required'    => true,
            ),
            array(
                'id'          => 'default_message',
                'label'       => __('Default Message', 'autonami-automations-connectors'),
                'type'        => 'textarea',
                'class'       => 'bwfan_telegram_default_message',
                'placeholder' => __('Enter default message', 'autonami-automations-connectors'),
                'required'    => false,
            ),
        );
    }
    /**
     * Retrieves the saved settings fields values for the current connector.
     *
     * @return array An array containing the saved settings fields values.
     */
    public function get_settings_fields_values() {
        $saved_data = WFCO_Common::$connectors_saved_data;
        $old_data   = isset($saved_data[$this->get_slug()]) ? $saved_data[$this->get_slug()] : array();
        
        return array(
            'bot_token' => isset($old_data['bot_token']) ? $old_data['bot_token'] : '',
        );
    }

    /**
     * Retrieves API data based on the provided bot token.
     *
     * @param array $posted_data An array containing the bot token.
     * @return array An array containing the API data or an error message.
     */
    protected function get_api_data($posted_data) {
        $bot_token = isset($posted_data['bot_token']) ? $posted_data['bot_token'] : '';

        $call_class = new WFCO_TELEGRAM_Call();
        $call_class->set_data(array(
            'bot_token' => $bot_token,
            'method'    => 'getMe',
        ));

        $response = $call_class->process();

        if (isset($response['ok']) && $response['ok'] === true) {
            return array(
                'status'   => 'success',
                'api_data' => array(
                    'bot_token' => $bot_token,
                ),
            );
        } else {
            return array(
                'status'  => 'failed',
                'message' => isset($response['description']) ? $response['description'] : __('Failed to connect to Telegram API', 'autonami-automations-connectors'),
            );
        }
    }

    /**
     * Adds the Telegram connector to the list of available connectors.
     *
     * @param array $available_connectors The list of available connectors.
     * @return array The updated list of available connectors with the Telegram connector added.
     */
    public function add_card($available_connectors) {
        $available_connectors['autonami']['connectors']['bwfco_telegram'] = array(
            'name'            => 'Telegram',
            'desc'            => __('Send messages via Telegram Bot', 'autonami-automations-connectors'),
            'connector_class' => 'BWFCO_TELEGRAM',
            'image'           => $this->get_image(),
            'source'          => '',
            'file'            => '',
        );

        return $available_connectors;
    }
}

WFCO_Load_Connectors::register('BWFCO_TELEGRAM');