<?php
/**
 * Enqueue Scripts and Styles
 *
 * @package CDC_Sistema
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue styles and scripts for CDC Sistema theme
 */
function cdc_enqueue_assets() {
    // Main CSS
    wp_enqueue_style(
        'cdc-main-css',
        get_template_directory_uri() . '/assets/css/main.css',
        array(),
        '1.0.0'
    );

    // Notifications CSS
    wp_enqueue_style(
        'cdc-notifications-css',
        get_template_directory_uri() . '/assets/css/notifications.css',
        array('cdc-main-css'),
        '1.0.0'
    );

    // WordPress Dashicons (for icons)
    wp_enqueue_style('dashicons');

    // jQuery (WordPress bundled version)
    wp_enqueue_script('jquery');

    // CDC API wrapper
    wp_enqueue_script(
        'cdc-api-js',
        get_template_directory_uri() . '/assets/js/cdc-api.js',
        array('jquery'),
        '1.0.0',
        true
    );

    // Main JS
    wp_enqueue_script(
        'cdc-app-js',
        get_template_directory_uri() . '/assets/js/app.js',
        array('jquery', 'cdc-api-js'),
        '1.0.0',
        true
    );

    // Localize script with data for AJAX
    wp_localize_script('cdc-app-js', 'cdcData', array(
        'apiUrl' => rest_url('cdc/v1/'),
        'nonce' => wp_create_nonce('wp_rest'),
        'homeUrl' => home_url(),
        'ajaxUrl' => admin_url('admin-ajax.php')
    ));

    // Load auth script only on login page
    if (is_page('login')) {
        wp_enqueue_script(
            'cdc-auth-js',
            get_template_directory_uri() . '/assets/js/auth.js',
            array('jquery'),
            '1.0.0',
            true
        );

        // Localize auth script with login-specific data
        wp_localize_script('cdc-auth-js', 'cdcAuthData', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'loginNonce' => wp_create_nonce('cdc_login_nonce'),
            'homeUrl' => home_url()
        ));
    }
}
add_action('wp_enqueue_scripts', 'cdc_enqueue_assets');
