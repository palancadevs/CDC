<?php
/**
 * Inscripcion Service
 *
 * @package CDC_API
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Inscripcion Service Class
 */
class CDC_Inscripcion_Service {
    /**
     * Inscripcion Taller model
     */
    private $inscripcion_model;

    /**
     * Taller model
     */
    private $taller_model;

    /**
     * Cuota Taller model
     */
    private $cuota_taller_model;

    /**
     * Constructor
     */
    public function __construct() {
        $this->inscripcion_model = new CDC_Inscripcion_Taller();
        $this->taller_model = new CDC_Taller();
        $this->cuota_taller_model = new CDC_Cuota_Taller();
    }

    /**
     * Inscribe a person to a taller
     *
     * @param int $taller_id Taller ID
     * @param int $persona_id Persona ID
     * @param array $data Additional data (monto_mensual, notas)
     * @return array Result
     */
    public function inscribir_persona($taller_id, $persona_id, $data = array()) {
        global $wpdb;

        // Validate taller exists
        $taller = $this->taller_model->find($taller_id);
        if (!$taller) {
            return array(
                'success' => false,
                'message' => 'Taller no encontrado',
            );
        }

        // Check if taller is active
        if ($taller->estado !== 'activo') {
            return array(
                'success' => false,
                'message' => 'El taller no está activo',
            );
        }

        // Check if person is already inscribed
        if ($this->inscripcion_model->is_inscribed($persona_id, $taller_id)) {
            return array(
                'success' => false,
                'message' => 'La persona ya está inscrita en este taller',
            );
        }

        // Check if taller has available spots
        if (!$this->taller_model->has_available_spots($taller_id)) {
            return array(
                'success' => false,
                'message' => 'El taller no tiene cupos disponibles',
            );
        }

        // Start transaction
        $wpdb->query('START TRANSACTION');

        try {
            // Determine monto_mensual (use provided or taller's precio_mensual)
            $monto_mensual = isset($data['monto_mensual']) && $data['monto_mensual'] > 0
                ? floatval($data['monto_mensual'])
                : floatval($taller->precio_mensual);

            // Create inscripcion
            $inscripcion_data = array(
                'taller_id' => $taller_id,
                'persona_id' => $persona_id,
                'fecha_inscripcion' => isset($data['fecha_inscripcion']) ? $data['fecha_inscripcion'] : date('Y-m-d'),
                'estado' => 'activo',
                'monto_mensual' => $monto_mensual,
                'notas' => isset($data['notas']) ? $data['notas'] : null,
            );

            $inscripcion_id = $this->inscripcion_model->create($inscripcion_data);

            if (!$inscripcion_id) {
                throw new Exception('Error al crear la inscripción');
            }

            // Generate cuotas for current year
            $current_year = date('Y');
            $current_month = intval(date('m'));

            // Start generating cuotas from current month
            $cuota_ids = $this->cuota_taller_model->generate_cuotas(
                $inscripcion_id,
                $persona_id,
                $taller_id,
                $current_year,
                $monto_mensual,
                $current_month
            );

            if (empty($cuota_ids)) {
                throw new Exception('Error al generar cuotas');
            }

            // Increment inscriptos count
            $updated = $this->taller_model->increment_inscriptos($taller_id);
            if (!$updated) {
                throw new Exception('Error al actualizar contador de inscriptos');
            }

            // Commit transaction
            $wpdb->query('COMMIT');

            return array(
                'success' => true,
                'message' => 'Persona inscrita correctamente',
                'data' => array(
                    'inscripcion_id' => $inscripcion_id,
                    'cuotas_generadas' => count($cuota_ids),
                    'monto_mensual' => $monto_mensual,
                ),
            );

        } catch (Exception $e) {
            // Rollback on error
            $wpdb->query('ROLLBACK');

            return array(
                'success' => false,
                'message' => $e->getMessage(),
            );
        }
    }

    /**
     * Get inscripciones for a taller
     *
     * @param int $taller_id Taller ID
     * @param string $estado Estado filter (optional)
     * @return array
     */
    public function get_inscripciones_taller($taller_id, $estado = null) {
        return $this->inscripcion_model->get_by_taller($taller_id, $estado);
    }

    /**
     * Get inscripciones for a persona
     *
     * @param int $persona_id Persona ID
     * @param string $estado Estado filter (optional)
     * @return array
     */
    public function get_inscripciones_persona($persona_id, $estado = null) {
        return $this->inscripcion_model->get_by_persona($persona_id, $estado);
    }

    /**
     * Dar de baja inscripcion
     *
     * @param int $inscripcion_id Inscripcion ID
     * @param array $data Additional data (fecha_baja, notas)
     * @return array Result
     */
    public function dar_de_baja($inscripcion_id, $data = array()) {
        global $wpdb;

        $inscripcion = $this->inscripcion_model->find($inscripcion_id);
        if (!$inscripcion) {
            return array(
                'success' => false,
                'message' => 'Inscripción no encontrada',
            );
        }

        if ($inscripcion->estado !== 'activo') {
            return array(
                'success' => false,
                'message' => 'La inscripción ya está inactiva',
            );
        }

        // Start transaction
        $wpdb->query('START TRANSACTION');

        try {
            // Update inscripcion
            $update_data = array(
                'estado' => 'inactivo',
                'fecha_baja' => isset($data['fecha_baja']) ? $data['fecha_baja'] : date('Y-m-d'),
            );

            if (isset($data['notas'])) {
                $update_data['notas'] = $data['notas'];
            }

            $updated = $this->inscripcion_model->update($inscripcion_id, $update_data);
            if (!$updated) {
                throw new Exception('Error al actualizar inscripción');
            }

            // Decrement inscriptos count
            $decremented = $this->taller_model->decrement_inscriptos($inscripcion->taller_id);
            if (!$decremented) {
                throw new Exception('Error al actualizar contador de inscriptos');
            }

            // Commit transaction
            $wpdb->query('COMMIT');

            return array(
                'success' => true,
                'message' => 'Inscripción dada de baja correctamente',
            );

        } catch (Exception $e) {
            // Rollback on error
            $wpdb->query('ROLLBACK');

            return array(
                'success' => false,
                'message' => $e->getMessage(),
            );
        }
    }
}
