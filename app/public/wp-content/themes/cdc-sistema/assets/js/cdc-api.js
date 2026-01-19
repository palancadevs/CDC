/**
 * CDC Sistema - API Wrapper
 *
 * @package CDC_Sistema
 */

(function($) {
    'use strict';

    /**
     * CDC API Wrapper Object
     */
    const CDCAPI = {
        /**
         * Make API request
         *
         * @param {string} endpoint API endpoint (without base URL)
         * @param {string} method HTTP method (GET, POST, PUT, DELETE)
         * @param {object} data Request data
         * @return {Promise} Promise that resolves with API response
         */
        request: function(endpoint, method, data) {
            method = method || 'GET';
            data = data || null;

            return new Promise(function(resolve, reject) {
                const ajaxConfig = {
                    url: cdcData.apiUrl + endpoint,
                    method: method,
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', cdcData.nonce);
                    },
                    success: function(response) {
                        resolve(response);
                    },
                    error: function(xhr) {
                        // Handle 401 - session expired
                        if (xhr.status === 401) {
                            window.location.href = cdcData.homeUrl + '/login?error=session_expired';
                            return;
                        }

                        // Return error
                        const error = {
                            status: xhr.status,
                            message: 'Error en la solicitud',
                            data: null
                        };

                        if (xhr.responseJSON) {
                            error.message = xhr.responseJSON.message || error.message;
                            error.data = xhr.responseJSON.data || null;
                        }

                        reject(error);
                    }
                };

                // Add data based on method
                if (method === 'GET') {
                    ajaxConfig.data = data;
                } else {
                    ajaxConfig.data = JSON.stringify(data);
                    ajaxConfig.contentType = 'application/json';
                }

                $.ajax(ajaxConfig);
            });
        },

        /**
         * Personas endpoints
         */
        personas: {
            /**
             * List personas
             * @param {object} filters Filter parameters (tipo, query, per_page, offset)
             * @return {Promise}
             */
            list: function(filters) {
                filters = filters || {};
                return CDCAPI.request('personas', 'GET', filters);
            },

            /**
             * Search personas
             * @param {string} query Search query
             * @return {Promise}
             */
            search: function(query) {
                return CDCAPI.request('personas/search', 'GET', { query: query });
            },

            /**
             * Get single persona
             * @param {number} id Persona ID
             * @return {Promise}
             */
            get: function(id) {
                return CDCAPI.request('personas/' + id);
            },

            /**
             * Create new persona
             * @param {object} data Persona data
             * @return {Promise}
             */
            create: function(data) {
                return CDCAPI.request('personas', 'POST', data);
            },

            /**
             * Update persona
             * @param {number} id Persona ID
             * @param {object} data Updated data
             * @return {Promise}
             */
            update: function(id, data) {
                return CDCAPI.request('personas/' + id, 'PUT', data);
            }
        },

        /**
         * Recibos endpoints
         */
        recibos: {
            /**
             * Create new recibo
             * @param {object} data Recibo data
             * @return {Promise}
             */
            create: function(data) {
                return CDCAPI.request('recibos', 'POST', data);
            },

            /**
             * Get single recibo
             * @param {number} id Recibo ID
             * @return {Promise}
             */
            get: function(id) {
                return CDCAPI.request('recibos/' + id);
            },

            /**
             * Get recent recibos
             * @param {number} limit Number of recibos to fetch
             * @return {Promise}
             */
            recent: function(limit) {
                limit = limit || 10;
                return CDCAPI.request('recibos/recent', 'GET', { limit: limit });
            },

            /**
             * Get today's recibos
             * @return {Promise}
             */
            today: function() {
                return CDCAPI.request('recibos/today');
            },

            /**
             * Anular recibo
             * @param {number} id Recibo ID
             * @param {string} motivo Reason for cancellation
             * @return {Promise}
             */
            anular: function(id, motivo) {
                return CDCAPI.request('recibos/' + id + '/anular', 'POST', { motivo: motivo });
            }
        },

        /**
         * Caja endpoints
         */
        caja: {
            /**
             * Get current balance
             * @return {Promise}
             */
            balance: function() {
                return CDCAPI.request('caja/balance');
            },

            /**
             * Get today's movements
             * @return {Promise}
             */
            movements: function() {
                return CDCAPI.request('caja/movimientos/today');
            },

            /**
             * Get today's summary
             * @return {Promise}
             */
            summary: function() {
                return CDCAPI.request('caja/summary/today');
            },

            /**
             * Open cash register
             * @param {object} data Opening data (monto_inicial, notas)
             * @return {Promise}
             */
            apertura: function(data) {
                return CDCAPI.request('caja/apertura', 'POST', data);
            },

            /**
             * Close cash register
             * @param {object} data Closing data (notas)
             * @return {Promise}
             */
            cierre: function(data) {
                return CDCAPI.request('caja/cierre', 'POST', data);
            },

            /**
             * Create expense (gasto)
             * @param {object} data Expense data
             * @return {Promise}
             */
            createGasto: function(data) {
                return CDCAPI.request('caja/gastos', 'POST', data);
            }
        },

        /**
         * Talleres endpoints
         */
        talleres: {
            /**
             * List talleres
             * @param {object} filters Filter parameters (query, sala_id, estado)
             * @return {Promise}
             */
            list: function(filters) {
                filters = filters || {};
                return CDCAPI.request('talleres', 'GET', filters);
            },

            /**
             * Get single taller
             * @param {number} id Taller ID
             * @return {Promise}
             */
            get: function(id) {
                return CDCAPI.request('talleres/' + id);
            },

            /**
             * Create taller
             * @param {object} data Taller data
             * @return {Promise}
             */
            create: function(data) {
                return CDCAPI.request('talleres', 'POST', data);
            },

            /**
             * Update taller
             * @param {number} id Taller ID
             * @param {object} data Updated data
             * @return {Promise}
             */
            update: function(id, data) {
                return CDCAPI.request('talleres/' + id, 'PUT', data);
            }
        },

        /**
         * Eventos endpoints
         */
        eventos: {
            /**
             * List eventos
             * @return {Promise}
             */
            list: function() {
                return CDCAPI.request('eventos');
            },

            /**
             * Get single evento
             * @param {number} id Evento ID
             * @return {Promise}
             */
            get: function(id) {
                return CDCAPI.request('eventos/' + id);
            },

            /**
             * Create evento
             * @param {object} data Evento data
             * @return {Promise}
             */
            create: function(data) {
                return CDCAPI.request('eventos', 'POST', data);
            },

            /**
             * Update evento
             * @param {number} id Evento ID
             * @param {object} data Updated data
             * @return {Promise}
             */
            update: function(id, data) {
                return CDCAPI.request('eventos/' + id, 'PUT', data);
            }
        },

        /**
         * Salas endpoints
         */
        salas: {
            /**
             * List salas
             * @return {Promise}
             */
            list: function() {
                return CDCAPI.request('salas');
            },

            /**
             * Get single sala
             * @param {number} id Sala ID
             * @return {Promise}
             */
            get: function(id) {
                return CDCAPI.request('salas/' + id);
            },

            /**
             * Create sala
             * @param {object} data Sala data
             * @return {Promise}
             */
            create: function(data) {
                return CDCAPI.request('salas', 'POST', data);
            },

            /**
             * Update sala
             * @param {number} id Sala ID
             * @param {object} data Updated data
             * @return {Promise}
             */
            update: function(id, data) {
                return CDCAPI.request('salas/' + id, 'PUT', data);
            },

            /**
             * Create reservation
             * @param {object} data Reservation data
             * @return {Promise}
             */
            reservar: function(data) {
                return CDCAPI.request('salas/reservas', 'POST', data);
            }
        }
    };

    // Make CDCAPI globally available
    window.CDCAPI = CDCAPI;

})(jQuery);
