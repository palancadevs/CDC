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
    // Only load assets on authenticated pages
    if (!is_page_template('template-login.php')) {
        // Main CSS
        wp_enqueue_style(
            'cdc-main-css',
            get_template_directory_uri() . '/assets/css/main.css',
            array(),
            '1.0.0'
        );

        // WordPress Dashicons (for icons)
        wp_enqueue_style('dashicons');

        // jQuery (WordPress bundled version)
        wp_enqueue_script('jquery');

        // Main JS
        wp_enqueue_script(
            'cdc-app-js',
            get_template_directory_uri() . '/assets/js/app.js',
            array('jquery'),
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
    }
}
add_action('wp_enqueue_scripts', 'cdc_enqueue_assets');
