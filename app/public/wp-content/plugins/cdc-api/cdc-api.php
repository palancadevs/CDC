<?php
/**
 * Plugin Name: CDC API
 * Plugin URI: https://github.com/palancadevs/CDC
 * Description: REST API y gestión de datos para Casa de la Cultura. Maneja base de datos, modelos, servicios y endpoints REST para el tema CDC Sistema.
 * Version: 1.0.0
 * Author: CDC Development Team
 * Author URI: https://github.com/palancadevs
 * Text Domain: cdc-api
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 *
 * @package CDC_API
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Plugin constants
define('CDC_API_VERSION', '1.0.0');
define('CDC_API_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CDC_API_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CDC_API_PLUGIN_FILE', __FILE__);

/**
 * Main CDC_API Class
 */
final class CDC_API {
    /**
     * The single instance of the class
     */
    private static $instance = null;

    /**
     * Main CDC_API Instance
     */
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
        $this->includes();
    }

    /**
     * Hook into actions and filters
     */
    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        add_action('init', array($this, 'init'), 0);
        add_action('rest_api_init', array($this, 'register_rest_routes'));
    }

    /**
     * Include required core files
     */
    private function includes() {
        // Database
        require_once CDC_API_PLUGIN_DIR . 'includes/database/schema.php';

        // Base Model
        require_once CDC_API_PLUGIN_DIR . 'includes/models/class-base-model.php';

        // Models
        require_once CDC_API_PLUGIN_DIR . 'includes/models/class-persona.php';
        require_once CDC_API_PLUGIN_DIR . 'includes/models/class-socio.php';
        require_once CDC_API_PLUGIN_DIR . 'includes/models/class-cliente.php';
        require_once CDC_API_PLUGIN_DIR . 'includes/models/class-recibo.php';
        require_once CDC_API_PLUGIN_DIR . 'includes/models/class-movimiento-caja.php';
        require_once CDC_API_PLUGIN_DIR . 'includes/models/class-gasto.php';
        require_once CDC_API_PLUGIN_DIR . 'includes/models/class-taller.php';
        require_once CDC_API_PLUGIN_DIR . 'includes/models/class-evento.php';
        require_once CDC_API_PLUGIN_DIR . 'includes/models/class-sala.php';
        require_once CDC_API_PLUGIN_DIR . 'includes/models/class-reserva-sala.php';

        // Services
        require_once CDC_API_PLUGIN_DIR . 'includes/services/class-persona-service.php';
        require_once CDC_API_PLUGIN_DIR . 'includes/services/class-recibo-service.php';
        require_once CDC_API_PLUGIN_DIR . 'includes/services/class-caja-service.php';
        require_once CDC_API_PLUGIN_DIR . 'includes/services/class-taller-service.php';
        require_once CDC_API_PLUGIN_DIR . 'includes/services/class-evento-service.php';
        require_once CDC_API_PLUGIN_DIR . 'includes/services/class-sala-service.php';

        // Base REST Controller
        require_once CDC_API_PLUGIN_DIR . 'includes/rest/class-base-controller.php';

        // REST API Controllers
        require_once CDC_API_PLUGIN_DIR . 'includes/rest/class-personas-controller.php';
        require_once CDC_API_PLUGIN_DIR . 'includes/rest/class-recibos-controller.php';
        require_once CDC_API_PLUGIN_DIR . 'includes/rest/class-caja-controller.php';
        require_once CDC_API_PLUGIN_DIR . 'includes/rest/class-talleres-controller.php';
        require_once CDC_API_PLUGIN_DIR . 'includes/rest/class-eventos-controller.php';
        require_once CDC_API_PLUGIN_DIR . 'includes/rest/class-salas-controller.php';
    }

    /**
     * Init CDC API when WordPress initializes
     */
    public function init() {
        // Set up localization
        load_plugin_textdomain('cdc-api', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        $controllers = array(
            new CDC_Personas_Controller(),
            new CDC_Recibos_Controller(),
            new CDC_Caja_Controller(),
            new CDC_Talleres_Controller(),
            new CDC_Eventos_Controller(),
            new CDC_Salas_Controller(),
        );

        foreach ($controllers as $controller) {
            $controller->register_routes();
        }
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Create database tables
        CDC_Database_Schema::create_tables();

        // Set plugin version
        update_option('cdc_api_version', CDC_API_VERSION);

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Get the plugin url
     */
    public function plugin_url() {
        return untrailingslashit(plugins_url('/', __FILE__));
    }

    /**
     * Get the plugin path
     */
    public function plugin_path() {
        return untrailingslashit(plugin_dir_path(__FILE__));
    }
}

/**
 * Returns the main instance of CDC_API
 */
function CDC_API() {
    return CDC_API::instance();
}

// Initialize the plugin
CDC_API();
