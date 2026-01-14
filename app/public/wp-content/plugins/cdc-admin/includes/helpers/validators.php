<?php
/**
 * Validators Helper
 *
 * Validation utilities for CDC Admin
 */

if (!defined('ABSPATH')) {
    exit;
}

class CDC_Validators {

    /**
     * Validate required field
     */
    public static function required($value, $field_name = 'Campo') {
        if (empty($value) && $value !== '0') {
            return new WP_Error('required_field', sprintf(__('%s es obligatorio.', 'cdc-admin'), $field_name));
        }
        return true;
    }

    /**
     * Validate DNI
     */
    public static function validate_dni($dni) {
        $dni = preg_replace('/[^0-9]/', '', $dni);

        if (strlen($dni) < 7 || strlen($dni) > 8) {
            return new WP_Error('invalid_dni', __('El DNI debe tener 7 u 8 dígitos.', 'cdc-admin'));
        }

        return true;
    }

    /**
     * Validate email
     */
    public static function validate_email($email) {
        if (!empty($email) && !is_email($email)) {
            return new WP_Error('invalid_email', __('El email no es válido.', 'cdc-admin'));
        }
        return true;
    }

    /**
     * Validate phone
     */
    public static function validate_phone($phone) {
        if (empty($phone)) {
            return true;
        }

        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (strlen($phone) < 10) {
            return new WP_Error('invalid_phone', __('El teléfono debe tener al menos 10 dígitos.', 'cdc-admin'));
        }

        return true;
    }

    /**
     * Validate amount (must be positive number)
     */
    public static function validate_amount($amount) {
        if (!is_numeric($amount) || $amount < 0) {
            return new WP_Error('invalid_amount', __('El monto debe ser un número positivo.', 'cdc-admin'));
        }
        return true;
    }

    /**
     * Validate date
     */
    public static function validate_date($date) {
        if (empty($date)) {
            return new WP_Error('invalid_date', __('La fecha no es válida.', 'cdc-admin'));
        }

        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return new WP_Error('invalid_date', __('La fecha no es válida.', 'cdc-admin'));
        }

        return true;
    }

    /**
     * Validate month (1-12)
     */
    public static function validate_month($month) {
        if (!is_numeric($month) || $month < 1 || $month > 12) {
            return new WP_Error('invalid_month', __('El mes debe estar entre 1 y 12.', 'cdc-admin'));
        }
        return true;
    }

    /**
     * Validate year
     */
    public static function validate_year($year) {
        if (!is_numeric($year) || $year < 2000 || $year > 2100) {
            return new WP_Error('invalid_year', __('El año no es válido.', 'cdc-admin'));
        }
        return true;
    }

    /**
     * Validate enum value
     */
    public static function validate_enum($value, $allowed_values, $field_name = 'Campo') {
        if (!in_array($value, $allowed_values)) {
            return new WP_Error(
                'invalid_value',
                sprintf(__('%s contiene un valor no válido.', 'cdc-admin'), $field_name)
            );
        }
        return true;
    }

    /**
     * Validate persona tipo
     */
    public static function validate_persona_tipo($tipo) {
        return self::validate_enum($tipo, array('socio', 'cliente'), 'Tipo de persona');
    }

    /**
     * Validate movimiento tipo
     */
    public static function validate_movimiento_tipo($tipo) {
        return self::validate_enum($tipo, array('ingreso', 'egreso'), 'Tipo de movimiento');
    }

    /**
     * Validate medio pago
     */
    public static function validate_medio_pago($medio) {
        $allowed = array('efectivo', 'transferencia', 'tarjeta', 'mercadopago', 'otro');
        return self::validate_enum($medio, $allowed, 'Medio de pago');
    }

    /**
     * Sanitize input
     */
    public static function sanitize_text($text) {
        return sanitize_text_field($text);
    }

    /**
     * Sanitize textarea
     */
    public static function sanitize_textarea($text) {
        return sanitize_textarea_field($text);
    }

    /**
     * Sanitize amount
     */
    public static function sanitize_amount($amount) {
        return floatval(str_replace(',', '.', $amount));
    }

    /**
     * Check if DNI is unique
     */
    public static function is_dni_unique($dni, $exclude_id = null) {
        global $wpdb;

        $sql = "SELECT id FROM {$wpdb->prefix}cdc_persona WHERE dni = %s";
        $params = array($dni);

        if ($exclude_id !== null) {
            $sql .= " AND id != %d";
            $params[] = $exclude_id;
        }

        $existing = $wpdb->get_var($wpdb->prepare($sql, $params));

        if ($existing) {
            return new WP_Error('duplicate_dni', __('Ya existe una persona con ese DNI.', 'cdc-admin'));
        }

        return true;
    }

    /**
     * Validate multiple fields
     */
    public static function validate_fields($fields, $rules) {
        $errors = array();

        foreach ($rules as $field => $validators) {
            $value = isset($fields[$field]) ? $fields[$field] : '';

            foreach ($validators as $validator => $params) {
                if ($validator === 'required') {
                    $result = self::required($value, $params);
                } elseif (method_exists(__CLASS__, 'validate_' . $validator)) {
                    $result = call_user_func(array(__CLASS__, 'validate_' . $validator), $value);
                }

                if (is_wp_error($result)) {
                    $errors[$field] = $result->get_error_message();
                    break;
                }
            }
        }

        return empty($errors) ? true : $errors;
    }
}
