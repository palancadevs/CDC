<?php
/**
 * Reserva Sala Model
 *
 * @package CDC_API
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Reserva Sala Model Class
 */
class CDC_Reserva_Sala extends CDC_Base_Model {
    /**
     * Table name
     */
    protected $table_name = 'cdc_reservas_salas';

    /**
     * Fillable columns
     */
    protected $fillable = array(
        'sala_id',
        'persona_id',
        'fecha_inicio',
        'fecha_fin',
        'duracion_horas',
        'monto_total',
        'estado',
        'motivo',
        'recibo_id',
        'notas',
    );

    /**
     * Get reservas by sala
     *
     * @param int $sala_id Sala ID
     * @param array $args Additional arguments
     * @return array
     */
    public function get_by_sala($sala_id, $args = array()) {
        $args['where'] = $this->get_wpdb()->prepare('sala_id = %d', $sala_id);
        return $this->get_all($args);
    }

    /**
     * Get reservas by persona
     *
     * @param int $persona_id Persona ID
     * @param array $args Additional arguments
     * @return array
     */
    public function get_by_persona($persona_id, $args = array()) {
        $args['where'] = $this->get_wpdb()->prepare('persona_id = %d', $persona_id);
        return $this->get_all($args);
    }

    /**
     * Get reservas by date range
     *
     * @param string $fecha_inicio Start date
     * @param string $fecha_fin End date
     * @param array $args Additional arguments
     * @return array
     */
    public function get_by_date_range($fecha_inicio, $fecha_fin, $args = array()) {
        global $wpdb;

        $args['where'] = $wpdb->prepare(
            'fecha_inicio >= %s AND fecha_inicio <= %s',
            $fecha_inicio,
            $fecha_fin
        );

        return $this->get_all($args);
    }

    /**
     * Get reservas by status
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
     * Get upcoming reservas
     *
     * @param int $sala_id Optional sala filter
     * @param array $args Additional arguments
     * @return array
     */
    public function get_upcoming($sala_id = null, $args = array()) {
        global $wpdb;

        $now = current_time('mysql');
        $where = $wpdb->prepare("fecha_inicio >= %s", $now);

        if ($sala_id) {
            $where .= $wpdb->prepare(" AND sala_id = %d", $sala_id);
        }

        $args['where'] = $where;
        $args['orderby'] = 'fecha_inicio';
        $args['order'] = 'ASC';

        return $this->get_all($args);
    }

    /**
     * Get full reserva data with sala and persona info
     *
     * @param int $id Reserva ID
     * @return object|null
     */
    public function get_full_data($id) {
        global $wpdb;

        $reserva = $this->find($id);

        if (!$reserva) {
            return null;
        }

        // Get sala data
        $sala_table = $wpdb->prefix . 'cdc_salas';
        $reserva->sala = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $sala_table WHERE id = %d",
            $reserva->sala_id
        ));

        // Get persona data
        $persona_table = $wpdb->prefix . 'cdc_personas';
        $reserva->persona = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $persona_table WHERE id = %d",
            $reserva->persona_id
        ));

        return $reserva;
    }
}
