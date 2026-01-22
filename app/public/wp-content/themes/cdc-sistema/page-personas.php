<?php
/**
 * Template Name: Personas
 *
 * @package CDC_Sistema
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<div class="cdc-personas">
    <!-- Header -->
    <div class="cdc-page-header">
        <div class="cdc-page-header-actions">
            <a href="<?php echo home_url('/nuevo-socio'); ?>" class="cdc-button cdc-button-primary">
                <span class="dashicons dashicons-plus"></span> Nuevo socio
            </a>
            <a href="<?php echo home_url('/nuevo-cliente'); ?>" class="cdc-button cdc-button-secondary">
                <span class="dashicons dashicons-plus"></span> Nuevo cliente
            </a>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="cdc-card">
        <div class="cdc-card-body">
            <div class="cdc-filters-bar">
                <!-- Tabs -->
                <div class="cdc-tabs">
                    <button class="cdc-tab cdc-tab-active" data-filter="todos">Todos</button>
                    <button class="cdc-tab" data-filter="socios">Socios</button>
                    <button class="cdc-tab" data-filter="clientes">Clientes</button>
                </div>

                <!-- Advanced Filters -->
                <div class="cdc-advanced-filters">
                    <select id="cdc-filter-orden" class="cdc-form-control">
                        <option value="apellido">Ordenar por Apellido</option>
                        <option value="nombre">Ordenar por Nombre</option>
                        <option value="created_at">Ordenar por Fecha Alta</option>
                        <option value="dni">Ordenar por DNI</option>
                    </select>
                </div>

                <!-- Search -->
                <div class="cdc-search-wrapper">
                    <input type="text"
                           id="cdc-personas-search"
                           class="cdc-form-control"
                           placeholder="Buscar por Nombre, Apellido o DNI...">
                    <button type="button" class="cdc-button cdc-button-primary" id="cdc-search-btn">
                        Buscar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Card -->
    <div class="cdc-card">
        <div class="cdc-card-body">
            <div id="cdc-personas-results">
                <p class="cdc-text-center cdc-text-muted">Cargando personas...</p>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    let currentFilter = 'todos';

    // Tab switching
    $('.cdc-tab').on('click', function() {
        $('.cdc-tab').removeClass('cdc-tab-active');
        $(this).addClass('cdc-tab-active');
        currentFilter = $(this).data('filter');
        loadPersonas();
    });

    // Advanced filters change
    $('#cdc-filter-orden').on('change', loadPersonas);

    // Search
    $('#cdc-search-btn').on('click', loadPersonas);
    $('#cdc-personas-search').on('keypress', function(e) {
        if (e.which === 13) {
            loadPersonas();
        }
    });

    // Load personas function
    function loadPersonas() {
        const query = $('#cdc-personas-search').val().trim();
        const orden = $('#cdc-filter-orden').val();
        const $results = $('#cdc-personas-results');

        $results.html('<p class="cdc-text-center cdc-text-muted">Cargando...</p>');

        const filters = {};

        // Filter by tipo (convert plural to singular: socios -> socio)
        if (currentFilter === 'socios') {
            filters.tipo = 'socio';
        } else if (currentFilter === 'clientes') {
            filters.tipo = 'cliente';
        }

        // Order by
        if (orden) {
            filters.orderby = orden;
            filters.order = 'ASC';
        }

        // Search query (only if 3+ characters)
        if (query && query.length >= 3) {
            filters.query = query;
        }

        CDCAPI.personas.list(filters)
            .then(function(response) {
                if (response.success) {
                    if (response.data && response.data.length > 0) {
                        $results.html(CDC.renderPersonasTable(response.data));
                    } else {
                        $results.html('<p class="cdc-text-center cdc-text-muted">No se encontraron personas.</p>');
                    }
                } else {
                    CDC.handleApiError(response, 'Load Personas');
                }
            })
            .catch(function(error) {
                $results.html('<p class="cdc-text-center" style="color: #d63638;">Error al cargar personas.</p>');
                CDC.handleApiError(error, 'Load Personas');
            });
    }

    // Initial load
    loadPersonas();
});
</script>

<style>
.cdc-advanced-filters {
    display: flex;
    gap: 10px;
    margin: 15px 0;
}

.cdc-advanced-filters select {
    flex: 1;
    max-width: 200px;
}

@media (max-width: 768px) {
    .cdc-advanced-filters {
        flex-direction: column;
    }

    .cdc-advanced-filters select {
        max-width: 100%;
    }
}
</style>

<?php get_footer(); ?>
