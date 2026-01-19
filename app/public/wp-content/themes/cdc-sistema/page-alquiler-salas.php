<?php
/**
 * Template Name: Alquiler de Salas
 *
 * @package CDC_Sistema
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<div class="cdc-alquiler-salas">
    <!-- Header -->
    <div class="cdc-page-header">
        <div class="cdc-page-header-actions">
            <button class="cdc-button cdc-button-primary" id="cdc-nueva-reserva">
                <span class="dashicons dashicons-plus"></span> Nueva reserva
            </button>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="cdc-card">
        <div class="cdc-card-body">
            <div class="cdc-filters-bar">
                <!-- Search -->
                <div class="cdc-search-wrapper" style="flex: 1;">
                    <input type="text"
                           id="cdc-search"
                           class="cdc-form-control"
                           placeholder="Buscar por solicitante o motivo...">
                </div>

                <!-- Filters -->
                <select id="cdc-filter-sala" class="cdc-form-control" style="max-width: 200px;">
                    <option value="">Todas las salas</option>
                    <option value="1">Sala 1</option>
                    <option value="2">Sala 2</option>
                </select>

                <input type="month"
                       id="cdc-filter-mes"
                       class="cdc-form-control"
                       value="<?php echo date('Y-m'); ?>"
                       style="max-width: 150px;">

                <button type="button" class="cdc-button cdc-button-primary" id="cdc-filter-btn">
                    Filtrar
                </button>
            </div>
        </div>
    </div>

    <!-- Results Card -->
    <div class="cdc-card">
        <div class="cdc-card-body">
            <div id="cdc-reservas-results">
                <p class="cdc-text-center cdc-text-muted">Cargando reservas...</p>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Load reservas
    function loadReservas() {
        const query = $('#cdc-search').val();
        const sala = $('#cdc-filter-sala').val();
        const mes = $('#cdc-filter-mes').val();
        const $results = $('#cdc-reservas-results');

        $results.html('<p class="cdc-text-center cdc-text-muted">Cargando...</p>');

        // TODO: Replace with actual API call
        setTimeout(function() {
            $results.html(`
                <div class="cdc-table-wrapper">
                    <table class="cdc-table">
                        <thead>
                            <tr>
                                <th>Alquiler de sala</th>
                                <th>Horario</th>
                                <th>Solicitante</th>
                                <th>Motivo</th>
                                <th>Importe</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="7" class="cdc-text-center cdc-text-muted">
                                    No hay reservas registradas para este período.<br>
                                    <small>Las reservas se mostrarán aquí una vez que se creen en el sistema.</small>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            `);
        }, 300);
    }

    // Filter button
    $('#cdc-filter-btn').on('click', loadReservas);

    // Nueva reserva button
    $('#cdc-nueva-reserva').on('click', function() {
        alert('Formulario de nueva reserva próximamente');
    });

    // Initial load
    loadReservas();
});
</script>

<?php get_footer(); ?>
