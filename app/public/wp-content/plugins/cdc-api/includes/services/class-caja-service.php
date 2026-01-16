<?php
/**
 * Caja Service
 *
 * @package CDC_API
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Caja Service Class
 * Business logic for cash register operations
 */
class CDC_Caja_Service {
    /**
     * Movimiento model
     */
    private $movimiento_model;

    /**
     * Constructor
     */
    public function __construct() {
        $this->movimiento_model = new CDC_Movimiento_Caja();
    }

    /**
     * Get current balance
     *
     * @return float
     */
    public function get_current_balance() {
        return $this->movimiento_model->get_current_balance();
    }

    /**
     * Register ingreso (income)
     *
     * @param float $monto Amount
     * @param string $concepto Concept
     * @param int $recibo_id Optional receipt ID
     * @return array Result
     */
    public function register_ingreso($monto, $concepto, $recibo_id = null) {
        $saldo_anterior = $this->get_current_balance();
        $saldo_nuevo = $saldo_anterior + $monto;

        $data = array(
            'tipo' => 'ingreso',
            'concepto' => $concepto,
            'monto' => $monto,
            'saldo_anterior' => $saldo_anterior,
            'saldo_nuevo' => $saldo_nuevo,
            'recibo_id' => $recibo_id,
            'usuario_id' => get_current_user_id(),
            'fecha_movimiento' => current_time('mysql'),
        );

        $movimiento_id = $this->movimiento_model->create($data);

        if (!$movimiento_id) {
            return array(
                'success' => false,
                'message' => 'Error al registrar el ingreso',
            );
        }

        return array(
            'success' => true,
            'message' => 'Ingreso registrado correctamente',
            'data' => array(
                'movimiento_id' => $movimiento_id,
                'saldo_nuevo' => $saldo_nuevo,
            ),
        );
    }

    /**
     * Register egreso (expense)
     *
     * @param float $monto Amount
     * @param string $concepto Concept
     * @param int $gasto_id Optional expense ID
     * @param int $recibo_id Optional receipt ID (for cancellations)
     * @return array Result
     */
    public function register_egreso($monto, $concepto, $gasto_id = null, $recibo_id = null) {
        $saldo_anterior = $this->get_current_balance();
        $saldo_nuevo = $saldo_anterior - $monto;

        if ($saldo_nuevo < 0) {
            return array(
                'success' => false,
                'message' => 'Saldo insuficiente en caja',
            );
        }

        $data = array(
            'tipo' => 'egreso',
            'concepto' => $concepto,
            'monto' => $monto,
            'saldo_anterior' => $saldo_anterior,
            'saldo_nuevo' => $saldo_nuevo,
            'gasto_id' => $gasto_id,
            'recibo_id' => $recibo_id,
            'usuario_id' => get_current_user_id(),
            'fecha_movimiento' => current_time('mysql'),
        );

        $movimiento_id = $this->movimiento_model->create($data);

        if (!$movimiento_id) {
            return array(
                'success' => false,
                'message' => 'Error al registrar el egreso',
            );
        }

        return array(
            'success' => true,
            'message' => 'Egreso registrado correctamente',
            'data' => array(
                'movimiento_id' => $movimiento_id,
                'saldo_nuevo' => $saldo_nuevo,
            ),
        );
    }

    /**
     * Open cash register (apertura)
     *
     * @param float $monto_inicial Initial amount
     * @param string $notas Notes
     * @return array Result
     */
    public function apertura_caja($monto_inicial, $notas = '') {
        $data = array(
            'tipo' => 'apertura',
            'concepto' => 'Apertura de caja',
            'monto' => $monto_inicial,
            'saldo_anterior' => 0.00,
            'saldo_nuevo' => $monto_inicial,
            'usuario_id' => get_current_user_id(),
            'fecha_movimiento' => current_time('mysql'),
            'notas' => $notas,
        );

        $movimiento_id = $this->movimiento_model->create($data);

        if (!$movimiento_id) {
            return array(
                'success' => false,
                'message' => 'Error al abrir la caja',
            );
        }

        return array(
            'success' => true,
            'message' => 'Caja abierta correctamente',
            'data' => array(
                'movimiento_id' => $movimiento_id,
                'saldo_inicial' => $monto_inicial,
            ),
        );
    }

    /**
     * Close cash register (cierre)
     *
     * @param string $notas Notes
     * @return array Result
     */
    public function cierre_caja($notas = '') {
        $saldo_actual = $this->get_current_balance();

        $data = array(
            'tipo' => 'cierre',
            'concepto' => 'Cierre de caja',
            'monto' => 0.00,
            'saldo_anterior' => $saldo_actual,
            'saldo_nuevo' => $saldo_actual,
            'usuario_id' => get_current_user_id(),
            'fecha_movimiento' => current_time('mysql'),
            'notas' => $notas,
        );

        $movimiento_id = $this->movimiento_model->create($data);

        if (!$movimiento_id) {
            return array(
                'success' => false,
                'message' => 'Error al cerrar la caja',
            );
        }

        return array(
            'success' => true,
            'message' => 'Caja cerrada correctamente',
            'data' => array(
                'movimiento_id' => $movimiento_id,
                'saldo_final' => $saldo_actual,
            ),
        );
    }

    /**
     * Get today's movements
     *
     * @return array
     */
    public function get_today_movements() {
        return $this->movimiento_model->get_today(array(
            'orderby' => 'fecha_movimiento',
            'order' => 'DESC',
        ));
    }

    /**
     * Get movements by date range
     *
     * @param string $fecha_inicio Start date
     * @param string $fecha_fin End date
     * @return array
     */
    public function get_movements_by_date_range($fecha_inicio, $fecha_fin) {
        return $this->movimiento_model->get_by_date_range($fecha_inicio, $fecha_fin, array(
            'orderby' => 'fecha_movimiento',
            'order' => 'DESC',
        ));
    }

    /**
     * Get cash summary for date range
     *
     * @param string $fecha_inicio Start date
     * @param string $fecha_fin End date
     * @return array Summary data
     */
    public function get_cash_summary($fecha_inicio, $fecha_fin) {
        $total_ingresos = $this->movimiento_model->get_total_ingresos($fecha_inicio, $fecha_fin);
        $total_egresos = $this->movimiento_model->get_total_egresos($fecha_inicio, $fecha_fin);

        return array(
            'total_ingresos' => $total_ingresos,
            'total_egresos' => $total_egresos,
            'diferencia' => $total_ingresos - $total_egresos,
            'saldo_actual' => $this->get_current_balance(),
        );
    }

    /**
     * Get today's summary
     *
     * @return array Summary data
     */
    public function get_today_summary() {
        $today = current_time('Y-m-d');
        return $this->get_cash_summary($today . ' 00:00:00', $today . ' 23:59:59');
    }
}
