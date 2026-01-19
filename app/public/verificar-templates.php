<?php
/**
 * Verificar Templates y Páginas
 * Acceder vía: http://localhost:10013/verificar-templates.php
 */

define('WP_USE_THEMES', false);
require('./wp-load.php');

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Verificar Templates y Páginas</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 50px auto; padding: 20px; }
        .success { background: #d4edda; border: 2px solid #28a745; padding: 10px; border-radius: 5px; margin: 5px 0; }
        .error { background: #f8d7da; border: 2px solid #dc3545; padding: 10px; border-radius: 5px; margin: 5px 0; }
        .warning { background: #fff3cd; border: 2px solid #ffc107; padding: 10px; border-radius: 5px; margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: bold; }
        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin-top: 10px; }
    </style>
</head>
<body>
    <h1>🔍 Verificación de Templates y Páginas</h1>

    <h2>1. Verificar Templates en el Tema</h2>
    <?php
    $theme_dir = get_template_directory();
    $required_templates = array(
        'page-nuevo-socio.php',
        'page-nuevo-cliente.php',
        'page-nuevo-taller.php',
        'page-tests-v2.php',
        'page-personas.php',
        'page-cobrar.php',
        'page-registrar-gasto.php',
        'page-talleres.php',
        'page-caja.php',
        'page-diagnostico.php',
    );

    echo '<table>';
    echo '<tr><th>Template</th><th>Estado</th><th>Ruta</th></tr>';
    foreach ($required_templates as $template) {
        $template_path = $theme_dir . '/' . $template;
        $exists = file_exists($template_path);
        $status_class = $exists ? 'success' : 'error';
        $status_text = $exists ? '✅ Existe' : '❌ No existe';
        echo '<tr>';
        echo '<td><strong>' . $template . '</strong></td>';
        echo '<td class="' . $status_class . '">' . $status_text . '</td>';
        echo '<td><code>' . $template_path . '</code></td>';
        echo '</tr>';
    }
    echo '</table>';
    ?>

    <h2>2. Verificar Páginas en WordPress</h2>
    <?php
    $required_pages = array(
        'nuevo-socio' => 'page-nuevo-socio.php',
        'nuevo-cliente' => 'page-nuevo-cliente.php',
        'nuevo-taller' => 'page-nuevo-taller.php',
        'tests-v2' => 'page-tests-v2.php',
        'personas' => 'page-personas.php',
        'cobrar' => 'page-cobrar.php',
        'registrar-gasto' => 'page-registrar-gasto.php',
        'talleres' => 'page-talleres.php',
        'caja' => 'page-caja.php',
        'diagnostico' => 'page-diagnostico.php',
    );

    echo '<table>';
    echo '<tr><th>Página (slug)</th><th>Estado</th><th>Template Asignado</th><th>URL</th></tr>';

    $missing_pages = array();

    foreach ($required_pages as $slug => $expected_template) {
        $page = get_page_by_path($slug);

        if ($page) {
            $assigned_template = get_post_meta($page->ID, '_wp_page_template', true);
            $template_match = ($assigned_template === $expected_template);
            $status_class = $template_match ? 'success' : 'warning';
            $status_text = $template_match ? '✅ Existe y OK' : '⚠️ Existe pero template incorrecto';

            echo '<tr>';
            echo '<td><strong>' . $slug . '</strong></td>';
            echo '<td class="' . $status_class . '">' . $status_text . '</td>';
            echo '<td>' . ($assigned_template ?: 'default') . '</td>';
            echo '<td><a href="' . get_permalink($page->ID) . '" target="_blank">' . get_permalink($page->ID) . '</a></td>';
            echo '</tr>';

            // Fix template if incorrect
            if (!$template_match) {
                update_post_meta($page->ID, '_wp_page_template', $expected_template);
                echo '<tr><td colspan="4" class="warning">⚙️ Template corregido automáticamente</td></tr>';
            }
        } else {
            echo '<tr>';
            echo '<td><strong>' . $slug . '</strong></td>';
            echo '<td class="error">❌ No existe</td>';
            echo '<td>-</td>';
            echo '<td>-</td>';
            echo '</tr>';
            $missing_pages[] = array(
                'slug' => $slug,
                'template' => $expected_template
            );
        }
    }
    echo '</table>';
    ?>

    <?php if (count($missing_pages) > 0): ?>
        <h2>3. Crear Páginas Faltantes</h2>
        <?php
        foreach ($missing_pages as $page_data) {
            $slug = $page_data['slug'];
            $template = $page_data['template'];
            $title = ucwords(str_replace('-', ' ', $slug));

            $new_page = array(
                'post_title'   => $title,
                'post_name'    => $slug,
                'post_content' => '',
                'post_status'  => 'publish',
                'post_type'    => 'page',
            );

            $page_id = wp_insert_post($new_page);

            if ($page_id && !is_wp_error($page_id)) {
                update_post_meta($page_id, '_wp_page_template', $template);
                echo '<div class="success">✅ Página <strong>' . $title . '</strong> creada exitosamente (ID: ' . $page_id . ')</div>';
                echo '<p>Acceder a: <a href="' . home_url('/' . $slug) . '" target="_blank">' . home_url('/' . $slug) . '</a></p>';
            } else {
                $error_msg = is_wp_error($page_id) ? $page_id->get_error_message() : 'Error desconocido';
                echo '<div class="error">❌ Error al crear <strong>' . $title . '</strong>: ' . $error_msg . '</div>';
            }
        }
        ?>
    <?php else: ?>
        <div class="success">
            <h2>✅ Todas las páginas existen correctamente</h2>
        </div>
    <?php endif; ?>

    <h2>4. Verificar Scripts Enqueued</h2>
    <?php
    global $wp_scripts;

    // Trigger wp_enqueue_scripts
    do_action('wp_enqueue_scripts');

    $required_scripts = array('jquery', 'cdc-api', 'cdc-app');
    echo '<table>';
    echo '<tr><th>Script</th><th>Estado</th><th>Source</th></tr>';
    foreach ($required_scripts as $handle) {
        if (isset($wp_scripts->registered[$handle])) {
            $script = $wp_scripts->registered[$handle];
            echo '<tr>';
            echo '<td><strong>' . $handle . '</strong></td>';
            echo '<td class="success">✅ Registrado</td>';
            echo '<td><code>' . $script->src . '</code></td>';
            echo '</tr>';
        } else {
            echo '<tr>';
            echo '<td><strong>' . $handle . '</strong></td>';
            echo '<td class="error">❌ No registrado</td>';
            echo '<td>-</td>';
            echo '</tr>';
        }
    }
    echo '</table>';

    // Check if cdcData is localized
    if (isset($wp_scripts->registered['cdc-app'])) {
        echo '<h3>Datos localizados (cdcData)</h3>';
        if (isset($wp_scripts->registered['cdc-app']->extra['data'])) {
            echo '<div class="success">✅ cdcData está configurado</div>';
            echo '<pre>' . htmlspecialchars($wp_scripts->registered['cdc-app']->extra['data']) . '</pre>';
        } else {
            echo '<div class="error">❌ cdcData NO está configurado</div>';
        }
    }
    ?>

    <h2>Próximos pasos:</h2>
    <p>
        <a href="<?php echo home_url('/nuevo-socio'); ?>" class="btn">Probar Nuevo Socio</a>
        <a href="<?php echo home_url('/tests-v2'); ?>" class="btn" style="background: #28a745;">Ejecutar Tests V2</a>
        <a href="<?php echo home_url('/diagnostico'); ?>" class="btn" style="background: #17a2b8;">Ver Diagnóstico Completo</a>
    </p>
</body>
</html>
