<?php
/**
 * Sala Service
 *
 * @package CDC_API
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Sala Service Class
 */
class CDC_Sala_Service {
    /**
     * Sala model
     */
    private $sala_model;

    /**
     * Reserva model
     */
    private $reserva_model;

    /**
     * Constructor
     */
    public function __construct() {
        $this->sala_model = new CDC_Sala();
        $this->reserva_model = new CDC_Reserva_Sala();
    }

    /**
     * Create sala
     *
     * @param array $data Sala data
     * @return array Result
     */
    public function create_sala($data) {
        if (empty($data['nombre'])) {
            return array(
                'success' => false,
                'message' => 'El nombre es requerido',
            );
        }

        $sala_id = $this->sala_model->create($data);

        if (!$sala_id) {
            return array(
                'success' => false,
                'message' => 'Error al crear la sala',
            );
        }

        return array(
            'success' => true,
            'message' => 'Sala creada correctamente',
            'data' => array('id' => $sala_id),
        );
    }

    /**
     * Update sala
     *
     * @param int $id Sala ID
     * @param array $data Update data
     * @return array Result
     */
    public function update_sala($id, $data) {
        $sala = $this->sala_model->find($id);

        if (!$sala) {
            return array(
                'success' => false,
                'message' => 'Sala no encontrada',
            );
        }

        $result = $this->sala_model->update($id, $data);

        return array(
            'success' => $result,
            'message' => $result ? 'Sala actualizada correctamente' : 'Error al actualizar la sala',
        );
    }

    /**
     * Get available salas
     *
     * @return array
     */
    public function get_available_salas() {
        return $this->sala_model->get_available();
    }

    /**
     * Create reserva
     *
     * @param array $data Reserva data
     * @return array Result
     */
    public function create_reserva($data) {
        // Validate required fields
        if (empty($data['sala_id']) || empty($data['persona_id']) ||
            empty($data['fecha_inicio']) || empty($data['fecha_fin'])) {
            return array(
                'success' => false,
                'message' => 'Faltan datos requeridos',
            );
        }

        // Check sala availability
        if (!$this->sala_model->is_available($data['sala_id'], $data['fecha_inicio'], $data['fecha_fin'])) {
            return array(
                'success' => false,
                'message' => 'La sala no está disponible en ese horario',
            );
        }

        // Calculate duration and total
        $inicio = new DateTime($data['fecha_inicio']);
        $fin = new DateTime($data['fecha_fin']);
        $duracion_horas = ($fin->getTimestamp() - $inicio->getTimestamp()) / 3600;

        $sala = $this->sala_model->find($data['sala_id']);
        $monto_total = $duracion_horas * $sala->precio_hora;

        $data['duracion_horas'] = $duracion_horas;
        $data['monto_total'] = $monto_total;

        $reserva_id = $this->reserva_model->create($data);

        if (!$reserva_id) {
            return array(
                'success' => false,
                'message' => 'Error al crear la reserva',
            );
        }

        return array(
            'success' => true,
            'message' => 'Reserva creada correctamente',
            'data' => array(
                'id' => $reserva_id,
                'monto_total' => $monto_total,
            ),
        );
    }

    /**
     * Get upcoming reservas for sala
     *
     * @param int $sala_id Sala ID
     * @return array
     */
    public function get_upcoming_reservas($sala_id = null) {
        return $this->reserva_model->get_upcoming($sala_id);
    }
}
