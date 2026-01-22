<?php
/**
 * Template Name: Editar Taller
 *
 * @package CDC_Sistema
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<div class="cdc-editar-taller">
    <!-- Loading State -->
    <div id="cdc-loading" class="cdc-text-center" style="padding: 40px;">
        <p class="cdc-text-muted">Cargando datos...</p>
    </div>

    <!-- Form Content (hidden initially) -->
    <div id="cdc-form-content" style="display: none;">
        <!-- Header -->
        <div class="cdc-page-header">
            <div>
                <h1 class="cdc-page-title">Editar Taller</h1>
                <p class="cdc-text-muted">Modificar datos de <span id="cdc-nombre-label"></span></p>
            </div>
            <div class="cdc-page-header-actions">
                <button class="cdc-button cdc-button-secondary" id="cdc-back-btn">
                    <span class="dashicons dashicons-arrow-left-alt"></span> Volver
                </button>
            </div>
        </div>

        <div class="cdc-card">
            <div class="cdc-card-body">
                <form id="cdc-taller-form">
                    <input type="hidden" id="cdc-taller-id">

                    <div class="cdc-form-group">
                        <label for="cdc-nombre">Nombre del taller *</label>
                        <input type="text"
                               id="cdc-nombre"
                               class="cdc-form-control"
                               placeholder="Ej: Yoga para principiantes"
                               required>
                    </div>

                    <div class="cdc-form-group">
                        <label for="cdc-descripcion">Descripción</label>
                        <textarea id="cdc-descripcion"
                                  class="cdc-form-control"
                                  rows="3"
                                  placeholder="Descripción del taller, nivel, objetivos..."></textarea>
                    </div>

                    <div class="cdc-form-row">
                        <div class="cdc-form-group cdc-form-col-6">
                            <label for="cdc-profesor">Profesor/Tallerista</label>
                            <input type="text"
                                   id="cdc-profesor"
                                   class="cdc-form-control"
                                   placeholder="Nombre del profesor">
                        </div>

                        <div class="cdc-form-group cdc-form-col-6">
                            <label for="cdc-sala">Sala</label>
                            <select id="cdc-sala" class="cdc-form-control">
                                <option value="">Sin asignar</option>
                                <!-- Salas will be loaded dynamically -->
                            </select>
                        </div>
                    </div>

                    <div class="cdc-form-row">
                        <div class="cdc-form-group cdc-form-col-6">
                            <label for="cdc-dia-semana">Día(s) de la semana</label>
                            <input type="text"
                                   id="cdc-dia-semana"
                                   class="cdc-form-control"
                                   placeholder="Ej: Lunes y Miércoles">
                            <small class="cdc-text-muted">Día o días en que se dicta el taller</small>
                        </div>

                        <div class="cdc-form-group cdc-form-col-6">
                            <label for="cdc-horario">Horario</label>
                            <input type="text"
                                   id="cdc-horario"
                                   class="cdc-form-control"
                                   placeholder="Ej: 18:00 a 20:00">
                            <small class="cdc-text-muted">Horario de inicio y fin</small>
                        </div>
                    </div>

                    <div class="cdc-form-row">
                        <div class="cdc-form-group cdc-form-col-6">
                            <label for="cdc-precio-mensual">Precio mensual *</label>
                            <input type="number"
                                   id="cdc-precio-mensual"
                                   class="cdc-form-control"
                                   placeholder="0.00"
                                   step="0.01"
                                   min="0"
                                   required>
                        </div>

                        <div class="cdc-form-group cdc-form-col-6">
                            <label for="cdc-cupo">Cupo máximo</label>
                            <input type="number"
                                   id="cdc-cupo"
                                   class="cdc-form-control"
                                   placeholder="0 = sin límite"
                                   min="0">
                            <small class="cdc-text-muted">Dejar en 0 para cupo ilimitado</small>
                        </div>
                    </div>

                    <div class="cdc-form-group">
                        <label for="cdc-estado">Estado *</label>
                        <select id="cdc-estado" class="cdc-form-control" required>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                            <option value="finalizado">Finalizado</option>
                        </select>
                    </div>

                    <div class="cdc-form-group">
                        <label for="cdc-notas">Notas</label>
                        <textarea id="cdc-notas"
                                  class="cdc-form-control"
                                  rows="2"
                                  placeholder="Observaciones internas, requisitos, materiales necesarios..."></textarea>
                    </div>

                    <div class="cdc-form-actions">
                        <button type="submit" class="cdc-button cdc-button-primary">
                            <span class="dashicons dashicons-saved"></span> Guardar cambios
                        </button>
                        <button type="button" class="cdc-button cdc-button-secondary" id="cdc-cancel-btn">
                            Cancelar
                        </button>
                    </div>
                </form>
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

    // Load salas for dropdown
    CDCAPI.salas.list()
        .then(function(response) {
            if (response.success && response.data) {
                response.data.forEach(function(sala) {
                    $('#cdc-sala').append(`<option value="${sala.id}">${sala.nombre}</option>`);
                });
            }
        });

    // Load taller data
    CDCAPI.talleres.get(tallerId)
        .then(function(response) {
            if (response.success && response.data) {
                currentTaller = response.data;
                populateForm(response.data);
                $('#cdc-loading').hide();
                $('#cdc-form-content').show();
            } else {
                $('#cdc-loading').html('<p style="color: #d63638;">Taller no encontrado</p>');
            }
        })
        .catch(function(error) {
            $('#cdc-loading').html('<p style="color: #d63638;">Error al cargar taller</p>');
            CDC.handleApiError(error, 'Load Taller');
        });

    // Populate form with taller data
    function populateForm(taller) {
        $('#cdc-taller-id').val(taller.id);
        $('#cdc-nombre-label').text(taller.nombre);

        $('#cdc-nombre').val(taller.nombre);
        $('#cdc-descripcion').val(taller.descripcion || '');
        $('#cdc-profesor').val(taller.profesor || '');
        $('#cdc-dia-semana').val(taller.dia_semana || '');
        $('#cdc-horario').val(taller.horario || '');
        $('#cdc-precio-mensual').val(taller.precio_mensual || '');
        $('#cdc-cupo').val(taller.cupo_maximo || '0');
        $('#cdc-estado').val(taller.estado);
        $('#cdc-notas').val(taller.notas || '');

        // Set sala if exists
        if (taller.sala_id) {
            $('#cdc-sala').val(taller.sala_id);
        }
    }

    // Form submission
    $('#cdc-taller-form').on('submit', function(e) {
        e.preventDefault();

        const tallerId = $('#cdc-taller-id').val();

        // Build data object
        const data = {
            nombre: $('#cdc-nombre').val().trim(),
            descripcion: $('#cdc-descripcion').val().trim() || null,
            profesor: $('#cdc-profesor').val().trim() || null,
            dia_semana: $('#cdc-dia-semana').val().trim() || null,
            horario: $('#cdc-horario').val().trim() || null,
            sala_id: $('#cdc-sala').val() || null,
            precio_mensual: parseFloat($('#cdc-precio-mensual').val()),
            cupo_maximo: $('#cdc-cupo').val() ? parseInt($('#cdc-cupo').val()) : null,
            estado: $('#cdc-estado').val(),
            notas: $('#cdc-notas').val().trim() || null
        };

        // Validation
        if (!data.nombre) {
            CDC.showNotification('Ingrese el nombre del taller', 'warning');
            return;
        }

        if (!data.precio_mensual || data.precio_mensual < 0) {
            CDC.showNotification('Ingrese un precio válido', 'warning');
            return;
        }

        const $submitBtn = $(this).find('button[type="submit"]');
        CDC.showLoadingButton($submitBtn, true);

        CDCAPI.talleres.update(tallerId, data)
            .then(function(response) {
                CDC.showLoadingButton($submitBtn, false);
                if (response.success) {
                    CDC.showNotification('Taller actualizado exitosamente', 'success');
                    setTimeout(function() {
                        window.location.href = cdcData.homeUrl + '/taller?id=' + tallerId;
                    }, 1500);
                } else {
                    CDC.handleApiError(response, 'Update Taller');
                }
            })
            .catch(function(error) {
                CDC.showLoadingButton($submitBtn, false);
                CDC.handleApiError(error, 'Update Taller');
            });
    });

    // Back button
    $('#cdc-back-btn, #cdc-cancel-btn').on('click', function() {
        const tallerId = $('#cdc-taller-id').val();
        window.location.href = cdcData.homeUrl + '/taller?id=' + tallerId;
    });
});
</script>

<?php get_footer(); ?>
