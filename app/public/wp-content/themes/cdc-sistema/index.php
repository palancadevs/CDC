<?php
/**
 * Main Template File
 *
 * Este es el template principal que WordPress usa como fallback.
 * En este sistema, redirige al login o dashboard según autenticación.
 *
 * @package CDC_Sistema
 */

if (!defined('ABSPATH')) {
    exit;
}

// Redirect to login or dashboard
if (cdc_is_user_logged_in()) {
    // User is logged in, redirect to dashboard
    wp_redirect(home_url('/dashboard'));
} else {
    // User not logged in, redirect to login
    wp_redirect(home_url('/login'));
}
exit;
