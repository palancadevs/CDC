<!DOCTYPE html>
<html>
<head><title>Install Taller Tables</title></head>
<body>
<h1>Installing Taller Tables...</h1>
<?php
require_once(dirname(__FILE__) . '/wp-load.php');
require_once(dirname(__FILE__) . '/wp-content/plugins/cdc-api/includes/database/schema.php');

global $wpdb;

echo '<h2>Creating inscripciones_taller table...</h2>';

$charset_collate = $wpdb->get_charset_collate();
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

$table_inscripciones_taller = $wpdb->prefix . 'cdc_inscripciones_taller';
$sql_inscripciones_taller = "CREATE TABLE $table_inscripciones_taller (
    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    taller_id bigint(20) unsigned NOT NULL,
    persona_id bigint(20) unsigned NOT NULL,
    fecha_inscripcion date NOT NULL,
    fecha_baja date DEFAULT NULL,
    estado enum('activo','inactivo','finalizado') NOT NULL DEFAULT 'activo',
    monto_mensual decimal(10,2) NOT NULL DEFAULT 0.00,
    notas text DEFAULT NULL,
    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY taller_id (taller_id),
    KEY persona_id (persona_id),
    KEY estado (estado),
    KEY fecha_inscripcion (fecha_inscripcion)
) $charset_collate;";
dbDelta($sql_inscripciones_taller);

echo '<p style="color: green;">✓ inscripciones_taller table created/updated</p>';

echo '<h2>Creating cuotas_taller table...</h2>';

$table_cuotas_taller = $wpdb->prefix . 'cdc_cuotas_taller';
$sql_cuotas_taller = "CREATE TABLE $table_cuotas_taller (
    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    inscripcion_id bigint(20) unsigned NOT NULL,
    persona_id bigint(20) unsigned NOT NULL,
    taller_id bigint(20) unsigned NOT NULL,
    anio int(4) NOT NULL,
    mes tinyint(2) NOT NULL,
    monto decimal(10,2) NOT NULL DEFAULT 0.00,
    pagada tinyint(1) NOT NULL DEFAULT 0,
    fecha_pago datetime DEFAULT NULL,
    medio_pago enum('efectivo','transferencia','tarjeta','mercadopago') DEFAULT NULL,
    comprobante_id varchar(100) DEFAULT NULL,
    recibo_id bigint(20) unsigned DEFAULT NULL,
    observaciones text DEFAULT NULL,
    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY inscripcion_id (inscripcion_id),
    KEY persona_id (persona_id),
    KEY taller_id (taller_id),
    KEY anio (anio),
    KEY mes (mes),
    KEY pagada (pagada),
    KEY recibo_id (recibo_id)
) $charset_collate;";
dbDelta($sql_cuotas_taller);

echo '<p style="color: green;">✓ cuotas_taller table created/updated</p>';

echo '<h2>Verifying tables exist...</h2>';
$result1 = $wpdb->get_var("SHOW TABLES LIKE '{$table_inscripciones_taller}'");
$result2 = $wpdb->get_var("SHOW TABLES LIKE '{$table_cuotas_taller}'");

if ($result1) {
    echo '<p style="color: green;">✓ inscripciones_taller exists in database</p>';
} else {
    echo '<p style="color: red;">✗ inscripciones_taller NOT found</p>';
}

if ($result2) {
    echo '<p style="color: green;">✓ cuotas_taller exists in database</p>';
} else {
    echo '<p style="color: red;">✗ cuotas_taller NOT found</p>';
}

echo '<p><a href="/">Go to Home</a></p>';
?>
</body>
</html>
