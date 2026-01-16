<?php
/**
 * Diagnóstico CDC Sistema
 * Ejecutar visitando: http://localhost:10013/wp-content/themes/cdc-sistema/diagnostico.php
 */

// Load WordPress
require_once('../../../wp-load.php');

echo '<html><head><title>Diagnóstico CDC Sistema</title>';
echo '<style>
body { font-family: monospace; padding: 20px; background: #f5f5f5; }
.ok { color: green; font-weight: bold; }
.error { color: red; font-weight: bold; }
.warning { color: orange; font-weight: bold; }
h2 { background: #333; color: white; padding: 10px; margin-top: 20px; }
pre { background: white; padding: 10px; border: 1px solid #ddd; overflow: auto; }
ul { background: white; padding: 20px; border: 1px solid #ddd; }
</style></head><body>';

echo '<h1>🔍 Diagnóstico CDC Sistema</h1>';

// 1. Check active theme
echo '<h2>1. Tema Activo</h2>';
$current_theme = wp_get_theme();
echo '<ul>';
echo '<li>Tema: <strong>' . $current_theme->get('Name') . '</strong></li>';
echo '<li>Directorio: <strong>' . $current_theme->get_stylesheet() . '</strong></li>';
echo '<li>Versión: <strong>' . $current_theme->get('Version') . '</strong></li>';

if ($current_theme->get_stylesheet() === 'cdc-sistema') {
    echo '<li class="ok">✓ Tema CDC Sistema está activo</li>';
} else {
    echo '<li class="error">✗ ERROR: Tema activo no es cdc-sistema</li>';
}
echo '</ul>';

// 2. Check plugins
echo '<h2>2. Plugins Activos</h2>';
$active_plugins = get_option('active_plugins');
echo '<ul>';
$maintenance_plugins = array('wp-maintenance-mode', 'coming-soon', 'under-construction', 'maintenance');
$found_maintenance = false;

foreach ($active_plugins as $plugin) {
    echo '<li>' . $plugin;

    // Check for maintenance plugins
    foreach ($maintenance_plugins as $maint) {
        if (stripos($plugin, $maint) !== false) {
            echo ' <span class="error">⚠️ PLUGIN DE MANTENIMIENTO DETECTADO</span>';
            $found_maintenance = true;
        }
    }

    // Highlight cdc-api
    if (stripos($plugin, 'cdc-api') !== false) {
        echo ' <span class="ok">✓ CDC API</span>';
    }

    echo '</li>';
}

if ($found_maintenance) {
    echo '<li class="error">✗ HAY PLUGINS DE MANTENIMIENTO ACTIVOS - DESACTÍVALOS</li>';
} else {
    echo '<li class="ok">✓ No hay plugins de mantenimiento detectados</li>';
}
echo '</ul>';

// 3. Check CDC pages
echo '<h2>3. Páginas CDC</h2>';
$page_slugs = array('login', 'personas', 'cobrar', 'registrar-gasto', 'talleres', 'eventos', 'alquiler-salas', 'salas', 'caja');
echo '<ul>';

$pages_created = get_option('cdc_pages_created');
echo '<li>Flag cdc_pages_created: ' . ($pages_created ? '<span class="ok">true</span>' : '<span class="error">false</span>') . '</li>';

foreach ($page_slugs as $slug) {
    $page = get_page_by_path($slug);
    if ($page) {
        $template = get_post_meta($page->ID, '_wp_page_template', true);
        echo '<li class="ok">✓ ' . $slug . ' (ID: ' . $page->ID . ', Template: ' . $template . ')</li>';
    } else {
        echo '<li class="error">✗ ' . $slug . ' NO EXISTE</li>';
    }
}
echo '</ul>';

// 4. Check front page settings
echo '<h2>4. Configuración de Página de Inicio</h2>';
echo '<ul>';
echo '<li>show_on_front: <strong>' . get_option('show_on_front') . '</strong></li>';
echo '<li>page_on_front: <strong>' . get_option('page_on_front') . '</strong></li>';
echo '<li>page_for_posts: <strong>' . get_option('page_for_posts') . '</strong></li>';
echo '</ul>';

// 5. Check template files
echo '<h2>5. Templates del Tema</h2>';
$theme_dir = get_template_directory();
$templates = array(
    'front-page.php',
    'page-login.php',
    'page-personas.php',
    'header.php',
    'footer.php',
    'sidebar.php',
    'functions.php'
);

echo '<ul>';
foreach ($templates as $template) {
    $file = $theme_dir . '/' . $template;
    if (file_exists($file)) {
        echo '<li class="ok">✓ ' . $template . '</li>';
    } else {
        echo '<li class="error">✗ ' . $template . ' NO EXISTE</li>';
    }
}
echo '</ul>';

// 6. Check if user is logged in
echo '<h2>6. Estado de Sesión</h2>';
echo '<ul>';
if (is_user_logged_in()) {
    $current_user = wp_get_current_user();
    echo '<li class="ok">✓ Estás logueado como: ' . $current_user->user_login . '</li>';
    echo '<li>Roles: ' . implode(', ', $current_user->roles) . '</li>';
} else {
    echo '<li class="warning">⚠ No estás logueado</li>';
}
echo '</ul>';

// 7. Check for errors
echo '<h2>7. Errores PHP Recientes</h2>';
$log_file = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/logs/php/error.log';
if (file_exists($log_file)) {
    $log_content = file_get_contents($log_file);
    $lines = explode("\n", $log_content);
    $recent_lines = array_slice($lines, -20);
    echo '<pre>' . esc_html(implode("\n", $recent_lines)) . '</pre>';
} else {
    echo '<p>No se encontró log de errores</p>';
}

// 8. Template hierarchy test
echo '<h2>8. Test de Template</h2>';
echo '<ul>';
echo '<li>Intentando cargar front-page.php directamente...</li>';

// Simulate what WordPress does
global $wp_query;
if ($wp_query) {
    echo '<li>is_front_page: ' . (is_front_page() ? 'true' : 'false') . '</li>';
    echo '<li>is_home: ' . (is_home() ? 'true' : 'false') . '</li>';
    echo '<li>is_page: ' . (is_page() ? 'true' : 'false') . '</li>';
}
echo '</ul>';

// 9. Recommendations
echo '<h2>9. 🎯 Recomendaciones</h2>';
echo '<ul>';

if ($found_maintenance) {
    echo '<li class="error"><strong>ACCIÓN REQUERIDA:</strong> Desactiva todos los plugins de mantenimiento/coming soon</li>';
    echo '<li>Ve a: <a href="' . admin_url('plugins.php') . '">' . admin_url('plugins.php') . '</a></li>';
}

if (!$pages_created) {
    echo '<li class="warning"><strong>Ejecutar:</strong> <a href="' . get_template_directory_uri() . '/create-pages.php">create-pages.php</a></li>';
}

if (is_user_logged_in()) {
    echo '<li><strong>Probar dashboard:</strong> <a href="' . home_url('/') . '">' . home_url('/') . '</a></li>';
    echo '<li><strong>Hacer logout y probar:</strong> <a href="' . wp_logout_url(home_url('/login')) . '">Logout y ver login</a></li>';
} else {
    echo '<li><strong>Probar login:</strong> <a href="' . home_url('/login') . '">' . home_url('/login') . '</a></li>';
}

echo '</ul>';

echo '<hr>';
echo '<p><a href="' . admin_url() . '">← Volver al Admin</a></p>';

echo '</body></html>';
