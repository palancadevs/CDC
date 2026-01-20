<?php
/**
 * Template Name: Nuevo Socio
 *
 * @package CDC_Sistema
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<div class="cdc-nuevo-socio">
    <div class="cdc-card">
        <div class="cdc-card-header">
            <h3 class="cdc-card-title">Nuevo Socio</h3>
        </div>
        <div class="cdc-card-body">
            <form id="cdc-socio-form">
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

                <h4 class="cdc-form-section-title" style="margin-top: 30px;">Categoría</h4>

                <div class="cdc-form-row">
                    <div class="cdc-form-group cdc-form-col-6">
                        <label for="cdc-categoria">Categoría</label>
                        <select id="cdc-categoria" class="cdc-form-control">
                            <option value="">Seleccionar...</option>
                            <option value="general">General</option>
                            <option value="estudiante">Estudiante</option>
                            <option value="jubilado">Jubilado</option>
                            <option value="especial">Especial</option>
                        </select>
                    </div>
                    <div class="cdc-form-group cdc-form-col-6">
                        <label for="cdc-subcategoria">Subcategoría</label>
                        <select id="cdc-subcategoria" class="cdc-form-control">
                            <option value="">Seleccionar...</option>
                            <option value="activo">Activo</option>
                            <option value="honorario">Honorario</option>
                        </select>
                    </div>
                </div>

                <div class="cdc-form-group">
                    <label for="cdc-observaciones">Observaciones</label>
                    <textarea id="cdc-observaciones"
                              class="cdc-form-control"
                              rows="3"
                              placeholder="Notas adicionales (opcional)"></textarea>
                </div>

                <h4 class="cdc-form-section-title" style="margin-top: 30px;">Cuotas</h4>

                <div class="cdc-form-group">
                    <label class="cdc-checkbox-label">
                        <input type="checkbox"
                               id="cdc-generar-cuotas"
                               class="cdc-checkbox"
                               checked>
                        <span>Generar planilla de cuotas del año actual (12 meses)</span>
                    </label>
                    <small class="cdc-text-muted">
                        Se crearán 12 registros de cuotas mensuales desde el mes actual hasta diciembre.
                    </small>
                </div>

                <div class="cdc-form-actions">
                    <button type="submit" class="cdc-button cdc-button-primary">
                        Crear socio
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
    console.log('🟢 Formulario Nuevo Socio cargado');
    console.log('🔍 CDCAPI disponible:', typeof CDCAPI !== 'undefined');
    console.log('🔍 CDC disponible:', typeof CDC !== 'undefined');
    console.log('🔍 cdcData:', cdcData);

    $('#cdc-socio-form').on('submit', function(e) {
        e.preventDefault();
        console.log('📝 Formulario enviado');

        const dni = $('#cdc-dni').val();
        console.log('🔍 DNI ingresado:', dni);

        if (!CDC.validateDNI(dni)) {
            console.error('❌ DNI inválido:', dni);
            CDC.showNotification('DNI inválido', 'warning');
            return;
        }

        const $submitBtn = $(this).find('button[type="submit"]');
        CDC.showLoadingButton($submitBtn, true);

        const data = {
            tipo: 'socio',
            nombre: $('#cdc-nombre').val(),
            apellido: $('#cdc-apellido').val(),
            dni: dni,
            telefono: $('#cdc-tel').val(),
            email: $('#cdc-email').val(),
            direccion: $('#cdc-domicilio').val(),
            notas: $('#cdc-observaciones').val(),
            generar_cuotas: $('#cdc-generar-cuotas').is(':checked')
        };

        console.log('📤 Enviando datos:', data);

        CDCAPI.personas.create(data)
            .then(function(response) {
                console.log('📥 Respuesta recibida:', response);
                CDC.showLoadingButton($submitBtn, false);

                if (response.success) {
                    console.log('✅ Socio creado exitosamente:', response.data);
                    CDC.showNotification('Socio creado exitosamente', 'success');
                    setTimeout(function() {
                        console.log('🔄 Redirigiendo a /personas');
                        window.location.href = cdcData.homeUrl + '/personas';
                    }, 1500);
                } else {
                    console.error('❌ Error en la respuesta:', response);
                    CDC.handleApiError(response, 'Create Socio');
                }
            })
            .catch(function(error) {
                console.error('❌ Error en la petición:', error);
                CDC.showLoadingButton($submitBtn, false);
                CDC.handleApiError(error, 'Create Socio');
            });
    });
});
</script>

<?php get_footer(); ?>
