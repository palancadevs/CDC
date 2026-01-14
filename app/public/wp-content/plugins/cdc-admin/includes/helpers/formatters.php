<?php
/**
 * Formatters Helper
 *
 * Formatting utilities for CDC Admin
 */

if (!defined('ABSPATH')) {
    exit;
}

class CDC_Formatters {

    /**
     * Format currency amount
     */
    public static function format_currency($amount, $include_symbol = true) {
        $symbol = get_option('cdc_currency_symbol', '$');
        $formatted = number_format((float)$amount, 2, ',', '.');

        return $include_symbol ? $symbol . $formatted : $formatted;
    }

    /**
     * Format date
     */
    public static function format_date($date, $format = null) {
        if (empty($date)) {
            return '';
        }

        if ($format === null) {
            $format = get_option('cdc_date_format', 'd/m/Y');
        }

        $timestamp = is_numeric($date) ? $date : strtotime($date);
        return date($format, $timestamp);
    }

    /**
     * Format time
     */
    public static function format_time($time, $format = null) {
        if (empty($time)) {
            return '';
        }

        if ($format === null) {
            $format = get_option('cdc_time_format', 'H:i');
        }

        $timestamp = is_numeric($time) ? $time : strtotime($time);
        return date($format, $timestamp);
    }

    /**
     * Format datetime
     */
    public static function format_datetime($datetime, $date_format = null, $time_format = null) {
        if (empty($datetime)) {
            return '';
        }

        return self::format_date($datetime, $date_format) . ' ' . self::format_time($datetime, $time_format);
    }

    /**
     * Format DNI
     */
    public static function format_dni($dni) {
        $dni = preg_replace('/[^0-9]/', '', $dni);

        if (strlen($dni) === 8) {
            return number_format($dni, 0, '', '.');
        }

        return $dni;
    }

    /**
     * Format phone number
     */
    public static function format_phone($phone) {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (strlen($phone) === 10) {
            return substr($phone, 0, 3) . ' ' . substr($phone, 3, 3) . ' ' . substr($phone, 6);
        }

        return $phone;
    }

    /**
     * Format month name (Spanish)
     */
    public static function format_month($month) {
        $months = array(
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        );

        return isset($months[$month]) ? $months[$month] : '';
    }

    /**
     * Format estado badge HTML
     */
    public static function format_estado_badge($estado, $text = null) {
        if ($text === null) {
            $text = ucfirst($estado);
        }

        $class_map = array(
            'activo' => 'success',
            'pagada' => 'success',
            'finalizado' => 'success',
            'pendiente' => 'warning',
            'reservado' => 'info',
            'inactivo' => 'secondary',
            'cancelado' => 'danger',
            'error' => 'danger',
        );

        $class = isset($class_map[$estado]) ? $class_map[$estado] : 'secondary';

        return sprintf(
            '<span class="badge badge-%s">%s</span>',
            esc_attr($class),
            esc_html($text)
        );
    }

    /**
     * Format medio de pago
     */
    public static function format_medio_pago($medio) {
        $medios = array(
            'efectivo' => 'Efectivo',
            'transferencia' => 'Transferencia',
            'tarjeta' => 'Tarjeta',
            'mercadopago' => 'Mercado Pago',
            'otro' => 'Otro',
        );

        return isset($medios[$medio]) ? $medios[$medio] : ucfirst($medio);
    }

    /**
     * Sanitize and format text for display
     */
    public static function sanitize_text($text) {
        return wp_kses_post($text);
    }

    /**
     * Format persona full name
     */
    public static function format_persona_name($apellido, $nombre) {
        return trim($apellido . ', ' . $nombre);
    }

    /**
     * Format concepto tipo for display
     */
    public static function format_concepto_tipo($concepto_tipo) {
        $conceptos = array(
            'CuotaSocio' => 'Cuota socio',
            'CuotaTaller' => 'Cuota taller',
            'EntradaEvento' => 'Entrada evento',
            'AlquilerSala' => 'Alquiler sala',
            'PagoTallerista' => 'Pago tallerista',
            'Gasto' => 'Gasto',
            'Otro' => 'Otro',
        );

        return isset($conceptos[$concepto_tipo]) ? $conceptos[$concepto_tipo] : $concepto_tipo;
    }

    /**
     * Truncate text
     */
    public static function truncate($text, $length = 50, $suffix = '...') {
        if (strlen($text) <= $length) {
            return $text;
        }

        return substr($text, 0, $length - strlen($suffix)) . $suffix;
    }
}
