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

    // Search
    $('#cdc-search-btn').on('click', loadPersonas);
    $('#cdc-personas-search').on('keypress', function(e) {
        if (e.which === 13) {
            loadPersonas();
        }
    });

    // Load personas function
    function loadPersonas() {
        const query = $('#cdc-personas-search').val();
        const $results = $('#cdc-personas-results');

        $results.html('<p class="cdc-text-center cdc-text-muted">Cargando...</p>');

        // TODO: Replace with actual API call
        setTimeout(function() {
            $results.html(`
                <div class="cdc-table-wrapper">
                    <table class="cdc-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>DNI</th>
                                <th>Teléfono</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="cdc-text-center cdc-text-muted">
                                    No hay personas registradas aún.<br>
                                    <small>La lista se mostrará aquí una vez que se registren personas en el sistema.</small>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            `);
        }, 300);

        /* FUTURE IMPLEMENTATION:
        $.ajax({
            url: cdcData.apiUrl + 'personas',
            method: 'GET',
            data: {
                tipo: currentFilter === 'todos' ? '' : currentFilter,
                query: query
            },
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', cdcData.nonce);
            },
            success: function(response) {
                displayPersonas(response.data);
            },
            error: function() {
                $results.html('<p class="cdc-text-center" style="color: #d63638;">Error al cargar personas.</p>');
            }
        });
        */
    }

    // Initial load
    loadPersonas();
});
</script>

<?php get_footer(); ?>
