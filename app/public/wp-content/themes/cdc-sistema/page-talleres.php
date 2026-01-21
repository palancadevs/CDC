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
    // Render talleres table
    function renderTalleresTable(talleres) {
        if (!talleres || talleres.length === 0) {
            return `<div class="cdc-table-wrapper"><table class="cdc-table">
                <thead><tr><th>Taller</th><th>Sala</th><th>Tallerista</th><th>Días y horarios</th><th>Precio</th><th>Cupo</th><th>Estado</th><th>Acción</th></tr></thead>
                <tbody><tr><td colspan="8" class="cdc-text-center cdc-text-muted">No hay talleres registrados.</td></tr></tbody>
            </table></div>`;
        }

        let html = '<div class="cdc-table-wrapper"><table class="cdc-table"><thead><tr><th>Taller</th><th>Sala</th><th>Tallerista</th><th>Días y horarios</th><th>Precio</th><th>Cupo</th><th>Estado</th><th>Acción</th></tr></thead><tbody>';

        talleres.forEach(t => {
            const horario_completo = (t.dia_semana || '') + ' ' + (t.horario || '');
            const cupo_text = t.cupo_maximo ? `${t.inscriptos}/${t.cupo_maximo}` : t.inscriptos;
            const cupo_disponible = !t.cupo_maximo || parseInt(t.inscriptos) < parseInt(t.cupo_maximo);

            html += `<tr>
                <td><strong>${t.nombre}</strong></td>
                <td>${t.sala_id || '-'}</td>
                <td>${t.profesor || '-'}</td>
                <td>${horario_completo.trim() || '-'}</td>
                <td>$${parseFloat(t.precio_mensual || 0).toFixed(2)}</td>
                <td>${cupo_text}</td>
                <td><span class="cdc-badge">${t.estado}</span></td>
                <td>
                    <a href="${cdcData.homeUrl}/taller?id=${t.id}" class="cdc-button cdc-button-small">Ver</a>
                    ${t.estado === 'activo' && cupo_disponible ?
                        `<button class="cdc-button cdc-button-small cdc-button-primary cdc-inscribir-btn" data-taller-id="${t.id}" data-taller-nombre="${t.nombre}" data-monto="${t.precio_mensual}">Inscribir</button>` :
                        ''}
                </td>
            </tr>`;
        });

        html += '</tbody></table></div>';
        return html;
    }

    // Load talleres
    function loadTalleres() {
        const query = $('#cdc-talleres-search').val();
        const sala = $('#cdc-filter-sala').val();
        const estado = $('#cdc-filter-estado').val();
        const $results = $('#cdc-talleres-results');

        $results.html('<p class="cdc-text-center cdc-text-muted">Cargando...</p>');

        const filters = {};
        if (sala) filters.sala_id = sala;
        if (estado && estado !== 'todos') filters.estado = estado;
        if (query) filters.query = query;

        CDCAPI.talleres.list(filters)
            .then(function(response) {
                if (response.success) {
                    $results.html(renderTalleresTable(response.data));
                } else {
                    CDC.handleApiError(response, 'Load Talleres');
                }
            })
            .catch(function(error) {
                $results.html('<p class="cdc-text-center" style="color: #d63638;">Error al cargar talleres.</p>');
                CDC.handleApiError(error, 'Load Talleres');
            });
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
        window.location.href = cdcData.homeUrl + '/nuevo-taller';
    });

    // Initial load
    loadTalleres();

    // Inscribir button handler (delegated event)
    $(document).on('click', '.cdc-inscribir-btn', function() {
        const tallerId = $(this).data('taller-id');
        const tallerNombre = $(this).data('taller-nombre');
        const monto = $(this).data('monto');

        openInscribirModal(tallerId, tallerNombre, monto);
    });

    // Open inscribir modal
    function openInscribirModal(tallerId, tallerNombre, monto) {
        $('#cdc-modal-taller-nombre').text(tallerNombre);
        $('#cdc-modal-taller-id').val(tallerId);
        $('#cdc-modal-monto').val(monto);
        $('#cdc-inscribir-modal').show();
        $('#cdc-modal-person-search').val('').focus();
        $('#cdc-modal-person-results').empty();
        $('#cdc-modal-selected-person').hide();
    }

    // Close modal
    $('#cdc-modal-close, #cdc-modal-cancel').on('click', function() {
        $('#cdc-inscribir-modal').hide();
    });

    // Search person in modal
    $('#cdc-modal-search-btn').on('click', searchPersonModal);
    $('#cdc-modal-person-search').on('keypress', function(e) {
        if (e.which === 13) {
            searchPersonModal();
        }
    });

    function searchPersonModal() {
        const query = $('#cdc-modal-person-search').val();
        const $results = $('#cdc-modal-person-results');

        if (query.length < 3) {
            CDC.showNotification('Por favor ingrese al menos 3 caracteres', 'warning');
            return;
        }

        $results.html('<p class="cdc-text-muted">Buscando...</p>');

        CDCAPI.personas.search(query)
            .then(function(response) {
                if (response.success && response.data.length > 0) {
                    $results.html(CDC.renderPersonSearchResults(response.data, selectPersonModal));
                } else {
                    $results.html('<p class="cdc-text-muted">No se encontraron personas.</p>');
                }
            })
            .catch(function(error) {
                CDC.handleApiError(error, 'Person Search');
            });
    }

    let selectedPersonModal = null;

    function selectPersonModal(person) {
        selectedPersonModal = person;
        $('#cdc-modal-selected-person-info').html(
            `<strong>${person.nombre} ${person.apellido}</strong> - DNI: ${person.dni}`
        );
        $('#cdc-modal-selected-person').show();
        $('#cdc-modal-person-results').hide();
    }

    // Submit inscription
    $('#cdc-modal-form').on('submit', function(e) {
        e.preventDefault();

        if (!selectedPersonModal) {
            CDC.showNotification('Seleccione una persona', 'warning');
            return;
        }

        const tallerId = $('#cdc-modal-taller-id').val();
        const monto = $('#cdc-modal-monto').val();
        const notas = $('#cdc-modal-notas').val();

        const $submitBtn = $(this).find('button[type="submit"]');
        CDC.showLoadingButton($submitBtn, true);

        const data = {
            persona_id: selectedPersonModal.id,
            monto_mensual: parseFloat(monto),
            notas: notas
        };

        CDCAPI.talleres.inscribir(tallerId, data)
            .then(function(response) {
                CDC.showLoadingButton($submitBtn, false);
                if (response.success) {
                    CDC.showNotification('Persona inscrita correctamente', 'success');
                    $('#cdc-inscribir-modal').hide();
                    loadTalleres(); // Reload talleres to update cupo
                } else {
                    CDC.handleApiError(response, 'Inscripción');
                }
            })
            .catch(function(error) {
                CDC.showLoadingButton($submitBtn, false);
                CDC.handleApiError(error, 'Inscripción');
            });
    });
});
</script>

