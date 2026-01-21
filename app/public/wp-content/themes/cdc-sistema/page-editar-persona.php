<?php
/**
 * Template Name: Editar Persona
 *
 * @package CDC_Sistema
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<div class="cdc-editar-persona">
    <!-- Loading State -->
    <div id="cdc-loading" class="cdc-text-center" style="padding: 40px;">
        <p class="cdc-text-muted">Cargando datos...</p>
    </div>

    <!-- Form Content (hidden initially) -->
    <div id="cdc-form-content" style="display: none;">
        <!-- Header -->
        <div class="cdc-page-header">
            <div>
                <h1 class="cdc-page-title">Editar <span id="cdc-tipo-label"></span></h1>
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
                <form id="cdc-persona-form">
                    <input type="hidden" id="cdc-persona-id">
                    <input type="hidden" id="cdc-persona-tipo">

                    <div class="cdc-form-row">
                        <div class="cdc-form-group cdc-form-col-6">
                            <label for="cdc-nombre">Nombre *</label>
                            <input type="text"
                                   id="cdc-nombre"
                                   class="cdc-form-control"
                                   required>
                        </div>

                        <div class="cdc-form-group cdc-form-col-6">
                            <label for="cdc-apellido">Apellido *</label>
                            <input type="text"
                                   id="cdc-apellido"
                                   class="cdc-form-control"
                                   required>
                        </div>
                    </div>

                    <div class="cdc-form-row">
                        <div class="cdc-form-group cdc-form-col-6">
                            <label for="cdc-dni">DNI *</label>
                            <input type="text"
                                   id="cdc-dni"
                                   class="cdc-form-control"
                                   required>
                        </div>

                        <div class="cdc-form-group cdc-form-col-6">
                            <label for="cdc-telefono">Teléfono</label>
                            <input type="tel"
                                   id="cdc-telefono"
                                   class="cdc-form-control"
                                   placeholder="Ej: 351-1234567">
                        </div>
                    </div>

                    <div class="cdc-form-group">
                        <label for="cdc-email">Email</label>
                        <input type="email"
                               id="cdc-email"
                               class="cdc-form-control"
                               placeholder="ejemplo@email.com">
                    </div>

                    <div class="cdc-form-group">
                        <label for="cdc-domicilio">Domicilio</label>
                        <input type="text"
                               id="cdc-domicilio"
                               class="cdc-form-control"
                               placeholder="Calle, número, localidad">
                    </div>

                    <div class="cdc-form-group">
                        <label for="cdc-fecha-nacimiento">Fecha de nacimiento</label>
                        <input type="date"
                               id="cdc-fecha-nacimiento"
                               class="cdc-form-control">
                    </div>

                    <div class="cdc-form-group">
                        <label for="cdc-estado">Estado *</label>
                        <select id="cdc-estado" class="cdc-form-control" required>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>

                    <!-- Socio-specific fields -->
                    <div id="cdc-socio-fields" style="display: none;">
                        <h4 style="margin-top: 30px; margin-bottom: 15px; border-top: 1px solid #e0e0e0; padding-top: 20px;">
                            Datos de Socio
                        </h4>

                        <div class="cdc-form-row">
                            <div class="cdc-form-group cdc-form-col-6">
                                <label for="cdc-categoria">Categoría</label>
                                <select id="cdc-categoria" class="cdc-form-control">
                                    <option value="">Seleccionar...</option>
                                    <option value="general">General</option>
                                    <option value="estudiante">Estudiante</option>
                                    <option value="jubilado">Jubilado</option>
                                    <option value="vitalicio">Vitalicio</option>
                                </select>
                            </div>

                            <div class="cdc-form-group cdc-form-col-6">
                                <label for="cdc-cuota-mensual">Cuota mensual</label>
                                <input type="number"
                                       id="cdc-cuota-mensual"
                                       class="cdc-form-control"
                                       placeholder="0.00"
                                       step="0.01">
                            </div>
                        </div>
                    </div>

                    <div class="cdc-form-group">
                        <label for="cdc-notas">Notas</label>
                        <textarea id="cdc-notas"
                                  class="cdc-form-control"
                                  rows="3"
                                  placeholder="Observaciones adicionales..."></textarea>
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
    // Get persona ID from URL
    const urlParams = new URLSearchParams(window.location.search);
    const personaId = urlParams.get('id');

    if (!personaId) {
        $('#cdc-loading').html('<p style="color: #d63638;">ID de persona no proporcionado</p>');
        return;
    }

    let currentPersona = null;

    // Load persona data
    CDCAPI.personas.get(personaId)
        .then(function(response) {
            if (response.success && response.data) {
                currentPersona = response.data;
                populateForm(response.data);
                $('#cdc-loading').hide();
                $('#cdc-form-content').show();
            } else {
                $('#cdc-loading').html('<p style="color: #d63638;">Persona no encontrada</p>');
            }
        })
        .catch(function(error) {
            $('#cdc-loading').html('<p style="color: #d63638;">Error al cargar persona</p>');
            CDC.handleApiError(error, 'Load Persona');
        });

    // Populate form with persona data
    function populateForm(persona) {
        $('#cdc-persona-id').val(persona.id);
        $('#cdc-persona-tipo').val(persona.tipo);
        $('#cdc-tipo-label').text(persona.tipo === 'socio' ? 'Socio' : 'Cliente');
        $('#cdc-nombre-label').text(`${persona.nombre} ${persona.apellido}`);

        // Basic fields
        $('#cdc-nombre').val(persona.nombre);
        $('#cdc-apellido').val(persona.apellido);
        $('#cdc-dni').val(persona.dni);
        $('#cdc-telefono').val(persona.telefono || '');
        $('#cdc-email').val(persona.email || '');
        $('#cdc-domicilio').val(persona.domicilio || '');
        $('#cdc-fecha-nacimiento').val(persona.fecha_nacimiento || '');
        $('#cdc-estado').val(persona.estado);
        $('#cdc-notas').val(persona.notas || '');

        // Socio-specific fields
        if (persona.tipo === 'socio') {
            $('#cdc-socio-fields').show();
            $('#cdc-categoria').val(persona.categoria || '');
            $('#cdc-cuota-mensual').val(persona.cuota_mensual || '');
        }
    }

    // Form submission
    $('#cdc-persona-form').on('submit', function(e) {
        e.preventDefault();

        const personaId = $('#cdc-persona-id').val();
        const tipo = $('#cdc-persona-tipo').val();

        // Build data object
        const data = {
            nombre: $('#cdc-nombre').val().trim(),
            apellido: $('#cdc-apellido').val().trim(),
            dni: $('#cdc-dni').val().trim(),
            telefono: $('#cdc-telefono').val().trim() || null,
            email: $('#cdc-email').val().trim() || null,
            domicilio: $('#cdc-domicilio').val().trim() || null,
            fecha_nacimiento: $('#cdc-fecha-nacimiento').val() || null,
            estado: $('#cdc-estado').val(),
            notas: $('#cdc-notas').val().trim() || null
        };

        // Add socio-specific fields
        if (tipo === 'socio') {
            data.categoria = $('#cdc-categoria').val() || null;
            data.cuota_mensual = $('#cdc-cuota-mensual').val() ? parseFloat($('#cdc-cuota-mensual').val()) : null;
        }

        // Validation
        if (!data.nombre || !data.apellido || !data.dni) {
            CDC.showNotification('Complete los campos obligatorios', 'warning');
            return;
        }

        const $submitBtn = $(this).find('button[type="submit"]');
        CDC.showLoadingButton($submitBtn, true);

        CDCAPI.personas.update(personaId, data)
            .then(function(response) {
                CDC.showLoadingButton($submitBtn, false);
                if (response.success) {
                    CDC.showNotification('Persona actualizada exitosamente', 'success');
                    setTimeout(function() {
                        window.location.href = cdcData.homeUrl + '/persona?id=' + personaId;
                    }, 1500);
                } else {
                    CDC.handleApiError(response, 'Update Persona');
                }
            })
            .catch(function(error) {
                CDC.showLoadingButton($submitBtn, false);
                CDC.handleApiError(error, 'Update Persona');
            });
    });

    // Back button
    $('#cdc-back-btn, #cdc-cancel-btn').on('click', function() {
        const personaId = $('#cdc-persona-id').val();
        window.location.href = cdcData.homeUrl + '/persona?id=' + personaId;
    });
});
</script>

<?php get_footer(); ?>
