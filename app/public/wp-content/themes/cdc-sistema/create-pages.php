<?php
/**
 * Force create CDC pages
 *
 * Run this file by visiting: http://localhost:10013/wp-content/themes/cdc-sistema/create-pages.php
 */

// Load WordPress
require_once('../../../wp-load.php');

// Must be logged in as admin
if (!current_user_can('manage_options')) {
    die('ERROR: Debes estar logueado como administrador para ejecutar este script.');
}

echo '<h1>CDC Sistema - Crear Páginas</h1>';

// Reset the flag to allow recreation
delete_option('cdc_pages_created');
echo '<p>✓ Flag cdc_pages_created eliminado</p>';

// Call the function directly
if (function_exists('cdc_create_required_pages')) {
    cdc_create_required_pages();
    echo '<p>✓ Función cdc_create_required_pages() ejecutada</p>';
} else {
    echo '<p>✗ ERROR: Función cdc_create_required_pages() no existe</p>';
}

// Check what was created
echo '<h2>Páginas CDC Creadas:</h2>';
echo '<ul>';

$page_slugs = array('login', 'personas', 'cobrar', 'registrar-gasto', 'talleres', 'eventos', 'alquiler-salas', 'salas', 'caja');

foreach ($page_slugs as $slug) {
    $page = get_page_by_path($slug);
    if ($page) {
        $template = get_post_meta($page->ID, '_wp_page_template', true);
        echo '<li>✓ <strong>' . $slug . '</strong> - ID: ' . $page->ID . ' - Template: ' . $template . '</li>';
    } else {
        echo '<li>✗ <strong>' . $slug . '</strong> - NO EXISTE</li>';
    }
}

echo '</ul>';

echo '<h2>Siguiente Paso:</h2>';
echo '<p>Ahora intenta acceder a: <a href="' . home_url('/login') . '">' . home_url('/login') . '</a></p>';

echo '<p><a href="' . admin_url() . '">← Volver al Admin</a></p>';
