<?php
/**
 * Template Name: Sala (Ficha)
 *
 * @package CDC_Sistema
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<div class="cdc-sala-ficha">
    <!-- Loading State -->
    <div id="cdc-loading" class="cdc-text-center" style="padding: 40px;">
        <p class="cdc-text-muted">Cargando sala...</p>
    </div>

    <!-- Sala Content (hidden initially) -->
    <div id="cdc-sala-content" style="display: none;">
        <!-- Header with Actions -->
        <div class="cdc-page-header">
            <div>
                <h1 id="cdc-sala-nombre" class="cdc-page-title"></h1>
                <span id="cdc-sala-estado-badge" class="cdc-badge"></span>
            </div>
            <div class="cdc-page-header-actions">
                <button class="cdc-button cdc-button-primary" id="cdc-reservar-sala">
                    <span class="dashicons dashicons-calendar-alt"></span> Reservar
                </button>
                <button class="cdc-button cdc-button-secondary" id="cdc-edit-sala">
                    <span class="dashicons dashicons-edit"></span> Editar
                </button>
                <a href="<?php echo home_url('/salas'); ?>" class="cdc-button cdc-button-secondary">
                    <span class="dashicons dashicons-arrow-left-alt"></span> Volver
                </a>
            </div>
        </div>

        <!-- Sala Info Card -->
        <div class="cdc-card">
            <div class="cdc-card-header">
                <h3>Información de la Sala</h3>
            </div>
            <div class="cdc-card-body">
                <div class="cdc-info-grid">
                    <div class="cdc-info-item">
                        <span class="cdc-info-label">Capacidad:</span>
                        <span id="cdc-info-capacidad" class="cdc-info-value"></span>
                    </div>
                    <div class="cdc-info-item">
                        <span class="cdc-info-label">Precio por hora:</span>
                        <span id="cdc-info-precio" class="cdc-info-value"></span>
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
                <div class="cdc-info-item" id="cdc-equipamiento-container" style="margin-top: 20px; display: none;">
                    <span class="cdc-info-label">Equipamiento:</span>
                    <p id="cdc-info-equipamiento" class="cdc-info-value" style="margin-top: 8px;"></p>
                </div>
                <div class="cdc-info-item" id="cdc-notas-container" style="margin-top: 20px; display: none;">
                    <span class="cdc-info-label">Notas:</span>
                    <p id="cdc-info-notas" class="cdc-info-value" style="margin-top: 8px; color: #666;"></p>
                </div>
            </div>
        </div>

        <!-- Reservas Card -->
        <div class="cdc-card">
            <div class="cdc-card-header">
                <h3>Próximas Reservas</h3>
            </div>
            <div class="cdc-card-body">
                <div id="cdc-reservas-list">
                    <p class="cdc-text-center cdc-text-muted">Cargando reservas...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Reservar Sala -->
<div id="cdc-reservar-modal" class="cdc-modal" style="display: none;">
    <div class="cdc-modal-content">
        <div class="cdc-modal-header">
            <h3>Reservar Sala: <span id="cdc-modal-sala-nombre"></span></h3>
            <button class="cdc-modal-close" id="cdc-modal-close">&times;</button>
        </div>
        <div class="cdc-modal-body">
            <input type="hidden" id="cdc-modal-sala-id">

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
                <div class="cdc-form-row">
                    <div class="cdc-form-group cdc-form-col-6">
                        <label for="cdc-modal-fecha-inicio">Fecha y hora de inicio *</label>
                        <input type="datetime-local" id="cdc-modal-fecha-inicio" class="cdc-form-control" required>
                    </div>

                    <div class="cdc-form-group cdc-form-col-6">
                        <label for="cdc-modal-fecha-fin">Fecha y hora de fin *</label>
                        <input type="datetime-local" id="cdc-modal-fecha-fin" class="cdc-form-control" required>
                    </div>
                </div>

                <div class="cdc-form-group">
                    <label for="cdc-modal-motivo">Motivo de la reserva</label>
                    <input type="text" id="cdc-modal-motivo" class="cdc-form-control" placeholder="Ej: Clase de yoga, ensayo de teatro...">
                </div>

                <div class="cdc-form-group">
                    <label for="cdc-modal-notas">Notas</label>
                    <textarea id="cdc-modal-notas" class="cdc-form-control" rows="2"></textarea>
                </div>

                <div id="cdc-modal-monto-info" style="display: none; margin-bottom: 15px; padding: 10px; background: #e8f5e9; border-radius: 4px;">
                    <strong>Monto total estimado:</strong> <span id="cdc-modal-monto-total">$0.00</span>
                </div>

                <div class="cdc-form-actions">
                    <button type="submit" class="cdc-button cdc-button-primary">Crear Reserva</button>
                    <button type="button" class="cdc-button cdc-button-secondary" id="cdc-modal-cancel">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Get sala ID from URL
    const urlParams = new URLSearchParams(window.location.search);
    const salaId = urlParams.get('id');

    if (!salaId) {
        $('#cdc-loading').html('<p style="color: #d63638;">ID de sala no proporcionado</p>');
        return;
    }

    let currentSala = null;

    // Load sala data
    function loadSala() {
        CDCAPI.salas.get(salaId)
            .then(function(response) {
                if (response.success && response.data) {
                    currentSala = response.data;
                    renderSala(response.data);
                    $('#cdc-loading').hide();
                    $('#cdc-sala-content').show();
                } else {
                    $('#cdc-loading').html('<p style="color: #d63638;">Sala no encontrada</p>');
                }
            })
            .catch(function(error) {
                $('#cdc-loading').html('<p style="color: #d63638;">Error al cargar sala</p>');
                CDC.handleApiError(error, 'Load Sala');
            });
    }

    // Render sala info
    function renderSala(sala) {
        $('#cdc-sala-nombre').text(sala.nombre);

        // Estado badge
        let estadoBadgeClass = 'cdc-badge';
        if (sala.estado === 'disponible') {
            estadoBadgeClass += ' cdc-badge-success';
        } else if (sala.estado === 'mantenimiento') {
            estadoBadgeClass += ' cdc-badge-warning';
        } else {
            estadoBadgeClass += ' cdc-badge-secondary';
        }
        $('#cdc-sala-estado-badge').attr('class', estadoBadgeClass).text(sala.estado);

        // Info fields
        const capacidadText = sala.capacidad ? `${sala.capacidad} personas` : 'No especificada';
        $('#cdc-info-capacidad').text(capacidadText);

        const precioText = sala.precio_hora ? '$' + parseFloat(sala.precio_hora).toFixed(2) + '/hora' : 'No especificado';
        $('#cdc-info-precio').text(precioText);

        $('#cdc-info-estado').html(`<span class="${estadoBadgeClass}">${sala.estado}</span>`);

        $('#cdc-info-descripcion').text(sala.descripcion || 'Sin descripción');

        if (sala.equipamiento) {
            $('#cdc-info-equipamiento').text(sala.equipamiento);
            $('#cdc-equipamiento-container').show();
        }

        if (sala.notas) {
            $('#cdc-info-notas').text(sala.notas);
            $('#cdc-notas-container').show();
        }

        // Disable reservar button if not disponible
        if (sala.estado !== 'disponible') {
            $('#cdc-reservar-sala').prop('disabled', true).addClass('cdc-button-disabled');
        }
    }

    // Edit sala button
    $('#cdc-edit-sala').on('click', function() {
        window.location.href = cdcData.homeUrl + '/editar-sala?id=' + salaId;
    });

    // Load reservas
    function loadReservas() {
        CDCAPI.salas.reservas(salaId)
            .then(function(response) {
                if (response.success) {
                    renderReservas(response.data);
                } else {
                    $('#cdc-reservas-list').html('<p class="cdc-text-muted">Error al cargar reservas.</p>');
                }
            })
            .catch(function(error) {
                $('#cdc-reservas-list').html('<p class="cdc-text-muted">Error al cargar reservas.</p>');
                console.error('Error loading reservas:', error);
            });
    }

    // Render reservas list
    function renderReservas(reservas) {
        if (!reservas || reservas.length === 0) {
            $('#cdc-reservas-list').html('<p class="cdc-text-center cdc-text-muted">No hay reservas próximas.</p>');
            return;
        }

        let html = '<div class="cdc-table-wrapper"><table class="cdc-table">';
        html += '<thead><tr><th>Fecha/Hora</th><th>Persona</th><th>Motivo</th><th>Duración</th><th>Monto</th><th>Estado</th></tr></thead><tbody>';

        reservas.forEach(function(r) {
            const fechaInicio = new Date(r.fecha_inicio).toLocaleString('es-AR');
            const fechaFin = new Date(r.fecha_fin).toLocaleString('es-AR');
            const persona = r.persona_nombre ? `${r.persona_nombre} ${r.persona_apellido || ''}` : '-';
            const duracion = `${parseFloat(r.duracion_horas).toFixed(1)}h`;
            const monto = `$${parseFloat(r.monto_total).toFixed(2)}`;

            html += '<tr>';
            html += `<td><strong>${fechaInicio}</strong><br><small>hasta ${fechaFin}</small></td>`;
            html += `<td>${persona}</td>`;
            html += `<td>${r.motivo || '-'}</td>`;
            html += `<td>${duracion}</td>`;
            html += `<td>${monto}</td>`;
            html += `<td><span class="cdc-badge">${r.estado}</span></td>`;
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        $('#cdc-reservas-list').html(html);
    }

    // Reservar sala button
    $('#cdc-reservar-sala').on('click', function() {
        if (!currentSala) return;

        if (currentSala.estado !== 'disponible') {
            CDC.showNotification('La sala no está disponible para reservas', 'warning');
            return;
        }

        openReservarModal();
    });

    // Open reservar modal
    function openReservarModal() {
        $('#cdc-modal-sala-nombre').text(currentSala.nombre);
        $('#cdc-modal-sala-id').val(salaId);
        $('#cdc-reservar-modal').show();
        $('#cdc-modal-person-search').val('').focus();
        $('#cdc-modal-person-results').empty();
        $('#cdc-modal-selected-person').hide();
        $('#cdc-modal-monto-info').hide();
        $('#cdc-modal-form')[0].reset();
        selectedPersonModal = null;
    }

    // Close modal
    $('#cdc-modal-close, #cdc-modal-cancel').on('click', function() {
        $('#cdc-reservar-modal').hide();
    });

    // Search person in modal
    $('#cdc-modal-search-btn').on('click', searchPersonModal);
    $('#cdc-modal-person-search').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
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

    // Calculate monto on date change
    $('#cdc-modal-fecha-inicio, #cdc-modal-fecha-fin').on('change', function() {
        calculateMonto();
    });

    function calculateMonto() {
        const inicio = $('#cdc-modal-fecha-inicio').val();
        const fin = $('#cdc-modal-fecha-fin').val();

        if (!inicio || !fin || !currentSala) return;

        const startDate = new Date(inicio);
        const endDate = new Date(fin);
        const hours = (endDate - startDate) / (1000 * 60 * 60);

        if (hours > 0) {
            const precioHora = parseFloat(currentSala.precio_hora || 0);
            const montoTotal = hours * precioHora;
            $('#cdc-modal-monto-total').text('$' + montoTotal.toFixed(2));
            $('#cdc-modal-monto-info').show();
        } else {
            $('#cdc-modal-monto-info').hide();
        }
    }

    // Submit reservation
    $('#cdc-modal-form').on('submit', function(e) {
        e.preventDefault();

        if (!selectedPersonModal) {
            CDC.showNotification('Seleccione una persona', 'warning');
            return;
        }

        const inicio = $('#cdc-modal-fecha-inicio').val();
        const fin = $('#cdc-modal-fecha-fin').val();

        if (!inicio || !fin) {
            CDC.showNotification('Complete fecha de inicio y fin', 'warning');
            return;
        }

        const startDate = new Date(inicio);
        const endDate = new Date(fin);

        if (endDate <= startDate) {
            CDC.showNotification('La fecha de fin debe ser posterior a la de inicio', 'warning');
            return;
        }

        const $submitBtn = $(this).find('button[type="submit"]');
        CDC.showLoadingButton($submitBtn, true);

        const data = {
            sala_id: parseInt(salaId),
            persona_id: selectedPersonModal.id,
            fecha_inicio: inicio,
            fecha_fin: fin,
            motivo: $('#cdc-modal-motivo').val() || null,
            notas: $('#cdc-modal-notas').val() || null
        };

        CDCAPI.salas.reservar(data)
            .then(function(response) {
                CDC.showLoadingButton($submitBtn, false);
                if (response.success) {
                    CDC.showNotification('Reserva creada correctamente', 'success');
                    $('#cdc-reservar-modal').hide();
                    loadReservas(); // Reload reservas list
                } else {
                    CDC.handleApiError(response, 'Crear Reserva');
                }
            })
            .catch(function(error) {
                CDC.showLoadingButton($submitBtn, false);
                CDC.handleApiError(error, 'Crear Reserva');
            });
    });

    // Initial load
    loadSala();
    loadReservas();
});
</script>

<style>
.cdc-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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

.cdc-badge-success {
    background-color: #4caf50;
    color: white;
}

.cdc-badge-warning {
    background-color: #ff9800;
    color: white;
}

.cdc-badge-secondary {
    background-color: #757575;
    color: white;
}

.cdc-button-disabled {
    opacity: 0.5;
    cursor: not-allowed;
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
    max-width: 700px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    max-height: 85vh;
    overflow-y: auto;
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
