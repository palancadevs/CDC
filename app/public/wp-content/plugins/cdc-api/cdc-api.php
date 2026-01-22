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
        require_once CDC_API_PLUGIN_DIR . 'includes/models/class-cuota-socio.php';
        require_once CDC_API_PLUGIN_DIR . 'includes/models/class-cliente.php';
        require_once CDC_API_PLUGIN_DIR . 'includes/models/class-recibo.php';
        require_once CDC_API_PLUGIN_DIR . 'includes/models/class-movimiento-caja.php';
        require_once CDC_API_PLUGIN_DIR . 'includes/models/class-gasto.php';
        require_once CDC_API_PLUGIN_DIR . 'includes/models/class-taller.php';
        require_once CDC_API_PLUGIN_DIR . 'includes/models/class-inscripcion-taller.php';
        require_once CDC_API_PLUGIN_DIR . 'includes/models/class-cuota-taller.php';
        require_once CDC_API_PLUGIN_DIR . 'includes/models/class-evento.php';
        require_once CDC_API_PLUGIN_DIR . 'includes/models/class-sala.php';
        require_once CDC_API_PLUGIN_DIR . 'includes/models/class-reserva-sala.php';

        // Services
        require_once CDC_API_PLUGIN_DIR . 'includes/services/class-persona-service.php';
        require_once CDC_API_PLUGIN_DIR . 'includes/services/class-recibo-service.php';
        require_once CDC_API_PLUGIN_DIR . 'includes/services/class-caja-service.php';
        require_once CDC_API_PLUGIN_DIR . 'includes/services/class-taller-service.php';
        require_once CDC_API_PLUGIN_DIR . 'includes/services/class-inscripcion-service.php';
        require_once CDC_API_PLUGIN_DIR . 'includes/services/class-evento-service.php';
        require_once CDC_API_PLUGIN_DIR . 'includes/services/class-sala-service.php';
        require_once CDC_API_PLUGIN_DIR . 'includes/services/class-woocommerce-service.php';
        require_once CDC_API_PLUGIN_DIR . 'includes/services/class-arca-service.php';

        // Base REST Controller
        require_once CDC_API_PLUGIN_DIR . 'includes/rest/class-base-controller.php';

        // REST API Controllers
        require_once CDC_API_PLUGIN_DIR . 'includes/rest/class-personas-controller.php';
        require_once CDC_API_PLUGIN_DIR . 'includes/rest/class-recibos-controller.php';
        require_once CDC_API_PLUGIN_DIR . 'includes/rest/class-caja-controller.php';
        require_once CDC_API_PLUGIN_DIR . 'includes/rest/class-cobros-controller.php';
        require_once CDC_API_PLUGIN_DIR . 'includes/rest/class-talleres-controller.php';
        require_once CDC_API_PLUGIN_DIR . 'includes/rest/class-eventos-controller.php';
        require_once CDC_API_PLUGIN_DIR . 'includes/rest/class-salas-controller.php';
        require_once CDC_API_PLUGIN_DIR . 'includes/rest/class-arca-controller.php';
    }

    /**
     * Init CDC API when WordPress initializes
     */
    public function init() {
        // Set up localization
        load_plugin_textdomain('cdc-api', false, dirname(plugin_basename(__FILE__)) . '/languages');

        // Initialize ARCA service to register WooCommerce hooks
        if (class_exists('WooCommerce')) {
            new CDC_ARCA_Service();
        }
    }

    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        $controllers = array(
            new CDC_Personas_Controller(),
            new CDC_Recibos_Controller(),
            new CDC_Caja_Controller(),
            new CDC_Cobros_Controller(),
            new CDC_Talleres_Controller(),
            new CDC_Eventos_Controller(),
            new CDC_Salas_Controller(),
            new CDC_ARCA_Controller(),
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

        // Sync WooCommerce products if WooCommerce is active
        if (class_exists('WooCommerce')) {
            $this->sync_woocommerce_products();
        }

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Sync WooCommerce products for CDC services
     */
    private function sync_woocommerce_products() {
        $wc_service = new CDC_WooCommerce_Service();

        // Create Cuota Socio product
        $cuota_socio_product = $wc_service->get_or_create_cuota_socio_product();

        if ($cuota_socio_product) {
            error_log('CDC: Producto "Cuota Socio" creado/sincronizado - ID: ' . $cuota_socio_product);
        }

        // Sync all talleres as products
        $taller_model = new CDC_Taller();
        $talleres = $taller_model->get_all();

        $synced = 0;
        foreach ($talleres as $taller) {
            $taller_data = array(
                'nombre' => $taller['nombre'],
                'precio_mensual' => $taller['precio_mensual'],
                'descripcion' => isset($taller['descripcion']) ? $taller['descripcion'] : '',
            );

            $product_id = $wc_service->sync_taller_product($taller['id'], $taller_data);

            if ($product_id) {
                $synced++;
            }
        }

        if ($synced > 0) {
            error_log("CDC: $synced talleres sincronizados como productos WooCommerce");
        }

        // Sync all salas as products
        $sala_model = new CDC_Sala();
        $salas = $sala_model->get_all();

        $synced_salas = 0;
        foreach ($salas as $sala) {
            $sala_data = array(
                'nombre' => $sala['nombre'],
                'precio_hora' => $sala['precio_hora'],
                'descripcion' => isset($sala['descripcion']) ? $sala['descripcion'] : '',
            );

            $product_id = $wc_service->sync_sala_product($sala['id'], $sala_data);

            if ($product_id) {
                $synced_salas++;
            }
        }

        if ($synced_salas > 0) {
            error_log("CDC: $synced_salas salas sincronizadas como productos WooCommerce");
        }
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
