<?php
/**
 * Template Name: Caja
 *
 * @package CDC_Sistema
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<div class="cdc-caja">
    <!-- Summary Card -->
    <div class="cdc-card">
        <div class="cdc-card-header">
            <h3 class="cdc-card-title">Resumen de caja</h3>
        </div>
        <div class="cdc-card-body">
            <div class="cdc-caja-summary">
                <div class="cdc-summary-item">
                    <span class="cdc-summary-label">Total Ingresos</span>
                    <span class="cdc-summary-value cdc-ingreso">$0.00</span>
                </div>
                <div class="cdc-summary-item">
                    <span class="cdc-summary-label">Total Egresos</span>
                    <span class="cdc-summary-value cdc-egreso">$0.00</span>
                </div>
                <div class="cdc-summary-item">
                    <span class="cdc-summary-label">Saldo Actual</span>
                    <span class="cdc-summary-value cdc-saldo">$0.00</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="cdc-card">
        <div class="cdc-card-body">
            <div class="cdc-filters-bar">
                <input type="date"
                       id="cdc-fecha-desde"
                       class="cdc-form-control"
                       value="<?php echo date('Y-m-01'); ?>"
                       style="max-width: 150px;">

                <span style="align-self: center;">hasta</span>

                <input type="date"
                       id="cdc-fecha-hasta"
                       class="cdc-form-control"
                       value="<?php echo date('Y-m-d'); ?>"
                       style="max-width: 150px;">

                <select id="cdc-filter-tipo" class="cdc-form-control" style="max-width: 150px;">
                    <option value="">Todos</option>
                    <option value="ingreso">Ingresos</option>
                    <option value="egreso">Egresos</option>
                </select>

                <button type="button" class="cdc-button cdc-button-primary" id="cdc-filter-btn">
                    Filtrar
                </button>
            </div>
        </div>
    </div>

    <!-- Movements Card -->
    <div class="cdc-card">
        <div class="cdc-card-header">
            <h3 class="cdc-card-title">Movimientos</h3>
        </div>
        <div class="cdc-card-body">
            <div id="cdc-movimientos-results">
                <p class="cdc-text-center cdc-text-muted">Cargando movimientos...</p>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    function loadMovimientos() {
        const desde = $('#cdc-fecha-desde').val();
        const hasta = $('#cdc-fecha-hasta').val();
        const tipo = $('#cdc-filter-tipo').val();

        const filters = {
            fecha_desde: desde,
            fecha_hasta: hasta
        };

        if (tipo) {
            filters.tipo = tipo;
        }

        // Load summary
        CDCAPI.caja.resumen(filters)
            .then(function(response) {
                if (response.success) {
                    const data = response.data;
                    $('.cdc-caja-summary .cdc-ingreso').text(CDC.formatCurrency(data.total_ingresos));
                    $('.cdc-caja-summary .cdc-egreso').text(CDC.formatCurrency(data.total_egresos));
                    $('.cdc-caja-summary .cdc-saldo').text(CDC.formatCurrency(data.saldo_actual));
                }
            })
            .catch(function(error) {
                console.error('Error loading summary:', error);
            });

        // Load movements
        const $results = $('#cdc-movimientos-results');
        $results.html('<p class="cdc-text-center cdc-text-muted">Cargando movimientos...</p>');

        CDCAPI.caja.movimientos(filters)
            .then(function(response) {
                if (response.success) {
                    if (response.data && response.data.length > 0) {
                        renderMovimientos(response.data);
                    } else {
                        $results.html('<p class="cdc-text-center cdc-text-muted">No hay movimientos registrados para este período.</p>');
                    }
                } else {
                    CDC.handleApiError(response, 'Load Movimientos');
                }
            })
            .catch(function(error) {
                $results.html('<p class="cdc-text-center" style="color: #d63638;">Error al cargar movimientos.</p>');
                CDC.handleApiError(error, 'Load Movimientos');
            });
    }

    function renderMovimientos(movimientos) {
        let html = '<div class="cdc-table-wrapper"><table class="cdc-table">';
        html += '<thead><tr>';
        html += '<th>Fecha/Hora</th><th>Tipo</th><th>Concepto</th><th>Monto</th><th>Saldo</th>';
        html += '</tr></thead><tbody>';

        movimientos.forEach(function(mov) {
            const fecha = new Date(mov.fecha_movimiento);
            const fechaStr = fecha.toLocaleDateString('es-AR');
            const horaStr = fecha.toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit' });

            let tipoClass = '';
            let tipoLabel = '';
            if (mov.tipo === 'ingreso') {
                tipoClass = 'cdc-ingreso';
                tipoLabel = 'Ingreso';
            } else if (mov.tipo === 'egreso') {
                tipoClass = 'cdc-egreso';
                tipoLabel = 'Egreso';
            } else {
                tipoLabel = mov.tipo.charAt(0).toUpperCase() + mov.tipo.slice(1);
            }

            html += `<tr>
                <td>${fechaStr} ${horaStr}</td>
                <td><span class="${tipoClass}">${tipoLabel}</span></td>
                <td>${mov.concepto}</td>
                <td class="${tipoClass}">${CDC.formatCurrency(mov.monto)}</td>
                <td>${CDC.formatCurrency(mov.saldo_nuevo)}</td>
            </tr>`;
        });

        html += '</tbody></table></div>';
        $('#cdc-movimientos-results').html(html);
    }

    $('#cdc-filter-btn').on('click', loadMovimientos);

    // Initial load
    loadMovimientos();
});
</script>

<?php get_footer(); ?>
