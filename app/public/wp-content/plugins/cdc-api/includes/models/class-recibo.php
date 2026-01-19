<?php
/**
 * Recibo Model
 *
 * @package CDC_API
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Recibo Model Class
 */
class CDC_Recibo extends CDC_Base_Model {
    /**
     * Table name
     */
    protected $table_name = 'cdc_recibos';

    /**
     * Fillable columns
     */
    protected $fillable = array(
        'numero_recibo',
        'persona_id',
        'tipo',
        'concepto',
        'monto_total',
        'fecha_emision',
        'metodo_pago',
        'estado',
        'usuario_id',
        'notas',
    );

    /**
     * Find recibo by numero
     *
     * @param string $numero_recibo Receipt number
     * @return object|null
     */
    public function find_by_numero($numero_recibo) {
        global $wpdb;

        $query = $wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE numero_recibo = %s",
            $numero_recibo
        );

        return $wpdb->get_row($query);
    }

    /**
     * Get recibos by persona
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
     * Get recibos by type
     *
     * @param string $tipo Type (cuota, taller, evento, sala, otro)
     * @param array $args Additional arguments
     * @return array
     */
    public function get_by_tipo($tipo, $args = array()) {
        $args['where'] = $this->get_wpdb()->prepare('tipo = %s', $tipo);
        return $this->get_all($args);
    }

    /**
     * Get recibos by date range
     *
     * @param string $fecha_inicio Start date
     * @param string $fecha_fin End date
     * @param array $args Additional arguments
     * @return array
     */
    public function get_by_date_range($fecha_inicio, $fecha_fin, $args = array()) {
        global $wpdb;

        $args['where'] = $wpdb->prepare(
            'fecha_emision >= %s AND fecha_emision <= %s',
            $fecha_inicio,
            $fecha_fin
        );

        return $this->get_all($args);
    }

    /**
     * Get today's recibos
     *
     * @param array $args Additional arguments
     * @return array
     */
    public function get_today($args = array()) {
        $today = current_time('Y-m-d');
        return $this->get_by_date_range($today . ' 00:00:00', $today . ' 23:59:59', $args);
    }

    /**
     * Generate next receipt number
     *
     * @param string $tipo Receipt type
     * @return string
     */
    public function generate_numero_recibo($tipo = 'general') {
        global $wpdb;

        $prefix = strtoupper(substr($tipo, 0, 1));
        $today = current_time('Ymd');

        // Get last receipt number for today
        $pattern = $prefix . $today . '%';

        $last_numero = $wpdb->get_var($wpdb->prepare(
            "SELECT numero_recibo FROM {$this->table_name} WHERE numero_recibo LIKE %s ORDER BY id DESC LIMIT 1",
            $pattern
        ));

        if (!$last_numero) {
            return $prefix . $today . '-001';
        }

        // Extract sequence and increment
        $parts = explode('-', $last_numero);
        $sequence = isset($parts[1]) ? intval($parts[1]) : 0;
        $sequence++;

        return $prefix . $today . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get full recibo data with items
     *
     * @param int $id Recibo ID
     * @return object|null
     */
    public function get_full_data($id) {
        global $wpdb;

        $recibo = $this->find($id);

        if (!$recibo) {
            return null;
        }

        // Get items
        $items_table = $wpdb->prefix . 'cdc_items_recibo';
        $recibo->items = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $items_table WHERE recibo_id = %d",
            $id
        ));

        // Get persona data
        $persona_table = $wpdb->prefix . 'cdc_personas';
        $recibo->persona = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $persona_table WHERE id = %d",
            $recibo->persona_id
        ));

        return $recibo;
    }

    /**
     * Get total amount by date range
     *
     * @param string $fecha_inicio Start date
     * @param string $fecha_fin End date
     * @param string $tipo Optional type filter
     * @return float
     */
    public function get_total_by_date_range($fecha_inicio, $fecha_fin, $tipo = null) {
        global $wpdb;

        $query = $wpdb->prepare(
            "SELECT SUM(monto_total) FROM {$this->table_name}
            WHERE estado = 'pagado'
            AND fecha_emision >= %s
            AND fecha_emision <= %s",
            $fecha_inicio,
            $fecha_fin
        );

        if ($tipo) {
            $query .= $wpdb->prepare(" AND tipo = %s", $tipo);
        }

        return (float) $wpdb->get_var($query);
    }
}
