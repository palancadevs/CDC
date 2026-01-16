<?php
/**
 * Authentication Functions
 *
 * @package CDC_Sistema
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Authenticate user by DNI
 *
 * @param string $dni DNI to authenticate
 * @return array Success/error response with user data
 */
function cdc_authenticate_by_dni($dni) {
    global $wpdb;

    // Sanitize DNI
    $dni = sanitize_text_field($dni);

    // Validate DNI format (7-8 digits)
    if (!preg_match('/^\d{7,8}$/', $dni)) {
        return array(
            'success' => false,
            'message' => 'DNI inválido. Debe contener 7 u 8 dígitos.'
        );
    }

    // Find persona by DNI
    $persona = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}cdc_personas WHERE dni = %s",
        $dni
    ));

    if (!$persona) {
        return array(
            'success' => false,
            'message' => 'DNI no encontrado en el sistema.'
        );
    }

    // Check if WordPress user already exists with this DNI
    $user = get_user_by('login', $dni);

    if (!$user) {
        // Create new WordPress user
        $user_data = array(
            'user_login'   => $dni,
            'user_pass'    => wp_generate_password(20, true, true),
            'user_email'   => $persona->email ?: $dni . '@cdc.local',
            'display_name' => $persona->nombre . ' ' . $persona->apellido,
            'first_name'   => $persona->nombre,
            'last_name'    => $persona->apellido,
            'role'         => 'cdc_user'
        );

        $user_id = wp_insert_user($user_data);

        if (is_wp_error($user_id)) {
            return array(
                'success' => false,
                'message' => 'Error al crear usuario: ' . $user_id->get_error_message()
            );
        }

        // Link persona to WordPress user
        update_user_meta($user_id, 'cdc_persona_id', $persona->id);
        update_user_meta($user_id, 'cdc_persona_tipo', $persona->tipo);

        // Get the user object
        $user = get_user_by('id', $user_id);
    }

    // Verify persona link exists
    $persona_id = get_user_meta($user->ID, 'cdc_persona_id', true);
    if (!$persona_id) {
        // Re-link if missing
        update_user_meta($user->ID, 'cdc_persona_id', $persona->id);
        update_user_meta($user->ID, 'cdc_persona_tipo', $persona->tipo);
    }

    // Set current user
    wp_clear_auth_cookie();
    wp_set_current_user($user->ID);
    wp_set_auth_cookie($user->ID, true); // true = remember me
    do_action('wp_login', $user->user_login, $user);

    return array(
        'success' => true,
        'user'    => $user,
        'persona' => $persona
    );
}

/**
 * Get current logged-in user's persona data
 *
 * @return object|null Persona object or null if not found
 */
function cdc_get_current_persona() {
    if (!is_user_logged_in()) {
        return null;
    }

    $persona_id = get_user_meta(get_current_user_id(), 'cdc_persona_id', true);

    if (!$persona_id) {
        return null;
    }

    global $wpdb;
    $persona = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}cdc_personas WHERE id = %d",
        $persona_id
    ));

    return $persona;
}

/**
 * Get persona type for current user
 *
 * @return string Persona type (socio, cliente, ambos) or empty string
 */
function cdc_get_current_persona_tipo() {
    if (!is_user_logged_in()) {
        return '';
    }

    return get_user_meta(get_current_user_id(), 'cdc_persona_tipo', true) ?: '';
}

/**
 * Check if current user is a socio
 *
 * @return bool True if socio, false otherwise
 */
function cdc_is_socio() {
    $tipo = cdc_get_current_persona_tipo();
    return in_array($tipo, array('socio', 'ambos'));
}

/**
 * AJAX handler for login
 */
function cdc_ajax_login() {
    // Verify nonce
    check_ajax_referer('cdc_login_nonce', 'nonce');

    // Get DNI from POST
    $dni = isset($_POST['dni']) ? sanitize_text_field($_POST['dni']) : '';

    if (empty($dni)) {
        wp_send_json_error(array('message' => 'Por favor ingrese su DNI.'));
    }

    // Attempt authentication
    $result = cdc_authenticate_by_dni($dni);

    if ($result['success']) {
        wp_send_json_success(array(
            'redirect' => home_url('/'),
            'message'  => 'Ingreso exitoso'
        ));
    } else {
        wp_send_json_error(array('message' => $result['message']));
    }
}
add_action('wp_ajax_nopriv_cdc_login', 'cdc_ajax_login');
add_action('wp_ajax_cdc_login', 'cdc_ajax_login'); // Also allow for already logged-in users

/**
 * Create custom user role for CDC users
 */
function cdc_create_user_role() {
    // Check if role already exists
    if (get_role('cdc_user')) {
        return;
    }

    add_role('cdc_user', 'CDC User', array(
        'read' => true,
        // No other permissions - this is a restricted role
    ));
}
add_action('init', 'cdc_create_user_role');

/**
 * Override display name with persona name
 *
 * @param string $display_name Original display name
 * @param int $user_id User ID
 * @param object $user User object
 * @return string Modified display name
 */
function cdc_override_display_name($display_name, $user_id, $user) {
    $persona = cdc_get_current_persona();

    if ($persona) {
        return $persona->nombre . ' ' . $persona->apellido;
    }

    return $display_name;
}
add_filter('pre_user_display_name', 'cdc_override_display_name', 10, 3);
