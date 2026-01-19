<?php
/**
 * Template Name: Instalar Tablas
 * USAR SOLO UNA VEZ PARA CREAR TABLAS FALTANTES
 *
 * @package CDC_Sistema
 */

if (!defined('ABSPATH')) {
    exit;
}

// Security check - solo ejecutar si se pasa un parámetro específico
if (!isset($_GET['ejecutar']) || $_GET['ejecutar'] !== 'si') {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>Instalar Tablas - CDC Sistema</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
            .warning { background: #fff3cd; border: 2px solid #ffc107; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
            .btn { display: inline-block; padding: 15px 30px; background: #dc3545; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; }
            .btn:hover { background: #c82333; }
            .info { background: #d1ecf1; border: 2px solid #17a2b8; padding: 15px; border-radius: 8px; margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class="warning">
            <h1>⚠️ Instalar Tablas de Base de Datos</h1>
            <p><strong>IMPORTANTE:</strong> Este script creará todas las tablas faltantes en la base de datos.</p>
            <p>Solo debes ejecutarlo <strong>UNA VEZ</strong>.</p>
            <p><a href="?ejecutar=si" class="btn">Sí, instalar tablas ahora</a></p>
        </div>
        <div class="info">
            <p><strong>Nota:</strong> Después de instalar las tablas, regresa al <a href="<?php echo home_url('/diagnostico'); ?>">diagnóstico</a> para verificar.</p>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Ejecutar la instalación
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Instalando Tablas - CDC Sistema</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .success { background: #d4edda; border: 2px solid #28a745; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .error { background: #f8d7da; border: 2px solid #dc3545; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .log { background: #fff; border: 1px solid #ddd; padding: 15px; border-radius: 4px; font-family: monospace; font-size: 13px; max-height: 400px; overflow-y: auto; }
        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin-top: 10px; }
    </style>
</head>
<body>
    <h1>🔧 Instalando Tablas de Base de Datos</h1>

    <?php
    // Verificar que existe la clase del schema
    if (!class_exists('CDC_Database_Schema')) {
        // Intentar cargar el archivo
        $schema_file = WP_PLUGIN_DIR . '/cdc-api/includes/database/schema.php';
        if (file_exists($schema_file)) {
            require_once $schema_file;
        }
    }

    if (!class_exists('CDC_Database_Schema')) {
        echo '<div class="error">';
        echo '<h2>❌ Error</h2>';
        echo '<p>No se pudo encontrar la clase CDC_Database_Schema.</p>';
        echo '<p>Verifica que el plugin CDC API esté instalado correctamente.</p>';
        echo '</div>';
        exit;
    }

    // Ejecutar creación de tablas
    try {
        ob_start();
        CDC_Database_Schema::create_tables();
        $output = ob_get_clean();

        echo '<div class="success">';
        echo '<h2>✅ Tablas creadas exitosamente</h2>';
        echo '<p>Todas las tablas del sistema CDC han sido creadas.</p>';
        echo '</div>';

        if ($output) {
            echo '<h3>Log de ejecución:</h3>';
            echo '<div class="log">' . esc_html($output) . '</div>';
        }

        // Mostrar tablas creadas
        global $wpdb;
        $tables = $wpdb->get_results("SHOW TABLES LIKE '{$wpdb->prefix}cdc_%'");

        echo '<h3>Tablas en la base de datos:</h3>';
        echo '<ul>';
        foreach ($tables as $table) {
            $table_name = array_values((array)$table)[0];
            $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
            echo '<li><code>' . esc_html($table_name) . '</code> - ' . $count . ' registros</li>';
        }
        echo '</ul>';

        echo '<p><a href="' . home_url('/diagnostico') . '" class="btn">Ver diagnóstico completo</a></p>';

    } catch (Exception $e) {
        echo '<div class="error">';
        echo '<h2>❌ Error durante la instalación</h2>';
        echo '<p>' . esc_html($e->getMessage()) . '</p>';
        echo '</div>';
    }
    ?>
</body>
</html>
