<?php
/**
 * Crear páginas faltantes
 * Acceder vía: http://localhost:10013/crear-paginas-faltantes.php
 */

define('WP_USE_THEMES', false);
require('./wp-load.php');

header('Content-Type: text/html; charset=utf-8');

// Security check
if (!current_user_can('manage_options')) {
    die('No tienes permisos para ejecutar este script');
}

$pages_to_create = array(
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
        'post_title'   => 'Tests V2',
        'post_name'    => 'tests-v2',
        'post_content' => '',
        'post_status'  => 'publish',
        'post_type'    => 'page',
        'page_template' => 'page-tests-v2.php'
    ),
);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Crear Páginas Faltantes</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { background: #d4edda; border: 2px solid #28a745; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; border: 2px solid #dc3545; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; border: 2px solid #17a2b8; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin-top: 10px; }
    </style>
</head>
<body>
    <h1>🔧 Creando Páginas Faltantes</h1>

    <?php
    $created = array();
    $errors = array();

    foreach ($pages_to_create as $page_data) {
        $existing = get_page_by_path($page_data['post_name']);

        if ($existing) {
            echo '<div class="info">ℹ️ La página <strong>' . $page_data['post_title'] . '</strong> ya existe (ID: ' . $existing->ID . ')</div>';
            continue;
        }

        $page_id = wp_insert_post($page_data);

        if ($page_id && !is_wp_error($page_id)) {
            update_post_meta($page_id, '_wp_page_template', $page_data['page_template']);
            $created[] = $page_data['post_title'];
            echo '<div class="success">✅ Página <strong>' . $page_data['post_title'] . '</strong> creada exitosamente (ID: ' . $page_id . ')</div>';
            echo '<p>Acceder a: <a href="' . home_url('/' . $page_data['post_name']) . '" target="_blank">' . home_url('/' . $page_data['post_name']) . '</a></p>';
        } else {
            $error_msg = is_wp_error($page_id) ? $page_id->get_error_message() : 'Error desconocido';
            $errors[] = $page_data['post_title'] . ': ' . $error_msg;
            echo '<div class="error">❌ Error al crear <strong>' . $page_data['post_title'] . '</strong>: ' . $error_msg . '</div>';
        }
    }

    if (count($created) > 0) {
        echo '<div class="success">';
        echo '<h2>✅ Resumen: ' . count($created) . ' página(s) creada(s)</h2>';
        echo '<ul>';
        foreach ($created as $page) {
            echo '<li>' . $page . '</li>';
        }
        echo '</ul>';
        echo '</div>';
    }

    if (count($errors) > 0) {
        echo '<div class="error">';
        echo '<h2>❌ Errores: ' . count($errors) . '</h2>';
        echo '<ul>';
        foreach ($errors as $error) {
            echo '<li>' . $error . '</li>';
        }
        echo '</ul>';
        echo '</div>';
    }

    if (count($created) === 0 && count($errors) === 0) {
        echo '<div class="info">';
        echo '<h2>ℹ️ Todas las páginas ya existen</h2>';
        echo '</div>';
    }
    ?>

    <h2>Próximos pasos:</h2>
    <p>
        <a href="<?php echo home_url('/verificar-paginas.php'); ?>" class="btn">Verificar Páginas</a>
        <a href="<?php echo home_url('/tests'); ?>" class="btn" style="background: #28a745;">Ejecutar Tests</a>
    </p>
</body>
</html>
