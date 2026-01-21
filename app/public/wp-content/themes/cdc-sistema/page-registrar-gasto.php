<?php
/**
 * Template Name: Registrar Gasto
 *
 * @package CDC_Sistema
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<div class="cdc-registrar-gasto">
    <!-- Header -->
    <div class="cdc-page-header">
        <div>
            <h1 class="cdc-page-title">Registrar Gasto</h1>
            <p class="cdc-text-muted">Registrar un egreso de caja</p>
        </div>
        <div class="cdc-page-header-actions">
            <a href="<?php echo esc_url(home_url('/caja')); ?>" class="cdc-button cdc-button-secondary">
                <span class="dashicons dashicons-arrow-left-alt"></span> Volver a Caja
            </a>
        </div>
    </div>

    <div class="cdc-card">
        <div class="cdc-card-body">
            <form id="cdc-gasto-form">
                <div class="cdc-form-row">
                    <div class="cdc-form-group cdc-form-col-6">
                        <label for="cdc-fecha">Fecha *</label>
                        <input type="date"
                               id="cdc-fecha"
                               class="cdc-form-control"
                               value="<?php echo date('Y-m-d'); ?>"
                               required>
                    </div>
                    <div class="cdc-form-group cdc-form-col-6">
                        <label for="cdc-hora">Hora *</label>
                        <input type="time"
                               id="cdc-hora"
                               class="cdc-form-control"
                               value="<?php echo date('H:i'); ?>"
                               required>
                    </div>
                </div>

                <div class="cdc-form-group">
                    <label for="cdc-monto">Monto *</label>
                    <input type="number"
                           id="cdc-monto"
                           class="cdc-form-control"
                           placeholder="0.00"
                           step="0.01"
                           required>
                </div>

                <div class="cdc-form-group">
                    <label for="cdc-descripcion">Detalle / Descripción *</label>
                    <textarea id="cdc-descripcion"
                              class="cdc-form-control"
                              rows="3"
                              placeholder="Ej: Compra de insumos para..."
                              required></textarea>
                </div>

                <div class="cdc-form-group">
                    <label for="cdc-categoria">Categoría</label>
                    <select id="cdc-categoria" class="cdc-form-control">
                        <option value="">Seleccionar...</option>
                        <option value="servicios">Servicios</option>
                        <option value="mantenimiento">Mantenimiento</option>
                        <option value="compras">Compras</option>
                        <option value="sueldos">Sueldos</option>
                        <option value="impuestos">Impuestos</option>
                        <option value="otros">Otros</option>
                    </select>
                </div>

                <div class="cdc-form-group">
                    <label for="cdc-medio-pago">Medio de pago *</label>
                    <select id="cdc-medio-pago" class="cdc-form-control" required>
                        <option value="">Seleccionar...</option>
                        <option value="efectivo">Efectivo</option>
                        <option value="transferencia">Transferencia</option>
                        <option value="tarjeta">Tarjeta</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>

                <div class="cdc-form-group">
                    <label for="cdc-comprobante">Comprobante (adjunto)</label>
                    <input type="file"
                           id="cdc-comprobante"
                           class="cdc-form-control"
                           accept="image/*,application/pdf">
                    <small class="cdc-text-muted">Opcional: Adjuntar foto o PDF del comprobante</small>
                </div>

                <div class="cdc-form-group">
                    <label for="cdc-observaciones">Observaciones</label>
                    <textarea id="cdc-observaciones"
                              class="cdc-form-control"
                              rows="2"
                              placeholder="Notas adicionales (opcional)"></textarea>
                </div>

                <div class="cdc-form-actions">
                    <button type="submit" class="cdc-button cdc-button-primary">
                        <span class="dashicons dashicons-saved"></span> Guardar gasto
                    </button>
                    <a href="<?php echo esc_url(home_url('/caja')); ?>" class="cdc-button cdc-button-secondary">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#cdc-gasto-form').on('submit', function(e) {
        e.preventDefault();

        const fecha = $('#cdc-fecha').val();
        const hora = $('#cdc-hora').val();
        const monto = parseFloat($('#cdc-monto').val());
        const descripcion = $('#cdc-descripcion').val();
        const categoria = $('#cdc-categoria').val();
        const medio_pago = $('#cdc-medio-pago').val();
        const observaciones = $('#cdc-observaciones').val();

        // Validation
        if (!monto || monto <= 0) {
            CDC.showNotification('Ingrese un monto válido', 'warning');
            return;
        }

        if (!descripcion) {
            CDC.showNotification('Ingrese una descripción', 'warning');
            return;
        }

        if (!medio_pago) {
            CDC.showNotification('Seleccione un medio de pago', 'warning');
            return;
        }

        const $submitBtn = $(this).find('button[type="submit"]');
        CDC.showLoadingButton($submitBtn, true);

        const data = {
            fecha_hora: `${fecha} ${hora}`,
            tipo: 'egreso',
            monto: monto,
            descripcion: descripcion,
            categoria: categoria,
            medio_pago: medio_pago,
            observaciones: observaciones
        };

        CDCAPI.caja.createGasto(data)
            .then(function(response) {
                CDC.showLoadingButton($submitBtn, false);
                if (response.success) {
                    CDC.showNotification('Gasto registrado exitosamente', 'success');
                    setTimeout(function() {
                        window.location.href = cdcData.homeUrl + '/caja';
                    }, 1500);
                } else {
                    CDC.handleApiError(response, 'Registrar Gasto');
                }
            })
            .catch(function(error) {
                CDC.showLoadingButton($submitBtn, false);
                CDC.handleApiError(error, 'Registrar Gasto');
            });
    });
});
</script>

<?php get_footer(); ?>
