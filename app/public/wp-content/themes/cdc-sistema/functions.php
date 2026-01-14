<?php
/**
 * CDC Sistema Theme Functions
 *
 * @package CDC_Sistema
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define theme constants
define('CDC_THEME_VERSION', '1.0.0');
define('CDC_THEME_DIR', get_template_directory());
define('CDC_THEME_URL', get_template_directory_uri());

// Require includes
require_once CDC_THEME_DIR . '/includes/setup.php';
require_once CDC_THEME_DIR . '/includes/auth.php';
require_once CDC_THEME_DIR . '/includes/permissions.php';
require_once CDC_THEME_DIR . '/includes/enqueue.php';
require_once CDC_THEME_DIR . '/includes/ajax.php';

/**
 * Theme setup
 */
function cdc_sistema_setup() {
    // Add theme support
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ));

    // Register nav menus
    register_nav_menus(array(
        'primary' => __('Menú Principal', 'cdc-sistema'),
    ));
}
add_action('after_setup_theme', 'cdc_sistema_setup');

/**
 * Check if user is logged in to sistema
 */
function cdc_is_user_logged_in() {
    return isset($_SESSION['cdc_user_id']) && !empty($_SESSION['cdc_user_id']);
}

/**
 * Get current sistema user
 */
function cdc_get_current_user() {
    if (!cdc_is_user_logged_in()) {
        return false;
    }

    $user_id = $_SESSION['cdc_user_id'];
    $user = get_userdata($user_id);

    return $user ? $user : false;
}

/**
 * Get user role display name
 */
function cdc_get_user_role_display($user = null) {
    if ($user === null) {
        $user = cdc_get_current_user();
    }

    if (!$user) {
        return '';
    }

    if (in_array('administrator', $user->roles)) {
        return 'Admin';
    }

    if (in_array('cdc_tesoreria', $user->roles)) {
        return 'Tesorería';
    }

    if (in_array('cdc_recepcion', $user->roles)) {
        return 'Recepción';
    }

    return 'Usuario';
}

/**
 * Redirect to login if not authenticated
 */
function cdc_require_auth() {
    if (!cdc_is_user_logged_in()) {
        wp_redirect(home_url('/login'));
        exit;
    }
}

/**
 * Start session for authentication
 */
function cdc_start_session() {
    if (!session_id()) {
        session_start();
    }
}
add_action('init', 'cdc_start_session', 1);
