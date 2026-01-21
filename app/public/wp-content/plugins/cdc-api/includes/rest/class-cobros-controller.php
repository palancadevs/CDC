<?php
/**
 * Cobros REST Controller
 *
 * @package CDC_API
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Cobros REST Controller Class
 */
class CDC_Cobros_Controller extends CDC_Base_Controller {
    /**
     * Rest base
     */
    protected $rest_base = 'cobros';

    /**
     * Cuota Socio model
     */
    private $cuota_socio_model;

    /**
     * Movimiento Caja model
     */
    private $movimiento_caja_model;

    /**
     * Constructor
     */
    public function __construct() {
        $this->cuota_socio_model = new CDC_Cuota_Socio();
        $this->movimiento_caja_model = new CDC_Movimiento_Caja();
    }

    /**
     * Register routes
     */
    public function register_routes() {
        // POST /cobros/cuota-socio - Cobrar cuota de socio
        register_rest_route($this->namespace, '/' . $this->rest_base . '/cuota-socio', array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'cobrar_cuota_socio'),
                'permission_callback' => array($this, 'check_auth'),
            ),
        ));

        // GET /cobros/cuotas-pendientes/{persona_id} - Get pending cuotas
        register_rest_route($this->namespace, '/' . $this->rest_base . '/cuotas-pendientes/(?P<persona_id>[\d]+)', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_cuotas_pendientes'),
                'permission_callback' => array($this, 'check_auth'),
            ),
        ));
    }

    /**
     * Get pending cuotas for a persona
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function get_cuotas_pendientes($request) {
        $persona_id = $request->get_param('persona_id');

        $cuotas = $this->cuota_socio_model->get_unpaid($persona_id);

        return $this->prepare_response(array(
            'success' => true,
            'data' => $cuotas,
        ));
    }

    /**
     * Cobrar cuota de socio
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function cobrar_cuota_socio($request) {
        $data = $request->get_json_params();

        // Validate required fields
        if (empty($data['persona_id']) || empty($data['cuota_ids']) || empty($data['medio_pago'])) {
            return $this->prepare_error('persona_id, cuota_ids y medio_pago son requeridos', 400);
        }

        $persona_id = (int) $data['persona_id'];
        $cuota_ids = $data['cuota_ids']; // Array of cuota IDs to pay
        $medio_pago = sanitize_text_field($data['medio_pago']);
        $observaciones = isset($data['observaciones']) ? sanitize_textarea_field($data['observaciones']) : '';

        // Validate medio_pago
        if (!in_array($medio_pago, array('efectivo', 'transferencia', 'tarjeta', 'mercadopago'))) {
            return $this->prepare_error('medio_pago inválido', 400);
        }

        global $wpdb;

        // Start transaction
        $wpdb->query('START TRANSACTION');

        try {
            $monto_total = 0;
            $cuotas_pagadas = array();

            // Process each cuota
            foreach ($cuota_ids as $cuota_id) {
                $cuota = $this->cuota_socio_model->find($cuota_id);

                if (!$cuota) {
                    throw new Exception('Cuota no encontrada: ' . $cuota_id);
                }

                if ($cuota->persona_id != $persona_id) {
                    throw new Exception('Cuota no pertenece al socio');
                }

                if ($cuota->pagada) {
                    throw new Exception('Cuota ya pagada: ' . $cuota_id);
                }

                // Mark cuota as paid
                $payment_data = array(
                    'fecha_pago' => current_time('mysql'),
                    'medio_pago' => $medio_pago,
                );

                $this->cuota_socio_model->mark_as_paid($cuota_id, $payment_data);

                $monto_total += floatval($cuota->monto);
                $cuotas_pagadas[] = array(
                    'id' => $cuota->id,
                    'mes' => $cuota->mes,
                    'anio' => $cuota->anio,
                    'monto' => $cuota->monto,
                );
            }

            // Get current balance
            $saldo_anterior = $this->movimiento_caja_model->get_current_balance();
            $saldo_nuevo = $saldo_anterior + $monto_total;

            // Get current user ID (for now, use 1 as placeholder since no auth)
            $user_id = get_current_user_id() ?: 1;

            // Create movimiento de caja
            $concepto = 'Cobro cuota socio - ' . count($cuota_ids) . ' mes(es)';
            if ($observaciones) {
                $concepto .= ' - ' . $observaciones;
            }

            $movimiento_data = array(
                'tipo' => 'ingreso',
                'concepto' => $concepto,
                'monto' => $monto_total,
                'saldo_anterior' => $saldo_anterior,
                'saldo_nuevo' => $saldo_nuevo,
                'usuario_id' => $user_id,
                'fecha_movimiento' => current_time('mysql'),
                'notas' => $observaciones,
            );

            $movimiento_id = $this->movimiento_caja_model->create($movimiento_data);

            if (!$movimiento_id) {
                throw new Exception('Error al crear movimiento de caja');
            }

            // Commit transaction
            $wpdb->query('COMMIT');

            return $this->prepare_response(array(
                'success' => true,
                'message' => 'Cuota(s) cobrada(s) correctamente',
                'data' => array(
                    'movimiento_id' => $movimiento_id,
                    'monto_total' => $monto_total,
                    'cuotas_pagadas' => $cuotas_pagadas,
                    'saldo_nuevo' => $saldo_nuevo,
                ),
            ), 201);

        } catch (Exception $e) {
            // Rollback on error
            $wpdb->query('ROLLBACK');

            return $this->prepare_error($e->getMessage(), 400);
        }
    }
}
