<?php
/**
 * Cuota Taller Model
 *
 * @package CDC_API
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Cuota Taller Model Class
 */
class CDC_Cuota_Taller extends CDC_Base_Model {
    /**
     * Table name
     */
    protected $table_name = 'cdc_cuotas_taller';

    /**
     * Fillable columns
     */
    protected $fillable = array(
        'inscripcion_id',
        'persona_id',
        'taller_id',
        'anio',
        'mes',
        'monto',
        'pagada',
        'fecha_pago',
        'medio_pago',
        'comprobante_id',
        'recibo_id',
        'observaciones',
    );

    /**
     * Find cuotas by inscripcion ID
     *
     * @param int $inscripcion_id Inscripcion ID
     * @param int $anio Year (optional)
     * @return array
     */
    public function find_by_inscripcion($inscripcion_id, $anio = null) {
        global $wpdb;

        if ($anio) {
            $query = $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE inscripcion_id = %d AND anio = %d ORDER BY mes ASC",
                $inscripcion_id,
                $anio
            );
        } else {
            $query = $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE inscripcion_id = %d ORDER BY anio DESC, mes DESC",
                $inscripcion_id
            );
        }

        return $wpdb->get_results($query);
    }

    /**
     * Get unpaid cuotas for a persona
     *
     * @param int $persona_id Persona ID
     * @param int $taller_id Taller ID (optional)
     * @return array
     */
    public function get_unpaid($persona_id, $taller_id = null) {
        global $wpdb;

        $query = "SELECT c.*, t.nombre as taller_nombre
                  FROM {$this->table_name} c
                  LEFT JOIN {$wpdb->prefix}cdc_talleres t ON c.taller_id = t.id
                  WHERE c.persona_id = %d AND c.pagada = 0";

        $params = array($persona_id);

        if ($taller_id) {
            $query .= " AND c.taller_id = %d";
            $params[] = $taller_id;
        }

        $query .= " ORDER BY c.anio ASC, c.mes ASC";

        return $wpdb->get_results($wpdb->prepare($query, $params));
    }

    /**
     * Mark cuota as paid
     *
     * @param int $id Cuota ID
     * @param array $payment_data Payment data (fecha_pago, medio_pago, etc.)
     * @return bool
     */
    public function mark_as_paid($id, $payment_data) {
        $data = array(
            'pagada' => 1,
            'fecha_pago' => isset($payment_data['fecha_pago']) ? $payment_data['fecha_pago'] : current_time('mysql'),
            'medio_pago' => isset($payment_data['medio_pago']) ? $payment_data['medio_pago'] : null,
            'comprobante_id' => isset($payment_data['comprobante_id']) ? $payment_data['comprobante_id'] : null,
            'recibo_id' => isset($payment_data['recibo_id']) ? $payment_data['recibo_id'] : null,
            'observaciones' => isset($payment_data['observaciones']) ? $payment_data['observaciones'] : null,
        );

        return $this->update($id, $data);
    }

    /**
     * Generate cuotas for inscripcion
     *
     * @param int $inscripcion_id Inscripcion ID
     * @param int $persona_id Persona ID
     * @param int $taller_id Taller ID
     * @param int $anio Year
     * @param float $monto_mensual Monthly amount
     * @param int $starting_month Starting month (default 1)
     * @return array Created cuota IDs
     */
    public function generate_cuotas($inscripcion_id, $persona_id, $taller_id, $anio, $monto_mensual, $starting_month = 1) {
        $cuota_ids = array();

        for ($mes = $starting_month; $mes <= 12; $mes++) {
            $cuota_id = $this->create(array(
                'inscripcion_id' => $inscripcion_id,
                'persona_id' => $persona_id,
                'taller_id' => $taller_id,
                'anio' => $anio,
                'mes' => $mes,
                'monto' => $monto_mensual,
                'pagada' => 0,
            ));

            if ($cuota_id) {
                $cuota_ids[] = $cuota_id;
            }
        }

        return $cuota_ids;
    }
}
