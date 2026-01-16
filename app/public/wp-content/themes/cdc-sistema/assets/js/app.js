/**
 * CDC Sistema - Main JavaScript
 *
 * @package CDC_Sistema
 */

(function($) {
    'use strict';

    /**
     * CDC App Object
     */
    const CDC = {
        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
            this.initDashboard();
        },

        /**
         * Bind global events
         */
        bindEvents: function() {
            // Add any global event bindings here
        },

        /**
         * Initialize Dashboard
         */
        initDashboard: function() {
            if ($('#cdc-quick-search').length) {
                this.initQuickSearch();
                this.loadRecentMovements();
            }
        },

        /**
         * Quick Search Functionality
         */
        initQuickSearch: function() {
            $('#cdc-quick-search').on('submit', function(e) {
                e.preventDefault();

                const query = $('#cdc-search-query').val().trim();
                const $results = $('#cdc-search-results');

                // Validation
                if (query.length < 3) {
                    CDC.showNotification('Por favor ingrese al menos 3 caracteres para buscar.', 'warning');
                    return;
                }

                // Show loading state
                $results.html('<p class="cdc-text-muted">Buscando...</p>');

                // TODO: Replace with actual API call when cdc-api plugin is ready
                // This is a placeholder for Phase 1
                setTimeout(function() {
                    $results.html(`
                        <div class="cdc-search-placeholder">
                            <p class="cdc-text-muted">
                                <strong>Búsqueda:</strong> "${query}"
                            </p>
                            <p class="cdc-text-muted">
                                La búsqueda estará disponible cuando se implemente el plugin cdc-api.
                            </p>
                            <p class="cdc-text-muted" style="font-size: 12px; margin-top: 10px;">
                                <strong>Siguiente fase:</strong> Integración con REST API para búsqueda de personas por nombre, apellido o DNI.
                            </p>
                        </div>
                    `);
                }, 500);

                /* FUTURE IMPLEMENTATION:
                $.ajax({
                    url: cdcData.apiUrl + 'personas/search',
                    method: 'GET',
                    data: { query: query },
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', cdcData.nonce);
                    },
                    success: function(response) {
                        CDC.displaySearchResults(response.data);
                    },
                    error: function(xhr) {
                        CDC.showNotification('Error al buscar. Por favor intente nuevamente.', 'error');
                    }
                });
                */
            });

            // Clear results when input is cleared
            $('#cdc-search-query').on('input', function() {
                if ($(this).val().length === 0) {
                    $('#cdc-search-results').empty();
                }
            });
        },

        /**
         * Display search results
         * @param {Array} results - Search results from API
         */
        displaySearchResults: function(results) {
            const $results = $('#cdc-search-results');

            if (!results || results.length === 0) {
                $results.html('<p class="cdc-text-muted">No se encontraron resultados.</p>');
                return;
            }

            let html = '<div class="cdc-search-results-list">';

            results.forEach(function(person) {
                html += `
                    <div class="cdc-search-result-item">
                        <div class="cdc-search-result-info">
                            <strong>${person.nombre} ${person.apellido}</strong>
                            <span class="cdc-text-muted"> - DNI: ${person.dni}</span>
                        </div>
                        <div class="cdc-search-result-actions">
                            <a href="${cdcData.homeUrl}/personas?id=${person.id}" class="cdc-button cdc-button-small">
                                Ver Perfil
                            </a>
                        </div>
                    </div>
                `;
            });

            html += '</div>';
            $results.html(html);
        },

        /**
         * Load Recent Movements
         */
        loadRecentMovements: function() {
            const $container = $('#cdc-recent-movements');

            if (!$container.length) {
                return;
            }

            // TODO: Replace with actual API call when cdc-api plugin is ready
            setTimeout(function() {
                $container.html(`
                    <p class="cdc-text-center cdc-text-muted">
                        No hay movimientos registrados hoy.
                    </p>
                    <p class="cdc-text-center cdc-text-muted" style="font-size: 12px; margin-top: 10px;">
                        Los movimientos se mostrarán aquí una vez que el módulo de Caja esté implementado.
                    </p>
                `);
            }, 500);

            /* FUTURE IMPLEMENTATION:
            $.ajax({
                url: cdcData.apiUrl + 'movimientos/recientes',
                method: 'GET',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', cdcData.nonce);
                },
                success: function(response) {
                    CDC.displayRecentMovements(response.data);
                },
                error: function(xhr) {
                    $container.html('<p class="cdc-text-center cdc-text-muted">Error al cargar movimientos.</p>');
                }
            });
            */
        },

        /**
         * Display recent movements
         * @param {Array} movements - Recent movements from API
         */
        displayRecentMovements: function(movements) {
            const $container = $('#cdc-recent-movements');

            if (!movements || movements.length === 0) {
                $container.html('<p class="cdc-text-center cdc-text-muted">No hay movimientos registrados hoy.</p>');
                return;
            }

            let html = '<table class="cdc-movements-table"><thead><tr>';
            html += '<th>Hora</th><th>Tipo</th><th>Concepto</th><th>Monto</th><th>Usuario</th>';
            html += '</tr></thead><tbody>';

            movements.forEach(function(mov) {
                const tipo = mov.tipo === 'ingreso' ? 'Ingreso' : 'Egreso';
                const clase = mov.tipo === 'ingreso' ? 'cdc-ingreso' : 'cdc-egreso';

                html += `
                    <tr>
                        <td>${mov.hora}</td>
                        <td><span class="${clase}">${tipo}</span></td>
                        <td>${mov.concepto}</td>
                        <td class="${clase}">$${mov.monto}</td>
                        <td>${mov.usuario}</td>
                    </tr>
                `;
            });

            html += '</tbody></table>';
            $container.html(html);
        },

        /**
         * Show Notification
         * @param {string} message - Notification message
         * @param {string} type - Notification type (success, error, warning, info)
         */
        showNotification: function(message, type) {
            type = type || 'info';

            // For now, use alert (will be replaced with better notification system)
            alert(message);

            /* FUTURE IMPLEMENTATION: Toast notifications
            const $notification = $(`
                <div class="cdc-notification cdc-notification-${type}">
                    <span class="dashicons dashicons-${this.getNotificationIcon(type)}"></span>
                    <span class="cdc-notification-message">${message}</span>
                    <button class="cdc-notification-close">&times;</button>
                </div>
            `);

            $('body').append($notification);

            setTimeout(function() {
                $notification.addClass('cdc-notification-show');
            }, 100);

            setTimeout(function() {
                $notification.removeClass('cdc-notification-show');
                setTimeout(function() {
                    $notification.remove();
                }, 300);
            }, 3000);

            $notification.find('.cdc-notification-close').on('click', function() {
                $notification.removeClass('cdc-notification-show');
                setTimeout(function() {
                    $notification.remove();
                }, 300);
            });
            */
        },

        /**
         * Get notification icon based on type
         * @param {string} type - Notification type
         * @return {string} Dashicon name
         */
        getNotificationIcon: function(type) {
            const icons = {
                success: 'yes-alt',
                error: 'dismiss',
                warning: 'warning',
                info: 'info'
            };
            return icons[type] || 'info';
        },

        /**
         * AJAX Helper
         * @param {string} endpoint - API endpoint
         * @param {string} method - HTTP method
         * @param {object} data - Request data
         * @param {function} callback - Success callback
         * @param {function} errorCallback - Error callback
         */
        ajax: function(endpoint, method, data, callback, errorCallback) {
            $.ajax({
                url: cdcData.apiUrl + endpoint,
                method: method || 'GET',
                data: data || {},
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', cdcData.nonce);
                },
                success: function(response) {
                    if (callback) callback(response);
                },
                error: function(xhr) {
                    if (errorCallback) {
                        errorCallback(xhr);
                    } else {
                        CDC.showNotification('Error en la solicitud. Por favor intente nuevamente.', 'error');
                    }
                }
            });
        },

        /**
         * Format currency
         * @param {number} amount - Amount to format
         * @return {string} Formatted currency
         */
        formatCurrency: function(amount) {
            return '$' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        },

        /**
         * Format date
         * @param {string} date - Date string
         * @return {string} Formatted date
         */
        formatDate: function(date) {
            const d = new Date(date);
            const day = ('0' + d.getDate()).slice(-2);
            const month = ('0' + (d.getMonth() + 1)).slice(-2);
            const year = d.getFullYear();
            return `${day}/${month}/${year}`;
        },

        /**
         * Validate DNI
         * @param {string} dni - DNI to validate
         * @return {boolean} Is valid
         */
        validateDNI: function(dni) {
            // DNI should be 7-8 digits
            return /^\d{7,8}$/.test(dni);
        },

        /**
         * Validate Email
         * @param {string} email - Email to validate
         * @return {boolean} Is valid
         */
        validateEmail: function(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        }
    };

    /**
     * Initialize on document ready
     */
    $(document).ready(function() {
        CDC.init();
    });

    // Make CDC object globally available
    window.CDC = CDC;

})(jQuery);
