<!DOCTYPE html>
<html>
<head><title>Create Taller Test Data</title></head>
<body>
<h1>Creating Taller Test Data...</h1>
<?php
require_once(dirname(__FILE__) . '/wp-load.php');
require_once(dirname(__FILE__) . '/wp-content/plugins/cdc-api/cdc-api.php');

global $wpdb;

echo '<h2>1. Create test inscripcion</h2>';

// Use existing persona_id=6 (Pedro Test Cuotas OK) and taller_id=4 (Yoga Integral)
$persona_id = 6;
$taller_id = 4;

// Check if inscripcion already exists
$existing = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}cdc_inscripciones_taller
     WHERE persona_id = %d AND taller_id = %d",
    $persona_id,
    $taller_id
));

if ($existing) {
    echo '<p>✓ Inscripcion already exists with ID: ' . $existing->id . '</p>';
    $inscripcion_id = $existing->id;
} else {
    $inscripcion_data = array(
        'taller_id' => $taller_id,
        'persona_id' => $persona_id,
        'fecha_inscripcion' => '2026-01-01',
        'estado' => 'activo',
        'monto_mensual' => 15000.00,
        'notas' => 'Inscripción de prueba'
    );

    $inscripcion_model = new CDC_Inscripcion_Taller();
    $inscripcion_id = $inscripcion_model->create($inscripcion_data);

    if ($inscripcion_id) {
        echo '<p style="color: green;">✓ Inscripcion created with ID: ' . $inscripcion_id . '</p>';
    } else {
        echo '<p style="color: red;">✗ Error creating inscripcion</p>';
        exit;
    }
}

echo '<h2>2. Generate cuotas for 2026</h2>';

// Check if cuotas already exist
$existing_cuotas = $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$wpdb->prefix}cdc_cuotas_taller
     WHERE inscripcion_id = %d AND anio = 2026",
    $inscripcion_id
));

if ($existing_cuotas > 0) {
    echo '<p>✓ Cuotas already exist (' . $existing_cuotas . ' cuotas)</p>';
} else {
    $cuota_model = new CDC_Cuota_Taller();
    $cuota_ids = $cuota_model->generate_cuotas(
        $inscripcion_id,
        $persona_id,
        $taller_id,
        2026,
        15000.00,
        1 // Starting from January
    );

    echo '<p style="color: green;">✓ Generated ' . count($cuota_ids) . ' cuotas for 2026</p>';
}

echo '<h2>3. Verify data</h2>';

$inscripcion = $wpdb->get_row($wpdb->prepare(
    "SELECT i.*, t.nombre as taller_nombre, p.nombre, p.apellido
     FROM {$wpdb->prefix}cdc_inscripciones_taller i
     LEFT JOIN {$wpdb->prefix}cdc_talleres t ON i.taller_id = t.id
     LEFT JOIN {$wpdb->prefix}cdc_personas p ON i.persona_id = p.id
     WHERE i.id = %d",
    $inscripcion_id
));

echo '<p><strong>Inscripcion:</strong><br>';
echo '- ID: ' . $inscripcion->id . '<br>';
echo '- Persona: ' . $inscripcion->nombre . ' ' . $inscripcion->apellido . ' (ID: ' . $inscripcion->persona_id . ')<br>';
echo '- Taller: ' . $inscripcion->taller_nombre . ' (ID: ' . $inscripcion->taller_id . ')<br>';
echo '- Monto Mensual: $' . number_format($inscripcion->monto_mensual, 2) . '<br>';
echo '- Estado: ' . $inscripcion->estado . '</p>';

$cuotas = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}cdc_cuotas_taller
     WHERE inscripcion_id = %d ORDER BY mes ASC",
    $inscripcion_id
));

echo '<p><strong>Cuotas 2026:</strong><br>';
foreach ($cuotas as $cuota) {
    $status = $cuota->pagada ? '✓ Pagada' : '✗ Pendiente';
    echo '- Mes ' . $cuota->mes . ': $' . number_format($cuota->monto, 2) . ' ' . $status . '<br>';
}
echo '</p>';

echo '<p><strong>API Test URL:</strong><br>';
echo '<a href="/wp-json/cdc/v1/cobros/cuotas-taller-pendientes/' . $persona_id . '" target="_blank">';
echo '/wp-json/cdc/v1/cobros/cuotas-taller-pendientes/' . $persona_id;
echo '</a></p>';

echo '<p><a href="/">Go to Home</a></p>';
?>
</body>
</html>
