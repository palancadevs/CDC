<?php
/**
 * Persona Service
 *
 * @package CDC_API
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Persona Service Class
 * Business logic for personas, socios and clientes
 */
class CDC_Persona_Service {
    /**
     * Persona model
     */
    private $persona_model;

    /**
     * Socio model
     */
    private $socio_model;

    /**
     * Cliente model
     */
    private $cliente_model;

    /**
     * Constructor
     */
    public function __construct() {
        $this->persona_model = new CDC_Persona();
        $this->socio_model = new CDC_Socio();
        $this->cliente_model = new CDC_Cliente();
    }

    /**
     * Create new persona
     *
     * @param array $data Persona data
     * @return array Result with success/error
     */
    public function create_persona($data) {
        // Validate required fields
        if (empty($data['nombre']) || empty($data['apellido']) || empty($data['dni'])) {
            return array(
                'success' => false,
                'message' => 'Nombre, apellido y DNI son requeridos',
            );
        }

        // Check if DNI already exists
        if ($this->persona_model->dni_exists($data['dni'])) {
            return array(
                'success' => false,
                'message' => 'El DNI ya está registrado',
            );
        }

        // Create persona
        $persona_id = $this->persona_model->create($data);

        if (!$persona_id) {
            return array(
                'success' => false,
                'message' => 'Error al crear la persona',
            );
        }

        // Create socio or cliente record based on type
        $tipo = isset($data['tipo']) ? $data['tipo'] : 'cliente';

        if (in_array($tipo, array('socio', 'ambos'))) {
            $this->create_socio_record($persona_id, $data);
        }

        if (in_array($tipo, array('cliente', 'ambos'))) {
            $this->create_cliente_record($persona_id, $data);
        }

        return array(
            'success' => true,
            'message' => 'Persona creada correctamente',
            'data' => array('id' => $persona_id),
        );
    }

    /**
     * Update persona
     *
     * @param int $id Persona ID
     * @param array $data Update data
     * @return array Result
     */
    public function update_persona($id, $data) {
        $persona = $this->persona_model->find($id);

        if (!$persona) {
            return array(
                'success' => false,
                'message' => 'Persona no encontrada',
            );
        }

        // Check DNI uniqueness if changing
        if (isset($data['dni']) && $data['dni'] !== $persona->dni) {
            if ($this->persona_model->dni_exists($data['dni'], $id)) {
                return array(
                    'success' => false,
                    'message' => 'El DNI ya está registrado',
                );
            }
        }

        $result = $this->persona_model->update($id, $data);

        return array(
            'success' => $result,
            'message' => $result ? 'Persona actualizada correctamente' : 'Error al actualizar la persona',
        );
    }

    /**
     * Search personas
     *
     * @param string $query Search query
     * @param array $args Additional arguments
     * @return array
     */
    public function search_personas($query, $args = array()) {
        return $this->persona_model->search_personas($query, $args);
    }

    /**
     * Get persona with full data
     *
     * @param int $id Persona ID
     * @return object|null
     */
    public function get_persona_full($id) {
        return $this->persona_model->get_full_data($id);
    }

    /**
     * Create socio record
     *
     * @param int $persona_id Persona ID
     * @param array $data Socio data
     * @return int|false
     */
    private function create_socio_record($persona_id, $data) {
        $socio_data = array(
            'persona_id' => $persona_id,
            'numero_socio' => $this->socio_model->generate_numero_socio(),
            'fecha_alta' => isset($data['fecha_alta']) ? $data['fecha_alta'] : current_time('mysql', false),
            'estado' => isset($data['estado_socio']) ? $data['estado_socio'] : 'activo',
            'monto_cuota' => isset($data['monto_cuota']) ? $data['monto_cuota'] : 0.00,
            'dia_cobro' => isset($data['dia_cobro']) ? $data['dia_cobro'] : null,
        );

        return $this->socio_model->create($socio_data);
    }

    /**
     * Create cliente record
     *
     * @param int $persona_id Persona ID
     * @param array $data Cliente data
     * @return int|false
     */
    private function create_cliente_record($persona_id, $data) {
        $cliente_data = array(
            'persona_id' => $persona_id,
            'primera_visita' => current_time('mysql', false),
            'total_gastado' => 0.00,
        );

        return $this->cliente_model->create($cliente_data);
    }

    /**
     * Convert cliente to socio
     *
     * @param int $persona_id Persona ID
     * @param array $socio_data Socio configuration
     * @return array Result
     */
    public function convert_to_socio($persona_id, $socio_data) {
        $persona = $this->persona_model->find($persona_id);

        if (!$persona) {
            return array(
                'success' => false,
                'message' => 'Persona no encontrada',
            );
        }

        // Update persona type
        $new_tipo = ($persona->tipo === 'cliente') ? 'socio' : 'ambos';
        $this->persona_model->update($persona_id, array('tipo' => $new_tipo));

        // Create socio record if doesn't exist
        $socio = $this->socio_model->find_by_persona_id($persona_id);

        if (!$socio) {
            $this->create_socio_record($persona_id, $socio_data);
        }

        return array(
            'success' => true,
            'message' => 'Convertido a socio correctamente',
        );
    }
}