<!-- Inscribir Modal -->
<div id="cdc-inscribir-modal" class="cdc-modal" style="display: none;">
    <div class="cdc-modal-content">
        <div class="cdc-modal-header">
            <h3>Inscribir a Taller: <span id="cdc-modal-taller-nombre"></span></h3>
            <button class="cdc-modal-close" id="cdc-modal-close">&times;</button>
        </div>
        <div class="cdc-modal-body">
            <input type="hidden" id="cdc-modal-taller-id">

            <div class="cdc-form-group">
                <label>Buscar persona</label>
                <div class="cdc-search-wrapper">
                    <input type="text" id="cdc-modal-person-search" class="cdc-form-control" placeholder="Nombre o DNI...">
                    <button type="button" class="cdc-button cdc-button-primary" id="cdc-modal-search-btn">Buscar</button>
                </div>
                <div id="cdc-modal-person-results" style="margin-top: 10px;"></div>
                <div id="cdc-modal-selected-person" style="display: none; margin-top: 10px; padding: 10px; background: #f0f6fc; border-radius: 4px;">
                    <p><strong>Persona seleccionada:</strong></p>
                    <p id="cdc-modal-selected-person-info"></p>
                </div>
            </div>

            <form id="cdc-modal-form">
                <div class="cdc-form-group">
                    <label for="cdc-modal-monto">Monto mensual *</label>
                    <input type="number" id="cdc-modal-monto" class="cdc-form-control" step="0.01" required>
                </div>

                <div class="cdc-form-group">
                    <label for="cdc-modal-notas">Notas</label>
                    <textarea id="cdc-modal-notas" class="cdc-form-control" rows="2"></textarea>
                </div>

                <div class="cdc-form-actions">
                    <button type="submit" class="cdc-button cdc-button-primary">Inscribir</button>
                    <button type="button" class="cdc-button cdc-button-secondary" id="cdc-modal-cancel">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.cdc-modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.cdc-modal-content {
    background-color: #fff;
    margin: 5% auto;
    padding: 0;
    border-radius: 8px;
    width: 90%;
    max-width: 600px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.cdc-modal-header {
    padding: 20px;
    border-bottom: 1px solid #e0e0e0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.cdc-modal-header h3 {
    margin: 0;
    font-size: 18px;
}

.cdc-modal-close {
    background: none;
    border: none;
    font-size: 28px;
    font-weight: bold;
    color: #999;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    line-height: 1;
}

.cdc-modal-close:hover {
    color: #333;
}

.cdc-modal-body {
    padding: 20px;
}
</style>

<?php get_footer(); ?>
