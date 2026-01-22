<?php
/**
 * Template Name: Taller (Ficha)
 *
 * @package CDC_Sistema
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<div class="cdc-taller-ficha">
    <!-- Loading State -->
    <div id="cdc-loading" class="cdc-text-center" style="padding: 40px;">
        <p class="cdc-text-muted">Cargando taller...</p>
    </div>

    <!-- Taller Content (hidden initially) -->
    <div id="cdc-taller-content" style="display: none;">
        <!-- Header with Actions -->
        <div class="cdc-page-header">
            <div>
                <h1 id="cdc-taller-nombre" class="cdc-page-title"></h1>
                <p id="cdc-taller-subtitle" class="cdc-text-muted"></p>
            </div>
            <div class="cdc-page-header-actions">
                <button class="cdc-button cdc-button-secondary" id="cdc-edit-taller">
                    <span class="dashicons dashicons-edit"></span> Editar
                </button>
                <button class="cdc-button cdc-button-primary" id="cdc-inscribir-taller">
                    <span class="dashicons dashicons-plus"></span> Inscribir persona
                </button>
            </div>
        </div>

        <!-- Taller Info Card -->
        <div class="cdc-card">
            <div class="cdc-card-header">
                <h3>Información del Taller</h3>
            </div>
            <div class="cdc-card-body">
                <div class="cdc-info-grid">
                    <div class="cdc-info-item">
                        <span class="cdc-info-label">Profesor/Tallerista:</span>
                        <span id="cdc-info-profesor" class="cdc-info-value"></span>
                    </div>
                    <div class="cdc-info-item">
                        <span class="cdc-info-label">Día y horario:</span>
                        <span id="cdc-info-horario" class="cdc-info-value"></span>
                    </div>
                    <div class="cdc-info-item">
                        <span class="cdc-info-label">Sala:</span>
                        <span id="cdc-info-sala" class="cdc-info-value"></span>
                    </div>
                    <div class="cdc-info-item">
                        <span class="cdc-info-label">Precio mensual:</span>
                        <span id="cdc-info-precio" class="cdc-info-value"></span>
                    </div>
                    <div class="cdc-info-item">
                        <span class="cdc-info-label">Cupo:</span>
                        <span id="cdc-info-cupo" class="cdc-info-value"></span>
                    </div>
                    <div class="cdc-info-item">
                        <span class="cdc-info-label">Estado:</span>
                        <span id="cdc-info-estado" class="cdc-info-value"></span>
                    </div>
                </div>
                <div class="cdc-info-item" style="margin-top: 20px;">
                    <span class="cdc-info-label">Descripción:</span>
                    <p id="cdc-info-descripcion" class="cdc-info-value" style="margin-top: 8px;"></p>
                </div>
                <div class="cdc-info-item" id="cdc-notas-container" style="margin-top: 20px; display: none;">
                    <span class="cdc-info-label">Notas:</span>
                    <p id="cdc-info-notas" class="cdc-info-value" style="margin-top: 8px; color: #666;"></p>
                </div>
            </div>
        </div>

        <!-- Inscripciones Card -->
        <div class="cdc-card">
            <div class="cdc-card-header">
                <h3>Personas Inscritas</h3>
                <div>
                    <select id="cdc-filter-estado-inscripcion" class="cdc-form-control" style="max-width: 150px;">
                        <option value="">Todos</option>
                        <option value="activo" selected>Activos</option>
                        <option value="inactivo">Inactivos</option>
                        <option value="finalizado">Finalizados</option>
                    </select>
                </div>
            </div>
            <div class="cdc-card-body">
                <div id="cdc-inscripciones-list">
                    <p class="cdc-text-center cdc-text-muted">Cargando inscripciones...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Get taller ID from URL
    const urlParams = new URLSearchParams(window.location.search);
    const tallerId = urlParams.get('id');

    if (!tallerId) {
        $('#cdc-loading').html('<p style="color: #d63638;">ID de taller no proporcionado</p>');
        return;
    }

    let currentTaller = null;

    // Load taller data
    function loadTaller() {
        CDCAPI.talleres.get(tallerId)
            .then(function(response) {
                if (response.success && response.data) {
                    currentTaller = response.data;
                    renderTaller(response.data);
                    loadInscripciones();
                    $('#cdc-loading').hide();
                    $('#cdc-taller-content').show();
                } else {
                    $('#cdc-loading').html('<p style="color: #d63638;">Taller no encontrado</p>');
                }
            })
            .catch(function(error) {
                $('#cdc-loading').html('<p style="color: #d63638;">Error al cargar taller</p>');
                CDC.handleApiError(error, 'Load Taller');
            });
    }

    // Render taller info
    function renderTaller(taller) {
        $('#cdc-taller-nombre').text(taller.nombre);

        const horario_completo = (taller.dia_semana || '') + ' ' + (taller.horario || '');
        $('#cdc-taller-subtitle').text(horario_completo.trim() || 'Sin horario definido');

        $('#cdc-info-profesor').text(taller.profesor || '-');
        $('#cdc-info-horario').text(horario_completo.trim() || '-');
        $('#cdc-info-sala').text(taller.sala_id ? `Sala ${taller.sala_id}` : '-');
        $('#cdc-info-precio').text('$' + parseFloat(taller.precio_mensual || 0).toFixed(2));

        const cupoText = taller.cupo_maximo
            ? `${taller.inscriptos}/${taller.cupo_maximo}`
            : `${taller.inscriptos} (sin límite)`;
        $('#cdc-info-cupo').text(cupoText);

        const estadoBadge = `<span class="cdc-badge">${taller.estado}</span>`;
        $('#cdc-info-estado').html(estadoBadge);

        $('#cdc-info-descripcion').text(taller.descripcion || 'Sin descripción');

        if (taller.notas) {
            $('#cdc-info-notas').text(taller.notas);
            $('#cdc-notas-container').show();
        }
    }

    // Load inscripciones
    function loadInscripciones() {
        const estado = $('#cdc-filter-estado-inscripcion').val();
        const $list = $('#cdc-inscripciones-list');

        $list.html('<p class="cdc-text-center cdc-text-muted">Cargando...</p>');

        CDCAPI.talleres.inscripciones(tallerId, estado)
            .then(function(response) {
                if (response.success) {
                    renderInscripciones(response.data);
                } else {
                    $list.html('<p class="cdc-text-center" style="color: #d63638;">Error al cargar inscripciones</p>');
                }
            })
            .catch(function(error) {
                $list.html('<p class="cdc-text-center" style="color: #d63638;">Error al cargar inscripciones</p>');
                CDC.handleApiError(error, 'Load Inscripciones');
            });
    }

    // Render inscripciones table
    function renderInscripciones(inscripciones) {
        const $list = $('#cdc-inscripciones-list');

        if (!inscripciones || inscripciones.length === 0) {
            $list.html('<p class="cdc-text-center cdc-text-muted">No hay inscripciones.</p>');
            return;
        }

        let html = '<div class="cdc-table-wrapper"><table class="cdc-table">';
        html += '<thead><tr>';
        html += '<th>Persona</th>';
        html += '<th>DNI</th>';
        html += '<th>Fecha inscripción</th>';
        html += '<th>Monto mensual</th>';
        html += '<th>Estado</th>';
        html += '<th>Acciones</th>';
        html += '</tr></thead><tbody>';

        inscripciones.forEach(function(ins) {
            const fechaInscripcion = ins.fecha_inscripcion ? new Date(ins.fecha_inscripcion + 'T00:00:00').toLocaleDateString('es-AR') : '-';

            html += '<tr>';
            html += `<td><strong>${ins.persona_nombre || ''} ${ins.persona_apellido || ''}</strong></td>`;
            html += `<td>${ins.persona_dni || '-'}</td>`;
            html += `<td>${fechaInscripcion}</td>`;
            html += `<td>$${parseFloat(ins.monto_mensual || 0).toFixed(2)}</td>`;
            html += `<td><span class="cdc-badge">${ins.estado}</span></td>`;
            html += '<td>';
            html += `<a href="${cdcData.homeUrl}/persona?id=${ins.persona_id}" class="cdc-button cdc-button-small">Ver persona</a>`;
            if (ins.estado === 'activo') {
                html += ` <button class="cdc-button cdc-button-small cdc-button-danger cdc-dar-baja-btn" data-inscripcion-id="${ins.id}" data-persona-nombre="${ins.persona_nombre} ${ins.persona_apellido}">Dar de baja</button>`;
            }
            html += '</td>';
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        $list.html(html);
    }

    // Filter inscripciones by estado
    $('#cdc-filter-estado-inscripcion').on('change', loadInscripciones);

    // Edit taller button
    $('#cdc-edit-taller').on('click', function() {
        window.location.href = cdcData.homeUrl + '/editar-taller?id=' + tallerId;
    });

    // Inscribir button
    $('#cdc-inscribir-taller').on('click', function() {
        if (!currentTaller) return;

        // Check if taller is active
        if (currentTaller.estado !== 'activo') {
            CDC.showNotification('El taller no está activo', 'warning');
            return;
        }

        // Check if has available spots
        const cupo_disponible = !currentTaller.cupo_maximo ||
            parseInt(currentTaller.inscriptos) < parseInt(currentTaller.cupo_maximo);

        if (!cupo_disponible) {
            CDC.showNotification('El taller no tiene cupos disponibles', 'warning');
            return;
        }

        openInscribirModal(currentTaller.id, currentTaller.nombre, currentTaller.precio_mensual);
    });

    // Dar de baja button handler (delegated)
    $(document).on('click', '.cdc-dar-baja-btn', function() {
        const inscripcionId = $(this).data('inscripcion-id');
        const personaNombre = $(this).data('persona-nombre');

        if (confirm(`¿Confirma dar de baja a ${personaNombre} de este taller?`)) {
            darDeBajaInscripcion(inscripcionId);
        }
    });

    // Dar de baja inscripcion
    function darDeBajaInscripcion(inscripcionId) {
        CDCAPI.talleres.darDeBajaInscripcion(tallerId, inscripcionId, {
            fecha_baja: new Date().toISOString().split('T')[0]
        })
            .then(function(response) {
                if (response.success) {
                    CDC.showNotification('Inscripción dada de baja correctamente', 'success');
                    loadTaller(); // Reload to update counter
                    loadInscripciones(); // Reload inscripciones list
                } else {
                    CDC.handleApiError(response, 'Dar de baja');
                }
            })
            .catch(function(error) {
                CDC.handleApiError(error, 'Dar de baja');
            });
    }

    // Open inscribir modal (reuse modal from page-talleres.php)
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
                    loadTaller(); // Reload to update counter
                    loadInscripciones(); // Reload inscripciones list
                } else {
                    CDC.handleApiError(response, 'Inscripción');
                }
            })
            .catch(function(error) {
                CDC.showLoadingButton($submitBtn, false);
                CDC.handleApiError(error, 'Inscripción');
            });
    });

    // Initial load
    loadTaller();
});
</script>

<!-- Inscribir Modal (reuse from page-talleres.php) -->
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
.cdc-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.cdc-info-item {
    display: flex;
    flex-direction: column;
}

.cdc-info-label {
    font-size: 12px;
    font-weight: 600;
    color: #666;
    text-transform: uppercase;
    margin-bottom: 4px;
}

.cdc-info-value {
    font-size: 14px;
    color: #1a1a1a;
}

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
