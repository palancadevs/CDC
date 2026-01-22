<?php
/**
 * Template Name: Editar Sala
 *
 * @package CDC_Sistema
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<div class="cdc-editar-sala">
    <!-- Loading State -->
    <div id="cdc-loading" class="cdc-text-center" style="padding: 40px;">
        <p class="cdc-text-muted">Cargando datos...</p>
    </div>

    <!-- Form Content (hidden initially) -->
    <div id="cdc-form-content" style="display: none;">
        <!-- Header -->
        <div class="cdc-page-header">
            <div>
                <h1 class="cdc-page-title">Editar Sala</h1>
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
                <form id="cdc-sala-form">
                    <input type="hidden" id="cdc-sala-id">

                    <div class="cdc-form-group">
                        <label for="cdc-nombre">Nombre de la sala *</label>
                        <input type="text"
                               id="cdc-nombre"
                               class="cdc-form-control"
                               placeholder="Ej: Sala Principal"
                               required>
                    </div>

                    <div class="cdc-form-group">
                        <label for="cdc-descripcion">Descripción</label>
                        <textarea id="cdc-descripcion"
                                  class="cdc-form-control"
                                  rows="3"
                                  placeholder="Descripción de la sala, características, usos..."></textarea>
                    </div>

                    <div class="cdc-form-row">
                        <div class="cdc-form-group cdc-form-col-6">
                            <label for="cdc-capacidad">Capacidad (personas)</label>
                            <input type="number"
                                   id="cdc-capacidad"
                                   class="cdc-form-control"
                                   placeholder="0"
                                   min="0">
                        </div>

                        <div class="cdc-form-group cdc-form-col-6">
                            <label for="cdc-precio-hora">Precio por hora *</label>
                            <input type="number"
                                   id="cdc-precio-hora"
                                   class="cdc-form-control"
                                   placeholder="0.00"
                                   step="0.01"
                                   min="0"
                                   required>
                        </div>
                    </div>

                    <div class="cdc-form-group">
                        <label for="cdc-equipamiento">Equipamiento</label>
                        <textarea id="cdc-equipamiento"
                                  class="cdc-form-control"
                                  rows="2"
                                  placeholder="Ej: Proyector, sonido, aire acondicionado..."></textarea>
                    </div>

                    <div class="cdc-form-group">
                        <label for="cdc-estado">Estado *</label>
                        <select id="cdc-estado" class="cdc-form-control" required>
                            <option value="disponible">Disponible</option>
                            <option value="mantenimiento">En mantenimiento</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>

                    <div class="cdc-form-group">
                        <label for="cdc-notas">Notas</label>
                        <textarea id="cdc-notas"
                                  class="cdc-form-control"
                                  rows="2"
                                  placeholder="Notas internas, requisitos, restricciones..."></textarea>
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
    // Get sala ID from URL
    const urlParams = new URLSearchParams(window.location.search);
    const salaId = urlParams.get('id');

    if (!salaId) {
        $('#cdc-loading').html('<p style="color: #d63638;">ID de sala no proporcionado</p>');
        return;
    }

    let currentSala = null;

    // Load sala data
    CDCAPI.salas.get(salaId)
        .then(function(response) {
            if (response.success && response.data) {
                currentSala = response.data;
                populateForm(response.data);
                $('#cdc-loading').hide();
                $('#cdc-form-content').show();
            } else {
                $('#cdc-loading').html('<p style="color: #d63638;">Sala no encontrada</p>');
            }
        })
        .catch(function(error) {
            $('#cdc-loading').html('<p style="color: #d63638;">Error al cargar sala</p>');
            CDC.handleApiError(error, 'Load Sala');
        });

    // Populate form with sala data
    function populateForm(sala) {
        $('#cdc-sala-id').val(sala.id);
        $('#cdc-nombre-label').text(sala.nombre);

        $('#cdc-nombre').val(sala.nombre);
        $('#cdc-descripcion').val(sala.descripcion || '');
        $('#cdc-capacidad').val(sala.capacidad || '');
        $('#cdc-precio-hora').val(sala.precio_hora || '');
        $('#cdc-equipamiento').val(sala.equipamiento || '');
        $('#cdc-estado').val(sala.estado);
        $('#cdc-notas').val(sala.notas || '');
    }

    // Form submission
    $('#cdc-sala-form').on('submit', function(e) {
        e.preventDefault();

        const salaId = $('#cdc-sala-id').val();

        // Build data object
        const data = {
            nombre: $('#cdc-nombre').val().trim(),
            descripcion: $('#cdc-descripcion').val().trim() || null,
            capacidad: $('#cdc-capacidad').val() ? parseInt($('#cdc-capacidad').val()) : null,
            precio_hora: parseFloat($('#cdc-precio-hora').val()),
            equipamiento: $('#cdc-equipamiento').val().trim() || null,
            estado: $('#cdc-estado').val(),
            notas: $('#cdc-notas').val().trim() || null
        };

        // Validation
        if (!data.nombre) {
            CDC.showNotification('Ingrese el nombre de la sala', 'warning');
            return;
        }

        if (!data.precio_hora || data.precio_hora < 0) {
            CDC.showNotification('Ingrese un precio válido', 'warning');
            return;
        }

        const $submitBtn = $(this).find('button[type="submit"]');
        CDC.showLoadingButton($submitBtn, true);

        CDCAPI.salas.update(salaId, data)
            .then(function(response) {
                CDC.showLoadingButton($submitBtn, false);
                if (response.success) {
                    CDC.showNotification('Sala actualizada exitosamente', 'success');
                    setTimeout(function() {
                        window.location.href = cdcData.homeUrl + '/sala?id=' + salaId;
                    }, 1500);
                } else {
                    CDC.handleApiError(response, 'Update Sala');
                }
            })
            .catch(function(error) {
                CDC.showLoadingButton($submitBtn, false);
                CDC.handleApiError(error, 'Update Sala');
            });
    });

    // Back button
    $('#cdc-back-btn, #cdc-cancel-btn').on('click', function() {
        const salaId = $('#cdc-sala-id').val();
        window.location.href = cdcData.homeUrl + '/sala?id=' + salaId;
    });
});
</script>

<?php get_footer(); ?>
