<?php
/**
 * CDC Sistema Theme Functions
 *
 * @package CDC_Sistema
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define theme constants
define('CDC_THEME_VERSION', '1.0.0');
define('CDC_THEME_DIR', get_template_directory());
define('CDC_THEME_URL', get_template_directory_uri());

// Require includes
require_once CDC_THEME_DIR . '/includes/enqueue.php';
require_once CDC_THEME_DIR . '/includes/auth.php';
require_once CDC_THEME_DIR . '/includes/session-guard.php';

/**
 * Theme setup
 */
function cdc_sistema_setup() {
    // Add theme support
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ));

    // Register nav menus
    register_nav_menus(array(
        'primary' => __('Menú Principal', 'cdc-sistema'),
    ));
}
add_action('after_setup_theme', 'cdc_sistema_setup');

/**
 * Create required pages on theme activation
 */
function cdc_create_required_pages() {
    // Check if pages already created
    if (get_option('cdc_pages_created')) {
        return;
    }

    $pages = array(
        array(
            'post_title'   => 'Login',
            'post_name'    => 'login',
            'post_content' => '',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'page_template' => 'page-login.php'
        ),
        array(
            'post_title'   => 'Personas',
            'post_name'    => 'personas',
            'post_content' => '',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'page_template' => 'page-personas.php'
        ),
        array(
            'post_title'   => 'Cobrar',
            'post_name'    => 'cobrar',
            'post_content' => '',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'page_template' => 'page-cobrar.php'
        ),
        array(
            'post_title'   => 'Registrar Gasto',
            'post_name'    => 'registrar-gasto',
            'post_content' => '',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'page_template' => 'page-registrar-gasto.php'
        ),
        array(
            'post_title'   => 'Talleres',
            'post_name'    => 'talleres',
            'post_content' => '',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'page_template' => 'page-talleres.php'
        ),
        array(
            'post_title'   => 'Eventos',
            'post_name'    => 'eventos',
            'post_content' => '',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'page_template' => 'page-eventos.php'
        ),
        array(
            'post_title'   => 'Alquiler de Salas',
            'post_name'    => 'alquiler-salas',
            'post_content' => '',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'page_template' => 'page-alquiler-salas.php'
        ),
        array(
            'post_title'   => 'Salas',
            'post_name'    => 'salas',
            'post_content' => '',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'page_template' => 'page-salas.php'
        ),
        array(
            'post_title'   => 'Caja',
            'post_name'    => 'caja',
            'post_content' => '',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'page_template' => 'page-caja.php'
        ),
        array(
            'post_title'   => 'Nuevo Socio',
            'post_name'    => 'nuevo-socio',
            'post_content' => '',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'page_template' => 'page-nuevo-socio.php'
        ),
        array(
            'post_title'   => 'Nuevo Cliente',
            'post_name'    => 'nuevo-cliente',
            'post_content' => '',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'page_template' => 'page-nuevo-cliente.php'
        ),
        array(
            'post_title'   => 'Nuevo Taller',
            'post_name'    => 'nuevo-taller',
            'post_content' => '',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'page_template' => 'page-nuevo-taller.php'
        ),
        array(
            'post_title'   => 'Diagnóstico del Sistema',
            'post_name'    => 'diagnostico',
            'post_content' => '',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'page_template' => 'page-diagnostico.php'
        ),
        array(
            'post_title'   => 'Instalar Tablas',
            'post_name'    => 'instalar-tablas',
            'post_content' => '',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'page_template' => 'page-instalar-tablas.php'
        ),
        array(
            'post_title'   => 'Tests del Sistema',
            'post_name'    => 'tests',
            'post_content' => '',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'page_template' => 'page-tests.php'
        ),
        array(
            'post_title'   => 'Tests V2',
            'post_name'    => 'tests-v2',
            'post_content' => '',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'page_template' => 'page-tests-v2.php'
        ),
    );

    foreach ($pages as $page) {
        // Check if page already exists
        $existing = get_page_by_path($page['post_name']);

        if (!$existing) {
            $page_id = wp_insert_post($page);

            // Set page template
            if ($page_id && !is_wp_error($page_id)) {
                update_post_meta($page_id, '_wp_page_template', $page['page_template']);
            }
        }
    }

    // Mark pages as created
    update_option('cdc_pages_created', true);
}
add_action('after_switch_theme', 'cdc_create_required_pages');
