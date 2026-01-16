<?php
/**
 * Sala Model
 *
 * @package CDC_API
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Sala Model Class
 */
class CDC_Sala extends CDC_Base_Model {
    /**
     * Table name
     */
    protected $table_name = 'cdc_salas';

    /**
     * Fillable columns
     */
    protected $fillable = array(
        'nombre',
        'descripcion',
        'capacidad',
        'precio_hora',
        'equipamiento',
        'estado',
        'notas',
    );

    /**
     * Get available salas
     *
     * @param array $args Additional arguments
     * @return array
     */
    public function get_available($args = array()) {
        $args['where'] = "estado = 'disponible'";
        return $this->get_all($args);
    }

    /**
     * Get salas by status
     *
     * @param string $estado Status
     * @param array $args Additional arguments
     * @return array
     */
    public function get_by_estado($estado, $args = array()) {
        $args['where'] = $this->get_wpdb()->prepare('estado = %s', $estado);
        return $this->get_all($args);
    }

    /**
     * Check if sala is available at date/time
     *
     * @param int $id Sala ID
     * @param string $fecha_inicio Start datetime
     * @param string $fecha_fin End datetime
     * @param int $exclude_reserva_id Exclude this reservation from check
     * @return bool
     */
    public function is_available($id, $fecha_inicio, $fecha_fin, $exclude_reserva_id = null) {
        global $wpdb;

        $reservas_table = $wpdb->prefix . 'cdc_reservas_salas';

        $query = $wpdb->prepare(
            "SELECT COUNT(*) FROM $reservas_table
            WHERE sala_id = %d
            AND estado IN ('confirmada', 'en_curso')
            AND (
                (fecha_inicio <= %s AND fecha_fin >= %s) OR
                (fecha_inicio <= %s AND fecha_fin >= %s) OR
                (fecha_inicio >= %s AND fecha_fin <= %s)
            )",
            $id,
            $fecha_inicio,
            $fecha_inicio,
            $fecha_fin,
            $fecha_fin,
            $fecha_inicio,
            $fecha_fin
        );

        if ($exclude_reserva_id) {
            $query .= $wpdb->prepare(" AND id != %d", $exclude_reserva_id);
        }

        return (int) $wpdb->get_var($query) === 0;
    }
}
