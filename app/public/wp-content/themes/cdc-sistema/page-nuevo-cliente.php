<?php
/**
 * Template Name: Nuevo Cliente
 *
 * @package CDC_Sistema
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<div class="cdc-nuevo-cliente">
    <div class="cdc-card">
        <div class="cdc-card-header">
            <h3 class="cdc-card-title">Nuevo Cliente</h3>
        </div>
        <div class="cdc-card-body">
            <form id="cdc-cliente-form">
                <h4 class="cdc-form-section-title">Datos personales</h4>

                <div class="cdc-form-row">
                    <div class="cdc-form-group cdc-form-col-6">
                        <label for="cdc-nombre">Nombre *</label>
                        <input type="text"
                               id="cdc-nombre"
                               class="cdc-form-control"
                               placeholder="Nombre"
                               required>
                    </div>
                    <div class="cdc-form-group cdc-form-col-6">
                        <label for="cdc-apellido">Apellido *</label>
                        <input type="text"
                               id="cdc-apellido"
                               class="cdc-form-control"
                               placeholder="Apellido"
                               required>
                    </div>
                </div>

                <div class="cdc-form-row">
                    <div class="cdc-form-group cdc-form-col-4">
                        <label for="cdc-dni">DNI *</label>
                        <input type="text"
                               id="cdc-dni"
                               class="cdc-form-control"
                               placeholder="12345678"
                               required>
                        <small class="cdc-text-muted">Sin puntos ni espacios</small>
                    </div>
                    <div class="cdc-form-group cdc-form-col-4">
                        <label for="cdc-tel">Teléfono</label>
                        <input type="tel"
                               id="cdc-tel"
                               class="cdc-form-control"
                               placeholder="3815123456">
                    </div>
                    <div class="cdc-form-group cdc-form-col-4">
                        <label for="cdc-email">Email</label>
                        <input type="email"
                               id="cdc-email"
                               class="cdc-form-control"
                               placeholder="email@ejemplo.com">
                    </div>
                </div>

                <div class="cdc-form-group">
                    <label for="cdc-domicilio">Domicilio</label>
                    <input type="text"
                           id="cdc-domicilio"
                           class="cdc-form-control"
                           placeholder="Dirección completa">
                </div>

                <div class="cdc-form-group">
                    <label for="cdc-observaciones">Observaciones</label>
                    <textarea id="cdc-observaciones"
                              class="cdc-form-control"
                              rows="3"
                              placeholder="Notas adicionales (opcional)"></textarea>
                </div>

                <div class="cdc-form-actions">
                    <button type="submit" class="cdc-button cdc-button-primary">
                        Crear cliente
                    </button>
                    <a href="<?php echo home_url('/personas'); ?>" class="cdc-button cdc-button-secondary">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#cdc-cliente-form').on('submit', function(e) {
        e.preventDefault();

        const dni = $('#cdc-dni').val();
        if (!CDC.validateDNI(dni)) {
            CDC.showNotification('DNI inválido', 'warning');
            return;
        }

        const $submitBtn = $(this).find('button[type="submit"]');
        CDC.showLoadingButton($submitBtn, true);

        const data = {
            tipo: 'cliente',
            nombre: $('#cdc-nombre').val(),
            apellido: $('#cdc-apellido').val(),
            dni: dni,
            tel: $('#cdc-tel').val(),
            email: $('#cdc-email').val(),
            domicilio: $('#cdc-domicilio').val(),
            observaciones: $('#cdc-observaciones').val()
        };

        CDCAPI.personas.create(data)
            .then(function(response) {
                CDC.showLoadingButton($submitBtn, false);
                if (response.success) {
                    CDC.showNotification('Cliente creado exitosamente', 'success');
                    setTimeout(function() {
                        window.location.href = cdcData.homeUrl + '/personas';
                    }, 1500);
                } else {
                    CDC.handleApiError(response, 'Create Cliente');
                }
            })
            .catch(function(error) {
                CDC.showLoadingButton($submitBtn, false);
                CDC.handleApiError(error, 'Create Cliente');
            });
    });
});
</script>

<?php get_footer(); ?>
