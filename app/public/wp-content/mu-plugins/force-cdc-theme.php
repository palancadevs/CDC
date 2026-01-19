<?php
/**
 * Plugin Name: Force CDC Sistema Theme
 * Description: Forces CDC Sistema theme to load - bypasses all cache
 * Version: 1.0.0
 * Author: CDC
 */

// This runs BEFORE everything else in WordPress
add_filter('pre_option_template', function($value) {
    return 'cdc-sistema';
});

add_filter('pre_option_stylesheet', function($value) {
    return 'cdc-sistema';
});

add_filter('pre_option_current_theme', function($value) {
    return 'CDC Sistema';
});

// Also set on init to be extra sure
add_action('init', function() {
    if (get_option('template') !== 'cdc-sistema') {
        update_option('template', 'cdc-sistema');
        update_option('stylesheet', 'cdc-sistema');
        update_option('current_theme', 'CDC Sistema');
    }
}, 1);
