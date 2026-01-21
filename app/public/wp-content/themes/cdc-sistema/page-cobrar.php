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
            <!-- Cuotas grid for cuota-socio -->
            <div id="cdc-cuotas-grid" style="display: none; margin-bottom: 20px;">
                <h4>Seleccione las cuotas a cobrar:</h4>
                <div id="cdc-cuotas-list"></div>
                <div style="margin-top: 15px; padding: 15px; background: #f0f6fc; border-radius: 4px;">
                    <strong>Total a cobrar: $<span id="cdc-total-cuotas">0.00</span></strong>
                </div>
            </div>

            <form id="cdc-payment-form">
                <div class="cdc-form-group" id="cdc-monto-group">
                    <label for="cdc-monto">Monto *</label>
                    <input type="number"
                           id="cdc-monto"
                           class="cdc-form-control"
                           placeholder="0.00"
                           step="0.01"
                           required>
                    <small class="cdc-text-muted">Ingrese el monto a cobrar</small>
                </div>

                <div class="cdc-form-group" id="cdc-descripcion-group" style="display: none;">
                    <label for="cdc-descripcion">Descripción/Concepto *</label>
                    <input type="text"
                           id="cdc-descripcion"
                           class="cdc-form-control"
                           placeholder="Ej: Donación, Venta de merchandising, etc.">
                    <small class="cdc-text-muted">Describa brevemente el concepto del ingreso</small>
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

        // For "otro-ingreso", skip person selection
        if (selectedType === 'otro-ingreso') {
            $('#cdc-step-2').hide();
            $('#cdc-cuotas-grid').hide();
            $('#cdc-monto-group').show();
            $('#cdc-descripcion-group').show();
            $('#cdc-step-3').slideDown();
            $('html, body').animate({
                scrollTop: $('#cdc-step-3').offset().top - 100
            }, 500);
        } else {
            // Show step 2 for other types
            $('#cdc-step-2').slideDown();
            $('#cdc-step-3').hide();
            // Scroll to step 2
            $('html, body').animate({
                scrollTop: $('#cdc-step-2').offset().top - 100
            }, 500);
        }
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

        // If cuota-socio or cuota-taller, load pending cuotas
        if (selectedType === 'cuota-socio') {
            loadPendingCuotas(person.id);
        } else if (selectedType === 'cuota-taller') {
            loadPendingCuotasTaller(person.id);
        } else {
            // Show normal form
            $('#cdc-cuotas-grid').hide();
            $('#cdc-monto-group').show();
            $('#cdc-step-3').slideDown();
            $('html, body').animate({
                scrollTop: $('#cdc-step-3').offset().top - 100
            }, 500);
        }
    }

    // Load pending cuotas for socio
    function loadPendingCuotas(persona_id) {
        const $cuotasList = $('#cdc-cuotas-list');
        $cuotasList.html('<p class="cdc-text-muted">Cargando cuotas...</p>');

        CDCAPI.cobros.cuotasPendientes(persona_id)
            .then(function(response) {
                if (response.success) {
                    if (response.data && response.data.length > 0) {
                        renderCuotasGrid(response.data);
                        $('#cdc-cuotas-grid').show();
                        $('#cdc-monto-group').hide();
                        $('#cdc-step-3').slideDown();
                        $('html, body').animate({
                            scrollTop: $('#cdc-step-3').offset().top - 100
                        }, 500);
                    } else {
                        $cuotasList.html('<p class="cdc-text-muted">No hay cuotas pendientes.</p>');
                        CDC.showNotification('Este socio no tiene cuotas pendientes', 'info');
                    }
                } else {
                    CDC.handleApiError(response, 'Load Cuotas');
                }
            })
            .catch(function(error) {
                CDC.handleApiError(error, 'Load Cuotas');
            });
    }

    // Load pending cuotas taller
    function loadPendingCuotasTaller(persona_id) {
        const $cuotasList = $('#cdc-cuotas-list');
        $cuotasList.html('<p class="cdc-text-muted">Cargando cuotas de talleres...</p>');

        CDCAPI.cobros.cuotasTallerPendientes(persona_id)
            .then(function(response) {
                if (response.success) {
                    if (response.data && response.data.length > 0) {
                        renderCuotasTallerGrid(response.data);
                        $('#cdc-cuotas-grid').show();
                        $('#cdc-monto-group').hide();
                        $('#cdc-step-3').slideDown();
                        $('html, body').animate({
                            scrollTop: $('#cdc-step-3').offset().top - 100
                        }, 500);
                    } else {
                        $cuotasList.html('<p class="cdc-text-muted">No hay cuotas de talleres pendientes.</p>');
                        CDC.showNotification('Esta persona no tiene cuotas de talleres pendientes', 'info');
                    }
                } else {
                    CDC.handleApiError(response, 'Load Cuotas Taller');
                }
            })
            .catch(function(error) {
                CDC.handleApiError(error, 'Load Cuotas Taller');
            });
    }

    // Render cuotas grid with checkboxes
    function renderCuotasGrid(cuotas) {
        const meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                      'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

        let html = '<table class="cdc-table"><thead><tr>';
        html += '<th style="width: 50px;"><input type="checkbox" id="cdc-select-all-cuotas"></th>';
        html += '<th>Año</th><th>Mes</th><th>Monto</th>';
        html += '</tr></thead><tbody>';

        cuotas.forEach(function(cuota) {
            html += `<tr>
                <td><input type="checkbox" class="cdc-cuota-checkbox" data-cuota-id="${cuota.id}" data-monto="${cuota.monto}"></td>
                <td>${cuota.anio}</td>
                <td>${meses[parseInt(cuota.mes)]}</td>
                <td>$${parseFloat(cuota.monto).toFixed(2)}</td>
            </tr>`;
        });

        html += '</tbody></table>';
        $('#cdc-cuotas-list').html(html);

        // Bind checkbox events
        $('.cdc-cuota-checkbox').on('change', calculateTotalCuotas);
        $('#cdc-select-all-cuotas').on('change', function() {
            $('.cdc-cuota-checkbox').prop('checked', $(this).is(':checked')).trigger('change');
        });
    }

    // Calculate total from selected cuotas
    function calculateTotalCuotas() {
        let total = 0;
        $('.cdc-cuota-checkbox:checked').each(function() {
            total += parseFloat($(this).data('monto'));
        });
        $('#cdc-total-cuotas').text(total.toFixed(2));
    }

    // Render cuotas taller grid with checkboxes
    function renderCuotasTallerGrid(cuotas) {
        const meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                      'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

        let html = '<table class="cdc-table"><thead><tr>';
        html += '<th style="width: 50px;"><input type="checkbox" id="cdc-select-all-cuotas"></th>';
        html += '<th>Taller</th><th>Año</th><th>Mes</th><th>Monto</th>';
        html += '</tr></thead><tbody>';

        cuotas.forEach(function(cuota) {
            html += `<tr>
                <td><input type="checkbox" class="cdc-cuota-checkbox" data-cuota-id="${cuota.id}" data-monto="${cuota.monto}"></td>
                <td>${cuota.taller_nombre || 'Taller'}</td>
                <td>${cuota.anio}</td>
                <td>${meses[parseInt(cuota.mes)]}</td>
                <td>$${parseFloat(cuota.monto).toFixed(2)}</td>
            </tr>`;
        });

        html += '</tbody></table>';
        $('#cdc-cuotas-list').html(html);

        // Bind checkbox events
        $('.cdc-cuota-checkbox').on('change', calculateTotalCuotas);
        $('#cdc-select-all-cuotas').on('change', function() {
            $('.cdc-cuota-checkbox').prop('checked', $(this).is(':checked')).trigger('change');
        });
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

        const medio_pago = $('input[name="medio_pago"]:checked').val();
        const observaciones = $('#cdc-observaciones').val();

        // For "otro-ingreso", person is optional
        if (!selectedPerson && selectedType !== 'otro-ingreso') {
            CDC.showNotification('Seleccione una persona', 'warning');
            return;
        }

        const $submitBtn = $(this).find('button[type="submit"]');
        CDC.showLoadingButton($submitBtn, true);

        // Handle cuota-socio and cuota-taller differently
        if (selectedType === 'cuota-socio' || selectedType === 'cuota-taller') {
            // Get selected cuotas
            const selectedCuotas = [];
            $('.cdc-cuota-checkbox:checked').each(function() {
                selectedCuotas.push(parseInt($(this).data('cuota-id')));
            });

            if (selectedCuotas.length === 0) {
                CDC.showNotification('Seleccione al menos una cuota', 'warning');
                CDC.showLoadingButton($submitBtn, false);
                return;
            }

            const data = {
                persona_id: selectedPerson.id,
                cuota_ids: selectedCuotas,
                medio_pago: medio_pago,
                observaciones: observaciones
            };

            // Call appropriate API method
            const apiMethod = selectedType === 'cuota-socio'
                ? CDCAPI.cobros.cobrarCuotaSocio(data)
                : CDCAPI.cobros.cobrarCuotaTaller(data);

            apiMethod
                .then(function(response) {
                    CDC.showLoadingButton($submitBtn, false);
                    if (response.success) {
                        const tipoText = selectedType === 'cuota-socio' ? 'socio' : 'taller';
                        CDC.showNotification(`Cuota(s) de ${tipoText} cobrada(s) exitosamente`, 'success');
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
        } else if (selectedType === 'otro-ingreso') {
            // Handle "otro ingreso" (generic income without persona required)
            const monto = parseFloat($('#cdc-monto').val());
            const descripcion = $('#cdc-descripcion').val();

            if (!monto || monto <= 0) {
                CDC.showNotification('Ingrese un monto válido', 'warning');
                CDC.showLoadingButton($submitBtn, false);
                return;
            }

            if (!descripcion || descripcion.trim() === '') {
                CDC.showNotification('Ingrese una descripción', 'warning');
                CDC.showLoadingButton($submitBtn, false);
                return;
            }

            const data = {
                monto: monto,
                descripcion: descripcion,
                medio_pago: medio_pago,
                persona_id: selectedPerson ? selectedPerson.id : null,
                observaciones: observaciones
            };

            CDCAPI.cobros.cobrarOtroIngreso(data)
                .then(function(response) {
                    CDC.showLoadingButton($submitBtn, false);
                    if (response.success) {
                        CDC.showNotification('Ingreso registrado exitosamente', 'success');
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
        } else {
            // Handle other payment types (generic - entrada-evento, alquiler-sala)
            const monto = parseFloat($('#cdc-monto').val());

            if (!monto || monto <= 0) {
                CDC.showNotification('Ingrese un monto válido', 'warning');
                CDC.showLoadingButton($submitBtn, false);
                return;
            }

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
        }
    });

    // Cancel button
    $('#cdc-cancel-btn').on('click', function() {
        window.location.href = cdcData.homeUrl;
    });
});
</script>

<?php get_footer(); ?>
