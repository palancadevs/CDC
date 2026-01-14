/**
 * CDC Admin Scripts
 *
 * Main JavaScript for CDC administration pages
 */

(function($) {
    'use strict';

    /**
     * CDC Admin Object
     */
    const cdcAdmin = {

        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
            this.initComponents();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Add your event handlers here
        },

        /**
         * Initialize components
         */
        initComponents: function() {
            this.initNotifications();
        },

        /**
         * Initialize notification system
         */
        initNotifications: function() {
            // Add notification container if it doesn't exist
            if ($('#cdc-notifications').length === 0) {
                $('body').append('<div id="cdc-notifications" class="cdc-notifications"></div>');
            }
        },

        /**
         * Show notification
         */
        notify: function(message, type = 'success', duration = 3000) {
            const $container = $('#cdc-notifications');
            const $notification = $('<div>')
                .addClass('cdc-notification')
                .addClass('cdc-notification-' + type)
                .html(message);

            $container.append($notification);

            // Slide in
            setTimeout(() => {
                $notification.addClass('show');
            }, 10);

            // Auto dismiss
            if (duration > 0) {
                setTimeout(() => {
                    this.dismissNotification($notification);
                }, duration);
            }
        },

        /**
         * Dismiss notification
         */
        dismissNotification: function($notification) {
            $notification.removeClass('show');
            setTimeout(() => {
                $notification.remove();
            }, 300);
        },

        /**
         * Show success message
         */
        success: function(message, duration = 3000) {
            this.notify(message, 'success', duration);
        },

        /**
         * Show error message
         */
        error: function(message, duration = 5000) {
            this.notify(message, 'error', duration);
        },

        /**
         * Show warning message
         */
        warning: function(message, duration = 4000) {
            this.notify(message, 'warning', duration);
        },

        /**
         * Show info message
         */
        info: function(message, duration = 3000) {
            this.notify(message, 'info', duration);
        },

        /**
         * Confirm dialog
         */
        confirm: function(message, callback) {
            if (confirm(message)) {
                callback();
            }
        },

        /**
         * AJAX request helper
         */
        ajax: function(endpoint, method = 'GET', data = {}) {
            return $.ajax({
                url: window.cdcAdmin.restUrl + endpoint,
                method: method,
                data: data,
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', window.cdcAdmin.nonce);
                }
            });
        },

        /**
         * Format currency
         */
        formatCurrency: function(amount) {
            return '$' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        },

        /**
         * Format date
         */
        formatDate: function(date) {
            const d = new Date(date);
            const day = String(d.getDate()).padStart(2, '0');
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const year = d.getFullYear();
            return `${day}/${month}/${year}`;
        },

        /**
         * Format time
         */
        formatTime: function(time) {
            const d = new Date(time);
            const hours = String(d.getHours()).padStart(2, '0');
            const minutes = String(d.getMinutes()).padStart(2, '0');
            return `${hours}:${minutes}`;
        },

        /**
         * Validate form
         */
        validateForm: function($form) {
            let isValid = true;

            // Check required fields
            $form.find('[required]').each(function() {
                const $field = $(this);
                const value = $field.val();

                if (!value || value.trim() === '') {
                    $field.addClass('error');
                    isValid = false;
                } else {
                    $field.removeClass('error');
                }
            });

            return isValid;
        },

        /**
         * Show loading overlay
         */
        showLoading: function(message = 'Cargando...') {
            const $overlay = $('<div>')
                .addClass('cdc-loading-overlay')
                .html(`
                    <div class="cdc-loading-spinner">
                        <div class="spinner is-active"></div>
                        <p>${message}</p>
                    </div>
                `);

            $('body').append($overlay);
        },

        /**
         * Hide loading overlay
         */
        hideLoading: function() {
            $('.cdc-loading-overlay').remove();
        },

        /**
         * Debounce function
         */
        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
    };

    /**
     * Make cdcAdmin globally available
     */
    window.cdcAdmin = window.cdcAdmin || {};
    window.cdcAdmin.app = cdcAdmin;

    /**
     * Initialize when DOM is ready
     */
    $(document).ready(function() {
        cdcAdmin.init();
    });

})(jQuery);
