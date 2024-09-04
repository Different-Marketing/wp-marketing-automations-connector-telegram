<?php
/**
 * Plugin Name: Autonami Marketing Automations Connectors - telegram
 * Plugin URI: https://my.mamatov.club
 * Description: telegram integration for Autonami Marketing Automations
 * Version: 1.1.0
 * Author: Evgenii Rezanov, Mikhail Kuznetsov
 * Author URI: https://my.mamatov.club
 * Text Domain: woofunnels-telegram-connector
 *
 * @package WooFunnels
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly

final class WFCO_TELEGRAM {

    public static $_instance = null;

    private function __construct() {
        // Загрузка важных переменных и констант
        $this->define_plugin_properties();

        // Загрузка общих файлов
        $this->load_commons();
    }

    // Определение констант
    public function define_plugin_properties() {
        define( 'WFCO_TELEGRAM_CONNECTOR_VERSION', '1.1.0' );
        define( 'WFCO_TELEGRAM_FULL_NAME', 'Autonami Marketing Automations Connectors : Telegram' );
        define( 'WFCO_TELEGRAM_CONNECTOR_FILE', __FILE__ );
        define( 'WFCO_TELEGRAM_CONNECTOR_DIR', __DIR__ );
        define( 'WFCO_TELEGRAM_CONNECTOR_URL', untrailingslashit( plugin_dir_url( WFCO_TELEGRAM_CONNECTOR_FILE ) ) );
        define( 'WFCO_TELEGRAM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
        define( 'WFCO_TELEGRAM_MAIN', 'autonami-automations-connectors' );
    }

    // Загрузка общих хуков
    public function load_commons() {
        add_action( 'wfco_load_connectors', [ $this, 'load_connector_classes' ] );
        add_action( 'bwfan_automations_loaded', [ $this, 'load_autonami_classes' ] );
        add_action( 'bwfan_loaded', [ $this, 'init_telegram' ] );
    }

    public static function get_instance() {
        if ( null === self::$_instance ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Initialization of the connector.
     *
     * Includes the main connector class and the class of the action.
     *
     * @since 2.0.0
     */
    public function init_telegram() {
        require WFCO_TELEGRAM_CONNECTOR_DIR . '/includes/class-wfco-telegram-common.php';
        require WFCO_TELEGRAM_CONNECTOR_DIR . '/includes/class-wfco-telegram-call.php';
    }

    // Загрузка классов коннектора
    public function load_connector_classes() {
        require_once( WFCO_TELEGRAM_PLUGIN_DIR . '/includes/class-wfco-telegram-common.php' );
        require_once( WFCO_TELEGRAM_PLUGIN_DIR . '/includes/class-wfco-telegram-call.php' );
        require_once( WFCO_TELAGRAM_PLUGIN_DIR . '/connector.php' );

        do_action( 'wfco_telegram_connector_loaded', $this );
    }

    // Загрузка классов интеграции Autonami
    public function load_autonami_classes() {
        $integration_dir = WFCO_TELEGRAM_PLUGIN_DIR . '/autonami';
        foreach ( glob( $integration_dir . '/class-*.php' ) as $_field_filename ) {
            require_once( $_field_filename );
        }
        do_action( 'wfco_telegram_integrations_loaded', $this );
    }
}

if ( ! function_exists( 'WFCO_Telegram_Core' ) ) {
    function WFCO_Telegram_Core() {
        return WFCO_Telegram::get_instance();
    }
}

WFCO_Telegram_Core();
