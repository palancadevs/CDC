<?php
/**
 * Template Name: Diagnóstico del Sistema
 *
 * @package CDC_Sistema
 */

if (!defined('ABSPATH')) {
    exit;
}

// Don't use get_header() - we want a clean diagnostic page
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Diagnóstico CDC Sistema</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .diagnostic-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 { color: #2c3e50; margin: 0 0 20px 0; }
        h2 { color: #34495e; font-size: 18px; margin: 0 0 15px 0; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        .status { display: inline-block; padding: 4px 12px; border-radius: 4px; font-weight: bold; font-size: 12px; }
        .status-ok { background: #27ae60; color: white; }
        .status-error { background: #e74c3c; color: white; }
        .status-warning { background: #f39c12; color: white; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid #ecf0f1; }
        th { background: #ecf0f1; font-weight: 600; }
        .code { background: #2c3e50; color: #ecf0f1; padding: 15px; border-radius: 4px; overflow-x: auto; font-family: monospace; font-size: 13px; }
        .btn { display: inline-block; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 4px; margin-top: 10px; }
        .btn:hover { background: #2980b9; }
    </style>
</head>
<body>
    <div class="diagnostic-card">
        <h1>🔍 Diagnóstico del Sistema CDC</h1>
        <p><strong>Fecha:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
    </div>

    <?php
    // 1. Check Plugin Status
    $plugin_active = is_plugin_active('cdc-api/cdc-api.php');
    ?>
    <div class="diagnostic-card">
        <h2>1. Plugin CDC API</h2>
        <table>
            <tr>
                <td><strong>Estado del Plugin</strong></td>
                <td>
                    <?php if ($plugin_active): ?>
                        <span class="status status-ok">✓ ACTIVO</span>
                    <?php else: ?>
                        <span class="status status-error">✗ INACTIVO</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td><strong>Archivo principal</strong></td>
                <td><?php echo file_exists(WP_PLUGIN_DIR . '/cdc-api/cdc-api.php') ? '✓ Existe' : '✗ No encontrado'; ?></td>
            </tr>
        </table>
        <?php if (!$plugin_active): ?>
            <p style="color: #e74c3c; margin-top: 10px;">
                ⚠️ El plugin debe estar activo. Ve a <strong>WP Admin > Plugins</strong> y activa "CDC API"
            </p>
        <?php endif; ?>
    </div>

    <?php
    // 2. Check Database Tables
    global $wpdb;
    $required_tables = array(
        'cdc_personas',
        'cdc_socios',
        'cdc_clientes',
        'cdc_recibos',
        'cdc_items_recibo',
        'cdc_movimientos_caja',
        'cdc_gastos',
        'cdc_talleres',
        'cdc_eventos',
        'cdc_salas',
        'cdc_reservas_salas',
        'cdc_pagos_mensuales',
        'cdc_pagos_eventos'
    );

    $tables_status = array();
    foreach ($required_tables as $table) {
        $full_table_name = $wpdb->prefix . $table;
        $exists = $wpdb->get_var("SHOW TABLES LIKE '$full_table_name'") === $full_table_name;
        $count = 0;
        if ($exists) {
            $count = $wpdb->get_var("SELECT COUNT(*) FROM $full_table_name");
        }
        $tables_status[$table] = array('exists' => $exists, 'count' => $count);
    }

    $all_tables_exist = count(array_filter($tables_status, function($t) { return $t['exists']; })) === count($required_tables);
    ?>
    <div class="diagnostic-card">
        <h2>2. Tablas de Base de Datos</h2>
        <?php if ($all_tables_exist): ?>
            <span class="status status-ok">✓ TODAS LAS TABLAS CREADAS</span>
        <?php else: ?>
            <span class="status status-error">✗ FALTAN TABLAS</span>
        <?php endif; ?>

        <table style="margin-top: 15px;">
            <thead>
                <tr>
                    <th>Tabla</th>
                    <th>Estado</th>
                    <th>Registros</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tables_status as $table => $status): ?>
                <tr>
                    <td><code><?php echo $wpdb->prefix . $table; ?></code></td>
                    <td>
                        <?php if ($status['exists']): ?>
                            <span class="status status-ok">✓ Existe</span>
                        <?php else: ?>
                            <span class="status status-error">✗ No existe</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $status['exists'] ? $status['count'] : 'N/A'; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if (!$all_tables_exist): ?>
            <p style="color: #e74c3c; margin-top: 10px;">
                ⚠️ Faltan tablas en la base de datos.
            </p>
            <p style="margin-top: 10px;">
                <strong>Solución rápida:</strong>
                <a href="<?php echo home_url('/instalar-tablas'); ?>" class="btn" style="background: #dc3545;">Instalar tablas ahora</a>
            </p>
            <p style="font-size: 12px; color: #6c757d; margin-top: 5px;">
                O desactiva y reactiva el plugin "CDC API" en WP Admin.
            </p>
        <?php endif; ?>
    </div>

    <?php
    // 3. Check REST API Endpoints
    $rest_url = rest_url('cdc/v1/');
    ?>
    <div class="diagnostic-card">
        <h2>3. REST API Endpoints</h2>
        <table>
            <tr>
                <td><strong>Base URL</strong></td>
                <td><code><?php echo $rest_url; ?></code></td>
            </tr>
            <tr>
                <td><strong>Disponible</strong></td>
                <td>
                    <?php if (function_exists('rest_url')): ?>
                        <span class="status status-ok">✓ SÍ</span>
                    <?php else: ?>
                        <span class="status status-error">✗ NO</span>
                    <?php endif; ?>
                </td>
            </tr>
        </table>

        <h3 style="margin-top: 20px; font-size: 14px;">Endpoints esperados:</h3>
        <ul style="font-family: monospace; font-size: 13px;">
            <li>GET <?php echo $rest_url; ?>personas</li>
            <li>POST <?php echo $rest_url; ?>personas</li>
            <li>POST <?php echo $rest_url; ?>recibos</li>
            <li>POST <?php echo $rest_url; ?>caja/gastos</li>
            <li>GET <?php echo $rest_url; ?>caja/movimientos/today</li>
            <li>GET <?php echo $rest_url; ?>talleres</li>
            <li>POST <?php echo $rest_url; ?>talleres</li>
        </ul>
    </div>

    <?php
    // 4. Check Theme
    $theme = wp_get_theme();
    ?>
    <div class="diagnostic-card">
        <h2>4. Tema Activo</h2>
        <table>
            <tr>
                <td><strong>Tema</strong></td>
                <td><?php echo $theme->get('Name'); ?></td>
            </tr>
            <tr>
                <td><strong>Versión</strong></td>
                <td><?php echo $theme->get('Version'); ?></td>
            </tr>
            <tr>
                <td><strong>Estado</strong></td>
                <td>
                    <?php if ($theme->get('Name') === 'CDC Sistema'): ?>
                        <span class="status status-ok">✓ CORRECTO</span>
                    <?php else: ?>
                        <span class="status status-warning">⚠ Tema incorrecto</span>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>

    <?php
    // 5. Sample Data Status
    $personas_count = $tables_status['cdc_personas']['count'];
    $has_data = $personas_count > 0;
    ?>
    <div class="diagnostic-card">
        <h2>5. Datos de Prueba</h2>
        <table>
            <tr>
                <td><strong>Personas registradas</strong></td>
                <td><?php echo $personas_count; ?></td>
            </tr>
            <tr>
                <td><strong>Estado</strong></td>
                <td>
                    <?php if ($has_data): ?>
                        <span class="status status-ok">✓ HAY DATOS</span>
                    <?php else: ?>
                        <span class="status status-warning">⚠ SIN DATOS</span>
                    <?php endif; ?>
                </td>
            </tr>
        </table>

        <?php if (!$has_data): ?>
            <p style="margin-top: 10px;">
                💡 <strong>Sugerencia:</strong> Crea tu primera persona desde:
                <a href="<?php echo home_url('/nuevo-socio'); ?>" class="btn">Nuevo Socio</a>
                <a href="<?php echo home_url('/nuevo-cliente'); ?>" class="btn">Nuevo Cliente</a>
            </p>
        <?php endif; ?>
    </div>

    <?php
    // 6. Final Verdict
    $system_ready = $plugin_active && $all_tables_exist;
    ?>
    <div class="diagnostic-card" style="background: <?php echo $system_ready ? '#d4edda' : '#f8d7da'; ?>; border: 2px solid <?php echo $system_ready ? '#c3e6cb' : '#f5c6cb'; ?>;">
        <h2>🎯 Veredicto Final</h2>
        <?php if ($system_ready): ?>
            <p style="font-size: 18px; margin: 0;"><strong>✅ El sistema está LISTO para usar</strong></p>
            <p style="margin-top: 10px;">Puedes comenzar a:</p>
            <ul>
                <li>Crear personas (socios/clientes)</li>
                <li>Registrar cobros</li>
                <li>Registrar gastos</li>
                <li>Crear talleres</li>
            </ul>
            <a href="<?php echo home_url('/'); ?>" class="btn" style="background: #27ae60;">Ir al Dashboard</a>
        <?php else: ?>
            <p style="font-size: 18px; margin: 0;"><strong>❌ El sistema NO está listo</strong></p>
            <p style="margin-top: 10px;">Pasos requeridos:</p>
            <ol>
                <?php if (!$plugin_active): ?>
                    <li>Activar el plugin "CDC API" en WP Admin > Plugins</li>
                <?php endif; ?>
                <?php if (!$all_tables_exist): ?>
                    <li>Desactivar y reactivar el plugin para crear las tablas de base de datos</li>
                <?php endif; ?>
            </ol>
        <?php endif; ?>
    </div>

    <div class="diagnostic-card">
        <p style="text-align: center; color: #7f8c8d; margin: 0;">
            <small>Diagnóstico generado por CDC Sistema v1.0.0</small>
        </p>
    </div>
</body>
</html>
