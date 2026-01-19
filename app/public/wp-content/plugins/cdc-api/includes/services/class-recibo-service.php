<?php
/**
 * Recibo Service
 *
 * @package CDC_API
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Recibo Service Class
 * Business logic for receipts and payments
 */
class CDC_Recibo_Service {
    /**
     * Recibo model
     */
    private $recibo_model;

    /**
     * Persona model
     */
    private $persona_model;

    /**
     * Constructor
     */
    public function __construct() {
        $this->recibo_model = new CDC_Recibo();
        $this->persona_model = new CDC_Persona();
    }

    /**
     * Create new recibo (5-step payment flow)
     *
     * Step 1: Select person
     * Step 2: Select payment type
     * Step 3: Add payment items
     * Step 4: Review and confirm
     * Step 5: Generate receipt
     *
     * @param array $data Receipt data
     * @return array Result
     */
    public function create_recibo($data) {
        global $wpdb;

        // Validate required fields
        if (empty($data['persona_id']) || empty($data['tipo']) || empty($data['items'])) {
            return array(
                'success' => false,
                'message' => 'Faltan datos requeridos',
            );
        }

        // Start transaction
        $wpdb->query('START TRANSACTION');

        try {
            // Calculate total
            $monto_total = 0;
            foreach ($data['items'] as $item) {
                $monto_total += $item['subtotal'];
            }

            // Generate receipt number
            $numero_recibo = $this->recibo_model->generate_numero_recibo($data['tipo']);

            // Create recibo
            $recibo_data = array(
                'numero_recibo' => $numero_recibo,
                'persona_id' => $data['persona_id'],
                'tipo' => $data['tipo'],
                'concepto' => $data['concepto'],
                'monto_total' => $monto_total,
                'fecha_emision' => current_time('mysql'),
                'metodo_pago' => isset($data['metodo_pago']) ? $data['metodo_pago'] : 'efectivo',
                'estado' => 'pagado',
                'usuario_id' => get_current_user_id(),
                'notas' => isset($data['notas']) ? $data['notas'] : null,
            );

            $recibo_id = $this->recibo_model->create($recibo_data);

            if (!$recibo_id) {
                throw new Exception('Error al crear el recibo');
            }

            // Create receipt items
            $items_table = $wpdb->prefix . 'cdc_items_recibo';
            foreach ($data['items'] as $item) {
                $wpdb->insert($items_table, array(
                    'recibo_id' => $recibo_id,
                    'descripcion' => $item['descripcion'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'subtotal' => $item['subtotal'],
                ));
            }

            // Register in cash register
            $caja_service = new CDC_Caja_Service();
            $caja_result = $caja_service->register_ingreso(
                $monto_total,
                'Cobro: ' . $data['concepto'],
                $recibo_id
            );

            if (!$caja_result['success']) {
                throw new Exception('Error al registrar en caja');
            }

            // Update cliente last visit and total spent if applicable
            $persona = $this->persona_model->find($data['persona_id']);
            if ($persona && in_array($persona->tipo, array('cliente', 'ambos'))) {
                $cliente_model = new CDC_Cliente();
                $cliente = $cliente_model->find_by_persona_id($data['persona_id']);
                if ($cliente) {
                    $cliente_model->update_last_visit($cliente->id);
                    $cliente_model->add_to_total_spent($cliente->id, $monto_total);
                }
            }

            // Commit transaction
            $wpdb->query('COMMIT');

            return array(
                'success' => true,
                'message' => 'Recibo creado correctamente',
                'data' => array(
                    'recibo_id' => $recibo_id,
                    'numero_recibo' => $numero_recibo,
                ),
            );

        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');

            return array(
                'success' => false,
                'message' => $e->getMessage(),
            );
        }
    }

    /**
     * Get recibo with full details
     *
     * @param int $id Recibo ID
     * @return object|null
     */
    public function get_recibo_full($id) {
        return $this->recibo_model->get_full_data($id);
    }

    /**
     * Get recent receipts
     *
     * @param int $limit Limit
     * @return array
     */
    public function get_recent_receipts($limit = 10) {
        return $this->recibo_model->get_all(array(
            'limit' => $limit,
            'orderby' => 'fecha_emision',
            'order' => 'DESC',
        ));
    }

    /**
     * Get receipts for today
     *
     * @return array
     */
    public function get_today_receipts() {
        return $this->recibo_model->get_today();
    }

    /**
     * Get receipts by date range
     *
     * @param string $fecha_inicio Start date
     * @param string $fecha_fin End date
     * @return array
     */
    public function get_receipts_by_date_range($fecha_inicio, $fecha_fin) {
        return $this->recibo_model->get_by_date_range($fecha_inicio, $fecha_fin);
    }

    /**
     * Anular recibo
     *
     * @param int $id Recibo ID
     * @param string $motivo Reason for cancellation
     * @return array Result
     */
    public function anular_recibo($id, $motivo = '') {
        global $wpdb;

        $recibo = $this->recibo_model->find($id);

        if (!$recibo) {
            return array(
                'success' => false,
                'message' => 'Recibo no encontrado',
            );
        }

        if ($recibo->estado === 'anulado') {
            return array(
                'success' => false,
                'message' => 'El recibo ya está anulado',
            );
        }

        $wpdb->query('START TRANSACTION');

        try {
            // Update receipt status
            $this->recibo_model->update($id, array(
                'estado' => 'anulado',
                'notas' => $recibo->notas . "\nANULADO: " . $motivo,
            ));

            // Register egreso in cash register
            $caja_service = new CDC_Caja_Service();
            $caja_service->register_egreso(
                $recibo->monto_total,
                'Anulación de recibo: ' . $recibo->numero_recibo,
                null,
                $id
            );

            $wpdb->query('COMMIT');

            return array(
                'success' => true,
                'message' => 'Recibo anulado correctamente',
            );

        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');

            return array(
                'success' => false,
                'message' => $e->getMessage(),
            );
        }
    }
}
