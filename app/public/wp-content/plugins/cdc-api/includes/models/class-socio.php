<?php
/**
 * Socio Model
 *
 * @package CDC_API
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Socio Model Class
 */
class CDC_Socio extends CDC_Base_Model {
    /**
     * Table name
     */
    protected $table_name = 'cdc_socios';

    /**
     * Fillable columns
     */
    protected $fillable = array(
        'persona_id',
        'numero_socio',
        'fecha_alta',
        'fecha_baja',
        'estado',
        'monto_cuota',
        'dia_cobro',
        'notas_pago',
    );

    /**
     * Find socio by persona ID
     *
     * @param int $persona_id Persona ID
     * @return object|null
     */
    public function find_by_persona_id($persona_id) {
        global $wpdb;

        $query = $wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE persona_id = %d",
            $persona_id
        );

        return $wpdb->get_row($query);
    }

    /**
     * Find socio by numero_socio
     *
     * @param string $numero_socio Member number
     * @return object|null
     */
    public function find_by_numero_socio($numero_socio) {
        global $wpdb;

        $query = $wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE numero_socio = %s",
            $numero_socio
        );

        return $wpdb->get_row($query);
    }

    /**
     * Get active members
     *
     * @param array $args Additional arguments
     * @return array
     */
    public function get_active($args = array()) {
        $args['where'] = "estado = 'activo'";
        return $this->get_all($args);
    }

    /**
     * Get members by status
     *
     * @param string $estado Status (activo, inactivo, suspendido)
     * @param array $args Additional arguments
     * @return array
     */
    public function get_by_estado($estado, $args = array()) {
        $args['where'] = $this->get_wpdb()->prepare('estado = %s', $estado);
        return $this->get_all($args);
    }

    /**
     * Get members with payment due
     *
     * @param int $dia_cobro Day of month
     * @return array
     */
    public function get_with_payment_due($dia_cobro = null) {
        global $wpdb;

        $where = "estado = 'activo'";

        if ($dia_cobro) {
            $where .= $wpdb->prepare(" AND dia_cobro = %d", $dia_cobro);
        }

        return $this->get_all(array('where' => $where));
    }

    /**
     * Generate next member number
     *
     * @return string
     */
    public function generate_numero_socio() {
        global $wpdb;

        $last_numero = $wpdb->get_var(
            "SELECT numero_socio FROM {$this->table_name} ORDER BY id DESC LIMIT 1"
        );

        if (!$last_numero) {
            return 'S-0001';
        }

        // Extract number and increment
        $parts = explode('-', $last_numero);
        $number = isset($parts[1]) ? intval($parts[1]) : 0;
        $number++;

        return 'S-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get full socio data with persona info
     *
     * @param int $id Socio ID
     * @return object|null
     */
    public function get_full_data($id) {
        global $wpdb;

        $socio = $this->find($id);

        if (!$socio) {
            return null;
        }

        // Get persona data
        $persona_table = $wpdb->prefix . 'cdc_personas';
        $persona = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $persona_table WHERE id = %d",
            $socio->persona_id
        ));

        $socio->persona = $persona;

        return $socio;
    }
}
