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
require_once CDC_THEME_DIR . '/includes/enqueue.php';

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
 * Get current user name (dummy for Phase 1)
 * In future phases this will return actual logged-in user
 */
function cdc_get_current_user_name() {
    return 'Usuario Sistema';
}

/**
 * Get user role display name (dummy for Phase 1)
 * In future phases this will return actual user role
 */
function cdc_get_user_role_display() {
    return 'Admin';
}
