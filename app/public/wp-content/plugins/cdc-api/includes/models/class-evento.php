<?php
/**
 * Evento Model
 *
 * @package CDC_API
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Evento Model Class
 */
class CDC_Evento extends CDC_Base_Model {
    /**
     * Table name
     */
    protected $table_name = 'cdc_eventos';

    /**
     * Fillable columns
     */
    protected $fillable = array(
        'nombre',
        'descripcion',
        'tipo',
        'fecha_evento',
        'duracion_minutos',
        'cupo_maximo',
        'inscriptos',
        'precio_entrada',
        'precio_socio',
        'sala_id',
        'estado',
        'notas',
    );

    /**
     * Get upcoming events
     *
     * @param array $args Additional arguments
     * @return array
     */
    public function get_upcoming($args = array()) {
        global $wpdb;

        $now = current_time('mysql');
        $args['where'] = $wpdb->prepare("fecha_evento >= %s AND estado = 'programado'", $now);
        $args['orderby'] = 'fecha_evento';
        $args['order'] = 'ASC';

        return $this->get_all($args);
    }

    /**
     * Get events by date range
     *
     * @param string $fecha_inicio Start date
     * @param string $fecha_fin End date
     * @param array $args Additional arguments
     * @return array
     */
    public function get_by_date_range($fecha_inicio, $fecha_fin, $args = array()) {
        global $wpdb;

        $args['where'] = $wpdb->prepare(
            'fecha_evento >= %s AND fecha_evento <= %s',
            $fecha_inicio,
            $fecha_fin
        );

        return $this->get_all($args);
    }

    /**
     * Get events by type
     *
     * @param string $tipo Type
     * @param array $args Additional arguments
     * @return array
     */
    public function get_by_tipo($tipo, $args = array()) {
        $args['where'] = $this->get_wpdb()->prepare('tipo = %s', $tipo);
        return $this->get_all($args);
    }

    /**
     * Get events by status
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
     * Check if event has available spots
     *
     * @param int $id Event ID
     * @return bool
     */
    public function has_available_spots($id) {
        $evento = $this->find($id);

        if (!$evento || !$evento->cupo_maximo) {
            return true;
        }

        return $evento->inscriptos < $evento->cupo_maximo;
    }

    /**
     * Increment inscriptos count
     *
     * @param int $id Event ID
     * @return bool
     */
    public function increment_inscriptos($id) {
        global $wpdb;

        $query = $wpdb->prepare(
            "UPDATE {$this->table_name} SET inscriptos = inscriptos + 1 WHERE id = %d",
            $id
        );

        return $wpdb->query($query) !== false;
    }
}
