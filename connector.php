<?php
defined( 'ABSPATH' ) || exit; // Exit if accessed directly

class BWFCO_TELEGRAM extends BWF_CO {

    public static $instance = null;
    public $v2 = true;

    /**
     * Constructor.
     *
     * @since 2.0.0
     */
    public function __construct() {
        $this->connector_url     = WFCO_TELEGRAM_PLUGIN_URL;
        $this->dir               = __DIR__;
        $this->nice_name         = __('Telegram', 'autonami-automations-connectors');
        $this->autonami_int_slug = 'BWFAN_Telegram_Integration';

        $this->keys_to_track = array(
            'login',
            'password',
        );
        $this->form_req_keys = array(
            'login',
            'password',
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
     * Returns an array of field schema for the SMSC.ru connector.
     *
     * The schema includes fields for login and password, each with their respective
     * labels, types, classes, and placeholders. Both fields are required.
     *
     * @return array An array of field schema.
     */
    public function get_fields_schema() {
        return array(
            array(
                'id'          => 'login',
                'label'       => __('Login', 'autonami-automations-connectors'),
                'type'        => 'text',
                'class'       => 'bwfan_telegram_login',
                'placeholder' => __('Enter your Telegram login', 'autonami-automations-connectors'),
                'required'    => true,
            ),
            array(
                'id'          => 'password',
                'label'       => __('Password', 'autonami-automations-connectors'),
                'type'        => 'password',
                'class'       => 'bwfan_telegram_password',
                'placeholder' => __('Enter your Telegram password', 'autonami-automations-connectors'),
                'required'    => true,
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
            'login'    => isset($old_data['login']) ? $old_data['login'] : '',
            'password' => isset($old_data['password']) ? $old_data['password'] : '',
        );
    }

    /**
     * Retrieves API data based on the provided login and password.
     *
     * @param array $posted_data An array containing the login and password.
     * @return array An array containing the API data or an error message.
     */
    protected function get_api_data($posted_data) {
        $login    = isset($posted_data['login']) ? $posted_data['login'] : '';
        $password = isset($posted_data['password']) ? $posted_data['password'] : '';

        WFCO_TELEGRAM_Common::set_headers($login, $password);
        // TODO: Test phone number
        $call_class = new WFCO_TELEGRAM_Call();
        $call_class->set_data(array(
            'phone'   => '79119387283', 
            'message' => 'Test message',
        ));

        $response = $call_class->process();

        if ($response['status'] === 'success') {
            return array(
                'status'   => 'success',
                'api_data' => array(
                    'login'    => $login,
                    'password' => $password,
                ),
            );
        } else {
            return array(
                'status'  => 'failed',
                'message' => $response['message'],
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
            'desc'            => __('Send message via Telegram', 'autonami-automations-connectors'),
            'connector_class' => 'BWFCO_TELEGRAM',
            'image'           => $this->get_image(),
            'source'          => '',
            'file'            => '',
        );

        return $available_connectors;
    }
   
}

WFCO_Load_Connectors::register('BWFCO_TELEGRAM');