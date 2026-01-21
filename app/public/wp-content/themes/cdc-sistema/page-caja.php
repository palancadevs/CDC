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
    <!-- Header -->
    <div class="cdc-page-header">
        <h1 class="cdc-page-title">Caja</h1>
        <div class="cdc-page-header-actions">
            <a href="<?php echo esc_url(home_url('/registrar-gasto')); ?>" class="cdc-button cdc-button-primary">
                <span class="dashicons dashicons-plus"></span> Registrar gasto
            </a>
        </div>
    </div>

    <!-- Summary Card -->
    <div class="cdc-card">
        <div class="cdc-card-body">
            <div class="cdc-caja-summary-grid">
                <div class="cdc-summary-card cdc-summary-ingresos">
                    <div class="cdc-summary-icon">
                        <span class="dashicons dashicons-arrow-down-alt"></span>
                    </div>
                    <div class="cdc-summary-content">
                        <span class="cdc-summary-label">Total Ingresos</span>
                        <span class="cdc-summary-value cdc-ingreso">$0.00</span>
                    </div>
                </div>
                <div class="cdc-summary-card cdc-summary-egresos">
                    <div class="cdc-summary-icon">
                        <span class="dashicons dashicons-arrow-up-alt"></span>
                    </div>
                    <div class="cdc-summary-content">
                        <span class="cdc-summary-label">Total Egresos</span>
                        <span class="cdc-summary-value cdc-egreso">$0.00</span>
                    </div>
                </div>
                <div class="cdc-summary-card cdc-summary-balance">
                    <div class="cdc-summary-icon">
                        <span class="dashicons dashicons-chart-line"></span>
                    </div>
                    <div class="cdc-summary-content">
                        <span class="cdc-summary-label">Saldo Actual</span>
                        <span class="cdc-summary-value cdc-saldo">$0.00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="cdc-card">
        <div class="cdc-card-body">
            <div class="cdc-filters-bar">
                <div class="cdc-form-group" style="margin-bottom: 0;">
                    <label for="cdc-fecha-desde" style="font-size: 12px; margin-bottom: 4px;">Desde</label>
                    <input type="date"
                           id="cdc-fecha-desde"
                           class="cdc-form-control"
                           value="<?php echo date('Y-m-d'); ?>"
                           style="max-width: 160px;">
                </div>

                <div class="cdc-form-group" style="margin-bottom: 0;">
                    <label for="cdc-fecha-hasta" style="font-size: 12px; margin-bottom: 4px;">Hasta</label>
                    <input type="date"
                           id="cdc-fecha-hasta"
                           class="cdc-form-control"
                           value="<?php echo date('Y-m-d'); ?>"
                           style="max-width: 160px;">
                </div>

                <div class="cdc-form-group" style="margin-bottom: 0;">
                    <label for="cdc-filter-tipo" style="font-size: 12px; margin-bottom: 4px;">Tipo</label>
                    <select id="cdc-filter-tipo" class="cdc-form-control" style="max-width: 150px;">
                        <option value="">Todos</option>
                        <option value="ingreso">Ingresos</option>
                        <option value="egreso">Egresos</option>
                    </select>
                </div>

                <div style="align-self: flex-end;">
                    <button type="button" class="cdc-button cdc-button-primary" id="cdc-filter-btn">
                        Filtrar
                    </button>
                    <button type="button" class="cdc-button cdc-button-secondary" id="cdc-today-btn">
                        Hoy
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Movements Card -->
    <div class="cdc-card">
        <div class="cdc-card-header">
            <h3 class="cdc-card-title">Movimientos</h3>
            <span id="cdc-movements-count" class="cdc-text-muted"></span>
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
                        $('#cdc-movements-count').text(`${response.data.length} movimiento${response.data.length !== 1 ? 's' : ''}`);
                        renderMovimientos(response.data);
                    } else {
                        $('#cdc-movements-count').text('0 movimientos');
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

    // Reset to today
    $('#cdc-today-btn').on('click', function() {
        const today = '<?php echo date('Y-m-d'); ?>';
        $('#cdc-fecha-desde').val(today);
        $('#cdc-fecha-hasta').val(today);
        $('#cdc-filter-tipo').val('');
        loadMovimientos();
    });

    // Auto-reload when dates change
    $('#cdc-fecha-desde, #cdc-fecha-hasta').on('change', function() {
        loadMovimientos();
    });

    // Initial load
    loadMovimientos();
});
</script>

<style>
.cdc-caja-summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.cdc-summary-card {
    display: flex;
    align-items: center;
    padding: 20px;
    border-radius: 8px;
    background: #f9f9f9;
}

.cdc-summary-ingresos {
    border-left: 4px solid #28a745;
}

.cdc-summary-egresos {
    border-left: 4px solid #dc3545;
}

.cdc-summary-balance {
    border-left: 4px solid #007bff;
}

.cdc-summary-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 16px;
    font-size: 24px;
}

.cdc-summary-ingresos .cdc-summary-icon {
    background: rgba(40, 167, 69, 0.1);
    color: #28a745;
}

.cdc-summary-egresos .cdc-summary-icon {
    background: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}

.cdc-summary-balance .cdc-summary-icon {
    background: rgba(0, 123, 255, 0.1);
    color: #007bff;
}

.cdc-summary-content {
    display: flex;
    flex-direction: column;
}

.cdc-summary-label {
    font-size: 12px;
    font-weight: 600;
    color: #666;
    text-transform: uppercase;
    margin-bottom: 4px;
}

.cdc-summary-value {
    font-size: 24px;
    font-weight: 700;
}

.cdc-filters-bar {
    display: flex;
    gap: 16px;
    align-items: flex-end;
    flex-wrap: wrap;
}
</style>

<?php get_footer(); ?>
