<?php
/**
 * Permissions Helper
 *
 * Centralized permission checks for CDC Admin
 */

if (!defined('ABSPATH')) {
    exit;
}

class CDC_Permissions {

    /**
     * Check if current user can manage personas
     */
    public static function can_manage_personas() {
        return current_user_can('cdc_manage_personas') || current_user_can('administrator');
    }

    /**
     * Check if current user can manage cobros
     */
    public static function can_manage_cobros() {
        return current_user_can('cdc_manage_cobros') || current_user_can('administrator');
    }

    /**
     * Check if current user can manage caja
     */
    public static function can_manage_caja() {
        return current_user_can('cdc_manage_caja') || current_user_can('administrator');
    }

    /**
     * Check if current user can view caja
     */
    public static function can_view_caja() {
        return current_user_can('cdc_view_caja') ||
               current_user_can('cdc_manage_caja') ||
               current_user_can('administrator');
    }

    /**
     * Check if current user can register expenses
     */
    public static function can_register_expenses() {
        return current_user_can('cdc_register_expenses') ||
               current_user_can('cdc_manage_caja') ||
               current_user_can('administrator');
    }

    /**
     * Check if current user can void movements
     */
    public static function can_void_movements() {
        return current_user_can('cdc_void_movements') || current_user_can('administrator');
    }

    /**
     * Check if current user can retry invoices
     */
    public static function can_retry_invoices() {
        return current_user_can('cdc_retry_invoices') || current_user_can('administrator');
    }

    /**
     * Check if current user can edit preset amounts
     */
    public static function can_edit_amounts() {
        return current_user_can('cdc_edit_amounts') || current_user_can('administrator');
    }

    /**
     * Check if current user can manage catalogs (talleres, salas, etc.)
     */
    public static function can_manage_catalogs() {
        return current_user_can('cdc_manage_catalogs') || current_user_can('administrator');
    }

    /**
     * Check if current user can manage talleres
     */
    public static function can_manage_talleres() {
        return current_user_can('cdc_manage_talleres') || current_user_can('administrator');
    }

    /**
     * Check if current user can manage salas
     */
    public static function can_manage_salas() {
        return current_user_can('cdc_manage_salas') || current_user_can('administrator');
    }

    /**
     * Check if current user can view reports
     */
    public static function can_view_reports() {
        return current_user_can('cdc_view_reports') || current_user_can('administrator');
    }

    /**
     * Get current user role display name
     */
    public static function get_user_role_display() {
        $user = wp_get_current_user();

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
     * Verify nonce for CDC actions
     */
    public static function verify_nonce($nonce, $action = 'cdc_action') {
        return wp_verify_nonce($nonce, $action);
    }

    /**
     * Check REST API permissions
     */
    public static function check_rest_permissions($capability = 'cdc_manage_personas') {
        if (!is_user_logged_in()) {
            return new WP_Error(
                'rest_not_logged_in',
                __('Debe iniciar sesión para acceder.', 'cdc-admin'),
                array('status' => 401)
            );
        }

        if (!current_user_can($capability) && !current_user_can('administrator')) {
            return new WP_Error(
                'rest_forbidden',
                __('No tiene permisos para realizar esta acción.', 'cdc-admin'),
                array('status' => 403)
            );
        }

        return true;
    }
}
