<?php
/**
 * Movimiento Caja Model
 *
 * @package CDC_API
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Movimiento Caja Model Class
 */
class CDC_Movimiento_Caja extends CDC_Base_Model {
    /**
     * Table name
     */
    protected $table_name = 'cdc_movimientos_caja';

    /**
     * Fillable columns
     */
    protected $fillable = array(
        'tipo',
        'concepto',
        'monto',
        'saldo_anterior',
        'saldo_nuevo',
        'recibo_id',
        'gasto_id',
        'usuario_id',
        'fecha_movimiento',
        'notas',
    );

    /**
     * Get movements by date range
     *
     * @param string $fecha_inicio Start date
     * @param string $fecha_fin End date
     * @param array $args Additional arguments
     * @return array
     */
    public function get_by_date_range($fecha_inicio, $fecha_fin, $args = array()) {
        global $wpdb;

        $args['where'] = $wpdb->prepare(
            'fecha_movimiento >= %s AND fecha_movimiento <= %s',
            $fecha_inicio,
            $fecha_fin
        );

        return $this->get_all($args);
    }

    /**
     * Get today's movements
     *
     * @param array $args Additional arguments
     * @return array
     */
    public function get_today($args = array()) {
        $today = current_time('Y-m-d');
        return $this->get_by_date_range($today . ' 00:00:00', $today . ' 23:59:59', $args);
    }

    /**
     * Get movements by type
     *
     * @param string $tipo Type (ingreso, egreso, apertura, cierre)
     * @param array $args Additional arguments
     * @return array
     */
    public function get_by_tipo($tipo, $args = array()) {
        $args['where'] = $this->get_wpdb()->prepare('tipo = %s', $tipo);
        return $this->get_all($args);
    }

    /**
     * Get current balance
     *
     * @return float
     */
    public function get_current_balance() {
        global $wpdb;

        $last_movement = $wpdb->get_row(
            "SELECT saldo_nuevo FROM {$this->table_name} ORDER BY id DESC LIMIT 1"
        );

        return $last_movement ? (float) $last_movement->saldo_nuevo : 0.00;
    }

    /**
     * Get total ingresos by date range
     *
     * @param string $fecha_inicio Start date
     * @param string $fecha_fin End date
     * @return float
     */
    public function get_total_ingresos($fecha_inicio, $fecha_fin) {
        global $wpdb;

        $query = $wpdb->prepare(
            "SELECT SUM(monto) FROM {$this->table_name}
            WHERE tipo = 'ingreso'
            AND fecha_movimiento >= %s
            AND fecha_movimiento <= %s",
            $fecha_inicio,
            $fecha_fin
        );

        return (float) $wpdb->get_var($query);
    }

    /**
     * Get total egresos by date range
     *
     * @param string $fecha_inicio Start date
     * @param string $fecha_fin End date
     * @return float
     */
    public function get_total_egresos($fecha_inicio, $fecha_fin) {
        global $wpdb;

        $query = $wpdb->prepare(
            "SELECT SUM(monto) FROM {$this->table_name}
            WHERE tipo = 'egreso'
            AND fecha_movimiento >= %s
            AND fecha_movimiento <= %s",
            $fecha_inicio,
            $fecha_fin
        );

        return (float) $wpdb->get_var($query);
    }
}
