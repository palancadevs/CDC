<?php
/**
 * Template Name: Tests del Sistema
 *
 * @package CDC_Sistema
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tests del Sistema CDC</title>

    <!-- Load WordPress head (includes jQuery and enqueued scripts) -->
    <?php wp_head(); ?>

    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            max-width: 1400px;
            margin: 20px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .test-header {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .test-section {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .test-case {
            border-left: 4px solid #ddd;
            padding: 15px;
            margin: 10px 0;
            background: #f9f9f9;
            border-radius: 4px;
        }
        .test-case.running {
            border-left-color: #ffc107;
            background: #fff3cd;
        }
        .test-case.success {
            border-left-color: #28a745;
            background: #d4edda;
        }
        .test-case.failed {
            border-left-color: #dc3545;
            background: #f8d7da;
        }
        .test-name {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .test-result {
            font-family: monospace;
            font-size: 13px;
            margin-top: 10px;
            padding: 10px;
            background: white;
            border-radius: 4px;
            white-space: pre-wrap;
            max-height: 200px;
            overflow-y: auto;
        }
        .test-error {
            color: #dc3545;
            font-weight: bold;
        }
        .test-success {
            color: #28a745;
            font-weight: bold;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 10px;
        }
        .btn:hover {
            background: #0056b3;
        }
        .btn-success {
            background: #28a745;
        }
        .btn-success:hover {
            background: #218838;
        }
        .btn-danger {
            background: #dc3545;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .summary {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .summary-item {
            flex: 1;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .summary-item h3 {
            margin: 0;
            font-size: 32px;
        }
        .summary-item p {
            margin: 5px 0 0 0;
            color: #6c757d;
        }
        .total { background: #e7f3ff; }
        .passed { background: #d4edda; }
        .failed { background: #f8d7da; }
        #progress-bar {
            width: 100%;
            height: 30px;
            background: #e9ecef;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        #progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #007bff, #0056b3);
            width: 0%;
            transition: width 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="test-header">
        <h1>🧪 Tests del Sistema CDC</h1>
        <p>Sistema de pruebas automatizado para verificar todas las funcionalidades</p>

        <div style="margin-top: 20px;">
            <button id="run-all-tests" class="btn btn-success">▶️ Ejecutar Todos los Tests</button>
            <button id="clear-data" class="btn btn-danger">🗑️ Limpiar Datos de Prueba</button>
            <a href="<?php echo home_url('/diagnostico'); ?>" class="btn">📊 Ver Diagnóstico</a>
        </div>

        <div id="progress-bar" style="display: none; margin-top: 20px;">
            <div id="progress-fill">0%</div>
        </div>

        <div class="summary" id="summary" style="display: none; margin-top: 20px;">
            <div class="summary-item total">
                <h3 id="total-tests">0</h3>
                <p>Total Tests</p>
            </div>
            <div class="summary-item passed">
                <h3 id="passed-tests">0</h3>
                <p>Pasados</p>
            </div>
            <div class="summary-item failed">
                <h3 id="failed-tests">0</h3>
                <p>Fallados</p>
            </div>
        </div>
    </div>

    <div class="test-section">
        <h2>1. Tests de API REST</h2>
        <div id="test-api-health" class="test-case">
            <div class="test-name">Test 1.1: Verificar que API REST está disponible</div>
            <div class="test-result"></div>
        </div>
        <div id="test-api-personas" class="test-case">
            <div class="test-name">Test 1.2: Endpoint GET /personas responde</div>
            <div class="test-result"></div>
        </div>
        <div id="test-api-caja" class="test-case">
            <div class="test-name">Test 1.3: Endpoint GET /caja/movimientos/today responde</div>
            <div class="test-result"></div>
        </div>
        <div id="test-api-talleres" class="test-case">
            <div class="test-name">Test 1.4: Endpoint GET /talleres responde</div>
            <div class="test-result"></div>
        </div>
    </div>

    <div class="test-section">
        <h2>2. Tests de Creación de Personas</h2>
        <div id="test-create-socio" class="test-case">
            <div class="test-name">Test 2.1: Crear nuevo socio via API</div>
            <div class="test-result"></div>
        </div>
        <div id="test-create-cliente" class="test-case">
            <div class="test-name">Test 2.2: Crear nuevo cliente via API</div>
            <div class="test-result"></div>
        </div>
        <div id="test-search-persona" class="test-case">
            <div class="test-name">Test 2.3: Buscar persona creada</div>
            <div class="test-result"></div>
        </div>
    </div>

    <div class="test-section">
        <h2>3. Tests de Recibos/Cobros</h2>
        <div id="test-create-recibo" class="test-case">
            <div class="test-name">Test 3.1: Crear recibo de cobro</div>
            <div class="test-result"></div>
        </div>
        <div id="test-list-recibos" class="test-case">
            <div class="test-name">Test 3.2: Listar recibos</div>
            <div class="test-result"></div>
        </div>
    </div>

    <div class="test-section">
        <h2>4. Tests de Caja/Gastos</h2>
        <div id="test-create-gasto" class="test-case">
            <div class="test-name">Test 4.1: Registrar gasto via API</div>
            <div class="test-result"></div>
        </div>
        <div id="test-list-movimientos" class="test-case">
            <div class="test-name">Test 4.2: Listar movimientos de caja</div>
            <div class="test-result"></div>
        </div>
    </div>

    <div class="test-section">
        <h2>5. Tests de Talleres</h2>
        <div id="test-create-taller" class="test-case">
            <div class="test-name">Test 5.1: Crear taller via API</div>
            <div class="test-result"></div>
        </div>
        <div id="test-list-talleres" class="test-case">
            <div class="test-name">Test 5.2: Listar talleres</div>
            <div class="test-result"></div>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        // Verificar que los objetos globales estén disponibles
        if (typeof CDCAPI === 'undefined') {
            alert('❌ ERROR: CDCAPI no está cargado. Verifica que el archivo cdc-api.js se haya cargado correctamente.');
            console.error('CDCAPI is not defined. Check that cdc-api.js is loaded.');
            return;
        }

        if (typeof CDC === 'undefined') {
            alert('❌ ERROR: CDC no está cargado. Verifica que el archivo app.js se haya cargado correctamente.');
            console.error('CDC is not defined. Check that app.js is loaded.');
            return;
        }

        if (typeof cdcData === 'undefined') {
            alert('❌ ERROR: cdcData no está definido. Verifica que wp_localize_script esté funcionando.');
            console.error('cdcData is not defined. Check that wp_localize_script is working.');
            return;
        }

        console.log('✅ Todos los scripts cargados correctamente');
        console.log('📦 CDCAPI:', CDCAPI);
        console.log('📦 CDC:', CDC);
        console.log('📦 cdcData:', cdcData);

        let testData = {
            socioId: null,
            clienteId: null,
            reciboId: null,
            tallerId: null
        };

        let stats = {
            total: 0,
            passed: 0,
            failed: 0
        };

        // Helper functions
        function updateTest(testId, status, message) {
            const $test = $('#' + testId);
            $test.removeClass('running success failed').addClass(status);
            $test.find('.test-result').html(message);

            if (status === 'success') {
                stats.passed++;
            } else if (status === 'failed') {
                stats.failed++;
            }
            updateSummary();
        }

        function updateProgress(percent) {
            $('#progress-fill').css('width', percent + '%').text(Math.round(percent) + '%');
        }

        function updateSummary() {
            $('#total-tests').text(stats.total);
            $('#passed-tests').text(stats.passed);
            $('#failed-tests').text(stats.failed);
        }

        // Test functions
        async function runTest1_1() {
            updateTest('test-api-health', 'running', 'Ejecutando...');
            try {
                const response = await fetch(cdcData.apiUrl);
                if (response.ok) {
                    updateTest('test-api-health', 'success', '<span class="test-success">✓ API REST disponible</span>\nURL: ' + cdcData.apiUrl);
                } else {
                    updateTest('test-api-health', 'failed', '<span class="test-error">✗ API no responde correctamente</span>\nStatus: ' + response.status);
                }
            } catch (error) {
                updateTest('test-api-health', 'failed', '<span class="test-error">✗ Error de conexión</span>\n' + error.message);
            }
        }

        async function runTest1_2() {
            updateTest('test-api-personas', 'running', 'Ejecutando...');
            try {
                const response = await CDCAPI.personas.list();
                if (response.success !== undefined) {
                    updateTest('test-api-personas', 'success', '<span class="test-success">✓ Endpoint responde correctamente</span>\nResultado: ' + JSON.stringify(response, null, 2));
                } else {
                    updateTest('test-api-personas', 'failed', '<span class="test-error">✗ Respuesta inválida</span>\n' + JSON.stringify(response, null, 2));
                }
            } catch (error) {
                updateTest('test-api-personas', 'failed', '<span class="test-error">✗ Error en la petición</span>\n' + error.message);
            }
        }

        async function runTest1_3() {
            updateTest('test-api-caja', 'running', 'Ejecutando...');
            try {
                const response = await CDCAPI.caja.movements();
                if (response.success !== undefined) {
                    updateTest('test-api-caja', 'success', '<span class="test-success">✓ Endpoint responde correctamente</span>\nResultado: ' + JSON.stringify(response, null, 2));
                } else {
                    updateTest('test-api-caja', 'failed', '<span class="test-error">✗ Respuesta inválida</span>\n' + JSON.stringify(response, null, 2));
                }
            } catch (error) {
                updateTest('test-api-caja', 'failed', '<span class="test-error">✗ Error en la petición</span>\n' + error.message);
            }
        }

        async function runTest1_4() {
            updateTest('test-api-talleres', 'running', 'Ejecutando...');
            try {
                const response = await CDCAPI.talleres.list();
                if (response.success !== undefined) {
                    updateTest('test-api-talleres', 'success', '<span class="test-success">✓ Endpoint responde correctamente</span>\nResultado: ' + JSON.stringify(response, null, 2));
                } else {
                    updateTest('test-api-talleres', 'failed', '<span class="test-error">✗ Respuesta inválida</span>\n' + JSON.stringify(response, null, 2));
                }
            } catch (error) {
                updateTest('test-api-talleres', 'failed', '<span class="test-error">✗ Error en la petición</span>\n' + error.message);
            }
        }

        async function runTest2_1() {
            updateTest('test-create-socio', 'running', 'Ejecutando...');
            try {
                const data = {
                    tipo: 'socio',
                    nombre: 'Juan Test',
                    apellido: 'Pérez Test',
                    dni: '12345678',
                    email: 'juan.test@test.com',
                    tel: '3815123456',
                    domicilio: 'Calle Test 123',
                    categoria: 'general',
                    observaciones: 'Creado por tests automatizados'
                };

                const response = await CDCAPI.personas.create(data);

                if (response.success && response.data && response.data.id) {
                    testData.socioId = response.data.id;
                    updateTest('test-create-socio', 'success', '<span class="test-success">✓ Socio creado exitosamente</span>\nID: ' + testData.socioId + '\n' + JSON.stringify(response.data, null, 2));
                } else {
                    updateTest('test-create-socio', 'failed', '<span class="test-error">✗ No se pudo crear el socio</span>\n' + JSON.stringify(response, null, 2));
                }
            } catch (error) {
                updateTest('test-create-socio', 'failed', '<span class="test-error">✗ Error en la petición</span>\n' + error.message);
            }
        }

        async function runTest2_2() {
            updateTest('test-create-cliente', 'running', 'Ejecutando...');
            try {
                const data = {
                    tipo: 'cliente',
                    nombre: 'María Test',
                    apellido: 'González Test',
                    dni: '87654321',
                    email: 'maria.test@test.com',
                    tel: '3815654321',
                    domicilio: 'Avenida Test 456',
                    observaciones: 'Creado por tests automatizados'
                };

                const response = await CDCAPI.personas.create(data);

                if (response.success && response.data && response.data.id) {
                    testData.clienteId = response.data.id;
                    updateTest('test-create-cliente', 'success', '<span class="test-success">✓ Cliente creado exitosamente</span>\nID: ' + testData.clienteId + '\n' + JSON.stringify(response.data, null, 2));
                } else {
                    updateTest('test-create-cliente', 'failed', '<span class="test-error">✗ No se pudo crear el cliente</span>\n' + JSON.stringify(response, null, 2));
                }
            } catch (error) {
                updateTest('test-create-cliente', 'failed', '<span class="test-error">✗ Error en la petición</span>\n' + error.message);
            }
        }

        async function runTest2_3() {
            updateTest('test-search-persona', 'running', 'Ejecutando...');
            try {
                const response = await CDCAPI.personas.search('Juan Test');

                if (response.success && response.data && response.data.length > 0) {
                    updateTest('test-search-persona', 'success', '<span class="test-success">✓ Búsqueda exitosa</span>\nEncontrados: ' + response.data.length + '\n' + JSON.stringify(response.data, null, 2));
                } else {
                    updateTest('test-search-persona', 'failed', '<span class="test-error">✗ No se encontraron resultados</span>\n' + JSON.stringify(response, null, 2));
                }
            } catch (error) {
                updateTest('test-search-persona', 'failed', '<span class="test-error">✗ Error en la petición</span>\n' + error.message);
            }
        }

        async function runTest3_1() {
            updateTest('test-create-recibo', 'running', 'Ejecutando...');

            if (!testData.socioId) {
                updateTest('test-create-recibo', 'failed', '<span class="test-error">✗ No hay socio para el test</span>\nEjecuta primero el test 2.1');
                return;
            }

            try {
                const data = {
                    persona_id: testData.socioId,
                    tipo: 'cuota-socio',
                    items: [{
                        descripcion: 'Cuota Test',
                        cantidad: 1,
                        precio_unitario: 1000,
                        subtotal: 1000
                    }],
                    concepto: 'Cuota Test',
                    metodo_pago: 'efectivo',
                    notas: 'Recibo de test'
                };

                const response = await CDCAPI.recibos.create(data);

                if (response.success && response.data && response.data.id) {
                    testData.reciboId = response.data.id;
                    updateTest('test-create-recibo', 'success', '<span class="test-success">✓ Recibo creado exitosamente</span>\nID: ' + testData.reciboId + '\n' + JSON.stringify(response.data, null, 2));
                } else {
                    updateTest('test-create-recibo', 'failed', '<span class="test-error">✗ No se pudo crear el recibo</span>\n' + JSON.stringify(response, null, 2));
                }
            } catch (error) {
                updateTest('test-create-recibo', 'failed', '<span class="test-error">✗ Error en la petición</span>\n' + error.message);
            }
        }

        async function runTest3_2() {
            updateTest('test-list-recibos', 'running', 'Ejecutando...');
            try {
                const response = await CDCAPI.recibos.recent(10);

                if (response.success && response.data) {
                    updateTest('test-list-recibos', 'success', '<span class="test-success">✓ Recibos listados exitosamente</span>\nCantidad: ' + (response.data.length || 0) + '\n' + JSON.stringify(response.data, null, 2));
                } else {
                    updateTest('test-list-recibos', 'failed', '<span class="test-error">✗ Error al listar recibos</span>\n' + JSON.stringify(response, null, 2));
                }
            } catch (error) {
                updateTest('test-list-recibos', 'failed', '<span class="test-error">✗ Error en la petición</span>\n' + error.message);
            }
        }

        async function runTest4_1() {
            updateTest('test-create-gasto', 'running', 'Ejecutando...');
            try {
                const data = {
                    fecha_hora: '<?php echo date('Y-m-d H:i:s'); ?>',
                    tipo: 'egreso',
                    monto: 500,
                    descripcion: 'Gasto de test automatizado',
                    categoria: 'otros',
                    medio_pago: 'efectivo',
                    observaciones: 'Creado por tests'
                };

                const response = await CDCAPI.caja.createGasto(data);

                if (response.success) {
                    updateTest('test-create-gasto', 'success', '<span class="test-success">✓ Gasto registrado exitosamente</span>\n' + JSON.stringify(response, null, 2));
                } else {
                    updateTest('test-create-gasto', 'failed', '<span class="test-error">✗ No se pudo registrar el gasto</span>\n' + JSON.stringify(response, null, 2));
                }
            } catch (error) {
                updateTest('test-create-gasto', 'failed', '<span class="test-error">✗ Error en la petición</span>\n' + error.message);
            }
        }

        async function runTest4_2() {
            updateTest('test-list-movimientos', 'running', 'Ejecutando...');
            try {
                const response = await CDCAPI.caja.movements();

                if (response.success && response.data) {
                    updateTest('test-list-movimientos', 'success', '<span class="test-success">✓ Movimientos listados exitosamente</span>\nCantidad: ' + (response.data.length || 0) + '\n' + JSON.stringify(response.data, null, 2));
                } else {
                    updateTest('test-list-movimientos', 'failed', '<span class="test-error">✗ Error al listar movimientos</span>\n' + JSON.stringify(response, null, 2));
                }
            } catch (error) {
                updateTest('test-list-movimientos', 'failed', '<span class="test-error">✗ Error en la petición</span>\n' + error.message);
            }
        }

        async function runTest5_1() {
            updateTest('test-create-taller', 'running', 'Ejecutando...');
            try {
                const data = {
                    nombre: 'Taller Test',
                    descripcion: 'Taller creado por tests automatizados',
                    tallerista_nombre: 'Profesor Test',
                    dias: 'Lunes y Miércoles',
                    horario_inicio: '18:00',
                    horario_fin: '20:00',
                    horarios: 'Lunes y Miércoles 18:00-20:00',
                    precio: 2000,
                    cupo_maximo: 20,
                    estado: 'activo'
                };

                const response = await CDCAPI.talleres.create(data);

                if (response.success && response.data && response.data.id) {
                    testData.tallerId = response.data.id;
                    updateTest('test-create-taller', 'success', '<span class="test-success">✓ Taller creado exitosamente</span>\nID: ' + testData.tallerId + '\n' + JSON.stringify(response.data, null, 2));
                } else {
                    updateTest('test-create-taller', 'failed', '<span class="test-error">✗ No se pudo crear el taller</span>\n' + JSON.stringify(response, null, 2));
                }
            } catch (error) {
                updateTest('test-create-taller', 'failed', '<span class="test-error">✗ Error en la petición</span>\n' + error.message);
            }
        }

        async function runTest5_2() {
            updateTest('test-list-talleres', 'running', 'Ejecutando...');
            try {
                const response = await CDCAPI.talleres.list();

                if (response.success && response.data) {
                    updateTest('test-list-talleres', 'success', '<span class="test-success">✓ Talleres listados exitosamente</span>\nCantidad: ' + (response.data.length || 0) + '\n' + JSON.stringify(response.data, null, 2));
                } else {
                    updateTest('test-list-talleres', 'failed', '<span class="test-error">✗ Error al listar talleres</span>\n' + JSON.stringify(response, null, 2));
                }
            } catch (error) {
                updateTest('test-list-talleres', 'failed', '<span class="test-error">✗ Error en la petición</span>\n' + error.message);
            }
        }

        // Run all tests
        $('#run-all-tests').on('click', async function() {
            $(this).prop('disabled', true).text('⏳ Ejecutando tests...');
            $('#progress-bar').show();
            $('#summary').show();

            // Reset stats
            stats = { total: 13, passed: 0, failed: 0 };
            updateSummary();

            const tests = [
                runTest1_1, runTest1_2, runTest1_3, runTest1_4,
                runTest2_1, runTest2_2, runTest2_3,
                runTest3_1, runTest3_2,
                runTest4_1, runTest4_2,
                runTest5_1, runTest5_2
            ];

            for (let i = 0; i < tests.length; i++) {
                await tests[i]();
                updateProgress(((i + 1) / tests.length) * 100);
                await new Promise(resolve => setTimeout(resolve, 500)); // Delay between tests
            }

            $(this).prop('disabled', false).text('▶️ Ejecutar Todos los Tests');

            // Show final summary
            const allPassed = stats.failed === 0;
            if (allPassed) {
                CDC.showNotification('✅ Todos los tests pasaron exitosamente!', 'success');
            } else {
                CDC.showNotification('⚠️ ' + stats.failed + ' test(s) fallaron. Revisa los detalles arriba.', 'warning');
            }
        });

        // Clear test data
        $('#clear-data').on('click', function() {
            if (confirm('¿Estás seguro de que quieres eliminar todos los datos de prueba?')) {
                CDC.showNotification('Funcionalidad de limpieza pendiente de implementación', 'info');
            }
        });
    });
    </script>

    <?php wp_footer(); ?>
</body>
</html>
