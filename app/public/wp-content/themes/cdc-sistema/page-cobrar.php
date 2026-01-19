<?php
/**
 * Template Name: Cobrar
 *
 * @package CDC_Sistema
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<div class="cdc-cobrar">
    <!-- Step 1: Select Payment Type -->
    <div class="cdc-card">
        <div class="cdc-card-header">
            <h3 class="cdc-card-title">Paso 1) ¿Qué va a cobrar?</h3>
        </div>
        <div class="cdc-card-body">
            <div class="cdc-payment-types">
                <button class="cdc-payment-type-card" data-type="cuota-socio">
                    <span class="dashicons dashicons-groups"></span>
                    <strong>Cuota socio</strong>
                    <small>Cobrar cuota mensual de socio</small>
                </button>

                <button class="cdc-payment-type-card" data-type="cuota-taller">
                    <span class="dashicons dashicons-book-alt"></span>
                    <strong>Cuota taller</strong>
                    <small>Cobrar cuota de taller</small>
                </button>

                <button class="cdc-payment-type-card" data-type="entrada-evento">
                    <span class="dashicons dashicons-tickets-alt"></span>
                    <strong>Evento/entrada</strong>
                    <small>Cobrar entrada a evento</small>
                </button>

                <button class="cdc-payment-type-card" data-type="alquiler-sala">
                    <span class="dashicons dashicons-admin-multisite"></span>
                    <strong>Alquiler de sala</strong>
                    <small>Cobrar alquiler de sala</small>
                </button>

                <button class="cdc-payment-type-card" data-type="otro-ingreso">
                    <span class="dashicons dashicons-money-alt"></span>
                    <strong>Otro ingreso</strong>
                    <small>Registrar otro tipo de ingreso</small>
                </button>
            </div>
        </div>
    </div>

    <!-- Step 2: Select Person (hidden initially) -->
    <div class="cdc-card" id="cdc-step-2" style="display: none;">
        <div class="cdc-card-header">
            <h3 class="cdc-card-title">Paso 2) ¿A quién?</h3>
        </div>
        <div class="cdc-card-body">
            <div class="cdc-search-wrapper">
                <input type="text"
                       id="cdc-person-search"
                       class="cdc-form-control"
                       placeholder="Buscar socio/cliente por Nombre o DNI...">
                <button type="button" class="cdc-button cdc-button-primary" id="cdc-person-search-btn">
                    Buscar
                </button>
            </div>
            <div id="cdc-person-results" style="margin-top: 15px;"></div>
            <div id="cdc-selected-person" style="display: none; margin-top: 15px; padding: 15px; background: #f0f6fc; border-radius: 4px;">
                <p><strong>Persona seleccionada:</strong></p>
                <p id="cdc-selected-person-info"></p>
            </div>
        </div>
    </div>

    <!-- Step 3: Payment Details (hidden initially) -->
    <div class="cdc-card" id="cdc-step-3" style="display: none;">
        <div class="cdc-card-header">
            <h3 class="cdc-card-title">Paso 3) Datos del cobro</h3>
        </div>
        <div class="cdc-card-body">
            <form id="cdc-payment-form">
                <div class="cdc-form-group">
                    <label for="cdc-monto">Monto *</label>
                    <input type="number"
                           id="cdc-monto"
                           class="cdc-form-control"
                           placeholder="0.00"
                           step="0.01"
                           required>
                    <small class="cdc-text-muted">Ingrese el monto a cobrar</small>
                </div>

                <div class="cdc-form-group">
                    <label>Medio de pago *</label>
                    <div class="cdc-radio-group">
                        <label class="cdc-radio">
                            <input type="radio" name="medio_pago" value="efectivo" checked>
                            <span>Efectivo</span>
                        </label>
                        <label class="cdc-radio">
                            <input type="radio" name="medio_pago" value="transferencia">
                            <span>Transferencia</span>
                        </label>
                        <label class="cdc-radio">
                            <input type="radio" name="medio_pago" value="tarjeta">
                            <span>Tarjeta</span>
                        </label>
                    </div>
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
                        Confirmar cobro
                    </button>
                    <button type="button" class="cdc-button cdc-button-secondary" id="cdc-cancel-btn">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    let selectedType = '';
    let selectedPerson = null;

    // Step 1: Select payment type
    $('.cdc-payment-type-card').on('click', function() {
        $('.cdc-payment-type-card').removeClass('active');
        $(this).addClass('active');
        selectedType = $(this).data('type');

        // Show step 2
        $('#cdc-step-2').slideDown();

        // Scroll to step 2
        $('html, body').animate({
            scrollTop: $('#cdc-step-2').offset().top - 100
        }, 500);
    });

    // Step 2: Search person
    $('#cdc-person-search-btn').on('click', searchPerson);
    $('#cdc-person-search').on('keypress', function(e) {
        if (e.which === 13) {
            searchPerson();
        }
    });

    function searchPerson() {
        const query = $('#cdc-person-search').val();
        const $results = $('#cdc-person-results');

        if (query.length < 3) {
            CDC.showNotification('Por favor ingrese al menos 3 caracteres', 'warning');
            return;
        }

        $results.html('<p class="cdc-text-muted">Buscando...</p>');

        CDCAPI.personas.search(query)
            .then(function(response) {
                if (response.success) {
                    $results.html(CDC.renderPersonSearchResults(response.data, selectPerson));
                } else {
                    CDC.handleApiError(response, 'Person Search');
                }
            })
            .catch(function(error) {
                CDC.handleApiError(error, 'Person Search');
            });
    }

    // Select person
    function selectPerson(person) {
        selectedPerson = person;
        $('#cdc-selected-person-info').html(
            `<strong>${person.nombre} ${person.apellido}</strong> - DNI: ${person.dni}`
        );
        $('#cdc-selected-person').show();
        $('#cdc-person-results').hide();

        // Show step 3
        $('#cdc-step-3').slideDown();

        // Scroll to step 3
        $('html, body').animate({
            scrollTop: $('#cdc-step-3').offset().top - 100
        }, 500);
    }

    // Helper function to get concepto by type
    function getConceptoByType(type) {
        const map = {
            'cuota-socio': 'Cuota Socio',
            'cuota-taller': 'Cuota Taller',
            'entrada-evento': 'Entrada Evento',
            'alquiler-sala': 'Alquiler Sala',
            'otro-ingreso': 'Otro Ingreso'
        };
        return map[type] || 'Cobro';
    }

    // Step 3: Submit payment
    $('#cdc-payment-form').on('submit', function(e) {
        e.preventDefault();

        const monto = parseFloat($('#cdc-monto').val());
        const medio_pago = $('input[name="medio_pago"]:checked').val();
        const observaciones = $('#cdc-observaciones').val();

        if (!monto || monto <= 0) {
            CDC.showNotification('Ingrese un monto válido', 'warning');
            return;
        }

        if (!selectedPerson) {
            CDC.showNotification('Seleccione una persona', 'warning');
            return;
        }

        const $submitBtn = $(this).find('button[type="submit"]');
        CDC.showLoadingButton($submitBtn, true);

        const data = {
            persona_id: selectedPerson.id,
            tipo: selectedType,
            items: [{
                descripcion: getConceptoByType(selectedType),
                cantidad: 1,
                precio_unitario: monto,
                subtotal: monto
            }],
            concepto: getConceptoByType(selectedType),
            metodo_pago: medio_pago,
            notas: observaciones
        };

        CDCAPI.recibos.create(data)
            .then(function(response) {
                CDC.showLoadingButton($submitBtn, false);
                if (response.success) {
                    CDC.showNotification('Cobro registrado exitosamente', 'success');
                    setTimeout(function() {
                        window.location.href = cdcData.homeUrl;
                    }, 1500);
                } else {
                    CDC.handleApiError(response, 'Payment Processing');
                }
            })
            .catch(function(error) {
                CDC.showLoadingButton($submitBtn, false);
                CDC.handleApiError(error, 'Payment Processing');
            });
    });

    // Cancel button
    $('#cdc-cancel-btn').on('click', function() {
        window.location.href = cdcData.homeUrl;
    });
});
</script>

<?php get_footer(); ?>
