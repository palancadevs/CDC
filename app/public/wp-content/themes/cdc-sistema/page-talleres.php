<?php
/**
 * Template Name: Talleres
 *
 * @package CDC_Sistema
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<div class="cdc-talleres">
    <!-- Header -->
    <div class="cdc-page-header">
        <div class="cdc-page-header-actions">
            <button class="cdc-button cdc-button-primary" id="cdc-nuevo-taller">
                <span class="dashicons dashicons-plus"></span> Nuevo taller
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
                           id="cdc-talleres-search"
                           class="cdc-form-control"
                           placeholder="Buscar por nombre de taller...">
                </div>

                <!-- Filters -->
                <select id="cdc-filter-sala" class="cdc-form-control" style="max-width: 200px;">
                    <option value="">Todas las salas</option>
                    <option value="1">Sala 1</option>
                    <option value="2">Sala 2</option>
                </select>

                <select id="cdc-filter-estado" class="cdc-form-control" style="max-width: 150px;">
                    <option value="">Todos</option>
                    <option value="activo" selected>Activos</option>
                    <option value="inactivo">Inactivos</option>
                    <option value="finalizado">Finalizados</option>
                </select>

                <button type="button" class="cdc-button cdc-button-primary" id="cdc-filter-btn">
                    Filtrar
                </button>
            </div>
        </div>
    </div>

    <!-- Results Card -->
    <div class="cdc-card">
        <div class="cdc-card-body">
            <div id="cdc-talleres-results">
                <p class="cdc-text-center cdc-text-muted">Cargando talleres...</p>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Load talleres
    function loadTalleres() {
        const query = $('#cdc-talleres-search').val();
        const sala = $('#cdc-filter-sala').val();
        const estado = $('#cdc-filter-estado').val();
        const $results = $('#cdc-talleres-results');

        $results.html('<p class="cdc-text-center cdc-text-muted">Cargando...</p>');

        // TODO: Replace with actual API call
        setTimeout(function() {
            $results.html(`
                <div class="cdc-table-wrapper">
                    <table class="cdc-table">
                        <thead>
                            <tr>
                                <th>Taller</th>
                                <th>Sala</th>
                                <th>Tallerista</th>
                                <th>Días y horarios</th>
                                <th>Precio</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="7" class="cdc-text-center cdc-text-muted">
                                    No hay talleres registrados aún.<br>
                                    <small>Los talleres se mostrarán aquí una vez que se creen en el sistema.</small>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="cdc-pagination" style="display: none;">
                    <span class="cdc-text-muted">Mostrando 1-0 de 0</span>
                </div>
            `);
        }, 300);
    }

    // Filter button
    $('#cdc-filter-btn').on('click', loadTalleres);

    // Search on enter
    $('#cdc-talleres-search').on('keypress', function(e) {
        if (e.which === 13) {
            loadTalleres();
        }
    });

    // Nuevo taller button
    $('#cdc-nuevo-taller').on('click', function() {
        alert('Formulario de nuevo taller próximamente');
    });

    // Initial load
    loadTalleres();
});
</script>

<?php get_footer(); ?>
