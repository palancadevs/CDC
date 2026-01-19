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

        $('#cdc-movimientos-results').html(`
            <div class="cdc-table-wrapper">
                <table class="cdc-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Tipo</th>
                            <th>Concepto</th>
                            <th>Monto</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="6" class="cdc-text-center cdc-text-muted">
                                No hay movimientos registrados para este período.<br>
                                <small>Los movimientos se mostrarán aquí cuando se registren cobros y gastos.</small>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        `);
    }

    $('#cdc-filter-btn').on('click', loadMovimientos);

    loadMovimientos();
});
</script>

<?php get_footer(); ?>
