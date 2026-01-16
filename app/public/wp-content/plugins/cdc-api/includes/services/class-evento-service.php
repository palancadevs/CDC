<?php
/**
 * Evento Service
 *
 * @package CDC_API
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Evento Service Class
 */
class CDC_Evento_Service {
    /**
     * Evento model
     */
    private $evento_model;

    /**
     * Constructor
     */
    public function __construct() {
        $this->evento_model = new CDC_Evento();
    }

    /**
     * Create evento
     *
     * @param array $data Evento data
     * @return array Result
     */
    public function create_evento($data) {
        if (empty($data['nombre']) || empty($data['fecha_evento'])) {
            return array(
                'success' => false,
                'message' => 'Nombre y fecha son requeridos',
            );
        }

        $evento_id = $this->evento_model->create($data);

        if (!$evento_id) {
            return array(
                'success' => false,
                'message' => 'Error al crear el evento',
            );
        }

        return array(
            'success' => true,
            'message' => 'Evento creado correctamente',
            'data' => array('id' => $evento_id),
        );
    }

    /**
     * Update evento
     *
     * @param int $id Evento ID
     * @param array $data Update data
     * @return array Result
     */
    public function update_evento($id, $data) {
        $evento = $this->evento_model->find($id);

        if (!$evento) {
            return array(
                'success' => false,
                'message' => 'Evento no encontrado',
            );
        }

        $result = $this->evento_model->update($id, $data);

        return array(
            'success' => $result,
            'message' => $result ? 'Evento actualizado correctamente' : 'Error al actualizar el evento',
        );
    }

    /**
     * Get upcoming eventos
     *
     * @return array
     */
    public function get_upcoming_eventos() {
        return $this->evento_model->get_upcoming();
    }

    /**
     * Get all eventos
     *
     * @param array $args Arguments
     * @return array
     */
    public function get_all_eventos($args = array()) {
        return $this->evento_model->get_all($args);
    }

    /**
     * Get evento by ID
     *
     * @param int $id Evento ID
     * @return object|null
     */
    public function get_evento($id) {
        return $this->evento_model->find($id);
    }

    /**
     * Register payment for evento
     *
     * @param int $evento_id Evento ID
     * @param int $persona_id Persona ID
     * @param float $monto Amount paid
     * @param bool $es_socio Is member
     * @return array Result
     */
    public function register_payment($evento_id, $persona_id, $monto, $es_socio) {
        global $wpdb;

        $evento = $this->evento_model->find($evento_id);

        if (!$evento) {
            return array(
                'success' => false,
                'message' => 'Evento no encontrado',
            );
        }

        // Check availability
        if (!$this->evento_model->has_available_spots($evento_id)) {
            return array(
                'success' => false,
                'message' => 'No hay cupos disponibles',
            );
        }

        // Create receipt
        $recibo_service = new CDC_Recibo_Service();
        $recibo_result = $recibo_service->create_recibo(array(
            'persona_id' => $persona_id,
            'tipo' => 'evento',
            'concepto' => 'Entrada: ' . $evento->nombre,
            'metodo_pago' => 'efectivo',
            'items' => array(
                array(
                    'descripcion' => 'Entrada: ' . $evento->nombre,
                    'cantidad' => 1,
                    'precio_unitario' => $monto,
                    'subtotal' => $monto,
                ),
            ),
        ));

        if (!$recibo_result['success']) {
            return $recibo_result;
        }

        // Register payment
        $pagos_table = $wpdb->prefix . 'cdc_pagos_eventos';
        $wpdb->insert($pagos_table, array(
            'evento_id' => $evento_id,
            'persona_id' => $persona_id,
            'recibo_id' => $recibo_result['data']['recibo_id'],
            'monto_pagado' => $monto,
            'fecha_pago' => current_time('mysql'),
            'es_socio' => $es_socio ? 1 : 0,
        ));

        // Increment inscriptos
        $this->evento_model->increment_inscriptos($evento_id);

        return array(
            'success' => true,
            'message' => 'Pago registrado correctamente',
            'data' => $recibo_result['data'],
        );
    }
}
