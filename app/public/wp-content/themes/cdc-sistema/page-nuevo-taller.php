<?php
/**
 * Template Name: Nuevo Taller
 *
 * @package CDC_Sistema
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<div class="cdc-nuevo-taller">
    <div class="cdc-card">
        <div class="cdc-card-header">
            <h3 class="cdc-card-title">Nuevo Taller</h3>
        </div>
        <div class="cdc-card-body">
            <form id="cdc-taller-form">
                <h4 class="cdc-form-section-title">Datos del taller</h4>

                <div class="cdc-form-group">
                    <label for="cdc-nombre">Nombre del taller *</label>
                    <input type="text"
                           id="cdc-nombre"
                           class="cdc-form-control"
                           placeholder="Ej: Yoga, Pintura, etc."
                           required>
                </div>

                <div class="cdc-form-group">
                    <label for="cdc-descripcion">Descripción</label>
                    <textarea id="cdc-descripcion"
                              class="cdc-form-control"
                              rows="3"
                              placeholder="Descripción breve del taller"></textarea>
                </div>

                <div class="cdc-form-row">
                    <div class="cdc-form-group cdc-form-col-6">
                        <label for="cdc-profesor">Profesor / Tallerista *</label>
                        <input type="text"
                               id="cdc-profesor"
                               class="cdc-form-control"
                               placeholder="Nombre del profesor"
                               required>
                    </div>
                    <div class="cdc-form-group cdc-form-col-6">
                        <label for="cdc-sala">Sala</label>
                        <select id="cdc-sala" class="cdc-form-control">
                            <option value="">Seleccionar...</option>
                            <option value="1">Sala 1</option>
                            <option value="2">Sala 2</option>
                            <option value="3">Sala 3</option>
                        </select>
                    </div>
                </div>

                <h4 class="cdc-form-section-title" style="margin-top: 30px;">Horarios y precio</h4>

                <div class="cdc-form-row">
                    <div class="cdc-form-group cdc-form-col-6">
                        <label for="cdc-dia-semana">Día(s) de la semana</label>
                        <input type="text"
                               id="cdc-dia-semana"
                               class="cdc-form-control"
                               placeholder="Ej: Lunes y Miércoles">
                    </div>
                    <div class="cdc-form-group cdc-form-col-6">
                        <label for="cdc-horario">Horario</label>
                        <input type="text"
                               id="cdc-horario"
                               class="cdc-form-control"
                               placeholder="Ej: 18:00 a 20:00">
                    </div>
                </div>

                <div class="cdc-form-row">
                    <div class="cdc-form-group cdc-form-col-4">
                        <label for="cdc-precio">Precio mensual *</label>
                        <input type="number"
                               id="cdc-precio"
                               class="cdc-form-control"
                               placeholder="0.00"
                               step="0.01"
                               required>
                    </div>
                    <div class="cdc-form-group cdc-form-col-4">
                        <label for="cdc-cupo">Cupo máximo</label>
                        <input type="number"
                               id="cdc-cupo"
                               class="cdc-form-control"
                               placeholder="0">
                    </div>
                    <div class="cdc-form-group cdc-form-col-4">
                        <label for="cdc-estado">Estado</label>
                        <select id="cdc-estado" class="cdc-form-control">
                            <option value="activo" selected>Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>
                </div>

                <div class="cdc-form-group">
                    <label for="cdc-notas">Notas</label>
                    <textarea id="cdc-notas"
                              class="cdc-form-control"
                              rows="2"
                              placeholder="Notas adicionales (opcional)"></textarea>
                </div>

                <div class="cdc-form-actions">
                    <button type="submit" class="cdc-button cdc-button-primary">
                        Crear taller
                    </button>
                    <a href="<?php echo home_url('/talleres'); ?>" class="cdc-button cdc-button-secondary">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#cdc-taller-form').on('submit', function(e) {
        e.preventDefault();

        const precio = parseFloat($('#cdc-precio').val());
        if (!precio || precio <= 0) {
            CDC.showNotification('Ingrese un precio válido', 'warning');
            return;
        }

        const $submitBtn = $(this).find('button[type="submit"]');
        CDC.showLoadingButton($submitBtn, true);

        const data = {
            nombre: $('#cdc-nombre').val(),
            descripcion: $('#cdc-descripcion').val(),
            profesor: $('#cdc-profesor').val(),
            dia_semana: $('#cdc-dia-semana').val(),
            horario: $('#cdc-horario').val(),
            sala_id: $('#cdc-sala').val() || null,
            precio_mensual: precio,
            cupo_maximo: $('#cdc-cupo').val() || null,
            inscriptos: 0,
            estado: $('#cdc-estado').val(),
            notas: $('#cdc-notas').val()
        };

        CDCAPI.talleres.create(data)
            .then(function(response) {
                CDC.showLoadingButton($submitBtn, false);
                if (response.success) {
                    CDC.showNotification('Taller creado exitosamente', 'success');
                    setTimeout(function() {
                        window.location.href = cdcData.homeUrl + '/talleres';
                    }, 1500);
                } else {
                    CDC.handleApiError(response, 'Create Taller');
                }
            })
            .catch(function(error) {
                CDC.showLoadingButton($submitBtn, false);
                CDC.handleApiError(error, 'Create Taller');
            });
    });
});
</script>

<?php get_footer(); ?>
