<?php
/**
 * Plugin Name: Autonami Marketing Automations Connectors - telegram
 * Plugin URI: https://my.mamatov.club
 * Description: telegram integration for Autonami Marketing Automations
 * Version: 1.2.4
 * Author: Evgenii Rezanov, Claude.ai
 * Author URI: https://eredmonkey.link
 * Text Domain: woofunnels-telegram-connector
 *

 * Requires at least: 5.0
 * Tested up to: 6.1
 */


if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class WFCO_Telegram {

    public static $_instance = null;

    /**
     * Constructs a new instance of the WFCO_Telegram class.
     *
     * This method is responsible for defining the plugin properties and loading the common functionality.
     */
    private function __construct() {
        $this->define_plugin_properties();
        $this->load_commons();
    }

    public function define_plugin_properties() {
        define( 'WFCO_TELEGRAM_VERSION', '1.2.4' );
        define( 'WFCO_TELEGRAM_FULL_NAME', 'Autonami Marketing Automations Connectors : Telegram' );
        define( 'WFCO_TELEGRAM_PLUGIN_FILE', __FILE__ );
        define( 'WFCO_TELEGRAM_PLUGIN_DIR', __DIR__ );
        define( 'WFCO_TELEGRAM_PLUGIN_URL', untrailingslashit( plugin_dir_url( WFCO_TELEGRAM_PLUGIN_FILE ) ) );
        define( 'WFCO_TELEGRAM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
        define( 'WFCO_TELEGRAM_MAIN', 'autonami-automations-connectors' );
    }

    public function load_commons() {
        add_action( 'wfco_load_connectors', [ $this, 'load_connector_classes' ] );
        add_action( 'bwfan_automations_loaded', [ $this, 'load_autonami_classes' ] );
        add_action( 'bwfan_loaded', [ $this, 'init_telegram' ] );
    }

    /**
     * Gets the instance of the class.
     *
     * @return WFCO_Telegram
     */
    public static function get_instance() {
        if ( null === self::$_instance ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function init_telegram() {
        require WFCO_TELEGRAM_PLUGIN_DIR . '/includes/class-wfco-telegram-common.php';
        require WFCO_TELEGRAM_PLUGIN_DIR . '/includes/class-wfco-telegram-call.php';
    }

    public function load_connector_classes() {
        require_once( WFCO_TELEGRAM_PLUGIN_DIR . '/includes/class-wfco-telegram-common.php' );
        require_once( WFCO_TELEGRAM_PLUGIN_DIR . '/includes/class-wfco-telegram-call.php' );
        require_once( WFCO_TELEGRAM_PLUGIN_DIR . '/connector.php' );

        do_action( 'wfco_telegram_connector_loaded', $this );
    }

    /**
     * Load all the integrations for Autonami.
     *
     * @action wfco_telegram_integrations_loaded
     *
     * @since 1.1.0
     */
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
