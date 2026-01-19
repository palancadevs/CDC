<?php
/**
 * Verificar páginas del sistema
 * Acceder vía: http://localhost:10013/verificar-paginas.php
 */

define('WP_USE_THEMES', false);
require('./wp-load.php');

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Verificar Páginas CDC</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #007bff; color: white; }
        .exists { color: #28a745; font-weight: bold; }
        .missing { color: #dc3545; font-weight: bold; }
        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
    </style>
</head>
<body>
    <h1>🔍 Verificación de Páginas del Sistema CDC</h1>

    <table>
        <thead>
            <tr>
                <th>Slug</th>
                <th>Estado</th>
                <th>ID</th>
                <th>Template</th>
                <th>Acceso</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $pages = array(
                'personas' => 'page-personas.php',
                'cobrar' => 'page-cobrar.php',
                'registrar-gasto' => 'page-registrar-gasto.php',
                'talleres' => 'page-talleres.php',
                'nuevo-socio' => 'page-nuevo-socio.php',
                'nuevo-cliente' => 'page-nuevo-cliente.php',
                'nuevo-taller' => 'page-nuevo-taller.php',
                'diagnostico' => 'page-diagnostico.php',
                'instalar-tablas' => 'page-instalar-tablas.php',
                'tests' => 'page-tests.php'
            );

            foreach ($pages as $slug => $template) {
                $page = get_page_by_path($slug);
                if ($page) {
                    $page_template = get_post_meta($page->ID, '_wp_page_template', true);
                    echo '<tr>';
                    echo '<td><code>/' . $slug . '</code></td>';
                    echo '<td class="exists">✓ EXISTE</td>';
                    echo '<td>' . $page->ID . '</td>';
                    echo '<td><code>' . ($page_template ?: 'default') . '</code></td>';
                    echo '<td><a href="' . home_url('/' . $slug) . '" target="_blank">Abrir →</a></td>';
                    echo '</tr>';
                } else {
                    echo '<tr>';
                    echo '<td><code>/' . $slug . '</code></td>';
                    echo '<td class="missing">✗ NO EXISTE</td>';
                    echo '<td>-</td>';
                    echo '<td><code>' . $template . '</code></td>';
                    echo '<td>-</td>';
                    echo '</tr>';
                }
            }
            ?>
        </tbody>
    </table>

    <?php
    $missing = array_filter($pages, function($slug) {
        return !get_page_by_path($slug);
    }, ARRAY_FILTER_USE_KEY);

    if (count($missing) > 0):
    ?>
        <h2 style="color: #dc3545;">⚠️ Faltan <?php echo count($missing); ?> páginas</h2>
        <p><strong>Solución:</strong> Ejecuta este código en WP Admin > Herramientas > Site Health > Info > Debug:</p>
        <pre style="background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto;">
delete_option('cdc_pages_created');
do_action('after_switch_theme');
        </pre>
        <p>O crea las páginas manualmente en <strong>Páginas > Añadir nueva</strong></p>
    <?php else: ?>
        <h2 style="color: #28a745;">✅ Todas las páginas existen correctamente</h2>
    <?php endif; ?>

    <a href="<?php echo home_url('/diagnostico'); ?>" class="btn">Ver Diagnóstico Completo</a>
    <a href="<?php echo home_url('/tests'); ?>" class="btn" style="background: #28a745;">Ejecutar Tests</a>
</body>
</html>
