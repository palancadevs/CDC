<?php
/**
 * Plugin Name: CDC Admin - Sistema de Gestión
 * Plugin URI: https://github.com/palancadevs/CDC
 * Description: Sistema de administración integral para Casa de la Cultura: gestión de socios, talleres, eventos, alquiler de salas, caja y facturación automática.
 * Version: 1.0.0
 * Author: Palanca Devs
 * Author URI: https://palancadevs.com
 * Text Domain: cdc-admin
 * Domain Path: /languages
 * Requires at least: 6.8
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('CDC_VERSION', '1.0.0');
define('CDC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CDC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CDC_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Require WooCommerce check
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    add_action('admin_notices', 'cdc_woocommerce_required_notice');
    return;
}

/**
 * Display notice if WooCommerce is not active
 */
function cdc_woocommerce_required_notice() {
    ?>
    <div class="notice notice-error">
        <p><?php _e('CDC Admin requiere que WooCommerce esté instalado y activado.', 'cdc-admin'); ?></p>
    </div>
    <?php
}

/**
 * Main CDC Admin Class
 */
class CDC_Admin {

    /**
     * Single instance of the class
     */
    private static $instance = null;

    /**
     * Get single instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
        $this->load_dependencies();
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        add_action('plugins_loaded', array($this, 'init'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        // Database
        require_once CDC_PLUGIN_DIR . 'includes/database/schema.php';

        // Helpers
        require_once CDC_PLUGIN_DIR . 'includes/helpers/permissions.php';
        require_once CDC_PLUGIN_DIR . 'includes/helpers/formatters.php';
        require_once CDC_PLUGIN_DIR . 'includes/helpers/validators.php';

        // Services
        require_once CDC_PLUGIN_DIR . 'includes/services/PersonasService.php';
        require_once CDC_PLUGIN_DIR . 'includes/services/CajaService.php';
        require_once CDC_PLUGIN_DIR . 'includes/services/CobrosService.php';
        require_once CDC_PLUGIN_DIR . 'includes/services/FacturacionService.php';
        require_once CDC_PLUGIN_DIR . 'includes/services/MercadoPagoService.php';

        // Models
        require_once CDC_PLUGIN_DIR . 'includes/models/Persona.php';
        require_once CDC_PLUGIN_DIR . 'includes/models/CuotaSocio.php';
        require_once CDC_PLUGIN_DIR . 'includes/models/MovimientoCaja.php';

        // REST API
        require_once CDC_PLUGIN_DIR . 'includes/rest-api/PersonasController.php';
        require_once CDC_PLUGIN_DIR . 'includes/rest-api/CajaController.php';
        require_once CDC_PLUGIN_DIR . 'includes/rest-api/CobrosController.php';

        // Admin Pages
        require_once CDC_PLUGIN_DIR . 'includes/admin-pages/menu.php';
        require_once CDC_PLUGIN_DIR . 'includes/admin-pages/inicio-page.php';
        // Additional pages will be loaded in future phases
        // require_once CDC_PLUGIN_DIR . 'includes/admin-pages/personas-page.php';
        // require_once CDC_PLUGIN_DIR . 'includes/admin-pages/cobrar-page.php';
        // require_once CDC_PLUGIN_DIR . 'includes/admin-pages/caja-page.php';
    }

    /**
     * Initialize plugin
     */
    public function init() {
        // Load text domain for translations
        load_plugin_textdomain('cdc-admin', false, dirname(CDC_PLUGIN_BASENAME) . '/languages');

        // Initialize REST API
        add_action('rest_api_init', array($this, 'register_rest_routes'));
    }

    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        $controllers = array(
            new CDC_Personas_Controller(),
            new CDC_Caja_Controller(),
            new CDC_Cobros_Controller(),
        );

        foreach ($controllers as $controller) {
            $controller->register_routes();
        }
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        // Only load on CDC admin pages
        if (strpos($hook, 'cdc-') === false) {
            return;
        }

        // Enqueue CSS
        wp_enqueue_style(
            'cdc-admin-style',
            CDC_PLUGIN_URL . 'assets/css/admin-style.css',
            array(),
            CDC_VERSION
        );

        // Enqueue JS
        wp_enqueue_script(
            'cdc-admin-scripts',
            CDC_PLUGIN_URL . 'assets/js/admin-scripts.js',
            array('jquery'),
            CDC_VERSION,
            true
        );

        // Localize script for AJAX
        wp_localize_script('cdc-admin-scripts', 'cdcAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'restUrl' => rest_url('cdc/v1/'),
            'nonce' => wp_create_nonce('wp_rest'),
            'i18n' => array(
                'loading' => __('Cargando...', 'cdc-admin'),
                'error' => __('Error al procesar la solicitud', 'cdc-admin'),
                'success' => __('Operación completada exitosamente', 'cdc-admin'),
                'confirm' => __('¿Está seguro?', 'cdc-admin'),
            ),
        ));
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Create database tables
        CDC_Database_Schema::create_tables();

        // Create custom roles
        $this->create_custom_roles();

        // Set default options
        $this->set_default_options();

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
     * Create custom roles
     */
    private function create_custom_roles() {
        // Get administrator capabilities as base
        $admin_role = get_role('administrator');

        // Create CDC Tesorería role
        add_role('cdc_tesoreria', __('CDC Tesorería', 'cdc-admin'), array(
            'read' => true,
            'cdc_manage_personas' => true,
            'cdc_manage_cobros' => true,
            'cdc_manage_caja' => true,
            'cdc_manage_talleres' => true,
            'cdc_manage_salas' => true,
            'cdc_view_reports' => true,
            'cdc_manage_catalogs' => true,
            'cdc_void_movements' => true,
            'cdc_retry_invoices' => true,
            'cdc_edit_amounts' => true,
        ));

        // Create CDC Recepción role
        add_role('cdc_recepcion', __('CDC Recepción', 'cdc-admin'), array(
            'read' => true,
            'cdc_manage_personas' => true,
            'cdc_manage_cobros' => true,
            'cdc_register_expenses' => true,
            'cdc_view_caja' => true,
        ));

        // Add CDC capabilities to administrator
        if ($admin_role) {
            $admin_role->add_cap('cdc_manage_personas');
            $admin_role->add_cap('cdc_manage_cobros');
            $admin_role->add_cap('cdc_manage_caja');
            $admin_role->add_cap('cdc_manage_talleres');
            $admin_role->add_cap('cdc_manage_salas');
            $admin_role->add_cap('cdc_view_reports');
            $admin_role->add_cap('cdc_manage_catalogs');
            $admin_role->add_cap('cdc_void_movements');
            $admin_role->add_cap('cdc_retry_invoices');
            $admin_role->add_cap('cdc_edit_amounts');
            $admin_role->add_cap('cdc_manage_users');
        }
    }

    /**
     * Set default plugin options
     */
    private function set_default_options() {
        $defaults = array(
            'cdc_invoice_on_status' => 'processing',
            'cdc_mp_mode' => 'assisted',
            'cdc_currency_symbol' => '$',
            'cdc_date_format' => 'd/m/Y',
            'cdc_time_format' => 'H:i',
        );

        foreach ($defaults as $key => $value) {
            if (get_option($key) === false) {
                add_option($key, $value);
            }
        }
    }
}

/**
 * Initialize the plugin
 */
function cdc_admin_init() {
    return CDC_Admin::get_instance();
}

// Start the plugin
cdc_admin_init();
