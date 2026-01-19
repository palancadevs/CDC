/**
 * CDC Sistema - Authentication JavaScript
 *
 * @package CDC_Sistema
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        const $form = $('#cdc-login-form');
        const $submitBtn = $('#cdc-login-submit');
        const $dniInput = $('#cdc-dni');
        const $errorBox = $('#cdc-login-error');
        const $loadingBox = $('#cdc-login-loading');

        // Focus DNI input
        $dniInput.focus();

        // Only allow numbers in DNI input
        $dniInput.on('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        // Handle form submission
        $form.on('submit', function(e) {
            e.preventDefault();

            const dni = $dniInput.val().trim();

            // Validate DNI
            if (!dni) {
                showError('Por favor ingrese su DNI');
                return;
            }

            if (dni.length < 7 || dni.length > 8) {
                showError('El DNI debe tener 7 u 8 dígitos');
                return;
            }

            // Show loading state
            showLoading();

            // Send AJAX request
            $.ajax({
                url: cdcAuthData.ajaxUrl,
                method: 'POST',
                data: {
                    action: 'cdc_login',
                    dni: dni,
                    nonce: cdcAuthData.loginNonce
                },
                success: function(response) {
                    if (response.success) {
                        // Success - redirect to dashboard
                        window.location.href = response.data.redirect;
                    } else {
                        // Error from server
                        hideLoading();
                        showError(response.data.message || 'Error al iniciar sesión');
                    }
                },
                error: function(xhr) {
                    hideLoading();

                    if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                        showError(xhr.responseJSON.data.message);
                    } else {
                        showError('Error de conexión. Por favor intente nuevamente.');
                    }
                }
            });
        });

        /**
         * Show error message
         */
        function showError(message) {
            $errorBox.html('<strong>Error:</strong> ' + message).slideDown();
            $submitBtn.prop('disabled', false);
            $dniInput.focus();
        }

        /**
         * Hide error message
         */
        function hideError() {
            $errorBox.slideUp();
        }

        /**
         * Show loading state
         */
        function showLoading() {
            hideError();
            $form.hide();
            $loadingBox.fadeIn();
            $submitBtn.prop('disabled', true);
        }

        /**
         * Hide loading state
         */
        function hideLoading() {
            $loadingBox.hide();
            $form.fadeIn();
            $submitBtn.prop('disabled', false);
        }

        // Hide error when user starts typing
        $dniInput.on('input', function() {
            if ($errorBox.is(':visible')) {
                hideError();
            }
        });
    });

})(jQuery);
