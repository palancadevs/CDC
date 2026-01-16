<?php
/**
 * Session Guard - Protect pages and check authentication
 *
 * @package CDC_Sistema
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check authentication and redirect if necessary
 */
function cdc_check_authentication() {
    // Skip for AJAX requests
    if (defined('DOING_AJAX') && DOING_AJAX) {
        return;
    }

    // Skip for REST API requests
    if (defined('REST_REQUEST') && REST_REQUEST) {
        return;
    }

    // Skip for admin pages
    if (is_admin()) {
        return;
    }

    // Get current URL path
    $current_path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    $home_path = trim(parse_url(home_url(), PHP_URL_PATH), '/');

    // Remove home path from current path
    if ($home_path && strpos($current_path, $home_path) === 0) {
        $current_path = trim(substr($current_path, strlen($home_path)), '/');
    }

    // Check if we're on the login page (by URL or by is_page check)
    $is_login_page = (is_page('login') || $current_path === 'login');

    if ($is_login_page) {
        // If already logged in, redirect to dashboard
        if (is_user_logged_in()) {
            wp_redirect(home_url('/'));
            exit;
        }
        // Allow access to login page
        return;
    }

    // All other pages require authentication
    if (!is_user_logged_in()) {
        // Prevent redirect loop
        if ($current_path === 'login') {
            return;
        }
        wp_redirect(home_url('/login'));
        exit;
    }

    // Verify user has persona linked
    $persona_id = get_user_meta(get_current_user_id(), 'cdc_persona_id', true);

    if (!$persona_id) {
        // User exists but has no persona - logout and redirect
        wp_logout();
        wp_redirect(home_url('/login?error=no_persona'));
        exit;
    }

    // Verify persona still exists in database
    global $wpdb;
    $persona_exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}cdc_personas WHERE id = %d",
        $persona_id
    ));

    if (!$persona_exists) {
        // Persona was deleted - logout and redirect
        wp_logout();
        wp_redirect(home_url('/login?error=persona_deleted'));
        exit;
    }
}
add_action('template_redirect', 'cdc_check_authentication');

/**
 * Modify logout redirect URL
 *
 * @param string $logout_url The logout URL
 * @return string Modified logout URL
 */
function cdc_logout_redirect($logout_url) {
    return add_query_arg('redirect_to', home_url('/login'), $logout_url);
}
add_filter('logout_url', 'cdc_logout_redirect');

/**
 * Show error messages on login page if redirected with error
 */
function cdc_login_error_messages() {
    if (!is_page('login')) {
        return;
    }

    if (isset($_GET['error'])) {
        $error = sanitize_text_field($_GET['error']);
        $message = '';

        switch ($error) {
            case 'no_persona':
                $message = 'Su usuario no está vinculado a una persona. Contacte al administrador.';
                break;
            case 'persona_deleted':
                $message = 'Su registro fue eliminado del sistema. Contacte al administrador.';
                break;
            case 'session_expired':
                $message = 'Su sesión ha expirado. Por favor ingrese nuevamente.';
                break;
            default:
                $message = 'Error de autenticación. Por favor intente nuevamente.';
        }

        if ($message) {
            echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    var errorBox = document.getElementById("cdc-login-error");
                    if (errorBox) {
                        errorBox.innerHTML = "<strong>Error:</strong> ' . esc_js($message) . '";
                        errorBox.style.display = "block";
                    }
                });
            </script>';
        }
    }
}
add_action('wp_footer', 'cdc_login_error_messages');
