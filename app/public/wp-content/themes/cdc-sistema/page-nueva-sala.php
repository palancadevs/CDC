<?php
/**
 * Template Name: Nueva Sala
 *
 * @package CDC_Sistema
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<div class="cdc-nueva-sala">
    <!-- Header -->
    <div class="cdc-page-header">
        <div>
            <h1 class="cdc-page-title">Nueva Sala</h1>
            <p class="cdc-text-muted">Registrar una nueva sala en el sistema</p>
        </div>
        <div class="cdc-page-header-actions">
            <a href="<?php echo esc_url(home_url('/salas')); ?>" class="cdc-button cdc-button-secondary">
                <span class="dashicons dashicons-arrow-left-alt"></span> Volver a Salas
            </a>
        </div>
    </div>

    <div class="cdc-card">
        <div class="cdc-card-body">
            <form id="cdc-sala-form">
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
                              placeholder="Descripción de la sala, ubicación, características especiales..."></textarea>
                </div>

                <div class="cdc-form-row">
                    <div class="cdc-form-group cdc-form-col-6">
                        <label for="cdc-capacidad">Capacidad (personas)</label>
                        <input type="number"
                               id="cdc-capacidad"
                               class="cdc-form-control"
                               placeholder="0"
                               min="1">
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
                    <input type="text"
                           id="cdc-equipamiento"
                           class="cdc-form-control"
                           placeholder="Ej: Proyector, sonido, aire acondicionado">
                    <small class="cdc-text-muted">Separar con comas los elementos de equipamiento</small>
                </div>

                <div class="cdc-form-group">
                    <label for="cdc-estado">Estado *</label>
                    <select id="cdc-estado" class="cdc-form-control" required>
                        <option value="disponible" selected>Disponible</option>
                        <option value="mantenimiento">En mantenimiento</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>

                <div class="cdc-form-group">
                    <label for="cdc-notas">Notas</label>
                    <textarea id="cdc-notas"
                              class="cdc-form-control"
                              rows="2"
                              placeholder="Notas internas, restricciones, información adicional..."></textarea>
                </div>

                <div class="cdc-form-actions">
                    <button type="submit" class="cdc-button cdc-button-primary">
                        <span class="dashicons dashicons-saved"></span> Guardar sala
                    </button>
                    <a href="<?php echo esc_url(home_url('/salas')); ?>" class="cdc-button cdc-button-secondary">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#cdc-sala-form').on('submit', function(e) {
        e.preventDefault();

        const nombre = $('#cdc-nombre').val().trim();
        const descripcion = $('#cdc-descripcion').val().trim();
        const capacidad = $('#cdc-capacidad').val();
        const precio_hora = parseFloat($('#cdc-precio-hora').val());
        const equipamiento = $('#cdc-equipamiento').val().trim();
        const estado = $('#cdc-estado').val();
        const notas = $('#cdc-notas').val().trim();

        // Validation
        if (!nombre) {
            CDC.showNotification('Ingrese el nombre de la sala', 'warning');
            $('#cdc-nombre').focus();
            return;
        }

        if (!precio_hora || precio_hora < 0) {
            CDC.showNotification('Ingrese un precio válido', 'warning');
            $('#cdc-precio-hora').focus();
            return;
        }

        const $submitBtn = $(this).find('button[type="submit"]');
        CDC.showLoadingButton($submitBtn, true);

        const data = {
            nombre: nombre,
            descripcion: descripcion || null,
            capacidad: capacidad ? parseInt(capacidad) : null,
            precio_hora: precio_hora,
            equipamiento: equipamiento || null,
            estado: estado,
            notas: notas || null
        };

        CDCAPI.salas.create(data)
            .then(function(response) {
                CDC.showLoadingButton($submitBtn, false);
                if (response.success) {
                    CDC.showNotification('Sala creada exitosamente', 'success');
                    setTimeout(function() {
                        window.location.href = cdcData.homeUrl + '/salas';
                    }, 1500);
                } else {
                    CDC.handleApiError(response, 'Crear Sala');
                }
            })
            .catch(function(error) {
                CDC.showLoadingButton($submitBtn, false);
                CDC.handleApiError(error, 'Crear Sala');
            });
    });
});
</script>

<?php get_footer(); ?>
