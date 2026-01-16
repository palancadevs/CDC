<?php
/**
 * Authentication System
 *
 * Custom login with DNI as username
 *
 * @package CDC_Sistema
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Authenticate user with DNI and password
 *
 * @param string $dni
 * @param string $password
 * @return bool|WP_Error
 */
function cdc_authenticate_user($dni, $password) {
    // Buscar usuario por DNI (en meta_key 'dni')
    $users = get_users(array(
        'meta_key' => 'dni',
        'meta_value' => $dni,
        'number' => 1,
    ));

    // Si no encuentra por DNI, intentar por username
    if (empty($users)) {
        $user = get_user_by('login', $dni);
        if (!$user) {
            return new WP_Error('invalid_dni', __('DNI no encontrado.', 'cdc-sistema'));
        }
    } else {
        $user = $users[0];
    }

    // Verificar contraseña
    if (!wp_check_password($password, $user->data->user_pass, $user->ID)) {
        return new WP_Error('invalid_password', __('Contraseña incorrecta.', 'cdc-sistema'));
    }

    // Verificar que tenga rol permitido
    $allowed_roles = array('administrator', 'cdc_tesoreria', 'cdc_recepcion');
    $user_roles = $user->roles;
    $has_access = false;

    foreach ($allowed_roles as $role) {
        if (in_array($role, $user_roles)) {
            $has_access = true;
            break;
        }
    }

    if (!$has_access) {
        return new WP_Error('no_access', __('No tiene permisos para acceder al sistema.', 'cdc-sistema'));
    }

    // Login exitoso - crear sesión
    $_SESSION['cdc_user_id'] = $user->ID;
    $_SESSION['cdc_user_login'] = $user->user_login;
    $_SESSION['cdc_user_role'] = $user->roles[0];

    return true;
}

/**
 * Logout user
 */
function cdc_logout_user() {
    unset($_SESSION['cdc_user_id']);
    unset($_SESSION['cdc_user_login']);
    unset($_SESSION['cdc_user_role']);
    session_destroy();
}

/**
 * Handle logout action
 */
function cdc_handle_logout() {
    if (isset($_GET['action']) && $_GET['action'] === 'logout') {
        cdc_logout_user();
        wp_redirect(home_url('/login'));
        exit;
    }
}
add_action('template_redirect', 'cdc_handle_logout');
