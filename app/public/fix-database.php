<?php
/**
 * Script temporal para arreglar foreign keys incorrectos
 *
 * Ejecutar visitando: http://localhost:10013/fix-database.php
 * ELIMINAR ESTE ARCHIVO después de ejecutarlo
 */

// Load WordPress
require_once('wp-load.php');

echo "<h1>Arreglando Base de Datos CDC</h1>";
echo "<pre>";

global $wpdb;

// Queries to drop incorrect foreign keys
$queries = array(
    "ALTER TABLE wp_cdc_cuota_socio DROP FOREIGN KEY IF EXISTS wp_cdc_cuota_socio_ibfk_1",
    "ALTER TABLE wp_cdc_cuotas_taller DROP FOREIGN KEY IF EXISTS wp_cdc_cuotas_taller_ibfk_1",
    "ALTER TABLE wp_cdc_socios DROP FOREIGN KEY IF EXISTS wp_cdc_socios_ibfk_1",
    "ALTER TABLE wp_cdc_clientes DROP FOREIGN KEY IF EXISTS wp_cdc_clientes_ibfk_1",
    "ALTER TABLE wp_cdc_inscripciones_taller DROP FOREIGN KEY IF EXISTS wp_cdc_inscripciones_taller_ibfk_1",
    "ALTER TABLE wp_cdc_inscripciones_taller DROP FOREIGN KEY IF EXISTS wp_cdc_inscripciones_taller_ibfk_2",
    "ALTER TABLE wp_cdc_reservas_salas DROP FOREIGN KEY IF EXISTS wp_cdc_reservas_salas_ibfk_1",
    "ALTER TABLE wp_cdc_reservas_salas DROP FOREIGN KEY IF EXISTS wp_cdc_reservas_salas_ibfk_2",
);

echo "Eliminando foreign keys incorrectos...\n\n";

foreach ($queries as $query) {
    echo "Ejecutando: $query\n";
    $result = $wpdb->query($query);

    if ($result === false) {
        echo "  ❌ Error: " . $wpdb->last_error . "\n";
    } else {
        echo "  ✅ OK\n";
    }
    echo "\n";
}

echo "\n";
echo "========================================\n";
echo "Verificando foreign keys restantes:\n";
echo "========================================\n\n";

$fks = $wpdb->get_results("
    SELECT
        TABLE_NAME,
        CONSTRAINT_NAME,
        COLUMN_NAME,
        REFERENCED_TABLE_NAME,
        REFERENCED_COLUMN_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = 'local'
    AND TABLE_NAME LIKE 'wp_cdc%'
    AND REFERENCED_TABLE_NAME IS NOT NULL
");

if (empty($fks)) {
    echo "✅ No hay foreign keys en tablas CDC (correcto)\n";
} else {
    echo "⚠️  Aún hay foreign keys:\n";
    foreach ($fks as $fk) {
        echo "  - {$fk->TABLE_NAME}.{$fk->COLUMN_NAME} -> {$fk->REFERENCED_TABLE_NAME}.{$fk->REFERENCED_COLUMN_NAME} ({$fk->CONSTRAINT_NAME})\n";
    }
}

echo "\n";
echo "========================================\n";
echo "✅ Proceso completado!\n";
echo "========================================\n\n";
echo "IMPORTANTE: Elimina este archivo (fix-database.php) después de ejecutarlo.\n";
echo "</pre>";
