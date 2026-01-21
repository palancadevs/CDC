<?php
/**
 * Template Name: Salas
 *
 * @package CDC_Sistema
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<div class="cdc-salas">
    <!-- Header -->
    <div class="cdc-page-header">
        <h1 class="cdc-page-title">Salas</h1>
        <div class="cdc-page-header-actions">
            <button class="cdc-button cdc-button-primary" id="cdc-nueva-sala">
                <span class="dashicons dashicons-plus"></span> Nueva sala
            </button>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="cdc-card">
        <div class="cdc-card-body">
            <div class="cdc-filters-bar">
                <div class="cdc-search-wrapper" style="flex: 1;">
                    <input type="text"
                           id="cdc-salas-search"
                           class="cdc-form-control"
                           placeholder="Buscar por nombre de sala...">
                </div>

                <select id="cdc-filter-estado" class="cdc-form-control" style="max-width: 150px;">
                    <option value="">Todos</option>
                    <option value="disponible" selected>Disponibles</option>
                    <option value="mantenimiento">En mantenimiento</option>
                    <option value="inactivo">Inactivos</option>
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
            <div id="cdc-salas-results">
                <p class="cdc-text-center cdc-text-muted">Cargando salas...</p>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Render salas table
    function renderSalasTable(salas) {
        if (!salas || salas.length === 0) {
            return `<div class="cdc-table-wrapper"><table class="cdc-table">
                <thead><tr><th>Sala</th><th>Capacidad</th><th>Precio/hora</th><th>Estado</th><th>Acción</th></tr></thead>
                <tbody><tr><td colspan="5" class="cdc-text-center cdc-text-muted">No hay salas registradas.</td></tr></tbody>
            </table></div>`;
        }

        let html = '<div class="cdc-table-wrapper"><table class="cdc-table"><thead><tr><th>Sala</th><th>Capacidad</th><th>Equipamiento</th><th>Precio/hora</th><th>Estado</th><th>Acción</th></tr></thead><tbody>';

        salas.forEach(s => {
            const capacidad = s.capacidad ? `${s.capacidad} personas` : '-';
            const precio = s.precio_hora ? `$${parseFloat(s.precio_hora).toFixed(2)}` : '-';
            const equipamiento = s.equipamiento || '-';

            html += `<tr>
                <td><strong>${s.nombre}</strong></td>
                <td>${capacidad}</td>
                <td>${equipamiento}</td>
                <td>${precio}</td>
                <td><span class="cdc-badge">${s.estado}</span></td>
                <td>
                    <a href="${cdcData.homeUrl}/sala?id=${s.id}" class="cdc-button cdc-button-small">Ver</a>
                    ${s.estado === 'disponible' ?
                        `<button class="cdc-button cdc-button-small cdc-button-primary cdc-reservar-btn" data-sala-id="${s.id}" data-sala-nombre="${s.nombre}">Reservar</button>` :
                        ''}
                </td>
            </tr>`;
        });

        html += '</tbody></table></div>';
        return html;
    }

    // Load salas
    function loadSalas() {
        const query = $('#cdc-salas-search').val();
        const estado = $('#cdc-filter-estado').val();
        const $results = $('#cdc-salas-results');

        $results.html('<p class="cdc-text-center cdc-text-muted">Cargando...</p>');

        const filters = {};
        if (estado) filters.estado = estado;
        if (query) filters.query = query;

        CDCAPI.salas.list(filters)
            .then(function(response) {
                if (response.success) {
                    $results.html(renderSalasTable(response.data));
                } else {
                    CDC.handleApiError(response, 'Load Salas');
                }
            })
            .catch(function(error) {
                $results.html('<p class="cdc-text-center" style="color: #d63638;">Error al cargar salas.</p>');
                CDC.handleApiError(error, 'Load Salas');
            });
    }

    // Filter button
    $('#cdc-filter-btn').on('click', loadSalas);

    // Search on enter
    $('#cdc-salas-search').on('keypress', function(e) {
        if (e.which === 13) {
            loadSalas();
        }
    });

    // Nueva sala button
    $('#cdc-nueva-sala').on('click', function() {
        window.location.href = cdcData.homeUrl + '/nueva-sala';
    });

    // Reservar button handler (delegated event)
    $(document).on('click', '.cdc-reservar-btn', function() {
        const salaId = $(this).data('sala-id');
        const salaNombre = $(this).data('sala-nombre');
        CDC.showNotification('Funcionalidad de reserva en desarrollo', 'info');
    });

    // Initial load
    loadSalas();
});
</script>

<?php get_footer(); ?>
