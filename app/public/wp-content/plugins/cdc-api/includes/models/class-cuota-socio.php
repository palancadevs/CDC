<?php
/**
 * Cuota Socio Model
 *
 * @package CDC_API
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Cuota Socio Model Class
 */
class CDC_Cuota_Socio extends CDC_Base_Model {
    /**
     * Table name
     */
    protected $table_name = 'cdc_cuota_socio';

    /**
     * Fillable columns
     */
    protected $fillable = array(
        'persona_id',
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
     * Find cuotas by persona ID
     *
     * @param int $persona_id Persona ID
     * @param int $anio Year (optional)
     * @return array
     */
    public function find_by_persona($persona_id, $anio = null) {
        global $wpdb;

        if ($anio) {
            $query = $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE persona_id = %d AND anio = %d ORDER BY mes ASC",
                $persona_id,
                $anio
            );
        } else {
            $query = $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE persona_id = %d ORDER BY anio DESC, mes DESC",
                $persona_id
            );
        }

        return $wpdb->get_results($query);
    }

    /**
     * Find cuota by persona, year and month
     *
     * @param int $persona_id Persona ID
     * @param int $anio Year
     * @param int $mes Month
     * @return object|null
     */
    public function find_by_persona_anio_mes($persona_id, $anio, $mes) {
        global $wpdb;

        $query = $wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE persona_id = %d AND anio = %d AND mes = %d",
            $persona_id,
            $anio,
            $mes
        );

        return $wpdb->get_row($query);
    }

    /**
     * Get unpaid cuotas by persona
     *
     * @param int $persona_id Persona ID
     * @return array
     */
    public function get_unpaid($persona_id) {
        global $wpdb;

        $query = $wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE persona_id = %d AND pagada = 0 ORDER BY anio ASC, mes ASC",
            $persona_id
        );

        return $wpdb->get_results($query);
    }

    /**
     * Mark cuota as paid
     *
     * @param int $id Cuota ID
     * @param array $payment_data Payment details
     * @return bool
     */
    public function mark_as_paid($id, $payment_data = array()) {
        global $wpdb;

        $data = array(
            'pagada' => 1,
            'fecha_pago' => isset($payment_data['fecha_pago']) ? $payment_data['fecha_pago'] : current_time('mysql'),
            'medio_pago' => isset($payment_data['medio_pago']) ? $payment_data['medio_pago'] : 'efectivo',
            'comprobante_id' => isset($payment_data['comprobante_id']) ? $payment_data['comprobante_id'] : null,
            'recibo_id' => isset($payment_data['recibo_id']) ? $payment_data['recibo_id'] : null,
        );

        return $this->update($id, $data);
    }

    /**
     * Generate cuotas for a year
     *
     * @param int $persona_id Persona ID
     * @param int $anio Year
     * @param float $monto Amount per month
     * @param int $start_month Starting month (1-12)
     * @return int Number of cuotas created
     */
    public function generate_cuotas_year($persona_id, $anio, $monto = 0.00, $start_month = 1) {
        global $wpdb;

        $created = 0;

        for ($mes = $start_month; $mes <= 12; $mes++) {
            // Check if cuota already exists
            $exists = $this->find_by_persona_anio_mes($persona_id, $anio, $mes);

            if (!$exists) {
                $result = $this->create(array(
                    'persona_id' => $persona_id,
                    'anio' => $anio,
                    'mes' => $mes,
                    'monto' => $monto,
                    'pagada' => 0,
                ));

                if ($result) {
                    $created++;
                }
            }
        }

        return $created;
    }

    /**
     * Get cuotas grid for a year (12 months)
     *
     * @param int $persona_id Persona ID
     * @param int $anio Year
     * @return array Array with 12 elements (one per month)
     */
    public function get_year_grid($persona_id, $anio) {
        $cuotas = $this->find_by_persona($persona_id, $anio);

        // Create grid with 12 months
        $grid = array();
        for ($mes = 1; $mes <= 12; $mes++) {
            $grid[$mes] = null;
        }

        // Fill with existing cuotas
        foreach ($cuotas as $cuota) {
            $grid[(int)$cuota->mes] = $cuota;
        }

        return $grid;
    }
}
